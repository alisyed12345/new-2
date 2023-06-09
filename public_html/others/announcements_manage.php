<?php
include_once "../includes/config.php";
$mob_title = "Announcements";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_announcements_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	return;
}
?>

<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4 style="display:inline-block">Announcements</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">Announcements</li>
		</ul>
	</div>
	<?php if (in_array("su_announcements_create", $_SESSION['login_user_permissions'])) {   ?>
	<div class="above-content"> <a href="javascript:void(0)" id="linkAddAnnouncement" class="pull-right"><span class="label label-danger">Add Announcement</span></a> </div>
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
								<th>Announcement</th>
								<th>Display Order</th>
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

<!-- Add Modal -->
<div id="modal_add_announcement" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form name="frmAnnouncement" id="frmAnnouncement" class="form-validate-jquery" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Add Announcement</h5>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Announcement:<span class="mandatory">*</span></label>
								<input type="text" name="message" id="message" class="form-control required">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
							    <?php
								$count = $db->get_var("SELECT count(*) from ss_announcements where session  = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active=1 ");
								?>
								<input type="hidden" id="orderid" value="<?php echo $count ?>">
								<label>Display Order:<span class="mandatory">*</span></label>
								<select  name="display_order" id="display_order" class="form-control required">
									
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Status:<span class="mandatory">*</span></label>
								<select class="form-control required" name="is_active" id="is_active">
									<option value="">Select</option>
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
							<div id="statusMsg"></div>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-success" id="to_disable_button"><i class="icon-spinner2 spinner hide marR10 insidebtn" ></i> Submit</button>
							<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
							<input type="hidden" name="announcement_id" id="announcement_id">
							<input type="hidden" name="action" id="action" value="save_announcement">
						</div>
					</div>

				</div>
			</form>
		</div>
	</div>
</div>
<!-- /Add modal -->
<script>
	$(document).ready(function() {
		$('.daterange-basic').daterangepicker({
			applyClass: 'bg-slate-600',
			cancelClass: 'btn-default',
			minDate: moment()
		});

		//FILL TABLE
		fillTable();
		jQuery.validator.addMethod("Limit", function(value, element) {
			return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
		}, "Please enter a valid display limit");


		$('#modal_add_announcement').on('hide.bs.modal', function(e) {
			$('#statusMsg').html('');
			$('#frmAnnouncement').trigger('reset');
			var validator = $("#frmAnnouncement").validate();
			validator.resetForm();
		});

		$('#linkAddAnnouncement').click(function() {


			$('.modal-title').html('Add Announcement');
			$('#announcement_id').val('');
			$("#frmAnnouncement")[0].reset();
			$('#modal_add_announcement').modal('show');

			if($('#orderid').val() == 0){
				$('#display_order').html('<option value="">Select</option><option value="1">1</option>');
			}else{
				var orderid = (parseInt($('#orderid').val()) + parseInt(1));
				var data = '<option value="">Select</option>';
				for (let i = 1; i <= orderid; i++) {
					data += "<option value="+i+">"+i+"</option>";
					}
				$('#display_order').html(data);

			}
		});

		$(document).on('click', '.delete_announcement', function(data, status) {
			var announcement_id = $(this).data('announcementid');
			$.confirm({
				title: 'Confirm!',
				content: 'Do you want to delete announcement?',
				buttons: {
					confirm: function() {
						$('.spinner').removeClass('hide');
						$.post('<?php echo SITEURL ?>ajax/ajss-announcements', {
							announcement_id: announcement_id,
							action: 'delete_announcement'
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


		$(document).on('click', '.edit_announcement', function(data, status) {
			//displayAjaxMsg("Data loading, please wait",2);

			$('.modal-title').html('Edit Announcement');
			$('#modal_add_announcement').modal('show');

			if($('#orderid').val() == 0){
				$('#display_order').html('<option value="">Select</option><option value="1">1</option>');
			}else{
				var orderid = parseInt($('#orderid').val());
				var data = '<option value="">Select</option>';
				for (let i = 1; i <= orderid; i++) {
					data += "<option value="+i+">"+i+"</option>";
					}
				$('#display_order').html(data);

			}


			var announcement_id = $(this).data('announcementid');

			$.post('<?php echo SITEURL ?>ajax/ajss-announcements', {
				announcement_id: announcement_id,
				action: 'fetch_announcement'
			}, function(data, status) {
				if (status == 'success') {
					if (data.code == 1) {
						$('#is_active').val(data.is_active);
						$('#message').val(data.message);
						$('#display_order').val(data.display_order);
						$('#announcement_id').val(data.id);

						hideAjaxMsg();
					}
				}
			}, 'json');
		});

		$('#frmAnnouncement').submit(function(e) {
			
			e.preventDefault();
			if ($('#frmAnnouncement').valid()) {
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-announcements';
				$('#statusMsg').html('Processing...');
				$("#to_disable_button").prop('disabled', true);
				var formDate = $(this).serialize();
				$.post(targetUrl, formDate, function(data, status) {
					if (status == 'success') {
						$('#statusMsg').removeClass("text-success");
						$('#statusMsg').removeClass("text-danger");
						$('#statusMsg').html(data.msg);
						$("#to_disable_button").prop('disabled', false);
						if (data.code == 1) {
							$('#statusMsg').addClass("text-success");
							
							fillTable();
							if (($('#action').val() == 'save_announcement') && (!$('#announcement_id').val())) {
								$('#frmAnnouncement').trigger('reset');
								var orderids = (parseInt($('#orderid').val()) + parseInt(1));
								$('#orderid').val(orderids);
							}
							displayAjaxMsg(data.msg, data.code);
							$('#modal_add_announcement').modal('hide');

						} else {
							$('#statusMsg').addClass("text-danger");
							displayAjaxMsg(data.msg, data.code);
						}
					} else {
						displayAjaxMsg(data.msg);
					}
				}, 'json');
			}
		});

	});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			ajax: '<?php echo SITEURL ?>ajax/ajss-announcements?action=list_all_announcements',
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'message',
					searchable: true,
					orderable: true
				},
				{
					'data': 'display_order',
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
						var btnLinks = '';
						<?php if (in_array("su_announcements_edit", $_SESSION['login_user_permissions'])) { ?>
							btnLinks += "<a href='javascript:void(0)' data-announcementid = " + row['id'] + " title='Edit Announcement' class='text-primary action_link edit_announcement'>Edit</a>";
						<?php } ?>
						<?php if (in_array("su_announcements_delete", $_SESSION['login_user_permissions'])) { ?>
							btnLinks += "<a href='javascript:void(0)' data-announcementid = " + row['id'] + " title='Delete Announcement' class='text-danger action_link delete_announcement'>Delete</a>";
						<?php } ?>
						return btnLinks;

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