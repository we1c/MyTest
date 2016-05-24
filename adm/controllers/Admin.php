<?php
class AdminController extends BaseController
{
    public function init()
    {
        parent::init();
        $my = Yaf_Registry::get('developer');
        $this->_view->myId = $my['roleId'];
    }

    public function indexAction()
    { 
        $perpage = $this->getQuery('perpage',15);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
        $data = Service::getInstance('Admin')->adminlist($page,$perpage,$keyword);
        $list = $data['list'];
        foreach ( $list as $k=>$v ) {
            if ( $v['disId'] ) {
                $disarr = array();
                foreach ( explode(',', $v['disId']) as $val ) {
                    $dis = Service::getInstance('distributor')->getInfoById( $val );
                    $disarr[] = $dis['name'];
                }
                 $list[$k]['disName'] = implode(',', $disarr);
            } else {
                $list[$k]['disName'] = '无';
            }
            $list[$k]['role'] = Service::getInstance('role')->getRoleByUser($v['id']);
        }
        $rolelist = Service::getInstance('role')->roleall();
        $roleArr = array();
        foreach ($rolelist as $k=>$v){
            $roleArr[$v['id']] = $v['name'];
        }
        $this->_view->rolelist = $roleArr;
        $this->_view->list = $list;
        // $url = $keyword ? '/admin/index?page=__page__&keyword='.$keyword : '/admin/index?page=__page__';
        // $this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
        $this->_view->total = $data['total'];
        $this->_view->keyword = $keyword;
        $this->_view->perpage = $perpage;
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('admin','index','keyword'=>$keyword,'perpage'=>$perpage));
        $this->_view->pagebar = $pageObj -> showPage();
    }
    
    //添加
    public function addAction(){
        if($this->isPost()){
            $account = $this->getPost('account','');
            $name = $this->getPost('name','');
            $pwd = $this->getPost('pwd','');
             
            if($account == '' || $name == '' || $pwd == ''){
                $this->error('请输入完整信息');
                return;
            }
            if( !Util::isValidMobile( trim( $account ) ) ) {
                $this->error('请输入正确手机号');
                return;
            }
            if( Service::getInstance('admin')->getInfoByAccount( $account ) ){
                $this->error('账号已经被注册');
                return ;
            }
            $disId = $this->getPost('disId');
            $admin['name'] = $name;
            $admin['account'] = $account;
            $admin['pwd'] = md5($pwd);
            $admin['createTime'] = time();
            $roleId = $this->getPost('roleId');
            $admin['disId'] = $disId ? implode(',', $disId) : 0;
            $userId = Service::getInstance('admin')->add( $admin );
            //$addRole = Service::getInstance('role')->adduser(array("role_id"=>$roleId,"user_id"=>$userId));
            if ($userId && $roleId) {
                $res = Service::getInstance("role")->addUserRole($userId,$roleId);
                if( $res ){
                    $this->flash( '/admin/index', '添加管理员成功' );
                }else{
                    $this->flash( '/admin/index', '添加管理员失败',1 );
                }
            }
        }
        $this->_view->distlist = Service::getInstance('distributor')->getAllDis();
        $this->_view->rolelist = Service::getInstance('role')->roleall();
        $this->_view->role = array();
    }
    
    //编辑
    public function editAction(){
        if($this->isPost()){
            $uid = $this->getPost('id',0);
            if ( !$uid ) {
                $this->flash( '/admin/index', '管理员信息错误' );
            }
            $account = trim( $this->getPost('account','') );
            $name = trim( $this->getPost('name','') );
            $pwd = trim( $this->getPost('pwd','') );
            if($account == '' || $name == ''){
                $this->error('请输入 姓名或 账号');
                $data = Service::getInstance('admin')->getInfoById($uid);
                $this->_view->info = $data;
                return;
            }
            if( $pwd ) {
                $admin['pwd'] = md5( $pwd );
            }
            $disId = $this->getPost('disId');
            $admin['name'] = $name;
            $admin['account'] = $account;
            $admin['disId'] = $disId ? implode(',', $disId) : 0;
            $roleId = $this->getPost('roleId');
            $edit = Service::getInstance('admin')->edit($admin,$uid);
            if ( $eidt >= 0 && $roleId ) {
                $del = Service::getInstance("role")->delUserRole($uid);
                $res = Service::getInstance("role")->addUserRole($uid,$roleId);
                if($res){
                    $this->flash( '/admin/index', '编辑管理员成功' );
                }else{
                    $this->flash( '/admin/index', '编辑管理员失败' );
                }
            }

        }
        $id = $this->getQuery('id');
        $this->_view->distlist = Service::getInstance('distributor')->getAllDis();
        $this->_view->rolelist = Service::getInstance('role')->roleall();
        $data = Service::getInstance('admin')->getInfoById($id);
        $roles = Service::getInstance('role')->getRoleByUser($id);
        $role = array();
        foreach ($roles as $v) {
            $role[] = $v['role_id'];
        }
        $this->_view->info = $data;
        $this->_view->role = $role;
    }
    
    public function delAction()
    {
        return false;
        //TODO 删除
        $id = $this->getQuery('id');
        if(!$id)return false;
        $this->db->update('users',array('status'=>'1'),'id = '.$id);
        $this->flash('/admin/index');
    }
    
    //禁用
    public function disableAction() {
        $id =$this->getQuery('id');
        $res = Service::getInstance('admin')->disable($id);
        if( $res >= 0 ) {
            $this->flash('/admin/index','禁用成功');
        } else {
            $this->flash('/admin/index','禁用失败');
        }
    }
    
    //激活
    public function enableAction() {
        $id = $this->getQuery('id');
        $res = Service::getInstance('admin')->enable($id);
        if( $res >= 0 ) {
            $this->flash('/admin/index','激活成功');
        } else {
            $this->flash('/admin/index','激活失败');
        }
    }
    
}