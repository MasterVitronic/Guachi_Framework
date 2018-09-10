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

    /**
     * Recurso de la db.
     *
     * @var resource
     * @access private
     */
    private $db;

    /**
     * El nombre de usuarios
     *
     * @var string
     * @access private
     */
    private $username;

    /**
     * El id del usuario den la base de datos
     *
     * @var int
     * @access private
     */
    private $id_user;

    /**
     * El campo en la sesion para el CSRF
     *
     * @var string
     */
    const   KEY_CSRF = "csrf";

    /**
     * El nombre de la session
     *
     * @var string
     */
	const SESSION_NAME = "guachi_session_id";

    /**
     * El id de la sesion
     *
     * @var string
     * @access private
     */
    private $session_id = null;

    /**
     * Sesion persistento o no
     *
     * @var bool
     * @access private
     */
    private $session_persistent;

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
            session_name(self::SESSION_NAME);
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
     * key
     * Inicia/Cierra la sesion
     *  parametros supportados:
     * * `exit` Destruye toda la sesion.
     * * `login` Inicia la sesion.
     * 
     * @param string $mode
     *
     * @author Máster Vitronic
     * @return bool
     * @access private
     */
    private function key($mode) {
        switch ($mode) {
            case 'exit':
                unset($_COOKIE[self::SESSION_NAME]);
                session_regenerate_id(true);
                session_unset();
                return session_destroy();
            case 'login':
                $_SESSION = [
                    'username'        => $this->username,
                    'id_user'         => $this->id_user,
                    'session_timeout' => is_true($this->session_persistent) ? false : (time() + session_timeout),
                    'ip_address'      => get_ip()
                ];
                $this->session_id     = session_id();
                setcookie(self::SESSION_NAME,$this->session_id,null,"/", "", is_true(enforce_https), true);
                $_COOKIE[self::SESSION_NAME] = $this->session_id;
                return true;
        }
        return false;
    }

    /**
     * logIn
     * Valida/verifica los credenciales
     *
     * @param string $username
     * @param string $password
     *
     * @author Máster Vitronic
     * @return bool
     * @access public
     */
    public function logIn($username, $password) {
        $query    = 'select id_user,status,password,session_persistent '
                    .'from users where username=%s and status=%s';
        $resource = $this->db->query($query,$username,'t');
        $results  = $this->db->get_row($resource);
        if(isset($results->id_user)){
            if(password_verify($password , $results->password)){
                $this->username            = $username;
                $this->id_user             = $results->id_user;
                $this->session_persistent  = $results->session_persistent;
                return $this->key('login');
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
     * hashPass
     * Crea un password
     * 
     * @param string $password
     *
     * @author Máster Vitronic
     * @return string
     * @access public
     */
    public function hashPass($password) {
        return  password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * logOut
     * Cierra la sesion actual
     * 
     *
     * @author Máster Vitronic
     * @return bool
     * @access public
     */
    public function logOut() {
        return $this->key('exit') == true ? true : false;
    }

    /**
     * sessionIsValid
     * Verifica que la sesion actual sea valida y esta vigente
     * 
     *
     * @author Máster Vitronic
     * @return bool
     * @access private
     */
    private function sessionIsValid() {
        if( isset($_SESSION['id_user']) ){
            if( $_SESSION['session_timeout'] === false ){
                return true;
            }
            if( $_SESSION['session_timeout'] > time() ){
                $_SESSION['session_timeout'] = (time() + session_timeout);
                return true;
            }
            $this->logOut();
        }
        return false;
    }

    /**
     * isLogged
     * Retornara true en caso que este logeado o false en caso contrario
     * 
     *
     * @author Máster Vitronic
     * @return bool
     * @access public
     */
    public function isLogged() {
       return ($this->sessionIsValid()) ? true : false ;
    }

    /**
     * setCsrf
     * Establece un nuevo valor ser usado como CSRF
     * 
     *
     * @author Máster Vitronic
     * @return string
     * @access public
     */
    public function setCsrf() {
        $_SESSION[self::KEY_CSRF] = hash("sha256", random_string(64));
    }

    /**
     * getCsrf
     * Retorna el CSRF actual
     * 
     *
     * @author Máster Vitronic
     * @return string
     * @access public
     */
    public function getCsrf() {
        return $_SESSION[self::KEY_CSRF];
    }
    
    /**
     * csrfIsValid
     * Valida/Verifica el CSRF
     *
     * @param string $csrf
     *
     * @author Máster Vitronic
     * @return bool
     * @access public
     */
    public function csrfIsValid($csrf) {
        return hash_equals($this->getCsrf(), $csrf);
    }

}
