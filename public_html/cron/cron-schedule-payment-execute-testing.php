<?php
//LIVE - PROD SITE
//set_include_path('/home/759032.cloudwaysapps.com/bfgqwxcqfp/public_html/school_uat/includes/');

//include_once "config.php";
//include_once "FortePayment.class.php";
//include_once 'dompdf/autoload.inc.php';




include_once "../includes/config.php";
//include_once "../includes/FortePayment.class.php";
include_once '../includes/dompdf/autoload.inc.php';


define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;

//CURRENT SESSION
$current_session_row = $db->get_row("select * from ss_school_sessions where current = 1");
$current_session = $current_session_row->id;


$current_dateTime = date('Y-m-d H:i');
$payment_start_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_START_TIME;
$payment_end_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_END_TIME;

//payments to run condition
//if($payment_start_dateTime < $current_dateTime &&  $payment_end_dateTime > $current_dateTime){


// $forte_configarray = array('FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID, 'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY, 'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID, 'FORTE_LOCATION_ID' => FORTE_LOCATION_ID, 'ENVIRONMENT' => ENVIRONMENT,);
// $fortePayment = new FortePayment($forte_configarray);
$payment_gateway = $db->get_row("select id,success_response_code from ss_payment_gateways where status = 'active' ");
$payment_gateway_id = $payment_gateway->id;
$payment_gateway_code = $payment_gateway->success_response_code;

$student_fees_items = $db->get_results("SELECT sfic.id,sfic.schedule_unique_id,sch_item_ids, sfic.schedule_payment_date,sfic.total_amount,sfic.cc_amount,sfic.wallet_amount, 
sfic.schedule_status, sfic.family_id, f.user_id AS family_user_id, 
 f.father_first_name, f.father_last_name, f.father_phone, f.primary_email,
f.secondary_email, f.billing_city, f.billing_post_code, f.forte_customer_token,pay.credit_card_no, 
pay.forte_payment_token, pay.id AS payment_credential_id
FROM `ss_payment_sch_item_cron` sfic
INNER JOIN ss_family f ON f.id = sfic.family_id
INNER JOIN ss_paymentcredentials pay ON pay.family_id = f.id
WHERE schedule_payment_date = '" . date('Y-m-d', strtotime($get_cron_info)) . "' AND sfic.schedule_status = 0 AND sfic.session = '" . $current_session . "' AND pay.default_credit_card =1 AND is_approval='1'");


if (count((array)$student_fees_items) > 0) {

    try {
        foreach ($student_fees_items as $family_data) {
            if ($family_data->total_amount > 0) {
                $token = genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD);
                $request_token  = 'req_'.RandomString();

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

                        $forteParamsSend = array('auth_token' => $token, 'request_token' =>  $request_token, 'schedule_payment_date' => date('Y-m-d'), 'family_user_id' => $family_data->family_user_id, 'customertoken' => $customertoken, 'paymethodtoken' => $paymethodtoken, 'amount' => $amount, 'firstName' => $family_data->father_first_name, 'lastName' => $family_data->father_last_name, 'city' => $family_data->billing_city, 'zip' => $family_data->billing_post_code, 'schedule_item_ids' => 'scheduleitemid_' . $family_data->sch_item_ids, 'countryCode' => 'US', 'action'=> 'cron_schedule_payment_execute', 'system_ip' => $_SERVER['REMOTE_ADDR'] );

                        $PAYSERVICE_URL = PAYSERVICE_URL."api/payment_capture";

                        $request_result = response_post_service($forteParamsSend,$PAYSERVICE_URL);
                        $transactions =  $request_result->data;
                        //$transactions = $fortePayment->transactionsWithPaymentToken($customertoken, $paymethodtoken, $forteParams);
                        $txn_summary = $db->query("insert into ss_txn_summary set  name='" .$family_data->father_first_name."', email='" .$family_data->primary_email. "', phone='" .$family_data->father_phone. "', raw_data='" .json_encode($transactions). "', created_dt='" . date('Y-m-d H:i:s') . "' ");


                        $db->query("insert into ss_received_payment_txn_ids set 
                                request_token='" .$request_token. "', 
                                status=0,
                                ip_address='" .$_SERVER['REMOTE_ADDR']. "',
                                created_dt='" .date('Y-m-d H:i:s'). "' ");


                        $full_amount = '$' . $amount;

                        if (isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01" && !empty(trim($transactions->transaction_id))) {
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

                        // $forteParamsSend = array('amount' => $amount, 'firstName' => $family_data->father_first_name, 'lastName' => $family_data->father_last_name, 'city' => $family_data->billing_city, 'zip' => $family_data->billing_post_code, 'schedule_item_ids' => 'scheduleitemid_' . $family_data->sch_item_ids, 'countryCode' => 'US',);
                        // $forteParams = json_encode($forteParamsSend);
                        // $transactions = $fortePayment->transactionsWithPaymentToken($customertoken, $paymethodtoken, $forteParams);

                        $forteParamsSend = array('auth_token' => $token, 'request_token' =>  $request_token, 'schedule_payment_date' => date('Y-m-d'), 'family_user_id' => $family_data->family_user_id, 'customertoken' => $customertoken, 'paymethodtoken' => $paymethodtoken, 'amount' => $amount, 'firstName' => $family_data->father_first_name, 'lastName' => $family_data->father_last_name, 'city' => $family_data->billing_city, 'zip' => $family_data->billing_post_code, 'schedule_item_ids' => 'scheduleitemid_' . $family_data->sch_item_ids, 'countryCode' => 'US', 'action'=> 'cron_schedule_payment_execute', 'system_ip' => $_SERVER['REMOTE_ADDR'] );
                        $PAYSERVICE_URL = PAYSERVICE_URL."api/payment_capture";
                        $request_result = response_post_service($forteParamsSend, $PAYSERVICE_URL);
                        $transactions =  $request_result->data;

                        $txn_summary = $db->query("insert into ss_txn_summary set  name='" .$family_data->father_first_name."', email='" .$family_data->primary_email. "', phone='" .$family_data->father_phone. "', raw_data='" .json_encode($transactions). "', created_dt='" . date('Y-m-d H:i:s') . "' ");
                        
                        $db->query("insert into ss_received_payment_txn_ids set 
                                request_token='" .$request_token. "', 
                                status=0,
                                ip_address='" .$_SERVER['REMOTE_ADDR']. "',
                                created_dt='" .date('Y-m-d H:i:s'). "' ");

                        $full_amount = '$' . $amount;

                        if (isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01" && !empty(trim($transactions->transaction_id))) {
                            $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                            $response_code = $transactions->response->response_code;
                            $transactionID = $transactions->transaction_id;
                            $payment_status = 1;
                            $trxn_msg = 'Payment Successful';

                            $db->query("update ss_received_payment_txn_ids set status=1 where request_token = '" . $request_token . "'");

                        } elseif(isset($transactions->response->response_code) && !empty(isset($transactions->response->response_code))){
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
            } else {

                $student_fees_items = $db->query("update ss_payment_sch_item_cron set schedule_status ='1',reason='Zero Payment Successful', updated_at = '" . date('Y-m-d H:i:s') . "' where schedule_unique_id = '" . $family_data->schedule_unique_id . "' AND id='" . $family_data->id . "' ");
            }
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        CreateLog($_REQUEST, json_encode($msg));
        //echo json_encode($msg);
    }
}
//}
