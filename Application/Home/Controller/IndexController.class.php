<?php
namespace Home\Controller;
use Common\Api\UploadApi;
use Think\Controller;

class IndexController extends Controller {
	/**
	 * 首页展示
	 */
        public function index(){
			//1 获取最新的文章展示
			$r1=M('activity_articles')->where(array('enabled'=>1))->order('id desc')->find();
			$uid=session('use_account.id');
			if($uid){
				//2 获取文章留言板信息
				/*$r2=M('notes')->alias('a')->field('a.*,b.nickname')->join('left join cos_user b ON b.id=a.uid')->where(array('pid'=>0,'uid'=>$uid))->order()->limit(0,5)->select();
				foreach($r2 as $k=>$vo){
					$n=$this->get_son_lst($vo['pid']);
					if(!empty($n)){
						$vo['son_lst'][]=$n;
					}
				}*/

				//因为时间关系，就不细想这个问题了
				$where='1=1';
				$where .=" AND a.uid = $uid";
				//$sql_count = "SELECT COUNT(*) num FROM cos_notes a WHERE ".$where;
				$sql_data = "SELECT a.* ,b.nickname FROM cos_notes a LEFT JOIN cos_user b ON b.id=a.uid  WHERE ".$where." ORDER BY a.id DESC";

				$lst = M()->query($sql_data);
				//双foreach遍历获取层级关系
				$tree = array();
				foreach($lst as $category){
					$tree[$category['id']] = $category;
					$tree[$category['id']]['children'] = array();

				}
				foreach ($tree as $key=>$value) {
					if ($value['pid'] != 0) {
						$tree[$value['pid']]['children'][] = &$tree[$key];
					}
				}
				//去除顶层pid不为0的数组方便前端展示
				foreach($tree as $k=>$v){
					if($v['pid'] !=0){
						unset($tree[$k]);
					}
				}

				$this->assign('user',session('use_account'));
			}
			$this->assign('info',$r1);
			//print_r($tree);exit;
			$this->assign('com',$tree);

            $this->display();
        }


	/**
	 * 获取二级评论
	 */
	private function get_son_lst($pid){
		return M('notes')->where(array('pid'=>$pid))->select();
	}


	
	   
	/**
	 * 添加留言
	 */
	public function add_notes()
	{
		$uid=session('use_account.id');

		if(!$uid){
			echoResult(-7);
		}
		$data=I('');

		if($data['cnt']){
			$arr1=array(
				'note_content'=>$data['cnt'],
				'note_time'=>time(),
				'uid'=>$uid,
				'pid'=>0
			);
			if(M('notes')->add($arr1)){
                echoResult(0);
			}else{
				echoResult(-1);
			};
		}
	}

	public function add_files()
	{
		$uid=session('use_account.id');
		if(!$uid){
			echoResult(-7);
		}
		$data=I('');

		if($data['Files']){
			$arr1=array(
				'files'=>$data['Files'],
				'uid'=>$uid,
				'createtime'=>time()
			);
			if(M('files_up')->add($arr1)){
                 echoResult(0);
			}else{
				echoResult(-1);
			}
		}
	}
	
	/**
	 * 上传文件功能
	 */
	public function up_files()
	{
		$set['path'] = '/Files/';
		$upload = new UploadApi();
		$json= $upload->upload($set);
		if($json['code'] == 0){
			session('sfz_back',$json['url']);
		}
		$json['msg'] = getErrArr($json['code']);
		echo json_encode($json);
	}
}