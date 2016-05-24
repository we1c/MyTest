<?php
class UpdateController extends BaseController{

	public function init(){
		parent::init();
	}
	//版本号对比
	public function checkAction(){
		$app_type = $this->getPost('type');
		if ( !$app_type ) $this->respon(0,"参数错误");
		$info=Service::getInstance('update')->getVersion( $app_type );
		if(file_exists($info['dir'])){
			unset($info['dir']);
			ob_clean();
			$this->respon(1,$info);
		}else{
			$this->respon(0,'文件不存在');
		}
	}

}