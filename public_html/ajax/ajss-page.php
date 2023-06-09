<?php 
include_once "../includes/config.php";

//AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
	return;
}
//==========================LIST PAGES=====================
if($_GET['action'] == 'list_pages'){
	$finalAry = array();
	$sql = "SELECT id, page_name, slug, (CASE WHEN active=1 THEN 'Active' ELSE 'Inactive' END) AS status FROM ss_page order by page_name asc";
	$pages = $db->get_results($sql,ARRAY_A);
	$finalAry['data'] = $pages;
	echo json_encode($finalAry);
	exit;
}
//==========================EDIT PAGES=====================
elseif($_POST['action'] == 'edit_page'){
	$page_id = $_POST['page_id'];
	$slug = slug($db->escape($_POST['page_name']));
	$titleCheck = $db->get_results("select * from ss_page where slug = '".$slug."' and id <> '".$page_id."'");
	if(count((array)$titleCheck) == 0){
		$db->query('BEGIN');
		$sql_ret =  $db->query("update ss_page set page_name='".$db->escape(trim($_POST['page_name']))."', 
		slug='".$slug."', contents='".$db->escape(trim($_POST['contents']))."', active = '".$_POST['active']."',
		updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id='".$page_id."'");		
		if($sql_ret && $db->query('COMMIT') !== false) {
			echo json_encode(array('code' => "1",'msg' => 'Page updated successfully'));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>'1'));
			exit;
		}
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Page title alrady exists','_errpos'=>'2'));
		exit;
	}
}
//==========================ADD PAGES=====================
elseif($_POST['action'] == 'add_page'){
	$slug = slug($db->escape($_POST['page_name']));
	$titleCheck = $db->get_results("select * from ss_page where slug = '".$slug."'");
	if(count((array)$titleCheck) == 0){
		$db->query('BEGIN');
		$sql_ret =  $db->query("insert into ss_page set page_name='".$db->escape(trim($_POST['page_name']))."', 
		slug='".$slug."', contents='".$db->escape(trim($_POST['contents']))."', active = '".$_POST['active']."',
		created_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', created_on='".date('Y-m-d H:i:s')."'");
		if($sql_ret && $db->query('COMMIT') !== false) {
			echo json_encode(array('code' => "1",'msg' => 'Page added successfully'));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: Process failed','_errpos'=>'1'));
			exit;
		}
	}else{
		echo json_encode(array('code' => "0",'msg' => 'Error: Page name alrady exists','_errpos'=>'2'));
		exit;
	}
}
?>