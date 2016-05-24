<?php

class Service_User extends Service
{
	private $error;
	public function getError(){
		return $this->error;
	}
	public function doLogin( $account , $pwd )
	{
        $sql = "SELECT * FROM users WHERE `account` = '".$account."'" ;

        $user = $this->db->fetchRow( $sql );

        if ( !$user ){
        	return '用户不存在' ;
        }
        Yaf_Registry::set('id', $user['id'] );
    
        if ( md5( $pwd ) != $user['pwd'] ){
            return '密码不正确';
        }
        if($user['status'] != '0') {
            return '该账号已被禁用，请联系管理员!';
        }
//         if($user['role'] != '1') {
//             return '您没有权限登录';
//         }
        $shop = $this->db->fetchAll('select id from shopkeeper where uid ='.$user['id']);
        if(!$shop) {
            return '您还没有店铺，请联系管理员！';
        }

        if ( isset( $user['id'] ) ) $this->_setCookie( $user['id'] , $user['pwd'] , true  );
        //if ( $user['Avata'] ) $user['Avata'] = $this->getAvata( $user['Avata'] );
        $this->setUser( $user );

// 		$profile = array();

// 		$this->updateProfile( $profile );

		return true;
	}
	
	/**
	 * 验证短信是否有效
	 */
	public function SMS($phone, $code)
	{
	    $data = $this->db->fetchRow("select mss,phone from apimss where phone = {$phone} order by id desc limit 1");
	    if ($code) {
	        if ($code == $data['mss'] && $phone == $data['phone']) {
	            return true;
	        }
	    }
	    return false;
	}

    private function _setCookie($uid, $pwd , $keep = true)
    {
        $uid = $this->_packCookie($uid);

        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $pwd = $this->_packCookie( md5( $key . $pwd ) );

        $expired = $keep ? time() + 86400 * 30 : 0;
        setcookie('pwd', $pwd, $expired, '/');
        setcookie('uid', $uid, $expired, '/');
    }

