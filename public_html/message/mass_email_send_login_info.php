<?php
include_once "../includes/config.php";
$mob_title = "Mass Email With Login Details";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
//if (!check_userrole_by_code('UT01') && !check_userrole_by_code('UT02')) {
  if (!check_userrole_by_code('UT01')) {
  include "../includes/unauthorized_msg.php";
  return;
}

if ($_POST['action'] == 'save_mass_email_to_queue') {
  //ADDED ON 14-MAY-2018
  ini_set('max_execution_time', 300); //300 seconds = 5 minutes
  ini_set('memory_limit', '1024M');

  $families = $db->get_results("select * from ss_family where id in (SELECT family_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id  WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (SELECT student_user_id FROM ss_studentgroupmap WHERE latest = 1) order by s.first_name,s.last_name)");

  //STOP REPEATED CLICK ENTRY
  $last_msg_time_diff = $db->get_var("select TIME_TO_SEC(TIMEDIFF('" . date('Y-m-d H:i:s') . "', created_on)) as time_diff from ss_bulk_message where created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' order by id desc limit 1");

  if ($last_msg_time_diff > 4 || $last_msg_time_diff == "") {
    foreach ($families as $fam) {
      $db->query('BEGIN');

      $subject = CENTER_SHORTNAME.' '.SCHOOL_NAME." Login Details";
      $primary_email = trim($fam->primary_email);
      $secondary_email = trim($fam->secondary_email);

      $message = "Dear Parents Assalamualaikum,<br><br>First of all, we would like to thank you for your co-operation and support. We are going to add a new feature in our system. From this week you will receive an email with the ".SCHOOL_NAME." homework.<br><br>";

      if ($primary_email != '' && $secondary_email != '') {
        $message .= "Your emails registered with ".SCHOOL_NAME." are <strong>" . $primary_email . "</strong> (primary) and <strong>" . $secondary_email . "</strong> (secondary).";
      } else if ($primary_email != '') {
        $message .= "Your email registered with ".SCHOOL_NAME." is <strong>" . $primary_email . "</strong>.";
      }

      $message .= '<br><br>Please use below details to login into '.CENTER_SHORTNAME.' '.SCHOOL_NAME.' Parent Section:<br><br><strong>Login URL :</strong> '.SITEURL.'login.php<br><br><strong>Email :</strong> ' . $primary_email . '<br><br><strong>Password :</strong> Please use password provided earlier or <a href="'.SITEURL.'forgot_password.php" target="_blank">click here</a> to generate new password.<br><br>Please feel free to contact at support@bayyan.org, if you need any further assistance.<br><br>'.CENTER_SHORTNAME.' '.SCHOOL_NAME.' Team';

      $sql_bulk_msg = "insert into ss_bulk_message set subject = '" . $subject . "', message = '" . $message . "', is_report_gen = 0, 
      created_on = '" . date('Y-m-d H:i:s') . "', created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'";

      if ($db->query($sql_bulk_msg)) {
        $message_id = $db->insert_id;

        if (trim($fam->primary_email) != '') {
          $to_primary = $fam->primary_email;
          //$to_primary = 'moh.urooj@gmail.com';

          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', receiver_email = '" . $to_primary . "', 
          is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }

        if (trim($fam->secondary_email) != '') {
          $to_secondary = $fam->secondary_email;
          //$to_secondary = 'moh.urooj@gmail.com';

          if ($db->query("insert into ss_bulk_message_emails set bulk_message_id = '" . $message_id . "', receiver_email = '" . $to_secondary . "', 
          is_cc = 0, is_bcc = 0, delivery_status = 2, attempt_counter = 0")) {
            $emailStatus = true;
          }
        }

        $bcc = 'support@bayyan.org';
        //$bcc = 'moh.urooj@gmail.com';
        if ($db->query("insert into ss_bulk_message_emails set bulk_message_id='" . $message_id . "', receiver_email='" . trim($bcc) . "', is_cc=0, 
        is_bcc=1, delivery_status = 2, attempt_counter = 0")) {
          $emailStatus = true;
        }

        if ($db->query('COMMIT') !== false) {
          $msg = 'Email(s) queue created successfully';
          $code = 1;
        } else {
          $db->query('ROLLBACK');
          $msg = "Email(s) queue not created, please try again.";
          $code = 0;
        }
      } else {
        $db->query('ROLLBACK');
        $msg = "Email(s) queue not created. Please try again.";
        $code = 0;
      }
    }
  } else {
    $msg = 'Email(s) queue created successfully';
    $code = 1;
  }
}
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
      <h4>Mass Email With Login Details</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Mass Email With Login Details</li>
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
          <div class="col-md-6">            
            <div class="row mt-30">
              <div class="col-md-12">
                <div class="form-group">
                  <p>Please click below button to send email to all parents with their login details.</p>
                  <input type="hidden" name="action" value="save_mass_email_to_queue">
                  <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<!-- /Content area -->

<script>
  $(document).ready(function(e) {
    //REMOVE UPLOADED FILE
    $(document).on('click', '.remove_attachment', function() {
      $(this).parent().parent().remove();
    });

    //ADD NEW ATTACHMENT
    $("#add_more_attachments").click(function() {
      $('#attach_box').append('<div class="row mt-10"><div class="col-md-8"><input type="file" name="attachmentfile[]"></div><div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment">remove</a></div></div>');
    });

    $('#group').change(function() {
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
    });

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
</script>
<?php include "../footer.php" ?>