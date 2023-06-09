<?php
include_once "../includes/config.php";
if(!empty(get_country()->currency)){
$currency = get_country()->currency;
}else{
$currency = '';
} 
$get_next_billing_date = $db->get_var("SELECT schedule_payment_date FROM ss_student_fees_items WHERE FIND_IN_SET(student_user_id,'$remind->user_id') and schedule_payment_date > '" . $remind->schedule_payment_date . "' and schedule_status = 0");
$get_country = $db->get_var("select country from ss_country where is_active = 1 and id = '".get_country()->country_id."'");
$logo = image_binary(LOGO);
$html = '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />

                <title>Invoice</title>

                <!-- Favicon -->
                <link rel="icon" href="./images/favicon.png" type="image/x-icon" />

                <!-- Invoice styling -->
                <style>
                    body {
                        font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
                        text-align: center;
                        color: #777;
                    }

                    body h1 {
                        font-weight: 300;
                        margin-bottom: 0px;
                        padding-bottom: 0px;
                        color: #000;
                    }

                    body h3 {
                        font-weight: 300;
                        margin-top: 10px;
                        margin-bottom: 20px;
                        font-style: italic;
                        color: #555;
                    }

                    body a {
                        color: #06f;
                    }

                    .invoice-box {
                        max-width: 800px;
                        margin: auto;
                        padding: 30px;
                        border: 1px solid #eee;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
                        font-size: 16px;
                        line-height: 24px;
                        font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
                        color: #555;
                    }

                    .invoice-box table {
                        width: 100%;
                        line-height: inherit;
                        text-align: left;
                        border-collapse: collapse;
                    }

                    .invoice-box table td {
                        padding: 10px;
                        vertical-align: top;
                    }

                    .invoice-box table tr td:nth-child(2) {
                        text-align: right;
                    }
                    .invoice-box table tr.top table td {
                        padding-bottom: 20px;
                    }

                    .invoice-box table tr.top table td.title {
                        font-size: 25px;
                        line-height: 25px;
                        color: #333;
                    }

                    .invoice-box table tr.information table td {
                        padding-bottom: 40px;
                    }

                    .invoice-box table tr.heading td {
                        background: #eee;
                        border-bottom: 1px solid #ddd;
                        font-weight: bold;
                    }

                    .invoice-box table tr.details td {
                        padding-bottom: 20px;
                    }

                    .invoice-box table tr.item td {
                        border-bottom: 1px solid #eee;
                    }

                    .invoice-box table tr.item.last td {
                        border-bottom: none;
                    }

                    .invoice-box table tr.total td:nth-child(2) {
                        border-top: 2px solid #eee;
                        font-weight: bold;
                    }

                    @media only screen and (max-width: 600px) {
                        .invoice-box table tr.top table td {
                            width: 100%;
                            display: block;
                            text-align: center;
                        }

                        .invoice-box table tr.information table td {
                            width: 100%;
                            display: block;
                            text-align: center;
                        }
                    }
                </style>
            </head>

            <body>
                <div class="invoice-box">
                        <table>
                            <tr>
                            <td><h1 style="text-align: left;">Invoice</h1></td>

                            <td class="title">
                                <img src="' . $logo . '" style="width: 100%; max-width: 300px" />
                            </td>

                               
                            </tr>
                        </table>

                    <table>
                        <tr>
                            <td>
                                ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . '
                                <br />
                                ' . SCHOOL_ADDRESS . '<br />
                                '.$get_country.'<br />
                                ' . SCHOOL_GEN_EMAIL . '
                            </td>

                            <td>';
                            if ($family_data->total_amount) {
                                if(get_country()->abbreviation == 'USA'){
                                    $country_currency_sign = '(USD)';
                                }else{
                                    $country_currency_sign = '';
                                }
                                $html .= '<strong>Invoice #</strong> ' . $invoce_id . '<br />
                                                            <strong>Invoice Date</strong> ' .my_date_changer(date('M d, Y'),'c') . '<br />
                                                            <strong>Invoice Amount</strong> '.$currency . $family_data->total_amount." ".$country_currency_sign.' <br /> </td>';
                                //          <strong>Customer ID</strong> '.substr(md5($family_data->user_id), 0, 13).'
                            } else {
                                $html .= '<strong>Invoice #</strong> ' . $invoce_id . '<br />
                                                            <strong>Invoice Date</strong> ' . my_date_changer(date('M d, Y'),'c') . '<br />
                                                            <strong>Invoice Amount</strong> '.$currency . $final_amount ." ".$country_currency_sign.'<br />    </td>';
                                /*  <strong>Customer ID</strong> '.substr(md5($remind->user_id), 0, 13).' */
                            }

                            $html .= '</tr>
                            </table>

                            <table style="margin-top:20px;">';
                            if ($family_data->total_amount) {
                                $html .= '<tr>
                            <td>
                            <strong>BILLED TO</strong><br />
                            ' . $family_data->father_first_name . ' ' . $family_data->father_last_name . '<br />
                            ' . $family_data->billing_address_1 . '<br />
                            '.get_country()->country.'<br />
                            </td>

                            <td>';
                            // $html.='<strong>SUBSCRIPTION</strong><br />
                            // <strong>ID</strong> ' . substr(md5($family_data->user_id), 0, 13) . '<br />';   
                            if(!empty($family_data->new_schedule_payment_date)){
                                $html.='<strong style="margin-left: 123px;">Billing Date</strong> ' . my_date_changer($family_data->new_schedule_payment_date,'c') . '<br />';
                            }else{
                                $html.='<strong style="margin-left: 123px;">Billing Date</strong> ' . my_date_changer($family_data->schedule_payment_date,'c') . '<br />';
                            }

                        if (!empty($get_next_billing_date)) {
                            $html .= '<strong style="text-align:right;">Next Billing Date</strong> ' . my_date_changer($get_next_billing_date,'c') . '</td>';
                        }
                        $html .= ' </tr>';
                        } else {
                            $html .= '<tr>
                        <td>
                        <strong>BILLED TO</strong><br />
                        ' . $remind->father_first_name . ' ' . $remind->father_last_name . '<br />
                        ' . $remind->billing_address_1 . '<br />
                        '.get_country()->country.'<br />
                        </td>

                        <td>';
                        // $html .= '<strong>SUBSCRIPTION</strong><br />
                        // <strong>ID</strong> ' . substr(md5($remind->user_id), 0, 13) . '<br />';
                        $html .= '<strong style="text-align:right;">Billing Date</strong> ' . my_date_changer($remind->schedule_payment_date,'c') . '<br />';

                        if (!empty($get_next_billing_date)) {
                            $html .= '<strong style="text-align:right;">Next Billing Date</strong> ' . my_date_changer($get_next_billing_date,'c') . '</td>';
                        }


                        $html .= ' </tr>';
                        }
                        $html .= ' </table>
                                    
                                        <table style="margin-top:20px;">
                                            <tr class="heading">
                                                <td style="width: 35%;">Item</td>
                                                <td style="width: 25%;">Student Name</td>
                                                <td style="text-align:right;">AMOUNT</td>
                                            </tr>';
                        if ($family_data->total_amount) {
                            $html .= '<tr class="item">
                                                <td>Monthly Payment - ' . date('F Y', strtotime($family_data->schedule_payment_date)) . '</td>
                                                <td>' . $family_data->student_name . '</td>
                                                <td style="text-align:right;"> '.$currency . $family_data->total_amount . '</td>
                                            </tr>';
                        } else {
                            $html .= '<tr class="item">
                                                <td>Monthly Payment - ' . date('F Y', strtotime($remind->schedule_payment_date)) . '</td>
                                                <td>' . rtrim($student_name, ', ') . '</td>
                                                <td style="text-align:right;"> '.$currency . $final_amount . '</td>
                                                </tr>';
                        }
                        $html .= '</table>
                                        <table>';
                        // if ($family_data->total_amount) {
                        //     $html .= '<tr class="item last">
                        //                         <td>Amount Due</td>
                        //                         <td> $' . $family_data->total_amount . '</td>
                        //                     </tr>

                        //                     <tr class="total">
                        //                         <td></td>
                        //                         <td>Total: $' . $family_data->total_amount . '</td>
                        //                     </tr>';
                        // } else {
                        //     $html .= '<tr class="item last">
                        //             <td>Amount Due</td>
                        //                 <td> $' . $final_amount . '</td>
                        //             </tr>

                        //             <tr class="total">
                        //                 <td></td>
                        //                 <td>Total: $' . $final_amount . '</td>
                        //             </tr>';
                        // }
$html .= '</table>
        </div>
        
    </body>
</html>';