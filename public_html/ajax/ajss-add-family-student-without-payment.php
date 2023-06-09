<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}
//==========================STUDENT REGISTER=====================
if ($_POST['action'] == 'student_register') {

	$country = $db->get_var("select country from ss_country where id='" . $_POST['country_id'] . "'");

	if ($_POST['child2_first_name'] != '') {
		$child2_first_name = trim($_POST['child2_first_name']);
		$child2_last_name = trim($_POST['child2_last_name']);
		$child2_dob_dt = $_POST['child2_dob_submit'];
		if (trim($child2_dob_dt) != '') {
			$child2_dob = "'" . $child2_dob_dt . "'";
		} else {
			$child2_dob = "NULL";
		}
		$child2_gender = $_POST['child2_gender'];
		$child2_allergies = $_POST['child2_allergies'];
		$child2_grade = $_POST['child2_grade'];
	}
	if ($_POST['child3_first_name'] != '') {
		$child3_first_name = trim($_POST['child3_first_name']);
		$child3_last_name = trim($_POST['child3_last_name']);
		$child3_dob_dt = $_POST['child3_dob_submit'];
		if (trim($child3_dob_dt) != '') {
			$child3_dob = "'" . $child3_dob_dt . "'";
		} else {
			$child3_dob = "NULL";
		}
		$child3_gender = $_POST['child3_gender'];
		$child3_allergies = $_POST['child3_allergies'];
		$child3_grade = $_POST['child3_grade'];
	}
	if ($_POST['child4_first_name'] != '') {
		$child4_first_name = trim($_POST['child4_first_name']);
		$child4_last_name = trim($_POST['child4_last_name']);
		$child4_dob_dt = $_POST['child4_dob_submit'];
		if (trim($child4_dob_dt) != '') {
			$child4_dob = "'" . $child4_dob_dt . "'";
		} else {
			$child4_dob = "NULL";
		}
		$child4_gender = $_POST['child4_gender'];
		$child4_allergies = $_POST['child4_allergies'];
		$child4_grade = $_POST['child4_grade'];
	}
	$parent1_email = $db->escape(trim($_POST['credit_holder_email']));
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
	$studentRegister =  $db->query("insert into ss_sunday_school_reg set
		father_first_name='" . trim($db->escape($_POST['credit_holder_first_name'])) . "',
		father_last_name='" . trim($db->escape($_POST['credit_holder_last_name'])) . "',
		father_phone='" . trim($db->escape($_POST['credit_holder_phone'])) . "',
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
		city='" . trim($db->escape($_POST['city'])) . "',
		state='" . trim($db->escape($_POST['state'])) . "',
		country_id='" . trim($db->escape($_POST['country_id'])) . "',
		post_code='" . trim($db->escape($_POST['post_code'])) . "',
		addition_notes='" . trim($db->escape($_POST['addition_notes'])) . "',
		payment_method = 'c',
		session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
		internal_registration = 1,
		is_waiting = 0,
		registerd_by='Admin',
		created_on='" . date('Y-m-d H:i:s') . "',
		updated_on='" . date('Y-m-d H:i:s') . "'");
	$reg_id = $db->insert_id;
	if ($reg_id > 0) {
		if ($_POST['child1_first_name'] != '') {
			$data = $db->query("insert into ss_sunday_sch_req_child set
				sunday_school_reg_id='" . $reg_id . "',
				first_name='" . trim($_POST['child1_first_name']) . "',
				last_name='" . trim($_POST['child1_last_name']) . "',
				dob= '" . trim($_POST['child1_dob_submit']) . "',
				gender='" . trim($_POST['child1_gender']) . "',
				allergies='" . trim($_POST['child1_allergies']) . "',
				school_grade = '" . $_POST['child1_grade'] . "',
				created_on='" . date('Y-m-d H:i:s') . "',
				updated_on='" . date('Y-m-d H:i:s') . "'");
		}
		if ($_POST['child2_first_name'] != '') {
			$data = $db->query("insert into ss_sunday_sch_req_child set
				sunday_school_reg_id='" . $reg_id . "',
				first_name='" . $child2_first_name . "',
				last_name='" . $child2_last_name . "',
				dob=" . $child2_dob . ",
				gender='" . $child2_gender . "',
				school_grade = '" . $child2_grade . "',
				allergies='" . $child2_allergies . "',
				created_on='" . date('Y-m-d H:i:s') . "',
				updated_on='" . date('Y-m-d H:i:s') . "'");
		}
		if ($_POST['child3_first_name'] != '') {
			$data = $db->query("insert into ss_sunday_sch_req_child set
				sunday_school_reg_id='" . $reg_id . "',
				first_name='" . $child3_first_name . "',
				last_name='" . $child3_last_name . "',
				dob=" . $child3_dob . ",
				gender='" . $child3_gender . "',
				school_grade = '" . $child3_grade . "',
				allergies='" . $child3_allergies . "',
				created_on='" . date('Y-m-d H:i:s') . "',
				updated_on='" . date('Y-m-d H:i:s') . "'");
		}
		if ($_POST['child4_first_name'] != '') {
			$data = $db->query("insert into ss_sunday_sch_req_child set
				sunday_school_reg_id='" . $reg_id . "',
				first_name='" . $child4_first_name . "',
				last_name='" . $child4_last_name . "',
				dob=" . $child4_dob . ",
				gender='" . $child4_gender . "',
				school_grade = '" . $child4_grade . "',
				allergies='" . $child4_allergies . "',
				created_on='" . date('Y-m-d H:i:s') . "',
				updated_on='" . date('Y-m-d H:i:s') . "'");
		}
		if ($reg_id > 0) {
			if (!empty($_POST['state'])) {
				$state_name = $db->get_var("SELECT state FROM ss_state WHERE id='" . $_POST['state'] . "' AND is_active=1 ");
			} else {
				$state_name = "";
			}
			$next_year = date('Y') + 1;
			$final_year = date('Y') - $next_year;
			$emailbody = '<table style="border:0" cellpadding="5"><tbody>
						<tr>
						<td colspan="4"> Dear Parents Assalamu-alaikum<br>
						<br> 
						Thank you for registration to ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . '. We appreciate your help and cooperation. ' . SCHOOL_NAME . ' administrator will get back if any other info is needed. <br>
						<br>
						Please feel free to contact us at <a href="mailto:' . SCHOOL_GEN_EMAIL . '" target="_blank">' . SCHOOL_GEN_EMAIL . '</a><br>
						<br></td>
						</tr>
						<tr>
						<td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
						<tbody>';
			$emailbody .= '<tr style="border:solid 1px #999">
						<td style="border:solid 1px #999"><strong>1st Parent Name</strong></td>
						<td style="border:solid 1px #999">' . $_POST['credit_holder_first_name'] . ' ' . $_POST['credit_holder_last_name'] . '</td>
						<td style="border:solid 1px #999"><strong>1st Parent Phone</strong></td>
						<td style="border:solid 1px #999">' . $_POST['credit_holder_phone'] . '</td>
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


			$emailbody .= '</tr><tr style="border:solid 1px #999">
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
						<td style="border:solid 1px #999">' . $country . '</td>
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
							<td style="border:solid 1px #999">' . my_date_changer($_POST['child1_dob_submit']) . '</td>
							<td style="border:solid 1px #999"><strong>Child 1 School Grade</strong></td>
							<td style="border:solid 1px #999">' . $_POST['child1_grade'] . '</td>
							</tr>

							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 1 Allergies</strong></td>
							<td style="border:solid 1px #999">' . $_POST['child1_allergies'] . '</td>
							<td style="border:solid 1px #999;"></td>
							<td style="border:solid 1px #999;"></td>
							</tr>';
			}
			if (!empty($_POST['child2_first_name'])) {
				$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Child 2 Name</strong></td>
							<td style="border:solid 1px #999;width:30%">' . $child2_first_name . ' ' . $child2_last_name . '</td>
							<td style="border:solid 1px #999;width:20%"><strong>Child 2 Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">' . (trim($child2_gender) == "f" ? "Female" : "Male") . '</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 2 DoB</strong></td>
							<td style="border:solid 1px #999">' . my_date_changer($_POST['child2_dob_submit']) . '</td>
							<td style="border:solid 1px #999"><strong>Child 2 School Grade</strong></td>
							<td style="border:solid 1px #999">' . $child2_grade . '</td>
							</tr>

							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 2 Allergies</strong></td>
							<td style="border:solid 1px #999">' . $child2_allergies . '</td>
							<td style="border:solid 1px #999;"></td>
							<td style="border:solid 1px #999;"></td>
							</tr>';
			}
			if (!empty($_POST['child3_first_name'])) {
				$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Child 3 Name</strong></td>
							<td style="border:solid 1px #999;width:30%">' . $child3_first_name . ' ' . $child3_last_name . '</td>
							<td style="border:solid 1px #999;width:20%"><strong>Child 3 Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">' . (trim($child3_gender) == "f" ? "Female" : "Male") . '</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 3 DoB</strong></td>
							<td style="border:solid 1px #999">' . my_date_changer($_POST['child3_dob_submit']) . '</td>
							<td style="border:solid 1px #999"><strong>Child 3 School Grade</strong></td>
							<td style="border:solid 1px #999">' . $child3_grade . '</td>
							</tr>

							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 3 Allergies</strong></td>
							<td style="border:solid 1px #999">' . $child3_allergies . '</td>
							<td style="border:solid 1px #999;"></td>
							<td style="border:solid 1px #999;"></td>
							</tr>';
			}
			if (!empty($_POST['child4_first_name'])) {
				$emailbody .= '<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999;width:20%"><strong>Child 4 Name</strong></td>
							<td style="border:solid 1px #999;width:30%">' . $child4_first_name . ' ' . $child3_last_name . '</td>
							<td style="border:solid 1px #999;width:20%"><strong>Child 4 Gender</strong></td>
							<td style="border:solid 1px #999;width:30%">' . (trim($child4_gender) == "f" ? "Female" : "Male") . '</td>
							</tr>
							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 4 DoB</strong></td>
							<td style="border:solid 1px #999">' . my_date_changer($_POST['child4_dob_submit']) . '</td>
							<td style="border:solid 1px #999"><strong>Child 4 School Grade</strong></td>
							<td style="border:solid 1px #999">' . $child4_grade . '</td>
							</tr>

							<tr style="border:solid 1px #999">
							<td style="border:solid 1px #999"><strong>Child 4 Allergies</strong></td>
							<td style="border:solid 1px #999">' . $child4_allergies . '</td>
							<td style="border:solid 1px #999;"></td>
							<td style="border:solid 1px #999;"></td>
							</tr>';
			}

			// $emailbody .= '<tr style="border:solid 1px #999">
			// <td style="border:solid 1px #999"><strong>Payment Method</strong></td>
			// <td style="border:solid 1px #999">Credit Card</td>
			// <td style="border:solid 1px #999"><strong></strong></td>
			// <td style="border:solid 1px #999"></td>
			// </tr>';

			// $emailbody .= '<tr style="border:solid 1px #999">
			// <td style="border:solid 1px #999;"><strong>Last 4 digits of Credit Card</strong></td>
			// <td style="border:solid 1px #999;">'.substr($_POST['credit_card_no'],-4).'</td>
			// <td style="border:solid 1px #999;"></td>
			// <td style="border:solid 1px #999;"></td>
			// </tr>';

			$emailbody .= '
						</tbody>
						</table>
						<br>
						<br>
						' . BEST_REGARDS_TEXT . '<br>
						' . ORGANIZATION_NAME . ' Team</td>
						</tr>
						</tbody>
						</table>';
			$emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));
			$sec_email = "";
			if (trim($secondary_email) != '') {
				$sec_email = $secondary_email;
			}
			$mailservice_request_from = MAIL_SERVICE_KEY;
			$mail_service_array = array(
				'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - New registration',
				'message' => $emailbody,
				'request_from' => $mailservice_request_from,
				'attachment_file_name' => '',
				'attachment_file' => '',
				'to_email' => [$primary_email, $sec_email],
				'cc_email' => '',
				'bcc_email' => ''
			);

			mailservice($mail_service_array);
			echo json_encode(array('code' => "1", 'msg' => 'Family and student added successfully.'));
			$db->query('COMMIT');
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => "Process failed. Please try again.", '_errpos' => 4));
			exit;
		}
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0", 'msg' => "Process failed. Please try again.", '_errpos' => 6));
		exit;
	}
}
