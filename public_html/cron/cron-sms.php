<?php 
//LIVE - PROD SITE
// set_include_path('/home3/bayyanor/public_html/ick/summercamp/includes/');
//set_include_path('/webroot/b/a/bayyan005/icksaturdaydv.click2clock.com/www/includes/');


//LIVE - QA SITE
//set_include_path('/home3/bayyanor/public_html/ick/academy_new/includes/');

//DEV
//Devlopment - QA SITE
// set_include_path('/webroot/b/a/bayyan001/icksatqa.magesticflyer.com/www/includes/');
// require "config.php";
include_once "../includes/config.php";
//CURRENT SESSION
$current_session_row = $db->get_row("select * from ss_school_sessions where current = 1 ");
$current_session = $current_session_row->id;

//ADDED ON 02-OCT-2018
//$requests = $db->get_results("select * from ss_bulk_sms_mobile where delivery_status <> 1 and (attempt_counter = 0 or attempt_counter = 1) order by attempt_counter asc limit 30");

//ADDED ON 30-AUG-2020
$fetch_limit = 10;
$requests = $db->get_results("select * from ss_bulk_sms_mobile where delivery_status <> 1 and (attempt_counter = 0 or attempt_counter = 1) order by attempt_counter asc limit ".$fetch_limit);

$nexmo_mno_ary_index = -1;
$nexmo_mno_ary = array('19138843888','12018172888','12019030888','12085042888','12097279888','12107145888','12134091888','12134094888','12134097888','12134933888');

foreach($requests as $req){
	$nexmo_mno_ary_index++;

	if($nexmo_mno_ary_index == $fetch_limit){
		$nexmo_mno_ary_index = 0;
	}

	$receiver_mobile_no = str_replace('-','',trim($req->receiver_mobile_no));

	$message = $db->get_row("select * from ss_bulk_sms where id = '".$req->bulk_sms_id."'");


	/*COMMENTED ON 03-MAR-2021
	if(strlen($req->receiver_mobile_no) == 10){
		$receiver_mobile_no = '1'.$req->receiver_mobile_no;
	}elseif(substr($req->receiver_mobile_no,0,2) == '+1'){
		$receiver_mobile_no = substr($req->receiver_mobile_no,1);
	}else{
		$receiver_mobile_no =  $req->receiver_mobile_no;
	} */

	//ADDED ON 03-MAR-2021
	// if(strlen($receiver_mobile_no) == 10){
	// 	$receiver_mobile_no = '+1'.$receiver_mobile_no;
	// }elseif(substr($receiver_mobile_no,0,2) == '+1'){
	// 	$receiver_mobile_no = substr($receiver_mobile_no,1);
	// }


	
	$message_text = strip_tags($message->message);
	$message_text = CENTER_SHORTNAME.' '.SCHOOL_NAME." : ".$message_text;
	
	//CURL CODE TO SEND MESSAGE
	$output=phone_sms($receiver_mobile_no,$message_text); /// function for sending the mobile sms

	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, "https://rest.nexmo.com/sms/json");
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// curl_setopt($ch, CURLOPT_POST, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, "api_key=".APIKEY."&api_secret=".APISECRET."&to=".$receiver_mobile_no."&from=".$nexmo_mno_ary[$nexmo_mno_ary_index]."&text=".$message_text);
	// $output = curl_exec($ch);
	// $info = curl_getinfo($ch);
	// curl_close($ch);
	
	$dec = json_decode($output,true);
	$rowdata = json_encode($output,true);


	// if($dec['messages'][0]['status'] == 3){
	// 	$requests = $db->query("update ss_bulk_sms_mobile set sms_raw_data = '".$rowdata."', email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");	
	// }
	// if($dec['messages'][0]['status'] == 0){
	// 	$requests = $db->query("update ss_bulk_sms_mobile set sms_raw_data = '".$rowdata."', delivery_status = 1, attempt_counter = attempt_counter + 1, 
	// 	email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
	// }else{
	// 	$requests = $db->query("update ss_bulk_sms_mobile set sms_raw_data = '".$rowdata."', delivery_status = 0, attempt_counter = attempt_counter + 1, 
	// 	email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
	// }  

	if($dec['status'] == 1){
		$requests = $db->query("update ss_bulk_sms_mobile set sms_raw_data = '".$rowdata."', delivery_status = 1, attempt_counter = attempt_counter + 1, 
		email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
	}else{
		$requests = $db->query("update ss_bulk_sms_mobile set sms_raw_data = '".$rowdata."', delivery_status = 0, attempt_counter = attempt_counter + 1, 
		email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
	}  

}


?>