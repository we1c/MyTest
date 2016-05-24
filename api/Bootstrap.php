<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initEnv(Yaf_Dispatcher $dispatcher)
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        $uri = $dispatcher->getRequest()->getRequestUri();
        $view = new StupidView(APP_PATH . '/views');
        $dispatcher->setView($view);
    }

    public function _initDb()
    {
        $conf = Yaf_Application::app()->getConfig();
        Db::setDefaultConfig($conf->get('db'));
    }

//     public function _initRedis()
//     {
//         $conf = Yaf_Application::app()->getConfig();
//         Red::setDefaultConfig($conf->get('redis'));
//     }

//      public function _initUser()
//     {
//         $token = empty($_COOKIE['token']) ? '' : $_COOKIE['token'];
//         $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');

//         if (!$token) {
//             return false;
//         }

//         $token = Util::strcode($token, $key, 'decode');
//         if (!$token) {
//             return false;
//         }

//         list( $account, $pwd ) = explode('|', $token);

//         $user = Service::getInstance('user')->getUserByAccount($account);
//         if ($pwd !== $user['pwd']) {
//             return false;
//         }
//         Yaf_Registry::set('uid', $user['id']);
//         Yaf_Registry::set('user', $user);
//         Yaf_Registry::set('role', $user['role']);
//         Yaf_Registry::set( 'isLogin', true );
//     }
    public function _initUser()
    {
    
        if (empty($_COOKIE['uid'])) {
            return false;
        }
    
        Service::getInstance('user')->checkUser( );
        if ( !Yaf_Registry::get( 'uid' ) ) return false;
    
        Yaf_Registry::set( 'isLogin', true );
    
    }
    
    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
    
    }
}
