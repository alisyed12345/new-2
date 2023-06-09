<?php
$mob_title = "Edit Family Info";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_family_edit", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}

if (isset($_GET['id'])) {
    $student_user_id = trim($_GET['id']);
} else {
    $student_user_id = $db->get_var('select user_id from ss_student where family_id = "' . $_GET['fid'] . '"');
}

$group_id = $db->get_var("select group_id from ss_studentgroupmap where student_user_id='" . $student_user_id . "' order by id desc limit 1");

//GROUP LEVEL CHECK, SHEIKH CAN VIEW DETAILS OF HIS STUDENTS ONLY
/*if(check_userrole_by_code('UT02')){
	if(!in_array($group_id,$_SESSION['ss_assigned_groups'])){
		include "../includes/unauthorized_msg.php";
		return;
	}  
}*/

$user = $db->get_row("select * from ss_user where id='" . $student_user_id . "'");
$student = $db->get_row("select * from ss_student where user_id='" . $student_user_id . "'");
$family = $db->get_row("select * from ss_family where id='" . $student->family_id . "'");
$group = $db->get_var("select group_name from ss_groups where id='" . $group_id . "'");


$stugroupclass = $db->get_results("select g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $_GET['id'] . "' order by m.id desc");

?>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Edit Family Info</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a>
            </li>

            <?php if (isset($_GET['from']) && $_GET['from'] = 'student') { ?>
                <li><a href="<?php echo SITEURL . "student/students_list" ?>">Student</a></li>
            <?php } else { ?>
                <li><a href="<?php echo SITEURL . "student/family_list" ?>">Family View</a></li>
            <?php } ?>
            <li class="active">Edit Family Info</li>
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

                        <!--                         <legend class="text-semibold"><i class="icon-user position-left"></i> Student Information
                        </legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Student Name:<span class="mandatory">*</span></label>
                                    <input readonly="readonly"
                                        value="<?php echo $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name ?>"
                                        class="form-control" type="text">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                             <?php foreach ($stugroupclass as $row) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><strong>Class:</strong></label>
                                    <span><?php echo $row->class_name; ?></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><strong>Group:</strong></label>
                                    <span><?php echo $row->group_name; ?></span>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <br> -->
                        <legend class="text-semibold"><i class="icon-user position-left"></i> Parents Information
                        </legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>1st Parent First Name:<span class="mandatory">*</span></label>
                                    <input placeholder="1st Parent First Name" spacenotallow="true" required lettersonly="true" name="father_first_name" value="<?php echo $family->father_first_name ?>" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>1st Parent Last Name: <span class="mandatory">*</span></label>
                                    <input placeholder="1st Parent Last Name" spacenotallow="true" lettersonly="true" value="<?php echo $family->father_last_name ?>" name="father_last_name" class="form-control required" type="text">
                                </div>
                            </div>
                            <!-- <div class="col-md-3">
                <div class="form-group">
                  <label>1st Parent Area Code:</label>
                  <input placeholder="1st Parent Area Code" value="<?php echo $family->father_area_code ?>" name="father_area_code" class="form-control" type="text">
                </div>
              </div> -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>1st Parent Phone:<span class="mandatory">*</span></label>
                                    <input placeholder="1st Parent Phone" PhoneNumber="true" value="<?php echo internal_phone_check($family->father_phone, 'edit') ?>" name="father_phone" class="form-control required phone_no" maxlength="10" id="father_phone" type="text">
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($family->mother_first_name) && !empty($family->mother_last_name)) { ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>2nd Parent First Name:<span class="mandatory">*</span></label>
                                        <input spacenotallow="true" placeholder="2nd Parent First Name" required lettersonly="true" name="mother_first_name" value="<?php echo $family->mother_first_name ?>" class="form-control required" type="text">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>2nd Parent Last Name:<span class="mandatory">*</span></label>
                                        <input spacenotallow="true" placeholder="2nd Parent Last Name" required lettersonly="true" value="<?php echo $family->mother_last_name ?>" name="mother_last_name" class="form-control" type="text">
                                    </div>
                                </div>
                                <!-- <div class="col-md-3">
                <div class="form-group">
                  <label>2nd Parent Area Code:</label>
                  <input placeholder="2nd Parent Area Code" value="<?php echo $family->mother_area_code ?>" name="mother_area_code" class="form-control" type="text">
                </div>
              </div> -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>2nd Parent Phone:<span class="mandatory">*</span></label>
                                        <input placeholder="2nd Parent Phone" PhoneNumber="true" value="<?php echo internal_phone_check($family->mother_phone, 'edit') ?>" name="mother_phone" maxlength="10" class="form-control phone_no" id="mother_phone" type="text">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Password Reset Link:</label><br>
                                    <?php $password_reset_link = SITEURL . "new_password.php?id=" . md5('iCjC' . $family->user_id . '1cjc') ?>
                                    <a href="<?php echo $password_reset_link ?>" target="_blank"><?php echo $password_reset_link ?></a>
                                </div>
                            </div>
                        </div>
                        <br />
                        <legend class="text-semibold"><i class="icon-envelop position-left"></i>Emails</legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Primary Email:<span class="mandatory">*</span></label>
                                    <input placeholder="Primary Email" class="form-control email" required name="primary_email" value="<?php echo $family->primary_email ?>" type="text">
                                    <?php if(strtolower($family->primary_contact) == "father" && empty($family->secondary_email) && !empty($family->primary_email)){ ?>
                                    <input type="hidden" name="only_primary" value="Father">
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Secondary Email: <?php if ($family->primary_contact == 'Mother') { ?> <span class="mandatory">*</span> <?php } ?> <?php if (!empty($family->secondary_email)) { ?> <input type="checkbox" name="primary"> Set As Primary <?php } ?></label>
                                    <input placeholder="Secondary Email" class="form-control email <?php if ($family->primary_contact == 'Mother') { ?> required <?php } ?>" name="secondary_email" value="<?php echo $family->secondary_email ?>" type="text">
                                </div>
                            </div>
                        </div>
                        <br />
                        <legend class="text-semibold"><i class="icon-address-book position-left"></i>Address
                        </legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Address Line 1:<span class="mandatory">*</span></label>
                                    <input placeholder="Address Line 1" spacenotallow="true" class="form-control" required name="billing_address_1" id="billing_address_1" value="<?php echo $family->billing_address_1 ?>" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Address Line 2:</label>
                                    <input placeholder="Address Line 2" class="form-control" name="billing_address_2" id="billing_address_2" value="<?php echo $family->billing_address_2 ?>" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>City:<span class="mandatory">*</span></label>
                                    <input placeholder="City" spacenotallow="true" class="form-control" required name="billing_city" id="billing_city" value="<?php echo $family->billing_city ?>" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>State:<span class="mandatory">*</span></label>
                                    <?php
                                    $states = $db->get_results("select * from ss_state where is_active=1 and country_id = '".get_country()->country_id."' order by state asc"); ?>
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Country:<span class="mandatory">*</span></label>
                                    <?php $countries = $db->get_results("select * from ss_country where is_active = 1  and id = '".get_country()->country_id."'");?>
                                    <select class="select form-control required" name="billing_country_id" id="billing_country_id">
                                    <?php foreach ($countries as $country) { ?>
                                        <option value="<?php echo $country->id; ?>"><?php echo $country->country; ?></option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Zipcode:<span class="mandatory">*</span></label>
                                    <input placeholder="Zipcode" class="form-control required" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" name="billing_post_code" id="billing_post_code" zipCodeCheck="true" value="<?php echo $family->billing_post_code ?>" type="text">
                                </div>
                            </div>
                        </div> 


                        <!--  <br />
                       <legend class="text-semibold">
                           <i class="icon-address-book position-left"></i>Shipping Address
                           <div class="pull-right">
                               <input type="checkbox" id="same_as_billing_ad" />
                               Copy billing address</div>
                       </legend>
                       <div class="row">
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>Address Line 1:<span class="mandatory">*</span></label>
                                   <input placeholder="Address Line 1" class="form-control" required
                                       name="shipping_address_1" id="shipping_address_1"
                                       value="<?php echo $family->shipping_address_1 ?>" type="text">
                               </div>
                           </div>
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>Address Line 2:</label>
                                   <input placeholder="Address Line 2" class="form-control" name="shipping_address_2"
                                       id="shipping_address_2" value="<?php echo $family->shipping_address_2 ?>"
                                       type="text">
                               </div>
                           </div>
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>City:</label>
                                   <input placeholder="City" class="form-control" required name="shipping_city"
                                       id="shipping_city" value="<?php echo $family->shipping_city ?>" type="text">
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>State:<span class="mandatory">*</span></label>
                                   <?php $states = $db->get_results("select * from ss_state where is_active=1"); ?>
                                   <select class="select form-control required" name="shipping_state_id"
                                       id="shipping_state_id">
                                       <option value="">Select</option>
                                       <?php foreach ($states as $state) { ?>
                                       <option value="<?php echo $state->id ?>"
                                           <?php echo $family->shipping_state_id == $state->id ? 'selected="selected"' : '' ?>>
                                           <?php echo $state->state ?></option>
                                       <?php } ?>
                                   </select>
                               </div>
                           </div>
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>Entered State:</label>
                                   <input placeholder="Entered State" class="form-control" required
                                       name="shipping_entered_state" id="shipping_entered_state"
                                       value="<?php echo $family->shipping_entered_state ?>" type="text">
                               </div>
                           </div>
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>Country:</label>
                                   <?php $countrys = $db->get_results("select * from ss_country where is_active=1 and id = '".get_country()->country_id."'"); ?>
                                   <select class="select form-control required" name="shipping_country_id"
                                       id="shipping_country_id">
                                       <option value="">Select</option>
                                       <?php foreach ($countrys as $country) { ?>
                                       <option value="<?php echo $country->id ?>"
                                           <?php echo $family->shipping_country_id == $country->id ? 'selected="selected"' : '' ?>>
                                           <?php echo $country->country ?></option>
                                       <?php } ?>
                                   </select>
                               </div>
                           </div>
                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>Postcode:</label>
                                   <input placeholder="Postcode" class="form-control" name="shipping_post_code"
                                       id="shipping_post_code" value="<?php echo $family->shipping_post_code ?>"
                                       type="text">
                               </div>
                           </div>
                       </div> -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Comments: </label>
                                    <textarea type="text" name="comments" class="form-control"><?php echo $family->comments ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="action" value="edit_family">
                                <input type="hidden" name="user_id" value="<?php echo $student_user_id ?>">
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
        <?php echo get_country()->phone_formate ?>
        $('input').on('keypress', function(e) {
            if (this.value.length === 0 && e.which === 32) {
                return false;
            }
        });

        $('#frmICK').submit(function(e) {
            e.preventDefault();

            if ($('#frmICK').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
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

        $('#same_as_billing_ad').change(function() {
            if ($(this).is(':checked')) {
                $('#shipping_address_1').val($('#billing_address_1').val());
                $('#shipping_address_2').val($('#billing_address_2').val());
                $('#shipping_city').val($('#billing_city').val());
                $('#shipping_state_id').val($('#billing_state_id').val());
                $('#shipping_entered_state').val($('#billing_entered_state').val());
                $('#shipping_country_id').val($('#billing_country_id').val());
                $('#shipping_post_code').val($('#billing_post_code').val());
            } else {
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
    });
</script>
<?php include "../footer.php" ?>