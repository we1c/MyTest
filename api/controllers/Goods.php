<?php
class GoodsController extends BaseController {
    private $_action;
    private $_controller;
    public function init() {	
        parent::init();
        $white = array('goodsdetail','goodshtmlinfo','getnextimg'); //白名单
        /*if ( !in_array( $this->getRequest()->getActionName(),$white) ) {
            if ( ! (bool)Yaf_Registry::get("isLogin") )
            {
                $this->respon( 0 , "请重新登录" );
            }
        }*/
        $this->_action = $this->getRequest()->getActionName();
        $this->_controller = $this->getRequest()->getControllerName();
    }
    
    //商品列表
    public function goodslistAction() {
        $uid = Yaf_Registry::get('uid');
        $status = $this->getPost('status',0);
        $pageNum = $this->getPost('pageNum') ? $this->getPost('pageNum') : 10;
        $shopId = $this->getPost('shopId')?$this->getPost('shopId') : 0;
        $lastId = $this->getPost('lastId') ? $this->getPost('lastId') : '';
        $list = Service::getInstance('goods')->goodsList($uid,intval($status),intval($lastId),intval($shopId),intval($pageNum));
        $this->respon(1,$list);
    }

    public function goodsList_v2Action(){

        $param['shopId'] = intval( $this->getPost('shopId',0) );
        if( !$param['shopId'] ) $this->respon(0,'参数有误');
        $param['category2'] = intval( $this->getPost('category2',0) );
        $param['status'] = intval( $this->getPost('status',0) );
        $param['page'] = intval( $this->getPost('page',1) );
        $param['perpage'] = intval( $this->getPost('pageNum',10) );
        $param['changeNum'] = intval( $this->getPost('changeNum',0) );
        $list = Service::getInstance('goods')->apiGoodsList( $param );
        $this->respon(1,$list);
    }
    
    //下架商品列表(取消)
    public function downlistAction() {
        $uid = Yaf_Registry::get('uid');
        $status = 2;
        $list = Service::getInstance('goods')->goodsList($uid,$status);
        $this->respon(1,$list);
    }
    
    //搜索商品列表
    public function searchAction() {
        $search = $this->getPost('search','');
        $goodsId = $this->getPost('goodsId','');
        $shopId = $this->getPost('shopId');
        $status = $this->getPost('status','0');
        $uid = Yaf_Registry::get('uid');
        $list = Service::getInstance('goods')->search($uid,$search,$shopId,$status,$goodsId);
        $this->respon(1,$list);
    }


    //搜索商品列表
    public function search_v2Action() {
        $search = $this->getPost('search','');
        $goodsId = $this->getPost('goodsId');
        $shopId = $this->getPost('shopId');
        if ( !$shopId ) $this->respon( 0,"数据异常" );
        $status = $this->getPost('status','0');
        $uid = Yaf_Registry::get('uid');
        $list = Service::getInstance('goods')->search_v2($uid,$search,$shopId,$status,$goodsId);
        $this->respon(1,$list);
    }
    
