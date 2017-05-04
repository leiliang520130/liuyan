<?php
namespace Admin\Controller;
class OriginalController extends BaseController {
    public function lst(){
    	$cModel = D('Category');
    	$filter = "a.enabled=1";

    	//筛选条件
    	$category_id = I('category_id', 0, 'intval');
    	$title = I('title', '');
        $check = I('enabled', 1, 'intval');
        if($check) $filter = "a.enabled = $check";
    	if($category_id) {
    		$son_str = "{$category_id}";
    		$son_lst = D('Category')->get_son_lst($category_id);
    		foreach ($son_lst as $k => $v) {
    			$son_str .= ",".$v['id'];
    		}
    		$filter .= " AND a.cid IN(".$son_str.")";
    	}
    	if($title) $filter .= " AND a.title like '%{$title}%'";
        
    	//获取数据
    	$sql_count = "SELECT COUNT(*) num FROM (SELECT a.id FROM cos_original_article a JOIN cos_user u ON a.uid=u.id JOIN cos_category ca ON a.cid=ca.id WHERE ".$filter." GROUP BY a.id)aaa";
    	$sql_data = "SELECT a.id,a.title,a.createtime,a.collect_nums,a.page_view,a.praise_nums,a.enabled,b.nickname,ca.cname,d.tname,GROUP_CONCAT(d.tname) tags FROM cos_original_article a JOIN cos_user b ON a.uid=b.id JOIN cos_category ca ON a.cid=ca.id LEFT JOIN cos_original_article_tags c ON a.id=c.original_id LEFT JOIN cos_original_tags d ON c.tid=d.id WHERE ".$filter." GROUP BY a.id ORDER BY a.id DESC";
        $lst = $this->sql_page($sql_count, $sql_data);
        // P($lst);die;
    	$cate_lst = $cModel->get_tree_lst();
   		$this->assign('lst', $lst);
    	$this->assign('cate_lst', $cate_lst);

    	$this->display();
    }
    //删除原创作品
    function del_article($id=0) {
        if(M('original_article')->save(array('id'=>$id, 'enabled'=>0)) !== false) {
            $code = 0;
        }else{
            $code = -100;
        }

        die(json_encode(array('code'=>$code)));
    }

    //原创作品审核通过
    function check_article($id=0) {

        $info = M(original_article)->where(array('id'=>$id))->select();
        $title = $info[0]['title'];
        $uid = $info[0]['uid'];
        $message = "管理员审核通过了你发布的原创作品：《".$title."》；加油！新青年，在创作的道路上 CO-SENSE 永远支持你";
        
        $system_message = M(system_message);
        $time= time();
        $date = array(
            'uid' => $uid,
            'content' => $message,
            'createtime' => $time,
        );
        $system_message->add($date);
        //将审核通过的消息传给系统消息表.

        if(M('original_article')->save(array('id'=>$id, 'enabled'=>2)) !== false) {
            $code = 0;
        }else{
            $code = -100;
        }
        die(json_encode(array('code'=>$code)));
    }

    //原创作品查看
    function look($id=0) {
        $original_article = M('original_article');
        $info = $original_article->where(array('id'=>$id))->select();
        
        $cnt = htmlspecialchars_decode($info[0]['cnt']);
        //此函数用作去除数据库取出的文章内容中的html标签
        $this->assign('cnt',$cnt);//专门获取文章内容

        $Category = M('Category');
        $Category_id = $Category->where(array('id'=>$info[0]['cid']))->select();
        if($Category_id[0]['pid'] == 0){
            $bq = $Category_id[0]['cname'];
            $this->assign('bq',$bq);
        }else{
            $one_category = $Category->where(array('id'=>$Category_id[0]['pid']))->getfield('cname');
            $bq = $one_category." -> ".$Category_id[0]['cname'];
            $this->assign('bq',$bq);
        }//查询原创作品的一二级分类

        $temp_tags = M('original_article_tags')->alias('a')->field('b.tname')->join('cos_original_tags b on a.tid=b.id')->where(array('a.original_id'=>$id))->select();
        $this->assign('temp_tags',$temp_tags);

        
        $this->assign('info',$info);
        $this->display();
    }
}