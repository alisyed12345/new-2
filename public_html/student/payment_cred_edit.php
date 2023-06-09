<?php
$mob_title = "Payment Credentials";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
//if(!check_userrole_by_code('UT01') && !check_userrole_by_code('UT04')){
if ((!check_userrole_by_code('UT01') && !check_userrole_by_code('UT04')) || (check_userrole_by_code('UT01') && !check_userrole_by_group('admin'))) {
    include "../includes/unauthorized_msg.php";
    return;
}

$user = $db->get_row("select * from ss_user where id='" . $_GET['id'] . "'");
$student = $db->get_row("select * from ss_student where user_id='" . $_GET['id'] . "'");
$family_id = $student->family_id;

$group_id = $db->get_var("select group_id from ss_studentgroupmap where student_user_id='" . $_GET['id'] . "' order by id desc limit 1");
$group = $db->get_var("select group_name from ss_groups where id='" . $group_id . "'");

$paymentcred = $db->get_row("select * from ss_paymentcredentials where family_id='" . $family_id . "'");
$paymentcred_id = $paymentcred->id;

$credit_card_type = base64_decode($paymentcred->credit_card_type);
$credit_card_no = str_replace(' ', '', base64_decode($paymentcred->credit_card_no));
$credit_card_exp = base64_decode($paymentcred->credit_card_exp);
$postal_code = base64_decode($paymentcred->postal_code);

$credit_card_expAry = explode('-', $credit_card_exp);
$credit_card_exp_month = $credit_card_expAry[0];
$credit_card_exp_year = $credit_card_expAry[1];

//$postal_code = $paymentcred->postal_code;
$bank_acc_no = base64_decode($paymentcred->bank_acc_no);
$routing_no = base64_decode($paymentcred->routing_no);

?>

