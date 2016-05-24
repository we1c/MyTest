<?php

class OrderController extends BaseController {
    public function init() {	
        parent::init();
        $white = array('lists',); //白名单
        if ( !in_array( $this->getRequest()->getActionName(),$white) ) {
            if ( ! (bool)Yaf_Registry::get("isLogin") )
            {
                $this->respon( 0 , "请重新登录" );
            }
        }
        $this->_action = $this->getRequest()->getActionName();
        $this->_controller = $this->getRequest()->getControllerName();
    }
    //订单列表
    public function listsAction() {
        $uid = Yaf_Registry::get('uid');
        $shopId = $this->getPost('shopId');
        $status = $this->getPost('status');
        $pageNum = $this->getPost('pageNum') ? $this->getPost('pageNum') : 1;
        $pageSize = $this->getPost('pageSize') ? $this->getPost('pageSize') : 10;
        $list = Service::getInstance('orders')->getList( $shopId, $status, $pageNum, $pageSize );
        if ( $list['data'] ) {
            foreach ( $list['data'] as $k => $v ) {
                $express = Service::getInstance('orders')->getOrderExpress( $v['id'] );
                $list['data'][$k]['expressName'] = $express ? $express['expressName'] : null;
                $list['data'][$k]['expressNumber'] = $express ? $express['number'] : null;
                $list['data'][$k]['province'] = Service::getInstance('orders')->getProCityAreaName( $v['province'] );
                $list['data'][$k]['city'] = Service::getInstance('orders')->getProCityAreaName( $v['city'] );
                $list['data'][$k]['area'] = Service::getInstance('orders')->getProCityAreaName( $v['area'] );
                $list['data'][$k]['imageurl'] = Service::getInstance('goods')->getGoodsOneImg( $v['goodsId'] ,'1' );
                if ( $v['isDel'] == 1 ) {
                    $list['data'][$k]['payStatus'] = '已取消';
                } elseif ( $v['isTake'] == 2 ) {
                    $list['data'][$k]['payStatus'] = '已签收';
                } elseif ( ($v['isTake'] == 1 ) || ( $v['isTake'] == 0 && $v['isDeliver'] == 2 ) ) {
                    $list['data'][$k]['payStatus'] = '待签收';
                } elseif ( ( $v['isTake'] == 0 && $v['isDeliver'] == 1 ) || ( $v['isDeliver'] == 0 && $v['isPay'] == 1 ) ) {
                    $list['data'][$k]['payStatus'] = '待发货';
                } elseif ( $v['isPay'] == 0 ) {
                    $list['data'][$k]['payStatus'] = '待付款';
                } else {
                    $list['data'][$k]['payStatus'] = '无效';
                }
            }
        }
        $this->respon( 1 ,$list ? $list : array() );
    }
    
    //发货
    public function deliverAction() {
        $id = $this->getPost('orderId');
        $name = $this->getPost('name');
        $number = $this->getPost('number');
        if ( !$id ) $this->respon( 0, '订单信息错误' );
        if ( trim( $name ) == '' ) $this->respon( 0, '请填写快递公司名称' );
        if ( trim( $number ) == '' ) $this->respon( 0, '请填写快递单号' );
        $expressId = Service::getInstance('orders')->getExpress( $name );
        $data = array(
            'orderId'=>$id,
            'expressId'=>$expressId,
            'number'=>$number
        );
        $result = Service::getInstance('orders')->addExpress( $data );
        if ( $result ) {
            $res = Service::getInstance('orders')->doDeliver( $id );
            if ( $res >= 0 ) {
                $this->respon( 1, '发货成功' );
            } else {
                Service::getInstance('orders')->delExpress( $result );
                $this->respon( 0, '发货失败' );
            }
        } else {
            $this->respon( 0, '发货失败' );
        }
    }
    //修改发货信息
    public function editExpressAction() {
        $id = $this->getPost('orderId');
        $name = $this->getPost('name');
        $number = $this->getPost('number');
        if ( !$id ) $this->respon( 0, '订单信息错误' );
        if ( trim( $name ) == '' ) $this->respon( 0, '请填写快递公司名称' );
        if ( trim( $number ) == '' ) $this->respon( 0, '请填写快递单号' );
        $expressId = Service::getInstance('orders')->getExpress( $name );
        $data = array(
            'orderId'=>$id,
            'expressId'=>$expressId,
            'number'=>$number
        );
        $result = Service::getInstance('orders')->editExpress( $data );
        if ( $result ) {
                $this->respon( 1, '修改成功' );
        } else {
            $this->respon( 0, '修改失败' );
        }
    }

