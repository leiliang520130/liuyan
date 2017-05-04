<?php
namespace Home\Controller;
use Think\Controller;
class UserCenterController extends Controller {
        //切换背景
        public function to_change_bg() {
            if(!session('user.uid')) echoResult(-7);

            $bid = I('bid', 1);
            $uid = session('user.uid');

            if(!in_array($bid, array(1,2,3,4))) $bid = 1;
            if(M('user')->save(array('id'=>$uid, 'bg_pic'=>$bid)) !== false) {
                die(json_encode(array('code'=>0)));
            }else{
                die(json_encode(array('code'=>-1)));
            }
        }

        /**
        *个人中心粉丝
        */
        public function personal(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            $msg = array(
                "typeid"=>1,
            );
            $this->assign("res", $msg);

            $fans=M('fans')->alias('a')->field('a.fans_id,u.*')
            ->join('left join cos_user u ON u.id=a.fans_id')
            ->where(array('a.uid'=>session('user.uid')))
            ->order('id desc')
            ->select();
            if(empty($fans)){
                $fans=0;
            }else{
                foreach($fans as $key => $fan){
                    $arts = M('original_article')->field('cover_curimg')->where(array('uid'=>$fan['fans_id'],'enabled'=>2))->limit(0,3)->select();
                    $fans[$key]['cover_curimg'] = $arts;

                    $fans_fans = M('fans')->where(array('uid'=>$fan['fans_id']))->count();
                    $fans[$key]['fans_count'] = $fans_fans;

                    if($fan['province']>534 && $fan['province']<567){
                        $locations = M('locations');
                        $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                        $address = $province;
                        $fans[$key]['address'] = $address;
                    }elseif($fan['province'] == 0){
                        $fans[$key]['address'] = "未设置";
                    }else{
                        if($fan['city'] == 0){
                            $locations = M('locations');
                            $province = $locations->where(array('id'=>$fan['province']))->getField('name'); 
                            $address = $province;
                            $fans[$key]['address'] = $address;
                        }elseif($fan['area'] == 0){
                            $locations = M('locations');
                            $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                            $city = $locations->where(array('id'=>$fan['city']))->getField('name');
                            $address = $province."&#8197/&#8197".$city;
                            $fans[$key]['address'] = $address;
                        }else{
                            $locations = M('locations');
                            $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                            $city = $locations->where(array('id'=>$fan['city']))->getField('name');
                            $area = $locations->where(array('id'=>$fan['area']))->getField('name');
                            $address = $province."&#8197/&#8197".$city."&#8197/&#8197".$area;
                            $fans[$key]['address'] = $address; 
                        }
                        
                    }
                }
                
                foreach ($fans as $key => $fanss) {
                    $res = M('fans')->where(array('uid'=>$fanss['id']))->getfield('fans_id',true);
                    if (in_array(session('user.uid'), $res)) {
                        $fans[$key]['follow_type'] = 1;
                    }else{
                        $fans[$key]['follow_type'] = 0;
                    }
                }
            }
            // P($fans);die;
            $this->assign('fans',$fans);
            $this->com_dataModel();//查询当前用户的关注和粉丝数以及最新10个粉丝的头像和昵称
            $this->display();
        }

        /**
        *个人中心收藏
        */
        public function personal_collect(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            $msg = array(
               "typeid"=>2,
            );
            $this->assign("res", $msg);
            $uid = session('user.uid');
            $collection_table = M('collection_table');
            $collection_name = $collection_table->where(array('uid'=>$uid))->select();

            if(empty($collection_name)) $collection_name=array();
            foreach( $collection_name as $v => $collection_names ){
                $table_id =  $collection_names['id'];
                $works_id=M('collection')->where(array('collection_table_id'=>$table_id))->limit(1)->order('id desc')->select();
                
                if($works_id[0]['collection_type'] == 1){
                    $url = M('fine_articles')->where(array('id'=>$works_id[0]['works_id'],'enabled'=>1))->getField('cover_img');
                }elseif($works_id[0]['collection_type'] == 2){
                    $url = M('original_article')->where(array('id'=>$works_id[0]['works_id'],'enabled'=>1))->getField('cover_img');
                }else{
                    $url = M('activity_articles')->where(array('id'=>$works_id[0]['works_id'],'enabled'=>1))->getField('cover_img');
                }
                //获得收藏夹第一个作品的封面作为封面
                $collection_name[$v]['cover_img'] = $url;

                $num = M('collection')->where(array('collection_table_id'=>$table_id))->count();
                $collection_name[$v]['num'] = $num;
            }
            $this->assign('collection_name',$collection_name);
            $names = $collection_table->where(array('uid'=>$uid))->select();
            $this->assign('names',$names);//当前用户的收藏夹名

            $this->com_dataModel();//查询当前用户的关注和粉丝数以及最新10个粉丝的头像和昵称   
            $this->display();
        }

