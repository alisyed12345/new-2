<?php 
include_once "../includes/config.php";

include "demo_values.php";

$results = $db->get_results("select * from erp_groups");

$counter = 0 ;
foreach($results as $res){
	$sql = "update erp_groups set 
	group_name = 'Group ".($counter+1)."'
	where id = '".$res->id."'";

	$query_res = $db->query($sql);
	
	if($query_res){
		$counter++;
	}
}

echo "<h2>Rows updated ".$counter."</h2>";
?>