    //获得商品详情
    public function goodsinfoAction() {
        $id = $this->getPost('goodsId');
        if ( !intval($id) ) $this->respon(0,'参数异常');
        $res = Service::getInstance('goods')->getGoodsInfoById($id , 'api');
        $this->respon(1,$res);
    }
    //获得商品详情
    public function goodsdetailAction() {
        $id = $this->getQuery('goodsId');
        $goods = Service::getInstance('goods')->getGoodsInfoById($id ,'api');
        $this->_view->info = $goods;
    }
    //获得商品详情H5
    public function goodshtmlinfoAction() {
       
        $id = $this->getQuery('id');
        //if ( !intval($id) ) return ;
        $res = Service::getInstance('goods')->getGoodsInfoById($id ,'api');
        $this->_view->info = $res;
    }
    //上传商品
    public function uploadAction() {
        if( $this->isPost() ){
            $goodArr = array();
            $uid = Yaf_Registry::get('uid');
            $shopId = $this->getPost('shopId');
            $shop = Service::getInstance('shop')->getShopinfo( $shopId);
            $goodArr['isChannel'] = intval($this->getPost('isChannel'));
            if (!$this->getPost('goodsNo')) {
                $goodArr['goodsNo'] = Service::getInstance('shop')->getGoodsNo($this->getPost('shopId'));
            }else{
                $goodArr['goodsNo'] = $this->getPost('goodsNo');
            }
            if (!$this->getPost('cost')) {
                $goodArr['cost'] = $this->getPost('purchPrice');
            }else {
                $goodArr['cost'] = $this->getPost('cost');
            }
            $goodArr['purchPrice'] = $this->getPost('purchPrice');
            $goodArr['goodsStock'] = $this->getPost('goodsStock');
            $goodArr['name'] = trim($this->getPost('name'));
            $goodArr['shopId'] = intval($this->getPost('shopId'))?intval($this->getPost('shopId')):Service::getInstance('goods')->getMeshopId($uid);
            $goodArr['code'] = Service::getInstance('goods')->getGoodsCode($goodArr['shopId']);
            $goodArr['price'] = intval($this->getPost('price')) ? $this->getPost('price') : round($goodArr['purchPrice'] * $shop['mtimes']);
            $goodArr['category1'] = trim($this->getPost('category1'));
            $goodArr['category2'] = trim($this->getPost('category2'));
            $goodArr['category3'] = trim($this->getPost('category3'));
//             $imageS = explode(',',$this->getPost('image'));
            $goodArr['fromWhere'] = 1;
            $goodArr['uploader'] = $goodArr['shopId'];
            $goodArr['createTime'] = time();
            $goodArr['intro'] = htmlspecialchars($this->getPost('intro'));
            $goodArr['showPrice'] = trim($this->getPost('showPrice'));
            $goodArr['groups'] = trim($this->getPost('groups'));
            $goodArr['platform'] = 1;
            $param = trim($this->getPost('status')) == 1? 1 : 3;
            $goodArr['status'] = $param;
            if( !$goodArr['code'] ) {
                $this->respon(0, '店铺信息错误');
            }
            if(!$goodArr['name']) {
                $this->respon(0,'请填写商品名称');
            }
            // if(!$goodArr['intro']) {
            //     $this->respon(0,'请填写商品描述');
            // }
            // if(!$goodArr['price']) {
            //     $this->respon(0,'请填写商品价格');
            // }
            $goodArr['attribute'] = $this->getPost('attribute');
            $goodId = Service::getInstance('goods')->add($goodArr);
            Service::getInstance('shop')->addNumday($goodArr['shopId']);
            unset($goodArr);
            if ( $goodId ) {
                $count = count( $_FILES );
                $imageS = '';
                for ( $i = 0; $i < $count; $i++ ) {
                    if(!$_FILES['file'.$i]['error'])
                    {
                        $avatar = $_FILES['file'.$i]['tmp_name'];
                        $hash = md5($avatar);
                        $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                        $original = Util::getDir( $dir ,$hash ) . $hash . "_image.jpg";
                        if ( move_uploaded_file( $avatar, $original ) ) {
                            $imageS[] = $hash;
                            $size = array('100x100','800x800');
                            if( $i == 0 ) $size[] = '400x400';
                            Service::getInstance('superadmin')->makeThumbFac( $original,$size );
                        } else {
                            continue;
                        }
                    }
                }
                if ( $imageS ) {
                    $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
                    foreach ($imageS as $k=>$v){
                        $temp = intval($k+1);
                        $sql .="('{$goodId}', '{$v}', $temp ),";
                    }
                    $sql = substr($sql, 0,-1);
                    Service::getInstance('goods')->addgoods_images($sql);
                }
                
                $userLog = array(
                    'uid'=>$uid,
                    'gid'=>$goodId,
                    'action'=>$this->_action,
                    'controller'=>$this->_controller,
                    'name'=>'添加商品'
                );
                Service::getInstance('blog')->add_user_log( $userLog );
                Service::getInstance("shop")->addNumday($shopId);
                $this->respon(1,'添加成功');
            } else {
                $this->respon( 0, '添加失败' );
            }
        }
    }
    //编辑商品
    public function editAction() {
        if( $this->isPost() ){
            $goodArr = array();
            $shopId = $this->getPost('shopId');
            $shop = Service::getInstance('shop')->getShopinfo( $shopId);
            $goodArr['isChannel'] = intval($this->getPost('isChannel'));
            if (!$this->getPost('goodsNo')) {
                $goodArr['goodsNo'] = Service::getInstance('shop')->getGoodsNo($this->getPost('shopId'));
            }else{
                $goodArr['goodsNo'] = $this->getPost('goodsNo');
            }
            if (!$this->getPost('cost')) {
                $goodArr['cost'] = $this->getPost('purchPrice');
            }else {
                $goodArr['cost'] = $this->getPost('cost');
            }
            $goodArr['purchPrice'] = $this->getPost('purchPrice');
            $goodArr['goodsStock'] = $this->getPost('goodsStock');
            $goodsId = intval($this->getPost('id'));
            $goodArr['name'] = trim($this->getPost('name'));
            $goodArr['price'] = intval($this->getPost('price')) ? $this->getPost('price') : round($goodArr['purchPrice'] * $shop['mtimes']);
            $goodArr['category1'] = trim($this->getPost('category1'));
            $goodArr['category2'] = trim($this->getPost('category2'));
            $goodArr['category3'] = trim($this->getPost('category3'));
            if ($this->getPost('oldImage')) {
                $imageS = json_decode($this->getPost('oldImage'),true);
            }else{
                $imageS = explode(',',$this->getPost('image'));
            }
            $newIndex = explode(',',$this->getPost('newIndex'));
            $goodArr['editor'] = 's-'.$shopId;
            $goodArr['updateTime'] = time();
            $goodArr['intro'] = htmlspecialchars($this->getPost('intro'));
            $goodArr['showPrice'] = trim($this->getPost('showPrice'));
            $goodArr['groups'] = trim($this->getPost('groups'));
            // $param = trim($this->getPost('status')) == 1? 1 : 3;
            // $goodArr['status'] = $param;
            if(!$goodArr['name']) {
                $this->respon(0,'请填写商品名称');
            }
            // if(!$goodArr['intro']) {
            //     $this->respon(0,'请填写商品描述');
            // }
            // if(!$goodArr['price']) {
            //     $this->respon(0,'请填写商品价格');
            // }
            $goodArr['attribute'] = $this->getPost('attribute');
            $res = Service::getInstance('goods')->edit($goodArr,$goodsId);
            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
            unset($goodArr);
            if($res >= 0) {
                $count = count( $_FILES );
                for ( $i = 0; $i < $count; $i++ ) {
                    if(!$_FILES['file'.$i]['error']) {
                        $avatar = $_FILES['file'.$i]['tmp_name'];
                        $hash = md5($avatar);
                        
                        $original = Util::getDir( $dir ,$hash ) . $hash . "_image.jpg";
                        if ( move_uploaded_file( $avatar, $original ) ) {
                            if ($this->getPost('oldImage')) {
                                $imageS[$newIndex[$i]] = $hash;
                            }else{
                                $imageS[] = $hash;
                            }
                            $size = array('100x100','800x800');
                            Service::getInstance('superadmin')->makeThumbFac( $original,$size );
                        } else {
                            continue;
                        }
                    }
                }
                foreach ( $imageS as $key=>$val ) {
                    if ( !trim( $val ) ) {
                        unset( $imageS[$key] );
                    }
                }
                if( !empty( $imageS ) ) {
                    ksort( $imageS );
                    list( $tmpThumb ) = $imageS;
                    if( !empty($tmpThumb) ){
                        $original = Util::getDir( $dir,$tmpThumb ).$tmpThumb.'_image.jpg';
                        $oneThumb = str_replace('_image.','_thumb_400x400.',$original);
                        if( !file_exists( $oneThumb ) ){
                            Service::getInstance('superadmin')->makeThumbFac( $original ,array( '400x400' ) );
                        }
                    }
                    $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
                    foreach ( $imageS as $k=>$v ){
                        if ( trim( $v ) ) {
                            $temp = intval($k+1);
                            $sql .= "('{$goodsId}', '{$v}', $temp ),";
                        }
                    }
                    $sql = substr($sql, 0,-1);
                    Service::getInstance('goods')->editgoods_images($sql,$goodsId);
                } else {
                    Service::getInstance('goods')->delgoods_images($goodsId);
                }
                $userLog = array(
                    'uid'=>Yaf_Registry::get('uid'),
                    'gid'=>$goodsId,
                    'action'=>$this->_action,
                    'controller'=>$this->_controller,
                    'name'=>'编辑商品'
                );
                Service::getInstance('blog')->add_user_log( $userLog );
                $this->respon(1,'编辑成功');
            } else {
                $this->respon(0,'编辑失败');
            }
        }
    }
    //上传商品图片
    public function uploadgoodsimgAction() { 
        if(!$_FILES['file']['error'])
        {
            $avatar = $_FILES['file']['tmp_name'];
            $hash = md5($avatar);
            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
            if ( move_uploaded_file( $avatar, Util::getDir( $dir ,$hash ) . $hash . "_image.jpg") ) {
                $Res = array(
                    'imgurl'  => Service::getInstance('goods')->getAvata( $hash ),
                    'path'=> $hash
                );
                $this->respon( 1 , $Res );
            } else {
                $this->respon( 0 , "上传失败" );
            }
        }
        $this->respon( 0 , $_FILES['file']['error']."请选择您要上传的图片!" );
    }
    
