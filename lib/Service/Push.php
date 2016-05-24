<?php

class Service_Push extends Service
{
	private $error;
	private $shop;
	private $cateNames;


	public function getError() {
		return $this->error;
	}

	public function init(  )
	{
		$this->shop = array();
		$this->cateNames = array();
	}

	public function goodsList( $jid = 0,$page = 1, $perpage = 15 ,$cid = 0 , $thumb = '1' )
	{

		$where = "";
		if ( intval( $cid ) )
		{
			$Level = Service::getInstance('goods')->getCatLevel( $cid );
			$where = " and  g.category".intval(  $Level ) . " = " . $cid ;
		}
		if ( intval( $jid ) )
		{
			$where .= " and p.channel = " . $jid ;
		}


		$totalSql = "select  count(1) from goods as g , `push` as p where g.id = p.goodsId 
				and p.status = 0  and g.status = 1 " . $where ;

		$total = $this->db->fetchOne($totalSql);
		$pages = ceil($total / $perpage);

		$sql = " select g.id ,g.name , g.purchPrice, g.attribute,g.category3,g.intro,g.shopId 
				from goods as g , `push` as p where g.id = p.goodsId 
				and p.status = 0  and g.status = 1 " . $where . " order BY id DESC".$this->db->buildLimit($page, $perpage);

	    $goods = $this->db->fetchAll( $sql );
	    
	    $list = array();
        $query = $this->db->query( $sql  );
        while ($row = $query->fetch()) 
        {
        	$row['category'] = $this->getcateName( $row['category3'] );
        	unset( $row['category3'] );
        	$shop = $this->getShop( intval( $row['shopId'] ) );
        	$row['price'] =  round($row['purchPrice'] * $shop['ptimes']);
        	$row['oldprice'] =  round($row['purchPrice'] * $shop['mtimes']);
        	unset( $row['shopId'] );
        	unset( $row['purchPrice'] );
        	$row['thumb'] = Service::getInstance('goods')->getGoodsOneImg( $row['id'],$thumb );
        	$row['imgs'] = Service::getInstance('goods')->getGoodsImgHashByGoodsId( $row['id'],'',$thumb );
        	$row['attribute'] = json_decode( $row['attribute'] , true );
        	$row['intro'] = htmlspecialchars_decode($row['intro']);
        	$row['total'] = $total;
        	$row['pages'] = $pages;
            array_push($list, $row);
        }
		return $list;
	}

	public function edit( $data, $id ) {
	    return $this->db->update( 'push', $data, ' id ='.$id );
	}

	public function editByWhere( $data,$where ){
		return $this->db->update( 'push',$data,$where );
	}

	public function xiuhuaGoodsList( $jid = 0, $page = 1, $perpage = 15 ,$cid = 0 ,$thumb = '1' )
	{

		$where = " where 1 = 1 ";
		if ( intval( $cid ) )
		{
			$Level = Service::getInstance('goods')->getCatLevel( $cid );
			$where .= " and category".intval(  $Level ) . " = " . $cid ;
		}
		if ( intval( $jid ) )
		{
		    $where .= " and channel = " . $jid ;
		}

		$totalSql = "select * from view_goods_xiuhua " . $where ;

		$total = $this->db->fetchOne($totalSql);
		$pages = ceil($total / $perpage);

		$sql = " select * from view_goods_xiuhua " . $where . " order BY id DESC".$this->db->buildLimit($page, $perpage);

	    $goods = $this->db->fetchAll( $sql );
	    
	    $list = array();
        $query = $this->db->query( $sql  );
        while ($row = $query->fetch()) 
        {
        	$row['category'] = $this->getcateName( $row['category3'] );
        	unset( $row['category3'] );
        	$shop = $this->getShop( intval( $row['shopId'] ) );
        	$row['price'] =  round($row['purchPrice'] * $shop['ptimes']);
        	$row['oldprice'] =  round($row['purchPrice'] * $shop['mtimes']);
        	unset( $row['shopId'] );
        	unset( $row['purchPrice'] );
        	$row['thumb'] = Service::getInstance('goods')->getGoodsOneImg( $row['id'],$thumb );
        	$row['imgs'] = Service::getInstance('goods')->getGoodsImgHashByGoodsId($row['id'],'',$thumb );
        	$row['attribute'] = json_decode( $row['attribute'] , true );
        	$row['intro'] = htmlspecialchars_decode($row['intro']);

        	$row['total'] = $total;
        	$row['pages'] = $pages;
            array_push($list, $row);
        }
        //if ( intval( $jid ) == 1 ) $list = array();
		return $list;
	}

