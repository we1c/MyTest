<?php



//订单
class Service_Orders extends Service {

    public $error = '';
    private $_status_arr;

    //获取商品购买状态，如果有人下单了就不能再购买
    public function getGoodsBuyStatus($gid) {
        $data = $this->db->fetchRow("SELECT id,status FROM goods WHERE id = {$gid}");
        if ( $data['id'] && $data['status'] == 1 ) {
            return true;
        }else{
            return '商品售罄';
        }
    }

    /**
     * 添加订单 
     * array(
     *     'goodsId'=>'商品Id',      'shopId'=>'店铺Id',       'price'=>'订单金额',
     *     'province'=>'省份id',     'city'=>'城市id',         'area'=>'地区id',         'address'=>'详细地址',
     *     'userId'=>'用户id',       'uname'=>'收货人姓名',      'tel'=>'收货人电话',        'remark'=>'买家留言',
     *     'createTime'=>'订单创建时间',   
     *     'deliverWay'=>'配送方式:1是快递，2是自取',
     *     'orderType'=>'订单类型:1是商家，2是平台，3是分销'
     * )
     */
    public function add($data, $channelId = 0) {
        $this->db->insert('orders', $data);
        return $this->db->lastInsertId();
    }

    //编辑订单
    public function edit($data, $id) {
        return $this->db->update('orders', $data, ' id =' . $id);
    }

    //改变订单审核状态
    public function editAccount($data, $orderIn) {
        return $this->db->update('orders', $data, ' id IN (' . $orderIn . ')');
    }

    /**
     * 订单快递信息 
     * 'id':订单id
     */
    public function getOrderExpress($id) {
        $sql = " SELECT B.id AS expressId , B.name AS expressName , A.number, A.createTime,A.real_freight FROM `orders_express` AS A LEFT JOIN `express` AS B ON A.expressId = B.id  WHERE A.orderId = {$id}";
        $res = $this->db->fetchRow($sql);
        return $res;
    }

    //添加订单
    public function addOrder($data) {
        foreach ($data as $v) {
            $this->db->insert('orders', $v);
            $orderId[] = $this->db->lastInsertId();
        }
        return $orderId;
    }

    //添加订单费用
    public function addOrderPrice($data) {
        return $this->db->insert('orders_price', $data);
    }

    //修改订单费用
    public function editOrderPrice($data, $orderId) {
        return $this->db->update('orders_price', $data, 'orderId = ' . $orderId);
    }

    //获取订单费用
    public function getOrderPrice($id) {
        $sql = "SELECT price_freight,price_certificate,price_pack,price_mount,price_other,real_freight,real_certificate,real_pack,real_mount,real_other FROM orders_price WHERE orderId = {$id}";
        return $this->db->fetchRow($sql);
    }

    //订单列表
    public function getList($shopId, $status, $pageNum, $pageSize) {
        $where = " WHERE A.shopId = {$shopId} AND A.isPay <> '0' AND A.isDel = '0' AND A.reback = '0' AND A.sellType <> 6 AND A.isDelete = 0 AND isBrush = 0 AND priceAnomaly = 0 ";
        if ($status == 0) {
            $where .= ' ';
        } elseif ($status == 1) {
            $where .= " AND A.isPay = '1' AND A.isDeliver = '1' AND ( A.isTake = '0' OR A.isTake = '1' ) ";
        } elseif ($status == 2) {
            $where .= " AND A.isPay = '1' AND A.isDeliver = '2' AND A.isTake = '1' ";
        } elseif ($status == 3) {
            $where .= " AND A.isPay = '1' AND A.isDeliver = '2' AND A.isTake = '2' ";
        }
        $sql = "SELECT 
	               A.id,
	               A.orderCode,
	               A.price AS orderPrice,
	               A.payWay,
	               A.isPay,
	               A.isDeliver,
	               A.isTake,
	               A.isDel,
	               A.tel,
	               A.remark,
	               A.payTime,
	               A.province,
	               A.city,
	               A.area,
	               A.address,
	               A.uname,
	               A.goodsId,
	               A.orderType,
	               C.goods_number AS number,
	               B.code, 
	               B.intro,
	               C.goods_name AS goodsName,
                   case 
                   when A.orderType = '1' then B.price 
                   when A.orderType = '2' then C.goods_price  
                   when A.orderType = '3' then C.goods_price 
                   end AS goodsPrice 
	           FROM orders AS A 
	           LEFT JOIN goods AS B ON A.goodsId = B.id 
               LEFT JOIN orders_goods AS C ON A.id = C.order_id 
	           {$where} 
	           ORDER BY A.createTime DESC" . $this->db->buildLimit($pageNum, $pageSize);
        $data = $this->db->fetchAll($sql);
        $count = $this->db->fetchOne("SELECT count(*) FROM orders AS A {$where}");
        return array('total' => $count, 'data' => $data);
    }

