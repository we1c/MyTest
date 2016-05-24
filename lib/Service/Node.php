<?php

class Service_Node extends Service
{
	//节点列表
	public function nodeList(){
		$sql = " SELECT id,name,con,act,level,pid,sort,status FROM `node` ORDER BY sort ASC ";

		$data = $this->db->fetchAll( $sql );
/*		foreach ($data as $k => $v) {
			if ( '0' == $v['pid'] ) {
				$arr[$v['id']] = $v;
				foreach ($data as $k2 => $v2) {
					if ( $v2['pid'] == $v['id']) {
						$arr[$v['id']]['son'][$v2['id']] = $v2;
						foreach ($data as $k3 => $v3) {
							if ( $v3['pid'] == $v2['id']) {
								$arr[$v['id']]['son'][$v2['id']]['son'][] = $v3;
							}
						}
					}
				}
			}
		}*/
		$arr = $this->getTree($data);
		return $arr;

	}

    //添加
    public function add( $data ) {
	    $this->db->insert('node', $data);
	    return $this->db->lastInsertId();
	}

	//编辑
	public function edit( $data, $id ) {
	    return $this->db->update( '`node`' , $data, ' id ='.$id);
	}

	//pid查询节点
	public function getNodeByPid( $id ) {
		$sql = " SELECT id FROM `node` WHERE pid = {$id} ";
		$res = $this->db->fetchAll( $sql );
		return $res;
	}

	//删除
	public function delete( $id ) {
		$res = $this->db->delete( '`node`' ,' id = '.$id );
		return $res;
	}

	//获取一二级菜单
	public function getMenu(){
		$sql = " SELECT id,name,con,act,level,pid FROM `node` WHERE `level` IN (1,2) ORDER BY sort ASC ";
		$data = $this->db->fetchAll( $sql );
		$arr = $this->getTree($data);
		return $arr;
	}

	//获取节点详情
	public function getNodeInfo( $id ){
		$sql = " SELECT * FROM `node` WHERE id = {$id}";
		return $this->db->fetchRow( $sql );
	}

	public function getTree($data,$pid = 0){
		$result = array();
		foreach ($data as $k => $v) {
			if( $v['pid'] == $pid ){
				$v['son'] = $this->getTree($data,$v['id']);
				$result[] = $v;
			}
		}
		return $result;
	}

	//通过权限获取菜单
	public function getMenuByRole( $role ){
		if (!$role) return array();
		if (is_array($role)) {
			$roles = implode(',',$role);
		}
		$sql = " SELECT 
				distinct id,name,con,act,level,pid 
				FROM `node` AS A 
				LEFT JOIN `role_node` AS B 
				ON A.id = B.node_id 
				WHERE A.`level` IN (1,2) AND A.`isMenu` = 1 AND B.`role_id` IN ( $roles ) ORDER BY A.sort ASC ";
		$data = $this->db->fetchAll( $sql );
		$arr = $this->getTree($data);
		return $arr;
	}

	public function getMenuLevel3( $role ){
		if (!$role) return array();
		if (is_array($role)) {
			$roles = implode(',',$role);
		}else{
			$roles = $role;
		}
		$sql = " SELECT 
				id,name,con,act,level,pid 
				FROM `node` AS A 
				LEFT JOIN `role_node` AS B 
				ON A.id = B.node_id 
				WHERE A.`level` IN (3) AND B.`role_id` IN ( $roles ) ORDER BY A.sort ASC ";
		$data = $this->db->fetchAll( $sql );
		return $data;
	}

	public function getParentMenuByConAndAct( $con,$act ){
		$sql = " SELECT pid FROM `node` WHERE con = '{$con}' AND act = '{$act}' AND `level` = 3 ";
		$pid = $this->db->fetchOne( $sql );
		if ( !$pid ) {
			return array('con'=>$con,'act'=>$act);
		} else {
			$sql = " SELECT con,act FROM `node` WHERE id = {$pid} ";
			$res = $this->db->fetchRow( $sql );
			return $res;
		}
	}
}