	public function geGoodsList( $jid = 0, $page = 1, $perpage = 15 ,$cid = 0 ,$thumb = '1' )
	{

		$where = " where 1 = 1 ";
		if ( intval( $cid ) )
		{
			$Level = Service::getInstance('goods')->getCatLevel( $cid );
			$where .= " and category".intval(  $Level ) . " = " . $cid ;
		}
		if ( intval( $jid ) )
		{
		    $where .= " and channel = " . $jid ;
		}

		$totalSql = "select count(*) from view_goods_1ge " . $where ;

		$total = $this->db->fetchOne($totalSql);
		$pages = ceil($total / $perpage);

		$sql = " select * from view_goods_1ge " . $where . " order BY pushId DESC".$this->db->buildLimit($page, $perpage);

	    $goods = $this->db->fetchAll( $sql );
	    
	    $list = array();
        $query = $this->db->query( $sql  );
        while ($row = $query->fetch()) 
        {
        	$attr = $row['attribute'] ? json_decode( $row['attribute'] , true ) : array();
        	if( !empty($attr) ){
	        	foreach( $attr as $k => $v ){
	        		if( !$v['value']['name'] ) unset( $attr[$k] );
	        	}
	        	sort($attr);
        	}
        	$row['attribute'] = $attr;
        	$row['category'] = $this->getcateName( $row['category3'] );
        	unset( $row['category3'] );
        	$shop = $this->getShop( intval( $row['shopId'] ) );
        	$row['price'] =  round($row['purchPrice'] * $shop['ptimes']);
        	$row['oldprice'] =  round($row['purchPrice'] * $shop['mtimes']);
        	unset( $row['shopId'] );
        	unset( $row['purchPrice'] );
        	$row['thumb'] = Service::getInstance('goods')->getGoodsOneImg( $row['id'] ,$thumb);
        	$row['imgs'] = Service::getInstance('goods')->getGoodsImgHashByGoodsId($row['id'],'',$thumb );
        	$row['intro'] = htmlspecialchars_decode($row['intro']);

        	$row['total'] = $total;
        	$row['pages'] = $pages;

            array_push($list, $row);
        }
        //if ( intval( $jid ) == 1 ) $list = array();
		return $list;
	}

	public function getOneGoodsById( $channel,$gid,$thumb ){
		$sql = " SELECT * FROM view_goods_1ge WHERE id = '{$gid}' ";
		$query = $this->db->query( $sql );
		if( $row = $query->fetch() ){

			$row['category'] = $this->getcateName( $row['category3'] );
        	unset( $row['category3'] );
        	$shop = $this->getShop( intval( $row['shopId'] ) );
        	$row['price'] =  round($row['purchPrice'] * $shop['ptimes']);
        	$row['oldprice'] =  round($row['purchPrice'] * $shop['mtimes']);
        	unset( $row['shopId'] );
        	unset( $row['purchPrice'] );
        	$row['thumb'] = Service::getInstance('goods')->getGoodsOneImg( $row['id'] ,$thumb);
        	$row['imgs'] = Service::getInstance('goods')->getGoodsImgHashByGoodsId($row['id'],'',$thumb );
        	$row['attribute'] = json_decode( $row['attribute'] , true );
        	$row['intro'] = htmlspecialchars_decode($row['intro']);

        	return $row;
		}else{
			return false;
		}
	}

	public function buy( $data , $goods , $channel = 0 )
	{
		if ( !isset( $data['tel'] ) ) return false;
		if ( !isset( $data['uname'] ) ) return false;

		$userId = Service::getInstance('orders')->getUser( $data['tel'], $data['uname'] );
		$data['userId'] = $userId;
		$data['orderCode'] = Service::getInstance('orders')->getOrderCode(1,3);
		$data['createTime'] = time();
		$data['payTime'] = time();
		$data['deliverWay'] = 1;
		//根据店铺判断订单类型
		if( $data['shopId'] == 105 ){
			$data['orderType'] = 4;
			$data['isBuyer'] = 0;
		}else{
			$data['orderType'] = 3;
			$data['isBuyer'] = 1;
		}
		
		$data['channel'] = $channel;
		$data['isPay'] = 1;
		$data['isDeliver'] = 1;
		$data['sellType'] = 4;

		$shop = $this->getShop( intval( $data['shopId'] ) );
		$ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop( $channel,$data['shopId'] );
		$price = round( $goods['purchPrice'] * $shop['ptimes'] * $ctimes );
		$data['price'] = $price;
		$id = Service::getInstance('orders')->add( $data , $channel );

        //添加订单商品
        $number = isset($data['number']) ? $data['number'] : 1;
        $orderGoods = array(
            'order_id'        => $id,
            'goods_id'        => $goods['id'],
            'shop_id'         => $goods['shopId'],
            'customer_id'     => $userId,
            'goods_name'      => $goods['name'],
            'goods_image'     => $goods['goodsOneImg'],
            'goods_price'     => $goods['purchPrice'],
            'goods_number'    => $number,
            'goods_pay_price' => $price,
        );
        $addOrderGoods = Service::getInstance('orders')->addOrderGoods($orderGoods);
		return $id;
	}

