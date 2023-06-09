<?php
include_once "../includes/config.php";

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}

//==========================LIST ROLE=====================
if ($_GET['action'] == 'list_role') {

	if (in_array("su_role_list", $_SESSION['login_user_permissions'])) {

		$finalAry = array();
		$roles = "";

		if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') {
			$roles = $db->get_results("SELECT ss_role.id,ss_role.access, ss_role.role,ss_usertype.user_type_code FROM ss_role INNER JOIN `ss_usertype` ON `ss_role`.`id`=`ss_usertype`.`role_id` where status = 1 AND public = 1 AND is_default = 0  order by role ASC", ARRAY_A);
		} else {
			$roles = $db->get_results("SELECT ss_role.id,ss_role.access, ss_role.role,ss_usertype.user_type_code FROM ss_role INNER JOIN `ss_usertype` ON `ss_role`.`id`=`ss_usertype`.`role_id` where status = 1 AND public = 1 AND is_default = 0  order by role ASC;", ARRAY_A);
		}

		//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
		//SUPER ADMIN
		//	$roles = $db->get_results("SELECT id , role FROM ss_role where public = 1 ORDER BY role ASC",ARRAY_A);

		for ($i = 0; $i < count((array)$roles); $i++) {
			if($roles[$i]['user_type_code'] =='UT01'){
				$roles[$i]['access']="Full Access";
			}else{
				$roles[$i]['access']="Limited Access";
			}
		$check=$db->get_results("SELECT * FROM ss_user_role_map where role_id= '".$roles[$i]['id']."' and status=1");
			if(count((array)$check)>0){
				$roles[$i]['is_use'] =1;
		}else{
			$roles[$i]['is_use'] =0;
		}	
		$roles[$i]['id'] = base64_encode($roles[$i]['id']);
		$roles[$i]['role'] = ucfirst(strtolower($roles[$i]['role']));
	}


		//}

		$finalAry['data'] = $roles;
		echo json_encode($finalAry);
		exit;
	}
}

