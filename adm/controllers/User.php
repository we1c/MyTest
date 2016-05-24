<?php

class UserController extends BaseController
{
    public function init()
    {
        parent::init();
        if(!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    }
	public function indexAction(){
		$perpage = 15;
		$page = $this->getQuery('page', 1);
		$keyword = $this->getQuery('keyword', '');
		$data = Service::getInstance('user')->userlist($page,$perpage,$keyword);
		$this->_view->list = $data['list'];
		$url = $keyword ? '/user/index?page=__page__&keyword='.$keyword : '/user/index?page=__page__';
		$this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
	}
	
	public function editAction(){
	    if($this->isPost()) {
	        $uid = $this->getPost('Uid',0);
	        $state = $this->getPost('State','');
	        $result = Service::getInstance('User')->editUserState($uid,$state);
	        if($result >= 0) {
	            $this->flash('/user','修改成功');
	        } else {
	            $this->error('修改失败');
	        }
	    } else {
	       $id = $this->getQuery('Id');
	       $data = Service::getInstance('User')->getUserinfo($id);
	       $this->_view->data = $data;
	    }
	}
   
    function delAction(){
        return;
    	$id = $this->getQuery('Id');
    	if(Service::getInstance('User')->del($id)){
    		$this->flash("/user/index/","删除成功");
    	}
    }
}