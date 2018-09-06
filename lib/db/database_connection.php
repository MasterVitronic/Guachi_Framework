<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://www.banshee-php.org/
	 *
	 * Licensed under The MIT License
	 */
    namespace lib\db;
    
	abstract class database_connection {
		protected $link              = null;
		protected $db_close          = null;
		protected $db_escape_string  = null;
		protected $db_query          = null;
		protected $db_fetch          = null;
		protected $db_free_result    = null;
		protected $db_insert_id      = null;
		protected $db_affected_rows  = null;
		protected $db_error          = null;
		protected $db_errno          = null;
		protected $id_delim          = null;
		private   $read_only         = false;
		private   $insert_id_history = array();

		/* Desctructor
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __destruct() {
			if ($this->link !== null) {
				call_user_func($this->db_close, $this->link);
				$this->link = null;
			}
		}

		/* Magic method get
		 *
		 * INPUT:  string key
		 * OUTPUT: mixed value
		 * ERROR:  null
		 */
		public function __get($key) {
			switch ($key) {
				case "connected":
					return $this->link !== null;
				case "last_insert_id":
					return $this->last_insert_id(0);
				case "affected_rows":
					if ($this->db_affected_rows !== null) {
						return call_user_func($this->db_affected_rows, $this->link);
					}
					break;
				case "error":
					if ($this->db_error !== null) {
						return call_user_func($this->db_error, $this->link);
					}
					break;
				case "errno":
					if ($this->db_errno !== null) {
						return call_user_func($this->db_errno, $this->link);
					}
					break;
			}

			return null;
		}

		/* Make database connection read-only
		 */
		public function make_read_only() {
			$this->read_only = true;
		}

		/* Flatten array to new array with depth 1
		 *
		 * INPUT:  array data
		 * OUTPUT: array data
		 * ERROR:  -
		 */
		protected function flatten_array($data) {
			$result = array();
			foreach ($data as $item) {
				if (is_array($item)) {
					$result = array_merge($result, $this->flatten_array($item));
				} else {
					array_push($result, $item);
				}
			}

			return $result;
		}

		/* Delimit an identifier
		 *
		 * INPUT:  string identifier
		 * OUTPUT: string delimited identifier
		 * ERROR:  -
		 */
		protected function delimit_identifier($identifier) {
			if ($this->id_delim === null) {
				return $identifier;
			} else if (is_array($this->id_delim)) {
				return $this->id_delim[0].$identifier.$this->id_delim[1];
			} else {
				return $this->id_delim.$identifier.$this->id_delim;
			}
		}

		/* Validates a table name
		 *
		 * INPUT:  string table
		 * OUTPUT: bool valid table name
		 * ERROR:  -
		 */
		private function valid_table_name($table) {
			static $table_name_chars = null;

			if ($table == "") {
				return false;
			}

			if ($table_name_chars === null) {
				$table_name_chars = str_split("-_".
					"ABCDEFGHIJKLMNOPQRSTUVWXYZ".
					"abcdefghijklmnopqrstuvwxyz");
			}

			$diff = array_diff(str_split($table), $table_name_chars);

			return count($diff) == 0;
		}

		/* Return printf format for variable
		 *
		 * INPUT:  mixed variable
		 * OUTPUT: string printf format
		 * ERROR:  -
		 */
		private function type_to_format($variable) {
			if (is_integer($variable)) {
				return "%d";
			}
			if (is_float($variable)) {
				return "%f";
			}
			if (is_bool($variable)) {
				return "%d";
			}

			return "%s";
		}

		/* Returns the id of the latest created record
		 *
		 * INPUT:  [int history offset, [resource query resource]
		 * OUTPUT: int insert identifier
		 * ERROR:  false
		 */
		public function last_insert_id($history = null, $resource = null) {
			$param = ($resource !== null) ? $resource : $this->link;

			if ($history !== null) {
				$size = count($this->insert_id_history);
				return $history < $size ? $this->insert_id_history[(int)$history] : 0;
			} else if ($this->db_insert_id == null) {
				return false;
			} else if (($last_id = call_user_func($this->db_insert_id, $param)) == 0) {
				return $this->last_insert_id(0);
			} else {
				return $last_id;
			}
		}

		/* Create an SQL query by securely inserting the parameters in the query string
		 *
		 * INPUT:  string query[, mixed query parameter, ...]
		 * OUTPUT: string
		 * ERROR:  -
		 */
		protected function make_query() {
			if (func_num_args() == 0) {
				return false;
			}

			$args = func_get_args();
			$format = array_shift($args);
			$values = $this->flatten_array($args);
			unset($args);

			/* Security checks
			 */
			foreach (array("'", "`", "\"") as $char) {
				if (strpos($format, $char) !== false) {
					print $format."\nQuery not accepted.\n";
					return false;
				}
			}

			$format = str_replace("%s", "'%s'", $format);
			$format = str_replace("%S", $this->delimit_identifier("%s"), $format);

			/* Escape arguments
			 */
			foreach ($values as $key => $value) {
				$values[$key] = call_user_func($this->db_escape_string, $value);
			}

			/* Apply arguments to format
			 */
			return vsprintf($format, $values);
		}

		/* Execute an SQL query and return the resource identifier
		 *
		 * INPUT:  string query[, mixed query parameter, ...]
		 * OUTPUT: mixed resource identifier
		 * ERROR:  false
		 */
		public function query() {
			static $notified = false;

			if ($this->connected == false) {
				if ($notified == false) {
					printf("%s: not connected to the database!\n", get_class($this));
					$notified = true;
				}
				return false;
			}

			$args = func_get_args();
			if (($query = call_user_func_array(array($this, "make_query"), $args)) === false) {
				return false;
			}

			if ($this->read_only) {
				$parts = explode(" ", ltrim($args[0]), 2);
				$statement = strtolower(array_shift($parts));
				if (in_array($statement, array("select", "show")) == false) {
					print "Dropped query that tried to alter the database via a read-only database connection.\n";
					return false;
				}
			}

			$result = call_user_func($this->db_query, $query, $this->link);
			if ($result === false) {
				print "SQL query: ".$query."\n";
				print "Error message: ".$this->error."\n";
			} else if (($insert_id = $this->last_insert_id()) != 0) {
				array_unshift($this->insert_id_history, $insert_id);
			}

			return $result;
		}

		/* Fetch one item from result by resource
		 *
		 * INPUT:  mixed resource identifier
		 * OUTPUT: array( string key => string value[, ...] )|null
		 * ERROR:  false
		 */
		public function fetch($resource) {
			if (in_array($resource, array(null, false, true), true)) {
				$result = false;
			}

			return call_user_func($this->db_fetch, $resource);
		}

		/* Free query result memory
		 *
		 * INPUT:  mixed resource identifier
		 * OUTPUT: true
		 * ERROR:  false
		 */
		public function free_result($resource) {
			if ($this->db_free_result === null) {
				return true;
			}

			return call_user_func($this->db_free_result, $resource);
		}

		/* Execute an SQL query and return the result
		 *
		 * INPUT:  string query[, mixed query parameter, ...]
		 * OUTPUT: mixed result for 'select' and 'show' queries / int affected rows
		 * ERROR:  false
		 */
		public function execute() {
			$args = func_get_args();

			if (($resource = call_user_func_array(array($this, "query"), $args)) === false) {
				return false;
			}

			$parts = explode(" ", ltrim($args[0]), 2);
			$statement = strtolower(array_shift($parts));

			if (in_array($statement, array("select", "show", "describe", "explain"))) {
				$result = array();
				while (($data = $this->fetch($resource)) != false) {
					array_push($result, $data);
				}
				$this->free_result($resource);

				return $result;
			} else if ($this->db_affected_rows !== null) {
				return call_user_func($this->db_affected_rows, $this->link);
			}

			return true;
		}

		/* Execute queries via transaction
		 *
		 * INPUT:  array( array( string query[, mixed query parameter, ...] )[, ...] )
		 * OUTPUT: boolean transaction succesful
		 * ERROR:  -
		 */
		public function transaction($queries) {
			$this->query("begin");

			foreach ($queries as $args) {
				if (is_array($args) == false) {
					$query = $args;
					$args = array();
				} else {
					$query = array_shift($args);
				}

				/* Handle insert ids
				 */
				if (strpos($query, "INSERT_ID") !== false) {
					$query = str_replace("{LAST_INSERT_ID}", $this->last_insert_id, $query);

					$history_size = count($this->insert_id_history);
					for ($q = 0; $q < $history_size; $q++) {
						$query = str_replace("{INSERT_ID_".$q."}", $this->last_insert_id($q), $query);
					}
				}

				/* Execute query
				 */
				if ($this->query($query, $args) === false) {
					$this->query("rollback");
					return false;
				}
			}

			return $this->query("commit") !== false;
		}

		/* Retrieve an entry from a table
		 *
		 * INPUT:  string table, int entry identifier
		 * OUTPUT: array( value[, ....] )
		 * ERROR:  false
		 */
		public function entry($table, $id, $col = "id") {
			$type = (is_int($id) || ($col == "id")) ? "%d" : "%s";

			$query = "select * from %S where %S=".$type." limit 1";
			if (($resource = $this->query($query, $table, $col, $id)) === false) {
				return false;
			}

			return $this->fetch($resource);
		}

		/* Retorna true si un campo existe
		 *
		 * INPUT:  string table, array( string column name[, ...] ) , array( column => value, ... ), string , int
		 * OUTPUT: mixed string or false
		 * ERROR:  -
		 */
        public function exist($table, $uniques, $target, $id_target = false, $id = false ) {
            if(is_array($uniques)){
                foreach ($uniques as $key => $value) {
                    if(isset($target[$value])){
                        $type         = ( is_int($target[$value]) ) ? "%d" : "%s";
                        $query        = "select %S from %S where %S=".$type." ";
                        if ( $id_target and $id ) {
                            $type     = ( is_int($id) ) ? "%d" : "%s";
                            $query    .="and %S!=".$type." limit 1";
                            $resource = $this->query($query, $value, $table, $value, $target[$value], $id_target, $id);
                        }else{
                            $query    .= " limit 1";
                            $resource = $this->query($query, $value, $table, $value, $target[$value]);
                        }
                        if ( $this->fetch($resource)[$value] ) {
                            return $value;
                        }
                    }
                }
                return false;
            }
        }

		/* Insert new entry in table
		 *
		 * INPUT:  string table, array( string value[, ...] ), array( string column name[, ...] )
		 * OUTPUT: true|integer inserted rows
		 * ERROR:  false
		 */
		public function insert($table, $data, $keys = null) {
			if ($this->valid_table_name($table) == false) {
				return false;
			}

			if ($keys == null) {
				$keys = array_keys($data);
			}

			$format = $values = array();
			foreach ($keys as $key) {
				if ($data[$key] === null) {
					array_push($format, "null");
				} else {
					array_push($format, $this->type_to_format($data[$key]));
					array_push($values, $data[$key]);
				}
			}

			$columns = implode(", ", array_fill(1, count($keys), "%S"));

			$query = "insert into %S (".$columns.") values (".implode(", ", $format).")";
			if ($this->query($query, $table, $keys, $values) === false) {
				return false;
			}

			if ($this->db_affected_rows != null) {
				return call_user_func($this->db_affected_rows, $this->link);
			}

			return true;
		}

		/* Update entry in table
		 *
		 * INPUT:  string table, int entry identifier, array( mixed value[, ...] ), array( string column name[, ...] )
		 * OUTPUT: true|integer updated rows
		 * ERROR:  false
		 */
		public function update($table, $id, $index, $data, $keys = null) {
			if ($this->valid_table_name($table) == false) {
				return false;
			}

			if ($keys === null) {
				$keys = array_keys($data);
			}

			$format = $values = array();
			foreach ($keys as $key) {
				if (!isset($data[$key]) or $data[$key] === null) {
					array_push($format, "%S=null");
					array_push($values, $key);
				} else {
					array_push($format, "%S=".$this->type_to_format($data[$key]));
					array_push($values, $key, $data[$key]);
				}
			}

			$query = "update %S set ".implode(", ", $format)." where $index=%d";
			if ($this->query($query, $table, $values, $id) === false) {
				return false;
			}

			if ($this->db_affected_rows != null) {
				return call_user_func($this->db_affected_rows, $this->link);
			}

			return true;
		}

		/* Delete entry from table
		 *
		 * INPUT:  string table, int entry identifier
		 * OUTPUT: true|integer deleted rows
		 * ERROR:  false
		 */
		public function delete($table, $id) {
			if ($this->valid_table_name($table) == false) {
				return false;
			}

			$query = "delete from %S where id=%d";
			if ($this->query($query, $table, $id) === false) {
				return false;
			}

			if ($this->db_affected_rows != null) {
				return call_user_func($this->db_affected_rows, $this->link);
			}

			return true;
		}
	}
?>