        /**
        * 添加收藏夹,修改收藏夹
        * $id 表示收藏夹的id,如果是新增不传id,如果是修改则需要传递id
        * $collect_name 表示收藏夹名字
        * $img 表示收藏夹的封面图片
        *
        */
        public function collect_add(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $id=I('post.id');
            $json = array();
            $col_name=I('post.name');
            //判断是否已经有同名了
            if(M('collection_table')->where(array('collection_table_name'=>$col_name,'uid'=>session('user.uid')))->find()) {
                echoDataResult(-107,$names); 
            }

            $data=array(
                'collection_table_name'=>$col_name,
                'uid'=>session('user.uid'),
                'createtime'=>time()
            );

            if(!$id){
                $cid=M('collection_table')->data($data)->add();
                if(!$cid){
                    $code=-1;
                    $msg='新增数据保存不成功';
                }else{
                    $code=0;
                    $id = $cid;
                    $json['id'] = $cid;
                    $msg='新增成功';
                }
            }else{
                $res=M('collection_table')->where(array('id'=>$id))->save(array( 'collection_table_name'=>$col_name));
                if($res===false){
                    $code=-2;
                    $msg='修改数据保存不成功';
                }else{
                    $code=0;
                    $id = $cid;
                    $json['id'] = $res;
                    $msg='修改成功';
                }
            }
            $json['code'] = $code;
            $json['msg'] = $msg;
            $this->ajaxReturn($json,'json');
        }

        /**
        *个人中心删除收藏夹
        */
        public function delete_collection(){
             if(!session('user.uid')){
                echoResult(-7);
            }

            $uid = session('user.uid');
            $cid = I('id');
            M('collection')->where(array('collection_table_id'=>$cid))->delete();
            $collection_table = M('collection_table');
            $res = $collection_table->where(array('uid'=>$uid,'id'=>$cid))->delete();
            $code = 0;
            if($res){
                $code = 106;
            }else{
                $code = -106;
            }
            echoResult($code);
        }
        /**
        *个人中心打开收藏夹
        *展现收藏夹内作品
        */
        public function open_collection(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $cid = I('post.id');//获取传递来的收藏夹的id
            // $cid = 128;
            $collection_type = I('post.type',1);
            if($collection_type == 0){
                $infofile = M('collection_table')->where(array('id'=>$cid))->select();
                $info[0]['cid'] = $info[0]['id'];
                $info['file'] = $infofile;
                $code = -25;
            }
            if($collection_type == 1){
                $info=M('collection')->alias('a')->field('a.works_id,o.collection_table_name')
                    ->join('left join cos_collection_table o ON o.id=a.collection_table_id')
                    ->where(array('a.collection_table_id'=>$cid,'collection_type'=>$collection_type))->select();
                    foreach($info as $v =>$infos){
                        $fine_articles = M('fine_articles');
                        $a = $infos['works_id'];
                        $info[$v]['id'] = $a;
                        $fine_1 = $fine_articles->where(array('id'=>$a,'enabled'=>1))->getField('title'); 
                        $info[$v]['title'] = $fine_1;
                        $fine_2 =  $fine_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('cover_img');
                        $fine_2 = explode(',', $fine_2);
						$info[$v]['cover_img'] = $fine_2[0];
                        $fine_3 =  $fine_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('intro');
                        $info[$v]['intro'] = $fine_3;
                        $info[$v]['cid'] = $cid;
                        $info[$v]['url'] = U('Choiceness/choice_details');
                        $c_id =  $fine_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('category_id');
                        $pid = M('category')->where(array('id'=>$c_id))->getfield('pid');
                        if($pid == 0){
                            $info[$v]['category'] = M('category')->where(array('id'=>$c_id))->getfield('cname');
                        }else{
                            $info[$v]['category'] = M('category')->where(array('id'=>$pid))->getfield('cname');
                        }
                    }
                    $code = -25; 
            }
            if($collection_type == 2){
                $info=M('collection')->alias('a')->field('a.works_id,o.collection_table_name')
                    ->join('left join cos_collection_table o ON o.id=a.collection_table_id')
                    ->where(array('a.collection_table_id'=>$cid,'collection_type'=>$collection_type))->select();
                
				foreach($info as $v =>$infos){
                    $original_article = M('original_article');
                    $a = $infos['works_id'];
                    $us_in = $original_article->where(array('id'=>$infos['works_id']))->find();
                    
					$info[$v]['id'] = $a;
                    $original_1 = $original_article->where(array('id'=>$a))->getField('title'); 
                    $info[$v]['title'] = $original_1;
                    $original_2 =  $original_article->where(array('id'=>$infos['works_id']))->getfield('cover_img');
                    $original_2 = explode(',', $original_2);
					$info[$v]['cover_img'] = $original_2[0];
                    $original_3 =  $original_article->where(array('id'=>$infos['works_id']))->getfield('intro');
                    $info[$v]['intro'] = $original_3;
                    $info[$v]['cid'] = $cid;
                    $info[$v]['url'] = U('Original/original_detail1');
                    $info[$v]['zuozhe'] = M('user')->where(array('id'=>$us_in['uid']))->getField('nickname');
                    $c_id =  $original_article->where(array('id'=>$infos['works_id']))->getfield('cid');
                    $pid = M('category')->where(array('id'=>$c_id))->getfield('pid');
                    if($pid == 0){
                        $info[$v]['p_category'] = '';
                        $info[$v]['category'] = M('category')->where(array('id'=>$c_id))->getfield('cname');
                    }else{
                        $info[$v]['p_category'] = M('category')->where(array('id'=>$pid))->getfield('cname');
                        $info[$v]['category'] = M('category')->where(array('id'=>$c_id))->getfield('cname');
                    }
                }
                $code = -25;
            }
            if($collection_type == 3){
                $info=M('collection')->alias('a')->field('a.works_id,o.collection_table_name')
                    ->join('left join cos_collection_table o ON o.id=a.collection_table_id')
                    ->where(array('a.collection_table_id'=>$cid,'collection_type'=>$collection_type))->select();
                foreach($info as $v =>$infos){
                    $activity_articles = M('activity_articles');
                    $a = $infos['works_id'];
                    $info[$v]['id'] = $a;
                    $activity_1 = $activity_articles->where(array('id'=>$a,'enabled'=>1))->getField('title'); 
                    $info[$v]['title'] = $activity_1;
                    $activity_2 =  $activity_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('cover_img');
                    $activity_2 = explode(',', $activity_2);
					$info[$v]['cover_img'] = $activity_2[0];
                    $activity_3 =  $activity_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('intro');
                    $info[$v]['intro'] = $activity_3;
                    $info[$v]['cid'] = $cid;
                    $info[$v]['url'] = U('Activity/active_detail');
                    $c_id =  $activity_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('category_id');
                    $info[$v]['city_detail'] = $activity_articles->where(array('id'=>$infos['works_id'],'enabled'=>1))->getfield('city_detail');
                    $pid = M('category')->where(array('id'=>$c_id))->getfield('pid');
                    if($pid == 0){
                        $info[$v]['category'] = M('category')->where(array('id'=>$c_id))->getfield('cname');
                    }else{
                        $info[$v]['category'] = M('category')->where(array('id'=>$pid))->getfield('cname');
                    }
                }
                $code = -25;
            }
            
			
            echoDataResult($code,$info);
        }
        /**
        *个人中心收藏夹
        *转移收藏夹内作品
        */
        public function change_collection(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $cid = I('cid');
            $wid = I('wid');
            $type = I('type');
            $collection_table = M('collection');
            $data['collection_table_id'] = $cid;
            $res = $collection_table->where(array('works_id'=>$wid,'collection_type'=>$type))->save($data);
            if($res){
                $code = -35;
            }else{
                $code = -36;
            }
            echoResult($code);
        }
        /**
        *个人中心收藏夹
        *删除收藏夹内作品
        */
        public function del_collection(){
             if(!session('user.uid')){
                echoResult(-7);
            }

            $cid = I('cid');
            $wid = I('wid');
            $type = I('type');
            $collection = M('collection');
            $res = $collection->where(array('collection_table_id'=>$cid,'works_id'=>$wid,'collection_type'=>$type))->delete();
            
            if($res){
                $code = 106;
            }else{
                $code = -106;
            }
            echoResult($code);
        }

