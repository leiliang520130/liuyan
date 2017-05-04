<?php
	namespace Admin\Controller;
	use Think\Controller;
	
	class AuthController extends BaseController {
		/**
		 * 角色列表
		 * @return [type] [description]
		 */
		public function role_lst() {
	    	//获取数据
	    	$sql_count = "SELECT COUNT(*) num FROM cos_auth_group";
	    	$sql_data = "SELECT * FROM cos_auth_group";
	    	$lst = $this->sql_page($sql_count, $sql_data);
	    	// p($lst);die;
	    	//分配数据
	   		$this->assign('lst', $lst);
			$this->display();
		}

		/**
		 * 角色删除
		 * @return [type] [description]
		 */
		public function role_del() {
			$id = I('id', 0);
			M('auth_group')->where(array('id'=>$id))->delete();
			M('auth_group_access')->where(array('group_id'=>$id))->delete();
			die(json_encode(array('code'=>0, 'msg'=>'成功')));
		}

		/**
		 * 【添加/编辑】角色
		 * @return [type] [description]
		 */		
		public function role_add() {
			$id = I('id', 0);

			if(IS_AJAX) {                   
				$title = I('role_name', '');
				$rules = trim(I('role_str', ''), ',');

				$data = array('title'=>$title, 'rules'=>$rules);
				if($id) {
					if(M('auth_group')->where(array('title'=>$title, 'id'=>array('neq',$id)))->find()) die(json_encode(array('code'=>-1, 'msg'=>'角色名重复')));

					$data['id'] = $id;
					M('auth_group')->save($data);
				}else{
					if(M('auth_group')->where(array('title'=>$title))->find()) die(json_encode(array('code'=>-1, 'msg'=>'角色名重复')));

					M('auth_group')->add($data);
				}

				die(json_encode(array('code'=>0, 'msg'=>'成功')));
			}else{
				//所有权限列表
				$role_lst = M('auth_rule')->order('id asc')->select();

				//获取角色信息
				if($id) {
					$info = M('auth_group')->where(array('id'=>$id))->find();
					$temp_rule = explode(',', $info['rules']);

					foreach ($role_lst as $k => &$v) {
						$v['check'] = in_array($v['id'], $temp_rule) ? true : false;
					}

					$this->assign('info', $info);
				}

				$this->assign('role_lst', $role_lst);
				$this->display();
			}	
		}

		/**
		 * 管理员列表
		 * @return [type] [description]
		 */
		public function admin_lst() {
			$lst = M('admin')->alias('a')
			                 ->field('a.*,c.title')
			                 ->join('LEFT JOIN cos_auth_group_access b on a.id=b.uid')
			                 ->join('LEFT JOIN cos_auth_group c on b.group_id=c.id')
			                 ->order('a.id asc')
			                 ->select();

			$this->assign("lst", $lst);
			$this->display();
		}

		/**
		 * 【添加/编辑】管理员
		 * @return [type] [description]
		 */
		public function admin_add() {
			$id = I('id', 0);

			if(IS_AJAX) {
				$username = I('username', '');
				$password = I('password', '');
				$group_id = I('group_id', 0);

				$ad = D('Admin');
				$code = $ad->user_op($username, $password, $id);
				if($code != 0) die(json_encode(array('code'=>-1, 'msg'=>'用户名重复')));

				$id = ($id != 0) ? $id : M('admin')->where(array('username'=>$username))->getField('id');
				M('auth_group_access')->where(array('uid'=>$id))->delete();
				M('auth_group_access')->add(array('uid'=>$id, 'group_id'=>$group_id));

				die(json_encode(array('code'=>0, 'msg'=>'成功')));
			}

			if($id) {
				$info = M('admin')->where(array('id'=>$id))->find();
				$info['group_id'] = M('auth_group_access')->where(array('uid'=>$id))->getField('group_id');
				$this->assign('info', $info);
			}
			//所有角色
			$role_lst = M('auth_group')->order('id asc')->select();

			$this->assign("role_lst", $role_lst);
			$this->display();
		}

		/**
		 * 删除管理员
		 * @return [type] [description]
		 */
		public function admin_del() {
			$id = I('id', 0);
			M('admin')->where(array('id'=>$id))->delete();
			M('auth_group_access')->where(array('uid'=>$id))->delete();

			die(json_encode(array('code'=>0, 'msg'=>'成功')));
		}

		/**
		 * 菜单管理
		 * @return [type] [description]
		 */
		public function menu_lst() {
			$lst = M('auth_rule')->order('id asc')->select();
			$menu_lst = array();
			$d = new \Common\Api\DataApi();
			$menu_lst = $d->channelLevel($lst, 0, "&nbsp;", 'id');
			// p($menu_lst);die;

			$this->assign('menu_lst', $menu_lst);
			$this->display();
		}

		/**
		 * 【添加/编辑】菜单
		 * @return [type] [description]
		 */
		public function menu_add() {
			$id = I('id', 0);

			if(IS_AJAX) {
				$title = I('title', '');
				$icon = I('icon', '');
				$pid = I('pid', '');
				$menu_name = I('menu_name', '');

				$data = array(
					'icon'          =>     $icon,
					'name'     =>     $menu_name,
					'title'         =>     $title,
					'pid'           =>     $pid
					);

				if($id) {
					if(M('auth_rule')->where(array('title'=>$title, 'id'=>array('neq',$id)))->find()) die(json_encode(array('code'=>-1, 'msg'=>'菜单名称重复')));

					$data['id'] = $id;
					unset($data['pid']);
					unset($data['icon']);
					unset($data['name']);
					M('auth_rule')->save($data);
				}else{
					if(M('auth_rule')->where(array('title'=>$title))->find()) die(json_encode(array('code'=>-1, 'msg'=>'菜单名称重复')));

					M('auth_rule')->add($data);
				}

				die(json_encode(array('code'=>0, 'msg'=>'成功')));
			}else{
				//获取菜单列表
				$menu_lst = M('auth_rule')->where(array('pid'=>0))->order('id asc')->select();

				//编辑菜单的信息
				if($id) {
					$menu = M('auth_rule')->where(array('id'=>$id))->find();
					$this->assign('info', $menu);
				}

				$this->assign('menu_lst', $menu_lst);
				$this->display();
			}
		}

		/**
		 * 删除菜单
		 * @return [type] [description]
		 */
		public function menu_del() {

			$id=I('id',0);

			$map=array('id'=>$id);

			$res=M('auth_rule')->where($map)->find();

			if($res){
				if(M('auth_rule')->where($map)->delete()){
					$code=0;
					$msg='删除成功';
				}else{
					$code=-1;
					$msg='删除失败';
				}
			}
			$data=array('code'=>$code,'msg'=>$msg);
			$this->ajaxReturn($data,'json');
		}
	}
?>