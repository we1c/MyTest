<?php

class DistributorController extends BaseController
{
    public function init()
    {
        parent::init();  
    }
	public function indexAction(){
		$perpage = $this->getQuery('perpage',15);
        $showpage = 5;
		$page = $this->getQuery('page', 1);
		$keyword = $this->getQuery('keyword', '');
        $status = $this->getQuery('status', 0);
		$data = Service::getInstance('distributor')->getList($page,$perpage,$keyword,$status);
		$this->_view->list = $data['list'];
		//$url = $keyword ? '/distributor/index?page=__page__&keyword='.$keyword : '/distributor/index?page=__page__';
		//$this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
        $this->_view->total = $data['total'];
        $this->_view->perpage = $perpage;
        $this->_view->keyword = $keyword;
        $this->_view->status = $status;
        $this->_view->page = $page;
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('distributor','index','keyword'=>$keyword,'perpage'=>$perpage,'status'=>$status));
        $this->_view->pagebar = $pageObj -> showPage();
    }
	
	//添加
    public function addAction() {
        if($this->isPost()) {
            $keyword = $this->getPost('keyword');
            $perpage = $this->getPost('perpage',15);
            $status = $this->getPost('status');
            $page = $this->getPost('page',1);

            $name = trim($this->getPost('name'));
            $domain = trim($this->getPost('domain'));
            $ctimes = $this->getPost('ctimes');
            $devId = $this->getPost('devId')+0;
            $payway = trim($this->getPost('payway'));
            $paybank = trim($this->getPost('paybank'));
            $headimgurl = $this->getPost('headimgurl');
            $clearing_type = $this->getPost('clearing_type');
            $credit_limit = $this->getPost('credit_limit');
            if( !$name ) {
                $this->error('请输入名称');
                return;
            }
            if( !$domain ) {
                $this->error('请输入域名');
                return;
            }
            
            $checkUser = Service::getInstance('distributor')->checkExist( $name );
            if ( $checkUser ) {
                $this->error('该名称已添加过，不能重复哒~~~');
                $this->_view->info = array('name'=>$name,'domain'=>$domain,'ctimes'=>$ctimes);
                return;
            }
            $uid = Service::getInstance('distributor')->add( $name, $domain, $devId, $payway, $paybank, $headimgurl, $clearing_type, $credit_limit );
            if ($uid) {
                $sql = " INSERT INTO `channel_shop_ctimes` (`channelId`,`shopId`,`ctimes`,`updateTime`) VALUES ";
                foreach ($ctimes as $k=>$v){
                    if ($v != '') {
                        $sql .="('{$uid}', '{$k}', {$v} , ".time()." ),";
                    }
                }
                $sql = rtrim($sql, ',');
                Service::getInstance("distributor")->addCtimes($sql);
            }
            $disIds = Service::getInstance('developers')->getDisIdById( $devId );

            if( $disIds == 0 ){
                $disIds = $uid;
            }else{
                $disIds .= ','.$uid;
            }
            $resDev = Service::getInstance('developers')->updateDev($devId,array('disId'=>$disIds));
            if( $uid && $resDev ) {
                $this->flash("/distributor/index/?keyword=".$keyword."&perpage=".$perpage."&status=".$status."&page=".$page,"添加成功",1);
            } else {
                $this->flash("/distributor/index","添加失败",2);
            }
        }else{
            $roleId = 11;
            $keyword = $this->getQuery('keyword');
            $perpage = $this->getQuery('perpage',15);
            $status = $this->getQuery('status');
            $page = $this->getQuery('page',1);
            $dev = Service::getInstance('developers')->getDeveloperByRole( $roleId );
            $payway = Service::getInstance('orders')->getPayWay();
            $shop = Service::getInstance('shop')->getShopName();
            $this->_view->shop = $shop;
            $this->_view->payway = $payway;
            $this->_view->dev = $dev;
            $this->_view->keyword = $keyword;
            $this->_view->perpage = $perpage;
            $this->_view->status = $status;
            $this->_view->page = $page;
        }
    }
    
    //编辑
    public function editAction() {
        if($this->isPost()) {
            $keyword = $this->getPost('keyword');
            $perpage = $this->getPost('perpage',15);
            $status = $this->getPost('status');
            $page = $this->getPost('page',1);
            $id = $this->getPost('id');
            $name = trim($this->getPost('name'));
            $domain = trim($this->getPost('domain'));
            $ctimes = $this->getPost('ctimes');
            $devId = $this->getPost('devId')+0;
            $oldDevId = $this->getPost('oldDevId')+0;
            $payway = trim($this->getPost('payway'));
            $paybank = trim($this->getPost('paybank'));
            $headimgurl = $this->getPost('headimgurl');
            $clearing_type = $this->getPost('clearing_type');
            $credit_limit = $this->getPost('credit_limit');
            if( !$name ) {
                $this->error('请输入名称');
                $info = Service::getInstance('distributor')->getInfoById( $id );
                $this->_view->info = $info;
                return;
            }
            if( !$domain ) {
                $this->error('请输入域名');
                $info = Service::getInstance('distributor')->getInfoById( $id );
                $this->_view->info = $info;
                return;
            }
            $user['name'] = $name;
            $user['domain'] = $domain;
            $user['devId'] = $devId;
            $user['payway'] = $payway;
            $user['paybank'] = $paybank;
            $user['clearing_type'] = $clearing_type;
            $user['credit_limit'] = $credit_limit;

            if ( $headimgurl) $user['headimgurl'] = $headimgurl;
            $res = Service::getInstance('distributor')->edit( $user, $id );
            $ctimesId = Service::getInstance('distributor')->getCtimesShopIdByChannel( $id );
            $existsCtimes = array();
            foreach ($ctimesId as $k => $v) {
                $existsCtimes[$v['shopId']] = $k;
            }
            $diff = array_diff_key($ctimes,$existsCtimes);
            if ($diff) {
                $sql = " INSERT INTO `channel_shop_ctimes` (`channelId`,`shopId`,`ctimes`,`updateTime`) VALUES ";
                foreach ($diff as $k=>$v){
                    if ($v != '') {
                        $values .="('{$id}', '{$k}', '{$v}' , ".time()." ),";
                    }
                }
                if (isset($values)) {
                    $values = rtrim($values, ',');
                    Service::getInstance("distributor")->addCtimes($sql.$values);
                }
            }
            $intersect = array_intersect_key($ctimes,$existsCtimes);
            if ($intersect) {
                foreach ($intersect as $key => $value) {
                    if ($value != '') {
                        $edit = Service::getInstance('distributor')->editCtimes( $value ,$id ,$key );
                    }
                }
            }

            if( $devId != $oldDevId ){
                $disIds = Service::getInstance('developers')->getDisIdById( $devId );
                if( $disIds == 0 ){
                    $disIds = $id;
                }else{
                    $disIds = explode(',',$disIds);
                    if( !in_array( $id,$disIds ) ){
                        $disIds[] = $id;
                    }
                    $disIds = implode(',',$disIds);
                }
                $resDev = Service::getInstance('developers')->updateDev( $devId,array('disId'=>$disIds) );
            }

            if($res >= 0 && $resDev >= 0 ) {
                $this->flash("/distributor/index/?keyword=".$keyword."&perpage=".$perpage."&status=".$status."&page=".$page,"编辑成功",1);
            } else {
                $this->flash("/distributor/index/?keyword=".$keyword."&perpage=".$perpage."&status=".$status."&page=".$page,"编辑失败",2);
            }
        }

        $keyword = $this->getQuery('keyword');
        $perpage = $this->getQuery('perpage',15);
        $status = $this->getQuery('status');
        $page = $this->getQuery('page',1);
        $uid = $this->getQuery('id');
        $info = Service::getInstance('distributor')->getInfoById( $uid );
        $roleId = 11;
        $dev = Service::getInstance('developers')->getDeveloperByRole( $roleId );
        $payway = Service::getInstance('orders')->getPayWay();
        $paytype = Service::getInstance('orders')->getPayType($info['payway']);
        $paybank = Service::getInstance('orders')->getPayBankById($info['paybank']);
        $shop = Service::getInstance('shop')->getShopName();
        $cTimes = Service::getInstance('distributor')->getCtimesByChannel($uid);
        $this->_view->cTimes = $cTimes;
        $this->_view->shop = $shop;
        $this->_view->paytype = $paytype;
        $this->_view->payway = $payway;
        $this->_view->paybank = $paybank;
        $this->_view->info = $info;
        $this->_view->keyword = $keyword;
        $this->_view->perpage = $perpage;
        $this->_view->status = $status;
        $this->_view->page = $page;
        $this->_view->dev = $dev;
    }

    //删除用户
    public function delAction(){
        $id = $this->getPost('id');
        $res = Service::getInstance('distributor')->del($id,1);
        if ($res)  $this->respon(1,$id);
    }

    //还原用户
    public function openAction(){
        $id = $this->getPost('id');
        $res = Service::getInstance('distributor')->del($id,0);
        if ($res)  $this->respon(1,$id);
    }
    
    //激活用户
    public function enableAction() {
        return false;
        $id = intval( $this->getQuery('id') );
        $res = Service::getInstance('distributor')->enable( $id );
        $this->flash("/distributor/index"," 操作成功");
    }
    
    //禁用用户
    public function disableAction() {
        return false;
        $id = intval( $this->getQuery('id') );
        $res = Service::getInstance('distributor')->disable( $id );
        $this->flash("/distributor/index"," 操作成功");
    }
    
    //查看
    public function viewAction() {
        return false;
        $id = intval($this->getQuery('id'));
        $info = Service::getInstance('distributor')->getInfoByid( $id );
        $this->_view->list = $info;
    }

    //重置可用授信额度
    public function resetLimitAction(){
        if ( $this->isPost() ){
            $id = $this->getPost('id');
            if ( !$id ) $this->respon(0,'no id!');
            $used = 0;
            $overflow = 0;
            $limit = Service::getInstance('distributor')->getCreditLimit( $id );
            $data = Service::getInstance('orders')->getBeyondOrders( $id );
            if ( $data ) {
                foreach ($data as $k => $v) {
                    $used += $v['price'];
                    if ( $used <= $limit ) {
                        // echo $v['price']."<br>";
                        Service::getInstance('orders')->edit( array('beyond_limit'=>0),$v['id'] );
                    }else{
                        $used -= $v['price'];
                        $overflow = 1;
                    }
                }
            }
                
            $updateUsed = Service::getInstance('distributor')->edit( array('used_limit'=>$used),$id ); 

            if ( $overflow ) {
                $this->respon(1,array('重置成功，超限订单过多，再次达到限额',$used."/".$limit));
            }else{
                if ( $updateUsed !== false ) {
                    $this->respon(1,array('重置成功',$used."/".$limit));
                }else{
                    $this->respon(0,"重置失败");
                }
            }
        }
    }
  
}