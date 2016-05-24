<?php

class Service
{
    public $db;
    protected static $_instances = array();
  
    public function __construct( )
    {
        $this->db = Db::getInstance( );
        /*$redis = new Redis(); 
        $host = Yaf_Application::app()->getConfig()->get('redis')->get( 'host' );
        $port = Yaf_Application::app()->getConfig()->get('redis')->get( 'port' );
        
        $redis->connect($host,$port); 
        $this->redis = $redis;*/
    }

    public static function getInstance( $name )
    {
        $name = strtolower($name);
        if (!isset(self::$_instances[$name])) {
            $serviceName = 'Service_' . ucfirst($name);
            self::$_instances[$name] = new $serviceName( );
        }

        return self::$_instances[$name];
    }

    public static function touchDb( ){

        if( !empty( self::$_instances ) ){
            foreach( self::$_instances as $name => $db ){
                self::$_instances[$name]->db->query( 'SHOW STATUS;' );
            }
        }
    }

    private function getKey()
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get( 'key' );
        return $key;
    }

    public function setData( $key , $v )
    {
        return ;
        $key = md5( $key . $this->getKey( ) );
        $this->redis->set( $key , json_encode ( $v ) );
    }

    public function getData( $key )
    {
        return ;
        $key = md5( $key . $this->getKey( ) );
        $data = $this->redis->get( $key );
        if ( $data ) $data = json_decode( $data , true );
        return $data;
    }

    public function setAppinfo( $appid , $data )
    {
        $key = md5( $appid . "Oauth2.0");
        $this->redis->set( $key , json_encode ( $data ) );
    }

    public function getAppinfo( $appid )
    {
        $key = md5( $appid . "Oauth2.0" );
        $data = $this->redis->get( $key );
        if ( $data ) $data = json_decode( $data , true );
        return $data;
    }
//     function getError(){
//     	return $this->error;
//     }
    public function error($error = '', $errno = 0)
    {
    	exit(json_encode(array('success'=>$errno,'data'=>$error)));
    }
    public function success($success = '', $no = 0)
    {
    	exit(json_encode(array('success'=>$no,'data'=>$success)));
    }

    static public function p($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    function __destruct(){}
}