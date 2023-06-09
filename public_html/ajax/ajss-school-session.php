<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================LIST SCHOOL SESSION=====================
if($_GET['action'] == 'list_school_session'){ 

	$finalAry = array();
		$school_session =$db->get_results("SELECT id, session, current,status, (CASE WHEN current=1 THEN 'YES' ELSE 'NO' END) AS is_current, start_date, 
		end_date,(CASE WHEN status=1 THEN 'Active' ELSE 'Inactive' END) AS is_actve, fees_full_payment_discount_unit, fees_full_payment_discount_value  
		FROM ss_school_sessions ORDER BY id DESC",ARRAY_A);
		for($i=0; $i<count((array)$school_session); $i++){
			$session_status = $school_session[$i]['current'];
			$school_session[$i]['cur_session'] = $school_session[$i]['session'];
			if($_SESSION['icksumm_uat_CURRENT_SESSION'] ==  $school_session[$i]['id'] && $school_session[$i]['current'] == '0'){
				$school_session[$i]['cur_session'] = $school_session[$i]['session'].'<span class="pull-right text-danger" style="font-size: medium;"> T</span>';
			}
			
			// $school_session[$i]['start_date'] = date('m/d/Y', strtotime($school_session[$i]['start_date']));

			$school_session[$i]['start_date'] = my_date_changer($school_session[$i]['start_date']);
			$school_session[$i]['end_date'] = my_date_changer($school_session[$i]['end_date']);
			if($school_session[$i]['fees_full_payment_discount_unit'] == 'p'){
				$school_session[$i]['fees_full_payment_discount_unit'] = '%';
			}elseif($school_session[$i]['fees_full_payment_discount_unit'] == '$'){
				$school_session[$i]['fees_full_payment_discount_unit'] = '$';
			}else{
				$school_session[$i]['fees_full_payment_discount_unit'] = '';
			}
		}
		$finalAry['data'] = $school_session;
		echo json_encode($finalAry);
		exit;

}

//==========================ADD SCHOOL SESSION=====================
elseif($_POST['action'] == 'school_session_add'){

	$school_session = $_POST['school_session'];
	$current_session = $_POST['current_session'];
	$start_date = date('Y-m-d', strtotime($_POST['start_date_submit']));
	$end_date = date('Y-m-d', strtotime($_POST['end_date_submit']));
	$discount_unit = $_POST['discount_unit'];
	$discount_percent = $_POST['discount_percent'];
	$status = $_POST['status'];
	if($end_date <= $start_date){
        $return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Invalid end date <p>');
		echo json_encode($return_resp);
		exit;    
	}
    $school_sessions_date_check = $db->get_results("SELECT start_date FROM ss_school_sessions where status = 1 and start_date ='".$start_date."' ");
    if(!empty($school_sessions_date_check)){
    	$return_resp = array('code' => "0",'msg' => '<p class="text-danger">  start date already exist in school session <p>');
		echo json_encode($return_resp);
		exit;
    }  

	if($current_session == 1){
		$school_old_session_id = $db->get_var("SELECT id FROM ss_school_sessions where current = 1 and status = 1 ");
		$db->query("update ss_school_sessions set current = 0 ");
	}

	if(trim($discount_unit) == '' || trim($discount_percent) == ''){
		$school_session = $db->query("insert into ss_school_sessions set session='".$school_session."', current='".$current_session."', 
		start_date = '".$start_date."', end_date = '".$end_date."',  fees_full_payment_discount_unit = NULL,  
		fees_full_payment_discount_value = NULL,  created_on='".date('Y-m-d H:i')."'");
	}else{
		$school_session = $db->query("insert into ss_school_sessions set session='".$school_session."', current='".$current_session."', 
		start_date = '".$start_date."', end_date = '".$end_date."',  fees_full_payment_discount_unit = '".$discount_unit."',  
		fees_full_payment_discount_value = '".$discount_percent."',  created_on='".date('Y-m-d H:i')."'");
	}


	if($school_session){

		$school_session_id = $db->get_var("SELECT id FROM ss_school_sessions where current = 1 and status = 1 ");


		if($current_session == 1){

		//FEES DISCOUNTS CREATE ON CREATE SESSION
		$old_fees_discounts_list = $db->get_results("SELECT * FROM ss_fees_discounts where session ='".$school_old_session_id."' and status <> 2 ");

		if(!empty($old_fees_discounts_list) && count((array)$old_fees_discounts_list) > 0){

			foreach($old_fees_discounts_list as $row){

				$db->query("insert into ss_fees_discounts set discount_name='".$row->discount_name."', discount_unit='".$row->discount_unit."', 
				discount_percent = '".$row->discount_percent."', session = '".$school_session_id."',  status = '".$row->status."',  
				created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i')."' ");

			}

		}
		

			//GROUPS CREATE ON CREATE SESSION
			// $groups_list = $db->get_results("SELECT * FROM ss_groups where session ='".$school_sessions_id."' and is_deleted = 0 ");

			// if(count((array)$groups_list) > 0){

			// 	foreach($groups_list as $groups_row){

			// 		$db->query("insert into ss_groups set group_name='".$groups_row->group_name."', category='".$groups_row->category."', 
			// 		max_limit = '".$groups_row->max_limit."',  session = '".$school_session_id."',  
			// 		is_active = '".$groups_row->is_active."', is_regis_open='".$groups_row->is_regis_open."',
			// 		created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i')."' ");

			// 	}

			// }


			//CLASSES CREATE ON CREATE SESSION
			// $classes_list = $db->get_results("SELECT * FROM ss_classes where session ='".$school_sessions_id."' and is_active <> 2 ");

			// if(count((array)$classes_list) > 0){

			// 	foreach($classes_list as $classes_row){

			// 		$db->query("insert into ss_classes set class_name='".$classes_row->class_name."', disp_order = '".$classes_row->disp_order."',  session = '".$school_session_id."',  
			// 		is_active = '".$classes_row->is_active."',created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i')."' ");

			// 	}

			// }
		}



		$dispMsg = "<p class='text-success'> School Session added successfully <p>";
		echo json_encode(array('code' => "1",'msg' => $dispMsg));
		exit;
	}else{
		$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> School Session not added <p>');
        CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
//==========================EDIT SCHOOL SESSION=====================
elseif($_POST['action'] == 'school_session_edit'){

		$id = $_POST['school_session_id'];
		$school_session = $_POST['school_session'];
		$current_session = $_POST['current_session'];
		$start_date = date('Y-m-d', strtotime($_POST['start_date_submit']));
		$end_date = date('Y-m-d', strtotime($_POST['end_date_submit']));
		$discount_unit = $_POST['discount_unit'];
		$discount_percent = $_POST['discount_percent'];
		$status = $_POST['status'];
		$school_sessions_id = $db->get_var("SELECT id FROM ss_school_sessions where current = 1 and status = 1 ");
	    if($end_date <= $start_date){
        $return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Invalid end date <p>');
		echo json_encode($return_resp);
		exit;    
	    }
		// if($current_session == 1) {
		// 	$db->query("update ss_school_sessions set current = 0 ");
		//  	$school_session = $db->query("update ss_school_sessions set `session`='".$school_session."', `current` = '".$current_session."', 
		// 	 `start_date` = '".$start_date."', `end_date` = '".$end_date."',  `fees_full_payment_discount_unit` = '".$discount_unit."',  
		// 	 `fees_full_payment_discount_value` = '".$discount_percent."', `status`= '".$status."', `updated_on`='".date('Y-m-d H:i')."' where id = '".$id."'");
		// }else{
		//    $school_session = $db->query("update ss_school_sessions set `session` ='".$school_session."', `current` = '".$current_session.", 
		// 	  `start_date` = '".$start_date."', `end_date` = '".$end_date."',  `fees_full_payment_discount_unit` = '".$discount_unit."',  
		// 	  `fees_full_payment_discount_value` = '".$discount_percent."', `status`= '".$status."', `updated_on`='".date('Y-m-d H:i')."' where id = '".$id."'");
		// }
		if(!empty($school_sessions_id)){
			if($school_sessions_id == $id && $current_session == 0){
				$return_resp = array('code' => "0",'msg' => '<p class="text-danger">At least One Session Must Be Active<p>');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		if($current_session == 1) {
			$db->query("update ss_school_sessions set current = 0 ");

        //FEES DISCOUNTS CREATE ON CREATE SESSION
		$fees_discounts = $db->get_results("SELECT * FROM ss_fees_discounts where session ='".$id."' and status <> 2 ");
		if(count((array)$fees_discounts) == 0){
			
			$old_fees_discounts_list = $db->get_results("SELECT * FROM ss_fees_discounts where session ='".$school_sessions_id."' and status <> 2 ");

			if(count((array)$old_fees_discounts_list) > 0){

				foreach($old_fees_discounts_list as $row){

					$db->query("insert into ss_fees_discounts set discount_name='".$row->discount_name."', discount_unit='".$row->discount_unit."', 
					discount_percent = '".$row->discount_percent."', session = '".$id."',  status = '".$row->status."',  
					created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i')."' ");

				}

			}
		}


		//GROUPS CREATE ON CREATE SESSION
		// $groups= $db->get_results("SELECT * FROM ss_groups where session ='".$id."' and is_deleted = 0 ");
		// if(count((array)$groups) == 0){
		// 	$groups_list = $db->get_results("SELECT * FROM ss_groups where session ='".$school_sessions_id."' and is_deleted = 0 ");

		// 	if(count((array)$groups_list) > 0){

		// 		foreach($groups_list as $groups_row){

		// 			$db->query("insert into ss_groups set group_name='".$groups_row->group_name."', category='".$groups_row->category."', 
		// 			max_limit = '".$groups_row->max_limit."',  session = '".$id."',  
		// 			is_active = '".$groups_row->is_active."', is_regis_open='".$groups_row->is_regis_open."',
		// 			created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i')."' ");

		// 		}

		// 	}
		// }


		//CLASSES CREATE ON CREATE SESSION
		// $classes = $db->get_results("SELECT * FROM ss_classes where session ='".$id."' and is_active <> 2 ");
		// if(count((array)$classes) == 0){
		// 	$classes_list = $db->get_results("SELECT * FROM ss_classes where session ='".$school_sessions_id."' and is_active <> 2 ");

		// 	if(count((array)$classes_list) > 0){

		// 		foreach($classes_list as $classes_row){

		// 			$db->query("insert into ss_classes set class_name='".$classes_row->class_name."', disp_order = '".$classes_row->disp_order."',  session = '".$id."',  
		// 			is_active = '".$classes_row->is_active."',created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i')."' ");

		// 		}

			
		// 	}
		// }



		}

		if(trim($discount_unit) == '' || trim($discount_percent) == ''){
			$school_session = $db->query("update ss_school_sessions set `session`='".$school_session."', `current` = '".$current_session."', 
			`start_date` = '".$start_date."', `end_date` = '".$end_date."',  `fees_full_payment_discount_unit` = NULL,  
			`fees_full_payment_discount_value` = NULL,  `updated_on`='".date('Y-m-d H:i')."' where id = '".$id."'");
		}else{
			$school_session = $db->query("update ss_school_sessions set `session`='".$school_session."', `current` = '".$current_session."', 
				`start_date` = '".$start_date."', `end_date` = '".$end_date."',  `fees_full_payment_discount_unit` = '".$discount_unit."',  
				`fees_full_payment_discount_value` = '".$discount_percent."',  `updated_on`='".date('Y-m-d H:i')."' where id = '".$id."'");
		}
	}else{
		$return_resp = array('code' => "0",'msg' => '<p class="text-danger">At least One Session Must Be Active<p>');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
        if ($school_session) {
			$cur_sess_header = $db->get_row("select * from ss_school_sessions where id = '".$id."' ");

			$_SESSION['icksumm_uat_CURRENT_SESSION'] = $cur_sess_header->id;
			$_SESSION['icksumm_uat_IS_CURRENT_SESSION_YES'] = $cur_sess_header->current;
	
			if($cur_sess_header->current == 1){
				$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $cur_sess_header->session;
			}
			
            $dispMsg = "<p class='text-success'> School Session updated successfully. Your temporary session has become a current session.<p>";
            echo json_encode(array('code' => "1",'msg' => $dispMsg));
            exit;
        } else {
            $return_resp = array('code' => "0",'msg' => '<p class="text-danger"> School Session Not Updated  <p>');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
   
}

//=====================DELETE SCHOOL SESSION==================

elseif($_POST['action'] == 'delete_school_session'){
	if(isset($_POST['id'])){

		$staff_session_map = $db->get_results("SELECT * FROM ss_staff_session_map where session_id ='".$_POST['id']."'");
		$student_session_map = $db->get_results("SELECT * FROM ss_student_session_map where session_id ='".$_POST['id']."'");
		$studentgroupmap = $db->get_results("SELECT * FROM ss_studentgroupmap where session ='".$_POST['id']."'");
		$student_feesdiscounts = $db->get_results("SELECT * FROM ss_student_feesdiscounts where session ='".$_POST['id']."'");
		$ss_user = $db->get_results("SELECT * FROM ss_user where session ='".$_POST['id']."'");
		$school_session = $db->get_results("SELECT * FROM ss_school_sessions where id ='".$_POST['id']."' and current = '1'");

	if(count((array)$staff_session_map) == 0 && count((array)$student_session_map) == 0 && count((array)$studentgroupmap) == 0 && count((array)$student_feesdiscounts) == 0 && count((array)$ss_user) == 0 && count((array)$school_session) == 0){

	
		$rec1 = $db->query("DELETE FROM ss_fees_discounts where session='".$_POST['id']."'");
		$rec2 = $db->query("DELETE FROM ss_groups where session='".$_POST['id']."'");
		$rec3 = $db->query("DELETE FROM ss_classes where session='".$_POST['id']."'");

		$rec = $db->query("DELETE FROM ss_school_sessions where id='".$_POST['id']."'");
		
		if($rec > 0){
			echo json_encode(array('code' => "1",'msg' => 'School Session deleted successfully'));
			exit;
		}else{
			$return_resp = array('code' => "0",'msg' => 'School Session not deletion');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}

	}else{
		echo json_encode(array('code' => "0",'msg' => 'School Session not deleted. session alraedy used'));
		exit;

	}
	
	}else{
		$return_resp = array('code' => "0",'msg' => 'Error: Process failed');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}


//=====================SET SESSION==================

elseif($_POST['action'] == 'set_session'){
	if(isset($_POST['id'])){
		if($_POST['sessionstatus']==1){
			
		$cur_sess_header = $db->get_row("select * from ss_school_sessions where id = '".$_POST['id']."' ");

		$_SESSION['icksumm_uat_CURRENT_SESSION'] = $cur_sess_header->id;
		$_SESSION['icksumm_uat_IS_CURRENT_SESSION_YES'] = $cur_sess_header->current;

		if($cur_sess_header->current == 1){
			$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $cur_sess_header->session;
		}else{
			$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $cur_sess_header->session."<span style='color:#ae0006'> Temporary</span>";
		}
		
		$dispMsg = "<p class='text-success'> Session successfully updated<p>";
		echo json_encode(array('code' => "1",'msg' => $dispMsg));
		exit;
	   }
	   else{
	   	$cur_sess_header = $db->get_row("select * from ss_school_sessions where id = '".$_POST['id']."' ");
	   	$cur_sess = $db->get_row("select * from ss_school_sessions where current = '1' ");
	    $_SESSION['icksumm_uat_CURRENT_SESSION'] = $cur_sess->id;
		$_SESSION['icksumm_uat_IS_CURRENT_SESSION_YES'] = $cur_sess->current;
		$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $cur_sess->session;
		
        $dispMsg = "<p class='text-success'> Session successfully updated<p>";
		echo json_encode(array('code' => "1",'msg' => $dispMsg));
		exit;
	   }

	}else{
		$return_resp = array('code' => "0",'msg' => 'Error: Process failed');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
