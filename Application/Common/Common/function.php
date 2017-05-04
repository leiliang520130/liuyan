<?php

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 检测用户是否登录
 *
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login() {
    $user = session ( 'user_auth' );
    $cuser = cookie( 'user_auth' );
/*  $json = array();
    $json['user'] = $user;
    $json['cuser'] = $cuser;
    echo json_encode($json); */
    if (empty ( $user ) && empty($cuser)) {
        return 0;
    } else {
        $M = D('AdminMember');
        if(empty ( $user )){
            return $M->auto($cuser);
        }else{
            return true;
        }
    }
}
function check_logins(){
    if( !is_index_login() ){
        echoResult(-7);
        exit();
    }
}

/**
 * 获取搜索关键字
 */
function get_keys() {
    //1、获取用户设置的关键字
    $path = CONF_PATH.'search_key.php';
    $keys = include($path);

    //2、获取当前搜索的热词
    $rst_lst = array();
    $lst = M('to_search')->order('num desc')->limit(0,16)->select();
    if($lst) {
        if(count($lst) >= 3) {
            foreach ($lst as $k => $v) {
                if($k == 2) {
                    if($keys['KEY_1']) $rst_lst[] = $keys['KEY_1'];
                    if($keys['KEY_2']) $rst_lst[] = $keys['KEY_2'];
                }

                $rst_lst[] = $v['skey'];
            }
        }else{
            foreach ($lst as $k => $v) {
                $rst_lst[] = $v['skey'];
            }
            if($keys['KEY_1']) $rst_lst[] = $keys['KEY_1'];
            if($keys['KEY_2']) $rst_lst[] = $keys['KEY_2'];
        }
    }else{
        if($keys['KEY_1']) $rst_lst[] = $keys['KEY_1'];
        if($keys['KEY_2']) $rst_lst[] = $keys['KEY_2'];
    }
    $rst_lst = array_unique($rst_lst);

    while(count($rst_lst) > 16) {
        array_pop($rst_lst);
    } 

    return $rst_lst;
}

/*
*input
*需要取出数组列的多维数组（或结果集）
*column_key
*需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。 也可以是NULL，此时将返回整个数组（配合index_key参数来重置数组键的时候，非常管用）
*index_key
*作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
*/

if(!function_exists('array_column')){
    function array_column($input , $columnKey , $indexKey = null){
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array) $input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
            if ($indexKeyIsNumber) {
                $key = array_slice($row, $indexKey, 1);
                $key = (is_array($key) && !empty($key)) ? current($key) : null;
                $key = is_null($key) ? 0 : $key;
            }else{
                $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
            }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}
/**
 * 检测用户是否登录
 *
 * @return integer 0-未登录，大于0-当前登录用户ID
 */

function is_index_login() {
    $user = session ( 'user_auth' );
    $cuser = cookie( 'user_auth' );
    if (empty ( $user ) && empty($cuser)) {
        return 0;
    } else {
        $M = D('Member');
        if(empty ( $user )){
            return $M->auto($cuser);
        }else{
            return $M->get_self($user);
        }
    }
}
/**
 * 用户信息更新
 *
 */
