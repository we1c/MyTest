<?php

class Service_Blog extends Service
{
	private $error;
	public function getError(){
		return $this->error;
	}
	
	public function getList($page, $perpage,$keyword)
	{
	   
	    $sql = "SELECT A.id,A.name AS aname,A.createTime,A.uid,A.devUid,A.shop,A.number,C.name AS gname,C.goodsNo,C.code FROM user_action_log AS A LEFT JOIN goods AS C ON C.id=A.gid ORDER BY A.id DESC".$this->db->buildLimit($page, $perpage);
	  
	    $list = $this->db->fetchAll($sql);
	    
	    foreach ($list as $k=>$v) {
	        if ($v['uid']) {
	            $user = Service::getInstance('user')->getUserInfoById($v['uid']);
	            $list[$k]['uname'] = $user['name'];
	        } else {
	            $list[$k]['uname'] = '';
	        }
	        if ($v['devUid']) {
	            $dev = Service::getInstance('admin')->getInfoById($v['devUid']);
	            $list[$k]['dname'] = $dev['name'];
	        } else {
	            $list[$k]['dname'] = '';
	        }
	        if ($v['shop']) {
	            $shop = Service::getInstance('shop')->getShopinfo($v['shop']);
	            $list[$k]['sname'] = $shop['name'];
	        } else {
	            $list[$k]['sname'] = '';
	        }
	    }
	    
	    $data['list'] = $list;
	     
	    $sql = "SELECT count(*) FROM user_action_log ";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
	}
	
	//添加日志
	public function add_user_log( $data ) {
	    $this->db->insert('user_action_log', $data);
	}
}