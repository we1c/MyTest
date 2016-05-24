<?php

class ShowcstmController extends BaseController{

	public function init(){
		parent::init();
	}

	public function indexAction() {

        $info = $this->getQuery('1ge');
        $goodsId = base64_decode($info);
        $data = Service::getInstance('goods')->getGoodsInfoById( $goodsId ,'gapi' );

        unset( $data['price'] );
        unset( $data['purchPrice'] );
        unset( $data['mtimes'] );
        unset( $data['ptimes'] );
        $this->_view->data = $data ? $data : array();
    }

    public function getnextimgAction(){
        $id=$this->getPost('id');
        $offset=$this->getPost('offset');
        $one_imgurl=Service::getInstance('goods')->getGoodsImgHashByGoodsId($id,$offset,'800');

        $this->respon(1,$one_imgurl);
    }

}