function sessionUserInfoUpdate(){
            $uid = session("user.uid");
            $info = M("user")->alias("a")->field("a.id as uid,a.nickname,a.email,a.avatar")->where(array("id"=>$uid))->find();
            session("user",$info);
}
/**
 * 获取汉字的首字母
 *
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function getFirstChar($s){
        $s0 = mb_substr($s,0,1,'utf-8');       //获取名字的姓  
            $s = iconv('UTF-8','gb2312', $s0);       //将UTF-8转换成GB2312编码  
            if (ord($s0)>128) {                      //汉字开头，汉字没有以U、V开头的  
                $asc=ord($s{0})*256+ord($s{1})-65536;  
                if($asc>=-20319 and $asc<=-20284)return "A";  
                if($asc>=-20283 and $asc<=-19776)return "B";  
                if($asc>=-19775 and $asc<=-19219)return "C";  
                if($asc>=-19218 and $asc<=-18711)return "D";  
                if($asc>=-18710 and $asc<=-18527)return "E";  
                if($asc>=-18526 and $asc<=-18240)return "F";  
                if($asc>=-18239 and $asc<=-17923)return "G";  
                if($asc>=-17922 and $asc<=-17418)return "I";               
                if($asc>=-17417 and $asc<=-16475)return "J";               
                if($asc>=-16474 and $asc<=-16213)return "K";               
                if($asc>=-16212 and $asc<=-15641)return "L";               
                if($asc>=-15640 and $asc<=-15166)return "M";               
                if($asc>=-15165 and $asc<=-14923)return "N";               
                if($asc>=-14922 and $asc<=-14915)return "O";               
                if($asc>=-14914 and $asc<=-14631)return "P";               
                if($asc>=-14630 and $asc<=-14150)return "Q";               
                if($asc>=-14149 and $asc<=-14091)return "R";               
                if($asc>=-14090 and $asc<=-13319)return "S";               
                if($asc>=-13318 and $asc<=-12839)return "T";               
                if($asc>=-12838 and $asc<=-12557)return "W";               
                if($asc>=-12556 and $asc<=-11848)return "X";               
                if($asc>=-11847 and $asc<=-11056)return "Y";               
                if($asc>=-11055 and $asc<=-10247)return "Z";   
        }else if(ord($s)>=48 and ord($s)<=57){ //数字开头  
            switch(iconv_substr($s,0,1,'utf-8')){  
                case 1:return "Y";  
                case 2:return "E";  
                case 3:return "S";  
                case 4:return "S";  
                case 5:return "W";  
                case 6:return "L";  
                case 7:return "Q";  
                case 8:return "B";  
                case 9:return "J";  
                case 0:return "L";  
            }                 
        }else if(ord($s)>=65 and ord($s)<=90){ //大写英文开头  
            return substr($s,0,1);  
        }else if(ord($s)>=97 and ord($s)<=122){ //小写英文开头  
            return strtoupper(substr($s,0,1));  
        }  
        else  
        {  
            return iconv_substr($s0,0,1,'utf-8');  
            //中英混合的词语，不适合上面的各种情况，因此直接提取首个字符即可  
        }  
 }
/**
 * 按字母排序当前根据 其中字段名字
 *$userName数组
 *$strname字段名
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function orderByName($userName,$strname=""){  
       sort($userName);  
       foreach($userName as $name){
            $char = getFirstChar($name[$strname]);  
            $nameArray = array();//将姓名按照姓的首字母与相对的首字母键进行配对  
            if(count($charArray[$char])!=0){  
                $nameArray = $charArray[$char];  
            }  
            array_push($nameArray,$name);   
            $charArray[$char] = $nameArray;  
        }  
        p($charArray);die;
        return $charArray;  
}  

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($uid = null) {
    $uid = is_null ( $uid ) ? is_login () : $uid;
    return $uid && (intval ( $uid ) === C ( 'USER_ADMINISTRATOR' ));
}

/**
 * 时间戳格式化
 *
 * @param int $time
 * @return string 完整的时间显示
 * 
 */
function time_format($time = NULL, $format = 'Y-m-d H:i') {
    if (empty ( $time ))
        return '';

    $time = $time === NULL ? NOW_TIME : intval ( $time );
    return date ( $format, $time );
}

/**
 * 判断字符串以什么开头
 */
function startWith($str, $needle) {
    return strpos($str, $needle) === 0;

}

/**
 * 判断字符串以什么结束
 */
function endWith($str, $needle) {   
    $length = strlen($needle);  
    if($length == 0){    
        return true;  
    }  
    return (substr($str, -$length) === $needle);
}
 
/**
 * 把数组转换为字符串
 *     例如[1,2,3] 转换为1,2,3格式，反之则只需要explode一下即可
 * @param 需要转换的数组    
 */
function arrToStr($rules){
    $strRules = '';
    foreach ($rules as $rule){
    if(!empty($strRules))
        $strRules .= ',';
        $strRules.=$rule;
    }
    return $strRules;
}
 
/**
 * 把结果集转换为以唯一字段为键值的map
 * @param
 *     $lists  结果集
 *     $field  主键字段
 */
function listToMap($lists,$field='id'){
    $map = array();
    foreach ($lists as $list){
        $map[$list[$field]] = $list;
    }
    return $map;
}
 
/**
 * 把结果集转换为以唯一字段为键值的map
 * @param
 *      $lists  结果集
 *      $field  主键字段
 * @return 
 *      maps
 *          key : str   $field
 *          value:array $arr   
 */
function listToMapArr($lists,$field){
    $map = array();
    $arr = array();
    foreach ($lists as $list){
        $map[$list[$field]][] = $list;
    }
    return $map;
}
 
 /**
  * 获取tp In条件所需要的值
  * 
  */
