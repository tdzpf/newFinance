<?php

class Tw_Mc
{
    protected $_conn;
    protected $_options = array(
        'ttl' => 900
    );
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($name)
    {
        if(!extension_loaded('memcached')) {
            throw new Exception('Can not find memcached extension.');
        }
        $this->_conn = new Memcached();
        $servers = $this->_parseConfig($name);
        if (empty($servers) || !$this->_conn->addServers($servers)) {
            throw new Exception('Mc config error');
        }
    }
    /**
     * 解析MC 配置
     *
     * @param $name String
     * @return Array array(array(host, port), ....)
     */
    protected function _parseConfig($name) {
        if (is_array($name)) {
            return $name;
        }

        $cfg = Tw::getConfig($name, array());

        $servers = array();

        if (isset($cfg['name'])) {
            $cfg['name'] = is_array($cfg['name']) ? $cfg['name'] : array($cfg['name']);
            foreach($cfg['name'] as $key) {
                $config = Tw::getHostByKey($key);
                $servers[] = array($config['host'], $config['port']);
            }
        }

        if (isset($cfg['host'])) {
            $hosts = $cfg['host'];
            foreach(explode(',', $hosts) as $host){
                $host = explode(':', $host);
                $servers[] = array($host[0], $host[1]);
            }
        }
        return $servers;
    }

    /**
     * MC 链接
     *
     * @param void
     * @return void
     */
    public function conn() {
        return $this->_conn;
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function set($id, $data, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->_options['ttl'];
        }
        $bret = $this->_conn->set($id, $data, $ttl);
        return $bret;
}

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($id)
    {
        return $this->_conn->get($id);
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        $this->_conn->delete($key);
    }

    /**
     * Increment value
     *
     * @param string $key
     * @param int $value
     */
    public function increment($key, $value = 1)
    {
        $this->_conn->increment($key, $value);
    }

    /**
     * clear cache
     */
    public function clear()
    {
        $this->_conn->flush();
    }


    public function stats()
    {
        return $this->_conn->getStats();
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function __set($key, $value)
    {
        return null === $value ? $this->delete($key) : $this->set($key, $value);
    }

    /**
     * Get cache
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Delete cache
     *
     * @param string $key
     * @return boolean
     */
    public function __unset($key)
    {
        return $this->delete($key);
    }
}
