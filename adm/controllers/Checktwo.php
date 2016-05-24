<?php

class ChecktwoController extends BaseController
{
    public function init()
    {
        parent::init(); 
    }
    
    //列表
	public function indexAction(){
		$page = $this->getQuery('page', 1);
        $perpage = $this->getQuery('perpage',30);
        $showpage = 5;
		$keyword = trim($this->getQuery('keyword', ''));
        $uploader = $this->getQuery('uploader', '');
        $searchType = $this->getQuery('searchType', '1');
		$data = Service::getInstance('goods')->goodsCheckList($page,$perpage,$keyword,1,$uploader,$searchType);
        //$Info = Service::getInstance('goods')->getGoodsInfoById($goods['goodsId']);
        $cat1 = Service::getInstance('goods')->getGoodsCategory();
        //echo "<pre>";
        $this->_view->list = $data['list'];
        if( !empty($data['attrs']) ) $this->_view->attrs = $data['attrs'];
        $this->_view->cat1 = $cat1;
        $this->_view->total = $data['total'];
        $this->_view->keyword = $keyword;
        $this->_view->uploader = $uploader;
        $this->_view->searchType = $searchType;
		/*$url = '/checktwo/index?page=__page__&keyword='.$keyword;
		$this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );*/
        $pageObj = new Page($data['total'],$perpage,$showpage,$page,'',array('checktwo','index','perpage'=>$perpage,'keyword'=>$keyword,'searchType'=>$searchType));
        $this->_view->pagebar = $pageObj -> showPage( );
        $this->_view->perpage = $perpage;
	}

    public function singleSaveAction(){
        if( $this->isPost() ){
            $gid = intval($this->getPost('goodsId'));
            $checkId = intval($this->getPost('checkId'));
            if( !$gid ) $this->respon( 0 , '商品参数出错' );
            $data['name'] = trim($this->getPost('name'));
            if( empty($data['name']) ) $this->respon( 0 , '商品名称不能为空' );
            $data['purchPrice'] = trim($this->getPost('purchPrice'));
            $data['goodsNo'] = trim($this->getPost('goodsNo'));
            $data['category1'] = intval($this->getPost('category1'));
            $data['category2'] = intval($this->getPost('category2'));
            $data['category3'] = intval($this->getPost('category3'));
            $data['intro'] = htmlspecialchars($this->getPost('descript'));
            //echo "<pre>";var_dump($_POST);exit;
            $param = $this->getPost('param');
            $attribute = array();
            if( !empty($param) ){
                foreach( $param[$checkId] as $keyid => $v ){
                    if( is_numeric($keyid) ){
                        $keyId = $v['key']['id'];
                        $keyName = $v['key']['name'];
                        $value = explode( '-',$v['value'] );
                        $valueId = $value[0];
                        $valueName = $value[1];
                    }else{
                        $keyId = 0;
                        $keyName = $v['key'];
                        $valueId = 0;
                        $valueName = $v['value'];
                    }
                    $attribute[] = array(
                                'key'=>array(
                                        'id'=>$keyId,
                                        'name'=>$keyName
                                        ),
                                'value'=>array(
                                        'id'=>$valueId,
                                        'name'=>$valueName
                                        )
                            );
                }
            }
            $data['attribute'] = json_encode($attribute);

            $res = Service::getInstance('goods')->edit($data,$gid);

            if( $res >= 0 ){
                $this->respon( 1 , '保存成功' );
                //$this->flash('/checktwo/index','保存成功',3);
            }else{
                $this->respon( 0 , '保存失败' );
            }

        }

    }

    public function getIntroAction(){
        if( $this->isPost() ){
            $gid = $this->getPost('gid') + 0;
            $intro = $this->db->fetchOne( ' SELECT intro FROM goods WHERE id = '.$gid );
            $this->respon( 1, $intro );
        }
    }

