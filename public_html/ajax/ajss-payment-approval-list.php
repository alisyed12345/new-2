<?php
include_once "../includes/config.php";

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}

//==========================LIST ALL SCHEDULE FOR ADMIN=====================
if ($_POST['action'] == 'list_approved_payments') { 
    $finalAry = array();

    $all_student_fees_items = $db->get_results("SELECT sfi.id, sfi.sch_item_ids, sfi.schedule_payment_date,sfi.total_amount as amount,
        sfi.schedule_status, sfi.family_id, sfi.is_approval,sfi.schedule_unique_id,sfi.payment_unique_id,  sfi.payment_response_code, sfi.payment_response, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, sfi.updated_at, sfi.session, 
        f.secondary_email, f.billing_city, f.billing_post_code, f.forte_customer_token, f.user_id AS family_user_id, pay.credit_card_no, 
        pay.forte_payment_token, pay.id AS payment_credential_id
        FROM `ss_payment_sch_item_cron` sfi 
        INNER JOIN ss_family f ON f.`id` = sfi.`family_id` 
        INNER JOIN ss_paymentcredentials pay ON pay.family_id = f.`id`
        where sfi.schedule_status = 0 
        AND pay.default_credit_card = 1 AND sfi.session= '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'", ARRAY_A);


    for ($i = 0; $i < count((array)$all_student_fees_items); $i++) {

        $child_name_data = $db->get_results("select GROUP_CONCAT(s.first_name) AS child_name from ss_student_fees_items sfi 
        INNER JOIN ss_student s ON s.user_id = sfi.student_user_id 
        where sfi.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND FIND_IN_SET(sfi.`id`, '" . $all_student_fees_items[$i]['sch_item_ids'] . "')");
        $child_name = $child_name_data[0]->child_name;

        if ($all_student_fees_items[$i]['schedule_status'] == 0) {
            $schedule_status = "Pending";
        } elseif ($all_student_fees_items[$i]['schedule_status'] == 1) {
            $schedule_status = "Success";
        } else {
            $schedule_status = "";
        }

        $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($all_student_fees_items[$i]['credit_card_no'])), -4);
        $all_student_fees_items[$i]['schedule_unique_id'] = $all_student_fees_items[$i]['schedule_unique_id'];
        $all_student_fees_items[$i]['schedule_payment_date'] =my_date_changer($all_student_fees_items[$i]['schedule_payment_date']);
        $all_student_fees_items[$i]['amount'] = $all_student_fees_items[$i]['amount'];
        $all_student_fees_items[$i]['parent_name'] = $all_student_fees_items[$i]['father_first_name'] . ' ' . $all_student_fees_items[$i]['father_last_name'];
        $all_student_fees_items[$i]['primary_email'] = $all_student_fees_items[$i]['primary_email'];
        $all_student_fees_items[$i]['father_phone'] = $all_student_fees_items[$i]['father_phone'];
        $all_student_fees_items[$i]['schedule_status'] = $schedule_status;
        $all_student_fees_items[$i]['credit_card_no'] = $credit_card_no;
        $all_student_fees_items[$i]['child_name'] = $child_name;
    }

    $finalAry['data'] = $all_student_fees_items;
    echo json_encode($finalAry);
    exit;
}
//////////////////////////////////////////////// payment_disapproved /////////////////////////
if ($_POST['action'] == 'payment_disapproved') {

    $db->query('BEGIN');
    try {

        if (!empty($_POST['id']) && !empty($_POST['itemids']) && !empty($_POST['reason']) && !empty($_POST['schedule_unique_id'])) {


    $student_fees_items = $db->get_results("SELECT sfic.id,sfic.schedule_unique_id,sch_item_ids, sfic.schedule_payment_date,sfic.total_amount,sfic.cc_amount,sfic.wallet_amount,sfic.payment_unique_id,sfic.payment_response_code,sfic.payment_response,sfic.updated_at,sfic.reason, sfic.schedule_status, sfic.family_id, f.user_id AS family_user_id, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.secondary_email, f.billing_city, f.billing_post_code, f.forte_customer_token,pay.credit_card_no, pay.forte_payment_token, pay.id AS payment_credential_id,sfic.session ,sfic.created_at
    FROM `ss_payment_sch_item_cron` sfic 
    INNER JOIN ss_family f ON f.id = sfic.family_id 
    INNER JOIN ss_paymentcredentials pay ON pay.family_id = f.id
    WHERE sfic.schedule_status = 0 AND sfic.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND pay.default_credit_card =1 AND is_approval='1';");
 
    foreach ($student_fees_items as $family_data) {

        if(isset($family_data->total_amount) && !empty($family_data->total_amount)){
            $total_amount = "total_amount ='".$family_data->total_amount ."',";
        }
        if(isset($family_data->retry_count) && !empty($family_data->retry_count)){
            $retry_count = "retry_count = '".$family_data->retry_count."',";
        }
        if(isset($family_data->is_approval) && !empty($family_data->is_approval)){
            $is_approval = "is_approval  = '".$family_data->is_approval."',";
        }
        if(isset($family_data->payment_unique_id) && !empty($family_data->payment_unique_id)){
            $payment_unique_id = "payment_unique_id = '".$family_data->payment_unique_id."',";
        }
        if(isset($family_data->payment_response_code) && !empty($family_data->payment_response_code)){
            $payment_response_code = "payment_response_code= '".$family_data->payment_response_code."',";
        }
        if(isset($family_data->payment_response) && !empty($family_data->payment_response)){
            $payment_response = "payment_response= '".$family_data->payment_response."',";
        }
        if(isset($family_data->reason) && !empty($family_data->reason)){
            $reason = "reason= '".$family_data->reason."',";
        }
 
        $db->query("insert into  ss_payment_sch_item_cron_backup  set schedule_unique_id ='".$family_data->schedule_unique_id ."',  	family_id ='".$family_data->family_id."', sch_item_ids='".$family_data->sch_item_ids."', schedule_payment_date='".$family_data->schedule_payment_date."', $total_amount wallet_amount = '".$family_data->wallet_amount."', cc_amount = '".$family_data->cc_amount."', schedule_status  = '".$family_data->schedule_status ."', $retry_count session  = '".$family_data->session ."', $is_approval $reason $payment_unique_id $payment_response_code $payment_response  is_cancel=1, created_at='".date('Y-m-d h:i:s',strtotime($family_data->created_at))."', updated_at='".date('Y-m-d h:i:s',strtotime($family_data->updated_at))."'");
        $query_backup_id = $db->insert_id;

    }

            $payment_sch_item_cron = $db->query("update ss_payment_sch_item_cron set is_approval = 0, reason = '" . trim($db->escape($_POST['reason'])) . "', updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $_POST['id'] . "' ");


            // $db->query("INSERT INTO  ss_payment_sch_item_cron_backup SELECT * FROM  ss_payment_sch_item_cron WHERE id = '" . $_POST['id'] . "' ");
            // $query_backup_id = $db->insert_id;

            $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 2, updated_at = '" . date('Y-m-d H:i:s') . "' where FIND_IN_SET(`id`, '" . $_POST['itemids'] . "') ");
            $get_parent_email = $db->get_row("SELECT fam.*,cron.schedule_payment_date,cron.total_amount,cron.reason,GROUP_CONCAT( DISTINCT stu.first_name ) AS students FROM ss_payment_sch_item_cron cron INNER JOIN ss_family fam ON fam.id = cron.family_id INNER JOIN ss_student stu ON stu.family_id=cron.family_id where cron.id = '" . $_POST['id'] . "' GROUP BY stu.family_id "); 

            $delete_invoice = $db->query("delete from ss_invoice where schedule_unique_id ='" . $_POST['schedule_unique_id'] . "' ");
            $delete_invoice = $db->insert_id;

            $delete_payment_sch_item_cron = $db->query("delete from ss_payment_sch_item_cron where id ='" . $_POST['id'] . "' ");

            if ($payment_sch_item_cron && $student_fees_items && $query_backup_id > 0 && $delete_payment_sch_item_cron && $delete_invoice) {
              
                
                    //Send Email 
                   
                    $emailbody_support .= "Assalamu-alaikum " . $get_parent_email->father_first_name . ' ' . $get_parent_email->father_last_name . ",<br>";
                    

                    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                    <tr>
                    <td colspan="2" style="text-align: center;">
                    <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Payment Schedule Information </u></div>
                    </td>
                    </tr>   
                    <tr>
                    <td colspan="2" style="text-align: left; padding-top:10px">
                    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">

                    <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                    <td style="width: 75%; text-align:left;">' . $get_parent_email->students . '
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2">Payment Schedule Date:</td>
                    <td style="width: 75%; text-align:left;">' . my_date_changer($get_parent_email->schedule_payment_date) . '
                    </td></tr>
                   
                    <tr><td style="width: 25%;" class="color2">Amount:</td> 
                    <td style="width: 75%; text-align:left;">' . $get_parent_email->total_amount . '</td></tr>

                    <tr><td style="width: 25%;" class="color2"> Payment Schedule Old Status:</td>
                    <td style="width: 75%; text-align:left;"> Pending
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2"> Payment Schedule New Status:</td>
                    <td style="width: 75%; text-align:left;"> Cancel
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2"> Reason:</td>
                    <td style="width: 75%; text-align:left;"> '.$_POST['reason'].'
                    </td></tr>


                    </table>
                    </td>
                    </tr>         
                    </table>';


                    $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='mailto:" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for the transaction.";

                    $mailservice_request_from = MAIL_SERVICE_KEY;
                    $mail_service_array = array(
                    'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Payment Schedule Information',
                    'message' => $emailbody_support,
                    'request_from' => $mailservice_request_from,
                    'attachment_file_name' => '',
                    'attachment_file' => '',
                    'to_email' => [$get_parent_email->primary_email],
                    'cc_email' => '',
                    'bcc_email' => ''
                    );

                    mailservice($mail_service_array);

                    // 
                    
                $db->query('COMMIT');
                $return_resp = array('code' => "1", 'msg' => 'Payment Cancel Successfully.');
                echo json_encode($return_resp);
                exit;
            } else {
                $db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => 'Payment Cancel Request Failed.');
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            $return_resp = array('code' => "0", 'msg' => 'Required Parameter Missing.');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    } catch (Exception $e) {
        $db->query('ROLLBACK');
        $msg = $e->getMessage();
        CreateLog($_REQUEST, json_encode($msg));
        echo json_encode($msg);
        exit;
    }
}
