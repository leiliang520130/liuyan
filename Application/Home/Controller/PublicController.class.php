<?php
namespace Home\Controller;
use Think\Controller;
use LT\ThinkSDK\ThinkOauth;

class PublicController extends Controller {
		/*
			第一步验证注册
			$account [用户账号]
		*/
		private function first_validate_reg($username){
            $user = M('user');
            //验证账号是否存在
            $account_res = $user->where(array("nickname"=>$username))->find();
            return $account_res;
        }

    /**
     * 验证邮箱是否绑定了
     */

    public function get_token($email,$register_token)
    {
        $user = M('user');
        $data = $user->where(array("email1"=>$email))->find();
        $token = encrypt_email($email,$data['reg_time']);
        if($token == $register_token){
            $user->where(array("email1"=>$email))->setField('status',1);
            $this->success('激活成功',U('Index/index'));
        }else{
            $this->success('激活失败',U('Index/index'));
        }
    }


    public function checkByParam(){
        $cond = I('get.');
        $user = M('user');
        if($user->where($cond)->count()){
            $this->ajaxReturn(false);
        }else{
            $this->ajaxReturn(true);
        }
    }

    public function test(){

        $url = U('Public/get_token',['email'=>'1031031088@qq.com','register_token'=>'21312312'],true,true);
        $content = '欢迎你注册我们的网站,请点击<a href="'.$url.'">链接</a>激活账号.如果无法点击,请复制以下链接粘贴到浏览器窗口打开!<br />'.$url;
        send_email('1031041088@qq.com','注册验证',$content);

    }

        /*
			正式注册
			$account [用户账号]
			$nickname [昵称]
			$password [用户密码]
            (尚未使用到第三方注册)
        */
        public function register(){

            if(IS_POST){
                $data=I('');
                $data['password']=md5($data['passwd']);
                $data['reg_time']=time();
                $data['reg_ip']=get_client_ip(1);
                //判断用户名是否存在
                $res=$this->first_validate_reg($data['nickname']);

                if($res){
                    echoResult(-24);
                }


                $user = M('user');
                //判断邮箱是否存在
                $email1 = $user->where(array("email1"=>$data['email1']))->find();
                if($email1){
                    echoResult(-57);
                }

                $result = $user->add($data);

                if($result) {
                    $usercode=get_code($result);
                    $user->where(array('id'=>$result))->save(array('usercode'=>$usercode));
                    //发送邮件
                    $url = U('Public/get_token',['email'=>$data['email1'],'register_token'=>encrypt_email($data['email1'],$data['reg_time'])],true,true);
                    $content = '欢迎你注册我们的网站,请点击<a href="'.$url.'">链接</a>激活账号.如果无法点击,请复制以下链接粘贴到浏览器窗口打开!<br />'.$url;
                    send_email($data['email1'],'注册验证',$content);
                    //发送邮件结束
                    echoResult(0);
                }else{
                    echoResult(-1);
                }
            }else{
                $this->get_country();
                $this->display();
            }
        }

    /**
     * 注册之后到问题页面
     */
    public function anwser()
    {
        $data=I('');
        M('user_anwser')->add($data);
    }
    
        /*
			用账号(手机或邮箱)登录
            (尚未使用到第三方登录)
        */
        public function login(){
            if(IS_POST){
                $account = I('post.nickname');
                $password = md5(I('post.password'));

                $user = M('user');

                $account_res = $user->where(array("nickname"=>$account))->find();

                if(!$account_res){
                    echoResult(-8);
                }
                if($account_res['status'] == 0){
                    echoResult(-56);
                }

                $password_res = $user->where(array("nickname"=>$account))->getField('password');
                //查询单个字段用getField方法
                if($password != $password_res){
                    echoResult(-28);
                }
                $user->where(array('id'=>$account_res['id']))->save(array('is_login'=>1));
                session('use_account',$account_res);
                echoResult(-27);
            }else{
                $this->display();
            }
        }
    

    //用户登出
    public function outlogin() {
        session(null);
        cookie(null);
        $this->redirect("Index/index");
    }

