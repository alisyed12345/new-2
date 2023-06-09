<?php
include_once "../../includes/config.php";
include_once '../../includes/dompdf/autoload.inc.php';
if(!empty(get_country()->currency)){
	$currency = get_country()->currency;
}else{
	$currency = '';
}
ob_clean();
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
                <th>1st Parent`s Name / 2nd Parent`s Name</th>
                <th>Student Name</th>
                <th>Original Fees</th>
                <th>Discount</th>
                <th>Net fees</th>
            </tr>
            </thead>';
$family = $db->get_results("SELECT f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name, f.primary_email, f.secondary_email from ss_student_feesdiscounts as d 
inner join ss_user as u on u.id=d.student_user_id
inner join ss_student as s on s.user_id=d.student_user_id
INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
INNER JOIN ss_family f ON  f.id = s.family_id
where u.is_active=1 and u.is_deleted=0 and d.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and d.status=1 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' GROUP by f.id");




foreach ($family as $family_row) {
    $students = $db->get_results("SELECT s.user_id, s.dob, s.allergies, s.school_grade, CONCAT(first_name,' ',COALESCE(middle_name, ''),' ',COALESCE(last_name, '')) AS student_name FROM ss_user u INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_student s ON u.id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and u.is_deleted = 0 AND s.family_id = '" . $family_row->id . "' ");
    $trxn_child_names = $db->get_results("SELECT CONCAT(s.first_name,' ',COALESCE(s.middle_name, ''),' ',COALESCE(s.last_name, '')) AS stu_full_name FROM ss_student s INNER JOIN ss_family f ON f.id = s.family_id WHERE s.family_id = '" . $family_row->id . "' GROUP BY s.user_id");

    $basic_fee = [];
    $final_amount_total = [];
    $discount_fee_val_all = [];
    foreach ($students as $stu) {
        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
        $groups = [];
        foreach ($user_groups as $group) {
            $groups[] = $group->id;
        }
        $group_ids = implode(",", $groups);    
        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
        $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $stu->user_id . "' AND sf.status = 1  and d.status=1 and sf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND d.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
        $discountPercentTotal = $basicFees->fee_amount;
        $discountDollarTotal = 0;
  
        foreach ($new_discountFeesData as $val) {
            if ($val->discount_unit == 'p') {
                $doller = '';
                $percent = '%';
                $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                $discountPercentTotal = $discountPercentTotal - $fee_percent;
            } else {
                $doller = $currency;
                $percent = '';
                $discountDollarTotal+= $val->discount_percent;
            }
        }
        $basic_fee[] = $basicFees->fee_amount;
        //$basic_fee_all+= $basicFees->fee_amount;
        $final_amount = ($discountPercentTotal - $discountDollarTotal);
        if ($final_amount > 0) {
            $total_final_amount = $final_amount;
        } else {
            $total_final_amount = '0';
        }
        $final_amount_total[] = $total_final_amount;
        //$final_amount_all+= $total_final_amount;
        $discount_fee_val_all[] =$basicFees->fee_amount - $total_final_amount;
    }

              

    $child_name = "";
    foreach ($trxn_child_names as $row) {
        $child_name .= $row->stu_full_name . '<br><legend class="text-semibold" style="margin-top:10px;"></legend><br>';
    }
    $bascifees = "";
    foreach ($basic_fee as $basicfeesdf) {
        $bascifees .= $currency.$basicfeesdf . '<br><legend class="text-semibold" style="margin-top:10px;"></legend><br>';
    }

    $finalamountfee = "";
    foreach ($final_amount_total as $finalamountdd) {
        $finalamountfee .= $currency.$finalamountdd . '<br><legend class="text-semibold" style="margin-top:10px;"></legend><br>';
    }

    $discountfee = "";
    foreach ($discount_fee_val_all as $discountsds) {
        $discountfee .= $currency.$discountsds . '<br><legend class="text-semibold" style="margin-top:10px;"></legend><br>';
    }
    if(!empty($family_row->father_first_name) && !empty($family_row->mother_first_name)){
        $family_name = $family_row->father_first_name.' '.$family_row->father_last_name.'  /  '.$family_row->mother_first_name.' '.$family_row->mother_last_name;
    }else{
        $family_name = $family_row->father_first_name.' '.$family_row->father_last_name;
    }
    $html .='<tbody>';
                if(!empty($discountfee)){
                    $html .='<tr role="row" class="odd">
                    <td class="sorting_1" tabindex="0">'.$family_name.'</td>
                    <td>'.substr($child_name, 0,-72).'</td>
                    <td>'.substr($bascifees,0,-72).'</td>
                    <td>'.substr($discountfee,0,-72).'</td>
                    <td>'.substr($finalamountfee,0,-72).'</td></tr>';
                }else{
                    $html .='<tr>
                        <td colspan="5">Data not found </td>
                    </tr>';
                }
                
    $html .='</tbody>';
}

$html .= '</table>';

$dompdf->loadHtml($html);
// (Optional) Setup the paper size and orientation 
$dompdf->setPaper('A4', 'landscape');
// Render the HTML as PDF 
$dompdf->render();
// $path = 'invoice_and_pdf';
// $filename="/test";
// $output = $dompdf->output();
// file_put_contents($path.$filename.'.pdf', $output);
//Output the generated PDF to Browser 
//$dompdf->stream();
$dompdf->stream('discount_report.pdf');
?>