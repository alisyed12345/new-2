<?php
include_once "../includes/config.php";
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}
if (!empty(get_country()->currency)) {
    $currency = get_country()->currency;
} else {
    $currency = '';
}
if ($_POST['action'] == 'list_account_transaction') {
    $finalAry = array();

    if (!empty($_POST['family_user_id'])) {
        $family_user_id = trim($_POST['family_user_id']);
    } else {
        $family_user_id = 0;
    }

    $account_id = $db->get_var("SELECT `id` FROM `ss_payment_accounts` WHERE `user_id`='" . $family_user_id . "'");
    $all_transaction_data = $db->get_results("SELECT `ent`.`id`,CONCAT('wtx_',md5(ent.id)) as vtxn,`ent`.`description`, `ent`.`amount`, `ent`.`debit_pay_account_id`, `ent`.`credit_pay_account_id`,`ent`.`created_on` FROM `ss_payment_account_entries` `ent` WHERE (`ent`.`debit_pay_account_id`='" . $account_id . "' OR  `ent`.`credit_pay_account_id`='" . $account_id . "') order by `ent`.`id` DESC", ARRAY_A);

    for ($i = 0; $i < count((array)$all_transaction_data); $i++) {

        $debit_pay_account_id = $all_transaction_data[$i]['debit_pay_account_id'];
        $credit_pay_account_id = $all_transaction_data[$i]['credit_pay_account_id'];

        if ($debit_pay_account_id == $account_id) {
            $debitAmount = $all_transaction_data[$i]['amount'];
        } else {
            $debitAmount = '';
        }
        if ($credit_pay_account_id == $account_id) {
            $creditAmount = $all_transaction_data[$i]['amount'];
        } else {
            $creditAmount = '';
        }
        $allamount = $currency . $creditAmount . '' . $debitAmount;


        if ($account_id == $debit_pay_account_id) {
            $payment_type = "Debit";
        } elseif ($account_id == $credit_pay_account_id) {
            $payment_type = "Credit";
        } else {
            $payment_type = "-";
        }

        /*  $creditAmountSum+=$creditAmount;
        $debitAmountSum+=$debitAmount;

        $totalAmount=$creditAmountSum-$debitAmountSum; */

        $all_transaction_data[$i]['id'] = $all_transaction_data[$i]['id'];
        $all_transaction_data[$i]['vtxn'] = $all_transaction_data[$i]['vtxn'];
        $all_transaction_data[$i]['date'] = my_date_changer($all_transaction_data[$i]['created_on'], 't');;
        $all_transaction_data[$i]['description'] = $all_transaction_data[$i]['description'];
        $all_transaction_data[$i]['debit'] = $debitAmount;
        $all_transaction_data[$i]['credit'] = $creditAmount;
        $all_transaction_data[$i]['allamount'] = $allamount;
        $all_transaction_data[$i]['payment_type'] = $payment_type;
        //$all_transaction_data[$i]['totalAmount'] = $totalAmount;
    }
    $finalAry['data'] = $all_transaction_data;
    echo json_encode($finalAry);
    exit;
} elseif ($_POST['action'] == 'account_payment_transactions') {

    $payment_amount = trim($_POST['payment_amount']);
    $payment_type = trim($_POST['payment_type']);
    $description = trim($_POST['description']);
    $family_id = $_POST['family_id'];

    if (!empty($_POST['family_user_id'])) {
        $family_user_id = trim($_POST['family_user_id']);
    } else {
        $family_user_id = 0;
    }

    $family = $db->get_row("SELECT user_id, CONCAT(father_first_name,' ',COALESCE(father_last_name, '')) AS father_name FROM `ss_family` WHERE id = '" . $family_id . "'");
    $SchoolAccountResult = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='ick_school' limit 1 ");
    $FamilyAccountResult = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `user_id`='" . $family_user_id . "' ORDER by id DESC limit 1 ");

    if (empty($FamilyAccountResult)) {
        $db->query("insert into ss_payment_accounts set account_holder='" . $family->father_name . "', user_id = '" . $family->user_id . "', system_account = '1', created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
        $FamilyAccountResult = $db->insert_id;
    } else {
        $FamilyAccountResult = $FamilyAccountResult;
    }

    if ($FamilyAccountResult > 0) {

        //---Credit---//
        if ($payment_type == '1') {
            $credit_pay_account_id = $FamilyAccountResult;
            $debit_pay_account_id = $SchoolAccountResult;
        }
        //---Debit---//
        elseif ($payment_type == '0') {
            $debit_pay_account_id = $FamilyAccountResult;
            $credit_pay_account_id = $SchoolAccountResult;
        }
        $db->query('BEGIN');
        $Result = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `is_manual`, `created_on`, `created_by_user_id`) VALUES ('" . $description . "','" . $payment_amount . "','" . $debit_pay_account_id . "','" . $credit_pay_account_id . "','1','" . date('Y-m-d H:i:s') . "','" . $_SESSION['icksumm_uat_login_userid'] . "') ");
        $payment_transaction_id = $db->insert_id;
        if ($payment_transaction_id > 0) {
            $db->query('COMMIT');
            echo json_encode(array('code' => "1", 'msg' => "<p class='text-success'> Transaction completed successfully <p>"));
            exit;
        } else {
            $db->query('ROLLBACK');
            echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'> Transaction Failed <p>"));
            exit;
        }
    } else {
        echo json_encode(array('code' => "0", 'msg' => "<p class='text-danger'> User Payment Account Not Found <p>"));
        exit;
    }
} elseif ($_POST['action'] == 'account_status') {
    $totalAmount = 0;
    if (!empty($_POST['family_user_id'])) {
        $family_user_id = trim($_POST['family_user_id']);
    } else {
        $family_user_id = 0;
    }

    $account_id = $db->get_var("SELECT `id` FROM `ss_payment_accounts` WHERE `user_id`='" . $family_user_id . "'");
    $CCAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='CC' limit 1 ");///used to remove credit card to virtual account entries 

    $all_transaction_data = $db->get_results("SELECT `ent`.`id` ,`ent`.`amount`, `ent`.`debit_pay_account_id`, `ent`.`credit_pay_account_id` FROM `ss_payment_account_entries` `ent` WHERE (`ent`.`debit_pay_account_id`='" . $account_id . "' OR  `ent`.`credit_pay_account_id`='" . $account_id . "') AND is_force_payment = 0 and debit_pay_account_id <> '" . $CCAccountId . "'", ARRAY_A);

    $creditAmountSum = 0;
    $debitAmountSum = 0;

    for ($i = 0; $i < count((array)$all_transaction_data); $i++) {


        $debit_pay_account_id = $all_transaction_data[$i]['debit_pay_account_id'];
        $credit_pay_account_id = $all_transaction_data[$i]['credit_pay_account_id'];
        // echo $debit_pay_account_id.' id '.$credit_pay_account_id.' amount '.$all_transaction_data[$i]['amount']."<br>";

        if ($debit_pay_account_id == $account_id ) {
            $debitAmount = $all_transaction_data[$i]['amount'];
        } else {
            $debitAmount = 0;
        }
        if ($credit_pay_account_id == $account_id) {
            $creditAmount = $all_transaction_data[$i]['amount'];
        } else {
            $creditAmount = 0;
        }

        if (!empty($creditAmount)) {
            $creditAmountSum += $creditAmount;
            // echo $creditAmountSum ."credit"."<br>";
        } else {
            $creditAmountSum = $creditAmountSum;
            // echo $creditAmountSum."credit"."<br>";
        }
        if (!empty($debitAmount)) {
            $debitAmountSum += $debitAmount;
            // echo $debitAmountSum."debit"."<br>";
        } else {
            $debitAmountSum = $debitAmountSum;
            // echo $debitAmountSum."debit"."<br>";
        }
    }
// echo $debitAmountSum."<br>";
// echo $creditAmountSum;
// die;

    $totalAmount = $creditAmountSum - $debitAmountSum;
    $account_status_amount= $totalAmount;
    if ($totalAmount == 0) {
        $totalAmount = $currency . $totalAmount;
    } elseif ($totalAmount > 0) {
        $totalAmount = $currency . $totalAmount;
    } elseif ($totalAmount < 0) {
        $totalAmount = "<span class='text-danger'>(-)</span>" . $currency . abs($totalAmount);
    }

    echo json_encode(array('code' => "1", 'msg' =>  $totalAmount,'account_status_amount' =>  $account_status_amount));
    exit;
}
