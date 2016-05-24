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
        if ($uri == '/' || $uri == '/index.php') {
            //
        } else if ($uri=='/developer/signin' || $uri=='/goodsdetails/index'){
            //
        } else {
            $view->setLayout('common.layout');
        }

        $dispatcher->setView($view);
    }

    public function _initDb()
    {
        $conf = Yaf_Application::app()->getConfig();
        Db::setDefaultConfig($conf->get('db'));
    }

    public function _initRedis()
    {
        $conf = Yaf_Application::app()->getConfig();
        Red::setDefaultConfig( $conf->get('redis') );
    }

//     public function _initMongo()
//     {
//         $conf = Yaf_Application::app()->getConfig();
//         Mon::setDefaultConfig( $conf->get('mongo') );
//     }  

    public function _initDeveloper()
    {
        $token = empty($_COOKIE['token']) ? '' : $_COOKIE['token'];
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');

        if (!$token) {
            return false;
        }

        $token = Util::strcode($token, $key, 'decode');
        if (!$token) {
            return false;
        }

        list($account, $pwd) = explode('|', $token);
        $developer = Service::getInstance('admin')->getInfoByAccount( $account );
        $roles = Service::getInstance('role')->getRoleByUser($developer['id']);
        $role = array();
        foreach ($roles as $row) {
            $role[] = $row['role_id'];
        }
        $developer['role'] = $role;
        if ($pwd !== $developer['pwd']) {
            return false;
        }
        Yaf_Registry::set('developer', $developer);
        Yaf_Registry::set('PublicUser', array('Name'=>$developer['name']) );
        Yaf_Registry::set('uid', $developer['id']);
    }

    public function _initPlugins(Yaf_Dispatcher $dispatcher)
    {
        $developerPlugin = new DeveloperPlugin();
        $dispatcher->registerPlugin($developerPlugin);
    }
}
