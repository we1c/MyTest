<?php

class OrderController extends BaseController {

    public function init() {
        parent::init();
        if (!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    }

    //订单列表
    public function indexAction() {
        $perpage = $this->getQuery('perpage', 15);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $orderType = $this->getQuery('orderType','');
        $dist = $this->getQuery('dist', '');
        $keyword = trim($this->getQuery('keyword', ''));
        $starttime = $this->getQuery('starttime', "2016-01-01");
        $endtime = $this->getQuery('endtime', date('Y-m-d', time()));
        $searchType = $this->getQuery('searchType', '1');
        $topLevel = $this->getQuery('topLevel', 'pingtai');
        $showType = $this->getQuery('showType','waitDeliver');
        //获取订单列表
        $data = Service::getInstance('orders')->orderslist($page, $perpage, $keyword, $dist, $starttime, $endtime, $searchType,$orderType,$showType);
//        echo '<pre>';
//        print_r($data);
//        exit;
        if ($data['list']) {
            foreach ($data['list'] as $k => $v) {
                $data['list'][$k]['imageurl'] = Service::getInstance('goods')->getGoodsOneImg(intval($v['goodsId']));
                $channel = Service::getInstance('goods')->getChannelById($v['channel']);
                $data['list'][$k]['cname'] = $channel['name'];
                $data['list'][$k]['c_devId'] = $channel['devId'];
                $data['list'][$k]['shopName'] = Service::getInstance('shop')->getShopNameById(intval($v['shopId']));
                if($v['isDelete'] == 1){
                    $data['list'][$k]['payStatus'] = '已删除';
                }else{
                    if ($v['isDel'] == 1) {
                        $data['list'][$k]['payStatus'] = '已取消';
                    } elseif ($v['isTake'] == 2) {
                        $data['list'][$k]['payStatus'] = '已签收';
                    } elseif (($v['isTake'] == 1 ) || ( $v['isTake'] == 0 && $v['isDeliver'] == 2 )) {
                        $data['list'][$k]['payStatus'] = '待签收';
                    } elseif (( $v['isTake'] == 0 && $v['isDeliver'] == 1 ) || ( $v['isDeliver'] == 0 && $v['isPay'] == 1 )) {
                        $data['list'][$k]['payStatus'] = '待发货';
                    } elseif ($v['isPay'] == 0) {
                        $data['list'][$k]['payStatus'] = '待付款';
                    } else {
                        $data['list'][$k]['payStatus'] = '无效';
                    }
                }                
                if ($v['payTime'] >= strtotime(date("Y-m-d"))) {
                    $todayData['list'][$k] = $data['list'][$k];
                } else {
                    $earlyData['list'][$k] = $data['list'][$k];
                }
            }
        }
        $dis = Service::getInstance('distributor')->getAllDis();

        $this->_view->dis = $dis;
        !empty($todayData['list']) ? $this->_view->todaylist = $todayData['list'] : "";
        !empty($earlyData['list']) ? $this->_view->earlylist = $earlyData['list'] : "";
        $this->_view->perpage = $perpage;
        $this->_view->keyword = $keyword;
        $this->_view->starttime = $starttime;
        $this->_view->endtime = $endtime;
        $this->_view->orderType = $orderType;
        $this->_view->dist = $dist;
        $this->_view->searchType = $searchType;
        $this->_view->topLevel = $topLevel;
        $this->_view->showType = $showType;
        $this->_view->total = $data['total'];
        $pageObj = new Page($data['total'][$showType]['totalNum'], $perpage, $showpage, $page, '', array('order', 'index', 'keyword' => $keyword, 'dist' => $dist, 'perpage' => $perpage, 'starttime' => $starttime, 'endtime' => $endtime, 'searchType' => $searchType,'orderType'=>$orderType,'topLevel'=>$topLevel,'showType'=>$showType));
        $this->_view->pagebar = $pageObj->showPage();

        // $url = "/order/index?page=__page__";
        // if($keyword != '') $url .= "&keyword={$keyword}";
        // if($status != '') $url .= "&status={$status}";
        // if($dist != '') $url .= "&dist={$dist}";
        // $this->_view->pagebar = Util::buildPagebar( $data['count']['totalNum'], $perpage, $page, $url );
    }

    //添加订单
    public function addAction() {
        if ($this->isPost()) {
            $shopId = $this->getPost('shop');
            $paytype = $this->getPost('paytype');
            $deliverWay = $this->getPost('deliver');
            $remark = $this->getPost('remark');
            $consignee = $this->getPost('consignee');
            $phone = $this->getPost('phone');
            $province = $this->getPost('province');
            $city = $this->getPost('city');
            $area = $this->getPost('area');
            $address = $this->getPost('address');
            $goodsId = $this->getPost('goodsId');
            $userId = Service::getInstance('orders')->getUser($phone, $consignee);
            foreach ($goodsId as $k => $v) {
                $gInof = Service::getInstance('goods')->getGoodsById($v);
                if ($gInof) {
                    $data[] = array(
                        'orderCode' => Service::getInstance('orders')->getOrderCode($k, 2),
                        'price' => $gInof['purchPrice'],
                        'userId' => $userId,
                        'uname' => $consignee,
                        'payWay' => $paytype,
                        'deliverWay' => $deliverWay,
                        'createTime' => time(),
                        'payTime' => time(),
                        'tel' => $phone,
                        'remark' => $remark,
                        'goodsId' => $v,
                        'shopId' => $gInof['shopId'],
                        'province' => $province,
                        'city' => $city,
                        'area' => $area,
                        'address' => $address,
                        'isPay' => 1,
                        'isDeliver' => 1,
                        'orderType' => 2
                    );
                }
            }
            if ($data) {
                $res = Service::getInstance('orders')->addOrder($data);
                if ($res) {
                    Service::getInstance('goods')->change($goodsId, array('status' => 2));
                    $this->flash('/order/index', '下单成功');
                } else {
                    $this->flash('/order/index', '下单失败');
                }
            } else {
                $this->flash('/order/idnex', '下单失败');
            }
        }
        $shop = Service::getInstance('shop')->getList();
        $province = Service::getInstance('shop')->getProvince();
        $goods = Service::getInstance('goods')->getGoodsByShopId($shop[0]['id']);
        $this->_view->shop = $shop;
        $this->_view->province = $province;
        $this->_view->goods = $goods;
    }

    //店铺下的商品列表
    public function getGoodsAction() {
        $id = $this->getPost('id');
        $goods = Service::getInstance('goods')->getGoodsByShopId($id);
        $this->respon(1, $goods);
    }

    //商品详情
    public function getGoodsInfoAction() {
        $id = $this->getPost('id');
        $goods = Service::getInstance('goods')->getGoodsInfoById($id);
        $this->respon(1, $goods);
    }

    /**
     * 查询快递数据
     * @return json 快递数据
     */
    public function getExpressAction() {
        $express = $this->getPost('express');
        $expressNum = $this->getPost('expressNum');
        $id = $this->getPost('id');

        $obj = new Queryorder2($this->redis);

        $result = $obj->orderTracesSubByJson($express, $expressNum, 3600 * 3);

        $result = json_decode($result, true);

        if ( isset($result['Reason']) ) {
            $this->respon(0,$result['Reason']);
        }

        if ($result['State'] == 3) {
            // 更新快递签收
            $info = Service::getInstance('orders')->edit(array('isTake' => 2), $id);
        }

        if ($result) {
            $this->respon(1, $result);
        } else {
            $this->respon(0, '失败');
        }
    }

    //订单详情
    public function detailAction() {
        $id = $this->getPost('id');
        $info = Service::getInstance('orders')->getOrderDetail($id);
        $info['order'] = Service::getInstance('orders')->getOrder($id);
        $info['price'] = Service::getInstance('orders')->getOrderPrice($id);
        $province = Service::getInstance('shop')->getProvince();
        $city = Service::getInstance('shop')->getCity($info['order']['province']);
        $area = Service::getInstance('shop')->getCity($info['order']['city']);
        $payway = Service::getInstance('orders')->getPayWay();
        $paytype = Service::getInstance('orders')->getPayType($info['order']['payw']);
        $paybank = Service::getInstance('orders')->getPayBank($info['order']['payType']);
        $express = Service::getInstance('orders')->getAllExpress();
        // //$obj = new  Queryorder('e23ab149f6a5ab61');
        // $obj = new Queryorder2($this->redis);
        // foreach ($express as $key => $value) {
        // 	if($value['id'] == $info['express']['expressId']){
        // 		$resultUrl = $obj->orderTracesSubByJson($value['name'],$info['express']['number'] );
        // 	}
        // }
        // $resultUrl = json_decode($resultUrl, true);
        // if (isset($resultUrl)) {
        //   		$info['opt']['orderurl'] = $resultUrl;
        // }else{
        // 	$info['opt']['orderurl'] = '';
        // }

        $info['opt']['province'] = $province;
        $info['opt']['city'] = $city;
        $info['opt']['area'] = $area;
        $info['opt']['payway'] = $payway;
        $info['opt']['paytype'] = $paytype;
        $info['opt']['paybank'] = $paybank;
        $info['opt']['express'] = $express;

        if ($info) {
            $this->respon(1, $info);
        } else {
            $this->respon(0, '失败');
        }


        // $keyword = $this->getQuery('keyword');
        // $status = $this->getQuery('status');
        // $dist = $this->getQuery('dist');
        //    $id = $this->getQuery('id');
        //    $info = Service::getInstance('orders')->getOrderDetail($id);
        //    $this->_view->keyword = $keyword;
        //    $this->_view->status = $status;
        //    $this->_view->dist = $dist;
        //    $this->_view->info = $info; 
    }

    //发货
    public function deliverAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            $express = $this->getPost('express');
            $expressNum = $this->getPost('expressNum');
            $real_freight = $this->getPost('real_freight');
            $real_certificate = $this->getPost('real_certificate');
            $real_pack = $this->getPost('real_pack');
            $real_mount = $this->getPost('real_mount');
            $real_other = $this->getPost('real_other');
            $data = array(
                'orderId' => $id,
                'expressId' => $express,
                'number' => $expressNum
            );
            $dataPrice = array(
                'real_freight' => $real_freight,
                'real_certificate' => $real_certificate,
                'real_pack' => $real_pack,
                'real_mount' => $real_mount,
                'real_other' => $real_other,
                'updateTime' => time()
            );
            $resPrice = Service::getInstance('orders')->editOrderPrice($dataPrice, $id);
            $isExpress = Service::getInstance('orders')->getOrderExpress($id);
            if ($isExpress) {
                $result = Service::getInstance('orders')->editExpress($data);
            } else {
                $result = Service::getInstance('orders')->addExpress($data);
            }
            if ($result) {
                $res = Service::getInstance('orders')->doDeliver($id);
                if ($res >= 0) {
                    echo"<script>alert('成功');window.location.href=document.referrer</script>";
                } else {
                    Service::getInstance('orders')->delExpress($result);
                    echo"<script>alert('失败');window.location.href=document.referrer</script>";
                }
            } else {
                echo"<script>alert('失败');window.location.href=document.referrer</script>";
            }
        }
// 	        $id = $this->getPost('id');
// 	        $name = $this->getPost('name');
// 	        $number = $this->getPost('number');
// // 	        if ( !intval( $id ) ) 
// // 	            $this->flash( '/order/index', '订单信息错误' );
// // 	        if ( !trim( $name ) ) 
// // 	            $this->flash( '/order/index', '请填写快递公司名称' );
// // 	        if ( !trim( $number ) ) 
// // 	            $this->flash( '/order/index', '请填写快递单号' );
// 	        $expressId = Service::getInstance('orders')->getExpress( $name );
// 	        $data = array(
// 	            'orderId'=>$id,
// 	            'expressId'=>$expressId,
// 	            'number'=>$number
// 	        );
// 	        $result = Service::getInstance('orders')->addExpress( $data );
// 	        if ( $result ) {
// 	            $res = Service::getInstance('orders')->doDeliver( $id );
// 	            if ( $res >= 0 ) {
// 	                $this->flash( '/order/index', '发货成功' );
// 	            } else {
// 	                Service::getInstance('orders')->delExpress( $result );
// 	                $this->flash( '/order/index', '发货失败' );
// 	            }
// 	        } else {
// 	            $this->flash( '/order/index', '发货失败' );
// 	        }
// 	    }
// 	    $id = $this->getQuery('id');
// 	    $this->_view->oid = $id;
    }

    //删除订单
    public function delAction() {
        $orderId = $this->getQuery('id');
        $trueDel = $this->getQuery('trueDel','');
        if (!$orderId) return false;

        //判断订单结算状态
        $checkAcc = Service::getInstance('orders')->getAccountStatus( $orderId );
        if ( $checkAcc != 0 && $checkAcc != 1 ) {
            echo "<script>alert('订单已经结算,无法删除');window.location.href=document.referrer</script>";
        }

        //变量trueDel有值则是物理删除订单
        if($trueDel == 1){
            $success = Service::getInstance("orders")->delOrder($orderId);
        }else{            
            $brush = Service::getInstance('orders')->checkBrush($orderId);
            if (!$brush) {
                $order = Service::getInstance("orders")->getOrderNum($orderId);
                $res = Service::getInstance("goods")->addStock($order['goodsId'], $order['number']);
            }
            $success = Service::getInstance("orders")->deleteOrder($orderId);
        } 
        if ($success) {
            echo "<script>alert('成功');window.location.href=document.referrer</script>";
        } else {
            echo "<script>alert('失败');window.location.href=document.referrer</script>";
        }
    }

    //编辑订单
    public function editAction() {
        $sub1 = $this->getPost('sub1');
        $sub3 = $this->getPost('sub3');
        $sub4 = $this->getPost('sub4');
        if ($this->isPost()) {
            unset($data);
            $id = $this->getPost('id');
            if (!empty($sub1)) {
                $consignee = $this->getPost('consignee');
                $phone = $this->getPost('phone');
                $province = $this->getPost('province');
                $city = $this->getPost('city');
                $area = $this->getPost('area');
                $address = $this->getPost('address');
                // $payway = $this->getPost('payway');
                // $paytype = $this->getPost('paytype');
                // $paybank = $this->getPost('paybank');
                // $freight = $this->getPost('freight');
                // $remark = $this->getPost('remark');
                // $sellerremark = $this->getPost('sellerremark');
                // $orderPrice = $this->getPost('orderPrice');
                // $price_certificate = $this->getPost('price_certificate');
                // $price_pack = $this->getPost('price_pack');
                // $price_mount = $this->getPost('price_mount');
                // $price_other = $this->getPost('price_other');
                $data = array(
                    // 'price' => $orderPrice,
                    'uname' => $consignee,
                    'tel' => $phone,
                    'province' => $province,
                    'city' => $city,
                    'area' => $area,
                    'address' => $address,
                    // 'payw' => $payway,
                    // 'payType' => $paytype,
                    // 'payBank' => $paybank,
                    //'freight'=>$freight,
                    // 'remark' => $remark,
                    // 'sellerremark' => $sellerremark,
                        // 'price_certificate'=>$price_certificate,
                        // 'price_pack'=>$price_pack,
                        // 'price_mount'=>$price_mount,
                        // 'price_other'=>$price_other,
                );
            } elseif (!empty($sub3)) {
                $payway = $this->getPost('payway');
                $paytype = $this->getPost('paytype');
                $paybank = $this->getPost('paybank');
                $data = array(
                    'payw' => $payway,
                    'payType' => $paytype,
                    'payBank' => $paybank,
                );
            } elseif (!empty($sub4)) {
                $remark = $this->getPost('remark');
                $sellerremark = $this->getPost('sellerremark');
                $data = array(
                    'remark' => $remark,
                    'sellerremark' => $sellerremark,
                );
            }
            $res = Service::getInstance('orders')->edit($data, $id);
            if ($res >= 0) {
                echo"<script>alert('成功');window.location.href=document.referrer</script>";
            } else {
                echo"<script>alert('失败');window.location.href=document.referrer</script>";
            }
        }
        // $keyword = $this->getPost('keyword');
        // $status = $this->getPost('status');
        // $dist = $this->getPost('dist');
        //    $id = $this->getPost('id');
        //    $payway = $this->getPost('payway');
        //    $paytype = $this->getPost('paytype');
        //    $paybank = $this->getPost('paybank');
        //    $deliverWay = $this->getPost('deliver');/////////////快递
        //    $remark = $this->getPost('remark');
        //    $sellerremark = $this->getPost('sellerremark');
        //    $freight = $this->getPost('freight');////////////////运费
        //    $realprice = $this->getPost('realprice');/////////////订单金额
        //    $consignee = $this->getPost('consignee');
        //    $phone = $this->getPost('phone');
        //    $province = $this->getPost('province');
        //    $city = $this->getPost('city');
        //    $area = $this->getPost('area');
        //    $address = $this->getPost('address');
        //    $userId = Service::getInstance('orders')->getUser( $phone, $consignee );
        //       $data = array(
        //           'price'=>$realprice,
        //           'userId'=>$userId,
        //           'uname'=>$consignee,
        //           'freight'=>$freight,
        //           'payw'=>$payway,
        //           'payType'=>$paytype,
        //           'payBank'=>$paybank,
        //           'deliverWay'=>$deliverWay,
        //           'tel'=>$phone,
        //           'remark'=>$remark,
        //           'province'=>$province,
        //           'city'=>$city,
        //           'area'=>$area,
        //           'address'=>$address,
        //           'sellerremark'=>$sellerremark,
        //       );
        //     $res = Service::getInstance('orders')->edit( $data, $id );
        //     if ( $res >= 0 ) {
        //         $this->flash( "/order/index/?keyword={$keyword}&status={$status}&dist={$dist}", "编辑成功" );
        //     } else {
        //         $this->flash( "/order/index/?keyword={$keyword}&status={$status}&dist={$dist}", "编辑失败" );
        //     }
        // }
        // $id = $this->getQuery('id');
        // if ( !intval( $id ) ) {
        //     $this->flash( '/order/index/?keyword={$keyword}&status={$status}&dist={$dist}', '无效订单' );
        // }
        // $keyword = $this->getQuery('keyword');
        // $status = $this->getQuery('status');
        // $dist = $this->getQuery('dist');
        // $order = Service::getInstance('orders')->getOrder( intval( $id ) );
        // $province = Service::getInstance('shop')->getProvince();
        // $city = Service::getInstance('shop')->getCity($order['province']);
        // $area = Service::getInstance('shop')->getCity($order['city']);
        // $payway = Service::getInstance('orders')->getPayWay();
        // $paytype = Service::getInstance('orders')->getPayType();
        // $paybank = Service::getInstance('orders')->getPayBank();
        // $this->_view->payway = $payway;
        // $this->_view->paytype = $paytype;
        // $this->_view->paybank = $paybank;
        // $this->_view->info = $order;
        // $this->_view->province = $province;
        // $this->_view->city = $city;
        // $this->_view->area = $area;
        // $this->_view->keyword = $keyword;
        // $this->_view->status = $status;
        // $this->_view->dist = $dist;
    }

    //付款类型
    public function getPayTypeAction() {
        $pid = $this->getPost('pid', 0);
        $paytype = Service::getInstance('orders')->getPayType($pid);
        $this->respon(1, $paytype);
    }

    //收款账号
    public function getPayBankAction() {
        $pid = $this->getPost('pid', 0);
        $paybank = Service::getInstance('orders')->getPayBank($pid);
        $this->respon(1, $paybank);
    }

    //快递公司
    public function getAllExpressAction() {
        $express = Service::getInstance('orders')->getAllExpress();
        $this->respon(1, $express);
    }

    public function takesAction() {
        $id = $this->getPost('id');
        $res = Service::getInstance('orders')->setOrderStatus(array('isTake' => 2), $id);
        if ($res >= 0) {
            $this->respon(1, '成功');
        } else {
            $this->respon(0, '失败');
        }
    }

    //退货
    public function rebackAction() {
        $id = intval($this->getPost('orderId'));
        $goodsId = intval($this->getPost('rebackGoodsId'));
        $ordersGoodsId = intval($this->getPost('ordersGoodsId'));
        $reason = $this->getPost('reason');
        $rebackNum = intval($this->getPost('rebackGoodsNum'));
        $rebackPrice = round($this->getPost('rebackPrice'),2);
        $rebackTime = $this->getPost('rebackTime');
        $accountStatus = intval($this->getPost('accountStatus'));
        $rebackTime = $rebackTime?strtotime($rebackTime):time();
        $sales = $this->getPost('sales');
        $dev = Yaf_Registry::get('developer');
        $operator = $dev['id'];
        if($rebackNum > 0){ 
            $num = Service::getInstance('orders')->getOrdersGoodsNumAndPrice($ordersGoodsId);
            $order_num = Service::getInstance('orders')->getOrderNum($id);
            //退货后剩余商品数量
            $last_num = $num['goods_number'] - $rebackNum;
            $orderlast_num = $order_num['number'] - $rebackNum;
            //退货后该商品支付金额
            $last_price = $num['goods_pay_price'] - $rebackPrice;
            $orderlast_price = $order_num['price'] - $rebackPrice;
            if($last_num >= 0){          
                if($last_num == 0){
                    $reback_state = 1;                                         
                }else{
                    $reback_state = 2;
                }
                if($accountStatus == 2){
                    $changeArr = array('reback_state' => $reback_state);
                }else{
                    $changeArr = array('reback_state' => $reback_state,'goods_number' => $last_num,'goods_pay_price' => $last_price);
                }
                //改变orders_goods表退货状态、数量、金额
                $reback_res = Service::getInstance('orders')->setOrderGoodsInfo($ordersGoodsId,$changeArr);
                //获取order_goods表退货状态不是全部退款的数据
                $otherState = Service::getInstance('orders')->getOrderGoodsRebackState($id);
                if($otherState){
                    $order_state = 2;
                }else{
                    $order_state = 1;
                }
                //改变orders表退货状态
                $res = Service::getInstance('orders')->setOrderStatus(array('reback' => $order_state), $id);               
                $goodsInfo = Service::getInstance('goods')->getGoodsInfoById($goodsId);
                if ($goodsInfo['status'] == '2') {
                    Service::getInstance('goods')->change($goodsId, array('status' => 1));
                }
                $newStock = $rebackNum + $goodsInfo['goodsStock'];
                $updateStock = Service::getInstance('goods')->edit(array('goodsStock' => $newStock), $goodsId);
                $rebackData = array(
                    'reback_time'   => $rebackTime,
                    'reback_reason' => intval($reason),
                    'goods_id'      => $goodsId,
                    'order_id'      => $id,
                    'reback_num'    => $rebackNum,
                    'reback_price'  => $rebackPrice,
//                    'reback_type'   => $rebackType,
                    'sales'         => $sales,
                    'operator'      => $operator
                );
                $rebackRes = Service::getInstance('orders')->addOrderReback($rebackData);        
                if ($updateStock >= 0 && $rebackRes) {
                    $this->respon(1, '成功');
                } else {
                    $this->respon(0, '失败');
                }
            }else{
                $this->respon(0, '退货数量大于订单数量,失败');
            }
        }else{
            $this->respon(0, '退货数量小于0,失败');
        }
        
        
    }

    //城市列表
    public function cityAction() {
        $province = $this->getPost('province');
        $city = Service::getInstance('shop')->getCity($province);
        if ($city) {
            $this->respon(1, $city);
        } else {
            $this->respon(0, '查询失败');
        }
    }

    //地区列表
    public function areaAction() {
        $city = $this->getPost('city');
        $area = Service::getInstance('shop')->getCity($city);
        if ($city) {
            $this->respon(1, $area);
        } else {
            $this->respon(0, '查询失败');
        }
    }

    function getMsgError() {
        return $this->error;
    }

    public function exporttextAction() {
        ini_set('memory_limit', '256M');
        $dist = $this->getQuery('dist');
        $keyword = trim($this->getQuery('keyword'));
        $status = $this->getQuery('status');
        $reback = $this->getQuery('reback');
        $startTime = trim($this->getQuery('starttime'));
        $endTime = trim($this->getQuery('endtime'));
        $data = Service::getInstance('orders')->getAllOrderExport($status, $reback, $keyword, $dist, $startTime, $endTime);

        $objPHPExcel = new PHPExcel(); //实例化PHPExcel类， 等同于在桌面上新建一个excel
        //获得当前活动单元格
        $objSheet = $objPHPExcel->getActiveSheet();
        //设置excel文件默认水平垂直方向居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(Plugins_PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(Plugins_PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置默认字体大小和格式
        $objSheet->getDefaultStyle()->getFont()->setSize(10)->setName("宋体");
        //设置第二行字体大小和加粗
        $objSheet->getStyle("A1:Z1")->getFont()->setSize(11)->setBold(true);
        $objSheet->getDefaultRowDimension()->setRowHeight(20); //设置默认行高
        /* $objSheet->getRowDimension(2)->setRowHeight(30);//设置第二行行高
          $objSheet->getRowDimension(3)->setRowHeight(30);//设置第三行行高 */

        $cell = range('A', 'Z');
        //表头数组/*, '付款方式', '付款类型', '支付时间', '收款银行', '收款账号', '运费'*/
        $head = array('订单类型','订单编号', '下单时间', '商品名称', 'SKU', '数量', '实付价格', '平台价格', '渠道', '买家备注', '卖家备注', '发货时间', '送货方式', '快递公司', '运单编号', '状态', '供货商', '退货状态', '收货人', '手机号','下单人');
        //'支付账号',,'省份','市区','县区','详细地址'
        //对应索引'payw', 'payType', 'payTime', 'payeebank', 'bankAccount', 'freight'
        $relation = array( 'isBrush', 'orderCode', 'createTime', 'name', 'code', 'amount', 'orderPrice', 'platfPrice', 'channel', 'remark', 'sellerremark', 'deliverTime', 'deliverWay', 'expressName', 'number', 'payStatus', 'shopName', 'reback', 'uname', 'tel', 'operator');
        //'payAccount',,'province','city','area','address'
        //填充表头信息
        $headLen = count($head);
        for ($i = 0; $i < $headLen; $i++) {
            $objSheet->setCellValue($cell[$i] . "1", $head[$i]);
        }
        $len = count($data);
        for ($i = 0; $i < $len; $i++) {
            $j = 0;
            foreach ($relation as $k => $v) {
                $row = $i + 2;
                $value = $data[$i][$v];
                if (is_numeric($value))
                    $value = ' ' . $value;
                $objSheet->setCellValue("$cell[$j]$row", $value);
                $j++;
            }
        }

        $objWriter = Plugins_PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); //生成excel文件
        header('Content-Type: application/vnd.ms-excel'); //告诉浏览器将要输出excel03文件\
        //告诉浏览器将输出文件的名称
        header('Content-Disposition: attachment;filename=order.xls');
        header('Cache-Control: max-age=0'); //禁止缓存	
        $objWriter->save("php://output");
    }

    //编辑订单实付价格
    public function editOrderPriceAction(){
        $price = $this->getPost('orderPrice');
        $id    = $this->getPost('orderId');
        if ( !$id ) {
            $this->respon(0,"error! no id!");
        }

        // 获取结算申请状态
        $check = Service::getInstance('orders')->getAccountStatus( $id );
        if( '2' == $check ) {
            $this->respon(0,"结算审核已通过，无法修改");
        }

        $data = array('price' =>$price);
        $res  = Service::getInstance('orders')->edit($data,$id);
        $resPrice = Service::getInstance('orders')->editOrdersGoods(array("goods_pay_price"=>$price), $id);

        if ( $res !== false ){
            $this->respon(1,array($price,$id));
        }else{
            $this->respon(0,"编辑失败");
        }
    }

    //取消订单
    public function cancelAction() {
        $orderId = $this->getPost('id');
        if (!$orderId) $this->respon(0, '数据异常');

        //判断订单结算状态
        $checkAcc = Service::getInstance('orders')->getAccountStatus( $orderId );
        if ( $checkAcc != 0 && $checkAcc != 1 ) {
            $this->respon(0,"订单已经结算,无法删除");
        }

        $brush = Service::getInstance('orders')->checkBrush($orderId);
        if (!$brush) {
            $order = Service::getInstance("orders")->getOrderNum($orderId);
            $res = Service::getInstance("goods")->addStock($order['goodsId'], $order['number']);
        }
        $success = Service::getInstance("orders")->setIsDel($orderId);
        if ($res) {
            $this->respon(1, "成功");
        } else {
            $this->respon(0, "失败");
        }
    }

    //激活订单
    public function activeAction() {
        $orderId = $this->getPost('id');
        $showType = $this->getPost('showType');
        if (!$orderId)
            $this->respon(0, '数据异常');
        $brush = Service::getInstance('orders')->checkBrush($orderId);
        if (!$brush) {
            $order = Service::getInstance("orders")->getOrderNum($orderId);
            $goodsStock = Service::getInstance('goods')->getStockById($order['goodsId']);
            $newStock = $goodsStock - $order['number'];
            $res = Service::getInstance("goods")->edit(array('goodsStock' => $newStock), $order['goodsId']);
        }
        $data = array(
            'isTake' => 0,
            'isDeliver' => 1,
            'isDel' => 0
        );
        if($showType == 'deleteStore'){
            $data['isDelete'] = 0;
        }        
        $success = Service::getInstance("orders")->setOrderStatus($data,$orderId);
        if ($res) {
            $this->respon(1, "成功");
        } else {
            $this->respon(0, "失败");
        }
    }

    //修改priceAnomaly状态   待审核订单
    public function priceAnomalyAction(){
        if ( $this->isPost() ) {
            $id = $this->getPost('id');
            if ( !$id ) $this->respon(0,'no id!');
            $data = array('priceAnomaly'=>0);
            $res = Service::getInstance('orders')->edit( $data ,$id );
            if ( $res !== false ) {
                $this->respon(1,'ok');
            }else{
                $this->respon(0,'error');
            }
        }
    }

    public function batchPriceAnomalyAction(){
        $checkIds = $this->getPost('checkIds');
        if( !$checkIds || !is_array($checkIds) ) $this->respon(0,'参数错误');
        $ids = array();
        foreach ($checkIds as $id ) {
            $ids[$id] = $id;
        }
        $ids = implode( ',',$ids );
        $sql = " UPDATE orders SET priceAnomaly = 0 WHERE id IN ( {$ids} ) ";

        if( $this->db->query($sql) ){
            $this->respon(1,'操作成功');
        }else{
            $this->respon(0,'操作失败');
        }
        
    }

    public function buyerAction(){
        if( $this->isPost() ){
            //$this->respon(1,$_POST);
            $data['order_id'] = $this->getPost('orderId');
            $data['track_code'] = $this->getPost('trackCode');
            $data['buyer_id'] = $this->getPost('buyerId');
            $data['buyer_time'] = time();
            $res = Service::getInstance('orders')->addOrderBuyer( $data );
            if( $res ){
                Service::getInstance('orders')->edit( array('isBuyer'=>1),$data['order_id'] );
                $this->respon( 1,'订单采购成功！' );
            }else{
                $this->respon( 0,'订单采购失败!' );
            }
        }
    }

}
