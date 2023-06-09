<?php 
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}
function studentFeesHistory($student_user_id){
	global $db, $MONTHS;
	$fees_rec = $db->get_results("SELECT * FROM ss_fees WHERE student_user_id = '".$student_user_id."' ORDER BY id DESC");
	if(count((array)$fees_rec)){
		foreach($fees_rec as $rec){
			$history .= '<tr>';
			$history .= '<td>'.$MONTHS[$rec->month].' - '.$rec->year.'</td>';
			$history .= '<td>'.$rec->amount.'</td>';
			$history .= '<td>'.$rec->receipt_no.'</td>';
			$history .= '<td>'.date('m/d/Y',strtotime($rec->created_on)).'</td>';
			$history .= '<td>'.$rec->remarks.'</td>';
			$history .= '<td><a href="'.SITEURL.'fees/fees_edit.php?id='.$rec->id.'" class="text-primary action_link"><i class = "icon-pencil5" ></i></a><a href="javascript:void(0)" data-feesid = "'.$rec->id.'" title="Delete Payment" class="text-danger action_link delete_fees"><i class="icon-trash"></i></a></td>';
			$history .= '</tr>';
		}
	}else{
		$history = '<tr>';
		$history .= '<td colspan="3">No record found</td>';
		$history .= '</tr>';
	}
	return $history;
}
//==========================GET ALL GROUP STUDENTS=====================
/*if($_POST['action'] == 'get_group_student'){
	//$groupid = $_POST['groupid'];
	$option = '<option value="">Select</option>';
	
	$students = $db->get_results("SELECT s.user_id, concat(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name) as student_name 
	FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id WHERE u.is_active = 1 
	AND u.is_deleted = 0 AND u.id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE group_id='".$groupid."' AND latest=1)");

	foreach($students as $stu){
		$option .= '<option value="'.$stu->user_id.'">'.$stu->student_name.'</option>';
	}
	
	if(count((array)$students)){
		echo json_encode(array('option'=>$option,'code'=>1));
		exit;
	}else{
		echo json_encode(array('msg'=>'Students not found','code'=>0));
		exit;
	}
}*/
//==========================LIST FEES=====================
if($_GET['action'] == 'list_fees'){
	$finalAry = array();
	$fees = $db->get_results("SELECT f.id, s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name) AS student_name,
	CONCAT(MONTHNAME(STR_TO_DATE(MONTH, '%m')),'-',YEAR) AS month_name, f.amount, f.remarks, '' as group_name, f.created_on as paid_on
	FROM ss_fees f INNER JOIN ss_student s ON f.student_user_id = s.user_id ORDER BY f.created_on desc",ARRAY_A);
	for($i=0; $i<count((array)$fees); $i++){
		$group_name = $db->get_var("SELECT group_name from ss_groups where id = (SELECT group_id FROM ss_studentgroupmap WHERE student_user_id='".$fees[$i]['user_id']."' AND latest=1 order by id desc limit 1)");
		$fees[$i]['group_name'] = $group_name;
		$fees[$i]['paid_on'] = date('m/d/Y',strtotime($fees[$i]['paid_on']));
	}
	$finalAry['data'] = $fees;
	echo json_encode($finalAry);
	exit;
}
//=====================DELETE GROUP==================
elseif($_POST['action'] == 'delete_fees'){
	if(isset($_POST['feesid'])){
		$rec = $db->query("delete from ss_fees where id='".$_POST['feesid']."'");
		if($rec > 0){
			echo json_encode(array('code' => "1",'msg' => 'Payment deleted successfully'));
			exit;
		}else{
			echo json_encode(array('code' => "0",'msg' => 'Error: Payment deletion failed'));
			exit;
		}
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Process failed'));
		exit;
	}
}
//==========================GET FEES PAYABLE MONTHS=====================
elseif($_POST['action'] == 'get_payable_month'){
	$student_user_id = $_POST['student_user_id'];	
	$payableOptions = feesMonths();
	$history = studentFeesHistory($student_user_id);
	if($payableOptions != '' ){
		$payableOptions = '<option value="" data-order="0">Select</option>'.$payableOptions;
		echo json_encode(array('option'=>$payableOptions,'code'=>1,'history'=>$history));
		exit;
	}else{
		echo json_encode(array('msg'=>'Process failed','code'=>0));
		exit;
	}
}
//==========================SAVE FEES=====================
elseif($_POST['action'] == 'submit_fees'){
	$student_user_id = $_POST['student'];
	$start_month = $_POST['start_month'];
	$end_month = $_POST['end_month'];
	$amount = $_POST['amount'];
	$receipt_no = $_POST['receipt_no'];
	$remarks = $_POST['remarks'];
	if($end_month == ''){
		$monAry = explode('-',$start_month);
		//SINGLE MONTH FEES
		$prevEntry = $db->get_row("select * from ss_fees where student_user_id='".$student_user_id."' and month='".$monAry[0]."' and year='".$monAry[1]."'");	
		if(!empty($prevEntry)){
			echo json_encode(array('msg'=>'Payment already added for this month','code'=>0,'_errpos'=>'1'));
			exit;
		}else{
			//CHECK RECEIPT NUMBER
			$receiptNoCheck = $db->get_row("select * from ss_fees where receipt_no='".$receipt_no."'");	
			if(!empty($receiptNoCheck)){
				echo json_encode(array('msg'=>'Receipt number already exists','code'=>0,'_errpos'=>'3'));
				exit;
			}else{
				if(date('m') >= 9 && date('m') <= 12){
					$session = date('Y').'-'.(date('y')+1);
				}else{
					$session = (date('Y')-1).'-'.date('y');
				}	
				$ret_sql = $db->query("insert into ss_fees set student_user_id='".trim($db->escape($student_user_id))."',
				month='".trim($db->escape($monAry[0]))."',session='".$session."',
				year='".trim($db->escape($monAry[1]))."',amount='".trim($db->escape($amount))."',
				receipt_no='".trim($db->escape($receipt_no))."',remarks='".trim($db->escape($remarks))."',
				created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."'");
				if($ret_sql){
					$history = studentFeesHistory($student_user_id);
		
					echo json_encode(array('msg'=>'Payment saved successfully','code'=>1,'history'=>$history));
					exit;
				}else{
					echo json_encode(array('msg'=>'Process failed','code'=>0,'_errpos'=>'2'));
					exit;
				}
			}
		}
	}
}
//==========================EDIT FEES=====================
elseif($_POST['action'] == 'edit_fees'){
	$student_user_id = $_POST['student'];
	$start_month = $_POST['start_month'];
	$end_month = $_POST['end_month'];
	$amount = $_POST['amount'];
	$receipt_no = $_POST['receipt_no'];
	$remarks = $_POST['remarks'];
	$fees_id = $_POST['fees_id'];
	if($end_month == ''){
		$monAry = explode('-',$start_month);
		//CHECK RECEIPT NUMBER
		$receiptNoCheck = $db->get_row("select * from ss_fees where receipt_no='".$receipt_no."' and id <> '".$fees_id."'");	
		if(!empty($receiptNoCheck)){
			echo json_encode(array('msg'=>'Receipt number already exists','code'=>0,'_errpos'=>'3'));
			exit;
		}else{
			$ret_sql = $db->query("update ss_fees set student_user_id='".$student_user_id."',month='".trim($db->escape($monAry[0]))."',
			year='".trim($db->escape($monAry[1]))."',amount='".$amount."',receipt_no='".trim($db->escape($receipt_no))."',
			remarks='".trim($db->escape($remarks))."',
			updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id='".$fees_id."'");
		}
		if($ret_sql){
			echo json_encode(array('msg'=>'Payment updated successfully','code'=>1));
			exit;
		}else{
			echo json_encode(array('msg'=>'Process failed','code'=>0,'_errpos'=>'2'));
			exit;
		}
	}
}
?>