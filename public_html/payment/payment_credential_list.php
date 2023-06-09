<?php
$mob_title = "Payment Credential";

include "../header.php";

if (!in_array("su_payment_credential_list", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    exit;
}

if (isset($_SESSION['icksumm_uat_login_familyid']) && $_SESSION['icksumm_uat_login_usertypecode'] == 'UT05') {
    $user_id = trim(htmlspecialchars($_SESSION['icksumm_uat_login_familyid']));
} else {
    $user_id = trim(htmlspecialchars($_GET['id']));
}

$fees_setting_data = $db->get_row("select is_new_registration_free,new_registration_fees_form_head,new_registration_fees from ss_client_settings where status=1");

$family = $db->get_row("select * from ss_family where id='" . $user_id . "' And is_deleted=0");

$students = $db->get_results("select * from ss_student as s INNER JOIN ss_student_session_map as m ON m.student_user_id = s.user_id INNER JOIN ss_user as u ON u.id = s.user_id where s.family_id ='" . $family->id . "' AND session_id='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");

$family_reg_id = $db->get_var("select c.sunday_school_reg_id from ss_student as s 
inner join  ss_sunday_sch_req_child  as c on c.user_id = s.user_id
inner join ss_family as f on f.id=s.family_id
where family_id='" . $family->id . "'");

if(!empty($family_reg_id)){
    $family_reg_id=$family_reg_id;
}else{
    $family_reg_id="";
}


if(count((array)$students) > 0 && $family->is_paid_registration_fee == 0){

    if ($fees_setting_data->new_registration_fees_form_head == 0 && $fees_setting_data->is_new_registration_free == 0) {
        $amount = $fees_setting_data->new_registration_fees;
    } elseif ($fees_setting_data->new_registration_fees_form_head == 1 && $fees_setting_data->is_new_registration_free == 0) {
        $amount = $fees_setting_data->new_registration_fees * count((array)$students);
    }

}
// comment
$_SESSION['token']  = genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD);
$request_token  = 'req_'.RandomString();

?>
<style>
    label.error {
        color: red;  
    }
</style>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Payment Credentials</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
            <li class="active"><a href="family_info.php">Family Info</a></li>
            <li class="active">Payment Credentials</li>
        </ul>
    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-flat">
                <div class="panel-body">
                    <div class="ajaxMsg"></div>
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <label><strong> 1st Parent Name: </strong>
                                <?php if (isset($family->father_first_name)) {
                                    echo $family->father_first_name . ' ' . $family->father_last_name;
                                } ?>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label><strong> 1st Parent Phone : </strong>
                                <?php if (isset($family->father_phone)) {
                                    echo internal_phone_check($family->father_phone);
                                } ?>
                            </label>
                        </div>
                        <div class="col-md-4 col-sm-4 text-right ">
                            <a href="javascript:;" id="addcreditcard" class="text-primary">+ ADD NEW CREDIT CARD</a>
                        </div>
                    </div>
                    <table class="table datatable-basic table-bordered dataTable no-footer dtr-inline" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">
                        <thead>
                            <tr role="row">
                                <th>Last 4 Digits of CC</th>
                                <th>Expiry</th>
                                <th>Default</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- /main content -->
</div>
<!-- /page content -->
</div>
<!-- /page container -->
<!-- Add Modal - Staff Detail  -->
<div id="addcard" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content maincontentsecond hide">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h5 class="modal-title  maincontentitile"></h5>
            </div>
            <div class="modal-body bodaycontenthere">
            </div>
            <div class="modal-footer footermdsecond">
                <div class="row col-md-4">
                    <strong id="confirmmsg"></strong>
                </div>
                <div class="col-md-8">
                    <button type="button" onclick="PaymentCondition('Yes')" class="btn btn-light btnsubmit btn-lg bg-success">Add cridt card & pay now</button>
                    <button type="button" onclick="PaymentCondition('No')" class="btn btn-danger btnsubmit btn-lg ">Add cridt card & skip pay </button>
                </div>
            </div>
        </div>
        <div class="modal-content maincontentfirst">
            <form id="frmAddcreditcard" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h5 class="modal-title headtext" id="familyinfo_title"> <b>ADD CREDIT CARD</b></h5>
                </div>
                <br>
                <div class="container showolddetailes">
                    <div class="row; margin-left:25px;">
                        <div class="col-md-6 col-sm-6">
                            <strong>Credit Card Number</strong>
                            <p id="card_number"></p>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <strong>Expiry Month/Year</strong>
                            <p id="card_exp_month_year"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <label for="credit_card_no">Credit Card Number: <span class="mandatory">*</span></label>
                                <input placeholder="**** **** **** ****" name="credit_card_no" id="credit_card_no" creditCardNoCheck_2="true" maxlength="16" class="form-control required" type="text" />
                                <input type="hidden" name="credit_card_type" id="credit_card_type">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <label style="padding: 0px;" for="expiry_month">Expiry Month:<span class="mandatory">*</span></label>
                                <select class="ddropdown form-control required" name="exp_month" id="expiry_month">
                                    <option value="">Select</option>
                                    <option value="01">01</option>
                                    <option value="02">02</option>
                                    <option value="03">03</option>
                                    <option value="04">04</option>
                                    <option value="05">05</option>
                                    <option value="06">06</option>
                                    <option value="07">07</option>
                                    <option value="08">08</option>
                                    <option value="09">09</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <label for="expiry_year">Expiry Year:<span class="mandatory">*</span></label>
                                <select class="ddropdown form-control required" name="exp_year" id="expiry_year">
                                <option value="">Select</option>
                                        <?php for($i=0;$i<10;$i++){
                                            $year = date('Y');
                                            $years = $year+$i; ?>
                                            <option value="<?php echo $years;?>"><?php echo $years;?></option>
                                        <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                            <div class="form-group">
                                <label for="cvv_no">CVV:<span class="mandatory">*</span></label>
                                <input placeholder="***" name="cvv_no" id="cvv_no" class="form-control required" checkCreditCardCVV="true" maxlength="4" type="password" aria-required="true" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php $ss_paymentcredentials = $db->get_row("select * from ss_paymentcredentials where family_id='" . $user_id . "' and default_credit_card = '1' ");?>
                        
                        <div class="col-md-12">
                            <div class="form-group hide">
                                <label for="Default">Set Default:<span class="mandatory">*</span></label>
                                <!-- <div class="setdefalutcheck"> -->
                                    <input type="radio" name="default" checked value="Yes" id="default" class="required"> Yes &nbsp;&nbsp;&nbsp;&nbsp;
                                    <?php if($ss_paymentcredentials){ ?>
                                    <!-- <input type="radio" name="default" value="No" id="default" aria-label="required"> No -->
                                    <?php } ?>
                                <!-- </div> -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 notes" style="margin-left: 12px;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="col-md-9">
                                <strong id="statusMsg"></strong>
                            </div>
                            <div class="col-md-3">
                                    <div id="reg_amount"></div>
                                    <input type="hidden" name="action" id="action" value="credit_card_add"> 
                                    <input type="hidden" name="privewschedule" id="privewschedule" value="">
                                    <input type="hidden" id="student_fees_items_ids" name="student_fees_items_ids" value=""> 
                                    <input type="hidden" id="decline_total_amount" name="decline_total_amount" value="">
                                    <input type="hidden" name="payid" id="payid" value="">
                                    <input type="hidden" name="family_id" id="family_id" value="<?php echo $user_id; ?>">
                                    <input type="hidden" name="firstName"  value="<?php echo $family->father_first_name; ?>">
                                    <input type="hidden" name="lastName"  value="<?php echo $family->father_last_name; ?>">
                                    <input type="hidden" name="email"  value="<?php echo $family->primary_email; ?>">
                                    <input type="hidden" name="phone"  value="<?php echo $family->father_phone; ?>">
                                    <input type="hidden" name="city"  value="<?php echo $family->billing_city; ?>">
                                    <input type="hidden" name="zipcode"  value="<?php echo $family->billing_post_code; ?>">
                                    <input type="hidden" name="address1" value="<?php echo $family->billing_address_1; ?>">
                                    <input type="hidden" name="system_ip" value="<?=$_SERVER['REMOTE_ADDR']?>" />
                                    <input type="hidden" name="session" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION'] ?>"/>
                                    <?php if(!empty($family->forte_customer_token)){ ?>
                                        <input type="hidden" name="forte_customer_token" value="<?php echo $family->forte_customer_token; ?>" />
                                    <?php } ?>
                                    <input type="hidden" id="father_reg_id" name="father_reg_id" />
                                    <input type="hidden" name="auth_token" value="<?=$_SESSION['token']?>" />
                                    <input type="hidden" name="request_token" value="<?php echo $request_token ?>" />
                                    <button type="submit" class="btn btn-primary btnsubmit">SUBMIT</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">close</button>
                                </div>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
<!--    /Add modal -->
<!--    /Add modal -->
<script type="text/javascript">
    $(document).ready(function() {
        fillTable();
        //Add Credit Card start
        jQuery.validator.addMethod("Digits", function(value, element) {
            return this.optional(element) || /^([0-9]{3,4})$/i.test(value);
        }, "Enter valid CVV");
        //VALIDATION - CVV
        jQuery.validator.addMethod("checkCreditCardCVV", function(value, element, params) {
            if ($('#credit_card_type').val() == "Amex") {
                return this.optional(element) || /^[0-9]{4}$/i.test(value);
            } else {
                return this.optional(element) || /^[0-9]{3}$/i.test(value);
            }
        }, "Enter valid CVV number");

        $(document).on('click', '#addcreditcard', function() {
            $('#addcard').modal('show');
            $('.headtext').html("ADD CREDIT CARD");
            $('.showolddetailes').hide();
            $('.btnsubmit').attr('disabled', false);
            //$('#default').attr('checked', false);
            $('#action').val("credit_card_add");

            <?php if(count((array)$students) > 0 && $family->is_paid_registration_fee == 0){ ?>
                let amount = "<?php echo $amount ?>";
                $('#reg_amount').html('<input type="hidden" name="amount" value="'+amount+'"><input type="hidden" name="is_reg_payment" value="1"><input type="hidden" name="frequency_type" value="onetime"><input type="hidden" name="is_waiting" value="0">');
                $('.notes').html("<label class='text-danger'><h5 class='text-danger'><b>Note : </b> Your fees wasn't deducted at the time of registration. On adding a credit card, your pending registration fee $"+amount+" will be deducted. </h5></lable>");
                let family_re_id = "<?php echo $family_reg_id ?>";
                
                $('#father_reg_id').val(family_re_id);

            <?php } ?>
        });

        $('#frmAddcreditcard').submit(function(e) {
            e.preventDefault();
            if ($('#frmAddcreditcard').valid()) {
                $('#statusMsg').html('Processing...');
                $('.btnsubmit').prop("type", "button");
                $('.btnsubmit').attr('disabled', true);
                var family_id = "<?php echo $user_id ?>";
                $('.footermdsecond').addClass('hide');

                <?php if(count((array)$students) > 0 && $family->is_paid_registration_fee == 0){ ?> 

                        var formDate = $('#frmAddcreditcard').serialize();
                        var post_url = "<?php echo PAYSERVICE_URL?>api/payment_request";
                        $.ajax({
                            type: 'POST',
                            url: post_url, 
                            dataType: "json",
                            crossDomain: true,
                            format: "json",
                            data: formDate,
                            success: (response) => {
                                //console.log(response);
                                if(response.data.code == 1){
                                    $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url',{
                                        payment_unique_id: response.data.transactionID,
                                        request_token: "<?php echo $request_token ?>",
                                        action: 'payment_verify',
                                    },function(data,status){                    
                                        if(status == 'success'){
                                            $('#addcard').modal('hide');
                                            $('.btnsubmit').prop("type", "submit");
                                            $('.btnsubmit').attr('disabled', false);
                                            if (data.code == 1) {
                                                if($('#action').val() == 'credit_card_add'){
                                                    $('#frmAddcreditcard').trigger('reset');
                                                }
                                                fillTable();
                                                displayAjaxMsg(data.msg, data.code);
                                                setTimeout(function() {
                                                    location.reload();
                                                }, 3000);
                                            } else {
                                                displayAjaxMsg(data.msg, data.code);
                                            }
                                        }
                                    },'json');

                                }else{
                                    $('.btnsubmit').prop("type", "submit");
                                    $('.btnsubmit').attr('disabled', false);
                                    displayAjaxMsg(response.data.message, response.data.code);
                                }
                            },
                            error: (response) => {
                                $('.btnsubmit').prop("type", "submit");
                                $('.btnsubmit').attr('disabled', false);
                                displayAjaxMsg(response.data.message);
                            }
                        })


                <?php }else{ ?>

                        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-credential';
                        $.post(targetUrl, {
                            family_id: family_id,
                            action: 'check_priveus_payment'
                        }, function(data, status) {
                            if (status == 'success') {
                                if (data.code == 1) {
                                    $('.maincontentitile').html('<b>You Have The Following Previously Declined Payments! </b>');
                                    $('.bodaycontenthere').html(data.msg);
                                    $('.footermdsecond').removeClass('hide');
                                    $('#student_fees_items_ids').val(data.student_fees_items_ids);
                                    $('#decline_total_amount').val(data.decline_total_amount);
                                    $('.maincontentsecond').removeClass('hide');
                                    $('.maincontentfirst').addClass('hide');
                                    $('.btnsubmit').prop("type", "submit");
                                    $('.btnsubmit').attr('disabled', false);
                                    return false;
                                } else {
                                        var formDate = $('#frmAddcreditcard').serialize();
                                        var post_url = "<?php echo PAYSERVICE_URL?>api/payment_token_request";
                                        $.ajax({
                                            type: 'POST',
                                            url: post_url,
                                            dataType: "json",
                                            crossDomain: true,
                                            format: "json",
                                            data: formDate,
                                            success: (response) => {
                                                //console.log(response);
                                                $('.btnsubmit').prop("type", "submit");
                                                $('.btnsubmit').attr('disabled', false);
                                                if(response.data.code == 1){

                                                    $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url',{
                                                        payment_token: response.data.transactionID,
                                                        request_token: "<?php echo $request_token ?>",
                                                        action: 'payment_verify_token',
                                                    },function(data,status){                    
                                                        if(status == 'success'){
                                                            $('#addcard').modal('hide');
                                                            if (data.code == 1) {
                                                                if($('#action').val() == 'credit_card_add'){
                                                                    $('#frmAddcreditcard').trigger('reset');
                                                                }
                                                                fillTable();
                                                                displayAjaxMsg(data.msg, data.code);
                                                            } else {
                                                                displayAjaxMsg(data.msg, data.code);
                                                            }
                                                        }
                                                    },'json');

                                                }else{
                                                    $('#addcard').modal('hide');
                                                    displayAjaxMsg(response.data.message, response.data.code);
                                                }
                                            },
                                            error: (response) => {
                                                $('#addcard').modal('hide');
                                                $('.btnsubmit').prop("type", "submit");
                                                $('.btnsubmit').attr('disabled', false);
                                                displayAjaxMsg(response.data.message);
                                            }
                                        })

                                }
                            }else {
                                displayAjaxMsg("something went wrong?.please try again.", 0);
                            }
                        }, 'json');

            <?php } ?>

            }

        });

            //ADDED BY UROOJ ON 02-OCT-2021 payment_token_request
            // $('#frmAddcreditcard').submit(function(e) {
            //     e.preventDefault();
            //     if ($('#frmAddcreditcard').valid()) {
            //         $('#statusMsg').html('Processing...');
            //         $('.btnsubmit').prop("type", "button");
            //         $('.btnsubmit').attr('disabled', true);

            //     var formDate = $(this).serialize();
            //     var post_url = "<?php echo PAYSERVICE_URL?>api/payment_token_request";
            //     $.ajax({
            //         type: 'POST',
            //         url: post_url,
            //         dataType: "json",
            //         crossDomain: true,
            //         format: "json",
            //         data: formDate,
            //         success: (response) => {
            //             //console.log(response);
            //             $('.btnsubmit').prop("type", "submit");
            //             $('.btnsubmit').attr('disabled', false);
            //             if(response.data.code == 1){

            //                 $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url',{
            //                     payment_token: response.data.transactionID,
            //                     request_token: "<?php echo $request_token ?>",
            //                     action: 'payment_verify_token',
            //                 },function(data,status){                    
            //                     if(status == 'success'){
            //                         $('#addcard').modal('hide');
            //                         if (data.code == 1) {
            //                             if($('#action').val() == 'credit_card_add'){
            //                                 $('#frmAddcreditcard').trigger('reset');
            //                             }
            //                             fillTable();
            //                             displayAjaxMsg(data.msg, data.code);
            //                         } else {
            //                             displayAjaxMsg(data.msg, data.code);
            //                         }
            //                     }
            //                 },'json');

            //             }else{
            //                 $('#addcard').modal('hide');
            //                 displayAjaxMsg(response.data.message, response.data.code);
            //             }
            //         },
            //         error: (response) => {
            //             $('#addcard').modal('hide');
            //             $('.btnsubmit').prop("type", "submit");
            //             $('.btnsubmit').attr('disabled', false);
            //             displayAjaxMsg(response.data.message);
            //         }
            //     })

            //     }
            // });


        $('#addcard').on('hide.bs.modal', function(e) {
            $('#reg_amount').html('');
            $('.notes').html('');
            $('.maincontentsecond').addClass('hide');
            $('.maincontentfirst').removeClass('hide');
            $('#privewschedule').val('');
            $('#statusMsg').html('');
            $('#confirmmsg').hide();
            $('#frmAddcreditcard').trigger('reset');
            var validator = $("#frmAddcreditcard").validate();
            validator.resetForm();
        });
        //End Add Credit Card  
        $(document).on('click', '.editmodel', function() {
            $('#addcard').modal('show');
            $('.showolddetailes').show();
            $('.headtext').html("Edit Credit Card");
            $('#payid').val($(this).data('id'));
            var credit_card_exp = $(this).data('credit_card_exp');
            // var card_exp = credit_card_exp.split("/");
            // $('#expiry_month').val(card_exp[0]);
            // $('#expiry_year').val(card_exp[1]);
            if ($(this).data('default') == 'Yes') {
                $('#default').attr('checked', true);
            } else {
                $('#default').attr('checked', false);
            }
            $('#action').val("credit_card_edit");
            $('#card_number').html($(this).data('credit_card_no'));
            $('#card_exp_month_year').html(credit_card_exp);
        });
        //REMOVE 
        $(document).on('click', '.remove_credential', function(data, status) {
            if (confirm('Do you want to delete payment credential ?')) {
                $('.spinner').removeClass('hide');
                var id = $(this).data('id');
                $.post('<?php echo SITEURL ?>ajax/ajss-payment-credential', {
                    id: id,
                    action: 'delete_credential'
                }, function(data, status) {
                    if (status == 'success') {
                        fillTable();
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            $(".ajaxMsg").hide();
                        }, 8000);
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            $(".ajaxMsg").hide();
                        }, 8000);
                    }
                }, 'json');
            }
        });

        $(document).on('click', '.set_default', function(data, status) {
                 var id = $(this).data('id');
                var family_id = $(this).data('familyid');
                  $.confirm({
                    title: 'Confirm!',
                    content: 'Do you really want to set it default ?',
                    buttons: {
                confirm: function () {
                    $('.spinner').removeClass('hide');
                    $.post('<?php echo SITEURL ?>ajax/ajss-payment-credential', {
                        id: id,
                        family_id:family_id,
                        action: 'set_default'
                    }, function(data, status) {
                        $(".ajaxMsg").show();
                        if (status == 'success') {
                            fillTable();
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $(".ajaxMsg").hide();
                            }, 8000);
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $(".ajaxMsg").hide();
                            }, 8000);
                        }
                    }, 'json');
                },
                cancel: function () {
                 }
                 }
                });
            });




    });


    function PaymentCondition(privewschedule) {
        $('.btnsubmit').attr('disabled', true);
        $('#privewschedule').val(privewschedule);
        $('#confirmmsg').html('Processing...');
        var formDate = $('#frmAddcreditcard').serialize();
        var post_url = "<?php echo PAYSERVICE_URL?>api/payment_token_request";
        $.ajax({
            type: 'POST',
            url: post_url,
            dataType: "json",
            crossDomain: true,
            format: "json",
            data: formDate,
            success: (response) => {
                $('#confirmmsg').html('');
                $('.btnsubmit').attr('disabled', false);
                if(response.data.code == 1){

                    $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url',{
                        payment_token: response.data.transactionID,
                        request_token: "<?php echo $request_token ?>",
                        action: 'payment_verify_token',
                    },function(data,status){                    
                        if(status == 'success'){
                            $('#addcard').modal('hide');
                            if (data.code == 1) {
                                if($('#action').val() == 'credit_card_add'){
                                    $('#frmAddcreditcard').trigger('reset');
                                }
                                fillTable();
                                displayAjaxMsg(data.msg, data.code);
                            } else {
                                displayAjaxMsg(data.msg, data.code);
                            }
                        }
                    },'json');

                }else{
                    $('#addcard').modal('hide');
                    displayAjaxMsg(response.data.message, response.data.code);
                }
            },
            error: (response) => {
                $('#addcard').modal('hide');
                $('.btnsubmit').attr('disabled', false);
                displayAjaxMsg(response.data.message);
            }
        })
    }

    // function PaymentCondition(privewschedule) {
    //     $('#confirmmsg').html('Processing...');
    //     var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-credential';
    //     var formDate = $('#frmAddcreditcard').serialize() + "&privewschedule=" + privewschedule + "";
    //     $.post(targetUrl, formDate, function(data, status) {
    //         if (status == 'success') {
    //             $('#confirmmsg').html(data.msg);
    //             if (data.code == 1) {
    //                 $('#frmAddcreditcard').trigger('reset');
    //                 fillTable();
    //                 displayAjaxMsg(data.msg, data.code);
    //                 $('#addcard').modal('hide');
    //             } else {
    //                 displayAjaxMsg(data.msg, data.code);
    //             }
    //         } else {
    //             displayAjaxMsg(data.msg);
    //         }
    //     }, 'json');
    // }

    function fillTable() {
        var table = $('.datatable-basic').DataTable({
            autoWidth: false,
            destroy: true,
            ordering: true,
            searching: false,
            lengthChange: false,
            processing: true,
            responsive: true,
            ajax: {
                "url": '<?php echo SITEURL ?>ajax/ajss-payment-credential',
                "type": "post",
                "data": function(d) {
                    d.action = "list_payments_credentcials";
                    d.user_id = '<?php echo $user_id ?>';
                }
            },
            sProcessing: '',
            language: {
                loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
            },
            'columns': [{
                    'data': 'credit_card_no',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'credit_card_exp',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'defaultno',
                    searchable: true,
                    orderable: true
                },
            ],
            "order": [
                [1, "desc"]
            ],
            "columnDefs": [{
                "render": function(data, type, row) {
                    //alert(row['user_id']);
                    var links = '';
                    // links = links + "<a href='javascript:;' class='text-primary action_link editmodel'  data-id='" + row['id'] + "'   data-familyid='" + row['family_id'] + "'   data-name='" + row['father_name'] + "' data-credit_card_no='" + row['credit_card_no'] + "' data-cvv='" + row['credit_card_cvv'] + "' data-credit_card_exp='" + row['credit_card_exp'] + "' data-default='" + row['defaultno'] + "' title='Edit'>Edit</a>";
                    if(row['payment_credentials_delete'] == 1){
                            links = links + "<a href='javascript:;' class='text-danger action_link remove_credential' title='Send Message' data-id='" + row['id'] + "' data-familyid='" + row['family_id'] + "'>Delete</a>";
                            links = links + "<a href='javascript:;' class='text-success action_link set_default' title='Send Message' data-id='" + row['id'] + "' data-familyid='" + row['family_id'] + "'>Set Default</a>";
                    }
                    return links;
                },
                "targets": 3
            }, ]
        });
    }
</script>
<?php include "../footer.php" ?>