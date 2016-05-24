<?php

class SuperadminController extends BaseController{
    private $imgObj;
	public function init(){
		parent::init();
	}

	public function updateMenuSortAction(){
		$position = array(
    		'supplier-index'	=>1,
    		'distributor-index'	=>2,
    		'shop-index'		=>3,
    		'category-index'	=>4,
    		'goods-index'		=>5,
    		'shopmarket-index'	=>6,
    		'platf-index'		=>7,
    		'checkone-index'	=>8,
    		'checktwo-index'	=>9,
    		'checkpush-index'	=>10,
    		'push-index'		=>11,
    		'order-index'		=>12,
    		'deliver-index'		=>13,
    		'account-index'		=>14,
            'audit-index'       =>15,
    		'role-index'		=>16,
    		'admin-index'		=>17,
    		'blog-index'		=>18,
    		'version-index'		=>19,
    		'import-index'		=>20,
    		'developer-index'	=>21
    		);
    	foreach( $position as $ca => $sort ){
    		$ca = explode('-',$ca);
    		$c = $ca[0];
    		$a = $ca[1];
    		$sql = " UPDATE menu SET sort = '{$sort}' WHERE con = '{$c}' AND act = '{$a}' ";
    		$this->db->query( $sql );
    	}
    	exit("<h1>更新菜单栏，操作成功！</h1>");
	}

    private $imgInfo = array( );
    public function makeImageThumbByDirAction(){
        ini_set('max_execution_time', '3600');
        ini_set('memory_limit', '512M');
        //初始化信息
        $this->imgInfo = array(
            'path'=>ROOT_PATH.'/api/public/image',
            'width'=>800,
            'height'=>800,
        );
        $this->imgObj = new Image();

        $imgs = array();
        $this->_readDir( $this->imgInfo['path'] , $imgs );
        echo "<pre>";
        var_dump( $imgs );
        exit;
    }

    private function _readDir( $path , &$imgs ){

        $files = opendir( $path );

        while( ($file = readdir( $files )) !== false ){
            $path_now = $path.'/'.$file;
            if( $file == '.' || $file == '..' ){

            }elseif( is_dir( $path_now ) ){
                $this->_readDir( $path_now , $imgs );
            }else{
                /*if( preg_match( '/_image/', $path_now ) ){

                    $thumb = str_replace( '_image', '_thumb', $path_now );

                    if( !file_exists( $thumb ) ){

                        $this->imgObj->open( $path_now );
                        $size = $this->imgObj->size();
                        //计算剪切的原图宽度和高度，以及剪切的位置坐标
                        if( $size[0] > $size[1] ){
                            $w = $h = $size[1];
                        }else{
                            $w = $h = $size[0];
                        }
                        $x = ( $size[0] - $w )/2;
                        $y = ( $size[1] - $h )/2;

                        $this->imgObj->crop( $w, $h, $x, $y, $this->imgInfo['width'],$this->imgInfo['height'] );

                        $this->imgObj->save( $thumb );
                    }
                }*/
                $imgs[] = $file;
                echo $file."<br>";
                if( preg_match('/_thumb/',$path_now) ){
                    unlink($path_now);
                }

            }
        }

        closedir( $files );

    }
    //
    private $badImg = array();
    public function makeImageThumbBySqlAction(){
        ini_set('max_execution_time', '3600');
        ini_set('memory_limit', '512M');
        $num = $this->getQuery( 'num','' );
        $limit = $this->getQuery( 'limit','' );
        $gid = $this->getQuery( 'gid','' );
        $sort = $this->getQuery('sort','');
        $where = ' WHERE id <> 0 ';
        $tmp = '';
        if( $gid != '' ){
            $where = ' AND goodsId = '.$gid;
        }
        if( $sort != '' ){
            $where .= " AND sort IN( {$sort} ) ";
        }
        if( $num > 0 && $limit > 0 ){
            $offset = ($num-1)*$limit;
            $tmp = " LIMIT {$offset},{$limit} ";
        }
        
        $sql = " SELECT id,goodsId,image,sort FROM goods_image {$where} {$tmp} ";
        $result = $this->db->fetchAll( $sql );


        $this->imgObj = new Image();

        $not_exists = array();
        $illegal = array();
        $fail = array();
        $success = array();
        foreach( $result as $k => $v ){
            //默认缩略图尺寸
            $size = array( '800x800','100x100' );
            $hash = $v['image'];
            $file = ROOT_PATH.'/api/public/image/'.substr( $hash, 0 , 2 ).'/'.substr( $hash , 2 , 2 ).'/'.$hash.'_image.jpg';

            if( $v['sort'] == 1 ){
                $size[] = '400x400';
            }

            $res = $this->makeThumbFac( $file , $size ,$v['goodsId'] );

            if( $res == '1' ){
                $illegal[] = $v;
            }elseif( $res == '2' ){
                $fail[] = $v;
            }elseif( $res == '3' ){
                $not_exists[] = $v;
            }else{
                $success[] = $v;
            }
        }
        
        echo "<pre>";
/*        if( !empty($this->badImg) ){
            $goodsIds = implode( ',',$this->badImg );
            $sql = " SELECT id,code FROM goods WHERE id IN ( {$goodsIds} )";
            $res = $this->db->fetchAll( $sql );
            $content = '';
            foreach( $res as $k => $v ){
                $content .= $v['id'].' \t '.$v['code']."<br>";
            }
            file_put_contents( ROOT_PATH.'/badImg.txt',$content );
        }*/
        if( !empty( $success ) ){
            echo "成功的为：<br>";
            var_dump( $success );
        }
        if( !empty( $illegal ) ){
            echo "非法图片为：<br>";
            var_dump( $illegal );
        }
        if( !empty( $fail ) ){
            echo "加载失败的为：<br>";
            var_dump( $fail );
        }
        if( !empty( $not_exists ) ){
            echo "不存在的图片有<br>";
            var_dump( $not_exists );
        }
        exit;
    }

