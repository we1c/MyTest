<?php

class DeveloperController extends BaseController
{
    private $_appId;
    private $_appSecret;

    public function init()
    {
        parent::init();

        $weiConfig = Yaf_Application::app()->getConfig()->get('wei');
        $this->_appId = $weiConfig -> get('appid');
        $this->_appSecret = $weiConfig -> get('secret');

    }

    public function indexAction()
    {
        
        if(!$this->_developer) {
            $this->flash('/developer/signin', '请登录');
        }
        $this->_view->developer = $this->_developer;
        
        $rolelist = Service::getInstance('role')->roleall();
        $roleArr = array();
        foreach ($rolelist as $k=>$v){
            $roleArr[$v['id']] = $v['name'];
        }
        $this->_view->rolelist = $roleArr;
    }

    public function editAction(){
        if ($this->isPost()) {
            $id         = $this->_developer['id'];
            $name       = $this->getPost('name');
            $sex        = $this->getPost('sex');
            $province   = $this->getPost('province');
            $city       = $this->getPost('city');
            $area       = $this->getPost('area');
            $address    = $this->getPost('address');
            $account    = $this->getPost('account');
            $tel        = $this->getPost('tel');
            $birthday   = $this->getPost('birthday');
            $data = array(
                    "name"=>$name,
                    "sex"=>$sex,
                    "province"=>$province,
                    "city"=>$city,
                    "area"=>$area,
                    "address"=>$address,
                    "account"=>$account,
                    "tel"=>$tel,
                    "birthday"=>$birthday,
                );
            $result = Service::getInstance('Developers')->updateDev( $id,$data);
            if ($result) {
                $this->flash('/developer/edit', '修改成功');
            }else{
                $this->flash('/developer/edit', '修改失败');
            }
        }else{
            $per = 0;
            if ($this->_developer['account']) {
                $per++;
            }
            if ($this->_developer['name']) {
                $per++;
            }
            if ($this->_developer['openId']) {
                $per++;
            }
            if ($this->_developer['sex']) {
                $per++;
            }
            if ($this->_developer['province']) {
                $per++;
            }
            if ($this->_developer['city']) {
                $per++;
            }
            if ($this->_developer['area']) {
                $per++;
            }
            if ($this->_developer['address']) {
                $per++;
            }
            if ($this->_developer['tel']) {
                $per++;
            }
            if ($this->_developer['birthday']) {
                $per++;
            }
            $province = Service::getInstance('shop')->getProvince();
            $city = Service::getInstance('shop')->getCity($this->_developer['province']);
            $area = Service::getInstance('shop')->getCity($this->_developer['city']);
            $this->_view->province = $province;
            $this->_view->city = $city;
            $this->_view->area = $area;
            $this->_view->per = round(($per/10)*100).'%';
        }
    }

    public function signinAction()
    {
        if ($this->isPost()) {
            $client = trim( $this->getPost('client','') );
            $account = trim( $this->getPost('email', '') );
            $password = trim( $this->getPost('password', '') );
            $isStore=intval($this->getPost('isStore',''));
            if ($account === '' || $password === '') {
                $this->error('请输入手机号和密码');
                return;
            }
            $developer = Service::getInstance('admin')->getInfoByAccount( $account );
            if (!$developer) {
                $this->error('电子邮件地址未注册');
                return;
            }
            $hash = md5($password);
            if ($hash !== $developer['pwd']) {
                $this->error('密码错误');
                return;
            }
            
            if($isStore){
                setcookie("email",$account,time()+3600*24*30,'/');
            }else{
                setcookie("email",'',time(),'/');
            }
            
            $this->_setCookie($developer, true);
            //将上次登录时间记录在session里面
            session_start();
            $_SESSION['lastTime'] = $developer['loginTime'];

            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            //$login['loginIp'] = ip2long($_SERVER['REMOTE_ADDR']);
            $login['loginTime'] = time();

            $res = Service::getInstance('developers')->updateDev( $developer['id'] , $login );

            if( empty($develper['openId']) && $client == 'micro' ){
                Util::weichatlogin( 'http://adm.1ge.com/dev/index?devid='.$developer['id'].'-from=adm_getopenid' , $this->_appId );
                return false;
            }
            
            $this->flash('/dev/index', '登录成功',0);

            return false;
        }

        $this->_view->email= isset($_COOKIE['email']) ? $_COOKIE['email']: '';
    }

