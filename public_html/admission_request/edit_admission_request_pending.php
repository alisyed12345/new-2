<?php
$mob_title = "Edit Admission Request";
include "../header.php";
//AUTHARISATION CHECK -
if (!in_array("su_admission_request_edit", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}
$grades = array('KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher');
$family = $db->get_row("SELECT * FROM ss_sunday_school_reg WHERE id = '".$_GET['id']."'");
$child = $db->get_results("SELECT * FROM ss_sunday_sch_req_child WHERE sunday_school_reg_id = '".$family->id."'");
?>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Edit Admission Request</h4>
        </div>
    </div>
    <div class="breadcrumb-line">  
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a>
            </li>

            <li><a href="<?php echo SITEURL . "admission_request/admission_request_pending" ?>">Admission Request (Registered/Waiting)</a></li>
            <li class="active">Edit Admission Request (Registered/Waiting)</li>
        </ul>
    </div>
    <div class="above-content">
        <a href="javascript:history.go(-1)" class="last_page">Go Back To Last Page</a>
    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <form id="frmICK" class="form-validate-jquery" method="post">
                <div class="panel panel-flat">
                    <div class="panel-body">
                        <div class="ajaxMsg"></div>
                        <div class="row hmar">
                            <div class="col-md-12">
                                <h3 class="subtitle">Student Information</h3>
                            </div>
                        </div>
                        <?php foreach($child as $key => $get_child){
                            $uni_key = $key+1;
                         ?>
                            
                        <div class="row fromcln">
                            <div class="col-md-2">
                                <input type="text" spacenotallow="true" name="child_first_name<?php echo $uni_key ?>" lettersonly="true" tabindex="5" id="child1_first_name" maxlength="25" placeholder="First Name" lettersonly="true" value="<?php echo $get_child->first_name ?>" class="form-control stu required">
                            </div>
                            <div class="col-md-2">
                                <input type="text" spacenotallow="true" name="child_last_name<?php echo $uni_key ?>" lettersonly="true" tabindex="8" id="child1_last_name" maxlength="25" placeholder="Last Name" value="<?php echo $get_child->last_name ?>" class="form-control required" lettersonly="true">
                            </div>
                            <div class="col-md-2">
                                <select name="child_gender<?php echo $uni_key ?>" id="child_gender" tabindex="12" class="form-control required">
                                    <option value="">Select Gender</option>
                                    <option value="m" <?php echo $get_child->gender == 'm' ? 'selected="selected"' : '' ?>>Male
                                    </option>
                                    <option value="f" <?php echo $get_child->gender == 'f' ? 'selected="selected"' : '' ?>>Female
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" id="child_dob" name="child_dob<?php echo $uni_key ?>"  tabindex="15" value="<?php echo my_date_changer($get_child->dob,'c') ?>" placeholder="Date of Birth" class="form-control required datepicker bgcolor-white">
                            </div>
                            </dev>


                            <div class="col-md-2">
                                <input type="text" id="child_allergies" name="child_allergies<?php echo $uni_key ?>" value="<?php echo $get_child->allergies ?>" maxlength="50" tabindex="18" placeholder="Enter Allergies" class="form-control required">
                            </div>

                            <div class="col-md-2">
                                <select name="child_grade<?php echo $uni_key ?>" id="child_grade" class="form-control required" tabindex="21">
                                    <option value="">Select Grade</option>
                                    <?php foreach ($grades as $garde) {?>
                                        <option value="<?=$garde?>" <?php echo $get_child->school_grade == $garde ? 'selected="selected"' : '' ?>><?=$garde?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="child<?php echo $uni_key ?>_reg_id" value="<?php echo $get_child->id ?>">
                        <br>
                      <?php } ?>
                        <div class="row" style="margin-top:15px;">
                            <div class="col-md-2">
                                <h3 class="subtitle">Family Information</h3>
                            </div>
                            <?php if(!empty($family->mother_first_name) && !empty($family->mother_last_name)){ ?>
                            <div class="col-md-10"><label class="checkbox-inline" style="margin-top: 25px;"><input type="checkbox" tabindex="110" name="singleParent" id="singleParent"> Single Parent </label>
                            </div>
                            <?php } ?>
                        </div>
                        
                        <div id="firstparent" style="display:block;">
                            <div class="row">
                                <div class="col-md-12"> <strong>1st Parent Information<span class="mandatory">*</span></strong> </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="text" spacenotallow="true" name="parent1_first_name" maxlength="25" value="<?php echo $family->father_first_name ?>" id="parent1_first_name" tabindex="115" placeholder="First Name" class="form-control required" lettersonly="true">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" spacenotallow="true" name="parent1_last_name" maxlength="25" value="<?php echo $family->father_last_name ?>" id="parent1_last_name" tabindex="120" placeholder="Last Name" class="form-control required" lettersonly="true">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" tabindex="125" name="parent1_phone" value="<?php echo internal_phone_check($family->father_phone, 'edit') ?>" id="parent1_phone" PhoneNumber="true" maxlength="10" placeholder="Phone Number" class="form-control required phone_no">
                                    <label id="parent1_phone-error" class="validation-error-label" for="parent1_phone" style="display: none;">Enter valid phone number</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" tabindex="130" name="parent1_email" value="<?php echo $family->primary_email ?>"  id="parent1_email" placeholder="1st Parent E-mail" class="form-control required email">
                                </div>
                                <div class="col-md-3">
                                    <input type="radio" checked="" tabindex="135" name="which_is_primary_email" value="parent1"  <?php echo $family->primary_contact == 'Father' ? 'checked="checked"' : '' ?> id="primary_email_parent1"> Primary Contact
                                </div>
                            </div>
                        </div>
                        <br>
                        <?php if(!empty($family->mother_first_name) && !empty($family->mother_last_name)){ ?>
                        <div id="secondparent" style="display:block;">
                            <div class="row">
                                <div class="col-md-12"> <strong>2nd Parent Information<span class="mandatory">*</span></strong> </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="text"  name="parent2_first_name" maxlength="25" value="<?php echo $family->mother_first_name ?>" tabindex="140" id="parent2_first_name" placeholder="First Name" class="form-control parent2 required" lettersonly="true">
                                </div>
                                <div class="col-md-2">
                                    <input type="text"  name="parent2_last_name" maxlength="25" value="<?php echo $family->mother_last_name ?>" tabindex="145" id="parent2_last_name" placeholder="Last Name" class="form-control parent2 required" lettersonly="true">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="parent2_phone" value="<?php echo internal_phone_check($family->mother_phone, 'edit') ?>" id="parent2_phone" tabindex="150" PhoneNumber="true" maxlength="10" placeholder="Phone Number" class="form-control parent2 required phone_no">
                                    <!-- <label id="parent2_phone-error" class="validation-error-label required" for="parent1_phone" style="display: none;">Enter valid phone number</label> -->
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="parent2_email" value="<?php echo $family->secondary_email ?>" tabindex="155" id="parent2_email" placeholder="2nd Parent  E-mail" class="form-control parent2 email required">
                                    <input type="hidden" name="primary_email" id="primary_email" tabindex="160">
                                    <input type="hidden" name="secondary_email" id="secondary_email" tabindex="165">
                                </div>
                                <div class="col-md-3">
                                    <input type="radio" name="which_is_primary_email" tabindex="170" value="parent1" <?php echo $family->primary_contact == 'Mother' ? 'checked="checked"' : '' ?> id="primary_email_parent2"> Primary Contact
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row" style="margin-top:15px;">
                            <div class="col-md-12">
                                <h3 class="subtitle">Address Information</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Address Line 1:<span class="mandatory">*</span></label>
                                    <input placeholder="Address Line 1" spacenotallow="true" class="form-control" required name="address_1" id="address_1" value="<?php echo $family->address_1 ?>" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Address Line 2:</label>
                                    <input placeholder="Address Line 2" class="form-control" name="address_2" id="address_2" value="<?php echo $family->address_2 ?>" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>City:<span class="mandatory">*</span></label>
                                    <input placeholder="City" spacenotallow="true" class="form-control" required name="city" id="city" value="<?php echo $family->city ?>" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>State:<span class="mandatory">*</span></label>
                                    <?php
                                    $states = $db->get_results("select * from ss_state where is_active=1 and country_id = '".get_country()->country_id."'"); ?>
                                    <select class="select form-control required" name="state" id="state">
                                        <option value="">Select</option>
                                        <?php foreach ($states as $state) { ?>
                                                <option value="<?php echo $state->id ?>" <?php echo $family->state == $state->id ? 'selected="selected"' : '' ?>>
                                                    <?php echo $state->state ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Country:<span class="mandatory">*</span></label>
                                    <?php $countrys = $db->get_results("select * from ss_country where is_active=1 and id = '".get_country()->country_id."'"); ?>
                                    <select class="select form-control required" name="country_id" id="country_id">
                                        <option value="">Select</option>
                                        <?php foreach ($countrys as $country) { ?>
                                            <option value="<?php echo $country->id ?>" <?php echo $family->country_id == $country->id ? 'selected="selected"' : '' ?>>
                                                <?php echo $country->country ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Zipcode:</label>
                                    <input placeholder="Zipcode" class="form-control required" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" name="post_code" id="post_code" zipCodeCheck="true" value="<?php echo $family->post_code ?>" type="text">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12"> <strong>Use this space for any additional information or questions</strong>
                                <textarea name="addition_notes" id="addition_notes" class="form-control" tabindex="235"><?php echo $family->addition_notes ?></textarea>
                            </div>
                        </div>
                        <div class="row" style="margin-top:20px;">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="action" value="edit_admission_request">
                                <input type="hidden" name="family_reg_id" value="<?php echo $family->id ?>">
                                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- /Content area -->
<script> 
    $(document).ready(function() {
        <?php echo get_country()->phone_formate; ?>
        $('.datepicker').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: 100,
            min: [<?php echo date('Y') - 100 ?>, 01, 01],
            max: [<?php echo date('Y') - 3 ?>, 12, 31],
            format: 'mm/dd/yyyy',
            formatSubmit: 'mm/dd/yyyy'
        });
        

        $('#singleParent').on("click", function() {
            if ($(this).prop("checked") == true) {
                $('#firstparent').show();
                $('#secondparent').hide();
                $("#primary_email_parent1").prop("checked", true);
                $('#primary_email_parent2').removeAttr('checked');

                $('.parent2').removeClass('required');
                $('.parent2').parent().find('label').remove('.error');
                $('.parent2').parent().find('select').removeClass('error');

                $('#parent2_first_name').val('');
                $('#parent2_last_name').val('');
                $('#parent2_phone').val('');
                $('#parent2_email').val('');
            } else if ($(this).prop("checked") == false) {
                $('#firstparent').show();
                $('#secondparent').show();
                $('#parent2_first_name').addClass('required');
                $('#parent2_last_name').addClass('required');
                $('#parent2_phone').addClass('required');
                $('#parent2_email').addClass('required');
            }
        });

       
        $('input').on('keypress', function(e) {
            if (this.value.length === 0 && e.which === 32) {
                return false;
            }
        });

        $('#frmICK').submit(function(e) {
            e.preventDefault();

            if ($('#frmICK').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';
                $('.spinner').removeClass('hide');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg(data.msg);
                    }
                    setTimeout(function() {
                        location.reload(true);
                    }, 1000);
                }, 'json');
            }
        });
    });
</script>
<?php include "../footer.php" ?>