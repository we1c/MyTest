<?php

class Service_Apigoods extends Service{

	public function uploadImg( $channel,$gid = 0 ){
		return Factory::instance( 'c_s_'.$channel, 'Api/channelSdk' )->uploadImg( $channel,$gid );
	}

	public function pushGoods( $channel,$gid ){
		return Factory::instance( 'c_s_'.$channel ,'Api/channelSdk' )->pushGoods( $channel,$gid );
	}

	public function downGoodsByGid( $gid ){

		$data = $this->_getPushChannelByGid( $gid );
		if( !$data ) return true;
		$channels = implode( ',',$this->_getCols( $data , 'channel' ) );
		$sql = " SELECT id FROM channel WHERE apiType = 2 AND apiDown = 1 AND id IN ( $channels ) ";
		$channels = $this->db->fetchAll( $sql );
		if( !empty($channels) ){
			foreach( $channels as $channel ){
				Factory::instance( 'c_s_'.$channel['id'] , 'Api/channelSdk' )
				->downGoods( $channel['id'] ,$gid );
			}
		}
	}

	private function _getPushChannelByGid( $gid ){
		return $this->db->fetchAll( " SELECT channel FROM push WHERE goodsId = '{$gid}' " );
	}

	private function _getCols( $arr ,$col ){
		$res = array();
		foreach( $arr as $k => $row ){
			$res[] = $row[$col];
		}
		return $res;
	}

	public function downGoodsByChannel( $channel ,$gid ){
		return Factory::instance( 'c_s_'.$channel , 'Api/channelSdk' )->downGoods( $channel ,$gid );
	}

}