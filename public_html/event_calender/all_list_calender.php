<?php
$mob_title = "List Event Calendar";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_event_calendar_list", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Event Calendar</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li cl ass="active">Event Calendar</li>
        </ul>
        <?php if (in_array("su_event_calendar_create", $_SESSION['login_user_permissions'])) {   ?>
            <div class="above-content"> <a href="javascript:void(0)" id="addeventCalendar" class="pull-right"><span class="label label-primary"> Add New Event</span></a></div>
        <?php  } ?>
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
                                <th>Event Date</th>
                                <th>Event Name</th>
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
<div id="modalAddeventCalendar" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form name="frmAddeventCalendar" id="frmAddeventCalendar" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title headtext">Add New Event Calendar</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Event Date:<span class="mandatory">*</span></label>
                                <input placeholder="Event Date" required name="event_date" id="event_date" value="" class="form-control" type="text">
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="group">Event Name:<span class="mandatory">*</span></label>
                                <input type="text" class="form-control" maxlength="50" name="event_name" id="event_name" placeholder="Event Name" spacenotallow="true" required>
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
                            <input type="hidden" name="action" id="action" value="event_calendar_add">
                            <input type="hidden" name="event_calendar_id" id="event_calendar_id" value="">
                            <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="http://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js"></script>
<script>

jQuery.extend( jQuery.fn.dataTableExt.oSort, {
"date-uk-pre": function ( a ) {
    var ukDatea ="";
    
    if(a.search('-')>-1){
        ukDatea = a.split('-');
    }else{
        ukDatea = a.split('/');
    };

    return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
},

"date-uk-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
},

"date-uk-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
}
} );


    $(document).ready(function() {
        //FILL TABLE
        fillTable();
        var yesterday = new Date((new Date()).valueOf() - 1000 * 60 * 60 * 24);

        $('#event_date').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: true,
            formatSubmit: 'yyyy-mm-dd',
            disable: [{
                from: [0, 0, 0],
                to: yesterday
            }]
        });

        $('input').on('keypress', function(e) {
            if (this.value.length === 0 && e.which === 32) {
                return false;
            }
        });
        //REMOVE Permission
        $(document).on('click', '.remove_event_calendar', function(data, status) {
            var id = $(this).data('id');
            $.confirm({
                title: 'Confirm!',
                content: 'Do you want to delete this event?',
                buttons: {
                    confirm: function() {
                        $('.spinner').removeClass('hide');
                        $.post('<?php echo SITEURL ?>ajax/ajss-event-calendar', {
                            id: id,
                            action: 'delete_event_calendar'
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
                    cancel: function() {}
                }
            });
        });



        //Add Basic fees start
        $('#modalAddeventCalendar').on('hide.bs.modal', function(e) {
            $('#statusMsg').html('');
            $('#frmAddeventCalendar').trigger('reset');
            var validator = $("#frmAddeventCalendar").validate();
            validator.resetForm();
        });

        $(document).on('click', '#addeventCalendar', function() {
            $('.headtext').html("Add New Event Calendar");
            $('#action').val("event_calendar_add");
            $('#modalAddeventCalendar').modal('show');
        });


        $('#frmAddeventCalendar').submit(function(e) {
            e.preventDefault();
            if ($('#frmAddeventCalendar').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-event-calendar';
                $('#statusMsg').html('Processing...');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('#statusMsg').html(data.msg);
                        if (data.code == 1) {
                            fillTable();
                            if ($('#action').val() == 'event_calendar_add') {
                                $('#frmAddeventCalendar').trigger('reset');
                            }
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('#modalAddeventCalendar').modal('hide');
                            }, 2500);
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

        $('#modalAddeventCalendar').on('hide.bs.modal', function(e) {
            $('#statusMsg').html('');
            $('#frmAddeventCalendar').trigger('reset');
            var validator = $("#frmAddeventCalendar").validate();
            validator.resetForm();
        });


        $(document).on('click', '.editEventCalendar', function() {
            $('.headtext').html("Edit School Calendar");
            $('#event_calendar_id').val($(this).data('id'));
            $('#event_date').val($(this).data('eventdate'));
            $('#event_name').val($(this).data('eventname'));
            $('#status').val($(this).data('status'));
            $('#action').val("event_calendar_edit");
            $('#modalAddeventCalendar').modal('show');
            $('input[name="event_date_submit"]').val($(this).data('eventdate'));
        });


        //edit Basic fees end



    });

    function fillTable() {
        var table = $('.datatable-basic').DataTable({
            autoWidth: false,
            destroy: true,
            pageLength: <?php echo TABLE_LIST_SHOW ?>,
            responsive: true,
            ajax: '<?php echo SITEURL ?>ajax/ajss-event-calendar?action=list_event_calendar',
            sProcessing: '',
            language: {
                loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
            },
            'columns': [{
                    'data': 'program_date',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'program_name',
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
            "columnDefs": [
                {
                    "targets": 0,
                    "type": "date-uk" 
                },{
                "render": function(data, type, row) {
                    var links = "";
                    <?php if (in_array("su_event_calendar_edit", $_SESSION['login_user_permissions'])) { ?>
                        links += "<a href='javascript:;'  data-id='" + row['id'] + "'  data-eventdate='" +
                            row['program_date'] + "' data-eventname='" + row['program_name'] +
                            "' data-status='" + row['status'] +
                            "'  title='Edit' class='text-primary action_link editEventCalendar'>Edit</a>";
                    <?php } ?>
                    <?php if (in_array("su_event_calendar_delete", $_SESSION['login_user_permissions'])) { ?>

                        links += "<a href='javascript:void(0)' data-id = " + row['id'] +
                            " title='Delete' class = 'text-danger remove_event_calendar action_link'>Delete</a>";
                    <?php } ?>

                    // return [links, attechment];
                    return links;
                },
                "targets": 3,
                // "type": "date"
            }]
        });
    }
</script>
<?php include "../footer.php" ?>