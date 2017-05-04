<?php
	namespace Admin\Controller;
	class FineArticleController extends BaseController {
		/**
		 * 精选文章列表
		 * @return [type] [description]
		 */
	    public function lst(){
	    	$cModel = D('Category');
	    	$filter = "a.enabled=1";

	    	//筛选条件
	    	$start_time = I('start_time', '');
	    	$end_time = I('end_time', '');
	    	$category_id = I('category_id', 0, 'intval');

	    	$title = I('title', '');

	    	if($start_time && $end_time) {
	    		$filter .= " AND a.createtime>=".strtotime($start_time)." AND a.createtime <=".strtotime($end_time." 23:59:59");
	    	}else if($start_time) {
	    		$filter .= " AND a.createtime>=".strtotime($start_time);
	    	}else if($end_time) {
	    		$filter .= " AND a.createtime<=".strtotime($end_time." 23:59:59");
	    	}
	    	if($category_id) {
	    		$son_str = "{$category_id}";
	    		$son_lst = D('Category')->get_son_lst($category_id);
	    		foreach ($son_lst as $k => $v) {
	    			$son_str .= ",".$v['id'];
	    		}
	    		$filter .= " AND a.category_id IN(".$son_str.")";
	    	}
	    	if($title) $filter .= " AND a.title like '%{$title}%'";

	    	//获取数据
	    	$sql_count = "SELECT count(*) num FROM (SELECT a.id FROM cos_fine_articles a JOIN cos_admin b ON a.authors=b.id JOIN cos_category ca ON a.category_id=ca.id LEFT JOIN cos_fine_articles_tags c ON a.id=c.fine_id LEFT JOIN cos_fine_tags d ON c.fine_tag_id=d.id WHERE ".$filter." GROUP BY a.id) aaa";
	    	$sql_data = "SELECT a.id,a.title,a.createtime,a.collect_nums,a.praise_nums,a.click_nums,b.username,ca.cname,GROUP_CONCAT(d.tname) tags FROM cos_fine_articles a JOIN cos_admin b ON a.authors=b.id JOIN cos_category ca ON a.category_id=ca.id LEFT JOIN cos_fine_articles_tags c ON a.id=c.fine_id LEFT JOIN cos_fine_tags d ON c.fine_tag_id=d.id WHERE ".$filter." GROUP BY a.id ORDER BY a.id DESC";

	    	$lst = $this->sql_page($sql_count, $sql_data);
	    	$cate_lst = $cModel->get_tree_lst();
	    	
	   		$this->assign('lst', $lst);
	    	$this->assign('cate_lst', $cate_lst);
	    	$this->display();
	    }

	    //新增和编辑
	    public function op() {
	    	//新增和编辑
	    	if(IS_POST) {
				$data = I('post.');
				$d = D('FineArticles');
				$code = $d->op($data);

				die(json_encode(array('code'=>$code)));
	    	}

	    	//1、获取所有分类
	    	$cModel = D('Category');
	    	$cate_lst = $cModel->get_tree_lst();

	    	//2、获取所有标签
	    	$tags_lst = M('fine_tags')->select();

	    	//3、如果是编辑
	    	$id = I('id', 0, 'intval');
	    	if($id) {
	    		//>>  获取精选文章信息
	    		$info = M('fine_articles')->where(array('id'=>$id, 'enabled'=>1))->find();
	    		//>>  获取精选文章的标签信息
	    		$temp_tags = M('fine_articles_tags')->alias('a')->field('b.tname')->join('cos_fine_tags b on a.fine_tag_id=b.id')
	    		                                    ->where(array('a.fine_id'=>$id))->select();
	    		$info['tags'] = $temp_tags ? $temp_tags : array();
	    		$this->assign('info', $info);
	    	}

	    	$this->assign('tags_lst', $tags_lst);
	    	$this->assign('cate_lst', $cate_lst);
	    	$this->display();
	    }

	    //删除
	    function del_article($id=0) {
	    	if(M('fine_articles')->save(array('id'=>$id, 'enabled'=>0)) !== false) {
	    		$code = 0;
	    	}else{
	    		$code = -100;
	    	}

	    	die(json_encode(array('code'=>$code)));
	    }

	    //上传图片
	    public function upimg($path = '/Fine/Cover/'){
	        $set['path'] = $path;
	        $img = new \Common\Api\ImgApi();
	        $json = $img->upload($set);
	        if($json['code'] == 0){
	            unset($json['realpath']);
	        }
	        
	        $json['msg'] = getErrArr($json['code']);
	        echo json_encode($json);
	    }
	}
?>