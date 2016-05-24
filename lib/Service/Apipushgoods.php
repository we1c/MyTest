<?php

class Service_Apipushgoods extends Service{
	//[jianlou]
	//'url'=>'http://120.26.216.95:9090/api.ashx?cmd=%s&c=1&t=%s&sign=%s&f=json&v=%s&p=%s',
	//'http://121.199.50.107:9999/api.ashx?cmd=%s&c=1&t=%s&sign=%s&f=json&v=%s&p=%s'
	//'token'=>'tpi4a5xygceh83azwct29oquymw7nf16',
	//'userID'=>'20038148',
	private $_channel = array(
		'c11'=>array(
			'alias'=>'jianlou.com',
			'url'=>'http://121.199.50.107:9999/api.ashx?cmd=%s&c=1&t=%s&sign=%s&f=json&v=%s&p=%s',
			'token'=>'0de2f73542644a97b15807cf5fffa39f',
			'userID'=>'314405',
			'action'=>array(
				'push'=>'ReleaseNewGoods',
				'down'=>'OffShelves',
				'sendImg'=>'UploadAuctionGoodsPhoto',
				'getServerTime'=>'GetServerTime',
				'selledGoods'=>'GetMyGoodsListForSelled',
				),
			'cmd'=>array(
				'push'=>'C.G.3',
				'down'=>'C.G.16',
				'sendImg'=>'A.G.20',
				'getServerTime'=>'S.P.1',
				'selledGoods'=>'C.G.28',
				),
			'v'=>array(
				'push'=>'3',
				'down'=>'3',
				'sendImg'=>'1',
				'getServerTime'=>'1',
				'selledGoods'=>'3',
				),
			)
		);

	public function dispatch( $data ){
		//echo "<pre>";
		//解析json格式数据
		$data = json_decode( $data,true );
		$channel = $data['channel'];
		$action = $data['action'];
		//拼凑请求的函数名
		$call = $channel.$this->_channel[$channel]['action'][$action];
		//调用并返回结果
		return $this->$call( $data );
	}
	
	private function c11UploadAuctionGoodsPhoto( $data ){

		$channel = $data['channel'];
		$gid 	 = $data['gid'];
		$action  = $data['action'];
		//商品图片的本地路径数组
		$imgs	 = $data['param'];
		//制作签名
		$mapping = $this->_channel[$channel];
		$info['userID'] = $mapping['userID'];
		$param = array(
			'userID'=>$mapping['userID'],
			);

		$signCall = $channel.'Sign';
		$url = $this->$signCall( $action , $mapping ,$param );

		$fail = array();
		$success = array();
        foreach( $imgs as $k => $v ){
        	if( $k == 8 ) break;
        	//发送图片的文件流
        	$res = json_decode( $this->_sendStreamFile( $url , $v , $info ) , true );
        	if( $res['Code'] == 0 ){
        		//成功后，将返回的图片URL存入数组，等待入库
        		$success[] = $res['Data'];
        	}else{
        		//失败后将失败的原因和图片路径存入数组，等待输出错误
        		$fail['return'][] = $res;
        		$fail['img'][] = $v;
        	}
        }
        //输出错误信息
        if( !empty($fail) ) var_dump($fail);
        //保存图片URL
        if( !empty($success) ){
        	$sql = 'INSERT INTO goods_image_push ( goodsId,imgUrl,channel ) VALUES ';
        	$channel = substr( $channel , 1 );
        	foreach( $success as $v ){
        		$sql .= "( '{$gid}','{$v}','{$channel}' ),";
        	}
        	$sql = rtrim( $sql,',' );
        	if( $this->db->query( $sql ) ) return true;
        }
        return false;
	}

	private function _sendStreamFile( $url, $file ,$info = '' ){
		if(file_exists($file)){
			$disk = '';
			if( strtoupper( substr( PHP_OS , 0 , 3 ) ) == 'WIN' ){
				$disk = substr( __File__ , 0 , 2 );
			}
			$file = $disk.$file;
			$name = ltrim( strrchr( $file , '/' ) , '/' );
			//创建上传文件的对象$_FILES
			$curlFile = new CURLFile( $file ,'image/jpeg',$name );
			$data = array( 'photoImg'=> $curlFile );
			return $this->_request( $url,'post',$data );
		}else{
			return false;
		}
	}
    
