<?php
$mob_title = "List Permissions";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_permissions_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Permissions</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">List Permissions</li>
		</ul>
	</div>

  <?php if(in_array("su_permissions_create", $_SESSION['login_user_permissions'])){   ?>
  <div class="above-content"> <a href="<?php echo SITEURL ?>permissions/permission_create" class="pull-right"><span class="label label-danger">Create Permissions</span></a> </div>
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
								<th>Permission</th>
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

		//REMOVE Permission
		$(document).on('click', '.remove_permission', function(data, status) {
			var id = $(this).data('permissionid');
			$.confirm({
				title: 'Confirm!',
				content: 'Do you want to delete permission?',
				buttons: {
					confirm: function () {
						$('.spinner').removeClass('hide');
						$.post('<?php echo SITEURL ?>ajax/ajss-permission',{id:id,action:'delete_permission'},function(data,status){
						if(status == 'success'){
							fillTable();
							displayAjaxMsg(data.msg,data.code);
						
						}else{
							displayAjaxMsg(data.msg,data.code);
						
						}
						},'json');
					},
					cancel: function () {
					}
				}
			});
		});


	// 	$(document).on('click','.remove_permission',function(data,status){
	// 	if(confirm('Do you want to delete permission?')){
	// 		$('.spinner').removeClass('hide');

	// 		var id = $(this).data('permissionid');

	// 		$.post('<?php echo SITEURL ?>ajax/ajss-permission',{id:id,action:'delete_permission'},function(data,status){
	// 			if(status == 'success'){
	// 				fillTable();
	// 				displayAjaxMsg(data.msg,data.code);
				
	// 			}else{
	// 				displayAjaxMsg(data.msg,data.code);
				
	// 			}
	// 		},'json');
	// 	}
	// });

});
	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-permission?action=list_permission',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'permission',
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
                       <?php if(in_array("su_permissions_edit", $_SESSION['login_user_permissions'])){   ?>
						btnLinks = "<a href='<?php echo SITEURL ?>permissions/permission_edit?id=" + row['id'] + "' title='Edit' class='text-primary action_link overlay_link'>Edit</a>";
						<?php } ?>
                       <?php if(in_array("su_permissions_delete", $_SESSION['login_user_permissions'])){   ?>
						<?php if ($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01') { ?>
							btnLinks += "<a href='javascript:void(0)' data-permissionid = " + row['id'] + " title='Delete' class = 'text-danger remove_permission action_link'>Delete</a>";
						<?php } ?>
						<?php } ?>

						return btnLinks;
					},
					"targets": 2
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