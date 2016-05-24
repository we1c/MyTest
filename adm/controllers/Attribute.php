<?php

class AttributeController extends BaseController{
	
	public function init(){
		parent::init();
	}

	public function indexAction(){
		$page = $this->getQuery('page',1);
		$perpage = $this->getQuery('perpage',30);
		$showpage = 5;
		$result = Service::getInstance('attribute')->getAttrNameList( $page,$perpage );
		$this->_view->list = $result['list'];
		$this->_view->total = $result['total'];
		$this->_view->perpage = $perpage;
		$pageObj = new Page( $result['total'],$perpage,$showpage,$page,'',array('attribute','index','perpage'=>$perpage,));
        $this->_view->pagebar = $pageObj -> showPage();
	}

	public function addAction(){
		if( $this->isPost() ){
			$attr['cat_id'] = $this->getPost('cat_id')+0;
			$attr['attr_name'] = $this->getPost('attr_name');
			$attr['status'] = $this->getPost('status')+0;
			$attr['is_key_attr'] = $this->getPost('is_key_attr')+0;
			$attr['is_sale_attr'] = $this->getPost('is_sale_attr')+0;
			$attr['attr_type'] = $this->getPost('attr_type')+0;
			$attr['input_type'] = $this->getPost('input_type')+0;
			$attr_value = $this->getPost('attr_value');
			foreach( $attr as $k => $v ){
				if( trim($v) == '' ){
					$this->flash('/attribute/add','参数错误！',1);
				}
			}
			$resA = Service::getInstance('attribute')->addAttr( $attr );
			$resAV = true;
			if( $resA && $attr['input_type'] == 1 && !empty( $attr_value ) ){
				$resAV = Service::getInstance('attribute')->addAttrValue( $attr_value,$resA,$attr['cat_id'] );
				if( !$resAV ){
					Service::getInstance('attribute')->delAttrById( $resA );
				}
			}
			if( $resA && $resAV ){
				$this->flash('/category/index','添加成功',0);
			}else{
				$this->flash('/attribute/add','添加失败',1);
			}
		}else{
			$category = Service::getInstance('category')->getCategoryByPid( );
			$this->_view->category = $category;
		}
	}

	public function editAction(){
		if( $this->isPost() ){
			//echo "<pre>";var_dump($_POST);exit;
			$attr_id = $this->getPost('attr_id')+0;
			$attr['cat_id'] = $this->getPost('cat_id')+0;
			$attr['attr_name'] = trim($this->getPost('attr_name'));
			$attr['status'] = $this->getPost('status')+0;
			$attr['is_key_attr'] = $this->getPost('is_key_attr')+0;
			$attr['is_sale_attr'] = $this->getPost('is_sale_attr')+0;
			$attr['attr_type'] = $this->getPost('attr_type')+0;
			$attr['input_type'] = $this->getPost('input_type')+0;
			$resAK = Service::getInstance('attribute')->edit( $attr,$attr_id );
			//$record = $this->getPost('record');
			if( $resAK ===0 || $resAK > 0 ){
				$attr_value = $this->getPost('attr_value');
				$oldV = $attr_value['old'];
				$newV = $attr_value['new'];
				/*$upd = array();
				foreach( $record['a_v'] as $id => $value ){
					if( isset( $attr_value[$id] ) && $attr_value[$id] != $value ){
						$upd[$id] = $attr_value[$id];
					}
				}*/

				$resOAV = Service::getInstance('attribute')->updateOldAV( $oldV,$attr_id,$attr['cat_id'] );
				$resNAV = true;
				if( count($newV) > 1 || $newV[0] != '' ){
					$resNAV = Service::getInstance('attribute')->addAttrValue( $newV , $attr_id, $attr['cat_id'] );
				}
				if( $resOAV && $resNAV ){
					$this->flash( '/attribute/index/','',0 );
				}else{
					$this->flash( '/attribute/edit?attr_id='.$attr_id ,'修改属性值失败！',1 );
				}
			}else{
				$this->flash( '/attribute/edit?attr_id='.$attr_id ,'修改属性键值失败！',1 );
			}
		}else{
			$attr_id = $this->getQuery('attr_id');
			if( !$attr_id ) $this->flash('/attribute/index','参数错误！',1);
			$attr = Service::getInstance('attribute')->getAttrByAId( $attr_id );
			$attr_value = Service::getInstance('attribute')->getAttrChild( $attr['cat_id'],$attr_id );
			$generation = Service::getInstance('category')->getGeneration( $attr['cat_id'] );
			$relation = Service::getInstance('category')->makeRelation( $attr['cat_id'] );
			if( !empty($relation) ) ksort( $relation );
			$bread = '';
			foreach( $relation as $k => $v ){
				$bread .= $v['name'].'->';
			}
			$this->_view->attr = $attr;
			$this->_view->attr_value = $attr_value;
			$this->_view->generation = $generation;
			$this->_view->bread = rtrim( $bread,'->' );
		}
	}

	public function delAttrAction(){
		if( $this->isPost() ){
			$attr_id = $this->getPost('attr_id');
			//$res = Service::getInstance('attribute')->checkAttrIsQuote( $attr_id );
			$res = true;
			if( !$res ){
				$this->respon( 1,'yes' );
			}else{
				$this->respon( 0,'某商品正在使用该属性,不能删除！' );
			}
		}
	}

	public function getAttrChildAction(){
		if( $this->isPost() ){
			$cat_id = $this->getPost('cat_id');
			$attr_id = $this->getPost('attr_id');
			$res = Service::getInstance('attribute')->getAttrChild( $cat_id,$attr_id );
			if( $res ){
				$this->respon( 1,$res );
			}else{
				$this->respon( 0,'获取数据失败！' );
			}
		}
	}

	public function getCategoryByPidAction(){
		if( $this->isPost() ){
			$pid = $this->getPost('pid',0);
			$category = Service::getInstance('category')->getCategoryByPid( $pid );
			if( !empty($category) ){
				$this->respon( 1,$category );
			}else{
				$this->respon( 0,'没有参数' );
			}
		}
	}

}
