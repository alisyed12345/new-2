<?php
session_start();
include_once "includes/config.php";

//UT02 for Teacher
if(check_userrole_by_code('UT02')){
	if(isset($_COOKIE["member_login"])) {
		setcookie ("member_login","",time(),'/');
	}
	if(isset($_COOKIE["member_password"])) {
		setcookie ("member_password","",time(),'/');
	}
	if(isset($_COOKIE["member_typecode"])) {
		setcookie ("member_typecode","",time(),'/');
	}
}

$lastLoginId = $db->get_var("select id from ss_loginhistory where user_id='".$_SESSION['icksumm_uat_login_userid']."' order by id desc limit 1");
$db->query("update ss_loginhistory set logout_datetime='".date('Y-m-d H:i:s')."' where id = '".$lastLoginId."'");
session_destroy();
header("location:".SITEURL."login");
?>