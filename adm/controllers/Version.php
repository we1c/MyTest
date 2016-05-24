<?php

class VersionController extends BaseController{
	public function init(){
		parent::init();
	}

	public function indexAction (){
		$perpage = $this->getQuery('perpage',15);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
		$data = Service::getInstance('version')->versionList($page,$perpage,$keyword);
		$list = $data['list'];
		$this->_view->list=$list;
        $this->_view->total = $data['total'];
        $this->_view->keyword = $keyword;
        $this->_view->perpage = $perpage;
        $url = Yaf_Application::app()->getConfig()->get('app')->url;
		$this->_view->url=$url;
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('version','index','keyword'=>$keyword,'perpage'=>$perpage));
        $this->_view->pagebar = $pageObj -> showPage();
	}

	public function addAction (){
		if($this->isPost()){
			$dir = Yaf_Application::app()->getConfig()->get('app')->dir;
			$apkName = "OnlyOne";
			$code = $this->getPost('code');
			$descript=htmlspecialchars($this->getPost('descript'));
			$is_must=$this->getPost('is_must');

			if ( !$code ) {
				$this->flash('/version/add','未填写版本号',1);
			}

			if ($_FILES["file"]["error"] > 0)
			{
				$this->flash('/version/add','上传错误，ErrorNo:'.$_FILES["file"]["error"],1);
			}
			else
			{
				$ext = substr(strrchr($_FILES["file"]["name"], '.'), 1);
				if ( $ext == 'apk' ) 
				{
					$app_type = 1;
				}
				elseif ( $ext == 'ipa' ) 
				{
					$app_type = 2;
				}
				$fileMd5 = md5_file($_FILES["file"]["tmp_name"]);

				if(!is_readable($dir)){
				    is_file($dir) or mkdir($dir,0777);  
				}
				$res = move_uploaded_file($_FILES["file"]["tmp_name"],$dir.$apkName.'.'.$ext);
			}
			if ( $res ) {
				$version = array(
							"code"=>$code,
							"descript"=>$descript,
							"createTime"=>date('Y-m-d H:i:s'),
							"file_md5"=>$fileMd5,
							"app_type"=>$app_type,
							"is_must"=>$is_must,
							);
				$insertId=Service::getInstance('version')->addVersion($version);
			}
				
			if($insertId){
				$this->flash('/version/index','添加成功',1);
			}else{
				$this->flash('/version/add','添加失败',1);
			}
		}
	}
            
	public function editAction (){
		if($this->isPost()){
			$dir = Yaf_Application::app()->getConfig()->get('app')->dir;
			$apkName = "OnlyOne";
			$id = intval($this->getPost('id'));
			$data['descript'] = htmlspecialchars($this->getPost('descript'));
			$data['code'] = $this->getPost('code');
			$is_must=$this->getPost('is_must');
			if ( !$data['code'] ) {
				$this->flash('/version/index','未填写版本号',1);
			}
			if ($_FILES) {
				if ($_FILES["file"]["error"] > 0)
				{
					$this->flash('/version/add','上传错误，ErrorNo:'.$_FILES["file"]["error"],1);
				}
				else
				{
					$ext = substr(strrchr($_FILES["file"]["name"], '.'), 1);
					if ( $ext == 'apk' ) 
					{
						$app_type = 1;
					}
					elseif ( $ext == 'ipa' ) 
					{
						$app_type = 2;
					}
					$fileMd5 = md5_file($_FILES["file"]["tmp_name"]);

					if(!is_readable($dir)){
					    is_file($dir) or mkdir($dir,0777);  
					}
					$res = move_uploaded_file($_FILES["file"]["tmp_name"],$dir.$apkName.'.'.$ext);
				}
			}
			$res = Service::getInstance('version')->edit($data,$id);
			if($res){
				$this->flash('/version/index','编辑成功');
			}else{
				$this->flash('/version/index','编辑失败');
			}
		}else{
			$url = Yaf_Application::app()->getConfig()->get('app')->url;
			$id = $this->getQuery('id');
			$Info = Service::getInstance('version')->getVersionInfoById($id);
			$this->_view->Info=$Info;
			$this->_view->url=$url;
		}
		

	}

	public function delAction (){
		$id=$this->getQuery('id',0);
		if($res=Service::getInstance('version')->delVersion($id)){
			$this->flash('/version/index','删除成功');
		}else{
			$this->flash('/version/index','删除失败');
		}

	}
}