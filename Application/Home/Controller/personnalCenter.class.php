<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/20
 * Time: 12:02
 */

namespace Home\Controller;

class personnalCenter extends BaseController
{
    /**
     * 获取用户fans
     *
     */
    public function user_fans()
    {
        $fans=M('fans')->alias('a')->field('a.fans_id,u.*')
            ->join('left join cos_user u ON u.id=a.fans_id')
            ->where(array('uid'=>session('user.uid')))->select();
        if(empty($fans)) $fans=array();

        foreach($fans as$key=> $fan){
            $arts=M('original_article')->field('cover_img')->where(array('uid'=>$fan['fans_id']))->limit(0,3)->select();
            $fans[$key]['cover_img']=$arts;
        }
        
        $this->assign('fans',$fans);
        $this->display();
    }
    
    
    /**
     * 获取我的关注
     */
    public function person_follow()
    {
        $fans=M('fans')->alias('a')->field('a.uid,u.*')
            ->join('left join cos_user u ON u.id=a.uid')
            ->where(array('fans_id'=>session('user.uid')))->select();
        if(empty($fans)) $fans=array();

        foreach($fans as$key=> $fan){
            $arts=M('original_article')->field('cover_img')->where(array('uid'=>$fan['uid']))->limit(0,3)->select();
            $fans[$key]['cover_img']=$arts;
        }

        $this->assign('fans',$fans);
        $this->display();
    }

    /**
     * 添加收藏夹,修改收藏夹
     * $id 表示收藏夹的id,如果是新增不传id,如果是修改则需要传递id
     * $collect_name 表示收藏夹名字
     * $img 表示收藏夹的封面图片
     *
     */
    public function collect_add()
    {
        $id=I('post.id');

        $col_name=I('post.name');

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
                $msg='新增成功';
            }
        }else{
            $res=M('collection_table')->where(array('id'=>$id))->save(array( 'collection_table_name'=>$col_name));
            if($res===false){
                $code=-2;
                $msg='修改数据保存不成功';
            }else{
                $code=0;
                $msg='修改成功';
            }
        }
        $data=array('code'=>$code,'msg'=>$msg);
        $this->ajaxReturn($data,'json');
    }



    /**
     * 获取我的收藏夹
     */
    public function collection()
    {
        $model=M("collection_table");
        $collect= $model->where(array('uid'=>session('user.uid')))->select();

        $this->assign('collect',$collect);
        $this->display();
    }

    /**
     * 获取收藏夹里面的收藏文章
     * $id 表示收藏夹id type 表示文章类型
     */
    public function collect_article($id='',$type=1)
    {
        $model=M('collection');
        $M1=M('fine_articles');//精选
        $M2=M('original_article');//原创
        $M3=M('activity_articles');//活动
        $msp=array('d.collection_table_id'=>$id,'d.collection_type'=>$type);

        if($type==1){//可看
            $res=$model->field('d.*,b.cover_img,b.title,b.id article_id,b.intro')
                ->join('left join cos_fine_articles b ON b.id=d.works_id')->where($msp)->select();

        }else if($type==2){//可想
            $res=$model->field('d.*,b.cover_img,b.title,b.id article_id,b.intro')
                ->join('left join cos_original_article b ON b.id=d.works_id')->where($msp)->select();

        }else if($type==3){//可玩
            $res=$model->field('d.*,b.cover_img,b.title,b.id article_id,b.intro')
                ->join('left join cos_activity_articles b ON b.id=d.works_id')->where($msp)->select();
        }
        if(empty($res)) $res=array();
        $this->assign('data',$res);
        $this->display();

    }

    /**
     * 收藏转移,
     * $id 表示表示收藏id
     * $cid 表示转移到某一个收藏夹的id
     */

    public function trans_article()
    {
        $id=I('post.id');
        $cid=I("post.cid");
        $M=M('collection');

        //根据原来的id找到对应的作品id
        $art=$M->field('d.*,b.collect_nums')
            ->join('left join cos_collection_table b ON b.id=d.collection_table_id')
            ->where(array('id'=>$id))->find();
        //如果$id 和$cid 其中的一个不存在都无法转移成功
        if(!$art){
            $code=-1;
            $msg='收藏文章不存在或已被删除';
        }
        $collect=M('collection_table')->where(array('id'=>$cid))->find();

        if(!$collect){
            $code=-2;
            $msg='收藏夹不存在或已被删除,请新建收藏夹';
        }
        
        $msp=array('collection_table_id'=>$cid,'works_id'=>$art['works_id']);
        //执行转移功能
        if($id && $cid){
           $res=$M->data($msp)->add();
            //需要将新的收藏夹的数量+1,需要将原来收藏夹的数量-1
            M('collection_table')->where(array('id'=>$cid))->save(array('collect_nums'=>($collect['collect_nums']+1)));
            if($res){
               $M->where(array('id'=>$id))->delete();
                M('collection_table')->where(array('id'=>$art['collection_table_id']))
                    ->save(array('collect_nums'=>($art['collect_nums']-1)));
                $code=0;
                $msg='作品转移成功';
            }
        }
        $data=array('code'=>$code,'msg'=>$msg);
        $this->ajaxReturn($data);
    }
    
    
    /**
     * 收藏删除,删除收藏夹,
     * $id 表示收藏夹id
     * 根据收藏夹id 找到对应的收藏夹下面是否存在收藏的文章
     * 如果存在则提示:收藏夹里存在收藏文章是否删除
     * 不存在直接删除
     */

    public function collect_queren()
    {
        $id=I('post.id');
        $M=M('collection');
        $arr=$M->where(array('collection_table_id'=>$id))->select();

        if($arr){
            $code=1;
            $msg='该收藏夹中存在收藏文章,如果删除,收藏文章会丢失,是否确认删除';
        }else{
            $code=0;
            $msg='是否确认删除该收藏夹';
        }
        $data=array('code'=>$code,'msg'=>$msg);
        $this->ajaxReturn($data);
    }

    /**
     * $id 表示执行删除
     */
    public function collect_del()
    {
        $M=M('collection_table');
        $id=I('post.id');

        $res=$M->where(array('id'=>$id))->delete();
        if($res){
            $code=0;
            $msg='删除成功';
        }
        $data=array('code'=>$code,'msg'=>$msg);
        $this->ajaxReturn($data);
    }

    /**
     * 删除收藏的文章
     * $id 表示收藏表中的id
     * 同时需要更新收藏表中的数量字段信息
     */
    public function del_collect_art()
    {
        $id=I('post.id');

        $M=M('collection');

       $col= $M->where(array('id'=>$id))->find();
        if(!$col){
            $code=-1;
            $msg='收藏文章不存在';
        }else{
          $r=$M->where(array('id'=>$id))->delete();
            if($r){
                $code=0;
                $msg='删除成功';
            }
        }
        $data=array('code'=>$code,'msg'=>$msg);

        $this->ajaxReturn($data,'json');
    }


    /********************************************消息开始**************************************************/
    /**
     * 获取系统消息
     * 
     */
    public function get_sys_msg()
    {

    }
    
    
    
    
    
    

}