<?php
include_once "../includes/config.php";
$mob_title = "Mass Email";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01') && !check_userrole_by_code('UT02') && !check_userrole_by_code('UT04')) {
  include "../includes/unauthorized_msg.php";
  return;
}

$results = $db->get_results("SELECT etype.id AS email_template_type_id, etype.type_name, etype.system_template, etemp.id, etemp.email_template, etemp.email_subject, etemp.email_cc, etemp.email_bcc FROM ss_email_templates etemp INNER JOIN ss_email_template_types etype ON etype.id = etemp.email_template_type_id WHERE etemp.status = 1 and etype.system_template = 0 ");

function reArrayFiles(&$file_post)
{
  $file_ary = array();
  $file_count = count((array)$file_post['name']);
  $file_keys = array_keys($file_post);

  for ($i = 0; $i < $file_count; $i++) {
    foreach ($file_keys as $key) {
      $file_ary[$i][$key] = $file_post[$key][$i];
    }
  }

  return $file_ary;
}

if ($_POST['action'] == 'save_mass_email_to_queue') {
  //ADDED ON 14-MAY-2018
  ini_set('max_execution_time', 300); //300 seconds = 5 minutes
  ini_set('memory_limit', '1024M');

  $db->query('BEGIN');
  //var_dump($_FILES);
   $group = $_POST['group'];
   $class = $_POST['class'];
   $student = $_POST['student'];
   $selection_method = $_POST['selection_method'];



  $cc_emails = explode(',', $_POST['cc']);
  $bcc_emails = explode(',', $_POST['bcc']);
  $subject = $db->escape($_POST['subject']);
  $text_msg = $_POST['message'];

  //$attachmentfiles = array();
  //$attachmentfiles = $_POST['attachmentfile'];

  if($selection_method == 'Single'){
   echo "single";die;

  if($group == 'all_groups' && $class == 'all_subjects' && $student == 'all_students'){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
 
    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0  order by s.first_name,s.last_name");

     $group_information = "All Groups"; 
     $subjects_information = "All Subjects";

  }elseif(is_numeric($group) && $class == 'all_subjects' && $student == 'all_students'){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id = '".$group."') order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '".$group."' order by s.first_name,s.last_name");

   $group_information = $db->get_var("select group_name from ss_groups where id = '".$group."'"); 
     $subjects_information = "All Subjects";

    }elseif($group == 'all_groups' && is_numeric($class) && $student == 'all_students'){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND class_id='".$class."') order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.class_id='".$class."' order by s.first_name,s.last_name");
     
     $group_information = "All Groups";  
     $subjects_information = $db->get_var("select class_name from ss_classes where id = '".$class."'");

   }elseif(is_numeric($group) && is_numeric($class) && $student == 'all_students'){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id = '".$group."' and class_id='".$class."') order by s.first_name,s.last_name)");

     $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '".$group."' and m.class_id='".$class."' order by s.first_name,s.last_name");


     $group_information = $db->get_var("select group_name from ss_groups where id = '".$group."'");
     $subjects_information = $db->get_var("select class_name from ss_classes where id = '".$class."'");

     }elseif(is_numeric($group) && $class == 'all_subjects' && is_numeric($student)){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id = '".$group."' and student_user_id='".$student."') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '".$group."' and m.student_user_id='".$student."' order by s.first_name,s.last_name");


    $group_information = $db->get_var("select group_name from ss_groups where id = '".$group."'");
    $subjects_information = "All Subjects";


     }elseif($group == 'all_groups' && is_numeric($class) && is_numeric($student)){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND class_id='".$class."' and student_user_id='".$student."') order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.class_id='".$class."' and m.student_user_id='".$student."' order by s.first_name,s.last_name");
     
     $group_information = "All Groups";
     $subjects_information = $db->get_var("select class_name from ss_classes where id = '".$class."'");

   }elseif(is_numeric($group) && is_numeric($class) && is_numeric($student)){

    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id = '".$group."' and class_id='".$class."' and student_user_id='".$student."') order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '".$group."' and m.class_id='".$class."' and m.student_user_id='".$student."' order by s.first_name,s.last_name");

     $group_information = $db->get_var("select group_name from ss_groups where id = '".$group."'");
     $subjects_information = $db->get_var("select class_name from ss_classes where id = '".$class."'");

    }elseif(is_numeric($student)){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."')");

     $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '".$student."'");
    }
    }
    else
    {

      $groupm = implode(',', $_POST['group']);
      $classm = implode(',', $_POST['class']);
      $studentm =implode(',', $_POST['student']);
      $group = $_POST['group'];
      $class = $_POST['class'];
      $student = $_POST['student'];

      if(in_array('all_groups', $group) && in_array('all_subjects', $class) && in_array('all_students', $student)) {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");
 
      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0  order by s.first_name,s.last_name");

      $group_information = "All Groups"; 
      $subjects_information = "All Subjects";

    }elseif(!in_array('all_groups', $group) && in_array('all_subjects', $class) && in_array('all_students', $student)){

    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id IN ('".$groupm."')) order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id  IN ('".$groupm."') order by s.first_name,s.last_name");

     $group_information = $db->get_results("select group_name from ss_groups where id IN ('".$groupm."')",ARRAY_A);
      $grp = [];
      foreach($group_information as $ee){
      $grp[] =$ee['group_name'];
      }
      $group_information = implode(',', $grp);
     $subjects_information = "All Subjects";

    }elseif(in_array('all_groups', $group) && !in_array('all_subjects', $class)  && in_array('all_students',$student)){ 
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND class_id IN ('".$classm."')) order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.class_id IN ('".$classm."') order by s.first_name,s.last_name");
     
     $group_information = "All Groups";  
     $subjects_information = $db->get_results("select class_name from ss_classes where id IN ('".$classm."')",ARRAY_A);
        $subb = [];
      foreach($subjects_information as $vv){
      $subb[] =$vv['class_name'];
      }

     $subjects_information =  implode(',',$subb);
    

   }elseif(!in_array('all_groups', $group)  && !in_array('all_subjects', $class)  && in_array('all_students',$student)){
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id IN ('".$groupm."') and class_id IN ('".$classm."')) order by s.first_name,s.last_name)");

     $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id IN ('".$groupm."') and m.class_idIN ('".$classm."') order by s.first_name,s.last_name");


     
     $group_information = $db->get_results("select group_name from ss_groups where id IN ('".$groupm."')",ARRAY_A);
     $subjects_information = $db->get_results("select class_name from ss_classes where id IN ('".$classm."')",ARRAY_A);
       $grp = [];
      foreach($group_information as $ee){
      $grp[] =$ee['group_name'];
      }
       $group_information = implode(',', $grp);
         $subb = [];
      foreach($subjects_information as $vv){
      $subb[] =$vv['class_name'];
      }

     $subjects_information =  implode(',',$subb);
    
   

     }elseif(!in_array('all_groups', $group) && in_array('all_subjects', $class) && !in_array('all_students',$student)){ 
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id IN ('".$groupm."') and student_user_id IN ('".$studentm."')) order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id IN ('".$groupm."') and m.student_user_id IN ('".$studentm."') order by s.first_name,s.last_name");


     $group_information = $db->get_results("select group_name from ss_groups where id IN ('".$groupm."')",ARRAY_A);
      $grp = [];
      foreach($group_information as $ee){
      $grp[] =$ee['group_name'];
      }
       $group_information = implode(',', $grp);
       $subjects_information = "All Subjects";


     }elseif(in_array('all_groups', $group) && !in_array('all_subjects', $class) && !in_array('all_students',$student)){ 
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND class_id IN ('".$classm."') and student_user_id IN ('".$studentm."')) order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.class_id IN ('".$classm."') and m.student_user_id IN ('".$studentm."') order by s.first_name,s.last_name");
     
     $group_information = "All Groups";
     $subjects_information = $db->get_results("select class_name from ss_classes where id IN ('".$classm."')",ARRAY_A);
      $subb = [];
      foreach($subjects_information as $vv){
      $subb[] =$vv['class_name'];
      }

     $subjects_information =  implode(',',$subb);
   }elseif(!in_array('all_groups', $group)  && !in_array('all_subjects', $class) && !in_array('all_students',$student)){ 

    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap 
    WHERE latest = 1 AND group_id IN ('".$groupm."') and class_id IN ('".$classm."')' and student_user_id IN ('".$studentm."')) order by s.first_name,s.last_name)");

    $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id IN ('".$groupm."') and m.class_id IN ('".$classm."') and m.student_user_id IN ('".$studentm."') order by s.first_name,s.last_name");
   
     $group_information = $db->get_results("select group_name from ss_groups where id IN ('".$groupm."')",ARRAY_A);
     $subjects_information = $db->get_results("select class_name from ss_classes where id IN (".$classm.")",ARRAY_A);
      $grp = [];
      foreach($group_information as $ee){
      $grp[] =$ee['group_name'];
      }
      $subb = [];
      foreach($subjects_information as $vv){
      $subb[] =$vv['class_name'];
      }

      $group_information = implode(',', $grp);
      $subjects_information =  implode(',',$subb);

    }elseif(!in_array('all_students',$student)){ 
    $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN ('".$studentm."')");

     $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u 
    ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN ('".$studentm."')");
    }
    }

  
    $emailStatus = false;

  $group_information = "Group  <strong>".$group_information."</strong>";
  $subjects_information = "Class <strong>".$subjects_information."</strong>";
 
  $message = "<br>".$group_information." <br>".$subjects_information." <br><br>".$text_msg;

