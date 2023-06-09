<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}
//==========================LIST BASIC FEES=====================
if ($_GET['action'] == 'list_discount_fees') {

	if (in_array("su_discount_manage_fees_list", $_SESSION['login_user_permissions'])) {
		$finalAry = array();
		$discount_fees = $db->get_results("SELECT id, discount_name, discount_unit,
		(case when discount_unit = 'p' then 'Percentage' when discount_unit = 'd' then 'Dollar' when discount_unit = 'L' then 'Pound' end) as discountunit, discount_percent, status as status_id, 
		(CASE WHEN status=1 THEN 'Active' ELSE 'Inactive' END) AS status, DATE_FORMAT(created_on,'%m/%d/%Y') AS created_on 
		FROM ss_fees_discounts where status != 2 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY id DESC", ARRAY_A);

		$family_schedule_payment = get_family_schedule_payment();

		$schedule_payment_cron = get_schedule_payment_cron();

		for ($i = 0; $i < count((array)$discount_fees); $i++) {
			// if($discount_fees[$i]['discount_unit'] == "p"){
			// 	$discount_fees[$i]['discount_percent'] = $discount_fees[$i]['discount_percent']."%";
			// }else{
			// 	$discount_fees[$i]['discount_percent'] = "$".$discount_fees[$i]['discount_percent'];
			// }

			$feesdiscounts_familys =  $db->get_results("SELECT d.*,sfd.id as stu_fee_dis_id,s.user_id,s.family_id from ss_student_feesdiscounts as sfd inner join ss_fees_discounts as d on d.id = sfd.fees_discount_id inner join ss_student as s on s.user_id = sfd.student_user_id where d.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and d.status <> 2 and d.id=" . $discount_fees[$i]['id'] . " and sfd.status=1 and sfd.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' group by s.family_id");

			$discount_fees[$i]['discount_percent'] = $discount_fees[$i]['discount_percent'];
			if ($discount_fees[$i]['status_id'] == 1 && count((array)$feesdiscounts_familys) > 0) {
				$obj_array = payment_confirmation_check($family_schedule_payment, $schedule_payment_cron);
				$discount_fees[$i]['note_text'] = $obj_array->confirm_msg;
				$discount_fees[$i]['confirm_check_con'] = $obj_array->confirm_check_con;
			}
		}

		$finalAry['data'] = $discount_fees;
		echo json_encode($finalAry);
		exit;
	}
}
//==========================ADD BASIC FEES=====================
elseif ($_POST['action'] == 'discount_fees_add') {
	if (in_array("su_discount_manage_fees_create", $_SESSION['login_user_permissions'])) {
		$discount_name = $_POST['discount_name'];

		if (isset($discount_name)) {
			$discount_exist = $db->get_row("SELECT * from ss_fees_discounts where discount_name='" . trim($db->escape($discount_name)) . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
			if (!empty($discount_exist)) {
				echo json_encode(array('code' => "0", 'msg' => '<p class="text-danger"> Discount is already exists in record </p>'));
				exit;
			}
		}

		$discount_percent = $_POST['discount_percent'];
		$status = $_POST['status'];
		$discount_unit = $_POST['discount_unit'];
		// echo "insert into ss_fees_discounts set discount_name='".$discount_name."', status='".$status."', 
		// discount_percent='".$discount_percent."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."', 
		// updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'";
		// die;
		$discount_fees = $db->query("insert into ss_fees_discounts set discount_name='" . trim($db->escape($discount_name)) . "', status='" . $status . "', 
		discount_percent='" . $discount_percent . "', discount_unit='" . $discount_unit . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on='" . date('Y-m-d H:i:s') . "', 
		updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
	}
	$discount_fees_id = $db->insert_id;
	if ($discount_fees_id > 0) {
		$dispMsg = "<p class='text-success'>  Fees Discounts Added Successfully </p>";
		echo json_encode(array('code' => "1", 'msg' => $dispMsg));
		exit;
	} else {
		$return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Fees Discounts Not Added </p>');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
//==========================EDIT BASIC FEES=====================
elseif ($_POST['action'] == 'discount_fees_edit') {

	if (in_array("su_discount_manage_fees_edit", $_SESSION['login_user_permissions'])) {

		try {
			$db->query('BEGIN');

			$id = $_POST['discount_fees_id'];
			$discount_sign = $_POST['discount_unit'];
			$discount_amount = $_POST['discount_percent'];
			$conn_check = true;

			$discount_name = $_POST['discount_name'];

			if (isset($discount_name)) {
				$discount_exist = $db->get_row("SELECT * from ss_fees_discounts where id <> '" . $id . "' and discount_name='" . trim($db->escape($discount_name)) . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
				if (!empty($discount_exist)) {
					echo json_encode(array('code' => "0", 'msg' => '<p class="text-danger"> Discount is already exists in record </p>'));
					exit;
				}
			}

			$student_discount = $db->get_row("SELECT id FROM ss_student_feesdiscounts where fees_discount_id='" . $id . "'");

			if (!empty($student_discount) && $_POST['status'] == 0) {
				echo json_encode(array('code' => "0", 'msg' => 'Do not inactive a discount because discount is already allocated to a student'));
				exit;
			}

			$basic_fees = $db->query("update ss_fees_discounts set discount_name='" . trim($db->escape($_POST['discount_name'])) . "',  discount_unit='" . $_POST['discount_unit'] . "',
		discount_percent='" . $_POST['discount_percent'] . "',  status='" . $_POST['status'] . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',  updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $id . "'");


			$process = 0;
			if ($discount_sign != $student_discount->discount_unit && $discount_amount != $student_discount->discount_percent) {
				$process = 1;
			} elseif ($discount_sign == $student_discount->discount_unit && $discount_amount != $student_discount->discount_percent) {
				$process = 1;
			} elseif ($discount_sign != $student_discount->discount_unit && $discount_amount == $student_discount->discount_percent) {
				$process = 1;
			}

			if ($process == 1) {

				$feesdiscounts_familys =  $db->get_results("SELECT d.*,sfd.id as stu_fee_dis_id,s.user_id,s.family_id from ss_student_feesdiscounts as sfd inner join ss_fees_discounts as d on d.id = sfd.fees_discount_id inner join ss_student as s on s.user_id = sfd.student_user_id where d.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and d.status <> 2 and d.id=" . $id . " and sfd.status=1 and sfd.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' group by s.family_id");

				if (count((array)$feesdiscounts_familys) > 0) {


					foreach ($feesdiscounts_familys as $family_info) {
						$family = get_family_schedule_payment($family_info->family_id);
						$schedule_payment_cron = get_schedule_payment_cron($family_info->family_id);


						//SCHEDULE PAYMENT CHANGED
						if (count((array)$family) > 0) {

							$sch_item_ids = $db->get_var("SELECT GROUP_CONCAT(sch_item_ids) as sch_item_ids FROM `ss_payment_sch_item_cron` where family_id = " . $family_info->family_id . " and session=" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . " and schedule_status <> 0");

							$student_users = explode(",", $family->user_id);

							if (count((array)$student_users) > 0) {
								$total_final_amount = "";
								$old_fee_amount = "";
								foreach ($student_users as $user_id) {

									$user_sch_amount = $db->get_var("select amount from ss_student_fees_items  where student_user_id = '" . $user_id . "' AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND schedule_payment_date >= '" . date('Y-m-d') . "' AND schedule_status IN (0,3)");
									$old_fee_amount += $user_sch_amount;

									$userDataAmount = student_fee_discount($user_id);

									$total_final_amount += $userDataAmount;

									$sql = "update ss_student_fees_items set amount = '" . $userDataAmount . "',  updated_at = '" . date('Y-m-d H:i') . "' where student_user_id = '" . $user_id . "' AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND schedule_payment_date >= '" . date('Y-m-d') . "' AND schedule_status IN (0,3) ";

									if (!empty($sch_item_ids)) {
										$sql .= "AND id NOT IN (" . $sch_item_ids . ")";
									}

									$result = $db->query($sql);

									if (!$result) {
										$conn_check = false;
									}
								}
							}
						}


						//REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
						if (count((array)$schedule_payment_cron) > 0) {

							foreach ($schedule_payment_cron as $data) {

								$db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='" . $data->schedule_unique_id . "',  	family_id ='" . $data->family_id . "', sch_item_ids='" . $data->sch_item_ids . "', schedule_payment_date='" . $data->schedule_payment_date . "', total_amount ='" . $data->old_total_amount . "', wallet_amount = '" . $data->wallet_amount . "', cc_amount = '" . $data->cc_amount . "', schedule_status  = '" . $data->schedule_status . "', retry_count = '" . $data->retry_count . "', session  = '" . $data->session . "', is_approval  = '" . $data->is_approval . "', reason = '" . $data->reason . "', payment_unique_id = '" . $data->payment_unique_id . "', payment_response_code= '" . $data->payment_response_code . "', payment_response= '" . $data->payment_response . "',  is_cancel=1, created_at='" . date('Y-m-d h:i:s', strtotime($data->created_at)) . "', updated_at='" . date('Y-m-d h:i:s', strtotime($data->updated_at)) . "'");

								$payment_sch_item_cron_backup_id = $db->insert_id;

								if ($payment_sch_item_cron_backup_id > 0) {

									$total_final_amount = $db->get_var("select sum(amount) as total_final_amount from ss_student_fees_items  where id IN (" . $data->sch_item_ids . ")");
									$family_data = (object) array_merge((array) $family, (array) $data);
									$family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "", "total_amount" => $total_final_amount, "old_total_amount" => $data->old_total_amount]);

									genrate_and_send_invoice($family_data);

									$result = $db->query("update ss_payment_sch_item_cron set total_amount = '" . $total_final_amount . "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" . $data->id . "'");

									if (!$result) {
										$conn_check = false;
									}
								}
							}
						}
					}
				}
			}


			if ($basic_fees && $conn_check == true  && $db->query('COMMIT') !== false) {
				$dispMsg = "<p class='text-success'> Fees Discounts updated Successfully </p>";
				echo json_encode(array('code' => "1", 'msg' => $dispMsg));
				exit;
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Fees Discounts Not Updated </p>');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} catch (customException $e) {
			$db->query('ROLLBACK');
			CreateLog($_REQUEST, json_encode($e->errorMessage()));
			exit;
		}
	}
	//=====================DELETE BASIC FEES==================
} elseif ($_POST['action'] == 'delete_discount_fees') {
	if (in_array("su_discount_manage_fees_delete", $_SESSION['login_user_permissions'])) {
		if (isset($_POST['id'])) {
			$student_discount = $db->get_var("SELECT id FROM ss_student_feesdiscounts where fees_discount_id='" . $_POST['id'] . "'");

			if ($student_discount) {
				echo json_encode(array('code' => "0", 'msg' => 'Discount is already alloted to a student'));
				exit;
			} else {
				$rec = $db->query("update ss_fees_discounts set status='2' where id='" . $_POST['id'] . "'");
			}
			if ($rec > 0) {

				echo json_encode(array('code' => "1", 'msg' => 'Fees Discounts Deleted Successfully'));
				exit;
			} else {
				$return_resp = array('code' => "0", 'msg' => 'Fees Discounts Not Deletion');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {
			$return_resp = array('code' => "0", 'msg' => 'Error: Process failed');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	}
}
