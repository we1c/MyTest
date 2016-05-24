<?php

class UserController extends BaseController
{
    public function init()
    {	
        $this->db = Db::getInstance();
        $this->_view->_module = 'index';
        $this->_view->_moduleName = '首页';
        $this->_view->_action = $this->getRequest()->getActionName();
        $this->_view->_controllerName = $this->getRequest()->getControllerName();
    }
    
    public function loginAction()
    {
        if($this->isPost()){
            $account = trim($this->getPost('account'));
            $password = trim($this->getPost('password'));
            if(!$account) {
                $this->respon(0,'请输入用户名');
            }
            if($password === '') {
                $this->respon(0,'请输入密码');
            }
            $user = Service::getInstance('user')->getUserByaccount($account);
            $res = Service::getInstance('user')->doLogin($account,$password);
            if($res === true) {
                $this->_setCookie($user, true);
                $this->respon(1,'登录成功');
            } else {
                $this->respon(0,$res);
            }
        }
        
    }
    
    protected function _setCookie($developer, $keep = true)
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = sprintf('%s|%s', $developer['account'], $developer['pwd']);
        $token = Util::strcode($token, $key, 'encode');
        $expired = $keep ? time() + 86400 * 30 : 0;
    
        setcookie('token', $token, $expired, '/');
    }
   
}