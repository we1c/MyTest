<?php

class CheckoneController extends BaseController
{
    public function init()
    {
        parent::init();  
    }
    
    //列表
	public function indexAction(){
		$perpage = $this->getQuery('perpage',100);
        $showpage = 5;
		$page = $this->getQuery('page', 1);
		$keyword = trim($this->getQuery('keyword', ''));
        $uploader = $this->getQuery('uploader','');
        $searchType = $this->getQuery('searchType', '1');
        $checkStatus = $this->getQuery('checkStatus','wait');
		$data = Service::getInstance('goods')->goodsCheckList($page,$perpage,$keyword,0,'',$searchType,$checkStatus);
		$this->_view->list = $data['list'];
        if( !empty($data['attrs']) ) $this->_view->attrs = $data['attrs'];
		$this->_view->total = $data['loseTotal']+$data['waitTotal'];
        $this->_view->loseTotal = $data['loseTotal'];
        $this->_view->waitTotal = $data['waitTotal'];
		$this->_view->keyword = $keyword;
        $this->_view->perpage = $perpage;
        $this->_view->searchType = $searchType;
        $this->_view->checkStatus = $checkStatus;
        $total = $checkStatus.'Total';
		// $url = '/checkone/index?page=__page__&keyword='.$keyword;
		// $this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
        $pageObj = new Page( $data[$total],$perpage,$showpage,$page,'',array('checkone','index','keyword'=>$keyword,'perpage'=>$perpage,'searchType'=>$searchType,'checkStatus'=>$checkStatus));
        $this->_view->pagebar = $pageObj -> showPage();

	}
	
