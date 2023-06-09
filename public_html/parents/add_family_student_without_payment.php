<?php
include_once "../includes/config.php";
$mob_title = "Add New Family";
include "../header.php";

$_SESSION['token']  = genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD);
$request_token  = 'req_'.RandomString();
if(!empty(get_country()->currency)){
    $currency = get_country()->currency;
}else{
    $currency = '';
}
//AUTHARISATION CHECK - 
if (!in_array("su_add_new_family", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    exit;
} 

    $check = $db->get_row("select is_new_registration_open, new_registration_start_date, new_registration_end_date, is_new_registration_free,
    new_registration_fees_form_head, new_registration_fees, registration_page_termsncond, school_name, is_waiting from ss_client_settings where status = 1 AND new_registration_session = '" .$_SESSION['icksumm_uat_CURRENT_SESSION']. "'");

    $ramount = round($check->new_registration_fees, 0);
    if ($check->new_registration_fees_form_head == 0 && $check->is_new_registration_free == 0) {
        $amount = $ramount;
        $is_form_fee = 1;
    } elseif ($check->new_registration_fees_form_head == 1 && $check->is_new_registration_free == 0) {
        $amount = $ramount;
        $is_form_fee = 0;
    }

$grades = array('KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher');
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

    lable.error {
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
</style>
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Add New Family</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Add New Family</li>
        </ul>
    </div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
    <div class="panel panel-flat">
        <div class="panel-body">

            <h5>Add New Family </h5>
            <!-- Advanced login -->
            <form name="frm_register" class="reg_form mt-20" id="frm_register" method="post">
                <div class="row">
                    <div class="col-md-12"> <strong>First Student<span class="mands">*</span></strong> </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" spacenotallow="true" name="child1_first_name" lettersonly="true" tabindex="5" id="child1_first_name" maxlength="25" placeholder="First Name" lettersonly="true" value="" class="form-control required">
                    </div>
                    <div class="col-md-2">
                        <input type="text" spacenotallow="true" name="child1_last_name" lettersonly="true" tabindex="10" id="child1_last_name" maxlength="25" placeholder="Last Name" value="" class="form-control required" lettersonly="true">
                    </div>
                    <div class="col-md-2">
                        <select name="child1_gender" id="child1_gender" tabindex="15" class="form-control required">
                            <option value="">Select Gender</option>
                            <option value="m">Male
                            </option>
                            <option value="f">Female
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child1_dob" id="child1_dob" tabindex="20" value="" placeholder="Date of Birth" class="form-control required datepicker bgcolor-white">
                    </div>
                    </dev>


                    <div class="col-md-2">
                        <input type="text" name="child1_allergies" id="child1_allergies" maxlength="50" tabindex="25" placeholder="Enter Allergies" class="form-control required">
                    </div>

                    <div class="col-md-2">
                        <select name="child1_grade" class="form-control required" tabindex="30">
                            <option value="">Select Grade</option>
                            <?php foreach ($grades as $garde) { ?>
                                <option value="<?= $garde ?>"><?= $garde ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row mar-top-20">
                    <div class="col-md-12">
                        <input type="checkbox" name="secondstudent" id="tsecondstudent">
                        <strong>Second Student</strong>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="child2_first_name" spacenotallow="true" lettersonly="true" tabindex="40" placeholder="First Name" maxlength="25" value="" class="form-control child2 ch2 required" lettersonly="true">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child2_last_name" spacenotallow="true" lettersonly="true" tabindex="45" placeholder="Last Name" maxlength="25" value="" class="form-control child2 ch2 required" lettersonly="true">
                    </div>
                    <div class="col-md-2">
                        <select name="child2_gender" class="form-control child2 ch2 required" tabindex="50">
                            <option value="">Select Gender</option>
                            <option value="m">Male
                            </option>
                            <option value="f">Female
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child2_dob" tabindex="55" placeholder="Date of Birth" value="" class="form-control datepicker child2 bgcolor-white ch2 required">
                    </div>


                    <div class="col-md-2">
                        <input type="text" name="child2_allergies" id="child2_allergies" maxlength="50" tabindex="60" placeholder="Enter Allergies" class="form-control ch2 required">
                    </div>

                    <div class="col-md-2">
                        <select name="child2_grade" class="form-control child2 ch2 required" tabindex="65">
                            <option value="">Select Grade</option>
                            <?php foreach ($grades as $garde) { ?>
                                <option value="<?= $garde ?>"><?= $garde ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row mar-top-20">
                    <div class="col-md-12">
                        <input type="checkbox" name="thirdstudent" id="dthirdstudent">
                        <strong>Third Student</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="child3_first_name" spacenotallow="true" lettersonly="true" placeholder="First Name" maxlength="25" tabindex="75" value="" class="child3 form-control ch3 required" lettersonly="true">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child3_last_name" spacenotallow="true" lettersonly="true" placeholder="Last Name" maxlength="25" value="" class="child3 form-control ch3 required" lettersonly="true" tabindex="80">
                    </div>
                    <div class="col-md-2">
                        <select name="child3_gender" class="child3 form-control ch3 required" tabindex="85">
                            <option value="">Select Gender</option>
                            <option value="m">Male
                            </option>
                            <option value="f">Female
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child3_dob" placeholder="Date of Birth" value="" tabindex="90" class="child3 form-control datepicker bgcolor-white ch3 required">
                    </div>

                    <div class="col-md-2">
                        <input type="text" name="child3_allergies" id="child3_allergies" maxlength="50" tabindex="95" placeholder="Enter Allergies" class="form-control ch3 required">
                    </div>

                    <div class="col-md-2">
                        <select name="child3_grade" class="form-control child3 ch3 required" tabindex="100">
                            <option value="">Select Grade</option>
                            <?php foreach ($grades as $garde) { ?>
                                <option value="<?= $garde ?>"><?= $garde ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>


                <div class="row mar-top-20">
                    <div class="col-md-12">
                        <input type="checkbox" name="fourtstudent" id="dfourthstudent">
                        <strong>Fourth Student</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="child4_first_name" spacenotallow="true" lettersonly="true" placeholder="First Name" maxlength="25" tabindex="101" value="" class="child4 form-control ch4 required" lettersonly="true">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child4_last_name" spacenotallow="true" lettersonly="true" placeholder="Last Name" maxlength="25" value="" class="child4 form-control ch4 required" lettersonly="true" tabindex="102">
                    </div>
                    <div class="col-md-2">
                        <select name="child4_gender" class="child4 form-control ch4 required" tabindex="103">
                            <option value="">Select Gender</option>
                            <option value="m">Male
                            </option>
                            <option value="f">Female
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="child4_dob" placeholder="Date of Birth" value="" tabindex="104" class="child4 form-control datepicker bgcolor-white ch4 required">
                    </div>

                    <div class="col-md-2">
                        <input type="text" name="child4_allergies" id="child4_allergies" maxlength="50" tabindex="105" placeholder="Enter Allergies" class="form-control ch4 required">
                    </div>

                    <div class="col-md-2">
                        <select name="child4_grade" class="form-control child4 ch4 required" tabindex="106">
                            <option value="">Select Grade</option>
                            <?php foreach ($grades as $garde) { ?>
                                <option value="<?= $garde ?>"><?= $garde ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>



                <div class="row" style="margin-top:15px;">
                    <div class="col-md-3">
                        <h3 class="subtitle">Family Information</h3>
                    </div>
                    <div class="col-md-2"><label class="checkbox-inline" style="margin-top: 25px;"><input type="checkbox" tabindex="110" name="singleParent" id="singleParent"> Single Parent </label>
                    </div>
                </div>
                <!-- <div class="row">
        
        </div> -->
                <div id="firstparent" style="display:block;">
                    <div class="row">
                        <div class="col-md-12"> <strong>1st Parent Information<span class="mands">*</span></strong> </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" spacenotallow="true" name="credit_holder_first_name" maxlength="25" value="" id="parent1_first_name" tabindex="115" placeholder="First Name" class="form-control required" lettersonly="true">
                        </div>
                        <div class="col-md-2">
                            <input type="text" spacenotallow="true" name="credit_holder_last_name" maxlength="25" value="" id="parent1_last_name" tabindex="120" placeholder="Last Name" class="form-control required" lettersonly="true">
                        </div>
                        <div class="col-md-2">
                            <input type="text" tabindex="125" name="credit_holder_phone" value="" id="parent1_phone" PhoneNumber="true" maxlength="10" placeholder="Phone Number" class="form-control required phone_no">
                        </div>
                        <div class="col-md-3">
                            <input type="text" tabindex="130" name="credit_holder_email" value="" value="" id="parent1_email" placeholder="1st Parent E-mail" class="form-control required email">
                        </div>
                        <div class="col-md-3">
                            <input type="radio" checked="" tabindex="135" name="which_is_primary_email" value="parent1" id="primary_email_parent1"> Primary Contact
                        </div>
                    </div>
                </div>
                <br>
                <div id="secondparent" style="display:block;">
                    <div class="row">
                        <div class="col-md-12"> <strong>2nd Parent Information<span class="mands">*</span></strong> </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" spacenotallow="true" name="parent2_first_name" maxlength="25" value="" tabindex="140" id="parent2_first_name" placeholder="First Name" class="form-control parent2 required " lettersonly="true">
                        </div>
                        <div class="col-md-2">
                            <input type="text" spacenotallow="true" name="parent2_last_name" maxlength="25" value="" tabindex="145" id="parent2_last_name" placeholder="Last Name" class="form-control parent2 required" lettersonly="true">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="parent2_phone" value="" id="parent2_phone" tabindex="150" PhoneNumber="true" maxlength="10" placeholder="Phone Number" class="form-control parent2 required phone_no">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="parent2_email" value="" tabindex="155" id="parent2_email" placeholder="2nd Parent  E-mail" class="form-control parent2 email required">
                            <input type="hidden" name="primary_email" id="primary_email" tabindex="160">
                            <input type="hidden" name="secondary_email" id="secondary_email" tabindex="165">
                        </div>
                        <div class="col-md-3">
                            <input type="radio" name="which_is_primary_email" tabindex="170" value="parent2" id="primary_email_parent2"> Primary Contact
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:15px;">
                    <div class="col-md-12">
                        <h3 class="subtitle">Address Information</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"> <strong>Home Address <span class="mands">*</span></strong> </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" spacenotallow="true" name="address_1" id="address_1" value="" maxlength="200" tabindex="185" placeholder="Address Line 1" class="form-control required">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="address_2" maxlength="200" value="" placeholder="Address Line 2" tabindex="190" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <input type="text" spacenotallow="true" name="city" id="city" maxlength="45" value="" placeholder="City" class="form-control required" lettersonly="true" tabindex="195">
                    </div>
                    <div class="col-md-3">
                        <?php $states = $db->get_results("select * from ss_state where is_active = 1 and country_id = '".get_country()->country_id."' order by state asc"); ?>
                        <select name="state" id="state_id" tabindex="200" class="required form-control">
                            <option value="">Select State</option>
                            <?php foreach ($states as $st) { ?>
                                <option value="<?php echo  $st->id; ?>"><?php echo  $st->state; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">

                    <div class="col-md-3">
                        <input type="text" name="post_code" maxlength="<?php echo (!empty(get_country()->length))?get_country()->length:'5' ?>" id="post_code" value="" zipCodeCheck="true" placeholder="Zip Code" class="form-control required" tabindex="205">
                    </div>
                    <div class="col-md-3">
                    <?php $countries = $db->get_results("select * from ss_country where is_active = 1  and id = '".get_country()->country_id."'");?>
                        <select name="country_id" id="country_id" class="form-control required" tabindex="210">
                            <option value="">Select Country</option>
                            <?php foreach ($countries as $country) { ?>
                                <option value="<?php echo $country->id; ?>"><?php echo $country->country; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check">
                            <label class="form-check-label" for="check1">
                                <input type="checkbox" class="form-check-input payment_columns" id="check1" name="option1"> Instant Payment.
                            </label>
                        </div>
                    </div>
                </div>

            <div class="hide" id="modal_payment">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="subtitle">Payment Information</h3>
                </div>
            </div>
            <div class="row" id="tr_credit_card">

            <input type="hidden" name="credit_card_type" id="credit_card_type">

            <div class="col-md-2"> <strong>Credit Card Number <span class="mands">*</span></strong>
                <input placeholder="Credit Card No" maxlength="16" creditCardNoCheck="true" value="" id="credit_card_no"
                    name="credit_card_no" class="form-control cc_valid" type="text">
            </div>
            <div class="col-md-2"> <strong>Expiration Month <span class="mands">*</span></strong>
                <select name="exp_month" creditCardExpMonthCheck="true" id="credit_card_exp_month"
                    class="form-control cc_valid">
                    <option value="">Select Month</option>
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                    <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>">
                        <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-2">
                <strong>Expiration Year <span class="mands">*</span></strong>

                <select name="exp_year" creditCardExpYearCheck="true" id="credit_card_exp_year"
                    class="form-control cc_valid">
                    <option value="">Select Year</option>
                    <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++) { ?>
                    <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>">
                        <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-1"> <strong>CVV <span class="mands">*</span></strong>
                <input placeholder="CVV" maxlength="4" disabled name="cvv_no" id="cvv" checkCreditCardCVV="credit_card_type"
                    value="" class="form-control cc_valid" type="text">
            </div>

            <div class="col-md-2"> 
                <strong>Registration Fee </strong>
                <?php
                ?>
                <p><strong id="amount_show" style="font-size: 25px;letter-spacing: 0.014em;font-weight: 500;"><?= $currency.$amount; ?></strong></p>
                <input type="hidden" name="amount" id="amount" value="<?=$amount; ?>">
            </div> 

        </div>
        </div>
        <br>

                <div class="row" style="margin-bottom:50px;">
                    <div class="col-md-10 col-xs-8">
                        <div class="ajaxMsgBot pull-right"></div>
                    </div>
                    <div class="col-md-2 col-xs-4">
                    <input type="hidden" name="action" value="student_register">
                    <input type="hidden" name="registerd_by" value="Admin">
                    <input type="hidden" name="internal_registration" value="1">
                    <input type="hidden" id="frequency_type" name="frequency_type" value='onetime'>
                    <input type="hidden" name="session" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION'] ?>"/>
                    <input type="hidden" name="session_text" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] ?>"/>
                    <input type="hidden" name="auth_token" value="<?=$_SESSION['token']?>" />
                    <input type="hidden" name="request_token" value="<?php echo $request_token ?>" />
                    <input type="hidden" name="system_ip" value="<?=$_SERVER['REMOTE_ADDR']?>" />
                    <input type="hidden" name="is_waiting" value="0" />
                        <input type="submit" value="Submit" class="btn btn-success btn-block btnsubmit" tabindex="225">
                    </div>
                </div>
                <input type="hidden" name="form_submit" value="1">
            </form>
            <!-- /advanced login -->

        </div>
        <!-- /content area -->
    </div>
</div>



<!-- /Content area -->
<script src="<?php echo SITEURL ?>assets/js/jquery-ui.min.js"></script>
<!-- <script src="<?php echo SITEURL ?>assets/js/jquery-ui-timepicker-addon.js"></script> -->
<script type="text/javascript">
    function child2_child3(clsName) {
        var counter = 0;
        $('.' + clsName).each(function(index, element) {
            if ($(this).val() == '') {
                counter++;
            }
        });

        if (counter < 5) {
            $('.' + clsName).addClass('required');
        } else {
            $('.' + clsName).removeClass('required');
            $('.' + clsName).parent().find('label').remove('.error');
            $('.' + clsName).parent().find('select').removeClass('error');
        }
    }

    jQuery(document).ready(function() {
        //cardFormValidate();
        $('.cc_valid').removeClass('required');
        $('.cc_valid').val('');

        $('input').on('keypress', function(e) {
            if (this.value.length === 0 && e.which === 32) {
                return false;
            }
        });

        $(document).on('click', '.payment_columns', function() {
            if($('.payment_columns').is(':checked')){
                $('#modal_payment').removeClass('hide');
                $('.cc_valid').addClass('required');

                //VALIDATION - CREDIT CARD NUMBER
                jQuery.validator.addMethod("creditCardNoCheck", function(value, element) {
                    if (cardFormValidate()) {
                        return true;
                    } else {
                        return false;
                    }
                }, "Invalid credit card");



            }else{
                $('#modal_payment').addClass('hide');
                $('.cc_valid').removeClass('required');
                $('.cc_valid').val('');
            }
        });


        $(document).on('click','#tsecondstudent,#dthirdstudent,#dfourthstudent',function(){
            if(0 == "<?= $is_form_fee ?>"){
                if($(this).is(':checked')){
                    let amt = (parseInt($('#amount').val()) + parseInt("<?= $amount ?>"));
                    $('#amount_show').html('$'+amt);
                    $('#amount').val(amt);

                }else{
                    let amt = (parseInt($('#amount').val()) - parseInt("<?= $amount ?>"));
                    $('#amount_show').html('$'+amt);
                    $('#amount').val(amt);

                }
            }   
        });

        <?php echo get_country()->phone_formate ?>

        //VALIDATION - US PHONE FORMAT 
        jQuery.validator.addMethod("phonenocheck", function(value, element) {
            return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);
        }, "Enter valid phone number");

        //VALIDATION - CVV
        jQuery.validator.addMethod("checkCreditCardCVV", function(value, element, params) {
            if($('#'+params).val() == "Amex"){ 
                return this.optional(element) || /^[0-9]{4}$/i.test(value);
            }else{
                return this.optional(element) || /^[0-9]{3}$/i.test(value);
            }
        }, "Enter valid CVV number");


        //VALIDATION ROUTING NO
        jQuery.validator.addMethod("NewRoutingNoCheck", function(value, element) {
            return this.optional(element) ||
                /^((0[0-9])|(1[0-2])|(2[1-9])|(3[0-2])|(6[1-9])|(7[0-2])|80)([0-9]{7})$/i.test(value);
        }, "Enter valid routung number");



        $('#frm_register').validate();
        var bankName = '';

        $('select.child2').change(function() {
            child2_child3('child2');
        });

        $('.child2').keyup(function() {
            child2_child3('child2');
        });

        $('select.child3').change(function() {
            child2_child3('child3');
        });

        $('.child3').keyup(function() {
            child2_child3('child3');

        });

        $('select.child4').change(function() {
            child2_child3('child4');
        });

        $('.child4').keyup(function() {
            child2_child3('child4');

        });


        // $('#payment_method').change(function() {
        //     if ($(this).val() == 'ach') {
        //         $('#tr_credit_card').addClass('hide');
        //         $('#tr_ach').removeClass('hide');
        //         //$('.dis_col').removeClass('hide');
        //         /*$('#credit_card_type').removeClass('required');
        //           $('#credit_card_no').removeClass('required');*/
        //         $('#credit_card_exp_month').removeClass('required');
        //         $('#credit_card_exp_year').removeClass('required');
        //         $('#cvv').removeClass('required');

        //         $('#bank_acc_no').addClass('required');
        //         $('#routing_no').addClass('required');

        //     } else if ($(this).val() == 'credit_card') {
        //         $('#tr_ach').addClass('hide');
        //         $('#tr_credit_card').removeClass('hide');

        //         $('#bank_acc_no').removeClass('required');
        //         $('#routing_no').removeClass('required');

        //         /* $('#credit_card_type').addClass('required');
        //            $('#credit_card_no').addClass('required');*/
        //         $('#credit_card_exp_month').addClass('required');
        //         $('#credit_card_exp_year').addClass('required');
        //         $('#cvv').addClass('required');
        //         //$('.dis_col').removeClass('hide');

        //     } else {
        //         $('#tr_ach').addClass('hide');
        //         $('#tr_credit_card').addClass('hide');

        //         /* $('#credit_card_type').val('');
        //            $('#credit_card_no').val('');*/
        //         $('#credit_card_exp_month').val('');
        //         $('#credit_card_exp_year').val('');
        //         $('#cvv').val('');
        //         $('#bank_acc_no').val('');
        //         $('#routing_no').val('');
        //         $('#bank_acc_no').removeClass('required');
        //         $('#routing_no').removeClass('required');

        //         /* $('#credit_card_type').removeClass('required');
        //            $('#credit_card_no').removeClass('required');*/
        //         $('#credit_card_exp_month').removeClass('required');
        //         $('#credit_card_exp_year').removeClass('required');
        //         $('#cvv').removeClass('required');
        //         //$('.dis_col').addClass('hide');
        //     }
        // });

      /*   $('input[name=which_is_primary_email]').change(function() {
            if ($(this).val() == "father") {
                $('#father_email').addClass('required');
                $('#mother_email').removeClass('required');
            } else {
                $('#father_email').removeClass('required');
                $('#mother_email').addClass('required');
            }
        }); */

        $('input[type=radio][name=which_is_primary_email]').change(function() {
                if (this.value == 'parent1') {
                    $('#parent1_email').addClass('required');
                    $('#parent1_phone').addClass('required');
                } else if (this.value == 'parent2') {
                    $('#parent2_email').addClass('required');
                    $('#parent2_phone').addClass('required');
                }
            });

        $('.datepicker').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: 100,
            min: [<?php echo date('Y') - 100 ?>, 01, 01],
            max: [<?php echo date('Y') - 4 ?>, 12, 31],
            format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
            formatSubmit: 'yyyy-mm-dd'
        });

        $('#credit_card_no').blur(function() {
            if ($('#credit_card_no').valid()) {
                $('#cvv').prop("disabled", true);
                $('#cvv').removeAttr("disabled");
            } else {
                $('#cvv').val('');
                $('#cvv').prop("disabled", true);
                $('#cvv').attr("disabled");
            }
        });

        $('#routing_no').blur(function() {
            if ($.trim($('#routing_no').val()) != '') {
                //$('#td_bankname').html('Processing...');

                $.get('https://www.usbanklocations.com/crn.php?q=' + $.trim($('#routing_no').val()),
                    function(data, status) {
                        if (status == 'success') {
                            try {
                                if (data.indexOf('ublcrnright') != -1 || data.indexOf(
                                        'is valid bank routing number') != -1) {

                                    var nameStartsWith = "<tr><td><b>Name:</b></td><td>";
                                    var nameEndsWith = "</td>";
                                    bankName = data.substring(data.indexOf(nameStartsWith) +
                                        nameStartsWith.length);
                                    bankName = bankName.substring(0, bankName.indexOf(nameEndsWith));
                                    bankName = bankName.replace("</a>", "");
                                    if (bankName.indexOf(">") != -1) {
                                        bankName = bankName.substring(bankName.indexOf(">") + 1);
                                    }
                                } else {
                                    bankName = '';
                                }
                            } catch (ex) {
                                bankName = '';
                            }

                            if (bankName == '') {
                                $('#td_bankname').html("");
                            } else {
                                $('#td_bankname').html(
                                    "<h4 class='text-success mt-0 mb-0'>Bank Name: " + bankName +
                                    "</h4>");
                            }
                        }
                    });
            }
        });



        function cardFormValidate() {
            var cardValid = false;

            // Card number validation
            $('#credit_card_no').validateCreditCard(function(result) {
                var cardType = (result.card_type == null) ? '' : result.card_type.name;
                if (cardType == 'Visa') {
                    var backPosition = result.valid ? '2px -163px, 260px -87px' : '2px -163px, 260px -61px';
                } else if (cardType == 'MasterCard') {
                    var backPosition = result.valid ? '2px -247px, 260px -87px' : '2px -247px, 260px -61px';
                } else if (cardType == 'Maestro') {
                    var backPosition = result.valid ? '2px -289px, 260px -87px' : '2px -289px, 260px -61px';
                } else if (cardType == 'Discover') {
                    var backPosition = result.valid ? '2px -331px, 260px -87px' : '2px -331px, 260px -61px';
                } else if (cardType == 'Amex') {
                    var backPosition = result.valid ? '2px -121px, 260px -87px' : '2px -121px, 260px -61px';
                } else {
                    var backPosition = result.valid ? '2px -121px, 260px -87px' : '2px -121px, 260px -61px';
                }

                $('#credit_card_no').css("background-position", backPosition);

                if (result.valid) {
                    $("#credit_card_type").val(cardType);
                    $("#credit_card_no").removeClass('required');
                    cardValid = true;
                } else {
                    $("#credit_card_type").val('');
                    $("#credit_card_no").addClass('required');
                    cardValid = false;
                }
            });

            return cardValid;

            // Card details validation
            var expMonth = $("#credit_card_exp_month").val();
            var expYear = $("#credit_card_exp_year").val();
            var cvv = $("#cvv").val();
            var regName = /^[a-z ,.'-]+$/i;
            var regMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
            var regYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
            var regCVV = /^[0-9]{3,3}$/;
            if (cardValid == 0) {
                $("#credit_card_no").addClass('required');
                $("#credit_card_no").focus();
                return false;
            } else if (!regMonth.test(expMonth)) {
                $("#credit_card_no").removeClass('required');
                $("#credit_card_exp_month").addClass('required');
                $("#credit_card_exp_month").focus();
                return false;
            } else if (!regYear.test(expYear)) {
                $("#credit_card_no").removeClass('required');
                $("#credit_card_exp_month").removeClass('required');
                $("#credit_card_exp_year").addClass('required');
                $("#credit_card_exp_year").focus();
                return false;
            } else if (!regCVV.test(cvv)) {
                $("#credit_card_no").removeClass('required');
                $("#credit_card_exp_month").removeClass('required');
                $("#credit_card_exp_month").removeClass('required');
                $("#cvv").addClass('required');
                $("#cvv").focus();
                return false;
            } else if (!regName.test(cardName)) {
                $("#credit_card_no").removeClass('required');
                $("#credit_card_exp_month").removeClass('required');
                $("#credit_card_exp_year").removeClass('required');
                $("#cvv").removeClass('required');
                return false;
            } else {
                $("#credit_card_no").removeClass('required');
                $("#credit_card_exp_month").removeClass('required');
                $("#credit_card_exp_year").removeClass('required');
                $("#cvv").removeClass('required');
                $('#cardSubmitBtn').prop('disabled', false);
                return true;
            }
        }


        // $("#school_rules").change(function() {
        //     validate_radio_checkbox();
        // });

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

    $('#singleParent').on("click", function() {
        if ($(this).prop("checked") == true) {
            $('#firstparent').show();
            $('#secondparent').hide();
            $("#primary_email_parent1").prop("checked", true);
            $('#primary_email_parent2').removeAttr('checked');
            $('.parent2').removeClass('required');
            $('.parent2').removeAttr('aria-required');
            $('.parent2').parent().find('label').remove('.error');
            $('.parent2').parent().find('select').removeClass('error');
            $('.parent2').removeAttr('spacenotallow');
            $('#parent2_first_name').val('');
            $('#parent2_last_name').val('');
            $('#parent2_phone').val('');
            $('#parent2_email').val('');
        } else if ($(this).prop("checked") == false) {
            $('#firstparent').show();
            $('#secondparent').show();
            $('.parent2').addClass('required');
            $('.parent2').attr('spacenotallow', true);

        }
    });

    // function validate_radio_checkbox() {
    //     var retVal = true;
    //     if ($('#school_rules').is(':checked')) {
    //         //$('#school_rules-error').removeClass('error');
    //         $('#school_rules-error').html('');
    //         $('#school_rules-error').css('display', 'none');
    //     } else {
    //         //$('#school_rules-error').addClass('error');
    //         $('#school_rules-error').html('Required field');
    //         $('#school_rules-error').css('display', 'block');
    //         retVal = false;
    //     }

    //     return retVal;
    // }
    $('.ch2').prop('disabled', true);
    $('#tsecondstudent').on("click", function() {
        if ($(this).prop("checked") == true) {
            $('.ch2').attr('disabled', false);
        } else {
            $('.ch2').prop('disabled', true);
            $('.ch2').val('');
        }

    });
    $('.ch3').prop('disabled', true);
    $('#dthirdstudent').on("click", function() {
        if ($(this).prop("checked") == true) {
            $('.ch3').attr('disabled', false);
        } else {
            $('.ch3').prop('disabled', true);
            $('.ch3').val('');
        }
    });
    $('.ch4').prop('disabled', true);
    $('#dfourthstudent').on("click", function() {
        if ($(this).prop("checked") == true) {
            $('.ch4').attr('disabled', false);
        } else {
            $('.ch4').prop('disabled', true);
            $('.ch4').val('');
        }
    });


    $('#frm_register').submit(function(e) {
            e.preventDefault();
           var el = $('.btnsubmit');
           el.prop('disabled', true);
           setTimeout(function(){el.prop('disabled', false); }, 3000);
            if ($('#frm_register').valid()) {
            //$('.btnsubmit').attr('disabled', true);
            $('.ajaxMsgBot').html(
                '<h3 class="mar-top-zero">Processing...Please Wait</h3>');

            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-add-family-student-without-payment'; 
            var formDate = $(this).serialize();
            var post_url = "<?php echo PAYSERVICE_URL?>api/payment_request";

            if($('.payment_columns').is(':checked') && $('#credit_card_no').val().length > 0 && $('#amount').val().length > 0){

                //With credit card Registration. Request payment side
                $.ajax({
                    type: 'POST',
                    url: post_url,    //'http://payservice.troohly.com/api/payment_request'
                    dataType: "json",
                    crossDomain: true,
                    format: "json",
                    data: formDate,
                    success: (response) => {
                        $('.btnsubmit').attr('disabled', false);
                        if(response.data.code == 1){
                            $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url',{
                                payment_unique_id: response.data.transactionID,
                                request_token: "<?php echo $request_token ?>",
                                action: 'payment_verify',
                            },function(data,status){                    
                                if(status == 'success'){
                                        if(data.code == 1){
                                            $('#amount').val("<?= $amount ?>");
                                            $('.ajaxMsgBot').html(data.msg);
                                            displayAjaxMsg("Successfully submitted your registration.", data.code);

                                            $("#frm_register")[0].reset();
                                            $('#td_bankname').hide();

                                            $('.' + 'child2').removeClass('required');
                                            $('.' + 'child2').parent().find('label').remove('.error');
                                            $('.' + 'child2').parent().find('select').removeClass('error');

                                            $('.' + 'child3').removeClass('required');
                                            $('.' + 'child3').parent().find('label').remove('.error');
                                            $('.' + 'child3').parent().find('select').removeClass('error');

                                            $('#secondparent').show();
                                            $('.parent2').addClass('required');
                                            $('.parent2').attr('spacenotallow', true);
                                            $('.ch2,.ch3,.ch4').prop('disabled', true);

                                        }else{
                                            $('.btnsubmit').attr('disabled', false);
                                            displayAjaxMsg(response.data.message, response.data.code);
                                        }
                                }
                            },'json');

                        }else{
                            displayAjaxMsg(response.data.message, response.data.code);
                        }
                    },
                    error: (response) => {
                        $('.btnsubmit').attr('disabled', false);
                        displayAjaxMsg(response.data.message);
                    }
                })

        }else{

            //Without credit card Registration 
            $.post(targetUrl, formDate, function(data, status) {
                if (status == 'success') {
                    if (data.code == 1) {
                        $('#amount').val("<?= $amount ?>");
                        $('.btnsubmit').attr('disabled', false);
                        $('.ajaxMsgBot').html(data.msg);
                        displayAjaxMsg(data.msg, data.code);

                        //COMMENTED ON 30-MAR-2020
                        $("#frm_register")[0].reset();
                        $('#td_bankname').hide();

                        $('.' + 'child2').removeClass('required');
                        $('.' + 'child2').parent().find('label').remove('.error');
                        $('.' + 'child2').parent().find('select').removeClass('error');

                        $('.' + 'child3').removeClass('required');
                        $('.' + 'child3').parent().find('label').remove('.error');
                        $('.' + 'child3').parent().find('select').removeClass('error');

                        $('#secondparent').show();
                        $('.parent2').addClass('required');
                        $('.parent2').attr('spacenotallow', true);
                        $('.ch2,.ch3,.ch4').prop('disabled', true);
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }
                } else {
                    displayAjaxMsg(data.msg);
                }
            }, 'json');

        }




        }
    });
</script>
<?php include "../footer.php" ?>