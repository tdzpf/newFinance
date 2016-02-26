<?php
/**
 *
 */
class Tw_Router
{
    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Tw_Router
     */
    protected static $_instance = null;

    protected $_enableDynamicMatch = true;

    protected $_dynamicRule = array(
        'defaultApp'         => 'default',
        'defaultController'  => 'IndexController',
        'defaultAction'      => 'indexAction'
    );

    /**
     * Router rules
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * Constructor
     *
     */
    protected function __construct()
    {

    }

    /**
     * Singleton instance
     *
     * @return Tw_Router
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Get rules
     *
     * @param string $regex
     * @return array
     */
    public function rules($regex = null)
    {
        if (null === $regex) return $this->_rules;
        return isset($this->_rules[$regex]) ? $this->_rules[$regex] : null;
    }

    /**
     * Add rule
     *
     * @param array $rule
     * @param boolean $overwrite
     */
    public function add($rules, $overwrite = true)
    {
        $rules = (array) $rules;
        if ($overwrite) {
            $this->_rules = $rules + $this->_rules;
        } else {
            $this->_rules += $rules;
        }

        return $this;
    }

    /**
     * Remove rule
     *
     * @param string $regex
     */
    public function remove($regex)
    {
        unset($this->_rules[$regex]);
        return $this;
    }

    /**
     * Enable or disable dynamic match
     *
     * @param boolean $flag
     * @param array $opts
     * @return Tw_Router
     */
    public function enableDynamicMatch($flag = true, $opts = array())
    {
        $this->_enableDynamicMatch = true;

        $this->_dynamicRule = $opts + $this->_dynamicRule;

        return $this;
    }

    /**
     * Dynamic Match
     *
     * @param string $pathInfo
     * @return array $dispatchInfo
     */
    protected function _dynamicMatch($pathInfo)
    {
        $dispatchInfo = array();
        if (!preg_match('/^(\w+\/?){0,3}$/i', $pathInfo)) {
            return false;
        }
        $tokens      = explode('/', $pathInfo);
        $tokens      = array_reverse($tokens);
        $app         = empty($tokens[2]) ? $this->_dynamicRule['defaultApp'] : $tokens[2];
        $controller  = empty($tokens[1]) ? $this->_dynamicRule['defaultController'] : (ucfirst($tokens[1]) . 'Controller');
        $action      = empty($tokens[0]) ? $this->_dynamicRule['defaultAction'] : ($tokens[0] . 'Action');

        $this->setApp($app);
        $dispatchInfo['app']         = $app;
        $dispatchInfo['controller']  = $controller;
        $dispatchInfo['action']      = $action;

        return $dispatchInfo;
    }

    /**
     * set apps base path
     *
     * @param $app string
     * @return void
     */
    protected function setApp ($app){
        $appDir = rtrim(APPS_DIR . '/' . $app, '/');
        $apps = array('_controllersHome' => "{$appDir}/controllers",
                      '_modelsHome'      => "{$appDir}/models",
                      '_viewsHome'       => "{$appDir}/views");
        foreach ($apps as $k => $v) {
            Tw::$config->set($k, Tw::$config->get($k, $v));
        }
    }

    /**
     * Match path
     *
     * @param string $path
     * @return boolean
     */
    public function match($pathInfo = null)
    {
        $pathInfo = trim($pathInfo, '/');

        foreach ($this->_rules as $regex => $rule) {

            $res = preg_match($regex, $pathInfo, $values);

            if (0 === $res) continue;

            $rule['app'] = empty($rule['app']) ? $this->_dynamicRule['defaultApp']: $rule['app'];
            $this->setApp($rule['app']);
            
			if (isset($rule['maps']) && count($rule['maps'])) {
                $params = array();

                foreach ($rule['maps'] as $pos => $key) {
                    if (isset($values[$pos]) && '' !== $values[$pos]) {
                        $params[$key] = urldecode($values[$pos]);
                    }
                }

                if (isset($rule['defaults'])) $params += $rule['defaults'];

                Tw::reg('_params', $params);
            }
            return $rule;

        }
        if ($this->_enableDynamicMatch) {
                return $this->_dynamicMatch($pathInfo);
        }

        return false;

    }
}
