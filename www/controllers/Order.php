<?php

class OrderController extends BaseController
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
    
    //列表
    public function listAction()
    {
        $user = Yaf_Registry::get('user');
        $role = Yaf_Registry::get('role');
        $list = Service::getInstance('orders')->getList($user['shopId'],$role);
        $this->_view->list = $list;
    }
    
    //发送
    public function sendAction() {
        $id = $this->getPost('id');
        $res = Service::getInstance('orders')->sendGoods($id);
        if($res) {
            $this->respon(1,$id);
        } else {
            $this->respon(0,'发货失败');
        }
    }
    //完成
    public function sendfinishAction() {
        $id = $this->getPost('id');
        $res = Service::getInstance('orders')->sendFinish($id);
        if($res) {
            $this->respon(1,$id);
        } else {
            $this->respon(0,'完成失败');
        }
    }
}