<?php
class OrderController extends BaseController
{
    public function init()
    {	
        parent::init();
    }
    
    //列表
    public function indexAction()
    {
        $user = Service::getInstance('user')->getUserByopenid( $this->openId );
        $list = Service::getInstance('orders')->getMyList( $user['id'], 1 );//全部
        $nodeliverlist = Service::getInstance('orders')->getMyList( $user['id'], 2 );//代发货
        $notakelist = Service::getInstance('orders')->getMyList( $user['id'], 3 );//待收货
//         $dellist = Service::getInstance('orders')->getMyList( $user['id'], 4 );//已取消
        $this->_view->alllist = $list;
        $this->_view->nodeliverlist = $nodeliverlist;
        $this->_view->notakelist = $notakelist;
//         $this->_view->dellist = $dellist;
    }
    
    //已下单，查看详情
    public function steponeAction() {
        $id = $this->getQuery( 'id' );
        $order = Service::getInstance( 'orders' )->getDetail( $id );
        $this->_view->order = $order;
    }
    //已发货，查看详情
    public function steptwoAction() {
        $id = $this->getQuery( 'id' );
        $order = Service::getInstance( 'orders' )->getDetail( $id );
        $this->_view->order = $order;
    }
    //已签收，查看详情
    public function stepthreeAction() {
        $id = $this->getQuery( 'id' );
        $order = Service::getInstance( 'orders' )->getDetail( $id );
        $this->_view->order = $order;
    }
    //已取消，查看详情
    public function cancelAction() {
        $id = $this->getQuery( 'id' );
        $order = Service::getInstance( 'orders' )->getDetail( $id );
        $this->_view->order = $order;
    }
}