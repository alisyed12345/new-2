<?php
include_once "../../includes/config.php";
if(!empty(get_country()->currency)){
	$currency = iconv("UTF-8", "cp1252", get_country()->currency);
}else{
	$currency = '';
}
$user_CSV = [];
$user_CSV[] = array('1st Parent`s Name / 2nd Parent`s Name', 'Student Name', 'Date', 'Amount','Refunded Amount' ,'Status');

// very simple to increment with i++ if looping through a database result 


$sql = "SELECT sfi.id AS sch_item_id, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, sfi.schedule_status, s.family_id, s.user_id, f.id, f.father_first_name, f.father_last_name, f.mother_first_name, f.mother_last_name FROM ss_student_fees_items sfi 
	INNER JOIN ss_student s ON sfi.student_user_id = s.user_id 
	INNER JOIN ss_user u ON u.id = s.user_id 
	INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
	INNER JOIN ss_family f ON f.id = s.family_id 
	INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id";

	$startdate = my_date_changer($_GET['fromdate']);
	$enddate = my_date_changer($_GET['todate']);
	if($_GET['status'] == 'Pending'){
		$status = 0;
	}elseif($_GET['status'] == 'Success'){
		$status = 1;
	}elseif($_GET['status'] == 'Cancel'){
		$status = 2;
	}elseif($_GET['status'] == 'Decline'){
		$status = 4;
	}

	if(!empty($_GET['fromdate']) && !empty($_GET['todate']) && !empty($_GET['status'])){
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) >= '".$startdate."' AND DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) <= '".$enddate."' AND sfi.schedule_status = '".$status."' AND ";
	}elseif(!empty($_GET['fromdate']) && !empty($_GET['todate'])){
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) >= '".$startdate."' AND DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) <= '".$enddate."' AND ";
	}elseif(!empty($_GET['fromdate'])){
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) = '".$startdate."' AND ";
    }elseif(!empty($_GET['todate'])){
		$sql .= " WHERE DATE_FORMAT(`original_schedule_payment_date` , '%Y-%m-%d' ) = '".$enddate."' AND ";
	}elseif(!empty(trim($_GET['status']))){
		$sql .= " WHERE sfi.schedule_status = '".$status."' AND ";
    }else{
		$sql .= " WHERE";
	}

	// $sql .= " sfi.session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND u.is_deleted = 0 AND pay.default_credit_card =1 
	// GROUP BY s.user_id ORDER BY sfi.original_schedule_payment_date ASC";

	$sql .= " sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0 AND pay.default_credit_card =1 
	GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.original_schedule_payment_date ASC";


	
	$family = $db->get_results($sql);
    if(count((array)$family) == 0){
        $user_CSV[] = array('Data Not Found');
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
    $first_parent_name = $family_row->father_first_name.' '.$family_row->father_last_name;
    if(!empty($family_row->mother_first_name)){
        $second_parent_name = $family_row->mother_first_name.' '.$family_row->mother_last_name;
    }else{
        $second_parent_name = '';
    }
    if(!empty($family_row->father_first_name) && !empty($family_row->mother_first_name)){
        $full_name = $first_parent_name.'  /  '.$second_parent_name;
    }elseif(!empty($family_row->father_first_name)){
        $full_name = $first_parent_name;
    }elseif(!empty($family_row->mother_first_name)){
        $full_name = $second_parent_name; 
    }else{
        $full_name = '';
    }
    $date = my_date_changer($family_row->schedule_payment_date);
    if(!empty($family_row->final_amount)){
        $amount = $currency.$family_row->final_amount;
    }else{
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

    $user_CSV[] = array($full_name, $child, $date, $amount,$refunded_amount, $status);
}

// echo "<pre>";
// print_r($user_CSV);
// die;
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="schedule_payment_report.csv"');
$fp = fopen('php://output', 'wb');
foreach ($user_CSV as $line) {
    // though CSV stands for "comma separated value"
    // in many countries (including France) separator is ";"
    fputcsv($fp, $line, ',');
}
fclose($fp);
?>