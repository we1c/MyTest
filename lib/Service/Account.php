<?php

class Service_Account extends Service
{
    //添加
    public function add( $data ) {
	    $this->db->insert('account_shop', $data);
	    return $this->db->lastInsertId();
	}
	
	//结算申请
	public function AccountList($page, $perpage,$keyword,$devId='',$status,$start,$end){
		$map = "";
		/*if ( $status == '1' ) {
			$map .= " AND A.audit_status = '0' ";
		}elseif( $status == '2') {
			$map .= " AND A.audit_status IN (1,2) ";
		}elseif( $status == '3') {
			$map .= " AND A.audit_status = '2' ";
		}elseif( $status == '4') {
			$map .= " AND A.audit_status = '3' ";
		} */
		if ( $devId != '') {
			$map .= " AND A.devId = {$devId} ";
		}
		if ( $keyword != '') {
			$map .= " AND B.scode LIKE '%{$keyword}%' ";
		}
		if( $start != '' && $end != '')
	    {
	        $map .= " AND A.createTime BETWEEN '{$start}' AND '{$end}' ";
	    }
	    $sql = " SELECT 
	    		A.id,
	    		A.shopId,
	    		A.devId,
	    		A.total,
	    		A.createTime,
	    		A.expectTime,
	    		A.note,
	    		A.audit_note,
	    		A.audit_status,
	    		A.type,
	    		A.auditTime,
	    		B.name AS shopName,
	    		B.scode,
	    		B.category,
	    		C.name AS devName 
	    		FROM account_shop AS A 
	    		LEFT JOIN shop AS B ON A.shopId = B.id 
	    		LEFT JOIN developers AS C ON A.devId = C.id 
	    		WHERE A.audit_status = {$status} 
	    		$map ORDER BY id DESC ".$this->db->buildLimit($page, $perpage);
//                        echo $sql;exit;
	    $data['list'] = $this->db->fetchAll($sql);
	    $sql = " SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id  $map ";
	    $data['total'] = $this->db->fetchOne($sql);
	    $sql = " SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id WHERE A.audit_status = 0 $map ";
	    $data['total0'] = $this->db->fetchOne( $sql );

	    $sql = " SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id WHERE A.audit_status = 1 $map ";
	    $data['total1'] = $this->db->fetchOne( $sql );

	    $sql = " SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id WHERE A.audit_status = 2 $map ";
	    $data['total2'] = $this->db->fetchOne( $sql );

	    $sql = " SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id WHERE A.audit_status = 3 $map ";
	    $data['total3'] = $this->db->fetchOne( $sql );

	    $sql = " SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id WHERE A.audit_status = 4 $map ";
	    $data['total4'] = $this->db->fetchOne( $sql );
	    return $data;
	}

	//id查询account_shop
	public function getAccountById ($id) {
		$sql = " SELECT 
	    		A.id,
	    		A.shopId,
	    		A.devId,
	    		A.total,
	    		A.createTime,
	    		A.expectTime,
	    		A.note,
	    		A.audit_note,
	    		A.audit_status,
	    		A.type, 
	    		B.name AS shopName, 
	    		B.category, 
	    		C.name AS devName 
	    		FROM account_shop AS A 
	    		LEFT JOIN shop AS B ON A.shopId = B.id 
	    		LEFT JOIN developers AS C ON A.devId = C.id 
	    		WHERE A.id = $id ";
	    return $this->db->fetchRow($sql);
	}

	//	详情
	public function getDetail($id){
		$sql = "SELECT 
						A.id,
						A.payTime,
						A.orderCode,
						A.number,
						A.price,
						A.goodsId,
						A.accountId,
						A.account_status,
						B.goodsNo,
						B.code,
						C.id AS shopId,
						C.name AS shopName,
						C.category,
						C.scode, 
						D.real_certificate,
						D.real_pack,
						D.real_other,
						D.real_freight,
						E.goods_name AS name,
						E.goods_price AS purchPrice    
					FROM orders AS A 
					LEFT JOIN goods AS B ON A.goodsId = B.id  
					LEFT JOIN shop AS C ON A.shopId = C.id 
					LEFT JOIN orders_price AS D ON A.id = D.orderId  
					LEFT JOIN orders_goods AS E ON A.id = E.order_id 
					WHERE A.accountId = {$id}";
		$data = $this->db->fetchAll($sql);
		foreach ($data as $k => $v) {
			$data[$k]['payTime'] = date('Y-m-d',$v['payTime']);
		}
		return $data;
	}

	//修改
	public function update( $data,$id ) {
	    return $this->db->update( 'account_shop' , $data, ' id = '.$id);
	}

	//删除
	public function delAccount( $id ) {
	    return $this->db->delete( 'account_shop',' id = '.$id );
	}

