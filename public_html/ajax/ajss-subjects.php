<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}
 

//==========================LIST SUBJECT=====================
if($_GET['action'] == 'list_subjects'){ 
	    $finalAry = array();
		$get_subjects =$db->get_results("SELECT id, class_name, disp_order, is_active, (CASE WHEN is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status 
		FROM ss_classes WHERE is_active <> 2 AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ORDER BY disp_order ASC",ARRAY_A);
		for($i=0; $i<count((array)$get_subjects); $i++){

			$check_schdule_class = $db->get_row("SELECT ct.id FROM ss_classtime ct INNER JOIN ss_classes c ON c.id = ct.class_id LEFT JOIN ss_studentgroupmap m ON m.class_id = c.id WHERE c.id = '".$get_subjects[$i]['id']."' AND ct.is_active <> '2'");
			$check_map = $db->get_row("SELECT class_id FROM ss_studentgroupmap WHERE class_id = '".$get_subjects[$i]['id']."'");
			$classes_online = $db->get_row("SELECT cls.id FROM ss_classes_online cls INNER JOIN ss_classes c ON c.id = cls.class_id WHERE cls.class_id = '" . $get_subjects[$i]['id'] . "' and cls.status <> 2  ");
			if(!empty($check_schdule_class) || !empty($check_map) || !empty($classes_online)){
				$get_subjects[$i]['delete'] = '1';
			}else{
				$get_subjects[$i]['delete'] = '';
			}
		}
		$finalAry['data'] = $get_subjects;
		echo json_encode($finalAry);
		exit;

}

//==========================ADD BASIC FEES=====================
elseif($_POST['action'] == 'subjects_add'){
	$subject_name = $_POST['subject_name'];
	$display_order = $_POST['display_order'];
	$status = $_POST['status'];
	$all_subjects = $db->get_results("select * from ss_classes where class_name='".$subject_name."' and is_active <> 2 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
	$all_subjects_display_order = $db->get_results("select * from ss_classes where disp_order='".$display_order."' and is_active <> 2 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
    if (count((array)$all_subjects) == 0) {
		if (count((array)$all_subjects_display_order) == 0) {
			$subects = $db->query("insert into ss_classes set class_name='".$subject_name."', is_active='".$status."', 
			session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."',
			disp_order='".$display_order."', created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."'");
			$last_id = $db->insert_id;

			if ($last_id > 0) {
				$dispMsg = "<p class='text-success'>Class Added Successfully<p>";
				echo json_encode(array('code' => "1",'msg' => $dispMsg));
				exit;
			} else {
				$return_resp = array('code' => "0",'msg' => '<p class="text-danger"> Class Not Added <p>');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		}else{
			$return_resp = array('code' => "0",'msg' => '<p class="text-danger">Display order already exist in database <p>');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
    }else{
		$return_resp = array('code' => "0",'msg' => '<p class="text-danger">Class name already exist in database <p>');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
 
} 
//==========================EDIT BASIC FEES=====================
elseif($_POST['action'] == 'subjects_edit'){
	$all_subjects = $db->get_results("select * from ss_classes where id = '".$_POST['subject_id']."' and class_name='".$_POST['subject_name']."' and is_active <> 2 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

    $is_class_already = $db->get_results("select * from ss_classes where  id <> '".$_POST['subject_id']."' and class_name='".$_POST['subject_name']."' and is_active <> 2 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

	$all_subjects_display_order = $db->get_results("select * from ss_classes where id <> '".$_POST['subject_id']."' and disp_order='".$_POST['display_order']."' and is_active <> 2 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
  
	if (count((array)$is_class_already) == 0) {
		if (count((array)$all_subjects_display_order) == 0) {
			$subects = $db->query("update ss_classes set class_name='".$_POST['subject_name']."',  
			disp_order='".$_POST['display_order']."', is_active='".$_POST['status']."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', 
			updated_on='".date('Y-m-d H:i:s')."' where id = '".$_POST['subject_id']."'");	

			if($subects){
				$dispMsg = "<p class='text-success'>Class Updated Successfully<p>";
				echo json_encode(array('code' => "1",'msg' => $dispMsg));
				exit;
			}else{
				$return_resp = array('code' => "0",'msg' => '<p class="text-danger">Class Not Updated<p>');
				CreateLog($_REQUEST, json_encode($return_resp));
				echo json_encode($return_resp);
				exit;
			}
		}else{
			$return_resp = array('code' => "0",'msg' => '<p class="text-danger">Display order already exist in database <p>');
			CreateLog($_REQUEST, json_encode($return_resp));
			echo json_encode($return_resp);
			exit;
		}
    }else{
		$return_resp = array('code' => "0",'msg' => '<p class="text-danger">Class name already exist in database <p>');
		CreateLog($_REQUEST, json_encode($return_resp));
		echo json_encode($return_resp);
		exit;
	}
}

//=====================DELETE BASIC FEES==================


elseif($_POST['action'] == 'delete_subjects'){
	 
	if(isset($_POST['id'])){
		$rec = $db->query("update ss_classes set is_active='2' where id='".$_POST['id']."'");
		
		if($rec > 0){
			echo json_encode(array('code' => "1",'msg' => 'Class deleted successfully'));
			exit;
		}else{
			$return_resp = array('code' => "0",'msg' => 'Class not deletion');
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