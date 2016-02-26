<?php
/**
 *
 */

class Tw_View
{
    /**
     * Base path of views
     *
     * @var string
     */
    protected $_viewsHome = '';

    /**
     * Constructor
     *
     */
    public function __construct($params = array())
    {
        if (isset($params['viewsHome'])) {
            $this->_viewsHome = $params['viewsHome'];
        }
    }

    /**
     * Set base path of views
     *
     * @param string $path
     */
    public function setViewsHome($path)
    {
        $this->_viewsHome = $path;
    }

    /**
     * Get base path of views
     *
     * @return string
     */
    public function getViewsHome()
    {
        return $this->_viewsHome;
    }

    /**
     * Render view
     *
     */
    protected function _render($tpl, $dir = null)
    {
        if (null === $dir) $dir = $this->_viewsHome;
        if ($dir) $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        ob_start();
        ob_implicit_flush(0);
        $file = $dir . $tpl;
        include $file;
        return ob_get_clean();
    }

    /**
     * Fetch
     *
     * @param string $tpl
     * @param string $dir
     * @return string
     */
    public function fetch($tpl, $dir = null)
    {
        return $this->_render($tpl, $dir);
    }


    /**
     * Display
     *
     * @param string $tpl
     * @param string $dir
     */
    public function display($tpl, $dir = null)
    {
        echo $this->_render($tpl, $dir);
    }

    /**
     * Escape
     *
     * @param string $str
     * @param string $type
     * @param string $encoding
     * @return string
     */
    public static function escape($str, $type = 'html', $encoding = 'UTF-8')
    {
        switch ($type) {
            case 'html':
                return htmlspecialchars($str, ENT_QUOTES, $encoding);

            case 'htmlall':
                return htmlentities($str, ENT_QUOTES, $encoding);

            case 'javascript':
                return strtr($str, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));

            case 'mail':
                return str_replace(array('@', '.'),array(' [AT] ', ' [DOT] '), $str);

            default:
                return $str;
        }
    }

    /**
     * Truncate
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @param string $regex
     * @return string
     */
    public static function truncate($str, $limit, $encoding = 'UTF-8', $suffix = '...', $regex = null)
    {
        if (function_exists('mb_strwidth')) {
            return  self::mbTruncate($str, $limit, $encoding, $suffix);
        }
        return self::regexTruncate($str, $limit, $encoding, $suffix, $regex = null);
    }

    /**
     * Truncate with mbstring
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @return string
     */
    public static function mbTruncate($str, $limit, $encoding = 'UTF-8', $suffix = '...')
    {
        if (mb_strwidth($str, $encoding) <= $limit) return $str;

        $limit -= mb_strwidth($suffix, $encoding);
        $tmp = mb_strimwidth($str, 0, $limit, '', $encoding);
        return $tmp . $suffix;
    }

    /**
     * Truncate with regex
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @param string $regex
     * @return string
     */
    public static function regexTruncate($str, $limit, $encoding = 'UTF-8', $suffix = '...', $regex = null)
    {
        $defaultRegex = array(
            'UTF-8'  => "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/",
            'GB2312' => "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/",
            'GBK'    => "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/",
            'BIG5'   => "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/"
        );

        $encoding = strtoupper($encoding);

        if (null === $regex && !isset($defaultRegex[$encoding])) {
            throw new Exception("Truncate failed: not supported encoding, you should supply a regex for $encoding encoding");
        }

        $regex || $regex = $defaultRegex[$encoding];

        preg_match_all($regex, $str, $match);

        $trueLimit = $limit - strlen($suffix);
        $len = $pos = 0;

        foreach ($match[0] as $word) {
            $len += strlen($word) > 1 ? 2 : 1;
            if ($len > $trueLimit) continue;
            $pos ++;
        }
        if ($len <= $limit) return $str;
        return join("",array_slice($match[0], 0, $pos)) . $suffix;
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
        return null;
    }
}
