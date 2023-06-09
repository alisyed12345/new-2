<?php
$mob_title = "General Settings";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
//if (!check_userrole_by_code('UT01')) {
if (!check_userrole_by_code('UT01')) {
  include "../includes/unauthorized_msg.php";
  exit;
}
//} 
$genral_info = $db->get_row("select school_name, school_opening_date, school_closing_date, school_opening_days, contact_admin_email, contact_organisation_email, 
contact_phone, contact_address, contact_city, contact_state_id, contact_zipcode, school_logo, school_header_logo, fees_monthly, one_student_one_lavel,
center_short_name, country_id, contact_organization_name from ss_client_settings where status = 1");
if (isset($genral_info->center_short_name)) {
  $center_short_name = $genral_info->center_short_name;
}
if (isset($genral_info->school_name)) {
  $sch_name = $genral_info->school_name;
}
if (isset($genral_info->school_opening_date)) {
  $school_opening_date = date('d F, Y', strtotime($genral_info->school_opening_date));
}
if (isset($genral_info->school_closing_date)) {
  $school_closing_date = date('d F, Y', strtotime($genral_info->school_closing_date));
}
if (isset($genral_info->school_opening_days)) {
  $school_opening_days = unserialize($genral_info->school_opening_days);
}
if (isset($genral_info->contact_admin_email)) {
  $admin_email = $genral_info->contact_admin_email;
}
if (isset($genral_info->contact_organisation_email)) {
  $organization_email = $genral_info->contact_organisation_email;
}
if (isset($genral_info->contact_phone)) {  
  $phone_no = $genral_info->contact_phone;
}
if (isset($genral_info->contact_address)) {
  $address = $genral_info->contact_address;
}
if (isset($genral_info->contact_city)) {
  $city = $genral_info->contact_city;
}
if (isset($genral_info->contact_state_id)) {
  $states = $genral_info->contact_state_id;
}
if (isset($genral_info->contact_zipcode)) {
  $zip_code = $genral_info->contact_zipcode;
}
if (isset($genral_info->school_logo)) {
  $school_logo = $genral_info->school_logo;
}
if (isset($genral_info->school_header_logo)) {
  $school_header_logo = $genral_info->school_header_logo;
}
if (isset($genral_info->fees_monthly)) {
  $monthly_fee = $genral_info->fees_monthly;
}
if (isset($genral_info->one_student_one_lavel)) {
  $one_student_one_lavel = $genral_info->one_student_one_lavel;
}
if (isset($genral_info->country_id)) {
  $country_id = $genral_info->country_id;
}
if (isset($genral_info->contact_organization_name)) {
  $organization_name = $genral_info->contact_organization_name;
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
    margin-top: 0px;
    padding-left: 12px;
    color: red;
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
      <h4>General Settings</h4>
    </div>
  </div> 
  <?php  if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->major)) {
            ?>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">General Settings</li>
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

      <!-- Advanced login -->
      <form name="frm_register" class="frm_register form-validate-jquery" id="frm_register" method="post" enctype="multipart/form-data">
        <legend class="text-semibold">General Info</legend>
        <div class="row" style="margin-top:-10px;">
          <div class="col-md-2">
            <div class="form-group">
              <label>Center Short Name:<span class="mands">*</span></label>
              <input type="text" spacenotallow="true" name="center_short_name" lettersonly="true" tabindex="5" id="center_short_name" maxlength="25" placeholder="Center Short Name" value="<?= $center_short_name ?>" class="form-control required">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Organization Name:<span class="mands">*</span></label>
              <input type="text" name="organization_name" id="organization_name" placeholder="Organization Name" value="<?= $organization_name ?>" class="form-control required">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>School Name:<span class="mands">*</span></label>
              <input type="text" spacenotallow="true" name="school_name" lettersonly="true" tabindex="5" id="school_name" maxlength="25" placeholder="School Name" value="<?= $sch_name ?>" class="form-control required">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>School Opening Date:<span class="mands">*</span></label>
              <input type="text" name="school_opening_date" tabindex="10" id="school_opening_date" maxlength="25" placeholder="School Opening Date" value="<?= $school_opening_date ?>" class="form-control school_opening_date required">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>School Closing Date:<span class="mands">*</span></label>
              <input type="text" name="school_closing_date" id="school_closing_date" tabindex="20" value="<?= $school_closing_date ?>" placeholder="School Closing Date" class="form-control required school_closing_date bgcolor-white" endDate="true">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Header Logo ( <strong>Note:</strong> Max. Height <span class="text-danger">100px</span> Max. Width <span class="text-danger">500px</span>) :<span class="mands">*</span></label>
              <input type="file" name="header_logo" id="header_logo" accept="image/x-png,image/gif,image/jpeg" class="form-control" onchange='CheckDimension("header_logo","100","500");'>
              <input type="hidden" name="head_logo" value="<?php echo $school_header_logo ?>">

            </div>
        
          </div>
          <div class="col-md-8">
            <?php if (!empty($school_header_logo)) { ?>
              <img src="<?php echo SITEURL . $school_header_logo ?>" style="height: 60px; width:auto;">
            <?php } else { ?>
              <img src="<?php echo SITEURL ?>/assets/images/logo-dummy.png">
            <?php } ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>School Logo ( <strong>Note:</strong> Max. Height <span class="text-danger">150px</span> Max. Width <span class="text-danger">300px</span>) :<span class="mands">*</span></label>
              <input type="file" name="school_logo" id="school_logo" accept="image/x-png,image/gif,image/jpeg" class="form-control" value="" onchange='CheckDimension("school_logo","150","300")'> 
              <input type="hidden" name="email_logo" value="<?php echo $school_logo ?>">
            </div>
          </div>
          <div class="col-md-8">
            <?php if (!empty($school_logo)) { ?>
              <img src="<?php echo SITEURL . $school_logo ?>" style="height: 60px; width:auto;">
            <?php } else { ?>
              <img src="<?php echo SITEURL ?>/assets/images/logo-dummy.png">
            <?php } ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label>Monthly Fee:<span class="mandatory">*</span></label>
              <div class="form-group">
                <label class="radio-inline">
                  <input type="radio" class="required" id="monthly_fee1" name="monthly_fee" value="1" <?php echo $monthly_fee == "1" ? "checked='checked'" : "" ?>> YES
                </label>
                <label class="radio-inline">
                  <input type="radio" class="required" id="monthly_fee2" name="monthly_fee" value="0" <?php echo $monthly_fee == "0" ? "checked='checked'" : "" ?>> NO
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>One Student One Level:<span class="mandatory">*</span></label>
              <div class="form-group">
                <label class="radio-inline">
                  <input type="radio" class="required" id="one_student_one_level" name="one_student_one_level" value="1" <?php echo $one_student_one_lavel == "1" ? "checked='checked'" : "" ?>> YES
                </label>
                <label class="radio-inline">
                  <input type="radio" class="required" id="one_student_one_level" name="one_student_one_level" value="0" <?php echo $one_student_one_lavel == "0" ? "checked='checked'" : "" ?>> NO
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-2">
              <label>Country:<span class="mands">*</span></label>
              <select name="country_id" id="country_id" class="form-control required">
                <option value="">Select Country</option>
                <?php
                $get_country = $db->get_results("select * from ss_country where is_active = 1");
                foreach ($get_country as $country) {
                ?>
                  <option value="<?php echo $country->id ?>" <?php echo $country_id == $country->id ? "selected='selected'" : "" ?>><?php echo $country->country ?></option>
                <?php } ?>
              </select>
          </div>
        </div>
        <br><br>
        <legend class="text-semibold" style="margin-top:-25px;">General Day Info </legend>

        <div class="row" style="margin-top:-10px;">
          <div class="col-md-12">
            <div class="form-group">
              <label>School Opening Day:<span class="mandatory">*</span></label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check1" name="school_opening_days[]" value="1" <?php if (in_array('1', $school_opening_days)) echo 'checked="checked"'; ?>> Monday
              </label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check2" name="school_opening_days[]" value="2" <?php if (in_array('2', $school_opening_days)) echo 'checked="checked"'; ?>> Tuesday
              </label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check1" name="school_opening_days[]" value="3" <?php if (in_array('3', $school_opening_days)) echo 'checked="checked"'; ?>> Wednesday
              </label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check1" name="school_opening_days[]" value="4" <?php if (in_array('4', $school_opening_days)) echo 'checked="checked"'; ?>> Thursday
              </label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check1" name="school_opening_days[]" value="5" <?php if (in_array('5', $school_opening_days)) echo 'checked="checked"'; ?>> Friday
              </label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check1" name="school_opening_days[]" value="6" <?php if (in_array('6', $school_opening_days)) echo 'checked="checked"'; ?>> Saturday
              </label>
              <label class="radio-inline">
                <input type="checkbox" class="required" id="check1" name="school_opening_days[]" value="0" <?php if (in_array('0', $school_opening_days)) echo 'checked="checked"'; ?>> Sunday
              </label>

              <label id="school_opening_days[]-error" class="validation-error-label" for="school_opening_days[]" style="display: none;margin-top: 3px;">Required field</label>
            </div>
          </div>
        </div>
        <br>
        <legend class="text-semibold">Contact Info</legend>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Admin Email:<span class="mands">*</span></label>
              <input type="text" name="admin_email" id="admin_email" placeholder="Admin Email" value="<?= $admin_email ?>" class="form-control email required">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Organization Email:<span class="mands">*</span></label>
              <input type="text" name="organization_email" id="organization_email" placeholder="Organization Email" value="<?= $organization_email ?>" class="form-control email required">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Phone No:<span class="mands">*</span></label>
              <input type="text" name="phone_no" maxlength="10" id="phoneno" PhoneNumber="true" placeholder="Phone No" value="<?= $phone_no ?>"  class="form-control phone_no required">
            </div>
          </div>
        </div>

        <div class="row" style="margin-top:-10px;">
          <div class="col-md-4">
            <div class="form-group">
              <label>City:<span class="mands">*</span></label>
              <input type="text" name="city" id="city" spacenotallow="true" lettersonly="true" maxlength="20" value="<?= $city ?>" placeholder="City" class="form-control required">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>State:<span class="mands">*</span></label>
              <select name="state_id" id="state_id" class="form-control required">
                <option value="">Select State</option>
                <?php
                $get_state = $db->get_results("select * from ss_state where is_active = 1 and country_id = '".get_country()->country_id."' order by state asc");
                foreach ($get_state as $state) {
                ?>
                  <option value="<?php echo $state->id ?>" <?php echo $states == $state->id ? "selected='selected'" : "" ?>><?php echo $state->state ?></option>
                <?php } ?>
              </select>
            </div> 
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Zip-code:<span class="mands">*</span></label>
              <input type="text" name="zip_code" id="zipcode" zipCodecountry="true" maxlength="5"  value="<?= $zip_code ?>" placeholder="Zip-code" class="form-control required">
            </div>
          </div>
        </div>

        <div class="row" style="margin-top:-10px;">
          <div class="col-md-4">
            <div class="form-group">
              <label>Address:<span class="mands">*</span></label>
              <textarea type="text" spacenotallow="true" name="address" id="address" maxlength="50" placeholder="Address" class="form-control required" colspan="8"><?= $address ?></textarea>
            </div>
          </div>

        </div>

        <div class="row" style="margin-bottom:50px;">
          <div class="col-md-10 col-xs-8">
            <strong class="text-right" id="statusMsg"></strong>
          </div>
          <div class="col-md-2 col-xs-4 text-right">
            <input type="hidden" name="action" value="general_setting">
          <!--   <input type="submit" value="Submit" class="btn btn-success btn-block btnffsubmit" > -->
            <button type="submit" class="btn btn-primary btnffsubmit" tabindex="225"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Save</button>
          </div>
        </div>
      </form>


    </div>
    <!-- /content area -->
  </div>
</div>


<!-- /Content area -->
<script src="<?php echo SITEURL ?>assets/js/jquery-ui.min.js"></script>
<!-- <script src="<?php echo SITEURL ?>assets/js/jquery-ui-timepicker-addon.js"></script> -->
<script type="text/javascript">
  jQuery(document).ready(function() {

    $.validator.addMethod("endDate", function(value, element) {
      var startDate = $('.school_opening_date').val();
      return Date.parse(startDate) <= Date.parse(value) || value == "";
    }, "School closing date must be after school opening date");

    /*   setTimeout(function(){ 
      $('.ajaxMsgBot').hide();
    }, 3000);
 */

    $('.school_opening_date').pickadate({
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

    $('.school_closing_date').pickadate({
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


    $('#frm_register').on('submit', (function(e) {
      e.preventDefault();
      // $(this).data('validator').settings.ignore = ".note-editor *";
      if ($('#frm_register').valid()) {
        $('.spinner').removeClass('hide');
        var targetUrl = '<?php echo $SITEURL ?>../ajax/ajss-settings';
        $('#statusMsg').html('Processing...');
        var formData = new FormData(this);

        $.ajax({
          type: 'POST',
          url: targetUrl,
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
            $('.spinner').addClass('hide');
            if (data.code == 1) {
              $('#statusMsg').html(data.msg);
              setTimeout(function() {
                $("#statusMsg").html('');
                location.reload();
              }, 2000);

            } else {
              $("#statusMsg").html(data.msg);
              setTimeout(function() {
                $("#statusMsg").html('');
              }, 3000);
            
            }
          },
          error: function(data) {
            $('.spinner').addClass('hide');
            $("#statusMsg").html(data);
            setTimeout(function() {
              $("#statusMsg").html('');
            }, 3000);
           
          }
        });

      }
    })); 
    jQuery.validator.addMethod('PhoneNumbercountry', function(value, element) {
			return this.optional(element) || /^\d{3}-?\d{3}-?\d{4}$/i.test(value);
		}, 'Enter valid phone number');
  <?php if(get_country()->abbreviation == 'USA'){ echo get_country()->phone_formate ?>
      jQuery.validator.addMethod("zipCodecountry", function(value, element) {
              //return this.optional(element) || /^((?!(0))[0-9]{6,7})$/i.test(value);
              //return this.optional(element) || /^[A-Z]{1,2}[0-9][A-Z0-9]? ?[0-9][A-Z]{2}$/i.test(value);
              return this.optional(element) || /^\d{5}(-\d{4})?$/.test(value);
          }, "Enter valid zipcode");
        var max_length = 5;
        $('#zipcode').attr('maxlength', max_length);
        $('#phoneno').val('<?php echo internal_phone_check($phone_no, 'edit');?>');
   <?php }elseif(get_country()->abbreviation == 'GB'){ echo get_country()->phone_formate?>
      jQuery.validator.addMethod("zipCodecountry", function(value, element) {
              //return this.optional(element) || /^((?!(0))[0-9]{6,7})$/i.test(value);
              return this.optional(element) || /^(([A-Z]{1,2}[0-9][A-Z0-9]?|ASCN|STHL|TDCU|BBND|[BFS]IQQ|PCRN|TKCA) ?[0-9][A-Z]{2}|BFPO ?[0-9]{1,4}|(KY[0-9]|MSR|VG|AI)[ -]?[0-9]{4}|[A-Z]{2} ?[0-9]{2}|GE ?CX|GIR ?0A{2}|SAN ?TA1)$/i.test(value);
          }, "Enter valid zipcode");
          var max_length = 8;
          $('#zipcode').attr('maxlength', max_length);
    <?php } ?>
    $('#country_id').change(function() {
      if($('#country_id').val() == 1){
          jQuery.validator.addMethod("zipCodecountry", function(value, element) {
              //return this.optional(element) || /^((?!(0))[0-9]{6,7})$/i.test(value);
              //return this.optional(element) || /^[A-Z]{1,2}[0-9][A-Z0-9]? ?[0-9][A-Z]{2}$/i.test(value);
              return this.optional(element) || /^\d{5}(-\d{4})?$/.test(value);
          }, "Enter valid zipcode");
          var max_length = 5; 
        
          var phone = '<?php echo internal_phone_check($phone_no, 'edit');?>';
          $(".phone_no").mask('000-000-0000');
      }else{
          jQuery.validator.addMethod("zipCodecountry", function(value, element) {
              //return this.optional(element) || /^((?!(0))[0-9]{6,7})$/i.test(value);
              return this.optional(element) || /^(([A-Z]{1,2}[0-9][A-Z0-9]?|ASCN|STHL|TDCU|BBND|[BFS]IQQ|PCRN|TKCA) ?[0-9][A-Z]{2}|BFPO ?[0-9]{1,4}|(KY[0-9]|MSR|VG|AI)[ -]?[0-9]{4}|[A-Z]{2} ?[0-9]{2}|GE ?CX|GIR ?0A{2}|SAN ?TA1)$/i.test(value);
          }, "Enter valid zipcode");
          var max_length = 8;
          var max_length_phone = 10;
          var phone = '<?php echo internal_phone_check($phone_no, 'edit');?>';
          $(".phone_no").unmask($(this).val());
          $('.phone_no').attr('maxlength', max_length_phone);
          $('.phone_no').removeAttr('PhoneNumber');
          $('.phone_no').attr('PhoneNumbercountry', true);

      }

      if ($('#country_id').val() == '') {
          $('#state_id').html('<option value="">Select State</option>');
      } else {
          //SUBJECT
          $('#state_id').html('<option value="">Loading...</option>');
          $('#zipcode').attr('maxlength', max_length);
          $('#phoneno').val(phone);
          var targetUrl = '<?php echo SITEURL ?>ajax/ajss-settings';
          $.post(targetUrl, {
              country_id: $('#country_id').val(),
              action: 'fetch_state'
          }, function(data, status) {
              if (status == 'success' && data != '') {
                  $('#state_id').html('<option value="">Select State</option>');
                  $('#state_id').append(data);
              } else {
                  $('#state_id').html('<option value="">State not found</option>');
              }
          });

      }
    });



    $('#frm_register').validate({ // initialize the plugin
      rules: {
        admin_email: {
          required: true,
          email: true
        },
        organization_email: {
          required: true,
          email: true
        },
        phone_no: {
          required: true
        },
        city: {
          required: true
        },
        state_id: {
          required: true
        },
        zip_code: {
          required: true
        },
        address: {
          required: true
        },
        // school_closing_date: { 
        //   greaterThan: "#school_opening_date"
        // },
      }
    });
  });


  function CheckDimension(img,heit,widt) {

    //Get reference of File.
    var fileUpload = document.getElementById(img);
    
    //Check whether the file is valid Image.
    var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.gif)$");
    if (regex.test(fileUpload.value.toLowerCase())) {
 
        //Check whether HTML5 is supported.
        if (typeof (fileUpload.files) != "undefined") {
            //Initiate the FileReader object.
            var reader = new FileReader();
            //Read the contents of Image File.
            reader.readAsDataURL(fileUpload.files[0]);
            reader.onload = function (e) {
                //Initiate the JavaScript Image object.
                var image = new Image();
 
                //Set the Base64 string return from FileReader as source.
                image.src = e.target.result;
                       
                //Validate the File Height and Width.
                image.onload = function () {
                    var height = this.height;
                    var width = this.width;
                    if (height > heit || width > widt) {
                      $("#"+img).after('<span class="text-danger msgs">Height and Width size exceed </span>');
                      $("#"+img).val(''); 
                      setTimeout(function(){
                        $(".msgs").remove();
                      }, 5000);
                        return false;
                    }
                   
                };
 
            }
        } else {
            alert("This browser does not support HTML5.");
            return false;
        }
    } else {
       $("#"+img).after('<span class="text-danger msgs">Please select a valid Image file </span>');
        $("#"+img).val(''); 
         setTimeout(function(){
                        $(".msgs").remove();
                      }, 5000);
         return false;
    }
}
</script>
<?php include "../footer.php" ?>