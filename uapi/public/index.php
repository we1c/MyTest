<?php
//error_reporting(E_ALL);
// exit('1234');
header("Content-type:text/html;charset=utf-8");
header("Access-Control-Allow-Origin:*");
define("ROOT_PATH",  realpath(dirname(__FILE__) . '/../../'));
define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));
//error_reporting(E_ERROR);

$app  = new Yaf_Application(ROOT_PATH . "/conf/api.ini");
$app->bootstrap()->run();