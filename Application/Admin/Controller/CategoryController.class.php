<?php
	namespace Admin\Controller;
	class CategoryController extends BaseController {
		/*
			分类列表页
		*/
		public function lst() {
			$category = M('category');
			$all = $category->order("orders asc")->select();
			$d = new \Common\Api\DataApi();
			$lst = $d->channelLevel($all, 0, "&nbsp;", 'id');

			$this->assign('lst', $lst);
			$this->display();
		}	

		/*
			新增分类页
			$type [通过url传递的状态值,用来阻止没有点击确定就向后台发送数据的行为]
		*/
		public function op($type=0) {

			$category = M('category');
			$cname = $category->where(array("pid"=>0))->getField('cname',true);
			$this->assign('cname',$cname);
			if($type == 1){
				$this->display();
				exit;
			}

			$input_cname = I('cname');
			$input_pid = I('pid');
			$input_orders = I('orders');

			if(empty($input_cname)){
				// echo '分类名称不能为空';

				$this->display();
				echo "<script>alert('分类名称不能为空')</script>";
				exit;
			}

			if($input_pid == -1){
				$input_pid = 0;

				if($category->where(array('cname'=>$input_cname))->find()) {
					$this->display();
					echo "<script>alert('分类名已经存在')</script>";
					exit;
				}

				$data = array(
					'cname' => $input_cname,
					'pid' => $input_pid,
					'orders' => $input_orders,
				);
				$result = $category->add($data);
	            if($result) {
	                $this->success(
	                	"添加成功"
	                );
	            }else{
	                $this->error(
	                	"添加失败"
	                );
	            }
	            header('Location:../Category/file_up_lst.html');
			}else{
				$id = $category->where(array("pid"=>0))->getField('id',true);
				$pid = $id[$input_pid];
				echo $pid;

				if($category->where(array('cname'=>$input_cname))->find()) {
					$this->display();
					echo "<script>alert('分类名已经存在')</script>";
					exit;
				}

				$data = array(
					'cname' => $input_cname,
					'pid' => $pid,
					'orders' => $input_orders,
				);
				$result = $category->add($data);
           		if($result) {
	                $this->success(
	                	"添加成功"
	                );
	            }else{
	                $this->error(
	                	"添加失败"
	                );
	            }
	            header('Location:../Category/file_up_lst.html');
			}
			
		}
		
		/*
			修改排序值
		*/
		public function orders() {
			$lst = I('');

			$up_lst = array();
			$m = M('category');
			foreach ($lst['orders'] as $k => $v) {
				$m->where(array('id'=>$k))->save(array('orders'=>$v));
			}


			$this->ajaxReturn(array('code'=>0));
		}

		/*
			编辑分类页
		*/
		public function edit($type=0) {
			$id = I('id');//列表页传来的选定的分类的id

			$category = M('category');
			$pid = $category->where(array("id"=>$id))->getField('pid');
			$this->assign('choose_id',$pid);

			$ids = $category->where(array("pid"=>0))->getField('id',true);
			$this->assign('ids',$ids);

			$type_name = $category->where(array("id"=>$id))->getField('cname');
			$this->assign('type_name',$type_name);
			session('id',$id);

			if($pid != 0){
				$cnames = $category->where(array("pid"=>0))->select();
				$this->assign('cname',$cnames);
			}
			if($type == 1){
				$this->display();
				exit;
			}

			$cname = I('post.cname');
			$input_pid = I('post.pid');

			if(empty($cname)){
				echo "分类名称不能为空";
				$this->display();
				exit;
			}
			if($input_pid == -1){
				$id = session('id');
				$data['id'] = $id;
				$data['cname'] = $cname;
				$data['pid'] = 0;
				$result = $category->save($data);
				if($result){
					$this->success('成功');
				}else{
					$this->error('失败');
				}
				session('id',null);
				header('Location:../Category/file_up_lst.html');
			}else{

				$id = session('id');
				$data['cname'] = $cname;
				$data['pid'] = $input_pid;
				$data['id'] = $id;
				$result =  $category->save($data);
				if($result){
					echo "成功";
				}else{
					echo "失败";
				}
				session('id',null);
				header('Location:../Category/file_up_lst.html');
			}
		}

		/*
			删除一级分类及下属的二级分类
		*/
		public function delete_one() {
			$id = I("id");
			$category = M('category');

			// $where['id']   = array('eq', $id);
			// $where['pid'] = array('eq', $id);
			// $where['_logic']  = 'or';
			// $map['_complex']  = $where;
			//使方法中可以使用or来连接多个条件
			// $result = $category->where($map)->delete();

			$map['id|pid'] = $id;
			//此方法与上面一样
			$result = $category->where($map)->delete();
			if($result){
				$result = "删除成功";
			}else{
				$result = "删除失败";
			}
			$this->ajaxReturn($result);
		}

		/*
			删除二级分类
		*/
		public function delete_two() {
			$id = I("id");
			$category = M('category');

			$result = $category->where(array('id'=>$id))->delete();
			if($result){
				$result = "删除成功";
			}else{
				$result = "删除失败";
			}
			$this->ajaxReturn($result);
		}
	}
?>