<?php
include_once "../includes/config.php";

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}

//==========================LIST ALL STAFF FOR ADMIN (SS)=====================
if ($_GET['action'] == 'list_all_groups') {
	$finalAry = array();
	$all_groups = $db->get_results("SELECT id,group_name, max_limit,category,is_regis_open,
	(CASE WHEN is_deleted=1 THEN 'Deleted' WHEN is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status from ss_groups 
	where is_deleted = 0 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'", ARRAY_A);
	for ($i = 0; $i < count((array)$all_groups); $i++) {
		$group_strength = $db->get_var("SELECT COUNT(DISTINCT sgm.student_user_id) FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
		INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
		WHERE (u.`is_active` = 1 OR u.`is_active` = 2) AND u.`is_deleted` = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		and sgm.group_id = '" . $all_groups[$i]['id'] . "' 
		AND sgm.latest = 1");

		$all_groups[$i]['group_strength'] = $group_strength;
		if ($all_groups[$i]['group_strength'] <= $all_groups[$i]['max_limit']) {
			$all_groups[$i]['strength'] = $group_strength;
		} else {
			$all_groups[$i]['strength'] = "";
		}
		$all_groups[$i]['is_regis_open'] = $all_groups[$i]['is_regis_open'] == 1 ? "Open" : "Closed";

		$classes_online = $db->get_row("SELECT cls.id FROM ss_classes_online cls INNER JOIN ss_groups g ON g.id = cls.group_id WHERE cls.group_id = '" . $all_groups[$i]['id'] . "' and cls.status <> 2  ");
		$check_group_in_classtime = $db->get_row("SELECT ct.id FROM ss_classtime ct INNER JOIN ss_groups g ON g.id = ct.group_id WHERE g.id = '" . $all_groups[$i]['id'] . "'  ");
		if (!empty($check_group_in_classtime) > 0 || !empty($classes_online) > 0) {
			$all_groups[$i]['delete'] = '1';
		} else {
			$all_groups[$i]['delete'] = '';
			if(!empty($all_groups[$i]['strength'])){ 
				$all_groups[$i]['delete'] = '1';
			}
		}
	}
	$finalAry['data'] = $all_groups;
	echo json_encode($finalAry);
	exit;
}
//==========================FETCH GROUP INFO (SS)=====================
if ($_POST['action'] == 'fetch_group') {
	$group = $db->get_row("SELECT * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and id = '" . $_POST['group_id'] . "'");
	echo json_encode(array('code' => 1, 'id' => $group->id, 'group_name' => $group->group_name, 'category' => $group->category, 'is_active' => $group->is_active, 'max_limit' => $group->max_limit, 'is_regis_open' => $group->is_regis_open));
	exit;
}
//==========================ASSIGN CHEIKH TO GROUP===================
elseif ($_POST['action'] == 'assign_sheikh_to_group') {
	$groupConflits = false;
	$group_id = $_POST['groupid'];
	$staff_user_id = $_POST['staff_user_id'];
	$alreadyAssigned = $db->get_results("select * from ss_staffgroupmap where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
	and group_id='" . $group_id . "' and staff_user_id='" . $staff_user_id . "' and active=1");
	if (!count((array)$alreadyAssigned)) {
		$assignedGroups = $db->get_results("select * from ss_staffgroupmap where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		and staff_user_id='" . $staff_user_id . "' and active=1");
		foreach ($assignedGroups as $grp) {
			if (areGroupsConflict($group_id, $grp->group_id)) {
				$groupConflits = true;
				break;
			}
		}
		if (!$groupConflits) {
			//INACTIVATE ALL ENTRIES OF SELECTED GROUP
			$db->query("update ss_staffgroupmap set active=0 where group_id='" . $group_id . "'");
			$db->query("insert into ss_staffgroupmap set staff_user_id='" . $staff_user_id . "', group_id='" . $group_id . "', active=1,
			session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
			created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "', 
			updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
			$assign_id = $db->insert_id;
			if ($assign_id > 0) {
				echo json_encode(array('code' => "1", 'msg' => 'Sheikh assigned to group successfully'));
				exit;
			} else {
				echo json_encode(array('code' => "0", 'msg' => "Error: Sheikh not assigned"));
				exit;
			}
		} else {
			echo json_encode(array('code' => "0", 'msg' => "Error: Group timings conflict"));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => "Error: Group already assigned to selected Sheikh"));
		exit;
	}
}
//==========================SAVE GROUP (SS)===================
elseif ($_POST['action'] == 'save_group') {
	$group_id = $_POST['group_id'];
	$db->query('BEGIN');
	if (is_numeric($group_id)) {
		$groupNameCheck = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND REPLACE(group_name,' ','') = '" . str_replace(' ', '', trim($_POST['group_name'])) . "' and id <> '" . $group_id . "' AND is_deleted='0' ");
		if (count((array)$groupNameCheck) == 0) {
			//EDIT EXISTING GROUP
                 
			//GROUP STRENGTH CHECK
			
			$isClassAssigned = $db->get_results("select * from ss_classtime where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and is_active='1' and group_id= '" . $group_id . "' ");
			
			if(!empty($isClassAssigned)){
			 $is_active = $_POST['is_active'];
			 if($is_active ==0){
				echo json_encode(array('code' => "0", 'msg' => 'Group not updated because group is in active classtime'));
				exit;
			 }
			}

			$group_strength = $db->get_var("SELECT COUNT(DISTINCT sgm.student_user_id) FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
			INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
			WHERE (u.`is_active` = 1 OR u.`is_active` = 2) AND u.`is_deleted` = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
			and sgm.group_id = '" . $group_id . "' 
			AND sgm.latest = 1");
			
			if($group_strength > $_POST['max_limit']){
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Group Limit Cannot Be Less Than '.$group_strength.''));
				exit;
			}

			$result = $db->query("update ss_groups set group_name='" . trim($db->escape($_POST['group_name'])) . "', category='" . trim($_POST['category']) . "', max_limit='" . trim($db->escape($_POST['max_limit'])) . "', is_regis_open='" . $_POST['is_regis_open'] . "', is_active='" . $_POST['is_active'] . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $group_id . "'");
			if($result){
					$db->query('COMMIT');
					echo json_encode(array('code' => "1", 'msg' => 'Group Updated Successfully'));
					exit;
			}else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Group Not Updated'));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Group name already exists'));
			exit;
		}

	} else {
		$groupNameCheck = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND 
		REPLACE(group_name,' ','') = '" . str_replace(' ', '', trim($_POST['group_name'])) . "' AND is_deleted = 0");
		if (count((array)$groupNameCheck) == 0) {
			//NEW GROUP	
			$db->query("insert into ss_groups set group_name='" . trim($db->escape($_POST['group_name'])) . "', 
			session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', 
			category='" . trim($_POST['category']) . "', max_limit='" . trim($db->escape($_POST['max_limit'])) . "', 
			is_regis_open='" . $_POST['is_regis_open'] . "', is_active='" . $_POST['is_active'] . "', is_deleted=0, 
			created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "', 
			updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
			$group_id = $db->insert_id;
            $status = 1;
			$fee_amount = $db->get_row("select fee_amount,fees_unique_id from ss_basicfees where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND status = 1 order by id desc");


			if(empty($fee_amount->fee_amount)){
				$fee_amount = 0;
				$status = 2;
				$fees_unique_id = uniqid();
			}else{
				$fees_unique_id = $fee_amount->fees_unique_id;
				$fee_amount=$fee_amount->fee_amount;
			}

			$res = $db->query("insert into ss_basicfees set fee_amount='".$fee_amount."', fees_unique_id='".$fees_unique_id."', status='".$status."', 
			group_id='".$group_id."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',  created_on='".date('Y-m-d H:i:s')."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'");

			if ($group_id > 0 && $res && $db->query('COMMIT') !== false) {
				echo json_encode(array('code' => "1", 'msg' => 'Group Added Successfully'));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Error: Group creation failed'));
				exit;
			}
		
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Error: Group name already exists'));
			exit;
		}

	}
}

//==========================EDIT STAFF=====================
elseif ($_POST['action'] == 'edit_group') {
	$group_id = $_POST['group_id'];
	$groupNameCheck = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND 
	REPLACE(group_name,' ','') = '" . str_replace(' ', '', trim($_POST['group_name'])) . "' and id <> '" . $group_id . "'");
	if (count((array)$groupNameCheck) == 0) {
		$db->query('BEGIN');
		
		//GROUP STRENGTH CHECK
		$group_strength = $db->get_var("SELECT COUNT(DISTINCT sgm.student_user_id) FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
		INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
		WHERE (u.`is_active` = 1 OR u.`is_active` = 2) AND u.`is_deleted` = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		and sgm.group_id = '" . $group_id . "' 
		AND sgm.latest = 1");
		
		if($group_strength > $_POST['max_limit']){
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Error: Group Limit Cannot Be Less Than '.$group_strength.' Cuurent Strength'));
			exit;
		}

		$db->query("update ss_groups set group_name='" . trim($db->escape($_POST['group_name'])) . "', category='" . trim($_POST['category']) . "', 
		max_limit='" . trim($db->escape($_POST['max_limit'])) . "', is_active='" . $is_active . "', updated_on='" . date('Y-m-d H:i:s') . "' 
		where id='" . $group_id . "'");
		if ($db->query('COMMIT') !== false) {
			echo json_encode(array('code' => "1", 'msg' => 'Group updated successfully'));
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Error: Group updated failed'));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Error: Group name already exists'));
		exit;
	}
}
//==========================FETCH GROUPS ASSIGNED TO TEACHERS - USED IN MESSAGE SECTION=====================
elseif ($_POST['action'] == 'fetch_grp_of_teachers_for_select') {
	$teacher_id = $_POST['teacher_id'];
	$groups = $db->get_results("SELECT * FROM ss_groups WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND 
	is_active = 1 AND is_deleted = 0 AND id IN (SELECT group_id FROM ss_classtime 
	WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND id 
	IN (SELECT classtime_id FROM ss_staffclasstimemap WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
	AND staff_user_id IN (" . implode(',', $teacher_id) . "))) order by group_name asc");
	if (count((array)$groups)) {
		foreach ($groups as $grp) {
			$retVal .= '<option value="' . $grp->id . '">' . $grp->group_name . '</option>';
		}
		echo json_encode(array('code' => 1, 'optionVal' => $retVal));
	} else {
		echo json_encode(array('code' => 0));
	}
	exit;
}
//==========================FETCH GROUPS OF TEACHERS - USED IN MESSAGE SECTION=====================
elseif ($_POST['action'] == 'fetch_teachers_groups_for_select') {
	$teacher_id = $_POST['teacher_id'];
	$groups = $db->get_results("SELECT * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
	AND is_active = 1 and is_deleted = 0 and id in (select group_id from ss_staffgroupmap 
	where staff_user_id in (" . implode(',', $teacher_id) . ")) order by group_name asc");
	if (count((array)$groups)) {
		foreach ($groups as $grp) {
			$retVal .= '<option value="' . $grp->id . '">' . $grp->group_name . '</option>';
		}
		echo json_encode(array('code' => 1, 'optionVal' => $retVal));
	} else {
		echo json_encode(array('code' => 0));
	}
	exit;
}
//=====================DELETE GROUP (SS)==================
elseif ($_POST['action'] == 'delete_group') {
	if (isset($_POST['group_id'])) {
		$isGroupInUse_Stu = $db->get_results("select * from ss_studentgroupmap inner join ss_user u on ss_studentgroupmap.student_user_id = u.id where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		and latest = 1 and group_id='" . $_POST['group_id'] . "' and u.`is_deleted` != 0");
		if (count((array)$isGroupInUse_Stu) == 0) {
			//$rec = $db->query("delete from ss_groups where id='".$_POST['group_id']."'");
			$rec = $db->query("update ss_groups set is_active = 0, is_deleted = 1 where id='" . $_POST['group_id'] . "'");
			if ($rec > 0) {
				echo json_encode(array('code' => "1", 'msg' => 'Group Deleted Successfully'));
				exit;
			} else {
				echo json_encode(array('code' => "0", 'msg' => 'Group Not Deleted'));
				exit;
			}
		} else {
			echo json_encode(array('code' => "0", 'msg' => "Error: Can't delete, group is in use"));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed'));
		exit;
	}
}
