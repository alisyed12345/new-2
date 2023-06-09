<?php
$mob_title = "Payment Credential";
include "../header.php";
$user_id = trim(htmlspecialchars($_GET['id']));
if (!in_array("su_payment_credential_list", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    exit;
}
$family = $db->get_row("select * from ss_family where id='" . $user_id . "' And is_deleted=0");
?>
<style>
    label.error {
        color: red;
    }
</style>

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Account</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="#">Dashboard</a></li>
            <li class="active"><a href="family_info.php">Family Info</a></li>
            <li class="active">Account</li>
        </ul>
    </div>
</div>

<!-- Main content -->
<div class="content-wrapper">
    <!-- Content area -->
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-flat">
                    <div class="panel-body">
                        <div class="ajaxMsg"></div>
                        <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper no-footer">

                            <div class="row" style="font-size: 15px;">
                                <div class="form-group col-md-3">
                                    <select class="form-control" id="family_id_search" name="family_id">
                                        <option value="">Select Family </option>
                                        <option value="1">Fahad Ali</option>
                                        <option value="2">Anushree</option>

                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <a href="javascript:void(0)" id="btnSurveyStatus" class="btn btn-primary">Filter</a>
                                </div>
                                <div class="form-group col-md-1">
                                    <a href="javascript:void(0)" id="btnReset" style="background: #f2af58;" class="btn btn-defult"><span style="color:white;">Reset </span></a>
                                </div>
                                <div class="col-md-3 text-right">
                                    <label><strong> Credit Amount : </strong> $20 </label> <!-- <a href="javascript:;" class="text-primary"> Refund </a> -->
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="javascript:;" id="add_account_payment" class="text-primary hide">+ Transaction </a>
                                </div>

                            </div>
                            <div class="dataTables_length" id="DataTables_Table_0_length"><label>Show <select name="DataTables_Table_0_length" aria-controls="DataTables_Table_0" class="">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> entries</label></div>
                            <div id="DataTables_Table_0_filter" class="dataTables_filter"><label>Search:<input type="search" class="" placeholder="" aria-controls="DataTables_Table_0"></label></div>
                            <table class="table datatable-basic table-bordered dataTable no-footer dtr-inline" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">
                                <thead>
                                    <tr role="row">
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Credit</th>
                                        <th>Debit</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>01/01/2022 10:15 PM</td>
                                        <td style="max-width: 300px;">This message is to confirm that we received a withdraw of $10 in your account.</td>
                                        <td></td>
                                        <td>$10</td>
                                    </tr>

                                    <tr>
                                        <td>01/02/2022 8:15 AM</td>
                                        <td style="max-width: 300px;">This message is to confirm that we received a Deposit of $20 in your account.</td>
                                        <td>$20</td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td>01/03/2022 9:35 AM</td>
                                        <td style="max-width: 300px;">This message is to confirm that we received a withdraw of $30 in your account.</td>
                                        <td></td>
                                        <td>$30</td>
                                    </tr>

                                </tbody>
                            </table>
                            <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">Showing 1 to 10 of 10 entries</div>
                            <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate"><a class="paginate_button previous disabled" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0" id="DataTables_Table_0_previous">Previous</a><span><a class="paginate_button current" aria-controls="DataTables_Table_0" data-dt-idx="1" tabindex="0">1</a></span><a class="paginate_button next disabled" aria-controls="DataTables_Table_0" data-dt-idx="3" tabindex="0" id="DataTables_Table_0_next">Next</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /main content -->

<!-- /page container -->

<!-- Add Modal   -->
<div id="Addaccount" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content maincontentfirst">
            <form id="frmadd_account_payment" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h5 class="modal-title headtext" id="familyinfo_title"> <b> Account Payment</b></h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="group">Amount :</label>
                                <input type="text" class="form-control required" dollarsscents="true" minlength="1" maxlength="8" name="fee_amount" id="fee_amount" placeholder=" Amount ($) " required="" aria-required="true" aria-invalid="false">

                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="group">Payment Type :</label>
                            <div class="form-group">

                                <label class="radio-inline">
                                    <input type="radio" name="payment_status" checked value="1"> Credit
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_status" value="1">Debit
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="group">Description :</label>
                                <textarea type="text" class="form-control required" minlength="1" maxlength="200" name="description" id="description" placeholder="Description " required="" aria-required="true" aria-invalid="false"></textarea>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="modal-footer">
                            <div class="col-md-9">
                                <strong id="statusMsg"></strong>
                            </div>
                            <div class="col-md-3">
                                <input type="hidden" name="action" id="action" value="account_payment_add">
                                <input type="hidden" name="payid" id="payid" value="">
                                <input type="hidden" name="family_id" id="family_id" value="<?php echo $user_id; ?>">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">close</button>
                            </div>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
<!--    /Add modal -->
<script type="text/javascript">
    $(document).ready(function() {
        //fillTable();
        jQuery.validator.addMethod("dollarsscents", function(value, element) {
            return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
        }, "Please enter a valid amount");

        $(document).on('click', '#add_account_payment', function() {
            $('#statusMsg').html('');
            $('#confirmmsg').html('');
            $('#frmadd_account_payment').trigger('reset');
            var validator = $("#frmadd_account_payment").validate();
            validator.resetForm();
            $('#Addaccount').modal('show');
            $('.headtext').html("Payment Transaction");
            $('#action').val("account_payment_add");
        });


        $(document).on('click', '#btnSurveyStatus', function() {
            var family_id_search = $('#family_id_search').val();
            if (family_id_search > 0) {
                $('#add_account_payment').removeClass('hide');
            } else {
                $('#add_account_payment').addClass('hide');

            }
        });
        $(document).on('click', '#btnReset', function() {
            $('#family_id_search').val('');
            $('#add_account_payment').addClass('hide');

        });
        /*     $(document).on('click', '.view_detail', function() {
             
                $('#Showaccount').modal('show');
               
            }); */

        /*   $('#frmadd_account_payment').submit(function(e) {
              e.preventDefault();
              if ($('#frmadd_account_payment').valid()) {
                  $('#statusMsg').html('Processing...');
                  var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-credential';
                  var formDate = $('#frmadd_account_payment').serialize();
                  $.post(targetUrl, formDate, function(data, status) {
                      if (status == 'success') {
                          $('#statusMsg').html(data.msg);
                          if (data.code == 1) {
                              $('#Addaccount').modal('hide');
                              $('#frmadd_account_payment').trigger('reset');
                              fillTable();
                              displayAjaxMsg(data.msg, data.code);
                          } else {
                              displayAjaxMsg(data.msg, data.code);
                          }
                      } else {
                          displayAjaxMsg(data.msg);
                      }
                  }, 'json');
              }
          }); */

        //End Add Credit Card  
        /*       $(document).on('click', '.editmodel', function() {
                  $('#Addaccount').modal('show');
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
              }); */

    });

    /*    function fillTable() {
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
                       'data': 'credit_card_type',
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
                       links = links + "<a href='javascript:;' class='text-danger action_link remove_credential' title='Send Message' data-id='" + row['id'] + "' data-familyid='" + row['family_id'] + "'>Delete</a>";
                       return links;
                   },
                   "targets": 4
               }, ]
           });
       } */
</script>
<?php include "../footer.php" ?>