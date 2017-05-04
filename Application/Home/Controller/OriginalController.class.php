<?php
namespace Home\Controller;
use Think\Controller;
class OriginalController extends Controller {
    /****原创页面首页(默认按照收藏数多少排序)*******/
    public function original(){
        //1、接收参数
        $category_id = I('type', 0);    //分类
        $page = I('p', 1);              //页数
        $num = I('n', 16);              //每页显示条数
        $r_type = I('r_type', 0);       //数据返回方式  0、page   1、json
        $o = I('o', 0);                 //排序方式 0点赞数最多 1最新 2浏览量
        
        //2、筛选参数
        $map['a.enabled'] = 2;
        if($category_id){
            $pid_list = M('category')->where(array('pid'=>$category_id))->getfield('id', true);
            $pid_list[] = $category_id;
            
            $map['a.cid'] = array('in', $pid_list);
        }
        if($o == 0) {
            $order = 'a.praise_nums desc';       //点赞数
        }else if($o == 1) {
            $order = 'a.id desc';                //最新
        }else if($o == 2) {
            $order = 'a.page_view desc';        //浏览量
        }

        //3、获取数据
        $original = M("original_article");
        $res = $original->alias('a')
             ->field('a.*,b.nickname,c.cname,c.pid')
             ->join('join cos_user b on a.uid=b.id')
             ->join('cos_category c on a.cid=c.id')
             ->where($map)
             ->order($order)
             ->page($page, $num)
             ->select();
             // p(M()->getLastSql());die;
        //4、获取所有一级栏目  
        $cat_lst = M('category')->where(array('pid'=>0))->order('id asc')->select();
        $c_lst = array();
        if($cat_lst) {
            foreach ($cat_lst as $k => $v) {
                $c_lst[$v['id']] = $v;
            }                
        }
        
        //5、如果为二级栏目的，获取它的一级栏目名
        if($res) {
            foreach ($res as $k => &$v) {
                $v['pname'] = '';
                if($v['pid'] != 0) {
                    $v['pname'] = $c_lst[$v['pid']]['cname'];
                }else{
                    $v['pname'] = $v['cname'];
                    $v['cname'] = '';
                }
            }
        }else{
            $res = array();
        }
 
        //6、分配数据
        if($r_type) {
            die(json_encode(array('data'=>$res)));
        }else{
            $this->assign('odata_list',$res);
            $this->display();
        }
    }

