<?php
$mob_title = "Edit Personal Info";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT05')) {
    include "../includes/unauthorized_msg.php";
    exit;
} 

$family = $db->get_row("select * from ss_family where id='".$_SESSION['icksumm_uat_login_familyid']."'");

$check_email_request = $db->get_row("select * from ss_change_email_request where userid='" . $_SESSION['icksumm_uat_login_userid']  . "' and status=0 ");
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
                  <input placeholder="Old Password" required name="old_password" id="old_password" maxlength="8" class="form-control" type="password">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>New Password:<span class="mandatory">*</span></label>
                  <input placeholder="New Password" required passwordCheck="true" name="new_password" id="new_password" maxlength="8" class="form-control" type="password">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Confirm Password:<span class="mandatory">*</span></label>
                  <input placeholder="Confirm Password" required equalTo="#new_password" name="confirm_password" id="confirm_password" class="form-control" maxlength="8" type="password">
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
  <!-- FORM EDIT PERDONAL INFO -->
  <div class="row">
    <div class="col-lg-12">
      <form id="frmPersInfo" class="form-validate-jquery-form2" method="post">
      <div class="panel panel-flat">
          <div class="panel-body">
            <div class="ajaxMsg_PI"></div>
            <legend class="text-semibold"><i class="icon-user position-left"></i> Parents Information</legend>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>1st Parent First Name:<span class="mandatory">*</span></label>
                  <input placeholder="1st Parent First Name" spacenotallow="true" required lettersonly="true" maxlength="25" name="father_first_name" value="<?php echo $family->father_first_name ?>" class="form-control required" type="text">
                </div>
              </div> 
              <div class="col-md-3">
                <div class="form-group">
                  <label>1st Parent Last Name:<span class="mandatory">*</span></label>
                  <input placeholder="1st Parent Last Name" spacenotallow="true" lettersonly="true"  maxlength="25"  value="<?php echo $family->father_last_name ?>" name="father_last_name" class="form-control required" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>1st Parent Phone:<span class="mandatory">*</span></label>
                  <input placeholder="1st Parent Phone"  maxlength="12" PhoneNumber="true"  value="<?php echo internal_phone_check($family->father_phone, 'edit') ?>" name="father_phone" class="form-control parent1_phone required phone_no" type="text">
                </div>
              </div>
            </div>
            
            <?php if($family->mother_first_name && $family->mother_first_name && $family->mother_phone){ ?>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>2nd Parent First Name:<span class="mandatory">*</span></label>
                  <input placeholder="2nd Parent First Name"  spacenotallow="true" maxlength="25"  lettersonly="true" name="mother_first_name" value="<?php echo $family->mother_first_name ?>" class="form-control required" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>2nd Parent Last Name:<span class="mandatory">*</span></label>
                  <input placeholder="2nd Parent Last Name"  spacenotallow="true" maxlength="25"   lettersonly="true" value="<?php echo $family->mother_last_name ?>" name="mother_last_name" class="form-control required" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>2nd Parent Phone:<span class="mandatory">*</span></label>
                  <input placeholder="2nd Parent Phone"  maxlength="12" PhoneNumber="true"  value="<?php echo internal_phone_check($family->mother_phone, 'edit') ?>" name="mother_phone" class="form-control parent1_phone required phone_no" type="text">
                </div>
              </div>
            </div>
            <?php } ?>
            <br />
            <legend class="text-semibold"><i class="icon-envelop position-left"></i>Emails</legend>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Primary Email:<span class="mandatory">*</span></label>
                  <input placeholder="Primary Email" class="form-control email" readonly value="<?php echo $family->primary_email ?>"  maxlength="100"  type="text">
                </div>
                <?php 
                // if(empty($check_email_request)){
                  ?>
                <a href="javascript:void(0)" title="c" class="action_link text-success changeemailpopup ">Email Change</a>
                <?php
              //  }
               ?>
              </div>
              <?php if($family->secondary_email!=null){?>
              <div class="col-md-3">
                <div class="form-group">
                
                  <label>Secondary Email:<span class="mandatory">*</span></label>
                  <input placeholder="Secondary Email" maxlength="100" class="form-control email required" name="secondary_email" value="<?php echo $family->secondary_email ?>" type="text">
                </div>
              </div>
              <?php } ?>

            </div>
            <br />
            <legend class="text-semibold"><i class="icon-address-book position-left"></i> Address</legend>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Address Line 1:<span class="mandatory">*</span></label>
                  <input placeholder="Address Line 1" spacenotallow="true" class="form-control" maxlength="200" required name="billing_address_1" id="billing_address_1" value="<?php echo $family->billing_address_1 ?>" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Address Line 2:</label>
                  <input placeholder="Address Line 2" class="form-control"  maxlength="200"  name="billing_address_2" id="billing_address_2" value="<?php echo $family->billing_address_2 ?>" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>City:<span class="mandatory">*</span></label>
                  <input placeholder="City" spacenotallow="true" class="form-control" maxlength="25"  required name="billing_city" id="billing_city" value="<?php echo $family->billing_city ?>" type="text">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>State:<span class="mandatory">*</span></label>
                  <?php $states = $db->get_results("select * from ss_state where is_active=1 and country_id = '".get_country()->country_id."' order by state "); ?>
                  <select class="select form-control required" name="billing_state_id" id="billing_state_id">
                    <option value="">Select</option>
                    <?php foreach ($states as $state) { ?>
                        <?php if (trim($family->billing_state_id) == '') { ?>
                            <option value="<?php echo $state->id ?>" <?php echo ($family->billing_entered_state == $state->abbreviation || $family->billing_entered_state == $state->id) ? 'selected="selected"' : '' ?>>
                                <?php echo $state->state ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $state->id ?>" <?php echo $family->billing_state_id == $state->id ? 'selected="selected"' : '' ?>>
                                <?php echo $state->state ?></option>
                        <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <!-- <div class="col-md-3">
                <div class="form-group">
                  <label>Entered State:<span class="mandatory">*</span></label>
                  <input placeholder="Entered State" class="form-control" maxlength="25"  required name="billing_entered_state" id="billing_entered_state" value="<?php echo $family->billing_entered_state ?>" type="text">
                </div>
              </div> -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Country:<span class="mandatory">*</span></label>
                  <?php $countrys = $db->get_results("select * from ss_country where id='".get_country()->country_id."' and is_active=1"); ?>
                  <select class="select form-control required" name="billing_country_id" id="billing_country_id">
                    <option value="">Select</option>
                    <?php foreach($countrys as $country){ ?>
                    <option value="<?php echo $country->id ?>" <?php echo $family->billing_country_id==$country->id?'selected="selected"':'' ?>><?php echo $country->country ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Zipcode:</label>
                  <input placeholder="Zipcode" class="form-control required"  name="billing_post_code" id="billing_post_code" value="<?php echo $family->billing_post_code ?>" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" type="text" zipCodeCheck="true">
                </div>
              </div>
            </div>

            <br />
            <!-- <legend class="text-semibold">
            <i class="icon-address-book position-left"></i>Shipping Address
            <div class="pull-right">
              <input type="checkbox" id="same_as_billing_ad" />
              Copy billing address</div>
            </legend> -->
            <!-- <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Address Line 1:<span class="mandatory">*</span></label>
                  <input placeholder="Address Line 1" spacenotallow="true" maxlength="200"  class="form-control" required name="shipping_address_1" id="shipping_address_1" value="<?php //echo $family->shipping_address_1 ?>" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Address Line 2:</label>
                  <input placeholder="Address Line 2"  maxlength="200" class="form-control" name="shipping_address_2" id="shipping_address_2" value="<?php //echo $family->shipping_address_2 ?>" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>City:<span class="mandatory">*</span></label>
                  <input placeholder="City" spacenotallow="true" class="form-control"  maxlength="25" required name="shipping_city" id="shipping_city" value="<?php //echo $family->shipping_city ?>" type="text">
                </div>
              </div>
            </div> -->
            <!-- <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>State:<span class="mandatory">*</span></label>
                  <?php //$states = $db->get_results("select * from ss_state where is_active=1"); ?>
                  <select class="select form-control required" name="shipping_state_id" id="shipping_state_id">
                    <option value="">Select</option>
                    <?php //foreach($states as $state){ ?>
                    <option value="<?php // echo $state->id ?>" <?php // echo $family->shipping_state_id==$state->id?'selected="selected"':'' ?>><?php //echo $state->state ?></option>
                    <?php //} ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Entered State:<span class="mandatory">*</span></label>
                  <input placeholder="Entered State" maxlength="25" class="form-control" required name="shipping_entered_state" id="shipping_entered_state" value="<?php //echo $family->shipping_entered_state ?>" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Country:<span class="mandatory">*</span></label>
                  <?php //$countrys = $db->get_results("select * from ss_country where is_active=1"); ?>
                  <select class="select form-control required" name="shipping_country_id" id="shipping_country_id">
                    <option value="">Select</option>
                    <?php //foreach($countrys as $country){ ?>
                    <option value="<?php //echo $country->id ?>" <?php //echo $family->shipping_country_id==$country->id?'selected="selected"':'' ?>><?php //echo $country->country ?></option>
                    <?php //} ?>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Postcode:</label>
                  <input placeholder="Postcode" class="form-control" name="shipping_post_code" id="shipping_post_code" Digit="true" maxlength="5" value="<?php //echo $family->shipping_post_code ?>" type="text">
                </div>
              </div>
            </div> -->

            <div class="row">
              <div class="col-md-10 text-right">
                <div class="ajaxMsgBot_PI"></div>
              </div>
              <div class="col-md-2 text-right">
                <input type="hidden" name="action" value="edit_family_personal">
                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner spinner_PI hide marR10 insidebtn"></i> Submit</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Popup to enter the email dated 26.09.2022-->
<div id="modal_change_email" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frmtochangeemail" id="frmtochangeemail1"  method="post">
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
          <button type="submit" class="btn btn-success" id="emailsubmit"><i class="icon-spinner2 spinner spinner_CP1 hide marR10 insidebtn" id="get_spinner1"></i> Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['icksumm_uat_login_userid']; ?>" >
          <input type="hidden" name="action" value="change_email">
          <input type="hidden" name="type" value="0">

        </div>
      </form>
    </div>
  </div>
</div>





<!-- /Content area -->
<script>
$(document).ready(function() {

<?php echo get_country()->phone_formate ?>
$(".changeemailpopup").click(function (e) { 


$('#modal_change_email').modal('show');


});

$('#modal_change_email').on('hide.bs.modal', function(e) {
        $('.ajaxMsgBot').html('');
        $('#frmtochangeemail1').trigger('reset');
        var validator = $("#frmtochangeemail1").validate();
        validator.resetForm();
       
    });


  $('#same_as_billing_ad').change(function(){
		if($(this).is(':checked')){
			$('#shipping_address_1').val($('#billing_address_1').val());
			$('#shipping_address_2').val($('#billing_address_2').val());
			$('#shipping_city').val($('#billing_city').val());
			$('#shipping_state_id').val($('#billing_state_id').val());
			$('#shipping_entered_state').val($('#billing_entered_state').val());
			$('#shipping_country_id').val($('#billing_country_id').val());
			$('#shipping_post_code').val($('#billing_post_code').val());
		}else{
			$('#shipping_address_1').val('');
			$('#shipping_address_2').val('');
			$('#shipping_city').val('');
			$('#shipping_state_id').val('');
			$('#shipping_entered_state').val('');
			$('#shipping_country_id').val('');
			$('#shipping_post_code').val('');
		}
    
    $('.select').change();
		$('#frmICK').valid();
  });

  $('input').on('keypress', function(e) {
        if (this.value.length === 0 && e.which === 32){
            return false;
        }
  });
  
  jQuery.validator.addMethod("Digit", function(value, element, params) {
            return this.optional(element) || /^[0-9]{5}$/i.test(value);
    }, "Enter valid digits only");

	$('#frmPersInfo').submit(function(e){
		e.preventDefault();
		
		if($('#frmPersInfo').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
			$('.spinner_PI').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
					}else{
          }
          
          displayAjaxMsgCust(data.msg,data.code,'ajaxMsg_PI','spinner_PI','alert');
          displayAjaxMsgCust(data.msg,data.code,'ajaxMsgBot_PI','spinner_PI','label');
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
  });
  
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
  ////////////////////////////////////////////////////
});
</script>
<?php include "../footer.php"?>