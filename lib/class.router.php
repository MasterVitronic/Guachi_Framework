<?php
/*
            ____                  _     _
           / ___|_   _  __ _  ___| |__ (_)
          | |  _| | | |/ _` |/ __| '_ \| |
          | |_| | |_| | (_| | (__| | | | |
           \____|\__,_|\__,_|\___|_| |_|_|
Copyright (c) 2014  Díaz  Víctor  aka  (Máster Vitronic)
Copyright (c) 2018  Díaz  Víctor  aka  (Máster Vitronic)
<vitronic2@gmail.com>   <mastervitronic@vitronic.com.ve>
*/

class router {

    /**
     * El nombre del modulo
     *
     * @var string
     * @access private
     */
    private $module         = null;

    /**
     * El tipo de modulo public|private
     *
     * @var string
     * @access private
     */
    private $type_module    = null;

    /**
     * La url 
     *
     * @var string
     * @access private
     */
    private $url            = null;

    /**
     * La pagina solicitada
     *
     * @var string
     * @access private
     */
    private $page           = null;

    private $request        = array();

    /**
     * Los parametros pasados a la url
     *
     * @var array
     * @access private
     */
    private $parameters     = array();

    /**
     * Indica si el request es ajax
     *
     * @var bool
     * @access private
     */
    private $ajax_request   = false;

    /**
     * El recuerso de la clase auth
     *
     * @var resource
     * @access private
     */
    private $auth           = null;

    /**
     * Code HTTP
     *
     * @var int
     * @access private
     */
    private $http_status    = 200;

    /**
     * Instancia para el patrón de diseño singleton (instancia única)
     * @var object instancia
     * @access private
     */
    private static $instancia = null;

    private function __construct() {
        $this->auth = auth::iniciar();
        /* AJAX request
         */
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' and !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->ajax_request = true;
        }

        list($this->url) = explode("?", $_SERVER["REQUEST_URI"], 2);
        $path = $this->url;
        if (substr($path, 0, 1) == "/") {
            $path = substr($path, 1);
        }
        $path = rtrim($path, "/");

        if ($path == "") {
            $page = start_module;
        } else if (valid_input($path, VALIDATE_URL, VALIDATE_NONEMPTY)) {
            $page = $path;
        } else {
            $this->module = ERROR_MODULE;
        }
        if(isset($page)){
            $this->pathinfo = explode("/", $page);
        }
        
        if ($this->module === null) {
            $this->select_module($page);
        }
       
    }

    public function __destruct() {
    }

    /**
     * Inicia la instancia de la clase
     * @return object
     */
    public static function iniciar() {
        if (!self::$instancia instanceof self) {
            self::$instancia = new self;
        }
        return self::$instancia;
    }

    /**
     * Método magico __clone
     */
    public function __clone() {
        trigger_error("Operación Invalida:" .
                " clonación no permitida", E_USER_ERROR);
    }

    /**
     * Método magico __wakeup
     */
    public function __wakeup() {
        trigger_error("Operación Invalida:" .
                " deserializar no esta permitido " .
                get_class($this) . " Class. ", E_USER_ERROR);
    }

    /* Magic method get
     *
     * INPUT:  string key
     * OUTPUT: mixed value
     * ERROR:  null
     */
    public function __get($key) {
        switch ($key) {
            case "module"       : return $this->module;
            case "type_module"  : return $this->type_module;
            case "page"         : return $this->page;
            case "url"          : return $this->url;
            case "request"      : return $this->request;
            case "parameters"   : return $this->parameters;
            case "ajax_request" : return $this->ajax_request;
        }   
        return null;
    }

    /**
     * module_on_disk
     * Module available on disk
     * 
     * @param string $url    la url
     * @param string $pages  public|private
     *
     * @author Hugo Leisink <hugo@leisink.net>
     * @return string module identifier
     * @access private
     *
     * @see https://banshee-php.org/
     */
    private function module_on_disk($url, $pages) {
        $module = null;
        $url = explode("/", $url);
        $url_count = count($url);
       
        foreach ($pages as $line) {
            $page = explode("/", $line);
            $parts = count($page);
            $match = true;
           
            for ($i = 0; $i < $parts; $i++) {
                if ($page[$i] == "*" ) {
                    continue;
                } else if ( !isset($url[$i]) or $page[$i] !== $url[$i] ) {                   
                    $match = false;
                    break;
                }
            }
    
            if ($match && (strlen($line) >= strlen($module))) {
                $module = page_to_module($line);
            }
        }
    
        return $module;
    }

    /**
     * select_module
     * Determina qué módulo necesita ser cargado basándose en la página solicitada.
     * 
     * @param string $page  La pagina solicitada
     *
     * @author Hugo Leisink <hugo@leisink.net>
     * @author Máster Vitronic
     * @access public
     *
     * @see https://banshee-php.org/
     */
    public function select_module($page) {
        if (($this->module !== null) && ($this->module !== LOGIN_MODULE)) {
            return;
        }
    
        if (($public_module = $this->module_on_disk($page, getModules("public",'page'))) !== null) {
            $public_count = substr_count($public_module, "/") + 1;
        } else {
            $public_count = 0;
        }
    
        if (($private_module = $this->module_on_disk($page, getModules("private",'admin') )) !== null) {
            $private_count = substr_count($private_module, "/") + 1;
        } else {
            $private_count = 0;
        }
    
        if (($public_module == null) && ($private_module == null)) {
            /* La pagina no existe
             */
            $this->module = ERROR_MODULE;
            //$this->http_code = 404;
            return;
        }
    
        if ($public_count >= $private_count) {
            /* La pagina es publica
             */
            $this->type_module = 'public';
            $this->module = $public_module;
            $this->parameters = array_slice($this->pathinfo, $public_count);
            return;
        }
    
        /* La pagina es privada
         */
        $this->type_module = 'private';
        $this->module = $private_module;
        $this->parameters = array_slice($this->pathinfo, $private_count);
    
        if ($this->auth->isLogged() == false) {
            /* Usuario no esta logueado
             */
            $this->module = LOGIN_MODULE;
        } //else if ($this->auth->permiso($this->__get("page")) == false) {
				/* Acceso denegado a causa de que no hay permisos sufucientes
				 */
				//$this->module = ERROR_MODULE;
				//$this->http_code = 403;
				//$this->type = "";
				//$this->user->log_action("unauthorized request for page %s", $page);
        //} 
    }


}
