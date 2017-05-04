<?php
namespace Admin\Controller;
use Think\Controller;
class PublicController extends Controller {
    //登录
    public function login() {
        //判断是否已经登录
        if($this->_is_login()) $this->redirect('Index/index');

        //异步登录
        if(IS_POST) {
            $lModel = D('Admin');
            $code = $lModel->login(I('username', ''), I('password', ''));

            echoDataResult($code);
        }

        $this->display();
    }

    //退出登录
    public function login_out() {
        session(null);
        cookie(null);

        $this->redirect('Public/login');
    }

    /**
     * 检测是否登录
     * @return boolean [description]
     */
    private function _is_login() {
        if(session('admin_info.id')) {
            return true;
        }else{
            return false;
        }
    } 
}