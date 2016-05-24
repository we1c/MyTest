<?php

class CategoryController extends BaseController {

    public function init() {
        parent::init();
    }

    public function indexAction() {

        $tree = Service::getInstance('category')->getCategoryTree();
        $this->_view->tree = $tree;
    }

    public function addAction() {
        //$this->respon( 0 , $_POST );
        /* action: "add_child",id: "43",level: "1"
          name: "测试类目",is_attr_pid: "0",is_parent: "1" */
        $data['name'] = $this->getPost('name');
        if ($data['name'] == '') {
            $this->respon(0, '参数错误！');
        }

        $id     = $this->getPost('id') + 0;
        $level  = $this->getPost('level') + 0;
        $action = $this->getPost('action');

        $data['pid'] = $id;
        $data['is_parent'] = $this->getPost('is_parent') + 0;
        $data['is_attr_pid'] = $this->getPost('is_attr_pid') + 0;
        $data['level'] = ++$level;

        $res = Service::getInstance('category')->add($data);

        if ($res) {
            $data['id'] = $res;
            $this->respon(1, array('msg' => '添加成功！', 'catInfo' => $data));
        } else {
            $this->respon(0, '添加失败！');
        }
    }

    public function editAction() {
        //$this->respon( 0 , $_POST );
        /* action: "add_child",id: "43",level: "1"
          name: "测试类目",is_attr_pid: "0",is_parent: "1" */
        $data['name'] = $this->getPost('name');
        if ($data['name'] == '') {
            $this->respon(0, '参数错误！');
        }

        $id = $this->getPost('id') + 0;

        $data['is_parent']   = $this->getPost('is_parent') + 0;
        $data['is_attr_pid'] = $this->getPost('is_attr_pid') + 0;

        $res = Service::getInstance('category')->edit($data, $id);

        if ($res == 1) {
            $this->respon(1, array('msg' => '编辑成功！'));
        } else if ($res == 0) {
            $this->respon(1, '没做任何修改！');
        } else {
            $this->respon(0, '编辑失败！');
        }
    }

    public function delCatAction() {
        $id = $this->getPost('id');
        $is_parent = Service::getInstance('category')->checkIsParent($id);
        if (!$is_parent) {
            if (Service::getInstance('category')->delCatById($id)) {
                $this->respon(1, '删除成功！');
            } else {
                $this->respon(0, '删除失败！');
            }
        } else {
            $this->respon(0, '该类目拥有子类目,不允许直接删除，请将子类删除后,再次尝试！');
        }
    }

    public function getInfoByIdAction() {

        $id = $this->getPost('id') + 0;
        $cat = Service::getInstance('category')->getInfoById($id);
        $this->respon(1, $cat);
    }

    public function getAttrByCatIdAction() {

        $id = $this->getPost('id') + 0;
        $attr = Service::getInstance('attribute')->getAttrByCatId($id);
        $this->respon(1, $attr);
    }

    public function changeParentAction(){
        if( $this->isAjax() ){
            $id  = $this->getPost('id')+0;
            $pid = $this->getPost('pid')+0;
            if( !$id || !$pid ) $this->respon( 0,'参数错误！' );
            $parent = Service::getInstance('category')->getInfoById( $pid );
            if( $parent['level'] == 3 ) $this->respon( 0,'不能有四级分类' );
            //$self = Service::getInstance('category')->getInfoById( $id );
            $level = $parent['level'] + 1;
            $res = Service::getInstance('category')->edit( array('pid'=>$pid,'level'=>$level),$id );
            if( $res >= 0 ){
                $this->respon( 1,'移动成功！' );
            }else{
                $this->respon( 0,'移动失败！' );
            }
        }
    }

}
