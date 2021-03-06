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

    public function _initWeichat()
    {
        $conf = Yaf_Application::app()->getConfig();
        Weichat::setDefaultConfig($conf->get('wei'));
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
    
    }
}
