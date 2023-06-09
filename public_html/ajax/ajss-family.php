<?php
include_once "../includes/config.php";
include_once "../includes/FortePayment.class.php";


$forte_configarray = array(
    'FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID,
    'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY,
    'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID,
    'FORTE_LOCATION_ID' => FORTE_LOCATION_ID,
    'ENVIRONMENT' => ENVIRONMENT,
);
$fortePayment = new FortePayment($forte_configarray);


/////////////////////////////////////////////////////// approve_email ////////////////////////////////////////////
//Approve change primary email request by Admin
if ($_POST['action'] == 'approve_email') {   

    $db->query("BEGIN");
    $main_usertable_id = $_POST['mainid'];
    $requested_userid = $_POST['id'];
    $email = $_POST['email'];
    $updated_by_user_id = $_POST['mainid'];
    $is_exist = $db->get_var("SELECT * from ss_user where email = '" . $_POST['email'] . "'");
    $old_email = $db->get_var("SELECT email from ss_user where id = '".$_POST['mainid'] ."'");
    
//usertype = 0 Parent
//usertype = 1 Staff
    if (!$is_exist) {
        if($_POST['user_type']==0){
            
            $updated_by_user_id = $_POST['mainid'];
            $a = $db->query("update ss_change_email_request set	status=1, updated_by_user_id='" . $updated_by_user_id . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $requested_userid . "'");

            $b = $db->query("update ss_user set username='" . $email . "',email='" . $email . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $main_usertable_id . "'");

            $c = $db->query("update ss_family set primary_email='" . $email . "', updated_on='" . date('Y-m-d H:i:s') . "' where user_id='" . $main_usertable_id . "'");

            if ($a && $b && $c && $db->query('COMMIT') !== false) {

                //call eMail service to send updated Email information 
                $stuinfo = $db->get_row("select fam.father_first_name,fam.father_last_name,fam.primary_email,fam.secondary_email from ss_family fam where fam.user_id = '" . $main_usertable_id . "'");
                $new_email_body = "Dear Parent Assalamu-alaikum,<br>";
                $new_email_body .= "Primary email has been changed successfully, Details are mentioned below <br>
                <strong>Name</strong>- " . $stuinfo->father_first_name . " " . $stuinfo->father_last_name . "<br>
                <strong>New Email</strong>- " .$_POST['email']. "<br>"; 
                $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
                $new_email_body .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                $mail_service_array = array(
                    'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - Primary Email updated sucessfully',
                    'message' => $new_email_body,
                    'request_from' => MAIL_SERVICE_KEY,
                    'attachment_file_name' => [],
                    'attachment_file' => [],
                    'to_email' => [$old_email],
                    'cc_email' => '',
                    'bcc_email' => ''
                );
                mailservice($mail_service_array);
                echo json_encode(array('code' => "1", 'msg' => "<p class='text-success text-center h1'>successfully updated.</p>", '_errpos' => 1));
                exit;
        }
    }else if($_POST['user_type']==1){
            $updated_by_user_id = $_POST['mainid'];
            $a = $db->query("update ss_change_email_request set	status=1, updated_by_user_id='" . $updated_by_user_id . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $requested_userid . "'");
            $b = $db->query("update ss_user set username='" . $email . "',email='" . $email . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $main_usertable_id . "'");

            $hybrid_account_check = $db->get_row("SELECT * FROM ss_usertypeusermap where user_id='" . $main_usertable_id . "' and  user_type_id=2 and session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
            
            if(!empty($hybrid_account_check)){
             $c = $db->query("update ss_family set primary_email='" . $email . "', updated_on='" . date('Y-m-d H:i:s') . "' where user_id='" . $main_usertable_id . "'");
            }

            if ($a && $b && $db->query('COMMIT') !== false) {
                //call eMail service to send updated Email information
                $staffinfo = $db->get_row("select stf.first_name,stf.last_name from ss_staff stf  where stf.user_id = '" . $main_usertable_id . "' ");
                $new_email_body = "Dear Staff Assalamu-alaikum,<br>";
                $new_email_body .= "Primary email has been changed successfully, Details are mentioned below - <br>
                <strong>Name</strong>- " . $staffinfo->first_name . " " . $staffinfo->last_name . "<br>
                <strong>New Email</strong>-" .$_POST['email']. "<br>"; 
                $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
                $new_email_body .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                $mail_service_array = array(
                    'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  Primary Email updated sucessfully',
                    'message' => $new_email_body,
                    'request_from' => MAIL_SERVICE_KEY,
                    'attachment_file_name' => [],
                    'attachment_file' => [],
                    'to_email' => [$old_email],
                    'cc_email' => '',
                    'bcc_email' => ''
                );
                mailservice($mail_service_array);
                echo json_encode(array('code' => "1", 'msg' => "<p class='text-success text-center h1'>successfully updated.</p>", '_errpos' => 1));
                exit;    
        }   
    } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger '>Failed Message.</p>", '_errpos' => 2));
            exit;
        }

    }else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'>This Email id already exits in the system.</p>", '_errpos' => 3));
        exit;
    }
}

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}

