<?php
include_once "../../includes/config.php";
include_once '../../includes/dompdf/autoload.inc.php';
if(!empty(get_country()->currency)){
	$currency = get_country()->currency;
}else{
	$currency = '';
}
define("DOMPDF_UNICODE_ENABLED", true);

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$html = '<style>
            table, td, th {  
                border: 1px solid #ddd;
                text-align: left;
            }
            table, td {
                border-bottom: 2px solid black !important;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            
            th, td {
                padding: 15px;
            }
            legend {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                padding-top: 10px !important;
                padding-bottom: 0px !important;
                margin-bottom: 20px !important; 
                font-size: 19.5px !important;
                line-height: inherit !important;
                color: #333 !important;
                border: 0 !important;
                border-bottom-color: currentcolor !important;
                border-bottom-style: none !important;
                border-bottom-width: 0px !important;
                border-bottom: 1px solid #e5e5e5 !important;
            }
         </style>';

$html .= '<table class="table table-bordered data-table dataGrid">';
$html .= '<thead>
            <tr>          
                <th>1st Parent`s Name / 2nd Parent`s Name</th>
                <th>Student Name</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Refunded Amount</th>
                <th>Status</th>
            </tr>
            </thead>';

$sql = "SELECT sfi.id AS sch_item_id, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status, s.family_id, s.user_id, f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name FROM ss_student_fees_items sfi 
            INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
            INNER JOIN ss_user u ON u.id = s.user_id 
            INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
            INNER JOIN ss_family f ON f.id = s.family_id 
            INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id";


$startdate = my_date_changer($_GET['fromdate']);
$enddate = my_date_changer($_GET['todate']);
if ($_GET['status'] == 'Pending') {
    $status = 0;
} elseif ($_GET['status'] == 'Success') {
    $status = 1;
} elseif ($_GET['status'] == 'Cancel') {
    $status = 2;
} elseif ($_GET['status'] == 'Decline') {
    $status = 4;
}

if (!empty($_GET['fromdate']) && !empty($_GET['todate']) && !empty($_GET['status'])) {
    $sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND sfi.schedule_status = '" . $status . "' AND ";
} elseif (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
    $sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND ";
} elseif (!empty($_GET['fromdate'])) {
    $sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) = '" . $startdate . "' AND ";
} elseif (!empty($_GET['todate'])) {
    $sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) = '" . $enddate . "' AND ";
} elseif (!empty(trim($_GET['status']))) {
    $sql .= " WHERE sfi.schedule_status = '" . $status . "' AND ";
} else {
    $sql .= " WHERE";
}

$sql .= " sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 AND pay.default_credit_card =1 
GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.original_schedule_payment_date ASC";

$family = $db->get_results($sql);



if (count((array)$family) == 0) {
    $html .= '<tbody>
                        <tr>
                            <td style="text-align:center;" colspan="5">Data not found</td>
                        </tr>
                    </tbody>';
}
foreach ($family as $family_row) {
    $trxn_child_names = $db->get_results("SELECT s.first_name FROM ss_student_fees_items sfi
                    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id  INNER JOIN ss_family f ON f.id = s.family_id WHERE s.family_id = '" . $family_row->id . "' GROUP BY s.user_id");

    $child_name = "";
    foreach ($trxn_child_names as $row) {
        $child_name .= $row->first_name . ", ";
    }

    if ($family_row->schedule_status == 1) {
        $status = 'Success';
    } elseif ($family_row->schedule_status == 2) {
        $status = 'Cancel';
    } elseif ($family_row->schedule_status == 3) {
        $status = 'Hold';
    } elseif ($family_row->schedule_status == 4) {
        $status = 'Decline';
    } elseif ($family_row->schedule_status == 0) {
        $status = 'Pending';
    } else {
        $status = '';
    }

    $child = rtrim($child_name, ", ");
    $first_parent_name = $family_row->father_first_name . ' ' . $family_row->father_last_name;
    if (!empty($family_row->mother_first_name)) {
        $second_parent_name = $family_row->mother_first_name . ' ' . $family_row->mother_last_name;
    } else {
        $second_parent_name = '';
    }
    if (!empty($family_row->father_first_name) && !empty($family_row->mother_first_name)) {
        $full_name = $first_parent_name . '  /  ' . $second_parent_name;
    } elseif (!empty($family_row->father_first_name)) {
        $full_name = $first_parent_name;
    } elseif (!empty($family_row->mother_first_name)) {
        $full_name = $second_parent_name;
    } else {
        $full_name = '';
    }

    $date = my_date_changer($family_row->schedule_payment_date);
    if (!empty($family_row->final_amount)) {
        $amount = $currency . $family_row->final_amount;
    } else {
        $amount = '';
    }
    $refunded_amount=$db->get_var(" SELECT refund_amount FROM `ss_student_fees_transactions` as sft
    inner join ss_payment_txns as txn on txn.id=sft.payment_txns_id
    inner join ss_refund_payment_txns as ref_txn on ref_txn.payment_txn_id=sft.payment_txns_id
    WHERE student_fees_item_id='" .$family_row->sch_item_id."'");

    if(!empty($refunded_amount)){
        $refunded_amount=$currency.$refunded_amount;
    }else{
        $refunded_amount='-';
    }



    if (!empty($full_name) && !empty($child) && !empty($date) && !empty($amount) && !empty($status) && !empty($refunded_amount)) {
        $html .= '<tbody>
                                <tr role="row" class="odd">
                                    <td class="sorting_1" tabindex="0">' . $full_name . '</td>
                                    <td>' . $child . '</td>
                                    <td>' . $date . '</td>
                                    <td>' . $amount . '</td>
                                    <td>' . $refunded_amount . '</td>
                                    <td>' . $status . '</td>
                                </tr>
                            </tbody>';
    }
}

$html .= '</table>';

$dompdf->loadHtml($html);
// (Optional) Setup the paper size and orientation 
$dompdf->setPaper('Executive', 'landscape');
// Render the HTML as PDF 
$dompdf->render();
// $path = 'invoice_and_pdf';
// $filename="/test";
// $output = $dompdf->output();
// file_put_contents($path.$filename.'.pdf', $output);
//Output the generated PDF to Browser 
$dompdf->stream('payment_report.pdf');
