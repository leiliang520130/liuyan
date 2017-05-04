<?php
	namespace Common\Api;
	class VerifyApi {
        public function VerCode(){
            if($_GET['type'] == 'pc'){
                $GtSdk = new \Common\Api\GeetestLibApi(C('CAPTCHA_ID'), C('PRIVATE_KEY'));
            }elseif ($_GET['type'] == 'mobile') {
                $GtSdk = new \Common\Api\GeetestLibApi(C('MOBILE_CAPTCHA_ID'), C('MOBILE_PRIVATE_KEY'));
            }

            $user_id = "cosense";
            $status = $GtSdk->pre_process($user_id);
            $_SESSION['gtserver'] = $status;
            $_SESSION['user_id'] = $user_id;
            
            return $GtSdk->get_response_str();
        }

        public function CheckVerCode() {
            if($_POST['type'] == 'pc'){
                $GtSdk = new \Common\Api\GeetestLibApi(C('CAPTCHA_ID'), C('PRIVATE_KEY'));
            }elseif ($_POST['type'] == 'mobile') {
                $GtSdk = new \Common\Api\GeetestLibApi(C('MOBILE_CAPTCHA_ID'), C('MOBILE_PRIVATE_KEY'));
            }

            $user_id = $_SESSION['user_id'];
            if ($_SESSION['gtserver'] == 1) {   //服务器正常
                $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $user_id);

                if ($result) {
                    return true;
                } else{
                    return false;
                }
            }else{  //服务器宕机,走failback模式
                if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                    return true;
                }else{
                    return false;
                }
            }
        }
	}
?>