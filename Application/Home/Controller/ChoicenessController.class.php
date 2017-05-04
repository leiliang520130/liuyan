<?php
namespace Home\Controller;
use Think\Controller;
class ChoicenessController extends Controller {
        public function choice(){
			//1、接收参数
            $category_id = I('type', 0);    //分类
            $page = I('p', 1);              //页数
            $num = I('n', 16);              //每页显示条数
            $r_type = I('r_type', 0);       //数据返回方式  0、page   1、json

            //2、筛选条件
			$map = null;
            $map['enabled'] = 1;
			if($category_id){
				$pid_list = M('category')->where(array('pid'=>$category_id))->getfield('id', true);
				$pid_list[] = $category_id;
				$map['a.category_id'] = array('in', $pid_list);
			}

            //3、获取数据
        	$fine_articles = M('fine_articles');
    		$info = $fine_articles->alias('a')
                                  ->field('a.*,b.cname,b.pid')
                                  ->join('cos_category b on a.category_id=b.id')
                                  ->where($map)
                                  ->page($page, $num)
                                  ->order('a.id desc')
                                  ->select();

            //4、获取所有一级栏目  
            $cat_lst = M('category')->where(array('pid'=>0))->order('id asc')->select();
            $c_lst = array();
            if($cat_lst) {
                foreach ($cat_lst as $k => $v) {
                    $c_lst[$v['id']] = $v;
                }                
            }
            
            //5、如果为二级栏目的，获取它的一级栏目名
            if($info) {
                foreach ($info as $k => &$v) {
                    $v['pname'] = '';
                    if($v['pid'] != 0) {
                        $v['pname'] = $c_lst[$v['pid']]['cname'];
                    }
                }
            }else{
                $info = array();
            }

            //6、分配数据
            if($r_type) {
                die(json_encode($info));
            }else{
                $this->assign('info',$info);
                $this->display();
            }
        }
		
        public function choice_details(){
         	$id = I('id');
            $praise_nums = I('like');
            $uid = session('user.uid');

            $names = M('collection_table')->where(array('uid'=>$uid))->select();
            $this->assign('names',$names);
            $result = M('collection')->alias('a')
                 ->field('a.collection_table_id')
                 ->join('left join cos_collection_table b on a.collection_table_id=b.id')
                 ->where(array('b.uid'=>$uid,'works_id'=>$id,'collection_type'=>1))
                 ->select(); 
            if($result){
                $collectioned = 1;
                $this->assign('collectioned',$collectioned);
            }else{
                $collectioned = 0;
                $this->assign('collectioned',$collectioned);
            }

        	$fine_articles = M('fine_articles');
    		$info = $fine_articles->where(array('id'=>$id,'enabled'=>1))->select();
            foreach ($info as $key => $infos) {
                // p($infos);
                $category = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
                $info[$key]['category'] = $category;
                //点赞状态
                $is_praise = M("article_praise")->where(array('puid'=>$uid,"article_id"=>$id,"praise_type"=>1))->find();
                if($is_praise){
                    $info[$key]["is_praise"] = 1;
                }else{
                    $info[$key]["is_praise"] = 0;
                }
            }
            $cnt = htmlspecialchars_decode($info[0]['cnt']);
            //此函数用作去除数据库取出的文章内容中的html标签
            $this->assign('cnt',$cnt);//专门获取文章内容
    		$this->assign('info',$info);//获取文章所有信息
    		$fine_articles_tags = M('fine_articles_tags');
    		$fine_tag_id = $fine_articles_tags->where(array('fine_id'=>$id))->getField('fine_tag_id',true);
    		$tags = array();
    		foreach ($fine_tag_id as $fine_tag_ids) {
    			$fine_tags = M('fine_tags');
    			$tname = $fine_tags->where(array('id'=>$fine_tag_ids))->select();
    			$tags[] = $tname;
    		}
    		$this->assign('tags',$tags);
            //获取此作品对应的标签

            $more_articles = $fine_articles->where(array('enabled'=>1))->order('id desc')->limit(4)->select();
            foreach($more_articles as $key => $more_articless){
                $fenlei = M('category')->where(array('id'=>$more_articless['category_id']))->select();
                $more_articles[$key]['cname'] = $fenlei[0]['cname'];
            }
            $this->assign('more_articles',$more_articles);
            //页面下方显示最新的4个精选作品

            $fine_articles = M("fine_articles");
            $fine_articles->where(array('id'=>$id))->setInc('click_nums');
            $resu = $fine_articles->alias('a')
                 ->field('a.*,b.nickname,b.address,b.fans_number,b.focus_on,b.industry,b.avatar,b.synopsis,b.sex,b.profession')
                 ->join('left join cos_user b on a.authors=b.id')
                 ->where(array("a.id"=>$id))
                 ->order('a.createtime desc')
                 ->select();
            $this->assign("or_details", $resu);

             $this->display();
        }
        /*
            新增点赞
            $id [页面传递的精选作品id]
        */
        public function like(){
            $id = I('id');
            $article_praise = M('article_praise');
            $res = $article_praise->where(array('article_id'=>$id,'praise_type'=>1))->getField('puid');
            $code = -1;
            if(empty($res)){
                //将当前用户和他点赞的精选作品传入数据库
                $praise_time = time();
                $user = session('user');
                $user_id = session('user.uid');
                $data = array(
                    'article_id' => $id,
                    'puid' => $user_id,
                    'praise_type' => 1,
                    'praise_time' => $praise_time,
                );
                $result = $article_praise->add($data);

                $praise_nums = I('like');
                $fine_articles = M('fine_articles');
                $data['id'] = $id;
                $data['praise_nums'] = $praise_nums;
                $result = $fine_articles->save($data);
                if($result){
                    $code = 200;
                }else{
                    $code = -200;
                }
                
            }else{
                $code = -201;
            }

            echoResult($code);  
        }
        /*
            新增收藏
            $id [页面传递的精选作品id]
        */
        public function choose_collection(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            $uid = session('user.uid');
            $name = I('collection_table_name');
            
            //判断是否已经有同名了
            if(M('collection_table')->where(array('collection_table_name'=>$name,'uid'=>$uid))->find()) {
                echoDataResult(-107,$names); 
            }

            $time = time();
            $data = array(
                'collection_table_name' => $name,
                'uid' => $uid,
                'createtime' => $time,
            );
            $res = M('collection_table')->add($data);
            if($res){
                $code = -45;
            }else{
                $code = -46;
            }

            $names = M('collection_table')->where(array('uid'=>$uid))->order('id desc')->limit(1)->select();
            
            echoDataResult($code,$names);  
        }