    /****原创页面首页(按照最新发布排序)*******/
    public function original_recent(){
            $original = M("original_article");
            $res = $original->alias('a')
                 ->field('a.*,b.nickname')
                 ->join('left join cos_user b on a.uid=b.id')
                 ->where(array("a.enabled"=>2))
                 ->order('a.createtime desc')
                 ->limit(16)
                 ->select();
            if($res){
                foreach ($res as $k => $value) {
                    $tres = M("category")->alias('a')
                    ->field('a.*')
                    ->where(array("a.id"=>$value["cid"]))
                    ->find();
                    if($tres["pid"] == 0){
                        //一级分类
                        $res[$k]["tlf_name"] = "";
                        $res[$k]["flf_name"] = $tres["cname"];
                    }else{
                        //二级分类
                        $res[$k]["tlf_name"] = $tres["cname"];
                        $fres = M("category")->alias('a')
                        ->field('a.*')
                        ->where(array("a.id"=>$tres["pid"]))
                        ->find();
                        $res[$k]["flf_name"] = $fres["cname"];
                    }  
                }
            }
            $this->assign("odata_list", $res);
      $this->display();
    }
    /****原创页面首页(按照浏览量多少排序)*******/
    public function original_viewed(){
            $original = M("original_article");
            $res = $original->alias('a')
                 ->field('a.*,b.nickname')
                 ->join('left join cos_user b on a.uid=b.id')
                 ->where(array("a.enabled"=>2))
                 ->order('a.page_view desc')
                 ->limit(16)
                 ->select();
            if($res){
                foreach ($res as $k => $value) {
                    $tres = M("category")->alias('a')
                    ->field('a.*')
                    ->where(array("a.id"=>$value["cid"]))
                    ->find();
                    if($tres["pid"] == 0){
                        //一级分类
                        $res[$k]["tlf_name"] = "";
                        $res[$k]["flf_name"] = $tres["cname"];
                    }else{
                        //二级分类
                        $res[$k]["tlf_name"] = $tres["cname"];
                        $fres = M("category")->alias('a')
                        ->field('a.*')
                        ->where(array("a.id"=>$tres["pid"]))
                        ->find();
                        $res[$k]["flf_name"] = $fres["cname"];
                    }  
                }
            }
            $this->assign("odata_list", $res);
      $this->display();
    }
     /****原创详情页面*******/
    public function original_detail1(){
            $id = I('param.oid','',false);
            $uid = session('user.uid');

            $result = M('collection')->alias('a')
                ->field('a.collection_table_id')
                ->join('left join cos_collection_table b on a.collection_table_id=b.id')
                ->where(array('b.uid'=>$uid,'works_id'=>$id,'collection_type'=>2))
                ->select();
            if($result){
                $collectioned = 1;
                $this->assign('collectioned',$collectioned);
            }else{
                $collectioned = 0;
                $this->assign('collectioned',$collectioned);
            }

            $names = M('collection_table')->where(array('uid'=>$uid))->select();
            $this->assign('names',$names);

            $original = M("original_article");
            /*当前声态**/
            $res = $original->alias('a')
                 ->field('a.*,b.nickname,b.province,b.city,b.area,b.fans_number,b.focus_on,b.industry,b.avatar,b.synopsis,b.sex,b.profession')
                 ->join('left join cos_user b on a.uid=b.id')
                 ->where(array("a.id"=>$id))
                 ->order('a.createtime desc')
                 ->select();
            
            if($res){
                foreach ($res as $k => $value) {
                    $res[$k]["cnt"] = explode(",",$value["cnt"]);
                    //标签数据拼接
                    $tname = array();
                    $tags = M("original_article_tags")->alias('a')
                     ->field('b.id,b.tname')
                     ->join('left join cos_original_tags b on a.tid=b.id')
                     ->where(array("a.original_id"=>$value["id"]))
                     ->select();
                     foreach ($tags as $key => $v) {
                        $tname[$key]['id'] = $v["id"];
                        $tname[$key]['tname'] = $v["tname"];
                     }
                     $res[$k]["tname"] = $tname;
                     //二级分类数据拼接
                    $tres = M("category")->alias('a')
                     ->field('a.*')
                     ->where(array("a.id"=>$value["cid"]))
                     ->find();
                     $res[$k]["tlf_name"] = $tres["cname"];

                     //一级分类
                     $fres = M("category")->alias('a')
                     ->field('a.*')
                     ->where(array("a.id"=>$tres["pid"]))
                     ->find();
                     $res[$k]["flf_name"] = $fres["cname"];

                     //关注状态
                      $isfollow = M("fans")->where(array('fans_id'=>$uid,'uid'=>$res[0]['uid']))->find();
                      if($isfollow){
                             $res[$k]["is_follow"] = 1;
                      }else{
                             $res[$k]["is_follow"] = 0;
                      }
                     //点赞状态
                      $is_praise = M("article_praise")->where(array('puid'=>$uid,"article_id"=>$id,"praise_type"=>2))->find();
                      if($is_praise){
                             $res[$k]["is_praise"] = 1;
                      }else{
                             $res[$k]["is_praise"] = 0;
                      }
                      //地址
                      if(534<$res[$k]["province"] && $res[$k]["province"]<567){
                        $res[$k]["address"] = M('locations')->where(array('id'=>$res[$k]["province"]))->getField('name');
                      }elseif ($res[$k]["province"] == 0){
                        $res[$k]["address"] = "未设置";
                      }elseif($res[$k]["city"] == 0){
                        $province = M('locations')->where(array('id'=>$res[$k]["province"]))->getField('name');
                        $res[$k]["address"] = $province;
                      }elseif ($res[$k]["area"] == 0) {
                        $province = M('locations')->where(array('id'=>$res[$k]["province"]))->getField('name');
                        $city = M('locations')->where(array('id'=>$res[$k]["city"]))->getField('name');
                        $res[$k]["address"] = $province."&#8197;/&#8197;".$city;
                      }else{
                        $province = M('locations')->where(array('id'=>$res[$k]["province"]))->getField('name');
                        $city = M('locations')->where(array('id'=>$res[$k]["city"]))->getField('name');
                        $area = M('locations')->where(array('id'=>$res[$k]["area"]))->getField('name');
                        $res[$k]["address"] = $province."&#8197;/&#8197;".$city."&#8197;/&#8197;".$area;
                      }
                      //行业信息
                      if(empty($res[$k]["industry"])){
                        $res[$k]["industry"] = "未设置";
                      }else{
                        $res[$k]["industry"] = $res[$k]["industry"];
                      }
					  
					$user_type = M('user')->where(array('id' => $value['uid']))->getField('type');
					if($user_type == 1){//个人
						//作者加入组织机构
						$group_list = M('user_group')->alias('a')
								->field('b.id,b.nickname,b.avatar')
								->join('left join cos_user b on a.mid=b.id')
								->where(array("a.uid"=>$value['uid']))
								->order('b.id desc')
								->limit(6)
								->select();
					}else{//机构
						//组织机构的成员
						$group_list = M('user_group')->alias('a')
								->field('b.id,b.nickname,b.avatar,b.type')
								->join('left join cos_user b on a.uid=b.id')
								->where(array("a.mid"=>$value['uid']))
								->order('b.id desc')
								->limit(6)
								->select();
					}
					
					$this->assign("user_type",$user_type); 
					$this->assign("fans_info",$group_list);

                }
            }
        /*****它的更多原创生态*******/
         $res1 = $original->alias('a')
                 ->field('a.*,b.nickname')
                 ->join('left join cos_user b on a.uid=b.id')
                 ->where(array("a.enabled"=>2,"a.uid"=>$res[0]["uid"],"a.id"=>array("neq",$res[0]["id"])))
                 ->order('a.createtime desc')
                 ->limit(0, 3)
                 ->select();
            if($res1){
                foreach ($res1 as $k => $value) {
                     //二级分类数据拼接
                    $tres = M("category")->alias('a')
                     ->field('a.*')
                     ->where(array("a.id"=>$value["cid"]))
                     ->find();
                     $res1[$k]["tlf_name"] = $tres["cname"];

                     //一级分类
                     $fres = M("category")->alias('a')
                     ->field('a.*')
                     ->where(array("a.id"=>$tres["pid"]))
                     ->find();
                     $res1[$k]["flf_name"] = $fres["cname"];
                }
            }
            $this->assign("odata_list_more", $res1);
            $this->assign("or_details", $res);
            M("original_article")->where(array("id"=>$id))->setInc("page_view");
            $this->display();
    }

