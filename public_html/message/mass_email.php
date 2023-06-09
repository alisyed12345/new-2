<?php
include_once "../includes/config.php";
$mob_title = "Mass Email";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01') && !check_userrole_by_code('UT02') && !check_userrole_by_code('UT04')) {
  include "../includes/unauthorized_msg.php";
  return;
}


/* function reArrayFiles(&$file_post)
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
} */
/* if ($_POST['action'] == 'save_mass_email_to_queue') {
  //ADDED ON 14-MAY-2018
  ini_set('max_execution_time', 300); //300 seconds = 5 minutes
  ini_set('memory_limit', '1024M');

  $db->query('BEGIN');
  //var_dump($_FILES);

  $message_to = $_POST['message_to']; //Group /Registered Parents, Pending Parents, Registered Staff, Pending Staff
  $cc_emails = explode(',', $_POST['cc']);
  $bcc_emails = explode(',', $_POST['bcc']);
  $subject = $db->escape($_POST['subject']);
  $text_msg = $db->escape($_POST['message']);

  //$attachmentfiles = array();
  //$attachmentfiles = $_POST['attachmentfile'];
  if ($message_to == 'registered_student') { //Group /Registered Parents 
    $group = $_POST['group'];
    $class = $_POST['class'];
    $student = $_POST['student'];
    if ($group == 'all_groups' && $class == 'all_subjects' && $student == 'all_students') {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0  order by s.first_name,s.last_name");

      $group_information = "All Groups";
      $subjects_information = "All Subjects";
    } elseif (is_numeric($group) && $class == 'all_subjects' && $student == 'all_students') {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '" . $group . "' order by s.first_name,s.last_name");

      $group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
      $subjects_information = "All Subjects";
    } elseif ($group == 'all_groups' && is_numeric($class) && $student == 'all_students') {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND class_id='" . $class . "') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 and m.class_id='" . $class . "' order by s.first_name,s.last_name");

      $group_information = "All Groups";
      $subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
    } elseif (is_numeric($group) && is_numeric($class) && $student == 'all_students') {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "' and class_id='" . $class . "') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '" . $group . "' and m.class_id='" . $class . "' order by s.first_name,s.last_name");


      $group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
      $subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
    } elseif (is_numeric($group) && $class == 'all_subjects' && is_numeric($student)) {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "' and student_user_id='" . $student . "') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '" . $group . "' and m.student_user_id='" . $student . "' order by s.first_name,s.last_name");


      $group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
      $subjects_information = "All Subjects";
    } elseif ($group == 'all_groups' && is_numeric($class) && is_numeric($student)) {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND class_id='" . $class . "' and student_user_id='" . $student . "') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 and m.class_id='" . $class . "' and m.student_user_id='" . $student . "' order by s.first_name,s.last_name");

      $group_information = "All Groups";
      $subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
    } elseif (is_numeric($group) && is_numeric($class) && is_numeric($student)) {

      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1 AND group_id = '" . $group . "' and class_id='" . $class . "' and student_user_id='" . $student . "') order by s.first_name,s.last_name)");

      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  INNER JOIN ss_studentgroupmap m ON m.student_user_id = s.user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 and m.group_id = '" . $group . "' and m.class_id='" . $class . "' and m.student_user_id='" . $student . "' order by s.first_name,s.last_name");

      $group_information = $db->get_var("select group_name from ss_groups where id = '" . $group . "'");
      $subjects_information = $db->get_var("select class_name from ss_classes where id = '" . $class . "'");
    } elseif (is_numeric($student)) {
      $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '" . $student . "')");
      $template_data = $db->get_row("select * from ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id = '" . $student . "'");
    }
  } elseif ($message_to == 'pending_student') { //Pending Parents 
    $pending_student = $_POST['pending_student'];
    if ($pending_student == 'all_pending_students') {
      $conditions = "";
    } else {
      $conditions = " and `reg_child`.`id`='" . $pending_student . "' ";
    }
    $families = $db->get_results("SELECT `school`.`primary_contact`,`school`.`primary_email`,`school`.`secondary_email` FROM `ss_sunday_sch_req_child` `reg_child` INNER JOIN `ss_sunday_school_reg` `school` ON `reg_child`.`sunday_school_reg_id`=`school`.`id` WHERE `reg_child`.`is_executed` = 0 and `school`.`session` = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' " . $conditions . " group by `school`.`id`");
  } elseif ($message_to == 'registered_staff') { //Registered Staff 
    $registered_staff = $_POST['registered_staff'];
    if ($registered_staff == 'all_registered_staff') {
      $conditions = "";
    } else {
      $conditions = " and `s`.`user_id`='" . $registered_staff . "' ";
    }
    $families = $db->get_results("SELECT s.user_id, CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS staff_name,u.email as primary_email FROM ss_user u INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_staff_session_map ssm on u.id = ssm.staff_user_id WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND status = 1 " . $conditions . " GROUP BY u.email ");
  } elseif ($message_to == 'pending_staff') { //Pending Staff
    $pending_staff = $_POST['pending_staff'];
    if ($pending_staff == 'all_pending_staff') {
      $conditions = "";
    } else {
      $conditions = " and `r`.`id`='" . $pending_staff . "' ";
    }
    $families = $db->get_results("SELECT distinct r.id, CONCAT(r.first_name,' ',COALESCE(r.middle_name,''),' ',COALESCE(r.last_name,'')) AS staff_name,r.email as primary_email FROM ss_staff_registration r LEFT JOIN ss_user u ON r.email = u.email WHERE r.is_request = 0 AND r.is_processed = 0 AND r.session ='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' " . $conditions . " ");
  }


  $emailStatus = false;
  if ($message_to == 'registered_student') {
    $group_information = "Group  <strong>" . $group_information . "</strong>";
    $subjects_information = "Class <strong>" . $subjects_information . "</strong>";

    $message = "<br>" . $group_information . " <br>" . $subjects_information . " <br><br>" . $text_msg;
  } else {

    $message =  $text_msg;
  }




  //STOP REPEATED CLICK ENTRY
  $last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");

  if ($last_msg_time_diff > 4 || $last_msg_time_diff == "") {
    $sql_bulk_msg = "insert into ss_bulk_message set subject = '" . $db->escape($subject) . "', message = '" . $db->escape($message) . "', is_report_gen = 0, created_on = '" . date('Y-m-d H:i:s') . "', session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";
    if ($db->query($sql_bulk_msg)) {
      $message_id = $db->insert_id;
   
      foreach ($families as $fam) {
        if (trim($fam->primary_email) != '') {
          $to_primary = $fam->primary_email;
          //$to_primary = 'moh.urooj@gmail.com';
          $family = '';
          if ($message_to == 'registered_student') {
            $family = $family_id . '=' . $fam->id . ',';
          }
          
          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', " . $family . " receiver_email = '" . $to_primary . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }

     if (trim($fam->secondary_email) != '') {
          $to_secondary = $fam->secondary_email;
          //$to_secondary = 'moh.urooj@gmail.com';
          $family = '';
          if ($message_to == 'registered_student') {
            $family = $family_id . '=' . $fam->id . ',';
          }
          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "',  " . $family . ", receiver_email = '" . $to_secondary . "', is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }  
      }

      foreach ($cc_emails as $cc) {
        if (filter_var(trim($cc), FILTER_VALIDATE_EMAIL)) {

          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($cc) . "', is_cc=1, is_bcc=0, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }
      }

      foreach ($bcc_emails as $bcc) {
        //$bcc = 'moh.urooj@gmail.com';
        if (filter_var(trim($bcc), FILTER_VALIDATE_EMAIL)) {
          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($bcc) . "', is_cc=0, is_bcc=1, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }
      }

      //---------------------------Attachment-----------------------------//
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
      //---------------------------Attachment-----------------------------//

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
} */
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
      <h4>Send New Mass Email</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL . "message/mass_email_history" ?>">Mass Email History</a></li>
      <li class="active">Send New Mass Email</li>
    </ul>
  </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
  <form id="frmICK" class="form-validate-jquery" method="post" enctype="multipart/form-data">
    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
      <div class="ajaxMsg"></div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message To</label>
              <br />
              <label class="radio-inline">
                <input type="radio" name="message_to" class="styled recipient" value="registered_student" checked="checked">Group /Registered Parents </label>
              <?php if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01') { ?>
              <label class="radio-inline">
                <input type="radio" name="message_to" class="styled recipient" value="pending_student">Pending Parents</label><?php } ?>
              <label class="radio-inline">
                <input type="radio" name="message_to" class="styled recipient" value="registered_staff">Registered Staff </label>
              <?php if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01') { ?>
              <label class="radio-inline">
                <input type="radio" name="message_to" class="styled recipient" value="pending_staff">Pending Staff </label><?php } ?>

            </div>
          </div>
        </div>
        <div class="row " id="registered_student_row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Group<span class="mandatory">*</span></label>
              <?php
              if (check_userrole_by_code('UT02')) {
                $Querygroup = "SELECT grp.id,grp.group_name from ss_groups grp 
             INNER JOIN ss_classtime clt ON clt.group_id=grp.id
             INNER JOIN ss_staffclasstimemap scltm ON scltm.classtime_id=clt.id
             where scltm.staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and grp.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and grp.is_active = 1 and grp.is_deleted = 0 and scltm.active='1' GROUP by grp.id order by grp.group_name asc";
              } else {
                $Querygroup = "SELECT id,group_name from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND is_active = 1 and is_deleted = 0 order by group_name asc";
              }
              $groups = $db->get_results($Querygroup);

              ?>
              <select class="bootstrap-select required" data-width="100%" id="group" name="group">
                <option value="">Select</option>
                <?php if(isset($groups)){ ?>
                   <option value="all_groups">All Groups</option>
                 <?php foreach ($groups as $gr) { ?>
                  <option value="<?php echo $gr->id ?>"><?php echo $gr->group_name ?></option>
                <?php } } ?>
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label for="group">Class<span class="mandatory">*</span></label>
              <select class="form-control required" name="class" id="classes">
                <option value="">Select</option>
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Parents Of<span class="mandatory">*</span></label>
              <select class="bootstrap-select required" data-width="100%" id="student" name="student">
              </select>
            </div>
          </div>
        </div>

        <div class="row hide" id="pending_student_row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Parents Of<span class="mandatory">*</span></label>
              <select class="form-control" data-width="100%" id="pending_student" name="pending_student[]"  multiple="multiple">
                <option value="">Select</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row hide" id="registered_staff_row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Registered Staff<span class="mandatory">*</span></label>
              <select class="bootstrap-select" data-width="100%" id="registered_staff" name="registered_staff">
                <option value="">Select</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row hide" id="pending_staff_row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Pending Staff<span class="mandatory">*</span></label>
              <select class="bootstrap-select" data-width="100%" id="pending_staff" name="pending_staff">
                <option value="" >Select</option>
              </select>
            </div>
          </div>
        </div>



        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Email Template Title</label>
              <select name="email_template_title" class="form-control email_template_title">
                <option value="">Select</option>
                <?php

                $results = $db->get_results("SELECT etype.id AS email_template_type_id, etype.type_name, etype.system_template, etemp.id, etemp.email_template, etemp.email_subject, etemp.email_cc, etemp.email_bcc FROM ss_email_templates etemp INNER JOIN ss_email_template_types etype ON etype.id = etemp.email_template_type_id WHERE etemp.status = 1 and etype.system_template = 0 ");

                foreach ($results as $row) { ?>
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

          <div class="col-md-4">
            <div class="form-group">
              <label>CC</label>
              <input type="text" class="form-control email_cc" id="cc" name="cc" value="" emailCommaSep="true" />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>BCC</label>
              <input type="text" class="form-control email_bcc" id="bcc" name="bcc" emailCommaSep="true" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Subject<span class="mandatory">*</span></label>
              <input type="text" class="form-control required email_subject" id="subject" name="subject" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message<span class="mandatory">*</span></label>
              <label class="error" id="statusMsgcomm"></label>
              <textarea class="form-control  messagecontent" id="message" name="message" style="height:200px"></textarea>
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

          </div>
          <div class="col-md-6">
          <div class="col-md-10 text-right">
          <div id="statusMsg"></div>
          </div>
          <div class="col-md-2 text-right">
          <div class="form-group">
          <input type="hidden" name="action" value="save_mass_email_to_queue">
          <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
          </div>
          </div>

          </div>
        </div>
        <!-- <div class="row mt-30">
          <div class="col-md-10 text-right">
            <div id="statusMsg"></div>
          </div>

         
        </div> -->
      </div>
    </div>
  </form>
</div>
<!-- /Content area -->
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script>
  $(document).ready(function(e) {
    CKEDITOR.replace('message', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });
    //REMOVE UPLOADED FILE
    $(document).on('click', '.remove_attachment', function() {
      $(this).parent().parent().remove();
    });

    //ADD NEW ATTACHMENT
    $("#add_more_attachments").click(function() {
      $('#attach_box').append('<div class="row mt-10"><div class="col-md-8"><input type="file" name="attachmentfile[]"></div><div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment">remove</a></div></div>');
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
      if (id.length == 0) {
        $('.note-editable').html("");
        $('.email_cc').val("");
        $('.email_bcc').val("");
        $('.email_subject').val("");
        $('.datacontent').html('');
      } else {

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
            $('.messagecontent').html(data.inputVal.email_template);
            CKEDITOR.instances['message'].setData(data.inputVal.email_template);
            
            
          } else {
            $('.email_cc').val();
            $('.email_bcc').val();
            $('.email_subject').val();
          }
        }, 'json');
      }
    });

    $('#group').change(function() {
      $('#group_id').val($(this).val());

      if ($('#group').val() == '') {
        $('#classes').html('<option value="">Select</option>');
      } else {
        //SUBJECT
        $('#classes').html('<option value="">Loading...</option>');

        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
        $.post(targetUrl, {
          group_id: $('#group').val(),
          action: 'fetch_assigned_group_class_for_select'
        }, function(data, status) {
          if (status == 'success' && data != '') {
            $('#classes').html('<option value="">Select Class</option>');
            $('#classes').append('<option value="all_subjects">All Classes</option>');
            $('#classes').append(data);
          } else {
            $('#classes').html('<option value="">Subject not found</option>');
          }
        });

      }
    });


    $('#classes').change(function() {
      $('#classes').val($(this).val());

      if ($('#classes').val() == '') {
        $('#student').html('<option value=" ">Select</option>');
      } else {

        //STUDENT
        $('#student').html('<option value="">Loading...</option>');
        $('#student').selectpicker('refresh');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
        $.post(targetUrl, {
          group_id: $('#group').val(),
          class_id: $('#classes').val(),
          action: 'get_students_of_group_for_select'
        }, function(data, status) {
          if (status == 'success' && data.code == 1) {
            $('#student').html('<option value=" ">Select</option>');
            // $('#student').append('<option value="all_students">All Students</option>');
            $('#student').append(data.optionVal);
          } else {
            $('#student').html('<option value=" ">Select</option>');
          }
          $('#student').selectpicker('refresh');
        }, 'json');
      }
    });

    //------------------Pending student----------------//

    $('.recipient').click(function() {
      var validator = $("#frmICK").validate();
      validator.resetForm();
      if ($(this).val() == 'registered_student') {
        $('#registered_student_row').removeClass('hide'); //-----open box
        $('#pending_student_row').addClass('hide'); //close box
        $('#registered_staff_row').addClass('hide'); //close box
        $('#pending_staff_row').addClass('hide'); //close box

        //--------------Required Add/Remove-------//
        $('#group').addClass('required');
        $('#classes').addClass('required');
        $('#student').addClass('required');

        $('#pending_student').removeClass('required');
        $('#registered_staff').removeClass('required');
        $('#pending_staff').removeClass('required');
        //--------------Required Add/Remove-------//



      } else if ($(this).val() == 'pending_student') {
        $('#registered_student_row').addClass('hide'); //close box
        $('#pending_student_row').removeClass('hide'); //-------open box
        $('#registered_staff_row').addClass('hide'); //close box
        $('#pending_staff_row').addClass('hide'); //close box

        //--------------Required Add/Remove-------//
        $('#group').removeClass('required');
        $('#classes').removeClass('required');
        $('#student').removeClass('required');

        $('#pending_student').addClass('required');
        $('#registered_staff').removeClass('required');
        $('#pending_staff').removeClass('required');
        //--------------Required Add/Remove-------//

        // Pending STUDENT
        $('#pending_student').html('<option value="">Loading...</option>');
        $('#pending_student').selectpicker('refresh');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
        $.post(targetUrl, {
          action: 'get_pending_students_for_select'
        }, function(data, status) {
          if (status == 'success' && data.code == 1) {
            $('#pending_student').html('');
            $('#pending_student').append(data.optionVal);
          } else {
            $('#pending_student').html('');
          }
          $('#pending_student').selectpicker('refresh');
        }, 'json');


      } else if ($(this).val() == 'registered_staff') {
        $('#registered_student_row').addClass('hide'); //close box
        $('#pending_student_row').addClass('hide'); //close box
        $('#registered_staff_row').removeClass('hide'); //--------open box
        $('#pending_staff_row').addClass('hide'); //close box

        //--------------Required Add/Remove-------//
        $('#group').removeClass('required');
        $('#classes').removeClass('required');
        $('#student').removeClass('required');

        $('#pending_student').removeClass('required');
        $('#registered_staff').addClass('required');
        $('#pending_staff').removeClass('required');
        //--------------Required Add/Remove-------//

        //----- Registered Staff-----------
        $('#registered_staff').html('<option value="">Loading...</option>');
        $('#registered_staff').selectpicker('refresh');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
        $.post(targetUrl, {
          action: 'get_registered_staff_for_select'
        }, function(data, status) {
          if (status == 'success' && data.code == 1) {
            $('#registered_staff').html('<option value="">Select</option>');
            $('#registered_staff').append(data.optionVal);
          } else {
            $('#registered_staff').html('<option value="">Select</option>');
          }
          $('#registered_staff').selectpicker('refresh');
        }, 'json');


      } else if ($(this).val() == 'pending_staff') {
        $('#registered_student_row').addClass('hide'); //close box
        $('#pending_student_row').addClass('hide'); //close box
        $('#registered_staff_row').addClass('hide'); //close box
        $('#pending_staff_row').removeClass('hide'); //--------open box

        //--------------Required Add/Remove-------//
        $('#group').removeClass('required');
        $('#classes').removeClass('required');
        $('#student').removeClass('required');

        $('#pending_student').removeClass('required');
        $('#registered_staff').removeClass('required');
        $('#pending_staff').addClass('required');
        //--------------Required Add/Remove-------//

        //----- Pending Staff-----------
        $('#pending_staff').html('<option value="">Loading...</option>');
        $('#pending_staff').selectpicker('refresh');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
        $.post(targetUrl, {
          action: 'get_pending_staff_for_select'
        }, function(data, status) {
          if (status == 'success' && data.code == 1) {
            $('#pending_staff').html('<option value="">Select</option>');
            // $('#pending_staff').append('<option value="all_students">All Staff</option>');
            $('#pending_staff').append(data.optionVal);
          } else {
            $('#pending_staff').html('<option value="">Select</option>');
          }
          $('#pending_staff').selectpicker('refresh');
        }, 'json');


      } else {
        $('#registered_student_row').removeClass('hide'); //-----open box
        $('#pending_student_row').addClass('hide'); //close box
        $('#registered_staff_row').addClass('hide'); //close box
        $('#pending_staff_row').addClass('hide'); //close box

        //--------------Required Add/Remove-------//
        $('#group').addClass('required');
        $('#classes').addClass('required');
        $('#student').addClass('required');

        $('#pending_student').removeClass('required');
        $('#registered_staff').removeClass('required');
        $('#pending_staff').removeClass('required');
        //--------------Required Add/Remove-------//
      }
    });


    $('.btn.dropdown-toggle').click(function() {
      var id = $(this).data('id');
      $('#' + id + '-error').css('display', 'none');
    });

    $('#frmICK').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.message.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');

      CKEDITOR.instances.message.updateElement();


      if ($('#frmICK').valid() && ckvalue.length > 0) {
        $('#statusMsgcomm').html('');
        $('.spinner').removeClass('hide');
        var formData = new FormData(this);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
        $.ajax({
          url: targetUrl,
          data: formData,
          type: 'POST',
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(data) {
            $('.spinner').addClass('hide');
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              $("#frmICK")[0].reset();
               CKEDITOR.instances.message.setData('');
                $("#group").selectpicker("refresh");
               

            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          },
          error: function(data) {
            $('.spinner').addClass('hide');
            displayAjaxMsg(data.msg, data.code);
          }
        }, 'json');
      }else{
       if (ckvalue.length === 0) {
        $('#statusMsgcomm').html('Required Field');
        return false;
      } else {
        $('#statusMsgcomm').html('');
      }

      }
    });


    $('#pending_student').selectpicker().change(function() {
            toggleSelectAll($(this));
        }).trigger('change');

    


  });


  function toggleSelectAll(control) {
        var allOptionIsSelected = (control.val() || []).indexOf("all_pending_students") > -1;

        function valuesOf(elements) {
            return $.map(elements, function(element) {
                return element.value;
            });
        }

        if (control.data('allOptionIsSelected') != allOptionIsSelected) {
            // User clicked 'All' option
            if (allOptionIsSelected) {
                // Can't use .selectpicker('selectAll') because multiple "change" events will be triggered
                control.selectpicker('val', valuesOf(control.find('option')));
            } else {
                control.selectpicker('val', []);
            }
        } else {
            // User clicked other option
            if (allOptionIsSelected && control.val().length != control.find('option').length) {
                // All options were selected, user deselected one option
                // => unselect 'All' option
                control.selectpicker('val', valuesOf(control.find('option:selected[value!=all_pending_students]')));
                allOptionIsSelected = false;
            } else if (!allOptionIsSelected && control.val().length == control.find('option').length - 1) {
                // Not all options were selected, user selected all options except 'All' option
                // => select 'All' option too
                control.selectpicker('val', valuesOf(control.find('option')));
                allOptionIsSelected = true;
            }
        }
        control.data('allOptionIsSelected', allOptionIsSelected);
    }

</script>
<?php include "../footer.php" ?>