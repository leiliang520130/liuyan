<?php
	namespace Admin\Controller;
	use Think\Controller;
	class CommentController extends BaseController {
		//评论列表
		public function lst() {
			//1、接收参数
			$start_time = I('start_time', '');
			$end_time = I('end_time', '');
			$cnt = I('cnt', '');
			$type = I('type', 0);

			//2、筛选条件
			$where = " WHERE 1=1";
			if($start_time && $end_time) {
				$where .= " AND a.createtime>=".strtotime($start_time)." AND a.createtime<=".strtotime($end_time." 23:59:59");
			}else if($start_time) {
				$where .= " AND a.createtime>=".strtotime($start_time);
			}else if($end_time) {
				$where .= " AND a.createtime<=".strtotime($end_time." 23:59:59");
			}
			if($cnt) $where .= " AND a.cnt LIKE '%{$cnt}%'";
			if($type) $where .= " AND a.type=".$type;

			//3、获取数据
			$sql_count = "SELECT COUNT(*) num FROM cos_comment a".$where;
			$sql_data = "SELECT a.*,b.nickname FROM cos_comment a JOIN cos_user b ON a.uid=b.id".$where;
			$lst = $this->sql_page($sql_count, $sql_data);

			//4、获取文章名
			foreach ($lst as $k => &$v) {
				$b_name = '';
				if($v['type'] == 1) {
					$b_name = 'fine_articles';
				}else if($v['type'] == 2) {
					$b_name = 'original_article';
				}else if($v['type'] == 3) {
					$b_name = 'activity_articles';
				}

				$v['aname'] = M($b_name)->where(array('id'=>$v['aid']))->getField('title');
			}
			// p($lst);die;
			$this->assign('lst', $lst);
			$this->display();
		}

		//编辑评论
		public function eidt_com() {
			$id = I('id', 0);
			$info = M('comment')->where(array('id'=>$id))->find();

			if(IS_AJAX) {
				$arr = array(
					'id'  =>   $id, 
					'cnt' =>   I('cnt', '')
					);
				M('comment')->save($arr);
				die(json_encode(array('code'=>0)));
			}

			$this->assign('cnt', $info['cnt']);
			$this->display();
		}

		//删除评论[删除当前评论及其子级评论]
		public function com_del() {
			$id = I('id', 0);

			//删除本级评论及所有下级评论
			M('comment')->where(array('id'=>$id))->delete();
			M('comment')->where(array('zx_pid'=>$id))->delete();

			die(json_encode(array('code'=>0)));
		}
	}
?>