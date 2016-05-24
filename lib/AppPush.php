<?php
if (!defined('JPUSH_ROOT')) {
    define('JPUSH_ROOT', dirname(__FILE__) . '/Plugins/');
    Yaf_Loader::import(JPUSH_ROOT ."JPush/JPush.php");
}

/**
 * 极光推送
 */
class AppPush{

    private $app_key = '905f5a03c11dd0eca2d67cc9';
    private $master_secret = 'a88bf5411fe5c632cb9e4f0d';
    private $client;

    public function __construct()
    {
        $this->client = new JPush($this->app_key, $this->master_secret, ROOT_PATH . "/logs/jpush.log");
    }

    /**
     * 推送消息(广播)
     * @param  String $massage 要推送的信息
     * @return 推送后返回的信息
     */
    public function simplePush($massage)
    {
        if(!is_string($massage))
        {
            return false;
        }

        $result = $this->client->push()
                            ->setPlatform('all')
                            ->addAllAudience()
                            ->setNotificationAlert($massage)
                            ->send();

        return $this->_format($result);
    }

    /**
     * 根据tag推送消息
     * @param  Array  $tag     tag数组
     * @param  String $massage 要推送的信息
     */
    public function simplePushForTag($tag, $massage)
    {
        if(!is_string($massage) && !is_string($tag))
        {
            return false;
        }

        $result = $this->client->push()
                            ->setPlatform('all')
                            ->addTag($tag)
                            ->setNotificationAlert($massage)
                            ->send();
                            
        return $this->_format($result);
    }

    /**
     * 根据别名进行推送
     * @param  String $alias   别名
     * @param  String $massage 要推送的信息
     */
    public function smimplePushForAlias($alias, $massage)
    {
        if(!is_string($massage) && !is_string($alias))
        {
            return false;
        }

         $result = $this->client->push()
                            ->setPlatform('all')
                            ->addAlias($alias)
                            ->setNotificationAlert($massage)
                            ->send();
                            
        return $this->_format($result);
    }

    /**
     * 根据tag进行安卓客户端推送
     * @param  Array  $tag       tag
     * @param  Sting  $alert     消息
     * @param  Sting  $title     标题
     * @param  int    $builderId 样式
     * @param  Array  $extas     key/value数组，业务使用
     */
    public function pushForTag2Android($tag, $alert, $extras, $title = '', $builderId = 1)
    {
         if(!is_string($tag) && !is_string($alert) && !is_string($title))
        {
            return false;
        }

        if(!is_int($builderId))
        {
            return false;
        }

        if(!is_array($extras))
        {
            return false;
        }

        $result = $this->client->push()
                            ->setPlatform('android')
                            ->addTag($tag)
                            ->addAndroidNotification($alert, $title, $builderId, $extras)
                            ->send();
        return $this->_format($result);
    }

    /**
     * 根据别名进行安卓客户端推送
     * @param  Array  $alias     别名
     * @param  Sting  $alert     消息
     * @param  Sting  $title     标题
     * @param  int    $builderId 样式
     * @param  Array  $extas     key/value数组，业务使用
     */
    public function pushForAlias2Android($alias, $alert, $extras, $title = '', $builderId = 1)
    {
        if(!is_string($alias) && !is_string($alert) && !is_string($title))
        {
            return false;
        }

        if(!is_int($builderId))
        {
            return false;
        }

        if(!is_array($extas))
        {
            return false;
        }
        
         $result = $this->client->push()
                            ->setPlatform('android')
                            ->addAlias($alias)
                            ->addAndroidNotification($alert, $title, $builderId, $extras)
                            ->send();
                            
        return $this->_format($result);
    }





    /**
     * 格式化数据
     * @param  object $obj 请求得到的数据
     * @return array       格式化后的数组
     */
    private function _format($obj)
    {
        return json_decode(json_encode($obj), true);
    }

}