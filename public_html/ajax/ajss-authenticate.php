<?php
include_once "../includes/config.php";
//LOGIN
if ($_POST['action'] == 'login') {
	/* if (isset($_POST['g-recaptcha-response'])) {
		$data = array(
            'secret' => RECAPTCHA_SECRETKEY,
            'response' => $_POST['g-recaptcha-response']
        );
		$verify = curl_init();
		curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($verify, CURLOPT_POST, true);
		curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($verify);
		$responseData = json_decode($output);
		if(isset($responseData) && $responseData->success){	 */
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);

	$user = $db->get_row("select * from ss_user where username = '" . $username . "' and password = '" . md5($password) . "' and is_locked=0 and is_deleted=0 and is_active=1");
	//$user = $db->get_row("select * from ss_user where username = 'nida.dada@gmail.com'");
	//$user = $db->get_row("select * from ss_user where username = 'tanvfatima'");
	if ($user->password_expired != 1) {
	
		if (!empty($user)) {
			$admintype = $db->get_row("select * from ss_usertype where id in (select user_type_id 
					from ss_usertypeusermap where user_id = '" . $user->id . "') order by preference asc limit 1");

			$type = $db->get_row("select * from ss_usertype where id in (select user_type_id 
					from ss_usertypeusermap where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
					and user_id = '" . $user->id . "') order by preference asc limit 1");

			$_SESSION['icksumm_uat_login_userid'] = $user->id;
			if($admintype->user_type_code == 'UT01'){
				$_SESSION['icksumm_uat_login_usertype'] = $admintype->user_type;
				$_SESSION['icksumm_uat_login_usertypegroup'] = $admintype->user_type_group;
				$_SESSION['icksumm_uat_login_usertypesubgroup'] = $admintype->user_type_subgroup;
				$_SESSION['icksumm_uat_login_usertypecode'] = $admintype->user_type_code;
				$_SESSION['login_user_permissions'] = get_user_role_waise_permission($user->id, $admintype->id);
			}elseif(!empty($type->user_type)){
				$_SESSION['icksumm_uat_login_usertype'] = $type->user_type;
				$_SESSION['icksumm_uat_login_usertypegroup'] = $type->user_type_group;
				$_SESSION['icksumm_uat_login_usertypesubgroup'] = $type->user_type_subgroup;
				$_SESSION['icksumm_uat_login_usertypecode'] = $type->user_type_code;
				$_SESSION['login_user_permissions'] = get_user_role_waise_permission($user->id, $type->id);
			}
			$_SESSION['icksumm_uat_login_username'] = $user->username;
			$_SESSION['icksumm_uat_login_email'] = $user->email;
			
			$total_roles_alloted_to_user = $db->get_results("SELECT distinct user_type_id FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE m.user_id = '" . $user->id . "'");
			$_SESSION['icksumm_uat_login_total_roles_alloted'] = count((array)$total_roles_alloted_to_user);
			if ($type->user_type_code == 'UT01' || $admintype->user_type_code == 'UT01') {
				//ADMIN AREA
				$_SESSION['icksumm_uat_login_fullname'] = $user->username;
				$_SESSION['icksumm_uat_login_firstname'] = 'Admin';
			} elseif ($type->user_type_code == 'UT05') {
				//PARENTS AREA
				$childs = $db->get_results("select s.user_id from ss_family f
				INNER JOIN ss_student s ON f.id = s.family_id
				INNER JOIN ss_user u ON u.id = s.user_id
				INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
				where f.user_id = '" . $user->id . "'
				AND ssm.session_id='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
				AND u.is_deleted = 0 AND u.is_locked=0 AND u.is_active=1");

				if(count((array)$childs) == 0){
				unset($_SESSION['icksumm_uat_login_usertype']);
				unset($_SESSION['icksumm_uat_login_usertypegroup']);
				unset($_SESSION['icksumm_uat_login_usertypesubgroup']);
				unset($_SESSION['icksumm_uat_login_usertypecode']);
				unset($_SESSION['login_user_permissions']);
				unset($_SESSION['icksumm_uat_login_username']);
				unset($_SESSION['icksumm_uat_login_email']);
				unset($_SESSION['icksumm_uat_login_total_roles_alloted']);
				echo json_encode(array('code' => "0", 'msg' => 'Your account has been deactivated'));
				die;
				}else{
				$familyinfo = $db->get_row("select * from ss_family where user_id = '" . $user->id . "'");
				$_SESSION['icksumm_uat_login_fullname'] = trim($familyinfo->father_first_name . ' ' . $familyinfo->father_last_name);
				$_SESSION['icksumm_uat_login_firstname'] = $familyinfo->father_first_name;
				$_SESSION['icksumm_uat_login_familyid'] = $familyinfo->id;
				}

			} elseif ($type->user_type_code == 'UT02' || $type->user_type_code == 'UT04') {
				//SHEIKH AREA
				$userinfo = $db->get_row("select * from ss_staff where user_id = '" . $user->id . "'");
				$_SESSION['icksumm_uat_login_fullname'] = $userinfo->first_name . (trim($userinfo->middle_name) != '' ? ' ' . $userinfo->middle_name : '') . (trim($userinfo->last_name) != '' ? ' ' . $userinfo->last_name : '');
				$_SESSION['icksumm_uat_login_firstname'] = $userinfo->first_name;
			} elseif ($type->user_type_code == 'UT03') {
				//STUDENT AREA
				$userinfo = $db->get_row("select * from ss_student where user_id = '" . $user->id . "'");
				$_SESSION['icksumm_uat_login_fullname'] = $userinfo->first_name . (trim($userinfo->middle_name) != '' ? ' ' . $userinfo->middle_name : '') . (trim($userinfo->last_name) != '' ? ' ' . $userinfo->last_name : '');
				$_SESSION['icksumm_uat_login_firstname'] = $userinfo->first_name;
			}
			$db->query("insert into ss_loginhistory set user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', ip_address='" . getRealIpAddr() . "', login_datetime='" . date('Y-m-d H:i:s') . "'");
			//REMEMBER ME					
			if (!empty($_POST["remember"])) {
				setcookie("member_username", $username, time() + 60 * 60 * 24 * 30, '/');
				setcookie("member_password", $password, time() + 60 * 60 * 24 * 30, '/');
				setcookie("member_school", $school, time() + 60 * 60 * 24 * 30, '/');
				setcookie("member_typecode", $_SESSION['icksumm_uat_login_usertypecode'], time() + 60 * 60 * 24 * 30, '/');
			} else {
				if (isset($_COOKIE["member_username"])) {
					setcookie("member_username", "", time(), '/');
				}
				if (isset($_COOKIE["member_password"])) {
					setcookie("member_password", "", time(), '/');
				}
				if (isset($_COOKIE["member_typecode"])) {
					setcookie("member_typecode", "", time(), '/');
				}
			} 
			if (isset($_SESSION['icksumm_uat_redirected_url'])) {
                if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->id)) {
                    $target_url = SITEURL . $_SESSION['icksumm_uat_redirected_url'];
                }else{
					$target_url = SITEURL . 'check_data.php';
				}
			} else {
				if ($type->user_type_code == 'UT05') {
					$new_session_students = $db->get_results("select * from ss_family f  INNER JOIN ss_student s ON f.id = s.family_id  INNER JOIN ss_user u ON u.id = s.user_id where f.user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' AND u.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_locked=0 and u.is_deleted=0 and u.is_active=1  ");

					if (count((array)$new_session_students) > 0) {
						$target_url = SITEURL . 'parents/dashboard.php';
					} else {
						// $target_url = SITEURL.'new_sesson_term_and_condition.php';

						// //ADDED ON 01-OCT-2020 - SISTER SHEHLA ANJUM - TEMPORARY LOGIC / TRICK TO LOGIC HER AS TEACHER
						// if($_SESSION['icksumm_uat_login_userid'] == 640){
						// 	$type = $db->get_row("select * from ss_usertype where id in (select user_type_id from ss_usertypeusermap where user_id = '".$user->id."') order by preference desc limit 1");

						// 	$_SESSION['icksumm_uat_login_userid'] = $user->id; 	
						// 	$_SESSION['icksumm_uat_login_usertype'] = $type->user_type;
						// 	$_SESSION['icksumm_uat_login_usertypegroup'] = $type->user_type_group;
						// 	$_SESSION['icksumm_uat_login_usertypesubgroup'] = $type->user_type_subgroup;
						// 	$_SESSION['icksumm_uat_login_usertypecode'] = $type->user_type_code;

						$target_url = SITEURL . 'dashboard.php';
						//}
					}
				} else {
                    if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->id)) {
                        $target_url = SITEURL . 'dashboard.php';
                    }else{
						$target_url = SITEURL . 'check_data.php';
					}
				}
			}
			echo json_encode(array('code' => 1, 'msg' => 'Login successfully, we are redirecting you to dashboard', 'target_url' => $target_url));
		} else {
			echo json_encode(array('code' => "0", 'msg' => 'Invalid username or password'));
		}
	} else {
		$user_key = md5('0A0' . $user->id . '0Z0');
		$target_url = SITEURL . 'onlineclass-waiver.php?key=' . $user_key;
		echo json_encode(array('code' => "2", 'msg' => 'Your password has expired. You will be redirected to password reset page soon.', 'target_url' => $target_url));
	}
	/* }
		else{
			echo json_encode(array('code' => "0",'msg' => 'Captcha validation failed', 'errpos' => 1));
		}
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Captcha validation required', 'errpos' => 2));
	} */
}
//FORGOT PASSWORD
elseif ($_POST['action'] == 'password_recovery') {
	/*	if (isset($_POST['g-recaptcha-response'])) {
 	$data = array(
			'secret' => RECAPTCHA_SECRETKEY,
			'response' => $_POST['g-recaptcha-response']
		);
		$verify = curl_init();
		curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($verify, CURLOPT_POST, true);
		curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($verify);
		$responseData = json_decode($output);
		if (isset($responseData) && $responseData->success) { */
			$username_email = $_POST['username_email'];
			$user = $db->get_row("select * from ss_user where username = '" . $db->escape($username_email) . "' and is_locked=0 and is_deleted=0 and is_active=1");

			if (!empty($user)) {
				$regard="Team";
				$user_name="User";

				$alloted_user_types_str = $db->get_var("select GROUP_CONCAT(user_type_code) from ss_usertype ut inner join ss_usertypeusermap utm on ut.id = utm.user_type_id where utm.user_id = '" . $user->id . "'");
				$alloted_user_types = explode(',', $alloted_user_types_str);

				if (in_array('UT01', $alloted_user_types) || in_array('UT02', $alloted_user_types) || in_array('UT04', $alloted_user_types) || in_array('UT05', $alloted_user_types)) {
					//$res = send_my_mail($user->email, CENTER_SHORTNAME.' - Password Recovery', $emailbody_pasword);
					$mailsendto = $user->email;
					$staff_info = $db->get_row("SELECT * FROM ss_staff t WHERE t.user_id = " . $user->id);

					$user_name=$staff_info->first_name.' '.$staff_info->last_name;
					$retMsg = 'Please check your email for password recovery link';
					$regard="Staff";
				} else {
					$family_info = $db->get_row("SELECT f.primary_email,f.father_first_name,f.father_last_name FROM ss_student s INNER JOIN ss_family f ON s.family_id = f.id WHERE s.id = " . $user->id);
					$mailsendto = $family_info->primary_email;
					//	$res = send_my_mail($parents_email, CENTER_SHORTNAME.' - Password Recovery', $emailbody_pasword);
					$user_name=$family_info->father_first_name.' '.$family_info->father_last_name;
					$retMsg = 'Please check your email of your parents for password recovery link';
				}




				$password_rec_link = SITEURL . "new_password.php?id=" . md5('iCjC' . $user->id . '1cjc');
				$emailbody_pasword .= "<p>Dear ".$user_name." Assalamualaikum,</p><br><p>Thank you for requesting a password reset. Please click the link below to generate new password.</p><p><a href=" . $password_rec_link . ">" . $password_rec_link . "</a></p>";
				$emailbody_pasword .= "<br><p><strong>Login URL:</strong> " . SITEURL . "login.php</p>";
				$emailbody_pasword .= "<p><strong>Email/Username:</strong> " . $username_email . "</p>";
				$emailbody_pasword .= "<br><p>" . CENTER_SHORTNAME . " " . SCHOOL_NAME . " ".$regard ."</p>";



				$emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_pasword));
				$email_subject = CENTER_SHORTNAME .' '.SCHOOL_NAME. ' - Password Recovery';
				$mail_service_array = array(
					'subject' => $email_subject,
					'message' => $emailbody,
					'request_from' => MAIL_SERVICE_KEY,
					'attachment_file_name' => [],
					'attachment_file' => [],
					'to_email' => [$mailsendto],
					'cc_email' => [],
					'bcc_email' => []
				);
				mailservice($mail_service_array);
				echo json_encode(array('code' => "1", 'msg' => $retMsg));
			} else {
				echo json_encode(array('code' => "0", 'msg' => 'No record found for provided information'));
			}
	/* 	} else {
			echo json_encode(array('code' => "0", 'msg' => 'Captcha validation failed'));
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Captcha validation required'));
	} */
}
//RESET PASSWORD ON PASSWORD EXPIRATION
elseif ($_POST['action'] == 'reset_password') {
	if (isset($_POST['g-recaptcha-response'])) {
		$data = array(
			'secret' => RECAPTCHA_SECRETKEY,
			'response' => $_POST['g-recaptcha-response']
		);
		$verify = curl_init();
		curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($verify, CURLOPT_POST, true);
		curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($verify);
		$responseData = json_decode($output);
		if (isset($responseData) && $responseData->success) {
			$encrypted_key = $_POST['key'];
			$new_password = trim($_POST['password']);
			$user = $db->get_row("select * from ss_user where md5(concat('0A0',id,'0Z0')) = '" . $encrypted_key . "' and password_expired = 1");
			if (!empty($user)) {
				//PARENTS EMAIL 					
				$emailbody .= "<p>Assalamualaikum Dear Parent of " . CENTER_SHORTNAME . " " . SCHOOL_NAME . ",</p>";
				$emailbody .= "<p>Thanks for completing the " . CENTER_SHORTNAME . " Online " . SCHOOL_NAME . " Registration.</p>";
				$emailbody .= "<p>Please find attached copy of the waiver with this email.</p>";
				$emailbody .= "<br><p><strong>FAMILY DETAILS</strong></p>";
				$family = $db->get_row("select * from ss_family where user_id = " . $user->id);
				$emailbody .= "<p><strong>Father Name:</strong>&nbsp;&nbsp;" . $family->father_first_name . ' ' . $family->father_last_name . "</p>";
				$emailbody .= "<p><strong>Mother Name:</strong>&nbsp;&nbsp;" . $family->mother_first_name . ' ' . $family->mother_last_name . "</p>";
				$emailbody .= "<p><strong>Primary Email:</strong>&nbsp;&nbsp;" . $family->primary_email . "</p>";
				$emailbody .= "<p><strong>Secondary Email:</strong>&nbsp;&nbsp;" . $family->secondary_email . "</p>";
				$stu_count = 0;
				$students = $db->get_results("select * from ss_student where family_id = " . $family->id);
				foreach ($students as $stu) {
					$stu_count++;
					$group = $db->get_var("SELECT group_name FROM ss_groups g INNER JOIN ss_studentgroupmap m ON g.id = m.group_id 
					WHERE m.latest = 1 AND m.student_user_id = " . $stu->user_id);
					if (!empty($group)) {
						$group = " - Group " . $group;
					}
					$emailbody .= "<p><strong>Student " . $stu_count . " Name:</strong>&nbsp;&nbsp;" . $stu->first_name . " " . $stu->last_name . $group . "</p>";
				}
				$emailbody .= "<br><p>Best regards,</p>";
				$emailbody .= "<p>" . CENTER_SHORTNAME . " " . SCHOOL_NAME . " Team</p>";
				$emailbody .= "<br><p>For any questions please reach out to " . SCHOOL_GEN_EMAIL . "</p>";
				$doc_path = $_SERVER["DOCUMENT_ROOT"] . '/sunday/documents/sunday_school_waiver.pdf';
				$email_response_par = send_my_mail_with_attachment($user->email, 'Online ' . SCHOOL_NAME . ' Waiver', $emailbody, $doc_path);
				$email_response_adm = send_my_mail_with_attachment(SCHOOL_GEN_EMAIL, 'Online ' . SCHOOL_NAME . ' Waiver', $emailbody, $doc_path);
				if ($email_response_par && $email_response_adm) {
					$updatePassword = $db->query("update ss_user set password_expired = 0, password = '" . md5($_POST['password']) . "' where id = '" . $user->id . "'");
					if ($updatePassword) {
						echo json_encode(array('code' => "1", 'msg' => 'Password Updated', 'target_url' => SITEURL));
						exit;
					} else {
						echo json_encode(array('code' => "0", 'msg' => 'Password change process failed'));
						exit;
					}
				} else {
					echo json_encode(array('code' => "0", 'msg' => 'Your password can\'t be updated. Please try later'));
					exit;
				}
			} else {
				echo json_encode(array('code' => "0", 'msg' => 'No record found for provided information'));
			}
		} else {
			echo json_encode(array('code' => "0", 'msg' => 'Captcha validation failed'));
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Captcha validation required'));
	}
}
//SET NEW PASSWORD 
elseif ($_POST['action'] == 'new_password') {
	$password = $_POST['password'];
	$md5_key = $_POST['key'];
	$user = $db->get_row("select * from ss_user where md5(concat('iCjC',id,'1cjc')) = '" . $md5_key . "'");
	if (!empty($user)) {
		$updatePassword = $db->query("update ss_user set password = '" . md5($password) . "' where id = '" . $user->id . "'");
		if (isset($updatePassword)) {
			echo json_encode(array('code' => "1", 'msg' => 'Password changed successfully. Redirecting to login page.'));
			exit;
		} else {
			echo json_encode(array('code' => "2", 'msg' => 'Password change process failed'));
			exit;
		}
	} else {
		echo json_encode(array('code' => "3", 'msg' => 'Password change process failed'));
		exit;
	}
}
//LOGIN HISTORY
elseif ($_GET['action'] == 'login_history') {
	//if($_SESSION['login_usertypecode'] == 'UT01'){
	$finalAry = array();
	$login = $db->get_results("SELECT l.id, s.user_id,CONCAT(s.first_name,' ',s.last_name) AS staff_name,login_datetime,logout_datetime FROM ss_loginhistory l INNER JOIN ss_staff s ON l.user_id = s.user_id 
	UNION 
	SELECT g.id, f.user_id,CONCAT(father_first_name,' ',father_last_name) AS full_name,login_datetime,logout_datetime FROM ss_loginhistory g INNER JOIN ss_family f ON g.user_id = f.user_id ORDER BY login_datetime DESC", ARRAY_A);
	for ($i = 0; $i < count((array)$login); $i++) {
		$user_info = $db->get_row("SELECT username, user_type, user_type_code FROM ss_user u INNER JOIN ss_usertype t ON u.user_type_id = t.id where u.id = '" . $login[$i]['user_id'] . "'");
		if ($user_info->user_type_code == "UT05") {
			$login[$i]['full_name'] = $login[$i]['full_name'] . ' ( ' . $user_info->username . ' )';
		}
		$login[$i]['user_type'] = $user_info->user_type;
		$login[$i]['login_datetime'] = my_date_changer($login[$i]['login_datetime'],'t');
		if (trim($login[$i]['logout_datetime']) != '') {
			$login[$i]['logout_datetime'] = my_date_changer($login[$i]['logout_datetime'],'t');
		}
	}
	$finalAry['data'] = $login;
	echo json_encode($finalAry);
	exit;
	//}
}

//SWITCH ACCOUNT
elseif ($_POST['action'] == 'switch_account') {
	$user_type_id = $_POST['user_type'];
	$check = $db->get_results("select * from ss_usertypeusermap where user_type_id = '" . $user_type_id . "' and user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
	
	if (count((array)$check)) {
		$type = $db->get_row("select * from ss_usertype where id = '" . $user_type_id . "'");
		$user = $db->get_row("select * from ss_user where id = '" . $_SESSION['icksumm_uat_login_userid'] . "' and is_locked=0 and is_deleted=0 and is_active=1");
		
		$_SESSION['icksumm_uat_login_usertype'] = $type->user_type;
		$_SESSION['icksumm_uat_login_usertypegroup'] = $type->user_type_group;
		$_SESSION['icksumm_uat_login_usertypesubgroup'] = $type->user_type_subgroup;
		$_SESSION['icksumm_uat_login_usertypecode'] = $type->user_type_code;
		if ($type->user_type_code == 'UT02' || $type->user_type_code == 'UT04') {
			//SHEIKH AREA

			$userinfo = $db->get_row("select * from ss_staff where user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
			$_SESSION['icksumm_uat_login_fullname'] = $userinfo->first_name . (trim($userinfo->middle_name) != '' ? ' ' . $userinfo->middle_name : '') . (trim($userinfo->last_name) != '' ? ' ' . $userinfo->last_name : '');
			$_SESSION['icksumm_uat_login_firstname'] = $userinfo->first_name;
		
		} elseif ($type->user_type_code == 'UT03') {
			//STUDENT AREA
			$userinfo = $db->get_row("select * from ss_student where user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
			$_SESSION['icksumm_uat_login_fullname'] = $userinfo->first_name . (trim($userinfo->middle_name) != '' ? ' ' . $userinfo->middle_name : '') . (trim($userinfo->last_name) != '' ? ' ' . $userinfo->last_name : '');
			$_SESSION['icksumm_uat_login_firstname'] = $userinfo->first_name;
		} elseif ($type->user_type_code == 'UT05') {
			//PARENTS AREA
			$familyinfo = $db->get_row("select * from ss_family where user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
			$_SESSION['icksumm_uat_login_fullname'] = $familyinfo->father_first_name . ' ' . $familyinfo->father_last_name;
			$_SESSION['icksumm_uat_login_firstname'] = $familyinfo->father_first_name;
			$_SESSION['icksumm_uat_login_familyid'] = $familyinfo->id; 
		}elseif($type->user_type_code == 'UT01'){
			//ADMIN AREA
			$_SESSION['icksumm_uat_login_fullname'] = $user->username;
			$_SESSION['icksumm_uat_login_firstname'] = 'Admin';
		}
		$_SESSION['icksumm_uat_login_username'] = $user->username;
		$_SESSION['icksumm_uat_login_email'] = $user->email;
		$_SESSION['login_user_permissions'] = get_user_role_waise_permission($user->id, $user_type_id);
		// echo "<pre>";
		// print_r($_SESSION);
		// die;
		if ($type->user_type_code == 'UT01' || $type->user_type_code == 'UT02' || $type->user_type_code == 'UT04') {
			$url = SITEURL . 'dashboard.php';
		} elseif ($type->user_type_code == 'UT05') {
			$url = SITEURL . 'parents/dashboard.php';
		}
	
		// $user_role_id = $db->get_var("select urm.role_id from ss_user_role_map urm INNER JOIN ss_usertype ut ON urm.role_id = ut.role_id where urm.user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND urm.status=1 AND ut.id = '".$user_type_id."'");
	
		// $user_permissions = $db->get_results("select permission from ss_role_wise_permissions INNER JOIN ss_permissions  ON ss_role_wise_permissions.permission_id = ss_permissions.id where ss_role_wise_permissions.role_id = '".$user_role_id."'");

		// $user_extra_permissions = $db->get_results("select ss_permissions.permission from ss_user_extra_permissions INNER JOIN ss_permissions ON ss_user_extra_permissions.extra_permission_id = ss_permissions.id INNER JOIN ss_usertypeusermap um ON um.user_id = ss_user_extra_permissions.user_id where ss_user_extra_permissions.user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND um.user_type_id = '".$user_type_id."'");
	
	
		// $arrayExtraPermission = [];
		// foreach ($user_extra_permissions as $rows) {
		// 	$arrayExtraPermission[] = $rows->permission;
		// }
	
		// $arrayPermission = [];
		// foreach ($user_permissions as $row) {
		// 	$arrayPermission[] = $row->permission;
		// }
		
		// $finalPermissionsArray = array_unique (array_merge ($arrayPermission, $arrayExtraPermission));
		// $_SESSION['login_user_permissions'] = $finalPermissionsArray;
		echo json_encode(array('code' => 1, 'url' => $url));
	} else {
		echo json_encode(array('code' => 0));
	}
}
//LOGIN HISTORY


elseif ($_GET['action'] == 'login_history') {
	$finalAry = array();
	$login = $db->get_results("SELECT l.id, s.user_id,CONCAT(s.first_name,' ',s.last_name) AS staff_name,login_datetime,logout_datetime FROM ss_loginhistory l INNER JOIN ss_staff s ON l.user_id = s.user_id UNION SELECT g.id, f.user_id,CONCAT(father_first_name,' ',father_last_name) AS full_name,login_datetime,logout_datetime FROM ss_loginhistory g INNER JOIN ss_family f ON g.user_id = f.user_id ORDER BY login_datetime DESC", ARRAY_A);
	for ($i = 0; $i < count((array)$login); $i++) {
		$user_info = $db->get_row("SELECT username, user_type, user_type_code FROM ss_user u INNER JOIN ss_usertype t ON u.user_type_id = t.id where u.id = '" . $login[$i]['user_id'] . "'");
		if ($user_info->user_type_code == "UT05") {
			$login[$i]['full_name'] = $login[$i]['full_name'] . ' ( ' . $user_info->username . ' )';
		}
		$login[$i]['user_type'] = $user_info->user_type;
		$login[$i]['login_datetime'] = date('m/d/Y h:i:s a', strtotime($login[$i]['login_datetime']));
		if (trim($login[$i]['logout_datetime']) != '') {
			$login[$i]['logout_datetime'] = date('m/d/Y h:i:s a', strtotime($login[$i]['logout_datetime']));
		}
	}
	$finalAry['data'] = $login;
	echo json_encode($finalAry);
	exit;
}