    //上传商品图片
    public function uploadgoodsimgsAction() { 
        if($_FILES['file']) {
            foreach ($_FILES['file']['error'] as $k=>$v) {
                if(!$v) {
                    $avatar = $_FILES['file']['tmp_name'][$k];
                    $hash = md5($avatar);
                    $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                    $data = array();
                    if ( move_uploaded_file( $avatar, Util::getDir( $dir ,$hash ) . $hash . "_image.jpg") ) {
                        $Res = array(
                            'status'=>1,
                            'imgurl'  => Service::getInstance('goods')->getAvata( $hash ),
                            'path'=> $hash
                        );
                    } else {
                        $Res = array(
                            'status'=>0,
                            'imgurl'  => '',
                            'path'=> ''
                        );
                    }
                    $data[] = $Res;
                }
            }
            $this->respon(1,$data);
        }
        $this->respon( 0 , "请选择您要上传的图片!" );
    }
    
    //删除商品
    public function delAction() {
        $goodsIds = $this->getPost('goodsId');
        if(!$goodsIds) $this->respon(0,'数据异常');

        //判断是否可以删除
        $check = Service::getInstance('goods')->checkDel($goodsIds);
        if ( $check ) {
            $this->respon(0,'该商品已在平台销售，请与工作人员进行沟通');      
        }
        $res = Service::getInstance('goods')->del($goodsIds);
        if($res > 0) {
            $ids = explode(',', $goodsIds);
            foreach ($ids as $v) {
                $userLog = array(
                    'uid'=>Yaf_Registry::get('uid'),
                    'gid'=>$v,
                    'action'=>$this->_action,
                    'controller'=>$this->_controller,
                    'name'=>'删除商品'
                );
                Service::getInstance('blog')->add_user_log( $userLog );
            }
            $this->respon(1,'删除成功');
        } else {
            $this->respon(0,'删除失败');
        }
    }
    
