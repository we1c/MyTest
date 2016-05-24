<?php
//error_reporting(E_ALL);
// exit('1234');
header( 'Content-type: text/html;charset=utf-8' );
//header( 'Cache-Control: max-age = 86400,must-revalidate' );
//header( 'Last-Modified:'.gmdate('D,d M Y H:i:s').'GMT' );
//header( 'Expires:'.gmdate( 'D, d M Y H:i:s',time() + '86400' ).'GMT' );
define("ROOT_PATH",  realpath(dirname(__FILE__) . '/../../'));
define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));


//error_reporting(E_ERROR);
$app  = new Yaf_Application(ROOT_PATH . "/conf/adm.ini");
$app->bootstrap()->run();