<?php
$mob_title = "List Invoice";
include "../header.php";

if ($_SESSION['icksumm_uat_login_usertypecode'] != 'UT05' && !in_array("su_family_accounting", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  exit;
}

if (check_userrole_by_code('UT05')) {
  $user_id = $_SESSION['icksumm_uat_login_familyid'];
  $check = true;
} else {
  $user_id = $_GET['id'];
  $check = false;
}
if (!empty(get_country()->currency)) {
  $currency = get_country()->currency;
} else {
  $currency = '';
}

$family = $db->get_row("SELECT f.id, f.user_id, f.father_first_name, f.father_last_name, f.primary_email, f.father_phone, f.billing_address_1, f.billing_address_2, f.billing_city, f.billing_post_code, s.state FROM ss_family f INNER JOIN ss_state s ON f.billing_entered_state = s.id WHERE f.id='" . $user_id . "' And f.is_deleted=0");
?>
<style>
  .tooltip {
    width: 300px !important;
    padding: 4px;
    z-index: 9999999;

  }
</style>

<!--Refund View Schedule History-->
<div id="refund_sechedule_history" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Refund Payment History</h5>
      </div>
      <div class="modal-body viewonly" id="refund_list_history"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Page header -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Accounting</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <?php if ($check == false) { ?>
        <li><a href="<?php echo SITEURL ?>payment/family_info"> Family Info</a></li>
      <?php } ?>
      <li class="active">Accounting</li>
    </ul>
  </div>
  <!-- <div class="above-content">
    <a href="javascript:void(0)" class="pull-right"><i class="fa fa-download"></i> Download</a>
</div> -->
</div>
<style>
  .panel-title {
    font-size: 20px;
  }

  .sorting_1 {
    text-align: left;
  }

  .acc_identity {
    margin-bottom: 47px;
  }
</style>
<!-- /page header -->

<div class="modal fade" id="refundmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title" id="exampleModalLabel">Refund Payment</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="refundfrm" class="form-validate-jquery" method="post">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-3 ">
              <div class="form-group">
                <label><b>Name : </b></label><span> <?php echo  $family->father_first_name . ' ' . $family->father_last_name ?> </span>
                <input type="hidden" id="firstName" name="firstName" value="<?php echo  $family->father_first_name; ?>">
                <input type="hidden" id="lastName" name="lastName" value="<?php echo  $family->father_last_name; ?>">
              </div>
            </div>
            <div class="col-md-3 ">
              <div class="form-group">
                <label><b>Phone : </b> <span> <?php echo $family->father_phone ?> </span></label>
                <input type="hidden" id="phone" name="phone" value="<?php echo  $family->father_phone; ?>">
              </div>
            </div>
            <div class="col-md-6 ">
              <div class="form-group">
                <label><b>Email :</b> </label> <span> <?php echo $family->primary_email ?> </span>
                <input type="hidden" id="email" name="email" value="<?php echo  $family->primary_email; ?>">
              </div>
            </div>

          </div>

          <div class="row">
            <div class="col-md-6 ">
              <div class="form-group">
                <label for="amount1"><b>Amount</b></label>
                <input type="text" class="form-control" name="paid_amount" id="amount1" readonly>
                <input type="hidden" name="paid_txn_amount" id="paid_txn_amount">
              </div>
            </div>

            <div class="col-md-6 ">
              <div class="form-group">
                <label for="amount2"><b>Refund Amount</b></label>
                <input type="text" class="form-control required" dollarsscents="true" validate_refund="true" minlength="1" maxlength="8" name="refund_amount_cal" id="refund_amount_cal" placeholder="Amount (<?php echo (!empty(get_country()->currency)) ? get_country()->currency : '' ?>) " required="" aria-required="true" aria-invalid="false">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="amount2"><b>Refund Reason</b></label>
                <textarea class="form-control required" name="refund_reason" id="refund_reason" placeholder="Reason" required="" aria-required="true" aria-invalid="false"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-9">
            <strong class="ajaxMsgBotRefund text-right"></strong>
          </div>
          <div class="col-md-3">
            <div class="modal-footer border-top-0 d-flex justify-content-center">
              <button type="submit" class="btn btn-success btnsubmit">Submit</button>
              <input type="hidden" name="action" value="refund_payment">
              <input type="hidden" name="refund_wallet_amount_calculation" id="refund_wallet_amount_calculation">
              <input type="hidden" name="refund_wallet_amount" id="refund_wallet_amount">
              <input type="hidden" name="refund_amount" id="refund_amount">
              <input type="hidden" name="payid_user" id="payid_user">
              <input type="hidden" name="type_of_refund_payment" id="type_of_refund_payment">
              <input type="hidden" name="family_id" value="<?php echo $family->id; ?>">
              <input type="hidden" id="payment_txn_id" name="payment_txn_id" value="">
              <input type="hidden" id="payment_credentials_id" name="payment_credentials_id" value="">
              <input type="hidden" id="txnid" name="txnid" value="">
              <input type="hidden" name="session" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION'] ?>" />
              <input type="hidden" name="created_by_user_id" value="<?php echo $_SESSION['icksumm_uat_login_userid'] ?>" />
              <input type="hidden" name="session_text" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] ?>" />
              <input type="hidden" name="auth_token" id="auth_token" value="" />
              <input type="hidden" name="request_token" id="request_token" value="" />
              <input type="hidden" name="system_ip" value="<?= $_SERVER['REMOTE_ADDR'] ?>" />
              <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closebtn_reset">Close</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Content area -->