	public function getOrderExpress( $orderId )
	{
		return Service::getInstance('orders')->getOrderExpress( intval( $orderId ) );
	}

	public function cate( )
	{
		$sql = "select * from category ";
	    $list = array();
        $query = $this->db->query( $sql  );
        while ($row = $query->fetch()) 
        {
        	array_push($list, $row);
        }
        return $list;		
	}

	private function getcateName( $id )
	{
		if ( isset( $this->cateNames[$id] ) ) return $this->cateNames[$id];
		$cate = Service::getInstance('goods')->getCategoryNameById( $id );

		if ( !$cate ) return "未分类";

		$this->cateNames[$id] = $cate;

		return $cate;

	}

	public function getShop( $id )
	{
		if ( isset( $this->shop[$id] ) ) return $this->shop[$id];

		$info = Service::getInstance('shop')->getShopinfo( $id );
		if ( !isset( $info['ptimes'] ) ) return 1.5;

		$row['ptimes'] = $info['ptimes'];
		$row['mtimes'] = $info['mtimes'];
		$this->shop[$id] = $row;
		return $row;
	}

    public function getPushInfo( $id ) {
        $data = $this->db->fetchRow( 'select * from push where id = '.intval( $id ) );
        return $data;
    }

    public function getChannelByPuid( $puid ) {
        return $this->db->fetchOne( 'select channel from push where id = '.intval( $puid ) );
    }

    public function getAccurateInfo( $gid,$cid ){
    	return $this->db->fetchRow( " SELECT * FROM push WHERE goodsId = '{$gid}' AND channel = '{$cid}' " );
    }


    public function delPresell( $pushId ) {
	    return $this->db->delete( '`presell`', ' pushId ='.$pushId);
	}

	public function getPushWithGoodsId( $goodsId) {
		$data = $this->db->fetchAll( 'select id from push where goodsId = '.intval( $goodsId ) );
		return $data;
	}

    public function delPush( $goodsId ) {
        return $this->db->delete( 'push', ' goodsId = '.intval( $goodsId ) );
    }

    public function countChannel( $cid ) {
    	$sql =  'SELECT CASE WHEN `cost` = 0 
                THEN sum(B.purchPrice*C.ptimes*B.goodsStock) 
                ELSE sum(B.cost*C.ptimes*B.goodsStock) 
                END AS costTotal, 
                sum(goodsStock) AS stockTotal, 
                count(*) AS skuNum FROM push AS A 
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN shop AS C ON C.id = B.shopId 
                WHERE B.goodsStock < 130 AND A.channel = '.$cid;
        return $this->db->fetchRow($sql);
    }

    // 获取费用信息(参数为pid)
    public function getCostInfo($pid)
    {
      $sql="SELECT id,costName,price FROM cost WHERE pid={$pid}";
      return $this->db->fetchAll($sql);
    }

    //根据商品ID获取推送信息
    public function getPushInfoByGid( $gid ){
        $sql = " SELECT channel FROM push WHERE goodsId = {$gid} ";
        $result = $this->db->fetchAll( $sql );

        $channel = array();
        foreach( $result as $row ){
            $channel[] = $row['channel'];
        }

        $channels = implode(',',$channel);
        $sql = " SELECT C.name,D.openId FROM channel AS C LEFT JOIN developers AS D ON C.devId = D.id WHERE C.id IN ( ".$channels." ) ";
        $info = $this->db->fetchAll( $sql );

        $openIds = array();
        $cnames = '';
        foreach( $info as $row ){
            if( !in_array( $row['openId'], $openIds ) ){
                $openIds[] = $row['openId'];
            }
            $cnames .= $row['name'].' | ';
        }
        $data['openIds'] = $openIds;
        $data['cnames'] = trim( $cnames,' | ' );

        return $data;
    }