        /**
        *个人中心投稿
        */
        public function personal_contribute(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $msg = array(
                "typeid"=>3,
            );
            $this->assign("res", $msg);

            $this->com_dataModel();//查询当前用户的关注和粉丝数以及最新10个粉丝的头像和昵称

            $this->display();
        }
        /**
        *个人中心关注
        */
        public function personal_follow(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $msg = array(
               "typeid"=>4,
            );
            $uid = session("user.uid");
            $this->assign("res", $msg);

            $fans=M('fans')->alias('a')->field('a.uid,u.*')
                ->join('left join cos_user u ON u.id=a.uid')
                ->where(array('fans_id'=>session('user.uid')))
                ->order('id desc')
                ->select();
            if(empty($fans)){
                $fans=0;
            }else{
                foreach($fans as $key=> $fan){
                    $arts=M('original_article')->field('cover_curimg')->where(array('uid'=>$fan['uid'],'enabled'=>2))->limit(0,3)->select();
                    $fans[$key]['cover_curimg']=$arts;

                    $fans_fans = M('fans')->where(array('uid'=>$fan['uid']))->count();
                    $fans[$key]['fans_count'] = $fans_fans;

                    if(534<$fan['province'] && $fan['province']<567){
                        $locations = M('locations');
                        $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                        $address = $province;
                        $fans[$key]['address'] = $address;
                    }elseif ($fan['province'] == 0){
                        $fans[$key]['address'] = "未设置";
                    }elseif($fan['city'] == 0){
                        $locations = M('locations');
                        $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                        $address = $province;
                        $fans[$key]['address'] = $address;
                    }elseif ($fan['area'] == 0) {
                        $locations = M('locations');
                        $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                        $city = $locations->where(array('id'=>$fan['city']))->getField('name');
                        $address = $province."&#8197;/&#8197;".$city;
                        $fans[$key]['address'] = $address;
                    }else{
                        $locations = M('locations');
                        $province = $locations->where(array('id'=>$fan['province']))->getField('name');
                        $city = $locations->where(array('id'=>$fan['city']))->getField('name');
                        $area = $locations->where(array('id'=>$fan['area']))->getField('name');
                        $address = $province."&#8197;/&#8197;".$city."&#8197;/&#8197;".$area;
                        $fans[$key]['address'] = $address;
                    }
                    //我是否关注了我的粉丝
                    if(M("fans")->where(array("fans_id"=>$uid))->find()){
                        $fans[$key]['is_follow'] = 0;
                    }else{
                        $fans[$key]['is_follow'] = 1;
                    }
                } 
            }
            // p($fans);die;
            $this->assign('fans',$fans);
            $this->com_dataModel();//查询当前用户的关注和粉丝数以及最新10个粉丝的头像和昵称

            $this->display();
        }
        /**
        *个人中心发布
        */
        public function personal_issuance(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $uid = session('user.uid');
			
            $original = M("original_article");
            $res = $original->where(array("uid"=>$uid,"enabled"=>2))->order("createtime desc")->select();
            $bqres = M("versionblg")->select();
            $flres = M("category")->select();
            if(!$res) $res = array();
            $msg["typeid"] = 5;
            
			$this->assign("ores", $res);
            $this->assign("bqres", $bqres);
            $this->assign("flres", $flres);
            $this->assign("res", $msg);

            $this->com_dataModel();//查询当前用户的关注和粉丝数以及最新10个粉丝的头像和昵称

            $this->display();
        }
        /**
        *个人中心 二级分类切换
        */
        public function personal_issuance_flcat(){
             if(!session('user.uid')){
                echoResult(-7);
            }

            $id = I('post.id');
            $res = M("category")->where(array("pid"=>$id))->select();
            if(!$res) $res = array();
            echoDataResult(0,$res);
        }
        /**
        *个人中心发布
        */
        public function create_orginMsg(){
             if(!session('user.uid')){
                echoResult(-7);
            }

            $uid = session('user.uid');//当前用户id
            $tid = I("post.tid",'');//1表示新增2表示修改
            $zpid=I("post.zpid",'');
            $title = I("post.title",'');
            $cover_img=I("post.cover_img",array());
            $cover_curimg=I("post.cover_curimg",'');
            $tname=I("post.tname",array());
            $cnt=I("post.cnt",array());
            $cid=I("post.cid",'');
            $bqid=I("post.bqid",'');
            $original = M("original_article");
            $code = 0;
            if(!$tid) echoResult(-30000);
            if(!$uid) echoResult(-7);
            $data = array(
                "title"=>$title,
                "uid"=>$uid,
                "cover_img"=>implode(",",$cover_img),
                "cover_curimg"=>$cover_curimg,
                "cnt" => implode(",",$cnt),
                "cid" => $cid,
                "bqid" => $bqid,
                "createtime" => time(),
            );
            if($tid == 1){
                $id = $original->data($data)->add();
                if($id){
                    foreach ($tname as $key => $value) {
                         $data = array();
                         $data["tname"] = $value;
                         $data["aid"] = $uid;
                         $ids =  M("original_tags")->data($data)->add();
                         if($ids){
                             $data2["original_id"] = $id;
                             $data2["tid"] = $ids;
                             M("original_article_tags")->data($data2)->add();
                         }else{
                            $code = -1;
                         }
                    }
                }else{
                     $code = -1;
                }
            }else{

                $id = $original->where(array("id"=>$zpid))->save($data);
                if($id){
                     $tid = M("original_article_tags")->where(array("original_id"=>$zpid))->select();
                     M("original_article_tags")->where(array("original_id"=>$zpid))->delete();
                     foreach ($tid as $key => $values) {
                         M("original_tags")->where(array("id"=>$values["tid"]))->delete();
                     }
                    foreach ($tname as $key => $value) {
                         $data = array();
                         $data["tname"] = $value;
                         $data["aid"] = $uid;
                         $ids =  M("original_tags")->data($data)->add();
                         if($ids){
                             $data2["original_id"] = $zpid;
                             $data2["tid"] = $ids;
                             M("original_article_tags")->data($data2)->add();
                         }else{
                            $code = -1;
                         }
                    }
                }else{
                     $code = -1;
                }
            }
            echoResult($code);
        }
        /**
        *个人中心 原创作品删除
        */
        public function orginnal_del(){
             if(!session('user.uid')){
                echoResult(-7);
            }

            $id = I('post.id',"");
            if(!$id) echoResult(-30000);
            $id = M("original_article")->where(array("id"=>$id))->setField(array("enabled"=>0));
            if($id){
                echoResult(0);
            }else{
                echoResult(-1);
            }
        }
        /**编辑原创**/
        public function update_orginnal(){
            $uid = session('user.uid');
             if(!$uid){
                echoResult(-7);
            }
            $id = I("post.id","");
            if(!$id) echoResult(-30000);
            $res = M("original_article")->where(array("id"=>$id))->find();
            $tagid = M("original_article_tags")->where(array("original_id"=>$id))->select();
            $ejfl = M("category")->where(array("id"=>$res['cid']))->find();
            $yjfl = M("category")->where(array("id"=>$ejfl['pid']))->find();
            $res["cnt"] = explode(",",$res["cnt"]);
            $res["yjfl"] = $yjfl["id"];
            $res["ejfl"] = $ejfl["id"];
            $res["yjname"] = $yjfl["cname"];
            $res["evals"] = $ejfl["cname"];
            $My_array=array();
            $My_tag_array=array();
            if($res){
                foreach ($tagid as $key => $value) {
                     $tag = M("original_tags")->where(array("id"=>$value["tid"]))->find();
                     $My_tag_array[] = $tag["tname"];
                }
                $res["tags"] = $My_tag_array;
                foreach ($res["cnt"] as $key => $value) {
                    $My_array[] = html_entity_decode($value);
                }
                $res["cnt"] = $My_array;
                echoDataResult(0,$res);
            }else{
                echoResult(-1);
            }
        }
        /****
        *上传原创图片
        *
        ******/
        public function original_upimg($path = '/original/Cover/'){
            $set['path'] = $path;
            $img = new \Common\Api\ImgApi();
            $json = $img->upload($set);
            if($json['code'] == 0){
                unset($json['realpath']);
            }
            $json['msg'] = getErrArr($json['code']);
            echo json_encode($json);
        }
        
