<?php 
include_once "../includes/config.php";
//==========================SAVE MESSAGE=====================
if($_POST['action'] == 'save_feedback'){
	$ret_sql = $db->query("insert into ss_feedback set full_name = '".$db->escape(trim($_POST['full_name']))."', 
	email= '".$db->escape(trim($_POST['email']))."', contact_no = '".$db->escape(trim($_POST['contact_no']))."', 
	message='".$db->escape(trim($_POST['message']))."',created_on='".date('Y-m-d H:i:s')."'");
	$emailbody = @'<table border="0" cellpadding="5" cellspacing="5" width="100%">
	<tr><td colspan="2">Hello Webmaster, below information received from feedback form<br><br></td></tr>
	<tr><td style="width:75px"><strong>Full Name</strong></td><td>'.trim($_POST['full_name']).'</td></tr>
	<tr><td><strong>Email</strong></td><td>'.trim($_POST['email']).'</td></tr>
	<tr><td><strong>Contact No</strong></td><td>'.trim($_POST['contact_no']).'</td></tr>
	<tr valign="top"><td><strong>Message</strong></td><td>'.trim($_POST['message']).'</td></tr></table>';
	if($ret_sql){
		$mailservice_request_from = MAIL_SERVICE_KEY; 
		$mail_service_array = array(
							'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME . "Feedback",
							'message' => $emailbody,
							'request_from' => '',
							'attachment_file_name' => '',
							'attachment_file' => '',
							'to_email' => '',
							'cc_email' => EMAIL_WEBMASTER,
							'bcc_email' => ''
						);
		mailservice($mail_service_array);
		echo json_encode(array('msg'=>'Feedback saved successfully','code'=>1));
		exit;
	}else{
		echo json_encode(array('msg'=>'Feedback not saved','code'=>0,'_errpos'=>'1'));
		exit;
	}
}
?>