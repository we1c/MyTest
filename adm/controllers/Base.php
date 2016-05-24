<?php

class BaseController extends Yaf_Controller_Abstract
{
    public $db;
    public $redis;
    protected $_debug;
    protected $_developer;
    protected $_devid;
    protected $_menus;
    protected $_conName;
    protected $_actName;
    
    public function init()
    {	
        $this->db = Db::getInstance();
        $this->redis = Red::getInstance();
        $developer = Yaf_Registry::get('developer');
        $conName = $this->getRequest()->getControllerName();
        $actName = $this->getRequest()->getActionName();
        $this->_actName = $actName;
        $this->_conName = $conName;
        if ($developer) {
            $this->_developer = $developer;
            $this->_devid = $developer['id'];
            $menus = Service::getInstance('node')->getMenuLevel3( $developer['role'] );
            if ( !in_array('1',$developer['role']) ) {
                $mymenus = Service::getInstance('node')->getMenuByRole( $developer['role']);
                $con = array();
                //print_r($menus);exit;
                foreach ( $menus as $v ) {
                    array_push( $con, ucfirst( $v['con'] )."/".strtolower($v['act']) );
                }
                $whiteList = array(
                                'Dev/index','Developer/signin','Developer/index','Developer/edit','Developer/index','Developer/updatepwd','Developer/updatepwd','Developer/signout'
                            );
                if ( !in_array( $conName."/".$actName, $whiteList ) ){
                    if ( !in_array( $conName."/".$actName, $con ) ) {
                        if ($this->isAjax()) {
                            $this->respon(2,'您没有该权限，如需要该权限，请联系管理员');
                        }else{
                            $this->flash( '/dev/index', '您没有该权限，如需要该权限，请联系管理员', 1 ,1 );
                        }
                        exit;
                    }   
                }
            }else{
                $mymenus = Service::getInstance('node')->getMenu();
            }
            $parentMenu = Service::getInstance('node')->getParentMenuByConAndAct($conName,$actName);
            $this->_view->parentMenu = $parentMenu;
            $this->_view->mymenus = $mymenus;
            $this->_view->_developer = $developer;
        }
        $this->_view->_module = 'dev';
        $this->_view->m = $this->getQuery( "m", '');
        $this->_view->_moduleName = '后台管理';
        $this->_view->_controllerName = $conName;
        $this->_view->_actionName = $actName;
              
    }

    public function isPost()
    {
        return $this->getRequest()->isPost();
    }

    public function isAjax(){
        return $this->getRequest()->isXmlHttpRequest();
    }
    
    public function getParam($name)
    {
        return $this->getRequest()->getParam($name);
    }
    
    public function getQuery($name, $default = '')
    {
        $value = $this->getRequest()->getQuery($name);
        if ($value === null) {
            $value = $default;
        }

        return $value;
    }

    public function getPost($name, $default = '')
    {
        $value = $this->getRequest()->getPost($name);
        if ($value === null) {
            $value = $default;
        }

        return $value;
    }

    public function setResponse($name, $value = '')
    {
        if (is_array($name)) {
            $this->getResponse()->data = $name;
            return;
        }

        $this->getResponse()->data[$name] = $value;
    }
    
    public function setError($code = 200, $error = '')
    {
        $this->getResponse()->code = $code;
        $this->getResponse()->error = $error;
    }

    public function error($error, $no = 0)
    {
        $this->_view->error = $error;
    }

    public function message($message = '')
    {
        $this->_view->message = $message ? $message : '操作完成';
        $this->display('../sys/message');
    }

    public function flash($url = '/', $message = '', $second = 0 ,$isBase = 0)
    {
        if ($second == 0) {
            $this->redirect($url);
            return;
        }

        if ($isBase){
            $dir = "/sys/flash";
        }else{
            $dir = "../sys/flash";
        }

        $this->_view->url = $url;
        $this->_view->message = $message ? $message : '操作成功';
        $this->_view->second = $second;

        $this->display( $dir );
        exit();
    }

    public function fatal($message = '', $url = '/')
    {
        $this->_view->error = $message;
        if ($this->_view->isAjax()) {
            throw new Exception($message);
        } else {
            $this->display('../sys/fatal');
        }

        exit();
    }
    public function respon( $success = 0 , $res  )
    {
    
    	$result['success'] = $success;
    
        switch ( $success ) {
            case '0':
                $result['error'] = $res;
                break;
            case '1':
                $result['data'] = $res;
                break;
            case '2':
                $result['notice'] = $res;
                break;
        }
    
    	exit( json_encode( $result ) );
    }
}