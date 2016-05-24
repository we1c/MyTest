<?php 
		
class Service_Sellplan extends Service{

    private $error;
    private $shop;
    private $cateNames;

    public function getError()
    {
        return $this->error;
    }

    public function init(  )
    {
        $this->shop = array();
        $this->cateNames = array();
    }

	/**
	 * 获取费用信息(参数为pid)
	 * @param  [type] $pid [description]
	 * @return [type]      [description]
	 */
	public function getCostInfo($pid)
	{
		$sql="SELECT id,costName,price 
			FROM cost 
			WHERE pid={$pid}";
		return $this->db->fetchAll($sql);
	}

	/**
	 * 获取多个推送商品所对应的goodsId
	 * @param  [type] $pushId_str [字符串,形式为 '1101,1102,1103,...'],
	 * @return [type]          [description]
	 */
	public function getMulPushGoodsId($pushId_str)
	{
		$sql="SELECT goodsId 
			FROM push 
			WHERE id 
			IN ({$pushId_str})";
		return $this->db->fetchAll($sql);
	}

	/**
	 * 获得商品预售id
	 * @param  [type] $pushId [description]
	 * @return [type]         [description]
	 */
	public function getPresellId($pushId)
	{
		$sql="SELECT id 
			FROM presell 
			WHERE pushId={$pushId}";
		return $this->db->fetchOne($sql);
	}

	/**
	 * 通过goodsId查出pTimes和purchPrice
	 * @param  [type] $goodsId [description]
	 * @return [type]          [description]
	 */
	public function getPtimesPurch($goodsId)
	{
	$sql="SELECT goods.purchPrice,shop.ptimes
		FROM goods
		JOIN shop 
		ON goods.shopId=shop.id
		WHERE goods.id={$goodsId}";
	return $this->db->fetchRow($sql);
	}

	/**
	 * 由pushId获取presell表中对应的时间
	 * @param  [type] $pushId [description]
	 * @return [type]         [description]
	 */
	public function getStartAndEnd($pushId)
	{
	$sql="SELECT startTime,endTime 
		FROM presell 
		WHERE pushId = {$pushId}";
	return $this->db->fetchRow($sql);	
	}

	/**
	 * 获取指定的销售计划内容
	 * @param  [type] $sellplanId [description]
	 * @return [type]             [description]
	 */
	public function getOldInfo($sellplanId)
	{
	$sql="SELECT express,certificate,package,addPrice,tradeStyle,tradeCount,tradePrice,startUpTime
		FROM sell_plan
		WHERE id = {$sellplanId}";
	return $this->db->fetchRow($sql);
	}

	public function getSellplanByPushChanel($pushId,$channelId){
		$sql="SELECT express,certificate,package,addPrice,tradeStyle,tradeCount,tradePrice,startUpTime
			FROM sell_plan
			WHERE pushId = {$pushId} AND channelId = {$channelId} ";
		return $this->db->fetchRow($sql);
	}
	/**
	 * 获取销售计划中的推送商品的预售起止时间
	 * @param  [type] $pushId [description]
	 * @return [type]         [description]
	 */
	public function getOldPreStartEnd($pushId)
	{
	$sql="SELECT startTime,endTime 
		FROM presell 
		WHERE pushId={$pushId}";
	return $this->db->fetchRow($sql);
	}

	/**
	 * 更新指定的销售计划内容
	 * @param  [type] $sellplanId [description]
	 * @param  [type] $content    [数组,键名对应字段名. array('id'=1,'content1'='word','content2'=>'word2')]
	 * @return [type]             [description]
	 */
	public function update($sellplanId,$content)
	{
		return $this->db->update('sell_plan',$content,'id='.$sellplanId);
	}
	
	/**
	 * 添加销售计划
	 * @param [type] $sellPlan [description]
	 */
	public function addSellPlan($sellPlan)
	{
		return $this->db->insert('sell_plan',$sellPlan);
	}

	/**
	 * 通过goodsId查询goods/goods_image表中指定的信息
	 * @param  [type] $goodsId [description]
	 * @return [type]          [description]
	 */
	public function getGoodsImgGid($goodsId)
	{
		$sql="SELECT * 
					FROM goods 
					JOIN goods_image on goods.id=goods_image.goodsId 
					WHERE goods.id={$goodsId}";
		return $this->db->fetchRow($sql);
	}

	/**
	 * 通过sellplanId查询goods/goods_image/sell_plan三表指定的信息
	 * @param  [type] $sellplanId [description]
	 * @return [type]             [description]
	 */
	public function getGoodsImgSp($sellplanId)
	{
		$sql="SELECT 
			G.shopId,
			G.purchPrice,
			G.platfPrice,
			G.name,
			G.intro,
			I.image,
			S.express,
			S.package,
			S.certificate,
			S.addPrice,
			S.channelId,
			S.tradeStyle,
			S.tradeCount,
			S.tradePrice,
			S.batch,
			S.description,
			S.startUpTime,
			G.code,
			G.goodsStock
			FROM goods AS G 
			JOIN goods_image AS I 
			ON G.id=I.goodsId 
			JOIN sell_plan AS S
			ON G.id=S.goodsId
			WHERE S.id={$sellplanId}";
		return $this->db->fetchRow($sql);
	}

