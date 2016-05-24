<?php

require_once __DIR__.'/lib/KdtApiClient.php';

class Api_channelSdk_jianlou_Dispatch{

	private $_client;
	private $_db;

	private $_common = array(
		'url'		 =>'http://121.199.50.107:9999/api.ashx',
		'userID'	 =>'314405',
		'token'		 =>'0de2f73542644a97b15807cf5fffa39f',
		'signMethod' =>'md5',
		'format'	 =>'json',
		'client'	 =>1,
		'channelId'	 =>11,
	);
	private $_action = array(
			'push'=>array(
				'action'=>'ReleaseNewGoods',
				'cmd'=>'C.G.3',
				'v'=>'3',
				),
			'down'=>array(
				'action'=>'OffShelves',
				'cmd'=>'C.G.16',
				'v'=>'3',
				),
			'sendImg'=>array(
				'action'=>'UploadAuctionGoodsPhoto',
				'cmd'=>'A.G.20',
				'v'=>'1',
				),
			'getServerTime'=>array(
				'action'=>'GetServerTime',
				'cmd'=>'S.P.1',
				'v'=>'1',
				),
			'selledGoods'=>array(
				'action'=>'GetMyGoodsListForSelled',
				'cmd'=>'C.G.28',
				'v'=>'3',
				),
	);

	public function __construct( ){
		$this->_client = new KdtApiClient( $this->_common );
		$this->_db = Db::getInstance( );
	}

	public function uploadImg( $channel,$gid ){
		$data = array( 'userID'=>$this->_common['userID'] );
		$param['p'] = json_encode( $data );
		$imgDir = Service::getInstance( 'goods' )->getImageDirByGoodsId( $gid );
		if( !$imgDir || !is_array( $imgDir ) ) return false;
		$result = $this->_client->sendImg( $this->_action['sendImg'], $param, $imgDir );
		if( !empty($result['success']) ){
			$sql = ' INSERT INTO goods_image_push ( `goodsId`,`imgUrl`,`channel` ) VALUES ';
			foreach( $result['success'] as $imgUrl ){
				$sql .= " ( '{$gid}' ,'{$imgUrl}','{$channel}' ),";
			}
			$sql = rtrim( $sql , ',' );
			if( $this->_db->query( $sql ) ) return true;
		}else{
			return false;//$result里面有错误返回，暂时只返回false;
		}
	}

	public function pushGoods( $channel,$gid ){
		$this->uploadImg( $channel,$gid );
		$goods = Service::getInstance( 'goods' )->getRedisPushGoodsById( $gid );
        $imgUrl = Service::getInstance( 'goods' )->getPushImgByGidCid( $gid,$channel );
        $imgs = array();
        foreach( $imgUrl as $k => $v ){
        	if( $k == 8 ) break;
            $imgs[] = $v['imgUrl'];
        }
        $intro = preg_replace( '/<\/?[^>]+>/i', '', htmlspecialchars_decode($goods['intro']));
        $intro = preg_replace( '/&nbsp;|&amp;|&#039;/', '', $intro );
        $data = array(
        		'userID'=>$this->_common['userID'],
                "goodsName"=>$goods['name'],
                "categoryID"=>"1",
                "memo"=>$intro,
                "price"=>round($goods['purchPrice']*$goods['ptimes']),
                "expressFee"=>0,
                "province"=>"北京市",
                "city"=>"北京",
                "district"=>"东城区",
                "lng"=>"116.391445",
                "lat"=>"39.920791",
                "sortFlag"=>"1",
                "num"=>$goods['goodsStock'],
                "marketPrice"=>($goods['purchPrice']*$goods['mtimes']),
                "goodsPhotoList"=>$imgs
        );
        $param['p'] = json_encode( $data );
        //{"Code":0,"Msg":"","Data":493710,"IsSuccess":true}
        $result = $this->_client->post( $this->_action['push'],$param );

        if( $result['Code'] === 0 ){
        	$data = array( 'cGoodsId'=>$result['Data'] );
        	$where = " goodsId = '{$gid}' AND channel = '{$channel}' ";
        	$res = Service::getInstance( 'push' )->editByWhere( $data ,$where );
        	if( $res === 0 || $res > 0 ) return true;
        }else{
        	$result['result'] = 'error';
        	return $result;
        }
	}

	//$data = array('channel'=>,'cGoodsId'=>,'gid'=>,);
	public function downGoods( $channel ,$gid ){
		$row = Service::getInstance( 'push' )->getAccurateInfo( $gid,$channel );//*
		$resP = Service::getInstance( 'goods' )->delPushGoods( $row['id'] );
		$resImg = Service::getInstance( 'goods' )->delPushGoodsImg( $gid , $channel );
		//if( $resImg === false || $resImg < 0 ) return 'img_false';
		$info = array(
	                'userID'=>$this->_common['userID'],
	                'goodsID'=>$row['cGoodsId'],
                );
		$param['p'] = json_encode( $info );
		$this->_client->get( $this->_action['down'],$param );
		return true;
	}

	public function getServerTime(){
		return $this->_client->get( $this->_action['getServerTime'] ,array() );
	}

}