<?php
include_once "../../includes/config.php";
if(!empty(get_country()->currency)){
	$currency = iconv("UTF-8", "cp1252", get_country()->currency);
}else{
	$currency = '';
}
$user_CSV = [];
$user_CSV[] = array('1st Parent`s Name', '2nd Parent`s Name', 'Student Name', 'Date', 'Amount', 'Transaction ID', 'Status');

$finalAry = array();
$admRequests = array();
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

$sql = "SELECT f.*,GROUP_CONCAT(c.first_name,' ',c.last_name)as child_name,t.payment_status,t.payment_date,t.payment_unique_id 
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

	// $admRequests[] = $temp;
	if((!empty($parent_first) || !empty($parent_second)) && !empty($adm_requests->child_name) && !empty($date) && !empty($amount) && !empty($transaction_id) && !empty($status)){        

		$parent_first = $adm_requests->father_first_name  . ' ' . $adm_requests->father_last_name;
		$parent_second = $adm_requests->mother_first_name . ' ' . $adm_requests->mother_last_name;
		$amount = $currency.$adm_requests->amount_received;
		$status = $asname;
		$transaction_id = $adm_requests->payment_unique_id;
		$date = my_date_changer($adm_requests->payment_date,'t');

		// $admRequests[] = $temp;
		$user_CSV[] = array($parent_first, $parent_second, $adm_requests->child_name, $date, $amount, $transaction_id, $status);
		}  


}

// $admission_reqs = $db->get_results("SELECT * from ss_sunday_school_reg where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND is_paid=1 order by id desc");
// 	$children = $db->get_results("SELECT * from ss_sunday_sch_req_child where sunday_school_reg_id = '" . $adm_requests->id . "'");

	//$child_counter = 0;
	// foreach ($children as $child) {


// }
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="registration_payment_report.csv"');
$fp = fopen('php://output', 'wb');
foreach ($user_CSV as $line) {
	// though CSV stands for "comma separated value"
	// in many countries (including France) separator is ";"
	fputcsv($fp, $line, ',');
}
fclose($fp);