function getInField($lists,$field){
    $mids = '';
    foreach($lists as $list){
        if(!empty($mids))
            $mids .= ',';
        $mids.=$list[$field];
    }
    return $mids;
}

/**
 * 手机号码校对
 * @param int/string $phone  手机号码
 *        
 * @return boolean $r  验证结果
 */ 
function checkPhone($phone){
    $pattern = '/1\d{10}$/';
    return preg_match($pattern, $phone);
} 

/**
 * 手机号码校对
 * @param int/string $phone  手机号码
 *        
 * @return boolean $r  验证结果
 */ 
function checkPayPwd($pwd){
    $pattern = '/\d{6}$/';
    return preg_match($pattern, $pwd);
} 

/**
 * 用户名/密码校验
 * @param int/string $uname  用户名/密码
 *
 * @return boolean $r  验证结果
 */
function checkUsername($uname){
    $pattern = '/^[A-Za-z0-9\!\@\#\$\%\^\&\*\.]{6,22}$/';
    return preg_match($pattern, $uname);
}

/**
 * 用户名/密码校验
 * @param int/string $uname  用户名/密码
 *
 * @return boolean $r  验证结果
 */
function checkLenth($address,$len){
    if(strlen($address) <= $len){
        return 1;
    }
}

/**
 * 邮箱校对
 * @param string $email  邮箱
 *
 * @return boolean $r  验证结果
 */
function checkEmail($email){
    $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    return preg_match($pattern, $email);
}


/**
 * http post请求
 * @param str $url  请求的url地址
 * @param unknown $data 发送的数据
 * @return $result  返回值
 */
function curlPost($url, $data){
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        echo 'Errno'.curl_error($curl);//捕抓异常
    }
    $result = json_decode($tmpInfo);
    return $result;
}

