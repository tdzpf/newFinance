<?php
/**
 *
 */
class Tw_Controller
{
    /**
     * The home directory of model
     *
     * @var string
     */
    protected $_modelsHome = null;

    /**
     * The home directory of view
     *
     * @var string
     */
    protected $_viewsHome = null;

    /**
     * Template file extension
     *
     * @var string
     */
    protected $_tplExt = '.php';

    /**
     * Error
     *
     * @var array
     */
    protected $_error;

    /**
     * Constructor
     *
     * Init $_modelsHome & $_viewsHome from config if they are null
     */
    public function __construct()
    {
        if (null === $this->_modelsHome) {
            $this->_modelsHome = Tw::getConfig('_modelsHome');
        }
        if (null === $this->_viewsHome) {
            $this->_viewsHome = Tw::getConfig('_viewsHome');
        }
    }

    /**
     * Magic method
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($methodName, $args)
    {
        throw new Exception("Call to undefined method: Tw_Controller::$methodName()");
    }

    /**
     * View
     *
     * @param array $config
     * @return Tw_View
     */
    protected function view($params = array())
    {
        $params = (array)$params + array('viewsHome' => $this->_viewsHome) + (array) Tw::getConfig('_view');

        return $this->view = new Tw_View($params);
    }
    /**
     * Display the view
     *
     * @param string $tpl
     */
    protected function json($data, $var = null)
    {
        if ($var) {
            echo $var, "=";
        }
        echo json_encode($data);
    }

    /**
     * Display the view
     *
     * @param string $tpl
     */
    protected function display($tpl = null, $dir = null)
    {
        if (empty($tpl)) {
            $tpl = $this->defaultTemplate();
        }

        $this->view->display($tpl, $dir);
    }

    /**
     * Smarty Handle
     *
     * @param string $key 
     * @return void
     */
    protected function smarty($key = 'smarty')
    {
        // 获得默认模版目录
        $tw = Tw::getInstance();
        $dispatchInfo = $tw->getDispatchInfo();
        $dir = Tw::getConfig('_viewsHome')
             . DIRECTORY_SEPARATOR
             . str_replace('_', DIRECTORY_SEPARATOR, substr($dispatchInfo['controller'], 0, -10));

        $config = Tw::getConfig($key, array()) + array('template_dir' => $dir);
        $smarty = new Smarty();
        foreach($config as $key => $value) {
            $smarty->$key = $value;
        }
        return $smarty;
    }

    /**
     * Get default template file path
     *
     * @return string
     */
    protected function defaultTemplate()
    {
        $tw = Tw::getInstance();
        $dispatchInfo = $tw->getDispatchInfo();

        $tpl = str_replace('_', DIRECTORY_SEPARATOR, substr($dispatchInfo['controller'], 0, -10))
             . DIRECTORY_SEPARATOR
             . substr($dispatchInfo['action'], 0, -6)
             . $this->_tplExt;

        return $tpl;
    }

    /**
     * Instantiated model
     *
     * @param string $name
     * @param string $dir
     * @return Tw_Model
     */
    protected function model($name = null, $dir = null)
    {
        if (null === $name) {
            return $this->model;
        }

        null === $dir && $dir = $this->_modelsHome;
        $class = ucfirst($name) . 'Model';
        if (Tw::loadClass($class, $dir)) {
            return new $class();
        }

        throw new exception("Can't load model '$class' from '$dir'");
    }

    /**
     * Set model home directory
     *
     * @param string $dir
     * @return Tw_Controller
     */
    protected function setModelsHome($dir)
    {
        $this->_modelsHome = $dir;
        return $this;
    }

    /**
     * Set view home directory
     *
     * @param string $dir
     * @return Tw_Controller
     */
    protected function setViewsHome($dir)
    {
        $this->_viewsHome = $dir;
        return $this;
    }


    /**
     * Post var
     *
     * @param string $key
     * @param mixed $default
     */
    protected function post($key = null, $default = null)
    {
        return $this->request->post($key, $default);
    }

    /**
     * Get var
     *
     * @param string $key
     * @param mixed $default
     */
    protected function get($key = null, $default = null)
    {
        return $this->request->get($key, $default);
    }

    /**
     * Redirect to other url
     *
     * @param string $url
     */
    protected function redirect($url, $code = 302)
    {
        $this->response->redirect($url, $code);
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
            case 'view':
                return $this->view();

            case 'request':
                $this->request = new Tw_Request();
                return $this->request;

            case 'response':
                $this->response = new Tw_Response();
                return $this->response;

            case 'smarty':
                $this->smarty = $this->smarty();
                return $this->smarty;

            default:
                throw new Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
    }
}
