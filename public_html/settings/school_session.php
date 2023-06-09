<?php
$mob_title = "List School Session";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01')) {
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->
<style type="text/css">
html, body {margin: 0; height: 100%; overflow: hidden}
.swal2-styled.swal2-confirm {
    font-size: 1.7em !important;
}

.swal2-styled.swal2-cancel {
    font-size: 1.7em !important;
}
span.mands {
  color: #ff0000;
  display: inline;
  line-height: 1;
  font-size: 12px;
  margin-left: 5px;
}
</style>

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>School Sessions</h4>
        </div>
    </div>
    <?php 


    if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->major)) {
             ?>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">List School Sessions</li>
        </ul>
    </div> 
    <?php }else{ ?>
        <div class="breadcrumb-line">
            <ul class="breadcrumb">
                <li><a href="<?php echo SITEURL ?>check_data" ><i class="glyphicon glyphicon-check"></i> Check Mandatory Information</a></li>
            </ul>
        </div>
    <?php } ?>
    <div class="above-content text-right"> <a href="javascript:void(0)" id="schoolsession" class="btn btn-primary">Add
            School Session</a>
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
                    <table class="table datatable-basic table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>School Session</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <!-- <th>Discount Unit</th>
                                <th>Discount Value</th> -->
                                <th>Current Session</th>
                                <!-- <th>Status</th> -->
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

<input type="hidden" id="sessid" name="sessid" value="<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION']; ?>">
<!-- START SCHEDULE MODEL START -->
<div id="modalschoolsession" class="modal fade">
    <div class="modal-dialog modal-dialog-centered" style="width: 700px !important;">
        <div class="modal-content">
            <form name="frmschoolsession" id="frmschoolsession" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title headtext">Add School Session</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group">School Session:<span class="mands">*</span></label>
                                <input type="text" class="form-control required" maxlength="20" name="school_session"
                                    id="school_session" spacenotallow="true" placeholder="School Session">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group">Current Session:<span class="mands">*</span></label>
                                <select class="form-control" name="current_session" id="current_session" required>
                                    <option value="">Select</option>
                                    <option value="1">YES</option>
                                    <option value="0">NO</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group">Start Date:<span class="mands">*</span></label>
                                <input type="text" class="form-control startdate required" maxlength="20" name="start_date" id="start_date" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group">End Date:<span class="mands">*</span></label>
                                <input type="text" class="form-control required" maxlength="20" name="end_date" id="end_date" endDate="true">
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group">Yearly One Time Payment Discount Unit:</label>
                                <select class="form-control" name="discount_unit" id="discount_unit">
                                    <option value="">Select Unit</option>
                                    <option value="p">Percent</option>
                                    <option value="d">Dollar</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="discount_percent">Yearly One Time Payment Discount Value:</label>
                                    <input type="text" class="form-control" name="discount_percent"  maxlength="4" id="discount_percent" placeholder="Discount Percent ($) ">
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group">Status:</label>
                                <select class="form-control" name="status" id="status" required>
                                    <option value="">Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div> -->
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-8 text-right">
                            <strong id="statusMsg"></strong>
                        </div>
                        <div class="col-md-4">
                            <input type="hidden" name="action" id="action" value="school_session_add">
                            <input type="hidden" name="school_session_id" id="school_session_id" value="">
                            <button type="submit" class="btn btn-success btnsubmit">Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

            </form>
        </div>
    </div>
