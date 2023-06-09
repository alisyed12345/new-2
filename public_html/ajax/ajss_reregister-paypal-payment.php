<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
  return;
}
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
$payableAmount = 25; //$5 Registration Charge
// Create an instance of PaypalPro class
$paypal = new PaypalPro();
// Payment details
$paypalParams = array(
	'paymentAction' => 'Sale',
	'itemName' => "School Registration",
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
	'zip' => $post_code,
	'countryCode' => $countryCode,
); 
$paypal_response = $paypal->paypalCall($paypalParams);
$paymentStatus = strtoupper($paypal_response["ACK"]);
if($paymentStatus == "SUCCESS"){
	// Transaction info
	$transactionID = $paypal_response['TRANSACTIONID'];
	$paidAmount = $paypal_response['AMT'];
	//MARK IS_PAID TO 1
	$db->query("Update ss_sunday_school_reg set is_paid = 1, paypal_response ='".mysql_real_escape_string(json_encode($paypal_response))."' where id = '".$sunday_school_reg_id."'");
	$next_year = date('Y')+1;
	$final_year = date('Y') - $next_year;
	//COMMENTED ONLY FOR TESTING - 29-JUL-2019
	$mailservice_request_from = MAIL_SERVICE_KEY; 
	$mail_service_array = array(
		'subject' => "registration ".$final_year,
		'message' => sun_sch_email_body($emailbody),
		'request_from' => $mailservice_request_from,
		'attachment_file_name' => '',
		'attachment_file' => '',
		'to_email' => [$primary_email],
		'cc_email' => '',
		'bcc_email' => ''
	);
	
	mailservice($mail_service_array);
	echo json_encode(array('code'=>'1','msg'=>'Registration form submitted successfully','encoded_key'=>md5($sunday_school_reg_id)));
	exit;
}else{
	$db->query("Update ss_sunday_school_reg set is_paid = 0, paypal_response ='".mysql_real_escape_string(json_encode($paypal_response))."' where id = '".$sunday_school_reg_id."'");
	//echo json_encode($paypal_response);
	$errorMsg = 'Registration process failed';
	if(isset($paypal_response["ACK"])){
	$errorMsg = $paypal_response["L_LONGMESSAGE0"];
	}
	echo json_encode(array('code' => '0', 'msg' => $errorMsg, '_errpos' => 2));
	exit;
}
?>