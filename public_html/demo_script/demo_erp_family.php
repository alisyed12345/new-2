<?php 
include_once "../includes/config.php";

include "demo_values.php";

$results = $db->get_results("select * from erp_family");

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

	$sql = "update erp_family set 
	father_first_name = '".ucwords(strtolower($maleNameAry[$mal1]))."', 
	father_last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."', 
	father_area_code = '', mother_area_code = '', 
	father_phone = '".$phone1."', mother_phone = '".$phone2."', 
	mother_first_name = '".ucwords(strtolower($femaleNameAry[$fem1]))."', 
	mother_last_name = '".ucwords(strtolower($maleNameAry[$mal1]))."', 
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

echo "<h2>Rows updated ".$counter."</h2>";
?>
