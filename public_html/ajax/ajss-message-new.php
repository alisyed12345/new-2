<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
} 
//==========================LIST FEES===================== 
if($_GET['action'] == 'list_messages'){
	$finalAry = array();
	if(check_userrole_by_code('UT05')){
		//MESSAGES FOR PARENTS
		$family_id = $db->get_var("select id from ss_family where user_id = '".$_SESSION['icksumm_uat_login_userid']."'");		
		$all_msg = $db->get_results("SELECT *,md5(id) as msgid FROM ss_message WHERE id IN (SELECT MAX(id) FROM ss_message where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' or rec_user_id = '".$_SESSION['icksumm_uat_login_userid']."' or rec_group_id in (SELECT group_id FROM ss_studentgroupmap WHERE latest = 1 AND student_user_id IN (SELECT user_id FROM ss_student WHERE family_id = '".$family_id."')) GROUP BY msg_set_no)",ARRAY_A);
	}elseif(check_userrole_by_code('UT01') && check_userrole_by_group('admin')){
		$all_msg = $db->get_results("SELECT *,md5(id) as msgid FROM ss_message WHERE id IN (SELECT MAX(id) FROM ss_message GROUP BY msg_set_no)",ARRAY_A);
	}else{
		//MESSAGES FOR ALL USERS EXCEPT PARENTS
		$all_msg = $db->get_results("SELECT *,md5(id) as msgid FROM ss_message WHERE id IN (SELECT MAX(id) FROM ss_message where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' or rec_user_id = '".$_SESSION['icksumm_uat_login_userid']."' GROUP BY msg_set_no)",ARRAY_A);
	}
	for($i=0; $i<count((array)$all_msg); $i++){
		//SENDER
		if($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid']){
			$all_msg[$i]['sen_name'] = 'Me';
		}else{
			$all_msg[$i]['sen_name'] = getUserFullName($all_msg[$i]['created_by_user_id']);
		}
		//RECEIVER
		if($all_msg[$i]['rec_user_id'] > 0){
			//MESSAGE FOR SPECIFIC USER - ONE TO ONE
			if($all_msg[$i]['rec_user_id'] == $_SESSION['icksumm_uat_login_userid']){
				$all_msg[$i]['rec_name'] = 'Me';
			}else{
				$all_msg[$i]['rec_name'] = getUserFullName($all_msg[$i]['rec_user_id']);	
			}		
		}else{
			//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
			$group = $db->get_row("select * from ss_groups where id = '".$all_msg[$i]['rec_group_id']."'");
			$all_msg[$i]['rec_name'] = 'Group '.$group->group_name;
		}	
		if(date('m/d/Y',strtotime($all_msg[$i]['created_on'])) == date('m/d/Y')){
			$all_msg[$i]['msg_datetime'] = date('h:i a',strtotime($all_msg[$i]['created_on']));
		}else{
			$all_msg[$i]['msg_datetime'] = date('m/d/Y',strtotime($all_msg[$i]['created_on']));
		}
		if(strlen($all_msg[$i]['message']) > 100){
			$all_msg[$i]['message'] = substr($all_msg[$i]['message'],0,100).'...';
		}
		// if($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid']){
		// 	$all_msg[$i]['message'] = '<span class="label label-warning">Me</span> '.$all_msg[$i]['message'];
		// }
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}
//=====================ALL MESSAGES OF RECEIVER==================
elseif($_GET['action'] == 'list_rec_messages'){
	$finalAry = array();
	$msg_info = $db->get_row("select * from ss_message where md5(id) = '".$_GET['mid']."'");
	if($msg_info->created_by_user_id != $_SESSION['icksumm_uat_login_userid'] && $msg_info->rec_user_id != $_SESSION['icksumm_uat_login_userid']){
		$msgOfThirdPerson = $msg_info->created_by_user_id;
	}
	if(trim($msg_info->rec_user_id) > 0){
		//MESSAGE FOR SPECIFIC USER - ONE TO ONE
		$all_msg = $db->get_results("select * from ss_message where (created_by_user_id = '".$msg_info->created_by_user_id."' and rec_user_id = '".$msg_info->rec_user_id."') or (created_by_user_id = '".$msg_info->rec_user_id."' and rec_user_id = '".$msg_info->created_by_user_id."') order by id desc",ARRAY_A);
	}else{
		//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
		$all_msg = $db->get_results("select * from ss_message where created_by_user_id = '".$msg_info->created_by_user_id."' and rec_group_id = '".$msg_info->rec_group_id."' order by id desc",ARRAY_A);
	}
	for($i=0; $i<count((array)$all_msg); $i++){	
		if(date('m/d/Y',strtotime($all_msg[$i]['created_on'])) == date('m/d/Y')){
			$msg_datetime = date('h:i a',strtotime($all_msg[$i]['created_on']));
		}else{
			$msg_datetime = date('m/d/Y h:i a',strtotime($all_msg[$i]['created_on']));
		}
		if($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid']){
			$personName = "me";
		}else{
			$personName = getUserFullName($all_msg[$i]['created_by_user_id']);
		}
		if($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid'] || $all_msg[$i]['created_by_user_id'] == $msgOfThirdPerson){
			$all_msg[$i]['message'] = '<div class="my_msg"> '.nl2br($all_msg[$i]['message']).'<br><span class="msg_signature">by '.$personName.' at '.$msg_datetime.'</span></div>';
		}else{
			$all_msg[$i]['message'] = '<div class="other_msg"> '.nl2br($all_msg[$i]['message']).'<br><span class="msg_signature">by '.$personName.' at '.$msg_datetime.'</span></div>';
		}
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
	/*$finalAry = array();
	
	$msg_info = $db->get_row("select * from ss_message where md5(id) = '".$_GET['mid']."'");
	
	if($msg_info->created_by_user_id != $_SESSION['icksumm_uat_login_userid'] && $msg_info->rec_user_id != $_SESSION['icksumm_uat_login_userid']){
		$msgOfThirdPerson = $msg_info->created_by_user_id;
	}

	if(trim($msg_info->rec_user_id) > 0){
		//MESSAGE FOR SPECIFIC USER - ONE TO ONE
		$all_msg = $db->get_results("select * from ss_message where (created_by_user_id = '".$msg_info->created_by_user_id."' and rec_user_id = '".$msg_info->rec_user_id."') or (created_by_user_id = '".$msg_info->rec_user_id."' and rec_user_id = '".$msg_info->created_by_user_id."') order by id desc",ARRAY_A);
	}else{
		//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
		$all_msg = $db->get_results("select * from ss_message where created_by_user_id = '".$msg_info->created_by_user_id."' and rec_group_id = '".$msg_info->rec_group_id."' order by id desc",ARRAY_A);
	}

	for($i=0; $i<count((array)$all_msg); $i++){	
		if(date('m/d/Y',strtotime($all_msg[$i]['created_on'])) == date('m/d/Y')){
			$msg_datetime = date('h:i a',strtotime($all_msg[$i]['created_on']));
		}else{
			$msg_datetime = date('m/d/Y h:i a',strtotime($all_msg[$i]['created_on']));
		}
		
		if($all_msg[$i]['created_by_user_id'] == $_SESSION['login_userid']){
			$personName = "me";
		}else{
			$personName = getUserFullName($all_msg[$i]['created_by_user_id']);
		}

		if($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid'] || $all_msg[$i]['created_by_user_id'] == $msgOfThirdPerson){
			$all_msg[$i]['message'] = '<div class="my_msg"> '.nl2br($all_msg[$i]['message']).'<br><span class="msg_signature">by '.$personName.' at '.$msg_datetime.'</span></div>';
		}else{
			$all_msg[$i]['message'] = '<div class="other_msg"> '.nl2br($all_msg[$i]['message']).'<br><span class="msg_signature">by '.$personName.' at '.$msg_datetime.'</span></div>';
		}
	}
	
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;*/
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
//==========================SAVE MESSAGE=====================
elseif($_POST['action'] == 'save_message'){
	$staff = $_POST['staff'];
	$group = $_POST['group'];
	$student = $_POST['student'];
	$message = $_POST['message'];
	$db->query('BEGIN');
	if($staff != ''){
		$msg_set_no = $db->get_var("select msg_set_no from ss_message where (rec_user_id='".$staff."' and created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."') or (rec_user_id='".$_SESSION['icksumm_uat_login_userid']."' and created_by_user_id = '".$staff."')");
		if(trim($msg_set_no) == ''){
			$msg_set_no = time();
		}
		$ret_sql = $db->query("insert into ss_message set rec_user_id='".$staff."', rec_group_id= NULL, msg_set_no = '".$msg_set_no."',
		message='".$db->escape(trim($message))."',is_read='0', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
		created_on='".date('Y-m-d H:i:s')."'");
	}else{
		if($student == ''){
			$ret_sql = $db->query("insert into ss_message set rec_user_id = NULL, rec_group_id = '".$group."', msg_set_no = '".$group."',
			message='".$db->escape(trim($message))."',is_read='0', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			created_on='".date('Y-m-d H:i:s')."'");
		}else{
			$msg_set_no = $db->get_var("select msg_set_no from ss_message where (rec_user_id='".$student."' and created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."') or (rec_user_id='".$_SESSION['icksumm_uat_login_userid']."' and created_by_user_id = '".$student."')");
			if(trim($msg_set_no) == ''){
				$msg_set_no = time();
			}		
			$ret_sql = $db->query("insert into ss_message set rec_user_id = '".$student."', rec_group_id = NULL, msg_set_no = '".$msg_set_no."',
			message='".$db->escape(trim($message))."',is_read='0', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			created_on='".date('Y-m-d H:i:s')."'");
		}
	}
	if($ret_sql && $db->query('COMMIT') !== false){
		echo json_encode(array('msg'=>'Messsage sent successfully','code'=>1));
		exit;
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('msg'=>'Messsage failed','code'=>0,'_errpos'=>'1'));
		exit;
	}
}
//==========================REPLY MESSAGE=====================
elseif($_POST['action'] == 'reply_message'){
	$msg_info = $db->get_row("select * from ss_message where md5(id) = '".$_POST['mid']."'");
	$db->query('BEGIN');
	if(trim($msg_info->rec_user_id) > 0){
		//MESSAGE FOR SPECIFIC USER - ONE TO ONE
		if($msg_info->rec_user_id == $_SESSION['icksumm_uat_login_userid']){
			$user_id = $msg_info->created_by_user_id;
		}else{
			$user_id = $msg_info->rec_user_id;
		}		
		$ret_sql = $db->query("insert into ss_message set rec_user_id='".$user_id."', rec_group_id= NULL, msg_set_no = '".$msg_info->msg_set_no."',
		message='".$db->escape(trim($_POST['message']))."',is_read='0', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
		created_on='".date('Y-m-d H:i:s')."'");
	}else{
		//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
		if(check_userrole_by_code('UT03') || check_userrole_by_code('UT05')){
			$groupMap = $db->get_row("select * from ss_staffgroupmap where active = 1 and group_id = '".$msg_info->rec_group_id."' order by id limit 1");
			$ret_sql = $db->query("insert into ss_message set rec_user_id = '".$groupMap->staff_user_id."', rec_group_id= NULL, 
			msg_set_no = '".$msg_info->msg_set_no."',
			message='".$db->escape(trim($_POST['message']))."',is_read='0', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
			created_on='".date('Y-m-d H:i:s')."'");
		}else{
			$ret_sql = $db->query("insert into ss_message set rec_user_id = NULL, rec_group_id= '".$msg_info->rec_group_id."', 
			msg_set_no = '".$msg_info->msg_set_no."', message='".$db->escape(trim($_POST['message']))."',is_read='0', 
			created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."'");
		}
	}
	if($ret_sql && $db->query('COMMIT') !== false){
		echo json_encode(array('msg'=>'Messsage sent successfully','code'=>1));
		exit;
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('msg'=>'Messsage not sent','code'=>0,'_errpos'=>'1'));
		exit;
	}
}

//COMMENTED ON 24-OCT-2018
//==========================SAME MASS EMAIL TO QUEUE=====================
/*elseif($_POST['action'] == 'save_mass_email_to_queue'){
	//ADDED ON 14-MAY-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit','1024M');
	
	$db->query('BEGIN');
	
	$group = $_POST['group'];
	$student = $_POST['student'];
	$cc_emails = explode(',',$_POST['cc']);
	$bcc_emails = explode(',',$_POST['bcc']);
	$subject = $db->escape($_POST['subject']);
	$message = $db->escape($_POST['message']);
	
	if($group == 'all_groups' && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
	}elseif(is_numeric($group) && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");
	}elseif(is_numeric($student)){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."')");
	}
	
	$emailStatus = false;
	
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' order by id desc limit 1");
	
	if($last_msg_time_diff > 4){
		$sql_bulk_msg = "insert into ss_bulk_message set subject = '".$subject."', message = '".$message."', 
		created_on = '".date('Y-m-d H:i:s')."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'";
		
		if($db->query($sql_bulk_msg )){
			$message_id = $db->insert_id;
			
			foreach($families as $fam){
				if(trim($fam->primary_email) != ''){
					$to_primary = $fam->primary_email;
					//$to_primary = 'moh.urooj@gmail.com';
					
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id = '".$message_id."', receiver_email = '".$to_primary."', 
					is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}  
				
				if(trim($fam->secondary_email) != ''){
					$to_secondary = $fam->secondary_email;
					//$to_secondary = 'moh.urooj@gmail.com';
					
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id = '".$message_id."', receiver_email = '".$to_secondary."', 
					is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}
			}
			
			foreach($cc_emails as $cc){
				if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {
					//$cc = 'moh.urooj@gmail.com';
					
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id='".$message_id."', receiver_email='".trim($cc)."', is_cc=1, 
					is_bcc=0, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}
			}
			
			foreach($bcc_emails as $bcc){ 
				//$bcc = 'moh.urooj@gmail.com';
					
				if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id='".$message_id."', receiver_email='".trim($bcc)."', is_cc=0, 
					is_bcc=1, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}
			}
			
			if($emailStatus && $db->query('COMMIT') !== false){
				echo json_encode(array('msg'=>'Email(s) queue created successfully','code'=>1));
				exit;
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('msg'=>"Email(s) queue not created. Please try again.",'code'=>0));
				exit;
			}
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('msg'=>"Email(s) queue not created. Please try again.",'code'=>0));
			exit;
		}
	}else{
		echo json_encode(array('msg'=>'Email(s) queue created successfully','code'=>1));
		exit;
	}
}*/

//ADDED ON 24-OCT-2018
//==========================SAME MASS EMAIL TO QUEUE=====================
elseif($_POST['action'] == 'save_mass_email_to_queue'){
	//ADDED ON 14-MAY-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit','1024M');
	$db->query('BEGIN');
	$group = $_POST['group'];
	$student = $_POST['student'];
	$cc_emails = explode(',',$_POST['cc']);
	$bcc_emails = explode(',',$_POST['bcc']);
	$subject = $db->escape($_POST['subject']);
	$message = $db->escape($_POST['message']);
	$attachmentfiles = array();
	$attachmentfiles = $_POST['attachmentfile'];
	if($group == 'all_groups' && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
	}elseif(is_numeric($group) && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");
	}elseif(is_numeric($student)){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."')");
	}
	$emailStatus = false;
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' order by id desc limit 1");
	
	if($last_msg_time_diff > 4 || $last_msg_time_diff == ""){
		$sql_bulk_msg = "insert into ss_bulk_message set subject = '".$subject."', message = '".$message."', is_report_gen = 0, 
		created_on = '".date('Y-m-d H:i:s')."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'";
		if($db->query($sql_bulk_msg )){
			$message_id = $db->insert_id;
			foreach($families as $fam){
				if(trim($fam->primary_email) != ''){
					$to_primary = $fam->primary_email;
					//$to_primary = 'moh.urooj@gmail.com';
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id = '".$message_id."', receiver_email = '".$to_primary."', 
					is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}  
				if(trim($fam->secondary_email) != ''){
					$to_secondary = $fam->secondary_email;
					//$to_secondary = 'moh.urooj@gmail.com';	
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id = '".$message_id."', receiver_email = '".$to_secondary."', 
					is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}
			}
			foreach($cc_emails as $cc){
				if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {
					//$cc = 'moh.urooj@gmail.com';
					
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id='".$message_id."', receiver_email='".trim($cc)."', is_cc=1, 
					is_bcc=0, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}
			}
			foreach($bcc_emails as $bcc){ 
				//$bcc = 'moh.urooj@gmail.com';
				if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {
					if($db->query("insert into ss_bulk_message_emails set bulk_message_id='".$message_id."', receiver_email='".trim($bcc)."', is_cc=0, 
					is_bcc=1, delivery_status = 2, attempt_counter = 0")){
						$emailStatus = true;
					}
				}
			}
			foreach($attachmentfiles as $attach){
				//$attach = date('ymdHi').'-'.$attach;
				if($db->query("insert into ss_bulk_message_attachment set bulk_message_id='".$message_id."', attachment_file='".$attach."'")){
					$emailStatus = true;
				}else{
					$emailStatus = false;
				}
			}
			if($emailStatus && $db->query('COMMIT') !== false){
				echo json_encode(array('msg'=>'Email(s) queue created successfully','code'=>1));
				exit;
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('msg'=>"Email(s) queue not created. Please try again.",'code'=>0));
				exit;
			}
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('msg'=>"Email(s) queue not created. Please try again.",'code'=>0));
			exit;
		}
	}else{
		echo json_encode(array('msg'=>'Email(s) queue created successfully','code'=>1));
		exit;
	}
}

//==========================SAME MASS TEXT SMS TO QUEUE=====================
elseif($_POST['action'] == 'save_mass_text_msg_to_queue'){
	//ADDED ON 02-OCT-2018
	$db->query('BEGIN');
	if(is_array($_POST['teacher'])){
		$teacher_ary = array();
		$teacher_ary = $_POST['teacher'];
		$teacher = implode(',',$teacher_ary);
	}else{
		$teacher = $_POST['teacher'];
	}
	$group = $_POST['group'];
	$student = $_POST['student'];
	$message = $db->escape($_POST['message']);
	if($group == 'all_groups' && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id in (SELECT group_id FROM ss_classtime WHERE id IN (SELECT classtime_id FROM ss_staffclasstimemap 
		WHERE active = 1 and staff_user_id IN (".$teacher.")))) order by s.first_name,s.last_name)");
	}elseif(is_numeric($group) && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");
	}elseif(is_numeric($student)){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."')");
	}else{
		$staffs = $db->get_results("select * from ss_staff where user_id in (".$teacher.")");
	}
	$smsStatus = false;
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', created_on)) as time_diff from ss_bulk_sms 
	where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' order by id desc limit 1");
	if($last_msg_time_diff > 4 || $last_msg_time_diff == ''){
		$sql_bulk_msg = "insert into ss_bulk_sms set message = '".$message."', para_teacher = '".$teacher."', para_group = '".$group."', 
		para_parents_of = '".$student."', created_on = '".date('Y-m-d H:i:s')."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'";
		if($db->query($sql_bulk_msg )){
			$message_id = $db->insert_id;	
			foreach($families as $fam){
				$father_phone = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->father_phone)))))));
				$father_area_code = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->father_area_code)))))));
				if(strlen($father_phone) == 10){
					$father_mobile_no = $father_phone;
				}elseif(strlen($father_area_code.$father_phone) == 10){
					$father_mobile_no = $father_area_code.$father_phone;
				}
				if(strlen($father_mobile_no) == 10){
					//$father_mobile_no = 'test mobile no';
					if(is_numeric($fam->user_id)){
						$receiver_user_id = $fam->user_id;
					}else{
						$receiver_user_id = 'NULL';
					}
					if($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '".$message_id."', receiver_user_id = ".$receiver_user_id.", 
					receiver_mobile_no = '".$father_mobile_no."', delivery_status = 2, attempt_counter = 0")){
						$smsStatus = true;
					}
				}  
				$mother_phone = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->mother_phone)))))));
				$mother_area_code = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->mother_area_code)))))));
				if(strlen($mother_phone) == 10){
					$mother_mobile_no = $mother_phone;
				}elseif(strlen($mother_area_code.$mother_phone) == 10){
					$mother_mobile_no = $mother_area_code.$mother_phone;
				}
				if(strlen($mother_mobile_no) == 10){
					//$mother_mobile_no = 'test mobile no';
					if(is_numeric($fam->user_id)){
						$receiver_user_id = $fam->user_id;
					}else{
						$receiver_user_id = 'NULL';
					}
					if($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '".$message_id."', receiver_user_id = ".$receiver_user_id.",
					receiver_mobile_no = '".$mother_mobile_no."', delivery_status = 2, attempt_counter = 0")){
						$smsStatus = true;
					}
				}
			}
			foreach($staffs as $sta){
				$mobile = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$sta->mobile)))))));
				$phone = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$sta->phone)))))));
				if(strlen($mobile) == 10){
					$contact_no = $mobile;
				}elseif(strlen($phone) == 10){
					$contact_no = $phone;
				}
				if(strlen($contact_no) == 10){
					//$contact_no = 'test mobile no';
					
					if($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '".$message_id."', receiver_user_id = '".$sta->user_id."', 
					receiver_mobile_no = '".$contact_no."', delivery_status = 2, attempt_counter = 0")){
						$smsStatus = true;
					}
				}  
			}
			if($smsStatus && $db->query('COMMIT') !== false){
				echo json_encode(array('msg'=>'Message(s) queue created successfully','code'=>1));
				exit;
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('msg'=>"Message(s) queue not created. Please try again.",'code'=>0,'_errpos'=>1));
				exit;
			}
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('msg'=>"Message(s) queue not created. Please try again.",'code'=>0,'_errpos'=>2));
			exit;
		}
	}else{
		echo json_encode(array('msg'=>'Message(s) queue created successfully','code'=>1));
		exit;
	}
	
	/*//ADDED ON 14-MAY-2018 - COMMENTED ON 02-OCT-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit','1024M');
	
	$db->query('BEGIN');
	
	$group = $_POST['group'];
	$student = $_POST['student'];
	$message = $db->escape($_POST['message']);
	
	if($group == 'all_groups' && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
	}elseif(is_numeric($group) && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");
	}elseif(is_numeric($student)){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."')");
	}
	
	$emailStatus = false;
	
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', created_on)) as time_diff from ss_bulk_sms where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' order by id desc limit 1");
	
	if($last_msg_time_diff > 4 || $last_msg_time_diff == ''){
		$sql_bulk_msg = "insert into ss_bulk_sms set message = '".$message."', 
		created_on = '".date('Y-m-d H:i:s')."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'";
		
		if($db->query($sql_bulk_msg )){
			$message_id = $db->insert_id;
			
			foreach($families as $fam){
				$father_phone = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->father_phone)))))));
				$father_area_code = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->father_area_code)))))));
				
				if(strlen($father_phone) == 10){
					$father_mobile_no = $father_phone;
				}elseif(strlen($father_area_code.$father_phone) == 10){
					$father_mobile_no = $father_area_code.$father_phone;
				}
				
				if(strlen($father_mobile_no) == 10){
					//$father_mobile_no = 'test mobile no';
					
					if($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '".$message_id."', 
					receiver_mobile_no = '".$father_mobile_no."', delivery_status = 2, attempt_counter = 0")){
						$smsStatus = true;
					}
				}  
				
				$mother_phone = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->mother_phone)))))));
				$mother_area_code = str_replace(' ','',str_replace(')','',str_replace('(','',(str_replace('.','',(str_replace('-','',$fam->mother_area_code)))))));
				
				if(strlen($mother_phone) == 10){
					$mother_mobile_no = $mother_phone;
				}elseif(strlen($mother_area_code.$mother_phone) == 10){
					$mother_mobile_no = $mother_area_code.$mother_phone;
				}
				
				if(strlen($mother_mobile_no) == 10){
					//$mother_mobile_no = 'test mobile no';
					
					if($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '".$message_id."', 
					receiver_mobile_no = '".$mother_mobile_no."', delivery_status = 2, attempt_counter = 0")){
						$smsStatus = true;
					}
				}
			}
			
			if($smsStatus && $db->query('COMMIT') !== false){
				echo json_encode(array('msg'=>'Message(s) queue created successfully','code'=>1));
				exit;
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('msg'=>"Message(s) queue not created. Please try again.",'code'=>0,'_errpos'=>1));
				exit;
			}
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('msg'=>"Message(s) queue not created. Please try again.",'code'=>0,'_errpos'=>2));
			exit;
		}
	}else{
		echo json_encode(array('msg'=>'Message(s) queue created successfully','code'=>1));
		exit;
	}*/
}

