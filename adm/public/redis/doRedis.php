<?php

class DoRedisList {

	private $_redis;
	private $_mysqli;
	private $_config;
	private $_wei;
	
	public function __construct( $config = array( ) ){ 

		$this->_config = require_once('./conf.php');

		$this->_config = array_merge($this->_config,$config);

		require('./weiChat.php');
		$this->_wei = new WeiChat();

		$this->_redis = new Redis();
		$this->_redis->connect($this->_config['RE_host'],$this->_config['RE_port']);

		$host = $this->_config['MY_host'];
		$user = $this->_config['MY_user'];
		$pwd  = $this->_config['MY_password'];
		$Db   = $this->_config['MY_defaultDb'];
		$charset = $this->_config['MY_charset'];

		$this->_mysqli = new mysqli($host,$user,$pwd,$Db);
		$this->_mysqli -> set_charset($charset);
	}

	public function run (){

		while(1){
			//出列
			$data=json_decode($this->_redis->rpop('time_list'),true);
			
			if(empty($data)) continue;
			//判断有没有对该信息操作过
			$key = md5($data['id']);
			if($this->_redis->exists($key)){
				$new = json_decode($this->_redis->get($key),true);
				if($new['type'] == 'del'){
					continue;
				}else if($new['type'] == 'upd'){
					foreach($new as $k => $v){
						$data[$k] = $v;
					}
				}
				$this->_redis->del($key);
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
						$data['start'] = 'yes';
						$data = $this->_doStart( $data );
					}
				}elseif( $now_time >= $end_time){
					$res = $this->_doEnd( $data );
					if($res) continue;
				}
			}else {
				//暂时没有
			}

			$this->_redis->lpush('time_list',json_encode($data));
			unset($data);
		}
	}

	private function _doStart( $data ){
		$gid = $data['gid'];
		$puid = $data['id'];
		$sql1 = " UPDATE goods SET status = '6' WHERE id = {$gid} ";
		$sql2 = " UPDATE push SET status = '2' WHERE id = {$puid} ";
		if($this->_mysqli->query( $sql1 ) && $this->_mysqli->query( $sql2 )){
			//锁定成功，推送消息：
			$data['action'] = $data['cname'].' 锁定商品';

			$info = $this->_getPushInfoByGid( $gid );

			$data = array_merge( $data,$info );
			$this->_wei -> doPushSend( $data );
			return $data;
		}
	}

	private function _doEnd( $data ){
		$gid = $data['gid'];
		$puid = $data['id'];
		$preid = $data['preId'];
		$chk_g = " SELECT status FROM goods WHERE id = {$gid}";
		$res_g=$this->_mysqli->query( $chk_g );
		$row_g=mysqli_fetch_assoc( $res_g );
		$status_g=$row_g['status'];
		if( $status_g == 6 ){
			//未购买，仍然是锁定状态，修改商品表和推送表状态，记录流拍次数
			$sql1 = " UPDATE goods SET status = '1' WHERE id = {$gid} ";
			$sql2 = " UPDATE push SET status = '0' WHERE id = {$puid} ";
			$res1 = $this->_mysqli->query( $sql1 );
			$res2 = $this->_mysqli->query( $sql2 );
			if( $res1>=0 && $res2>=0 ){
				//删除预售记录：
				$sql3 = " DELETE FROM presell WHERE id = {$preid} ";
				if($res_pr = $this->_mysqli->query( $sql3 )){
					mysqli_free_result( $res_g );
					$data['action'] = $data['cname'].' 商品流拍';
					$this->_wei -> doPushSend( $data );
					return true;
				}
			}
		}
		
		if( $status_g == 2 ){
			$data['action'] = $data['cname'].' 售出商品';
			$this->_wei -> doPushSend( $data );
			return true;
		}
		return false;
	}
	
	private function _getPushInfoByGid( $gid ){

		$sql = " SELECT devId,channel FROM push WHERE goodsId = {$gid} ";
		$result = $this->_mysqli->query( $sql );

		$ids = array();
		$channel = array();
		while( $row = mysqli_fetch_assoc($result) ){
			$ids[] = $row['devId'];
			$channel[] = $row['channel'];
		}
		$devIds = implode(',',$ids);
		$sql = " SELECT openId FROM developers WHERE id IN ( ".$devIds." ) ";
		$result = $this->_mysqli->query( $sql );
		$data['openIds'] = array();
		while( $row = mysqli_fetch_assoc($result) ){
			$data['openIds'][] = $row['openId'];
		}

		$channels = implode(',',$channel);
		$sql = " SELECT name FROM channel WHERE id IN ( ".$channels." ) ";
		$result = $this->_mysqli->query( $sql );
		$cnames = '';
		while( $row = mysqli_fetch_assoc($result) ){
			$cnames .= $row['name'].' | ';
		}
		$data['cnames'] = trim($cnames,' | ');

		return $data;
	}

}

$doRedis = new DoRedisList( );
$doRedis -> run( );

?>