</div>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script>
$(document).ready(function() {
    //FILL TABLE
    fillTable();
    $(document).on('change', '#discount_unit', function(){
        if($(this).val() == 'p'){
            $('#discount_percent').removeAttr('dollarsscents');
            $('#discount_percent').attr('parcentamount', true);
        }else{
            $('#discount_percent').removeAttr('parcentamount');
            $('#discount_percent').attr('dollarsscents', true);
        }
    });
    jQuery.validator.addMethod("dollarsscents", function(value, element) {
        return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,4})?$/i.test(value);
    }, "Please enter a valid amount");

    jQuery.validator.addMethod("parcentamount", function(value, element) {
        return this.optional(element) ||/^((100)|(\d{1,2}(\.\d*)?))$/i.test(value);
    }, "The percentage must be between 1 and 100");

    $.validator.addMethod("validYear", function(value, element) {
        return ((parseInt(value) > 1999) && parseInt(value) < 2040);
    }, "Session name should be in 2000-01 format");

    $.validator.addMethod("endDate", function(value, element) {
            var startDate = $('.startdate').val();
            return Date.parse(startDate) <= Date.parse(value) || value == "";
    }, "End date must be after start date");

    $('#start_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: 100,
        min: [<?php echo date('Y') ?>, <?php echo date('m')-1 ?>, <?php echo date('d') ?>],
        formatSubmit: 'yyyy-mm-dd',
        onOpen: function(context) {
        var picker = $("#end_date").pickadate('picker');
        picker.clear();
        },
        onSet: function(context) {
        var date = new Date(context.select);
        var picker = $("#end_date").pickadate('picker');
        picker.set('min', date);
        }
      
    });


    $('#end_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: 100,
        formatSubmit: 'yyyy-mm-dd'
    });
    //REMOVE


    $(document).on('click', '.remove_school_session', function(data, status) {
        var id = $(this).data('sesid');
        $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete school session?',
            buttons: {
                confirm: function () {
                    $('.spinner').removeClass('hide');
                    $.post('<?php echo SITEURL ?>ajax/ajss-school-session', {
                        id: id,
                        action: 'delete_school_session'
                    }, function(data, status) {
                        if (status == 'success') {
                            displayAjaxMsg(data.msg, data.code);
                            // setTimeout(function() {
                            //     $(".ajaxMsg").html("");
                            // }, 8000);
                            fillTable();
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                            // setTimeout(function() {
                            //     $(".ajaxMsg").html("");
                            // }, 8000);
                        }
                    }, 'json');
                },
                cancel: function () {
                }
            }
        });
    });




    //REMOVE Permission
    $(document).on('click', '.setsession', function(data, status) {
        var statusSess = $(this).data('result');
        var heading = "Do you want to set this session temporary?";
        var resultText ="Remove Session Temp";
        if(statusSess == 0){
        var heading = "Do you want to remove this session temporary?";
        var resultText ="Set Session Temp";
        }

        //if($.confirm('<h3>Do you want to set session?</h3>')){
        $('.spinner').removeClass('hide');

        var id = $(this).data('id');
        var session = $(this).data('session');

        var header_text = "<?php echo SCHOOL_NAME ?>" + " ( " + session + " ) ";

        Swal.fire({
            title: heading,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: "No",
            closeOnConfirm: false,
            closeOnCancel: false
        }).then((result) => { 
            if (result.isConfirmed) {
                $('.bartext' + id).html('<span style="color:black;">Processing...</span>');
                $.post('<?php echo SITEURL ?>ajax/ajss-school-session', {
                    id: id,
                    session: session,
                    sessionstatus:statusSess,
                    action: 'set_session'
                }, function(data, status) {
                    if (status == 'success') {
                        $('#sessid').val(id);
                        $('.header_lable_text').html(header_text);

                        $('.bartext' + id).html(resultText);
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                        fillTable();
                    } else {
                        $('.bartext' + id).html(resultText);
                        displayAjaxMsg(data.msg, data.code);
                        // setTimeout(function() {
                        //     $(".ajaxMsg").html("");
                        // }, 8000);
                    }
                }, 'json');
            }
        })

        //}
    });




    //Add School Session start
    $('#modalschoolsession').on('hide.bs.modal', function(e) {
        $('#statusMsg').html('');
        $('#frmschoolsession').trigger('reset');
        var validator = $("#frmschoolsession").validate();
        validator.resetForm();
    });

    $(document).on('click', '#schoolsession', function() {
        $('.headtext').html("Add School Session");
        $('#action').val("school_session_add");
        $('#modalschoolsession').modal('show');
    });

    $('#frmschoolsession').submit(function(e) {
        e.preventDefault();
        if ($('#frmschoolsession').valid()) {
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-school-session';
            $('#statusMsg').html('Processing...');

            var formDate = $(this).serialize();
            $.post(targetUrl, formDate, function(data, status) {
                if (status == 'success') {
                    $('#statusMsg').html(data.msg);
                    if (data.code == 1) {
                        fillTable();
                        if ($('#action').val() == 'school_session_add') {
                            $('#frmschoolsession').trigger('reset');
                        }
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            $("#statusMsg").html("");
                            $('#modalschoolsession').modal('hide');
                        }, 3000);
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            $("#statusMsg").html("");
                            $('#modalschoolsession').modal('hide');
                        }, 3000);
                    }
                } else {
                    displayAjaxMsg(data.msg);
                    setTimeout(function() {
                        $("#statusMsg").html("");
                        $('#modalschoolsession').modal('hide');
                    }, 3000);
                }
            }, 'json');
        }
    });

    //Add School Session end


    //edit School Session start

    $('#modalschoolsession').on('hide.bs.modal', function(e) {
        $('#statusMsg').html('');
        $('#frmschoolsession').trigger('reset');
        var validator = $("#frmschoolsession").validate();
        validator.resetForm();
    });


    $(document).on('click', '.editschoolsession', function() {
        $('.headtext').html("Edit School Session");
        $('#school_session_id').val($(this).data('id'));
        $('#school_session').val($(this).data('school_session'));
        $('#current_session').val($(this).data('current_session'));
        $('#start_date').val($(this).data('startdate'));
        $('#end_date').val($(this).data('enddate'));
        $('#discount_unit').val($(this).data('discountunit'));
        $('#discount_percent').val($(this).data('discountpercent'));
        $('#status').val($(this).data('status'));
        $('#action').val("school_session_edit");
        $('#modalschoolsession').modal('show');
    });


    //edit School Session end



});

