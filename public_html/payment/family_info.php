<?php 
$mob_title = "Family Info";
include "../header.php";

if(!in_array("su_family_info", $_SESSION['login_user_permissions'])){
include "../includes/unauthorized_msg.php";
exit;
}    

$_SESSION['token']  = genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD);
$request_token  = 'req_'.RandomString();

?>

<style>
span.mands {
color: #ff0000;
display: inline;
line-height: 1;
font-size: 12px;
margin-left: 5px;
}


.table > tbody > tr > td {
   position: relative;
}
 .top-right {
    height: 3.5vh;
    position: absolute;
    top: 0px;
    right: 0px;
}
</style>
<!-- Page header -->



<div class="page-header page-header-default">
<div class="page-header-content">
    <div class="page-title">
        <h4>Family Info</h4>
    </div>
</div>
<div class="breadcrumb-line">
    <ul class="breadcrumb">
        <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
        <li class="active">Family Info</li>
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
                <div class="col-md-12 mt-4 mt-xl-0">
                    <h5 class="card-title"> Parent Information</h5>
                    <div class="ajaxMsg"></div>
                    <table class="table datatable-basic table-bordered">
                        <thead>
                            <tr>
                                <th>Family ID</th>
                                <th>1st Parent Name</th>
                                <th>2nd Parent Name</th>
                                <!-- <th>City</th> -->
                                <th>Phone No.</th>
                                <th>Primary Email</th>
                                <th>Last Payment</th>
                                <th>Next Payment</th>
                                <th>Collected Payments</th>
                                <th class="text-center action_col"></th>
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
<!-- Manual Payment Model -->
<div id="manual_payment_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title" id="manual_payment_heading"></h5>
            </div>
            <form id="frmmanualpayment" class="form-validate-jquery" method="post">
                <div class="modal-body" style="margin-top:-20px;">
                        <div class="row">
                            <div class="col-md-12">
                                <span class="modal-body viewonly" id="manual_payment_family_detail"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="margin-top:-15px;">
                                <label for="group">Amount :<span class="mandatory ">*</span></label>
                                <input type="text" class="form-control required" dollarsscents="true" minlength="1" maxlength="8" name="amount" id="manual_amount" placeholder=" Amount (<?php echo (!empty(get_country()->currency))?get_country()->currency:'' ?>) " required="" aria-required="true" aria-invalid="false">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="margin-top:15px;">
                                <label for="group">Reason :<span class="mandatory">*</span></label>
                                <textarea type="text" spacenotallow='true' class="form-control required" minlength="1" maxlength="200" name="description" id="description" placeholder="Reason " required aria-required="true" aria-invalid="false"></textarea>
                            </div>
                        </div>
                </div>
                <div class="modal-footer familyfoot hide">
                    <div class="row">
                        <div class="col-md-9">
                            <strong id="statusMsg"></strong>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="family_id" id="family_id">
                            <input type="hidden" name="customertoken" id="customertoken"/>
                            <input type="hidden" name="paymethodtoken" id="paymenttoken"/>
                            <input type="hidden" name="firstName" id="firstname">
                            <input type="hidden" name="lastName" id="lastname">
                            <input type="hidden" name="email" id="email">
                            <input type="hidden" name="phone" id="phone">
                            <input type="hidden" name="city" id="city">
                            <input type="hidden" name="zip" id="zipcode">
                            <input type="hidden" name="address1" id="billingaddress">
                            <input type="hidden" name="system_ip" value="<?=$_SERVER['REMOTE_ADDR']?>" />
                            <input type="hidden" name="session" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION'] ?>"/>
                            <input type="hidden" name="created_by_user_id" value="<?php echo $_SESSION['icksumm_uat_login_userid'] ?>"/>
                            <input type="hidden" name="auth_token" value="<?=$_SESSION['token']?>" />
                            <input type="hidden" name="request_token" value="<?php echo $request_token ?>" />
                            <input type="hidden" name="action" id="action" value="manual_payment">
                            <button type="submit" class="btn btn-success" id="submit">Submit</button>
                            <button type="button" class="btn btn-default closebtnmanual" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Manual Payment Model End-->

