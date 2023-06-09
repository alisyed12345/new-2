<?php
include_once "../includes/config.php";
require_once '../includes/dompdf/autoload.inc.php';
ob_clean();
define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;

$account_entries_id = $_GET['account_entries_id'];
$payment_txns_id = $_GET['payment_txns_id'];
//if (!empty($user_id)) {

if (isset($_GET['account_entries_id']) && !empty($_GET['account_entries_id']) && $_GET['account_entries_id'] != 'null' && isset($_GET['payment_txns_id']) && !empty($_GET['payment_txns_id']) && $_GET['payment_txns_id'] != 'null') {
    $user_payment_txn = $db->get_row("SELECT sft.payment_txns_id, ptx.payment_unique_id, ptx.payment_date, sum(sfi.amount) as final_amount, f.father_first_name,  f.father_last_name, f.father_phone, f.primary_email, s.family_id, s.user_id, pay.credit_card_no, ptx.is_payment_type
        FROM ss_payment_txns ptx  
        INNER JOIN ss_student_fees_transactions sft ON sft.payment_txns_id = ptx.id 
        INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
        INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
        INNER JOIN ss_user u ON u.id = s.user_id
        INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
        INNER JOIN ss_family f ON f.id = s.family_id
        INNER JOIN ss_paymentcredentials pay ON pay.id = ptx.payment_credentials_id
        WHERE sft.payment_txns_id = '" . $payment_txns_id . "' AND u.is_deleted = 0 ");
    // }
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
} elseif (isset($_GET['payment_txns_id']) && !empty($_GET['payment_txns_id']) && $_GET['payment_txns_id'] != 'null') {
    $user_payment_txn = $db->get_row("SELECT sft.payment_txns_id, ptx.payment_unique_id, ptx.payment_date, sum(sfi.amount) as final_amount, f.father_first_name,  f.father_last_name, f.father_phone, f.primary_email, s.family_id, s.user_id, pay.credit_card_no, ptx.is_payment_type
    FROM ss_payment_txns ptx  
    INNER JOIN ss_student_fees_transactions sft ON sft.payment_txns_id = ptx.id 
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sft.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    INNER JOIN ss_user u ON u.id = s.user_id
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
    INNER JOIN ss_family f ON f.id = s.family_id
    INNER JOIN ss_paymentcredentials pay ON pay.id = ptx.payment_credentials_id
    WHERE sft.payment_txns_id = '" . $payment_txns_id . "' AND u.is_deleted = 0 ");
    // }
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
    $walletno = "";
} elseif (isset($_GET['account_entries_id']) && !empty($_GET['account_entries_id']) && $_GET['account_entries_id'] != 'null') {
    $user_payment_txn = $db->get_row("SELECT sfvt.id as virtual_transactions_id, pae.id as account_entries_id, pae.created_on, sum(sfi.amount) as final_amount, f.father_first_name,  f.father_last_name, f.father_phone, f.primary_email, s.family_id, s.user_id
    FROM ss_payment_account_entries pae  
    INNER JOIN ss_student_fees_virtual_transactions sfvt ON sfvt.payment_account_entries_id = pae.id 
    INNER JOIN ss_student_fees_items sfi ON sfi.id = sfvt.student_fees_item_id 
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    INNER JOIN ss_user u ON u.id = s.user_id
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
    INNER JOIN ss_family f ON f.id = s.family_id
    WHERE pae.id = '" . $account_entries_id . "' AND u.is_deleted = 0 GROUP BY pae.id");
    // }
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
if (!empty(get_country()->currency)) {
    $currency = get_country()->currency;
} else {
    $currency = '';
}

$final_amount = $currency . ($user_payment_txn->final_amount + 0);
$child_name = rtrim($child_name, ', ');
// $current_date = date('m/d/Y H:i A');
$current_date = my_date_changer(date('m/d/Y H.i'), 't');
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
    </style>
</head>

<body>
    <table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
            <tr>
                <td style="width:60%;text-align: left;vertical-align:top;padding-top:20px">
                    <div style="font-size: 20px;" class="color2">' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . '</div>
                    <div style="font-size: 12px; margin-top:5px;">
                    ' . SCHOOL_ADDRESS . ', ' . SCHOOL_ADDRESSCITY . ', ' . SCHOOL_ADDRESSSTATE . ' - ' . SCHOOL_ADDRESSZIPCODE . '
                    </div>
                    <div style="font-size: 12px; margin-top:4px;"><strong>Phone:</strong> ' . SCHOOL_CONTACT_NO . ' </div>
                    <div style="font-size: 12px; margin-top:3px;"><strong>Email:</strong> ' . SCHOOL_GEN_EMAIL . ' </div>
                </td>
                <td style="width:50%; text-align:right;padding-top:10px">
                    <img src="' . image_binary(LOGO) . '" style="vertical-align:top;margin-right: 2%;">
                </td>
            </tr> 
            <tr>
                <td colspan="2" style="text-align: center;">
                    <div style="font-size: 18px;margin-top:30px; text-align:center;"><u>PAYMENT RECEIPT</u></div>
                </td>
            </tr>   
            <tr>
                <td colspan="2" style="text-align: left; padding-top:30px">
                    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="10">
                        <tr>
                            <td style="width: 25%;" class="color2">Parent Name:</td>
                            <td style="width: 75%; text-align:left;">' . $user_payment_txn->father_first_name . ' ' . $user_payment_txn->father_last_name . '</td>
                        </tr>
                        <tr>
                            <td style="width: 25%;" class="color2">Phone Number:</td>
                            <td style="width: 75%; text-align:left;">' . $user_payment_txn->father_phone . '</td>
                        </tr>
                        <tr>
                            <td style="width: 25%;" class="color2">Email:</td>
                            <td style="width: 75%; text-align:left;"> ' . $user_payment_txn->primary_email . ' </td>
                        </tr>
                        <tr>
                            <td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                            <td style="width: 75%; text-align:left;">' . $child_name . '</td>
                        </tr> ';

if (!empty($credit_card_no)) {
    $html .= ' <tr>
                                            <td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                            <td style="width: 75%; text-align:left;">' . $user_payment_txn->payment_unique_id . '</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                                            <td style="width: 75%; text-align:left;">' . $credit_card_no . '</td>
                                        </tr>
                                    ';
} elseif (!empty($walletno)) {
    $html .= ' <tr>
                            <td style="width: 25%;" class="color2">Wallet Payment Transaction ID:</td>
                            <td style="width: 75%; text-align:left;">' . $walletno . '</td>
                            </tr>';
}
if (!empty($user_payment_txn->is_payment_type) && ($user_payment_txn->is_payment_type > 0)) {
    if ($user_payment_txn->is_payment_type == 1) {
        $is_type = 'Registration Payment';
    } elseif ($user_payment_txn->is_payment_type == 2) {
        $is_type = 'Registration Payment';
    } elseif ($user_payment_txn->is_payment_type == 3) {
        $is_type = 'Schedule Payment';
    } elseif ($user_payment_txn->is_payment_type == 4) {
        $is_type = 'Manual Payment';
    } elseif ($user_payment_txn->is_payment_type == 5) {
        $is_type = 'Wallet Payment';
    } elseif ($user_payment_txn->is_payment_type == 6) {
        $is_type = 'Refund Payment';
    }
    $html .= '<tr><td style="width: 25%;" class="color2">Payment Type:</td>
    <td style="width: 75%; text-align:left;">' . $is_type . '
    </td></tr>';
}
$html .= ' <tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                            <td style="width: 75%; text-align:left;">' . $final_amount . '</td>
                        </tr>
                        <tr>
                            <td style="width: 25%;" class="color2">Payment Date:</td>
                            <td style="width: 75%; text-align:left;">' . $payment_date . '</td>
                        </tr>
                        <tr>
                            <td style="width: 25%;" class="color2">Receipt Issue Date:</td>
                            <td style="width: 75%; text-align:left;">' . $current_date . '</td>
                        </tr>
                    </table>
                </td>
            </tr>          
            <tr>
                <td colspan="2" style="padding-top:50px;">
                    Sincerly,
                    <p>' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . '</p>
                </td>
            </tr>
    </table>
    <div style="position:absolute;width:100%;bottom:0; text-align:center; border-top:solid 1px #333; padding-top:15px"> 
        <div style="font-size:17px; margin-bottom:10px" class="color2">Thank you!</div>
        <div>For any comment/question send email to ' . EMAIL_FROM . '</div>
    </div>
</body>
</html>';

$filename = "receipt.pdf";
try {
    //reference the Dompdf namespace
    //use Dompdf\Dompdf;
    //instantiate and use the dompdf class
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('Executive', 'Potrate');
    // Render the HTML as PDF
    $dompdf->render();
    $output = $dompdf->output();
    // file_put_contents($pdfroot, $output);
    // Output the generated PDF to Browser
    $dompdf->stream($filename);
    return $dompdf;
} catch (Exception $e) {
    echo $e;
}



exit();
