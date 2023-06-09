<?php 
$mob_title = "Registration Payment History";
include "../header.php";

if(!in_array("su_family_info", $_SESSION['login_user_permissions'])){
  include "../includes/unauthorized_msg.php";
exit;
}    

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
            <h4> Payment History</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
            <li class="active"> Payment History</li>
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
                        <div class="ajaxMsg"></div>
                        <table class="table datatable-basic table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>1st parent Name</th>
                                    <th>Phone No.</th>
                                    <th>Primary Email</th>
                                    <th>Payment Date</th>
                                    <th>Last 4 Digits of CC</th>
                                    <th>Final Amount</th>
                                    <th>Payment Txns Id</th>
                                    <th>Status</th>
                                    
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



    <script>
        var table;

        $(document).ready(function() {
            //FILL TABLE
            fillTable();
        });

        function fillTable() {
            table = $('.datatable-basic').DataTable({
                autoWidth: false,
                destroy: true,
                pageLength: <?php echo TABLE_LIST_SHOW ?>,
                responsive: true,
                ajax: '<?php echo SITEURL ?>ajax/ajss-registration-payment-history?action=list_registration_payment_history',
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
                    {
                        'data': 'city',
                        searchable: true,
                        orderable: true
                    },
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
                ],
                "order": [
                    [1, "asc"]
                ],
                {
                    "visible": false,
                    "targets": [0]
                }
                ]
            });
        }
    </script>
    <?php include "../footer.php"?>