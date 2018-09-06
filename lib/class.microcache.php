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

	public $patch       = cache_patch;
	public $lifetime    = cache_lifetime;
	public $c_type      = cache_type;
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

	public function check() {
		return $this->is_cached = !$this->cache_on ? false
			: $this->c_type == 'file' ?
				( is_readable($this->file) and is_writable($this->file) and (time()-filemtime($this->file) < $this->lifetime) )
				: $this->is_cached = ( $this->cache_on and $this->memcache->get($this->key) );
                
	}

	public function out() {
            return !$this->is_cached ? '' : $this->c_type == 'file' ? file_get_contents($this->file) : $this->memcache->get($this->key);
	}

	public function start() {
		ob_start();
        print('<!--Served from cache. Stored on '.strftime("%A %d de %B del %Y").' '.date('h:i:s A') . "-->\n");
	}

	public function end() {
        print("<!--End of cache block-->");
		$buffer = ob_get_contents();
		ob_end_clean();
		$this->cache_on and $this->write($buffer);
		unset($buffer);
	}

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

	public function clear($key=false){
		$key = $key === false ? $this->key : md5($key);
		$file = $this->patch . $key . '.cache';
		if ($this->c_type == 'file') @unlink($file);
		else $this->memcache->delete($key);
	}
}
