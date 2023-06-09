<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================LIST permission=====================
if($_GET['action'] == 'list_permission'){ 

	if(in_array("su_permissions_list", $_SESSION['login_user_permissions'])){

	$finalAry = array();

	$permissions = "";
	
	if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
		//SUPER ADMIN

		if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){
		$permissions = $db->get_results("SELECT id, permission FROM ss_permissions where status!= 2  order by permission ASC",ARRAY_A);
	    }else{
	    $permissions = $db->get_results("SELECT id, permission FROM ss_permissions where status!= 2 AND public_access = 1 order by permission ASC",ARRAY_A);	
	    }
	}
	
	$finalAry['data'] = $permissions;
	echo json_encode($finalAry);
	exit;
}
}

//==========================ADD permission=====================
elseif($_POST['action'] == 'permission_add'){

 if(in_array("su_permissions_create", $_SESSION['login_user_permissions'])){
	$db->query('BEGIN');

	$check_permission = $db->query("SELECT * FROM ss_permissions where permission='".trim($db->escape($_POST['permission']))."' AND permission_name='".trim($db->escape($_POST['permission_name']))."' AND status <> 2 ");

	 if(empty($check_permission)){

	$permission = $db->query("insert into ss_permissions set permission='".trim($db->escape($_POST['permission']))."', permission_name='".trim($db->escape($_POST['permission_name']))."' ");		
    $permission_id = $db->insert_id;
	
	if($permission_id > 0 && $db->query('COMMIT') !== false){
		echo json_encode(array('code' => "1",'msg' => 'Permission added successfully'));
		exit;
	}else{
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0",'msg' => 'Permission not added');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}

	}else{
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0",'msg' => 'Permission name already used');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
}

//==========================EDIT permission =====================
elseif($_POST['action'] == 'permission_edit'){
 if(in_array("su_permissions_edit", $_SESSION['login_user_permissions'])){

	$id = $_POST['permission_id'];
	$db->query('BEGIN');
	
	$permission = $db->query("update ss_permissions set permission='".trim($db->escape($_POST['permission']))."', permission_name='".trim($db->escape($_POST['permission_name']))."' where id = '".$id."'");		
	
	if($permission && $db->query('COMMIT') !== false){
		echo json_encode(array('code' => "1",'msg' => 'Permission updated successfully'));
		exit;
	}else{
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0",'msg' => 'Permission not updated');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}

}

//=====================DELETE permissions==================
elseif($_POST['action'] == 'delete_permission'){
	 if(in_array("su_permissions_delete", $_SESSION['login_user_permissions'])){
	if(isset($_POST['id'])){

	$check_permission = $db->query("SELECT * FROM ss_role_wise_permissions where permission_id = '".$_POST['id']."' ");
    
    if(empty($check_permission)){

		$rec = $db->query("update ss_permissions  set status='2'  where id='".$_POST['id']."'");
		
		if($rec > 0){
			echo json_encode(array('code' => "1",'msg' => 'Permission deleted successfully'));
			exit;
		}else{
			$return_resp = array('code' => "0",'msg' => 'Permission not deletion');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	 }else{
			$return_resp = array('code' => "0",'msg' => 'Permission already used. not deleted');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	}else{
		$return_resp = array('code' => "0",'msg' => 'Error: Process failed');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
}







?>