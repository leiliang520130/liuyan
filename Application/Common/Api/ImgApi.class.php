<?php
/**
 * 图片处理类
 * 
 */

namespace Common\Api;
class ImgApi {
    private $maxSize = 31457280;//设置图片上传大小
    private $set = array('jpg', 'gif', 'png', 'jpeg');//设置图片格式
    private $sizes = array(50, 100, 200, 400, 600);//设置图片格式
    private $path = 'Default';

    /**
     * 图片上传
     * @param array $set
     *    $set['path'] 上传文件路径   格式 head  或者 head/xxx 表示一级或多级目录
     */
    public function upload($set){
        $upload = new UploadApi();
        $set['maxSize'] = $this->maxSize;
        $set['$set'] = $this->$set;
        return $upload->upload($set);
    }

    /**
     * [upload_img description]
     * @return [type] [description]
     */
    public function upload_file($set) {
        $upload = new UploadApi();
        $set['maxSize'] = $set['maxSize'] ? $set['maxSize'] : $this->maxSize;
        $set['path'] = $set['path'] ? $set['path'] : $this->path;
        $set['ext'] = $this->set;
        return $upload->upload($set);
    }
    
    /**
     * 图片调整固定大小
     * @param array $size
     *    $size 宽度
     */
    public function resizeCenter($path,$size){
        foreach ($this->sizes as $size){
            $image = new \Think\Image();
            $image->open($path);
            $ext = getLastPattern('.', $path);
            $len = strlen($ext) + 1;
            $new = substr($path,0, -$len).'@'.$size.'.'.$ext;
            $image->thumb($size, $size,\Think\Image::IMAGE_THUMB_FIXED)->save($new);
        }
    }
    
    public function getSize($path){
        $image = new \Think\Image();
        $image->open($path);
        return $image->width().','. $image->height();
    }
    
    /**
     * 图片处理类，用于在现有图片的基础上进行修改操作
     * 
     */
    public function oper($name,$path){
    	$imgs = $_POST[$name];
    	$fileArray = $_FILES[$name];//根据请求的name获取文件
    	$date = date('Y-m-d',NOW_TIME);
    	$base_path = 'Uploads/'.BIND_MODULE.'/'.$path.'/'.$date.'/';
    	$upload_dir = $_SERVER['DOCUMENT_ROOT'].__ROOT__.'/'.$base_path;
    	if (!is_dir($upload_dir)) mkdir($upload_dir,0777,true); // 如果不存在则创建
    	
    	foreach ($fileArray['error'] as $key => $error){  //遍历处理文件
    		if ( $error == UPLOAD_ERR_OK ) {
    			$check_result = $this->img_ctrl($fileArray,$key);
    			if($check_result === 0){
    				$temp_name = $fileArray['tmp_name'][$key];
    				$ext_arr = explode('.', $fileArray['name'][$key]);
    				$ext_name = $ext_arr[sizeof($ext_arr) - 1];		//后缀名
    				$file_name = substr(md5(NOW_TIME.$fileArray['name'][$key]),8,16).'.'.$ext_name;
    				
    				move_uploaded_file($temp_name, $upload_dir.$file_name);
    				$imgs[$key] = $base_path.$file_name;
    			}
    		}
    	}
    	ksort($imgs);
    	return implode(',', $imgs);
    }

    private function img_ctrl($file,$i){
    	if($file['size'][$i] > 3*1024*1024){
    		return -1005;
    	}
    	return 0;
    }
    private function get_extension($file){
    	return pathinfo($file, PATHINFO_EXTENSION);
    }
}