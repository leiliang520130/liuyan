<?php
/**
 * 文件上传类
 * 
 */

namespace Common\Api;
class UploadApi {
    
    /**
     * 
     * @param array $set
     *    $set['path'] 上传文件路径   格式 head  或者 head/xxx 表示一级或多级目录
     */
    public function upload($set){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     $set['maxSize'] ? $set['maxSize'] : 3145728 ;// 设置附件上传大小
        $upload->exts      =     $set['exts'] ? $set['exts'] : array('jpg', 'gif', 'png', 'jpeg','zip','rar');// 设置附件上传类型
        
        $path = '/Uploads'.(isset($set['path']) ? $set['path']: 'default/');//路径设置
        $uploadPath = C('REAL_PATH').$path; //上传路径
        FileApi::checks($uploadPath);     //检查目录
        $upload->rootPath  = $uploadPath; // 设置附件上传根目录
        
        // 上传文件
        $info   =   $upload->upload();
        $json = array();
        if(!$info) {// 上传错误提示错误信息
            $json['code'] = $upload->getError();
        }else{// 上传成功
            $json['code'] = 0;
            $json['url'] = $path.$info['photo']['savepath']
                            .$info['photo']['savename'];
            $json['realpath'] = C('REAL_PATH').$path.$info['photo']['savepath']
                            .$info['photo']['savename'];  
        }
        return $json;
    }
    
    public function dif_type_upload($files=''){
    	if('' === $files){
    		$files  =   $_FILES;
    	}
        // 对上传文件数组信息处理
        $files   =  $this->dealFiles($files);
        $file = $files['photo'];					//暂时只支持一次性只能上传一个
        $json = array();
        if($file){
        	$ext = strtolower(get_extension($file['name']));	//获取文件后缀名
        	if(in_array($ext, array('jpg', 'gif', 'png', 'jpeg','amr'))){	//该文件为图片
        		$upload = new UploadApi();
        		$set['maxSize'] = 3145728;
        		$set['exts'] =  array('jpg', 'gif', 'png', 'jpeg','amr');//设置图片格式;
        		$json = $upload->upload($set);
        		if(0 === $json['code']){
        			if(in_array($ext,array('amr'))){
        				$json['type'] = '3';
        			}else{
        				$json['type'] = '2';
        				$img = new ImgApi();
        				$json['size'] = $img->getSize($json['realpath']);
        			}
        			unset($json['realpath']);
        		}
        	}else{
        		$json['code'] = -1100;
        	}
        }else{
        	$json['code'] = -1101;
        }
        $json['msg'] = getErrArr($json['code']);
        return $json;
    }
    
    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files) {
    	$fileArray  = array();
    	$n          = 0;
    	foreach ($files as $key=>$file){
    		if(is_array($file['name'])) {
    			$keys       =   array_keys($file);
    			$count      =   count($file['name']);
    			for ($i=0; $i<$count; $i++) {
    				$fileArray[$n]['key'] = $key;
    				foreach ($keys as $_key){
    					$fileArray[$n][$_key] = $file[$_key][$i];
    				}
    				$n++;
    			}
    		}else{
    			$fileArray = $files;
    			break;
    		}
    	}
    	return $fileArray;
    }
}