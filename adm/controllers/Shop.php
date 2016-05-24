<?php

class ShopController extends BaseController
{
    public function init()
    {
        parent::init();  
    }
	public function indexAction(){
		$perpage = $this->getQuery('perpage',15);
        $showpage = 5;
		$page = $this->getQuery('page', 1);
		$keyword = $this->getQuery('keyword', '');
		$data = Service::getInstance('shop')->shoplist($page,$perpage,$keyword);
		$this->_view->list = $data['list'];
		// $url = $keyword ? '/shop/index?page=__page__&keyword='.$keyword : '/shop/index?page=__page__';
		//$this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
        $this->_view->total = $data['total'];
        $this->_view->perpage = $perpage;
        $this->_view->page = $page;
        $this->_view->keyword = $keyword;
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('shop','index','keyword'=>$keyword,'perpage'=>$perpage));
        $this->_view->pagebar = $pageObj -> showPage();
    }
	
	//添加店铺
	public function addAction() {
	    if($this->isPost()) {
	        $name = trim( $this->getPost('name','') );
	        $uid = $this->getPost('uid','');
	        $province = trim( $this->getPost('province',0) );
	        $city = trim( $this->getPost('city',0) );
	        $area = trim( $this->getPost('area',0) );
	        $address = trim( $this->getPost('address','') );
            $period = trim( $this->getPost('period','') );
            $quota = trim( $this->getPost('quota','') );
	        $ptimes = trim( $this->getPost('ptimes','') );
	        $mtimes = trim( $this->getPost('mtimes','') );
	        $type = 1;
            $category = trim( $this->getPost('category','') );
            $principal = trim( $this->getPost('principal','') );
	        $headimgurl = $this->getPost('headimgurl');
	        $intro = htmlspecialchars($this->getPost('intro'));
	        if(!$name) {
	            $this->error('请填写店铺名称');
	            $province = Service::getInstance('shop')->getProvince();
        	    $supplier = Service::getInstance('supplier')->getSupplierInfo();
        	    $this->_view->province = $province;
        	    $this->_view->supplier = $supplier;
	            return;
	        }
// 	        $sName = Service::getInstance('shop')->getShopByName($name);
	        $code = Service::getInstance('shop')->getScode();
	        if ( !$code ) {
	            $this->error('店铺编码超出编码范围');
	            $province = Service::getInstance('shop')->getProvince();
        	    $supplier = Service::getInstance('supplier')->getSupplierInfo();
        	    $this->_view->province = $province;
        	    $this->_view->supplier = $supplier;
	            return;
	        }
	        $shop = array(
	            'name'=>$name,
	            'scode'=>$code,
	            'province'=>$province,
	            'city'=>$city,
	            'area'=>$area,
	            'address'=>$address,
                'period'=>$period,
                'quota'=>$quota,
	            'ptimes'=>$ptimes,
	            'mtimes'=>$mtimes,
	            'type'=>$type,
	            'headimgurl'=>$headimgurl,
	            'intro'=>$intro,
                'category'=>$category,
                'principal'=>$principal
	        );
	        $sid =Service::getInstance('shop')->add($shop);
	        if ( $sid ) {
                if ($uid) {
                    $res = Service::getInstance('shop')->addRelation($sid,$uid);
                }else{
                    $res = true;
                }
                $allDis = Service::getInstance('distributor')->getAllDis();
                $ctimes = Service::getInstance('shop')->addCtimes($allDis,$sid);

	            if( $res ) {
    	            $this->flash('/shop/index','添加成功');
    	        } else {
    	            Service::getInstance('shop')->del($sid);
    	            $this->flash('/shop/index','添加失败');
    	        }
	        } else {
	            $this->flash('/shop/index','添加失败');
	        }
	        
	    }
	    $province = Service::getInstance('shop')->getProvince();
	    $supplier = Service::getInstance('supplier')->getSupplierInfo();
        $developers = Service::getInstance('developers')->getDeveloperNameList();
	    $this->_view->province = $province;
	    $this->_view->supplier = $supplier;
        $this->_view->developers = $developers;
	}

	//编辑店铺
	public function editAction() {
	    if($this->isPost()) {
	        $name = trim( $this->getPost('name','') );
            $uid = $this->getPost('uid','');
	        $id = $this->getPost('id');
	        $province = trim( $this->getPost('province',0) );
	        $city = trim( $this->getPost('city',0) );
	        $area = trim( $this->getPost('area',0) );
	        $address = trim( $this->getPost('address','') );
            $period = trim( $this->getPost('period','') );
            $quota = trim( $this->getPost('quota','') );
	        $ptimes = trim( $this->getPost('ptimes','') );
	        $mtimes = trim( $this->getPost('mtimes','') );
	        $type = 1;
            $category = trim( $this->getPost('category','') );
            $principal = trim( $this->getPost('principal','') );
	        $headimgurl = $this->getPost('headimgurl');
	        $intro = htmlspecialchars($this->getPost('intro'));
                $page = $this->getPost('page');
                $keyword = $this->getPost('keyword');
	        if( !$name ) {
	            $this->error('请填写店铺名称');
        	    $info = Service::getInstance('shop')->getShopinfo($id);
        	    $skp = Service::getInstance('shop')->getSkp($id);
        	    $info['skp'] = $skp;
        	    $province = Service::getInstance('shop')->getProvince();
        	    $city = Service::getInstance('shop')->getCity($info['province']);
        	    $area = Service::getInstance('shop')->getCity($info['city']);
        	    $supplier = Service::getInstance('supplier')->getSupplierInfo();
        	    $this->_view->info = $info;
        	    $this->_view->province = $province;
        	    $this->_view->city = $city;
        	    $this->_view->area = $area;
        	    $this->_view->supplier = $supplier;
	            return;
	        }
	        $shop = array(
	            'name'=>$name,
	            'province'=>$province,
	            'city'=>$city,
	            'area'=>$area,
	            'address'=>$address,
                'period'=>$period,
                'quota'=>$quota,
	            'ptimes'=>$ptimes,
	            'mtimes'=>$mtimes,
	            'type'=>$type,
	            'headimgurl'=>$headimgurl,
	            'intro'=>$intro,
                'category'=>$category,
                'principal'=>$principal
	        );
    	    $shopInfo = Service::getInstance('shop')->getShopinfo($id);
	        $sres =Service::getInstance('shop')->edit($shop,$id);
	        if($sres >= 0){
                if ($uid) {
                    $res = Service::getInstance('shop')->editRelation($id,$uid);
                }else{
                    $res = true;
                }
	            if( $res >= 0 ) {
    	            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'编辑成功');
    	        } else {
    	            $data = array(
    	                'name'=>$shopInfo['name'],
    	                'province'=>$shopInfo['province'],
    	                'city'=>$shopInfo['city'],
    	                'area'=>$shopInfo['area'],
    	                'address'=>$shopInfo['address'],
    	                'type'=>$shopInfo['type'],
                        'quota'=>$shopInfo['quota'],
                        'period'=>$shopInfo['period'],
    	                'ptimes'=>$shopInfo['ptimes'],
    	                'mtimes'=>$shopInfo['mtimes'],
    	                'headimgurl'=>$shopInfo['headimgurl'],
    	                'intro'=>$shopInfo['intro'],
    	            );
    	            Service::getInstance('shop')->edit($data,$id);
    	            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'编辑失败');
    	        }
	        } else {
	            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'编辑失败');
	        }
            
	        
	    }
	    $id = $this->getQuery('id');
            $page = $this->getQuery('page');
            $keyword = $this->getQuery('keyword');
	    $info = Service::getInstance('shop')->getShopinfo($id);
	    $skp = Service::getInstance('shop')->getSkp($id);
	    $info['skp'] = $skp;
	    $province = Service::getInstance('shop')->getProvince();
        if ($info['province']) {
            $city = Service::getInstance('shop')->getCity($info['province']);
        }else{
            $city = '';
        }
        if ($info['city']) {
            $area = Service::getInstance('shop')->getCity($info['city']);
        }else{
            $area = '';
        }
	    $supplier = Service::getInstance('supplier')->getSupplierInfo();
        $developers = Service::getInstance('developers')->getDeveloperNameList();
        $this->_view->developers = $developers;
	    $this->_view->info = $info;
	    $this->_view->province = $province;
	    $this->_view->city = $city;
	    $this->_view->area = $area;
	    $this->_view->supplier = $supplier;
            $this->_view->page = $page;
            $this->_view->keyword = $keyword;
	}

	//删除店铺
    function delAction(){
        return;
    	$id = $this->getQuery('id');
    	if(Service::getInstance('user')->del($id)){
    		$this->flash("/user/index/","删除成功");
    	}
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

    public function numdayAction() {
        $shopId = $this->getPost('shopId');
        $numday = Service::getInstance('shop')->getNumday($shopId);
        $shop = str_pad($shopId,3,"0",STR_PAD_LEFT);
        $num = str_pad($numday+1,3,"0",STR_PAD_LEFT);
        $data = "m".$shop.date('ymd').$num;
        if($numday === false) {
            $this->respon(0,'查询失败');
        } else {
            $this->respon(1,$data);
        }
    }
    
    //关闭
    public function disableAction() {
        $id = $this->getQuery('id');
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword');
        $data = array('status'=>'1');
        $goodsIdArr = Service::getInstance('goods')->getGoodsIdByShopId($id," former_platform = 0 ",1);
        if ($goodsIdArr) {
            $goodsIdIn = '';
            foreach ($goodsIdArr as $key => $value) {
                $goodsIdIn .= $value['id'].",";
            }
            $goodsIdIn = rtrim($goodsIdIn, ",");
            $set = Service::getInstance('goods')->setFormerPlatformByGoodsId($goodsIdIn);
        } 
        $res = Service::getInstance('shop')->edit($data,$id);
        if($res >= 0) {
            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'设置成功');
        } else {
            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'设置失败');
        }
    }
    //开启
    public function enableAction() {
        $id = $this->getQuery('id');
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword');
        $data = array('status'=>'0');
        $goodsIdArr = Service::getInstance('goods')->getGoodsIdByShopId($id," former_platform <> 0 ",3);
        if ($goodsIdArr) {
            $goodsIdIn = '';
            foreach ($goodsIdArr as $key => $value) {
                $goodsIdIn .= $value['id'].",";
            }
            $goodsIdIn = rtrim($goodsIdIn, ",");
            $get = Service::getInstance('goods')->setPlatformByGoodsId($goodsIdIn);
        }
        $res = Service::getInstance('shop')->edit($data,$id);
        if($res >= 0) {
            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'设置成功');
        } else {
            $this->flash('/shop/index?page='.$page.'&keyword='.$keyword,'设置失败');
        }
    }
    
    //店铺头像
    public function uploadheadimgAction(){
        if(!$_FILES['file']['error'])
        {
            $avatar = $_FILES['file']['tmp_name'];
            $hash = md5($avatar);
            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
             
            if ( move_uploaded_file( $avatar, Util::getDir( $dir ,$hash ) . $hash . "_avatar.jpg") )
            {
                $Res = "";
                $Res = array(
                    'headimgurl'  => Service::getInstance('shop')->getAvata( $hash ),
                    'path'=> $hash
                );
                 
                $this->respon( 1 , $Res );
            }
            else
            {
                $this->respon( 0 , array("上传失败") );
            }
             
        }
         
        $this->respon( 0 , $_FILES['file']['error']."请选择您要上传的头像!" );
    }

    public function setScoreAction(){

        $shopId = (int)$this->getPost('shopId');
        if( !$shopId ) $this->respon(0,'参数错误');
        $score = (int)$this->getPost('score');
        if( !$score ) $this->respon(0,'参数错误');

        $year = date('Y');
        $month = date('m');
        $day = date('j');
        $cols = 'day_'.$day;
        $id = $this->db->fetchOne( " SELECT id FROM shop_score WHERE shop_id = '{$shopId}' AND year = '{$year}' AND month = '{$month}' " );

        if( $id ){
            $sql = " UPDATE shop_score SET $cols = '{$score}' WHERE id = '{$id}' ";
            $action = "更新";
        }else{
            $sql = " INSERT INTO shop_score ( shop_id,year,month,$cols ) VALUES ( '{$shopId}','{$year}','{$month}',$score ) ";
            $action = "添加";
        }
        $res = $this->db->query( $sql );

        if( $res ){
            $this->respon( 1,$action.'成功' );
        }else{
            $this->respon( 0,$action.'失败' );
        }
    } 

    public function getScoreAction(){
        $shopId = $this->getQuery('id');
        if( !$shopId ) $this->respon(0,'参数错误');
        $year = date('Y');
        $month = date('m');
        $day = date('j');
        $cols = 'day_'.$day;
        $score = $this->db->fetchOne( " SELECT $cols FROM shop_score WHERE shop_id = '{$shopId}' AND year = '{$year}' AND month = '{$month}' " );

        if( !$score ) $score = 0;

        $this->respon( 1,$score );
    }
  
}