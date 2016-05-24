<?php

class BaseController extends Yaf_Controller_Abstract
{
    public $db;
    public $redis;
    public $wei;

    protected $_appId;
    protected $_appSecret;

    protected $_debug;
    protected $_developer;
    protected $_devid;
    public $openId;
    
    public function init()
    {	
        $this->db = Db::getInstance();
        $this->redis= Red::getInstance();
        $this->wei = Weichat::getInstance();

        $weiConfig = Yaf_Application::app()->getConfig()->get('wei');
        $this->_appId = $weiConfig -> get('appid');
        $this->_appSecret = $weiConfig -> get('secret');

        $token = empty($_COOKIE['openId']) ? '' : $_COOKIE['openId'];
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        
        $token = Util::strcode($token, $key, 'decode');
        $this->openId = $token;

        $this->_view->_module = 'index';
        $this->_view->_moduleName = '首页';
        $this->_view->_action = $this->getRequest()->getActionName();
        $this->_view->_controllerName = $this->getRequest()->getControllerName();
    }

    public function isPost()
    {
        return $this->getRequest()->isPost();
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

    public function flash($url = '/', $message = '', $second = 2)
    {
        if ($second == 0) {
            $this->redirect($url);
            return;
        }

        $this->_view->url = $url;
        $this->_view->message = $message ? $message : '操作成功';
        $this->_view->second = $second;

        $this->display('../sys/flash');
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
    
    	if( $success )
    	{
    		$result['data'] = $res;
    	}
    	else
    	{
    		$result['error'] = $res;
    	}
    
    	exit( json_encode( $result ) );
    }
}