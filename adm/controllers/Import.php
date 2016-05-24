<?php

class ImportController extends BaseController {
	
    public function init()
    {
        parent::init();  
    }

    public function dispatchAction(){
    	if( $this->isPost() ){
    		$action = trim( $this->getPost('action') );
    		$files = $this->getPost('files');
    		if( !$action || empty($files)) $this->respon( 0,'参数错误' );
    		switch ( $action ) {
    			case 'orderInsert':
    				$this->orderInsert( $files );
    				break;
    			case 'orderUpdate':
    				$this->orderUpdate( $files );
    				break;
    			default:
    				# code...
    				break;
    		}
    	}
    }

	public function indexAction(){
		if( $this->isPost() ){

	        foreach ($this->getPost('picture_name') as $k=>$v){
    			$data = '../public/data/'.$k[0].$k[1].'/'.$k[2].$k[3].'/'.$k;
    			break;
    		}
    		$objPHPExcel = Plugins_PHPExcel_IOFactory::load($data);
	    	$arr = $objPHPExcel->getActiveSheet()->toArray();
	    	array_shift($arr);
	    	$count=0;
// 	    	foreach ($arr as $v){
// 	    		if ($v[0]) {
// 	    		    $code = Service::getInstance('goods')->getGoodsCode($v[5]);
// 	    		    $str = '';
// 	    		    if ( !empty($v[4]) ) {
// 	    		        $param = explode(',', $v[4]);
//     	    		    if ( is_array( $param ) ) {
//     	    		        foreach ( $param as $kk=>$vv ) {
//     	    		            $paramData = explode('&', $vv);
//     	    		            if ( $paramData[0] && $paramData[1] ) {
//     	    		                $str[] = '{"key":{"id":0,"name":"'.$paramData[0].'"},"value":{"id":0,"name":"'.$paramData[1].'"}}';
//     	    		            }
//     	    		        }
//     	    		    }
// 	    		    }
// 	    		    if ( $str ) {
// 	    		        $attribute = '['.implode(',', $str).']';
// 	    		    } else {
// 	    		        $attribute = '';
// 	    		    }
// 	    		    if ( empty($v[6]) ) $v[6] = '';
// 	    		    if ( empty($v[7]) ) $v[7] = 1;
// 	    			$this->db->insert('goods', array('uploader'=>0,'goodsNo'=>$v[0],'name'=>$v[1],'purchPrice'=>$v[2],'attribute'=>$attribute,'shopId'=>$v[5],'intro'=>$v[6],'createTime'=>time(),'code'=>$code,'platform'=>2,'status'=>$v[7]));
// 	    			$gid = $this->db->lastInsertId();
// 	    			$pic = explode(',', str_replace('，',',',$v[3]));
// 	    			if ( $pic ) {
// 	    			    $sql = "INSERT INTO `goods_image` (`goodsId`, `imgurl`, `sort`, `image`) VALUES";
// 	    			    foreach ($pic as $key=>$value) {
// 	    			        $num = intval( $key + 1 );
// 	    			        $sql .="('{$gid}', '{$value}', $num, '' ),";
// 	    			    }
// 	    			    $sql = substr($sql, 0,-1);
// 	    			    Service::getInstance('goods')->addgoods_images($sql);
// 	    			}
// 	    			$count++;
// 	    		}
// 	    	}

	    	foreach ( $arr as $row ){
	    		if ( $row[0] ) {

					$status = $this->getOrderStatus( $row[10] );
	    			$orderCode = ltrim( $row[0] ,'\'');
	    			$sql = " SELECT orderCode FROM `orders` WHERE orderCode = '{$orderCode}' ";
	    			if( $this->db->fetchOne( $sql ) ){
	    				$this->db->update( '`orders`',$status," orderCode= '{$orderCode}' " );
	    			}else{
	    				$address = !$row[36] ? $row[13] : $row[36];
	    				$address = $this->getOrderAddress( $address );
	    				$remark = $row[11] ? $row[11] : '无';
	    				$data = array(
							'goodsId'      =>0,
							'payw'         =>0,
							'payType'=>0,
							'payBank'=>0,
							'orderCode'    =>$row[0],
							'price'        =>$row[8],
							'remark'       =>$remark,
							'uname'        =>$row[12],
							'deliverWay'   =>1,
							'tel'          =>ltrim( $row[16],'\'' ),
							'createTime'   =>strtotime( $row[17] ),
							'payTime'      =>$row[18] ? strtotime( $row[18] ) : '0',
							'goodsAlias'   =>$row[19],
							'sellerremark' =>$row[23],
							'number'       =>$row[24],
							'channel'      =>32,
							'shopId'       =>105,
							'orderType'    =>4,
	    					);
	    				$data = array_merge( $data,$status,$address );

	    				$this->db->insert( 'orders',$data );

	    				$orderId = $this->db->lastInsertId();
	    				$expressId = $this->getExpressId( $row[22] );

	    				$number = $row[22] ? explode( ':',$row[21] ) : '';

	    				$param = array(
							'expressId'    =>$expressId ? $expressId : '0',
							'number'       =>$number ? $number[1] : '0',
							'createTime'   =>date( 'Y-m-d H:i:s' ),
							'orderId'      =>$orderId,
							'real_freight' =>0
	    					);
	    				$this->db->insert( 'orders_express',$param );
	    			}
	    			$count++;
	    		}
	    	}
// 	    	foreach ($arr as $v){
// 	    		if ($v[0]) {
// 	    		    $str = '';
// 	    		    if ( !empty($v[1]) ) {
// 	    		        $param = explode(',', $v[1]);
//     	    		    if ( is_array( $param ) ) {
//     	    		        foreach ( $param as $kk=>$vv ) {
//     	    		            $paramData = explode('&', $vv);
//     	    		            if ( $paramData[0] && $paramData[1] ) {
//     	    		                $str[] = '{"key":{"id":0,"name":"'.$paramData[0].'"},"value":{"id":0,"name":"'.$paramData[1].'"}}';
//     	    		            }
//     	    		        }
//     	    		    }
// 	    		    }
// 	    		    if ( $str ) {
// 	    		        $attribute = '['.implode(',', $str).']';
// 	    		    } else {
// 	    		        $attribute = '';
// 	    		    }
// 	    			$this->db->update('goods', array('attribute'=>$attribute), " goodsNo = '{$v[0]}'");
// 	    			$count++;
// 	    		}
// 	    	}

	    	//添加标签
//             foreach ($arr as $v) {
//                 if($v[0]) {
//                     $p = $this->getCateId($v[0]);
//                     if($p) {
//                         $pid = $p['id'];
//                     } else {
//                         $pid = $this->add(array('name'=>$v[0],'pid'=>0,'level'=>1));
//                     }
//                     $s = $this->getCateId($v[1]);
//                     if($s) {
//                         $sid = $s['id'];
//                     } else {
//                         $sid = $this->add(array('name'=>$v[1],'pid'=>$pid,'level'=>2));
//                     }
                    
//                     $this->db->insert('category', array('name'=>$v[2],'pid'=>$sid,'level'=>3));
//                     $count++;
//                 }
//             }

	    	//添加参数
// 	    	foreach ($arr as $v) {
// 	    	    $cate = $this->getCateId($v[0]);
// 	    	    $par = $this->getParId($v[1],$cate['id']);
// 	    	    if($par) {
// 	    	        $pid = $this->addPar(array('cid'=>$cate['id'],'name'=>$v[2],'pid'=>$par['id']));
// 	    	    } else {
// 	    	        $pid = $this->addPar(array('cid'=>$cate['id'],'name'=>$v[1],'pid'=>0));
// 	    	        $res = $this->addPar(array('cid'=>$cate['id'],'name'=>$v[2],'pid'=>$pid));
// 	    	    }
// 	    	    $count++;
// 	    	}
	    	exit(json_encode(array('code'=>200,'msg'=>'共操作 '.$count.' 件商品')));
	    }
	}

