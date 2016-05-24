<?php
//报表
class Service_Report extends Service
{
	//日收支报表
	public function getReportDay($channel,$shop,$start,$end){
		$map = ' WHERE A.isPay <> "0" AND A.isDel = "0" AND A.reback = "0" AND A.sellType IN (0,4,5,6) AND A.isDelete = 0 ';
		if ( $shop != '') {
			$map .= " AND E.scode = '{$shop}' ";
		}
		if ( $channel != '') {
			$map .= " AND D.name LIKE '{$channel}' ";
		}
		if( $start != '' && $end != '')
	    {
	        $map .= " AND A.payTime BETWEEN '{$start}' AND '{$end}' ";
	    }
	    $sql = " SELECT
	    		A.shopId,
	    		A.channel,
	    		A.number,
	    		A.payTime,
	    		A.price,
	    		B.goods_price AS purchPrice,
	    		C.price_freight,
	    		C.price_pack,
	    		C.price_certificate,
	    		C.price_fluctuate,
	    		C.real_freight,
	    		C.real_pack,
	    		C.price_platf,
	    		C.price_transfer,
	    		D.name,
	    		E.scode
	    		FROM orders AS A
	    		LEFT JOIN orders_goods AS B ON A.id = B.order_id
	    		LEFT JOIN orders_price AS C ON A.id = C.orderId
	    		LEFT JOIN channel AS D ON A.channel = D.id
	    		LEFT JOIN shop AS E ON A.shopId = E.id 
	    		$map ORDER BY A.payTime DESC ";
	    $list = $this->db->fetchAll($sql);
	    $dayList = array();
	    foreach ($list as $key => $value) {
    		$ctimes = Service::getInstance('distributor')->getCtimesByChannelAndShop( $value['channel'],$value['shopId'] );

	        $shop = Service::getInstance('shop')->getShopinfo($value['shopId']);
	        $orderPurchPrice = round($value['purchPrice']*$value['number']);

	        $platPrice = round($orderPurchPrice*$shop['ptimes']);

	        $channelPrice = round($ctimes*$platPrice);

	        $income= $channelPrice+$value['price_freight']+$value['price_pack']+$value['price_certificate']+$value['price_fluctuate'];

	        $expenses = $orderPurchPrice+$value['real_freight']+$value['real_pack']+$value['price_platf']+$value['price_transfer'];

	        $day = date('Y-m-d',$value['payTime']);

	    	@$dayList[$day]['day'] = $day;
	    	@$dayList[$day]['platPrice'] += $platPrice;
	    	@$dayList[$day]['channelPrice'] += $channelPrice;
	    	/*@$dayList[$day]['price_freight'] += $list[$key]['price_freight'];
	    	@$dayList[$day]['price_pack'] += $list[$key]['price_pack'];
	    	@$dayList[$day]['price_certificate'] += $list[$key]['price_certificate'];
	    	@$dayList[$day]['price_fluctuate'] += $list[$key]['price_fluctuate'];
	    	@$dayList[$day]['income'] += $list[$key]['income'];*/
	    	@$dayList[$day]['price'] += $list[$key]['price'];
	    	@$dayList[$day]['purchPrice'] += $orderPurchPrice;
	    	@$dayList[$day]['real_freight'] += $list[$key]['real_freight'];
	    	@$dayList[$day]['real_pack'] += $list[$key]['real_pack'];
	    	@$dayList[$day]['price_platf'] += $list[$key]['price_platf'];
	    	@$dayList[$day]['price_transfer'] += $list[$key]['price_transfer'];
	    	@$dayList[$day]['expenses'] += $expenses;
	    	@$dayList[$day]['number'] += $list[$key]['number'];
	    	@$dayList[$day]['orderNum']++;
	    	@$dayList[$day]['profit'] = $dayList[$day]['price']-$dayList[$day]['expenses'];
	    	@$dayList[$day]['rate'] = (round((($dayList[$day]['price']-$dayList[$day]['expenses'])/$dayList[$day]['price']),4)*100).'%';
	    	@$dayList[$day]['distribution'] = (round((($dayList[$day]['real_freight']+$dayList[$day]['real_pack'])/$dayList[$day]['price']),4)*100).'%';
	    }
	    $monthTotal = array();
	    foreach ($dayList as $k => $v) {
	    	@$monthTotal['day'] = '总计';
	    	@$monthTotal['platPrice'] += $v['platPrice'];
	    	@$monthTotal['channelPrice'] += $v['channelPrice'];
	    	/*@$monthTotal['price_freight'] += $v['price_freight'];
	    	@$monthTotal['price_pack'] += $v['price_pack'];
	    	@$monthTotal['price_certificate'] += $v['price_certificate'];
	    	@$monthTotal['price_fluctuate'] += $v['price_fluctuate'];
	    	@$monthTotal['income'] += $v['income'];*/
	    	@$monthTotal['price'] += $v['price'];
	    	@$monthTotal['purchPrice'] += $v['purchPrice'];
	    	@$monthTotal['real_freight'] += $v['real_freight'];
	    	@$monthTotal['real_pack'] += $v['real_pack'];
	    	@$monthTotal['price_platf'] += $v['price_platf'];
	    	@$monthTotal['price_transfer'] += $v['price_transfer'];
	    	@$monthTotal['expenses'] += $v['expenses'];
			@$monthTotal['orderNum'] += $v['orderNum'];
	    	@$monthTotal['number'] += $v['number'];
	    	@$monthTotal['profit'] = $monthTotal['price'] - $monthTotal['expenses'];
	    	@$monthTotal['rate'] = (round((($monthTotal['price']-$monthTotal['expenses'])/$monthTotal['price']),4)*100).'%';
	    	@$monthTotal['distribution'] = (round((($monthTotal['real_freight']+$monthTotal['real_pack'])/$monthTotal['price']),4)*100).'%';
	    }
	    $data['total'] = count($dayList);
	    $data['list'] = $dayList;
	    $data['monthTotal'] = $monthTotal;
	    return $data;
	}
}