    //上架商品
    public function groundAction() {
        $this->respon(0,"上架功能已取消");
        /*if($this->isPost()) {
            $id = $this->getPost('goodsId');
            $res = Service::getInstance('goods')->groundGoods($id);
            if($res) {
                $ids = explode(',', $id);
                foreach ($ids as $v) {
                    $userLog = array(
                        'uid'=>Yaf_Registry::get('uid'),
                        'gid'=>$v,
                        'action'=>$this->_action,
                        'controller'=>$this->_controller,
                        'name'=>'上架商品'
                    );
                    Service::getInstance('blog')->add_user_log( $userLog );
                }
                $this->respon(1,'操作成功');
            } else {
                $this->respon(0,'操作失败');
            }
        }
       return false;*/
    }
    
    //下架商品
    public function downgoodsAction() {
        $this->respon(0,"下架功能已取消");
        /*if($this->isPost()) {
            $data = $this->getPost('status');
            $id = $this->getPost('goodsId');
            if(!$id) $this->respon(0,'数据异常');
            $arr = array();
            $arr['status'] = $data;
            $arr['remark'] = '';
            if($data == 4) {
                $remark = $this->getPost('remark');
                if(!$remark) {
                    $this->respon(0,'请填写下架原因');
                }
                $arr['remark'] = $remark;
            
            }
            $goodsid = $id;
            Service::getInstance('goods')->downGoods($arr,$goodsid);
            $ids = explode(',', $id);
            foreach ($ids as $v) {
                $userLog = array(
                    'uid'=>Yaf_Registry::get('uid'),
                    'gid'=>$v,
                    'action'=>$this->_action,
                    'controller'=>$this->_controller,
                    'name'=>'下架商品'
                );
                Service::getInstance('blog')->add_user_log( $userLog );
            }
            $this->respon(1,'下架成功');
        }
        return false;*/
    }
    
