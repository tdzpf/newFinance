<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
date_default_timezone_set('Asia/Shanghai');

define('ROOT_DIR', dirname(dirname(__FILE__)));
define('TW_DIR', ROOT_DIR . '/libs/Tw');
define('APPS_DIR', ROOT_DIR . '/demo/apps');

require TW_DIR . '/Tw.php';

/**
 * 错误日志处理
 */
set_error_handler('_errorHandler');
/**
 * 请求清理函数
 */
register_shutdown_function('_shutdown');

$tw = Tw::getInstance();
/**
$router = $tw->getRouter();
$router->enableDynamicMatch(true, array('defaultApp' => ''));
**/

TW::reg('_startTime', microtime(true));

try {
    $ret = $tw->boot()->dispatch();
} catch (Exception $e) {
    /**
     * @todo 统一跳转到错误页面
     * @todo 上报错误日志
     *
     */
}

/**
 * 错误处理
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 * @return boolean
 */

function _errorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
        case E_USER_ERROR:
            // 上报错误日志
            break;

        case E_USER_WARNING:
            // 上报警告日志
            break;

        case E_USER_NOTICE:
            // 上报提示性日志
            break;

        default:
            // 未知错误
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

/**
 * 清理函数
 *
 * 处理致命错误
 * 记录访问时间
 */
function _shutdown()
{
    $startTime = TW::reg('_startTime');
    $error = error_get_last();
    if (isset($error['type']) && E_ERROR == $error['type']) {
        /**
         * 统一错误处理
         */
    }

    /**
     * @todo 统计访问时间
     */
}