/*  echo $message;
  die;
*/
  //STOP REPEATED CLICK ENTRY
  $last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");

  if ($last_msg_time_diff > 4 || $last_msg_time_diff == "") {
    $sql_bulk_msg = "insert into ss_bulk_message set subject = '" . $subject . "', message = '" . $message . "', is_report_gen = 0, 
    created_on = '" . date('Y-m-d H:i:s') . "', session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";

    if ($db->query($sql_bulk_msg)) {
      $message_id = $db->insert_id;

      foreach ($families as $fam) {
        if (trim($fam->primary_email) != '') {
          $to_primary = $fam->primary_email;
          $family_id =  $fam->id;
          //$to_primary = 'moh.urooj@gmail.com';
     
          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', family_id = '" . $family_id . "', receiver_email = '" . $to_primary . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }

        if (trim($fam->secondary_email) != '') {
          $to_secondary = $fam->secondary_email; 
          $family_id =  $fam->id;
          //$to_secondary = 'moh.urooj@gmail.com';

          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', family_id = '" . $family_id . "', receiver_email = '" . $to_secondary . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
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
        //$bcc = 'moh.urooj@gmail.com';

        if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {

          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($bcc) . "', is_cc=0, 
          is_bcc=1, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }
      }

      ///////////////////

      $file_ary = reArrayFiles($_FILES['attachmentfile']);

      foreach ($file_ary as $file) {
        $fileName = $file['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $filenameWOExt = pathinfo($fileName, PATHINFO_FILENAME);
        $filenameWOExt = str_replace(' ', '-', $filenameWOExt);
        $newFileName = $filenameWOExt . "-" . $message_id . "." . $fileExtension;

        $uploadFileDir = 'attachments/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $dest_path)) {
          if ($db->query("insert into ss_bulk_message_attachment set bulk_message_id='" . $message_id . "', attachment_file='" . $newFileName . "'")) {
            $emailStatus = true;
          } else {
            $emailStatus = false;
          }
        }
      }

      /////////////////////

      // foreach ($attachmentfiles as $attach) { 
      //   //$uploadFileDir = './uploaded_files/';
      //   //$dest_path = $uploadFileDir . $newFileName;

      //   // if(move_uploaded_file($fileTmpPath, $dest_path))
      //   // {
      //   //   $message ='File is successfully uploaded.';
      //   // }
      //   // else
      //   // {
      //   //   $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
      //   // }

      //   //$attach = date('ymdHi').'-'.$attach;
      //   if ($db->query("insert into ss_bulk_message_attachment set bulk_message_id='" . $message_id . "', attachment_file='" . $attach . "'")) {
      //     $emailStatus = true; 
      //   } else {
      //     $emailStatus = false; 
      //   }
      // }

      if ($emailStatus && $db->query('COMMIT') !== false) {
        $msg = 'Email(s) queue created successfully';
        $code = 1;
      } else {
        $db->query('ROLLBACK');
        $msg = "Email(s) queue not created. Please try again.";
        $code = 0;
      }
    } else {
      $db->query('ROLLBACK');
      $msg = "Email(s) queue not created. Please try again.";
      $code = 0;
    }
  } else {
    $msg = 'Email(s) queue created successfully';
    $code = 1;
  }
}
//echo $msg;
?>
<style>
  span.file_name_size {
    display: inline-block;
    width: 30%;
  }

  span.prog {
    display: inline-block;
    width: 10%;
  }

  a.remove_file {
    width: 10%;
  }

  #filelist {
    margin-bottom: 10px;
  }
