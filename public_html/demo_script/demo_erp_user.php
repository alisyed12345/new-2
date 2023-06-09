<?php 
include_once "../includes/config.php";

include "demo_values.php";

$results = $db->get_results("select * from erp_user");

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
		$sql = "update erp_user set 
		username = '".str_replace(' ','',strtolower($maleNameAry[$mal2])."_".$random_no)."@demo.com',
		password = md5('123456'),
		email = '".str_replace(' ','',strtolower($maleNameAry[$mal2])."_".$random_no)."@demo.com'  	
		where id = '".$res->id."'";
	}else{
		$sql = "update erp_user set 
		email = '".str_replace(' ','',strtolower($maleNameAry[$mal2])."_".$random_no)."@demo.com',
		password = md5('123456') 	
		where id = '".$res->id."'";
	}

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h2>Rows updated ".$counter."</h2>";
?>
