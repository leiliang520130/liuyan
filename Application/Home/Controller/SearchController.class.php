<?php
namespace Home\Controller;
use Think\Controller;
class SearchController extends Controller {
	public function search(){
		$uid = session('user.uid');
    $value = I('value');

    //搜索关键字入库
    $st = M('to_search')->where(array('skey'=>$value))->find();
    if($st) {
      M('to_search')->where(array('id'=>$st['id']))->setInc('num');
    }else{
      M('to_search')->add(array('skey'=>$value, 'num'=>1));
    }

		//查询精选作品
		$fine_where['title'] = array('like',"%$value%");
		$fine_where['enabled'] = '1';
		$fine = M('fine_articles')->where(array($fine_where))->order('id desc')->limit(4)->select();
		foreach ($fine as $key => $fines) {
      $category = M('category')->where(array('id'=>$fines['category_id']))->getfield('cname');
    	$fine[$key]['category'] = $category;
  	}
  	$fine_count = M('fine_articles')->where($fine_where)->count();
  	
  	//查询原创作品
  	$original_where['title'] = array('like',"%$value%");
    $original_where['enabled'] = '2';
    $original = M('original_article')->alias('a')
    ->field('a.*,b.nickname')
    ->join('left join cos_user b on a.uid=b.id')
    ->where(array($original_where))
    ->order('id desc')
    ->limit(4)
    ->select();
    $original_count = M('original_article')->alias('a')
    ->field('a.*,b.nickname')
    ->join('left join cos_user b on a.uid=b.id')
    ->where(array($original_where))
    ->count();
  	if($original){
      foreach ($original as $k => $originals) {
        $tres = M("category")->alias('a')
        ->field('a.*')
        ->where(array("a.id"=>$originals["cid"]))
        ->find();
        if($tres["pid"] == 0){
          //一级分类
          $original[$k]["tlf_name"] = "";
          $original[$k]["flf_name"] = $tres["cname"];
        }else{
          //二级分类
          $original[$k]["tlf_name"] = $tres["cname"];
            $fres = M("category")->alias('a')
            ->field('a.*')
            ->where(array("a.id"=>$tres["pid"]))
            ->find();
            $original[$k]["flf_name"] = $fres["cname"];
        }  
    	}
	  }
  		// P($original);die;

  		//查询网站用户
  		$user_where['nickname'] = array('like',"%$value%");
  		$user = M('user')->where($user_where)->order('id desc')->limit(2)->select();
  		$user_count = M('user')->where($user_where)->count();
  		foreach($user as $key => $users){
        $arts = M('original_article')->field('cover_curimg')->where(array('uid'=>$users['id'], 'enabled'=>2))->limit(0,3)->select();
        $user[$key]['cover_curimg'] = $arts;
        $user_user = M('fans')->where(array('uid'=>$users['id']))->count();
        $user[$key]['fans_count'] = $user_user;
        $user[$key]['industry']= str_replace(",","&#8197;/&#8197;",$users['industry']);
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
                $address = $province."&#8197;/&#8197;".$city;
                $user[$key]['address'] = $address;
            }else{
                $locations = M('locations');
                $province = $locations->where(array('id'=>$users['province']))->getField('name');
                $city = $locations->where(array('id'=>$users['city']))->getField('name');
                $area = $locations->where(array('id'=>$users['area']))->getField('name');
                 $address = $province."&#8197;/&#8197;".$city."&#8197;/&#8197;".$area;
                $user[$key]['address'] = $address;
            } 
        }
        //我是否关注了我的粉丝
        $uid = session("user.uid");
        if(M("fans")->where(array("fans_id"=>$uid,"uid"=>$users['id']))->find()){
            $user[$key]['is_follow'] = 0;
        }else{
            $user[$key]['is_follow'] = 1;
        }
      }

  		//查询活动
      $activity_where['title'] = array('like',"%$value%");
		  $activity_where['enabled'] = '1';
    	$activity = M('activity_articles')->where(array($activity_where))->order('id desc')->limit(4)->select();
	    $activity_count = M('activity_articles')->where(array($activity_where))->count();
      foreach ($activity as $key => $activitys) {
		    $category = M('category')->where(array('id'=>$activitys['category_id']))->getfield('cname');
		    $activity[$key]['category'] = $category;

		    $name = M('locations')->where(array('id'=>$activitys['province']))->getfield('name');
		    $activity[$key]['city_name'] = $name;
	    }
      if(empty($value)){
        $fine = "";
        $fine_count = 0;
        $original = "";
        $original_count = 0;
        $user = "";
        $user_count = 0;
        $activity = "";
        $activity_count = 0;
      }
	    $this->assign('fine',$fine);
	    $this->assign('fine_count',$fine_count);

	    $this->assign('original',$original);
	    $this->assign('original_count',$original_count);

	    $this->assign('user',$user);
	    $this->assign('user_count',$user_count);
	    $this->assign('activity',$activity);
	    $this->assign('activity_count',$activity_count);
      $all_count = $fine_count+$original_count+$user_count+$activity_count;
      $this->assign('all_count',$all_count);
      $this->display();
    }
    public function fine_more($begin){
      $value = I('value');
      if(empty($value)){
        echoDataResult(-47,$user);
      }
      $fine_where['title'] = array('like',"%$value%");
      $fine_where['enabled'] = '1';
      $fine = M('fine_articles')->where(array($fine_where))->order('id desc')->limit($begin,16)->select();
      if(empty($fine)){
        echoDataResult(-47,$fine);
      }
      foreach ($fine as $key => $fines) {
        $category = M('category')->where(array('id'=>$fines['category_id']))->getfield('cname');
        $fine[$key]['category'] = $category;
      }
      echoDataResult(0,$fine);
      //点击新增精选文章列表
    }
    public function original_more($begin){
      $value =I('value');
      if(empty($value)){
        echoDataResult(-47,$user);
      }
      $original_where['title'] = array('like',"%$value%");
      $original_where['enabled'] = '2';
      $info = M('original_article')->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->where(array($original_where))
        ->order('a.id desc')
        ->limit($begin, 16)
        ->select();
      if(empty($info)){
        echoDataResult(-47,$info);
      }
      if($info){
          foreach ($info as $k => $value) {
              $tres = M("category")->alias('a')
            ->field('a.*')
            ->where(array("a.id"=>$value["cid"]))
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
      }
      echoDataResult(0,$info);
    }
    public function user_more(){
      $begin =I('begin');
      $value = I('value');
      if(empty($value)){
        echoDataResult(-52,$user);
      }
      $user_where['nickname'] = array('like',"%$value%");
      $user = M('user')->where(array($user_where))->order('id desc')->limit($begin,5)->select();
      if(empty($user)){
        echoDataResult(-52,$user);
      }
      foreach($user as $key => $users){
        $arts = M('original_article')->field('cover_curimg')->where(array('uid'=>$users['id'], 'enabled'=>2))->limit(0,3)->select();
        if(empty($arts)){
          $user[$key]['cover_curimg'] = '<div class="fr pub_img no-img">
                                    <div class="no-img_emp"><i>This is empty</i></div>
                                </div>';
        }else{
          $u_url = U("/Original/original_detail2");
          $user[$key]['cover_curimg'] = '<div class="fr pub_img">
                                      <a href="'.$u_url.'?tid='.$users['id'].'">
                                        <img class="f-pic" src="'.$arts[0]["cover_curimg"].'">
                                        <img class="f-pic" src="'.$arts[1]["cover_curimg"].'">
                                        <img class="f-pic" src="'.$arts[2]["cover_curimg"].'">
                                      </a>
                                    </div>';
        }
        $user_user = M('fans')->where(array('uid'=>$users['id']))->count();
        $user[$key]['fans_count'] = $user_user;
        $user[$key]['industry']= str_replace(",","&#8197;/&#8197;",$users['industry']);
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
                $address = $province."&#8197;/&#8197;".$city;
                $user[$key]['address'] = $address;
            }else{
                $locations = M('locations');
                $province = $locations->where(array('id'=>$users['province']))->getField('name');
                $city = $locations->where(array('id'=>$users['city']))->getField('name');
                $area = $locations->where(array('id'=>$users['area']))->getField('name');
                $address = $province."&#8197;/&#8197;".$city."&#8197;/&#8197;".$area;
                $user[$key]['address'] = $address;
            } 
        }

        //我是否关注了我的粉丝
        $uid = session("user.uid");
        if(M("fans")->where(array("fans_id"=>$uid,"uid"=>$users['id']))->find()){
            $user[$key]['is_follow'] = 0;
            $user[$key]['followed'] = '<a type="1" class="followBtn" ids="'.$users['id'].'" href="javascript:void(0);">CANCEL</a>';
        }else{
            $user[$key]['is_follow'] = 1;
            $user[$key]['followed'] = '<a type="0" class="followBtn" ids="'.$users['id'].'" href="javascript:void(0);"">FOLLOW</a>';
        }

      }
      echoDataResult(0,$user);
    }

    public function activity_more($begin){
      $value = I('value');
      if(empty($value)){
        echoDataResult(-53,$user);
      }
      $acivity_where['title'] = array('like',"%$value%");
      $acivity_where['enabled'] = '1';
      $acivity = M('activity_articles')->where(array($acivity_where))->order('id desc')->limit($begin,16)->select();
      if(empty($acivity)){
        echoDataResult(-53,$acivity);
      }
      foreach ($acivity as $key => $fines) {
        $category = M('category')->where(array('id'=>$fines['category_id']))->getfield('cname');
        $acivity[$key]['category'] = $category;
      }
      echoDataResult(0,$acivity);
      //点击新增精选文章列表
    }
}