        //异步修改密码
        public function re_set_password() {
            $o_pwd = I('o_pwd', '');      //旧密码
            $n_pwd = I('n_pwd', '');      //新密码
            $uid = session('user.uid');  //用户id

            $password = M('user')->where(array('id'=>$uid))->getField('password');
            if($password != encrypt_password($o_pwd)) die(json_encode(array('code'=>-1, 'msg'=>'原始密码错误')));
            if(M('user')->save(array('id'=>$uid, 'password'=>encrypt_password($n_pwd))) !== false) {
                die(json_encode(array('code'=>0, 'msg'=>'成功')));
            }else{
                die(json_encode(array('code'=>-2, 'msg'=>'系统错误')));
            }
        }

        /**
        *个人中心设置
        */
        public function personal_set(){
            $uid = session('user.uid');
             if(!$uid){
                echoResult(-7);
            }
            //3、获取所有省
            $lModel = D('locations');
            $pro_lst = $lModel->get_son_lst(0);
            //当前用户所在的用户中心分类id栏目id 
            $msg = array(
                "typeid"=>7,
            );
            if($uid){
                $user = M('user');
                $info= $user->where(array('id'=>$uid))->find();
                //>>  获取城市列表
                $city_lst = $lModel->get_son_lst($info['province']);
                //>>  获取区/县列表
                $area_lst = $lModel->get_son_lst($info['city']);
                $province = '';
                $city = '';
                $area = '';
                foreach ($pro_lst as $key => $value) {
                    if($value["id"] == $info['province']){
                        $province = $value["name"]."/";
                    }
                }
                foreach ($city_lst as $key => $value) {
                    if($value["id"] == $info['city']){
                        $city = $value["name"]."/";
                    }
                }
                foreach ($area_lst as $key => $value) {
                    if($value["id"] == $info['area']){
                        $area = $value["name"];
                    }
                }
                $info["pdaddress"] = $province.$city.$area;
            }
            $is_renzheng = M('sfz_info')->where(array('uid'=>$uid))->getField('enabled');
            if(empty($is_renzheng) || $is_renzheng == 0){
                $info["is_renzheng"] = 0;
            }elseif($is_renzheng == 1){
                $info["is_renzheng"] = 1;
            }elseif($is_renzheng == 2){
                $info["is_renzheng"] = 2;
                $info["renzheng_name"] = M('sfz_info')->where(array('uid'=>$uid))->getField('true_name');
            }
            $this->com_dataModel();//查询当前用户的关注和粉丝数以及最新10个粉丝的头像和昵称

			//组织机构--查询机构内的组员
			$m_list = array();
			if($info['type'] == 2){
				$m_list = M('user_group')->alias('a')
                ->field('b.industry,b.sex,b.id,b.nickname,b.avatar,c.name pname,d.name cname,e.name anme')
                ->join('left join cos_user b on a.uid=b.id')
                ->join('left join cos_locations c on b.province=c.id')
                ->join('left join cos_locations d on b.city=d.id')
                ->join('left join cos_locations e on b.area=e.id')
                ->where(array("a.mid"=>$info['id']))
                ->order('b.id desc')
                ->select();
			}else{
                //参加了哪些组织
                $group_lst = M('user_group')->alias('a')
                                            ->field('b.id,b.nickname')
                                            ->join('cos_user b ON a.mid=b.id')
                                            ->where(array('a.uid'=>$uid))
                                            ->select();
                if(!$group_lst) $group_lst = array();
                
                $this->assign('group_lst', $group_lst);
            }
            
            if($m_list) {
                foreach ($m_list as $k => &$v) {
                    if($v['industry']) $v['industry'] = str_replace(",","&#8197;/&#8197;",$v['industry']);
                    if($v['pname']) {
                        $v['address'] = $v['pname'];
                    }else{
                        $v['address'] = '未设置';
                    }
                    if($v['cname']) $v['address'] .= '/'.$v['cname'];
                    if($v['anme']) $v['address'] .= '/'.$v['anme'];
                    $v['fans'] = M('fans')->where(array('uid'=>$v['id']))->count();
                    if(!$v['fans']) $v['fans'] = 0;
                }
            }
            
			$this->assign('m_list', $m_list);
			
			$info['user_email'] = $info['email'] ? substr($info['email'], 0, 2).'****'.substr($info['email'], -8, 8) : '';
			
            if($info["industry"]) $tags = explode(",",$info["industry"]);

            $this->assign('pro_lst', $pro_lst);
            $this->assign('city_lst', $city_lst);
            $this->assign('area_lst', $area_lst);
            $this->assign('tags',$tags);
            $this->assign("res", $msg);
            $this->assign('user_info',$info);
            //P($info);die;
            $this->display();
        }

