<?php

class DeliverController extends BaseController {

    public function init() {
        parent::init();
        if (!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    }

    //发货订单
    public function indexAction() {
        $perpage = $this->getQuery('perpage', 15);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $orderType = $this->getQuery('orderType', '');
        $dist = $this->getQuery('dist', 0);
        $keyword = trim($this->getQuery('keyword', ''));
        $starttime = $this->getQuery('starttime', "2016-01-01");
        $endtime = $this->getQuery('endtime', date('Y-m-d', time()));
        $searchType = $this->getQuery('searchType', '1');
        $topLevel = $this->getQuery('topLevel', 'pingtai');
        $showType = $this->getQuery('showType','waitDeliver');
        //获取订单列表
        $data = Service::getInstance('orders')->orderslist($page, $perpage, $keyword, $dist, $starttime, $endtime, $searchType,$orderType,$showType);
        if ($data['list']) {
            foreach ($data['list'] as $k => $v) {
                $data['list'][$k]['imageurl'] = Service::getInstance('goods')->getGoodsOneImg($v['goodsId']);
                $channel = Service::getInstance('goods')->getChannelById($v['channel']);
                $express = Service::getInstance('orders')->getOrderExpress($v['id']);
                $data['list'][$k]['express'] = $express;
                $data['list'][$k]['cname'] = $channel['name'];
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
                if ($v['payTime'] >= strtotime(date("Y-m-d"))) {
                    $todayData['list'][$k] = $data['list'][$k];
                } else {
                    $earlyData['list'][$k] = $data['list'][$k];
                }
            }
        }
        $dis = Service::getInstance('distributor')->getAllDis();
        $express = Service::getInstance('orders')->getAllExpress();
        /*$province = Service::getInstance('shop')->getProvince();*/
        $this->_view->express = $express;
        $this->_view->dis = $dis;
        /*$this->_view->province = $province;*/
        !empty($todayData['list']) ? $this->_view->todaylist = $todayData['list'] : "";
        !empty($earlyData['list']) ? $this->_view->earlylist = $earlyData['list'] : "";
        //Factory::vd($data['list'],1);
        $this->_view->perpage = $perpage;
        $this->_view->keyword = $keyword;
        $this->_view->starttime = $starttime;
        $this->_view->endtime = $endtime;
        $this->_view->orderType = $orderType;
        $this->_view->dist = $dist;
        $this->_view->topLevel = $topLevel;
        $this->_view->searchType = $searchType;
        $this->_view->showType = $showType;
        $this->_view->total = $data['total'];
        $pageObj = new Page($data['total'][$showType]['totalNum'], $perpage, $showpage, $page, '', array('deliver', 'index', 'keyword' => $keyword, 'dist' => $dist, 'perpage' => $perpage, 'starttime' => $starttime, 'endtime' => $endtime, 'searchType' => $searchType,'orderType'=>$orderType,'topLevel'=>$topLevel,'showType'=>$showType));
        $this->_view->pagebar = $pageObj->showPage();
    }

    //删除发货订单
    public function delAction() {
        $orderId = $this->getQuery('id');
        if (!$orderId)
            return false;
        $brush = Service::getInstance('orders')->checkBrush($orderId);
        if (!$brush) {
            $order = Service::getInstance("orders")->getOrderNum($orderId);
            $res = Service::getInstance("goods")->addStock($order['goodsId'], $order['number']);
        }
        $success = Service::getInstance("orders")->deleteOrder($orderId);
        if ($success) {
            echo "<script>alert('成功');window.location.href=document.referrer</script>";
        } else {
            echo "<script>alert('失败');window.location.href=document.referrer</script>";
        }
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

/*    //取消订单
    public function cancelAction() {
        $orderId = $this->getPost('id');
        if (!$orderId)
            $this->respon(0, '数据异常');
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
        $status = $this->getPost('status');
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
        if($status == 6){
            $data['isDelete'] = 0;
        }        
        $success = Service::getInstance("orders")->setOrderStatus($data,$orderId);
        if ($res) {
            $this->respon(1, "成功");
        } else {
            $this->respon(0, "失败");
        }
    }
*/
    //订单详情
    public function detailAction() {
        $id = $this->getPost('id');
        $info = Service::getInstance('orders')->getOrderDetail( $id );
        $info['order'] = Service::getInstance('orders')->getOrder( $id );
        $info['price'] = Service::getInstance('orders')->getOrderPrice( $id );
        $info['payway'] = Service::getInstance('orders')->getPayWById( $info['order']['payw'] );
        $info['paytype'] = Service::getInstance('orders')->getPayTypeById( $info['order']['payType'] );
        $info['paybank'] = Service::getInstance('orders')->getPayBankById( $info['order']['payBank'] );

        /*
        $province = Service::getInstance('shop')->getProvince();
        $city = Service::getInstance('shop')->getCity($info['order']['province']);
        $area = Service::getInstance('shop')->getCity($info['order']['city']);
        $payway = Service::getInstance('orders')->getPayWay();
        $paytype = Service::getInstance('orders')->getPayType($info['order']['payw']);
        $paybank = Service::getInstance('orders')->getPayBank($info['order']['payType']);
        $express = Service::getInstance('orders')->getAllExpress();
        $info['opt']['province'] = $province;
        $info['opt']['city'] = $city;
        $info['opt']['area'] = $area;
        $info['opt']['payway'] = $payway;
        $info['opt']['paytype'] = $paytype;
        $info['opt']['paybank'] = $paybank;
        $info['opt']['express'] = $express;
        */

        if ($info) {
            $this->respon(1, $info);
        } else {
            $this->respon(0, '失败');
        }
    }

    //获取快递信息
    public function getExpressAction(){
        if ( $this->isPost() ) {
            $id = $this->getPost('id');
            if ( !$id ) $this->respon(0,'参数异常');
            $express = Service::getInstance('orders')->getOrderExpress( $id );
            $orderPrice = Service::getInstance('orders')->getOrderPrice( $id );
            $data = array(
                    'express'=>$express,
                    'orderPrice'=>$orderPrice,
                    );
            if ( $data ) {
                $this->respon( 1,$data);
            }else{
                $this->respon( 0,"暂无信息");
            }
        }
    }

    //发货
    public function deliverAction() {
        if ($this->isPost()) {
            $searchType = $this->getPost('searchType');
            $keyword = $this->getPost('keyword');
//            $status = $this->getPost('status');
//            $reback = $this->getPost('reback');
//            $isBuyer = $this->getPost('isBuyer');
            $dist = $this->getPost('dist');
            $starttime = $this->getPost('starttime', '2016-01-01');
            $endtime = $this->getPost('endtime', date('Y-m-d', time()));
            $perpage = $this->getPost('perpage', 15);
            $page = $this->getPost('page');
            $orderType = $this->getPost('orderType');
            $showType = $this->getPost('showType');
            $topLevel = $this->getPost('topLevel');            
            $id = $this->getPost('id');
            $express = $this->getPost('express');
            $expressNum = $this->getPost('expressNum');
            $sub5 = $this->getPost('sub5');

            $data = array(
                'orderId' => $id,
                'expressId' => $express,
                'number' => $expressNum
            );

            if ( $sub5 ) {
                $isExpress = Service::getInstance('orders')->getOrderExpress($id);
                if ($isExpress) {
                    $result = Service::getInstance('orders')->editExpress($data);
                } else {
                    //自动结算
                    $shopId = Service::getInstance('orders')->getSHopIdByOrder( $id );
                    $period = Service::getInstance('shop')->getShopPeriodById( $shopId );
                    if ( $period > 0 ) {
                        $account = $this->redisAddAccount( $period, $id);
                    }
                    //发货
                    $result = Service::getInstance('orders')->addExpress($data);
                }
                if ($result) {
                    $res = Service::getInstance('orders')->doDeliver( $id );
                    if ($res >= 0) {
                        $this->flash('/deliver/index/?searchType=' . $searchType . '&keyword=' . $keyword . '&dist=' . $dist . '&starttime=' . $starttime . '&endtime=' . $endtime . '&perpage=' . $perpage . '&page=' . $page . '&orderType=' . $orderType . '&topLevel=' . $topLevel . '&showType=' . $showType, '发货成功', 1);
                    } else {
                        Service::getInstance('orders')->delExpress($result);
                        $this->flash('/deliver/index/?searchType=' . $searchType . '&keyword=' . $keyword . '&dist=' . $dist . '&starttime=' . $starttime . '&endtime=' . $endtime . '&perpage=' . $perpage . '&page=' . $page . '&orderType=' . $orderType . '&topLevel=' . $topLevel . '&showType=' . $showType, '发货失败', 1);
                    }
                } else {
                    $this->flash('/deliver/index/?searchType=' . $searchType . '&keyword=' . $keyword . '&dist=' . $dist . '&starttime=' . $starttime . '&endtime=' . $endtime . '&perpage=' . $perpage . '&page=' . $page . '&orderType=' . $orderType . '&topLevel=' . $topLevel . '&showType=' . $showType, '发货失败', 1);
                }
            }
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
                $payway = $this->getPost('payway');
                $paytype = $this->getPost('paytype');
                $paybank = $this->getPost('paybank');
                $freight = $this->getPost('freight');
                $remark = $this->getPost('remark');
                $sellerremark = $this->getPost('sellerremark');
                $orderPrice = $this->getPost('orderPrice');
                $data = array(
                    'price' => $orderPrice,
                    'uname' => $consignee,
                    'tel' => $phone,
                    'province' => $province,
                    'city' => $city,
                    'area' => $area,
                    'address' => $address,
                    'payw' => $payway,
                    'payType' => $paytype,
                    'payBank' => $paybank,
                    //'freight'=>$freight,
                    'remark' => $remark,
                    'sellerremark' => $sellerremark,
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
        $id = $this->getPost('id');
        $res = Service::getInstance('orders')->setOrderStatus(array('reback' => 1), $id);
        $num = Service::getInstance('orders')->getOrderNum($id);
        $goodsInfo = Service::getInstance('goods')->getGoodsInfoById($num['goodsId']);
        if ($goodsInfo['status'] == '2') {
            Service::getInstance('goods')->change($num['goodsId'], array('status' => 1));
        }
        $newStock = $num['number'] + $goodsInfo['goodsStock'];
        $updateStock = Service::getInstance('goods')->edit(array('goodsStock' => $newStock), $num['goodsId']);
        if ($updateStock >= 0) {
            $this->respon(1, '成功');
        } else {
            $this->respon(0, '失败');
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
        //表头数组
        $head = array('订单编号', '下单时间', '商品名称', '货号', '数量', '实付价格', '平台价格', '渠道', '买家备注', '卖家备注', '付款方式', '付款类型', '支付时间', '收款银行', '收款账号', '发货时间', '送货方式', '快递公司', '运单编号', '运费', '状态', '供货商', '退货状态', '收货人', '手机号');
        //'支付账号',,'省份','市区','县区','详细地址'
        //对应索引
        $relation = array('orderCode', 'createTime', 'name', 'goodsNo', 'amount', 'orderPrice', 'platfPrice', 'channel', 'remark', 'sellerremark', 'payw', 'payType', 'payTime', 'payeebank', 'bankAccount', 'deliverTime', 'deliverWay', 'expressName', 'number', 'freight', 'payStatus', 'shopName', 'reback', 'uname', 'tel');
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

        if ( $res !== false ){
            $this->respon(1,array($price,$id));
        }else{
            $this->respon(0,"编辑失败");
        }
    }

    //编辑订单其他价格
    public function editOtherPriceAction(){
        if ( $this->isPost() ) {
            $id = $this->getPost('orderId');
            if ( !$id ) {
                $this->respon(0,"error! no id!");
            }

            // 获取结算申请状态
            $check = Service::getInstance('orders')->getAccountStatus( $id );
            if( '2' == $check ) {
                $this->respon(0,"结算审核已通过，无法修改");
            }

            $real_freight = $this->getPost('real_freight');
            $real_certificate = $this->getPost('real_certificate');
            $real_pack = $this->getPost('real_pack');
            //$real_mount = $this->getPost('real_mount');
            $real_other = $this->getPost('real_other');

            $dataPrice = array(
                'real_freight' => $real_freight,
                'real_certificate' => $real_certificate,
                'real_pack' => $real_pack,
                //'real_mount'=>$real_mount,
                'real_other' => $real_other,
                'updateTime' => time()
            );

            $resPrice = Service::getInstance('orders')->editOrderPrice($dataPrice, $id);

            //修改其他价格后，改变结算单总计
            $res = $this->_editAccountTotal( $id );

            if ( $resPrice !== false ){
                $this->respon(3,"修改成功");
            }else{
                $this->respon(0,"编辑失败");
            }
        }
    }

    private function _editAccountTotal( $id ){
        $accountId = Service::getInstance('orders')->getAccountIdByOrder( $id );
        $data = Service::getInstance('orders')->getPriceByAccount( $accountId );

        $total = 0;
        foreach ( $data as $v ) {
            $price = $v['number']*$v['goods_price']+$v['real_freight']+$v['real_certificate']+$v['real_pack']+$v['real_other']+$v['real_mount'];
            $total += $price;
        }
        $res = Service::getInstance('account')->update( array('total'=>$total),$accountId );
        return $res;
    }

    //放入redis自动结算申请队列
    private function redisAddAccount( $period,$orderId ){
        $accDate = date('Ymd',time()+$period*86400);
        $key = md5('account_'.$accDate);
        $res = $this->redis->SADD($key,$orderId);
        //606b3ffef8df5de1564a7bc30e35a0a3
        return $res;
    }
}
