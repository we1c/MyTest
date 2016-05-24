<?php
require "init.php";

$logo = array('','',
    '   8888   8     8  8    8     8 |  8888   8     8  888888',
    '  8    8  8 8   8  8     8   8  | 8    8  8 8   8  8     ',
    '  8    8  8  8  8  8      8 8   | 8    8  8  8  8  888888',
    '  8    8  8   8 8  8       8    | 8    8  8   8 8  8     ',
    '   8888   8     8  88888   8    |  8888   8     8  888888',
    '','');

echo join("\n", $logo);

$conf = Yaf_Application::app()->getConfig();
$app->execute( 'main', $argv );

function main( $argv ){
    //updateShopNumDay();
    setShopScore();
}

function updateShopNumDay(){
    $request = buildRequest('GET', '/updateshop/updateNumDay', array());
    Yaf_Application::app()->getDispatcher()->dispatch($request); 
}

function setShopScore(){
    $request = buildRequest('GET', '/updateshop/setShopScore', array());
    Yaf_Application::app()->getDispatcher()->dispatch($request); 
}


?>