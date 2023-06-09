<?php
$mob_title = "Add Staff";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_staff_create", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}
?>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Add New Staff Member</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Add New Staff Member</li>
        </ul>

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
                        <legend class="text-semibold"><i class="icon-user position-left"></i> Personal Information</legend>
                        <!-- <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="staff_number">Staff Number:</label>
                                    <input placeholder="Staff Number" id="staff_number" name="staff_number" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="joining_date">Joining Date:</label>
                                    <input placeholder="Joining Date" name="joining_date" id="joining_date" class="form-control" type="text">
                                </div>
                            </div>                            
                        </div> -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="first_name">First Name:<span class="mandatory">*</span></label>
                                    <input placeholder="First Name" spacenotallow="true" id="first_name" lettersonly="true" name="first_name" required class="form-control required" type="text" tabindex="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="middle_name">Middle Name:</label>
                                    <input placeholder="Middle Name" name="middle_name" lettersonly="true" id="middle_name" class="form-control" type="text" tabindex="2">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="last_name">Last Name:<span class="mandatory">*</span></label>
                                    <input placeholder="Last Name" spacenotallow="true" name="last_name" lettersonly="true" id="last_name" class="form-control required" type="text" tabindex="3">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dob">Date of Birth:<span class="mandatory">*</span></label>
                                    <input placeholder="Date of Birth" id="dob" name="dob" class="form-control required" type="text" tabindex="4">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gender:<span class="mandatory">*</span></label>
                                    <div class="col-md-12">
                                        <label class="radio-inline">
                                            <input type="radio" required name="gender" id="gender_m" value="m" tabindex="5"> Male
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" id="gender" name="gender" id="gender_f" value="f" tabindex="6"> Female
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Staff Role:<span class="mandatory">*</span></label>
                                    <?php $userType = $db->get_results("select *,ss_usertype.id as usertype_id from ss_usertype INNER JOIN ss_role ON ss_role.id=ss_usertype.role_id where status='1' and is_active=1 and is_default = '0'"); ?>
                                    <!--<select class="select form-control required" name="user_type" id="user_type">-->
                                    <select class="bootstrap-select" multiple="multiple" data-width="100%" id="user_type" name="user_type[]" required tabindex="7">
                                        <option value="All">Select All</option>
                                        <?php foreach ($userType as $type) { ?>
                                            <option value="<?php echo $type->usertype_id ?>"><?php echo $type->user_type ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <!-- <label>Username:<span class="mandatory">*</span></label>
                                    <input placeholder="Username" name="username" usernameCheck="true" required id="username" class="form-control" type="text"> -->
                                    <label>Email:<span class="mandatory">*</span></label>
                                    <input placeholder="Email" id="email"  name="email" emailcheck='true' class="form-control email required" type="text" spacenotallow="true" tabindex="8">
                                    <div id="email_check"></div>
                                </div>

                            </div>
                            <!-- <div class="col-md-4" id="password_col">
                                <div class="form-group">
                                    <label>Password:<span class="mandatory">*</span></label>
                                    <input placeholder="Password" name="password" passwordCheck="true" required id="password" class="form-control" type="password">
                                </div>
                            </div>
                            <div class="col-md-4" id="confirm_password_col">
                                <div class="form-group">
                                    <label>Confirm Password:<span class="mandatory">*</span></label>
                                    <input placeholder="Confirm Password" required equalTo="#password" name="confirm_password" id="confirm_password" class="form-control" type="password">
                                </div>
                            </div> -->

                        </div>
                        <legend class="text-semibold"><i class="icon-envelop position-left"></i>Contact Information</legend>
                        <div class="row">
                            <!-- <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email:<span class="mandatory">*</span></label>
                                    <input placeholder="Email" name="email" required class="form-control" type="email">
                                </div>
                            </div> -->

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Primary No:<span class="mandatory">*</span></label>
                                    <input placeholder="Primary No" name="mobile" required maxlength="10" PhoneNumber="true" id="mobile" class="form-control phone_no" type="text" tabindex="9">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Alternate No:</label>
                                    <input placeholder="Alternate No" maxlength="12" name="phone" PhoneNumber="true" id="phone" class="form-control phone_no" type="text" tabindex="10">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Address Line 1:<span class="mandatory">*</span></label>
                                    <input placeholder="Address Line 1" spacenotallow="true" name="address_1" required class="form-control" type="text" tabindex="11">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Address Line 2:</label>
                                    <input placeholder="Address Line 2" name="address_2" class="form-control" type="text" tabindex="12">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>City:<span class="mandatory">*</span></label>
                                    <input placeholder="City" spacenotallow="true" lettersonly="true" name="city" class="form-control required" type="text" tabindex="13">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>State:<span class="mandatory">*</span></label>
                                    <?php $states = $db->get_results("select * from ss_state where is_active=1 and country_id = '".get_country()->country_id."' order by state asc"); ?>
                                    <select class="bootstrap-select required" data-width="100%" name="state_id" id="state_id" tabindex="14">
                                        <option value="">Select</option>
                                        <?php foreach ($states as $state) { ?>
                                            <option value="<?php echo $state->id ?>"><?php echo $state->state ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Country:<span class="mandatory">*</span></label>
                                    <?php $countries = $db->get_results("select * from ss_country where is_active = 1  and id = '".get_country()->country_id."'");?>
                                    <select class="form-control required" name="country_id" id="country_id" tabindex="15">
                                        <option value="">Select</option>
                                        <?php foreach ($countries as $country) { ?>
                                            <option value="<?php echo $country->id; ?>"><?php echo $country->country; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">ZipCode:<span class="mandatory">*</span></label>
                                    <input type="text" name="post_code" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" id="post_code" value="" zipCodeCheck="true" placeholder="Zip Code" class="form-control required" tabindex="16">
                                </div>
                            </div>
                        </div>
                        <?php /*?><legend class="text-semibold"><i class="icon-camera position-left"></i>Photo</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Photo:</label>
                                    <input type="file" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">

                                </div>
                            </div>
                        </div><?php */ ?>

                        <div class="row">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right" >
                                <input type="hidden" name="action" value="add_staff" >

                                <input type="submit" class="btn btn-success" tabindex="17" value="Submit"><i class="icon-spinner2 spinner hide marR10 insidebtn" ></i> 
<!-- <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" ></i> Submit</button>
    -->
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
    $(function() {
        $("#email").keyup(function(){
            $('#email_check').empty();
            var value= $('#email').val();
            var emailcheck =  /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/i.test(value);
            if (emailcheck == true) {
                var email = $.trim($('#email').val());
                if (email == '') {
                    $('#email_check').empty();
                }
            }
            else {
                $('#email_check').empty();
            }
        }); 
     
        <?php echo get_country()->phone_formate ?>
        //VALIDATION - US PHONE FORMAT 
        // jQuery.validator.addMethod("phonenocheck", function(value, element) {
        //     return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);
        // }, "Enter valid phone number");

        

        $('#dob').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: 100,
            min: [<?php echo date('Y') - 100 ?>, 01, 01],
            max: [<?php echo date('Y') - 15 ?>, 12, 31],
            /*max: [<?php echo date('Y') - 15 ?>,12,31],*/
            /*min: new Date(1991,1,1),
            max: new Date(2001,11,31),*/
            format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
            formatSubmit: 'yyyy-mm-dd'
        });

        $('#joining_date').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: true,
            max: [<?php echo date('Y') ?>, <?php echo date('m') ?>, <?php echo date('d') ?>],
            format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
            formatSubmit: 'yyyy-mm-dd'
        });



        $('#frmICK').submit(function(e) {
            e.preventDefault();

            if ($('#frmICK').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff';
                $('.spinner').removeClass('hide');

                var formDate = $(this).serialize();

                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                            $("#frmICK")[0].reset();
                            $('#email_check').html('');
                            $('#user_type').selectpicker('refresh');

                            $('.select').change();
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg(data.msg);
                    }
                }, 'json');
            }
        });

        $('#email').focusout(function(){
            $('#email_check').empty();
            var value= $('#email').val();
            var emailcheck =  /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/i.test(value);
            if (emailcheck == true) {
                var email = $.trim($('#email').val());
                if (email != '') {
                    $('#email_check').html('Verifying entered email...');
                    $.post('<?php echo SITEURL ?>ajax/ajss-staff', {
                        email: email,
                        'action': 'is_email_in_user_internal'
                    }, function(data, status) {
                        if($("#reuse_email").length == '0') {
                        $('#email_check').html(data); }
                        else{
                            $('#email_check').empty();
                                return false;
                        }
                    });
                }else{
                    $('#email_check').empty();
                    return false;
                }
                return true;
            }
            else {
                $('#email_check').empty();
                    return false;
            }
        })

        
        $('#user_type').change(function() {
            var checkele = $('#user_type').val();
            if (checkele !== '') {
                $("#user_type-error").empty();
            }

        })

        $('#user_type').selectpicker().change(function() {
            toggleSelectAll($(this));
        }).trigger('change');

        $(document).on('change', '#reuse_email', function() {
            $('#password').val('');
            $('#confirm_password').val('');

            if ($('#reuse_email').is(':checked')) {
                $('#password_col').addClass('hide');
                $('#password').removeAttr('required');
                $('#confirm_password_col').addClass('hide');
                $('#confirm_password').removeAttr('required');
            } else {
                $('#password_col').removeClass('hide');
                $('#password').attr('required', 'required');
                $('#confirm_password_col').removeClass('hide');
                $('#confirm_password').attr('required', 'required');
            }
        });

        // $('#country_id').change(function() {

        // if ($('#country_id').val() == '') {
        //     $('#state_id').html('<option value="">Select State</option>');
        // } else {
        //     //SUBJECT
        //     $('#state_id').html('<option value="">Loading...</option>');

        //     var targetUrl = '<?php echo SITEURL ?>ajax/ajss-settings';
        //     $.post(targetUrl, {
        //         country_id: $('#country_id').val(),
        //         action: 'fetch_state'
        //     }, function(data, status) {
        //         if (status == 'success' && data != '') {
        //             $('#state_id').html('<option value="">Select State</option>');
        //             $('#state_id').append(data);
        //         } else {
        //             $('#state_id').html('<option value="">State not found</option>');
        //         }
        //     });

        // }
        // });


    });

    function toggleSelectAll(control) {
            var allOptionIsSelected = (control.val() || []).indexOf("All") > -1;

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
                    control.selectpicker('val', valuesOf(control.find('option:selected[value!=All]')));
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