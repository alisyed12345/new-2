<?php
include_once "includes/config.php";
include "header_guest.php"; ?>

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

    .error {
        margin-top: 0px;
        padding-left: 12px;
    }

    .error_cust {
        padding-left: 10px;
        z-index: 0;
        display: inline-block;
        margin-bottom: 7px;
        color:
            #f44336;
        position: relative;
    }

    .page-container {
        background-color: white;
    }
    .select2 {  
width:100%!important;
}
</style>

<?php
$grades = array('KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher');
?>
<!-- Content area -->
<div class="content">
    <h2><?php echo CENTER_SHORTNAME ?> - Staff Registration Form</h2>
    <!-- Advanced login -->
    <form id="frmICK" class="form-validate-jquery" method="post">
        <div class="panel panel-flat">
            <div class="panel-body">
                <div class="ajaxMsg"></div>
                <legend class="text-semibold"><i class="icon-user position-left"></i> Personal Information</legend>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="first_name">First Name:<span class="mandatory">*</span></label>
                            <input placeholder="First Name" spacenotallow="true" id="first_name" lettersonly="true" tabindex="5" name="first_name" required class="form-control required" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="middle_name">Middle Name:</label>
                            <input placeholder="Middle Name" name="middle_name" lettersonly="true" id="middle_name"  tabindex="10" class="form-control" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="last_name">Last Name:<span class="mandatory">*</span></label>
                            <input placeholder="Last Name" spacenotallow="true" name="last_name" lettersonly="true" tabindex="15" id="last_name" class="form-control required" type="text">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="dob">Date of Birth:<span class="mandatory">*</span></label>
                            <input placeholder="Date of Birth" id="dob" name="dob" class="form-control required" tabindex="20" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Gender:<span class="mandatory">*</span></label>
                            <div class="col-md-12">
                                <label class="radio-inline">
                                    <input type="radio" required name="gender" id="gender_m" value="m" tabindex="25"> Male
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" id="gender" name="gender" id="gender_f" value="f"> Female
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <legend class="text-semibold"><i class="icon-envelop position-left"></i>Contact Information</legend>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email:<span class="mandatory">*</span></label>
                            <input placeholder="Email" id="email" emailcheck='true' name="email" required class="form-control" type="text" tabindex="30">
                            <div id="email_check"></div>
                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Primary No:<span class="mandatory">*</span></label>
                            <input placeholder="Primary No" name="mobile"  maxlength="10" PhoneNumber="true" id="mobile" class="form-control required phone_no" type="text" tabindex="30">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alternate No:</label>
                            <input placeholder="Alternate No <?php echo get_country()->alternateno ?>" maxlength="10" name="phone" PhoneNumber="true" id="phone" class="form-control phone_no" type="text" tabindex="35">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Address Line 1:<span class="mandatory">*</span></label>
                            <input placeholder="Address Line 1" name="address_1" required class="form-control" type="text" tabindex="40">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Address Line 2:</label>
                            <input placeholder="Address Line 2" name="address_2" class="form-control" type="text" tabindex="45">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>City:<span class="mandatory">*</span></label>
                            <input placeholder="City" spacenotallow="true" lettersonly="true" name="city" class="form-control required" type="text" tabindex="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>State:<span class="mandatory">*</span></label>
                            <?php $states = $db->get_results("select * from ss_state where is_active=1 and country_id = '".get_country()->country_id."' order by state asc"); ?>
                            <select class="select form-control required" name="state_id" id="state_id" tabindex="55">
                                <option value="">Select</option>
                                <?php foreach ($states as $state) { ?>
                                    <option value="<?php echo $state->id ?>"><?php echo $state->state ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="country">Country:<span class="mandatory">*</span></label>
                            <?php $countrys = $db->get_results("select * from ss_country where is_active=1 and id = '".get_country()->country_id."'"); ?>
                            <select class="select form-control required" name="country_id" id="country_id" tabindex="60">
                                <option value="">Select</option>
                                <?php foreach ($countrys as $country) { ?>
                                    <option value="<?php echo $country->id  ?>">
                                        <?php echo $country->country ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="country">ZipCode:<span class="mandatory">*</span></label>
                            <input type="text" name="post_code" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" id="post_code" value="" zipCodeCheck="true" placeholder="Zip Code" class="form-control required" tabindex="65">
                        </div>
                    </div>
                </div>
             
              
                <div class="row">
                    <div class="col-md-12">
                        <h3><strong>Terms And Conditions</strong> </h3> 
                        <p>
                            “In the name of Allah, the most beneficial and merciful”
                        </p>
                        <p>
                            Narrated 'Uthman: The Prophet said, "The best among you (Muslims) are those who learn the
                            Qur'an and teach it."
                        </p>
                        <p>
                            Narrated by Jabir ibn Abdullah: The Prophet (saws) said, “On the Day of Judgment the dearest
                            and closest to me, as regards my company, will be those persons who will bear the best moral
                            character.”
                        </p>
                        <p>
                            <strong>Attendance:</strong> Staff Member shall attend every class day except when
                            emergencies occur in which case the member must communicate with the principal no later than
                            1 hour before school start time.
                        </p>
                        <p>
                            <strong>COVID 19 Protocol:</strong> Staff must wear masks at all times, no exceptions.
                        </p>
                        <p>
                            <strong>Consensus:</strong> All changes to school policies, curriculum or related matters
                            must be discussed with the principal and the subject leads and must go through consensus of
                            the above people before being implemented.
                        </p>
                        <p>
                            <strong>Communication:</strong> Staff shall communicate openly via email, text or Whatapp
                            with other teachers or the school staff in order to facilitate decision making or to resolve
                            issues. All teachers are required to respond to requests or questions from other colleagues
                            in a timely manner.
                        </p>
                        <p>
                            <strong>Punctuality:</strong> Staff shall arrive at least 15 mins before their class/duty
                            start time. In case of teaching online, please be prepared for your class at least 10 mins
                            before its scheduled time.
                        </p>
                        <p>
                            <strong>Privacy/Confidentiality:</strong> All matters discussed regarding the school or the
                            <?php echo SCHOOL_NAME ?> are confidential and must be kept private between the members who
                            are involved. Any public release or discussion of such matters requires permission from the
                            principal who will engage the <?php echo CENTER_SHORTNAME ?> management to address any such issues as necessary.
                        </p>
                        <p>
                            <strong>Conduct:</strong> Staff members are held at a higher standard than most of the
                            community members. As such, they will abide by all <?php echo CENTER_SHORTNAME ?> policies as well as policies set
                            forth by the <?php echo CENTER_SHORTNAME.' '.SCHOOL_NAME ?>.
                        </p>
                        <p>
                            <strong>Dress Code:</strong> All staff members must abide by the Islamic dress code when at
                            <?php echo CENTER_SHORTNAME ?> regardless of the Academy hours. Male staff should wear pants or shalwars, etc. i.e. no
                            shorts. Female teachers should wear an abayah or jilbab with a head scarf. Please discuss
                            any specific questions with the administrative staff.
                        </p>
                        <p>
                            <strong>Commitment:</strong> Staff members who agree to teach or volunteer for <?php echo CENTER_SHORTNAME.' '.SCHOOL_NAME ?>
                            commit their time for all school days for the current term. All staff members must be
                            available for the entire year.
                        </p>
                        <p>
                            <strong>Safety:</strong> Staff members shall refrain from touching or harming the students.
                        </p>
                        <p>
                            <strong>Preparation:</strong> Each staff member must prepare for the class ahead of time.
                        </p>
                        <p>
                            <strong>Compliance:</strong> All staff members must agree and comply with the Code of
                            Conduct, <?php echo CENTER_SHORTNAME ?> policies, Islamic laws and procedures and the Local Laws.
                        </p>
                        <p>
                            <strong>
                                Use of Photo/Image or Video at the Academy</strong><br>
                            We take pictures or videos of students during the activities of a program for future
                            illustrative purposes and share them with the parents only.
                        </p>
                        <p>
                            I hereby grant <?php echo SCHOOL_NAME ?> Inc (<?php echo CENTER_SHORTNAME ?>) (the organization) permission to use mine,
                            or my family members (spouse or children whom I am a legal guardian of) likeness in
                            photographs, video recordings or electronic images in any and all of its publications,
                            including website entries, without payment or any other considerations. I understand and
                            agree that these materials will become the property of the organization and will not be
                            returned. I hereby irrevocably authorize the organization to edit, alter, copy, exhibit,
                            publish or distribute these images for purposes of publicizing the organization's programs
                            or for any other lawful purpose. In addition, I waive the right to inspect or approve the
                            finished product, including written or electronic copy, wherein my likeness appears.
                            Additionally, I waive any right to royalties or other compensation arising or related to the
                            use of my image. I hereby hold harmless and release and forever discharge the organization
                            from all claims, demands, and causes of action which I, my heirs, representatives,
                            executors, administrators, or any other persons acting on my behalf or on behalf of my
                            estate have or may have by reason of this authorization.
                        </p>
                        <p>
                            <strong>
                            <?php echo CENTER_SHORTNAME ?> General Release of Liability</strong><br>
                            As the parent/legal guardian of the minor(s) listed above, I hereby grant permission for the
                            student(s) to participate in the activities of the <?php echo SCHOOL_NAME ?>’ Saturday
                            Academy, IQRA’A Quran Center, or Summer School programs. I assume full responsibility for
                            any injuries or damages which may occur to these student(s), in, on, or about the premises
                            of <?php echo SCHOOL_NAME ?>, or arising out of its activities, whether occurring on the
                            premises of the center or at any other location, and do hereby fully release, indemnify,
                            discharge and hold harmless the <?php echo SCHOOL_NAME ?>, its Trustees, and all associated
                            with it, including teachers, administrators, and volunteers, from any and all claims,
                            responsibilities, liabilities, legal actions or suits, damages or losses of any kind or
                            description, both at law or in equity, arising out of, or in any way connected with, any of
                            the above-mentioned acts and activities.

                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
<br>
                        <strong>
                            <input type="checkbox" id="school_rules" style="margin-right:5px;" tabindex="70">I Agree to Above Terms & Conditions <span class="mands">*</span></strong> <br>
                        <label id="school_rules-error" class="error_cust" for="school_rules"></label>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 text-right">
                        <div class="ajaxMsgBot" style="margin-right: -110px;"></div>
                    </div>
                    <div class="col-md-2 text-right">
                        <input type="hidden" name="action" value="add_staff">
                        <button type="submit" class="btn btn-success" tabindex="80"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- /advanced login -->

</div>
<!-- /content area -->
<script src="<?php echo SITEURL ?>assets/js/jquery-ui.min.js"></script>

<script>
    function validate_radio_checkbox() {
        var retVal = true;
        if ($('#school_rules').is(':checked')) {
            //$('#school_rules-error').removeClass('error');
            $('#school_rules-error').html('');
            $('#school_rules-error').css('display', 'none');
        } else {
            //$('#school_rules-error').addClass('error');
            $('#school_rules-error').html('Required field');
            $('#school_rules-error').css('display', 'block');
            retVal = false;
        }

        return retVal;
    }

    $(document).ready(function() {
        $('#email_check').html('');
        $('input').on('keypress', function(e) {
            if (this.value.length === 0 && e.which === 32) {
                return false;
            }
        });

        <?php echo get_country()->phone_formate ?>

        $("#school_rules").change(function() {
            validate_radio_checkbox();
        });

        $('input').on('keypress', function(e) {
            if (this.value.length === 0 && e.which === 32) {
                return false;
            }
        });

        //VALIDATION - US PHONE FORMAT 
        // jQuery.validator.addMethod("phonenocheck", function(value, element) {
        //     return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);
        // }, "Enter valid phone number");


        $('#joining_date').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: true,
            max: [<?php echo date('Y') ?>, <?php echo date('m') ?>, <?php echo date('d') ?>],
            format: '<?php echo my_date_changer('d mmmm, yyyy'); ?>',
            formatSubmit: 'mm/dd/yyyy'
        });

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
 
        $('#country_id').change(function(){
            if($(this).val()){
             $('#country_id-error').empty();
            }
        })
        /*   $('#email').blur(function() {
              var email = $.trim($('#email').val());
              if (email != '') {
                  $('#email_check').html('Verifying entered email...');
                  $.post('<?php echo $SITEURL ?>ajax/ajss-staff', {
                      email: email,
                      'action': 'is_email_in_user'
                  }, function(data, status) {
                      $('#email_check').html(data);
                  });
              }
          }); */
        // jQuery.validator.addMethod("emailcheck", function(value, element) {
        //     var emailcheck = this.optional(element) || /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/i.test(value);
        //     if (emailcheck == true) {

        //         var email = $.trim($('#email').val());
        //         if (email != '') {
        //             $('#email_check').html('Verifying entered email...');
        //             $.post('<?php echo SITEURL ?>ajax/ajss-staff', {
        //                 email: email,
        //                 'action': 'is_email_in_user'
        //             }, function(data, status) {
        //                 console.log(data);
        //                 $('#email_check').html(data);
        //             });
        //             return true;
        //         }
        //         else{
        //         $('#email_check').html('');
        //         return false;
        //         }
                
        //     } else {
        //         $('#email_check').html('');
        //         return false;
        //     }
        // }, "Enter a valid email address.");


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
                        'action': 'is_email_in_user'
                    }, function(data, status) {
                        if($("#reuse_email").length == '0') {
                            $('#email_check').html(data); }
                        else{
                            $('#email_check').empty();
                                return false;
                        }
                    });
                }
                return true;
            }else {
                $('#email_check').empty();
                    return false;
            }
        })


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
            format: '<?php echo my_date_changer('d mmmm, yyyy'); ?>',
            formatSubmit: 'mm/dd/yyyy'
        });

        $('#frmICK').submit(function(e) {
            e.preventDefault();

            var val_rad_check = validate_radio_checkbox();
            //if ($('#frmICK').valid() && val_rad_check) {

            //if ($('#frmICK').valid()) {
            if ($('#frmICK').valid() && val_rad_check) {
                var targetUrl = '<?php echo $SITEURL ?>ajax/ajss-staff-registration';
                $('.spinner').removeClass('hide');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                            $('.spinner').addClass('hide');
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

        // $('#country_id').change(function() {
        //     if ($('#country_id').val() == '') {
        //         $('#state_id').html('<option value="">Select State</option>');
        //     } else {
        //         //SUBJECT
        //         $('#state_id').html('<option value="">Loading...</option>');

        //         var targetUrl = '<?php echo SITEURL ?>ajax/ajss-settings';
        //         $.post(targetUrl, {
        //             country_id: $('#country_id').val(),
        //             action: 'fetch_state'
        //         }, function(data, status) {
        //             if (status == 'success' && data != '') {
        //                 $('#state_id').html('<option value="">Select State</option>');
        //                 $('#state_id').append(data);
        //             } else {
        //                 $('#state_id').html('<option value="">State not found</option>');
        //             }
        //         });

        //     }
        // });
    });
</script>
<?php include "footer.php" ?>