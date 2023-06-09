<?php
include_once "../includes/config.php";

//==========================EDIT FAMILY PERSONAL=====================
if($_POST['action'] == 'new_session_submit'){
	$db->query('BEGIN');
	$ip_server = $_SERVER['SERVER_ADDR'];

	if(!empty($_POST['confirm_reg_stu'])){
		foreach ($_POST['confirm_reg_stu'] as $userid => $val) {

			$result =  $db->query("update ss_user set session = ".$_SESSION['icksumm_uat_CURRENT_SESSION'].", updated_on='".date('Y-m-d H:i:s')."' where id='".$userid."' ");

			/*$stu_new_session =  $db->query("insert into ss_new_session_student_varification set student_user_id = '".$userid."', payment_type = '".$_POST['payment_method']."',  ip_address = '".$ip_server."', re_new_date='".date('Y-m-d H:i:s')."' ");*/
		}
	}

	$sql_RetFamily =  $db->query("update ss_family set father_first_name='".trim($db->escape($_POST['father_first_name']))."', 
		father_last_name='".trim($db->escape($_POST['father_last_name']))."',
		father_phone='".trim($db->escape($_POST['father_phone']))."',mother_first_name='".trim($db->escape($_POST['mother_first_name']))."',
		mother_last_name='".trim($db->escape($_POST['mother_last_name']))."',
		mother_phone='".trim($db->escape($_POST['mother_phone']))."',
		secondary_email='".trim($db->escape($_POST['secondary_email']))."',
		updated_on='".date('Y-m-d H:i:s')."' where user_id='".$_SESSION['icksumm_uat_login_userid']."' ");

	if($sql_RetFamily) {
		if($_POST['payment_method'] == 'credit_card'){
			$credit_card_type = base64_encode($_POST['credit_card_type']);
			$credit_card_no = base64_encode($_POST['credit_card_no']);
			$credit_card_exp = base64_encode($_POST['credit_card_exp_month'].'-'.$_POST['credit_card_exp_year']);
			$credit_card_cvv = base64_encode($_POST['credit_card_cvv']);
			$bank_acc_no = '';
			$routing_no = '';

		}else{
			$credit_card_type = '';
			$credit_card_no = '';
			$credit_card_exp = '';
			$credit_card_cvv = '';
			$bank_acc_no = base64_encode($_POST['bank_acc_no']);
			$routing_no = base64_encode($_POST['routing_no']);
		}

		$payment_credential = $db->get_row("select * from ss_paymentcredentials where id='".$_POST['paymentcred_id']."'");

		// $payment_credentials_backup =  $db->query("insert into ss_paymentcredentials_backup set 
		// 	paymentcredentials_id='".$_POST['paymentcred_id']."', credit_card_type='".$payment_credential->credit_card_type."',credit_card_no='".$payment_credential->credit_card_no."',
		// 	credit_card_exp='".$payment_credential->credit_card_exp."',credit_card_cvv='".$payment_credential->credit_card_cvv."',bank_acc_no='".$payment_credential->bank_acc_no."',
		// 	routing_no='".$payment_credential->routing_no."', family_id='".$payment_credential->family_id."', created_on='".date('Y-m-d H:i:s')."' ");

		$payment_credentials_backup =  $db->query("insert into ss_paymentcredentials_backup set 
			paymentcredentials_id='".$_POST['paymentcred_id']."', credit_card_type='".$payment_credential->credit_card_type."',credit_card_no='".$payment_credential->credit_card_no."',
			credit_card_exp='".$payment_credential->credit_card_exp."',bank_acc_no='".$payment_credential->bank_acc_no."',
			routing_no='".$payment_credential->routing_no."', family_id='".$payment_credential->family_id."', created_on='".date('Y-m-d H:i:s')."' ");

		if($payment_credentials_backup) {
			// $sql_ret = $db->query("update ss_paymentcredentials set credit_card_type='".$credit_card_type."',credit_card_no='".$credit_card_no."',
			// 	credit_card_exp='".$credit_card_exp."',credit_card_cvv='".$credit_card_cvv."',bank_acc_no='".$bank_acc_no."',
			// 	routing_no='".$routing_no."', created_on='".date('Y-m-d H:i:s')."' 
			// 	where id='".$_POST['paymentcred_id']."'");

			$sql_ret = $db->query("update ss_paymentcredentials set credit_card_type='".$credit_card_type."',credit_card_no='".$credit_card_no."',
				credit_card_exp='".$credit_card_exp."',bank_acc_no='".$bank_acc_no."',
				routing_no='".$routing_no."', created_on='".date('Y-m-d H:i:s')."' 
				where id='".$_POST['paymentcred_id']."'");

			if($sql_ret){
				$db->query("insert into ss_reregistration set 
					family_id='".$payment_credential->family_id."', payable_amount='".trim($db->escape($_POST['payableAmount']))."', is_paid=0, payment_gateway='paypal', payment_type='".$_POST['payment_method']."', session=".$_SESSION['icksumm_uat_CURRENT_SESSION'].", registration_date='".date('Y-m-d H:i:s')."' ");
				$ss_reregistration_id = $db->insert_id;
                
				foreach($_POST['confirm_reg_stu'] as $userid => $val) {
					//ADDED TO INCREASE LEVEL/GROUP AUTOMATICALLY
					$getStudentGroupId = $db->get_var("SELECT group_id FROM ss_studentgroupmap WHERE student_user_id='".$userid."' AND latest=1 ");
					$group_disp_order_id = $db->get_var("SELECT disp_order FROM ss_groups WHERE id='".$getStudentGroupId."' and is_active=1 and is_deleted=0 ");
					$new_group_disp_order_id = $group_disp_order_id + 1;
					$new_group_id = $db->get_var("SELECT id FROM ss_groups WHERE disp_order='".$new_group_disp_order_id."' and is_active=1 and is_deleted=0 ");
					$res = $db->query("update ss_studentgroupmap set latest=0, updated_on='".date('Y-m-d H:i:s')."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."' where student_user_id='".$userid."' and group_id='".$getStudentGroupId."' AND latest=1 ");           
					$db->query("insert into ss_studentgroupmap set student_user_id='".$userid."', group_id='".$new_group_id."', latest=1, created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."' ");
			
					$ss_reregistration_details =  $db->query("insert into ss_reregistration_details set reregistration_id = '".$ss_reregistration_id."', student_user_id = '".$userid."' ");
				}

				$familyInfo =  $db->get_row("select * from ss_family where is_deleted=0 and user_id='".$_SESSION['icksumm_uat_login_userid']."' ");

				if($ss_reregistration_id > 0 ){                
                  $msgCheck = true;

					if($_POST['payment_method'] == 'credit_card'){

						$firstName = trim($db->escape($_POST['father_first_name']));
						$lastName = trim($db->escape($_POST['father_last_name']));
						$countryCode = 'US';

						// Card details
						$creditCardNumber = trim(str_replace(" ","",$_POST['credit_card_no']));
						$creditCardType = $_POST['credit_card_type'];
						$expMonth = $_POST['credit_card_exp_month'];
						$expYear = $_POST['credit_card_exp_year'];
						$cvv = $_POST['credit_card_cvv'];

						$payableAmount = trim($db->escape($_POST['payableAmount'])); // Registration Charge

						// Create an instance of PaypalPro class
						$paypal = new PaypalPro();

						// Payment details
						$paypalParams = array(
							'paymentAction' => 'Sale',
							'itemName' => SCHOOL_NAME." Registration 2020-21",
							'itemNumber' => $ss_reregistration_id,
							'amount' => $payableAmount,
							'currencyCode' => 'USD',
							'creditCardType' => $creditCardType,
							'creditCardNumber' => $creditCardNumber,
							'expMonth' => $expMonth,
							'expYear' => $expYear,
							'cvv' => $cvv,
							'firstName' => $firstName,
							'lastName' => $lastName,
							'city' => $familyInfo->billing_city,
							'zip' => $familyInfo->billing_post_code,
							'countryCode' => $countryCode,
						);

						$paypal_response = $paypal->paypalCall($paypalParams);

						$paymentStatus = strtoupper($paypal_response["ACK"]);

						if($paymentStatus == "SUCCESS"){
							// Transaction info
							$transactionID = $paypal_response['TRANSACTIONID'];
							$paidAmount = $paypal_response['AMT'];

							//MARK IS_PAID TO 1
							$db->query("Update ss_reregistration set is_paid = 1, txn_id ='".$transactionID."', paygateway_response =
							'".json_encode($paypal_response)."' where id = '".$ss_reregistration_id."'");


/*							if(!empty($_POST['child'])){
       
                            $family = $db->get_row("select * from ss_family where user_id='".$_SESSION['icksumm_uat_login_userid']."' ");

							foreach ($_POST['child'] as $rows) {

							$db->query("insert into ss_sunday_school_reg set father_first_name = '".trim($db->escape($_POST['father_first_name']))."', father_last_name = '".trim($db->escape($_POST['father_last_name']))."',  father_area_code = '".$family->father_area_code."',
							    father_phone = '".trim($db->escape($_POST['father_phone']))."',  mother_first_name = '".trim($db->escape($_POST['mother_first_name']))."', mother_last_name = '".trim($db->escape($_POST['mother_last_name']))."', mother_area_code = '".$family->mother_area_code."', mother_phone = '".trim($db->escape($_POST['mother_phone']))."', primary_email = '".$family->primary_email."', secondary_email = '".trim($db->escape($_POST['secondary_email']))."', address_1 = '".$family->billing_address_1."', address_2 = '".$family->billing_address_2."', city = '".$family->billing_city."', state = '".$family->billing_state_id."', country_id = '".$family->billing_country_id."', post_code = '".$family->billing_post_code."', addition_notes = '".$family->addition_notes."', payment_method = '".$_POST['payment_method']."', terms_conditions = 1, ip_address = '".$ip_server."', is_paid = 1, amount_received = '".$paidAmount."', paypal_response = '".json_encode($paypal_response)."', session = '2020-21',  created_on='".date('Y-m-d H:i:s')."' ");
							$sunday_school_reg_id = $db->insert_id;

							$password = uniqid();
							$username = $rows['first_name'].$rows['last_name'].time(); 
							$db->query("insert into ss_user set username = '".$username."', password = '".md5($password)."',  email = '".$family->primary_email."',  session = '2020-21', created_on='".date('Y-m-d H:i:s')."' ");
							$user_id = $db->insert_id;

							$sch_req_child =  $db->query("insert into ss_sunday_sch_req_child set sunday_school_reg_id = '".$sunday_school_reg_id."', first_name = '".$rows['first_name']."',  last_name = '".$rows['last_name']."',  gender = '".$rows['gender']."', dob = '".$rows['dob']."', school_grade = '".$rows['school_grade']."',  user_id = '".$user_id."', created_on='".date('Y-m-d H:i:s')."' ");

							}

						}*/

							$msgCheck = true;
						}else{
							//$db->query("Update ss_reregistration set paygateway_response =
							//'".json_encode($paypal_response)."' where id = '".$ss_reregistration_id."'");

							$msgCheck = false;
						}               			
					}
              
					if($msgCheck == true && $db->query('COMMIT') !== false) {
						echo json_encode(array('code' => "1",'msg' => 'Registration successfully completed. We are redirecting you to dashboard.','target_url' => SITEURL.'parents/dashboard.php'));
						exit;
					}else{
						$db->query('ROLLBACK');

						//ADD TRANSACTION INFO AGAIN TO CHECK ISSUE OF PAYMENT FAILURE
						$db->query('BEGIN');
						$resReReg = $db->query("insert into ss_reregistration set 
							family_id='".$payment_credential->family_id."', payable_amount='".trim($db->escape($_POST['payableAmount']))."', is_paid=0, payment_gateway='paypal', payment_type='".$_POST['payment_method']."', session='2020-21', registration_date='".date('Y-m-d H:i:s')."' ");
						$ss_reregistration_id = $db->insert_id;
						
						foreach($_POST['confirm_reg_stu'] as $userid => $val) {
							$ss_reregistration_details =  $db->query("insert into ss_reregistration_details set reregistration_id = '".$ss_reregistration_id."', student_user_id = '".$userid."' ");
						}

						if($resReReg && $ss_reregistration_details) {
							$db->query('COMMIT');
						}else{
							$db->query('ROLLBACK');
						}

						echo json_encode(array('code' => "0",'msg' => 'Process failed','_errpos'=>'1'));
						exit;
					}
				}else{
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => 'Process failed','_errpos'=>'6'));
					exit;
				}
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0",'msg' => 'Process failed','_errpos'=>'5'));
				exit;
			}

		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Process failed','_errpos'=>'4'));
			exit;
		}
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "2",'msg' => 'Process failed','_errpos'=>'3'));
		exit;
	}
}elseif($_POST['action'] == 'get_stu_payment'){

	$payment_method = $_POST['payment_method'];
	$total_student = count((array)$_POST['confirm_reg_stu']);
	$family_user_id = $_SESSION['icksumm_uat_login_userid'];

	//TEACHER KIDS
	if(isset($_POST['confirm_reg_stu']) && $total_student > 0){
		$one_stu_fees_teacher_kid = $db->get_var("select monthly_payment from ss_session_wise_students_fees where type='regular_student' AND number_of_student='1' AND session='2020-21'  AND status=1 ");	
        //sunday_school_teacher_kid
		$family_type = $db->get_results("select m.user_id from ss_usertypeusermap m  INNER JOIN ss_usertype t ON m.user_type_id = t.id where m.user_id='".$family_user_id."' AND t.user_type_code= 'UT02' ");

		if(count((array)$family_type) > 0){

			$payment_fees = $db->get_row("select * from ss_session_wise_students_fees where type='sunday_school_teacher_kid' AND number_of_student='".$total_student."' AND session='2020-21'  AND status=1 ");

			if($payment_method == 'credit_card'){

                $orignal_payment = ($one_stu_fees_teacher_kid * 9);

				$totalStudentFeeAmount = $payment_fees->one_time_payment;
				$fees_payment_single = $payment_fees->one_time_payment / $total_student;
				$fees_payment_type =  'One Time'; 

			}else{
				$orignal_payment = $one_stu_fees_teacher_kid;

				$totalStudentFeeAmount = $payment_fees->monthly_payment;
				$fees_payment_single = $payment_fees->monthly_payment / $total_student;
				$fees_payment_type =  'Monthly';

			}

            $actualtotalamount = $total_student * $orignal_payment;

			$newArrayUser = [];
			foreach ($_POST['confirm_reg_stu'] as $useridstu) {

				$students = $db->get_results("select  ss_student.user_id, ss_student.first_name, ss_student.last_name from ss_student INNER JOIN ss_user ON ss_user.id = ss_student.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = ss_user.id where ss_student.user_id='".$useridstu."' AND ss_user.is_deleted=0 AND  ss_user.is_active=1");

				foreach ($students as  $value) {

					//$newArrayUser[] = $value; 
					$newArrayUser[] = array('fees'=>$fees_payment_single, 'orignal_fee' => $orignal_payment, 'stu_type' => SCHOOL_NAME.' Teachers',  'stu_user_id'=> $value->user_id, 'first_name'=> $value->first_name, 'last_name'=> $value->last_name); 
				}
			}

			//echo json_encode(array('code' => "1", 'msg' => $newArrayUser,  'pyment_type'=> $fees_payment_type, 'fees'=>$fees_payment_single,  'totalamount' => $totalStudentFeeAmount));
			echo json_encode(array('code'=>"1", 'msg'=>$newArrayUser, 'actualtotalamount'=>$actualtotalamount,  'totalamount'=>$totalStudentFeeAmount));
			exit;
	}

	//QURANS SCHOOL & REGULAR
	else{
        //GET NUMBER OF STUDETS IN QURAN SCHOOL OR REGULAR
         $newArrayUser = [];
         $newAryRegStu = [];
         $quranCountAry = [];
		 $regularCountAry = [];
		 
         foreach ($_POST['confirm_reg_stu'] as $useridstu) {
         	//CHECK IS EXISTS IN QURAN SCHOOL
            $student_quran_school = $db->get_row("select s.* from ss_student s INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where s.user_id='".$useridstu."' AND u.is_deleted=0 AND  u.is_active=1");

			if(count((array)$student_quran_school) > 0){

				if($student_quran_school->qur_sch_stu_user_id > 0 && $student_quran_school->qur_sch_stu_user_id != 'null' ){
				    $quranCountAry[] = $student_quran_school->user_id;
				 }else{
				   $regularCountAry[] = $student_quran_school->user_id;
				 }
			}
		}

		////////////////////////////
		$one_stu_fees_quran = $db->get_var("select monthly_payment from ss_session_wise_students_fees where type='regular_student' AND number_of_student='1' AND session='2020-21'  AND status=1 ");
		$payment_fees_quran = $db->get_row("select * from ss_session_wise_students_fees where type='quran_school_student' AND number_of_student='".count((array)$quranCountAry)."' AND session='2020-21'  AND status=1 ");

		if($payment_method == 'credit_card'){
			$orignal_payment = ($one_stu_fees_quran * 9);
			$totalStudentFeeAmount = $payment_fees_quran->one_time_payment;
			$fees_payment_single = $payment_fees_quran->one_time_payment / count((array)$quranCountAry);
		}else{
			$orignal_payment = $one_stu_fees_quran;
			$totalStudentFeeAmount = $payment_fees_quran->monthly_payment;
			$fees_payment_single = $payment_fees_quran->monthly_payment / count((array)$quranCountAry);

		}

		foreach ($quranCountAry as $useridstu) {
			$students = $db->get_results("select  ss_student.user_id, ss_student.first_name, ss_student.last_name from ss_student INNER JOIN ss_user ON ss_user.id = ss_student.user_id  where ss_student.user_id='".$useridstu."' AND ss_user.is_deleted=0 AND  ss_user.is_active=1");

			foreach ($students as  $value) {

				//$newArrayUser[] = $value; 
				$newArrayUser[] = array('fees'=>$fees_payment_single, 'orignal_fee' => $orignal_payment, 'stu_type' => 'Quran School Students', 'stu_user_id'=> $value->user_id, 'first_name'=> $value->first_name, 'last_name'=> $value->last_name); 
			}
		}

		/*-----------------------------------------------------------*/
		$one_stu_fees_regular = $db->get_var("select monthly_payment from ss_session_wise_students_fees where type='regular_student' AND number_of_student='1' AND session='2020-21'  AND status=1 ");
		$payment_fees_regular = $db->get_row("select * from ss_session_wise_students_fees where type='regular_student' AND number_of_student='".count((array)$regularCountAry)."' AND session='2020-21'  AND status=1 ");

		if($payment_method == 'credit_card'){
			$orignal_payment = ($one_stu_fees_regular * 9);
			$totalStudentFeeAmount = $payment_fees_regular->one_time_payment;
			$fees_payment_single = $payment_fees_regular->one_time_payment / count((array)$regularCountAry);
		}else{
			$orignal_payment = $one_stu_fees_regular;
			$totalStudentFeeAmount = $payment_fees_regular->monthly_payment;
			$fees_payment_single = $payment_fees_regular->monthly_payment / count((array)$regularCountAry);

		}

		foreach ($regularCountAry as $useridstu) {
			$students1 = $db->get_results("select  ss_student.user_id, ss_student.first_name, ss_student.last_name from ss_student INNER JOIN ss_user ON ss_user.id = ss_student.user_id  where ss_student.user_id='".$useridstu."' AND ss_user.is_deleted=0 AND  ss_user.is_active=1");

			foreach ($students1 as  $row) {
				//$newArrayUser[] = $value; 
				$newAryRegStu[] = array('fees'=>$fees_payment_single, 'orignal_fee' => $orignal_payment, 'stu_type' => 'Regular Students', 'stu_user_id'=> $row->user_id, 'first_name'=> $row->first_name, 'last_name'=> $row->last_name);
			}
		}

		////////////////////////////////

		$combinArray = array_merge($newArrayUser, $newAryRegStu);

		$totalStudentFeeAmount = 0;
		foreach ($combinArray as $rows) {

			$totalStudentFeeAmount+= $rows['fees'];
		}

        $total_student = count((array)$quranCountAry) + count((array)$regularCountAry);
		$actualtotalamount = $total_student * $orignal_payment;	

		echo json_encode(array('code'=>"1", 'msg'=>$combinArray, 'actualtotalamount'=>$actualtotalamount, 'totalamount'=>$totalStudentFeeAmount));
		exit;
	}

  }

  	echo json_encode(array('code' => "0", 'msg'=>'Students not found.'));
	exit;

 }


?>