//==========================LIST ALL STAFF FOR ADMIN=====================
if ($_GET['action'] == 'list_family') {
    //  if (in_array("su_family_list", $_SESSION['login_user_permissions'])) {
    $finalAry = array();

    $all_families = $db->get_results("SELECT DISTINCT f.id, f.user_id as family_user_id, CONCAT(father_first_name,' ',COALESCE(father_last_name, '')) AS father_name
                , CONCAT(mother_first_name,' ',COALESCE(mother_last_name, '')) AS mother_name , f.primary_email, f.father_phone, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name,
                billing_city AS city, billing_post_code AS zipcode, f.forte_customer_token, f.billing_address_1 FROM ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 ORDER BY father_name ASC", ARRAY_A);

    if ($_SESSION['icksumm_uat_login_usertype'] == "Teacher") {
        $user_id = $_SESSION['icksumm_uat_login_userid'];
        $get_classes_teacher = $db->get_results("SELECT clt.group_id,clt.class_id FROM ss_staffclasstimemap sct INNER JOIN ss_classtime clt on clt.id = sct.classtime_id WHERE sct.staff_user_id = '" . $user_id . "' and sct.active=1 and clt.is_active =1 and clt.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $total_fam = [];
        foreach ($get_classes_teacher as $clsgrp) {
            $get_family = $db->get_results("SELECT s.family_id FROM ss_studentgroupmap sgm inner join ss_student s on s.user_id = sgm.student_user_id  where sgm.group_id = '" . $clsgrp->group_id . "' and  sgm.class_id = '" . $clsgrp->class_id . "' and sgm.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  ");
            foreach ($get_family as $fmget) {
                $total_fam[] = $fmget->family_id;
            }
        }
        $total_fm = array_unique($total_fam);
        $family_set = implode(',', $total_fm);

        $all_families = $db->get_results("SELECT DISTINCT f.id, f.user_id as family_user_id, CONCAT(father_first_name,' ',COALESCE(father_last_name, '')) AS father_name
                , CONCAT(mother_first_name,' ',COALESCE(mother_last_name, '')) AS mother_name , f.primary_email, f.father_phone
                , billing_city AS city, billing_post_code AS zipcode FROM ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_deleted = 0 and f.id IN (" . $family_set . ") ORDER BY father_name ASC", ARRAY_A);
    }

    for ($i = 0; $i < count($all_families); $i++) {
        $pay_credencial = $db->get_var("SELECT forte_payment_token FROM ss_paymentcredentials WHERE family_id = '". $all_families[$i]['id']."' and default_credit_card = 1");

        $sqlQuery = "SELECT sfi.id AS sch_item_id, u.is_active,
            sfi.original_schedule_payment_date,
            sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount,
            sfi.schedule_status,sfi.schedule_notification, s.family_id, s.user_id,
            f.father_first_name, f.father_last_name,
            f.father_phone, f.primary_email,
            pay.credit_card_no
            FROM ss_student_fees_items sfi
            INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
            INNER JOIN ss_user u ON u.id = s.user_id
            INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
            INNER JOIN ss_family f ON f.id = s.family_id
            LEFT JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
            WHERE s.family_id = '" . $all_families[$i]['id'] . "'
            AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
            AND pay.default_credit_card= 1
            AND u.is_deleted = 0 ";

        $sqlQuery_last = " GROUP BY sfi.schedule_unique_id
            ORDER BY  sfi.original_schedule_payment_date ASC";

        if (empty(trim($all_families[$i]['mother_name']))) {
            $all_families[$i]['mother_name'] = 'Data not available';
        }
        //CANCEL
        $status_Cancel_con = $sqlQuery . " AND sfi.schedule_status = 2 " . $sqlQuery_last;
        $status_Cancel_con_res = $db->get_row($sqlQuery . " AND sfi.schedule_status = 2");
        $cancel_status_count = count((array)$db->get_results($status_Cancel_con));
        $cancel_status_total_amount = $status_Cancel_con_res->final_amount;

        //Success
        $status_success_con = $sqlQuery . " AND sfi.schedule_status = 1 " . $sqlQuery_last;
        $status_success_con_res = $db->get_row($sqlQuery . " AND sfi.schedule_status = 1");
        $success_status_count = count((array)$db->get_results($status_success_con));
        $success_status_total_amount = $status_success_con_res->final_amount;

        //PENDING / SCHEDULE
        $status_pending_con = $sqlQuery . " AND sfi.schedule_status = 0 " . $sqlQuery_last;
        $status_pending_con_res = $db->get_row($sqlQuery . " AND sfi.schedule_status = 0");
        $pending_status_count = count((array)$db->get_results($status_pending_con));
        $pending_status_total_amount = $status_pending_con_res->final_amount;

        //Hold
        $status_hold_con = $sqlQuery . " AND sfi.schedule_status = 3 " . $sqlQuery_last;
        $status_hold_con_res = $db->get_row($sqlQuery . " AND sfi.schedule_status = 3");
        $hold_status_count = count((array)$db->get_results($status_hold_con));
        $hold_status_total_amount = $status_hold_con_res->final_amount;

        //Decline
        $status_decline_con = $sqlQuery . " AND sfi.schedule_status = 4 " . $sqlQuery_last;
        $status_decline_con_res = $db->get_row($sqlQuery . " AND sfi.schedule_status = 4");
        $decline_status_count = count((array)$db->get_results($status_decline_con));
        $decline_status_total_amount = $status_decline_con_res->final_amount;


        //check if reminder send
        // $chekRminder = $sqlQuery . " AND sfi.schedule_notification = 1 " . $sqlQuery_last;
        // $chekRminder_res = $db->get_row($chekRminder);


        //  echo $chekRminder_res_count;die;
        // $total_amount =  $cancel_status_total_amount + $success_status_total_amount + $pending_status_total_amount + $hold_status_total_amount + $decline_status_total_amount;
        if (!empty($success_status_total_amount)) {
            $total_amount = $success_status_total_amount;
        } else {
            $total_amount = "0";
        }

        $all_student_fees_items = $db->get_results($sqlQuery . " GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY  sfi.original_schedule_payment_date ASC");

        $next_payment_date = "";
        $last_payment_status = "";
        $next_pay_date = "";
        $next_pay_sch_date = "";
        $schedule_amount = "";
        $hold_amount = "";
        $cancel_amount = "";
        $success_amount = "";
        $reminder_icon = "";

        if (count((array)$all_student_fees_items) > 0) {

            if(!empty(get_country()->currency)){
                $currency = get_country()->currency;
            }else{
                $currency = '';
            }


            foreach ($all_student_fees_items as $row) {

                //Next Payment
                if ($row->original_schedule_payment_date >= date('Y-m-d')) {
                    if ($row->schedule_status == 0) {
                        $next_payment_date .= $row->schedule_payment_date . "|";
                        $schedule_amount = $row->final_amount;
                        $next_pay_sch_date = "Schedule - [NEXTPAYMENTDATE] - <br>".$currency . $schedule_amount . "";
                        if ($row->schedule_notification == 1) {
                            $reminder_icon =   '<img src="' . SITEURL . 'assets/images/Reminder_icon-removebg-preview.png" class="top-right"/>';
                        }
                    } elseif ($row->schedule_status == 3) {
                        $next_pay_date = "Hold - " . my_date_changer($row->schedule_payment_date) . " - <br>$" . $row->final_amount . "";
                    }
                    // elseif($row->schedule_status == 2){
                    //     $next_pay_date = "Cancel - ".date('m/d/Y', strtotime($row->schedule_payment_date))." - <br>$".$row->final_amount."";
                    //     $cancel_amount = $row->final_amount;
                    // }
                }

                //Last Payment
                if ($row->schedule_payment_date <= date('Y-m-d')) {
                    if ($row->schedule_status == 1) {
                        $last_payment_status = "Success - " . my_date_changer($row->schedule_payment_date) . " - <br>".$currency . $row->final_amount . "";
                    } elseif ($row->schedule_status == 4) {
                        $last_payment_status = "Decline - " . my_date_changer($row->schedule_payment_date) . " - <br>".$currency . $row->final_amount . "";
                    }
                }
            }


            if (!empty($next_payment_date)) {
                $pay_date = explode('|', rtrim($next_payment_date, "|"));
                $next_payment = my_date_changer($pay_date[0]);
                $all_families[$i]['payment'] = $reminder_icon . str_replace("[NEXTPAYMENTDATE]", $next_payment, $next_pay_sch_date);
                // if(!empty($chekRminder_res)){
                //     $all_families[$i]['payment'] = $reminder_icon.str_replace("[NEXTPAYMENTDATE]", $next_payment, $next_pay_sch_date);
                // }
                // else{
                //     $all_families[$i]['payment'] = str_replace("[NEXTPAYMENTDATE]", $next_payment, $next_pay_sch_date);
                // }
            } elseif (empty($next_payment_date) && !empty($next_pay_date)) {
                $all_families[$i]['payment'] = $next_pay_date;
            } else {
                $all_families[$i]['payment'] = "";
            }

            if (!empty($last_payment_status)) {
                $all_families[$i]['last_payment_status'] = $last_payment_status;
            } else {
                $all_families[$i]['last_payment_status'] = "";
            }

            $all_families[$i]['collected_payments'] = 'Total '.$currency . $total_amount . '<br>' . $success_status_count . ' - Success<br>' . $pending_status_count . ' - Schedule<br>' . $hold_status_count . ' - Hold<br>' . $decline_status_count . ' - Decline<br>' . $cancel_status_count . ' - Cancel';
        } else {
            $all_families[$i]['payment'] = "";
            $all_families[$i]['last_payment_status'] = "";
            $all_families[$i]['collected_payments'] = "";
        }

        if (check_userrole_by_subgroup('admin')) {
            $user_key = md5('0A0' . $all_families[$i]['family_user_id'] . '0Z0');
            $all_families[$i]['admin_forced_login'] = $target_url = SITEURL . 'admin-forced-login.php?unique_key=' . $user_key;
        }

        if(isset($all_families[$i]['forte_customer_token']) && !empty($all_families[$i]['forte_customer_token'])){
            $all_families[$i]['customer_token'] = $all_families[$i]['forte_customer_token'];
        }else{
            $all_families[$i]['customer_token'] = '';
        }
        if(isset($pay_credencial) && !empty($pay_credencial)){
            $all_families[$i]['payment_token'] = $pay_credencial;
        }else{
            $all_families[$i]['payment_token'] = ''; 
        }
        $all_families[$i]['father_phone'] = internal_phone_check($all_families[$i]['father_phone']);
    }

    $finalAry['data'] = $all_families;
    echo json_encode($finalAry);
    exit;
    //}
}

//==========================EMAIL LOGIN INFO TO PARENTS=====================
elseif ($_POST['action'] == 'email_login_info_to_parents') {
    if (in_array("su_family_send_login_info", $_SESSION['login_user_permissions'])) {
        $familyid = $_POST['familyid'];
        $family = $db->get_row("SELECT u.*,f.father_first_name,f.father_last_name FROM ss_family f INNER JOIN ss_user u ON f.user_id = u.id INNER JOIN ss_usertypeusermap ut
            ON f.user_id = ut.user_id WHERE ut.user_type_id = '5' AND f.id = '" . $familyid . "' ORDER BY ut.id DESC LIMIT 1");

        $fathers_name = $family->father_first_name . ' ' . $family->father_last_name;


        if (count((array)$family) && trim($family->username) != '') {
            $emailbody_parents = "Dear $fathers_name Assalamu-alaikum,<br><br>You can login in " . CENTER_SHORTNAME . " " . SCHOOL_NAME . " Parent’s Portal using below information:<br>";
            $password_rec_link = SITEURL . "new_password.php?id=" . md5('iCjC' . $family->id . '1cjc');
            $emailbody_parents .= "<br><br><strong>Login URL:</strong> " . SITEURL . "login.php";
            $emailbody_parents .= "<br><br><strong>Username/Email:</strong> " . trim($family->username);
            $emailbody_parents .= "<br><br><strong>Password:</strong> Please use password provided earlier or <a href='" . $password_rec_link . "'>click here</a> to generate new password.";
            $emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
            $mailservice_request_from = MAIL_SERVICE_KEY;
            $mail_service_array = array(
                'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Login Details',
                'message' => $emailbody_parents,
                'request_from' => $mailservice_request_from,
                'attachment_file_name' => '',
                'attachment_file' => '',
                'to_email' => [$family->username],
                'cc_email' => '',
                'bcc_email' => '',
            );
            mailservice($mail_service_array);

            echo json_encode(array('code' => "1", 'msg' => 'Login details sent successfully'));
            exit;
        } else {
            $return_resp = array('code' => "0", 'msg' => 'Login details not sent. Please try later.', 'err_pos' => 2);
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
        exit;
    }
}
//==========================EDIT FAMILY PERSONAL=====================
elseif ($_POST['action'] == 'edit_family_personal') {
    // if (in_array("su_family_edit", $_SESSION['login_user_permissions'])) {
    $db->query('BEGIN');
    $sql_ret = $db->query("update ss_family set father_first_name='" . trim($db->escape($_POST['father_first_name'])) . "',
            father_last_name='" . trim($db->escape($_POST['father_last_name'])) . "', father_area_code='" . trim($db->escape($_POST['father_area_code'])) . "',
            father_phone='" . trim($db->escape($_POST['father_phone'])) . "',mother_first_name='" . trim($db->escape($_POST['mother_first_name'])) . "',
            mother_last_name='" . trim($db->escape($_POST['mother_last_name'])) . "',mother_area_code='" . trim($db->escape($_POST['mother_area_code'])) . "',
            mother_phone='" . trim($db->escape($_POST['mother_phone'])) . "',
            secondary_email='" . trim($db->escape($_POST['secondary_email'])) . "',
            billing_address_1='" . trim($db->escape($_POST['billing_address_1'])) . "',
            billing_address_2='" . trim($db->escape($_POST['billing_address_2'])) . "',
            billing_city='" . trim($db->escape($_POST['billing_city'])) . "',
            billing_state_id='" . trim($db->escape($_POST['billing_state_id'])) . "',
            billing_country_id='" . trim($db->escape($_POST['billing_country_id'])) . "',
            billing_post_code='" . trim($db->escape($_POST['billing_post_code'])) . "',
            updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $_SESSION['icksumm_uat_login_familyid'] . "'");
    if ($sql_ret && $db->query('COMMIT') !== false) {
        echo json_encode(array('code' => "1", 'msg' => 'Family information updated successfully'));
        exit;
    } else {
        $db->query('ROLLBACK');
        $return_resp = array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '1');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
    //}
}
//==========================STAFF VIEW ONLY INFO=====================
elseif ($_POST['action'] == 'view_family_detail') {

    if (in_array("su_family_view", $_SESSION['login_user_permissions'])) {
        if(!empty(get_country()->currency)){
            $currency = get_country()->currency;
        }else{
            $currency = '';
        }
        $familyid = $_POST['familyid'];
        $family = $db->get_row("select * from ss_family where id='" . $familyid . "'");

        if (!empty($family->father_phone)) {
            if (strpos($family->father_phone, "-") == false) {
                $number = $family->father_phone;
                $formatted_number = "$number[0]$number[1]$number[2]-$number[3]$number[4]$number[5]-$number[6]$number[7]$number[8]$number[9]";
                $family->father_phone = $formatted_number;
            }
        }

        if ($family->primary_contact == 'Father') {
            $primary_contact = '1st Parent';
        } elseif ($family->primary_contact == 'Mother') {
            $primary_contact = '2nd Parent';
        } else {
            $primary_contact = '';
        }
        $students = $db->get_results("SELECT s.user_id, s.dob, s.allergies, s.school_grade, CONCAT(first_name,' ',COALESCE(middle_name, ''),' ',COALESCE(last_name, '')) AS student_name, (CASE WHEN u.is_active=1 THEN 'Active' WHEN u.is_active=2 THEN 'Hold' ELSE 'Inactive' END) AS status FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and u.is_deleted = 0 AND s.family_id = '" . $familyid . "'");
        $retStr .= '<legend class="text-semibold">Child Information</legend>';
        $i = 1;
        foreach ($students as $stu) {
            $dob =$stu->dob;
            $from = new DateTime($dob);
            $to = new DateTime('today');
            $stu_age = $from->diff($to)->y;
            $age = $stu_age . ' Yrs';
            $stugroupclass = $db->get_results("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "' and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.is_active = 1");
            $group = $db->get_row("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "' and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and s.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and s.is_active = 1");
            $retStr .= '<div class="row">';
            $retStr .= '<div class="col-md-4"><label><strong>Child ' . $i . ': </strong></label> ' . $stu->student_name . '</div> <div class="col-md-4"><label><strong>Grade:</strong></label> ' . $stu->school_grade . '</div><div class="col-md-4"><label><strong>Status: </strong></label> ' . $stu->status . '</div>';
            $retStr .= '</div>';
            $retStr .= '<div class="row">
            <div class="col-md-4">
            <label><strong>Date of Birth: </strong></label>' . my_date_changer($stu->dob) . '
            </div>
            <div class="col-md-4">
            <label><strong>Age:</strong></label>' . $age . '
            </div>';
            if (!empty($stu->allergies)) {
                $retStr .= '<div><label><strong>Allergies:</strong></label> ' . $stu->allergies . ' </div>';
            }
            $retStr .= '</div><br>';
            foreach ($stugroupclass as $row) {
                $retStr .= '<div class="row">
                <div class="col-md-4">
                <label>Class:</label>
                <span>' . $row->class_name . '</span>
                </div>

                <div class="col-md-4">
                <label>Group :</label>
                <span>' . $row->group_name . '</span>
                </div>';
                $retStr .= '</div>';
            }
            $retStr .= '<hr>';
            // $retStr .='<legend class="text-semibold">Payment Fees Information</legend>';
            //$studiscountfees = $db->get_results("select fd.discount_name,fd.discount_percent from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='".$stu->user_id."' And fd.status = 1 AND sfd.status = 1");
            //$stubasicfees = $db->get_row("select bcf.fee_amount from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join ss_basicfees bcf on m.group_id = bcf.group_id where m.latest = 1 and m.student_user_id='".$stu->user_id."'");
            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.is_active = 1");
            $groups = [];
            foreach ($user_groups as $group) {
                $groups[] = $group->id;
            }
            $group_ids = implode(",", $groups);
            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

            $new_discountFeesData = $db->get_results("select * from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='" . $stu->user_id . "' And fd.status = 1 AND sfd.status = 1 ORDER BY discount_percent DESC");
            $discountPercentTotalnew = $basicFees->fee_amount;
            $discountDollarTotalnew = 0;
            $discountDollarDetailes = '';
            foreach ($new_discountFeesData as $val) {
                if ($val->discount_unit == 'p') {
                    $doller = '';
                    $percent = '%';
                    $fee_percent = ($discountPercentTotalnew * $val->discount_percent) / 100;
                    $discountPercentTotalnew = $discountPercentTotalnew - $fee_percent;
                } else {
                    $doller = $currency;
                    $percent = '';
                    $discountDollarTotalnew += $val->discount_percent;
                }
                $amount_val = $val->discount_percent + 0;
                $discountDollarDetailes .= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
            }
            //$basicDiscountFees = (100 - $discountPercentTotalnew) / 100 * $basicFees->fee_amount;
            $final_amount = ($discountPercentTotalnew - $discountDollarTotalnew);
            if ($final_amount > 0) {
                $total_final = $currency . $final_amount;
            } else {
                $total_final = $currency.'0';
            }
            if (!empty($basicFees->fee_amount)) {
                $basic_fee_amount = $currency . ($basicFees->fee_amount + 0);
            } else {
                $basic_fee_amount = $currency.'0';
            }

            if (empty($discountDollarDetailes)) {
                $discountDollarDetailes = "N/A";
            }

            $retStr .= '<div class="row">
            <div class="col-md-3">
            <label>Basic Fees:</label>' . $basic_fee_amount . '
            </div>

            <div class="col-md-6">
            <label>Discount:</label>' . rtrim($discountDollarDetailes, " ,") . ' </div>';
            $retStr .= '<div class="col-md-3">
            <label>Final Amount:</label> ' . $total_final . '
            </div>
            </div>';
            $retStr .= '<hr>';
            $i++;
        }
        $retStr .= '<legend class="text-semibold">Parent Information</legend>
            <div class="row">
            <div class="col-md-5">
            <label>1st Parent Name:</label>' . $family->father_first_name . ' ' . $family->father_last_name . '
            </div>
            <div class="col-md-3">
            <label>Phone:</label>' . internal_phone_check($family->father_phone) . '
            </div>
            <div class="col-md-4">
            <label>Email:</label>' . $family->primary_email . '
            </div>
            </div>';
        if (!empty($family->mother_first_name)) {
            $retStr .= '<div class="row">
                <div class="col-md-5">
                <label>2nd Parent Name:</label>' . $family->mother_first_name . ' ' . $family->mother_last_name . '
                </div>
                <div class="col-md-3">
                <label>Phone:</label>' . internal_phone_check($family->mother_phone) . '
                </div>
                <div class="col-md-4">
                <label>Email:</label>' . $family->secondary_email . '
                </div>
                </div>';
        }
        if (!empty($primary_contact)) {
            $retStr .= '<div class="row">
            <div class="col-md-4 col-md-6">
            <label>Primary Contact:</label>' . $primary_contact . '
            </div>
            </div>';
        }
        $billing_state = $db->get_var("select state from ss_state where id = '" . $family->billing_entered_state . "'");
        $retStr .= '<div class="row"><div class="col-md-5"><label>Address:</label>' . $family->billing_address_1 . ', ' . $family->billing_address_2 . ', ' . $family->billing_city . ', ' . (trim($billing_state) != '' ? $billing_state : $family->billing_entered_state) . ', ' . $family->billing_post_code . '</div><div class="col-md-4"><label>Zipcode:</label>' . $family->billing_post_code . '</div></div>';
        $retStr .= '<div class="row"><div class="col-md-12"><label>Additional Notes:</label>' . $family->addition_notes . '</div></div>';
        if (!empty($family->comments)) {
            $retStr .= '<div class="row">
                        <div class="col-md-3">
                        <label>Comments:</label>' . $family->comments . '
                        </div>
                    </div>';
        }
        echo $retStr;
        exit;
    }
}
//==========================ADD CHILD=====================
elseif ($_POST['action'] == 'add_child') {
    // if (in_array("su_student_create", $_SESSION['login_user_permissions'])) {
    $family = $db->get_row("SELECT * FROM ss_family WHERE id='" . $_POST['family_id'] . "' ");
    $db->query("BEGIN");
    $studentRegister = $db->query("insert into ss_sunday_school_reg set
                father_first_name='" . $family->father_first_name . "',
                father_last_name='" . $family->father_last_name . "',
                father_phone='" . $family->father_phone . "',
                father_email='" . $family->father_email . "',
                mother_first_name='" . $family->mother_first_name . "',
                mother_last_name='" . $family->mother_last_name . "',
                mother_phone='" . $family->mother_phone . "',
                mother_email='" . $family->mother_email . "',
                primary_email='" . $family->primary_email . "',
                secondary_email='" . $family->secondary_email . "',
                address_1='" . $family->billing_address_1 . "',
                address_2='" . $family->billing_address_2 . "',
                city='" . $family->billing_city . "',
                state='" . $family->billing_state_id . "',
                country_id='" . $family->billing_country_id . "',
                post_code='" . $family->billing_post_code . "',
                addition_notes='" . $family->addition_notes . "',
                payment_method = 'c',
                session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                registerd_by='Admin',
                is_paid=1,
                registerd_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "',
                created_on='" . date('Y-m-d H:i:s') . "',
                updated_on='" . date('Y-m-d H:i:s') . "'");
    $reg_id = $db->insert_id;
    if ($reg_id > 0) {
        if ($_POST['child_first_name'] != '') {
            $data = $db->query("insert into ss_sunday_sch_req_child set
                        sunday_school_reg_id='" . $reg_id . "',
                        first_name='" . trim($_POST['child_first_name']) . "',
                        last_name='" . trim($_POST['child_last_name']) . "',
                        dob= '" . date('Y-m-d', strtotime($_POST['child_dob_submit'])) . "',
                        gender='" . trim($_POST['child_gender']) . "',
                        allergies='" . trim($_POST['child_allergies']) . "',
                        school_grade = '" . $_POST['child_grade'] . "',
                        created_on='" . date('Y-m-d H:i:s') . "',
                        updated_on='" . date('Y-m-d H:i:s') . "'");
        }
        if (!empty($data)) {
            $paymentcredentials = $db->get_row("SELECT * FROM ss_paymentcredentials WHERE family_id='" . $_POST['family_id'] . "' ");
            $credit_card_type = $paymentcredentials->credit_card_type;
            $credit_card_no = $paymentcredentials->credit_card_no;
            $credit_card_exp = $paymentcredentials->credit_card_exp;
            $bank_acc_no = '';
            $routing_no = '';
            $db->query("insert into ss_sunday_sch_payment set sunday_sch_req_id='" . $reg_id . "', credit_card_type='" . $credit_card_type . "', credit_card_no='" . $credit_card_no . "',
                        credit_card_exp='" . $credit_card_exp . "', bank_acc_no='" . $bank_acc_no . "',
                        routing_no='" . $routing_no . "'");
            $sql_ret = $db->insert_id;
            if ($sql_ret > 0 && $db->query('COMMIT') !== false) {
                $gender = trim($_POST['child_gender']) == 'm' ? "Male" : "Female";

                $new_email_body = "Dear Parent Assalamu-alaikum,<br>";
                $new_email_body .= "New child added successfully, Details are mentioned below - <br>
                                            <strong>Name</strong>- " . trim($_POST['child_first_name'] . " " . $_POST['child_last_name']) . "<br>
                                            <strong>Gender</strong> - " . $gender . "<br>
                                            <strong>Date of Birth</strong> - " . my_date_changer($_POST['child_dob_submit']) . "<br>
                                            <strong>Allergy</strong> - " . trim($_POST['child_allergies']) . "<br>
                                            <strong>Grade</strong> -  " . trim($_POST['child_grade']) . "<br>";
                $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
                $new_email_body .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                $mailservice_request_from = MAIL_SERVICE_KEY;
                $mail_service_array = array(
                    'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - New Child Added',
                    'message' => $new_email_body,
                    'request_from' => $mailservice_request_from,
                    'attachment_file_name' => '',
                    'attachment_file' => '',
                    'to_email' => [$family->primary_email],
                    'cc_email' => '',
                    'bcc_email' => '',
                );

                mailservice($mail_service_array);

                echo json_encode(array('code' => "1", 'msg' => 'Child Added Successfully.'));
                exit;
            } else {
                $db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => "Child Added Successfully", '_errpos' => 2);
                echo json_encode($return_resp);
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            $return_resp = array('code' => "0", 'msg' => "Child Not Added", '_errpos' => 3);
            echo json_encode($return_resp);
            exit;
        }
    } else {
        $db->query('ROLLBACK');
        $return_resp = array('code' => "0", 'msg' => "Child Not Added", '_errpos' => 4);
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
    // } else {
    //     $db->query('ROLLBACK');
    //     $return_resp = array('code' => "0", 'msg' => "Sorry, you are not authorized", '_errpos' => 5);
    //     CreateLog($_REQUEST, json_encode($return_resp));
    //     echo json_encode($return_resp);
    //     exit;
    // }
}
//==========================ADMIN VIEW ONLY INFO=====================
elseif ($_POST['action'] == 'view_family_detail_payment') {
    // if (in_array("su_family_view", $_SESSION['login_user_permissions'])) {
    if(!empty(get_country()->currency)){
        $currency = get_country()->currency;
    }else{
        $currency = '';
    }
    $familyid = $_POST['familyid'];
    $family = $db->get_row("select ss_family.* from ss_family where id='" . $familyid . "'");

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
    $students = $db->get_results("SELECT s.user_id, s.dob, s.allergies, s.school_grade, CONCAT(first_name,' ',COALESCE(middle_name, ''),' ',COALESCE(last_name, '')) AS student_name FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and u.is_deleted = 0 AND s.family_id = '" . $familyid . "' ");
    $basic_fee_all = 0;
    $discount_fee_val_all = 0;
    $final_amount_all = 0;
    foreach ($students as $stu) {
        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $groups = [];
        foreach ($user_groups as $group) {
            $groups[] = $group->id;
        }
        $group_ids = implode(",", $groups);
        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $stu->user_id . "' AND sf.status = 1  and d.status=1 and sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND d.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $discountPercentTotal = $basicFees->fee_amount;
        $discountDollarTotal = 0;
        foreach ($new_discountFeesData as $val) {
            if ($val->discount_unit == 'p') {
                $doller = '';
                $percent = '%';
                $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                $discountPercentTotal = $discountPercentTotal - $fee_percent;
            } else {
                $doller = $currency;
                $percent = '';
                $discountDollarTotal += $val->discount_percent;
            }
        }
        $basic_fee_all += $basicFees->fee_amount;
        $final_amount = ($discountPercentTotal - $discountDollarTotal);
        if ($final_amount > 0) {
            $total_final_amount = $final_amount;
        } else {
            $total_final_amount = 0;
        }
        $final_amount_all += $total_final_amount;
    }

    $discount_fee_val_all = $basic_fee_all - $final_amount_all;
    $retStr .= '<legend class="text-semibold">Total Fee Information</legend>
            <div class="row viewonly">
            <div class="col-md-2">
            <label>Basic Fees</label>
            <p>'.$currency . ($basic_fee_all + 0) . '</p>
            </div>
            <div class="col-md-2">
            <label>Discount</label>
            <p>'.$currency . ($discount_fee_val_all + 0) . '</p>
            </div>
            <div class="col-md-2">
            <label>Net Fees</label>
            <p>'.$currency . ($final_amount_all + 0) . '</p>
            </div>
            <div class="col-md-3">
            </div>
            <div class="col-md-3">';

    if (in_array("su_payment_fees_history_list", $_SESSION['login_user_permissions'])) {
        $retStr .= ' <a href="' . SITEURL . 'payment/payment_fees_history_list?id=' . $familyid . '"" style="float:right;" class="text-warning action_link"> Schedule Payment </a>';
    }

    $retStr .= '</div>
            </div>
            <br>';
    $retStr .= '<legend class="text-semibold">Child Information</legend>';
    $i = 1;
    foreach ($students as $stu) {
        $dob = date('Y-m-d', strtotime($stu->dob));
        $from = new DateTime($dob);
        $to = new DateTime('today');
        $stu_age = $from->diff($to)->y;
        $age = $stu_age . ' Yrs';
        $group = $db->get_row("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "'");


        if (in_array("su_payment_recurring_history", $_SESSION['login_user_permissions'])) {
            $retStr .= '<div style="float:right; margin-right:10px;"><a href="' . SITEURL . 'payment/schedule_payment?id=' . $stu->user_id . '"" class="text-warning action_link">Payment Recurring History</a></div>';
        }
        if (in_array("su_student_edit", $_SESSION['login_user_permissions'])) {
            $retStr .= '<div style="float:right; margin-right:10px;"><a href="' . SITEURL . 'student/student_edit?id=' . $stu->user_id . '&page=family_info" class="text-primary action_link">Edit</a></div>';
        }

        $retStr .= '<div class="row">';
        $retStr .= '<div class="col-md-4"><label>Child ' . $i . ': </label> ' . $stu->student_name . '</div> <div class="col-md-4"><label>Grade:</label> ' . $stu->school_grade . '</div>';
        $retStr .= '</div>';
        $retStr .= '<div class="row">
                <div class="col-md-4">
                <label>Date of Birth: </label>' . my_date_changer($stu->dob) . '
                </div>
                <div class="col-md-3">
                <label>Age:</label>' . $age . '
                </div>';
        if (!empty($stu->allergies)) {
            $retStr .= '<div class="col-md-4"><label>Allergies:</label> ' . $stu->allergies . ' </div>';
        }
        $retStr .= '</div><br>';
        // $retStr .='<legend class="text-semibold">Payment Fees Information</legend>';
        $all_discounts = $db->get_results("select fd.id, fd.discount_name,fd.discount_percent, fd.discount_unit, fd.created_on from ss_fees_discounts fd  where  fd.status = 1 AND fd.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $studiscountfees = $db->get_results("select fd.id from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='" . $stu->user_id . "' And fd.status = 1 AND sfd.status = 1 AND fd.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sfd.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $newstuarray = [];
        foreach ($studiscountfees as $discountid) {
            $newstuarray[] = $discountid->id;
        }
        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
        $groups = [];
        foreach ($user_groups as $group) {
            $groups[] = $group->id;
        }
        $group_ids = implode(",", $groups);
        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

        $new_discountFeesData = $db->get_results("select * from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='" . $stu->user_id . "' And fd.status = 1 AND sfd.status = 1 AND fd.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and sfd.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY discount_percent DESC");
        $discountPercentTotalnew = $basicFees->fee_amount;
        $discountDollarTotalnew = 0;
        foreach ($new_discountFeesData as $val) {
            if ($val->discount_unit == 'p') {
                $doller = '';
                $percent = '%';
                $fee_percent = ($discountPercentTotalnew * $val->discount_percent) / 100;
                $discountPercentTotalnew = $discountPercentTotalnew - $fee_percent;
            } else {
                $doller = $currency;
                $percent = '';
                $discountDollarTotalnew += $val->discount_percent;
            }
            $amount_val = $val->discount_percent + 0;
            $discountDollarDetailes .= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
        }

        //$basicDiscountFees = (100 - $discountPercentTotalnew) / 100 * $basicFees->fee_amount;
        $final_amount = ($discountPercentTotalnew - $discountDollarTotalnew);
        if ($final_amount > 0) {
            $total_final = $final_amount;
        } else {
            $total_final = 0;
        }

        if (!empty($basicFees->fee_amount)) {
            $basic_fee_amount = $currency . ($basicFees->fee_amount + 0);
        } else {
            $basic_fee_amount = $currency.'0';
        }
        $retStr .= '<div class="row">
                <div class="col-md-4">
                <label>Basic Fees:</label> ' . $basic_fee_amount . '
                </div>';
        $retStr .= '<div class="col-md-4">
                <label>Final Amount:</label>'.$currency . $total_final . '
                </div>';
        $retStr .= '</div>';
        $retStr .= '<div class="row"><div class="col-md-12">
                <label>Discount:</label>';
        foreach ($all_discounts as $studiscountfee) {
            if ($studiscountfee->discount_unit == 'p') {
                $percent = '%';
                $doller = '';
            }else{
                $doller = $currency;
                $percent = '';
            }

            if (in_array($studiscountfee->id, $newstuarray)) {
                $selected = 'checked="checked"';
                $discount_date = $db->get_var("select sfd.created_on from ss_student_feesdiscounts sfd where sfd.student_user_id='" . $stu->user_id . "' And sfd.fees_discount_id = '" . $studiscountfee->id . "' And sfd.status = 1");
                $data_add_discount = date('m/d/Y H:i A', strtotime($discount_date));
                $texttobox = "Applied on $data_add_discount";
            } else {
                $selected = '';
                $texttobox = "";
            }
            $retStr .= '<input type="checkbox" name="discount[' . $stu->user_id . '][]" class="discount form-check-input" ' . $selected . ' title="' . $texttobox . '"  value="' . $studiscountfee->id . '"> ' . $studiscountfee->discount_name . ' (' . $doller . '' . ($studiscountfee->discount_percent + 0) . '' . $percent . ' ) &nbsp&nbsp';
        }
        $retStr .= '<input type="hidden" name="student_ids[]" class="student_ids" checked="checked"  value="' . $stu->user_id . '">';
        $retStr .= '</div></div>';
        $retStr .= '<hr>';
        $i++;
    }
    $retStr .= '<legend class="text-semibold">Parent Information</legend>
                <div class="row">
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
        $retStr .= '<div class="row">
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
    $retStr .= '<div class="row">
                <div class="col-md-4 col-md-6">
                <label>Primary Contact:</label>' . $primary_contact . '
                </div>
                </div>';
                if(!empty($family->billing_entered_state)){
                    $billing_entered_state = $db->get_var("select state from ss_state where id = '" . $family->billing_entered_state . "'");
                }elseif(!empty($family->billing_state_id)){
                    $billing_state = $db->get_var("select state from ss_state where id = '" . $family->billing_state_id . "'");
                }
                         
    
    $retStr .= '<div class="row"><div class="col-md-12"><label>Address:</label>' . $family->billing_address_1 . ', ' . $family->billing_address_2 . ', ' . $family->billing_city . ', ' . (trim($billing_state) != '' ? $billing_state : $billing_entered_state) . ', ' . $family->billing_post_code . '</div></div>';
    $retStr .= '<div class="row"><div class="col-md-12"><label>Additional Notes:</label>' . $family->addition_notes . '</div></div>';
    $paymentcred = $db->get_results("select * from ss_paymentcredentials where family_id='" . $familyid . "' AND credit_card_deleted =0");
    $retStr .= '<legend class="text-semibold">Payment Info</legend>
                <table class="table datatable-basic table-bordered dataTable no-footer dtr-inline"
                id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">
                <thead>
                <tr role="row">
                <th>Last 4 Digits of CC</th>
                <th>Expiry</th>
                <th>Default</th>
                <th>Mark Default</th>
                </tr>
                </thead>
                <tbody>';
    foreach ($paymentcred as $key => $val) {
        $credit_card_type = base64_decode($val->credit_card_type);
        $credit_card_no = str_replace(' ', '', base64_decode($val->credit_card_no));
        $credit_card_exp = base64_decode($val->credit_card_exp);
        $credit_card_cvv = base64_decode($val->credit_card_cvv);
        $credit_card_expAry = explode('-', $credit_card_exp);
        $credit_card_exp_month = $credit_card_expAry[0];
        $credit_card_exp_year = $credit_card_expAry[1];
        if ($val->default_credit_card == 1) {
            $default = 'Yes';
            $btn = '<input type="radio" name="default_card" checked class="default_card"  value="' . $val->id . '">';
        } else {
            $default = 'No';
            $btn = '<input type="radio" name="default_card" class="default_card"  value="' . $val->id . '">';
        }
        $retStr .= '<tr role="row" class="odd">';
        $retStr .= '<td tabindex="0">************ ' . substr($credit_card_no, -4) . '</td>';
        $retStr .= '<td> ' . $credit_card_exp_month . '/' . $credit_card_exp_year . ' </td>';
        $retStr .= '<td> ' . $default . ' </td>';
        $retStr .= '<td> ' . $btn . ' </td>';
        $retStr .= '</tr>';
    }
    $retStr .= '</tbody>
                </table>
                </div>';
    echo $retStr;
    exit;
    //}
    //////////////////////////////// FAMILY_INFO_SUBMIT ///////////////////////////////////////

} elseif ($_POST['action'] == 'family_info_submit') {
    $db->query('BEGIN');
    if(!empty(get_country()->currency)){
        $currency = get_country()->currency;
    }else{
        $currency = '';
    }

    $family_data = get_family_schedule_payment($_POST['family_id']);
    $schedule_payment_cron = get_schedule_payment_cron($_POST['family_id']);
    $family_payment_on_excution_check = get_payment_on_excution($_POST['family_id']);

    if (count((array)$family_payment_on_excution_check) == 0) {

        $con_check_var = 0;
        if (!empty($_POST['default_card'])) {
            $db->query("update ss_paymentcredentials set default_credit_card=0  where family_id='" . $_POST['family_id'] . "' ");

            $paymentcred = $db->query("update ss_paymentcredentials set default_credit_card=1, updated_on='" . date('Y-m-d H:i:s') . "'
    where id='" . $_POST['default_card'] . "' ");
        }


        if (isset($_POST['discount'])) {

            $discounted_stu_ids = [];
            foreach ($_POST['discount'] as $user_id => $vals) {
                $discounted_stu_ids[] = $user_id;
            }

            foreach ($_POST['student_ids'] as $user_id) {
                if (in_array($user_id, $discounted_stu_ids)) {
                    //Has Discount
                    //old discounts
                    // echo "select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d
                    // ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1
                    // where sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'";
                    // die;    
                    $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d
                ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1
                and sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
                    //yaha
                    $fees_discount_ids = [];
                    $old_discountDetailes = "";
                    foreach ($old_discountFeesData as $value) {
                        if ($value->discount_unit == 'p') {
                            $doller = '';
                            $percent = '%';
                        } else {
                            $doller = $currency;
                            $percent = '';
                        }
                        $fees_discount_ids[] = $value->fees_discount_id;
                        $amount_val = $value->discount_percent + 0;
                        $old_discountDetailes .= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
                    }

                    $array_one = (array) $_POST['discount'][$user_id];
                    $array_two = (array) $fees_discount_ids;
                    $result = array_merge(array_diff($array_one, $array_two), array_diff($array_two, $array_one));

                    //$result = array_diff($fees_discount_ids, $_POST['discount'][$user_id]);

                    if (count((array)$result) > 0) {
                        $con_check_var = 1;

                        $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "' and  session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
                        foreach ($_POST['discount'][$user_id] as $discount_id) {

                            $sql_ret = $db->query("insert into ss_student_feesdiscounts set
                            session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                            udated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',
                            fees_discount_id = '" . $discount_id . "', updated_on='" . date('Y-m-d H:i:s') . "', created_on='" . date('Y-m-d H:i:s') . "',
                            student_user_id = '" . $user_id . "' ");
                            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id
                        where m.student_user_id='" . $user_id . "' and m.latest = 1 and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and
                        m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
                            $groups = [];
                            foreach ($user_groups as $group) {
                                $groups[] = $group->id;
                            }
                            $group_ids = implode(",", $groups);
                            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1
                        AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");

                            //COMMENTED ON 06-NOV-2021
                            // $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d
                            // ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1
                            // and d.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and sf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

                            //ADDED ON 06-NOV-2021
                            $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d
                        ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1
                        and sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

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
                                    $doller = $currency;
                                    $percent = '';
                                    $discountDollarTotal += $val->discount_percent;
                                }
                                $amount_val = $val->discount_percent + 0;
                                $new_discountDetailes .= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
                            }
                            $final_amount = ($discountPercentTotal - $discountDollarTotal);
                            if ($final_amount > 0) {
                                $actualbasicDiscountFees = $final_amount;
                            } else {
                                $actualbasicDiscountFees = 0;
                            }
                            $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong>" . $new_discountDetailes . " ";
                            $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND (schedule_status = 0 OR schedule_status = 3) ");
                            if (count((array)$student_fees_items) > 0) {
                                foreach ($student_fees_items as $items) {
                                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
                                current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "', comments='" . $comments . "',
                                session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                                created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'  ");

                                    $sql_ret = $db->query("update ss_student_fees_items set amount='" . $actualbasicDiscountFees . "' , updated_at='" . date('Y-m-d H:i:s') . "'
                                where id = '" . $items->id . "'");
                                }
                            }
                        }
                    }
                } else {
                    //No discount
                    //old discounts

                    //COMMENTED ON 06-NOV-2021
                    // $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id
                    // where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1 and d.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and
                    // sf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

                    //ADDED ON 06-NOV-2021
                    $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id
                    where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1 and
                    sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

                    $fees_discount_ids = [];
                    $old_discountDetailes = "";
                    foreach ($old_discountFeesData as $value) {
                        if ($value->discount_unit == 'p') {
                            $doller = '';
                            $percent = '%';
                        } else {
                            $doller = $currency;
                            $percent = '';
                        }
                        $fees_discount_ids[] = $value->fees_discount_id;
                        $amount_val = $value->discount_percent + 0;
                        $old_discountDetailes .= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
                    }

                    if (isset($_POST['discount'][$user_id])) {
                        $array_one_new = (array) $_POST['discount'][$user_id];
                        $array_two_new = (array) $fees_discount_ids;
                        // $result_res = array_filter(array_diff($array_one_new,$array_two_new));
                        $result_res = array_merge(array_diff($array_one_new, $array_two_new), array_diff($array_two_new, $array_one_new));
                        // $result = array_diff($fees_discount_ids, $_POST['discount'][$user_id]);

                        if (count($result_res) > 0) {
                            $con_check_var = 1;
                        }
                    }



                    if (!isset($_POST['discount'][$user_id]) || count($result) > 0) {
                        $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");

                        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id
                        where m.student_user_id='" . $user_id . "' and m.latest = 1 and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                        and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

                        $groups = [];
                        foreach ($user_groups as $group) {
                            $groups[] = $group->id;
                        }
                        $group_ids = implode(",", $groups);

                        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1
                        AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

                        $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong>";
                        $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND (schedule_status = 0 OR schedule_status = 3) ");
                        if (count((array)$student_fees_items) > 0) {
                            foreach ($student_fees_items as $items) {
                                $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' , current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "', comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'  ");
                                $sql_ret = $db->query("update ss_student_fees_items set amount='" . $basicFees->fee_amount . "' , updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                            }
                        }
                    }
                }
            }

            ///////////////////////////////////////////////////////////
        } else {


            // $students = $db->get_results("SELECT s.user_id FROM ss_user u INNER JOIN ss_student s ON u.id = s.user_id
            // INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
            // WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1
            // and u.is_deleted = 0 AND s.family_id = '" . $_POST['family_id'] . "' ");


            //     foreach ($students as $row) {
            foreach ($_POST['student_ids'] as $user_id) {
                $old_discountDetailes = "";
                //old discounts

                //COMMENTED ON 06-NOV-2021
                // $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf
                // INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id
                // where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1 and d.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
                // and sf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

                //ADDED ON 06-NOV-2021

                $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf
            INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id
            where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1 and d.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
            and sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
                // echo"<pre>";
                // print_r($old_discountFeesData);
                // die;
                $fees_discount_ids = [];
                foreach ($old_discountFeesData as $value) {
                    if ($value->discount_unit == 'p') {
                        $doller = '';
                        $percent = '%';
                    } else {
                        $doller = $currency;
                        $percent = '';
                    }
                    $fees_discount_ids[] = $value->fees_discount_id;
                    $amount_val = $value->discount_percent + 0;
                    $old_discountDetailes .= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
                }

                if (count((array)$old_discountFeesData) > 0) {

                    $sql_ret = $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "'  and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

                    $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id
                where m.student_user_id='" . $user_id . "' and m.latest = 1 and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and
                m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
                    $groups = [];
                    foreach ($user_groups as $group) {
                        $groups[] = $group->id;
                    }
                    $group_ids = implode(",", $groups);

                    $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1
                AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

                    $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong> ";


                    $student_fees_items = $db->get_results("select * from ss_student_fees_items
                where student_user_id = '" . $user_id . "' and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND (schedule_status = 0 OR schedule_status = 3) ");

                    if (count((array)$student_fees_items) > 0) {

                        foreach ($student_fees_items as $items) {
                            $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' ,
                    current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "',
                    session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
                    comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "',
                    created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'  ");

                            $sql_ret = $db->query("update ss_student_fees_items set amount='" . $basicFees->fee_amount . "' ,
                    updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                        }
                    }

                    if (count($fees_discount_ids) > 0) {
                        $con_check_var = 1;
                    }
                }
            }
        }



        if ($con_check_var === 1) {

            //REMINDER PAYMENT CHANGED AND NEW INVOICE SEND PARENTS
            if (count((array)$schedule_payment_cron) > 0) {

                foreach ($schedule_payment_cron as $data) {

                    $db->query("insert into ss_payment_sch_item_cron_backup  set schedule_unique_id ='" . $data->schedule_unique_id . "',  	family_id ='" . $data->family_id . "', sch_item_ids='" . $data->sch_item_ids . "', schedule_payment_date='" . $data->schedule_payment_date . "', total_amount ='" . $data->old_total_amount . "', wallet_amount = '" . $data->wallet_amount . "', cc_amount = '" . $data->cc_amount . "', schedule_status  = '" . $data->schedule_status . "', retry_count = '" . $data->retry_count . "', session  = '" . $data->session . "', is_approval  = '" . $data->is_approval . "', reason = '" . $data->reason . "', payment_unique_id = '" . $data->payment_unique_id . "', payment_response_code= '" . $data->payment_response_code . "', payment_response= '" . $data->payment_response . "',  is_cancel=1, created_at='" . date('Y-m-d h:i:s', strtotime($data->created_at)) . "', updated_at='" . date('Y-m-d h:i:s', strtotime($data->updated_at)) . "'");

                    $payment_sch_item_cron_backup_id = $db->insert_id;

                    if ($payment_sch_item_cron_backup_id > 0) {

                        $total_final_amount = $db->get_var("select sum(amount) as total_final_amount from ss_student_fees_items  where id IN (" . $data->sch_item_ids . ")");
                        $family_data = (object) array_merge((array) $family, (array) $data);
                        $family_data = (object) array_merge((array) $family_data, ["total_amount" => $total_final_amount, "old_total_amount" => $data->old_total_amount]);

                        genrate_and_send_invoice($family_data);

                        $result = $db->query("update ss_payment_sch_item_cron set total_amount = '" . $total_final_amount . "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" . $data->id . "'");

                        if (!$result) {
                            $conn_check = false;
                        }
                    }
                }
            }
        }


        if ($paymentcred || $sql_ret) {
            $db->query('COMMIT');
            echo json_encode(array('code' => "1", 'msg' => '<p class="text-success">Family information updated successfully </p>'));
            exit;
        } else {
            $db->query('ROLLBACK');
            $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Error: Process failed </p>', '_errpos' => '1');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    } else {
        $db->query('ROLLBACK');
        $return_resp = array('code' => "0", 'msg' => '<p class="text-danger">A Payment is under process, please try again after some time. </p>', '_errpos' => '13');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
} elseif ($_POST['action'] == 'get_stu_schedule') {
    $family_id = $_POST['familyid'];
    $students = $db->get_results("select s.user_id, s.first_name, s.last_name from ss_student s inner join ss_user u on u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where s.family_id= '" . $family_id . "' AND u.is_locked=0 AND u.is_active=1 and u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
    $student_ids_exist = "";
    foreach ($students as $row) {
        $check_exist = $db->get_row("select * from ss_student_fees_items where student_user_id = '" . $row->user_id . "' AND (schedule_status = 0 OR schedule_status = 3)");
        if (!empty($check_exist)) {
            $student_ids_exist .= $row->user_id . ",";
        }
    }
    $schedule_stu_ids = rtrim($student_ids_exist, ",");
    if (!empty($schedule_stu_ids)) {
        echo json_encode(array('code' => "1", 'stuids' => "$schedule_stu_ids"));
        exit;
    } else {
        echo json_encode(array('code' => "0", 'msg' => " Child(ren) : " . $student_all . " <br> Status : Payment Already Scheduled"));
        exit;
    }
} elseif ($_POST['action'] == 'get_stu_not_schedule') {
    $family_id = $_POST['familyid'];
    $students = $db->get_results("select s.user_id, s.first_name, s.last_name from ss_student s inner join ss_user u on u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where s.family_id= '" . $family_id . "' AND u.is_locked=0 AND u.is_active=1 and u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
    $student_names = "";
    $student_ids_not_exist = "";
    $student_ids_exist = [];
    $total_stu_family = "";
    foreach ($students as $row) {
        $check_exist = $db->get_row("select * from ss_student_fees_items where student_user_id = '" . $row->user_id . "' AND (schedule_status = 0 OR schedule_status = 3)");
        if (empty($check_exist)) {
            $student_names .= $row->first_name . ' ' . $row->last_name . ", ";
            $student_ids_not_exist .= $row->user_id . ",";
        } else {
            $student_ids_exist[] = $row->user_id;
        }
        $total_stu_family .= $row->first_name . ' ' . $row->last_name . ", ";
    }
    if (count($student_ids_exist) > 0 && !empty($student_ids_exist)) {
        // $stu_user_id = $student_ids_exist[0];
        $stu_user_id = implode(",", $student_ids_exist);
        $sch_item_count = $db->get_results("SELECT * FROM ss_student_fees_items WHERE student_user_id IN (" . $stu_user_id . ") AND (schedule_status = 0 OR schedule_status = 3) GROUP BY schedule_payment_date ORDER BY schedule_payment_date ASC");

        if ($sch_item_count[0]->schedule_payment_date >= Date('Y-m-d')) {
            $quantity = count((array)$sch_item_count);
            $schedule_payment_start_date = date("m/d/Y", strtotime($sch_item_count[0]->schedule_payment_date));
        } else {
            $quantity = count((array)$sch_item_count) - 1;
            $start_date = strtotime($sch_item_count[0]->schedule_payment_date);
            $schedule_payment_start_date = date("m/d/Y", strtotime("+1 month", $start_date));
        }
        $state_view = "readonly";
    } else {
        $quantity = "";
        $schedule_payment_start_date = "";
        $state_view = "";
    }

    // <label class="radio-inline">
    // <input type="radio" name="sch_type" class="sch_type" value="yearly">Yearly
    // </label>
    $schedule_not_stu_names = rtrim($student_names, ", ");
    $schedule_not_stu_ids = rtrim($student_ids_not_exist, ",");
    $student_all = rtrim($total_stu_family, ", ");
    $html = '<hr style="margin-top:0px;margin-bottom:0px;">
            <br>
            <div class="row">
                <div class="col-md-12">
                <label>Payment of : </label><span> ' . $schedule_not_stu_names . ' </span>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                        <label class="radio-inline">
                        <input type="radio" name="sch_type" class="sch_type" value="monthly" checked>Monthly
                        </label>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">';
    $html .= '<label for="group_name">Date<span class="mands">*</span></label>';
    if ($state_view == 'readonly') {
        $html .= '<input required name="schedule_start_date" class="form-control required" ' . $state_view . ' type="text" value="' . $schedule_payment_start_date . '">';
    } else {
        $html .= '<input required name="schedule_start_date" id="schedule_start_date" class="form-control required" ' . $state_view . ' type="text" value="' . $schedule_payment_start_date . '">';
    }
    $html .= '
                </div>
            </div>
            <br>
            <div class="row qulitydiv" style="display:block;">
                <div class="col-md-12">
                <label for="group_name">Quantity<span class="mands">*</span></label>
                <input name="quantity" id="quantity" minlength="1" maxlength="2" Digits="true" class="form-control quantityval required" ' . $state_view . ' value="' . $quantity . '" type="text">
                <input type="hidden" name="stu_ids" value="' . $schedule_not_stu_ids . '">
                </div>
            </div>';

    if (!empty($schedule_not_stu_names)) {
        echo json_encode(array('code' => "1", 'msg' => $html, 'schedule_payment_start_date' => "$schedule_payment_start_date"));
        exit;
    }
    // else {
    //     echo json_encode(array('code' => "0", 'msg' => " Child(ren) : " . $student_all . " <br> Status : Payment Already Scheduled"));
    //     exit;
    // }
    /////////////////////////////////////////////////// Start schedule ////////////////////////////    
} elseif ($_POST['action'] == 'start_schedule') {
    $family_id = $_POST['familyid'];
    $students = explode(",", $_POST['stu_ids']);
    if (isset($_POST['schedule_start_date_submit'])) {
        $schedule_start_date = Date('Y-m-d', strtotime(trim($_POST['schedule_start_date_submit'])));
    } else {
        $schedule_start_date = Date('Y-m-d', strtotime(trim($_POST['schedule_start_date'])));
    }
    $schedule_unique_id = "U" . uniqid();

    //Monthly
    if (strtolower($_POST['sch_type']) == 'monthly') {
        $recurring_month_count = trim($_POST['quantity']);
        $check_count = 0;
        $bFees = $db->get_results("select fee_amount from ss_basicfees where status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
        if (count((array)$bFees) > 0) {
            foreach ($students as $user_id) {
                $db->query('BEGIN');
                $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 ");
             
                $groups = [];
                $k = 0;
                foreach ($user_groups as $group) {
                    $groups[] = $group->id;
                }

                $user_groups_unique = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 group by g.id");
                $total_groups = count((array)$user_groups_unique);
               
                foreach ($user_groups_unique as $grp) {
                    $basicFees_ingroup = $db->get_row("select fee_amount from ss_basicfees where group_id = '" . $grp->id . "' AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
                    if (!empty($basicFees_ingroup)) {
                        $k++;
                    }
                }
               
                if ($total_groups !== $k) {
                    echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'msg' => "Payment can't be scheduled because assigned group fees is not added", 'errpos' => 21]);
                    exit;
                }


                $group_ids = implode(",", $groups);
                $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
                $discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
                $discountPercentTotal = $basicFees->fee_amount;
                $discountDollarTotal = 0;
                foreach ($discountFeesData as $val) {
                    if ($val->discount_unit == 'd') {
                        $discountDollarTotal += $val->discount_percent;
                    } elseif ($val->discount_unit == 'p') {
                        $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                        $discountPercentTotal = $discountPercentTotal - $fee_percent;
                    }
                }
                $final_amount = ($discountPercentTotal - $discountDollarTotal);
                if ($final_amount > 0) {
                    $actualbasicDiscountFees = $final_amount;
                } else {
                    $actualbasicDiscountFees = 0;
                }
                $userDataAmount = $actualbasicDiscountFees;
                $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND schedule_status = 0 ");
                $student_data = $db->get_row("SELECT s.admission_no, s.user_id, s.school_grade, f.id as family_id, f.forte_customer_token,  f.father_phone, f.father_area_code, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_city, f.billing_post_code, pay.credit_card_type, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv, pay.forte_payment_token, pay.id as payment_credential_id FROM ss_user u
                INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id where s.user_id='" . $user_id . "' AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND pay.default_credit_card = 1 AND pay.credit_card_deleted = 0 ");

                if (isset($student_data->credit_card_type) && isset($student_data->credit_card_no) && isset($student_data->credit_card_exp)) {
                    $credit_card_exp = base64_decode($student_data->credit_card_exp);
                    $credit_card_expAry = explode('-', $credit_card_exp);
                    $CardType = base64_decode($student_data->credit_card_type);
                    $CardNumber = str_replace(' ', '', base64_decode($student_data->credit_card_no));
                    $CardExpiryMonth = $credit_card_expAry[0];
                    $CardExpiryYear = $credit_card_expAry[1];
                    $CardCVV = base64_decode($student_data->credit_card_cvv);
                    $cardHolderFirstName = $student_data->father_first_name;
                    $cardHolderLastName = $student_data->father_last_name;
                    $userDataEmail = $student_data->primary_email;
                    $userDataPhoneNo = $student_data->father_phone;
                    $userDataCity = $student_data->billing_city;
                    $userDataZip = $student_data->billing_post_code;
                    if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
                        $customertoken = $student_data->forte_customer_token;
                        $paymethodtoken = $student_data->forte_payment_token;
                    } else {
                        $customertoken = "";
                        $paymethodtoken = "";
                    }
                    $payment_gateway_id = $db->get_var("select id from ss_payment_gateways where status = 'active' ");
                    if (is_numeric($user_id)) {

                        // $forteParamsSend = array('coustomer_token' => $forte_customer_token, 'paymentAction' => 'Sale', 'itemName' => 'Fees', 'itemNumber' => '10001', 'amount' => $userDataAmount, 'currencyCode' => 'USD', 'creditCardType' => $CardType, 'creditCardNumber' => $CardNumber, 'expMonth' => $CardExpiryMonth, 'expYear' => $CardExpiryYear, 'cvv' => $CardCVV, 'firstName' => $cardHolderFirstName, 'lastName' => $cardHolderLastName, 'email' => $userDataEmail, 'phone' => $userDataPhoneNo, 'city' => $userDataCity, 'zip' => $userDataZip, 'countryCode' => 'US', 'recurring' => 'Yes');
                        // $forteParams = json_encode($forteParamsSend);
                        // if (!empty($student_data->forte_customer_token)) {
                        //     if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
                        //         $customertoken = $student_data->forte_customer_token;
                        //         $paymethodtoken = $student_data->forte_payment_token;
                        //     } else {
                        //         $customerPostRequest = $fortePayment->GenratePaymentToken($forteParams);
                        //         if (!empty($student_data->forte_customer_token) && isset($customerPostRequest->paymethod_token)) {
                        //             $customertoken = $student_data->forte_customer_token;
                        //             $paymethodtoken = $customerPostRequest->paymethod_token;
                        //             $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                        //             $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                        //         } else {
                        //             $responsemsg = "Payment processing failed. Please retry";
                        //         }
                        //     }
                        // } else {
                        //     $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
                        //     if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
                        //         $customertoken = $customerPostRequest->customer_token;
                        //         $paymethodtoken = $customerPostRequest->default_paymethod_token;
                        //         $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                        //         $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                        //     } else {
                        //         $customertoken = "";
                        //         $paymethodtoken = "";
                        //     }
                        // }
                        // if (!empty(trim($customerPostRequest->response->response_desc))) {
                        //     $msgError = str_replace("Error[", "<br>Error[", trim($customerPostRequest->response->response_desc));
                        //     $response_msg_error = ltrim($msgError, "<br>");
                        //     $responsemsg = $response_msg_error;
                        // } else {
                        //     $responsemsg = "Payment processing failed. Please retry";
                        // }
                        if (!empty($customertoken) && !empty($paymethodtoken)) {
                            $stu_items_data = $db->get_results("SELECT sfi.*, s.family_id FROM `ss_student_fees_items` sfi
                                    INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`
                                    INNER JOIN ss_user u ON u.id = s.user_id
                                    AND schedule_status = 0 AND sfi.`student_user_id` = '" . $user_id . "'
                                    AND u.is_active=1 AND u.is_deleted=0");
                            if (count((array)$stu_items_data) == 0) {
                                $no = 1;
                                for ($i = 0; $i <= ($recurring_month_count - 1); $i++) {
                                    $time = strtotime($schedule_start_date);
                                    $main_date = date("Y-m-d", strtotime("+$i month", $time));
                                    $modify_date = date("Y-m", strtotime("+$i month", $time));
                                    $check_date = $modify_date . '-01';
                                    $month_lastdate = date("Y-m-t", strtotime($check_date));
                                    if ($month_lastdate >= $main_date) {
                                        $next_schedule_start_dates = $main_date;
                                    } else {
                                        $next_schedule_start_dates = $month_lastdate;
                                    }
                                    $res = $db->query("insert into ss_student_fees_items set schedule_unique_id='" . $schedule_unique_id . $no . "',student_user_id='" . $user_id . "',  original_schedule_payment_date= '" . $next_schedule_start_dates . "', schedule_payment_date = '" . $next_schedule_start_dates . "', amount='" . $userDataAmount . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', schedule_status = 0, created_at = '" . date('Y-m-d H:i') . "', created_by='" . $_SESSION['icksumm_uat_login_userid'] . "' ");
                                    $no++;
                                }
                            } elseif (count((array)$stu_items_data) > 0 && $recurring_month_count >= count((array)$stu_items_data)) {
                                foreach ($stu_items_data as $i => $items_data) {
                                    $time = strtotime($schedule_start_date);
                                    $main_date = date("Y-m-d", strtotime("+$i month", $time));
                                    $modify_date = date("Y-m", strtotime("+$i month", $time));
                                    $check_date = $modify_date . '-01';
                                    $month_lastdate = date("Y-m-t", strtotime($check_date));
                                    if ($month_lastdate >= $main_date) {
                                        $next_schedule_start_dates = $main_date;
                                        $reason = 'Reschedule date ' . $next_schedule_start_dates;
                                    } else {
                                        $next_schedule_start_dates = $month_lastdate;
                                        $reason = 'Reschedule date ' . $next_schedule_start_dates;
                                    }
                                    $student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items_data->id . "', current_status = 0, new_status= 0, comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "'");
                                    $res = $db->query("update ss_student_fees_items set schedule_status = 0, schedule_payment_date = '" . $next_schedule_start_dates . "',  updated_at = '" . date('Y-m-d H:i') . "', updated_by='" . $_SESSION['icksumm_uat_login_userid'] . "' where id = '" . $items_data->id . "' ");
                                    $db->query("DELETE FROM ss_payment_sch_item_cron WHERE family_id = '".$items_data->family_id."'");
                                    
                                    $i++;
                                }
                                $remain_item_count = $recurring_month_count - count((array)$stu_items_data);
                                if ($remain_item_count > 0) {
                                    for ($i = 1; $i <= $remain_item_count; $i++) {
                                        $time = strtotime($next_schedule_start_dates);
                                        $main_date = date("Y-m-d", strtotime("+$i month", $time));
                                        $modify_date = date("Y-m", strtotime("+$i month", $time));
                                        $check_date = $modify_date . '-01';
                                        $month_lastdate = date("Y-m-t", strtotime($check_date));
                                        if ($month_lastdate >= $main_date) {
                                            $next_schedule_start_datess = $main_date;
                                        } else {
                                            $next_schedule_start_datess = $month_lastdate;
                                        }
                                        $res = $db->query("insert into ss_student_fees_items set schedule_unique_id='" . $schedule_unique_id . $i . "',student_user_id='" . $user_id . "',  original_schedule_payment_date= '" . $next_schedule_start_datess . "', schedule_payment_date = '" . $next_schedule_start_datess . "', amount='" . $userDataAmount . "', schedule_status = 0, session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_at = '" . date('Y-m-d H:i') . "', created_by='" . $_SESSION['icksumm_uat_login_userid'] . "'");
                                    }
                                }
                            } else {
                                if (count((array)$stu_items_data) > 0) {
                                    $remain_item_count = count((array)$stu_items_data) - $recurring_month_count;
                                    $db->query("delete from ss_student_fees_items where student_user_id='" . $user_id . "' AND session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND  (schedule_status = 0 OR  schedule_status = 2 OR schedule_status = 3) ORDER BY id DESC limit " . $remain_item_count . " ");
                                    $stu_items_data_reamin = $db->get_results("SELECT sfi.*, s.family_id FROM `ss_student_fees_items` sfi
                            INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`
                            INNER JOIN ss_user u ON u.id = s.user_id
                            AND schedule_status = 0 AND sfi.`student_user_id` = '" . $user_id . "'
                            AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_active=1 AND u.is_deleted=0");
                                    foreach ($stu_items_data_reamin as $i => $items_data) {
                                        $time = strtotime($schedule_start_date);
                                        $main_date = date("Y-m-d", strtotime("+$i month", $time));
                                        $modify_date = date("Y-m", strtotime("+$i month", $time));
                                        $check_date = $modify_date . '-01';
                                        $month_lastdate = date("Y-m-t", strtotime($check_date));
                                        if ($month_lastdate >= $main_date) {
                                            $next_schedule_start_dates = $main_date;
                                        } else {
                                            $next_schedule_start_dates = $month_lastdate;
                                        }
                                        $res = $db->query("update ss_student_fees_items set schedule_status = 0, schedule_payment_date = '" . $next_schedule_start_dates . "',  updated_at = '" . date('Y-m-d H:i') . "', updated_by='" . $_SESSION['icksumm_uat_login_userid'] . "' where id = '" . $items_data->id . "' ");
                                        $i++;
                                    }
                                }
                            }
                            if ($res) {
                                $check_count++;
                            } else {
                                $db->query('ROLLBACK');
                                echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'msg' => "Payment processing failed. Please retry", 'errpos' => 21]);
                                exit;
                            }
                        } else {
                            //forte customer create failed else
                            $db->query('ROLLBACK');
                            echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'msg' => "$responsemsg", 'errpos' => 18]);
                            exit;
                        }
                    } else {
                        $db->query('ROLLBACK');
                        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '-2'));
                        exit;
                    }
                } else {
                    $db->query('ROLLBACK');
                    echo json_encode(array('code' => "0", 'msg' => 'Payment credential not found.', '_errpos' => '-1'));
                    exit;
                }
            }
        } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => 'Fees not found.', '_errpos' => '-1'));
            exit;
        }
        //Yearly
    } else {
        $sch_sessions = $db->get_row("select *  from ss_school_sessions s where s.current = 1 ");
        if (!empty($sch_sessions)) {
            $date1 = $sch_sessions->start_date;
            $date2 = $sch_sessions->end_date;
            $ts1 = strtotime($date1);
            $ts2 = strtotime($date2);
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            $school_session_month = (($year2 - $year1) * 12) + ($month2 - $month1);
        } else {
            $school_session_month = null;
        }
        $finalAllStudentFeeAmount = 0;
        $check_count = 0;
        foreach ($students as $user_id) {
            $db->query('BEGIN');
            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1");
            $groups = [];
            foreach ($user_groups as $group) {
                $groups[] = $group->id;
            }
            $group_ids = implode(",", $groups);
            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
            $discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
            $discountPercentTotal = $basicFees->fee_amount;
            $discountDollarTotal = 0;
            foreach ($discountFeesData as $val) {
                if ($val->discount_unit == 'd') {
                    $discountDollarTotal += $val->discount_percent;
                } elseif ($val->discount_unit == 'p') {
                    $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                    $discountPercentTotal = $discountPercentTotal - $fee_percent;
                }
            }
            $final_amount = ($discountPercentTotal - $discountDollarTotal);
            if ($final_amount > 0) {
                if (!empty($sch_sessions) && !empty($sch_sessions->fees_full_payment_discount_unit) && !empty($sch_sessions->fees_full_payment_discount_value)) {
                    if (strtolower($sch_sessions->fees_full_payment_discount_unit) == 'p') {
                        $pre_final_amount = $final_amount * $school_session_month;
                        $discountFees = ($pre_final_amount * $sch_sessions->fees_full_payment_discount_value) / 100;
                        $actualbasicDiscountFees = $pre_final_amount - $discountFees;
                    } else {
                        $pre_final_amount = $final_amount * $school_session_month;
                        $actualbasicDiscountFees = $pre_final_amount - $sch_sessions->fees_full_payment_discount_value;
                    }
                } else {
                    $actualbasicDiscountFees = $final_amount;
                }
            } else {
                $actualbasicDiscountFees = 0;
            }
            $finalAllStudentFeeAmount += $actualbasicDiscountFees;
            $userDataAmount = $actualbasicDiscountFees;
            $student_data = $db->get_row("SELECT s.admission_no, s.user_id, s.school_grade, f.id as family_id, f.forte_customer_token,  f.father_phone, f.father_area_code, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_city, f.billing_post_code, pay.credit_card_type, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv, pay.forte_payment_token, pay.id as payment_credential_id FROM ss_user u
                INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id where s.user_id='" . $user_id . "' AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND pay.default_credit_card = 1 AND pay.credit_card_deleted = 0 ");
            if (!empty($student_data) && isset($student_data->credit_card_type) && isset($student_data->credit_card_no) && isset($student_data->credit_card_exp)) {
                $credit_card_exp = base64_decode($student_data->credit_card_exp);
                $credit_card_expAry = explode('/', $credit_card_exp);
                $CardType = base64_decode($student_data->credit_card_type);
                $CardNumber = str_replace(' ', '', base64_decode($student_data->credit_card_no));
                $CardExpiryMonth = $credit_card_expAry[0];
                $CardExpiryYear = $credit_card_expAry[1];
                $CardCVV = base64_decode($student_data->credit_card_cvv);
                $cardHolderFirstName = $student_data->father_first_name;
                $cardHolderLastName = $student_data->father_last_name;
                $userDataEmail = $student_data->primary_email;
                $userDataPhoneNo = $student_data->father_phone;
                $userDataCity = $student_data->billing_city;
                $userDataZip = $student_data->billing_post_code;
                if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
                    $customertoken = $student_data->forte_customer_token;
                    $paymethodtoken = $student_data->forte_payment_token;
                } else {
                    $customertoken = "";
                    $paymethodtoken = "";
                }
                $payment_gateway_id = $db->get_var("select id from ss_payment_gateways where status = 'active' ");
                if (is_numeric($user_id)) {

                    // $forteParamsSend = array('coustomer_token' => $forte_customer_token, 'paymentAction' => 'Sale', 'itemName' => 'Fees', 'itemNumber' => '10001', 'amount' => $userDataAmount, 'currencyCode' => 'USD', 'creditCardType' => $CardType, 'creditCardNumber' => $CardNumber, 'expMonth' => $CardExpiryMonth, 'expYear' => $CardExpiryYear, 'cvv' => $CardCVV, 'firstName' => $cardHolderFirstName, 'lastName' => $cardHolderLastName, 'email' => $userDataEmail, 'phone' => $userDataPhoneNo, 'city' => $userDataCity, 'zip' => $userDataZip, 'countryCode' => 'US', 'recurring' => 'Yes');
                    // $forteParams = json_encode($forteParamsSend);
                    // if (!empty($student_data->forte_customer_token)) {
                    //     if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
                    //         $customertoken = $student_data->forte_customer_token;
                    //         $paymethodtoken = $student_data->forte_payment_token;
                    //     } else {
                    //         $customerPostRequest = $fortePayment->GenratePaymentToken($forteParams);

                    //         if (!empty($student_data->forte_customer_token) && isset($customerPostRequest->paymethod_token)) {
                    //             $customertoken = $student_data->forte_customer_token;
                    //             $paymethodtoken = $customerPostRequest->paymethod_token;
                    //             $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                    //             $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                    //         } else {
                    //             $responsemsg = "Payment processing failed. Please retry";
                    //         }
                    //     }
                    // } else {
                    //     $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
                    //     if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
                    //         $customertoken = $customerPostRequest->customer_token;
                    //         $paymethodtoken = $customerPostRequest->default_paymethod_token;
                    //         $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                    //         $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                    //     } else {
                    //         $customertoken = "";
                    //         $paymethodtoken = "";
                    //     }
                    // }
                    // if (!empty(trim($customerPostRequest->response->response_desc))) {
                    //     $msgError = str_replace("Error[", "<br>Error[", trim($customerPostRequest->response->response_desc));
                    //     $response_msg_error = ltrim($msgError, "<br>");
                    //     $responsemsg = $response_msg_error;
                    // } else {
                    //     $responsemsg = "Payment processing failed. Please retry";
                    // }
                    if (!empty($customertoken) && !empty($paymethodtoken)) {
                        $stu_items_data = $db->get_results("SELECT sfi.*, s.family_id FROM `ss_student_fees_items` sfi
                                    INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`
                                    INNER JOIN ss_user u ON u.id = s.user_id
                                    AND schedule_status = 0 AND sfi.`student_user_id` = '" . $user_id . "'
                                    AND u.is_active=1 AND u.is_deleted=0");
                        if (count((array)$stu_items_data) == 0) {
                            $res = $db->query("insert into ss_student_fees_items set student_user_id='" . $user_id . "',schedule_unique_id='" . $schedule_unique_id . "',  original_schedule_payment_date= '" . $schedule_start_date . "', schedule_payment_date = '" . $schedule_start_date . "', amount='" . $userDataAmount . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', full_payment = 1, schedule_status = 0, created_at = '" . date('Y-m-d H:i') . "', created_by='" . $_SESSION['icksumm_uat_login_userid'] . "'");
                        } elseif (count((array)$stu_items_data) > 0) {
                            foreach ($stu_items_data as $items_data) {
                                $res = $db->query("update ss_student_fees_items set schedule_status = 0, schedule_payment_date = '" . $schedule_start_date . "',  updated_at = '" . date('Y-m-d H:i') . "', updated_by='" . $_SESSION['icksumm_uat_login_userid'] . "' where id = '" . $items_data->id . "' ");
                            }
                        }
                        if ($res) {
                            $check_count++;
                        } else {
                            $db->query('ROLLBACK');
                            echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'msg' => "Payment processing failed. Please retry", 'errpos' => 21]);
                            exit;
                        }
                    } else {
                        //forte customer create failed else
                        $db->query('ROLLBACK');
                        echo json_encode(['code' => '0', 'donorname' => $cardHolderFirstName, 'msg' => "$responsemsg", 'errpos' => 18]);
                        exit;
                    }
                } else {
                    $db->query('ROLLBACK');
                    echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '-2'));
                    exit;
                }
            } else {
                $db->query('ROLLBACK');
                echo json_encode(array('code' => "0", 'msg' => 'Payment credential not found.', '_errpos' => '-1'));
                exit;
            }
        } // end foreach student yearly
    } //else end yearly
    if (count($students) == $check_count) {
        $family_data = $db->get_row("SELECT f.*, s.* FROM ss_student s inner join ss_family f on f.id = s.family_id where  f.id = " . $family_id . " ");

        $emailbody_support .= "Dear" . ' ' . $family_data->father_first_name . ' ' . $family_data->father_last_name . ' ' . "Assalamu-alaikum,<br><br>";
        $emailbody_support .= CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . " Payment Information:<br><br>";

        //$emailbody_support .= "<br>Hope you’re doing well. I just wanted to inform you that your payment for Saturday Academy is Schedule on the ".date('jS', strtotime($schedule_start_date))." of ".date('F', strtotime($schedule_start_date))." starting from ".date('F', strtotime($schedule_start_date))." for ".$recurring_month_count." month";
        $emailbody_support .= "your payment for " . CENTER_SHORTNAME . " " . SCHOOL_NAME . ' ' . $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] . ' ' . " is Schedule for " . $recurring_month_count . " months starting from " . my_date_changer($schedule_start_date);
        $emailbody_support .= "<br><br>For any comments or question, please email <a href='mailto:" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";

        //Text Message Start
        $message_text = "Dear " . $family_data->father_first_name . ' ' . $family_data->father_last_name . " ";
        $message_text .= "Hope you are doing well. I just wanted to inform you that your payment for " . CENTER_SHORTNAME . " " . SCHOOL_NAME . " is Schedule for " . $recurring_month_count . " months starting from " . my_date_changer($schedule_start_date) . " Thank You";
        if (!empty($family_data->father_phone)) {
            $receiver_mobile_no = str_replace("-", "", $family_data->father_phone);
        }
        if (!empty($family_data->mother_phone)) {
            $receiver_mobile_no = str_replace("-", "", $family_data->mother_phone);
        }
        // $fetch_limit = 10;
        // $nexmo_mno_ary_index = -1;
        // $nexmo_mno_ary = array('19138843888', '12018172888', '12019030888', '12085042888', '12097279888', '12107145888', '12134091888', '12134094888', '12134097888', '12315590961');
        // $nexmo_mno_ary_index++;
        // if ($nexmo_mno_ary_index == $fetch_limit) {
        //     $nexmo_mno_ary_index = 0;
        // }
        if (strlen($receiver_mobile_no) == 10) {
            $receiver_mobile_no = '+1' . $receiver_mobile_no;
        } elseif (substr($receiver_mobile_no, 0, 2) == '+1') {
            $receiver_mobile_no = substr($receiver_mobile_no, 1);
        }

        // $url = "https://api.twilio.com/2010-04-01/Accounts/AC03d61d504f943a06ee253f79861547b1/Messages.json";
        // $from = "16802213952";
        // $to = $receiver_mobile_no;
        // $id = "AC03d61d504f943a06ee253f79861547b1";
        // $token = "0069c93d420fdd2713c06d0f045dc7de";
        // $data = array (
        //         'From' => $from,
        //         'To' => $to,
        //         'Body' => $message_text,

        //     );
        // $post = http_build_query($data);
        // $x = curl_init($url );
        // curl_setopt($x, CURLOPT_POST, true);
        // curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
        // curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($x, CURLOPT_POSTFIELDS, $post);
        // //var_dump($post);
        // $y = curl_exec($x);
        // var_dump($y);
        // curl_close($x);
        
        $output=phone_sms($receiver_mobile_no,$message_text); /// function for sending the mobile sms

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, "https://rest.nexmo.com/sms/json");
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "api_key=" . APIKEY . "&api_secret=" . APISECRET . "&to=" . $receiver_mobile_no . "&from=" . $nexmo_mno_ary[$nexmo_mno_ary_index] . "&text=" . $message_text);
        // $output = curl_exec($ch);
        // $info = curl_getinfo($ch);
        // curl_close($ch);

        $dec = json_decode($output, true);
        $rowdata = json_encode($output, true);
        //Text Message End

        $mailservice_request_from = MAIL_SERVICE_KEY;
        $mail_service_array = array(
            'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Payment Schedule Reminder',
            'message' => $emailbody_support,
            'request_from' => $mailservice_request_from,
            'attachment_file_name' => $get_email->reg_form_term_cond_attach_url,
            'attachment_file' => $attachmentFiles,
            'to_email' => [$family_data->primary_email, $family_data->secondary_email],
            'cc_email' => '',
            'bcc_email' => '',
        );

        mailservice($mail_service_array);

        $db->query('COMMIT');
        echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> Schedule successfully </p>'));
        exit;
    } else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '-9'));
        exit;
    }
    /////////////////////////////// /////////////// get_reg_pay_history //////////////////////////////////    
} elseif ($_POST['action'] == 'get_reg_pay_history') {
    if(!empty(get_country()->currency)){
        $currency = get_country()->currency;
    }else{
        $currency = '';
    }
    //ADDED ON 26-SEP-2021
    $get_student = $db->get_results("SELECT  schreg.id as reg_id, schreg.father_first_name, schreg.father_last_name, schreg.father_phone, schreg.father_email, schreg.city, schreg.amount_received, txn.payment_unique_id, txn.comments, txn.payment_status, txn.payment_date, GROUP_CONCAT(CONCAT(' ',reg.first_name,' ',reg.last_name)) AS kids_names,
    schreg.is_waiting, schreg.internal_registration  
    FROM ss_family f 
    INNER JOIN ss_student s ON s.family_id = f.id 
    INNER JOIN ss_user u ON u.id = s.user_id 
    LEFT JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    LEFT JOIN ss_sunday_sch_req_child reg ON reg.user_id = s.user_id 
    LEFT JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.sunday_school_reg_id 
    LEFT JOIN ss_sunday_school_reg schreg ON schreg.id = reg.sunday_school_reg_id 
    WHERE f.id='" . $_POST['familyid'] . "' AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 group by reg_id;");

    $html_body = "";

    if (count((array)$get_student) > 0) {

        // $html_body.= '<legend class="text-semibold">Parent Information </legend>';
        // $html_body .= '<div class="row">';
        // $html_body .= '<div class="col-md-6"> <label>Parent Name:</label> ' . $get_student[0]->father_first_name . ' ' . $row->father_last_name . ' </div>';
        // $html_body .= '<div class="col-md-6"> <label>Parent Email:</label> ' . $get_student[0]->father_email . ' </div>';
        // $html_body .= '<div class="col-md-6"> <label>Parent Phone:</label> ' . $get_student[0]->father_phone . ' </div>';
        // $html_body .= '<div class="col-md-6"> <label>City:</label> ' . $get_student[0]->city . ' </div>';
        //$child_name = "";
        $html_body_table = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Students</th>
                            <th>Details</th>
                            <th></th>
                        </tr>
                    </thead>
                <tbody>';

        foreach ($get_student as $key => $row) {

            $get_registration = $db->get_results("SELECT reg.amount_received, txn.payment_unique_id, txn.comments, txn.payment_status, txn.payment_date FROM ss_sunday_school_reg reg INNER JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.id WHERE reg.id ='" . $row->reg_id . "'");
            //$child_name .= $row->kids_names.',';

            if ($row->is_waiting == 0 && $row->internal_registration == 0) {
                if (count((array)$get_registration) > 0) {
                    foreach ($get_registration as $get_reg) {
                        if ($get_reg->payment_status == 1) {
                            $payment_status = 'Success';
                        }
                        if (!empty($get_reg->payment_date)) {
                            $payment_date = my_date_changer($get_reg->payment_date);
                        }
                        if (!empty($get_reg->amount_received)) {
                            $payment_amount = $currency . $get_reg->amount_received;
                        }
                        if (!empty($get_reg->payment_unique_id)) {
                            $transaction_id = $get_reg->payment_unique_id;
                        }
                        if (!empty($get_reg->comments)) {
                            $comments = $get_reg->comments;
                        }

                        if (!empty($get_reg->payment_date) && !empty($get_reg->amount_received) && !empty($get_reg->payment_unique_id)) {
                            $html_body_table .= '<tr>
                                    <td>' . $row->kids_names . '</td>
                                    <td><strong>Date:</strong> ' . $payment_date . ' | <strong>Amount:</strong> ' . $payment_amount . ' | <strong>Txn Id:</strong> ' . $transaction_id . ' | <strong>Status:</strong> ' . $payment_status . ' | <strong>Comments:</strong> ' . $comments . '</td>';
                            $html_body_table .= "<td><a href='" . SITEURL . "ajax/ajss-download-registraion-receipt-pdf?id=" . $row->reg_id . "&familyid=" . $_POST['familyid'] . "'  class='text-primary action_link' title='Send Message'>Download Receipt</a></td>";
                            $html_body_table .= '</tr>';
                        } else {
                            $html_body_table .= '<tr>
                                    <td>' . $row->kids_names . '</td>
                                    <td>Student is added from the ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel</td>
                                    </tr>';
                        }
                    }
                } else {
                    $html_body_table .= '<tr>
                                <td>' . $row->kids_names . '</td>
                                <td>Student is added from the ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel</td>
                                </tr>';
                }
            }elseif($row->is_waiting == 0 && $row->internal_registration == 1 && $row->amount_received!=0){
                if (count((array)$get_registration) > 0) {
                    foreach ($get_registration as $get_reg) {
                        if ($get_reg->payment_status == 1) {
                            $payment_status = 'Success';
                        }
                        if (!empty($get_reg->payment_date)) {
                            $payment_date = my_date_changer($get_reg->payment_date);
                        }
                        if (!empty($get_reg->amount_received)) {
                            $payment_amount = $currency . $get_reg->amount_received;
                        }
                        if (!empty($get_reg->payment_unique_id)) {
                            $transaction_id = $get_reg->payment_unique_id;
                        }
                        if (!empty($get_reg->comments)) {
                            $comments = $get_reg->comments;
                        }

                        if (!empty($get_reg->payment_date) && !empty($get_reg->amount_received) && !empty($get_reg->payment_unique_id)) {
                            $html_body_table .= '<tr>
                                    <td>' . $row->kids_names . '</td>
                                    <td><strong>Date:</strong> ' . $payment_date . ' | <strong>Amount:</strong> ' . $payment_amount . ' | <strong>Txn Id:</strong> ' . $transaction_id . ' | <strong>Status:</strong> ' . $payment_status . ' | <strong>Comments:</strong> ' . $comments .'<strong>By:</strong>['. $_SESSION['icksumm_uat_login_usertypesubgroup'] . '] </td>';


                            $html_body_table .= "<td><a href='" . SITEURL . "ajax/ajss-download-registraion-receipt-pdf?id=" . $row->reg_id . "&familyid=" . $_POST['familyid'] . "'  class='text-primary action_link' title='Send Message'>Download Receipt</a></td>";
                            $html_body_table .= '</tr>';
                        } else {
                            $html_body_table .= '<tr>
                                    <td>' . $row->kids_names . '</td>
                                    <td>Student is added from the ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel</td>
                                    </tr>';
                        }          
                    }
                } else {
                    $html_body_table .= '<tr>
                                <td>' . $row->kids_names . '</td>
                                <td>Student is added from the ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel</td>
                                </tr>';
                }
            } elseif ($row->is_waiting == 1 && $row->internal_registration == 0) {
                $html_body_table .= '<tr>
                                <td>' . $row->kids_names . '</td>
                                <td>The student was registered from the waiting list, and the payment has not yet been captured.</td>
                                </tr>';
            } else {
                $html_body_table .= '<tr>
                                <td>' . $row->kids_names . '</td>
                                <td>Student is added from the ' . $_SESSION['icksumm_uat_login_usertypesubgroup'] . ' panel</td>
                                </tr>';
            }
        }
        $html_body_table .= '</tbody></table>';
        // $html_body .= '<div class="col-md-6"> <label>Child (ren) :</label> ' . $get_student[0]->kids_names  . ' </div>';
        $html_body .= '</div>';
        $html_body .= $html_body_table;
    } else {
        $html_body .= '<h5>Data not found..</h5>';
    }
    echo $html_body;
    ///////////////////////////////////////////////   family_info_communication ///////////////////////////////////////  
} elseif ($_POST['action'] == 'family_info_communication') {
    $comm_email_or_msg = $_POST['communication_check'];
    $communication_msg = $_POST['communication_msg'];
    $family_id = $_POST['family_id'];
    $father_phone_no = $text = str_replace('-', '', $_POST['father_phone_no']);
    $primary_email = $_POST['primary_email'];
    $user_id = $db->get_var("SELECT user_id FROM ss_family WHERE id = '" . $family_id . "'");
    $db->query("BEGIN");
    $processStatus = false;
    if (!empty($comm_email_or_msg)) {
        if ($comm_email_or_msg == 1) {
            $sql_bulk_msg = $db->query("insert into ss_bulk_message set subject = 'Communication', message = '" . $db->escape(addslashes($communication_msg)) . "', is_report_gen = 0, request_from = 'Communication', scheduled_time = '" . date('Y-m-d H:i:s') . "', created_on = '" . date('Y-m-d H:i:s') . "'");
            $bulk_msg_id = $db->insert_id;
            if ($bulk_msg_id > 0) {
                if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $bulk_msg_id . "', receiver_email = '" . $primary_email . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
                    $processStatus = true;
                }
            }
        } else {
            $sql_bulk_sms = $db->query("insert into ss_bulk_sms set message = '" . $db->escape($communication_msg) . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
            $bulk_sms_id = $db->insert_id;
            if ($bulk_sms_id > 0) {
                if ($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $bulk_sms_id . "', receiver_user_id = " . $user_id . ",
            receiver_mobile_no = '" . $father_phone_no . "', delivery_status = 2, attempt_counter = 0")) {
                    $processStatus = true;
                }
            }
        }

        if ($processStatus == true && $db->query('COMMIT') !== false) {
            echo json_encode(array('code' => "1", 'msg' => "<p class='text-success'>Message Sent Successfully.</p>"));
            exit;
        } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'>Failed Message.</p>", '_errpos' => 2));
            exit;
        }
    } else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'>Process Failed.</p>", '_errpos' => 1));
        exit;
    }
}
///Change Primary Email Request by Parent /Teacher
elseif ($_POST['action'] == 'change_email') {
    $db->query("BEGIN");
    $email_name = $_POST['email_name'];
    //0 means parent
    //1 means teacher/staff
    $user_type=$_POST['type'];
    $user_id=trim($_POST['user_id']);

    // $user_id = $_SESSION['icksumm_uat_login_userid'];
    // $sql="SELECT * from ss_change_email_request where new_email = '" . $_POST['email_name'] . "'";
    $is_exist = $db->get_var("SELECT * from ss_user where email = '" . $email_name . "'");
    // $is_exist = $db->get_var("SELECT * from ss_change_email_request where new_email = '" . $_POST['email_name'] . "'");
  
    if (!$is_exist) {
  
        $inserted_Data=$db->query("INSERT INTO `ss_change_email_request`( `userid`, `new_email`, `created_by_user_id`, `created_on`, `status`, `user_type`) VALUES ('" . $user_id . "','" . $email_name . "','" . $user_id . "','" . date('Y-m-d H:i:s') . "','0','" . $user_type . "')");
        
        if ($inserted_Data ) {
            $db->query('COMMIT');

            echo json_encode(array('code' => "1", 'msg' => "<p class='text-success'>Check your Entered Email for the verification.</p>", '_errpos' => 1));
            

            $new_email_body=""; 
            
            $new_email_body.='<!DOCTYPE html>; 
            <html>
            
            <head>
                <title></title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                <style type="text/css">';
            
            $new_email_body.="        @media screen {
                        @font-face {
                            font-family: 'Lato';
                            font-style: normal;
                            font-weight: 400;
                            src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
                        }
            
                        @font-face {
                            font-family: 'Lato';
                            font-style: normal;
                            font-weight: 700;
                            src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format('woff');
                        }
            
                        @font-face {
                            font-family: 'Lato';
                            font-style: italic;
                            font-weight: 400;
                            src: local('Lato Italic'), local('Lato-Italic'), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format('woff');
                        }
            
                        @font-face {
                            font-family: 'Lato';
                            font-style: italic;
                            font-weight: 700;
                            src: local('Lato Bold Italic'), local('Lato-BoldItalic'), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format('woff');
                        }
                    }
            
                    /* CLIENT-SPECIFIC STYLES */
                    body,
                    table,
                    td,
                    a {
                        -webkit-text-size-adjust: 100%;
                        -ms-text-size-adjust: 100%;
                    }
            
                    table,
                    td {
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                    }
            
                    img {
                        -ms-interpolation-mode: bicubic;
                    }
            
                    /* RESET STYLES */
                    img {
                        border: 0;
                        height: auto;
                        line-height: 100%;
                        outline: none;
                        text-decoration: none;
                    }
            
                    table {
                        border-collapse: collapse !important;
                    }
            
                    body {
                        height: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                    }
            
                    /* iOS BLUE LINKS */
                    a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                    }
            
                    /* MOBILE STYLES */
                    @media screen and (max-width:600px) {
                        h1 {
                            font-size: 32px !important;
                            line-height: 32px !important;
                        }
                    }";
            
                    /* ANDROID CENTER FIX */
            $new_email_body.='       div[style*="margin: 16px 0;"] {
                        margin: 0 !important;
                    }
                </style>
            </head>
            
            <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
                <!-- HIDDEN PREHEADER TEXT -->';
            
            $new_email_body.='<div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: "Lato", Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">';
            
            $new_email_body.='
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <!-- LOGO -->
                    <tr>
                        <td bgcolor="#1a7339" align="center">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>
                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#1a7339" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">';
            $new_email_body.= '<tr>
                                    <td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                        <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Welcome!</h1> <img src=" https://img.icons8.com/clouds/100/000000/handshake.png" width="125" height="120" style="display: block; border: 0px;" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>';
            $new_email_body.= '<td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family:  "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                        <p style="margin: 25px;">You are receiving this email because(you may be or someone else) wanted to change your email.If you requested to change your email, please go to the following button.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td bgcolor="#ffffff" align="left">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center" style="border-radius: 3px;" bgcolor="#1a7339"><a href="'.SITEURL.'change_email_req_confirm?id='.md5($user_id) .'" target="_blank" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #1a7339; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #1a7339; display: inline-block;">Confirm Account</a></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr> <!-- COPY -->
                                <tr>
                                    <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 0px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                        <p style="margin: 25px;">If you did not request this, please ignore this email and your email will not be changed.</p>
                                    </td>
                                </tr> <!-- COPY -->
                 
                                <tr>
                                    <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 20px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                        <p style="margin: 25px;">If you have any questions, feel free to contact the school.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">';
                    $new_email_body .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                    $new_email_body .= '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
            
            $new_email_body.= '
                </table>
            </body>
            
            </html>';
            
            $mail_service_array = array(
                'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - Primary Email Updation Request',
                'message' => $new_email_body,
                'request_from' => MAIL_SERVICE_KEY,
                'attachment_file_name' => [],
                'attachment_file' => [],
                'to_email' => [$email_name],
                'cc_email' => '',
                'bcc_email' => ''
            );
            mailservice($mail_service_array);
            exit;
        } else {
            // $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'>Failed Message.</p>", '_errpos' => 2));
            exit;
        }
    } else {
        // $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'>This Email id already exits in the system</p>", '_errpos' => 2));
        exit;
    }
}


/////////////////////////////////////////////////////// decline_email ////////////////////////////////////////////
 elseif ($_POST['action'] == 'decline_email') {
    $requested_userid = $_POST['id'];
    $main_usertable_id = $_POST['mainid'];
    $email = $_POST['email'];
    $updated_by_user_id = $_POST['mainid'];
    $is_exist = $db->get_var("SELECT * from ss_user where email = '" . $_POST['email'] . "'");
     $old_email = $db->get_var("SELECT email from ss_user where id = '".$_POST['mainid'] ."'");
    $updated_by_user_id = $_POST['mainid'];
    //echo $sql="update ss_change_email_request set 	status=1, updated_by_user_id='".$updated_by_user_id ."', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $requested_userid . "'";
    if($_POST['user_type']=='Staff'){
    if ($db->query("update ss_change_email_request set status=2, updated_by_user_id='" . $updated_by_user_id . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $requested_userid . "'")) {
      //  $staffinfo = $db->get_row("select stf.first_name,stf.last_name from ss_staff stf  where fam.user_id = '" . $requested_userid . "'");
        $new_email_body = "Dear Staff Assalamu-alaikum,<br><br>";
        $new_email_body .= "Change email request Decline by admin  - <br>";  
        $new_email_body .= "<br><br>JazakAllah Khairan";
        $new_email_body .= "<br>" . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Team';
        $mail_service_array = array(
            'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  change email request declined',
            'message' => $new_email_body,
            'request_from' => MAIL_SERVICE_KEY,
            'attachment_file_name' => [],
            'attachment_file' => [],
            'to_email' => [$old_email],
            'cc_email' => '',
            'bcc_email' => ''
        );
        mailservice($mail_service_array);
        echo json_encode(array('code' => "1", 'msg' => "<p class='text-success'>Decline sucessfully .</p>", '_errpos' => 1));
        exit;
    }
 }else if($_POST['user_type']=='Parent'){
    if ($db->query("update ss_change_email_request set status=2, updated_by_user_id='" . $updated_by_user_id . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $requested_userid . "'")) {
       // $staffinfo = $db->get_row("select stf.first_name,stf.last_name from ss_staff stf  where fam.user_id = '" . $requested_userid . "'");
        $new_email_body = "Dear parent Assalamu-alaikum,<br><br>";
        $new_email_body .= "Change email request Decline by admin <br>";
        $new_email_body .= "For further details if required kindly contact at " . SCHOOL_GEN_EMAIL;
        $new_email_body .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
        $mail_service_array = array(
            'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' -  change email request declined',
            'message' => $new_email_body,
            'request_from' => MAIL_SERVICE_KEY,
            'attachment_file_name' => [],
            'attachment_file' => [],
            'to_email' => [$old_email],
            'cc_email' => '',
            'bcc_email' => ''
        );
        mailservice($mail_service_array);
        echo json_encode(array('code' => "1", 'msg' => "<p class='text-success'>Decline sucessfully .</p>", '_errpos' => 1));
        exit;
 }
}
 else {

        echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'>Failed Message.</p>", '_errpos' => 2));
        exit;
    }
       

}
/////////////////////////////////////////////////////////// get_email_request_data ///////////////////////////
 elseif ($_GET['action'] == 'get_email_request_data') {
    $finalAry = array();

    $email_reqs = $db->get_results("SELECT * from ss_change_email_request  order by id desc");
    foreach ($email_reqs as $email_requests) {

        $temp['id'] = $email_requests->id;
        $temp['new_email'] = $email_requests->new_email;
        $temp['userid'] = $email_requests->userid;
        $temp['created_on'] = $email_requests->created_on;
        $temp['created_by_user_id'] = $email_requests->created_by_user_id;
        $temp['created_by_user_id'] = $email_requests->created_by_user_id;
        $temp['user_type'] = $email_requests->user_type;

        if($email_requests->user_type==0){
            $temp['user_type']='Parent';
            $old_data = $db->get_row("SELECT f.* FROM ss_family as f inner join ss_user as u on u.id=f.user_id where user_id=$email_requests->userid");
            $temp['old_email']=$old_data->primary_email;
            $temp['phone_no']=$old_data->father_phone;

          }else if($email_requests==1){
            $temp['user_type']='Staff';
            $old_data = $db->get_row("SELECT u.email,s.mobile FROM ss_staff as s inner join ss_user as u on u.id=s.user_id where user_id=$email_requests->userid");
            $temp['old_email']=$old_data->email;
            $temp['phone_no']=$old_data->mobile;

          }

        if ($email_requests->status == 0) {
            $status = 'Pending';
        } else  if ($email_requests->status == 1) {
            $status = 'Approved';
        } else if ($email_requests->status == 2) {
            $status = 'Rejected';
        }
        $temp['status'] = $status;
        $temp['statusnum'] = $email_requests->status;
        $temp['student_link'] = "<a href='javascript:void(0)' title='View Full Detail' data-childno='" . $email_requests->id . "' data-version='v2' data-reqno='" . $adm_requests->id . "' class='text-primary viewdetail'>" . $child->first_name . ' ' . $child->last_name . "</a>";
        $admRequests[] = $temp;
    }
    $finalAry['data'] = $admRequests;
    echo json_encode($finalAry);
    exit;
}
//////////////////////////////////////////manual_payment_request ///////////////////////////////////////
elseif ($_POST['action'] == 'manual_payment_request') {

    $family_data = $db->get_row("SELECT * FROM ss_family as f inner join ss_user as u on u.id=f.user_id where f.id='".$_POST['familyid']."'  and u.is_active=1 and u.is_deleted=0");
    
    if(!empty($family_data)){
    $rtn= "";
    
    $rtn .='<div class="row"><div class="col-md-6"><label>Parent Name : </label>'.$family_data->father_first_name.' '.$family_data->father_last_name.'</div>';
    // $rtn .='<div><label>Phone No</label>"'.$family_data->father_phone.'"</div>';
    $rtn .='<div class="col-md-6"><label style="margin-left:10px;">Primary Email : </label>'.$family_data->primary_email.'</div></div>';
    
    echo $rtn;
    exit;
    }else{
        echo "data not found";
        exit;
    }
    }