    /*
    新建收藏夹
    */
    public function choose_collection(){
        if(!session('user.uid')){
            echoResult(-7);
        }
        $uid = session('user.uid');
        $name = I('collection_table_name');
        
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
        $cid = I('cid');
        $id = I('id');
        $time = time();
        $data = array(
            'collection_table_id' => $cid,
            'works_id' => $id,
            'createtime' => $time,
            'collection_type' => 2,
        );
        $res = M('collection')->add($data);
        if($res){
            $code = -32;
            M('original_article')->where(array('id'=>$id))->setInc('collect_nums');
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
            ->where(array('b.uid'=>$uid,'a.works_id'=>$id,'collection_type'=>2))
            ->select();
        $data = array(
            'collection_id' => $res[0]['collection_table_id'],
            'works_id' => $id,
            'collection_type' => 2
        );
        $result = M('collection')->where($data)->delete();
        if($result){
            $code = -48;
            M('original_article')->where(array('id'=>$id))->setDec('collect_nums');
        }else{
            $code = -49;
        }
        echoResult($code);
    }

    public function original_detail2($tid){
        $res = M('original_article')->where(array('uid'=>$tid,'enabled'=>2))->order('id desc')->limit(6)->select();
        $this->assign("res",$res);

        $use_info = M('user')->where(array('id'=>$tid))->select();
        
        if(534<$use_info[0]['province'] && $use_info[0]['province']<567){
            $locations = M('locations');
            $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name');
            $address = $province;
            $use_info[0]['address'] = $address;
        }elseif($use_info[0]['province'] == 0){
            $use_info[0]['address'] = "未设置";
        }else{
            if($use_info[0]['city'] == 0){
                $locations = M('locations');
                $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name'); 
                $address = $province;
                $use_info[0]['address'] = $address;
            }elseif($use_info[0]['area'] == 0){
                $locations = M('locations');
                $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name');
                $city = $locations->where(array('id'=>$use_info[0]['city']))->getField('name');
                $address = $province."&#8197;/&#8197;".$city;
                $use_info[0]['address'] = $address;
            }else{
                $locations = M('locations');
                $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name');
                $city = $locations->where(array('id'=>$use_info[0]['city']))->getField('name');
                $area = $locations->where(array('id'=>$use_info[0]['area']))->getField('name');
                $address = $province."&#8197;/&#8197;".$city."&#8197;/&#8197;".$area;
                $use_info[0]['address'] = $address; 
            }
            
        }
        // P($use_info);die;
        $this->assign("use_info",$use_info);

        /* $fans_info = M('fans')->alias('a')
                 ->field('b.id,b.nickname,b.avatar')
                 ->join('left join cos_user b on a.fans_id=b.id')
                 ->where(array("a.uid"=>$tid))
                 ->order('b.id desc')
                 ->limit(6)
                 ->select(); */
		//所在机构查询
		$fans_info = M('user_group')->alias('a')
                ->field('b.id,b.nickname,b.avatar')
                ->join('left join cos_user b on a.mid=b.id')
                ->where(array("a.uid"=>$tid))
                ->order('b.id desc')
                ->limit(6)
                ->select();
        $this->assign("fans_info",$fans_info);

        $uid = session('user.uid');
        $result = M('fans')->where(array('uid'=>$tid,'fans_id'=>$uid))->select();
        if($result){
            $type = 1;
        }else{
            $type = 0;
        }
        $this->assign("type",$type);
    	$this->display();
    }
    public function original_detail3($tid){
        $res = M('original_article')->where(array('uid'=>$tid))->order('id desc')->limit(6)->select();
        $this->assign("res",$res);

        $use_info = M('user')->where(array('id'=>$tid))->select();
        
        if(534<$use_info[0]['province'] && $use_info[0]['province']<567){
            $locations = M('locations');
            $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name');
            $address = $province;
            $use_info[0]['address'] = $address;
        }elseif($use_info[0]['province'] == 0){
            $use_info[0]['address'] = "未设置";
        }else{
            if($use_info[0]['city'] == 0){
                $locations = M('locations');
                $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name'); 
                $address = $province;
                $use_info[0]['address'] = $address;
            }elseif($use_info[0]['area'] == 0){
                $locations = M('locations');
                $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name');
                $city = $locations->where(array('id'=>$use_info[0]['city']))->getField('name');
                $address = $province."&#8197;/&#8197;".$city;
                $use_info[0]['address'] = $address;
            }else{
                $locations = M('locations');
                $province = $locations->where(array('id'=>$use_info[0]['province']))->getField('name');
                $city = $locations->where(array('id'=>$use_info[0]['city']))->getField('name');
                $area = $locations->where(array('id'=>$use_info[0]['area']))->getField('name');
                $address = $province."&#8197;/&#8197;".$city."&#8197;/&#8197;".$area;
                $use_info[0]['address'] = $address; 
            }
            
        }
        $this->assign("use_info",$use_info);

		if($use_info[0]['type'] == 1){//个人
			$fans_info = M('fans')->alias('a')
                ->field('b.id,b.nickname,b.avatar')
                ->join('left join cos_user b on a.fans_id=b.id')
                ->where(array("a.uid"=>$tid))
                ->order('b.id desc')
                ->limit(6)
                ->select();
				
		}else if($use_info[0]['type'] == 2){//组织机构
			$fans_info = M('user_group')->alias('a')
                ->field('b.id,b.nickname,b.avatar')
                ->join('left join cos_user b on a.uid=b.id')
                ->where(array("a.mid"=>$tid))
                ->order('b.id desc')
                ->limit(6)
                ->select();
		}
        
        $this->assign("fans_info",$fans_info);

        $uid = session('user.uid');
        $result = M('fans')->where(array('uid'=>$tid,'fans_id'=>$uid))->select();
        if($result){
            $type = 1;
        }else{
            $type = 0;
        }
        $this->assign("type",$type);
      $this->display();
    }
    public function original_opus(){
        $tid = I("param.tid",'',false);
             
    	$this->display();
    }



    /**
     * 用户列表
     * @return [type] [description]
     */
    public function original_plist(){
        //1、接收参数
        $type = I('type', 1, 'intval');             //1、People   2、Teams
        $page = I('page', 1, 'intval');             //当前页码
        $rows = I('rows', 10, 'intval');            //本页条数
        $order = I('order', 'ent');                 //排序
        $s_type = I('s_type', 1, 'intval');         //1、直接hmtl+数据  2、返回数据  
        $uid = session('user.uid') ? session('user.uid') : 0;   //浏览用户的uid

        //2、筛选信息
        if($order == 'ent'){
            $order = 'a.id desc';
        }elseif($order == 'pop'){
            $order = 'a.focus_on desc';
        }elseif($order == 'rec'){
            $order = 'a.fans_number desc';
        }
        $map['type'] = $type;

        //3、获取用户列表
        $user_lst = M('user')->alias('a')
                             ->field('a.*,ifnull(b.name, "未设置") pname,c.name cname,d.name aname,ifnull(e.id,0) is_follow')
                             ->join('LEFT JOIN cos_locations b ON a.province=b.id')
                             ->join('LEFT JOIN cos_locations c ON a.city=c.id')
                             ->join('LEFT JOIN cos_locations d ON a.area=d.id')
                             ->join('LEFT JOIN cos_fans e ON (a.id=e.uid AND e.fans_id='.$uid.')')
                             ->where($map)
                             ->page($page, $rows)
                             ->order($order)
                             ->select();

        //4、获取每个用户的作品信息
        foreach ($user_lst as $k => &$v) {
            //设置爱好
            $v['industry']= str_replace(",","&#8197;/&#8197;",$v['industry']);
            //设置地址
            if($v['pname'] !== '未设置') {
                $v['address'] = $v['pname'];
                if($v['cname']) $v['address'] .= " / ".$v['cname'];
                if($v['aname']) $v['address'] .= " / ".$v['aname'];
            }else{
                $v['address'] = $v['pname'];
            }
            
            //设置原创文章列表
            $lst = M('original_article')->field('id,cover_curimg')->where(array('uid'=>$v['id'], 'enabled'=>2))->limit(0,3)->select();
            if($lst) {
                $v['orginal_list'] = $lst;
            }else{
                $v['orginal_list'] = array();
            }
        }
        // p($user_lst);die;
        //5、分配数据
        if($s_type == 1) {
            $this->assign('user_lst', $user_lst);
            $this->display();
        }else{
            die(json_encode(array('data'=>$user_lst)));
        }
    }


    /*
    点击加载更多用户
    */
    public function user_more(){
      $begin =I('begin');
	  $type = I("param.type",1,false);
	   if($type == 1){
            $map['type'] = 1;
        }else{
            $map['type'] = 2;
        }

        $order = I('order', 'pop');
        if($order == 'ent'){
          $order_str = 'id desc';
        }elseif($order == 'pop'){
          $order_str = 'focus_on desc';
        }elseif($order == 'rec'){
          $order_str = 'fans_number desc';
        }else{
          $order_str = 'id desc';
        }

      $user = M('user')->where($map)->order($order_str)->limit($begin,10)->select();
      if(empty($user)){
			if($type == 1){
				echoDataResult(-52,$user);
			}else{
				echoDataResult(-30011,$user);
			}
			
      }
      foreach($user as $key => $users){
        $arts = M('original_article')->field('cover_curimg')->where(array('uid'=>$users['id']))->limit(0,3)->select();
        if(empty($arts)){
          $user[$key]['cover_curimg'] = '<div class="fr pub_img no-img">
                                    <div class="no-img_emp"><i>This is empty</i></div>
                                </div>';
        }else{
          $user[$key]['cover_curimg'] = '<div class="fr pub_img">
                                      <img class="f-pic" src="'.$arts[0]["cover_curimg"].'">
                                      <img class="f-pic" src="'.$arts[1]["cover_curimg"].'">
                                      <img class="f-pic" src="'.$arts[2]["cover_curimg"].'">
                                    </div>';
        }
        $user_user = M('fans')->where(array('uid'=>$users['id']))->count();
        $user[$key]['fans_count'] = $user_user;

        if($users['province']>534 && $users['province']<567){
            $locations = M('locations');
            $province = $locations->where(array('id'=>$users['province']))->getField('name');
            $address = $province;
            $user[$key]['address'] = $address;
        }elseif($users['province'] == 0){
            $user[$key]['address'] = "未设置";
        }else{
            if($users['city'] == 0){
                $locations = M('locations');
                $province = $locations->where(array('id'=>$users['province']))->getField('name'); 
                $address = $province;
                $user[$key]['address'] = $address;
            }elseif($users['area'] == 0){
                $locations = M('locations');
                $province = $locations->where(array('id'=>$users['province']))->getField('name');
                $city = $locations->where(array('id'=>$users['city']))->getField('name');
                $address = $province."/".$city;
                $user[$key]['address'] = $address;
            }else{
                $locations = M('locations');
                $province = $locations->where(array('id'=>$users['province']))->getField('name');
                $city = $locations->where(array('id'=>$users['city']))->getField('name');
                $area = $locations->where(array('id'=>$users['area']))->getField('name');
                $address = $province."/".$city."/".$area;
                $user[$key]['address'] = $address;
            } 
        }

        //我是否关注了我的粉丝
        $uid = session("user.uid");
        if(M("fans")->where(array("fans_id"=>$uid,"uid"=>$users['id']))->find()){
            $user[$key]['is_follow'] = 0;
            $user[$key]['followed'] = '<a type="1" class="followBtn" ids="'.$users['id'].'" href="javascript:void(0);" style="background-color:#c9c9c9">CANCEL</a>';
        }else{
            $user[$key]['is_follow'] = 1;
            $user[$key]['followed'] = '<a type="0" class="followBtn" ids="'.$users['id'].'" href="javascript:void(0);" style="background-color:#f7b53e">FOLLOW</a>';
        }

      }
      echoDataResult(0,$user);
    }
    /*
      原创列表页(标签列表)
    */
    public function original_tags($value){
      $info = M('original_article')->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->join('left join cos_original_article_tags d on d.original_id=a.id')
        ->join('left join cos_original_tags c on c.id=d.tid')
        ->where(array("a.enabled"=>2,"c.tname"=>$value))
        ->order('a.id desc')
        ->limit(16)
        ->select();
      foreach ($info as $k => $v) {
        $tres = M("category")->alias('a')
        ->field('a.*')
        ->where(array("a.id"=>$v["cid"]))
        ->find();
        if($tres["pid"] == 0){
            //一级分类
            $info[$k]["tlf_name"] = "";
            $info[$k]["flf_name"] = $tres["cname"];
        }else{
            //二级分类
            $info[$k]["tlf_name"] = $tres["cname"];
            $fres = M("category")->alias('a')
            ->field('a.*')
            ->where(array("a.id"=>$tres["pid"]))
            ->find();
            $info[$k]["flf_name"] = $fres["cname"];
        }  
      }
      $this->assign('info',$info);
      $this->display();
    }
    /*
      新增原创列表(标签页面)
    */
    public function original_tags_more($value,$begin){
      $info = M('original_article')->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->join('left join cos_original_article_tags d on d.original_id=a.id')
        ->join('left join cos_original_tags c on c.id=d.tid')
        ->where(array("a.enabled"=>2,"c.tname"=>$value))
        ->order('a.id desc')
        ->limit($begin,32)
        ->select();
      if(empty($info)){
        echoDataResult(-47,$info);
      }
      foreach ($info as $k => $v) {
        $tres = M("category")->alias('a')
        ->field('a.*')
        ->where(array("a.id"=>$v["cid"]))
        ->find();
        if($tres["pid"] == 0){
            //一级分类
            $info[$k]["tlf_name"] = "";
            $info[$k]["flf_name"] = $tres["cname"];
        }else{
            //二级分类
            $info[$k]["tlf_name"] = $tres["cname"];
            $fres = M("category")->alias('a')
            ->field('a.*')
            ->where(array("a.id"=>$tres["pid"]))
            ->find();
            $info[$k]["flf_name"] = $fres["cname"];
        } 
      }
      echoDataResult(0,$info);
      //点击新增原创文章列表(标签页面)
      
    }

}