    public function makeThumbAction(){
        $sql = " SELECT count(*) FROM goods_image ";
        $num = $this->db->fetchOne( $sql );
        $this->_view->num = $num;
    }

    public function makeImageThumbByJsAction(){
        ini_set('max_execution_time', '3600');
        ini_set('memory_limit', '512M');
        $num = $this->getQuery( 'num',1 );
        $offset = ($num-1)*100;
        $sql = " SELECT id,goodsId,image,sort FROM goods_image LIMIT {$offset},200 ";
        $result = $this->db->fetchAll( $sql );

        $this->imgObj = new Image();

        foreach( $result as $k => $v ){
            //默认缩略图尺寸
            $size = array( '800x800','100x100' );
            $hash = $v['image'];
            $file = ROOT_PATH.'/api/public/image/'.substr( $hash, 0 , 2 ).'/'.substr( $hash , 2 , 2 ).'/'.$hash.'_image.jpg';
            if( $v['sort'] == 1 ){
                $size[] = '400x400';
            }

            $res = $this->makeThumbFac( $file , $size ,$v['goodsId'] );
            if( !$res ) continue;
        }

        $this->respon( 1 ,'success' );
        exit;
    }

    private function makeThumbFac( $file , $size = array() ,$info = '' ){
        if( empty($size) ) $size = array( '100x100','800x800' );

        if( file_exists( $file ) ){

            $flag = $this->imgObj->open( $file );

            if( $flag === '1' || $flag === '2' ) return $flag;

            $wh = $this->imgObj->size();
            if( $wh[0] < 800 || $wh[1] < 800 ){
                $this->badImg[] = $info;
            }
            if( $wh[0] > $wh[1] ){
                $w = $h = $wh[1];
            }else{
                $w = $h = $wh[0];
            }
            $x = ( $wh[0] - $w )/2;
            $y = ( $wh[1] - $h )/2;

            $original = explode( '_image' ,$file );
            foreach( $size as $k => $v ){
                $thumb = implode( '_thumb_'.$v , $original );
                if( !file_exists( $thumb ) ){
                    //获取缩略的尺寸数组array('800','800');0=>width;1=>eight;
                    $mm = explode( 'x',$v );
                    $this->imgObj->crop( $w ,$h ,$x ,$y ,$mm[0] ,$mm[1] );
                    $this->imgObj->save( $thumb );
                    $this->imgObj->open( $file );
                }
            }
        }else{
            return '3';
        }

    }

/*
    public function insertShopGoodsFromGoodsAction(){

        $data = $this->db->fetchAll(" SELECT id FROM goods WHERE fromWhere = 1 AND platform = 2 AND checkResult != 0 ");
        $fail = array();
        foreach( $data as $k => $v ){
            $gid = $v['id'];
            $exist = $this->db->fetchOne(" SELECT count(*) FROM shop_push WHERE goods_id = ".$gid );
            if( $exist == '0' ){
                $insert = array(
                    'goods_id'=>$gid,
                    'push_time'=>time(),
                    'command_id'=>9,
                    'command_time'=>time(),
                    'exe_id'=>9,
                    's_time'=>time(),
                    'e_time'=>time(),
                    'follow'=>3
                    );
                $res = $this->db->insert( 'shop_push' , $insert );
                if( !$res ) $fail[] = $insert;
            }
        }
        if( empty($fail) ){
            exit( '<h1>操作成功</h1>' );
        }else{
            echo "<pre>失败的数据为:";
            var_dump($fail);
            exit();
        }
    }

    public function insertGoodsCheckFromGoodsAction(){

        $data = $this->db->fetchAll(" SELECT id FROM goods WHERE fromWhere = 1 AND platform = 2 AND checkResult = 0 ");
        $fail = array();
        foreach( $data as $k => $v ){

            $insert = array(
                'goods_id'=>$v['id'],
                'push_time'=>time(),
                'command_id'=>9,
                'command_time'=>time(),
                'exe_id'=>9,
                's_time'=>time(),
                'e_time'=>time(),
                'follow'=>1
                );
            $res_sp = $this->db->insert( 'shop_push' , $insert );
            if( !$res_sp ) $fail['shop_push'] = $insert;

            $insert = array(
                'goodsId'=>$v['id'],
                'createTime'=>time(),
                'status'=>0,
                'fromWhere'=>2
                );
            $res_gc = $this->db->insert( 'goods_check' , $insert );
            if( !$res_gc ) $fail['goods_check'] = $insert;

            $update = array( 'platform'=>1 );
            $res_g = $this->db->update( 'goods' , $update , 'id = '.$v['id'] );
            if( !$res_g ) $fail['goods'] = $update;
        }

        if( empty($fail) ){
            exit( '<h1>操作成功</h1>' );
        }else{
            echo "<pre>失败的数据为:";
            var_dump($fail);
            exit();
        }
    }

    public function moveUpdateTimeToTmpAction(){
        $data = $this->db->fetchAll( " SELECT id,updateTime FROM goods " );
        $fail = array();
        foreach( $data as $k => $v ){
            $time = strtotime($v['updateTime']);
            $update = array(
                'tmp'=>$time
                );
            $res = $this->db->update( 'goods',$update,'id = '.$v['id'] );
            if( !$res ){
                $fail[] = $v['id'];
            }
        }
        if( empty($fail) ){
            exit( '<h1>操作成功</h1>' );
        }else{
            echo "<pre>失败的数据为:";
            var_dump($fail);
            exit();
        }
    }

    public function updateGoodsEditorUpdateTimeAction(){
        $data = $this->db->fetchAll( " SELECT id,tmp FROM goods " );
        $fail = array();
        foreach( $data as $k => $v ){
            $time = $v['tmp'] == true ? $v['tmp'] : time();
            $update = array(
                'editor'=>'d-9',
                'updateTime'=>$time
                );
            $res = $this->db->update( 'goods',$update,'id = '.$v['id'] );
            if( !$res ){
                $fail[] = $v['id'];
            }
        }
        if( empty($fail) ){
            exit( '<h1>操作成功</h1>' );
        }else{
            echo "<pre>失败的数据为:";
            var_dump($fail);
            exit();
        }
    }

    //费用表拆分倒数据
    public function updateOredersToOrdersPriceAction(){
        $truncate = $this->db->query("Truncate Table orders_price");
        $sql = "SELECT id AS orderId,freight AS price_freight FROM orders ORDER BY id ASC";
        $data = $this->db->fetchAll($sql);
        for ($j=0; $j < count($data) ; $j++) { 
            $this->db->insert('orders_price',array('orderId'=>$data[$j]['orderId'],'price_freight'=>$data[$j]['price_freight']));
        }

            $sql2 = "SELECT orderId,real_freight FROM `orders_express` WHERE real_freight <> 0; ";
            $data2 = $this->db->fetchAll($sql2);
            for ($i=0; $i < count($data2); $i++) { 
                $exist = $this->db->fetchOne("SELECT * FROM orders_price WHERE orderId = ".$data2[$i]['orderId']);

                if ($exist) {
                    $this->db->update('orders_price',array('real_freight'=>$data2[$i]['real_freight']),'orderId = '.$data2[$i]['orderId']);  
                }else{
                    $this->db->insert('orders_price',array('orderId'=>$data2[$i]['orderId'],'real_freight'=>$data2[$i]['real_freight']));
                }
            }
            $result = $this->db->fetchAll("SELECT * FROM orders_price");
            echo "<pre>";
            print_r($result);
        
        exit;
    }*/
    public function createNodesAction(){
        $nodes = array(
            array(
                'name'=>'平台模块',
                'con'=>'module',
                'act'=>'pingtai',
                'pid'=>'',
                'sort'=>'1',
                'level'=>'1',
                'isMenu'=>'1',
                'status'=>'1',
                'child'=>array(
                    array(
                        'name'=>'属性管理',
                        'con'=>'attribute',
                        'act'=>'index',
                        'pid'=>'',
                        'sort'=>'1',
                        'level'=>'2',
                        'isMenu'=>'1',
                        'status'=>'1',
                        'child'=>array(
                            array('name'=>'属性列表','con'=>'attribute','act'=>'index','level'=>'3','sort'=>'1'),
                            array('name'=>'添加属性','con'=>'attribute','act'=>'add','level'=>'3','sort'=>'1'),
                            array('name'=>'编辑属性','con'=>'attribute','act'=>'edit','level'=>'3','sort'=>'1'),
                            array('name'=>'删除编辑','con'=>'attribute','act'=>'delAttr','level'=>'3','sort'=>'1'),
                            array('name'=>'获得属性值','con'=>'attribute','act'=>'getAttrChild','level'=>'3','sort'=>'1'),
                            array('name'=>'获得属性分类','con'=>'attribute','act'=>'getCategoryByPid','level'=>'3','sort'=>'1')
                            ),
                        ),
                    ),
                ),
            array(
                'name'=>'系统模块',
                'con'=>'module',
                'act'=>'xitong',
                'pid'=>'',
                'sort'=>'6',
                'level'=>'1',
                'isMenu'=>'1',
                'status'=>'1',
                'child'=>array(
                    array(
                        'name'=>'版本管理',
                        'con'=>'version',
                        'act'=>'index',
                        'pid'=>'6',
                        'sort'=>'5',
                        'level'=>'2',
                        'isMenu'=>'1',
                        'status'=>'1',
                        'child'=>array(
                            array('name'=>'版本列表','con'=>'version','act'=>'index','level'=>'3','sort'=>'1'),
                            array('name'=>'编辑版本','con'=>'version','act'=>'edit666','level'=>'3','sort'=>'2'),
                            array('name'=>'删除版本','con'=>'version','act'=>'del','level'=>'3','sort'=>'3'),
                            array('name'=>'添加版本','con'=>'version','act'=>'add','level'=>'3','sort'=>'4'),
                            ),
                        ),
                    ),
                ),
            );

        $this->_makeNodeTree( $nodes , 0 );
        exit('<h1>执行成功</h1>');
    }