        private function com_dataModel(){
            $uid = session('user.uid');
            $fans = M('fans');
            $userCount = $fans->where(array('uid'=>$uid))->count();
            $fansCount = $fans->where(array('fans_id'=>$uid))->count();
            $this->assign('userCount',$userCount);
            $this->assign('fansCount',$fansCount);
            $fans_info = $fans->alias('a')
                 ->field('b.nickname,b.avatar,b.id,b.type')
                 ->join('left join cos_user b on a.fans_id=b.id')
                 ->where(array("a.uid"=>$uid))
                 ->order('b.id desc')
                 ->limit(10)
                 ->select();
            $this->assign('fans_info',$fans_info);//查询出最新10个粉丝头像和昵称
        }
        /**
         * 异步获取地区信息
         * @return [type] [description]
         */
        public function ajax_get_area_son($pid) {
            $lst = D('locations')->get_son_lst($pid);
            die(json_encode(array('code'=>0, 'data'=>$lst)));
        }
        /*
            个人中心资料修改    
        */
        public function personal_revise(){
            $uid = session('user.uid');
            if(!$uid){
                echoResult(-7);
            }
            $sex = I('post.sex','');
            $province = I('post.province','');
            $city = I('post.city','');
            $industry = I('post.industry',array());
            $area = I('post.area','');
            $synopsis = I('post.synopsis','');
            $nickname = I('nickname', '');
            if($sex == "") echoResult(-30000);
            $data = array(
                'sex' => $sex,
                'province' => $province,
                'city' => $city,
                'industry' => implode( ",",$industry),
                'area' => $area,
                'synopsis' => $synopsis,
            );

            if($nickname) {
                if(M('user')->where(array('nickname'=>$nickname))->find()){
                    echoResult(-55);
                }else{
                    $data['nickname'] = $nickname;
                }
            }

            $user = M('user');
            $result = $user->where(array("id"=>$uid))->save($data);

            if($result !== false){
                $code = 0;
                sessionUserInfoUpdate();
            }else{
                $code = -1;
            }
            echoResult($code);
        }
        //个人认证身份证正面上传
        public function sfz_face_upimg($path = '/Activity/Cover/'){
            $set['path'] = $path;
            $img = new \Common\Api\ImgApi();
            $json = $img->upload($set);
            if($json['code'] == 0){
                session('sfz_face',$json['url']); 
            }
            $json['msg'] = getErrArr($json['code']);
            echo json_encode($json);
        }
        //个人认证身份证背面上传
        public function sfz_back_upimg($path = '/Activity/Cover/'){
            $set['path'] = $path;
            $img = new \Common\Api\ImgApi();
            $json = $img->upload($set);
            if($json['code'] == 0){
                session('sfz_back',$json['url']);
            }
            $json['msg'] = getErrArr($json['code']);
            echo json_encode($json);
        }

