<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://www.banshee-php.org/
	 *
	 * Licensed under The MIT License
	 */
    namespace lib\db;
    
	class MySQLi_connection extends database_connection {
		public function __construct($hostname, $database, $username, $password, $port = 3306) {
			$this->db_close         = "mysqli_close";
			$this->db_insert_id     = "mysqli_insert_id";
			$this->db_escape_string = array($this, "db_escape_string_wrapper");
			$this->db_query         = array($this, "db_query_wrapper");
			$this->db_fetch         = "mysqli_fetch_assoc";
			$this->db_free_result   = "mysqli_free_result";
			$this->db_affected_rows = "mysqli_affected_rows";
			$this->db_error         = "mysqli_error";
			$this->db_errno         = "mysqli_errno";
			$this->id_delim         = "`";

			if ($database != "") {
				if (($this->link = mysqli_connect($hostname, $username, $password, $database, $port)) == false) {
					$this->link = null;
				} else {
					$this->link->set_charset("utf8");
				}
			}
		}

		protected function db_escape_string_wrapper($str) {
			return mysqli_real_escape_string($this->link, $str);
		}

		protected function db_query_wrapper($query) {
			return mysqli_query($this->link, $query);
		}

		public function get_var($resource) {
			if ($this->link === null) {
				return false;
			}

            $result = $resource->fetch_assoc();
            if($result){
                $data = array_values($result)[0];
                $resource->free_result();
                return $data;
            }
		}

		public function get_results($resource) {
			if ($this->link === null) {
				return false;
			}

            $num_rows=0;
            while ($row = $resource->fetch_assoc() ){
                $obj= (object) $row; 
                $result[$num_rows] = $obj;
                $num_rows++;
            }
            $resource->free_result();
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
