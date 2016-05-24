<?php
class ReportController extends BaseController {
    public function init()
    {
        parent::init();
        if(!$this->_developer) {
            $this->redirect('/developer/signin');
            exit;
        }
    }

    public function indexAction(){
        $firstDay = date('Y-m-01', time());
        $start = strtotime($this->getQuery('start',$firstDay));
        $queryEnd = $this->getQuery('end',date('Y-m-d',time()));
        $end = strtotime($queryEnd."+1 day");
    	$channel = $this->getQuery('channel');
        $queryShop = $this->getQuery('shop');
        /*if ($queryShop) {
            preg_match('/\d+/',$queryShop,$arr);
            $shop = "AA".str_pad($arr[0],3,"0",STR_PAD_LEFT);
        }else{
            $shop = '';
        }*/
        $shop = $queryShop;

    	$data = Service::getInstance('report')->getReportDay($channel,$shop,$start,$end);
        $disList = Service::getInstance('distributor')->getAllDis();
        $shopList = Service::getInstance('shop')->getShopName();

        $this->_view->list = $data['list'];
        $this->_view->total = $data['total'];
        $this->_view->monthTotal = $data['monthTotal'];
        $this->_view->disList = $disList;
        $this->_view->shopList = $shopList;
        $this->_view->channel = $channel;
        $this->_view->shop = $queryShop;
        $this->_view->start = date('Y-m-d',$start);
        $this->_view->end = $queryEnd;
    }

    public function exportAction(){
        $firstDay = date('Y-m-01', time());
        $start = strtotime($this->getQuery('start',$firstDay));
        $queryEnd = $this->getQuery('end',date('Y-m-d',time()));
        $end = strtotime($queryEnd."+1 day");
        $channel = $this->getQuery('channel');
        $queryShop = $this->getQuery('shop');
        if ($queryShop) {
            preg_match('/\d+/',$queryShop,$arr);
            $shop = "AA".str_pad($arr[0],3,"0",STR_PAD_LEFT);
        }else{
            $shop = '';
        }

        $data = Service::getInstance('report')->getReportDay($channel,$shop,$start,$end);

        $objPHPExcel=new PHPExcel();//实例化PHPExcel类， 等同于在桌面上新建一个excel
        //获得当前活动单元格
        $objSheet=$objPHPExcel->getActiveSheet();
        //设置excel文件默认水平垂直方向居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(Plugins_PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(Plugins_PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置默认字体大小和格式
        $objSheet->getDefaultStyle()->getFont()->setSize(10)->setName("宋体");
        //设置第二行字体大小和加粗
        $objSheet->getStyle("A1:Z1")->getFont()->setSize(11)->setBold(true);
        $objSheet->getDefaultRowDimension()->setRowHeight(20);//设置默认行高
        /*$objSheet->getRowDimension(2)->setRowHeight(30);//设置第二行行高
        $objSheet->getRowDimension(3)->setRowHeight(30);//设置第三行行高*/

        $cell = range( 'A','Z' );
        //表头数组
        //日期 渠道价   统一运费    统一包装费   证书费用    价格波动    合计  商品成本    实付运费    包装费 平台费 转账费 合计 订单量   商品数量    毛利  毛利率%
        $head = array('日期','平台价','渠道价','销售额','商品成本','实付运费','包装费','平台费','转账费','合计','订单量','商品数量','毛利','毛利率%','配销率');
        //对应索引
        $relation = array('day','channelPrice','platPrice','price','purchPrice','real_freight','real_pack','price_platf','price_transfer','expenses','number','orderNum','profit','rate','distribution');
        //填充表头信息
        $objSheet->setCellValue( "B1","收入" );
        $objSheet->setCellValue( "E1","支出" );
        $objSheet->setCellValue( "A1","日期" );
        $objSheet->setCellValue( "K1","订单量" );
        $objSheet->setCellValue( "L1","商品数量" );
        $objSheet->setCellValue( "M1","毛利" );
        $objSheet->setCellValue( "N1","毛利率%" );
        $objSheet->setCellValue( "O1","配销率%" );
        $objSheet->mergeCells('B1:D1');
        $objSheet->mergeCells('E1:J1');
        $objSheet->mergeCells('A1:A2');
        $objSheet->mergeCells('K1:K2');
        $objSheet->mergeCells('L1:L2');
        $objSheet->mergeCells('M1:M2');
        $objSheet->mergeCells('N1:N2');
        $objSheet->mergeCells('O1:O2');
        $headLen = count($head);
        for( $i=0;$i<$headLen;$i++ ) {
            $objSheet->setCellValue( $cell[$i]."2",$head[$i] );
        }
        //表内容
        foreach ($data['list'] as $k => $v) {
            $res[]=$v;
        }
        $len = count($res);
        for ( $i=0; $i<$len; $i++ ) {
            $j = 0;
            foreach ( $relation as $k => $v ) {
                $row = $i + 3;
                $value = $res[$i][$v];
                if( is_numeric($value) ) $value = ' '.$value;
                $objSheet->setCellValue( "$cell[$j]$row",$value );
                $j++;
            }
        }
        //总计内容
        foreach ($data['monthTotal'] as $key => $value) {
            $monthTotal[] = $value;
        }
        for( $i=0;$i<$headLen;$i++ ) {
            $objSheet->setCellValue( $cell[$i].($len+3),$monthTotal[$i] );
        }

        $objWriter=Plugins_PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件\
        //告诉浏览器将输出文件的名称
        header('Content-Disposition: attachment;filename=report.xls');
        header('Cache-Control: max-age=0');//禁止缓存   
        $objWriter->save("php://output");
    
    }
}