    /**
     * 忘记密码
     */

    public function forgot1()
    {
        if(IS_POST){
           $nickname=I('nickname');
           $user=M('user')->where(array('nickname'=>$nickname))->find();
            if($user){
                echoDataResult(0,$user);
            } else{
                echoResult(-1);
            }
        }else{
            $this->display();
        }


    }

    public function forgot2($uid)
    {
        if(IS_POST){
            $uid=$uid;
            $answers=I('');
            $M=M('user_answer');
            $count=0;
            foreach($answers as$k=> $vo){
                $res=$M->where(array('uid'=>$uid,'answer'=>$vo))->find();
                if($res){
                   $count++;
                }
            }
            if($count-1==$k){
                echoResult(0);
            }else{
                echoResult(-1);
            }
        }else{
            $this->display();
        }

    }













































        /**
        *极验  后台验证码接口初始化
        */
        public function VerCode(){
           $v = new \Common\Api\VerifyApi();
           echo $v->VerCode();
        }
         /**
        *关注
        */
        public function fans_follow(){
            if(!session('user.uid')){
                echoResult(-7);
            }
            $uid = session('user.uid');
            $id = I('post.id');//被关注人的id
            if($uid == $id){
              echoResult(-54);
            }
            $type = I('post.type');//0表示关注 1表示取消关注
            $fans = M('fans');
            $code = 0;
            $isfollow = $fans->where(array('fans_id'=>$uid,"uid"=>$id))->find();
            if($type == 0 && !$isfollow){
                 $ids = $fans->data(array('fans_id'=>$uid,"uid"=>$id))->add();
                 M("user")->where(array("id"=>$id))->setInc("fans_number");
                 M("user")->where(array("id"=>$uid))->setInc("focus_on");
            }else{
                 $ids = $fans->where(array('fans_id'=>$uid,"uid"=>$id))->delete();
                 M("user")->where(array("id"=>$id))->setDec("fans_number");
                 M("user")->where(array("id"=>$uid))->setDec("focus_on");
            }
            $userCount = $fans->where(array('uid'=>$uid))->count();
            $fansCount = $fans->where(array('fans_id'=>$uid))->count();
            $this->assign('userCount',$userCount);
            $this->assign('fansCount',$fansCount);
            echoResult($code);
    }

    /*****公用点赞*接口****/
    public function praise_production(){
        $puid = session("user.uid");//当前点赞人
        $article_id =  I("post.article_id");//文章id
        $type = I("post.type");//0 表示点赞 1表示取消点赞
        $praise_type = I("post.praise_type",1);//1表示精选,2表示原创,3表示活动',
        if(!$puid) echoResult(-7);
        $praise = M("article_praise");
        $code = 0;
        if($type == 0){
            if($praise->where(array("puid"=>$puid,"article_id"=>$article_id,"praise_type"=>$praise_type))->find()){
                $code = 100;
            }else{
                $res = $praise->data(array("puid"=>$puid,"article_id"=>$article_id,"praise_type"=>$praise_type,"praise_time"=>time()))->add();
                if($res){
                      if($praise_type == 1){
                         M("fine_articles")->where(array("id"=>$article_id))->setInc("praise_nums");
                      }
                      if($praise_type == 2){
                         M("original_article")->where(array("id"=>$article_id))->setInc("praise_nums");
                      }
                      if($praise_type == 3){
                         M("activity_articles")->where(array("id"=>$article_id))->setInc("praise_nums");
                      }
                      $code = 0;
                }else{
                      $code = 101;
                }
            }
        }else{
                $res = $praise->where(array("puid"=>$puid,"article_id"=>$article_id,"praise_type"=>$praise_type))->delete();
                if($res){
                      if($praise_type == 1){
                         M("fine_articles")->where(array("id"=>$article_id))->setDec("praise_nums");
                      }
                      if($praise_type == 2){
                         M("original_article")->where(array("id"=>$article_id))->setDec("praise_nums");
                      }
                      if($praise_type == 3){
                        M("activity_articles")->where(array("id"=>$article_id))->setDec("praise_nums");
                      }
                  }else{
                    $code = 102;
                  }
         }
         echoResult($code);
    }

