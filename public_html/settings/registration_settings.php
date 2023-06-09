<?php
$mob_title = "Registration Setting";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01')) {
  include "../includes/unauthorized_msg.php";
  exit;
}
$registration_info = $db->get_row("select is_new_registration_open, new_registration_start_date, new_registration_end_date, is_new_registration_free, new_registration_fees, new_registration_session,is_waiting,registration_page_termsncond,new_registration_fees_form_head,status,new_registration_email_bcc,new_registration_email_cc from ss_client_settings where status = 1");
if (isset($registration_info->is_new_registration_open)) {
  $reg_status = $registration_info->is_new_registration_open;
}
if (isset($registration_info->new_registration_start_date)) {
  $new_registration_start_date = date('d F, Y', strtotime($registration_info->new_registration_start_date));
}
if (isset($registration_info->new_registration_end_date)) {
  $new_registration_end_date = date('d F, Y', strtotime($registration_info->new_registration_end_date));
}
if (isset($registration_info->is_new_registration_free)) {
  $is_new_registration_free = $registration_info->is_new_registration_free;
}
if (isset($registration_info->new_registration_fees)) {
  $new_registration_fees = $registration_info->new_registration_fees;
}
if (isset($registration_info->registration_page_termsncond)) {
  $registration_page_termsncond = $registration_info->registration_page_termsncond;
}
if (isset($registration_info->new_registration_fees_form_head)) {
  $fee_type = $registration_info->new_registration_fees_form_head;
}
if (isset($registration_info->new_registration_email_bcc)) {
  $sender_bcc = $registration_info->new_registration_email_bcc;
}
if (isset($registration_info->new_registration_email_cc)) {
  $sender_cc = $registration_info->new_registration_email_cc;
}
if (isset($registration_info->new_registration_session)) {
  $new_registration_session = $registration_info->new_registration_session;
}
if (isset($registration_info->is_waiting)) {
  $is_waiting = $registration_info->is_waiting;
}
?>
<!-- Page header -->
<style>
  .reg_form .row {
    text-align: left;
  }

  .reg_form .row [class^="col-"] {
    margin-top: 10px;
  }

  span.mands {
    color: #ff0000;
    display: inline;
    line-height: 1;
    font-size: 12px;
    margin-left: 5px;
  }

  label.error {
    color: #ff0000;
    margin-top: 0px;
    padding-left: 12px;
  }

  .error_cust {
    padding-left: 10px;
    z-index: 0;
    display: inline-block;
    margin-bottom: 7px;
    color: #f44336;
    position: relative;
  }

  .shoinline {
    display: flex;
    margin-left: -14px;
  }

  .form-check-inline {
    margin-left: 15px;
  }
