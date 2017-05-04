<?php
	namespace Admin\Model;
	use Think\Model;
	class AdminModel extends Model {
		private $secret = 'cosense';

		public function login($username, $password) {
			$aModel = M('admin');

			//1、验证码用户名密码为空
			if(!$username || !$password) return -100;
			$password = $this->_option_pwd($password);

			//2、验证码用户名密码是否匹配
			$info = $aModel->where(array('username'=>$username))->find();

			if($info && $info['password'] == $password) {
				//>> 管理员信息入session
				session('admin_info', array('id'=>$info['id'], 'username'=>$info['username']));

				//>> 更新最后登录时间和登录IP
				$aModel->save(array('id'=>$info['id'], 'last_login_ip'=>get_client_ip(), 'last_login_time'=>time()));

				//>> 记录管理员登录日志
				M('admin_login_log')->add(array('aid'=>$info['id'], 'login_time'=>time(), 'login_ip'=>get_client_ip()));

				return 0;
			}else{
				return -101;
			}
		}

		/**
		 * 【添加/编辑】用户
		 * @param  [type]  $username [用户名]
		 * @param  [type]  $password [密码]
		 * @param  integer $id       [用户id]
		 * @return [type]            [description]
		 */
		public function user_op($username, $password, $id=0) {
			if($id) {
				if($this->where(array('username'=>$username, 'id'=>array('neq',$id)))->find()) return -1; //用户名重复
				$data = array('id'=>$id, 'username'=>$username);
				if($password) $data['password'] = $this->_option_pwd($password);;
				$this->save($data);

				return 0;    //成功
			}else{
				if($this->where(array('username'=>$username))->find()) return -1; //用户名重复
				$password = $this->_option_pwd($password);
				$this->add(array('username'=>$username, 'password'=>$password));

				return 0;   //成功
			}
		}

		//加密密码
		private function _option_pwd($pwd) {
			return md5(md5($pwd.$this->secret));
		}
	}
?>