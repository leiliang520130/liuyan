<?php
	namespace Home\Controller;
	use Think\Controller;
	class CommentController extends Controller {
		public function lst() {
			$this->display();
		}

		/**
		 * 评论列表
		 * @param  integer $id          [文章id]
		 * @param  integer $type        [文章类型：1精选   2原创   3活动]
		 * @param  integer $page        [当前页数]
		 * @param  integer $return_type [返回值类型：1display  2json]
		 * @param  integer $row         [每个分类的条数]
		 * @return [type]               [description]
		 */
		public function com_lst($id=0, $type=1, $page=1, $return_type=1, $row=10) {
			$page = $page <= 0 ? 1 : $page;
			$comment = M('comment');

			//1、每次取10条1级评论
			$filter = array('a.aid'=>$id, 'a.type'=>$type, 'a.pid'=>0, 'a.is_del'=>0);
			$lst = $comment->alias('a')
			               ->field('a.*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%s") time,b.nickname,b.avatar,c.nickname to_nickname')
			               ->join('cos_user b ON a.uid=b.id')
			               ->join('LEFT JOIN cos_user c ON a.to_uid=c.id')
			               ->where($filter)
			               ->page("{$page}, {$row}")
			               ->order('a.id desc')
						   ->select();

			if(!$lst) $lst = array();

			//2、取每一条评论下的子评论
			foreach ($lst as $k => &$v) {
				$v['avatar'] = set_header($v['avatar']);
				//>> 子级评论条数
				$count = $comment->where(array('zx_pid'=>$v['id'], 'is_del'=>0))->count();
				$v['son_lst_count'] = $count;

				//>> 子级评论列表
				$temp = $comment->alias('a')
				                ->field('a.*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%s") time,b.nickname,b.avatar,c.nickname to_nickname')
				                ->join('cos_user b ON a.uid=b.id')
				                ->join('LEFT JOIN cos_user c ON a.to_uid=c.id')
				                ->where(array('a.zx_pid'=>$v['id'], 'a.is_del'=>0))
				                ->page("1, {$row}")
				                ->order('a.id asc')
							    ->select();

				foreach ($temp as $key => $val) {
					$temp[$key]['avatar'] = set_header($val['avatar']);
				}
				$v['son_lst'] = $temp ? $temp : array();
			}
			if($return_type == 1) {
				$conment_num = $comment->where(array('aid'=>$id))->count();
				
				//3、分配数据
				$this->assign('total', $comment->where(array('aid'=>$id, 'type'=>$type, 'pid'=>0, 'is_del'=>0))->count());
				$this->assign('lst', $lst);
				$this->assign('conment_num', $conment_num);
				$this->display();
			}else{
				jsonReturn(0, $lst);
			}
		}

		/**
		 * 获取某条具体评论下的二级评论列表
		 * @param  integer $id          [评论表的id]
		 * @param  integer $page        [当前页数]
		 * @param  integer $row         [每个分类的条数]
		 * @return [type]               [description]
		 */
		public function son_lst($id=1, $page=2, $row=10) {
			$page = $page <= 0 ? 2 : $page;
			$comment = M('comment');

			//>> 子级评论列表
			$lst = $comment->alias('a')
			                ->field('a.*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%s") time,b.nickname,b.avatar,c.nickname to_nickname')
			                ->join('cos_user b ON a.uid=b.id')
			                ->join('LEFT JOIN cos_user c ON a.to_uid=c.id')
			                ->where(array('a.zx_pid'=>$id, 'a.is_del'=>0))
			                ->page("{$page}, {$row}")
			                ->order('a.id asc')
						    ->select();

			foreach ($lst as $key => $val) {
				$lst[$key]['avatar'] = set_header($val['avatar']);
			}
			$lst = $lst ? $lst : array();

			jsonReturn(0, $lst);
		}

		/**
		 * 点赞
		 * @param  [type] $id [被点赞的评论id]
		 * @return [type]     [description]
		 */
		public function com_praise($id) {
			//判断是否登录
			if(!login_status()) jsonReturn(-7);

			//判断是否已经点赞过
			$praise = M('comment_praise');
			$res = $praise->where(array('cid'=>$id, 'uid'=>session('user.uid')))->find();
			
			if($res){
				//取消点赞
				$praise->where(array('id'=>$res['id']))->delete();
				//>> 点赞数-1
				M('comment')->where(array('id'=>$id))->setDec('praise_nums', 1);
				
				$this->ajaxReturn(array('code'=>0));
				
			}else{
				//点赞
				$data = array('cid'=>$id, 'uid'=>session('user.uid'), 'createtime'=>time());
				if($praise->add($data)) {
					//>> 点赞数+1
					M('comment')->where(array('id'=>$id))->setInc('praise_nums', 1);

					$this->ajaxReturn(array('code'=>1));
				}
			}	
		}

		//添加评论
		public function add_comment() {
			//判断是否登录
			if(!login_status()) jsonReturn(-7);

			//添加评论
			$data = I('post.');
			$data['uid'] = session('user.uid');
			$data['createtime'] = time();
			$id = M('comment')->add($data);
			if($id) {

				//文章的评论数+1
				if($data['type'] == 1) {
					
				}else if($data['type'] == 2) {

				}else if($data['type'] == 3) {

				}
				//评论自已的评论+1
				if($data['pid']) M('comment')->where(array('id'=>$data['pid']))->setInc('com_nums', 1);
				if($data['zx_pid']) M('comment')->where(array('id'=>$data['zx_pid']))->setInc('com_nums', 1);
				$data['id'] = $id;
				$user_info = M('user')->field('avatar,nickname')->where(array('id'=>$data['uid']))->find();
				$data['avatar'] = set_header($user_info['avatar']);
				$data['nickname'] = $user_info['nickname'];
				$data['time'] = date('Y-m-d H:i:s', $data['createtime']);  
				if($data['to_uid']) $data['to_nickname'] = M('user')->where(array('id'=>$data['to_uid']))->getField('nickname');

				jsonReturn(0, $data);
			}else{
				jsonReturn(-5);
			}
		}
	}
?>