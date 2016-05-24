<?php

class DevController extends BaseController
{
    public function init()
    {
        parent::init();
        if(!$this->_developer) {
            $this->redirect('/developer/signin');
    	    exit;
        }
        $this->_view->_module = 'dev';        
        $this->_view->_moduleName = '后台管理';        
    }

    public function indexAction()
    {   
        session_start();
        $hour = date('H');
        if( 0 <= $hour && $hour <=12 ){
            $bucket = '上午';
        }else if( 12 <= $hour && $hour <= 18  ){
            $bucket = '下午';
        }else{
            $bucket = '晚上';
        }
        $lastTime = $_SESSION['lastTime'];
        if( $lastTime == 0 ){
            $this->_view->loginTime = time();
            $this->_view->showTime = 'now';
        }else{
            $this->_view->lastTime = $lastTime;
        }
        $this->_view->loginIp = $this->_developer['loginIp'];
        $this->_view->bucket = $bucket;
    }

/*function getLocalTime(nS) {     
   return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');     
}
$("#payTime").val(getLocalTime(result.data['payTime']));*/
}
