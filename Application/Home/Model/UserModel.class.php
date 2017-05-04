<?php
	namespace Home\Model;
	use Think\Model;
	class UserModel extends Model {
		//注册步骤1
		public function reg_step_one() {
			$email = I('post.email', '');  

			//1、检测邮箱（空、正则、是否注册）
			// if($code = $this->_check_email($email)) return $code;

			//2、发送邮件
			$rst = $this->send_activate_email($email); 
 			$code = $rst['code'];

			return $code;
		}

		//注册
		public function reg() {
			$code = 0;

			//1、获取注册参数          
			$nickname = I('post.nickname', '');
			$password = I('post.password', '');
			$email = I('post.email', '');

			//2、验证注册参数
			if($code = $this->_check_email($email)) return $code;
			if($code = $this->_check_nickname($nickname)) return $code;
			if($code = $this->_check_password($password)) return $code;

			//3、判断邮箱是否已经通过激活
			if(!M('email_verify')->where(array('email'=>$email, 'status'=>1))->find()) return -102;
			
			//5、用户入库
			$data = array(
				'nickname'       =>  $nickname, 
				'password'       =>  encrypt_password($password),
				'email'          =>  $email,
				'email_status'   =>  0, 
				'reg_time'       =>  time(), 
				'reg_ip'         =>  get_client_ip() 
				);

			if(!$this->add($data)) $code = -5;

 			return $code;
		}

		//发送激活邮件
		public function send_activate_email($email) {
			$user = $this->where(array('email'=>$email))->find();
			//1、邮件已激活
			if($user) return array('code'=>-210);

			//2、生成token
			$verModel = M('email_verify');
			$exptime = time() + 24*3600;
			$data = array(
				'createtime'   =>  time(),
				'token'        =>  encrypt_email($email, $exptime),
				'expiretime'   =>  $exptime
				);


			$ver = $verModel->where(array('email'=>$email))->find();

			if(!$ver) {
				$data['email'] = $email;

				$verModel->add($data);
			}else{
				//发邮件时间间隔1分钟
				if($ver['createtime'] + 60 > time()) return array('code'=>-213);

				$data['id'] = $ver['id'];
				$verModel->save($data);
			}

			//3、发送激活邮件
			$title = "consense网站激活邮件";
			$cnt = "<p>您好！</p><p>恭喜您在cosense注册成功！</p><p><a href='".U('User/activate_account', array('token'=>$data['token'], 'e'=>$email), 'html', true, true)."'>请点击链接验证邮箱，有效期为24小时</a></p>";

 			return send_email($email, $title, $cnt);
		}

		//发送邮件---找回密码
		public function send_reward_email($email) {
			$user = $this->where(array('email'=>$email))->find();
			//1、邮件未激活
			if(!$user) return array('code'=>-102);

			//2、生成token
			$verModel = M('email_verify');
			$exptime = time() + 24*3600;
			$data = array(
				'createtime'   =>  time(),
				'token'        =>  encrypt_email($email, $exptime),
				'expiretime'   =>  $exptime
				);


			$ver = $verModel->where(array('email'=>$email))->find();

			if(!$ver) {
				$data['email'] = $email;

				$verModel->add($data);
			}else{
				//发邮件时间间隔1分钟
				if($ver['createtime'] + 60 > time()) return array('code'=>-213);

				$data['id'] = $ver['id'];
				$verModel->save($data);
			}

			//3、发送找回密码邮件
			$title = "consense网站找回密码邮件";
			$cnt = "<p>您好！</p><p>您正在通过邮箱找回cosense登录密码！</p><p><a href='".U('User/verify_reward_email', array('token'=>$data['token'], 'e'=>$email), 'html', true, true)."'>请点击链接进行密码修改，有效期为24小时</a></p>";

 			return send_email($email, $title, $cnt);
		}

		//发送邮件---修改邮箱
		public function send_edit_email($email) {
			$user = $this->where(array('email'=>$email))->find();
			//1、邮件未激活
			if(!$user) return array('code'=>-102);

			//2、生成token
			$verModel = M('email_verify');
			$exptime = time() + 24*3600;
			$data = array(
				'createtime'   =>  time(),
				'token'        =>  encrypt_email($email, $exptime),
				'expiretime'   =>  $exptime
				);


			$ver = $verModel->where(array('email'=>$email))->find();

			if(!$ver) {
				$data['email'] = $email;

				$verModel->add($data);
			}else{
				//发邮件时间间隔1分钟
				if($ver['createtime'] + 60 > time()) return array('code'=>-213);

				$data['id'] = $ver['id'];
				$verModel->save($data);
			}

			//3、发送找回密码邮件
			$title = "consense网站修改邮箱邮件";
			$cnt = "<p>您好！</p><p>您正在通过邮箱修改cosense登录邮箱！</p><p><a href='".U('User/verify_reward_email', array('token'=>$data['token'], 'e'=>$email), 'html', true, true)."'>请点击链接进行密码修改，有效期为24小时</a></p>";

 			return send_email($email, $title, $cnt);
		}

		
		
		//登录
		public function login($email, $password) {
			$code = 0;

			//1、获取用户信息，并验证
			if(!$email || !$password) return -103;                                //帐号或密码为空
			$user = $this->where(array('email'=>$email))->find();
			if(!$user)  return -101;                                             //帐号密码或不正确
			
			if($user['password'] != encrypt_password($password)) return -101;    //帐号密码或不正确
			if($user['locked']) return -104;                                     //帐号已被锁定
			
			//2、更新用户登录信息
			$this->save(array('id'=>$user['id'], 'last_login_time'=>time(), 'last_login_ip'=>get_client_ip()));

			//3、用户信息入session
			session('user', array('uid'=>$user['id'], 'nickname'=>$user['nickname'], 'email'=>$user['email'], 'avatar'=>$user['avatar'], 'user_type'=>$user['type']));

			return $code;
		}

		//验证邮箱
		private function _check_email($email) {
			// 1、为空验证
			if($email == '')  return -1;

			//2、正则验证
			// $pattern = "/\w+[@]{1}\w+[.]\w+/";
			// if(!preg_match( $pattern, $email)) return -2;
			
			//3、邮箱是否注册过
			if($this->where(array('email'=>$email))->find()) return -3;

			return 0;
		}

		//验证昵称
		private function _check_nickname($nickname) {
			//1、为空验证
			if($nickname == '') return -11;
				
			//2、正则验证
			// $pattern = "/^([\x80-\xff]|[a-zA-Z]){2,10}$/g";
			// if(!preg_match( $pattern, $nickname)) return -12;

			//3、昵称是否注册过验证
			if($this->where(array('nickname'=>$nickname))->find()) return -13;

			return 0;
		}

		//验证密码
		private function _check_password($password) {
			if($password == '') return -20;

			// $pattern = "/^([\w\W]){6,20}$/g";
			// if(!preg_match( $pattern, $password)) return -21;

			return 0;
		}
		
		
		
	}
?>