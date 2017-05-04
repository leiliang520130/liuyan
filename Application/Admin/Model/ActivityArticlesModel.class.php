<?php
	namespace Admin\Model;
	use Think\Model;
	class ActivityArticlesModel extends Model {
		public function op($data) {
			$aModel = M('activity_tags');
			$aatModel = M('activity_articels_tags');

			//新增
			if($data['id'] == 0) {
				$data['createtime'] = time();
				$data['authors'] = session('admin_info.id');
				$data['comment_nums'] = 0;
				$data['click_nums'] = 0;
				$data['collect_nums'] = 0;
				$data['praise_nums'] = 0;
				//>> 精选文章入库
				$id = $this->add($data);

				//>> 精选文章标签入库 
				if($id) {
					foreach ($data['tags'] as $k => $v) {
						$tid = $aModel->where(array('tname'=>$v))->getField('id');
						if(!$tid) $tid = $aModel->add(array('tname'=>$v,'createtime'=>time(),'aid'=>session('admin_info.id')));
						$aatModel->add(array('activity_id'=>$id, 'tid'=>$tid));
					}					
				}

				return $id ? 0 : -100;
			//编辑
			}else{
				//>> 精选文章修改
				$up = $this->save($data);

				//>> 精选文章标签入库 
				//>> 1、先删除
				$aatModel->where(array('activity_id'=>$data['id']))->delete();

				//>> 2、再添加
				foreach ($data['tags'] as $k => $v) {
					$tid = $aModel->where(array('tname'=>$v))->getField('id');
					if(!$tid) $tid = $aModel->add(array('tname'=>$v,'createtime'=>time(),'aid'=>session('admin_info.id')));
					$aatModel->add(array('activity_id'=>$data['id'], 'tid'=>$tid));
				}					

				return ($up!==false) ? 0 : -100;
			}
		}
	}
?>