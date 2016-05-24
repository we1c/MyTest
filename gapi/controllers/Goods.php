<?php

class GoodsController extends BaseController
{
    
    private $_channel;
    private $_goodsId;
    
    public function init()
    {	
        parent::init();
       
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        
//         $token = empty($_COOKIE['openId']) ? '' : $_COOKIE['openId'];
//         $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');

//         $token = Util::strcode($token, $key, 'decode');
        
        $gid = intval( $this->getQuery('gid',0) );
        $channel = intval( $this->getQuery('channel',0) );
        $this->_goodsId = $gid;
        $this->_channel = $channel;
        if (!$this->openId) {
            if ( !empty( $gid ) && !empty( $channel ) ) {
                Util::weichatlogin( "http://m.1ge.com/goods/index?gid={$gid}&channel={$channel}" ,$this->_appId );
            } else {
                Util::weichatlogin("http://m.1ge.com/goods/index?gid={$gid}" , $this->_appId );
            }
            return false;
        }
    }
    
    public function indexAction() {
        if ( !empty( $this->_channel ) && !empty( $this->_goodsId ) ) {
            $data = Service::getInstance('goods')->getBuyPushGoods( $this->_channel, $this->_goodsId ,'800' );
            $this->_view->dinfo = array('gid'=>$this->_goodsId,'channel'=>$this->_channel);
        } else {
            $data = Service::getInstance('goods')->getGoodsInfoById( intval( $this->_goodsId ),'gapi' );
            $data['goodsPrice'] = $data['price'] ? $data['price'] : $data['purchPrice'] * $data['mtimes'];
            unset( $data['price'] );
            unset( $data['purchPrice'] );
            unset( $data['mtimes'] );
            unset( $data['ptimes'] );
        }
        if ( $data['showPrice'] ) {
            $accessToken = $this->wei->getAccessToken();
            require_once("jssdk.php");
            $jssdk = new JSSDK("{$this->_appId}", "{$this->_appSecret}" ,$accessToken);
            $signPackage = $jssdk->GetSignPackage();
            $this->_view->jsdata = $signPackage;
        }
        $user = Service::getInstance('user')->getUserByopenid( $this->openId );
        $this->_view->user = $user;
        $this->_view->data = $data ? $data : array();
    }
    
