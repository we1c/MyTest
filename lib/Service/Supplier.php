<?php

class Service_Supplier extends Service
{
	private $error;
	public function getError(){
		return $this->error;
	}
	
	public function supplierlist($page, $perpage,$keyword)
	{
	    $map = '';
	    if($keyword !='')
	    {
	        $map = " and (name like '%{$keyword}%' or account like '%{$keyword}%') ";
	    }
	    $sql = "SELECT * from users WHERE role = '1' $map ORDER BY id desc".$this->db->buildLimit($page, $perpage);
	    $list = $this->db->fetchAll($sql);
	    foreach ($list as $k=>$v) {
	        if($v['province'] && $v['city'] && $v['area']) {
	            $province = $this->getProCityAreaName($v['province']);
    	        $city = $this->getProCityAreaName($v['city']);
    	        $area = $this->getProCityAreaName($v['area']);
    	        $list[$k]['pname'] = $province['name'];
    	        $list[$k]['cname'] = $city['name'];
    	        $list[$k]['aname'] = $area['name'];
	        }
	        
	    }
	    $data['list'] = $list;
	     
	    $sql = "SELECT count(*) FROM users where role = '1' $map ";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
	}
	
	public function enable($id) {
	    $res = $this->db->update( 'users', array('status'=>'0'),' id=' . $id );
	    return $res;
	}
	
	
	public function disable($id) {
	    $res = $this->db->update( 'users', array('status'=>'1'),' id=' . $id );
	    return $res;
	}
	
	public function getUserInfoByUid($id)
	{
	    $sql = "SELECT * FROM users WHERE id=".$id;
	    $data = $this->db->fetchRow($sql);
	    unset($data['pwd']);
	    if ( $data['province'] && $data['city'] && $data['area'] ) {
	        $pname = $this->getProCityAreaName($data['province']);
    	    $cname = $this->getProCityAreaName($data['city']);
    	    $aname = $this->getProCityAreaName($data['area']);
    	    $data['pname'] = $pname['name'];
    	    $data['cname'] = $cname['name'];
    	    $data['aname'] = $aname['name'];
	    }
	    return $data;
	}
	
	public function edit($user,$id){
	    return $this->db->update('users', $user,' id='.$id);
	}
	
	public function getProvince() {
	    $sql = 'select id,name from city where parent_id =0 order by sort_num';
	    return $this->db->fetchAll($sql);
	}
	public function getCity($pid) {
	    $sql = 'select id,name from city where parent_id =' . $pid . ' order by sort_num';
	    return $this->db->fetchAll($sql);
	}
	public function getArea($pid) {
	    $sql = 'select id,name from city where parent_id =' . $pid . ' order by sort_num';
	    return $this->db->fetchAll($sql);
	}
	
	public function getProCityAreaName($id) {
	    $sql = 'select id,name from city where id = '.$id;
	    return $this->db->fetchRow($sql);
	}
	
	public function getSupplierInfo() {
	    $sql = "select id,name from users where role = '1'";
	    $data = $this->db->fetchAll($sql);
	    return $data;
	}
}