<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}  

//==========================LIST ALL STAFF FOR ADMIN=====================
if ($_GET['action'] == 'list_all_student') {     
    $finalAry = array();
    if ($_GET['deleted_record_student'] == 'show_deleted_stu') {
        $sql = "SELECT s.admission_no, s.user_id,
        CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student_name, s.allergies , s.dob,
        (CASE s.gender WHEN 'm' THEN 'Male' ELSE 'Female' END) AS gender, s.school_grade, f.father_phone, f.father_area_code, f.mother_phone,
        f.mother_area_code,s.qur_sch_stu_user_id,
        CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father_name,
        (CASE WHEN u.is_deleted = 1 THEN 'Delete' END) AS status, u.is_deleted FROM ss_user u
        INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
        INNER JOIN ss_family f ON s.family_id = f.id where u.is_deleted = 1 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ";
    } else {
        $sql = "SELECT s.admission_no, s.user_id,
        CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student_name, s.allergies , s.dob,
        (CASE s.gender WHEN 'm' THEN 'Male' ELSE 'Female' END) AS gender, s.school_grade, f.father_phone, f.father_area_code, f.mother_phone,
        f.mother_area_code,s.qur_sch_stu_user_id,
        CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father_name,
        (CASE WHEN u.is_active=1 THEN 'Active' WHEN u.is_active=2 THEN 'Hold' ELSE 'Inactive' END) AS status, u.is_deleted FROM ss_user u
        INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
        INNER JOIN ss_family f ON s.family_id = f.id where u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ";
    }



    if (check_userrole_by_code('UT01') || check_userrole_by_code('UT04')) { 
        $all_students = $db->get_results($sql, ARRAY_A);
    } else {
        //COMMENTED ON 19-AUG-2018
        //$sql = $sql." and s.user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 and group_id IN (".implode(',',$_SESSION['icksumm_uat_assigned_groups'])."))";
        //ADDED ON 19-AUG-2018
        $assigned_group_str = $db->get_var("SELECT DISTINCT GROUP_CONCAT(group_id) FROM ss_staffclasstimemap m INNER JOIN ss_classtime c ON c.id = m.classtime_id WHERE m.active = 1 AND c.is_active = 1 AND staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
        $sql = $sql . " and s.user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 and group_id IN (" . $assigned_group_str . "))";
        $all_students = $db->get_results($sql, ARRAY_A);
    }

    for ($i = 0; $i < count((array)$all_students); $i++) {

        if(!empty($all_students[$i]['father_phone'])){
            if (strpos($all_students[$i]['father_phone'],"-") == false) {	
                $number=$all_students[$i]['father_phone'];
                $formatted_number = "$number[0]$number[1]$number[2]-$number[3]$number[4]$number[5]-$number[6]$number[7]$number[8]$number[9]";
                $all_students[$i]['father_phone']= $formatted_number;
            }
        }

      


        // $checkForSunSch = $db->get_var("select COUNT(1) from erp_user where id = '" . $all_students[$i]['qur_sch_stu_user_id'] . "' and is_active = 1 and is_deleted = 0");
       
        // if ($checkForSunSch) {
        //     $all_students[$i]['student_name'] = '<span class="label label-success">Q</span> ' . $all_students[$i]['student_name'];
        // }
    
        /*$group = $db->get_var("select group_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id where m.latest = 1 and student_user_id='".$all_students[$i]['user_id']."' order by m.id desc limit 1");*/
        // $group = $db->get_results("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='".$all_students[$i]['user_id']."' ORDER BY m.id ASC");
        
        $stugroup = $db->get_results("select CONCAT(m.class_id,'_',COALESCE(m.group_id,'')) AS class_group_id, m.class_id, m.group_id from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join ss_classes s on m.class_id = s.id where m.latest = 1 and g.is_active=1 and m.student_user_id='" . $all_students[$i]['user_id'] . "' and s.is_active = 1 and g.is_active = 1 and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        // echo "<pre>";
        // print_r($stugroup);
        // die;
        $class = $db->get_results("SELECT id FROM ss_classes WHERE is_active = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        //  echo "<pre>";
        // print_r($class);
        // die;
        
        $class_group_id = "";
        $assign_class_array = [];
        $class_array = [];
        foreach ($stugroup as $row) {
            foreach ($class as $cls){
                    $class_array[$cls->id] = $cls->id;
            }
            $assign_class_array[] = $row->class_id;
            $class_group_id .= $row->class_group_id . ",";
        }

        $result_value = array_diff($class_array,$assign_class_array);

        $all_students[$i]['class'] = (count((array)$result_value) > 0)?1:"";

        if (trim($all_students[$i]['father_area_code']) != '') {
            $all_students[$i]['primary_phone'] = $all_students[$i]['father_area_code'] . '-' . internal_phone_check($all_students[$i]['father_phone']);
        } else {
            $all_students[$i]['primary_phone'] = internal_phone_check($all_students[$i]['father_phone']);
        }
        if (trim($all_students[$i]['mother_area_code']) != '') {
            $all_students[$i]['secondary_phone'] = $all_students[$i]['mother_area_code'] . '-' . internal_phone_check($all_students[$i]['mother_phone']);
        } else {
            $all_students[$i]['secondary_phone'] = internal_phone_check($all_students[$i]['mother_phone']);
        }
        $dob = date('Y-m-d', strtotime($all_students[$i]['dob']));
        $from = new DateTime($dob);
        $to = new DateTime('today');
        $stu_age = $from->diff($to)->y;
        $age = sprintf("%02d", $stu_age);
        $all_students[$i]['dob'] = $age;
        $grouplink = '<a href="javascript:void(0)" data-stuid=' . $all_students[$i]['user_id'] . '  data-stuname="' . $all_students[$i]['student_name'] . '"  class="stugroups">View Group</a>';
        if ($all_students[$i]['status'] == 'Delete') {
            $all_students[$i]['status'] = "<div class='text-danger'>Deleted</div>";
        } else {
            $all_students[$i]['status'] = $all_students[$i]['status'];
        }
        //$all_students[$i]['group'] = $group;
        //$all_students[$i]['group'] = $grouplink;
        $all_students[$i]['stugroups'] = rtrim($class_group_id, ',');
        $all_students[$i]['group'] = $grouplink;
    }

    $finalAry['data'] = $all_students;
    echo json_encode($finalAry);
    exit;
} elseif ($_POST['action'] == 'student_restore') {
    if (isset($_POST['stu_userid'])) {
        $rec = $db->query("update ss_user set is_deleted=0, updated_on='" . date("Y-m-d H:i:s") . "' where id='" . $_POST['stu_userid'] . "'");
        if ($rec > 0) {
            echo json_encode(array('code' => "1", 'msg' => 'Student retore successfully'));
            exit;
        } else {
            echo json_encode(array('code' => "0", 'msg' => 'Error: Student restoration failed', '_errpos' => 1));
            exit;
        }
    } else {
        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => 2));
        exit;
    }
}
//==========================GET STUDENTS OF A GROUP=====================
// elseif ($_POST['action'] == 'student_of_group') {

//     $group_id = $_POST['group_id'];
//     $class_id = $_POST['class_id'];
//     if ($group_id == "") {
//         $retVal = '<option value="">Select</option>';
//         //}elseif($group_id == "all_groups"){
//         //    $retVal = '<option value="all_students">Parents Of All Students Of All Groups</option>';
//     } else {

//         $students = $db->get_results("SELECT s.first_name, s.middle_name, s.last_name, s.user_id,  f.user_id as family_user_id  FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id
// 	INNER JOIN ss_family f ON f.id = s.family_id
// 	INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
// 	WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
// 	AND s.user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group_id . "'
// 	AND class_id= '" . $class_id . "') order by s.first_name,s.last_name");
//         // $retVal = '<option value="">Select</option>';
//         if (count((array)$students) > 0) {
//             $retVal .= '<option value="whole_group">ALL STUDENTS OF GROUP</option>';
//         }
//         foreach ($students as $stu) {
//             $retVal .= '<option value="' . $stu->user_id . '">' . $stu->first_name . ' ' . trim($stu->middle_name . ' ' . $stu->last_name) . '</option>';
//         }
//     }
//     echo json_encode(array('code' => 1, 'optionVal' => $retVal));
//     exit;
// }
elseif ($_POST['action'] == 'student_of_group') {

    $group_id = $_POST['group_id'];
    $class_id = $_POST['class_id'];
    if ($group_id == "") {
        $retVal = '<option value="">Select</option>';
        //}elseif($group_id == "all_groups"){
        //    $retVal = '<option value="all_students">Parents Of All Students Of All Groups</option>';
    } else {
        $students = $db->get_results("SELECT DISTINCT sgm.student_user_id, f.primary_email, f.secondary_email, 
                    CONCAT(s.first_name,' ',s.last_name) AS student_name, s.family_id,
                    g.group_name, c.class_name FROM ss_student s 
                    INNER JOIN ss_studentgroupmap sgm ON s.`user_id` = sgm.student_user_id 
                    INNER JOIN ss_user u ON u.id = sgm.student_user_id 
                    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
                    INNER JOIN ss_family f ON s.family_id = f.id 
                    INNER JOIN ss_groups g ON g.id = sgm.group_id 
                    INNER JOIN `ss_classtime` ct ON ct.`group_id` = g.`id`
                    INNER JOIN ss_classes c ON c.`id` = ct.class_id 
                    WHERE sgm.latest = 1 
                    AND sgm.group_id = '" . $group_id . "' AND ct.class_id = '" . $class_id . "' AND u.is_active = 1 AND u.is_deleted = 0 
                    AND g.is_active = 1 
                    AND g.is_deleted = 0 AND c.is_active = 1 ORDER BY s.`first_name` ASC ");

        // $retVal = '<option value="">Select</option>';
        if (count((array)$students) > 0) {
            $retVal .= '<option value="whole_group">ALL STUDENTS OF GROUP</option>';
        }else{
            $retVal .= '<option value="whole_group">No data found</option>';  
        }
        foreach ($students as $stu) {
            $retVal .= '<option value="' . $stu->student_user_id . '">' . $stu->student_name .  '</option>';
        }
    }
   
    echo json_encode(array('code' => 1, 'optionVal' => $retVal));
    exit;
}
//==========================STUDENT VIEW ONLY INFO=====================
elseif ($_POST['action'] == 'view_student_detail') {
    $userid = $_POST['userid'];
    $student = $db->get_row("SELECT s.user_id,s.admission_no,s.gender,s.allergies,s.school_grade,s.birth_place,s.blood_group,u.username,u.email,
s.phone,s.mobile,s.family_id,
CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student_name,
(CASE s.admission_date WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(s.admission_date,'%m/%d/%Y') END) AS admission_date,
(CASE s.dob WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(s.dob,'%m/%d/%Y') END) AS dob,
(CASE WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status, s.comments
FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id
where ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and s.user_id='" . $userid . "' ");
    $group = $db->get_var("select group_name from ss_groups
where id = (select group_id from ss_studentgroupmap where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
AND student_user_id='" . $student->user_id . "' and latest = 1)");
    $family_info = $db->get_row("select * from ss_family where id = '" . $student->family_id . "'");

    if(!empty($family_info->father_phone)){
        if (strpos($family_info->father_phone,"-") == false) {	
        $number=$family_info->father_phone;
        $formatted_number = "$number[0]$number[1]$number[2]-$number[3]$number[4]$number[5]-$number[6]$number[7]$number[8]$number[9]";
        $family_info->father_phone= $formatted_number;
        }
        }




    $primary_phone = (trim($family_info->father_area_code) != '' ? ($family_info->father_area_code . '-') : '') . internal_phone_check($family_info->father_phone);
    $secondary_phone = (trim($family_info->mother_area_code) != '' ? ($family_info->mother_area_code . '-') : '') . internal_phone_check($family_info->mother_phone);

    if ($family_info->primary_contact == 'Father') {
        $primary_contact = '1st Parent';
    } elseif ($family_info->primary_contact == 'Mother') {
        $primary_contact = '2nd Parent';
    } else {
        $primary_contact = 'a';
    }
    // my_date_changer($student->dob);
    $dob = my_date_changer($student->dob);
    $from = new DateTime($dob);
    $to = new DateTime('today');
    $stu_age = $from->diff($to)->y;
    $age = $stu_age . ' Yrs';
    $get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
    if ($get_general_info == 0) {
        $classes = $db->get_results("select c.id, c.class_name from ss_classes c where c.is_active= 1 and c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'  order by c.disp_order ");

 //        $stugroupclass = $db->get_results("select g.group_name,s.class_name from ss_groups g
	// inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
	// and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and m.latest = 1 and m.student_user_id='" . $student->user_id . "'");
    } else {
        $stugroupclass = $db->get_row("select g.group_name from ss_groups g
	inner join ss_studentgroupmap m on g.id = m.group_id  where g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and
	m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and m.latest = 1 and m.student_user_id='" . $student->user_id . "'");
    }
    if (check_userrole_by_code('UT01')) {
        $retStr = '<a href="' . SITEURL . 'student/student_edit?id=' . $userid . '" style="float: right;" >Edit</a>';
    }

    $retStr .= '<legend class="text-semibold">Student Information</legend>
		<div class="row">
			<div class="col-md-4 col-md-4">
				<label>Student Name:</label>' . $student->student_name . '
			</div>
			<div class="col-md-4 col-md-4">
			<label>Gender</label>' . ($student->gender == 'm' ? 'Male' : 'Female') . '
			</div>
			<div class="col-md-4 col-md-4">
			<label>Status: </label>' . $student->status . '
			</div>
		</div>
		<div class="row">
			<div class="col-md-4 col-md-4">
			<label>Grade: </label>' . $student->school_grade . '
			</div>
			<div class="col-md-4 col-md-4">
				<label>Date of Birth:</label>' . my_date_changer($student->dob) . '
			</div>
			<div class="col-md-4 col-md-4">
				<label>Age:</label>' . $age . '
			</div>
		</div>

			<div class="row">
			<div class="col-md-4 col-md-6">
				<label>Admission Date:</label>' . my_date_changer($student->admission_date). '
			</div>';
    if (!empty($student->allergies)) {
        $retStr .= '<div class="col-md-4 col-md-6">
				<label>Allergies:</label>' . $student->allergies . '
			</div>';
    }
    $retStr .= '</div>';
    $i = 1;
    if ($get_general_info == 0) {
        $retStr .= '<br><legend class="text-semibold">Group &amp; Classes</legend><div class="row">';
        foreach ($classes as $row) {

        
               $stugroupclass = $db->get_row("select g.group_name,s.class_name from ss_groups g
     inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and m.latest = 1 and m.student_user_id='" . $student->user_id . "' and m.class_id ='".$row->id."' ");
               if(isset($stugroupclass) && !empty($stugroupclass)){
                $group_name = $stugroupclass->group_name;
               }else{
                $group_name = "Unassigned";
               }
            $retStr .= '
				<div class="col-md-6">
					<label>' . $i . ') Class (Group) :</label>
					<span>' . $row->class_name . '(' . $group_name. ')</span>
				</div>';
            $retStr .= '';
            $i++;
        }
    } else {
        $retStr .= '<br><legend class="text-semibold">Group</legend><div class="row">';
        $retStr .= '
				<div class="col-md-6">
					<label>Group :</label>
					<span>' . $group_name . '</span>
				</div>';
    }
    $retStr .= '</div><br> <legend class="text-semibold">Contact Information</legend>
		<div class="row">
			<div class="col-md-4 col-sm-5">
				<label>Primary Contact:</label>' . $primary_contact . '
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<label>1st Parent Name:</label>' . $family_info->father_first_name . ' ' . $family_info->father_last_name . '
			</div>
			<div class="col-md-3">
				<label>Phone:</label>' . internal_phone_check($family_info->father_phone) . '
			</div>
			<div class="col-md-4">
				<label>Email:</label>' . $family_info->primary_email . '
			</div>
		</div>';
    if (!empty($family_info->mother_first_name)) {
        $retStr .= '<div class="row">
			<div class="col-md-5">
				<label>2nd Parent Name:</label>' . $family_info->mother_first_name . ' ' . $family_info->mother_last_name . '
			</div>
			<div class="col-md-3">
				<label>Phone:</label>' . internal_phone_check($family_info->mother_phone) . '
			</div>
			<div class="col-md-4">
				<label>Email:</label>' . $family_info->secondary_email . '
			</div>
		</div>
		';
    }
    if (!empty($student->comments)) {
        $retStr .= '<div class="row">
			<div class="col-md-12">
				<label>Comments:</label>' . $student->comments . '
			</div>
		</div>
		';
    }
    echo $retStr;
    exit;
}
//==========================EDIT PAYMENT CREDENTIALS=====================
elseif ($_POST['action'] == 'edit_payment_credentials') {
    $paymentcred_id = $_POST['paymentcred_id'];
    $paycred = $db->get_row("select * from ss_paymentcredentials where id='" . $paymentcred_id . "'");
    $db->query('BEGIN');
    $sql = "insert into ss_paymentcredentials_backup set paymentcredentials_id='" . $paymentcred_id . "',
family_id='" . $paycred->family_id . "',credit_card_type='" . $paycred->credit_card_type . "',credit_card_no='" . $paycred->credit_card_no . "',
credit_card_exp='" . $paycred->credit_card_exp . "',postal_code='" . $paycred->postal_code . "',bank_acc_no='" . $paycred->bank_acc_no . "',
routing_no='" . $paycred->routing_no . "',created_by_user_id='" . $paycred->created_by_user_id . "',created_on='" . $paycred->created_on . "',
changed_by_userd_id='" . $_SESSION['icksumm_uat_login_userid'] . "',changed_on='" . date('Y-m-d H:i:s') . "'";
    $sql_ret = $db->query($sql);
    if ($sql_ret) {
        if ($_POST['payment_method'] == 'credit_card') {
            $credit_card_type = base64_encode($_POST['credit_card_type']);
            if (isset($_POST['edit_cc'])) {
                $credit_card_no = base64_encode($_POST['credit_card_no']);
            } else {
                $credit_card_no = $paycred->credit_card_no;
            }
            $credit_card_exp = base64_encode($_POST['credit_card_exp_month'] . '-' . $_POST['credit_card_exp_year']);
            $postal_code = base64_encode($_POST['postal_code']);
            $bank_acc_no = '';
            $routing_no = '';
        } else {
            $credit_card_type = '';
            $credit_card_no = '';
            $credit_card_exp = '';
            $postal_code = '';
            $bank_acc_no = base64_encode($_POST['bank_acc_no']);
            $routing_no = base64_encode($_POST['routing_no']);
        }
        $sql_ret = $db->query("update ss_paymentcredentials set credit_card_type='" . $credit_card_type . "',credit_card_no='" . $credit_card_no . "',
	credit_card_exp='" . $credit_card_exp . "',postal_code='" . $postal_code . "',bank_acc_no='" . $bank_acc_no . "',
	routing_no='" . $routing_no . "',created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',created_on='" . date('Y-m-d H:i:s') . "'
	where id='" . $paymentcred_id . "'");
        if ($sql_ret && $db->query('COMMIT') !== false) {
            echo json_encode(array('code' => "1", 'msg' => 'Payment credentials successfully'));
            exit;
        } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1'));
            exit;
        }
    } else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '2'));
        exit;
    }
}





//==========================EDIT STUDENT=====================
elseif ($_POST['action'] == 'edit_student') {

try{
    $user_id = $_POST['user_id'];
    $classes = $_POST['class'];
    $totalClass_count = count((array)$classes);
    $student_data = $db->get_row("select s.family_id,u.is_active,u.is_deleted,f.father_first_name,f.father_last_name,f.primary_email  from ss_student as s  INNER JOIN ss_user as u ON u.id = s.user_id INNER JOIN ss_family as f ON f.id = s.family_id where s.user_id = '" . $user_id . "'");
    $family_id = $student_data->family_id;
    $db->query('BEGIN');

	$family = get_family_schedule_payment($family_id);
	$schedule_payment_cron = get_schedule_payment_cron($family_id);
    $family_payment_on_excution_check = get_payment_on_excution($family_id);
    if(count((array)$family_payment_on_excution_check) == 0){
    /* $admissionNoCheck = $db->get_row("select * from ss_student where admission_no='".trim($_POST['admission_no'])."' and user_id <> '".$_POST['user_id']."'");
    if(count((array)$admissionNoCheck) == 0){*/
    $queryCount = 0;
    foreach ($classes as $class_id) {
        $group_id = $_POST["group_id" . $class_id];
        
        $sql_query = $db->get_row("select * from ss_studentgroupmap where student_user_id = '" . $user_id . "' AND group_id='" . $group_id . "'
						AND class_id = '" . $class_id . "' AND latest = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        if (!empty($sql_query)) {
            $queryCount = $queryCount + 1;
        }

         //check student assign to closed group 
        $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
        $groupIsOpen = $group_details->is_regis_open;
        $groupName = $group_details->group_name;

        if(empty($sql_query) && $groupIsOpen==0){
            echo json_encode(array('code' => "0", 'msg' => 'Error: Registration closed in ' . $group_details->group_name));
            exit;
        }
       //end 
    }


    if ($totalClass_count != $queryCount) {
        foreach ($classes as $class_id) {
            $group_id = $_POST["group_id" . $class_id];
            $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
            $groupMaxLimit = $group_details->max_limit;
            $group_name = $group_details->group_name;
            $groupCurStrength = $db->get_results("select * from ss_studentgroupmap where group_id = '" . $group_id . "'
						AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and latest = 1 group by student_user_id");
            $stuCurGroup = $db->get_var("select group_id from ss_studentgroupmap where student_user_id = '" . $user_id . "' and latest = 1
						AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            $querystu = $db->get_row("select * from ss_studentgroupmap where student_user_id = '" . $user_id . "' AND group_id='" . $group_id . "'
						AND class_id = '" . $class_id . "' AND latest = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            if (empty($querystu)) {
                if (count((array)$groupCurStrength) <= $groupMaxLimit || $stuCurGroup == $group_id) {
                    $sql_ret = $db->query("update ss_studentgroupmap set latest='0',
						updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'
						where student_user_id = '" . $user_id . "' AND class_id = '" . $class_id . "' AND latest=1 ");
                    $sql_ret = $db->query("insert into ss_studentgroupmap set student_user_id='" . $user_id . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
						group_id='" . $group_id . "', class_id = '" . $class_id . "', latest=1, created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
						created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
						updated_on='" . date('Y-m-d H:i:s') . "'");
                } else {
                    $db->query('ROLLBACK');
                    echo json_encode(array('code' => "0", 'msg' => 'Error: Group ' . $group_name . ' has reached maximum limit', '_errpos' => '10'));
                    exit;
                }
            }
        }
    }
    /*if($totalClass_count == $queryCount) {*/
    if ($_POST['status'] == 'delete_soft') {
        $is_deleted = 1;
        $is_active = 1;
    } elseif ($_POST['status'] == 'active') {
        $is_active = 1;
        $is_deleted = 0;
    } elseif ($_POST['status'] == 'hold') {
        $is_active = 2;
        $is_deleted = 0;
    } else {
        $is_active = 0;
        $is_deleted = 0;
    }

    $sql_ret = $db->query("update ss_user set email='" . trim($db->escape($_POST['email'])) . "',is_deleted='" . $is_deleted . "',
						is_active='" . $is_active . "',updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $user_id . "'");

    // if(empty($_POST['fees_discount_id'])){
    // $sql_ret = $db->query("insert into ss_student_feesdiscounts set
    //         updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', latest=1, fees_discount_id = '".$_POST['fees_discount_id']."', updated_on='".date('Y-m-d H:i:s')."' where student_user_id = '".$user_id."' ");
    // }
    if (trim($_POST['password']) != '') {
        $db->query("update ss_user set password='" . md5(trim($_POST['password'])) . "' where id = '" . $user_id . "'");
    }

    $sql_ret = $db->query("update ss_student set admission_no='" . trim($db->escape($_POST['admission_no'])) . "',
						first_name='" . trim($db->escape($_POST['first_name'])) . "', middle_name='" . trim($db->escape($_POST['middle_name'])) . "',
						last_name='" . trim($db->escape($_POST['last_name'])) . "',gender='" . trim($db->escape($_POST['gender'])) . "',
						dob='" . trim($db->escape($_POST['dob_submit'])) . "',phone='" . trim($db->escape($_POST['phone'])) . "', school_grade='" . trim($db->escape($_POST['child_grade'])) . "', allergies='" . trim($db->escape($_POST['child_allergies'])) . "',
						mobile='" . trim($db->escape($_POST['mobile'])) . "',blood_group='" . trim($db->escape($_POST['blood_group'])) . "',
						birth_place='" . trim($db->escape($_POST['birth_place'])) . "',
						admission_date='" . trim($db->escape($_POST['admission_date_submit'])) . "', comments='" . $db->escape($_POST['comments']) . "',
						updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',updated_on='" . date('Y-m-d H:i:s') . "' where user_id='" . $user_id . "'");
    
    
        //old discounts
        $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id
                                where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1 AND d.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $fees_discount_ids = [];
        $old_discountDetailes = "";
        foreach ($old_discountFeesData as $value) {
            if ($value->discount_unit == 'p') {
                $doller = '';
                $percent = '%';
            } else {
                $doller = '$';
                $percent = '';
            }
            $fees_discount_ids[] = $value->fees_discount_id;
            $amount_val = $value->discount_percent + 0;
            $old_discountDetailes .= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
        }

    if (count((array)$_POST['fees_discount_id']) > 0) {

        $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "' AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        
        foreach ($_POST['fees_discount_id'] as $feesdiscount) {
            $sql_ret = $db->query("insert into ss_student_feesdiscounts set
								udated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
								fees_discount_id = '" . $feesdiscount . "',  session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', updated_on='" . date('Y-m-d H:i:s') . "', created_on='" . date('Y-m-d H:i:s') . "',
								student_user_id = '" . $user_id . "' ");
        }

        //new discounts and fees
        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id
								where m.student_user_id='" . $user_id . "' and m.latest = 1 AND g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $groups = [];
        foreach ($user_groups as $group) {
            $groups[] = $group->id;
        }

        $group_ids = implode(",", $groups);
        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1
								AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

        $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf
								INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id
								where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1 AND d.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $discountPercentTotal = $basicFees->fee_amount;
        $discountDollarTotal = 0;
        $new_discountDetailes = "";
        foreach ($new_discountFeesData as $val) {
            if ($val->discount_unit == 'p') {
                $doller = '';
                $percent = '%';
                $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                $discountPercentTotal = $discountPercentTotal - $fee_percent;
            } else {
                $doller = '$';
                $percent = '';
                $discountDollarTotal += $val->discount_percent;
            }
            $amount_val = $val->discount_percent + 0;
            $new_discountDetailes .= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
        }
        // $basicDiscountFees = (100 - $discountPercentTotal) / 100 * $basicFees->fee_amount;
        // $basicDiscountFees = ($basicFees->fee_amount * $discountPercentTotal) / 100 ;
        // $discount_fee_val = $basicFees->fee_amount - $basicDiscountFees + $discountDollarTotal;
        $final_amount = ($discountPercentTotal - $discountDollarTotal);
        if ($final_amount > 0) {
            $actualbasicDiscountFees = $final_amount;
        } else {
            $actualbasicDiscountFees = 0;
        }
        $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong>" . $new_discountDetailes . " ";
        $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND (schedule_status = 0 OR schedule_status = 3) AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND schedule_payment_date >= '".date('Y-m-d')."'");
        if (count((array)$student_fees_items) > 0) {
            foreach ($student_fees_items as $items) {
                $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
										current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "', comments='" . $comments . "',
										session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
										created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', schedule_payment_date = '" .$items->schedule_payment_date. "'  ");

                $sql_ret = $db->query("update ss_student_fees_items set amount='" . $actualbasicDiscountFees . "' ,
										updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
            }
        }
    } elseif(count((array)$old_discountFeesData) > 0) {
        // if (count((array)$old_discountFeesData) > 0) {
            $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "'
									AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id
									where m.student_user_id='" . $user_id . "' and m.latest = 1 AND g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            $groups = [];
            foreach ($user_groups as $group) {
                $groups[] = $group->id;
            }
            $group_ids = implode(",", $groups);
            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1
									AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong> ";
            
            $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "'
									AND (schedule_status = 0 OR schedule_status = 3) AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND schedule_payment_date >= '".date('Y-m-d')."' ");
            if (count((array)$student_fees_items) > 0) {
                foreach ($student_fees_items as $items) {
                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
										session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
										current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "',
										comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', schedule_payment_date = '" .$items->schedule_payment_date. "'  ");
                    
                                        $sql_ret = $db->query("update ss_student_fees_items set amount='" . $basicFees->fee_amount . "' ,
										updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                }
            }
        // }
    }


    $array_one = (array) $_POST['fees_discount_id'];
    $array_two = (array) $fees_discount_ids;
    $discount_check = array_merge(array_diff($array_one, $array_two), array_diff($array_two, $array_one));

    if(count((array)$discount_check) > 0 && $is_active == $student_data->is_active && $student_data->is_deleted == $is_deleted){

        //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
        if(count((array)$schedule_payment_cron) > 0){

            foreach($schedule_payment_cron as $data){

                $db->query("insert into ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."',  is_cancel=1, created_at='".date('Y-m-d h:i:s',strtotime($data->created_at))."', updated_at='".date('Y-m-d h:i:s',strtotime($data->updated_at))."'");

                $payment_sch_item_cron_backup_id = $db->insert_id;

                
                if($payment_sch_item_cron_backup_id > 0){

                    $total_final_amount = $db->get_var("select sum(amount) as total_final_amount from ss_student_fees_items  where id IN (".$data->sch_item_ids.")");
                    $family_data = (object) array_merge((array) $family, (array) $data);
                    $family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "","total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
                    
                    genrate_and_send_invoice($family_data);

                    $result = $db->query("update ss_payment_sch_item_cron set total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");

                    if(!$result){
                        $conn_check = false;
                    }
                }

            }


        }

    }



    //Student status change
    if($is_active != $student_data->is_active || $student_data->is_deleted != $is_deleted){

        //CASE HOLD AND INACTIVE
        if (($is_active === 0 || $is_active === 2) && $is_deleted === 0) {

            $current_status = 0;
            $new_status = 3;
            $comments = "<strong>Preview Schedule Status : </strong> Pending <br> <strong>Current Schedule Status : </strong> Hold ";
            $schedule_status = 3;
            $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND schedule_status = 0 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            
            if (count((array)$student_fees_items) > 0) {

                foreach ($student_fees_items as $items) {
                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
                                        session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                                        current_status='" . $current_status . "', new_status='" . $new_status . "', comments='" . $comments . "',
                                        created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', schedule_payment_date = '" .$items->schedule_payment_date. "'  ");

                    $sql_ret = $db->query("update ss_student_fees_items set schedule_status='" . $schedule_status . "',
                                        updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                }


                if($sql_ret){

                    $current_date = date('Y-m-d');
                    $current_month_last_date = date("Y-m-t", strtotime($current_date));

                    $student_fees_items_updated = $db->get_row("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
                    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                    INNER JOIN ss_user u ON u.id = s.user_id
                    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                    INNER JOIN ss_family f ON f.id = s.family_id
                    INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                    WHERE s.family_id = '" . $family_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                    AND u.is_deleted = 0  
                    AND u.is_locked=0  AND u.is_active = 1 AND sfi.schedule_payment_date <= '" .$current_month_last_date. "'
                    AND pay.default_credit_card =1 AND sfi.schedule_status = 0  AND sfi.schedule_notification = 1
                    GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY schedule_payment_date asc");


                    if(!empty($student_fees_items_updated)){

                        $schedule_payment_cron = get_schedule_payment_cron($family_id);

                        //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
                        if(count((array)$schedule_payment_cron) > 0){

                            foreach($schedule_payment_cron as $data){

                                $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."', is_cancel=1,  created_at='".$data->created_at."', updated_at='".$data->updated_at."'");
                                $payment_sch_item_cron_backup_id = $db->insert_id;
                                
                                if($payment_sch_item_cron_backup_id > 0){

                                    $total_final_amount = $student_fees_items_updated->final_amount;
                                    $family_data = (object) array_merge((array)  $student_fees_items_updated, (array) $data);
                                    $family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "", "total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
                                    
                                    genrate_and_send_invoice($family_data);

                                    $result = $db->query("update ss_payment_sch_item_cron set sch_item_ids= '" .$student_fees_items_updated->sch_item_id. "', total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");
                                    if(!$result){
                                        $conn_check = false;
                                    }

                                }

                            }

                        }


                    }else{

                        $schedule_payment_cron = get_schedule_payment_cron($family_id);

                        //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
                        if(count((array)$schedule_payment_cron) > 0){

                            foreach($schedule_payment_cron as $data){

                                $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."', is_cancel=1,  created_at='".$data->created_at."', updated_at='".$data->updated_at."'");
                                $payment_sch_item_cron_backup_id = $db->insert_id;

                                $result = $db->query("DELETE FROM ss_payment_sch_item_cron where id = '" .$data->id . "'");
                                $db->query("update ss_student_fees_items set schedule_notification=0, updated_at = '" . date('Y-m-d H:i') . "' where id IN (".$data->sch_item_ids.") ");

                                if(!$result){
                                    $conn_check = false;
                                }
                            }

                        }
                        //is month me all child inactive hai or payment hold hai to remainder hold ka jayega. case-4.B
                        $email_text = "Please disregard the prior email.Your payment for this month is being held.";
                        schedule_payment_update_notify($student_data,$email_text);
                    }

                }else{

                    $db->query('ROLLBACK');
                    $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '123');
                    CreateLog($_REQUEST, json_encode($return_resp));
                    echo json_encode($return_resp);
                    exit;

                }


            }



            //if($is_active === 0){
                // $stuinfo = $db->get_row("select stu.first_name,stu.last_name,stu.gender,stu.dob,stu.allergies,stu.school_grade,fam.primary_email,fam.secondary_email from ss_student stu inner join ss_family fam on fam.id=stu.family_id where stu.user_id = '" . $user_id . "'");
                // $gender = $stuinfo->gender == 'm' ? "Male" : "Female";
                // $new_email_body = "Dear Parent Assalamu-alaikum,<br>";
                // $new_email_body .= "Child inactive successfully, Details are mentioned below - <br>
                // <strong>Name</strong>- " . $stuinfo->first_name." ".$stuinfo->last_name . "<br>
                // <strong>Gender</strong> - " . $gender . "<br>
                // <strong>Date of Birth</strong> - " .date('m/d/Y', strtotime($stuinfo->dob)). "<br>
                // <strong>Allergy</strong> - " . trim($stuinfo->allergies) . "<br>
                // <strong>Grade</strong> -  " . trim($stuinfo->school_grade) . "<br>";
                // $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
                // $new_email_body .= "<br><br>JazakAllah Khairan";
                // $new_email_body .= "<br>" . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Team';
                // $mail_service_array = array(
                // 'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  Child inactive',
                // 'message' => $new_email_body,
                // 'request_from' => MAIL_SERVICE_KEY,
                // 'attachment_file_name' => [],
                // 'attachment_file' => [],
                // 'to_email' => [$stuinfo->primary_email, $stuinfo->secondary_email],
                // 'cc_email' => '',
                // 'bcc_email' => ''
                // );
        
                // mailservice($mail_service_array);
            //}
        }

        //CASE ACTIVE
        elseif ($is_active === 1 && $is_deleted === 0) {

            $current_status = 3;
            $new_status = 0;
            $comments = "<strong>Preview Schedule Status : </strong> Hold  <br> <strong>Current Schedule Status : </strong> Pending ";
            $schedule_status = 0;

            $check_student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

            //user not schedule 
            if(count((array)$check_student_fees_items) == 0){

                //check current month schedule payment
                $get_stu_schedule_payment = $db->get_results("SELECT sfi.* FROM ss_student_fees_items sfi
                INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                INNER JOIN ss_user u ON u.id = s.user_id
                INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                INNER JOIN ss_family f ON f.id = s.family_id
                INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                WHERE s.family_id = '" . $family_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                AND u.is_deleted = 0  
                AND u.is_locked=0  AND u.is_active = 1 AND sfi.schedule_payment_date >= '".date('Y-m-d')."'
                AND pay.default_credit_card =1 AND (sfi.schedule_status = 0 OR sfi.schedule_status = 3)
                GROUP BY sfi.schedule_unique_id ORDER BY schedule_payment_date asc");

                if(count((array)$get_stu_schedule_payment) > 0){

                    $user_payment_amount = student_fee_discount($user_id);

                    foreach($get_stu_schedule_payment as $pay_item){

                        $db->query("insert into ss_student_fees_items set schedule_unique_id='" . $pay_item->schedule_unique_id . "',student_user_id='" . $user_id . "',  original_schedule_payment_date= '" . $pay_item->schedule_payment_date . "', schedule_payment_date = '" . $pay_item->schedule_payment_date . "', amount='" . $user_payment_amount . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', schedule_status = '".$schedule_status."', created_at = '" . date('Y-m-d H:i') . "', created_by='" . $_SESSION['icksumm_uat_login_userid'] . "' ");

                    }

                }
                
            }

            $amount_sch = student_fee_discount($user_id);

            $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND schedule_status = 3
            AND schedule_payment_date >= '" . date('Y-m-d') . "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

            //schedule payment current date or next month payments
            if (count((array)$student_fees_items) > 0 ) {

                foreach ($student_fees_items as $items) {

                    $get_item_sch_date = $db->get_row("SELECT schedule_payment_date FROM ss_student_fees_items WHERE schedule_unique_id='".$items->schedule_unique_id."' AND  schedule_status=0 AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' order by id desc");

                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
                                        session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                                        current_status='" . $current_status . "', new_status='" . $new_status . "',
                                        comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', schedule_payment_date = '" .$get_item_sch_date->schedule_payment_date. "'  ");

                    $sql_ret = $db->query("update ss_student_fees_items set schedule_status='" . $schedule_status . "', amount='".$amount_sch."', schedule_payment_date='".$get_item_sch_date->schedule_payment_date."', updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                }

            }


            $student_fees_items_old = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND schedule_status = 3
                                AND schedule_payment_date < '" . date('Y-m-d') . "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

            //check current month schedule payment
            $student_fees_items_updated = $db->get_results("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
            INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
            INNER JOIN ss_user u ON u.id = s.user_id
            INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
            INNER JOIN ss_family f ON f.id = s.family_id
            INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
            WHERE s.family_id = '" . $family_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
            AND u.is_deleted = 0  
            AND u.is_locked=0  AND u.is_active = 1 AND sfi.schedule_payment_date <= '" .date('Y-m-t'). "'
            AND pay.default_credit_card =1 AND sfi.schedule_status = 0 
            GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY schedule_payment_date asc");


            //privous month payment hold & not schedule payment this month
            if (count((array)$student_fees_items_old) > 0 && count((array)$student_fees_items_updated) == 0) {

                foreach ($student_fees_items_old as $old_items) {

                    $sch_month = date('m',strtotime($old_items->schedule_payment_date));
                    $sch_month_date =  date("Y-$sch_month-d");
                    $current_date = date('Y-m-d');
                    if(date("Y-m-t", strtotime($sch_month_date)) >= $current_date){

                        $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $old_items->id . "' ,
                                            session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                                            current_status='" . $current_status . "', new_status='" . $new_status . "',
                                            comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', schedule_payment_date = '" .$old_items->schedule_payment_date. "'  ");

                        $sql_ret = $db->query("update ss_student_fees_items set schedule_status='" . $schedule_status . "', amount='".$amount_sch."', schedule_payment_date = '" .$current_date. "',
                                        updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $old_items->id . "'");

                    }
                }
            }




            //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
            if(count((array)$schedule_payment_cron) > 0){

                foreach($schedule_payment_cron as $data){

                    $db->query("insert into ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."',  is_cancel=1, created_at='".date('Y-m-d h:i:s',strtotime($data->created_at))."', updated_at='".date('Y-m-d h:i:s',strtotime($data->updated_at))."'");

                    $payment_sch_item_cron_backup_id = $db->insert_id;

                
                    if($payment_sch_item_cron_backup_id > 0){

                    //check current month schedule payment
                    $updated_sch_payment = $db->get_row("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
                    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                    INNER JOIN ss_user u ON u.id = s.user_id
                    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                    INNER JOIN ss_family f ON f.id = s.family_id
                    INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                    WHERE sfi.schedule_unique_id = '" . $data->schedule_unique_id. "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                    AND u.is_deleted = 0  
                    AND u.is_locked=0  AND u.is_active = 1
                    AND pay.default_credit_card =1 AND sfi.schedule_status = 0 
                    GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY schedule_payment_date asc");

                        $total_final_amount = $updated_sch_payment->final_amount;

                        $family_data = (object) array_merge((array) $family, (array) $data);
                        $family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "","total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
                        
                        genrate_and_send_invoice($family_data);

                        $result = $db->query("update ss_payment_sch_item_cron set sch_item_ids = '" .$updated_sch_payment->sch_item_id. "', total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");

                    $result1 = $db->query("update ss_student_fees_items set schedule_notification = '1', updated_at = '" . date('Y-m-d H:i') . "' where id IN (".$updated_sch_payment->sch_item_id.") ");

                    if(!$result && !$result1){
                        $conn_check = false;
                    }

                }


            }
        }



            // $student_fees_items_updated = $db->get_row("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
            // INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
            // INNER JOIN ss_user u ON u.id = s.user_id
            // INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
            // INNER JOIN ss_family f ON f.id = s.family_id
            // INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
            // WHERE s.family_id = '" . $family_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
            // AND u.is_deleted = 0  
            // AND u.is_locked=0  AND u.is_active = 1
            // AND pay.default_credit_card =1 AND sfi.schedule_status = 0  AND sfi.schedule_notification = 1
            // GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY schedule_payment_date asc");

            // $schedule_payment_cron = get_schedule_payment_cron($family_id);

            // //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
            // if(count((array)$schedule_payment_cron) > 0){ 

            //     foreach($schedule_payment_cron as $data){

            //         $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."', is_cancel=1,  created_at='".$data->created_at."', updated_at='".$data->updated_at."'");
            //         $payment_sch_item_cron_backup_id = $db->insert_id;
                    
            //         if($payment_sch_item_cron_backup_id > 0){
                        
            //             $total_final_amount = $student_fees_items_updated->final_amount;
            //             $family_data = (object) array_merge((array)  $student_fees_items_updated, (array) $data);
            //             $family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "", "total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
                        
            //             genrate_and_send_invoice($family_data);

            //             $result = $db->query("update ss_payment_sch_item_cron set sch_item_ids= '" .$student_fees_items_updated->sch_item_id. "', total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");
            //             if(!$result){
            //                 $conn_check = false;
            //             }

            //         }

            //     }


            // }else{

            //     //new remainder create active child
            // }






            // $db->query("update ss_student_fees_items set schedule_status='" . $schedule_status . "',
            // updated_at='" . date('Y-m-d H:i:s') . "' where student_user_id = '" . $user_id . "' AND schedule_status = 3
            // AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");

            // if($is_active === 1){
            //     $stuinfo = $db->get_row("select stu.first_name,stu.last_name,stu.gender,stu.dob,stu.allergies,stu.school_grade,fam.primary_email,fam.secondary_email from ss_student stu inner join ss_family fam on fam.id=stu.family_id where stu.user_id = '" . $user_id . "'");
            //     $gender = $stuinfo->gender == 'm' ? "Male" : "Female";
            //     $new_email_body = "Dear Parent Assalamu-alaikum,<br>";
            //     $new_email_body .= "Child active successfully, Details are mentioned below - <br>
            //     <strong>Name</strong>- " . $stuinfo->first_name." ".$stuinfo->last_name . "<br>
            //     <strong>Gender</strong> - " . $gender . "<br>
            //     <strong>Date of Birth</strong> - " .date('m/d/Y', strtotime($stuinfo->dob)). "<br>
            //     <strong>Allergy</strong> - " . trim($stuinfo->allergies) . "<br>
            //     <strong>Grade</strong> -  " . trim($stuinfo->school_grade) . "<br>";
            //     $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
            //     $new_email_body .= "<br><br>JazakAllah Khairan";
            //     $new_email_body .= "<br>" . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Team';
            //     $mail_service_array = array(
            //     'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  Child active',
            //     'message' => $new_email_body,
            //     'request_from' => MAIL_SERVICE_KEY,
            //     'attachment_file_name' => [],
            //     'attachment_file' => [],
            //     'to_email' => [$stuinfo->primary_email, $stuinfo->secondary_email],
            //     'cc_email' => '',
            //     'bcc_email' => ''
            //     );
        
            //     mailservice($mail_service_array);
            // }

        }

        //CASE DELETE
        elseif ($is_active === 1 && $is_deleted === 1) {
            $current_status = 0;
            $new_status = 2;
            $comments = "<strong>Preview Schedule Status : </strong> Pending  <br> <strong>Current Schedule Status : </strong> Cancel  ";
            $schedule_status = 2;
            $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "'
                            AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND (schedule_status = 0 OR schedule_status = 3)");
            if (count((array)$student_fees_items) > 0) {
                foreach ($student_fees_items as $items) {
                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
                                    session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                                    current_status='" . $current_status . "', new_status='" . $new_status . "', comments='" . $comments . "',
                                    created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', schedule_payment_date = '" .$items->schedule_payment_date. "'  ");
                    $sql_ret = $db->query("update ss_student_fees_items set schedule_status='" . $schedule_status . "',
                                    updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                }
            }


            if($sql_ret){

                $current_date = date('Y-m-d');
                $current_month_last_date = date("Y-m-t", strtotime($current_date));

                $student_fees_items_updated = $db->get_row("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
                INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                INNER JOIN ss_user u ON u.id = s.user_id
                INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                INNER JOIN ss_family f ON f.id = s.family_id
                INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                WHERE s.family_id = '" . $family_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                AND u.is_deleted = 0  
                AND u.is_locked=0  AND u.is_active = 1 AND sfi.schedule_payment_date <= '" .$current_month_last_date. "'
                AND pay.default_credit_card =1 AND sfi.schedule_status = 0  AND sfi.schedule_notification = 1
                GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY schedule_payment_date asc");


                if(!empty($student_fees_items_updated)){

                    $schedule_payment_cron = get_schedule_payment_cron($family_id);

                    //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
                    if(count((array)$schedule_payment_cron) > 0){

                        foreach($schedule_payment_cron as $data){

                            $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."', is_cancel=1,  created_at='".$data->created_at."', updated_at='".$data->updated_at."'");
                            $payment_sch_item_cron_backup_id = $db->insert_id;
                            
                            if($payment_sch_item_cron_backup_id > 0){

                                $total_final_amount = $student_fees_items_updated->final_amount;
                                $family_data = (object) array_merge((array)  $student_fees_items_updated, (array) $data);
                                $family_data = (object) array_merge((array) $family_data, ["new_schedule_payment_date" => "", "total_amount"=>$total_final_amount,"old_total_amount"=>$data->old_total_amount]);
                                
                                genrate_and_send_invoice($family_data);

                                $result = $db->query("update ss_payment_sch_item_cron set sch_item_ids= '" .$student_fees_items_updated->sch_item_id. "', total_amount = '" .$total_final_amount. "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" .$data->id . "'");
                                if(!$result){
                                    $conn_check = false;
                                }

                            }

                        }

                    }


                }else{

                    $schedule_payment_cron = get_schedule_payment_cron($family_id);

                    //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
                    if(count((array)$schedule_payment_cron) > 0){

                        foreach($schedule_payment_cron as $data){

                            $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$data->schedule_unique_id ."',  	family_id ='".$data->family_id."', sch_item_ids='".$data->sch_item_ids."', schedule_payment_date='".$data->schedule_payment_date."', total_amount ='".$data->old_total_amount ."', wallet_amount = '".$data->wallet_amount."', cc_amount = '".$data->cc_amount."', schedule_status  = '".$data->schedule_status ."', retry_count = '".$data->retry_count."', session  = '".$data->session ."', is_approval  = '".$data->is_approval."', reason = '".$data->reason."', payment_unique_id = '".$data->payment_unique_id."', payment_response_code= '".$data->payment_response_code."', payment_response= '".$data->payment_response."', is_cancel=1,  created_at='".$data->created_at."', updated_at='".$data->updated_at."'");
                            $payment_sch_item_cron_backup_id = $db->insert_id;

                            $result = $db->query("DELETE FROM ss_payment_sch_item_cron where id = '" .$data->id . "'");

                            if(!$result){
                                $conn_check = false;
                            }
                        }

                    }
                    //is month me all child delete hai or payment cancel hai to remainder cancel ka jayega. case-4.B
                    $email_text = "Please disregard the prior email.This month's payment for you is being stopped.";
                    schedule_payment_update_notify($student_data,$email_text);
                }

            }else{

                $db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '125');
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;

            }

            ////Send email on delete student
            // $stuinfo = $db->get_row("select stu.first_name,stu.last_name,stu.gender,stu.dob,stu.allergies,stu.school_grade,fam.primary_email,fam.secondary_email from ss_student stu inner join ss_family fam on fam.id=stu.family_id where stu.user_id = '" . $user_id . "'");
            // $gender = $stuinfo->gender == 'm' ? "Male" : "Female";
            // $new_email_body = "Dear Parent Assalamu-alaikum,<br>";
            // $new_email_body .= "Child removed successfully, Details are mentioned below - <br>
            // <strong>Name</strong>- " . $stuinfo->first_name." ".$stuinfo->last_name . "<br>
            // <strong>Gender</strong> - " . $gender . "<br>
            // <strong>Date of Birth</strong> - " .date('m/d/Y', strtotime($stuinfo->dob)). "<br>
            // <strong>Allergy</strong> - " . trim($stuinfo->allergies) . "<br>
            // <strong>Grade</strong> -  " . trim($stuinfo->school_grade) . "<br>";
            // $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
            // $new_email_body .= "<br><br>JazakAllah Khairan";
            // $new_email_body .= "<br>" . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Team';

            // $mail_service_array = array(
            // 'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  Child Removed',
            // 'message' => $new_email_body,
            // 'request_from' => MAIL_SERVICE_KEY,
            // 'attachment_file_name' => [],
            // 'attachment_file' => [],
            // 'to_email' => [$stuinfo->primary_email, $stuinfo->secondary_email],
            // 'cc_email' => '',
            // 'bcc_email' => ''
            // );

            // mailservice($mail_service_array);
        }



    }



    if ($sql_ret && $db->query('COMMIT') !== false) {
        echo json_encode(array('code' => "1", 'msg' => 'Student details updated successfully '));
        exit;
    } else {
        $db->query('ROLLBACK');
        $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
    /*}else{
    $db->query('ROLLBACK');
    $return_resp = array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>'4');
    CreateLog($_REQUEST, json_encode($return_resp));
    echo json_encode($return_resp);
    exit;
    }        */
    /*}else{
$return_resp = array('code' => "0",'msg' => 'Error: Admission number already exists','_errpos'=>'3');
CreateLog($_REQUEST, json_encode($return_resp));
echo json_encode($return_resp);
exit;
}*/


}else{
    $db->query('ROLLBACK');
    $return_resp = array('code' => "0", 'msg' => '<p class="text-danger">A Payment is under process, please try again after some time. </p>', '_errpos' => '13');
    CreateLog($_REQUEST, json_encode($return_resp));
    echo json_encode($return_resp);
    exit;
    
}
}catch (customException $e) {
	$db->query('ROLLBACK');
	CreateLog($_REQUEST, json_encode($e->errorMessage()));
	exit;

}
}
//==========================ASSIGN NEW GROUP TO STUDENT===================
elseif ($_POST['action'] == 'assign_new_group_to_student') {
    $get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
    // Multiple Group
    if ($get_general_info == 0) {
        $user_id = $_POST['user_id'];
        $classes = $_POST['class'];
        $totalClass_count = count((array)$classes);
        $queryCount = 0;

       //check student assign to closed group 
        foreach ($classes as $class_id) {
        $group_id = $_POST["group_id" . $class_id];
        $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
        $groupIsOpen = $group_details->is_regis_open;
        $groupName = $group_details->group_name;
        
        $is_already_in_closed_group = $db->get_var("select COUNT(*) from ss_studentgroupmap where group_id = '145' and class_id = '" . $class_id . "' and latest = 1  AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and student_user_id = '" . $user_id . "'");
       
        if($is_already_in_closed_group==0 && $groupIsOpen==0){
            echo json_encode(array('code' => "0", 'msg' => 'Error: Registration closed in ' . $group_details->group_name));
            exit;
        }

        }

       //end 

        foreach ($classes as $class_id) {
            $group_id = $_POST["group_id" . $class_id];
            $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
            $groupMaxLimit = $group_details->max_limit;
            $group_name = $group_details->group_name;
           
            $groupCurStrength = $db->get_var("select COUNT(1) from ss_studentgroupmap where group_id = '" . $group_id . "' and class_id = '" . $class_id . "' and latest = 1
	AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

            
               
            if ($groupCurStrength < $groupMaxLimit) {
                $sql_ret = $db->query("update ss_studentgroupmap set latest='0',
		updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'
		where student_user_id = '" . $user_id . "' AND class_id = '" . $class_id . "' AND latest=1 ");
                $sql_ret = $db->query("insert into ss_studentgroupmap set student_user_id='" . $user_id . "',
		group_id='" . $group_id . "', class_id = '" . $class_id . "', latest=1, created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
		session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
		created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
		updated_on='" . date('Y-m-d H:i:s') . "'");
                $queryCount = $queryCount + 1;
            } else {
                $return_resp = array('code' => "0", 'msg' => 'Error: ' . $group_name . ' Group has reached maximum limit', '_errpos' => '10');
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;
            }
        }
        if ($totalClass_count == $queryCount) {
            echo json_encode(array('code' => "1", 'msg' => 'Group assigned successfully'));
            exit;
        } else {
            $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    }
    //Single Group
    elseif ($get_general_info == 1) {
        $user_id = $_POST['user_id'];
        $group_id = $_POST['group_id'];
        $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
        $groupMaxLimit = $group_details->max_limit;
        $group_name = $group_details->group_name;
        $groupCurStrength = $db->get_var("select COUNT(1) from ss_studentgroupmap where group_id = '" . $group_id . "' and student_user_id = '" . $user_id . "' and latest = 1
	AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        if ($groupCurStrength < $groupMaxLimit) {
            $sql_ret = $db->query("update ss_studentgroupmap set group_id = '" . $group_id . "',
		updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "'
		where student_user_id = '" . $user_id . "' AND latest=1 ");
            if ($sql_ret) {
                echo json_encode(array('code' => "1", 'msg' => 'Group assigned successfully'));
                exit;
            } else {
                $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1');
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;
            }
        } else {
            $return_resp = array('code' => "0", 'msg' => 'Error: ' . $group_name . ' Group has reached maximum limit', '_errpos' => '10');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    }
}
//==========================EDIT FAMILY=====================
elseif ($_POST['action'] == 'edit_family') {
    $student = $db->get_row("select * from ss_student where user_id='" . $_POST['user_id'] . "'");
    $pp_em = trim($db->escape($_POST['primary_email']));
    $ss_user = $db->get_row("select * from ss_user where id='" . $_POST['user_id'] . "'");
    if ($ss_user) {

        if($_POST['only_primary']){
            $primary_email = trim($db->escape($_POST['primary_email']));
            $secondary_email = trim($db->escape($_POST['secondary_email']));
            $primary_contact = 'Father';
        }else{
            if ($_POST['primary']) {
                $primary_email = trim($db->escape($_POST['secondary_email']));
                $secondary_email = trim($db->escape($_POST['primary_email']));
                $primary_contact = 'Father';
            } else {
                $primary_email = trim($db->escape($_POST['primary_email']));
                $secondary_email = trim($db->escape($_POST['secondary_email']));
                $primary_contact = 'Mother';
            }
            
        }

        $family_id = $student->family_id;
        $family = $db->get_row("select * from ss_family where id = '" . $family_id . "'");
        //COMMENTED ON 22-JAN-2022
        //$isemailexist = $db->get_row("select * from ss_user where username='".$pp_em."'");
        //ADDED ON 22-JAN-2022
        $isemailexist = $db->get_row("select * from ss_user where username='" . $primary_email . "' and id <> '" . $family->user_id . "'");
        if (empty($isemailexist)) {
            $db->query('BEGIN');
            $sql_ret = $db->query("update ss_family set father_first_name='" . trim($db->escape($_POST['father_first_name'])) . "',
			father_last_name='" . trim($db->escape($_POST['father_last_name'])) . "', father_area_code='" . trim($db->escape($_POST['father_area_code'])) . "',
			father_phone='" . trim($db->escape($_POST['father_phone'])) . "',mother_first_name='" . trim($db->escape($_POST['mother_first_name'])) . "',
			mother_last_name='" . trim($db->escape($_POST['mother_last_name'])) . "',mother_area_code='" . trim($db->escape($_POST['mother_area_code'])) . "',
			mother_phone='" . trim($db->escape($_POST['mother_phone'])) . "',primary_email='" . $primary_email . "', primary_contact='" . $primary_contact . "',
			secondary_email='" . $secondary_email . "',
			billing_address_1='" . trim($db->escape($_POST['billing_address_1'])) . "',
			billing_address_2='" . trim($db->escape($_POST['billing_address_2'])) . "',
			billing_city='" . trim($db->escape($_POST['billing_city'])) . "',
			billing_state_id='" . trim($db->escape($_POST['billing_state_id'])) . "',
			billing_entered_state='" . trim($db->escape($_POST['billing_state_id'])) . "',
			billing_country_id='" . trim($db->escape($_POST['billing_country_id'])) . "',
			billing_post_code='" . trim($db->escape($_POST['billing_post_code'])) . "',
			comments='" . trim($db->escape($_POST['comments'])) . "',
			updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $family_id . "'");
            $sqls_ret = $db->query("update ss_user set username = '" . $primary_email . "', email = '" . $primary_email . "',
			updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $family->user_id . "' ");
            if ($sql_ret && $sqls_ret && $db->query('COMMIT') !== false) {
                echo json_encode(array('code' => "1", 'msg' => 'Family information updated successfully'));
                exit;
            } else {
                $db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1');
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;
            }
        } else {
            echo json_encode(array('code' => "0", 'msg' => 'Email already exists'));
            exit;
        }
    } else {
        echo json_encode(array('code' => "0", 'msg' => 'User not exist'));
        exit;
    }
}
//==========================FETCH STUDENTS OF A GROUP - FOR TEXT MESSAGE=====================
elseif ($_POST['action'] == 'fetch_grp_stu_for_select') {
    $group_id = $_POST['group_id'];
    $teacher_id = $_POST['teacher_id'];
    if (is_numeric($group_id)) {
        $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group_id . "') order by s.first_name,s.last_name");
    } elseif ($group_id == "all_groups") {
        $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id in (SELECT id FROM ss_groups WHERE is_active = 1
	AND is_deleted = 0 AND id IN (SELECT group_id FROM ss_classtime WHERE id IN (SELECT classtime_id FROM ss_staffclasstimemap
	WHERE active = 1 and staff_user_id IN (" . implode(',', $teacher_id) . "))))) order by s.first_name,s.last_name");
    }
    if (count((array)$students)) {
        foreach ($students as $stu) {
            $retVal .= '<option value="' . $stu->user_id . '">' . $stu->first_name . ' ' . trim($stu->middle_name . ' ' . $stu->last_name) . '</option>';
        }
        echo json_encode(array('code' => 1, 'optionVal' => $retVal));
        exit;
    } else {
        echo json_encode(array('code' => 0));
        exit;
    }
}
//==========================GET STUDENTS OF A GROUP=====================
elseif ($_POST['action'] == 'group_students_for_select') {
    $group_id = $_POST['group_id'];
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];
    if (is_numeric($group_id)) {
        $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group_id . "' AND class_id='" . $class_id . "') order by s.first_name,s.last_name");
    } elseif ($group_id == "all_groups") {
        $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id in (select group_id from ss_staffgroupmap
	where staff_user_id in (" . implode(',', $teacher_id) . ") and active = 1)) order by s.first_name,s.last_name");
    }
    if (count((array)$students)) {
        foreach ($students as $stu) {
            $retVal .= '<option value="' . $stu->user_id . '">' . $stu->first_name . ' ' . trim($stu->middle_name . ' ' . $stu->last_name) . '</option>';
        }
        echo json_encode(array('code' => 1, 'optionVal' => $retVal));
        exit;
    } else {
        echo json_encode(array('code' => 0));
        exit;
    }
}
//==========================GET STUDENTS OF A GROUP=====================
elseif ($_POST['action'] == 'get_students_of_group_for_select') {
    $group_id = $_POST['group_id'];
    $class_id = $_POST['class_id'];
    //$teacher_id = $_POST['teacher_id'];
    if (is_array($group_id) && is_array($class_id)) {
        // if(in_array('all_groups', $group_id)){
        // }
        // if(in_array('all_subjects', $class_id)){
        // }
        // if(in_array('all_groups', $group_id) && in_array('all_subjects', $class_id)){
        // }
        // $group_id = implode(',',$group_id);
        // $class_id = implode(',',$class_id);
        if (!in_array('all_groups', $group_id) && !in_array('all_subjects', $class_id)) {
            $group_id = implode(',', $group_id);
            $class_id = implode(',', $class_id);
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id IN ('" . $group_id . "') AND class_id IN ('" . $class_id . "')) order by s.first_name,s.last_name");
        } elseif (in_array('all_groups', $group_id) && (!in_array('all_subjects', $class_id))) {
            $class_id = implode(',', $class_id);
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND class_id IN ('" . $class_id . "')) order by s.first_name,s.last_name");
        } elseif (!in_array('all_groups', $group_id) && in_array('all_subjects', $class_id)) {
            $group_id = implode(',', $group_id);
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id IN ('" . $group_id . "')) order by s.first_name,s.last_name");
        } elseif (in_array('all_groups', $group_id) && in_array('all_subjects', $class_id)) {
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name");
        }
    } else {
        if (is_numeric($group_id) && is_numeric($class_id)) {
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group_id . "' AND class_id ='" . $class_id . "') order by s.first_name,s.last_name");
        } elseif ($group_id == "all_groups" && is_numeric($class_id)) {
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND class_id ='" . $class_id . "') order by s.first_name,s.last_name");
        } elseif (is_numeric($group_id) && $class_id = "all_subjects") {
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id  ='" . $group_id . "') order by s.first_name,s.last_name");
        } elseif ($group_id == "all_groups" && $class_id = "all_subjects") {
            $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0
	AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name");
        }
    }
    if (count((array)$students)) {
        if (count((array)$students) > 1) {
            $retVal .= '<option value="all_students">ALL STUDENTS OF GROUP</option>';
        }

        foreach ($students as $stu) {
            $retVal .= '<option value="' . $stu->user_id . '">' . $stu->first_name . ' ' . trim($stu->middle_name . ' ' . $stu->last_name) . '</option>';
        }
        echo json_encode(array('code' => 1, 'optionVal' => $retVal));
        exit;
    } else {
        echo json_encode(array('code' => 0));
        exit;
    }
} elseif ($_POST['action'] == 'get_pending_students_for_select') {

    $admission_reqs = $db->get_results("SELECT * from ss_sunday_school_reg where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  order by id desc");
    
    $students = $db->get_results("SELECT `reg_child`.`id`,`reg_child`.`first_name`,`reg_child`.`last_name`,`reg_child`.`sunday_school_reg_id` FROM `ss_sunday_sch_req_child` `reg_child` INNER JOIN `ss_sunday_school_reg` `school` ON `reg_child`.`sunday_school_reg_id`=`school`.`id` WHERE `reg_child`.`is_executed` = 0 and `reg_child`.is_delete <> 1 and `school`.`session` = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' order by `reg_child`.`first_name`,`reg_child`.`last_name`;");

    if (count((array)$students)) {
        if (count((array)$students) > 1) {
            $retVal = '<option value="all_pending_students">All Students</option>';
        }
        foreach ($students as $stu) {
            $retVal .= '<option value="' . $stu->id . '">' . $stu->first_name . ' ' . trim($stu->last_name) . '</option>';
        }
        echo json_encode(array('code' => 1, 'optionVal' => $retVal));
        exit;
    } else {
        echo json_encode(array('code' => 0));
        exit;
    }
} elseif ($_POST['action'] == 'get_registered_staff_for_select') {
    $staffs = $db->get_results("SELECT s.user_id,
	CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name,
	u.email, mobile, (CASE WHEN u.is_deleted=1 THEN 'Deleted' WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status FROM ss_user u
	INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_staff_session_map ssm on u.id = ssm.staff_user_id
	WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND status = 1 GROUP BY u.email;");

    $princi = $db->get_row("SELECT id FROM ss_user WHERE is_active = 1 AND is_deleted = 0 AND username <> 'admin31' 
    AND user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT01' AND user_type_group = 'principal' AND is_default = 1)");

    if (count((array)$staffs)) {
        if (count((array)$staffs) > 1) {
            $retVal = '<option value="all_registered_staff">All Staff</option>';
        }
        if(!empty($princi)){
            $retVal .='<option value="' . $princi->id . '"> Principal </option>';
        }

        foreach ($staffs as $stu) {
            $retVal .= '<option value="' . $stu->user_id . '">' . $stu->staff_name . '</option>';
        }
        echo json_encode(array('code' => 1, 'optionVal' => $retVal));
        exit;
    } else {
        echo json_encode(array('code' => 0));
        exit;
    }
} elseif ($_POST['action'] == 'get_pending_staff_for_select') {
    $staffs = $db->get_results("SELECT distinct r.id, CONCAT(r.first_name,' ',COALESCE(r.middle_name,''),' ',COALESCE(r.last_name,'')) AS staff_name FROM ss_staff_registration r LEFT JOIN ss_user u ON r.email = u.email WHERE r.is_request = 0 AND r.is_processed = 0 AND r.session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
    if (count((array)$staffs)) {

        if (count((array)$staffs) > 1) {
            $retVal = '<option value="all_pending_staff">All Staff</option>';
        }

        foreach ($staffs as $stu) {
            $retVal .= '<option value="' . $stu->id . '">' . $stu->staff_name . '</option>';
        }
        echo json_encode(array('code' => 1, 'optionVal' => $retVal));
        exit;
    } else {
        echo json_encode(array('code' => 0));
        exit;
    }
}

//=====================DELETE STAFF==================
/*elseif($_POST['action'] == 'delete_student'){
if(isset($_POST['user_id'])){
$rec = $db->query("update ss_user set is_deleted=1, updated_on='".date("Y-m-d H:i:s")."' where id='".$_POST['user_id']."'");

if($rec > 0){
echo json_encode(array('code' => "1",'msg' => 'Student deleted (soft) successfully'));
exit;
}else{
echo json_encode(array('code' => "0",'msg' => 'Error: Student deletion failed','_errpos'=>1));
exit;
}
}else{
echo json_encode(array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>2));
exit;
}
}*/elseif ($_POST['action'] == 'check_photo_file') {
    if (isset($_FILES["photo"])) {
        if ($_FILES["photo"]["error"] == 0) {
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES["photo"]["name"];
            $filetype = $_FILES["photo"]["type"];
            $filesize = $_FILES["photo"]["size"];
            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed)) {
                //echo json_encode(array('code' => "0",'msg' => 'Error: Please select a valid file format'));
                echo 'Error: Please select a valid file format';
                exit;
            }
            // Verify file size - 2MB maximum
            $maxsize = 2 * 1024 * 1024;
            if ($filesize > $maxsize) {
                //echo json_encode(array('code' => "0",'msg' => 'Error: File size must be less than 2 MB'));
                echo 'Error: File size must be less than 2 MB';
                exit;
            }
        } else {
            //echo json_encode(array('code' => "0",'msg' => 'Error: '.$_FILES["photo"]["error"]));
            echo $_FILES["photo"]["error"];
            exit;
        }
    }
} elseif ($_POST['action'] == 'update_photo') {
} elseif ($_POST['action'] == 'view_student_groupclass') {
    $stugroupclass = $db->get_results("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $_POST['stuid'] . "' ");
    echo json_encode(array('groups' => $stugroupclass));
    exit;
}
