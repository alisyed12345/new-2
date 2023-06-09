<?php
$mob_title = "List Event Calendar";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
// if(!in_array("su_event_calendar", $_SESSION['login_user_permissions'])){
// 	include "../includes/unauthorized_msg.php";
// 	return;
// }
$get_cron_data = $db->get_results("SELECT * FROM ss_cron_payment_testing WHERE status <> '2'");
?>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Cron Payment Date</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Cron Payment Date</li>
        </ul>
        <?php if(count((array)$get_cron_data) == 0){ ?>
        <div class="above-content"> <a href="javascript:void(0)" id="cronCalendar"
                class="pull-right"><span class="label label-primary"> Add New Cron Payment Date</span></a>
        </div>
        <?php } ?>
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
                                <th>Cron Date</th>
                                <th>Status</th>
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


<!-- START SCHEDULE MODEL START -->
<div id="modalcronCalendar" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form name="frmcronCalendar" id="frmcronCalendar" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title headtext">Add New Cron Calendar</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cron Date:<span class="mandatory">*</span></label>
                                <input placeholder="Cron Date" required name="cron_date" id="cron_date" value=""
                                    class="form-control" type="text">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="group">Status:<span class="mandatory">*</span></label>
                                <select class="form-control" name="status" id="status" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-9">
                            <strong id="statusMsg"></strong>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="action" id="action" value="cron_calendar_add">
                            <input type="hidden" name="cron_calendar_id" id="cron_calendar_id" value="">
                            <button type="submit" class="btn btn-success btnsubmit"><i
                                    class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    //FILL TABLE
    fillTable();
var yesterday = new Date((new Date()).valueOf()-1000*60*60*24);

    $('#cron_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: true,
        formatSubmit: 'yyyy-mm-dd',
        disable: [
        { from: [0,0,0], to: yesterday }
        ]
    });
    
    $('input').on('keypress', function(e) {
        if (this.value.length === 0 && e.which === 32){
            return false;
        }
    });
    //REMOVE Permission
    $(document).on('click', '.remove_cron_calendar', function(data, status) {
		var id = $(this).data('id');
		$.confirm({
			title: 'Confirm!',
			content: 'Do you want to delete this cron date?',
			buttons: {
				confirm: function () {
					$('.spinner').removeClass('hide');
					$.post('<?php echo SITEURL ?>ajax/ajss-cron-calendar', {
						id:id,action:'delete_cron_calendar'
					}, function(data, status) {
						if (status == 'success') {
                            fillTable();
						}
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                                $(".ajaxMsg").html("");
                        }, 8000);
					}, 'json');
				},
				cancel: function () {
				}
			}
		});
	});



    //Add Basic fees start
    $('#modalcronCalendar').on('hide.bs.modal', function(e) {
        $('#statusMsg').html('');
        $('#frmcronCalendar').trigger('reset');
        var validator = $("#frmcronCalendar").validate();
        validator.resetForm();
    });

    $(document).on('click', '#cronCalendar', function() {
        $('.headtext').html("Add New Cron Calendar");
        $('#action').val("cron_calendar_add");
        $('#modalcronCalendar').modal('show');
    });


    $('#frmcronCalendar').submit(function(e) {
        e.preventDefault();
        if ($('#frmcronCalendar').valid()) {
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-cron-calendar';
            $('#statusMsg').html('Processing...');

            var formDate = $(this).serialize();
            $.post(targetUrl, formDate, function(data, status) {
                if (status == 'success') {
                    $('#statusMsg').html(data.msg);
                    if (data.code == 1) {
                        fillTable();
                        $('#modalcronCalendar').modal('hide');
                        if ($('#action').val() == 'event_calendar_add') {
                            $('#frmcronCalendar').trigger('reset');
                        }
                        displayAjaxMsg(data.msg, data.code);
                    //     setTimeout(function() {
                    //         $('#modalcronCalendar').modal('hide');
                    // }, 2500);
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }
                } else {
                    displayAjaxMsg(data.msg);
                }
            }, 'json');
        }
    });
    //Add Basic fees end


    //edit Basic fees start

    $('#modalcronCalendar').on('hide.bs.modal', function(e) {
        $('#statusMsg').html('');
        $('#frmcronCalendar').trigger('reset');
        var validator = $("#frmcronCalendar").validate();
        validator.resetForm();
    });


    $(document).on('click', '.editCronCalendar', function() {
        $('.headtext').html("Edit Cron Payment Date");
        $('#cron_calendar_id').val($(this).data('id'));
        $('#cron_date').val($(this).data('crondate'));
        $('#status').val($(this).data('status'));
        $('#action').val("cron_calendar_edit");
        $('#modalcronCalendar').modal('show');
        $('input[name="cron_date_submit"]').val($(this).data('crondate'));
    });


    //edit Basic fees end



});

function fillTable() {
    var table = $('.datatable-basic').DataTable({
        autoWidth: false,
        destroy: true,
        pageLength: <?php echo TABLE_LIST_SHOW ?>,
        responsive: true,
        ajax: '<?php echo SITEURL ?>ajax/ajss-cron-calendar?action=list_cron_calendar',
        sProcessing: '',
        language: {
            loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
        },
        'columns': [{
                'data': 'cron_payment_date',
                searchable: true,
                orderable: true
            },
            {
                'data': 'is_active',
                searchable: true,
                orderable: true
            },
        ],
        "order": [
            [0, "desc"]
        ],
        "columnDefs": [{
            "render": function(data, type, row) {
                var links = "";

                links += "<a href='javascript:;'  data-id='" + row['id'] + "'  data-crondate='" +
                    row['cron_payment_date'] + "' data-status='" + row['status'] +"'  title='Edit' class='text-primary action_link editCronCalendar'>Edit</a>";

                // links += "<a href='javascript:void(0)' data-id = " + row['id'] +
                //     " title='Delete' class = 'text-danger remove_event_calendar action_link'>Delete</a>";


                // return [links, attechment];
                return links;
            },
            "targets": 2
        }]
    });
}
</script>
<?php include "../footer.php" ?>