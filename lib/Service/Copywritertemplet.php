<?php

class Service_Copywritertemplet extends Service{

        //添加文案模板
	public function add( $data ){
		$this->db->insert('copywriter_templet',$data);
		return $this->db->lastInsertId();
	}
        //编辑文案模板
	public function edit( $data, $id ){
		return $this->db->update('copywriter_templet', $data , 'id = '.$id );
	}
        //获取文案模板列表
	public function getCopywriterTempletAll($page,$perpage,$keyword = '',$searchType = ''){
            $where = 'WHERE 1=1 ';
            if ($keyword != '') {
                if ($searchType == 1) {
                    $where .= " AND (c.name like '%{$keyword}%') ";
                } elseif ($searchType == 2) {
                    $where .= "  ";
                } elseif ($searchType == 3) {
                    $where .= "  ";
                } elseif ($searchType == 4) {
                    $where .= "  ";
                }
            }
		$data = $this->db->fetchAll(" SELECT c.*,d.createname,e.name as editname from copywriter_templet as c left join developers as e on c.editor = e.account left join (select a.id,b.name as createname from copywriter_templet as a left join developers as b on a.creator = b.account) as d on d.id = c.id {$where} group by c.`cat_id` order by c.id desc ".$this->db->buildLimit($page, $perpage));

		$all = $this->db->fetchAll("SELECT * FROM `copywriter_templet` as c {$where} group by `cat_id`");
		$data['total'] = count($all);
		return $data;
	}
        //通过id获取文案模板，编辑文案模板时用到
	public function getCopywriterTempletById($id){
		return $this->db->fetchRow( ' SELECT * FROM `copywriter_templet` WHERE `id` = '.$id );
	}
        //通过类目id获取文案模板，用于验证该类目是否有模板
	public function getCopywriterTempletByCatId($cat_id){
		return $this->db->fetchRow( ' SELECT * FROM `copywriter_templet` WHERE `cat_id` = '.$cat_id );
	}
        //删除文案模板
	public function del($id){
		return $this->db->delete('copywriter_templet', ' id ='.$id );
	}

	

}