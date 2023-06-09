<?php 
include_once "../includes/config.php";
 
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================CHANGE PASSWORD=====================
if($_POST['action'] == 'change_password'){
	$old_password = $_POST['old_password'];
	$new_password = $_POST['new_password'];
	$confirm_password = $_POST['confirm_password'];
	
	$passwordCheck = $db->get_row("select * from ss_user where username='".$_SESSION['icksumm_uat_login_username']."' and password='".md5($old_password)."'");
		
	if(!empty($passwordCheck)){
		if($new_password == $confirm_password){
			$ret_sql = $db->query("update ss_user set password='".md5($new_password)."' where id='".$_SESSION['icksumm_uat_login_userid']."'");
	
			if($ret_sql){
				echo json_encode(array('code' => "1",'msg' => 'Password changed successfully'));
				exit;
			}else{
				echo json_encode(array('code' => "0",'msg' => "Error: Old Password and New Password Can't be Same"));
				exit;
			}
		}else{
			echo json_encode(array('code' => "0",'msg' => "Error: Confirm password does not match"));
			exit;
		}
	}else{
		echo json_encode(array('code' => "0",'msg' => "Error: Old password does not match"));
		exit;
	}

}elseif($_POST['action'] == 'change_session'){
        if(isset($_POST['session'])){
			$_SESSION['icksumm_uat_CURRENT_SESSION'] = $_POST['session'];
			$target_url = SITEURL.'settings.php';
			echo json_encode(array('code' => 1,'msg' => 'Session set successfully, we are redirecting you to settings','target_url' => $target_url));
		}else{
			echo json_encode(array('code' => "0",'msg' => 'Failed session set.'));
		}
    
}

?>