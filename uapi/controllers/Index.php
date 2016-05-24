<?php

class IndexController extends BaseController
{
    
    public function indexAction() {
        exit('欢迎来到这里！！！');
    }

    public function cateAction( )
    {
        $list = Service::getInstance('push')->cate();
        $this->respon(1,$list);
    }

    public function goodsAction( )
    {
    	$cid = $this->getPost( 'cid', 0 );
    	$page = $this->getPost( 'page', 1 );
        $thumb = $this->getPost( 'thumb','1' );
    	$perpage = 20 ;
        $list = Service::getInstance('push')->goodsList( $this->channel, $page , $perpage ,$cid ,$thumb );
        $this->respon(1,$list);
    }

    public function onegoodsAction( )
    {
        $cid = $this->getPost( 'cid', 0 );
        $page = $this->getPost( 'page', 1 );
        $thumb = '1';
        $perpage = 20 ; 
        $list = Service::getInstance('push')->geGoodsList( $this->channel, $page , $perpage ,$cid ,$thumb );
        $this->respon(1,$list);
    }

    public function getOneGoodsByIdAction( ){
        if( $this->isPost() ){
            $thumb   = $this->getPost('thumb','1');
            $goodsId = $this->getPost('goodsId',0) + 0;//兼容接口，不能删除
            $goodsId = $goodsId ? $goodsId : $this->getPost( 'goodsid',0 );//兼容接口，不能删除

            if( !$goodsId ) $this->respon( 0,'参数错误' );

            $goods = Service::getInstance('push')->getOneGoodsById( $this->channel ,$goodsId ,$thumb );
            if( !empty($goods) ){
                $this->respon( 1,$goods );
            }else{
                $this->respon( 0,'商品已经不存在');
            }
        }
    }

    public function xiuhuagoodsAction( )
    {
        $cid = $this->getPost( 'cid', 0 );
        $page = $this->getPost( 'page', 1 );
        $thumb = $this->getPost( 'thumb', '1' );
        $perpage = 20 ; 
        $list = Service::getInstance('push')->xiuhuaGoodsList( $this->channel, $page , $perpage ,$cid ,$thumb );
        $this->respon(1,$list);
    }

    //查询商品库存状态
    public function goodsstatusAction( )
    {
        $goodsid = $this->getPost( 'goodsid', 0 );

        if ( !intval( $goodsid ) ) $this->respon( 0 , "商品id不正确!" );

        $ms = Service::getInstance('orders')->getGoodsBuyStatus( $goodsid );
        if ( $ms != true )
        {
            $this->respon( 1 , array('num'=>0) );
        }

        $this->respon( 1 , array('num'=>1) );
    }


    //购买下架
    public function buyAction( )
    {
    	$goodsid = $this->getPost( 'goodsid', 0 );
    	$uname = $this->getPost( 'uname', "" );
    	$price = $this->getPost( 'price', 0 );
    	$tel = $this->getPost( 'tel', "" );
    	$address = $this->getPost( 'address', "" );
    	$remark = $this->getPost( 'remark', "" );

    	if ( !intval( $goodsid ) ) $this->respon( 0 , "商品id不正确!" );

    	$ms = Service::getInstance('orders')->getGoodsBuyStatus( $goodsid );

        if ( $ms !== true ) $this->respon( 0 , $ms );

    	$data['goodsId'] = $goodsid;
    	$goods = Service::getInstance('goods')->getGoodsInfoById( $goodsid );
    	$data['shopId'] = 0;
    	if (  isset( $goods['shopId'] ) ) $data['shopId'] = $goods['shopId'];

    	//收件人
    	if ( $uname == "" ) $this->respon( 0 , "收件人不能为空!" );
    	$data['uname'] = $uname;
   
    	if ( !$tel ) $this->respon( 0 , "收件人不能为空!" );
    	$data['tel'] = $tel;

    	if ( !$address ) $this->respon( 0 , "收件地址不能为空!" );
    	$data['address'] = $address;

    	if ( $remark ) $data['remark'] = $remark;

    	$orderId = Service::getInstance('push')->buy( $data , $goods , $this->channel ) ;
        
        if ( $orderId ) {
            $orderPrice = Service::getInstance('orders')->addOrderPrice(array('orderId'=>$orderId,'updateTime'=>time()));
        }

    	if ( $orderId )
    	{
    	    Service::getInstance('goods')->change($goodsid,array('status'=>2));
    		$this->respon( 1 , array( 'orderId'=>$orderId ) );
    	}

    	$this->respon( 0 , "购买失败!" );

    }

