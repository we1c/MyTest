<?php

class Service_Role extends Service
{

    //角色列表
	  public function lists( )
	  {
        $sql = "SELECT * FROM role ORDER BY sort";
        $data = $this->db->fetchAll($sql);
        $arr = $this->getTree($data);
        return $arr;
   	}

    //获取所有角色
   	function roleall(){
   	    return $this->db->fetchAll("SELECT id,name FROM role ORDER BY sort asc ");
   	}

    //获取角色信息
   	function getRow($id){
   		return $this->db->fetchRow("SELECT * FROM role WHERE id=$id");
   	}

    //获取父级权限
    public function getParentRole($pid){
      if ( $pid == 0 ) {
        $sql = "SELECT id AS node_id FROM node";
      }else{
        $sql = "SELECT node_id FROM role_node WHERE role_id = ".$pid;
      }
      $roleArr = $this->db->fetchAll( $sql );
      $data = array();
      foreach ($roleArr as $value) {
        $data[] = $value['node_id'];
      }
      return $data;
    }

/*   	function insert($name,$permission,$sort){
   		return $this->db->insert('role', array('name'=>$name,'menus'=>$permission,'sort'=>$sort));
   	}*/

    //添加角色
    public function add( $data ) {
        $this->db->insert( '`role`' , $data);
        return $this->db->lastInsertId();
    }

    //编辑角色
    function edit($id,$data){
      return $this->db->update('role', $data,"id=".$id);
    }

    //判断是否有子级角色
    public function checkDelete($roleId){
      $sql = "SELECT count(*) FROM role WHERE pid = ".$roleId;
      return $this->db->fetchOne($sql);
    }

    //删除角色 并删除角色用户表
    public function delete($roleId){
      $role = $this->db->delete('role','id='.$roleId);
      $userRole = $this->db->delete('user_role', 'role_id='.$roleId);
      return $userRole;
    }

/*   	function update($id,$name,$permission,$sort){
   		return $this->db->update('role', array('name'=>$name,'menus'=>$permission,'sort'=>$sort),"id=".$id);
   	}*/

    //获取角色用户
   	function getDeveloper($roleId){
      $sql = 'SELECT B.* FROM user_role AS A LEFT JOIN developers AS B ON B.id = A.user_id WHERE A.role_id='.$roleId;
   		return $this->db->fetchAll( $sql );
   	}

    //获取用户角色
    function getRoleByUser($userId){
      $sql = 'SELECT A.role_id FROM user_role AS A LEFT JOIN developers AS B ON B.id = A.user_id WHERE A.user_id='.$userId;
      return $this->db->fetchAll( $sql );
    }
   	
    //获取角色名称
   	function getRole($id){
   	    return $this->db->fetchRow("SELECT id,name FROM role WHERE id=$id");
   	}

    //获取不属于该角色的用户
   	function getUser(){
   	    return $this->db->fetchAll("SELECT distinct A.* FROM developers AS A LEFT JOIN user_role AS B ON A.id = B.user_id ");
   	}

    //给角色分配用户
   	function adduser($data){
   	    return $this->db->insert('user_role', $data);
   	}

    //删除用户角色
   	function deleterole($userId,$roleId){
   	    return $this->db->delete('user_role', array('user_id='.$userId,'role_id='.$roleId));
   	}

    function getTree($data,$pid = 0){
      $result = array();
      foreach ($data as $k => $v) {
        if( $v['pid'] == $pid ){
          $v['son'] = $this->getTree($data,$v['id']);
          $result[] = $v;
        }
      }
      return $result;
    }

    //添加角色节点
    public function addRoleNode($roleId,$permission){
        $sql = " INSERT INTO `role_node` (`role_id`,`node_id`) VALUES ";
        foreach ($permission as $k=>$v){
            if ($v != '') {
                $sql .="('{$roleId}', {$v} ),";
            }
        }
        $sql = rtrim($sql, ',');
        return $this->db->query($sql);
    }

   	//删除角色节点
    public function delRoleNode( $roleId ) {
        return $this->db->delete( 'role_node', ' role_id = '.$roleId);
    }

    //添加角色节点
    public function addUserRole($userId,$roleId){
        $sql = " INSERT INTO `user_role` (`user_id`,`role_id`) VALUES ";
        foreach ($roleId as $k=>$v){
            if ($v != '') {
                $sql .="('{$userId}', {$v} ),";
            }
        }
        $sql = rtrim($sql, ',');
        return $this->db->query($sql);
    }

    //删除用户角色
    public function delUserRole( $userId ) {
        return $this->db->delete( 'user_role', ' user_id = '.$userId);
    }

    //获取角色节点
    public function getMyNode( $roleId ){
      $sql = "SELECT node_id FROM `role_node` WHERE role_id = ".$roleId;
      $data = $this->db->fetchAll( $sql );
      $nodeIds = array();
      foreach ($data as $row) {
        $nodeIds[] = $row['node_id'];
      }
      return $nodeIds;
    }
}
