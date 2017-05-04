<?php

/**
 * 文件操作Api
 *
 */
namespace Common\Api;
class FileApi{
    function check($dr,$mode=0777){
        if (!is_dir($dr)) mkdir($dr,$mode,true); // 如果不存在则创建
    }
    
    //创建多级目录，并设置权限
    static function checks($dr,$mode=0777){
        if (!is_dir($dr)) mkdir($dr,$mode,true); // 如果不存在则创建
    }
}