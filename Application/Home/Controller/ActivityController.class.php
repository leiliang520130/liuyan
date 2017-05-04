<?php
namespace Home\Controller;
use Think\Controller;
class ActivityController extends Controller {
	/*
		活动列表页(默认即流行)
	*/
    public function activity(){
        //1、接收参数
        $category_id = I('type', 0);    //分类
        $page = I('p', 1);              //页数
        $num = I('n', 16);              //每页显示条数
        $r_type = I('r_type', 0);       //数据返回方式  0、page   1、json
        $o = I('o', 0);                 //排序方式 1 点赞数最多 0 表示时间最新 2浏览量
        $province = I('province', 0);   //国家id
        $city = I('city', 0);           //城市id


        //2、筛选参数
        if($o == 0) {
            $order = 'a.createtime desc';//按时间排序
        }else if($o == 1) {
            $order = 'a.collect_nums desc,a.createtime desc';//按照点赞数排序
        }else{
            $order = 'a.comment_nums desc,a.createtime desc';//按时间排序
        }
		$map = array();
		if($category_id){
			$pid_list = M('category')->where(array('pid'=>$category_id))->getfield('id', true);
			$pid_list[] = $category_id;
			
			$map['a.category_id'] = array('in', $pid_list);
		}
        if($province) $map['a.province'] = $province;
        if($city) $map['a.city'] = $city;
        $map['a.enabled'] = 1;

		$activity_articles = M('activity_articles');
        $info = $activity_articles->alias('a')
                                  ->field('a.*,b.cname,b.pid')
                                  ->join('cos_category b on b.id=a.category_id')
                                  ->where($map)
                                  ->order($order)
                                  ->page($page, $num)
                                  ->select();

        // $cat_lst = M('category')->where(array('pid'=>0))->order('id asc')->select();
        // $c_lst = array();
        // if($cat_lst) {
        //     foreach ($cat_lst as $k => $v) {
        //         $c_lst[$v['id']] = $v;
        //     }                
        // }

        //5、如果为二级栏目的，获取它的一级栏目名
        // if($info) {
        //     foreach ($info as $k => &$v) {
        //         $v['pname'] = '';
        //         if($v['pid'] != 0) {
        //             $v['pname'] = $c_lst[$v['pid']]['cname'];
        //         }else{
        //             $v['pname'] = $v['cname'];
        //             $v['cname'] = '';
        //         }
        //     }
        // }else{
        //     $info = array();
        // }
        // p($info);die;
        //获取所有国家和城市数据
        $pro_lst = M('areas')->where(array('pid'=>0))->select();
        if($city) $city_lst = M('areas')->where(array('pid'=>$province))->select();
        if(!$city_lst) $city_lst = array();

        if($r_type) {
            die(json_encode(array('data'=>$info)));
        }else{
            $this->assign('info',$info);
            $this->assign('o',$o);
            $this->assign('pro_lst', $pro_lst);
            $this->assign('city_lst', $city_lst);

            $this->display();
        }
    }

    //异步获取城市数据
    public function ajax_get_city() {
        $id = I('id', 0);
        $city_lst = $city_lst = M('areas')->where(array('pid'=>$id))->select();
        if(!$city_lst) $city_lst = array();

        die(json_encode(array('code'=>0, 'lst'=>$city_lst)));
    }

    /*
        活动列表页(推荐)
    */
    public function activity_recommend(){
        $activity_articles = M('activity_articles');
        $info = $activity_articles->order('collect_nums desc')->limit(16)->select();
        foreach ($info as $key => $infos) {
            $category_a = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
            $info[$key]['category'] = $category_a;
        }
        $this->assign('info',$info);
        // P($info);die;
        $this->display();
    }

