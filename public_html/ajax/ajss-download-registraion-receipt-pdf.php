<?php 
// include autoloader
ob_start();
include_once "../includes/config.php";
require_once '../includes/dompdf/autoload.inc.php';
define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;
$get_student = $db->get_results("SELECT schreg.id as reg_id, schreg.father_first_name, schreg.father_last_name, schreg.father_phone, schreg.father_email, schreg.city, schreg.amount_received, txn.payment_unique_id, txn.comments, txn.payment_status, txn.payment_date, GROUP_CONCAT(CONCAT(' ',reg.first_name,' ',reg.last_name)) AS kids_names,
pay.credit_card_no, schreg.is_waiting, schreg.internal_registration
FROM ss_family f 
INNER JOIN ss_student s ON s.family_id = f.id 
INNER JOIN ss_user u ON u.id = s.user_id 
LEFT JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
LEFT JOIN ss_sunday_sch_req_child reg ON reg.user_id = s.user_id 
LEFT JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.sunday_school_reg_id 
LEFT JOIN ss_sunday_school_reg schreg ON schreg.id = reg.sunday_school_reg_id 
INNER JOIN ss_paymentcredentials pay ON pay.sunday_sch_req_id = schreg.id
WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 AND (schreg.id = '".$_GET['id']."' OR f.id = '".$_GET['familyid']."') group by reg_id;");

// $current_date = date('m/d/Y H:i A');
$current_date = my_date_changer(date('m/d/Y H.i'),'t');
if(!empty(get_country()->currency)){
    $currency = get_country()->currency;
}else{
    $currency = '';
}
$star = '************';
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
                    <div style="font-size: 20px;" class="color2">ISLAMIC CENTER OF KANSAS</div>
                    <div style="font-size: 12px; margin-top:5px;">
                    ' . SCHOOL_ADDRESS . ', ' . SCHOOL_ADDRESSCITY . ', ' . SCHOOL_ADDRESSSTATE . ' - ' . SCHOOL_ADDRESSZIPCODE . '
                    </div>
                    <div style="font-size: 12px; margin-top:4px;"><strong>Phone:</strong> ' . SCHOOL_CONTACT_NO . ' </div>
                    <div style="font-size: 12px; margin-top:3px;"><strong>Email:</strong> ' . SCHOOL_GEN_EMAIL . ' </div>
                </td>
                <td style="width:40%; text-align:right;padding-top:10px">
                </td>
            </tr> 
            <tr>
                <td colspan="2" style="text-align: center;">
                    <div style="font-size: 18px;margin-top:30px; text-align:center;"><u>REGISTRATION PAYMENT RECEIPT</u></div>
                </td>
            </tr>';
foreach ($get_student as $key => $row) {
    $get_registration = $db->get_results("SELECT reg.amount_received, txn.payment_unique_id, txn.comments, txn.payment_status, txn.payment_date FROM ss_sunday_school_reg reg INNER JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.id WHERE reg.id ='" . $row->reg_id . "'");
    //$child_name .= $row->kids_names.',';
   
    $html .='<tr>
    <td colspan="2" style="text-align: left; padding-top:30px">
    <table style="width:100%; cellpadding:0px; border:0;" cellspacing="10">
        <tr>
            <td style="width: 25%;" class="color2">Parent Name:</td>
            <td style="width: 75%; text-align:left;">' . $row->father_first_name . ' ' . $row->father_last_name . '</td>
        </tr>
        <tr>
            <td style="width: 25%;" class="color2">Phone Number:</td>
            <td style="width: 75%; text-align:left;">' . $row->father_phone . '</td>
        </tr>
        <tr>
            <td style="width: 25%;" class="color2">Email:</td>
            <td style="width: 75%; text-align:left;"> ' . $row->father_email . ' </td>
        </tr>
        <tr>
            <td style="width: 25%;" class="color2"> Child(ren) Name:</td>
            <td style="width: 75%; text-align:left;">' . $row->kids_names . '</td>
        </tr> ';

        if (!empty($credit_card_no)) {
        $credit_card_no = $star . substr(str_replace(' ', '', base64_decode($row->credit_card_no)), -4);
        $html .= '<tr>
                    <td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                    <td style="width: 75%; text-align:left;">' . $credit_card_no . '</td>
                </tr>
            ';
        }
if ($row->is_waiting == 0 && $row->internal_registration == 0) {
    if (count((array)$get_registration) > 0) {
        foreach ($get_registration as $get_reg) {
            if ($get_reg->payment_status == 1) {
                $payment_status = 'Success';
            }
            if (!empty($get_reg->payment_date)) {
                $payment_date = my_date_changer($get_reg->payment_date);
            }
            if (!empty($get_reg->amount_received)) {
                $payment_amount = $currency . $get_reg->amount_received;
            }
            if (!empty($get_reg->payment_unique_id)) {
                $transaction_id = $get_reg->payment_unique_id;
            }
            if (!empty($get_reg->comments)) {
                $comments = $get_reg->comments;
            }
            if (!empty($get_reg->payment_date) && !empty($get_reg->amount_received) && !empty($get_reg->payment_unique_id)) {
                $html .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                        <td style="width: 75%; text-align:left;">' . $transaction_id . '</td>
                                        </tr> 
                                        <tr>
                                            <td style="width: 25%;" class="color2">Paid Amount:</td> 
                                            <td style="width: 75%; text-align:left;">' . $payment_amount . '</td>
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
                            </tr>';
            }
        }
    }
}
}
            $html .= '<tr>
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
} catch(Exception $e) {
echo $e;
}



exit();
?>