function getErrArr($code){
    $errorArr = array(
        '1' =>   '您已登陆',
        '-1' =>  '失败',
        '-2' =>  '验证码错误',
        '-3' =>  '电话号码格式不正确',
        '-4' =>  '电话号码已被注册',
        '-5' =>  '密码格式错误，请输入6-16位字符',
        '-6' =>  '账号或密码错误，请重新输入',
        '-7' =>  '您尚未登陆',
        '-8' =>  '您输入的账号不存在，请重新输入',
        '-9' =>  '未知错误',
        '-10' => '短信发送失败',
        '-11' => '验证码已过期',
        '-12' => '参数错误',
        '-13' => '参数已存在',
        '-14' => '该账号已被锁定',
        '-15' => '旧密码错误',
        '-16' => '该用户已存在，请到用户列表里设置该用户为管理员',
        '-17' => '对不起，您的余额不足，请充值。',
        '-18' => '账户更新失败，请检查。',
        '-19' => '生成红包失败',
        '-20' => '支付密码格式不正确',
        '-21' => '余额不足，请多参与红包抽奖',
        '-22' => '提现密码不正确，请重新输入',
        '-23' => '支付密码不能和登录密码相同',
        '-24' => '此账号已被注册',
        '-25' => '输入成功',
        '-26' => '输入失败',
        '-27' => '登陆成功',
        '-28' => '密码输入错误',
        '-29' => '作品上传成功',
        '-30' => '作品上传失败',
        '-31' => '您还没有收藏夹,请新建',
        '-32' => '收藏成功',
        '-33' => '收藏失败',
        '-34' => '请勿重复收藏',
        '-35' => '保存成功',
        '-36' => '保存失败',
        '-37' => '申请成功',
        '-38' => '申请失败',
        '-39' => '修改成功',
        '-40' => '修改失败,与原标题一致',
        '-41' => '关注成功',
        '-42' => '关注失败',
        '-43' => '您已经关注过了',
        '-44' => '不能关注自己!',
        '-45' => '创建成功',
        '-46' => '创建失败',
        '-47' => '没有更多作品了',
        '-48' => '取消收藏成功',
        '-49' => '取消收藏失败',
        '-50' => 'banner图片存放数据库成功',
        '-51' => 'banner图片存放数据库失败',
        '-52' => '没有更多用户了',
        '-53' => '没有更多活动了',
        '-54' => '不能关注自己',
        '-55' => '昵称已存在',
        '-56' => '邮箱没有验证',
        '-57' => '邮箱1已被注册',
        '-101' => '该IP当天短信已发送完了，请明天继续',
        '-102' => '该手机号码当天短信已发送完了，请明天继续',
        '-103' => '数据写入错误',
        '-104' => '您的访问过快，请稍后再试',
        '-105' => '您未选择dna',
        '-106' => '删除失败',
        '-107' => '收藏夹不能重复',
        '106' => '删除成功',
        '200'  => '点赞成功',
        '-200'  => '点赞失败',
        '201'  => '取消点赞成功',
        '-201'  => '取消点赞失败',
        '-201'  => '请勿重复点赞',
        '-1001' => '没有上传的文件！',
        '-1002' => '非法图像文件！',
        '-1003' => '未知上传错误！',
        '-1004' => '非法上传文件！',
        '-1005' => '上传文件大小不符！',
        '-1006' => '上传文件MIME类型不允许！',
        '-1007' => '上传文件后缀不允许',
        //'上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！',
        '-1008' => '上传文件超过了服务器设置的限制',
        //'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！',
        '-1009' => '上传文件超过了网页设置的限制',
        '-1010' => '文件只有部分被上传！',
        '-1011' => '没有文件被上传！',
        '-1012' => '找不到临时文件夹！',
        '-1013' => '文件写入失败！',
        '-1014' => '未知上传错误！',
        '-1015' => '文件命名规则错误！',
        '-1100' => '群聊上传文件格式不正确',
        '-1101' => '没有上传的文件！',      
        '-1102' => '发送失败！',         
        '-1103' => '您已被禁言！',    
        '-1200' => '该节目未找到！',   
        '-1201' => '该时间段冲突，请重新选择时间！',
        '-1202' => '节目已经开始录制，请不要重复操作！',
        '-1203' => '结束时间必须大于开始时间！',
        '-1204' => '请不要重复点赞！',
        '-1205' => '推送消息失败！',
        '-1206' => '请选择频道！',
        '-10001' => '奖品被抢光了！',
        '-10002' => '活动尚未开始！',
        '-10003' => '您已领取该礼品！',
        '-10004' => '该礼品已过有效期！',
        '-10005' => '对不起，该用户没有充值的权限！',
        '-20001' => '未中奖',
        '-20002' => '抽奖礼品不存在',
        '-20003' => '抽奖频率太快',
        '-20004' => '活动已结束', 
        '-20005' => '请选择要@的用户',
        '-30000' => '参数错误',
        '-30001' => '分组名重复',
        '-30002' => '系统错误',
        '-30003' => '标签已存在',
        '-30004' => '您已经屏蔽了该用户',
        '-30005' => '您已经对该用户解除了屏蔽',
        '-30006' => '您已经关注了该用户',
        '-30007' => '您已经对该用户取消了关注',
        '-30008' =>'该分组已经存在',
        '-30009' =>'分组名称已存在',
        '-30010' =>'内容涉及敏感文字',
        '0'         =>        '成功',
        '-100'      =>        '请输入用户名或密码',  
        '-101'      =>        '用户名密码错误',
        '100' =>'你已点赞',
        '101' =>'点赞失败',
        '101' =>'取消点赞失败',
		'-30011' =>'没有更多组织机构了',
    );
    return $errorArr[$code];
}


function getFucArr($code){
    $errorArr = array(
            '0' =>   '成功',
            '-1' =>  '参数错误',
            '-2' =>  '您转换的时间格式错误',
    );
    return $errorArr[$code];
}

/**
 * 通用提示信息输出
 * 
 */
function echoResult($code){
    $json = array();
    $json['code'] = $code;
    $json['msg'] = getErrArr($code);
    echo json_encode($json);
    exit;
}


/**
 * 通用提示信息输出，带数据输出
 *  
 */
function echoDataResult($code,$data = array()){
    $json = array();
    $json['code'] = $code;
    $json['msg'] = getErrArr($code);
    $json['data'] = $data;
    echo json_encode($json);
    exit;
}


//后台管理系统菜单过滤
function getMenus(){
    $M = M('AdminAuthMenuAccess');
    $map['uid'] = session('user_auth.uid');
    $menuAccesses = $M->where($map)->select();
    $ids = '';
    foreach ($menuAccesses as $menuAccess){
        if(!empty($ids)){
            $ids .= ',';
        }
        $ids .= $menuAccess['menu_id'];
    }
    $memuMap['id'] = array('IN',$ids);
    $menuModel = M('AdminAuthMenu');
    $menus = $menuModel->where($memuMap)->select();

    $url = array();
    foreach ($menus as $menu){
        $url[] .= $menu['name'];
    }
    return $url;
}

