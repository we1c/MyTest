<?php

class AddaccountController extends BaseController{

	private $yesterday;
	private $key;

    public function init()
    {
        parent::init();
		//匹配前一天的[key]
		$yesterday       = date( 'Ymd',strtotime("-1 day"));
		$key             = md5( "account_".$yesterday );
		$this->yesterday = $yesterday;
		$this->key       = $key;
    }

	public function redistoaccountAction(){
		$yesterday   = $this->yesterday;
		$key         = $this->key;
		$checkExists = $this->redis->exists( $key );
		$content     = PHP_EOL."[start] [date={$yesterday}] [key={$key}]";
		
		if ( $checkExists ) {
			$type = $this->redis->type( $key );

			//如果为set
			if ( $type == 2 ) {
				$orders = $this->redis->smembers( $key );
				if ( !$orders ) {
					$content .= " no orders! [end]".PHP_EOL;
					$this->writeToLog( $content );
					return false;
				}

				$data   = $this->getData( $orders );

				if ( $data ) {
					$content = $this->main( $data,$content );
				}
				

				$content .= PHP_EOL." [end]";
				$this->writeToLog( $content );
			}else{
				$content .= " no set! [end]".PHP_EOL;
				$this->writeToLog( $content );
				return false;
			}
		}else{
			$content .= " no key! [end]".PHP_EOL;
			$this->writeToLog( $content );
			return false;
		}
	}

	//获取订单的信息
	private function getData( $orders ){
		$orderIn = implode( $orders,',' );
		$result  = Service::getInstance('orders')->getAccountDataById( $orderIn );

		if ( $result['remOrder'] ) {
			foreach ($result['remOrder'] as $rem) {
				$remove = $this->redis->SREM( $this->key,$rem );
			}
		}
		
		if ( $result['data'] ) {
			foreach ( $result['data'] as $k => $v ) {
				if ( !isset( $data[$v['shopId']] ) ) {
					$data[$v['shopId']] = array();
				}
				if ( !isset( $data[$v['shopId']]['total'] ) ) {
					$data[$v['shopId']]['total'] = '';
				}

				$v['price'] = $v['goods_price'] * $v['number']
							+ $v['real_certificate']
							+ $v['real_freight']
							+ $v['real_mount']
							+ $v['real_other']
							+ $v['real_pack'];
				$data[$v['shopId']][] = $v;
				$data[$v['shopId']]['total'] += $v['price'];
			}
			return $data;
		}else{
			return false;
		}
		
	}

	//添加结算单并修改订单状态
	private function main( $data,$content ){
		foreach ( $data as $shopId => $v ) {
			$account = array(
						"shopId"       =>$shopId,
						"devId"        =>0,
						"total"        =>$v['total'],
						"createTime"   =>time(),
						"expectTime"   =>date('Y-m-d'),
						"note"         =>'',
						"type"         =>1,
						"audit_status" =>4,
                        );
			unset( $v['total'] );
			$orderArr = array();
			foreach ( $v as $value ) {
				$orderArr[] = $value['id'];
			}
			$orderIn = implode( $orderArr,',' );
			$accountId = Service::getInstance('account')->add( $account );
			$content .= PHP_EOL." [insert accountId={$accountId}] ";

            if ( $accountId ){
                $update = Service::getInstance('orders')->editAccount(array('accountId'=>$accountId,'account_status'=>1),$orderIn);
                $content .= " [update orderId IN ({$orderIn})] ";
                if ( $update !== false ) {
                	foreach ( $orderArr as $order ) {
						$remove = $this->redis->SREM( $this->key,$order );

						$content .= " [delete redis={$order}] ";
					}
                }
            }
		}
		return $content;
	}

	//写入日志
	private function writeToLog( $content ){
		$dir = ROOT_PATH.'/logs/addAccount.log';
		file_put_contents( $dir , $content , FILE_APPEND );
	}
}