<?php

class Api {

	private $_db;
	private static $_defaultConfig = null;
	private static $_instance = array();

	public function __construct( ){
		$this->_db = Db::getInstance( );
	}

	public static function getInstance( $name = 'api_cli',$config = null ){

		//if( !self::$_defaultConfig && !$config ) throw new Api_Exception(' Without any configuration ! ');
		//$config = !$config ? self::$_defaultConfig : $config;

		if( !isset( self::$_instance[$name] ) || !( self::$_instance[$name] instanceof self ) ){
			self::$_instance[$name] = new Api( );
		}
		return self::$_instance[$name];
	}

	public function touchDb( ){
		$this->_db->query('SHOW STATUS;');
	}

	public static function setDefaultConfig( $config ){
		self::$_defaultConfig = $config;
	}

	public function uploadImg( $channel,$gid = 0 ){
		return Factory::instance( 'c_s_'.$channel, 'Api/channelSdk' )->uploadImg( $channel,$gid );
	}

	public function pushGoods( $channel,$gid ){
		return Factory::instance( 'c_s_'.$channel ,'Api/channelSdk' )->pushGoods( $channel,$gid );
	}

	public function downGoodsByGid( $gid ){

		$data = $this->_getPushChannelByGid( $gid );
		if( !$data ) return true;
		$channels = implode( ',',$this->_getCols( $data , 'channel' ) );
		$sql = " SELECT id FROM channel WHERE apiType = 2 AND apiDown = 1 AND id IN ( $channels ) ";
		$channels = $this->_db->fetchAll( $sql );
		if( !empty($channels) ){
			foreach( $channels as $channel ){
				Factory::instance( 'c_s_'.$channel['id'] , 'Api/channelSdk' )
				->downGoods( $channel['id'] ,$gid );
			}
		}
	}

	private function _getPushChannelByGid( $gid ){
		return $this->_db->fetchAll( " SELECT channel FROM push WHERE goodsId = '{$gid}' " );
	}

	private function _getCols( $arr ,$col ){
		$res = array();
		foreach( $arr as $k => $row ){
			$res[] = $row[$col];
		}
		return $res;
	}

	public function downGoodsByChannel( $channel ,$gid ){
		return Factory::instance( 'c_s_'.$channel , 'Api/channelSdk' )->downGoods( $channel ,$gid );
	}

}

class Api_Exception extends Exception {

    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
