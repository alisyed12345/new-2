<?php
$mob_title = "Panding List Staff";
include "../header.php";

if (!in_array("su_staff_pending_list", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    exit;
}

?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Staff Pending List</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
			<li class="active">Staff Pending List</li>
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
					<div class="ajaxMsg"></div>
					<table class="table datatable-basic table-bordered">
						<thead>
							<tr>
								<th>Req No.</th>
								<th>Staff Name</th>
								<th>Email</th>
								<th>Mobile Number</th>
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
<!-- Add Modal - Staff Detail-->
<div id="modal_staff_detail" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title">Staff Detail <span id="staffinfo_title"></span></h5>
			</div>
			<div class="modal-body viewonly" id="staff_detail"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- /Add modal -->

<!-- Add Modal - Assign Group-->
<div id="modal_assign_group" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form name="frmAssignGroup" id="frmAssignGroup" class="form-validate-jquery" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Assign Group To <span id="modal_title_staffname"></span></h5>
				</div>
				<div class="modal-body">
					<?php
$get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
if ($get_general_info == 1) {
    $groups = $db->get_results("select * from ss_groups where (is_active=1 or is_active=2) and is_deleted=0 ORDER BY id ASC LIMIT 1");
    ?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group multi-select-full">
									<select id="group_id" name="group_id" class="form-control" required style="width:100%">
										<?php foreach ($groups as $grp) {?>
											<option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
										<?php }?>
									</select>
								</div>
							</div>
						</div>
					<?php }?>
					<?php if ($get_general_info == 0) {
    $groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0");
    $classes = $db->get_results("select * from ss_classes where is_active=1");
    ?>
						<div class="row">
							<?php foreach ($classes as $key => $class) {?>
								<div class="col-md-3" style="margin-bottom:15px;">
									<div class="form-group">
										<input type="hidden" name="class[]" value="<?php echo $class->id ?>">
										<span><?php echo $class->class_name; ?></span>
									</div>
								</div>

								<div class="col-md-3" style="margin-bottom:15px;">
									<div class="form-group multi-select-full">
										<select name="group_id<?php echo $class->id ?>" id="group_id<?php echo $class->id ?>" class="form-control required" required>
											<option value="" selected="">Select</option>
											<?php foreach ($groups as $grp) {?>
												<option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
											<?php }?>
										</select>
									</div>
								</div>
							<?php }?>
						</div>
					<?php }?>

				</div>
				<div class="modal-footer">
					<div class="ajaxMsgBot"></div>
					<button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Assign</button>
					<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
					<input type="hidden" name="user_id" id="assign_gr_user_id">
					<input type="hidden" name="action" value="assign_new_group_to_staff">
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /Add modal -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

<script>
	$(document).ready(function() {
		//FILL TABLE
		fillTable();

		//FETCH STAFF DETAILS
		$(document).on('click', '.viewdetail', function() {
			var id = $(this).data('id');
			var staffname = $(this).data('staffname');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff-registration';

			$('#staffinfo_title').html(' - ' + staffname);
			$('#staff_detail').html('<h5>Data loading... Please wait</h5>');
			$('#modal_staff_detail').modal('show');

			$.post(targetUrl, {
				id: id,
				action: 'view_staff_detail'
			}, function(data, status) {
				if (status == 'success') {
					$('#staff_detail').html(data);
				}
			});
		});



		$('#frmAssignGroup').submit(function(e) {
			e.preventDefault();
			if ($('#frmAssignGroup').valid()) {
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff-registration';
				$('.spinner').removeClass('hide');
				e.preventDefault();

				var formDate = $(this).serialize();
				$.post(targetUrl, formDate, function(data, status) {
					if (status == 'success') {
						if (data.code == 1) {
							displayAjaxMsg(data.msg, data.code);
							fillTable();
						} else {
							displayAjaxMsg(data.msg, data.code);
						}
					} else {
						displayAjaxMsg(data.msg);
					}
				}, 'json');
			}
		});

		$(document).on('click', '.assigngroup', function() {
			$('#assign_gr_user_id').val($(this).data('id'));

			$('#modal_title_staffname').html($(this).data('staffname'));
			$('#modal_assign_group').modal('show');
		});


		$('#modal_assign_group').on('show.bs.modal', function(e) {
			$('#frmAssignGroup').trigger('reset');
			$('.select').change();
			var validator = $("#frmAssignGroup").validate();
			validator.resetForm();
		});

		$(document).on('click', '.remove_staff', function(data, status) {
			    var id = $(this).data('id');
				$.confirm({
					title: 'Confirm!',
					content: "Do you want to delete this Staff Registration Request?. You won't be able to recover it.",
					buttons: {
						confirm: function () {
						$(this).find('.spinnerDeletestaff').removeClass('hide');

						$.post('<?php echo SITEURL ?>ajax/ajss-staff-registration', {
						id: id,
						action: 'remove_staff'
						}, function(data, status) {
						$(this).find('.spinnerDeletestaff').addClass('hide');
						if (status == 'success') {
						displayAjaxMsg(data.msg, data.code);
						// setTimeout(function() {
						// $(".ajaxMsg").html("");
						// }, 3000);
						fillTable();
						} else {
						displayAjaxMsg(data.msg, data.code);
						// setTimeout(function() {
						// $(".ajaxMsg").html("");
						// }, 3000);
						}
						}, 'json');
                },
                	cancel: function () {
                }
            }
            })
			// if (confirm('Do you want to delete staff?')) {

			// }
		});


		//ADD STAFF
		// $(document).on('click', '.addstaff', function(data, status) {
		// 	if (confirm('Do you want to add staff?')) {
		// 		$(this).find('.spinnerAddstaff').removeClass('hide');
		// 		var id = $(this).data('id');
		// 		$.post('<?php echo SITEURL ?>ajax/ajss-staff-registration', {
		// 			id: id,
		// 			action: 'add_new_staff'
		// 		}, function(data, status) {
		// 			$(this).find('.spinnerAddstaff').addClass('hide');
		// 			if (status == 'success') {
		// 				displayAjaxMsg(data.msg, data.code);
		// 				setTimeout(function() {
		// 					$(".ajaxMsg").html("");
		// 				}, 3000);
		// 				fillTable();
		// 			} else {
		// 				displayAjaxMsg(data.msg, data.code);
		// 				setTimeout(function() {
		// 					$(".ajaxMsg").html("");
		// 				},3000);
		// 			}
		// 		}, 'json');
		// 	}
		// });

		$(document).on('click', '.addstaff', function(data, status) {
			    var id = $(this).data('id');
				$.confirm({
					title: 'Confirm!',
					content: 'Do you want to add staff?',
					buttons: {
						confirm: function () {
						$(this).find('.spinnerDeletestaff').removeClass('hide');
						$.post('<?php echo SITEURL ?>ajax/ajss-staff-registration', {
						id: id,
					    action: 'add_new_staff'
						}, function(data, status) {
						$(this).find('.spinnerAddstaff').addClass('hide');
						if (status == 'success') {
						displayAjaxMsg(data.msg, data.code);
						// setTimeout(function() {
						// $(".ajaxMsg").html("");
						// }, 3000);
						fillTable();
						} else {
						displayAjaxMsg(data.msg, data.code);
						// setTimeout(function() {
						// $(".ajaxMsg").html("");
						// }, 3000);
						}
						}, 'json');
                },
                	cancel: function () {
                }
            }
            })
			// if (confirm('Do you want to delete staff?')) {

			// }
		});





		//UPDATE STAFF
		$(document).on('click', '.updatestaff', function(data, status) {
			    var id = $(this).data('id');
				$.confirm({
					title: 'Confirm!',
					content: 'Do you want to add staff?',
					buttons: {
						confirm: function () {
						$(this).find('.spinnerDeletestaff').removeClass('hide');

						$.post('<?php echo SITEURL ?>ajax/ajss-staff-registration', {
						id: id,
					    action: 'update_staff'
						}, function(data, status) {
						$(this).find('.spinnerAddstaff').addClass('hide');
						if (status == 'success') {
						displayAjaxMsg(data.msg, data.code);
						setTimeout(function() {
						$(".ajaxMsg").html("");
						}, 3000);
						fillTable();
						} else {
						displayAjaxMsg(data.msg, data.code);
						setTimeout(function() {
						$(".ajaxMsg").html("");
						}, 3000);
						}
						}, 'json');
                },
                	cancel: function () {
                }
            }
            })
		});


	});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-staff-registration?action=list_all_staff_register',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'id',
					searchable: true,
					orderable: true
				},
				{
					'data': 'staff_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'email',
					searchable: true,
					orderable: true
				},
				{
					'data': 'mobile',
					searchable: true,
					orderable: true
				},
				{
					'data': 'status',
					searchable: true,
					orderable: true
				}
			],
			"order": [
				[0, "desc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
						var btn = '';
						<?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){
if (check_userrole_by_code('UT01')) {?>

							//  <?php if (in_array("su_staff_edit", $_SESSION['login_user_permissions'])) {?>
							// btn += "<a href='#' class='text-warning action_link viewdetail' data-staffname='" + row['staff_name'] + "' data-userid='" + row['user_id'] + "'>View</a><a href='<?php echo SITEURL ?>staff/staff_edit?id=" + row['user_id'] + "' class='text-primary action_link overlay_link'>Edit</a>";
							// <?php }?>
							// btn +="<a href='#' class='text-success action_link sendlogininfo  bartext"+row['user_id']+"' title='Send Login Info' data-staffid='" + row['user_id'] + "'>Send Login Info</a> ";

							// <?php //if (in_array("su_staff_assign_role", $_SESSION['login_user_permissions'])) {?>
							// 	if(row['status'] != 'Deleted'){

							// btn += "<a href='#' class='text-primary action_link assigngroup' data-staffname='" + row['staff_name'] + "' data-id='" + row['id'] + "' title='Assign Group'>Assign Group</a>";
							btn += "<a href='javascript:void(0)' class='text-primary action_link viewdetail' data-staffname='" + row['staff_name'] + "' data-id='" + row['id'] + "'>View</a>";

							btn += "<a href='javascript:void(0)' data-id = " + row['id'] + " title='Delete' class = 'text-danger remove_staff action_link'>Delete <span class='icon-spinner2 spinner insidebtn hide spinnerDeletestaff'></span></a>";
							//         }
							// <?php //}?>

							if (row['staff_email'] == null || row['staff_email'] == "") {
								btn += "<a href='javascript:void(0)' class='text-primary action_link addstaff ' data-id='" + row['id'] + "'>Add <span class='icon-spinner2 spinner insidebtn hide spinnerAddstaff'></span></a>";
							} else {
								btn += "<a href='javascript:void(0)' class='text-primary action_link updatestaff' data-id='" + row['id'] + "'>Add <span class='icon-spinner2 spinner insidebtn hide spinnerAddstaff'></span></a>";

							}




							// <?php } elseif (check_userrole_by_code('UT04')) {?>
							//  <?php if (in_array("su_staff_list", $_SESSION['login_user_permissions'])) {?>

							// <?php }
}?>

							return btn;

					},
					"targets": 5
				},
				{
					"visible": true,
					"targets": [0]
				}
			]
		});
	}

	/*<a href='javascript:void(0)' data-staffid = " + row['user_id'] + " class = 'text-danger remove_staff action_link'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php"?>