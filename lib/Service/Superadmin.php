<?php

class Service_Superadmin extends Service{

	private $error;
	public function getError() {
		return $this->error;
	}

	public function makeThumbFac( $file , $size = array() ,$info = '' ){
		$imgObj = new Image();
        if( empty($size) ) $size = array( '100x100','800x800' );

        if( file_exists( $file ) ){
            $imgObj->open( $file );

            $wh = $imgObj->size();
            if( $wh[0] > $wh[1] ){
                $w = $h = $wh[1];
            }else{
                $w = $h = $wh[0];
            }
            $x = ( $wh[0] - $w )/2;
            $y = ( $wh[1] - $h )/2;

            $original = explode( '_image' ,$file );
            foreach( $size as $k => $v ){
                $thumb = implode( '_thumb_'.$v , $original );
                if( !file_exists( $thumb ) ){
                    //获取缩略的尺寸数组array('800','800');0=>width;1=>eight;
                    $mm = explode( 'x',$v );
                    $imgObj->crop( $w ,$h ,$x ,$y ,$mm[0] ,$mm[1] );
                    $imgObj->save( $thumb );
                    $imgObj->open( $file );
                }
            }
        }
        $imgObj = null;
    }

    public function insertNode( $sql ){
        if( $this->db->query( $sql ) ){
            return $this->db->lastInsertId();
        }else{
            return false;
        }
    }



}