        //用户头像修改
        public function user_upimg($path = '/Activity/Cover/'){
            $set['path'] = $path;
            $img = new \Common\Api\ImgApi();
            $json = $img->upload($set);
            if($json['code'] == 0){
                unset($json['realpath']);
            }
            $json['msg'] = getErrArr($json['code']);
            echo json_encode($json);
        }
        //用户头像修改
        public function user_upimg_update(){
            $url = I("post.txurl","");
            $res = M("user")->where(array("id"=>session("user.uid")))->save(array("avatar"=>$url));
            if($res){
               sessionUserInfoUpdate();
               echoResult(0);
            }
            echoResult(-1);
        }
        //用户头像修改
        public function base64imgsave_Upload($img=''){
            $json = base64imgsave($img);
            echo json_encode($json);
        }
        //用户头像框修改
        public function userback_upimg($path = '/Activity/Cover/'){
            $set['path'] = $path;
            $img = new \Common\Api\ImgApi();
            $json = $img->upload($set);
            if($json['code'] == 0){
                unset($json['realpath']);
                M("user")->where(array("id"=>session("user.uid")))->save(array("avatar_back"=>$json["url"]));
            }
            $json['msg'] = getErrArr($json['code']);
            echo json_encode($json);
        }
        /*
            个人中心申请认证    
        */
        public function authentication(){
            if(!session('user.uid')){
                echoResult(-7);
            }

            $true_name = I('post.true_name');
            $phone = I('post.phone');
            $email = I('post.email');
            $sfz_face = session('sfz_face');
            $sfz_back = session('sfz_back');
            $data = array(
                'uid' => session('user.uid'),
                'true_name' => $true_name,
                'phone' => $phone,
                'email' => $email,
                'sfz_face' => $sfz_face,
                'sfz_back' => $sfz_back,
                'enabled' => 1,
            );
            $user = M('sfz_info');
            $result = $user->add($data);
            if($result){
                $code = -37;
            }else{
                $code = -38;
            }
            echoResult($code);
        }