    private function _makeNodeTree( $nodes , $pid = 0 ){
        $final = " INSERT INTO node ( `name`, `con`, `act`, `pid`, `sort`, `level`, `isMenu`, `status` ) VALUES ";
        $flag = false;
        foreach( $nodes as $node ){
            $sql = " SELECT id FROM node WHERE con = '{$node['con']}' AND act = '{$node['act']}' AND level = '{$node['level']}' AND pid = '{$pid}' ";
            $resI = $this->db->fetchOne( $sql );

            if( !$resI ){
                if( $node['level'] < 3 ){
                    $sql = " INSERT INTO node ( `name`, `con`, `act`, `pid`, `sort`, `level`, `isMenu`, `status`) VALUES ( '{$node['name']}', '{$node['con']}', '{$node['act']}', '{$pid}', '{$node['sort']}', '{$node['level']}', '{$node['isMenu']}', '{$node['status']}') ";
                    $resI = Service::getInstance('superadmin')->insertNode( $sql );
                }else{
                    $final .= " ( '{$node['name']}', '{$node['con']}', '{$node['act']}', '$pid', '{$node['sort']}', '3', '0', '1' ), ";
                    $flag = true;
                }
            }
            if( $node['level'] < 3 ) $this->_makeNodeTree( $node['child'] , $resI );
        }
        if( $flag ){
            $res = Service::getInstance('superadmin')->insertNode( rtrim( $final,', ' ) );
            if( $res ) echo "<h1>添加三级节点OK</h1>";
            return true;
        }
        
    }

