<?php

class Service_Distributor extends Service
{
	private $error;
	public function getError(){
		return $this->error;
	}
	
	public function getList($page, $perpage,$keyword,$status)
	{
	    $map = ' WHERE A.id <> 0 ';
	    if($keyword !='')
	    {
	        $map = " AND A.name LIKE '%{$keyword}%'";
	    }
	    if(!$status){
	    	$map .= " AND A.status = 0 ";
	    }else{
	    	$map .= " AND A.status = 1 ";
	    }
	    $sql = "SELECT A.*,B.name AS devName FROM channel AS A LEFT JOIN developers AS B ON A.devId = B.id $map ORDER BY A.id desc".$this->db->buildLimit($page, $perpage);
	    $list = $this->db->fetchAll($sql);
	    foreach ($list as $k=>$v) {
	   		$default = "/default-dis-image.png";
	        $list[$k]['headimgurl'] = Service::getInstance('shop')->getAvata( $v['headimgurl'],$default );
	    }
	    $data['list'] = $list;
	     
	    $sql = "SELECT count(*) FROM channel AS A LEFT JOIN developers AS B ON A.devId = B.id $map ";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
	}
	
	public function enable($id) {
	    $res = $this->db->update( 'channel', array('status'=>'0'),' id=' . $id );
	    return $res;
	}
	
	
	public function disable($id) {
	    $res = $this->db->update( 'channel', array('status'=>'1'),' id=' . $id );
	    return $res;
	}
	
	public function edit( $user,$id ){
	    return $this->db->update('channel', $user,' id='.$id);
	}
	public function add( $name, $domain, $devId, $payway, $paybank, $headimgurl ) {
	    if ( !$name ) return false;
	    if ( !$domain ) return false;
	    $user['name'] = $name;
	    $user['domain'] = $domain;
	    $user['devId'] = $devId;
	    $user['userId'] = Yaf_Registry::get('uid');
	    $md5str = $domain.time().rand(1111, 9999);
	    $user['`key`'] = md5($md5str);
	    $user['payway'] = $payway;
	    $user['paybank'] = $paybank;
	    if ( $headimgurl) $user['headimgurl'] = $headimgurl;
	    
	    $this->db->insert('channel', $user);
	    return $this->db->lastInsertId();
	}

	public function del( $id,$status ) {
		return $this->db->update('`channel`', array('status'=>$status),' id = '.$id);
	}
	
	public function getInfoById( $id ) {
	    $sql = "SELECT id,name,domain,devId,payway,paybank,headimgurl,apiType,apiImg,apiDown,clearing_type,credit_limit FROM `channel` WHERE `id` = {$id}";
	    $data = $this->db->fetchRow( $sql );
	    $default = "/default-dis-image.png";
	    $data['headimgurl'] = Service::getInstance('shop')->getAvata( $data['headimgurl'],$default );
	    return $data;
	}
	public function getAllDis() {
	    $sql = "SELECT id,name FROM `channel`";
	    $data = $this->db->fetchAll( $sql );
	    return $data;
	}

	public function getMyDis($dis,$role,$devId = ''){
            $map = '';
            if($devId != ''){
                $map .= " AND devId = {$devId}";
            }
	    if ( in_array('1',$role) || in_array('6',$role)  ) {
	        $sql = "SELECT * FROM `channel` WHERE 1=1 {$map}";
	    } else {
	        $sql = "SELECT * FROM `channel` WHERE id IN ( ".$dis." ) {$map}";
	    }
		return $this->db->fetchAll( $sql );
	}

	public function getManagerById( $id ){
		return $this->db->fetchOne( 'SELECT devId FROM channel WHERE id = '.$id );
	}

	public function checkExist( $name ) {
	    $sql = "SELECT id FROM `channel` WHERE `name` = '{$name}'";
	    $data = $this->db->fetchOne( $sql );
	    return $data;
	}

	//获取分销商头像
	public function getAvata( $hash )
	{
	    $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
	    $url = Yaf_Application::app()->getConfig()->get('image')->get('url');
	    $avatar_url = $url . "/default-dis-image.png";
	
	    if ( empty($hash) ) return $avatar_url;
	
	    if ( file_exists( Util::getDir( $dir , $hash ).$hash."_avatar.jpg" ) )
	    {
	        $url = $url = Yaf_Application::app()->getConfig()->get('image')->get('url');
	        $avatar_url = $url . Util::getPath( $hash ) . $hash . "_avatar.jpg";
	    }
	    return $avatar_url;
	}

	//查询渠道倍数ID by分销商，店铺
	public function getCtimesShopIdByChannel( $channel ){
		$sql = " SELECT shopId FROM channel_shop_ctimes 
					WHERE channelId = {$channel} ";
		return $this->db->fetchAll( $sql );
	}

	//查询渠道倍数by分销商
	public function getCtimesByChannel( $channel ){
		$sql = " SELECT shopId,ctimes FROM channel_shop_ctimes 
					WHERE channelId = {$channel} ";
		$data = $this->db->fetchAll( $sql );
		foreach ($data as $k => $v) {
			$list[$v['shopId']] = $v['ctimes']; 
		}
		return $list;
	}

	//添加渠道倍数
	public function addCtimes( $sql ){
		return $this->db->query( $sql );
	}

	//修改渠道倍数
	public function editCtimes( $ctimes ,$channel ,$shop ){
		$sql = "UPDATE channel_shop_ctimes SET ctimes = '{$ctimes}' WHERE channelId = {$channel} AND shopId = {$shop} ";
		return $this->db->query( $sql );
	}

	//查询渠道倍数by分销商 店铺
	public function getCtimesByChannelAndShop( $channel,$shop ){
		$sql = " SELECT ctimes FROM channel_shop_ctimes 
					WHERE channelId = {$channel} AND shopId = {$shop} ";
		return $this->db->fetchOne( $sql );
	}
        
    //以渠道负责人分组，获得渠道
    public function getGroupDis($dis,$role) {
        if ( in_array('1',$role) || in_array('6',$role)  ) {
	        $sql = "SELECT devId,count(devId) as num FROM `channel` GROUP BY devId ORDER BY devId DESC";
	    } else {
	        $sql = "SELECT devId,count(devId) as num FROM `channel` WHERE id IN ( ".$dis." ) GROUP BY devId ORDER BY devId DESC";
	    }	    
	    $data = $this->db->fetchAll( $sql );
	    return $data;
	}

	//更新分销商可用已用授信额度
	public function updateUsedLimit( $data,$id ){
		$sql = "UPDATE `erp`.`channel` SET `used_limit`= used_limit+{$data} WHERE (`id`={$id})";
		$res = $this->db->query( $sql );
		return $res;
	}

	//重置分销商可用已用授信额度
	public function resetUsedLimit( $id ){
		$res = $this->db->update('channel',array('used_limit'=>0),' id = '. $id );
		return $res;
	}

	//获取授信额度
	public function getCreditLimit( $id ){
		$sql = " SELECT credit_limit FROM channel WHERE id = {$id} ";
		$res = $this->db->fetchOne( $sql );
		return $res;
	}
}