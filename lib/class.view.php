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

/*camelCase  :-) */
class view {

    private $css            = '' ;
    private $js             = '' ;
    private $author         = '' ;
    private $title          = '' ;
    private $description    = '' ;
    private $html           = '' ;
    private $meta                ;
    public  $tpl                 ;
    private $router              ;
    private $messages       = [] ;
    private $values         = [] ;
    public  $cache               ;
    private $http_status    = 200;

    /**
     * Instancia para el patrón de diseño singleton (instancia única)
     * @var object instancia
     * @access private
     */
    private static $instancia = null;

    private function __construct() {
        $this->tpl      =  new Mustache_Engine(array(
            'loader'    => new Mustache_Loader_FilesystemLoader(
                                ROOT . 'views' . DS ,
                                ['extension' => '.html']
            )
        ));
        $this->cache    = MicroCache::iniciar();
        $this->router   = router::iniciar();
        
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
     * @access public
     */
    public function __get($key) {
        switch ($key) {
            case 'meta'     : return $this->meta;
            case 'js'       : return $this->js;
            case 'css'      : return $this->css;
            case 'messages' : return $this->messages;
            case 'values'   : return $this->values;
            case 'title'    : return $this->title;
            case 'http_status': return $this->http_status;
            case 'description': return $this->description;
        }
        return null;
    }

    /* Magic method set
     *
     * @access public
     */
    public function __set($key,$value) {
        switch ($key) {
            case 'meta'     :  $this->meta      = $value; break;
            case 'messages' :  $this->messages  = $value; break;
            case 'values'   :  $this->values    = $value; break;
            case 'author'   :  $this->author    = $value; break;
            case 'title'    :  $this->title     = $value; break;
            case 'description':$this->description = $value; break;
            case 'http_status':$this->http_status = $value; break;
            default: trigger_error('Unknown variable: '.$key);
        }
    }

    /* Add message to message buffer
     *
     * INPUT:  string format[, string arg,...]
     * OUTPUT: -
     * ERROR:  -
     */
    public function add_message($message) {
        if (func_num_args() == 0) {
            return;
        }
        $args = func_get_args();
        $format = array_shift($args);

        array_push($this->messages,['message' => vsprintf($format, $args)]);
    }

    /**
     * addContent
     *
     */
    public function addContent($values) {
        if(is_array($values)){
            foreach ($values as $key => $value) {
                $this->values[$key] = $value;
            }
        }
    }

    /**
     * getContent 
     *
     */
    public function getContent() {
        return $this->values + [
                'messages' => $this->messages
        ];
    }

    /**
     * loadTemplate
     *
     * wraper a mustache loadTemplate
     *
     * @access public
     */
    public function loadTemplate($tpl) {
        $theme = ($this->router->type_module === 'private') ? private_theme : public_theme ;
        $template = $this->router->type_module .DS. $theme . DS . $tpl;
        return $this->tpl->loadTemplate($template);
    }

    /**
     * addCss
     *
     * Añade los css a usar en el metadata
     *
     * @access public
     */
    public function addCss($css) {
         $this->css .= "\t\t" .'<link rel="stylesheet"           href="'.$css.'">' . PHP_EOL;
    }

    /**
     * addJs
     *
     * Añade los js a usar
     *
     * @access public
     */
    public function addJs($js) {
         $this->js .= "\t\t" .'<script src="'.$js.'"></script>' . PHP_EOL;
    }

    /**
     * set
     *
     * setea todo
     *
     * @access public
     * @return string
     */
    public function set() {
        if(is_true(use_js_bundle)){
            $this->js .= "\t\t" .'<script src="/js/guachi.js"></script>' . PHP_EOL;
        }
        if( is_false(production) ){
            if( is_true(node_development_mode) and is_true(use_node_livereload) and valid_input(node_livereload_port, VALIDATE_NUMBERS) ){
                $this->js .= "\t\t" .'<script src="//127.0.0.1:'.node_livereload_port.'/livereload.js"></script>' . PHP_EOL;
            }
        }
        $this->js   = trim($this->js);
        $this->meta =    '<title>'.$this->title.'</title>' . PHP_EOL
                ."\t\t" .'<meta charset="utf-8">' . PHP_EOL
                ."\t\t" .'<meta http-equiv="X-UA-Compatible" content="IE=edge">' . PHP_EOL
                .$this->css
                ."\t\t" .'<meta name="viewport"            content="width=device-width, initial-scale=1.0">' . PHP_EOL
                ."\t\t" .'<meta name="generator"           content="Guachi (Lightweight and very simple php framework) v'.GUACHI_VERSION.'">' . PHP_EOL
                ."\t\t" .'<meta name="description"         content="'.$this->description.'">' . PHP_EOL
                ."\t\t" .'<meta name="author"              content="'.$this->author.'">' . PHP_EOL
                ."\t\t" .'<meta name="module"              content="'.$this->router->module.'">';
    }

    /**
     * load
     *
     * Carga el html
     *
     * @access private
     * @return string
     */
    public function load($html) {
        $this->html = trim($html);
    }

    /**
     * generate
     *
     * Escupe el html
     *
     * @access public
     * @return string
     */
    public function generate() {
        if ((headers_sent() == false) && ($this->http_status != 200)) {
            header(sprintf("Status: %d", $this->http_status));
        }
        if( is_true(production) ){
            header("X-Frame-Options: sameorigin");
            header("X-Content-Type-Options: nosniff");
        }
        $header="X-Powered-By: Guachi (Lightweight and very simple web "
        ."development framework of Vitronic) v".GUACHI_VERSION;
        header($header);
        if(is_true(use_cache)) {
            if(is_true($this->cache->cache_on)){
                $this->cache->init($this->router->url);
                set_cache_header();
                if ($this->cache->check()) {
                    print($this->cache->out());
                    return;
                }
                $this->cache->start();
                print($this->html . PHP_EOL);
                $this->cache->end();
            }
            print($this->html);
        }else{
            print($this->html);
        }
    }

}