    public function insert_goods_price_historyAction()
    {
        $sql="SELECT purchPrice,id as goodsId FROM goods";
        $res=$this->db->fetchAll($sql);
        
        foreach ($res as $key => $value) {
          $res[$key]['updateTime']=time();
          $insert=$this->db->insert('goods_price_history',$res[$key]);
        }     
    }

    public function insertSellPlanAction()
    {
        /**
         * 随机分配status到push数据中,此方法仅用于测试
         */
        /*ini_set('max_execution_time', '0');
        $count=$this->db->fetchOne("SELECT COUNT(*) FROM push");
        for ($i=1; $i < $count+1 ; $i++) { 
            $status=mt_rand(0,3);
            $sql="UPDATE push SET status={$status}";
            $res=$this->db->update('push',array('status'=>$status),'id='.$i);
        exit;
        }*/
        if ($this->isAjax()) {
            ini_set('max_execution_time', '0');
            // $start=file_get_contents('./start.txt');
            $sql="SELECT goods.id AS goodsId,push.id AS pushId,push.channel AS channelId,shop.quota as quota,shop.ptimes as pTimes,goods.purchPrice as purchPrice,shop.id AS shopId FROM push JOIN goods ON push.goodsId = goods.id JOIN shop ON shop.id = goods.shopId WHERE push.status = 0";
            $res=$this->db->fetchAll($sql);
            foreach ($res as $k => $v) {
                $sql="SELECT ctimes FROM channel_shop_ctimes WHERE shopId = {$v['shopId']} AND channelId = {$v['channelId']}";
                $res[$k]['cTimes']=$this->db->fetchOne($sql);
                if (!$res[$k]['cTimes']) {
                    $res[$k]['cTimes']=1;
                }
                $res[$k]['channelPrice']=$v['purchPrice']*$v['pTimes']*$res[$k]['cTimes'];
                // 判断邮费模式
                if ($res[$k]['channelPrice']>=$v['quota']) {
                    $res[$k]['express']=4;
                }elseif($v['quota']==0 || $res[$k]['channelPrice']<$v['quota'] || is_null($v['quota'])){
                    $res[$k]['express']=5;
                }
                $res[$k]['tradePrice']=$res[$k]['channelPrice'];
                $res[$k]['certificate']=7;
                $res[$k]['package']=12;
                $res[$k]['createTime']=time();
                unset($res[$k]['quota']);
                unset($res[$k]['purchPrice']);
                unset($res[$k]['channelPrice']);
                unset($res[$k]['pTimes']);
                unset($res[$k]['cTimes']);
                unset($res[$k]['shopId']);
                $this->db->insert('sell_plan',$res[$k]);
                // 同时还要在push表中将该商品inSellplan修改为1
                $this->db->update('push',array('inSellplan'=>1),'id='.$v['pushId']);
            }
            $this->respon(1,'添加成功');
        }
    }

