<?php
/**
 * 上报BOSS日志
 *
 * 使用方法
$ip = "192.168.1.1";
$qq = 12345;
$biz = "finance.stock.dpfx";
$op = "login";
$status = 0;
$logid = 119;
$flowid = 345678;
$custom = "custom message from php";
loginit("127.0.0.1", 6578);	//	如果ip和端口有改变，可以初始化，默认是127.0.0.1 和 6578
if(Tw_Boss::send("%s,%d,%s,%s,%d,%d,%d,%s", $ip, $qq, $biz, $op, $status, $logid, $flowid, $custom) < 0)
{
	echo "logprintf failed\n";
}
 */

class Tw_Boss
{
    protected static $_ip = '127.0.0.1';
    protected static $_port = 6578;

    public static function init($ip = '127.0.0.1', $port = 6578)
    {
        self::$_ip = $ip;
        self::$_port = $port;
    }

    public static function send()
    {
    	$num = func_num_args();

    	//7个必填的字段，再加一个格式串
    	if ($num < 8) {
    		return -1;
    	}

    	$args = func_get_args();
    	$log = vsprintf($args[0], array_slice($args, 1));

    	$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    	if ($socket < 0) {
    		return -1;
    	}

    	if (!socket_connect($socket, $this->_ip, $this->_port)) {
    		return -1;
    	}

    	$len = strlen( $log );
    	$ret = socket_write($socket, $log, strlen($log));
    	if ($ret != $len) {
    		socket_close($socket);
    		return -1;
    	}
    	socket_close($socket);

    	return 0;
    }
}
