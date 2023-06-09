<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================LIST ALL STAFF FOR ADMIN=====================
if($_GET['action'] == 'list_registration_payment_history'){  
	$finalAry = array();
 
	$all_families = $db->get_results("SELECT DISTINCT reg.id, CONCAT(reg.father_first_name,' ',COALESCE(reg.father_last_name, '')) AS father_name, reg.father_phone, reg.primary_email, reg.amount_received, 
		FROM ss_sunday_school_reg reg 
		INNER JOIN ss_payment_txns txn ON txn.sunday_school_reg_id = reg.id
	    WHERE u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ORDER BY reg.father_first_name",ARRAY_A);

	$finalAry['data'] = $all_families;
	echo json_encode($finalAry);
	exit;
}
?>