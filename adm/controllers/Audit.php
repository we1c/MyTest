<?php
class AuditController extends BaseController {
    public function init()
    {
        parent::init();
        if(!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    }
    public function indexAction() {
        $perpage = $this->getQuery('perpage',15);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
        $start  = strtotime($this->getQuery('start',"2016-01-01"));
        $end    = strtotime($this->getQuery('end',date('Y-m-d',time()))."+1 day");
        $status = $this->getQuery('status', '0');
        $data = Service::getInstance('account')->AuditList($page,$perpage,$keyword,$status,'',$start,$end);
        $list = $data['list'];
        $this->_view->list = $list;
        $total = $data['total'.$status];
        $this->_view->total = $total;
        $this->_view->total0 = $data['total0'];
        $this->_view->total1 = $data['total1'];
        $this->_view->total2 = $data['total2'];
        $this->_view->keyword = $keyword;
        $this->_view->start = date('Y-m-d',$start);
        $this->_view->end = date('Y-m-d',$end-86400);
        $this->_view->perpage = $perpage;
        $this->_view->status = $status;
        $pageObj = new Page( $total,$perpage,$showpage,$page,'',array('audit','index','keyword'=>$keyword,'perpage'=>$perpage,'status'=>$status,'keyword'=>$keyword,'start'=>date('Y-m-d',$start),'end'=>date('Y-m-d',$end-86400)));
        $this->_view->pagebar = $pageObj -> showPage();
    }


    //结算订单详情
    public function detailAction(){
        if ( $this->isPost()) {
            $id = $this->getPost('id');
            $data = Service::getInstance('account')->getDetail($id);
            if ($data) {
                $this->respon(1,$data);
            }else {
                $this->respon(0,"失败");
            }
        }
    }

    
    //详情
    public function viewAction() {
        $type = $this->getQuery('type');
        $id = $this->getQuery('id');
        $list = Service::getInstance('account')->getDetail($id);
        $data = Service::getInstance('account')->getAccountById($id);
        $this->_view->id = $id;
        $this->_view->list = $list;
        $this->_view->data = $data;
        $this->_view->type = $type;
    }

    //审核操作
    public function changeAction(){
        if( $this->isPost()) {
            $id = $this->getPost('id');
            $audit_note = $this->getPost('audit_note');
            $real_total = $this->getPost('real_total');
            if (!$id) {
                $this->flash( '/audit/index', '失败' );
            }
            $relationArr = $this->getPost('relation');
            $audit_status = 1;
            foreach ($relationArr as $k => $v) {
                if ( $v ) {
                    $status[$v][] = $k;
                }
                if ( $v != 2 ){
                    $audit_status = 2;
                }
            }
            if (count($status[3]) == count($relationArr)){
                $audit_status = 3;
            }
            for ( $i = 1 ; $i <= 3 ; $i++ ){
                if (isset($status[$i])) {
                    $orderIn[$i] = implode(",",$status[$i]);
                    $update = Service::getInstance('orders')->editAccount( array("account_status"=>$i),$orderIn[$i] );
                }
            }
            $data = array(
                    "audit_status"=>$audit_status,
                    "audit_note"=>$audit_note,
                    "real_total"=>$real_total,
                    "auditTime"=>time()
                );
            $res = Service::getInstance('account')->update($data,$id);
            if ($res) {
                $this->flash( '/audit/index', '提交成功' );
            }else{
                $this->flash( '/audit/index', '失败' );
            }
        }else{
            $this->flash( '/audit/index', '失败' );
        }
    }

}