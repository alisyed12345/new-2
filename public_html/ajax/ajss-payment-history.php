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
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}

//==========================LIST ALL SCHEDULE FOR ADMIN=====================
if ($_POST['action'] == 'list_history_payments') {
    if(!empty(get_country()->currency)){
        $currency = get_country()->currency;
    }else{
        $currency = '';
    }
    $finalAry = array();
    $user_id = trim($_POST['user_id']);


    if (trim($_POST['show_cancel_sch']) == 2) {
        $all_student_fees_items = $db->get_results("SELECT sfi.id AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification 
                        FROM ss_student_fees_items sfi
                        INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                        INNER JOIN ss_user u ON u.id = s.user_id
                        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                        INNER JOIN ss_family f ON f.id = s.family_id
                        INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                        WHERE s.family_id = '" . $user_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 AND u.is_locked=0 AND pay.default_credit_card =1 AND sfi.schedule_status = 2 
                        GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY  sfi.original_schedule_payment_date ASC", ARRAY_A);
    } else {
        // $all_student_fees_items = $db->get_results("SELECT sfi.id AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
        //                 INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
        //                 INNER JOIN ss_user u ON u.id = s.user_id
        //                 INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
        //                 INNER JOIN ss_family f ON f.id = s.family_id
        //                 INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
        //                 WHERE s.family_id = '" . $user_id . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0  AND u.is_locked=0  AND pay.default_credit_card =1 AND sfi.schedule_status != 2 
        //                 GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.id desc", ARRAY_A);

        ///  ( uper wale code k baad comment kiya hai Date = 21/dec/2022 )

    //     $all_student_fees_items = $db->get_results("SELECT sfi.id AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification 
    //     FROM ss_student_fees_items sfi 
    //     INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
    //     INNER JOIN ss_user u ON u.id = s.user_id 
    //     INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    //     INNER JOIN ss_family f ON f.id = s.family_id 
    //     INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id 
    //     WHERE s.family_id = '" . $user_id . "' 
    //     AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0  AND u.is_locked=0 
    //     AND pay.default_credit_card =1 AND sfi.schedule_status <> 1 and sfi.schedule_status <> 4 
    //     GROUP BY sfi.schedule_unique_id,sfi.schedule_status
    //     UNION
    //     SELECT sfi.id AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification 
    //     FROM ss_student_fees_items sfi 
    //     INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
    //     INNER JOIN ss_user u ON u.id = s.user_id 
    //     INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    //     INNER JOIN ss_family f ON f.id = s.family_id 
    //     INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id 
    //     WHERE s.family_id = '" . $user_id . "' 
    //     AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  AND u.is_locked=0 
    //     AND pay.default_credit_card =1 AND sfi.schedule_status <> 0 and sfi.schedule_status <> 3 and sfi.schedule_status <> 2
    //     GROUP BY sfi.schedule_unique_id,sfi.schedule_status", ARRAY_A);
    // }

    $all_student_fees_items = $db->get_results("SELECT sfi.id AS sch_item_id, GROUP_CONCAT(s.first_name) as child_names, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification 
    FROM ss_student_fees_items sfi 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
    INNER JOIN ss_user u ON u.id = s.user_id 
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    INNER JOIN ss_family f ON f.id = s.family_id 
    INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id 
    WHERE s.family_id = '" . $user_id . "' 
    AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0  AND u.is_locked=0 
    AND pay.default_credit_card =1 AND sfi.schedule_status <> 2
    GROUP BY sfi.schedule_unique_id,sfi.schedule_status", ARRAY_A);
}
    $check_family_exec_mode = $db->get_row("SELECT * from ss_payment_sch_item_cron where family_id='" . $user_id . "' and schedule_status <> 0 ");

    if(!empty($check_family_exec_mode)){
        $exec=1;
    }else{
        $exec=0;
    }

    $note='<p style="color:red;"><b>Note</b> : A payment reminder has already been sent to the parents. Do you still want to send this reminder and cancel the previous ones?</p>
    <input type="checkbox" class="agree_check" name="agree_check"> <b>I Agree </b><br><label id="agree_check-error" class="error" for="agree_check" style="display:none;">Required field</label>';

    for ($i = 0; $i < count((array)$all_student_fees_items); $i++) {

        // $trxn_child_names = $db->get_results("SELECT s.first_name FROM ss_student_fees_items sfi
        // INNER JOIN ss_user u ON u.id = sfi.student_user_id
        // INNER JOIN ss_student s ON sfi.student_user_id = s.user_id  
        // INNER JOIN ss_family f ON f.id = s.family_id 
        // WHERE  u.is_deleted = 0  AND u.is_locked=0 AND u.is_active=1 AND sfi.schedule_payment_date = '" . $all_student_fees_items[$i]['schedule_payment_date'] . "' AND s.family_id = '" . $user_id . "' GROUP BY s.user_id");

        // $child_name = "";
        // foreach ($trxn_child_names as $row) {
        //     $child_name .= $row->first_name . ", ";
        // }

        $payment_trxn = $db->get_row("SELECT payment_txns_id,payment_date, pay.credit_card_no,payment_unique_id FROM ss_student_fees_transactions 
                                INNER JOIN ss_payment_txns ON ss_payment_txns.id = ss_student_fees_transactions.payment_txns_id 
                                INNER JOIN ss_paymentcredentials pay ON pay.id = ss_payment_txns.payment_credentials_id
                                WHERE student_fees_item_id = '" . $all_student_fees_items[$i]['sch_item_id'] . "' 
                                ORDER BY ss_student_fees_transactions.id DESC LIMIT 1");

        $payment_virtual_trxn = $db->get_row("SELECT pae.id as account_entries_id,pae.created_on,CONCAT('wtx_',md5(pae.id)) as vtxn FROM ss_student_fees_virtual_transactions sfvt 
                                INNER JOIN ss_payment_account_entries pae ON pae.id = sfvt.payment_account_entries_id 
                                WHERE sfvt.student_fees_item_id = '" . $all_student_fees_items[$i]['sch_item_id'] . "' 
                                ORDER BY sfvt.id DESC LIMIT 1");

        $star = '************';
        $all_student_fees_items[$i]['parent_name'] =  $all_student_fees_items[$i]['father_first_name'] . ' ' . $all_student_fees_items[$i]['father_last_name'];

        if ($all_student_fees_items[$i]['original_schedule_payment_date'] == $all_student_fees_items[$i]['schedule_payment_date']) {
            $all_student_fees_items[$i]['schedule_payment_date'] =  my_date_changer($all_student_fees_items[$i]['schedule_payment_date']);
        } else {
            // $all_student_fees_items[$i]['schedule_payment_date'] =  date('m/d/Y', strtotime($all_student_fees_items[$i]['schedule_payment_date'])). " <b>( Prev. ".date('m/d/Y', strtotime($all_student_fees_items[$i]['original_schedule_payment_date']))."  )</b>";
            $all_student_fees_items[$i]['schedule_payment_date'] =  my_date_changer($all_student_fees_items[$i]['schedule_payment_date']);
        }

        $all_student_fees_items[$i]['sch_payment_date'] = my_date_changer($all_student_fees_items[$i]['original_schedule_payment_date']);


        if ($all_student_fees_items[$i]['schedule_status'] == 1) {
            $all_student_fees_items[$i]['payment_trxn_status'] = 'Success';
        } elseif ($all_student_fees_items[$i]['schedule_status'] == 2) {
            $all_student_fees_items[$i]['payment_trxn_status'] = 'Cancel';
        } elseif ($all_student_fees_items[$i]['schedule_status'] == 3) {
            $all_student_fees_items[$i]['payment_trxn_status'] = 'Hold';
        } elseif ($all_student_fees_items[$i]['schedule_status'] == 4) {
            $all_student_fees_items[$i]['payment_trxn_status'] = 'Decline';
        } elseif ($all_student_fees_items[$i]['schedule_status'] == 5) {
            $all_student_fees_items[$i]['payment_trxn_status'] = 'Skipped';
        } elseif ($all_student_fees_items[$i]['schedule_status'] == 0) {
            $all_student_fees_items[$i]['payment_trxn_status'] = 'Pending';
            $all_student_fees_items[$i]['payment_exec_status'] = $exec;
                if($all_student_fees_items[$i]['schedule_notification']==1){
                    $all_student_fees_items[$i]['reminder_note'] = $note;
                }
            
        } else {
            $all_student_fees_items[$i]['payment_trxn_status'] = '';
        }
        
        if (!empty($payment_trxn->payment_txns_id) && $all_student_fees_items[$i]['payment_trxn_status'] != 'Pending') {

            if (!empty($payment_trxn->credit_card_no) && $all_student_fees_items[$i]['payment_trxn_status'] == 'Success') {
                $credit_card_number = $star . substr(str_replace(' ', '', base64_decode($payment_trxn->credit_card_no)), -4);
            } elseif (($payment_trxn->credit_card_no) && $all_student_fees_items[$i]['payment_trxn_status'] == 'Decline') {
                $credit_card_number = $star . substr(str_replace(' ', '', base64_decode($payment_trxn->credit_card_no)), -4);
            } else {
                $credit_card_number = $star . substr(str_replace(' ', '', base64_decode($all_student_fees_items[$i]['credit_card_no'])), -4);
            }
        } else {
            $credit_card_number = '';
        }

        if (!empty($payment_trxn->payment_date) && $all_student_fees_items[$i]['payment_trxn_status'] != 'Pending') {
            $all_student_fees_items[$i]['payment_date'] = my_date_changer($payment_trxn->payment_date);
        } else if (!empty($payment_virtual_trxn->created_on) && $all_student_fees_items[$i]['payment_trxn_status'] != 'Pending') {
            $all_student_fees_items[$i]['payment_date'] =  my_date_changer($payment_virtual_trxn->created_on);
        } else {
            $all_student_fees_items[$i]['payment_date'] = "";
        }


        if (!empty($payment_trxn->payment_txns_id) && empty($payment_virtual_trxn->account_entries_id) && $all_student_fees_items[$i]['payment_trxn_status'] != 'Pending') {
            $all_student_fees_items[$i]['payment_unique_id'] =  $payment_trxn->payment_unique_id;
        } elseif (!empty($payment_virtual_trxn->account_entries_id) && empty($payment_trxn->payment_txns_id) && $all_student_fees_items[$i]['payment_trxn_status'] != 'Pending') {
            $all_student_fees_items[$i]['payment_unique_id'] =  $payment_virtual_trxn->vtxn;
        } elseif (!empty($payment_trxn->payment_txns_id) && !empty($payment_virtual_trxn->vtxn) && $all_student_fees_items[$i]['payment_trxn_status'] != 'Pending') {
            $all_student_fees_items[$i]['payment_unique_id'] = $payment_trxn->payment_unique_id . " <hr style='margin:5px'> " . $payment_virtual_trxn->vtxn;
        } else {
            $all_student_fees_items[$i]['payment_unique_id'] = "";
        }



        if (isset($payment_trxn->payment_txns_id)) {
            $all_student_fees_items[$i]['payment_txns_id'] = $payment_trxn->payment_txns_id;
        } else {
            $all_student_fees_items[$i]['payment_txns_id'] = null;
        }
        if (isset($payment_virtual_trxn->account_entries_id)) {
            $all_student_fees_items[$i]['account_entries_id'] = $payment_virtual_trxn->account_entries_id;
        } else {
            $all_student_fees_items[$i]['account_entries_id'] = null;
        }
        if ($all_student_fees_items[$i]['original_schedule_payment_date'] < date('Y-m-d') && $all_student_fees_items[$i]['schedule_payment_date'] < date('Y-m-d') && $all_student_fees_items[$i]['schedule_status'] == 0) {
            $all_student_fees_items[$i]['retry'] = "Retry";
        } elseif ($all_student_fees_items[$i]['original_schedule_payment_date'] < date('Y-m-d') && $all_student_fees_items[$i]['schedule_payment_date'] < date('Y-m-d') && $all_student_fees_items[$i]['schedule_status'] == 4) {
            $all_student_fees_items[$i]['retry'] = "Retry";
        } else {
            $all_student_fees_items[$i]['retry'] = "";
        }
        $all_student_fees_items[$i]['credit_card_no'] = $credit_card_number;
        $all_student_fees_items[$i]['final_amount'] = $currency . ($all_student_fees_items[$i]['final_amount'] + 0);
        $all_student_fees_items[$i]['child_name'] = $all_student_fees_items[$i]['child_names'];
        
    }
// echo "<pre>";
// print_r($all_student_fees_items) ;
// die;

    $finalAry['data'] = $all_student_fees_items;
    echo json_encode($finalAry);
    exit;
} elseif ($_POST['action'] == 'sendInvoice') {
    if(!empty(get_country()->currency)){
        $currency = get_country()->currency;
    }else{
        $currency = '';
    }
    $user_id = trim($_POST['stu_user_id']);
    $family_id = trim($_POST['family_id']);
    $payment_txns_id = trim($_POST['trxn_id']);
    $account_entries_id = trim($_POST['account_entries_id']);
    $email = trim($_POST['email']);
    if (isset($_POST['trxn_id']) && !empty($_POST['trxn_id']) && $_POST['trxn_id'] != 'null' && isset($_POST['account_entries_id']) && !empty($_POST['account_entries_id']) && $_POST['account_entries_id'] != 'null') {
        $user_payment_txn = $db->get_row("SELECT sft.payment_txns_id, ptx.payment_unique_id, ptx.payment_date, sum(sfi.amount) as final_amount, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, s.family_id, s.user_id, pay.credit_card_no FROM ss_payment_txns ptx  
        INNER JOIN ss_student_fees_transactions sft ON sft.payment_txns_id = ptx.id 
        INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
        INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
        INNER JOIN ss_user u ON u.id = s.user_id
        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
        INNER JOIN ss_family f ON f.id = s.family_id
        INNER JOIN ss_paymentcredentials pay ON pay.id = ptx.payment_credentials_id
        WHERE sft.payment_txns_id = '" . $payment_txns_id . "' AND u.is_active = 1 AND u.is_deleted = 0 ");

        $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where  sft.payment_txns_id='" . $payment_txns_id . "' GROUP BY s.user_id ");

        $child_name = "";
        foreach ($trxn_child_names as $row) {
            $child_name .= $row->first_name . ", ";
        }

        $star = '************';
        $payment_date_formate =  date('F Y', strtotime($user_payment_txn->payment_date));
        $payment_date = my_date_changer($user_payment_txn->payment_date);
        $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($user_payment_txn->credit_card_no)), -4);
        $walletno = "wtx_" . md5($account_entries_id);
    } elseif (isset($_POST['trxn_id']) && !empty($_POST['trxn_id']) && $_POST['trxn_id'] != 'null') {
        $user_payment_txn = $db->get_row("SELECT sft.payment_txns_id, ptx.payment_unique_id, ptx.payment_date, sum(sfi.amount) as final_amount, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, s.family_id, s.user_id, pay.credit_card_no FROM ss_payment_txns ptx  
        INNER JOIN ss_student_fees_transactions sft ON sft.payment_txns_id = ptx.id 
        INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
        INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
        INNER JOIN ss_user u ON u.id = s.user_id
        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
        INNER JOIN ss_family f ON f.id = s.family_id
        INNER JOIN ss_paymentcredentials pay ON pay.id = ptx.payment_credentials_id
        WHERE sft.payment_txns_id = '" . $payment_txns_id . "' AND u.is_active = 1 AND u.is_deleted = 0 ");

        $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where  sft.payment_txns_id='" . $payment_txns_id . "' GROUP BY s.user_id ");

        $child_name = "";
        foreach ($trxn_child_names as $row) {
            $child_name .= $row->first_name . ", ";
        }


        $star = '************';
        $payment_date_formate =  date('F Y', strtotime($user_payment_txn->payment_date));
        $payment_date = my_date_changer($user_payment_txn->payment_date);
        $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($user_payment_txn->credit_card_no)), -4);
        $walletno = '';
    } elseif (isset($_POST['account_entries_id']) && !empty($_POST['account_entries_id']) && $_POST['account_entries_id'] != 'null') {
        $user_payment_txn = $db->get_row("SELECT sfvt.id as virtual_transactions_id, pae.id as account_entries_id, pae.created_on, sum(sfi.amount) as final_amount, f.father_first_name,  f.father_last_name, f.father_phone, f.primary_email, s.family_id, s.user_id
    FROM ss_payment_account_entries pae  
    INNER JOIN ss_student_fees_virtual_transactions sfvt ON sfvt.payment_account_entries_id = pae.id 
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sfvt.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    INNER JOIN ss_user u ON u.id = s.user_id
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
    INNER JOIN ss_family f ON f.id = s.family_id
    WHERE pae.id = '" . $account_entries_id . "' AND u.is_active = 1 AND u.is_deleted = 0 GROUP BY pae.id");

        $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_virtual_transactions sfvt
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sfvt.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
    INNER JOIN ss_family f ON f.id = s.family_id 
    where sfvt.payment_account_entries_id = '" . $account_entries_id . "'  ");
        $child_name = "";
        foreach ($trxn_child_names as $row) {
            $child_name .= $row->first_name . ", ";
        }
        $payment_date_formate =  date('F Y', strtotime($user_payment_txn->created_on));
        $payment_date = my_date_changer($user_payment_txn->created_on);
        $credit_card_no = "";
        $walletno = "wtx_" . md5($account_entries_id);
    }



    $final_amount = $currency . ($user_payment_txn->final_amount + 0);
    $child_name = rtrim($child_name, ', ');

    $emailbody_support = '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
            <tr>
            <td colspan="2" style="text-align: center;">
                <div style="font-size: 30px; text-align:center;"><u>RECEIPT</u></div>
            </td>
        </tr>   
        <tr>
    <td colspan="2" style="text-align: left; padding-top:30px">
    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="10"><tr><td style="width: 25%;" class="color2">Parent Name:</td><td style="width: 75%; text-align:left;">' . $user_payment_txn->father_first_name . ' ' . $user_payment_txn->father_last_name . '</td></tr><tr><td style="width: 25%;" class="color2">Phone
        Number:</td><td style="width: 75%; text-align:left;">' . $user_payment_txn->father_phone . '</td></tr>
<tr><td style="width: 25%;" class="color2">Email:</td><td style="width: 75%; text-align:left;"> ' . $user_payment_txn->primary_email . ' </td></tr><tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td><td style="width: 75%; text-align:left;">' . $child_name . '</td></tr>';
    if (!empty($credit_card_no)) {
        $emailbody_support .= ' <tr>
                                    <td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                    <td style="width: 75%; text-align:left;">' . $user_payment_txn->payment_unique_id . '</td>
                                </tr>
                                <tr>
                                    <td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                    <td style="width: 75%; text-align:left;">' . $credit_card_no . '</td>
                                </tr>
                                   ';
    }
    if (!empty($walletno)) {
        $emailbody_support .= ' <tr><td style="width: 25%;" class="color2">Wallet Payment Transaction ID:</td>
                                     <td style="width: 75%; text-align:left;">' . $walletno . '</td>
                                 </tr>
                                   ';
    }

    $emailbody_support .= ' <tr><td style="width: 25%;" class="color2">Paid
            Amount:</td> 
        <td style="width: 75%; text-align:left;">' . $final_amount . '</td></tr>
    <tr><td style="width: 25%;" class="color2">Payment
            Date:</td>
        <td style="width: 75%; text-align:left;">' . $payment_date . '
            </td></tr>
        </table>
        </td>
    </tr>          
</table>';

    $mailservice_request_from = MAIL_SERVICE_KEY;
    $mail_service_array = array(
        'subject' => CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' Payment Receipt',
        'message' => $emailbody_support,
        'request_from' => $mailservice_request_from,
        'attachment_file_name' => [],
        'attachment_file' => [],
        'to_email' => [$email],
        'cc_email' => [],
        'bcc_email' => []
    );
    $res = mailservice($mail_service_array);

    echo json_encode(array('code' => "1", 'msg' => 'Receipt sent successfully'));
    exit;
} 


elseif ($_POST['action'] == 'status_cancel_all_schedule') {

    $db->query('BEGIN');
    $family_id = trim($_POST['user_id']);
    $reason = $_POST['reason'];

    $students = $db->get_results("select s.user_id, s.first_name, s.last_name from ss_student s inner join ss_user u on u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where s.family_id= '" . $family_id . "' AND u.is_locked=0 AND u.is_active=1 and u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");

$check_execution=$db->get_results("SELECT * from ss_payment_sch_item_cron where family_id='" . $family_id . "' and schedule_status <> 0");

if(count((array)$check_execution)>0){

        echo json_encode(array('code' => "0", 'msg' => 'Payment is in the Execution mode ,Cancelation is not possible '));
        exit;
    
}

    $stu_names = "";
    $sch_months_cancel=array();

    foreach ($students as $row) {
        $user_id = $row->user_id;


        $sql_ret = $db->get_results("select id,schedule_payment_date,schedule_unique_id from ss_student_fees_items  where (schedule_status = 0 OR schedule_status = 3) and student_user_id='" . $user_id . "' ");
        if (!empty($sql_ret)) {
            foreach ($sql_ret as $result) {

                $student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $result->id . "', current_status = 0, new_status=2, comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "', schedule_payment_date = '" .$result->schedule_payment_date. "'");

                 $get_payment_sch_item_cron = $db->get_results("select * from ss_payment_sch_item_cron where is_approval = 1 and schedule_unique_id = '" . $result->schedule_unique_id . "' ");
                
                 $sch_months_cancel[]=my_date_changer($result->schedule_payment_date);

                if(count((array)$get_payment_sch_item_cron)>0){
                   
                   $payment_sch_item_cron = $db->query("update ss_payment_sch_item_cron set schedule_status = 2, updated_at = '" . date('Y-m-d H:i:s') . "' where schedule_unique_id = '" . $result->schedule_unique_id . "' ");
                   $db->query("INSERT INTO  ss_payment_sch_item_cron_backup SELECT * FROM  ss_payment_sch_item_cron WHERE schedule_unique_id = '" . $result->schedule_unique_id . "' ");  
                
                  $delete_invoice = $db->query("delete from ss_invoice where schedule_unique_id ='" . $result->schedule_unique_id . "' ");
                  $delete_payment_sch_item_cron = $db->query("delete from ss_payment_sch_item_cron where schedule_unique_id ='" . $result->schedule_unique_id . "' ");
                }

            }
            $stu_names .= $row->first_name . ' ' . $row->last_name . ", ";
        }

        $sql_ret = $db->query("update ss_student_fees_items set schedule_status = 2 where (schedule_status = 0 OR schedule_status = 3) and student_user_id='" . $user_id . "' ");
    }
    $cancel_dates=array_unique($sch_months_cancel) ;
    $cancel_dates =  implode(', ',$cancel_dates);
   
    // $sch_months_cancel=$db->get_results("SELECT * from ")
    $family_data  = $db->get_row("SELECT f.*, s.* FROM ss_student s inner join ss_family f on f.id = s.family_id where f.id = " . $family_id . " ");

    if ($student_fees_items > 0 && $db->query('COMMIT') !== false) {

        $emailbody_support .= "Assalamu-alaikum " . $family_data->father_first_name . ' ' . $family_data->father_last_name . ",<br>";
        $emailbody_support .= CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " Payment Schedule Update : Canceled. <br><br>";
        $emailbody_support.='Concerning this Payment Schedule, we
        need to inform you, that the payments for all the following
        month(s)and child(ren) will stand canceled.<br><br>';
        $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                    <tr>
                    <td colspan="2" style="text-align: center;">
                            <div style="font-size: 18px;margin-top:30px; text-align:left;"><u> Payment Schedule Cancelation Information </u></div>
                        </td>
                    </tr>   
                    <tr>
                    <td colspan="2" style="text-align: left; padding-top:10px">
                    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">

                    <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                    <td style="width: 75%; text-align:left;">' . rtrim($stu_names, ", ") . '
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2"> Month(s):</td>
                    <td style="width: 75%; text-align:left;">' . rtrim($cancel_dates, ", ") . '
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2"> Payment Schedule Status:</td>
                    <td style="width: 75%; text-align:left;"> Canceled
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2"> Reason:</td>
                    <td style="width: 75%; text-align:left;">' . $reason . '
                    </td></tr>
                    </table>
                    </td>
                    </tr>         
                    </table>';


        $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
        $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='mailto:" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
        $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for the transaction.";
        //Text Message Start
        $message_text = "Dear " . $family_data->father_first_name . ' ' . $family_data->father_last_name . ", Your saturday academy payment scheduled has been cancelled. Far any query please contact at" . SCHOOL_GEN_EMAIL . " Thank you";

        $mailservice_request_from = MAIL_SERVICE_KEY;
        $mail_service_array = array(
            'subject' => CENTER_SHORTNAME . " " . SCHOOL_NAME . ' Payment Schedule Cancelation Information',
            'message' => $emailbody_support,
            'request_from' => $mailservice_request_from,
            'attachment_file_name' => [],
            'attachment_file' => [],
            'to_email' => [$family_data->primary_email, $family_data->secondary_email],
            'cc_email' => [],
            'bcc_email' => []
        );
        mailservice($mail_service_array);
        //SMS Start
        if (!empty($family_data->father_phone)) {
            $receiver_mobile_no = str_replace("-", "", $family_data->father_phone);
        }
        if (!empty($family_data->mother_phone)) {
            $receiver_mobile_no = str_replace("-", "", $family_data->mother_phone);
        }

        // $fetch_limit = 10;
        // $nexmo_mno_ary_index = -1;
        // $nexmo_mno_ary = array('19138843888', '12018172888', '12019030888', '13257578600', '18788801234', '12107145888', '12134091888', '12134094888', '12134097888', '12315590961');
        // $nexmo_mno_ary_index++;
        // if ($nexmo_mno_ary_index == $fetch_limit) {
        //     $nexmo_mno_ary_index = 0;
        // }
        if (strlen($receiver_mobile_no) == 10) {
            $receiver_mobile_no = '+1' . $receiver_mobile_no;
        } elseif (substr($receiver_mobile_no, 0, 2) == '+1') {
            $receiver_mobile_no = substr($receiver_mobile_no, 1);
        }
        
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
        //SMS End


        echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> update successfully </p>'));
        exit;
    } else {

        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '1'));
        exit;
    }

}/////////////////////////////////////////////status_schedule_save ///////  CANCEL BUTTON  ////////////////////////////////////////// 
elseif ($_POST['action'] == 'status_schedule_save') {

    $db->query('BEGIN');

    $family_id = trim($_POST['user_id']);

//     $date=str_replace('/','-',$_POST['sch_pay_date']);
// echo $date."<br>";
if(get_country()->abbreviation=="USA"){
    $sch_pay_date = Date('Y-m-d', strtotime($_POST['sch_pay_date']));
}elseif(get_country()->abbreviation=="GB"){
    $date=str_replace('/','-',$_POST['sch_pay_date']);
    $sch_pay_date = Date('Y-m-d', strtotime( $date));
}


    $current_status_post = strtolower($_POST['current_status']);
    $schedule_status_post = strtolower($_POST['schedule_status']);


    if ($current_status_post == 'success') {
        $current_status = 1;
    } elseif ($current_status_post == 'cancel') {
        $current_status = 2;
    } elseif ($current_status_post == 'hold') {
        $current_status = 3;
    } elseif ($current_status_post == 'skipped') {
        $current_status = 4;
    }elseif ($current_status_post == 5) {
        $current_status = 'Skipped';
    } elseif ($current_status_post == 'pending') {
        $current_status = 0;
    }

    if ($schedule_status_post == 'success') {
        $new_status = 1;
    } elseif ($schedule_status_post == 'cancel') {
        $new_status = 2;
    } elseif ($schedule_status_post == 'hold') {
        $new_status = 3;      
    } elseif ($schedule_status_post == 'Decline') {
        $new_status = 4;
    }elseif ($schedule_status_post == 'pending') {
        $new_status = 0;
    } elseif ($schedule_status_post == 'skipped') {
        $new_status = 5;
    }  elseif ($schedule_status_post == 'resume') {
        $new_status = 0;
    }
    
    $fee_amount = $_POST['fee_amount'];
    $reason = $_POST['reason'];

    $sch_payment_items = $db->get_results("SELECT i.id,i.student_user_id,i.schedule_payment_date,i.schedule_status from ss_student_fees_items i inner join ss_student s on s.user_id = i.student_user_id where i.original_schedule_payment_date='" . $sch_pay_date . "' and s.family_id='" . $family_id . "' AND  (i.schedule_status = 0 OR i.schedule_status = 3) ");
//    echo  $new_status;
//    die;  


if($new_status == 2 || $new_status == 3 ){

    $sch_item_id = trim($_POST['sch_item_id']);

    $stu_remainder_status = $db->get_results("SELECT * FROM `ss_payment_sch_item_cron` WHERE schedule_unique_id='".$_POST['sch_item_id']."' and  schedule_status <> 0");
    
    if($new_status==2){
        $content_box='Payment is in the Execution mode ,Cancelation is not possible ';
    }
    else{
        $content_box='You can`t make any changes, Payment is in the Execution mode';
    }

   if(count((array)$stu_remainder_status) > 0){

        echo json_encode(array('code' => "0", 'msg' => $content_box));
        exit;
    }
}

    foreach ($sch_payment_items as $sch_items) {


        if ($schedule_status_post == 'resume' && $sch_items->schedule_payment_date < date('Y-m-d')) {

            $next_payment_date = $db->get_var("SELECT schedule_payment_date FROM ss_student_fees_items WHERE student_user_id = '" . $sch_items->student_user_id . "' AND schedule_status=0 ORDER BY original_schedule_payment_date ASC");

            $sql_ret = $db->query("update ss_student_fees_items set  schedule_payment_date='" . $next_payment_date . "' , schedule_status='" . $new_status . "' where id='" . $sch_items->id . "' and student_user_id='" . $sch_items->student_user_id . "' ");

            $student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $sch_items->id . "', current_status = '" . $current_status . "', new_status='" . $new_status . "', comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "', schedule_payment_date = '" .$sch_items->schedule_payment_date. "'");
        } else {
        
            if($new_status ==2){
            
                $invoice_id = $db->get_var("SELECT inv.id FROM `ss_invoice` as inv
                inner join ss_invoice_info as inv_info on inv_info.invoice_id=inv.id
                WHERE inv.schedule_unique_id= '" . $sch_item_id . "' ");

                $get_payment_sch_item_cron = $db->get_results("select * from ss_payment_sch_item_cron where is_approval = 1 and schedule_unique_id = '" . $sch_item_id . "' ");
              
                if(count((array)$get_payment_sch_item_cron)>0){
                   //is approval = 0 means cancel used for backup purpose
                   $payment_sch_item_cron = $db->query("update ss_payment_sch_item_cron set is_approval = 0, updated_at = '" . date('Y-m-d H:i:s') . "' where schedule_unique_id = '" . $sch_item_id . "' ");

                   $db->query("INSERT INTO  ss_payment_sch_item_cron_backup SELECT * FROM  ss_payment_sch_item_cron WHERE schedule_unique_id = '" . $sch_item_id . "' ");  

                   if(!empty($invoice_id)){
                    $ss_invoice_info_delete = $db->query("delete from ss_invoice_info where invoice_id ='" . $invoice_id . "' ");
                   }

                  $delete_invoice = $db->query("delete from ss_invoice where schedule_unique_id ='" . $sch_item_id . "' ");
                  $delete_payment_sch_item_cron = $db->query("delete from ss_payment_sch_item_cron where schedule_unique_id ='" . $sch_item_id . "' ");
                }
               
            }
            
            $sql_ret = $db->query("update ss_student_fees_items set schedule_status='" . $new_status . "' where id='" . $sch_items->id . "' and student_user_id='" . $sch_items->student_user_id . "' ");
            

            $student_fees_items = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $sch_items->id . "', current_status = '" . $current_status . "', new_status='" . $new_status . "', comments = '" . $reason . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_at = '" . date('Y-m-d H:i') . "', schedule_payment_date = '" .$sch_items->schedule_payment_date. "'");
        }
    }


    $family_data  = $db->get_row("SELECT  s.*,GROUP_CONCAT(s.first_name,' ',s.last_name) as children_name,f.* FROM ss_student s inner join ss_family f on f.id = s.family_id where  f.id = " . $family_id . " ");


    $emailbody_support .= "Assalamu-alaikum " . $family_data->father_first_name . ' ' . $family_data->father_last_name . ",<br>";
    $emailbody_support .= CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " Payment Schedule Update : " . $_POST['schedule_status'] . ". <br><br>";

    $emailbody_support.='Concerning this Payment Schedule, we
    need to inform you, that the payments for all the following
    month(s)and child(ren) will stand ' . $_POST['schedule_status'] . '.<br><br>';

    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                <tr>
                <td colspan="2" style="text-align: center;">
                        <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>
Payment Schedule ' . $_POST['schedule_status'] . ' Information </u></div>
                    </td>
                </tr>   
                <tr>
                <td colspan="2" style="text-align: left; padding-top:10px">
                <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">

                <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                <td style="width: 75%; text-align:left;">' . rtrim($family_data->children_name, ', ') .' 
                </td></tr>

                <tr><td style="width: 25%;" class="color2">Payment Schedule Date:</td>
                <td style="width: 75%; text-align:left;">' . my_date_changer($sch_pay_date) . '
                </td></tr>
                    
                <tr><td style="width: 25%;" class="color2">Amount:</td> 
                    <td style="width: 75%; text-align:left;">' . $fee_amount . '</td></tr>

                <tr><td style="width: 25%;" class="color2"> Payment Schedule Old Status:</td>
                <td style="width: 75%; text-align:left;">' . $_POST['current_status'] . '
                </td></tr>

                <tr><td style="width: 25%;" class="color2"> Payment Schedule New Status:</td>
                <td style="width: 75%; text-align:left;">' . $_POST['schedule_status'] . '
                </td></tr>

                <tr><td style="width: 25%;" class="color2"> Reason:</td>
                <td style="width: 75%; text-align:left;">' . $reason . '
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
        'to_email' => [$family_data->primary_email,$family_data->secondary_email],
        'cc_email' => '',
        'bcc_email' => ''
    );

    mailservice($mail_service_array);

    if ($db->insert_id > 0) {

        $db->query('COMMIT');
        echo json_encode(array('code' => "1", 'msg' => '<p class="text-success"> update successfully </p>'));
        exit;
    } else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0", 'msg' => 'Process failed', '_errpos' => '2'));
        exit;
    }
} elseif ($_POST['action'] == "retry_schedule_payment") {
    $itemid = $_POST['itemid'];
    $user_id = $_POST['id'];
    $check_count = 0;
    $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 ");

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
        $actualbasicDiscountFees = $final_amount;
    } else {
        $actualbasicDiscountFees = 0;
    }
    $userDataAmount = $actualbasicDiscountFees;
    //  $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $user_id . "' AND schedule_status = 0 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

    $student_data = $db->get_row("SELECT s.admission_no, s.user_id, s.school_grade, f.id as family_id, f.forte_customer_token,  f.father_phone, f.father_area_code, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_city, f.billing_post_code, pay.credit_card_type, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv, pay.forte_payment_token, pay.id as payment_credential_id FROM ss_user u 
     INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id where s.user_id='" . $user_id . "' AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND pay.default_credit_card = 1 AND pay.credit_card_deleted = 0 ");

    if (isset($student_data->credit_card_type) && isset($student_data->credit_card_no) && isset($student_data->credit_card_exp)) {
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
            $forteParamsSend = array('coustomer_token' => $forte_customer_token, 'paymentAction' => 'Sale', 'itemName' => 'Fees', 'itemNumber' => '10001', 'amount' => $userDataAmount, 'currencyCode' => 'USD', 'creditCardType' => $CardType, 'creditCardNumber' => $CardNumber, 'expMonth' => $CardExpiryMonth, 'expYear' => $CardExpiryYear, 'cvv' => $CardCVV, 'firstName' => $cardHolderFirstName, 'lastName' => $cardHolderLastName, 'email' => $userDataEmail, 'phone' => $userDataPhoneNo, 'city' => $userDataCity, 'zip' => $userDataZip, 'countryCode' => 'US', 'recurring' => 'Yes');
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
                        $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
                    } else {
                        $responsemsg = "Payment processing failed. Please retry";
                    }
                }
            } else {
                $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
                if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
                    $customertoken = $customerPostRequest->customer_token;
                    $paymethodtoken = $customerPostRequest->default_paymethod_token;
                    $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . $student_data->family_id . "'");
                    $db->query("update ss_paymentcredentials set forte_payment_token='" . $paymethodtoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id='" . $student_data->payment_credential_id . "' ");
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
                $stu_items_data = $db->get_results("SELECT sfi.*, s.family_id FROM ss_student_fees_items sfi 
                                INNER JOIN ss_student s ON s.user_id = sfi.student_user_id  
                                INNER JOIN ss_user u ON u.id = s.user_id 
                                AND schedule_status = 0 AND sfi.student_user_id = '" . $user_id . "' AND sfi.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND schedule_payment_date < '" . date('Y-m-d') . "'
                                AND u.is_active=1 AND u.is_deleted=0");

                if (count((array)$stu_items_data) > 0) {
                    $get_result = $db->query("update ss_student_fees_items set schedule_status = 0, original_schedule_payment_date = '" . date('Y-m-d') . "', schedule_payment_date = '" . date('Y-m-d') . "',  updated_at = '" . date('Y-m-d H:i') . "' where id = '" . $itemid . "' ");
                }
                if ($get_result) {
                    $check_count++;
                }
                if ($check_count > 0) {
                    $db->query('COMMIT');
                    echo json_encode(array('code' => '1', 'msg' => "Scheduled Successfully"));
                    exit;
                } else {
                    $db->query('ROLLBACK');
                    echo json_encode(array('code' => "0", 'msg' => 'Scheduled Not Proceed.'));
                    exit;
                }
            } else {
                //forte customer create failed else
                $db->query('ROLLBACK');
                echo json_encode(array('code' => '0', 'donorname' => $cardHolderFirstName, 'msg' => "$responsemsg", 'errpos' => 18));
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