    public function saveIntroAction(){
        if( $this->isPost() ){
            $gid = $this->getPost('gid') + 0;
            $intro = htmlspecialchars($this->getPost('intro'));
            if( !$gid ){
                $this->respon( 0, '参数错误' );
            }
            $res = $this->db->update( 'goods',array( 'intro'=>$intro ), 'id = '.$gid );
            if( $res >= 0 ){
                $this->respon( 1, '保存成功' );
            }else{
                $this->respon( 0, '保存失败' );
            }
        }
    }

    public function changeAction(){
        if( $this->isPost() ){
            $chkid = intval($this->getPost('id'));
            $gid = intval($this->getPost('gid'));
            $action = trim($this->getPost('action'));
            if( $action == '0-0' ){
                $checkData = array('status'=>0,'reason'=>'不合格','twoTime'=>time());
                $prompt = '驳回请求: ';
            }elseif( $action == '2-1' ){
                $checkData = array('status'=>2,'twoTime'=>time());
                $goodsData = array('platform'=>2,'checkResult'=>1);
                $prompt = '审核通过: ';
            }elseif( $action == '2-2' ){
                $checkData = array('status'=>2,'twoTime'=>time());
                $goodsData = array('platform'=>2,'checkResult'=>2);
                $prompt = '无文案通过: ';
            }

            if( $action != '0-0' ){
                $resCheck = Service::getInstance('goods')->saveCheck( $checkData , $chkid );
                $resGoods = Service::getInstance('goods')->change( $gid , $goodsData );
            }else{
                $resCheck = Service::getInstance('goods')->saveCheck( $checkData , $chkid );
                $resGoods = 1;
            }

            if( $resCheck > 0 && $resGoods > 0 ){
                $result = '操作成功！';
                $this->respon( 1, $prompt.$result );
            }else{
                $result = '操作成功！';
                $this->respon( 0, $prompt.$result );
            }

        }

    }

