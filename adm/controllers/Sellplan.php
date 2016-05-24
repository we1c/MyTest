<?php 

class SellplanController extends BaseController
{
	public function init()
	{
		parent::init();
	}

	/**
	 * 销售计划商品列表
	 * @return [type] [description]
	 */
	public function indexAction()
	{
		Factory::p(' ',1);
		/**合并到推送商品页面中,所以取消了渠道商选择界面,改为根据channel查询数据
		 * update:2016-04-22 15:14 By breakdinner
		 * 
		 */
		$showType = trim($this->getQuery('showType','list'));
		$perpage    = trim($this->getQuery('perpage',100));
		$showpage   = 5;
		$page       = trim($this->getQuery('page', 1));
		$keyword    = trim($this->getQuery('keyword', ''));
		$time       = trim($this->getQuery('time', ''));
		$channelId    = trim($this->getQuery('channel'));
		$searchType = trim($this->getQuery('searchType', '1'));
		$minPrice   = trim($this->getQuery('minPrice', ''));
		$maxPrice   = trim($this->getQuery('maxPrice', ''));
		$inSellplan =1;//0无销售计划,1有销售计划

		// 推送商品的状态判定，当status值为1的时候，送回审核。0时为正常(上架)，2时为锁定，3时为价格异常（推送商品被改变进货价后出现）,4时为仓库中
		$data = Service::getInstance('sellplan')->goodsSpList($page,$perpage,$keyword,$channelId,array(0),$time,$searchType,$minPrice,$maxPrice,$inSellplan);

		/**
		 * 根据channelId和isDel获取计划中的商品
		 * update:2016-04-22 15:21 By breakdinner
		 * @var [type]
		 */
				
		foreach ($data['list'] as $key => $value) {
			// 操作权限判断
			$myRole = $this->_developer['roleId'];
			$manager = Service::getInstance('distributor')->getManagerById( $channelId );
			if( $manager == $this->_devid || $myRole === '1' || $myRole === '5' ){
					$action = true;
			}else{
					$action = false;
			}
			// 分配页面变量
			$this->_view->showType   = $showType;
			$this->_view->action     =$action;
			$this->_view->list       = $data['list'];
			$this->_view->total      = $data['total'];
			$this->_view->perpage    = $perpage;
			$this->_view->page       = $page;
			$this->_view->channel    = $channelId;
			$this->_view->keyword    = $keyword;
			$this->_view->time       = $time;
			$this->_view->searchType = $searchType;
			$this->_view->home = Yaf_Application::app()->getConfig()->get('home')->get('gapi')->url;
			$pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('sellplan','index','keyword'=>$keyword,'channel'=>$channelId,'perpage'=>$perpage,'time'=>$time,'showType'=>$showType,'searchType'=>$searchType));
			$this->_view->pagebar = $pageObj -> showpage( );
		}
	}

	/**
	 * 新建销售计划(页面,要判断是否批量属于批量推送的批次)
	 */
	public function addOldAction()
	{
		/*判断是否批量添加销售计划*/
		if($this->isPost()){
			$_createBatch=$this->getPost("batch");
			// 如果存在batch则表示为批量添加
			if($_createBatch){
				$this->_addMulSellPlan($_createBatch);
			}else{
				$this->_addSellPlan();
			}
		}
		/*判断当前页面是否按照'批量添加'显示.存在puids即为批量添加*/
		if ($this->getQuery('puids')) {
			$pushId=$this->getQuery('puids');
			$pushId_str=implode(",", $pushId);
			//获取所有推送商品的goodsId
			$goodsIds=Service::getInstance('sellplan')->getMulPushGoodsId($pushId_str);
			$goodsId=array();
			foreach ($goodsIds as $key => $value) {
				$goodsId[]=$value['goodsId'];
			}
			$goodsInfo['goodsId']=json_encode($goodsId);
			$batch=$this->_createBatch(trim($this->getQuery('channel')),$pushId);
			$this->_view->batch       =$batch;
			$pushId=json_encode($pushId);
		}else{
			// 获得商品pushId
			$pushId=trim($this->getQuery('id'));
			// 获得商品预售id
			$presellId=Service::getInstance('sellplan')->getPresellId($pushId);
			// 获得商品id
			$goodsId=trim($this->getQuery('goodsId'));
			// 查询出该id对应的goods表和goods_image表中的对应信息
			$goodsInfo=Service::getInstance('sellplan')->getGoodsImgGid($goodsId);
			// 根据shopId查出ptimes,cTimes，与purchPrice相乘得出渠道价.同时查出该店铺的包邮限额quota
			$shop=Service::getInstance('sellplan')->getPtimesQuota($goodsInfo['shopId']);
			$shop['cTimes']=Service::getInstance('sellplan')->getCtimes($goodsInfo['shopId'],trim($this->getQuery('channel')));
			// 批量添加,页面不显示商品详情和图片,但要取出其他简要信息.同时提交编辑内容的时候,是对相应批次商品批量修改;
			$goodsInfo['channelPrice']=round($goodsInfo['purchPrice']*$shop['cTimes']*$shop['ptimes']);
			$goodsInfo['quota']=$shop['quota'];
			// 获取img
			$goodsInfo['imgurl']=Service::getInstance('goods')->getGoodsImgsByGoodsId($goodsId);
			// 如果当前商品渠道价高于该店铺包邮限额(限额若为0则作为普通邮费)，则包邮,为4；否则为5，普通快递。4和5是和cost表中的id相对应的。
			/**
			 * update:2016-04-21 15:45 breakdinner
			 */
			if ($shop['quota'] ==0 || is_null($shop['quota']) || $goodsInfo['channelPrice'] <$shop['quota']) {
				$goodsInfo['freeExpress'] =5;
			}elseif ($goodsInfo['channelPrice'] >=$shop['quota']) {
				$goodsInfo['freeExpress'] =4;
			}

			// 分配到页面
			$this->_view->presellId=$presellId;
		}
		/*******************
		 以下为公共部分 
		 *******************/
		// 获取物流费用信息
		$express=Service::getInstance('sellplan')->getCostInfo(1);
		// 获取证书费用信息
		$certificate=Service::getInstance('sellplan')->getCostInfo(2);
		// 获取包装费用信息
		$package=Service::getInstance('sellplan')->getCostInfo(3);
		// 定义一个expressJson数组
		$expressJson=$this->_arrayToJson($express);
		// 定义一个certificateJson数组
		$certificateJson=$this->_arrayToJson($certificate);
		// 定义一个packageJson数组
		$packageJson=$this->_arrayToJson($package); 

		//分配页面变量 
		$this->_view->express     =$expressJson;
		$this->_view->certificate =$certificateJson;
		$this->_view->package     =$packageJson;
		$this->_view->channel     =trim($this->getQuery('channel'));
		$this->_view->goodsInfo   =$goodsInfo;
		$this->_view->pushId      =$pushId;
	}

	public function addAction(){
		if ( $this->isPost() ) {
			$pushId = $this->getPost('pushId');
			$goodsId = $this->getPost('goodsId');
			$channel = $this->getPost('channel');
			$tradeStyle = $this->getPost('tradeStyle');
			$startPrice = $this->getPost('startPrice');
			$upAccount = $this->getPost('upAccount');
			$presellId = $this->getPost('presellId');
			$addPrice = $this->getPost('addPrice');
			$express = $this->getPost('express');
			$certificate = $this->getPost('certificate');
			$package = $this->getPost('package');
			/*预售时间是可选项*/
			// 整理起止时间.如果没填写,设置为0     
			$startTime = $this->getPost('startTime',0);
			$endTime = $this->getPost('endTime',0);

			/*添加上架时间*/
			switch ($this->getPost('startUpTime')) {
				// 立即
				case 1:
				$startUpTime=time();
				break;
				// 指定时间
				case 2:
				$startUpTime = strtotime(trim($this->getPost('detailStartUpTime')));
				break;
			}

			// 设置交易模式
			if( $tradeStyle == 0 ){
				// 竞买模式.
				$tradePrice = floatval( $startPrice );
				$tradeCount = 1;
				$channelPrice = 0;
			}else{
				//需要判断一口价是否低于渠道价的95折
				// 获取渠道价,通过goodsId查出pTimes和purchPrice
				// 获取ctimes
				$shopId =Service::getInstance('goods')->getGoodsShop( $goodsId );
				$cTimes =Service::getInstance('sellplan')->getCtimes( $shopId, $channel );
				$res    =Service::getInstance('sellplan')->getPtimesPurch( $goodsId );
				// 渠道价
				$channelPrice=round($res['purchPrice']*$cTimes*$res['ptimes']);
				/*if($channelPrice*0.95>floatval($this->getPost('fixedPrice')))
				{
					$this->error('一口价不能低于'.round($channelPrice*0.95));
				}*/
				$tradePrice =floatval($this->getPost('fixedPrice'));
				$tradeCount =$upAccount;
			}

			/*如果填写了预售起止时间,则执行添加预售队列*/
			if(!empty($startTime)&&!empty($endTime)){	
				// 获得商品预售id
				$presellId=Service::getInstance('sellplan')->getPresellId($pushId);
				$res=$this->_addPresellLine($pushId,$presellId,$goodsId,$channel,$startTime,$endTime,$tradeStyle,$keyword ='');
			}
					// 关于返回值$res参数的注释
					/*1 清空成功 2 修改成功 3 修改失败 4 日期时间不能为空 5 设置成功 6 设置失败 7参数错误 8结束时间不得早于起始时间*/
					// 如果修改成功,数据库也作出相应改变;如果
			$tradeStyle  =intval($tradeStyle);
			$addPrice    =floatval($addPrice);
			// 物流/包装/证书信息传回的是json
			$express     =json_decode($express)->id;
			$certificate =json_decode($certificate)->id;
			$package     =json_decode($package)->id;
			/**
			 * 分别获取三项费用的具体值
			 */
			$express=Service::getInstance('sellplan')->getCostByCostId($express);
			$certificate=Service::getInstance('sellplan')->getCostByCostId($certificate);
			$package=Service::getInstance('sellplan')->getCostByCostId($package);

			// 计算单件商品总价
			// $sellPlanInfo['tradePrice']=$express+$certificate+$package+$sellPlanInfo['addPrice']+$sellPlanInfo['tradePrice'];

			$sellPlanInfo = array(
							"pushId"=>$pushId,
							"tradePrice"=>$tradePrice,
							"tradeCount"=>$tradeCount,
							"channelPrice"=>$channelPrice,
							"startUpTime"=>$startUpTime,
							"tradeStyle"=>$tradeStyle,
							"addPrice"=>$addPrice,
							"express"=>$express,
							"certificate"=>$certificate,
							"package"=>$package,
							"goodsId"=>$goodsId,
							"channelId"=>$channel,
							"createTime"=>time(),
							);

			// 添加
			$sellplanId =Service::getInstance('Sellplan')->addSellPlan($sellPlanInfo);
			if($sellplanId){
				// 在push表中标记该商品已列入销售计划
				$inSellplan=Service::getInstance('push')->edit(array('inSellplan'=>1),$pushId);
				// 判断是否存在于批量销售计划中,存在则删除
				$sellplanMD5=Service::getInstance('sellplan')->sellplanMD5($this->_developer['account'],$channel);
				if (in_array($pushId, $this->redis->SMEMBERS($sellplanMD5))) {
					$this->redis->SREM($sellplanMD5,$pushId);
				}
				// 跳转回当前渠道推送商品列表
				$this->respon(1,'添加成功');
			}
		}else{

		}
	}

	//销售计划弹框详情
	public function detailAction(){
		if ( $this->isPost() ) {
			$pushId = $this->getPost('pushId');
			$goodsId = $this->getPost('goodsId');
			$channel = $this->getPost('channel');
			$sellplanId = $this->getPost('sellplan');
			$act = $this->getPost('act');

			
			if ( $act == 'add' ) {
				// 查询出该id对应的goods表和goods_image表中的对应信息
				$goodsInfo=Service::getInstance('sellplan')->getGoodsImgGid($goodsId);
				// 根据shopId查出ptimes,cTimes，与purchPrice相乘得出渠道价.同时查出该店铺的包邮限额quota
				$shop=Service::getInstance('sellplan')->getPtimesQuota($goodsInfo['shopId']);
				$shop['cTimes']=Service::getInstance('sellplan')->getCtimes($goodsInfo['shopId'],trim($channel));
				// 批量添加,页面不显示商品详情和图片,但要取出其他简要信息.同时提交编辑内容的时候,是对相应批次商品批量修改;
				$goodsInfo['channelPrice']=round($goodsInfo['purchPrice']*$shop['cTimes']*$shop['ptimes']);
				$goodsInfo['quota']=$shop['quota'];
				// 如果当前商品渠道价高于该店铺包邮限额(限额若为0则作为普通邮费)，则包邮,为4；否则为5，普通快递。4和5是和cost表中的id相对应的。
				if ($shop['quota'] ==0 || is_null($shop['quota']) || $goodsInfo['channelPrice'] <$shop['quota']) {
					$goodsInfo['freeExpress'] =5;
				}elseif ($goodsInfo['channelPrice'] >=$shop['quota']) {
					$goodsInfo['freeExpress'] =4;
				}
			}elseif( $act = 'edit' ){
				// 查询出该sellplanId对应的goods表和goods_image表中的对应信息
				$goodsInfo=Service::getInstance('sellplan')->getGoodsImgSp($sellplanId);

				// 根据shopId查出ptimes,cTimes，与purchPrice相乘得出渠道价.同时查出该店铺的包邮限额quota
				$shop=Service::getInstance('sellplan')->getPtimesQuota($goodsInfo['shopId']);
				$shop['cTimes']=Service::getInstance('sellplan')->getCtimes($goodsInfo['shopId'],$goodsInfo['channelId']);
				$goodsInfo['channelPrice']=round($goodsInfo['purchPrice']*$shop['cTimes']*$shop['ptimes']);
				$goodsInfo['quota']=$shop['quota'];

				// 如果当前商品渠道价高于该店铺包邮限额(限额若为0则作为普通邮费)，则包邮,为4；否则为5，普通快递。4和5是和cost表中的id相对应的。
				/**
				 * update:2016-04-21 15:45 breakdinner
				 */
				if ($shop['quota'] ==0 || is_null($shop['quota']) || $goodsInfo['channelPrice'] <$shop['quota']) {
					$goodsInfo['freeExpress'] =5;
				}elseif ($goodsInfo['channelPrice'] >=$shop['quota']) {
					$goodsInfo['freeExpress'] =4;
				}
				// 由pushId获取presell表中对应的时间
				$time=Service::getInstance('sellplan')->getStartAndEnd($pushId);

				if(!empty($time)){
					$goodsInfo['startTime']=$time['startTime'];
					$goodsInfo['endTime']=$time['endTime'];
				}else{
					$goodsInfo['startTime']='';
					$goodsInfo['endTime']='';
				}
			}
			$this->respon( 1, $goodsInfo );
			
		}
	}

	// 新建销售计划(具体方法)
	private function _addSellPlan()
	{
		$sellPlanInfo=array();
		$sellPlanInfo['pushId']=$this->getPost('pushId');
		// 设置交易模式
		if($this->getPost('tradeStyle')==0){
			// 竞买模式.
			$sellPlanInfo['tradePrice']=floatval($this->getPost('startPrice'));
			$sellPlanInfo['tradeCount']=1;
		}else{
			//需要判断一口价是否低于渠道价的95折
			// 获取渠道价,通过goodsId查出pTimes和purchPrice
			// 获取ctimes
			$shopId =Service::getInstance('goods')->getGoodsShop($this->getPost('goodsId'));
			$cTimes =Service::getInstance('sellplan')->getCtimes($shopId,$this->getPost('channel'));
			$res    =Service::getInstance('sellplan')->getPtimesPurch($this->getPost('goodsId'));
			// 渠道价
			$channelPrice=round($res['purchPrice']*$cTimes*$res['ptimes']);
			/*if($channelPrice*0.95>floatval($this->getPost('fixedPrice')))
			{
				$this->error('一口价不能低于'.round($channelPrice*0.95));
			}*/
			$sellPlanInfo['tradePrice'] =floatval($this->getPost('fixedPrice'));
			$sellPlanInfo['tradeCount'] =$this->getPost('upAccount');
			$sellPlanInfo['channelPrice'] = $channelPrice;
		}
		/*预售时间是可选项*/
		// 整理起止时间.如果没填写,设置为0     
		$startTime =trim($this->getPost('startTime'))?trim($this->getPost('startTime')):0;
		$endTime =trim($this->getPost('endTime'))?trim($this->getPost('endTime')):0;

		/*添加上架时间*/
		switch ($this->getPost('startUpTime')) {
			// 立即
			case 1:
			$sellPlanInfo['startUpTime']=time();
			break;
			// 指定时间
			case 2:
			$sellPlanInfo['startUpTime']=strtotime(trim($this->getPost('detailStartUpTime')));
			break;
		}

		/*如果填写了预售起止时间,则执行添加预售队列*/
		if(!empty($startTime)&&!empty($endTime)){
			$res=$this->_addPresellLine(
				$this->getPost('pushId'),
				$this->getPost('presellId'),
				$this->getPost('goodsId'),
				$this->getPost('channel'),
				$startTime,
				$endTime,
				$this->getPost('tradeStyle'),
				$keyword =''
				);
		}
				// 关于返回值$res参数的注释
				/*1 清空成功 2 修改成功 3 修改失败 4 日期时间不能为空 5 设置成功 6 设置失败 7参数错误 8结束时间不得早于起始时间*/
				// 如果修改成功,数据库也作出相应改变;如果
		$sellPlanInfo['tradeStyle']  =intval($this->getPost('tradeStyle'));
		$sellPlanInfo['addPrice']    =floatval($this->getPost('addPrice'));
		// 物流/包装/证书信息传回的是json
		$sellPlanInfo['express']     =json_decode($this->getPost('express'))->id;
		$sellPlanInfo['certificate'] =json_decode($this->getPost('certificate'))->id;
		$sellPlanInfo['package']     =json_decode($this->getPost('package'))->id;
		/**
		 * 分别获取三项费用的具体值
		 */
		$express=Service::getInstance('sellplan')->getCostByCostId($sellPlanInfo['express']);
		$certificate=Service::getInstance('sellplan')->getCostByCostId($sellPlanInfo['certificate']);
		$package=Service::getInstance('sellplan')->getCostByCostId($sellPlanInfo['package']);

		// 计算单件商品总价
		// $sellPlanInfo['tradePrice']=$express+$certificate+$package+$sellPlanInfo['addPrice']+$sellPlanInfo['tradePrice'];

		$sellPlanInfo['goodsId']     =$this->getPost('goodsId');
		// 渠道id
		$sellPlanInfo['channelId']   =intval($this->getPost('channel'));
		// 创建时间
		$sellPlanInfo['createTime']  =time();
		// 添加
		$sellplanId =Service::getInstance('Sellplan')->addSellPlan($sellPlanInfo);
		if($sellplanId){
			// 在push表中标记该商品已列入销售计划
			$inSellplan=Service::getInstance('push')->edit(array('inSellplan'=>1),$this->getPost('pushId'));
			// 判断是否存在于批量销售计划中,存在则删除
			$sellplanMD5=Service::getInstance('sellplan')->sellplanMD5($this->_developer['account'],$sellPlanInfo['channelId']);
			$pushId=$this->getPost('pushId');
			if (in_array($pushId, $this->redis->SMEMBERS($sellplanMD5))) {
				$this->redis->SREM($sellplanMD5,$pushId);
			}
			// 跳转回当前渠道推送商品列表
			$this->flash($this->getPost('fromUrl'),'添加成功',1);
			return;
		}
	}

	// 检测待销售商品数
	public function countToSellplanAction()
	{
		// 生成集合key
		$sellplanMD5=Service::getInstance('sellplan')->sellplanMD5($this->getPost('account'),$this->getPost('channelId'));
		// 获得数量
		$count=$this->redis->SCARD($sellplanMD5);
		if ($count) {
			$this->respon($count,'添加完成');
		}else{
			$this->respon($count,'无商品待添加销售计划');
		}
	} 

	/**
	 * 批量新建销售计划
	 * @param [type] $batch [description]
	 */
	private function _addMulSellPlan($batch)
	{
		// 组合要添加到sell_plan表的相同字段内容
		$data=array();
		$addPrice=trim($this->getPost('addPrice'));
		if(!trim($addPrice)){
			$data['addPrice']=0;
		}else{
			$data['addPrice']=$addPrice;
		}
		$data['batch']       =$batch;
		$data['tradeStyle']  =1;
		$data['tradeCount']  =1;
		$data['createTime']  =time();
		$data['package']     =json_decode($this->getPost('package'))->id;
		$data['certificate'] =json_decode($this->getPost('certificate'))->id;
		$data['channelId']   =$this->getPost('channel');
		$data['description'] =htmlspecialchars(trim($this->getPost('description')));
		$pushIds             =json_decode($this->getPost('pushId'),true);
		$pushIds_str         =implode(",", $pushIds);

		$info=Service::getInstance('push')->getMulPushInfo($pushIds_str);


		// 判断是否填写了预售时间,如果全部填写则添加预售时间,并写入队列
		$startTime=trim($this->getPost('startTime'));
		$endTime=trim($this->getPost('endTime'));
		if($startTime && $endTime){
			$presell              =array();
			$presell['startTime'] =$startTime;
			$presell['endTime']   =$endTime;
			$presell['sellType']  ='一口价';
			foreach ($pushIds as $key => $value) {
				$presell['pushId'] =$value;
				$presellId=$this->db->insert('presell',$presell);
				// 放入队列
				$this->_addPresellLine($value,$presellId,$info[$key]['goodsId'],$data['channelId'],$startTime,$endTime,$data['tradeStyle']);
			}
		}
		

		// 判断上架时间
		$startUpTime=$this->getPost('startUpTime');      
		switch ($startUpTime) {
			case 1:
				$startUpTime =time();
				break;
			case 2:
				$startUpTime =strtotime(trim($this->getPost('detailStartUpTime')));
				break;
		}       

		foreach ($info as $key => $value) {
			// 获取shopId
			$shopId =Service::getInstance('goods')->getGoodsShop($value['goodsId']);
			// 获取cTimes
			$cTimes =Service::getInstance('sellplan')->getCtimes($shopId,$data['channelId']);
			 // 计算出渠道价与quota对比,判断包邮与否
			$channelPrice       =$value['purchPrice']*$cTimes*$value['ptimes'];
			$value['express'] =($channelPrice<=$value['quota'])?4:5;
			/**包邮限额为0 null 或者高于渠道价,都是普通快递;否则为包邮
			 * update:2016-04-21 15:59 by breakdinner
			 */
			if ($value['quota']==0 || $value['quota']==null || $channelPrice<$value['quota']) {
				$value['express'] =5;
				$express =Service::getInstance('sellplan')->getCostByCostId(5);
			}else{
				$value['express'] =4;
				$express =Service::getInstance('sellplan')->getCostByCostId(4);
			}

			// 获取包装费和证书费
			$package =Service::getInstance('sellplan')->getCostByCostId($data['package']);
			$certificate =Service::getInstance('sellplan')->getCostByCostId($data['certificate']);

			$info[$key]['tradePrice'] =$channelPrice+$addPrice+$express+$package+$certificate;

			$info[$key]       =array_merge($data,$value);
			unset($info[$key]['ptimes']);
			unset($info[$key]['quota']);
			unset($info[$key]['purchPrice']);
			// 循环添加批量上架的时间,按商品次序递增30分钟
			$info[$key]['startUpTime'] =$startUpTime;
			$startUpTime +=1800;

			$sellplanId =Service::getInstance('sellplan')->addSellPlan($info[$key]);
			if($sellplanId){
				$inSellplan =Service::getInstance('push')->edit(array('inSellplan'=>1),$info[$key]['pushId']);
			}
		}

		// 从redis的集合中去除待添加计划的商品
		$key=Service::getInstance('sellplan')->sellplanMD5($this->_developer['account'],$data['channelId']);
		$res=array();
		foreach ($pushIds as $k => $v) {
			$res[]=$this->redis->SREM($key,$v);
		}
		// 成功执行的数目
		$res=count($res);
		
		// $fromUrl =substr($this->getPost('fromUrl'), 0,strpos($this->getPost('fromUrl'), '&type'));
		$fromUrl=$this->getPost('fromUrl'); 
		$this->flash($fromUrl,'批量添加成功',1);
		return;
	}

	/**
	 * 修改销售计划
	 * @return [type] [description]
	 */
	public function editOldAction()
	{
		if($this->isPost()){
			// 执行修改销售计划
			$this->_editSellPlan();
		}
		$sellplanId=trim($this->getQuery('id'));
		$goodsId=trim($this->getQuery('goodsId'));
		$pushId=trim($this->getQuery('pushId'));

		// 查询出该sellplanId对应的goods表和goods_image表中的对应信息
		$goodsInfo=Service::getInstance('sellplan')->getGoodsImgSp($sellplanId);

		// 根据shopId查出ptimes,cTimes，与purchPrice相乘得出渠道价.同时查出该店铺的包邮限额quota
		$shop=Service::getInstance('sellplan')->getPtimesQuota($goodsInfo['shopId']);
		$shop['cTimes']=Service::getInstance('sellplan')->getCtimes($goodsInfo['shopId'],$goodsInfo['channelId']);
		$goodsInfo['channelPrice']=round($goodsInfo['purchPrice']*$shop['cTimes']*$shop['ptimes']);
		$goodsInfo['quota']=$shop['quota'];

		// 如果当前商品渠道价高于该店铺包邮限额(限额若为0则作为普通邮费)，则包邮,为4；否则为5，普通快递。4和5是和cost表中的id相对应的。
		/**
		 * update:2016-04-21 15:45 breakdinner
		 */
		if ($shop['quota'] ==0 || is_null($shop['quota']) || $goodsInfo['channelPrice'] <$shop['quota']) {
			$goodsInfo['freeExpress'] =5;
		}elseif ($goodsInfo['channelPrice'] >=$shop['quota']) {
			$goodsInfo['freeExpress'] =4;
		}
		// 获取img
		$goodsInfo['imgurl']=Service::getInstance('goods')->getGoodsImgsByGoodsId($goodsId);
		// 获取物流费用信息
		$express=Service::getInstance('sellplan')->getCostInfo(1);
		// 获取证书费用信息
		$certificate=Service::getInstance('sellplan')->getCostInfo(2);
		// 获取包装费用信息
		$package=Service::getInstance('sellplan')->getCostInfo(3);
		// 定义一个expressJson数组
		$expressJson=$this->_arrayToJson($express);
		// 定义一个certificateJson数组
		$certificateJson=$this->_arrayToJson($certificate);
		// 定义一个packageJson数组
		$packageJson=$this->_arrayToJson($package); 

		/*关联push表获取此商品是否添加过销售计划*/

		// 由pushId获取presell表中对应的时间
		$time=Service::getInstance('sellplan')->getStartAndEnd($pushId);

		if(!empty($time)){
			$goodsInfo['startTime']=$time['startTime'];
			$goodsInfo['endTime']=$time['endTime'];
		}else{
			$goodsInfo['startTime']='';
			$goodsInfo['endTime']='';
		}
		$this->_view->time        =$time;
		$this->_view->pushId      =$pushId;
		$this->_view->sellplanId  =$sellplanId;
		$this->_view->channel     =trim($this->getQuery('channel'));
		$this->_view->goodsInfo   =$goodsInfo;
		$this->_view->express     =$expressJson;
		$this->_view->certificate =$certificateJson;
		$this->_view->package     =$packageJson;
		$this->_view->fromUrl     =$_SERVER['HTTP_REFERER'];
	}

	/**
	 * 修改销售计划
	 * @return [type] [description]
	 */
	private function _editSellPlan()
	{
		$sellplanId=$this->getPost('sellplanId');
		$fromUrl=$this->getPost('fromUrl');
		$pushId=$this->getPost('pushId');
		
		// 声明非时间修改内容数组和时间修改内容数组
		$editArray=array();
		$editTime=array();

		/*整理上架时间*/
		switch ($this->getPost('startUpTime')) {
			// 立即
			case 1:
				$editArray['startUpTime']=time();
				break;
			// 指定时间
			case 2:
				$editArray['startUpTime']=strtotime(trim($this->getPost('detailStartUpTime')));
				break;
			default:
			// 暂时方法.由于添加页面暂时默认为立即上架,所以编辑页面不作时间修改.
			// 下面由于要和$oldArray字段对比,所以下面也要把$oldArray的startUpTime删除
				unset($editArray['startUpTime']);
		}

		/*组合修改后的数据数组做对比用*/
		/*整理交易模式*/
		switch ($this->getPost('tradeStyle')) {
			// 竞买,数量固定为1
			case 0:
				$editArray['tradePrice']=trim($this->getPost('startPrice'));
				$editArray['tradeCount']=1;
				$tradeName='0元起拍';
				break;
				// 一口价
			case 1:
				$editArray['tradePrice']=trim($this->getPost('fixedPrice'));
				$editArray['tradeCount']=trim($this->getPost('upAccount'));
				$tradeName='一口价';
				break;
		}    

		// 邮费/证书费/包装费json取id
		$toGet=array('express','certificate','package');
		foreach ($toGet as $key => $value) {
			$editArray[$value]=json_decode($this->getPost($value))->id;
		}

		/**
		 * 取出所有的费用
		 * update:2016-04-28 18:46 By breakdinenr
		 */
		$allCostIds=$editArray['express'] . ',' . $editArray['certificate'] . ',' . $editArray['package'];
		$allCost=Service::getInstance('sellplan')->getAllCost($allCostIds);
		$allCosts=0;
		foreach ($allCost as $k => $v) {
			$allCosts+=$v['price'];
		}

		$editArray['addPrice']=trim($this->getPost('addPrice'));

		// 计算出总价格
		$editArray['tradePrice'] = $editArray['tradePrice']+$allCosts+$editArray['addPrice'];

		// 根据sellplanId查出原数据
		$oldArray=Service::getInstance('sellplan')->getOldInfo($sellplanId);

		// 暂时方法.与上面unset($editArray['startUpTime'])一致
		if ($this->getPost('startUpTime')==0) {
			unset($oldArray['startUpTime']);
		}

		// 整理时间
		$editTime['startTime']=trim($this->getPost('startTime'));
		$editTime['endTime']=trim($this->getPost('endTime'));

		// 对比,参数必须为新数据在前,旧数据在后.若对比的结果$diff不为空,则$diff就是被修改的部分
		$diff=array_diff_assoc($editArray, $oldArray);

		// 存在非时间差异的情况下
		if (!empty($diff)) {
			// 记录updateTime
			$diff['updateTime']=time();
			$res1=Service::getInstance('sellplan')->update($sellplanId,$diff);
		}

		// 根据pushId查出原预售时间(如果之前sell_plan表中有是否预售的字段,此处可以视情况是否查询);此查询结果也可以用来判断该商品是否添加过预售
		$oldTime=Service::getInstance('sellplan')->getOldPreStartEnd($pushId);

		// 如果查询有结果则表明添加过预售计划
		if ($oldTime) {
			// 对比时间
			$diff_time=array_diff_assoc($editTime, $oldTime);
			// 存在时间差异的情况下
			if ($diff_time) {
				// 根据提交值的数量来做
				$diff_count=count($diff_time);
				switch ($diff_count) {
					case 1:
						// 有内容
						$all_values=array_values($diff_time);
						if ($all_values[0]) {
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
						}
						// 无内容不做修改
						break;
					case 2:
						$all_values=array_values($diff_time);
						// 两处都有内容
						if ($all_values[0]&&$all_values[1]) { 
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
								break;
						}
						// 有startTime而无endTime
						if ($all_values[0]&&!$all_values[1]) {
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
								break;
						}
						// 无startTime而有endTime
						if (!$all_values[0]&&$all_values[1]) {
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
								break;
						}
						// 两处都为空
						if (!$all_values[0]&&!$all_values[1]) {
								$res2=Service::getInstance('sellplan')->deletePresell($pushId);
								break;
						}
						break;
				}
			}
		}else{
			// 添加到预售表
			$presell              =array();
			$presell['pushId']    =$pushId;
			$presell['startTime'] =$editTime['startTime'];
			$presell['endTime']   =$editTime['endTime'];
			$presell['sellType']  =$tradeName;
			Service::getInstance('sellplan')->addPresell($presell);
		}
		// 跳回列表页
		$this->flash($fromUrl,'编辑完成',1);
	}


	//编辑销售计划
	public function editAction(){
		$sellplanId=$this->getPost('sellplan');
		$pushId=$this->getPost('pushId');
		
		// 声明非时间修改内容数组和时间修改内容数组
		$editArray=array();
		$editTime=array();

		/*整理上架时间*/
		switch ($this->getPost('startUpTime')) {
			// 立即
			case 1:
				$editArray['startUpTime']=time();
				break;
			// 指定时间
			case 2:
				$editArray['startUpTime']=strtotime(trim($this->getPost('detailStartUpTime')));
				break;
			default:
			// 暂时方法.由于添加页面暂时默认为立即上架,所以编辑页面不作时间修改.
			// 下面由于要和$oldArray字段对比,所以下面也要把$oldArray的startUpTime删除
				unset($editArray['startUpTime']);
		}

		/*组合修改后的数据数组做对比用*/
		/*整理交易模式*/
		switch ($this->getPost('tradeStyle')) {
			// 竞买,数量固定为1
			case 0:
				$editArray['tradePrice']=trim($this->getPost('startPrice'));
				$editArray['tradeCount']=1;
				$tradeName='0元起拍';
				break;
				// 一口价
			case 1:
				$editArray['tradePrice']=trim($this->getPost('fixedPrice'));
				$editArray['tradeCount']=trim($this->getPost('upAccount'));
				$tradeName='一口价';
				break;
		}    

		// 邮费/证书费/包装费json取id
		$toGet=array('express','certificate','package');
		foreach ($toGet as $key => $value) {
			$editArray[$value]=json_decode($this->getPost($value))->id;
		}

		/**
		 * 取出所有的费用
		 * update:2016-04-28 18:46 By breakdinenr
		 */
		$allCostIds=$editArray['express'] . ',' . $editArray['certificate'] . ',' . $editArray['package'];
		$allCost=Service::getInstance('sellplan')->getAllCost($allCostIds);
		$allCosts=0;
		foreach ($allCost as $k => $v) {
			$allCosts+=$v['price'];
		}

		$editArray['addPrice']=trim($this->getPost('addPrice'));

		// 计算出总价格
		$editArray['tradePrice'] = $editArray['tradePrice']+$allCosts+$editArray['addPrice'];

		// 根据sellplanId查出原数据
		$oldArray=Service::getInstance('sellplan')->getOldInfo($sellplanId);

		// 暂时方法.与上面unset($editArray['startUpTime'])一致
		if ($this->getPost('startUpTime')==0) {
			unset($oldArray['startUpTime']);
		}

		// 整理时间
		$editTime['startTime']=trim($this->getPost('startTime'));
		$editTime['endTime']=trim($this->getPost('endTime'));

		// 对比,参数必须为新数据在前,旧数据在后.若对比的结果$diff不为空,则$diff就是被修改的部分
		$diff=array_diff_assoc($editArray, $oldArray);

		// 存在非时间差异的情况下
		if (!empty($diff)) {
			// 记录updateTime
			$diff['updateTime']=time();
			$res1=Service::getInstance('sellplan')->update($sellplanId,$diff);
		}

		// 根据pushId查出原预售时间(如果之前sell_plan表中有是否预售的字段,此处可以视情况是否查询);此查询结果也可以用来判断该商品是否添加过预售
		$oldTime=Service::getInstance('sellplan')->getOldPreStartEnd($pushId);

		// 如果查询有结果则表明添加过预售计划
		if ($oldTime) {
			// 对比时间
			$diff_time=array_diff_assoc($editTime, $oldTime);
			// 存在时间差异的情况下
			if ($diff_time) {
				// 根据提交值的数量来做
				$diff_count=count($diff_time);
				switch ($diff_count) {
					case 1:
						// 有内容
						$all_values=array_values($diff_time);
						if ($all_values[0]) {
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
						}
						// 无内容不做修改
						break;
					case 2:
						$all_values=array_values($diff_time);
						// 两处都有内容
						if ($all_values[0]&&$all_values[1]) { 
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
								break;
						}
						// 有startTime而无endTime
						if ($all_values[0]&&!$all_values[1]) {
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
								break;
						}
						// 无startTime而有endTime
						if (!$all_values[0]&&$all_values[1]) {
								$res2=Service::getInstance('sellplan')->updatePresell($pushId,$diff_time);
								break;
						}
						// 两处都为空
						if (!$all_values[0]&&!$all_values[1]) {
								$res2=Service::getInstance('sellplan')->deletePresell($pushId);
								break;
						}
						break;
				}
			}
		}else{
			// 添加到预售表
			$presell              =array();
			$presell['pushId']    =$pushId;
			$presell['startTime'] =$editTime['startTime'];
			$presell['endTime']   =$editTime['endTime'];
			$presell['sellType']  =$tradeName;
			Service::getInstance('sellplan')->addPresell($presell);
		}
		// 跳回列表页
		$this->respon(1,'编辑完成');
	}

	/**
	 * 删除销售计划
	 * @return [type] [description]
	 */
	public function delSellPlanAction()
	{
		if($this->isPost()){
			$sellplanId =$this->getPost('sellplanId');
			$goodsId    =$this->getPost('goodsId');
			$channel    =$this->getPost('channel');
			$pushId     =$this->getPost('pushId');

			// 从sell_plan表中为该商品添加删除值
			$res1 =Service::getInstance('sellplan')->deleteSellPlan($sellplanId);
			// 从presell表中删除该商品的预售记录
			$res2 =Service::getInstance('sellplan')->deletePresell($pushId);
			// 上面修改成功的前提下才继续执行
			if($res1){
				// 改变push表中inSellplan的状态.(后续将WHERE条件的判断改成pushId)
				$res3 =Service::getInstance('push')->editByWhere(array('inSellplan'=>0),"id = {$pushId} AND inSellplan = 1");
				$this->respon(1,'移除成功');
			}else{
				$this->respon(0,$res1);
			}
		}
	}

	/**
	 * 操作预售队列
	 * @param [type] $pushId     [description]
	 * @param [type] $presellId  [description]
	 * @param [type] $goodsId    [description]
	 * @param [type] $channel    [description]
	 * @param [type] $start_Time [description]
	 * @param [type] $end_Time   [description]
	 * @param [type] $tradeStyle [description]
	 */
	private function _addPresellLine($pushId,$presellId,$goodsId,$channel,$start_Time,$end_Time,$tradeStyle)
	{
		// $pushId    =$this->getPost('pushId');
		$presellId =$this->getPost('presellId');
		// $goodsId   =$this->getPost('goodsId');
		$channel   =$this->getPost('channel');
		$startDay  =substr($start_Time, 0,10);
		$startTime =substr($start_Time, 11);
		$endDay    =substr($end_Time, 0,10);
		$endTime   =substr($end_Time, 11);

		if (!$pushId) {
				return 7;//参数错误
		}
		if (strtotime($end_Time)<strtotime($start_Time)) {
				return 8;//结束时间不得早于开始时间
		}

		// 根据交易模式确定字段值
		switch ($tradeStyle) {
			case 1:
					$sellType ='一口价';
					break;
			case 0:
					$sellType ='0元起拍';
					break;
		}
		$data=array(
			'pushId'    =>$pushId,
			'startTime' =>$start_Time,
			'endTime'   =>$end_Time,
			'sellType'  =>$sellType
		);
		if ($presellId) {
			// 如果起止时间同时不存在
			if (!$start_Time&&!$end_Time) {
				$result=Service::getInstance('goods')->delPresell($presellId);
				if ($result) {
						//清除队列中的该预售商品
						$data=array(
								'action' =>'del',
								'do'     =>'pre_goods'
						);
						$key =md5('pre_goods-'.$pushId);
						$this->redis->set($key,json_encode($data));
						return 1;//表示清空成功
				}
			}
			$res = Service::getInstance('goods')->editPresell( $data, $presellId );
			if ( $res >= 0 ) {
				//修改队列中,预售商品时间
				$data['do']     = 'pre_goods';
				$data['action'] = 'upd';
				$key            = md5('pre_goods-'.$pushId);
				unset($data['pushId']);
				$this->redis->set($key,json_encode($data));
				return 2;// 修改成功
			} else {
				return 3;//修改失败
			}
		} else {
			if ( $startDay=='' || $startTime=='' || $endDay=='' || $endTime=='' ) {
					return 4;//日期时间不能为空
			}
			$res = Service::getInstance('sellplan')->addPresell( $data );
			if ( $res ) {
				//将该预售商品放入队列中
				$goods              = Service::getInstance('goods')->getPushGoodsById( $pushId );
				$goods['do']        = 'pre_goods';
				$goods['startTime'] = $goods['start'];
				$goods['endTime']   = $goods['end'];
				unset($goods['startDay']);
				unset($goods['start']);
				unset($goods['endDay']);
				unset($goods['end']);
				$goods['start'] = 'no';
				$pushInfo       =Service::getInstance('push')->getPushInfoByGid($goodsId);
				$goods          = array_merge( $goods,$pushInfo );
				$key            = md5( 'pre_goods-'.$pushId );
				if( $this->redis->exists( $key ) ) $this->redis->del( $key );
				$this->redis->lpush('time_list',json_encode($goods));
				return 5;//设置成功
			} else {
				return 6;//设置失败
			}
		}
	}

	/**
	 * 生成批次号
	 * @param  [type] $channelId [description]
	 * @param  array  $puids     [description]
	 * @return [type]            [description]
	 */
	private function _createBatch($channelId,$puids=array())
	{
		$batch=$channelId;
		foreach ($puids as $key => $value) {
			$batch.='_'.$value;
		}
		return substr(md5($batch), 0,10);
	}

	/**
	 * 二维数组信息转Json
	 * @param  [type] $array [description]
	 * @return [type]        [description]
	 */
	private function _arrayToJson($array)
	{
		$newArray =array();
		foreach ($array as $v) {
			$newArray[] =json_encode($v);   
		}
		return $newArray;
	}

	/**
	 * 待添加销售计划的商品写入redis
	 * @return [type] [description]
	 */
	public function sellplanToRedisAction()
	{
		if($this->getRequest()->isXmlHttpRequest()){      
			// 生成集合key
			$sellplanMD5 =Service::getInstance('sellplan')->sellplanMD5($this->getPost('account'),$this->getPost('channelId'));
			// 执行添加
			$res         =$this->redis->SADD($sellplanMD5,trim($this->getPost('pushId'),','));
			// 获取当前集合中pushId的总数
			$count       =$this->redis->SCARD($sellplanMD5);
			if($res){
				$this->respon($res,$count);
			}else{
				$this->respon($res,'添加失败，稍后再试');
			}
		}
	}

	// 批量移除准销售计划(从redis集合中删除指定的pushId)
	public function sellplanOutRedisAction()
	{
		if ($this->getRequest()->isXmlHttpRequest()) {      
			// 生成集合key
			$sellplanMD5 =Service::getInstance('sellplan')->sellplanMD5($this->getPost('account'),$this->getPost('channelId'));
			$pushId      =$this->getPost('pushId');
			// 判断是批量还是单个,存在,则是批量
			if (strpos($pushId, ',')) {
				$pushId=explode(",", $pushId);
				foreach ($pushId as $key => $value) {
					$this->redis->SREM($sellplanMD5,$value);
				}
			}else{
				// 单个删除
				$this->redis->SREM($sellplanMD5,$pushId);
			}
			// 获取当前集合中pushId的总数
			$count =$this->redis->SCARD($sellplanMD5);
			$this->respon(1,$count);
		}
	}

	//	批量上架 获取批次号
	public function batchDetailAction(){

		$puids=$this->getPost('puids');
		$channel = $this->getPost('channel');
		// 获得不在销售计划中的 推送商品
		$pushId = Service::getInstance('sellplan')->getPushNoSellPlan( $puids,$channel );
		$diffPushId = array_values(array_diff($puids,$pushId));

		if ( $pushId ) {
			$pushId_str=implode(",", $pushId);
			//获取所有推送商品的goodsId
			$goodsIds=Service::getInstance('sellplan')->getMulPushGoodsId($pushId_str);
			$goodsId=array();
			foreach ($goodsIds as $key => $value) {
				$goodsId[]=$value['goodsId'];
			}
			$goodsInfo['goodsId']=json_encode($goodsId);
			$data['batch'] = $this->_createBatch(trim($channel),$pushId);
		}
		$data['pushId'] = $pushId;
		$data['diffPushId'] = $diffPushId;

		$this->respon(1,$data);
	}

	// 批量添加销售计划
	public function addBatchAction(){

		// print_r($_POST);exit;
		// echo 1;exit;
		$batch = $this->getPost('batch');
		$upNumber = $this->getPost('upNumber');
		// 组合要添加到sell_plan表的相同字段内容
		$data=array();
		$addPrice=trim($this->getPost('addPrice'));
		if(!trim($addPrice)){
			$data['addPrice']=0;
		}else{
			$data['addPrice']=$addPrice;
		}

		$data['batch']       =$batch;
		$data['tradeStyle']  =1;

		// $data['tradeCount']  =1;
		$data['createTime']  =time();
		$data['package']     =json_decode($this->getPost('package'))->id;
		$data['certificate'] =json_decode($this->getPost('certificate'))->id;
		$data['channelId']   =$this->getPost('channel');
		$data['description'] =htmlspecialchars(trim($this->getPost('description')));
		$pushIds_str         =$this->getPost('pushId');
		$pushIds         =explode(",", $pushIds_str);

		$info=Service::getInstance('push')->getMulPushInfo($pushIds_str);

		// 判断是否填写了预售时间,如果全部填写则添加预售时间,并写入队列
		$startTime=trim($this->getPost('startTime'));
		$endTime=trim($this->getPost('endTime'));
		if($startTime && $endTime){
			$presell              =array();
			$presell['startTime'] =$startTime;
			$presell['endTime']   =$endTime;
			$presell['sellType']  ='一口价';
			foreach ($pushIds as $key => $value) {
				$presell['pushId'] =$value;
				$presellId=$this->db->insert('presell',$presell);
				// 放入队列
				$this->_addPresellLine($value,$presellId,$info[$key]['goodsId'],$data['channelId'],$startTime,$endTime,$data['tradeStyle']);
			}
		}
		

		// 判断上架时间
		$startUpTime=$this->getPost('startUpTime');      
		switch ($startUpTime) {
			case 1:
				$startUpTime =time();
				break;
			case 2:
				$startUpTime =strtotime(trim($this->getPost('detailStartUpTime')));
				break;
		}       

		foreach ($info as $key => $value) {

			// 获取shopId
			$shopId =Service::getInstance('goods')->getGoodsShop($value['goodsId']);
			// 获取cTimes
			$cTimes =Service::getInstance('sellplan')->getCtimes($shopId,$data['channelId']);
			 // 计算出渠道价与quota对比,判断包邮与否
			$channelPrice       =$value['purchPrice']*$cTimes*$value['ptimes'];
			$value['express'] =($channelPrice<=$value['quota'])?4:5;
			/**包邮限额为0 null 或者高于渠道价,都是普通快递;否则为包邮
			 * update:2016-04-21 15:59 by breakdinner
			 */
			if ($value['quota']==0 || $value['quota']==null || $channelPrice<$value['quota']) {
				$value['express'] =5;
				$express =Service::getInstance('sellplan')->getCostByCostId(5);
			}else{
				$value['express'] =4;
				$express =Service::getInstance('sellplan')->getCostByCostId(4);
			}

			// 获取包装费和证书费
			$package =Service::getInstance('sellplan')->getCostByCostId($data['package']);
			$certificate =Service::getInstance('sellplan')->getCostByCostId($data['certificate']);

			$value['tradePrice'] =$channelPrice+$addPrice+$express+$package+$certificate;
			if ( $upNumber == 1 ) {
				$value['tradeCount'] = 1;
			}else{
				$value['tradeCount'] = $value['goodsStock'];
			}
			$info[$key]       =array_merge($data,$value);
			unset($info[$key]['ptimes']);
			unset($info[$key]['quota']);
			unset($info[$key]['purchPrice']);
			unset($info[$key]['goodsStock']);
			// 循环添加批量上架的时间,按商品次序递增30分钟
			$info[$key]['startUpTime'] =$startUpTime;
			$startUpTime +=1800;
			$sellplanId =Service::getInstance('sellplan')->addSellPlan($info[$key]);
			if($sellplanId){
				$inSellplan =Service::getInstance('push')->edit(array('inSellplan'=>1),$info[$key]['pushId']);
			}
		}

		// 从redis的集合中去除待添加计划的商品
		$key=Service::getInstance('sellplan')->sellplanMD5($this->_developer['account'],$data['channelId']);
		$res=array();
		foreach ($pushIds as $k => $v) {
			$res[]=$this->redis->SREM($key,$v);
		}
		// 成功执行的数目
		$res=count($res);
		
		$this->respon(1,'批量添加成功');
	}
}
 ?>