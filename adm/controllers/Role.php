<?php
class RoleController extends BaseController
{
    public function init()
    {
        parent::init();
        if(!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    
    }

    //角色列表
    public function indexAction()
    {
        $data = Service::getInstance('role')->lists( );

        $this->_view->list = $data;
    }
    
    //添加角色
	function addAction(){
	    if ($this->isPost()) {
	    	$name = $this->getPost('name');
	    	$pid = $this->getPost('pid');
	    	$sort = $this->getPost('sort');
	    	$status = $this->getPost('status');
	    	$permission = $this->getPost('permission');
	    	$data = array(
	    			"name"=>$name,
	    			"pid"=>$pid,
	    			"sort"=>$sort,
	    			"status"=>$status
	    			);
	    	$roleId = Service::getInstance('role')->add($data);
	    	if ( $roleId && $permission ) {
                $res = Service::getInstance("role")->addRoleNode($roleId,$permission);
                if ($res) {
                	$this->flash('/role/index', '添加成功');
                }else{
                	$this->flash('/role/index', '添加失败',1);
                }
	    	}
	    }
	    $menu = Service::getInstance('node')->nodeList();
	    $role = Service::getInstance('role')->lists();
	    $parentRole = Service::getInstance('role')->getParentRole(0);
	    $this->_view->role = $role;
	    $this->_view->menu = $menu;
		$this->_view->myNode = array();
		$this->_view->parentRole = $parentRole;
	}
	
	//删除角色
	function deleteAction()
	{
	    $id = $this->getQuery("id",0);
	    if(!$id) return;
	    // $rs = $this->db->delete('Role',' Id = '.$id);
	    $check = Service::getInstance('role')->checkDelete($id);
	    if ($check) {
	    	$this->flash('/role/index','该角色有子级，删除失败',2);
	    }
	    $res = Service::getInstance('role')->delete($id);
	    if ($res) {
	    	$this->flash('/role/index','删除成功');
	    }else{
	    	$this->flash('/role/index','删除失败',1);
	    }
	}
/*	
	function insertAction(){
  		$name = $this->getPost('name');
  		if(!$name){
  		  $this->flash('/role/add','角色名称不能为空'); 
  		  exit;
  		}
  		$permission = $this->getPost('permission');
  		$sort = $this->getPost('sort');
  		$menu = implode(',', $permission);
  		$res = Service::getInstance('role')->insert($name,$menu,$sort);
  		if( $res )
  		{
  			$this->flash('/role/index', '添加成功');
  			exit;
  		}
  	}*/

  	//编辑角色
	function editAction(){
		if ($this->isPost()) {
	    	$id = $this->getPost('id');
	    	$name = $this->getPost('name');
	    	$pid = $this->getPost('pid');
	    	$sort = $this->getPost('sort');
	    	$status = $this->getPost('status');
	    	$permission = $this->getPost('permission');
	    	$data = array(
	    			"name"=>$name,
	    			"pid"=>$pid,
	    			"sort"=>$sort,
	    			"status"=>$status
	    			);
	    	$result = Service::getInstance('role')->edit($id,$data);
	    	if ( $permission ) {
	    		$del = Service::getInstance("role")->delRoleNode($id);
                $res = Service::getInstance("role")->addRoleNode($id,$permission);
                if ($res) {
                	$this->flash('/role/index', '添加成功');
                }else{
                	$this->flash('/role/index', '添加失败',1);
                }
	    	}
	    }
	    $id = $this->getQuery('id',0);
	    $menu = Service::getInstance('node')->nodeList();
	    $role = Service::getInstance('role')->lists();
	    $info = Service::getInstance('role')->getRow($id);
	    $parentRole = Service::getInstance('role')->getParentRole($info['pid']);
	    $myNode = Service::getInstance('role')->getMyNode($id);
	    $this->_view->id = $id;
	    $this->_view->menu = $menu;
	    $this->_view->role = $role;
		$this->_view->info = $info;
		$this->_view->parentRole = $parentRole;
		$this->_view->myNode = $myNode;
	}


/*	function updateAction(){
	   
		$id = $this->getPost('id');
	    $name = $this->getPost('name');
	    $sort = $this->getPost('sort');
	    if(!trim($name)){
	        $this->flash('/role/edit?id='.$id,'角色名称不能为空');
	        exit;
	    }
  		$permission = $this->getPost('permission'); //一级
  		$menu = implode(',', $permission);
		Service::getInstance('role')->update($id,$name,$menu,$sort);
		$this->flash('/role/index', '修改成功');
		exit;
		
	}*/

	//角色用户列表
	function developerAction(){
		$this->_view->list = Service::getInstance('role')->getDeveloper($this->getQuery('id'));
		$this->_view->id = $this->getQuery('id');
	}
	
	//给角色分配用户
	function adduserAction() {
	    if($this->isPost()) {
	        $roleid = $this->getPost('roleid');
	        $userid = $this->getPost('user');
	        $data = array("user_id"=>$userid,"role_id"=>$roleid);
	        $res = Service::getInstance('role')->adduser($data);
	        if($res) {
	            $this->flash('/role/developer?id='.$roleid, '添加成功');
	        }else{
	            $this->flash('/role/developer?id='.$roleid, '添加失败',1);
	        }
	    } else {
	        $id = $this->getQuery('id');
	        $role = Service::getInstance('role')->getRole($id);
	        $user = Service::getInstance('role')->getUser();
	        $this->_view->user = $user;
	        $this->_view->role = $role;
	    }
	}

	//删除用户角色
	function deleteroleAction() {
	    $id = $this->getQuery("id",0);
	    $rid = $this->getQuery("rid",0);
	    if(!$id) return;
	    $res = Service::getInstance('role')->deleterole($id,$rid);
        $this->flash('/role/developer/?id='.$rid,'删除成功');
        exit;
	}

	//切换上级部门 改变可选权限
	public function getParentRoleAction(){
		if ( $this->isPost() ) {
			$pid = $this->getPost('pid');
			$data = Service::getInstance('role')->getParentRole($pid);
			$this->respon(1,$data);
		}
	}
}
