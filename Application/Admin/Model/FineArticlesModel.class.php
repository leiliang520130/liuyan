<?php
	namespace Admin\Model;
	use Think\Model;
	class FineArticlesModel extends Model {
		public function op($data) {
			$tModel = M('fine_tags');
			$ftaModel = M('fine_articles_tags');

			//新增
			if($data['id'] == 0) {
				$data['createtime'] = time();
				$data['authors'] = session('admin_info.id');
				$data['collect_nums'] = 0;
				$data['praise_nums'] = 0;
				//>> 精选文章入库
				$id = $this->add($data);

				//>> 精选文章标签入库 
				if($id) {
					foreach ($data['tags'] as $k => $v) {
						$tid = $tModel->where(array('tname'=>$v))->getField('id');
						if(!$tid) $tid = $tModel->add(array('tname'=>$v,'createtime'=>time(),'aid'=>session('admin_info.id')));
						$ftaModel->add(array('fine_id'=>$id, 'fine_tag_id'=>$tid));
					}					
				}

				return $id ? 0 : -100;
			//编辑
			}else{
				//>> 精选文章修改
				$up = $this->save($data);

				//>> 精选文章标签入库 
				//>> 1、先删除
				$ftaModel->where(array('fine_id'=>$data['id']))->delete();

				//>> 2、再添加
				foreach ($data['tags'] as $k => $v) {
					$tid = $tModel->where(array('tname'=>$v))->getField('id');
					if(!$tid) $tid = $tModel->add(array('tname'=>$v,'createtime'=>time(),'aid'=>session('admin_info.id')));
					$ftaModel->add(array('fine_id'=>$data['id'], 'fine_tag_id'=>$tid));
				}					

				return ($up!==false) ? 0 : -100;
			}
		}
	}
?>