<?php

/* ===================================
* Author: Nazarkin Roman
* -----------------------------------
* Contacts:
* email - roman@nazarkin.su
* icq - 642971062
* skype - roman444ik
* -----------------------------------
* GitHub:
* https://github.com/NazarkinRoman
* ===================================
*/

class MicroCache {

    /**
     * La ruta al directorio cache 
     *
     * @var string
     * @access public
     */
	public $patch       = cache_patch;

    /**
     * Segundos en los cuales sera valida la cache
     *
     * @var int
     * @access public
     */
	public $lifetime    = cache_lifetime;

    /**
     * Tipo de cache que se usara
     * *Parametros soportados
     * 'file' cache basada en archivos
     * 'mencache' cache basada en mancache
     *
     * @var string
     * @access public
     */
	public $c_type      = cache_type;

    /**
     * Usar la cache?
     *
     * @var bool
     * @access public
     */
	public $cache_on    = use_cache;
	public $is_cached   = false;
	public $memcache_compressed = false;
	public $file, $key;
	private $memcache;

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
     * init
     *
     * inicia
     *
     * @access public
     *
     */
	public function init($key=false) {
		class_exists('Memcache') or $this->c_type = 'file';
		if($this->c_type != 'file'){
			$this->memcache = new Memcache;
			@$this->memcache->connect('localhost', 11211) or $this->c_type = 'file';
		}
		$this->key = md5( $key===false ? rtrim($_SERVER["REQUEST_URI"],'/') : $key  );
		$this->file = $this->patch . $this->key . '.cache';
	}

    /**
     * check
     * verifica si la cache existe
     *
     *
     * @author Nazarkin Roman
     * @return bool
     * @access public
     */
	public function check() {
		return $this->is_cached = !$this->cache_on ? false
			: $this->c_type == 'file' ?
				( is_readable($this->file) and is_writable($this->file) and (time()-filemtime($this->file) < $this->lifetime) )
				: $this->is_cached = ( $this->cache_on and $this->memcache->get($this->key) );
                
	}

    /**
     * out
     * Escupe la cache 
     *
     *
     * @author Nazarkin Roman
     * @return string
     * @access public
     */
	public function out() {
            return !$this->is_cached ? '' : $this->c_type == 'file' ? file_get_contents($this->file) : $this->memcache->get($this->key);
	}

    /**
     * start
     * Inicializa la grabacion de cache
     *
     *
     * @author Nazarkin Roman
     * @access public
     */
	public function start() {
		ob_start();
        print('<!--Served from cache. Stored on '.strftime("%A %d de %B del %Y").' '.date('h:i:s A') . "-->\n");
	}

    /**
     * end
     * Finaliza la grabacion de la cache
     *
     *
     * @author Nazarkin Roman
     * @access public
     */
	public function end() {
        print("<!--End of cache block-->");
		$buffer = ob_get_contents();
		ob_end_clean();
		$this->cache_on and $this->write($buffer);
		unset($buffer);
	}

    /**
     * write
     * Escribe la cache
     *
     * @param $buffer resorce
     *
     * @author Nazarkin Roman
     * @access public
     */
	public function write($buffer = ''){
		if($this->c_type == 'file') {
			$fp = @fopen($this->file, 'w') or die("No he podido almacenar el archivo : {$this->file}");
			if ( @flock($fp, LOCK_EX)) {
				fwrite($fp, $buffer);
				fflush($fp);
				flock($fp, LOCK_UN);
				fclose($fp);
			}
		} else $this->memcache->set($this->key, $buffer, $this->memcache_compressed, $this->lifetime);
	}

    /**
     * clear
     * Limpia la cache
     *
     * @param string
     * 
     * @author Nazarkin Roman
     * @access public
     */
	public function clear($key=false){
		$key = $key === false ? $this->key : md5($key);
		$file = $this->patch . $key . '.cache';
		if ($this->c_type == 'file') @unlink($file);
		else $this->memcache->delete($key);
	}
}
