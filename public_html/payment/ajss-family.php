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

//AUTHARISATION CHECK
if(!isset($_SESSION['school_login_userid'])){
    return;
}

//==========================LIST ALL STAFF FOR ADMIN=====================
if ($_GET['action'] == 'list_family') {
    if (in_array("su_family_list", $_SESSION['login_user_permissions'])) {
        $finalAry = array();
  
        $all_families = $db->get_results("SELECT DISTINCT f.id, CONCAT(father_first_name,' ',COALESCE(father_last_name, '')) AS father_name
                , CONCAT(mother_first_name,' ',COALESCE(mother_last_name, '')) AS mother_name , f.primary_email, f.father_phone
                , billing_city AS city, billing_post_code AS zipcode FROM ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
                WHERE ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "' and u.is_deleted = 0 ORDER BY father_name ASC", ARRAY_A);
        $finalAry['data'] = $all_families;
        echo json_encode($finalAry);
        exit;
    }
}
//==========================EMAIL LOGIN INFO TO PARENTS=====================
elseif ($_POST['action'] == 'email_login_info_to_parents') {
    if (in_array("su_family_send_login_info", $_SESSION['login_user_permissions'])) {
        $familyid = $_POST['familyid'];
        $family = $db->get_row("SELECT u.* FROM ss_family f INNER JOIN ss_user u ON f.user_id = u.id INNER JOIN ss_usertypeusermap ut 
                ON f.user_id = ut.user_id WHERE ut.user_type_id = '5' AND f.id = '" . $familyid . "' ORDER BY ut.id DESC LIMIT 1");
        // print_r($family);
        // die;
        if (!empty($family) && trim($family->username) != '') {
            $emailbody_parents = "Dear Parents Assalamualaikum,<br><br>You can login in ".CENTER_SHORTNAME." ".SCHOOL_NAME." parent's section using below information:<br>";
            $password_rec_link = SITEURL."new_password.php?id=".md5('iCjC'.$family->id.'1cjc');
            $emailbody_parents.= "<br><br><strong>Login URL:</strong> " . $QURANSCH_SITEURL . "login.php";
            $emailbody_parents.= "<br><br><strong>Username/Email:</strong> " . trim($family->username);
            $emailbody_parents.= "<br><br><strong>Password:</strong> Please use password provided earlier or <a href='".$password_rec_link."'>click here</a> to generate new password.";
            $emailbody_parents.= "<br><br>".SCHOOL_NAME." Team";
         
            $res = send_my_mail(trim($family->username), SCHOOL_NAME.' Login Details', $emailbody_parents);
            $res = send_my_mail("moh.urooj@gmail.com", SCHOOL_NAME.' Login Details', $emailbody_parents);
            if ($res == true) {
                echo json_encode(array('code' => "1", 'msg' => 'Login details sent successfully'));
            } else {
                $return_resp = array('code' => "0", 'msg' => 'Login details not sent. Please try later.', 'err_pos' => 1);
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;
            }
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
    if (in_array("su_family_edit", $_SESSION['login_user_permissions'])) {
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
                updated_by_user_id='" . $_SESSION['school_login_userid'] . "',updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $_SESSION['icksumm_uat_login_familyid'] . "'");
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
    }
}
//==========================STAFF VIEW ONLY INFO=====================
elseif ($_POST['action'] == 'view_family_detail') {
    if (in_array("su_family_view", $_SESSION['login_user_permissions'])) {
        $familyid = $_POST['familyid'];
        $family = $db->get_row("select * from ss_family where id='" . $familyid . "'");
        if ($family->primary_contact == 'Father') {
            $primary_contact = '1st Parent';
        } else {
            $primary_contact = '2nd Parent';
        }
        $students = $db->get_results("SELECT s.user_id, s.dob, s.allergies, s.school_grade, CONCAT(first_name,' ',COALESCE(middle_name, ''),' ',COALESCE(last_name, '')) AS student_name FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id WHERE ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "'  and u.is_deleted = 0 AND s.family_id = '" . $familyid . "'");
        $retStr.= '<legend class="text-semibold">Child Information</legend>';
        $i = 1;
        foreach ($students as $stu) {
            $dob = date('Y-m-d', strtotime($stu->dob));
            $from = new DateTime($dob);
            $to = new DateTime('today');
            $stu_age = $from->diff($to)->y;
            $age = $stu_age . ' Yrs';
            $stugroupclass = $db->get_results("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "'");
            $group = $db->get_row("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "'");
            $retStr.= '<div class="row">';
            $retStr.= '<div class="col-md-4"><label><strong>Child ' . $i . ': </strong></label> ' . $stu->student_name . '</div> <div class="col-md-4"><label><strong>Grade:</strong></label> ' . $stu->school_grade . '</div>';
            $retStr.= '</div>';
            $retStr.= '<div class="row">
                <div class="col-md-4">
                <label><strong>Date of Birth: </strong></label>' . date('m/d/Y', strtotime($stu->dob)) . '
                </div>
                <div class="col-md-4">
                <label><strong>Age:</strong></label>' . $age . '
                </div>';
            if (!empty($stu->allergies)) {
                $retStr.= '<div><label><strong>Allergies:</strong></label> ' . $stu->allergies . ' </div>';
            }
            $retStr.= '</div><br>';
            foreach ($stugroupclass as $row) {
                $retStr.= '<div class="row">
                    <div class="col-md-4">
                    <label>Class:</label>
                    <span>' . $row->class_name . '</span>
                    </div>

                    <div class="col-md-4">
                    <label>Group :</label>
                    <span>' . $row->group_name . '</span>
                    </div>';
                $retStr.= '</div>';
            }
            $retStr.= '<hr>';
            // $retStr .='<legend class="text-semibold">Payment Fees Information</legend>';
            //$studiscountfees = $db->get_results("select fd.discount_name,fd.discount_percent from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='".$stu->user_id."' And fd.status = 1 AND sfd.status = 1");
            //$stubasicfees = $db->get_row("select bcf.fee_amount from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join ss_basicfees bcf on m.group_id = bcf.group_id where m.latest = 1 and m.student_user_id='".$stu->user_id."'");
            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 ");
            $groups = [];
            foreach ($user_groups as $group) {
                $groups[] = $group->id;
            }
            $group_ids = implode(",", $groups);
            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '".$_SESSION['school_CURRENT_SESSION']."'");
            
            $new_discountFeesData = $db->get_results("select * from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='" . $stu->user_id . "' And fd.status = 1 AND sfd.status = 1 ORDER BY discount_percent DESC");
            $discountPercentTotalnew = $basicFees->fee_amount;
            $discountDollarTotalnew = 0;
            foreach ($new_discountFeesData as $val) {
                if ($val->discount_unit == 'p') {
                    $doller = '';
                    $percent = '%';
                    $fee_percent = ($discountPercentTotalnew * $val->discount_percent) / 100;
                    $discountPercentTotalnew = $discountPercentTotalnew - $fee_percent;
                
                } else {
                    $doller = '$';
                    $percent = '';
                    $discountDollarTotalnew+= $val->discount_percent;
                }
                $amount_val = $val->discount_percent + 0;
                $discountDollarDetailes.= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
            }

            //$basicDiscountFees = (100 - $discountPercentTotalnew) / 100 * $basicFees->fee_amount;
            $final_amount = ($discountPercentTotalnew - $discountDollarTotalnew);
            if ($final_amount > 0) {
                $total_final = $final_amount;
            } else {
                $total_final = 0;
            }
            if (!empty($basicFees->fee_amount)) {
                $basic_fee_amount = '$' . ($basicFees->fee_amount + 0);
            } else {
                $basic_fee_amount = '$0';
            }



            $retStr.= '<div class="row">
                <div class="col-md-3">
                <label>Basic Fees:</label>' . $basic_fee_amount . '
                </div>

                <div class="col-md-6">    
                <label>Discount:</label>' . $discountDollarDetailes . ' </div>';
            $retStr.= '<div class="col-md-3">
                <label>Final Amount:</label> ' . $total_final . '
                </div>
                </div>';
            $retStr.= '<hr>';
            $i++;
        }
        $retStr.= '<legend class="text-semibold">Parent Information</legend>
                <div class="row">
                <div class="col-md-4">
                <label>1st Parent Name:</label>' . $family->father_first_name . ' ' . $family->father_last_name . '
                </div>
                <div class="col-md-3">
                <label>Phone:</label>' . $family->father_phone . '
                </div>
                <div class="col-md-5">
                <label>Email:</label>' . $family->primary_email . '
                </div>
                </div>';
        if (!empty($family->mother_first_name)) {
            $retStr.= '<div class="row">
                    <div class="col-md-4">
                    <label>2nd Parent Name:</label>' . $family->mother_first_name . ' ' . $family->mother_last_name . '
                    </div>
                    <div class="col-md-3">
                    <label>Phone:</label>' . $family->mother_phone . '
                    </div>
                    <div class="col-md-5">
                    <label>Email:</label>' . $family->secondary_email . '
                    </div>
                    </div>';
        }
        $retStr.= '<div class="row">
                <div class="col-md-4 col-md-6">
                <label>Primary Contact:</label>' . $primary_contact . '
                </div>
                </div>';
        $billing_state = $db->get_var("select state from ss_state where id = '" . $family->billing_state_id . "'");
        $retStr.= '<div class="row"><div class="col-md-12"><label>Address:</label>' . $family->billing_address_1 . ', ' . $family->billing_address_2 . ', ' . $family->billing_city . ', ' . (trim($billing_state) != '' ? $billing_state : $family->billing_entered_state) . ', ' . $family->billing_post_code . '</div></div>';
        $retStr.= '<div class="row"><div class="col-md-12"><label>Additional Noted:</label>' . $family->addition_notes . '</div></div>';
        echo $retStr;
        exit;
    }
}
//==========================ADD CHILD=====================
elseif ($_POST['action'] == 'add_child') {
    if (in_array("su_student_create", $_SESSION['login_user_permissions'])) {
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
                    session = '" . $_SESSION['school_CURRENT_SESSION'] . "',
                    registerd_by='Admin',
                    is_paid=1,
                    registerd_by_user_id='" . $_SESSION['school_login_userid'] . "',
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
                    echo json_encode(array('code' => "1", 'msg' => 'Add child successfully.'));
                    exit;
                } else {
                    $db->query('ROLLBACK');
                    $return_resp = array('code' => "0", 'msg' => "Add child failed", '_errpos' => 2);
                    echo json_encode($return_resp);
                    exit;
                }
            } else {
                $db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => "Add child failed", '_errpos' => 3);
                echo json_encode($return_resp);
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            $return_resp = array('code' => "0", 'msg' => "Add child failed", '_errpos' => 4);
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    }
}
//==========================ADMIN VIEW ONLY INFO=====================
elseif ($_POST['action'] == 'view_family_detail_payment') {

    if (in_array("su_family_view", $_SESSION['login_user_permissions'])) {
        $familyid = $_POST['familyid'];
        $family = $db->get_row("select ss_family.* from ss_family where id='" . $familyid . "'");
        if ($family->primary_contact == 'Father') {
            $primary_contact = '1st Parent';
        } else {
            $primary_contact = '2nd Parent';
        }


        $students = $db->get_results("SELECT s.user_id, s.dob, s.allergies, s.school_grade, 
        CONCAT(first_name,' ',COALESCE(middle_name, ''),' ',COALESCE(last_name, '')) AS student_name FROM ss_user u 
        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id 
        WHERE ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "'  and u.is_deleted = 0 AND s.family_id = '" . $familyid . "' ");

        $basic_fee_all = 0;
        $discount_fee_val_all = 0;
        $final_amount_all = 0;
        foreach ($students as $stu) {


            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id 
            where g.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' and m.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' 
            and m.student_user_id='" . $stu->user_id . "' and m.latest = 1 ");

            $groups = [];
            foreach ($user_groups as $group) {
                $groups[] = $group->id;
            }
            $group_ids = implode(",", $groups);

            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 
            AND session = '".$_SESSION['school_CURRENT_SESSION']."'");
            $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf 
            INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' and
            d.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' and sf.student_user_id = '" . $stu->user_id . "' AND sf.status = 1  and d.status=1");
        
            $discountPercentTotal = $basicFees->fee_amount;
            $discountDollarTotal = 0;
            
            foreach ($new_discountFeesData as $val) {
                if ($val->discount_unit == 'p') {
                    $doller = '';
                    $percent = '%';
                    $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                    $discountPercentTotal = $discountPercentTotal - $fee_percent;
                } else {
                    $doller = '$';
                    $percent = '';
                    $discountDollarTotal+= $val->discount_percent;
                }
            }
            $basic_fee_all+= $basicFees->fee_amount;
            // echo $discountPercentTotal.'<br>';
            // echo $discountDollarTotal;
            // die;

            $final_amount = ($discountPercentTotal - $discountDollarTotal);
            if ($final_amount > 0) {
                $total_final_amount = $final_amount;
            } else {
                $total_final_amount = 0;
            }
            $final_amount_all+= $total_final_amount;

        }

        $discount_fee_val_all = $basic_fee_all - $final_amount_all;

        //REQUESTED DISCOUNTS AT THE TIME OF REGISTRATION
        $requested_discounts_str = $db->get_var("SELECT r.`discount_type` FROM `ss_sunday_sch_req_child` rc 
        INNER JOIN `ss_sunday_school_reg` r ON r.id = rc.`sunday_school_reg_id` WHERE rc.`user_id` = '" . $stu->user_id . "'");
        $requested_discounts_ary = explode('|',$requested_discounts_str);

        $requested_discounts_str = '';
        foreach($requested_discounts_ary as $req_dis){
        $requested_discounts_str .= ucwords(str_replace('_',' ', $req_dis)).', ';
        }
        $requested_discounts_str = rtrim($requested_discounts_str, ', ');

        // $retStr.= '<div class="row">';
        // $retStr.= '<div class="col-md-12"><label>Requested Discount:</label> '.$requested_discounts_str.'</div>';
        // $retStr.= '</div><br>';

        $retStr.= '<legend class="text-semibold">Total Fee Information</legend>
                <div class="row viewonly">
                <div class="col-md-2">
                <label>Basic Fees</label>
                <p>$' . ($basic_fee_all + 0) . '</p>
                </div>
                <div class="col-md-2">
                <label>Discount</label>
                <p>$' . ($discount_fee_val_all + 0) . '</p>
                </div>
                <div class="col-md-2">
                <label>Net Fees</label>
                <p>$' . ($final_amount_all + 0) . '</p>
                </div>
                <div class="col-md-3"><label>Requested Discount</label><p>'.$requested_discounts_str.'</p>
                </div>
                <div class="col-md-3">
                <a href="' . SITEURL .'payment/payment_fees_history_list?id=' . $familyid  . '"" style="float:right;" class="text-warning action_link"> Schedule Payment </a>
                </div>
                </div>
                ';                

        $retStr.= '<legend class="text-semibold">Child Information</legend>';
        $i = 1;
        foreach ($students as $stu) {
            $dob = date('Y-m-d', strtotime($stu->dob));
            $from = new DateTime($dob);
            $to = new DateTime('today');
            $stu_age = $from->diff($to)->y;
            $age = $stu_age . ' Yrs';
            $group = $db->get_row("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu->user_id . "'");
            $retStr.= '<div style="float:right; margin-right:10px;"><a href="' . SITEURL .'payment/schedule_payment?id=' . $stu->user_id . '"" class="text-warning action_link">Payment History</a></div>';
            $retStr.= '<div style="float:right; margin-right:10px;"><a href="' . SITEURL . 'student/student_edit?id=' . $stu->user_id . '&page=family_info" class="text-primary action_link">Edit</a></div>';
            $retStr.= '<div class="row">';
            $retStr.= '<div class="col-md-4"><label>Child ' . $i . ': </label> ' . $stu->student_name . '</div> <div class="col-md-4"><label>Grade:</label> ' . $stu->school_grade . '</div>';
            $retStr.= '</div>';
            $retStr.= '<div class="row">
                    <div class="col-md-4"> 
                    <label>Date of Birth: </label>' . date('m/d/Y', strtotime($stu->dob)) . '
                    </div>
                    <div class="col-md-3">
                    <label>Age:</label>' . $age . '
                    </div>';
            if (!empty($stu->allergies)) {
                $retStr.= '<div class="col-md-4"><label>Allergies:</label> ' . $stu->allergies . ' </div>';
            }
            $retStr.= '</div><br>';            

            // $retStr .='<legend class="text-semibold">Payment Fees Information</legend>';
            $all_discounts = $db->get_results("select fd.id, fd.discount_name,fd.discount_percent, fd.discount_unit, fd.created_on 
            from ss_fees_discounts fd  where  fd.status = 1 and fd.session = '".$_SESSION['school_CURRENT_SESSION']."'");
            
            // $studiscountfees = $db->get_results("select fd.id from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  
            // where sfd.student_user_id=SELECT r.`discount_type` FROM `ss_sunday_sch_req_child` rc INNER JOIN `ss_sunday_school_reg` r ON r.id = rc.`sunday_school_reg_id`
            // WHERE rc.`user_id` = ' . $stu->user_id . ' And fd.status = 1 AND sfd.status = 1 and fd.session = '".$_SESSION['school_CURRENT_SESSION']."' 
            // and sfd.session = '".$_SESSION['school_CURRENT_SESSION']."'");

            $studiscountfees = $db->get_results("select fd.id from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  where sfd.student_user_id='" . $stu->user_id . "' And fd.status = 1 AND sfd.status = 1 and fd.session = '".$_SESSION['school_CURRENT_SESSION']."' and sfd.session = '".$_SESSION['school_CURRENT_SESSION']."' ");

            // $studiscountfees = $db->get_results("select fd.id from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id  
            // where sfd.student_user_id=' . $stu->user_id . ' And fd.status = 1 AND sfd.status = 1 and fd.session = '".$_SESSION['school_CURRENT_SESSION']."' 
            // and sfd.session = '".$_SESSION['school_CURRENT_SESSION']."'");

            $newstuarray = [];
            foreach ($studiscountfees as $discountid) {
                $newstuarray[] = $discountid->id;
            }
            $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id 
            where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '".$_SESSION['school_CURRENT_SESSION']."' and 
            g.session = '".$_SESSION['school_CURRENT_SESSION']."'");
            $groups = [];
            foreach ($user_groups as $group) {
                $groups[] = $group->id;
            }
            $group_ids = implode(",", $groups);
            $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 
            AND session = '".$_SESSION['school_CURRENT_SESSION']."'");
            
            $new_discountFeesData = $db->get_results("select * from ss_fees_discounts fd inner join ss_student_feesdiscounts sfd on fd.id = sfd.fees_discount_id 
            where sfd.student_user_id='" . $stu->user_id . "' And fd.status = 1 AND sfd.status = 1 and fd.session = '".$_SESSION['school_CURRENT_SESSION']."' 
            and sfd.session = '".$_SESSION['school_CURRENT_SESSION']."' ORDER BY discount_percent DESC");
            $discountPercentTotalnew = $basicFees->fee_amount;
            $discountDollarTotalnew = 0;
            foreach ($new_discountFeesData as $val) {
                if ($val->discount_unit == 'p') {
                    $doller = '';
                    $percent = '%';
                    $fee_percent = ($discountPercentTotalnew * $val->discount_percent) / 100;
                    $discountPercentTotalnew = $discountPercentTotalnew - $fee_percent;
                
                } else {
                    $doller = '$';
                    $percent = '';
                    $discountDollarTotalnew+= $val->discount_percent;
                }
                $amount_val = $val->discount_percent + 0;
                $discountDollarDetailes.= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
            }

            //$basicDiscountFees = (100 - $discountPercentTotalnew) / 100 * $basicFees->fee_amount;
            $final_amount = ($discountPercentTotalnew - $discountDollarTotalnew);
            if ($final_amount > 0) {
                $total_final = $final_amount;
            } else {
                $total_final = 0;
            }
            if (!empty($basicFees->fee_amount)) {
                $basic_fee_amount = '$' . ($basicFees->fee_amount + 0);
            } else {
                $basic_fee_amount = '$0';
            }
            $retStr.= '<div class="row">
                    <div class="col-md-4">
                    <label>Basic Fees:</label> ' . $basic_fee_amount . '
                    </div>';
            $retStr.= '<div class="col-md-4">
                    <label>Final Amount:</label>$' . $total_final . '
                    </div>';
            $retStr.= '</div>';
            $retStr.= '<div class="row"><div class="col-md-12">       
                    <label>Discount:</label>';
            foreach ($all_discounts as $studiscountfee) {
                if ($studiscountfee->discount_unit == 'd') {
                    $doller = '$';
                    $percent = '';
                } elseif ($studiscountfee->discount_unit == 'p') {
                    $percent = '%';
                    $doller = '';
                }

                if(in_array($studiscountfee->id, $newstuarray)){
                    $selected = 'checked="checked"';
                    $discount_date = $db->get_var("select sfd.created_on from ss_student_feesdiscounts sfd where sfd.student_user_id='" . $stu->user_id . "' And sfd.fees_discount_id = '" . $studiscountfee->id . "' And sfd.status = 1");
                    $data_add_discount = date('m/d/Y H:i A',strtotime($discount_date));
                    $texttobox = "Applied on $data_add_discount";
                }else{
                    $selected = '';
                    $texttobox = "";
                }

                $retStr.= '<input type="checkbox" name="discount[' . $stu->user_id . '][]" class="discount form-check-input" '. $selected . ' title="'.$texttobox.'"  value="' . $studiscountfee->id . '"> ' . $studiscountfee->discount_name . ' (' . $doller . '' . ($studiscountfee->discount_percent + 0) . '' . $percent . ' ) &nbsp&nbsp';
            }
            $retStr.= '<input type="hidden" name="student_ids[]" class="student_ids" checked="checked"  value="' . $stu->user_id . '">';
            $retStr.= '</div></div>';
            $retStr.= '<hr>';
            $i++;
        }
        $retStr.= '<legend class="text-semibold">Parent Information</legend>
                    <div class="row">
                    <div class="col-md-4">
                    <label>1st Parent Name:</label>' . $family->father_first_name . ' ' . $family->father_last_name . '
                    </div>
                    <div class="col-md-3">
                    <label>Phone:</label>' . $family->father_phone . '
                    </div>
                    <div class="col-md-5">
                    <label>Email:</label>' . $family->primary_email . '
                    </div>
                    </div>';
        if (!empty($family->mother_first_name)) {
            $retStr.= '<div class="row">
                    <div class="col-md-4">
                    <label>2nd Parent Name:</label>' . $family->mother_first_name . ' ' . $family->mother_last_name . '
                    </div>
                    <div class="col-md-3">
                    <label>Phone:</label>' . $family->mother_phone . '
                    </div>
                    <div class="col-md-5">
                    <label>Email:</label>' . $family->secondary_email . '
                    </div>
                    </div>';
        }
        $retStr.= '<div class="row">
                    <div class="col-md-4 col-md-6">
                    <label>Primary Contact:</label>' . $primary_contact . '
                    </div>
                    </div>';
        $billing_state = $db->get_var("select state from ss_state where id = '" . $family->billing_state_id . "'");
        $retStr.= '<div class="row"><div class="col-md-12"><label>Address:</label>' . $family->billing_address_1 . ', ' . $family->billing_address_2 . ', ' . $family->billing_city . ', ' . (trim($billing_state) != '' ? $billing_state : $family->billing_entered_state) . ', ' . $family->billing_post_code . '</div></div>';
        $retStr.= '<div class="row"><div class="col-md-12"><label>Additional Noted:</label>' . $family->addition_notes . '</div></div>';
        $paymentcred = $db->get_results("select * from ss_paymentcredentials where family_id='" . $familyid . "' AND credit_card_deleted =0");
        $retStr.= '<legend class="text-semibold">Payment Info</legend>
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
            $retStr.= '<tr role="row" class="odd">';
            $retStr.= '<td tabindex="0">************ ' . substr($credit_card_no, -4) . '</td>';
            $retStr.= '<td> ' . $credit_card_exp_month . '/' . $credit_card_exp_year . ' </td>';
            $retStr.= '<td> ' . $default . ' </td>';
            $retStr.= '<td> ' . $btn . ' </td>';
            $retStr.= '</tr>';
        }
        $retStr.= '</tbody>
                    </table>
                    </div>';
        echo $retStr;
        exit;
    }
} elseif ($_POST['action'] == 'family_info_submit') {
    $db->query('BEGIN');
    if (!empty($_POST['default_card'])) {
        $db->query("update ss_paymentcredentials set default_credit_card=0  where family_id='" . $_POST['family_id'] . "' ");
        $paymentcred = $db->query("update ss_paymentcredentials set default_credit_card=1, updated_on='" . date('Y-m-d H:i:s') . "'  where id='" . $_POST['default_card'] . "' ");
    }

    if (isset($_POST['discount'])) {
        $discounted_stu_ids = [];
        foreach ($_POST['discount'] as $user_id => $vals) {
            $discounted_stu_ids[] = $user_id;
        }

        ///////////////////////////////////////////////////
        foreach ($_POST['student_ids'] as $user_id) {

            if (in_array($user_id, $discounted_stu_ids)) {
                //Has Discount
                //old discounts

                $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
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
                    $old_discountDetailes.= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
                }
                $result = array_diff($fees_discount_ids, $_POST['discount'][$user_id]);

                if (count((array)$result) >= 0) {
                    $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "' ");
                    foreach ($_POST['discount'][$user_id] as $discount_id) { 
                        $sql_ret = $db->query("insert into ss_student_feesdiscounts set 
                            udated_by_user_id = '" . $_SESSION['school_login_userid'] . "', created_by_user_id = '" . $_SESSION['school_login_userid'] . "', fees_discount_id = '" . $discount_id . "', updated_on='" . date('Y-m-d H:i:s') . "', session='".$_SESSION['school_CURRENT_SESSION']."',  created_on='" . date('Y-m-d H:i:s') . "', student_user_id = '" . $user_id . "' ");
                        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 ");
                        $groups = [];
                        foreach ($user_groups as $group) {
                            $groups[] = $group->id;
                        }
                        $group_ids = implode(",", $groups);
                        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '".$_SESSION['school_CURRENT_SESSION']."' ");
                        $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
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
                                $discountDollarTotal+= $val->discount_percent;
                            }
                            $amount_val = $val->discount_percent + 0;
                            $new_discountDetailes.= $val->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
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
                                $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' , current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "', comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['school_login_userid'] . "'  ");
                                $sql_ret = $db->query("update ss_student_fees_items set amount='" . $actualbasicDiscountFees . "' , updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                            }
                        }
                    }
                }
            } else {
                //No discount
                //old discounts
                $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
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
                    $old_discountDetailes.= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
                }
                if (isset($_POST['discount'][$user_id])) {
                    $result = array_diff($fees_discount_ids, $_POST['discount'][$user_id]);
                }
                if (!isset($_POST['discount'][$user_id]) || count((array)$result) > 0) {
                    $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $user_id . "' ");
                    $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 ");
                    $groups = [];
                    foreach ($user_groups as $group) {
                        $groups[] = $group->id;
                    }
                    $group_ids = implode(",", $groups);
                    $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '".$_SESSION['school_CURRENT_SESSION']."'");
                    $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong>";
                    $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND (schedule_status = 0 OR schedule_status = 3) ");
                    if (count((array)$student_fees_items) > 0) {
                        foreach ($student_fees_items as $items) {
                            $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' , current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "', comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['school_login_userid'] . "'  ");
                            $sql_ret = $db->query("update ss_student_fees_items set amount='" . $basicFees->fee_amount . "' , updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
                        }
                    }
                }
            }
        }
        ///////////////////////////////////////////////////////////
        
    } else {
        $students = $db->get_results("SELECT s.user_id FROM ss_user u INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "' and u.is_active = 1  and u.is_deleted = 0 AND s.family_id = '" . $_POST['family_id'] . "' ");
        foreach ($students as $row) {
            $old_discountDetailes = "";
            $user_id = $row->user_id;
            //old discounts
            $old_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
            foreach ($old_discountFeesData as $value) {
                if ($value->discount_unit == 'p') {
                    $doller = '';
                    $percent = '%';
                } else {
                    $doller = '$';
                    $percent = '';
                }
                $amount_val = $value->discount_percent + 0;
                $old_discountDetailes.= $value->discount_name . ' ( ' . $doller . '' . $amount_val . '' . $percent . '  ), ';
            }
            if (count((array)$old_discountFeesData) > 0) {
                $db->query("delete from ss_student_feesdiscounts where student_user_id = '" . $row->user_id . "' ");
                $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 ");
                $groups = [];
                foreach ($user_groups as $group) {
                    $groups[] = $group->id;
                }
                $group_ids = implode(",", $groups);
                $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '".$_SESSION['school_CURRENT_SESSION']."'");
                $comments = "<strong>Discount Update  </strong><br><strong>Preview Discount : </strong>" . $old_discountDetailes . " <br> <strong>Current Discount : </strong> ";
                $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND (schedule_status = 0 OR schedule_status = 3) ");
                if (count((array)$student_fees_items) > 0) {
                    foreach ($student_fees_items as $items) {
                        $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' , current_status='" . $items->schedule_status . "', new_status='" . $items->schedule_status . "', comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['school_login_userid'] . "'  ");
                        $sql_ret = $db->query("update ss_student_fees_items set amount='" . $basicFees->fee_amount . "' , updated_at='" . date('Y-m-d H:i:s') . "' where id = '" . $items->id . "'");
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
    
}





elseif ($_POST['action'] == 'get_stu_schedule') {
    $family_id = $_POST['familyid'];
    $students = $db->get_results("select s.user_id, s.first_name, s.last_name from ss_student s inner join ss_user u on u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where s.family_id= '" . $family_id . "' AND u.is_locked=0 AND u.is_active=1 and u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "' ");
    $student_ids_exist = "";
    foreach ($students as $row) {
        $check_exist = $db->get_row("select * from ss_student_fees_items where student_user_id = '" . $row->user_id . "' AND (schedule_status = 0 OR schedule_status = 3)");
        if (!empty($check_exist)) {
            $student_ids_exist.= $row->user_id . ",";
        }
    }
 
  $schedule_stu_ids = rtrim($student_ids_exist, ",");

  if(!empty($schedule_stu_ids)){
        echo json_encode(array('code' => "1", 'stuids' => "$schedule_stu_ids" ));
        exit;
   }else{
        echo json_encode(array('code' => "0", 'msg' => " Child(ren) : ".$student_all." <br> Status : Payment Already Scheduled"));
        exit;
   }
      

} 





elseif ($_POST['action'] == 'get_stu_not_schedule') {

    $family_id = $_POST['familyid'];
    $students = $db->get_results("select s.user_id, s.first_name, s.last_name from ss_student s inner join ss_user u on u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where s.family_id= '" . $family_id . "' AND u.is_locked=0 AND u.is_active=1 and u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "' ");
    $student_names = "";
    $student_ids_not_exist = "";
    $student_ids_exist = [];
    $total_stu_family = "";
    foreach ($students as $row) {
        $check_exist = $db->get_row("select * from ss_student_fees_items where student_user_id = '" . $row->user_id . "' AND (schedule_status = 0 OR schedule_status = 3)");
        if (empty($check_exist)) {
            $student_names.= $row->first_name . ' ' . $row->last_name . ", ";
            $student_ids_not_exist.= $row->user_id . ",";
        }else{
            $student_ids_exist[] = $row->user_id;
        }
        $total_stu_family .= $row->first_name . ' ' . $row->last_name . ", ";
    }

    if(count((array)$student_ids_exist) > 0 && !empty($student_ids_exist)) {
        // $stu_user_id = $student_ids_exist[0];
        $stu_user_id = implode(",", $student_ids_exist);
        $sch_item_count = $db->get_results("SELECT * FROM ss_student_fees_items WHERE student_user_id IN (" . $stu_user_id . ") AND (schedule_status = 0 OR schedule_status = 3) GROUP BY schedule_payment_date ORDER BY schedule_payment_date ASC"); 
       
        if($sch_item_count[0]->schedule_payment_date >= Date('Y-m-d')){
            $quantity = count((array)$sch_item_count);
            $schedule_payment_start_date = date("m/d/Y", strtotime($sch_item_count[0]->schedule_payment_date));
        }else{
            $quantity = count((array)$sch_item_count) - 1;
            $start_date = strtotime($sch_item_count[0]->schedule_payment_date);
            $schedule_payment_start_date = date("m/d/Y", strtotime("+1 month", $start_date));
        }
        $state_view = "readonly";
    }else{
        $quantity = ""; 
        $schedule_payment_start_date = "";
        $state_view = "";
    }
    $schedule_not_stu_names = rtrim($student_names, ", ");
    $schedule_not_stu_ids = rtrim($student_ids_not_exist, ",");
    $student_all = rtrim($total_stu_family, ", ");

    $html = '<hr style="margin-top:0px;margin-bottom:0px;">
               <br>
                <div class="row">
                    <div class="col-md-12">
                    <label>Payment of : </label><span> '.$schedule_not_stu_names.' </span>
                    </div>
                  </div>
                  <br>
                 
                 <div class="row">
                    <div class="col-md-12">
                         <label class="radio-inline">
                          <input type="radio" name="sch_type" class="sch_type" value="monthly" checked>Monthly
                         </label>
                         <label class="radio-inline">
                          <input type="radio" name="sch_type" class="sch_type" value="yearly">Yearly
                         </label>
                     </div>
                 </div>
                <br>

                <div class="row">
                    <div class="col-md-12">';
                            $html .= '<label for="group_name">Date</label>';
                                if($state_view == 'readonly'){
                                    $html .= '<input required name="schedule_start_date" class="form-control required" '.$state_view.' type="text" value="'.$schedule_payment_start_date.'">';
                                }else{
                                    $html .= '<input required name="schedule_start_date" id="schedule_start_date" class="form-control required" '.$state_view.' type="text" value="'.$schedule_payment_start_date.'">';
                                }
                            $html .= '<i class="fas fa-calendar input-prefix" tabindex=0></i>
                    </div>
                </div>
                <br>

                <div class="row qulitydiv" style="display:block;">
                    <div class="col-md-12">
                    <label for="group_name">Quantity</label>
                    <input name="quantity" id="quantity" minlength="1" maxlength="2" Digits="true" class="form-control quantityval required" '.$state_view.' value="'.$quantity.'" type="text">
                    <input type="hidden" name="stu_ids" value="' . $schedule_not_stu_ids . '">
                    </div>
                </div>';

    if(!empty($schedule_not_stu_names)){
        echo json_encode(array('code' => "1", 'msg' => $html, 'schedule_payment_start_date' => "$schedule_payment_start_date" ));
        exit;
   }else{
        echo json_encode(array('code' => "0", 'msg' => " Child(ren) : ".$student_all." <br> Status : Payment Already Scheduled"));
        exit;
   }









} elseif ($_POST['action'] == 'start_schedule') {
    $family_id = $_POST['familyid'];
    $students = explode(",", $_POST['stu_ids']);

    if(isset($_POST['schedule_start_date_submit'])){
        $schedule_start_date = Date('Y-m-d', strtotime(trim($_POST['schedule_start_date_submit'])));
    }else{
        $schedule_start_date = Date('Y-m-d', strtotime(trim($_POST['schedule_start_date'])));
    }



//Monthly
if(strtolower($_POST['sch_type']) == 'monthly'){


    $recurring_month_count = trim($_POST['quantity']);


    $check_count = 0;
    foreach ($students as $user_id) {
        $db->query('BEGIN');

        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id 
        where g.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' and m.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' 
        and m.student_user_id='".$user_id."' and m.latest = 1 ");
        $groups = [];
        
        foreach($user_groups as $group){
            $groups[] = $group->id;
        }
        
        $group_ids = implode(",", $groups);

        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (".$group_ids.") AND status = 1 
        AND session = '".$_SESSION['school_CURRENT_SESSION']."' ");


        $discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id 
        where sf.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' and d.session = '" . $_SESSION['school_CURRENT_SESSION'] . "' 
        and sf.student_user_id = '".$user_id."' AND sf.status = 1  and d.status=1");

       
        $discountPercentTotal = $basicFees->fee_amount;
        $discountDollarTotal = 0;
        
        foreach ($discountFeesData as $val) { 
            if ($val->discount_unit == 'd') {
                $discountDollarTotal+= $val->discount_percent;
            }elseif ($val->discount_unit == 'p') {
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
        $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' 
        AND schedule_status = 0 AND session = '".$_SESSION['school_CURRENT_SESSION']."' ");

        $student_data = $db->get_row("SELECT s.admission_no, s.user_id, s.school_grade, f.id as family_id, f.forte_customer_token,  
        f.father_phone, f.father_area_code, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_city, 
        f.billing_post_code, pay.credit_card_type, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv, pay.forte_payment_token, 
        pay.id as payment_credential_id FROM ss_user u 
        INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
        INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id 
        where s.user_id='" . $user_id . "' AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "' 
        AND pay.default_credit_card = 1 AND pay.credit_card_deleted = 0 ");

        // echo "<pre>";
        // print_r($student_data);
        // die;

        if (isset($student_data->credit_card_type) && isset($student_data->credit_card_no) && isset($student_data->credit_card_exp) ) {

            $credit_card_exp = base64_decode($student_data->credit_card_exp);
            $credit_card_expAry = explode('-', $credit_card_exp);
            $CardType = base64_decode($student_data->credit_card_type);;
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

            if (!empty($student_data->forte_customer_token)) {
                $forte_customer_token = $student_data->forte_customer_token;
            } else {
                $forte_customer_token = "";
            }

            $payment_gateway_id = $db->get_var("select id from ss_payment_gateways where status = 'active' ");

            if (is_numeric($user_id)) {
                $forteParamsSend = array('coustomer_token' => $forte_customer_token, 'paymentAction' => 'Sale', 'itemName' => 'Fees', 
                'itemNumber' => '10001', 'amount' => $userDataAmount, 'currencyCode' => 'USD', 'creditCardType' => $CardType, 
                'creditCardNumber' => $CardNumber, 'expMonth' => $CardExpiryMonth, 'expYear' => $CardExpiryYear, 'cvv' => $CardCVV, 
                'firstName' => $cardHolderFirstName, 'lastName' => $cardHolderLastName, 'email' => $userDataEmail, 'phone' => $userDataPhoneNo, 
                'city' => $userDataCity, 'zip' => $userDataZip, 'countryCode' => 'US', 'recurring' => 'Yes');
                $forteParams = json_encode($forteParamsSend);

                if (!empty($student_data->forte_customer_token)) {

             
                    if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
                        $customertoken = $student_data->forte_customer_token;
                        $paymethodtoken = $student_data->forte_payment_token;
                    } else {
                        $customerPostRequest = $fortePayment->GenratePaymentToken($forteParams);

                        if (!empty($student_data->forte_customer_token) && isset($customerPostRequest->paymethod_token)) {
                            $customertoken = $student_data->forte_customer_token;
                            $paymethodtoken = $customerPostRequest->paymethod_token;
                            $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                            $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', 
                            updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                        } else {
                            $customertoken = "";
                            $paymethodtoken = "";
                        }
                    }
                } else {

                    $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
                    if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
                        $customertoken = $customerPostRequest->customer_token;
                        $paymethodtoken = $customerPostRequest->default_paymethod_token;
                        $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                        $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', 
                        updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                       
                    } else {
                        $customertoken = "";
                        $paymethodtoken = "";
                    }
                }                
                
                if (!empty(trim($customerPostRequest->response->response_desc))) {
                    $msgError = str_replace("Error[", "<br>Error[", trim($customerPostRequest->response->response_desc));
                    $response_msg_error = ltrim($msgError, "<br>");
                    $responsemsg = $response_msg_error;
                } else {
                    $responsemsg = "Payment processing failed. Please retry";
                }

                if (!empty($customertoken) && !empty($paymethodtoken)) {


                        $stu_items_data = $db->get_results("SELECT sfi.*, s.family_id FROM `ss_student_fees_items` sfi 
                                        INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
                                        INNER JOIN ss_user u ON u.id = s.user_id 
                                        AND schedule_status = 0 AND sfi.`student_user_id` = '".$user_id."'
                                        AND sfi.session = '".$_SESSION['school_CURRENT_SESSION']."'
                                        AND u.is_active=1 AND u.is_deleted=0");


                        if(count((array)$stu_items_data) == 0){


                                for ($i = 0;$i <= ($recurring_month_count - 1); $i++) {
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
                                $res = $db->query("insert into ss_student_fees_items set student_user_id='" . $user_id . "',  
                                original_schedule_payment_date= '" . $next_schedule_start_dates. "', 
                                schedule_payment_date = '" . $next_schedule_start_dates . "', amount='" . $userDataAmount . "', 
                                schedule_status = 0, session = '".$_SESSION['school_CURRENT_SESSION']."', created_at = '" . date('Y-m-d H:i') . "'");
                                }



                        }elseif(count((array)$stu_items_data) > 0 && $recurring_month_count >= count((array)$stu_items_data)){


                            foreach ($stu_items_data as $i => $items_data) {

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

                               $res = $db->query("update ss_student_fees_items set schedule_status = 0, 
                               schedule_payment_date = '" . $next_schedule_start_dates . "',  updated_at = '" . date('Y-m-d H:i') . "' 
                               where id = '" . $items_data->id . "' ");
                               $i++;
                            }
                            

                             $remain_item_count = $recurring_month_count - count((array)$stu_items_data);
                            if($remain_item_count > 0){

                            for ($i = 1;$i <= $remain_item_count; $i++) {
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
                                $res = $db->query("insert into ss_student_fees_items set student_user_id='" . $user_id . "',  
                                original_schedule_payment_date= '" . $next_schedule_start_datess. "', 
                                schedule_payment_date = '" . $next_schedule_start_datess . "', amount='" . $userDataAmount . "', 
                                schedule_status = 0, session = '".$_SESSION['school_CURRENT_SESSION']."', created_at = '" . date('Y-m-d H:i') . "'");
                            }

                        }

                        }else{

                            if(count((array)$stu_items_data) > 0){

                                $remain_item_count = count((array)$stu_items_data) - $recurring_month_count;

                                $db->query("delete from ss_student_fees_items where student_user_id='" . $user_id . "' 
                                AND session = '".$_SESSION['school_CURRENT_SESSION']."', AND  (schedule_status = 0 
                                OR  schedule_status = 2 OR schedule_status = 3) ORDER BY id DESC limit ".$remain_item_count." ");
                                
                                $stu_items_data_reamin = $db->get_results("SELECT sfi.*, s.family_id FROM `ss_student_fees_items` sfi 
                                INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
                                INNER JOIN ss_user u ON u.id = s.user_id 
                                AND schedule_status = 0 AND sfi.`student_user_id` = '".$user_id."' 
                                AND sfi.session = '".$_SESSION['school_CURRENT_SESSION']."'
                                AND u.is_active=1 AND u.is_deleted=0");

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

                                       $res = $db->query("update ss_student_fees_items set schedule_status = 0, 
                                       schedule_payment_date = '" . $next_schedule_start_dates . "',  updated_at = '" . date('Y-m-d H:i') . "' 
                                       where id = '" . $items_data->id . "' ");
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



//Yearly
}else{


    $sch_sessions = $db->get_row("select *  from ss_school_sessions s where s.current = 1 ");

    if(!empty($sch_sessions)){

    $date1 = $sch_sessions->start_date;
    $date2 = $sch_sessions->end_date;

    $ts1 = strtotime($date1);
    $ts2 = strtotime($date2);

    $year1 = date('Y', $ts1);
    $year2 = date('Y', $ts2);

    $month1 = date('m', $ts1);
    $month2 = date('m', $ts2);

    $school_session_month = (($year2 - $year1) * 12) + ($month2 - $month1);

    }else{

    $school_session_month = null;

    }


    $finalAllStudentFeeAmount = 0;
    $check_count = 0;
    foreach ($students as $user_id) {
        $db->query('BEGIN');

        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id 
        where m.student_user_id='".$user_id."' and m.latest = 1");
        $groups = [];
        
        foreach($user_groups as $group){
            $groups[] = $group->id;
        }
        
        $group_ids = implode(",", $groups);
        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (".$group_ids.") AND status = 1 
        AND session = '".$_SESSION['school_CURRENT_SESSION']."' ");

        $discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id 
        where sf.student_user_id = '".$user_id."' AND sf.status = 1  and d.status=1");
       
        $discountPercentTotal = $basicFees->fee_amount;
        
        $discountDollarTotal = 0;
        
        foreach ($discountFeesData as $val) { 
            if ($val->discount_unit == 'd') {
                $discountDollarTotal+= $val->discount_percent;
            }elseif ($val->discount_unit == 'p') {
                $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                $discountPercentTotal = $discountPercentTotal - $fee_percent;
            }
         }
     
         $final_amount = ($discountPercentTotal - $discountDollarTotal);

         if ($final_amount > 0) {

            if(!empty($sch_sessions) && !empty($sch_sessions->fees_full_payment_discount_unit) && !empty($sch_sessions->fees_full_payment_discount_value)){
             
                   if(strtolower($sch_sessions->fees_full_payment_discount_unit) == 'p'){

                     $pre_final_amount   = $final_amount * $school_session_month; 
                     $discountFees = ($pre_final_amount * $sch_sessions->fees_full_payment_discount_value) / 100;
                     $actualbasicDiscountFees = $pre_final_amount -  $discountFees;

                   }else{

                     $pre_final_amount = $final_amount * $school_session_month;
                     $actualbasicDiscountFees = $pre_final_amount - $sch_sessions->fees_full_payment_discount_value;

                   }


            } else {
                 $actualbasicDiscountFees = $final_amount;
            }


         } else {
             $actualbasicDiscountFees = 0;
         }

        $finalAllStudentFeeAmount+= $actualbasicDiscountFees;
         
        $userDataAmount = $actualbasicDiscountFees;


        $student_data = $db->get_row("SELECT s.admission_no, s.user_id, s.school_grade, f.id as family_id, f.forte_customer_token,  
        f.father_phone, f.father_area_code, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_city, 
        f.billing_post_code, pay.credit_card_type, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv, pay.forte_payment_token, 
        pay.id as payment_credential_id FROM ss_user u 
            INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
            INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id 
            where s.user_id='" . $user_id . "' AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['school_CURRENT_SESSION'] . "' 
            AND pay.default_credit_card = 1 AND pay.credit_card_deleted = 0 ");



        if (!empty($student_data) && isset($student_data->credit_card_type) && isset($student_data->credit_card_no) && isset($student_data->credit_card_exp) ) {

            $credit_card_exp = base64_decode($student_data->credit_card_exp);
            $credit_card_expAry = explode('/', $credit_card_exp);
            $CardType = base64_decode($student_data->credit_card_type);;
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

            if (!empty($student_data->forte_customer_token)) {
                $forte_customer_token = $student_data->forte_customer_token;
            } else {
                $forte_customer_token = "";
            }

            $payment_gateway_id = $db->get_var("select id from ss_payment_gateways where status = 'active' ");

        if (is_numeric($user_id)) {

                $forteParamsSend = array('coustomer_token' => $forte_customer_token, 'paymentAction' => 'Sale', 'itemName' => 'Fees', 
                'itemNumber' => '10001', 'amount' => $userDataAmount, 'currencyCode' => 'USD', 'creditCardType' => $CardType, 
                'creditCardNumber' => $CardNumber, 'expMonth' => $CardExpiryMonth, 'expYear' => $CardExpiryYear, 'cvv' => $CardCVV, 
                'firstName' => $cardHolderFirstName, 'lastName' => $cardHolderLastName, 'email' => $userDataEmail, 
                'phone' => $userDataPhoneNo, 'city' => $userDataCity, 'zip' => $userDataZip, 'countryCode' => 'US', 'recurring' => 'Yes');
                $forteParams = json_encode($forteParamsSend);


                if (!empty($student_data->forte_customer_token)) {

                    if (!empty($student_data->forte_customer_token) && !empty($student_data->forte_payment_token)) {
                        $customertoken = $student_data->forte_customer_token;
                        $paymethodtoken = $student_data->forte_payment_token;
                    } else {
                        $customerPostRequest = $fortePayment->GenratePaymentToken($forteParams);

                        if (!empty($student_data->forte_customer_token) && isset($customerPostRequest->paymethod_token)) {
                            $customertoken = $student_data->forte_customer_token;
                            $paymethodtoken = $customerPostRequest->paymethod_token;
                            $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                            $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', 
                            updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                        } else {
                            $customertoken = "";
                            $paymethodtoken = "";
                        }
                    }

                }else {

                    $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
                    if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
                        $customertoken = $customerPostRequest->customer_token;
                        $paymethodtoken = $customerPostRequest->default_paymethod_token;
                        $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                        $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', 
                        updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                       
                    } else {
                        $customertoken = "";
                        $paymethodtoken = "";
                    }
                }      

                if (!empty(trim($customerPostRequest->response->response_desc))) {
                    $msgError = str_replace("Error[", "<br>Error[", trim($customerPostRequest->response->response_desc));
                    $response_msg_error = ltrim($msgError, "<br>");
                    $responsemsg = $response_msg_error;
                } else {
                    $responsemsg = "Payment processing failed. Please retry";
                }

                if (!empty($customertoken) && !empty($paymethodtoken)) {


                        $stu_items_data = $db->get_results("SELECT sfi.*, s.family_id FROM `ss_student_fees_items` sfi 
                                        INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
                                        INNER JOIN ss_user u ON u.id = s.user_id 
                                        AND schedule_status = 0 AND sfi.`student_user_id` = '".$user_id."'
                                        AND sfi.session = '".$_SESSION['school_CURRENT_SESSION']."'
                                        AND u.is_active=1 AND u.is_deleted=0");


                        if(count((array)$stu_items_data) == 0){


                            $res = $db->query("insert into ss_student_fees_items set student_user_id='" . $user_id . "',  
                            original_schedule_payment_date= '" . $schedule_start_date. "', schedule_payment_date = '" . $schedule_start_date . "', 
                            amount='" . $userDataAmount . "', full_payment = 1, schedule_status = 0, 
                            session = '".$_SESSION['school_CURRENT_SESSION']."', created_at = '" . date('Y-m-d H:i') . "'");

                        }elseif(count((array)$stu_items_data) > 0 ){

                            foreach ($stu_items_data as $items_data) {

                                 $res = $db->query("update ss_student_fees_items set schedule_status = 0, 
                                 schedule_payment_date = '" . $schedule_start_date . "',  updated_at = '" . date('Y-m-d H:i') . "' 
                                 where id = '" . $items_data->id . "' ");
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


    if(count((array)$students) == $check_count){
         $db->query('COMMIT');
        echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> Schedule successfully </p>'));
        exit;
    }else{
         $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed', '_errpos' => '-9'));
        exit;
    }

    
}






elseif($_POST['action'] == 'get_reg_pay_history'){

    $get_student = $db->get_results("SELECT schreg.father_first_name, schreg.father_last_name, schreg.father_phone, schreg.father_email, schreg.city, schreg.amount_received, txn.payment_unique_id, txn.comments, txn.payment_status, txn.payment_date, reg.first_name,  reg.last_name, reg.gender, reg.dob, reg.school_grade FROM ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_sunday_sch_req_child reg ON reg.user_id = s.user_id INNER JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.sunday_school_reg_id INNER JOIN ss_sunday_school_reg schreg ON schreg.id = reg.sunday_school_reg_id WHERE  f.id='".$_POST['familyid']."' and ssm.session_id = '".$_SESSION['school_CURRENT_SESSION']."' and u.is_deleted = 0 ");
    
    $html_body = "";
    
    if(count((array)$get_student) > 0){
    
    $html_body.= '<legend class="text-semibold">Parent Information </legend>';
    $html_body .= '<div class="row">';
    
    $html_body .= '<div class="col-md-6"> <label>Parent Name:</label> ' . $get_student[0]->father_first_name . ' ' . $row->father_last_name . ' </div>';
    $html_body .= '<div class="col-md-6"> <label>Parent Email:</label> ' . $get_student[0]->father_email . ' </div>';
    
    $html_body .= '<div class="col-md-6"> <label>Parent Phone:</label> ' . $get_student[0]->father_phone . ' </div>';
    $html_body .= '<div class="col-md-6"> <label>City:</label> ' . $get_student[0]->city . ' </div>';
    
    
    $child_name = "";
    
    $html_body_table = '<legend class="text-semibold">Transaction Information </legend>
                        <table class="table table-bordered">
                        <thead>
                        <tr>
                        <th>Payment Date</th>
                        <th>Payment Amount</th>
                        <th>Payment Txn Id</th>
                        <th>Payment Status</th>
                        <th>Payment Comments</th>
                        </tr>
                        </thead>
                        <tbody>';
    
    foreach ($get_student as $key => $row) {
    
    $child_name .= $row->first_name.',';
    
    if($row->payment_status == 1){
        $payment_status = 'Success';
    }else{
        $payment_status = 'Failed';
    }
    
    $html_body_table .= '<tr>
                            <td>' . date('m/d/Y',strtotime($row->payment_date)) . '</td>
                            <td>' . '$'.$row->amount_received . '</td>
                            <td>' . $row->payment_unique_id . ' </td>
                            <td> ' . $payment_status . '</td>
                            <td>' . $row->comments . '</td>
                          </tr>';
    
    }
    
    $html_body_table .= '</tbody></table>';
    
    $html_body .= '<div class="col-md-6"> <label>Child (ren) :</label> ' . rtrim($child_name,',')  . ' </div>';
    $html_body .= '</div>';
    
    $html_body .= $html_body_table;
    
    }else{
    
    $html_body .= '<h5>Data not found..</h5>';
    } 
    
    echo $html_body;
    
    
    
    }



?>