        /*
            个人中心,修改密码
            这里写的是md5算法加密的.
        */
        public function password_set(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            
            $old_psd = I('old_psd');
            $old_password = md5($old_psd);
            $new_psd = I('new_psd');
            $new_password = md5($new_psd);

            $uid = session('user.uid');
            $code = 0;
            $user = M('user');
            $res = $user->where(array('id'=>$uid))->getfield('password');
            if($old_password == $res){

                $date = array();
                $date['password'] = $new_password;  
                $date['id'] = $uid;  
                
                $psd = $user->save($date);
                if($psd){
                    $code = -39;
                }else{
                    $code = -40;
                }
            }else{
                $code = -15;
            }
            echoResult($code);

        }
		
		//修改邮箱----发送邮件
		public function send_email_new(){
			$email = I('email', '');
			$code = I('code', '');
			
			//1、验证验证码
			$Verify = new \Think\Verify();
			$res = $Verify->check($code);
			if(!$res) echoResult(-2);
			
			// 2、为空验证
			if($email == '')  jsonReturn(-1);
			
			//3、正则验证
			$pattern = "/\w+[@]{1}\w+[.]\w+/";
			if(!preg_match( $pattern, $email)) jsonReturn(-2);
			
			//4、邮箱是否注册过
			$info = M('User')->field('id,nickname,email')->where(array('email'=>$email))->find();
			if(!$info){//验证通过
				//4、发送邮件
				D('User')->send_edit_email($email);
				
				
				$email_arr = explode('@', $email);
				$rst = array('email'=>$email, 'url'=>'http://mail.'.$email_arr[1]);
				
				jsonReturn(0, $rst);
				
			} else {
				jsonReturn(-3);
			}
			
		}
		
		
		
		


    //////////////////////////////////////////*************消息开始***************////////////////////////////////////////////////

    /**
     * 获取系统消息
     * 根据用户获取用户
     */
    public function personal_news(){
        if(!session('user.uid')) echoResult(-7);
        $msg = array(
               "typeid"=>6,
            );
            $this->assign("res", $msg);
        //1、接收参数
        $m = D('Msg');
        $page = I('page', 1, 'intval');
        $uid = session('user.uid');
        $s_type = I('s_type', 0, 'intval');    //0、系统消息和评论回复  1、系统消息  2、评论回复
        $r_type = I('r_type', 0, 'intval');    //0、返回数据和页面  1、返回页面

        if($s_type == 0) {
            //1、获取系统消息
            $data['system']= M('system_message')->where(array('uid'=>$uid))->order('id desc')->page($page,10)->select();

            //2、获取评论消息
            $data['com'] = $m->get_user_selfmsg($uid, $page); 
        }else if($s_type == 1){
            $data['system']= M('system_message')->where(array('uid'=>$uid))->order('id desc')->page($page,10)->select();
        }else if($s_type == 2) {
            $data['com'] = $m->get_user_selfmsg($uid, $page); 
        }

        
        if($r_type == 0) {
            $this->com_dataModel();
            $this->assign('smsg',$data);
            $this->display();
        }else{
            if($data['com']) {
                foreach ($data['com'] as $k => $v) {
                    $data['com'][$k]['createtime'] = date('Y.m.d', $v['createtime']);
                    foreach ($v['son_lst'] as $key => $value) {
                        $data['com'][$k]['son_lst'][$key]['createtime'] = date('Y.m.d', $value['createtime']);
                    }
                }
            }

            if($data['system']) {
                foreach ($data['system'] as $k => $v) {
                    $data['system'][$k]['createtime'] = date('Y.m.d', $v['createtime']);
                }
            }
            die(json_encode(array('data'=>$data)));
        }
    }
    /*
        评论点赞
    */
    public function praise_comment(){
        $id = I('id', 0, 'intval');
        $uid = session('user.uid');

        //取消点赞
        if(M('comment_praise')->where(array('cid'=>$id, 'uid'=>$uid))->find()) {
            M('comment_praise')->where(array('cid'=>$id, 'uid'=>$uid))->delete();
            M('comment')->where(array('id'=>$id))->setDec('praise_nums');
            echoResult(2);
        //点赞
        }else{
            $data = array(
                'cid' => $id,
                'uid' => $uid,
                'createtime' => time()
            );

            M('comment_praise')->add($data);
            M('comment')->where(array('id'=>$id))->setInc('praise_nums');
            echoResult(1);
        }
    }


