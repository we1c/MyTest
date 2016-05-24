<?php

class SupplierController extends BaseController
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
		$data = Service::getInstance('supplier')->supplierlist($page,$perpage,$keyword);
		$this->_view->list = $data['list'];
		//$url = $keyword ? '/supplier/index?page=__page__&keyword='.$keyword : '/supplier/index?page=__page__';
		//$this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
        $this->_view->total = $data['total'];
        $this->_view->perpage = $perpage;
        $this->_view->keyword = $keyword;
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('supplier','index','keyword'=>$keyword,'perpage'=>$perpage));
        $this->_view->pagebar = $pageObj -> showPage( );
	}
	
	//添加
    public function addAction() {
        if($this->isPost()) {
            $name = trim($this->getPost('name',''));
            $account = trim($this->getPost('uphone',''));
            $pwd = trim($this->getPost('upwd',''));
            if(!Util::isValidMobile($account)) {
                $this->error('请输入正确手机号码');
                return;
            }
            if(!$pwd) {
                $this->error('请输入密码');
                return;
            }
            if( Service::getInstance('user')->getUserByaccount( $account ) ){
                $this->error('用户已存在');
                return;
            }else{
                $user['account'] = $account;
                $user['name'] = $name;
                $user['pwd'] = $pwd;
                $user['createTime'] = time();
                $user['role'] = '1';
                $uid = Service::getInstance('user')->add($user);
                if($uid) {
                    $this->flash("/supplier/index","添加成功");
                } else {
                    $this->flash("/supplier/index","添加失败");
                }
            }
        }
    }
    
    //编辑
    public function editAction() {
        if($this->isPost()) {
            $id = $this->getPost('id');
            $name = trim($this->getPost('name',''));
            $account = trim($this->getPost('uphone',''));
            $pwd = trim($this->getPost('upwd',''));
            if(!Util::isValidMobile($account)) {
                $this->error('请输入正确手机号码');
                $info = Service::getInstance('user')->getUserInfoById($id);
                $this->_view->info = $info;
                return;
            }
            $uinfo = Service::getInstance('user')->getUserByaccount( $account );
            if( $uinfo['id'] && $uinfo['id'] != $id ){
                $this->error($account.'已注册过，你不能在用了！');
                $info = Service::getInstance('user')->getUserInfoById($id);
                $this->_view->info = $info;
                return;
            }
            
            $user['account'] = $account;
            $user['name'] = $name;
            if( $pwd ) {
               $user['pwd'] = md5($pwd);
            }
            $res = Service::getInstance('user')->edit($user,$id);
            if($res >= 0) {
                $this->flash("/supplier/index","编辑成功");
            } else {
                $this->flash("/supplier/index","编辑失败");
            }
        }
        $uid = $this->getQuery('id');
        $info = Service::getInstance('user')->getUserInfoById( $uid );
        $this->_view->info = $info;
    }
    
    //激活用户
    public function enableAction() {
        $id = intval( $this->getQuery('id') );
        $res = Service::getInstance('supplier')->enable($id);
        $this->flash("/supplier/index"," 操作成功");
    }
    
    //禁用用户
    public function disableAction() {
        $id = intval( $this->getQuery('id') );
        $res = Service::getInstance('supplier')->disable($id);
        $this->flash("/supplier/index"," 操作成功");
    }
    
    //查看
    public function viewAction() {
        $id = intval($this->getQuery('id'));
        $info = Service::getInstance('supplier')->getUserInfoByUid($id);
        $this->_view->list = $info;
    }
  
}