    //商家
    public function getPackageAction() {
        /**统一支付是JSAPI/NATIVE/APP各种支付场景下生成支付订单，返回预支付订单号的接口，目前微信支付所有场景均使用这一接口
                                 统一支付中以下参数从配置中获取，或由类自动生成，不需要用户填写
                                 微信支付统一下单接口文档：http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_1
         */
        $id    = $this->getPost('data');
        $number = $this->getPost('number',1);
        $gInof = Service::getInstance('goods')->getGoodsInfoById( $id );
        if ( $gInof['status'] != 1 ) {
            $this->respon( 0, '该商品已下架,不能下单!' );
        }
        $user = Service::getInstance('user')->getUserByopenid( $this->openId );

        $orderdata = array(
            'orderCode'  =>Service::getInstance('orders')->getOrderCode(1,1),
            'price'      => $gInof['price']? round( $gInof['price'] ): round( $gInof['purchPrice'] * $gInof['mtimes'] ),
            'userId'     =>$user['id'],
            'uname'      =>'',
            'payWay'     =>1,
            'deliverWay' =>1,
            'createTime' =>time(),
            'goodsId'    =>$gInof['id'],
            'shopId'     =>$gInof['shopId'],
            'isPay'      =>0,
            'orderType'  =>1,
            'sellType'   =>2
        );
        $res = Service::getInstance('orders')->add( $orderdata );

        if ( !$res ) {
            $this->respon( 0, '创建订单失败' );
        }else {
            //减少库存
            Service::getInstance('goods')->sellGoods($id,$number);

            //添加订单商品
            $orderGoods = array(
                'order_id'        => $res,
                'goods_id'        => $id,
                'shop_id'         => $gInof['shopId'],
                'customer_id'     => 0,
                'goods_name'      => $gInof['name'],
                'goods_image'     => $gInof['goods_image'],
                'goods_price'     => $gInof['price'],
                'goods_number'    => $number,
                'goods_pay_price' => $gInof['price']? round( $gInof['price'] ): round( $gInof['purchPrice'] * $gInof['mtimes'] ),
            );
            $addOrderGoods = Service::getInstance('orders')->addOrderGoods( $orderGoods );

        }

        $this->_setCookie( $res );
        $order = Service::getInstance('orders')->getOrderInfo( $res );
        
        $price = $gInof['price']? round( $gInof['price'] ): round( $gInof['purchPrice'] * $gInof['mtimes'] );

        $rand = md5(time() . mt_rand(0,1000));
        $param["appid"] = "{$this->_appId}";
        $param["openid"] = "{$this->openId}";
        $param["mch_id"] = "1255470901";
        $param["nonce_str"] = "$rand";
        $param["body"] = "支付{$gInof['name']}";
        $param["out_trade_no"] = $order['orderCode'];
        $param["total_fee"] = $price * 100;
        $param["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        $param["notify_url"] = "http://m.1ge.com/goods/callback";
        $param["trade_type"] = "JSAPI";
        //签名算法：http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=4_3
        $signStr = 'appid='.$param["appid"]."&body=".$param["body"]."&mch_id=".$param["mch_id"]."&nonce_str=".$param["nonce_str"]."&notify_url=".$param["notify_url"]."&openid=".$param["openid"]."&out_trade_no=".$param["out_trade_no"]."&spbill_create_ip=".$param["spbill_create_ip"]."&total_fee=".$param["total_fee"]."&trade_type=".$param["trade_type"];
        $signStr = $signStr."&key=bbfd25619f1e62ef5458f26a391d19f1";
        $param["sign"] = strtoupper(MD5($signStr));
        $data = Util::arrayToXml($param);
        $postResult = Util::postCurl("https://api.mch.weixin.qq.com/pay/unifiedorder",$data);
        $postObj = Util::xmlToArray( $postResult );
        
        $msg = $postObj['return_code'];
        
        if($msg == "SUCCESS"){
            $result["timestamp"] = time();
            $result["nonceStr"] = $postObj['nonce_str'];  //不加""拿到的是一个json对象
            $result["package"] = "prepay_id=".$postObj['prepay_id'];
            $result["signType"] = "MD5";
    
            /**支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                                        备注：prepay_id 通过微信支付统一下单接口拿到，paySign 采用统一的微信支付 Sign 签名生成方法，注意这里 appId 也要参与签名，
             appId 与 config 中传入的 appId 一致，即最后参与签名的参数有appId, timeStamp, nonceStr, package, signType。*/
            $paySignStr = 'appId='.$this->_appId.'&nonceStr='.$result["nonceStr"].'&package='.$result["package"].'&signType='.$result["signType"].'&timeStamp='.$result["timestamp"];
            $paySignStr = $paySignStr."&key=bbfd25619f1e62ef5458f26a391d19f1";
    
            $result["paySign"] = strtoupper(MD5($paySignStr));
            $result['appId'] = "{$this->_appId}";
            $result['order'] = $res;
            $this->respon( 1, $result );
        }else{
            Service::getInstance('orders')->delOrder( $res );
            $this->respon( 0, '购买失败' );
        }
    }
    
    
    //分销商
    public function getDistPackageAction() {
        /**统一支付是JSAPI/NATIVE/APP各种支付场景下生成支付订单，返回预支付订单号的接口，目前微信支付所有场景均使用这一接口
                                 统一支付中以下参数从配置中获取，或由类自动生成，不需要用户填写
                                 微信支付统一下单接口文档：http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_1
         */
        $gid = $this->getPost('gid');
        $channel = $this->getPost('channel');
        $number = $this->getPost('number',1);
        
//         if ( $gid != $this->_goodsId && $channel != $this->_channel ) {
//             $this->respon( 0, '信息错误' );
//         }
        
        $gInof = Service::getInstance('goods')->getBuyPushGoods( $channel, $gid );
        if ( $gInof['status'] != 1 ) {
            $this->respon( 0, '该商品已下架,不能下单!' );
        }
        $user = Service::getInstance('user')->getUserByopenid( $this->openId );
        if( $gInof['shopId'] == 105 ){
            $orderType = 4;
            $isBuyer   = 0;
        }else{
            $orderType = 3;
            $isBuyer   = 1;
        }
        $orderdata = array(
            'orderCode'  =>Service::getInstance('orders')->getOrderCode(1,1),
            'price'      => $gInof['goodsPrice'],
            'userId'     =>$user['id'],
            'uname'      =>'',
            'payWay'     =>1,
            'deliverWay' =>1,
            'createTime' =>time(),
            'goodsId'    =>$gInof['id'],
            'shopId'     =>$gInof['shopId'],
            'channel'    =>$channel,
            'isPay'      =>0,
            'orderType'  =>$orderType,
            'isBuyer'    =>$isBuyer,
            'sellType'   =>5
        );
        $res = Service::getInstance('orders')->add( $orderdata );
        if ( $res ) {
            $orderPrice = Service::getInstance('orders')->addOrderPrice(array('orderId'=>$res,'updateTime'=>time()));

            //减少库存
            Service::getInstance('goods')->sellGoods($gid,$number);

            //添加订单商品
            $orderGoods = array(
                'order_id'        => $res,
                'goods_id'        => $gid,
                'shop_id'         => $gInof['shopId'],
                'customer_id'     => 0,
                'goods_name'      => $gInof['name'],
                'goods_image'     => $gInof['goods_image'],
                'goods_price'     => $gInof['purchPrice'],
                'goods_number'    => $number,
                'goods_pay_price' => $gInof['goodsPrice'],
            );
            $addOrderGoods = Service::getInstance('orders')->addOrderGoods( $orderGoods );

        }
        if ( !$orderPrice ) {
            $this->respon( 0, '创建订单失败' );
        }
        $this->_setCookie( $res );
        $order = Service::getInstance('orders')->getOrderInfo( $res );
        
        $price = $gInof['goodsPrice'];

        $rand = md5(time() . mt_rand(0,1000));
        $param["appid"] = "{$this->_appId}";
        $param["openid"] = "{$this->openId}";
        $param["mch_id"] = "1255470901";
        $param["nonce_str"] = "$rand";
        $param["body"] = "支付{$gInof['name']}";
        $param["out_trade_no"] = $order['orderCode'];
        $param["total_fee"] = $price * 100;
        $param["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        $param["notify_url"] = "http://m.1ge.com/goods/callback";
        $param["trade_type"] = "JSAPI";
        //签名算法：http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=4_3
        $signStr = 'appid='.$param["appid"]."&body=".$param["body"]."&mch_id=".$param["mch_id"]."&nonce_str=".$param["nonce_str"]."&notify_url=".$param["notify_url"]."&openid=".$param["openid"]."&out_trade_no=".$param["out_trade_no"]."&spbill_create_ip=".$param["spbill_create_ip"]."&total_fee=".$param["total_fee"]."&trade_type=".$param["trade_type"];
        $signStr = $signStr."&key=bbfd25619f1e62ef5458f26a391d19f1";
        $param["sign"] = strtoupper(MD5($signStr));
        $data = Util::arrayToXml($param);
        $postResult = Util::postCurl("https://api.mch.weixin.qq.com/pay/unifiedorder",$data);
        $postObj = Util::xmlToArray( $postResult );
        
        $msg = $postObj['return_code'];
        
        if($msg == "SUCCESS"){
            $result["timestamp"] = time();
            $result["nonceStr"] = $postObj['nonce_str'];  //不加""拿到的是一个json对象
            $result["package"] = "prepay_id=".$postObj['prepay_id'];
            $result["signType"] = "MD5";
    
            /**支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                                        备注：prepay_id 通过微信支付统一下单接口拿到，paySign 采用统一的微信支付 Sign 签名生成方法，注意这里 appId 也要参与签名，
             appId 与 config 中传入的 appId 一致，即最后参与签名的参数有appId, timeStamp, nonceStr, package, signType。*/
            $paySignStr = 'appId='.$this->_appId.'&nonceStr='.$result["nonceStr"].'&package='.$result["package"].'&signType='.$result["signType"].'&timeStamp='.$result["timestamp"];
            $paySignStr = $paySignStr."&key=bbfd25619f1e62ef5458f26a391d19f1";
    
            $result["paySign"] = strtoupper(MD5($paySignStr));
            $result['appId'] = "{$this->_appId}";
            $result['order'] = $res;
            $this->respon( 1, $result );
        }else{
            Service::getInstance('orders')->delOrder( $res );
            $this->respon( 0, '购买失败' );
        }
    }

    public function callbackAction(){
    
    }
    public function setstatusAction(){
        $oid = $this->getPost('oid');
        $res = Service::getInstance('orders')->setOrderStatus( array( 'isPay'=>1,'isDeliver'=>1,'payTime'=>time() ), $oid );
        if ( $res >= 0 ) {
            $order = Service::getInstance('orders')->getOrderInfo( $oid );
            Service::getInstance('goods')->change( $order['goodsId'], array('status'=>2) );
            if ( $this->_channel && $this->_goodsId ) {
                $data = Service::getInstance('goods')->getBuyPushGoods( $this->_channel, $this->_goodsId );
                Service::getInstance('push')->delPush( $data['pid'] );
            }
            $this->respon( 1, '成功' );
        } else {
            $this->respon( 0, '失败' );
        }
    }
    
    public function cancelOrderAction() {
        $id = $this->getPost('id');
        $res = Service::getInstance( 'orders' )->delOrder( $id );
        $this->respon( 1, '操作成功' );
    }
    
    public function signoutAction(){
        setcookie('openId', false, 0, '/');
        return false;
    }
    
    public function bindingoneAction() {
        
    }
    public function bindingtwoAction() {
        $phone = $this->getQuery('phone');
        $this->_view->phone = $phone;
    }
    public function setbindingAction() {
        $phone = $this->getPost('phone');
        $code = $this->getPost('captcha');
        if(!$code) {
            $this->respon( 0, '验证码不能为空' );
        }
        if ( !Service::getInstance('user')->SMS( $phone , $code ) ) {
            $this->respon( 0 , "验证码不正确!" );
        }
        
        $res = Service::getInstance('user')->updateUserByOpenid( array( 'tel'=>$phone ), $this->openId );
        if ( $res >= 0 ) {
            $this->respon( 1, '绑定成功' );
        } else {
            $this->respon( 0, '绑定失败' );
        }
    }
    
    public function userinfoAction() {
        $oid = $this->_getCookie( $this->openId );
        $order = Service::getInstance('orders')->getOrderInfo( $oid );
        $gInof = Service::getInstance('goods')->getGoodsInfoById( $order['goodsId'] );
        $province = Service::getInstance('shop')->getProvince();
        $this->_view->province = $province;
        $this->_view->goods = $gInof;
        $this->_view->order = $order;
    }
    
    //城市列表
    public function cityAction() {
        $province = $this->getPost('province');
        $city = Service::getInstance('shop')->getCityByName($province);
        if($city) {
            $this->respon(1,$city);
        } else {
            $this->respon(0,'查询失败');
        }
    
    }
    
    //地区列表
    public function areaAction() {
        $city = $this->getPost('city');
        $area = Service::getInstance('shop')->getCityByName($city);
        if($city) {
            $this->respon(1,$area);
        } else {
            $this->respon(0,'查询失败');
        }
    }
    public function consigneeAction() {
        $name = $this->getPost('name');
        $tel = $this->getPost('tel');
        $province = $this->getPost('province');
        $city = $this->getPost('city');
        $area = $this->getPost('area');
        $address = $this->getPost('address');
        $remark = $this->getPost('remark');
        if ( !$name ) $this->respon( 0, '收货人姓名不能为空' );
        if ( !$tel ) $this->respon( 0, '收货人电话不能为空' );
        if ( !$address ) $this->respon( 0, '收货人地址不能为空' );
        $addr = '';
        if ( $province ) {
            $addr .= $province;
        }
        if ( $city ) {
            $addr .= $city;
        }
        if ( $area ) {
            $addr .= $area;
        }
        $addr = $addr.$address;
        $oid = $this->_getCookie( $this->openId );
        $res = Service::getInstance('orders')->setOrderStatus( array( 'uname'=>$name,'tel'=>$tel,'address'=>$addr,'remark'=>$remark ), $oid );
        if ( $res >= 0 ) {
            $this->respon( 1, '成功' );
        } else {
            $this->respon( 0, '失败' );
        }
    }
    
    /**
     * 发送验证码
     * @param post:account
     * @return boolean
     */
    public function verifyAction()
    {
        if($this->isPost()) {
            $phone = $this->getPost( 'phone' );
            if ( !Util::isValidMobile( $phone ) ) $this->respon( 0 , "手机号不合法!" );
            if ( Service::getInstance( 'user' )->sendsmsCode( $phone ) )
            {
                $this->respon( 1, "验证码已发送到您的手机，请查收!" );
            }
            $this->respon( 0, "验证码发送失败，请重试!" );
        }
        return false;
    }
    
    protected function _setCookie($data, $keep = true)
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = Util::strcode($data, $key, 'encode');
        $expired = $keep ? time() + 86400 * 30 : 0;
    
        setcookie('order_'.$this->openId, $token, $expired, '/');
    }
    protected function _getCookie($data, $keep = true)
    {
        $token = empty( $_COOKIE["order_{$data}"] ) ? '' : $_COOKIE["order_{$data}"];
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');

        $token = Util::strcode($token, $key, 'decode');
        return $token;
    }

    public function getnextimgAction( ){
        $id=$this->getPost('id');
        $offset=$this->getPost('offset');
        $size = $this->getPost('size','800');
        $one_imgurl=Service::getInstance('goods')->getGoodsImgHashByGoodsId($id,$offset,$size);

        $this->respon(1,$one_imgurl);
    }
}