    /**
     * 评论回复
     * $zxid 表示评论的最初级id
     * $puid 表示评论的父级id
     * $uid 表示评论人id
     * $tuid 表示被评论人的id
     * $coment 表示评论内容
     * 在执行评论的时候 父级评论的数量+1 并且评论的爷爷辈也需要+1
     */
    public function comment_add(){   
        $m= M('comment');

        //1、接收参数
        $pid = I('pid');
        $to_uid = I('to_uid');
        $zx_pid = I('zx_pid');
        $cnt = I('cnt');
        $c=$m->where(array('id'=>$pid))->find();

        //2、数据
        $data = array(
            'aid'          =>   $c['aid'],
            'type'         =>   $c['type'],
            'uid'          =>   session('user.uid'),
            'to_uid'       =>   $to_uid,
            'pid'          =>   $pid,
            'zx_pid'       =>   $zx_pid ? $zx_pid : $pid,
            'cnt'          =>   $cnt,
            'createtime'   =>   time()
            );

        //3、入库
        $id = $m->add($data);
        $data['id'] = $id;
        $m->where(array('id'=>$pid))->setInc('praise_nums');
        if($zx_pid) $m->where(array('id'=>$zx_pid))->setInc('praise_nums');

        //4、获取返回信息
        $my = M('user')->field('nickname,avatar,type')->where(array('id'=>$data['uid']))->find();
        $data['nickname'] = $my['nickname'];
        $data['avatar'] = set_header($my['avatar']);
        $data['user_type'] = $my['type'];
        $data['to_nickname'] = M('user')->where(array('id'=>$data['to_uid']))->getField('nickname');
        $data['c_time'] = date('y.m.d', $data['createtime']);

        die(json_encode(array('code'=>0, 'data'=>$data)));
    }



    /**
     * 删除系统消息
     * $id 消息id
     */

    public function del_system_msg()
    {
        $id=I('id');

        if(empty($id)){
            echoResult(-12);
        }

        $model=M("system_message");

        $r=$model->where(array('id'=>$id))->delete();

        if($r){
            $code=106;
        }else{
            $code=-106;
        }

        echoResult($code);

    }

    public function to_out_bind(){
        if(!session('user.uid')) echoResult(-7);

        $type = I('type', 1);
        $uid=session('user.uid');

        if($type == 1) {
            $data['qq_id'] = '';
        }else{
            $data['weibo_id'] = '';
        }

        if(M('user')->where(array('id'=>$uid))->save($data) !== false) {
            echoResult(0);
        }else{
            echoResult(-1);
        }
    }

    
    /**
     * 获取消息评论列表
     * $parent_id  int  父级id
     */

    protected function get_comment_msg($parent_id='0',$page='1',$page_size='10')
    {
        $uid=session('user.uid');
            //获取别人评论我的原创作品
            $sql1="SELECT a.*,b.nickname,b.avatar,c.title FROM cos_comment a left join cos_user b ON b.id=a.to_uid left join cos_original_article c ON c.id= a.aid  WHERE a.zx_pid = ".$parent_id." AND a.to_uid = ".$uid." AND a.type=2";

            //获取我评论别人的原创作品
            $sql2="SELECT a.*,b.nickname,b.avatar,c.title FROM cos_comment a left join cos_user b ON b.id=a.uid left join cos_original_article c ON c.id= a.aid  WHERE a.zx_pid = ".$parent_id." AND a.uid=".$uid." AND a.type=2";

            //获取我评论活动文章的评论内容
            $sql3="SELECT a.*,b.nickname,b.avatar,c.title FROM cos_comment a left join cos_user b ON b.id=a.uid left join cos_activity_articles c ON c.id= a.aid WHERE a.zx_pid = ".$parent_id." AND a.uid =".$uid." AND a.type=3";

        $sql="($sql1) UNION ALL ($sql2) UNION ($sql3) ORDER by createtime desc limit ".(($page-1)*$page_size).",{$page_size}";

        $arr=M()->query($sql);
        // P($arr);die;
        if(empty($arr)){
            return array();
        }
        $result=array();
        foreach($arr as $val){
            $val['comment']=M('comment')->alias('a')
                ->field('a.*,b.nickname toname,b.avatar toavatar,c.nickname,c.avatar')
                ->join('left join cos_user b ON b.id=a.to_uid')
                ->join('left join cos_user c ON c.id=a.uid')
                ->where(array('zx_pid'=>$val['id']))->order('createtime desc')->limit(2)->select();
            $result[]=$val;
        }
        
		//P($result);exit;
		
		foreach($result as $k => $v){
			foreach($v['comment'] as $key => $val){
				$res = M('comment_praise')->where(array('cid'=>$val['id'],'uid'=>$uid))->select();
				if($res){
					$result[$k]['comment'][$key]['is_praise'] = 1;
					
				}else{
					$result[$k]['comment'][$key]['is_praise'] = 0;
				}
			}
			
		}
		//P($result);exit;
        return $result;
    }






    /////////////////////////////////////////*****************消息结束*********************/////////////////////////////



    ////////////////////////////////////////////************认证中心开始*************//////////////////////////////////////////







    ///////////////////////////////////////////******************认证中心结束*******************///////////////////////////////////





}
