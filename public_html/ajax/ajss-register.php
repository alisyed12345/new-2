<?php
include_once "../includes/config.php";
include_once "../includes/FortePayment.class.php";
$forte_configarray = array(
'FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID,
'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY,
'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID,
'FORTE_LOCATION_ID' => FORTE_LOCATION_ID,
'ENVIRONMENT' => ENVIRONMENT,
);
$fortePayment = new FortePayment($forte_configarray);


//==========================STUDENT REGISTER=====================
if ($_POST['action'] == 'student_register') {

$get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc,is_waiting, new_registration_session, registration_page_termsncond,reg_form_term_cond_attach_url from ss_client_settings where status = 1");
if (!empty($get_email->new_registration_email_bcc)) {
$emails_bcc = explode(",", $get_email->new_registration_email_bcc);
}
if (!empty($get_email->new_registration_email_cc)) {
$emails_cc = explode(",", $get_email->new_registration_email_cc);
}


$parent1_email = $db->escape(trim($_POST['parent1_email']));
$parent2_email = $db->escape(trim($_POST['parent2_email']));
$which_is_primary_email = $db->escape(trim($_POST['which_is_primary_email']));

if (!empty(trim($_POST['child1_dob']))) {
$child1_dob = "'".date('Y-m-d', strtotime($_POST['child1_dob']))."'";
$child1_dob_email = date('m/d/Y', strtotime($_POST['child1_dob']));
}else{
$child1_dob = "NULL";
$child1_dob_email = "NULL";
}

if ($which_is_primary_email == "parent1") {
$primary_email = $parent1_email;
$secondary_email = $parent2_email;
$primary_contact = 'Father';
} else {
$primary_email = $parent2_email;
$secondary_email = $parent1_email;
$primary_contact = 'Mother';
}

$db->query("BEGIN");

if (isset($_POST['registration_fee']) && !empty($_POST['registration_fee'])) {
$fee = $_POST['registration_fee'];
} else {
$fee = 0.00;
}

$isWaiting = $get_email->is_waiting;
$response_code = null;
$transactionID = null;

$studentRegister =  $db->query("insert into ss_sunday_school_reg set 
father_first_name='" . trim($db->escape($_POST['parent1_first_name'])) . "', 
father_last_name='" . trim($db->escape($_POST['parent1_last_name'])) . "', 
father_phone='" . trim($db->escape($_POST['parent1_phone'])) . "',
father_email='" . $parent1_email . "',
mother_first_name='" . trim($db->escape($_POST['parent2_first_name'])) . "', 
mother_last_name='" . trim($db->escape($_POST['parent2_last_name'])) . "', 
mother_phone='" . trim($db->escape($_POST['parent2_phone'])) . "',
mother_email='" . $parent2_email . "',
primary_email='" . $primary_email . "',
secondary_email='" . $secondary_email . "',
primary_contact='" . $primary_contact . "',
address_1='" . trim($db->escape($_POST['address_1'])) . "',
address_2='" . trim($db->escape($_POST['address_2'])) . "',
class_session='" . trim($db->escape($_POST['class_session'])) . "',
city='" . trim($db->escape($_POST['city'])) . "',
state='" . trim($db->escape($_POST['state'])) . "',
country_id='" . trim($db->escape($_POST['country_id'])) . "',
post_code='" . trim($db->escape($_POST['post_code'])) . "',
addition_notes='" . trim($db->escape($_POST['addition_notes'])) . "',
payment_method = 'c',
session = '" . $get_email->new_registration_session . "',
is_waiting='".$isWaiting."',
registerd_by='Parent',
amount_received = '" .$db->escape($fee). "',
created_on='" . date('Y-m-d H:i:s') . "',
updated_on='" . date('Y-m-d H:i:s') . "'");

$reg_id = $db->insert_id;

if ($reg_id > 0) {

		if ($_POST['child1_first_name'] != '') {

			$data = $db->query("insert into ss_sunday_sch_req_child set 
			sunday_school_reg_id='" . $reg_id . "',
			first_name='" . trim($_POST['child1_first_name']) . "',
			last_name='" . trim($_POST['child1_last_name']) . "',
			dob=".$child1_dob.",
			gender='" . trim($_POST['child1_gender']) . "',
			allergies='" . trim($_POST['child1_allergies']) . "',
			school_grade = '" . $_POST['child1_grade'] . "',
			created_on='" . date('Y-m-d H:i:s') . "',
			updated_on='" . date('Y-m-d H:i:s') . "'");
		}

	if(isset($_POST['stu_uniq_ids']) && !empty($_POST['stu_uniq_ids'])){
		$stu_uniq_ids = explode(",",ltrim($_POST['stu_uniq_ids'], ','));
	
		foreach($stu_uniq_ids as $uniq_id){
	
			if ($_POST['child'.$uniq_id.'_first_name'] != '') {
				$child_first_name = trim($_POST['child'.$uniq_id.'_first_name']);
				$child_last_name = trim($_POST['child'.$uniq_id.'_last_name']);
				
				if (!empty(trim($_POST['child'.$uniq_id.'_dob']))) {
					$child_dob =  "'".date('Y-m-d', strtotime($_POST['child'.$uniq_id.'_dob']))."'";
					$child_dob_email = date('m/d/Y', strtotime($_POST['child'.$uniq_id.'_dob']));
				}else{
					$child_dob = "NULL";
					$child_dob_email = "NULL";
				}
				
				$child_gender = $_POST['child'.$uniq_id.'_gender'];
				$child_allergies = $_POST['child'.$uniq_id.'_allergies'];
				$child_grade = $_POST['child'.$uniq_id.'_grade'];
			}


			$data = $db->query("insert into ss_sunday_sch_req_child set 
			sunday_school_reg_id='" . $reg_id . "',
			first_name='" . $child_first_name . "',
			last_name='" . $child_last_name . "',
			dob=" . $child_dob . ",
			gender='" . $child_gender . "',
			school_grade = '" . $child_grade . "',
			allergies='" . $child_allergies . "',
			created_on='" . date('Y-m-d H:i:s') . "',
			updated_on='" . date('Y-m-d H:i:s') . "'");

		}
	
	}


	$cc_no = substr($_POST['credit_card_no'], -4);
	$encoded_cc_no = base64_encode($cc_no);

	$credit_card_type = base64_encode($_POST['credit_card_type']);
	$credit_card_no = base64_encode($_POST['credit_card_no']);
	$credit_card_exp = base64_encode($_POST['credit_card_exp_month'] . '-' . $_POST['credit_card_exp_year']);
	$credit_card_cvv = base64_encode($_POST['credit_card_cvv']);
	$bank_acc_no = '';
	$routing_no = '';

	$db->query("insert into ss_sunday_sch_payment set sunday_sch_req_id='" . $reg_id . "', credit_card_type='" . $credit_card_type . "', credit_card_no='" . $encoded_cc_no . "',
			credit_card_exp='" . $credit_card_exp . "', credit_card_cvv='', bank_acc_no='" . $bank_acc_no . "',
			routing_no='" . $routing_no . "'");
	$sql_ret = $db->insert_id;

if ($sql_ret > 0) {

	$check = $db->get_row("select is_new_registration_open, new_registration_start_date, new_registration_end_date, is_new_registration_free, new_registration_fees_form_head, new_registration_fees, registration_page_termsncond from ss_client_settings where status = 1");

	$forteParamsSend = array(
		'creditCardType' => trim($db->escape($_POST['credit_card_type'])),
		'creditCardNumber' => trim($db->escape($_POST['credit_card_no'])),
		'expMonth' => trim($db->escape($_POST['credit_card_exp_month'])),
		'expYear' => trim($db->escape($_POST['credit_card_exp_year'])),
		'cvv' => trim($db->escape($_POST['credit_card_cvv'])),
		'firstName' => trim($db->escape($_POST['parent1_first_name'])),
		'lastName' => trim($db->escape($_POST['parent1_last_name'])),
		'email' => trim($db->escape($primary_email)),
		'phone' => trim($db->escape($_POST['parent1_phone'])),
		'city' => trim($db->escape($_POST['city'])),
		'zip'    => trim($db->escape($_POST['post_code'])),
		'countryCode' => 'US',
	);

	$forteParams = json_encode($forteParamsSend);
	$customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
	if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
		$customertoken = $customerPostRequest->customer_token;
		$paymethodtoken = $customerPostRequest->default_paymethod_token;

		// echo "<pre>";
		// print_r($fortePaymentParams);
		// die;
		$process = false;
		if ($isWaiting == 1) {
			$process = true;
			$ispaid = 0;
		}else{

			$fortePaymentParams = array(
				'amount' => $db->escape($fee),
				'firstName' => trim($db->escape($_POST['parent1_first_name'])),
				'lastName' => trim($db->escape($_POST['parent1_last_name'])),
				'email' => trim($db->escape($primary_email)),
				'phone' => trim($db->escape($_POST['parent1_phone'])),
				'city' => trim($db->escape($_POST['city'])),
				'zip'    => trim($db->escape($_POST['post_code'])),
				'countryCode' => 'US',
				'schedule_item_ids' => 'scheduleitemid_0',
			);

			$forteParams = json_encode($fortePaymentParams);
			$transactions =  $fortePayment->transactionsWithPaymentToken($customertoken, $paymethodtoken, $forteParams);

			if(isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01" && !empty(trim($transactions->transaction_id)) && $reg_id > 0 && $sql_ret > 0) {

				$trxn_msg = 'Payment Sucessful';
				$response_code = $transactions->response->response_code;
				$transactionID = $transactions->transaction_id;

				$comments = "Registration fees and registration id " . $reg_id . " ";
				$db->query("insert into ss_payment_txns amount ='" . $fee . "', payment_response_code='" . $response_code . "', sunday_school_reg_id='" . $reg_id . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',  payment_gateway='forte', payment_status=1, payment_unique_id='" . $transactionID . "', comments='" . $comments . "', payment_response='" . json_encode($transactions) . "',  payment_date='" . date('Y-m-d H:i:s') . "'");
				$payment_txns_id = $db->insert_id;

					if ($sql_ret > 0 && $payment_txns_id > 0 && $reg_id > 0 ) {
						$process = true;
						$ispaid = 1;
					} else {
						$db->query('ROLLBACK');
						echo json_encode(array('code' => "0", 'msg' => 'Registration failed because payment details incorrectly', '_errpos' => 1));
						exit;
					}

			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0", 'msg' => 'Registration failed because payment details incorrectly', '_errpos' => 13));
				exit;
			}

		}


		if($process == true && $db->query('COMMIT') !== false){

				$db->query("update ss_sunday_school_reg set is_paid='" . $ispaid . "', forte_customer_token='" . $customertoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $reg_id . "'");
				
				$db->query("update ss_sunday_sch_payment set forte_payment_token='" . $paymethodtoken . "' where id = '" . $sql_ret . "'");
				
				$comments = "Registration fees and registration id " . $reg_id . " ";


				if (!empty($_POST['state'])) {
	
					$state_name = $db->get_var("SELECT state FROM ss_state WHERE id='" . $_POST['state'] . "' AND is_active=1 ");
				} else {
					$state_name = "";
				}

				$emailbody = '<table style="border:0;font-family: Verdana, Geneva, sans-serif;" cellpadding="5"><tbody>
				<tr>
				<td colspan="4"> Dear Parents Assalamu-alaikum<br>
				<br>
				Thank you for registration to ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . '. We appreciate your help and cooperation. ' . SCHOOL_NAME . '  administrator will get back if any other info is needed. <br>
				<br>
				Please feel free to contact us at <a href="mailto:' . SCHOOL_GEN_EMAIL . '" target="_blank">' . SCHOOL_GEN_EMAIL . '</a><br>
				<br></td>
				</tr>
				<tr>
				<td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
				<tbody>';


				$emailbody .= '<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>1st Parent Name</strong></td>
				<td style="border:solid 1px #999">' . $_POST['parent1_first_name'] . ' ' . $_POST['parent1_last_name'] . '</td>
				<td style="border:solid 1px #999"><strong>1st Parent Phone</strong></td>
				<td style="border:solid 1px #999">' . $_POST['parent1_phone'] . '</td>
				</tr>';
				if (!empty($_POST['parent2_first_name'])) {
					$emailbody .= '<tr style="border:solid 1px #999">
					<td style="border:solid 1px #999"><strong>2nd Parent Name</strong></td>
					<td style="border:solid 1px #999">' . $_POST['parent2_first_name'] . ' ' . $_POST['parent2_last_name'] . '</td>
					<td style="border:solid 1px #999"><strong>2nd Parent Phone</strong></td>
					<td style="border:solid 1px #999">' . $_POST['parent2_phone'] . '</td>
					</tr>';
				}
				$emailbody .= '<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>1st Parent Email</strong></td>
				<td style="border:solid 1px #999">' . $parent1_email . '</td>';
				
				if (!empty($_POST['parent2_email'])) {
					$emailbody .= '<td style="border:solid 1px #999"><strong>2nd Parent Email</strong></td>
				<td style="border:solid 1px #999">' . $parent2_email . '</td>';
				} else {
					$emailbody .= '<td style="border:solid 1px #999;"></td>
					<td style="border:solid 1px #999;"></td>';
				}
				$emailbody .= '</tr>
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>Address 1</strong></td>
				<td style="border:solid 1px #999">' . $_POST['address_1'] . '</td>
				<td style="border:solid 1px #999"><strong>Address 2</strong></td>
				<td style="border:solid 1px #999">' . $_POST['address_2'] . '</td>
				</tr>
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>City</strong></td>
				<td style="border:solid 1px #999">' . $_POST['city'] . '</td>
				<td style="border:solid 1px #999"><strong>State</strong></td>
				<td style="border:solid 1px #999">' . $state_name . '</td>
				</tr>
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>Country</strong></td>
				<td style="border:solid 1px #999">USA</td>
				<td style="border:solid 1px #999"><strong>Zipcode</strong></td>
				<td style="border:solid 1px #999">' . $_POST['post_code'] . '</td>
				</tr>';
				if (!empty($_POST['child1_first_name'])) {

					$emailbody .= '<tr style="border:solid 1px #999">
					<td style="border:solid 1px #999;width:20%"><strong>Child 1 Name</strong></td>
					<td style="border:solid 1px #999;width:30%">' . $_POST['child1_first_name'] . ' ' . $_POST['child1_last_name'] . '</td>
					<td style="border:solid 1px #999;width:20%"><strong>Child 1 Gender</strong></td>
					<td style="border:solid 1px #999;width:30%">' . (trim($_POST['child1_gender']) == "f" ? "Female" : "Male") . '</td>
					</tr>
					<tr style="border:solid 1px #999">
					<td style="border:solid 1px #999"><strong>Child 1 DoB</strong></td>
					<td style="border:solid 1px #999">' .$child1_dob_email. '</td>
					<td style="border:solid 1px #999"><strong>Child 2020/2021 School Grade</strong></td>
					<td style="border:solid 1px #999">' . $_POST['child1_grade'] . '</td>
					</tr>

					<tr style="border:solid 1px #999">
					<td style="border:solid 1px #999"><strong>Child 1 Allergies</strong></td>
					<td style="border:solid 1px #999">' . $_POST['child1_allergies'] . '</td>
					<td style="border:solid 1px #999;"></td>
					<td style="border:solid 1px #999;"></td>
					</tr>';
				}

				if(isset($_POST['stu_uniq_ids']) && !empty($_POST['stu_uniq_ids'])){
					$stu_uniq_ids = explode(",",ltrim($_POST['stu_uniq_ids'], ','));
				    $i = 1;
					foreach($stu_uniq_ids as $uniq_id){

						$i++;
				
						if ($_POST['child'.$uniq_id.'_first_name'] != '') {
							$child_first_name = trim($_POST['child'.$uniq_id.'_first_name']);
							$child_last_name = trim($_POST['child'.$uniq_id.'_last_name']);
							
							if (!empty(trim($_POST['child'.$uniq_id.'_dob']))) {
								$child_dob =  "'".date('Y-m-d', strtotime($_POST['child'.$uniq_id.'_dob']))."'";
								$child_dob_email = my_date_changer($_POST['child'.$uniq_id.'_dob']);
							}else{
								$child_dob = "NULL";
								$child_dob_email = "NULL";
							}
							
							$child_gender = $_POST['child'.$uniq_id.'_gender'];
							$child_allergies = $_POST['child'.$uniq_id.'_allergies'];
							$child_grade = $_POST['child'.$uniq_id.'_grade'];
						}


						$emailbody .= '<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999;width:20%"><strong>Child '.$i.' Name</strong></td>
						<td style="border:solid 1px #999;width:30%">' . $child_first_name . ' ' . $child_last_name . '</td>
						<td style="border:solid 1px #999;width:20%"><strong>Child '.$i.' Gender</strong></td>
						<td style="border:solid 1px #999;width:30%">' . (trim($child_gender) == "f" ? "Female" : "Male") . '</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>Child '.$i.' DoB</strong></td>
						<td style="border:solid 1px #999">' .$child_dob_email  . '</td>
						<td style="border:solid 1px #999"><strong>Child '.$key.' 2020/2021 School Grade</strong></td>
						<td style="border:solid 1px #999">' . $child_grade . '</td>
						</tr>
	
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>Child '.$i.' Allergies</strong></td>
						<td style="border:solid 1px #999">' . $child_allergies . '</td>
						<td style="border:solid 1px #999;"></td>
						<td style="border:solid 1px #999;"></td>
						</tr>';
					}
				}

				$emailbody .= '
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>Payment Method</strong></td>
				<td style="border:solid 1px #999">Credit Card</td>';
				if($isWaiting == 0){
				$emailbody .= '<td style="border:solid 1px #999"><strong>Registration Fee</strong></td><td style="border:solid 1px #999">$' .$fee. '</td>';
				}
				$emailbody .= '</tr>';

				$emailbody .= '<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999;"><strong>Last 4 digits of Credit Card</strong></td>
				<td style="border:solid 1px #999;">' . substr($_POST['credit_card_no'], -4) . '</td>';
				if($isWaiting == 0){
				$emailbody .= '<td style="border:solid 1px #999;"><strong>Transaction ID</strong></td><td style="border:solid 1px #999;">'.$transactionID.'</td>';
				}
				$emailbody .= '</tr>';

				if($isWaiting == 0){
					$reg_status = "Success";
				}else{
					$reg_status = "Waiting";
				}
				$emailbody .= '<tr style="border:solid 1px #999"><td style="border:solid 1px #999;"><strong>Registration Status</strong></td><td style="border:solid 1px #999;">'.$reg_status.'</td></tr>';

				$emailbody .= '
				</tbody>
				</table>';
				$emailbody .= $get_email->registration_page_termsncond;
				$emailbody .= '<br>
				<br>
				JazakAllah Khair<br>
				' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Team</td>
				</tr>
				</tbody>
				</table>';

				$attachmentFiles = SITEURL . 'email_pdf/student-reg-term-condition-1631095148.pdf';
				$emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));
				$email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - New registration';
				//$mailservice_request_from = SUPPORT_EMAIL;  
				$sec_email = "";
				if (trim($secondary_email) != '') {
					$sec_email = $secondary_email;
				}
				$bcc_email = "";
				foreach ($emails_bcc as $bcc) {
					$bcc_email = $bcc;
				}

				$cc_email = "";
				foreach ($emails_cc as $cc) {
					$cc_email = $cc;
				}

				$mail_service_array = array(
					'subject' => $email_subject,
					'message' => $emailbody,
					'request_from' => MAIL_SERVICE_KEY,
					'attachment_file_name' => [],
					'attachment_file' => [],
					'to_email' => [$primary_email, $sec_email],
					'cc_email' => [$cc_email],
					'bcc_email' => $bcc_email
				);

				mailservice($mail_service_array);
				$targetUrl = SITEURL . 'thankyou.php';
				echo json_encode(array('code' => "1", 'msg' => 'Student successfully registered. We are redirecting you to payment page soon.', 'targeturl' => $targetUrl));
				exit;


		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Registration failed because details incorrectly', '_errpos' => 1));
			exit;
		}
	
	} else {
		$msg = $customerPostRequest->response->response_desc;
		echo json_encode(array('code' => "0", 'msg' =>'' . $msg . ''));
		$db->query('ROLLBACK');
		exit;
	}
}else{
	echo json_encode(array('code' => "0", 'msg' => 'Registration failed.', '_errpos' => 13));
	$db->query('ROLLBACK');
	exit;
}
}else{
echo json_encode(array('code' => "0", 'msg' => 'Registration failed.', '_errpos' => -11));
$db->query('ROLLBACK');
exit;
}
}
