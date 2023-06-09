<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}
if (!empty(get_country()->currency)) {
	$currency = get_country()->currency;
} else {
	$currency = '';
}
//==========================LIST BASIC FEES=====================
if ($_POST['action'] == 'list_invoice') {
	$finalAry = array();
	// $get_all_invoice_data = $db->get_results("SELECT inv.id,inv.schedule_unique_id, inv.invoice_id, inv.invoice_date, inv.amount, inv.receipt_id, inv.receipt_date, (CASE WHEN inv.is_due = 2 THEN 'Overdue' WHEN inv.is_due = 1 THEN 'Paid' ELSE 'Due' END) AS due, inv.status, inv.invoice_file_path, inv.receipt_file_path FROM ss_invoice inv WHERE inv.family_id = '" . trim($_POST['user_id']) . "' and inv.status=1 order by inv.id desc", ARRAY_A);
	$get_all_invoice_data = $db->get_results("SELECT inv.id,inv.schedule_unique_id, inv.invoice_id, inv.invoice_date, inv.amount, inv.receipt_id, inv.receipt_date, 
	(CASE WHEN inv.is_due = 2 THEN 'Overdue' WHEN inv.is_due = 1 THEN 'Paid' WHEN inv.is_due = 3 THEN 'Failed' ELSE 'Due' END) AS due,
	inv.is_type, inv.status, inv.invoice_file_path, inv.receipt_file_path 
	FROM ss_invoice inv WHERE inv.family_id = '" . trim($_POST['user_id']) . "' and inv.status=1 and inv.is_type=" . trim($_POST['is_type']) . " order by inv.id desc", ARRAY_A);

	if (empty($get_all_invoice_data)) {
		$finalAry['data'] = $get_all_invoice_data;
		echo json_encode($finalAry);
		exit;
	}

	for ($i = 0; $i < count((array)$get_all_invoice_data); $i++) {

		if (!empty($get_all_invoice_data[$i]['invoice_date']) && !empty($get_all_invoice_data[$i]['invoice_id'])) {
			$get_all_invoice_data[$i]['invoicedate'] = my_date_changer($get_all_invoice_data[$i]['invoice_date']);
			$get_all_invoice_data[$i]['invoice_id'] = $get_all_invoice_data[$i]['invoice_id'];
		} else {
			$get_all_invoice_data[$i]['invoicedate'] = '-';
			$get_all_invoice_data[$i]['invoice_id'] = 'No invoice';
		}
		if (!empty($get_all_invoice_data[$i]['receipt_date']) && !empty($get_all_invoice_data[$i]['receipt_id'])) {
			$get_all_invoice_data[$i]['receiptdate'] = my_date_changer($get_all_invoice_data[$i]['receipt_date']);
			$get_all_invoice_data[$i]['receipt_id'] = $get_all_invoice_data[$i]['receipt_id'] . ' (' . $get_all_invoice_data[$i]['receiptdate'] . ')';
		} else {
			$get_all_invoice_data[$i]['receiptdate'] = '';
			$get_all_invoice_data[$i]['receipt_id'] = '';
		}
		$invoice = SITEURL . 'payment/invoice_and_pdf/' . $get_all_invoice_data[$i]['invoice_file_path'];
		$reciept = SITEURL . 'payment/invoice_and_pdf/' . $get_all_invoice_data[$i]['receipt_file_path'];
		if (file_get_contents($invoice)) {
			$get_all_invoice_data[$i]['invoice_path'] = SITEURL . 'payment/invoice_and_pdf/' . $get_all_invoice_data[$i]['invoice_file_path'];
		} else {
			$get_all_invoice_data[$i]['invoice_path'] = "";
		}

		if (file_get_contents($reciept)) {
			$get_all_invoice_data[$i]['receipt_path'] = SITEURL . 'payment/invoice_and_pdf/' . $get_all_invoice_data[$i]['receipt_file_path'];
		} else {
			$get_all_invoice_data[$i]['receipt_path'] = "";
		}
		$get_all_invoice_data[$i]['amount'] =  $get_all_invoice_data[$i]['amount'];
		$get_all_invoice_data[$i]['invoice_mainid'] =  $get_all_invoice_data[$i]['id'];
	}

	$finalAry['data'] = $get_all_invoice_data;
	echo json_encode($finalAry);
	exit;
} elseif ($_POST['action'] == 'send_invoice_or_receipt') {
	$invoice_mainid = $_POST['invoice_mainid'];
	$email = $_POST['email'];

	if (!empty(trim($_POST['invoice_id'])) && !empty(trim($_POST['receipt_id']))) {
		$selectmsg = "Invoice/Receipt";
	} elseif (!empty(trim($_POST['invoice_id'])) && empty(trim($_POST['receipt_id']))) {
		$selectmsg = "Invoice";
	} elseif (!empty(trim($_POST['receipt_id'])) && empty(trim($_POST['invoice_id']))) {
		$selectmsg = "Receipt";
	}
	if (in_array("invoice",  $_POST['invoice']) && in_array("receipt",  $_POST['invoice'])) {
		$InvoiceReceiptTitle = "Invoice/Receipt";
	} elseif (in_array("invoice",  $_POST['invoice'])) {
		$InvoiceReceiptTitle = "Invoice";
	} elseif (in_array("receipt",  $_POST['invoice'])) {
		$InvoiceReceiptTitle = "Receipt";
	}


	if (in_array("invoice",  $_POST['invoice']) or in_array("receipt",  $_POST['invoice'])) {


		if ('no invoice' == strtolower($_POST['invoice_id']) && !empty($_POST['family_id']) && !empty($invoice_mainid)) {

			$Query = "SELECT u.is_active,inv.amount as invoice_amount,inv.family_id, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_address_1, f.billing_address_2, pay.credit_card_no, inv.invoice_file_path, inv.receipt_file_path,inv.receipt_date,inv.receipt_id,inv.invoice_date,inv.invoice_id , s.family_id, s.user_id, s.first_name, s.last_name
	FROM ss_invoice  AS inv 
	inner join ss_family as f on inv.family_id=f.id 
	inner join ss_user as u on u.id=f.user_id
	inner join ss_student as s on s.family_id=f.id
	INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = s.user_id
	inner join ss_paymentcredentials as pay on pay.family_id = f.id AND pay.default_credit_card =1
	where f.id='" . $_POST['family_id'] . "' and inv.id='" . trim($invoice_mainid) . "' and ssm.session_id='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 ";
		} else {

			$Query = "SELECT sfi.id AS sch_item_id, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount,inv.amount as invoice_amount, sfi.schedule_status, s.family_id, s.user_id, s.first_name, s.last_name, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_address_1, f.billing_address_2, pay.credit_card_no, inv.invoice_file_path, inv.receipt_file_path,inv.receipt_date,inv.receipt_id,inv.invoice_date,inv.invoice_id FROM 
		ss_student_fees_items sfi
		INNER JOIN ss_invoice inv ON inv.schedule_unique_id = sfi.schedule_unique_id
		INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
		INNER JOIN ss_user u ON u.id = s.user_id
		INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
		INNER JOIN ss_family f ON f.id = s.family_id
		INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
		WHERE sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 AND pay.default_credit_card =1 AND s.family_id = '" . $_POST['family_id'] . "' AND inv.id = '" . trim($invoice_mainid) . "'
		ORDER BY  sfi.original_schedule_payment_date ASC";
		}
		$db->query('BEGIN');
		$remind = $db->get_row($Query);
		$emailsent = 0;
		$star = '************';
		$credit_card_no = $star . substr(str_replace(' ', '', base64_decode($remind->credit_card_no)), -4);

		$trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where  s.family_id='" . $remind->family_id . "' GROUP BY s.user_id ");
		$child_name = "";
		foreach ($trxn_child_names as $row) {
			$child_name .= $row->first_name . ", ";
		}

		$emailbody_support = "Dear " . $remind->father_first_name . " " . $remind->father_last_name . " Assalamu-alaikum<br>";
		$emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
		<tr>
		<td colspan="2" style="text-align: center;">
		<div style="font-size: 18px;margin-top:30px; text-align:center;"><u>' . $InvoiceReceiptTitle . '</u></div>
		</td>
		</tr>   
		<tr>
		<td colspan="2" style="text-align: left; padding-top:10px">
		<table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">
		<tr><td style="width: 25%;" class="color2">Parent Name:</td>
		<td style="width: 75%; text-align:left;">' . $remind->father_first_name . ' ' . $remind->father_last_name . '
		</td></tr>
		<tr><td style="width: 25%;" class="color2">Phone Number:</td>
		<td style="width: 75%; text-align:left;">' . internal_phone_check($remind->father_phone) . '  
		</td></tr>
		<tr><td style="width: 25%;" class="color2">Email:</td>
		<td style="width: 75%; text-align:left;"> ' . $remind->primary_email . ' </td></tr>
		<tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
		<td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
		</td></tr>';
		$emailbody_support .= '<tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
		<td style="width: 75%; text-align:left;">' . $credit_card_no . '
		</td></tr>
		<tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
		<td style="width: 75%; text-align:left;">' . $currency . $remind->invoice_amount . '</td></tr>
		';
		if (!empty($remind->invoice_id) && in_array("invoice",  $_POST['invoice'])) {
			$emailbody_support .= '
		<tr><td style="width: 25%;" class="color2">Invoice ID:</td>
		<td style="width: 75%; text-align:left;">' . $remind->invoice_id . '
		</td></tr>
		<tr><td style="width: 25%;" class="color2">Invoice Date:</td>
		<td style="width: 75%; text-align:left;">' .   my_date_changer($remind->invoice_date) . '
		</td></tr>
		';
		}
		if (!empty($remind->receipt_id)  && in_array("receipt",  $_POST['invoice'])) {
			$emailbody_support .= '<tr><td style="width: 25%;" class="color2">Receipt ID:</td>
		<td style="width: 75%; text-align:left;">' . $remind->receipt_id . '
		</td></tr>
		<tr><td style="width: 25%;" class="color2">Receipt Date:</td>
		<td style="width: 75%; text-align:left;">' .   my_date_changer($remind->receipt_date) . '
		</td></tr>
		';
		}
		$emailbody_support .= '</table>';
		$emailbody_support .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME . ' Team';
		$emailbody_support .= '</td>
		</tr>         
		</table>';

		$emailbody_support .= "For any comments or question, please send email to " . SUPPORT_EMAIL . "";

		if (!empty(trim($_POST['invoice_id'])) && !empty(trim($_POST['receipt_id'])) && in_array("invoice",  $_POST['invoice']) && in_array("receipt",  $_POST['invoice'])) {
			$subject = "Payment invoice and receipt";
			$path = SITEURL . 'payment/invoice_and_pdf';
			$invoicepath = $path . '/' . $remind->invoice_file_path;
			$receiptpath = $path . '/' . $remind->receipt_file_path;
			$mail_service_array = array(
				'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $subject,
				'message' => $emailbody_support,
				'request_from' => MAIL_SERVICE_KEY,
				'attachment_file_name' => [],
				'attachment_file' => [$invoicepath, $receiptpath],
				'to_email' => [$email],
				'cc_email' => [],
				'bcc_email' => ''
			);

			$res = mailservice($mail_service_array);
			$emailsent = '2';
		} elseif (!empty(trim($_POST['invoice_id'])) && in_array("invoice",  $_POST['invoice'])) {
			$subject = "Payment invoice";
			$path =  SITEURL . 'payment/invoice_and_pdf';
			$invoicepath = $path . '/' . $remind->invoice_file_path;
			$receiptpath = $path . '/' . $remind->receipt_file_path;

			$mail_service_array = array(
				'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $subject,
				'message' => $emailbody_support,
				'request_from' => MAIL_SERVICE_KEY,
				'attachment_file_name' => [$remind->invoice_file_path],
				'attachment_file' => [$invoicepath],
				'to_email' => [$email],
				'cc_email' => [],
				'bcc_email' => ''
			);

			$res = mailservice($mail_service_array);
			$emailsent = '1';
		} elseif (!empty(trim($_POST['receipt_id'])) && in_array("receipt",  $_POST['invoice'])) {
			$subject = "Payment receipt";
			$path =  SITEURL . 'payment/invoice_and_pdf';
			$invoicepath = $path . '/' . $remind->invoice_file_path;
			$receiptpath = $path . '/' . $remind->receipt_file_path;

			$mail_service_array = array(
				'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $subject,
				'message' => $emailbody_support,
				'request_from' => MAIL_SERVICE_KEY,
				'attachment_file_name' => [$remind->receipt_file_path],
				'attachment_file' => [$receiptpath],
				'to_email' => [$email],
				'cc_email' => [],
				'bcc_email' => ''
			);

			$res = mailservice($mail_service_array);
			$emailsent = '3';
		}
		if ($emailsent > 0 && $db->query('COMMIT') !== false) {
			echo json_encode(array('code' => "1", 'msg' => '<p class="text-success">Email sent successfully</p>'));
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => '<p class="text-danger">Email not sent</p>'));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => '<p class="text-danger">Please Select ' . $selectmsg . '</p>'));
		exit;
	}
} elseif ($_POST['action'] == 'download_invoice_or_receipt') {
	$id = $_POST['invid'];
	$invoice = $_POST['invoice'];
	$receipt = $_POST['receipt'];
	$Query = "SELECT inv.id, inv.invoice_file_path, inv.receipt_file_path FROM ss_invoice inv WHERE  inv.id = '" . $id . "' ";
	$remind = $db->get_row($Query);
	$downloadcount = 0;
	$invoice_download = "";
	$receipt_download = "";

	if (!empty($invoice) && !empty($receipt)) {
		$invoice_download = SITEURL . 'payment/invoice_and_pdf/' . $remind->invoice_file_path;
		$receipt_download = SITEURL . 'payment/invoice_and_pdf/' . $remind->receipt_file_path;
		$downloadcount = 1;
	} elseif (!empty($invoice) && empty($receipt)) {
		$invoice_download = SITEURL . 'payment/invoice_and_pdf/' . $remind->invoice_file_path;
		$downloadcount = 2;
	} elseif (!empty($receipt) && empty($invoice)) {
		$receipt_download = SITEURL . 'payment/invoice_and_pdf/' . $remind->receipt_file_path;
		$downloadcount = 3;
	}

	if ($downloadcount > 0) {
		echo json_encode(array('code' => "1", 'msg' => '<p class="text-success">Downloaded successfully</p>', 'receiptdownload' => $receipt_download, 'invoicedownload' => $invoice_download));
		exit;
	} else {

		echo json_encode(array('code' => "0", 'msg' => '<p class="text-danger"> Invoice/Receipt Not downloaded</p>'));
		exit;
	}
}
