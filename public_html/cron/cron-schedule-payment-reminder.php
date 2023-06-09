<?php
//LIVE - QA SITE
//set_include_path('/home3/bayyanor/public_html/ick/academy_new/includes/');

//Devlopment - QA SITE
// set_include_path('/webroot/b/a/bayyan001/icksatqa.magesticflyer.com/www/includes/');

// include_once "config.php";
// include_once 'dompdf/autoload.inc.php'; 

//Development - LOCALHOST SITE
include_once "../includes/config.php";
include_once '../includes/dompdf/autoload.inc.php';

/* if (ENVIRONMENT == 'dev') {
    $reminderdate = "sfi.schedule_payment_date";
} else {
    $reminderdate = "DATE_SUB(sfi.schedule_payment_date, INTERVAL '" . $SCHEDULE_PAYMENT_EMAIL_REMINDER . "' DAY)   ";
}

echo $reminderdate; */

use Dompdf\Dompdf;

if (!empty(get_country()->currency)) {
    $currency = get_country()->currency;
} else {
    $currency = '';
}
$current_dateTime = date('Y-m-d H:i');
$payment_start_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_START_TIME;
$payment_end_dateTime = date('Y-m-d') . ' ' . CRON_PAYMENT_END_TIME;

//payments to run condition
if ($payment_start_dateTime < $current_dateTime &&  $payment_end_dateTime > $current_dateTime) {

    $SCHEDULE_PAYMENT_EMAIL_REMINDER = SCHEDULE_PAYMENT_EMAIL_REMINDER;

    $created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
WHERE t.user_type_code = 'UT00'  limit 1 ");

    $current_session = $db->get_row("select * from ss_school_sessions where current = 1 AND status = 1");

    $x = 0;
    while ($x <= $SCHEDULE_PAYMENT_EMAIL_REMINDER) {
        //before Remainder1
        $Query = "SELECT GROUP_CONCAT(sfi.id) as sch_item_id, GROUP_CONCAT(s.first_name,' ',s.last_name) as name, GROUP_CONCAT(s.user_id) as user_id,sfi.schedule_unique_id,sfi.schedule_status, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date,SUM(sfi.amount) AS final_amount, s.family_id, f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, f.billing_address_1, f.billing_address_2,pay.forte_payment_token,f.forte_customer_token, pay.credit_card_no FROM 
ss_student_fees_items sfi
INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
INNER JOIN ss_user u ON u.id = s.user_id
INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
INNER JOIN ss_family f ON f.id = s.family_id
INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
WHERE sfi.session='" . $current_session->id . "' AND u.is_deleted = 0 AND u.is_locked=0 AND u.is_active=1 AND pay.default_credit_card =1 AND sfi.schedule_notification = 0 AND sfi.schedule_status = 0 AND CURDATE() = DATE_SUB(sfi.schedule_payment_date, INTERVAL '" . $x . "' DAY)
group by sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.original_schedule_payment_date ASC";

        $remainder = $db->get_results($Query);

        if (count((array)$remainder) > 0) {
            $html_body = "";
            $full_view = "";
            $count = 0;
            $final_amount = 0;
            $student_name = '';
            $student_fees_item_id = "";
            foreach ($remainder as $remind) {
                $paymentcheck = $db->get_var("SELECT schedule_unique_id FROM `ss_payment_sch_item_cron` WHERE schedule_unique_id = '" . $remind->schedule_unique_id . "' ");
                if (empty($paymentcheck)) {
                    $final_amount = $remind->final_amount;
                    $student_name = $remind->name .  ",";
                    //-------------Check  customer token,payment token,total amount Start------------//
                    if (!empty($remind->forte_customer_token) && !empty($remind->forte_payment_token)) {
                        $payment_sch_item_cron = $db->query("INSERT INTO `ss_payment_sch_item_cron`(`schedule_unique_id`,`family_id`, `sch_item_ids`, `schedule_payment_date`,`total_amount`,`session`, `is_approval`,`created_at`) VALUES ('" . $remind->schedule_unique_id . "','" . $remind->family_id . "','" . $remind->sch_item_id . "','" . $remind->schedule_payment_date . "','" . $final_amount . "','" . $current_session->id . "','1','" . date('Y-m-d H:i:s') . "')");
                        $payment_last_inserted = $db->insert_id;
                        if ($payment_last_inserted > 0) {
                            $invoce_id = mt_rand();
                            include "../payment/invoice_pdf.php";
                            define("DOMPDF_UNICODE_ENABLED", true);
                            $dompdf = new Dompdf();
                            $dompdf->loadHtml($html);/// this variable is coming from the line include "../payment/invoice_pdf.php";

                            // (Optional) Setup the paper size and orientation 
                            $dompdf->setPaper('Executive', 'Potrate');

                            // Render the HTML as PDF 
                            $dompdf->render();
                            $path = '../payment/invoice_and_pdf';
                            $filename = "/" . $invoce_id;
                            $output = $dompdf->output();
                            $fullpath = $invoce_id . 'invoice.pdf';
                            file_put_contents($path . $filename . 'invoice.pdf', $output);
                            unset($dompdf);
                            $file_path = SITEURL . 'payment/invoice_and_pdf/' . $fullpath;
                            //--------------Invoice Insert-----------//
                            /*  $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where schedule_unique_id = '" . $remind->schedule_unique_id . "' ");
                        if (!empty($Student_invoice_Id)) {
                            $db->query("update ss_invoice set invoice_id = '" . $invoce_id . "', invoice_date = '" . date('Y-m-d') . "', is_due = '1', invoice_file_path = '" . $fullpath . "' where schedule_unique_id = '" . $remind->schedule_unique_id . "' ");
                        } else {
                        } */
                            $db->query("insert into ss_invoice set  family_id='" . $remind->family_id . "',schedule_unique_id='" . $remind->schedule_unique_id . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "', amount='" . $final_amount . "', is_due='0', status = '1', created_at='" . date('Y-m-d H:i:s') . "', invoice_file_path='" . $fullpath . "', is_type = 3, created_by='" . $created_user_id . "'");
                            $last_invoice_id = $db->insert_id;
                            $db->query("insert into ss_invoice_info set invoice_id = '" . $last_invoice_id . "', created_at = '" . date('Y-m-d h:i:s') . "'");

                            $star = '************';
                            $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($remind->credit_card_no)), -4);
                            $emailbody_support = "Assalamualaikum, <strong> " . $remind->father_first_name . " " . $remind->father_last_name . "</strong><br>";
                            $emailbody_support .= "This is a reminder that your payment of  " . $currency . $final_amount . ",is due for on " . my_date_changer($remind->schedule_payment_date, 'c') . " We will process your credit card ending with " . $credit_card_no . " on the due date.";

                            $emailbody_support_last = '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME . ' Team';

                            $emailbody_support_last .= "<br><br>For any comments or question, please send email to " . SUPPORT_EMAIL . "";
                            $subject = CENTER_SHORTNAME . " " . SCHOOL_NAME . "  Payment Reminder: Due  " . my_date_changer($remind->schedule_payment_date, 'c');

                            $emailbody_support_last;
                            $mail_service_array = array(
                                'subject' => $subject,
                                'message' => $emailbody_support . "<br>" . $html . "<br>" . $emailbody_support_last,
                                'request_from' => MAIL_SERVICE_KEY,
                                'attachment_file_name' => [$filename . 'invoice.pdf'],
                                'attachment_file' => $file_path,
                                'to_email' => [$remind->primary_email],
                                //'to_email' => ['krishn.patel@quasardigital.com'],
                                'cc_email' => [],
                                'bcc_email' => []
                            );

                            mailservice($mail_service_array);

                            $remainder1 =  $db->query("update ss_student_fees_items set schedule_notification = 1 where FIND_IN_SET(id,'$remind->sch_item_id')");

                            $html_body .= "<tr>
                    <td>" . ($count + 1) . "</td>
                    <td>" . $remind->father_first_name . " " . $remind->father_last_name . "</td>
                    <td>" . internal_phone_check($remind->father_phone) . "</td>
                    <td>" . $remind->primary_email . "</td>
                    <td>" . my_date_changer($remind->schedule_payment_date) . "</td> 
                    <td>" . $currency . $remind->final_amount . "</td>
                    <td>" . $credit_card_no . "</td>
                </tr> ";
                            $count++;
                            echo "Success";
                        } else {
                            $return_resp = array('code' => "0", 'msg' => 'Family Id : ' . $remind->family_id . ', Dear ' . $remind->father_first_name . ' ' . $remind->father_last_name . ' Schedule Reminder Processed failed because data not insert in database');
                            CreateLog($_REQUEST, json_encode($return_resp));
                        }
                    } else {
                        $return_resp = array('code' => "0", 'msg' => 'Family Id : ' . $remind->family_id . ', Dear ' . $remind->father_first_name . ' ' . $remind->father_last_name . ' Schedule Reminder Processed failed because payment credential wrong');
                        CreateLog($_REQUEST, json_encode($return_resp));
                    }
                }
            }

            //-----------------------------PRINCIPLE REPORT MAIL HTML-----------------------//  
            if (!empty($html_body)) {

                $path = image_binary(SCHOOL_LOGO);
                $html = '<!DOCTYPE html>
            <html lang="en" moznomarginboxes mozdisallowselectionprint>

            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width initial-scale=1.0">
                <style type="text/css" media="all">
                body {
                    font-family: "Helvetica";
                    font-size: 14px;
                    color: #333;
                }

                .page-break {
                    page-break-after: always;
                }

                .color1{
                    color:#0e772e;
                }

                .color2{
                    color:#863d11;
                }

                #customers td, #customers th {
                    border: 1px solid #ddd;
                    padding: 15px;
                }

                </style>
            </head>

            <body>

    <table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
        <tr>
            <td style="width:60%;text-align: left;vertical-align:top;padding-top:20px">
                <div style="font-size: 20px;" class="color2">' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . '
                </div>
                <div style="font-size: 12px; margin-top:5px;">
                    ' . SCHOOL_ADDRESS . '
                </div>
                <div style="font-size: 12px; margin-top:4px;"><strong>Phone:</strong>  ' . SCHOOL_CONTACT_NO . '</div>
                <div style="font-size: 12px; margin-top:3px;"><strong>Email:</strong>  ' . SCHOOL_GEN_EMAIL . '</div>
            </td>
            <td style="width:40%; text-align:right;padding-top:10px">
                <img src="' . $path . '" style="max-width: 150px; max-height: 80px;
                vertical-align:top;">
            </td>
        </tr> 
    </table>

    <table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
        <tr>
            <td colspan="2" style="text-align: center;">
                <div style="font-size: 18px;margin-top:30px; text-align:center;"><u>PAYMENT REMINDER REPORT</u></div>
            </td>
        </tr> 
    </table>
        <br>
        <table  id="customers" style="width:100%;" cellpadding="0" >

        <thead>
        <tr>
            <th style="text-align: left;">S. NO</th>
            <th style="text-align: left;">Parent Name</th>
            <th style="text-align: left;">Parent Phone</th>
            <th style="text-align: left;">Parent Email</th>
            <th style="text-align: left;">Payment Date</th>
            <th style="text-align: left;">Payment Due Amount</th>
            <th style="text-align: left;">CC Last Digits</th>
        </tr>
        </thead>
        <tbody> ';


                $html_footer = ' </tbody>
        </table>
        </body>
        </html>';

                $full_view = $html . $html_body . $html_footer;

                $dompdf = new Dompdf();
                $dompdf->load_html($full_view);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $output = $dompdf->output();
                $path = '../payment/payment_remainder_report/';
                $filename = date('m-d-Y', strtotime($remainder[0]->schedule_payment_date)) . '-' . uniqid() . ".pdf";
                file_put_contents($path . $filename, $output);
                unset($dompdf);

                $file_http_path = SITEURL . 'payment/payment_remainder_report/' . $filename;

                $subject = CENTER_SHORTNAME . " "  . SCHOOL_NAME . " Payment Reminder Report: Due " . my_date_changer($remainder[0]->schedule_payment_date, 'c');
                $message = "<p>Hello Admin,</p><p>We received the following information from schedule payment.</p><p>Please find the payment reminder report attached with this mail.</p>";
                $message .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME . ' Team';
                $mail_service_array = array(
                    'subject' => $subject,
                    'message' => $message,
                    'request_from' => MAIL_SERVICE_KEY,
                    'attachment_file_name' => $filename,
                    'attachment_file' => $file_http_path,
                    'to_email' => [SCHOOL_GEN_EMAIL],
                    'cc_email' => [],
                    'bcc_email' => []
                );

                mailservice($mail_service_array);
            }

            //-----------------------------PRINCIPLE REPORT MAIL HTML-----------------------//
        }
        $x++;
    }
}
