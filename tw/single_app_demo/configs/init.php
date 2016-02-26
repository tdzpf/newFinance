<?php
$config['_urls'] = array(
        '/^view\/?(\d+)?$/' => array(
	        'app'	     => 'default',
            'controller' => 'IndexController',
            'action'     => 'viewAction',
            'maps'       => array(
                1 => 'id'
            ),
            'defaults' => array(
                'id' => 9527
            )
        )
);

$configFile = dirname(__FILE__) . "/config.ini";
if(file_exists($configFile)){
	$serviceinfo = parse_ini_file($configFile, true);
	$config = array_merge($config, $serviceinfo);
}