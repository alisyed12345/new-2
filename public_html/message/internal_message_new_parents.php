<?php
$mob_title = "New Message";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if ($_SESSION['icksumm_uat_login_usertypecode'] != 'UT01' && $_SESSION['icksumm_uat_login_usertypecode'] != 'UT05') {
  include "../includes/unauthorized_msg.php";
  return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Send Internal Message</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "parents/dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL . "message/inertnal_message_list" ?>">Received Internal Message</a></li>
      <li class="active">Send Internal Message</li>
    </ul>
  </div>
</div>  
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
  <form id="frmICJC" class="form-validate-jquery" method="post">
    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
        <div class="ajaxMsg"></div>
        <div class="row">

          <div class="col-md-4 multi-select-full rec_staff">
            <div class="form-group">
              <label>Staff (Group) <span class="mandatory">*</span></label>

              <?php
              $staffs = $db->get_results("SELECT g.group_name, u.id, s.first_name, s.middle_name, s.last_name, CONCAT(' (',t.user_type,')') AS user_type FROM ss_user u 
              INNER JOIN ss_staff s ON u.id = s.user_id 
              INNER JOIN `ss_staff_session_map` ssm ON ssm.`staff_user_id` = s.user_id 
              INNER JOIN ss_usertypeusermap utm ON utm.`user_id` = u.`id`
              INNER JOIN ss_usertype t ON t.`id` = utm.`user_type_id` 
              INNER JOIN ss_staffclasstimemap sct ON sct.`staff_user_id` = s.user_id 
              INNER JOIN ss_classtime c ON c.id = sct.classtime_id 
              INNER JOIN ss_groups g ON g.id = c.group_id WHERE t.user_type_code = 'UT02' AND t.user_type_subgroup = 'teacher' 
              AND u.is_active = 1 AND u.is_deleted = 0 AND ssm.`session_id` = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' GROUP BY u.id
             ORDER BY first_name ASC");
              
              
              $get_principle  = $db->get_row("SELECT u.id, CONCAT(' ',t.user_type,' ') AS user_type FROM ss_user u 
              INNER JOIN ss_usertypeusermap utm ON utm.`user_id` = u.`id`
              INNER JOIN ss_usertype t ON t.`id` = utm.`user_type_id` 
              AND u.is_active = 1 AND u.is_deleted = 0  AND t.user_type_subgroup ='principal'");

              // echo "<pre>";
              // print_r($staffs);
              // die;

              ?>
              <select class="form-control required" id="staff" name="staff">
                <option value="">Select</option>
                <?php if ($get_principle) { ?>
                  <option value="<?php echo $get_principle->id; ?>"><?php echo $get_principle->user_type; ?></option>
                <?php } ?>
                <?php foreach ($staffs as $sta) {
                  if ($sta->id != $_SESSION['ss_login_userid']) {
                ?>
                    <option value="<?php echo $sta->id ?>"><?php echo $sta->first_name . ' ' . $sta->last_name . ' ' . $sta->user_type . ' (' . $sta->group_name . ')' ?></option>
                <?php }
                } ?>
              </select>
            </div>
          </div>
          <div>

            <!-- <div class="col-md-4 multi-select-full rec_staff">
            <div class="form-group">

            </div>
          </div> -->
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message <span class="mandatory">*</span></label>
              <textarea class="form-control required" id="message" name="message"></textarea>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-10 text-right">
            <div class="form-group">
              <div class="ajaxMsgBot"></div>
            </div>
          </div>
          <div class="col-md-2 text-right">
            <div class="form-group">
              <input type="hidden" name="user_type_id" value="<?php echo $_SESSION['icksumm_uat_login_userid'] ?>">
              <input type="hidden" name="user_type_code" value="<?php echo $_SESSION['icksumm_uat_login_usertypecode'] ?>">
              <input type="hidden" name="action" value="save_message">
              <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10"></i> Send </button>
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
    $('.recipient').click(function() {
      if ($(this).val() == 'student') {
        $('.rec_student').removeClass('hide');
        $('.rec_staff').addClass('hide');
        $('#staff').val('');
      } else {
        $('.rec_student').addClass('hide');
        $('.rec_staff').removeClass('hide');
        $('#group').val('');
        $('#student').html('<option value="">Select</option>');
      }
    });

    $('#group').change(function() {
      $('#student').html('<option value="">Loading...</option>');

      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
      $.post(targetUrl, {
        group_id: $('#group').val(),
        action: 'student_of_group'
      }, function(data, status) {
        if (status == 'success' && data.code == 1) {
          $('#student').html(data.optionVal);
        } else {
          $('#student').html('<option value="">Select</option>');
        }
      }, 'json');
    });

    $('#frmICJC').submit(function(e) {
      e.preventDefault();

      if ($('#frmICJC').valid()) {
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
        $('.spinner').removeClass('hide');
        

        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
              $("input[name=message_to][value=staff]").prop('checked', true);
              $('#staff').val('');
              $('#group').val('');
              $('#student').html('<option value="">Select</option>');
              $('#message').val('');
              displayAjaxMsg(data.msg, data.code);
            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          } else {
            displayAjaxMsg('Error: Process failed');
          }
        }, 'json');
      }
    });

    //$('.bootstrap-select').selectpicker();
  });
</script>
<?php include "../footer.php" ?>