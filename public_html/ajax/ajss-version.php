<?php 
include_once "../includes/config.php";

//==========================ADD version=====================
if($_POST['action'] == 'add_version'){
	  $db->query('BEGIN');

		 if($_POST['status'] == 1){

		  $db->query("update ss_software_version set status='0' ");
           
		 }

		$res = $db->query("insert into ss_software_version set
		major='".trim($db->escape($_POST['major']))."', 
		miner='".trim($db->escape($_POST['miner']))."', 
		patch='".trim($db->escape($_POST['patch']))."',	
		notification='".trim($db->escape($_POST['notification']))."',
		status='".trim($db->escape($_POST['status']))."',			
		describtion='".trim($db->escape($_POST['describtion']))."',
		created_on='".date('Y-m-d H:i:s')."',
		updated_on='".date('Y-m-d H:i:s')."'");

		if($res){
            $db->query('COMMIT');
			echo json_encode(array('code' => "1",'msg' => 'Version added successfully.'));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: version added failed','_errpos'=>'1'));
			exit;
		}
	
}


//==========================LIST ALL STAFF FOR ADMIN=====================
elseif($_GET['action'] == 'list_all_version'){  
	$finalAry = array();

	$all_version = $db->get_results("SELECT *, (CASE WHEN status=1 THEN 'Active' ELSE 'Inactive' END) AS status, (CASE WHEN notification=1 THEN 'Yes' ELSE 'No' END) AS notification FROM ss_software_version ",ARRAY_A);
	
	$finalAry['data'] = $all_version;
	echo json_encode($finalAry);
	exit;
}


//==========================ADD version=====================
elseif($_POST['action'] == 'edit_version'){
	  $db->query('BEGIN');

	  	 if($_POST['status'] == 1){

		   $db->query("update ss_software_version set status='0' where id <> '".$_POST['id']."' ");
           
		 }

		$res = $db->query("update ss_software_version set
		major='".trim($db->escape($_POST['major']))."', 
		miner='".trim($db->escape($_POST['miner']))."', 
		patch='".trim($db->escape($_POST['patch']))."',	
		notification='".trim($db->escape($_POST['notification']))."',
		status='".trim($db->escape($_POST['status']))."',			
		describtion='".trim($db->escape($_POST['describtion']))."',
		updated_on='".date('Y-m-d H:i:s')."' where id = ".$_POST['id']." ");

		if($res){
            $db->query('COMMIT');
			echo json_encode(array('code' => "1",'msg' => 'Version updated successfully.'));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: version updated failed','_errpos'=>'1'));
			exit;
		}
	
}


//==========================ADD version=====================
elseif($_POST['action'] == 'delete_version'){
	  $db->query('BEGIN');

         $res = $db->query("delete from ss_software_version where id = '".$_POST['version_id']."'");	

		if($res){
            $db->query('COMMIT');
			echo json_encode(array('code' => "1",'msg' => 'Version deleted successfully.'));
			exit;
		}else{
			$db->query('ROLLBACK');
			echo json_encode(array('code' => "0",'msg' => 'Error: version deleted failed','_errpos'=>'1'));
			exit;
		}
	
}


?>