<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://www.banshee-php.org/
	 *
	 * Licensed under The MIT License
	 */
    namespace lib\db;
    
	class PostgreSQL_connection extends database_connection {
		private $last_query_resource = null;

		public function __construct($hostname, $database, $username, $password, $port = 5432) {
			$this->db_close         = "pg_close";
			$this->db_insert_id     = "pg_last_oid";
			$this->db_escape_string = array($this, "db_escape_string_wrapper");
			$this->db_query         = array($this, "db_query_wrapper");
			$this->db_fetch         = "pg_fetch_assoc";
			$this->db_free_result   = "pg_free_result";
			$this->db_affected_rows = array($this, "db_affected_rows_wrapper");
			$this->db_error         = "pg_last_error";
			$this->id_delim         = '"';

			if (($this->link = pg_connect("host=".$hostname." port=".$port." dbname=".$database." user=".$username." password=".$password)) == false) {
				$this->link = null;
			}
		}

		public function __get($key) {
			switch ($key) {
				case "last_insert_id":
				return $this->last_insert_id(0, $this->last_query_resource);
			}

			return parent::__get($key);
		}

		public function last_insert_id($history = null, $resource = null) {
			parent::last_insert_id($history, $resource !== null ? $resource : $this->last_query_resource);
		}

		protected function db_escape_string_wrapper($str) {
			return pg_escape_string($this->link, $str);
		}

		protected function db_query_wrapper($query, $link) {
			$this->last_query_resource = pg_query($link, $query);
			return $this->last_query_resource;
		}

		protected function db_affected_rows_wrapper($link) {
			pg_affected_rows($this->last_query_resource);
		}

		public function affected_rows($resource) {
			$this->db_affected_rows_wrapper($resource);
		}


		public function get_var($resource) {
			if ($this->link === null) {
				return false;
			}

            $result = pg_fetch_assoc($resource);
            if($result){
                $data = array_values($result)[0];
                pg_free_result($this->last_query_resource);
                return $data;
            }
		}

		public function get_results($resource) {
			if ($this->link === null) {
				return false;
			}

            $num_rows=0;
            while ($row = pg_fetch_assoc($resource) ){
                $obj= (object) $row; 
                $result[$num_rows] = $obj;
                $num_rows++;
            }
            pg_free_result($this->last_query_resource);
            if(isset($result)){
                return $result;
            }
		}

		public function get_row($resource) {
			if ($this->link === null) {
				return false;
			}

            $result = $this->get_results($resource);
            if($result){
                return $result[0]?$result[0]:null;
            }
		}

        
	}
?>
