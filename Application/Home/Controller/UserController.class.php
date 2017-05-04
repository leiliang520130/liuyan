<?php
namespace Home\Controller;
use Think\Controller;
use Common\Api\LibWeiBo;

	Vendor('qqConnectAPI.QC');
	class UserController extends Controller {
		//注册步骤1
		public function reg() {
			//1、判断邮箱是否已经注册
			$email = I('email', '');
			$code = $this->_check_email($email);
			if($code != 0) jsonReturn($code); 

			$ver = new \Common\Api\VerifyApi();
			$uModel = D('User');

			//2、验证验证码
			if($ver->CheckVerCode()) {
				//>> 发送邮件注册
				$code = $uModel->reg_step_one();
			}else{
				$code = -6;
			}

			//3、返回数据
			$rst = array();
			if($code == 0) {
				$email = I('email', '');
				$email_arr = explode('@', $email);
				$rst = array('email'=>I('email'), 'url'=>'http://mail.'.$email_arr[1]);
			}

			jsonReturn($code,$rst); 
		}

		//验证邮箱
		private function _check_email($email) {
			// 1、为空验证
			if($email == '')  return -1;

			//2、正则验证
			// $pattern = "/\w+[@]{1}\w+[.]\w+/";
			// if(!preg_match( $pattern, $email)) return -2;
			
			//3、邮箱是否注册过
			if(M('User')->where(array('email'=>$email))->find()) return -3;

			return 0;
		}

		//注册步骤2
		public function register() {
			$uModel = D('User');
			$code = $uModel->reg();

			$email = I('email', '');
			$email_arr = explode('@', $email);
			$rst = array('email'=>I('email'), 'url'=>'http://mail.'.$email_arr[1]);
			jsonReturn($code,$rst); 
		}
	
		//激活账户
		public function activate_account() {
			header("Content-type:text/html;charset=utf-8");

			$token = I('token', '');
			$e = I('e', '');
			if(!$e) die('error');
			if(!$token) die('error');

			$uModel = M('user');

			//1、判断是否激活过
			$user = $uModel->where(array('email'=>$e))->find();
			if($user) die('error');

			//2、判断是否存在
			$a_info = M('email_verify')->where(array('email'=>$e))->find();	
			if(!$a_info) die('error');
			
			//3、判断激活码过期时间
			if($a_info['expiretime'] < time()) die('验证码已过期，请重新发送邮件!');
		
			//3、判断激活码是否正确
			if($token == encrypt_email($e, $a_info['expiretime'])) {
				//>> 改变激活状态
				M('email_verify')->save(array('id'=>$a_info['id'], 'status'=>1));

				//跳转
				$this->redirect("Index/index", array('token'=>$token, 'e'=>$e));
			}else{
				die('验证码错误!');
			}
		} 

		//重新发送验证码
		public function send_active_email($email) {
			$uModel = D('User');
			
			$code = $uModel->send_activate_email($email);
			
			jsonReturn($code['code']); 
		}

		//用户登录
		public function login() {
			$email = I('email', '');
			$password = I('password', '');

			$uModel = D('User');
			$code = $uModel->login($email, $password);
			jsonReturn($code);
		}
		//用户登录
		public function outlogin() {
			 session(null);
		}
		
		
		//找回密码--验证邮箱--发邮件
		public function get_reward($email = ''){
			
			// 1、为空验证
			if($email == '')  jsonReturn(-1);

			//2、正则验证
			$pattern = "/\w+[@]{1}\w+[.]\w+/";
			if(!preg_match( $pattern, $email)) jsonReturn(-2);
			
			//3、邮箱是否注册过
			$info = M('User')->field('id,nickname,email')->where(array('email'=>$email))->find();
			if($info){//验证通过
				//4、发送邮件
				D('User')->send_reward_email($email);
				
				$email_arr = explode('@', $email);
				$rst = array('email'=>$email, 'url'=>'http://mail.'.$email_arr[1]);
				
				jsonReturn(0, $rst);
				
			} else {
				jsonReturn(-215);
			}
	
		}
		
		//找回密码-验证邮箱--验证邮件
		public function verify_reward_email() {
			header("Content-type:text/html;charset=utf-8");

			$token = I('token', '');
			$e = I('e', '');
			
			if(!$e) die('error');
			if(!$token) die('error');

			$uModel = M('user');

			//1、判断是否激活
			$user = $uModel->where(array('email'=>$e))->find();
			if(!$user) die('error');

			//2、判断是否存在
			$a_info = M('email_verify')->where(array('email'=>$e))->find();	
			if(!$a_info) die('error');
			
			//3、判断激活码过期时间
			if($a_info['expiretime'] < time()) die('验证码已过期，请重新发送邮件!');
		
			//3、判断激活码是否正确
			if($token == encrypt_email($e, $a_info['expiretime'])) {
				//>> 改变激活状态
				M('email_verify')->save(array('id'=>$a_info['id'], 'status'=>1));
				
				session('reward_user', $user);
				session('reward_user.end_time', time()+600);
				
				//跳转
				$this->redirect("Index/index", array('type'=>'reward', 'e'=>$e));
				
			}else{
				die('验证码错误!');
			}
		} 

		//找回密码-重置密码
		public function update_reward_password() {
			$password = I('password', '');
			
			if(!$password){
				jsonReturn(-20);//密码为空
			}
			
			$uModel = M('user');
			$info = session('reward_user');
			
			
			//1、判断是否存在
			$user = $uModel->where(array('email'=>$info['email']))->find();
			if(!$user) jsonReturn(-5);//密码为空

			//2、判断邮箱验证过期时间
			if($info['end_time'] < time()) jsonReturn(-211);
			
			//3、重置密码
			$data['password'] = encrypt_password($password);
			$data['last_login_time'] = time();
			
			$res = $uModel->where(array('email'=>$info['email']))->save($data);
			
			if($res){
				session('reward_user', null);
				
				jsonReturn(0);
			}else{
				jsonReturn(-5);
			}
			
		} 

		//第三方登录---获取授权地址
		public function get_order_login(){
			//第三登录
			//微博--授权地址
			$o = new LibWeiBo( '3807008683' , 'e9d1fb3c8872fd0d3240aae6bdd61fe2' );
			$weibo_url = $o->getAuthorizeURL('https://www.co-sense.cn/Index/index');
			
			$arr['weibo'] = $weibo_url;
			
			
			
			jsonReturn(0, $arr);
		}
		
		//第三方登录--qq登录
		public function qq_ordert_login(){
			$qc = new \QC('appid', 'skey');
			
			$qc->qq_login();
			
		}
	}
?>