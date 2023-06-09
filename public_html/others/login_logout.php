<?php include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_login_history", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Login Logout History</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Login Logout History</li>
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
                        <table class="table datatable-basic table-bordered">
							<thead>
								<tr>
								<th>Login Id</th>
								<th>Staff Name</th>
									<th>Type</th>
									<th>Login Date Time</th>
									<th>Logout Date Time</th>
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
$( document ).ready(function() {
	//FILL TABLE
	fillTable();
});

function fillTable(){
	var table = $('.datatable-basic').DataTable({
        autoWidth: false,
		destroy: true,
		pageLength: <?php echo TABLE_LIST_SHOW ?>,
		responsive: true,
		ajax: '<?php echo SITEURL ?>ajax/ajss-authenticate?action=login_history',
		sProcessing:'',		
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		'columns': [
			{ 'data': 'id'},
			{ 'data': 'staff_name',searchable: true,orderable: true },
			{ 'data': 'user_type',searchable: true,orderable: true },
			{ 'data': 'login_datetime',searchable: true,orderable: true },
			{ 'data': 'logout_datetime',searchable: true,orderable: true }
		],
		"order": [[ 0, "asc" ]],
		"columnDefs": [
			{ "visible": false,  "targets": [ 0 ] }
        ]
    });
}
</script>
<?php include "../footer.php"?>