    // 通过批次pushIds获取goodsId/pTimes/purchPrice/quota
    public function getMulPushInfo($pushIds)
    {
      $sql= "SELECT 
            push.id as pushId,
            goods.purchPrice,
            goods.goodsStock, 
            goods.id as goodsId,
            shop.ptimes,
            shop.quota
            FROM push  
            JOIN goods 
            ON push.goodsId=goods.id 
            JOIN shop 
            ON goods.shopId=shop.id
            WHERE push.id
            IN
            ({$pushIds})";
            // echo $sql;exit;
      return $this->db->fetchAll($sql);
    }
    
    public function getCheckPushList($page, $perpage, $keyword, $channel, $status, $searchType, $showType = '', $isCount = '') {
		$map = '';
		if ($keyword != '') {
			if ($searchType == 1) {
				$map .= " AND ( B.goodsNo LIKE '%{$keyword}%' OR B.code LIKE '%{$keyword}%' OR B.name LIKE '%{$keyword}%' ) ";
			} elseif ($searchType == 2) {
				$map .= " AND ( B.code LIKE '{$keyword}' ) ";
			} elseif ($searchType == 3) {
				$map .= " AND ( B.goodsNo LIKE '{$keyword}' ) ";
			}
		}

		if ($channel != '') {
			$map .= " AND A.channel = {$channel} ";
		}
		if($status != ''){
                    $map .=" AND A.status = {$status} ";
		} 
                
               

		$developer = Yaf_Registry::get('developer');
		$roleId=$developer['role'];
		if (!in_array('1', $roleId) && !in_array('6', $roleId)) {
				$disId = $developer['disId'];
				$map .= " AND A.channel IN ( {$disId} ) ";
			}
                
                $map1 = $map;
                if($showType != ''){
                    switch($showType){
                        case  'pingtai':
                            $fromWhere = 2;
                            break;
                        case  'shangjia':
                            $fromWhere = 1;
                            break;                      
                    }
                    $map .= " AND A.fromWhere = {$fromWhere}";
                }

		// 判断是否只获取数量
		if($isCount == 1){
			$sql = "SELECT count(DISTINCT(A.goodsId)) FROM push AS A 
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				LEFT JOIN shop AS C ON C.id = B.shopId WHERE (B.status IN (1,2,3,6)) {$map}";
			return $this->db->fetchOne($sql);
		} 

		$sql = "SELECT 
				A.goodsId,
				A.id AS id,
				A.createTime,
				A.channel,
				A.status,
				A.fromWhere,
				B.name,
				B.intro,
				B.code,
				B.goodsNo,
				B.goodsStock,
				B.purchPrice,
				B.attribute,
				B.shopId,
				C.ptimes,
				P.preTime,
				P.startTime,
				P.endTime,
				P.sellType
				FROM push AS A 
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				LEFT JOIN shop AS C ON C.id = B.shopId 
				WHERE ((B.status IN (1,2,3,6)) ) {$map} GROUP BY A.goodsId ORDER BY A.id DESC ".$this->db->buildLimit($page, $perpage);

		$goods = $this->db->fetchAll($sql);
		foreach ( $goods as $k=>$v ) {			

			/**
			 * 获取渠道价格
			 * update:2016-04-27 11:29 By breakdinner
			 * @var [type]
			 */
			$cTimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
			$goods[$k]['channelPrice'] = round($v['purchPrice']*$v['ptimes']*$cTimes);
			$shop = Service::getInstance('shop')->getShopinfo($v['shopId']);
			$ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
			$goods[$k]['shopName'] = $shop['name'];
			$goods[$k]['ctimes'] = $ctimes;
			$goods[$k]['mtimes'] = $shop['mtimes'];
			$goods[$k]['ptimes'] = $shop['ptimes'];
			$cname = Service::getInstance('goods')->getChannelById( $v['channel'] );
			$goods[$k]['cname'] = $cname ? $cname['name'] : '';
			$goods[$k]['thumb'] = Service::getInstance('goods')->getGoodsOneImg($v['goodsId']);
		}
		$data['list'] = $goods;

		// 获取数量	
                $sql = "SELECT count(DISTINCT(A.goodsId)) FROM push AS A 
                                LEFT JOIN goods AS B ON A.goodsId = B.id 
                                LEFT JOIN presell AS P ON A.id = P.pushId 
                                LEFT JOIN shop AS C ON C.id = B.shopId WHERE ( (B.status IN (1,2,3,6)) ) {$map}";
		$data['total'] = $this->db->fetchOne($sql);
		return $data;
	}
}

