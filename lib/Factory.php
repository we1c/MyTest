<?php

class Factory {

    private static $_classPath = array(
        'Api'=>array(
            'channelSdk'=>array(
                'c_s_11'=>'jianlou',
                ),
            ),
        );

    protected static $_instances = array();
  
    public function __construct(){ }

    public static function instance( $name , $path ) {

        $name = strtolower($name);

        if ( !isset( self::$_instances[$name] ) ) {
            $path = trim($path,'/');
            $dirs = explode( '/',$path );
            $pathName = self::$_classPath;
            foreach( $dirs as $dir ){
                $pathName = $pathName[$dir];
            }
            $finalDir = $pathName[$name];
            $serviceName = str_replace('/','_',$path).'_'.$finalDir.'_Dispatch';
            self::$_instances[$name] = new $serviceName();
        }

        return self::$_instances[$name];
    }

    static public function p( $var, $exit = 0) {
        echo '<pre>';
        print_r( $var );
        echo '</pre>';
        if( $exit ) exit;
    }

    static public function vd( $var, $exit = 0) {
        echo '<pre>';
        var_dump( $var );
        echo '</pre>';
        if( $exit ) exit;
    }


    function __destruct() { }
}