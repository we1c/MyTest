<?php

class CheckpushController extends BaseController
{
    public function init()
    {
        parent::init();  
    }
    
    //列表
    public function indexAction(){
        $perpage = $this->getQuery('perpage',30);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = trim($this->getQuery('keyword', ''));
        $channel = $this->getQuery('channel', '');
        $searchType = $this->getQuery('searchType', '1');
        $showType = $this->getQuery('showType','pingtai');
        $pingtaiTotal = Service::getInstance('push')->getCheckPushList($page,$perpage,$keyword,$channel,1,$searchType,'pingtai',1);
        $shangjiaTotal = Service::getInstance('push')->getCheckPushList($page,$perpage,$keyword,$channel,1,$searchType,'shangjia',1);
        $data = Service::getInstance('push')->getCheckPushList($page,$perpage,$keyword,$channel,1,$searchType,$showType);
        $shop = Service::getInstance('shop')->getList();
        $dis = Service::getInstance('distributor')->getAllDis();
        $this->_view->shop = $shop;
        $this->_view->list = $data['list'];
        $this->_view->total = $pingtaiTotal+$shangjiaTotal;
        $this->_view->channel = $channel;
        $this->_view->dis = $dis;
        $this->_view->keyword = $keyword;
        $this->_view->perpage = $perpage;
        $this->_view->page = $page;
        $this->_view->searchType = $searchType;
        $this->_view->showType = $showType;
        $this->_view->pingtaiTotal = $pingtaiTotal;
        $this->_view->shangjiaTotal = $shangjiaTotal;
        /*$url = '/checkpush/index?page=__page__&keyword='.$keyword.'&channel='.$channel;
        $this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );*/
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('checkpush','index','keyword'=>$keyword,'channel'=>$channel,'perpage'=>$perpage,'searchType'=>$searchType,'showType'=>$showType));
        $this->_view->pagebar = $pageObj->showPage( );
    }
	
   
    //详情
    public function editAction() {
        if($this->isPost()) {
            $perpage = intval($this->getPost('perpage',30));
            $page = intval($this->getPost('page',1));
            $keyword = trim($this->getPost('keyword',''));
            $channel = $this->getPost('channel','');
            $gid = intval($this->getPost('id'));
            $pid = intval($this->getPost('pid'));
            $name = trim($this->getPost('name'));
//             $price = trim($this->getPost('price'));
//             $goodsNo = trim($this->getPost('goodsNo'));
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
                $this->flash('/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage,'参数错误' ,1);
                return false;
            }
            if ( !$name ) {
                $this->flash('/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage,'商品名称不能为空' ,1);
                return false;
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
//             $data = array(
//                 'name'=>$name,
//                 'purchPrice'=>$price,
//                 'category1'=>$category1,
//                 'category2'=>$category2,
//                 'category3'=>$category3,
//                 'intro'=>$intro,
//                 'goodsNo'=>$goodsNo,
//                 'content'=>$content,
//                 'attribute'=>$attributes
//             );
            $data = array(
                'name'=>$name,
                'category1'=>$category1,
                'category2'=>$category2,
                'category3'=>$category3,
                'intro'=>$intro,
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
                    Service::getInstance('goods')->editPush( $gid, array('status'=>0) );
                    Service::getInstance('goods')->change( $gid, array( 'checkResult'=>1 ) ); 
                }
                if ( $smttype ==3 ) {
                    Service::getInstance('goods')->delPushGoods( $pid );
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
                                        $original = Util::getDir( $dir,$hash ).$hash.'_image.jpg';
                                        if( move_uploaded_file( $avatar , $original ) ){
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
                $this->flash('/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage,'编辑成功');
            } else {
                $this->flash('/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage,'编辑失败',1);
                return false;
            }
	    } else {
            $id = $this->getQuery('id');
            $channel = $this->getQuery('channel','');
            $keyword = $this->getQuery('keyword','');
            $perpage = $this->getQuery('perpage',30);
            $page = $this->getQuery('page',1);
            if ( !$id ) {
                $this->flash( '/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage, '参数不合法' ,1);
            }
            $pushinfo = Service::getInstance('push')->getPushInfo( $id );
            if ( !$pushinfo ) {
                $this->flash( '/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage, '未查到数据,请检查请求是否正确',1);
            }
            $Info = Service::getInstance('goods')->getGoodsInfoById( $pushinfo['goodsId'] );
            if ( !$Info ) {
                $this->flash( '/checkpush/index?&page='.$page.'&keyword='.$keyword.'&channel='.$channel.'&perpage='.$perpage, '该商品不存在！',1);
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
                        $data = Service::getInstance('goods')->getAttrValueByAttrId($v['key']['id']);
                        $arr[$k]['value']['param'] = $data;
                    }
                }
            }
            $Info['attribute'] = $arr ? $arr : '';
            $Info['pid'] = $id;
            
            $this->_view->page = $page;
            $this->_view->perpage = $perpage;
            $this->_view->channel = $channel;
            $this->_view->keyword = $keyword;
            $this->_view->Info = $Info;
            $this->_view->dis = $dis;
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
                $res = Service::getInstance('goods')->editPush( $id, array('status'=>0) );
                if ( $res >= 0 ) {
                    $this->flash( '/checkpush/index', '设置成功' );
                } else {
                    $this->flash( '/checkpush/index', '设置失败' );
                }
            } elseif ( $status == 2 ) {
                $res = Service::getInstance('goods')->delPushGoods( $id );
                if ( $res ) {
                    $this->flash( '/checkpush/index', '设置成功' );
                } else {
                    $this->flash( '/checkpush/index', '设置失败' );
                }
            }
            return false;
        }
        $id = $this->getQuery('id');
        $goods = Service::getInstance('push')->getPushInfo( $id );
        $this->_view->data = $goods;
    }

}