<div class="content">
  <div class="row">
    <?php if (check_userrole_by_code('UT01')) { ?>
      <div class="col-md-12">
        <div class="panel panel-flat">
          <div class="panel-body">
            <div class="row">
              <div class="col-md-12 mt-4 mt-xl-0">
                <?php if (!empty($family->father_first_name)) { ?>
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
                        <?php
                        if (isset($family->father_phone)) {
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
                  <div class="col-md-4"></div>
                  <div class="col-md-4">
                    <label><strong> Account Status : </strong>
                      <span class='totalAmount'></span>
                      <input type="hidden" id="account_status_amount" value="">
                  </div>
                </div>

              </div>

            </div>



          </div>
        </div>
      </div>
    <?php } ?>

    <!---------------------Accounting ------------------------>
    <div class="col-md-12">
      <div class="panel panel-flat">
        <div class="panel-body">
          <div class="ajaxMsg"></div>
          <!---------------------SELECT ------------------------>
          <form id="frmaccounting" method="post">
            <div class="col-md-12">

              <div class="row">
                <div class="col-md-3">
                  <select class="form-control required acc_identity" id="acc_identity" name="acc_identity">
                    <option value="" id="wal_select">Wallet Payment </option>
                    <option value="manu_is_type">Manual Payment </option>
                    <option value="reg_is_type">Registration Payment </option>
                    <option value="sch_is_type">Schedule Payment </option>
                    <option value="refund_is_type">Refund Payment </option>
                  </select>
                </div>
                <input type="hidden" value="accounting_record">
                <input type="hidden" id="fam_id" value="<?php echo $user_id ?>">
                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Filter</button>
                <a href="javascript:void(0)" id="btnReset" style="background: #f2af58;" class="btn btn-defult"><span style='color:white;'>Reset </span></a>
                <!-- <a href='<?php echo SITEURL ?>payment/payment_fees_history_list?id=<?php echo $user_id ?>'  style="background: green;float:right;" class="btn btn-defult "><span style='color:white;'>Schedule </span></a> -->
              </div>
            </div>
          </form>

          <div class="row">
            <div class="col-md-6">
              <p class="panel-title heading_for_all">Wallet Transaction</p>

            </div>
            <?php if (check_userrole_by_code('UT05')) { ?>
              <div class="col-md-6 text-right">
                <p style="font-size: 15px;font-weight: 600;">Account Status : <span class='totalAmount'></span> </p>
              </div>
            <?php } ?>

            <!--    <a href='#' class='testtooltip' data-toggle='tooltip' title='11' >Hover Me</a> -->
            <div class="col-md-6 text-right fade_transaction_button">
              <?php if (in_array("su_family_payment_transation", $_SESSION['login_user_permissions'])) {  ?>
                <p> <a href="javascript:;" id="add_account_payment" class="text-primary ">+ Transaction </a></p>
                <input type="hidden" id="debit_amount_status" value="">
              <?php  } ?>
            </div>


            <div class="col-md-12 mt-10">
              <table class="table datatable-basic-account table-bordered dataTable " id="DataTables_Table">
                <thead>
                  <tr role="row">

                    <th>Date</th>
                    <!-- <th >father_name</th> -->
                    <th>Student Name</th>
                    <th>Primaryemail</th>
                    <th id="change_des">Description</th>
                    <th>(<?php echo $currency ?>) Amount</th>
                    <th>Type</th>
                    <th>Action</th>

                  </tr>
                </thead>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
    <!---------------------Invoice & Receipt ------------------------>
    <div class="col-md-12">
      <div class="panel panel-flat">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-12">
              <p class="panel-title heading_for_invoice">Wallet Invoice & Receipt</p>
            </div>
            <div class="col-md-12 mt-10">
              <table class="table datatable-basic-invoice table-bordered">
                <thead>
                  <tr>
                    <th>Invoice ID </th>
                    <th>Invoice Date</th>
                    <th>Receipt ID & Date</th>
                    <th>(<?php echo $currency ?>) Invoice Amount</th>
                    <th>Status</th>
                    <th class="text-center action_col">Action</th>
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
</div>
</div>

<!-- The Modal -->
<div class="modal" id="viewModel">
  <div class="modal-dialog" style="width:30%;">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title invoicetitle"></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="post" id="frmsendinvoice">
        <!-- Modal body -->
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <label class="leveltitle"></label><span class="mandatory">*</span>
              <div class="form-group">
                <label class="checkbox-inline downloadInvoiceCheck hide">
                  <input type="checkbox" name="invoice[]" value="invoice" id="sendinvoice"> Invoice
                </label>
                <label class="checkbox-inline downloadReceiptCheck hide">
                  <input type="checkbox" name="invoice[]" value="receipt" id="sendreceipt"> Receipt
                </label>
                <label id="invoice-error" class="error" for="invoice" style="display: none; margin-left:10px;">Required field</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <label>Email:<span class="mandatory">*</span></label>
              <input type="text" name="email" class="form-control email required" placeholder="Email" value="<?php echo $family->primary_email ?>">
            </div>
          </div>
        </div>
        <!-- Modal footer -->
        <div class="modal-footer">
          <div class="row">
            <div class="col-md-8 text-right">
              <strong id="statusMsg"></strong>
            </div>
            <div class="col-md-4 text-right">
              <input type="hidden" name="family_id" id="family_id" value="<?php echo $user_id ?>">
              <input type="hidden" name="invoice_id" id="invoivce_id" value="">
              <input type="hidden" name="receipt_id" id="receipt_id" value="">
              <input type="hidden" name="invoice_mainid" id="invoice_mainid" value="">
              <input type="hidden" name="action" id="action" value="send_invoice_or_receipt">
              <button type="submit" class="btn btn-success sendbtn">Send</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- The Modal -->
<div class="modal" id="downloadModel">
  <div class="modal-dialog" style="width:30%;">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title downloadtitle">Download Invoice/Receipt</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="post" id="frmdownloadinvoice">
        <!-- Modal body -->
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <label class="downloadleveltitle">Download Invoice/Receipt :<span class="mandatory">*</span></label>
              <div class="form-group">
                <label class="checkbox-inline downloadInvoiceCheck hide">
                  <input type="checkbox" class="checkboxsingle" data-type="invoice" name="invoice" value="" id='dw_invoivceid'> Invoice
                </label>
                <label class="checkbox-inline downloadReceiptCheck hide">
                  <input type="checkbox" class="checkboxsingle" data-type="receipt" name="receipt" value="" id="dw_receiptid"> Receipt
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <div class="row">
            <div class="col-md-6 text-right">
              <strong class="statusMsg"></strong>
            </div>
            <div class="col-md-6 text-right">
              <input type="hidden" name="invid" id="invid" value="">
              <input type="hidden" name="action" value="download_invoice_or_receipt">
              <button type="submit" class="btn btn-success downloadinvoicebtn">Download</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Transaction Modal   -->
<div id="Addaccount" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content maincontentfirst">
      <form id="frmadd_account_payment" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
          <h5 class="modal-title headtext" id="familyinfo_title"> <b> Payment Transaction</b></h5>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="group">Amount :<span class="mandatory">*</span></label>
                <input type="text" class="form-control required change_detector" dollarsscents="true" minlength="1" maxlength="8" name="payment_amount" id="payment_amount" placeholder=" Amount (<?php echo (!empty(get_country()->currency)) ? get_country()->currency : '' ?>) " required="" aria-required="true" aria-invalid="false" validate_debit_amount="false">

              </div>
            </div>
            <div class="col-md-4">
              <label for="group">Payment Type :<span class="mandatory">*</span></label>
              <div class="form-group">
                <label class="radio-inline">
                  <input type="radio" name="payment_type" checked value="1" class="change_detector"> Credit
                </label>
                <label class="radio-inline hide" id="debit_radio">
                  <input type="radio" name="payment_type" value="0" class="change_detector" id="debit_button">Debit
                </label>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="group">Description :<span class="mandatory">*</span></label>
                <textarea type="text" spacenotallow='true' class="form-control required" minlength="1" maxlength="200" name="description" id="description" placeholder="Description " required aria-required="true" aria-invalid="false"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="modal-footer">
              <div class="col-md-9 text-right">
                <strong id="statusMsg_account"></strong>
              </div>
              <div class="col-md-3">
                <input type="hidden" name="action" id="action" value="account_payment_transactions">
                <input type="hidden" name="family_user_id" value="<?php echo $family->user_id ?>">
                <input type="hidden" name="family_id" value="<?php echo $_GET['id'] ?>">
                <button type="submit" class="btn btn-primary addaccountbtn" id="Addaccount_button">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">close</button>
              </div>
            </div>
          </div>
      </form>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {

$('.change_detector').change(function(){
  var account_status_amount = document.getElementById("account_status_amount").value;
  if($('#debit_button').is(':checked')){
    var eleman = document.getElementById('payment_amount');
    eleman.setAttribute("validate_debit_amount", true);

    var message;
        jQuery.validator.addMethod("validate_debit_amount", function(value, element) {
          var old_value = Number(account_status_amount);
          var result;
          if (old_value >= value) {
        
            result = true;
            message = "";
          } else {
            result = false;
            message = "please enter small and valid amount";
          }
          return this.optional(element) || result;
        }, function() {
          return message;
        });
  }else{
    var eleman = document.getElementById('payment_amount');
    // eleman.setAttribute("aria-invalid",false);  
    // eleman.removeAttribute("aria-required"); 
    eleman.removeAttribute("validate_debit_amount");
  
  }
  
})


    $("#refund_amount_cal").change(function() {

      let wallet_amount1 = Number($('#refund_wallet_amount_calculation').val());
      let refund_amount1 = Number($('#refund_amount_cal').val());
      let paid_txn_refund_amount = Number($('#paid_txn_amount').val());
      let total_amount_rw = Number($('#amount1').val());

      if (isNaN(wallet_amount1) == false) {

        if (wallet_amount1 > refund_amount1) {

          if (refund_amount1 < paid_txn_refund_amount) {
            wallet_amount_left = 0;
            remaining_txn_amount = refund_amount1;

            $('#refund_amount').val(remaining_txn_amount);
            $('#refund_wallet_amount').val(wallet_amount_left);

          } else {

            wallet_amount_left = refund_amount1 - paid_txn_refund_amount;
            $('#refund_amount').val(paid_txn_refund_amount);
            $('#refund_wallet_amount').val(wallet_amount_left.toFixed(2));
          }


        } else if (refund_amount1 >= wallet_amount1) {

          wallet_amount_left = refund_amount1 - paid_txn_refund_amount;

          $('#refund_amount').val(paid_txn_refund_amount);
          $('#refund_wallet_amount').val(wallet_amount_left);

          if (Number($('#refund_wallet_amount').val()) == 0 && Number($('#refund_amount').val()) == 0) {

            $('#refund_wallet_amount').val(wallet_amount1);
            $('#refund_amount').val(paid_txn_refund_amount);

          }
        }
      } else {
        $('#refund_amount').val(refund_amount1);
      }


    });


    $('#closebtn_reset').on('click', function() {
      $('#refund_reason').val('');
      $('#refund_amount_cal').val('');
    })

    $('#wal_select').on('click', function() {
      $('#frmaccounting').submit();
      $('#change_des').html('<th>Description</th>');
    })

    $('#btnReset').on('click', function() {
      $('#acc_identity').val('');
      // $('.datatable-basic-account').DataTable().destroy();
      $('#frmaccounting').submit();


    });

    $('#frmaccounting').submit();


    $(".checkboxsingle").on('click', function() {
      if ($(this).data("type") == "invoice" && $(this).is(":checked")) {
        $('#dw_invoivceid').prop("checked", true);
        $('#dw_receiptid').prop("checked", false);
      } else if ($(this).data("type") == "receipt" && $(this).is(":checked")) {
        $('#dw_invoivceid').prop("checked", false);
        $('#dw_receiptid').prop("checked", true);
      } else {
        $('#dw_invoivceid').prop("checked", false);
        $('#dw_receiptid').prop("checked", false);
      }


    });

    // fillTableAccount();
    accountStatus();
    jQuery.validator.addMethod("dollarsscents", function(value, element) {
      return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
    }, "Please enter a valid amount");

    var message;
    jQuery.validator.addMethod("validate_refund", function(value, element) {
      var old_value = Number($('#amount1').val());
      var result;
      if (old_value >= value) {
        result = true;
        message = "";
      } else {
        result = false;
        message = "please enter small and valid amount";
      }
      return this.optional(element) || result;
    }, function() {
      return message;
    });

    $(document).on('click', '.viewinvoice', function(data, status) {
      $('#frmsendinvoice').trigger('reset');
      $('#statusMsg').html('');
      var validator = $("#frmsendinvoice").validate();
      var invoice = $(this).data('invoiceid');
      var receipt = $(this).data('receiptid');
      var invoice_mainid = $(this).data('invoice_mainid');
      $('#invoice_mainid').val(invoice_mainid);

      var invoicepath = $(this).data('invoicepath');
      var receiptpath = $(this).data('receiptpath');

      if (invoicepath == '' && receiptpath == '') {
        $('#sendinvoice').prop("disabled", true);
        $('#sendreceipt').prop("disabled", true);
        $('.sendbtn').prop("disabled", true);
      } else if (invoicepath == '') {
        $('#sendinvoice').prop("disabled", true);
        $('.sendbtn').prop("disabled", true);
      } else if (receiptpath == '') {
        $('#sendreceipt').prop("disabled", true);
        $('.sendbtn').prop("disabled", true);
      } else {
        $('#sendinvoice').prop("disabled", false);
        $('#sendreceipt').prop("disabled", false);
        $('.sendbtn').prop("disabled", false);
      }

      validator.resetForm();
      if (invoice && receipt == '') {
        $('#invoivce_id').val(invoice);
        $('.invoicetitle').text('Send Invoice');
        $('.leveltitle').text('Send Invoice :');
        $('.downloadReceiptCheck').addClass('hide');
        $('.downloadInvoiceCheck').removeClass('hide');
        $('#receipt_id').val('');
      } else if (receipt && invoice == '') {
        $('#receipt_id').val(receipt);
        $('.invoicetitle').text('Send Receipt');
        $('.leveltitle').text('Send Receipt :');
        $('.downloadReceiptCheck').removeClass('hide');
        $('.downloadInvoiceCheck').addClass('hide');
        $('#invoivce_id').val('');
      } else if (receipt != '' && invoice != '') {
        $('#invoivce_id').val(invoice);
        $('#receipt_id').val(receipt);
        $('.invoicetitle').text('Send Invoice/Receipt');
        $('.leveltitle').text('Send Invoice/Receipt :');
        $('.downloadReceiptCheck').removeClass('hide');
        $('.downloadInvoiceCheck').removeClass('hide');
      }
      $('#viewModel').modal('show');
    });

    $(document).on('click', '.downloadinvoice', function(data, status) {
      $('.statusMsg').html(' ');
      var validator = $("#frmdownloadinvoice").validate();
      validator.resetForm();

      var invoice = $(this).data('invoiceid');
      var receipt = $(this).data('receiptid');
      var invoicepath = $(this).data('invoicepath');
      var receiptpath = $(this).data('receiptpath');

      if (invoicepath == '' && receiptpath == '') {
        $('#dw_invoivceid').prop("disabled", true);
        $('#dw_receiptid').prop("disabled", true);
        $('.downloadinvoicebtn').prop("disabled", true);
      } else if (invoicepath == '') {
        $('#dw_invoivceid').prop("disabled", true);
        $('.downloadinvoicebtn').prop("disabled", true);
      } else if (receiptpath == '') {
        $('#dw_receiptid').prop("disabled", true);
        $('.downloadinvoicebtn').prop("disabled", true);
      } else {
        $('#dw_invoivceid').prop("disabled", false);
        $('#dw_receiptid').prop("disabled", false);
        $('.downloadinvoicebtn').prop("disabled", false);
      }

      var id = $(this).data('id');
      $('#invid').val(id);
      if (invoice && receipt == '') {
        $('#dw_invoivceid').val(invoice);
        $('#dw_receiptid').val('');
        $('.downloadtitle').text('Download Invoice');
        $('.downloadleveltitle').text('Download Invoice :');
        $('.downloadReceiptCheck').addClass('hide');
        $('.downloadInvoiceCheck').removeClass('hide');
      } else if (receipt && invoice == '') {
        $('#dw_receiptid').val(receipt);
        $('#dw_invoivceid').val('');
        $('.downloadtitle').text('Download Receipt');
        $('.downloadleveltitle').text('Download Receipt :');
        $('.downloadReceiptCheck').removeClass('hide');
        $('.downloadInvoiceCheck').addClass('hide');

      } else if (receipt != '' && invoice != '') {
        $('#dw_invoivceid').val(invoice);
        $('#dw_receiptid').val(receipt);
        $('.downloadtitle').text('Download Invoice/Receipt');
        $('.downloadleveltitle').text('Download Invoice/Receipt :');
        $('.downloadReceiptCheck').removeClass('hide');
        $('.downloadInvoiceCheck').removeClass('hide');
      }
      $('#downloadModel').modal('show');
    });

    $('#frmsendinvoice').submit(function(e) {
      e.preventDefault();
      if ($('#frmsendinvoice').valid()) {
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-invoice';
        $('#statusMsg').html('Processing...');

        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            $('#statusMsg').html(data.msg);
            if (data.code == 1) {
              fillTable(is_type_no);
              $('#frmsendinvoice').trigger('reset');
              displayAjaxMsg(data.msg, data.code);
              setTimeout(function() {
                $('#statusMsg').html(' ');
                $('#viewModel').modal('hide');
              }, 2000);
            } else {
              displayAjaxMsg(data.msg, data.code);
              setTimeout(function() {
                $('#statusMsg').html(' ');
              }, 4000);
            }
          } else {
            displayAjaxMsg(data.msg);
            setTimeout(function() {
              $('#statusMsg').html(' ');
            }, 4000);
          }
        }, 'json');
      }
    });
    $('#dw_invoivceid').click(function() {
      if ($(this).is(":checked")) {
        $('.downloadinvoicebtn').prop("disabled", false);
      }
    });
    $('#dw_receiptid').click(function() {
      if ($(this).is(":checked")) {
        $('.downloadinvoicebtn').prop("disabled", false);
      }
    });
    $('#sendinvoice').click(function() {
      if ($(this).is(":checked")) {
        $('.sendbtn').prop("disabled", false);
      }
    });
    $('#sendreceipt').click(function() {
      if ($(this).is(":checked")) {
        $('.sendbtn').prop("disabled", false);
      }
    });

    $('#frmdownloadinvoice').submit(function(e) {
      e.preventDefault();
      var inv = $('#dw_invoivceid:checked').length;
      var recp = $('#dw_receiptid:checked').length;
      if (recp == 0 && inv == 0) {
        $('.statusMsg').css('color', 'red');
        $('.statusMsg').html('Please select invoice/receipt');
        return false;
      }
      if ($('#frmdownloadinvoice').valid()) {
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-invoice';
        $('.statusMsg').html('Processing...');

        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            $('.statusMsg').html(data.msg);
            if (data.code == 1) {
              var link = document.createElement('a');
              var download = "";


              if (data.invoicedownload && data.receiptdownload) {
                download = data.receiptdownload;
                link.href = download;
                link.download = "receipt_" + new Date() + ".pdf";
                download = data.invoicedownload;
                link.click();
                link.remove();
                var link2 = document.createElement('a');
                link2.href = download;
                link2.download = "invoice_" + new Date() + ".pdf";
                link2.click();
                link2.remove();

                // if (data.receiptdownload) {
                //   download = data.receiptdownload;
                //   link.href = download;
                //   link.download = "receipt_" + new Date() + ".pdf";
                //   link.click();
                //   link.remove();
                // }
                // if (data.invoicedownload) {  
                //   download = data.invoicedownload;
                //   link.href = download;
                //   link.download = "invoice_" + new Date() + ".pdf";
                //   link.click();
                //   link.remove();
                // }

              } else if (data.receiptdownload) {
                download = data.receiptdownload;
                link.href = download;
                link.download = "receipt_" + new Date() + ".pdf";
                link.click();
                link.remove();
              } else if (data.invoicedownload) {
                download = data.invoicedownload;
                link.href = download;
                link.download = "invoice_" + new Date() + ".pdf";
                link.click();
                link.remove();
              }



              //window.open(data.receiptdownload);
              $('#frmdownloadinvoice').trigger('reset');
              setTimeout(function() {
                $('.statusMsg').html(' ');
                $('#downloadModel').modal('hide');
              }, 2000);
            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          } else {
            displayAjaxMsg(data.msg);
          }
        }, 'json');
      }
    });

    //-----------------Payment Account Modal Open-----------------------//
    $(document).on('click', '#add_account_payment', function() {

      var account_status_amount = document.getElementById("account_status_amount").value;

      if (account_status_amount > 0) {
        $('#debit_radio').removeClass('hide');
        // var eleman = document.getElementById('payment_amount');

        // eleman.setAttribute("validate_debit_amount", true);

        // var message;
        // jQuery.validator.addMethod("validate_debit_amount", function(value, element) {
        //   var old_value = Number(account_status_amount);
        //   var result;
        //   if (old_value >= value) {
        //     result = true;
        //     message = "";
        //   } else {
        //     result = false;
        //     message = "please enter small and valid amount";
        //   }
        //   return this.optional(element) || result;
        // }, function() {
        //   return message;
        // });


      } else {
        $('#debit_radio').addClass('hide');
        // document.getElementById("myAnchor").removeAttribute("href"); 
        // var eleman = document.getElementById('payment_amount');
        // eleman.removeAttribute("validate_debit_amount");
      }

      $('#statusMsg').html('');
      $('#frmadd_account_payment').trigger('reset');
      var validator = $("#frmadd_account_payment").validate();
      validator.resetForm();
      $('#Addaccount').modal('show');
    });
    //----------------- Payment Account Form Submit-----------------------//
    $('#frmadd_account_payment').submit(function(e) {
      e.preventDefault();
      $('.addaccountbtn').prop("type", "button");
      $('#Addaccount_button').prop('disabled', true);

      if ($('#frmadd_account_payment').valid()) {
        $('#statusMsg_account').html('Processing...');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-account';
        var formDate = $('#frmadd_account_payment').serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
               $('.addaccountbtn').prop("type", "submit");
              $('#Addaccount_button').prop('disabled', false);
              $('#statusMsg_account').html(data.msg);
              $('#frmadd_account_payment').trigger('reset');
              // fillTableAccount();
              $('#frmaccounting').submit();
              accountStatus();
              setTimeout(function() {
                $('#statusMsg_account').html('');
                $('#Addaccount').modal('hide');
              }, 2000); //Time before execution
            } else {
              setTimeout(function() {
                $('#statusMsg_account').html('');
              }, 3000); //Time before execution
            }
          } else {
            setTimeout(function() {
              $('#statusMsg_account').html('');
            }, 3000); //Time before execution
          }
        }, 'json');
      } else {
        $('.addaccountbtn').prop("type", "submit");
        $('#Addaccount_button').prop('disabled', false);
      }
    });

    /* $('.testtooltip').tooltip({
      sanitize: false
    }).tooltip('show')
    $('[data-toggle="tooltip"]').tooltip(); */

    // $('.datatable-basic-account').on('draw.dt', function() {
    //   $('[data-toggle="tooltip"]').tooltip();
    // });



  });
  /// view history 

  $(document).on('click', '.btnhistory', function() {
    var payment_txn_id = $(this).data('paytxnid');
    var refund_txn_id = $(this).data('refundtxnid');
    var currency_sign = "<?php echo $currency ?>";
    $('#refund_list_history').html('<h5>Data loading... Please wait</h5>');
    $('#refund_sechedule_history').modal('show');

    var targetUrl = "<?php echo SITEURL ?>ajax/ajss_accounting";
    $.post(targetUrl, {
      payment_txn_id: payment_txn_id,
      refund_txn_id: refund_txn_id,
      action: 'view_refund_sechedule_history'
    }, function(data, status) {
      if (status == 'success') {

        if (data.code == 1) {
          var list = '<table class="table"><thead><tr><th>Refunded Date</th><th>Reason</th><th>(' + currency_sign + ') Refunded Amount</th><th>Refunded By</th></tr></thead><tbody>';

          $.each(data.msg, function(key, value) {

            list += '<tr>';
            list += '<td> ' + value.payment_date + ' </td>';
            list += '<td> ' + value.reason + ' </td>';
            list += '<td> ' + value.refund_amount + ' </td>';
            list += '<td> ' + value.created_by + ' </td>';
            list += '</tr>';
          });

          list += '</tbody></table>';

          $('#refund_list_history').html(list);

        } else {
          $('#refund_list_history').html(data.msg);
        }
      } else {
        displayAjaxMsg(data.msg, data.code);
      }

    }, 'json');

  });


  //Refund Payment
  $(document).on('click', '.refund', function() {
    $('#refundmodel').modal('show');
    var old_amount = $(this).data('old_amount');
    var txnid = $(this).data('txnid');
    var paygatewaye = $(this).data('paygatewaye');
    var paymentTxnId = $(this).data('paytxnid');
    var paymentCredentialsId = $(this).data('payment_credentials_id');
    var auth_token = "<?php echo genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD) ?>";
    var request_token = "<?php echo RandomString(); ?>";

    $('#auth_token').val(auth_token);
    $('#request_token').val('req_' + request_token);
    $('#amount1').val(old_amount);
    $('#paid_txn_amount').val($(this).data('txnamount'));
    $('#refund_amount').val($(this).data('txnamount'));
    $('#txnid').val(txnid);
    $('#payment_txn_id').val(paymentTxnId);
    $('#payment_credentials_id').val(paymentCredentialsId);
    $('#refund_wallet_amount').val($(this).data('wallet_amount'));
    $('#refund_wallet_amount_calculation').val($(this).data('wallet_amount'));
    $('#payid_user').val($(this).data('payid_user'));


    let type_of_refund_payment = $('#acc_identity').val();


    if (type_of_refund_payment == "sch_is_type") {

      $('#type_of_refund_payment').val("Schedule Payment");
    } else if (type_of_refund_payment == "reg_is_type") {
      $('#type_of_refund_payment').val("Registration Payment");
    } else if (type_of_refund_payment == "manu_is_type") {
      $('#type_of_refund_payment').val("Manual Payment");
    } else if (type_of_refund_payment == "refund_is_type") {
      $('#type_of_refund_payment').val("Refund Payment");
    }

  });

  $('#refundfrm').submit(function(e) {
    e.preventDefault();
    const req_token = $('#request_token').val();
    if ($('#refundfrm').valid()) {
      $('.btnsubmit').attr('disabled', true);
      $('.ajaxMsgBotRefund').html('<h3 class="mar-top-zero">Processing...Please Wait</h3>');
      var formDate = $(this).serialize();
      var post_url = "<?php echo PAYSERVICE_URL ?>api/payment_refund";
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
          if (response.data.code == 1) {
            $.post('<?php echo SITEURL ?>ajax/ajss-response_post_url', {
              payment_unique_id: response.data.transactionID,
              request_token: req_token,
              action: 'payment_verify',
            }, function(data, status) {
              if (status == 'success') {
                if (data.code == 1) {
                  $('#refundfrm').trigger('reset');
                  $('#refundmodel').modal('hide');
                  $('.btnsubmit').prop("type", "submit");
                  $('.btnsubmit').attr('disabled', false);
                  displayAjaxMsg('Refund payment has been successfully', data.code);
                  $('.ajaxMsgBotRefund').html('');
                  $('#frmaccounting').submit();
                } else {
                  if (data.code == 0) {
                    $('#refundfrm').trigger('reset');
                    $('#refundmodel').modal('hide');
                    $('.btnsubmit').prop("type", "submit");
                    $('.btnsubmit').attr('disabled', false);
                    displayAjaxMsg('Process failed. Please try again later.', data.code);
                    $('.ajaxMsgBotRefund').html('');
                  }
                }
              }
            }, 'json');

          } else {
            $('#refundfrm').trigger('reset');
            $('#refundmodel').modal('hide');
            $('.btnsubmit').prop("type", "submit");
            $('.btnsubmit').attr('disabled', false);
            $('.ajaxMsgBotRefund').html('');
            displayAjaxMsg(response.data.message, response.data.code);
          }
        },
        error: (response) => {
          $('#refundfrm').trigger('reset');
          $('#refundmodel').modal('hide');
          $('.btnsubmit').prop("type", "submit");
          $('.btnsubmit').attr('disabled', false);
          $('.ajaxMsgBotRefund').html('');
          displayAjaxMsg(response.data.message);
        }
      })
    }

  });
  //End Refund Payment





  //---------------------Invoice Table--------------------------//
  function fillTable(mydata) {
    var table = $('.datatable-basic-invoice').DataTable({
      autoWidth: false,
      destroy: true,
      ordering: true,
      searching: false,
      lengthChange: false,
      processing: true,
      responsive: true,
      ajax: {
        "url": '<?php echo SITEURL ?>ajax/ajss-invoice',
        "type": "post",
        "data": function(d) {
          d.action = "list_invoice";
          d.user_id = '<?php echo $user_id ?>';
          d.is_type = mydata;
        },

      },
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      'columns': [{
          'data': 'invoice_id',
          searchable: false,
          orderable: false
        },
        {
          'data': 'invoicedate',
          searchable: false,
          orderable: true

        },
        {
          'data': 'receipt_id',
          searchable: false,
          orderable: false
        },
        {
          'data': 'amount',
          searchable: false,
          orderable: true
        },
        {
          'data': 'due',
          searchable: false,
          orderable: true
        },
      ],
      "order": [
        [1, "desc"]
      ],
      "columnDefs": [{

        "render": function(data, type, row) {
          var btn = '';
          <?php if (check_userrole_by_code('UT05') || in_array("su_family_invoice_send", $_SESSION['login_user_permissions'])) {   ?>
            btn += "<a href='javascript:void(0);' title='Send' class='action_link text-warning viewinvoice'  data-invoice_mainid = '" + row['invoice_mainid'] + "' data-invoiceid = '" + row['invoice_id'] + "' data-receiptid = '" + row['receipt_id'] + "' data-invoicepath = '" + row['invoice_path'] + "' data-receiptpath = '" + row['receipt_path'] + "'>Send</a>";
          <?php } ?>
          <?php if (check_userrole_by_code('UT05') || in_array("su_family_invoice_download", $_SESSION['login_user_permissions'])) {   ?>
            btn += "<a href='javascript:void(0);' title='Download' class='action_link text-primary downloadinvoice' data-id = '" + row['id'] + "' data-invoiceid = '" + row['invoice_id'] + "' data-receiptid = '" + row['receipt_id'] + "' data-invoicepath = '" + row['invoice_path'] + "' data-receiptpath = '" + row['receipt_path'] + "'>Download</a>";
          <?php } ?>

          return btn;
        },
        "targets": 5
      }, ]

    });
  }




  //---------------------Payment Account Table--------------------------//
  $('#frmaccounting').submit(function(e) {
    accountStatus()
    var smr_type = $('#acc_identity').val();
    var familyid_userid = "";

    if (smr_type == 'sch_is_type') {
      is_type_no = 3;
      $('.heading_for_all').html('<h3 class="panel-title">Schedule Transaction</h3>');
      $('.heading_for_invoice').html('<h3 class="panel-title">Schedule Invoice & Receipt</h3>');
      familyid_userid = $('#fam_id').val();
      $('.fade_transaction_button').addClass('fade');
      $('#change_des').html('<th>Description</th>');
    } else if (smr_type == 'reg_is_type') {
      is_type_no = 1;
      $('.heading_for_all').html('<h3 class="panel-title">Registration Transaction</h3>');
      $('.heading_for_invoice').html('<h3 class="panel-title">Registration Invoice & Receipt</h3>');
      familyid_userid = $('#fam_id').val();
      $('.fade_transaction_button').addClass('fade');
      $('#change_des').html('<th>Description</th>');

    } else if (smr_type == 'manu_is_type') {
      is_type_no = 4;
      $('.heading_for_all').html('<h3 class="panel-title">Manual Transaction</h3>');
      $('.heading_for_invoice').html('<h3 class="panel-title">Manual Invoice & Receipt</h3>');
      familyid_userid = $('#fam_id').val();
      $('.fade_transaction_button').addClass('fade');
      $('#change_des').html('<th>Reason</th>');
      var hide_column = 1
    } else if (smr_type == 'refund_is_type') {
      is_type_no = 6;
      $('.heading_for_all').html('<h3 class="panel-title">Refund Transaction</h3>');
      $('.heading_for_invoice').html('<h3 class="panel-title">Refund Invoice & Receipt</h3>');
      familyid_userid = $('#fam_id').val();
      $('.fade_transaction_button').addClass('fade');
      $('#change_des').html('<th>Reason</th>');
      var hide_column = 1
    } else {
      is_type_no = 0; //// wallet ke liye hai
      $('.fade_transaction_button').removeClass('fade');
      $('.heading_for_all').html('<h3 class="panel-title">Wallet Transaction</h3>');
      $('.heading_for_invoice').html('<h3 class="panel-title">Wallet Invoice & Receipt</h3>');
      $('#change_des').html('<th>Description</th>');
      var hide_column = 1
    }

    if (smr_type == '' || smr_type == null) {
      smr_type = 'wal_is_type';
      family_id = '<?php echo $_GET['id'] ?>';
      if (family_id == null || family_id == '') {
        family_id = '<?php echo $user_id ?>';
      }
    }

    fillTable(is_type_no);
    e.preventDefault();

    var table = $('.datatable-basic-account').DataTable({
      autoWidth: false,
      destroy: true,
      ordering: true,
      searching: false,
      lengthChange: false,
      processing: true,
      responsive: true,
      ajax: {
        "url": '<?php echo SITEURL ?>ajax/ajss_accounting',
        "type": "post",
        "data": function(d) {
          d.action = "accounting_record";
          d.family_id = family_id;
          d.acc_identity = smr_type;
        }
      },
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      'columns': [{
          'data': 'payment_date'
        },
        //  {
        //   'data': 'father_name',
        //   searchable: false,
        //   orderable: false
        // },
        {
          'data': 'student_name',
          searchable: false,
          orderable: false
        },
        {
          'data': 'primary_email',
          searchable: false,
          orderable: true
        },
        {
          'data': 'description',
          searchable: false,
          orderable: false
        },
        {
          'data': 'amount',
          searchable: false,
          orderable: false
        },
        {
          'data': 'type',
          searchable: false,
          orderable: false
        },
      ],
      "order": [
        [1, "desc"]
      ],
      "columnDefs": [{

          "render": function(data, type, row) {

            var btn = '';
            if (row['hide_button'] == 1) {
              btn += "";
            } else {
              if (row['refund_txn_id'] == null && row['amount_received'] != '0.00' && row['payment_txn_id'] != null) {
                <?php if (check_userrole_by_code('UT01') == '1') { ?>
                  btn += "<a href='javascript:void(0);' title='Refund' class='action_link text-warning refund' data-txnid='" + row['payment_unique_id'] + " ' data-paygatewaye='" + row['payment_gateway'] + "' data-paytxnid=" + row['payment_txn_id'] + " data-payment_credentials_id=" + row['payment_credentials_id'] + " data-txnamount=" + row['txn_amount'] + "  data-old_amount = '" + row['amount'] + "' data-wallet_amount=" + row['wallet_amount'] + " data-payid_user=" + row['payid_user'] + ">Refund</a>";
                <?php } ?>
              } else if (row['refund_txn_id'] == null && row['amount'] != '0.00' && row['payment_txn_id'] != null) {
                <?php if (check_userrole_by_code('UT01') == '1') { ?>
                  btn += "<a href='javascript:void(0);' title='Refund' class='action_link text-warning refund' data-txnid='" + row['payment_unique_id'] + " ' data-paygatewaye='" + row['payment_gateway'] + "' data-paytxnid=" + row['payment_txn_id'] + " data-payment_credentials_id=" + row['payment_credentials_id'] + " data-txnamount=" + row['txn_amount'] + "  data-old_amount = '" + row['amount'] + "' data-wallet_amount=" + row['wallet_amount'] + " data-payid_user=" + row['payid_user'] + ">Refund</a>";
                <?php } ?>
              } else if (row['refund_txn_id'] != null && row['student_name'] != 'hide') {
                btn += "<a href='javascript:void(0);' title='Refund' class='action_link text-info btnhistory' data-txnid='" + row['payment_unique_id'] + " ' data-paygatewaye='" + row['payment_gateway'] + "' data-paytxnid=" + row['payment_txn_id'] + " data-payment_credentials_id=" + row['payment_credentials_id'] + " data-txnamount=" + row['txn_amount'] + "  data-old_amount = '" + row['amount'] +
                  "' data-refundtxnid=" + row['refund_txn_id'] + ">Refund Payment History</a>"
              }
            }
            return btn;
          },
          "targets": 6
        },
        {
          "visible": false,
          "targets": [hide_column, 2]
        }
      ]

    });
  });



  function accountStatus() {
    var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-account';
    var family_user_id = '<?php echo $family->user_id ?>';
    $.post(targetUrl, {
      family_user_id: family_user_id,
      action: 'account_status'
    }, function(data, status) {
      if (status == 'success') {
        if (data.code == 1) {
          $('.totalAmount').html(data.msg);
          $('#account_status_amount').val(data.account_status_amount);
        } else {
          $('.totalAmount').html('0');
        }
      } else {
        $('.totalAmount').html('0');
      }

    }, 'json');
  }


  // $(document).on('click', '.refund', function() {
  //   $('#refundmodel').modal('show');
  //   var old_amount = $(this).data('old_amount');
  //   var txnid = $(this).data('txnid');
  //   var paygatewaye = $(this).data('paygatewaye');
  //   var paymentTxnId = $(this).data('paytxnid');
  //   var paymentCredentialsId = $(this).data('payment_credentials_id');
  //   var auth_token = "<?php echo genrate_encrypt_token(PAYMENT_GATEWAYE_MODE_KEYWORD) ?>";
  //   var request_token = "<?php echo RandomString(); ?>";

  //   $('#auth_token').val(auth_token);
  //   $('#request_token').val('req_' + request_token);
  //   $('#amount1').val(old_amount);
  //   $('#paid_txn_amount').val($(this).data('txnamount'));
  //   $('#txnid').val(txnid);
  //   $('#payment_txn_id').val(paymentTxnId);
  //   $('#payment_credentials_id').val(paymentCredentialsId);
  // });
</script>
<?php include "../footer.php" ?>