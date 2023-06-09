<?php 
include_once "../includes/config.php";
//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}
//==========================LIST ALL ANNOUNCEMENTS FOR ADMIN=====================
if($_GET['action'] == 'list_all_announcements'){
	//ACCESS TO ADMIN ONLY
	//if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
	//if(check_userrole_by_code('UT01')){
		$finalAry = array();
		$all_announcements = $db->get_results("SELECT * from ss_announcements where session  = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'",ARRAY_A);
		for($i=0; $i<count((array)$all_announcements); $i++){
			if($all_announcements[$i]['is_active'] == '1'){
				$all_announcements[$i]['is_active'] = "Active";
			}elseif($all_announcements[$i]['is_active'] == '0'){
				$all_announcements[$i]['is_active'] = "Inactive";
			}
            $message = $all_announcements[$i]['message'];
			$all_announcements[$i]['message'] = strlen($message) > 50 ? substr($message,0,50)."..." : $message;
 
			/*if($all_announcements[$i]['date_start'] == $all_announcements[$i]['date_end']){
				$all_announcements[$i]['announcement_date'] = date('m/d/Y',strtotime($all_announcements[$i]['date_start']));
			}else{
				$all_announcements[$i]['announcement_date'] = date('m/d/Y',strtotime($all_announcements[$i]['date_start'])).' - '.date('m/d/Y',strtotime($all_announcements[$i]['date_end']));
			}

			if($all_announcements[$i]['is_for_all_groups'] == 1){
				$all_announcements[$i]['for_group'] = 'All Groups';
			}else{
				$announcement_groups = $db->get_results("SELECT * FROM ss_announcement_groups h INNER JOIN ss_groups g ON h.group_id = g.id where announcement_id = '".$all_announcements[$i]['id']."'");

				foreach($announcement_groups as $hg){
					if(trim($all_announcements[$i]['for_group']) == ""){
						$all_announcements[$i]['for_group'] = $hg->group_name;
					}else{
						$all_announcements[$i]['for_group'] = $all_announcements[$i]['for_group'].", ".$hg->group_name;
					}
				}
			}*/
		}
		$finalAry['data'] = $all_announcements;
		echo json_encode($finalAry);
		exit;
	//}
}
//==========================ADD/EDIT ANNOUNCEMENT===================
elseif($_POST['action'] == 'save_announcement'){
	$announcement_date_ary = explode('-',trim($_POST['announcement_date']));
	//$date_start = date('Y-m-d',strtotime($announcement_date_ary[0]));
	//$date_end = date('Y-m-d',strtotime($announcement_date_ary[1]));
	$is_active = $_POST['is_active'];
	$announcement_id = $_POST['announcement_id'];
	$message = $db->escape($_POST['message']);
	$display_order = $db->escape($_POST['display_order']);
	/*if(is_array($_POST['group_id'])){
		$is_for_all_groups = 0;
	}else{
		$is_for_all_groups = 1;
	}*/

				$db->query('BEGIN');
				$all_announcement = $db->get_results("select * from ss_announcements where message='".$message."' and is_active <> 2 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
                
				
    
				if(is_numeric($announcement_id)){


					$order = $db->get_var("SELECT display_order from ss_announcements where id='".$announcement_id."' AND session  = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active=1 ");

					$a = $order;
					$b = $display_order;

					if($a < $b){
						$set = "display_order = display_order - 1";
						$where = "display_order > $a and display_order <= $b";
					}else{
						$set = "display_order = display_order + 1";
						$where = "display_order < $a and display_order >= $b";
						}

					$db->query("update ss_announcements set $set where $where ");

					//EDIT EXISTING ANNOUNCEMENT
					$result = $db->query("update ss_announcements set is_active='".$is_active."', message='".$message."', display_order='".$display_order."', 		
					updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on = '".date('Y-m-d H:i:s')."' where id = '".$announcement_id."'");

				}else{
					//NEW ANNOUNCEMENT
					if (count((array)$all_announcement) == 0) {


							$db->query("update ss_announcements set display_order = display_order+1 where display_order >= $display_order");

							$result = $db->query("insert into ss_announcements set is_active='".$is_active."', message = '".$message."', display_order='".$display_order."', 
							session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."', created_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', 
							created_on = '".date('Y-m-d H:i:s')."', updated_by_user_id = '".$_SESSION['icksumm_uat_login_userid']."', updated_on = '".date('Y-m-d H:i:s')."'");
							$announcement_id = $db->insert_id;


					}else{
							$db->query('ROLLBACK');
							echo json_encode(array('code' => "0",'msg' => 'Announcement already exist in database'));
							exit;
					}
				}  
				/*if($result){
					$db->query("delete from ss_announcement_groups where announcement_id = '".$announcement_id."'");

					foreach($_POST['group_id'] as $grpid){
						$group_result = $db->query("insert into ss_announcement_groups set announcement_id = '".$announcement_id."', group_id = '".$grpid."'");
					}
				}*/
				if($result && $db->query('COMMIT') !== false) {
					echo json_encode(array('code' => "1",'msg' => 'Announcement saved successfully'));
					exit;
				}else{
					$db->query('ROLLBACK');
					echo json_encode(array('code' => "0",'msg' => 'Error: Announcement not saved'));
					exit;
				}
}
//==========================FETCH GROUPS OF TEACHERS - USED IN MESSAGE SECTION=====================
elseif($_POST['action'] == 'fetch_announcement'){
	$announcement_id = $_POST['announcement_id'];
	$announcement = $db->get_row("SELECT * from ss_announcements where id = '".$announcement_id."'");
	if(!empty($announcement)){
		/*if($announcement->is_for_all_groups == 0){
			$group_ids = $db->get_var("SELECT GROUP_CONCAT(group_id) from ss_announcement_groups where announcement_id = '".$announcement_id."'");
		}*/
   		echo json_encode(array('code' => 1, 'id' => $announcement->id, 'message' => $announcement->message, 
		'display_order' => $announcement->display_order, 'is_active' => $announcement->is_active));
	}else{
		echo json_encode(array('code' => 0));
	}
	exit;
}
//=====================DELETE ANNOUNCEMENT==================
elseif($_POST['action'] == 'delete_announcement'){
	$announcement_id = $_POST['announcement_id'];
	$rec = $db->query("delete from ss_announcements where id='".$_POST['announcement_id']."'");
	if($rec > 0){
		echo json_encode(array('code' => "1",'msg' => 'Announcement deleted successfully'));
		exit;
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Announcement deletion failed'));
		exit;
	}
}
?>