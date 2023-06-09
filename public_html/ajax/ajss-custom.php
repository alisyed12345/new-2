<?php
include_once "../includes/config.php";
include_once "../includes/FortePayment.class.php";
$forte_configarray = array(
    'FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID, 
    'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY, 
    'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID,
    'FORTE_LOCATION_ID' => FORTE_LOCATION_ID,
    'ENVIRONMENT' => ENVIRONMENT, 
 );
$fortePayment = new FortePayment($forte_configarray);
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}
$db->query('BEGIN');
$paymentcredentials = $db->get_results("select * from ss_paymentcredentials");
$i = 0;
//hell
foreach($paymentcredentials as $paymentcred){
    $credit_card_type = base64_decode($paymentcred->credit_card_type);
    $credit_card_no = str_replace(' ', '', base64_decode($paymentcred->credit_card_no));
    $credit_card_exp = base64_decode($paymentcred->credit_card_exp);
    $credit_card_cvv = base64_decode($paymentcred->credit_card_cvv);
    $credit_card_expAry = explode('-', $credit_card_exp);
    $credit_card_exp_month = $credit_card_expAry[0];
    $credit_card_exp_year = $credit_card_expAry[1];
    $cc_no = substr($credit_card_no,-4);
    $encoded_cc_no = base64_encode($cc_no);
    $family_info = $db->get_row("select * from ss_family where id = '".trim($db->escape($paymentcred->family_id))."'");
    $forteParamsSend = array(
      'creditCardType' => trim($credit_card_type),
      'creditCardNumber' => trim($credit_card_no),
      'expMonth' => $credit_card_exp_month,
      'expYear' => $credit_card_exp_year,
      'cvv' => $credit_card_cvv,
      'firstName' => $family_info->father_first_name,
      'lastName' => $family_info->father_last_name,
      'email' =>$family_info->primary_email,
      'phone' => $family_info->father_phone,
      'city' => $family_info->billing_city,
      'zip'    => $family_info->billing_post_code,
      'countryCode' => 'US',
    );
    $forteParams = json_encode($forteParamsSend);
    $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
    if(isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)){
      $customertoken = $customerPostRequest->customer_token;
      $paymethodtoken = $customerPostRequest->default_paymethod_token;
    }else{
      $customertoken = "";
      $paymethodtoken = "";
    }
    if(!empty($customertoken) && !empty($paymethodtoken)){
      $customer = $db->query("update ss_family set forte_customer_token='".$customertoken."', updated_on='".date('Y-m-d H:i:s')."' where id='".trim($db->escape($paymentcred->family_id))."'");
      $payment_credentials = $db->query("update ss_paymentcredentials set credit_card_no='".$encoded_cc_no."', credit_card_cvv='', forte_payment_token= '".$paymethodtoken."',
      updated_on='".date('Y-m-d H:i:s')."' where id='".trim($db->escape($paymentcred->id))."' ");      
    } 
    $i++;
}
if(count((array)$paymentcredentials) == $i){
    $db->query('COMMIT');
    echo json_encode(array('code' => '1', 'msg' => '<p class="text-success">Process successfully</p>', '_errpos' => 1));
	  exit;
}else{
    $db->query('ROLLBACK');
    echo json_encode(array('code' => '0', 'msg' => '<p class="text-danger">Process Failed</p>', '_errpos' => 1));
	  exit;
}
?>