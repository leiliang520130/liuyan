<?php
	namespace Admin\Controller;
	class UserController extends BaseController {

		//获取会员列表,按照

		public function lst() {

			//1、接收参数
			$start = I('start', '');
			$end = I('end', '');
			$nickname = I('nickname', '');
			$email = I('email', '');
			$category_id=I('category_id','');

			//2、筛选
			$where = ' 1=1';
			if($start && $end) {
				$where .= " AND a.reg_time>=".strtotime($start)." AND reg_time<=".strtotime($end.' 23:59:59');
			}else if($start) {
				$where .= " AND a.reg_time>=".strtotime($start);
			}else if($end) {
				$where .= " AND a.reg_time<=".strtotime($end);
			}
			if($category_id)  $where.=" AND a.industry = {$category_id}";

			if($nickname) $where .= " AND a.nickname LIKE '%{$nickname}%'";
			if($email) $where .= " AND a.email LIKE '%{$email}%'";

	    	//3、获取数据
	    	$sql_count = "SELECT COUNT(*) num FROM cos_user a WHERE ".$where;
	    	$sql_data = "SELECT a.* ,b.name,c.tname,d.cname FROM cos_user a LEFT JOIN cos_country b ON b.id=a.country LEFT JOIN cos_research_area c ON c.id=a.industry LEFT JOIN cos_appellation d ON d.id=a.appellation WHERE ".$where." ORDER BY a.id DESC";
	    	$lst = $this->sql_page($sql_count, $sql_data);
//	    	 p($lst);die;
            $this->get_rearea();
			//获取分类

	    	$this->assign('lst', $lst);
	    	$this->display();
		}

		/**
		 * 批量执行发送邮件的功能
		 */
		public function send_email1()
		{
			$emails=I('emails');

			$em_num=I('enum');

			$er=M('send_email')->where('id='.$em_num)->find();

			foreach($emails as $email){
				$json[]=send_email($email,$er['title'],$er['cnt']);
			}
			echoDataResult($json);
		}


		/**
		 * 获取按照个人领域分类
		 */
		private function get_rearea(){
			$res=M('research_area')->field('id,tname')->select();
			$this->assign('cate_lst',$res);
		}

		//会员审核
		public function renzheng() {
			$id = I('id');
			$type=I('type',0);

			if(M('user')->where(array('id'=>$id))->save(array('status'=>$type))!==false){
				die(json_encode(array('code'=>0)));
			}else{
				die(json_encode(array('code'=>-1)));
			};

		}
		//锁定解锁用户
		public function to_locked(){
			$id = I('uid');
			$locked = I('type', 0);

			if(M('user')->save(array('id'=>$id, 'locked'=>$locked)) !== false) {
				die(json_encode(array('code'=>0)));
			}else{
				die(json_encode(array('code'=>-1)));
			}
		}

		/**
		 * 会员上传文件列表
		 */
		public function file_up_lst()
		{
			$where='1=1';
			//3、获取数据
			$sql_count = "SELECT COUNT(*) num FROM cos_files_up a WHERE ".$where;
			$sql_data = "SELECT a.* ,b.nickname FROM cos_files_up a LEFT JOIN cos_user b ON b.id=a.uid  WHERE ".$where." ORDER BY a.id DESC";
			$lst = $this->sql_page($sql_count, $sql_data);

			$this->assign('lst',$lst);

			$this->display();
		}

		/**
		 * 会员文件下载
		 */

		public function down_files() {
			$file=I('file');
			header('Content-Disposition: attachment; filename="'.$file.'"'); //指定下载文件的描述
			header('Content-Length:'.filesize($file)); //指定下载文件的大小
			readfile($file);
		}
		
		/**
	 * 新增添加职位
	 */
		public function add_industry()
		{
			if(IS_POST){
				$data=I('');
				if($data['id']){
					if(M('research_area')->save($data)!==false){
						die(json_encode(array('code'=>0)));
					}else{
						die(json_encode(array('code'=>-1)));
					}
				}else{
					$data['uid']=session('admin_info.id');
					$data['createtime']=time();
					if(M('research_area')->add($data)){
						die(json_encode(array('code'=>0)));
					}else{
						die(json_encode(array('code'=>-1)));
					}
				}
			}else{
				$id=I('id');
				$lst=M('research_area')->where(array('id'=>$id))->find();
				$this->assign('info',$lst);
				$this->display();
			}

		}

		/**
		 * 个人领域列表
		 */
		public function industry_lst()
		{
			$name=I('tname','');
			$where = ' 1=1';
			if($name){
				$where .= " a.tname LIKE '%{$name}%'";
			}
			$sql_count = "SELECT COUNT(*) num FROM cos_research_area WHERE ".$where;
			$sql_data = "SELECT b.username,a.tname,a.createtime,a.id FROM cos_research_area a JOIN cos_admin b ON b.id=a.uid WHERE ".$where." ORDER BY a.id DESC";
			$lst = $this->sql_page($sql_count, $sql_data);
			$this->assign('lst',$lst);
			$this->display();
		}

		public function del_industry()
		{
			$id=I('id');
			$res=M('research_area')->where(array('id'=>$id))->find();
			if($res){
				$r=M('research_area')->where(array('id'=>$id))->delete();
				if($r){
					die(json_encode(array('code'=>0)));
				}else{
					die(json_encode(array('code'=>-1)));
				}
			}
		}


		/**************************称谓列表,新增,修改,删除功能***********************/
		/**
		 * 新增添加职位
		 */
		public function add_appellation()
		{
			if(IS_POST){
				$data=I('');
				if($data['id']){
					if(M('appellation')->save($data)!==false){
						die(json_encode(array('code'=>0)));
					}else{
						die(json_encode(array('code'=>-1)));
					}
				}else{
					$data['uid']=session('admin_info.id');
					$data['createtime']=time();
					if(M('appellation')->add($data)){
						die(json_encode(array('code'=>0)));
					}else{
						die(json_encode(array('code'=>-1)));
					}
				}
			}else{
				$id=I('id');
				$lst=M('appellation')->where(array('id'=>$id))->find();
				$this->assign('info',$lst);
				$this->display();
			}

		}

		/**
		 * 个人领域列表
		 */
		public function appellation_lst()
		{
			$name=I('cname','');
			$where = ' 1=1';
			if($name){
				$where .= "a.cname LIKE '%{$name}%'";
			}
			$sql_count = "SELECT COUNT(*) num FROM cos_appellation WHERE ".$where;
			$sql_data = "SELECT b.username,a.cname,a.createtime,a.id FROM cos_appellation a JOIN cos_admin b ON b.id=a.uid WHERE ".$where." ORDER BY a.id DESC";
			$lst = $this->sql_page($sql_count, $sql_data);

			$this->assign('lst',$lst);
			$this->display();
		}

		public function del_appellation()
		{
			$id=I('id');
			$res=M('appellation')->where(array('id'=>$id))->find();

			if($res){
				$r=M('appellation')->where(array('id'=>$id))->delete();
				if($r){
					die(json_encode(array('code'=>0)));
				}else{
					die(json_encode(array('code'=>-1)));
				}
			}
		}

	}
?>