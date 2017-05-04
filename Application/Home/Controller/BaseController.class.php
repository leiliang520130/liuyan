<?php
/**
 * Created by PhpStorm.
 * User: jiangling
 * Date: 2016/12/20
 * Time: 10:15
 * 该控制器为基础控制器,判断在访问个人中心的时候是否已经登陆成功,如果没有登陆则跳转到登陆界面,执行登陆
 */

namespace Home\Controller;


use Think\Controller;

class BaseController extends Controller
{
    /**
     * 入口文件
     */
    public function _initialize() {

        $user = M('User')->where('id ='.session('user.uid'))->find();
        if(!$user){
            $this->redirect('User/login');
        }
        $this->assign('user',$user);

    }



}