	private function orderInsert( $files ){
		foreach ( $files as $k=>$v ){
			$data = '../public/data/'.$k[0].$k[1].'/'.$k[2].$k[3].'/'.$k;
			break;
		}

		$objPHPExcel = Plugins_PHPExcel_IOFactory::load($data);
    	$arr = $objPHPExcel->getActiveSheet()->toArray();
    	array_shift($arr);
    	$count=0;
    	foreach ( $arr as $row ){
    		if ( $row[0] ) {

				$status    = $this->getOrderStatus( $row[8] );
				
				$address   = !$row[21] ? $row[13] : $row[21];
				$address   = $this->getOrderAddress( $address );
				
				$sku       = $row[4] ? $row[4] : ( $row[9] ? $row[9] : 0 );
				$scode     = $sku ? substr( $sku,0,5 ) : 0 ;
				$shopId    = $scode ? Service::getInstance('shop')->getShopIdByScode( $scode ) : '105';
				$shopId    = $shopId ? $shopId : '105';
				$orderType = $shopId == '105' ? '4' : '3';

				$remark    = $row[7] ? $row[7] : '无';
				$orderCode = ltrim( $row[0] ,'\'');

				$data = array(
					'goodsId'      =>0,
					'payw'         =>0,
					'payType'      =>0,
					'payBank'      =>0,
					'orderCode'    =>$orderCode,
					'price'        =>$row[11] ? $row[11] : '0',
					'remark'       =>$remark,
					'uname'        =>$row[12] ? $row[12] : '',
					'deliverWay'   =>$row[14]== '快递' ? '1' : '2',
					'tel'          =>ltrim( $row[15],'\'' ),
					'createTime'   =>strtotime( $row[16] ),
					'payTime'      =>$row[17] ? strtotime( $row[17] ) : '0',
					'number'       =>$row[20],
					'channel'      =>32,
					'shopId'       =>$shopId,
					'orderType'    =>$orderType,
					'operator'	   =>'黄军',
					);

    			$data = array_merge( $data,$status,$address );
    			
    			$sql = " SELECT id FROM `orders` WHERE orderCode = '{$orderCode}' AND shopId = '{$shopId}' ";
    			if( $orderId = $this->db->fetchOne( $sql ) ){
    				$this->db->update( '`orders`',$data," id= '{$orderId}' " );
    			}else{
    				$this->db->insert( '`orders`',$data );
    				$orderId = $this->db->lastInsertId();
    			}

				$expressId = $this->getExpressId( $row[19] );
				$number = $row[18] ? explode( ':',$row[18] ) : '';
				$param = array(
					'expressId'    =>$expressId ? $expressId : '0',
					'number'       =>$number ? $number[1] : '0',
					'createTime'   =>date( 'Y-m-d H:i:s' ),
					'orderId'      =>$orderId,
					'real_freight' =>0
					);

				$sql = " SELECT id FROM `orders_express` WHERE orderid = '{$orderId}' ";
				if( $id = $this->db->fetchOne( $sql ) ){
					$this->db->update( 'orders_express',$param," id = '{$id}' " );
				}else{
					$this->db->insert( 'orders_express',$param );
				}

				$goodsImg = '';
				$goodsPrice = 0;
				
				$goodsId = $sku ? Service::getInstance('goods')->getGoodsIdBySku( $sku ) : '0';
				$goodsId = $goodsId ? $goodsId : '0';
				if( $goodsId ){
					$goodsImg = Service::getInstance('goods')->getGoodsImg( $goodsId );
					$goodsData = Service::getInstance('goods')->getGoodsById( $goodsId );
					$goodsPrice = $goodsData['purchPrice'];
					$param = array( 'goodsStock'=>($goodsData['goodsStock']-$row[3]),'price'=>$goodsData['price'] );
					$this->db->query("set sql_mode=''");
					$this->db->update( 'goods',$param," id = '{$goodsId}' " );
				}
				
				$tel = $row[15] ? ltrim( $row[15],'\'' ) : '';
				$customer_id = $tel ? Service::getInstance('user')->getUidBymobile( $tel ) : 0;
				$goods = array(
					'order_id'=>$orderId,
					'goods_id'=>$goodsId,
					'shop_id'=>$shopId,
					'customer_id'=>$customer_id ? $customer_id : '0',
					'goods_name'=>$row[1],
					'goods_image'=>$goodsImg ? $goodsImg : '',
					'goods_price'=>$goodsPrice ? $goodsPrice : '0',
					'goods_number'=>$row[3],
					'goods_pay_price'=>$row[2],
					);

				$sql = " SELECT id FROM orders_goods WHERE order_id = '{$orderId}' AND goods_id = '{$goodsId}' ";
				if( $id = $this->db->fetchOne( $sql ) ){
					$this->db->update( 'orders_goods',$goods," id = '{$id}' " );
				}else{
					$this->db->insert( 'orders_goods',$goods );
				}

    			$count++;
    		}
	    }
	    $this->respon( 1,'共操作 '.$count.' 件商品' );
	}

