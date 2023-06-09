<?php 
include_once "includes/config.php";

$ary = array();

$groups = $db->get_results("select * from ss_groups where is_active = 1 and is_deleted = 0");
foreach($groups as $gr){
  $strength = $db->get_var("SELECT COUNT(*) FROM ss_studentgroupmap WHERE latest = 1 and group_id = '".$gr->id."'");
    
  $ary[] = array('letter'=>$gr->group_name,'frequency'=>$strength);
}
 
echo json_encode($ary);
?>