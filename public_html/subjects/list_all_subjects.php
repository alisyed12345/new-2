<?php
$mob_title = "Manage Subjects";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_class_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Manage Classes</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">Manage Classes</li>
		</ul>
	</div>
	<?php if (in_array("su_class_create", $_SESSION['login_user_permissions'])) {   ?>
		<div class="above-content"> <a href="javascript:void(0)" id="addsubject" class="pull-right"><span class="label label-danger"> Add Class</span></a> </div>
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
								<th></th>
								<th>Class Name</th>
								<th>Display Order</th>
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
<div id="modalsubject" class="modal fade">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form name="frmsubject" id="frmsubject" class="form-validate-jquery" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title headtext">Add Class</h5>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="subject_name">Class Name:<span class="mandatory">*</span></label>
								<input type="text" spacenotallow="true" class="form-control required" name="subject_name" id="subject_name" placeholder="Subject Name">
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label for="display_order">Display Order:<span class="mandatory">*</span></label>
								<select class="form-control required" name="display_order" id="display_order">
									<option value="">Select</option>
									<?php
									$i = 1;
									for ($i = 1; $i <= 20; $i++) {
									?>
										<option value="<?php echo $i ?>"><?php echo $i ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label for="status">Status:<span class="mandatory">*</span></label>
								<select class="form-control required" name="status" id="status">
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
							<input type="hidden" name="action" id="action" value="subjects_add">
							<input type="hidden" name="subject_id" id="subject_id" value="">
							<button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>

			</form>
		</div>
	</div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script>
	$(document).ready(function() {
		//FILL TABLE
		fillTable();

		jQuery.validator.addMethod("dollarsscents", function(value, element) {
			return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
		}, "Please enter a valid order");


		//REMOVE Permission
		$(document).on('click', '.remove_subject', function(data, status) {
			var id = $(this).data('id');
			$.confirm({
				title: 'Confirm!',
				content: 'Do you want to delete, class will be permanently delete?',
				buttons: {
					confirm: function() {
						$('.spinner').removeClass('hide');
						$.post('<?php echo SITEURL ?>ajax/ajss-subjects', {
							id: id,
							action: 'delete_subjects'
						}, function(data, status) {
							if (status == 'success') {
								displayAjaxMsg(data.msg, data.code);
								setTimeout(function() {
									$(".ajaxMsg").html("");
								}, 8000);
								fillTable();
							} else {
								displayAjaxMsg(data.msg, data.code);
								setTimeout(function() {
									$(".ajaxMsg").html("");
								}, 8000);
							}
						}, 'json');
					},
					cancel: function() {}
				}
			});
		});


		//Add Basic fees start
		$('#modalsubject').on('hide.bs.modal', function(e) {
			$('#statusMsg').html('');
			$('#frmsubject').trigger('reset');
			var validator = $("#frmsubject").validate();
			validator.resetForm();
		});

		$(document).on('click', '#addsubject', function() {
			$('.headtext').html("Add Class");
			$('#action').val("subjects_add");
			$('#modalsubject').modal('show');
		});


		$('#frmsubject').submit(function(e) {
			e.preventDefault();
			if ($('#frmsubject').valid()) {
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-subjects';
				$('#statusMsg').html('Processing...');

				var formDate = $(this).serialize();
				$.post(targetUrl, formDate, function(data, status) {
					if (status == 'success') {
						$('#statusMsg').html(data.msg);
						if (data.code == 1) {
							$('#modalsubject').modal('hide');
							fillTable();
							if ($('#action').val() == 'basic_fees_add') {
								$('#frmsubject').trigger('reset');
							}
							displayAjaxMsg(data.msg, data.code);
							setInterval(function() {
								$('#statusMsg').html('');
							}, 3000);
						} else {
							setTimeout(function() {
								displayAjaxMsg(data.msg, data.code);
								$('#statusMsg').html('');
							}, 3000);

						}
					} else {
						displayAjaxMsg(data.msg);
					}
				}, 'json');
			}
		});

		//Add Basic fees end


		//edit Basic fees start

		$('#modalsubject').on('hide.bs.modal', function(e) {
			$('#statusMsg').html('');
			$('#frmsubject').trigger('reset');
			var validator = $("#frmsubject").validate();
			validator.resetForm();
		});


		$(document).on('click', '.editsubjects', function() {
			$('.headtext').html("Edit Class");
			$('#subject_id').val($(this).data('id'));
			$('#subject_name').val($(this).data('subject_name'));
			$('#display_order').val($(this).data('display_order'));
			$('#status').val($(this).data('status'));
			$('#action').val("subjects_edit");
			$('#modalsubject').modal('show');
		});
		//edit Basic fees end
	});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-subjects?action=list_subjects',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'class_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'disp_order',
					searchable: true,
					orderable: true
				},
				{
					'data': 'status',
					searchable: true,
					orderable: true
				},
			],
			"order": [
				[1, "asc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
						var links = "";

						<?php if (in_array("su_class_edit", $_SESSION['login_user_permissions'])) { ?>
							links += "<a href='javascript:;'  data-id='" + row['id'] + "'  data-subject_name='" + row['class_name'] + "' data-display_order='" + row['disp_order'] + "' data-status='" + row['is_active'] + "'  title='Edit' class='text-primary action_link editsubjects'>Edit</a>";
						<?php } ?>

						<?php if (in_array("su_class_delete", $_SESSION['login_user_permissions'])) { ?>
							if (row['delete'] == '') {
								links += "<a href='javascript:void(0)' data-id = " + row['id'] + " title='Delete' class = 'text-danger remove_subject action_link'>Delete</a>";
							}
						<?php } ?>

						// return [links, attechment];
						return links;
					},
					"targets": 4
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