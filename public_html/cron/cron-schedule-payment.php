<?php
//LIVE - PROD SITE
//set_include_path('/home3/bayyanor/public_html/ick/summercamp/includes/');

//LIVE - QA SITE
//set_include_path('/home3/bayyanor/public_html/ick/academy_new/includes/');

//Devlopment - QA SITE
set_include_path('/webroot/b/a/bayyan005/icksaturdayqa.click2clock.com/www/includes/');

include_once "config.php";
include_once "FortePayment.class.php";
include_once 'dompdf/autoload.inc.php';

define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;
if(!empty(get_country()->currency)){  
    $currency = get_country()->currency;
}else{
    $currency = '';
}
//CURRENT SESSION
$current_session_row = $db->get_row("select * from ss_school_sessions where current = 1 ");
$current_session = $current_session_row->id;


$current_dateTime = date('Y-m-d H:i');
$payment_start_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_START_TIME;
$payment_end_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_END_TIME;

//payments to run condition
// if($payment_start_dateTime < $current_dateTime &&  $payment_end_dateTime > $current_dateTime){


$forte_configarray = array('FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID, 'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY, 'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID, 'FORTE_LOCATION_ID' => FORTE_LOCATION_ID, 'ENVIRONMENT' => ENVIRONMENT,);
$fortePayment = new FortePayment($forte_configarray);
$payment_gateway = $db->get_row("select id,success_response_code from ss_payment_gateways where status = 'active' ");
$payment_gateway_id = $payment_gateway->id;
$payment_gateway_code = $payment_gateway->success_response_code;

$student_fees_items = $db->get_results("SELECT DISTINCT family_id FROM `ss_student_fees_items` sfi INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id` 
INNER JOIN ss_user u ON u.`id` = s.`user_id` INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
WHERE sfi.schedule_payment_date = '" . date('Y-m-d') . "' AND sfi.schedule_status = 0 AND u.is_active=1 AND u.is_deleted=0 AND ssm.session_id = '" . $current_session . "'");

if (count((array)$student_fees_items) > 0) {
    $txn_summry = [];
    
    try {
        $created_user_id = $db->get_var("select id from ss_user where (username='systemuser' OR email='digitalsupport@quasardigital.com') and is_active = 1 AND  is_deleted = 0");
        $SchoolAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='ick_school' limit 1 ");
        $CCAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='CC' limit 1 ");
        $get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc, new_registration_session, registration_page_termsncond,reg_form_term_cond_attach_url from ss_client_settings where status = 1");
        if (!empty($get_email->new_registration_email_bcc)) {
            $emails_bcc = explode(",", $get_email->new_registration_email_bcc);
        }
        if (!empty($get_email->new_registration_email_cc)) {
            $emails_cc = explode(",", $get_email->new_registration_email_cc);
        }

        foreach ($student_fees_items as $row) {
            $db->query('BEGIN');
            $family_data = $db->get_row("SELECT sfi.id AS sch_item_id, sfi.schedule_payment_date,sfi.original_schedule_payment_date,SUM(amount) AS total_amount, 
                sfi.schedule_status, s.family_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student_name, 
                s.school_grade, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email,
                f.secondary_email, f.billing_city, f.billing_post_code, f.forte_customer_token, f.user_id, s.user_id AS family_user_id, pay.credit_card_no, 
                pay.forte_payment_token, pay.id AS payment_credential_id,
                t.id AS trxn_id, t.payment_unique_id
                FROM `ss_student_fees_items` sfi
                INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
                INNER JOIN ss_user u ON u.id = s.user_id
                INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                INNER JOIN ss_family f ON f.id = s.family_id
                INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
                LEFT JOIN ss_student_fees_transactions ON ss_student_fees_transactions.student_fees_item_id = sfi.id
                LEFT JOIN ss_payment_txns t ON t.id = ss_student_fees_transactions.payment_txns_id
                WHERE sfi.schedule_payment_date = '" . date('Y-m-d') . "' 
                AND sfi.schedule_status = 0 
                AND f.id = '" . $row->family_id . "' 
                AND pay.default_credit_card = 1 
                AND u.is_active=1 
                AND ssm.session_id = '" . $current_session . "'
                AND u.is_deleted=0 
                GROUP BY sfi.original_schedule_payment_date,schedule_status,payment_unique_id
                ORDER BY  sfi.original_schedule_payment_date ASC LIMIT 1");

            $Txnx_summary = $db->get_var("SELECT `id` FROM `ss_txn_summary` WHERE `family_user_id`='" . $family_data->user_id . "' AND FIND_IN_SET('" . $family_data->sch_item_id . "',`student_fees_item_id`) AND `payment_status`='1';");
            if (empty($Txnx_summary)) {
                $star = '************';
                $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($family_data->credit_card_no)), -4);
                if ($family_data->total_amount > 0) {

                    //--------------------------Family Virtual Wallet Ammount------------------//
                    $FamilyAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `user_id`='" . $family_data->user_id . "' ORDER by id DESC limit 1 ");
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
                        } elseif ($totalWalletAmount < 0) {
                            $totalWalletAmount = 0;
                        }
                    }


                    //++++++++++++++++++++++++++++##-All Amount Debit From Credit Card-##++++++++++++++++++++++++++++++//
                 if ($totalWalletAmount <= 0) {
                        $AmountDebitFrom = "AllCC";

                        //-------------Check  customer token,payment token,total amount Start------------//
                        if (!empty($family_data->forte_customer_token) && !empty($family_data->forte_payment_token) && !empty($family_data->total_amount)) {
                            $customertoken = $family_data->forte_customer_token;
                            $paymethodtoken = $family_data->forte_payment_token;
                            $amount = $family_data->total_amount;

                            $schedule_item_student = $db->get_results("SELECT sfi.id, family_id, sfi.id as item_id ,s.user_id,sfi.amount as item_amount,schedule_payment_date
                            FROM `ss_student_fees_items` sfi 
                            INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
                            INNER JOIN ss_user u ON u.id = s.user_id
                            INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
                            WHERE schedule_payment_date = '" . date('Y-m-d') . "' 
                            AND ssm.session_id = '" . $current_session . "'
                            AND sfi.original_schedule_payment_date = '" . $family_data->original_schedule_payment_date . "' 
                            AND schedule_status = 0 AND family_id = '" . $row->family_id . "' AND u.is_active=1 AND u.is_deleted=0");

                            $schedule_item_student_ids = "";
                            $student_fees_item_id = "";
                            foreach ($schedule_item_student as $val) {
                                $schedule_item_student_ids .= $val->id . '|';
                                $student_fees_item_id .= $val->id . ','; //for transactions summary
                            }

                            $forteParamsSend = array('amount' => $amount, 'firstName' => $family_data->father_first_name, 'lastName' => $family_data->father_last_name, 'city' => $family_data->billing_city, 'zip' => $family_data->billing_post_code, 'schedule_item_ids' => 'scheduleitemid_' . rtrim($schedule_item_student_ids, '|'), 'countryCode' => 'US',);
                            $forteParams = json_encode($forteParamsSend);
                            $transactions = $fortePayment->transactionsWithPaymentToken($customertoken, $paymethodtoken, $forteParams);

                            $full_amount = $currency . $amount;
                            if (isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01") {
                                $trxn_msg = 'Payment Successful';
                                $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                                $response_code = $transactions->response->response_code;
                                $payment_status = 1;
                            } else {
                                $trxn_msg = 'Payment Failed';
                                $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                                $response_code = $transactions->response->response_code;
                                $payment_status = 0;
                            }

                            if (!empty(trim($transactions->transaction_id))) {
                                $transactionID = $transactions->transaction_id;
                            } else {
                                $transactionID = "";
                            }
                            //---------------------PAyment summary Entry In Summary Table Start-------------------//
                            $txn_summry[] = array("name" => $family_data->father_first_name . ' ' . $family_data->father_last_name, "email" => $family_data->primary_email, "phone" => $family_data->father_phone, "raw_data" => json_encode($transactions), "family_user_id" => $family_data->user_id, "student_fees_item_id" => rtrim($student_fees_item_id, ','), "payment_status" => $payment_status);
                            //---------------------PAyment summary Entry In Summary Tabl End-------------------//

                            //---------------------------------Payment Success Execution-------------------------//
                            if (!empty($transactionID) && strtoupper($transactions->response->response_code) == "A01") {

                                //$transactionID  = $transactions->transaction_id;
                                $payment_txns = $db->query("insert into ss_payment_txns set session='" . $current_session . "', automatic=1, payment_credentials_id='" . $family_data->payment_credential_id . "', payment_gateway='forte', payment_status=1, payment_unique_id='" . $transactionID . "', payment_response_code='" . $response_code . "', payment_response='" . json_encode($transactions) . "', payment_date='" . date('Y-m-d H:i') . "' ");
                                $payment_txns_id = $db->insert_id;

                                //---This Entry For Manage Account Credit /Debit -> Amount Entries From CC TO family Account ---//
                                $AmountTransferedcCCToFamily = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Amount Transfered From Credit Card To Family Virtual Account','" . $amount . "','" . $CCAccountId . "','" . $FamilyAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                                $AmountTransferedcFamilyToSchool = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Amount Transfered From Family Virtual Account To ICK School Account','" . $amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");


                                $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";
                                $count = 0;

                                //----------------------------------Invoice--------------------------------//
                                $invoce_id = mt_rand();
                                include_once "../payment/invoice_pdf.php";
                                $dompdf = new Dompdf();
                                $dompdf->loadHtml($html);
                                // (Optional) Setup the paper size and orientation 
                                $dompdf->setPaper('Executive', 'Potrate');
                                // Render the HTML as PDF 
                                $dompdf->render();
                                $path_Invoice = '../payment/invoice_and_pdf';
                                $filename_Invoice = "/" . $invoce_id;
                                $output_Invoice = $dompdf->output();
                                $fullpathInvoice = $invoce_id . 'invoice.pdf';
                                file_put_contents($path_Invoice . $filename_Invoice . 'invoice.pdf', $output_Invoice);
                                unset($dompdf);

                                //-------------------------------------Receipt--------------//
                                $receipt_id = mt_rand();
                                include_once "../payment/receipt_pdf.php";
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
                                unset($dompdf);


                                foreach ($schedule_item_student as $val) {
                                    $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $val->id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i') . "'");
                                    $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 1, updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $val->id . "' ");

                                    //--------------Invoice Update(IF Exist) OR Insert-----------//
                                    $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where student_item_id = '" . $val->id . "' limit 1 ");
                                    if ($Student_invoice_Id > 0) {
                                        $db->query("update ss_invoice set receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "' where student_item_id = '" . $val->id . "' ");
                                    } else {
                                        $db->query("insert into ss_invoice set student_item_id= '" . $val->id  . "',user_id= '" . $val->user_id . "',amount= '" . $family_data->total_amount . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "',invoice_file_path='" . $fullpathInvoice . "',receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "', created_at = '" . date('Y-m-d H:i:s') . "', created_by='" . $created_user_id . "' ");
                                    }

                                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=1, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session . "', schedule_payment_date = '" .$val->schedule_payment_date. "'");
                                    $count++;
                                }

                                if ($count === count((array)$schedule_item_student)) {
                                    $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where sfi.session = '" . $current_session . "' and sft.payment_txns_id='" . $payment_txns_id . "' GROUP BY s.user_id ");

                                    $child_name = "";
                                    foreach ($trxn_child_names as $row) {
                                        $child_name .= $row->first_name . ", ";
                                    }




                                    //$emailbody_support.= "Assalamualaikum ,<br>";
                                    $emailbody_support = "Assalamualaikum ,<br>";
                                    $emailbody_support .= "Your payment for ".CENTER_SHORTNAME." ".SCHOOL_NAME." is Confirmed";
                                    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
                                    <tr>
                                    <td colspan="2" style="text-align: center;">
                                        <div style="font-size: 18px;margin-top:30px; text-align:left;"><u>Receipt </u></div>
                                    </td>
                                </tr>   
                                <tr><td colspan="2" style="text-align: left; padding-top:10px">
                                <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">
                                <tr><td style="width: 25%;" class="color2">Parent Name:</td>
                                <td style="width: 75%; text-align:left;">' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                                <td style="width: 75%; text-align:left;">' . internal_phone_check($family_data->father_phone) . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Email:</td><td style="width: 75%; text-align:left;"> ' . $family_data->primary_email . ' </td></tr>
                                <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                                <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                <td style="width: 75%; text-align:left;">' . $credit_card_no . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                <td style="width: 75%; text-align:left;"> '.$currency . $amount . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Payment Date:</td>
                                <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'),'t') . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                <td style="width: 75%; text-align:left;">' . $transactionID . '</td></tr>
                                <tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                <td style="width: 75%; text-align:left;">' . $trxn_msg . '</td></tr>
                                    </table>
                                    </td>
                                </tr>         
                            </table>';
                                    $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";



                                    //---------------Mail Send By MAil Service Start------------//
                                    $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                                    $email_subject = CENTER_SHORTNAME.' '.SCHOOL_NAME.' - Payment Confirmation';

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
                                        'bcc_email' => $bcc_email
                                    );
                                   mailservice($mail_service_array);

                                    //---------------Mail Send By MAil Service End------------//

                                    $db->query("insert into ss_bulk_sms set session = '" . $current_session . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                                    $message_id = $db->insert_id;
                                    $father_phone = str_replace("-", "", $family_data->father_phone);
                                    $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");
                                    $db->query('COMMIT');
                                    echo "success";
                                } else {
                                    $db->query('ROLLBACK');
                                    $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                                    CreateLog($_REQUEST, json_encode($return_resp));
                                    echo json_encode($return_resp);
                                    
                                }
                            }
                            //---------------------------------Payment Failed Execution Start-------------------------//
                            else {
                                $payment_txns = $db->query("insert into ss_payment_txns set session='" . $current_session . "',automatic=1, payment_credentials_id='" . $family_data->payment_credential_id . "', payment_gateway='forte', payment_status=0, payment_unique_id='" . $transactionID . "', payment_response_code='" . $response_code . "', payment_response='" . json_encode($transactions) . "', payment_date='" . date('Y-m-d H:i') . "' ");
                                $payment_txns_id = $db->insert_id;

                                $schedule_item_student = $db->get_results("SELECT sfi.id, family_id, s.user_id, sfi.amount FROM `ss_student_fees_items` sfi INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE schedule_payment_date = '" . date('Y-m-d') . "' AND original_schedule_payment_date = '" . $family_data->original_schedule_payment_date . "'  AND schedule_status = 0 AND family_id = '" . $row->family_id . "' AND u.is_active=1 AND u.is_deleted=0 AND ssm.session_id = '" . $current_session . "'");

                                $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong> Pending <br> <strong>Current Status : </strong> Decline";
                                $count = 0;

                                //-------------------Invoice----------------------//
                                $invoce_id = mt_rand();
                                include_once "../payment/invoice_pdf.php";
                                $dompdf = new Dompdf();
                                $dompdf->loadHtml($html);
                                // (Optional) Setup the paper size and orientation 
                                $dompdf->setPaper('Executive', 'Potrate');
                                // Render the HTML as PDF 
                                $dompdf->render();
                                $path = '../payment/invoice_and_pdf';
                                $filename = "/" . $invoce_id;
                                $output = $dompdf->output();
                                $fullpath = $invoce_id . 'overdueinvoice.pdf';
                                file_put_contents($path . $filename . 'overdueinvoice.pdf', $output);
                                unset($dompdf);
                                //-------------------Invoice----------------------//
                                foreach ($schedule_item_student as $val) {
                                    $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $val->id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i') . "'");

                                    $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 4, updated_at = '" . date('Y-m-d H:i') . "' where id = '" . $val->id . "' ");

                                    $db->query("insert into ss_invoice set user_id='" . $val->user_id . "', student_item_id='" . $val->id . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "', amount='" . $family_data->total_amount . "', is_due='2', status = '1', created_at='" . date('Y-m-d H:i:s') . "', invoice_file_path='" . $fullpath . "', created_by='" . $created_user_id . "'");

                                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=4, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session . "', schedule_payment_date = '" .$val->schedule_payment_date. "'");
                                    $count++;
                                }
                                if ($count === count((array)$schedule_item_student)) {
                                    $db->query('COMMIT');
                                    $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where sfi.session = '" . $current_session . "' and sft.payment_txns_id='" . $payment_txns_id . "' GROUP BY s.user_id ");
                                    $child_name = "";
                                    foreach ($trxn_child_names as $row) {
                                        $child_name .= $row->first_name . ", ";
                                    }
                                    //$emailbody_support.= "Assalamualaikum <br>";
                                    $emailbody_support = "Assalamualaikum <br>";
                                    $emailbody_support .= "Your payment for ".CENTER_SHORTNAME." ".SCHOOL_NAME." was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                                    $emailbody_support .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0"><tr>
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
                                    <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                                        <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
                                        </td></tr>';
                                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                        <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                                        </td></tr>
                                    <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                        <td style="width: 75%; text-align:left;"> '.$currency . $amount . '</td></tr>
                                    <tr><td style="width: 25%;" class="color2">Payment Date:</td>
                                        <td style="width: 75%; text-align:left;">' . date('Y-m-d H:i A') . '
                                            </td></tr>';
                                    if (!empty($transactionID)) {
                                        $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                        <td style="width: 75%; text-align:left;">' . $transactionID . '
                                        </td></tr>';
                                    }
                                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                    <td style="width: 75%; text-align:left;">' . $trxn_msg . '
                                    </td></tr>';
                                    $emailbody_support .= '</table>
                                        </td>
                                    </tr>         
                                </table>';
                                    $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                                    //---------------Mail Send By MAil Service Start------------//
                                    $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                                    $email_subject = CENTER_SHORTNAME.' '.SCHOOL_NAME.' - Payment Declined';

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
                                        'bcc_email' => $bcc_email
                                    );
                                    mailservice($mail_service_array);

                                    //---------------Mail Send By MAil Service End------------//

                                    $db->query("insert into ss_bulk_sms set session = '" . $current_session . "',  message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                                    $message_id = $db->insert_id;

                                    $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $family_data->father_phone . "', delivery_status = 2, attempt_counter = 0");
                                    $db->query('COMMIT');
                                    echo "success";
                                } else {
                                    $db->query('ROLLBACK');
                                    $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                                    CreateLog($_REQUEST, json_encode($return_resp));
                                    echo json_encode($return_resp);
                                    
                                }
                            }
                            //---------------------------------Payment Failed Execution End-------------------------//
                        } else {
                            $db->query('ROLLBACK');
                            $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because payment credential wrong');
                            CreateLog($_REQUEST, json_encode($return_resp));
                            echo json_encode($return_resp);
                         
                        }


                        //++++++++++++++++++++++++++++##-All Amount Debit From Wallet-##++++++++++++++++++++++++++++++//

                    } 
                    elseif ($totalWalletAmount >= $family_data->total_amount) {
                        $AmountDebitFrom = "AllWallet";
                        $amount = $family_data->total_amount;
                        $full_amount = $currency . $amount;
                        $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                        $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";
                        $schedule_item_student = $db->get_results("SELECT sfi.id, family_id, sfi.id as item_id,s.user_id,sfi.amount as item_amount  
                    FROM `ss_student_fees_items` sfi 
                    INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
                    INNER JOIN ss_user u ON u.id = s.user_id
                    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
                    WHERE schedule_payment_date = '" . date('Y-m-d') . "' 
                    AND ssm.session_id = '" . $current_session . "'
                    AND sfi.original_schedule_payment_date = '" . $family_data->original_schedule_payment_date . "' 
                    AND schedule_status = 0 AND family_id = '" . $row->family_id . "' AND u.is_active=1 AND u.is_deleted=0");

                        $student_fees_item_id = "";
                        foreach ($schedule_item_student as $val) {
                            $student_fees_item_id .= $val->id . ','; //for transactions summary
                        }
                        //---Amount Entry From Family TO ICK School Account ---//
                       $AmountTransfered = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Amount Transfered From Family Virtual Account To ICK School Account','" . $amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                        $payment_account_entries_id = $db->insert_id;
                    
                        if ($payment_account_entries_id > 0) {
                            //---------------------PAyment summary Entry In Summary Table Start-------------------//
                        $txn_summry[] = array("name" => $family_data->father_first_name . ' ' . $family_data->father_last_name, "email" => $family_data->primary_email, "phone" => $family_data->father_phone, "raw_data" => json_encode(array("mesage" => $message, "debit" => 'Amount From Wallet')), "family_user_id" => $family_data->user_id, "student_fees_item_id" => rtrim($student_fees_item_id, ','), "payment_status" => 1);
                        //---------------------PAyment summary Entry In Summary Tabl End-------------------//
                            $count = 0;
                            //----------------------------------Invoice--------------------------------//
                            $invoce_id = mt_rand();
                            include_once "../payment/invoice_pdf.php";
                            $dompdf = new Dompdf();
                            $dompdf->loadHtml($html);
                            // (Optional) Setup the paper size and orientation 
                            $dompdf->setPaper('Executive', 'Potrate');
                            // Render the HTML as PDF 
                            $dompdf->render();
                            $path_Invoice = '../payment/invoice_and_pdf';
                            $filename_Invoice = "/" . $invoce_id;
                            $output_Invoice = $dompdf->output();
                            $fullpathInvoice = $invoce_id . 'invoice.pdf';
                            file_put_contents($path_Invoice . $filename_Invoice . 'invoice.pdf', $output_Invoice);
                            unset($dompdf);

                            //-------------------------------------Receipt--------------//
                            $receipt_id = mt_rand();
                            include_once "../payment/receipt_pdf.php";
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
                            unset($dompdf);

                            foreach ($schedule_item_student as $val) {
                                //--- Entry student fees virtual transactions ---//
                                $student_fees_virtual_transactions = $db->query("insert into ss_student_fees_virtual_transactions set student_fees_item_id='" . $val->id . "', payment_account_entries_id = '" . $payment_account_entries_id . "', created_on = '" . date('Y-m-d H:i') . "'");
                                //--- Update student fees item ---//
                                $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 1, updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $val->id . "' ");

                                $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where student_item_id = '" . $val->id . "' limit 1 ");
                                if ($Student_invoice_Id > 0) {
                                    $db->query("update ss_invoice set receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "' where student_item_id = '" . $val->id . "' ");
                                } else {
                                    $db->query("insert into ss_invoice set student_item_id= '" . $val->id  . "',user_id= '" . $val->user_id . "',amount= '" . $family_data->total_amount . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "',invoice_file_path='" . $fullpathInvoice . "',receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "', created_at = '" . date('Y-m-d H:i:s') . "', created_by='" . $created_user_id . "' ");
                                }

                                $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=1, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session . "', schedule_payment_date = '" .$val->schedule_payment_date. "'");
                                $count++;
                            }
                            if ($count === count((array)$schedule_item_student)) {
                                $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_virtual_transactions sfvt
                    INNER JOIN ss_student_fees_items sfi ON sfi.id = sfvt.student_fees_item_id 
                    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where sfi.session = '" . $current_session . "' 
                    and sfvt.payment_account_entries_id='" . $payment_account_entries_id . "' GROUP BY s.user_id ");

                                $child_name = "";
                                foreach ($trxn_child_names as $row) {
                                    $child_name .= $row->first_name . ", ";
                                }

                                //$emailbody_support.= "Assalamualaikum ,<br>";
                                $emailbody_support = "Assalamualaikum ,<br>";
                                $emailbody_support .= "Your payment for ".CENTER_SHORTNAME." ".SCHOOL_NAME." is Confirmed";
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

                    <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                        <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
                        </td></tr>

                        <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                        <td style="width: 75%; text-align:left;"> '.$currency . $amount . '</td></tr>';

                                if (!empty($payment_account_entries_id)) {
                                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                <td style="width: 75%; text-align:left;">wtx_' . md5($payment_account_entries_id) . '
                                </td></tr>';
                                }

                                $emailbody_support .= ' <tr><td style="width: 25%;" class="color2">Payment Date:</td>
                        <td style="width: 75%; text-align:left;">' . date('Y-m-d H:i A') . '
                            </td></tr>

                    <tr><td style="width: 25%;" class="color2">Payment Status:</td>
                        <td style="width: 75%; text-align:left;"> Payment Successful
                        </td></tr>
                    
                        </table>
                        </td>
                    </tr>         
                  </table>';

                                $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                                $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                                $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                                //---------------Mail Send By MAil Service Start------------//
                                $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                                $email_subject = CENTER_SHORTNAME.' '.SCHOOL_NAME . ' - Payment Confirmation';

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
                                    'bcc_email' => $bcc_email
                                );
                                mailservice($mail_service_array);

                                //---------------Mail Send By MAil Service End------------//

                                //$res = '1';
                                $db->query("insert into ss_bulk_sms set session = '" . $current_session . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                                $message_id = $db->insert_id;
                                $father_phone = str_replace("-", "", $family_data->father_phone);
                                $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");
                                $db->query('COMMIT');
                                echo "success";
                            } else {
                                $db->query('ROLLBACK');
                                $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                                CreateLog($_REQUEST, json_encode($return_resp));
                                echo json_encode($return_resp);
                               
                            }
                        } else {
                             $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                            //---------------------PAyment summary Entry In Summary Table Start-------------------//
                             $txn_summry[] = array("name" => $family_data->father_first_name . ' ' . $family_data->father_last_name, "email" => $family_data->primary_email, "phone" => $family_data->father_phone, "raw_data" => json_encode(array("mesage" => $message, "debit" => 'Amount From Wallet')), "family_user_id" => $family_data->user_id, "student_fees_item_id" => rtrim($student_fees_item_id, ','), "payment_status" => 0);
                        //---------------------PAyment summary Entry In Summary Tabl End-------------------//
                            $db->query('ROLLBACK');
                            $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in virtual account');
                            CreateLog($_REQUEST, json_encode($return_resp));
                            echo json_encode($return_resp);
                         
                        }

                        //++++++++++++++++++++++++++++##-Amount Debit From Wallet And Credit Card-##++++++++++++++++++++++++++++++//

                    }  elseif ($totalWalletAmount < $family_data->total_amount && $totalWalletAmount > 0) {
                    $AmountDebitFrom = "WalletAndCC";
                    //$amount = $family_data->total_amount;
                    $WalletAmmount = $totalWalletAmount;
                    $ccAmount = $family_data->total_amount - $totalWalletAmount;

                    if (!empty($family_data->forte_customer_token) && !empty($family_data->forte_payment_token) && !empty($family_data->total_amount)) {
                        $customertoken = $family_data->forte_customer_token;
                        $paymethodtoken = $family_data->forte_payment_token;
                        $amount = $ccAmount;

                        $schedule_item_student = $db->get_results("SELECT sfi.id, family_id, sfi.id as item_id ,s.user_id,sfi.amount as item_amount
                        FROM `ss_student_fees_items` sfi 
                        INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
                        INNER JOIN ss_user u ON u.id = s.user_id
                        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
                        WHERE schedule_payment_date = '" . date('Y-m-d') . "' 
                        AND ssm.session_id = '" . $current_session . "'
                        AND sfi.original_schedule_payment_date = '" . $family_data->original_schedule_payment_date . "' 
                        AND schedule_status = 0 AND family_id = '" . $row->family_id . "' AND u.is_active=1 AND u.is_deleted=0");

                              $schedule_item_student_ids = "";
                            $student_fees_item_id = "";
                            foreach ($schedule_item_student as $val) {
                                $schedule_item_student_ids .= $val->id . '|';
                                $student_fees_item_id .= $val->id . ','; //for transactions summary
                            }

                        $forteParamsSend = array('amount' => $amount, 'firstName' => $family_data->father_first_name, 'lastName' => $family_data->father_last_name, 'city' => $family_data->billing_city, 'zip' => $family_data->billing_post_code, 'schedule_item_ids' => 'scheduleitemid_' . rtrim($schedule_item_student_ids, '|'), 'countryCode' => 'US',);
                        $forteParams = json_encode($forteParamsSend);
                        $transactions = $fortePayment->transactionsWithPaymentToken($customertoken, $paymethodtoken, $forteParams);

                        $full_amount = $currency . $amount;
                        if (isset($transactions->response->response_code) && strtoupper($transactions->response->response_code) == "A01") {
                            $trxn_msg = 'Payment Successful';
                            $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                            $response_code = $transactions->response->response_code;
                             $payment_status = 1;
                        } else {
                            $trxn_msg = 'Payment Failed';
                            $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                            $response_code = $transactions->response->response_code;
                             $payment_status = 0;
                        }

                        if (!empty(trim($transactions->transaction_id))) {
                            $transactionID = $transactions->transaction_id;
                        } else {
                            $transactionID = "";
                        }
                         //---------------------PAyment summary Entry In Summary Table Start-------------------//
                            $txn_summry[] = array("name" => $family_data->father_first_name . ' ' . $family_data->father_last_name, "email" => $family_data->primary_email, "phone" => $family_data->father_phone, "raw_data" => json_encode($transactions), "family_user_id" => $family_data->user_id, "student_fees_item_id" => rtrim($student_fees_item_id, ','), "payment_status" => $payment_status);
                            //---------------------PAyment summary Entry In Summary Tabl End-------------------//

                        //---------------------------------Payment Success Execution-------------------------//
                        if (!empty($transactionID) && strtoupper($transactions->response->response_code) == "A01") {
                            $PaymentStatus = 1; //Success
                            $PaymentConfirmDecliendMSG = "Payment Confirmation";
                            $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";

                            //-----------------Credit Card Amount Entry IN Transaction Table------------------//
                            $payment_txns = $db->query("insert into ss_payment_txns set session='" . $current_session . "', automatic=1, payment_credentials_id='" . $family_data->payment_credential_id . "', payment_gateway='forte', payment_status=1, payment_unique_id='" . $transactionID . "', payment_response_code='" . $response_code . "', payment_response='" . json_encode($transactions) . "', payment_date='" . date('Y-m-d H:i') . "' ");
                            $payment_txns_id = $db->insert_id;

                            //---Wallet Amount Entries From Family TO ICK School Account ---//
                            $AmountTransfered = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family account to School account','" . $WalletAmmount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $payment_account_entries_id = $db->insert_id;

                            //---This Entry For Manage Account Credit /Debit -> Amount Entries From CC TO family Account ---//
                            $AmountTransferedcCCToFamily = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family CC to Family account','" . $amount . "','" . $CCAccountId . "','" . $FamilyAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $AmountTransferedcFamilyToSchool = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family account to School account','" . $amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                        } else { //Failed
                            //---------------------------------Payment Failed Execution Start Here!-------------------------//
                            $PaymentStatus = 2; //Failed
                            $PaymentConfirmDecliendMSG = "Payment - DECLINED";
                            $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong> Pending <br> <strong>Current Status : </strong> Decline";

                            //-----------------Amount Entry IN Transaction Table------------------//
                            $payment_txns = $db->query("insert into ss_payment_txns set session='" . $current_session . "', automatic=1, payment_credentials_id='" . $family_data->payment_credential_id . "', payment_gateway='forte',  payment_status=0, payment_unique_id='" . $transactionID . "', payment_response_code='" . $response_code . "', payment_response='" . json_encode($transactions) . "', payment_date='" . date('Y-m-d H:i') . "' ");
                            $payment_txns_id = $db->insert_id;

                            //---Wallet Amount Entries From Family TO ICK School Account ---//
                            $AmountTransfered = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family account to School account','" . $WalletAmmount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $payment_account_entries_id = $db->insert_id;
                            //---Refund Wallet Amount-> Entries From ICK School  O Family Account ---//
                            $RefundAmountTransfered = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Refunded - School account to Family account','" . $WalletAmmount . "','" . $SchoolAccountId . "','" . $FamilyAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $refund_payment_account_entries_id = $db->insert_id;
                        }
                        $count = 0;

                        //----------------------------------Invoice--------------------------------//
                        $invoce_id = mt_rand();
                        include_once "../payment/invoice_pdf.php";
                        $dompdf = new Dompdf();
                        $dompdf->loadHtml($html);
                        // (Optional) Setup the paper size and orientation 
                        $dompdf->setPaper('Executive', 'Potrate');
                        // Render the HTML as PDF 
                        $dompdf->render();
                        $path_Invoice = '../payment/invoice_and_pdf';
                        $filename_Invoice = "/" . $invoce_id;
                        $output_Invoice = $dompdf->output();
                        $fullpathInvoice = $invoce_id . 'invoice.pdf';
                        file_put_contents($path_Invoice . $filename_Invoice . 'invoice.pdf', $output_Invoice);
                        unset($dompdf);

                        //-------------------------------------Receipt--------------//
                        if ($PaymentStatus == 1) {
                            $receipt_id = mt_rand();
                            include_once "../payment/receipt_pdf.php";
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
                        }

                        foreach ($schedule_item_student as $val) {
                            if ($PaymentStatus == 1) { //---------Success Condition---------------//
                                //--- Entry student fees CC transactions ---//
                                $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $val->id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i') . "'");
                                //--- Entry student fees virtual transactions ---//
                                $student_fees_virtual_transactions = $db->query("insert into ss_student_fees_virtual_transactions set student_fees_item_id='" . $val->id . "', payment_account_entries_id = '" . $payment_account_entries_id . "', created_on = '" . date('Y-m-d H:i') . "'");
                                //--- Update student fees item ---//
                                $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 1, updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $val->id . "' ");

                                //--------------Invoice Update(IF Exist) OR Insert-----------//
                                $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where student_item_id = '" . $val->id . "' limit 1 ");
                                if ($Student_invoice_Id > 0) {
                                    $db->query("update ss_invoice set receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "' where student_item_id = '" . $val->id . "' ");
                                } else {
                                    $db->query("insert into ss_invoice set student_item_id= '" . $val->id  . "',user_id= '" . $val->user_id . "',amount= '" . $family_data->total_amount . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "',invoice_file_path='" . $fullpathInvoice . "',receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "', created_at = '" . date('Y-m-d H:i:s') . "', created_by='" . $created_user_id . "' ");
                                }



                                $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=1, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session . "', schedule_payment_date = '" .$val->schedule_payment_date. "'");
                            } elseif ($PaymentStatus == 2) { //---------------failed Condition------------// 
                                //--- Entry student fees CC transactions ---//
                                $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $val->id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i') . "'");

                                //--- Entry student fees virtual transactions ---//
                                $student_fees_virtual_transactions = $db->query("insert into ss_student_fees_virtual_transactions set student_fees_item_id='" . $val->id . "', payment_account_entries_id = '" . $payment_account_entries_id . "', created_on = '" . date('Y-m-d H:i') . "'");

                                //--- Refund Entry student fees virtual transactions ---//
                                $Refundstudent_fees_virtual_transactions = $db->query("insert into ss_student_fees_virtual_transactions set student_fees_item_id='" . $val->id . "', payment_account_entries_id = '" . $refund_payment_account_entries_id . "', created_on = '" . date('Y-m-d H:i') . "'");


                                //--- Update student fees item ---//
                                $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 4, updated_at = '" . date('Y-m-d H:i') . "' where id = '" . $val->id . "' ");


                                $db->query("insert into ss_invoice set user_id='" . $val->user_id . "', student_item_id='" . $val->id . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "', amount='" . $family_data->total_amount . "', is_due='2', status = '1', created_at='" . date('Y-m-d H:i:s') . "', invoice_file_path='" . $fullpathInvoice . "', created_by='" . $created_user_id . "'");

                                $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=4, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session . "', schedule_payment_date = '" .$val->schedule_payment_date. "'");
                            }
                            $count++;
                        }
                        if ($count === count((array)$schedule_item_student)) {
                            $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where sfi.session = '" . $current_session . "' and sft.payment_txns_id='" . $payment_txns_id . "' GROUP BY s.user_id ");
                            $child_name = "";
                            foreach ($trxn_child_names as $row) {
                                $child_name .= $row->first_name . ", ";
                            }

                            $emailbody_support = "Assalamualaikum ,<br>";
                            if ($PaymentStatus == 1) { //---------Success Condition---------------//
                                $emailbody_support .= "Your payment for ".CENTER_SHORTNAME." ".SCHOOL_NAME." is Confirmed";
                            } elseif ($PaymentStatus == 2) { //---------------failed Condition------------// 
                                $emailbody_support .= "Your payment for ".CENTER_SHORTNAME." ".SCHOOL_NAME." was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
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
                                            <td style="width: 75%; text-align:left;">' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '
                                            </td></tr>

                                        <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                                            <td style="width: 75%; text-align:left;">' . internal_phone_check($family_data->father_phone) . '
                                            </td></tr>

                                        <tr><td style="width: 25%;" class="color2">Email:</td>
                                            <td style="width: 75%; text-align:left;"> ' . $family_data->primary_email . ' </td></tr>

                                        <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                                            <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
                                            </td></tr>

                                        <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                            <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                                            </td></tr>
                                        
                                        <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                                <td style="width: 75%; text-align:left;"> $' . $family_data->total_amount . '</td></tr>

                                            <tr><td style="width: 25%;" class="color2">Payment Date:</td>
                                                <td style="width: 75%; text-align:left;">' . date('Y-m-d H:i A') . '
                                                    </td></tr>';
                            if (!empty($transactionID)) {
                                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Credit Card Payment Transaction ID:</td>
                                    <td style="width: 75%; text-align:left;">' . $transactionID . '
                                    </td></tr>';
                            }
                            if (!empty($payment_account_entries_id)) {
                                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Wallet Payment Transaction ID:</td>
                                    <td style="width: 75%; text-align:left;">wtx_' . md5($payment_account_entries_id) . '
                                    </td></tr>';
                            }


                            $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                            <td style="width: 75%; text-align:left;">' . $trxn_msg . '
                                            </td></tr>
                                        
                                            </table>
                                            </td>
                                        </tr>         
                                    </table>';
                            $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                            $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                            $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";

                            //---------------Mail Send By MAil Service Start------------//
                            $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                            $email_subject =CENTER_SHORTNAME.' '.SCHOOL_NAME .  ' ' . $PaymentConfirmDecliendMSG;

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
                                'bcc_email' => $bcc_email
                            );
                            mailservice($mail_service_array);

                            //---------------Mail Send By MAil Service End------------//
                            $db->query("insert into ss_bulk_sms set session = '" . $current_session . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                            $message_id = $db->insert_id;
                            $father_phone = str_replace("-", "", $family_data->father_phone);
                            $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");
                            $db->query('COMMIT');
                            echo "success";
                        } else {
                            $db->query('ROLLBACK');
                            $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                            CreateLog($_REQUEST, json_encode($return_resp));
                            echo json_encode($return_resp);
                        }
                    } else {
                        $db->query('ROLLBACK');
                        $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because payment credential wrong');
                        CreateLog($_REQUEST, json_encode($return_resp));
                        echo json_encode($return_resp);
                       
                    }
                }  //Wallet+CC



                    //Zero Payment Proccess
                }  else {

                $schedule_item_student = $db->get_results("SELECT sfi.id, family_id 
											FROM `ss_student_fees_items` sfi 
											INNER JOIN ss_student s ON s.`user_id` = sfi.`student_user_id`  
											INNER JOIN ss_user u ON u.id = s.user_id 
											WHERE schedule_payment_date = '" . date('Y-m-d') . "' AND sfi.session = '" . $current_session . "' 
                                            AND sfi.original_schedule_payment_date = '" . $family_data->original_schedule_payment_date . "'
                                            AND schedule_status = 0 AND family_id = '" . $row->family_id . "' AND u.is_active=1 AND u.is_deleted=0");

                             $transactionID = "qds_" . uniqid() . "-" . uniqid();
                             $payment_status = 1;
                             $schedule_item_student_ids = "";
                            foreach ($schedule_item_student as $val) {
                                $student_fees_item_id .= $val->id . ','; //for transactions summary
                            }
                    //---------------------PAyment summary Entry In Summary Table Start-------------------//
                    $txn_summry[] = array("name" => $family_data->father_first_name . ' ' . $family_data->father_last_name, "email" => $family_data->primary_email, "phone" => $family_data->father_phone, "raw_data" => json_encode(array("mesage" => $transactionID, "debit" => '0 Amount')), "family_user_id" => $family_data->user_id, "student_fees_item_id" => rtrim($student_fees_item_id, ','), "payment_status" => $payment_status);
                    //---------------------PAyment summary Entry In Summary Tabl End-------------------//

                $payment_txns = $db->query("insert into ss_payment_txns set automatic=1, 
                                        payment_credentials_id='" . $family_data->payment_credential_id . "', 
                                        payment_gateway='forte',  payment_status=1, payment_unique_id='" . $transactionID . "', 
                                        payment_response_code='" . $payment_gateway_code . "', session = '" . $current_session . "',
                                        payment_date='" . date('Y-m-d H:i') . "' ");

                $payment_txns_id = $db->insert_id;
                $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";
                $count = 0;
                //----------------------------------Invoice--------------------------------//
                $invoce_id = mt_rand();
                include_once "../payment/invoice_pdf.php";
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                // (Optional) Setup the paper size and orientation 
                $dompdf->setPaper('Executive', 'Potrate');
                // Render the HTML as PDF 
                $dompdf->render();
                $path_Invoice = '../payment/invoice_and_pdf';
                $filename_Invoice = "/" . $invoce_id;
                $output_Invoice = $dompdf->output();
                $fullpathInvoice = $invoce_id . 'invoice.pdf';
                file_put_contents($path_Invoice . $filename_Invoice . 'invoice.pdf', $output_Invoice);
                unset($dompdf);

                //-------------------------------------Receipt--------------//
                $receipt_id = mt_rand();
                include_once "../payment/receipt_pdf.php";
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
                unset($dompdf);

                foreach ($schedule_item_student as $val) {
                    $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $val->id . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . date('Y-m-d H:i') . "'");

                    $student_fees_items = $db->query("update ss_student_fees_items set session = '" . $current_session . "', schedule_status = 1, updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $val->id . "' ");
                    $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where student_item_id = '" . $val->id . "' limit 1 ");
                    if ($Student_invoice_Id > 0) {
                        $db->query("update ss_invoice set receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "' where student_item_id = '" . $val->id . "' ");
                    } else {
                        $db->query("insert into ss_invoice set student_item_id= '" . $val->id  . "',user_id= '" . $val->user_id . "',amount= '" . $family_data->total_amount . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "',invoice_file_path='" . $fullpathInvoice . "',receipt_id = '" . $receipt_id . "', receipt_date = '" . date('Y-m-d') . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "', created_at = '" . date('Y-m-d H:i:s') . "', created_by='" . $created_user_id . "' ");
                    }

                    $sql_ret = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=1, comments='" . $comments . "', session = '" . $current_session . "',created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', schedule_payment_date = '" .$val->schedule_payment_date. "'  ");

                    $count++;
                }


                if ($count === count((array)$schedule_item_student)) {

                    $trxn_child_names = $db->get_results("select s.first_name from ss_student_fees_transactions sft
						INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
						INNER JOIN ss_student s ON sfi.student_user_id = s.user_id where sfi.session = '" . $current_session . "' 
                        AND sft.payment_txns_id='" . $payment_txns_id . "' GROUP BY s.user_id ");
                    $child_name = "";
                    foreach ($trxn_child_names as $row) {
                        $child_name .= $row->first_name . ", ";
                    }

                    $full_amount = $family_data->total_amount;
                    //$emailbody_support.= "Assalamualaikum ,<br>";
                    $emailbody_support = "Assalamualaikum ,<br>";
                    $emailbody_support .= "Your payment for ".CENTER_SHORTNAME." ".SCHOOL_NAME." is Confirmed";
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

                    <tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                        <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
                        </td></tr>
            
                    <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                        <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                        </td></tr>
                    
                    <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                        <td style="width: 75%; text-align:left;"> '.$currency . $full_amount . '</td></tr>

                    <tr><td style="width: 25%;" class="color2">Payment Date:</td>
                        <td style="width: 75%; text-align:left;">' . date('Y-m-d H:i A') . '
                            </td></tr>

                    <tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                    <td style="width: 75%; text-align:left;">' . $transactionID . '
                    </td></tr>

                    <tr><td style="width: 25%;" class="color2">Payment Status:</td>
                        <td style="width: 75%; text-align:left;"> Payment Successful
                        </td></tr>
                    
                        </table>
                        </td>
                    </tr>         
                    </table>';
                    $emailbody_support .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
                    $emailbody_support .= "<br><br>For any comments or question, please send email to <a href='" . SCHOOL_GEN_EMAIL . "'> " . SCHOOL_GEN_EMAIL . " </a>";
                    $emailbody_support .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . SCHOOL_NAME . ".";
                    //---------------Mail Send By MAil Service Start------------//
                    $emailbody = trim(preg_replace('/\s+/', ' ', $emailbody_support));
                    $email_subject = CENTER_SHORTNAME.' '.SCHOOL_NAME . ' - Payment Confirmation';

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
                        'bcc_email' => $bcc_email
                    );
                    mailservice($mail_service_array);

                    //---------------Mail Send By MAil Service End------------//
                    $message = str_replace("[AMOUNT]", $currency.$full_amount, PAYMENT_SUCCESS);
                    $db->query("insert into ss_bulk_sms set session = '" . $current_session . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                    $message_id = $db->insert_id;
                    $father_phone = str_replace("-", "", $family_data->father_phone);
                    $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");

                    $db->query('COMMIT');
                    echo "success";
                } else {

                    $db->query('ROLLBACK');
                    $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                    CreateLog($_REQUEST, json_encode($return_resp));
                    echo json_encode($return_resp);
                    
                }
            } 
            } // check  $Txnx_summary 
            else {

                echo "Payment already done";
            }
        }
    } catch (Exception $e) {
        $db->query('ROLLBACK');
        $msg = $e->getMessage();
        CreateLog($_REQUEST, json_encode($msg));
        echo json_encode($msg);
    }
    if (!empty($txn_summry)) {
        foreach ($txn_summry as $value) {
            $db->query("INSERT INTO `ss_txn_summary`(`name`, `email`, `phone`, `raw_data`, `family_user_id`, `student_fees_item_id`, `payment_status`, `created_dt`) VALUES ('" . $value['name'] . "','" . $value['email'] . "','" . $value['phone'] . "','" . $value['raw_data'] . "','" . $value['family_user_id'] . "','" . $value['student_fees_item_id'] . "','" . $value['payment_status'] . "','" . date('Y-m-d H:i:s') . "' ) ");
        }
    }
}
//}
