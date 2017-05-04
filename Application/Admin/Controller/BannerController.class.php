<?php
namespace Admin\Controller;
class BannerController extends BaseController {
    public function index(){
        $filter = "status=0";
        //筛选条件
        $seo_title = I('seo_title', '');
        if($seo_title) $filter .= " AND seo_title like '%{$seo_title}%'";
        
        //获取数据
        $sql_count = "SELECT COUNT(*) num FROM cos_banner";

        $sql_data = "SELECT id,seo_title,img_url FROM cos_banner WHERE ".$filter." ORDER BY id ASC";
        $lst = $this->sql_page($sql_count, $sql_data);
        $this->assign('lst', $lst);

    	$this->display();
    }
    public function op(){
        $this->display();
    }
    public function edit($id){
        $info = M('banner')->where(array('id'=>$id))->select();
        $this->assign('info',$info);
        $this->display();
    }
    /*
        修改轮播图
    */
    public function update($id){

        $seo_title = I('post.seo_title');
        //$lunbo_imgurl = session('lunbo_imgurl');
        //session('lunbo_imgurl',null); // 删除name
		
		$lunbo_imgurl = I('cover_img', '');
		
        $data = array(
            'seo_title' => $seo_title,
            'img_url' => $lunbo_imgurl,
            'status' => 0,
            'url_link' =>  I('url_link', '')
        );
		
        $res = M('banner')->where(array('id'=>$id))->save($data);
        if($res){
            echoresult(0);
        }else{
            echoresult(-1);
        }
    }
    /*
        新增轮播图
    */
    public function insert(){
        $seo_title = I('post.seo_title');
        $lunbo_imgurl = session('lunbo_imgurl');
        session('lunbo_imgurl',null); // 删除name
        $data = array(
            'seo_title'    =>   $seo_title,
            'img_url'      =>   $lunbo_imgurl,
            'status'       =>   0,
            'url_link'     =>   I('url_link', ''),
        );
        $res = M('banner')->data($data)->add();
        if($res){
            echoresult(0);
        }else{
            echoresult(-1);
        }
    }
    /*
        删除轮播图
    */
    public function del(){
        $id = I('post.id');
        $res = M('banner')->where(array('id'=>$id))->delete();
        if($res){
            echoresult(0);
        }else{
            echoresult(-1);
        }
    }

    //上传图片
    public function upimg($path = '/Fine/Cover/'){
        $set['path'] = $path;
        $img = new \Common\Api\ImgApi();
        $json = $img->upload($set);
        if($json['code'] == 0){
            unset($json['realpath']);
        }
        $url = $json['url'];
        session('lunbo_imgurl',$url);
        
        $json['msg'] = getErrArr($json['code']);
        echo json_encode($json);
    }
}