<?php
	namespace Admin\Controller;
	use Think\Controller;
	class ActivityController extends BaseController {
        public function lst(){
			$filter='enabled = 1';
	    	//筛选条件
	    	$start_time = I('start_time', '');
	    	$end_time = I('end_time', '');

	    	$title = I('title', '');

	    	if($start_time && $end_time) {
	    		$filter .= " AND a.createtime>=".strtotime($start_time)." AND a.createtime <=".strtotime($end_time." 23:59:59");
	    	}else if($start_time) {
	    		$filter .= " AND a.createtime>=".strtotime($start_time);
	    	}else if($end_time) {
	    		$filter .= " AND a.createtime<=".strtotime($end_time." 23:59:59");
	    	}
//	    	if($category_id) {
//	    		$son_str = "{$category_id}";
//	    		$son_lst = D('Category')->get_son_lst($category_id);
//	    		foreach ($son_lst as $k => $v) {
//	    			$son_str .= ",".$v['id'];
//	    		}
//	    		$filter .= " AND a.category_id IN(".$son_str.")";
//	    	}
	    	if($title) $filter .= " AND a.title like '%{$title}%'";

	    	//获取数据
	    	$sql_count = "SELECT COUNT(*) num FROM cos_activity_articles WHERE ".$filter;

	    	$sql_data = "SELECT a.*,b.username FROM cos_activity_articles a JOIN cos_admin b ON a.authors=b.id  WHERE ".$filter." ORDER BY a.id DESC";

	    	$lst = $this->sql_page($sql_count, $sql_data);
//	    	$cate_lst = $cModel->get_tree_lst();

	   		$this->assign('lst', $lst);

	    	$this->display();
        }



        public function op() {
			$M=M('activity_articles');
	    	//新增和编辑
	    	if(IS_POST) {
				$data = I('post.');
				if($data['id']){
					$res =$M->save($data);
					if($res){
						$code=0;
					}else{
						$code=-1;
					}
				}else{
					$data['createtime'] = time();
					$data['authors'] = session('admin_info.id');
					$data['comment_nums'] = 0;
					$data['click_nums'] = 0;
					$data['collect_nums'] = 0;
					$data['praise_nums'] = 0;
					$id=$M->add($data);
					if($id){
						$code=0;
					}else{
						$code=-1;
					}
				}
				die(json_encode(array('code'=>$code)));
	    	}else{
				$id=I('id');
				$info=$M->where(array('id'=>$id))->find();
				$this->assign('info',$info);
				$this->display();
			}

        }

		/**
		 * 文章详情内容
		 */
		public function detail()
		{
			$M=M('activity_articles');
			$id=I('id');
			$res=$M->where(array('id'=>$id))->find();
			$this->assign('info',$res);
            $this->display();
		}



	    //删除
	    function del_article($id=0) {
	    	if(M('activity_articles')->save(array('id'=>$id, 'enabled'=>0)) !== false) {
	    		$code = 0;
	    	}else{
	    		$code = -100;
	    	}
	    	die(json_encode(array('code'=>$code)));
	    }
	}
?>