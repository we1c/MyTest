<?php

class ShopController extends BaseController {
    public function init() {	
        parent::init();
        if ( ! (bool)Yaf_Registry::get("isLogin") ) {
            $this->respon( 0 , "请重新登录" );
        }
    }
    
    //店铺列表
    public function shoplistAction() {
        $uid = Yaf_Registry::get('uid');
        $list = Service::getInstance('shop')->shopApiList($uid);
        $this->respon(1,$list);
    }
    
    //设置默认店铺
    public function setdefaultAction() {
        $uid = Yaf_Registry::get('uid');
        $shopId = $this->getPost('shopId');
        $res = Service::getInstance('shop')->setDefault($uid,$shopId);
        if($res >= 0) {
            $this->respon(1,$shopId);
        } else {
            $this->respon(0,'设置失败');
        }
    }

    public function setdefault_v2Action() {
        $uid = Yaf_Registry::get('uid');
        $shopId = $this->getPost('shopId');
        $res = Service::getInstance('shop')->setDefault($uid,$shopId);
        if($res >= 0) {
            $tags = Service::getInstance('shop')->getTagsByShopId( $shopId );
            if( $tags ){
                $this->respon( 1,$tags );
            }else{
                $this->respon( 1,array() );
            }
        }else{
            $this->respon(0,'设置失败');
        }
    }

    public function setShopTagAction(){

        $shopId = $this->getPost('shopId');
        if( !intval($shopId) ) $this-respon( 0,'设置失败' );
        $values = $this->getPost('values');
        $tags = json_decode( $values,true );
        if( !$tags || !is_array( $tags ) || !isset($tags['0']['name']) ) 
            $this->respon(0,'设置失败');

        $res = Service::getInstance('shop')->addTag( $tags,$shopId );

        if( $res ){
            $data['shopName'] = Service::getInstance('shop')->getShopNameById( $shopId );
            $data['shopId']   = $shopId;
            $this->respon( 1,$data );
        }else{
            $this->respon( 0,'设置失败' );
        }
    }

    public function getShopScoreAction(){

        $shopId = $this->getPost('shopId');
        if( !intval($shopId) ) $this-respon( 0,'参数错误' );

        $endDay = array(
                '5'=>'31',
                '6'=>'30',
                '7'=>'31',
                '8'=>'31',
                '9'=>'30',
                '10'=>'31',
                '11'=>'30',
                '12'=>'31'
            );

        $year = date('Y');
        $month = date('m');
        $day = date('j')-10;

        $fontYear = $year;
        $fontMonth = $month;

        if( $day < 8 ){
            $fontMonth = $month - 1;
            if( $fontMonth === 0 ){
                $fontMonth = 12;
                $fontYear = $year - 1;
            }

            $need = 8 - $day;
            $start = $endDay[$fontMonth] - $need;

            $fontPart = $this->getRangeDayScore($start,$endDay[$fontMonth],$shopId,$fontYear,$fontMonth);

            $currPart = $this->getRangeDayScore(0,$day,$shopId,$year,$month);

            $result = array();

            $result = array_merge($fontPart,$currPart);

        }else{
            $start = $day - 8;
            $result = $this->getRangeDayScore($start,$day,$shopId,$year,$month);
        }
        $this->respon( 1,$result );

    }
    /**
     * [getRangeDayScore description]
     * @param  [type] $start  [偏移量]
     * @param  [type] $end    [结束值]
     * @param  [type] $shopId [店铺ID]
     * @param  [type] $year   [当前年份]
     * @param  [type] $month  [当前月份]
     * @return [type]         [一维从start到end的数组]
     */
    public function getRangeDayScore($start,$end,$shopId,$year,$month){

        $cols = '';
        for( $i = $start + 1;$i<=$end;$i++ ){
            $cols .= 'day_'.$i.',';
        }
        $cols = rtrim( $cols,',' );

        $sql = " SELECT $cols FROM shop_score WHERE shop_id = '{$shopId}' AND year = '{$year}' AND month = '{$month}' ";

        return $this->db->fetchRow( $sql );

    }

}