<?php

class ListerrorController extends BaseController{

	public function init(){
		//parent::init();
	}

	public function indexAction(){
		$event = $this->getQuery('event','');
		$file = ROOT_PATH.'/logs/'.$event.'.error.log';
		if( file_exists( $file ) ){
			$content = file_get_contents( $file );
		}else{
			$this->respon( 0, 'no such a file');
		}
		echo $content;
		exit;
	}

	public function delErrorLogAction(){
		$event = $this->getQuery('event','');
		$file = ROOT_PATH.'/logs/'.$event.'.error.log';
		if( file_exists( $file ) ){
			if( fopen( $file , "w+" ) ) echo "<h1>执行成功</h1>";
		}else{
			$this->respon( 0, 'no such a file');
		}
		exit;
	}


	public function errorListAction(){

		$event = $this->getQuery('event','');

		$file  = ROOT_PATH.'/logs/'.$event.'.error.log';

		if( file_exists( $file ) ){
			$content = file_get_contents( $file );
		}else{
			die( 'no such a file' );
		}
		$reg = '/\[.*\]/';
		preg_match_all( $reg, $content ,$match );
		echo "<pre>";
		var_dump($match);
	}

	public function readLogAction(){
		$event = $this->getQuery('event','');
		$file = ROOT_PATH.'/logs/'.$event.'.log';
		if( file_exists( $file ) ){
			$content = file_get_contents($file);
		}else{
			$this->respon( 0, 'no such a file');
		}
		echo "<pre>".$content."<pre>";
		exit;
	}


}