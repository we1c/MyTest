<?php
/**
 * @require curl-extension
 */
class SimpleHttpClient {
	private static $boundary = '';
	
	public static function get($url, $params) {
		$url = $url . '?' . http_build_query($params);
		return self::http($url, 'GET');
	}

	public static function post($url, $params, $files = array()) {
		$headers = array();
		if (!$files) {
			$body = http_build_query($params);
		} else {
			$body = self::build_http_query_multi($params, $files);
			$headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
		}
		return self::http($url, 'POST', $body, $headers);
	}
	/**
	 * [sendStreamFile description]
	 * @param  [string] $url  [可带有GET参数]
	 * @param  [string] $file [绝对路径]
	 * @param  string $info   [暂时没用]
	 * @return [type]         [请求结果]
	 */
	public static function sendStreamFile( $url, $file ,$info = '' ){
		if( file_exists( $file ) ){
			$name = ltrim( strrchr( $file , '/' ) , '/' );
			//创建上传文件的对象$_FILES
			$curlFile = new CURLFile( $file ,'image/jpeg',$name );
			$body = array( 'photoImg'=> $curlFile );
			return self::_request( $url,'post', $body );
		}else{
			return false;
		}
	}

	private function _file_get_contents_fopen($url, $post = null){
	    $context = array();
	    if (is_array($post)) {
			ksort($post);
			//模拟创建POST数据
			$post = http_build_query( $post );
			//设置HTTP的请求头
			$context['http'] = array (
				'header'=>"Content-type: application/x-www-form-urlencoded\r\n"
			    . "Content-Length: " . strlen($post) . "\r\n",
				'timeout'=>60,
				'method' => 'POST',
				'content' => $post,
			);
			//创建请求字符串流
			$context = stream_context_create( $context );
	    	return file_get_contents( $url, false, $context );
	    }
	}

	/**
	 * Make an HTTP request
	 *
	 * @return string API results
	 * @ignore
	 */
	private static function http($url, $method, $postfields = NULL, $headers = array()) {
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, 'KdtApiSdk Client v0.1');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
		}

		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$httpInfo = curl_getinfo($ci);

		curl_close ($ci);
		return $response;
	}

	private static function build_http_query_multi($params, $files) {
		if (!$params) return '';

		$pairs = array();

		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $key => $value) {
			$multipartbody .= $MPboundary . "\r\n";
			$multipartbody .= 'content-disposition: form-data; name="' . $key . "\"\r\n\r\n";
			$multipartbody .= $value."\r\n";
		}
		foreach ($files as $key => $value) {
			if (!$value) {continue;}
			
			if (is_array($value)) {
				$url = $value['url'];
				if (isset($value['name'])) {
					$filename = $value['name'];
				} else {
					$parts = explode( '?', basename($value['url']));
					$filename = $parts[0];
				}
				$field = isset($value['field']) ? $value['field'] : $key;
			} else {
				$url = $value;
				$parts = explode( '?', basename($url));
				$filename = $parts[0];
				$field = $key;
			}
			$content = file_get_contents($url);
		
			$multipartbody .= $MPboundary . "\r\n";
			$multipartbody .= 'Content-Disposition: form-data; name="' . $field . '"; filename="' . $filename . '"'. "\r\n";
			$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
			$multipartbody .= $content. "\r\n";
		}

		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}

	private static function _request($url,$type='post',$data='', $ssl=true) {
		// curl完成
		$curl = curl_init();
		//设置curl选项
		curl_setopt($curl, CURLOPT_URL, $url);//URL
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '
Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36';
		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
		//SSL相关
		if ($ssl) {
			//禁用后cURL将终止从服务端进行验证
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			//检查服务器SSL证书中是否存在一个公用名(common name)
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		}
		// 处理post相关选项
		if($type == 'post'){
			curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data );// 处理请求数据
		}
		// 处理响应结果
		curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果
		// 发出请求
		$response = curl_exec($curl);
		if (false === $response) {
			echo '<br>', curl_error($curl), '<br>';
			return false;
		}
		curl_close($curl);
		return $response;
	}
	
	
	/*
	private static function request($host, $data, $method = 'GET', $timeout = 5) {
		if (is_array($data)) $data = http_build_query($data);
		
		$parse = parse_url($host);
		$method = strtoupper ($method);
		if (!$parse) return null;
		if (!isset($parse['port']) || !$parse['port']) $parse['port'] = '80';
		if (!isset($parse['path'])) $parse['path'] = '/';
		if (!in_array($method, ['POST', 'GET'])) return null;
		
		$parse['host'] = str_replace(['http://', 'https://'], ['', 'ssl://'], $parse['scheme'] . "://") . $parse['host'];
		$fp = fsockopen($parse['host'], $parse['port'], $errnum, $errstr, $timeout);
		if (!$fp) throw new Exception('connect failed: ' . $errnum . ', ' . $errstr);
		
		$contentLength = '';
		$postContent = '';
		$query = isset($parse['query']) ? $parse['query'] : '';
		$parse['path'] = str_replace(['\\', '//'], '/', $parse['path']) . "?" . $query;
		if ($method == 'GET') {
			substr($data, 0, 1) == '&' && $data = substr($data, 1);
			$parse['path'] .= ($query ? '&' : '') . $data;
		} elseif ($method == 'POST') {
			$contentLength = "Content-length: " . strlen($data) . "\r\n";
			$postContent = $data;
		}
		//echo '<a href="' .$host . '?' . $data. '">JUMP</a>';
		$write = $method . " " . $parse['path'] . " HTTP/1.0\r\n";
		$write .= "Host: " . $parse['host'] . "\r\n";
		$write .= "Content-type: application/x-www-form-urlencoded\r\n";
		$write .= $contentLength;
		$write .= "Connection: close\r\n\r\n";
		$write .= $postContent;
		fwrite($fp, $write);
		
		$responseText = '';
		while ($data = fread($fp, 4096)) {
			$responseText .= $data;
		}
		fclose($fp);
		$responseText = trim(stristr($responseText, "\r\n\r\n" ), "\r\n");
		return $responseText;
	}
	*/
}