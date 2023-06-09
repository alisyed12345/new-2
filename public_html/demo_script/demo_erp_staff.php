<?php 
include_once "../includes/config.php";

include "demo_values.php";

$results = $db->get_results("select * from erp_staff");

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
	$mobile = rand(9130000000,9139999999);

	$sql = "update erp_staff set 
	first_name = '".$child1Name."', 
	last_name = '".ucwords(strtolower($maleNameAry[$mal2]))."',
	mobile = '".$mobile."',
	phone = '',
	address_1 = '".$addressAry[$add]."', 
	address_2 = ''
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h2>Rows updated ".$counter."</h2>";
?>
