<?php

class IndexController extends BaseController
{
    public function init() {	
        if ( ! (bool)Yaf_Registry::get("isLogin") )
        {
            $this->respon( 0 , "请重新登录" );
        }
    }
    
    public function indexAction() {
        exit('欢迎来到这里！！！');
    }
   
}