    //订单统计
    public function countAction(){
        $shopId = $this->getPost('shopId');
        if ( !$shopId) $this->respon( 0, '商家信息错误' );
        $start = date('Y-m-01', time());
        $end = date('Y-m-d', strtotime($start."+1 month -1 day"));
        $orders = Service::getInstance('orders')->getOrderCount($shopId,strtotime($start),strtotime($end));
        $time_1 =  strtotime(date('Y-m-d'));
        $time_2 =  strtotime(date('Y-m-d')."-1 day");
        $time_3 =  strtotime(date('Y-m-d')."-2 day");
        $time_4 =  strtotime(date('Y-m-d')."-3 day");
        $time_5 =  strtotime(date('Y-m-d')."-4 day");
        $time_6 =  strtotime(date('Y-m-d')."-5 day");
        $time_7 =  strtotime(date('Y-m-d')."-6 day");
        $time_8 =  strtotime(date('Y-m-d')."-7 day");
        $p = array(
                "price1"=>0,
                "price2"=>0,
                "price3"=>0,
                "price4"=>0,
                "price5"=>0,
                "price6"=>0,
                "price7"=>0,
                "price8"=>0,
                );
        $c = array(
                "count1"=>0,
                "count2"=>0,
                "count3"=>0,
                "count4"=>0,
                "count5"=>0,
                "count6"=>0,
                "count7"=>0,
                "count8"=>0,
                );
        $totalPrice = 0;
        $totalNumber = 0;
        foreach ($orders as $k => $v) {
            $totalPrice += $v['price'];
            $totalNumber ++; 
            if ( $v['payTime'] > $time_1) {
                $p['price1'] += $v['price'];
                $c['count1']++;
            }elseif($v['payTime'] > $time_2 && $v['payTime'] < $time_1) {
                $p['price2'] += $v['price'];
                $c['count2']++;
            }elseif($v['payTime'] > $time_3 && $v['payTime'] < $time_2) {
                $p['price3'] += $v['price'];
                $c['count3']++;
            }elseif($v['payTime'] > $time_4 && $v['payTime'] < $time_3) {
                $p['price4'] += $v['price'];
                $c['count4']++;
            }elseif($v['payTime'] > $time_5 && $v['payTime'] < $time_4) {
                $p['price5'] += $v['price'];
                $c['count5']++;
            }elseif($v['payTime'] > $time_6 && $v['payTime'] < $time_5) {
                $p['price6'] += $v['price'];
                $c['count6']++;
            }elseif($v['payTime'] > $time_7 && $v['payTime'] < $time_6) {
                $p['price7'] += $v['price'];
                $c['count7']++;
            }elseif($v['payTime'] > $time_8 && $v['payTime'] < $time_7) {
                $p['price8'] += $v['price'];
                $c['count8']++;
            }
        }
        for ($i=8; $i >=1 ; $i--) { 
            $price = array("price"=>$p['price'.$i]);
            $count = array("count"=>$c['count'.$i]);
            $data['price'][] = $price;
            $data['count'][] = $count;
        }
        $data['totalPrice'] = $totalPrice;
        $data['totalNumber'] = $totalNumber;
        $data['time'] = time();
        $this->respon( 1 ,$data ? $data : array() );
    }

    public function getTotalAction(){
        
        $shopId = $this->getPost('shopId');
        if ( !$shopId) $this->respon( 0, '商家信息错误' );
        $total = Service::getInstance('orders')->getTotal($shopId);

        $end = time();
        $start = strtotime(date('Y-m-d',$end)."-30 day");
        $orders = Service::getInstance('orders')->getOrderCount($shopId,$start,$end);

        $p = array();
        $c = array();
        for ($i=0; $i < 14; $i++) { 
            if ($i<8) {
                $time[$i] = strtotime(date('Y-m-d').-($i-1)." day");
            }else{
                $week = ($i - 7) * 5;
                $time[$i] = strtotime(date('Y-m-d').-($week)." day");
            }
            if ($i != 0) {
                $p['price'.$i] = 0;
                $c['count'.$i] = 0;
            }
        }
        foreach ($orders as $k => $v) {
            for ($i=1; $i < 14; $i++) { 
                if ($i == 8) {$time[$i-1] = date('Y-m-d', time());}
                if ($v['payTime']>$time[$i] && $v['payTime']<$time[$i-1]) {
                    $p['price'.$i] += $v['price'];
                    $c['count'.$i]++;
                }
            }
        }
        // print_r($p);exit;
        for ($i=1; $i < 14 ; $i++) { 
            if ($i < 8) {
                $data['weeksPrice'][] = $p['price'.$i];
                $data['weeksCount'][] = $c['count'.$i];
            }else{
                $data['monthPrice'][] = $p['price'.$i];
                $data['monthCount'][] = $c['count'.$i];
            }
        }
        $data['time'] = time();
        $data['costTotal'] = round($total['costTotal']);
        $data['stockTotal'] = $total['stockTotal'];
        $data['skuCount'] = $total['number'];
        $this->respon( 1 ,$data ? $data : array() );
    }
}