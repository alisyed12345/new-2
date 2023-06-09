<?php
include_once "../includes/config.php";
get_country()->timezone;
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}
if (!empty(get_country()->currency)) {
	$currency = get_country()->currency;
} else {
	$currency = '';
}
//==========================ENROLLMENT REPORT=====================
if ($_GET['action'] == 'enroll_report') {
	if (in_array("su_report_enrollment", $_SESSION['login_user_permissions'])) {
		$finalAry = array();

		$sql = "SELECT u.created_on, s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student, 
		CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father,
		CONCAT(f.mother_first_name,' ',COALESCE(f.mother_last_name,'')) AS mother,
		f.primary_email, f.secondary_email,f.father_phone, f.mother_phone, s.dob, s.gender, s.school_grade
		FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
		INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_family f ON s.family_id = f.id ";

		if (!empty($_GET['group']) || !empty($_GET['subject'])) {

			$sql .= " INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id where m.latest = 1";

			if (!empty($_GET['group']) && !empty($_GET['subject'])) {
				$sql .= " and m.group_id='" . $_GET['group'] . "' and m.class_id='" . $_GET['subject'] . "' and ";
			} elseif (!empty($_GET['group']) && empty($_GET['subject'])) {
				$sql .= " and m.group_id='" . $_GET['group'] . "' and ";
			} elseif (empty($_GET['group']) && !empty($_GET['subject'])) {
				$sql .= " and m.class_id='" . $_GET['subject'] . "' and ";
			}
		} else {
			$sql .= " where ";
		}

		$sql .= " u.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 group by s.user_id order by created_on desc";

		$all_students = $db->get_results($sql, ARRAY_A);

		for ($i = 0; $i < count((array)$all_students); $i++) {

			if (!isset($all_students[$i]['mother']) && empty($all_students[$i]['mother'])) {
				$all_students[$i]['mother'] = 'Data not found';
			} else {
				$all_students[$i]['mother'] = $all_students[$i]['mother'];
			}

			if (!empty($all_students[$i]['father_phone'])) {
				$all_students[$i]['father_phone'] = internal_phone_check($all_students[$i]['father_phone']);
			}

			if (!empty($all_students[$i]['mother_phone'])) {
				$all_students[$i]['mother_phone'] = internal_phone_check($all_students[$i]['mother_phone']);
			}






			$stugroupclass = $db->get_results("select g.group_name,s.id,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $all_students[$i]['user_id'] . "' and  s.is_active=1 ");

			$classes = $db->get_results("select s.id,s.class_name from ss_classes s where s.is_active = 1 ");

			foreach ($classes as $class) {

				$group =	$db->get_var("select g.group_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id where m.latest = 1 and m.student_user_id='" . $all_students[$i]['user_id'] . "' and m.class_id='" . $class->id . "' ");

				if (!empty($group)) {
					$groupClass = $group;
				} else {
					$groupClass = "";
				}

				$all_students[$i]['group' . $class->id] = $groupClass;
			}

			$all_students[$i]['dob'] = my_date_changer($all_students[$i]['dob']);

			$dob = date('Y-m-d', strtotime($all_students[$i]['dob']));
			$from = new DateTime($dob);
			$to   = new DateTime('today');
			$age = $from->diff($to)->y;

			$all_students[$i]['age'] = $age;
			$all_students[$i]['created_on'] = my_date_changer($all_students[$i]['created_on'], 't');
			$all_students[$i]['gender'] = $all_students[$i]['gender'] == 'm' ? 'Male' : 'Female';
		}

		$finalAry['data'] = $all_students;
		echo json_encode($finalAry);
		exit;
	}
	/////////////////////////////////////// admission_pending_req_report /////////////////////////
} elseif ($_GET['action'] == 'admission_pending_req_report') {
	if (in_array("su_report_admission_request", $_SESSION['login_user_permissions'])) {
		$finalAry = array();

		$sql = "SELECT r.id as admreq_id, CONCAT(c.first_name,' ',COALESCE(c.last_name,'')) AS student, c.gender, c.dob, c.school_grade,
		CONCAT(r.father_first_name,' ',COALESCE(r.father_last_name,'')) AS father,
		CONCAT(r.mother_first_name,' ',COALESCE(r.mother_last_name,'')) AS mother,
		r.primary_email, r.secondary_email,r.father_phone, r.mother_phone, r.class_session, r.created_on FROM ss_sunday_school_reg r 
		INNER JOIN ss_sunday_sch_req_child c ON r.id = c.sunday_school_reg_id WHERE r.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		and c.is_executed = 0 and c.is_delete <> 1 order by student asc";

		$all_students = $db->get_results($sql, ARRAY_A);

		for ($i = 0; $i < count((array)$all_students); $i++) {

			if (!empty($all_students[$i]['father_phone'])) {
				$all_students[$i]['father_phone'] = internal_phone_check($all_students[$i]['father_phone']);
			}

			$all_students[$i]['dob'] = my_date_changer($all_students[$i]['dob']);

			$dob = date('Y-m-d', strtotime($all_students[$i]['dob']));
			$from = new DateTime($dob);
			$to = new DateTime('today');
			$stu_age = $from->diff($to)->y;
			$age = sprintf("%02d", $stu_age);
			$all_students[$i]['age'] = $age;
			$all_students[$i]['created_on'] = my_date_changer($all_students[$i]['created_on'], 't');
			$all_students[$i]['gender'] = $all_students[$i]['gender'] == 'm' ? 'Male' : 'Female';
		}

		$finalAry['data'] = $all_students;
		echo json_encode($finalAry);
		exit;
	}
} elseif ($_GET['action'] == 'sche_payment_report') {
	if (in_array("su_report_scheduled_payment", $_SESSION['login_user_permissions'])) {
		$finalAry = array();

		$sql = "SELECT u.created_on, s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student, 
		CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father,
		CONCAT(f.mother_first_name,' ',COALESCE(f.mother_last_name,'')) AS mother,
		f.primary_email, f.secondary_email,f.father_phone, f.mother_phone, s.dob, s.gender, s.school_grade,
		sfi.schedule_payment_date, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, 
		sfi.schedule_status, s.family_id
		FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
		INNER JOIN ss_student s ON u.id = s.user_id 
		INNER JOIN ss_family f ON s.family_id = f.id 
		INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
		INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id 
		INNER JOIN ss_student_fees_items sfi ON sfi.student_user_id = s.user_id";



		if (!empty($_GET['status'])) {

			$sql .= " where m.latest = 1";
			if ($_GET['status'] == 1) {
				$sql .= " and sfi.schedule_status = 1 and ";
			} elseif ($_GET['status'] == 2) {
				$sql .= " and sfi.schedule_status = 2 and ";
			} elseif ($_GET['status'] == 3) {
				$sql .= " and sfi.schedule_status = 3 and ";
			} elseif ($_GET['status'] == 4) {
				$sql .= " and sfi.schedule_status = 4 and ";
			} elseif ($_GET['status'] == 5) {
				$sql .= " and sfi.schedule_status = 0 and ";
			}
		} else {
			$sql .= " where ";
		}

		$sql .= "u.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 and sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 and pay.default_credit_card =1 ";

		if (!empty($_GET['keysearch'])) {
			$sql .= " and (f.father_first_name LIKE '%" . $_GET['keysearch'] . "%' or f.father_last_name LIKE '%" . $_GET['keysearch'] . "%' or f.mother_first_name LIKE '%" . $_GET['keysearch'] . "%' or f.mother_last_name LIKE '%" . $_GET['keysearch'] . "%') ";
		}

		$sql .= " group by sfi.original_schedule_payment_date ORDER BY  sfi.original_schedule_payment_date ASC";
		// echo $sql;
		// die;
		$all_students = $db->get_results($sql, ARRAY_A);

		for ($i = 0; $i < count((array)$all_students); $i++) {
			$trxn_child_names = $db->get_results("SELECT s.first_name FROM ss_student_fees_items sfi
			INNER JOIN ss_student s ON sfi.student_user_id = s.user_id  INNER JOIN ss_family f ON f.id = s.family_id WHERE  sfi.schedule_payment_date = '" . $all_students[$i]['schedule_payment_date'] . "' AND s.family_id = '" . $all_students[$i]['family_id'] . "' GROUP BY s.user_id");

			$child_name = "";
			foreach ($trxn_child_names as $row) {
				$child_name .= $row->first_name . ", ";
			}

			if ($all_students[$i]['schedule_status'] == 1) {
				$all_students[$i]['payment_trxn_status'] = 'Success';
			} elseif ($all_students[$i]['schedule_status'] == 2) {
				$all_students[$i]['payment_trxn_status'] = 'Cancel';
			} elseif ($all_students[$i]['schedule_status'] == 3) {
				$all_students[$i]['payment_trxn_status'] = 'Hold';
			} elseif ($all_students[$i]['schedule_status'] == 4) {
				$all_students[$i]['payment_trxn_status'] = 'Decline';
			} elseif ($all_students[$i]['schedule_status'] == 0) {
				$all_students[$i]['payment_trxn_status'] = 'Pending';
			} elseif ($all_students[$i]['schedule_status'] == 5) {
				$all_students[$i]['payment_trxn_status'] = 'Skipped';
			} else {
				$all_students[$i]['payment_trxn_status'] = '';
			}
			$all_students[$i]['child_name'] = rtrim($child_name, ', ');
			$all_students[$i]['father'] = $all_students[$i]['father'];
			$all_students[$i]['mother'] = $all_students[$i]['mother'];
			$all_students[$i]['primary_email'] = $all_students[$i]['primary_email'];
			$all_students[$i]['secondary_email'] = $all_students[$i]['secondary_email'];
			$all_students[$i]['father_phone'] = $all_students[$i]['father_phone'];
			$all_students[$i]['mother_phone'] = $all_students[$i]['mother_phone'];
			$all_students[$i]['schedule_date'] = date('m/d/Y', strtotime($all_students[$i]['schedule_payment_date']));
			$all_students[$i]['payment_date'] = date('m/d/Y', strtotime($all_students[$i]['original_schedule_payment_date']));
			$all_students[$i]['final_amount'] = '$' . ($all_students[$i]['final_amount'] + 0);
		}

		$finalAry['data'] = $all_students;
		echo json_encode($finalAry);
		exit;
	}
}

/////////////////////////////////registration_payment_report/////////////////////////////
elseif ($_GET['action'] == 'registration_payment_report') {
	if (in_array("su_report_registration_payment", $_SESSION['login_user_permissions'])) {
		//if (in_array("su_report_list", $_SESSION['login_user_permissions'])) {
		$finalAry = array();
		$admRequests = array();
		$startdate = date('Y-m-d', strtotime($_GET['fromdate']));
		$enddate = date('Y-m-d', strtotime($_GET['todate']));
		if ($_GET['status'] == 'Pending') {
			$status = 0;
		} elseif ($_GET['status'] == 'Success') {
			$status = 1;
		} else {
			$status = 2;
		}

		$admission_reqs = $db->get_results("SELECT * from ss_sunday_school_reg where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND is_paid=1 order by id desc");
		foreach ($admission_reqs as $adm_requests) {
			$children = $db->get_results("SELECT * from ss_sunday_sch_req_child where sunday_school_reg_id = '" . $adm_requests->id . "'");
			$students = "";
			//$child_counter = 0;
			foreach ($children as $child) {
				$students .= $child->first_name . ' ' . $child->last_name . ', ';
			}
			$sql = '';
			if (!empty($_GET['fromdate']) && !empty($_GET['todate']) && !empty($_GET['status'])) {
				$sql .= " WHERE DATE_FORMAT(`payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(`payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND payment_status = '" . $status . "' AND ";
			} elseif (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
				$sql .= " WHERE DATE_FORMAT(`payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(`payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND ";
			} elseif (!empty($_GET['fromdate'])) {
				$sql .= " WHERE DATE_FORMAT(`payment_date` , '%Y-%m-%d' ) = '" . $startdate . "' AND ";
			} elseif (!empty($_GET['todate'])) {
				$sql .= " WHERE DATE_FORMAT(`payment_date` , '%Y-%m-%d' ) = '" . $enddate . "' AND ";
			} elseif (!empty(trim($_GET['status']))) {
				$sql .= " WHERE payment_status = '" . $status . "' AND ";
			} else {
				$sql .= " WHERE";
			}
			$sql .= " sunday_school_reg_id = '" . $adm_requests->id . "' ";

			$payment_txns = $db->get_row("SELECT * from ss_payment_txns " . $sql);

			if (trim($child->first_name) != '') {
				if ($payment_txns->payment_status == 0) {
					$asname = 'Pending';
				} elseif ($payment_txns->payment_status == 1) {
					$asname = 'Success';
				} else {
					$asname = 'Failed';
				}
				if ($payment_txns) {
					//$child_counter++;
					$temp = array();

					$refund_amount = $db->get_var("SELECT refund_amount FROM ss_refund_payment_txns where payment_txn_id='" . $payment_txns->id . "'");
					if (!empty($refund_amount)) {
						$temp['refund_amount'] = $refund_amount;
					} else {
						$temp['refund_amount'] = '<p style="text-align:center">-</p>';
					}

					$temp['child_name'] = rtrim($students, ', ');
					$temp['parent_first'] = $adm_requests->father_first_name . ' ' . $adm_requests->father_last_name;
					$temp['parent_second'] = $adm_requests->mother_first_name . ' ' . $adm_requests->mother_last_name;
					$temp['amount'] = $adm_requests->amount_received;
					$temp['status'] = $asname;
					$temp['transaction_id'] = $payment_txns->payment_unique_id;
					$temp['date'] = my_date_changer($payment_txns->payment_date);
					$admRequests[] = $temp;
				}
			}
		}
		$finalAry['data'] = $admRequests;
		echo json_encode($finalAry);
		exit;
	}
}

//Family wise report

elseif ($_GET['action'] == 'family_wise_sche_payment_report') {
	if (in_array("su_report_discount", $_SESSION['login_user_permissions'])) {
		$finalAry = array();

		$sql = "SELECT u.created_on, s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student, 
		CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father,
		CONCAT(f.mother_first_name,' ',COALESCE(f.mother_last_name,'')) AS mother,
		f.primary_email, f.secondary_email,f.father_phone, f.mother_phone, s.dob, s.gender, s.school_grade,
		sfi.schedule_payment_date, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, 
		sfi.schedule_status, s.family_id
		FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
		INNER JOIN ss_student s ON u.id = s.user_id 
		INNER JOIN ss_family f ON s.family_id = f.id 
		INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
		INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id 
		INNER JOIN ss_student_fees_items sfi ON sfi.student_user_id = s.user_id";


		if (!empty($_GET['status'])) {

			$sql .= " where m.latest = 1";
			if ($_GET['status'] == 1) {
				$sql .= " and sfi.schedule_status = 1 and ";
			} elseif ($_GET['status'] == 2) {
				$sql .= " and sfi.schedule_status = 2 and ";
			} elseif ($_GET['status'] == 3) {
				$sql .= " and sfi.schedule_status = 3 and ";
			} elseif ($_GET['status'] == 4) {
				$sql .= " and sfi.schedule_status = 4 and ";
			} elseif ($_GET['status'] == 5) {
				$sql .= " and sfi.schedule_status = 0 and ";
			}
		} else {
			$sql .= " where ";
		}

		$sql .= "u.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 and sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 and pay.default_credit_card =1 ";

		if (!empty($_GET['family'])) {
			$sql .= " and s.family_id ='" . $_GET['family'] . "'";
		}

		$sql .= " group by sfi.original_schedule_payment_date ORDER BY  sfi.original_schedule_payment_date ASC";

		$all_students = $db->get_results($sql, ARRAY_A);

		for ($i = 0; $i < count((array)$all_students); $i++) {
			$trxn_child_names = $db->get_results("SELECT s.first_name FROM ss_student_fees_items sfi
			INNER JOIN ss_student s ON sfi.student_user_id = s.user_id  INNER JOIN ss_family f ON f.id = s.family_id WHERE  sfi.schedule_payment_date = '" . $all_students[$i]['schedule_payment_date'] . "' AND s.family_id = '" . $all_students[$i]['family_id'] . "' GROUP BY s.user_id");

			$child_name = "";
			foreach ($trxn_child_names as $row) {
				$child_name .= $row->first_name . ", ";
			}

			if ($all_students[$i]['schedule_status'] == 1) {
				$all_students[$i]['payment_trxn_status'] = 'Success';
			} elseif ($all_students[$i]['schedule_status'] == 2) {
				$all_students[$i]['payment_trxn_status'] = 'Cancel';
			} elseif ($all_students[$i]['schedule_status'] == 3) {
				$all_students[$i]['payment_trxn_status'] = 'Hold';
			} elseif ($all_students[$i]['schedule_status'] == 4) {
				$all_students[$i]['payment_trxn_status'] = 'Decline';
			} elseif ($all_students[$i]['schedule_status'] == 0) {
				$all_students[$i]['payment_trxn_status'] = 'Pending';
			} elseif ($all_students[$i]['schedule_status'] == 5) {
				$all_students[$i]['payment_trxn_status'] = 'Skipped';
			} else {
				$all_students[$i]['payment_trxn_status'] = '';
			}
			$all_students[$i]['child_name'] = rtrim($child_name, ', ');
			$all_students[$i]['father'] = $all_students[$i]['father'];
			$all_students[$i]['mother'] = $all_students[$i]['mother'];
			$all_students[$i]['primary_email'] = $all_students[$i]['primary_email'];
			$all_students[$i]['secondary_email'] = $all_students[$i]['secondary_email'];
			$all_students[$i]['father_phone'] = $all_students[$i]['father_phone'];
			$all_students[$i]['mother_phone'] = $all_students[$i]['mother_phone'];
			$all_students[$i]['schedule_date'] = date('m/d/Y', strtotime($all_students[$i]['schedule_payment_date']));
			$all_students[$i]['payment_date'] = date('m/d/Y', strtotime($all_students[$i]['original_schedule_payment_date']));
			$all_students[$i]['final_amount'] = '$' . ($all_students[$i]['final_amount'] + 0);
		}

		$finalAry['data'] = $all_students;
		echo json_encode($finalAry);
		exit;
	}
} elseif ($_GET['action'] == 'discount_report') {
	$finalAry = array();

	// $family = $db->get_results("SELECT f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name, f.primary_email, f.secondary_email FROM ss_family f 
	// INNER JOIN ss_user u ON u.id = f.user_id 
	// INNER JOIN ss_student s ON s.family_id = f.id
	// INNER JOIN ss_student_feesdiscounts sfd ON sfd.student_user_id = s.user_id
	// WHERE f.is_deleted=0 
	// AND u.is_active=1 AND sfd.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' GROUP BY f.id", ARRAY_A);


	$family = $db->get_results("SELECT f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name, f.primary_email, f.secondary_email from ss_student_feesdiscounts as d 
inner join ss_user as u on u.id=d.student_user_id
inner join ss_student as s on s.user_id=d.student_user_id
INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
INNER JOIN ss_family f ON  f.id = s.family_id
where u.is_active=1 and u.is_deleted=0 and d.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and d.status=1 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' GROUP by f.id", ARRAY_A);


	// echo "<pre>";
	// print_r($family);
	// die;
	for ($i = 0; $i < count((array)$family); $i++) {

		$students = $db->get_results("SELECT s.user_id, s.dob, s.allergies, s.school_grade, CONCAT(first_name,' ',COALESCE(middle_name, ''),' ',COALESCE(last_name, '')) AS student_name FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and u.is_deleted = 0 AND s.family_id = '" . $family[$i]['id'] . "' ");

		$trxn_child_names = $db->get_results("SELECT s.user_id,s.first_name,s.last_name FROM ss_student s INNER JOIN ss_family f ON f.id = s.family_id WHERE s.family_id = '" . $family[$i]['id'] . "' GROUP BY s.user_id");


		$basic_fee = [];
		$final_amount_total = [];
		$discount_fee_val_all = [];
		foreach ($students as $stu) {
			$user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
			$groups = [];
			foreach ($user_groups as $group) {
				$groups[] = $group->id;
			}
			$group_ids = implode(",", $groups);

			$basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

			$new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $stu->user_id . "' AND sf.status = 1  and d.status=1 and sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND d.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

			$discountPercentTotal = $basicFees->fee_amount;
			$discountDollarTotal = 0;
			foreach ($new_discountFeesData as $val) {
				if ($val->discount_unit == 'p') {
					$doller = '';
					$percent = '%';
					$fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
					$discountPercentTotal = $discountPercentTotal - $fee_percent;
				} else {
					$doller = $currency;
					$percent = '';
					$discountDollarTotal += $val->discount_percent;
				}
			}
			$basic_fee[$stu->user_id] = $basicFees->fee_amount;
			//$basic_fee_all+= $basicFees->fee_amount;
			$final_amount = ($discountPercentTotal - $discountDollarTotal);
			if ($final_amount > 0) {
				$total_final_amount = $final_amount;
			} else {
				$total_final_amount = 0;
			}
			$final_amount_total[$stu->user_id] = $total_final_amount;
			//$final_amount_all+= $total_final_amount;
			$discount_fee_val_all[$stu->user_id] = $basicFees->fee_amount - $total_final_amount;
		}


		$child_name = "";
		$bascifees = "";
		$finalamountfee = "";
		$discountfee = "";

		foreach ($trxn_child_names as $row) {
			$child_name .= $row->first_name . ' ' . $row->last_name;
			if (isset($basic_fee[$row->user_id]) && !empty($basic_fee[$row->user_id])) {
				$bascifees .= $basic_fee[$row->user_id];
			} else {
				$bascifees .= "0";
			}

			if (isset($final_amount_total[$row->user_id]) &&  !empty($final_amount_total[$row->user_id])) {
				$finalamountfee .= $final_amount_total[$row->user_id];
			} else {
				$finalamountfee .= "0";
			}

			if (isset($discount_fee_val_all[$row->user_id]) &&  !empty($discount_fee_val_all[$row->user_id])) {
				$discountfee .= $discount_fee_val_all[$row->user_id];
			} else {
				$discountfee .= "0";
			}
		}

		$family[$i]['total_student'] = count((array)$trxn_child_names);

		// $bascifees = "";
		// foreach($basic_fee as $basicfee){
		// 	if(!empty($basicfee)){
		// 		$bascifees .= '$'.round($basicfee, 0). "<legend class='text-semibold' style='margin:0px;'></legend>";
		// 	}else{
		// 		$bascifees .= '';
		// 	}
		// }

		// $finalamountfee = "";
		// foreach($final_amount_total as $finalamount){
		// 	if(!empty($finalamount)){
		// 		$finalamountfee .= '$'.$finalamount . "<legend class='text-semibold' style='margin:0px;'></legend>";
		// 	}else{
		// 		$finalamountfee .= '';
		// 	}
		// }

		// $discountfee = "";
		// foreach($discount_fee_val_all as $discount){
		// 	if(!empty($discount)){
		// 		$discountfee .= '$'.$discount . "<legend class='text-semibold' style='margin:0px;'></legend>";
		// 	}else{
		// 		$discountfee .= '';
		// 	}
		// }
		if (!empty($family[$i]['mother_first_name']) && !empty($family[$i]['mother_last_name'])) {
			$family[$i]['1st_parent_name'] = $family[$i]['father_first_name'] . ' ' . $family[$i]['father_last_name'];
			$family[$i]['2st_parent_name'] = $family[$i]['mother_first_name'] . ' ' . $family[$i]['mother_last_name'];
			$family[$i]['parent_name'] = $family[$i]['1st_parent_name'] . ' / ' . $family[$i]['2st_parent_name'];
		} else {
			$family[$i]['1st_parent_name'] = $family[$i]['father_first_name'] . ' ' . $family[$i]['father_last_name'];
			$family[$i]['parent_name'] = $family[$i]['1st_parent_name'];
		}

		$family[$i]['primary_email'] = $family[$i]['primary_email'];
		$family[$i]['secondary_email'] = $family[$i]['secondary_email'];
		$family[$i]['basic_fee'] = $bascifees;
		$family[$i]['discount_fee'] = $discountfee;
		$family[$i]['net_fee'] = $finalamountfee;
		$family[$i]['child_name'] = $child_name;
	}

	$finalAry['data'] = $family;
	echo json_encode($finalAry);
	exit;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ($_GET['action'] == 'payment_report') {
	$finalAry = array();

	$sql = "SELECT sfi.id AS sch_item_id, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_unique_id, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status, s.family_id, s.user_id, f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name,pay.id as pay_id
	FROM ss_student_fees_items sfi 
	INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
	INNER JOIN ss_user u ON u.id = s.user_id 
	INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
	INNER JOIN ss_family f ON f.id = s.family_id 
	INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
	";

	$startdate = date('Y-m-d', strtotime($_GET['fromdate']));
	$enddate = date('Y-m-d', strtotime($_GET['todate']));
	if ($_GET['status'] == 'Pending') {
		$status = 0;
	} elseif ($_GET['status'] == 'Success') {
		$status = 1;
	} elseif ($_GET['status'] == 'Cancel') {
		$status = 2;
	} elseif ($_GET['status'] == 'Decline') {
		$status = 4;
	} elseif ($_GET['status'] == 'Skipped') {
		$status = 5;
	}

	if (!empty($_GET['fromdate']) && !empty($_GET['todate']) && !empty($_GET['status'])) {
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND sfi.schedule_status = '" . $status . "' AND ";
	} elseif (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND ";
	} elseif (!empty($_GET['fromdate'])) {
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) = '" . $startdate . "' AND ";
	} elseif (!empty($_GET['todate'])) {
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) = '" . $enddate . "' AND ";
	} elseif (!empty(trim($_GET['status']))) {
		$sql .= " WHERE sfi.schedule_status = '" . $status . "' AND ";
	} else {
		$sql .= " WHERE";
	}

	$sql .= " sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 AND pay.default_credit_card =1 
	GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.original_schedule_payment_date ASC";


	$family = $db->get_results($sql, ARRAY_A);

	for ($i = 0; $i < count((array)$family); $i++) {
		$trxn_child_names = $db->get_results("SELECT s.first_name FROM ss_student_fees_items sfi
		INNER JOIN ss_student s ON sfi.student_user_id = s.user_id  INNER JOIN ss_family f ON f.id = s.family_id WHERE s.family_id = '" . $family[$i]['id'] . "' GROUP BY s.user_id");

		$child_name = "";
		foreach ($trxn_child_names as $row) {
			$child_name .= $row->first_name . ", ";
		}

		if ($family[$i]['schedule_status'] == 1) {
			$family[$i]['status'] = 'Success';
		} elseif ($family[$i]['schedule_status'] == 2) {
			$family[$i]['status'] = 'Cancel';
		} elseif ($family[$i]['schedule_status'] == 4) {
			$family[$i]['status'] = 'Decline';
		} elseif ($family[$i]['schedule_status'] == 0) {
			$family[$i]['status'] = 'Pending';
		} elseif ($family[$i]['schedule_status'] == 5) {
			$family[$i]['status'] = 'Skipped';
		} else {
			$family[$i]['status'] = '';
		}

		$family[$i]['child_name'] = rtrim($child_name, ", ");

		if (!empty($family[$i]['mother_first_name']) && $family[$i]['mother_last_name']) {
			$family[$i]['1st_parent_name'] = $family[$i]['father_first_name'] . ' ' . $family[$i]['father_last_name'];
			$family[$i]['2st_parent_name'] = $family[$i]['mother_first_name'] . ' ' . $family[$i]['mother_last_name'];
			$family[$i]['parent_name'] = $family[$i]['1st_parent_name'] . ' / ' . $family[$i]['2st_parent_name'];
		} else {
			$family[$i]['1st_parent_name'] = $family[$i]['father_first_name'] . ' ' . $family[$i]['father_last_name'];
			$family[$i]['parent_name'] = $family[$i]['1st_parent_name'];
		}
		$family[$i]['date'] = my_date_changer($family[$i]['schedule_payment_date']);

		if (!empty($family[$i]['final_amount'])) {
			$family[$i]['amount'] =  ($family[$i]['final_amount'] + 0);
		} else {
			$family[$i]['amount'] = '';
		}

		//// 25_jan_2023 /////////
		// $payment_txn_id=$db->get_var(" SELECT id FROM `ss_payment_txns` 
		// WHERE payment_credentials_id='" .$family[$i]['pay_id'] . "' and payment_status=1");

		$refunded_amount = $db->get_var(" SELECT refund_amount FROM `ss_student_fees_transactions` as sft
		inner join ss_payment_txns as txn on txn.id=sft.payment_txns_id
		inner join ss_refund_payment_txns as ref_txn on ref_txn.payment_txn_id=sft.payment_txns_id
		WHERE student_fees_item_id='" . $family[$i]['sch_item_id'] . "'");

		if (!empty($refunded_amount)) {
			$family[$i]['refund_amount'] = $refunded_amount;
		} else {
			$family[$i]['refund_amount'] = '-';
		}
	}

	$finalAry['data'] = $family;
	echo json_encode($finalAry);
	exit;
}

//==========================Manual Payment REPORT=====================

elseif ($_GET['action'] == 'manu_payment_report') {

	$manual_payments = $db->get_results("SELECT txn.payment_date,f.primary_email,txn.comments as reason,CONCAT(f.father_first_name,' ',f.father_last_name) AS father_name,f.father_phone,txn.id,txn.amount
	FROM ss_family as f 
    inner join ss_invoice as inv on f.id=inv.family_id
    inner join ss_invoice_info as inv_info on inv_info.invoice_id=inv.id
    inner join ss_payment_txns as txn on txn.id=inv_info.payment_txn_id
	inner join ss_user as u on u.id=f.user_id
	inner join ss_paymentcredentials as pc on pc.family_id=f.id
	where u.is_active=1 and u.is_deleted=0 and txn.is_payment_type=4 and pc.default_credit_card=1", ARRAY_A);

	if (!empty($manual_payments)) {
		foreach ($manual_payments as $key => $value) {

			$refunded_amount = $db->get_var(" SELECT refund_amount FROM `ss_refund_payment_txns` as ref
	inner join  ss_payment_txns  as txn on txn.id=ref.refund_txn_id
	WHERE payment_txn_id='" . $manual_payments[$key]['id'] . "'");

			if (!empty($refunded_amount)) {
				$manual_payments[$key]['refund_amount'] = $refunded_amount;
			} else {
				$manual_payments[$key]['refund_amount'] = '-';
			}
			$manual_payments[$key]['amount'] = $manual_payments[$key]['amount'];
			$manual_payments[$key]['payment_date'] = my_date_changer($manual_payments[$key]['payment_date']);
			$manual_payments[$key]['father_phone'] = internal_phone_check($manual_payments[$key]['father_phone']);
		}
	}

	$finalAry['data'] = $manual_payments;
	echo json_encode($finalAry);
	exit;
}

//==========================Refund Payment REPORT=====================

elseif ($_GET['action'] == 'refund_payment_report') {
	$refund_payments = $db->get_results("SELECT  txn.payment_date,f.primary_email,txn.comments as reason,ref.refund_amount,CONCAT(f.father_first_name,' ',f.father_last_name) AS father_name,f.father_phone,ref.payment_txn_id,ref.refund_txn_id
	FROM ss_family as f 
	inner join ss_refund_payment_txns  as ref on f.id=ref.family_id
	inner join ss_payment_txns as txn on txn.id= ref.refund_txn_id
	inner join ss_user as u on u.id=f.user_id
	inner join ss_paymentcredentials as pc on pc.family_id=f.id
	where u.is_active=1 and u.is_deleted=0 and txn.is_payment_type=6 and pc.default_credit_card=1", ARRAY_A);

	if (!empty($refund_payments)) {
		foreach ($refund_payments as $key => $value) {

			$old_amount = $db->get_var('SELECT amount FROM `ss_payment_txns` WHERE id="' . $refund_payments[$key]['payment_txn_id'] . '"');
			//// this query return the amount from the schedule item 
			if (empty($old_amount)) {
				$old_amount = $db->get_var('SELECT SUM(sft.amount) FROM `ss_refund_payment_txns` as rft 
			inner join ss_student_fees_transactions as sftt  on sftt.payment_txns_id=rft.payment_txn_id
			inner join ss_student_fees_items as sft on sft.id=sftt.student_fees_item_id
			where sftt.payment_txns_id="' . $refund_payments[$key]['payment_txn_id'] . '"');
			}
			//// this query return the amount from the registration  
			if (empty($old_amount)) {
				$old_amount = $db->get_var('SELECT amount_received FROM ss_payment_txns as txn 
			inner join ss_sunday_school_reg as schreg on schreg.id=txn.sunday_school_reg_id
			inner join ss_refund_payment_txns as rfp on rfp.payment_txn_id=txn.id 
			where rfp.payment_txn_id="' . $refund_payments[$key]['payment_txn_id'] . '"');
			}
			$refund_payments[$key]['amount'] = $old_amount;
			$refund_payments[$key]['refund_amount'] = $refund_payments[$key]['refund_amount'];
			$refund_payments[$key]['payment_date'] = my_date_changer($refund_payments[$key]['payment_date']);
			$refund_payments[$key]['father_phone'] = internal_phone_check($refund_payments[$key]['father_phone']);
		}
	}

	$finalAry['data'] = $refund_payments;
	echo json_encode($finalAry);
	exit;
}
