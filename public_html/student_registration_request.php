<?php
include_once "includes/config.php";
include "header_guest.php";

$_SESSION['token']  = genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD);
$request_token  = 'req_' . RandomString();
?>

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

    label.error {
        color: #ff0000;
    }

    .input_fields_wrap {
        margin-top: 25px;
    }

    .remove_field {
        border-radius: 100px !important;
        line-height: 3.2222;
        text-transform: uppercase;
    }

    .panelcl {
        margin-top: 30px;
    }

    .divhide {
        display: none;
    }

    .div1hide {
        display: block;
    }

    @media only screen and (max-width: 768px) {
        .panelcl {
            margin-top: 80px;
        }

        .divhide {
            display: block;
        }

        .div1hide {
            display: none;
        }

        .desk {
            display: none;
        }

        .footer {
            margin-top: 20px;
        }

        .hmar {
            margin-top: 30px;
        }
    }
</style>

<?php
$session = $db->get_var("SELECT id FROM ss_school_sessions WHERE current = 1 AND status = 1");
$get_session = $db->get_results("SELECT * FROM ss_school_sessions ");
$current_year = date('Y');
$next_year = date('y', strtotime('+1 year'));
$final_year = $current_year . '-' . $next_year;

$check_class_session = $db->get_results("select ch.id, r.class_session from ss_sunday_sch_req_child ch  INNER JOIN ss_sunday_school_reg r
    ON r.id = ch.sunday_school_reg_id where r.class_session = '9:00 AM-11:00 AM' and r.session = '" . $session . "'");

$check_class_session1 = $db->get_results("select ch.id, r.class_session from ss_sunday_sch_req_child ch  INNER JOIN ss_sunday_school_reg r
    ON r.id = ch.sunday_school_reg_id where r.class_session = '11:15 AM-1:15 PM' and r.session = '" . $session . "'");

$grades = array('KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher');
$check = $db->get_row("select is_new_registration_open, new_registration_start_date, new_registration_end_date, is_new_registration_free,
    new_registration_fees_form_head, new_registration_fees, registration_page_termsncond, school_name, is_waiting from ss_client_settings where status = 1 AND new_registration_session = '" . $session . "'");

$ramount = round($check->new_registration_fees, 0);
if ($check->new_registration_fees_form_head == 0 && $check->is_new_registration_free == 0) {
    $amount = $ramount;
} elseif ($check->new_registration_fees_form_head == 1 && $check->is_new_registration_free == 0) {
    $amount1 = $ramount;
    $amount2 = ($ramount * 2);
    $amount3 = ($ramount * 3);
    $amount4 = ($ramount * 4);
}

$check_start_date = date('Y-m-d', strtotime($check->new_registration_start_date));
$check_end_date = date('Y-m-d', strtotime($check->new_registration_end_date));

if ($check->is_new_registration_open == 1 && date('Y-m-d') >= $check_start_date && date('Y-m-d') <= $check_end_date) {
?>

    <!-- Content area -->
    <div class="content">
        <div class="panel panel-flat panelcl">
            <div class="panel-body">
                <!-- Advanced login -->
                <form name="frm_register" class=" reg_form mt-20" id="frm_register" method="post">
                    <div class="row desk" style="margin-top:-30px;">
                        <div class="col-md-12">
                            <h1><?php echo CENTER_SHORTNAME ?>- Student Registration Form</h1>
                        </div>
                    </div>
                    <div class="row divhide">
                        <div class="col-md-12">
                            <h1 style="font-weight:bold;margin-top:-10px;text-align:center;"><?php echo CENTER_SHORTNAME ?>- Student Registration Form (<?php echo $viewsession ?>)</h1>
                        </div>
                        <!-- <div class="col-md-2">
                        <strong>Registration For Session<span class="mands">*</span></strong>
                        <select name="session" id="session" class="form-control required">
                        <option value="">Select</option>
                        <?php
                        foreach ($get_session as $sess) {
                            if (date('Y-m-d') <= $sess->start_date && $sess->end_date >= date('Y-m-d')) {
                        ?>
                                <option value="<?php echo $sess->id ?>"><?php echo $sess->session ?></option>
                            <?php }
                        } ?>

                    </select>
                </div> -->
                        <!-- <?php if ($ramount > 0) { ?>
                <div class="col-md-2">
                        <strong>Registration Fee</strong> <br>
                        <?php
                                    if ($check->new_registration_fees_form_head == 0 && $check->is_new_registration_free == 0) { ?>
                        <label style="margin-top: 5px;font-size: 16px;">We charge <?php echo '$' . round($check->new_registration_fees, 0); ?> per form</label>
                        <?php } elseif ($check->new_registration_fees_form_head == 1 && $check->is_new_registration_free == 0) { ?>
                        <label style="margin-top: 5px;font-size: 16px;">We charge <?php echo '$' . round($check->new_registration_fees, 0); ?> per head</label>
                        <?php } ?>
                </div>
                <?php } ?> -->
                    </div>

                    <div class="row hmar">
                        <div class="col-md-12 text-right" style="margin-top:-40px;">
                            <button type="button" class="add_field_button btn btn-xs btn-info">Add Student</button>
                        </div>
                    </div>
                    <div class="row fromcln">
                        <div class="col-md-2">
                            <input type="text" spacenotallow="true" name="child1_first_name" lettersonly="true" tabindex="5" id="child1_first_name" maxlength="25" placeholder="First Name" lettersonly="true" value="" class="form-control stu required">
                        </div>
                        <div class="col-md-2">
                            <input type="text" spacenotallow="true" name="child1_last_name" lettersonly="true" tabindex="8" id="child1_last_name" maxlength="25" placeholder="Last Name" value="" class="form-control required" lettersonly="true">
                        </div>
                        <div class="col-md-2">
                            <select name="child1_gender" id="child1_gender" tabindex="12" class="form-control required">
                                <option value="">Select Gender</option>
                                <option value="m">Male
                                </option>
                                <option value="f">Female
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" id="child1_dob" name="child1_dob" tabindex="15" value="" placeholder="Date of Birth" class="form-control required datepicker bgcolor-white">
                        </div>
                        </dev>


                        <div class="col-md-2">
                            <input type="text" id="child1_allergies" name="child1_allergies" maxlength="50" tabindex="18" placeholder="Enter Allergies" class="form-control required">
                        </div>

                        <div class="col-md-2">
                            <select name="child1_grade" id="child1_grade" class="form-control required" tabindex="21">
                                <option value="">Select Grade</option>
                                <?php foreach ($grades as $garde) { ?>
                                    <option value="<?= $garde ?>"><?= $garde ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="m-2 input_fields_wrap">
                        <div id="clonedItems"></div>
                    </div>

                    <!--  <div class="row mar-top-20">
                <label class="checkbox-inline"></label>

                <div class="col-md-12">
                    <input type="checkbox" tabindex="110" name="secondstudent" id="tsecondstudent">
                    <strong>Second Student</strong>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <input type="text" spacenotallow="true" name="child2_first_name" id="child2_first_name" lettersonly="true" tabindex="40" placeholder="First Name" maxlength="25" value="" class="form-control required ch2 " lettersonly="true">
                </div>
                <div class="col-md-2">
                    <input type="text" spacenotallow="true" name="child2_last_name" lettersonly="true" tabindex="45" placeholder="Last Name" maxlength="25" value="" class="form-control child2 required ch2" lettersonly="true">
                </div>
                <div class="col-md-2">
                    <select name="child2_gender" class="form-control child2 required ch2" tabindex="50">
                        <option value="">Select Gender</option>
                        <option value="m">Male
                        </option>
                        <option value="f">Female
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="child2_dob" tabindex="55" placeholder="Date of Birth" value="" class="form-control datepicker child2 bgcolor-white required ch2">
                </div>


                <div class="col-md-2">
                    <input type="text" name="child2_allergies" maxlength="50" tabindex="60" placeholder="Enter Allergies" class="form-control ch2">
                </div>

                <div class="col-md-2">
                    <select name="child2_grade" class="form-control child2 required ch2" tabindex="65">
                        <option value="">Select Grade</option>
                        <?php foreach ($grades as $garde) { ?>
                            <option value="<?= $garde ?>"><?= $garde ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div> -->

                    <!-- <div class="row mar-top-20">
                <label class="checkbox-inline"></label>

                <div class="col-md-12"><input type="checkbox" tabindex="110" name="thirdstudent" id="dthirdstudent">
                    <strong>Third Student</strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <input type="text" spacenotallow="true" name="child3_first_name" id="child3_first_name" lettersonly="true" placeholder="First Name" maxlength="25" tabindex="75" value="" class="stu1 form-control required ch3" lettersonly="true">
                </div>
                <div class="col-md-2">
                    <input type="text" spacenotallow="true" name="child3_last_name" lettersonly="true" placeholder="Last Name" maxlength="25" value="" class="child3 form-control required ch3" lettersonly="true" tabindex="80">
                </div>
                <div class="col-md-2">
                    <select name="child3_gender" class="child3 form-control required ch3" tabindex="85">
                        <option value="">Select Gender</option>
                        <option value="m">Male
                        </option>
                        <option value="f">Female
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="child3_dob" placeholder="Date of Birth" value="" tabindex="90" class="child3 form-control datepicker bgcolor-white required ch3">
                </div>

                <div class="col-md-2">
                    <input type="text" name="child3_allergies" maxlength="50" tabindex="95" placeholder="Enter Allergies" class="form-control ch3">
                </div>

                <div class="col-md-2">
                    <select name="child3_grade" class="form-control child3 required ch3" tabindex="100">
                        <option value="">Select Grade</option>
                        <?php foreach ($grades as $garde) { ?>
                            <option value="<?= $garde ?>"><?= $garde ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div> -->


                    <!--  <div class="row mar-top-20">
                <label class="checkbox-inline"></label>
                <div class="col-md-12"> <input type="checkbox" tabindex="110" name="fourtstudent" id="dfourthstudent">
                    <strong>Fourth Student</strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <input type="text" spacenotallow="true" name="child4_first_name" id="child4_first_name" lettersonly="true" placeholder="First Name" maxlength="25" tabindex="101" value="" class="stu1 form-control required ch4" lettersonly="true">
                </div>
                <div class="col-md-2" class="st4">
                    <input type="text" spacenotallow="true" name="child4_last_name" lettersonly="true" placeholder="Last Name" maxlength="25" value="" class="stu4 form-control required ch4" lettersonly="true" tabindex="102">
                </div>
                <div class="col-md-2" class="st4">
                    <select name="child4_gender" class="child4 form-control required ch4" tabindex="103">
                        <option value="">Select Gender</option>
                        <option value="m">Male
                        </option>
                        <option value="f">Female
                        </option>
                    </select>
                </div>
                <div class="col-md-2" class="st4">
                    <input type="text" name="child4_dob" placeholder="Date of Birth" value="" tabindex="104" class="child4 form-control datepicker bgcolor-white required ch4">
                </div>

                <div class="col-md-2" class="st4">
                    <input type="text" name="child4_allergies" maxlength="50" tabindex="105" placeholder="Enter Allergies" class="form-control ch4">
                </div>

                <div class="col-md-2">
                    <select name="child4_grade" class="form-control child4 required ch4" tabindex="106">
                        <option value="">Select Grade</option>
                        <?php foreach ($grades as $garde) { ?>
                            <option value="<?= $garde ?>"><?= $garde ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div> -->



                    <div class="row" style="margin-top:15px;">
                        <div class="col-md-2">
                            <h3 class="subtitle">Family Information</h3>
                        </div>
                        <div class="col-md-10"><label class="checkbox-inline" style="margin-top: 25px;"><input type="checkbox" tabindex="110" name="singleParent" id="singleParent"> Single Parent </label>
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
                                <label id="parent1_phone-error" class="validation-error-label" for="parent1_phone" style="display: none;">Enter valid phone number</label>
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
                                <input type="text" name="parent2_first_name" maxlength="25" value="" tabindex="140" id="parent2_first_name" placeholder="First Name" class="form-control parent2 required" lettersonly="true">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="parent2_last_name" maxlength="25" value="" tabindex="145" id="parent2_last_name" placeholder="Last Name" class="form-control parent2 required" lettersonly="true">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="parent2_phone" value="" id="parent2_phone" tabindex="150" PhoneNumber="true" maxlength="10" placeholder="Phone Number" class="form-control parent2 required phone_no">
                                <!-- <label id="parent2_phone-error" class="validation-error-label required" for="parent1_phone" style="display: none;">Enter valid phone number</label> -->
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
                            <input type="text" name="address_1" spacenotallow="true" id="address_1" value="" maxlength="200" tabindex="185" placeholder="Address Line 1" class="form-control required">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="address_2" maxlength="200" value="" placeholder="Address Line 2" tabindex="190" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="city" spacenotallow="true" id="city" maxlength="45" value="" placeholder="City" class="form-control required" lettersonly="true" tabindex="195">
                        </div>
                        <div class="col-md-3">
                            <?php $states = $db->get_results("select * from ss_state where is_active = 1 and country_id = '" . get_country()->country_id . "' order by state asc"); ?>
                            <select name="state" id="state_id" tabindex="200" class="required form-control">
                                <option value="">Select State</option>
                                <?php foreach ($states as $st) { ?>
                                    <option value="<?php echo $st->id; ?>"><?php echo $st->state; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">

                        <div class="col-md-3">
                            <input type="text" name="post_code" maxlength="<?php echo (!empty(get_country()->length)) ? get_country()->length : '5' ?>" id="post_code" value="" zipCodeCheck="true" placeholder="Zip Code" class="form-control required" tabindex="205">
                        </div>
                        <div class="col-md-3">
                            <?php
                            $countries = $db->get_results("select * from ss_country where is_active = 1  and id = '" . get_country()->country_id . "'"); ?>
                            <select name="country_id" id="country_id" class="form-control" tabindex="210">
                                <?php foreach ($countries as $country) { ?>
                                    <option value="<?php echo $country->id; ?>"><?php echo $country->country; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- <br>
<div class="row">
<div class="col-md-3"> <strong>Payment Type <span class="mands">*</span></strong>
    <select name="payment_method" id="payment_method" tabindex="72" class="form-control required">
        <option value="">Select</option>
        <option value="ach" >Monthly</option>
        <option value="credit_card">One time Full Payment - Discount Applies</option>
    </select>
</div>

<div class="col-md-6">
    <p style="margin-top: 26px;">Registration Fee: $20</p>
</div>
</div> -->


                    <!--                 <br>
<div class="row">
<div class="col-md-12"> <strong class="paymnt_title"></strong>
    <div id="payment_summery" class="table-responsive"></div>
</div>
</div>
<div class="row hide" id="tr_ach">
<div class="col-md-3"> <strong>Bank Account Number <span class="mands">*</span></strong>
    <input placeholder="Bank Account Number" maxlength="22"
        value="<?php // echo $bank_acc_no
                ?>" name="bank_acc_no" id="bank_acc_no" class="form-control
        type="text">
</div>
<div class="col-md-3"> <strong>Routing Number <span class="mands">*</span></strong>
    <input placeholder="Routing Number" maxlength="16" NewRoutingNoCheck="true" name="routing_no" id="routing_no" class="form-control"
        type="text">
</div>
<div class="col-md-3 col-xs-6 pt-20" id="td_bankname"></div>
</div> -->
                    <div class="row" style="margin-top:15px; display:none">
                        <div class="col-md-12">
                            <div class="form-group">
                                <strong>Please pick a session:<span class="mandatory">*</span></strong>
                                <div class="col-md-12">
                                    <?php if (count((array)$check_class_session) < 30) { ?>
                                        <label class="radio-inline">
                                            <input type="radio" class="required" id="radio1" name="class_session" value="9:00 AM-11:00 AM"> 9:00 AM to 11:00 AM &nbsp;
                                            <?php if (count((array)$check_class_session) == 0) { ?>
                                                (Available Seats : 30)
                                            <?php } else { ?>
                                                <?php
                                                $total_slot_count = count((array)$check_class_session);
                                                $total_slot = (30 - $total_slot_count);
                                                ?>
                                                (Available Seats : <?php echo $total_slot ?>)
                                            <?php } ?>
                                        </label>
                                    <?php } ?>
                                </div>
                                <div class="col-md-12">
                                    <?php if (count((array)$check_class_session1) < 30) { ?>
                                        <label class="radio-inline">
                                            <input type="radio" id="radio2" name="class_session" value="11:15 AM-1:15 PM" checked>11:15 AM to 1:15 PM &nbsp;
                                            <?php if (count((array)$check_class_session1) == 0) { ?>
                                                (Available Seats : 30)
                                            <?php } else { ?>
                                                <?php
                                                $totalcount = count((array)$check_class_session1);
                                                $totalslot = (30 - $totalcount);
                                                ?>
                                                (Available Seats : <?php echo $totalslot ?>)
                                            <?php } ?>
                                        </label>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:15px;">
                        <div class="col-md-12">
                            <h3 class="subtitle">Payment Information </h3>
                            <p><?php if (IsWaiting == 1) { ?>
                                    <strong>Note:</strong>
                                <?php } ?>
                                <?php if (IsWaiting == 1) {
                                    echo 'You`re applying in the waiting list. We will save your data but we will not charge you.';
                                } ?>
                            </p>
                        </div>
                    </div>
                    <div class="row" id="tr_credit_card">

                        <input type="hidden" name="credit_card_type" id="credit_card_type">

                        <div class="col-md-2"> <strong>Credit Card Number <span class="mands">*</span></strong>
                            <input placeholder="Credit Card No" maxlength="16" creditCardNoCheck="true" value="" id="credit_card_no" name="credit_card_no" class="form-control required" type="text" tabindex="215">
                        </div>
                        <div class="col-md-2"> <strong>Expiration Month <span class="mands">*</span></strong>
                            <select name="exp_month" creditCardExpMonthCheck="true" id="credit_card_exp_month" class="form-control required" tabindex="220">
                                <option value="">Select Month</option>
                                <?php for ($i = 1; $i <= 12; $i++) { ?>
                                    <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>">
                                        <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <strong>Expiration Year <span class="mands">*</span></strong>

                            <select name="exp_year" creditCardExpYearCheck="true" id="credit_card_exp_year" class="form-control required" tabindex="225">
                                <option value="">Select Year</option>
                                <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++) { ?>
                                    <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>">
                                        <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-1"> <strong>CVV <span class="mands">*</span></strong>
                            <input placeholder="CVV" maxlength="4" disabled name="cvv_no" id="cvv" checkCreditCardCVV="credit_card_type" value="" inputmode="numeric" class="form-control required" type="PASSWORD" tabindex="230">
                        </div>

                        <!-- <div class="col-md-5">
            <strong>Comment </strong>
            <input type="text" name="comment_post" value="" class="form-control"  placeholder="Comment" maxlength="100"  id="comment">
        </div> -->

                        <?php if (IsWaiting == 0) { ?>
                            <div class="col-md-2 feeshow">
                                <strong>Chargeable Amount</strong>
                                <?php if (!empty($amount1)) { ?>
                                    <p style="margin-top: 5px;margin-left: 20px; font-size: 20px;" class="fees"><?php
                                                                                                                if (!empty(get_country()->currency)) {
                                                                                                                    $currency = get_country()->currency;
                                                                                                                } else {
                                                                                                                    $currency = '';
                                                                                                                }
                                                                                                                echo $currency . $amount1
                                                                                                                ?></p>
                                    <input type="hidden" name="amount" class="regfees" value="<?php echo $amount1 ?>">
                                <?php }
                                if (!empty($amount)) { ?>
                                    <p style="margin-top: 5px;margin-left: 20px; font-size: 20px;"><?php if (!empty(get_country()->currency)) {
                                                                                                        $currency = get_country()->currency;
                                                                                                    } else {
                                                                                                        $currency = '';
                                                                                                    }
                                                                                                    echo $currency . $amount ?></p>
                                    <input type="hidden" name="amount" class="regfees" value="<?php echo $amount ?>">
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="amount" class="regfees" value="0">
                        <?php } ?>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-md-12"> <strong>Use this space for any additional information or questions</strong>
                            <textarea name="addition_notes" id="addition_notes" class="form-control" tabindex="235"></textarea>
                        </div>
                    </div>

                    <?php if (isset($check->registration_page_termsncond)) { ?>
                        <div class="row">
                            <div class="col-md-12">

                                <?php echo $check->registration_page_termsncond ?>
                                <br>
                                <strong>
                                    <input type="checkbox" id="school_rules" style="margin-right:5px;" tabindex="240">
                                    I Agree to Above Terms & Conditions <span class="mands">*</span></strong> <br>
                                <label id="school_rules-error" class="error_cust" for="school_rules"></label>


                            </div>
                        </div>
                    <?php } ?>
                    <br>
                    <div class="row" style="margin-bottom:50px;">
                        <div class="col-md-10 col-xs-8">
                            <div class="ajaxMsgBot pull-right"></div>
                        </div>
                        <div class="col-md-2 col-xs-4">
                            <input type="hidden" name="stu_uniq_ids" id="stu_uniq_ids" value="">
                            <input type="hidden" name="action" value="student_register">
                            <input type="hidden" id="frequency_type" name="frequency_type" value='onetime'>
                            <input type="hidden" name="session" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION'] ?>" />
                            <input type="hidden" name="session_text" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] ?>" />
                            <input type="hidden" name="auth_token" value="<?= $_SESSION['token'] ?>" />
                            <input type="hidden" name="request_token" value="<?php echo $request_token ?>" />
                            <input type="hidden" name="system_ip" value="<?= $_SERVER['REMOTE_ADDR'] ?>" />
                            <input type="hidden" name="is_waiting" value="<?= IsWaiting ?>" />
                            <input type="submit" value="Submit" class="btn btn-success btn-block btnsubmit" tabindex="255">
                        </div>
                    </div>
                    <input type="hidden" name="form_submit" value="1">
                </form>
                <!-- /advanced login -->


            <?php } else { ?>
                <style>
                    body {
                        font-family: Roboto, Helvetica Neue, Helvetica, Arial, sans-serif;
                        font-size: 13px;
                        line-height: 1.5384616;
                        color: #333;
                        /* background-color: white !important;*/
                    }

                    .error-template {
                        padding: 40px 15px;
                        text-align: center;
                    }
                </style>
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="error-template">
                                <h2>Registration is closed. Please contact <a href="mailto:academy@ickansas.org">academy@ickansas.org</a> </h2>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>

    <!-- /content area -->
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->

    <script src="<?php echo SITEURL ?>/assets/js/jquery-ui.min.js"></script>
    <script type="text/javascript">
        var is_double_submit = 0;

        $('.datepicker').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: 100,
            min: [<?php echo date('Y') - 100 ?>, 01, 01],
            max: [<?php echo date('Y') - 4 ?>, 12, 31],
            format: '<?php echo my_date_changer('d mmmm, yyyy'); ?>',
            formatSubmit: 'mm/dd/yyyy'
        });
        var max_fields = 3; //maximum input boxes allowed
        var wrapper = $("#clonedItems"); //Fields wrapper
        var add_button = $(".add_field_button"); //Add button ID
        var totalch = "";
        var total = "";
        var tabindex = 21;

        var x = 1; //initlal text box count
        $(add_button).on("click", function(e) { //on add input button click
            e.preventDefault();
            if (x <= max_fields) {
                var ukey = Math.floor((1 + Math.random()) * 0x100000).toString(16);
                //max input box allowed
                x++; //text box increment

                var items2 = $('.fromcln').first().clone().find("input:text").val("").end().addClass('newClass' + ukey);
                var items3 = '<div class="col-md-12 text-right"><a href="javascript:void(0)" data-id ="' + ukey + '" class="btn-xs btn-danger remove_field"><i class="icon-minus2"></i> Remove</a></div>';

                wrapper.append(items2);
                wrapper.append(items3);
                $('#clonedItems').find(".datepicker").pickadate({
                    labelMonthNext: 'Go to the next month',
                    labelMonthPrev: 'Go to the previous month',
                    labelMonthSelect: 'Pick a month from the dropdown',
                    labelYearSelect: 'Pick a year from the dropdown',
                    selectMonths: true,
                    selectYears: 100,
                    min: [<?php echo date('Y') - 100 ?>, 01, 01],
                    max: [<?php echo date('Y') - 3 ?>, 12, 31],
                    format: '<?php echo my_date_changer('d mmmm, yyyy'); ?>',
                    formatSubmit: 'mm/dd/yyyy'
                });

                tabindex1 = tabindex + 3;
                tabindex2 = tabindex1 + 3;
                tabindex3 = tabindex2 + 3;
                tabindex4 = tabindex3 + 3;
                tabindex5 = tabindex4 + 3;
                tabindex6 = tabindex5 + 3;
                tabindex = tabindex6;

                var stu_uniq_ids = $('#stu_uniq_ids').val();
                stu_uniq_ids = stu_uniq_ids.concat(",", ukey);
                $('#stu_uniq_ids').val(stu_uniq_ids);

                items2.find('input,select').each(function() {

                    this.name = this.name.replace('child1_', 'child' + ukey + '_');
                    this.id = this.id.replace('child1_', 'child' + ukey + '_');

                    $('#child' + ukey + '_first_name').attr('tabindex', tabindex1);
                    $('#child' + ukey + '_last_name').attr('tabindex', tabindex2);
                    $('#child' + ukey + '_gender').attr('tabindex', tabindex3);
                    $('#child' + ukey + '_dob').attr('tabindex', tabindex4);
                    $('#child' + ukey + '_allergies').attr('tabindex', tabindex5);
                    $('#child' + ukey + '_grade').attr('tabindex', tabindex6);

                    <?php if (!empty($amount1)) { ?>
                        var total = <?php echo $amount1 ?> * x;
                    <?php } ?>

                    <?php if (!empty($amount)) { ?>
                        var total = "<?php echo $amount ?>";
                    <?php } ?>

                    //var total = amount * x;

                    $('.feeshow').show();
                    $(".regfees").val(total);
                    $(".fees").html("$" + total);

                });

                $('#clonedItems label[for=child1_first_name]').remove();
                $('#clonedItems label[for=child1_last_name]').remove();
                $('#clonedItems label[for=child1_gender]').remove();
                $('#clonedItems label[for=child1_dob]').remove();
                $('#clonedItems label[for=child1_allergies]').remove();
                $('#clonedItems label[for=child1_grade]').remove();

                $('#clonedItems #child' + ukey + '_first_name').removeClass('error');
                $('#clonedItems #child' + ukey + '_last_name').removeClass('error');
                $('#clonedItems #child' + ukey + '_gender').removeClass('error');
                $('#clonedItems #child' + ukey + '_dob').removeClass('error');
                $('#clonedItems #child' + ukey + '_allergies').removeClass('error');
                $('#clonedItems #child' + ukey + '_grade').removeClass('error');

                // $(wrapper).html('<div>'+items+'<a href="#" class="remove_field">Remove</a></div>'); //add input box
            }

        });

        // $(".stu").keyup(function() {
        //         if ($.trim($('#child1_first_name').val()) != '') {
        //             <?php if ($amount1) { ?>
        //                 $('.feeshow').show();
        //                 $(".regfees").val("<?php echo $amount1 ?>");
        //                 $(".fees").html("$<?php echo $amount1 ?>");
        //             <?php } else if ($amount) { ?>
        //                 $('.feeshow').show();
        //                 $(".regfees").val("<?php echo $amount ?>");
        //                 $(".fees").html("$<?php echo $amount ?>");
        //             <?php } ?>
        //         }
        //  });


        $(document).on('change', '.datepicker', function() {
            $(this).parent().find('label.error').remove();
        })

        //    $('#country_id').change(function() {

        //         if ($('#country_id').val() == '') {
        //             $('#state_id').html('<option value="">Select State</option>');
        //         } else {
        //             //SUBJECT
        //             $('#state_id').html('<option value="">Loading...</option>');

        //             var targetUrl = '<?php echo SITEURL ?>ajax/ajss-settings';
        //             $.post(targetUrl, {
        //                 country_id: $('#country_id').val(),
        //                 action: 'fetch_state'
        //             }, function(data, status) {
        //                 if (status == 'success' && data != '') {
        //                     $('#state_id').html('<option value="">Select State</option>');
        //                     $('#state_id').append(data);
        //                 } else {
        //                     $('#state_id').html('<option value="">State not found</option>');
        //                 }
        //             });

        //         }
        //     });


        $(wrapper).on("click", ".remove_field", function(e) { //user click on remove text
            e.preventDefault();
            var id = $(this).data('id');
            $('.newClass' + id).remove();

            var stu_uniq_ids = $('#stu_uniq_ids').val();
            stu_uniq_ids = stu_uniq_ids.replace("," + id, "");
            $('#stu_uniq_ids').val(stu_uniq_ids);

            var newamount = "<?php echo $amount1 ?>";
            var totalamount = newamount * id;
            if ($(this).remove()) {
                var amount_val = $('.regfees').val();
                var subamount = amount_val - newamount;
                $('.feeshow').show();
                $(".regfees").val(subamount);
                $(".fees").html("$" + subamount);
            }
            x--;
        })

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

            $('.btnsubmit').prop("type", "submit");

            $('input').on('keypress', function(e) {
                if (this.value.length === 0 && e.which === 32) {
                    return false;
                }
            });


            $('.guest_screen').removeClass('guest_screen ');
            cardFormValidate();

            <?php echo get_country()->phone_formate; ?>

            //VALIDATION - US PHONE FORMAT
            // jQuery.validator.addMethod("phonenocheck", function(value, element) {
            //     return this.optional(element) || /^([0-9]{1,10})$/i.test(value);
            // }, "Enter valid phone number");

            //VALIDATION - CVV
            jQuery.validator.addMethod("checkCreditCardCVV", function(value, element, params) {
                if ($('#' + params).val() == "Amex") {
                    return this.optional(element) || /^[0-9]{4}$/i.test(value);
                } else {
                    return this.optional(element) || /^[0-9]{3}$/i.test(value);
                }
            }, "Enter valid CVV number");

            //VALIDATION - CREDIT CARD NUMBER
            jQuery.validator.addMethod("creditCardNoCheck", function(value, element) {
                if (cardFormValidate()) {
                    return true;
                } else {
                    return false;
                }
            }, "Invalid credit card");


            //VALIDATION ROUTING NO
            jQuery.validator.addMethod("NewRoutingNoCheck", function(value, element) {
                return this.optional(element) ||
                    /^((0[0-9])|(1[0-2])|(2[1-9])|(3[0-2])|(6[1-9])|(7[0-2])|80)([0-9]{7})$/i.test(value);
            }, "Enter valid routung number");



            $('#frm_register').validate();
            var bankName = '';

            // $('select.child2').change(function() {
            //     child2_child3('child2');
            // });

            // $('.child2').keyup(function() {
            //     child2_child3('child2');
            // });

            // $('select.child3').change(function() {
            //     child2_child3('child3');
            // });

            // $('.child3').keyup(function() {
            //     child2_child3('child3');

            // });

            // $('select.child4').change(function() {
            //     child2_child3('child4');
            // });

            // $('.child4').keyup(function() {
            //     child2_child3('child4');

            // });


            $('#payment_method').change(function() {
                if ($(this).val() == 'ach') {
                    $('#tr_credit_card').addClass('hide');
                    $('#tr_ach').removeClass('hide');
                    //$('.dis_col').removeClass('hide');
                    /*$('#credit_card_type').removeClass('required');
                        $('#credit_card_no').removeClass('required');*/
                    $('#credit_card_exp_month').removeClass('required');
                    $('#credit_card_exp_year').removeClass('required');
                    $('#cvv').removeClass('required');

                    // $('#bank_acc_no').addClass('required');
                    // $('#routing_no').addClass('required');

                } else if ($(this).val() == 'credit_card') {
                    $('#tr_ach').addClass('hide');
                    $('#tr_credit_card').removeClass('hide');

                    // $('#bank_acc_no').removeClass('required');
                    // $('#routing_no').removeClass('required');

                    /* $('#credit_card_type').addClass('required');
                        $('#credit_card_no').addClass('required');*/
                    $('#credit_card_exp_month').addClass('required');
                    $('#credit_card_exp_year').addClass('required');
                    $('#cvv').addClass('required');
                    //$('.dis_col').removeClass('hide');

                } else {
                    $('#tr_ach').addClass('hide');
                    $('#tr_credit_card').addClass('hide');

                    /* $('#credit_card_type').val('');
                        $('#credit_card_no').val('');*/
                    $('#credit_card_exp_month').val('');
                    $('#credit_card_exp_year').val('');
                    $('#cvv').val('');
                    // $('#bank_acc_no').val('');
                    // $('#routing_no').val('');
                    // $('#bank_acc_no').removeClass('required');
                    // $('#routing_no').removeClass('required');

                    /* $('#credit_card_type').removeClass('required');
                        $('#credit_card_no').removeClass('required');*/
                    $('#credit_card_exp_month').removeClass('required');
                    $('#credit_card_exp_year').removeClass('required');
                    $('#cvv').removeClass('required');
                    //$('.dis_col').addClass('hide');
                }
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


            // $('#frm_register').submit(function() {
            //  var val_rad_check = validate_radio_checkbox();

            //  if ($('#frm_register').valid() && val_rad_check) {
            //      $('.ajaxMsgBot').html('<strong><i class="fa fa-spinner fa-spin" style="font-size:26px;"></i> Please Wait...</strong>');
            //      return true;
            //  } else {
            //      return false;
            //  }
            // });

            // $('#payment_method').change(function() {
            //  if ($(this).val() == 'auto_deduction_acc') {
            //      $('#tr_credit_card').addClass('hide');
            //      $('#credit_card_type').val('');
            //      $('#credit_card_no').val('');
            //      $('#credit_card_exp_month').val('');
            //      $('#credit_card_exp_year').val('');
            //      $('#postal_code').val('');

            //      $('#tr_auto_deduction_acc').removeClass('hide');
            //  } else if ($(this).val() == 'credit_card') {
            //      $('#tr_auto_deduction_acc').addClass('hide');
            //      $('#bank_acc_no').val('');
            //      $('#routing_no').val('');

            //      $('#tr_credit_card').removeClass('hide');
            //  } else {
            //      $('#tr_auto_deduction_acc').addClass('hide');
            //      $('#tr_credit_card').addClass('hide');

            //      $('#credit_card_type').val('');
            //      $('#credit_card_no').val('');
            //      $('#credit_card_exp_month').val('');
            //      $('#credit_card_exp_year').val('');
            //      $('#postal_code').val('');
            //      $('#bank_acc_no').val('');
            //      $('#routing_no').val('');
            //  }
            // });

            $("#school_rules").change(function() {
                validate_radio_checkbox();
            });
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

        // $('input[type=radio][name=which_is_primary_email]').change(function() {
        //     if (this.value == 'parent1') { 
        // $('#parent1_email').addClass('required');
        // $('#parent1_phone').addClass('required');

        // $('#parent2_email').removeClass('required');
        // $('#parent2_phone').removeClass('required');

        // } else if (this.value == 'parent2') { 
        // $('#parent2_email').addClass('required');
        // $('#parent2_phone').addClass('required');
        //  $('#parent1_email').removeClass('required');
        // $('#parent1_phone').removeClass('required');


        //     }
        // });

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

        $('#frm_register').submit(function(e) {
            e.preventDefault();
            // is_double_submit++;
            // if(is_double_submit==1){
            var val_rad_check = validate_radio_checkbox();
            if ($('#frm_register').valid() && val_rad_check) {
                $('.btnsubmit').prop("type", "button");
                $('.btnsubmit').attr('disabled', true);
                $('.ajaxMsgBot').html('<h3 class="mar-top-zero">Processing...Please Wait</h3>');
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-register';
                var formDate = $(this).serialize();
                var post_url = "<?php echo PAYSERVICE_URL ?>api/payment_request";
                $.ajax({
                    type: 'POST',
                    url: post_url, //'http://payservice.troohly.com/api/payment_request'
                    dataType: "json",
                    crossDomain: true,
                    format: "json",
                    data: formDate,
                    success: (response) => {
                        //console.log(response);
                        if (response.data.code == 1) {
                            $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url', {
                                payment_unique_id: response.data.transactionID,
                                request_token: "<?php echo $request_token ?>",
                                action: 'payment_verify',
                            }, function(data, status) {
                                if (status == 'success') {
                                    if (data.code == 1) {
                                        $('.btnsubmit').prop("type", "submit");
                                        $('.btnsubmit').attr('disabled', false);
                                        history.pushState(null, null, 'no-back-button');
                                        window.addEventListener('popstate', function(event) {
                                            history.pushState(null, null, 'no-back-button');
                                        });

                                        $('.ajaxMsgBot').html('');
                                        $("#frm_register")[0].reset();
                                        var validator = $("#frm_register").validate();
                                        validator.resetForm();
                                        $('#td_bankname').hide();
                                        $('.ajaxMsgBot').hide();

                                        $('.' + 'child2').removeClass('required');
                                        $('.' + 'child2').parent().find('label').remove('.error');
                                        $('.' + 'child2').parent().find('select').removeClass('error');

                                        $('.' + 'child3').removeClass('required');
                                        $('.' + 'child3').parent().find('label').remove('.error');
                                        $('.' + 'child3').parent().find('select').removeClass('error');
                                        window.location = '<?php echo SITEURL ?>thankyou.php';

                                    } else {
                                        displayAjaxMsg(response.data.message, response.data.code);
                                    }
                                }
                            }, 'json');

                        } else {
                            displayAjaxMsg(response.data.message, response.data.code);
                        }
                    },
                    error: (response) => {
                        $('.btnsubmit').prop("type", "submit");
                        $('.btnsubmit').attr('disabled', false);
                        displayAjaxMsg(response.data.message);
                    }
                })
            }
            // }
        });


        $(document).ready(function() {
            $('.ch2').prop('disabled', true);
            $('#tsecondstudent').on("click", function() {
                if ($(this).prop("checked") == true) {
                    $('.ch2').attr('disabled', false);
                } else {
                    $('.ch2').prop('disabled', true);
                }

            });
            $('.ch3').prop('disabled', true);
            $('#dthirdstudent').on("click", function() {
                if ($(this).prop("checked") == true) {
                    $('.ch3').attr('disabled', false);
                } else {
                    $('.ch3').prop('disabled', true);
                }
            });
            $('.ch4').prop('disabled', true);
            $('#dfourthstudent').on("click", function() {
                if ($(this).prop("checked") == true) {
                    $('.ch4').attr('disabled', false);
                } else {
                    $('.ch4').prop('disabled', true);
                }
            });

            // $('#parent1_phone').keyup(function(){
            // var parent_ophone= $(this).val().length;

            // if(parent_ophone < 12 || parent_ophone ==0){
            // $('#parent1_phone-error').html('Enter valid phone number');
            // $('#parent1_phone-error').css('display','block');
            // }
            // else{
            // $('#parent1_phone-error').html('');
            // $('#parent1_phone-error').css('display','none'); 
            // }
            // })

            //  $('#parent2_phone').keyup(function(){
            // var parent_ophone= $(this).val().length;

            // if(parent_ophone < 12 || parent_ophone ==0){
            // $('#parent2_phone-error').html('Enter valid phone number');
            // $('#parent2_phone-error').css('display','block');
            // }
            // else{
            // $('#parent2_phone-error').html('');
            // $('#parent2_phone-error').css('display','none'); 
            // }
            // })
        });
    </script>
    <div class="footer">
        <?php include "footer.php" ?>
    </div>