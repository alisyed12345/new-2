<?php 
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

$one_student_one_lavel = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
//==========================LIST BASIC FEES=====================
if($_GET['action'] == 'list_basic_fees'){ 
	if(in_array("su_basic_fees_list", $_SESSION['login_user_permissions'])){   
		$finalAry = array();

		if($one_student_one_lavel == 1){

			$basic_fees =$db->get_results("SELECT bcf.id, bcf.fee_amount, bcf.status as status_id,  (CASE WHEN bcf.status=1 THEN 'Active' ELSE 'Inactive' END) AS status,
			g.group_name, bcf.group_id,  DATE_FORMAT(bcf.created_on,'%m/%d/%Y') AS created_on 
			FROM ss_basicfees bcf left outer join ss_groups g on bcf.group_id = g.id where bcf.group_id is not null AND bcf.status != 2 
			and bcf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and g.is_deleted=0 ORDER BY id DESC",ARRAY_A);

		}else{

			$basic_fees =$db->get_results("SELECT bcf.id, bcf.fee_amount, bcf.status as status_id,  (CASE WHEN bcf.status=1 THEN 'Active' ELSE 'Inactive' END) AS status,
			g.group_name, bcf.group_id,  DATE_FORMAT(bcf.created_on,'%m/%d/%Y') AS created_on 
			FROM ss_basicfees bcf left outer join ss_groups g on bcf.group_id = g.id where bcf.group_id is not null and bcf.fee_amount <> 0 
			and bcf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and g.is_deleted=0 GROUP BY bcf.fees_unique_id,bcf.session ORDER BY id DESC",ARRAY_A);

		}

		$family_schedule_payment = get_family_schedule_payment();

		$schedule_payment_cron = get_schedule_payment_cron();

		

		if(!empty(get_country()->currency)){
			$currency = get_country()->currency;
		}else{
			$currency = '';
		}

		for($i=0; $i<count((array)$basic_fees); $i++){
			$basic_fees[$i]['fee_amount'] = $currency.($basic_fees[$i]['fee_amount'] + 0);

			if($basic_fees[$i]['status_id'] == 1){
				$obj_array = payment_confirmation_check($family_schedule_payment,$schedule_payment_cron);
				$basic_fees[$i]['note_text'] = $obj_array->confirm_msg;
				$basic_fees[$i]['confirm_check_con'] = $obj_array->confirm_check_con;
			}
		}
		$finalAry['data'] = $basic_fees;
		echo json_encode($finalAry);
		exit;
	}
}
//==========================ADD BASIC FEES=====================
elseif($_POST['action'] == 'basic_fees_add'){
	if(in_array("su_basic_fees_create", $_SESSION['login_user_permissions'])){

		$fees = $_POST['fee_amount'];
		$status = $_POST['status'];
		$fees_unique_id = uniqid();

		if($one_student_one_lavel == 1){
			$group_id = $_POST['group_id'];
			$check_group_exist = $db->get_row("select g.group_name from ss_basicfees f inner join ss_groups g ON g.id = f.group_id where f.status=1 AND f.group_id='".$group_id."' AND f.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
			if(empty($check_group_exist)){
				$basic_fees = $db->query("insert into ss_basicfees set fees_unique_id='".$fees_unique_id."',  fee_amount='".$fees."', status='".$status."', 
				group_id='".$group_id."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',  created_on='".date('Y-m-d H:i:s')."', 
				updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'");
				$basic_fees_id = $db->insert_id;
				if($basic_fees_id > 0){
					$dispMsg = "<p class='text-success'> Basic Fees added successfully <p>";
					echo json_encode(array('code' => "1",'msg' => $dispMsg));
					exit;
				}else{
					$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Basic Fees not added <p>');
					CreateLog($_REQUEST, json_encode($return_resp));
					echo json_encode($return_resp);
					exit;
				}
			}else{
				$dispMsg = "<p class='text-danger'> Basic fees already exist for '".$check_group_exist->group_name."' group <p>";
				echo json_encode(array('code' => "0",'msg' => $dispMsg));
				exit;
			}

		}else{

			$all_groups = $db->get_results("SELECT id,group_name from ss_groups 
			where is_deleted = 0 and is_active=1  and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            if(count((array)$all_groups) > 0){
				
				foreach($all_groups as $group){
					$basic_fees = $db->query("insert into ss_basicfees set fees_unique_id='".$fees_unique_id."', fee_amount='".$fees."', status=1, 
					group_id='".$group->id."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',  created_on='".date('Y-m-d H:i:s')."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'");
				}

				if($basic_fees){
					$dispMsg = "<p class='text-success'> Basic Fees added successfully <p>";
					echo json_encode(array('code' => "1",'msg' => $dispMsg));
					exit;
				}else{
					$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Basic Fees not added <p>');
					CreateLog($_REQUEST, json_encode($return_resp));
					echo json_encode($return_resp);
					exit;
				}
			}else{
				$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Without Groups Basic Fees not added <p>');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}

		}


    }
}
//==========================EDIT BASIC FEES=====================
elseif($_POST['action'] == 'basic_fees_edit'){
	if(in_array("su_basic_fees_edit", $_SESSION['login_user_permissions'])){

	try{	
	$db->query('BEGIN');

	$fees_unique_id = uniqid();

	$error_text="";
	$family_data = get_family_schedule_payment();
	$schedule_payment_cron = get_schedule_payment_cron();


	//SINGLE LAVEL
	if($one_student_one_lavel == 1){
		$id = $_POST['basic_fees_id'];
		$check_group_exist = $db->get_row("select g.group_name from ss_basicfees f inner join ss_groups g ON g.id = f.group_id where f.status=1 AND f.id <> '".$id."' And f.group_id ='".$_POST['group_id']."' and  f.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
		if(empty($check_group_exist)){
			
			$db->query("update ss_basicfees set status='2', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id = '".$id."'");
			$res = $db->query("insert into ss_basicfees set fees_unique_id='".$fees_unique_id."', fee_amount='".$_POST['fee_amount']."', status='".$_POST['status']."', 
			group_id='".$_POST['group_id']."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',  created_on='".date('Y-m-d H:i:s')."', 
			updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'");

			// $student_users = $db->get_results("SELECT s.family_id, s.user_id 
			// FROM ss_student_fees_items sfi
			// INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
			// INNER JOIN ss_user u ON u.id = s.user_id
			// INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
			// INNER JOIN ss_family f ON f.id = s.family_id
			// WHERE sfi.session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND sfi.schedule_payment_date >= '".date('Y-m-d')."' 
			// AND u.is_deleted = 0  AND u.is_locked=0 AND (sfi.schedule_status = 0 OR sfi.schedule_status = 3) GROUP by s.user_id ORDER BY sfi.id desc");
            
            $conn_check = true;
			//SCHEDULE PAYMENT CHANGED AND EMAILED PARENTS
			if(count((array)$family_data) > 0){

				$sch_sessions = $db->get_row("select *  from ss_school_sessions s where s.current = 1 ");
				$finalAllStudentFeeAmount = 0;
				$check_count = 0;
				$family_info_data = [];
				foreach ($family_data as $family) {

					$sch_item_ids = $db->get_var("SELECT GROUP_CONCAT(sch_item_ids) as sch_item_ids FROM `ss_payment_sch_item_cron` where family_id = ".$family->parent_id." and session=".$_SESSION['icksumm_uat_CURRENT_SESSION']." and schedule_status <> 0");
					$family_info_data[$family->parent_id] = $family;

					$student_users = explode(",",$family->user_id);
					if(count((array)$student_users) > 0){
						$total_final_amount = "";
						$old_fee_amount = "";
						foreach ($student_users as $user_id) {

							$user_sch_amount = $db->get_var("select amount from ss_student_fees_items  where student_user_id = '" . $user_id . "' AND session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND schedule_payment_date >= '".date('Y-m-d')."' AND schedule_status IN (0,3)");
							$old_fee_amount += $user_sch_amount;

							$userDataAmount = student_fee_discount($user_id);

							$total_final_amount += $userDataAmount;
							
							$result = $db->query("update ss_student_fees_items set amount = '" . $userDataAmount . "',  updated_at = '" . date('Y-m-d H:i') . "' where student_user_id = '" . $user_id . "' AND session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND schedule_payment_date >= '".date('Y-m-d')."' AND schedule_status IN (0,3) AND id NOT IN (".$sch_item_ids.")");

							if(!$result){
							$conn_check = false;
							}
						
						}

					}

					
				}

			}


			//REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
			if(count((array)$schedule_payment_cron) > 0){

				foreach($schedule_payment_cron as $data){

					$db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."', is_cancel=1,  created_at='".$data->created_at."', updated_at='".$data->updated_at."'");
					$payment_sch_item_cron_backup_id = $db->insert_id;
					
					if($payment_sch_item_cron_backup_id > 0){

						$total_final_amount = $db->get_var("select sum(amount) as total_final_amount from ss_student_fees_items  where id IN (".$data->sch_item_ids.")");
						$family_data = (object) array_merge((array) $family_info_data[$data->family_id], (array) $data);
						$family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "", "total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
						
						genrate_and_send_invoice($family_data);

						$result = $db->query("update ss_payment_sch_item_cron set total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");
						if(!$result){
							$conn_check = false;
						}

					}

				}


			}


			if($res && $conn_check == true && $db->query('COMMIT') !== false){
				$dispMsg = "<p class='text-success'> Basic Fees updated successfully <p>";
				echo json_encode(array('code' => "1",'msg' => $dispMsg));
				exit;
			}else{
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Basic Fees not updated <p>');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		}else{
			$db->query('ROLLBACK');
			$dispMsg = "<p class='text-danger'> Basic fees already exist for '".$check_group_exist->group_name."' group <p>";
			echo json_encode(array('code' => "0",'msg' => $dispMsg));
			exit;
		}

	}
	else{	
	//MULTIPLE LAVEL

		$basicfees = $db->get_row("SELECT bcf.id, bcf.fee_amount, bcf.status FROM ss_basicfees bcf left outer join ss_groups g on bcf.group_id = g.id where bcf.group_id is not null AND bcf.status != 2 
		and bcf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and g.is_deleted=0 ORDER BY id DESC");
		
		$conn_check = true;

		if($basicfees->fee_amount != $_POST['fee_amount']){

			$db->query("update ss_basicfees set status='2', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', updated_on='".date('Y-m-d H:i:s')."' where status=1 ");
			$all_groups = $db->get_results("SELECT id,group_name from ss_groups 
			where is_deleted = 0 and is_active=1  and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
			
			foreach($all_groups as $group){
				$res = $db->query("insert into ss_basicfees set fees_unique_id='".$fees_unique_id."', fee_amount='".$_POST['fee_amount']."', status=1, 
				group_id='".$group->id."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',  created_on='".date('Y-m-d H:i:s')."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'");
			}


			//SCHEDULE PAYMENT CHANGED
			if(count((array)$family_data) > 0){

					$sch_sessions = $db->get_row("select *  from ss_school_sessions s where s.current = 1 ");
					$finalAllStudentFeeAmount = 0;
					$check_count = 0;

                    $family_info_data = [];
					foreach ($family_data as $family) {
			
						$sch_item_ids = $db->get_var("SELECT GROUP_CONCAT(sch_item_ids) as sch_item_ids FROM `ss_payment_sch_item_cron` where family_id = ".$family->parent_id." and session=".$_SESSION['icksumm_uat_CURRENT_SESSION']." and schedule_status <> 0");

						$family_info_data[$family->parent_id] = $family;

						$student_users = explode(",",$family->user_id);
						if(count((array)$student_users) > 0){
							$total_final_amount = 0;
							$old_fee_amount = 0;
							foreach ($student_users as $user_id) {

								$user_sch_amount = $db->get_var("select amount from ss_student_fees_items  where student_user_id = '" . $user_id . "' AND session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND schedule_payment_date >= '".date('Y-m-d')."' AND schedule_status IN (0,3)");
								
								
								$old_fee_amount += (int)$user_sch_amount;
							
								$userDataAmount = student_fee_discount($user_id);

								$total_final_amount += $userDataAmount;

								$sql = "update ss_student_fees_items set amount = '" . $userDataAmount . "',  updated_at = '" . date('Y-m-d H:i') . "' where student_user_id = '" . $user_id . "' AND session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND schedule_payment_date >= '".date('Y-m-d')."' AND schedule_status IN (0,3) ";

								if(!empty($sch_item_ids)){
									$sql .= "AND id NOT IN (".$sch_item_ids.")";

									$error_text="A Payment is under process, please try again after some time. ";
								}

							
								$result = $db->query($sql);

								if(!$result){
									$conn_check = false;
								}
							}


							//schedule_payment_update_notify($family,$old_fee_amount,$total_final_amount);
						}

						
				}

			}

			//REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
			if(count((array)$schedule_payment_cron) > 0){
				
				foreach($schedule_payment_cron as $data){

					$db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."',  is_cancel=1, created_at='".date('Y-m-d h:i:s',strtotime($data->created_at))."', updated_at='".date('Y-m-d h:i:s',strtotime($data->updated_at))."'");

					$payment_sch_item_cron_backup_id = $db->insert_id;
					
					if($payment_sch_item_cron_backup_id > 0){

						$total_final_amount = $db->get_var("select sum(amount) as total_final_amount from ss_student_fees_items  where id IN (".$data->sch_item_ids.")");
						$family_data = (object) array_merge((array) $family_info_data[$data->family_id], (array) $data);
						$family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "","total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
						
						genrate_and_send_invoice($family_data);

						$result = $db->query("update ss_payment_sch_item_cron set total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");

						if(!$result){
							$conn_check = false;
						}
					}

				}

			}

			if($res && $conn_check == true && $db->query('COMMIT') !== false){
				$dispMsg = "<p class='text-success'> Basic Fees updated successfully <p>";
				echo json_encode(array('code' => "1",'msg' => $dispMsg));
				exit;
			}else{
				$db->query('ROLLBACK');
				if(!empty(trim($error_text))){
					$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Basic Fees not updated. '.$error_text.'<p>');
					CreateLog($_REQUEST, json_encode($return_resp));
					echo json_encode($return_resp);

				}else{
					$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Basic Fees not updated. <p>');
					CreateLog($_REQUEST, json_encode($return_resp));
					echo json_encode($return_resp);

				}

				exit;
			}

		}else{
			$db->query('ROLLBACK');
			$dispMsg = "<p class='text-danger'> Basic Fees amount not change <p>";
			echo json_encode(array('code' => "0",'msg' => $dispMsg));
			exit;
		}

	}
}catch (customException $e) {
	$db->query('ROLLBACK');
	CreateLog($_REQUEST, json_encode($e->errorMessage()));
	exit;

}

}

	
}
//=====================DELETE BASIC FEES==================
elseif($_POST['action'] == 'delete_basic_fees'){
    if(in_array("su_basic_fees_delete", $_SESSION['login_user_permissions'])){
		if(isset($_POST['id'])){
			$rec = $db->query("update ss_basicfees set status='2' where id='".$_POST['id']."'");
			if($rec > 0){
				echo json_encode(array('code' => "1",'msg' => 'Basic Fees deleted successfully'));
				exit;
			}else{
				$return_resp = array('code' => "0",'msg' => 'Basic Fees not deletion');
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