	private function orderUpdate( $files ){
		$this->respon(1,'已停用');
		foreach ( $files as $k=>$v ){
			$data = '../public/data/'.$k[0].$k[1].'/'.$k[2].$k[3].'/'.$k;
			break;
		}
		$objPHPExcel = Plugins_PHPExcel_IOFactory::load($data);
    	$arr = $objPHPExcel->getActiveSheet()->toArray();
    	$head = array_shift($arr);
    	if( count($head) > 15 ) $this->respon( 0,'导入文件错误' );
    	$count=0;
    	foreach( $arr as $row ){
    		if( $row[0] ){

	    		$goodsId = $row[9] ? Service::getInstance('goods')->getGoodsIdBySku( $row[9] ) : '0';
	    		$goodsId = $goodsId ? $goodsId : '0';
    			$orderCode = ltrim( $row[0] ,'\'');
    			$sql = " SELECT orderCode FROM `orders` WHERE orderCode = '{$orderCode}' ";
    			if( $this->db->fetchOne( $sql ) ){
	    			$data = array( 'goodsId'=>$goodsId );
    				$this->db->update( 'orders',$data," orderCode = '{$orderCode}' " );
    			}
    		}
    		$count++;
    	}
    	$this->respon( 1,'共操作 '.$count.' 件商品' );
	}
	
