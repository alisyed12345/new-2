<?php
$mob_title = "Edit Staff";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if ((check_userrole_by_code('UT01') && check_userrole_by_group('admin')) || check_userrole_by_code('UT03') || check_userrole_by_code('UT05')) {
    include "../includes/unauthorized_msg.php";
    exit;
}

$user = $db->get_row("select * from ss_user usr INNER JOIN ss_staff_session_map ssm ON ssm.staff_user_id = usr.id  where usr.id='" . $_SESSION['icksumm_uat_login_userid'] . "'");
$staff = $db->get_row("select * from ss_staff where user_id='" . $_SESSION['icksumm_uat_login_userid']  . "'");

$check_email_request = $db->get_row("select * from ss_change_email_request where userid='" . $_SESSION['icksumm_uat_login_userid']  . "' and status=0 ");


if ($user->is_deleted == 1) {
    $status = "delete_soft";
} elseif ($user->is_active == 1) {
    $status = "active";
} elseif ($user->is_active == 0) {
    $status = "inactive";
}
 
if (verifyDate($staff->joining_date)) {
    $joining_date = date('d F, Y', strtotime($staff->joining_date));
}

if (verifyDate($staff->dob)) {
    $dob = date('d F, Y', strtotime($staff->dob));
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Edit Personal Info</h4>
    </div>
  </div>
  <div class="breadcrumb-line"> 
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Edit Personal Info</li>
    </ul>
  </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
  <!-- FORM EDIT PERDONAL INFO -->
  <div class="row">
    <div class="col-lg-12">
      <form id="frmChangePswd" class="form-validate-jquery" method="post">
        <div class="panel panel-flat">
          <div class="panel-body">
          <div class="ajaxMsg_CP"></div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Old Password:<span class="mandatory">*</span></label>
                  <input placeholder="Old Password" required name="old_password" id="old_password" class="form-control" type="password">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>New Password:<span class="mandatory">*</span></label>
                  <input placeholder="New Password" required passwordCheck="true" name="new_password" id="new_password" class="form-control" type="password">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Confirm Password:<span class="mandatory">*</span></label>
                  <input placeholder="Confirm Password" required equalTo="#new_password" name="confirm_password" id="confirm_password" class="form-control" type="password">
                </div>
              </div>
              </div>
              <div class="row">
              <div class="col-md-9 text-right">
                    <div class="ajaxMsgBot_CP"></div>
                      </div>

              <div class="col-md-3 text-right">
                <input type="hidden" name="action" value="change_password">
                <button type="submit" id="btnSubmitPI" class="btn btn-success"><i class="icon-spinner2 spinner spinner_CP hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Popup to enter the email dated 26.09.2022-->
  <div id="modal_change_email" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frmtochangeemail" id="frmtochangeemail1" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title">Add New Email <span id="modal_studentname"></span></h5>
        </div>
        <div class="modal-body">
          <div class="ajaxMsg"></div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
              
                  <input class="form-control required"  id="email_name" name="email_name" type="email">
               
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success" id="emailsubmit"> <i class="icon-spinner2 spinner spinner_CP1 hide marR10 insidebtn" id="get_spinner"></i>Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['icksumm_uat_login_userid']; ?>" >
          <input type="hidden" name="action" value="change_email">
          <input type="hidden" name="type" value="1">
        </div>
      </form>
    </div>
  </div>
</div>

  <!-- FORM EDIT PERDONAL INFO -->
  <?php if(check_userrole_by_subgroup('teacher') || check_userrole_by_subgroup('accounatnt') || check_userrole_by_subgroup('teacher_helper') 
  || check_userrole_by_subgroup('teacher_substitute')) { ?>
  <div class="row">
    <div class="col-lg-12">
      <form id="frmPersInfo" class="form-validate-jquery-form2" method="post">
        <div class="panel panel-flat">
          <div class="panel-body">
            <div class="ajaxMsg_PI"></div>
            <legend class="text-semibold"><i class="icon-user position-left"></i> Personal Information</legend>

            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="first_name">First Name:<span class="mandatory">*</span></label>
                  <input placeholder="First Name" spacenotallow="true" id="first_name" lettersonly="true" name="first_name" required class="form-control" type="text" value="<?php echo $staff->first_name ?>">
                </div>
              </div>
              <!-- <div class="col-md-3">
                <div class="form-group">
                  <label for="middle_name">Middle Name:</label>
                  <input placeholder="Middle Name" name="middle_name" lettersonly="true" id="middle_name" class="form-control" type="text" value="<?php echo $staff->middle_name ?>">
                </div>
              </div> -->
              <div class="col-md-3">
                <div class="form-group">
                  <label for="last_name">Last Name:</label>
                  <input placeholder="Last Name" spacenotallow="true" name="last_name" lettersonly="true" id="last_name" class="form-control" type="text" value="<?php echo $staff->last_name ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Gender:<span class="mandatory">*</span></label>
                  <div class="col-md-12">
                    <label class="radio-inline">
                      <input type="radio" required <?php echo $staff->gender == "m" ? 'checked="checked"' : '' ?> name="gender" id="gender_m" value="m">
                      Male </label>
                    <label class="radio-inline">
                      <input type="radio" id="gender" <?php echo $staff->gender == "f" ? 'checked="checked"' : '' ?> name="gender" id="gender_f" value="f">
                      Female </label>
                  </div>
                </div>
              </div>              
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Email:<span class="mandatory">*</span></label>
                  <input placeholder="Email" readonly class="form-control" type="text" value="<?php echo $user->email ?>">
                </div>

                <?php 
                // if(empty($check_email_request)){
                  ?>
                <a href="javascript:void(0)" title="c" class="action_link text-success changeemailpopup">Email Change</a>
                  <?php 
                  // }
                  ?>

              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="dob">Date of Birth:</label>
                  <input placeholder="Date of Birth" id="dob" name="dob" class="form-control" type="text" value="<?php echo $dob ?>">
                </div>
              </div>
            </div>
            <legend class="text-semibold"><i class="icon-envelop position-left"></i>Contact Information</legend>
            <div class="row">
              <!-- <div class="col-md-4">
                <div class="form-group">
                  <label>Email:<span class="mandatory">*</span></label>
                  <input placeholder="Email" name="email" required class="form-control" type="email" value="<?php echo $user->email ?>">
                </div>
              </div> -->
           
              <div class="col-md-4">
                <div class="form-group">
                  <label>Primary No:<span class="mandatory">*</span></label>
                  <input placeholder="Primary No" name="mobile" maxlength="12" required phonenocheck="true" class="form-control" type="text" id="mobile" value="<?php echo $staff->mobile ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Alternate No:</label>
                  <input placeholder="Alternate No XXX-XXX-XXXX" name="phone" maxlength="12" phonenocheck="true" class="form-control" type="text" id="phone" value="<?php echo $staff->phone ?>">
                </div>
              </div>  
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Address Line 1:<span class="mandatory">*</span></label>
                  <input placeholder="Address Line 1" spacenotallow="true" name="address_1" required class="form-control" type="text" value="<?php echo $staff->address_1 ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Address Line 2:</label>
                  <input placeholder="Address Line 2" name="address_2" class="form-control" type="text" value="<?php echo $staff->address_2 ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>City:<span class="mandatory">*</span></label>
                  <input placeholder="City" spacenotallow="true" required name="city" class="form-control" type="text" value="<?php echo $staff->city ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                  <label>State:<span class="mandatory">*</span></label>
                  <?php $states = $db->get_results("select * from ss_state where is_active=1 and country_id = '".get_country()->country_id."' order by state");?>
                  <select class="select form-control required" name="state_id" id="state_id">
                    <option value="">Select</option>
                    <?php foreach ($states as $state) {?>
                    <option value="<?php echo $state->id ?>" <?php echo $staff->state_id == $state->id ? 'selected="selected"' : '' ?>><?php echo $state->state ?></option>
                    <?php }?>
                  </select>
              </div>
              <div class="col-md-4">
                  <label for="country">Country:<span class="mandatory">*</span></label>
                  <?php $countrys = $db->get_results("select * from ss_country where id='".get_country()->country_id."'  and is_active=1");?>
                  <select class="select form-control required" name="country_id" id="country_id">
                    <option value="">Select</option>
                    <?php foreach ($countrys as $country) {?>
                    <option value="<?php echo $country->id ?>" <?php echo $staff->country_id == $country->id ? 'selected="selected"' : '' ?>><?php echo $country->country ?></option>
                    <?php }?>
                  </select>
              </div>
              <div class="col-md-4">
                     <label for="country">Zipcode:<span class="mandatory">*</span></label>
                    <input type="text" name="post_code" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" id="post_code" value="<?php echo $staff->post_code ?>" zipCodeCheck="true" placeholder="Zip Code" class="form-control required" tabindex="205">
              </div>
            </div>
            <div class="row" style="margin-top: 30px;">
              <div class="col-md-10 text-right">
                <div class="ajaxMsgBot_PI" style="margin-top: 10px;"></div>
              </div>
              <div class="col-md-2 text-right">
                <input type="hidden" name="action" value="edit_staff_personal">
                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner spinner_PI hide marR10 insidebtn"></i> Submit</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>
</div>
<!-- /Content area -->
<script>
$(document).ready(function() {

   $('#phone').mask('000-000-0000');
   $('#mobile').mask('000-000-0000');

  //VALIDATION - US PHONE FORMAT 
  jQuery.validator.addMethod("phonenocheck", function(value, element) {
         return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);  
  }, "Enter valid phone number");


	$('#frmChangePswd').submit(function(e){
    e.preventDefault();

    if($('#frmChangePswd').valid()){
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-user';
      $('.spinner_CP').removeClass('hide');

      var formDate = $(this).serialize();
      $.post(targetUrl,formDate,function(data,status){
        if(status == 'success'){
          if(data.code == 1){
            //displayAjaxMsgCust(data.msg,data.code,'ajaxMsg_CP');
            //displayAjaxMsgCust(data.msg,data.code,'ajaxMsgBot_CP');
          }else{
            //displayAjaxMsg(data.msg,data.code);
          }

          displayAjaxMsgCust(data.msg,data.code,'ajaxMsg_CP','spinner_CP','alert');
          displayAjaxMsgCust(data.msg,data.code,'ajaxMsgBot_CP','spinner_CP','label');
        }else{
          displayAjaxMsg('Error: Process failed');
        }
      },'json');
    }
  });

  $('#joining_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: true,
		max: [<?php echo date('Y') ?>,<?php echo date('m') ?>,<?php echo date('d') ?>],
		formatSubmit: 'yyyy-mm-dd'
  });

	$('#dob').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: true,
		max: [<?php echo date('Y') - 15 ?>,12,31],
		formatSubmit: 'yyyy-mm-dd'
    });

	$('#frmPersInfo').submit(function(e){
		e.preventDefault();

		if($('#frmPersInfo').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff';
			$('.spinner_PI').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){
				if(status == 'success'){
					if(data.code == 1){
						//displayAjaxMsg(data.msg,data.code);
					}else{
						//displayAjaxMsg(data.msg,data.code);
          }
          
          displayAjaxMsgCust(data.msg,data.code,'ajaxMsg_PI','spinner_PI','alert');
          displayAjaxMsgCust(data.msg,data.code,'ajaxMsgBot_PI','spinner_PI','label');
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
	});

  //Change Email Request

  
$(".changeemailpopup").click(function (e) { 
$('#modal_change_email').modal('show');
});

$('#modal_change_email').on('hide.bs.modal', function(e) {
      $('.ajaxMsgBot').html('');
      $('#frmtochangeemail1').trigger('reset');
      
      var validator = $("#frmtochangeemail1").validate();
      validator.resetForm();
  });


$('#frmtochangeemail1').submit(function(e) {
   e.preventDefault();
    if ($('#frmtochangeemail1').valid()) {
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
      $('.spinner_CP1').removeClass('hide');
      var formDate = $(this).serialize();
      $.post(targetUrl, formDate, function(data, status) {
        if (status == 'success') {
          if (data.code == 1) {
            displayAjaxMsg(data.msg, data.code);
               $('#email_name').val('');
               $(".changeemailpopup").addClass("hide");
               setTimeout(function() {
               $('#modal_change_email').modal('hide');
                    }, 2500);          
          } else {
            displayAjaxMsg(data.msg, data.code);
          }
        } else {
          displayAjaxMsg(data.msg);
        }
      }, 'json');
    }

  });


});
</script>
<?php include "../footer.php"?>