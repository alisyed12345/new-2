<?php
$mob_title = "Family Schedule Payment ";
include "../header.php";

//AUTHARISATION CHECK 

if($_SESSION['icksumm_uat_login_usertypecode'] != 'UT05'){
    include "../includes/unauthorized_msg.php";
    exit;
    }  
  

if (isset($_SESSION['icksumm_uat_login_familyid'])) {
    $user_id = trim(htmlspecialchars($_SESSION['icksumm_uat_login_familyid']));
} else {
    $user_id = trim(htmlspecialchars($_GET['id']));
}



$family = $db->get_row("select *,f.id as familyid from ss_family f INNER JOIN ss_state s ON f.billing_entered_state = s.id where f.id='" . $user_id . "' And is_deleted=0");

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
            <h4>Payment History</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>parents/dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
            <li class="active">Payment History</li>
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

                    <div class="row">
                        <div class="col-md-12 mt-4 mt-xl-0">
                          


                            <?php 
                            
                            if (!empty($family->father_first_name)) { ?>
                                <div class="row">
                                    <div class="col-md-4">
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

                                    <div class="col-md-4">
                                        <label><strong> 1st Parent Email : </strong>
                                            <?php if (isset($family->primary_email)) {
                                                echo $family->primary_email;
                                            } ?>
                                        </label>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (!empty($family->mother_first_name)) { ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label><strong> 2st Parent Name: </strong>
                                            <?php if (isset($family->mother_first_name)) {
                                                echo $family->mother_first_name . ' ' . $family->mother_last_name;
                                            } ?>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong> 2st Parent Phone : </strong>
                                            <?php if (isset($family->mother_phone)) {
                                                echo $family->mother_phone;
                                            } ?>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong> 2st Parent Email : </strong>
                                            <?php if (isset($family->secondary_email)) {
                                                echo $family->secondary_email;
                                            } ?>
                                        </label>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <label><strong> City : </strong>
                                        <?php if (isset($family->billing_city)) {
                                            echo $family->billing_city;
                                        } ?>
                                </div>

                                <div class="col-md-4">
                                    <label><strong> State : </strong>
                                        <?php if (isset($family->state)) {
                                            echo $family->state;
                                        } ?>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label><strong> Zip Code : </strong>
                                        <?php if (isset($family->billing_post_code)) {
                                            echo $family->billing_post_code;
                                        } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label><strong> Address : </strong>
                                        <?php if (isset($family->billing_address_1)) {
                                            echo $family->billing_address_1;
                                        } ?>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-12 text-right text-primary">
                            <!-- <a data-toggle="collapse" href="#collapseExample" class="pull-right btn btn-danger regpayhistory" role="button" aria-expanded="false" aria-controls="collapseExample">
                                Show Registration Payment Information
                            </a> -->
                        </div>
                    </div>
                    <div class="row" style="margin-top:15px;">
                        <div class="col-md-12">
                            <div id="reg_payment_detail"></div>
                        </div>
                    </div>
                    <br>

                    <div class="ajaxMsg"></div>
                    <table class="table datatable-basic table-bordered">
                        <thead>
                            <tr>
                                <th>Schedule Date</th>
                                <th>Child(ren)</th>
                                <th>Payment Date</th>
                                <th>Last 4 Digits of CC</th>
                                <th>Final Amount</th>
                                <th>Payment Txns Id</th>
                                <th>Status</th>
                                <th>Invoice</th>
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





<div id="modal_send_model" class="modal fade ">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h5 class="modal-title" style="margin-bottom:20px;" id="familyinfo_title"> <b>Send Receipt</b> ( <span class="payment_date_title"></span> )
                </h5>
            </div>

            <div class="container">
                <div class="row" style="margin-left:3px;">
                    <div class="col-md-2">
                        <strong>Credit Card Number</strong>
                        <p id="crad_no"></p>
                    </div>
                    <div class="col-md-2">
                        <strong>Final Fees</strong>
                        <p id="final_amount"></p>
                    </div>
                </div>
            </div>



            <div class="modal-body">
                <form id="sendInvoiceForm" class="form-validate-jquery" method="post">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email">Email:<span class="mandatory">*</span></label>
                                <input placeholder="Email" id="email" name="email" class="form-control required email" type="email">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="text-left">
                                <p id="sinlingMsg" style="color: green;"></p>
                            </div>
                            <input type="hidden" name="family_id" id="family_id">
                            <input type="hidden" name="stu_user_id" id="stu_user_id">
                            <input type="hidden" name="trxn_id" id="trxn_id">
                            <input type="hidden" name="account_entries_id" id="account_entries_id">
                            <input type="hidden" name="action" value="sendInvoice">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Registraion Payment -->
<div id="reg_payment" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title" id="reg_payment_title">Registration Payment History</h5>
            </div>
            <div class="modal-body viewonly" id="reg_payment_detail"></div>
            <div class="modal-footer regpaymentfoot" style="display: none;">
                <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /Registraion Payment -->


<script>
    $(document).ready(function() {
        //FILL TABLE
        fillTable();


        $(document).on('click', '.sendInvoice', function() {

            $('#sendInvoiceForm').trigger('reset');
            var validator = $("#sendInvoiceForm").validate();
            validator.resetForm();

            var familyid = $(this).data('familyid');
            var trxn_id = $(this).data('trxnid');
            var account_entries_id = $(this).data('account_entries_id');
            var crad_no = $(this).data('cradno');
            var final_amount = $(this).data('finalamount');
            var basic_fee = $(this).data('basicfee');
            var discount_fee_amount = $(this).data('discountfeeamount');



            $('.payment_date_title').html($(this).data('paymentdate'));

            $('#family_id').val(familyid);
            $('#trxn_id').val(trxn_id);
            $('#account_entries_id').val(account_entries_id);
            $('#stu_user_id').val($(this).data('id'));
            $('#crad_no').html(crad_no);
            $('#final_amount').html(final_amount);
            $('#modal_send_model').modal('show');
        });



        $('#sendInvoiceForm').submit(function(e) {
            e.preventDefault();
            if ($('#sendInvoiceForm').valid()) {
                $('#sinlingMsg').html('Processing...');
                var formDate = $(this).serialize();
                $.post('<?php echo SITEURL ?>ajax/ajss-payment-history', formDate, function(data, status) {
                    $('#sinlingMsg').html('');
                    if (status == 'success') {
                        if (data.code == 1) {
                            $('#sendInvoiceForm').trigger('reset');
                            $("#sinlingMsg").html(data.msg);
                            setInterval(function(){
                                $('#modal_send_model').modal('hide');
                                $('#sinlingMsg').html('');
                            }, 2000);
                        } else {
                            $("#sinlingMsg").html(data.msg);
                            setInterval(function(){
                                $('#sinlingMsg').html('');
                            }, 3000);
                        }
                    } else {
                        $("#sinlingMsg").html(data.msg);
                        setInterval(function(){
                                $('#sinlingMsg').html('');
                            }, 34000);
                    }
                }, 'json');

            }
        });
        $(document).on('click', '.regpayhistory', function() {

            var familyid = '<?php echo $family->familyid ?>';
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';

            if ($.trim($('#reg_payment_detail').html()) != '') {
                $('#reg_payment_detail').html('');
                $('.regpayhistory').html('Show Registration Payment Information');
            } else {
                $('.regpayhistory').html('Loading... Registration Payment Information');

                $.post(targetUrl, {
                    familyid: familyid,
                    action: 'get_reg_pay_history'
                }, function(data, status) {
                    if (status == 'success') {
                        $('.regpayhistory').html('Hide Registration Payment Information');
                        $('#reg_payment_detail').html(data);
                    }
                });
            }
        })

    });

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
                "url": '<?php echo SITEURL ?>ajax/ajss-payment-history',
                "type": "post",
                "data": function(d) {
                    d.action = "list_history_payments";
                    d.user_id = '<?php echo $user_id ?>';
                }
            },
            sProcessing: '',
            language: {
                loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
            },
            'columns': [{
                    'data': 'schedule_payment_date',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'child_name',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'payment_date',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'credit_card_no',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'final_amount',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'payment_unique_id',
                    searchable: true,
                    orderable: true,
                    visible:false
                },
                {
                    'data': 'payment_trxn_status',
                    searchable: true,
                    orderable: true
                },
            ],
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [{
                    "targets": 0,
                    "type": "date"
            },
            {
                "render": function(data, type, row) {
                    btn = '';
                    <?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
                    if (check_userrole_by_code('UT05')) { ?>
                        if (row['payment_trxn_status'] == 'Success') {
                            btn += "<a href='<?php echo SITEURL ?>ajax/ajss-download_invoice_pdf?account_entries_id=" + row['account_entries_id']+ "&id=" + row['user_id'] + "&payment_txns_id=" + row['payment_txns_id'] + "' class='text-primary action_link downloadInvoice' title='Send Message'>Download Receipt</a>";
                            //btn += "<a href='javascript:void(0)' class='text-success action_link sendInvoice' title='Send Message' data-account_entries_id='" + row['account_entries_id'] + "' data-id='" + row['user_id'] + "'  data-familyid='" + row['family_id'] + "'  data-trxnid='" + row['payment_txns_id'] + "' data-cradno='" + row['credit_card_no'] + "' data-finalamount='" + row['final_amount'] + "'  data-basicfee='" + row['basic_fee'] + "'  data-discountfeeamount='" + row['discount_fee_amount'] + "' data-paymentdate='" + row['payment_date'] + "'    >Send Receipt</a>";
                        }
                        // btn += "<a href='javascript:;' class='text-primary action_link regpayhistory' data-familyid='" + row['family_id'] + "' title='Registration Payment' >Registration Payment</a>";
                    <?php } ?>
                    return btn;

                },
                "targets": 7,
            }]


        });
    }
</script>
<?php include "../footer.php" ?>