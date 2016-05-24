<?php
class NodeController extends BaseController
{
    public function init()
    {
        parent::init();
        $my = Yaf_Registry::get('developer');
        $this->_view->myId = $my['roleId'];
    }

    public function indexAction()
    { 
        $data = Service::getInstance('node')->nodeList();
        $this->_view->data = $data;
    }
    
    //添加
    public function addAction(){
        if($this->isPost()){
            $name 	= $this->getPost('name');
            $pNode 	= $this->getPost('pNode');
            $con 	= $this->getPost('con','');
            $act 	= $this->getPost('act','');
            $status = $this->getPost('status');
            $sort 	= $this->getPost('sort');
            $isMenu = $this->getPost('isMenu');

            if ( '0' == $pNode ) {
            	$level 	= 1;	
            }else{
            	$exp = explode('-',$pNode);
            	$level 	= $exp[0] + 1;
            	$pid 	= $exp[1];
            }

            $data = array(
            		"name"=>$name,
            		"pid"=>$pid,
            		"con"=>$con,
            		"act"=>$act,
            		"status"=>$status,
            		"sort"=>$sort,
            		"level"=>$level,
                    "isMenu"=>$isMenu
            		);

            $res = Service::getInstance('node')->add( $data );
            if( $res ){
                $this->flash( '/node/index', '添加节点成功' );
            }else{
                $this->flash( '/node/index', '添加节点失败' );
            }
        }
        $menus = Service::getInstance('node')->getMenu();
        $this->_view->menus = $menus;
    }
    
    //编辑
    public function editAction(){
         if($this->isPost()){

            $id     = $this->getPost('id');
            $name 	= $this->getPost('name');
            $pNode 	= $this->getPost('pNode');
            $con 	= $this->getPost('con','');
            $act 	= $this->getPost('act','');
            $status = $this->getPost('status');
            $sort 	= $this->getPost('sort');
            $isMenu = $this->getPost('isMenu');

            if ( '0' == $pNode ) {
            	$level 	= 1;	
            }else{
            	$exp = explode('-',$pNode);
            	$level 	= $exp[0] + 1;
            	$pid 	= $exp[1];
            }

            $data = array(
            		"name"=>$name,
            		"pid"=>$pid,
            		"con"=>$con,
            		"act"=>$act,
            		"status"=>$status,
            		"sort"=>$sort,
            		"level"=>$level,
                    "isMenu"=>$isMenu
            		);

            $res = Service::getInstance('node')->edit( $data,$id );
            if( $res ){
                $this->flash( '/node/index', '添加节点成功' );
            }else{
                $this->flash( '/node/index', '添加节点失败' );
            }
        }
        $id = $this->getQuery('id');
        $menus = Service::getInstance('node')->getMenu();
        $info = Service::getInstance('node')->getNodeInfo( $id );
        $this->_view->menus = $menus;
        $this->_view->info = $info;
    }
    
    //删除节点
    public function delAction(){
        if ( $this->isPost() ) {
            $id  = $this->getPost('id');
            if ( !$id ) $this->respon( 0, '参数异常');
            $check = Service::getInstance('node')->getNodeByPid( $id );
            if ( !$check ) {
                $res = Service::getInstance('node')->delete( $id );
                if ( $res ) {
                    $this->respon( 1, '成功');
                } else {
                    $this->respon( 0, '失败');
                }
            } else {
                $this->respon( 0,'此节点包含下级节点' );
            }
        }
    }

}