<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================LIST ALL TEACHER RESOURCE=====================
if($_GET['action'] == 'list_teacher_resource'){
	//ACCESS TO ADMIN ONLY
		$finalAry = array();
		$all_teacher_resource = $db->get_results("SELECT g.group_name, cb.id, cb.group_id, cb.title,cl.class_name, cb.message, 
		(CASE WHEN cb.status=1 THEN 'Active' ELSE 'Inactive' END) AS status, cb.created_by_user_id, cb.group_id from ss_class_common_board cb 
		INNER JOIN ss_groups g ON g.id = cb.group_id
		LEFT JOIN ss_classes as cl on cl.id=cb.subject_id  
		WHERE cb.status <> 2 AND cb.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND g.is_active=1 AND g.is_deleted=0",ARRAY_A);

		for($i=0; $i<count((array)$all_teacher_resource); $i++){
				$all_attach = $db->get_results("SELECT attachment_file_path FROM ss_class_common_board_attach 
				WHERE class_common_board_id = '".$all_teacher_resource[$i]['id']."'");
				$attachemts = '';
			foreach ($all_attach as $key =>$value) {
				$attachemts .= '<a href="'.$value->attachment_file_path.'" target="_blank"> Attachment '.($key+1).'</a>'.', ';
			 }

			 $all_teacher_resource[$i]['attachment_file_path'] = rtrim($attachemts,', ');
			 if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT02'){
				if ($all_teacher_resource[$i]['created_by_user_id'] == $_SESSION['icksumm_uat_login_userid']) {
					$all_teacher_resource[$i]['check_role'] = $_SESSION['icksumm_uat_login_userid'];
				} 
			}elseif($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
				$all_teacher_resource[$i]['check_role'] = $_SESSION['icksumm_uat_login_userid'];
			}else{
				$all_teacher_resource[$i]['check_role'] = "";
			}
		}

		$finalAry['data'] = $all_teacher_resource;
		echo json_encode($finalAry);
		exit;

}

//==========================VIEW TEACHER RESOURCE=====================

elseif ($_GET['action'] == 'view_teacher_resource') {

	 $id = $_GET['id'];

	$all_teacher_resource = $db->get_row("SELECT g.group_name, cb.id, cb.group_id, cb.title, cb.message, 
	(CASE WHEN cb.status=1 THEN 'Active' ELSE 'Inactive' END) AS status, cb.created_on from ss_class_common_board cb 
	INNER JOIN ss_groups g ON g.id = cb.group_id WHERE cb.status <> 2 and cb.id = '".$id."' AND cb.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
	 AND g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

	$detail = '
	     <div class="row">
		   <div class="col-md-4"> <strong>Title:</strong> '.$all_teacher_resource->title.' </div>
		   <div class="col-md-4"> <strong>Date:</strong> '.my_date_changer($all_teacher_resource->created_on).'</div>
          <div class="col-md-4"> <strong>Group :</strong> '.$all_teacher_resource->group_name.' </div>	
		</div>
	  </div>
	  <br />
	  <div class="row">
		<div class="col-md-12"> <strong>Message:</strong> '.$all_teacher_resource->message.' </div>
	  </div>';

	  	$all_attach = $db->get_results("SELECT attachment_file_path FROM ss_class_common_board_attach WHERE class_common_board_id = '".$id."'");
				$attachemts = '';
			foreach ($all_attach as $key =>$value) {
				$attachemts .= '<a href="'.$value->attachment_file_path.'" target="_blank"> Attachment '.($key+1).'</a>'.', ';
			 }
		$detail .= '</div><div class="row">
			<div class="col-md-6"> <strong>Attachments:</strong> '.rtrim($attachemts,', ').' </div>
		  </div>';

	
	echo $detail;
	exit;
}

////////////////////////////////// save_teacher_resource ///////////////////
if ($_POST['action'] == 'save_teacher_resource') {
  //ADDED ON 14-MAY-2018
  ini_set('max_execution_time', 300); //300 seconds = 5 minutes
  ini_set('memory_limit', '1024M');

  $db->query('BEGIN');
  //var_dump($_FILES);

  $group_id = $_POST['group_id'];
  $subject_id = $_POST['subject'];
  $title = $db->escape($_POST['title']);
  $status = $db->escape($_POST['status']);
  $message = $db->escape($_POST['message']);

//   if(strlen($message)>35561){
//   echo json_encode(array('code' => "0",'msg' => 'Content or Image is too large'));
//   exit;
//   }
  $emailStatus = true;

  //STOP REPEATED CLICK ENTRY


    $sql_teacher_resource = $db->query("insert into ss_class_common_board set title = '" .  $title . "', message = '" . nl2br($message) . "', 
    status = '". $status."', group_id = '".$group_id."',subject_id='".$subject_id."' , created_on = '" . date('Y-m-d H:i:s') . "', 
    session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'");
    $message_id = $db->insert_id;


    if ($message_id) {
      

     $file_ary = reArrayFiles($_FILES['attachmentfile']);


     if(count((array)$file_ary)){
      $emailStatus = true;
     }
    
      foreach ($file_ary as $file) {

        $fileName = $file['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $uploadFileDir = 'attachments/';
        $filenameWOExt = pathinfo($fileName, PATHINFO_FILENAME);
        $filenameWOExt = str_replace(' ', '-', $filenameWOExt);
        $newFileName = $filenameWOExt . "-" . uniqid() . "." . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;
		$movefile=ROOTPATH.'teacher_resource/'.$uploadFileDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $movefile)) {

          if ($db->query("insert into ss_class_common_board_attach set class_common_board_id='" . $message_id . "', attachment_file_path='" . $dest_path . "'")) {
            $emailStatus = true;
          } else {
            $emailStatus = false;
          }
        }
      }
    
      /////////////////////


      if ($emailStatus && $db->query('COMMIT') !== false) {
      	echo json_encode(array('code' => "1",'msg' => 'Teacher resource created successfully'));
      	exit;
      } else {
        $db->query('ROLLBACK');
        echo json_encode(array('code' => "0",'msg' => 'Teacher resource not created. Please try again.'));
      	exit;
      }
    } else {
      $db->query('ROLLBACK');
       echo json_encode(array('code' => "0",'msg' => 'Record not saved. Please try again.'));
      exit;
    }
 
}


/////////////////// edit teacher resource

elseif($_POST['action'] == 'edit_teacher_resource'){


//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01') && !check_userrole_by_code('UT02')) {
	include "../includes/unauthorized_msg.php";
	return;
	}
	
	
	$get_teacher_resource = $db->get_row("SELECT g.group_name, cb.id, cb.group_id, cb.title, cb.message, cb.status from ss_class_common_board cb 
	INNER JOIN ss_groups g ON g.id = cb.group_id WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
	AND cb.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND cb.id = '".$_POST['id']."'");
	


	$get_class_board_attach = $db->get_results("SELECT id, attachment_file_path FROM ss_class_common_board_attach 
	Where class_common_board_id = '".$get_teacher_resource->id."'");
	
	
	if ($_POST['action'] == 'edit_teacher_resource') {
	
	//ADDED ON 14-MAY-2018
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	ini_set('memory_limit', '1024M');
	
	$db->query('BEGIN');
	//var_dump($_FILES);
	$group_id = $_POST['group_id'];
	$subject_id = $_POST['subject'];
	$title = $db->escape($_POST['title']);
	$status = $db->escape($_POST['status']);
	$message = $db->escape($_POST['message']);
	
	$check = $db->get_row("select * from ss_class_common_board  where LOWER(title) = '" .strtolower(trim($title)). "' and  group_id = '".$group_id."' and subject_id = '".$subject_id."' and id <> '".$_POST['id']."'  ");
	
	if(empty($check)){
	
	$emailStatus = false;
	
	//STOP REPEATED CLICK ENTRY
	$sql_teacher_resource = $db->query("update ss_class_common_board set title = '" .  $title . "', message = '" . nl2br($message) . "', 
	status = '". $status."', group_id = '".$group_id."', subject_id = '".$subject_id."', created_on = '" . date('Y-m-d H:i:s') . "', 
	session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' where id = '".$_POST['id']."'");
	
		if ($sql_teacher_resource) {
	
	
		if (!empty($_FILES['attachmentfile'])){
	
		$remove_attachment =  rtrim($_POST['remove_attachment_id'],', ');
	
		$remove_attach_array = explode(',', $remove_attachment);
		
		foreach ($remove_attach_array as  $remove) {
			$rec = $db->query("delete from ss_class_common_board_attach where id='".$remove."'");
	
		}
	
		$file_ary = reArrayFiles($_FILES['attachmentfile']);
		
	
		foreach ($file_ary as $file) {
			$fileName = $file['name'];
			$fileNameCmps = explode(".", $fileName);
			$fileExtension = strtolower(end($fileNameCmps));
			$uploadFileDir = 'teacher_resource/attachments/';
			$filenameWOExt = pathinfo($fileName, PATHINFO_FILENAME);
			$filenameWOExt = str_replace(' ', '-', $filenameWOExt);
			$newFileName = $filenameWOExt . "-" . uniqid() . "." . $fileExtension;
			
		//    $dest_path = $uploadFileDir . $newFileName;
			$dest_path = ROOTPATH.$uploadFileDir.$newFileName;
			$target_path = SITEURL.$uploadFileDir.$newFileName;
			
			if (move_uploaded_file($file['tmp_name'], $dest_path)) {
			
			if ($db->query("insert into ss_class_common_board_attach set class_common_board_id='" . $_POST['id']. "', attachment_file_path='" . $target_path . "'")) {
				$emailStatus = true;

			} else {
				$emailStatus = false;
			}
			}
		}
		/////////////////////
		}
	
		if ($sql_teacher_resource && $db->query('COMMIT') !== false) {
			$msg = 'Teacher resource updated successfully';
			$code = 1;
			echo json_encode(array('code' => "1",'msg' => 'Teacher resource updated successfully'));
		} else {
			$db->query('ROLLBACK');
			$msg = "Teacher resource not updated attachments. Please try again.";
			$code = 0;
			echo json_encode(array('code' => "0",'msg' => "Teacher resource not updated attachments. Please try again."));
		}
		} else {
		$db->query('ROLLBACK');
		$msg = "Teacher resource not updated. Please try again.";
		$code = 0;
		echo json_encode(array('code' => "0",'msg' => 'Teacher resource not updated. Please try again.'));
		
		}
	
	}else {
		$db->query('ROLLBACK');
		$msg = "Teacher Resources Already Exist In Database.";
		$code = 0;
		echo json_encode(array('code' => "0",'msg' => "Teacher Resources Already Exist In Database."));
	}
	
	}
	//echo $msg;

}

//==========================DELETE TEACHER RESOURCE=====================
elseif($_POST['action'] == 'delete_teach_resource'){
	if(isset($_POST['id'])){
	
			$rec = $db->query("update ss_class_common_board set status = 2 where id='".$_POST['id']."'");
			
			if($rec > 0){
				echo json_encode(array('code' => "1",'msg' => 'Teacher Resource deleted successfully'));
				exit;
			}else{
				echo json_encode(array('code' => "0",'msg' => 'Error: Teacher Resource deletion failed'));
				exit;
			}
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Process failed'));
		exit;
	}
}

//////////////////////////////////////////// fetch_assigned_group_class_for_select //////////////
elseif ($_POST['action'] == 'fetch_assigned_group_class_for_select') {
	$group_id = $_POST['group_id'];
	if (check_userrole_by_code('UT01')) {
		$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id where 
		ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and c.is_active = '1' and  c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	} elseif (check_userrole_by_code('UT02')) {
		$classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classes c INNER JOIN ss_classtime ct ON c.id = ct.class_id 
		INNER JOIN ss_staffclasstimemap sctm ON ct.id = sctm.classtime_id WHERE c.is_active = 1 AND ct.is_active = 1 AND sctm.active = 1 
		AND sctm.staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND group_id = '" . $group_id . "' AND 
		c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
		and sctm.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY c.disp_order");
	}
	$option = "";
	if (count((array)$classes)) {
		foreach ($classes as $cls) {
			$option .= "<option value = '" . $cls->id . "'>" . $cls->class_name . "</option>";
		}
	}
	echo $option;
	exit;
}



?>