	private function getExpressId( $name ) {
		return $this->db->fetchOne( " SELECT id From express WHERE name = '{$name}' " );
	}

	private function getOrderAddress( $address ) {
		$info = explode( ' ',$address );
		$province = $this->getAddressId( array_shift( $info ) );
		$city     = $this->getAddressId( array_shift( $info ) );
		$area     = $this->getAddressId( array_shift( $info ) );
		$address  = $province && $city && $area ? implode( ' ',$info ) : $address;
		$param = array(
			'province' =>$province,
			'city'     =>$city,
			'area'     =>$area,
			'address'  =>$address,
			);
		return $param;
	}

	private function getAddressId( $name ){
		$id = $this->db->fetchOne( " SELECT id From city WHERE name = '{$name}' " );
		return $id ? $id : '0';
	}

	private function getOrderStatus( $data ) {

		if( !$data ) return array();
		$split = substr_count($data,'，') ? '，' : ',';
	    $status = explode( $split,$data );
	    if( $status[0] == '买家已付款' && $status[1] == '等待卖家发货' ){
	    	$param = array( 'isPay'=>1,'isDeliver'=>1,'isBuyer'=>0 );
	    }else if( $status[0] == '卖家已发货' && $status[1] == '等待买家确认' ){
	    	$param = array( 'isPay'=>1,'isDeliver'=>2,'isTake'=>1 );
	    }else if( $status[0] == '交易关闭' ){
	    	$param = array( 'isPay'=>1,'isDeliver'=>2,'isDel'=>1 );
	    }else if( $status[0] == '交易成功' ){
	    	$param = array( 'isPay'=>1,'isDeliver'=>2,'isTake'=>2 );
	    }
	    return $param;
	}
	
	public function getCateId($name) {
	    return $this->db->fetchRow("select id,name from category where name='{$name}'");
	}
	public function getParId($name,$cid) {
	    return $this->db->fetchRow("select id,name from parameter where name='{$name}' and cid = {$cid}");
	}
	public function add($data) {
	    $this->db->insert('category', $data);
	    return $this->db->lastInsertId();
	}
	public function addPar($data) {
	    $this->db->insert('parameter', $data);
	    return $this->db->lastInsertId();
	}
	public function addimageAction() {
	    if ($this->isPost()) {
	        $image = $this->getPost('goodsImg');
	        $hash  = $this->getPost('goodsHash');
	        $dir   = Yaf_Application::app()->getConfig()->get('image')->get('dir');
            $name = substr($image, 0, strpos($image, '.'));
            $goodsimg = Service::getInstance('goods')->getImg();
            foreach ($goodsimg as $k=>$v) {
                if($v['imgurl'] == $name) {
                    Service::getInstance('goods')->setImgHash($v['id'],$hash);
                    break;
                }
            }
            $this->respon( 1 , $dir );
	   }
	    return false;
	}
}