/**
 * 获取最后个$pattern后面的内容
 * @param str $str  需要处理的字符串
 * @param unknown $pattern  规则，如'.'
 */
function getLastPattern($pattern,$str){
    $arr = explode($pattern, $str);
    $num = sizeof($arr);
    return $arr[$num-1];
}

/**
 * 获取星座的相关信息
 * @return array
 */

function getConstellations(){
    $M = M('Constellation');
    $data = $M->field('id,name')->order('id asc')->select();
    return $data ? $data : array(); 
}

function getConstellationsById(){
    return listToMap(getConstellations());
}

function get_code($id){
	$pre_id=M('company')->where(array('enabled'=>1))->getField('cnt');
    $id = (string)$id;
    $num = strlen($id);
//    $pre_id='';
    if($num<6){
        for($i=0;$i<(6-$num);$i++){
            $pre_id .= '0';
        }
        $id = $pre_id.$id;
        return $id;
    }else{
        return $id;
    }

}

/**
 * 获取所在地的相关信息
 * @return array
 */
function getCities(){
    $M = M('AreaCity');
    $data = $M->field('id,name')->order('id asc')->select();
    return $data ? $data : array(); 
}

function getCitiesById(){
    return listToMap(getCities()); 
}

/**
 * 获取DNA的相关信息
 * @return array
 */
function getDna(){
    $M = M('Dna');
    $data = $M->field('id,name,icon,level')->order('id asc')->select();
    return $data ? $data : array(); 
}

function getDnaById(){
    return listToMap(getDna());
}

function get_token(){
    return rand(1000,9999);
}

//获取后缀名
function get_extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

