<?php
if (!defined('JPUSH_ROOT')) {
    define('JPUSH_ROOT', dirname(__FILE__) . '/Plugins/');
    Yaf_Loader::import(JPUSH_ROOT ."JPush/JPush.php");
}

/**
 * 极光推送计划推送
 */
class AppSchedule{
    private $app_key = '905f5a03c11dd0eca2d67cc9';
    private $master_secret = 'a88bf5411fe5c632cb9e4f0d';
    private $client;

    public function __construct()
    {
        $this->client = new JPush($this->app_key, $this->master_secret, ROOT_PATH . "/logs/jschedule.log");
    }

    /**
     * 创建一个单次执行的定时广播
     * @param  String $massage 推送消息
     * @param  String $name    定时任务名字
     * @param  String $time    开始时间 格式2016-12-22 13:45:00
     */
    public function timingPush($massage, $name, $time)
    {
        $payload = $this->client->push()
                            ->setPlatform("all")
                            ->addAllAudience()
                            ->setNotificationAlert($massage)
                            ->build();

        $response = $this->client->schedule()->createSingleSchedule($name, $payload, array("time"=>$time));

        return $this->_format($response);
    }

    /**
     * 创建一个重复的定时任务
     * @param  String $massage 推送消息
     * @param  String $name    定时任务名字
     * @param  String $start   开始时间 格式2016-12-22 13:45:00
     * @param  String $end     结束时间 格式2016-12-25 13:45:00
     * @param  String $time    定时任务时间 格式14:00:00
     */
    public function loopPush($massage, $name, $start, $end, $time)
    {
        $payload = $this->client->push()
                            ->setPlatform("all")
                            ->addAllAudience()
                            ->setNotificationAlert($massage)
                            ->build();

        $response = $this->client->schedule()->createPeriodicalSchedule($name, $payload, 
                array(
                    "start"=>$start,
                    "end"=>$end,
                    "time"=>$time,
                    "time_unit"=>"DAY",
                    "frequency"=>1
                ));

        return $this->_format($response);
    }

    /**
     * 获取定时任务列表
     * @param  int $page 页数
     */
    public function getSchedules($page=1)
    {
        $response = $this->client->schedule()->getSchedules($page);

        return $this->_format($response);
    }

    /**
     * 删除指定的定时任务
     * @param  Int $scheduleId 任务id
     */
    public function delSchedule($scheduleId)
    {
        $response = $this->client->schedule()->deleteSchedule($scheduleId);

        return $this->_format($response);
    }

    /**
     * 更新一个单次执行定时任务
     * @param  String $sceduleId 任务id
     * @param  String $massage 推送消息
     * @param  String $name    定时任务名字
     * @param  String $time    开始时间 格式2016-12-22 13:45:00
     */
     public function updateTiming($scheduleId, $massage, $name, $time)
    {
       $payload = $this->client->push()
                            ->setPlatform("all")
                            ->addAllAudience()
                            ->setNotificationAlert($massage)
                            ->build();

        $response = $this->client->schedule()->updateSingleSchedule($name, $payload, array("time"=>$time));

        return $this->_format($response);
    }

    /**
     * 更新一个重复的任务
     * @param  String $sceduleId 任务id
     * @param  String $massage 推送消息
     * @param  String $name    定时任务名字
     * @param  String $start   开始时间 格式2016-12-22 13:45:00
     * @param  String $end     结束时间 格式2016-12-25 13:45:00
     * @param  String $time    定时任务时间 格式14:00:00
     */
    public function updateLoop($scheduleId, $massage, $name, $start, $end, $time)
    {
        $payload = $this->client->push()
                            ->setPlatform("all")
                            ->addAllAudience()
                            ->setNotificationAlert($massage)
                            ->build();

        $response = $this->client->schedule()->createPeriodicalSchedule($name, $payload, 
                array(
                    "start"=>$start,
                    "end"=>$end,
                    "time"=>$time,
                    "time_unit"=>"DAY",
                    "frequency"=>1
                ));

        return $this->_format($response);
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