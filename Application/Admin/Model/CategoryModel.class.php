<?php
	namespace Admin\Model;
	use Think\Model;
	class CategoryModel extends Model {
		/**
		 * null转化为空数组
		 * @param  [type] $arr [description]
		 * @return [type]      [description]
		 */
		private function _return_arr($arr){
			return $arr ? $arr : array(); 
		}

		/**
		 * 获所有分类（树型结构）
		 * @return [type] [description]
		 */
		public function get_tree_lst() {
			$d = new \Common\Api\DataApi();

			$lst = M('category')->order('orders asc')->select();
			$lst = $d->tree($lst,"&nbsp;",'id','pid');
			
			return $this->_return_arr($lst);
		}

		/**
		 * 获取子分类
		 * @param  [type] $id [description]
		 * @return [type]     [description]
		 */
		public function get_son_lst($id) {
			$d = new \Common\Api\DataApi();

			$lst = M('category')->order('orders asc')->select();
			$lst = $d->channelList($lst, $id, "&nbsp;", 'id', 'pid');

			return $this->_return_arr($lst);
		}
	}
?>