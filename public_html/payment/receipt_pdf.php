<?php
if (!empty(get_country()->currency)) {
    $currency = get_country()->currency;
} else {
    $currency = '';
}
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
        <td style="width:50%;text-align: left;vertical-align:top;padding-top:20px">
            <div style="font-size: 20px;" class="color2">' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . '
            </div>
            <div style="font-size: 12px; margin-top:5px;">
            ' . SCHOOL_ADDRESS . ', ' . SCHOOL_ADDRESSCITY . ', ' . SCHOOL_ADDRESSSTATE . ' - ' . SCHOOL_ADDRESSZIPCODE . '
            </div>
            <div style="font-size: 12px; margin-top:4px;"><strong>Phone:</strong> ' . internal_phone_check(SCHOOL_CONTACT_NO) . ' </div>

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

        <tr><td style="width: 25%;" class="color2">Parent Name:</td>
                            <td style="width: 75%; text-align:left;">' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '
                            </td></tr>

                        <tr><td style="width: 25%;" class="color2">Phone Number:</td>
                            <td style="width: 75%; text-align:left;">' . internal_phone_check($family_data->father_phone) . '
                            </td></tr>

                        <tr><td style="width: 25%;" class="color2">Email:</td>
                            <td style="width: 75%; text-align:left;"> ' . $family_data->primary_email . ' </td></tr>';
if (empty($client_data['refund_amount'])) {
    $html .= '<tr><td style="width: 25%;" class="color2"> Child(ren) Name:</td>
                                                        <td style="width: 75%; text-align:left;">' . rtrim($child_name, ', ') . '
                                                        </td></tr>';
}
if (!empty($transactionID)) {
    $html .= '<tr><td style="width: 25%;" class="color2">Payment Transaction ID:</td>
                                <td style="width: 75%; text-align:left;">' . $transactionID . '
                                </td></tr>';
}

$html .= '<tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
                            <td style="width: 75%; text-align:left;">' . $credit_card_no . '
                            </td></tr>';

// if (isset($payment_account_entries_id) && !empty($payment_account_entries_id)) {
//     $html .= '<tr><td style="width: 25%;" class="color2">Wallet Payment Transaction ID:</td>
//     <td style="width: 75%; text-align:left;">wtx_' . md5($payment_account_entries_id) . '
//     </td></tr>';
// }

if (isset($family_data->total_amount) && !empty($family_data->total_amount)) {
    $html .= '<tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                <td style="width: 75%; text-align:left;"> ' . $currency . $family_data->total_amount . '</td></tr>';
} elseif (isset($full_amount) && !empty($full_amount)) {
    $html .= '<tr><td style="width: 25%;" class="color2">Paid Amount:</td> 
                                <td style="width: 75%; text-align:left;"> ' . $currency . $full_amount . '</td></tr>';
} elseif (isset($client_data['refund_amount']) && !empty($client_data['refund_amount'])) {
    $html .= '<tr><td style="width: 25%;" class="color2">Refund Amount:</td> 
                                <td style="width: 75%; text-align:left;"> ' . $currency . $client_data['refund_amount'] . '</td></tr>';
}
if (!empty($is_payment_type)) {
    if ($is_payment_type == 1) {
        $is_type = 'Registration Payment';
    } elseif ($is_payment_type == 2) {
        $is_type = 'Registration Payment';
    } elseif ($is_payment_type == 3) {
        $is_type = 'Schedule Payment';
    } elseif ($is_payment_type == 4) {
        $is_type = 'Manual Payment';
    } elseif ($is_payment_type == 5) {
        $is_type = 'Wallet Payment';
    } elseif ($is_payment_type == 6) {
        $is_type = 'Refund Payment';
    }
    $html .= '<tr><td style="width: 25%;" class="color2">Payment Type:</td>
    <td style="width: 75%; text-align:left;">' . $is_type . '
    </td></tr>';
} elseif (!empty($is_refund_payment_type)) {
    $html .= '<tr><td style="width: 25%;" class="color2">Payment Type:</td>
    <td style="width: 75%; text-align:left;">' . $is_refund_payment_type . '
    </td></tr>';
}
if (isset($client_data['refund_amount']) && !empty($client_data['refund_amount'])) {
    $html .= '<tr><td style="width: 25%;" class="color2">Refund Payment Date:</td>
                                <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'), 't') . '
                                    </td></tr>';
} else {
    $html .= '<tr><td style="width: 25%;" class="color2">Payment Date:</td>
                                <td style="width: 75%; text-align:left;">' . my_date_changer(date('Y-m-d H.i'), 't') . '
                                    </td></tr>';
}


if (!empty($PaymentStatusMSG)) {
    $html .= '<tr><td style="width: 25%;" class="color2">Payment Status:</td>
                                        <td style="width: 75%; text-align:left;">' . $PaymentStatusMSG . '
                                        </td></tr>';
}

$html .= '</table>
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
    <div>For any comments or question, please send email to <a href="' . SCHOOL_GEN_EMAIL . '"> ' . SCHOOL_GEN_EMAIL . '</a></div>
    <div><br>You agreed to give us consent to authorize electronic fund transfer from the account specified above for ' . SCHOOL_NAME . '</div>
</div>';

$html .= '</body> 
</html>';
// echo $html;
// die;
