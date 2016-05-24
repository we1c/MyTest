<?php
/**
 * @require PHP>=5.3
 */
require_once __DIR__ . '/KdtApiProtocol.php';
require_once __DIR__ . '/SimpleHttpClient.php';

class KdtApiClient {
	
	private $_common;
	private $_sysPairs;
	private $_sign;

	public function __construct( $common ) {
		if ( !$common ) throw new Exception('公共配置参数不能为空');
		$this->_common = $common;
	}

	public function sendImg( $action,$params = array(), $localPath = array() ){

		if( !$localPath || !is_array( $localPath ) ) return false;

		$pairs = $this->buildRequestParams( $action,$params );
		$url = $this->_makeUrl( $pairs );

		$result = array();
		foreach( $localPath as $k => $file ){
			$files = array( array( 'url'=>$file,'field'=>'photoImg' ) );
			$respon = $this->parseResponse( SimpleHttpClient::post( $url,$pairs,$files ) );
			if( $respon['Code'] === 0 ){
				$result['success'][] = $respon['Data'];
			}else{
				$result['error'][] = $respon;
			}
		}
		return $result;
	}

	public function get($action, $params = array()) {
		$pairs = $this->buildRequestParams( $action, $params );
		return $this->parseResponse( SimpleHttpClient::get($this->_common['url'], $pairs ) );
	}
	
	public function post($action, $params = array(), $files = array()) {
		$pairs = $this->buildRequestParams( $action, $params );
		$params['p'] = $pairs['p'];
		unset( $pairs['p'] );
		$url = $this->_makeUrl( $pairs );
		return $this->parseResponse( SimpleHttpClient::post( $url, $params , $files ) );
	}

	private function _makeUrl( $pairs ){
		$domain = $this->_common['url'];
		if( !is_array( $pairs ) || empty( $pairs ) ) return $domain;
		$urlGet = '';
		foreach( $pairs as $key => $value ){
			$urlGet .= $key.'='.$value.'&';
		}
		return $domain.'?'.rtrim( $urlGet, '&' );
	}
	
	public function setFormat($format) {
		if (!in_array($format, KdtApiProtocol::allowedFormat()))
			throw new Exception('设置的数据格式错误');	
		$this->format = $format;
		return $this;
	}
	
	public function setSignMethod($method) {
		if (!in_array($method, KdtApiProtocol::allowedSignMethods()))
			throw new Exception('设置的签名方法错误');
		$this->signMethod = $method;
		return $this;
	}
	
	private function parseResponse($responseData) {
		$data = json_decode($responseData, true);
		if (null === $data) throw new Exception('response invalid, data: ' . $responseData);
		return $data;
	}
	
	private function buildRequestParams( $action, $apiParams ) {
		if (!is_array($apiParams)) $apiParams = array();
		$pairs = $this->getCommonParams( $action );
		foreach ( $apiParams as $k => $v ) {
			if ( isset($pairs[$k]) ) throw new Exception('参数名冲突');
			$pairs[$k] = $v;
		}
		$sign = KdtApiProtocol::sign( $this->_common['token'], $pairs, $this->_common['signMethod'] );
		$pairs[KdtApiProtocol::SIGN_KEY] = $sign;
		$this->_sign = $sign;
		return $pairs;
	}
	//cmd=%s&c=1&t=%s&sign=%s&f=json&v=%s&p=%s',
	private function getCommonParams( $action ) {
		$params = array();
		$params[KdtApiProtocol::CMD_KEY] = $action['cmd'];
		$params[KdtApiProtocol::C_KEY] = $this->_common['client'];
		$params[KdtApiProtocol::T_KEY] = time();
		$params[KdtApiProtocol::F_KEY] = $this->_common['format'];
		$params[KdtApiProtocol::V_KEY] = $action['v'];
		$this->_sysPairs = $params;
		return $params;
	}
}