    public function batchChangeAction(){
        if( $this->isPost() ){
            $action = $this->getQuery('action');
            $chkGoods = $this->getPost('chkGoods');
            $fail = array();
            foreach( $chkGoods as $v ){
                $ids = explode('-',$v);
                $chkid = $ids[0];
                $gid = $ids[1];
                if( $action == '0-0' ){
                    $checkData = array('status'=>0,'twoTime'=>time(),'reason'=>'不合格');
                }else{
                    $checkData = array('status'=>2,'twoTime'=>time());
                    if( $action == '2-1' ){
                        $goodsData = array('platform'=>2,'checkResult'=>1);
                    }else{
                        $goodsData = array('platform'=>2,'checkResult'=>2);
                    }
                }

                if( $action != '0-0' ){
                    $resCheck = Service::getInstance('goods')->saveCheck( $checkData , $chkid );
                    $resGoods = Service::getInstance('goods')->change( $gid , $goodsData );
                }else{
                    $resCheck = Service::getInstance('goods')->saveCheck( $checkData , $chkid );
                    $resGoods = 1;
                }

                if( !$resCheck || !$resGoods ){
                    $fail[] = $v;
                }

            }

            if( $action == '0-0' ){
                $prompt = '批量->驳回申请: ';
            }elseif( $action == '2-1' ){
                $prompt = '批量->审核通过: ';
            }elseif( $action == '2-2' ){
                $prompt = '批量->暂过文案: ';
            }

            if( empty($fail) ){
                $result = '操作成功！';
                $this->respon( 1, $prompt.$result );
            }else{
                $result = '操作失败！';
                $this->respon( 0, $prompt.$result );
            }

            
        }

    }
   
//详情
    public function editAction() {
        if($this->isPost()) {
            $gid = intval($this->getPost('id'));
            $name = trim($this->getPost('name'));
            $price = trim($this->getPost('mprice'));
            $goodsNo = trim($this->getPost('goodsNo'));
            $category1 = trim($this->getPost('category1'));
            $category2 = trim($this->getPost('category2'));
            $category3 = trim($this->getPost('category3'));
            $key = $this->getPost('key');
            $value = $this->getPost('value');
            $intro = htmlspecialchars($this->getPost('intro'));
            $imgs = $this->getPost('imgs');
            $content = htmlspecialchars($this->getPost('content'));
            if ( !$gid ) {
                $this->flash('/checktwo/index','参数错误');
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
                'category1'=>$category1,
                'category2'=>$category2,
                'category3'=>$category3,
                'intro'=>$intro,
                'goodsNo'=>$goodsNo,
                'content'=>$content,
                'attribute'=>$attributes
            );
            $res = Service::getInstance('goods')->edit($data,$gid);
            if ( $res >= 0 ) {
                $smttype = $this->getPost('submittype');
                $cgoods = Service::getInstance('goods')->getCheckInfoByGoodsId( $gid );
                if ( $smttype == 2 ) {
                    $data = array( 'twoTime'=>time(), 'status'=>2 );
                    Service::getInstance('goods')->saveCheck( $data, $cgoods['id'] );
                    Service::getInstance('goods')->change( $cgoods['goodsId'], array( 'platform'=>2, 'checkResult'=>1 ) );
                }
                if ( $smttype == 4 ) {
                    $data = array( 'twoTime'=>time(), 'status'=>2 );
                    Service::getInstance('goods')->saveCheck( $data, $cgoods['id'] );
                    Service::getInstance('goods')->change( $cgoods['goodsId'], array( 'platform'=>2, 'checkResult'=>2 ) );
                }
                if ( $smttype == 3 ) {
                    $data = array( 'twoTime'=>time(), 'status'=>0, 'reason'=>'不合格' );
                    $res = Service::getInstance('goods')->saveCheck( $data, $cgoods['id'] );
                }
                $file = $_FILES['file'];
                $count = count($file['name']);
                if ( $count ) {
                    for ( $i = 0; $i < $count; $i++ ) {
                        if( !$file['error'][$i] ) {
                            $avatar = $file['tmp_name'][$i];
                            $hash = md5($avatar);
                            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                            if ( move_uploaded_file( $avatar, Util::getDir( $dir ,$hash ) . $hash . "_image.jpg") ) {
                                $imgs[] = $hash;
                            } else {
                                continue;
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
                }
                $this->flash('/checktwo/index','编辑成功');
            } else {
                $this->flash('/checktwo/index','编辑失败');
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
	                   $data = Service::getInstance('goods')->getParamByPid($v['key']['id']);
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

    public function getCurrentAllAttrsAction() {
        $pid = $this->getPost('pid');
        $type = $this->getPost('type','');
        $gid = $this->getPost('gid','');
        $data = Service::getInstance('goods')->getCurrentAllAttrs($pid);
        if( $type == 'now' && $gid != '' ) {
            $data['now'] = Service::getInstance('goods')-> getGoodsAttrById( $gid );
        }
        $this->respon(1, $data);
    }
    
    //审核
    public function checkAction() {
        if ( $this->isPost() ) {
            $id = intval( $this->getPost( 'id',0 ) );
            $status = intval( $this->getPost( 'status',0 ) );
            $cause = trim( $this->getPost( 'cause','' ) );
            if ( !$status ) {
                $this->error( '请选择审核结果' );
                return;
            }
            if ( $status == 1 ) {
                $data = array( 'twoTime'=>time(), 'status'=>2 );
                $res = Service::getInstance('goods')->saveCheck( $data, $id );
                if ( $res >= 0 ) {
                    $goods = Service::getInstance('goods')->getCheckInfoById( $id );
                    Service::getInstance('goods')->change( $goods['goodsId'], array( 'platform'=>2 ) );
                    $this->flash( '/checktwo/index', '保存成功' );
                } else {
                    $this->flash( '/checktwo/index', '保存失败' );
                }
            } elseif ( $status == 2 ) {
                if ( !$cause ) {
                    $this->error( '请填写失败原因' );
                    return;
                }
                $data = array( 'twoTime'=>time(), 'status'=>0, 'reason'=>$cause );
                $res = Service::getInstance('goods')->saveCheck( $data, $id );
                if ( $res ) {
                    $this->flash( '/checktwo/index', '设置成功' );
                } else {
                    $this->flash( '/checktwo/index', '设置失败' );
                }
            }
        }
        $id = $this->getQuery('id');
        $this->_view->data = array( 'id'=>$id );
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