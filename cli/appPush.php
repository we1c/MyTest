<?php

require('./init.php');

$logo = array('','',
    '   8888   8     8  8    8     8 |  8888   8     8  888888',
    '  8    8  8 8   8  8     8   8  | 8    8  8 8   8  8     ',
    '  8    8  8  8  8  8      8 8   | 8    8  8  8  8  888888',
    '  8    8  8   8 8  8       8    | 8    8  8   8 8  8     ',
    '   8888   8     8  88888   8    |  8888   8     8  888888   1ge.com----pushGoods',
    '','');

echo join("\n",$logo);

$conf = Yaf_Application::app()->getConfig();

$app->execute( 'main', $argv );

function main( $argv ){
	pushMsg();
}

function pushMsg(){
	$request = buildRequest('GET','/apppush/pushmsg',array());
	Yaf_Application::app()->getDispatcher()->dispatch($request);
}