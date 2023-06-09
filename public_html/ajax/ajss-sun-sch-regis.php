<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================LIST ALL REGISTRATION FOR ADMIN=====================
if($_GET['action'] == 'list_sun_sch_regis'){
	$finalAry = array();
	
	$sql = "SELECT id,'' as students_name, CONCAT(father_first_name,' ',COALESCE(father_last_name,'')) AS father_name,
	CONCAT(mother_first_name,' ',COALESCE(mother_last_name,'')) AS mother_name, created_on FROM ss_sunday_school_reg ";
		
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	if(check_userrole_by_code('UT01')){	
		$all_reg = $db->get_results($sql,ARRAY_A);
	}

	for($i=0; $i<count($all_reg); $i++){
		$students_name = '';
		
		$students = $db->get_results("select * from ss_sunday_sch_req_child where sunday_school_reg_id = '".$all_reg[$i]['id']."'");
		foreach($students as $stu){
			$students_name .= $stu->first_name.' '.$stu->last_name.", ";
		}
		
		$check_image = $db->get_var("select check_image from ss_sunday_sch_payment where sunday_sch_req_id = '".$all_reg[$i]['id']."'");
		$all_reg[$i]['check_image'] = trim($check_image);
		
		$all_reg[$i]['students_name'] = ucwords(rtrim($students_name,', '));
		$all_reg[$i]['created_on'] = date('d/m/Y h:i a',$all_reg[$i]['created_on']);
	}
	
	$finalAry['data'] = $all_reg;
	echo json_encode($finalAry);
	exit;
}

//==========================FETCH CHEQUE/CHECK IMAGE=====================
elseif($_POST['action'] == 'check_image'){
	$sunday_school_reg_id = $_POST['regid'];
	$check_image = $db->get_var("select check_image from ss_sunday_sch_payment where sunday_sch_req_id = '".$sunday_school_reg_id."'");
	echo $check_image;
}
?>