    public function makeOrderAction( )
    {
        //'[{"goodsid":"1000","num":"3","price":"500"},{"goodsid":"1001","num":"2","price":"600"}]'
        $goodsData = $this->getPost( 'goodsData', '' );
        $goodsData = json_decode( $goodsData,true );

        if( !$goodsData || !isset($goodsData['0']['goodsid']) || !isset($goodsData['0']['num']) || !isset($goodsData['0']['price']) ) $this->respon(0,'商品参数错误');

        $uname   = $this->getPost( 'uname', '' );
        $tel     = $this->getPost( 'tel', '' );
        $address = $this->getPost( 'address', '' );
        $remark  = $this->getPost( 'remark', '' );

        if ( $uname == "" ) $this->respon( 0 , "收件人不能为空!" );
        $data['uname'] = $uname;
   
        if ( !$tel ) $this->respon( 0 , "收件人不能为空!" );
        $data['tel'] = $tel;

        if ( !$address ) $this->respon( 0 , "收件地址不能为空!" );
        $data['address'] = $address;

        if ( $remark ) $data['remark'] = $remark;

        $userId = Service::getInstance('orders')->getUser( $data['tel'], $data['uname'] );
        $data['userId']     = $userId;
        $data['orderCode']  = Service::getInstance('orders')->getOrderCode(1,3);
        $data['createTime'] = time();
        $data['payTime']    = time();
        $data['deliverWay'] = 1;
        $data['channel']    = $this->channel;
        $data['isPay']      = 1;
        $data['isDeliver']  = 1;
        $data['sellType']   = 4;
        //为了满足本地测试
        $data['goodsId'] = 0;
        $data['payw']    = 0;
        $data['payType'] = 0;
        $data['payBank'] = 0;

        $shopIds = array();
        $success = array();
        $fail    = array();
        foreach( $goodsData as $k => $row ){
            Service::getInstance('goods')->change( $row['goodsid'],
                array('status'=>1,'goodsStock'=>10,)
            );
            $ms = Service::getInstance('orders')->getGoodsBuyStatus( $row['goodsid'] );
            if ( $ms !== true ){
                $fail['soldOut'][] = $row['goodsid'];
                continue;
            }
            $goods = Service::getInstance('goods')->getGoodsInfoById( $row['goodsid'] );
            if( $goods['goodsStock'] < $row['num'] ){
                $fail['underStock'][] = $row['goodsid'];
                continue;
            }
            if( !in_array( $goods['shopId'],$shopIds ) ){
                //根据店铺判断订单类型
                if( $goods['shopId'] == 105 ){
                    $data['orderType'] = 4;
                    $data['isBuyer']   = 0;
                }else{
                    $data['orderType'] = 3;
                    $data['isBuyer']   = 1;
                }
                $data['shopId']     = $goods['shopId'];
                $shop = Service::getInstance('push')->getShop( intval( $goods['shopId'] ) );
                $ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop( $this->channel,$goods['shopId'] );
                $price = round( $goods['purchPrice'] * $shop['ptimes'] * $ctimes );
                $data['price'] = $price;

                $orderId = Service::getInstance('orders')->add( $data , $this->channel );

                if( $orderId ){
                    $orderPrice = Service::getInstance('orders')->addOrderPrice(
                        array('orderId'=>$orderId,'updateTime'=>time() )
                        );
                    if( $goods['goodsStock'] == $row['num'] ){
                        Service::getInstance('goods')->change(
                            $row['goodsid'],array('status'=>2,'goodsStock'=>0)
                            );
                    }

                    $shopIds[$orderId] = $goods['shopId'];
                }
                
            }else{
                foreach( $shopIds as $k => $v ){
                    if( $v == $goods['shopId'] ){
                        $orderId = $k;
                        break;
                    }
                }
            }

            if( $orderId ){
                //添加订单商品
                $orderGoods = array(
                    'order_id'        => $orderId,
                    'goods_id'        => $row['goodsid'],
                    'shop_id'         => $goods['shopId'],
                    'customer_id'     => $userId,
                    'goods_name'      => $goods['name'],
                    'goods_image'     => $goods['goodsOneImg'],
                    'goods_price'     => $goods['purchPrice'],
                    'goods_number'    => $row['num'],
                    'goods_pay_price' => $row['price'],
                );
                
                $addOrderGoods = Service::getInstance('orders')->addOrderGoods($orderGoods);
                //装载返回信息
                $success[$orderId][] = $row['goodsid'];
            }else{
                $fail['sysError'][] = $row['goodsid'];
            }
        }
        
        $result = array();
        $nums = count($shopIds);
        if( $nums > 1 ){
            $result['notice'] = '为了发货方便，我们将订单拆为'.$nums.'单';
        }
        
        if( !empty( $fail ) ){
            if( !empty( $success ) ){
                $result['msg'] = '部分成功';
                $result['success'] = $success;
                $result['error']   = $fail;
            }else{
                $result['msg'] = '全部失败';
                $result['error'] = $fail;
            }
        }else{
            $result['msg'] = '全部成功';
            $result['success'] = $success;
        }
        unset($shopIds);
        Factory::p($result);
        return false;
    }

    public function getOrderExpressAction( )
    {
    	$orderId = $this->getPost( 'orderId', 0 );
    	if ( !intval( $orderId ) ) $this->respon( 0 , "订单错误" );

    	$Express = Service::getInstance('orders')->getOrderExpress( $orderId );
    	$this->respon( 1 , $Express );
    }
   
}