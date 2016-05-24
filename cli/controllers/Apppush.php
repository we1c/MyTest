<?php

class ApppushController extends BaseController{

	public function pushmsgAction(){
		while(1){
			$data = $this->redis->rpop( 'app_push' );
			if( !empty($data) ){
				$data = json_decode( $data,true );

				$client = new AppPush();
				switch ($data['sendType']) {
					//广播
					case '1':
						$result = $client->simplePush( $data['massage'] );
						break;
					//根据tag推送到Android
					case '2':
						$result = $client->pushForTag2Android($data['tag'], $data['massage'],$data['extras']);
						break;
					//根据alias推送到Android
					case '3':
						$result = $client->pushForAlias2Android($data['alias'], $data['massage'],$data['extras']);
						break;
				}
				if( !$result ){
					$data = json_encode($data);
					$content = '[start][data]['.date('Y-m-d H:i:s').']'.$data.'[end]'.PHP_EOL;
					file_put_contents(ROOT_PATH.'/logs/app_push.error.log', $content , FILE_APPEND );
				}
			}else{
				sleep(1);
			}
		}
	}

}