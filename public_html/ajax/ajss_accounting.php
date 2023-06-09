<?php
include "../includes/config.php";


if ($_POST['action'] == 'accounting_record') {

    $identity = $_POST['acc_identity'];

    $family_id = $db->escape($_POST['family_id']);


    $currency_sign = ''; //// jab amount k saath dollar dikhana ho to isme iska use kare get_country()->currency

    ////////////////// manu_is_type ///////////////////////////////////

    if (!empty($identity) && $identity == 'manu_is_type') {

        $manual_record =  $db->get_results("SELECT CONCAT(f.father_first_name,f.father_last_name) AS father_name,f.primary_email, txn.payment_date, txn.id AS payment_txn_id, 
        txn.payment_unique_id, txn.payment_gateway, rtxn.refund_txn_id, txn.comments AS description, txn.amount, txn.payment_credentials_id
        FROM ss_family as f 
        inner join ss_invoice as inv on f.id=inv.family_id
        inner join ss_invoice_info as inv_info on inv_info.invoice_id=inv.id
        inner join ss_payment_txns as txn on txn.id=inv_info.payment_txn_id
        LEFT JOIN ss_refund_payment_txns AS rtxn ON rtxn.payment_txn_id = txn.id
        inner join ss_user as u on u.id=f.user_id
        inner join ss_paymentcredentials as pc on pc.family_id=f.id
        where f.id='" . $family_id . "' and u.is_active=1 and u.is_deleted=0 and txn.is_payment_type=4 and pc.default_credit_card=1", ARRAY_A);

        if (!empty($manual_record)) {
            foreach ($manual_record  as $key => $sch) {
                $manual_record[$key]['payment_date'] = my_date_changer($manual_record[$key]['payment_date']);
                $manual_record[$key]['type'] = '<p style="text-align: center;">-</p>';

                if (!empty($manual_record[$key]['amount'])) {
                    $manual_record[$key]['txn_amount'] = $manual_record[$key]['amount'];
                    $manual_record[$key]['amount'] = $currency_sign . $manual_record[$key]['amount'];
                }
                $manual_record[$key]['student_name'] = "";
            }
        }

        $finalAry['data'] = $manual_record;
        echo json_encode($finalAry);
        exit;


        ////////////////// reg_is_type ///////////////////////////////////

    } elseif (!empty($identity) && $identity == 'reg_is_type') {
        // $reg_query = $db->get_results("SELECT reg.id AS reg_id, CONCAT(reg.father_first_name,'', reg.father_last_name) AS father_name, GROUP_CONCAT(CONCAT(child.first_name,' ',child.last_name)) AS student_name, reg.is_paid, reg.internal_registration, reg.amount_received, reg.created_on, reg.internal_registration, reg.is_paid, f.primary_email FROM ss_sunday_school_reg reg INNER JOIN ss_sunday_sch_req_child child ON child.sunday_school_reg_id = reg.id 
        // INNER JOIN ss_user u ON u.email = reg.father_email
        // INNER JOIN ss_family f ON u.id = f.user_id
        // where f.id='" . $family_id . "' and u.is_active=1 and u.is_deleted=0 AND reg.is_waiting = 0 GROUP BY reg.id", ARRAY_A);

        //ADDED BY MUSAIB
        $reg_query = $db->get_results("SELECT reg.id AS reg_id, CONCAT(reg.father_first_name,'', reg.father_last_name) AS father_name, GROUP_CONCAT(CONCAT(child.first_name,' ',child.last_name)) AS student_name, reg.is_paid, reg.internal_registration, reg.amount_received, reg.created_on, reg.internal_registration, reg.is_paid, f.primary_email 
        FROM ss_sunday_school_reg reg 
        INNER JOIN ss_sunday_sch_req_child child ON child.sunday_school_reg_id = reg.id
        INNER JOIN ss_user u ON u.id = child.user_id
        inner join ss_student as s on s.user_id=u.id
        inner join ss_family as f on f.id=s.family_id
        where f.id=" . $family_id . " and u.is_active=1 and u.is_deleted=0 AND reg.is_waiting = 0 GROUP BY reg.id", ARRAY_A);


        if (count((array)$reg_query) > 0) {
            for ($i = 0; $i < count((array)$reg_query); $i++) {
                $pay_txn_query = $db->get_row("SELECT txn.id, txn.payment_date, txn.amount, txn.is_payment_type, rtxn.refund_txn_id, txn.payment_unique_id, txn.payment_gateway 
                FROM ss_payment_txns txn 
                LEFT JOIN ss_refund_payment_txns rtxn ON rtxn.payment_txn_id = txn.id 
                WHERE txn.sunday_school_reg_id = '" . $reg_query[$i]['reg_id'] . "' AND txn.is_payment_type <> 6");

                if (count((array)$pay_txn_query) > 0) {
                    $refund_pay_query = $db->get_row("SELECT txn.id, refund.payment_txn_id, refund.family_id, txn.payment_unique_id, txn.payment_gateway, refund.refund_amount, refund.refund_txn_id FROM ss_refund_payment_txns refund INNER JOIN ss_payment_txns txn ON txn.id = refund.refund_txn_id WHERE refund_txn_id ='" . $pay_txn_query->refund_txn_id . "'");
                    $pay_credencial = $db->get_row("SELECT id, credit_card_no, credit_card_exp FROM ss_paymentcredentials WHERE family_id ='" . $refund_pay_query->family_id . "'");
                    $reg_query[$i]['payment_unique_id'] = $refund_pay_query->payment_unique_id;
                    $reg_query[$i]['payment_gateway'] = $refund_pay_query->payment_gateway;
                    $reg_query[$i]['payment_txn_id'] = $pay_txn_query->id;
                    $reg_query[$i]['payment_credentials_id'] = $pay_credencial->id;
                    $reg_query[$i]['txn_amount'] = $refund_pay_query->refund_amount;
                    $reg_query[$i]['amount'] = $reg_query[$i]['amount_received'];

                    if (empty($refund_pay_query->payment_gateway) && empty($refund_pay_query->payment_unique_id)) {
                        $pay_credencial = $db->get_row("SELECT id, credit_card_no, credit_card_exp FROM ss_paymentcredentials WHERE family_id ='" . $family_id . "'");
                        $reg_query[$i]['payment_unique_id'] = $pay_txn_query->payment_unique_id;
                        $reg_query[$i]['payment_gateway'] = $pay_txn_query->payment_gateway;
                        $reg_query[$i]['payment_txn_id'] = $pay_txn_query->id;
                        $reg_query[$i]['payment_credentials_id'] = $pay_credencial->id;
                        $reg_query[$i]['txn_amount'] = $reg_query[$i]['amount_received'];
                        $reg_query[$i]['amount'] = $reg_query[$i]['amount_received'];

                    }
                }

                $payment_date = date('m/d/Y', strtotime($reg_query[$i]['created_on']));
                $reg_query[$i]['payment_date'] = my_date_changer($payment_date);
                if ($reg_query[$i]['internal_registration'] == '0' && ($reg_query[$i]['is_paid'] == '1')) {
                    $reg_query[$i]['type'] = 'External ';
                } elseif (($reg_query[$i]['internal_registration'] == '1') && ($reg_query[$i]['is_paid'] == '1') && ($reg_query[$i]['amount_received'] != '0.00')) {
                    $reg_query[$i]['type'] = 'Internal ';
                } elseif (($reg_query[$i]['internal_registration'] == '1') && ($reg_query[$i]['is_paid'] == '0') && ($reg_query[$i]['amount_received'] == '0.00')) {
                    $reg_query[$i]['type'] = 'Internal ';
                }
                $reg_query[$i]['refund_txn_id'] = $refund_pay_query->refund_txn_id;
                $reg_query[$i]['amount'] = $currency_sign . $reg_query[$i]['amount_received'];
                $reg_query[$i]['description'] = '<p style="text-align:center;">-</p>';

                if($reg_query[$i]['amount_received'] == '0.00'){

                    $registration_amount =  $db->get_var("SELECT rft.amount FROM ss_registration_fee_txns as rft 
                    inner join ss_payment_txns as txn on txn.sunday_school_reg_id=rft.registration_id
                    where txn.payment_status=1 and rft.registration_id='" . $reg_query[$i]['reg_id'] . "'");

                      if(!empty($registration_amount)){
                          $reg_query[$i]['amount']=  $registration_amount ;
                          $reg_query[$i]['txn_amount'] =$registration_amount;
                      }else{
                        $reg_query[$i]['amount'] = $currency_sign . $reg_query[$i]['amount_received'];  
                      }
                }


            }
        }
        $finalAry['data'] = $reg_query;
        echo json_encode($finalAry);
        exit;
    }
    ////////////////// sch_is_type ///////////////////////////////////
    elseif (!empty($identity) && $identity == 'sch_is_type') {
        $schedule_record =  $db->get_results('SELECT sfi.id as sch_id,CONCAT(f.father_first_name,f.father_last_name) AS father_name,f.primary_email,SUM(sfi.amount) as amount, CASE WHEN sfi.schedule_status = 1 THEN "Success" ELSE "Failed" END AS sch_status, sfi.schedule_payment_date as payment_date,GROUP_CONCAT(s.first_name," ",s.last_name)as student_name, txn.id as payment_txn_id, txn.payment_unique_id, txn.payment_gateway, txn.payment_credentials_id,rtxn.refund_txn_id
        FROM ss_student_fees_items as sfi
        inner join ss_student as s on sfi.student_user_id=s.user_id
        inner join ss_family as f on f.id=s.family_id
        left join ss_student_fees_transactions as sft on sft.student_fees_item_id = sfi.id
        left join ss_payment_txns as txn on txn.id = sft.payment_txns_id
        left join ss_refund_payment_txns as rtxn on rtxn.payment_txn_id = txn.id
        WHERE f.id=' . $family_id . ' and sfi.session=' . $_SESSION['icksumm_uat_CURRENT_SESSION'] . ' and sfi.schedule_status=1 GROUP BY schedule_unique_id', ARRAY_A);

        if (!empty($schedule_record)) {
            foreach ($schedule_record  as $key => $sch) {

            $wallet_amount_pe_id = $db->get_row("SELECT pe.amount,pe.debit_pay_account_id as payid_user
            from ss_student_fees_virtual_transactions as sfvt
            inner join ss_payment_account_entries as pe on pe.id=sfvt.payment_account_entries_id
            where student_fees_item_id='".$schedule_record[$key]['sch_id']."'");

            if(!empty($wallet_amount_pe_id)){
                $schedule_amount = $schedule_record[$key]['amount'] - $wallet_amount_pe_id->amount;
                $schedule_record[$key]['txn_amount'] = $schedule_amount;
                $schedule_record[$key]['wallet_amount'] = $wallet_amount_pe_id->amount;
                $schedule_record[$key]['payid_user'] = $wallet_amount_pe_id->payid_user;
                $schedule_record[$key]['amount'] = $currency_sign . $schedule_record[$key]['amount'];
            }elseif (!empty($schedule_record[$key]['amount'])) {
                $schedule_record[$key]['txn_amount'] = $schedule_record[$key]['amount'];
                $schedule_record[$key]['amount'] = $currency_sign . $schedule_record[$key]['amount'];
            }

        //    $schedule_amount = $db->get_var("select sum(sfi.amount) from ss_student_fees_virtual_transactions as sfvt
        //     inner join ss_student_fees_items as sfi on sfi.id=sfvt.student_fees_item_id
        //     inner join ss_payment_account_entries as pe on pe.id=sfvt.payment_account_entries_id
        //     where sfvt.payment_account_entries_id='".$wallet_ids."'");

                $schedule_record[$key]['description'] = '<p style="text-align: center;">-</p>';
                $schedule_record[$key]['type'] = $schedule_record[$key]['sch_status'];


                $schedule_record[$key]['payment_date'] = my_date_changer($schedule_record[$key]['payment_date']);
            }
            
        }

        $finalAry['data'] = $schedule_record;
        echo json_encode($finalAry);
        exit;
        ////////////////////////////////////////////// wal_is_type ////////////////////////////////////////////
    } elseif (!empty($identity) && $identity == 'wal_is_type') {

        $finalAry = array();

        if (!empty($_POST['family_id'])) {
            $family_id = trim($_POST['family_id']);
        }

        $student_name = $db->get_row("SELECT group_concat(s.first_name,' ',s.last_name ,' ') as student_name, f.user_id FROM ss_family as f
        inner join ss_student as s on f.id=s.family_id where f.id='" . $family_id . "'");
    
        $wallet_record = $db->get_results("(SELECT pe.id,CONCAT(f.father_first_name,' ',f.father_last_name) AS father_name,f.primary_email,pe.amount ,pe.description,pe.created_on as payment_date,pe.debit_pay_account_id,pe.credit_pay_account_id,'Added to parent account' as type
        FROM `ss_payment_accounts` as pa
        inner join ss_user as u on u.id = pa.user_id
        inner join ss_family as f on f.user_id=u.id
        inner join ss_student as s on s.family_id=f.id
        inner join ss_payment_account_entries as pe on pa.id= pe.credit_pay_account_id
        WHERE pa.user_id='" . $student_name->user_id . "'  and pe.is_force_payment=0 GROUP by pe.created_on
        UNION 
        SELECT pe.id,CONCAT(f.father_first_name,' ',f.father_last_name) AS father_name,f.primary_email,pe.amount ,pe.description,pe.created_on as payment_date,pe.debit_pay_account_id,pe.debit_pay_account_id,'Deduct from parent account' as type
        FROM `ss_payment_accounts` as pa
        inner join ss_user as u on u.id = pa.user_id
        inner join ss_family as f on f.user_id=u.id
        inner join ss_student as s on s.family_id=f.id
        inner join ss_payment_account_entries as pe on pa.id= pe.debit_pay_account_id
        WHERE pa.user_id='" . $student_name->user_id . "'  and pe.is_force_payment=0 GROUP by pe.created_on) order by id asc
        ", ARRAY_A);

        $remove_null_index = "";

        foreach ($wallet_record as $index => $value) {

            if ($wallet_record[$index]['credit_pay_account_id'] == "null" || $wallet_record[$index]['credit_pay_account_id'] == "") {
                $remove_null_index = $index;
            }
        }
        unset($wallet_record[$remove_null_index]);

        if (!empty($wallet_record)) {
            foreach ($wallet_record as $key => $value) {

                $wallet_record[$key]['payment_date'] = my_date_changer($wallet_record[$key]['payment_date']);

                $wallet_record[$key]['hide_button'] = 1;
                $wallet_record[$key]['student_name'] = $student_name->student_name;
                if (!empty($wallet_record[$key]['amount'])) {
                    $wallet_record[$key]['amount'] = $currency_sign . $wallet_record[$key]['amount'];
                }
            }
        }
        $finalAry['data'] = $wallet_record;
        echo json_encode($finalAry);
        exit;
    }
    /////////////////////////////////////////////////////////// refund_is_type ////////////////////////////////     
    elseif (!empty($identity) && $identity == 'refund_is_type') {
        $refund_payments =  $db->get_results("SELECT txn.payment_date, f.primary_email, txn.comments as description,ref.refund_amount as amount, ref.payment_txn_id,ref.refund_txn_id
        FROM ss_refund_payment_txns ref
        inner join ss_family f ON f.id = ref.family_id
        inner join ss_payment_txns as txn on txn.id= ref.refund_txn_id
        where txn.is_payment_type=6 and ref.family_id='" . $family_id . "'", ARRAY_A);



        if (!empty($refund_payments)) {
            foreach ($refund_payments as $key => $value) {

                $type_id = $db->get_var('SELECT CASE 
                    WHEN is_payment_type=1 THEN "internal registration"
                    WHEN is_payment_type=2 THEN "external registration"
                    WHEN is_payment_type=3 THEN "Schedule"
                    WHEN is_payment_type=4 THEN "Manual"
                    else ""
                    END  as type
                    FROM `ss_payment_txns` WHERE id="' . $refund_payments[$key]['payment_txn_id'] . '"');

                $refund_payments[$key]['student_name'] = 'hide';
                $refund_payments[$key]['type'] = $type_id;
                $refund_payments[$key]['refund_amount'] = $refund_payments[$key]['refund_amount'];
                $refund_payments[$key]['payment_date'] = my_date_changer($refund_payments[$key]['payment_date']);
            }
        }

        $finalAry['data'] = $refund_payments;
        echo json_encode($finalAry);
        exit;
    }
}
///////////////////////////////////////////////// view_refund_sechedule_history //////////////////////////////
elseif ($_POST['action'] == 'view_refund_sechedule_history') {

    if (!empty($_POST['payment_txn_id'] && !empty($_POST['refund_txn_id']))) {
        $payment_txn_id = trim($_POST['payment_txn_id']);
        $refund_txn_id = trim($_POST['refund_txn_id']);
    }
    $refund_history_items = $db->get_results("SELECT ref.created_on as payment_date,txn.comments as reason,ref.refund_amount,ref.payment_txn_id,ref.refund_txn_id,ref.created_by_user_id as created_by
    FROM ss_refund_payment_txns ref
    inner join ss_payment_txns as txn on txn.id= ref.refund_txn_id
    where txn.is_payment_type = 6 and ref.refund_txn_id='" . $refund_txn_id . "' and ref.payment_txn_id='" . $payment_txn_id . "'");

    foreach ($refund_history_items as $key => $value) {
        $value->created_by = getUserFullName($value->created_by);
        if ($value->created_by == null) {
            $value->created_by = '-';
        }
        $value->payment_date = my_date_changer($value->payment_date);
    }

    if (count((array)$refund_history_items) > 0) {
        echo json_encode(array('code' => "1", 'msg' => $refund_history_items));
        exit;
    } else {
        echo json_encode(array('code' => "0", 'msg' => "Data not found."));
        exit;
    }
}
