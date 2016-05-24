<?php

class Service_Admin extends Service
{
    //列表
    public function adminlist($page, $perpage,$keyword)
	{
	    $map = " WHERE status = 1 ";
	    if($keyword!='')
	    {
	        $map .= " AND name LIKE '%{$keyword}%'";
	    }
	    $sql = "SELECT * FROM developers $map ORDER BY id DESC".$this->db->buildLimit($page, $perpage);
	    $data['list'] = $this->db->fetchAll($sql);
	    $sql = "SELECT count(*) FROM developers $map";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
	}
	
	//添加管理员
	public function add( $data ) {
	    $this->db->insert('developers', $data);
	    return $this->db->lastInsertId();
	}
	
	//编辑管理员
	public function edit( $admin,$uid ) {
	    return $this->db->update('developers', $admin, ' `id` ='.$uid);
	}
	
	//管理员信息
	public function getInfoById($id) {
	    $data = $this->db->fetchRow('SELECT * FROM developers WHERE `id` ='.$id);
	    return $data;
	}
	//管理员信息
	public function getDevNameById($id) {
		return $this->db->fetchOne('SELECT name FROM developers WHERE `id` ='.$id);
	}
	public function getDevIdByName( $name ){
		return $this->db->fetchOne(" SELECT id FROM developers WHERE name = '{$name}'" );
	}
	//管理员信息
	public function getInfoByAccount($account) {
	    $data = $this->db->fetchRow('SELECT * FROM developers WHERE `account` ='.$account);
	    return $data;
	}

    //禁用
    public function disable($id) {
        return $this->db->update('developers', array('status'=>1),' id='.$id);
    }
    
    //激活
    public function enable($id) {
        return $this->db->update('developers', array('status'=>0),' id='.$id);
    }
    
}