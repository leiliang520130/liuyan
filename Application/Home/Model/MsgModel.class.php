<?php
	namespace Home\Model;
	use Think\Model;
	class MsgModel extends Model {
		protected $tableName = 'user';

		private function _to_array($arr) {
			if(!$arr) return array();
			return $arr;
		}

		/**
		 * 获取用户消息
		 * @param  [type] $uid      [用户id]
		 * @param  [type] $type     [类型：1系统消息  2评论回复]
		 * @param  [type] $page     [页数]
		 * @param  [type] $row [每页显示条数]
		 * @return [type]           [description]
		 */
		public function get_msg($uid, $type, $page = 1, $row = 10) {
			// $lst = 
		}

		/**
		 * 获取用户消息
		 * @param  [type] $uid      [用户id]
		 * @param  [type] $page     [页数]
		 * @param  [type] $row [每页显示条数]
		 * @return [type]           [description]
		 */
		public function get_user_sys($uid, $page = 1, $row = 10) {
			$lst = M('system_message')->where(array('uid'=>$uid))
			                          ->page($page, $page_num)
			                          ->order('id desc')
			                          ->select();
			return $this->_to_array($lst);
		}

		/**
		 * 获取用户消息
		 * @param  [type] $uid      [用户id]
		 * @param  [type] $page     [页数]
		 * @param  [type] $row [每页显示条数]
		 * @return [type]           [description]
		 */
		public function get_user_selfmsg($uid, $page = 1, $row = 10) {
			$comment = M('comment');

			//>> 搜索条件
			$where_1 = " WHERE c.uid=".$uid." AND a.type=2 AND a.pid=0 AND a.is_del=0";    //用户的原创作品、类型原创、一级评论、未删除
			$where_2 = " WHERE a.to_uid=".$uid." AND a.is_del=0 AND a.pid != 0";           //被回复人是我、未删除
			$order = ' id desc';

			//>> 别人对我的原创作品进行回复
			$sql_1 = "SELECT a.*,b.nickname,b.avatar,c.title,1 as d_type FROM cos_comment a JOIN cos_user b ON a.uid=b.id JOIN cos_original_article c ON a.aid=c.id ".$where_1;
			
			//>> 别人对我的回复的评论
			$sql_2 = "SELECT a.*,b.nickname,b.avatar,'' as title,2 as d_type FROM cos_comment a JOIN cos_user b ON a.uid=b.id ".$where_2;

			//>> 组合sql
			$sql = "($sql_1) UNION ($sql_2) order by ".$order." limit ".(($page-1)*$row).",{$row}";

			//>> 获取数据
			$lst = M()->query($sql);
			$lst = $this->_to_array($lst);

			//>> 获取下级数据
			foreach ($lst as $k => &$v) {
				//>> 获取原创作品的下级评论
				if($v['d_type'] == 1) {
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
				//>> 获取下级评论
				}else{
					$v['avatar'] = set_header($v['avatar']);
					$v['son_lst_count'] = 0;
					//>> 下级评论
					$temp = $comment->alias('a')
					                ->field('a.*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%s") time,b.nickname,b.avatar,c.nickname to_nickname')
					                ->join('cos_user b ON a.uid=b.id')
					                ->join('LEFT JOIN cos_user c ON a.to_uid=c.id')
					                ->where(array('a.pid'=>$v['id'], 'a.is_del'=>0))
					                ->order('a.id asc')
								    ->select();

					foreach ($temp as $key => $val) {
						$temp[$key]['avatar'] = set_header($val['avatar']);
					}	
					$v['son_lst'] = $temp ? $temp : array();

					//>> 获取作品信息
					if($v['type'] == 1) {
						$v['title'] = M('fine_articles')->where(array('id'=>$v['aid']))->getField('title');
					}else if($v['type'] == 2){
						$v['title'] = M('original_article')->where(array('id'=>$v['aid']))->getField('title');
					}else{
						$v['title'] = M('activity_articles')->where(array('id'=>$v['aid']))->getField('title');
					}	
				}
			}
		
			return $lst;
		}
	}
?>