    /**
     * 删除push表中所有status为5的商品
     */
    public function delAllPushStatus5Action()
    {
        $res=$this->db->delete('push','status=5');
        Factory::p('已删除push表中'.$res.'条status为5的数据',1);
    }

    //订单商品表批量导入数据
    public function insertOrdersGoodsAction(){
        ini_set('max_execution_time', '0');
        $parm = $this->getQuery('parm');
        if ( !$parm ) {
            echo "no parm!";
            exit;
        } else {
            $step = $this->getQuery('step',500);
            $start = ($parm-1)*$step;
            $limit = $start.','.$step;
        }
        if ( $parm == 1 ) {
            $truncate = $this->db->query("Truncate Table orders_goods");
        }
        $sql = " SELECT 
                A.id,A.goodsId,A.shopId,A.userId,A.number,A.price, 
                B.name,B.purchPrice AS goods_price 
                FROM orders AS A 
                LEFT JOIN goods AS B ON A.goodsId = B.id 
                ORDER BY A.id ASC limit {$limit} ";
        $orders = $this->db->fetchAll( $sql );
        // print_r($orders);
        // exit;
        if ( !$orders ) {
            echo "no orders!";
            exit;
        }
        foreach ($orders as $key => $value) {
            $orders[$key]['goods_image'] = Service::getInstance('goods')->getGoodsImg( $value['goodsId']);
        }
        // print_r($orders);
        // exit;
        foreach ($orders as $key => $value) {
            $addSql = " INSERT INTO orders_goods ( `order_id`, `goods_id`, `shop_id`, `customer_id`, `goods_name`, `goods_image`, `goods_price`, `goods_number`, `goods_pay_price` ) VALUE ( '{$value['id']}', '{$value['goodsId']}', '{$value['shopId']}', '{$value['userId']}', '{$value['name']}', '{$value['goods_image']}', '{$value['goods_price']}', '{$value['number']}', '{$value['price']}' ) ";
            echo $value['id']."---->>>".$addSql."<br>";
            $this->db->query( $addSql );
        }

        echo "ok";
        exit;

    }
    
