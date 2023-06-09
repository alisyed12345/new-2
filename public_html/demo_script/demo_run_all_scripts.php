<?php 
include_once "../includes/config.php";

include "demo_values.php";

//===========ERP_USER
$results = $db->get_results("select * from  ss_user");

$counter = 0 ;
foreach($results as $res){
	$mal2 = rand(0,count($maleNameAry)-1);
	$mal5 = rand(0,count($maleNameAry)-1);

	$fem3 = rand(0,count($femaleNameAry)-1);

	if($res->gender == "m" || trim($res->gender) == ""){
		$child1Name = ucwords(strtolower($maleNameAry[$mal5]));
	}else{
		$child1Name = ucwords(strtolower($femaleNameAry[$fem3]));
	}

	$random_no = rand(1,999);

	if(strpos($res->username,'@') !== false){
		$sql = "update  ss_user set 
		username = '".str_replace(' ','',strtolower($maleNameAry[$mal2])."_".$random_no)."@demo.com',
		password = md5('123456'),
		email = '".str_replace(' ','',strtolower($maleNameAry[$mal2])."_".$random_no)."@demo.com'  	
		where id = '".$res->id."'";
	}else{
		$sql = "update  ss_user set 
		email = '".str_replace(' ','',strtolower($maleNameAry[$mal2])."_".$random_no)."@demo.com',
		password = md5('123456') 	
		where id = '".$res->id."'";
	}

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_USER: Rows updated ".$counter."</h4>";

//===========ERP_FAMILY
$results = $db->get_results("select * from  ss_family");

$counter = 0 ;
foreach($results as $res){
	$mal1 = rand(0,count($maleNameAry)-1);
	$mal2 = rand(0,count($maleNameAry)-1);
	$fem1 = rand(0,count($femaleNameAry)-1);
	$fem2 = rand(0,count($femaleNameAry)-1);

	$mal5 = rand(0,count($maleNameAry)-1);
	$mal6 = rand(0,count($maleNameAry)-1);
	$mal7 = rand(0,count($maleNameAry)-1);
	$mal8 = rand(0,count($maleNameAry)-1);

	$fem3 = rand(0,count($femaleNameAry)-1);
	$fem4 = rand(0,count($femaleNameAry)-1);

	$add = rand(0,count($addressAry)-1);
	$cit = rand(0,count($cityAry)-1);
	//$sta = rand(0,count($addressAry)-1);
	$zip = rand(0,count($zipcodeAry)-1);	

	$phone1 = $phoneNoAry[rand(0,count($phoneNoAry)-1)];
	$phone2 = $phoneNoAry[rand(0,count($phoneNoAry)-1)];

	$sql = "update  ss_family set 
	parent1_first_name = '".ucwords(strtolower($maleNameAry[$mal1]))."', 
	parent1_last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."', 
	parent1_area_code = '', parent2_area_code = '', 
	parent1_phone = '".$phone1."', parent2_phone = '".$phone2."', 
	parent2_first_name = '".ucwords(strtolower($femaleNameAry[$fem1]))."', 
	parent2_last_name = '".ucwords(strtolower($maleNameAry[$mal1]))."', 
	primary_email = '".str_replace(' ','',strtolower($maleNameAry[$mal1])."_".rand(1,99))."@demo.com', 
	secondary_email = '".str_replace(' ','',strtolower($femaleNameAry[$fem1])."_".rand(1,99))."@demo.com',
	billing_address_1 = '".$addressAry[$add]."', billing_address_2 = '', billing_city = '".$cityAry[$cit]."', 
	billing_state_id = '16', billing_entered_state = 'KS',  
	billing_post_code = '".$zipcodeAry[$zip]."',
	shipping_address_1 = '".$addressAry[$add]."', shipping_address_2 = '', shipping_city = '".$cityAry[$cit]."', 
	shipping_state_id = '16', shipping_entered_state = 'KS',  
	shipping_post_code = '".$zipcodeAry[$zip]."', shipping_country_id = '1',
	addition_notes = ''
	where id = '".$res->id."'";

	$query_res = $db->query($sql);

	if($query_res){
		$counter++;
	}	
}

echo "<h4>ERP_FAMILY: Rows updated ".$counter."</h4>";

//=========ERP_STAFF
$results = $db->get_results("select * from  ss_staff");

$counter = 0 ;
foreach($results as $res){
	$mal2 = rand(0,count($maleNameAry)-1);
	$mal5 = rand(0,count($maleNameAry)-1);

	$fem3 = rand(0,count($femaleNameAry)-1);

	if($res->gender == "m" || trim($res->gender) == ""){
		$child1Name = ucwords(strtolower($maleNameAry[$mal5]));
	}else{
		$child1Name = ucwords(strtolower($femaleNameAry[$fem3]));
	}

	$add = rand(0,count($addressAry)-1);
	$phonenoIndex = rand(0,count($phoneNoAry)-1);

	$sql = "update  ss_staff set 
	first_name = '".$child1Name."', 
	last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."',
	mobile = '".$phoneNoAry[$phonenoIndex]."',
	phone = '',
	address_1 = '".$addressAry[$add]."', 
	address_2 = ''
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_STAFF: Rows updated ".$counter."</h4>";

//===========ERP_STUDENT
$results = $db->get_results("select * from  ss_student");

$counter = 0 ;
foreach($results as $res){
	$mal2 = rand(0,count($maleNameAry)-1);
	$mal5 = rand(0,count($maleNameAry)-1);

	$fem3 = rand(0,count($femaleNameAry)-1);

	if($res->gender == "m" || trim($res->gender) == ""){
		$child1Name = ucwords(strtolower($maleNameAry[$mal5]));
	}else{
		$child1Name = ucwords(strtolower($femaleNameAry[$fem3]));
	}

	$sql = "update  ss_student set 
	first_name = '".$child1Name."', 
	last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);	

	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_STUDENT: Rows updated ".$counter."</h4>";

//===========ERP_GROUP
$results = $db->get_results("select * from  ss_groups");
foreach($results as $res){
	$sql = "update  ss_groups set 
	group_name = '".chr(65+$counter)."',
	updated_on = NOW()
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_GROUP: Rows updated ".$counter."</h4>";

//======== ss_admissionrequest
$results = $db->get_results("select * from  ss_admissionrequest");

$counter = 0 ;
foreach($results as $res){
	$mal1 = rand(0,count($maleNameAry)-1);
	$mal2 = rand(0,count($maleNameAry)-1);
	$fem1 = rand(0,count($femaleNameAry)-1);
	$fem2 = rand(0,count($femaleNameAry)-1);

	$mal5 = rand(0,count($maleNameAry)-1);
	$mal6 = rand(0,count($maleNameAry)-1);
	$mal7 = rand(0,count($maleNameAry)-1);
	$mal8 = rand(0,count($maleNameAry)-1);

	$fem3 = rand(0,count($femaleNameAry)-1);
	$fem4 = rand(0,count($femaleNameAry)-1);

	$add = rand(0,count($addressAry)-1);
	$cit = rand(0,count($cityAry)-1);
	//$sta = rand(0,count($addressAry)-1);
	$zip = rand(0,count($zipcodeAry)-1);

	$phone1 = $phoneNoAry[rand(0,count($phoneNoAry)-1)];
	$phone2 = $phoneNoAry[rand(0,count($phoneNoAry)-1)];

	if($res->child1_gender == "m" || trim($res->child1_gender) == ""){
		$child1Name = ucwords(strtolower($maleNameAry[$mal5]));
	}else{
		$child1Name = ucwords(strtolower($femaleNameAry[$fem3]));
	}

	if($res->child2_gender == "m" || trim($res->child2_gender) == ""){
		$child2Name = ucwords(strtolower($maleNameAry[$mal6]));
	}else{
		$child2Name = ucwords(strtolower($femaleNameAry[$fem4]));
	}

	$sql = "update  ss_admissionrequest set 
	parent1_first_name = '".ucwords(strtolower($maleNameAry[$mal1]))."', 
	parent1_last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."', 
	parent1_area_code = '', parent2_area_code = '', 
	parent1_phone = '".$phone1."', parent2_phone = '".$phone2."', 
	parent2_first_name = '".ucwords(strtolower($femaleNameAry[$fem1]))."', 
	parent2_last_name = '".ucwords(strtolower($femaleNameAry[$fem2]))."', 
	parent1_email = '".str_replace(' ','',strtolower($maleNameAry[$mal1])."_".rand(1,99))."@demo.com', 
	parent2_email = '".str_replace(' ','',strtolower($femaleNameAry[$fem1])."_".rand(1,99))."@demo.com',
	primary_email = '".str_replace(' ','',strtolower($maleNameAry[$mal1])."_".rand(1,99))."@demo.com', 
	secondary_email = '".str_replace(' ','',strtolower($femaleNameAry[$fem1])."_".rand(1,99))."@demo.com',
	address_1 = '".$addressAry[$add]."', address_2 = '', city = '".$cityAry[$cit]."', 
	state = 'KS', post_code = '".$zipcodeAry[$zip]."',
	child1_first_name = '".$child1Name."', 
	child1_last_name = '".$maleNameAry[$mal1]."', 
	child2_first_name = '".$child2Name."', 
	child2_last_name = '".$maleNameAry[$mal1]."', 
	child3_first_name = '', 
	child3_last_name = '', 
	child3_dob = NULL, 
	child3_gender = NULL, 
	child3_arabic_level = NULL, 
	child3_interview_date = NULL, 
	child3_user_id = NULL, 
	child3_executed = '0', 
	addition_notes = '',
	updated_on = NOW()
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_AdmissionRequest: Rows updated ".$counter."</h4>";

//======= ss_admreq_child
$results = $db->get_results("select * from  ss_admreq_child");

$counter = 0 ;
foreach($results as $res){
	$mal2 = rand(0,count($maleNameAry)-1);
	$mal5 = rand(0,count($maleNameAry)-1);

	$fem3 = rand(0,count($femaleNameAry)-1);

	if($res->gender == "m" || trim($res->gender) == ""){
		$child1Name = ucwords(strtolower($maleNameAry[$mal5]));
	}else{
		$child1Name = ucwords(strtolower($femaleNameAry[$fem3]));
	}

	$sql = "update  ss_admreq_child set 
	first_name = '".$child1Name."', 
	last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."',
	updated_on = NOW()
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_AdmReq_Child: Rows updated ".$counter."</h4>";

//=========ERP_AdmReq_Payment
$updatedrows = $db->query("UPDATE  ss_admreq_payment SET credit_card_no = '', credit_card_exp = '', postal_code = '', bank_acc_no = '', routing_no = ''");

echo "<h4>ERP_AdmReq_Payment: Rows updated ".$updatedrows."</h4>";

//========= ss_bulk_mail_login_info
$updatedrows = $db->query("Delete from  ss_bulk_mail_login_info");

echo "<h4> ss_bulk_mail_login_info: Rows updated ".$updatedrows."</h4>";

//========== ss_bulk_message
$results = $db->get_results("select * from  ss_bulk_message");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_bulk_message set 
	subject = 'Homewrok #".($counter+1)."', 
	message = 'Please read and learn excercice of page #".rand(100,150)."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_bulk_message: Rows updated ".$counter."</h4>";

//========== ss_bulk_message_emails
$mal1 = rand(0,count($maleNameAry)-1);
$results = $db->get_results("select * from  ss_bulk_message_emails");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_bulk_message_emails set 
	receiver_email = '".str_replace(' ','',strtolower($maleNameAry[$mal1])."_".rand(1,99))."@demo.com'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>ERP_bulk_message: Rows updated ".$counter."</h4>";

//========== ss_bulk_sms
$results = $db->get_results("select * from  ss_bulk_sms");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_bulk_sms set 
	message = 'Please read and learn excercice of page #".rand(100,150)."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4> ss_bulk_sms: Rows updated ".$counter."</h4>";

//========== ss_bulk_sms_mobile
$results = $db->get_results("select * from  ss_bulk_sms_mobile");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_bulk_sms_mobile set 
	receiver_mobile_no = '".$phoneNoAry[rand(0,count($phoneNoAry)-1)]."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>".ucwords(' ss_bulk_sms_mobile').": Rows updated ".$counter."</h4>";

//========= ss_bulk_sms_reply
$updatedrows = $db->query("Delete from  ss_bulk_sms_reply");
echo "<h4>".ucwords(' ss_bulk_sms_reply').": Rows updated ".$updatedrows."</h4>";

//========= ss_feedback
$results = $db->get_results("select * from  ss_feedback");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_feedback set 
	full_name = '".$maleNameAry[rand(0,count($maleNameAry)-1)]." ".$maleNameAry[rand(0,count($maleNameAry)-1)]."',
	email = '".str_replace(' ','',strtolower($maleNameAry[rand(0,count($maleNameAry)-1)])."_".rand(100,999))."@demo.com',
	contact_no = '".$phoneNoAry[rand(0,count($phoneNoAry)-1)]."',
	message = '".$parentsFeedbackAry[rand(0,count($parentsFeedbackAry)-1)]."',
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4>".ucwords(' ss_bulk_sms_mobile').": Rows updated ".$counter."</h4>";

//========= ss_fees
$updatedrows = $db->query("Delete from  ss_fees");
$updatedrows = $db->query("Delete from  ss_fees_thirdparty_status");
echo "<h4>".ucwords(' ss_fees').": Rows updated ".$updatedrows."</h4>";

//========= ss_holiday_groups
$updatedrows = $db->query("Delete from  ss_holiday_groups");
echo "<h4>".ucwords(' ss_holiday_groups').": Rows updated ".$updatedrows."</h4>";

//========= ss_holidays
$updatedrows = $db->query("Delete from  ss_holidays");
echo "<h4>".ucwords(' ss_holidays').": Rows updated ".$updatedrows."</h4>";

//========== ss_homework
$results = $db->get_results("select * from  ss_homework");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_homework set 
	homework_text = 'Please read and learn excercice of page #".rand(100,150)."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4> ss_homework: Rows updated ".$counter."</h4>";

//========== ss_homework_sms_mobile
$results = $db->get_results("select * from  ss_homework_sms_mobile");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_homework_sms_mobile set 
	receiver_mobile_no = '".$phoneNoAry[rand(0,count($phoneNoAry)-1)]."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4> ss_homework_sms_mobile: Rows updated ".$counter."</h4>";

//========== ss_loginhistory
$results = $db->get_results("select * from  ss_loginhistory");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_loginhistory set 
	ip_address = '".rand(1,255).".".rand(1,255).".".rand(1,255).".".rand(1,255)."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4> ss_loginhistory: Rows updated ".$counter."</h4>";

//========== ss_message
$results = $db->get_results("select * from  ss_message");

$counter = 0 ;
foreach($results as $res){
	$sql = "update  ss_message set 
	message = '".$schoolMessagesAry[rand(0,count($schoolMessagesAry)-1)]."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h4> ss_message: Rows updated ".$counter."</h4>";

//========= ss_onlinepaymentinfo
$updatedrows = $db->query("Delete from  ss_onlinepaymentinfo");
echo "<h4>".ucwords(' ss_onlinepaymentinfo').": Rows updated ".$updatedrows."</h4>";

//========= ss_parents_payment_info
$updatedrows = $db->query("Delete from  ss_parents_payment_info");
echo "<h4>".ucwords(' ss_parents_payment_info').": Rows updated ".$updatedrows."</h4>";

//========= ss_paymentcredentials
$updatedrows = $db->query("Delete from  ss_paymentcredentials");
echo "<h4>".ucwords(' ss_paymentcredentials').": Rows updated ".$updatedrows."</h4>";

//========= ss_paymentcredentials_backup
$updatedrows = $db->query("Delete from  ss_paymentcredentials_backup");
echo "<h4>".ucwords(' ss_paymentcredentials_backup').": Rows updated ".$updatedrows."</h4>";

//========= ss_verification_code
$updatedrows = $db->query("Delete from  ss_verification_code");
echo "<h4>".ucwords(' ss_verification_code').": Rows updated ".$updatedrows."</h4>";
?>
