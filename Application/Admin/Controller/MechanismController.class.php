<?php
	namespace Admin\Controller;
	use Common\Api\UploadApi;

	class MechanismController extends BaseController {
	    
		//组织机构列表
		public function index(){
	    	$filter = "type=2";

	    	//筛选条件
	    	$nickname = I('nickname', '');
	        if($nickname) $filter .= " AND nickname like '%{$nickname}%'";
	        
	    	//获取数据
	    	$sql_count = "SELECT COUNT(*) num FROM cos_user WHERE ".$filter;
	    	$sql_data = "SELECT id,nickname,email,fans_number,focus_on,reg_time FROM cos_user WHERE ".$filter." ORDER BY id DESC";
	        $lst = $this->sql_page($sql_count, $sql_data);
	   		$this->assign('lst', $lst);
	    	

	    	$this->display();
	    }
	    
		//新增机构
		public function op(){

			if(IS_POST){
				$nickname = I('nickname', '');
				$password = I('password', '');
				$email = I('email', '');
				$synopsis = I('synopsis', '');
				$industry = I('industry', '');
				$avatar = I('avatar', '');
				$province_detail=I('province','');
				$city_detail=I('city','');
				$area_detail=I('area','');

				//1、验证提交的信息
				if(!$email){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'邮箱有误'));
				}
				if(!$nickname){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'组织机构名称有误'));
				}
				if(!$password){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'密码有误'));
				}
				
				//2、验证邮箱和组织机构名是否存在
				$is_name = M('user')->where(array('nickname'=>$nickname, 'type'=>2))->find();
				if($is_name){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'此组织机构名称已存在！'));
				}
				
				$is_email = M('user')->where(array('email'=>$email))->find();
				if($is_email){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'此登录邮箱已存在！'));
				}
				
				//3、数据入库
				$data = array(
					'nickname'       =>  $nickname, 
					'password'       =>  encrypt_password($password),
					'email'          =>  $email,
					'email_status'   =>  0, 
					'reg_time'       =>  time(), 
					'reg_ip'         =>  get_client_ip(),
					'type'           =>  2,
					'synopsis'       =>  $synopsis,
					'industry'       =>  $industry,
					'avatar'         =>  $avatar,
					'province'=>  $province_detail,
					'city'=>  $city_detail,
					'area'=>  $area_detail
				);
				
				$res = M('user')->add($data);
				
				if($res){
					$this->ajaxReturn(array('code'=>1, 'msg'=>'添加组织机构成功'));
				}else{
					$this->ajaxReturn(array('code'=>0, 'msg'=>'添加失败！'));
				}
				
				
			}else{
				$pro_lst=D('locations')->get_son_lst(0);
				$this->assign('pro_lst',$pro_lst);
				$this->display();
			}
		}

		//一步获取地址
		public function ajax_get_area_son($pid) {
			$lst = D('locations')->get_son_lst($pid);
			die(json_encode(array('code'=>0, 'data'=>$lst)));
		}

		//异步上传头像
		public function img_upload($path = '/Activity/Cover/'){
			$set['path'] = $path;
			$img = new \Common\Api\ImgApi();
			$json = $img->upload($set);
			if($json['code'] == 0){
				unset($json['realpath']);
			}
			$json['msg'] = getErrArr($json['code']);
			echo json_encode($json);
		}



		//机构编辑
		public function edit($id){
			$id = $id;
			if(IS_POST){
				$nickname = I('nickname', '');
				$email = I('email', '');
				$synopsis = I('synopsis', '');
				$industry = I('industry', '');
				$avatar = I('avatar', '');
				$province_detail=I('province','');
				$city_detail=I('city','');
				$area_detail=I('area','');
				
				//1、验证提交的信息
				if(!$email){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'邮箱有误'));
				}
				if(!$nickname){
					$this->ajaxReturn(array('code'=>0, 'msg'=>'组织机构名称有误'));
				}
				
				//2、数据入库
				$data = array(
					'nickname'       =>  $nickname, 
					'email'          =>  $email,
					'synopsis'       =>  $synopsis,
					'industry'       =>  $industry,
					'avatar'         =>  $avatar,
					'province'=>  $province_detail,
					'city'=>  $city_detail,
					'area'=>  $area_detail
				);

				$res = M('user')->where(array('id'=>$id))->save($data);

				if($res){
					$this->ajaxReturn(array('code'=>1, 'msg'=>'修改组织机构成功'));
				}else{
					$this->ajaxReturn(array('code'=>0, 'msg'=>'修改失败！'));
				}

			}else{
				$info = M('user')->where(array('id'=>$id, 'type'=>2))->find();

				$pro_lst=D('locations')->get_son_lst($info['province']);
				$this->assign('pro_lst',$pro_lst);

				$city_lst=D('locations')->get_son_lst($info['city']);
				$this->assign('city_lst',$city_lst);

				$area_lst=D('locations')->get_son_lst($info['area']);
				$this->assign('area_lst',$area_lst);
				$this->assign('info', $info);
				
				$this->display();
			}
			
		}
		
		//机构下属成员
		public function member(){
			$id = I('id', '');
			
			//机构信息
			$info = M('user')->where(array('id'=>$id, 'type'=>2))->find();
			$this->assign('info', $info);
			
			$filter = "mid={$id}";

	    	//筛选条件
	    	$nickname = I('nickname', '');
	        if($nickname) $filter .= " AND b.nickname like '%{$nickname}%'";
	        
	    	//获取数据
	    	$sql_count = "SELECT COUNT(*) num FROM cos_user_group as a left join cos_user as b on a.uid=b.id WHERE ".$filter;
	    	$sql_data = "SELECT b.id,b.nickname,b.email,b.fans_number,b.focus_on,b.reg_time FROM  cos_user_group as a left join cos_user as b on a.uid=b.id  WHERE ".$filter." ORDER BY a.id DESC";
	        $lst = $this->sql_page($sql_count, $sql_data);
	   		$this->assign('lst', $lst);
			
			$this->display();
		}
		
		//查询可添加成员
		public function add_member_list($id = ''){
			$page = I('page', 1);
			$rows = I('rows', 10);
			
			$filter = "type=1";
	    	//筛选条件
	    	$nickname = I('nickname', '');
	        if($nickname) $filter .= " AND nickname like '%{$nickname}%'";
	        
	    	//获取数据
			$sql_count = "SELECT COUNT(*) num FROM cos_user WHERE {$filter} and id not in (select uid from cos_user_group where mid={$id})";
	    	$count = M('')->query($sql_count);
			$totalCount = $count[0]['num'];
			
			$start = ($page - 1)*$rows;
			
			$sql_data = "SELECT id,nickname,email,fans_number FROM  cos_user  
						WHERE {$filter} and id not in (select uid from cos_user_group where mid={$id}) ORDER BY id DESC limit $start,$rows";
	        $lst = M('')->query($sql_data);
			
			$pageTotal = ceil($totalCount / $rows);
			
			$this->assign('id', $id);
			$this->assign('lst', $lst);
			$this->assign('page', $page);
			$this->assign('rows', $rows);
			$this->assign('count', $totalCount);
			$this->assign('pageTotal', $pageTotal);
			
			$this->display();
			
		}
		
		//添加机构成员
		public function add_member(){
			$mid = I('id', '');
			$id_list = I('ids', '');
			
			if(!$mid || !$id_list){
				$this->ajaxReturn(array('code'=>0, 'msg'=>'提交数据有误,请重新提交'));
			}
			
			$data = array();
			$error_data = array();
			foreach($id_list as $k => $v){
				$user_info = M('user')->where(array('id'=>$v,'type'=>1))->find();
				if(!$user_info){
					$error_data[$k] = $v;
					continue;
				}
				
				$data[$k] = array(
					'uid' => $v,
					'mid' => $mid,
					'add_time' => time(),
					'status' => 0,
				);
			}
			
			if($data){
				$res = M('user_group')->addAll($data);
				
				if($res){
					$this->ajaxReturn(array('code'=>1, 'msg'=>'添加成功', 'error_data'=>$error_data));
				}else{
					$this->ajaxReturn(array('code'=>0, 'msg'=>'添加失败'));
				}
			}else{
				$this->ajaxReturn(array('code'=>0, 'msg'=>'提交数据有误,请重新提交'));
			}
			
		}
		
		//删除机构成员
		public function delete_member(){
			$id = I('id', '');
			$mid = I('mid', '');
			
			$map['uid'] = $id;
			$map['mid'] = $mid;
			
			$res = M('user_group')->where($map)->delete();
			if($res){
				$this->ajaxReturn(array('code'=>1, 'msg'=>'删除成功'));
			}else{
				$this->ajaxReturn(array('code'=>0, 'msg'=>'删除失败'));
			}
			
		}
	}
?>