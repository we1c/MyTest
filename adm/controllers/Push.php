<?php

class PushController extends BaseController {
	public function init() {
		parent::init();
	}
	
	//商品列表
	public function indexAction() {
		$showType = $this->getQuery('showType', 'manager');
        $myDis  = $this->_developer['disId'];
        $myRole = $this->_developer['role'];
        if($showType == 'manager'){
            $channel = Service::getInstance('distributor')->getGroupDis($myDis, $myRole);
            $manager = array();
            if($channel){
                $url = Yaf_Application::app()->getConfig()->get('image')->get('url');
                foreach ($channel as $k=>$row){
                    $manager[$k]['devId'] = $row['devId'];
                    $manager[$k]['name'] = Service::getInstance('developers')->getNameById($row['devId']);
                    $manager[$k]['num'] = $row['num'];
                    $default = "/default-dis-image.png";
                    $manager[$k]['headimgurl'] = $url.$default;
                }
            }                    
            $this->_view->manager = $manager;
        }elseif ($showType == 'choice') {			
            $devId = $this->getQuery('devId');
			$dis   = Service::getInstance('distributor')->getMyDis($myDis, $myRole, $devId);
            foreach( $dis as $k => $v ){
				if( $v['devId'] == 0 ){
					$dis[$k]['manager'] = '暂无';                                   
				}else{
					$dis[$k]['manager'] = Service::getInstance('developers')->getNameById($v['devId']);
				}
//				$count                 = Service::getInstance('push')->countChannel( $v['id'] );
//				$dis[$k]['skuNum']     = $count['skuNum'];
//				$dis[$k]['costTotal']  = round($count['costTotal']);
//				$dis[$k]['stockTotal'] = $count['stockTotal'];   
                $passTotal = Service::getInstance('goods')->goodsPushList('', '', '', $v['id'], 0, '', '', '','pass', 1);
                $sellplanTotal=Service::getInstance('sellplan')->sellplanCountByCid($v['id']);
                $dis[$k]['passTotal'] = $passTotal;
                $dis[$k]['sellplanTotal'] = $sellplanTotal;
				$default               = "/default-dis-image.png";
				$dis[$k]['headimgurl'] = Service::getInstance('shop')->getAvata( $v['headimgurl'],$default );
			}
            $this->_view->passTotal = $passTotal;
            $this->_view->sellplanTotal = $sellplanTotal;
            $this->_view->showType = $showType;
            $this->_view->devId = $devId;
			$this->_view->dis = $dis;
		}else{
			$perpage     = $this->getQuery('perpage',100);
			$showpage    = 5;
			$page        = $this->getQuery('page', 1);
			$keyword     = trim($this->getQuery('keyword', ''));
			$time        = $this->getQuery('time', '');
			$channel     = $this->getQuery('channel', '');
			$searchType  = $this->getQuery('searchType', '1');
			$minPrice    = $this->getQuery('minPrice', '');
			$maxPrice    = $this->getQuery('maxPrice', '');
			$checkStatus = $this->getQuery('checkStatus', 'pass');
                        $devId       = $this->getQuery('devId','');
			$isCount     = 1; #$isCount为1表示只获取数量
			$g_status    = 1; #$g_status表示有关于goods表status字段的参数
			// 从redis中获取当前操作用户在当前渠道商中的预销售计划集合
			$sellplanMD5=Service::getInstance('sellplan')->sellplanMD5($this->_developer['account'],$channel);
			$sellplanInRedis=$this->redis->SMEMBERS($sellplanMD5);
			$this->_view->sellplanInRedis=$sellplanInRedis; 
			$this->_view->batch=$sellplanMD5;			
            // 审核中/仓库中/异常/停售/锁定/销售计划状态数组
            $statusArr = array('waitCheck','pass','abnormal','stop','lock','sellplan');
            $count = array();
            //获取各个状态的数量
            foreach($statusArr as $row){
                $count[$row] = Service::getInstance('goods')->goodsPushList($page, $perpage, $keyword, $channel, $time, $searchType, $minPrice, $maxPrice,$row, $isCount);
            }
            //获取销售计划数量
            // $count['sellplan'] = Service::getInstance('sellplan')->sellplanCountByCid($channel);
            $this->_view->count = $count;
            // 获取不同状态的商品列表
            $data = Service::getInstance('goods')->goodsPushList($page, $perpage, $keyword, $channel, $time, $searchType, $minPrice, $maxPrice,$checkStatus,'');
            $this->_view->checkStatus    = $checkStatus;                       
			// 推送商品的状态判定，当status值为1的时候，送回审核。0时为正常，2时为锁定，3时为价格异常（推送商品被改变进货价后出现）;(停售和售罄的状态要去goods表中获取)
			$myRole = $this->_developer['role'];
			$manager = Service::getInstance('distributor')->getManagerById($channel);
			if ($manager == $this->_devid || in_array('1', $myRole) || in_array('6', $myRole)) {
				$action = true;
			}else{
				$action = false;
			}
			$shop = Service::getInstance('shop')->getList();
			$myDis = $this->_developer['disId'];
			$dis = Service::getInstance('distributor')->getMyDis($myDis,$myRole);
            foreach ($data['list'] as $k => $v){
                $data['list'][$k]['shopScore'] = Service::getInstance('shop')->getShopScoreToday($v['shopId']);
            }
			$this->_view->account        =$this->_developer['account'];#这个是为了配合添加到【待】添加销售计划方法使用的
			$this->_view->shop           = $shop;
			$this->_view->showType       = $showType;
			$this->_view->action         = $action;
			$this->_view->list           = $data['list'];

			/**update:2016-04-26 19:58 By breakdinner
			 * 此处关闭的原因是在不同的页面需要单独获取非当前页面状态商品的数目,而上面所有获取数目的方法是针对于不同的一级标签而定的
			 * 有些是获取不到的
			 */
			// $this->_view->total          = $passTotal + $waitCheckTotal+$lockTotal+$abnormalTotal+$stopTotal;
			$this->_view->perpage        = $perpage;
			$this->_view->page           = $page;
			$this->_view->channel        = $channel;
			$this->_view->keyword        = $keyword;
			$this->_view->time           = $time;
			$this->_view->searchType     = $searchType;
			$this->_view->minPrice       = $minPrice;
			$this->_view->maxPrice       = $maxPrice;
            $this->_view->devId          = $devId;
			$this->_view->home           = Yaf_Application::app()->getConfig()->get('home')->get('gapi')->url;
			/**
			 * update:2016-04-26 19:47 By breakdinner
			 * 修复了各种状态的分页
			 */
			$pageObj = new Page($data['total'], $perpage, $showpage, $page, '', array('push', 'index', 'keyword' => $keyword, 'checkStatus' => $checkStatus, 'channel'=>$channel, 'perpage' => $perpage, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice, 'showType' => $showType, 'searchType' => $searchType, 'devId' => $devId));
			$this->_view->pagebar = $pageObj -> showpage( );
            // 获取物流费用信息
            $express=Service::getInstance('sellplan')->getCostInfo(1);
            // 获取证书费用信息
            $certificate=Service::getInstance('sellplan')->getCostInfo(2);
            // 获取包装费用信息
            $package=Service::getInstance('sellplan')->getCostInfo(3);
            $this->_view->express = $this->_arrayToJson($express);
            $this->_view->certificate = $this->_arrayToJson($certificate);
            $this->_view->package = $this->_arrayToJson($package);
		}
	}
	
