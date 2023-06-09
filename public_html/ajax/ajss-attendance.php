<?php 
include_once "../includes/config.php";
$attendance_day_number = date('w');
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
return;
}
//==========================DISPLAY ATTENDANCE HISTORY=====================
if($_POST['action'] == 'get_attendance_of_groupoo'){
$retAry = array();
$group_id = $_POST['group'];
$class_id = $_POST['group_class'];  
$period = explode('-',$_POST['period']);
$get_month =  $period[0];
$get_year =  $period[1];
$begin = date("$get_year-$get_month-01");
$monthcount   = date('t', strtotime($get_month, $get_year));
$end = date("$get_year-$get_month-$monthcount");    
$school_days = $db->get_row("select school_opening_days from ss_client_settings where status = 1 order by id desc limit 1");
$serdays = unserialize($school_days->school_opening_days);
$days_array = [];
foreach($serdays as $row){
	$startDate = new DateTime($begin);
	$endDate = new DateTime($end);
	while ($startDate <= $endDate) {
		if ($startDate->format('w') == $row) {
				$days_array[] = $startDate->format('d');
			//array_push($days_array,$day);
		}
		$startDate->modify('+1 day');
	}
}
$table_header = '<th>Student</th>';
for($i = 0; $i < $monthcount; $i++){
	$ddadd = $i+1;
	$dd = str_pad($ddadd, 2, '0', STR_PAD_LEFT);
	$time = mktime(12, 0, 0, $get_month, $dd, $get_year);            
	if (date('m', $time) == $get_month){
		$table_header .= '<th>'.date ("M d D", strtotime("+$i day", strtotime($begin))).'</th>';
	}
}	
$group_stu = $db->get_results("select user_id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS student_name 
from ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_deleted = 0 AND s.user_id 
in (select student_user_id from ss_studentgroupmap where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
and latest = 1 and class_id='".$class_id."' and group_id='".$group_id."')");
if(count((array)$group_stu)){
	$retAry['data_found'] = 1;	
	foreach($group_stu as $stu){
		$table_row .= '<tr><td>';
		if(check_userrole_by_code('UT01')){	
			$table_row .= '<a href="'.SITEURL.'student/student_edit.php?id='.$stu->user_id.'" target="_blank">'.$stu->student_name.'</a>';
		}else{
			$table_row .= '<a href="javascript:void(0)" class="viewdetail" data-studentname="'.$stu->student_name.'" data-userid="'.$stu->user_id.'">'.$stu->student_name.'</a>';
		}
		$table_row .= '</td>';
		for($i = 0; $i < $monthcount; $i++){
			$ddadd = $i+1;
			$dd = str_pad($ddadd, 2, '0', STR_PAD_LEFT);
			str_pad($value, 8, '0', STR_PAD_LEFT);
			$time = mktime(12, 0, 0, $get_month, $dd, $get_year);  
			if (date('m', $time) == $get_month){
				if(in_array($dd, $days_array)){					
					$table_row .= '<td>';
					$holidayAllGrpCheck = $db->get_results("SELECT * FROM ss_holidays WHERE is_for_all_groups = 1 
					AND date_start <= '".date('Y-m-d',$time)."' AND date_end >= '".date('Y-m-d',$time)."'");
					$holidaySpecGrpCheck = $db->get_results("SELECT * FROM ss_holidays h INNER JOIN ss_holiday_groups hg 
					ON h.id = hg.holiday_id WHERE is_for_all_groups = 0 AND date_start <= '".date('Y-m-d',$time)."' 
					AND date_end >= '".date('Y-m-d',$time)."' AND hg.group_id = '".$group_id."'");
					if(!count((array)$holidayAllGrpCheck) && !count((array)$holidaySpecGrpCheck)){
						//if(date('Y-m-d') != date('Y-m-d',$time)){							
							$student_group_map_id = $db->get_var("select id from ss_studentgroupmap where
							session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and latest = 1 and 
							group_id='".$group_id."' and class_id='".$class_id."' and student_user_id = '".$stu->user_id."' ");
							$classtime_id = $db->get_var("select id from ss_classtime where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
							and class_id = '".$class_id."' and group_id = '".$group_id."'");
							//COMMENTED ON 26AUG2021
							// $atten_info = $db->get_row("select * from ss_attendance where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
							// AND student_user_id='".$stu->user_id."' 
							// and attendance_date='".date('Y-m-d',$time)."' and student_group_map_id='".$student_group_map_id."' 
							// and classtime_id='".$classtime_id."'"); 
							//ADDEDON 26AUG2021
							$atten_info = $db->get_row("select * from ss_attendance where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
							AND student_user_id='".$stu->user_id."' 
							and attendance_date='".date('Y-m-d',$time)."'  
							and classtime_id='".$classtime_id."'");
							// echo "select * from ss_attendance where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
							// AND student_user_id='".$stu->user_id."' 
							// and attendance_date='".date('Y-m-d',$time)."' and student_group_map_id='".$student_group_map_id."' 
							// and classtime_id='".$classtime_id."'"; exit;
							if(!empty($atten_info)){
								//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
								if(check_userrole_by_code('UT01')){	
									//INSIDE - SUPER ADMIN
									/*$table_row .= '<input type="checkbox" '.($atten_info->is_present?'checked="checked"':'').' class="atten_check"><input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d',$time).']" class="atten_hid" value="'.$atten_info->is_present.'" />';*/
									//COMMENTED ON 01-JUN-2021
									//$table_row .= '<input type="radio" '.($atten_info->is_present==1?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" value="1" class="atten_check"> P <input type="radio" '.($atten_info->is_present==2?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" value="2" class="atten_check"> L <input type="radio" '.($atten_info->is_present==0?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" class="atten_check" value="0"> A <input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d',$time).']" class="atten_hid" value="'.$atten_info->is_present.'" />';
									//ADDED ON 01-JUN-2021
									$table_row .= '
									<div class="radio">
									<label>
									<input type="radio" '.($atten_info->is_present==1?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" value="1" class="atten_check"> P 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" '.($atten_info->is_present==2?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" value="2" class="atten_check"> L 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" '.($atten_info->is_present==0?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" class="atten_check" value="0"> A 
									</label>
									</div>	
									<input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d',$time).']" class="atten_hid" value="'.$atten_info->is_present.'" />';
								}elseif(check_userrole_by_code('UT02') || check_userrole_by_code('UT04')){
									//INSIDE - STAFF (SHEIKH/ACCOUNTANT)
									if($atten_info->is_present == 1){
										$table_row .= '<span class="label att_pres">Present</span>';
									}elseif($atten_info->is_present == 2){
										$table_row .= '<span class="label att_late">Late</span>';
									}elseif($atten_info->is_present == 0){
										$table_row .= '<span class="label att_abse">Absent</span>';
									}elseif($atten_info->is_present == '-1'){
										$table_row .= '<span class="label "></span>';
									}
								}
							}else{
								//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
								if(check_userrole_by_code('UT01')){	
									//INSIDE - SUPER ADMIN
									//COMMENTED ON 01-JUN-2021
									//$table_row .= '<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" value="1" class="atten_check"> P <input type="radio" name="att_'.$time.'_'.$stu->user_id.'" value="2" class="atten_check"> L <input type="radio" name="att_'.$time.'_'.$stu->user_id.'" class="atten_check" value="0"> A
									//<input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d',$time).']" class="atten_hid" value="-1" />';
									//ADDED ON 01-JUN-2021
									$table_row .= '
									<div class="radio">
									<label>
									<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" value="1" class="atten_check"> P 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" value="2" class="atten_check"> L 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" class="atten_check" value="0"> A
									</label>
									</div>
									<input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d',$time).']" class="atten_hid" value="-1" />';
								}elseif(check_userrole_by_code('UT02') || check_userrole_by_code('UT04')){
									//INSIDE - STAFF (SHEIKH/ACCOUNTANT)
									//$table_row .= '<input type="checkbox" disabled="disabled">';
									$table_row .= '';
								}
							}
						//}
					}else{ 
						$holiday_reason = '';
						if(count((array)$holidayAllGrpCheck)){
							foreach($holidayAllGrpCheck as $holi){
								if($holiday_reason == ''){
									$holiday_reason = '<span class="label label-danger">'.$holi->reason.'</span>';
								}else{
									$holiday_reason = $holiday_reason.', <span class="label label-danger">'.$holi->reason.'</span>';
								}
							}
						}elseif(count((array)$holidaySpecGrpCheck)){ 
							foreach($holidaySpecGrpCheck as $holi){
								if($holiday_reason == ''){
									$holiday_reason = '<span class="label label-danger">'.$holi->reason.'</span>';
								}else{
									$holiday_reason = $holiday_reason.', <span class="label label-danger">'.$holi->reason.'</span>';
								}
							}
						}
						$table_row .=  $holiday_reason;
					}
					$table_row .= '</td>';
				}else{
					$table_row .= '<td> </td>';
				}	
			}
		}
		$table_row .= '</tr>';
	}
}else{
	$retAry['data_found'] = 0;
}
$sheet = '<table class="table table-bordered" id="attendance_sheet"><thead><tr>'.$table_header.'</tr></thead>';
$sheet .= '<tbody>'.$table_row.'</tbody>';
$sheet .= '</table>';
if($retAry['data_found'] == 0){
	$sheet .= '<div>No record found</div>';
}
$retAry['sheet'] = $sheet;
echo json_encode($retAry);
exit;
}

////////////////////////////////////// get_attendance_of_group /////////////////////////////////////////////////
if($_POST['action'] == 'get_attendance_of_group'){
$retAry = array();
$group_id = $_POST['group'];
$class_id = $_POST['group_class'];
$period = explode('-',$_POST['period']);
$get_month =  $period[0];
$get_year =  $period[1];

// $begin = date("$get_year-$get_month-01");
// $monthcount   = date('t', strtotime($get_month, $get_year));
//$end = date("$get_year-$get_month-$monthcount"); 

$selected_days=[];

$all_day_of_month = [];

$end = date("Y-m-d");

$school_days = $db->get_row("select school_opening_days from ss_client_settings where status = 1 order by id desc limit 1");

$serdays = unserialize($school_days->school_opening_days);


foreach($serdays as $days){
	if($days == '0'){
		$selected_days[]='sunday';
	}elseif($days == '1'){
		$selected_days[]='monday';
	}elseif($days == '2'){
		$selected_days[]='tuesday';
	}elseif($days == '3'){
		$selected_days[]='wednesday';
	}elseif($days == '4'){
		$selected_days[]='thursday';
	}elseif($days == '5'){
		$selected_days[]='friday';
	}elseif($days == '6'){
		$selected_days[]='saturday';
	}
}

if(!empty($_POST['showfullmonth'])){

$show_full_month =$_POST['showfullmonth'];

	$month_start_date = new DateTime("first day of $get_year-$get_month");

	$month_end_date = new DateTime("last day of $get_year-$get_month");

	$month_start_date = $month_start_date->format("Y-m-d");

	$month_end_date = $month_end_date->format("Y-m-d");

	$all_day_of_month=[];

		////////////// difference of days ////////
		$date1=date_create($month_start_date);
		$date2=date_create($month_end_date);
		$diff=date_diff($date1,$date2);
		$days = $diff->format("%a");

		////////////// adding the difference of days ////////
		
		for($y=0;$y<=$days;$y++){
		$all_day_of_month[]=$month_start_date;
		$date=date_create($month_start_date);
		date_add($date,date_interval_create_from_date_string("1 days"));
		$month_start_date = date_format($date,"Y-m-d");
		}
		sort($all_day_of_month);
		$normal_format = $all_day_of_month;

}else{

	function getdays($year, $month,$day)
	{
		return new DatePeriod(
			new DateTime("first $day of $year-$month"),
			DateInterval::createFromDateString("next $day"),
			new DateTime("last day of $year-$month")
		);
	}
	
	foreach($selected_days as $each_day){
		foreach (getdays($get_year,$get_month,$each_day) as $wednesday) {
			$all_day_of_month[] = $wednesday->format("Y-m-d");
		}
	}

	sort($all_day_of_month);
	$normal_format = $all_day_of_month;
}

$table_header = '<th>Student</th>';

foreach($all_day_of_month as $dates){
	$table_header .= '<th>'.date('M d D',strtotime($dates)).'</th>';
}

						/////////////////////////////// holiday of group /////////////////////

$holiday_dates= $db->get_results('SELECT h.date_start,h.date_end,h.reason,g.id
FROM `ss_holidays` as h 
inner join ss_holiday_groups as hg on h.id=hg.holiday_id
inner join ss_groups as g on g.id=hg.group_id
WHERE g.id="'.$group_id.'" and h.is_active=1');


$array_for_holiday_check_db=[];

foreach($holiday_dates as $dates_day){
	
	if($dates_day->date_start==$dates_day->date_end){

		$array_for_holiday_check_db[]= $dates_day->date_start;

	}elseif($dates_day->date_start < $dates_day->date_end){
		////////////// difference of days ////////
		$date1=date_create($dates_day->date_start);
		$date2=date_create($dates_day->date_end);
		$diff=date_diff($date1,$date2);
		$days = $diff->format("%a");
	////////////// adding the difference of days ////////
	$date_increase =$dates_day->date_start;
		for($y=0;$y<=$days;$y++){

			$array_for_holiday_check_db[]=$date_increase;
			$date=date_create($date_increase);
			date_add($date,date_interval_create_from_date_string("1 days"));
			$date_increase = date_format($date,"Y-m-d");
			
		}
		sort($array_for_holiday_check_db);
	}
	
}

				/////////////////////////////// holiday of all group /////////////////////

$holiday_of_all_groups= $db->get_results('SELECT h.date_start,h.date_end,h.reason
FROM `ss_holidays` as h 
WHERE h.is_active=1 and h.is_for_all_groups=1 and h.session="'.$_SESSION['icksumm_uat_CURRENT_SESSION'].'"');



foreach($holiday_of_all_groups as $dates_day){
	
	if($dates_day->date_start==$dates_day->date_end){

		$array_for_holiday_check_db[]= $dates_day->date_start;

	}elseif($dates_day->date_start <= $dates_day->date_end){
		////////////// difference of days ////////
		$date1=date_create($dates_day->date_start);
		$date2=date_create($dates_day->date_end);
		$diff=date_diff($date1,$date2);
		$days = $diff->format("%a");
	////////////// adding the difference of days ////////
	$date_increase =$dates_day->date_start;
		for($y=0;$y<=$days;$y++){

			$array_for_holiday_check_db[]=$date_increase;
			$date=date_create($date_increase);
			date_add($date,date_interval_create_from_date_string("1 days"));
			$date_increase = date_format($date,"Y-m-d");
			
		}
		sort($array_for_holiday_check_db);
	}
	
}


/////////////////////////////// holiday of all group END /////////////////////

$group_and_class_stu= $db->get_results("SELECT concat(s.first_name,' ',s.last_name) as student_name ,s.user_id,g.id as grp_id
from ss_groups as g 
inner join ss_studentgroupmap as grpm on grpm.group_id=g.id
inner join ss_classes as c on c.id=grpm.class_id
inner join ss_student as s on s.user_id=grpm.student_user_id
inner join ss_classtime as ct on ct.group_id=g.id
inner join ss_user as u on u.id=grpm.student_user_id
where g.id='".$group_id."' and c.id='".$class_id."' and grpm.session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and c.is_active=1 and g.is_active=1 and u.is_active=1 and u.is_deleted=0 and grpm.latest=1 GROUP by grpm.id");


foreach($group_and_class_stu as $student_details){

	$table_row .= '<tr><td>';

	$table_row .= '<a href="'.SITEURL.'student/student_edit.php?id='.$student_details->user_id.'" target="_blank">'.$student_details->student_name.'</a>';
	$table_row .= '</td>';
 
// $time = mktime(12, 0, 0, $get_month, $dd, $get_year);
foreach($normal_format as $days){
	
if(in_array($days, $array_for_holiday_check_db)){

	$holiday_reason = $db->get_row('SELECT h.date_start,h.date_end,h.reason,g.id FROM `ss_holidays` as h 
	inner join ss_holiday_groups as hg on h.id=hg.holiday_id
	inner join ss_groups as g on g.id=hg.group_id
	WHERE g.id="'.$student_details->grp_id.'" and h.is_active=1 and (h.date_start <="'.$days.'" and h.date_end >="'.$days.'")');

if(empty($holiday_reason)){   //////////////////// holiday for all groups //////////
	$holiday_reason = $db->get_row('SELECT h.date_start,h.date_end,h.reason
	FROM `ss_holidays` as h 
	WHERE h.is_active=1 and h.is_for_all_groups=1 and h.session="'.$_SESSION['icksumm_uat_CURRENT_SESSION'].'" and (h.date_start <="'.$days.'" and h.date_end >="'.$days.'")');
}

	if(!empty($holiday_reason)){
		$holiday_reason = $holiday_reason->reason;
	}else{
		$holiday_reason = $old_reason;
	}
	$old_reason = $holiday_reason;



$table_row .= '<td><span class="label label-danger">'.$holiday_reason.'</span></td>';
	
}else{

 $attendance_date_of_stu = $db->get_row("SELECT * FROM `ss_attendance`  as a
 inner join ss_classtime as ct on ct.id=a.classtime_id
 WHERE attendance_date='".$days."' and student_user_id='".$student_details->user_id."' and ct.class_id='".$class_id."'");

 if(!empty($attendance_date_of_stu)){

	$table_row .= '<td>
	<div class="radio">
	<label>
	<input type="radio" '.($attendance_date_of_stu->is_present==1?'checked="checked"':'').' name="att_'.$days.'_'.$student_details->user_id.'" value="1" class="atten_check"> P 
	</label>
	</div>
	<div class="radio">
	<label>
	<input type="radio" '.($attendance_date_of_stu->is_present==2?'checked="checked"':'').' name="att_'.$days.'_'.$student_details->user_id.'" value="2" class="atten_check"> L 
	</label>
	</div>
	<div class="radio">
	<label>
	<input type="radio" '.($attendance_date_of_stu->is_present==0?'checked="checked"':'').' name="att_'.$days.'_'.$student_details->user_id.'" class="atten_check" value="0"> A 
	</label>
	</div>
	<input type="hidden" name="attendance['.$student_details->user_id.']['.$days.']" class="atten_hid" value="-1" />';

	$table_row .= '</td>';

 }else{

	$table_row .= '<td>
	<div class="radio">
	<label>
	<input type="radio" name="att_'.$days.'_'.$student_details->user_id.'" value="1" class="atten_check"> P 
	</label>
	</div>
	<div class="radio">
	<label>
	<input type="radio" name="att_'.$days.'_'.$student_details->user_id.'" value="2" class="atten_check"> L 
	</label>
	</div>
	<div class="radio">
	<label>
	<input type="radio" name="att_'.$days.'_'.$student_details->user_id.'" class="atten_check" value="0"> A 
	</label>
	</div>
	<input type="hidden" name="attendance['.$student_details->user_id.']['.$days.']" class="atten_hid" value="-1" />';

	$table_row .= '</td>';

 }

}

}
$table_row .= '</tr>';
}

$sheet = '<table class="table table-bordered" id="attendance_sheet"><thead><tr>'.$table_header.'</tr></thead>';
$sheet .= '<tbody>'.$table_row.'</tbody>';
$sheet .= '</table>';

$retAry['data_found'] = 1;

$retAry['sheet'] = $sheet;
echo json_encode($retAry);
exit;

}
//==========================DISPLAY TODAY'S ATTENDANCE=====================
elseif($_POST['action'] == 'get_group_todays_attendance'){
$retAry = array();
$group_id = $_POST['group'];
$class_id = $_POST['group_class'];
$d =  date('d');
$month =  date('m');
$year =  date('Y');
$holidayAllGrpCheck = $db->get_results("SELECT * FROM ss_holidays WHERE is_for_all_groups = 1 AND date_start <= '".($year."-".$month."-".$d)."' AND date_end >= '".($year."-".$month."-".$d)."' AND is_active = 1");
$holidaySpecGrpCheck = $db->get_results("SELECT * FROM ss_holidays h INNER JOIN ss_holiday_groups hg ON h.id = hg.holiday_id WHERE is_for_all_groups = 0 AND date_start <= '".($year."-".$month."-".$d)."' AND date_end >= '".($year."-".$month."-".$d)."' AND hg.group_id = '".$group_id."' AND h.is_active = 1");
if(!count((array)$holidayAllGrpCheck) && !count((array)$holidaySpecGrpCheck)){
	$group_stu = $db->get_results("select user_id, family_id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS student_name from ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_deleted = 0 AND u.is_active = 1 AND s.user_id in (select student_user_id from ss_studentgroupmap where latest = 1 and  class_id='".$class_id."' and group_id='".$group_id."') order by student_name asc");
	if(count((array)$group_stu)){

		$retAry['data_found'] = 1;
		foreach($group_stu as $stu){
			$family_info = $db->get_row("select * from ss_family where id = '".$stu->family_id."'");
			$phones = (trim($family_info->father_area_code)!=''?($family_info->father_area_code.'-'):'').internal_phone_check($family_info->father_phone);
			if($phones != ''){
				$phones = $phones.', '.(trim($family_info->mother_area_code)!=''?($family_info->mother_area_code.'-'):'').internal_phone_check($family_info->mother_phone);
			}else{
				$phones = $family_info->mother_area_code.'-'.internal_phone_check($family_info->mother_phone);
			}
			$time = mktime(12, 0, 0, $month, $d, $year);          
			if(date('Y-m-d') == date('Y-m-d',$time)){
				$student_group_map_id = $db->get_var("select id from ss_studentgroupmap where latest = 1 and 
				group_id='".$group_id."' and class_id = '".$class_id."' and student_user_id = '".$stu->user_id."' order by id desc");
				//REPLACE date('w') WITH 0 FOR TESTING
				$classtime_id = $db->get_var("select id from ss_classtime where class_id = '".$class_id."' and group_id = '".$group_id."' ");
				$atten_info = $db->get_row("select * from ss_attendance where student_user_id='".$stu->user_id."' 
				and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and
				attendance_date='".date('Y-m-d',$time)."' and student_group_map_id='".$student_group_map_id."' and classtime_id='".$classtime_id."'"); 
				if(count((array)$atten_info)){
					//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
					if(check_userrole_by_code('UT01')){	
						$retAry['show_submit_btn'] = 1;
						$retAry['show_homework_box'] = 1;
					}else{
						$retAry['show_submit_btn'] = 0;
						$retAry['show_homework_box'] = 1;
					}
					$att_val = $atten_info->is_present;
					if($atten_info->is_present == 0){
						$att_class = 'att_abse';
					}elseif($atten_info->is_present == 1){							
						$att_class = 'att_pres';
					}elseif($atten_info->is_present == 2){							
						$att_class = 'att_late';
					}
				}else{
					$retAry['show_homework_box'] = 0;
					$retAry['show_submit_btn'] = 1;
					$att_val = '0'; 
					$att_class = 'att_abse';
				}
				$table_row .= '<div class="col-md-4 col-sm-6">';
				$table_row .= '<div class="att_day '.$att_class.' atten_check" data-userid="'.$stu->user_id.'">'.$stu->student_name.'<br><small>'.rtrim($phones,', ').'</small></div>';
				$table_row .= '<input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d').']" class="atten_hid" value="'.$att_val.'" />';
				$table_row .= '</div>';
			}
		}
	}else{
		$retAry['data_found'] = 0;
	}
	$sheet .= $table_row;	
	if($retAry['data_found'] == 0){
		$sheet = '<div class="col-lg-12">Oops! No student found in selected group</div>';
	}else{
		$group_stu = $db->get_results("SELECT s.* FROM ss_student s INNER JOIN ss_studentgroupmap m ON s.user_id = m.student_user_id WHERE m.group_id = '".$group_id."' AND latest = 1");
		if(count((array)$group_stu)){
			$grp_students = "<option value='whole_group'>Whole Group</option>";
			foreach($group_stu as $gs){
				$grp_students .= "<option value='".$gs->user_id."'>".$gs->first_name." ".$gs->last_name."</option>";
			}
		}
	}
	$retAry['group_students'] = $grp_students;
}else{
	$retAry['data_found'] = 0;
	$holiday_reason = '';
	if(count((array)$holidayAllGrpCheck)){
		foreach($holidayAllGrpCheck as $holi){
			if($holiday_reason == ''){
				$holiday_reason = $holi->reason;
			}else{
				$holiday_reason = $holiday_reason.', '.$holi->reason;
			}
		}
	}elseif(count((array)$holidaySpecGrpCheck)){ 
		foreach($holidaySpecGrpCheck as $holi){
			if($holiday_reason == ''){
				$holiday_reason = $holi->reason;
			}else{
				$holiday_reason = $holiday_reason.', '.$holi->reason;
			}
		}
	}
	//$sheet ='<div class="label label-danger">Holiday: '.$holiday_reason."</div>";
	//$sheet ='<div class="label label-danger">There is no class today because of: '.$holiday_reason."</div>";
	//$sheet ='<div class="alert alert-default">There is no class today because of: '.$holiday_reason."</div>";
	//$sheet ='<div class="alert alert-danger">'.$holiday_reason.' : There is no class today</div>';
	//$sheet = '<h4>There is no class today because of '.$holiday_reason.'</h4>';
	//$sheet ='<div class="alert alert-default"><h4>There is no class today because of '.$holiday_reason.'</h4></div>';
	$sheet ='<div class="alert alert-default"><h5>'.$holiday_reason.' : There is no class today</h5></div>';
	//$sheet = 'Oops! There is class today. Reason is '.strtolower($holiday_reason);
}
$retAry['sheet'] = $sheet;	
echo json_encode($retAry);
exit;
}
//==========================DISPLAY ATTENDANCE OF FAMILY=====================
elseif($_POST['action'] == 'get_attendance_of_family'){ 
$retAry = array();
$group_id = $_POST['group'];
$class_id = $_POST['group_class'];
$period = explode('-',$_POST['period']);
$month =  $period[0];
$year =  $period[1];
//CHECK - IS CURRENT MONTH
if(date('m') == $month){
	$lastDateOfMonth = date('d');
}else{
	$lastDateOfMonth = date('t',strtotime($year.'-'.$month.'-01'));		
}
$begin = date("$year-$month-01");
$end = date("$year-$month-$lastDateOfMonth");


$school_days = $db->get_row("select school_opening_days from ss_client_settings where status = 1 ");
$serdays = unserialize($school_days->school_opening_days);
$days_array = [];
foreach($serdays as $row){
	$startDate = new DateTime($begin);
	$endDate = new DateTime($end);
	while ($startDate <= $endDate) {
		if ($startDate->format('w') == $row) {
				$days_array[] = $startDate->format('d');
			//array_push($days_array,$day);
		}
		$startDate->modify('+1 day');
	}
}
$table_header = '<table class="table table-bordered" id="attendance_sheet"><thead>';
/*for($d = 1; $d <= 31; $d++){
	$time = mktime(12, 0, 0, $month, $d, $year);          
	if (date('m', $time) == $month && date('Y-m-d',$time) <= date('Y-m-d')){      
		$table_header .= '<th>'.date('d D', $time).'</th>';
	}
}*/
//COMMENTED ON 13-MAR-2021
// $family_stu = $db->get_results("select user_id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS student_name 
// from ss_student s inner join ss_user u on s.user_id = u.id where family_id = '".$_SESSION['icksumm_uat_login_familyid']."'  AND u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
// and u.is_active = 1 group by user_id ");

//ADDED ON 13-MAR-2021
$family_stu = $db->get_results("select user_id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS student_name 
from ss_student s inner join ss_user u on s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
INNER JOIN ss_studentgroupmap m ON m.student_user_id = u.id 
where family_id = '".$_SESSION['icksumm_uat_login_familyid']."'  AND ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
and u.is_active = 1 AND m.class_id = '". $class_id."' AND m.group_id = '".$group_id."' AND u.is_deleted = 0 AND u.is_active = 1 group by user_id ");
if(count((array)$family_stu)){
	$retAry['data_found'] = 1;
	$table_row .= '<tr><td><strong>Date</strong></td>';	
	foreach($family_stu as $stu){
		$table_row .= '<td><strong>';
		$table_row .= $stu->student_name;
		$table_row .= '</strong></td>';
	}
	$table_row .= '</tr>';
for($d = 0; $d < $lastDateOfMonth; $d++){
	$ddadd = $d+1;
	$dd = str_pad($ddadd, 2, '0', STR_PAD_LEFT);
	$time = mktime(12, 0, 0, $month, $dd, $year);  
	if (date('m', $time) == $month){
		if(in_array($dd, $days_array)){
	// for($d = 1; $d <= $lastDateOfMonth; $d++){
	// 	$time = mktime(12, 0, 0, $month, $d, $year); 
	// 	if(date('D', $time) == 'Sun'){
			$table_row .= '<tr>';
			$table_row .= '<td>'.date('d D', $time).'</td>';
			if (date('m', $time) == $month && date('Y-m-d',$time) <= date('Y-m-d')){      
				foreach($family_stu as $stu){
					$table_row .= '<td>';

					$student_group_map_id = $db->get_var("select id from ss_studentgroupmap where latest = 1 
					AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and 
					group_id='".$group_id."' and class_id='".$class_id."' and student_user_id = '".$stu->user_id."'");

					//REPLACE date('w') WITH 0 FOR TESTING
					$classtime_id = $db->get_var("select id from ss_classtime where class_id = '".$class_id."' 
					AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and group_id = '".$group_id."'");

					$atten_info = $db->get_row("select * from ss_attendance where student_user_id='".$stu->user_id."' 
					and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
					and attendance_date='".date('Y-m-d',$time)."' and student_group_map_id='".$student_group_map_id."' and classtime_id='".$classtime_id."'"); 

					$holiday_info = $db->get_row("select * from ss_holidays where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and date_start <='".date('Y-m-d',$time)."' AND date_end >='".date('Y-m-d',$time)."' and is_active=1");

					
					if(!empty($holiday_info)){

						if($holiday_info->is_for_all_groups == 1){
							$table_row .= '<span class="label label-danger">'.$holiday_info->reason.'</span>';
						}else{

							$holiday_group_info = $db->get_row("select h.reason from ss_holiday_groups hg inner join ss_holidays h on h.id = hg.holiday_id where hg.group_id ='".$group_id."' and  hg.holiday_id = '".$holiday_info->id."' ");

							if(!empty($holiday_group_info)){
								$table_row .= '<span class="label label-danger">'.$holiday_group_info->reason.'</span>';
							}else{

								if(!empty($atten_info)){
									if($atten_info->is_present == 1){
										$table_row .= '<span class="label label-success">Present</span>';
									}elseif($atten_info->is_present == 0){
										$table_row .= '<span class="label label-danger">Absent</span>';
									}elseif($atten_info->is_present == 2){
									$table_row .= '<span class="label label-warning" style="background-color: #fff822;border-color: #fff822;color:#333;width: 54px;">Late</span>';
									}elseif($atten_info->is_present == -1){
										$table_row .= " ";
									}
								}else{
									$table_row .= "-";
								}

							}

						}

					}else{

						if(!empty($atten_info)){
							if($atten_info->is_present == 1){
								$table_row .= '<span class="label label-success">Present</span>';
							}elseif($atten_info->is_present == 0){
								$table_row .= '<span class="label label-danger">Absent</span>';
							}elseif($atten_info->is_present == 2){
							$table_row .= '<span class="label label-warning" style="background-color: #fff822;border-color: #fff822;color:#333;width: 54px;">Late</span>';
							}elseif($atten_info->is_present == -1){
								$table_row .= " ";
							}
						}else{
							$table_row .= "-";
						}

					}

					$table_row .= '</td>';
				}
			}
			$table_row .= '</tr>';
		}
	}
	}			
	$table_row .= '</thead></table>';
}else{
	$retAry['data_found'] = 0;
}
$sheet = '<table class="table table-bordered" id="attendance_sheet"><thead><tr>'.$table_header.'</tr></thead>';
$sheet .= '<tbody>'.$table_row.'</tbody>';
$sheet .= '</table>';
if($retAry['data_found'] == 0){
	$sheet .= '<div>No record found</div>';
}
$retAry['sheet'] = $sheet;
echo json_encode($retAry);
exit;
}
//==========================SAVE ATTENDANCE=====================
elseif($_POST['action'] == 'save_attendance'){
$group_id = $_POST['group_id'];
$class_id = $_POST['class_id'];
$classtime_id = $db->get_var("select id from ss_classtime where group_id='".$group_id."' and class_id = '".$class_id."' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
foreach($_POST['attendance'] as $key => $val){
	$userid = $key;
	$student_group_map_id = $db->get_var("select id from ss_studentgroupmap where latest = 1 and group_id='".$group_id."' and class_id='".$class_id."' and student_user_id = '".$userid."'");
	foreach($val as $inner_key => $inner_val){ 
		$attendance_date = $inner_key;
		$is_present = $inner_val;
		if($is_present != '-1'){
		$attendance_rs = $db->get_row("select * from ss_attendance where student_user_id='".$userid."' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'and DATE_FORMAT(attendance_date, '%Y-%m-%d') = '".$attendance_date."' and classtime_id='".$classtime_id."'");
		if(!empty($attendance_rs)){
			$sql_ret = $db->query("update ss_attendance set attendance_date='".$attendance_date."',	is_present='".$is_present."', updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id = '".$attendance_rs->id."'"); 
			
		}else{
			$sql_ret = $db->query("insert into ss_attendance set student_user_id='".$userid."',
			attendance_date='".$attendance_date."', student_group_map_id='".$student_group_map_id."',classtime_id='".$classtime_id."',
			session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',
			is_present='".$is_present."',created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			created_on='".date('Y-m-d H:i:s')."', updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			updated_on='".date('Y-m-d H:i:s')."'"); 
		}

		}
	}
}



if($sql_ret){
	echo json_encode(array('code' => "1",'msg' => 'Attendance saved successfully'));
	exit;
}else{
	echo json_encode(array('code' => "0",'msg' => 'Attendance not saved'));
	exit;
}
}

elseif($_POST['action'] == 'get_attendance_of_class_and_month_based'){
$retAry = array();
// $group_id = $_POST['group'];
$class_id = $_POST['group_class'];
$period = explode('-',$_POST['period']);
$get_month =  $period[0];
$get_year =  $period[1];
$begin = date("$get_year-$get_month-01");

$monthcount   = date('t', strtotime($get_month, $get_year));

$end = date("$get_year-$get_month-$monthcount");    

$school_days = $db->get_row("select school_opening_days from ss_client_settings where status = 1 order by id desc limit 1");
$serdays = unserialize($school_days->school_opening_days);
$days_array = [];
foreach($serdays as $row){

	$startDate = new DateTime($begin);

	$endDate = new DateTime($end);

	while ($startDate <= $endDate) {
		if ($startDate->format('w') == $row) {
				$days_array[] = $startDate->format('d');
			//array_push($days_array,$day);
		}
		$startDate->modify('+1 day');
	}
}
$table_header = '<th>Student</th>';
for($i = 0; $i < $monthcount; $i++){
	$ddadd = $i+1;
	$dd = str_pad($ddadd, 2, '0', STR_PAD_LEFT);
	$time = mktime(12, 0, 0, $get_month, $dd, $get_year);            
	if (date('m', $time) == $get_month){
		if(in_array($dd, $days_array)){	
			$table_header .= '<th>'.date ("M d D", strtotime("+$i day", strtotime($begin))).'</th>';
		}else{
			$table_header .= '<th class="rowhide">'.date ("M d D", strtotime("+$i day", strtotime($begin))).'</th>';
		}
	}
}
if(ONE_STUDENT_ONE_LEVEL == 1){
	$group_stu = $db->get_results("select user_id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS student_name 
	from ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
	WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_deleted = 0 AND s.user_id 
	in (select student_user_id from ss_studentgroupmap where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
	and latest = 1) order by student_name asc");
}else{
	$group_stu = $db->get_results("select user_id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS student_name 
	from ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
	WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_deleted = 0 AND s.user_id 
	in (select student_user_id from ss_studentgroupmap where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
	and latest = 1 and class_id='".$class_id."') order by student_name asc");
}
if(count((array)$group_stu)){
	$retAry['data_found'] = 1;
	foreach($group_stu as $stu)
	{
		$table_row .= '<tr><td>';
		if(check_userrole_by_code('UT01')){	
			$table_row .= '<a href="'.SITEURL.'student/student_edit.php?id='.$stu->user_id.'" target="_blank">'.$stu->student_name.'</a>';
		}else{
			$table_row .= '<a href="javascript:void(0)" class="viewdetail" data-studentname="'.$stu->student_name.'" data-userid="'.$stu->user_id.'">'.$stu->student_name.'</a>';
		}
		$table_row .= '</td>';
		for($i = 0; $i < $monthcount; $i++){
			$ddadd = $i+1;
			$dd = str_pad($ddadd, 2, '0', STR_PAD_LEFT);
			str_pad($value, 8, '0', STR_PAD_LEFT);
			$time = mktime(12, 0, 0, $get_month, $dd, $get_year);  
		
			if (date('m', $time) == $get_month){
				if(in_array($dd, $days_array)){				
					$table_row .= '<td>';
					$holidayAllGrpCheck = $db->get_results("SELECT * FROM ss_holidays WHERE is_for_all_groups = 1 
					AND date_start <= '".date('Y-m-d',$time)."' AND date_end >= '".date('Y-m-d',$time)."'");
					$holidaySpecGrpCheck = $db->get_results("SELECT * FROM ss_holidays h INNER JOIN ss_holiday_groups hg 
					ON h.id = hg.holiday_id WHERE is_for_all_groups = 0 AND date_start <= '".date('Y-m-d',$time)."' 
					AND date_end >= '".date('Y-m-d',$time)."' AND hg.group_id = '".$group_id."'");
					if(!count((array)$holidayAllGrpCheck) && !count((array)$holidaySpecGrpCheck)){
							if(ONE_STUDENT_ONE_LEVEL == 1){
								$student_group_map_id = $db->get_row("select sgm.id, g.group_name from ss_studentgroupmap sgm INNER JOIN ss_groups g ON g.id = sgm.group_id INNER JOIN ss_classtime ct ON ct.group_id = g.id where
								sgm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and sgm.latest = 1 and sgm.student_user_id = '".$stu->user_id."' ");
							}else{					
								$student_group_map_id = $db->get_row("select sgm.id, g.group_name from ss_studentgroupmap sgm INNER JOIN ss_groups g ON g.id = sgm.group_id INNER JOIN ss_classtime ct ON ct.group_id = g.id where
								sgm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and sgm.latest = 1 and sgm.class_id='".$class_id."' and sgm.student_user_id = '".$stu->user_id."' ");
							}
							
							// $classtime_id = $db->get_results("SELECT id FROM ss_classtime WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
							// and class_id = '".$class_id."' ORDER BY id DESC ");
							$classtime_id = $db->get_var("select id from ss_classtime where is_active = 1 and class_id = '".$class_id."' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
					
								$atten_info = $db->get_row("select * from ss_attendance where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
							AND student_user_id='".$stu->user_id."' 
							and attendance_date='".date('Y-m-d', $time)."' and classtime_id = '".$classtime_id."' ");
							
								if (!empty($atten_info) > 0) {
									if (check_userrole_by_code('UT01')) {
										if (!empty($student_group_map_id->group_name)) {
											$table_row .= '
										<div class="radio">
											<span class="label label-info">'.$student_group_map_id->group_name.'</span>
										</div>';
										} else {
											$table_row .= '
										<div class="radio">
										<span class="label label-info">'.$student_group_map_id->group_name.'</span>
										</div>';
										}
										$table_row .='
									<div class="radio">
									<label>
									<input type="radio" '.($atten_info->is_present==1?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" value="1" class="atten_check"> P 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" '.($atten_info->is_present==2?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" value="2" class="atten_check"> L 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" '.($atten_info->is_present==0?'checked="checked"':'').' name="att_'.$time.'_'.$stu->user_id.'" class="atten_check" value="0"> A 
									</label>
									</div>	
									<input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d', $time).']" class="atten_hid" value="'.$atten_info->is_present.'" />';
									
									} elseif (check_userrole_by_code('UT02') || check_userrole_by_code('UT04')) {
										//INSIDE - STAFF (SHEIKH/ACCOUNTANT)

										if (!empty($student_group_map_id->group_name)) {
											$table_row .= '<span class="label label-info" style="margin-bottom:5px;">'.$student_group_map_id->group_name.'</span>';
										}

										if ($atten_info->is_present == 1) {
											$table_row .= '<span class="label att_pres">Present</span>';
										} elseif ($atten_info->is_present == 2) {
											$table_row .= '<span class="label att_late">Late</span>';
										} elseif ($atten_info->is_present == 0) {
											$table_row .= '<span class="label att_abse">Absent</span>';
										} elseif ($atten_info->is_present == '-1') {
											$table_row .= '<span class="label "></span>';
										}
									}
								} else {
									if (check_userrole_by_code('UT01')) {
										if (!empty($student_group_map_id->group_name)) {
											$table_row .= '
										<div class="radio">
											<span class="label label-info">'.$student_group_map_id->group_name.'</span>
										</div>';
										} else {
											$table_row .= '
										<div class="radio">
										<span class="label label-info">'.$student_group_map_id->group_name.'</span>
										</div>';
										}
										$table_row .='
									<div class="radio">
									<label>
									<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" value="1" class="atten_check"> P 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" value="2" class="atten_check"> L 
									</label>
									</div>
									<div class="radio">
									<label>
									<input type="radio" name="att_'.$time.'_'.$stu->user_id.'" class="atten_check" value="0"> A
									</label>
									</div>
									<input type="hidden" name="attendance['.$stu->user_id.']['.date('Y-m-d', $time).']" class="atten_hid" value="-1" />';
									} elseif (check_userrole_by_code('UT02') || check_userrole_by_code('UT04')) {
										$table_row .= '';
									}
								}
					}else{
						$holiday_reason = '';
						if(count((array)$holidayAllGrpCheck)){
							foreach($holidayAllGrpCheck as $holi){
								if($holiday_reason == ''){
									$holiday_reason = '<span class="label label-danger">'.$holi->reason.'</span>';
								}else{
									$holiday_reason = $holiday_reason.', <span class="label label-danger">'.$holi->reason.'</span>';
								}
							}
						}elseif(count((array)$holidaySpecGrpCheck)){ 
							foreach($holidaySpecGrpCheck as $holi){
								if($holiday_reason == ''){
									$holiday_reason = '<span class="label label-danger">'.$holi->reason.'</span>';
								}else{
									$holiday_reason = $holiday_reason.', <span class="label label-danger">'.$holi->reason.'</span>';
								}
							}
						}
						$table_row .=  $holiday_reason;
					}
					$table_row .= '</td>';
				}else{
					$table_row .= '<td class="rowhide"> </td>';
				}
			}
		}
		
		$table_row .= '</tr>';
	}
}else{
	$retAry['data_found'] = 0;
}
$sheet = '<table class="table table-bordered" id="attendance_sheet"><thead><tr>'.$table_header.'</tr></thead>';
$sheet .= '<tbody>'.$table_row.'</tbody>';
$sheet .= '</table>';
if($retAry['data_found'] == 0){
	$sheet .= '<div>No record found</div>';
}
$retAry['sheet'] = $sheet;
echo json_encode($retAry);
exit;
}
//==========================SAVE ATTENDANCE CLASS=====================
elseif($_POST['action'] == 'save_attendance_class_based'){
$class_id = $_POST['class_id'];
$db->query('BEGIN');
$classtime_id = $db->get_var("select id from ss_classtime where is_active = 1 and class_id = '".$class_id."' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
foreach($_POST['attendance'] as $key => $val){
	$userid = $key;
	$student_group_map_id = $db->get_var("select id from ss_studentgroupmap where latest = 1 and class_id='".$class_id."' and student_user_id = '".$userid."'");
	foreach($val as $inner_key => $inner_val){ 
		$attendance_date = $inner_key;
		$is_present = $inner_val;
		if($is_present != '-1'){
		$attendance_rs = $db->get_row("select * from ss_attendance where student_user_id='".$userid."' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
		and DATE_FORMAT(attendance_date, '%Y-%m-%d') = '".$attendance_date."' and classtime_id='".$classtime_id."'");
		if(!empty($attendance_rs)){

				$sql_ret = $db->query("update ss_attendance set attendance_date='".$attendance_date."',				
				is_present='".$is_present."', updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
				updated_on='".date('Y-m-d H:i:s')."' where id = '".$attendance_rs->id."'"); 
			
		}else{

			$sql_ret = $db->query("insert into ss_attendance set student_user_id='".$userid."',
			attendance_date='".$attendance_date."', student_group_map_id='".$student_group_map_id."',classtime_id='".$classtime_id."',
			session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',
			is_present='".$is_present."',created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			created_on='".date('Y-m-d H:i:s')."', updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			updated_on='".date('Y-m-d H:i:s')."'"); 
		}

		}
	}
}
if ($sql_ret && $db->query('COMMIT') !== false) {
	echo json_encode(array('code' => "1",'msg' => 'Attendance saved successfully'));
	exit;
}else{
	$db->query('ROLLBACK');
	echo json_encode(array('code' => "0",'msg' => 'Attendance not saved'));
	exit;
}
}

elseif($_GET['action'] == 'get_working_days'){

$genral_info = $db->get_row("select school_name, school_opening_date, school_closing_date, school_opening_days, contact_admin_email, contact_organisation_email, 
contact_phone, contact_address, contact_city, contact_state_id, contact_zipcode, school_logo, school_header_logo, fees_monthly, one_student_one_lavel,
center_short_name from ss_client_settings where status = 1");
if (isset($genral_info->school_opening_days)) {
  $school_opening_days = unserialize($genral_info->school_opening_days);
}
$isfullweek = count((array)$school_opening_days);
echo $isfullweek;
}

?>