    /*public function getweiopenidAction ( ){
        $code = $this->getQuery( 'code' );
        $state = $this->getQuery( 'state' );
        if( !empty( $code ) ) {
            $token = json_decode( Util::getWeiChatToken( $code ,$this->_appId,$this->_appSecret ),true );
            if( empty($token['errcode']) ){
                $openId = $token['openid'];
                preg_match('/\bdevid=(\d+)\b/',$state,$matchs);
                $id = $matchs[1];
                $data = array( 'openId'=> $openId );
                $res = Service::getInstance('developers')->updateDev( $id,$data );
            }
        }
        header('Location:'.$state);
        return false;
    }*/

//     public function signinAction()
//     {
//         if ($this->isPost()) {
//             $email = $this->getPost('email', '');
//             $password = $this->getPost('password', '');
//             if ($email === '' || $password === '') {
//                 $this->error('请输入账号和密码');
//                 return;
//             }
//             if (!Util::isValidMobile($email)) {
//                 $this->error('请输入正确手机号码');
//                 return;
//             }
    
//             $developer = Service::getInstance('user')->getUserByaccount($email);
//             if (!$developer) {
//                 $this->error('该账号未注册');
//                 return;
//             }
    
//             $hash = md5($password);
//             if ($hash !== $developer['pwd']) {
//                 $this->error('密码错误');
//                 return;
//             }
//             if($developer['role'] != '2') {
//                 $this->error('没有权限登录');
//                 return;
//             }
//             $this->_setCookie($developer, true);
    
//             $this->flash('/dev', '登录成功', 0);
    
//             return false;
//         }
//     }

    // 账户设置修改密码
    public function updatepwdAction()
    {
        if (!$this->isPost()) {
            return;
        }

        $current = $this->getPost('current', '');
        $password = $this->getPost('password', '');
        $confirm = $this->getPost('confirm', '');
        if (md5($current) !== $this->_developer['pwd']) {
            echo"<script>alert('当前密码不正确');window.location.href=document.referrer</script>";
            exit;
        }

        if ($password === '' || $password != $confirm) {
            echo"<script>alert('密码过短或两次输入不匹配');window.location.href=document.referrer</script>";
            exit;
        }
        $pwd = md5($password);
        Service::getInstance('developers')->updatePassword($this->_devid, $pwd);

        $this->flash('/developer', '密码修改成功');

        return false;
    }

    // 重置密码
    public function passwordAction()
    {
        $code = $this->getQuery('code', '');
        if (!$code || !($developer = Service::getInstance('developers')->getDeveloperByCode($code))) {
            $this->fatal('链接不合法');
            return false;
        }

        if ($this->isPost()) {
            $password = $this->getPost('password', '');
            $confirm = $this->getPost('confirm', '');

            if ($password === '' || $password != $confirm) {
                $this->error('密码过短或两次输入不匹配');
                return;
            }

            $pwd = md5($password . $developer['salt']);
            Service::getInstance('developers')->updatePassword($developer['id'], $pwd);

            $message = $developer['pwd'] == '' ? '注册完成' : '密码重置成功';
            $message .= '，正在转向';
            $this->flash('/dev', $message);

            return false;
        }
    }


    //后台管理列表
    public function listAction()
    {
        $perpage = 15;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
        $data = Service::getInstance('Developers')->developerlist($page,$perpage,$keyword);
        $this->_view->list = $data['list'];
        $url = $keyword ? '/Developer/list?page=__page__&keyword='.$keyword : '/order/list?page=__page__';
        $this->_view->pagebar = Util::buildPagebar( $data['total'], $perpage, $page, $url );
    }
    
    public function addAction(){
        if($this->isPost()){
            $email = $this->getPost('email','');
            $name = $this->getPost('name','');
            if($email === '' && $name === ''){
                $this->error('请输入电子邮箱地址和姓名');
                return;
            }
            if(Service::getInstance('developers')->getDeveloperByEmail($email)){
                $this->error('账号已经被注册');
                return ;
            }
            $Info = Service::getInstance('developers')->getRegisterInfoByEmail($email, $name);
            $password = '123456';
            $pwd = md5($password . $Info['salt']);
            Service::getInstance('developers')->updatePassword($Info['lastId'], $pwd);
            $this->flash('/developer/list','添加用户成功');
        }
    }
    
    public function signoutAction()
    {
        setcookie('token', false, 0, '/');
        $this->flash('/', '已退出', 0);
        return false;
    }


    protected function _setCookie($developer, $keep = true)
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $token = sprintf('%s|%s', $developer['account'], $developer['pwd']);
        $token = Util::strcode($token, $key, 'encode');
        $expired = $keep ? time() + 86400 * 30 : 0;

        setcookie('token', $token, $expired, '/');
    }
}