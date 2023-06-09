<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}  

//==========================LIST ALL STAFF FOR ADMIN=====================
if($_GET['action'] == 'list_all_staff'){    
	$finalAry = array();

	$all_staffs = $db->get_results("SELECT s.user_id, 
	CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name, 
	u.email, mobile, s.is_deleted, u.is_active, (CASE WHEN s.is_deleted=1 THEN 'Deleted' WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status FROM ss_user u 
	INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_staff_session_map ssm on u.id = ssm.staff_user_id 
	WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND status = 1 ",ARRAY_A);

	for($i=0; $i<count((array)$all_staffs); $i++){
		$user_types = $db->get_var("SELECT GROUP_CONCAT(user_type)  FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON m.user_type_id = t.id WHERE m.user_id = '".$all_staffs[$i]['user_id']."'");
		$user_roleId = $db->get_var("SELECT role_id FROM ss_user_role_map where user_id = '".$all_staffs[$i]['user_id']."' ");

		if(empty($user_roleId)){
			$all_staffs[$i]['user_role_map'] = 'text-danger';
		}else{
			$all_staffs[$i]['user_role_map'] = 'text-muted';
		}

		$all_staffs[$i]['user_type'] = str_replace(',', ', ',$user_types);

		if($all_staffs[$i]['status'] == 'Deleted'){
			$all_staffs[$i]['status'] = "<span class='text-danger'>".$all_staffs[$i]['status']."</span>";
		}else{
			$all_staffs[$i]['status'] = $all_staffs[$i]['status'];
		}
		$all_staffs[$i]['mobile'] = internal_phone_check($all_staffs[$i]['mobile']);

		if(check_userrole_by_subgroup('admin')){
			$user_key = md5('0A0'.$all_staffs[$i]['user_id'].'0Z0');
			$all_staffs[$i]['admin_forced_login'] = $target_url = SITEURL.'admin-forced-login.php?unique_key='.$user_key;
		}
	}
	
	$finalAry['data'] = $all_staffs;
	echo json_encode($finalAry);
	exit;
}

//==========================EMAIL LOGIN INFO TO STAFF=====================
elseif($_POST['action'] == 'email_login_info_to_staff'){ 

	if(in_array("su_staff_list", $_SESSION['login_user_permissions'])){ 
		$staffid = $_POST['staffid'];
		
		$staff = $db->get_row("SELECT u.* FROM ss_staff f INNER JOIN ss_user u ON f.user_id = u.id where f.user_id = '".$staffid."'");

		
		if(!empty($staff) && trim($staff->username) != ''){
			$emailbody_parents = "Dear Staff Assalamualaikum,<br><br>You can login in ".CENTER_SHORTNAME.' '.SCHOOL_NAME." staff section using below information:<br>";

			$password_rec_link = SITEURL."new_password.php?id=".md5('iCjC'.$staffid.'1cjc');
														
			$emailbody_parents .= "<br><br><strong>Login URL:</strong> ".SITEURL."login.php";
			$emailbody_parents .= "<br><br><strong>Username/Email:</strong> ".trim($staff->username);
			$emailbody_parents .= "<br><br><strong>Password:</strong> Please use password provided earlier or <a href='".$password_rec_link."'>click here</a> to generate new password.";

			$emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';											

			$mailservice_request_from = MAIL_SERVICE_KEY; 
			$mail_service_array = array(
									'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME.' Login Details',
									'message' => $emailbody_parents,
									'request_from' => $mailservice_request_from,
									'attachment_file_name' => '',
									'attachment_file' => '',
									'to_email' => [$staff->username],
									'cc_email' => '',
									'bcc_email' => ''
								);

								mailservice($mail_service_array);
			
				echo json_encode(array('code' => "1",'msg' => 'Login details sent successfully'));

		}else{
			$return_resp = array('code' => "0",'msg' => 'Login details not sent. Please try later.', 'err_pos' => 2);
	            CreateLog($_REQUEST, json_encode($return_resp));
			    echo json_encode($return_resp);
			    exit;
		}
		
		exit;
	}
	}

//==========================STAFF VIEW ONLY INFO=====================
elseif($_POST['action'] == 'view_staff_detail'){
	$userid = $_POST['userid'];
	 
	/*$staff = $db->get_row("SELECT s.user_id,s.staff_number,s.gender,u.user_type_id,t.user_type,u.username,u.email,s.phone,s.mobile,
	s.address_1,s.address_2,s.city,s.state_id,s.country_id,
	CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name,	
	(CASE s.dob WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(s.dob,'%m/%d/%Y') END) AS dob,
	(CASE WHEN u.is_deleted=1 THEN 'Deleted' WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status FROM ss_user u 
	INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_usertype t ON t.id = u.user_type_id where s.user_id='".$userid."'");*/
	
	$staff = $db->get_row("SELECT s.user_id,s.staff_number,s.gender,u.user_type_id, u.username,u.email,s.phone,s.mobile,
	s.address_1,s.address_2,s.city,s.state_id,s.country_id,s.post_code,
	CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name,	
	(CASE s.dob WHEN '0000-00-00 00:00:00' THEN '-' ELSE DATE_FORMAT(s.dob,'%m/%d/%Y') END) AS dob,
	(CASE WHEN s.is_deleted=1 THEN 'Deleted' WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status FROM ss_user u 
	INNER JOIN ss_staff s ON u.id = s.user_id where s.user_id='".$userid."'");
	
	$user_types = $db->get_var("SELECT GROUP_CONCAT(user_type)  FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON m.user_type_id = t.id WHERE m.user_id = '".$userid."'");
	$user_types = str_replace(',', ', ',$user_types);
	
	$state = $db->get_var("select state from ss_state where id='".$staff->state_id."'");
	$country = $db->get_var("select country from ss_country where id='".$staff->country_id."'");
	
	$retStr = '<legend class="text-semibold">Personal Information</legend>				
            <div class="row">
              <div class="col-md-4">
                  <label for="first_name">Staff Name:</label>'.$staff->staff_name.'
              </div>
			  <div class="col-md-8">
                  <label>Username:</label>'.$staff->username.'
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                  <label for="dob">Date of Birth:</label>'.my_date_changer($staff->dob).'
              </div>
              <div class="col-md-4">
                  <label>Staff Type:</label>'.$user_types.'
              </div>
              <div class="col-md-4">
                <label for="status">Status</label>'.$staff->status.'
              </div>
			</div>
			<div class="row">
              <div class="col-md-4">
                  <label>Gender:</label>'.($staff->gender == 'm'?'Male':'Female').'
              </div>
            </div>
			<br>
			<legend class="text-semibold">Contact Information</legend>
            <div class="row">
              <div class="col-md-4">
                  <label>Primary No:</label>'.internal_phone_check($staff->mobile).'
              </div>';
              if (!empty($staff->phone)) {
              	  $retStr .='<div class="col-md-3">
                  <label>Alternate No:</label>'.internal_phone_check($staff->phone).'
              		</div>';
              }
			  $retStr .='  <div class="col-md-5">
					<label>Email:</label>'.$staff->email.'
				</div>';
            $retStr .='</div>
            <div class="row">
              <div class="col-md-4">
                  <label>Address Line 1:</label>'.$staff->address_1.'
              </div>
              <div class="col-md-4">
                  <label>Address Line 2:</label>'.$staff->address_2.'
              </div>
              
            </div>
            <div class="row">
			  <div class="col-md-4">
                  <label>City:</label>'.$staff->city.'
              </div>
              <div class="col-md-4">
                  <label>State:</label>'.$state.'
              </div>
              <div class="col-md-4">
                  <label>Country:</label>'.$country.'
              </div>
            </div>
			<div class="row">
			<div class="col-md-4">
			<label>Zip Code:</label>'.$staff->post_code.'
			</div>
            </div>';
	
	echo $retStr;
	exit;
}

//==========================FETCH SHEIKH TO ASSIGN GROUP <OPTION> WITH SELECTED STAFF=====================
elseif($_POST['action'] == 'get_sheikh_to_assign_group'){
	if(isset($_POST['groupid'])){
		$groupid = $_POST['groupid'];
	}
	
	$sel_user_id = $db->get_var("select s.user_id from ss_staff s inner join ss_staffgroupmap m on s.user_id = m.staff_user_id where group_id='".$groupid."' order by m.id desc limit 1");
	
	$option = '<option value="">Select</option>';
	
	$all_staffs = $db->get_results("SELECT s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name
	FROM ss_user u INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_usertype t ON t.id = u.user_type_id
	where u.is_active=1 and  u.is_deleted=0 and t.user_type_code = 'UT02'");
	
	foreach($all_staffs as $staff){
		$option .= "<option value='".$staff->user_id."' ".($staff->user_id == $sel_user_id?'selected="selected"':'')." >".$staff->staff_name."</option>";
	}

	echo $option;
	exit;
}

//==========================SAVE/ASSIGN SHEIKH TO GROUP=====================
/*if($_POST['action'] == 'assign_sheikh_to_group'){
	$groupid = $_POST['groupid'];
	$staffid = $_POST['staffid'];
	
	$db->query("insert into staffgroupmap set staff_user_id='".$_POST['staff_user_id']."', group_id='".$group_id."', 
	created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."', 
	updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."'");
	
	$sel_user_id = $db->get_var("select s.user_id from staff s inner join staffgroupmap m on s.user_id = m.staff_user_id where group_id='".$groupid."' order by m.id desc limit 1");
	
	$option = '<option value="">Select</option>';
	
	$all_staffs = $db->get_results("SELECT s.user_id, CONCAT(s.first_name,' ',s.middle_name,' ',s.last_name) AS staff_name
	FROM USER u INNER JOIN staff s ON u.id = s.user_id INNER JOIN usertype t ON t.id = u.user_type_id
	where u.is_active=1 and  u.is_deleted=0 and t.user_type_code = 'UT02'");
	
	foreach($all_staffs as $staff){
		$option .= "<option value='".$staff->user_id."' ".($staff->user_id == $sel_user_id?'selected="selected"':'')." >".$staff->staff_name."</option>";
	}

	echo $option;
	exit;
}*/

//==========================ADD STAFF=====================
elseif($_POST['action'] == 'add_staff'){
	$db->query('BEGIN');
	$found_in_staff = false;
	$staff_firstname = $_POST['first_name'];
	$staff_lastname = $_POST['last_name'];
	$staff_full_name = $staff_firstname.' '.$staff_lastname;
 
	
	//COMMENTED ON 16-AUG-2018
	//$emailCheck = $db->get_row("select * from ss_user where email='".trim($_POST['email'])."'");
	//ADDED ON 16-AUG-2018
// echo "select * from ss_user usr left join ss_staff stf on stf.user_id= usr.id where usr.username='".trim($_POST['email'])."' and usr.is_active=1 and usr.is_deleted =0";
// die;
	$emailCheck = $db->get_row("select usr.* from ss_user usr left join ss_staff stf on stf.user_id= usr.id where usr.username='".trim($_POST['email'])."' and usr.is_active=1 and usr.is_deleted =0");
	//$emailCheck = $db->get_row("select * from ss_user where email='".trim($_POST['email'])."'");
    $digits = 8;
    $password = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $digits));

	if(!empty($emailCheck) == 0){
		//EMAIL NOT EXISTS, MEANS NEW USER
		$db->query("insert into ss_user set username='".trim($db->escape($_POST['email']))."', password='".md5(trim($password))."', email='".trim($db->escape($_POST['email']))."', is_email_verified=0, is_locked=0, is_active=1, created_on='".date('Y-m-d H:i:s')."'");	
		$user_id = $db->insert_id;

	}elseif(isset($_POST['reuse_email'])){
		
		//EMAIL EXITS AND USER WANT TO REUSE THIS EMAIL AGAIN
		$user_id = $emailCheck->id;		

		//ROLES ALREADY ALLOTED TO USER


		$roles_already_alloted = $db->get_results("SELECT t.user_type, t.user_type_group, m.user_type_id  FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON m.user_type_id = t.id INNER JOIN ss_user u ON u.id = m.user_id WHERE m.user_id = '".$user_id."' AND m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and  u.is_active = 1 AND u.is_deleted = 0");
		// echo "<pre>";
		// print_r($roles_already_alloted);
		// die;

		foreach($roles_already_alloted as $already_alloted){
			foreach($_POST['user_type'] as $usertype){
				if($already_alloted->user_type_id == $usertype){
					echo json_encode(array('code' => "0",'msg' => 'Error: Role '.$already_alloted->user_type.' already alloted to this email id, Please try with another email or role','_errpos'=>'5'));
					exit;
				}
				
				if($already_alloted->user_type_group == "staff"){
					$found_in_staff	= true;			
				}
			}
		}
		$emailbody_parents = "Dear $staff_full_name Assalamu-alaikum,,<br><br>You can login in ".CENTER_SHORTNAME.' '.SCHOOL_NAME." staff portal using below information:<br>";
		$emailbody_parents .= "<br><br><strong>Login URL:</strong> ".SITEURL."login.php";
		$emailbody_parents .= "<br><br><strong>Username/Email:</strong> ".trim($_POST['email']);
		//$emailbody_parents .= "<br><br><strong>Password:</strong> Previous Password";
		$emailbody_parents .= "<br><strong>Password:</strong>Please Use the previous password or generate a new password <a href='" . SITEURL . "forgot_password.php'>click here</a>.";									
		$emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
		$mailservice_request_from = MAIL_SERVICE_KEY; 
		$mail_service_array = array(
								'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME.' Login Details',
								'message' => $emailbody_parents,
								'request_from' => $mailservice_request_from,
								'attachment_file_name' => '',
								'attachment_file' => '',
								'to_email' => [$_POST['email']],
								'cc_email' => '',
								'bcc_email' => ''
							);
							
		mailservice($mail_service_array);
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: The email address has already been used','_errpos'=>'4'));
		exit;
	}

	$staff_pending_check_no = $db->get_row("select * from ss_staff_registration where mobile='" . trim($db->escape($_POST['mobile'])) . "'");
	$staff_check_no = $db->get_row("select * from ss_staff where mobile='" . trim($db->escape($_POST['mobile'])) . "'");
	if(empty($staff_pending_check_no) && empty($staff_check_no)){
	if($user_id > 0){
		//SAVE DATA IN SESION TABLE


		$staff_session = $db->get_row("select * from ss_staff_session_map where staff_user_id='".$user_id."' and session_id='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and status = 1");

		if(empty($staff_session)){

			$staff_session_res = $db->query("insert into ss_staff_session_map set staff_user_id='".$user_id."', session_id='".$_SESSION['icksumm_uat_CURRENT_SESSION']."', 
			status = 1, created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."'");
		}


		if($found_in_staff){
			//ALREADY HOLDS A ROLE OF ROLE GROUP 'STAFF'
			foreach($_POST['user_type'] as $usertype){
				$usertype_added = $db->query("insert into ss_usertypeusermap set user_id='".$user_id."', user_type_id = '".$usertype."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', created_on = '".date('Y-m-d H:i')."'");
				$check_role_id = $db->get_var("SELECT role_id FROM ss_usertype WHERE id = '".$usertype."'");
				$check_user = $db->query("SELECT * FROM ss_user_role_map where user_id = '".$user_id."' AND role_id = '".$check_role_id."' AND status = 1");

				if(!empty($check_user)){
				$db->query("update ss_user_role_map set role_id='".trim($db->escape($check_role_id))."' where user_id = '".$user_id."'");
				$user_role = 1;
				}else{
				$user_role = $db->query("insert into ss_user_role_map set user_id='".trim($db->escape($user_id))."', role_id='".trim($db->escape($check_role_id))."' ");
				}
			}
			
			if($usertype_added && $db->query('COMMIT') !== false) {
				echo json_encode(array('code' => "1",'msg' => 'Staff created successfully','user_id'=>$user_id));
				exit;
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0",'msg' => 'Error: Registration failed','_errpos'=>'6'));
				exit;
			}
		}else{
			//ADD STAFF
			if($db->escape($_POST['dob_submit']) != ''){
				$dob_submit = date('Y-m-d',strtotime($db->escape($_POST['dob_submit'])));
				
				$sql_insert = "insert into ss_staff set user_id='".$user_id."', 
				staff_number='".trim($db->escape($_POST['staff_number']))."', 
				first_name='".trim($db->escape($_POST['first_name']))."', 
				middle_name='".trim($db->escape($_POST['middle_name']))."',				
				last_name='".trim($db->escape($_POST['last_name']))."',gender='".trim($db->escape($_POST['gender']))."',
				dob='".$dob_submit."',phone='".trim($db->escape($_POST['phone']))."',
				mobile='".trim($db->escape($_POST['mobile']))."',address_1='".trim($db->escape($_POST['address_1']))."',
				address_2='".trim($db->escape($_POST['address_2']))."',city='".trim($db->escape($_POST['city']))."',
				state_id='".trim($db->escape($_POST['state_id']))."',country_id='".trim($db->escape($_POST['country_id']))."',
				post_code='".trim($db->escape($_POST['post_code']))."',
				created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',created_on='".date('Y-m-d H:i:s')."',
				updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',updated_on='".date('Y-m-d H:i:s')."'";
			}else{
				$sql_insert = "insert into ss_staff set user_id='".$user_id."', 
				staff_number='".trim($db->escape($_POST['staff_number']))."', 
				first_name='".trim($db->escape($_POST['first_name']))."', 
				middle_name='".trim($db->escape($_POST['middle_name']))."',				
				last_name='".trim($db->escape($_POST['last_name']))."',gender='".trim($db->escape($_POST['gender']))."',
				phone='".trim($db->escape($_POST['phone']))."',
				mobile='".trim($db->escape($_POST['mobile']))."',address_1='".trim($db->escape($_POST['address_1']))."',
				address_2='".trim($db->escape($_POST['address_2']))."',city='".trim($db->escape($_POST['city']))."',
				state_id='".trim($db->escape($_POST['state_id']))."',country_id='".trim($db->escape($_POST['country_id']))."',
				post_code='".trim($db->escape($_POST['post_code']))."',
				created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',created_on='".date('Y-m-d H:i:s')."',
				updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',updated_on='".date('Y-m-d H:i:s')."'";
			}					
			
			$sql_usertype = '';
			$is_staff_added =  $db->query($sql_insert);

			
			//ADD NEW ROLE
			$cnt = 0;
			foreach($_POST['user_type'] as $usertype){

				$usertype_added = $db->query("insert into ss_usertypeusermap set user_id='".$user_id."', user_type_id = '".$usertype."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', created_on = '".date('Y-m-d H:i')."'");
				$check_role_id = $db->get_var("SELECT role_id FROM ss_usertype WHERE id = '".$usertype."'");
				$check_user = $db->query("SELECT * FROM ss_user_role_map where user_id = '".$user_id."' AND role_id = '".$check_role_id."' AND status = 1");

				if(!empty($check_user)){
				$db->query("update ss_user_role_map set role_id='".trim($db->escape($check_role_id))."' where user_id = '".$user_id."'");
				$user_role = 1;
				}else{
				$user_role = $db->query("insert into ss_user_role_map set user_id='".trim($db->escape($user_id))."', role_id='".trim($db->escape($check_role_id))."' ");
				}
			}
			
			if($usertype_added && $is_staff_added){
			
				if($db->query('COMMIT') !== false) {
					if(!isset($_POST['reuse_email'])){
					$emailbody_parents = "Dear $staff_full_name Assalamualaikum,<br><br>You can login in ".CENTER_SHORTNAME.' '.SCHOOL_NAME." staff portal using below information:<br>";
														
					$emailbody_parents .= "<br><br><strong>Login URL:</strong> ".SITEURL."login.php";
					$emailbody_parents .= "<br><br><strong>Username/Email:</strong> ".trim($_POST['email']);
					$emailbody_parents .= "<br><br><strong>Password:</strong> ".trim($password);
										
					$emailbody_parents .= '<br><br>'.BEST_REGARDS_TEXT.'<br>' . ORGANIZATION_NAME . ' Team';
					$mailservice_request_from = MAIL_SERVICE_KEY; 
					$mail_service_array = array(
											'subject' => CENTER_SHORTNAME.' '.SCHOOL_NAME.' Login Details',
											'message' => $emailbody_parents,
											'request_from' => $mailservice_request_from,
											'attachment_file_name' => '',
											'attachment_file' => '',
											'to_email' => [$_POST['email']],
											'cc_email' => '',
											'bcc_email' => ''
										);
										
					mailservice($mail_service_array);
				  }
					echo json_encode(array('code' => "1",'msg' => 'Staff Added Successfully','user_id'=>$user_id));
					exit;
				}else{
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => 'Staff Not Added','_errpos'=>'1'));
					exit;
				}
			}else{
				$db->query('ROLLBACK');
				echo json_encode(array('code' => "0",'msg' => 'Staff Not Added','_errpos'=>'2'));
				exit;
			}
		}
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0",'msg' => 'Staff Not Added','_errpos'=>'3'));
		exit;
	}
}else{
    $db->query('ROLLBACK');
    $return_resp = array('code' => "0",'msg' => 'Mobile number already exist in database','_errpos'=>'4');
    CreateLog($_REQUEST, json_encode($return_resp));
    echo json_encode($return_resp);
    exit;

}
	
}

//==========================CHECK, IS EMAIL IN USE=====================
elseif($_POST['action'] == 'is_email_in_user_internal'){
	if(isset($_POST['email'])){ 
		$email = trim($_POST['email']);
		
		$emailCheck = $db->get_row("select stf.first_name,usr.*  from ss_user usr 
		left join ss_staff stf on stf.user_id= usr.id 
		where usr.email = '".$email."' 
		and usr.is_active=1 
		and usr.is_deleted =0 ");
	 
		if(!empty($emailCheck)>0){
			if(empty($emailCheck->first_name)){
			echo '<div class="checkbox text-danger"><label><input type="checkbox" name="reuse_email" id="reuse_email" value="1"> Please reuse this email</label></div>';	//Email exists in our database
			}else{
				echo '<div class="checkbox text-danger"><label style="padding-left: 0px;"> This email address has already been used </label></div>';
				die;	
			}
		}else{
			echo '<div class="checkbox text-success"><label style="cursor: auto;">Email verified</label></div>';
		}


	}
}

//==========================CHECK, IS EMAIL IN USE=====================
elseif($_POST['action'] == 'is_email_in_user'){
	if(isset($_POST['email'])){ 
		$email = trim($_POST['email']);
	
		//$emailCheck = $db->get_results("select * from ss_user where username = '".$email."'");
		$staffCheck = $db->get_row("select * from ss_staff_registration where email='" .$email . "' and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
		if(!empty($staffCheck)){
			if($staffCheck->is_processed == 1){

				$emailCheck = $db->get_row("select stf.first_name,usr.*  from ss_user usr 
				left join ss_staff stf on stf.user_id= usr.id 
				where usr.email = '".$email."' 
				and usr.is_active=1 
				and usr.is_deleted =0 ");
					if(!empty($emailCheck)>0){
						if(empty($emailCheck->first_name)){
							echo '<div class="checkbox text-danger"><label><input type="checkbox" name="reuse_email" id="reuse_email" value="1"> Please reuse this email</label></div>';	//Email exists in our database
							}else{
								echo '<div class="checkbox text-danger"><label style="padding-left: 0px;"> This email address has already been used </label></div>';
								die;	
							}
					}else{
							echo '<div class="checkbox text-success"><label style="cursor: auto;">Email verified</label></div>';
					}

				
			}else{
				echo '<div class="checkbox text-danger"><label style="padding-left: 0px;"> We have already received a registration request for the entered email</label></div>';
				die;
			}
			
		}else{
			$emailCheck = $db->get_row("select stf.first_name,usr.*  from ss_user usr 
			left join ss_staff stf on stf.user_id= usr.id 
			where usr.email = '".$email."' 
			and usr.is_active=1 
			and usr.is_deleted =0 ");
				if(!empty($emailCheck)>0){
					if(empty($emailCheck->first_name)){
						echo '<div class="checkbox text-danger"><label><input type="checkbox" name="reuse_email" id="reuse_email" value="1"> Please reuse this email</label></div>';	//Email exists in our database
						}else{
							echo '<div class="checkbox text-danger"><label style="padding-left: 0px;"> This email address has already been used </label></div>';
							die;	
						}
				}else{
						echo '<div class="checkbox text-success"><label style="cursor: auto;">Email verified</label></div>';
				}
		}

		// $emailCheck = $db->get_results("select * from ss_user where email = '".$email."'");
		// if(count((array)$emailCheck)){
		// 	echo '<div class="checkbox text-danger"><label><input type="checkbox" name="reuse_email" id="reuse_email" value="1"> Please reuse this email</label></div>';	//Email exists in our database
		// }else{
		// 	echo '<div class="checkbox text-success"><label style="cursor: auto;">Email verified</label></div>';
		// }


	}
}

//==========================EDIT STAFF=====================
elseif($_POST['action'] == 'edit_staff'){ 
	$user_id = $_POST['user_id'];


	$emailCheck = $db->get_row("select * from ss_user where username='".trim($_POST['email'])."' and id <> '".$_POST['user_id']."'");
	$staff_check_no = $db->get_row("select * from ss_staff where mobile='" . trim($db->escape($_POST['mobile'])) . "' and user_id <> '".$_POST['user_id']."'");
	if(empty($staff_check_no)){
		if(!empty($emailCheck) == 0){
			// $emailCheck = $db->get_row("select * from ss_user where username='".trim($_POST['email'])."' and is_deleted = '0'");
			// if($emailCheck->id !== $_POST['user_id']){
			// echo json_encode(array('code' => "0",'msg' => 'Staff already exist'));
			// 		exit;
			// }
			$db->query('BEGIN');
			
			if($_POST['status'] == 'delete_hard'){
				$sql_ret =  $db->query("delete from ss_user where id = '".$user_id."'");	
				
				if($sql_ret && $db->query('COMMIT') !== false) {
					echo json_encode(array('code' => "1",'msg' => 'Staff deleted successfully'));
					exit;
				}else{
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => 'Error: Deletion (Permanent) failed','_errpos'=>'1'));
					exit;
				}	
			}else{
				if($_POST['status'] == 'delete_soft'){
					$is_deleted = 1;
					$is_active = 1;
				}elseif($_POST['status'] == 'active'){
					$is_active = 1;
					$is_deleted = 0;
				}else{
					$is_active = 0;
					$is_deleted = 0;
				}
			
				/*COMMENTED ON 16-AUG-2018
				$db->query("update ss_user set email='".trim($db->escape($_POST['email']))."',is_deleted='".$is_deleted."',
				is_active='".$is_active."',user_type_id='".$_POST['user_type']."', updated_on='".date('Y-m-d H:i:s')."' 
				where id = '".$user_id."'");*/
				
				//ADDED ON 16-AUG-2018

				$db->query("update ss_user set username='".trim($db->escape($_POST['email']))."', email='".trim($db->escape($_POST['email']))."', is_active='".$is_active."',updated_on='".date('Y-m-d H:i:s')."' where id = '".$user_id."'");		
				
				if(trim($_POST['password']) != ''){
					$db->query("update ss_user set password='".md5(trim($_POST['password']))."' where id = '".$user_id."'");		
				}
				
				if($db->escape($_POST['dob_submit']) != ''){
					$dob_submit = date('Y-m-d',strtotime($db->escape($_POST['dob_submit'])));
					$sql_update = "update ss_staff set first_name='".trim($db->escape($_POST['first_name']))."', 
					middle_name='".trim($db->escape($_POST['middle_name']))."',				
					last_name='".trim($db->escape($_POST['last_name']))."',gender='".trim($db->escape($_POST['gender']))."',
					dob='".$dob_submit."',phone='".trim($db->escape($_POST['phone']))."',
					mobile='".trim($db->escape($_POST['mobile']))."',address_1='".trim($db->escape($_POST['address_1']))."',
					address_2='".trim($db->escape($_POST['address_2']))."',city='".trim($db->escape($_POST['city']))."',
					state_id='".trim($db->escape($_POST['state_id']))."',country_id='".trim($db->escape($_POST['country_id']))."',
					post_code='".trim($db->escape($_POST['post_code']))."',				
					updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',updated_on='".date('Y-m-d H:i:s')."', is_deleted='".$is_deleted."' where user_id='".$user_id."'";
				}else{
					$sql_update = "update ss_staff set first_name='".trim($db->escape($_POST['first_name']))."', 
					middle_name='".trim($db->escape($_POST['middle_name']))."',				
					last_name='".trim($db->escape($_POST['last_name']))."',gender='".trim($db->escape($_POST['gender']))."',
					phone='".trim($db->escape($_POST['phone']))."',
					mobile='".trim($db->escape($_POST['mobile']))."',address_1='".trim($db->escape($_POST['address_1']))."',
					address_2='".trim($db->escape($_POST['address_2']))."',city='".trim($db->escape($_POST['city']))."',
					state_id='".trim($db->escape($_POST['state_id']))."',country_id='".trim($db->escape($_POST['country_id']))."',
					post_code='".trim($db->escape($_POST['post_code']))."',				
					updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',updated_on='".date('Y-m-d H:i:s')."', is_deleted='".$is_deleted."' where user_id='".$user_id."'";
				}

				$is_staff_added =  $db->query($sql_update);
				
				//$db->query("delete from ss_usertypeusermap where user_id='".$user_id."'");

				$user_type = $db->get_row("SELECT * FROM ss_usertype where user_type_code = 'UT05'");
				$db->query("delete from ss_usertypeusermap where user_id='".$user_id."' and user_type_id <> '".$user_type->id."' AND session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
				$db->query("delete from ss_user_role_map where user_id = '".$user_id."' ");

				foreach($_POST['user_type'] as $usertype){
					$usertype_added = $db->query("insert into ss_usertypeusermap set user_id='".$user_id."', 
					user_type_id = '".$usertype."', session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."', 
					created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', created_on = '".date('Y-m-d H:i')."'");

					$check_role_id = $db->get_var("SELECT role_id FROM ss_usertype WHERE id = '".$usertype."'");
					$check_user = $db->query("SELECT * FROM ss_user_role_map where user_id = '".$user_id."' AND role_id = '".$check_role_id."' AND status = 1");
					if($_POST['status'] == 'delete_soft'){
						$db->query("delete from ss_user_role_map where user_id = '".$user_id."' and role_id='".trim($db->escape($check_role_id))."'");
						$db->query("delete from ss_usertypeusermap where user_id = '".$user_id."' and user_type_id='".trim($db->escape($usertype))."'");		
					}
	/* 				if(!empty($check_user)){
					$db->query("update ss_user_role_map set role_id='".trim($db->escape($check_role_id))."' where user_id = '".$user_id."'");
					$user_role = 1;
					}else{ */
					
					$user_role = $db->query("insert into ss_user_role_map set user_id='".trim($db->escape($user_id))."', role_id='".trim($db->escape($check_role_id))."' ");
					//}
				}
					
				if($is_staff_added){
					if($db->query('COMMIT') !== false) {
						echo json_encode(array('code' => "1",'msg' => 'Staff updated successfully','user_id'=>$user_id));
						exit;
					}else{
						$db->query('ROLLBACK');
						echo json_encode(array('code' => "0",'msg' => 'Error: Updation failed','_errpos'=>'2'));
						exit;
					}
				}else{
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => "Error: Updation failed",'_errpos'=>'3'));
					exit;
				}
			}
		}else{
			echo json_encode(array('code' => "0",'msg' => 'Error: Email already exists','_errpos'=>'4'));
			exit;
		}
  }else{
		$db->query('ROLLBACK');
		$return_resp = array('code' => "0",'msg' => 'Mobile number already exist in database','_errpos'=>'5');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;

	}
}

//==========================EDIT STAFF PERDONAL=====================
elseif($_POST['action'] == 'edit_staff_personal'){
	$user_id = $_SESSION['icksumm_uat_login_userid'];
	$db->query('BEGIN');
	
	$is_staff_added =  $db->query("update ss_staff set 
	first_name='".trim($db->escape($_POST['first_name']))."', middle_name='".trim($db->escape($_POST['middle_name']))."',				
	last_name='".trim($db->escape($_POST['last_name']))."',gender='".trim($db->escape($_POST['gender']))."',
	dob='".trim($db->escape($_POST['dob_submit']))."',phone='".trim($db->escape($_POST['phone']))."',
	mobile='".trim($db->escape($_POST['mobile']))."',address_1='".trim($db->escape($_POST['address_1']))."',
	address_2='".trim($db->escape($_POST['address_2']))."',city='".trim($db->escape($_POST['city']))."',post_code='".$_POST['post_code']."',
	state_id='".trim($db->escape($_POST['state_id']))."',country_id='".trim($db->escape($_POST['country_id']))."',
	updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."',updated_on='".date('Y-m-d H:i:s')."' where user_id='".$user_id."'");
	
	if($is_staff_added){
		if($db->query('COMMIT') !== false) {
			echo json_encode(array('code' => "1",'msg' => 'Staff updated successfully','user_id'=>$user_id));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: Updation failed'));
			exit;
		}
	}else{
		$db->query('ROLLBACK');
		echo json_encode(array('code' => "0",'msg' => 'Error: Updation failed'));
		exit;
	}
}

//=====================DELETE STAFF==================
/*elseif($_POST['action'] == 'delete_staff'){
	if(isset($_POST['user_id'])){
		$rec = $db->query("update ss_user set is_deleted=1, updated_on='".date("Y-m-d H:i:s")."' where id='".$_POST['user_id']."'");
		
		if($rec > 0){
			echo json_encode(array('code' => "1",'msg' => 'Staff deleted (soft) successfully'));
			exit;
		}else{
			echo json_encode(array('code' => "0",'msg' => 'Error: Staff deletion failed'));
			exit;
		}
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Process failed'));
		exit;
	}
}*/

elseif($_POST['action'] == 'check_photo_file'){
	if(isset($_FILES["photo"])){
		if($_FILES["photo"]["error"] == 0){
			$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
			$filename = $_FILES["photo"]["name"];
			$filetype = $_FILES["photo"]["type"];
			$filesize = $_FILES["photo"]["size"];    
	
			// Verify file extension
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
	
			if(!array_key_exists($ext, $allowed)){
				//echo json_encode(array('code' => "0",'msg' => 'Error: Please select a valid file format'));
				echo 'Error: Please select a valid file format';
				exit;
			}    
	
			// Verify file size - 2MB maximum
			$maxsize = 2 * 1024 * 1024;
	
			if($filesize > $maxsize){
				//echo json_encode(array('code' => "0",'msg' => 'Error: File size must be less than 2 MB'));
				echo 'Error: File size must be less than 2 MB';
				exit;
			}
		}else{
			//echo json_encode(array('code' => "0",'msg' => 'Error: '.$_FILES["photo"]["error"]));
			echo $_FILES["photo"]["error"];
			exit;
		}	
	}
}elseif($_POST['action'] == 'update_photo'){
	
}



?>