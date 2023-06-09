<?php
include_once "../includes/config.php";
if ($_POST['action'] == 'email_support') {
	if (isset($_POST['email'])) {
		$emailbody_support = "Hello Admin,<br>";
		$emailbody_support .= "Below information received from " . CENTER_SHORTNAME . ' ' . SCHOOL_NAME."<br>";
		$emailbody_support .= "<br><strong>Name:</strong> " . $_POST['name'];
		$emailbody_support .= "<br><strong>Phone Number:</strong> " . $_POST['phone_no'];
		$emailbody_support .= "<br><strong>Email:</strong> " . $_POST['email'];
		$emailbody_support .= "<br><strong>Message:</strong> " . $_POST['message'];
		$mailservice_request_from = MAIL_SERVICE_KEY;
		$mail_service_array = array(
			'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME ."Inquiry Form - " . $_POST['name'],
			'message' => $emailbody_support,
			'request_from' => MAIL_SERVICE_KEY,
			'attachment_file_name' => '',
			'attachment_file' => '',
			'to_email' => [SCHOOL_GEN_EMAIL],
			'cc_email' => '',
			'bcc_email' => ''
		);
		mailservice($mail_service_array);
		echo json_encode(array('code' => "1", 'msg' => 'Support email sent successfully'));
	} else {
		$return_resp = array('code' => "0", 'msg' => 'Support email not sent. Please try later.', 'err_pos' => 1);
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
	exit;
}
