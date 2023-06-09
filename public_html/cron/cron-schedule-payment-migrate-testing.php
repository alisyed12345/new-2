<?php
//LIVE - PROD SITE
//set_include_path('/home3/bayyanor/public_html/ick/summercamp/includes/');

//LIVE - QA SITE
//set_include_path('/home3/bayyanor/public_html/ick/academy_new/includes/');

//Devlopment - QA SITE
// set_include_path('/webroot/b/a/bayyan005/icksaturdayqa.click2clock.com/www/includes/');


// include_once "config.php";
// include_once "FortePayment.class.php";

include_once "../includes/config.php";
include_once "../includes/FortePayment.class.php";
include_once '../includes/dompdf/autoload.inc.php';
define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;
//CURRENT SESSION
$current_session_row = $db->get_row("select * from ss_school_sessions where current = 1 ");
$current_session = $current_session_row->id;


$current_dateTime = date('Y-m-d H:i');
$payment_start_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_START_TIME;
$payment_end_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_END_TIME;

//payments to run condition
// if($payment_start_dateTime < $current_dateTime &&  $payment_end_dateTime > $current_dateTime){

$student_fees_items = $db->get_results("SELECT sfic.id,sfic.schedule_unique_id,sch_item_ids, sfic.schedule_payment_date,sfic.total_amount,sfic.cc_amount,sfic.wallet_amount,sfic.payment_unique_id,sfic.payment_response_code,sfic.payment_response,sfic.updated_at,sfic.reason, 
sfic.schedule_status, sfic.family_id, f.user_id AS family_user_id, 
 f.father_first_name, f.father_last_name, f.father_phone, f.primary_email,
f.secondary_email, f.billing_city, f.billing_post_code, f.forte_customer_token,pay.credit_card_no, 
pay.forte_payment_token, pay.id AS payment_credential_id,sfic.session
FROM `ss_payment_sch_item_cron` sfic
INNER JOIN ss_family f ON f.id = sfic.family_id
INNER JOIN ss_paymentcredentials pay ON pay.family_id = f.id
WHERE sfic.schedule_status <> 0 AND sfic.session = '" . $current_session . "' AND pay.default_credit_card =1 AND is_approval='1';");
if (count((array)$student_fees_items) > 0) {
    $db->query('BEGIN');
    try {

        //$created_user_id = $_SESSION['icksumm_uat_login_userid'];

        $created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
        INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
        INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
        WHERE t.user_type_code = 'UT00'  limit 1 ");

        $SchoolAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='ick_school' limit 1 ");
        $CCAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `account_holder`='CC' limit 1 ");

        $get_email = $db->get_row("select new_registration_email_bcc, new_registration_email_cc, new_registration_session, registration_page_termsncond,reg_form_term_cond_attach_url from ss_client_settings where status = 1");
        if (!empty($get_email->new_registration_email_bcc)) {
            $emails_bcc = explode(",", $get_email->new_registration_email_bcc);
        }
        if (!empty($get_email->new_registration_email_cc)) {
            $emails_cc = explode(",", $get_email->new_registration_email_cc);
        }

        foreach ($student_fees_items as $family_data) {
            
            $star = '************';
            $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($family_data->credit_card_no)), -4);
            $schedule_item_student = explode(',', $family_data->sch_item_ids);

            $trxn_child_names = $db->get_results("SELECT GROUP_CONCAT(s.first_name,' ',s.last_name) as name FROM ss_student_fees_items sfi INNER JOIN ss_student s ON sfi.student_user_id = s.user_id WHERE FIND_IN_SET(sfi.id,'" . $family_data->sch_item_ids . "'); ");
            $child_name = $trxn_child_names[0]->name;

            //--------------------------Family Account id------------------//
            $FamilyAccountId = $db->get_var("SELECT id FROM `ss_payment_accounts` WHERE `user_id`='" . $family_data->family_user_id . "' ORDER by id DESC limit 1 ");
            $full_amount = '$' . $family_data->total_amount;

            if ($family_data->total_amount > 0) {

                //-------check condition for Amount debit from Credit Card,Wallet and Credit Card +  Wallet---------//
                if ($family_data->cc_amount > 0 && $family_data->wallet_amount > 0) {
                    $AmountDebitFrom = "CreditCard_Wallet";
                } else if ($family_data->cc_amount > 0) {
                    $AmountDebitFrom = "CreditCard";
                } elseif ($family_data->wallet_amount > 0) {
                    $AmountDebitFrom = "Wallet";
                }

                if ($AmountDebitFrom == 'CreditCard' || $AmountDebitFrom == 'CreditCard_Wallet') {
                    $transactionID = $family_data->payment_unique_id;
                    if (!empty($family_data->payment_unique_id) && strtoupper($family_data->payment_response_code) == "A01") {
                        $PaymentStatus = 1; //Success
                        $PaymentConfirmDecliendMSG = "Payment Confirmation";
                        $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";
                        $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                        $schedule_status = '1';
                        $new_status = '1';
                        $payment_statustxn = '1';
                        //---This Entry For Manage Account Credit /Debit -> Amount Entries From CC TO family Account ---//
                        $AmountTransferedcCCToFamily = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Amount Transfered From Credit Card To Family Virtual Account','" . $family_data->cc_amount . "','" . $CCAccountId . "','" . $FamilyAccountId . "','" . $family_data->updated_at . "') ");
                        $AmountTransferedcFamilyToSchool = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Amount Transfered From Family Virtual Account To ICK School Account','" . $family_data->cc_amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . $family_data->updated_at . "') ");
                    } else {
                        //---------------------------------Payment Failed Execution Start Here!-------------------------//
                        $PaymentStatus = 2; //Failed
                        $PaymentConfirmDecliendMSG = "Payment - DECLINED";
                        $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong> Pending <br> <strong>Current Status : </strong> Decline";
                        $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                        $schedule_status = '4';
                        $new_status = '4';
                        $payment_statustxn = '0';
                    }


                    //-----------------Amount Entry IN Transaction Table------------------//
                    $payment_txns = $db->query("insert into ss_payment_txns set session='" . $family_data->session . "', automatic=1, payment_credentials_id='" . $family_data->payment_credential_id . "', payment_gateway='forte', payment_status='" . $payment_statustxn . "', payment_unique_id='" . $transactionID . "', payment_response_code='" . $family_data->payment_response_code . "', payment_response='" . $family_data->payment_response . "', payment_date='" . $family_data->updated_at . "' ");
                    $payment_txns_id = $db->insert_id;
                }

                if ($AmountDebitFrom == 'Wallet' || $AmountDebitFrom == 'CreditCard_Wallet') {

                    if ($AmountDebitFrom == 'Wallet') {
                        //---Wallet Amount Entries From Family TO ICK School Account ---//
                        $payment_txns = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family account to School account','" . $family_data->wallet_amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                        $payment_account_entries_id = $db->insert_id;
                        if ($payment_account_entries_id > 0) {
                            $PaymentStatus = 1; //Success
                            $PaymentConfirmDecliendMSG = "Payment Confirmation";
                            $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";
                            $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                            $schedule_status = '1';
                            $new_status = '1';
                            $payment_statustxn = '1';
                        } else {
                            //---------------------------------Payment Failed Execution Start Here!-------------------------//
                            $PaymentStatus = 2; //Failed
                            $PaymentConfirmDecliendMSG = "Payment - DECLINED";
                            $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong> Pending <br> <strong>Current Status : </strong> Decline";
                            $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_FAILED);
                            $schedule_status = '4';
                            $new_status = '4';
                            $payment_statustxn = '0';
                        }
                    } elseif ($AmountDebitFrom == 'CreditCard_Wallet') {
                        if ($PaymentStatus == '1') {
                            //---Wallet Amount Entries From Family TO ICK School Account ---//
                            $payment_txns = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family account to School account','" . $family_data->wallet_amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $payment_account_entries_id = $db->insert_id;
                        } else {
                            //---Wallet Amount Entries From Family TO ICK School Account ---//
                            $AmountTransfered = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Transfered - Family account to School account','" . $family_data->wallet_amount . "','" . $FamilyAccountId . "','" . $SchoolAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $payment_account_entries_id = $db->insert_id;
                            //---Refund Wallet Amount-> Entries From ICK School  O Family Account ---//
                            $RefundAmountTransfered = $db->query("INSERT INTO `ss_payment_account_entries`(`description`, `amount`, `debit_pay_account_id`, `credit_pay_account_id`, `created_on`) VALUES ('Refunded - School account to Family account','" . $family_data->wallet_amount . "','" . $SchoolAccountId . "','" . $FamilyAccountId . "','" . date('Y-m-d H:i:s') . "') ");
                            $refund_payment_account_entries_id = $db->insert_id;
                        }
                    }
                }
            } else {
                $transactionID = "qds_" . uniqid() . "-" . uniqid();
                $PaymentStatus = 1; //Success
                $PaymentConfirmDecliendMSG = "Payment Confirmation";
                $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Success";
                $message = str_replace("[AMOUNT]", $full_amount, PAYMENT_SUCCESS);
                $schedule_status = '1';
                $new_status = '1';
                $payment_statustxn = '1';
                //-----------------Amount Entry IN Transaction Table------------------//
                $payment_txns = $db->query("insert into ss_payment_txns set session='" . $family_data->session . "', automatic=1, payment_credentials_id='" . $family_data->payment_credential_id . "', payment_gateway='forte', payment_status='" . $payment_statustxn . "', payment_unique_id='" . $transactionID . "', payment_response_code='" . $family_data->payment_response_code . "', payment_response='" . $family_data->payment_response . "', payment_date='" . $family_data->updated_at . "' ");
                $payment_txns_id = $db->insert_id;
            }


            //--------------Invoice Update(IF Exist) OR Insert-----------//
            $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where schedule_unique_id = '" . $family_data->schedule_unique_id . "' limit 1 ");
            if (empty($Student_invoice_Id)) {
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
                $invoice_update = $db->query("insert into ss_invoice set schedule_unique_id='" . $family_data->schedule_unique_id . "',family_id='" . $family_data->family_id . "', invoice_id='" . $invoce_id . "', invoice_date='" . $family_data->updated_at . "', amount='" . $family_data->total_amount . "', is_due='2', status = '1', created_at='" . $family_data->updated_at . "', invoice_file_path='" . $fullpathInvoice . "', created_by='" . $created_user_id . "'");
            }
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

                $invoice_update = $db->query("update ss_invoice set receipt_id = '" . $receipt_id . "', receipt_date = '" . $family_data->updated_at . "', is_due = '1', receipt_file_path = '" . $fullpathReceipt . "' where schedule_unique_id = '" . $family_data->schedule_unique_id . "' ");
            }

            $count = 0;
            foreach ($schedule_item_student as $val) {
                if ($AmountDebitFrom == 'CreditCard' || $AmountDebitFrom == 'CreditCard_Wallet' || $family_data->total_amount == '0') {
                    //--- insert student fees CC transactions ---//
                    $student_fees_transactions = $db->query("insert into ss_student_fees_transactions set student_fees_item_id='" . $val . "', payment_txns_id = '" . $payment_txns_id . "', created_at = '" . $family_data->updated_at . "'");
                }
                if ($AmountDebitFrom == 'Wallet' || $AmountDebitFrom == 'CreditCard_Wallet') {
                    //--- Entry student fees virtual transactions ---//
                    $student_fees_transactions = $db->query("insert into ss_student_fees_virtual_transactions set student_fees_item_id='" . $val . "', payment_account_entries_id = '" . $payment_account_entries_id . "', created_on = '" . date('Y-m-d H:i') . "'");
                }

                //--- Update student fees item ---//
                $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = '" . $schedule_status . "', updated_at = '" . $family_data->updated_at . "' where id = '" . $val . "' ");
                //--- insert student fees item status_history ---//
                $tudent_fees_item__history = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val . "' , current_status=0, new_status='" . $new_status . "', comments='" . $comments . "', created_at='" . $family_data->updated_at . "', created_by_user_id='" . $created_user_id . "', session ='" . $family_data->session . "', schedule_payment_date = '" .$family_data->schedule_payment_date. "'");

                $count++;
            }
            if ($count === count((array)$schedule_item_student)) {
                $emailbody_support = "Assalamualaikum ,<br>";
                if ($PaymentStatus == '1') { //---------Success Condition---------------//
                    $PaymentStatusMSG = "Payment Success";
                    $emailbody_support .= "Your payment for " . SCHOOL_NAME . " is Confirmed";
                } elseif ($PaymentStatus == '2') { //---------------failed Condition------------// 
                    $emailbody_support .= "Your payment for " . SCHOOL_NAME . " was declined. Please reach out to " . SCHOOL_GEN_EMAIL . " or call 913-390-5055 and leave a message.";
                    $PaymentStatusMSG = "Payment Failed";
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
                            <td style="width: 75%; text-align:left;">' . $family_data->father_phone . '
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
                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                    <td style="width: 75%; text-align:left;">' . $transactionID . '
                    </td></tr>';
                }
                if (!empty($payment_account_entries_id)) {
                    $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Wallet Payment Transaction ID:</td>
                    <td style="width: 75%; text-align:left;">wtx_' . md5($payment_account_entries_id) . '
                    </td></tr>';
                }


                $emailbody_support .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                            <td style="width: 75%; text-align:left;">' . $PaymentStatusMSG . '
                            </td></tr>
                        
                            </table>
                            </td>
                        </tr>         
                    </table>';
                $emailbody_support .= "<br><br>JazakAllah Khairan";
                $emailbody_support .= "<br" . SCHOOL_NAME;
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

                //---------------Mail Send By MAil Service End------------//
                $bulk_sms = $db->query("insert into ss_bulk_sms set session = '" . $family_data->session . "', message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "'");
                $message_id = $db->insert_id;
                $father_phone = str_replace("-", "", $family_data->father_phone);
                $bulk_sms_mobile = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $family_data->family_user_id . ", receiver_mobile_no = '" . $father_phone . "', delivery_status = 2, attempt_counter = 0");

                //----------------------Backup Payment Execute data ---------------//
                $backup_sch_cron = $db->query("INSERT INTO  ss_payment_sch_item_cron_backup SELECT * FROM  ss_payment_sch_item_cron WHERE id = " . $family_data->id . " ");
                //----------------------After Backup Delecte Executed data  ---------------//
                $delete_sch_cron = $db->query("delete from ss_payment_sch_item_cron where id =" . $family_data->id . " ");
                if ($payment_txns && $student_fees_transactions && $student_fees_items && $tudent_fees_item__history && $backup_sch_cron && $delete_sch_cron) {
                    $db->query('COMMIT');
                    mailservice($mail_service_array);
                    echo "success";
                } else {
                    $db->query('ROLLBACK');
                    $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                    CreateLog($_REQUEST, json_encode($return_resp));
                }
            } else {
                $db->query('ROLLBACK');
                $return_resp = array('code' => "0", 'msg' => 'Schedule payment Processed failed because data not insert in database');
                CreateLog($_REQUEST, json_encode($return_resp));
            }
        }
    } catch (Exception $e) {
        $db->query('ROLLBACK');
        $msg = $e->getMessage();
        CreateLog($_REQUEST, json_encode($msg));
        //echo json_encode($msg);
    }
}
//}