	//财务审核
	public function AuditList($page, $perpage,$keyword,$status='',$devId='',$start,$end){
		$map = ' WHERE A.id <> "0" ';
			if ( $status == '0' ) {
				$map .= " AND A.audit_status = '0' ";
			}elseif( $status == '1') {
				$map .= " AND A.audit_status IN (1,2) ";
			}elseif( $status == '2') {
				$map .= " AND A.audit_status = '3' ";
			} 
		if ( $devId != '') {
			$map .= " AND A.devId = {$devId} ";
		}
		if ( $keyword != '') {
			$map .= " AND B.scode LIKE '%{$keyword}%' ";
		}
		if( $start != '' && $end != '')
	    {
	        $map .= " AND A.createTime BETWEEN '{$start}' AND '{$end}' ";
	    }
	    $sql = " SELECT 
	    		A.id,
	    		A.shopId,
	    		A.devId,
	    		A.total,
	    		A.real_total,
	    		A.createTime,
	    		A.expectTime,
	    		A.note,
	    		A.audit_note,
	    		A.audit_status,
	    		A.type,
	    		A.auditTime,
	    		B.name AS shopName,
	    		B.scode,
	    		B.category,
	    		C.name AS devName 
	    		FROM account_shop AS A 
	    		LEFT JOIN shop AS B ON A.shopId = B.id 
	    		LEFT JOIN developers AS C ON A.devId = C.id 
	    		$map ORDER BY id DESC ".$this->db->buildLimit($page, $perpage);
	    $data['list'] = $this->db->fetchAll($sql);
	    //$sql = " SELECT count(*) FROM account_shop AS A $map ";
	    //$data['total'] = $this->db->fetchOne($sql);
	    $data['total0'] = $this->db->fetchOne(" SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id LEFT JOIN developers AS C ON A.devId = C.id WHERE A.audit_status = '0' ");
	    $data['total1'] = $this->db->fetchOne(" SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id LEFT JOIN developers AS C ON A.devId = C.id WHERE  A.audit_status IN (1,2) ");
	    $data['total2'] = $this->db->fetchOne(" SELECT count(*) FROM account_shop AS A LEFT JOIN shop AS B ON A.shopId = B.id LEFT JOIN developers AS C ON A.devId = C.id WHERE  A.audit_status = '3' ");
	    return $data;
	}

	public function getAccountExport($keyword,$devId,$status,$start,$end){
		$map = ' WHERE A.id <> "0" ';
		if ( $status == '1' ) {
			$map .= " AND A.audit_status = '0' ";
		}elseif( $status == '2') {
			$map .= " AND A.audit_status IN (1,2) ";
		}elseif( $status == '3') {
			$map .= " AND A.audit_status = '2' ";
		}elseif( $status == '4') {
			$map .= " AND A.audit_status = '3' ";
		} 
		if ( $devId != '') {
			$map .= " AND A.devId = {$devId} ";
		}
		if ( $keyword != '') {
			$map .= " AND B.name LIKE '%{$keyword}%' ";
		}
		if( $start != '' && $end != '')
	    {
	        $map .= " AND A.createTime BETWEEN '{$start}' AND '{$end}' ";
	    }
	    $sql = " SELECT 
	    		A.id,
	    		A.shopId,
	    		A.devId,
	    		A.total,
	    		A.createTime,
	    		A.expectTime,
	    		A.note,
	    		A.audit_note,
	    		A.audit_status,
	    		A.type,
	    		A.auditTime,
	    		B.name AS shopName,
	    		B.scode,
	    		B.category,
	    		C.name AS devName, 
	    		D.id,
				D.payTime,
				D.orderCode,
				D.number,
				D.price,
				D.goodsId,
				D.account_status,
				E.real_certificate,
				E.real_pack,
				E.real_other,
				E.real_freight, 
				F.name,
				F.goodsNo,
				F.code,
				F.purchPrice 
	    		FROM account_shop AS A 
	    		LEFT JOIN shop AS B ON A.shopId = B.id 
	    		LEFT JOIN developers AS C ON A.devId = C.id 
	    		LEFT JOIN orders AS D ON A.id = D.accountId
	    		LEFT JOIN orders_price AS E ON D.id = E.orderId 
				LEFT JOIN goods AS F ON D.goodsId = F.id 
	    		$map ORDER BY A.id DESC ";
	    $data = $this->db->fetchAll($sql);
	    foreach ($data as $k => $v) {
	    	$data[$k]['createTime'] = date('Y-m-d',$v['createTime']);
	    	if ($v['category'] == 1) {
		    	$data[$k]['category'] = "只有一个"; 
	    	}
	    	switch ($v['audit_status']) {
	    		case '0':
	    			$data[$k]['audit_status'] = "未审核";
	    			break;
	    		case '1':
	    			$data[$k]['audit_status'] = "已审核";
	    			break;
	    		case '2':
	    			$data[$k]['audit_status'] = "有驳回审核";
	    			break;
	    		case '3':
	    			$data[$k]['audit_status'] = "全驳回审核";
	    			break;
	    	}
	    	$data[$k]['payTime'] = date('Y-m-d',$v['payTime']);
	    	switch ($v['account_status']) {
	    		case '0':
	    			$data[$k]['account_status'] = "未提交";
	    			break;
	    		case '1':
	    			$data[$k]['account_status'] = "待结算";
	    			break;
	    		case '2':
	    			$data[$k]['account_status'] = "已结算";
	    			break;
	    		case '3':
	    			$data[$k]['account_status'] = "被驳回";
	    			break;
	    	}
	    	$data[$k]['payable'] = ($v['purchPrice']*$v['number'])+$v['real_certificate']+$v['real_pack']+$v['real_other']+$v['real_freight'];
	    }
	    return $data;
	}

}