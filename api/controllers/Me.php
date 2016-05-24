<?php

class MeController extends BaseController {
    public function init() {
        parent::init();
        if ( ! (bool)Yaf_Registry::get("isLogin") ) {
            $this->respon( 0 , "请重新登录" );
        }
    }
    
    //我（显示个人信息）
    public function indexAction() {
        $uid = Yaf_Registry::get('uid');
        $user = Service::getInstance('user')->getUserInfoById($uid);
        if(!$user['shopId']) {
            $user['shopId'] = Service::getInstance('goods')->getMeshopId($user['id']);
        }
        $shop = Service::getInstance('shop')->getShopinfo( $user['shopId'] );
        $user['shopName'] = $shop['name'];
        if ( $user['province'] && $user['city'] && $user['area'] ) {
            $pname = Service::getInstance('shop')->getProCityAreaName($user['province']);
            $cname = Service::getInstance('shop')->getProCityAreaName($user['city']);
            $aname = Service::getInstance('shop')->getProCityAreaName($user['area']);
            $user['provincename'] = $pname['name'];
            $user['cityname'] = $cname['name'];
            $user['areaname'] = $aname['name'];
        } else {
            $user['provincename'] = '';
            $user['cityname'] = '';
            $user['areaname'] = '';
        }
        
        if($user) {
            $this->respon(1,$user);
        } else {
            $this->respon(0,'该账号异常');
        }
    }
    
    //编辑个人信息
    public function editAction() {
        $uid = Yaf_Registry::get('uid');
        $name = $this->getPost('name','');
        $sex = $this->getPost('sex','1');
        $weixin = $this->getPost('weixin','');
        $info = $this->getPost('info','');
        $province = $this->getPost('province',0);
        $city = $this->getPost('city',0);
        $area = $this->getPost('area',0);
        $address = $this->getPost('address','');
        $data = array(
            'name'=>$name,
            'sex'=>$sex,
            'weixin'=>$weixin,
            'info'=>$info,
            'province'=>$province,
            'city'=>$city,
            'area'=>$area,
            'address'=>$address
        );
        $res = Service::getInstance('user')->edit($data,$uid);
        if($res >= 0) {
            $this->respon(1,'保存成功');
        } else {
            $this->respon(0,'保存失败');
        }
    }
    
    //头像
    public function headimgAction() {
        $uid = Yaf_Registry::get( 'uid' );
        if(!$_FILES['file']['error']) {
            $avatar = $_FILES['file']['tmp_name'];
            $hash = md5($avatar);
            $dir = Yaf_Application::app()->getConfig()->get('avatar')->get('dir');
            if ( move_uploaded_file( $avatar, Util::getDir( $dir ,$hash ) . $hash . "_avatar.jpg") ) {
                $Res = array(
                    'avatar'  => Service::getInstance('user')->getAvata( $hash ),
                    'hash'=> $hash
                );
                Service::getInstance('user')->updateHead($hash,$uid);
                $this->respon(1,$Res);
            } else {
                $this->respon(0,'上传失败');
            }
    
        }
        $this->respon(0,'请选择您要上传的头像!');
    }
}
