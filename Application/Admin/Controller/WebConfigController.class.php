<?php
	namespace Admin\Controller;
	class WebConfigController extends BaseController {
		/**
		 * 邮箱配置
		 * @return [type] [description]
		 */
		public function conf_email() {

			if(IS_POST) {
				$data=I('');
				M('send_email')->add($data);
			}else{
				$this->display();
			}
		}

		/**
		 * 搜索控制
		 * @return [type] [description]
		 */
		public function conf_search() {
			$cm=M('company');
			if(IS_POST) {
				$data=I('cnt');
				$data['createtime']=time();
				$data['enabled']=1;
				$cm->where(array('enabled'=>1))->save('enabled=.0');
				if($cm->add($data)){
					echoResult(0);
				}else{
					echoResult(-1);
				};
			}else{
				$r=$cm->where(array('enabled'=>1))->find();
				$this->assign('email',$r);
				$this->display();
			}


		}
	}
?>