    //订单统计
    public function getOrderCount($shopId, $start, $end) {
        $where = " WHERE A.shopId = {$shopId} AND A.isPay <> '0' AND A.isDel <> '1' AND A.payTime BETWEEN '{$start}' AND '{$end}' AND A.sellType <> 6 AND A.isBrush <> '1' ";
        $sql = "SELECT A.id,A.price,A.payTime,A.sellType,B.purchPrice FROM orders AS A LEFT JOIN goods AS B ON A.goodsId = B.id {$where} ";
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            if ($v['sellType'] != 1) {
                $data[$k]['price'] = $v['purchPrice'];
            } else {
                $data[$k]['price'] = $v['price'];
            }
        }
        return $data;
    }

    //订单编号
    public function getOrderCode($num = 1, $type = 1) {
        $code = $type . date('ymd') . rand(1111, 9999) . date('His') . $num;
        while ($this->_isInvalid($code)) {
            $code = $type . date('ymd') . rand(1111, 9999) . date('His') . $num;
        }
        return $code;
    }

    //判断订单编号是否已存在
    protected function _isInvalid($code) {
        return $this->db->fetchOne('select id from orders where orderCode =' . $code);
    }

    //后台列表
    public function orderslist($page, $perpage, $keyword, $dist = 0, $starttime, $endtime, $searchType,$orderType,$showType ) {
        //各状态筛选条件
        $filter_arr = array(
            'waitDeliver'       => " AND ((A.isTake = '0' AND A.isDeliver = '1') OR (A.isDeliver = '0' AND A.isPay = '1')) AND A.isDel = '0' AND ((A.reback = 0 OR A.reback = 2) AND (C.reback_state = 0 OR C.reback_state = 2)) AND A.isDelete = 0 AND A.isBrush = 0 AND A.isBuyer = 1 AND A.priceAnomaly = 0 AND A.beyond_limit = 0 ",
            'waitCheck'         => " AND (A.priceAnomaly = 1 OR A.beyond_limit = 1) AND A.isBrush = 0 ",
            'waitTake'          => " AND (A.isTake = '1' OR (A.isTake = '0' AND A.isDeliver = '2')) AND A.isDel = '0' AND ((A.reback = 0 OR A.reback = 2) AND (C.reback_state = 0 OR C.reback_state = 2)) AND A.isDelete = 0 AND A.isBrush = 0 AND A.isBuyer = 1 AND A.priceAnomaly = 0 AND A.beyond_limit = 0 ",
            'take'              => " AND A.isTake = '2' AND A.isDel = '0' AND ((A.reback = 0 OR A.reback = 2) AND (C.reback_state = 0 OR C.reback_state = 2)) AND A.isDelete = 0 AND A.isBrush = 0 AND A.isBuyer = 1 AND A.priceAnomaly = 0 AND A.beyond_limit = 0 ",
            'brush'             => " AND A.isBrush = 1 ",
            'cancel'            => " AND A.isDel = '1' AND A.reback = 0 AND A.isDelete = 0 AND A.isBrush = 0 AND A.isBuyer = 1 AND A.priceAnomaly = 0 AND A.beyond_limit = 0 ",
            'reback'            => " AND ((A.reback = 1 OR A.reback = 2) OR (C.reback_state = 1 OR C.reback_state = 2)) AND A.isDelete = 0 AND A.isBrush = 0 AND A.isBuyer = 1 AND A.priceAnomaly = 0 AND A.beyond_limit = 0 ",
            'deleteStore'       => " AND A.isDelete = 1 ",
            'waitBuy'           => " AND ((A.isTake = '0' AND A.isDeliver = '1') OR (A.isDeliver = '0' AND A.isPay = '1')) AND A.isDel = '0' AND ((A.reback = 0 OR A.reback = 2) AND (C.reback_state = 0 OR C.reback_state = 2)) AND A.isDelete = 0 AND A.isBrush = 0 AND A.isBuyer = 0 AND A.priceAnomaly = 0 AND A.beyond_limit = 0 "
        );
//        $uncancel = ' AND A.isDel = 0 ';
//        $unreback = ' AND A.reback = 0 ';
//        $undelete = ' AND A.isDelete = 0 ';
//        $unbrush = ' AND A.isBrush = 0 ';
//        $buy = ' AND A.isBuyer = 1 ';
//        $check = ' AND A.priceAnomaly = 0 AND A.beyond_limit = 0 ';               
        $where = " WHERE A.isPay <> '0' AND A.sellType IN (0,4,5,6) ";
            
        if ($keyword != '') {
            if ($searchType == 1) {
                $where .= " AND (A.orderCode like '%{$keyword}%' OR B.goodsNo like '%{$keyword}%' OR B.name like '%{$keyword}%' OR B.code like '%{$keyword}%' OR A.uname like '%{$keyword}%' OR A.tel like '%{$keyword}%' ) ";
            } elseif ($searchType == 2) {
                $where .= " AND ( B.code LIKE '{$keyword}' ) ";
            } elseif ($searchType == 3) {
                $where .= " AND ( B.goodsNo LIKE '{$keyword}' ) ";
            } elseif ($searchType == 4) {
                $where .= " AND ( A.orderCode like '{$keyword}' ) ";
            }
        }
        if ($dist) {
            $where .= " AND A.channel = {$dist} ";
        }
        //通过orderType筛选订单
        if( $orderType ){
            $where .= " AND A.orderType = '{$orderType}' ";
        }else{
            $where .= " AND A.orderType <> 4";
        }
        if ($starttime != '' && $endtime != '') {
            $where .= " AND A.payTime BETWEEN " . strtotime($starttime) . " AND " . strtotime($endtime . '+1 day') . " ";
        }
        if ($page == 'all' && $perpage == 'all') {
            $limit = '';
        } else {
            $limit = $this->db->buildLimit($page, $perpage);
        }

        $sql = "SELECT 
                A.id,A.channel,A.freight,A.orderCode,A.price AS orderPrice,A.createTime,
                A.updateTime,A.payTime,A.tel,A.number,A.payWay,A.deliverWay,A.remark,A.sellerremark,
                A.isDeliver,A.isPay,A.isTake,A.isDel,A.reback,A.sellType,A.orderType,A.operator,A.isBrush,A.isDelete,A.priceAnomaly,A.beyond_limit,A.account_status,
                B.code,B.goodsNo,
                C.goods_price AS goodsPrice,C.goods_name AS name,C.goods_id AS goodsId,C.order_id AS orderId,C.shop_id AS shopId,C.goods_number,C.goods_pay_price,C.id AS ordersGoodsId
                FROM orders AS A LEFT JOIN orders_goods AS C ON A.id = C.order_id 
                LEFT JOIN goods AS B ON C.goods_id = B.id 
                {$where} ".$filter_arr[$showType]." ORDER BY A.createTime DESC " . $limit;
                // echo $sql;exit;
        $list = $this->db->fetchAll($sql);
//        Factory::vd($list,1);
        foreach ($list as $k => $v) {
            $exp = $this->getOrderExpress($v['id']);
            $list[$k]['deliverTime'] = isset($exp['createTime']) ? strtotime($exp['createTime']) : '';
            $buyer = $this->getBuyerByOrderId( $v['id'] );
            $list[$k]['trackCode'] = isset($buyer['track_code']) ? $buyer['track_code'] : '' ;
            $buyerName = isset($buyer['buyer_id']) ? Service::getInstance('developers')->getNameById($buyer['buyer_id']) : '';
            $list[$k]['buyer'] = $buyerName;
            $list[$k]['rebackInfo'] = $this->getRebackNumAndPriceById(intval($v['id']),intval($v['goodsId']));
        }
        $data['list'] = $list;
//        Factory::vd($list,100);
        //获取各状态订单的数量
        foreach($filter_arr as $k=>$row){
            $data['total'][$k] = $this->db->fetchRow("SELECT count(A.id) as totalNum,sum(A.price) as totalPrice FROM orders AS A LEFT JOIN orders_goods AS C ON A.id = C.order_id 
                LEFT JOIN goods AS B ON C.goods_id = B.id {$where}".$row);
        }        
        return $data;
    }

    public function getAllOrderExport($status, $reback, $keyword, $dist = 0, $starttime, $endtime) {
        $this->_status_arr = array(
            '1' => array('A.isDel' => 1),
            '2' => array('A.isTake' => 2),
            '3' => array(
                '0' => array('A.isTake' => 1),
                'OR' => array(
                    '0' => array('A.isTake' => 0),
                    'AND' => array('A.isDeliver' => 2)
                ),
            ),
            '4' => array(
                '0' => array(
                    '0' => array('A.isTake' => 0),
                    'AND' => array('A.isDeliver' => 1),
                ),
                'OR' => array(
                    '0' => array('A.isDeliver' => 0),
                    'AND' => array('A.isPay' => 1),
                ),
            ),
            '5' => array('A.isPay' => 0,),
        );

        $where = " WHERE A.isPay <> '0' AND A.sellType IN (0,4,5,6) AND A.isDelete = 0 ";


        if ($keyword != '') {
            if ($searchType == 1) {
                $where .= " AND (A.orderCode like '%{$keyword}%' OR B.goodsNo like '%{$keyword}%' OR B.name like '%{$keyword}%' OR B.code like '%{$keyword}%' OR A.uname like '%{$keyword}%' OR A.tel like '%{$keyword}%' ) ";
            } elseif ($searchType == 2) {
                $where .= " AND ( B.code LIKE '{$keyword}' ) ";
            } elseif ($searchType == 3) {
                $where .= " AND ( B.goodsNo LIKE '{$keyword}' ) ";
            } elseif ($searchType == 4) {
                $where .= " AND ( A.orderCode like '{$keyword}' ) ";
            }
        }

        if ($dist) {
            $where .= " AND A.channel = {$dist} ";
        }
        if ($reback != '') {
            if ($reback == 'reback') {
                $where .= " AND A.reback = 1 ";
            } elseif ($reback == 'unreback') {
                $where .= " AND A.reback = 0 ";
            } else {
                $where .= " AND A.reback = {$reback} ";
            }
        }

        if ($status != '') {
            $status_sql = $this->_getStatusSql($this->_status_arr[$status]);
            $where .= "  AND (" . $status_sql . ") ";
            if($status != '1'){
                $where .= " AND isDel = '0'";
            }
        }

        if ($starttime != '' && $endtime != '') {
            $where .= " AND A.payTime BETWEEN " . strtotime($starttime) . " AND " . strtotime($endtime . '+1 day') . " ";
        }
        /* 'orderCode','createTime','name','goodsNo','orderPrice','channel','payw','payType','payAccount','payTime','paybank','bank','deliverTime','deliverWay','expressName','number','freight','payStatus','reback','uname','tel','province','city','area','address'
          '订单编号','下单时间','商品名称','货号','实付价格','渠道','付款方式','付款类型','支付账号','支付时间','收款银行','收款账号','发货时间','送货方式','快递公司','运单编号','运费','状态','退货状态','收货人','手机号','省份','市区','县区','详细地址'
         */
        $sql = "SELECT A.id,A.channel,A.goodsId,A.number AS amount,A.orderCode,A.createTime,A.price AS orderPrice,A.payw,A.payType,A.payTime,A.paybank,A.reback,A.remark,A.sellerremark,A.uname,A.tel,A.province,A.city,A.area,A.address,A.deliverWay,A.freight,A.shopId AS shopName,A.remark,A.isDeliver,A.isPay,A.isTake,A.isDel,A.isBrush,A.operator,B.name,B.code,B.goodsNo,B.purchPrice AS goodsPrice,S.ptimes FROM orders AS A LEFT JOIN goods AS B ON A.goodsId = B.id LEFT JOIN shop AS S ON A.shopId = S.id {$where} ORDER BY A.createTime DESC ";
        $data = $this->db->fetchAll($sql);
        // $relation = array('orderCode', 'createTime', 'name', 'goodsNo', 'amount', 'orderPrice', 'platfPrice', 'channel', 'remark', 'sellerremark', 'payw', 'payType', 'payTime', 'payeebank', 'bankAccount', 'deliverTime', 'deliverWay', 'expressName', 'number', 'freight', 'payStatus', 'shopName', 'reback', 'uname', 'tel');
        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]['platfPrice'] = $v['goodsPrice'] * $v['ptimes'];
                $data[$k]['payAccount'] = '';
                $data[$k]['bank'] = '8888';
                $data[$k]['createTime'] = date('Y-m-d', $v['createTime']);
                $data[$k]['payTime'] = date('Y-m-d', $v['payTime']);

                $data[$k]['payw'] = $v['payw'] ? $this->getPayWById($v['payw']) : '';

                $paybank = $this->getPayBankById($v['paybank']);
                if (!empty($paybank)) {
                    $data[$k]['payeebank'] = $paybank['name'];
                    $data[$k]['bankAccount'] = $paybank['bank'];
                    $data[$k]['payType'] = $this->getPayTypeById($paybank['tid']);
                } else {
                    $data[$k]['payeebank'] = '';
                    $data[$k]['bankAccount'] = '';
                    $data[$k]['payType'] = '';
                }

                $exp = $this->getOrderExpress($v['id']);
                if (!empty($exp)) {
                    $data[$k]['deliverTime'] = isset($exp['createTime']) ? date('Y-m-d H:i:s', strtotime($exp['createTime'])) : '';
                    $data[$k]['expressName'] = $exp['expressName'];
                    $data[$k]['number'] = $exp['number'];
                    $data[$k]['deliverWay'] = $v['deliverWay'] == '1' ? '快递' : '自取';
                } else {
                    $data[$k]['deliverTime'] = '';
                    $data[$k]['expressName'] = '';
                    $data[$k]['number'] = '';
                    $data[$k]['deliverWay'] = '';
                }

                if ($v['reback'] == 1) {
                    $data[$k]['reback'] = '退货';
                } else {
                    $data[$k]['reback'] = '';
                }

                if ( $v['isBrush'] == 1 ) {
                    $data[$k]['isBrush'] = '到店取货';
                }else{
                    $data[$k]['isBrush'] = '正常订单';
                }

                $channel = Service::getInstance('goods')->getChannelById($v['channel']);
                $data[$k]['channel'] = $channel['name'];

                $data[$k]['shopName'] = Service::getInstance('shop')->getShopNameById($v['shopName']);

                if ($v['isDel'] == 1) {
                    $data[$k]['payStatus'] = '已取消';
                } elseif ($v['isTake'] == 2) {
                    $data[$k]['payStatus'] = '已签收';
                } elseif (($v['isTake'] == 1 ) || ( $v['isTake'] == 0 && $v['isDeliver'] == 2 )) {
                    $data[$k]['payStatus'] = '待签收';
                } elseif (( $v['isTake'] == 0 && $v['isDeliver'] == 1 ) || ( $v['isDeliver'] == 0 && $v['isPay'] == 1 )) {
                    $data[$k]['payStatus'] = '待发货';
                } elseif ($v['isPay'] == 0) {
                    $data[$k]['payStatus'] = '待付款';
                } else {
                    $data[$k]['payStatus'] = '无效';
                }
            }
            return $data;
        }
    }

    private function _getStatusSql($arr) {
        $where = '';
        foreach ($arr as $k => $v) {
            if ($k == '0') {
                $where.=' (' . $this->_getStatusSql($v) . ') ';
            } else if (in_array($k, array('AND', 'OR'))) {
                $res = $this->_getStatusSql($v);
                $where.=" {$k} (" . $res . ") ";
            } else {
                $where.=" {$k}='" . $v . "' ";
                //根据$v的不同设定$where.=" {$k} |<|>|<>| {$v} ";
            }
        }
        return $where;
    }

    //用户
    public function getUser($phone, $consignee) {
        $res = $this->db->fetchOne('SELECT id FROM `customer` WHERE `tel` =' . $phone);
        if ($res) {
            return $res;
        } else {
            $this->db->insert('customer', array('tel' => $phone, 'name' => $consignee));
            return $this->db->lastInsertId();
        }
    }

    //订单信息
    public function getOrderInfo($id) {
        return $this->db->fetchRow('SELECT * FROM orders WHERE id =' . $id);
    }

    //订单详细信息
    public function getOrderDetail($id) {
        $sql = "SELECT 
	               A.id,
	               A.orderCode,
	               A.price AS orderPrice,
	               A.payWay,
	               A.freight,
	               A.deliverWay,
	               A.isPay,
	               A.isDeliver,
	               A.isTake,
	               A.isDel,
	               A.tel,
	               A.remark,
	               A.sellerremark,
	               A.payTime,
	               A.province,
	               A.city,
	               A.area,
	               A.address,
	               A.uname,
	               A.goodsId,
	               A.orderType,
	               A.channel,
	               A.reback,
	               A.number,
	               A.price_certificate,
	               A.price_pack,
	               A.price_mount,
	               A.price_other,
	               B.goodsNo,
	               B.attribute,
	               C.shop_id AS shopId,
                   C.goods_name AS goodsName,
                   case 
                   when A.orderType = '1' then B.price 
                   when A.orderType = '2' then C.goods_price 
                   when A.orderType = '3' then B.price 
                   end AS goodsPrice 
	           FROM orders AS A 
	           LEFT JOIN goods AS B ON A.goodsId = B.id 
               LEFT JOIN orders_goods AS C ON A.id = C.order_id 
	           WHERE A.id = {$id}";
        $data = $this->db->fetchRow($sql);
        $data['attribute'] = json_decode($data['attribute'], true) ? json_decode($data['attribute'], true) : null;
        if ($data['province'] && $data['city'] && $data['area']) {
            $data['province'] = $this->getProCityAreaName($data['province']);
            $data['city'] = $this->getProCityAreaName($data['city']);
            $data['area'] = $this->getProCityAreaName($data['area']);
        } else {
            $data['province'] = '';
            $data['city'] = '';
            $data['area'] = '';
        }
        $data['goodsImg'] = Service::getInstance('goods')->getGoodsOneImg($data['goodsId']);
        $data['express'] = $this->getOrderExpress($data['id']);
        if ($data['channel']) {
            $data['channel'] = Service::getInstance('distributor')->getInfoById($data['channel']);
        }
        if ($data['isDel'] == 1) {
            $data['payStatus'] = '已取消';
        } elseif ($data['isTake'] == 2) {
            $data['payStatus'] = '已签收';
        } elseif (($data['isTake'] == 1 ) || ( $data['isTake'] == 0 && $data['isDeliver'] == 2 )) {
            $data['payStatus'] = '待签收';
        } elseif (( $data['isTake'] == 0 && $data['isDeliver'] == 1 ) || ( $data['isDeliver'] == 0 && $data['isPay'] == 1 )) {
            $data['payStatus'] = '待发货';
        } elseif ($data['isPay'] == 0) {
            $data['payStatus'] = '待付款';
        } else {
            $data['payStatus'] = '无效';
        }
        return $data;
    }

    //订单信息
    public function getOrder($id) {
        $sql = "SELECT 
	               A.id,
	               A.price,
	               A.payWay,
	               A.payw,
	               A.payType,
	               A.payBank,
	               A.freight,
	               A.deliverWay,
	               A.tel,
	               A.remark,
	               A.sellerremark,
	               A.payTime,
	               A.province,
	               A.city,
	               A.area,
	               A.address,
	               A.uname,
	               A.goodsId,
	               B.goodsNo,
	               B.code,
	               C.ptimes,
                   D.goods_name AS goodsName,
                   D.goods_price AS purchPrice 
	           FROM orders AS A 
	           LEFT JOIN goods AS B ON A.goodsId = B.id
	           LEFT JOIN shop AS C ON A.shopId = C.id 
               LEFT JOIN orders_goods AS D ON A.id = D.order_id 
	           WHERE A.id = {$id}";
        $data = $this->db->fetchRow($sql);
        $data['goodsImg'] = Service::getInstance('goods')->getGoodsOneImg($data['goodsId']);
        return $data;
    }

    //通过供应商编号查询订单
    public function getOrderByScode($scode, $start, $end) {

        $map = " WHERE A.account_status IN(0,3) 
                AND A.sellType IN(0,4,5) 
                AND orderType <> 1 
                AND isDel <> '1' 
                AND reback <> '1' 
                AND isPay = '1' 
                AND A.isDelete = 0 
                AND A.isBrush = 0 
                AND A.priceAnomaly = 0 ";
        $sql = " SELECT 
						A.id,
						A.payTime,
						A.orderCode,
						A.number,
						A.price,
						A.goodsId,
						A.account_status,
						B.goodsNo,
						B.code,
						C.id AS shopId,
						C.name AS shopName,
						C.category,
						C.scode,
						D.real_certificate,
						D.real_pack,
						D.real_other,
						D.real_freight,
                        E.goods_name AS name,
                        E.goods_price AS purchPrice  
					FROM orders AS A 
					LEFT JOIN goods AS B ON A.goodsId = B.id  
					LEFT JOIN shop AS C ON A.shopId = C.id 
					LEFT JOIN orders_price AS D ON A.id = D.orderId 
                    LEFT JOIN orders_goods AS E ON A.id = E.order_id 
					$map AND C.scode = '{$scode}' AND A.payTime BETWEEN '{$start}' AND '{$end}'";
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['payTime'] = date('Y-m-d', $v['payTime']);
        }
        return $data;
    }

    //添加订单快递信息
    public function addExpress($data) {
        $this->db->insert('orders_express', $data);
        return $this->db->lastInsertId();
    }

    //编辑订单快递信息
    public function editExpress($data) {
        $this->db->delete('orders_express', ' orderId =' . $data['orderId']);
        return $this->addExpress($data);
    }

    //删除快递信息
    public function delExpress($id) {
        return $this->db->delete('orders_express', ' id = ' . $id);
    }

    //订单发货状态
    public function doDeliver($id) {
        return $this->db->update('orders', array('isDeliver' => 2, 'isTake' => 1), ' id = ' . $id);
    }

    //取消订单
    public function setIsDel($id) {
        return $this->db->update('orders', array('isDel' => 1), ' id = ' . $id);
    }

    //更新订单信息
    public function setOrderStatus($data, $id) {
        return $this->db->update('orders', $data, ' id =' . intval($id));
    }

    //省市区名称
    public function getProCityAreaName($id) {
        $sql = 'SELECT name FROM `city` WHERE `id` = ' . $id;
        return $this->db->fetchOne($sql);
    }

    //快递公司
    public function getExpress($name) {
        $res = $this->db->fetchOne("SELECT id FROM `express` WHERE `name` LIKE '{$name}%'");
        if ($res) {
            return $res;
        } else {
            $this->db->insert('express', array('name' => $name));
            return $this->db->lastInsertId();
        }
    }

    //获取快递公司列表
    public function getAllExpress() {
        $res = $this->db->fetchAll(" SELECT id,name,price FROM `express` WHERE pid = 0 ");
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    //获取快递公司列表
    public function getSonExpress() {
        $res = $this->db->fetchAll(" SELECT id,name,price FROM `express` WHERE pid <> 0 ");
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    //物理删除订单
    public function delOrder($orderId) {
        return $this->db->delete('orders', ' id = ' . $orderId);
    }

    //逻辑删除订单
    public function deleteOrder($orderId) {
        return $this->db->update('orders', array("isDelete" => 1), ' id =' . $orderId);
    }

    //我的订单列表
    public function getMyList($uid, $status) {
        $where = " WHERE A.userId = {$uid} ";
        if ($status == 1) {
            $where .= " AND A.isPay = '1' ";
        } elseif ($status == 2) {
            $where .= " AND A.isPay = '1' AND A.isDeliver = '1' AND ( A.isTake = '0' OR A.isTake = '1' ) ";
        } elseif ($status == 3) {
            $where .= " AND A.isPay = '1' AND A.isDeliver = '2' AND A.isTake = '1' ";
        } elseif ($status == 4) {
            $where .= " AND A.isDel = '1' AND A.isPay = '1' ";
        }
        $sql = "SELECT
    	    A.id,
    	    A.orderCode,
    	    A.price AS orderPrice,
    	    A.payWay,
    	    A.isPay,
    	    A.isDeliver,
    	    A.isTake,
    	    A.isDel,
    	    A.tel,
    	    A.remark,
    	    A.payTime,
    	    A.province,
    	    A.city,
    	    A.area,
    	    A.address,
    	    A.uname,
    	    A.goodsId,
    	    B.id AS gid,
    	    B.name AS goodsName,
    	    B.price AS goodsPrice
    	    FROM orders AS A
    	    LEFT JOIN goods AS B ON A.goodsId = B.id
    	    {$where}
    	    ORDER BY A.createTime DESC";
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['goodsImg'] = Service::getInstance('goods')->getGoodsOneImg($v['gid']);
            if ($v['isDel'] == 1) {
                $data[$k]['payStatus'] = 1;
            } elseif ($v['isTake'] == 2) {
                $data[$k]['payStatus'] = 2;
            } elseif (($v['isTake'] == 1 ) || ( $v['isTake'] == 0 && $v['isDeliver'] == 2 )) {
                $data[$k]['payStatus'] = 3;
            } elseif (( $v['isTake'] == 0 && $v['isDeliver'] == 1 ) || ( $v['isDeliver'] == 0 && $v['isPay'] == 1 )) {
                $data[$k]['payStatus'] = 4;
            } elseif ($v['isPay'] == 0) {
                $data[$k]['payStatus'] = 5;
            } else {
                $data[$k]['payStatus'] = 6;
            }
        }
        return $data;
    }

    public function getDetail($id) {
        $sql = " SELECT id,orderCode,price,userId,uname,tel,province,city,area,address,createTime,payTime,shopId,freight,goodsId,operator FROM orders where id = {$id}";
        $data = $this->db->fetchRow($sql);
        $cus = $this->getCustomer($data['userId']);
        $data['userhead'] = $cus['headimgurl'];
        $shop = Service::getInstance('shop')->getShopinfo($data['shopId']);
        $data['shopName'] = $shop['name'];
        $goods = Service::getInstance('goods')->getGoodsById($data['goodsId']);
        $data['goodsImg'] = Service::getInstance('goods')->getGoodsOneImg($data['goodsId']);
        $data['goodsName'] = $goods['name'];
        $data['goodsPrice'] = $goods['price'];
        if ($data['province']) {
            $data['proName'] = $this->getProCityAreaName($data['province']);
        }
        if ($data['city']) {
            $data['cityName'] = $this->getProCityAreaName($data['city']);
        }
        if ($data['area']) {
            $data['areaName'] = $this->getProCityAreaName($data['city']);
        }

        return $data;
    }

    private function getCustomer($id) {
        return $this->db->fetchRow('select id,headimgurl from customer where id =' . $id);
    }

    //付款方式
    public function getPayWay() {
        $sql = 'select id,name from pay_way';
        return $this->db->fetchAll($sql);
    }

    //付款类型
    public function getPayType($pid = 0) {
        $where = '';
        if ($pid) {
            $where = ' where pid = ' . $pid;
        }
        $sql = 'select id,name from pay_type ' . $where;
        return $this->db->fetchAll($sql);
    }

    //付款类型
    public function getPayBank($pid = 0) {
        $where = '';
        if ($pid) {
            $where = ' where a.tid = ' . $pid;
        }
        $sql = "select a.id,a.tid,a.bid,b.name,b.bank from pay_bank as a left join bank as b on a.bid = b.id {$where} group by bid";
        return $this->db->fetchAll($sql);
    }

    public function getPayBankById($id) {
        $sql = " SELECT A.id,A.tid,A.bid,B.name,B.bank FROM pay_bank AS A LEFT JOIN bank AS B ON A.bid = B.id WHERE A.id = " . $id;
        return $this->db->fetchRow($sql);
    }

    public function getPayWById($id) {
        $sql = " SELECT name FROM pay_way WHERE id = " . $id;
        return $this->db->fetchOne($sql);
    }

    public function getPayTypeById($id) {
        $sql = " SELECT name FROM pay_type WHERE id = " . $id;
        return $this->db->fetchOne($sql);
    }

    public function getOrderNum($id) {
        $sql = " SELECT `goodsId`,`number`,`price` FROM orders WHERE id = " . $id;
        return $this->db->fetchRow($sql);
    }
    
    public function getTotal($shopId) {
        $where = " WHERE `status` <> '5' AND goodsStock <> '0' AND shopId = {$shopId} ";
        $sql = "SELECT 
                CASE 
                WHEN `cost` = 0 THEN sum(purchPrice*goodsStock) 
                ELSE sum(cost*goodsStock) 
                END AS costTotal, 
                sum(goodsStock) AS stockTotal, 
                COUNT(*) AS number 
                FROM `goods` {$where} ";
        return $this->db->fetchRow($sql);
    }

    public function checkBrush($orderId) {
        $sql = "SELECT count(*) FROM orders WHERE id = {$orderId} AND sellType = 6 ";
        return $this->db->fetchOne($sql);
    }

    public function addOrderGoods( $data ) {
        $res = $this->db->insert('orders_goods',$data);
        return $res;
    }

    public function getAccountStatus( $orderId ){
        $sql = " SELECT account_status FROM orders WHERE id = {$orderId} ";
        $res = $this->db->fetchOne( $sql );
        return $res;
    }

    public function getOrderPayTime( $orderId ){
        $sql = " SELECT payTime FROM orders WHERE id = {$orderId} ";
        $res = $this->db->fetchOne( $sql );
        return $res;
    }

    //获取 添加需要添加到account_shop表的订单数据
    public function getAccountDataById( $orderId ){
        $sql = " SELECT A.id,A.shopId,B.goods_price,A.number,C.real_certificate,
                    C.real_freight,C.real_mount,C.real_other,C.real_pack
                    FROM `orders` AS A 
                    LEFT JOIN `orders_goods` AS B ON A.id = B.order_id  
                    LEFT JOIN `orders_price` AS C ON A.id = C.orderId 
                    WHERE A.id IN ( {$orderId} ) AND A.account_status IN ( 0,3 ) ";
        $result['data'] = $this->db->fetchAll( $sql );
        $sql = " SELECT id FROM `orders` WHERE account_status IN ( 1,2 ) ";
        $result['remOrder'] = $this->db->fetchAll( $sql );
        return $result;
    }

    //获取店铺Id
    public function getSHopIdByOrder( $id ){
        $sql = " SELECT shopId FROM orders WHERE id = {$id} ";
        $res = $this->db->fetchOne( $sql );
        return $res;
    }    

    public function addOrderBuyer( $data ){
        $this->db->insert('orders_buyer',$data);
        return $this->db->lastInsertId();
    }

    public function getBuyerByOrderId( $orderId ){
        return $this->db->fetchRow(" SELECT * FROM orders_buyer WHERE order_id = '{$orderId}' ");
    }
    
    //新增退货信息
    public function addOrderReback( $data ) {
        $this->db->insert('reback',$data);
        return $this->db->lastInsertId();
    }
    
    //获取退货信息
    public function getRebackInfo($start,$end,$countType = 'day'){
        
        $where = "WHERE 1=1 ";
        if($start && $end){
            $where .= " AND reback_time BETWEEN {$start} AND {$end}";
        }
        switch ($countType) {
            case 'day':
                $group = " FROM_UNIXTIME(reback_time, '%Y-%m-%d' )";
                $field = " reback_time,count(id) as num,sum(reback_price) as price";
                break;
            case 'buyer':
                $group = ' buyer';
                $field = " buyer,count(order_id) as order_num,sum(reback_num) as goods_num,sum(reback_price) as price";
                break;
            case 'reason':
                $group = ' reback_reason';
                $field = " reback_reason,count(order_id) as order_num,sum(reback_num) as goods_num,sum(reback_price) as price";
                break;
                
        }
        $sql = "SELECT {$field} FROM reback {$where} GROUP BY {$group}";
        $reback['list'] = $this->db->fetchAll($sql);
        $order_num_total = 0;
        $order_price_total = 0;
        $reback_num_total = 0;
        $reback_price_total = 0;
        $reback_goods_total = 0;
        $reback_order_total = 0;
        $scale_total = 0;
        if($countType == 'sales'){
            
        }
        if($countType == 'reason'){
            foreach($reback['list'] as $k => $row){
                switch ($row['reback_reason']) {
                    case 0:
                        $reback['list'][$k]['reback_reason'] = '未指定';
                        break;
                    case 1:
                        $reback['list'][$k]['reback_reason'] = '买家原因';
                        break;
                    case 2:
                        $reback['list'][$k]['reback_reason'] = '产品原因';
                        break;
                    case 3:
                        $reback['list'][$k]['reback_reason'] = '其他';
                        break;
                    case 4:
                        $reback['list'][$k]['reback_reason'] = '发错地址';
                        break;
                    case 5:
                        $reback['list'][$k]['reback_reason'] = '发错数量';
                        break;
                    case 6:
                        $reback['list'][$k]['reback_reason'] = '发错货';
                        break;
                    case 7:
                        $reback['list'][$k]['reback_reason'] = '未及时发货';
                        break;
                }
                $reback_order_total += $row['order_num'];
                $reback_goods_total += $row['goods_num'];
                $reback_price_total += $row['price'];              
                
            }
            $reback['reback_order_total'] = $reback_order_total;
            $reback['reback_goods_total'] = $reback_goods_total;
            $reback['reback_price_total'] = $reback_price_total;
        }
        if($countType == 'day'){           
            foreach($reback['list'] as $k => $row){
                $orderInfo = $this->getOrderGroupTime($row['reback_time']);
                $reback['list'][$k]['order_num'] = intval($orderInfo['orderNum']);
                $reback['list'][$k]['order_price'] = intval($orderInfo['orderPrice']);
                if($row['num'] == 0){
                    $scale = 0;
                }elseif($reback['list'][$k]['order_num'] == 0 && $row['num'] != 0){
                    $scale = 1;
                }else{
                    $scale = $row['num']/$reback['list'][$k]['order_num'];
                }
                $reback['list'][$k]['scale'] = round($scale*100,2).'%';
                $order_num_total += $orderInfo['orderNum'];
                $order_price_total += $orderInfo['orderPrice'];
                $reback_num_total += $row['num'];
                $reback_price_total += $row['price'];
            }
            $reback['order_num_total'] = $order_num_total;
            $reback['order_price_total'] = $order_price_total;
            $reback['reback_num_total'] = $reback_num_total;
            $reback['reback_price_total'] = $reback_price_total;
            if($reback['order_num_total'] == 0){
                $reback['scale_total'] = '100%';
            }else{
                $reback['scale_total'] = round(($reback['reback_num_total']/$reback['order_num_total'])*100,2).'%';
            }            
        }
        
        return $reback;
    }
    
    public function getOrderGroupTime($time){
        $sql = "SELECT sum(number) as orderNum,sum(price) as orderPrice FROM orders WHERE FROM_UNIXTIME(createTime, '%Y-%m-%d' ) = FROM_UNIXTIME(".intval($time).", '%Y-%m-%d' ) GROUP BY FROM_UNIXTIME(createTime, '%Y-%m-%d' )";
        $res = $this->db->fetchRow($sql);
        return $res;
    }

    //查询accountId 
    public function getAccountIdByOrder( $id ) {
        $sql = " SELECT accountId FROM `orders` WHERE id = {$id} ";
        $res = $this->db->fetchOne( $sql );
        return $res;
    }

    //通过结算单id查询结算金额
    public function getPriceByAccount( $AccountId ) {
        $sql = " SELECT A.number,
                B.goods_price,
                C.real_freight,
                C.real_certificate,
                C.real_pack,
                C.real_other,
                C.real_mount 
                FROM `orders` AS A 
                LEFT JOIN orders_goods AS B ON A.id = B.order_id 
                LEFT JOIN orders_price AS C ON A.id = C.orderId 
                WHERE A.accountId = {$AccountId} ";
        $res = $this->db->fetchAll( $sql );
        return $res;
    }
    
    public function getOrdersGoodsNumAndPrice($id) {
        $sql = " SELECT `goods_number`,`goods_pay_price` FROM orders_goods WHERE id = " . $id;
        return $this->db->fetchRow($sql);
    }
    
    //获取order_goods表退货状态不是全部退款的数据
    public function getOrderGoodsRebackState($id){
        $sql = " SELECT `id` FROM orders_goods WHERE order_id = " . intval($id) . " AND reback_state <> 1";
        return $this->db->fetchAll($sql);
    }
    
    //改变orders_goods表退货状态与数量
    public function setOrderGoodsInfo($id,$data){
        return $this->db->update('orders_goods', $data, ' id =' . intval($id));
    }

    //获取渠道的超限额订单
    public function getBeyondOrders( $channel ){
        $sql = " SELECT id,price FROM orders WHERE channel = {$channel} AND beyond_limit = 1 ORDER BY createTime ";
        $res = $this->db->fetchAll( $sql );
        return $res;
    }
    
    //获取该订单商品退货数量
    public function getRebackNumAndPriceById($order_id,$goods_id){
        $sql = " SELECT sum(reback_num) AS rebackNum,sum(reback_price) AS rebackPrice FROM reback WHERE order_id = {$order_id} AND goods_id = {$goods_id} GROUP BY goods_id";
        return $this->db->fetchRow( $sql );       
    }
    public function editOrdersGoods( $data, $id ){
        $res = $this->db->update("orders_goods",$data,' order_id =' . $id);
        return $res;
    }
}


