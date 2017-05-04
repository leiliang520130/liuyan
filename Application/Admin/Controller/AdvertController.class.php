<?php
	namespace Admin\Controller;
	use Think\Controller;
	class AdvertController extends BaseController {
		/**
		 * 广告位设置
		 * @return [type] [description]
		 */
		public function lst() {
			$info = D('advert')->where(array('id'=>1))->find();

			$this->assign('info', $info);
			$this->display();
		}

		public function advert_edit() {
			$img = I('img', '');
			$url = I('url', '');

			if(D('advert')->where(array('id'=>1))->save(array('img'=>$img, 'url'=>$url)) !== false) {
				die(json_encode(array('code'=>0)));
			}else{
				die(json_encode(array('code'=>-1)));
			}
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