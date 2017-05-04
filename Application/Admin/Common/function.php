<?php

/**
 * 发送邮件
 * @param  [type] $address [地址]
 * @param  [type] $subject [标题]
 * @param  [type] $content [内容]
 */
function send_email($address, $subject, $content){
	$email_smtp = C('STMP_SERVER');
	$email_username = C('EMAIL_USERNAME');
	$email_password = C('EMAIL_PASSWORD');
	$email_from_name = C('EMAIL_FROM_NAME');

	if(empty($email_smtp) || empty($email_username) || empty($email_password) || empty($email_from_name)){
		return array("code"=>-51,"msg"=>'邮箱配置不完整');
	}

	require './ThinkPHP/Library/Org/Nx/class.phpmailer.php';
	require './ThinkPHP/Library/Org/Nx/class.smtp.php';

	$phpmailer=new \Phpmailer();
	// 设置PHPMailer使用SMTP服务器发送Email
	$phpmailer->IsSMTP();
	// 设置为html格式
	$phpmailer->IsHTML(true);
	// 设置邮件的字符编码'
	$phpmailer->CharSet='UTF-8';
	// 设置SMTP服务器。
	$phpmailer->Host=$email_smtp;
	// 设置为"需要验证"
	$phpmailer->SMTPAuth=true;
	// 设置用户名
	$phpmailer->Username=$email_username;
	// 设置密码
	$phpmailer->Password=$email_password;
	// 设置邮件头的From字段。
	$phpmailer->From=$email_username;
	// 设置发件人名字
	$phpmailer->FromName=$email_from_name;
	// 添加收件人地址，可以多次使用来添加多个收件人
	if(is_array($address)){
		foreach($address as $addressv){
			$phpmailer->AddAddress($addressv);
		}
	}else{
		$phpmailer->AddAddress($address);
	}
	// 设置邮件标题
	$phpmailer->Subject=$subject;
	// 设置邮件正文
	$phpmailer->Body=$content;
	// 发送邮件。
	if(!$phpmailer->Send()) {
		$phpmailererror=$phpmailer->ErrorInfo;
		return array("code"=>-52,"msg"=>$phpmailererror);
	}else{
		return array("code"=>0);
	}
}

	/**
	 * ���� ����
	 * @param  [type] $pwd [description]
	 * @return [type]      [description]
	 */
	function encrypt_password($pwd) {
		return md5(md5($pwd.C('ENCRYPT_PASSWORD')));
	}









