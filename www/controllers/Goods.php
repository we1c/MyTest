<?php

class GoodsController extends BaseController
{
    public function init()
    {	
        $this->db = Db::getInstance();
        $user = Yaf_Registry::get('user');
        $this->_view->user = $user;
        $this->_view->_module = 'index';
        $this->_view->_moduleName = '首页';
        $this->_view->_action = $this->getRequest()->getActionName();
        $this->_view->_controllerName = $this->getRequest()->getControllerName();
        $token = empty($_COOKIE['color_'.$user['id']]) ? '' : $_COOKIE['color_'.$user['id']];
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = Util::strcode($token, $key, 'decode');
        $this->_view->color = $token;
    }
    
    public function listAction()
    {
        $search = $this->getPost('search');
        $user = Yaf_Registry::get('user');
        $role = Yaf_Registry::get('role');
        $list = Service::getInstance('goods')->goodsList($user['shopId'],$role,$search);
        $this->_view->list = $list;
    }
    public function stockAction() {
        $list = Service::getInstance('goods')->getStock();
        $this->_view->list = $list;
    }

    //上传商品
    public function uploadAction(){
        if(Yaf_Registry::get('role')=='2'){
            $this->flash('/goods/list','管理员暂时无法添加商品');
            exit;
        }
        if( $this->isPost() ){
            $goodArr = array();
            $goodArr['name'] = $this->getPost('name');
            $goodArr['tags'] = implode(" ", $this->getPost('tags') );
            $goodArr['price'] = $this->getPost('price');
            $goodArr['marketprice'] = $this->getPost('marketprice');
            $goodArr['baseprice'] = $this->getPost('baseprice');
            
            $goods_key = $this->getPost('goods_key');
            $goods_val = $this->getPost('goods_val');
            $attribute = array();
            if($goods_key){
                foreach ($goods_key as $k=>$v){
                    $attribute[] = array($v,$goods_val[$k]);
                }
            }
            
            $goodArr['attribute'] = serialize($attribute);
            $goodArr['uploader'] = Yaf_Registry::get('uid');
            $goodArr['recommend'] = $this->getPost('recommend');
            $goodArr['intro'] = htmlspecialchars($this->getPost('intro'));
            
            //TODO 管理员bug
            if(Yaf_Registry::get('role')=='2'){
                $shopId = $goodArr['shopId'] = 1;
            }
            if(Yaf_Registry::get('role')=='1'){
                $shopId = $goodArr['shopId']= Service::getInstance('shop')->getshopIdbyUid(Yaf_Registry::get('uid') ) ;
            }
            $goodId = Service::getInstance('goods')->add($goodArr);
            unset($goodArr);
            $imageS = $this->getPost('image');
            
            if(!$imageS){
                $this->flash('/goods/upload','请上传商品图片');
            }
           $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
           foreach ($imageS as $k=>$v){
               $temp = intval($k+1);
               $sql .="('{$goodId}', '{$v}', $temp ),";
           }
           $sql = substr($sql, 0,-1);
          
           Service::getInstance('goods')->addgoods_images($sql);
           
            $tagS = $this->getPost('tags');
            if($tagS){
                foreach ($tagS as $k=>$v){
                $tagId = Service::getInstance('tags')->getTagIdbyname($v); //标签
                Service::getInstance('tags')->addTagIndex($tagId,$goodId,$shopId);
                }
            }
            $this->flash('/goods/stock','添加成功');
        }
    }
    //上传商品图片@/goods/uploadheadimg
    public function uploadgoodsimgAction(){ 
        if(!$_FILES['file']['error'])
        {
            $avatar = $_FILES['file']['tmp_name'];
            $hash = md5($avatar);
            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
             
            if ( move_uploaded_file( $avatar, Util::getDir( $dir ,$hash ) . $hash . "_image.jpg") )
            {
                $Res = "";
                $Res = array(
                    'imgurl'  => Service::getInstance('goods')->getAvata( $hash ),
                    'path'=> $hash
                );
                 
                $this->respon( 1 , $Res );
            }
            else
            {
                $this->respon( 0 , array("上传失败") );
            }
             
        }
         
        $this->respon( 0 , $_FILES['file']['error']."请选择您要上传的图片!" );
    }
    
    public function delfileAction()
    {
   
    }
    
    //上架商品
    public function groundAction() {
        if($this->isPost()) {
            $id = $this->getPost('id');
            $res = Service::getInstance('goods')->groundGoods($id);
            if($res) {
                $this->respon(1,'成功');
            } else {
                $this->respon(0,'操作失败');
            }
        }
       return false;
    }
    
    //下架商品
    public function downgoodsAction() {
        if($this->isPost()) {
            $data = $this->getPost('down');
            $id = $this->getPost('goodsId');
            if(!$id) $this->respon(0,'参数错误');
            $arr = array();
            $arr['status'] = $data;
            $arr['id'] = $id;
            if($data == 4) {
                $price = $this->getPost('price');
                if(!$price) {
                    $this->respon(0,'请填写出售价格');
                }
                $arr['price'] = $price;
            
            }
            $res = Service::getInstance('goods')->downGoods($arr);
            if($res) {
                $this->respon(1,'成功');
            } else {
                $this->respon(0,'操作失败');
            }
        }
        return false;
        
    }
    
    //取消推荐
    public function unrecommAction() {
        $id = $this->getPost('id');
        $res = Service::getInstance('goods')->unrecomm($id);
        if($res) {
            $this->respon(1,'取消成功');
        } else {
            $this->respon(0,'取消失败');
        }
    }
    //推荐
    public function recommAction() {
        $id = $this->getPost('id');
        $res = Service::getInstance('goods')->recomm($id);
        if($res) {
            $this->respon(1,'推荐成功');
        } else {
            $this->respon(0,'推荐失败');
        }
    }
    
    public function setcolorAction() {
        $user = Yaf_Registry::get('user');
        $data = $this->getPost('data');
        if($this->_setCookie($data,$user['id'])) {
            $this->respon(1,'成功');
        } else {
            $this->respon(0,'失败');
        }
        return false;
        
    }
    
    protected function _setCookie($data,$uid, $keep = true)
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = Util::strcode($data, $key, 'encode');
        $expired = $keep ? time() + 86400 * 30 : 0;
        setcookie('color_'.$uid, $token, $expired, '/');
    }
   
}