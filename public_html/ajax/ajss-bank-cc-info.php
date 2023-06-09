<?php 
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}
if($_POST['action'] == 'save_pay_info'){
	$paycred = $db->get_row("select * from ss_paymentcredentials where family_id = '".$_SESSION['icksumm_uat_login_familyid']."'");
	$paymentcred_id = $paycred->id;
	$paycred = $db->get_row("select * from ss_paymentcredentials where id='".$paymentcred_id."'");
	$db->query('BEGIN');
	$sql = "insert into ss_paymentcredentials_backup set paymentcredentials_id='".$paymentcred_id."',
	family_id='".$paycred->family_id."',credit_card_type='".$paycred->credit_card_type."',credit_card_no='".$paycred->credit_card_no."',
	credit_card_exp='".$paycred->credit_card_exp."',postal_code='".$paycred->postal_code."',bank_acc_no='".$paycred->bank_acc_no."',
	routing_no='".$paycred->routing_no."',created_by_user_id='".$paycred->created_by_user_id."',created_on='".$paycred->created_on."',
	changed_by_userd_id='".$_SESSION['icksumm_uat_login_userid']."',changed_on='".date('Y-m-d H:i:s')."'";
	$sql_ret = $db->query($sql);
	if($sql_ret){
		if($_POST['payment_method'] == 'credit_card'){
			$credit_card_type = base64_encode($_POST['credit_card_type']);
			$credit_card_no = base64_encode($_POST['credit_card_no']);
			$credit_card_exp = base64_encode($_POST['credit_card_exp_month'].'-'.$_POST['credit_card_exp_year']);
			$postal_code = base64_encode($_POST['postal_code']);
			$bank_acc_no = '';
			$routing_no = '';
		}else{
			$credit_card_type = '';
			$credit_card_no = '';
			$credit_card_exp = '';
			$postal_code = '';
			$bank_acc_no = base64_encode($_POST['bank_acc_no']);
			$routing_no = base64_encode($_POST['routing_no']);
		}
		$sql_ret = $db->query("update ss_paymentcredentials set credit_card_type='".$credit_card_type."',credit_card_no='".$credit_card_no."',
		credit_card_exp='".$credit_card_exp."',postal_code='".$postal_code."',bank_acc_no='".$bank_acc_no."',
		routing_no='".$routing_no."',created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',created_on='".date('Y-m-d H:i:s')."' 
		where id='".$paymentcred_id."'");
		
		if($sql_ret && $db->query('COMMIT') !== false) {
			echo json_encode(array('code' => "1",'msg' => 'Payment credentials successfully'));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>'1'));
			exit;
		}	
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>'2'));
		exit;
	}
	/////////////////////////////////////
	/*$paycred = $db->get_row("select * from ss_paymentcredentials where family_id = '".$_SESSION['icksumm_uat_login_familyid']."'");
	$paymentcred_id = $paycred->id;
	$db->query('BEGIN');
	$sql = "insert into ss_paymentcredentials_backup set paymentcredentials_id='".$paymentcred_id."',
	family_id='".$paycred->family_id."',credit_card_type='".$paycred->credit_card_type."',credit_card_no='".$paycred->credit_card_no."',
	credit_card_exp='".$paycred->credit_card_exp."',postal_code='".$paycred->postal_code."',bank_acc_no='".$paycred->bank_acc_no."',
	routing_no='".$paycred->routing_no."',created_by_user_id='".$paycred->created_by_user_id."',created_on='".$paycred->created_on."',
	changed_by_userd_id='".$_SESSION['icksumm_uat_login_userid']."',changed_on='".date('Y-m-d H:i:s')."'";
	$sql_ret = $db->query($sql);
	if($sql_ret){	
		if(trim($_POST['credit_card_exp_month']) != '' && trim($_POST['credit_card_exp_year']) != ''){
			$credit_card_exp = base64_encode($_POST['credit_card_exp_month'].'-'.$_POST['credit_card_exp_year']);
		}				
		$sqlQuery = "update ss_paymentcredentials set credit_card_type ='".base64_encode($_POST['credit_card_type'])."', 
		credit_card_no ='".base64_encode(trim($_POST['credit_card_no']))."', 
		credit_card_exp ='".$credit_card_exp."', postal_code = '".trim($_POST['postal_code'])."', 
		bank_acc_no ='".base64_encode(trim($_POST['bank_acc_no']))."', 
		routing_no ='".base64_encode(trim($_POST['routing_no']))."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',created_on='".date('Y-m-d H:i:s')."' 
		where id = '".$paymentcred_id."'";
		
		$ret_sql_new = $db->query($sqlQuery);
		
		if($ret_sql_new && $db->query('COMMIT') !== false) {
			echo json_encode(array('msg'=>'Payment information updated successfully','code'=>1));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('msg'=>'Payment information not updated','code'=>0,'_errpos'=>'1'));
			exit;
		}
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('msg'=>'Payment information not updated','code'=>0,'_errpos'=>'2'));
		exit;
	}*/
}
?>