	/**
	 * 通过shopId查询平台倍数pTimes和包邮限额quota
	 * @param  [type] $shopId [description]
	 * @return [type]         [description]
	 */
	public function getPtimesQuota($shopId)
	{
		$sql="SELECT ptimes,quota 
			FROM shop 
			WHERE id={$shopId}";
		return $this->db->fetchRow($sql);     
	}

	/**
	 * 通过shopId查询渠道倍数cTimes
	 * @param  [type] $shopId    [description]
	 * @param  [type] $channelId [description]
	 * @return [type]            [description]
	 */
	public function getCtimes($shopId,$channelId)
	{
		$sql="SELECT ctimes
			FROM channel_shop_ctimes
			WHERE shopId={$shopId}
			AND
			channelId={$channelId}";
		return $this->db->fetchOne($sql);
	}

	/**
	 * 使用当前操作者的account和channelId组合加密成一个唯一的redis集合
	 * @param  [type] $account   [description]
	 * @param  [type] $channelId [description]
	 * @return [type]            [description]
	 */
	public function sellplanMD5($account,$channelId)
	{
		return substr($account,-5,5).'_'.substr(md5($account.'_'.$channelId), 0,10).'_'.$channelId;
	}

	/**
	 * 获取当前渠道商的销售计划数
	 * @param  [type] $channelId [description]
	 * @return [type]            [description]
	 */
	public function sellplanCountByCid($channelId)
	{
		$sql =" SELECT 
				COUNT(distinct A.goodsId) 
				FROM sell_plan AS S 
				LEFT JOIN push AS A ON S.pushId=A.Id 
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN shop AS C ON C.id = B.shopId 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				WHERE (S.channelId = {$channelId} AND S.isDel=0 AND (B.status IN (1,2,3,6))) 
				AND A.channel = {$channelId} AND A.status IN ( 0 )";
		return $this->db->fetchOne($sql);
	}

