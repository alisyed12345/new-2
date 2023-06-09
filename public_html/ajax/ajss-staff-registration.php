<?php
include_once "../includes/config.php";


//==========================LIST ALL STAFF FOR ADMIN=====================
if ($_GET['action'] == 'list_all_staff_register') {
	$finalAry = array();
	$all_staffs = $db->get_results("SELECT distinct r.id, CONCAT(r.first_name,' ',COALESCE(r.middle_name,''),' ',COALESCE(r.last_name,'')) AS staff_name,  r.mobile, r.email, (CASE WHEN r.is_request=1 THEN 'Active' ELSE 'Pending' END) AS status, u.email AS staff_email  
		 FROM ss_staff_registration r LEFT JOIN ss_user u ON r.email = u.email
		 WHERE r.is_request = 0 AND r.is_processed = 0 AND r.session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'", ARRAY_A);
		for ($i=0; $i<count((array)$all_staffs); $i++) {
			$all_staffs[$i]['mobile'] = internal_phone_check($all_staffs[$i]['mobile']);
		}
	$finalAry['data'] = $all_staffs;
	echo json_encode($finalAry);  
	exit;
} elseif ($_POST['action'] == 'add_staff') {
	$db->query('BEGIN');
	$found_in_staff = false;

	$staff_pending_check_no = $db->get_row("select * from ss_staff_registration where mobile='" . trim($db->escape($_POST['mobile'])) . "'");
	$staff_check_no = $db->get_row("select * from ss_staff where mobile='" . trim($db->escape($_POST['mobile'])) . "'");
	$get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc, new_registration_session, registration_page_termsncond,reg_form_term_cond_attach_url from ss_client_settings where status = 1");
	if (!empty($get_email->new_registration_email_bcc)) {
		$emails_bcc = explode(",", $get_email->new_registration_email_bcc);
	}
	if (!empty($get_email->new_registration_email_cc)) {
		$emails_cc = explode(",", $get_email->new_registration_email_cc);
	}
	//COMMENTED ON 16-AUG-2018
	//$emailCheck = $db->get_row("select * from ss_user where email='".trim($_POST['email'])."'");

	//ADDED ON 16-AUG-2018
	// $emailCheck = $db->get_row("select * from ss_user where username='".trim($_POST['email'])."'");
	// $mobileCheck = $db->get_row("select * from ss_staff where mobile='".trim($_POST['mobile'])."'");


			// $emailCheck = $db->get_row("select * from ss_user usr inner join ss_staff stf on stf.user_id= usr.id where usr.username='" . trim($_POST['email']) . "' and  stf.is_deleted=1 and usr.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
			$emailCheck = $db->get_row("select stf.first_name,usr.*  from ss_user usr 
			left join ss_staff stf on stf.user_id= usr.id 
			where usr.email = '". trim($_POST['email'])."' 
			and usr.is_active=1 
			and usr.is_deleted =0 ");
			if(!empty($emailCheck)){
				if(empty($emailCheck->first_name)){
				$add = true;
				}else{
				$add = false;	
				}
			}else{
				$staffCheck = $db->get_row("select * from ss_staff_registration where email='" . trim($_POST['email']) . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
				if (empty($staffCheck)) {
					$add = true;
				} else {
					if ($staffCheck->is_processed == 0) {
						$add = false;
					} else {
						$add = true;	
					}

				}
			}
			// if (empty($emailCheck)) {
			// 	$add = true;
			// } else {
			// 	$add = false;
			// } 

	// if(empty($mobileCheck)){

	// $emailCheck = $db->get_row("select * from ss_user where username='".trim($_POST['email'])."' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

	$user_id = $emailCheck->id;

	//ROLES ALREADY ALLOTED TO USER
	$roles_already_alloted = $db->get_results("SELECT t.user_type, t.user_type_group, m.user_type_id  FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON m.user_type_id = t.id INNER JOIN ss_user u ON u.user_type_id = t.id WHERE m.user_id = '" . $user_id . "' AND u.is_active = 1 AND u.is_deleted = 0");

	foreach ($roles_already_alloted as $already_alloted) {
		if ($already_alloted->user_type_id == 2) {
			echo json_encode(array('code' => "0", 'msg' => 'Error: Role ' . $already_alloted->user_type . ' already alloted to this email id, Please try with another email or role', '_errpos' => '5'));
			exit;
		}
	}

	if(empty($staff_pending_check_no) && empty($staff_check_no)){
	if ($add) {


		if ($db->escape($_POST['dob_submit']) != '') {
			$dob_submit = date('Y-m-d', strtotime($db->escape($_POST['dob_submit'])));

			$sql_insert = "insert into ss_staff_registration set 
					first_name='" . trim($db->escape($_POST['first_name'])) . "', 
					middle_name='" . trim($db->escape($_POST['middle_name'])) . "',				
					last_name='" . trim($db->escape($_POST['last_name'])) . "',gender='" . trim($db->escape($_POST['gender'])) . "',
					email='" . trim($db->escape($_POST['email'])) . "',
					dob='" . $dob_submit . "',phone='" . trim($db->escape($_POST['phone'])) . "',
					mobile='" . trim($db->escape($_POST['mobile'])) . "',address_1='" . trim($db->escape($_POST['address_1'])) . "',
					address_2='" . trim($db->escape($_POST['address_2'])) . "',city='" . trim($db->escape($_POST['city'])) . "',
					state_id='" . trim($db->escape($_POST['state_id'])) . "', country_id='" . trim($db->escape($_POST['country_id'])) . "', post_code='" . trim($db->escape($_POST['post_code'])) . "',session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
					created_on='" . date('Y-m-d H:i:s') . "',
					updated_on='" . date('Y-m-d H:i:s') . "'";
		} else {
			$sql_insert = "insert into ss_staff_registration set 
					first_name='" . trim($db->escape($_POST['first_name'])) . "', 
					middle_name='" . trim($db->escape($_POST['middle_name'])) . "',				
					last_name='" . trim($db->escape($_POST['last_name'])) . "',gender='" . trim($db->escape($_POST['gender'])) . "',
					email='" . trim($db->escape($_POST['email'])) . "',
					phone='" . trim($db->escape($_POST['phone'])) . "',
					mobile='" . trim($db->escape($_POST['mobile'])) . "',address_1='" . trim($db->escape($_POST['address_1'])) . "',
					address_2='" . trim($db->escape($_POST['address_2'])) . "',city='" . trim($db->escape($_POST['city'])) . "',
					state_id='" . trim($db->escape($_POST['state_id'])) . "',country_id='" . trim($db->escape($_POST['country_id'])) . "', post_code='" . trim($db->escape($_POST['post_code'])) . "',session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
					created_on='" . date('Y-m-d H:i:s') . "',
					updated_on='" . date('Y-m-d H:i:s') . "'";
		}

		$is_staff_added =  $db->query($sql_insert);


		if ($is_staff_added) {

			if (!empty($_POST['state_id'])) {

				$state_name = $db->get_var("SELECT state FROM ss_state WHERE id='" . $_POST['state_id'] . "' AND is_active=1 ");
			} else {
				$state_name = "";
			}


			$emailbody = '<table style="border:0" cellpadding="5"><tbody>
						<tr>
						<td colspan="4">Dear Staff Assalamu-alaikum<br>
						<br>
						Thank you for registration to ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . ' . We appreciate your help and cooperation. ' . SCHOOL_NAME . ' Staff will reach out  if any other info is needed. <br>
						<br>
						Please feel free to contact us at <a href="mailto:' . SCHOOL_GEN_EMAIL . '" target="_blank">' . SCHOOL_GEN_EMAIL . '</a><br>
						<br></td>
						</tr>
						<tr>
						<td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
						<tbody>';


			$emailbody .= '<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong> Name</strong></td>
						<td style="border:solid 1px #999">' . $_POST['first_name'] . ' ' . $_POST['middle_name'] . ' ' . $_POST['last_name'] . ' </td>
						<tr style="border:solid 1px #999">

						<td style="border:solid 1px #999"><strong> DOB </strong></td>
						<td style="border:solid 1px #999">' . my_date_changer($_POST['dob_submit']) . ' </td>

						<td style="border:solid 1px #999"><strong> Gender</strong></td>
						<td style="border:solid 1px #999">' . ($_POST['gender'] == 'm' ? 'Male' : 'Female') . ' ' . $_POST['parent1_last_name'] . '</td>
						<td style="border:solid 1px #999"><strong> Email</strong></td>
						<td style="border:solid 1px #999">' . $_POST['email'] . '</td>
						</tr>
						<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong> Mobile</strong></td>
						<td style="border:solid 1px #999">' . internal_phone_check($_POST['mobile']) . ' ' . $_POST['parent2_last_name']  ;
						if(!empty($_POST['phone'])){
							$emailbody .= '</td>
								<td style="border:solid 1px #999"><strong> Phone</strong></td>
								<td style="border:solid 1px #999">' . internal_phone_check($_POST['phone']) . '</td>
								</tr>';
						}
			$emailbody .= '<tr style="border:solid 1px #999">
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
						<td style="border:solid 1px #999">'.get_country()->country.'</td>
						<td style="border:solid 1px #999"><strong>ZipCode</strong></td>
						<td style="border:solid 1px #999">' . $_POST['post_code'] . '</td>
						</tr>';



			$emailbody .= '
						</tbody>
						</table>';
			$emailbody .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';			
			$emailbody .='</td></tr>
						</tbody>
						</table>';


			$emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));

			//send_my_mail(trim($db->escape($_POST['email'])), 'ICK Saturday Academy - Staff New registration', $emailbody);
			
			if(get_country()->abbreviation == 'GB'){
				$attachment_file_name = "staff-reg-term-condition-uk.pdf";
				$attachment_url = SITEURL . "email_pdf/staff-reg-term-condition-uk.pdf";
			}else{
				$attachment_file_name = "staff-reg-term-condition.pdf";
				$attachment_url = SITEURL . "email_pdf/staff-reg-term-condition.pdf";
			}
			

			$email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - Staff New registration';
			$email = $_POST['email'];

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
				'attachment_file_name' => $attachment_file_name,
				'attachment_file' => $attachment_url,
				'to_email' => [$email],
				'cc_email' => [$cc_email],
				'bcc_email' => [$bcc_email]
			);

			mailservice($mail_service_array);

			if ($db->query('COMMIT') !== false) {

				echo json_encode(array('code' => "1", 'msg' => 'Staff successfully registered.'));
				exit;
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => 'Error: Registration failed', '_errpos' => '1');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			$return_resp = array('code' => "0", 'msg' => 'Error: Registration failed', '_errpos' => '2');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	} else {
		$db->query('ROLLBACK');
		if (!empty($staffCheck) && $staffCheck->is_processed == 0) {
			$return_resp = array('code' => "0", 'msg' => 'Error: Requests with this email already exist in the pending list', '_errpos' => '3');
		} else {
			$return_resp = array('code' => "0", 'msg' => 'Error: The email address has already been used', '_errpos' => '3');
		}

		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}

	}else{

		$db->query('ROLLBACK');
		$return_resp = array('code' => "0",'msg' => 'Mobile number already exist in database','_errpos'=>'3');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;

	}

}
//==========================STAFF VIEW ONLY INFO=====================
elseif ($_POST['action'] == 'view_staff_detail') {
	$id = $_POST['id'];

	$staff = $db->get_row("SELECT s.gender, s.phone,s.mobile,
	s.address_1,s.address_2,s.city,s.state_id,s.country_id,s.email, s.post_code,
	CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name,	
	(CASE s.dob WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(s.dob,'%m/%d/%Y') END) AS dob FROM ss_staff_registration s  where s.id='" . $id . "'");

	$user = $db->get_row("select * from ss_user where email = '" . $staff->email . "'");
	$staff_reg = $db->get_row("select * from ss_staff where user_id = '" . $user->id . "'");

	$state = $db->get_var("select state from ss_state where id='" . $staff->state_id . "'");
	$country = $db->get_var("select country from ss_country where id='" . $staff->country_id . "'");

	// if (!empty($staff_reg)) {
	// 	$retStr = '<a href="'.SITEURL.'staff/staff_register_edit?id='.$id.'" style="float:right;">Update Information</a>';
	// }else{
	// 	$retStr = '<a href="'.SITEURL.'staff/staff_add" style="float:right;">Add Information</a>';	
	// }

	$retStr .=	'<legend class="text-semibold">Personal Information</legend>
            <div class="row">
              <div class="col-md-4">
                  <label for="first_name">Staff Name:</label>' . $staff->staff_name . '
              </div>
              <div class="col-md-4">
                  <label for="dob">Date of Birth:</label>' . my_date_changer($staff->dob) . '
              </div>
              <div class="col-md-4">
                  <label>Gender:</label>' . ($staff->gender == 'm' ? 'Male' : 'Female') . '
              </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <label>Email:</label>' . $staff->email . '
              </div>
            </div>
			<br>
			<legend class="text-semibold">Contact Information</legend>
            <div class="row">
              <div class="col-md-4">
                  <label>Primary No:</label>' . internal_phone_check($staff->mobile) . '
              </div>';
	if (!empty($staff->phone)) {
		$retStr .= '<div class="col-md-4">
                  <label>Alternate No:</label>' . internal_phone_check($staff->phone) . '
              		</div>';
	}

	$retStr .= '</div>
            <div class="row">
              <div class="col-md-4">
                  <label>Address Line 1:</label>' . $staff->address_1 . '
              </div>
              <div class="col-md-4">
                  <label>Address Line 2:</label>' . $staff->address_2 . '
              </div>
              <div class="col-md-4">
                  <label>City:</label>' . $staff->city . '
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                  <label>State:</label>' . $state . '
              </div>
              <div class="col-md-4">
                  <label>Country:</label>' . $country . '
              </div>
              <div class="col-md-4">
                <label>ZipCode:</label>' . $staff->post_code . '
              </div>
            </div>';

	echo $retStr;
	exit;
}
//==========================ASSIGN NEW GROUP TO STUDENT===================
elseif ($_POST['action'] == 'assign_new_group_to_staff') {

	$get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");

	// Multiple Group 
	if ($get_general_info == 0) {
		$user_id = $_POST['user_id'];
		$classes = $_POST['class'];
		$totalClass_count = count((array)$classes);

		$check_staff_reg = $db->get_row("SELECT * FROM ss_staff_registration WHERE id='" . $user_id . "'");
		$check_users_email = $db->get_row("SELECT email FROM ss_user WHERE email = '" . $check_staff_reg->email . "'");
		$staff_users_email = $db->get_row("SELECT email FROM ss_staff WHERE email = '" . $check_staff_reg->email . "'");
		$digits = 8;
		$password = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $digits));
		if (empty($check_users_email->email) && empty($staff_users_email->email)) {
			$db->query("insert into ss_user set username='" . trim($db->escape($check_staff_reg->email)) . "', password='" . md5(trim($password)) . "', email='" . trim($db->escape($check_staff_reg->email)) . "', is_email_verified=0, is_locked=0, is_active=1, session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', user_type_id = 3, created_on='" . date('Y-m-d H:i:s') . "'");
			$lastid = $db->insert_id;

			if ($lastid > 0) {
				$db->query("update ss_staff_registration set is_request = 1 where id='" . $user_id . "'");
				$dob_submit = date('Y-m-d', strtotime($check_staff_reg->dob));
				$db->query("insert into ss_staff set user_id='" . $lastid . "', 
					staff_number=0, 
					first_name='" . trim($db->escape($check_staff_reg->first_name)) . "', 
					middle_name='" . trim($db->escape($check_staff_reg->middle_name)) . "',				
					last_name='" . trim($db->escape($check_staff_reg->last_name)) . "',
					gender='" . trim($db->escape($check_staff_reg->gender)) . "',
					dob='" . $dob_submit . "',phone='" . trim($check_staff_reg->phone) . "',
					mobile='" . trim($check_staff_reg->mobile) . "',address_1='" . trim($check_staff_reg->address_1) . "',
					address_2='" . trim($check_staff_reg->address_2) . "',city='" . trim($check_staff_reg->city) . "',
					state_id='" . trim($check_staff_reg->state_id) . "',country_id='" . trim($check_staff_reg->country_id) . "',
					created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',created_on='" . date('Y-m-d H:i:s') . "'");
				$staff = $db->insert_id;
			}
		} else {

			$users =  $db->query("update ss_user set username='" . trim($db->escape($check_staff_reg->email)) . "', email='" . trim($db->escape($check_staff_reg->email)) . "', is_email_verified=0, is_locked=0, is_active=1, updated_on='" . date('Y-m-d H:i:s') . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', user_type_id = 3  WHERE id = '" . $check_users_email->id . "'");
			if ($users) {
				$db->query("update ss_staff_registration set is_request = 1 where id='" . $user_id . "'");
				$dob_submit = date('Y-m-d', strtotime($check_staff_reg->dob));
				$up_staff = $db->query("update ss_staff set user_id='" . $check_users_email->id . "', 
					staff_number=0, 
					first_name='" . trim($db->escape($check_staff_reg->first_name)) . "', 
					middle_name='" . trim($db->escape($check_staff_reg->middle_name)) . "',				
					last_name='" . trim($db->escape($check_staff_reg->last_name)) . "',
					gender='" . trim($db->escape($check_staff_reg->gender)) . "',
					dob='" . $dob_submit . "',phone='" . trim($check_staff_reg->phone) . "',
					mobile='" . trim($check_staff_reg->mobile) . "',address_1='" . trim($check_staff_reg->address_1) . "',
					address_2='" . trim($check_staff_reg->address_2) . "',city='" . trim($check_staff_reg->city) . "',
					state_id='" . trim($check_staff_reg->state_id) . "',country_id='" . trim($check_staff_reg->country_id) . "',
					created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',created_on='" . date('Y-m-d H:i:s') . "' where user_id='" . $check_users_email->id . "'");
			}
		}


		$queryCount = 0;
		foreach ($classes as $class_id) {

			$group_id = $_POST["group_id" . $class_id];

			$group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
			$groupMaxLimit = $group_details->max_limit;
			$group_name = $group_details->group_name;

			$groupCurStrength = $db->get_var("select COUNT(1) from ss_classtime where group_id = '" . $group_id . "' and class_id = '" . $class_id . "' and is_active = 1 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
			$class_time_id = $db->get_var("select id from ss_classtime where group_id = '" . $group_id . "' and class_id = '" . $class_id . "' and is_active = 1 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
			if ($groupCurStrength < $groupMaxLimit) {

				$sql_ret = $db->query("update ss_staffclasstimemap set staff_user_id='" . $lastid . "', active='0',
			updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' 
			where staff_user_id = '" . $lastid . "' AND classtime_id = '" . $class_time_id . "' AND active=1 ");


				$sql_ret = $db->query("insert into ss_staffclasstimemap set staff_user_id='" . $lastid . "', 
			classtime_id = '" . $class_time_id . "', active=1, created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', 
			created_on='" . date('Y-m-d H:i:s') . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

				$queryCount = $queryCount + 1;
			} else {
				$return_resp = array('code' => "0", 'msg' => 'Error: ' . $group_name . ' Group has reached maximum limit', '_errpos' => '10');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		}

		if ($totalClass_count == $queryCount) {
			echo json_encode(array('code' => "1", 'msg' => 'Group assigned successfully'));
			exit;
		} else {
			$return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	}

	//Single Group
	// elseif ($get_general_info == 1) {
	// 	$user_id = $_POST['user_id'];
	// 	$group_id = $_POST['group_id'];

	// 	$group_details = $db->get_row("select * from ss_groups where id = '".$group_id."'");
	// 	$groupMaxLimit = $group_details->max_limit;
	// 	$group_name = $group_details->group_name;

	// 	$groupCurStrength = $db->get_var("select count((array)1) from ss_studentgroupmap where group_id = '".$group_id."' and student_user_id = '".$user_id."' and latest = 1");

	// 	 if($groupCurStrength < $groupMaxLimit){

	//        $sql_ret = $db->query("update ss_studentgroupmap set group_id = '".$group_id."',
	// 		updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' 
	// 		where student_user_id = '".$user_id."' AND latest=1 ");

	//        if($sql_ret) {
	// 		   echo json_encode(array('code' => "1",'msg' => 'Group assigned successfully'));
	// 		   exit;
	// 		}else{
	// 	       $return_resp = array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>'1');
	// 	       CreateLog($_REQUEST, json_encode($return_resp));
	// 			echo json_encode($return_resp);
	// 	       exit;
	// 		}

	//      }else{
	// 		$return_resp = array('code' => "0",'msg' => 'Error: '.$group_name.' Group has reached maximum limit','_errpos' => '10');
	// 		CreateLog($_REQUEST, json_encode($return_resp));
	// 		echo json_encode($return_resp);
	// 	    exit;
	// 	}

	// }
} elseif ($_POST['action'] == 'remove_staff') {
	if (isset($_POST['id'])) {
		$get_staff = $db->get_row("select * from ss_staff_registration where id = '" . $_POST['id'] . "'");

		$email_temp = $db->get_var("select e.email_template, e.email_subject, e.email_cc, e.email_bcc from ss_email_templates e INNER JOIN ss_email_template_types t ON  e.email_template_type_id = t.id where t.type_name = 'Admission Request Delation Notification' and t.system_template = 1 and t.status = 1 and e.status = 1");
		$new_email_body = str_replace('{first_name}', $get_staff->first_name, $email_temp);
		$new_email_body = str_replace('{last_name}', $get_staff->last_name, $new_email_body);
		$emailbody = 'Dear ' . $get_staff->first_name . ' ' . $get_staff->last_name . ' Assalamu-alaikum<br><br>';
		$emailbody .= 'Your request has been Rejected by the administrator.<br><br>';
		$emailbody .= 'For any comments or question, please send email at ' . SCHOOL_GEN_EMAIL;
		$email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  Staff Registration Request Update';
		//send_my_mail(trim($db->escape($get_staff->email)), 'ICK Saturday School - Your Staff Request Deleted ', $emailbody);

		$rec = $db->query("DELETE FROM ss_staff_registration where id='" . $_POST['id'] . "'");

		if ($rec) {
			$mail_service_array = array(
				'subject' => $email_subject,
				'message' => $emailbody,
				'request_from' => MAIL_SERVICE_KEY,
				'attachment_file_name' => [],
				'attachment_file' => [],
				'to_email' => [$get_staff->email],
				'cc_email' => '',
				'bcc_email' => ''
			);

			mailservice($mail_service_array);
			echo json_encode(array('code' => "1", 'msg' => 'Staff deleted successfully'));
			exit;
		} else {
			$return_resp = array('code' => "0", 'msg' => 'Staff not deletion');
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
} elseif ($_POST['action'] == 'add_new_staff') {

	$db->query('BEGIN');

	$staff_reg = $db->get_row("select * from ss_staff_registration where id='" . trim($_POST['id']) . "'");

	if (!empty($staff_reg)) {

		$digits = 8;
		$password = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $digits));

		//EMAIL NOT EXISTS, MEANS NEW USER
		$db->query("insert into ss_user set username='" . trim($db->escape($staff_reg->email)) . "', password='" . md5(trim($password)) . "', email='" . trim($db->escape($staff_reg->email)) . "', is_email_verified=0, is_locked=0, is_active=1, created_on='" . date('Y-m-d H:i:s') . "'");

		$user_id = $db->insert_id;

		if ($user_id > 0) {
			if ($staff_reg->phone) {
				$phone = trim($db->escape($staff_reg->phone));
			} else {
				$phone = 'NULL';
			}
			if (!empty($staff_reg->country_id)) {
				$country = $staff_reg->country_id;
			} else {
				$country = '1';
			}
			if ($staff_reg->dob) {
				$dob = $staff_reg->dob;
				$sql_insert = "insert into ss_staff set 
					user_id='" . $user_id . "', 
					first_name='" . trim($db->escape($staff_reg->first_name)) . "', 
					middle_name='" . trim($db->escape($staff_reg->middle_name)) . "',				
					last_name='" . trim($db->escape($staff_reg->last_name)) . "',
					gender='" . trim($db->escape($staff_reg->gender)) . "',
					dob='" . $dob . "',
					phone= '" . $phone . "',
					mobile='" . trim($db->escape($staff_reg->mobile)) . "',
					address_1='" . trim($db->escape($staff_reg->address_1)) . "',
					address_2='" . trim($db->escape($staff_reg->address_2)) . "',
					city='" . trim($db->escape($staff_reg->city)) . "',
					state_id='" . trim($db->escape($staff_reg->state_id)) . "', 
					country_id='" . trim($db->escape($country)) . "', 
					post_code='" . trim($db->escape($staff_reg->post_code)) . "',
					created_on='" . date('Y-m-d H:i:s') . "',
					updated_on='" . date('Y-m-d H:i:s') . "'";
			} else {
				$sql_insert = "insert into ss_staff set 
					user_id='" . $user_id . "', 
					first_name='" . trim($db->escape($staff_reg->first_name)) . "', 
					middle_name='" . trim($db->escape($staff_reg->middle_name)) . "',				
					last_name='" . trim($db->escape($staff_reg->last_name)) . "',
					gender='" . trim($db->escape($staff_reg->gender)) . "',
					phone= '" . $phone . "',
					mobile='" . trim($db->escape($staff_reg->mobile)) . "',
					address_1='" . trim($db->escape($staff_reg->address_1)) . "',
					address_2='" . trim($db->escape($staff_reg->address_2)) . "',
					city='" . trim($db->escape($staff_reg->city)) . "',
					state_id='" . trim($db->escape($staff_reg->state_id)) . "', 
					country_id='" . trim($db->escape($country)) . "', 
					post_code='" . trim($db->escape($staff_reg->post_code)) . "',
					created_on='" . date('Y-m-d H:i:s') . "',
					updated_on='" . date('Y-m-d H:i:s') . "'";
			}

			$is_staff_added =  $db->query($sql_insert);


			if ($is_staff_added) {

				$usertypeusermap = $db->query("insert into ss_usertypeusermap set user_id='" . $user_id . "', user_type_id='2', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "' ");

				$ss_staff_session_map = $db->query("insert into ss_staff_session_map set staff_user_id='" . $user_id . "', session_id='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' , status='1', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "' ");

				$check_role_id = $db->get_var("SELECT role_id FROM ss_usertype WHERE id = '2'");
				$check_user = $db->query("SELECT * FROM ss_user_role_map where user_id = '" . $user_id . "' AND role_id = '" . $check_role_id . "' AND status = 1");

				if (!empty($check_user)) {
					$db->query("update ss_user_role_map set role_id='" . trim($db->escape($check_role_id)) . "' where user_id = '" . $user_id . "'");
					$user_role = 1;
				} else {
					$user_role = $db->query("insert into ss_user_role_map set user_id='" . trim($db->escape($user_id)) . "', role_id='" . trim($db->escape($check_role_id)) . "' ");
				}

				if ($usertypeusermap && $ss_staff_session_map  && $db->query('COMMIT') !== false) {
					$db->get_row("update ss_staff_registration set is_processed=1 where id='" . trim($_POST['id']) . "'");

					$emailbody_parents = "Dear Staff Assalamualaikum,<br><br>You can login in " . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " staff section using below information:<br>";

					$emailbody_parents .= "<br><br><strong>Login URL:</strong> " . SITEURL . "login.php";
					$emailbody_parents .= "<br><br><strong>Username/Email:</strong> " . trim($staff_reg->email);
					$emailbody_parents .= "<br><br><strong>Password:</strong> " . trim($password);

					$emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';

					$mailservice_request_from = MAIL_SERVICE_KEY;
					$mail_service_array = array(
						'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Login Details',
						'message' => $emailbody_parents,
						'request_from' => $mailservice_request_from,
						'attachment_file_name' => '',
						'attachment_file' => '',
						'to_email' => [trim($staff_reg->email)],
						'cc_email' => '',
						'bcc_email' => ''
					);

					mailservice($mail_service_array);
					//send_my_mail(trim($staff_reg->email), CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Login Details', $emailbody_parents);

					echo json_encode(array('code' => "1", 'msg' => 'Staff added successfully.'));
					exit;
				} else {
					$db->query('ROLLBACK');
					$return_resp = array('code' => "0", 'msg' => 'Error: Process failed.', '_errpos' => '2');
					CreateLog($_REQUEST, json_encode($return_resp));
					echo json_encode($return_resp);
					exit;
				}
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => 'Error: Process failed.', '_errpos' => '9');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			$return_resp = array('code' => "0", 'msg' => 'Error: Process failed.', '_errpos' => '5');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	} else {
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0", 'msg' => 'Error: Process failed.', '_errpos' => '3');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
} elseif ($_POST['action'] == 'update_staff') {


	$db->query('BEGIN');

	$staff_reg = $db->get_row("select * from ss_staff_registration where id='" . trim($_POST['id']) . "'");

	if (isset($staff_reg->dob) && !empty($staff_reg->dob)) {

		$staff_reg_dob = date('Y-m-d', strtotime($staff_reg->dob));
	} else {
		$staff_reg_dob = null;
	}


	if (!empty($staff_reg) && !empty($staff_reg->country_id)) {
		$country = $staff_reg->country_id;
	} else {
		$country = '1';
	}


	if (!empty($staff_reg)) {

		//$staff = $db->get_row("select * from ss_staff where email = '".trim($staff_reg->email)."'");

		$staff = $db->get_row("select s.id from ss_user u inner join ss_staff s on u.id = s.user_id where u.email = '" . trim($staff_reg->email) . "'");
		$emailCheck = $db->get_row("select * from ss_user usr inner join ss_staff stf on stf.user_id= usr.id where usr.username='" . trim($staff_reg->email) . "' and stf.is_deleted=0");

		if (!empty($staff) && !empty($emailCheck)) {
			$db->query("update ss_user set is_active = 1, is_deleted = 0 where username = '" . trim($staff_reg->email) . "'");

			$db->query("Update ss_staff_session_map SET status = 0, updated_on = NOW(), 
			updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' where staff_user_id = '" . $staff->user_id . "'");

			$db->query("INSERT INTO ss_staff_session_map SET staff_user_id = '" . $staff->user_id . "', session_id ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', status = '1', 
			created_on = NOW(), created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on = NOW(), 
			updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");

			$sql_insert = "update ss_staff set 
			first_name='" . trim($db->escape($staff_reg->first_name)) . "', 
			middle_name='" . trim($db->escape($staff_reg->middle_name)) . "',				
			last_name='" . trim($db->escape($staff_reg->last_name)) . "',
			gender='" . trim($db->escape($staff_reg->gender)) . "',
			dob='" . $staff_reg_dob . "',
			phone='" . trim($db->escape($staff_reg->phone)) . "',
			mobile='" . trim($db->escape($staff_reg->mobile)) . "',
			address_1='" . trim($db->escape($staff_reg->address_1)) . "',
			address_2='" . trim($db->escape($staff_reg->address_2)) . "',
			city='" . trim($db->escape($staff_reg->city)) . "',
			state_id='" . trim($db->escape($staff_reg->state_id)) . "', 
			country_id='" . trim($db->escape($country)) . "', 
			post_code='" . trim($db->escape($staff_reg->post_code)) . "',
			updated_on='" . date('Y-m-d H:i:s') . "' 
			where id='" . $staff->id . "'";

			$is_staff_updated =  $db->query($sql_insert);
			if ($is_staff_updated && $db->query('COMMIT') !== false) {

				$db->get_row("update ss_staff_registration set is_processed=1 where id='" . trim($_POST['id']) . "'");

				if (!empty($staff_reg->state_id)) {
					$state_name = $db->get_var("SELECT state FROM ss_state WHERE id='" . $staff_reg->state_id . "' AND is_active=1 ");
				} else {
					$state_name = "";
				}

				$emailbody = '<table style="border:0" cellpadding="5"><tbody>
				<tr>
				<td colspan="4">Dear Staff Assalamu-alaikum<br>
				<br>
				Thank you for registration to ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . ' . We appreciate your help and cooperation. ' . SCHOOL_NAME . ' administrator will get back if any other info is needed. <br>
				<br>
				Please feel free to contact us at <a href="mailto:' . SCHOOL_GEN_EMAIL . '" target="_blank">' . SCHOOL_GEN_EMAIL . '</a><br>
				<br></td>
				</tr>
				<tr>
				<td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
				<tbody>';

				$emailbody .= '<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong> Name</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->first_name . ' ' . $staff_reg->middle_name . ' ' . $staff_reg->last_name . ' </td>
				<tr style="border:solid 1px #999">
	
				<td style="border:solid 1px #999"><strong> DOB </strong></td>
				<td style="border:solid 1px #999">' . my_date_changer($staff_reg_dob) . ' </td>';
	
				if($staff_reg->gender = 'm'){
					$gender = 'Male';
				}elseif($staff_reg->gender = 'f'){
					$gender = 'Female';
				}
				$emailbody .= '<td style="border:solid 1px #999"><strong> Gender</strong></td>
				<td style="border:solid 1px #999">' . $gender . ' </td>';

				$emailbody .= '<td style="border:solid 1px #999"><strong> Email</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->email . '</td>
				</tr>
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong> Mobile</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->mobile . ' </td>';

				if(!empty($staff_reg->phone)){
					$emailbody .='<td style="border:solid 1px #999"><strong> Phone</strong></td>
								<td style="border:solid 1px #999">' . $staff_reg->phone . '</td>
								</tr>';
				}

				$emailbody .='<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>Address 1</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->address_1 . '</td>
				<td style="border:solid 1px #999"><strong>Address 2</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->address_2 . '</td>
				</tr>
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>City</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->city . '</td>
				<td style="border:solid 1px #999"><strong>State</strong></td>
				<td style="border:solid 1px #999">' . $state_name . '</td>
				</tr>
				<tr style="border:solid 1px #999">
				<td style="border:solid 1px #999"><strong>Country</strong></td>
				<td style="border:solid 1px #999">'.get_country()->country.'</td>
				<td style="border:solid 1px #999"><strong>ZipCode</strong></td>
				<td style="border:solid 1px #999">' . $staff_reg->post_code . '</td>
				</tr>';

				$emailbody .= '
				</tbody>
				</table>';
				$emailbody .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
				$emailbody .='</td>
				</tr>
				</tbody>
				</table>';

				$emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));

				$emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';

				$mailservice_request_from = MAIL_SERVICE_KEY;
				$mail_service_array = array(
					'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - Updated Info',
					'message' => $emailbody,
					'request_from' => $mailservice_request_from,
					'attachment_file_name' => '',
					'attachment_file' => '',
					'to_email' => [trim($staff_reg->email)],
					'cc_email' => '',
					'bcc_email' => ''
				);
				mailservice($mail_service_array);
				//send_my_mail(trim($db->escape($staff_reg->email)), 'ICK Saturday Academy - Updated Info', $emailbody);
				echo json_encode(array('code' => "1", 'msg' => 'Staff updated successfully.'));
				exit;
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => 'Staff updation failed', '_errpos' => '2');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		} else {

			$email_check = $db->get_row("select u.id from ss_user u 
				INNER join ss_usertypeusermap t on t.user_id = u.id 
				INNER join ss_usertype ty on ty.id = t.user_type_id 
				where u.email = '" . trim($staff_reg->email) . "' and ty.user_type_code <> 'UT03'");

			if (!empty($email_check)) {
				$user_id = $email_check->id;
				$password = "Use Active Account Password";
			} else {

				$digits = 8;
				$password = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $digits));
				//EMAIL NOT EXISTS, MEANS NEW USER
				$db->query("insert into ss_user set username='" . trim($db->escape($staff_reg->email)) . "', password='" . md5(trim($password)) . "', email='" . trim($db->escape($staff_reg->email)) . "', is_email_verified=0, is_locked=0, is_active=1, created_on='" . date('Y-m-d H:i:s') . "'");

				$user_id = $db->insert_id;
			}

			if ($user_id > 0) {
				$sql_insert = "insert into ss_staff set 
					user_id='" . $user_id . "', 
					first_name='" . trim($db->escape($staff_reg->first_name)) . "', 
					middle_name='" . trim($db->escape($staff_reg->middle_name)) . "',				
					last_name='" . trim($db->escape($staff_reg->last_name)) . "',
					gender='" . trim($db->escape($staff_reg->gender)) . "',
					dob='" . $staff_reg_dob . "',
					phone='" . trim($db->escape($staff_reg->phone)) . "',
					mobile='" . trim($db->escape($staff_reg->mobile)) . "',
					address_1='" . trim($db->escape($staff_reg->address_1)) . "',
					address_2='" . trim($db->escape($staff_reg->address_2)) . "',
					city='" . trim($db->escape($staff_reg->city)) . "',
					state_id='" . trim($db->escape($staff_reg->state_id)) . "', 
					country_id='" . trim($db->escape($country)) . "', 
					post_code='" . trim($db->escape($staff_reg->post_code)) . "',
					created_on='" . date('Y-m-d H:i:s') . "',
					updated_on='" . date('Y-m-d H:i:s') . "'";

				$is_staff_added =  $db->query($sql_insert);

				if ($is_staff_added) {

					$usertypeusermap = $db->query("insert into ss_usertypeusermap set user_id='" . $user_id . "', user_type_id='2', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' , created_on='" . date('Y-m-d H:i:s') . "' ");

					$ss_staff_session_map = $db->query("insert into ss_staff_session_map set staff_user_id='" . $user_id . "', session_id='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' , status='1', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "' ");

					$check_role_id = $db->get_var("SELECT role_id FROM ss_usertype WHERE id = '2'");
					$check_user = $db->query("SELECT * FROM ss_user_role_map where user_id = '" . $user_id . "' AND role_id = '" . $check_role_id . "' AND status = 1");

					if (!empty($check_user)) {
						$db->query("update ss_user_role_map set role_id='" . trim($db->escape($check_role_id)) . "' where user_id = '" . $user_id . "'");
						$user_role = 1;
					} else {
						$user_role = $db->query("insert into ss_user_role_map set user_id='" . trim($db->escape($user_id)) . "', role_id='" . trim($db->escape($check_role_id)) . "' ");
					}

					if ($user_role && $usertypeusermap && $ss_staff_session_map && $db->query('COMMIT') !== false) {
						$db->get_row("update ss_staff_registration set is_processed=1 where id='" . trim($_POST['id']) . "'");

						$emailbody_parents = "Dear Staff Assalamualaikum,<br><br>You can login in " . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " staff section using below information:<br>";
						$emailbody_parents .= "<br><br><strong>Login URL:</strong> " . SITEURL . "login.php";
						$emailbody_parents .= "<br><br><strong>Username/Email:</strong> " . trim($staff_reg->email);
						$emailbody_parents .= "<br><br><strong>Password:</strong> " . $password;

						$emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';

						$mailservice_request_from = MAIL_SERVICE_KEY;
						$mail_service_array = array(
							'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Login Details',
							'message' => $emailbody_parents,
							'request_from' => $mailservice_request_from,
							'attachment_file_name' => '',
							'attachment_file' => '',
							'to_email' => [trim($staff_reg->email)],
							'cc_email' => '',
							'bcc_email' => ''
						);
						mailservice($mail_service_array);
						//send_my_mail(trim($staff_reg->email), CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Login Details', $emailbody_parents);
						//send_my_mail(admin@demo.com, CENTER_SHORTNAME.' '.SCHOOL_NAME.' Login Details', $emailbody_parents);

						echo json_encode(array('code' => "1", 'msg' => 'Staff added successfully.'));
						exit;
					} else {
						$db->query('ROLLBACK');
						$return_resp = array('code' => "0", 'msg' => 'Error: Staff added process failed', '_errpos' => '22');
						CreateLog($_REQUEST, json_encode($return_resp));
						echo json_encode($return_resp);
						exit;
					}
				} else {
					$db->query('ROLLBACK');
					$return_resp = array('code' => "0", 'msg' => 'Error: Process failed.', '_errpos' => '9');
					CreateLog($_REQUEST, json_encode($return_resp));
					echo json_encode($return_resp);
					exit;
				}
			} else {
				$db->query('ROLLBACK');
				$return_resp = array('code' => "0", 'msg' => 'Error: Process failed.', '_errpos' => '5');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		}
	} else {
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0", 'msg' => 'Staff updation failed', '_errpos' => '5');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
