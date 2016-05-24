<?php

require('./init.php');

$logo = array('','',
    '   8888   8     8  8    8     8 |  8888   8     8  888888',
    '  8    8  8 8   8  8     8   8  | 8    8  8 8   8  8     ',
    '  8    8  8  8  8  8      8 8   | 8    8  8  8  8  888888',
    '  8    8  8   8 8  8       8    | 8    8  8   8 8  8     ',
    '   8888   8     8  88888   8    |  8888   8     8  888888   1ge.com----daemon',
    '','');

echo join("\n",$logo);

$config = Yaf_Application::app()->getConfig()->toArray();

$app->execute( 'main', $config['process'] );

function main( $proConfig ){
	daemon( $proConfig );
}

function daemon( $config ){

	$file_md5 = '';
	$confPath = ROOT_PATH.'/conf/cli.ini';
	if( file_exists( $confPath ) ){
		$file_md5 = md5_file( $confPath );
	}

	while ( true ) {
		
		/*$md5 = checkFileMd5( $file_md5 );
		if( $md5 ){
			$file_md5 = $md5;
			restartProcess( $config );
		}*/

		checkProcess( $config );

		sleep(1);
	}
}

function checkFileMd5( $file_md5 ){

	$confPath = ROOT_PATH.'/conf/cli.ini';

	if( $file_md5 && file_exists($confPath) ){
		$new_md5 = md5_file($confPath);
		if( $new_md5 != $file_md5 ) return $new_md5;
	}

	return false;
}

function checkProcess( $config ){
	//var_dump($config);
	foreach( $config as $process => $info ){
		echo $process.PHP_EOL;
		$num = getProcessNum( $process );

		if( $num < $info['num'] ){
			$less = $info['num'] - $num;
			startProcess( $process ,$less );
		}
	}
	return true;

}

function getProcessNum( $process ){

	$cmd = ' ps ax | grep '.$process.'.php';
	$result = shell_exec($cmd);

	$result = explode("\n", $result);
	var_dump($result);
	$num = 0;
	foreach( $result as $row ){
		if( preg_match('/\s+php\s+/', $row ) ){
			$num++;
		}
	}echo "get '{$process}' : '{$num}' ".PHP_EOL;
	return $num;
}

function startProcess( $process,$less = 0 ){

	$file = ROOT_PATH.'/cli/'.$process.'.php';

	$cmd = 'nohup php '.$file.' >> '.ROOT_PATH.'/logs/cli.log & '.PHP_EOL.PHP_EOL;
	
	for( $i = 0 ; $i < $less ; $i++ ){
		shell_exec( $cmd.PHP_EOL );
	}

}