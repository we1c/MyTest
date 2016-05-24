<?php
require "init.php";

$logo = array('','',
    '   8888   8     8  8    8     8 |  8888   8     8  888888',
    '  8    8  8 8   8  8     8   8  | 8    8  8 8   8  8     ',
    '  8    8  8  8  8  8      8 8   | 8    8  8  8  8  888888',
    '  8    8  8   8 8  8       8    | 8    8  8   8 8  8     ',
    '   8888   8     8  88888   8    |  8888   8     8  888888  1ge.com----pre_goods',
    '','');

echo join("\n", $logo);

$conf = Yaf_Application::app()->getConfig();
$app->execute( 'main', $argv );

function main( $argv )
{
    //Yaf_Registry::set('table' , $argv[1]);
    //mysql2redis();
    redisTimeList();
}

function redisTimeList(){
    $request = buildRequest('GET', '/timelist/mysql1redis1push', array());
    Yaf_Application::app()->getDispatcher()->dispatch($request);
}

function mysql2redis()
{
    $request = buildRequest('GET', '/artron/mysql2redis4err', array());
    Yaf_Application::app()->getDispatcher()->dispatch($request);       
}

function catch_error()
{    
    $time = date('Y-m-d H:i:s');
    $error = error_get_last();
    $msg = "$time [error]";
    
    if($error){
        $msg .= var_export($error,1);
    }
    echo $msg."\r\n";
}

register_shutdown_function("catch_error");
 
function sig_handler($signo)
{
    $time = date('Y-m-d H:i:s');
    if($signo == 14){
        //忽略alarm信号
        echo $time." ignore alarm signo[{$signo}]\r\n";
    }else{
        echo $time." exit  signo[{$signo}]\r\n";
        exit("");
    }
}
/*pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP, "sig_handler");
pcntl_signal(SIGINT, "sig_handler");
pcntl_signal(SIGQUIT, "sig_handler");
pcntl_signal(SIGILL, "sig_handler");
pcntl_signal(SIGPIPE, "sig_handler");
pcntl_signal(SIGALRM, "sig_handler");*/

?>