        /*
        将选中的作品收藏到收藏夹里
        */
        public function collectioned(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            // $uid = session('user.uid');
            $cid = I('post.cid');
            $id = I('post.id');
            $time = time();
            $data = array(
                'collection_table_id' => $cid,
                'works_id' => $id,
                'createtime' => $time,
                'collection_type' => 1,
            );
            $res = M('collection')->add($data);
            if($res){
                $code = -32;
                M('fine_articles')->where(array('id'=>$id))->setInc('collect_nums');
            }else{
                $code = -33;
            }
            echoResult($code);
        }

        /*
        删除收藏的作品
        */
        public function del_collectioned(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            $id = I('post.id');
            $uid = session('user.uid');
            $res = M('collection')->alias(a)
                ->field('a.collection_table_id')
                ->join('left join cos_collection_table b on a.collection_table_id=b.id')
                ->where(array('b.uid'=>$uid,'a.works_id'=>$id,'collection_type'=>1))
                ->select();
            $data = array(
                'collection_id' => $res[0]['collection_table_id'],
                'works_id' => $id,
                'collection_type' => 1
            );
            $result = M('collection')->where($data)->delete();
            if($result){
                $code = -48;
                M('fine_articles')->where(array('id'=>$id))->setDec('collect_nums');
            }else{
                $code = -49;
            }
            echoResult($code);
        }
        /*
            进入所指定的标签列表页面
        */
        public function fine_tags($id){
            
            $info = M('fine_articles')->alias(a)
                ->field('a.*')
                ->join('left join cos_fine_articles_tags b on a.id = b.fine_id')
                ->where(array('b.fine_tag_id'=>$id,'enabled'=>1))
                ->order('id desc')
                ->limit(16)
                ->select();
            $count = M('fine_articles')->alias(a)
                ->field('a.*')
                ->join('left join cos_fine_articles_tags b on a.id = b.fine_id')
                ->where(array('b.fine_tag_id'=>$id,'enabled'=>1))
                ->order('id desc')
                ->limit(16)
                ->count();
            
            foreach ($info as $key => $infos) {
                $category = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
                $info[$key]['category'] = $category;
            }
            $this->assign('info',$info);
            $this->assign('count',$count);
            $this->display();
        }
        /*
            指定标签页面的more按钮
        */
        public function fine_tags_more($begin){
            $begin = I('begin');
            $id = I('id');
            $info = M('fine_articles')->alias(a)
                ->field('a.*')
                ->join('left join cos_fine_articles_tags b on a.id = b.fine_id')
                ->where(array('b.fine_tag_id'=>$id,'enabled'=>1))
                ->order('id desc')
                ->limit($begin,32)
                ->select();
            if(empty($info)){
                echoDataResult(-47,$info);
            }
            foreach ($info as $key => $infos) {
                $category = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
                $info[$key]['category'] = $category;
            }
            echoDataResult(0,$info);
            //点击新增精选文章列表
        }

}