    //删除商品
    public function delAction(){
        if ( $this->isPost() ) {
            $id = $this->getPost('id');
            $channel = $this->getPost('channel');
            $cInfo = Service::getInstance('distributor')->getInfoById( $channel );
            $resImg = true;
            $resGoods = true;
            if( $cInfo['apiType'] == 2 ){
                $pInfo = Service::getInstance('push')->getPushInfo( $id );
                //删除数据库保存的上传图片路径
                if( $cInfo['apiImg'] == 1 ){
                    $resImg = Service::getInstance('goods')->delPushGoodsImg( $pInfo['goodsId'] , $pInfo['channel'] );
                }
                //通知渠道下架商品
                if ($cInfo['apiDown'] == 1) {
                    $resGoods = Service::getInstance('apigoods')->downGoodsByChannel($channel, $pInfo['goodsId']);
                    $this->respon(1, '移除成功');
                }
            }
            if( $resImg && $resGoods ){
                if( Service::getInstance('goods')->delPushGoods( $id ) ){
                    $this->respon( 1, "移除成功" );
                } else {
                    $this->respon( 0, "移除失败" );
                }    
            }else{
                if( $resImg && !$resGoods ){
                    $this->respon( 0 ,'通知渠道下架商品失败,移除失败！' );
                }elseif( !$resImg && $resGoods ){
                    $this->respon( 0 ,'删除推送商品图片的数据库数据失败！' );
                }else{
                    $this->respon( 0, '删除推送商品图片的数据库数据失败;通知渠道下架商品失败！' );
                }
            }
        }
    	return false;
    }

    public function batchDelAction(){
        if( $this->isPost() ){
            $puids = $this->getPost('puids');
            if (empty($puids))
                $this->respon(0, "没有选择商品");
            $fail = array();
            foreach ($puids as $id) {
                $pInfo = Service::getInstance('push')->getPushInfo($id);
                $cInfo = Service::getInstance('distributor')->getInfoById($pInfo['channel']);
                //判断是否有预售或者锁定，给出相应的记录数组
                $row = Service::getInstance('goods')->getPresell( $id );
                if( !empty($row) ){
                    if( time() < strtotime($row['startTime']) ){
                        $fail['error']['unlock'][] = $id;
                    }elseif(time()>strtotime($row['startTime']) && time()<strtotime($row['endTime'])){
                        $fail['error']['lock'][] = $id;
                    }
                    continue;
                }
                if ($cInfo['apiType'] == 2) {
                    //通知渠道下架商品
                    if ($cInfo['apiDown'] == 1) {
                        if (Service::getInstance('apigoods')->downGoodsByChannel($pInfo['channel'], $pInfo['goodsId'])) {
                            $fail['success'][] = $id;
                        } else {
                            $fail['error']['fail'][] = $id;
                        }
                    }
                } else {
                    if (Service::getInstance('goods')->delPushGoods($id)) {
                        $fail['success'][] = $id;
                    } else {
                        $fail['error']['fail'][] = $id;
                    }
                }
            }

            if( empty( $fail['error'] ) ){
                $this->respon( 1 ,"批量删除成功！" );
            }else{
                $this->respon( 0 , $fail );
            }
        }
    }
    
