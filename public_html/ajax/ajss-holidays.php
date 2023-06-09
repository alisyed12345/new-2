<?php 
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}
//==========================LIST ALL STAFF FOR ADMIN=====================
if($_GET['action'] == 'list_all_holidays'){
	//ACCESS TO ADMIN ONLY
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	//if(check_userrole_by_code('UT01')){	
		$finalAry = array();
		if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
			$all_holidays = $db->get_results("SELECT * from ss_holidays where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'",ARRAY_A);
		}else{
			$all_holidays = $db->get_results("SELECT * from ss_holidays where is_active = 1 AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'",ARRAY_A);
		}
		

		for($i=0; $i<count((array)$all_holidays); $i++){
			if($all_holidays[$i]['is_active'] == '1'){
				$all_holidays[$i]['is_active'] = "Active";
			}elseif($all_holidays[$i]['is_active'] == '0'){
				$all_holidays[$i]['is_active'] = "Inactive";
			}
			if($all_holidays[$i]['date_start'] == $all_holidays[$i]['date_end']){
				$all_holidays[$i]['holiday_date'] = my_date_changer($all_holidays[$i]['date_start']);
			}else{
				$all_holidays[$i]['holiday_date'] = my_date_changer($all_holidays[$i]['date_start']).' - '.my_date_changer($all_holidays[$i]['date_end']);
			}

			
              
			if($all_holidays[$i]['is_for_all_groups'] == 1){
				$all_holidays[$i]['for_group'] = 'All Groups';
			}else{

				$groups = $db->get_results("select *,(CASE WHEN category='b' THEN 'Beginner' 
				WHEN category='i' THEN 'Intermediate' WHEN category='a' THEN 'Advanced' END) AS category 
				from ss_groups g where g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active=1 
				and is_deleted=0 order by group_name asc"); 
				$total_goups = count((array)$groups);
				$holidays_goups = count((array)$db->get_results("SELECT * from ss_holiday_groups where holiday_id = '".$all_holidays[$i]['id']."'"));
				
                if($total_goups == $holidays_goups){
                  $all_holidays[$i]['for_group'] = 'All Groups';
                }
                else{
				$holiday_groups = $db->get_results("SELECT * FROM ss_holiday_groups h INNER JOIN ss_groups g ON h.group_id = g.id where holiday_id = '".$all_holidays[$i]['id']."'");

				foreach($holiday_groups as $hg){
					if(trim($all_holidays[$i]['for_group']) == ""){
						$all_holidays[$i]['for_group'] = $hg->group_name;
					}else{
						$all_holidays[$i]['for_group'] = $all_holidays[$i]['for_group'].", ".$hg->group_name;
					}
				}
				}
			}
		}
		$finalAry['data'] = $all_holidays;
		echo json_encode($finalAry);
		exit;
	//}
}

//==========================ADD/EDIT HOLIDAY===================
elseif($_POST['action'] == 'save_holiday'){

	if(get_country()->abbreviation=='GB'){

		if(substr_count(trim($_POST['holiday_date']),"-")>1){
			$holiday_date_ary =	explode(' - ',trim($_POST['holiday_date']),2);
			$date_start = date('Y-m-d',strtotime($holiday_date_ary[0]));
			$date_end = date('Y-m-d',strtotime($holiday_date_ary[1]));
		}else{
			$holiday_date_ary = explode('-',trim($_POST['holiday_date']));
			$date_start = date('Y-m-d',strtotime($holiday_date_ary[0]));
			$date_end = date('Y-m-d',strtotime($holiday_date_ary[1]));
		}

	}else{
			$holiday_date_ary = explode('-',trim($_POST['holiday_date']));
			$date_start = date('Y-m-d',strtotime($holiday_date_ary[0]));
			$date_end = date('Y-m-d',strtotime($holiday_date_ary[1]));
		}

	$is_active = $_POST['is_active'];
	$holiday_id = $_POST['holiday_id'];
	$oldone = $_POST['holiday_id'];
	$reason = $db->escape($_POST['reason']);



	/*if($group_id == ''){
		$is_for_all_groups = 1;
	}else{
		$is_for_all_groups = 0;
	}*/
	if(is_array($_POST['group_id'])){
		$is_for_all_groups = 0;
	}else{
		$is_for_all_groups = 1;
	}

	$db->query('BEGIN');



	if(is_numeric($holiday_id)){
		//EDIT EXISTING HOLIDAY
		$result = $db->query("update ss_holidays set date_start='".$date_start."', date_end='".$date_end."', is_active='".$is_active."',
		is_for_all_groups='".$is_for_all_groups."', reason='".$reason."', 
		updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', updated_on = '".date('Y-m-d H:i:s')."' where id = '".$holiday_id."'");
	}else{
		//NEW HOLIDAY	
		$result = $db->query("insert into ss_holidays set date_start='".$date_start."', date_end='".$date_end."', is_active='".$is_active."',
		is_for_all_groups = '".$is_for_all_groups."', reason = '".$reason."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',
		created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on = '".date('Y-m-d H:i:s')."', 
		updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on = '".date('Y-m-d H:i:s')."'");
		$holiday_id = $db->insert_id;
	}
	if($result){
		$db->query("delete from ss_holiday_groups where holiday_id = '".$holiday_id."'");

		foreach($_POST['group_id'] as $grpid){
			$group_result = $db->query("insert into ss_holiday_groups set holiday_id = '".$holiday_id."', group_id = '".$grpid."'");
		}
	}
	if($result && $db->query('COMMIT') !== false) {
        if(!empty($oldone)){ 
        echo json_encode(array('code' => "1",'msg' => 'Holiday updated successfully'));
        }
        else{	
		echo json_encode(array('code' => "1",'msg' => 'Holiday added successfully'));
	    }
		exit;
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0",'msg' => 'Error: Holiday not saved'));
		exit;
	}
}
//==========================FETCH GROUPS OF TEACHERS - USED IN MESSAGE SECTION=====================
elseif($_POST['action'] == 'fetch_holiday'){
	$holiday_id = $_POST['holiday_id'];
	$holiday = $db->get_row("SELECT * from ss_holidays where id = '".$holiday_id."'");
	if(!empty($holiday)){
		if($holiday->is_for_all_groups == 0){
			$group_ids = $db->get_var("SELECT GROUP_CONCAT(group_id) from ss_holiday_groups where holiday_id = '".$holiday_id."'");
		}
		echo json_encode(array('code' => 1, 'id' => $holiday->id, 'date_start' => my_date_changer($holiday->date_start), 'date_end' => my_date_changer($holiday->date_end), 'reason' => $holiday->reason, 'is_active' => $holiday->is_active, 'is_for_all_groups' => $holiday->is_for_all_groups, 'group_ids' => $group_ids));
	}else{
		echo json_encode(array('code' => 0));
	}
	exit;
}
//=====================DELETE HOLIDAY==================
elseif($_POST['action'] == 'delete_holiday'){
	$holiday_id = $_POST['holiday_id'];
	$rec = $db->query("delete from ss_holidays where id='".$_POST['holiday_id']."'");
	if($rec > 0){
		echo json_encode(array('code' => "1",'msg' => 'Holiday deleted successfully'));
		exit;
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Holiday deletion failed'));
		exit;
	}
}
?>