</style>
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Registration Settings</h4> 
    </div>
  </div>
  <?php  if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->major)) {?>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Registration Settings</li>
    </ul>
  </div>
  <?php }else{ ?> 
  <div class="breadcrumb-line">
      <ul class="breadcrumb">
          <li><a href="<?php echo SITEURL ?>check_data" ><i class="glyphicon glyphicon-check"></i> Check Mandatory Information</a></li>
      </ul>
  </div>
  <?php } ?>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
  <div class="panel panel-flat">
    <div class="panel-body">
    <div class="ajaxMsg"></div>

      <!-- Advanced login -->
      <form name="frm_register" class="reg_form" id="frm_register" method="post" enctype='multipart/form-data'>

        <legend class="text-semibold">New Registration Info</legend>
        <div class="row" style="margin-top:-20px;">
          <div class="col-md-3">
            <div class="form-group">
              <label>New Registration Status:<span class="mands">*</span></label>
              <select name="new_registration_open" class="form-control required">
                <option value="">Select</option>
                <option value="1" <?php echo $reg_status == "1" ? "selected='selected'" : "" ?>>Open</option>
                <option value="0" <?php echo $reg_status == "0" ? "selected='selected'" : "" ?>>Close</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Fee Type:<span class="mands">*</span></label>
              <select name="fees_type" class="form-control required">
                <option value="">Select</option>
                <option value="1" <?php echo $fee_type == "1" ? "selected='selected'" : "" ?>>Per Head</option>
                <option value="0" <?php echo $fee_type == "0" ? "selected='selected'" : "" ?>>Per Form</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <?php $get_session = $db->get_results("select * from ss_school_sessions where status = 1"); ?>
            <div class="form-group">
              <label>New Registration Session:<span class="mands">*</span></label>
              <select name="new_reg_session_id" class="form-control required">
                <option value="">Select</option>
                <?php foreach ($get_session as $session) { ?>
                  <option value="<?php echo $session->id ?>" <?php echo $new_registration_session == $session->id ? "selected='selected'" : "" ?>><?php echo $session->session;  echo $new_registration_session == $session->id ? " (Current)" : ""?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Registration Start Date:<span class="mands">*</span></label>
              <input type="text" name="new_registration_start_date" id="new_registration_start_date" maxlength="25" placeholder="Registration Start Date" value="<?= $new_registration_start_date ?>" class="form-control new_registration_start_date required">
            </div>
          </div>


          <div class="col-md-3">
            <div class="form-group">
              <label>Registration End Date:<span class="mands">*</span></label>
              <input type="text" name="new_registration_end_date" id="new_registration_end_date" value="<?= $new_registration_end_date ?>" placeholder="Registration End Date" class="form-control required new_registration_end_date bgcolor-white" endDate="true">
            </div>
          </div>

          <div class="col-md-3" style="display:none;">
            <div class="form-group">
              <label>Is Registration Free:<span class="mandatory">*</span></label>
              <div class="col-md-12">
                <label class="radio-inline">
                  <input type="radio" class="required" onclick="Reg_fees(0)" id="radio1" name="is_new_registration_free" value="1" <?php echo $is_new_registration_free == "1" ? "checked='checked'" : "" ?>> YES
                </label>
                <label class="radio-inline">
                  <input type="radio" onclick="Reg_fees(1)" id="radio2" name="is_new_registration_free" value="0" <?php echo $is_new_registration_free == "0" ? "checked='checked'" : "" ?>> NO
                </label>
              </div>
            </div>
          </div>

           

          <div class="col-md-3 show_reg_fees" style="display:none;">
            <div class="form-group">
              <label>Registration Fees:<span class="mands">*</span></label>
              <input type="text" class="form-control" dollarsscents="true" minlength="1" maxlength="8" name="new_registration_fees" id="new_registration_fees" placeholder="Registration Fees Amount ($) " value="<?= $new_registration_fees ?>">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Is Waiting List Open:<span class="mandatory">*</span></label>
              <div class="col-md-12">
                <label class="radio-inline">
                  <input type="radio" class="required" id="radiow1" name="is_waiting" value="1" <?php echo $is_waiting == "1" ? "checked='checked'" : "" ?>> YES
                </label>
                <label class="radio-inline">
                  <input type="radio" id="radiow2" name="is_waiting" value="0" <?php echo $is_waiting == "0" ? "checked='checked'" : "" ?>> NO
                </label>
              </div>
            </div>
          </div>
          </dev>
        </div>
        <br>
        <legend class="text-semibold" style="margin-top:-10px;">Terms & Condition Information</legend>
        <div class="row" style="margin-top:-20px;">
          <div class="col-md-12">
            <div class="form-group">
              <label>Terms & Condition:<span class="mands">*</span></label>
              <label id="statusMsgcomm" style="color:red"></label>
              <textarea id="summernote" name="term_and_condition" spacenotallow="true" class="required"><?= $registration_page_termsncond ?></textarea>
            </div>
          </div>
        </div>
        <legend class="text-semibold" style="margin-top:-50px;">Email Settings</legend>
         <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Email Cc:</label>
              <!-- <input type="text" name="sender_cc" id="sender_cc" placeholder="Cc" lettersonly="true" value="<?= $sch_name ?>" class="form-control required"> -->
              <input type="text" name="sender_cc" emailCommaSep="true" class="form-control emailcc" placeholder="Cc" value="<?= $sender_cc ?>">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Email Bcc:</label>
              <input type="text" name="sender_bcc" emailCommaSep="true" placeholder="Bcc" class="form-control emailbcc" value="<?= $sender_bcc ?>">
            </div>
          </div>
        </div>
        <br>
       

        <div class="row" style="margin-bottom:50px;">
          <div class="col-md-10 col-xs-8" style="margin-top:15px;">
            <strong class="pull-right" id="statusMsg"></strong>
          </div>

          <div class="col-md-2 col-xs-4">
            <input type="hidden" name="action" value="register_setting">
            <input type="submit" value="Submit" class="btn btn-success btn-block btnsubmit" tabindex="225">
          </div>
        </div>

      </form>
     

    </div>
    <!-- /content area -->
  </div>
</div>



<!-- /Content area -->
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script type="text/javascript">
  function Reg_fees(val) {
    if (val == 1) {
      $('#new_registration_fees').addClass('required');
      $('.show_reg_fees').show();
    } else {
      $('.show_reg_fees').hide();
      $('#new_registration_fees').removeClass('required');
    }


  }

  jQuery(document).ready(function() {
    CKEDITOR.replace('summernote', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });

    $("div.note-editing-area div.note-editable").keypress(function (evt) {
     var kc = evt.keyCode;
     var qbQuestion = CKEDITOR.instances.summernote.getData();
     if (kc === 32 && (qbQuestion.length == 0 || qbQuestion == '<p><br></p>')) {
        event.preventDefault();
     }
    
  });






    $.validator.addMethod("endDate", function(value, element) {
      var startDate = $('.new_registration_start_date').val();
      return Date.parse(startDate) <= Date.parse(value) || value == "";
    }, "School closing date must be after school opening date");

    $('input').on('keypress', function(e) {
      if (this.value.length === 0 && e.which === 32) {
        return false;
      }
    });

    $('.emailcc').on('keypress', function(e) {
      if (e.which === 32) {
        return false;
      }
    });

    $('.emailbcc').on('keypress', function(e) {
      if (e.which === 32) {
        return false;
      }
    });



    if ($('input[name=is_new_registration_free]:checked', '#frm_register').val()) {
      if ($('input[name=is_new_registration_free]:checked').val() == 0) {
        $('.show_reg_fees').show();
      } else if ($('input[name=is_new_registration_free]:checked').val() == 1) {
        $('.show_reg_fees').hide();
        $('#new_registration_fees').removeClass('required');
      }
    }



    jQuery.validator.addMethod("dollarsscents", function(value, element) {
      return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
    }, "Please enter a valid amount");

    jQuery.validator.addMethod("emailCommaSep", function(value, element) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      var str_array = value.split(',');

      for (var i = 0; i < str_array.length; i++) {
        if ($.trim(str_array[i]) != '') {
          if (!re.test(String($.trim(str_array[i])).toLowerCase())) {
            return false;
          }
        }
      }

      return true;
    }, "Enter valid email");



    $('.new_registration_start_date').pickadate({
      labelMonthNext: 'Go to the next month',
      labelMonthPrev: 'Go to the previous month',
      labelMonthSelect: 'Pick a month from the dropdown',
      labelYearSelect: 'Pick a year from the dropdown',
      selectMonths: true,
      selectYears: 100,
      min: [<?php echo date('Y') ?>, <?php echo date('m') - 1  ?>, <?php echo date('d') ?>],
      max: [2030, 12, 31],
      formatSubmit: 'yyyy-mm-dd'
    });


    $('.new_registration_end_date').pickadate({
      labelMonthNext: 'Go to the next month',
      labelMonthPrev: 'Go to the previous month',
      labelMonthSelect: 'Pick a month from the dropdown',
      labelYearSelect: 'Pick a year from the dropdown',
      selectMonths: true,
      selectYears: 100,
      min: [<?php echo date('Y') ?>, <?php echo date('m') - 1 ?>, <?php echo date('d') ?>],
      max: [2030, 12, 31],
      formatSubmit: 'yyyy-mm-dd'
    });



    /* $('#frm_register').submit(function() {

      if ($('#frm_register').valid()) {
          $('.btnsubmit').attr('disabled', true);
          $('.ajaxMsgBot').html('<h3 class="mar-top-zero">Processing...Please Wait</h3>');
          return true;
      }else{
        return false;
      }
    }); */

    $('#frm_register').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.summernote.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');

      CKEDITOR.instances.summernote.updateElement();


      if ($('#frm_register').valid() && ckvalue.length > 0) {
        $('#statusMsgcomm').html('');
        $('.spinner').removeClass('hide');
        var formData = new FormData(this);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-settings';
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
              $("#frm_register")[0].reset();
                // CKEDITOR.instances.summernote.setData('');
                location.reload(true);
      
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


    //Email Settings
    $('#email_setting').submit(function(e) {
      e.preventDefault();

      if ($('#email_setting').valid()) {
        $('.btnemailsubmit').attr('disabled', true);
        $('.statusmsg').html('<p class="mar-top-zero">Processing...Please Wait</p>');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-settings';
        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            $('.btnemailsubmit').attr('disabled', false);
            if (data.code == 1) {
              $('.statusmsg').html(data.emailmsg);
              //$('#frm_register').trigger('reset');
              $('.statusmsg').html(data.emailmsg, data.code);
              setTimeout(function() {
                $('.statusmsg').html('');
              }, 3000);
              
            } else {
              $('.statusmsg').html(data.emailmsg, data.code);
              setTimeout(function() {
                $('.statusmsg').html('');
              }, 3000);
            }
          } else {
            $('.statusmsg').html(data.emailmsg);
            setTimeout(function() {
              $('.statusmsg').html('');
            }, 3000);
          }
        }, 'json');
      }
    });

  });
</script>
<?php
/* unset ($_SESSION["error"]);
unset ($_SESSION["success"]); */
include "../footer.php"
?>