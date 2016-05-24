<?php

class Service_Category extends Service{

	public function add( $data ){
		return $this->db->insert('category',$data);
	}

	public function edit( $data, $id ){
		return $this->db->update('category', $data , 'id = '.$id );
	}

	public function delCatById( $id ){
		return $this->db->delete('category', ' id = '.$id );
	}

	public function checkIsParent( $id ){
		return $this->db->fetchOne( 'SELECT count(*) FROM category WHERE pid = '.$id );
	}

	public function getAllCat(){
		$sql = ' SELECT * FROM category ';
		return $this->db->fetchAll( $sql );
	}

	public function getCategoryTree(){
		$data = $this->getAllCat();
		return $this->makeTree( $data );
	}

	public function makeTree( $arr , $pid = 0 ){
		$result = array();
		foreach( $arr as $k => $v ){
			if( $v['pid'] == $pid ){
				$v['child'] = $this->makeTree( $arr , $v['id'] );
				$result[$v['id']] = $v;
			}
		}
		return $result;
	}

	public function getGeneration( $cat_id ){
		$relation = $this->makeRelation( $cat_id );
		$result = array();
		foreach( $relation as $cat_id => $cat_info ){
			$result[$cat_info['level']]['siblings'] = $this->getInfoByPid( $cat_info['pid'] );
			$result[$cat_info['level']]['pid'] = $cat_info['pid'];
		}
		return $result;
	}

	public function makeRelation( $cat_id ){
		$generation = array();
		$curCat = $this->getInfoById( $cat_id );

		if( !$curCat ) return array();

		$generation['c-'.$curCat['id']] = $curCat;

		$parent = $this->makeRelation( $curCat['pid'] );

		return array_merge( $generation,$parent );
	}

	public function getInfoById( $id ){
		return $this->db->fetchRow( ' SELECT * FROM category WHERE id = '.$id );
	}

	public function getCategoryByPid( $pid = 0 ){
		return $this->db->fetchAll( 'SELECT id,name,pid,is_attr_pid FROM category WHERE status != 0 AND pid = '.$pid );
	}
	
	public function getInfoByPid( $pid ){
		return $this->db->fetchAll( ' SELECT * FROM category WHERE pid = '.$pid );
	}

}