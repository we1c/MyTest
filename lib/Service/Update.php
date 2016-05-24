<?php

class Service_Update extends Service{
	public function getVersion($app_type){
		$info=$this->db->fetchRow(" SELECT code AS versionCode,descript AS intro,file_md5 AS fileMd5,is_must AS isMust,app_type AS appType FROM app_version WHERE app_type = {$app_type} ORDER BY code DESC LIMIT 1 ");
		if ( !$info ) {
			$info['versionCode'] = 0;
			$info['intro'] = '';
		}
		$info['versionCode'] = (int)$info['versionCode'];
		$info['intro'] = htmlspecialchars_decode($info['intro']);
		$url = Yaf_Application::app()->getConfig()->get('app')->url;
		$dir = Yaf_Application::app()->getConfig()->get('app')->dir;
        if ( $info['appType'] == 1 ) {
            $fileName = "OnlyOne.apk";
        }elseif( $info['appType'] == 2 ){
            $fileName = "OnlyOne.ipa";
        }
        $info['url'] = $url.$fileName;
        $info['dir'] = $dir.$fileName;

		$size=file_exists($info['dir']) ? filesize($info['dir']) : '';
		$info['size']=$size;
		return $info;
	}


}