    //处理customer表、address表存量数据
    public function updateCustomerAndAddressInfoAction(){
        $orders = Service::getInstance('address')->getOrders();
        $customer = Service::getInstance('address')->getCustomer();
        $developers = Service::getInstance('address')->getDevelopers();
        $users = Service::getInstance('address')->getUsers();
        
        //orders表数据导入customer
        $orderslist = array();
        foreach($orders as $k=>$row){
            if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/",$row['tel'])){    
                $orderslist[] = $row;
            }
        }
        foreach($orderslist as $olist){
            //通过变量a的值的变化来确定customer表中是否有与orders表相同的手机号码
            $a = 'no';
            foreach($customer as $clist){
                if($olist['tel'] == $clist['tel']){
                    $a = 'yes';
                }
            }
            if($a == 'no'){
                //新增customer表数据
                $res = Service::getInstance('address')->addCustomer($olist);
            }           
        }
        
        //developers表数据导入customer
        $developerslist = array();
        foreach($developers as $k=>$row){
            if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/",$row['tel'])){    
                $developerslist[] = $row;
            }
        }
        foreach($developerslist as $dlist){
            //通过变量a的值的变化来确定customer表中是否有与developers表相同的手机号码
            $a = 'no';
            foreach($customer as $clist){
                if($dlist['tel'] == $clist['tel']){
                    $a = 'yes';
                }
            }
            if($a == 'no'){
                //新增customer表数据
                $res = Service::getInstance('address')->addCustomer($dlist);
            }           
        }
        
        //users表数据导入customer
        $userslist = array();
        foreach($users as $k=>$row){
            if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/",$row['tel'])){    
                $userslist[] = $row;
            }
        }
        foreach($userslist as $ulist){
            //通过变量a的值的变化来确定customer表中是否有与users表相同的手机号码
            $a = 'no';
            foreach($customer as $clist){
                if($ulist['tel'] == $clist['tel']){
                    $a = 'yes';
                }
            }
            if($a == 'no'){
                //新增customer表数据
                $res = Service::getInstance('address')->addCustomer($ulist);
            }           
        }
        
        //orders表的地址信息导入收货地址表
        $ordersAll = Service::getInstance('address')->getOrdersAll();    
        $addressAll = Service::getInstance('address')->getAddressAll();
        $new = $ordersAll;
        //去重地址信息
        for($i = 0,$indexOne = count($ordersAll);$i<$indexOne;$i++){
            for($j = $i+1;$j<$indexOne;$j++){
//                if(!array_diff($ordersAll[$i],$ordersAll[$j])){
//                    unset($new[$j]);
//                }
                $repeatOrders = trim($ordersAll[$i]['customer_id']) === trim($ordersAll[$j]['customer_id']) && trim($ordersAll[$i]['name']) === trim($ordersAll[$j]['name']) && trim($ordersAll[$i]['tel']) === trim($ordersAll[$j]['tel']) && trim($ordersAll[$i]['province']) === trim($ordersAll[$j]['province']) && trim($ordersAll[$i]['city']) === trim($ordersAll[$j]['city']) && trim($ordersAll[$i]['area']) === trim($ordersAll[$j]['area']) && trim($ordersAll[$i]['address']) === trim($ordersAll[$j]['address']);
                if($repeatOrders){
                    unset($new[$j]);
                }
            }
            
        }
        //新增收货地址数据
        foreach($new as $row){
            $b = 'no';
            //判断address表中是否有与新增的数据相同
            foreach($addressAll as $addRow){
                $repeatAddress = trim($row['customer_id']) === trim($addRow['customer_id']) && trim($row['name']) === trim($addRow['name']) && trim($row['tel']) === trim($addRow['tel']) && trim($row['province']) === trim($addRow['province']) && trim($row['city']) === trim($addRow['city']) && trim($row['area']) === trim($addRow['area']) && trim($row['address']) === trim($addRow['address']);
                if($repeatAddress){
                    $b = 'yes';
                }
            }
            if($b == 'no'){
                $res = Service::getInstance('address')->addAddress($row);
            }            
        }
        exit('<h4>执行成功</h4>');
    }
    //获得分类的JSON字符串(拼凑好带有父子接口的树)
    public function getCategoryTreeJsonAction(){
        $sql = " SELECT id,name,pid FROM category ";
        $category = $this->db->fetchAll($sql);

        $tree = $this->makeTree($category);
        $res['obj'] = $tree;
        exit( json_encode($res) );
    }

    public function makeTree( $arr,$pid = 0 ){
        $result = array();
        foreach( $arr as $k => $v ){
            if( $v['pid'] == $pid ){
                unset($arr[$k]);
                $child = $this->makeTree( $arr,$v['id'] );
                if( !empty($child) ) $v['child'] = $child;
                $result[] = $v;
            }
        }
        return $result;
    }
