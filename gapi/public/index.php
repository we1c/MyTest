<?php
header("Content-type:text/html;charset=utf-8");
define("ROOT_PATH",  realpath(dirname(__FILE__) . '/../../'));
define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));

$app  = new Yaf_Application(ROOT_PATH . "/conf/adm.ini");
$app->bootstrap()->run();