    private function _packCookie($uid)
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        return Util::strcode($uid, $key, 'encode');
    }

	public function getAccount(  )
	{
        if ( ! (bool)Yaf_Registry::get("isLogin") )
        {
            return false;
        }
	}

	//通过电话获获取用户id
	public function getUidBymobile( $mobile ){
	    $uid = $this->db->fetchOne("select id from users where account = '".$mobile."'");
	    return $uid;
	}
	
	public function sendsmsCode( $mobile)
	{
	    if ( !Util::isValidMobile( $mobile ) ) return false;
	
	    $num = rand( 1000 , 9999 );
	
	    $res = array(
	        'phone' => $mobile,
	        'mss' => $num,
	        'updatetime' => time()
	    );
	
	    if ( ! $this->db->insert('apimss', $res) ) return false;
	    $smsjson = Util::sendsms( $mobile , "验证码:$num".'【绣花张】' );
	    $smsArr = json_decode($smsjson,true);
	    #TODO 短信接口未定义
	    if ( $smsArr['msg'] == "ok" )
	    {
	        return true;
	    }
	
	    return false;
	}
	
	public function getUserinfobyShopid($shopid) {
	    return $this->db->fetchRow('select * from users where shopId = '.$shopid);
	}
	
	//通过账号获得用户信息
	public function getUserByaccount( $account )
	{
   		$sql = "SELECT * FROM users WHERE account = '" . $account . "'";
        $user = $this->db->fetchRow( $sql );
        return $user;
	}

	//注册
	public function register( $user , $Devtoken = "" , $platform = "iOS" )
	{
		$pwd = $user['Password'];
		$user['Password'] = md5( $user['Password'] );
		$user['Devtoken'] = $Devtoken;
		$user['platform'] = $platform;

		if ( ! $this->db->insert('Users', $user) ) return false;

		//注册成功!
		$this->doLogin( $user['Email'] , $pwd );
		return true;
		
	}

	//修改密码
	public function updatePassword( $phone , $pwd )
	{
	    $uid = $this->getUidBymobile($phone);
		$profile['pwd'] = md5( $pwd );
		$res = $this->db->update('users', $profile, ' id =' . $uid );
		if($res) $this->_setCookie($uid,md5($pwd),true);
		return $res;
	}

	
	//更新用户头像
	public function updateAvatar( $Avata )
	{
        if ( ! (bool)Yaf_Registry::get("isLogin") )
        {
            return false;
        }

		$profile = array();
		if ( $Avata ) $profile['Avata'] = $Avata;
		return $this->updateProfile( $profile );
	}

	//获取用户头像
	public function getAvata( $hash )
	{
		$dir = Yaf_Application::app()->getConfig()->get('avatar')->get('dir');
	
		$url = Yaf_Application::app()->getConfig()->get('home')->get('url');
	    $avatar_url = $url . "/default-avatar.png";

	    if ( empty($hash) ) return $avatar_url;

	    if ( file_exists( Util::getDir( $dir , $hash ).$hash."_avatar.jpg" ) )
	    {
	    	$url = Yaf_Application::app()->getConfig()->get('avatar')->get('url');
	        $avatar_url = $url . Util::getPath( $hash ) . $hash . "_avatar.jpg";
	    }
	    
	    return $avatar_url;
	}


	//发送邮件
	public function sendemailCode( $email , $types = 'reg' )
	{
		if ( !Util::isValidEmail( $email ) ) return false;
		
		$num = rand( 1000 , 9999 );

		$res = array(
			'mail' => $email,
			'code' => $num,
			'types' => $types,
			'updateTime' => time()
		);

		if ( ! $this->db->insert('Mailcode', $res) ) return false;

		if ( Util::sendmail( $email , "HaoJind-验证码" , '您的验证码是:' . $num  ) == "ok" )
		{
			return true;
		}

		return false;
	}

	//验证邮件验证码
	public function checkmailCode( $email, $code , $types = 'reg' )
	{
		$where = " code = ".$code." and mail= '" . $email . "'";
		$sql = "select updateTime , types from Mailcode where " . $where . "  limit 0 ,1  ";

        $codeInfo = $this->db->fetchRow( $sql );
        if ( !$codeInfo ) return false ;

		if ( intval( time( ) - intval( $codeInfo['updateTime'] ) ) > 60 * 60  ) return false;

		if ( $codeInfo['types'] != $types ) return false;

		$this->db->delete( "Mailcode" , $where );
		return true;
	}

	//通过邮箱获取用户id
	public function getUidByemail( $email )
	{
		$uid = $this->db->fetchOne("select Id from Users where Email= '" . $email . "'"); 
		return $uid;
	}

	//通过邮箱查找用户
	public function getUserByemail( $email )
	{
	    $user = $this->db->fetchRow("select * from Users where Email= '" . $email . "'"); 
		return $user;		
	}

	//获得用户信息
	public function getUserById( $uid = 0 )
	{

        if ( ! (bool)Yaf_Registry::get("isLogin") )
        {
            return false;
        }

		if ( $uid == 0 ) $uid = Yaf_Registry::get( 'uid' );

		if ( (int)$uid == 0 ) return array();

		if ( $uid !=  Yaf_Registry::get( 'uid' ) )
		{
			$this->visitorGetUserById( $uid );
		}

        $sql = 'SELECT * FROM users WHERE id = ' . (int) $uid ;

        $user = $this->db->fetchRow( $sql );

        if ( isset( $user['id'] ) ) 
        {
        	if ( $user['avata'] ) $user['avata'] = $this->getAvata( $user['avata'] );
        }

        return $user;
	}

	
    //更新用户信息
    public function updateProfile( $profile = array()  )
    {

        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $uid = Yaf_Registry::get('uid');

    	$fields = array( 'name', 'tel' , 'pwd'  );

		foreach ( $profile as $field ) 
		{
			if ( !in_array( $field , $fields ) ) unset( $profile[$field] );
		}

		if ( isset( $profile['pwd'] ) ) $profile['pwd'] = md5( $profile['pwd'] );
		
		return $this->db->update('users', $profile, ' id =' . $uid );
    }


    //验证用户信息
    public function checkUser()
    {
        $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
        $uid = Util::strcode($_COOKIE['uid'], $key, 'decode');
//         var_dump($uid);
//         exit;
//         $uid = $_COOKIE['uid'];
        if ( !intval( $uid ) ) return false;
		$pwd = Util::strcode($_COOKIE['pwd'], $key, 'decode');
		    
		$account = $this->db->fetchRow("SELECT * FROM users WHERE id = ?", intval($uid));
		if ( !$account ) {
			return false;
		}
		
		$shop = $this->db->fetchAll('select id from shopkeeper where uid ='.$account['id']);
		if( !$shop ) {
		    return false;
		}

		$key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');

		if ( $pwd !== md5( $key  . $account['pwd']) ) {
			return false;
		}

		unset( $account['pwd'] );

        $this->setUser( $account );

        return true;
    }

    //设置注册信息
    private function setUser( $user )
    {
        
        $u['id'] = $user['id'];
        $u['name'] = $user['name'];
      
		Yaf_Registry::set('uid', $user['id'] );
		Yaf_Registry::set('user', $user );
		Yaf_Registry::set('PublicUser', $u );
    }

    //添加用户
    public function add( $user  )
    {
        $user['pwd'] = md5($user['pwd']);
        if( $this->db->insert('users',$user) ){
            return $this->db->lastInsertId();
        }
        $this->error = '添加失败';
        return false;
    }
    
    //通过ID获得用户信息
    public function getUserInfoById($id) {
        $sql = "SELECT id,account,tel,name,role,sex,avata,weixin,info,province,city,area,address,shopId FROM users WHERE id=".$id;
        $user = $this->db->fetchRow($sql);
        if ( isset( $user['id'] ) )
        {
            $user['avata'] = $this->getAvata( $user['avata'] );
        }
        return $user;
    }
    
    public function getUserIdNameByAccount($account) {
        return $this->db->fetchRow('select id,name from users where account='.$account);
    }
    
    //编辑用户
    public function edit($info,$uid) {
        return $this->db->update('users', $info,' id='.$uid);
    }
    
    //删除用户
	public function del($id){
		return $this->db->update('users',array('status'=>1),' id='.$id);
	}
	
	public function updateHead($hash,$uid) {
	    return $this->db->update('users', array('avata'=>$hash),' id='.$uid);
	}
	
	public function reg( $userinfo,$code='')
	{
	    if ( !isset( $userinfo['openid'] ) ) return;
	
	    $user = $this->getUserByopenid( $userinfo['openid'] );
	
	    $fields = array( 'openid', 'nickname' , 'sex','language','city','province','country','headimgurl' );
	
	    unset( $userinfo['privilege'] );
	    unset( $userinfo['sex'] );
	    unset( $userinfo['language'] );
	    unset( $userinfo['city'] );
	    unset( $userinfo['province'] );
	    unset( $userinfo['country'] );
	
	    if ( $user )
	    {
	        $this->db->update( 'customer', $userinfo , " id = ".$user['id']  );
	        
	        $this->_setOneCookie( $userinfo['openid'] );
	
	        return $userinfo;
	    }
	    $userinfo['name'] = $userinfo['nickname'];

	    foreach ( $userinfo as $k => $v ) 
	    {
	    	if ( $v == "" ) unset( $userinfo[$k] );
	    }
	
	    $this->db->insert( "customer" , $userinfo );
	    $this->_setOneCookie( $userinfo['openid'] );
	
	    return $userinfo;
	}
	
	public function getUserByopenid( $openid )
	{
	    if ( !$openid ) return false;
	    return $this->db->fetchRow('SELECT * FROM customer WHERE openid = "'.$openid.'"');
	}
	public function updateUserByOpenid( $data, $openid )
	{
	    if ( !$openid ) return false;
	    return $this->db->update( 'customer' , $data, " openid = '{$openid}'");
	}
	
	protected function _setOneCookie($openId, $keep = true)
	{
	    $key = Yaf_Application::app()->getConfig()->get('cookie')->get('key');
	    $token = Util::strcode($openId, $key, 'encode');
	    $expired = $keep ? time() + 86400 * 30 : 0;
	
	    setcookie('openId', $token, $expired, '/');
	}
}