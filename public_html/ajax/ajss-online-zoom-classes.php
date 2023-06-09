<?php 
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}

//==========================LIST SUBJECT=====================
if($_GET['action'] == 'list_online_zoom_classes'){ 
		$finalAry = array();
		$get_subjects =$db->get_results("SELECT cls.id, cls.group_id, cls.class_id, cls.meeting_url, cls.meeting_id, cls.meeting_password, 
		cls.status, (CASE WHEN cls.status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active, g.group_name, c.class_name 
		FROM ss_classes_online cls INNER JOIN ss_groups g ON cls.group_id = g.id INNER JOIN ss_classes c ON cls.class_id = c.id 
		WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND cls.status <> 2",ARRAY_A);

		$finalAry['data'] = $get_subjects;
		echo json_encode($finalAry);
		exit;
}

//==========================ADD Online class=====================
elseif($_POST['action'] == 'online_classes_add'){
	$is_class_already = $db->get_results("SELECT * FROM ss_classes_online WHERE group_id='".$_POST['group_id']."' AND class_id='".$_POST['group_class']."' AND status='1' ");
    if(count((array)$is_class_already)>0)
    {
        $return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Same group and class already active <p>');
		echo json_encode($return_resp);
		exit;
    }
    else
    {
	    $online_class = $db->query("insert into ss_classes_online set group_id='".$_POST['group_id']."', class_id='".$_POST['group_class']."', meeting_url='".$_POST['meeting_url']."', meeting_id='".$_POST['meeting_id']."', meeting_password='".$_POST['meeting_password']."',
	    status='".$_POST['status']."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."'");
		$last_id = $db->insert_id;
		if($last_id > 0){
			$dispMsg = "<p class='text-success'> Online class added successfully <p>";
			echo json_encode(array('code' => "1",'msg' => $dispMsg));
			exit;
		}else{
			$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Online class not added <p>');
	        CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
    }
    
}

//==========================EDIT Online Class=====================
elseif($_POST['action'] == 'online_classes_edit'){
		$online_class = $db->query("update ss_classes_online set group_id='".$_POST['group_id']."', class_id='".$_POST['group_class']."', meeting_url='".$_POST['meeting_url']."', meeting_id='".$_POST['meeting_id']."', meeting_password='".$_POST['meeting_password']."',
		status='".$_POST['status']."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id = '".$_POST['online_class_id']."'");
		if($online_class){
			$dispMsg = "<p class='text-success'> Online class updated successfully <p>";
			echo json_encode(array('code' => "1",'msg' => $dispMsg));
			exit;
		}else{
			$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Online class not updated <p>');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
}

//=====================DELETE Online Class==================
elseif($_POST['action'] == 'delete_online_class'){
	if(isset($_POST['id'])){
		$rec = $db->query("update ss_classes_online set status='2' where id='".$_POST['id']."'");
		if($rec > 0){
			echo json_encode(array('code' => "1",'msg' => 'Online Class deleted successfully'));
			exit;
		}else{
			$return_resp = array('code' => "0",'msg' => 'Online Class not deletion');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
	}else{
		$return_resp = array('code' => "0",'msg' => 'Error: Process failed');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}
?>