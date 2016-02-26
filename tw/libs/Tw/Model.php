<?php
/**
 *
 */
class Tw_Model
{
    /**
     * Db config name
     *
     * @var string
     */
    protected $_db;

    /**
     * Mc config name
     *
     * @var string
     */
    protected $_mc;

    /**
     * Table name, with prefix and main name
     *
     * @var string
     */
    protected $_table;

    /**
     * Error
     *
     * @var mixed string | array
     */
    protected $_error;

    const UNKNOWN_ERROR = -9;
    const SYSTEM_ERROR = -8;
    const VALIDATE_ERROR = -7;


    /**
     * Get function cache
     *
     * @param string $func
     * @param mixed $args
     * @param int $expire
     * @return mixed
     */
    public function cached($func, $args = null, $expire = 60)
    {
        $key = md5(get_class($this) . $func . serialize($args));

        if (!$data = $this->cache->get($key)) {
            $data = call_user_func_array(array($this, $func), $args);
            $this->cache->set($key, $data, $expire);
        }

        return $data;
    }

    /**
     * Init mc
     *
     * @param mixed $name
     * @return mc
     */
    public function mc($name = null)
    {
        if (empty($name)) {
            $name = $this->_mc;
        }

        if (is_string($name) && !$mc = Tw::reg($name)) {
            $mc = new Tw_Mc($name);
            Tw::reg($name, $mc);
        } else {
        	$mc = new Tw_Mc($name);
        }

        return $mc;
    }

    /**
     * Get SQL result
     *
     * @param string $sql
     * @return array
     */
    public function sql($sql)
    {
        $result = $this->db->sql($sql);
        return $result;
    }

    /**
     * Connect db from config
     *
     * @param array $config
     * @param string $regName
     * @return Tw_Mysql
     */
    public function db($name = null)
    {
        if (empty($name)) {
            $name = $this->_db;
        }

        if (is_string($name) && !$db = Tw::reg($name)) {
            $db = new Tw_Mysql($name);
            Tw::reg($name, $db);
        } else {
            $db = new Tw_Mysql($name);
        }

        return $db;
    }

    /**
     * Set table Name
     *
     * @param string $table
     */
    public function table($table = null)
    {
        if (!is_null($table)) {
            $this->_table = $table;
            return $this;
        }

        return $this->_table;
    }

    /**
     * Get or set error
     *
     * @param mixed $error string|array
     * @return mixed $error string|array
     */
    public function error($error = null)
    {
        if (!is_null($error)) {
            $this->_error = $error;
        }

        return $this->_error;
    }

    /**
     * Instantiated model
     *
     * @param string $name
     * @param string $dir
     * @return Tw_Model
     */
    protected function model($name, $dir = null)
    {
        null === $dir && $dir = Tw::getConfig('_modelsHome');
        $class = ucfirst($name) . 'Model';
        if (Tw::loadClass($class, $dir)) {
            return new $class();
        }

        throw new Exception("Can't load model '$class' from '$dir'");
    }

    /**
     * Dynamic set vars
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value = null)
    {
        $this->$key = $value;
    }

    /**
     * Dynamic get vars
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'db' :
                 $this->db = $this->db();
                   return $this->db;

            case 'mc' :
                $this->mc = $this->mc();
                return $this->mc;

            default:
                throw new Exception('Undefined property: ' . get_class($this). '::' . $key);
        }
    }
}
