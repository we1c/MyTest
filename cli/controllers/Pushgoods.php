<?php

class PushgoodsController extends BaseController{

	private $_api;

	public function init(){
		parent::init();
		$this->_api = Api::getInstance();
	}

	public function pushgoodsbychannelAction(){
		$workTime = time();
		try{
			while(1){
				//每六小时重新连接一次数据库
				if( time() > $workTime + 21600 ){
					Db::touchDb( );
					$workTime = time();
				}

				$data = $this->redis->rpop( 'channel_goods' );
				if( $data ){
					$data = json_decode( $data,true );
					switch( $data['action'] ){
						case 'pushGoods':
							$result = $this->_api->pushGoods( $data['channel'],$data['goodsId'] );
							break;
						case 'downGoodsByChannel':
							$result = $this->_api->downGoodsByChannel( $data['channel'],$data['goodsId'] );
							break;
						case 'downGoodsByGid':
							$result = $this->_api->downGoodsByGid( $data['goodsId'] );
							break;
					}

					//如果失败，需要放入后续的错误队列，或者其他的处理，暂时先放入文件
					//if( !$result ) $this->redis->lpush( 'push_goods' , $data );

					if( !$result || isset($result['error']) ){
						unset($result['error']);
						$content = '[start][data]['.date('Y-m-d H:i:s').']'.json_encode($result).json_encode( $data ).'[end]'.PHP_EOL;
						file_put_contents(ROOT_PATH.'/logs/channel_goods.error.log', $content , FILE_APPEND );
					}
				}
			}
		}catch (Exception $e) {   
            echo $e->getMessage();
        } 
	}

}