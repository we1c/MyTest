<?php

class ShareController extends BaseController
{


	public function indexAction( )
	{

		$jid = $this->getParam('jid',1);
		$thumb = '400';
		$res = $this->goods( $jid , '1' , $thumb );

		$res = json_decode( $res , true );
		$list = array();

		if ( isset( $res['data'] ) )
		{
			$list = $res['data'];
		}

		$this->_view->jid = $jid;
		$this->_view->list = $list;
	}

	public function getlistAction( )
	{
		$jid = $this->getParam('jid',1);
		$page = $this->getQuery('page',2);
		$thumb = $this->getQuery('thumb','800');
		$res = $this->goods( $jid , $page ,$thumb );
		exit($res);
	}

	public function goods(  $jid , $page = 1 ,$thumb = '' )
	{
		$data['jid'] = intval( $jid );
		$data['page'] = $page;
		$data['thumb'] = $thumb;
		$res = $this->post( "http://uapi.1ge.com/index/goods/" , $data );
		return $res;
	}

	public function xiuhuagoods( $jid , $page = 1 )
	{
		$data['jid'] = intval( $jid );
		$data['page'] = $page;
		$res = $this->post( "http://uapi.1ge.com/index/xiuhuagoods/" , $data );
		return $res;
	}


	public function post( $url , $data = array()  )
	{
		//exit( $url );
		$data['t'] = time();
		$data['token'] = md5( $data['t'] . "d11edd70fed958651b4619131ffbb236" );
		//dump( $data );
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$res = curl_exec ( $ch );
		curl_close ( $ch );	

		return $res;
	}

}