//==========================VIEW MAIL DETAILS=====================
elseif($_POST['action'] == 'view_mail_detail'){
	$msgid = $_POST['msgid'];
	
	$msg = $db->get_row("SELECT * FROM ss_bulk_message WHERE md5(id) = '".$msgid."'");
	
	$retStr = '<div class="row">
              <div class="col-md-12">
                  <label>Subject:</label>'.$msg->subject.'
			  </div>
			  </div>
			<div class="row">
			  <div class="col-md-12">
                  <label>Message:</label>'.nl2br($msg->message).'
              </div>			 
			</div>';
			
	$attachments = $db->get_results("select * from ss_bulk_message_attachment where md5(bulk_message_id) = '".$msgid."'");
	foreach($attachments as $attach){
		if(trim($attachmentFileURL) == ''){
			$attachmentFileURL = "<a href='".SITEURL.'message/attachments/'.$attach->attachment_file."' target='_blank'>".$attach->attachment_file."</a>";
		}else{
			$attachmentFileURL = $attachmentFileURL.", <a href='".SITEURL.'message/attachments/'.$attach->attachment_file."' target='_blank'>".$attach->attachment_file."</a>";
		}
	}	
	if(count((array)$attachments)){
		$retStr .= '
			<div class="row">
			  <div class="col-md-12">
                  <label>Attachments:</label>'.$attachmentFileURL.'
              </div>			 
			</div>';
	}
	echo $retStr;
	exit;
}
//==========================VIEW SMS DETAILS=====================
elseif($_POST['action'] == 'view_sms_detail'){
	$msgid = $_POST['msgid'];	
	$to = $_POST['to'];
	$msgtype = strtolower($_POST['msgtype']);
	//if(is_numeric($to)){
	if($msgtype == 'reply'){	
		$msgs = $db->get_results("SELECT received_raw_data AS message, 'reply' AS src, created_on FROM ss_bulk_sms_reply 
		WHERE sender_mobile_no = '".$to."' or sender_mobile_no = '".substr($to,1)."' 
		UNION SELECT s.message, 'new' AS src, s.created_on FROM ss_bulk_sms s 
		INNER JOIN ss_bulk_sms_mobile m ON s.id = m.bulk_sms_id WHERE m.receiver_mobile_no = '".$to."' 
		or m.receiver_mobile_no = '".substr($to,1)."' ORDER BY created_on DESC");
		foreach($msgs as $ms){
			if(date('m/d/Y',strtotime($ms->created_on)) == date('m/d/Y')){
				$msg_datetime = date('h:i a',strtotime($ms->created_on));
			}else{
				$msg_datetime = date('m/d/Y h:i a',strtotime($ms->created_on));
			}
			if($ms->src == 'reply'){
				$msg_dec = json_decode($ms->message);
				$message_text .= '<div class="text-left" style="width:100%; margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> '.$msg_dec->text.'<br><span class="msg_signature">by Customer at '.$msg_datetime.'</span></div>';
			}else{
				$message_text .= '<div class="text-right" style="width:100%;  margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> '.$ms->message.'<br><span class="msg_signature">by '.CENTER_SHORTNAME.' at '.$msg_datetime.'</span></div>';
			}
		}
	}else{
		$msg = $db->get_row("SELECT * FROM ss_bulk_sms WHERE md5(id) = '".$msgid."'");
		if(date('m/d/Y',strtotime($msg->created_on)) == date('m/d/Y')){
			$msg_datetime = date('h:i a',strtotime($msg->created_on));
		}else{
			$msg_datetime = date('m/d/Y h:i a',strtotime($msg->created_on));
		}
		$message_text .= '<div class="text-right" style="width:100%;">'.nl2br($msg->message).'<br><span class="msg_signature">by '.CENTER_SHORTNAME.' at '.$msg_datetime.'</span></div>';
		//$message_text .= '<div class="my_msg"> '.$msg->message.'<br><span class="msg_signature"> at '.$msg_datetime.'</span></div>';	
		//$message_text = nl2br($msg->message);
	}
	$retStr = '<div class="row"><div class="col-md-12">'.$message_text.'</div></div>';
	echo $retStr;
	exit;
}

//==========================SEND REPLY TO SMS=====================
elseif($_POST['action'] == 'send_reply_msg'){
	$mobileno = $_POST['rec_mobile_no'];
	$message = $_POST['message']; 
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', created_on)) as time_diff from ss_bulk_sms 
	where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' order by id desc limit 1");
	if($last_msg_time_diff > 4 || $last_msg_time_diff == ''){
		$sql_bulk_msg = "insert into ss_bulk_sms set message = '".$message."', created_on = '".date('Y-m-d H:i:s')."', 
		is_reply = 1, request_from = 'reply_model', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'";	
		if($db->query($sql_bulk_msg )){
			$message_id = $db->insert_id;			
			$smsStatus = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '".$message_id."', 
			receiver_mobile_no = '".$mobileno."', delivery_status = 2, attempt_counter = 0");
			if($smsStatus && $db->query('COMMIT') !== false){
				echo json_encode(array('msg'=>'Message saved successfully','code'=>1));
				exit;
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('msg'=>"Message not saved. Please try again.",'code'=>0,'_errpos'=>1));
				exit;
			}
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('msg'=>"Message not saved. Please try again.",'code'=>0,'_errpos'=>2));
			exit;
		}
	}else{
		echo json_encode(array('msg'=>'Message(s) queue created successfully','code'=>1));
		exit;
	}
}
//==========================VIEW SMS REPLY=====================
elseif($_POST['action'] == 'view_sms_reply'){
	$msgid = $_POST['msgid'];	
	$mobileno = $_POST['mobileno'];
	$msgs = $db->get_results("SELECT received_raw_data AS message, 'reply' AS src, created_on FROM ss_bulk_sms_reply 
	WHERE sender_mobile_no = '".$mobileno."' or sender_mobile_no = '".substr($mobileno,1)."' UNION 
	SELECT s.message, 'new' AS src, s.created_on FROM ss_bulk_sms s 
	INNER JOIN ss_bulk_sms_mobile m ON s.id = m.bulk_sms_id WHERE m.receiver_mobile_no = '".$mobileno."' 
	or m.receiver_mobile_no = '".substr($mobileno,1)."' ORDER BY created_on DESC");
	foreach($msgs as $ms){
		if(date('m/d/Y',strtotime($ms->created_on)) == date('m/d/Y')){
			$msg_datetime = date('h:i a',strtotime($ms->created_on));
		}else{
			$msg_datetime = date('m/d/Y h:i a',strtotime($ms->created_on));
		}
		if($ms->src == 'reply'){
			$msg_dec = json_decode($ms->message);
			$message_text .= '<div class="text-left" style="width:100%; margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> '.$msg_dec->text.'<br><span class="msg_signature">by Customer at '.$msg_datetime.'</span></div>';
		}else{
			$message_text .= '<div class="text-right" style="width:100%;  margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> '.$ms->message.'<br><span class="msg_signature">by '.CENTER_SHORTNAME.' at '.$msg_datetime.'</span></div>';
		}
	}
	$retStr = '<div class="row"><div class="col-md-12">'.$message_text.'</div></div>';
	echo $retStr;
	exit;
}
//==========================RESEND MASS EMAIL=====================
elseif($_POST['action'] == 'resend_mass_emails'){	
	$message_id = $db->get_var("select id from ss_bulk_message where md5(id) = '".$_POST['msgid']."'");
	$status = $db->query("update ss_bulk_message_emails set delivery_status = 2, attempt_counter = 0 where bulk_message_id = '".$message_id."' and delivery_status = 0");
	if($status){
		echo json_encode(array('msg'=>'Email(s) queued for resending','code'=>1));
		exit;
	}else{
		echo json_encode(array('msg'=>"Email(s) resend process failed. Please try again.",'code'=>0));
		exit;
	}
}
//==========================RESEND MASS SMS=====================
elseif($_POST['action'] == 'resend_mass_sms'){	
	$message_id = $db->get_var("select id from ss_bulk_sms where md5(id) = '".$_POST['msgid']."'");
	$status = $db->query("update ss_bulk_sms_mobile set delivery_status = 2, attempt_counter = 0 where bulk_sms_id = '".$message_id."' 
	and delivery_status = 0");
	if($status){
		echo json_encode(array('msg'=>'Message(s) queued for resending','code'=>1));
		exit;
	}else{
		echo json_encode(array('msg'=>"Message(s) resend process failed. Please try again.",'code'=>0));
		exit;
	}
	/*$message_id = $db->get_var("select id from ss_bulk_sms where md5(id) = '".$_POST['msgid']."'");
	
	$status = $db->query("update ss_bulk_sms_mobile set delivery_status = 2, attempt_counter = 0 where bulk_sms_id = '".$message_id."' and delivery_status = 0");
	
	if($status){
		echo json_encode(array('msg'=>'Message(s) queued for resending','code'=>1));
		exit;
	}else{
		echo json_encode(array('msg'=>"Message(s) resend process failed. Please try again.",'code'=>0));
		exit;
	}*/
}
//==========================MASS EMAIL HISTORY=====================
elseif($_GET['action'] == 'mass_email_history'){
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	if(check_userrole_by_code('UT01') || check_userrole_by_code('UT02')){	
		if(check_userrole_by_code('UT02')){	
			$all_msg = $db->get_results("SELECT *, md5(id) as msgid FROM ss_bulk_message where created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'",ARRAY_A);
		}else{
			$all_msg = $db->get_results("SELECT *, md5(id) as msgid FROM ss_bulk_message",ARRAY_A);
		}
		for($i=0; $i<count((array)$all_msg); $i++){
			$all_msg[$i]['sent'] = $db->get_var("select COUNT(1) from ss_bulk_message_emails where bulk_message_id = '".$all_msg[$i]['id']."' and delivery_status = 1");
			$all_msg[$i]['in_queue'] = $db->get_var("select COUNT(1) from ss_bulk_message_emails where bulk_message_id = '".$all_msg[$i]['id']."' and delivery_status = 2");
			$all_msg[$i]['failed'] = $db->get_var("select COUNT(1) from ss_bulk_message_emails where bulk_message_id = '".$all_msg[$i]['id']."' and delivery_status = 0");
			$all_msg[$i]['created_on'] = date('M d Y, h:i:s a',strtotime($all_msg[$i]['created_on']));
		}
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}
//==========================MASS SMS HISTORY=====================
elseif($_GET['action'] == 'mass_sms_history'){
	if(check_userrole_by_code('UT01')){
		$all_msg = $db->get_results("SELECT *, md5(id) as msgid FROM ss_bulk_sms",ARRAY_A);
		for($i=0; $i<count((array)$all_msg); $i++){
			$message = $all_msg[$i]['msg_type'] = $all_msg[$i]['is_reply'] == 0?'New':'Reply';
			$message = $all_msg[$i]['message'];
			$all_msg[$i]['message'] = strlen($message) > 50 ? (substr($message,0,50).'...') : $message;
			$para_parents_of = $all_msg[$i]['para_parents_of'];
			$para_group = $all_msg[$i]['para_group'];
			$para_teacher = $all_msg[$i]['para_teacher'];
			if(is_numeric($para_parents_of)){
				$student_name = $db->get_var("select concat(first_name,' ',last_name) from ss_student 
				where user_id = '".$para_parents_of."'");
				$all_msg[$i]['to'] = $student_name;	
			}elseif($para_parents_of == 'all_students'){
				if(is_numeric($para_group)){
					$group_name = $db->get_var("select group_name from ss_groups where id = '".$para_group."'");
					$all_msg[$i]['to'] = 'All students of group '.$group_name;	
				}else{
					$all_msg[$i]['to'] = 'All students of all groups';
				}
			}elseif(trim($para_teacher) != ''){
				$teachers = $db->get_results("select * from ss_staff where user_id in (".$para_teacher.")");
				foreach($teachers as $tea){
					if(trim($all_msg[$i]['to']) == ''){
						$all_msg[$i]['to'] = $tea->first_name.' '.$tea->last_name;
					}else{
						$all_msg[$i]['to'] = $all_msg[$i]['to'].', '.$tea->first_name.' '.$tea->last_name;
					}
				}
			}else{
				$all_msg[$i]['to'] = $db->get_var("select receiver_mobile_no from ss_bulk_sms_mobile 
				where bulk_sms_id = '".$all_msg[$i]['id']."'");
			}		
			$all_msg[$i]['sent'] = $db->get_var("select COUNT(1) from ss_bulk_sms_mobile where bulk_sms_id = '".$all_msg[$i]['id']."' and delivery_status = 1");
			$all_msg[$i]['in_queue'] = $db->get_var("select COUNT(1) from ss_bulk_sms_mobile where bulk_sms_id = '".$all_msg[$i]['id']."' and delivery_status = 2");
			$all_msg[$i]['failed'] = $db->get_var("select COUNT(1) from ss_bulk_sms_mobile where bulk_sms_id = '".$all_msg[$i]['id']."' and delivery_status = 0");
			$all_msg[$i]['created_on'] = date('M d Y, h:i:s a',strtotime($all_msg[$i]['created_on']));
		}
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}
//==========================MASS SMS REPLY=====================
elseif($_GET['action'] == 'mass_sms_reply'){
	if(check_userrole_by_code('UT01')){
		$all_msg = $db->get_results("SELECT *, md5(id) as msgid FROM ss_bulk_sms_reply",ARRAY_A);
		for($i=0; $i<count((array)$all_msg); $i++){
			$db->query("update ss_bulk_sms_reply set is_read = 1 where id = '".$all_msg[$i]['id']."'");
			$raw_data = json_decode($all_msg[$i]['received_raw_data'],true);
			$message = $raw_data['text'];
			$all_msg[$i]['message'] = strlen($message) > 75 ? (substr($message,0,75).'...') : $message;
			$all_msg[$i]['created_on'] = date('M d Y, h:i:s a',strtotime($all_msg[$i]['created_on']));
		}
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}

//==========================SAVE GROUP EMAIL TO QUEUE=====================
elseif($_POST['action'] == 'send_email_to_group'){
	//ADDED ON 14-MAY-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit','1024M');
	$db->query('BEGIN');
	$group = $_POST['groupid'];
	$subject = $db->escape($_POST['subject']);
	$message = $db->escape($_POST['message']);
	$cc_emails = explode(',',$_POST['cc']);
	$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
	ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
	WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");
	$db->query("insert into ss_bulk_message set subject = '".$subject."', message = '".$message."', request_from = 'teacher_time_table', is_report_gen = 0,
	created_on = '".date('Y-m-d H:i:s')."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."'");
	$message_id = $db->insert_id;
	$emailStatus = false;
	foreach($families as $fam){
		if(trim($fam->primary_email) != ''){
			$db->query("insert into ss_bulk_message_emails set bulk_message_id = '".$message_id."', receiver_email = '".$fam->primary_email."', 
			is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0");
			$emailStatus = true;
		}  
		if(trim($fam->secondary_email) != ''){
			$db->query("insert into ss_bulk_message_emails set bulk_message_id = '".$message_id."', receiver_email = '".$fam->secondary_email."', 
			is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0");
			$emailStatus = true;
		}
	}
	foreach($cc_emails as $cc){
		if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {
			if($db->query("insert into ss_bulk_message_emails set bulk_message_id='".$message_id."', receiver_email='".trim($cc)."', is_cc=1, 
			is_bcc=0, delivery_status = 2, attempt_counter = 0")){
				$emailStatus = true;
			}
		}
	}
	
	if($emailStatus && $db->query('COMMIT') !== false){
		echo json_encode(array('msg'=>'Email(s) queue created successfully','code'=>1));
		exit;
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('msg'=>"Email(s) queue not created. Please try again.",'code'=>0));
		exit;
	}
}
/*//==========================REPLY MESSAGE=====================
elseif($_POST['action'] == 'mass_email'){
	//ADDED ON 14-MAY-2018
	//set_time_limit ( 300 ); //300 SECONDS MEANS 5 MINUTES
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit','1024M');
	
	if(is_array($_POST['teacher'])){
		$teacher = implode(',',$teacher);
	}else{
		$teacher = $_POST['teacher'];
	}
	$group = $_POST['group'];
	$student = $_POST['student'];
	$cc_emails = explode(',',$_POST['cc']);
	$bcc_emails = explode(',',$_POST['bcc']);
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	
	if($group == 'all_groups' && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id in (select group_id from ss_staffgroupmap where staff_user_id in (".$teacher.") 
		and active = 1)) order by s.first_name,s.last_name)");
	}elseif(is_numeric($group) && $student == 'all_students'){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");
	}elseif(is_numeric($student)){
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."')");
	}
	
	$emailStatus = false;
	foreach($families as $fam){
		//if(send_my_mail('moh.urooj@gmail.com', $subject, $message, EMAIL_FROM_SCHOOL)){
		if(send_my_mail($fam->primary_email, $subject, $message, EMAIL_FROM_SCHOOL)){
			$emailStatus = true;
		}  
		
		//if(send_my_mail('moh.urooj@gmail.com', $subject, $message, EMAIL_FROM_SCHOOL)){
		if(trim($fam->secondary_email) != '' && send_my_mail($fam->secondary_email, $subject, $message, EMAIL_FROM_SCHOOL)){
			$emailStatus = true;
		}
	}
	
	foreach($cc_emails as $cc){
		if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {
			//if(send_my_mail('moh.urooj@gmail.com', $subject, $message, EMAIL_FROM_SCHOOL)){
			if(send_my_mail(trim($cc), $subject, $message, EMAIL_FROM_SCHOOL)){
				$emailStatus = true;
			}  
		}
	}
	
	foreach($bcc_emails as $bcc){ 		
		if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {
			//if(send_my_mail('moh.urooj@gmail.com', $subject, $message, EMAIL_FROM_SCHOOL)){
			if(send_my_mail(trim($bcc), $subject, $message, EMAIL_FROM_SCHOOL)){
				$emailStatus = true;
			}  
		}
	}
	
	if($emailStatus){
		echo json_encode(array('msg'=>'Email(s) sent successfully','code'=>1));
		exit;
	}else{
		echo json_encode(array('msg'=>"Email process failed. Please try again.",'code'=>0));
		exit;
	}
}*/
?>