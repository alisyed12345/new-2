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

$html .='<table class="table table-bordered data-table dataGrid">';
$html .='<thead>
            <tr>          
                <th>1st Parent`s Name </th>
                <th>2nd Parent`s Name </th>
                <th>Student Name</th>
                <th>Date</th>
                <th>Amount</th>
                <th>refunded Amount</th>
                <th>Transaction ID</th>
                <th>Status</th>
            </tr>
            </thead>';

          $startdate = my_date_changer($_GET['fromdate']);
    $enddate = my_date_changer($_GET['todate']);
    if($_GET['status'] == 'Pending'){
        $status = 0;
    }elseif($_GET['status'] == 'Success'){
        $status = 1; 
    }
    else{
        $status = 2;
    }

    $sql = "SELECT f.*,GROUP_CONCAT(c.first_name,' ',c.last_name)as child_name,t.payment_status,t.payment_date,t.payment_unique_id,t.id as txn_id 
from ss_sunday_school_reg as f 
inner join ss_sunday_sch_req_child as c  on f.id=c.sunday_school_reg_id 
left join ss_payment_txns  as t  on t.sunday_school_reg_id=f.id 
where f.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND f.is_paid=1 ";

if (!empty($_GET['fromdate']) && !empty($_GET['todate']) && !empty($_GET['status'])) {
	$sql .= " AND DATE_FORMAT(t.`payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(t.`payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' AND t.payment_status = '" . $status . "'  ";
} elseif (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
	$sql .= " AND DATE_FORMAT(t.`payment_date` , '%Y-%m-%d' ) >= '" . $startdate . "' AND DATE_FORMAT(t.`payment_date` , '%Y-%m-%d' ) <= '" . $enddate . "' ";
} elseif (!empty($_GET['fromdate'])) {
	$sql .= " AND DATE_FORMAT(t.payment_date , '%Y-%m-%d' ) = '" . $startdate . "' ";
} elseif (!empty($_GET['todate'])) {
	$sql .= " AND DATE_FORMAT(t.payment_date, '%Y-%m-%d' ) = '" . $enddate . "' ";
} elseif (!empty(trim($_GET['status']))) {
	$sql .= " AND t.payment_status = '" . $status . "' ";
} else {
	$sql .= "";
}

$sql .= " group by f.id order by f.id desc";

$admission_reqs = $db->get_results($sql);
    


foreach ($admission_reqs as $adm_requests) {

            if ($adm_requests->payment_status == 0) {
                $asname = 'Pending';
            } elseif ($adm_requests->payment_status == 1) {
                $asname = 'Success';
            } else {
                $asname = 'Failed';
            }


            $parent_first = $adm_requests->father_first_name . ' ' . $adm_requests->father_last_name;
            $parent_second = $adm_requests->mother_first_name . ' ' . $adm_requests->mother_last_name;
            $amount= $currency.$adm_requests->amount_received; 
            $status = $asname;
            $transaction_id = $adm_requests->payment_unique_id;
            $date = my_date_changer($adm_requests->payment_date,'t');
        
            $refund_amount = $db->get_var("SELECT refund_amount FROM ss_refund_payment_txns where payment_txn_id='" . $adm_requests->txn_id . "'");
            if(!empty($refund_amount)){
            $temp['refund_amount'] = $refund_amount ;
            }else{
                $temp['refund_amount'] = '<p style="text-align:center">-</p>' ;
            }


            // $admRequests[] = $temp;
            if((!empty($parent_first) || !empty($parent_second)) && !empty($adm_requests->child_name) && !empty($date) && !empty($amount) && !empty($transaction_id) && !empty($status) && !empty($refund_amount)){        

                
                $html .='<tbody>
                                <tr role="row" class="odd">
                                    <td class="sorting_1" tabindex="0">'.$parent_first.'</td>
                                    <td class="sorting_1" tabindex="0">'.$parent_second.'</td>
                                    <td>'.$adm_requests->child_name.'</td>
                                    <td>'.$date.'</td>
                                    <td>'.$amount.'</td>
                                    <td>'.$refund_amount.'</td>
                                    <td>'.$transaction_id.'</td>
                                    <td>'.$status.'</td>
                                </tr>
                            </tbody>';
                }  

       
        }


$html .= '</table>';

// echo $html;
// die;
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
$dompdf->stream('registration_payment_report.pdf');
?>