    //售出
    public function sellAction() {
        if ($this->isPost()) {
            $goodsId   = $this->getPost('goodsId');
            $number    = $this->getPost('number');
            $realPrice = $this->getPost('realPrice',0);
            if (!$goodsId) $this->respon(0,'数据异常');
            $goodsInfo = Service::getInstance('goods')->getGoodsInfoById( $goodsId );
            $price     = $realPrice*$number;

            $data = array(
                        "orderCode"  =>Service::getInstance('orders')->getOrderCode(1,2),
                        "isPay"      =>1,
                        "isDeliver"  =>2,
                        "isTake"     =>2,
                        "shopId"     =>$goodsInfo['shopId'],
                        "orderType"  =>1,
                        "goodsId"    =>$goodsId,
                        "number"     =>$number,
                        "price"      =>$price,
                        "createTime" =>time(),
                        "payTime"    =>time(),
                        "sellType"   =>1,
                    );
            $orderId = Service::getInstance("orders")->add($data);
            
            if ( $orderId ) {
                //减少库存
                Service::getInstance('goods')->sellGoods($goodsId,$number);

                //添加订单商品
                $orderGoods = array(
                    'order_id'        => $orderId,
                    'goods_id'        => $goodsId,
                    'shop_id'         => $goodsInfo['shopId'],
                    'customer_id'     => 0,
                    'goods_name'      => $goodsInfo['name'],
                    'goods_image'     => $goodsInfo['goods_image'],
                    'goods_price'     => $goodsInfo['price'],
                    'goods_number'    => $number,
                    'goods_pay_price' => $price,
                );
                $addOrderGoods = Service::getInstance('orders')->addOrderGoods( $orderGoods );

            }

            //添加操作日志
            $userLog = array(
                    'uid'        =>Yaf_Registry::get('uid'),
                    'gid'        =>$goodsId,
                    'action'     =>$this->_action,
                    'controller' =>$this->_controller,
                    'name'       =>'售出商品',
                    'realPrice'  =>$realPrice,
                    'number'     =>$number
                );
            Service::getInstance('blog')->add_user_log( $userLog );
            $this->respon(1,'操作成功');
            
        }
        return false;
    }

