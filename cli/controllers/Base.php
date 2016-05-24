<?php
class BaseController extends Yaf_Controller_Abstract
{
    public $db;
    public $mongo;
    public $redis;
    public $wei;
    
    public $response;

    protected $_token;
    protected $_appid;
    
    public function init()
    {   try{
            $this->db = Db::getInstance( );
            $this->redis = Red::getInstance();
            $this->wei = Weichat::getInstance();

        } catch ( Exception $e ){
            echo $e->getMessage();
        }
    }

    public function getParam($name, $default = null)
    {
        return $this->getRequest()->getParam($name, $default);
    }

    public function setResponse($name, $value = '')
    {
        if (is_array($name)) {
            $this->getResponse()->data = $name;
            return;
        }

        $this->getResponse()->data[$name] = $value;
    }

    public function setMessage($message)
    {
        $this->getResponse()->messages[] = $message;
    }

    public function setPushMessages($debug, $token, $message)
    {
        $row = array('appid' => $this->_appid, 'debug' => $debug, 'token' => $token, 'message' => $message);
        //$this->getResponse()->pushMessages[] = $row;
        $row = json_encode($row);
        $queue = Yaf_Application::app()->getConfig()->get('push')->queue;
        Red::getInstance()->lPush($queue, $row);
        echo "push to queue: $queue\n";
    }

    public function setError($code = 200, $error = '')
    {
        $this->getResponse()->code = $code;
        $this->getResponse()->error = $error;
    }

    public function error($error, $no = 0)
    {
        //$this->_view->error = $error;
    }

    public function fatal($message = '', $url = '/')
    {
        //exit($message);
    }

}