<!-- Add Modal - Staff Detail-->
<div id="modal_family_detail" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title" id="familyinfo_title"></h5>
            </div>
            <form id="frmFamilyInfo" class="form-validate-jquery" method="post">
                <div class="modal-body viewonly" id="family_detail"></div>
                <div class="modal-footer familyfoot hide">
                    <div class="row">
                        <div class="col-md-9">
                            <strong id="statusMsg"></strong>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="family_id" id="family_father_id">
                            <input type="hidden" name="action" id="action" value="family_info_submit">
                            <button type="submit" class="btn btn-success"></i> Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add modal -->




<!-- Registraion Payment -->
<div id="communicate_msg_email" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h5 class="modal-title" id="familyinfo_titlename">Communication</h5>
</div>
<form id="frmFamilyInfoCommunication" class="form-validate-jquery" method="post">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Email Or Message:<span class="mands">*</span></label>
                    <select name="communication_check" class="form-control required">
                        <option value="">Select</option>
                        <option value="1">Email</option>
                        <option value="2">Text Message</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Message:<span class="mands">*</span></label>
                    <textarea type="text" name="communication_msg" id="maxContentPost" maxlength="120" placeholder="Communication Message" class="form-control required summernote" colspan="8" aria-required="true"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="row">
            <div class="col-md-9">
                <strong id="statusMsgcomm"></strong>
            </div>
            <div class="col-md-3">
                <input type="hidden" name="father_phone_no" id="father_phone_no">
                <input type="hidden" name="primary_email" id="primary_email">
                <input type="hidden" name="family_id" id="familyid">
                <input type="hidden" name="action" id="action" value="family_info_communication">
                <button type="submit" class="btn btn-success btnsubmit"></i> Submit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</form>
