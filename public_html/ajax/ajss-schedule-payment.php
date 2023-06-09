<?php
include_once "../includes/config.php";
include_once "../includes/FortePayment.class.php";
if (!empty(get_country()->currency)) {
	$currency = get_country()->currency;
} else {
	$currency = '';
}
$forte_configarray = array(
	'FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID,
	'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY,
	'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID,
	'FORTE_LOCATION_ID' => FORTE_LOCATION_ID,
	'ENVIRONMENT' => ENVIRONMENT,
);
$fortePayment = new FortePayment($forte_configarray);
// print_r($fortePayment);
// die;
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}

//==========================LIST ALL SCHEDULE FOR ADMIN=====================
if ($_POST['action'] == 'list_sechedule_payments') {
	$finalAry = array();
	$user_id = trim($_POST['user_id']);

	$all_student_fees_items = $db->get_results("SELECT item.id, item.student_user_id, item.schedule_payment_date, item.amount, item.created_at,
		     (CASE WHEN item.schedule_status=1 THEN 'Success' WHEN item.schedule_status=2 THEN 'Cancel' WHEN item.schedule_status=3 THEN 'Hold' 
			 WHEN item.schedule_status=4 THEN 'Decline' WHEN item.schedule_status=5 THEN 'Skipped' ELSE 'Pending' END) AS status FROM ss_student_fees_items item 
			 where item.student_user_id='" . $user_id . "' AND item.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ", ARRAY_A);

	for ($i = 0; $i < count((array)$all_student_fees_items); $i++) {
		$amount_val = $all_student_fees_items[$i]['amount'] + 0;
		$all_student_fees_items[$i]['amount'] = $currency . $amount_val;
		$all_student_fees_items[$i]['schedule_payment_date'] = my_date_changer($all_student_fees_items[$i]['schedule_payment_date']);
		$all_student_fees_items[$i]['created_at'] = my_date_changer($all_student_fees_items[$i]['created_at'], 't');
	}

	$finalAry['data'] = $all_student_fees_items;
	echo json_encode($finalAry);
	exit;
} elseif ($_POST['action'] == "start_schedule") {
	$db->query('BEGIN');

	$user_id = trim($_POST['user_id']);
	$userDataAmount = trim($_POST['fee_amount']);
	$schedule_start_date = trim($_POST['schedule_start_date_submit']);
	$recurring_month_count = trim($_POST['quantity']);
	$student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND schedule_status = 0 ");


	if (count((array)$student_fees_items) > 0) {


		foreach ($student_fees_items as $key => $val) {

			$time = strtotime($schedule_start_date);
			$main_date = date("Y-m-d", strtotime("+$key month", $time));
			$modify_date = date("Y-m", strtotime("+$key month", $time));
			$check_date  = $modify_date . '-01';
			$month_lastdate = date("Y-m-t", strtotime($check_date));
			if ($month_lastdate >= $main_date) {
				$next_schedule_start_dates = $main_date;
			} else {
				$next_schedule_start_dates = $month_lastdate;
			}

			$student_fees_items = $db->query("update ss_student_fees_items set schedule_payment_date = '" . $next_schedule_start_dates . "', amount='" . $userDataAmount . "', updated_at = '" . date('Y-m-d H:i') . "' where id = '" . $val->id . "'");
		}


		$db->query('COMMIT');
		echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> Update successfully </p>'));
		exit;
	} else {

		$student_data = $db->get_row("SELECT s.admission_no, s.user_id, s.school_grade, f.id as family_id, f.forte_customer_token,  f.father_phone, f.father_area_code, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_city, f.billing_post_code, pay.credit_card_type, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv, pay.forte_payment_token, pay.id as payment_credential_id FROM ss_user u 
		     	INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id where s.user_id='" . $user_id . "' AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND pay.default_credit_card = 1 AND pay.credit_card_deleted = 0 ");

		//    echo "<pre>";
		//    print_r($student_data);
		//    die;

		if (isset($student_data->credit_card_type) && isset($student_data->credit_card_no) &&  isset($student_data->credit_card_exp) && isset($student_data->credit_card_cvv)) {

			$credit_card_exp = base64_decode($student_data->credit_card_exp);
			$credit_card_expAry = explode('-', $credit_card_exp);

			$CardType = base64_decode($student_data->credit_card_type);;
			$CardNumber = str_replace(' ', '', base64_decode($student_data->credit_card_no));
			$CardExpiryMonth = $credit_card_expAry[0];
			$CardExpiryYear = $credit_card_expAry[1];
			$CardCVV = base64_decode($student_data->credit_card_cvv);


			$cardHolderFirstName = $student_data->father_first_name;
			$cardHolderLastName =  $student_data->father_last_name;
			$userDataEmail =   $student_data->primary_email;
			$userDataPhoneNo = $student_data->father_phone;
			$userDataCity = $student_data->billing_city;
			$userDataZip = $student_data->billing_post_code;

			if (!empty($student_data->forte_customer_token)) {
				$forte_customer_token = $student_data->forte_customer_token;
			} else {
				$forte_customer_token = "";
			}

			$payment_gateway_id = $db->get_var("select id from ss_payment_gateways where status = 'active' ");

			if (is_numeric($user_id)) {

				$forteParamsSend = array(
					'coustomer_token' => $forte_customer_token,
					'paymentAction' => 'Sale',
					'itemName' => 'Fees',
					'itemNumber' => '10001',
					'amount' => $userDataAmount,
					'currencyCode' => 'USD',
					'creditCardType' => $CardType,
					'creditCardNumber' => $CardNumber,
					'expMonth' => $CardExpiryMonth,
					'expYear' => $CardExpiryYear,
					'cvv' => $CardCVV,
					'firstName' => $cardHolderFirstName,
					'lastName' => $cardHolderLastName,
					'email' => $userDataEmail,
					'phone' => $userDataPhoneNo,
					'city' => $userDataCity,
					'zip'    => $userDataZip,
					'countryCode' => 'US',
					'recurring' => 'Yes'
				);


				$forteParams = json_encode($forteParamsSend);

				if (!empty($student_data->forte_customer_token)) {

					if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
						$customertoken = $student_data->forte_customer_token;
						$paymethodtoken = $student_data->forte_payment_token;
					} else {
						$customerPostRequest = $fortePayment->GenratePaymentToken($forteParams);

						if (!empty($student_data->forte_customer_token) && isset($customerPostRequest->paymethod_token)) {
							$customertoken = $student_data->forte_customer_token;
							$paymethodtoken = $customerPostRequest->paymethod_token;
							$db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
						} else {
							$customertoken = "";
							$paymethodtoken = "";
						}
					}
				} else {
					$customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
					if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
						$customertoken = $customerPostRequest->customer_token;
						$paymethodtoken = $customerPostRequest->default_paymethod_token;
					} else {
						$customertoken = "";
						$paymethodtoken = "";
					}
				}


				if (!empty(trim($customerPostRequest->response->response_desc))) {
					$msgError = str_replace("Error[", "<br>Error[", trim($customerPostRequest->response->response_desc));
					$response_msg_error = ltrim($msgError, "<br>");
					$responsemsg = $response_msg_error;
				} else {
					$responsemsg = "Payment processing failed. Please retry";
				}

				if (!empty($customertoken) && !empty($paymethodtoken)) {


					$payment_credential = $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");

					if ($payment_credential) {

						$count = 0;

						for ($i = 0; $i <= ($recurring_month_count - 1); $i++) {

							$time = strtotime($schedule_start_date);
							$main_date = date("Y-m-d", strtotime("+$i month", $time));
							$modify_date = date("Y-m", strtotime("+$i month", $time));
							$check_date  = $modify_date . '-01';
							$month_lastdate = date("Y-m-t", strtotime($check_date));

							if ($month_lastdate >= $main_date) {
								$next_schedule_start_dates = $main_date;
							} else {
								$next_schedule_start_dates = $month_lastdate;
							}

							$student_fees_items = $db->query("insert into ss_student_fees_items set student_user_id='" . $user_id . "', schedule_payment_date = '" . $next_schedule_start_dates . "', amount='" . $userDataAmount . "', schedule_status = 0, created_at = '" . date('Y-m-d H:i') . "'");

							$count++;
						}

						if ($count == $recurring_month_count) {
							$db->query('COMMIT');
							echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> Schedule successfully </p>'));
							exit;
						} else {

							$db->query('ROLLBACK');
							echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'message' => "Payment processing failed. Please retry", 'errpos' => 21]);
							exit;
						}
					} else {
						//ss_family table upadte payment-token and customer-token failed else
						$db->query('ROLLBACK');
						echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'message' => "Payment processing failed. Please retry", 'errpos' => 15]);
						exit;
					}
				} else {
					//forte customer create failed else
					$db->query('ROLLBACK');
					echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'message' => "$responsemsg", 'errpos' => 18]);
					exit;
				}
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '-2'));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Payment credential not found.', '_errpos' => '-1'));
			exit;
		}
	}
} elseif ($_POST['action'] == 'status_schedule_save') {

	$db->query('BEGIN');

	$user_id = trim($_POST['user_id']);
	$item_id = trim($_POST['itemid']);

	if ($_POST['current_status'] == 'Success') {
		$current_status = 1;
	} elseif ($_POST['current_status'] == 'Cancel') {
		$current_status = 2;
	} elseif ($_POST['current_status'] == 'Hold') {
		$current_status = 3;
	} elseif ($_POST['current_status'] == 'Decline') {
		$current_status = 4;
	} elseif ($_POST['current_status'] == 'Skipped') {
		$current_status = 5;
	} elseif ($_POST['current_status'] == 'Pending') {
		$current_status = 0;
	}

	if ($_POST['schedule_status'] == 'Success') {
		$new_status = 1;
	} elseif ($_POST['schedule_status'] == 'Cancel') {
		$new_status = 2;
	} elseif ($_POST['schedule_status'] == 'Hold') {
		$new_status = 3;
	} elseif ($_POST['schedule_status'] == 'Decline') {
		$new_status = 4;
	} elseif ($_POST['schedule_status'] == 'Pending') {
		$new_status = 0;
	} elseif ($_POST['current_status'] == 'Skipped') {
		$current_status = 5;
	} elseif ($_POST['schedule_status'] == 'Resume') {
		$new_status = 0;
	}

	$reason = $_POST['reason'];

	$sql_ret = $db->query("update ss_student_fees_items set schedule_status='" . $new_status . "' where id='" . $item_id . "' and student_user_id='" . $user_id . "' ");

	if ($sql_ret) {


		$family_data  = $db->get_row("SELECT * FROM ss_student s inner join ss_family f on f.id = s.family_id where s.user_id = " . $user_id . " ");
		$fees_items  = $db->get_row("SELECT * FROM ss_student_fees_items where id = " . $item_id . " ");



		$student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $item_id . "', current_status = '" . $current_status . "', new_status='" . $new_status . "', comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "'");

		$emailbody_support .= "Assalamu-alaikum " . $family_data->father_first_name . ' ' . $family_data->father_last_name . ",<br>";
		$emailbody_support .= CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " Payment Information:<br><br>";

		$emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
					<tr>
					<td colspan="2" style="text-align: center;">
							<div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Payment Schedule Information </u></div>
						</td>
					</tr>   
					<tr>
					<td colspan="2" style="text-align: left; padding-top:10px">
					<table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">

				    <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
					<td style="width: 75%; text-align:left;">' . $family_data->first_name . ' ' . $family_data->last_name . '
					</td></tr>

					<tr><td style="width: 25%;" class="color2">Payment Schedule Date:</td>
					<td style="width: 75%; text-align:left;">' . my_date_changer($fees_items->schedule_payment_date) . '
					</td></tr>
					
					<tr><td style="width: 25%;" class="color2">Amount:</td> 
						<td style="width: 75%; text-align:left;">' . $fees_items->amount . '</td></tr>

					<tr><td style="width: 25%;" class="color2"> Payment Schedule Old Status:</td>
					<td style="width: 75%; text-align:left;">' . $_POST['current_status'] . '
					</td></tr>

					<tr><td style="width: 25%;" class="color2"> Payment Schedule New Status:</td>
					<td style="width: 75%; text-align:left;">' . $_POST['schedule_status'] . '
					</td></tr>

					<tr><td style="width: 25%;" class="color2"> Comment:</td>
					<td style="width: 75%; text-align:left;">' . $reason . '
					</td></tr>
					</table>
					</td>
					</tr>         
					</table>';

		$emailbody_support .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME . ' Team';
		$emailbody_support .= "<br><br>For any comments or question, please send email to <a href='mailto:" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
		$emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for the transaction.";

		$mailservice_request_from = MAIL_SERVICE_KEY;
		$mail_service_array = array(
			'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Payment Schedule Information',
			'message' => $emailbody_support,
			'request_from' => $mailservice_request_from,
			'attachment_file_name' => '',
			'attachment_file' => '',
			'to_email' => [$family_data->secondary_email, $family_data->primary_email],
			'cc_email' => '',
			'bcc_email' => ''
		);

		mailservice($mail_service_array);

		// $mailservice_request_from = SUPPORT_EMAIL; 
		// $mail_service_array = array(
		// 						'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME.' Payment Schedule Information',
		// 						'message' => $emailbody_support,
		// 						'request_from' => $mailservice_request_from,
		// 						'attachment_file_name' => '',
		// 						'attachment_file' => '',
		// 						'to_email' => [$family_data->secondary_email, $family_data->primary_email],
		// 						'cc_email' => '',
		// 						'bcc_email' => ''
		// 					);

		// mailservice($mail_service_array);
		if ($db->insert_id > 0) {

			$db->query('COMMIT');
			echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> update successfully </p>'));
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '2'));
			exit;
		}
	} else {

		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '1'));
		exit;
	}
} elseif ($_POST['action'] == 'status_cancel_all_schedule') {

	$db->query('BEGIN');
	$user_id = trim($_POST['user_id']);
	$reason = $_POST['reason'];

	$sql_ret = $db->get_results("select id from ss_student_fees_items  where schedule_status = 0 and student_user_id='" . $user_id . "' ");
	foreach ($sql_ret as $result) {
		$student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $result->id . "', current_status = 0, new_status=2, comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "'");
	}


	if ($student_fees_items) {

		$sql_ret = $db->query("update ss_student_fees_items set schedule_status = 2 where schedule_status=0 and student_user_id='" . $user_id . "' ");


		$family_data  = $db->get_row("SELECT * FROM ss_student s inner join ss_family f on f.id = s.family_id where s.user_id = " . $user_id . " ");


		$student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $item_id . "', current_status = '" . $current_status . "', new_status='" . $new_status . "', comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "'");

		$emailbody_support .= "Assalamu-alaikum " . $family_data->father_first_name . ' ' . $family_data->father_last_name . ",<br>";
		$emailbody_support .= CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " Cancelation Payment Information:<br><br>";

		$emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
						<tr>
						<td colspan="2" style="text-align: center;">
								<div style="font-size: 18px;margin-top:30px; text-align:left;"><u> Payment Schedule Cancelation Information </u></div>
							</td>
						</tr>   
						<tr>
						<td colspan="2" style="text-align: left; padding-top:10px">
						<table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">
	
						<tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
						<td style="width: 75%; text-align:left;">' . $family_data->first_name . ' ' . $family_data->last_name . '
						</td></tr>
	
	                    <tr><td style="width: 25%;" class="color2"> Payment Schedule Status:</td>
						<td style="width: 75%; text-align:left;"> Cancel
						</td></tr>

						<tr><td style="width: 25%;" class="color2"> Comment:</td>
						<td style="width: 75%; text-align:left;">' . $reason . '
						</td></tr>
						</table>
						</td>
						</tr>         
						</table>';


		$emailbody_support .= "<br><br>Thanks";
		$emailbody_support .= "<br>'" . ORGANIZATION_NAME . "'";
		$emailbody_support .= "<br><br>For any comments or question, please send email to <a href='mailto:" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
		$emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for the transaction.";

		$mailservice_request_from = MAIL_SERVICE_KEY;
		$mail_service_array = array(
			'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Payment Schedule Cancelation Information',
			'message' => $emailbody_support,
			'request_from' => $mailservice_request_from,
			'attachment_file_name' => '',
			'attachment_file' => '',
			'to_email' => [$family_data->secondary_email, $family_data->primary_email],
			'cc_email' => '',
			'bcc_email' => ''
		);

		mailservice($mail_service_array);

		// $mailservice_request_from = SUPPORT_EMAIL; 
		// $mail_service_array = array(
		// 						'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME.' Payment Schedule Cancelation Information',
		// 						'message' => $emailbody_support,
		// 						'request_from' => $mailservice_request_from,
		// 						'attachment_file_name' => '',
		// 						'attachment_file' => '',
		// 						'to_email' => [$family_data->secondary_email, $family_data->primary_email],
		// 						'cc_email' => '',
		// 						'bcc_email' => ''
		// 					);

		// mailservice($mail_service_array);
		$db->query('COMMIT');
		echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> update successfully </p>'));
		exit;
	} else {

		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '1'));
		exit;
	}
} elseif ($_POST['action'] == 'view_sechedule_status_history') {

	$user_id = trim($_POST['user_id']);
	$item_id = trim($_POST['itemid']);

	$schedule_items = $db->get_results("SELECT  (CASE i.schedule_payment_date WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(i.schedule_payment_date,'%m/%d/%Y') END) AS schedule_payment_date , h.current_status, h.new_status, h.comments, (CASE h.created_at WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(h.created_at,'%m/%d/%Y %H:%i:%s') END) AS created_at, f.first_name, f.last_name,i.created_by,i.updated_by FROM ss_student_fees_item_status_history h 
	                       INNER JOIN ss_student_fees_items i ON i.id = h.student_fees_items_id
	                       INNER JOIN ss_user u ON u.id = h.created_by_user_id
	                       left JOIN ss_staff f ON f.user_id = u.id
	                       where i.student_user_id='" . $user_id . "' AND h.student_fees_items_id='" . $item_id . "' ");


	foreach ($schedule_items as $sch_item) {
		$sch_item->schedule_payment_date =	my_date_changer($sch_item->schedule_payment_date);
		$sch_item->created_at =	my_date_changer($sch_item->created_at, 't');

		$created_user =	getUserFullName($sch_item->created_by);
		$sch_item->created_by = $created_user;
	}
	// echo "<pre>";
	// print_r($schedule_items);
	// die;


	if (count((array)$schedule_items) > 0) {
		echo json_encode(array('code' => "1", 'msg' => $schedule_items));
		exit;
	} else {
		echo json_encode(array('code' => "0", 'msg' => "Data not found."));
		exit;
	}
} elseif ($_POST['action'] == 'reschedule_payment_items') {

	$reschedule_items = $db->get_results("SELECT i.id AS student_fees_item_id, i.student_user_id, i.schedule_payment_date, i.original_schedule_payment_date, fee.payment_txns_id, pay.id AS pay_creden_id
								FROM  ss_student_fees_transactions fee 
								INNER JOIN ss_student_fees_items i ON i.id = fee.student_fees_item_id 
								INNER JOIN ss_student s ON s.user_id = i.student_user_id
								INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id  
								WHERE fee.payment_txns_id=" . trim($_POST['trxnid']) . "  
								AND i.schedule_status = 4 
								AND pay.default_credit_card = 1");



	if (count((array)$reschedule_items) > 0) {
		$db->query('BEGIN');
		$count = 0;
		$comments = "<strong>Reschedule Payments Status  </strong><br><strong>Preview Status : </strong> Decline  <br> <strong>Current Status : </strong> Pending";
		$childern = "";
		foreach ($reschedule_items as $items) {
			$family_data = $db->get_row("SELECT s.*, f.father_first_name,  f.father_last_name,  f.secondary_email,  f.primary_email FROM ss_student s INNER JOIN ss_family f ON f.id = s.family_id where s.user_id=" . $items->student_user_id . " ");

			$next_payment_date = $db->get_var("SELECT schedule_payment_date FROM ss_student_fees_items WHERE student_user_id = '" . $items->student_user_id . "' AND schedule_status=0 ORDER BY schedule_payment_date ASC");

			$db->query("update ss_payment_txns set payment_credentials_id='" . $items->pay_creden_id . "' where id='" . $items->payment_txns_id . "' ");

			$sql_ret = $db->query("update ss_student_fees_items set schedule_status=0, schedule_payment_date='" . $next_payment_date . "' where id='" . $items->student_fees_item_id . "' ");
			$res = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->student_fees_item_id . "' , current_status=4, new_status=0, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'  ");

			$childern .= $family_data->first_name . ' ' . $family_data->last_name . ', ';
			$count++;
		}



		if ($count == count((array)$reschedule_items)) {



			// $payment_amount = $db->get_row("SELECT SUM(sfi.amount) AS total_amount
			// FROM ss_student_fees_items sfi
			// INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
			// INNER JOIN ss_user u ON u.id = s.user_id
			// INNER JOIN ss_family f ON f.id = s.family_id
			// INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
			// LEFT JOIN ss_student_fees_transactions ON ss_student_fees_transactions.student_fees_item_id = sfi.id
			// LEFT JOIN ss_payment_txns t ON t.id = ss_student_fees_transactions.payment_txns_id
			// LEFT JOIN ss_payment_gateway_codes c ON c.code = t.payment_response_code
			// WHERE sfi.schedule_status = 4 AND u.is_active = 1 AND u.is_deleted = 0 AND pay.default_credit_card =1 AND t.id = '".trim($_POST['trxnid'])."' 
			// GROUP BY payment_unique_id
			// ORDER BY  sfi.original_schedule_payment_date ASC");

			$payment_amount = $db->get_row("SELECT SUM(sfi.amount) AS total_amount
			FROM ss_student_fees_items sfi
			INNER JOIN ss_student_fees_transactions ON ss_student_fees_transactions.student_fees_item_id = sfi.id
			INNER JOIN ss_payment_txns t ON t.id = ss_student_fees_transactions.payment_txns_id
			WHERE sfi.schedule_status = 4 AND t.id = " . trim($_POST['trxnid']) . "
			GROUP BY payment_unique_id
			ORDER BY  sfi.original_schedule_payment_date ASC");


			$emailbody_support .= "Assalamu-alaikum " . $family_data->father_first_name . ' ' . $family_data->father_last_name . ",<br>";
			$emailbody_support .= CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " Reschedule Payment Information:<br><br>";

			$emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
			<tr>
			<td colspan="2" style="text-align: center;">
					<div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Reschedule Payment Information </u></div>
				</td>
			</tr>   
			<tr>
			<td colspan="2" style="text-align: left; padding-top:10px">
			<table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">

			<tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
			<td style="width: 75%; text-align:left;">' . rtrim($childern, ', ') . '
			</td></tr>

			<tr><td style="width: 25%;" class="color2">Payment Schedule Date:</td>
			<td style="width: 75%; text-align:left;">' . date('m/d/Y', strtotime($items->original_schedule_payment_date)) . '
			</td></tr>

				
			<tr><td style="width: 25%;" class="color2">Amount:</td> 
				<td style="width: 75%; text-align:left;">' . $payment_amount->total_amount . '</td></tr>

			<tr><td style="width: 25%;" class="color2"> Payment Schedule Old Status:</td>
			<td style="width: 75%; text-align:left;">Decline
			</td></tr>

			<tr><td style="width: 25%;" class="color2"> Payment Schedule New Status:</td>
			<td style="width: 75%; text-align:left;">Pending
			</td></tr>

			
			<tr><td style="width: 25%;" class="color2">Payment Reschedule Decline Date:</td>
			<td style="width: 75%; text-align:left;">' . date('m/d/Y') . '
			</td></tr>

			</table>
			</td>
			</tr>         
			</table>';

			$emailbody_support .= "<br><br>Thanks";
			$emailbody_support .= "<br>'" . ORGANIZATION_NAME . "'";
			$emailbody_support .= "<br><br>For any comments or question, please send email to <a href='mailto:" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
			$emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for the transaction.";

			$mailservice_request_from = MAIL_SERVICE_KEY;
			$mail_service_array = array(
				'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Reschedule Payment Information',
				'message' => $emailbody_support,
				'request_from' => $mailservice_request_from,
				'attachment_file_name' => '',
				'attachment_file' => '',
				'to_email' => [$family_data->secondary_email, $family_data->primary_email],
				'cc_email' => SCHOOL_GEN_EMAIL,
				'bcc_email' => ''
			);

			mailservice($mail_service_array);
			$db->query('COMMIT');
			echo json_encode(array('code' => "1", 'msg' => ' update successfully'));
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '1'));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '2'));
		exit;
	}
} elseif ($_POST['action'] == 'failed_payment_clear') {
	$check_txn = $db->get_var("SELECT id FROM ss_payment_txns WHERE id ='" . $_POST['trxnid'] . "'");
	$db->query('BEGIN');
	if (!empty($check_txn)) {
		$db->query("update ss_payment_txns set is_clear_payment=0 where id='" . $check_txn . "' ");
		$db->query('COMMIT');
		echo json_encode(array('code' => "1", 'msg' => 'Clear successfully'));
		exit;
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '1'));
		exit;
	}
}
