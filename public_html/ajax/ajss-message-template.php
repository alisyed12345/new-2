<?php
include_once "../includes/config.php";
if($_GET['action'] == 'list_sms_template'){ 
        $finalAry = array();
        $sms_temp =$db->get_results("SELECT s.id, st.type_name, s.status, s.sms_text, s.sms_template_id, (CASE WHEN s.status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active FROM ss_sms_templates s INNER JOIN ss_sms_template_types st ON s.sms_template_id = st.id where st.status = 1  ORDER BY id DESC",ARRAY_A);
        for($i=0; $i<count((array)$sms_temp); $i++){
            $sms_temp[$i]['sms_text'] = $sms_temp[$i]['sms_text'];
        }
        $finalAry['data'] = $sms_temp;
        echo json_encode($finalAry);
        exit;
}
elseif($_POST['action'] == 'message_template_add'){
    $sms_template_id = $_POST['sms_template_id'];
    $sms_text = $_POST['sms_text'];
    $message_template_type = $_POST['message_template_type'];
    if (!empty($message_template_type)) {
      $sms_temp_type = $db->query("insert into ss_sms_template_types set type_name='".$message_template_type."',  status = 1");
      $sms_temp_type_id = $db->insert_id;
        if ($sms_temp_type_id > 0) {
            //$db->query("update ss_sms_templates set status = 0,  updated_on='".date('Y-m-d H:i:s')."' where sms_template_id = '".$sms_template_id."'");
            $sms_temp = $db->query("insert into ss_sms_templates set sms_template_id='".$sms_temp_type_id."', sms_text='".$sms_text."', status = '".$_POST['status']."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."'");
            $sms_temp_id = $db->insert_id;
        } 
    }         
    if($sms_temp_id > 0){
        echo json_encode(array('code' => "1",'msg' => "<div class='text-success'>Message Template Added Successfully</div>"));
        exit;
    }else{
        $return_resp = array('code' => "0",'msg' => '<div class="text-danger">Message Template Not Added</div>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
elseif($_POST['action'] == 'message_template_edit'){
    $temp_sms_id = $_POST['msg_temp_id'];
    $sms_text = $_POST['sms_text'];
    $message_template_type = $_POST['message_template_type'];
    $get_temp_type = $db->get_var("SELECT sms.sms_template_id FROM ss_sms_template_types t INNER JOIN ss_sms_templates sms ON sms.sms_template_id = t.id WHERE sms.id = '".$temp_sms_id."'");
      if (!empty($get_temp_type)) {
        $sms_temp_type = $db->query("update ss_sms_template_types set type_name='".$message_template_type."',  status = 1 where id ='".$get_temp_type."'");
        
      } 
      $edit_sms_template = $db->query("update ss_sms_templates set  sms_text='".$sms_text."',  status='".$_POST['status']."', updated_by_user_id='".$_SESSION['icksumm_uat_login_userid']."', updated_on='".date('Y-m-d H:i:s')."' where id = '".$temp_sms_id."'");
        
    if($edit_sms_template){
       
        echo json_encode(array('code' => "1",'msg' => "<div class='text-success'>Message Template Updated Successfully</div>"));
        exit;
    }else{
        $return_resp = array('code' => "0",'msg' => '<div class="text-danger" Message Template Not Updated </div>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
elseif($_POST['action'] == 'delete_message_template'){
   if(isset($_POST['id'])){
       $rec = $db->query("DELETE FROM ss_sms_templates where id='".$_POST['id']."'");
       if($rec > 0){
           echo json_encode(array('code' => "1",'msg' => '<div class="text-success">Message Template Deleted successfully</div>'));
           exit;
       }else{
           $return_resp = array('code' => "0",'msg' => '<div class="text-danger" Message Template Not Deleted </div>');
           CreateLog($_REQUEST, json_encode($return_resp));
           echo json_encode($return_resp);
           exit;
       }
   }else{
       $return_resp = array('code' => "0",'msg' => '<div class="text-danger" Error: Process failed</div>');
       CreateLog($_REQUEST, json_encode($return_resp));
       echo json_encode($return_resp);
       exit;
   }
}
