<?php

class Service_Shop extends Service
{
	private $error;
	public function getError(){
		return $this->error;
	}
	
	public function shoplist($page, $perpage,$keyword)
	{
	    $map = '';
	    if($keyword !='')
	    {
	        $map = " WHERE A.name like '%{$keyword}%' ";
	    }
	    $sql = "SELECT A.*,B.name AS devName FROM shop as A LEFT JOIN developers AS B ON A.principal = B.id $map ORDER BY A.id DESC".$this->db->buildLimit($page, $perpage);
	    $list = $this->db->fetchAll($sql);
	    foreach ($list as $k=>$v) {
	        /*if($v['province'] && $v['city'] && $v['area']) {
	            $province = $this->getProCityAreaName($v['province']);
    	        $city = $this->getProCityAreaName($v['city']);
    	        $area = $this->getProCityAreaName($v['area']);
    	        $list[$k]['pname'] = $province['name'];
    	        $list[$k]['cname'] = $city['name'];
    	        $list[$k]['aname'] = $area['name'];
	        }*/
    	    $list[$k]['headimgurl'] = $this->getAvata( $v['headimgurl'] );
    	    $list[$k]['score']      = $this->getShopScoreToday( $v['id'] );
	    }
	    $data['list'] = $list;
	     
	    $sql = "SELECT count(*) FROM shop as A $map ";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
	}
	
	public function shopApiList($uid) {
	    $data = $this->db->fetchAll("SELECT A.* FROM shop as A left join shopkeeper as B on A.id = B.sid WHERE B.uid = {$uid} ORDER BY A.id ASC ");
	    foreach ( $data as $k=>$v ) {
	        $data[$k]['headimgurl'] = $this->getAvata($v['headimgurl']);
	    }
	    return $data;
	}
	
	public function getShopinfo($id)
	{
	    $sql = "SELECT * FROM shop WHERE id=".$id;
	    $data = $this->db->fetchRow($sql);
	    return $data;
	}

	public function getShopNameById($id)
	{
	    $sql = "SELECT name FROM shop WHERE id=".$id;
	    $data = $this->db->fetchOne($sql);
	    return $data;
	}
	
	public function edit($shop,$id){
	    return $this->db->update('shop', $shop,' id='.$id);
	}
	public function add($shop)
	{
	    $this->db->insert('shop', $shop);
	    return $this->db->lastInsertId();
	}
	
	public function getProvince() {
	    $sql = 'SELECT id,name FROM city WHERE parent_id =0 ORDER BY sort_num';
	    return $this->db->fetchAll($sql);
	}
	public function getCity($pid) {
	    $sql = 'SELECT id,name FROM city WHERE parent_id =' . $pid . ' ORDER BY sort_num';
	    return $this->db->fetchAll($sql);
	}
	public function getArea($pid) {
	    $sql = 'SELECT id,name FROM city WHERE parent_id =' . $pid . ' ORDER BY sort_num';
	    return $this->db->fetchAll($sql);
	}
	public function getCityByName($pid) {
	    $sql = "SELECT id,name FROM city WHERE name ='{$pid}' ORDER BY sort_num";
	    $res = $this->db->fetchRow($sql);
	    return $this->getCity( $res['id'] );
	}
	public function getAreaByName($pid) {
	    $sql = "SELECT id,name FROM city WHERE name ='{$pid}' ORDER BY sort_num";
	    $res = $this->db->fetchRow($sql);
	    return $this->getArea( $res['id'] );
	}
	
	public function updateUid($uid,$id) {
	    return $this->db->update('shop', array('onwer'=>$uid),' id='.$id);
	}


	public function getshopIdbyUid($uid) {
	    return $this->db->fetchOne("SELECT id FROM shop WHERE onwer = '".$uid."'");

	}
	public function getProCityAreaName($id) {
	    $sql = 'SELECT id,name FROM city WHERE id = '.$id;
	    return $this->db->fetchRow($sql);
	}
	
	public function getList() {
	    $data = $this->db->fetchAll('SELECT * FROM shop ORDER BY id DESC');
	    foreach ($data as $k=>$v) {
	        $data[$k]['proname'] = $this->getProCityAreaName($v['province']);
	        $data[$k]['cityname'] = $this->getProCityAreaName($v['city']);
	        $data[$k]['areaname'] = $this->getProCityAreaName($v['area']);
	    }
	    return $data;
	}
	
	public function addRelation($sid,$uid) {
	    if( !empty($uid) && $sid ){
	    	$sql = "INSERT INTO `shopkeeper` (`uid`, `sid`) VALUES ";
		    foreach ($uid as $k=>$v){
		        $sql .="('{$v}', '{$sid}' ),";
		    }
		    $sql = substr($sql, 0,-1);
		    $this->db->query($sql);
		    return $this->db->lastInsertId();
		}else{
			return false;
		}
	}

	public function addCtimes($channel,$shop,$ctimes = '1.00'){
		$sql = " INSERT INTO `channel_shop_ctimes` (`channelId`,`shopId`,`ctimes`,`updateTime`) VALUES ";
		$values = '';
        foreach ($channel as $k=>$v){
            if ($v != '') {
                $values .="('{$v['id']}', '{$shop}', '{$ctimes}' , ".time()." ),";
            }
        }
        if (isset($values)) {
            $values = rtrim($values, ',');
            return $this->db->query($sql.$values);
        }
	}

