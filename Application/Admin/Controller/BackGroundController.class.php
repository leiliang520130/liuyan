<?php
	namespace Admin\Controller;
	use Think\Controller;
	class BackGroundController extends BaseController {
		public function add_bg() {
			$img = M('background_reg')->where(array('id'=>1))->getField('bg_img');
			
			$this->assign('img', $img);
			$this->display();
		}

	    public function upimg($path = '/Fine/Cover/'){
	        $set['path'] = $path;
	        $img = new \Common\Api\ImgApi();
	        $json = $img->upload($set);

	        if($json['code'] == 0){
	        	M('background_reg')->where(array('id'=>1))->save(array('bg_img'=>$json['url']));

	            unset($json['realpath']);
	        }
	        
	        $json['msg'] = getErrArr($json['code']);
	        echo json_encode($json);
	    }
	}
?>