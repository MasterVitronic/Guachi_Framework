<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://www.banshee-php.org/
	 *
	 * Licensed under The MIT License
	 */
    namespace lib\db;
    
	class PGSQL_PDO_connection extends PDO_connection {
		protected $type = "pgsql";
		protected $id_delim = "`";
		protected $options = array();

		public function __construct() {
			$args = func_get_args();
			call_user_func_array(array("parent", "__construct"), $args);
            
		}
	}
?>