	public function editRelation($sid,$uid) {
	    $this->db->delete('shopkeeper',' sid='.$sid);
	    $sql = "INSERT INTO `shopkeeper` (`uid`, `sid`) VALUES ";
	    foreach ($uid as $k=>$v){
	        $sql .="('{$v}', '{$sid}' ),";
	    }
	    $sql = substr($sql, 0,-1);
	    $this->db->query($sql);
	    return $this->db->lastInsertId();
	}
	
	public function del($id) {
	    return $this->db->delete('shop',' id='.$id);
	}
	
	public function getScode() {
	    $shop = $this->db->fetchRow("SELECT id,scode FROM shop ORDER BY id DESC LIMIT 1");
	    if ($shop) {
	        $s = $shop['scode'];
	        $num = intval( substr( $s, 2 ) );
    	    if ( $num < 999 ) {
                $code = $s[0].$s[1] . str_pad( ( $num + 1 ), 3, '0', STR_PAD_LEFT );
            } else {
                if ( $s[0] == 'Z' ) {
                    if ( $s[0].$s[1] == 'ZZ' ) {
                        return false;
                    } else {
                        $code = $s[0] . chr( ord($s[1]) + 1 ) . '001';
                    }
                } else {
                    $code = chr( ord($s[0]) + 1 ) . $s[1] . '001';
                }
            }
	    } else {
            $code = 'AA001';
	    }
	    return $code;
	}
	
	public function setDefault($uid,$shopId) {
	    return $this->db->update('users', array('shopId'=>$shopId),' id ='.$uid);
	}
	
	public function getSkp($sid) {
	    $data = $this->db->fetchAll('SELECT uid FROM shopkeeper WHERE sid ='.$sid);
	    $arr = array();
	    foreach ($data as $k=>$v) {
	        $arr[] = $v['uid'];
	    }
	    return $arr;
	}
	
	public function getShopByName($name) {
	    return $this->db->fetchOne("SELECT name FROM shop WHERE name = '{$name}' ");
	}
	
	//获取店铺头像
	public function getAvata( $hash,$default = '' )
	{
	    $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
	    $url = Yaf_Application::app()->getConfig()->get('image')->get('url');
	    $avatar_url = $url . "/avatar/default-goods-image.png";
	    if ( $default) {
	    	$avatar_url = $url . $default;
	    }
	
	    if ( empty($hash) ) return $avatar_url;
	
	    if ( file_exists( Util::getDir( $dir , $hash ).$hash."_avatar.jpg" ) )
	    {
	        $url = $url = Yaf_Application::app()->getConfig()->get('image')->get('url');
	        $avatar_url = $url . Util::getPath( $hash ) . $hash . "_avatar.jpg";
	    }
	    return $avatar_url;
	}

	public function addNumday($shopId) {
		$res = $this->db->query("UPDATE shop SET num_day = num_day+'1' WHERE id = ".$shopId);
		return $res;
	}

	public function getNumday($shopId) {
		$data = $this->db->fetchOne("SELECT num_day FROM shop WHERE id =".$shopId);
		return $data;
	}

	public function getGoodsNo($shopId) {
		$res = $this->getNumday($shopId);
		$shop = str_pad($shopId,3,"0",STR_PAD_LEFT);
        $num = str_pad($res+1,3,"0",STR_PAD_LEFT);
        $data = "m".$shop.date('ymd').$num;
        return $data;
	}

	public function getShopName(){
		return $this->db->fetchAll(" SELECT id,scode FROM shop ");
	}

	public function getShopPeriodById( $shopId ){
		$sql = " SELECT period FROM `shop` WHERE id = {$shopId} ";
		$res = $this->db->fetchOne( $sql );
		return $res;
	}

	public function getPtimesById( $shopId ){
		$sql = " SELECT ptimes FROM `shop` WHERE id = {$shopId} ";
		$res = $this->db->fetchOne( $sql );
		return $res;
	}

	public function getShopIdByScode( $scode ){
		return $this->db->fetchOne( " SELECT id FROM shop WHERE scode = '{$scode}' " );
	}

	public function addTag( $tags,$shopId ){
		
		$this->db->query( " DELETE FROM `shop_tag` WHERE shop_id = '{$shopId}' " );
		
		$sql = " INSERT INTO `shop_tag` ( `shop_id`,`cat_id`,`cat_name` ) VALUES ";
		foreach( $tags as $tag ){
			$sql .= " ( '{$shopId}','{$tag['id']}','{$tag['name']}' ),";
		}
		$sql = rtrim( $sql,',' );
		return $this->db->query( $sql );

	}

	public function getTagsByShopId( $shopId ){
		$sql = " SELECT cat_id AS id,cat_name AS name FROM shop_tag WHERE shop_id = '{$shopId}' ";
		return $this->db->fetchAll( $sql );
	}

	public function getAllNormalShopIds(){
		return $this->db->fetchAll( " SELECT id FROM shop WHERE status = '0' " );
	}
        
    public function getShopScoreToday($shopId){
        $year = date('Y');
        $month = date('m');
        $day = date('j');
        $cols = 'day_'.$day;
        $score = $this->db->fetchOne( " SELECT $cols FROM shop_score WHERE shop_id = '{$shopId}' AND year = '{$year}' AND month = '{$month}' " );

        return intval($score);
    }

}