<?php


	define('GUACHI_VERSION','2.1');
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', realpath(dirname( __DIR__ ) ) . DS );
	define("YES", 1);
	define("NO", 0);
	define("HOUR", 3600);
	define("DAY", 86400);
	define("PASSWORD_MIN_LENGTH", 8);
	define("PASSWORD_MAX_LENGTH", 1000);
	define("DIR_CONTROLLERS",   ROOT . 'controllers' . DS );
	define("DIR_MODELS",        ROOT . 'models'      . DS );
	define("DIR_VIEWS",         ROOT . 'views'       . DS );
	define("ERROR_MODULE",'notFound');
	define("LOGIN_MODULE",'login');

    /**
     * guachi_autoload
     *
     * @access public
     */
    function guachi_autoload($class_name) {
        $parts = explode("\\", $class_name);
        $class = array_pop($parts);

        array_shift($parts);
        $class = strtolower($class);
        $path = ROOT . 'lib/' .implode("/", $parts);

        if (file_exists($file = $path."/class.".strtolower($class).".php")) {
            include_once($file);
        } else if (file_exists($file = $path."/".strtolower($class).".php")) {
            include_once($file);
        } else if (file_exists($file = $path."/".$class.".php")) {
            include_once($file);
        }
    };

	/* Convert a page path to a module path
	 *
	 * INPUT:  array / string page path
	 * OUTPUT: array / string module path
	 * ERROR:  -
	 */
	function page_to_module($page) {
		if (is_array($page) == false) {
			if (($pos = strrpos($page, ".")) !== false) {
				$page = substr($page, 0, $pos);
			}
		} else foreach ($page as $i => $item) {
			$page[$i] = page_to_module($item);
		}

		return $page;
	}

    /**
     * metodo getModules
     *
     * @access public
     */
    function getModules($section,$module) {
        static $cache = array();
		if (isset($cache[$section][$module])) {
			return $cache[$section][$module];
		}
        $modules = parse_ini_file(ROOT . 'modules.ini', true);
        $cache = $modules;
        return $modules[$section][$module];
    }

	/* Check for module existence
	 *
	 * INPUT:  string module
	 * OUTPUT: bool module exists
	 * ERROR:  -
	 */
	function module_exists($module, $warn = false) {
		foreach (array("public", "private") as $type) {
            $section = ($type == 'private') ? 'admin' : 'page';
			if (in_array($module, getModules($type , $section)) ) {
				if ($warn) {
					printf("Ya existe un módulo %s '%s'.\n", $type, $module);
				}
				return true;
			}
		}
		return false;
	}

    /**
     * metodo setConfig
     *
     * @access public
     */
    function setConfig() {
        $config = parse_ini_file(ROOT . 'guachi.ini', true) ;
        foreach ($config as $bloque => $conf) {
            foreach ($config[$bloque] as $var => $valor) {
                if(!is_array($valor)){
                    define(trim($var), trim($valor));
                }
            }
        }
    }

    /**
     * get_ip
     *
     * Si es posible retorna la ip del visitante
     *
     * @access public
     * @return string
     */
    function get_ip(){
        $ip = false;
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }    
        if( filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ) {
          $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Formatea un numero a su correspondiente en moneda
     * @param  int      
     * @param  strin    
     * @param  bool     
     * @return string 
     */   
    function format_money($number, $locale, $remove = false) {
        $money = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $remove ? str_replace($remove, '', $money->format($number)) : $money->format($number);
    }

    /**
     * Redondeo bancario
     * @param  int      
     * @param  int    
     * @return mixed
     */
    function bround($dVal,$iDec) {
        static $dFuzz=0.00001; // to deal with floating-point precision loss
        $iRoundup=0; // amount to round up by
        $iSign=($dVal!=0.0) ? intval($dVal/abs($dVal)) : 1;
        $dVal=abs($dVal);
        // get decimal digit in question and amount to right of it as a fraction
        $dWorking=$dVal*pow(10.0,$iDec+1)-floor($dVal*pow(10.0,$iDec))*10.0;
        $iEvenOddDigit=floor($dVal*pow(10.0,$iDec))-floor($dVal*pow(10.0,$iDec-1))*10.0;

        if (abs($dWorking-5.0)<$dFuzz) $iRoundup=($iEvenOddDigit & 1) ? 1 : 0;
        else $iRoundup=($dWorking>5.0) ? 1 : 0;

        return $iSign*((floor($dVal*pow(10.0,$iDec))+$iRoundup)/pow(10.0,$iDec));
    }

    /**
     * Convierte mixed a boolean
     * @param  mixed      
     * @return boolean
     */
	function is_true($bool) {
		if (is_string($bool)) {
			$bool = strtolower($bool);
		}
		return in_array($bool, array(true, YES, "1", "yes", "true", "on"), true);
	}

	/* Convert mixed to boolean
	 *
	 * INPUT:  mixed
	 * OUTPUT: boolean
	 * ERROR:  -
	 */
	function is_false($bool) {
		return (is_true($bool) === false);
	}

	/* Convert boolean to string
	 *
	 * INPUT:  boolean
	 * OUTPUT: string "yes"|"no"
	 * ERROR:  -
	 */
	function show_boolean($bool) {
		return (is_true($bool) ? "yes" : "no");
	}

	/* Convert empty string to null
	 *
	 * INPUT:  string
	 * OUTPUT: string|null
	 * ERROR:  -
	 */
	function null_if_empty($str) {
		if (is_string($str) == false) {
			return $str;
		}

		if (trim($str) == "") {
			$str = null;
		}

		return $str;
	}

	/* Localized date string
	 *
	 * INPUT:  string format[, integer timestamp]
	 * OUTPUT: string date
	 * ERROR:  -
	 */
	function date_string($format, $timestamp = null) {
		if ($timestamp === null) {
			$timestamp = time();
		}

		$days_of_week = config_array(days_of_week);
		$months_of_year = config_array(months_of_year);

		$format = strtr($format, "lDFM", "#$%&");
		$result = date($format, $timestamp);

		$day = $days_of_week[(int)date("N", $timestamp) - 1];
		$result = str_replace("#", $day, $result);

		$day = substr($days_of_week[(int)date("N", $timestamp) - 1], 0, 3);
		$result = str_replace("$", $day, $result);

		$month = $months_of_year[(int)date("n", $timestamp) - 1];
		$result = str_replace("%", $month, $result);

		$month = substr($months_of_year[(int)date("n", $timestamp) - 1], 0, 3);
		$result = str_replace("&", $month, $result);

		return $result;
	}

	/* Convert configuration line to array
	 *
	 * INPUT:  string config line[, bool look for key-value
	 * OUTPUT: array config line
	 * ERROR:  -
	 */
	function config_array($line, $key_value = true) {
		$items = explode("|", $line);

		if ($key_value == false) {
			return $items;
		}

		$result = array();
		foreach ($items as $item) {
			@list($key, $value) =  explode(":", $item, 2);
			if ($value === null) {
				array_push($result, $key);
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}

    /**
     * https://stackoverflow.com/questions/5695145/how-to-read-and-write-to-an-ini-file-with-php
     * Write an ini configuration file
     * 
     * @param string $file
     * @param array  $array
     * @return bool
     */
    function write_ini_file($file, $array = []) {
        // check first argument is string
        if (!is_string($file)) {
            throw new \InvalidArgumentException('Function argument 1 must be a string.');
        }

        // check second argument is array
        if (!is_array($array)) {
            throw new \InvalidArgumentException('Function argument 2 must be an array.');
        }

        // process array
        $data = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            // empty line
            $data[] = null;
        }

        // open file pointer, init flock options
        $fp = fopen($file, 'w');
        $retries = 0;
        $max_retries = 100;

        if (!$fp) {
            return false;
        }

        // loop until get lock, or reach max retries
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

        // couldn't get the lock
        if ($retries == $max_retries) {
            return false;
        }

        // got lock, write data
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

        // release lock
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    /**
     * set_cache_header
     *
     * establece un header cache con un tiempo determinado
     *
     * @access public
     * @return 
     */
    function set_cache_header($seconds = false) {
        $seconds_to_cache = ($seconds) ? $seconds : $seconds_to_cache = 3600;
        $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$seconds_to_cache");
    }

    /**
     * no_cache
     *
     * no-cache al browser
     *
     * @access public
     * @return 
     */
    function set_no_cache_header() {
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /*seteo toda la configuracion*/
    setConfig();

    /*cosas por default*/
    date_default_timezone_set(default_date_timezone);
    setlocale(LC_TIME, locale);    
    (production)?error_reporting(0):error_reporting(-1);

    /*autocargo las dependencias*/
    spl_autoload_register("guachi_autoload", true, true);

	ini_set("zlib.output_compression", "Off");
	if (ini_get("allow_url_include") != 0) {
		exit("Set 'allow_url_include' to 0.");
	}

    /*configuro la conexion a la base de datos*/
    if( is_true(use_db) ){
        switch (db_type){
            case 'sqlite':
                $db  = new \lib\db\SQLite_connection(db_database);
                break;
            case 'sqlite3':
                $db  = new \lib\db\SQLite3_connection(db_database);
                break;
            case 'pgsql':
                $db  = new \lib\db\PostgreSQL_connection(db_hostname, db_database, db_username, db_password, db_port);
                break;
            case 'mysqli':
                $db  = new \lib\db\MySQLi_connection(db_hostname, db_database, db_username, db_password, db_port);
                break;
            case 'mysql_pdo':
                $db  = new \lib\db\MySQL_PDO_connection(db_hostname, db_database, db_username, db_password);
                break;
            case 'pgsql_pdo':
                $db  = new \lib\db\PGSQL_PDO_connection(db_hostname, db_database, db_username, db_password);
                break;
                
        }
    }