</div>
</div>
</div>
<!-- /Registraion Payment -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
    var table;

    $(document).ready(function() {
        
        //FILL TABLE
        fillTable();
        jQuery.validator.addMethod("dollarsscents", function(value, element) {
      return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
    }, "Please enter a valid amount");

        //VALIDATION - digits 
        jQuery.validator.addMethod("Digits", function(value, element) {
            return this.optional(element) || /^((?!(0))[0-9]{1,2})$/i.test(value);
        }, "Enter valid Quantity");


        //FETCH STAFF DETAILS
        $(document).on('click', '.viewdetail', function() {
            var familyid = $(this).data('familyid');
            document.getElementById('family_father_id').value=familyid;
            // $('#family_id').val(familyid);
            var fathername = $(this).data('fathername');
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
            $('#familyinfo_title').html(fathername + "'s Family");
            $('#family_detail').html('<h5>Data loading... Please wait</h5>');
            $('#modal_family_detail').modal('show');

            $.post(targetUrl, {
                familyid: familyid,
                action: 'view_family_detail_payment'
            }, function(data, status) {
                if (status == 'success') {
                    $('.familyfoot').removeClass('hide');
                    $('#family_detail').html(data);

                    }
                });

        });
        $(document).on('click', '.manual_payment', function() {
            var familyid = $(this).data('familyid');
            var fathername = $(this).data('fathername');
            var custoken = $(this).data('customertoken');
            $('#customertoken').val(custoken);
            $('#paymenttoken').val($(this).data('paymenttoken'));
            $('#firstname').val($(this).data('firstname'));
            $('#lastname').val($(this).data('lastname'));
            $('#phone').val($(this).data('phone'));
            $('#email').val($(this).data('email'));
            $('#city').val($(this).data('city'));
            $('#zipcode').val($(this).data('zipcode'));
            $('#billingaddress').val($(this).data('address'));
             $('#family_id').val(familyid);
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
            $('#manual_payment_heading').html("Manual Payment");
            $('#manual_payment_family_detail').html('<h5>Data loading... Please wait</h5>');
            $('#manual_payment_model').modal('show');
            $("#manual_amount").html("");
            $("#description").html("");
            $.post(targetUrl, {
                familyid: familyid,
                action: 'manual_payment_request'
            }, function(data, status) {
                if (status == 'success') {
                    $('.familyfoot').removeClass('hide');
                    $('#manual_payment_family_detail').html(data);

                    }
                });

        });
        $(document).on('click', '.closebtnmanual', function() {
            $('#frmmanualpayment').trigger('reset');
        });  
        //SCHEDULE PAYMENT
        $(document).on('click', '.schedulePayment', function() {
            var familyid = $(this).data('familyid');
            var fathername = $(this).data('fathername');
            $('#familyid').val(familyid);
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
            $('#familyinfo_title_name').html("Schedule Payment ( " + fathername + " ) ");
            $('#data_load').html('<h5>Data loading... Please wait</h5>');
            $('#modalSchedulePayment').modal('show');

            $.post(targetUrl, {
                familyid: familyid,
                action: 'get_stu_not_schedule'
            }, function(data, status) {
                if (status == 'success') {

                    if (data.code == 1) {
                    $('.familyfooter').removeClass('hide');
                    $('#data_load').html(data.msg);
                    var yesterday = new Date((new Date()).valueOf() - 1000 * 60 * 60 * 24);
                    $('#schedule_start_date').pickadate({
                        labelMonthNext: 'Go to the next month',
                        labelMonthPrev: 'Go to the previous month',
                        labelMonthSelect: 'Pick a month from the dropdown',
                        labelYearSelect: 'Pick a year from the dropdown',
                        selectMonths: true,
                        selectYears: true,
                        disable: [{
                            from: [0, 0, 0],
                            to: yesterday
                        }],
                        min: [<?php echo date('Y') ?>, <?php echo date('m') - 1 ?>, <?php echo date('d') ?>],
                        formatSubmit: 'yyyy-mm-dd'
                    });

                    }else{
                    $('#data_load').html(data.msg);
                    }

                }
                }, 'json');
        });

        $('#modalSchedulePayment').on('hide.bs.modal', function(e) {
            $('#statusMsgs').html('');
            $('#frmSchedulePayment').trigger('reset');
            var validator = $("#frmSchedulePayment").validate();
            validator.resetForm();
        });



        // SUBMIT EVENT
        $('#frmSchedulePayment').submit(function(e) {
            e.preventDefault();
            if ($('#frmSchedulePayment').valid()) {
                $('.btnsubmit').prop("disabled", true);
                $('#statusMsgs').html('Processing...');
                var targetUrl = "<?php echo SITEURL ?>ajax/ajss-family";
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('.btnsubmit').prop("disabled", false);
                        fillTable();
                        
                        if (data.code == 1) {
                            $('#statusMsgs').html(data.msg);
                            displayAjaxMsg(data.msg, data.code);
                        } else {
                            $('#statusMsgs').html(data.msg);
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }

                }, 'json');

                //grecaptcha.reset();
            }
        });


        $('#modal_family_detail').on('hide.bs.modal', function(e) {
            $('#statusMsg').html('');
            $('#statusMsgcomm').empty();
            $('.familyfoot').addClass('hide');
        });

        $('#frmFamilyInfo').submit(function(e) {
            e.preventDefault();
            if ($('#frmFamilyInfo').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
                $('#statusMsg').html('Processing...');
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('#statusMsg').html(data.msg);
                        $('#modal_family_detail').modal('hide');
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




        //Communication Start
        $(document).on('click', '.communication', function() {
            $('#frmFamilyInfoCommunication').trigger('reset');
            var validator = $("#frmFamilyInfoCommunication").validate();
            validator.resetForm();
             $('#statusMsgcomm').empty();
            var familyid = $(this).data('familyid');
            var fathername = $(this).data('fathername');
            var father_phone_no = $(this).data('fatherphone');
            var email = $(this).data('primaryemail');
            $('#father_phone_no').val(father_phone_no);
            $('#primary_email').val(email);
            $('#familyid').val(familyid);
            $('#familyinfo_titlename').html(fathername + "'s Family");
            $('#communicate_msg_email').modal('show');
        });


        $('#frmFamilyInfoCommunication').submit(function(e) {
            e.preventDefault();
             $('#statusMsgcomm').html('');
            if ($('#frmFamilyInfoCommunication').valid()) {

                var content = $('#maxContentPost').val();
                var getcode = $('.summernote').summernote('code');
                getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
                var div = document.createElement("div");
                div.innerHTML = getcode;
                var text = div.textContent || div.innerText || "";
                if($.trim(text).length==0){
                     $('#statusMsgcomm').css("color","red");
                     $('#statusMsgcomm').html("Message cannot be empty ");
                     return false;
                }
                $('.btnsubmit').prop("disabled", true);
                $('#statusMsgcomm').html('Processing...');
                var targetUrl = "<?php echo SITEURL ?>ajax/ajss-family";
                var formDate = $(this).serialize();

                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('.btnsubmit').prop("disabled", false);
                        if (data.code == 1) {
                            $('.note-editable').html(' ');
                            $('#frmFamilyInfoCommunication').trigger('reset');
                            $('#statusMsgcomm').html(data.msg);
                            setInterval(function(){
                            $('#statusMsgcomm').html('');
                            }, 3000);
                        } else {
                            $('#statusMsgcomm').html(data.msg);
                            setInterval(function(){
                                $('#statusMsgcomm').html('');
                            }, 3000);
                        }
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }

                }, 'json');

                //grecaptcha.reset();
            }
        });
        //Communication End
   

        //Manual Payment
        $('#frmmanualpayment').submit(function(e) {
            e.preventDefault();
            if ($('#frmmanualpayment').valid()) {
                $('#statusMsg').html('Processing...');
                $('#submit').prop("type", "button");
                $('#submit').attr('disabled', true);
                $('.footermdsecond').addClass('hide');

                var formDate = $('#frmmanualpayment').serialize();
                        var post_url = "<?php echo PAYSERVICE_URL?>api/payment_capture";
                        $.ajax({
                            type: 'POST',
                            url: post_url, 
                            dataType: "json",
                            crossDomain: true,
                            format: "json",
                            data: formDate,
                            success: (response) => {
                                if(response.data.code == 1 || response.data.code == 2){
                                    
                                   // console.log(response);
                                    $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url',{
                                        payment_unique_id: response.data.transactionID,
                                        request_token: "<?php echo $request_token ?>",
                                        response_code: response.data.code,
                                        action: 'payment_verify',
                                    },function(data,status){                
                                        if(status == 'success'){
                                            $('#manual_payment_model').modal('hide');
                                            $('#submit').prop("type", "submit");
                                            $('#submit').attr('disabled', false);
                                            if (data.code == 1) {
                                                if($('#action').val() == 'manual_payment'){
                                                    $('#frmmanualpayment').trigger('reset');
                                                }
                                                if(data.code == 1){
                                                    displayAjaxMsg('Manual payment has been successfully', data.code);
                                                    $('#statusMsg').html('');
                                                }
                                                
                                                // setTimeout(function() {
                                                //     location.reload();
                                                // }, 3000);
                                            } else {
                                                if (data.code == 0) {
                                                    displayAjaxMsg('Process failed. Please try again later.', data.code);
                                                    $('#statusMsg').html('');
                                                }
                                            }
                                        }
                                    },'json');


                                }else{
                                    $('#manual_payment_model').modal('hide');
                                    $('#submit').prop("type", "submit");
                                    $('#submit').attr('disabled', false);
                                    displayAjaxMsg(response.data.message, response.data.code);
                                    $('#statusMsg').html('');
                                    $('#frmmanualpayment').trigger('reset');
                                }
                            },
                            error: (response) => {
                                $('#submit').prop("type", "submit");
                                $('#submit').attr('disabled', false);
                                //displayAjaxMsg(response.data.message);
                            }
                        })
                }
            });
           
        
    });
   


    function fillTable() {
        table = $('.datatable-basic').DataTable({
            autoWidth: false,
            destroy: true,
            pageLength: <?php echo TABLE_LIST_SHOW ?>,
            responsive: true,
            ajax: '<?php echo SITEURL ?>ajax/ajss-family?action=list_family',
            sProcessing: '',
            language: {
                loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
            },
            'columns': [{
                    'data': 'id'
                },
                {
                    'data': 'father_name',
                    searchable: true,
                    orderable: true,
                },
                {
                    'data': 'mother_name',
                    searchable: true,
                    orderable: true
                },
                // {
                //     'data': 'city',
                //     searchable: true,
                //     orderable: true
                // },
                {
                    'data': 'father_phone',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'primary_email',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'last_payment_status',
                },
                {
                    'data': 'payment',
                },
                {
                    'data': 'collected_payments',
                },
            ],
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                    "render": function(data, type, row) {

                        var btn = '';
                        <?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
                        //if(check_userrole_by_code('UT01')){ ?>


                        <?php  if(in_array("su_family_info", $_SESSION['login_user_permissions'])){ ?>

                        btn += "<a href='javascript:;' class='text-warning action_link viewdetail' data-fathername='" + row['father_name'] + "' data-familyid='" + row['id'] + "' title='View Details' >View</a>";

                        <?php  if(in_array("su_family_communicate", $_SESSION['login_user_permissions'])){ ?>
                        btn += "<a href='javascript:;' class='text-primary action_link communication' data-fathername='" + row['father_name'] + "' data-familyid='" + row['id'] + "' data-fatherphone='" + row['father_phone'] + "' data-primaryemail='" + row['primary_email'] + "' title='Communicate' >Communicate</a>";
                        <?php } ?>

                        <?php if($client_setting->fees_monthly == 1){ ?>

                        <?php  if(in_array("su_payment_credential_list", $_SESSION['login_user_permissions'])){ ?>
                        btn += "<a href='payment_credential_list.php?id=" + row['id'] + "' class='text-success action_link' title='Credit Card'>Credit Card</a>";
                        <?php } ?>

                        <?php  if(in_array("su_payment_fees_history_list", $_SESSION['login_user_permissions'])){ ?>
                        btn += "<a href='payment_fees_history_list.php?id=" + row['id'] + "' class='text-primary action_link' title='Schedule Payment'>Schedule Payment</a>";
                        <?php } ?>

                        <?php }  } ?>

                        <?php  if(in_array("su_family_accounting", $_SESSION['login_user_permissions'])){ ?>
                        btn += "<a href='<?php echo SITEURL ?>payment/invoice_list?id="+ row['id'] +"' class='text-secondary action_link' data-familyid='" + row['id'] + "' title='Accounting' >Accounting</a>";
                        <?php } ?>

                        <?php if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ ?>
                            if(row['customer_token'] && row['payment_token']){
                            btn += '<a href="javascript:;" class="text-info action_link manual_payment" data-fathername=' + row['father_name'] + ' data-customertoken=' + row['customer_token'] + ' data-paymenttoken=' + row['payment_token'] + ' data-familyid=' + row['id'] + ' data-firstname=' + row['father_first_name'] + ' data-lastname=' + row['father_last_name'] + ' data-phone=' + row['father_phone'] + ' data-email=' + row['primary_email'] + ' data-city=' + row['city'] + ' data-zipcode=' + row['zipcode'] + ' data-address=' + row['billing_address_1'] + ' title="View Details" >Manual Payment</a>';
                            }
                        <?php }; ?>

                        return btn;
                    },
                    "targets": 8 
                },
                {
                    "visible": false,
                    "targets": [0]
                }
            ]
        });
    }

        function registerSummernote(element, placeholder, max, callbackMax) {
        $(element).summernote({
            toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']]
            ],
            placeholder,
            callbacks: {
            onKeydown: function(e) {
                var t = e.currentTarget.innerText;
                if (t.length >= max) {
                //delete key
                if (e.keyCode != 8)
                    e.preventDefault();
                // add other keys ...
                }
            },
            onKeyup: function(e) {
                var t = e.currentTarget.innerText;
                if (typeof callbackMax == 'function') {
                callbackMax(max - t.length);
                }
            },
            onPaste: function(e) {
                var t = e.currentTarget.innerText;
                var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                e.preventDefault();
                var all = t + bufferText;
                document.execCommand('insertText', false, all.trim().substring(0, 1000));
                if (typeof callbackMax == 'function') {
                callbackMax(max - t.length);
                }
            }
            }
        });
        }


    $(function(){
        registerSummernote('.summernote', 'Message', 1000, function(max) {
        $('#maxContentPost').text(max)
        });
    });

    $(".summernote").on("summernote.change", function (e) {   // callback as jquery custom event 
     var getcode = $('.summernote').summernote('code');
                getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
                var div = document.createElement("div");
                div.innerHTML = getcode;
                var text = div.textContent || div.innerText || "";
                if($.trim(text).length==0){
                     $('#statusMsgcomm').css("color","red");
                     $('#statusMsgcomm').html("Message cannot be empty ");
                     return false;
                }
                else{
                     $('#statusMsgcomm').html("");
                }
});
</script>
<?php include "../footer.php"?>