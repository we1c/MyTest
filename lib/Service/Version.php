<?php

class Service_Version extends Service{

	public function versionList($page, $perpage,$keyword){
		$map = '';
	    if($keyword !='')
	    {
	        $map = " WHERE versionCode like '%{$keyword}%' or descript like '%{$keyword}%' ";
	    }
	    $sql = " SELECT * FROM app_version $map ORDER BY id DESC ".$this->db->buildLimit($page, $perpage);
	    $data['list'] = $this->db->fetchAll($sql);
	    $sql = "SELECT count(*) FROM app_version $map";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
		// $list = $this->db->fetchAll(" SELECT * FROM app_version ORDER BY versionCode DESC LIMIT 5 ");
		// return $list;
	}

	public function addVersion ($version){
		$this->db->insert('app_version',$version);
		return $this->db->lastInsertId();
	}

	public function getVersionInfoById ($id){
		$Info =$this->db->fetchAll("SELECT * FROM app_version WHERE id=".$id);
		return $Info[0];
	}

	public function edit($data,$id){
	    return $this->db->update('app_version', $data,' id='.$id);
	}

	public function delVersion($id){
		return $this->db->delete('app_version','id='.$id);
	}

}