    /*
      首页和精选列表页的more按钮点击.
    */
    public function finearticle_more($begin){
      $fine_articles = M('fine_articles');
      $info = $fine_articles->where(array('enabled'=>1))->order('id desc')->limit($begin,32)->select();
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
    /*
      首页和原创列表页的more按钮点击.
    */
    public function original_more($begin){
      $type = I('post.type',0);
      $original_article = M('original_article');
      if($type == 1){
        $info = $original_article->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->where(array("a.enabled"=>2))
        ->order('a.collect_nums desc')
        ->limit($begin, 32)
        ->select();
      }elseif($type == 2){
        $info = $original_article->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->where(array("a.enabled"=>2))
        ->order('a.createtime desc')
        ->limit($begin, 32)
        ->select();
      }elseif($type == 3){
        $info = $original_article->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->where(array("a.enabled"=>2))
        ->order('a.page_view desc')
        ->limit($begin, 32)
        ->select();
      }else{
        $info = $original_article->alias('a')
        ->field('a.*,b.nickname')
        ->join('left join cos_user b on a.uid=b.id')
        ->where(array("a.enabled"=>2))
        ->order('a.id desc')
        ->limit($begin, 32)
        ->select();
      }
      
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
      //点击新增原创文章列表
    }
    /*
      首页和活动列表页(默认)的more按钮点击.
    */
    public function activity_more($begin){
      $activity_articles = M('activity_articles');
      $info = $activity_articles->where(array('enabled'=>1))->order('id desc')->limit($begin,32)->select();
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
    ///关于我们页面
    public function about_us(){
      $this->display();
    }
    ///自频道页面
    public function self_channel(){
      $this->display();
    }

	/**
     * 验证码
	 * @param    $fontSize     字体大小
	 * @param    $imgw         宽度
	 * @param    $imgh         高度
	 * @param    $length       长度
	 * @param    $type         类型 1、数字(默认)  2、数字+小写字母  3、数字+大小字母 4、其他（数字+小写字母+大小字母）
	 * @author ddf
     */
	public function getVerify($fontSize = 15, $imgw = 120, $imgh = 30, $length = 4, $type = 1, $msgtype = 1){
		
		$Verify = new \Think\Verify();
		
		//$Verify->expire   = 60;//有效期
		//$verify->useImgBg = true;//是否使用背景图片 默认false
		//$Verify->bg       = array(243, 251, 254); //验证码背景颜色 rgb数组设置，例如 array(243, 251, 254) 
		
		$Verify->fontSize = $fontSize;   //字体大小
		$Verify->useCurve = true;        //使用混淆曲线 默认true
		$Verify->useNoise = true;        //是否添加杂点 默认true
		$Verify->imageW   = $imgw.'px';  //宽度
		$Verify->imageH   = $imgh.'px';  //高度
		$Verify->length   = $length;     //长度
		
		if($type == 1){
			$Verify->codeSet  = '0123456789';
		}elseif($type == 2){
			$Verify->codeSet  = '123456789abcdefghijklmnopqrestuvwxyz';
		}elseif($type == 3){
			$Verify->codeSet  = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
		
		if($msgtype == 1){
			$str = $Verify->app_entry();
		
			echoDataResult(0, $str);//输出code，（app）
		}else{
			$str = $Verify->entry();//输出图像，（微信，pc端，手机端）
		}
		
	}
	
	/**
     * 验证码 检测
	 * @param     $code  验证码内容
	 * @return    false/true
	 * @author    ddf
     */
	public function checkVerify($code){
		
		$Verify = new \Think\Verify();
		
		$res = $Verify->check($code);
		
		if($res){
			echoDataResult(0, $res);
		}else{
			echoDataResult(-1);
		}
		
	}

  /**
   * 三方登录
   * @param  [type] $type [description]
   * @return [type]       [description]
   */
  public function to_login($type=null) {
        empty($type) && die('error');
        //加载ThinkOauth类并实例化一个对象
        $sns = ThinkOauth::getInstance($type);
        //跳转到授权页面
        redirect($sns->getRequestCodeURL());
  }

  /**
   * 三方登录的回调处理
   * @return [type] [description]
   */
  public function _empty() {
      $code = I('code', null);
      if(!$code ) redirect('http://' . $_SERVER['SERVER_NAME']);

      if(startWith(ACTION_NAME, 'callback')) {
          $type = substr(ACTION_NAME,8);
          $sns = ThinkOauth::getInstance($type);

          //腾讯微博需传递的额外参数
          $extend = null;
          if ($type == 'tencent') {
              $extend = array('openid' => $this->_get('openid'), 'openkey' => $this->_get('openkey'));
          }

          //请妥善保管这里获取到的Token信息，方便以后API调用
          //调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入
          //如： $qq = ThinkOauth::getInstance('qq', $token);
          $token = $sns->getAccessToken($code, $extend);
          if(!$token) redirect('http://' . $_SERVER['SERVER_NAME']);

          //1、已登录，把opein绑定到帐号内
          $uid = session('user.uid');
          if($uid) {
              if($type == 'sina') {
                  $error_type = 1;
                  $success_type = 3;
                  $map['weibo_id'] = $token['openid'];
                  $u_data = array('id'=>$uid, 'weibo_id'=>$token['openid']);
              }else if($type == 'qq') {
                  $error_type = 2;
                  $success_type = 4;
                  $map['qq_id'] = $token['openid'];
                  $u_data = array('id'=>$uid, 'qq_id'=>$token['openid']);
              }

              if(M('user')->where($map)->find()) {
                  redirect(U('UserCenter/personal_set', array('t'=>'z', 'e'=>$error_type)));
              }else{
                  M('user')->save($u_data);
                  redirect(U('UserCenter/personal_set', array('t'=>'z', 'e'=>$success_type)));
              }
          //2、未登录
          }else{  
              //>> 判断是否已经绑定过，如绑定过，则直接登录
              if($type == 'sina') {
                  $reg_data = array('weibo_id'=>$token['openid']);
                  $map['weibo_id'] = $token['openid'];
              }else if($type == 'qq') {
                  $reg_data = array('qq_id'=>$token['openid']);
                  $map['qq_id'] = $token['openid'];
              }
              $user = M('user')->where($map)->find();
              if($user) {
                  if($user['locked']) {
                      redirect(U('Index/index', array('ac'=>1)));
                      die;
                  }
                  session('user', array('uid'=>$user['id'], 'nickname'=>$user['nickname'], 'email'=>$user['email'], 'avatar'=>$user['avatar'], 'user_type'=>$user['type']));
                  redirect(U('UserCenter/personal_set'));
              //>> 如果没有绑定过，则显示绑定邮箱的页面   
              }else{
                  //>> 注册
                  $reg_data['reg_time'] = time();
                  $reg_data['reg_ip'] = get_client_ip();
                  $reg_data['last_login_time'] = time();
                  $reg_data['last_login_ip'] = get_client_ip();
                  M('user')->add($reg_data);

                  //>> 登录
                  $user = M('user')->where($map)->find();
                  session('user', array('uid'=>$user['id'], 'nickname'=>$user['nickname'], 'email'=>$user['email'], 'avatar'=>$user['avatar'], 'user_type'=>$user['type']));

                  //>> 跳转
                  redirect(U('UserCenter/personal_set'));
              }
          }        
      }else{
          redirect('http://' . $_SERVER['SERVER_NAME']);
      }

  }

    /**
     * 获取国籍接口
     * 获取研究领域的接口
     */

    private function get_country()
    {
        $r=M('country')->field('id,name,zh_name')->select();

        $r1=M('research_area')->select();

        $r2=M('appellation')->select();

        $this->assign('research',$r1);
        $this->assign('appellation',$r2);

        $this->assign('country',$r);
    }
	
}