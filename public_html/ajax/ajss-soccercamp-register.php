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
if($_POST['action'] == 'student_register'){


	$get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc from ss_client_settings where status = 1");
	if(!empty($get_email->new_registration_email_bcc)){
		$emails_bcc = explode(",",$get_email->new_registration_email_bcc);
	}
	if(!empty($get_email->new_registration_email_cc)){
		$emails_cc = explode(",",$get_email->new_registration_email_cc);
	}

	if($_POST['child2_first_name'] != ''){
		$child2_first_name = trim($_POST['child2_first_name']);
		$child2_last_name = trim($_POST['child2_last_name']);
		$child2_dob_dt = $_POST['child2_dob_submit'];

		if (trim($child2_dob_dt) != '') {
			$child2_dob = "'".date('Y-m-d', strtotime($child2_dob_dt))."'";
		}else{
			$child2_dob = "NULL";
		}

		$child2_gender = $_POST['child2_gender'];
		$child2_allergies = $_POST['child2_allergies'];
		$child2_grade = $_POST['child2_grade'];
	}
	

	if($_POST['child3_first_name'] !=''){
		$child3_first_name = trim($_POST['child3_first_name']);
		$child3_last_name = trim($_POST['child3_last_name']);

		$child3_dob_dt = $_POST['child3_dob_submit'];
		if (trim($child3_dob_dt) != '') {
			$child3_dob = "'".date('Y-m-d', strtotime($child3_dob_dt))."'";
		}else{
			$child3_dob = "NULL";
		}

		$child3_gender = $_POST['child3_gender'];
		$child3_allergies = $_POST['child3_allergies'];
		$child3_grade = $_POST['child3_grade'];
	}
	

	if($_POST['child4_first_name'] !=''){
		$child4_first_name = trim($_POST['child4_first_name']);
		$child4_last_name = trim($_POST['child4_last_name']);

		$child4_dob_dt = $_POST['child4_dob_submit'];
		if (trim($child4_dob_dt) != '') {
			$child4_dob = "'".date('Y-m-d', strtotime($child4_dob_dt))."'";
		}else{
			$child4_dob = "NULL";
		}

		$child4_gender = $_POST['child4_gender'];
		$child4_allergies = $_POST['child4_allergies'];
		$child4_grade = $_POST['child4_grade'];
	}


	$parent1_email = $db->escape(trim($_POST['parent1_email']));
	$parent2_email = $db->escape(trim($_POST['parent2_email']));
	$which_is_primary_email = $db->escape(trim($_POST['which_is_primary_email']));

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
	if(!empty($_POST['registration_fee'])){
		$fee = $_POST['registration_fee'];
	}else{
		$fee ='0';
	}
	$studentRegister =  $db->query("insert into ss_sunday_school_reg set 
		father_first_name='".trim($db->escape($_POST['parent1_first_name']))."', 
		father_last_name='".trim($db->escape($_POST['parent1_last_name']))."', 
		father_phone='".trim($db->escape($_POST['parent1_phone']))."',
		father_email='".$parent1_email."',

		mother_first_name='".trim($db->escape($_POST['parent2_first_name']))."', 
		mother_last_name='".trim($db->escape($_POST['parent2_last_name']))."', 
		mother_phone='".trim($db->escape($_POST['parent2_phone']))."',
		mother_email='".$parent2_email."',

		primary_email='".$primary_email."',
		secondary_email='".$secondary_email."',

		primary_contact='".$primary_contact."',

		address_1='".trim($db->escape($_POST['address_1']))."',
		address_2='".trim($db->escape($_POST['address_2']))."',
		
		class_session='".trim($db->escape($_POST['class_session']))."',
		city='".trim($db->escape($_POST['city']))."',
		state='".trim($db->escape($_POST['state']))."',
		country_id='".trim($db->escape($_POST['country_id']))."',
		post_code='".trim($db->escape($_POST['post_code']))."',
		addition_notes='".trim($db->escape($_POST['addition_notes']))."',
		payment_method = 'c',
		session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',
		registerd_by='Parent',
		amount_received = '".trim($db->escape($fee))."',
		created_on='".date('Y-m-d H:i:s')."',
		updated_on='".date('Y-m-d H:i:s')."'");

	$reg_id = $db->insert_id;

	if($reg_id > 0){

		
		if($_POST['child1_first_name'] !=''){
			$data = $db->query("insert into ss_sunday_sch_req_child set 
				sunday_school_reg_id='".$reg_id."',
				first_name='".trim($_POST['child1_first_name'])."',
				last_name='".trim($_POST['child1_last_name'])."',
				dob= '".date('Y-m-d', strtotime($_POST['child1_dob_submit']))."',
				gender='".trim($_POST['child1_gender'])."',
				allergies='".trim($_POST['child1_allergies'])."',
				school_grade = '".$_POST['child1_grade']."',
				created_on='".date('Y-m-d H:i:s')."',
				updated_on='".date('Y-m-d H:i:s')."'");
		}
	

		if($_POST['child2_first_name'] !=''){
			$data = $db->query("insert into ss_sunday_sch_req_child set 
				sunday_school_reg_id='".$reg_id."',
				first_name='".$child2_first_name."',
				last_name='".$child2_last_name."',
				dob=".$child2_dob.",
				gender='".$child2_gender."',
				school_grade = '".$child2_grade."',
				allergies='".$child2_allergies."',
				created_on='".date('Y-m-d H:i:s')."',
				updated_on='".date('Y-m-d H:i:s')."'");
		}
	

		if($_POST['child3_first_name'] !=''){
			$data = $db->query("insert into ss_sunday_sch_req_child set 
				sunday_school_reg_id='".$reg_id."',
				first_name='".$child3_first_name."',
				last_name='".$child3_last_name."',
				dob=".$child3_dob.",
				gender='".$child3_gender."',
				school_grade = '".$child3_grade."',
				allergies='".$child3_allergies."',
				created_on='".date('Y-m-d H:i:s')."',
				updated_on='".date('Y-m-d H:i:s')."'"); 
		}


		if($_POST['child4_first_name'] !=''){
			$data = $db->query("insert into ss_sunday_sch_req_child set 
				sunday_school_reg_id='".$reg_id."',
				first_name='".$child4_first_name."',
				last_name='".$child4_last_name."',
				dob=".$child4_dob.",
				gender='".$child4_gender."',
				school_grade = '".$child4_grade."',
				allergies='".$child4_allergies."',
				created_on='".date('Y-m-d H:i:s')."',
				updated_on='".date('Y-m-d H:i:s')."'"); 
		}
		   
			$cc_no = substr($_POST['credit_card_no'],-4);
			$encoded_cc_no = base64_encode($cc_no);
			 
			$credit_card_type = base64_encode($_POST['credit_card_type']);
			$credit_card_no = base64_encode($_POST['credit_card_no']);
			$credit_card_exp = base64_encode($_POST['credit_card_exp_month'].'-'.$_POST['credit_card_exp_year']);
			$credit_card_cvv = base64_encode($_POST['credit_card_cvv']);
			$bank_acc_no = '';
			$routing_no = '';

			$db->query("insert into ss_sunday_sch_payment set sunday_sch_req_id='".$reg_id."', credit_card_type='".$credit_card_type."', credit_card_no='".$encoded_cc_no."',
				credit_card_exp='".$credit_card_exp."', credit_card_cvv='', bank_acc_no='".$bank_acc_no."',
				routing_no='".$routing_no."'");
			$sql_ret = $db->insert_id;

			$check = $db->get_row("select is_new_registration_open, new_registration_start_date, new_registration_end_date, is_new_registration_free, new_registration_fees_form_head, new_registration_fees, registration_page_termsncond from ss_client_settings where status = 1");

			if ($check->is_new_registration_free == 1) {
			    if($sql_ret > 0 && $db->query('COMMIT') !== false){

			    	$targetUrl = SITEURL.'thankyou.php';
						echo json_encode(array('code' => "1",'msg' => 'Registration first step completed. We are redirecting you to payment page soon.', 'targeturl'=>$targetUrl));
						exit;

				}else{			
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => "Process failed. Please try again.", '_errpos' => 6));
					exit;
				}

			}else{ 
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
				if(isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)){
				$customertoken = $customerPostRequest->customer_token;
				$paymethodtoken = $customerPostRequest->default_paymethod_token;
	 
			   
				
				$fortePaymentParams = array(
					'amount' => $fee,
					'firstName' => trim($db->escape($_POST['parent1_first_name'])),
					'lastName' => trim($db->escape($_POST['parent1_last_name'])),
					'email' => trim($db->escape($primary_email)),
					'phone' => trim($db->escape($_POST['parent1_phone'])),
					'city' => trim($db->escape($_POST['city'])),
					'zip'    => trim($db->escape($_POST['post_code'])),
					'countryCode' => 'US',
					'schedule_item_ids' => 'scheduleitemid_0',
				);
				// echo "<pre>";
				// print_r($fortePaymentParams);
				// die;
			  $forteParams = json_encode($fortePaymentParams);
			  $transactions =  $fortePayment->transactionsWithPaymentToken($customertoken, $paymethodtoken, $forteParams);

				// echo "<pre>";
				// print_r($transactions);
				// die;

			if(isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01") { 
				$trxn_msg = 'Payment Sucessful';
				$response_code = $transactions->response->response_code;
			} else {
				$response_code = "";
			}

			if (!empty(trim($transactions->transaction_id))) {
				$transactionID = $transactions->transaction_id;
			} else {
				$transactionID = "";
			}

				// echo "<pre>";
				// print_r($transactionID);
				// die;

			if(!empty($transactionID) && strtoupper($transactions->response->response_code) == "A01"){
				
					$db->query("update ss_sunday_school_reg set is_paid=1, forte_customer_token='".$customertoken."', updated_on='".date('Y-m-d H:i:s')."' where id = '".$reg_id."'");

					$db->query("update ss_sunday_sch_payment set forte_payment_token='".$paymethodtoken."' where id = '".$sql_ret."'");

					
	
					$db->query("insert into ss_payment_txns set  payment_response_code='".$response_code."', sunday_school_reg_id='".$reg_id."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',  payment_gateway='forte', payment_status=1, payment_unique_id='".$transactionID."', comments='".$_POST['comment_post']."', payment_response='".json_encode($transactions)."',  payment_date='".date('Y-m-d H:i:s')."'");
					$payment_txns_id = $db->insert_id;

					
					if($payment_txns_id > 0 && $db->query('COMMIT') !== false) {

						if(!empty($_POST['state'])){

							$state_name = $db->get_var("SELECT state FROM ss_state WHERE id='".$_POST['state']."' AND is_active=1 ");	
						}else{
							$state_name = "";
						}

						$emailbody = '<table style="border:0;width:100%;" cellpadding="5" ><tbody>
						<tr>
						<td colspan="4"> Dear Parents Assalamu-alaikum<br>
						<br>
						Thank you for registration to ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' '.$_SESSION['icksumm_uat_CURRENT_SESSION'].'. We appreciate your help and cooperation. '.SCHOOL_NAME.' School administrator will get back if any other info is needed. <br>
						<br>
						Please feel free to contact us at <a href="mailto:'.SCHOOL_GEN_EMAIL.'" target="_blank">'.SCHOOL_GEN_EMAIL.'</a><br>
						<br></td>
						</tr>
						<tr>
						<td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
						<tbody>';
						
						
						$emailbody .= '<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>1st Parent Name</strong></td>
						<td style="border:solid 1px #999">'.$_POST['parent1_first_name'].' '.$_POST['parent1_last_name'].'</td>
						<td style="border:solid 1px #999"><strong>1st Parent Phone</strong></td>
						<td style="border:solid 1px #999">'.$_POST['parent1_phone'].'</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>2nd Parent Name</strong></td>
						<td style="border:solid 1px #999">'.$_POST['parent2_first_name'].' '.$_POST['parent2_last_name'].'</td>
						<td style="border:solid 1px #999"><strong>2nd Parent Phone</strong></td>
						<td style="border:solid 1px #999">'.$_POST['parent2_phone'].'</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>1st Parent Email</strong></td>
						<td style="border:solid 1px #999">'.$parent1_email.'</td>
						<td style="border:solid 1px #999"><strong>2nd Parent Email</strong></td>
						<td style="border:solid 1px #999">'.$parent2_email.'</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>Address 1</strong></td>
						<td style="border:solid 1px #999">'.$_POST['address_1'].'</td>
						<td style="border:solid 1px #999"><strong>Address 2</strong></td>
						<td style="border:solid 1px #999">'.$_POST['address_2'].'</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>City</strong></td>
						<td style="border:solid 1px #999">'.$_POST['city'].'</td>
						<td style="border:solid 1px #999"><strong>State</strong></td>
						<td style="border:solid 1px #999">'.$state_name.'</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>Country</strong></td>
						<td style="border:solid 1px #999">USA</td>
						<td style="border:solid 1px #999"><strong>Zipcode</strong></td>
						<td style="border:solid 1px #999">'.$_POST['post_code'].'</td>
						</tr>';


						if(!empty($_POST['child1_first_name'])){
							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:100%" colspan="4"><strong>CHILD 1 INFORMATION</strong></td></tr>';

							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Name</strong></td>
							<td style="border:solid 1px #999;width:30%">'.$_POST['child1_first_name'].' '.$_POST['child1_last_name'].'</td>
							<td style="border:solid 1px #999;width:20%"><strong>Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">'.(trim($_POST['child1_gender']) == "f"?"Female":"Male").'</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>DoB</strong></td>
							<td style="border:solid 1px #999">'.date('m/d/Y', strtotime($_POST['child1_dob_submit'])).'</td>
							<td style="border:solid 1px #999"><strong>Allergies</strong></td>
							<td style="border:solid 1px #999">'.$_POST['child1_allergies'].'</td>
							</tr>';

						}


						if(!empty($_POST['child2_first_name'])){

							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:100%" colspan="4"><strong>CHILD 2 INFORMATION</strong></td></tr>';

							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Name</strong></td>
							<td style="border:solid 1px #999;width:30%">'.$child2_first_name.' '.$child2_last_name.'</td>
							<td style="border:solid 1px #999;width:20%"><strong>Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">'.(trim($child2_gender) == "f"?"Female":"Male").'</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>DoB</strong></td>
							<td style="border:solid 1px #999">'.str_replace("'","",$child2_dob).'</td>
							<td style="border:solid 1px #999"><strong>Allergies</strong></td>
							<td style="border:solid 1px #999">'.$child2_allergies.'</td>
							</tr>';

						}

						if(!empty($_POST['child3_first_name'])){
							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:100%" colspan="4"><strong>CHILD 3 INFORMATION</strong></td></tr>';	

							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Name</strong></td>
							<td style="border:solid 1px #999;width:30%">'.$child3_first_name.' '.$child3_last_name.'</td>
							<td style="border:solid 1px #999;width:20%"><strong>Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">'.(trim($child3_gender) == "f"?"Female":"Male").'</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>DoB</strong></td>
							<td style="border:solid 1px #999">'.str_replace("'","",$child3_dob).'</td>
							<td style="border:solid 1px #999"><strong>Allergies</strong></td>
							<td style="border:solid 1px #999">'.$child3_allergies.'</td>
							</tr>';

						}

						if(!empty($_POST['child4_first_name'])){
							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:100%" colspan="4"><strong>CHILD 4 INFORMATION</strong></td></tr>';

							$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Name</strong></td>
							<td style="border:solid 1px #999;width:30%">'.$child4_first_name.' '.$child3_last_name.'</td>
							<td style="border:solid 1px #999;width:20%"><strong>Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">'.(trim($child4_gender) == "f"?"Female":"Male").'</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>DoB</strong></td>
							<td style="border:solid 1px #999">'.str_replace("'","",$child4_dob).'</td>
							<td style="border:solid 1px #999"><strong>Allergies</strong></td>
							<td style="border:solid 1px #999">'.$child4_allergies.'</td>
							</tr>';

						}
						
						$emailbody .= '<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999;width:100%" colspan="4"><strong>PAYMENT INFORMATION</strong></td></tr>';

						$emailbody .= '<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>Payment Method</strong></td>
						<td style="border:solid 1px #999">Credit Card</td>
						<td style="border:solid 1px #999"><strong>Registration Fee</strong></td>
						<td style="border:solid 1px #999">$'.$_POST['registration_fee'].'</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999;"><strong>Last 4 digits of Credit Card</strong></td>
						<td style="border:solid 1px #999;">'.substr($_POST['credit_card_no'],-4).'</td>
						<td style="border:solid 1px #999;"><strong>Payment Transaction IDs</strong></td>
						<td style="border:solid 1px #999;">'.$transactionID.'</td></tr>';
						
						$emailbody .= '
						</tbody>
						</table>
						<br>
						<br>
						JazakAllah Khair<br>
						'.CENTER_SHORTNAME." ".SCHOOL_NAME.'</td>
						</tr>
						</tbody>
						</table>';


						$emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));

						send_my_mail($primary_email,  SCHOOL_NAME.' - New registration', $emailbody);

						// if (ENVIRONMENT == 'dev') {
						// 	send_my_mail(DEVELOPER_EMAIL, SCHOOL_NAME.' - New registration', $emailbody);
						// }else{
						// 	send_my_mail(SUPPORT_EMAIL, SCHOOL_NAME.' - New registration', $emailbody);
						// }

						if(trim($secondary_email) != ''){
							send_my_mail($secondary_email, SCHOOL_NAME.' - New registration', $emailbody);	
						}

						foreach($emails_bcc as $bcc){
							send_my_mail($bcc,  SCHOOL_NAME.' - New registration', $emailbody);
						}

						foreach($emails_cc as $cc){
							send_my_mail($cc,  SCHOOL_NAME.' - New registration', $emailbody);
						}
						
						$targetUrl = SITEURL.'thankyou.php';
						echo json_encode(array('code' => "1",'msg' => 'Registration first step completed. We are redirecting you to payment page soon.', 'targeturl'=>$targetUrl));
						exit;
					}else{
						$db->query('ROLLBACK');
						echo json_encode(array('code' => "0",'msg' => 'Registration failed because payment details incorrectly', '_errpos' => 1));
						exit;
					} 
					  
				}else{
				//.(isset($forteTranstaionResponse['response']['response_code'])?ucwords($forteTranstaionResponse['response']['response_code']):''), 't' => json_encode($forteTranstaionResponse)
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => "Registration failed due to invalid payment details", '_errpos' => 2));
					exit;
				}

			}else{
				$msg = $customerPostRequest->response->response_desc;
				echo json_encode(array('code' => "0",'msg' => '<p class="text-danger"> '.$msg.' <p>'));
				$db->query('ROLLBACK');
				  exit;
			  }
			}
		}
	}

?>