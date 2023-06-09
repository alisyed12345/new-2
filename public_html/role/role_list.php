<?php
$mob_title = "List Role";
include "../header.php";


//AUTHARISATION CHECK 
if (!in_array("su_role_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Role</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">List Role</li>
		</ul>
	</div>

	<?php if (in_array("su_role_create", $_SESSION['login_user_permissions'])) {   ?>
		<div class="above-content"> <a href="<?php echo SITEURL ?>role/role_create" class="pull-right"><span class="label label-danger">Add Role</span></a> </div>
	<?php  } ?>
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
								<th>Role</th>
								<th>Access</th>
								
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

<script>
	$(document).ready(function() {
		//FILL TABLE
		fillTable();

		//REMOVE ROLE
		$(document).on('click', '.remove_role', function(data, status) {
			var id = $(this).data('roleid');

			$.confirm({
				title: 'Confirm!',
				content: 'You want to delete the role?',
				buttons: {
					confirm: function() {
						$('.spinner').removeClass('hide');
						$.post('<?php echo SITEURL ?>ajax/ajss-role', {
							id: id,
							action: 'delete_role'
						}, function(data, status) {
							if (status == 'success') {
								fillTable();
								displayAjaxMsg(data.msg, data.code);
							} else {
								displayAjaxMsg(data.msg, data.code);
							}
						}, 'json');
					},
					cancel: function() {}
				}
			});
		});

		// $(document).on('click', '.remove_role', function(data, status) {
		// 	var id = $(this).data('roleid');

		// 	swal({
		// 			title: "Are you sure?",
		// 			text: "You want to delete the role",
		// 			type: "warning",
		// 			showCancelButton: true,
		// 			confirmButtonColor: "#72c02c",
		// 			confirmButtonText: "Yes, Delete It",
		// 			cancelButtonText: "No, Cancel It",
		// 			closeOnConfirm: true,
		// 			closeOnCancel: true
		// 		},
		// 		function(isConfirm) {
		// 			if (isConfirm) {
		// 				$('.spinner').removeClass('hide');
		// 				$.post('<?php echo SITEURL ?>ajax/ajss-role', {
		// 					id: id,
		// 					action: 'delete_role'
		// 				}, function(data, status) {
		// 					if (status == 'success') {
		// 						fillTable();
		// 						displayAjaxMsg(data.msg, data.code);
		// 					} else {
		// 						displayAjaxMsg(data.msg, data.code);
		// 					}
		// 				}, 'json');
		// 			}
		// 		});

		// });
	});


	// $(document).on('click','.remove_role',function(data,status){
	// 	if(confirm('Do you want to delete role?')){
	// 		$('.spinner').removeClass('hide');

	// 		var id = $(this).data('roleid');

	// 		$.post('<?php echo SITEURL ?>ajax/ajss-role',{id:id,action:'delete_role'},function(data,status){
	// 			if(status == 'success'){
	// 				fillTable();
	// 				displayAjaxMsg(data.msg,data.code);
	// 				setTimeout(function() {
	// 	              $(".ajaxMsg").hide();
	// 	          }, 8000);
	// 			}else{
	// 				displayAjaxMsg(data.msg,data.code);
	// 				setTimeout(function() {
	// 	              $(".ajaxMsg").hide();
	// 	          }, 8000);
	// 			}
	// 		},'json');
	// 	}
	// });


	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-role?action=list_role',
			Processing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [
				{
					'data': 'id'
				},
				{
					'data': 'role',
					searchable: true,
					orderable: true
				},
				{
					'data': 'access',
					searchable: true,
					orderable: true
				},

			],
			"order": [
				[0, "desc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
						var btnLinks = '';
						<?php if (in_array("su_role_edit", $_SESSION['login_user_permissions'])) { ?>
							btnLinks = "<a href='<?php echo SITEURL ?>role/role_edit?id=" + row['id'] + "' title='Edit' class='text-primary action_link overlay_link'>Edit</a>";

						<?php } ?>
						<?php if (in_array("su_role_delete", $_SESSION['login_user_permissions'])) { ?>
							if(row['is_use']!=1){
								btnLinks += "<a href='javascript:void(0)' data-roleid = " + row['id'] + " title='Delete' class = 'text-danger remove_role action_link'>Delete</a>";
							}
							<?php } ?>

						return btnLinks;
					},
					"targets": 3
				},
				{
					"visible": false,
					"targets": [0]
				}
			]
		});
	}

	/*<a href='javascript:void(0)' data-homeworkid = " + row['user_id'] + " class = 'text-danger remove_homework action_link'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php" ?>