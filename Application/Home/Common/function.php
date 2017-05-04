<?php
	/**
	 * 发送邮件
	 * @param  [type] $address [地址]
	 * @param  [type] $subject [标题]
	 * @param  [type] $content [内容]
	 */
	function send_email($address, $subject, $content){
	    $email_smtp = C('STMP_SERVER');
	    $email_username = C('EMAIL_USERNAME');
	    $email_password = C('EMAIL_PASSWORD');
	    $email_from_name = C('EMAIL_FROM_NAME');

	    if(empty($email_smtp) || empty($email_username) || empty($email_password) || empty($email_from_name)){
	        return array("code"=>-51,"msg"=>'邮箱配置不完整');
	    }

	    require './ThinkPHP/Library/Org/Nx/class.phpmailer.php';
	    require './ThinkPHP/Library/Org/Nx/class.smtp.php';

	    $phpmailer=new \Phpmailer();
	    // 设置PHPMailer使用SMTP服务器发送Email
	    $phpmailer->IsSMTP();
	    // 设置为html格式
	    $phpmailer->IsHTML(true);
	    // 设置邮件的字符编码'
	    $phpmailer->CharSet='UTF-8';
	    // 设置SMTP服务器。
	    $phpmailer->Host=$email_smtp;
	    // 设置为"需要验证"
	    $phpmailer->SMTPAuth=true;
	    // 设置用户名
	    $phpmailer->Username=$email_username;
	    // 设置密码
	    $phpmailer->Password=$email_password;
	    // 设置邮件头的From字段。
	    $phpmailer->From=$email_username;
	    // 设置发件人名字
	    $phpmailer->FromName=$email_from_name;
	    // 添加收件人地址，可以多次使用来添加多个收件人
	    if(is_array($address)){
	        foreach($address as $addressv){
	            $phpmailer->AddAddress($addressv);
	        }
	    }else{
	        $phpmailer->AddAddress($address);
	    }
	    // 设置邮件标题
	    $phpmailer->Subject=$subject;
	    // 设置邮件正文
	    $phpmailer->Body=$content;
	    // 发送邮件。
	    if(!$phpmailer->Send()) {
	        $phpmailererror=$phpmailer->ErrorInfo;
	        return array("code"=>-52,"msg"=>$phpmailererror);
	    }else{
	        return array("code"=>0);
	    }
	}

	/**
	 * 密码 加密
	 * @param  [type] $pwd [description]
	 * @return [type]      [description]
	 */
	function encrypt_password($pwd) {
		return md5(md5($pwd.C('ENCRYPT_PASSWORD')));
	}



	/**
	 * 加密邮箱验证token
	 * @return [type] [description]
	 */
	function encrypt_email($email, $reg_time) {
		return md5($email.$reg_time.C('ENCRYPT_EMAIL'));
	}

	/**
	 * [resultReturn description]
	 * @return [type] [description]
	 */
	function jsonReturn($code, $data=array(), $msg='') {
		$error = array(
			0      =>     '成功',
			-1     =>     '邮箱为空!',
			-2     =>     '邮箱格式不合法!',
			-3     =>     '邮箱已注册!',
			-4     =>     '用户信息入库失败!',
			-5     =>     '系统错误!',
			-6     =>     '验证码错误!',
			-7     =>     '请先登录!',
			-8     =>     '请勿重复点赞!',
			-11    =>     '昵称为空!',
			-12    =>     '昵称不合法!',
			-13    =>     '昵称已注册!',
			-20    =>     '密码为空!',
			-21    =>     '密码不合法!',
			-51    =>     '邮箱配置不完整',
			-52    =>     '邮件发送失败，请与管理员联系!',
			-101   =>     '帐号或密码不正确!',
			-102   =>     '邮箱未激活!',
			-103   =>     '帐号或密码为空!',
			-104   =>     '帐号已被锁定',
			-210   =>     '邮箱已激活!',
			-211   =>     '邮箱激活码已过期!',
			-212   =>     '邮箱激活失败!',
			-213   =>     '两次发邮件的时间间隔必须大于1分钟',
			-215   =>     '该邮箱还未注册',
			);

		if(!$msg ) $msg = $error[$code];
		die(json_encode(array('code'=>$code, 'msg'=>$msg, 'data'=>$data)));
	}

	/**
	 * 登录状态
	 * @return [type] [description]
	 */
	function login_status() {
		// session('user', array('uid'=>85, 'email'=>'2644301914@qq.com'));
		return session('user.uid') ? true : false;
	}

	function set_header($url='') {
		return $url ? $url : '/Public/Home/images/img/user_pho.jpg';
	}
?>