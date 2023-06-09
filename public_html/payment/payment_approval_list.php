<?php 
$mob_title = "Family Schedule Payment ";
include "../header.php";

$user_id = trim(htmlspecialchars($_GET['id']));

if(!in_array("su_payment_approval_list", $_SESSION['login_user_permissions']) && is_numeric($user_id)){
include "../includes/unauthorized_msg.php";
exit;
} 
?>
<style>
    label.error {
        color: red;
    }
    .fas.fa-calendar{
        display:none;
    }
</style>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Payment Approval List</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
            <li class="active">Payment Approval List</li>
        </ul>
    </div>
    <div class="above-content">


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
                            <th>Schedule Date</th>
                            <th>Parent Name</th>
                            <th>Parent Email</th>
                            <th>Child(ren)</th>
                            <th>Last 4 Digits of CC</th>
                            <th>Final Amount</th>
                            <th>Status</th>
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<!-- /View Schedule History -->
<script type="text/javascript" src="http://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js"></script>

<script>
        $(document).ready(function() {
            //FILL TABLE
            fillTable();

            $(document).on('click','.btndisapproved', function(){
                var id = $(this).data('id');
                var date = $(this).data('date');
                var parent = $(this).data('parent');
                var itemids = $(this).data('itemids');
                var schedule_unique_id = $(this).data('schedule_unique_id');
                
                $.confirm({
                    title: 'Confirm!',
                    content: 'Are you sure you want to Cancel this payment!',
                    buttons: {
                        confirm: function () {
                            $.confirm({
                                    title: parent + ' - ' + date,
                                    content: '' +
                                    '<form action="" class="formName">' +
                                    '<div class="form-group">' +
                                    '<label>Reason<span class="mandatory">*</span></label>' +
                                    '<textarea class="reason form-control" required></textarea>' +
                                    '</div>' +
                                    '</form>',
                                    buttons: {
                                        formSubmit: {
                                            text: 'Cancel',
                                            btnClass: 'btn-primary',
                                            action: function () {
                                                var reason = this.$content.find('.reason').val();
                                                if(!$.trim(reason)){
                                                    $.alert('provide a valid Reason');
                                                    return false;
                                                }else{
                                                    $.post('<?php echo SITEURL ?>ajax/ajss-payment-approval-list', 
                                                    {
                                                        id: id, 
                                                        schedule_unique_id: schedule_unique_id,
                                                        reason: reason,
                                                        itemids:itemids,
                                                        action: "payment_disapproved"
                                                    },
                                                    function(data, status) {
                                                        if(status === "success") {
                                                            
                                                            if(data.code == 1){
                                                                fillTable();
                                                                $.alert(data.msg, 'Success');
                                                            }else{
                                                                $.alert(data.msg, 'Failed');
                                                            }
                                                        }
                                                    },"json");
                                                    // return false;
                                                }
                                            }
                                        },
                                        close: function () {
                                            //close
                                        },
                                    },
                                    onContentReady: function () {
                                        // bind to events
                                        var jc = this;
                                        this.$content.find('form').on('submit', function (e) {
                                            // if the user submits the form by pressing enter in the field.
                                            e.preventDefault();
                                            jc.$$formSubmit.trigger('click'); // reference the button and click it
                                        });
                                    }
                                });
                        },
                        close: function () {
                        }
                    }
                });

            });


            
        });

        function fillTable() {
            var table = $('.datatable-basic').DataTable({
                autoWidth: false,
                destroy: true,
                ordering: true,
                searching: true,
                lengthChange: true,
                processing: true,
                responsive: true,
                ajax: {
                    "url": '<?php echo SITEURL ?>ajax/ajss-payment-approval-list', 
                    "type": "post",
                    "data": function(d) {
                        d.action = "list_approved_payments";
                    }
                },
                sProcessing: '',
                language: {
                    loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
                },
                'columns': [
                    {
                        'data': 'schedule_payment_date',
                        searchable: true,
                        orderable: true
                    },
                    {
                        'data': 'parent_name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        'data': 'primary_email',
                        searchable: true,
                        orderable: true
                    },
                    {
                        'data': 'child_name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        'data': 'credit_card_no',
                        searchable: true,
                        orderable: true
                    },
                    {
                        'data': 'amount',
                        searchable: true,
                        orderable: true
                    },
                    {
                        'data': 'schedule_status',
                        searchable: true,
                        orderable: true
                    },
                    
                    
                ], 
                "order": [[ 0, "asc" ]], 
                "columnDefs": [
                        {"targets":0, "type":"date"},
                        {
                            "render": function(data, type, row) { 
                                btn = '';
                                <?php 
                                if(check_userrole_by_code('UT01')){ ?>
                                
                                btn += "<a href='javascript:void(0)' class='text-danger action_link btndisapproved' data-itemids='"+row['sch_item_ids']+"' data-parent='"+row['parent_name']+"' data-date='"+row['schedule_payment_date']+"' data-schedule_unique_id='"+row['schedule_unique_id']+"' data-id='"+row['id']+"'> Cancel </a>";
        
                                <?php  } ?>
                                return btn;

                            }, 
                            "targets":7, "type":"date",
                            
                        }
                ]

            });
        }
    </script>
    <?php include "../footer.php"?>