//对象转数组,使用get_object_vars返回对象属性组成的数组
function objectToArray($obj){
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($arr)){
        return array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

//数组转对象
function arrayToObject($arr){
    if(is_array($arr)){
        return (object) array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

//时间转换工具，转换h:i:s格式时间为int类型
function getTime($time){
    if(isset($time)){
        $time_arr = explode(':', $time);
        $times = 0;
        foreach ($time_arr as $k=>$t){
            $t = intval($t);
            if($t > 60){
                return -2;
            }
            $times += $t*pow(60,(2-$k));
        }
        return $times;
    }
}

//把时间转换为字符串
function getStrTime($timestamp){
    if(isset($timestamp) && $timestamp < 24*3600){
        $time = '';
        for ($i=2; $i>=0; $i--){
            $temp = intval($timestamp/pow(60,$i));
            $timestamp -= $temp*pow(60,$i);
            if($temp < 10){
                $temp = '0'.$temp;
            }
            $time .= $temp;
            if($i != 0){
                $time .= ':';
            }
        }
        return $time;
    }
}

//把当前时间转换为H:i:s格式字符串
function getNowTime(){
    return getTime(date('H:i:s',NOW_TIME));
}

function curl_get($url, $data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT,5);   //只需要设置一个秒的数量就可以
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
/**
 * 格式化打印
 */
function p($arr){
    header("Content-type:text/html;charset=utf-8");
    echo "<pre>";
    print_r($arr);
}
/**
 * 
 */
function curPageURL(){
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on") 
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80") 
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
    } 
    else 
    {
        $pageURL .= $_SERVER["SERVER_NAME"];
    }
    return $pageURL;
}


//使用uasort进行排序
function re_uasort($a,$b){
    if ($a==$b) return 0;
    return ($a<$b)?-1:1;
}
//使用uasort进行倒叙排序
function re_uasort_desc($a,$b){
    if ($a==$b) return 0;
    return ($a>$b)?-1:1;
}

/**
 * PHP获取多个随机整数
 * 
 */
function multi_rand($begin, $end, $count){
    $rand_array = array();
    if ( $count > ($end - $begin + 1)) {
        $count = ($end - $begin + 1);
    }
    for ($i = 0;$i < $count; $i++ ) {
        $is_ok = false;
        $num = 0;
        while(!$is_ok){
            $num = rand($begin,$end);
            $is_out = 1;
            foreach ( $rand_array as $v) {
                if ( $v == $num ) {
                    $is_ok = false;
                    $is_out = 0;
                    break;
                }
            }
            if ($is_out == 1) {
                $is_ok = true;
            }
        }
        $rand_array[] = $num;
    }
    return $rand_array;
}

function get_first_day(){
    $date=new \DateTime();
    $date->modify('this week');
    $first_day_of_week=$date->format('Y-m-d');
}

function first_day_of_week(){
    $date=new \DateTime();
    $date->modify('this week');
    $first_day_of_week=strtotime($date->format('Y-m-d'));
    $today = date('Y-m-d');
    if(strtotime($today) == $first_day_of_week - 24*60*60){
        $first_day_of_week = $first_day_of_week - 7*24*60*60;
    }
    return $first_day_of_week;
}

function end_day_of_week(){
    $date=new \DateTime();
    $date->modify('this week +6 days');
    $end_day_of_week=$date->format('Y-m-d');
    return $end_day_of_week;
}

//判断是否是周末
function isWeekend(){
    $a = date("w",NOW_TIME);
    if($a =="0"){
        return 0;
    }else if($a=="6"){
        return 1;
    }else{
        return 2;
    }
}

//判断是否是周末
function isnWeekend(){
    $a = date("w",NOW_TIME);
    if($a =="0"){
        return 3;
    }else if($a=="6"){
        return 2;
    }else{
        return 1;
    }
}

function get_code1(){
    return date('ymd').substr(implode(NULL, array_map('ord',
            str_split(substr(uniqid(), 7, 13), 1))), 0, 8).generate_code();
}

function generate_code($length = 2) {
    return rand(pow(10,($length-1)), pow(10,$length)-1);
}
//UUID
function create_uuid($prefix = ""){    //可以指定前缀
    $str = md5(uniqid(mt_rand(), true));
    $uuid  = substr($str,0,8) . '-';
    $uuid .= substr($str,8,4) . '-';
    $uuid .= substr($str,12,4) . '-';
    $uuid .= substr($str,16,4) . '-';
    $uuid .= substr($str,20,12);
    return $prefix . $uuid;
}


/**
 * 需要选定频道
 */
function _c_channel(){
    // 获取当前用户ID
    $tcid = I('cid');
    if(empty($tcid)){
        //未选择频道
        $code = -1206;
        echoDataResult($code);
    }
}

/**
 * 获取生日接口
 * @param unknown_type $birthday
 * @return boolean|number
 */
function birthday($age){
    if($age === false){
        return false;
    }
    list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
    $now = strtotime("now");
    list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
    $age = $y2 - $y1;
    if((int)($m2.$d2) < (int)($m1.$d1))
        $age -= 1;
    return $age;
}

function getKey_arr($array){
    foreach($array as $v){
        $key = array_keys($v);
    }
    foreach($key as $kv){
        $new[$kv][] = $v[$kv]; 
    }
    return $new;
}//获取二维数组中某值

/**
 * 获取菜单列表
 * @return [type] [description]
 */
function my_menu_lst() {
    $uid = session('admin_info.id');
    $r_lst = M('auth_group_access')->alias('a')
                                   ->field('b.rules')
                                   ->join('cos_auth_group b ON a.group_id=b.id')
                                   ->where(array('a.uid'=>$uid))
                                   ->find();
    $m_lst = M('auth_rule')->where(array('id'=>array('in', $r_lst['rules'])))->order('id asc')->select();
    $menu_lst = array();
    $d = new \Common\Api\DataApi();
    $menu_lst = $d->channelLevel($m_lst, 0, "&nbsp;", 'id');

    return $menu_lst ? $menu_lst : array();
}

//base64位图片保存
function base64imgsave($img){
        //文件夹日期
        $ymd = date("Ymd");
         //图片路径地址   
        $basedir = C('REAL_PATH').'/Uploads/base64/'.$ymd.'';
        $fullpath = $basedir;
        if(!is_dir($fullpath)){
            mkdir($fullpath,0777,true);
        }
        $types = empty($types)? array('jpg', 'gif', 'png', 'jpeg'):$types;
        $img = str_replace(array('_','-'), array('/','+'), $img);
        $b64img = substr($img, 0,100);
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $b64img, $matches)){
            $type = $matches[2];
            if(!in_array($type, $types)){
                return array('status'=>1,'info'=>'图片格式不正确，只支持 jpg、gif、png、jpeg','url'=>'');
            }
            $img = str_replace($matches[1], '', $img);
            $img = base64_decode($img);
            $photo = '/'.md5(date('YmdHis').rand(1000, 9999)).'.'.$type;
            file_put_contents($fullpath.$photo, $img);
            $ary['status'] = 1;
            $ary['info'] = '保存图片成功';
            $ary['url'] = '/Uploads/base64/'.$ymd.''.$photo;
            return $ary;
        }
        $ary['status'] = 0;
        $ary['info'] = '请选择要上传的图片';
        return $ary;
    }