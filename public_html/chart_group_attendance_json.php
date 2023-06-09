<?php 
include_once "includes/config.php";

$ary = array();

$groups = $db->get_results("select * from ss_groups where is_active = 1 and is_deleted = 0");
foreach($groups as $gr){
  $presAttendance = $db->get_var("SELECT COUNT(*) as tot_present FROM ss_attendance WHERE (is_present = 1 OR is_present = 2) and student_group_map_id in (select id from ss_studentgroupmap where group_id = '".$gr->id."')");
  $totaAttendance = $db->get_var("SELECT COUNT(*) as tot_present FROM ss_attendance WHERE student_group_map_id in (select id from ss_studentgroupmap where group_id = '".$gr->id."')");
  
  $ary[] = array('letter'=>$gr->group_name,'frequency'=>number_format($presAttendance/$totaAttendance,2));
}
 
echo json_encode($ary);
?>