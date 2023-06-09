<?php 
	include_once "../includes/config.php";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){ 
	 
		$current_session = "2020-21";
		
		$father_first_name = $db->escape(trim($_POST['father_first_name']));
		$father_last_name = $db->escape(trim($_POST['father_last_name']));
		$father_area_code = $db->escape(trim($_POST['father_area_code']));
		$father_phone = $db->escape(trim($_POST['father_phone']));
		$mother_first_name = $db->escape(trim($_POST['mother_first_name']));
		$mother_last_name = $db->escape(trim($_POST['mother_last_name']));
		$mother_area_code = $db->escape(trim($_POST['mother_area_code']));
		$mother_phone = $db->escape(trim($_POST['mother_phone']));		

		//$primary_email = $db->escape(trim($_POST['primary_email']));
		//$secondary_email = $db->escape(trim($_POST['secondary_email']));
		$father_email = strtolower($db->escape(trim($_POST['father_email'])));
		$mother_email = strtolower($db->escape(trim($_POST['mother_email'])));
		$which_is_primary_email = $db->escape(trim($_POST['which_is_primary_email']));			

		if($which_is_primary_email == "father"){
			$primary_email = $father_email;
			$secondary_email = $mother_email;
		}else{
			$primary_email = $mother_email;
			$secondary_email = $father_email;
		} 

		$address_1 = $db->escape(trim($_POST['address_1']));
		$address_2 = $db->escape(trim($_POST['address_2']));
		$city = $db->escape(trim($_POST['city']));
		$state = $db->escape(trim($_POST['state']));
		$country_id = trim($_POST['country_id']);
		$post_code = $db->escape(trim($_POST['post_code']));
		$food_allergies = trim($_POST['food_allergies']);
		$payment_method = trim($_POST['payment_method']);
		$addition_notes = $db->escape(trim($_POST['addition_notes']));		
		$discount_type = implode('|',$_POST['discount_type']);
		//$terms_conditions = $db->escape($_POST['terms_conditions']);
		
		$childAry = $_POST['child'];			
		
		$terms_conditions = "
		<ol>
						<li>Islamic education will be provided to children aged 5-14. All students will be taught the basics of Islam including the five pillars, details of performing Salah, basic skills to read and understand Qur'an, key aspects of Islamic law and history, as well as classes on Islamic behavior and character.</li><br>
						<li>School starts every Sunday at 10:30 AM. Parents are expected to bring their children at least five minutes prior.</li><br>
						<li>A significant part of the student's educational experience will be derived from classroom relationships, activities, discussions and group participations. We hope to have an interactive teaching atmosphere and will incorporate a variety of teaching techniques into classes including audio-visual aids, games, competitions and quizzes.</li><br>
						<li>A dress code shall be observed by all students:
							<ul>
								<li>Boys should wear long pants. Shorts are not acceptable</li><br>
								<li>Girls should wear loose clothing with an Islamic headscarf</li><br>
								<li>Torn clothing is not acceptable</li><br>
								<li>Attire of any kind with inappropriate messages, slogans, or symbols is not
									acceptable</li><br>
								<li>Visible undergarments are not acceptable</li><br>
							</ul>
			<br>
							If you need your child to change the clothes for an after school activity, please send the clothes and a note with your child, and the school will help the child change the clothes.</li><br>
						<li>Parents are expected to pick up their children promptly at the end of the ".SCHOOL_NAME." at 1:20 PM. Neither babysitting nor children supervision is available after school hours. The school will not be responsible for kids after 1:30 PM. If you need to pick your child up earlier than the end of the school, please come to the office to sign the child out.</li><br>
						<li>The school is a non-profit entity and its expenses are funded through nominal
							tuition and donations from community members. The cost per semester is: <br>
							<br>
							
								<table style='width:90%;border:0' cellpadding='5px' cellspacing='0px' border='0'>
									<tr>
										<td style='border:solid 1px #999;border-bottom:0px;background-color: #f9f9f9;'><br><strong>Fees and Payments information</strong><br></td> 
									</tr>
									<tr>
										<td style='border:solid 1px #999;border-bottom:0px;'><br>(a) There is one time $25 registration and processing fee per family to be charged at time of submission of this form.<br></td>
									</tr>
									<tr>
										<td style='border:solid 1px #999;border-bottom:0px;background-color: #f9f9f9;'><br>(b) Monthly Fee per child is $80. Each additional sibling gets a $10 discount.<br></td>
									</tr>
									<tr>
										<td style='border:solid 1px #999;border-bottom:0px;'><br>(c) You can pay tuition monthly or a one time yearly fees at 5% discount.<br></td>
									</tr>
									<tr>
										<td style='border:solid 1px #999;border-bottom:0px;background-color: #f9f9f9;'><br>(d) Quran School families get 30% discount for the entire family. The discount does
											not apply to registration fee. Must signup for Auto payment for Quran School.<br></td>
									</tr>
									<tr>
										<td style='border:solid 1px #999;border-bottom:0px;'><br>(e) Payments are accepted via automatic payment (ACH/CC). No monthly checks, no
											cash please.<br></td>
									</tr>
									<tr>
										<td style='border:solid 1px #999;background-color: #f9f9f9;'><br>(f) Tuition is not refundable for absence of the child from the ".SCHOOL_NAME." for trips,
											illness, withdrawal, dismissal from the School, or any other reason.<br></td>
									</tr>
								</table>
							
						</li><br>
						<li>School fees are due at the time of registration and at the beginning of January.</li><br>
						<li>The school provides school supplies.</li><br>
						<li>In case of lost/misplaced textbooks, parents can either purchase them from school or buy their own books.</li><br>
						<li>Some classes will require homework and folder will be provided for this purpose. Parents are urged to set a specific time and place for carrying out homework, to assist students when appropriate, and to sign homework assignments when required.</li><br>
						<li>All students should obey the rules set by the Principal and ".CENTER_SHORTNAME." Education Committee. All students are required to behave in class such that teaching and learning is accomplished in the most efficient manner. Constant or prolonged disruptive behavior can result in:
							<ul>
								<li>Students being asked to leave the classroom.</li><br>
								<li>Parents being asked to pick up the student from the school.</li><br>
								<li>Greater than two incidences will result in a conference between the
									Principal and the student's parents.</li><br>
								<li>Greater than 3 incidents and the student may be expelled from the school.</li><br>
							</ul>
						</li><br>
						<li>Tardiness will not be tolerated, as it will impact the student performance. Tardy students must report to the office first. Continued lateness will require an explanation from parents. Absence of a student will require a parent's explanatory note. Excessive absences and tardiness will result in a teacher-parent meeting to resolve the issue. The school reserves the right to expel a student for excessive tardiness, absences or persistent bad behavior.</li><br>
						<li>The school expects that each student practice some common good behavior guidelines:
							<ul>
								<li>BE KIND: Students should show respect for themselves and others and respect the authority of teachers, the Principal and other staff members.</li><br>
								<li>BE SAFE: Keep hands, feet and objects to yourself. Students should conduct themselves in classrooms, recreation areas and playgrounds in a manner that does not physically harm others.</li><br>
								<li>BE PRODUCTIVE: Follow directions the first time. Students should demonstrate pride in self, home, school and being a Muslim.</li><br>
							</ul>
						</li><br>
						<li>It is the responsibility of the parents to read all the reports, emails and notices sent to them from the school. Parents should make every effort to attend school functions as these are related to, and form an important aspect of, their children's education.</li><br>
						<li>It is the parent's responsibility to review and assist in their children's homework and communicate any issues to the appropriate teacher in a proper and polite manner.</li><br>
						<li>Tests form an integral part of the educational curriculum. Parents are encouraged to set aside sufficient time for their children to adequately prepare for these tests.</li><br>
						<li>End of school testing will be used to assess student readiness for the next level.</li><br>
						<li>".CENTER_SHORTNAME." and its ".SCHOOL_NAME." are not responsible for any monetary or physical loss or for any injury of any nature to a student.</li><br>
						<li>Parents will be responsible for any damage caused to the ".CENTER_SHORTNAME." property by their child/children and shall be required to pay the cost of such damage.</li><br>
						<li>Toys, radios and electronic games are not allowed in classrooms. The school is  not responsible for damage or loss to student's personal property. Please mark your children's clothing, coats etc. Cell phones will be confiscated and returned at the end of school.</li><br>
						<li>Children that are sick (including lice infestation) should not be brought to the school. In case of illness during school hours, parents will be notified and asked to pick up their child. If acute emergency care is required, an ambulance will be summoned. ".CENTER_SHORTNAME." is not responsible for any costs arising out of emergency care for students.</li><br>
						<li>In the event of severe weather during school hours, parents will be notified and asked to pick up their children. All contact information on the registration form should be current.</li><br>
						<li>In case of inclement weather parents will be notified by 8 AM the day of via email, texts and ".CENTER_SHORTNAME." Facebook page. Fees will not be refunded for a cancellation of class due to severe weather.</li><br>
						<li> Any form of Physical or verbal aggression will not be tolerated:
							<ul>
								<li>Behavior injurious to the physical well-being of others such as inflicting or encouraging others to inflict bodily harm on another person.</li><br>
								<li>inflicting verbal (oral or written), comments of a sexual or racial nature that hurt an individual or group of individuals;</li><br>
								<li>threatening physical harm, bullying or harassing others; and</li><br>
								<li>Using any form of discrimination.</li><br>
							</ul>
						</li><br>
					</ol>";
		
		$db->query('BEGIN');	
		
		$sqlQuery = "insert into ss_sunday_school_reg set father_first_name = '".$father_first_name."', father_last_name = '".$father_last_name."',	father_area_code = '".$father_area_code."', father_phone = '".$father_phone."', mother_first_name = '".$mother_first_name."',mother_last_name = '".$mother_last_name."', mother_area_code = '".$mother_area_code."', mother_phone = '".$mother_phone."',father_email = '".$father_email."', mother_email = '".$mother_email."',primary_email = '".$primary_email."', secondary_email = '".$secondary_email."', address_1 = '".$address_1."', address_2 = '".$address_2."',city = '".$city."', state = '".$state."', country_id = '".$country_id."', post_code = '".$post_code."',food_allergies = '".trim($_POST['food_allergies'])."', addition_notes = '".$addition_notes."', payment_method = '".trim($_POST['payment_method'])."', discount_type = '".$discount_type."', terms_conditions = '".$db->escape($terms_conditions)."', is_paid = 0, session = '".$current_session."', ip_address = '".$_SERVER['REMOTE_ADDR']."', created_on ='".time()."'";
		
		$db->query($sqlQuery);
	
		$sunday_school_reg_id = $db->insert_id; 
					
		$sun_sch_child_ary = array();
		if($sunday_school_reg_id > 0){
			$process_status = true;
			
			foreach($_POST['child'] as $child){ 
				if(trim($child['first_name']) != '' && trim($child['last_name']) != '' && trim($child['gender']) != '' && trim($child['dob']) != '' && trim($child['school_grade']) != ''){
					$family = $db->get_results("select * from ss_family where primary_email = '".$primary_email."' or secondary_email = '".$primary_email."'".(trim($secondary_email)!=''?" or primary_email = '".$secondary_email."' or secondary_email = '".$secondary_email."'":""));
		
					$isregisopen = isRegisOpenInSundaySchool($child['school_grade']);	
					$added_to_waitlist = $isregisopen== 1?0:1;

					if(count((array)$family)){
						foreach($family as $fam){
							$student = $db->get_row("select * from ss_student where family_id = '".$fam->id."' and replace(concat(first_name,last_name),' ','') = '".str_replace(' ','',trim($child['first_name']).trim($child['last_name']))."'");
							
							if(!empty($student)){
								$qur_sch_stu_user_id = $student->user_id;	
								$sqlQuery = "insert into ss_sunday_sch_req_child set first_name = '".trim($child['first_name'])."',last_name = '".trim($child['last_name'])."', gender = '".trim($child['gender'])."',dob = '".date('Y-m-d',strtotime(trim($child['dob'])))."', school_grade = '".trim($child['school_grade'])."',sunday_school_reg_id = '".$sunday_school_reg_id."', added_to_waitlist = '".$added_to_waitlist."', qur_sch_stu_user_id = '".$qur_sch_stu_user_id."', created_on='".time()."'";														
							}else{
								$sqlQuery = "insert into ss_sunday_sch_req_child set first_name = '".trim($child['first_name'])."',last_name = '".trim($child['last_name'])."', gender = '".trim($child['gender'])."',dob = '".date('Y-m-d',strtotime(trim($child['dob'])))."', school_grade = '".trim($child['school_grade'])."',sunday_school_reg_id = '".$sunday_school_reg_id."', added_to_waitlist = '".$added_to_waitlist."', created_on='".time()."'";
							}
						}		
					}else{
						$sqlQuery = "insert into ss_sunday_sch_req_child set first_name = '".trim($child['first_name'])."',last_name = '".trim($child['last_name'])."', gender = '".trim($child['gender'])."',dob = '".date('Y-m-d',strtotime(trim($child['dob'])))."', school_grade = '".trim($child['school_grade'])."', added_to_waitlist = '".$added_to_waitlist."', sunday_school_reg_id = '".$sunday_school_reg_id."', created_on='".time()."'";						
					}			
					
					$sun_sch_child_ary[] = $db->query($sqlQuery);
				}
			}
			
			if(count((array)$sun_sch_child_ary)){
				foreach($sun_sch_child_ary as $admreq){
					if(!$admreq){
						$process_status = false;
					}
				}
			}else{
				$process_status = false;
			}
			
			if(trim($_POST['credit_card_exp_month']) != '' && trim($_POST['credit_card_exp_year']) != ''){
				$credit_card_exp = base64_encode($_POST['credit_card_exp_month'].'-'.$_POST['credit_card_exp_year']);
			}
			
			$sqlQuery = "insert into ss_sunday_sch_payment set sunday_sch_req_id='".$sunday_school_reg_id."', credit_card_type ='".base64_encode($_POST['card_type'])."', credit_card_no ='".base64_encode($_POST['credit_card_no'])."', credit_card_exp ='".$credit_card_exp."', credit_card_cvv ='".base64_encode($_POST['credit_card_cvv'])."', postal_code ='".$_POST['postal_code']."', bank_acc_no ='".base64_encode($_POST['bank_acc_no'])."', routing_no ='".base64_encode($_POST['routing_no'])."', check_image ='".$final_check_image."'";
			$process_status = $db->query($sqlQuery);
				
			if($process_status && $db->query('COMMIT') !== false){
				$emailbody = '<table style="border:0" cellpadding="5"><tbody>
				<tr>
					<td colspan="4"> Dear Parents Assalamu-alaikum<br>
					<br>
					Thank you for registration to '.CENTER_SHORTNAME." ".SCHOOL_NAME.' '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'].'. We appreciate your help and cooperation. '.SCHOOL_NAME.' administrator will get back if any other info is needed. <br>
					<br>
					Please feel free to contact us at <a href="mailto:'.SCHOOL_GEN_EMAIL.'" target="_blank">'.SCHOOL_GEN_EMAIL.'</a>. <br>
					<br></td>
				</tr>
				<tr>
					<td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
						<tbody>';
			
				$child_count = 0;
				foreach($_POST['child'] as $child){ 
					if(trim($child['first_name']) != '' && trim($child['last_name']) != '' && trim($child['gender']) != '' && trim($child['dob']) != '' && trim($child['school_grade']) != ''){		  
						$child_count++;

						if(isRegisOpenInSundaySchool($child['school_grade'])){
							$school_grade_for_email = $child['school_grade'];
						}else{
							$school_grade_for_email = $child['school_grade'].' - Waitlist';
						}
						

						$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Child '.$child_count.' Name</strong></td>
							<td style="border:solid 1px #999;width:30%">'.$child['first_name'].' '.$child['last_name'].'</td>
							<td style="border:solid 1px #999;width:20%"><strong>Child '.$child_count.' Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">'.($child['gender']=='m'?'Male':'Female').'</td>
						</tr>
						<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child '.$child_count.' DoB</strong></td>
							<td style="border:solid 1px #999">'.my_date_changer($child['dob']).'</td>
							<td style="border:solid 1px #999"><strong>Child '.$child_count.' 2017/2018 School Grade</strong></td>
							<td style="border:solid 1px #999">'.$school_grade_for_email.'</td>
						</tr>';
					}
				} 
				
				foreach($_POST['discount_type'] as $discount){						
					if($discount == 'aaafriends'){
						$discount_type_text .= 'Friends of aaa (discount TBD), ';
					}else{
						$discount_type_text .= ucwords(str_replace('_',' ',$discount)).', ';
					}
				} 
				
				$discount_type_text = rtrim($discount_type_text,', ');
				
				$emailbody .= '<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>Father\'s Name</strong></td>
								<td style="border:solid 1px #999">'.$father_first_name.' '.$father_last_name.'</td>
								<td style="border:solid 1px #999"><strong>Father\'s Phone</strong></td>
								<td style="border:solid 1px #999">'.$father_phone.'</td>
							</tr>
							<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>Mother\'s Name</strong></td>
								<td style="border:solid 1px #999">'.$mother_first_name.' '.$mother_last_name.'</td>
								<td style="border:solid 1px #999"><strong>Mother\'s Phone</strong></td>
								<td style="border:solid 1px #999">'.$mother_phone.'</td>
							</tr>
							<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>Father\'s Email</strong></td>
								<td style="border:solid 1px #999">'.$father_email.'</td>
								<td style="border:solid 1px #999"><strong>Mother\'s Email</strong></td>
								<td style="border:solid 1px #999">'.$mother_email.'</td>
							</tr>
							<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>Address 1</strong></td>
								<td style="border:solid 1px #999">'.$address_1.'</td>
								<td style="border:solid 1px #999"><strong>Address 2</strong></td>
								<td style="border:solid 1px #999">'.$address_2.'</td>
							</tr>
							<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>City</strong></td>
								<td style="border:solid 1px #999">'.$city.'</td>
								<td style="border:solid 1px #999"><strong>State</strong></td>
								<td style="border:solid 1px #999">'.$state.'</td>
							</tr>
							<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>Country</strong></td>
								<td style="border:solid 1px #999">USA</td>
								<td style="border:solid 1px #999"><strong>Zip Code</strong></td>
								<td style="border:solid 1px #999">'.$post_code.'</td>
							</tr>
							<tr style="border:solid 1px #999">
								<td style="border:solid 1px #999"><strong>Food Allergies?</strong></td>
								<td style="border:solid 1px #999">'.$food_allergies.'</td>
								<td style="border:solid 1px #999"><strong>Questions/Comments/Allergy Details</strong></td>
								<td style="border:solid 1px #999">'.$addition_notes.'</td>
							</tr>
							<tr style="border:solid 1px #999">								  
								<td style="border:solid 1px #999"><strong>Discount Type</strong></td>
								<td style="border:solid 1px #999">'.$discount_type_text.'</td>
								<td style="border:solid 1px #999"><strong>Payment Method</strong></td>
								<td style="border:solid 1px #999">'.($payment_method=='ach'?'ACH':'Credit Card').'</td>
							</tr>
								
							<tr style="border:solid 1px #999">								  
								<td colspan="4" style="border:solid 1px #999">
								<br>
								<strong>I accepted '.CENTER_SHORTNAME." ".SCHOOL_NAME.' Terms and Conditions</strong>
								<br><br>'.$terms_conditions.'</td>
							</tr>
							</tbody>
						</table>
						<br>
						<br> 
						Thank You<br>
						'.CENTER_SHORTNAME." ".SCHOOL_NAME.' Team</td>
					</tr>
					</tbody>
				</table>';
						
				if($payment_method == "credit_card"){
					//======PAYPAL DETAILS===============	  
					// Buyer information
					$name = $_POST['name_on_card'];
					$nameArr = explode(' ', $name); 
					$firstName = !empty($nameArr[0])?$nameArr[0]:'';
					$lastName = !empty($nameArr[1])?$nameArr[1]:'';
					$countryCode = 'US';
					
					// Card details
					$creditCardNumber = trim(str_replace(" ","",$_POST['credit_card_no']));
					$creditCardType = $_POST['card_type'];
					$expMonth = $_POST['credit_card_exp_month'];
					$expYear = $_POST['credit_card_exp_year'];
					$cvv = $_POST['credit_card_cvv'];
					
					$payableAmount = 25;	//$5 Registration Charge
					
					// Create an instance of PaypalPro class
					$paypal = new PaypalPro();
					
					// Payment details
					$paypalParams = array(
						'paymentAction' => 'Sale',
						'itemName' => SCHOOL_NAME." Registration",
						'itemNumber' => $sunday_school_reg_id,
						'amount' => $payableAmount,
						'currencyCode' => $currency,
						'creditCardType' => $creditCardType,
						'creditCardNumber' => $creditCardNumber,
						'expMonth' => $expMonth,
						'expYear' => $expYear,
						'cvv' => $cvv,
						'firstName' => $firstName,
						'lastName' => $lastName,
						'city' => $city,
						'zip'	=> $post_code,
						'countryCode' => $countryCode,
					);
					
					$paypal_response = $paypal->paypalCall($paypalParams);
					$paymentStatus = strtoupper($paypal_response["ACK"]);
						
					if($paymentStatus == "SUCCESS"){
						// Transaction info
						$transactionID = $paypal_response['TRANSACTIONID'];
						$paidAmount = $paypal_response['AMT'];	
										
						//MARK IS_PAID TO 1
						$db->query("Update ss_sunday_school_reg set is_paid = 1, paypal_response = '".$db->escape(json_encode($paypal_response))."' where id = '".$sunday_school_reg_id."'");
									
						//COMMENTED ONLY FOR TESTING - 29-JUL-2019
						send_my_mail(SCHOOL_GEN_EMAIL, SCHOOL_NAME.' registration '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'], $emailbody,SCHOOL_GEN_EMAIL);
								
						send_my_mail($primary_email, SCHOOL_NAME.' registration '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'], $emailbody,SCHOOL_GEN_EMAIL);
						send_my_mail('moh.urooj@gmail.com', SCHOOL_NAME.' registration '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'], $emailbody);
							
						echo json_encode(array('code'=>'1','msg'=>'Registration form submitted successfully','encoded_key'=>md5($sunday_school_reg_id)));
						exit;
					}else{   
						$db->query("Update ss_sunday_school_reg set is_paid = 0, paypal_response = '".$db->escape(json_encode($paypal_response))."' where id = '".$sunday_school_reg_id."'");
						//echo json_encode($paypal_response);
						$errorMsg = 'Registration process failed';
						if(isset($paypal_response["ACK"])){
							$errorMsg = $paypal_response["L_LONGMESSAGE0"];
						}
						
						echo json_encode(array('code' => '0', 'msg' => $errorMsg, '_errpos' => 2));
						exit;
					}
				}else{
					//COMMENTED ONLY FOR TESTING - 29-JUL-2019
					send_my_mail(SCHOOL_GEN_EMAIL, SCHOOL_NAME.' registration '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'], $emailbody,SCHOOL_GEN_EMAIL);
							
					send_my_mail($primary_email, SCHOOL_NAME.' registration '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'], $emailbody,SCHOOL_GEN_EMAIL);
					send_my_mail('moh.urooj@gmail.com', SCHOOL_NAME.' registration '.$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'], $emailbody);
						
					echo json_encode(array('code'=>'1','msg'=>'Registration form submitted successfully','encoded_key'=>md5($sunday_school_reg_id)));
				}
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('code' => '0', 'msg' => 'Registration process failed', '_errpos' => 2));
				exit;
			} 
		}else{
			$db->query('ROLLBACK');         
			echo json_encode(array('code' => '0', 'msg' => 'One or more mandatory fields are empty', '_errpos' => 3));
			exit;
		}
	}
?>