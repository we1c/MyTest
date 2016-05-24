<?php

class UserController extends BaseController {
    public function init() {
        parent::init();	
    }
    
    public function indexAction() {
        if ( !Service::getInstance('user')->checkUser() ) {
            $this->respon( 0 , "请重新登录" );
        }
        $this->respon( 1 , Yaf_Registry::get("user") );
    }
    
    /**
     * 用户登录
     * @param post:account,password
     * @return boolean
     */
    public function loginAction() {
        if($this->isPost()) {
            $account = trim($this->getPost('account'));
            $password = trim($this->getPost('password'));
            if(!$account) {
                $this->respon(0,'请输入用户名');
            }
            if($password === '') {
                $this->respon(0,'请输入密码');
            }
            $res = Service::getInstance('user')->doLogin($account,$password);
            if($res === true) {
                $user = Service::getInstance('user')->getUserByaccount($account);
                $user['avata'] = Service::getInstance('user')->getAvata($user['avata']);
                if(!$user['shopId']) {
                    $user['shopId'] = Service::getInstance('goods')->getMeshopId($user['id']);
                }
                $shop = Service::getInstance('shop')->getShopinfo( $user['shopId'] );
                $user['shopName'] = $shop['name'];
                $this->_setCookie($user, true);
                unset($user['pwd']);
                unset($user['role']);
                $this->respon(1,$user);
            } else {
                $this->respon(0,$res);
            }
        }
        return false;
    }
    
    /**
     * 忘记密码
     * @param post:account,password,code
     * @return boolean
     */
    public function lostAction() {
        if($this->isPost()) {
            $phone = trim($this->getPost('account', ''));
            $pwd = trim($this->getPost('password', ''));
            $code = trim($this->getPost('code', ''));
            if(!Util::isValidMobile($phone)) {
                $this->respon(0,'请输入正确手机号码');
            }
            if(!$pwd) {
                $this->respon(0,'密码不能为空');
            }
            if(strlen($pwd) < 6 || strlen($pwd) > 16) {
                $this->respon(0,'密码长度在6到16位之间');
            }
            if(!$code) {
                $this->respon(0,'验证码不能为空');
            }
            if ( !Service::getInstance('user')->SMS( $phone , $code ) ) {
                $this->respon( 0 , "验证码不正确!" );
            }
            $res = Service::getInstance('user')->updatePassword ($phone,$pwd);
            if($res) {
                $this->respon(1,'密码设置成功');
            } else {
                $this->respon(0,'密码设置失败');
            }
        }
        return false;
    }
    
    /**
     * 发送验证码
     * @param post:account
     * @return boolean
     */
    public function verifyAction()
    {
        if($this->isPost()) {
            $phone = $this->getPost( 'account' );
            if ( !Util::isValidMobile( $phone ) ) $this->respon( 0 , "账号不合法!" );
            $user = Service::getInstance( 'user' )->getUserByaccount( $phone );
            if ( !$user )
            {
                $this->respon( 0 , "您的账号不存在，请重新填写!" );
            }
            if( $user['status'] ==="1" ){
                $this->respon( 0, '您的账号已被管理员封停' );
            }
            if ( Service::getInstance( 'user' )->sendsmsCode( $phone ) )
            {
                $this->respon( 1, "验证码已发送到您的手机，请查收!" );
            }
            $this->respon( 0, "验证码发送失败，请重试!" );
        }
        return false;
    }
    
    /**
     * 设置cookie
     * @param unknown $developer
     * @param string $keep
     */
    protected function _setCookie($developer, $keep = true) {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = sprintf('%s|%s', $developer['account'], $developer['pwd']);
        $token = Util::strcode($token, $key, 'encode');
        $expired = $keep ? time() + 86400 * 30 : 0;
        setcookie('token', $token, $expired, '/');
    }
    
    /**
     * 退出
     * @return boolean
     */
    public function logoutAction() {
        setcookie('token', false, 0, '/');
        setcookie('uid', false, 0, '/');
        $this->respon(1,'已退出');
        return false;
    }
   
}