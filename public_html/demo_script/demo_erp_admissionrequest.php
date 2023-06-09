<?php 
include_once "../includes/config.php";

include "demo_values.php";

$results = $db->get_results("select * from erp_admissionrequest");

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

	$phone1 = rand(9130000000,9139999999);
	$phone2 = rand(9130000000,9139999999);

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

	$sql = "update erp_admissionrequest set 
	father_first_name = '".ucwords(strtolower($maleNameAry[$mal1]))."', 
	father_last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."', 
	father_area_code = '', mother_area_code = '', 
	father_phone = '".$phone1."', mother_phone = '".$phone2."', 
	mother_first_name = '".ucwords(strtolower($femaleNameAry[$fem1]))."', 
	mother_last_name = '".ucwords(strtolower($femaleNameAry[$fem2]))."', 
	father_email = '".str_replace(' ','',strtolower($maleNameAry[$mal1])."_".rand(1,99))."@demo.com', 
	mother_email = '".str_replace(' ','',strtolower($femaleNameAry[$fem1])."_".rand(1,99))."@demo.com',
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
	addition_notes = ''
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h2>Rows updated ".$counter."</h2>";
?>
