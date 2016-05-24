<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initEnv(Yaf_Dispatcher $dispatcher)
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        
    }

    public function _initDb()
    {
        $conf = Yaf_Application::app()->getConfig();
        Db::setDefaultConfig($conf->get('db'));
    }

    
    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
    
    }
}
