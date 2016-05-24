<?php
if (!defined('JPUSH_ROOT')) {
    define('JPUSH_ROOT', dirname(__FILE__) . '/Plugins/');
    Yaf_Loader::import(JPUSH_ROOT ."JPush/JPush.php");
}

/**
 * 极光推送设备设置
 */
class AppDevice{

    private $app_key = '905f5a03c11dd0eca2d67cc9';
    private $master_secret = 'a88bf5411fe5c632cb9e4f0d';
    private $client;

    public function __construct()
    {
        $this->client = new JPush($this->app_key, $this->master_secret, ROOT_PATH . "/logs/jdeample.log");
    }

    /**
     * 根据registrationId获取设备的Mobile,Alias,Tags等信息
     * @param  String $registrationId APP-SDK生成的id
     */
    public function getInfoByRegistrationId($registrationId)
    {
        if(!is_int($registrationId))
        {
            return false;
        }

        $result = $this->client->device()->getDevices($registrationId);

        return $this->_format($result);
    }

    /**
     * 获取Tag下设备列表
     */
    public function getTags()
    {
        $result = $client->device()->getTags();

        return $this->_format($result);
    }

    /**
     * 判断指定RegistrationId是否在指定Tag中
     */
    public function inTag($registrationId, $tag)
    {
        if(!is_string($registrationId))
        {
            return false;
        }

        if(!is_string($tag))
        {
            return false;
        }

        $result = $this->client->device()->isDeviceInTag($registrationId, $tag);

        return $this->_format($result);
    }

    /**
     * 获取别名下设备列表
     * @param  String $alias 别名
     */
    public function getAlias($alias)
    {
        if(!is_string($alias))
        {
            return false;
        }

        $result = $this->client->device()->getAliasDevices($alias);

        return $this->_format($result);
    }

    /**
     * 更新指定的设备的Alias
     * @param  String $registrationId APP-SDK生成的id
     */
    public function updateDevice($registrationId, $alias)
    {
        if(!is_string($registrationId))
        {
            return false;
        }

        if(!is_string($alias))
        {
            return false;
        }

        $result = $this->client->device()->updateDevice($registrationId, $alias);

        return $this->_format($result);
    }

    /**
     * 更新指定tag下的设备
     * @param  String $tag     tag
     * @param  Array  $idArray id数组
     * @param  String $action  添加或删除，添加 add 删除 remove
     */
    public function updateTag($tag, $idArray, $action = 'add')
    {
        if(!is_string($tag))
        {
            return false;
        }

        if(!is_array($idArray))
        {
            return false;
        }

        if(!in_array($action, array('add', 'remove')))
        {
            return false;
        }

        if($action == 'add')
        {
            $result = $this->client->device()->updateTag($tag, $idArray);
        }
        else if($action == 'remove')
        {
            $result = $this->client->device()->updateTag($tag, null, $idArray);
        }

        return $this->_format($result);
    }

    /**
     * 删除指定的别名
     * @param String $alias 别名
     */
    public function removeAlias($alias)
    {
        if(!is_string($alias))
        {
            return false;
        }

        $result = $this->client->device()->deleteAlias($alias);

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