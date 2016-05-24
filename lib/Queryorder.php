<?php
class Queryorder{
	
	private $Appkey;
	
	//适用于汇通、天天、德邦、宅急送等
	private $url = 'http://api.kuaidi100.com/api?';
	
	//适用于EMS、顺丰、申通、圆通、韵达、中通等
	private $htmlUrl = 'http://www.kuaidi100.com/applyurl?';
	
	public function __construct($Appkey)
	{
		$this->Appkey = $Appkey;
	}
	
	public function get_nu($com,$nu,$type='html')
	{
		if($type == 'html')
		{
			if($com = $this->get_com($com))
			{
				$arr = array(
					'key' => $this->Appkey,
					'com' => $com,
					'nu'  => $nu,
					'show'=>0,
				);
				
				$url = $this->htmlUrl. http_build_query($arr);
			}else{
				return '快递公司名输入错误！';
			}
		}else{
			
			if($com = $this->get_com($com))
			{
				$arr = array(
					'id' => $this->Appkey,
					'com' => $com,
					'nu'  => $nu,
					'show'=>0,
					'muti'=>1,
					'order'=>'desc'
				);
				$url = $this->url. http_build_query($arr);
			}else{
				return '快递公司名输入错误！';
			}
		}
		
		$result =  $this->curl($url);
		
		return $result;
	}
	
	private function curl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
		curl_setopt ($ch, CURLOPT_TIMEOUT,5);
		$get_content = curl_exec($ch);
		curl_close ($ch);
		return $get_content;
	}
	
	private function get_com($com)
	{
		$arr = array(
			'顺丰快递'  => 'shunfeng',
			'申通快递'  => 'shentong',
			'EMS'      =>  'ems',
			'ems国际件' =>  'emsguoji',
			'中国邮政'  =>  'youzhengguonei',
			'圆通快递'  =>  'yuantong',
			'德邦'		=>	'debangwuliu',
			'韵达快递'	=>	'yunda',
			'百世汇通'	=>	'huitongkuaidi',
			'中通快递'	=>	'zhongtong',
		);
		
		if(isset($arr[$com]))
		{
			return $arr[$com];
		}else{
			return FALSE;
		}
	}
}
?>
