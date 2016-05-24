<?php

class Service_Goods extends Service {

	private $error;

	public function getError() {
		return $this->error;
	}

	//获得商品列表
	public function goodsList($uid, $status, $lastId, $shopId, $pageNum) {
		if (!$shopId) {
			$shopId = $this->db->fetchOne('SELECT A.id FROM shop as A left join shopkeeper as B on A.id=B.sid WHERE B.uid=' . $uid . ' ORDER BY B.id DESC LIMIT 1');
		}

		if ($lastId) {
			$WHERE = ' AND A.id < ' . $lastId;
		} else {
			$WHERE = ' ';
		}
		if ($status == 1) {
			$sql = "SELECT A.id,A.name,A.code,A.goodsNo,A.price,A.attribute,A.status,A.remark,A.createTime,A.recommend,A.intro,A.freight,A.shopId,A.category1,A.category2,A.category3,A.showPrice,A.groups,A.cost,A.goodsStock,A.purchPrice,A.isChannel FROM goods AS A WHERE A.shopId = {$shopId}  AND A.status = '1' {$WHERE} ORDER BY A.id DESC LIMIT {$pageNum} ";
			$total = $this->db->fetchOne("SELECT count(A.id) FROM goods AS A WHERE A.shopId = {$shopId} AND A.status = '1'");
		} elseif ($status == 2) {
			$sql = "SELECT A.id,A.name,A.code,A.goodsNo,A.price,A.attribute,A.status,A.remark,A.createTime,A.recommend,A.intro,A.freight,A.shopId,A.category1,A.category2,A.category3,A.showPrice,A.groups,A.cost,A.goodsStock,A.purchPrice,A.isChannel FROM goods AS A WHERE (A.status <> '5' AND A.status <> '2' AND A.shopId = {$shopId} AND A.status <> '1') {$WHERE} ORDER BY A.id DESC LIMIT {$pageNum} ";
			$total = $this->db->fetchOne("SELECT count(A.id) FROM goods AS A WHERE (A.status <> '5' AND A.status <> '2' AND A.shopId = {$shopId} AND A.status <> '1')");
		} elseif ($status == 3) {
			$sql = "SELECT A.id,A.name,A.code,A.goodsNo,A.price,A.attribute,A.status,A.remark,A.createTime,A.recommend,A.intro,A.freight,A.shopId,A.category1,A.category2,A.category3,A.showPrice,A.groups,A.cost,A.goodsStock,A.purchPrice,A.isChannel FROM goods AS A WHERE (A.shopId = {$shopId} AND A.status = '2') {$WHERE} ORDER BY A.id DESC LIMIT {$pageNum} ";
			$total = $this->db->fetchOne("SELECT count(A.id) FROM goods AS A WHERE (A.shopId = {$shopId} AND A.status = '2')");
		} elseif ($status == 0) {
			$sql = "SELECT A.id,A.name,A.code,A.goodsNo,A.price,A.attribute,A.status,A.remark,A.createTime,A.recommend,A.intro,A.freight,A.shopId,A.category1,A.category2,A.category3,A.showPrice,A.groups,A.cost,A.goodsStock,A.purchPrice,A.isChannel FROM goods AS A WHERE (A.status <> '5' AND A.shopId = {$shopId} ) {$WHERE} ORDER BY A.id DESC LIMIT {$pageNum} ";
			$total = $this->db->fetchOne("SELECT count(A.id) FROM goods AS A WHERE (A.status <> '5' AND A.shopId = {$shopId} )");
		}
		$data = $this->db->fetchAll($sql);
		foreach ($data as $k => $v) {
			$data[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$url = $this->getGoodsImgsByGoodsIds($v['id'], '1');
			$data[$k]['goodsImg'] = $url;
			$data[$k]['attribute'] = json_decode($v['attribute'], true) ? json_decode($v['attribute'], true) : null;
			$data[$k]['codeUrl'] = '/goods/goodshtmlinfo?id=' . $v['id'];
			$data[$k]['buyUrl'] = '/goods/index?gid=' . $v['id'];
		}
		return array('total' => $total, 'data' => $data);
	}

	public function getShopTags( $shopId ){
		$tags = Service::getInstance('shop')->getTagsByShopId( $param['shopId'] );
		if( !empty( $tags) ){
			$result = array();
			foreach( $tags as $tag ){
				$result[] = $tag['id'];
			}
			return $result;
		}
		return array();
	}

	//获得商品列表
	public function apiGoodsList( $param ) {

		$where = '';
		$where .= " G.shopId = {$param['shopId']} AND ";
		$shopTags = $this->getShopTags( $param['shopId'] );

		if( $param['category2'] ){
			$where .= ' G.category2 = '.$param['category2'];
		}
		switch( $param['status'] ){
			case 0:
				$where .= " G.status <> '5' ";
				break;
			case 1:
				$where .= " G.status = '1' ";
				break;
			case 2:
				$where .= " G.status IN ( 3,4 ) ";
				break;
			case 3:
				$where .= " G.status = '2' ";
				break;
		}
		
		if( $param['page'] < 1 ) $param['page'] = 0;
        $offset = ( ($param['page'] - 1)*$param['perpage'] ) + $param['changeNum'];
		$limit = " LIMIT {$offset},{$param['perpage']} ";

		$sql = "SELECT G.id,G.name,G.code,G.goodsNo,G.price,G.attribute,G.status,G.remark,G.createTime,G.recommend,G.intro,G.freight,G.shopId,G.category1,G.category2,G.category3,G.showPrice,G.groups,G.cost,G.goodsStock,G.purchPrice,G.isChannel FROM goods AS G WHERE {$where} ORDER BY G.id DESC {$limit} ";

		$data = $this->db->fetchAll($sql);

		foreach ($data as $k => $v) {
			$data[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$url = $this->getGoodsImgsByGoodsIds($v['id'], '1');
			$data[$k]['goodsImg'] = $url;
			$data[$k]['attribute'] = json_decode($v['attribute'], true) ? json_decode($v['attribute'], true) : null;
			$data[$k]['codeUrl'] = '/goods/goodshtmlinfo?id=' . $v['id'];
			$data[$k]['buyUrl'] = '/goods/index?gid=' . $v['id'];
		}
		$sql = " SELECT count(*) FROM goods AS G WHERE {$where}  ";
		$total = $this->db->fetchOne( $sql );
		return array('total' => $total, 'data' => $data);
	}

	//获得商品列表
	public function goodsAdmList($page, $perpage, $keyword, $status, $searchType, $isRecommend = '', $checkResult = '', $minPrice = '', $maxPrice = '', $isCount = '') {
		$map = ' WHERE A.status <> 5 ';
		if ($keyword != '') {
			if ($searchType == 1) {
				$map .= " AND ( A.goodsNo LIKE '%{$keyword}%' OR A.code LIKE '%{$keyword}%' OR A.name LIKE '%{$keyword}%' ) ";
			} elseif ($searchType == 2) {
				$map .= " AND ( A.code LIKE '{$keyword}' ) ";
			} elseif ($searchType == 3) {
				$map .= " AND ( A.goodsNo LIKE '{$keyword}' ) ";
			}
		}
		if ($checkResult != '') {
			$map .= " AND A.checkResult = '" . $checkResult . "'";
		}
		if ($isRecommend != '') {
			$map .= " AND A.recommend = '" . $isRecommend . "'";
		}
		if ($minPrice && $maxPrice) {
			$map .= " AND (A.purchPrice*B.ptimes) BETWEEN '{$minPrice}' AND '{$maxPrice}' ";
		} elseif ($minPrice) {
			$map .= " AND (A.purchPrice*B.ptimes) >= '{$minPrice}' ";
		} elseif ($maxPrice) {
			$map .= " AND (A.purchPrice*B.ptimes) <= '{$maxPrice}' ";
		}

		if (is_array($status)) {
			if (!array_diff($status, array(2, 3))) {
				$map .= " AND ( A.status = '2' OR A.status = '3' ) ";
			}
		} else {
			if ($status != '') {
				$map .= " AND A.status = '" . $status . "'";
			}
		}
		//$isCount为1表示只获取数量
		if($isCount == 1){
			$sql = "SELECT count(*) FROM goods AS A LEFT JOIN shop AS B ON A.shopId = B.id $map ";
			return $this->db->fetchOne($sql);            
		}
		$sql = "SELECT A.* FROM goods AS A LEFT JOIN shop AS B ON A.shopId = B.id {$map} ORDER BY A.id DESC" . $this->db->buildLimit($page, $perpage);
		$goods = $this->db->fetchAll($sql);
		foreach ($goods as $k => $v) {
			$check = $this->getCheckGoodsByGoodsId($v['id']);
			if ($check) {
				$goods[$k]['check'] = 1;
			} else {
				$goods[$k]['check'] = 0;
			}
			$shop = Service::getInstance('shop')->getShopinfo($v['shopId']);
			if ($v['fromWhere'] == 1) {
				$goods[$k]['uploader'] = $shop['name'] ? $shop['name'] : '';
			} else {
				$name = Service::getInstance('developers')->getNameById($v['uploader']);
				$goods[$k]['uploader'] = $name ? $name : '';
			}
			if ($v['editor']) {
				$editor = explode('-', $v['editor']);
				if ($editor[0] == 's') {
					$goods[$k]['editor'] = Service::getInstance('shop')->getShopNameById($editor[1]);
				} else {
					$goods[$k]['editor'] = Service::getInstance('developers')->getNameById($editor[1]);
				}
			} else {
				$goods[$k]['editor'] = $goods[$k]['uploader'];
				$goods[$k]['updateTime'] = $goods[$k]['createTime'];
			}
                        
                        $cname = $this->getChannelByGoodsId($v['id']);
			$goods[$k]['cname'] = $cname ? $cname : '';
                        $goods[$k]['ptimes'] = $shop['ptimes'];
			$goods[$k]['mtimes'] = $shop['mtimes'];
			$goods[$k]['thumb'] = $this->getGoodsOneImg($v['id']);
		}
		$data['list'] = $goods;

		$sql = "SELECT count(*) FROM goods AS A LEFT JOIN shop AS B ON A.shopId = B.id $map ";
		$data['total'] = $this->db->fetchOne($sql);
		return $data;
	}

	//获得商品列表.参数中添加了$inSellplan参数,并且WHERE条件中添加了inSellplan的判断,默认是0;checkStatus是页面的显示状态
	public function goodsPushList($page, $perpage, $keyword, $channel, $time = 0, $searchType, $minPrice = '', $maxPrice = '', $checkStatus= '', $isCount = '') {
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

		if ($minPrice && $maxPrice) {
			$map .= " AND (B.purchPrice*C.ptimes) BETWEEN '{$minPrice}' AND '{$maxPrice}' ";
		}elseif ($minPrice) {
			$map .= " AND (B.purchPrice*C.ptimes) >= '{$minPrice}' ";
		}elseif ($maxPrice) {
			$map .= " AND (B.purchPrice*C.ptimes) <= '{$maxPrice}' ";
		}

		if ($time == 0) {
			$time = " ,P.startTime DESC ";
		} elseif ($time == 1) {
			$time = " ,A.id DESC ";
		}
		if ($channel != '') {
			$map .= " AND A.channel = {$channel} ";
		}
                
                $filter_arr = array(                   
                    'pass'          => ' AND A.status = 0 AND B.status <> 2 AND B.status <> 3 AND (S.tradeCount is null OR (CAST(B.goodsStock AS SIGNED) - CAST(S.tradeCount AS SIGNED)) > 0)',
                    'waitCheck'     => ' AND A.status = 1 AND B.status <> 2 AND B.status <> 3',
                    'lock'          => ' AND A.status = 2 AND B.status <> 2 AND B.status <> 3',
                    'abnormal'      => ' AND A.status = 3 AND B.status <> 2 AND B.status <> 3',
                    'stop'          => ' AND (B.status = 2 OR B.status = 3) ',
                    'sellplan'      => ' AND A.status = 0 AND B.status <> 2 AND B.status <> 3 AND A.inSellplan = 1 AND S.isDel = 0'
                );
                $developer = Yaf_Registry::get('developer');		
		$roleId=$developer['role'];
		if (!in_array('1', $roleId) && !in_array('6', $roleId)) {
				$disId = $developer['disId'];
				$map .= " AND A.channel IN ( {$disId} ) ";
			}

		// 判断是否只获取数量
		if($isCount == 1){
			$sql = "SELECT count(DISTINCT(A.id)) FROM push AS A 
                                LEFT JOIN sell_plan as S ON A.id = S.pushId
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				LEFT JOIN shop AS C ON C.id = B.shopId WHERE (B.status IN (1,2,3,6))
                                {$map} ".$filter_arr[$checkStatus];
			return $this->db->fetchOne($sql);
		} 

		/**update:2016-04-29 17:30 By breakdinner
		 * 如果是待上架的商品,则要获取(库存量)-(销售计划对应商品的tradeCount交易量),如果大于0,则在页面的状态显示为  部分上架.
		 */

		$sql = "SELECT 
				DISTINCT(A.goodsId),
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
				B.status as g_status,
				B.checkResult,
                                B.taobao_special,
				C.ptimes,
				P.preTime,
				P.startTime,
				P.endTime,
				P.sellType,
				S.tradeCount,
				S.id AS sellplanId,
                                S.tradePrice,
                                S.batch
				FROM push AS A 
				LEFT JOIN sell_plan as S ON A.id = S.pushId
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				LEFT JOIN shop AS C ON C.id = B.shopId 
				WHERE ((B.status IN (1,2,3,6)))
				{$map} ".$filter_arr[$checkStatus]." GROUP BY A.goodsId ORDER BY B.status DESC {$time} ".$this->db->buildLimit($page, $perpage);
				// echo $sql;exit;
		$goods = $this->db->fetchAll($sql);
		$redisGoods=array();
		foreach ( $goods as $k=>$v ) {
			/**
			 * update:2016-04-29 17:38 By breakdinner
			 * 用goodsStock-对应商品的销售计划交易量
			 */
			if (empty($v['tradeCount'])) {
				$goods[$k]['tradeCount'] =0;
			}
			if($goods[$k]['tradeCount'] > 0 && ($v['goodsStock'] - $goods[$k]['tradeCount']) > 0 ){
				$goods[$k]['toShow'] = '部分上架';
			}else{
				$goods[$k]['toShow'] = '';
			}

			/**
			 * 获取渠道价格
			 * update:2016-04-27 11:29 By breakdinner
			 * @var [type]
			 */
			$cTimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
			$goods[$k]['channelPrice'] = $v['purchPrice']*$v['ptimes']*$cTimes;
			$goods[$k]['createTime'] = date('Y-m-d H:i:s ', $v['createTime']);
			$start = explode(' ',$goods[$k]['startTime']);
			$end   = explode(' ',$goods[$k]['endTime']);
			if(count($start)==1){
				$goods[$k]['startTime']=$goods[$k]['preTime'].$start[0];
				$goods[$k]['endTime']=$goods[$k]['preTime'].$end[0];
			}
			// 如果商品表中状态售罄,则相应改变推送商品状态;停售也一样
			switch ($goods[$k]['g_status']) {
				case 2:
					$goods[$k]['status']=5;
					break;
				case 3:
					$goods[$k]['status']=4;
					break;
			}
			$shop = Service::getInstance('shop')->getShopinfo($v['shopId']);
			$ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
			$goods[$k]['shopName'] = $shop['name'];
			$goods[$k]['ctimes'] = $ctimes;
			$goods[$k]['mtimes'] = $shop['mtimes'];
			$goods[$k]['ptimes'] = $shop['ptimes'];
			$cname = $this->getChannelById( $v['channel'] );
			$goods[$k]['cname'] = $cname ? $cname['name'] : '';
			$goods[$k]['thumb'] = $this->getGoodsOneImg($v['goodsId']);

			/**
			 * update:2016-04-27 13:00 By breakdinner
			 * 将需要导出excel表的字段添加到$redisGoods
			 */
			$redisGoods[$k]['code']=$goods[$k]['code'];
			$redisGoods[$k]['name']=$goods[$k]['name'];
			$redisGoods[$k]['purchPrice']=$goods[$k]['purchPrice'];
			$redisGoods[$k]['attribute']=$goods[$k]['attribute'];
			$redisGoods[$k]['status']=$goods[$k]['g_status'];
			$redisGoods[$k]['shopId']=$goods[$k]['shopId'];
			$redisGoods[$k]['intro']=$goods[$k]['intro'];
			$redisGoods[$k]['channel']=$goods[$k]['channel'];
			$redisGoods[$k]['checkResult']=$goods[$k]['checkResult'];
		}
		$data['list'] = $goods;
		/**
		 * 同时存入redis,要判断goods是否为空,否则取不出$goods[0]['channel']
		 * update:2016-04-27 12:53 By breakdinner
		 */
		if($goods && $checkStatus){
			// 获取状态商品的集合id
			$statusId = $this->getStatusId($checkStatus,$channel);
			Red::getInstance()->SET($statusId,json_encode($redisGoods));
		}

		// 获取数量
                $sql = "SELECT count(DISTINCT(A.id)) FROM push AS A 
                                LEFT JOIN sell_plan as S ON A.id = S.pushId
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				LEFT JOIN shop AS C ON C.id = B.shopId WHERE (B.status IN (1,2,3,6))
                                {$map} ".$filter_arr[$checkStatus];
		$data['total'] = $this->db->fetchOne($sql);
		return $data;
	}
	//获得商品列表
	public function getAllPushGoods() {
		$sql = "SELECT B.code,B.name,B.purchPrice,B.attribute,B.status,B.shopId,B.intro,A.channel,B.checkResult FROM push AS A inner JOIN goods AS B ON A.goodsId = B.id WHERE B.status = '1'";
		$goods = $this->db->fetchAll($sql);
		foreach ( $goods as $k=>$v ) {
			$shop = Service::getInstance('shop')->getShopinfo( $v['shopId'] );
			$dis = Service::getInstance('distributor')->getInfoById( $v['channel'] );
			$ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
			$goods[$k]['purchPrice'] = round($v['purchPrice'] * $shop['ptimes'] * $ctimes);
			unset($goods[$k]['shopId']);
			if ( $v['status'] == 1 ) {
				$goods[$k]['status'] = '在售';
			} elseif ( $v['status'] == 2 ) {
				$goods[$k]['status'] = '售罄';
			} elseif ( $v['status'] == 3 ) {
				$goods[$k]['status'] = '停售';
			} elseif ( $v['status'] == 4 ) {
				$goods[$k]['status'] = '其他下架原因';
			} else {
				$goods[$k]['status'] = '删除';
			}
			$goods[$k]['channel'] = $dis['name'];
			$goods[$k]['checkResult'] = $v['checkResult'] == 1 ? '已审核':'未审核';
			$goods[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$preg = "/<\/?[^>]+>/i";
			$goods[$k]['intro'] = preg_replace($preg,'',$goods[$k]['intro']);
			$attr = json_decode( $v['attribute'], true );
			$arr = array();
			if ( is_array($attr) ) {
				foreach ( $attr as $key=>$val ) {
					$arr[] = $val['key']['name'].'&'.$val['value']['name'];
				}
				$arr = implode(',', $arr);
				$goods[$k]['attribute'] = $arr;
			}
		}
		$list = array();
		foreach ( $goods as $k=>$v ) {
			foreach ( $v as $key=>$val ) {
				$list[$k][] = $val;
			}   
		}
		return $list;
	}

	/**
	 * 获取指定状态的推送商品
	 * update:2016-04-27 10:36 By breakdinner
	 */
	public function getPushGoodsByStatus($goods)
	{
		foreach ( $goods as $k=>$v ) {
			$shop = Service::getInstance('shop')->getShopinfo( $v['shopId'] );
			$dis = Service::getInstance('distributor')->getInfoById( $v['channel'] );
			$ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
			$goods[$k]['purchPrice'] = $v['purchPrice'] * $shop['ptimes'] * $ctimes;
			unset($goods[$k]['shopId']);
			if ( $v['status'] == 1 ) {
				$goods[$k]['status'] = '在售';
			} elseif ( $v['status'] == 2 ) {
				$goods[$k]['status'] = '售罄';
			} elseif ( $v['status'] == 3 ) {
				$goods[$k]['status'] = '停售';
			} elseif ( $v['status'] == 4 ) {
				$goods[$k]['status'] = '其他下架原因';
			} else {
				$goods[$k]['status'] = '删除';
			}
			$goods[$k]['channel'] = $dis['name'];
			$goods[$k]['checkResult'] = $v['checkResult'] == 1 ? '已审核':'未审核';
			$goods[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$preg = "/<\/?[^>]+>/i";
			$goods[$k]['intro'] = preg_replace($preg,'',$goods[$k]['intro']);
			$attr = json_decode( $v['attribute'], true );
			$arr = array();
			if ( is_array($attr) ) {
				foreach ( $attr as $key=>$val ) {
					$arr[] = $val['key']['name'].'&'.$val['value']['name'];
				}
				$arr = implode(',', $arr);
				$goods[$k]['attribute'] = $arr;
			}
		}
		$list = array();
		foreach ( $goods as $k=>$v ) {
			foreach ( $v as $key=>$val ) {
				$list[$k][] = $val;
			}   
		}
		return $list;
	}

	//获得商品的数目
	public function getGoodsRows($keyword, $status) {
		$map = '';
		if ($keyword != '') {
			$map .= " AND B.name like '%{$keyword}%' OR B.code like '%{$keyword}%' OR B.goodsNo like '%{$keyword}%' ";
		}
		if ($status != '') {
			$map .= " AND status = '" . $status . "' ";
		}
		$sql = " SELECT count(*) FROM goods AS B WHERE B.platform='2' AND B.status <> 5 " . $map;
		return $this->db->fetchOne($sql);
	}

	//获得商品列表
	public function getAllPushGoodsNoC($times, $size, $keyword, $status, $is_img = null) {
		$map = '';
		if ($keyword != '') {
			$map .= " AND ( B.name like '%{$keyword}%' OR B.code like '%{$keyword}%' OR B.goodsNo like '%{$keyword}%') ";
		}
		if ($status != '') {
			$map .= " AND status = '" . $status . "'";
		}
		//判断是否有偏移量
		$limit = "";
		if ($times != '' && $size != '') {
			$limit.=$this->db->buildLimit($times, $size);
		}
		//判断是否有图片
		$img = "";
		if ($is_img) {
			$img.= " B.id AS imageId, ";
		}

		$sql = "SELECT B.id AS gId," . $img . "B.name,B.code,B.goodsNo,B.purchPrice,B.goodsStock,B.attribute,B.intro,B.status,B.shopId,B.uploader,B.fromWhere FROM goods AS B WHERE B.platform='2' AND B.status <> 5 " . $map . " order by B.id desc " . $limit;
		$goods = $this->db->fetchAll($sql);
		foreach ($goods as $k => $v) {
			if ($v['status'] == 1) {
				$goods[$k]['status'] = '在售';
			} elseif ($v['status'] == 2) {
				$goods[$k]['status'] = '售罄';
			} elseif ($v['status'] == 3) {
				$goods[$k]['status'] = '停售';
			} elseif ($v['status'] == 4) {
				$goods[$k]['status'] = '其他下架原因';
			} elseif ($v['status'] == 5) {
				$goods[$k]['status'] = '删除';
			} elseif ($v['status'] == 6) {
				$goods[$k]['status'] = '锁定';
			} else {
				$goods[$k]['status'] = '异常';
			}
			$goods[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$preg = "/<\/?[^>]+>/i";
			$goods[$k]['intro'] = preg_replace($preg, '', $goods[$k]['intro']);
			$attr = json_decode($v['attribute'], true);
			$arr = array();
			if (is_array($attr)) {
				foreach ($attr as $key => $val) {
					$arr[] = $val['key']['name'] . '&' . $val['value']['name'];
				}
				$arr = implode(',', $arr);
				$goods[$k]['attribute'] = $arr;
			}
			if ($is_img) {
				//给数据增加图片路径
				$img = $this->getGoodsOneImgExport($v['imageId']);
				$goods[$k]['imageId'] = $img;
			}
		}
		$list = array();
		foreach ($goods as $k => $v) {
			foreach ($v as $key => $val) {
				$list[$k][] = $val;
			}
		}
		return $list;
	}

	//获得商品列表
	public function goodsPlatList($page, $perpage, $keyword, $status, $minPrice, $maxPrice, $checkResult, $searchType, $isCount = '') {
		$map = ' WHERE A.platform = "2" AND A.status <> 5 AND A.isChannel = 0 ';
		if ($keyword != '') {
			if ($searchType == 1) {
				$map .= " AND ( A.goodsNo LIKE '%{$keyword}%' OR A.code LIKE '%{$keyword}%' OR A.name LIKE '%{$keyword}%' ) ";
			} elseif ($searchType == 2) {
				$map .= " AND ( A.code LIKE '{$keyword}' ) ";
			} elseif ($searchType == 3) {
				$map .= " AND ( A.goodsNo LIKE '{$keyword}' ) ";
			}
		}

		if ($status != '') {
			$map .= " AND A.status = '" . $status . "'";
		}
		if ($checkResult != '') {
			$map .= " AND A.checkResult = '" . $checkResult . "'";
		}
		if ($minPrice && $maxPrice) {
			$map .= " AND (A.purchPrice*C.ptimes) BETWEEN '{$minPrice}' AND '{$maxPrice}' ";
		} elseif ($minPrice) {
			$map .= " AND (A.purchPrice*C.ptimes) >= '{$minPrice}' ";
		} elseif ($maxPrice) {
			$map .= " AND purchPrice *ptimes <= '{$maxPrice}' ";
		}
		//$isCount为1表示只获取数量
		if($isCount == 1){
			$sql = "SELECT count(*) FROM goods AS A LEFT JOIN `goods_check` AS B ON A.id = B.goodsId LEFT JOIN `shop` AS C ON A.shopId = C.id {$map} ";
			return $this->db->fetchOne($sql);
		}
		$sql = "SELECT 
				A.id,
				A.name,
				A.code,
				A.goodsNo,
				A.price,
				A.platfPrice,
				A.purchPrice,
				A.attribute,
				A.createTime,
				A.updateTime,
				A.status,
				A.remark,
				A.uploader,
				A.recommend,
				A.intro,
				A.shopId,
				A.freight,
				A.delflg,
				A.category1,
				A.category2,
				A.category3,
				A.orderTime,
				A.content,
				A.showPrice,
				A.groups,
				A.platform,
				A.checkResult,
				A.goodsStock,
						A.editor,
						A.fromWhere,
                                                A.taobao_special
		 FROM `goods` AS A LEFT JOIN `goods_check` AS B ON A.id = B.goodsId LEFT JOIN `shop` AS C ON A.shopId = C.id {$map} ORDER BY twoTime DESC " . $this->db->buildLimit($page, $perpage);
		//$sql = "SELECT * FROM goods $map ORDER BY id DESC".$this->db->buildLimit($page, $perpage);
		$goods = $this->db->fetchAll($sql);
		foreach ($goods as $k => $v) {
			$shop = Service::getInstance('shop')->getShopinfo($v['shopId']);
			$goods[$k]['shopName'] = $shop['name'];
			$goods[$k]['ptimes'] = $shop['ptimes'];
			$goods[$k]['mtimes'] = $shop['mtimes'];
			$cname = $this->getChannelByGoodsId($v['id']);
			$goods[$k]['cname'] = $cname ? $cname : '';
			$goods[$k]['thumb'] = $this->getGoodsOneImg($v['id']);
			//获取商品分类名称，暂时挨个请求数据库，将来分级放到redis里面
			$goods[$k]['category1'] = $this->getCategoryNameById($v['category1']);
			$goods[$k]['category2'] = $this->getCategoryNameById($v['category2']);
			$goods[$k]['category3'] = $this->getCategoryNameById($v['category3']);
			if ($v['fromWhere'] == 1) {
				$goods[$k]['uploader'] = $shop['name'] ? $shop['name'] : '';
			} else {
				$name = Service::getInstance('developers')->getNameById($v['uploader']);
				$goods[$k]['uploader'] = $name ? $name : '';
			}
			if ($v['editor']) {
				$editor = explode('-', $v['editor']);
				if ($editor[0] == 's') {
					$goods[$k]['editor'] = Service::getInstance('shop')->getShopNameById($editor[1]);
				} else {
					$goods[$k]['editor'] = Service::getInstance('developers')->getNameById($editor[1]);
				}
			} else {
				$goods[$k]['editor'] = $goods[$k]['uploader'];
				$goods[$k]['updateTime'] = $goods[$k]['createTime'];
			}
		}
		$data['list'] = $goods;
		$sql = "SELECT count(*) FROM goods AS A LEFT JOIN `goods_check` AS B ON A.id = B.goodsId LEFT JOIN `shop` AS C ON A.shopId = C.id {$map} ";
		$data['total'] = $this->db->fetchOne($sql);
		return $data;
	}

	public function getPresell($id) {
		return $this->db->fetchRow('SELECT * FROM `presell` WHERE pushId = ' . $id);
	}

	//搜索商品列表
	public function search($uid, $search, $shopId, $status, $goodsId) {
		if (!$shopId) {
			$shopId = $this->db->fetchOne('SELECT A.id FROM shop as A left join shopkeeper as B on A.id=B.sid WHERE B.uid=' . $uid . ' ORDER BY B.id DESC LIMIT 1');
		}
		if ($search) {
			$map = " AND (A.name like '%{$search}%' OR A.code like '%{$search}%' OR A.goodsNo like '%{$search}%') ";
		} else {
			$map = "";
		}
		if ($goodsId) {
			$map = " AND A.id like '{$goodsId}' ";
		}
		if ($status) {
			$map .= " AND status = {$status} ";
		}
		$sql = "SELECT A.id,A.name,A.code,A.goodsNo,A.price,A.attribute,A.status,A.remark,A.recommend,A.intro,A.freight,A.shopId,A.goodsStock,A.cost,A.purchPrice,A.showPrice,A.isChannel,A.createTime FROM goods AS A WHERE A.shopId = {$shopId} AND A.status <> '5' {$map} ORDER BY A.id DESC";
		$total = $this->db->fetchOne("SELECT count(A.id) FROM goods AS A WHERE A.shopId = {$shopId} AND A.status <> '5' {$map} ");
		$data = $this->db->fetchAll($sql);
		foreach ($data as $k => $v) {
			$url = $this->getGoodsImgsByGoodsIds($v['id'], '1');
			$data[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$data[$k]['goodsImg'] = $url;
			$data[$k]['attribute'] = json_decode($v['attribute'], true) ? json_decode($v['attribute'], true) : null;
			$data[$k]['codeUrl'] = '/goods/goodshtmlinfo?id=' . $v['id'];
			$data[$k]['buyUrl'] = '/goods/index?gid=' . $v['id'];
		}
		return array('total' => $total, 'data' => $data);
	}

	//搜索商品列表api
	public function search_v2($uid, $search, $shopId, $status, $goodsId) {
		if ($search) {
			$map = " AND (A.name like '%{$search}%' OR A.code like '%{$search}%' OR A.goodsNo like '%{$search}%') ";
		} else {
			$map = "";
		}
		if ($goodsId) {
			$map = " AND A.id like '{$goodsId}' ";
		}
		if ($status) {
			$map .= " AND status = {$status} ";
		}
		$sql = "SELECT A.id,A.name,A.price,A.goodsStock FROM goods AS A WHERE A.shopId = {$shopId} AND A.status <> '5' {$map} ORDER BY A.id DESC";
		$data = $this->db->fetchAll($sql);
		foreach ($data as $k => $v) {
			$data[$k]['image'] = $this->getGoodsOneImg( $v['id'] );
		}
		return $data;
	}

	//获取商品图片
	public function getGoodsImg($goodsId) {
		$data = $this->db->fetchOne("SELECT image FROM goods_image WHERE goodsId = {$goodsId} ORDER BY `id` LIMIT 1");
		return $data;
	}

	//获取商品图片
	public function getGoodsOneImg($goodsId, $size = '100') {
		$data = $this->db->fetchOne("SELECT image FROM goods_image WHERE goodsId = {$goodsId} ORDER BY `sort` ASC,`id` ASC LIMIT 1");
		return $this->getAvata($data, $size);
	}

	//获取商品图片的数量
	public function getGoodsImgNum($goodsId) {
		return $this->db->fetchOne("SELECT count(*) FROM goods_image WHERE goodsId =" . $goodsId);
	}

	//获取商品图片
	public function getGoodsOneImgExport($goodsId) {
		$data = $this->db->fetchOne("SELECT image FROM goods_image WHERE goodsId = {$goodsId} ORDER BY `sort` ASC,`id` ASC LIMIT 1");
		return $this->getAvataExp($data);
	}

	//获取商品图片
	public function getGoodsImgsByGoodsId($goodsId) {
		$url = $this->db->fetchAll("SELECT image FROM goods_image WHERE goodsId = {$goodsId}");
		$arr = array();
		foreach ($url as $v) {
			$arr[] = array('imgurl' => $this->getAvata(trim($v['image'])), 'hash' => $v['image']);
		}
		return $arr;
	}

	//获取商品图片
	public function getGoodsImgsByGoodsIds($goodsId, $size = '') {
		$url = $this->db->fetchAll("SELECT image FROM goods_image WHERE goodsId =" . intval($goodsId) . " ORDER BY `sort` ASC,`id` ASC");
		$arr = array();
		foreach ($url as $v) {
			$arr[] = array('imgurl' => $this->getAvata(trim($v['image']), $size), 'hash' => $v['image']);
		}
		return $arr;
	}

	//获取商品图片地址和hash值
	public function getGoodsImgHashByGoodsId($goodsId, $offset = '', $size = '1') {
		$map = '';
		if ($offset) {
			$map = ' LIMIT ' . $offset . ',3 ';
		}
		$url = $this->db->fetchAll("SELECT id,image FROM goods_image WHERE goodsId = " . intval($goodsId) . " ORDER BY `sort` ASC,`id` ASC " . $map);
		$arr = array();
		if ($url) {
			foreach ($url as $v) {
				$arr[] = array(
					'id' => $v['id'],
					'imgurl' => $this->getAvata(trim($v['image']), $size),
					'hash' => $v['image']
				);
			}
		}
		return $arr;
	}

	//获取商品信息
	public function getGoodsInfoById($id, $type = 'adm') {
		$sql = "SELECT
			goods.id,
			goods.name,
			goods.code,
			goods.goodsNo,
			goods.price,
			goods.purchPrice,
			goods.showPrice,
			goods.attribute,
			goods.status,
			goods.remark,
			goods.shopId,
			goods.intro,
			goods.category1,
			goods.category2,
			goods.category3,
			goods.content,
			goods.goodsStock,
			goods.isChannel,
			goods.cost,
			goods.groups,
                        goods.taobao_special,
			shop.name AS shop_name,
			shop.intro AS shop_intro,
			shop.headimgurl AS shop_logo,
			shop.ptimes,
			shop.mtimes
		FROM
			goods,
			shop
		WHERE
			goods.id = {$id}
		AND goods.shopId = shop.id";
		$data = $this->db->fetchRow($sql);
		if ($data) {
			$data['intro'] = htmlspecialchars_decode($data['intro']);
			$data['content'] = htmlspecialchars_decode($data['content']);
			$data['shop_logo'] = Service::getInstance('shop')->getAvata($data['shop_logo']);
			if (!isset($data['id']))
				$data['id'] = 0;
			switch ($type) {
				case 'adm':
					$size = '100';
					break;
				case 'api':
					$size = '1';
					break;
				case 'gapi':
					$size = '800';
					break;
				default:
					break;
			}

            $goodsimg = $this->getGoodsImgHashByGoodsId($data['id'], '', $size);
            $data['goodsImg'] = $goodsimg;
            $data['attribute'] = json_decode($data['attribute'], true) ? json_decode($data['attribute'], true) : null;
            $data['goods_image'] = $this->getGoodsImg( $data['id'] );
            $data['goodsOneImg'] = $this->getGoodsOneImg( $data['id'] );
        }
        return $data;
    }

	public function getGoodsById($id) {
		return $this->db->fetchRow('SELECT id,name,code,shopId,price,purchPrice,status,checkResult,goodsStock,category3,attribute FROM `goods` WHERE `id` =' . $id);
	}

	public function getRedisPushGoodsById($id) {

		$sql = " SELECT G.id,G.name,G.purchPrice,G.shopId,G.intro,G.goodsStock,
						S.mtimes,S.ptimes 
				FROM `goods` AS G 
				LEFT JOIN `shop` AS S ON G.shopId = S.id 
				WHERE G.id = '{$id}' ";

		return $this->db->fetchRow( $sql );
	}

	public function getPushGoodsById($id) {
		$data = $this->db->fetchRow("SELECT A.id,A.channel,B.name,B.id AS gid,B.goodsNo,B.goodsStock FROM `push` AS A,`goods` AS B WHERE A.`id` = {$id} AND A.goodsId = B.id AND A.status IN ( 0,1,2,3,4 ) ");
		//应该推送消息
		$pid = isset($data['id']) ? intval($data['id']) : 0;

		$pre = $this->db->fetchRow('select * from `presell` where pushId =' . $pid);
		if ($pre) {
			$start = explode(' ', $pre['startTime']);
			$end = explode(' ', $pre['endTime']);
			$data['preId'] = $pre['id'];
			$data['preTime'] = $pre['preTime'];
			if (count($start) == 1) {
				$data['startDay'] = $pre['preTime'];
				$data['startTime'] = $start['0'];
				$data['endDay'] = $pre['preTime'];
				$data['endTime'] = $end['0'];
			} else {
				$data['startDay'] = $start['0'];
				$data['startTime'] = $start['1'];
				$data['endDay'] = $end['0'];
				$data['endTime'] = $end['1'];
			}
			$data['start'] = $data['startDay'] . ' ' . $data['startTime'];
			$data['end'] = $data['endDay'] . ' ' . $data['endTime'];
			$data['sellType'] = $pre['sellType'];
		}
		$nameArr = $this->getChannelById($data['channel']);
		$data['cname'] = $nameArr['name'];
		return $data;
	}

	public function addPresell($data) {
		$this->db->insert('`presell`', $data);
		return $this->db->lastInsertId();
	}

	public function editPresell($data, $pid) {
		return $this->db->update('`presell`', $data, ' id =' . $pid);
	}

	public function delPresell($pid) {
		return $this->db->delete('`presell`', ' id =' . $pid);
	}

	//通过店铺获得商品
	public function getGoodsByShopId($id,$platform = '2') {
		$map = " WHERE 1 = 1 ";
		
		if ( !$id ) {
			return false;
		}else {
			$map .= " AND `shopId` = {$id} ";
		}

		if ( $platform ) {
			$map .= " AND `platform` = {$platform} ";
		}

		$sql = " SELECT id,name FROM `goods` {$map} ";
		$data = $this->db->fetchAll($sql);
		return $data;
	}

	//通过店铺获取 今日上传商品图片 名称 库存 价格
	public function getGoodsListByShop($id,$time) {
		$map = " WHERE status = 1 ";
		
		if ( !$id ) {
			return false;
		}else {
			$map .= " AND `shopId` = {$id} ";
		}

		if ( $time ) {
			$map .= " AND `createTime` >= {$time} ";
		}
		$sql = " SELECT id,name,price,goodsStock,intro FROM `goods` {$map} ";
		$data = $this->db->fetchAll($sql);
		foreach ($data as $key => $value) {
			$data[$key]['image'] = $this->getGoodsOneImg( $value['id'] );
		}
		return $data;
	}

	//获取等待完善商品
	public function getNoIntroGoods($shopId,$page,$perpage){
		$sql = " SELECT 
				id,
				name,
				price,
				goodsStock,
				FROM_UNIXTIME(`createTime`, '%Y-%m-%d') AS createDate 
				FROM `goods` WHERE `shopId` = {$shopId} 
				AND `intro` = '' 
				AND `status` = 1 
				ORDER BY createTime DESC ".$this->db->buildLimit($page, $perpage);
		$res = $this->db->fetchAll($sql);

		foreach ($res as $key => $value) {
			$res[$key]['image'] = $this->getGoodsOneImg( $value['id'] );
		}

		$goods = array();
		foreach ($res as $key => $value) {
			$goods[$value['createDate']]['goodsInfos'][] = $value;
			$goods[$value['createDate']]['time'] = strtotime( $value['createDate'] );
		}

		return $goods;
	}

	//编辑商品
	public function edit($goods, $id) {
		return $this->db->update('goods', $goods, ' id=' . $id);
	}

	//根据供应商ID关闭，更新商品状态
	public function updateByShopStatus($data, $where) {
		return $this->db->update('goods', $data, $where);
	}

	//添加商品
	public function add($shop) {
		$this->db->insert('goods', $shop);
		return $this->db->lastInsertId();
	}

	public function getMeshopId($uid) {
		$shopId = $this->db->fetchOne('SELECT A.id FROM shop as A left join shopkeeper as B on A.id=B.sid WHERE B.uid=' . $uid . ' ORDER BY B.id DESC LIMIT 1');
		return $shopId;
	}

	// 新增商品历史价格（新增商品/编辑商品进货价时）
	public function addHistoryPrice($purchPrice, $gid) {
		return $this->db->insert('goods_price_history', array(
					'purchPrice' => $purchPrice,
					'goodsId' => $gid,
					'updateTime' => time()));
	}

	//删除商品
	public function del($ids) {
		return $this->db->update('goods', array('status' => 5), ' id in (' . $ids . ')');
	}

	//删除商品
	public function delGoods($id) {
		return $this->db->delete('goods', ' id =' . $id);
	}

	//改变商品状态
	public function change($id, $data) {
		return $this->db->update('goods', $data, ' id =' . $id);
	}

	//获取商品图片
	public function getAvata($hash, $size = '') {
		if (!$size)
			$size = '100';
		$hash = trim($hash);
		$dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
		$url = Yaf_Application::app()->getConfig()->get('image')->get('url');
		$avatar_url = $url . "/default-goods-image.png";
		if (empty($hash))
			return $avatar_url;

		if ($size == '1') {
			$type = "_image.jpg";
		} else {
			$type = "_thumb_{$size}x{$size}.jpg";
		}

		$fileName = Util::getDir($dir, $hash) . $hash;
		$file = $fileName . $type;

		$url = Yaf_Application::app()->getConfig()->get('image')->get('url');
		$path = Util::getPath($hash);
		if (file_exists($file)) {
			$avatar_url = $url . $path . $hash . $type;
		} elseif (file_exists($fileName . '_image.jpg')) {
			$avatar_url = $url . $path . $hash . '_image.jpg';
		}
		return $avatar_url;
	}

	//获得商品的本地图片路径
	public function getImageDirByGoodsId($gid, $size = 1) {
		$images = $this->db->fetchAll(" SELECT image FROM goods_image WHERE goodsId = {$gid} ORDER BY sort ASC ");
		$dirs = array();
		foreach ($images as $k => $v) {
			$dirs[] = $this->getAvataExp($v['image'], $size);
		}
		return $dirs;
	}

	public function getPushImgByGidCid($gid, $cid) {
		return $this->db->fetchAll(" SELECT imgUrl FROM goods_image_push WHERE goodsId = {$gid} AND channel = {$cid} ");
	}

	//获取商品图片，返回的是图片的本地路径
	public function getAvataExp($hash, $size = '1') {
		$hash = trim($hash);
		$dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
		$url = Yaf_Application::app()->getConfig()->get('image')->get('url');

		$disk = '';
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
			$disk = substr(__File__, 0, 2);

		$avatar_dir = $disk . $dir . "/default-goods-image.png";

		if (empty($hash))
			return $avatar_dir;

		if ($size == '1') {
			$type = '_image';
		} else {
			$type = '_thumb_' . $size . 'x' . $size;
		}

		$fileName = Util::getDir($dir, $hash) . $hash;
		$file = $disk . $fileName . $type . '.jpg';

		if (file_exists($file)) {
			$avatar_dir = $file;
		} elseif (file_exists($fileName . '_image.jpg')) {
			$avatar_dir = $fileName . '_image.jpg';
		}
		return $avatar_dir;
	}

	//添加商品图片
	public function addgoods_images($sql) {
		return $this->db->query($sql);
	}

	//删除单个商品图片
	public function delGoodsImgById($imgId) {
		return $this->db->delete('goods_image', 'id = ' . $imgId);
	}

	//更新商品图片
	public function editgoods_images($sql, $goodsid) {
		$this->db->delete('goods_image', ' goodsId = ' . $goodsid);
		return $this->db->query($sql);
	}

	//删除商品图片
	public function delgoods_images($goodsId) {
		return $this->db->delete('goods_image', ' goodsId = ' . $goodsId);
	}

	//获得库存商品
	public function getStock() {
		$user = Yaf_Registry::get('user');
		$role = Yaf_Registry::get('role');
		if ($role == 1) {
			$sql = "SELECT A.*,C.name AS shopname FROM goods AS A LEFT JOIN shop AS C ON C.id=A.shopId WHERE A.status <> 1 AND C.onwer = {$user['id']} ORDER BY A.id DESC";
		} elseif ($role == 2) {
			$sql = "SELECT A.*,C.name AS shopname FROM goods AS A LEFT JOIN shop AS C ON C.id=A.shopId WHERE A.status <> 1 ORDER BY A.id DESC";
		}
		$data = $this->db->fetchAll($sql);
		foreach ($data as $k => $v) {
			$url = $this->getGoodsImg($v['id']);
			$data[$k]['img'] = $this->getAvata($url);
		}
		return $data;
	}

	//上架商品
	public function groundGoods($id) {
		return $this->db->update('goods', array('status' => 1), ' id in (' . $id . ')');
	}

	//下架商品
	public function downGoods($arr, $id) {
		if ($arr['remark']) {
			return $this->db->update('goods', array('status' => $arr['status'], 'remark' => $arr['remark']), ' id in (' . $id . ')');
		} else {
			return $this->db->update('goods', array('status' => $arr['status']), ' id in (' . $id . ')');
		}
	}

	//售出商品
	public function sellGoods($goodsId, $number) {
		if ($number <= 0) {
			return false;
		}
		$goods = $this->db->fetchRow("SELECT status,goodsStock FROM goods WHERE id = " . $goodsId);
		$stock = $goods['goodsStock'] - $number;
		if ($stock < 1 && $goods['status'] == 1) {
			$this->db->update("goods", array("goodsStock" => $stock, "status" => 2, "platform" => 1), ' id=' . $goodsId);
			// 售罄状态
			$this->db->update("push", array( "status" => 6 ), "goodsId=" . $goodsId);
			//通知推送渠道商品下架
			Service::getInstance('apigoods')->downGoodsByGid($goodsId);
			return array("3", $goodsId, $stock);
		} else {
			$this->db->update("goods", array("goodsStock" => $stock), ' id=' . $goodsId);
			return $data = array('4', $goodsId, $stock);
		}
	}

	//增加库存
	public function addStock($goodsId, $number) {
		if ($number <= 0) {
			return false;
		}
		$goods = $this->db->fetchRow("SELECT status,goodsStock FROM goods WHERE id = " . $goodsId);
		$stock = $goods['goodsStock'] + $number;
		if ($goods['status'] == 2) {
			$this->db->update("goods", array("goodsStock" => $stock, "status" => 1, "platform" => 2), ' id=' . $goodsId);
			//修改推送商品由于商品售罄改变的状态,status=6 >> 4
			$this->db->update('push',array('status'=>4)," goodsId = {$goodsId} AND status = '6' ");
			return $data = array("1", $goodsId, $stock);
		} else {
			$this->db->update("goods", array("goodsStock" => $stock), ' id=' . $goodsId);
			return $data = array('2', $goodsId, $stock);
		}
	}
       
	//编辑价格
	public function editPrice($goodsId, $price) {
		if ($price < 0) {
			return false;
		}
		$res = $this->db->update("goods", array("purchPrice" => $price), ' id=' . $goodsId);
		if ($res) {
			return array('5', $goodsId, $price);
		} else {
			return false;
		}
	}

	//获得商品编号
	public function getGoodsCode($shopId) {
		$shop = Service::getInstance('shop')->getShopinfo($shopId);
		if (!$shop) {
			return false;
		}
		$gcode = $this->getLastCode($shop['scode']);
		if ($gcode) {
			$code = $shop['scode'] . ( intval(substr($gcode, 5)) + 1 );
		} else {
			$code = $shop['scode'] . '1';
		}
		return $code;
	}

	//最后一个code
	public function getLastCode($code) {
		$data = $this->db->fetchOne("SELECT code FROM goods WHERE code like '" . $code . "%' ORDER BY id DESC LIMIT 1");
		return $data;
	}

	//图片
	public function getImg() {
		$data = $this->db->fetchAll('SELECT id,imgurl FROM goods_image');
		return $data;
	}

	public function getStatus($id) {
		$data = $this->db->fetchOne('SELECT status FROM goods WHERE id =' . $id);
		return $data;
	}

	//设置图片
	public function setImgHash($id, $hash) {
		return $this->db->update('goods_image', array('image' => $hash, 'imgurl' => ''), ' id=' . $id);
	}

	//类目
	public function getGoodsCategory($pid = 0) {
		return $this->db->fetchAll('SELECT * FROM category WHERE pid = ' . $pid);
	}

	public function getCategoryNameById($id) {
		return $this->db->fetchOne(' SELECT name FROM category WHERE id = ' . $id);
	}

	//参数
	public function getPar($cid) {
		$data = $this->db->fetchAll("SELECT * FROM `attribute` WHERE `cat_id` = {$cid} AND `status` = 1 ");

		$attrKey = array();
		foreach ($data as $k => $v) {
			$attrKey[$v['attr_id']] = $v;
		}
		$attrValue = $this->_getAttrValueByCat($cid);

		$result = array();
		foreach ($attrValue as $k => $v) {
			$keyId = $v['attr_pid'];
			if (!$attrKey[$keyId])
				continue;
			if (!isset($result[$keyId]))
				$result[$keyId] = $attrKey[$keyId];
			$result[$keyId]['child'][] = $v;
		}

		return $result;
		/* foreach ($data as $k=>$v) {
		  $data[$k]['child'] = $this->getParChild($v['attr_id']);
		  } */
	}

	private function _getAttrValueByCat($cat_id) {
		return $this->db->fetchAll(" SELECT * FROM attr_value WHERE cat_id = '{$cat_id}' ");
	}

	//参数
	public function getParChild($pid) {
		return $this->db->fetchAll('SELECT * FROM `parameter` WHERE `pid` = ' . $pid);
	}

	//类目
	public function getCat($pid) {
		return $this->db->fetchAll('SELECT * FROM category WHERE pid =' . $pid);
	}

	//类目
	public function getAttr() {
		$arr = $this->getCat(0);
		foreach ($arr as $k => $v) {
			$arr[$k]['child'] = $this->getCat($v['id']);
			foreach ($arr[$k]['child'] as $key => $val) {
				$arr[$k]['child'][$key]['child'] = $this->getCat($val['id']);
			}
		}
		return $arr;
	}

	//参数
	public function getParam() {
		$arr = $this->getParChild(0);
		foreach ($arr as $k => $v) {
			$arr[$k]['child'] = $this->getParChild($v['id']);
		}
		return $arr;
	}

	//通过pid得参数
	public function getParamByPid($pid) {
		return $this->db->fetchAll('SELECT * FROM `parameter` WHERE `pid` =' . $pid);
	}
        
        //获得参数属性值
        public function getAttrValueByAttrId($attr_id){
            return $this->db->fetchAll("SELECT * FROM `attr_value` WHERE `attr_pid` = {$attr_id}");
        }

	//分组
	public function getGroupsByShopId($id) {
		$arr = $this->db->fetchAll('SELECT `groups` FROM `goods` WHERE `shopId` =' . $id);
		$list = array();
		if (count($arr)) {
			foreach ($arr as $k => $v) {
				if ($v['groups']) {
					$count = $this->db->fetchOne("SELECT count(*) FROM `goods` WHERE `groups` = '" . $v['groups'] . "'");
					$list[] = array('name'=>$v['groups'],'count'=>$count);
				}
			}
		}
		return $list;
	}

	//推送商品
	public function addGoods($goods) {
		$this->db->insert('push', $goods);
		return $this->db->lastInsertId();
	}

	//编辑推送
	public function editPush($id, $data) {
		return $this->db->update('push', $data, ' goodsId = ' . $id);
	}

	//判断是否推送
	public function getIsPushGoods($gid, $did) {
		return $this->db->fetchOne("SELECT `id` FROM `push` WHERE `goodsId` = {$gid} AND `channel` = {$did}");
	}

	//渠道名称
	public function getChannelByGoodsId($id) {
		return $this->db->fetchAll('SELECT B.name FROM `push` AS A LEFT JOIN `channel` AS B ON A.channel = B.id WHERE A.`goodsId` =' . $id);
	}

	//渠道名称
	public function getChannelById($id) {
		return $this->db->fetchRow('SELECT name,payway,paybank,devId FROM `channel` WHERE `id` =' . $id);
	}

	public function delPushGoods($id) {
		return $this->db->delete('push', ' id =' . $id);
	}

	//删除推送商品的图片表
	public function delPushGoodsImg($gid, $cid) {
		return $this->db->delete('goods_image_push', 'goodsId = ' . $gid . ' AND channel = ' . $cid);
	}

	//分类级别
	public function getCatLevel($id) {
		return $this->db->fetchOne('SELECT `level` FROM `category` WHERE `id` =' . $id);
	}

	public function getPrice() {
		$goods = $this->db->fetchAll('select id,purchPrice,shopId from `goods`');
		foreach ($goods as $k => $v) {
			$shop = Service::getInstance('shop')->getShopinfo(intval($v['shopId']));
			$price = round($v['purchPrice'] * $shop['mtimes']);
			$this->db->update('goods', array('price' => $price), ' id = ' . $v['id']);
		}
		return true;
	}

	//获取推送商品信息，分享购买使用
	public function getBuyPushGoods($cid, $gid, $size = '800') {
		$sql = 'SELECT p.id AS pid,g.id,g.name,g.code,g.intro,g.purchPrice,g.attribute,g.status,g.showPrice,g.shopId,g.price  
			   FROM `push` AS p 
			   LEFT JOIN `goods` AS g ON p.goodsId = g.id 
			   LEFT JOIN `channel` AS c ON p.channel = c.id 
			   WHERE p.goodsId = ' . intval($gid) . ' AND p.channel = ' . intval($cid);
		$data = $this->db->fetchRow($sql);
		if ($data) {
			$data['intro'] = htmlspecialchars_decode($data['intro']);
			$shop = Service::getInstance('shop')->getShopinfo(intval($data['shopId']));
			$data['goodsPrice'] = round($shop['ptimes'] * $data['purchPrice']);
			unset($data['purchPrice']);
			//unset( $data['ctimes'] );
			$goodsimg = $this->getGoodsImgHashByGoodsId($data['id'], '', $size);
			$data['goodsImg'] = $goodsimg;
			$data['goodsOneImg'] = $this->getGoodsOneImg( $data['id'] );
			$data['attribute'] = json_decode($data['attribute'], true) ? json_decode($data['attribute'], true) : null;
		}
		return $data;
	}

	//添加到审核表
	public function addGoodsCheck($goodsId) {
		return $this->db->insert('goods_check', array('goodsId' => $goodsId, 'createTime' => time(), 'status' => 0));
	}

	//添加到审核表
	public function addGoodsCheckTwo($goodsId) {
		return $this->db->insert('goods_check', array('goodsId' => $goodsId, 'createTime' => time(), 'oneTime' => time(), 'status' => 1));
	}

	//获取审核表商品
	public function getCheckInfoById($id) {
		return $this->db->fetchRow('select * from goods_check where id =' . $id);
	}

	//获取审核表商品
	public function getCheckInfoByGoodsId($id) {
		return $this->db->fetchRow('select * from goods_check where goodsId =' . $id);
	}

	//审核结果
	public function saveCheck($data, $id) {
		return $this->db->update('goods_check', $data, ' id = ' . $id);
	}

	//删除
	public function delCheck($id) {
		return $this->db->delete('goods_check', ' id = ' . $id);
	}

	//获取审核表商品
	public function getCheckGoodsByGoodsId($id) {
		return $this->db->fetchAll('select * from goods_check where goodsId =' . $id);
	}

	//获得审核商品列表
	public function goodsCheckList($page, $perpage, $keyword, $status = 0, $uploader = '', $searchType, $checkStatus = '') {
		$map = ' WHERE 1 = 1 ';
		if ($keyword != '') {
			if ($searchType == 1) {
				$map .= " AND ( B.goodsNo LIKE '%{$keyword}%' OR B.code LIKE '%{$keyword}%' OR B.name LIKE '%{$keyword}%' ) ";
			} elseif ($searchType == 2) {
				$map .= " AND ( B.code LIKE '{$keyword}' ) ";
			} elseif ($searchType == 3) {
				$map .= " AND ( B.goodsNo LIKE '{$keyword}' ) ";
			} elseif ($searchType == 4) {
				$id = Service::getInstance('admin')->getDevIdByName($uploader);
				if ($id)
					$map .= " AND ( B.uploader = 'd-{$id}' ) ";
			}
		}
		if ($status !== '') {
			$map .= " AND A.status = {$status}";
		}
		if ($uploader != '') {
			$id = Service::getInstance('admin')->getDevIdByName($uploader);
			if ($id)
				$map .= " AND ( B.uploader = 'd-{$id}' ) ";
		}
		$map1 = $map;
		if ($checkStatus == 'lose') {
			$map .= " AND A.reason <> '' AND A.twoTime <> 0 ";
		} elseif ($checkStatus == 'wait') {
			$map .= " AND A.reason = '' ";
		}

		$sql = "SELECT A.*,B.id AS gid,B.name,B.code,B.goodsNo,B.price,B.category1,B.category2,B.category3,B.purchPrice,B.attribute,B.shopId,B.fromWhere,B.uploader,B.intro FROM goods_check AS A LEFT JOIN goods AS B ON A.goodsId = B.id {$map} ORDER BY A.id DESC" . $this->db->buildLimit($page, $perpage);

		$goods = $this->db->fetchAll($sql);

		$cat3 = array();
		if (!empty($goods)) {
			foreach ($goods as $k => $v) {
				$shop = Service::getInstance('shop')->getShopinfo($v['shopId']);
				$goods[$k]['shopName'] = $shop['name'];
				$goods[$k]['mtimes'] = $shop['mtimes'];
				$goods[$k]['ptimes'] = $shop['ptimes'];
				$goods[$k]['thumb'] = $this->getGoodsOneImg($v['goodsId']);
				$goods[$k]['imgNum'] = $this->getGoodsImgNum($v['goodsId']);
				$goods[$k]['purchPrice'] = substr($v['purchPrice'], 0, strpos($v['purchPrice'], '.'));
				$goods[$k]['intro'] = htmlspecialchars_decode($v['intro']);

				if ($v['fromWhere'] == 1) {
					if ($v['uploader']) {
						$name = Service::getInstance('shop')->getShopNameById(intval($shop['id']));
					} else {
						$name = "暂无记录";
					}
				} else {
					if ($v['uploader']) {
						$name = Service::getInstance('admin')->getDevNameById(intval($v['uploader']));
					} else {
						$name = "暂无记录";
					}
				}
				$goods[$k]['uploaderName'] = $name;

				$goods[$k]['oneTime'] = date('Y-m-d H:i:s', $v['oneTime']);

				if (!empty($v['attribute'])) {
                                        $goods[$k]['oldAttribute'] = $v['attribute'];
					$goods[$k]['attribute'] = $this->convetAttr($v['attribute']);
				}

				$cat3[$v['category3']] = $v['category3'];
				if ($v['category1'] && $v['category2']) {
					$goods[$k]['cat2'] = $this->getGoodsCategory($v['category1']);
					$goods[$k]['cat3'] = $this->getGoodsCategory($v['category2']);
				}
			}

			$cat3 = implode(',', $cat3);

			$data['attrs'] = $this->getCurrentAllAttrs($cat3);

			$data['list'] = $goods;
		}

		$sql = "SELECT count(*) FROM goods_check AS A LEFT JOIN goods AS B ON A.goodsId = B.id {$map}";
		$data['total'] = $this->db->fetchOne($sql);
		$data['loseTotal'] = $this->db->fetchOne("SELECT count(*) FROM goods_check AS A LEFT JOIN goods AS B ON A.goodsId = B.id {$map1} AND A.reason <> '' AND A.twoTime <> 0 ");
		$data['waitTotal'] = $this->db->fetchOne("SELECT count(*) FROM goods_check AS A LEFT JOIN goods AS B ON A.goodsId = B.id {$map1} AND A.reason = '' ");
		return $data;
	}

	public function convetAttr($attribute) {
		$attr = array();
		foreach (json_decode($attribute, true) as $v) {
			if ($v['key']['id'] != 0) {
				$attr[$v['key']['id']] = $v['value']['id'];
			} else {
				$attr[$v['key']['name']] = $v['value']['name'];
			}
		}
		return $attr;
	}

	public function getGoodsAttrById($gid) {
		$attribute = $this->db->fetchOne('SELECT attribute FROM goods WHERE id =' . $gid);
		return $this->convetAttr($attribute);
	}

	public function getCurrentAllAttrs($cat3) {
		$data = $this->db->fetchAll(' SELECT * FROM parameter WHERE cid IN ( ' . $cat3 . ' ) ');
		return $this->getTree($data);
	}

	public function getTree($arr, $pid = 0, $level = 0) {
		$result = array();
		foreach ($arr as $k => $v) {
			if ($v['pid'] == $pid) {
				$v['level'] = $level;
				$v['child'] = $this->getTree($arr, $v['id'], ++$level);
				if ($v['pid'] == 0) {
					$result[$v['cid']][$v['id']] = $v;
				} else {
					$result[$v['id']] = $v;
				}
			}
		}
		return $result;
	}

	//删除已售出的推送商品
	public function delSellGoods() {
		$sql = "SELECT A.id FROM push AS A LEFT JOIN goods AS B ON A.goodsId = B.id WHERE B.status = '1'";
		$goods = $this->db->fetchAll($sql);
		$arr1 = array();
		foreach ($goods as $k => $v) {
			$arr1[] = $v['id'];
		}

		$sql1 = "SELECT id FROM push";
		$goods1 = $this->db->fetchAll($sql);
		$arr2 = array();
		foreach ($goods1 as $k => $v) {
			$arr2[] = $v['id'];
		}

		foreach ($arr2 as $v) {
			if (!in_array($v, $arr1)) {
				$this->delPushGoods($v);
			}
		}
	}

	public function goodsToCheck() {
		$sql = "select id,status,checkResult from goods where platform = '2' and checkResult = 0 order by id desc limit 500 ";
		$data = $this->db->fetchAll($sql);
		foreach ($data as $k => $v) {
			if ($v['checkResult'] == 0) {
				$this->change($v['id'], array('platform' => 1));
				$this->addGoodsCheckTwo($v['id']);
			}
		}
		return true;
	}

	public function delCheckAndGoods($id) {
		try {
			$data = $this->getCheckInfoById($id);
			$this->db->delete('goods', ' id=' . $data['goodsId']);
			$this->db->delete('goods_check', ' id=' . $id);
		} catch (Exception $e) {
			return $e->getMessage();
		}
		return true;
	}

	public function getExportAllGoods($keyword, $status) {
		$map = '';
		if ($keyword != '') {
			$map .= " AND ( B.name like '%{$keyword}%' OR B.code like '%{$keyword}%' OR B.goodsNo like '%{$keyword}%') ";
		}
		if ($status != '') {
			$map .= " AND status = '" . $status . "'";
		}

		$sql = "SELECT B.id AS gId,B.name,B.code,B.goodsNo,B.purchPrice,B.goodsStock,B.attribute,B.intro,B.status,B.shopId,B.uploader,B.fromWhere FROM goods AS B WHERE B.status <> 5 " . $map . " order by B.id desc ";
		$goods = $this->db->fetchAll($sql);
		foreach ($goods as $k => $v) {
			if ($v['status'] == 1) {
				$goods[$k]['status'] = '在售';
			} elseif ($v['status'] == 2) {
				$goods[$k]['status'] = '售罄';
			} elseif ($v['status'] == 3) {
				$goods[$k]['status'] = '停售';
			} elseif ($v['status'] == 4) {
				$goods[$k]['status'] = '其他下架原因';
			} elseif ($v['status'] == 5) {
				$goods[$k]['status'] = '删除';
			} elseif ($v['status'] == 6) {
				$goods[$k]['status'] = '锁定';
			} else {
				$goods[$k]['status'] = '异常';
			}
			$goods[$k]['intro'] = htmlspecialchars_decode($v['intro']);
			$preg = "/<\/?[^>]+>/i";
			$goods[$k]['intro'] = preg_replace($preg, '', $goods[$k]['intro']);
			$attr = json_decode($v['attribute'], true);
			$arr = array();
			if (is_array($attr)) {
				foreach ($attr as $key => $val) {
					$arr[] = $val['key']['name'] . '&' . $val['value']['name'];
				}
				$arr = implode(',', $arr);
				$goods[$k]['attribute'] = $arr;
			}
		}
		$list = array();
		foreach ($goods as $k => $v) {
			foreach ($v as $key => $val) {
				$list[$k][] = $val;
			}
		}
		return $list;
	}

	//改变former_platform
	public function setFormerPlatformByGoodsId($goodsIdIn) {
		return $this->db->query("UPDATE goods SET former_platform = platform , status = '3' , platform = '1' WHERE id IN ( $goodsIdIn )");
	}

	//还原former_platform
	public function setPlatformByGoodsId($goodsIdIn) {
		return $this->db->query("UPDATE goods SET platform = former_platform , status = '1' , former_platform = '0' WHERE id IN ( $goodsIdIn )");
	}

	//获取范围内的店铺商品id
	public function getGoodsIdByShopId($shopId, $map, $status) {
		$sql = "SELECT id FROM goods WHERE shopId = $shopId AND status = '{$status}' AND $map ";
		return $this->db->fetchAll($sql);
	}

	public function checkDel($goodsId) {
		$sql = "SELECT count(*) FROM `goods` AS G LEFT JOIN `shop_push` AS SP ON G.id = SP.goods_id  WHERE G.id = {$goodsId} AND (G.platform = '1' AND G.status <> 5  AND G.checkResult = '0' AND G.fromWhere = '1' AND SP.follow IS NULL AND SP.command_id IS NULL) OR (G.id = {$goodsId} AND G.isChannel = 1) ";
		return $this->db->fetchOne($sql);
	}

	/**
	 * 获取商品所属商家
	 * @param  [type] $goodsId [description]
	 * @return [type]          [description]
	 */
	public function getGoodsShop($goodsId)
	{
		$sql="SELECT shopId
			FROM goods 
			WHERE id = {$goodsId}";
		return $this->db->fetchOne($sql);
	}

	public function getGoodsIdBySku( $sku ){
		return $this->db->fetchOne( " SELECT id FROM goods WHERE code = '{$sku}' " );
	}

	/**
	 * 获取推送商品里特定状态商品的集合id
	 * @param  [type] $checkStatus [description]
	 * @param  [type] $channel     [description]
	 * @return [type]              [description]
	 */
	public function getStatusId($checkStatus,$channel){
		$res = Yaf_Registry::get('developer');
		$account = $res['account'];
		return substr($account,-5,5) . '_' . $checkStatus . '_' . $channel;
	}
        
        //获得商品库存
        public function getStockById($id){
            $sql = "SELECT goodsStock FROM goods WHERE id = {$id} ";
            return $this->db->fetchOne($sql);
        }
        
        //添加商品评论日志
        public function addGoodsCommentLog($data){
            $this->db->insert('goods_comment_log', $data);
            return $this->db->lastInsertId();
        }
}