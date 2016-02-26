<?php
/**
 *
 */

class Tw_Mysql
{
    /**
     * Configuration
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Database connection
     *
     * @var object|resource|null
     */
    protected $_conn = null;

    /**
     * Query handler
     *
     * @var resource
     */
    protected $_query = null;

    /**
     * Debug or not
     *
     * @var boolean
     */
    protected $_debug = false;

    /**
     * Log
     *
     * @var array
     */
    protected $_log = array();

    /**
     * Last query sql
     *
     * @var string
     */
    protected $_lastSql;

    /**
     * Constructor.
     *
     * $config is an array of key/value pairs
     * containing configuration options.  These options are common to most adapters:
     *
     * database       => (string) The name of the database to user
     * user           => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * port           => (string) The port of the database
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * charset        => (string) The charset of the database
     *
     * @param array $config
     */
    public function __construct($name)
    {
        $config =  is_string($name) ? Tw::getConfig($name) : $name;
        $keys = array('name', 'host', 'port', 'user', 'password', 'database', 'persistent', 'charset', 'options');

        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $this->_config[$key] = $config[$key];
            }
        }

        if(empty($this->_config['host']) && isset($this->_config['name'])) {
            $config = Tw::getHostByKey($this->_config['name']);
            $this->_config += $config;
        }
        /**
         * Default config
         *
         * @var array
         */
        $defaults = array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '',
            'database' => 'test',
            'charset' => 'UTF8',
            'persistent' => false,
            'options' => array()
        );

        $this->_config += $defaults;

        $this->connect();
    }

    /**
     * Get db connection
     *
     * @return resource
     */
    public function conn()
    {
        return $this->_conn;
    }

    /**
     * Get query statment
     *
     * @return resource
     */
    public function stmt()
    {
        return $this->_query;
    }

    /**
     * Connect to database
     *
     */
    protected function connect()
    {
        if(null !== $this->_conn) return $this;

        if (!extension_loaded('mysqli')) {
            throw new Exception('NO_MYSQLI_EXTENSION_FOUND');
        }

        if ($this->_config['persistent']) {
            throw new Exception('MYSQLI_EXTENSTION_DOES_NOT_SUPPORT_PERSISTENT_CONNECTION');
        }

        $this->_conn = mysqli_init();

        $connected = @mysqli_real_connect(
            $this->_conn,
            $this->_config['host'],
            $this->_config['user'],
            $this->_config['password'],
            $this->_config['database'],
            $this->_config['port']
        );
        if (false === $connected) {
            throw new Exception($this->error());
        }
        $this->query("SET NAMES '" . $this->_config['charset'] . "';");
    }

    /**
     * Select Database
     *
     * @param string $database
     * @return boolean
     */
    public function selectDb($database)
    {
        return $this->_conn->select_db($database);
    }

    /**
     * Set Debug or not
     *
     * @param boolean $flag
     */
    public function debug($flag = true)
    {
        $this->_debug = $flag;
        return $this;
    }

    /**
     * Get or set log
     *
     * if $msg is null, then will return log
     *
     * @param string $msg
     * @return array|Mysql
     */
    public function log($msg = null)
    {
        if (null === $msg) {
            return $this->_log;
        }

        $this->_log[] = $msg;

        return $this;
    }

    /**
     * Get SQL result
     *
     * @param string $sql
     * @param string $type
     * @return mixed
     */
    public function sql($sql, $type = 'ASSOC')
    {
        $this->query($sql);
        $tags = explode(' ', $sql, 2);
        switch (strtoupper($tags[0])) {
            case 'SELECT':
                ($result = $this->fetchAll($type)) || ($result = array());
                break;
            case 'INSERT':
                $result = $this->lastInsertId();
                break;
            case 'UPDATE':
            case 'DELETE':
                $result = $this->affectedRows();
                break;
            default:
                $result = $this->_query;
        }

        return $result;
    }

    /**
     * Get a result row
     *
     * @param string $sql
     * @param string $type
     * @return array
     */
    public function row($sql, $type = 'ASSOC')
    {
        $this->query($sql);
        return $this->fetch($type);
    }

    /**
     * Get first column of result
     *
     * @param string $sql
     * @return string
     */
    public function col($sql)
    {
        $this->query($sql);
        $result = $this->fetch();
        return empty($result) ? null : current($result);
    }

    /**
     * Insert
     *
     * @param array $data
     * @param string $table
     * @return boolean
     */
    public function insert($data, $table)
    {
        $keys = '';
        $values = '';
        foreach ($data as $key => $value) {
            $keys .= "`$key`,";
            $values .= "'" . $this->escape($value) . "',";
        }
        $sql = "insert into $table (" . substr($keys, 0, -1) . ") values (" . substr($values, 0, -1) . ");";
        return $this->sql($sql);
    }

    /**
     * Update table
     *
     * @param array $data
     * @param string $where
     * @param string $table
     * @return int
     */
    public function update($data, $where = '0', $table)
    {
        $tmp = array();

        foreach ($data as $key => $value) {
            $tmp[] = "`$key`='" . $this->escape($value) . "'";
        }

        $str = implode(',', $tmp);

        $sql = "update $table set " . $str . " where $where";

        return $this->sql($sql);
    }

    /**
     * Delete from table
     *
     * @param string $where
     * @param string $table
     * @return int
     */
    public function delete($where = '0', $table)
    {
        $sql = "delete from $table where $where";
        return $this->sql($sql);
    }


    /**
     * Get last query sql
     *
     * @return string
     */
    public function lastSql()
    {
        return $this->_lastSql;
    }


    /**
     * Query SQL
     *
     * @param string $sql
     * @return Mysqli
     */
    public function query($sql)
    {
        $this->_lastSql = $sql;

        if ($this->_debug) {
            $this->log($sql . '@' . date('Y-m-d H:i:s'));
        }

        if ($this->_query = $this->_conn->query($sql)) {
            return $this->_query;
        }

        $msg = $this->error() . '@' . $sql . '@' . date('Y-m-d H:i:s');

        $this->log($msg);

        throw new Exception($msg);
    }

    /**
     * Return the rows affected of the last sql
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->_conn->affected_rows;
    }

    /**
     * Fetch result
     *
     * @param string $type
     * @return mixed
     */
    public function fetch($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'fetch_assoc';
                break;
            case 'BOTH':
                $func = 'fetch_array';
                break;
            case 'OBJECT':
                $func = 'fetch_object';
                break;
            default:
                $func = 'fetch_assoc';
        }

        return $this->_query->$func();
    }

    /**
     * Fetch all results
     *
     * @param string $type
     * @return mixed
     */
    public function fetchAll($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'fetch_assoc';
                break;
            case 'BOTH':
                $func = 'fetch_array';
                break;
            case 'OBJECT':
                $func = 'fetch_object';
                break;
            default:
                $func = 'fetch_assoc';
        }

        $result = array();
        while ($row = $this->_query->$func()) {
            $result[] = $row;
        }
        $this->_query->free();
        return $result;
    }

    /**
     * Get last insert id
     *
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->_conn->insert_id;
    }

    /**
     * Begin transaction
     *
     */
    public function beginTransaction()
    {
        $this->_conn->autocommit(false);
    }

    /**
     * Commit transaction
     *
     */
    public function commit()
    {
        $this->_conn->commit();
        $this->_conn->autocommit(true);
    }

    /**
     * Rollback
     *
     */
    public function rollBack()
    {
        $this->_conn->rollback();
        $this->_conn->autocommit(true);
    }

    /**
     * Escape string
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        if($this->_conn) {
            return  $this->_conn->real_escape_string($str);
        }else{
            return mysql_escape_string($str);
        }
    }

    /**
     * Get error
     *
     * @return string|array
     */
    public function error($type = 'STRING')
    {
        $type = strtoupper($type);

        if ($this->_conn) {
            $errno = $this->_conn->errno;
            $error = $this->_conn->error;
        } else {
            $errno = mysqli_connect_errno();
            $error = mysqli_connect_error();
        }

        if ('ARRAY' == $type) {
            return array('code' => $errno, 'msg' => $error);
        }
        return $errno . ':' . $error;
    }

    /**
     * Close db connection
     *
     */
    public function close()
    {
        $this->_conn->close();
    }

    /**
     * Free query result
     *
     */
    public function free()
    {
        if ($this->_query) $this->_query->free();
    }

}