	private function c11ReleaseNewGoods( $data ){

		$channel = 	$data['channel'];
		$gid 	 = 	$data['gid'];
		$action  = 	$data['action'];
		$param   = 	$data['param'];

		$photo = $param['goodsPhotoList'];

		if( count($photo) > 8 ){
			$limit = array();
			foreach( $photo as $k => $v ){
				if( $k == 8 ) break;
				$limit[] = $v;
			}
			$param['goodsPhotoList'] = $limit;
		}

		$mapping = $this->_channel[$channel];
		$userID = array('userID'=>$mapping['userID']);
		$param = array_merge( $userID,$param );
		
		$signCall = $channel.'Sign';
		$url = $this->$signCall( $action , $mapping , $param );

		$url = explode( '&p=',$url );
		$url = $url[0];
		var_dump($param);
		$post['p'] = json_encode($param);
		$response = json_decode( $this->_file_get_contents_fopen( $url,$post ) ,true );

		/*echo "<hr>requestURL:".$url."<hr>";
		echo "POST：<br>";
		var_dump($post);
		echo "<hr>result:<br>";
		var_dump($response);
		echo "<hr>";*/
		if( $response['Code'] === 0 ){
			$cid = substr( $channel,1 );
			$cGoodsId = $response['Data'];
			$update = array( 'cGoodsId'=>$cGoodsId );
			$res = $this->db->update( 'push',$update,'channel = '.$cid.' AND goodsId = '.$gid );
			if( $res ) return true;
		}else{
			$response['content'] = $data;
			$context = '['.date( 'Y-m-d H:i:s' ).'][response]['.$response['Msg'].'][start]'.json_encode( $response ).'[url=]'.$url.'[end]'.PHP_EOL;
			file_put_contents( ROOT_PATH.'/logs/push_goods.error.log', $context , FILE_APPEND );
			return false;
		}
	}

	private function c11GetMyGoodsListForSelled( $data ){
		$channel = $data['channel'];
		$action  = $data['action'];
		$param   = $data['param'];

		$mapping = $this->_channel[$channel];

		$param['userID'] = $mapping['userID'];

		$signCall = $channel.'Sign';
		$url = $this->$signCall( $action,$mapping,$param );

		return $this->_request( $url,'get' );

	}

	private function c11OffShelves( $data ){
		$channel = $data['channel'];
		$action  = $data['action'];
		$param   = $data['param'];

		$mapping = $this->_channel[$channel];

		$param['userID'] = $mapping['userID'];

		$signCall = $channel.'Sign';
		$url = $this->$signCall( $action , $mapping , $param );

		$response = $this->_request( $url ,'get' );
		return $response;
	}

	private function c11GetServerTime( $data ){
		$channel = $data['channel'];
		$action  = $data['action'];
		$param   = $data['param'];

		$mapping = $this->_channel[$channel];

		$signCall = $channel.'Sign';
		$url = $this->$signCall( $action , $mapping , $param );

		$url = explode( '&p=',$url );
		$url = $url[0];
		$response = json_decode( $this->_request( $url,'get' ) ,true );

		/*echo "<hr>requestURL:".$url."<hr>";
		echo "POST：<br>";
		echo "<hr>result:<br>";
		var_dump($response);
		echo "<hr>";*/
		return $response;
	}
	//返回带有所有参数的URL（包括SIGN和P 参数）
	private function c11Sign( $action , $mapping , $param = '' ){
		//收集信息
		$url = $mapping['url'];
		$token = $mapping['token'];
		$cmd = $mapping['cmd'][$action];
		$t = time();
		$version = $mapping['v'][$action];
		$p = '';

		if( !empty( $param ) ) $p = json_encode( $param );

		$tmp = sprintf( $url ,$cmd,$t,'',$version, $p );

		$str = substr( $tmp,strpos($tmp,'?')+1 );
		//echo $str;
		$get = explode('&',$str);
		//var_dump($get)."<hr>";
		$record = array();
		foreach( $get as $k => $v ){
			$kv = explode( '=' , $v );
			$record[$kv[0]] = $kv[1];
		}

		ksort( $record );
		//echo "按参数名排序后的所有参数:<br><pre>";
		//var_dump($param);
		//var_dump($record);

		$check = '';
		foreach( $record as $k => $v ){
			if( $k != 'sign' ) $check .= $v;
		}
		$check .= $token;
		//var_dump($check)."<hr>";
		$sign =	strtoupper( md5( $check ) );

		return sprintf( $url ,$cmd,$t,$sign,$version,$p );
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

	private function _request($url,$type='post',$data='', $ssl=true) {
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

}