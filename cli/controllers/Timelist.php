<?php

class TimelistController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function defaultAction()
    {
        $this->setResponse(array('wawa' => 'sdfds'));
    }  

    public function mysql1redis1pushAction(){
        $workerTime = time();
        try{
            while(1){

                //间断性请求数据库
                if( time() > $workerTime + 60 ){
                    Db::touchDb();
                    $workerTime = time();
                }

                //出列
                $data=json_decode( $this->redis->rpop('time_list'),true );
                //如果队列为空，终止此次循环，进行下次
                if(empty($data)) {
                    sleep(1);
                    continue;
                }
                //判断有没有对该信息操作过
                $key = md5( $data['do'].'-'.$data['id'] );

                if( $this->redis->exists( $key ) ){

                    $new = json_decode( $this->redis->get( $key ),true );

                    if($new['action'] == 'del'){
                        if( $data['start'] == 'yes' ){
                            //echo "lock->del".PHP_EOL;
                            if( $this->_doUnlock( $data['gid'] ) ) continue;
                        }
                        //echo "unlock->del".PHP_EOL;
                        continue;
                    }else if( $new['action'] == 'upd' ){
                        $newStart = strtotime( $new['startTime'] );
                        $newEnd = strtotime( $new['endTime'] );
                        if( $data['start'] == 'yes' ){
                            if(  time() < $newStart  ){
                                //echo "lock->upd->defer".PHP_EOL;
                                $res = $this->_doUnlock( $data['gid'] );
                                if( $res ) $data['start'] = 'no';  
                            }
                        }else if ( $data['start'] == 'no' ){
                            if( $newEnd < time() ){
                                //echo "lock->upd->outlist".PHP_EOL;
                                $res = $this->_delPresell( $data['preId'] );
                            }
                        }
                        //echo "only->upd".PHP_EOL;
                        foreach($new as $k => $v){
                            $data[$k] = $v;
                        }
                    }
                    $this->redis->del( $key );
                }
                //根据不同的键值 'do'=>'完成事件' ,做相应的时间逻辑处理
                if( $data['do'] == 'pre_goods' ){
                    $startTime = $data['startTime'];
                    $endTime = $data['endTime'];
                    $now_time = time();
                    $start_time = strtotime($startTime);
                    $end_time   = strtotime($endTime);
                    
                    if( $now_time >= $start_time && $now_time<$end_time ){
                        if($data['start'] == 'no'){
                            $data = $this->_doStart( $data );
                            //var_dump(json_encode($data));
                        }
                    }elseif( $now_time >= $end_time){
                        if( $data['start'] == 'yes' ){
                            $res = $this->_doEnd( $data );
                            if($res) continue;
                        }else{
                            $this->_delPresell( $data['preId'] );
                        }
                        continue;
                    }
                }else {
                    //暂时没有
                }
                
                $this->redis->lpush('time_list',json_encode($data));
                unset($data);
            }
        } catch (Exception $e) {   
            echo $e->getMessage();
        } 
    }
    /*"{\"id\":\"138\",\"channel\":\"3\",\"name\":\"\u4e94\u74e3\u5927\u91d1\u521a\",\"gid\":\"1158\",\"goodsNo\":\"m007151119005\",\"preId\":\"25\",\"startTime\":\"2016-01-23 03:50\",\"endTime\":\"2016-01-23 03:55\",\"sellType\":\"\u4e00\u53e3\u4ef7\",\"cname\":\"Red One\",\"do\":\"pre_goods\",\"start\":\"yes\",\"action\":\"Red One \u9501\u5b9a\u5546\u54c1\",\"openIds\":[\"o-PQFjxppcToSM2BoanjGQKNhCv4\",\"o-PQFj7jjy0nUl69hGeUK8ZJ1rmE\",\"o-PQFjyZIwr0yrGiLV7nL3oOfR4E\"],\"cnames\":\"\u4e1c\u5bb6 | Red One | Red Two\"}"*/
    //预售时间开始的操作
    private function _doStart( $data ){
        
            $gid = $data['gid'];
            $puid = $data['id'];
        try{
            if( $this->_doLock( $gid ) ){
                //echo "lock-successful".PHP_EOL;
                //锁定成功，推送消息：
                $data['action'] = $data['cname'].' 锁定商品';

                /*$info = $this->_getPushInfoByGid( $gid );

                $data = array_merge( $data,$info );*/

                $res= $this-> wei -> doPushSend( $data );
                if($res) $data['start'] = 'yes';

                return $data;
            }
        } catch( Exception $e ){
            echo $e->getMessage();
        }
    }

    //预售时间结束的操作
    private function _doEnd( $data ){
        
            $gid = $data['gid'];
            $preId = $data['preId'];
        try{
            $sql = " SELECT status FROM goods WHERE id = {$gid}";
            $status=$this->db->fetchOne( $sql );
            //这里暂时实现，后面可以用事务进行控制这些逻辑同时成立
            //未购买，仍然是锁定状态，修改商品表和推送表状态，记录流拍次数
            if( $status == 6 ){
                if( $this->_doUnlock( $gid ) ){
                    //echo "nosell---unlock---successful!".PHP_EOL;
                    //删除预售记录：
                    if( $this->_delPresell( $preId ) ){
                        $data['action'] = $data['cname'].' 商品流拍';
                    }
                }
            }
            //直接发送消息，由购买的动作去删除推送表和预售表
            if( $status == 2 ){
                //echo "sell---outlist!".PHP_EOL;
                $data['action'] = $data['cname'].' 售出商品';
            }

            $res = $this -> wei -> doPushSend( $data );
            if( $res ) return true;
            return false;
        } catch ( Exception $e ){
            echo $e->getMessage();
        }
    }

    //根据商品找对应的渠道、推送者
    private function _getPushInfoByGid( $gid ){
        return false;
        try{
            $sql = " SELECT devId,channel FROM push WHERE goodsId = {$gid} ";
            $result = $this->db->fetchAll( $sql );

            $ids = array();
            $channel = array();
            foreach( $result as $row ){
                $ids[] = $row['devId'];
                $channel[] = $row['channel'];
            }

            $devIds = implode(',',$ids);
            $sql = " SELECT openId FROM developers WHERE id IN ( ".$devIds." ) ";
            $openIds = $this->db->fetchAll( $sql );

            $data['openIds'] = array();
            foreach( $openIds as $row ){
                $data['openIds'][] = $row['openId'];
            }

            $channels = implode(',',$channel);
            $sql = " SELECT name FROM channel WHERE id IN ( ".$channels." ) ";
            $channels = $this->db->fetchAll( $sql );

            $cnames = '';
            foreach( $channels as $row ){
                $cnames .= $row['name'].' | ';
            }
            $data['cnames'] = trim($cnames,' | ');

            return $data;
        } catch ( Exception $e ){
            echo $e->getMessage();
        }
    }

    private function _doLock( $gid ){
        try{
            $resG = $this->db->update('goods',array('status'=>6),'id='.$gid);
            $resP = $this->db->update('push',array('status'=>2),'goodsId='.$gid);

            if( $resG >=0 && $resP >=0 ) return true;

            return false;
        } catch ( Exception $e ){
            echo $e->getMessage();
        }
    }

    private function _doUnlock( $gid ){
        try{
            $resG = $this->db->update('goods',array('status'=>1),'id='.$gid);
            $resP = $this->db->update('push',array('status'=>0),'goodsId='.$gid);

            if( $resG >= 0 && $resP >=0 ) return true;

            return false;
        } catch ( Exception $e ){
            echo $e->getMessage();
        }  
    }

    private function _delPresell( $preId ){
        try{
            return $this->db->delete( 'presell' , 'id = '.intval( $preId ) );
        } catch ( Exception $e ){
            echo $e->getMessage();
        }    
    }

}

