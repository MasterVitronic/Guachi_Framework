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

class auth {

    private $db;
    private $username;
    private $id_user;
    const   KEY_CSRF = "csrf";
    
    /**
     * Instancia para el patrón de diseño singleton (instancia única)
     * @var object instancia
     * @access private
     */
    private static $instancia = null;

    /**
     * __construct
     *
     * Constructor de la clase
     *
     * @access public
     *
     */
    private function __construct() {
        if (!isset($_SESSION)) {
            session_start();
        }
        global $db;
        $this->db = $db;

        /* Autenticación HTTP básica para servicios web
         */
        if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
            list($method, $auth) = explode(" ", $_SERVER["HTTP_AUTHORIZATION"], 2);
            if (($method == "Basic") && (($auth = base64_decode($auth)) !== false)) {
                list($username, $password) = explode(":", $auth, 2);
                if ($this->logIn($username, $password) == false) {
                    header("Status: 401");
                } else {
                    $this->key('login');
                }
            }
        }

    }

    /**
     * __destruct
     *
     * Destructor, destruye automaticamente la clase.
     *
     * @access public
     */
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
        trigger_error('Operación Invalida:' .
                ' clonación no permitida', E_USER_ERROR);
    }

    /**
     * Método magico __wakeup
     */
    public function __wakeup() {
        trigger_error('Operación Invalida:' .
                ' deserializar no esta permitido ' .
                get_class($this) . " Class. ", E_USER_ERROR);
    }

    /**
     * metodo key
     *
     * @access private
     */
    private function key($mode) {
        switch ($mode) {
            case 'exit':
                return (session_destroy()) ? true : false;
            case 'login':
                $ip_address = get_ip();
                if($ip_address){
                    $_SESSION = [
                        'username'          => $this->username,
                        'id_user'           => $this->id_user,
                        'session_timeout'   => (time() + session_timeout),
                        'ip_address'        => $ip_address
                    ];
                    return true;
                }
                return false;
        }
        return false;
    }

    /**
     * metodo logIn
     *
     * @access public
     */
    public function logIn($username, $password) {
        $query    = 'select id_user,status,password from users where username=%s and status=%s';
        $resource = $this->db->query($query,$username,'t');
        $results  = $this->db->get_row($resource);
        if(isset($results->id_user)){
            if(password_verify($password , $results->password)){
                $this->username  = $username;
                $this->id_user   = $results->id_user;
                $this->key('login');
                return true;
            }
        }
        return false;
    }

    /**
     * Magic method get
     *
     * @access public
     */
    public function __get($key) {
        switch ($key) {
            case "username"     : return isset($_SESSION['id_user']) ? intval($_SESSION['username']) : false;
            case "id_user"      : return isset($_SESSION['id_user']) ? intval($_SESSION['id_user'])  : false;
        }   
        return null;
    }

    /**
     * metodo hashPass
     *
     * @access public
     */
    public function hashPass($password) {
        return  password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * metodo logOut
     *
     * @access public
     */
    public function logOut() {
        return $this->key('exit') == true ? true : false;
    }

    /**
     * metodo sessionIsValid
     *
     * @access public
     */
    public function sessionIsValid() {
        if( isset($_SESSION['id_user']) and isset($_SESSION['session_timeout']) ){
            if( $_SESSION['session_timeout'] > time() ){
                $_SESSION['session_timeout'] = (time() + session_timeout);
                return true;
            }
            $this->logOut();
        }
        return false;
    }

    /**
     * metodo isLogged
     *
     * @access public
     */
    public function isLogged() {
       return ($this->sessionIsValid()) ? true : false ;
    }

    /**
     * metodo setCsrf
     *
     * @access public
     */
    public function setCsrf() {
        $_SESSION[self::KEY_CSRF] = hash("sha256", random_string(16));
    }

    /**
     * metodo getCsrf
     *
     * @access public
     */
    public function getCsrf() {
        return $_SESSION[self::KEY_CSRF];
    }
    
    /**
     * metodo csrfIsValid
     *
     * @access public
     */
    public function csrfIsValid($csrf) {
        return hash_equals($this->getCsrf(), $csrf);
    }

}
