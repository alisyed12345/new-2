<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}

//==========================LIST ALL CLASSED FOR ADMIN=====================
if ($_GET['action'] == 'list_all_classes') {
	//ACCESS TO ADMIN ONLY
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	//if(check_userrole_by_code('UT01')){	
	$finalAry = array();
	$all_classes = $db->get_results("SELECT * from ss_classes where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'", ARRAY_A);
	for ($i = 0; $i < count((array)$all_classes); $i++) {
		if ($all_classes[$i]['is_active'] == '1') {
			$all_classes[$i]['is_active'] = "Active";
		} elseif ($all_classes[$i]['is_active'] == '0') {
			$all_classes[$i]['is_active'] = "Inactive";
		}
		$classes_online = $db->get_row("SELECT cls.id FROM ss_classes_online cls INNER JOIN ss_classes c ON c.id = cls.class_id WHERE cls.class_id = '" . $all_classes[$i]['id'] . "' and cls.status <> 2 ");
		$check_classes_in_classtime = $db->get_row("SELECT ct.id FROM ss_classtime ct INNER JOIN ss_classes c ON c.id = ct.class_id WHERE c.id = '" . $all_classes[$i]['id'] . "'  ");
		if (!empty($check_classes_in_classtime) || !empty($classes_online)) {
			$all_classes[$i]['delete'] = '1';
		} else {
			$all_classes[$i]['delete'] = '';
		}
	}
	$finalAry['data'] = $all_classes;
	echo json_encode($finalAry);
	exit;
	//}
}
//==========================LIST ALL CLASS TIME=====================
elseif ($_GET['action'] == 'list_classtimes') {
	//ACCESS TO ADMIN ONLY
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	//if(check_userrole_by_code('UT01')){	
	$finalAry = array();
	$all_classes = $db->get_results("SELECT c.id, g.disp_order, g.group_name, c.is_active, c.group_id, c.class_id, c.time_to, c.time_from, c.day_number 
		FROM ss_classtime c INNER JOIN ss_groups g ON c.group_id = g.id WHERE c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and 
		g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' group by c.group_id,c.class_id", ARRAY_A);

	for ($i = 0; $i < count((array)$all_classes); $i++) {
		$check_attandance = $db->get_row("SELECT id FROM ss_attendance WHERE classtime_id = '" . $all_classes[$i]['id'] . "'");
		$check_schdule_class = $db->get_row("SELECT id FROM ss_staffclasstimemap WHERE classtime_id = '" . $all_classes[$i]['id'] . "'");

		if ($all_classes[$i]['is_active'] == '1') {
			$all_classes[$i]['is_active'] = "Active";
		} elseif ($all_classes[$i]['is_active'] == '0') {
			$all_classes[$i]['is_active'] = "Inactive";
		}
		if ($all_classes[$i]['day_number'] == 1) {
			$all_classes[$i]['day'] = 'Monday';
		} elseif ($all_classes[$i]['day_number'] == 2) {
			$all_classes[$i]['day'] = 'Tuesday';
		} elseif ($all_classes[$i]['day_number'] == 3) {
			$all_classes[$i]['day'] = 'Wednesday';
		} elseif ($all_classes[$i]['day_number'] == 4) {
			$all_classes[$i]['day'] = 'Thursday';
		} elseif ($all_classes[$i]['day_number'] == 5) {
			$all_classes[$i]['day'] = 'Friday';
		} elseif ($all_classes[$i]['day_number'] == 6) {
			$all_classes[$i]['day'] = 'Saturday';
		} elseif ($all_classes[$i]['day_number'] == 0) {
			$all_classes[$i]['day'] = 'Sunday';
		}
		/*$assigned_staff = $db->get_row("select s.user_id, concat(s.first_name,' ',s.last_name) as sheikh from ss_staff s 
			inner join ss_staffclasstimemap m on s.user_id = m.staff_user_id where m.active = 1 and classtime_id='".$all_classes[$i]['id']."' 
			order by m.id desc limit 1");
			$all_classes[$i]['assign_to'] = '<a href="'.SITEURL.'staff/staff_edit.php?id='.$assigned_staff->user_id.'">'.$assigned_staff->sheikh.'</a>';*/
		$all_classes[$i]['time_to'] = date('h:i A', strtotime($all_classes[$i]['time_to']));
		$all_classes[$i]['time_from'] = date('h:i A', strtotime($all_classes[$i]['time_from']));
		$all_classes[$i]['assign_to'] = '';
		$all_classes[$i]['class_name'] = $db->get_var("select class_name from ss_classes where id = '" . $all_classes[$i]['class_id'] . "'");

		if (!empty($check_attandance) && !empty($check_schdule_class)) {
			$all_classes[$i]['delete'] = '1';
		} elseif (!empty($check_attandance)) {
			$all_classes[$i]['delete'] = '1';
		} elseif (!empty($check_schdule_class)) {
			$all_classes[$i]['delete'] = '1';
		} else {
			$all_classes[$i]['delete'] = '';
		}
	}
	$finalAry['data'] = $all_classes;
	echo json_encode($finalAry);
	exit;
	//}
}
//==========================ASSIGN TEACHER TO CLASS TIME===================
//ADDED ON 28-SEP-2018
elseif ($_POST['action'] == 'assign_teacher_to_classtime') {
	$classtimeid = $_POST['classtimeid'];
	$teacher_id = $_POST['teacher_id'];
	$helper_id_ary = array();
	$helper_id_ary = $_POST['helper_id'];
	$helper_ids = implode(',', (array)$helper_id_ary);
	$substitute_id_ary = array();
	$substitute_id_ary = $_POST['substitute_id'];
	$substitute_ids = implode(',', (array)$substitute_id_ary);
	$conflictTeacherCheck = false;
	$conflictHelperCheck = false;
	$conflictSubstituteCheck = false;
	if(empty($teacher_id)){
	echo json_encode(array('code' => "0", 'msg' => "Error: Please Select Teacher", '_erpos' => 1));
	exit;	
	}
	
	$db->query('BEGIN');
   
	//CHECK TEACHER TIME CLASH
	$isTeacherCurClass = $db->get_var("select COUNT(1) from ss_staffclasstimemap where active = 1 and staff_user_id = '" . $teacher_id . "' and 
	classtime_id = '" . $classtimeid . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	if (!$isTeacherCurClass) {
		$conflictTeacherCheck = areClasstimeConflict($teacher_id, $classtimeid);
	}
	
	//CHECK HELPER TIME CLASH
	if(!empty($helper_id_ary)){
	foreach ($helper_id_ary as $helper_id) {
		$isHelperCurClass = $db->get_var("select COUNT(1) from ss_staffclasstimemap where active = 1 and staff_user_id = '" . $helper_id . "' 
		and classtime_id = '" . $classtimeid . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
		if (!$isHelperCurClass) {
			$conflictHelperCheck = areClasstimeConflict($helper_id, $classtimeid);
		}
	}
}
	//CHECK SUBSTITUTE TIME CLASH
	if(!empty($substitute_id_ary)){
	foreach ($substitute_id_ary as $substitute_id) {
		$isSubstituteCurClass = $db->get_var("select COUNT(1) from ss_staffclasstimemap where active = 1 and staff_user_id = '" . $substitute_id . "' 
		and classtime_id = '" . $classtimeid . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
		if (!$isSubstituteCurClass) {
			$conflictSubstituteCheck = areClasstimeConflict($substitute_id, $classtimeid);
		}
	}
}

	//UNCOMMENTED ON 06-SEP-2020
	if (!$conflictTeacherCheck) {
		//COMMENTED ON 06-SEP-2020
		//	if(true){	
		if (!$conflictHelperCheck) {
			if (!$conflictSubstituteCheck) {
				//RESET/INACTIVATE ALL STAFF ASSIGNMENTS OF SELECTED CLASS
				$db->query("update ss_staffclasstimemap set active = 0,updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND classtime_id='" . $classtimeid . "'");

				//TEACHER
				if ($teacher_id > 0) {
					$result_tea = $db->query("insert into ss_staffclasstimemap set staff_user_id='" . $teacher_id . "', classtime_id='" . $classtimeid . "', 
				role_for_class = 'teacher', active=1, session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', 
				created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
				} else {
					$result_tea = true;
				}

				//HELPER
				if (!empty($helper_id_ary)) {
					foreach ($helper_id_ary as $helper_id) {
						$result_hel = $db->query("insert into ss_staffclasstimemap set staff_user_id='" . $helper_id . "', 
						classtime_id='" . $classtimeid . "', role_for_class = 'helper', active=1, session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
						created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "', 
						updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
					}
				} else {
					$result_hel = true;
				}
								
				//SUBSTITUTE
				if (!empty($substitute_id_ary)) {
					foreach ($substitute_id_ary as $substitute_id) {
						$result_sub = $db->query("insert into ss_staffclasstimemap set staff_user_id='" . $substitute_id . "', 
						classtime_id='" . $classtimeid . "', active=1, session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
						role_for_class = 'substitute',  
						created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', 
						updated_on='" . date('Y-m-d H:i:s') . "'");
					}
				} else {
					$result_sub = true;
				}

				if ($result_tea && $result_hel && $result_sub && $db->query('COMMIT') !== false) {
					if ($teacher_id > 0) {
						$teacher = $db->get_row("SELECT first_name,last_name,gender FROM ss_staff WHERE user_id = '" . $teacher_id . "'");
						if ($teacher->gender == "f") {
							$teacher_name = "Sr. " . $teacher->first_name . " " . $teacher->last_name;
						} else {
							$teacher_name = "Br. " . $teacher->first_name . " " . $teacher->last_name;
						}
					} else {

						$teacher_name = '';
					}

					if (!empty($helper_id_ary)) {
						foreach ($helper_id_ary as $helper_id) {
							if (trim($helper_name) != '') {
								$helper_name = $helper_name . ", ";
							}
							$helper = $db->get_row("SELECT first_name,last_name,gender FROM ss_staff WHERE user_id = '" . $helper_id . "'");
							if ($helper->gender == "f") {
								$helper_name .= "Sr. " . $helper->first_name . " " . $helper->last_name;
							} elseif ($helper->gender == "m") {
								$helper_name .= "Br. " . $helper->first_name . " " . $helper->last_name;
							}
						}
					}
					if (!empty($substitute_id_ary)) {
						foreach ($substitute_id_ary as $substitute_id) {
							if (trim($substitute_name) != '') {
								$substitute_name = $substitute_name . ", ";
							}

							$substitute = $db->get_row("SELECT first_name,last_name,gender FROM ss_staff WHERE user_id = '" . $substitute_id . "'");
							if ($substitute->gender == "f") {
								$substitute_name .= "Sr. " . $substitute->first_name . " " . $substitute->last_name;
							} elseif ($substitute->gender == "m") {
								$substitute_name .= "Br. " . $substitute->first_name . " " . $substitute->last_name;
							}
						}
					}
					//echo json_encode(array('code' => "1",'msg' => 'Teacher assigned successfully', 'teacher_name'=>$teacher_name, 'helper_name'=>$helper_name, 'helper_id'=>$helper_id, 'substitute_name'=>$substitute_name, 'substitute_id'=>$substitute_id, 'classtimeid'=>$classtimeid));
					echo json_encode(array('code' => "1", 'msg' => 'Teacher assigned successfully', 'teacher_name' => $teacher_name, 'helper_name' => $helper_name, 'helper_id' => $helper_ids, 'substitute_name' => $substitute_name, 'substitute_id' => $substitute_ids, 'classtimeid' => $classtimeid));
					exit;
				} else {
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0", 'msg' => 'Error: Teacher not assigned', '_erpos' => 4));
					exit;
				}
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => "Error: Substitute timings conflict", '_erpos' => 3));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => "Error: Helper timings conflict", '_erpos' => 2));
			exit;
		}
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => "Error: Teacher timings conflict", '_erpos' => 1));
		exit;
	}
}
//==========================FETCH CLASSES OF GROUP ASSIGNED TO STAFF=====================
elseif ($_POST['action'] == 'fetch_assigned_group_class_for_select') {
	$group_id = $_POST['group_id'];
	if (check_userrole_by_code('UT01')) {
		$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id where 
		ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and c.is_active = '1' and  c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	} elseif (check_userrole_by_code('UT02')) {
		$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classes c INNER JOIN ss_classtime ct ON c.id = ct.class_id 
		INNER JOIN ss_staffclasstimemap sctm ON ct.id = sctm.classtime_id WHERE c.is_active = 1 AND ct.is_active = 1 AND sctm.active = 1 
		AND sctm.staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND group_id = '" . $group_id . "' AND 
		c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
		and sctm.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY c.disp_order");
	}
	$option = "";
	if (count((array)$classes)) {
		foreach ($classes as $cls) {
			$option .= "<option value = '" . $cls->id . "'>" . $cls->class_name . "</option>";
		}
	}
	echo $option;
	exit;
}
//==========================FETCH MUlTIPLE CLASSES OF GROUP ASSIGNED TO STAFF=====================
elseif ($_POST['action'] == 'fetch_assigned_multiple_group_class_for_select') {
	$group_id = $_POST['group_id'];
	$classes = [];

	if (check_userrole_by_code('UT01')) {
		$classes[] = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id where 
			ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	} elseif (check_userrole_by_code('UT02')) {
		foreach ($group_id as $groupid) {
			$classes[] = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classes c INNER JOIN ss_classtime ct ON c.id = ct.class_id 
				INNER JOIN ss_staffclasstimemap sctm ON ct.id = sctm.classtime_id WHERE c.is_active = 1 AND ct.is_active = 1 AND sctm.active = 1 
				AND sctm.staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND group_id IN ('" . $groupid . "') AND 
				c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
				and sctm.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY c.disp_order");
		}
	}

	// echo"<pre>";
	// print_r($classes);
	// die;
	// if(check_userrole_by_code('UT01')){	
	// 	$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id where 
	// 	ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
	// }elseif(check_userrole_by_code('UT02')){
	// 	$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classes c INNER JOIN ss_classtime ct ON c.id = ct.class_id 
	// 	INNER JOIN ss_staffclasstimemap sctm ON ct.id = sctm.classtime_id WHERE c.is_active = 1 AND ct.is_active = 1 AND sctm.active = 1 
	// 	AND sctm.staff_user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND group_id = '".$group_id."' AND 
	// 	c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
	// 	and sctm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ORDER BY c.disp_order");
	// }
	$option = "";
	if (count((array)$classes) > 0) {
		foreach ($classes as $cls) {
			foreach ($cls as $row) {
				$option .= "<option value = '" . $row->id . "'>" . $row->class_name . "</option>";
			}
		}
	}
	echo $option;
	exit;
}
//==========================ADD CLASS TIME===================
elseif ($_POST['action'] == 'add_classtime') {
	$group_id = $_POST['group'];
	$class_id = $_POST['group_class'];
	$is_active = $_POST['is_active'];
	//$no_of_days = $_POST['no_of_days'];
	$sch_time_from = $_POST['sun_from'];
	$sch_time_to = $_POST['sun_to'];

	$db->query('BEGIN');
	$chcek_classtime = $db->get_results("SELECT id FROM ss_classtime WHERE group_id='" . $group_id . "' AND class_id='" . $class_id . "'  ");
	if (count((array)$chcek_classtime) == 0) {

		$sch_time_fromnew = date("H:i:s", strtotime('+1 minutes', strtotime($sch_time_from))); //only for check condition
		$sch_time_tonew = date("H:i:s", strtotime('-1 minutes', strtotime($sch_time_to))); //only for check condition
		$chcek_time = $db->get_results("SELECT id FROM ss_classtime WHERE group_id='" . $group_id . "'  AND ((time_from BETWEEN '" . $sch_time_fromnew . "' AND '" . $sch_time_tonew . "') OR (time_to BETWEEN '" . $sch_time_fromnew . "' AND '" . $sch_time_tonew . "') OR (time_from='" . $sch_time_from . "' AND time_to='" . $sch_time_to . "') )  ");
		if (count((array)$chcek_time) == 0) {
			//---------Check : Class Time can not inserted when already exist in same group and start time between end time --------------//
			//foreach ($no_of_days as $day) {
			$result = $db->query("insert into ss_classtime set group_id='" . $group_id . "', class_id='" . $class_id . "',time_from='" . $sch_time_from . "', time_to='" . $sch_time_to . "', is_active='" . $is_active . "',session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
			//}
			if ($result && $db->query('COMMIT') !== false) {
				echo json_encode(array('code' => "1", 'msg' => 'Class Time Added Successfully'));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Class Time Not Added'));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Time Conflict: This time already exist'));
			exit;
		}
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => 'Time Conflict: This time already exist'));
		exit;
	}
}
//==========================EDIT CLASS TIME===================
elseif ($_POST['action'] == 'edit_classtime') {
	$classtime_id = $_POST['classtime_id'];
	$group_id = $_POST['group'];
	$class_id = $_POST['group_class'];
	$is_active = $_POST['is_active'];

	//$no_of_days = $_POST['no_of_days'];
	$sch_time_from = $_POST['sun_from'];
	$sch_time_to = $_POST['sun_to'];
	$db->query('BEGIN');

	$chcek_classtimeG = $db->get_results("SELECT id FROM ss_classtime WHERE group_id='" . $group_id . "' AND class_id='" . $class_id . "' id <> '" . $classtime_id . "' ");
	if (count((array)$chcek_classtimeG) == 0) {

		$sch_time_fromnew = date("H:i:s", strtotime('+1 minutes', strtotime($sch_time_from))); //only for check condition
		$sch_time_tonew = date("H:i:s", strtotime('-1 minutes', strtotime($sch_time_to))); //only for check condition
		$chcek_time = $db->get_results("SELECT id FROM ss_classtime WHERE group_id='" . $group_id . "'  AND ((time_from BETWEEN '" . $sch_time_fromnew . "' AND '" . $sch_time_tonew . "') OR (time_to BETWEEN '" . $sch_time_fromnew . "' AND '" . $sch_time_tonew . "') OR (time_from='" . $sch_time_from . "' AND time_to='" . $sch_time_to . "') )  AND id <> '" . $classtime_id . "' ");
		if (count((array)$chcek_time) == 0) {

			/* 	foreach ($no_of_days as $day) {
		$chcek_classtime = $db->get_results("SELECT id FROM ss_classtime WHERE group_id='" . $group_id . "' AND class_id='" . $class_id . "' and  day_number='" . $day . "'  ");
		if (count((array)$chcek_classtime) > 0) {
			$result = $db->query("update ss_classtime set group_id='" . $group_id . "', class_id='" . $class_id . "', day_number='" . $day . "',time_from='" . $sch_time_from . "', time_to='" . $sch_time_to . "', is_active='" . $is_active . "',updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where group_id='" . $group_id . "' and class_id='" . $class_id . "' and  day_number='" . $day . "' ");
		} else {
			$chcek_classtime = $db->get_results("SELECT id FROM ss_classtime WHERE group_id='" . $group_id . "' AND class_id='" . $class_id . "' and  day_number='" . $day . "'  ");
			if (count((array)$chcek_classtime) == 0) {
				$result = $db->query("insert into ss_classtime set group_id='" . $group_id . "', class_id='" . $class_id . "', day_number='" . $day . "',time_from='" . $sch_time_from . "', time_to='" . $sch_time_to . "', is_active='1',session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
			}
		}
	} */
			$result = $db->query("update ss_classtime set group_id='" . $group_id . "', class_id='" . $class_id . "',time_from='" . $sch_time_from . "', time_to='" . $sch_time_to . "', is_active='" . $is_active . "',updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $classtime_id . "' ");
			if($is_active == 0){
				$unassignTeacherHelperSub = $db->query("update ss_staffclasstimemap set active='0',updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where classtime_id='" . $classtime_id . "' ");
			}
			if ($result && $db->query('COMMIT') !== false) {
				echo json_encode(array('code' => "1", 'msg' => 'Class Time Updated Successfully'));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Class Time Not Updated'));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Time Conflict: This time already exist'));
			exit;
		}
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => 'Time Conflict: This time already exist'));
		exit;
	}
}
//==========================ADD/EDIT CLASS===================
elseif ($_POST['action'] == 'save_class') {
	$is_active = $_POST['is_active'];
	$class_id = $_POST['class_id'];
	$class_name = $db->escape($_POST['class_name']);
	$db->query('BEGIN');
	if (is_numeric($class_id)) {
		//EDIT EXISTING CLASS
		$result = $db->query("update ss_classes set is_active='" . $is_active . "', class_name='" . $class_name . "', 		
		updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on = '" . date('Y-m-d H:i:s') . "' where id = '" . $class_id . "'");
	} else {
		//NEW CLASS	
		$result = $db->query("insert into ss_classes set is_active='" . $is_active . "', class_name = '" . $class_name . "', 
		session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', 
		created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_on = '" . date('Y-m-d H:i:s') . "', 
		updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on = '" . date('Y-m-d H:i:s') . "'");
		$class_id = $db->insert_id;
	}
	if ($result && $db->query('COMMIT') !== false) {
		echo json_encode(array('code' => "1", 'msg' => 'Class saved successfully'));
		exit;
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => 'Error: Class not saved'));
		exit;
	}
}

