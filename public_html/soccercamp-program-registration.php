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
</style>

<?php

       $grades = array('Pre K', 'KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher');

       $check = $db->get_row("select is_new_registration_open, new_registration_start_date, new_registration_end_date, is_new_registration_free, new_registration_fees_form_head, new_registration_fees, registration_page_termsncond, school_name from ss_client_settings where status = 1");
       $ramount = round($check->new_registration_fees, 0);
       if($check->new_registration_fees_form_head == 0 && $check->is_new_registration_free == 0){
            $amount_per_form = $ramount;
       }elseif($check->new_registration_fees_form_head == 1 && $check->is_new_registration_free == 0){
            // $amount1 = $ramount;
            // $amount2 = ($ramount * 2);
            // $amount3 = ($ramount * 3);
            // $amount4 = ($ramount * 4);
            $amount_per_head = $ramount;
       }
       $check_start_date = date('m/d/Y', strtotime($check->new_registration_start_date));
       $check_end_date = date('m/d/Y', strtotime($check->new_registration_end_date));
       if($check->is_new_registration_open == 1 && date('m/d/Y') >= $check_start_date  && date('m/d/Y') <= $check_end_date){
  
      
 ?>
<!-- Content area -->
<div class="content">
    <h2 style="font-size:24px">Registration Form </h2>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <h2 style="font-size:32px; margin:0;font-weight: bold;"><?php echo $check->school_name ?></h2>
                    <h2 style="font-size:20px; margin:0">Develop the skills with us!</h2>
                    <h2 style="font-size:20px; margin:0;margin-top: 15px;">Kids will be provided with a tshirt and water
                    </h2>
                </div>
                <div class="col-md-2 text-align: center;">
                    <h3 style="margin:0;font-size: 18px;">June 14 - July 18, 2021</h3>
                    <h3 style="margin:0;font-size: 18px;">Monday to Thursday </h3>
                    
                </div>
                <div class="col-md-3 text-align: center;">
                <h3 style="margin:0;font-size: 18px;">Time - 10:30AM to 1:30pm</h3>
                    <h3 style="margin:0;font-size: 18px;">Youth Ages 7 - 13</h3>
                </div>
                <div class="col-md-3 text-align: center;">
                    <h3 style="margin:0;font-size: 18px;">Fees <span
                            style="font-size: 20px;border-radius: 500px;padding: 0 15px;"
                            class="label label-danger">$100 per child</span></h3>
                </div>
            </div>
        </div>
    </div>
    <form name="frm_register" class=" reg_form mt-20 form-validate-jquery" id="frm_register" method="post">
        <div class="row">
            <div class="col-md-12"> <strong>First Child<span class="mands">*</span></strong> </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="child1_first_name" lettersonly="true" tabindex="5" id="child1_first_name"
                    maxlength="25" placeholder="First Name" lettersonly="true" value=""
                    class="form-control stu1 required">
            </div>
            <div class="col-md-3">
                <input type="text" name="child1_last_name" lettersonly="true" tabindex="10" id="child1_last_name"
                    maxlength="25" placeholder="Last Name" value="" class="form-control required" lettersonly="true">
            </div>
            <div class="col-md-3">
                <select name="child1_gender" id="child1_gender" tabindex="15" class="form-control required">
                    <option value="">Select Gender</option>
                    <option value="m">Male
                    </option>
                    <option value="f">Female
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="child1_dob" id="child1_dob" tabindex="20" value="" placeholder="Date of Birth"
                    class="form-control required datepicker bgcolor-white">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="child1_allergies" id="child1_allergies" maxlength="50" tabindex="25"
                    placeholder="Any allergies" class="form-control">
            </div>

            <?php /* <div class="col-md-3">
                <select name="child1_grade" class="form-control" tabindex="30">
                    <option value="">Select Grade</option>
                    <?php foreach ($grades as $garde) { ?>
            <option value="<?= $garde ?>"><?= $garde ?></option>
            <?php } ?>
            </select>
        </div> */ ?>

        <div class="col-md-3">
            <select name="child1_tshirt_size" id="child1_tshirt_size" tabindex="15" class="form-control required">
                <option value="">TShirt Size</option>
                <option value="XS">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
                <option value="XXL">XXL</option>

            </select>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <lable>Share pictures of your children's activities on FB</strong>
                    <div class="col-md-12">
                        <label class="radio-inline">
                            <input type="radio" class="required" id="radio1" name="child1_share_picture" value="1">
                            Yes
                        </label>

                        <label class="radio-inline">
                            <input type="radio" class="required" id="radio2" name="child1_share_picture" value="0">
                            No
                        </label>
                    </div>
            </div>
        </div>



</div>

<div class="row mar-top-20">
    <div class="col-md-12"> <strong>Second Child</strong> </div>
</div>

<div class="row">
    <div class="col-md-3">
        <input type="text" name="child2_first_name" id="child2_first_name" lettersonly="true" tabindex="40"
            placeholder="First Name" maxlength="25" value="" class="form-control child2 stu1" lettersonly="true">
    </div>
    <div class="col-md-3">
        <input type="text" name="child2_last_name" lettersonly="true" tabindex="45" placeholder="Last Name"
            maxlength="25" value="" class="form-control child2" lettersonly="true">
    </div>
    <div class="col-md-3">
        <select name="child2_gender" class="form-control child2" tabindex="50">
            <option value="">Select Gender</option>
            <option value="m">Male
            </option>
            <option value="f">Female
            </option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="child2_dob" tabindex="55" placeholder="Date of Birth" value=""
            class="form-control datepicker child2 bgcolor-white">
    </div>

</div>
<div class="row">
    <div class="col-md-3">
        <input type="text" name="child2_allergies" id="child2_allergies" maxlength="50" tabindex="60"
            placeholder="Any allergies" class="form-control">
    </div>

    <?php /* <div class="col-md-3 hide">
                <select name="child2_grade" class="form-control child2 hide" tabindex="65">
                    <option value="">Select Grade</option>
                    <?php foreach ($grades as $garde) { ?>
    <option value="<?= $garde ?>"><?= $garde ?></option>
    <?php } ?>
    </select>
</div> */ ?>

<div class="col-md-3">
    <select name="child2_tshirt_size" id="child2_tshirt_size" tabindex="15" class="form-control child2">
        <option value="">TShirt Size</option>
        <option value="XS">XS</option>
        <option value="S">S</option>
        <option value="M">M</option>
        <option value="L">L</option>
        <option value="XL">XL</option>
        <option value="XXL">XXL</option>

    </select>
</div>

<div class="col-md-3">
    <div class="form-group">
        <lable>Share pictures of your children's activities on FB</strong>
            <div class="col-md-12">
                <label class="radio-inline">
                    <input type="radio" id="radio1" class="child2" name="child2_share_picture" value="1">
                    Yes
                </label>

                <label class="radio-inline">
                    <input type="radio" id="radio2" class="child2" name="child2_share_picture" value="0"> No
                </label>
            </div>
    </div>
</div>
<div class="col-md-3">
    <a href="javascript:void(0);" class="btn btn-success" onclick="clear_fileds('child2')">Clear</a>
</div>
</div>

<div class="row mar-top-20">
    <div class="col-md-12"> <strong>Third Child</strong> </div>
</div>
<div class="row">
    <div class="col-md-3">
        <input type="text" name="child3_first_name" id="child3_first_name" lettersonly="true" placeholder="First Name"
            maxlength="25" tabindex="75" value="" class="stu1 child3 form-control" lettersonly="true">
    </div>
    <div class="col-md-3">
        <input type="text" name="child3_last_name" lettersonly="true" placeholder="Last Name" maxlength="25" value=""
            class="child3 form-control" lettersonly="true" tabindex="80">
    </div>
    <div class="col-md-3">
        <select name="child3_gender" class="child3 form-control" tabindex="85">
            <option value="">Select Gender</option>
            <option value="m">Male
            </option>
            <option value="f">Female
            </option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="child3_dob" placeholder="Date of Birth" value="" tabindex="90"
            class="child3 form-control datepicker bgcolor-white">
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <input type="text" name="child3_allergies" id="child3_allergies" maxlength="50" tabindex="95"
            placeholder="Any allergies" class="form-control">
    </div>

    <?php /* <div class="col-md-3 hide">
                <select name="child3_grade" class="form-control child3 hide" tabindex="100">
                    <option value="">Select Grade</option>
                    <?php foreach ($grades as $garde) { ?>
    <option value="<?= $garde ?>"><?= $garde ?></option>
    <?php } ?>
    </select>
</div> */ ?>

<div class="col-md-3">
    <select name="child3_tshirt_size" id="child3_tshirt_size" tabindex="15" class="form-control child3">
        <option value="">TShirt Size</option>
        <option value="XS">XS</option>
        <option value="S">S</option>
        <option value="M">M</option>
        <option value="L">L</option>
        <option value="XL">XL</option>
        <option value="XXL">XXL</option>

    </select>
</div>

<div class="col-md-3">
    <div class="form-group">
        <lable>Share pictures of your children's activities on FB</strong>
            <div class="col-md-12">
                <label class="radio-inline">
                    <input type="radio" id="radio1" class="child3" name="child3_share_picture" value="1">
                    Yes
                </label>

                <label class="radio-inline">
                    <input type="radio" id="radio2" class="child3" name="child3_share_picture" value="0"> No
                </label>
            </div>
    </div>
</div>
<div class="col-md-3">
    <a href="javascript:void(0);" class="btn btn-success" onclick="clear_fileds('child3')">Clear</a>
</div>
</div>


<div class="row mar-top-20">
    <div class="col-md-12"> <strong>Fourth Child</strong> </div>
</div>
<div class="row">
    <div class="col-md-3">
        <input type="text" name="child4_first_name" id="child4_first_name" lettersonly="true" placeholder="First Name"
            maxlength="25" tabindex="101" value="" class="stu1 child4 form-control" lettersonly="true">
    </div>
    <div class="col-md-3">
        <input type="text" name="child4_last_name" lettersonly="true" placeholder="Last Name" maxlength="25" value=""
            class="stu4 child4 form-control" lettersonly="true" tabindex="102">
    </div>
    <div class="col-md-3">
        <select name="child4_gender" class="child4 form-control" tabindex="103">
            <option value="">Select Gender</option>
            <option value="m">Male
            </option>
            <option value="f">Female
            </option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="child4_dob" placeholder="Date of Birth" value="" tabindex="104"
            class="child4 form-control datepicker bgcolor-white">
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <input type="text" name="child4_allergies" id="child4_allergies" maxlength="50" tabindex="105"
            placeholder="Any allergies" class="form-control">
    </div>

    <?php /* <div class="col-md-3 hide">
                <select name="child4_grade" class="form-control child4 hide" tabindex="106">
                    <option value="">Select Grade</option>
                    <?php foreach ($grades as $garde) { ?>
    <option value="<?= $garde ?>"><?= $garde ?></option>
    <?php } ?>
    </select>
</div> */ ?>

<div class="col-md-3">
    <select name="child4_tshirt_size" id="child4_tshirt_size" tabindex="15" class="form-control child4">
        <option value="">TShirt Size</option>
        <option value="XS">XS</option>
        <option value="S">S</option>
        <option value="M">M</option>
        <option value="L">L</option>
        <option value="XL">XL</option>
        <option value="XXL">XXL</option>

    </select>
</div>

<div class="col-md-3">
    <div class="form-group">
        <lable>Share pictures of your children's activities on FB</strong>
            <div class="col-md-12">
                <label class="radio-inline">
                    <input type="radio" id="radio1" class="child4" name="child4_share_picture" value="1">
                    Yes
                </label>

                <label class="radio-inline">
                    <input type="radio" id="radio2" class="child4" name="child4_share_picture" value="0"> No
                </label>
            </div>
    </div>
</div>
<div class="col-md-3">
    <a href="javascript:void(0);" class="btn btn-success" onclick="clear_fileds('child4')">Clear</a>
</div>
</div>



<div class="row" style="margin-top:15px;">
    <div class="col-md-2">
        <h3 class="subtitle">Family Information</h3>
    </div>
    <div class="col-md-10"><label class="checkbox-inline" style="margin-top: 25px;">
            <!-- <input type="checkbox" tabindex="110" name="singleParent" id="singleParent"> Single Parent  -->
        </label>
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
            <input type="text" name="parent1_first_name" maxlength="25" value="" id="parent1_first_name" tabindex="115"
                placeholder="First Name" class="form-control required" lettersonly="true">
        </div>
        <div class="col-md-2">
            <input type="text" name="parent1_last_name" maxlength="25" value="" id="parent1_last_name" tabindex="120"
                placeholder="Last Name" class="form-control required" lettersonly="true">
        </div>
        <div class="col-md-2">
            <input type="text" tabindex="125" name="parent1_phone" value="" id="parent1_phone" phonenocheck="true"
                maxlength="10" placeholder="Phone Number" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="text" tabindex="130" name="parent1_email" value="" value="" id="parent1_email"
                placeholder="1st Parent E-mail" class="form-control required email">
        </div>
        <div class="col-md-3">
            <input type="radio" checked="" tabindex="135" name="which_is_primary_email" value="parent1"
                id="primary_email_parent1"> Primary Contact
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
            <input type="text" name="parent2_first_name" maxlength="25" value="" tabindex="140" id="parent2_first_name"
                placeholder="First Name" class="form-control parent2 required" lettersonly="true">
        </div>
        <div class="col-md-2">
            <input type="text" name="parent2_last_name" maxlength="25" value="" tabindex="145" id="parent2_last_name"
                placeholder="Last Name" class="form-control parent2 required" lettersonly="true">
        </div>
        <div class="col-md-2">
            <input type="text" name="parent2_phone" value="" id="parent2_phone" tabindex="150" phonenocheck="true"
                maxlength="10" placeholder="Phone Number" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="text" name="parent2_email" value="" tabindex="155" id="parent2_email"
                placeholder="2nd Parent  E-mail" class="form-control parent2 email">
            <input type="hidden" name="primary_email" id="primary_email" tabindex="160">
            <input type="hidden" name="secondary_email" id="secondary_email" tabindex="165">
        </div>
        <div class="col-md-3">
            <input type="radio" name="which_is_primary_email" tabindex="170" value="parent2" id="primary_email_parent2">
            Secondery Contact
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
        <input type="text" name="address_1" id="address_1" value="" maxlength="200" tabindex="185"
            placeholder="Address Line 1" class="form-control required">
    </div>
    <div class="col-md-3">
        <input type="text" name="address_2" maxlength="200" value="" placeholder="Address Line 2" tabindex="190"
            class="form-control">
    </div>
    <div class="col-md-3">
        <input type="text" name="city" id="city" maxlength="45" value="" placeholder="City"
            class="form-control required" lettersonly="true" tabindex="195">
    </div>
    <div class="col-md-3">
        <?php $states = $db->get_results("select * from ss_state where is_active = 1 order by state asc"); ?>
        <select name="state" id="state" tabindex="200" class="required form-control">
            <option value="">Select State</option>
            <?php  foreach($states as $st){ ?>
            <option value="<?php echo  $st->id; ?>"><?php echo  $st->state; ?></option>
            <?php }?>
        </select>
    </div>
</div>
<br>
<div class="row">

    <div class="col-md-3">
        <input type="text" name="post_code" maxlength="5" id="post_code" value="" zipCodeCheck="true"
            placeholder="Zipcode" class="form-control required" tabindex="205">
    </div>
    <div class="col-md-3">
        <select name="country_id" id="country_id" class="form-control" tabindex="210">
            <option value="1">USA</option>
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
            value="<?php // echo $bank_acc_no ?>" name="bank_acc_no" id="bank_acc_no" class="form-control
            type="text">
    </div>
    <div class="col-md-3"> <strong>Routing Number <span class="mands">*</span></strong>
        <input placeholder="Routing Number" maxlength="16" NewRoutingNoCheck="true" name="routing_no" id="routing_no" class="form-control"
            type="text">
    </div>
    <div class="col-md-3 col-xs-6 pt-20" id="td_bankname"></div>
</div> -->

<div class="row" style="margin-top:15px;">
    <div class="col-md-12">
        <h3 class="subtitle">Payment Information</h3>
    </div>
</div>
<div class="row" id="tr_credit_card">

    <input type="hidden" name="credit_card_type" id="credit_card_type">

    <div class="col-md-2"> <strong>Credit Card Number <span class="mands">*</span></strong>
        <input placeholder="Credit Card No" maxlength="16" creditCardNoCheck="true" value="" id="credit_card_no"
            name="credit_card_no" class="form-control required" type="text">
    </div>
    <div class="col-md-2"> <strong>Expiration Month <span class="mands">*</span></strong>
        <select name="credit_card_exp_month" creditCardExpMonthCheck="true" id="credit_card_exp_month"
            class="form-control required">
            <option value="">Select Month</option>
            <?php for ($i = 1; $i <= 12; $i++) {?>
            <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>">
                <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-2">
        <strong>Expiration Year <span class="mands">*</span></strong>

        <select name="credit_card_exp_year" creditCardExpYearCheck="true" id="credit_card_exp_year"
            class="form-control required">
            <option value="">Select Year</option>
            <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++) {?>
            <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>">
                <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
            <?php }?>
        </select>
    </div>
    <div class="col-md-1"> <strong>CVV <span class="mands">*</span></strong>
        <input placeholder="CVV" maxlength="4" disabled name="credit_card_cvv" id="cvv"
            checkCreditCardCVV="credit_card_type" value="" class="form-control required" type="text">
    </div>

    <!--  <div class="col-md-2"> 
                <strong>Comment <span class="mands">*</span></strong>
                <input type="text" name="comment_post" value="" class="form-control required"  placeholder="Comment" maxlength="100"  id="comment">
            </div> -->

    <div class="col-md-2 feeshow" style="display: none;">
        <strong>Registration Fee <span class="mands">*</span></strong>
        <p style="margin-top: 5px;margin-left: 20px;" class="fees"></p>
        <input type="hidden" name="registration_fee" class="regfees" value="">
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-12"> <strong>Any other info/comments/special requests </strong>
        <textarea name="addition_notes" id="addition_notes" class="form-control" tabindex="215"></textarea>
    </div>
</div>
<br>
<?php if(isset($check->registration_page_termsncond)){?>
<div class="row">
    <div class="col-md-12">
        <strong style="margin-bottom:15px;">Term and Conditions</strong><br><br>
        <?php echo  $check->registration_page_termsncond ?>
        <br><br>
        <strong style="margin-top:15px;">
            <input type="checkbox" id="school_rules" style="margin-right:5px;" tabindex="220">
            I have read the above rules and regulations and agree to accept and comply with them <span
                class="mands">*</span></strong> <br>
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
        <input type="hidden" name="action" value="student_register">
        <input type="submit" value="SAVE" class="btn btn-success btn-block btnsubmit" tabindex="225">
    </div>
</div>
<input type="hidden" name="form_submit" value="1">
</form>
<!-- /advanced login -->


<?php }else{?>
<style>
body {
    font-family: Roboto, Helvetica Neue, Helvetica, Arial, sans-serif;
    font-size: 13px;
    line-height: 1.5384616;
    color: #333;
    background-color: white !important;
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
                <h1>
                    Oops!</h1>
                <h2>Registrations Closed</h2>
            </div>
        </div>
    </div>
</div>
<?php } ?>
</div>
<!-- /content area -->


<script src="https://ick.bayyan.org/assets/js/jquery-ui.min.js"></script>
<!-- <script src="https://ick.bayyan.org/assets/js/jquery-ui-timepicker-addon.js"></script> -->
<script type="text/javascript">
function clear_fileds(clsName) {
    $('.' + clsName).val('');
    $('.' + clsName).removeClass('required');
    $('.' + clsName).parent().find('label').remove('.error');
    $('.' + clsName).parent().find('select').removeClass('error');
    $('.' + clsName).valid();

    calculate_amount();
}

function child2_child3(clsName) {
    if ($.trim($('#' + clsName + '_first_name').val()) != '') {
        $('.' + clsName).addClass('required');
    } else {
        $('.' + clsName).removeClass('required');
        $('.' + clsName).parent().find('label').remove('.error');
        $('.' + clsName).parent().find('select').removeClass('error');
    }

    //     var counter = 0;
    //     $('.' + clsName).each(function(index, element) {
    //         if ($(this).val() == '') {
    //             counter++;
    //         }
    //     });
    // alert(counter);
    //     if (counter < 6) {
    //         $('.' + clsName).addClass('required');
    //     } else {
    //         $('.' + clsName).removeClass('required');
    //         $('.' + clsName).parent().find('label').remove('.error');
    //         $('.' + clsName).parent().find('select').removeClass('error');
    //     }
}

function calculate_amount() {
    var calculated_amount = 0;

    if ($.trim($('#child1_first_name').val()) != '') {
        <?php if($amount_per_head){ ?>
        $('.feeshow').show();
        calculated_amount = <?php echo $amount_per_head ?>;
        $(".regfees").val(calculated_amount);
        $(".fees").html("$" + calculated_amount);
        <?php }else if($amount_per_form){ ?>
        $('.feeshow').show();
        $(".regfees").val("<?php echo $amount_per_form ?>");
        $(".fees").html("$<?php echo $amount_per_form ?>");
        <?php } ?>
    }
    if ($.trim($('#child2_first_name').val()) != '') {
        <?php if($amount_per_head){ ?>
        $('.feeshow').show();
        calculated_amount = parseInt(calculated_amount) + <?php echo $amount_per_head ?>;
        $(".regfees").val(calculated_amount);
        $(".fees").html("$" + calculated_amount);
        <?php }else if($amount_per_form){ ?>
        $('.feeshow').show();
        $(".regfees").val("<?php echo $amount_per_form ?>");
        $(".fees").html("$<?php echo $amount_per_form ?>");
        <?php } ?>
    }
    if ($.trim($('#child3_first_name').val()) != '') {
        <?php if($amount_per_head){ ?>
        $('.feeshow').show();
        calculated_amount = parseInt(calculated_amount) + <?php echo $amount_per_head ?>;
        $(".regfees").val(calculated_amount);
        $(".fees").html("$" + calculated_amount);
        <?php }else if($amount_per_form){ ?>
        $('.feeshow').show();
        $(".regfees").val("<?php echo $amount_per_form ?>");
        $(".fees").html("$<?php echo $amount_per_form ?>");
        <?php } ?>
    }
    if ($.trim($('#child4_first_name').val()) != '') {
        <?php if($amount_per_head){ ?>
        $('.feeshow').show();
        calculated_amount = parseInt(calculated_amount) + <?php echo $amount_per_head ?>;
        $(".regfees").val(calculated_amount);
        $(".fees").html("$" + calculated_amount);
        <?php }else if($amount_per_form){ ?>
        $('.feeshow').show();
        $(".regfees").val("<?php echo $amount_per_form ?>");
        $(".fees").html("$<?php echo $amount_per_form ?>");
        <?php } ?>
    }
}

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

jQuery(document).ready(function() {
    $('.guest_screen').removeClass('guest_screen ');
    cardFormValidate();

    $('#parent1_phone').mask('000-000-0000');
    $('#parent2_phone').mask('000-000-0000');

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

            $('#bank_acc_no').addClass('required');
            $('#routing_no').addClass('required');

        } else if ($(this).val() == 'credit_card') {
            $('#tr_ach').addClass('hide');
            $('#tr_credit_card').removeClass('hide');

            $('#bank_acc_no').removeClass('required');
            $('#routing_no').removeClass('required');

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
            $('#bank_acc_no').val('');
            $('#routing_no').val('');
            $('#bank_acc_no').removeClass('required');
            $('#routing_no').removeClass('required');

            /* $('#credit_card_type').removeClass('required');
               $('#credit_card_no').removeClass('required');*/
            $('#credit_card_exp_month').removeClass('required');
            $('#credit_card_exp_year').removeClass('required');
            $('#cvv').removeClass('required');
            //$('.dis_col').addClass('hide');
        }
    });

    $('input[name=which_is_primary_email]').change(function() {
        if ($(this).val() == "father") {
            $('#father_email').addClass('required');
            $('#mother_email').removeClass('required');
        } else {
            $('#father_email').removeClass('required');
            $('#mother_email').addClass('required');
        }
    });

    $('.datepicker').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: 100,
        min: [<?php echo date('Y') - 13 ?>, 5, 1],
        max: [<?php echo date('Y') - 7 ?>, 5, 30],
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

            $.get('https://www.usbanklocations.com/crn.phpq=' + $.trim($('#routing_no').val()),
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




    // $('#frm_register').submit(function() {
    // 	var val_rad_check = validate_radio_checkbox();

    // 	if ($('#frm_register').valid() && val_rad_check) {
    // 		$('.ajaxMsgBot').html('<strong><i class="fa fa-spinner fa-spin" style="font-size:26px;"></i> Please Wait...</strong>');
    // 		return true;
    // 	} else {
    // 		return false;
    // 	}
    // });

    // $('#payment_method').change(function() {
    // 	if ($(this).val() == 'auto_deduction_acc') {
    // 		$('#tr_credit_card').addClass('hide');
    // 		$('#credit_card_type').val('');
    // 		$('#credit_card_no').val('');
    // 		$('#credit_card_exp_month').val('');
    // 		$('#credit_card_exp_year').val('');
    // 		$('#postal_code').val('');

    // 		$('#tr_auto_deduction_acc').removeClass('hide');
    // 	} else if ($(this).val() == 'credit_card') {
    // 		$('#tr_auto_deduction_acc').addClass('hide');
    // 		$('#bank_acc_no').val('');
    // 		$('#routing_no').val('');

    // 		$('#tr_credit_card').removeClass('hide');
    // 	} else {
    // 		$('#tr_auto_deduction_acc').addClass('hide');
    // 		$('#tr_credit_card').addClass('hide');

    // 		$('#credit_card_type').val('');
    // 		$('#credit_card_no').val('');
    // 		$('#credit_card_exp_month').val('');
    // 		$('#credit_card_exp_year').val('');
    // 		$('#postal_code').val('');
    // 		$('#bank_acc_no').val('');
    // 		$('#routing_no').val('');
    // 	}
    // });

    $("#school_rules").change(function() {
        validate_radio_checkbox();
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
            $('.parent2').addClass('required');
        }
    });

    $('#frm_register').submit(function(e) {
        e.preventDefault();
        var val_rad_check = validate_radio_checkbox();

        if ($('#frm_register').valid() && val_rad_check) {
            //$('.btnsubmit').attr('disabled', true);
            $('.ajaxMsgBot').html(
                '<h3 class="mar-top-zero">Processing...Please Wait</h3>');
            var targetUrl = '<?php echo $SITEURL ?>ajax/ajss-soccercamp-register';
            var formDate = $(this).serialize();
            $.post(targetUrl, formDate, function(data, status) {
                if (status == 'success') {
                    if (data.code == 1) {
                        //$('.btnsubmit').attr('disabled', false);
                        //$('.ajaxMsgBot').html('');
                        //displayAjaxMsg(data.msg, data.code);
                        window.location = data.targeturl;
                        //COMMENTED ON 30-MAR-2020
                        // $("#frm_register")[0].reset();
                        // $('#td_bankname').hide();
                        // $('.ajaxMsgBot').hide();

                        // $('.' + 'child2').removeClass('required');
                        // $('.' + 'child2').parent().find('label').remove('.error');
                        // $('.' + 'child2').parent().find('select').removeClass('error');

                        // $('.' + 'child3').removeClass('required');
                        // $('.' + 'child3').parent().find('label').remove('.error');
                        // $('.' + 'child3').parent().find('select').removeClass('error');
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }
                } else {
                    displayAjaxMsg(data.msg);
                }
            }, 'json');
        }
    });

    $(".stu1").blur(function() {
        calculate_amount();
    });
});
</script>
<?php include "footer.php" ?>