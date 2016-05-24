<?php
class AccountController extends BaseController {
    public function init()
    {
        parent::init();
        if(!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    }
    public function indexAction() {
        $page = 1;
        $perpage = 9999;
        $keyword = $this->getQuery('keyword', '');
        $status = $this->getQuery('status', '0');
        $start  = strtotime($this->getQuery('starttime',"2016-01-01"));
        $end    = strtotime($this->getQuery('endtime',date('Y-m-d',time()))."+1 day");
        $devId  = '';
        $data = Service::getInstance('account')->AccountList($page,$perpage,$keyword,$devId,$status,$start,$end);
        $list = $data['list'];
        $this->_view->list = $list;
        $total = $data['total'];
        $this->_view->total = $total;
        $this->_view->total0 = $data['total0'];
        $this->_view->total1 = $data['total1'];
        $this->_view->total2 = $data['total2'];
        $this->_view->total3 = $data['total3'];
        $this->_view->total4 = $data['total4'];
        $this->_view->keyword = $keyword;
        $this->_view->status = $status;
        $this->_view->starttime = date('Y-m-d',$start);
        $this->_view->endtime = date('Y-m-d',$end-86400);
        $this->_view->perpage = $perpage;
        //$pageObj = new Page( $total,$perpage,$showpage,$page,'',array('audit','index','keyword'=>$keyword,'perpage'=>$perpage));
        //$this->_view->pagebar = $pageObj -> showPage(); 
    }

    public function searchAction() {
    	if ( $this->isPost()){
	    	$scode	= $this->getPost('scode');
	    	$start	= strtotime($this->getPost('start'));
	    	$end	= strtotime($this->getPost('end')."+1 day");

	    	if (!$scode) $this-respon(0,"请填写供应商编号");
	    	if (!$start) $this-respon(0,"请填写开始时间");
	    	if (!$end) $this-respon(0,"请填写结束时间");
	    	$data	= Service::getInstance('orders')->getOrderByScode( $scode,$start,$end );
	    	if ( $data ) $this->respon(1,$data);
	    	else $this->respon(0,"没有数据");
    	}
    }

    public function addAction() {
        // $this->redisDelAccount( 3,array('805','804','803') );
        // exit;
        if ( $this->isPost()) {
            $orderArr = $this->getPost('orderId');
            $shopId = $this->getPost('shopId');
            $devId  = $this->_developer['id'];
            $total  = $this->getPost('total');
            $expectTime = $this->getPost('expectTime');
            $note   = $this->getPost('note');
            $data   = array(
                    "shopId"=>$shopId,
                    "devId"=>$devId,
                    "total"=>$total,
                    "createTime"=>time(),
                    "expectTime"=>$expectTime,
                    "note"=>$note,
                    "type"=>1
                    );

            $res = Service::getInstance('account')->add($data);
            if ($res) {
                //删除redis中的自动结算申请
                $this->redisDelAccount( $shopId,$orderArr );

                //更新订单状态
                $orderIn = implode(',',$orderArr);
                $update = Service::getInstance('orders')->editAccount(array('accountId'=>$res,'account_status'=>1),$orderIn);
                if ( $update ) {
                    $this->flash( '/account/index', '添加成功' );
                }
            }else{
                $this->flash( '/account/index', '添加失败' );
            }
        }
        exit;
    }


    //删除申请单
    public function delAction(){
        if ($this->isPost()) {
            $id = $this->getPost('id');
            $data = Service::getInstance('account')->getDetail($id);
            if (empty($data)) {
                Service::getInstance('account')->delAccount($id);
                $this->respon(1,'删除成功');
            }
            $count = 0 ;
            foreach ($data as $k => $v) {
                if($v['account_status'] == 1){
                    $count++;
                }
                $idArr[] = $v['id'];
            }
            $idIn = implode(',',$idArr);
            if (count($data) == $count || count($data) == 0) {
                Service::getInstance('orders')->editAccount(array("account_status"=>0),$idIn);
                Service::getInstance('account')->delAccount($id);

                //加入到redis
                $this->redisAddAccount( $idArr );
                $this->respon(1,'删除成功');
            }else{
                $this->respon(0,'审核完成，无法删除');
            }
        }
        exit;
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

    //导出excel
    public function exportExcelAction(){
        $cell = range( 'A','Z' );
        $keyword = $this->getQuery('keyword', '');
        $status = $this->getQuery('status', '0');
        $start  = strtotime($this->getQuery('starttime',"2016-01-01"));
        $end    = strtotime($this->getQuery('endtime',date('Y-m-d',time()))."+1 day");
        $devId  = $this->_developer['id'];
        $data = Service::getInstance('account')->getAccountExport($keyword,$devId,$status,$start,$end);
        $objPHPExcel=new PHPExcel();//实例化PHPExcel类， 等同于在桌面上新建一个excel
        //获得当前活动单元格
        $objSheet=$objPHPExcel->getActiveSheet();
        //设置excel文件默认水平垂直方向居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(Plugins_PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(Plugins_PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置默认字体大小和格式
        $objSheet->getDefaultStyle()->getFont()->setSize(10)->setName("宋体");
        //设置第二行字体大小和加粗
        $objSheet->getStyle("A1:Z1")->getFont()->setSize(11)->setBold(true);
        //设置宽度
        for ($i=0; $i < count($cell) ; $i++) { 
            $objSheet->getColumnDimension($cell[$i])->setWidth(10); 
        }
        $objSheet->getDefaultRowDimension()->setRowHeight(20);//设置默认行高
        /*$objSheet->getRowDimension(2)->setRowHeight(30);//设置第二行行高
        $objSheet->getRowDimension(3)->setRowHeight(30);//设置第三行行高*/

        //表头数组
        $head = array('单据编号','申请时间','供应商名称','分组类别','申请人','应付小计','预计付款时间','备注','支付日期','订单编号','商品名称','SKU编码','数量','进货价格','运费结算','包装结算','证书结算','其他费用','应付金额','结算状态');
        //对应索引
        $relation = array('id','createTime','shopName','category','devName','total','expectTime','note','payTime','orderCode','name','code','number','purchPrice','real_freight','real_pack','real_certificate','real_other','payable','account_status');
        //填充表头信息
        $headLen = count($head);
        for( $i=0;$i<$headLen;$i++ ) {
            $objSheet->setCellValue( $cell[$i]."1",$head[$i] );
        }
        $len = count($data);
        for ( $i=0; $i<$len; $i++ ) {
            $j = 0;
            foreach ( $relation as $k => $v ) {
                $row = $i + 2;
                $value = $data[$i][$v];
                if( is_numeric($value) ) $value = ' '.$value;
                $objSheet->setCellValue( "$cell[$j]$row",$value );
                $j++;
            }
        }

        $objWriter=Plugins_PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件\
        //告诉浏览器将输出文件的名称
        header('Content-Disposition: attachment;filename=account.xls');
        header('Cache-Control: max-age=0');//禁止缓存   
        $objWriter->save("php://output");
    }

    //删除redis中对应订单的自动结算申请
    private function redisDelAccount( $shopId,$orderArr ){
        $period = Service::getInstance('shop')->getShopPeriodById($shopId);
        foreach ($orderArr as $k => $v) {
            $payTime = Service::getInstance('orders')->getOrderPayTime($v);
            $expire  = $payTime+$period*86400;
            $accDate = date('Ymd',$expire);
            $this->redis->SREM(md5('account_'.$accDate),$v);
            // echo md5('account_'.$accDate).'<br>';
        }
        return true;
    }

    //redis存储订单结算申请   md5(account_20160425)
    private function redisAddAccount( $orderArr ){
        foreach ($orderArr as $k => $v) {
            $orderInfo = Service::getInstance('orders')->getOrderInfo($v);
            $period    = Service::getInstance('shop')->getShopPeriodById($orderInfo['shopId']);
            $today     = strtotime(date("Y-m-d"));
            $expire    = strtotime(date("Ymd",$orderInfo['payTime']))+$period*86400;
            $accDate   = date('Ymd',$expire);
            $key       = md5('account_'.$accDate);
            if ( $today <= $expire ) {
                $res     = $this->redis->SADD($key,$v);
            }
        }
        return true;
    }

    //确认系统自动提交的申请
    public function editStatusAction(){
        if ( $this->isPost() ) {
            $id = $this->getPost('id');
            $update = Service::getInstance('account')->update(array('audit_status'=>0),$id);
            $this->respon(1,$update);
        }else{
            $this->respon(0,'数据异常');
        }
    }

    //修改备注
    public function editNoteAction(){
        if ( $this->isPost() ) {
            $accountId = $this->getPost('accountId');
            $note = $this->getPost('editNote');
            if ( !$accountId ) $this->respon( 0, "no id!"); 
            $update = Service::getInstance('account')->update(array('note'=>$note),$accountId);
            $this->respon(1,array($accountId,$note));
        }else{
            $this->respon(0,'数据异常');
        }
    }

}