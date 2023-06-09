<?php
include_once "../../includes/config.php";
if(!empty(get_country()->currency)){
	$currency = iconv("UTF-8", "cp1252", get_country()->currency);
}else{
	$currency = '';
}
$user_CSV = [];
$user_CSV[] = array('1st Parent`s Name / 2nd Parent`s Name', 'Student Name', 'Original Fees', 'Discount', 'Net fees');

// very simple to increment with i++ if looping through a database result 


$family = $db->get_results("SELECT f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name, f.primary_email, f.secondary_email from ss_student_feesdiscounts as d 
inner join ss_user as u on u.id=d.student_user_id
inner join ss_student as s on s.user_id=d.student_user_id
INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
INNER JOIN ss_family f ON  f.id = s.family_id
where u.is_active=1 and u.is_deleted=0 and d.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and d.status=1 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' GROUP by f.id");




// "SELECT f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name, f.primary_email, f.secondary_email FROM ss_family f 
// INNER JOIN ss_user u ON u.id = f.user_id 
// INNER JOIN ss_student s ON s.family_id = f.id
// INNER JOIN ss_student_feesdiscounts sfd ON sfd.student_user_id = s.user_id
// WHERE f.is_deleted=0 
// AND u.is_active=1 AND sfd.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' GROUP BY f.id"



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
        $child_name .= $row->stu_full_name ."\t"."\n\r";
    }

    $bascifees = "";
    foreach ($basic_fee as $basicfeesdf) {
        $bascifees .= $currency.$basicfeesdf ."\t"."\n\r";
    }

    $finalamountfee = "";
    foreach ($final_amount_total as $finalamountdd) {
        $finalamountfee .= $currency.$finalamountdd ."\t"."\n\r";
    }

    $discountfee = "";
    foreach ($discount_fee_val_all as $discountsds) {
        $discountfee .= $currency.$discountsds ."\t"."\n\r";
    }
    $firstparent = $family_row->father_first_name.' '.$family_row->father_last_name;
    $lastparent = $family_row->mother_first_name.' '.$family_row->mother_last_name;
    $full_name = $firstparent.' / '.$lastparent;
    $childname = $child_name."\t"."\n\r";

    $basic = $bascifees."\t"."\n\r";

    $discount_fee_f = $discountfee."\t"."\n\r";

    $f_fee = $finalamountfee."\t"."\n\r";
    
    $user_CSV[] = array($full_name, $child_name, $basic, $discount_fee_f, $f_fee);
}

// echo "<pre>";
// print_r($user_CSV);
// die;
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="discount_report.csv"');
$fp = fopen('php://output', 'wb');
foreach ($user_CSV as $line) {
    // though CSV stands for "comma separated value"
    // in many countries (including France) separator is ";"
    fputcsv($fp, $line);
}
fclose($fp);
?>