<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Payment Credentials</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i>
                    Dashboard</a></li>
            <li><a href="<?php echo SITEURL . "student/students_list" ?>">Students List</a></li>
            <li class="active">Payment Credentials</li>
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

                        <legend class="text-semibold"><i class="icon-user position-left"></i> Student Information
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Group:</label>
                                    <input readonly="readonly" value="<?php echo $group ?>" class="form-control"
                                        type="text">
                                </div>
                            </div>
                        </div>
                        <legend class="text-semibold"><i class="icon-coin-dollar position-left"></i> Payment Credentials
                        </legend>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="radio-inline">
                                        <input type="radio" name="payment_method" id="credit_card"
                                            <?php echo $credit_card_type == '' ? '' : 'checked="checked"' ?>
                                            value="credit_card">
                                        Credit Card </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="payment_method" id="auto_deduction_acc"
                                            <?php echo $bank_acc_no == '' ? '' : 'checked="checked"' ?>
                                            value="auto_deduction_acc">
                                        Automatic Dedecution From Checking Account </label>
                                </div>
                            </div>
                        </div>
                        <div class="row <?php echo $credit_card_type == '' ? 'hide' : '' ?>" id="row_credit_card">
                        <div class="col-md-12 mb-10 text-danger">
                        To edit the CC number click on Edit checkbox and replace the exiting CC details with the new one
                        </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Credit Card Type:<span class="mandatory">*</span></label>
                                    <select name="credit_card_type" creditCardTypeCheck="true" id="credit_card_type"
                                        class="form-control">
                                        <option value="">Select</option>
                                        <option value="americanexpress"
                                            <?php echo strtolower($credit_card_type) == "americanexpress" ? 'selected="selected"' : '' ?>>
                                            American Express</option>
                                        <option value="visa"
                                            <?php echo strtolower($credit_card_type) == "visa" ? 'selected="selected"' : '' ?>>
                                            Visa</option>
                                        <option value="mastercard"
                                            <?php echo strtolower($credit_card_type) == "mastercard" ? 'selected="selected"' : '' ?>>
                                            MasterCard</option>
                                        <option value="discover"
                                            <?php echo strtolower($credit_card_type) == "discover" ? 'selected="selected"' : '' ?>>
                                            Discover</option>
                                        <option value="dinersclub"
                                            <?php echo strtolower($credit_card_type) == "dinersclub" ? 'selected="selected"' : '' ?>>
                                            Diners Club</option>
                                        <option value="jcb"
                                            <?php echo strtolower($credit_card_type) == "jcb" ? 'selected="selected"' : '' ?>>
                                            JCB</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Credit Card No:<span class="mandatory">*</span>
                                        <div class="form-check checkbox-inline" for="edit_cc" style="margin-left:30px; padding-left:20px">
                                            <input type="checkbox" name="edit_cc" id="edit_cc" value="edit_cc"
                                                style="margin-right:5px"><label class="form-check-label" for="edit_cc">Edit</label>                                            
                                        </div>
                                    </label>
                                    <div class="input-group">
                                        <input placeholder="Credit Card No" readonly="readonly" maxlength="23"
                                            value="************<?php echo substr($credit_card_no,-4) ?>"
                                            id="credit_card_no" name="credit_card_no" class="form-control" type="text">
                                        <!-- <span class="input-group-addon cceye cursor_default"><i class="icon-eye"></i></span> -->
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-2">
                                <div class="form-group">
                                    <label>Edit Credit Card </label>
                                </div>
                            </div> -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Expiration Month:<span class="mandatory">*</span></label>
                                    <select name="credit_card_exp_month" creditCardExpMonthCheck="true"
                                        id="credit_card_exp_month" class="form-control">
                                        <option value="">Select Month</option>
                                        <?php for ($i = 1; $i <= 12; $i++) {?>
                                        <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>"
                                            <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) == $credit_card_exp_month ? 'selected="selected"' : '' ?>>
                                            <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Expiration Year:<span class="mandatory">*</span></label>
                                    <select name="credit_card_exp_year" creditCardExpYearCheck="true"
                                        id="credit_card_exp_year" class="form-control">
                                        <option value="">Select Year</option>
                                        <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++) {?>
                                        <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>"
                                            <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) == $credit_card_exp_year ? 'selected="selected"' : '' ?>>
                                            <?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>ZipCode:</label>
                                    <input placeholder="ZipCode" maxlength="5" name="postal_code" id="postal_code"
                                        zipCodeCheck="true" value="<?php echo $postal_code ?>" class="form-control"
                                        type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row <?php echo $bank_acc_no == '' ? 'hide' : '' ?>" id="row_auto_deduction_acc">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bank Account Number:<span class="mandatory">*</span></label>
                                    <input placeholder="Bank Account Number" bankAcNoCheck="true"
                                        value="<?php echo $bank_acc_no ?>" name="bank_acc_no" class="form-control"
                                        type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Routing Number:<span class="mandatory">*</span></label>
                                    <input placeholder="Routing Number" routingNoCheck="true"
                                        value="<?php echo $routing_no ?>" name="routing_no" class="form-control"
                                        type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="action" value="edit_payment_credentials">
                                <input type="hidden" name="paymentcred_id" value="<?php echo $paymentcred_id ?>">
                                <button type="submit" class="btn btn-success"><i
                                        class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
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
    //HIDE/SHOW CREDIT CARD NUMBER
    // $('.cceye').click(function(){
    //   if($('#credit_card_no').hasClass('viewable')){
    //     $('#credit_card_no').removeClass('viewable');
    //     $(this).find('i').removeClass('icon-eye-blocked').addClass('icon-eye');
    //     $('#credit_card_no').attr('type','password');
    //   }else{
    //     $('#credit_card_no').addClass('viewable');
    //     $(this).find('i').addClass('icon-eye-blocked').removeClass('icon-eye');
    //     $('#credit_card_no').attr('type','text');
    //   }
    // });

    $('#edit_cc').change(function() {
        if ($(this).is(':checked')) {
            $('#credit_card_no').removeAttr('readonly');
            $('#credit_card_no').val('');
            $('#credit_card_no').attr('creditCardNoCheck', 'true');
        } else {
            $('#credit_card_no').attr('readonly', 'readonly');
            $('#credit_card_no').val('************<?php echo substr($credit_card_no,-4) ?>');
            $('#credit_card_no').removeAttr('creditCardNoCheck');
            $('#credit_card_no').valid();
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

        $('#frmICK').valid();
    });

    $("input:radio[name=payment_method]").click(function() {
        if ($(this).val() == 'credit_card') {
            $('#row_auto_deduction_acc').addClass('hide');
            $('#row_credit_card').removeClass('hide');
        } else {
            $('#row_credit_card').addClass('hide');
            $('#row_auto_deduction_acc').removeClass('hide');
        }

        //$('#frmICK').valid();
    });
});
</script>
<?php include "../footer.php"?>