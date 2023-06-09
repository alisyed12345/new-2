<?php
include_once "../includes/config.php";
if($_GET['action'] == 'list_email_template'){ 
 
        $finalAry = array();
        $email_temp =$db->get_results("SELECT e.id, e.email_template, e.email_subject, e.email_cc, e.email_bcc, et.type_name, et.status, (CASE WHEN et.status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active, et.system_template FROM ss_email_templates e INNER JOIN ss_email_template_types et ON e.email_template_type_id = et.id where et.status <> 2 AND et.system_template=0 ORDER BY id DESC",ARRAY_A);
        if(check_userrole_by_code('UT01') && check_userrole_by_group('admin')){ 
        $email_temp =$db->get_results("SELECT e.id, e.email_template, e.email_subject, e.email_cc, e.email_bcc, et.type_name, et.status, (CASE WHEN et.status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active, (CASE WHEN et.system_template=1 THEN 'YES' ELSE 'NO' END) AS defaulttemp, et.system_template FROM ss_email_templates e INNER JOIN ss_email_template_types et ON e.email_template_type_id = et.id where et.status <> 2 ORDER BY id DESC",ARRAY_A);
        }
        for($i=0; $i<count((array)$email_temp); $i++){
            $email_temp[$i]['email_subject'] = $email_temp[$i]['email_subject'];
        }
        $finalAry['data'] = $email_temp;
        echo json_encode($finalAry);
        exit;
}
elseif($_POST['action'] == 'add_email_template'){

    $email_template_type = $_POST['email_template_type'];
    $email_cc = $_POST['email_cc'];
    $email_bcc = $_POST['email_bcc'];
    $email_subject = $_POST['email_subject'];
    $template_body = $_POST['template_body'];

    // if(strlen($template_body)>1424320){
    // echo json_encode(array('code' => "0",'msg' => 'Content or Image is too large'));
    // exit;
    // }

    if ($_POST['system_temp'] == 1) {
      $system_template = $_POST['system_temp'];
    }else{
      $system_template = '0';
    }
    $db->query('BEGIN');


    $email_template_type = $db->query("insert into ss_email_template_types set type_name='".$email_template_type."', system_template='".$system_template."', status = '".$_POST['status']."'");

   	$email_template_type_id = $db->insert_id;
    $email_template_type_id;

   
   if ($email_template_type_id > 0) {

      $email_temp = $db->query("insert into ss_email_templates set email_template_type_id='".$email_template_type_id."', email_template='".$template_body."', email_cc='".$email_cc."', email_bcc='".$email_bcc."', email_subject='".$email_subject."', status = '".$_POST['status']."'");
      $email_temp_last_id = $db->insert_id;             
      if($email_temp_last_id > 0 && $db->query('COMMIT') !== false){
         
          echo json_encode(array('code' => "1",'msg' => "Email Template Added Successfully"));
          exit;
      }else{
          $db->query('ROLLBACK');
          $return_resp = array('code' => "0",'msg' => 'Email Template Not Added', 'Error' => '1' );
          CreateLog($_REQUEST, json_encode($return_resp));
          echo json_encode($return_resp);
          exit;
      }

    }else{
         $db->query('ROLLBACK');
          $return_resp = array('code' => "0",'msg' => 'Email Template Not Added', 'Error' => '1');
          CreateLog($_REQUEST, json_encode($return_resp));
          echo json_encode($return_resp);
          exit;
    }
}
elseif($_POST['action'] == 'edit_email_template'){
    $temp_email_id = $_POST['email_temp_id'];
    $email_template_type = $_POST['email_template_type'];
    $email_cc = $_POST['email_cc'];
    $email_bcc = $_POST['email_bcc'];
    $email_subject = $_POST['email_subject'];
    $template_body = $_POST['template_body'];

    if ($_POST['system_temp'] == 1) {
      $system_template = '1';
    }else{
      $system_template = '0';
    }
  
     
    $db->query('BEGIN');
    $results = $db->get_row("SELECT etype.id, etemp.email_template, etemp.email_subject, etemp.email_cc, etemp.email_bcc, etemp.status, etype.type_name FROM ss_email_templates etemp INNER JOIN ss_email_template_types etype ON etype.id = etemp.email_template_type_id WHERE etemp.id = '".$temp_email_id."'");

    if($temp_email_id && $db->query('COMMIT') !== false){
        $db->query("update ss_email_template_types set type_name = '".$email_template_type."', status='".$_POST['status']."',  system_template='".$system_template."' where id = '".$results->id."'");
        $edit_email_template = $db->query("update ss_email_templates set email_template_type_id='".$results->id."', email_template='".$template_body."', email_cc='".$email_cc."', email_bcc='".$email_bcc."', email_subject='".$email_subject."', status='".$_POST['status']."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id = '".$temp_email_id."'");
        echo json_encode(array('code' => "1",'msg' => "Email Template Updated Successfully"));
        exit;
    }else{
        $db->query('ROLLBACK');
        $return_resp = array('code' => "0",'msg' => 'Email Template Not Updated ');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
//========================== VIEW EMAIL TEMPLATE =====================

elseif ($_POST['action'] == 'view_email_template') {

  $id = $_POST['id'];
  $all_email_template = $db->get_row("SELECT e.id, e.email_template as temp_boday, e.email_subject, e.email_cc, e.email_bcc, et.type_name, et.status, (CASE WHEN e.status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active FROM ss_email_templates e INNER JOIN ss_email_template_types et ON e.email_template_type_id = et.id where et.status = 1 and e.id = '".$id."'");

  $detail = '
    <div class="row">
    <div class="col-md-12"> <strong>Template Name:</strong> '.$all_email_template->type_name.' </div>
    </div>
    <div class="row" style="margin-top:10px;"> 
      <div class="col-md-4"> <strong>Subject :</strong> '.$all_email_template->email_subject.' </div> 
      <div class="col-md-4"> <strong>Cc :</strong> '.$all_email_template->email_cc.' </div> 
      <div class="col-md-4"> <strong>BCc :</strong> '.$all_email_template->email_bcc.' </div> 
    </div>
    <div class="row" style="margin-top:10px;">
    <div class="col-md-12"> <strong>Template Body:</strong> '.$all_email_template->temp_boday.' </div>
    </div>';
  
  echo $detail;
  exit;
}
elseif($_POST['action'] == 'delete_email_template'){
   if(isset($_POST['id'])){
       $rec = $db->query("DELETE FROM ss_email_templates where id='".$_POST['id']."'");
       
       if($rec > 0){
           echo json_encode(array('code' => "1",'msg' => 'Email Template Deleted Successfully'));
           exit;
       }else{
           $return_resp = array('code' => "0",'msg' => 'Email Template Not Deleted');
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