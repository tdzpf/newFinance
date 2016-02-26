<?php
/**
 *
 */

/**
 * Define
 */
defined('TW_DIR') || define('TW_DIR', dirname(__FILE__));
defined('LIBS_DIR') || define('LIBS_DIR', dirname(TW_DIR));
defined('NAME_API_PHP') || define('NAME_API_PHP', '/usr/local/zk_agent/names/nameapi.php');
defined('TW_DS') || define('TW_DS', DIRECTORY_SEPARATOR);

require_once TW_DIR . '/Config.php';

class Tw
{
    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Tw_Dispatcher
     */
    protected static $_instance = null;

    /**
     * Run time config
     *
     * @var Tw_Config
     */
    public static $config;

    /**
     * Object register
     *
     * @var array
     */
    protected static $_reg = array();

    /**
     * Router
     *
     * @var Tw_Router
     */
    protected $_router;

    /**
     * Path info
     *
     * @var string
     */
    protected $_pathInfo = null;

    /**
     * Dispathc info
     *
     * @var array
     */
    protected $_dispatchInfo = null;

    /**
     * Constructor
     *
     */
    protected function __construct()
    {
        $config['_class'] = array(
            'Tw_Router'      => TW_DIR . '/Router.php',
            'Tw_Model'       => TW_DIR . '/Model.php',
            'Tw_View'        => TW_DIR . '/View.php',
            'Tw_Controller'  => TW_DIR . '/Controller.php',
            'Tw_Request'     => TW_DIR . '/Request.php',
            'Tw_Response'    => TW_DIR . '/Response.php',
            'Tw_Mysql'       => TW_DIR . '/Mysql.php',
            'Tw_Mc'          => TW_DIR . '/Mc.php',
            'Tw_Itil'        => TW_DIR . '/Itil.php',
            'Tw_Boss'        => TW_DIR . '/Boss.php',
            'Smarty'         => LIBS_DIR . '/Smarty_2.6.26/Smarty.class.php',
        );

        self::$config = new Tw_Config($config);

        Tw::registerAutoload();
    }

    /**
     * Bootstrap
     *
     * @param mixed $arg string as a file and array as config
     * @return Tw
     */
    public static function boot($conf= '../configs/init.php')
    {
     if (is_string($conf)) {
            include $conf;
        }
        if (!is_array($config)) {
            throw new Exception('Boot config must be an array, if you use config file, the variable should be named $config');
        }

        self::$config->merge($config);
        return self::$_instance;
    }

    /**
     * Singleton instance
     *
     * @return Tw
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Get Config
     *
     * @param string $name
     * @param mixed $default
     * @param mixed $delimiter
     * @return mixed
     */
    public static function getConfig($name = null, $default = null, $delimiter = '.')
    {
        return self::$config->get($name, $default, $delimiter);
    }

    /**
     * Register
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function reg($name = null, &$value = null, $default = null)
    {
        if (null === $name) {
            return self::$_reg;
        }

        if (null === $value) {
            return isset(self::$_reg[$name]) ? self::$_reg[$name] : $default;
        }

        self::$_reg[$name] = $value;
        return self::$_instance;
    }

    /**
     * Name service
     *
     * @param $alias string
     *
     * @return array
     */
    public static function getHostByKey($key) {
        if ($return = Tw::reg($key)) {
            return $return;
        }

        require_once NAME_API_PHP;
        $host = new ZkHost;
        getHostByKey($key , $host);
        $return = array('host' => $host->ip, 'port' => $host->port);
        Tw::reg($key, $return);
        return $return;
    }

    /**
     * Load class
     *
     * @param string $className
     * @param string $dir
     * @return boolean
     */
    public static function loadClass($className, $dir = '')
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }
        $class = self::getConfig('_class');
        if (isset($class[$className])) {
            include $class[$className];
            return true;
        }
        $dir = rtrim($dir,'\\/') . TW_DS;

        $file = str_replace('_', TW_DS, $className) . '.php';
        $classFile = $dir . $file;
        if (file_exists($classFile)) {
            include $classFile;
        }

        return (class_exists($className, false) || interface_exists($className, false));
    }

    /**
     * User define class path
     *
     * @param array $classPath
     * @return Tw
     */
    public static function setClassPath($class, $path = '')
    {
        if (is_array($class)) {
            self::$config['_class'] = $class + self::$config['_class'];
        } else {
            self::$config['_class'][$class] = $path;
        }

        return self::$_instance;
    }

    /**
     * Register autoload function
     *
     * @param string $func
     * @param boolean $enable
     */
    public static function registerAutoload($func = 'Tw::loadClass', $enable = true)
    {
        $enable ? spl_autoload_register($func) : spl_autoload_unregister($func);
    }

    /**
     * Set router
     *
     * @param Tw_Router $router
     * @return Tw
     */
    public function setRouter($router = null)
    {
        if (null === $router) {
            $router = Tw_Router::getInstance();
        }

        $this->_router = $router;

        return $this;
    }

    /**
     * Get router
     *
     * @return Tw_Router
     */
    public function getRouter()
    {
        if (null === $this->_router) {
            $this->setRouter();
        }

        return $this->_router;
    }

    /**
     * Set path info
     *
     * @param string $pathinfo
     * @return Tw
     */
    public function setPathInfo($pathinfo = null)
    {
        if (null === $pathinfo) {
            $pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        }

        $this->_pathInfo = $pathinfo;

        return $this;
    }

    /**
     * Get path info
     *
     * @return string
     */
    public function getPathInfo()
    {
        if (null === $this->_pathInfo) {
            $this->setPathInfo();
        }

        return $this->_pathInfo;
    }

    /**
     * Set dispatch info
     *
     * @param array $dispatchInfo
     * @return Tw
     */
    public function setDispatchInfo($dispatchInfo = null)
    {
        if (null === $dispatchInfo) {
            $router = $this->getRouter();
            // add urls to router from config
            $urls = self::getConfig('_urls');
            if (!empty($urls)) {
                $router->add($urls, false);
            }
            $pathInfo = $this->getPathInfo();
            $dispatchInfo = $router->match($pathInfo);
        }

        $this->_dispatchInfo = $dispatchInfo;

        return $this;
    }

    /**
     * Get dispatch info
     *
     * @return array
     */
    public function getDispatchInfo()
    {
        if (null === $this->_dispatchInfo) {
            $this->setDispatchInfo();
        }

        return $this->_dispatchInfo;
    }

    /**
     * Dispatch
     *
     */
    public function dispatch()
    {
        if (!$this->getDispatchInfo()) {
            throw new Exception('No dispatch info found');
        }

        if (isset($this->_dispatchInfo['file'])) {
            if (!file_exists($this->_dispatchInfo['file'])) {
                throw new Exception("Can't find dispatch file:{$this->_dispatchInfo['file']}");
            }
            require_once $this->_dispatchInfo['file'];
        }
        if (isset($this->_dispatchInfo['controller'])) {
            if (!self::loadClass($this->_dispatchInfo['controller'], self::$config->get('_controllersHome'))) {
                throw new Exception("Can't load controller:{$this->_dispatchInfo['controller']}");
            }
            $cls = new $this->_dispatchInfo['controller']();
        }

        if (isset($this->_dispatchInfo['action'])) {
            $func = isset($cls) ? array($cls, $this->_dispatchInfo['action']) : $this->_dispatchInfo['action'];
            if (!is_callable($func, true)) {
                throw new Exception("Can't dispatch action:{$this->_dispatchInfo['action']}");
            }
            call_user_func($func);
        }
    }
}
