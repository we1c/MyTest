<?php

define("ROOT_PATH",  realpath(dirname(__FILE__) . '/../'));
define("APP_PATH",  realpath(dirname(__FILE__) . '/'));
define("DATA_PATH",  realpath(dirname(__FILE__) . '/../../'));

$app  = new Yaf_Application(ROOT_PATH . "/conf/cli.ini");
$app->bootstrap();

function buildRequest($method = 'GET', $uri = '/', $params = array())
{
    $controller = 'default';
    $action = 'default';
    $parts = preg_split('/\/{1,}/', $uri);
    if (!empty($parts[1])) {
        $controller = strtolower($parts[1]);
    }
    
    if (!empty($parts[2])) {
        $action = $parts[2];
    }
    return new Yaf_Request_Simple($method, "Default", $controller, $action, $params);
}