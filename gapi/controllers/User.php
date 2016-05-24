<?php

class UserController extends BaseController
{
    public function init()
    {	
        parent::init();
    }
    
    public function indexAction()
    {
        
    }

    public function regAction(){
        $token = json_decode( Util::getWeichatToken( $this->getQuery('code') , $this->_appId , $this->_appSecret ) , true );
        $state = $this->getQuery('state');
        if(preg_match('/\bfrom=adm_getopenid\b/', $state)){
            preg_match('/\bdevid=(\d+)\b/', $state ,$matches );
            $devid = intval($matches[1]);
            $data = array( 'openId'=> $token['openid'] );
            $res = Service::getInstance('developers')->updateDev( $devid,$data );
            header('Location:'.$state);
            return false;
        }

        $userinfo = Util::checkWeichat($token['access_token'],$token['openid']);
        $res = Service::getInstance('user')->reg($userinfo);
        $this->_setCookie( $token['openid'] );
        header('Location:'.$state);
        return false;
    }
    
    protected function _setCookie($openId, $keep = true)
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = Util::strcode($openId, $key, 'encode');
        $expired = $keep ? time() + 86400 * 30 : 0;
    
        setcookie('openId', $token, $expired, '/');
    }
   
}