//==========================ADD ROLE=====================
elseif ($_POST['action'] == 'role_add') {

	if (in_array("su_role_create", $_SESSION['login_user_permissions'])) {
		$permissions = $_POST['permission'];

		$db->query('BEGIN');

		$check_role = $db->query("SELECT * FROM ss_role where role='" . trim($db->escape($_POST['role'])) . "' ");

		if (empty($check_role)) {
			//---------------------Create User Type with the help of role Start----------------------//
			$maxprefrence = $db->get_row("SELECT max(`preference`)+1 as pref FROM `ss_usertype`");
			$Prefrence_Max_Plus_One = $maxprefrence->pref;
			if ($_POST['access'] == '1') {
				$user_type_code = "UT01";
				$user_type_group = "principal";
				$user_type_subgroup = "principal";
			} elseif ($_POST['access'] == '0') {
				$user_type_code = "UT02";
				$user_type_group = "staff";
				$user_type_subgroup = "teacher";
			}
			$role = $db->query("insert into ss_role set role='" . trim($db->escape($_POST['role'])) . "',access='".$_POST['access']."'");
			$role_id = $db->insert_id;
			

			$type_query = "INSERT INTO `ss_usertype`(`user_type`, `user_type_code`, `user_type_group`, `user_type_subgroup`, `preference`, `is_active`,`role_id`, `created_on`) VALUES ('" . trim($db->escape($_POST['role'])) . "','" . $user_type_code . "','" . $user_type_group . "','" . $user_type_subgroup . "','" . $Prefrence_Max_Plus_One . "','1','" . $role_id . "','" . date('Y-m-d H:i:s') . "')";
			$user_type = $db->query($type_query);
			$user_type_id = $db->insert_id;
			//---------------------Create User Type with the help of role END----------------------//
			if (!empty($permissions)) {

				foreach ($permissions as $permission => $val) {
					$role = $db->query("insert into ss_role_wise_permissions set role_id='" . trim($db->escape($role_id)) . "', permission_id='" . trim($db->escape($permission)) . "'  ");
				}
			}

			if ($role_id > 0 && $db->query('COMMIT') !== false) {
				echo json_encode(array('code' => "1", 'msg' => 'Role added successfully'));
				exit;
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => 'Role not added');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			$return_resp = array('code' => "0", 'msg' => 'Role name already exists in database');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	}
}
//==========================EDIT ROLE=====================
elseif ($_POST['action'] == 'role_edit') {

	if (in_array("su_role_edit", $_SESSION['login_user_permissions'])) {
		$id = $_POST['role_id'];
		$permissions = $_POST['permission'];
		$db->query('BEGIN');

		$check_role = $db->query("SELECT * FROM ss_role where role='" . trim($db->escape($_POST['role'])) . "' and id <> '" . $id . "' ");

		if (empty($check_role)) {
			$role = $db->query("update ss_role set role='" . trim($db->escape($_POST['role'])) . "' where id = '" . $id . "'");

			if (array_key_exists("access", $_POST)) {
				if ($_POST['access'] == '1') {
					$user_type_code = "UT01";
					$user_type_group = "principal";
					$user_type_subgroup = "principal";
				} elseif ($_POST['access'] == '0') {
					$user_type_code = "UT02";
					$user_type_group = "staff";
					$user_type_subgroup = "teacher";
				}
				$userType = $db->query("update ss_usertype set user_type='" . trim($db->escape($_POST['role'])) . "',user_type_code='" . $user_type_code . "',user_type_group='" . $user_type_group . "',user_type_subgroup='" . $user_type_subgroup . "'   where role_id = '" . $id . "'");
			}

			$role = $db->query("delete from ss_role_wise_permissions  where role_id = '" . $id . "'");
			foreach ($permissions as $permission => $val) {
				$role = $db->query("insert into ss_role_wise_permissions set role_id='" . trim($db->escape($id)) . "', permission_id='" . trim($db->escape($permission)) . "'  ");
			}


			if ($role && $db->query('COMMIT') !== false) {
				echo json_encode(array('code' => "1", 'msg' => 'Role updated successfully'));
				exit;
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => 'Role not updated', '_error' => 1);
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {

			$db->query('ROLLBACK');
			$return_resp = array('code' => "0", 'msg' => 'Role name already exists in database');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	}
}

//=====================DELETE ROLE==================
elseif ($_POST['action'] == 'delete_role') {

	// if (in_array("su_role_delete", $_SESSION['login_user_permissions'])) {
	if (isset($_POST['id'])) {

		// $check_permission = $db->query("SELECT * FROM ss_role_wise_permissions where role_id = '".$_POST['id']."' ");
		$check_user = $db->query("SELECT * FROM ss_user_role_map where role_id = '" . base64_decode($_POST['id']) . "' AND status=1");

		if (empty($check_user)) {

			$rec = $db->query("update ss_role  set status='2'  where id='" . base64_decode($_POST['id']) . "'");

			if ($rec) {
				echo json_encode(array('code' => "1", 'msg' => 'Role deleted successfully'));
				exit;
			} else {
				$return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_error' => 1);
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {
			$return_resp = array('code' => "0", 'msg' => 'Role already used. not deleted');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	} else {
		$return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_error' => 2);
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
	// }
}


//==========================USER ROLE AND PERMISSION ADD=====================
if ($_POST['action'] == 'user_role_and_permission_add') {


	$user_id = $_POST['user_id'];
	$role_id = $_POST['role_id'];
	$permissions = $_POST['permission'];

	$db->query('BEGIN');


	$check_user = $db->query("SELECT * FROM ss_user_role_map where user_id = '" . $user_id . "' AND status = 1");

	if (!empty($check_user)) {

		$db->query("update ss_user_role_map set role_id='" . trim($db->escape($role_id)) . "' where user_id = '" . $user_id . "'");
		$user_role = 1;
	} else {

		$user_role = $db->query("insert into ss_user_role_map set user_id='" . trim($db->escape($user_id)) . "', role_id='" . trim($db->escape($role_id)) . "' ");
	}

	$role = $db->query("delete from ss_user_extra_permissions  where user_id = '" . $user_id . "'");
	foreach ($permissions as $permission => $val) {
		$role = $db->query("insert into ss_user_extra_permissions set user_id='" . trim($db->escape($user_id)) . "', extra_permission_id='" . trim($db->escape($permission)) . "'  ");
	}


	if ($user_role && $db->query('COMMIT') !== false) {
		echo json_encode(array('code' => "1", 'msg' => 'User role assign successfully'));
		exit;
	} else {
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0", 'msg' => 'User role not assign', '_error' => 1);
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
} elseif ($_POST['action'] == 'role_get_permission') {

	$user_id = $_POST['user_id'];
	$roleid = $_POST['roleid'];

	// $role_permissions = $db->get_results("SELECT p.id , p.permission,permission_name FROM ss_role_wise_permissions e INNER JOIN ss_permissions p ON e.permission_id = p.id where e.role_id = '".$roleid."' order by e.id desc");



	//  if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){
	//      $permissions = $db->get_results("SELECT id , permission, permission_name FROM ss_permissions where  status=1 AND id not in(SELECT permission_id FROM ss_role_wise_permissions where role_id = '".$roleid."') order by id desc");
	//  }else{
	//  	 $permissions = $db->get_results("SELECT id , permission, permission_name FROM ss_permissions where  status=1 AND public_access = 1 AND id not in(SELECT permission_id FROM ss_role_wise_permissions where role_id = '".$roleid."')  order by id desc");
	//  }

	//$user_id = $db->get_var("SELECT user_id FROM ss_user_role_map where role_id = '".$roleid."' ");


	$user_extra_permission = $db->get_results("SELECT extra_permission_id FROM ss_user_extra_permissions  where user_id = '" . $user_id . "'");

	$arrayExtraPermission = [];
	foreach ($user_extra_permission as $rows) {
		$arrayExtraPermission[] = $rows->extra_permission_id;
	}



	$roleWaisePermission = $db->get_results("SELECT * FROM ss_role_wise_permissions WHERE role_id = " . $roleid . " ");

	if (is_array($roleWaisePermission)) {
		$newarray = [];
		foreach ($roleWaisePermission as $val) {
			$newarray[] = $val->permission_id;
		}
	}

	$permissions_group = $db->get_results("SELECT * FROM ss_permission_groups ");

	$html = '<div class="row">';
	foreach ($permissions_group  as $key => $group) {

		$html .= '<div class="col-md-3">
				<div class="form-group">    
				<lable><strong>' . $group->permission_group . '</strong></lable>';

		if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') {
			$permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " ");
		} else {
			$permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " AND public_access = 1");
		}

		foreach ($permissions as $key => $row) {

			if (is_array($newarray)) {
				if (in_array($row->id, $newarray)) {
					$checked = "checked";
					$disabled = "disabled";
				} else {
					$checked = "";
					$disabled = "";
				}
			}

			if (is_array($arrayExtraPermission)) {
				if (in_array($row->id, $arrayExtraPermission)) {
					$checkedd = "checked";
					$disabledd = "";
				} else {
					$checkedd = "";
					$disabledd = "";
				}
			}

			$html .= '<div class="custom-control custom-checkbox mb-3">
					<input type="checkbox" class="custom-control-input" name="permission[' . $row->id . ']" ' . $checked . ' ' . $disabled . '  ' . $checkedd . ' ' . $disabledd . '  >
					<label class="custom-control-label" for="customCheck">' . $row->permission_name . '</label>
					</div>';
		}
		$html .= '</div>
					     </div>';
	}
	$html .= '</div>';

	echo $html;
}
