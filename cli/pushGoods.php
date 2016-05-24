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
	pushGoodsByChannel();
}

function pushGoodsByChannel(){
	$request = buildRequest('GET','/pushgoods/pushgoodsbychannel',array());
	Yaf_Application::app()->getDispatcher()->dispatch($request);
}