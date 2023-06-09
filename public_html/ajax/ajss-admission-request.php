<?php
header('Access-Control-Allow-Origin: *');
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}
if(!empty(get_country()->currency)){
    $currency = get_country()->currency;
}else{
    $currency = '';  
}
//==========================GET NOTIFICATION DETAILS=====================
if ($_GET['action'] == 'get_header_notif') {

    $all_data = $db->get_results("SELECT created_on, id, 'student' AS type FROM ss_sunday_school_reg WHERE id IN (SELECT sunday_school_reg_id FROM ss_sunday_sch_req_child WHERE is_executed = 0 AND is_delete =0) AND session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
    UNION
    SELECT created_on, id AS staff_id, 'staff' AS type FROM ss_staff_registration r WHERE is_request = 0 AND is_processed = 0 AND session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' order by created_on desc");

    foreach ($all_data as $req) {
        if (!empty($req->created_on)) {
            $date = my_date_changer($req->created_on,'t');
        }

        if ($req->type == "student") {
            $notification_text1 = "Admission";
            $notification_text2 = "New admission request received";
            $link = "admission_request/admission_request_pending.php?reqid=" . $req->id;
        } else {
            $notification_text1 = "Staff";
            $notification_text2 = "New staff request received";
            $link = "staff/staff_pending_list.php?reqid=" . $req->staff_id;
        }

        $notification_summary .= '
        <li class="media">
            <div class="media-body">
                <a href="' . SITEURL . $link . '" class="media-heading">
                    <span class="text-semibold">' . $notification_text1 . ' Request #' . $req->id . '</span>
                    &nbsp;&nbsp;<span class="text-muted">' . $date . ' ' . $notification_text2 . '</span>

                </a>
            </div>
        </li>';
    }

    $total_count = count((array)$all_data);

    echo json_encode(array('notif_count' => $total_count, 'notif_summary' => $notification_summary));
    exit;
}

//==========================assign_gr_discount=====================
elseif ($_POST['action'] == 'assign_gr_discount') {
    $req_no = $_POST['req_no'];
    $db->query('BEGIN');
    $wait_child_father_id = $db->get_row("SELECT * FROM ss_sunday_school_reg WHERE id='" . $req_no . "'  AND is_waiting=1");
    $primary_email = $wait_child_father_id->primary_email;

    if (!empty($wait_child_father_id) && !empty($primary_email)) {

        $check_student = $db->get_row("SELECT GROUP_CONCAT(DISTINCT(s.user_id)) as child_ids 
   FROM ss_user as u 
   inner join ss_family as f on u.id=f.user_id 
   inner join ss_student as s on f.id=s.family_id 
   inner join ss_student_fees_items as sfi on sfi.student_user_id=s.user_id
   where primary_email='" . $primary_email . "' and f.is_deleted=0 and u.is_active=1 and u.is_deleted=0 and sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' GROUP by primary_email;  ");

        if (!empty($check_student)) {
            $discounts_record = $db->get_results("SELECT * FROM ss_fees_discounts WHERE status = 1 and session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
            $retStr = '<label><b>Discount:</b></label>';
            if (count((array)$discounts_record) > 0) {
                foreach ($discounts_record as $stu) {

                    $discount_amount = (int)$stu->discount_percent;
                    if ($stu->discount_unit == 'p') {
                        $sign = $discount_amount . '%';
                        $discount_name_with_sign = $stu->discount_name . '(' . $sign . ') ';
                    } else {
                        $sign = '$' . $discount_amount;
                        $discount_name_with_sign = '(' . $sign . ')' . $stu->discount_name;
                    }


                    $retStr .= '<input type="checkbox" name="discount[' . $stu->id . ']" class="discount form-check-input" style="margin:0 10px 0;" >' . $discount_name_with_sign . ' ';
                }
                $retStr .= '<p style="color:red;"><b>Note</b> : A payment reminder has already been sent to the parents. Do you still want to send this reminder and cancel the previous ones? </p>';

                $retStr .= '<input type="checkbox" name="agree" value="agree" class="required"><b> I Agree </b>';
                echo json_encode(array('code' => "1", 'msg' => $retStr));
                exit;
            } else {
                echo json_encode(array('code' => "0"));
                exit;
            }
        } else {
            echo json_encode(array('code' => "0"));
            exit;
        }
    } else {
        echo json_encode(array('code' => "0"));
        exit;
    }
}
//==========================REMOVE REQUEST=====================
elseif ($_POST['action'] == 'remove_request') {
    $childno = $_POST['childno'];
    $reqno = $_POST['reqno'];
    $db->query('BEGIN');

    $get_data_child = $db->get_row("select ch.first_name, ch.last_name, r.primary_email from ss_sunday_sch_req_child ch INNER JOIN ss_sunday_school_reg r
    ON r.id = ch.sunday_school_reg_id where ch.sunday_school_reg_id = '" . $reqno . "' and ch.id='" . $childno . "'");
    $new_email_body = "Dear Parent Assalamu-alaikum ,<br>";
    $new_email_body .= "The requested application for " . $get_data_child->first_name . " " . $get_data_child->last_name . "  has been rejected. For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
    $new_email_body .= "<br><br>".BEST_REGARDS_TEXT;
    $new_email_body .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
    //$resp_1 = $db->query("delete from ss_sunday_sch_req_child where id = '" . $childno . "'");
    $resp_1 = $db->query("update ss_sunday_sch_req_child set is_delete=1, updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $childno . "'");

    //$checkReq = $db->get_results("select * from ss_sunday_sch_req_child where sunday_school_reg_id = '" . $reqno . "'");
    // if (count((array)$checkReq) == 0) {
    //     $resp_2 = $db->query("delete from ss_sunday_school_reg where id = '" . $reqno . "'");

    // } else {
    //     $resp_2 = 1;
    // }
    if ($resp_1 > 0 && $db->query('COMMIT') !== false) {

        $mailservice_request_from = MAIL_SERVICE_KEY;
        $mail_service_array = array(
            'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " - Admission Report",
            'message' => $new_email_body,
            'request_from' => $mailservice_request_from,
            'attachment_file_name' => '',
            'attachment_file' => '',
            'to_email' => [$get_data_child->primary_email],
            'cc_email' => '',
            'bcc_email' => '',
        );
        mailservice($mail_service_array);
        echo json_encode(array('code' => "1", 'msg' => 'Admission request deleted successfully'));
        exit;
    } else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => 'Admission request not deleted'));
        exit;
    }
}

//==========================LIST ALL PENDING ADMISSION REQUESTS=====================
elseif ($_POST['action'] == 'list_adm_req_pending') {

    //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
    /// if (check_userrole_by_code('UT01')) {
    $finalAry = array();
    $admRequests = array();
    $delete_request = 0;
    if (is_numeric($_POST['reqid'])) {
        $admission_reqs = $db->get_results("SELECT * from ss_sunday_school_reg where id = '" . $_POST['reqid'] . "'");
    } else {
        if (trim($_POST['delete_request']) == 1) {
            $delete_request = 1;
        }
        $admission_reqs = $db->get_results("SELECT * from ss_sunday_school_reg where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  order by id desc");
    }
    foreach ($admission_reqs as $adm_requests) {
        $children = $db->get_results("SELECT * from ss_sunday_sch_req_child where sunday_school_reg_id = '" . $adm_requests->id . "' and is_delete = '" . $delete_request . "'");
        
        //$child_counter = 0;
        foreach ($children as $child) {
            if (trim($child->first_name) != '' && $child->is_executed == 0) {
                //$child_counter++;
                $temp = array();
                $interview_date = '';
                if (trim($child->interview_date) != '') {
                    $interview_date = date('d F,Y', strtotime($child->interview_date));
                }
                if ($child->added_to_waitlist == 1) {
                    $child->school_grade = $child->school_grade . ' - Waitlist';
                }

                if ($delete_request == 1) {
                    $status = 'Deleted';
                } else {
                    if ($adm_requests->is_waiting == 1) {
                        $status = 'Waiting';
                    } elseif ($adm_requests->is_waiting == 2) {
                        $status = 'Free';
                    } elseif($adm_requests->is_paid == 1) {
                        $status = 'Paid';
                    } 
                    // $status =  $adm_requests->is_waiting == '1' ? 'Waiting' : 'Paid';
                }

                $temp['req_no'] = $adm_requests->id;
                $temp['student_name'] = $child->first_name . ' ' . $child->last_name;
                $temp['gender'] = $child->gender == 'm' ? 'Male' : 'Female';
                $temp['father_name'] = $adm_requests->father_first_name . ' ' . $adm_requests->father_last_name;
                $temp['mother_name'] = $adm_requests->mother_first_name . ' ' . $adm_requests->mother_last_name;
                $temp['school_grade'] = $child->school_grade;
                $temp['interview'] = $interview_date;
                $temp['class_session'] = str_replace("-", "  to ", $adm_requests->class_session);
                $temp['child_no'] = $child->id; //$child_counter;
                $temp['dob'] = my_date_changer($child->dob);
                $temp['is_waiting'] = $status;
                $temp['created_on'] = my_date_changer($adm_requests->created_on,'t');
                $temp['is_delete'] = $child->is_delete;
                $temp['student_link'] = "<a href='javascript:void(0)' title='View Full Detail' data-childno='" . $child->id . "' data-version='v2' data-reqno='" . $adm_requests->id . "' class='text-primary viewdetail'>" . $child->first_name . ' ' . $child->last_name . "</a>";
                $admRequests[] = $temp;
            }
        }
    }
    $finalAry['data'] = $admRequests;
    echo json_encode($finalAry);
    exit;
    //}
}

//==========================LIST ALL COMPLETED ADMISSION REQUESTS=====================
elseif ($_GET['action'] == 'list_adm_req_completed') {
    //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
    //  if (check_userrole_by_code('UT01')) {
    $finalAry = array();
    $admRequests = array();
    $deletedStuAry = array();
    $del_students = $db->get_results("select ss_user.id from ss_user INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = ss_user.id
	where ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and is_deleted=1");
    foreach ($del_students as $delStu) {
        $deletedStuAry[] = $delStu->id;
    }

    $admission_reqs = $db->get_results("SELECT *, reg.id as reg_id, ch.id as child_id,ch.updated_on as child_updated_on  from ss_sunday_school_reg reg
        INNER JOIN ss_sunday_sch_req_child ch ON ch.sunday_school_reg_id = reg.id
        where reg.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' order by ch.updated_on DESC");

    foreach ($admission_reqs as $child) {
        if (trim($child->first_name) != '' && $child->user_id > 0 && !in_array($child->user_id, $deletedStuAry)) {
            $temp = array();
            $child_user_id = $child->user_id;
            $child_admission_date = $db->get_var("select admission_date from ss_student where user_id='" . $child_user_id . "'");
            $child_admission_date = my_date_changer($child_admission_date);
            $child_group = $db->get_results("select group_name from ss_groups where id IN (select group_id from ss_studentgroupmap
				where student_user_id='" . $child_user_id . "' order by id desc ) AND is_active=1");
            $child_grp = [];
            foreach ($child_group as $grop) {
                $child_grp[] = $grop->group_name;
            }
            $allChildGrp = implode(',', $child_grp);
            $temp['req_no'] = $child->reg_id;
            $temp['student_name'] = $child->first_name . ' ' . $child->last_name;
            $temp['gender'] = $child->gender == 'm' ? 'Male' : 'Female';
            $temp['father_name'] = $child->father_first_name . ' ' . $child->father_last_name;
            $temp['mother_name'] = $child->mother_first_name . ' ' . $child->mother_last_name;
            $temp['school_grade'] = $child->school_grade;
            $temp['admission_date'] = $child_admission_date;
            $temp['alloted_group'] = $allChildGrp;
            $temp['child_no'] = $child->child_id;
            $temp['updated_on'] = $child->child_updated_on;
            $temp['student_link'] = "<a href='javascript:void(0)' title='View Full Detail' data-childno='" . $child->child_id . "'  data-reqno='" . $child->reg_id . "' class='text-primary viewdetail'>" . $child->first_name . ' ' . $child->last_name . "</a>";
            $admRequests[] = $temp;
        }
    }
    $finalAry['data'] = $admRequests;
    echo json_encode($finalAry);
    exit;
    // }
    ///////////////////////////////////////////////////////    assign_group_to_new_student /////////////////////
} elseif ($_POST['action'] == 'assign_group_to_new_student') {
    $get_general_info = $db->get_row("select one_student_one_lavel, new_registration_session from ss_client_settings where status = 1");
    //Single Group
    if ($get_general_info->one_student_one_lavel == 1) {
        $group_id = $_POST['group_id'];
        $reqno = $_POST['reqno'];
        $childno = $_POST['childno'];
        $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");
        $groupMaxLimit = $group_details->max_limit;
        $group_name = $group_details->group_name;
        //ADDED ON 21AUG2021
        $adm_info = $db->get_row("select * from ss_sunday_school_reg where id='" . $reqno . "'");

        //$groupCurStrength = $db->get_var("select count(1) from ss_studentgroupmap where group_id = '".$group_id."' and latest = 1");
        $groupCurStrength = $db->get_var("SELECT COUNT(1) FROM ss_studentgroupmap m INNER JOIN ss_user u ON m.student_user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE u.`is_active` = 1
	    AND u.`is_deleted` = 0 AND group_id = '" . $group_id . "' AND latest = 1 AND ssm.`session_id` = '" . $adm_info->session . "'");
        if ($groupCurStrength < $groupMaxLimit) {
            $child_user_id = 0;
            $child_user_id = $db->get_var("select user_id from ss_sunday_sch_req_child where id='" . $childno . "'");
            if (trim($child_user_id) == '') {
                //COMMENTED ON 21AUG2021
                //$adm_info = $db->get_row("select * from ss_sunday_school_reg where id='".$reqno."'");
                $child_info = $db->get_row("select * from ss_sunday_sch_req_child where id='" . $childno . "'");
                $newUsername = generateUsername($child_info->first_name, $child_info->last_name, $child_info->dob);
                $first_name = trim($db->escape($child_info->first_name));
                $last_name = trim($db->escape($child_info->last_name));
                $dob = trim($db->escape($child_info->dob));
                $gender = trim($db->escape($child_info->gender));
                $allergies = trim($db->escape($child_info->allergies));
                $school_grade = trim($db->escape($child_info->school_grade));
                $qur_sch_stu_user_id = $child_info->qur_sch_stu_user_id;
                if (trim($qur_sch_stu_user_id) == '') {
                    $qur_sch_stu_user_id = "NULL";
                }
                $newPassword = generatePassword();
                //GET USER TYPE ID FOR STUDENT (UT03 FOR  STUDENT)
                $userTypeId = $db->get_var("select id from ss_usertype where user_type_code='UT03'");
                if ($newUsername != '') {
                    $db->query('BEGIN');
                    //ENTRY INTO USER TABLE
                    $db->query("insert into ss_user set username='" . $newUsername . "', password='" . md5($newPassword) . "',
				email='" . trim($db->escape($adm_info->primary_email)) . "', user_type_id='" . $userTypeId . "', is_email_verified=0, is_locked=0, is_active=1,
				session='" . $adm_info->session . "', created_on='" . date('Y-m-d H:i:s') . "'");
                    $user_id = $db->insert_id;
                    if ($user_id > 0) {
                        //GET USER TYPE ID FOR STUDENT (UT03 FOR  STUDENT)
                        $studentUserTypeId = $db->get_var("select id from ss_usertype where user_type_code='UT03'");
                        //ADDED ON 19-AUG-2018 - INSERT/ASSIGN 'STUDENT' ROLE TO STUDENT USER
                        $stu_usertype_added = $db->query("insert into ss_usertypeusermap set user_id='" . $user_id . "',
					user_type_id = '" . $studentUserTypeId . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
					created_on = '" . date('Y-m-d H:i') . "'");
                        //ENTRY IN STUDENT TABLE
                        $student_res = $db->query("insert into ss_student set user_id='" . $user_id . "', admission_date='" . date('Y-m-d') . "', first_name='" . $first_name . "', last_name='" . $last_name . "', dob='" . $dob . "', gender='" . $gender . "',  allergies='" . $allergies . "', school_grade='" . $school_grade . "', qur_sch_stu_user_id = " . $qur_sch_stu_user_id . ",
						created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
                        //SAVE DATA IN SESION TABLE
                        $student_session_res = $db->query("insert into ss_student_session_map set student_user_id='" . $user_id . "', session_id='" . $adm_info->session . "',
					status = 1, created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
                        if ($stu_usertype_added && $student_res) {
                            //CHECK IF FAMILY EXISTS
                            $userdata = $db->get_row("select * from ss_user where username='" . trim($adm_info->primary_email) . "'");
                            if ($userdata) {
                                $parents_user_id = $userdata->id;
                                $is_family_exists_in_db = true;
                            } else {
                                $parentsPassword = generatePassword();
                                $db->query("insert into ss_user set username='" . $db->escape(trim($adm_info->primary_email)) . "', password='" . md5($parentsPassword) . "',email='" . trim($db->escape($adm_info->primary_email)) . "', is_email_verified=1, is_locked=0, is_active=1,created_on='" . date('Y-m-d H:i:s') . "'");
                                $parents_user_id = $db->insert_id;
                                $is_family_exists_in_db = false;
                            }
                            $familyInfo = $db->get_row("select * from ss_family where primary_email ='" . trim($adm_info->primary_email) . "'");
                            if ($familyInfo) {
                                $family_id = $familyInfo->id;
                            } else {
                                if ($parents_user_id > 0) {
                                    //ENTRY IN FAMILY TABLE
                                    $sql = "insert into ss_family set login_pin='" . rand(1000, 9999) . "',
                                    user_id='" . $parents_user_id . "',
                                    father_first_name='" . trim($db->escape($adm_info->father_first_name)) . "',
                                    father_last_name='" . trim($db->escape($adm_info->father_last_name)) . "',
                                    father_area_code='" . trim($db->escape($adm_info->father_area_code)) . "',
                                    father_phone='" . trim($db->escape($adm_info->father_phone)) . "',
                                    mother_first_name='" . trim($db->escape($adm_info->mother_first_name)) . "',
                                    mother_last_name='" . trim($db->escape($adm_info->mother_last_name)) . "',
                                    mother_area_code='" . trim($db->escape($adm_info->mother_area_code)) . "',
                                    mother_phone='" . trim($db->escape($adm_info->mother_phone)) . "',
                                    primary_email='" . trim($db->escape($adm_info->primary_email)) . "',
                                    primary_contact='" . trim($db->escape($adm_info->primary_contact)) . "',
                                    forte_customer_token='" . trim($db->escape($adm_info->forte_customer_token)) . "',
                                    forte_payment_token='" . trim($db->escape($adm_info->forte_payment_token)) . "',
                                    secondary_email='" . trim($db->escape($adm_info->secondary_email)) . "',
                                    billing_address_1='" . trim($db->escape($adm_info->address_1)) . "',
                                    billing_address_2='" . trim($db->escape($adm_info->address_2)) . "',
                                    billing_city='" . trim($db->escape($adm_info->city)) . "',
                                    billing_post_code='" . trim($db->escape($adm_info->post_code)) . "',
                                    billing_state_id='" . trim($db->escape($adm_info->state)) . "',
                                    billing_entered_state='" . trim($db->escape($adm_info->state)) . "',
                                    billing_country_id='1',addition_notes='" . trim($db->escape($adm_info->addition_notes)) . "',
                                    payment_method='" . trim($db->escape($adm_info->payment_method)) . "',
                                    is_paid_registration_fee='".trim($db->escape($adm_info->is_paid))."',
                                    is_deleted='0',
                                    created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'";
                                    $family_res = $db->query($sql);
                                    $family_id = $db->insert_id;
                                } else {
                                    $parents_user_id = 0;
                                    $family_id = 0;
                                }
                            }
                            //--------------------------User Data Add in Account Table If Not Exist-------------------------------//
                            $accountInfo = $db->get_row("select id from ss_payment_accounts where user_id ='" . trim($parents_user_id) . "'");
                            if ($accountInfo->id > 0) {
                                $family_account_id = $accountInfo->id;
                            } else {
                                if ($family_id > 0) {
                                    $family_account = $db->query("INSERT INTO `ss_payment_accounts`(`user_id`, `system_account`, `created_by_user_id`, `created_on`) VALUES ('" . $parents_user_id . "','1','" . $_SESSION['icksumm_uat_login_userid'] . "','" . date('Y-m-d H:i:s') . "')");
                                    $family_account_id = $db->insert_id;
                                } else {
                                    $family_account_id = 0;
                                }
                            }
                            //--------------------------//User Data Add in Account Table If Not Exist-------------------------------//
                            //GET USER TYPE ID FOR PARENTS (UT05 FOR  PARENTS)
                            $parentsUserTypeId = $db->get_var("select id from ss_usertype where user_type_code='UT05'");
                            $check_exist_usertype = $db->get_row("select id from ss_usertypeusermap where user_id='" . $parents_user_id . "' AND user_type_id = '" . $parentsUserTypeId . "' AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
                            if (empty($check_exist_usertype)) {
                                //ADDED ON 16-AUG-2018 - INSERT/ASSIGN 'PARENTS' ROLE TO PARENTS USER
                                $usertype_added = $db->query("insert into ss_usertypeusermap set user_id='" . $parents_user_id . "', user_type_id = '" . $parentsUserTypeId . "',
						created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on = '" . date('Y-m-d H:i') . "'");
                            }
                            //ADDED ON 07SEPTEMBER2021
                            $role = $db->get_row("SELECT * FROM ss_role where `role` = 'Parents'");
                            $check_user_role = $db->get_row("select id from ss_user_role_map where status = 1 AND user_id='" . $parents_user_id . "' AND role_id='" . $role->id . "' ");
                            if (empty($check_exist_usertype)) {
                                $user_role = $db->query("insert into ss_user_role_map set status = 1,  user_id='" . $parents_user_id . "', role_id='" . $role->id . "',
						created_on = NOW(), created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
                            }
                            if ($parents_user_id > 0 && $family_id > 0 && $family_account_id > 0) {
                                //SET FAMILY ID IN STUDENT TABLE
                                $sql_ret = $db->query("update ss_student set family_id='" . $family_id . "', updated_on ='" . date('Y-m-d h:i:s') . "' where user_id='" . $user_id . "'");
                                if ($sql_ret) {
                                    $payment_res = $db->get_row("select * from ss_paymentcredentials where family_id='" . $family_id . "'");
                                    if (empty($payment_res)) {
                                        //ENTRY IN PAYMENT TABLE
                                        $adreq_pay = $db->get_row("select * from ss_sunday_sch_payment where sunday_sch_req_id='" . $reqno . "'");
                                        $payment_res = $db->query("insert into ss_paymentcredentials set sunday_sch_req_id='" . $reqno . "',
									family_id='" . $family_id . "',
									credit_card_type='" . $adreq_pay->credit_card_type . "', credit_card_no='" . $adreq_pay->credit_card_no . "',
									credit_card_exp='" . $adreq_pay->credit_card_exp . "', forte_payment_token='" . trim($db->escape($adreq_pay->forte_payment_token)) . "',
									default_credit_card = 1,  postal_code='" . $adreq_pay->postal_code . "',
									bank_acc_no='" . $adreq_pay->bank_acc_no . "', routing_no='" . $adreq_pay->routing_no . "',
									check_image='" . $adreq_pay->check_image . "',
									created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
                                        if ($payment_res) {
                                            $paymentInfoExist = true;
                                        } else {
                                            $paymentInfoExist = false;
                                        }
                                    } else {
                                        $paymentInfoExist = true;
                                    }
                                    if ($paymentInfoExist) {
                                        //ASSIGN GROUP
                                        $grp_upd = $db->query("insert into ss_studentgroupmap set student_user_id='" . $user_id . "', group_id='" . $group_id . "', latest=1,
									session = '" . $adm_info->session . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',
									created_on='" . date('Y-m-d H:i:s') . "'");
                                        if ($grp_upd > 0) {
                                            //SET USER ID IN ADMISSION REQUEST TABLE
                                            $sql_ret = $db->query("update ss_sunday_sch_req_child set user_id='" . $user_id . "', is_executed=1, updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $childno . "'");
                                            if ($sql_ret && $db->query('COMMIT') !== false) {
                                                //SEND LOGIN DETAILS TO PARENTS
                                                $emailbody_parents = "Dear Parent Assalamualaikum,<br><br>Congratulations! Group <strong>" . $group_name . "</strong> is allotted to your child " . $first_name . " " . $last_name . ". You can login in " . CENTER_SHORTNAME . " " . SCHOOL_NAME . " school parent's section using below information:";
                                                $emailbody_parents .= "<br><br><strong>Login URL:</strong> " . SITEURL . "login.php";
                                                $emailbody_parents .= "<br><strong>Email:</strong> " . trim($adm_info->primary_email);
                                                if ($is_family_exists_in_db == false) {
                                                    $emailbody_parents .= "<br><strong>Password:</strong> " . $parentsPassword;
                                                } else {
                                                    $emailbody_parents .= "<br><strong>Password:</strong>Please use password provided earlier or <a href='" . SITEURL . "forgot_password.php'>click here</a> to generate new password.";
                                                }
                                                $emailbody_parents .= '<br>
                                                <br>
                                                '.BEST_REGARDS_TEXT.'<br>
                                                ' . ORGANIZATION_NAME . ' Team';

                                                $mailservice_request_from = MAIL_SERVICE_KEY;
                                                $mail_service_array = array(
                                                    'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " - School Account Details",
                                                    'message' => $emailbody_parents,
                                                    'request_from' => $mailservice_request_from,
                                                    'attachment_file_name' => '',
                                                    'attachment_file' => '',
                                                    'to_email' => [$adm_info->primary_email],
                                                    'cc_email' => '',
                                                    'bcc_email' => '',
                                                );
                                                mailservice($mail_service_array);

                                                echo json_encode(array(
                                                    'code' => "1", 'msg' => 'Student added in active list successfully',
                                                    'user_id' => $user_id,
                                                ));
                                                exit;
                                            } else {
                                                $db->query('ROLLBACK');
                                                echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '7'));
                                                exit;
                                            }
                                        } else {
                                            $db->query('ROLLBACK');
                                            echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1'));
                                            exit;
                                        }
                                    } else {
                                        $db->query('ROLLBACK');
                                        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '9'));
                                        exit;
                                    }
                                } else {
                                    $db->query('ROLLBACK');
                                    echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '2'));
                                    exit;
                                }
                            } else {
                                $db->query('ROLLBACK');
                                echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '3'));
                                exit;
                            }
                        } else {
                            $db->query('ROLLBACK');
                            echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '4'));
                            exit;
                        }
                    } else {
                        $db->query('ROLLBACK');
                        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '5'));
                        exit;
                    }
                } else {
                    $db->query('ROLLBACK');
                    echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '6'));
                    exit;
                }
            } else {
                $db->query('ROLLBACK');
                echo json_encode(array('code' => "0", 'msg' => 'Error: Student already added', '_errpos' => '8'));
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => 'Error: Group has reached maximum limit', '_errpos' => '10'));
            exit;
        }
    }

    // Multiple Group
    elseif ($get_general_info->one_student_one_lavel == 0) {

        //$group_id = $_POST['group_id'];
        $reqno = $_POST['reqno'];
        $childno = $_POST['childno'];
        $db->query('BEGIN');
        $classes = $_POST['class'];
        $all_discounts = $_POST['discount'];
        $totalClass_count = count((array)$classes);
        $queryCount = 0;

        //ADDED ON 21AUG2021
        $adm_info = $db->get_row("select * from ss_sunday_school_reg where id='" . $reqno . "'");
        $fathers_name = $adm_info->father_first_name . ' ' . $adm_info->father_last_name;
        $check_internal_reg_status = $adm_info->internal_registration;
        $check_internal_reg_is_paid = $adm_info->is_paid;
        $check_internal_reg_amount = $adm_info->amount_received;

        $get_arr = array();
        foreach ($classes as $class_id) {
            $group_id = $_POST["group_id" . $class_id];
            $group_details = $db->get_row("select * from ss_groups where id = '" . $group_id . "'");

            $groupMaxLimit = $group_details->max_limit;
            $group_name = $group_details->group_name;
            $get_arr[$group_details->id] = $group_details->is_regis_open;
            //$groupCurStrength = $db->get_var("select count(1) from ss_studentgroupmap where group_id = '" . $group_id . "' and latest = 1");
            $groupCurStrength = $db->get_results("select * from ss_studentgroupmap inner join ss_user u on ss_studentgroupmap.student_user_id = u.id  where ss_studentgroupmap.group_id = '" . $group_id . "' and latest = 1 and  (u.`is_active` = 1 OR u.`is_active` = 2) AND u.`is_deleted` = 0 group by student_user_id");
            if (count((array)$groupCurStrength) < $groupMaxLimit) {
                $queryCount = $queryCount + 1;
            } else {
                $db->query('ROLLBACK');
                echo json_encode(array('code' => "0", 'msg' => 'Error: ' . $group_name . ' Group has reached maximum limit', '_errpos' => '10'));
                exit;
            }
        }

        if (false !== $key = array_search(0, $get_arr)) {
            $group_details = $db->get_row("select group_name from ss_groups where id = '" . $key . "'");
            echo json_encode(array('code' => "0", 'msg' => 'Error: Registration closed in ' . $group_details->group_name, '_errpos' => '11'));
            exit;
        }

        if ($totalClass_count == $queryCount) {
            $child_user_id = 0;
            $child_user_id = $db->get_var("select user_id from ss_sunday_sch_req_child where id='" . $childno . "'");
            if (trim($child_user_id) == '') {
                //COMMENTED ON 21AUG2021
                //$adm_info = $db->get_row("select * from ss_sunday_school_reg where id='" . $reqno . "'");
                $child_info = $db->get_row("select * from ss_sunday_sch_req_child where id='" . $childno . "'");
                $newUsername = generateUsername($child_info->first_name, $child_info->last_name, $child_info->dob);
                $first_name = trim($db->escape($child_info->first_name));
                $last_name = trim($db->escape($child_info->last_name));
                $dob = trim($db->escape($child_info->dob));
                $gender = trim($db->escape($child_info->gender));
                $school_grade = trim($db->escape($child_info->school_grade));
                $allergies = trim($db->escape($child_info->allergies));
                $qur_sch_stu_user_id = $child_info->qur_sch_stu_user_id;
                if (trim($qur_sch_stu_user_id) == '') {
                    $qur_sch_stu_user_id = "NULL";
                }

                $newPassword = generatePassword();
                //GET USER TYPE ID FOR STUDENT (UT03 FOR  STUDENT)
                $userTypeId = $db->get_var("select id from ss_usertype where user_type_code='UT03'");
                if ($newUsername != '') {
                    //ENTRY INTO USER TABLE
                    $db->query("insert into ss_user set username='" . $newUsername . "', password='" . md5($newPassword) . "', email='" . trim($db->escape($adm_info->primary_email)) . "', user_type_id='" . $userTypeId . "', is_email_verified=0, is_locked=0,
							is_active=1, session='" . $adm_info->session . "', created_on='" . date('Y-m-d H:i:s') . "'");
                    $user_id = $db->insert_id;
                    if ($user_id > 0) {

                        //GET USER TYPE ID FOR STUDENT (UT03 FOR  STUDENT)
                        $studentUserTypeId = $db->get_var("select id from ss_usertype where user_type_code='UT03'");
                        //ADDED ON 19-AUG-2018 - INSERT/ASSIGN 'STUDENT' ROLE TO STUDENT USER
                        $stu_usertype_added = $db->query("insert into ss_usertypeusermap set user_id='" . $user_id . "', user_type_id = '" . $studentUserTypeId . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
								created_on = '" . date('Y-m-d H:i') . "'");
                        //ENTRY IN STUDENT TABLE
                        $student_res = $db->query("insert into ss_student set user_id='" . $user_id . "', admission_date='" . date('Y-m-d') . "',
						first_name='" . $first_name . "', last_name='" . $last_name . "', dob='" . $dob . "', allergies='" . $allergies . "',
						school_grade='" . $school_grade . "', gender='" . $gender . "', qur_sch_stu_user_id = " . $qur_sch_stu_user_id . ",
						created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
                        //SAVE DATA IN SESION TABLE
                        $student_session_res = $db->query("insert into ss_student_session_map set student_user_id='" . $user_id . "', session_id='" . $adm_info->session . "',
						status = 1, created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
                        if ($stu_usertype_added && $student_res) {
                            //CHECK IF FAMILY EXISTS
                            $userdata = $db->get_row("select * from ss_user where username='" . trim($adm_info->primary_email) . "'");
                            if ($userdata) {
                                $parents_user_id = $userdata->id;
                                $is_family_exists_in_db = true;
                            } else {
                                $parentsPassword = generatePassword();
                                $db->query("insert into ss_user set username='" . $db->escape(trim($adm_info->primary_email)) . "', password='" . md5($parentsPassword) . "',
								email='" . trim($db->escape($adm_info->primary_email)) . "', is_email_verified=1, is_locked=0, is_active=1,
								created_on='" . date('Y-m-d H:i:s') . "'");
                                $parents_user_id = $db->insert_id;
                                $is_family_exists_in_db = false;
                            }



                            $familyInfo = $db->get_row("select * from ss_family where primary_email ='" . trim($adm_info->primary_email) . "'");
                            if ($familyInfo) {
                                $family_id = $familyInfo->id;
                            } else {
                                if ($parents_user_id > 0) {
                                    //ENTRY IN FAMILY TABLE
                                    $sql = "insert into ss_family set login_pin='" . rand(1000, 9999) . "',
                                    user_id='" . $parents_user_id . "',
                                    father_first_name='" . trim($db->escape($adm_info->father_first_name)) . "',
                                    father_last_name='" . trim($db->escape($adm_info->father_last_name)) . "',
                                    father_area_code='" . trim($db->escape($adm_info->father_area_code)) . "',
                                    father_phone='" . trim($db->escape($adm_info->father_phone)) . "',
                                    mother_first_name='" . trim($db->escape($adm_info->mother_first_name)) . "',
                                    mother_last_name='" . trim($db->escape($adm_info->mother_last_name)) . "',
                                    mother_area_code='" . trim($db->escape($adm_info->mother_area_code)) . "',
                                    mother_phone='" . trim($db->escape($adm_info->mother_phone)) . "',
                                    primary_email='" . trim($db->escape($adm_info->primary_email)) . "',
                                    primary_contact='" . trim($db->escape($adm_info->primary_contact)) . "',
                                    forte_customer_token='" . trim($db->escape($adm_info->forte_customer_token)) . "',
                                    forte_payment_token='" . trim($db->escape($adm_info->forte_payment_token)) . "',
                                    secondary_email='" . trim($db->escape($adm_info->secondary_email)) . "',
                                    billing_address_1='" . trim($db->escape($adm_info->address_1)) . "',
                                    billing_address_2='" . trim($db->escape($adm_info->address_2)) . "',
                                    billing_city='" . trim($db->escape($adm_info->city)) . "',
                                    billing_post_code='" . trim($db->escape($adm_info->post_code)) . "',
                                    billing_entered_state='" . trim($db->escape($adm_info->state)) . "',
                                    billing_country_id='1',addition_notes='" . trim($db->escape($adm_info->addition_notes)) . "',
                                    payment_method='" . trim($db->escape($adm_info->payment_method)) . "',
                                    is_paid_registration_fee='".trim($db->escape($adm_info->is_paid))."',
                                    is_deleted='0',
                                    created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'";

                                    $family_res = $db->query($sql);
                                    $family_id = $db->insert_id;
                                } else {
                                    $parents_user_id = 0;
                                    $family_id = 0;
                                }
                            }
                            
                            $family_payment_on_excution_check = get_payment_on_excution($family_id);
                            if (count((array)$family_payment_on_excution_check) == 0) {
                                
                                //--------------------------User Data Add in Account Table If Not Exist-------------------------------//
                                $accountInfo = $db->get_row("select id from ss_payment_accounts where user_id ='" . trim($parents_user_id) . "'");
                                if ($accountInfo->id > 0) {
                                    $family_account_id = $accountInfo->id;
                                } else {
                                    if ($family_id > 0) {
                                        $family_account = $db->query("INSERT INTO `ss_payment_accounts`(`user_id`, `system_account`, `created_by_user_id`, `created_on`) VALUES ('" . $parents_user_id . "','1','" . $_SESSION['icksumm_uat_login_userid'] . "','" . date('Y-m-d H:i:s') . "')");
                                        $family_account_id = $db->insert_id;
                                    } else {
                                        $family_account_id = 0;
                                    }
                                }
                                //--------------------------//User Data Add in Account Table If Not Exist-------------------------------//

                                //GET USER TYPE ID FOR PARENTS (UT05 FOR  PARENTS)
                                $parentsUserTypeId = $db->get_var("select id from ss_usertype where user_type_code='UT05'");
                                $check_exist_usertype = $db->get_row("select id from ss_usertypeusermap where user_id='" . $parents_user_id . "' AND user_type_id = '" . $parentsUserTypeId . "' AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
                                if (empty($check_exist_usertype)) {
                                    //ADDED ON 16-AUG-2018 - INSERT/ASSIGN 'PARENTS' ROLE TO PARENTS USER
                                    $usertype_added = $db->query("insert into ss_usertypeusermap set user_id='" . $parents_user_id . "', user_type_id = '" . $parentsUserTypeId . "',
                                    created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on = '" . date('Y-m-d H:i') . "'");
                                }
                                //ADDED ON 07SEPTEMBER2021
                                $role = $db->get_row("SELECT * FROM ss_role where `role` = 'Parents'");
                                $check_user_role = $db->get_row("select id from ss_user_role_map where status = 1 AND user_id='" . $parents_user_id . "' AND role_id='" . $role->id . "' ");
                                if (empty($check_exist_usertype)) {
                                    $user_role = $db->query("insert into ss_user_role_map set status = 1,  user_id='" . $parents_user_id . "', role_id='" . $role->id . "',
                                    created_on = NOW(), created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
                                }
                                if ($parents_user_id > 0 && $family_id > 0 && $family_account_id > 0) {
                                    //SET FAMILY ID IN STUDENT TABLE
                                    $sql_ret = $db->query("update ss_student set family_id='" . $family_id . "', updated_on ='" . date('Y-m-d h:i:s') . "' where user_id='" . $user_id . "'");
                                    
                                    if ($sql_ret) {
                                        $payment_res = $db->get_row("select * from ss_paymentcredentials where family_id='" . $family_id . "'");
                                        $adreq_pay = $db->get_row("select * from ss_sunday_sch_payment where sunday_sch_req_id='" . $reqno . "'");
                                       
                                        if (empty($payment_res) && !empty($adreq_pay->credit_card_no) && !empty($adreq_pay->credit_card_exp)) {

                                            //ENTRY IN PAYMENT TABLE
                                            $payment_res = $db->query("insert into ss_paymentcredentials set sunday_sch_req_id='" . $reqno . "', family_id='" . $family_id . "', credit_card_type='" . $adreq_pay->credit_card_type . "', credit_card_no='" . $adreq_pay->credit_card_no . "',
                                            credit_card_exp='" . $adreq_pay->credit_card_exp . "', postal_code='" . $adreq_pay->postal_code . "', bank_acc_no='" . $adreq_pay->bank_acc_no . "', credit_card_cvv='" . $adreq_pay->credit_card_cvv . "',
                                            default_credit_card=1, forte_payment_token='" . $adreq_pay->forte_payment_token . "', check_image='" . $adreq_pay->check_image . "',
                                            created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
                                            $pay_credential_id = $db->insert_id;

                                            if ($pay_credential_id > 0) {
                                                $paymentInfoExist = true;
                                            } else {
                                                $paymentInfoExist = false;
                                            }
                                            
                                            if ($check_internal_reg_status == 1 && $check_internal_reg_is_paid == 1) {
                                                $reg_payment_type = 1;
                                            }elseif($check_internal_reg_status == 0 && $check_internal_reg_is_paid == 1){
                                                $reg_payment_type = 2;
                                            }
                                            if($check_internal_reg_is_paid == 1){
                                                $payment_txn_id = $db->get_var("select id from ss_payment_txns where sunday_school_reg_id='" . $reqno . "'");
                                                
                                                if ($payment_txn_id > 0) {
                                                    $payment_res = $db->query("insert into ss_registration_fee_txns set registration_id='" . $reqno . "', family_id='" . $family_id . "', transaction_id='" . $payment_txn_id . "', amount='" . $check_internal_reg_amount . "', reg_payment_type='" . $reg_payment_type . "',created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at='" . date('Y-m-d H:i:s') . "'");
                                                }
                                            }
                                            
                                        } else {
                                            $paymentInfoExist = true;
                                            if ($check_internal_reg_status == 1 && $check_internal_reg_is_paid == 1) {
                                                $reg_payment_type = 1;
                                            }elseif($check_internal_reg_status == 0 && $check_internal_reg_is_paid == 1){
                                                $reg_payment_type = 2;
                                            }
                                            if($check_internal_reg_is_paid == 1){
                                                $payment_txn_id = $db->get_var("select id from ss_payment_txns where sunday_school_reg_id='" . $reqno . "'");
                                                
                                                if ($payment_txn_id > 0) {
                                                    $payment_res = $db->query("insert into ss_registration_fee_txns set registration_id='" . $reqno . "', family_id='" . $family_id . "', transaction_id='" . $payment_txn_id . "', amount='" . $check_internal_reg_amount . "', reg_payment_type='" . $reg_payment_type . "',created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at='" . date('Y-m-d H:i:s') . "'");
                                                }
                                            }
                                        }
                                        
                                        if ($paymentInfoExist) {
                                            //ASSIGN GROUP
                                            $groups = [];
                                            foreach ($classes as $class_id) {
                                                $group_id = $_POST["group_id" . $class_id];
                                                $grp_upd = $db->query("insert into ss_studentgroupmap set student_user_id='" . $user_id . "', session = '" . $adm_info->session . "',
                                                group_id='" . $group_id . "', class_id = '" . $class_id . "', latest=1, created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
                                                created_on='" . date('Y-m-d H:i:s') . "', updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
                                                updated_on='" . date('Y-m-d H:i:s') . "'");
                                                $groups[] = $group_id;
                                            }

                                            //Exist Family Schedule Payment Start
                                            if (!empty($userdata)) {

                                                $student_fees_items_query_first = "SELECT sfi.id AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification
                                                FROM ss_student_fees_items sfi
                                                INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                                                INNER JOIN ss_user u ON u.id = s.user_id
                                                INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                                                INNER JOIN ss_family f ON f.id = s.family_id
                                                INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                                                WHERE f.user_id = '" . $userdata->id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0  AND u.is_locked=0 AND u.is_active=1 AND pay.default_credit_card =1 ";

                                                $student_fees_items_query_second = " AND sfi.schedule_status = 2 AND sfi.schedule_status = 4 GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.id desc";

                                                $student_fees_items_query_third = " AND sfi.schedule_status != 2 AND sfi.schedule_status != 1  AND schedule_status != 4  AND sfi.schedule_payment_date >= '" . date('Y-m-d') . "' GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.schedule_payment_date asc";

                                                $check_point_sch_payment_stu = $db->get_results($student_fees_items_query_first . $student_fees_items_query_second);

                                                $sch_payment_stu = $db->get_results($student_fees_items_query_first . $student_fees_items_query_third);

                                                if (count((array)$sch_payment_stu) == 0 && count((array)$check_point_sch_payment_stu) > 0) {
                                                    $msg_text = " Now you can Schedule the payment for this family.";
                                                } else {
                                                    $msg_text = " ";

                                                    if (!empty($all_discounts)) {
                                                        foreach ($all_discounts as $key => $value) {
                                                            $in_discount_table = $db->query("INSERT INTO ss_student_feesdiscounts (student_user_id,fees_discount_id,`session`,`status`,created_by_user_id,created_on ) VALUES ($user_id,$key,'" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',1,'" . $_SESSION['icksumm_uat_login_userid'] . "','" . date('Y-m-d H:i:s') . "')");
                                                        }
                                                    }

                                                    $userDataAmount = student_fee_discount($user_id);
                                                    if (count((array)$sch_payment_stu) > 0 && !empty($userDataAmount)) {
                                                        foreach ($sch_payment_stu as $no => $row) {
                                                            $res = $db->query("insert into ss_student_fees_items set schedule_unique_id='" . $row->schedule_unique_id . "',student_user_id='" . $user_id . "',  original_schedule_payment_date= '" . $row->schedule_payment_date . "', schedule_payment_date = '" . $row->schedule_payment_date . "', amount='" . $userDataAmount . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', schedule_status = 0, created_at = '" . date('Y-m-d H:i') . "' ");
                                                        }
                                                        if ($user_id) {
                                       
                                                        $check_stu_sch=$db->get_results("SELECT * from ss_student_fees_items where student_user_id='" . $user_id . "'  and original_schedule_payment_date >= '" . date('Y-m-01') . "' 
                                                            and original_schedule_payment_date <= '" . date('Y-m-t') . "' ");
                                                            
                                                        if(count((array)$check_stu_sch)==0){
                                                            $schedule_unique_id = "U" . uniqid();

                                                            $db->query("insert into ss_student_fees_items set schedule_unique_id='" . $schedule_unique_id . "',student_user_id='" . $user_id . "',  original_schedule_payment_date= '" . date('Y-m-d') . "', schedule_payment_date = '" . date('Y-m-d') . "', amount='" . $userDataAmount  . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', schedule_status = 0, created_at = '" . date('Y-m-d H:i') . "' ");

                                                        }


                                                        }
                                                    }

                                                    $schedule_payment_cron = get_schedule_payment_cron($family_id);
                                           
                                                    if (count((array)$schedule_payment_cron) > 0) {

                                                        foreach ($schedule_payment_cron as $data) {

                                                            $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='" . $data->schedule_unique_id . "',  	family_id ='" . $data->family_id . "', sch_item_ids='" . $data->sch_item_ids . "', schedule_payment_date='" . $data->schedule_payment_date . "', total_amount ='" . $data->old_total_amount . "', wallet_amount = '" . $data->wallet_amount . "', cc_amount = '" . $data->cc_amount . "', schedule_status  = '" . $data->schedule_status . "', retry_count = '" . $data->retry_count . "', session  = '" . $data->session . "', is_approval  = '" . $data->is_approval . "', reason = '" . $data->reason . "', payment_unique_id = '" . $data->payment_unique_id . "', payment_response_code= '" . $data->payment_response_code . "', payment_response= '" . $data->payment_response . "', is_cancel=1,  created_at='" . $data->created_at . "', updated_at='" . $data->updated_at . "'");

                                                            $payment_sch_item_cron_backup_id = $db->insert_id;

                                                            if ($payment_sch_item_cron_backup_id > 0) {

                                                                $family_data = $db->get_row("SELECT GROUP_CONCAT(DISTINCT(id)) AS  sch_items_ids from ss_student_fees_items where schedule_unique_id='" . $data->schedule_unique_id . "'");

                                                                $latest_amount = ((int)$data->old_total_amount + (int)$userDataAmount);
                                                                $result = $db->query("update ss_payment_sch_item_cron set sch_item_ids = '" . $family_data->sch_items_ids . "', total_amount = '" . $latest_amount . "',  updated_at = '" . date('Y-m-d H:i') . "' where schedule_unique_id = '" . $data->schedule_unique_id . "'");

                                                                //// yaha code karna hai ss_invoice ka
                                                            }
                                                        }
                                                        
                                                    }
                                                   
                                                }
                                            }
                                            //Exist Family Schedule Payment End

                                            // when admin give discount to child,(add child) 


                                            /// end

                                            
                                            if ($grp_upd > 0) {
                                             
                                                //SET USER ID IN ADMISSION REQUEST TABLE
                                                $sql_ret = $db->query("update ss_sunday_sch_req_child set user_id='" . $user_id . "', is_executed=1, updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $childno . "'");
                                                //SEND LOGIN DETAILS TO PARENTS
                                                //SEND LOGIN DETAILS TO PARENTS
                                                $emailbody_parents .= "Dear $fathers_name Assalamu-alaikum,,<br><br>
                                                JazakAllah Khairan for enrolling <strong>" . $first_name . " " . $last_name . "</strong>. to " . CENTER_SHORTNAME . " " . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . " . <strong>" . $first_name . " " . $last_name . "</strong> levels are stated below. If you have any questions or concerns about the levels, please reach out to the staff and we will InshaAllah do our best to discuss any details. <br><br>You may also login to the parent’s portal using below information:";
                                                $emailbody_parents .= "<br><br><strong>Login URL:</strong> " . SITEURL . "login.php";
                                                $emailbody_parents .= "<br><strong>Email/Username:</strong> " . trim($adm_info->primary_email);
                                                if ($is_family_exists_in_db == false) {
                                                    $emailbody_parents .= "<br><strong>Password:</strong> " . $parentsPassword;
                                                } else {
                                                    $emailbody_parents .= "<br><strong>Password:</strong>Please use password provided earlier or <a href='" . SITEURL . "forgot_password.php'>click here</a> to generate new password.";
                                                }
                                                $stugroupclass = $db->get_results("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $user_id . "' order by m.id desc");
                                                $emailbody_parents .= '<br><br><table style="border:0" width="100%" cellspacing="0" cellpadding="3"><tbody><tr style="border:solid 1px #999;background: #dfdfdf;font-weight: bold;"><td style="border:solid 1px #999;background: #dfdfdf;font-weight: bold;"><strong>CLASS</th><th style="border:solid 1px #999;background: #dfdfdf;font-weight: bold;">GROUP</th></tr>';
                                                foreach ($stugroupclass as $row) {
                                                    $emailbody_parents .= '<tr style="border:solid 1px #999">';
                                                    $emailbody_parents .= '<td style="border:solid 1px #999">' . $row->class_name . '</td><td style="border:solid 1px #999">' . $row->group_name . '</td>';
                                                    $emailbody_parents .= '</tr>';
                                                }
                                                $emailbody_parents .= '<tbody></table>';
                                                // $emailbody_parents .= "<br><strong>Login Instructions for Parents</strong><ul style='padding: 0 10px;'><li>Select School</li><li>Enter your registered email</li><li>Enter password (Password field is case sensitive)</li></ul>";

                                                $emailbody_parents .= '<br>
                                                <br>
                                                '.BEST_REGARDS_TEXT.'<br>
                                                ' . ORGANIZATION_NAME . ' Team';

                                                //REMOVE NEW LINE CHARACTERS
                                                $emailbody_parents = str_replace("\n", "", $emailbody_parents);
                                                $emailbody_parents = str_replace("\r", "", $emailbody_parents);
                                                if ($sql_ret && $db->query('COMMIT') !== false) {

                                                    $sec_email = "";
                                                    if (trim($adm_info->secondary_email) != "") {
                                                        $sec_email = $adm_info->secondary_email;
                                                    }
                                                    $mailservice_request_from = MAIL_SERVICE_KEY;
                                                    $mail_service_array = array(
                                                        'subject' => CENTER_SHORTNAME . " " . SCHOOL_NAME . ' Account Details',
                                                        'message' => $emailbody_parents,
                                                        'request_from' => $mailservice_request_from,
                                                        'attachment_file_name' => '',
                                                        'attachment_file' => '',
                                                        'to_email' => [$adm_info->primary_email, $sec_email],
                                                        'cc_email' => '',
                                                        'bcc_email' => '',
                                                    );
                                                   
                                                    mailservice($mail_service_array);

                                                    echo json_encode(array('code' => "1", 'msg' => 'Student added in active list successfully' . $msg_text, 'user_id' => $user_id));
                                                    exit;
                                                } else {
                                                    $db->query('ROLLBACK');
                                                    echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '7'));
                                                    exit;
                                                }
                                            } else {
                                                $db->query('ROLLBACK');
                                                echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1'));
                                                exit;
                                            }
                                        } else {
                                            $db->query('ROLLBACK');
                                            echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '9'));
                                            exit;
                                        }
                                    } else {
                                        $db->query('ROLLBACK');
                                        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '2'));
                                        exit;
                                    }
                                } else {
                                    $db->query('ROLLBACK');
                                    echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '3'));
                                    exit;
                                }
                            } else {
                                $db->query('ROLLBACK');
                                echo json_encode(array('code' => "0", 'msg' => 'Error:TRY AFTER SOME TIME', '_errpos' => '101'));
                                exit;
                            }
                        } else {
                            $db->query('ROLLBACK');
                            echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '4'));
                            exit;
                        }
                    } else {
                        $db->query('ROLLBACK');
                        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '5'));
                        exit;
                    }
                } else {
                    $db->query('ROLLBACK');
                    echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '6'));
                    exit;
                }
            } else {
                $db->query('ROLLBACK');
                echo json_encode(array('code' => "0", 'msg' => 'Error: Student already added', '_errpos' => '8'));
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => 'Error: Group has reached maximum limit', '_errpos' => '10'));
            exit;
        }
    }
}
//====================SET INTERVIEW DATE========================
elseif ($_POST['action'] == 'schedule_interview') {
    $req_no = $_POST['sch_int_reqno'];
    $child_no = $_POST['sch_int_childno'];
    $interview_date = date('Y-m-d', strtotime($_POST['interview_date']));
    $res = $db->query("update ss_sunday_sch_req_child set interview_date='" . $interview_date . "',updated_on='" . time() . "' where id='" . $child_no . "'");
    if ($res > 0) {
        echo json_encode(array('code' => "1", 'msg' => 'Interview scheduled successfully'));
        exit;
    } else {
        echo json_encode(array('code' => "0", 'msg' => 'Error: Interview not scheduled'));
        exit;
    }
}
//==========================VIEW CHILD DETAILS===================
elseif ($_POST['action'] == 'view_child_detail') {
    $id = $_POST['reqno'];
    $childno = $_POST['childno'];
    $family = $db->get_row("select ss_sunday_school_reg.* from ss_sunday_school_reg where id='" . $id . "'");

    if (!empty($family->father_phone)) {
        if (strpos($family->father_phone, "-") == false) {
            $number = $family->father_phone;
            $formatted_number = "$number[0]$number[1]$number[2]-$number[3]$number[4]$number[5]-$number[6]$number[7]$number[8]$number[9]";
            $family->father_phone = $formatted_number;
        }
    }


    if ($family->primary_contact == 'Father') {
        $primary_contact = '1st Parent';
    } else {
        $primary_contact = '2nd Parent';
    }
    $students = $db->get_results("SELECT ch.user_id, ch.dob, ch.allergies, ch.school_grade, CONCAT(ch.first_name,' ',COALESCE(ch.last_name, '')) AS student_name, reg.amount_received,reg.internal_registration, reg.is_waiting FROM ss_sunday_sch_req_child ch INNER JOIN ss_sunday_school_reg reg ON reg.id = ch.sunday_school_reg_id  WHERE ch.sunday_school_reg_id = '" . $id . "' AND ch.id = '" . $childno . "' ");
   //
    $check = $db->get_row("select is_new_registration_open, is_new_registration_free, new_registration_fees_form_head, new_registration_fees from ss_client_settings where status = 1");
    $family->amount_received;
    $ramount = round($family->amount_received, 0);

    if ($students[0]->internal_registration == 1 && $students[0]->is_waiting == 0) {
        if ($students[0]->amount_received == 0 || $students[0]->amount_received!=0) {
            $ramount = $students[0]->amount_received . ' (Student is added from  ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel)';
        } else {
            $ramount = $students[0]->amount_received;
        }
    } elseif ($students[0]->internal_registration == 0 && $students[0]->is_waiting == 1) {
        $ramount = $students[0]->amount_received . ' (Waiting List)';
    } elseif ($students[0]->internal_registration == 0 && $students[0]->is_waiting == 0 && $students[0]->amount_received == 0) {
        $ramount = $students[0]->amount_received . ' (Student is added from  ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel)';
    } elseif ($students[0]->internal_registration == 1 && $students[0]->is_waiting == 2) {
        $ramount = $students[0]->amount_received . ' (Student is added from  ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel)';
    }
    foreach ($students as $stu) {
        $final_amount_all = $stu->amount_received;
    }

    $retStr .= '<legend class="text-semibold">Registration Payment Information</legend>
	<div class="row viewonly">';

    $retStr .= '<div class="col-md-4">
		<label>Registration Amount :</label> '.$currency . ($ramount) . ' 
		</div>';
        
    $paymentcred = $db->get_row("select * from ss_sunday_sch_payment where sunday_sch_req_id='" . $id . "'");
    $credit_card_no = str_replace(' ', '', base64_decode($paymentcred->credit_card_no));
    $credit_card_exp = base64_decode($paymentcred->credit_card_exp);
    $credit_card_cvv = base64_decode($paymentcred->credit_card_cvv);
    $credit_card_expAry = explode('-', $credit_card_exp);
    $credit_card_exp_month = $credit_card_expAry[0];
    $credit_card_exp_year = $credit_card_expAry[1];
    if (isset($credit_card_no) && !empty($credit_card_no)) {
        $retStr .= '<div class="col-md-4">
				<label>Last 4 digits of credit card :</label> ************ ' . substr($credit_card_no, -4) . '
				</div>';
    }

    if (isset($credit_card_exp_month) && isset($credit_card_exp_year)) {
        $retStr .= '<div class="col-md-3">
				<label>Expiry Date :</label> ' . $credit_card_exp_month . '/' . $credit_card_exp_year . '
			</div>';
    }

    $retStr .= '</div>';
    $retStr .= '<legend class="text-semibold">Child Information</legend>';
    $i = 1;
    foreach ($students as $stu) {
        $dob = date('Y-m-d', strtotime($stu->dob));
        $from = new DateTime($dob);
        $to = new DateTime('today');
        $stu_age = $from->diff($to)->y;
        $age = $stu_age . ' Yrs';
        $group = $db->get_row("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "'");
        $retStr .= '<div class="row viewonly">';
        $retStr .= '<div class="col-md-3">
				<label>Child ' . $i . ': </label> ' . $stu->student_name . '</div>
				<div class="col-md-3">
				<label>Grade:</label> ' . $stu->school_grade . '</div>';
        $retStr .= '</div>';
        $retStr .= '<div class="row viewonly">
		<div class="col-md-3">
		<label>Date of Birth: </label>' . my_date_changer($stu->dob) . '
		</div>
		<div class="col-md-3">
		<label>Age:</label>' . $age . '
		</div>';
        if (!empty($stu->allergies)) {
            $retStr .= '<div class="col-md-3"><label>Allergies:</label> ' . $stu->allergies . ' </div>';
        }
        $retStr .= '</div>';
        $i++;
    }
    $retStr .= '<legend class="text-semibold">Parent Information</legend>
		<div class="row viewonly">
		<div class="col-md-4">
		<label>1st Parent Name:</label>' . $family->father_first_name . ' ' . $family->father_last_name . '
		</div>
		<div class="col-md-3">
		<label>Phone:</label>' . internal_phone_check($family->father_phone) . '
		</div>
		<div class="col-md-5">
		<label>Email:</label>' . $family->primary_email . '
		</div>
		</div>';
    if (!empty($family->mother_first_name)) {
        if (empty($family->secondary_email)) {
            $family->secondary_email = "N/A";
        }
        $retStr .= '<div class="row viewonly">
		<div class="col-md-4">
		<label>2nd Parent Name:</label>' . $family->mother_first_name . ' ' . $family->mother_last_name . '
		</div>
		<div class="col-md-3">
		<label>Phone:</label>' . internal_phone_check($family->mother_phone) . '
		</div>
		<div class="col-md-5">
		<label>Email:</label>' . $family->secondary_email . '
		</div>
		</div>';
    }
    $retStr .= '<div class="row viewonly">
		<div class="col-md-4 col-md-6">
		<label>Primary Contact:</label>' . $primary_contact . '
		</div>
		</div>';
    $billing_state = $db->get_var("select state from ss_state where id = '" . $family->state . "'");

    $full_address = "";
    if (!empty($family->address_1)) {
        $full_address .= $family->address_1;
    }

    if (!empty($family->address_2)) {
        $full_address .= ', ' . $family->address_2;
    }
    if (!empty($family->city)) {
        $full_address .= ', ' . $family->city;
    }
    if (!empty($billing_state)) {
        $full_address .= ', ' . $billing_state;
    } elseif (!empty($family->state)) {
        $full_address .= ', ' . $family->state;
    }
    if (!empty($family->post_code)) {
        $full_address .= ', ' . $family->post_code;
    }

    $retStr .= '<div class="row viewonly"><div class="col-md-12"><label>Address:</label>' . $full_address . '</div></div>';

    if (!empty($family->addition_notes)) {
        $retStr .= '<div class="row viewonly"><div class="col-md-12"><label>Additional Noted:</label>' . $family->addition_notes . '</div></div>';
    }
    echo $retStr;
    exit;
} elseif ($_POST['action'] == 'edit_admission_request') {

    $country = $db->get_var("select country from ss_country where id='".trim($db->escape($_POST['country_id']))."'");


    $family_id = $db->escape(trim($_POST['family_reg_id']));
    $child1_id = $db->escape(trim($_POST['child1_reg_id']));
    $child2_id = $db->escape(trim($_POST['child2_reg_id']));
    $child3_id = $db->escape(trim($_POST['child3_reg_id']));
    $child4_id = $db->escape(trim($_POST['child4_reg_id']));
    $parent1_email = $db->escape(trim($_POST['parent1_email']));
    $parent2_email = $db->escape(trim($_POST['parent2_email']));
    $which_is_primary_email = $db->escape(trim($_POST['which_is_primary_email']));

    if ($which_is_primary_email == "parent1") {
        $primary_email = $parent1_email;
        $secondary_email = $parent2_email;
        $primary_contact = 'Father';
    } else {
        $primary_email = $parent2_email;
        $secondary_email = $parent1_email;
        $primary_contact = 'Mother';
    }

    if (!empty(trim($_POST['child_dob1']))) {
        $child_dob1 = "'" . date('Y-m-d', strtotime($_POST['child_dob1'])) . "'";
        $child_dob_email1 = date('m/d/Y', strtotime($_POST['child_dob1']));
    } else {
        $child_dob1 = "NULL";
        $child_dob_email1 = "NULL";
    }

    if (!empty(trim($_POST['child_dob2']))) {
        $child_dob2 = "'" . date('Y-m-d', strtotime($_POST['child_dob2'])) . "'";
        $child_dob_email2 = date('m/d/Y', strtotime($_POST['child_dob2']));
    } else {
        $child_dob2 = "NULL";
        $child_dob_email2 = "NULL";
    }
    if (!empty(trim($_POST['child_dob3']))) {
        $child_dob3 = "'" . date('Y-m-d', strtotime($_POST['child_dob3'])) . "'";
        $child_dob_email3 = date('m/d/Y', strtotime($_POST['child_dob3']));
    } else {
        $child_dob3 = "NULL";
        $child_dob_email3 = "NULL";
    }
    if (!empty(trim($_POST['child_dob4']))) {
        $child_dob4 = "'" . date('Y-m-d', strtotime($_POST['child_dob4'])) . "'";
        $child_dob_email4 = date('m/d/Y', strtotime($_POST['child_dob4']));
    } else {
        $child_dob4 = "NULL";
        $child_dob_email4 = "NULL";
    }

    $db->query("BEGIN");
    $studentRegister = $db->query("update ss_sunday_school_reg set
                        father_first_name='" . trim($db->escape($_POST['parent1_first_name'])) . "',
                        father_last_name='" . trim($db->escape($_POST['parent1_last_name'])) . "',
                        father_phone='" . trim($db->escape($_POST['parent1_phone'])) . "',
                        father_email='" . $parent1_email . "',
                        mother_first_name='" . trim($db->escape($_POST['parent2_first_name'])) . "',
                        mother_last_name='" . trim($db->escape($_POST['parent2_last_name'])) . "',
                        mother_phone='" . trim($db->escape($_POST['parent2_phone'])) . "',
                        mother_email='" . $parent2_email . "',
                        primary_email='" . $primary_email . "',
                        secondary_email='" . $secondary_email . "',
                        primary_contact='" . $primary_contact . "',
                        address_1='" . trim($db->escape($_POST['address_1'])) . "',
                        address_2='" . trim($db->escape($_POST['address_2'])) . "',
                        class_session='" . trim($db->escape($_POST['class_session'])) . "',
                        city='" . trim($db->escape($_POST['city'])) . "',
                        state='" . trim($db->escape($_POST['state'])) . "',
                        country_id='" . trim($db->escape($_POST['country_id'])) . "',
                        post_code='" . trim($db->escape($_POST['post_code'])) . "',
                        addition_notes='" . trim($db->escape($_POST['addition_notes'])) . "',
                        updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $family_id . "'");

    if ($studentRegister) {
        if ($_POST['child_first_name1'] != '') {
            $data = $db->query("update ss_sunday_sch_req_child set
			sunday_school_reg_id='" . $family_id . "',
			first_name='" . trim($_POST['child_first_name1']) . "',
			last_name='" . trim($_POST['child_last_name1']) . "',
			dob=" . $child_dob1 . ",
			gender='" . trim($_POST['child_gender1']) . "',
			allergies='" . trim($_POST['child_allergies1']) . "',
			school_grade = '" . $_POST['child_grade1'] . "',
			updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $child1_id . "'");
        }
        if ($_POST['child_first_name2'] != '') {
            $data = $db->query("update ss_sunday_sch_req_child set
			sunday_school_reg_id='" . $family_id . "',
			first_name='" . trim($_POST['child_first_name2']) . "',
			last_name='" . trim($_POST['child_last_name2']) . "',
			dob=" . $child_dob2 . ",
			gender='" . trim($_POST['child_gender2']) . "',
			allergies='" . trim($_POST['child_allergies2']) . "',
			school_grade = '" . $_POST['child_grade2'] . "',
			updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $child2_id . "'");
        }
        if ($_POST['child_first_name3'] != '') {
            $data = $db->query("update ss_sunday_sch_req_child set
			sunday_school_reg_id='" . $family_id . "',
			first_name='" . trim($_POST['child_first_name3']) . "',
			last_name='" . trim($_POST['child_last_name3']) . "',
			dob=" . $child_dob3 . ",
			gender='" . trim($_POST['child_gender3']) . "',
			allergies='" . trim($_POST['child_allergies3']) . "',
			school_grade = '" . $_POST['child_grade3'] . "',
			updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $child3_id . "'");
        }
        if ($_POST['child_first_name4'] != '') {
            $data = $db->query("update ss_sunday_sch_req_child set
			sunday_school_reg_id='" . $family_id . "',
			first_name='" . trim($_POST['child_first_name4']) . "',
			last_name='" . trim($_POST['child_last_name4']) . "',
			dob=" . $child_dob4 . ",
			gender='" . trim($_POST['child_gender4']) . "',
			allergies='" . trim($_POST['child_allergies4']) . "',
			school_grade = '" . $_POST['child_grade4'] . "',
			updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $child4_id . "'");
        }
    }

    if ($studentRegister && $data && $db->query('COMMIT') !== false) {
        if (!empty($_POST['state'])) {

            $state_name = $db->get_var("SELECT state FROM ss_state WHERE id='" . $_POST['state'] . "' AND is_active=1 ");
        } else {
            $state_name = "";
        }
        $next_year = date('Y')+1;
        $final_year = date('Y') - $next_year;
        $emailbody = '<table style="border:0;font-family: Verdana, Geneva, sans-serif;" cellpadding="5"><tbody>
        <tr>
        <td colspan="4"> Dear Parents Assalamu-alaikum<br>
        <br>
        Your admission request details has been updated for ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . '. We appreciate your help and cooperation. ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . '  administrator will get back if any other info is needed. <br>
        <br>
        Please feel free to contact us at <a href="mailto:' . SCHOOL_GEN_EMAIL . '" target="_blank">' . SCHOOL_GEN_EMAIL . '</a><br>
        <br></td>
        </tr>
        <tr>
        <td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
        <tbody>';

        $emailbody .= '<tr style="border:solid 1px #999">
        <td style="border:solid 1px #999"><strong>1st Parent Name</strong></td>
        <td style="border:solid 1px #999">' . $_POST['parent1_first_name'] . ' ' . $_POST['parent1_last_name'] . '</td>
        <td style="border:solid 1px #999"><strong>1st Parent Phone</strong></td>
        <td style="border:solid 1px #999">' . $_POST['parent1_phone'] . '</td>
        </tr>';
        if (!empty($_POST['parent2_first_name'])) {
            $emailbody .= '<tr style="border:solid 1px #999">
            <td style="border:solid 1px #999"><strong>2nd Parent Name</strong></td>
            <td style="border:solid 1px #999">' . $_POST['parent2_first_name'] . ' ' . $_POST['parent2_last_name'] . '</td>
            <td style="border:solid 1px #999"><strong>2nd Parent Phone</strong></td>
            <td style="border:solid 1px #999">' . $_POST['parent2_phone'] . '</td>
            </tr>';
        }
        $emailbody .= '<tr style="border:solid 1px #999">
        <td style="border:solid 1px #999"><strong>1st Parent Email</strong></td>
        <td style="border:solid 1px #999">' . $parent1_email . '</td>';

        if (!empty($_POST['parent2_email'])) {
            $emailbody .= '<td style="border:solid 1px #999"><strong>2nd Parent Email</strong></td>
        <td style="border:solid 1px #999">' . $parent2_email . '</td>';
        } else {
            $emailbody .= '<td style="border:solid 1px #999;"></td>
            <td style="border:solid 1px #999;"></td>';
        }
        $emailbody .= '</tr>
        <tr style="border:solid 1px #999">
        <td style="border:solid 1px #999"><strong>Address 1</strong></td>
        <td style="border:solid 1px #999">' . $_POST['address_1'] . '</td>
        <td style="border:solid 1px #999"><strong>Address 2</strong></td>
        <td style="border:solid 1px #999">' . $_POST['address_2'] . '</td>
        </tr>
        <tr style="border:solid 1px #999">
        <td style="border:solid 1px #999"><strong>City</strong></td>
        <td style="border:solid 1px #999">' . $_POST['city'] . '</td>
        <td style="border:solid 1px #999"><strong>State</strong></td>
        <td style="border:solid 1px #999">' . $state_name . '</td>
        </tr>
        <tr style="border:solid 1px #999">
        <td style="border:solid 1px #999"><strong>Country</strong></td>
        <td style="border:solid 1px #999">'.$country.'</td>
        <td style="border:solid 1px #999"><strong>Zipcode</strong></td>
        <td style="border:solid 1px #999">' . $_POST['post_code'] . '</td>
        </tr>';
        if (!empty($_POST['child_first_name1'])) {
            $emailbody .= '<tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999;width:20%"><strong>Child 1 Name</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . $_POST['child_first_name1'] . ' ' . $_POST['child_last_name1'] . '</td>
                        <td style="border:solid 1px #999;width:20%"><strong>Child 1 Gender</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . (trim($_POST['child_gender1']) == "f" ? "Female" : "Male") . '</td>
                        </tr>
                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 1 DoB</strong></td>
                        <td style="border:solid 1px #999">' . my_date_changer($child_dob_email1) . '</td>
                        <td style="border:solid 1px #999"><strong>Child '.$final_year.' School Grade</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_grade1'] . '</td>
                        </tr>

                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 1 Allergies</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_allergies1'] . '</td>
                        <td style="border:solid 1px #999;"></td>
                        <td style="border:solid 1px #999;"></td>
                        </tr>';
        }
        if (!empty($_POST['child_first_name2'])) {
            $emailbody .= '<tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999;width:20%"><strong>Child 2 Name</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . $_POST['child_first_name2'] . ' ' . $_POST['child_last_name2'] . '</td>
                        <td style="border:solid 1px #999;width:20%"><strong>Child 2 Gender</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . (trim($_POST['child_gender2']) == "f" ? "Female" : "Male") . '</td>
                        </tr>
                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 2 DoB</strong></td>
                        <td style="border:solid 1px #999">' . my_date_changer($child_dob_email2) . '</td>
                        <td style="border:solid 1px #999"><strong>Child '.$final_year.' School Grade</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_grade2'] . '</td>
                        </tr>

                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 2 Allergies</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_allergies2'] . '</td>
                        <td style="border:solid 1px #999;"></td>
                        <td style="border:solid 1px #999;"></td>
                        </tr>';
        }
        if (!empty($_POST['child_first_name3'])) {
            $emailbody .= '<tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999;width:20%"><strong>Child 3 Name</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . $_POST['child_first_name3'] . ' ' . $_POST['child_last_name3'] . '</td>
                        <td style="border:solid 1px #999;width:20%"><strong>Child 3 Gender</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . (trim($_POST['child_gender3']) == "f" ? "Female" : "Male") . '</td>
                        </tr>
                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 3 DoB</strong></td>
                        <td style="border:solid 1px #999">' . my_date_changer($child_dob_email3) . '</td>
                        <td style="border:solid 1px #999"><strong>Child '.$final_year.' School Grade</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_grade3'] . '</td>
                        </tr>

                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 3 Allergies</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_allergies3'] . '</td>
                        <td style="border:solid 1px #999;"></td>
                        <td style="border:solid 1px #999;"></td>
                        </tr>';
        }
        if (!empty($_POST['child_first_name4'])) {
            $emailbody .= '<tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999;width:20%"><strong>Child 4 Name</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . $_POST['child_first_name4'] . ' ' . $_POST['child_last_name4'] . '</td>
                        <td style="border:solid 1px #999;width:20%"><strong>Child 4 Gender</strong></td>
                        <td style="border:solid 1px #999;width:30%">' . (trim($_POST['child_gender4']) == "f" ? "Female" : "Male") . '</td>
                        </tr>
                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 4 DoB</strong></td>
                        <td style="border:solid 1px #999">' . my_date_changer($child_dob_email4) . '</td>
                        <td style="border:solid 1px #999"><strong>Child '.$final_year.' School Grade</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_grade4'] . '</td>
                        </tr>

                        <tr style="border:solid 1px #999">
                        <td style="border:solid 1px #999"><strong>Child 4 Allergies</strong></td>
                        <td style="border:solid 1px #999">' . $_POST['child_allergies4'] . '</td>
                        <td style="border:solid 1px #999;"></td>
                        <td style="border:solid 1px #999;"></td>
                        </tr>';
        }
        $emailbody .= '
            </tbody>
            </table>';
        $emailbody .= '<br>
            <br>
            '.BEST_REGARDS_TEXT.'<br>
            ' . ORGANIZATION_NAME . ' Team</td>
            </tr>
            </tbody>
            </table>';

        $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));
        $email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - Admission registration information updated';
        //$mailservice_request_from = SUPPORT_EMAIL;
        $sec_email = "";
        if (trim($secondary_email) != '') {
            $sec_email = $secondary_email;
        }
        $bcc_email = "";
        foreach ($emails_bcc as $bcc) {
            $bcc_email = $bcc;
        }

        $cc_email = "";
        foreach ($emails_cc as $cc) {
            $cc_email = $cc;
        }
        $mail_service_array = array(
            'subject' => $email_subject,
            'message' => $emailbody,
            'request_from' => MAIL_SERVICE_KEY,
            'attachment_file_name' => [],
            'attachment_file' => [],
            'to_email' => [$primary_email, $sec_email],
            'cc_email' => [$cc_email],
            'bcc_email' => $bcc_email
        );

        mailservice($mail_service_array);
        echo json_encode(array('code' => "1", 'msg' => 'Admission request updated.'));
        exit;
    } else {
        echo json_encode(array('code' => "0", 'msg' => 'Admission request not updated.', '_errpos' => 13));
        $db->query('ROLLBACK');
        exit;
    }
}
