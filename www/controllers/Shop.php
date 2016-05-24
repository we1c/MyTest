<?php

class ShopController extends BaseController
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
    
    public function addAction()
    {
        if($this->isPost()) {
            $shop = $this->getPost('shop');
            $user = $this->getPost('user');
            if( Service::getInstance('user')->getUserByaccount( $user['account'] ) ){
                $this->respon(0,'用户已存在');
            }else{
                $id = Service::getInstance('shop')->add($shop);
                if(!id) {
                    $this->respon(0,'添加失败');
                }
                $user['role'] = 1;
                $user['shopId'] = $id;
                $user['name'] = $user['account'];
                $uid  = Service::getInstance('user')->add($user);
                if(!uid){
                    $this->respon(0,'添加失败');
                } else {
                    Service::getInstance('shop')->updateUid($uid,$id);
                    $this->respon(1,'添加成功');
                }
            }
        }  
        $province = Service::getInstance('shop')->getProvince();
        $this->_view->province = $province;
    }
    
    public function cityAction() {
        $province = $this->getPost('province');
        $city = Service::getInstance('shop')->getCity($province);
        if($city) {
            $this->respon(1,$city);
        } else {
            $this->respon(0,'查询失败');
        }
        
    }
    
    public function areaAction() {
        $city = $this->getPost('city');
        $area = Service::getInstance('shop')->getCity($city);
        if($city) {
            $this->respon(1,$area);
        } else {
            $this->respon(0,'查询失败');
        }
        
    }
    
    public function managerAction() {
        $list = Service::getInstance('shop')->getList();
        $this->_view->list = $list;
    }
   
}