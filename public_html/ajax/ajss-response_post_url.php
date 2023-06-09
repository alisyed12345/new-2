<?php
include_once "../includes/config.php";
include_once '../includes/dompdf/autoload.inc.php';
get_country()->timezone;
define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;
// if(isset($_POST) && !empty($_POST)){
if (!empty(get_country()->currency)) {
    $currency = get_country()->currency;
} else {
    $currency = '';
}
//PAYMENT VERIFY START
if (isset($_POST['action']) && $_POST['action'] == "payment_verify") {

    if (isset($_POST['response_code']) && $_POST['response_code'] == "2") {

        $status = 1;
        $code = 0;
    } else {
        if (IsWaiting == 0) {
            $transactions = $db->get_row("SELECT * FROM `ss_payment_txns` WHERE payment_unique_id = '" . $_POST['payment_unique_id'] . "' ");
        } else {
            $transactions = $db->get_row("SELECT * FROM `ss_sunday_sch_payment` WHERE forte_payment_token = '" . $_POST['payment_unique_id'] . "' ");
        }

        if (empty($transactions)) {
            $status = 0;
            $code = 0;
        } else {
            $status = 1;
            $code = 1;
        }
    }


    $db->query("insert into ss_received_payment_txn_ids set 
        request_token='" . $_POST['request_token'] . "', 
        status='" . $status . "', 
        ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
        created_dt='" . date('Y-m-d H:i:s') . "' ");

    $id = $db->insert_id;

    if ($id > 0 && $code == 1) {
        echo json_encode(array('code' => 1, 'msg' => 'success'));
        exit;
    } else {
        echo json_encode(array('code' => 0, 'msg' => 'failed'));
        exit;
    }
}
//PAYMENT VERIFY END


//PAYMENT VERIFY TOKEN START
if (isset($_POST['action']) && $_POST['action'] == "payment_verify_token") {

    $paymentcredentials = $db->get_row("SELECT * FROM `ss_paymentcredentials` WHERE forte_payment_token = '" . $_POST['payment_token'] . "' ");

    if (empty($paymentcredentials)) {
        $status = 0;
    } else {
        $status = 1;
    }

    $db->query("insert into ss_received_payment_txn_ids set 
        request_token='" . $_POST['request_token'] . "',  
        status='" . $status . "', 
        ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
        created_dt='" . date('Y-m-d H:i:s') . "' ");

    $id = $db->insert_id;

    if ($id > 0) {
        echo json_encode(array('code' => 1, 'msg' => 'Payment credentials added successfully'));
        exit;
    } else {
        echo json_encode(array('code' => 0, 'msg' => 'Payment credentials not added'));
        exit;
    }
}
//PAYMENT VERIFY TOKEN END




// PAYMENT PROCESS RESPONSE CODE START 
$json = @file_get_contents('php://input');
$data = json_decode($json, true);

if (count((array)$data['client_data']) >= 1) {
    $client_data = $data['client_data'];
} else {
    $client_data = null;
}

if (count((array)$data['payment_response']) >= 1) {
    $payment_response = $data['payment_response'];
} else {
    $payment_response = null;
}


// MANUAL PAYMENT  
if (isset($data['action']) && $data['action'] == 'manual_payment') {
    if (count((array)$data['client_data']) >= 1) {
        $client_data = $data['client_data'];
    } else {
        $client_data = null;
    }

    if (count((array)$data['payment_response']) >= 1) {
        $payment_response = $data['payment_response'];
    } else {
        $payment_response = null;
    }

    $family_data = $db->get_row("SELECT * FROM ss_family WHERE id ='" . trim($db->escape($client_data['family_id'])) . "'");

    $txn_summary = $db->query("insert into ss_txn_summary set  name='" . $client_data['firstName'] . "', email='" . $client_data['email'] . "', family_user_id='" . $family_data->user_id . "', phone='" . $client_data['phone'] . "', raw_data='" . str_replace("'", "", json_encode($payment_response)) . "', created_dt='" . date('Y-m-d H:i:s') . "' ");

    try {

        $transactionID  = (isset($data['transactionID']) && !empty($data['transactionID'])) ? $data['transactionID'] : null;
        $payment_transactions  = (isset($data['payment_response']) && !empty($data['payment_response'])) ? $data['payment_response'] : null;
        $payment_transactions_code = (isset($data['transactions_code']) && !empty($data['transactions_code'])) ? $data['transactions_code'] : null;
        $customertoken = (isset($data['customertoken']) && !empty($data['customertoken'])) ? $data['customertoken'] : null;
        $paymethodtoken = (isset($data['paymethodtoken']) && !empty($data['paymethodtoken'])) ? $data['paymethodtoken'] : null;
        $billing_address_id = (isset($data['billing_address_id']) && !empty($data['billing_address_id'])) ? $data['billing_address_id'] : null;
        $shipping_address_id = (isset($data['shipping_address_id']) && !empty($data['shipping_address_id'])) ? $data['shipping_address_id'] : null;
        $transactions = $payment_response;
        if (isset($client_data['amount']) && !empty($client_data['amount'])) {
            $full_amount = $client_data['amount'];
        }

        $db->query('BEGIN');

        $payment_credential = $db->get_row("SELECT * FROM ss_paymentcredentials WHERE forte_payment_token = '" . $paymethodtoken . "'");
        $credit_card_no = base64_decode($payment_credential->credit_card_no);
        $SchoolAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='CC' limit 1 ");
        $account_holder = $family_data->father_first_name . ' ' . $family_data->father_last_name;

        $created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
        INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
        INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
        WHERE t.user_type_code = 'UT00'  limit 1 ");



        if ((strtoupper($payment_transactions_code) == "A01" || strtoupper($payment_transactions_code) == "100" || strtoupper($payment_transactions_code) == "SUCCEEDED")) {

            $PaymentStatus = 1;

            $payment_txns = $db->query("insert into ss_payment_txns set session='" . $client_data['session'] . "', comments='" . $client_data['description'] . "', amount='" . $client_data['amount'] . "', automatic=0, payment_credentials_id='" . $payment_credential->id . "', payment_gateway='" . $data['payment_gateway'] . "', payment_status=1, is_payment_type=4, payment_unique_id='" . $transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', payment_date='" . $data['payment_date'] . "' ");
            $payment_txns_id = $db->insert_id;
            $is_payment_type = 4;
        } else {

            $PaymentStatus = 2;

            $payment_txns = $db->query("insert into ss_payment_txns set session='" . $client_data['session'] . "', comments='" . $client_data['description'] . "', amount='" . $client_data['amount'] . "', automatic=0, payment_credentials_id='" . $payment_credential->id . "', payment_gateway='" . $data['payment_gateway'] . "', payment_status=2,is_payment_type=7, payment_unique_id='" . $transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . str_replace("'", "", json_encode($payment_transactions)) . "', payment_date='" . $data['payment_date'] . "' ");
            $payment_txns_id = $db->insert_id;
            $is_payment_type = 7;
        }

        if ($payment_txns_id > 0) {
            $payment_accounts = $db->get_var("SELECT a.id FROM ss_payment_accounts a WHERE a.user_id = '" . $family_data->user_id . "'");

            if (empty($payment_accounts)) {
                $db->query("insert into ss_payment_accounts set account_holder='" . $account_holder . "', user_id = '" . $family_data->user_id . "', system_account = '1', created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $client_data['created_by_user_id'] . "'");
                $FamilyAccountId = $db->insert_id;
            } else {
                $FamilyAccountId = $payment_accounts;
            }

            if ($FamilyAccountId > 0) {
                $payment_amount_entry = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `is_force_payment`, `created_on`) VALUES ('Transfered - Family account to School account','" . $client_data['amount'] . "', '" . $FamilyAccountId . "','" . $SchoolAccountId . "', '1', '" . date('Y-m-d H:i:s') . "') ");
                $payment_account_entries_ids = $db->insert_id;

                if ($payment_account_entries_ids > 0) {
                    $students = $db->get_row("select GROUP_CONCAT(s.first_name,' ',s.last_name)  as name from ss_student as s INNER JOIN ss_student_session_map as m ON m.student_user_id = s.user_id INNER JOIN ss_user as u ON u.id = s.user_id where s.family_id ='" . $client_data['family_id'] . "' AND session_id='" . $client_data['session'] . "' ");
                    $child_name = $students->name;

                    if ($PaymentStatus == '1') { //---------Success Condition---------------//
                        $PaymentConfirmDecliendMSG = "Payment Confirmation";
                        $PaymentStatusMSG = "Payment Success";
                        $emailbody_support .= "Your payment for " . SCHOOL_NAME . " is Confirmed";
                        $message = str_replace("[AMOUNT]", $currency . $full_amount, PAYMENT_SUCCESS);

                        $body_email = "<p>We would like to inform you that Admin has deducted the amount from your Credit Card.</p>";
                        $paid_amount_change = "Paid Amount";
                        $payment_Date = "Payment Date";

                        $receipt_id = mt_rand();
                        include "../payment/receipt_pdf.php";

                        $dompdf = new Dompdf();
                        $dompdf->loadHtml($html);

                        // (Optional) Setup the paper size and orientation 
                        $dompdf->setPaper('Executive', 'Potrate');
                        // Render the HTML as PDF 
                        $dompdf->render();
                        $pathm = '../payment/invoice_and_pdf';
                        $filenamem = "/" . $receipt_id;
                        $outputm = $dompdf->output();
                        $fullpathReceipt = $receipt_id . 'receipt.pdf';
                        file_put_contents($pathm . $filenamem . 'receipt.pdf', $outputm);

                        $schedule_unique_id = "manual_U" . uniqid();
                        $invoice_update = $db->query("insert into ss_invoice set family_id='" . trim($db->escape($client_data['family_id'])) . "', receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d h:i:s') . "', schedule_unique_id='" . $schedule_unique_id . "', is_type=4, receipt_file_path = '" . $fullpathReceipt . "', amount='" . $full_amount . "', is_due='1', status = '1', reason='" . $client_data['description'] . "', pay_acc_enteries_id='" . $payment_account_entries_ids . "', created_at = '" . date('Y-m-d h:i:s') . "', created_by='" . $client_data['created_by_user_id'] . "'");
                        $last_receipt_id = $db->insert_id;

                        $db->query("insert into ss_invoice_info set payment_txn_id='" . $payment_txns_id . "', invoice_id = '" . $last_receipt_id . "', created_at = '" . date('Y-m-d h:i:s') . "'");
                        $last_invoice_info_id = $db->insert_id;
                    } elseif ($PaymentStatus == '2') { //---------------failed Condition------------// 

                        $emailbody_support .= "Your payment for " . SCHOOL_NAME . " was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                        $PaymentStatusMSG = "Payment Failed";
                        $PaymentConfirmDecliendMSG = "Payment - DECLINED";
                        $message = str_replace("[AMOUNT]", $currency . $full_amount, PAYMENT_FAILED);

                        $body_email = "We would like to inform you that the Admin has failed to manually deduct the amount you requested from your credit card.";
                        $paid_amount_change = "Amount";

                        $payment_Date = "Date";

                        $receipt_id = mt_rand();
                        include "../payment/receipt_pdf.php";

                        $dompdf = new Dompdf();
                        $dompdf->loadHtml($html);

                        // (Optional) Setup the paper size and orientation 
                        $dompdf->setPaper('Executive', 'Potrate');
                        // Render the HTML as PDF 
                        $dompdf->render();
                        $pathm = '../payment/invoice_and_pdf';
                        $filenamem = "/" . $receipt_id;
                        $outputm = $dompdf->output();
                        $fullpathReceipt = $receipt_id . 'receipt.pdf';
                        file_put_contents($pathm . $filenamem . 'receipt.pdf', $outputm);

                        $schedule_unique_id = "manual_U" . uniqid();
                        $invoice_update = $db->query("insert into ss_invoice set family_id='" . trim($db->escape($client_data['family_id'])) . "', receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d h:i:s') . "', schedule_unique_id='" . $schedule_unique_id . "', is_type=4, receipt_file_path = '" . $fullpathReceipt . "', amount='" . $full_amount . "', is_due='3', status = '1', reason='" . $client_data['description'] . "', pay_acc_enteries_id='" . $payment_account_entries_ids . "', created_at = '" . date('Y-m-d h:i:s') . "', created_by='" . $client_data['created_by_user_id'] . "'");
                        $last_receipt_id = $db->insert_id;

                        $db->query("insert into ss_invoice_info set payment_txn_id='" . $payment_txns_id . "', invoice_id = '" . $last_receipt_id . "', created_at = '" . date('Y-m-d h:i:s') . "'");
                        $last_invoice_info_id = $db->insert_id;
                    }

                    $db->query('COMMIT');
                    $emailbody_support = "Dear " . $family_data->father_first_name . ' ' . $family_data->father_last_name . " Assalamu-alaikum,<br>";
                    $emailbody_support .= $body_email;

                    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                                    <tr>
                                        <td colspan="2" style="text-align: center;">
                                                <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Receipt </u></div>
                                            </td>
                                        </tr>   
                                        <tr>
                                    <td colspan="2" style="text-align: left; padding-top:10px">
                                    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">


                                    <tr><td style="width: 25%;" class="color2">Parent Name:</td>
                                        <td style="width: 75%; text-align:left;">' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '
                                        </td></tr>

                                    <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                                        <td style="width: 75%; text-align:left;">' . internal_phone_check($family_data->father_phone) . '
                                        </td></tr>

                                    <tr><td style="width: 25%;" class="color2">Email:</td>
                                        <td style="width: 75%; text-align:left;"> ' . $family_data->primary_email . ' </td></tr>


                                    <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                        <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                                        </td></tr>
                                    
                                    <tr><td style="width: 25%;" class="color2">' . $paid_amount_change . ':</td> 
                                            <td style="width: 75%; text-align:left;"> ' . $currency . $client_data['amount'] . '</td></tr>

                                        <tr><td style="width: 25%;" class="color2">' . $payment_Date . ':</td>
                                            <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'), 't') . '
                                                </td></tr>
                                        <tr>
                                            <td style="width: 25%;" class="color2">Reason:</td>
                                                <td style="width: 75%; text-align:left;">' . $client_data['description'] . '
                                            </td>
                                        </tr>';
                    if (!empty($transactionID)) {
                        $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                                    <td style="width: 75%; text-align:left;">' . $transactionID . '
                                                    </td></tr>';
                    }

                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                        <td style="width: 75%; text-align:left;">' . $PaymentStatusMSG . '
                                        </td></tr>
                                    
                                        </table>
                                        </td>
                                    </tr>         
                                </table>';
                    $emailbody_support .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME;
                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                    //---------------Mail Send By MAil Service Start------------//
                    $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                    $email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME .  ' ' . $PaymentConfirmDecliendMSG;

                    $primary_email = '';
                    if (!empty($family_data->primary_email)) {
                        $primary_email = $family_data->primary_email;
                    }

                    $sec_email = '';
                    if (!empty($family_data->secondary_email)) {
                        $sec_email = $family_data->secondary_email;
                    }

                    $sec_gen_email = "";
                    if (!empty(SCHOOL_GEN_EMAIL)) {
                        $sec_gen_email = SCHOOL_GEN_EMAIL;
                    }

                    $mail_service_array = array(
                        'subject' => $email_subject,
                        'message' => $emailbody_support,
                        'request_from' => MAIL_SERVICE_KEY,
                        'attachment_file_name' => [],
                        'attachment_file' => [],
                        'to_email' => [$primary_email, $sec_email, $sec_gen_email],
                        'cc_email' => [],
                        'bcc_email' => []
                    );

                    if ($payment_txns_id > 0) {
                        //---------------Mail Send By MAil Service End------------//
                        $bulk_sms = $db->query("insert into ss_bulk_sms set session = '" . $client_data['session'] . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                        $message_id = $db->insert_id;
                        $father_phone = str_replace("-", "", $family_data->father_phone);
                        $bulk_sms_mobile = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");

                        mailservice($mail_service_array);
                    }


                    $dispMsg = "<p class='text-success'> Manual payment has been successfully received. <p>";
                    echo json_encode(array('code' => "1", 'msg' => $dispMsg));
                    exit;
                } else {
                    $db->query('ROLLBACK');
                    $dispMsg = "<p class='text-danger'> Process Failed. Please try again later. <p>";
                    echo json_encode(array('code' => "0", 'msg' => $dispMsg, '_error' => '6'));
                    exit;
                }
            } else {
                $db->query('ROLLBACK');
                $dispMsg = "<p class='text-danger'> Process Failed. Please try again later. <p>";
                echo json_encode(array('code' => "0", 'msg' => $dispMsg, '_error' => '5'));
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            $dispMsg = "<p class='text-danger'> Process Failed. Please try again later. <p>";
            echo json_encode(array('code' => "0", 'msg' => $dispMsg, '_error' => '1'));
            exit;
        }
    } catch (customException $e) {
        $db->query('ROLLBACK');
        CreateLog($_REQUEST, json_encode($e->errorMessage()));
        exit;
    }
}


// CRON SCHEDULE PAYMENT  
if (isset($data['action']) && $data['action'] == 'cron_schedule_payment_execute') {

    //CURRENT SESSION
    $current_session_row = $db->get_row("select * from ss_school_sessions where current = 1");
    $current_session = $current_session_row->id;

    if (isset($data['family_user_id']) && !empty($data['family_user_id']) && isset($data['schedule_payment_date']) && !empty($data['schedule_payment_date'])) {

        $family_data = $db->get_row("SELECT sfic.id,sfic.schedule_unique_id,sch_item_ids, sfic.schedule_payment_date,sfic.total_amount,sfic.cc_amount,sfic.wallet_amount, 
            sfic.schedule_status, sfic.family_id, f.user_id AS family_user_id, 
            f.father_first_name, f.father_last_name, f.father_phone, f.primary_email,
            f.secondary_email, f.billing_city, f.billing_post_code, f.forte_customer_token,pay.credit_card_no, 
            pay.forte_payment_token, pay.id AS payment_credential_id
            FROM `ss_payment_sch_item_cron` sfic
            INNER JOIN ss_family f ON f.id = sfic.family_id
            INNER JOIN ss_paymentcredentials pay ON pay.family_id = f.id
            WHERE schedule_payment_date = '" . $data['schedule_payment_date'] . "' AND family_user_id = '" . $data['family_user_id'] . "'  AND sfic.schedule_status = 0 AND sfic.session = '" . $current_session . "' AND pay.default_credit_card =1 AND is_approval='1';");

        $txn_summary = $db->query("insert into ss_txn_summary set  name='" . $family_data->father_first_name . "', email='" . $family_data->primary_email . "', phone='" . $family_data->father_phone . "', raw_data='" . json_encode($payment_response) . "', created_dt='" . date('Y-m-d H:i:s') . "' ");

        //--------------------------Family Virtual Wallet Ammount------------------//
        $FamilyAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `user_id`='" . $family_data->family_user_id . "' ORDER by id DESC limit 1 ");
        $FamilyAccountCredits = $db->get_results("SELECT `ent`.`id` ,`ent`.`amount`, `ent`.`debit_pay_account_id`, `ent`.`credit_pay_account_id` FROM `ss_payment_account_entries` `ent` WHERE (`ent`.`debit_pay_account_id`='" . $FamilyAccountId . "' OR  `ent`.`credit_pay_account_id`='" . $FamilyAccountId . "') ");
        $totalWalletAmount = 0;
        if (count((array)$FamilyAccountCredits) > 0) {

            foreach ($FamilyAccountCredits as $CreditsVal) {
                if ($CreditsVal->debit_pay_account_id == $FamilyAccountId) {
                    $debitAmount = $CreditsVal->amount;
                } else {
                    $debitAmount = '';
                }
                if ($CreditsVal->credit_pay_account_id == $FamilyAccountId) {
                    $creditAmount = $CreditsVal->amount;
                } else {
                    $creditAmount = '';
                }
                $creditAmountSum += $creditAmount;
                $debitAmountSum += $debitAmount;
                // $creditAmountSum - $debitAmountSum
                $totalWalletAmount = $creditAmountSum - $debitAmountSum;
            }

            if ($totalWalletAmount > 0) {
                $totalWalletAmount = $totalWalletAmount;
            } elseif ($totalWalletAmount <= 0) {
                $totalWalletAmount = 0;
            }
        }

        //-------------Check  customer token,payment token,total amount Start------------//
        if (!empty($family_data->forte_customer_token) && !empty($family_data->forte_payment_token) && !empty($family_data->total_amount)) {
            $customertoken = $family_data->forte_customer_token;
            $paymethodtoken = $family_data->forte_payment_token;

            //++++++++++++++++++++++++++++##-All Amount Debit From Credit Card-##++++++++++++++++++++++++++++++//
            if ($totalWalletAmount <= 0) {
                $AmountDebitFrom = "AllCC";
                $amount = $family_data->total_amount;

                $transactions =  (object) $payment_response;

                $full_amount = $currency . $amount;

                if (isset($transactions->response->response_code) && (strtoupper($transactions_code) == "A01" || strtoupper($transactions_code) == "100" || strtoupper($transactions_code) == "SUCCEEDED") && !empty(trim($transactions->transaction_id))) {
                    $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                    $response_code = $transactions->response->response_code;
                    $transactionID = $transactions->transaction_id;
                    $payment_status = 1;
                    $trxn_msg = 'Payment Successful';
                    $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $request_token . "'");
                } else {
                    $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                    $response_code = $transactions->response->response_code;
                    $transactionID = "";
                    $payment_status = 4;
                    $trxn_msg = 'Payment Failed';
                    $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $request_token . "'");
                }

                $student_fees_items = $db->query("update ss_payment_sch_item_cron set schedule_status ='" . $payment_status . "',wallet_amount ='0',cc_amount ='" . $amount . "',reason='" . $trxn_msg . "', payment_unique_id='" . $transactionID . "', payment_response_code='" . $response_code . "', payment_response='" . json_encode($transactions) . "', updated_at = '" . date('Y-m-d H:i:s') . "' where schedule_unique_id = '" . $family_data->schedule_unique_id . "' AND id='" . $family_data->id . "' ");

                if (!$student_fees_items) {
                    $return_resp = array('code' => "0", 'msg' => 'Family Id : ' . $family_data->family_id . ', Dear ' . $family_data->father_first_name . ' ' . $family_data->father_last_name . ' schedule payment Processed failed because data not insert in database');
                    CreateLog($_REQUEST, json_encode($return_resp));
                }
            }
            //++++++++++++++++++++++++++++##-All Amount Debit From Wallet-##++++++++++++++++++++++++++++++//

            elseif ($totalWalletAmount >= $family_data->total_amount) {
                $AmountDebitFrom = "AllWallet";
                $amount = $family_data->total_amount;
                $student_fees_items = $db->query("update ss_payment_sch_item_cron set wallet_amount ='" . $amount . "',cc_amount ='0',schedule_status ='1',reason='Payment Successful', updated_at = '" . date('Y-m-d H:i:s') . "' where schedule_unique_id = '" . $family_data->schedule_unique_id . "' AND id='" . $family_data->id . "' ");
                if (!$student_fees_items) {
                    $return_resp = array('code' => "0", 'msg' => 'Family Id : ' . $family_data->family_id . ', Dear ' . $family_data->father_first_name . ' ' . $family_data->father_last_name . ' schedule payment Processed failed because data not insert in database');
                    CreateLog($_REQUEST, json_encode($return_resp));
                }
            }
            //++++++++++++++++++++++++++++##-Amount Debit From Wallet And Credit Card-##++++++++++++++++++++++++++++++//

            elseif ($totalWalletAmount < $family_data->total_amount && $totalWalletAmount > 0) {
                $AmountDebitFrom = "WalletAndCC";
                $WalletAmmount = $totalWalletAmount;
                $amount = $family_data->total_amount - $totalWalletAmount;

                $transactions =  (object) $payment_response;

                $full_amount = $currency . $amount;

                if (isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01" && !empty(trim($transactions->transaction_id))) {
                    $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                    $response_code = $transactions->response->response_code;
                    $transactionID = $transactions->transaction_id;
                    $payment_status = 1;
                    $trxn_msg = 'Payment Successful';

                    $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $request_token . "'");
                } elseif (isset($transactions->response->response_code) && !empty(isset($transactions->response->response_code))) {
                    $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                    $response_code = $transactions->response->response_code;
                    $transactionID = "";
                    $payment_status = 4;
                    $trxn_msg = 'Payment Failed';
                    $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $request_token . "'");
                }

                $student_fees_items = $db->query("update ss_payment_sch_item_cron set wallet_amount ='" . $WalletAmmount . "',cc_amount ='" . $amount . "' ,schedule_status ='" . $payment_status . "',reason='" . $trxn_msg . "', payment_unique_id='" . $transactionID . "', payment_response_code='" . $response_code . "', payment_response='" . json_encode($transactions) . "', updated_at = '" . date('Y-m-d H:i:s') . "' where schedule_unique_id = '" . $family_data->schedule_unique_id . "' AND id='" . $family_data->id . "' ");
                if (!$student_fees_items) {
                    $return_resp = array('code' => "0", 'msg' => 'Family Id : ' . $family_data->family_id . ', Dear ' . $family_data->father_first_name . ' ' . $family_data->father_last_name . ' schedule payment Processed failed because data not insert in database');
                    CreateLog($_REQUEST, json_encode($return_resp));
                }
            }
            echo "success";
        } else {
            $return_resp = array('code' => "0", 'msg' => 'Family Id : ' . $family_data->family_id . ', Dear ' . $family_data->father_first_name . ' ' . $family_data->father_last_name . ' schedule payment Processed failed because payment credential wrong');
            CreateLog($_REQUEST, json_encode($return_resp));
        }
    }
}


// PAYMENT CREDIT CARD TOKEN START  
if (isset($data['action']) && $data['action'] == 'credit_card_add' || $data['action'] == 'credit_card_edit') {

    if (count((array)$data['client_data']) >= 1) {
        $client_data = $data['client_data'];
    } else {
        $client_data = null;
    }

    if (count((array)$data['payment_response']) >= 1) {
        $payment_response = $data['payment_response'];
    } else {
        $payment_response = null;
    }

    $txn_summary = $db->query("insert into ss_txn_summary set  name='" . $client_data['firstName'] . "', email='" . $client_data['email'] . "', phone='" . $client_data['phone'] . "', raw_data='" . json_encode($payment_response) . "', created_dt='" . date('Y-m-d H:i:s') . "' ");

    // $txn_summary = $db->query("insert into ss_txn_summary set  name='" . $client_data['firstName'] . "', email='" . $client_data['email'] . "', phone='" . $client_data['phone'] . "', raw_data='" . json_encode($client_data) . "', created_dt='" . date('Y-m-d H:i:s') . "' ");

    // die;
    //$db->query("BEGIN");

    try {

        $created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
                    INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
                    INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
                    WHERE t.user_type_code = 'UT00'  limit 1 ");

        $credit_card_type = base64_encode($client_data['credit_card_type']);
        $credit_card_no_db = base64_encode(substr(str_replace(' ', '', $client_data['credit_card_no']), -4));
        $credit_card_no = substr(str_replace(' ', '', $client_data['credit_card_no']), -4);
        $email_show_credit_card_no = "************" . substr(str_replace(' ', '', $client_data['credit_card_no']), -4);
        $expiry_month = $client_data['exp_month'];
        $expiry_year = $client_data['exp_year'];
        $cvv_no = base64_encode($client_data['cvv_no']);
        $exp_card_addon = $expiry_month . '/' . $expiry_year;
        $expiry_card = base64_encode($exp_card_addon);

        $transactionID  = (isset($data['transactionID']) && !empty($data['transactionID'])) ? $data['transactionID'] : null;
        $payment_transactionID  = (isset($data['transactionID']) && !empty($data['transactionID'])) ? $data['transactionID'] : null;
        $payment_transactions  = (isset($data['payment_response']) && !empty($data['payment_response'])) ? $data['payment_response'] : null;

        //$payment_transactions  = (isset($data['payment_transactions']) && !empty($data['payment_transactions'])) ? $data['payment_transactions'] : null;
        // $payment_transactions_code  = (isset($data['payment_transactions_code']) && !empty($data['payment_transactions_code'])) ? $data['payment_transactions_code'] : null;
        $payment_transactions_code = (isset($data['transactions_code']) && !empty($data['transactions_code'])) ? $data['transactions_code'] : null;
        $customertoken = (isset($data['customertoken']) && !empty($data['customertoken'])) ? $data['customertoken'] : null;
        $paymethodtoken = (isset($data['paymethodtoken']) && !empty($data['paymethodtoken'])) ? $data['paymethodtoken'] : null;
        $billing_address_id = (isset($data['billing_address_id']) && !empty($data['billing_address_id'])) ? $data['billing_address_id'] : null;
        $shipping_address_id = (isset($data['shipping_address_id']) && !empty($data['shipping_address_id'])) ? $data['shipping_address_id'] : null;
        $transactions = $payment_response;

        if (isset($client_data['decline_total_amount']) && !empty($client_data['decline_total_amount'])) {
            $full_amount = $client_data['decline_total_amount'];
        } elseif (isset($client_data['amount']) && !empty($client_data['amount'])) {
            $full_amount = $client_data['amount'];
        }

        $is_credit_already = $db->query("select * from ss_paymentcredentials where family_id='" . trim($db->escape($client_data['family_id'])) . "' AND credit_card_no='" . $credit_card_no_db . "' AND credit_card_exp='" . $expiry_card . "' AND credit_card_cvv='" . $cvv_no . "' AND default_credit_card =1 ");
        if ($is_credit_already) {
            $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Payment credentials already exist <p>');
            echo json_encode($return_resp);
            exit;
        }
        if (!empty($client_data['default']) && $client_data['default'] === "Yes") {
            $default = 1;
        } else {
            $default = 0;
        }
        $family_info = $db->get_row("select * from ss_family where id = '" . trim($db->escape($client_data['family_id'])) . "'");
        $family_data = $family_info;

        if (!empty($customertoken) && !empty($paymethodtoken)) {
            $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . trim($db->escape($client_data['family_id'])) . "'");
            if (isset($data['action']) &&  $data['action'] == 'credit_card_edit') {
                $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=0, credit_card_deleted=1 where id='" . trim($db->escape($client_data['payid'])) . "'");
                $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=0 where family_id='" . trim($db->escape($client_data['family_id'])) . "'");
            } else {
                if (!empty($client_data['default']) && $client_data['default'] === "Yes") {
                    $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=0 where family_id='" . trim($db->escape($client_data['family_id'])) . "'");
                }
            }

            $credit_card = $db->query("insert into ss_paymentcredentials set family_id='" . trim($db->escape($client_data['family_id'])) . "', credit_card_no='" . $credit_card_no_db . "', credit_card_type='" . $credit_card_type . "', credit_card_exp='" . $expiry_card . "',  default_credit_card='" . $default . "', forte_payment_token= '" . $paymethodtoken . "',  created_by_user_id='" . $created_user_id . "', created_on='" . date('Y-m-d H:i:s') . "' ");
            $credit_card_id = $db->insert_id;
            if ($credit_card_id > 0) {


                if (isset($client_data['is_reg_payment']) && $client_data['is_reg_payment'] == "1" && isset($client_data['default']) && $client_data['default'] === "Yes") {

                    if ((strtoupper($payment_transactions_code) == "A01" || strtoupper($payment_transactions_code) == "100" || strtoupper($payment_transactions_code) == "SUCCEEDED")) {

                        $PaymentStatus = 1;

                        $payment_txns = $db->query("insert into ss_payment_txns set session='" . $client_data['session'] . "', automatic=0, payment_credentials_id='" . $credit_card_id . "', payment_gateway='" . $data['payment_gateway'] . "', payment_status=1, is_payment_type = 1, payment_unique_id='" . $payment_transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', amount='" . $full_amount . "', payment_date='" . $data['payment_date'] . "', sunday_school_reg_id='" . $client_data['father_reg_id'] . "' ");
                        $payment_txns_id = $db->insert_id;
                    } else {

                        $PaymentStatus = 2;

                        $payment_txns = $db->query("insert into ss_payment_txns set session='" . $client_data['session'] . "', automatic=0, payment_credentials_id='" . $credit_card_id . "', payment_gateway='" . $data['payment_gateway'] . "', payment_status=2, payment_unique_id='" . $payment_transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', amount='" . $full_amount . "', payment_date='" . $data['payment_date'] . "' ");

                        $payment_txns_id = $db->insert_id;
                    }


                    if ($payment_txns_id > 0) {

                        $payment_res = $db->query("insert into ss_registration_fee_txns set family_id='" . trim($db->escape($client_data['family_id'])) . "', transaction_id='" . $payment_txns_id . "', amount='" . $full_amount . "', reg_payment_type='1',created_by_user_id='" . $created_user_id . "', created_at='" . date('Y-m-d H:i:s') . "', registration_id='" . $client_data['father_reg_id'] . "' ");

                        if ($db->query("update ss_family set is_paid_registration_fee='1' where id='" . trim($db->escape($client_data['family_id'])) . "' ")) {

                            $students = $db->get_row("select GROUP_CONCAT(s.first_name,' ',s.last_name)  as name from ss_student as s INNER JOIN ss_student_session_map as m ON m.student_user_id = s.user_id INNER JOIN ss_user as u ON u.id = s.user_id where s.family_id ='" . $family_info->id . "' AND session_id='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
                            $child_name = $students->name;

                            if ($PaymentStatus == '1') { //---------Success Condition---------------//
                                $PaymentConfirmDecliendMSG = "Payment Confirmation";
                                $PaymentStatusMSG = "Payment Success";
                                $emailbody_support .= "Your payment for " . SCHOOL_NAME . " is Confirmed";
                                $message = str_replace("[AMOUNT]", $currency . $full_amount, PAYMENT_SUCCESS);
                            } elseif ($PaymentStatus == '2') { //---------------failed Condition------------// 
                                $emailbody_support .= "Your payment for " . SCHOOL_NAME . " was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                                $PaymentStatusMSG = "Payment Failed";
                                $PaymentConfirmDecliendMSG = "Payment - DECLINED";
                                $message = str_replace("[AMOUNT]", $currency . $full_amount, PAYMENT_FAILED);
                            }

                            $receipt_id = mt_rand();
                            include "../payment/receipt_pdf.php";
                            $dompdf = new Dompdf();
                            $dompdf->loadHtml($html);
                            // (Optional) Setup the paper size and orientation 
                            $dompdf->setPaper('Executive', 'Potrate');
                            // Render the HTML as PDF 
                            $dompdf->render();
                            $pathR = '../payment/invoice_and_pdf';
                            $filenameR = "/" . $receipt_id;
                            $outputR = $dompdf->output();
                            $fullpathReceipt = $receipt_id . 'receipt.pdf';
                            file_put_contents($pathR . $filenameR . 'receipt.pdf', $outputR);

                            $schedule_unique_id = "reg_U" . uniqid();
                            $invoice_update = $db->query("insert into ss_invoice set family_id='" . $family_info->id . "', receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d h:i:s') . "', is_type = 1,  schedule_unique_id='" . $schedule_unique_id . "', receipt_file_path = '" . $fullpathReceipt . "', amount='" . $full_amount . "', is_due='1', status = '1', created_at = '" . date('Y-m-d h:i:s') . "', created_by='" . $created_user_id . "'");
                            $last_receipt_id = $db->insert_id;
                            $db->query("insert into ss_invoice_info set payment_txn_id='" . $payment_txns_id . "', invoice_id = '" . $last_receipt_id . "', created_at = '" . date('Y-m-d h:i:s') . "'");
                            $last_invoice_info_id = $db->insert_id;

                            $emailbody_support = "Dear " . $family_data->father_first_name . ' ' . $family_data->father_last_name . " Assalamu-alaikum,<br>";
                            if ($PaymentStatus == '1') { //---------Success Condition---------------//
                                $PaymentStatusMSG = "Payment Successful";
                                $emailbody_support .= "Your payment for " . SCHOOL_NAME . " is Confirmed";
                            } elseif ($PaymentStatus == '2') { //---------------failed Condition------------// 
                                $emailbody_support .= "Your payment for " . SCHOOL_NAME . " was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                                $PaymentStatusMSG = "Payment Failed";
                            }

                            $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                            <tr>
                                        <td colspan="2" style="text-align: center;">
                                            <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>PAYMENT RECEIPT</u></div>
                                        </td>
                                    </tr>   
                                    <tr>
                                <td colspan="2" style="text-align: left; padding-top:10px">
                                <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">
        
        
                                <tr><td style="width: 25%;" class="color2">Parent Name:</td>
                                    <td style="width: 75%; text-align:left;">' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '
                                    </td></tr>
        
                                <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                                    <td style="width: 75%; text-align:left;">' . internal_phone_check($family_data->father_phone) . '
                                    </td></tr>
        
                                <tr><td style="width: 25%;" class="color2">Email:</td>
                                    <td style="width: 75%; text-align:left;"> ' . $family_data->primary_email . ' </td></tr>
        
                                <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                                    <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
                                    </td></tr>';

                            if (!empty($transactionID)) {
                                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                    <td style="width: 75%; text-align:left;">' . $transactionID . '
                                    </td></tr>';
                            }

                            $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                                </td></tr>';


                            if (isset($family_data->total_amount) && !empty($family_data->total_amount)) {
                                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                    <td style="width: 75%; text-align:left;"> ' . $currency . $family_data->total_amount . '</td></tr>';
                            } elseif (isset($full_amount) && !empty($full_amount)) {
                                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                    <td style="width: 75%; text-align:left;"> ' . $currency . $full_amount . '</td></tr>';
                            }


                            $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Date:</td>
                                <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'), 't') . '
                                    </td></tr>';
                            if (!empty($PaymentStatusMSG)) {
                                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                            <td style="width: 75%; text-align:left;">' . $PaymentStatusMSG . '
                                            </td></tr>';
                            }
                            $emailbody_support .= '</table>
                                    </td>
                                </tr>         
                            </table>';
                            $emailbody_support .= "<br><br>" . BEST_REGARDS_TEXT;
                            $emailbody_support .= "<br" . ORGANIZATION_NAME;
                            $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                            $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                            //---------------Mail Send By MAil Service Start------------//
                            $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                            $email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME .  ' ' . $PaymentConfirmDecliendMSG;

                            $primary_email = '';
                            if (!empty($family_data->primary_email)) {
                                $primary_email = $family_data->primary_email;
                            }

                            $sec_email = '';
                            if (!empty($family_data->secondary_email)) {
                                $sec_email = $family_data->secondary_email;
                            }

                            $sec_gen_email = "";
                            if (!empty(SCHOOL_GEN_EMAIL)) {
                                $sec_gen_email = SCHOOL_GEN_EMAIL;
                            }

                            $mail_service_array = array(
                                'subject' => $email_subject,
                                'message' => $emailbody,
                                'request_from' => MAIL_SERVICE_KEY,
                                'attachment_file_name' => [],
                                'attachment_file' => [],
                                'to_email' => [$primary_email, $sec_email, $sec_gen_email],
                                'cc_email' => [],
                                'bcc_email' => []
                            );

                            if ($payment_txns_id > 0) {
                                //---------------Mail Send By MAil Service End------------//
                                $bulk_sms = $db->query("insert into ss_bulk_sms set session = '" . $client_data['session'] . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                                $message_id = $db->insert_id;
                                $father_phone = str_replace("-", "", $family_info->father_phone);
                                $bulk_sms_mobile = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");

                                mailservice($mail_service_array);
                            }
                        }
                    }
                } elseif (isset($client_data['privewschedule']) && $client_data['privewschedule'] == "Yes" && isset($client_data['default']) && $client_data['default'] === "Yes" && !empty($client_data['student_fees_items_ids'])) {

                    if ((strtoupper($payment_transactions_code) == "A01" || strtoupper($payment_transactions_code) == "100" || strtoupper($payment_transactions_code) == "SUCCEEDED")) {

                        $comments = "<strong>Reschedule Payments Status  </strong><br><strong>Preview Status : </strong> Decline  <br> <strong>Current Status : </strong> Success";
                        $student_fees_items_ids = explode("|", $client_data['student_fees_items_ids']);

                        $PaymentStatus = 1;

                        $payment_txns = $db->query("insert into ss_payment_txns set session='" . $client_data['session'] . "', automatic=0, payment_credentials_id='" . $credit_card_id . "', payment_gateway='" . $data['payment_gateway'] . "', payment_status=1, is_payment_type=3, payment_unique_id='" . $payment_transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', payment_date='" . $data['payment_date'] . "' ");
                        $payment_txns_id = $db->insert_id;

                        if ($payment_txns_id > 0) {
                            $schedule_payment_dates = "";
                            foreach ($student_fees_items_ids as $items_id) {
                                $student_fees_items = explode(',', $items_id);

                                if (count((array)$student_fees_items) == 1) {
                                    $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $items_id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i:s') . "'");

                                    $res = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items_id . "' , current_status=4, new_status=1, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "'  ");
                                } else {
                                    foreach ($student_fees_items as $id) {
                                        $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i:s') . "'");

                                        $res = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $id . "' , current_status=4, new_status=1, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "'  ");
                                    }
                                }

                                $db->query("update ss_student_fees_items set schedule_status = 1, updated_at = '" . date('Y-m-d H:i') . "' where id IN (" . $items_id . ") ");

                                $student_fees_items_row = $db->get_row("SELECT schedule_payment_date FROM `ss_student_fees_items` WHERE id IN (" . $items_id . ") AND session='" . $client_data['session'] . "' GROUP BY schedule_unique_id,schedule_status");
                                $schedule_payment_dates .= $student_fees_items_row->schedule_payment_date . ", ";
                            }
                        }
                    } else {

                        $PaymentStatus = 2;

                        $payment_txns = $db->query("insert into ss_payment_txns set session='" . $client_data['session'] . "', automatic=0, payment_credentials_id='" . $credit_card_id . "', payment_gateway='" . $data['payment_gateway'] . "', payment_status=2, payment_unique_id='" . $payment_transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', payment_date='" . $data['payment_date'] . "' ");
                        $payment_txns_id = $db->insert_id;
                    }

                    $get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc, new_registration_session, registration_page_termsncond,reg_form_term_cond_attach_url from ss_client_settings where status = 1");
                    if (!empty($get_email->new_registration_email_bcc)) {
                        $emails_bcc = explode(",", $get_email->new_registration_email_bcc);
                    }
                    if (!empty($get_email->new_registration_email_cc)) {
                        $emails_cc = explode(",", $get_email->new_registration_email_cc);
                    }


                    $emailbody_support = "Assalamualaikum ,<br>";
                    if ($PaymentStatus == '1') { //---------Success Condition---------------//
                        $PaymentConfirmDecliendMSG = "Payment Confirmation";
                        $PaymentStatusMSG = "Payment Success";
                        $emailbody_support .= "Your payment for " . SCHOOL_NAME . " is Confirmed";
                        $message = str_replace("[AMOUNT]", $currency . $full_amount, PAYMENT_SUCCESS);
                    } elseif ($PaymentStatus == '2') { //---------------failed Condition------------// 
                        $emailbody_support .= "Your payment for " . SCHOOL_NAME . " was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                        $PaymentStatusMSG = "Payment Failed";
                        $PaymentConfirmDecliendMSG = "Payment - DECLINED";
                        $message = str_replace("[AMOUNT]", $currency . $full_amount, PAYMENT_FAILED);
                    }

                    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                                <tr>
                                                <td colspan="2" style="text-align: center;">
                                                    <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Receipt </u></div>
                                                </td>
                                            </tr>   
                                            <tr>
                                        <td colspan="2" style="text-align: left; padding-top:10px">
                                        <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">


                                        <tr><td style="width: 25%;" class="color2">Parent Name:</td>
                                            <td style="width: 75%; text-align:left;">' . $family_info->father_first_name . ' ' . $family_info->father_last_name . '
                                            </td></tr>

                                        <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                                            <td style="width: 75%; text-align:left;">' . internal_phone_check($family_info->father_phone) . '
                                            </td></tr>

                                        <tr><td style="width: 25%;" class="color2">Email:</td>
                                            <td style="width: 75%; text-align:left;"> ' . $family_info->primary_email . ' </td></tr>

                                        <tr><td style="width: 25%;" class="color2"> Capture Payments Date:</td>
                                            <td style="width: 75%; text-align:left;">' . rtrim($schedule_payment_dates, ', ') . '
                                            </td></tr>

                                        <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                            <td style="width: 75%; text-align:left;">' . $email_show_credit_card_no . '
                                            </td></tr>
                                        
                                        <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                                <td style="width: 75%; text-align:left;"> ' . $currency . $client_data['decline_total_amount'] . '</td></tr>

                                            <tr><td style="width: 25%;" class="color2">Payment Date:</td>
                                                <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'), 't') . '
                                                    </td></tr>';
                    if (!empty($payment_transactionID)) {
                        $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                    <td style="width: 75%; text-align:left;">' . $payment_transactionID . '
                                    </td></tr>';
                    }

                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                            <td style="width: 75%; text-align:left;">' . $PaymentStatusMSG . '
                                            </td></tr>
                                        
                                            </table>
                                            </td>
                                        </tr>         
                                    </table>';
                    $emailbody_support .= "<br><br>" . BEST_REGARDS_TEXT;
                    $emailbody_support .= "<br" . ORGANIZATION_NAME;
                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                    //---------------Mail Send By MAil Service Start------------//
                    $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                    $email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME .  ' ' . $PaymentConfirmDecliendMSG;

                    $primary_email = '';
                    if (!empty($family_info->primary_email)) {
                        $primary_email = $family_info->primary_email;
                    }

                    $sec_email = '';
                    if (!empty($family_info->secondary_email)) {
                        $sec_email = $family_info->secondary_email;
                    }

                    $sec_gen_email = "";
                    if (!empty(SCHOOL_GEN_EMAIL)) {
                        $sec_gen_email = SCHOOL_GEN_EMAIL;
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
                        'to_email' => [$primary_email, $sec_email, $sec_gen_email],
                        'cc_email' => [$cc_email],
                        'bcc_email' => [$bcc_email]
                    );

                    if ($payment_txns_id > 0) {
                        //---------------Mail Send By MAil Service End------------//
                        $bulk_sms = $db->query("insert into ss_bulk_sms set session = '" . $client_data['session'] . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                        $message_id = $db->insert_id;
                        $father_phone = str_replace("-", "", $family_info->father_phone);
                        $bulk_sms_mobile = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");

                        mailservice($mail_service_array);
                    }
                } elseif (isset($client_data['privewschedule']) && $client_data['privewschedule'] == "No") {

                    $all_student_fees_items_list = $db->get_results("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_name, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, CONCAT('$',SUM(sfi.amount)) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
                        INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                        INNER JOIN ss_user u ON u.id = s.user_id
                        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                        INNER JOIN ss_family f ON f.id = s.family_id
                        INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                        WHERE s.family_id = '" . trim($db->escape($client_data['family_id'])) . "' AND sfi.session='" . $client_data['session'] . "' AND u.is_deleted = 0  AND u.is_locked=0  AND pay.default_credit_card =1 AND sfi.schedule_status = 4
                        GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.id desc", ARRAY_A);

                    $total_amount_email = 0;

                    $emailbody_admin = "Assalamualaikum,<br><br>";
                    $emailbody_admin .= "A new Credit Card was added to the following account.<br><br>";
                    $emailbody_admin .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">  
                                                <tr>
                                                    <td style="width: 25%;" class="color2">Name:</td>
                                                    <td style="width: 75%;">' . $family_info->father_first_name . ' ' . $family_info->father_last_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%;" class="color2">Phone:</td>
                                                    <td style="width: 75%;">' . internal_phone_check($family_info->father_phone) . '</td>
                                                </tr>     
                                                <tr>
                                                    <td style="width: 25%;" class="color2">Email:</td>
                                                    <td style="width: 75%;">' . $family_info->primary_email . '</td>
                                                </tr>  
                                            </table><br><br>';
                    $emailbody_admin .= "The user has updated the CC info and has declined to pay now. URGENT follow-up required<br><br>";
                    $emailbody_admin .= "Previously Declined payment Details,<br><br>";

                    $emailbody_admin .= '<table style="width: 100%;border-collapse: collapse;border: 1px solid black;">
                                            <tr>
                                                <th style="border: 1px solid black;text-align: center;">Schedule Date</th>
                                                <th style="border: 1px solid black;text-align: center;">Child(ren)</th>
                                                <th style="border: 1px solid black;text-align: center;">Amount</th>
                                            </tr>';
                    foreach ($all_student_fees_items_list as $row) {
                        $student_fees_items_ids .= $row['sch_item_id'] . "|";
                        $amout = str_replace("$", "", $row['final_amount']);
                        $total_amount_email += $amout;
                        $emailbody_admin .= '<tr>
                                                        <td style="border: 1px solid black;text-align: center;">' . Date('m/d/Y', strtotime($row['schedule_payment_date'])) . '</td>
                                                        <td style="border: 1px solid black;text-align: center;">' . $row['child_name'] . '</td>
                                                        <td style="border: 1px solid black;text-align: center;">' . $row['final_amount'] . '</td>
                                                        </tr>';
                    }
                    $emailbody_admin .= '<tr><td style="border:1px solid black;text-align:center;font-weight:bold;" colspan="2">Total Amount</td><td style="border: 1px solid black;text-align: center;">' . $currency . ($total_amount_email + 0) . '</td></tr></table>';


                    $emailbody_admin .= "<br><br>JazakAllah Khairan";
                    $emailbody_admin .= "<br>Islamic Center of Kansas";
                    $emailbody_admin .= "<br><br>For any comments or question, please send email to <a href='academy@ickansas.org'> academy@ickansas.org </a>";
                    $mailservice_request_from = MAIL_SERVICE_KEY;
                    $mail_service_array = array(
                        'subject' => CENTER_SHORTNAME . " " . SCHOOL_NAME . ' New Credit Card Added',
                        'message' => $emailbody_admin,
                        'request_from' => $mailservice_request_from,
                        'attachment_file_name' => '',
                        'attachment_file' => '',
                        'to_email' => [EMAIL_GENERAL],
                        'cc_email' => '',
                        'bcc_email' => ''
                    );
                    mailservice($mail_service_array);
                }




                $star = '************';
                $card_no = $star . substr(str_replace(' ', '', $client_data['credit_card_no']), -4);
                $emailbody_parents .= "Dear " . $family_info->father_first_name . ' ' . $family_info->father_last_name . "  Assalamu-alaikum,,<br><br>";
                $emailbody_parents .= "Your Payment information was updated for " . CENTER_SHORTNAME . " " . SCHOOL_NAME . ". This is the confirmation email<br><br>";
                $emailbody_parents .= "Your new payment credential information is,<br><br>";
                $emailbody_parents .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                                        <tr>
                                            <td colspan="2" style="text-align: center;">
                                                <div style="font-size: 18px; text-align:left;"><u> New Payment Credential Information</u></div>
                                            </td>
                                        </tr>   
                                        <tr>
                                        <td colspan="2" style="text-align: left; padding-top:30px">
                                        <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">
            
            
                                        <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                        <td style="width: 75%; text-align:left;">' . $card_no . '
                                        </td></tr>
                                        
                                        <tr><td style="width: 25%;" class="color2">Credit Card Expiry:</td>
                                        <td style="width: 75%; text-align:left;">' . $client_data['exp_month'] . '/' . $client_data['exp_year'] . '
                                        </td></tr>     
            
                                    </table>';
                $emailbody_parents .= "<br><br>" . BEST_REGARDS_TEXT;
                $emailbody_parents .= "<br>" . ORGANIZATION_NAME;
                $emailbody_parents .= "<br><br>For any comments or question, please send email to <a href='academy@ickansas.org'> academy@ickansas.org </a>";
                $emailbody_parents .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . CENTER_SHORTNAME . " " . SCHOOL_NAME;
                $mailservice_request_from = MAIL_SERVICE_KEY;
                $mail_service_array = array(
                    'subject' => CENTER_SHORTNAME . " " . SCHOOL_NAME . ' New Payment Credential',
                    'message' => $emailbody_parents,
                    'request_from' => $mailservice_request_from,
                    'attachment_file_name' => '',
                    'attachment_file' => '',
                    'to_email' => [$family_info->primary_email, $family_info->secondary_email],
                    'cc_email' => EMAIL_GENERAL,
                    'bcc_email' => ''
                );
                mailservice($mail_service_array);
                //$db->query('COMMIT');

                if (isset($data['transactions_data_request']) &&  $data['transactions_data_request'] == 1) {
                    $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $data['request_token'] . "'");
                }

                exit;
            } else {
                //$db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Payment credentials not added <p>');
                CreateLog($_REQUEST, json_encode($return_resp));
                echo json_encode($return_resp);
                exit;
            }
        } else {
            //$db->query('ROLLBACK');
            $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Payment credentials not added <p>');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    } catch (Exception $e) {
        $return_resp = json_encode($e);
        CreateLog($_REQUEST, $return_resp);
        //$db->query('ROLLBACK');
        exit;
    }
}
// PAYMENT CREDIT CARD TOKEN END 



if (isset($data['action']) && $data['action'] == 'student_register') {

    $country = $db->get_var("select country from ss_country where id='" . $client_data['country_id'] . "'");

    $created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
                    INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
                    INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
                    WHERE t.user_type_code = 'UT00'  limit 1 ");

    $swippedCardNumber = (isset($client_data['credit_card_no']) && !empty($client_data['credit_card_no'])) ? $client_data['credit_card_no'] : null;
    $swippedCardType = (isset($client_data['credit_card_type']) && !empty($client_data['credit_card_type'])) ? $client_data['credit_card_type'] : null;
    $cvv = (isset($client_data['cvv_no']) && !empty($client_data['cvv_no'])) ? $client_data['cvv_no'] : null;
    $account_no = (isset($client_data['account_no']) && !empty($client_data['account_no'])) ? $client_data['account_no'] : null;
    $routing_no = (isset($client_data['routing_no']) && !empty($client_data['routing_no'])) ? $client_data['routing_no'] : null;
    $account_type = (isset($client_data['account_type']) && !empty($client_data['account_type'])) ? $client_data['account_type'] : null;
    $userDataPaymentType = (isset($client_data['frequency_type']) && !empty($client_data['frequency_type'])) ? $client_data['frequency_type'] : null;

    if (isset($client_data['firstName']) && isset($client_data['lastName']) && isset($client_data['email']) && isset($client_data['phone'])) {
        $firstName = $client_data['firstName'];
        $lastName = $client_data['lastName'];
        $email = $client_data['email'];
        $phone = $client_data['phone'];
    } elseif (isset($client_data['credit_holder_first_name']) && isset($client_data['credit_holder_last_name']) && isset($client_data['credit_holder_email']) && isset($client_data['credit_holder_phone'])) {
        $firstName = $client_data['credit_holder_first_name'];
        $lastName = $client_data['credit_holder_last_name'];
        $email = $client_data['credit_holder_email'];
        $phone = $client_data['credit_holder_phone'];
    } else {
        $firstName = null;
        $lastName = null;
        $email = null;
        $phone = null;
    }

    $userDataEmail = $email;
    $userDataPhoneNo = str_replace("-", "", $phone);
    $userDataAmount = (isset($client_data['amount']) && !empty($client_data['amount'])) ? $client_data['amount'] : null;
    $userDataDonCatName = $firstName . ' ' . $lastName;
    $cardHolderFirstName = $firstName;
    $cardHolderLastName = $lastName;
    $coverTxnFees = (isset($client_data['cover_txn_fees']) && !empty($client_data['cover_txn_fees'])) ? $client_data['cover_txn_fees'] : 0;
    $txnCoverAmount = (isset($data['txnCoverAmount']) && !empty($data['txnCoverAmount'])) ? $data['txnCoverAmount'] : null;

    if (isset($client_data['address1']) && !empty($client_data['address1'])) {
        $address1 = $client_data['address1'];
    } else if (isset($client_data['address']) && !empty($client_data['address'])) {
        $address1 = $client_data['address'];
    } else if (isset($client_data['address_1']) && !empty($client_data['address_1'])) {
        $address1 = $client_data['address_1'];
    } else {
        $address1 = null;
    }

    if (isset($client_data['address2']) && !empty($client_data['address2'])) {
        $address2 = $client_data['address2'];
    } else if (isset($client_data['address_2']) && !empty($client_data['address_2'])) {
        $address2 = $client_data['address_2'];
    } else {
        $address2 = null;
    }

    if (isset($client_data['city']) && !empty($client_data['city'])) {
        $city = $client_data['city'];
    } else {
        $city = 'Overland Park';
    }

    if (isset($client_data['country_id']) && !empty($client_data['country_id'])) {
        $country_id = $client_data['country_id'];
    } else {
        $country_id = 1; //1 = USA
    }


    if (isset($client_data['state_id']) && !empty($client_data['state_id'])) {
        $state_id = $client_data['state_id'];
    } else if (isset($client_data['state']) && !empty($client_data['state'])) {
        $state_id = $client_data['state'];
    } else {
        $state_id = 1;  //1 = Kansas 
    }


    if (isset($client_data['zipcode']) && !empty($client_data['zipcode'])) {
        $zipcode = $client_data['zipcode'];
    } elseif (isset($client_data['post_code']) && !empty($client_data['post_code'])) {
        $zipcode = $client_data['post_code'];
    } else {
        $zipcode = null;
    }

    if (isset($client_data['system_ip']) && !empty($client_data['system_ip'])) {
        $system_ip = $client_data['system_ip'];
    } else {
        $system_ip = null;
    }

    $schedule_amount = $userDataAmount;
    $schedule_start_date = date('Y-m-d');
    $transactionID  = (isset($data['transactionID']) && !empty($data['transactionID'])) ? $data['transactionID'] : null;
    $transactions_code = (isset($data['transactions_code']) && !empty($data['transactions_code'])) ? $data['transactions_code'] : null;
    $customertoken = (isset($data['customertoken']) && !empty($data['customertoken'])) ? $data['customertoken'] : null;
    $paymethodtoken = (isset($data['paymethodtoken']) && !empty($data['paymethodtoken'])) ? $data['paymethodtoken'] : null;
    $billing_address_id = (isset($data['billing_address_id']) && !empty($data['billing_address_id'])) ? $data['billing_address_id'] : null;
    $shipping_address_id = (isset($data['shipping_address_id']) && !empty($data['shipping_address_id'])) ? $data['shipping_address_id'] : null;
    $countryCode = 'US';
    //$currency = 'usd';
    $transactions = $payment_response;
    $message = "";

    $txn_summary = $db->query("insert into ss_txn_summary set  name='" . $userDataDonCatName . "', email='" . $userDataEmail . "', phone='" . $userDataPhoneNo . "', raw_data='" . json_encode($payment_response) . "', created_dt='" . date('Y-m-d H:i:s') . "' ");

    $db->query("BEGIN");

    try {

        $isWaiting = $client_data['is_waiting'];
        if (isset($client_data['registerd_by']) && !empty($client_data['registerd_by'])) {
            $registerd_by = $client_data['registerd_by'];
        } else {
            $registerd_by = "Parent";
        }

        if (isset($client_data['internal_registration']) && !empty($client_data['internal_registration'])) {
            $internal_registration = $client_data['internal_registration'];
        } else {
            $internal_registration = "0";
        }

        // if (!empty($userDataPaymentType)  && !empty($userDataAmount)  && !empty($userDataDonCatName)) {

        $get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc,is_waiting, new_registration_session, registration_page_termsncond,reg_form_term_cond_attach_url from ss_client_settings where status = 1");
        if (!empty($get_email->new_registration_email_bcc)) {
            $emails_bcc = explode(",", $get_email->new_registration_email_bcc);
        }
        if (!empty($get_email->new_registration_email_cc)) {
            $emails_cc = explode(",", $get_email->new_registration_email_cc);
        }

        $parent1_email = $db->escape(trim($userDataEmail));
        $parent2_email = $db->escape(trim($client_data['parent2_email']));
        $which_is_primary_email = $db->escape(trim($client_data['which_is_primary_email']));

        if (isset($client_data['child1_dob']) && !empty(trim($client_data['child1_dob']))) {
            $child1_dob = "'" . date('Y-m-d', strtotime($client_data['child1_dob'])) . "'";
            $child1_dob_email = date('m/d/Y', strtotime($client_data['child1_dob']));
        } else {
            $child1_dob = "NULL";
            $child1_dob_email = "NULL";
        }

        if (isset($client_data['child2_dob']) && !empty(trim($client_data['child2_dob']))) {
            $child2_dob = "'" . date('Y-m-d', strtotime($client_data['child2_dob'])) . "'";
            $child2_dob_email = date('m/d/Y', strtotime($client_data['child2_dob']));
        } else {
            $child2_dob = "NULL";
            $child2_dob_email = "NULL";
        }


        if (isset($client_data['child3_dob']) && !empty(trim($client_data['child3_dob']))) {
            $child3_dob = "'" . date('Y-m-d', strtotime($client_data['child3_dob'])) . "'";
            $child3_dob_email = date('m/d/Y', strtotime($client_data['child3_dob']));
        } else {
            $child3_dob = "NULL";
            $child3_dob_email = "NULL";
        }


        if (isset($client_data['child4_dob']) && !empty(trim($client_data['child4_dob']))) {
            $child4_dob = "'" . date('Y-m-d', strtotime($client_data['child4_dob'])) . "'";
            $child4_dob_email = date('m/d/Y', strtotime($client_data['child4_dob']));
        } else {
            $child4_dob = "NULL";
            $child4_dob_email = "NULL";
        }



        if ($which_is_primary_email == "parent1") {
            $primary_email = $parent1_email;
            $secondary_email = $parent2_email;
            $primary_contact = 'Father';
        } else {
            $primary_email = $parent2_email;
            $secondary_email = $parent1_email;
            $primary_contact = 'Mother';
        }

        if (isset($userDataAmount) && !empty($userDataAmount)) {
            $fee = $userDataAmount;
        } else {
            $fee = 0.00;
        }



        $studentRegister =  $db->query("insert into ss_sunday_school_reg set 
                    father_first_name='" . $cardHolderFirstName . "', 
                    father_last_name='" . $cardHolderLastName . "', 
                    father_phone='" . $userDataPhoneNo . "',
                    father_email='" . $parent1_email . "',
                    mother_first_name='" . trim($db->escape($client_data['parent2_first_name'])) . "', 
                    mother_last_name='" . trim($db->escape($client_data['parent2_last_name'])) . "', 
                    mother_phone='" . trim($db->escape($client_data['parent2_phone'])) . "',
                    mother_email='" . $parent2_email . "',
                    primary_email='" . $primary_email . "',
                    secondary_email='" . $secondary_email . "',
                    primary_contact='" . $primary_contact . "',
                    address_1='" . trim($db->escape($address1)) . "',
                    address_2='" . trim($db->escape($address2)) . "',
                    class_session='" . trim($db->escape($client_data['class_session'])) . "',
                    city='" . trim($db->escape($city)) . "',
                    state='" . trim($db->escape($state_id)) . "',
                    country_id='" . trim($db->escape($country_id)) . "',
                    post_code='" . trim($db->escape($zipcode)) . "',
                    addition_notes='" . trim($db->escape($client_data['addition_notes'])) . "',
                    payment_method = 'c',
                    session = '" . $get_email->new_registration_session . "',
                    is_waiting='" . $isWaiting . "',
                    internal_registration='" . $internal_registration . "',
                    registerd_by='" . $registerd_by . "',
                    amount_received = '" . $db->escape($fee) . "',
                    created_on='" . date('Y-m-d H:i:s') . "',
                    updated_on='" . date('Y-m-d H:i:s') . "'");

        $reg_id = $db->insert_id;

        if ($reg_id > 0) {

            if ($client_data['child1_first_name'] != '') {

                $data = $db->query("insert into ss_sunday_sch_req_child set 
                            sunday_school_reg_id='" . $reg_id . "',
                            first_name='" . trim($client_data['child1_first_name']) . "',
                            last_name='" . trim($client_data['child1_last_name']) . "',
                            dob=" . $child1_dob . ",
                            gender='" . trim($client_data['child1_gender']) . "',
                            allergies='" . trim($client_data['child1_allergies']) . "',
                            school_grade = '" . $client_data['child1_grade'] . "',
                            created_on='" . date('Y-m-d H:i:s') . "',
                            updated_on='" . date('Y-m-d H:i:s') . "'");
            }

            if (isset($client_data['stu_uniq_ids']) && !empty($client_data['stu_uniq_ids'])) {
                $stu_uniq_ids = explode(",", ltrim($client_data['stu_uniq_ids'], ','));

                foreach ($stu_uniq_ids as $uniq_id) {

                    if ($client_data['child' . $uniq_id . '_first_name'] != '') {
                        $child_first_name = trim($client_data['child' . $uniq_id . '_first_name']);
                        $child_last_name = trim($client_data['child' . $uniq_id . '_last_name']);

                        if (!empty(trim($client_data['child' . $uniq_id . '_dob']))) {
                            $child_dob =  "'" . date('Y-m-d', strtotime($client_data['child' . $uniq_id . '_dob'])) . "'";
                            $child_dob_email = date('m/d/Y', strtotime($client_data['child' . $uniq_id . '_dob']));
                        } else {
                            $child_dob = "NULL";
                            $child_dob_email = "NULL";
                        }

                        $child_gender = $client_data['child' . $uniq_id . '_gender'];
                        $child_allergies = $client_data['child' . $uniq_id . '_allergies'];
                        $child_grade = $client_data['child' . $uniq_id . '_grade'];
                    }


                    $data = $db->query("insert into ss_sunday_sch_req_child set 
                                sunday_school_reg_id='" . $reg_id . "',
                                first_name='" . $child_first_name . "',
                                last_name='" . $child_last_name . "',
                                dob=" . $child_dob . ",
                                gender='" . $child_gender . "',
                                school_grade = '" . $child_grade . "',
                                allergies='" . $child_allergies . "',
                                created_on='" . date('Y-m-d H:i:s') . "',
                                updated_on='" . date('Y-m-d H:i:s') . "'");
                }
            } else {

                if ($client_data['child2_first_name'] != '') {

                    $data = $db->query("insert into ss_sunday_sch_req_child set 
                                sunday_school_reg_id='" . $reg_id . "',
                                first_name='" . trim($client_data['child2_first_name']) . "',
                                last_name='" . trim($client_data['child2_last_name']) . "',
                                dob=" . $child2_dob . ",
                                gender='" . trim($client_data['child2_gender']) . "',
                                allergies='" . trim($client_data['child2_allergies']) . "',
                                school_grade = '" . $client_data['child2_grade'] . "',
                                created_on='" . date('Y-m-d H:i:s') . "',
                                updated_on='" . date('Y-m-d H:i:s') . "'");
                }

                if ($client_data['child3_first_name'] != '') {

                    $data = $db->query("insert into ss_sunday_sch_req_child set 
                                sunday_school_reg_id='" . $reg_id . "',
                                first_name='" . trim($client_data['child3_first_name']) . "',
                                last_name='" . trim($client_data['child3_last_name']) . "',
                                dob=" . $child3_dob . ",
                                gender='" . trim($client_data['child3_gender']) . "',
                                allergies='" . trim($client_data['child3_allergies']) . "',
                                school_grade = '" . $client_data['child3_grade'] . "',
                                created_on='" . date('Y-m-d H:i:s') . "',
                                updated_on='" . date('Y-m-d H:i:s') . "'");
                }


                if ($client_data['child4_first_name'] != '') {

                    $data = $db->query("insert into ss_sunday_sch_req_child set 
                                sunday_school_reg_id='" . $reg_id . "',
                                first_name='" . trim($client_data['child4_first_name']) . "',
                                last_name='" . trim($client_data['child4_last_name']) . "',
                                dob=" . $child4_dob . ",
                                gender='" . trim($client_data['child4_gender']) . "',
                                allergies='" . trim($client_data['child4_allergies']) . "',
                                school_grade = '" . $client_data['child4_grade'] . "',
                                created_on='" . date('Y-m-d H:i:s') . "',
                                updated_on='" . date('Y-m-d H:i:s') . "'");
                }
            }

            $cc_no = substr($swippedCardNumber, -4);
            $encoded_cc_no = base64_encode($cc_no);
            $credit_card_type = base64_encode($swippedCardType);
            $credit_card_exp = base64_encode($client_data['exp_month'] . '-' . $client_data['exp_year']);
            $bank_acc_no = '';
            $routing_no = '';

            $db->query("insert into ss_sunday_sch_payment set sunday_sch_req_id='" . $reg_id . "', credit_card_type='" . $credit_card_type . "', credit_card_no='" . $encoded_cc_no . "',
                                credit_card_exp='" . $credit_card_exp . "', credit_card_cvv='', bank_acc_no='" . $bank_acc_no . "',
                                routing_no='" . $routing_no . "'");
            $sql_ret = $db->insert_id;

            if ($sql_ret > 0) {

                if (isset($customertoken) && isset($paymethodtoken)) {

                    $process = false;
                    if ($isWaiting == 1) {
                        $process = true;
                        $ispaid = 0;
                    } else {
                        if (isset($transactions_code) &&  (strtoupper($transactions_code) == "A01" || strtoupper($transactions_code) == "100" || strtoupper($transactions_code) == "SUCCEEDED") && !empty(trim($transactionID)) && $reg_id > 0 && $sql_ret > 0) {

                            $trxn_msg = 'Payment Sucessful';

                            if (isset($internal_registration) && $internal_registration == 1) {
                                $pay_txns_status = 1;
                            } else {
                                $pay_txns_status = 2;
                            }

                            $comments = "Registration fees and registration id " . $reg_id . " ";
                            $db->query("insert into ss_payment_txns set  payment_response_code='" . $transactions_code . "', sunday_school_reg_id='" . $reg_id . "', session = '" . $client_data['session'] . "',  payment_gateway='" . $data['payment_gateway'] . "', payment_status=1,  payment_unique_id='" . $transactionID . "', comments='" . $comments . "', payment_response='" . json_encode($transactions) . "', amount='" . $fee . "', payment_date='" . date('Y-m-d H:i:s') . "', is_payment_type='" . $pay_txns_status . "'");

                            $payment_txns_id = $db->insert_id;

                            $db->query("insert into ss_invoice_info set payment_txn_id='" . $payment_txns_id . "', created_at = '" . date('Y-m-d h:i:s') . "'");
                            $last_invoice_info_id = $db->insert_id;

                            if ($sql_ret > 0 && $payment_txns_id > 0 && $reg_id > 0 && $last_invoice_info_id > 0) {
                                $process = true;
                                $ispaid = 1;
                            } else {
                                $return_resp = json_encode(array('code' => "0", 'msg' => 'Registration failed because payment details incorrectly', '_errpos' => 1));
                                CreateLog($_REQUEST, $return_resp);
                                $db->query('ROLLBACK');
                                exit;
                            }
                        } else {
                            $return_resp = json_encode(array('code' => "0", 'msg' => 'Registration failed because payment details incorrectly', '_errpos' => 13));
                            CreateLog($_REQUEST, $return_resp);
                            $db->query('ROLLBACK');
                            exit;
                        }
                    }


                    if ($process == true && $db->query('COMMIT') !== false) {

                        if (isset($data['transactions_data_request']) &&  $data['transactions_data_request'] == 1) {
                            $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $data['request_token'] . "'");
                        }

                        $db->query("update ss_sunday_school_reg set is_paid='" . $ispaid . "', forte_customer_token='" . $customertoken . "', updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $reg_id . "'");

                        $db->query("update ss_sunday_sch_payment set forte_payment_token='" . $paymethodtoken . "' where id = '" . $sql_ret . "'");

                        $comments = "Registration fees and registration id " . $reg_id . " ";


                        if (!empty($state_id)) {
                            $state_name = $db->get_var("SELECT state FROM ss_state WHERE id='" . $state_id . "' AND is_active=1 ");
                        } else {
                            $state_name = "";
                        }




                        $emailbody = '<table style="border:0;font-family: Verdana, Geneva, sans-serif;" cellpadding="5"><tbody>
                                    <tr>
                                    <td colspan="4"> Dear ' . $cardHolderFirstName . ' ' . $cardHolderLastName . ' Assalamu-alaikum<br>
                                    <br>
                                    Thank you for registering for  ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' ' . $client_data['session_text'] . '. The staff will reach out to you if any other info is needed.<br>
                                    <br>
                                    Please feel free to contact us at <a href="mailto:' . SCHOOL_GEN_EMAIL . '" target="_blank">' . SCHOOL_GEN_EMAIL . '</a> with any questions. JazakAllah Khair<br>
                                    <br></td>
                                    </tr>
                                    <tr>
                                    <td colspan="4"><table style="border-collapse:collapse;width:100%" cellpadding="5">
                                    <tbody>';


                        $emailbody .= '<tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999"><strong>1st Parent Name</strong></td>
                                    <td style="border:solid 1px #999">' . $firstName . ' ' . $lastName . '</td>
                                    <td style="border:solid 1px #999"><strong>1st Parent Phone</strong></td>
                                    <td style="border:solid 1px #999">' . internal_phone_check($phone) . '</td>
                                    </tr>';
                        if (!empty($client_data['parent2_first_name'])) {
                            $emailbody .= '<tr style="border:solid 1px #999">
                                        <td style="border:solid 1px #999"><strong>2nd Parent Name</strong></td>
                                        <td style="border:solid 1px #999">' . $client_data['parent2_first_name'] . ' ' . $client_data['parent2_last_name'] . '</td>
                                        <td style="border:solid 1px #999"><strong>2nd Parent Phone</strong></td>
                                        <td style="border:solid 1px #999">' . $client_data['parent2_phone'] . '</td>
                                        </tr>';
                        }
                        $emailbody .= '<tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999"><strong>1st Parent Email</strong></td>
                                    <td style="border:solid 1px #999">' . $parent1_email . '</td>';

                        if (!empty($client_data['parent2_first_name'])) {
                            $emailbody .= '<td style="border:solid 1px #999"><strong>2nd Parent Email</strong></td>
                                    <td style="border:solid 1px #999">' . $parent2_email . '</td>';
                        } else {
                            $emailbody .= '<td style="border:solid 1px #999;"></td>
                                        <td style="border:solid 1px #999;"></td>';
                        }
                        $emailbody .= '</tr>
                                    <tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999"><strong>Address 1</strong></td>
                                    <td style="border:solid 1px #999">' . $address1 . '</td>
                                    <td style="border:solid 1px #999"><strong>Address 2</strong></td>
                                    <td style="border:solid 1px #999">' . $address2 . '</td>
                                    </tr>
                                    <tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999"><strong>City</strong></td>
                                    <td style="border:solid 1px #999">' . $city . '</td>
                                    <td style="border:solid 1px #999"><strong>State</strong></td>
                                    <td style="border:solid 1px #999">' . $state_name . '</td>
                                    </tr>
                                    <tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999"><strong>Country</strong></td>
                                    <td style="border:solid 1px #999">' . $country . '</td>
                                    <td style="border:solid 1px #999"><strong>Zipcode</strong></td>
                                    <td style="border:solid 1px #999">' . $zipcode . '</td>
                                    </tr>';
                        if (!empty($client_data['child1_first_name'])) {

                            $emailbody .= '<tr style="border:solid 1px #999">
                                        <td style="border:solid 1px #999;width:20%"><strong>Child 1 Name</strong></td>
                                        <td style="border:solid 1px #999;width:30%">' . $client_data['child1_first_name'] . ' ' . $client_data['child1_last_name'] . '</td>
                                        <td style="border:solid 1px #999;width:20%"><strong>Child 1 Gender</strong></td>
                                        <td style="border:solid 1px #999;width:30%">' . (trim($client_data['child1_gender']) == "f" ? "Female" : "Male") . '</td>
                                        </tr>
                                        <tr style="border:solid 1px #999">
                                        <td style="border:solid 1px #999"><strong>Child 1 DoB</strong></td>
                                        <td style="border:solid 1px #999">' . my_date_changer($child1_dob_email) . '</td>
                                        <td style="border:solid 1px #999"><strong>School Grade</strong></td>
                                        <td style="border:solid 1px #999">' . $client_data['child1_grade'] . '</td>
                                        </tr>
                    
                                        <tr style="border:solid 1px #999">
                                        <td style="border:solid 1px #999"><strong>Child 1 Allergies</strong></td>
                                        <td style="border:solid 1px #999">' . $client_data['child1_allergies'] . '</td>
                                        <td style="border:solid 1px #999;"></td>
                                        <td style="border:solid 1px #999;"></td>
                                        </tr>';
                        }

                        if (isset($client_data['stu_uniq_ids']) && !empty($client_data['stu_uniq_ids'])) {
                            $stu_uniq_ids = explode(",", ltrim($client_data['stu_uniq_ids'], ','));
                            $i = 1;
                            foreach ($stu_uniq_ids as $uniq_id) {

                                $i++;

                                if ($client_data['child' . $uniq_id . '_first_name'] != '') {
                                    $child_first_name = trim($client_data['child' . $uniq_id . '_first_name']);
                                    $child_last_name = trim($client_data['child' . $uniq_id . '_last_name']);

                                    if (!empty(trim($client_data['child' . $uniq_id . '_dob']))) {
                                        $child_dob =  "'" . date('Y-m-d', strtotime($client_data['child' . $uniq_id . '_dob'])) . "'";
                                        $child_dob_email = date('m/d/Y', strtotime($client_data['child' . $uniq_id . '_dob']));
                                    } else {
                                        $child_dob = "NULL";
                                        $child_dob_email = "NULL";
                                    }

                                    $child_gender = $client_data['child' . $uniq_id . '_gender'];
                                    $child_allergies = $client_data['child' . $uniq_id . '_allergies'];
                                    $child_grade = $client_data['child' . $uniq_id . '_grade'];
                                }
                                $next_year = date('Y') + 1;
                                $final_year = date('Y') - $next_year;
                                $emailbody .= '<tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999;width:20%"><strong>Child ' . $i . ' Name</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . $child_first_name . ' ' . $child_last_name . '</td>
                                            <td style="border:solid 1px #999;width:20%"><strong>Child ' . $i . ' Gender</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . (trim($child_gender) == "f" ? "Female" : "Male") . '</td>
                                            </tr>
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child ' . $i . ' DoB</strong></td>
                                            <td style="border:solid 1px #999">' . my_date_changer($child_dob_email)  . '</td>
                                            <td style="border:solid 1px #999"><strong>Child ' . $i . ' School Grade</strong></td>
                                            <td style="border:solid 1px #999">' . $child_grade . '</td>
                                            </tr>
                        
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child ' . $i . ' Allergies</strong></td>
                                            <td style="border:solid 1px #999">' . $child_allergies . '</td>
                                            <td style="border:solid 1px #999;"></td>
                                            <td style="border:solid 1px #999;"></td>
                                            </tr>';
                            }
                        } else {

                            if (!empty($client_data['child2_first_name'])) {

                                $emailbody .= '<tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999;width:20%"><strong>Child 2 Name</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . $client_data['child2_first_name'] . ' ' . $client_data['child2_last_name'] . '</td>
                                            <td style="border:solid 1px #999;width:20%"><strong>Child 2 Gender</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . (trim($client_data['child2_gender']) == "f" ? "Female" : "Male") . '</td>
                                            </tr>
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child 2 DoB</strong></td>
                                            <td style="border:solid 1px #999">' . my_date_changer($child2_dob_email) . '</td>
                                            <td style="border:solid 1px #999"><strong>School Grade</strong></td>
                                            <td style="border:solid 1px #999">' . $client_data['child2_grade'] . '</td>
                                            </tr>
                        
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child 2 Allergies</strong></td>
                                            <td style="border:solid 1px #999">' . $client_data['child2_allergies'] . '</td>
                                            <td style="border:solid 1px #999;"></td>
                                            <td style="border:solid 1px #999;"></td>
                                            </tr>';
                            }


                            if (!empty($client_data['child3_first_name'])) {

                                $emailbody .= '<tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999;width:20%"><strong>Child 3 Name</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . $client_data['child3_first_name'] . ' ' . $client_data['child3_last_name'] . '</td>
                                            <td style="border:solid 1px #999;width:20%"><strong>Child 3 Gender</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . (trim($client_data['child3_gender']) == "f" ? "Female" : "Male") . '</td>
                                            </tr>
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child 3 DoB</strong></td>
                                            <td style="border:solid 1px #999">' . my_date_changer($child3_dob_email) . '</td>
                                            <td style="border:solid 1px #999"><strong>School Grade</strong></td>
                                            <td style="border:solid 1px #999">' . $client_data['child3_grade'] . '</td>
                                            </tr>
                        
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child 3 Allergies</strong></td>
                                            <td style="border:solid 1px #999">' . $client_data['child3_allergies'] . '</td>
                                            <td style="border:solid 1px #999;"></td>
                                            <td style="border:solid 1px #999;"></td>
                                            </tr>';
                            }


                            if (!empty($client_data['child4_first_name'])) {

                                $emailbody .= '<tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999;width:20%"><strong>Child 4 Name</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . $client_data['child4_first_name'] . ' ' . $client_data['child4_last_name'] . '</td>
                                            <td style="border:solid 1px #999;width:20%"><strong>Child 4 Gender</strong></td>
                                            <td style="border:solid 1px #999;width:30%">' . (trim($client_data['child4_gender']) == "f" ? "Female" : "Male") . '</td>
                                            </tr>
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child 4 DoB</strong></td>
                                            <td style="border:solid 1px #999">' . my_date_changer($child4_dob_email) . '</td>
                                            <td style="border:solid 1px #999"><strong>School Grade</strong></td>
                                            <td style="border:solid 1px #999">' . $client_data['child4_grade'] . '</td>
                                            </tr>
                        
                                            <tr style="border:solid 1px #999">
                                            <td style="border:solid 1px #999"><strong>Child 4 Allergies</strong></td>
                                            <td style="border:solid 1px #999">' . $client_data['child4_allergies'] . '</td>
                                            <td style="border:solid 1px #999;"></td>
                                            <td style="border:solid 1px #999;"></td>
                                            </tr>';
                            }
                        }

                        $emailbody .= '
                                    <tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999"><strong>Payment Method</strong></td>
                                    <td style="border:solid 1px #999">Credit Card</td>';
                        if ($isWaiting == 0) {
                            $emailbody .= '<td style="border:solid 1px #999"><strong>Registration Fee</strong></td><td style="border:solid 1px #999">' . $currency . $fee . '</td>';
                        }
                        $emailbody .= '</tr>';

                        $emailbody .= '<tr style="border:solid 1px #999">
                                    <td style="border:solid 1px #999;"><strong>Last 4 digits of Credit Card</strong></td>
                                    <td style="border:solid 1px #999;">' . substr($client_data['credit_card_no'], -4) . '</td>';
                        if ($isWaiting == 0) {
                            $emailbody .= '<td style="border:solid 1px #999;"><strong>Transaction ID</strong></td><td style="border:solid 1px #999;">' . $transactionID . '</td>';
                        }
                        $emailbody .= '</tr>';

                        if ($isWaiting == 0) {
                            $reg_status = "Success";
                        } else {
                            $reg_status = "Waiting";
                        }
                        $emailbody .= '<tr style="border:solid 1px #999"><td style="border:solid 1px #999;"><strong>Registration Status</strong></td><td style="border:solid 1px #999;">' . $reg_status . '</td></tr>';

                        $emailbody .= '
                                    </tbody>
                                    </table>';
                        $emailbody .= $get_email->registration_page_termsncond;
                        $emailbody .= '<br>
                                    <br>
                                    ' . BEST_REGARDS_TEXT . '<br>
                                    ' . ORGANIZATION_NAME . ' Team</td>
                                    </tr>
                                    </tbody>
                                    </table>';

                        $attachmentFiles = SITEURL . 'email_pdf/student-reg-term-condition-1631095148.pdf';
                        $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody));
                        $email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' - New registration';
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
                        $targetUrl = SITEURL . 'thankyou.php';
                        echo json_encode(array('code' => "1", 'msg' => 'Student successfully registered. We are redirecting you to payment page soon.', 'targeturl' => $targetUrl));
                        exit;
                    } else {
                        $return_resp = json_encode(array('code' => "0", 'msg' => 'Registration failed because details incorrectly', '_errpos' => 1));
                        CreateLog($_REQUEST, $return_resp);
                        $db->query('ROLLBACK');
                        exit;
                    }
                } else {
                    if (isset($payment_response['response']['response_desc'])) {
                        $msg = $payment_response['response']['response_desc'];
                    } else {
                        $msg = "Something Went Wrong, Please Try Again.";
                    }

                    $return_resp = json_encode(array('code' => "0", 'msg' => '' . $msg . ''));
                    CreateLog($_REQUEST, $return_resp);
                    $db->query('ROLLBACK');
                    exit;
                }
            } else {
                $return_resp = json_encode(array('code' => "0", 'msg' => 'Registration failed.', '_errpos' => 12));
                CreateLog($_REQUEST, $return_resp);
                $db->query('ROLLBACK');
            }
        } else {
            $return_resp = json_encode(array('code' => "0", 'msg' => 'Registration failed.', '_errpos' => 13));
            CreateLog($_REQUEST, $return_resp);
            $db->query('ROLLBACK');
        }

        // }else{
        //     $return_resp = json_encode(array('code' => 0, 'msg' => 'Your credit card details could not be processed. Please try again', '_errpos' => 4));
        //     CreateLog($_REQUEST, $return_resp);
        //     $db->query('ROLLBACK');
        //     exit;
        // }


    } catch (Exception $e) {
        $return_resp = json_encode($e);
        CreateLog($_REQUEST, $return_resp);
        $db->query('ROLLBACK');
        exit;
    }
}
// PAYMENT PROCESS RESPONSE CODE END 


//REFUND PAYMENT START
if (isset($data['action']) && $data['action'] == 'refund_payment') {
    if (count((array)$data['client_data']) >= 1) {
        $client_data = $data['client_data'];
    } else {
        $client_data = null;
    }

    if (count((array)$data['payment_response']) >= 1) {
        $payment_response = $data['payment_response'];
    } else {
        $payment_response = null;
    }

    $family_data = $db->get_row("SELECT * FROM ss_family WHERE id ='" . $client_data['family_id'] . "' ");

    $txn_summary = $db->query("insert into ss_txn_summary set  name='" . $client_data['firstName'] . "', email='" . $client_data['email'] . "', family_user_id='" . $family_data->user_id . "', phone='" . $client_data['phone'] . "', payment_status = 1, raw_data='" . json_encode($payment_response) . "', created_dt='" . date('Y-m-d H:i:s') . "' ");


    try {
        $transactionID  = (isset($data['transactionID']) && !empty($data['transactionID'])) ? $data['transactionID'] : null;
        $payment_transactions  = (isset($data['payment_response']) && !empty($data['payment_response'])) ? $data['payment_response'] : null;
        $payment_transactions_code = (isset($data['transactions_code']) && !empty($data['transactions_code'])) ? $data['transactions_code'] : null;
        $transactions = $payment_response;
        if (isset($client_data['refund_amount']) && !empty($client_data['refund_amount'])) {
            $refund_amount = $client_data['refund_amount'];
        }

        $db->query('BEGIN');
        if (!empty($client_data['payment_credentials_id'])) {
            $payment_credential_id = "payment_credentials_id='" . $client_data['payment_credentials_id'] . "',";
        }

        $payment_credential = $db->get_row("SELECT * FROM ss_paymentcredentials WHERE id = '" . $payment_credential_id . "'");
        $payment_trns = $db->get_row("SELECT schpay.credit_card_no, reg.id FROM ss_sunday_school_reg reg INNER JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.id INNER JOIN ss_sunday_sch_payment schpay ON schpay.sunday_sch_req_id = reg.id");

        if (!empty($payment_credential->credit_card_no)) {
            $credit_card_no = base64_decode($payment_credential->credit_card_no);
        } else {
            $credit_card_no = base64_decode($payment_trns->credit_card_no);
            $reg_id = "sunday_school_reg_id='" . $payment_trns->id . "',";
        }


        $account_holder = $family_data->father_first_name . ' ' . $family_data->father_last_name;

        $created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
        INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
        INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
        WHERE t.user_type_code = 'UT00'  limit 1 ");



        if ((strtoupper($payment_transactions_code) == "A01" || strtoupper($payment_transactions_code) == "100" || strtoupper($payment_transactions_code) == "SUCCEEDED")) {

            $PaymentStatus = 1;

            $payment_txns = $db->query("insert into ss_payment_txns set $reg_id session='" . $client_data['session'] . "', comments='" . $client_data['refund_reason'] . "', automatic=0, $payment_credential_id payment_gateway='" . $data['payment_gateway'] . "', payment_status=1, is_payment_type=6, payment_unique_id='" . $transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', payment_date='" . $data['payment_date'] . "' ");
            $payment_txns_id = $db->insert_id;
        } else {

            $PaymentStatus = 2;

            $payment_txns = $db->query("insert into ss_payment_txns set $reg_id session='" . $client_data['session'] . "', comments='" . $client_data['refund_reason'] . "', automatic=0, $payment_credential_id payment_gateway='" . $data['payment_gateway'] . "', payment_status=2, is_payment_type=6, payment_unique_id='" . $transactionID . "', payment_response_code='" . $payment_transactions_code . "', payment_response='" . json_encode($payment_transactions) . "', payment_date='" . $data['payment_date'] . "' ");
            $payment_txns_id = $db->insert_id;
        }

        if ($payment_txns_id > 0) {

            $refund_payment = $db->query("insert into ss_refund_payment_txns set family_id='" . $client_data['family_id'] . "', payment_txn_id='" . $client_data['payment_txn_id'] . "', refund_txn_id='" . $payment_txns_id . "', refund_amount='" . $client_data['refund_amount'] . "', created_by_user_id ='" . $client_data['created_by_user_id'] . "', created_on ='" . date('Y-m-d h:i:s') . "' ");
            $refund_payment_txn_id = $db->insert_id;
            if ($refund_payment_txn_id > 0) {
                $students = $db->get_row("select GROUP_CONCAT(s.first_name,' ',s.last_name)  as name from ss_student as s INNER JOIN ss_student_session_map as m ON m.student_user_id = s.user_id INNER JOIN ss_user as u ON u.id = s.user_id where s.family_id ='" . $client_data['family_id'] . "' AND session_id='" . $client_data['session'] . "' ");
                $child_name = $students->name;

                if ($PaymentStatus == '1') { //---------Success Condition---------------//
                    $PaymentConfirmDecliendMSG = "Refund Payment Confirmation";
                    $PaymentStatusMSG = "Refund Payment Success";
                    $emailbody_support .= "Your refund payment for " . SCHOOL_NAME . " is Confirmed";
                    $message = str_replace("[AMOUNT]", $currency . $refund_amount, PAYMENT_SUCCESS);
                } elseif ($PaymentStatus == '2') { //---------------failed Condition------------// 
                    $emailbody_support .= "Your refund payment for " . SCHOOL_NAME . " was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                    $PaymentStatusMSG = "Refund Payment Failed";
                    $PaymentConfirmDecliendMSG = "Refund Payment - DECLINED";
                    $message = str_replace("[AMOUNT]", $currency . $refund_amount, PAYMENT_FAILED);
                }
                $is_refund_payment_type = $client_data['type_of_refund_payment'];
                $receipt_id = mt_rand();
                include "../payment/receipt_pdf.php";

                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);

                // (Optional) Setup the paper size and orientation 
                $dompdf->setPaper('Executive', 'Potrate');
                // Render the HTML as PDF 
                $dompdf->render();
                $pathm = '../payment/invoice_and_pdf';
                $filenamem = "/" . $receipt_id;
                $outputm = $dompdf->output();
                $fullpathReceipt = $receipt_id . 'receipt.pdf';
                file_put_contents($pathm . $filenamem . 'receipt.pdf', $outputm);

                $schedule_unique_id = "refund_U" . uniqid();
                $invoice_update = $db->query("insert into ss_invoice set family_id='" . trim($db->escape($client_data['family_id'])) . "', receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d h:i:s') . "', schedule_unique_id='" . $schedule_unique_id . "', is_type=6, receipt_file_path = '" . $fullpathReceipt . "', amount='" . $client_data['refund_amount'] . "', is_due='1', status = '1', reason='" . $client_data['refund_reason'] . "',  created_at = '" . date('Y-m-d h:i:s') . "', created_by='" . $created_user_id . "'");
                $last_receipt_id = $db->insert_id;
                $db->query("insert into ss_invoice_info set payment_txn_id='" . $payment_txns_id . "', invoice_id = '" . $last_receipt_id . "', created_at = '" . date('Y-m-d h:i:s') . "'");
                $last_invoice_info_id = $db->insert_id;

                $CCAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='ick_school' limit 1");
                if (!empty($client_data['refund_wallet_amount'])) {

                    $db->query("insert into ss_payment_account_entries set description='Transferred - School account to Family account', amount='" . $client_data['refund_wallet_amount'] . "', debit_pay_account_id='" . $CCAccountId . "', credit_pay_account_id='" . $client_data['payid_user'] . "', is_manual ='1', is_force_payment ='0', created_on ='" . date('Y-m-d h:i:s') . "' , created_by_user_id ='" . $created_user_id  . "'");
                }


                if ($last_receipt_id > 0 && $last_invoice_info_id > 0 && $db->query('COMMIT') !== false) {
                    $emailbody_support = "Dear " . $family_data->father_first_name . ' ' . $family_data->father_last_name . " Assalamu-alaikum,<br>";
                    // $emailbody_support .= "<p>We want to let you know that the amount on your credit card has been refunded by the admin.</p>";

                    $emailbody_support .= "<p>We want to let you know that the amount of " . $currency . $client_data['refund_amount'] . " from your " . $client_data['type_of_refund_payment'] . " dated " . my_date_changer(date('Y-m-d h:i:s')) . ", has been successfully refunded by the Admin.</p>";

                    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                                                                    <tr>
                                        <td colspan="2" style="text-align: center;">
                                                <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Receipt </u></div>
                                            </td>
                                        </tr>   
                                        <tr>
                                    <td colspan="2" style="text-align: left; padding-top:10px">
                                    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">


                                    <tr><td style="width: 25%;" class="color2">Parent Name:</td>
                                        <td style="width: 75%; text-align:left;">' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '
                                        </td></tr>

                                    <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                                        <td style="width: 75%; text-align:left;">' . internal_phone_check($family_data->father_phone) . '
                                        </td></tr>

                                    <tr><td style="width: 25%;" class="color2">Email:</td>
                                        <td style="width: 75%; text-align:left;"> ' . $family_data->primary_email . ' </td></tr>


                                    <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                        <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                                        </td></tr>
                                    
                                    <tr><td style="width: 25%;" class="color2">Refund Amount:</td> 
                                            <td style="width: 75%; text-align:left;"> ' . $currency . $client_data['refund_amount'] . '</td></tr>

                                        <tr><td style="width: 25%;" class="color2">Refund Payment Date:</td>
                                            <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'), 't') . '
                                                </td></tr>
                                        <tr>
                                            <td style="width: 25%;" class="color2">Refund Reason:</td>
                                                <td style="width: 75%; text-align:left;">' . $client_data['refund_reason'] . '
                                            </td>
                                        </tr>';
                    if (!empty($transactionID)) {
                        $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                                                                <td style="width: 75%; text-align:left;">' . $transactionID . '
                                                                                </td></tr>';
                    }

                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                                                    <td style="width: 75%; text-align:left;">' . $PaymentStatusMSG . '
                                                                    </td></tr>
                                                                
                                                                    </table>
                                                                    </td>
                                                                </tr>         
                                                            </table>';
                    $emailbody_support .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME;
                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                    //---------------Mail Send By MAil Service Start------------//
                    $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                    $email_subject = CENTER_SHORTNAME . ' ' . SCHOOL_NAME .  ' ' . $PaymentConfirmDecliendMSG;

                    $primary_email = '';
                    if (!empty($family_data->primary_email)) {
                        $primary_email = $family_data->primary_email;
                    }

                    $sec_email = '';
                    if (!empty($family_data->secondary_email)) {
                        $sec_email = $family_data->secondary_email;
                    }

                    $sec_gen_email = "";
                    if (!empty(SCHOOL_GEN_EMAIL)) {
                        $sec_gen_email = SCHOOL_GEN_EMAIL;
                    }

                    $mail_service_array = array(
                        'subject' => $email_subject,
                        'message' => $emailbody_support,
                        'request_from' => MAIL_SERVICE_KEY,
                        'attachment_file_name' => [],
                        'attachment_file' => [],
                        'to_email' => [$primary_email, $sec_email, $sec_gen_email],
                        'cc_email' => [ADMIN_EMAIL],
                        'bcc_email' => []
                    );

                    if ($payment_txns_id > 0) {
                        //---------------Mail Send By MAil Service End------------//
                        $bulk_sms = $db->query("insert into ss_bulk_sms set session = '" . $client_data['session'] . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                        $message_id = $db->insert_id;
                        $father_phone = str_replace("-", "", $family_data->father_phone);
                        $bulk_sms_mobile = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");

                        mailservice($mail_service_array);
                    }


                    $dispMsg = "<p class='text-success'> Refund has been successfully received. <p>";
                    echo json_encode(array('code' => "1", 'msg' => $dispMsg));
                    exit;
                } else {
                    $db->query('ROLLBACK');
                    $dispMsg = "<p class='text-danger'> Process Failed. Please try again later. <p>";
                    echo json_encode(array('code' => "0", 'msg' => $dispMsg, '_error' => '3'));
                    exit;
                }
            } else {
                $db->query('ROLLBACK');
                $dispMsg = "<p class='text-danger'> Process Failed. Please try again later. <p>";
                echo json_encode(array('code' => "0", 'msg' => $dispMsg, '_error' => '2'));
                exit;
            }
        } else {
            $db->query('ROLLBACK');
            $dispMsg = "<p class='text-danger'> Process Failed. Please try again later. <p>";
            echo json_encode(array('code' => "0", 'msg' => $dispMsg, '_error' => '1'));
            exit;
        }
    } catch (customException $e) {
        $db->query('ROLLBACK');
        CreateLog($_REQUEST, json_encode($e->errorMessage()));
        exit;
    }
}
//REFUND PAYMENT END
