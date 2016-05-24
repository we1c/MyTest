<?php

class Service_Attribute extends Service{

	public function getAttrNameList( $page,$prepage ){
		$map = '';
		$sql = ' SELECT * FROM attribute '.$map.' ORDER BY cat_id ASC '.$this->db->buildLimit( $page,$prepage );
		$attr = $this->db->fetchAll( $sql );
		$sql = ' SELECT * FROM category WHERE level = 3 ';
		$cat = $this->db->fetchAll($sql);
		$tmp = $this->range( $attr, 'cat_id' );
		$list = array();
		foreach( $cat as $k => $v ){
			if( isset($tmp[$v['id']]) ){
				$list[$v['id']]['attrs'] = $tmp[$v['id']];
				$relation = Service::getInstance('category')->makeRelation( $v['id'] );
				ksort( $relation );
				$path = '';
				foreach( $relation as $cid => $cinfo ){
					$path .= $cinfo['name'].'<br>↓↓<br>';
				}
				$list[$v['id']]['cat_path'] = rtrim($path,'<br>↓↓<br>');
				unset($tmp[$v['id']]);
			}
		}
		$result['list'] = $list;
		$sql = ' SELECT count(*) FROM attribute '.$map;
		$result['total'] = $this->db->fetchOne( $sql );
		//echo "<pre>";var_dump($list);exit;
		return $result;
	}
	//将二维数组按照某个列相同的值归类------(可排序)
	private function range( $arr ,$col ){
		$res = array();
		foreach ( $arr as $k => $v ){
			$res[$v[$col]][] = $v;
		}
		return $res;
	}

	public function getAttrByAId( $attr_id ){
		return $this->db->fetchRow( ' SELECT * FROM attribute WHERE attr_id = '.$attr_id );
	}

	public function getAttrByCatId( $cat_id ){
		$attr_k = $this->getAttr_k( $cat_id );
		$attr_v = $this->getAttr_v( $cat_id );
		return $this->getAttrTree( $attr_k ,$attr_v );
	}

	public function getAttr_k( $cat_id ){
		return $this->db->fetchAll('SELECT attr_id,attr_name FROM attribute WHERE cat_id ='.$cat_id);
	}

	public function getAttr_v( $cat_id ){
		return $this->db->fetchAll( 'SELECT id,`values`,attr_pid FROM attr_value WHERE cat_id ='.$cat_id);
	}
	//将两个数组合并成父子结构
	public function getAttrTree( $arr_k ,$arr_v ){
		foreach( $arr_k as $k1 => $v1 ){
			foreach( $arr_v as $k2 => $v2 ){
				if( $v2['attr_pid'] == $v1['attr_id'] ){
					$arr_k[$k1]['child'][$v2['id']] = $v2;
				}
			}
		}
		return $arr_k;
	}

	public function getAttrChild( $cat_id ,$pid ){
		$test = 'SELECT * FROM attr_value WHERE cat_id ='.$cat_id.' AND attr_pid ='.$pid;
		return $this->db->fetchAll( 'SELECT * FROM attr_value WHERE cat_id ='.$cat_id.' AND attr_pid ='.$pid );
	}

	public function addAttr( $attr ){
		if( $this->db->insert( 'attribute' , $attr )){
			return $this->db->lastInsertId();
		}else{
			return false;
		}
	}

	public function edit( $attr , $attr_id ){
		return $this->db->update('attribute', $attr, ' attr_id = '.$attr_id );
	}

	public function delAttrById( $attr_id ){
		return $this->db->delete('attribute',' attr_id ='.$attr_id );
	}

	public function addAttrValue( $values,$pid,$cat_id ){
		$sql = 'INSERT INTO attr_value ( `values`,`attr_pid`,`cat_id` ) VALUES ';
		foreach( $values as $k => $v ){
			if( trim($v) != '' ) $sql .= " ( '".trim($v)."','$pid','$cat_id' ),";
		}
		$sql = rtrim($sql,',');
		return $this->db->query( $sql );
	}
/*UPDATE categories
    SET display_order = CASE id
        WHEN 1 THEN 3
        WHEN 2 THEN 4
        WHEN 3 THEN 5
    END,
    title = CASE id
        WHEN 1 THEN 'New Title 1'
        WHEN 2 THEN 'New Title 2'
        WHEN 3 THEN 'New Title 3'
    END
WHERE id IN (1,2,3)*/
	public function updateOldAV( $values,$pid,$cat_id ){
		$sql = ' UPDATE attr_value SET ';
		$sql .= ' `values` = CASE id ';
		$ids = array();
		foreach( $values as $id => $value ){
			$ids[] = $id;
			$sql .= " WHEN {$id} THEN '{$value}' ";
		}
		$ids = implode(',', $ids);
		$sql .= ' END WHERE id IN ( '.$ids.' ) ';
		$resV = $this->db->query( $sql );

		$sql = ' UPDATE attr_value SET cat_id = '.$cat_id.' WHERE id IN ('.$ids.') ';
		$resPC = $this->db->query( $sql );

		if( $resV && $resPC ){
			return true;
		}else{
			return false;
		}
	}


}