    //推送商品
    public function pushGoodsAction() {
        $id = $this->getQuery('id');
        $exist = Service::getInstance('goods')->getPushGoods( $id );
        if (!empty($exist) ) {
            $this->flash( '/push/index', '该商品已经推送过，不能重复推送' );
        } else {
            $goods = array(
                'goodsId'=>$id,
                'createTime'=>time(),
                //推送完成时设置状态为仓库中
                'status'=>0
            );
            $res = Service::getInstance('goods')->addGoods( $goods );
            if ( $res ) {
                $this->flash("/push/index", "推送成功");
            } else {
                $this->flash("/push/index", "推送失败");
            }
        }
    }
    //预售
    public function presellAction() {
        if ( $this->isPost()) {
            $id = $this->getPost('id');//pushId
            $pid = $this->getPost('pid');//presellId
            $gid = $this->getPost('gid');//goodsId
            $keyword = $this->getPost('keyword');
            $channel = $this->getPost('channel');
            $startDay = trim($this->getPost('startday'));
            $startTime = trim($this->getPost('starttime'));
            $start = $startDay.' '.$startTime;
            $endDay = trim($this->getPost('endday'));
            $endTime = trim($this->getPost('endtime'));
            $end = $endDay.' '.$endTime;
            $sellType = $this->getPost('sellType');

            if ( !$id ) {
                $this->error( '参数错误' );
                return;
            }
            if ( $end < $start ) {
                $this->error( '结束时间不能小于开始时间' );
                return;
            }
            $data = array(
                'pushId'=>$id,
                'preTime'=>'',
                'startTime'=>$start,
                'endTime'=>$end,
                'sellType'=>$sellType
            );
            if ( $pid ) {
                if ( !$startDay && !$startTime && !$endDay && !$endTime ) {
                    $result = Service::getInstance('goods')->delPresell( $pid );
                    if ( $result ) {
                        //清除队列中的该预售商品
                        $data=array('action' => 'del','do' => 'pre_goods');
                        $key = md5( 'pre_goods-'.$id );
                        $this->redis->set($key,json_encode($data));
                        $this->flash( '/push/index/?showType=list&keyword='.$keyword.'&channel='.$channel, '清空成功' );
                    }
                }
                $res = Service::getInstance('goods')->editPresell( $data, $pid );
                if ( $res >= 0 ) {
                    //修改队列中,预售商品时间
                    $data['do'] = 'pre_goods';
                    $data['action'] = 'upd';
                    $key = md5('pre_goods-'.$id);
                    unset($data['pushId']);
                    unset($data['preTime']);
                    $this->redis->set($key,json_encode($data));
                    $this->flash( '/push/index/?showType=list&keyword='.$keyword.'&channel='.$channel, '修改成功' );
                } else {
                    $this->flash( '/push/index/?showType=list&keyword='.$keyword.'&channel='.$channel, '修改失败' );
                }
            } else {
                if ( $startDay=='' || $startTime=='' || $endDay=='' || $endTime=='' ) {
                    $this->flash( '/push/index/?showType=list&keyword='.$keyword.'&channel='.$channel, '日期时间不能为空' );
                }
                $res = Service::getInstance('goods')->addPresell( $data );
                if ( $res ) {
                    //将该预售商品放入队列中
                    $goods = Service::getInstance('goods')->getPushGoodsById( $id );
                    $goods['do'] = 'pre_goods';
                    $goods['startTime'] = $goods['start'];
                    $goods['endTime'] = $goods['end'];
                    unset($goods['startDay']);
                    unset($goods['start']);
                    unset($goods['endDay']);
                    unset($goods['end']);
                    unset($goods['preTime']);
                    $goods['start'] = 'no';
                    $pushInfo = $this->_getPushInfoByGid($gid);
                    $goods = array_merge($goods, $pushInfo);
                    $key = md5('pre_goods-' . $id);
                    if ($this->redis->exists($key))
                        $this->redis->del($key);
                    $this->redis->lpush('time_list', json_encode($goods));
                    $this->flash('/push/index/?showType=list&keyword=' . $keyword . '&channel=' . $channel, '设置成功');
                } else {
                    $this->flash( '/push/index/?showType=list&keyword='.$keyword.'&channel='.$channel, '设置失败' );
                }
            }
        }

        $id = $this->getQuery('id');
        $keyword = $this->getQuery('keyword', '');
        $channel = $this->getQuery('channel', '');
        $goods = Service::getInstance('goods')->getPushGoodsById( $id );
        $this->_view->goods = $goods;
        $this->_view->keyword = $keyword;
        $this->_view->channel = $channel;
    }
    //根据商品ID获取推送信息
    private function _getPushInfoByGid( $gid ){
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
    
    //编辑
    public function editAction() {
        if($this->isPost()) {
            $gid = intval($this->getPost('id'));
            $channel = $this->getPost('channel');
            $keyword = $this->getPost('keyword');
            //$time = $this->getPost('time');
            $showType = $this->getPost('showType');
            //$page = $this->getPost('page');
            //$perpage = $this->getPost('perpage');
            $imgs = $this->getPost('imgs');
            if ( !$gid ) {
                $this->flash('/push/index','参数错误');
                return false;
            }

            $file = $_FILES['file'];
            
            if( !empty($file) ){
                 
                //获取删除新增图片的记录
                $delImg = $this->getPost('delImg');
                $record = array();
                if( !empty($delImg) ){
                    foreach( $delImg as $num => $del ){
                        foreach( $del as $index => $value ){
                            $record[] = $num.'-'.$index;
                        }
                    }
                }
                //获取新增加图片的索引记录
                $imgSort = $this->getPost('imgSort');
                $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                if( !empty($file['name']) ){
                    foreach( $file['name'] as $num => $img ){
                        if( !empty($img) ){
                            foreach( $img as $index => $name ){
                                $flag = $num.'-'.$index;
                                if( !$file['error'][$num][$index] && !in_array( $flag,$record ) ){
                                    $avatar = $file['tmp_name'][$num][$index];
                                    $hash = md5( $avatar );
                                    if( move_uploaded_file( $avatar ,Util::getDir( $dir,$hash ).$hash.'_image.jpg' ) ){
                                        //对新增加的图片做页面相对应的索引记录
                                        $sortIndex = $imgSort[$num][$index];
                                        $imgs[$sortIndex] = $hash;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //对图片所有hash值按页面索引进行排序
            if (!empty($imgs))
                ksort($imgs);
            $original = Util::getDir($dir, $imgs[0]) . $imgs[0] . '_image.jpg';
            $oneThumb = str_replace('_image.', '_thumb_400x400.', $original);
            if (!file_exists($oneThumb)) {
                Service::getInstance('superadmin')->makeThumbFac($original, array('400x400'));
            }
            //获取删除已经保存的记录
            $delOldImg = $this->getPost('delOldImg');
            if( !empty($delOldImg) ){
                foreach( $delOldImg as $k => $hash ){
                    $file = Util::getDir( $dir,$hash ).$hash.'_image.jpg';
                    if( file_exists( $file ) ){
                        @unlink($file);
                    }
                }
            }
            if( is_array( $imgs ) ) {
                $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
                foreach ($imgs as $k=>$v){
                    $temp = intval($k+1);
                    $sql .="('{$gid}', '{$v}', $temp ),";
                }
                $sql = substr($sql, 0,-1);
                Service::getInstance('goods')->editgoods_images($sql,$gid);
            }
            $this->flash('/push/index?keyword='.$keyword.'&channel='.$channel.'&showType=list','编辑成功');
        }else{
            $id = $this->getQuery('id',0);
            $channel = $this->getQuery('channel');
            $keyword = $this->getQuery('keyword');
            //$time = $this->getQuery('time');
            $showType = $this->getQuery('showType');
            //$page = $this->getQuery('page');
            //$perpage = $this->getQuery('perpage');
            if ( !$id ) {
                $this->flash( '/push/index', '参数不合法' );
            }
            $pushinfo = Service::getInstance('push')->getPushInfo( $id );
            if ( !$pushinfo ) {
                $this->flash( '/push/index', '未查到数据,请检查请求是否正确' );
            }
            $Info = Service::getInstance('goods')->getGoodsInfoById( $pushinfo['goodsId'] );
            if ( !$Info ) {
                $this->flash( '/push/index', '该商品不存在！' );
            }
            $dis = Service::getInstance('distributor')->getInfoById( $pushinfo['channel'] );
            $category1 = Service::getInstance('goods')->getGoodsCategory();
            if($Info['category1'] && $Info['category2']) {
                $category2 = Service::getInstance('goods')->getCat($Info['category1']);
                $category3 = Service::getInstance('goods')->getCat($Info['category2']);
            } else {
                $category2 = array();
                $category3 = array();
            }
            $arr = $Info['attribute'];
            if ( is_array($arr) ) {
                foreach ( $arr as $k=>$v ) {
                    if( $v['value']['id'] ) {
                        $data = Service::getInstance('goods')->getParamByPid($v['key']['id']);
                        $arr[$k]['value']['param'] = $data;
                    }
                }
            }
            $Info['attribute'] = $arr ? $arr : '';
            $this->_view->Info = $Info;
            $this->_view->dis = $dis;
            $this->_view->category1 = $category1;
            $this->_view->category2 = $category2;
            $this->_view->category3 = $category3;
            $this->_view->channel = $channel;
        }
    }
    
    //下单
    public function addOrderAction() {
        if ( $this->isPost() ) {
            $showType          = 'list';
            $developer         = Yaf_Registry::get('developer');
            $keyword           = $this->getPost('keyword');
            $channel           = $this->getPost('channel');
            $shopId            = $this->getPost('sid');
            $payway            = $this->getPost('payway');
            $paytype           = $this->getPost('paytype');
            $paybank           = $this->getPost('paybank');
            $deliverWay        = $this->getPost('deliver');
            $remark            = $this->getPost('remark');
            $sellerremark      = $this->getPost('sellerremark');
            $freight           = $this->getPost('freight');
            $realprice         = $this->getPost('realprice');
            $consignee         = $this->getPost('consignee');
            $phone             = preg_replace('/[\s-]/', '', $this->getPost('phone'));
            $telephone         = $this->getPost('tel');
            $province          = $this->getPost('province');
            $city              = $this->getPost('city');
            $area              = $this->getPost('area');
            $address           = $this->getPost('address');
            $number            = $this->getPost('number');
            $goodsId           = $this->getPost('gid');
            $pushId            = $this->getPost('pushid');
            $price_certificate = $this->getPost('price_certificate');
            $price_pack        = $this->getPost('price_pack');
            $price_mount       = $this->getPost('price_mount');
            $price_other       = $this->getPost('price_other');
            $addressId         = $this->getPost('addressIndex');
            if($addressId){
                $addressId = substr($addressId, 8);
                $addressData = Service::getInstance('address')->getAddressById($addressId);
                $userId = $addressData['customer_id'];
                $consignee = $addressData['name'];
                $phone = $addressData['tel'];
                $province = $addressData['province'];
                $city = $addressData['city'];
                $area = $addressData['area'];
                $address = $addressData['address'];
            }else{
                $userId   = Service::getInstance('orders')->getUser( $phone, $consignee );
            }
            $isBrush = 0;
            $isDeliver = 1;
            $isTake = 0;
            if ( $this->getPost('sub1') ){
                $isBrush = 1;
                $isDeliver = 2;
                $isTake = 2;
            }
            $pushinfo = Service::getInstance('push')->getPushInfo( $pushId );            
            $gInfo    = Service::getInstance('goods')->getGoodsInfoById( $goodsId );
            $period = Service::getInstance('shop')->getShopPeriodById($shopId);
            if ( $gInfo['status'] != 1 && $gInfo['status'] != 6 ) {
                $this->flash('/push/index/?keyword='.$keyword.'&channel='.$channel,'该商品已下架，不能出售',3);
            }
            if ( $gInfo['status'] == 6 && $pushinfo['status'] != 2 ) {
                $this->flash('/push/index/?keyword='.$keyword.'&channel='.$channel,'该商品已被其他渠道锁定',3);
            }
            $stock = $gInfo['goodsStock']-$number;
            if ( $stock < 0) {
                $this->flash('/push/index/?keyword='.$keyword.'&channel='.$channel,'该商品库存不足'.$number.'个',1);
            }
            
            //orderType等于4为淘宝订单
            if($gInfo['shopId'] == 105){
                $orderType = 4;
                $isBuyer   = 0;
            }else{
                $orderType = 3;
                $isBuyer = 1;
            }

            //如果实付价格低于 平台价格90% 将订单设为价格异常订单
            $unitPrice = ($realprice-$price_certificate-$price_pack-$price_mount-$price_other)/$number;
            $minPrice  = round($gInfo['purchPrice'] * $gInfo['ptimes'] * 0.9); 
            if ( $unitPrice < $minPrice ) {
                $priceAnomaly = 1;
            }else{
                $priceAnomaly = 0;
            }

            //如果分销商订单超过受限额度，订单状态为超额异常
            $cInfo = Service::getInstance('distributor')->getInfoById( $channel );
            $beyond_limit = 0;
            if ( $cInfo['clearing_type'] == 2 ) {
                $usableLimit = $cInfo['credit_limit'] - $cInfo['used_limit'];
                if ( $usableLimit <= $realprice ) {
                    $beyond_limit = 1;
                }else{
                    $res = Service::getInstance('distributor')->updateUsedLimit($realprice,$channel);
                }
            }

            if ( $gInfo ) {
                $data = array(
                    'orderCode'    =>Service::getInstance('orders')->getOrderCode(1,2),
                    'price'        =>$realprice,
                    'userId'       =>$userId,
                    'uname'        =>$consignee,
                    'payw'         =>$payway,
                    'payType'      =>$paytype,
                    'payBank'      =>$paybank,
                    'deliverWay'   =>$deliverWay,
                    'createTime'   =>time(),
                    'payTime'      =>time(),
                    'tel'          =>$phone,
                    'telephone'    =>$telephone,
                    'remark'       =>$remark,
                    'sellerremark' =>$sellerremark,
                    'goodsId'      =>$gInfo['id'],
                    'shopId'       =>$gInfo['shopId'],
                    'province'     =>$province,
                    'city'         =>$city,
                    'area'         =>$area,
                    'address'      =>$address,
                    'number'       =>$number,
                    'channel'      =>$pushinfo['channel'],
                    'isPay'        =>1,
                    'isDeliver'    =>$isDeliver,
                    'isTake'       =>$isTake,
                    'orderType'    =>$orderType,
                    'sellType'     =>0,
                    'operator'     =>$developer['name'],
                    'isBrush'      =>$isBrush,
                    'priceAnomaly' =>$priceAnomaly,
                    'isBuyer'      =>$isBuyer,
                    'beyond_limit' =>$beyond_limit,
                );
                $dataPrice = array(
                    'price_freight'     =>$freight,
                    'price_certificate' =>$price_certificate,
                    'price_pack'        =>$price_pack,
                    'price_mount'       =>$price_mount,
                    'price_other'       =>$price_other,
                    'updateTime'        =>time()
                );
            }
            if ( $data && $stock >= 0) {
                $res = Service::getInstance('orders')->add($data);
                $dataPrice['orderId'] = $res;
                $resPrice = Service::getInstance('orders')->addOrderPrice($dataPrice);
                
                //添加客户地址
                if(!$addressId){
                    $address = array(
                        'customer_id'   => $userId,
                        'name'          => trim($consignee),
                        'province'      => $province,
                        'city'          => $city,
                        'area'          => $area,
                        'address'       => trim($address),
                        'tel'           => trim($phone),                    
                    );
                    $oldAddress = Service::getInstance('address')->getAddressAll();
                    //通过变量a的值的变化来确定address表中是否与新增的数据相同
                    $a = 'no';
                    foreach($oldAddress as $oldlist){
                        $repeatAddress = $address['customer_id'] === trim($oldlist['customer_id']) && $address['name'] === trim($oldlist['name']) && $address['province'] === trim($oldlist['province']) && $address['city'] === trim($oldlist['city']) && $address['area'] === trim($oldlist['area']) && $address['address'] === trim($oldlist['address']) ;
                        if($repeatAddress){
                            $a = 'yes';
                        }
                    }
                    if($a == 'no'){
                        $addressRes = Service::getInstance('address')->add($address);
                    }
                }
                           
                if ($res) {
                    //极光推送
                    $resPush = $this->redisAppPush( $shopId );
                    //更新库存
                    $updateStock = Service::getInstance('goods')->edit(array('goodsStock'=>$stock),$gInfo['id']);
                    if ( $period == 0 && $isBrush == 0 ){
                        //当时结算
                        $accountPrice = ($gInfo['purchPrice']*$number)
                                      + $freight+$price_certificate+$price_pack+$price_mount+$price_other;
                        $data   = array(
                                "shopId"=>$shopId,
                                "devId"=>$developer['id'],
                                "total"=>$accountPrice,
                                "createTime"=>time(),
                                "expectTime"=>date('Y-m-d',time()),
                                "note"=>'',
                                "type"=>1,
                                "audit_status"=>4,
                                );

                        $resAcc = Service::getInstance('account')->add($data);
                        if ( $resAcc ){
                            $update = Service::getInstance('orders')->editAccount(array('accountId'=>$resAcc,'account_status'=>1),$res);
                        }
                    }
                    

                    //添加订单商品
                    $orderGoods = array(
                        'order_id'        => $res,
                        'goods_id'        => $goodsId,
                        'shop_id'         => $shopId,
                        'customer_id'     => $userId,
                        'goods_name'      => $gInfo['name'],
                        'goods_image'     => $gInfo['goodsOneImg'],
                        'goods_price'     => $gInfo['purchPrice'],
                        'goods_number'    => $number,
                        'goods_pay_price' => $realprice,
                    );
                    $addOrderGoods = Service::getInstance('orders')->addOrderGoods($orderGoods);                    
                    if ($stock > 0) {
                        Service::getInstance('push')->delPresell($pushId);
                        Service::getInstance('push')->edit(array('status'=>0),$pushId);
                         $this->flash('/push/index/?keyword='.$keyword.'&channel='.$channel.'&showType=list','下单成功');
                    } else {
                        Service::getInstance('goods')->change( $goodsId, array('status'=>2) );
                        $pushIdArr = Service::getInstance('push')->getPushWithGoodsId($goodsId);
                        foreach ($pushIdArr as $k => $v) {
                            Service::getInstance('push')->delPresell($v['id']);
                        }
						//通知渠道下架商品
						Service::getInstance('apigoods')->downGoodsByGid($goodsId);
                        Service::getInstance('sellplan')->deleteSellPlanByGid( $goodsId );
						Service::getInstance('push')->delPush($goodsId);

						$resPush = $this->redisAppPush( $shopId );

						$this->flash('/push/index/?keyword='.$keyword.'&channel='.$channel.'&showType=list','下单成功');
					}
				}else {
					$this->flash('/push/index/?keyword='.$keyword.'&channel='.$channel.'&showType=list','下单失败');
				}
			} else {
				$this->flash('/push/idnex/?keyword='.$keyword.'&channel='.$channel.'&showType=list','下单失败');
			}
		}
		//获取信息
		$id      = $this->getQuery('id');
		$keyword = $this->getQuery('keyword');
		$channel = $this->getQuery('channel');

		if ( !$id ) {
			$this->flash( '/push/index/?id='.$id.'&keyword='.$keyword.'&channel='.$channel, '参数错误' );
		}
		$pushGoods = Service::getInstance('goods')->getPushGoodsById( $id );
		if ( $pushGoods ) {
			$goods = Service::getInstance('goods')->getGoodsInfoById( $pushGoods['gid'] );
			$shop  = Service::getInstance('shop')->getShopinfo($goods['shopId']);
            $spInfo = Service::getInstance('sellplan')->getSellplanByPushChanel( $id,$channel);
			$goods['totalPrice'] = round( $goods['purchPrice'] * $goods['ptimes'] );
			$goods['pushId']     = $pushGoods['id'];
			$nameArr             = Service::getInstance('goods')->getChannelById($pushGoods['channel']);
			$goods['cname']      = $nameArr['name'];
			$goods['payway']     = $nameArr['payway'];
			$goods['paybank']    = $nameArr['paybank'];
			$goods['goodsStock'] = $pushGoods['goodsStock'];
			unset( $goods['price'] );
			unset( $goods['platfPrice'] );
			//unset( $goods['purchPrice'] );
			unset( $goods['ptimes'] );
			unset( $goods['mtimes'] );
			// $express  = Service::getInstance('orders')->getSonExpress();
			$province = Service::getInstance('shop')->getProvince();
			$payway   = Service::getInstance('orders')->getPayWay();
			$paytype  = Service::getInstance('orders')->getPayType( $goods['payway']);
			$paybank  = Service::getInstance('orders')->getPayBank();

            // 获取物流费用信息
            $express=Service::getInstance('sellplan')->getCostInfo(1);
            // 获取证书费用信息
            $certificate=Service::getInstance('sellplan')->getCostInfo(2);
            // 获取包装费用信息
            $package=Service::getInstance('sellplan')->getCostInfo(3);
            $this->_view->express     = $express;
            $this->_view->certificate = $certificate;
            $this->_view->package     = $package;
            $this->_view->payway      = $payway;
            $this->_view->paytype     = $paytype;
            $this->_view->paybank     = $paybank;
            $this->_view->province    = $province;
            // $this->_view->express  = $express;
            $this->_view->goods       = $goods;
            $this->_view->keyword     = $keyword;
            $this->_view->channel     = $channel;
            $this->_view->shop        = $shop;
            $this->_view->spInfo      = $spInfo;
		} else {
			$this->flash( '/push/index/?id='.$id.'&keyword='.$keyword.'&channel='.$channel, '商品信息错误' );
		}
	}
	
	//付款类型
	public function getPayTypeAction() {
		$pid = $this->getPost('pid',0);
		$paytype = Service::getInstance('orders')->getPayType( $pid );
		$this->respon(1,$paytype);
	}
	
	//收款账号
	public function getPayBankAction() {
		$pid = $this->getPost('pid',0);
		$paybank = Service::getInstance('orders')->getPayBank( $pid );
		$this->respon(1,$paybank);
	}
	
	
	//城市列表
	public function cityAction() {
		$province = $this->getPost('province');
		$city = Service::getInstance('shop')->getCity($province);
		if($city) {
			$this->respon(1,$city);
		} else {
			$this->respon(0,'查询失败');
		}
	
	}
	
	//地区列表
	public function areaAction() {
		$city = $this->getPost('city');
		$area = Service::getInstance('shop')->getCity($city);
		if($city) {
			$this->respon(1,$area);
		} else {
			$this->respon(0,'查询失败');
		}
	}
	
	//导出数据
	public function exportAction() {
		/**
		 * 根据get参数获得要导出的页面
		 * update:2016-04-27 10:34  By breakdinner
		 */
		$topLevel=trim($this->getQuery('topLevel'));
		$checkStatus=trim($this->getQuery('checkStatus'));
		$channel=trim($this->getQuery('channel'));

		$excel = new PHPExcel();
		$letter = array('A','B','C','D','E','F','G','H');
		
		//表头数组
		$tableheader = array('SKU编码','商品名称','渠道价格','参数','状态','商品描述','渠道','审核状态');
		
		
		//填充表头信息
		for($i = 0;$i < count($tableheader);$i++) {
			$excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
		}
		
		/**
		 * 从redis中获取当前checkStatus下的商品信息
		 * update:2016-04-27 15:53 By breakdinner
		 */
		$statusId = Service::getInstance('goods')->getStatusId($checkStatus,$channel);
		$redis = json_decode(Red::getInstance()->GET($statusId),TRUE);

		// 如果为空,则不继续执行,报错 无内容可以导出
		if (!$redis) {
			$this->flash("/push/index/?channel={$channel}&topLevel={$topLevel}&showType=list&checkStatus={$checkStatus}",'无内容可导出',2);
			return false;
		}
		$data = Service::getInstance('goods')->getPushGoodsByStatus($redis);

		//填充表格信息
		
		for ($i = 2;$i <= count($data) + 1;$i++) {
		
			$j = 0;
		
			foreach ($data[$i - 2] as $key=>$value) {
		
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
		
				$j++;
		
			}
		}

		$write = new Plugins_PHPExcel_Writer_Excel5($excel);

		header("Pragma: public");
		
		header("Expires: 0");
		
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		
		header("Content-Type:application/force-download");
		
		header("Content-Type:application/vnd.ms-execl");
		
		header("Content-Type:application/octet-stream");
		
		header("Content-Type:application/download");
		
		header('Content-Disposition:attachment;filename="pushGoods.xls"');
		
		header("Content-Transfer-Encoding:binary");
		
		$write->save('php://output');
	}
	//导出数据
	public function exportgAction() {
		$excel = new PHPExcel();
		$letter = array('A','B','C','D','E','F','G');
		
		//表头数组
		$tableheader = array('货号','商品名称','价格','参数','店铺Id','商品描述');
		
		
		//填充表头信息
		for($i = 0;$i < count($tableheader);$i++) {
			$excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
		}
		
		$data = Service::getInstance('goods')->getAllPushGoodsNoC();
	   
		//填充表格信息
		
		for ($i = 2;$i <= count($data) + 1;$i++) {
		
			$j = 0;
		
			foreach ($data[$i - 2] as $key=>$value) {
		
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
		
				$j++;
		
			}
		}

		$write = new Plugins_PHPExcel_Writer_Excel5($excel);

		header("Pragma: public");
		
		header("Expires: 0");
		
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		
		header("Content-Type:application/force-download");
		
		header("Content-Type:application/vnd.ms-execl");
		
		header("Content-Type:application/octet-stream");
		
		header("Content-Type:application/download");
		
		header('Content-Disposition:attachment;filename="Goods.xls"');
		
		header("Content-Transfer-Encoding:binary");
		
		$write->save('php://output');
	}

	
	// 上架价格异常商品
	public function upGoodsAction() {
		if ($this->isPost()) {
			// 渠道商id
			$channel = $this->getPost('channel');
			// 推送商品id
			$id = $this->getPost('id');
			// 根据渠道商id获得渠道负责人id
			$sql = "SELECT devId FROM channel WHERE id={$channel}";
			$devId = $this->db->fetchOne($sql);
			// 获取当前用户roleId
			$nowId = $this->_developer['id'];
			// 如果当前用户id和devId对应，或者当前用户身份是超管或者黄端
			if ($nowId == $devId || in_array('1', $this->_developer['role']) || in_array('6', $this->_developer['role'])) {
				$res = $this->db->update('push', array('status' => 0), 'id=' . $id);
				$this->respon(1, $res);
			} else {
				$this->respon(0, '权限不足');
			}
		}
    }

    //批量上架异常商品
    public function batchUpGoodsAction(){
        if ($this->isPost()) {
            // 渠道商id
            $puids = $this->getPost('puids');
            if( !$puids || !is_array( $puids ) ) $this->respon(0,'参数错误');

            foreach( $puids as $puid ){
                $channel = Service::getInstance('push')->getChannelByPuid( $puid );
                // 根据渠道商id获得渠道负责人id
                $sql = "SELECT devId FROM channel WHERE id={$channel}";
                $devId = $this->db->fetchOne($sql);
                // 获取当前用户roleId
                $nowId = $this->_developer['id'];
                // 如果当前用户id和devId对应，或者当前用户身份是超管或者黄端
                if ($nowId == $devId || in_array('1', $this->_developer['role']) || in_array('6', $this->_developer['role'])) {
                    $res = $this->db->update('push', array('status' => 0), 'id=' . $puid);
                }
            }
            $this->respon(1,'操作成功');
        }
    }

    //app推送消息
    public function redisAppPush( $shopId ){
        $shopName = Service::getInstance("shop")->getShopNameById( $shopId );
        $data = array(
            "sendType"=>2,
            "tag"=>"{$shopId}",
            "massage"=>"您的店铺：".$shopName."有1个新订单",
            "extras"=>array(
                        "shopId"=>$shopId,
                        "shopName"=>$shopName,
                        "msgType"=>"1",
                    )
            );
        $res = $this->redis->lpush( 'app_push' ,  json_encode( $data ) );
        return $res;
    }
    
    //redis存储订单结算申请   md5(account_20160425)
    private function redisAddAccount( $period,$orderId ){
        $accDate = date('Ymd',time()+$period*86400);
        $key = md5('account_'.$accDate);
        $res = $this->redis->SADD($key,$orderId);
        //606b3ffef8df5de1564a7bc30e35a0a3
        return $res;
    }
    
    //获取客户地址
    public function getAddressAction(){
        $tel = $this->getPost('tel');
        $customer_id = Service::getInstance('address')->getCustomerId($tel);
        if($customer_id){
            $addressList = Service::getInstance('address')->getAddressByCustomerId($customer_id);
            foreach($addressList as $k=>&$row){  
                $province = Service::getInstance('shop')->getProCityAreaName($row['province']);
                $city = Service::getInstance('shop')->getProCityAreaName($row['city']);
                $area = Service::getInstance('shop')->getProCityAreaName($row['area']);
                $row['province'] = $province['name'];
                $row['city'] = $city['name'];
                $row['area'] = $area['name'];
            }
//          echo '<pre>';
//          print_r($addressList);
//          exit;
            if($addressList){
                $this->respon(1,$addressList);
            }else{
                $this->respon(0,'该客户的地址不存在，请添加');
            }
        }else{
            $this->respon(0,'没有找到该客户的信息');
        }
        
    }
    
    //删除收货地址
    public function delAddressAction(){
        $id = $this->getPost('addressId');
        $id = substr($id,8);
        $res = Service::getInstance('address')->del($id);
        if($res){
            $this->respon(1,'删除地址成功');
        }else{
            $this->respon(0,'删除地址失败');
        }
    }

    private function _arrayToJson($array)
    {
        $newArray =array();
        foreach ($array as $v) {
            $newArray[] =json_encode($v);   
        }
        return $newArray;
    }
	
}