    /*
		活动详情页
	*/
    public function active_detail(){
    	$id = I('id');
        $uid = session('user.uid');

        $names = M('collection_table')->where(array('uid'=>$uid))->select();
        $this->assign('names',$names);
        $result = M('collection')->alias('a')
            ->field('a.collection_table_id')
            ->join('left join cos_collection_table b on a.collection_table_id=b.id')
            ->where(array('b.uid'=>$uid,'works_id'=>$id,'collection_type'=>3))
            ->select();
        if($result){
            $collectioned = 1;
            $this->assign('collectioned',$collectioned);
        }else{
            $collectioned = 0;
            $this->assign('collectioned',$collectioned);
        }

    	$activity_articles = M('activity_articles');
		$info = $activity_articles->where(array('id'=>$id,'enabled'=>1))->select();

        foreach ($info as $key => $infos) {
            $category = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
            $info[$key]['category'] = $category;
        }
		$cnt = htmlspecialchars_decode($info[0]['cnt']);//对得出的正文进行去标签化处理
		$this->assign('cnt',$cnt);
		$this->assign('info',$info);//获取活动所有信息

		
		$activity_articles_tags = M('activity_articels_tags');
		$tid = $activity_articles_tags->where(array('activity_id'=>$id))->getField('tid',true);
		$tags = array();
		foreach ($tid as $key => $value) {
			$activity_tags = M('activity_tags');
			$tname = $activity_tags->where(array('id'=>$value))->select();
			$tags[]=$tname;
		}
		$this->assign('tags',$tags);//获取活动标签信息

		//通过活动id分别依次从收藏表,收藏夹表,用户表中查询出用户头像

		// $collection = M('collection');
		// $collection_table_id = $collection->where(array('works_id'=>$id,'collection_type'=>3))->getField('collection_table_id',true);
  //       $user_id = array();
		// foreach ($collection_table_id as $key => $value) {
		// 	$collection_table = M('collection_table');
		// 	$uid = $collection_table->where(array('id'=>$value))->getField('uid',true);
		// 	$user_id[$key] = $uid;
		// }
		// $user_avatar = array();
		// foreach ($collection_table_id as $key => $value) {
  //           $avatar = M('collection_table')->alias('a')->field('b.id,b.avatar')
  //           ->join('cos_user b ON a.uid=b.id')
  //           ->where(array('a.id'=>$value))->select();
		// }
        $user_avatar = M('collection')->alias('a')
                                      ->field('c.id,c.avatar')
                                      ->join('cos_collection_table b ON a.collection_table_id=b.id')
                                      ->join('cos_user c ON b.uid=c.id')
                                      ->where(array('a.works_id'=>$id, 'a.collection_type'=>3))
                                      ->select();


		$this->assign('user_avatar',$user_avatar);//获取收藏人头像
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
            'collection_type' => 3,
        );
        $res = M('collection')->add($data);
        if($res){
            $code = -32;
            M('activity_articles')->where(array('id'=>$id))->setInc('collect_nums');
            $ret['avatar'] = M('user')->where(array('id'=>session('user.uid')))->getField('avatar');
            $ret['id'] = session('user.uid');
        }else{
            $code = -33;
        }
        echoDataResult($code, $ret);
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
            ->where(array('b.uid'=>$uid,'a.works_id'=>$id,'collection_type'=>3))
            ->select();
        $data = array(
            'collection_id' => $res[0]['collection_table_id'],
            'works_id' => $id,
            'collection_type' => 3
        );
        $result = M('collection')->where($data)->delete();
        if($result){
            $code = -48;
            M('activity_articles')->where(array('id'=>$id))->setDec('collect_nums');
        }else{
            $code = -49;
        }
        echoDataResult($code, array('uid'=>$uid));
    }
    /*
        活动列表页(推荐)点击more新增
    */
    public function recom_activity_more($begin){
      $activity_articles = M('activity_articles');
      $info = $activity_articles->where(array('enabled'=>1))->order('collect_nums desc')->limit($begin,32)->select();
      if(empty($info)){
        echoDataResult(-53,$info);
      }
      foreach ($info as $key => $infos) {
        $category = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
        $info[$key]['category'] = $category;

        $name = M('locations')->where(array('id'=>$infos['province']))->getfield('name');
        $info[$key]['city_name'] = $name;
      }
      echoDataResult(0,$info);
      //点击新增精选文章列表
    }
    /*
        活动标签页面
    */
    public function activity_tags(){
        $id = I('id');
        $info = M('activity_articels_tags')->alias(a)
                ->join('left join cos_activity_articles b on a.activity_id=b.id')
                ->where(array('a.tid'=>$id))
                ->order('id desc')
                ->limit(16)
                ->select();
        $this->assign('info',$info);
        $this->display();
    }
    public function activity_tags_more($begin){
        $activity_articles = M('activity_articles');
        $id = I('id');
        $info = M('activity_articels_tags')->alias(a)
                ->join('left join cos_activity_articles b on a.activity_id=b.id')
                ->where(array('a.tid'=>$id,'b.enabled'=>1))
                ->order('id desc')
                ->limit($begin,32)
                ->select();
        if(empty($info)){
            echoDataResult(-53,$info);
        }
        foreach ($info as $key => $infos) {
            $category = M('category')->where(array('id'=>$infos['category_id']))->getfield('cname');
            $info[$key]['category'] = $category;
            $name = M('locations')->where(array('id'=>$infos['province']))->getfield('name');
            $info[$key]['city_name'] = $name;
        }
        echoDataResult(0,$info);
        //点击新增精选文章列表
    }
}