//{"id":"1","cid":"3","name":"\u9898\u6750","pid":"0","child":[{"id":"2","cid":"3","name":"\u4eba\u7269","pid":"1"},{"id":"3","cid":"3","name":"\u5c71\u6c34","pid":"1"}]
    //获得属性的JSON字符串(拼凑好带有父子接口的树)
    public function getAttrTreeJsonAction(){
        $sql    = " SELECT attr_id AS id,cat_id AS cid,attr_name AS name,0 AS pid FROM attribute";
        $attr   = $this->db->fetchAll( $sql );
        $sql    = " SELECT id,cat_id AS cid,`values` AS name,attr_pid AS pid FROM attr_value ";
        $values = $this->db->fetchAll( $sql );

        $tree = $this->makeAttrTree( $attr,$values );

        $result['paramArr'] = $tree;
        exit( json_encode($result) );

    }

    public function makeAttrTree( $attr,$values ){

        $valuesTree = array();
        if( !empty($values) ){
            foreach( $values as $v ){
                $valuesTree[$v['pid']][] = $v;
            }
        }

        $tree = array();
        if( !empty($attr) ){
            foreach( $attr as $v ){
                $child = isset( $valuesTree[$v['id']] ) ? $valuesTree[$v['id']] : array();
                $v['child'] = $child;
                $tree[] = $v;
            }
        }
        unset($attr);
        unset($values);
        return $tree;
    }

}