//==========================FETCH CLASSES OF GROUP FOR SELECT=====================
elseif ($_POST['action'] == 'fetch_group_class_for_select') {
	$group_id = $_POST['group_id'];
	$attendance_day_number = date('w');
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	if (check_userrole_by_code('UT01')) {
		//COMMENTED ON 24AUG2021
		// $classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id WHERE ct.is_active = 1 
		// AND c.is_active = 1 AND ct.group_id = '".$group_id."' AND ct.day_number = '".$attendance_day_number."' AND c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
		// AND ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
		//ADDED ON 24AUG2021
		$classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id WHERE ct.is_active = 1 AND c.is_active = 1 AND ct.group_id = '" . $group_id . "' AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
	} elseif (check_userrole_by_code('UT02')) {
		//COMMENTED ON 24AUG2021
		// $classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id WHERE ct.is_active = 1 
		// AND c.is_active = 1 AND ct.group_id = '".$group_id."' AND ct.day_number = '".$attendance_day_number."' 
		// and ct.id in (select classtime_id from ss_staffclasstimemap where staff_user_id = '".$_SESSION['icksumm_uat_login_userid']."')
		// AND c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
		//ADDED ON 24AUG2021
		$classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id WHERE ct.is_active = 1 AND c.is_active = 1 AND ct.group_id = '" . $group_id . "' and ct.id in (select classtime_id from ss_staffclasstimemap where staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  ");
	} elseif (check_userrole_by_code('UT05')) {
		//COMMENTED ON 24AUG2021
		// $classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id WHERE ct.is_active = 1 
		// AND c.is_active = 1 AND ct.group_id = '".$group_id."' AND ct.day_number = '".$attendance_day_number."'
		// AND c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
		//ADDED ON 24AUG2021
		$classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id WHERE ct.is_active = 1 AND c.is_active = 1 AND ct.group_id = '" . $group_id . "' AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
	}
	$option = "<option value=''>Select Class</option>";
	if (count((array)$classes)) {
		foreach ($classes as $cls) {
			$option .= "<option value = '" . $cls->id . "'>" . $cls->class_name . "</option>";
		}
	}
	echo $option;
	exit;
}
//==========================FETCH CLASSES OF GROUP FOR SELECT History=====================
elseif ($_POST['action'] == 'fetch_group_class_for_select_history') {
	$group_id = $_POST['group_id'];
	//$attendance_day_number = date('w');
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	if (check_userrole_by_code('UT01')) {
		$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id 
		WHERE ct.is_active <> 2 AND c.is_active = 1 AND ct.group_id = '" . $group_id . "' AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		AND ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	} elseif (check_userrole_by_code('UT02')) {
		//COMMENTED ON 24AUG2021
		// $classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id 
		// WHERE ct.is_active = 1 AND c.is_active = 1 AND ct.group_id = '".$group_id."' AND ct.day_number = '".$attendance_day_number."' 
		// and ct.id in (select classtime_id from ss_staffclasstimemap where staff_user_id = '".$_SESSION['icksumm_uat_login_userid']."') 
		// AND c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
		//ADDED ON 24AUG2021
		$classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id 
		WHERE ct.is_active <> 2 AND c.is_active = 1 AND ct.group_id = '" . $group_id . "'  
		and ct.id in (select classtime_id from ss_staffclasstimemap where staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') 
		AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
	} elseif (check_userrole_by_code('UT05')) {
		//COMMENTED ON 24AUG2021
		// $classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id 
		// WHERE ct.is_active = 1 AND c.is_active = 1 AND ct.group_id = '".$group_id."' AND ct.day_number = '".$attendance_day_number."' 
		// AND c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
		//ADDED ON 24AUG2021
		$classes = $db->get_results("SELECT c.id,c.class_name FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id 
		WHERE ct.is_active <> 2 AND c.is_active = 1 AND ct.group_id = '" . $group_id . "' 
		AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	}
	$option = "<option value=''>Select Class</option>";
	if (count((array)$classes)) {
		foreach ($classes as $cls) {
			$option .= "<option value = '" . $cls->id . "'>" . $cls->class_name . "</option>";
		}
	}
	echo $option;
	exit;
}
//==========================FETCH CLASS BY ID=====================
elseif ($_POST['action'] == 'fetch_class') {
	$class_id = $_POST['class_id'];
	$class = $db->get_row("SELECT * from ss_classes where id = '" . $class_id . "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	if (!empty($class)) {
		echo json_encode(array('code' => 1, 'id' => $class->id, 'class_name' => $class->class_name, 'is_active' => $class->is_active));
	} else {
		echo json_encode(array('code' => 0));
	}
	exit;
}
//=====================DELETE CLASS==================
elseif ($_POST['action'] == 'delete_classtime') {
	$classtime_id = $_POST['classtime_id'];
	$rec = $db->query("delete from ss_classtime where group_id='" . $_POST['groupid'] . "' and class_id='" . $_POST['classid'] . "'");
	if ($rec > 0) {
        $unassignTeacherHelperSub = $db->query("delete from ss_staffclasstimemap where classtime_id='" . $classtime_id . "' ");
		echo json_encode(array('code' => "1", 'msg' => 'Class time deleted successfully'));
		exit;
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Error: Class time deletion failed'));
		exit;
	}
}
