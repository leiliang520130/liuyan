<?php
	namespace Admin\Controller;
	class RenzhengController extends BaseController {
	    public function lst(){
	    	$filter = "enabled=1";

	    	//筛选条件
	    	$true_name = I('true_name', '');
	        $check = I('enabled', 1, 'intval');
	        if($check) $filter = "enabled = $check";
	    	if($true_name) $filter .= " AND true_name like '%{$true_name}%'";
	        
	    	//获取数据
	    	$sql_count = "SELECT COUNT(*) num FROM cos_sfz_info";
	    	$sql_data = "SELECT id,true_name,phone,email,sfz_face,sfz_back,enabled FROM cos_sfz_info WHERE ".$filter." ORDER BY id DESC";
	        $lst = $this->sql_page($sql_count, $sql_data);
	   		$this->assign('lst', $lst);
	    	

	    	$this->display();
	    }
	    /*
			审核认证信息
	    */
	    public function check_renzheng(){
	    	
	    	$id = I('id');
	    	$data =array(
	    		'enabled' => 2
	    	);
	    	$res = M('sfz_info')->where(array('id'=>$id))->save($data);
	    	if($res){
	    		$uid = M('sfz_info')->where(array('id'=>$id))->getfield('uid');
	    		$data['uid'] = $uid;
				$data['content'] = '管理员通过了您的个人认证申请；加油！新青年，在创作的道路上 CO-SENSE 永远支持你';
				$data['createtime'] = time();
				M('system_message')->add($data);

				M('user')->save(array('id'=>$uid, 'v_ident'=>1));

	    		echoResult(0);
	    	}else{
	    		echoResult(-1);
	    	}

	    }
	}
?>