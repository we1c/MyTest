<?php

class UpdateshopController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function defaultAction()
    {
        $this->setResponse(array('wawa' => 'sdfds'));
    }

    public function updateNumDayAction() {
    	$this->db->update("shop",array("num_day"=>0)," id <> 0 ");
    }

    public function setShopScoreAction() {

        $shopIds = Service::getInstance('shop')->getAllNormalShopIds();

        if( !empty( $shopIds ) ){

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

            $year  = date('Y');
            $month = date('m');
            $day   = date('j');

            $fontYear = $year;
            $fontMonth = $month;
            $fontDay = $day - 1;
            
            if( $fontDay === 0 ){
                $fontMonth = $month - 1;
                if( $fontMonth === 0 ){
                    $fontMonth = 12;
                    $fontYear = $year -1;
                }
                $fontDay = $endDay[$fontMonth];
            }

            $fontCols = 'day_'.$fontDay;
            $cols  = 'day_'.$day;

            foreach( $shopIds as $shopId ){

                $score = 99;
                
                $font = $this->db->fetchRow( " SELECT id,$fontCols FROM shop_score WHERE shop_id = '{$shopId['id']}' AND year = '{$fontYear}' AND month = '{$fontMonth}' " );

                if( $font ) $score = $font[$fontCols] !== false ? $font[$fontCols] : 0;

                if( $day == 1 ){
                    $sql = " INSERT INTO shop_score ( shop_id,year,month,$cols ) VALUES ( '{$shopId['id']}','{$year}','{$month}','{$score}' ) ";
                }else{
                    if( isset( $font['id'] ) ){
                        $sql = " UPDATE shop_score SET {$cols}='{$score}' WHERE id = '{$font['id']}' ";
                    }else{
                        $sql = " INSERT INTO shop_score ( shop_id,year,month,$cols ) VALUES ( '{$shopId['id']}','{$year}','{$month}','{$score}' ) ";
                    }
                }

                $this->db->query( $sql );

            }
            echo "OK!";
        }
        
    }
 
}