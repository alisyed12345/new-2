<?php
include_once "../includes/config.php";
get_country()->timezone;
//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
	return;
}
//==========================LIST FEES===================== 
if ($_GET['action'] == 'list_messages') {
	$finalAry = array();

	if (check_userrole_by_code('UT05')) {

		//MESSAGES FOR PARENTS
		$family_id = $db->get_var("select id from ss_family where user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
		$all_msg = $db->get_results("SELECT m.*,MD5(m.id) AS msgid, g.group_name, c.class_name, GROUP_CONCAT( m.rec_user_id) as multiple_rec, t1.user_type_code AS rec_user_type_code,t2.user_type_code AS created_user_type_code 
		FROM ss_message AS m 
		INNER JOIN ss_usertype AS t1 ON t1.id=m.`rec_user_type_id` 
		INNER JOIN ss_usertype AS t2 ON t2.id=m.`created_user_type_id` 
		LEFT JOIN ss_groups g ON g.id = m.rec_group_id
		LEFT JOIN ss_classes c ON c.id = m.rec_class_id
		WHERE (( m.created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND t2.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."') OR (m.rec_user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND t1.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."'))
		GROUP BY m.msg_set_no", ARRAY_A);

		
	} elseif (check_userrole_by_code('UT01') && check_userrole_by_subgroup('admin')) {

		//Commented By 23SEP2022
		if ($_GET['filter'] == 'Parents') {
			$all_msg = $db->get_results("SELECT m.*,md5(m.id) as msgid, g.group_name, c.class_name, GROUP_CONCAT( m.rec_user_id) as multiple_rec, t1.user_type_code as rec_user_type_code,t2.user_type_code as created_user_type_code 
			FROM ss_message as m 
			inner join ss_usertype as t1 on t1.id=m.`rec_user_type_id` 
			inner join ss_usertype as t2 on t2.id=m.`created_user_type_id` 
			LEFT JOIN ss_groups g ON g.id = m.rec_group_id
			LEFT JOIN ss_classes c ON c.id = m.rec_class_id
			WHERE m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ( t1.user_type_code = 'UT05' OR  t2.user_type_code = 'UT05') GROUP BY m.msg_set_no", ARRAY_A);
		}else{
			$all_msg = $db->get_results("SELECT m.*,md5(m.id) as msgid, g.group_name, c.class_name, GROUP_CONCAT( m.rec_user_id) as multiple_rec, t1.user_type_code as rec_user_type_code,t2.user_type_code as created_user_type_code 
			FROM ss_message as m 
			inner join ss_usertype as t1 on t1.id=m.`rec_user_type_id` 
			inner join ss_usertype as t2 on t2.id=m.`created_user_type_id`
			LEFT JOIN ss_groups g ON g.id = m.rec_group_id
			LEFT JOIN ss_classes c ON c.id = m.rec_class_id 
			WHERE m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND t1.user_type_code <> 'UT05' AND t2.user_type_code <> 'UT05' GROUP BY m.msg_set_no", ARRAY_A);
		}



	} elseif (check_userrole_by_code('UT01') && check_userrole_by_subgroup('principal')) {


		if ($_GET['filter'] == 'Parents') {
			
			$all_msg = $db->get_results("SELECT m.*,md5(m.id) as msgid, g.group_name, c.class_name, GROUP_CONCAT( m.rec_user_id) as multiple_rec, t1.user_type_code as rec_user_type_code,t2.user_type_code as created_user_type_code 
			FROM ss_message as m 
			inner join ss_usertype as t1 on t1.id=m.`rec_user_type_id` 
			inner join ss_usertype as t2 on t2.id=m.`created_user_type_id` 
			LEFT JOIN ss_groups g ON g.id = m.rec_group_id
			LEFT JOIN ss_classes c ON c.id = m.rec_class_id 
			WHERE m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND ( t1.user_type_code = 'UT05' OR  t2.user_type_code = 'UT05') GROUP BY m.msg_set_no", ARRAY_A);
		}else{
			$all_msg = $db->get_results("SELECT m.*,md5(m.id) as msgid,  g.group_name, c.class_name, GROUP_CONCAT( m.rec_user_id) as multiple_rec, t1.user_type_code as rec_user_type_code,t2.user_type_code as created_user_type_code 
			FROM ss_message as m 
			inner join ss_usertype as t1 on t1.id=m.`rec_user_type_id` 
			inner join ss_usertype as t2 on t2.id=m.`created_user_type_id`
			LEFT JOIN ss_groups g ON g.id = m.rec_group_id
			LEFT JOIN ss_classes c ON c.id = m.rec_class_id 
			WHERE m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND t1.user_type_code <> 'UT05' AND t2.user_type_code <> 'UT05' GROUP BY m.msg_set_no", ARRAY_A);
		}

	} else {
		$all_msg = $db->get_results("SELECT m.*,MD5(m.id) AS msgid, g.group_name, c.class_name, GROUP_CONCAT( m.rec_user_id) as multiple_rec, t1.user_type_code AS rec_user_type_code,t2.user_type_code AS created_user_type_code 
		FROM ss_message AS m 
		INNER JOIN ss_usertype AS t1 ON t1.id=m.`rec_user_type_id` 
		INNER JOIN ss_usertype AS t2 ON t2.id=m.`created_user_type_id` 
		LEFT JOIN ss_groups g ON g.id = m.rec_group_id
		LEFT JOIN ss_classes c ON c.id = m.rec_class_id
		WHERE (( m.created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND t2.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."') OR (m.rec_user_id = '".$_SESSION['icksumm_uat_login_userid']."' AND t1.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."'))
		GROUP BY m.msg_set_no",ARRAY_A);

	}

	// echo "<pre>";
	// print_r($all_msg);
	// die;

	for ($i = 0; $i < count((array)$all_msg); $i++) {

		//SENDER
		if ($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid'] && $all_msg[$i]['created_user_type_code'] == $_SESSION['icksumm_uat_login_usertypecode']) {
			$all_msg[$i]['sen_name'] = 'Me';
		} else {
			$all_msg[$i]['sen_name'] = getUserFullName($all_msg[$i]['created_by_user_id']);
		}



	

		//RECEIVER
		if ($all_msg[$i]['is_all_parents'] == 0) {
			$admin_id = $db->get_var("select m.user_id from  ss_usertype t inner join ss_usertypeusermap m on t.id=m.user_type_id where t.user_type_subgroup = 'admin' and t.is_active =1");
			//MESSAGE FOR SPECIFIC USER - ONE TO ONE
			$multiple = $all_msg[$i]['multiple_rec'];
			$mul_arr = explode(',',$multiple);

			if (check_userrole_by_code('UT05') && $all_msg[$i]['rec_user_type_id'] == $_SESSION['icksumm_uat_login_userid']) {
				//$all_msg[$i]['rec_name'] = getUserFullName($all_msg[$i]['rec_user_id']);
				$all_msg[$i]['rec_name'] = 'Me';
			}elseif(count((array)$mul_arr) >= 1){
					$get_names =[];
					foreach($mul_arr as $val){
						if($val == $_SESSION['icksumm_uat_login_userid']){
							$get_names[] =	'Me';
						}else{
							$get_names[] =	getUserFullName($val);
						}
					
					}

					$names = implode(', ',array_unique($get_names));

					if(!empty($all_msg[$i]['group_name']) && !empty($all_msg[$i]['class_name'])){
						$all_group = $all_msg[$i]['group_name'] .' ( '.$all_msg[$i]['class_name'].' ) '. " : ";
					}elseif(!empty($all_msg[$i]['group_name'])){
						$all_group = $all_msg[$i]['group_name']. " : ";
					}else{
						$all_group = "";
					}
					$all_msg[$i]['rec_name'] = $all_group.$names;
			}




		} else {

			//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
			//$group = $db->get_row("select * from ss_groups where id = '" . $all_msg[$i]['rec_group_id'] . "'");
			if(!empty($all_msg[$i]['group_name']) && !empty($all_msg[$i]['class_name'])){
				$all_group = $all_msg[$i]['group_name'] .' ( '.$all_msg[$i]['class_name'].' ) '. " : All";
			}elseif(!empty($all_msg[$i]['group_name'])){
				$all_group = $all_msg[$i]['group_name']. " : All";
			}else{
				$all_group = "All";
			}
			$all_msg[$i]['rec_name'] = $all_group;

		}


		if (date('m/d/Y', strtotime($all_msg[$i]['created_on'])) == date('m/d/Y')) {
			$all_msg[$i]['msg_datetime'] = my_date_changer($all_msg[$i]['created_on']);
		} else {
			$all_msg[$i]['msg_datetime'] = my_date_changer($all_msg[$i]['created_on']);
		}

		if(check_userrole_by_code('UT01')){

			$mess = $db->get_row("select message from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and msg_set_no=".$all_msg[$i]['msg_set_no']." order by id desc");

		}else{
              
			// if(!empty(trim($all_msg[$i]['rec_group_id']))){
			// 	$mess = $db->get_row("select message from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and msg_set_no=".$all_msg[$i]['rec_group_id']." order by id desc");
			// }else{

				$mess = $db->get_row("SELECT m.message
				FROM ss_message as m 
				inner join ss_usertype as t1 on t1.id=m.`rec_user_type_id` 
				inner join ss_usertype as t2 on t2.id=m.`created_user_type_id` 
				WHERE m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and msg_set_no=".$all_msg[$i]['msg_set_no']." and ((t1.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."' or t2.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."' ) and (t1.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."' or t2.user_type_code = '".$_SESSION['icksumm_uat_login_usertypecode']."' ))
				and ((created_by_user_id = ".$_SESSION['icksumm_uat_login_userid']." or rec_user_id = ".$_SESSION['icksumm_uat_login_userid'].") or (created_by_user_id = ".$_SESSION['icksumm_uat_login_userid']." or rec_user_id = ".$_SESSION['icksumm_uat_login_userid'].")) order by m.id desc");

			//}

		}
		
		if (strlen($mess->message) > 100) {
			$all_msg[$i]['message'] = substr($mess->message, 0, 100) . '...';
		}else{
			$all_msg[$i]['message'] = $mess->message;
		}
	}

	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}
//=====================ALL MESSAGES OF RECEIVER==================
elseif ($_GET['action'] == 'list_rec_messages') {
	$finalAry = array();
	$msg_info = $db->get_row("select * from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and md5(id) = '" . $_GET['mid'] . "'");

	if ($msg_info->created_by_user_id != $_SESSION['icksumm_uat_login_userid'] && $msg_info->rec_user_id != $_SESSION['icksumm_uat_login_userid']) {
		$msgOfThirdPerson = $msg_info->created_by_user_id;
	}
	if (trim($msg_info->rec_user_id) > 0) {

		if(check_userrole_by_code('UT01')){
			$all_msg = $db->get_results("select * from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and ((created_by_user_id = ".$msg_info->created_by_user_id." or rec_user_id = ".$msg_info->created_by_user_id.") or (created_by_user_id = ".$msg_info->rec_user_id." or rec_user_id = ".$msg_info->rec_user_id.")) and msg_set_no = '" . $msg_info->msg_set_no . "' group by msg_set_no,created_by_user_id,created_on order by id desc", ARRAY_A);

		}else{

			$all_msg = $db->get_results("select * from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and ((created_by_user_id = ".$_SESSION['icksumm_uat_login_userid']." or rec_user_id = ".$_SESSION['icksumm_uat_login_userid'].") or (created_by_user_id = ".$_SESSION['icksumm_uat_login_userid']." or rec_user_id = ".$_SESSION['icksumm_uat_login_userid'].")) and msg_set_no = '" . $msg_info->msg_set_no . "' group by msg_set_no,created_by_user_id,created_on order by id desc", ARRAY_A);

		}

	} else {
		//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
		$all_msg = $db->get_results("select * from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
		and created_by_user_id = '" . $msg_info->created_by_user_id . "' and rec_group_id = '" . $msg_info->rec_group_id . "' order by id desc", ARRAY_A);
	}
	for ($i = 0; $i < count((array)$all_msg); $i++) {
		$db->query("update ss_message set is_read = 1 where id = '" . $all_msg[$i]['id'] . "'");
		if (date('m/d/Y', strtotime($all_msg[$i]['created_on'])) == date('m/d/Y')) {
			$msg_datetime = date('h:i a', strtotime($all_msg[$i]['created_on']));
		} else {
			$msg_datetime = my_date_changer($all_msg[$i]['created_on'],'t');
		}
	
		if (($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid']) && $all_msg[$i]['created_user_type_id'] == check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid'])) {
			$personName = "me";
		} else {
			$personName = getUserFullName($all_msg[$i]['created_by_user_id']);
		}

		if ($all_msg[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid'] && $all_msg[$i]['created_user_type_id'] == check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) || $all_msg[$i]['created_by_user_id'] == $msgOfThirdPerson) {
			$all_msg[$i]['message'] = '<div class="my_msg"> ' . nl2br($all_msg[$i]['message']) . '<br><span class="msg_signature">by ' . $personName . ' at ' . $msg_datetime . '</span></div>';
		} else {
			$all_msg[$i]['message'] = '<div class="other_msg"> ' . nl2br($all_msg[$i]['message']) . '<br><span class="msg_signature">by ' . $personName . ' at ' . $msg_datetime . '</span></div>';
		}
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}
//=====================DELETE GROUP==================
elseif ($_POST['action'] == 'delete_fees') {
	if (isset($_POST['feesid'])) {
		$rec = $db->query("delete from ss_fees where id='" . $_POST['feesid'] . "'");
		if ($rec > 0) {
			echo json_encode(array('code' => "1", 'msg' => 'Payment deleted successfully'));
			exit;
		} else {
			echo json_encode(array('code' => "0", 'msg' => 'Error: Payment deletion failed'));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Error: Process failed'));
		exit;
	}
}
//==========================SAVE MESSAGE=====================
elseif ($_POST['action'] == 'save_message') {

	$staff = $_POST['staff'];
	$group = $_POST['group'];
	$subject = $_POST['subject'];
	$students = $_POST['student'];
	// echo "<pre>";
	// print_r($students);
	// die;
	if(!empty($staff)){
		$user_type_code = 'UT02';
	}elseif(!empty($students)){
		$user_type_code = 'UT05';
	}elseif(!empty($_POST['user_type_code'])){
		$user_type_code = $_POST['user_type_code'];
	}

	$message = nl2br($_POST['message']);
	if (!check_userrole_by_code('UT05')){
	$message=substr_replace($message,"", -8);
	}
	$condition = trim($db->escape($_POST['message']));
	$condition = str_replace("<p><br></p>", '', $condition);
	$condition = str_replace("&nbsp;", '', $condition);
	$condition = strip_tags($condition);
	if(strlen($message)>35561){
	echo json_encode(array('code' => "0",'msg' => 'Content or Image is too large'));
	exit;
	}
	if (!empty(trim($condition))) {
		$db->query('BEGIN');

		$msg_set_no = time();
		if (count((array)$staff) > 0) {
			if(count((array)$staff) > 0 && isset($staff[0]) && $staff[0] == 'whole_group') {
					
					$staffs = $db->get_results("SELECT 2 AS row_order, u.id, s.first_name, s.middle_name, s.last_name,
					(SELECT CONCAT(' (',GROUP_CONCAT(ut.user_type),')') FROM `ss_usertypeusermap` utm INNER JOIN ss_usertype ut 
					ON utm.user_type_id = ut.id WHERE user_id = s.user_id) as user_type FROM ss_user u 
					INNER JOIN ss_staff s ON u.id = s.user_id 
					INNER JOIN ss_staff_session_map ssm ON u.id = ssm.staff_user_id 
					WHERE u.is_active = 1 AND u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
					UNION
					SELECT 1 AS row_order, id, 'Principal', '', '', '' FROM ss_user WHERE is_active = 1 AND is_deleted = 0 AND username <> 'admin31' 
					AND user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT01' AND user_type_group = 'admin') GROUP BY row_order
					ORDER BY row_order ASC, first_name asc");

				
					
					foreach ($staffs as $sta) 
					{ 

						$msg_set_no = $db->get_var("select msg_set_no from ss_message 
						where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and 
						(rec_user_id='" . $sta->id . "' and created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') 
						AND (rec_user_type_id='" . check_user_type($user_type_code, $sta->id) . "' and created_user_type_id = '" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "')
						AND (rec_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and created_by_user_id = '" . $sta->id . "') AND (rec_user_type_id='" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "' and created_user_type_id = '" . check_user_type($user_type_code, $sta->id). "')");
					
						if (trim($msg_set_no) == '') {
						$msg_set_no = time();
						}
						$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $reply = null, $_SESSION['icksumm_uat_login_usertypecode']);
						$receiverid = messagereceiver($sta->id);
			

						$ret_sql = $db->query("insert into ss_message set rec_user_id='" . $sta->id . "', rec_group_id= NULL, msg_set_no = '" . $msg_set_no . "',
						session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', message='" . $db->escape(trim($message)) . "', rec_user_type_id = '".$receiverid."', is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "',
						created_on='" . date('Y-m-d H:i:s') . "'");


					}
			}else{  
			  if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT05'){

                    $msg_set_no = $db->get_var("select msg_set_no from ss_message 
					where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and 
					(rec_user_id='" . $staff . "' and created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') 
					AND (rec_user_type_id='" . check_user_type($user_type_code, $staff) . "' and created_user_type_id = '" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "')
					AND (rec_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and created_by_user_id = '" . $staff . "') AND (rec_user_type_id='" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "' and created_user_type_id = '" . check_user_type($user_type_code, $staff). "')");

					if (trim($msg_set_no) == '') {
					$msg_set_no = time();
					}
					$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $reply = null, $_SESSION['icksumm_uat_login_usertypecode']);
					$receiverid = messagereceiver($staff);
					$ret_sql = $db->query("insert into ss_message set rec_user_id='" . $staff . "', rec_group_id= NULL, msg_set_no = '" . $msg_set_no . "', rec_user_type_id = '".$receiverid."',
					session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', message='" . $db->escape(trim($message)) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', 
					created_user_type_id = '" . $senderid . "', created_on='" . date('Y-m-d H:i:s') . "'");
			  }
			  else{
			  	foreach ($staff as $sta) 
				{
					$msg_set_no = $db->get_var("select msg_set_no from ss_message 
					where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and 
					(rec_user_id='" . $sta . "' and created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') 
					AND(rec_user_type_id='" . check_user_type($user_type_code, $sta) . "' and created_user_type_id = '" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "')
					AND (rec_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and created_by_user_id = '" . $sta . "') AND (rec_user_type_id='" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "' and created_user_type_id = '" . check_user_type($user_type_code, $sta). "')");

					if (trim($msg_set_no) == '') {
					$msg_set_no = time();
					}
					$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $reply = null, $_SESSION['icksumm_uat_login_usertypecode']);
					$receiverid = messagereceiver($sta);
					$ret_sql = $db->query("insert into ss_message set rec_user_id='" . $sta . "', rec_group_id= NULL, msg_set_no = '" . $msg_set_no . "', rec_user_type_id = '".$receiverid."',
					session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', message='" . $db->escape(trim($message)) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "',
					created_on='" . date('Y-m-d H:i:s') . "'");
				}
			  }
				
			}			
		} else {
			
			if(count((array)$students) > 0 && isset($students[0]) && $students[0] == 'whole_group') {

                $family_user_id = $db->get_results("SELECT DISTINCT f.user_id AS family_user_id FROM ss_student s INNER JOIN ss_family f ON f.id = s.family_id WHERE s.user_id IN ('".implode("','", $students)."')");
				foreach ($family_user_id as $parents) {
					if (is_numeric($parents->family_user_id)) {
						$msg_set_no = $db->get_var("select msg_set_no from ss_message where (rec_user_id='" . $parents->family_user_id . "' and created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') 
						or (rec_user_type_id='" . check_user_type($user_type_code, $parents->family_user_id) . "' and created_user_type_id = '" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "')
						and (rec_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and created_by_user_id = '" . $parents->family_user_id. "') AND (rec_user_type_id='" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "' and created_user_type_id = '" . check_user_type($user_type_code, $parents->family_user_id). "')");

						if (empty($msg_set_no)) {
							$msg_set_no = time();
						}
						$chek_user_type = $db->get_var("SELECT ut.id FROM ss_user u INNER JOIN ss_usertypeusermap m ON  u.id = m.user_id INNER JOIN ss_usertype ut ON m.user_type_id = ut.id WHERE ut.user_type_group = '".$_SESSION['icksumm_uat_login_usertypegroup']."' AND u.id = '".$_SESSION['icksumm_uat_login_userid']."'");
					
						if($_SESSION['icksumm_uat_login_usertypegroup'] == 'staff'){
							$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $chek_user_type);
						}else{
							$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $reply = null, $_SESSION['icksumm_uat_login_usertypecode']);
						}
						
						//$receiverid = messagereceiver($parents->family_user_id, $students);
						$receiverid = $db->get_var("SELECT id FROM ss_usertype WHERE user_type_code = 'UT05' and user_type_subgroup='parents' ");


						$ret_sql = $db->query("insert into ss_message set rec_user_id = '" . $parents->family_user_id . "', rec_user_type_id = '".$receiverid."', rec_group_id = '".$group."', rec_class_id = '".$subject."',
									msg_set_no = '" . $msg_set_no . "', is_all_parents = '1', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
									message='" . $db->escape($message) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "',
									created_on='" . date('Y-m-d H:i:s') . "'");
						//$ret_sql = $db->query("insert into ss_message set rec_user_id = NULL, rec_group_id = '" . $group . "', msg_set_no = '" . $group . "', message='" . $db->escape(trim($message)) . "', is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on='" . date('Y-m-d H:i:s') . "'");
					}
				}
			} else { 


				$new_arr = $db->get_results("SELECT DISTINCT f.user_id AS family_user_id FROM ss_student s INNER JOIN ss_family f ON f.id = s.family_id WHERE s.user_id IN ('".implode("','", $students)."')");
				
				foreach($new_arr as $family){
					if (is_numeric($family->family_user_id)) {

							$msg_set_no = $db->get_var("select msg_set_no from ss_message where (rec_user_id='" . $family->family_user_id . "' and created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') 
							or (rec_user_type_id='" . check_user_type($user_type_code, $family->family_user_id) . "' and created_user_type_id = '" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "')
							and (rec_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and created_by_user_id = '" . $family->family_user_id. "') AND (rec_user_type_id='" . check_user_type($_SESSION['icksumm_uat_login_usertypecode'], $_SESSION['icksumm_uat_login_userid']) . "' and created_user_type_id = '" . check_user_type($user_type_code, $family->family_user_id). "')");

							if (empty($msg_set_no)) {
								$msg_set_no = time();
							}

							$chek_user_type = $db->get_var("SELECT ut.id FROM ss_user u INNER JOIN ss_usertypeusermap m ON  u.id = m.user_id INNER JOIN ss_usertype ut ON m.user_type_id = ut.id WHERE ut.user_type_group = '".$_SESSION['icksumm_uat_login_usertypegroup']."' AND u.id = '".$_SESSION['icksumm_uat_login_userid']."'");
					
							if($_SESSION['icksumm_uat_login_usertypegroup'] == 'staff'){
								$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $chek_user_type);
							}else{
								$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $reply = null, $_SESSION['icksumm_uat_login_usertypecode']);
							}

							
							//$receiverid = messagereceiver($family->family_user_id, $students);
							$receiverid = $db->get_var("SELECT id FROM ss_usertype WHERE user_type_code = 'UT05' and user_type_subgroup='parents' ");

							$ret_sql = $db->query("insert into ss_message set rec_user_id = '" . $family->family_user_id . "', rec_user_type_id = '".$receiverid."', rec_group_id = '".$group."', rec_class_id = '".$subject."',
										msg_set_no = '" . $msg_set_no . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
										message='" . $db->escape(trim($message)) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "',
										created_on='" . date('Y-m-d H:i:s') . "'");
					}
				}

				// foreach($students as $student){
				// 	if(is_numeric($student)){

				// 	$family_user_id = $db->get_var("SELECT f.user_id as family_user_id  FROM ss_student s
				// 		INNER JOIN ss_family f ON f.id = s.family_id
				// 		WHERE s.user_id = '" . $student . "' ");

				// 	$msg_set_no = $db->get_var("select msg_set_no from ss_message where (rec_user_id='" . $family_user_id . "' and created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "') or (rec_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and created_by_user_id = '" . $family_user_id. "')");
				// 	if (trim($msg_set_no) == '') {
				// 		$msg_set_no = time();
				// 	}
				// 	$ret_sql = $db->query("insert into ss_message set rec_user_id = '" . $family_user_id . "', rec_group_id = NULL, 
				// 	msg_set_no = '" . $msg_set_no . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
				// 	message='" . $db->escape(trim($message)) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', 
				// 	created_on='" . date('Y-m-d H:i:s') . "'");
				// 	}
				// }
				
			}
		}
		if ($ret_sql && $db->query('COMMIT') !== false) {
			/* 	$emailbody = "Dear Assalamualaikum,<br><br>";
		$emailbody .= " ".$message."";
		$emailbody .= "<br><br>".SCHOOL_NAME." School Team";											
		send_my_mail('sujata.lodhi@quasardigital.com', ''.SCHOOL_NAME.' Internal Messsage ', $emailbody);		 */
			echo json_encode(array('msg' => 'Messsage sent successfully', 'code' => 1));
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('msg' => 'Messsage failed', 'code' => 0, '_errpos' => '1'));
			exit;
		}
	} else {
		echo json_encode(array('msg' => "Terms and Conditions cannot be empty", 'code' => 0));
		exit;
	}
}
//==========================REPLY MESSAGE=====================
elseif ($_POST['action'] == 'reply_message') {
	$msg_info = $db->get_row("select * from ss_message where md5(id) = '" . $_POST['mid'] . "'");
	$db->query('BEGIN');
	if(!empty($_POST['user_type_code'])){
		$user_type_code = $_POST['user_type_code'];
	}

	if(isset($msg_info->rec_group_id) && !empty($msg_info->rec_group_id)){
		$group = "rec_group_id= '".$msg_info->rec_group_id."'";
	}else{
		$group = "rec_group_id = NULL";
	}
	if(isset($msg_info->rec_class_id) && !empty($msg_info->rec_class_id)){
		$subject = "rec_class_id= '".$msg_info->rec_class_id."'";
	}else{
		$subject = "rec_class_id = NULL";
	}
	if(isset($msg_info->is_all_parents) && !empty($msg_info->is_all_parents)){
		$is_parent = '1';
	}else{
		$is_parent = '0';
	}

	// echo $msg_info->rec_user_id;
	// die;

	if (!empty($msg_info->rec_user_id)) {
			$message_set = $db->get_results("select * from ss_message where msg_set_no = '" . $msg_info->msg_set_no . "' and  created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' group by rec_user_id");

		if(count((array)$message_set) > 0){
			// echo "test principal reply";
			// die;
			foreach($message_set as $msg){
				// echo $_SESSION['icksumm_uat_login_userid'].'<br>';	
				$chek_user_type = $db->get_var("SELECT ut.id FROM ss_user u INNER JOIN ss_usertypeusermap m ON  u.id = m.user_id INNER JOIN ss_usertype ut ON m.user_type_id = ut.id WHERE ut.user_type_group = '".$msg->created_user_type_id."' AND u.id = '".$_SESSION['icksumm_uat_login_userid']."'");

				$chek_sender_user_type = $db->get_var("SELECT ut.id FROM ss_user u INNER JOIN ss_usertypeusermap m ON  u.id = m.user_id INNER JOIN ss_usertype ut ON m.user_type_id = ut.id WHERE ut.user_type_group = '".$_SESSION['icksumm_uat_login_usertypegroup']."' AND u.id = '".$_SESSION['icksumm_uat_login_userid']."'");
			
				if($_SESSION['icksumm_uat_login_userid'] == 1){
					$senderid = messagesender($_SESSION['icksumm_uat_login_userid']);
				}elseif($_SESSION['icksumm_uat_login_usertypegroup'] == 'staff'){
					 $senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $chek_sender_user_type, $_SESSION['icksumm_uat_login_usertypecode']);
				}else{
					 $senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $msg_info->rec_user_type_id, $_SESSION['icksumm_uat_login_usertypecode']);
				}
				
				if($_SESSION['icksumm_uat_login_usertypegroup'] == 'parents'){
					$receiverid = messagereceiver($msg->rec_user_id, $students = null, $chek_user_type);
				}else{
					if($_SESSION['icksumm_uat_login_userid'] == 1){
						$receiverid = messagereceiver($msg->rec_user_id, $students = null, $msg->rec_user_type_id);
					}else{
					 $receiverid = messagereceiver($msg->rec_user_id, $students = null, $msg_info->created_user_type_id);
					}
				} 
				
				$ret_sql = $db->query("insert into ss_message set rec_user_type_id = '".$receiverid."', rec_user_id='" . $msg->rec_user_id . "', ".$group.", ".$subject.", msg_set_no = '" . $msg_info->msg_set_no . "', is_all_parents='".$is_parent."', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', message='" . $db->escape(trim($_POST['message'])) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "', created_on='" . date('Y-m-d H:i:s') . "'");
			}
		}else{
			// echo "test teacher reply";
			// die;
			//MESSAGE FOR SPECIFIC USER - ONE TO ONE
			if ($msg_info->rec_user_id == $_SESSION['icksumm_uat_login_userid']) {
				$user_id = $msg_info->created_by_user_id;
			} else {
				$user_id = $msg_info->rec_user_id;
			}
			$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $msg_info->rec_user_type_id);
			$receiverid = messagereceiver($user_id, $students = null, $msg_info->created_user_type_id);
		
			$ret_sql = $db->query("insert into ss_message set rec_user_type_id = '".$receiverid."', rec_user_id='" . $user_id . "', ".$group.", ".$subject.", msg_set_no = '" . $msg_info->msg_set_no . "', is_all_parents='".$is_parent."',
			session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', 
			message='" . $db->escape(trim($_POST['message'])) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "',
			created_on='" . date('Y-m-d H:i:s') . "'");

		}

	} else {
		//MESSAGE FOR ALL USERS ATTACHED TO A GROUP
		if (check_userrole_by_code('UT03') || check_userrole_by_code('UT05')) {
			$groupMap = $db->get_row("select * from ss_staffgroupmap where active = 1 and group_id = '" . $msg_info->rec_group_id . "' order by id limit 1");
			$senderid = messagesender($_SESSION['icksumm_uat_login_userid'], $msg_info->rec_user_type_id);
			$receiverid = messagereceiver($groupMap->staff_user_id, $students = null, $msg_info->created_user_type_id);

			$ret_sql = $db->query("insert into ss_message set rec_user_type_id = '".$receiverid."', rec_user_id = '" . $groupMap->staff_user_id . "', ".$group.", ".$subject.", 
			msg_set_no = '" . $msg_info->msg_set_no . "', is_all_parents='".$is_parent."', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', 
			message='" . $db->escape(trim($_POST['message'])) . "',is_read='0', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "', created_user_type_id = '" . $senderid . "',
			created_on='" . date('Y-m-d H:i:s') . "'");
			
		}
	}


	if ($ret_sql && $db->query('COMMIT') !== false) {
		echo json_encode(array('msg' => 'Messsage sent successfully', 'code' => 1));
		exit;
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('msg' => 'Messsage not sent', 'code' => 0, '_errpos' => '1'));
		exit;
	}
}
//ADDED ON 24-OCT-2018
//==========================SAME MASS EMAIL TO QUEUE=====================
/* elseif ($_POST['action'] == 'save_mass_email_to_queue') {
	//ADDED ON 14-MAY-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit', '1024M');
	$db->query('BEGIN');
	$group = $_POST['group'];
	$student = $_POST['student'];
	$cc_emails = explode(',', $_POST['cc']);
	$bcc_emails = explode(',', $_POST['bcc']);
	$subject = $db->escape($_POST['subject']);
	$message = nl2br($db->escape($_POST['message']));
	$attachmentfiles = array();
	$attachmentfiles = $_POST['attachmentfile'];
	if ($group == 'all_groups' && $student == 'all_students') {
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
	} elseif (is_numeric($group) && $student == 'all_students') {
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '" . $group . "') order by s.first_name,s.last_name)");
	} elseif (is_numeric($student)) {
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '" . $student . "')");
	}
	$emailStatus = false;
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");
	if ($last_msg_time_diff > 4 || $last_msg_time_diff == "") {
		//REPLACE DYNAMIC KEYWORDS
		//$message = str_replace("{parent1_first_name}","",$message);
		$sql_bulk_msg = "insert into ss_bulk_message set subject = '" . $subject . "', message = '" . $message . "', is_report_gen = 0, 
		session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', 
		created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";
		if ($db->query($sql_bulk_msg)) {
			$message_id = $db->insert_id;
			foreach ($families as $fam) {
				if (trim($fam->primary_email) != '') {
					$to_primary = $fam->primary_email;
					//$to_primary = 'moh.urooj@gmail.com';
					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', receiver_email = '" . $to_primary . "', 
					is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
				if (trim($fam->secondary_email) != '') {
					$to_secondary = $fam->secondary_email;
					//$to_secondary = 'moh.urooj@gmail.com';

					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', receiver_email = '" . $to_secondary . "', 
					is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
			}
			foreach ($cc_emails as $cc) {
				if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {
					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($cc) . "', is_cc=1, 
					is_bcc=0, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
			}
			foreach ($bcc_emails as $bcc) {
				if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {
					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($bcc) . "', is_cc=0, 
					is_bcc=1, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
			}
			foreach ($attachmentfiles as $attach) {
				if ($db->query("insert into ss_bulk_message_attachment set bulk_message_id='" . $message_id . "', attachment_file='" . $attach . "'")) {
					$emailStatus = true;
				} else {
					$emailStatus = false;
				}
			}
			if ($emailStatus && $db->query('COMMIT') !== false) {
				echo json_encode(array('msg' => 'Email(s) queue created successfully', 'code' => 1));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('msg' => "Email(s) queue not created. Please try again.", 'code' => 0));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('msg' => "Email(s) queue not created. Please try again.", 'code' => 0));
			exit;
		}
	} else {
		echo json_encode(array('msg' => 'Email(s) queue created successfully', 'code' => 1));
		exit;
	}
} */

if ($_POST['action'] == 'save_mass_email_to_queue') {
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit', '1024M');

	$db->query('BEGIN');
	//var_dump($_FILES);

	$message_to = $_POST['message_to']; //Group /Registered Parents, Pending Parents, Registered Staff, Pending Staff
	$cc_emails = explode(',', $_POST['cc']);
	$bcc_emails = explode(',', $_POST['bcc']);
	$subject = $db->escape($_POST['subject']);
	$text_msg = $_POST['message'];
	if (empty(trim($text_msg))) {
		echo json_encode(array('msg' => "<p class='text-danger'>Message cannot be empty.</p>", 'code' => 0));
		exit;
	}
	//$attachmentfiles = array();
	//$attachmentfiles = $_POST['attachmentfile'];
	if ($message_to == 'registered_student') { //Group /Registered Parents 
		$group = $_POST['group'];
		$class = $_POST['class'];
		$student = $_POST['student'];

		if (is_numeric($group) && is_numeric($class) && is_numeric($student)) {
			$families = $db->get_results("select id,primary_email,secondary_email from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "' and class_id='" . $class . "' and student_user_id='" . $student . "') order by s.first_name,s.last_name)");
			$group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
			$subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
		} elseif ($group == 'all_groups' && $class == 'all_subjects' && $student == 'all_students') {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
			$group_information = "All Groups";
			$subjects_information = "All Subjects";
		} elseif ($group == 'all_groups' && $class == 'all_subjects' &&  is_numeric($student)) {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND student_user_id='" . $student . "') order by s.first_name,s.last_name)");
			$group_information = "All Groups";
			$subjects_information = "All Subjects";
		} elseif ($group == 'all_groups' && is_numeric($class) && $student == 'all_students') {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND class_id='" . $class . "') order by s.first_name,s.last_name)");
			$group_information = "All Groups";
			$subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
		} elseif ($group == 'all_groups' && is_numeric($class) && is_numeric($student)) {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND class_id='" . $class . "' and student_user_id='" . $student . "') order by s.first_name,s.last_name)");
			$group_information = "All Groups";
			$subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
		} elseif (is_numeric($group) && $class == 'all_subjects' && $student == 'all_students') {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "') order by s.first_name,s.last_name)");
			$group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
			$subjects_information = "All Subjects";
		} elseif (is_numeric($group) && $class == 'all_subjects' && is_numeric($student)) {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "' and student_user_id='" . $student . "') order by s.first_name,s.last_name)");
			$group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
			$subjects_information = "All Subjects";
		} elseif (is_numeric($group) && is_numeric($class) && $student == 'all_students') {
			$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "' and class_id='" . $class . "') order by s.first_name,s.last_name)");
			$group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
			$subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
		}
	} elseif ($message_to == 'pending_student') { //Pending Parents 


		$pending_student=array_values(array_filter($_POST['pending_student']));


		if(!is_numeric($pending_student[0])){
			$conditions = "";
		}else{
			$students_id = implode(',',$pending_student);
			$conditions = " and `reg_child`.`id` in (" . $students_id . ") ";
		}


		// $pending_student = $_POST['pending_student'];
		// if ($pending_student == 'all_pending_students') {
		// 	$conditions = "";
		// } else {
		// 	$conditions = " and `reg_child`.`id`='" . $pending_student . "' ";
		// }

		$families = $db->get_results("SELECT `school`.`primary_contact`,`school`.`primary_email`,`school`.`secondary_email` FROM `ss_sunday_sch_req_child` `reg_child` INNER JOIN `ss_sunday_school_reg` `school` ON `reg_child`.`sunday_school_reg_id`=`school`.`id` WHERE `reg_child`.`is_executed` = 0 and `school`.`session` = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' " . $conditions . " group by `school`.`id`");


	} elseif ($message_to == 'registered_staff') { //Registered Staff 
		$registered_staff = $_POST['registered_staff'];
		if ($registered_staff == 'all_registered_staff') {
			$conditions = "";
		} else {
			$conditions = " and `s`.`user_id`='" . $registered_staff . "' ";
		}

	
		$families = $db->get_results("SELECT s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name,u.email as primary_email FROM ss_user u INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_staff_session_map ssm on u.id = ssm.staff_user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND status = 1 " . $conditions . " GROUP BY u.email ");

		$princi = $db->get_row("SELECT ss_user.* FROM ss_user WHERE is_active = 1 AND is_deleted = 0 AND username <> 'admin31' 
		AND user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT01' AND user_type_group = 'principal' AND is_default = 1)");

		if(!empty($princi) && $princi->id == $registered_staff){
			 $staff_princi =  [
				(object)array('user_id'=>$princi->id,'staff_name'=>'Principal','primary_email'=>$princi->email)
			];
            $families = array_merge((array)$families,(array)$staff_princi);
		}


	} elseif ($message_to == 'pending_staff') { //Pending Staff
		$pending_staff = $_POST['pending_staff'];
		if ($pending_staff == 'all_pending_staff') {
			$conditions = "";
		} else {
			$conditions = " and `r`.`id`='" . $pending_staff . "' ";
		}

		$families = $db->get_results("SELECT distinct r.id, CONCAT(r.first_name,' ',COALESCE(r.middle_name,''),' ',COALESCE(r.last_name,'')) AS staff_name,r.email as primary_email FROM ss_staff_registration r LEFT JOIN ss_user u ON r.email = u.email WHERE r.is_request = 0 AND r.is_processed = 0 AND r.session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' " . $conditions . " ");
	}


	$emailStatus = false;
	if ($message_to == 'registered_student') {
		$group_information = "Group  <strong>" . $group_information . "</strong>";
		$subjects_information = "Class <strong>" . $subjects_information . "</strong>";

		$message = "<br>" . $group_information . " <br>" . $subjects_information . " <br><br>" . $text_msg;
	} else {

		$message =  $text_msg;
	}

	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");

	if ($last_msg_time_diff > 4 || $last_msg_time_diff == "") {
		$sql_bulk_msg = "insert into ss_bulk_message set scheduled_time = '" . date('Y-m-d H:i:s') . "',subject = '" . $db->escape($subject) . "', message = '" . $db->escape($message) . "', is_report_gen = 0, created_on = '" . date('Y-m-d H:i:s') . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";
		if ($db->query($sql_bulk_msg)) {
			$message_id = $db->insert_id;
			$family = '';

			foreach ($families as $fam) {

				if ($message_to == 'registered_student') {
					$family = "family_id ='" . $fam->id . "',";
				}

				if (!empty($fam->primary_email)) {
					$to_primary = $fam->primary_email;
					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', " . $family . " receiver_email = '" . $to_primary . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}

				if (!empty($fam->secondary_email)) {
					$to_secondary = $fam->secondary_email;

					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "',  " . $family . " receiver_email = '" . $to_secondary . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
			}

			foreach ($cc_emails as $cc) {
				if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {

					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($cc) . "', is_cc=1, is_bcc=0, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
			}

			foreach ($bcc_emails as $bcc) {
				//$bcc = 'moh.urooj@gmail.com';
				if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {
					if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($bcc) . "', is_cc=0, is_bcc=1, delivery_status = 2, attempt_counter = 0")) {
						$emailStatus = true;
					}
				}
			}

			//---------------------------Attachment-----------------------------//
			$file_ary = reArrayFiles($_FILES['attachmentfile']);

			foreach ($file_ary as $file) {
				$fileName = $file['name'];
				$fileNameCmps = explode(".", $fileName);
				$fileExtension = strtolower(end($fileNameCmps));

				$filenameWOExt = pathinfo($fileName, PATHINFO_FILENAME);
				$filenameWOExt = str_replace(' ', '-', $filenameWOExt);
				$newFileName = $filenameWOExt . "-" . $message_id . "." . $fileExtension;

				$uploadFileDir = '../message/attachments/';
				$dest_path = $uploadFileDir . $newFileName;

				if (move_uploaded_file($file['tmp_name'], $dest_path)) {
					if ($db->query("insert into ss_bulk_message_attachment set bulk_message_id='" . $message_id . "', attachment_file='" . $newFileName . "'")) {
						$emailStatus = true;
					} else {
						$emailStatus = false;
					}
				}
			}
			//---------------------------Attachment-----------------------------//

			if ($emailStatus && $db->query('COMMIT') !== false) {
				echo json_encode(array('msg' => '<p class="text-success">Email(s) queue created successfully </p>', 'code' => 1));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('msg' => "<p class='text-danger'>Email(s) queue not created. Please try again.</p>.", 'code' => 0));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('msg' => "<p class='text-danger'>Email(s) queue not created. Please try again.</p>.", 'code' => 0));
			exit;
		}
	} else {
		echo json_encode(array('msg' => "<p class='text-danger'>Email(s) queue not created. Please try again.</p>.", 'code' => 0));
		exit;
	}
}

//==========================SAME MASS TEXT SMS TO QUEUE=====================
elseif ($_POST['action'] == 'save_mass_text_msg_to_queue') {
	//ADDED ON 02-OCT-2018
	$db->query('BEGIN');
	if (is_array($_POST['teacher'])) {
		$teacher_ary = array();
		$teacher_ary = $_POST['teacher'];
		$teacher = implode(',', $teacher_ary);
	} else {
		$teacher = $_POST['teacher'];
	}
	$group = $_POST['group'];
	$student = $_POST['student'];

	$message = $db->escape($_POST['message']);
	if ($group == 'all_groups' && count((array)$student) > 0 && isset($student[0]) && $student[0] == 'all_students') {
		//ADDED ON 22-JUN-2021
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id 
		IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 ) order by s.first_name,s.last_name)");
	} elseif (is_numeric($group) && count((array)$student) > 0 && isset($student[0]) && $student[0] == 'all_students') {
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id 
		IN (SELECT student_user_id FROM ss_studentgroupmap 
		WHERE latest = 1 AND group_id = '" . $group . "') order by s.first_name,s.last_name)");
	} elseif (count((array)$student) > 0 && isset($student[0]) && $student[0] != 'all_students') {
		$new_stu = implode(', ', $student);
		$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
		ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE user_id IN (" . $new_stu . ") and u.is_active = 1 AND u.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "')");
	} else {
		$staffs = $db->get_results("select * from ss_staff where user_id in (" . $teacher . ")");
	}
	$smsStatus = false;
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_sms 
	where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");
	if ($last_msg_time_diff > 4 || $last_msg_time_diff == '') {
		foreach ($student as $stu) {
            $sql_bulk_msg = "insert into ss_bulk_sms set message = '" . $message . "', para_teacher = '" . $teacher . "', para_group = '" . $group . "', 
			para_parents_of = '" . $stu . "', session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";
        
			
            if ($db->query($sql_bulk_msg)) {
                $message_id = $db->insert_id;
                foreach ($families as $fam) {
                    $father_phone = str_replace(' ', '', str_replace(')', '', str_replace('(', '', (str_replace('.', '', (str_replace('-', '', $fam->father_phone)))))));
                    $father_area_code = str_replace(' ', '', str_replace(')', '', str_replace('(', '', (str_replace('.', '', (str_replace('-', '', $fam->father_area_code)))))));
                    if (strlen($father_phone) == 10) {
                        $father_mobile_no = $father_phone;
                    } elseif (strlen($father_area_code . $father_phone) == 10) {
                        $father_mobile_no = $father_area_code . $father_phone;
                    }
                    if (strlen($father_mobile_no) == 10) {
                        if (is_numeric($fam->user_id)) {
                            $receiver_user_id = $fam->user_id;
                        } else {
                            $receiver_user_id = 'NULL';
                        }
                        if ($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $receiver_user_id . ", 
					receiver_mobile_no = '" . $father_mobile_no . "', delivery_status = 2, attempt_counter = 0")) {
                            $smsStatus = true;
                        }
                    }
                    $mother_phone = str_replace(' ', '', str_replace(')', '', str_replace('(', '', (str_replace('.', '', (str_replace('-', '', $fam->mother_phone)))))));
                    $mother_area_code = str_replace(' ', '', str_replace(')', '', str_replace('(', '', (str_replace('.', '', (str_replace('-', '', $fam->mother_area_code)))))));
                    if (strlen($mother_phone) == 10) {
                        $mother_mobile_no = $mother_phone;
                    } elseif (strlen($mother_area_code . $mother_phone) == 10) {
                        $mother_mobile_no = $mother_area_code . $mother_phone;
                    }
                    if (strlen($mother_mobile_no) == 10) {
                        if (is_numeric($fam->user_id)) {
                            $receiver_user_id = $fam->user_id;
                        } else {
                            $receiver_user_id = 'NULL';
                        }
                        if ($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = " . $receiver_user_id . ",
					receiver_mobile_no = '" . $mother_mobile_no . "', delivery_status = 2, attempt_counter = 0")) {
                            $smsStatus = true;
                        }
                    }
                }

                foreach ($staffs as $sta) {
                    $mobile = str_replace(' ', '', str_replace(')', '', str_replace('(', '', (str_replace('.', '', (str_replace('-', '', $sta->mobile)))))));
                    $phone = str_replace(' ', '', str_replace(')', '', str_replace('(', '', (str_replace('.', '', (str_replace('-', '', $sta->phone)))))));
                    if (strlen($mobile) == 10) {
                        $contact_no = $mobile;
                    } elseif (strlen($phone) == 10) {
                        $contact_no = $phone;
                    }
                    if (strlen($contact_no) == 10) {
                        if ($db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', receiver_user_id = '" . $sta->user_id . "', 
					receiver_mobile_no = '" . $contact_no . "', delivery_status = 2, attempt_counter = 0")) {
                            $smsStatus = true;
                        }
                    }
                }
            } else {
                $db->query('ROLLBACK');
                echo json_encode(array('msg' => "Message(s) queue not created. Please try again.", 'code' => 0, '_errpos' => 2));
                exit;
            }
			if ($smsStatus && $db->query('COMMIT') !== false) {
				echo json_encode(array('msg' => 'Message(s) queue created successfully', 'code' => 1));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('msg' => "Message(s) queue not created. Please try again.", 'code' => 0, '_errpos' => 1));
				exit;
			}
        } 
	} else {
		echo json_encode(array('msg' => 'Message(s) queue created successfully', 'code' => 1));
		exit;
	}
}
//==========================VIEW MAIL DETAILS=====================
elseif ($_POST['action'] == 'view_mail_detail') {
	$msgid = $_POST['msgid'];
	$msg = $db->get_row("SELECT * FROM ss_bulk_message WHERE id = '" . $msgid . "'");
	$retStr = '<div class="row">
					<div class="col-md-12">
						<label>Subject:</label>' . $msg->subject . '
					</div>
			    </div>
				<div class="row">
					<div class="col-md-12">
						<label>Message:</label>' . nl2br($msg->message) . '
					</div>			 
				</div>';
	$attach_counter = 0;
	$attachments = $db->get_results("select * from ss_bulk_message_attachment where bulk_message_id = '" . $msgid . "'");
	foreach ($attachments as $attach) {
		$attach_counter++;
		if (!empty($attach->attachment_file)) {
			if (trim($msg->request_from) == 'message_module') {
				$attachmentFileURL .= "(" . $attach_counter . ") <a href='" . SITEURL . 'message/attachments/' . $attach->attachment_file . "' target='_blank'>" . $attach->attachment_file . "</a>";
			} else {
				$attachmentFileURL .= " (" . $attach_counter . ") <a href='" . SITEURL . 'homework/attachments/' . $attach->attachment_file . "' target='_blank'>" . $attach->attachment_file . "</a>";
			}
		} else {

			$attachmentFileURL = '';
		}
	}
	if (count((array)$attachments)) {
		$retStr .= '
			<div class="row">
			  <div class="col-md-12">
                  <label>Attachments:</label>' . $attachmentFileURL . '
              </div>			 
			</div>';
	}
	echo $retStr;
	exit;
}
//==========================VIEW SMS DETAILS=====================
elseif ($_POST['action'] == 'view_sms_detail') {
	$msgid = $_POST['msgid'];
	$to = $_POST['to'];
	$msgtype = strtolower($_POST['msgtype']);
	//if(is_numeric($to)){
	if ($msgtype == 'reply') {
		$msgs = $db->get_results("SELECT received_raw_data AS message, 'reply' AS src, created_on FROM ss_bulk_sms_reply 
		WHERE sender_mobile_no = '" . $to . "' or sender_mobile_no = '" . substr($to, 1) . "' 
		UNION SELECT s.message, 'new' AS src, s.created_on FROM ss_bulk_sms s 
		INNER JOIN ss_bulk_sms_mobile m ON s.id = m.bulk_sms_id WHERE m.receiver_mobile_no = '" . $to . "' 
		or m.receiver_mobile_no = '" . substr($to, 1) . "' ORDER BY created_on DESC");
		foreach ($msgs as $ms) {
			if (date('m/d/Y', strtotime($ms->created_on)) == date('m/d/Y')) {
				$msg_datetime = my_date_changer($ms->created_on,'t');
			} else {
				$msg_datetime = my_date_changer($ms->created_on,'t');
			}
			if ($ms->src == 'reply') {
				$msg_dec = json_decode($ms->message);
				$message_text .= '<div class="text-left" style="width:100%; margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> ' . $msg_dec->text . '<br><span class="msg_signature">by Customer at ' . $msg_datetime . '</span></div>';
			} else {
				$message_text .= '<div class="text-right" style="width:100%;  margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> ' . $ms->message . '<br><span class="msg_signature">by ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' at ' . $msg_datetime . '</span></div>';
			}
		}
	} else {
		$msg = $db->get_row("SELECT * FROM ss_bulk_sms WHERE md5(id) = '" . $msgid . "'");

		if (date('m/d/Y', strtotime($msg->created_on)) == date('m/d/Y')) {
			$msg_datetime = my_date_changer($msg->created_on,'t');
		} else {
			$msg_datetime = my_date_changer($msg->created_on,'t');
		}
		$message_text .= '<div class="text-right" style="width:100%;">' . nl2br($msg->message) . '<br><span class="msg_signature">by ' . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' at ' . $msg_datetime . '</span></div>';
		//$message_text .= '<div class="my_msg"> '.$msg->message.'<br><span class="msg_signature"> at '.$msg_datetime.'</span></div>';	
		//$message_text = nl2br($msg->message);
	}
	$retStr = '<div class="row"><div class="col-md-12">' . $message_text . '</div></div>';
	echo $retStr;
	exit;
}

//==========================SEND REPLY TO SMS=====================
elseif ($_POST['action'] == 'send_reply_msg') {
	$mobileno = $_POST['rec_mobile_no'];
	$message = $_POST['message'];
	//STOP REPEATED CLICK ENTRY
	$last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_sms 
	where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");
	if ($last_msg_time_diff > 4 || $last_msg_time_diff == '') {
		$sql_bulk_msg = "insert into ss_bulk_sms set message = '" . $message . "', created_on = '" . date('Y-m-d H:i:s') . "', 
		session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', 
		is_reply = 1, request_from = 'reply_model', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";
		if ($db->query($sql_bulk_msg)) {
			$message_id = $db->insert_id;
			$smsStatus = $db->query("insert into ss_bulk_sms_mobile set bulk_sms_id = '" . $message_id . "', 
			receiver_mobile_no = '" . $mobileno . "', delivery_status = 2, attempt_counter = 0");
			if ($smsStatus && $db->query('COMMIT') !== false) {
				echo json_encode(array('msg' => 'Message saved successfully', 'code' => 1));
				exit;
			} else {
				$db->query('ROLLBACK');
				echo json_encode(array('msg' => "Message not saved. Please try again.", 'code' => 0, '_errpos' => 1));
				exit;
			}
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('msg' => "Message not saved. Please try again.", 'code' => 0, '_errpos' => 2));
			exit;
		}
	} else {
		echo json_encode(array('msg' => 'Message(s) queue created successfully', 'code' => 1));
		exit;
	}
}
//==========================VIEW SMS REPLY=====================
elseif ($_POST['action'] == 'view_sms_reply') {
	$msgid = $_POST['msgid'];
	$mobileno = $_POST['mobileno'];
	$msgs = $db->get_results("SELECT received_raw_data AS message, 'reply' AS src, created_on FROM ss_bulk_sms_reply 
	WHERE sender_mobile_no = '" . $mobileno . "' or sender_mobile_no = '" . substr($mobileno, 1) . "' UNION 
	SELECT s.message, 'new' AS src, s.created_on FROM ss_bulk_sms s 
	INNER JOIN ss_bulk_sms_mobile m ON s.id = m.bulk_sms_id WHERE m.receiver_mobile_no = '" . $mobileno . "' 
	or m.receiver_mobile_no = '" . substr($mobileno, 1) . "' ORDER BY created_on DESC");
	foreach ($msgs as $ms) {
		if (date('m/d/Y', strtotime($ms->created_on)) == date('m/d/Y')) {
			$msg_datetime = date('h:i a', strtotime($ms->created_on));
		} else {
			$msg_datetime = date('m/d/Y h:i a', strtotime($ms->created_on));
		}
		if ($ms->src == 'reply') {
			$msg_dec = json_decode($ms->message);
			$message_text .= '<div class="text-left" style="width:100%; margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> ' . $msg_dec->text . '<br><span class="msg_signature">by Customer at ' . $msg_datetime . '</span></div>';
		} else {
			$message_text .= '<div class="text-right" style="width:100%;  margin-bottom:5px; border-bottom: 1px solid #e0dfdf;"> ' . $ms->message . '<br><span class="msg_signature">by school at ' . $msg_datetime . '</span></div>';
		}
	}
	$retStr = '<div class="row"><div class="col-md-12">' . $message_text . '</div></div>';
	echo $retStr;
	exit;
}
//==========================RESEND MASS EMAIL=====================
elseif ($_POST['action'] == 'resend_mass_emails') {
	$message_id = $db->get_var("select id from ss_bulk_message where md5(id) = '" . $_POST['msgid'] . "'");
	$status = $db->query("update ss_bulk_message_emails set delivery_status = 2, attempt_counter = 0 where bulk_message_id = '" . $message_id . "' and delivery_status = 0");
	if ($status) {
		echo json_encode(array('msg' => 'Email(s) queued for resending', 'code' => 1));
		exit;
	} else {
		echo json_encode(array('msg' => "Email(s) resend process failed. Please try again.", 'code' => 0));
		exit;
	}
}
//==========================RESEND MASS SMS=====================
elseif ($_POST['action'] == 'resend_mass_sms') {
	$message_id = $db->get_var("select id from ss_bulk_sms where md5(id) = '" . $_POST['msgid'] . "'");
	$status = $db->query("update ss_bulk_sms_mobile set delivery_status = 2, attempt_counter = 0 where bulk_sms_id = '" . $message_id . "' 
	and delivery_status = 0");
	if ($status) {
		echo json_encode(array('msg' => 'Message(s) queued for resending', 'code' => 1));
		exit;
	} else {
		echo json_encode(array('msg' => "Message(s) resend process failed. Please try again.", 'code' => 0));
		exit;
	}
}
//==========================MASS EMAIL HISTORY=====================
elseif ($_GET['action'] == 'mass_email_history') {
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	//if (check_userrole_by_code('UT01') || check_userrole_by_code('UT02')) {

		$dbVisibleColumns = array('id','msgid', 'checkbox', 'created_on', 'subject', 'sent', 'in_queue', 'failed');

		$draw = $_GET['draw'];
		$start = $_GET['start'];
		$length = $_GET['length'];
	
		$GetSearchAry = $_GET['search'];
		$searchKey = trim($GetSearchAry['value']);
	
		$GetOrderAry = $_GET['order'];
		$orderColumn = $dbVisibleColumns[$GetOrderAry[0]['column']];
		$orderDir = $GetOrderAry[0]['dir'];

	
		$dataAry = array();


		if (check_userrole_by_code('UT01')) {
		/* 	$recordsFiltered = "SELECT *, id as msgid FROM ss_bulk_message where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ";
		    
			if($searchKey != '') {
				$recordsFiltered .= " AND  `subject` LIKE '%$searchKey%' ";
			}
		
			$recordsFiltered .= " order by $orderColumn $orderDir ";
			$recordsTotal = count((array)$db->get_results($recordsFiltered, ARRAY_A));
			if ($length == -1) {
				$all_msg = $db->get_results($recordsFiltered, ARRAY_A);
			} else {
				$recordsFiltered .= "LIMIT $length OFFSET $start ";
				$all_msg = $db->get_results($recordsFiltered, ARRAY_A);
			} */
			$recordsFiltered = "SELECT ss_bulk_message.*, ss_bulk_message.id as msgid,COUNT(IF(delivery_status='2',1,null)) AS in_queue,
			COUNT(IF(delivery_status='1',1,null)) AS sent,
			COUNT(IF(delivery_status='0',1,null)) AS failed
			FROM ss_bulk_message
			LEFT join ss_bulk_message_emails ON ss_bulk_message_emails.bulk_message_id = ss_bulk_message.id
			where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ";

		} else { 
			$recordsFiltered = "SELECT ss_bulk_message.*, ss_bulk_message.id as msgid,COUNT(IF(delivery_status='2',1,null)) AS in_queue,
			COUNT(IF(delivery_status='1',1,null)) AS sent,
			COUNT(IF(delivery_status='0',1,null)) AS failed
			FROM ss_bulk_message
			LEFT join ss_bulk_message_emails ON ss_bulk_message_emails.bulk_message_id = ss_bulk_message.id
			where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ";
			
		}
		if($searchKey != '') {
			$recordsFiltered .= " AND  `subject` LIKE '%$searchKey%' ";
		}
	
		$recordsFiltered .= " GROUP BY ss_bulk_message.id order by $orderColumn $orderDir ";

		$recordsTotal = count((array)$db->get_results($recordsFiltered, ARRAY_A));

		if ($length == -1) {
			$all_msg = $db->get_results($recordsFiltered, ARRAY_A);
		} else {
			$recordsFiltered .= "LIMIT $length OFFSET $start ";
			$all_msg = $db->get_results($recordsFiltered, ARRAY_A);
		}

		for ($i = 0; $i < count((array)$all_msg); $i++) {
			//$queue = $db->get_var("select COUNT(1) from ss_bulk_message_emails where bulk_message_id = '" . $all_msg[$i]['id'] . "' and delivery_status = 2");
			// echo "select bm.id from ss_bulk_message_emails bme inner join ss_bulk_message bm on bm.id = bme.bulk_message_id where bme.bulk_message_id = '".$all_msg[$i]['id']."' and bme.delivery_status = 2 and (bm.scheduled_time is NULL or bm.scheduled_time <= '".date('Y-m-d')."') and bm.created_on < '".date('Y-m-d')."'";
			// die;
			$queue_current_date = $db->get_var("select bm.id, bm.scheduled_time from ss_bulk_message_emails bme inner join ss_bulk_message bm on bm.id = bme.bulk_message_id where bme.bulk_message_id = '" . $all_msg[$i]['id'] . "' and bme.delivery_status = 2 and (bm.scheduled_time is NULL or DATE(bm.scheduled_time) <= '" . date('Y-m-d') . "') and DATE(bm.created_on) <= '" . date('Y-m-d') . "'");
			$queueiniciate = $db->get_row("select bm.scheduled_time from ss_bulk_message_emails bme inner join ss_bulk_message bm on bm.id = bme.bulk_message_id where bme.bulk_message_id = '" . $all_msg[$i]['id'] . "' and bme.delivery_status = 2");

			//$all_msg[$i]['sent'] = $db->get_var("select count(1) from ss_bulk_message_emails where bulk_message_id = '" . $all_msg[$i]['id'] . "' and delivery_status = 1");
			$all_msg[$i]['sent'] = $all_msg[$i]['sent'];
			if (check_userrole_by_code('UT01')) {
				if (!empty($queue_current_date)) {
					$all_msg[$i]['checkbox'] = 	'<center><input type="checkbox" name="" data-msgid="' . $all_msg[$i]['id'] . '" class="selectedrow"></center>';
					$all_msg[$i]['delete'] = '<center><input type="checkbox" name="" data-msgid="' . $all_msg[$i]['id'] . '" class="selectedrow"></center>';
				} else {
					$all_msg[$i]['checkbox'] = '<center><input type="checkbox" name="" data-msgid="' . $all_msg[$i]['id'] . '" class="selectedrowunchecked" disabled></center>';
				}
			} else {
				$all_msg[$i]['checkbox'] = ' ';
				$icon = ' ';
			}
			$all_msg[$i]['in_queue'] = $all_msg[$i]['in_queue'];
			$all_msg[$i]['failed'] = $all_msg[$i]['failed'];
			//$all_msg[$i]['failed'] = $db->get_var("select count(1) from ss_bulk_message_emails where bulk_message_id = '" . $all_msg[$i]['id'] . "' and delivery_status = 0");

			if (!empty($all_msg[$i]['created_on'])) {
				if (!empty($queueiniciate->scheduled_time)) {
					$icon = '<a herf="javascript:void(0);" title="Email initiated queue" style"background-color:black;"><i class="icon-file-upload2" aria-hidden="true" style="float:right;"></i></a>';
				} else {
					$icon = ' ';
				}
				$all_msg[$i]['created_on'] = my_date_changer($all_msg[$i]['created_on'],'t') . $icon;
			} else {
				$all_msg[$i]['created_on'] = " ";
			}
		}
	//}
	echo json_encode(array(
        'draw' => $draw++,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $all_msg
    ));
	// $finalAry['data'] = $all_msg;
	// echo json_encode($finalAry);
	exit;
}
//==========================MASS SMS HISTORY=====================
elseif ($_GET['action'] == 'mass_sms_history') {
	//if (check_userrole_by_code('UT01')) {
		$all_msg = $db->get_results("SELECT *, md5(id) as msgid FROM ss_bulk_sms WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY id DESC", ARRAY_A);
		for ($i = 0; $i < count((array)$all_msg); $i++) {
			$message = $all_msg[$i]['msg_type'] = $all_msg[$i]['is_reply'] == 0 ? 'New' : 'Reply';
			$message = $all_msg[$i]['message'];
			$all_msg[$i]['message'] = strlen($message) > 50 ? (substr($message, 0, 50) . '...') : $message;
			$para_parents_of = $all_msg[$i]['para_parents_of'];
			$para_group = $all_msg[$i]['para_group'];
			$para_teacher = $all_msg[$i]['para_teacher'];
			if (is_numeric($para_parents_of)) {
				$student_name = $db->get_var("select concat(first_name,' ',last_name) from ss_student 
				where user_id = '" . $para_parents_of . "'");
				$all_msg[$i]['to'] = $student_name;
			} elseif ($para_parents_of == 'all_students') {
				if (is_numeric($para_group)) {
					$group_name = $db->get_var("select group_name from ss_groups where id = '" . $para_group . "'");
					$all_msg[$i]['to'] = 'All students of group ' . $group_name;
				} else {
					$all_msg[$i]['to'] = 'All students of all groups';
				}
			} elseif (trim($para_teacher) != '') {
				$teachers = $db->get_results("select * from ss_staff where user_id in (" . $para_teacher . ")");
				foreach ($teachers as $tea) {
					if (trim($all_msg[$i]['to']) == '') {
						$all_msg[$i]['to'] = $tea->first_name . ' ' . $tea->last_name;
					} else {
						$all_msg[$i]['to'] = $all_msg[$i]['to'] . ', ' . $tea->first_name . ' ' . $tea->last_name;
					}
				}
			} else {
				$all_msg[$i]['to'] = $db->get_var("select receiver_mobile_no from ss_bulk_sms_mobile 
				where bulk_sms_id = '" . $all_msg[$i]['id'] . "'");
			}
			$all_msg[$i]['sent'] = $db->get_var("select COUNT(1) from ss_bulk_sms_mobile where bulk_sms_id = '" . $all_msg[$i]['id'] . "' and delivery_status = 1");
			$all_msg[$i]['in_queue'] = $db->get_var("select COUNT(1) from ss_bulk_sms_mobile where bulk_sms_id = '" . $all_msg[$i]['id'] . "' and delivery_status = 2");
			$all_msg[$i]['failed'] = $db->get_var("select COUNT(1) from ss_bulk_sms_mobile where bulk_sms_id = '" . $all_msg[$i]['id'] . "' and delivery_status = 0");
			$all_msg[$i]['created_on'] = my_date_changer($all_msg[$i]['created_on'],'t');
		}
	//}
	$finalAry['data'] = $all_msg; 
	echo json_encode($finalAry);
	exit;
}
//==========================MASS SMS REPLY=====================
elseif ($_GET['action'] == 'mass_sms_reply') {
	if (check_userrole_by_code('UT01')) {
		$all_msg = $db->get_results("SELECT *, md5(id) as msgid FROM ss_bulk_sms_reply", ARRAY_A);
		for ($i = 0; $i < count((array)$all_msg); $i++) {
			$db->query("update ss_bulk_sms_reply set is_read = 1 where id = '" . $all_msg[$i]['id'] . "'");
			$raw_data = json_decode($all_msg[$i]['received_raw_data'], true);
			$message = $raw_data['text'];

			$all_msg[$i]['message'] = strlen($message) > 75 ? (substr($message, 0, 75) . '...') : $message;
			$all_msg[$i]['created_on'] = date('M d Y, h:i:s a', strtotime($all_msg[$i]['created_on']));
		}
	}
	$finalAry['data'] = $all_msg;
	echo json_encode($finalAry);
	exit;
}
//==========================SAVE GROUP EMAIL TO QUEUE=====================
elseif ($_POST['action'] == 'send_email_to_group') {
	//ADDED ON 14-MAY-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit', '1024M');
	$db->query('BEGIN');
	$group = $_POST['groupid'];
	$subject = $db->escape($_POST['subject']);
	$message = nl2br($db->escape($_POST['message']));
	$cc_emails = explode(',', $_POST['cc']);
	$families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
	ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
	WHERE latest = 1 AND group_id = '" . $group . "') order by s.first_name,s.last_name)");
	$db->query("insert into ss_bulk_message set subject = '" . $subject . "', message = '" . $message . "', request_from = 'teacher_time_table', is_report_gen = 0,
	session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
	$message_id = $db->insert_id;
	$emailStatus = false;
	foreach ($families as $fam) {
		if (trim($fam->primary_email) != '') {
			$db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', receiver_email = '" . $fam->primary_email . "', 
			is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0");
			$emailStatus = true;
		}
		if (trim($fam->secondary_email) != '') {
			$db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', receiver_email = '" . $fam->secondary_email . "', 
			is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0");
			$emailStatus = true;
		}
	}
	foreach ($cc_emails as $cc) {
		if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {
			if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($cc) . "', is_cc=1, 
			is_bcc=0, delivery_status = 2, attempt_counter = 0")) {
				$emailStatus = true;
			}
		}
	}
	if ($emailStatus && $db->query('COMMIT') !== false) {
		echo json_encode(array('msg' => 'Email(s) queue created successfully', 'code' => 1));
		exit;
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('msg' => "Email(s) queue not created. Please try again.", 'code' => 0));
		exit;
	}
}
//==========================GET Email Template=====================
elseif ($_POST['action'] == 'get_email_template_data') {
	$id = $_POST['id'];
	if (!empty($id)) {
		$results = $db->get_row("SELECT etype.id AS email_template_type_id, etype.type_name, etemp.id, etemp.email_template, etemp.email_subject, etemp.email_cc, etemp.email_bcc FROM ss_email_templates etemp INNER JOIN ss_email_template_types etype ON etype.id = etemp.email_template_type_id WHERE etemp.status = 1 and etype.id ='" . $id . "'");
	} else {
		$results = '';
	}
	echo json_encode(array('code' => 1, 'inputVal' => $results));
	exit;
}
//==========================SEND EMAIL CRON=====================
elseif ($_POST['action'] == 'sendemail') {
	$db->query('BEGIN');
	//$bulk_message = $db->get_results("select * from ss_bulk_message where DATE(created_on) = '".date('Y-m-d')."' and scheduled_time is NULL");
	//if(count((array)$bulk_message) > 0){
	$msg = 0;
	foreach ($_POST['msg_ids'] as $row) {
		$requests = $db->query("update ss_bulk_message set scheduled_time = '" . date('Y-m-d H:i:s') . "' where id = '" . $row . "'");
		$msg++;
	}

	if ($msg > 0  && $db->query('COMMIT') !== false) {
		echo json_encode(array('msg' => 'Email Queue Initiated Successfully', 'code' => 1));
		exit;
	} else {
		$db->query('ROLLBACK');
		echo json_encode(array('msg' => "Process failed. Please try again.", 'code' => 0));
		exit;
	}
	// }else{
	// 	$db->query('ROLLBACK');
	// 	echo json_encode(array('msg'=>"No data found today.",'code'=>0));
	// 	exit;
	// }

}
//=====================DELETE MASS EMAIL==================
elseif ($_POST['action'] == 'delete_mass_email') {
	if (isset($_POST['msgid'])) {
		$result = $db->get_row("SELECT bme.id AS bulk_msg_email_id, bma.id AS bulk_msg_attach_id FROM ss_bulk_message bm INNER JOIN ss_bulk_message_emails bme ON bme.bulk_message_id = bm.id INNER JOIN ss_bulk_message_attachment bma ON bma.bulk_message_id = bm.id WHERE bma.bulk_message_id ='" . $_POST['msgid'] . "' AND bme.bulk_message_id='" . $_POST['msgid'] . "'");

		$bulk_message_email = $db->query("delete from ss_bulk_message_emails where bulk_message_id='" . $_POST['msgid'] . "'");

		if (!empty($result->bulk_msg_email_id) && !empty($result->bulk_msg_attach_id)) {
			$bulk_email_attach = $db->query("delete from ss_bulk_message_attachment where bulk_message_id='" . $_POST['msgid'] . "'");
		}
		$bulk_message = $db->query("delete from ss_bulk_message where id='" . $_POST['msgid'] . "'");

		$db->query("BEGIN");
		if ($bulk_message) {
			echo json_encode(array('code' => "1", 'msg' => 'Mass email deleted successfully'));
			$db->query('COMMIT');
			exit;
		} else {
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0", 'msg' => 'Mass email deletion failed', 'Error_' => '1'));
			exit;
		}
	} else {
		echo json_encode(array('code' => "0", 'msg' => 'Mass email not found'));
		exit;
	}
}
  