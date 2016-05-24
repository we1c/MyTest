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

    public function _initRedis()
    {
        $conf = Yaf_Application::app()->getConfig();
        Red::setDefaultConfig($conf->get('redis'));
    }

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

        list( $account, $pwd ) = explode('|', $token);

        $user = Service::getInstance('user')->getUserByAccount($account);
        if ($pwd !== $user['pwd']) {
            return false;
        }
        Yaf_Registry::set('uid', $user['id']);
        Yaf_Registry::set('user', $user);
        Yaf_Registry::set('role', $user['role']);
    }

    public function _initPlugins(Yaf_Dispatcher $dispatcher)
    {
        $userPlugin = new UserPlugin();
        $dispatcher->registerPlugin($userPlugin);
    }
}
