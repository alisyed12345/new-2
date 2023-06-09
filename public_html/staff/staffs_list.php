<?php
$mob_title = "List Staff";
include "../header.php";

if (!in_array("su_staff_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	exit;
}

?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>List All Staff</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
			<li class="active">List All Staff</li>
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
								<th>User ID</th>
								<th>Staff Name</th>
								<th>Email</th>
								<th>Mobile Number</th>
								<!-- <th>Type</th> -->
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
	<div class="modal-dialog" style="width:900px;">
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



<!-- Add Modal - Forced Login Admin URL-->
<div class="modal" id="parent_login">
	<div class="modal-dialog">
		<div class="modal-content">

			<!-- Modal Header -->
			<div class="modal-header">
				<h4 class="modal-title">Staff Login URL </h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<!-- Modal body -->
			<div class="modal-body">
				<input type="text" id="parentloginurl" class="form-control js-copytextarea" readonly>
				<br>
				<button class="js-textareacopybtn" style="vertical-align:top;">Copy URL</button>
			</div>

			<!-- Modal footer -->
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>

		</div>
	</div>
</div>
<!-- /Add modal -->
<script>
	$(document).ready(function() {
		//FILL TABLE
		fillTable();



		//TO SELECT FORCED ADMIN LOGIN URL
		var copyTextareaBtn = document.querySelector('.js-textareacopybtn');
		copyTextareaBtn.addEventListener('click', function(event) {
			var copyTextarea = document.querySelector('.js-copytextarea');
			copyTextarea.focus();
			copyTextarea.select();
			var successful = document.execCommand('copy');
		});

		$(document).on('click', '.parentforcelogin', function() {
			var loginurl = $(this).data('loginurl');
			$('#parentloginurl').val(loginurl);
			$('#parent_login').modal('show');
		});


		//FETCH STAFF DETAILS
		$(document).on('click', '.viewdetail', function() {
			var userid = $(this).data('userid');
			var staffname = $(this).data('staffname');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff';

			$('#staffinfo_title').html(' - ' + staffname);
			$('#staff_detail').html('<h5>Data loading... Please wait</h5>');
			$('#modal_staff_detail').modal('show');

			$.post(targetUrl, {
				userid: userid,
				action: 'view_staff_detail'
			}, function(data, status) {
				if (status == 'success') {
					$('#staff_detail').html(data);
				}
			});
		});



		//SEND LOGIN INFO TO STAFF
		$(document).on('click', '.sendlogininfo', function() {
			$this = $(this);
			$this.find('i').removeClass('icon-key');
			$this.find('i').addClass('icon-spinner9 spinner spinner-orig');


			var staffid = $this.data('staffid');
			$('.bartext' + staffid).html('<span style="color:black;">Processing...</span>');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff';

			$.post(targetUrl, {
				staffid: staffid,
				action: 'email_login_info_to_staff'
			}, function(data, status) {
				if (status == 'success') {
					$('.bartext' + staffid).html('Send Login Info');
					displayAjaxMsg(data.msg, data.code);
				} else {
					$('.bartext' + staffid).html('Send Login Info');
					displayAjaxMsg('Login information sending process failed');
				}

				$this.find('i').removeClass('icon-spinner9 spinner spinner-orig hide');
				$this.find('i').addClass('icon-key');
			}, 'json');
		});


		//REMOVE STAFF
		$(document).on('click', '.remove_staff', function(data, status) {
			if (confirm('Do you want to delete staff?')) {
				$('.spinner').removeClass('hide');

				var user_id = $(this).data('staffid');

				$.post('<?php echo SITEURL ?>ajax/ajss-staff', {
					user_id: user_id,
					action: 'delete_staff'
				}, function(data, status) {
					if (status == 'success') {
						fillTable();
						displayAjaxMsg(data.msg, data.code);
					} else {
						displayAjaxMsg(data.msg, data.code);
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
			ajax: '<?php echo SITEURL ?>ajax/ajss-staff?action=list_all_staff',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'user_id'
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
				// { 'data': 'user_type',searchable: true,orderable: true },
				{
					'data': 'status',
					searchable: true,
					orderable: true
				}
			],
			"order": [
				[1, "asc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
						var btn = '';
						<?php if (in_array("su_staff_view", $_SESSION['login_user_permissions'])) { ?>
							btn += "<a href='javascript:;' class='text-warning action_link viewdetail' data-staffname='" + row['staff_name'] + "' data-userid='" + row['user_id'] + "'>View</a>";
						<?php }
						?>
						<?php if (in_array("su_staff_edit", $_SESSION['login_user_permissions'])) { ?>
							if (row['is_deleted'] == 0) {
								btn += "<a href='<?php echo SITEURL ?>staff/staff_edit?id=" + row['user_id'] + "' class='text-primary action_link overlay_link'>Edit</a>";
							}
						<?php } ?>

						<?php if (in_array("su_staff_send_login_info", $_SESSION['login_user_permissions'])) { ?>
							if (row['is_deleted'] == 0 && row['is_active'] == 1) {
								btn += "<a href='javascript:;' class='text-success action_link sendlogininfo  bartext" + row['user_id'] + "' title='Send Login Info' data-staffid='" + row['user_id'] + "'>Send Login Info</a> ";
							}
						<?php } ?>

						<?php  //if(in_array("su_staff_assign_role", $_SESSION['login_user_permissions'])){ 
						?>
						// 	if(row['status'] != 'Deleted'){

						// btn += "<a href='<?php echo SITEURL ?>role/user_role_parmissions_create?userid=" + row['user_id'] + "' class='"+row['user_role_map']+" action_link overlay_link' title='Assign Role'>Assign Role</a>";
						// 		}
						<?php // } 
						?>

						<?php if (in_array("su_staff_login_url", $_SESSION['login_user_permissions'])) { ?>
							if (row['is_deleted'] == 0 && row['is_active'] == 1) {
								btn += "<a href='javascript:void(0)' class='text-primary action_link parentforcelogin' title='Login URL' data-loginurl='" + row['admin_forced_login'] + "'>Login URL</a>";
							}
						<?php } ?>


						return btn;

					},
					"targets": 5
				},
				{
					"visible": false,
					"targets": [0]
				}
			]
		});
	}

	/*<a href='javascript:void(0)' data-staffid = " + row['user_id'] + " class = 'text-danger remove_staff action_link'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php" ?>