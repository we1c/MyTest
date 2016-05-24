<?php

class Service_Tags extends Service
{
	private $error;
	public function getError(){
		return $this->error;
	}
	
	public function getTagIdbyname($name){
	    $tagid = $this->db->fetchOne("select id from tag where name = '".$name."'");
	    if($tagid){
	        $this->db->query("UPDATE `tag` SET `num` = `num`+1 WHERE `tag`.`id` =$tagid;");
	        return $tagid;
	    }else{
	       $this->db->insert('tag', array('name'=>$name,'num'=>1) );
	       return $this->db->lastInsertId();
	    }
	}
	
	public function addTagIndex($tagId,$goodsId,$shopId) {
	    $this->db->insert('tag_index', array('tagId'=>$tagId,'goodsId'=>$goodsId,'shopId'=> $shopId) );
	}
	
	public function getTags() {
	    $data = $this->db->fetchAll('select * from tag order by num desc');
	    return $data;
	}
}