	//添加
	public function addAction() {
	    if ($this->isPost()) {
	        $uid = Yaf_Registry::get('uid');
	        $goodArr = array();
	        $goodArr['name'] = trim($this->getPost('name'));
	        $goodArr['shopId'] = intval($this->getPost('shopId'));
            setcookie("shopId", $goodArr['shopId'], time()+3600);
	        $goodArr['code'] = Service::getInstance('goods')->getGoodsCode($goodArr['shopId']);
	        $goodArr['purchPrice'] = trim($this->getPost('purchPrice'));
	        $goodArr['goodsStock'] = intval($this->getPost('stock',0));
            $goodArr['fromWhere'] = 2;
	        $goodArr['uploader'] = $uid;
	        $goodArr['createTime'] = time();
	        $goodArr['intro'] = htmlspecialchars($this->getPost('intro'));
	        $goodArr['category1'] = trim($this->getPost('category1'));
	        $goodArr['category2'] = trim($this->getPost('category2'));
	        $goodArr['category3'] = trim($this->getPost('category3'));
	        $goodArr['content'] = htmlspecialchars($this->getPost('content'));
	        $goodArr['showPrice'] = 1;
	        $goodArr['goodsNo'] = trim($this->getPost('goodsNo'));
	        $goodArr['platform'] = 1;
                $goodArr['taobao_special'] = trim($this->getPost('taobaoSpecial',''));
	        if(!$goodArr['shopId']) {
	            $this->error('请选择店铺');
	            $category1 = Service::getInstance('goods')->getGoodsCategory();
	            $shop = Service::getInstance('shop')->getList();
	            $this->_view->shop = $shop;
	            $this->_view->category1 = $category1;
	            return;
	        }
	        if(!$goodArr['name']) {
	            $this->error('请填写商品名称');
	            $category1 = Service::getInstance('goods')->getGoodsCategory();
	            $shop = Service::getInstance('shop')->getList();
	            $this->_view->shop = $shop;
	            $this->_view->category1 = $category1;
	        }
	        if(!$goodArr['purchPrice']) {
	            $this->error('请填写商品价格');
	            $category1 = Service::getInstance('goods')->getGoodsCategory();
	            $shop = Service::getInstance('shop')->getList();
	            $this->_view->shop = $shop;
	            $this->_view->category1 = $category1;
	        }
	        $key = $this->getPost('key');
	        $value = $this->getPost('value');
	         
	        //参数处理
	        $arra = '';
	        if ( $key ) {
	            foreach ($key as $k=>$v){
	                if(trim($v) != '' && trim($value[$k]) != '') {
	                    $a = explode('-', trim($v));
	                    $b = explode('-', trim($value[$k]));
	                    if ( !isset($a[1]) ) $a[1] = '0';
	                    if ( !isset($b[1]) ) $b[1] = '0';
	                    $arra[] = '{"key":{"id":'.$a[1].',"name":"'.$a[0].'"},"value":{"id":'.$b[1].',"name":"'.$b[0].'"}}';
	                }
	            }
	        }
	
	        if ( $arra ) {
	            $attribute = '['.implode(',', $arra).']';
	        } else {
	            $attribute = '';
	        }
	        $goodArr['attribute'] = $attribute;
	        $shop = Service::getInstance('shop')->getShopinfo( intval( $goodArr['shopId'] ) );
	        $goodArr['price'] = round($goodArr['purchPrice'] * $shop['mtimes']);
	        $goodId = Service::getInstance('goods')->add($goodArr);
	        if ( $goodId ) {
                Service::getInstance('shop')->addNumday($goodArr['shopId']);
	            $smttype = $this->getPost('submittype');
	            if ( $smttype == 2 ) {
	                $result = Service::getInstance('goods')->addGoodsCheckTwo( $goodId );
	                if ( !$result ) {
	                    Service::getInstance('goods')->delGoods( $goodId );
	                    $this->flash('/checkone/index','添加失败');
	                }
	            }
	            if ( $smttype == 1 ) {
	                $result = Service::getInstance('goods')->addGoodsCheck( $goodId );
	                if ( !$result ) {
	                    Service::getInstance('goods')->delGoods( $goodId );
	                    $this->flash('/checkone/index','添加失败');
	                }
	            }
	            unset($goodArr);

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
                    foreach( $file['name'] as $num => $img ){
                        if( !empty($img) ){
                            foreach( $img as $index => $name ){
                                $flag = $num.'-'.$index;
                                if( !$file['error'][$num][$index] && !in_array( $flag,$record ) ){
                                    $avatar = $file['tmp_name'][$num][$index];
                                    $hash = md5( $avatar );
                                    $original = Util::getDir( $dir,$hash ).$hash.'_image.jpg';
                                    if( move_uploaded_file( $avatar , $original ) ){
                                        //对新增加的图片做页面相对应的索引记录
                                        $sortIndex = $imgSort[$num][$index];
                                        $imgs[$sortIndex] = $hash;
                                        $size = array( '100x100','800x800' );
                                        if( $sortIndex === '0' ) $size[] = '400x400';
                                        Service::getInstance('superadmin')->makeThumbFac( $original,$size );
                                    }
                                }
                            }
                        }
                    }
                }
                //对图片所有hash值按页面索引进行排序
                if( !empty($imgs) ) ksort( $imgs );

	            if( $imgs ) {
	                $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
	                foreach ($imgs as $k=>$v){
	                    $temp = intval($k+1);
	                    $sql .="('{$goodId}', '{$v}', $temp ),";
	                }
	                $sql = substr($sql, 0,-1);
	                Service::getInstance('goods')->addgoods_images($sql);
	            }
                  // 将成功添加的商品进货价和gid写入goods_price_history
                  $res=Service::getInstance('goods')->addHistoryPrice($this->getPost('purchPrice'),$goodId);
                  $this->flash('/checkone/index','添加成功');
          } else {
	            $this->flash('/checkone/index','添加失败');
	        }
	    }
	    $category1 = Service::getInstance('goods')->getGoodsCategory();
	    $shop = Service::getInstance('shop')->getList();
	    $this->_view->shop = $shop;
	    $this->_view->category1 = $category1;
        if (!empty($_COOKIE['shopId'])) {
            $this->_view->shopId = $_COOKIE['shopId'];
        }
	}
   
    //编辑
    public function editAction() {
        if($this->isPost()) {
            $gid = intval($this->getPost('id'));
            $name = trim($this->getPost('name'));
            $price = trim($this->getPost('price'));
            $goodsNo = trim($this->getPost('goodsNo'));
            $stock = intval($this->getPost('stock',0));
            $category1 = trim($this->getPost('category1'));
            $category2 = trim($this->getPost('category2'));
            $category3 = trim($this->getPost('category3'));
            $key = $this->getPost('key');
            $value = $this->getPost('value');
            $intro = htmlspecialchars($this->getPost('intro'));
            $imgs = $this->getPost('imgs');
            $content = htmlspecialchars($this->getPost('content'));
            $taobaoSpecial = trim($this->getPost('taobaoSpecial',''));
            if ( !$gid ) {
                $this->flash('/checkone/index','参数错误');
                return false;
            }
            if ( !$name ) {
                $this->error('商品名称不能为空');
                $Info = Service::getInstance('goods')->getGoodsInfoById($gid);
                $category1 = Service::getInstance('goods')->getGoodsCategory();
                $this->_view->Info = $Info;
                $this->_view->category1 = $category1;
                return;
            }
            
            //参数处理
            $arra = '';
            if ( $key ) {
                foreach ($key as $k=>$v){
                    if(trim($v) != '' && trim($value[$k]) != '') {
                        $a = explode('-', trim($v));
                        $b = explode('-', trim($value[$k]));
                        if ( !isset($a[1]) ) $a[1] = '0';
                        if ( !isset($b[1]) ) $b[1] = '0';
                        $arra[] = '{"key":{"id":'.$a[1].',"name":"'.$a[0].'"},"value":{"id":'.$b[1].',"name":"'.$b[0].'"}}';
                    }
                }
            }
            
            if ( $arra ) {
                $attributes = '['.implode(',', $arra).']';
            } else {
                $attributes = '';
            }
            $data = array(
                'name'=>$name,
                'purchPrice'=>$price,
                'goodsStock'=>$stock,
                'category1'=>$category1,
                'category2'=>$category2,
                'category3'=>$category3,
                'intro'=>$intro,
                'goodsNo'=>$goodsNo,
                'content'=>$content,
                'attribute'=>$attributes,
                'editor'=>'d-'.Yaf_Registry::get('uid'),
                'updateTime'=>time(),
                'taobao_special' => $taobaoSpecial
            );
            $res = Service::getInstance('goods')->edit($data,$gid);
            if ( $res >= 0 ) {
                $smttype = $this->getPost('submittype');
                if ( $smttype == 2 ) {
                    $cgoods = Service::getInstance('goods')->getCheckInfoByGoodsId( $gid );
                    Service::getInstance('goods')->saveCheck( array('status'=>1, 'oneTime'=>time()), $cgoods['id'] );
                }
                if ( $smttype == 3 ) {
                    $cgoods = Service::getInstance('goods')->getCheckInfoByGoodsId( $gid );
                    Service::getInstance('goods')->delCheck( $cgoods['id'] );
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
                    foreach( $file['name'] as $num => $img ){
                        if( !empty($img) ){
                            foreach( $img as $index => $name ){
                                $flag = $num.'-'.$index;
                                if( !$file['error'][$num][$index] && !in_array( $flag,$record ) ){
                                    $avatar = $file['tmp_name'][$num][$index];
                                    $hash = md5( $avatar );
                                    $original = Util::getDir( $dir,$hash ).$hash.'_image.jpg';
                                    if( move_uploaded_file( $avatar , $original ) ){
                                        //对新增加的图片做页面相对应的索引记录
                                        $sortIndex = $imgSort[$num][$index];
                                        $imgs[$sortIndex] = $hash;
                                        $size = array( '100x100','800x800' );
                                        Service::getInstance('superadmin')->makeThumbFac( $original,$size );
                                    }
                                }
                            }
                        }
                    }
                }
                //对图片所有hash值按页面索引进行排序
                if( !empty($imgs) ) ksort( $imgs );
                if( isset($imgs[0]) ){
                    $original = Util::getDir( $dir,$imgs[0] ).$imgs[0].'_image.jpg';
                    $oneThumb = str_replace('_image.','_thumb_400x400.',$original);
                    if( !file_exists( $oneThumb ) ){
                        Service::getInstance('superadmin')->makeThumbFac( $original ,array( '400x400' ) );
                    }
                }
                //获取删除已经保存的记录
                $delOldImg = $this->getPost('delOldImg');
                if( !empty($delOldImg) ){
                    foreach( $delOldImg as $k => $hash ){
                        $imgType = array('_image','_thumb_100x100','_thumb_400x400','_thumb_800x800');
                        foreach( $imgType as $i => $t ){
                            $file = Util::getDir( $imgDir,$hash ).$hash.$t.'.jpg';
                            if( file_exists( $file ) ){
                                @unlink($file);
                            }
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
                }else{
                    Service::getInstance('goods')->delgoods_images( $gid );
                }
                $this->flash('/checkone/index','编辑成功');
            } else {
                $this->flash('/checkone/index','编辑失败');
                return false;
            }
	    } else {
	       $id = $this->getQuery('id',0);
	       $goods = Service::getInstance('goods')->getCheckInfoById( $id );
	       $Info = Service::getInstance('goods')->getGoodsInfoById($goods['goodsId']);
	       $category1 = Service::getInstance('goods')->getGoodsCategory();
	       if($Info['category1'] && $Info['category2']) {
	           $category2 = Service::getInstance('goods')->getCat($Info['category1']);
	           $category3 = Service::getInstance('goods')->getCat($Info['category2']);
	       } else {
	           $category2 = array();
	           $category3 = array();
	       }
    	   $shop = Service::getInstance('shop')->getList();
	       $arr = $Info['attribute'];
	       if ( is_array($arr) ) {
	           foreach ( $arr as $k=>$v ) {
	               if( $v['value']['id'] ) {
	                   $data = Service::getInstance('goods')->getAttrValueByAttrId($v['key']['id']);
	                   $arr[$k]['value']['param'] = $data;
	               }
	           }
	       }
	       $Info['attribute'] = $arr ? $arr : ''; 
    	   $this->_view->shop = $shop;
	       $this->_view->Info = $Info;
	       $this->_view->category1 = $category1;
	       $this->_view->category2 = $category2;
	       $this->_view->category3 = $category3;
	    }
    }
    
    public function categoryAction(){
        $id = $this->getPost('pid');
        if($id === '') {
            $this->respon(0,array());
        }
        $category = Service::getInstance('goods')->getGoodsCategory($id);
        $this->respon(1,$category);
        return false;
    }
    
    
    public function getParaAction() {
        $pid = $this->getPost('pid');
        $data = Service::getInstance('goods')->getPar($pid);
        $this->respon(1, $data);
    }
    //审核
    public function checkAction() {
        if ( $this->isPost() ) {
            $id = $this->getPost( 'id',0 );
            $status = $this->getPost( 'status',0 );
            if ( !$status ) {
                $this->error( '请选择审核结果' );
                return;
            }
            if ( $status == 1 ) {
                $data = array( 'oneTime'=>time(), 'twoTime'=>time(), 'status'=>2, 'reason'=>'' );
                $res = Service::getInstance('goods')->saveCheck( $data, $id );
                if ( $res >= 0 ) {
                    $goods = Service::getInstance('goods')->getCheckInfoById( $id );
                    Service::getInstance('goods')->change( $goods['goodsId'], array( 'platform'=>2 ) );
                    $this->flash( '/checkone/index', '保存成功' );
                } else {
                    $this->flash( '/checkone/index', '保存失败' );
                }
            } elseif ( $status == 2 ) {
                $res = Service::getInstance('goods')->delCheck( $id );
                if ( $res ) {
                    $this->flash( '/checkone/index', '设置成功' );
                } else {
                    $this->flash( '/checkone/index', '设置失败' );
                }
            }
        }
        $id = $this->getQuery('id');
        $goods = Service::getInstance('goods')->getCheckInfoById( $id );
        $this->_view->data = $goods;
    }
    
    //删除
    public function delAction() {
        $id = $this->getPost('gid',0);
        if (!$id) $this->respon(0,'参数异常');
        $res = Service::getInstance('goods')->delCheckAndGoods($id);
        if ($res === true) {
            $this->respon(1, '操作成功');
        } else {
            $this->respon(0, $res);
        }
    }


}