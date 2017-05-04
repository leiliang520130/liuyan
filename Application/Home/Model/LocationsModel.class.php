<?php
	namespace Home\Model;
	use Think\Model;
	class LocationsModel extends Model {
		/**
		 * 获取的有儿子
		 * @param  [type] $pid [description]
		 * @return [type]      [description]
		 */
		public function get_son_lst($pid) {
			$lst = $this->where(array('parent_id'=>$pid))->select();
			return $this->_return_arr($lst);
		}

		/**
		 * null转化为空数组
		 * @param  [type] $arr [description]
		 * @return [type]      [description]
		 */
		private function _return_arr($arr){
			return $arr ? $arr : array(); 
		}
	}
?>