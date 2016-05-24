<?php

class RecomController extends BaseController
{
    public function init()
    {	
        $this->db = Db::getInstance();
        $user = Yaf_Registry::get('user');
        $this->_view->user = $user;
        $this->_view->_module = 'index';
        $this->_view->_moduleName = '首页';
        $this->_view->_action = $this->getRequest()->getActionName();
        $this->_view->_controllerName = $this->getRequest()->getControllerName();
        $token = empty($_COOKIE['color_'.$user['id']]) ? '' : $_COOKIE['color_'.$user['id']];
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = Util::strcode($token, $key, 'decode');
        $this->_view->color = $token;
    }
    
    public function listAction()
    {
        $user = Yaf_Registry::get('user');
        $role = Yaf_Registry::get('role');
        $list = Service::getInstance('goods')->getRecommend($user['shopId'],$role);
        $this->_view->list = $list;
    }
   
   
}