</style>
<!-- <script type="text/javascript" src="plupload_js/plupload.full.min.js"></script> -->
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Mass Email</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Mass Email</li>
    </ul>
  </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
  <form id="frmICK" class="form-validate-jquery" method="post" enctype="multipart/form-data">
    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
        <?php if ($code === 1) { ?>
        <div class="alert alert-success"><?php echo $msg ?></div>
        <?php } elseif ($code === 0) { ?>
        <div class="alert alert-danger"><?php echo $msg ?></div>
        <?php } ?>
        <div class="row">
           <div class="col-md-12">
            <div class="form-group">
              <label>Select For</label>
              <label><input type="radio" name="selection_method" value="Single" checked> Single</label>
              <label><input type="radio" name="selection_method" value="Multiple"> Multiple</label>
            </div>
          </div>
          <!-- <div class="col-md-6">
            <div class="form-group">
              <label>Teacher</label>
              <?php
              $staffTypeId = $db->get_var("select id from ss_usertype where user_type_code = 'UT02'"); //UT02 - teachers code
              $teachers = $db->get_results("SELECT u.id, s.first_name, s.middle_name, s.last_name FROM ss_user u 
          INNER JOIN ss_staff s ON u.id = s.user_id WHERE u.is_active = 1 AND u.is_deleted = 0 and u.user_type_id = '" . $staffTypeId . "' 
        ORDER BY s.first_name ASC, s.middle_name ASC, s.last_name ASC ") ?>
              <select class="bootstrap-select" multiple="multiple" data-width="100%" id="teacher" name="teacher" required>
                <?php foreach ($teachers as $tea) {
                  if ($tea->id != $_SESSION['icksumm_uat_login_userid']) {
                    ?>
                <option value="<?php echo $tea->id ?>" ><?php echo $tea->first_name . ' ' . $tea->last_name ?></option>
                <?php }
                } ?>
              </select>
            </div>
          </div> -->
          <div id ="forsingle">
          <div class="col-md-4">
            <div class="form-group">
              <label>Group</label>
              <?php $groups = $db->get_results("SELECT * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active = 1 and is_deleted = 0 order by group_name asc"); ?>
              <select class="bootstrap-select" data-width="100%" id="group" name="group" >
                <option value="">Select</option>
                <option value="all_groups">All Groups</option>
                <?php foreach ($groups as $gr) { ?>
                <option value="<?php echo $gr->id ?>"><?php echo $gr->group_name ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
                <label for="group">Subject</label>
                <select class="form-control" name="class" id="classes" >
                <option value="">Select</option>                                    
                </select>
            </div>
        </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Parents Of</label>
              <select class="bootstrap-select" data-width="100%" id="student" name="student" >
                <option value="">Select</option>
              </select>
            </div>
          </div>
        </div>
         <div id ="formultiple" style="display: none;" >
            <div class="col-md-4">
              <div class="form-group">
                <label>Group</label>
                <?php $groups = $db->get_results("SELECT * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active = 1 and is_deleted = 0 order by group_name asc"); ?>
                <select class="bootstrap-select " data-width="100%" id="groupm" name="group[]"  multiple="true" >
                  <option value="">Select</option>
                  <option value="all_groups">All Groups</option>
                  <?php foreach ($groups as $gr) { ?>
                  <option value="<?php echo $gr->id ?>"><?php echo $gr->group_name ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>

          <div class="col-md-4">
              <div class="form-group">
                  <label for="group">Subject</label>
                  <select class="bootstrap-select" data-width="100%" name="class[]" id="classesm" multiple="true" >
                   <!--  <option value="">Select Subject</option>    -->                                  
                  </select>
              </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Parents Of</label>
              <select class="bootstrap-select" data-width="100%" id="studentm" name="student" multiple="true" >
                <option value="">Select</option>
               <!--  <option value="0">All Students</option> -->
             
              </select>
            </div>
          </div>
         </div>
        </div>
        <div class="row">
           <div class="col-md-3">
            <div class="form-group">
              <label>Email Template Title</label>
              <select name="email_template_title" class="form-control email_template_title">
                <option value="">Select</option>
                <?php foreach($results as $row){ ?>
                <option value="<?= $row->email_template_type_id ?>"><?= $row->type_name ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group" style="margin-top: 30px;">
              <span class="datacontent"></span>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>CC</label>
              <input type="text" class="form-control email_cc" id="cc" name="cc" value="" emailCommaSep="true" />
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>BCC</label>
              <input type="text" class="form-control email_bcc" id="bcc" name="bcc" emailCommaSep="true" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Subject</label>
              <input type="text" class="form-control required email_subject" id="subject" name="subject" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message</label>
              <textarea class="form-control required messagecontent" id="message" name="message" style="height:200px"></textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group" id="attach_box">
              <label>Attachment</label>
              <div class="row">
                <div class="col-md-8">
                  <input type="file" name="attachmentfile[]">
                </div>
                <div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment">remove</a></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <a href="javascript:void(0)" id="add_more_attachments"><i class="icon-plus2"></i> Add More Attachment</a>
                </div>
              </div>
            </div>
            <div class="row mt-30">
              <div class="col-md-12">
                <div class="form-group">
                  <input type="hidden" name="action" value="save_mass_email_to_queue">
                  <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">

          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<!-- /Content area -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
  $(document).ready(function(e) {
    $('#message').summernote();
    //REMOVE UPLOADED FILE
    $(document).on('click', '.remove_attachment', function() {
      $(this).parent().parent().remove();
    });

    //ADD NEW ATTACHMENT
    $("#add_more_attachments").click(function() {
      $('#attach_box').append('<div class="row mt-10"><div class="col-md-8"><input type="file" name="attachmentfile[]"></div><div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment">remove</a></div></div>');
    });


    $('#group').change(function(){
        $('#group_id').val($(this).val()); 

    if($('#group').val() == ''){
            $('#classes').html('<option value="">Select</option>');
    }else{
            //SUBJECT
            $('#classes').html('<option value="">Loading...</option>');
            
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
            $.post(targetUrl,{group_id:$('#group').val(),action:'fetch_assigned_group_class_for_select'},function(data,status){
                if(status == 'success' && data != ''){
                    $('#classes').html('<option value="">Select Subject</option>');
                    $('#classes').append('<option value="all_subjects">All Subjects</option>');
                    $('#classes').append(data);
                }else{
                    $('#classes').html('<option value="">Subject not found</option>');
                }
            });

    }
  });

    $('#groupm').change(function(){

      //$('.bootstrap-select').trigger('change');
        $('#group_id').val($(this).val());  
        if($('#groupm').val() == ''){
        $('#classesm').append('<option value="">Select</option>');
        }else{
    //  $('#classes').append('<option value="">Loading...</option>').trigger('change');
       var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
       $.post(targetUrl,{group_id:$('#group').val(),action:'fetch_assigned_multiple_group_class_for_select'},function(data,status){
       if(status == 'success' && data != ''){
         data +='<option value="all_subjects">All Subjects</option>';
        $('#classesm').html(data);
        $("#classesm").selectpicker('destroy');
        $("#classesm").selectpicker();
      
       }else{
      $('#classesm').selectpicker('refresh');
         // $('#classes').select2('<option value="">Subject not found</option>');
       }
         //$('#classes').select2().trigger('change');
        });
        }
  });
 $('#classesm').change(function(){
    // $('#classes').val($(this).val()); 

        if($('#classesm').val() == ''){
            $('#studentm').html('<option value="">Select</option>');
        }else{
            
            //STUDENT
            $('#studentm').html('<option value="">Loading...</option>');
            $('#studentm').selectpicker('refresh');
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';

            $.post(targetUrl,{group_id:$('#groupm').val(),class_id:$('#classesm').val(),action:'get_students_of_group_for_select'},function(data,status){
                if(status == 'success' && data.code == 1){
                    // $('#student').html('<option value="">Select</option>');
                    // $('#student').append('<option value="all_students">All Students</option>');
                    // $('#student').append(data.optionVal);
                    data.optionVal +='<option value="all_students">All Students</option>'

                    $('#studentm').html(data.optionVal);
                    $("#studentm").selectpicker('destroy');
                    $("#studentm").selectpicker();
                }else{
                    $('#studentm').html('<option value="">Select</option>');
                }
                $('#studentm').selectpicker('refresh');
            },'json');
        }
    });
  //COMMENTED ON 27-AUG-2021 BY UROOJ - ASK BEFORE UNCOMMENTING IT
  // $('#group').change(function(){
  // $('#group_id').val($(this).val()); 

  //   if($('#group').val() == ''){
  //           $('#classes').html('<option value="">Select</option>');
  //   }else{
  //           //SUBJECT
  //           $('#classes').html('<option value="">Loading...</option>');
            
  //           var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
  //           $.post(targetUrl,{group_id:$('#email_template_title').val(),action:'fetch_assigned_group_class_for_select'},function(data,status){
  //               if(status == 'success' && data != ''){
  //                   $('#classes').html('<option value="">Select Subject</option>');
  //                   $('#classes').append('<option value="all_subjects">All Subjects</option>');
  //                   $('#classes').append(data);
  //               }else{
  //                   $('#classes').html('<option value="">Subject not found</option>');
  //               }
  //           });

  //   }
  // });

   $('.email_template_title').change(function() {
        var id = $('.email_template_title').val();
        $('.datacontent').html('Processing...');
        if(id.length == 0){
          $('.note-editable').html("");
          $('.email_cc').val("");
          $('.email_bcc').val("");
          $('.email_subject').val("");
          $('.datacontent').html('');
        }else{
         
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
        $.post(targetUrl, {
          id: id,
          action: 'get_email_template_data'
        }, function(data, status) {
          if (status == 'success' && data.code == 1) {
            $('.datacontent').html('');
            $('.email_cc').val(data.inputVal.email_cc);
            $('.email_bcc').val(data.inputVal.email_bcc);
            $('.email_subject').val(data.inputVal.email_subject);
            //$('.messagecontent').val(data.inputVal.email_template);
            $(".messagecontent").summernote("code", data.inputVal.email_template);
          } else {
            $('.email_cc').val();
            $('.email_bcc').val();
            $('.email_subject').val();
          }
        }, 'json');
      }
    });


    $('#classes').change(function(){
    $('#classes').val($(this).val()); 

        if($('#classes').val() == ''){
            $('#student').html('<option value="">Select</option>');
        }else{
            
            //STUDENT
            $('#student').html('<option value="">Loading...</option>');
            $('#student').selectpicker('refresh');
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
            $.post(targetUrl,{group_id:$('#group').val(),class_id:$('#classes').val(),action:'get_students_of_group_for_select'},function(data,status){
                if(status == 'success' && data.code == 1){
                    $('#student').html('<option value="">Select</option>');
                    $('#student').append('<option value="all_students">All Students</option>');
                    $('#student').append(data.optionVal);
                }else{
                    $('#student').html('<option value="">Select</option>');
                }
                $('#student').selectpicker('refresh');
            },'json');
        }
    });


/*    $('#group').change(function() {
      $('#student').html('<option value="">Loading...</option>');
      $('#student').selectpicker('refresh');

      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
      $.post(targetUrl, {
        group_id: $('#group').val(),
        action: 'get_students_of_group_for_select'
      }, function(data, status) {
        if (status == 'success' && data.code == 1) {
          $('#student').html('<option value="">Select</option>');
          $('#student').append('<option value="all_students">All Students</option>');
          $('#student').append(data.optionVal);
        } else {
          $('#student').html('<option value="">Select</option>');
        }
        $('#student').selectpicker('refresh');
      }, 'json');
    });*/


    $('.btn.dropdown-toggle').click(function() {
      var id = $(this).data('id');
      $('#' + id + '-error').css('display', 'none');
    });

    $('#frmICK').submit(function(e) {
      if ($('#frmICK').valid()) {
        $('.spinner').removeClass('hide');
        return true;
      } else {
        return false;
      }
    });
  });
$('input[name=selection_method]').change(function(){
var selection_method = $(this).val();

 if(selection_method == 'Multiple'){
   $('#formultiple').css('display','contents');
   $('#forsingle').css('display','none');
   $('#formultiple select').attr("required", "true");
  // $('#forsingle select').attr("required", "false");
 }
 else{  
  $('#formultiple').css('display','none');
  $('#forsingle').css('display','contents');
  $('#forsingle select').attr("required", "true");
   //$('#formultiple select').attr("required", "false");
 }
})
</script>
<?php include "../footer.php" ?>