    //补货
    public function addStockAction() {
        if ($this->isPost()) {
            $goodsId = $this->getPost('goodsId');
            $number = $this->getPost('number');
            if (!$goodsId) $this->respon(0,'数据异常');
            Service::getInstance('goods')->addStock($goodsId,$number);
            $userLog = array(
                    'uid'=>Yaf_Registry::get('uid'),
                    'gid'=>$goodsId,
                    'action'=>$this->_action,
                    'controller'=>$this->_controller,
                    'name'=>'商品补货',
                    'number'=>$number
                );
            Service::getInstance('blog')->add_user_log( $userLog );
            $this->respon(1,'操作成功');
        }
        return false;
    }
    //批量补货
    public function batchAddStockAction() {
        if ($this->isPost()) {
            $batch_json = $this->getPost('batchAdd');
            if($batch_json){
                $batch_arr = json_decode($batch_json,true);
                foreach($batch_arr as $v){
                    $goodsId = $v['goodsId'];
                    $number = $v['addCount'];
                    if (!$goodsId) $this->respon(0,'数据异常');
                    Service::getInstance('goods')->addStock($goodsId,$number);
                    $userLog = array(
                            'uid'=>Yaf_Registry::get('uid'),
                            'gid'=>$goodsId,
                            'action'=>$this->_action,
                            'controller'=>$this->_controller,
                            'name'=>'商品批量补货',
                            'number'=>$number
                        );
                    Service::getInstance('blog')->add_user_log( $userLog );               
                }
                $this->respon(1,'操作成功');
            }else{
                $this->respon(0,'操作失败');
            }           
        }
        return false;
    }


    //分组
    public function getGroupsAction() {
        $id = $this->getPost('shopId');
        $list = Service::getInstance('goods')->getGroupsByShopId($id);
        $this->respon(1, $list);
    }

    public function getnextimgAction(){
        $id=$this->getPost('id');
        $offset=$this->getPost('offset');
        $one_imgurl=Service::getInstance('goods')->getGoodsImgHashByGoodsId($id,$offset);

        $this->respon(1,$one_imgurl);
    }
    
    //分享记录
    public function shareGoodsAction() {
        $gid = $this->getPost('goodsId',0);
        if (!$gid) $this->respon(0, '参数异常');
        $channel = $this->getPost('shareWay');
        $userLog = array(
            'uid'=>Yaf_Registry::get('uid'),
            'gid'=>$gid,
            'action'=>$this->_action,
            'controller'=>$this->_controller,
            'name'=>"分享商品到{$channel}"
        );
        if (Service::getInstance('blog')->add_user_log( $userLog )) {
            $this->respon(1, '成功');
        } else {
            $this->respon(0, '失败');
        }
    }

    //获取今日上传商品列表
    public function todayGoodsAction(){
        $shopId = $this->getPost('shopId');
        if ( !$shopId ) $this->respon(0,'参数异常');
        $today = strtotime(date('Y-m-d'));
        $goodsInfos = Service::getInstance('goods')->getGoodsListByShop($shopId,$today);
        if ($goodsInfos !== false) {
            $data['goodsInfos'] = $goodsInfos;
            $data['time']       = $today;
            $this->respon(1,$data);
        }else{
            $this->respon(0,'失败');
        }
    }

    //获取等待完善商品列表
    public function waitCompleteAction(){
        $shopId  = $this->getPost('shopId');
        if ( !$shopId ) $this->respon(0,'参数异常');
        $page    = $this->getPost('page',1);
        $perpage = $this->getPost('pageNum',20);
        $goods   = Service::getInstance('goods')->getNoIntroGoods( $shopId, $page, $perpage );
        $goods   = array_values($goods);
        $this->respon(1,$goods);
    }


}