	/**
	 * 获得销售计划列表
	 * @param  [type]  $page       [description]
	 * @param  [type]  $perpage    [description]
	 * @param  [type]  $keyword    [description]
	 * @param  [type]  $channel    [description]
	 * @param  [type]  $status     [description]
	 * @param  integer $time       [description]
	 * @param  [type]  $searchType [description]
	 * @param  string  $minPrice   [description]
	 * @param  string  $maxPrice   [description]
	 * @param  integer $inSellplan [description].WHERE条件中添加了inSellplan的判断,默认是0
	 * @param  $[checkStatus] [<description>]
	 * @return [type]              [description]
	 */
	public function goodsSpList($page, $perpage,$keyword,$channel,$status,$time = 0,$searchType,$minPrice = '' ,$maxPrice = '',$inSellplan=0) 
	{
		$map = '';
		if( $keyword != '' ){
			if ($searchType == 1) {
				$map .= " AND ( B.goodsNo LIKE '%{$keyword}%' OR B.code LIKE '%{$keyword}%' OR B.name LIKE '%{$keyword}%' ) ";
			}elseif($searchType == 2){
				$map .= " AND ( B.code LIKE '{$keyword}' ) ";
			}elseif($searchType == 3){
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

		if($time == 0) {
				$time = " P.startTime DESC ";
		}elseif($time == 1){
				$time = " A.id DESC ";
		}
		if ( $channel != '' ) {
				$map .= " AND A.channel = {$channel} ";
		}
		if( is_array($status) ){
			$status = implode(',',$status);
			$map .= " AND A.status IN ( ".$status." ) ";
		}else{
			$map .=" AND A.status = {$status}";
		}

		$developer = Yaf_Registry::get('developer');
		$role=$developer['role'];
		if (!in_array(1,$role) && (!in_array(5,$role))) {
			$disId = $developer['disId'];
			$map .= " AND A.channel IN ( {$disId} ) ";
		}

		$sql = "SELECT A.id AS id,
				A.createTime,
				A.channel,
				A.status,
				A.fromWhere,
				A.goodsId,
				B.name,
				B.intro,
				B.code,
				B.goodsNo,
				B.goodsStock,
				B.purchPrice,
				B.attribute,
				B.shopId,
				B.status AS g_status,
				B.checkResult,
				C.ptimes,
				C.mtimes,
				P.preTime,
				P.startTime,
				P.endTime,
				P.sellType,
				S.id AS sellplanId,
				S.tradePrice,
				S.tradeCount,
				S.batch 
				FROM sell_plan AS S 
				LEFT JOIN push AS A ON S.pushId=A.Id 
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN shop AS C ON C.id = B.shopId 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				WHERE (S.channelId={$channel} 
				AND S.isDel=0 
				AND (B.status IN (1,2,3,6))) {$map} 
				GROUP BY A.goodsId 
				ORDER BY B.status 
				DESC, A.id ASC ".$this->db->buildLimit($page, $perpage);
				// echo $sql;exit;
			$goods = $this->db->fetchAll($sql);
			/**
			 * 如果$goods为空,就不执行循环组合数据了,避免资源浪费
			 * update:2016-04-28 01:12 By breakdinner
			 */
			if ($goods) {
				foreach ( $goods as $k=>$v ) {
					/**
					 * 获取渠道价格
					 * update:2016-04-27 11:29 By breakdinner
					 * @var [type]
					 */
					$cTimes = Service::getInstance('distributor')->getCtimesByChannelAndShop($v['channel'],$v['shopId']);
					$goods[$k]['channelPrice'] = round($v['purchPrice']*$v['ptimes']*$cTimes);

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
					$goods[$k]['shopName'] = $shop['name'];
					$goods[$k]['mtimes'] = $shop['mtimes'];
					$goods[$k]['ptimes'] = $shop['ptimes'];
					$cname = Service::getInstance('goods')->getChannelById( $v['channel'] );
					$goods[$k]['cname'] = $cname ? $cname['name'] : '';
					$goods[$k]['thumb'] = Service::getInstance('goods')->getGoodsOneImg($v['goodsId']);

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
			}
			$data['list'] = $goods;
			/**
			 * 同时存入redis,要判断goods是否为空,否则取不出$goods[0]['channel']
			 * update:2016-04-27 12:53 By breakdinner
			 */
			// 获取状态商品的集合id
			$statusId = Service::getInstance('goods')->getStatusId('sellplan',$channel);
			if($goods){
				Red::getInstance()->SET($statusId,json_encode($redisGoods));
			}else{
				Red::getInstance()->DEL($statusId);
			}

			$sql = " SELECT 
				COUNT(distinct A.goodsId) 
				FROM sell_plan AS S 
				LEFT JOIN push AS A ON S.pushId=A.Id 
				LEFT JOIN goods AS B ON A.goodsId = B.id 
				LEFT JOIN shop AS C ON C.id = B.shopId 
				LEFT JOIN presell AS P ON A.id = P.pushId 
				WHERE (S.channelId={$channel} 
				AND S.isDel=0 
				AND (B.status IN (1,2,3,6))) {$map} ";
				// echo $sql;exit;
			$data['total'] = $this->db->fetchOne($sql);
			return $data;
	}

	/**
	 * 添加预售
	 * @param [type] $data [description]
	 */
	public function addPresell($content) {
		$this->db->insert('`presell`', $content);
		return $this->db->lastInsertId();
	}

	/**
	 * 修改添加过销售计划的推送商品的预售信息
	 * @param  [type] $pushId  [description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public function updatePresell($pushId,$content)
	{
		return $this->db->update('presell',$content,'pushId = '. $pushId);
	}

	/**
	 * 删除预售
	 * @param  [type] $pushId [description]
	 * @return [type]         [description]
	 */
	public function deletePresell($pushId)
	{
		return $this->db->delete('presell','pushId='.$pushId);
	}

	/**
	 * 删除销售计划
	 * @param  [type] $pushId [description]
	 * @return [type]         [description]
	 */
	public function deleteSellPlan($Id)
	{
		return $this->db->delete('sell_plan','id='.$Id);
	}

	public function deleteSellPlanByGid( $gid ){
		return $this->db->delete( 'sell_plan','goodsId ='.$gid );
	}

	/**
	 * 获取具体费用
	 * @param  [type] $costId [description]
	 * @return [type]         [description]
	 */
	public function getCostByCostId($costId)
	{
		$sql="SELECT price
			FROM cost
			WHERE id={$costId}";
		return $this->db->fetchOne($sql);
	}

	/**
	 * 获取多个费用
	 * @param  [type] $costIds [字符串.格式为  1,2,3,4]
	 * @return [type]          [description]
	 */
	public function getAllCost($costIds)
	{
		$sql = "SELECT price FROM cost WHERE id IN ({$costIds})";
		return $this->db->fetchAll($sql);
	}
	
    public function getAccurateInfo( $channel,$goodsId ){
        return $this->db->fetchRow( " SELECT respon_goods_id,express,certificate,package,addPrice FROM `sell_plan` WHERE channelId = '{$channel}' AND goodsId = '{$goodsId}' AND isDel = 0 " );
    }

    public function editByWhere( $data,$where ){
        return $this->db->update( 'sell_plan' , $data, $where );
    }

    public function deleteByWhere( $where ){
        return $this->db->delete( 'sell_plan' , $where );
    }

    //获取不在销售计划的PushId
    public function getPushNoSellPlan( $pushId ,$channel ){
    	$pushId_str=implode(",", $pushId);
    	$sql = " SELECT A.id FROM push AS A 
				LEFT JOIN sell_plan as S ON A.id = S.pushId 
				WHERE A.channel = {$channel} 
				AND A.id IN ({$pushId_str}) 
				AND (S.tradeCount is null OR S.tradeCount = 0) ";
		$data = $this->db->fetchAll( $sql );
		$res = array();
		foreach ($data as $v) {
			$res[] = $v['id']; 
		}
		return $res;
    }

}

 ?>