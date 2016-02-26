<?php

/**
 * 上报Itil日志
 *
 * 使用方法
$ip = "192.168.1.1";
$qq = 12345;
$moudle = "finance.stock.dpfx";
$command = "login";
$status = 0;
$logid = 479;
$errorCode = 101;
$custom = "custom message from php";
Tw_Itil::loginit("127.0.0.1", 6578);	//	如果ip和端口有改变，可以初始化，默认是127.0.0.1 和 6578
if(Tw_Itil::send("%s,%d,%s,%s,%d,%d,%d,%s", $ip, $qq, $moudle, $command, $status, $logid, $errorCode, $custom) < 0)
{
    echo "send log failed\n";
}
 */

class Tw_Itil
{
    protected static $_ip = '127.0.0.1';
    protected static $_port = 6578;

    public static function init($ip = '127.0.0.1', $port = 6578)
    {
        self::$_ip = $ip;
        self::$_port = $port;
    }
    
    public static function send() {
        $argsNum = func_num_args();
    
        //7个必填的字段，再加一个格式串
        if( $argsNum < 8 ){
            return -1;
        }
        
        $args   = func_get_args();
        $logPkg = vsprintf($args[0], array_slice($args, 1));
        
        $socketHandle = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socketHandle <= 0){
            return -1;
        }
        
        if(!(socket_set_nonblock($socketHandle))){
            socket_close($socketHandle);
            return -1;
        }
        
        //发送请求
        $sendRet = (socket_sendto($socketHandle, 
                                  $logPkg, 
                                  strlen($logPkg), 
                                  0x100, 
                                  self::$_ip, 
                                  self::$_port) != strlen($logPkg));

        
        socket_close($socketHandle);
        if($sendRet != strlen($logPkg)){
            return -1;
        }

        return 0;
    }
}

?>