function fillTable() {
    
    var session_id = $('#sessid').val();

    var table = $('.datatable-basic').DataTable({
        autoWidth: false,
        destroy: true,
        pageLength: <?php echo TABLE_LIST_SHOW ?>,
        responsive: true,
        ajax: '<?php echo SITEURL ?>ajax/ajss-school-session?action=list_school_session',
        sProcessing: '',
        language: {
            loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
        },
        'columns': [{
                'data': 'id'
            },
            {
                'data': 'cur_session',
                searchable: true,
                orderable: true
            },
            {
                'data': 'start_date',
                searchable: true,
                orderable: true
            },
            {
                'data': 'end_date',
                searchable: true,
                orderable: true
            },
            // {
            //     'data': 'fees_full_payment_discount_unit',
            //     searchable: true,
            //     orderable: true
            // },
            // {
            //     'data': 'fees_full_payment_discount_value',
            //     searchable: true,
            //     orderable: true
            // },
            {
                'data': 'is_current',
                searchable: true,
                orderable: true
            },
            // {
            //     'data': 'is_actve',
            //     searchable: true,
            //     orderable: true
            // },
        ],
        "order": [
            [0, "desc"]
        ],
        "columnDefs": [{
                "render": function(data, type, row) {
                    console.log(row);
                    var links = "";
                    var text_color = "text-success";
                    var text = "Set Session Temp";  var title = "Set Session"; var result = "1";
                    // if (row['current'] == 1) {

                    links += "<a href='javascript:;'  data-id='" + row['id'] +
                        "'  data-school_session='" + row['session'] + "' data-current_session='" + row[
                            'current'] + "' data-startdate='" + row['start_date'] + "' data-enddate='" +
                        row['end_date'] + "' data-status='" + row['status'] + "' data-discountunit='" +
                        row['fees_full_payment_discount_unit'] + "' data-discountpercent='" + parseFloat(row[
                            'fees_full_payment_discount_value']) +
                        "' title='Edit' class='text-primary action_link editschoolsession'>Edit</a>";
                    // }
                    links += "<a href='javascript:void(0)' data-sesid = " + row['id'] +
                        " title='Delete' class = 'text-danger remove_school_session action_link'>Delete</a>";

                        if(session_id == row['id']){
                            text_color = "text-warning";
                            text = "Remove Session Temp";
                            title = "Remove Session";
                            result = "0";
                        }

                    if(row['current'] == 0){
                    links += "<a href='javascript:void(0)' data-id = " + row['id'] + "  data-session=" +
                        row['session'] +
                        " title='"+title+"' class = '"+text_color+" action_link setsession bartext" +
                        row['id'] + "' data-sess='"+session_id+"' data-result='"+result+"'>"+text+"</a>";
                    // return [links, attechment];
                    }
                    return links;
                },
                "targets": 5
            },
            {
                "visible": false,
                "targets": [0]
            }
        ]
    });
}
</script>
<?php include "../footer.php" ?>