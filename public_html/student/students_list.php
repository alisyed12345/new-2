<?php
$mob_title = "List Student";
include "../header.php";

if (!in_array("su_student_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	exit;
}

?>
<style>

</style>
<!-- Page header -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.10/sweetalert2.css" />
<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4 style="display:inline-block">Student</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">Student</li>
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
					<div class="row">
						<?php if (check_userrole_by_code('UT01')) { ?>
							<div class="col-md-10">
							</div>
							<div class="col-md-2">
								<input type="checkbox" name="show_deleted_student" id="deleted_record" value="show_deleted_stu" class="record" style="margin-left: 20px;margin-top: 3px;"> <span style="margin-left: 5px;margin-top: 3px;">Show Deleted Student</span>
							</div>
						<?php } ?>
					</div>
					<div class="row" style="margin-top: 10px;">
						<div class="col-lg-12">
							<table class="table table-bordered dataGrid">
								<thead>
									<tr>
										<th>User ID</th>
										<th>Student</th>
										<th>Gender</th>
										<th>Age (Yrs)</th>
										<th>Allergies</th>
										<th>1st Parent Phone</th>
										<th>1st Parent Name</th>
										<th>Status</th>
										<th class="action_col text-center"></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
							<!-- COMMENTED ON 24AUG2021 FOR ICK
				<span class="label label-success">Q</span> shows that this student is also registered in Quran School -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Add Modal - Assign Group-->
<div id="modal_assign_group" class="modal fade">
	<div class="modal-dialog" style="width: 500px !important;">
		<div class="modal-content">
			<form name="frmAssignGroup" id="frmAssignGroup" class="form-validate-jquery" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Assign Group To <span id="modal_title_studentname"></span></h5>
				</div>
				<div class="modal-body">
					<?php
					$get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
					if ($get_general_info == 1) {
						$groups = $db->get_results("select * from ss_groups where (is_active=1 or is_active=2) and is_deleted=0 
					 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY id ASC LIMIT 1");
					?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group multi-select-full">
									<select id="group_id" name="group_id" class="form-control" required style="width:100%">
										<?php foreach ($groups as $grp) { ?>
											<option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if ($get_general_info == 0) {
						$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
						$classes = $db->get_results("select * from ss_classes where is_active=1 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
					?>
						<div class="row">
							<?php foreach ($classes as $key => $class) { ?>
								<div class="col-md-6">
									<div class="form-group">
										<input type="hidden" name="class[]" value="<?php echo $class->id ?>">
										<span><?php echo $class->class_name; ?></span>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group multi-select-full">
										<select name="group_id<?php echo $class->id ?>" id="group_id<?php echo $class->id ?>" class="form-control required">
											<option value="" selected="">Select</option>
											<?php foreach ($groups as $grp) { ?>
												<option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php } ?>

				</div>
				<div class="modal-footer">
					<div class="ajaxMsgBot"></div>
					<button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Assign</button>
					<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
					<input type="hidden" name="user_id" id="assign_gr_user_id">
					<input type="hidden" name="action" value="assign_new_group_to_student">
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /Add modal -->


<!-- View Student Group And Class -->
<div class="modal" id="model_view_group">
	<div class="modal-dialog" style="width: 400px;">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="stu_title"></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body" id="student_groupdetail">
			</div>
		</div>
	</div>
</div>



<!-- Add Modal - Student Detail-->
<div id="modal_student_detail" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title">Student Detail <span id="studentinfo_title"></span></h5>
			</div>
			<div class="modal-body viewonly" id="student_detail"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- /Add modal -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.10/sweetalert2.js" integrity="sha512-mLrZ/I45W7yBc/QFrxW04Aj8Ly5T51AbqNk0buPhsslnMhb+oexiGE1UMuR4XFGQ2KkPazCWA9Cw/jwtkAd+aA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>


<script>
	$(document).ready(function() {

		var is_delete_checked = $.cookie("isDeletedChecked");
		//FILL TABLE
		jQuery.extend(jQuery.fn.dataTableExt.oSort, {
			"formatted-num-pre": function(a) {
				a = (a === "-" || a === "") ? 0 : a.replace(/[^\d\-\.]/g, "");
				return parseFloat(a);
			},

			"formatted-num-asc": function(a, b) {
				return a - b;
			},

			"formatted-num-desc": function(a, b) {
				return b - a;
			}
		});
		if (is_delete_checked) {
			$('#deleted_record').prop('checked', true);
			$deleted_record = $('#deleted_record').val();
			fillTable($deleted_record);
		} else {
			fillTable();
		}



		//VIEW STUDENTS GROUPS
		$(document).on('click', '.stugroups', function() {
			var stuid = $(this).data('stuid');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
			$('#stu_title').html($(this).data('stuname'));
			$('#student_groupdetail').html('<h5>Data loading... Please wait</h5>');
			$('#model_view_group').modal('show');
			$.post(targetUrl, {
				stuid: stuid,
				action: 'view_student_groupclass'
			}, function(data, status) {
				if (status == 'success') {

					var datagroup = '<div class="row"> <div class="col-md-6"><span><b>Class</b></span></div><div class="col-md-6"><span><b>Group</b></span></div></div><div class="row">';
					$.each(data.groups, function(key, value) {
						datagroup = datagroup + '<div class="col-md-6"> <div class="form-group"><span></span>' + value.class_name + '</div></div><div class="col-md-6"> <div class="form-group"><span>' + value.group_name + '</span></div></div>';
					});
					datagroup = datagroup + '</div>';

					$('#student_groupdetail').html(datagroup);
				}
			}, 'json');
		});



		//FETCH STUDENT DETAILS
		$(document).on('click', '.viewdetail', function() {
			var userid = $(this).data('userid');
			var studentname = $(this).data('studentname');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';

			$('#studentinfo_title').html(' - ' + studentname);
			$('#student_detail').html('<h5>Data loading... Please wait</h5>');
			$('#modal_student_detail').modal('show');

			$.post(targetUrl, {
				userid: userid,
				action: 'view_student_detail'
			}, function(data, status) {
				if (status == 'success') {
					$('#student_detail').html(data);
				}
			});
		});

		$('#frmAssignGroup').submit(function(e) {
			e.preventDefault();

			if ($('#frmAssignGroup').valid()) {
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
				$('.spinner').removeClass('hide');

				var formDate = $(this).serialize();
				$.post(targetUrl, formDate, function(data, status) {
					if (status == 'success') {
						if (data.code == 1) {
							displayAjaxMsg(data.msg, data.code);
							fillTable();
							setTimeout(function() {
								$('.ajaxMsgBot').html(' ');
								$('#modal_assign_group').modal('hide');
							}, 1500);
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
			$('#assign_gr_user_id').val($(this).data('userid'));
			$('#modal_title_studentname').html($(this).data('studentname'));
			$('#modal_assign_group').modal('show');
			var groupClassArray = $(this).data('stugroups');

			<?php if ($get_general_info == 0) { ?>

				var trainindIdArray = groupClassArray.split(',');
				$.each(trainindIdArray, function(index, value) {
					var grpClasss = value.split('_');
					$('#group_id' + grpClasss[0]).val(grpClasss[1]);
				});

			<?php } else { ?>

				var grpClasss = groupClassArray.split('_');
				$('#group_id').val(grpClasss[1]);

			<?php } ?>
		});

		$('#modal_assign_group').on('show.bs.modal', function(e) {
			$('#frmAssignGroup').trigger('reset');
			$('.select').change();
			var validator = $("#frmAssignGroup").validate();
			validator.resetForm();
		});

		$('#deleted_record').on('change', function() {

			if ($('.record').is(":checked")) {
				$.cookie('isDeletedChecked', 1);
				$deleted_record = $(this).val();
				fillTable($deleted_record);
			} else {
				$.removeCookie('isDeletedChecked');
				fillTable();
			}
		});


	});


	function fillTable($deleted_record) {
		var last_page = document.referrer;
		var last_segment = last_page.substring(last_page.lastIndexOf('/') + 1);
		if (last_segment !== 'students_list' || last_segment !== 'students_list#') {
			$.removeCookie('isDeletedChecked');
		}
		var table = $('.dataGrid').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			//oSearch: {"sSearch": "<?php //echo $_GET['group'] 
									?>"},
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-student?action=list_all_student&deleted_record_student=' + $deleted_record,
			'columns': [{
					'data': 'user_id'
				},
				{
					'data': 'student_name',
					searchable: true,
					orderable: true,
					responsivePriority: 1
				},
				{
					'data': 'gender',
					"width": "8%",
					searchable: true,
					orderable: true
				},
				{
					'data': 'dob',
					"width": "6%",
					searchable: true,
					orderable: true
				},
				{
					'data': 'allergies',
					"width": "10%",
					searchable: true,
					orderable: true
				},
				{
					'data': 'primary_phone',
					searchable: true,
					orderable: true
				},
				{
					'data': 'father_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'status',
					"width": "8%",
					searchable: true,
					orderable: true,
					responsivePriority: 1
				},
			],
			//"order": [[ 1, "asc" ]],
			"order": [
				[1, "asc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {

						var action_link = '';
						if (row['class'] == 1) {
							var classes = "<span class='' title='Check it group and classes' style='color:red'>*</span>";
						} else {
							var classes = "";
						}
						<?php
						//if (check_userrole_by_code('UT01') || check_userrole_by_code('UT02')) {
						?>
						<?php if (in_array("su_student_list", $_SESSION['login_user_permissions'])) { ?>
							action_link = "<a href='#' class='text-warning action_link viewdetail' data-studentname='" + row['student_name'] + "' data-userid='" + row['user_id'] + "' title='Readonly View'>View </a>";
						<?php }  ?>
						<?php if (in_array("su_group_assign", $_SESSION['login_user_permissions'])) { ?>
							if (row['is_deleted'] == 0) {
								action_link = action_link + "<a href='#' class='text-primary action_link assigngroup' data-studentname='" + row['student_name'] + "' data-userid='" + row['user_id'] + "' data-stugroups='" + row['stugroups'] + "' title='Assign Group'>Assign Group</a>";
							}
						<?php }
						//} 
						?>
						<?php 	/* if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01' && check_userrole_by_group('admin')){
						if (check_userrole_by_code('UT01') || check_userrole_by_group('admin')) {	 ?>
						action_link = action_link + "<a href='<?php echo SITEURL ?>student/payment_cred_edit?id=" + row['user_id'] + "' class='text-info action_link overlay_link' title='Edit Payment Credentials'>Edit Payment Credentials</a>";
						<?php } */ ?>
						<?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 

						//if (check_userrole_by_code('UT01')) { 
						?>
						<?php if (in_array("su_student_edit", $_SESSION['login_user_permissions'])) { ?>
							action_link = action_link + "<a href='<?php echo SITEURL ?>student/student_edit?id=" + row['user_id'] + "' class='text-primary action_link overlay_link' title='Edit Student Info'>Edit " + classes + "</a>";
						<?php } ?>

						<?php if (in_array("su_family_edit", $_SESSION['login_user_permissions'])) { ?>
							var is_checked_dele = $('input[name="show_deleted_student"]:checked').val();
							if (is_checked_dele !== 'show_deleted_stu') {
								action_link = action_link + "<a href='<?php echo SITEURL ?>student/family_edit?id=" + row['user_id'] + "&from=student ' class='text-success action_link overlay_link' title='Edit Family Info'>Edit Family Info</a>";
							}
						<?php }
						//} 
						?>

						<?php //if(check_userrole_by_code('UT04')){ 
						if (check_userrole_by_code('UT04')) { ?>
							action_link = action_link + "<a href='#' class='text-warning action_link viewdetail' data-studentname='" + row['student_name'] + "' data-userid='" + row['user_id'] + "'>View</a><a href='<?php echo SITEURL ?>student/payment_cred_edit?id=" + row['user_id'] + "' class='text-info action_link overlay_link' title='Edit Payment Credentials'>Edit Payment Credentials</a>";
						<?php } ?>

						<?php /* if (check_userrole_by_code('UT01')) { ?>
							   if(row['status'] != 'Hold'){
							   action_link = action_link + "<a href='<?php echo SITEURL ?>payment/schedule_payment?id=" + row['user_id'] + "' class='text-warning action_link'>Schedule Payment</a>";
							   }
						<?php }  */?>

						return action_link;
					},
					"targets": 8,
					responsivePriority: 999999,
					hidden: true
				},
				{
					type: 'formatted-num',
					targets: 3
				},
				{
					"visible": false,
					"targets": [0]
				}
			]
		});

		<?php if (isset($_GET['group'])) { ?>
			table.columns(7).search('<?php echo $_GET['group'] ?>').draw();
		<?php } ?>
	}

	$('#deletedRE').change(function() {
		var isDelete = document.getElementById("deletedRE").checked;
		if (isDelete) {

			var table = $('.dataGrid').DataTable({
				autoWidth: false,
				destroy: true,
				pageLength: <?php echo TABLE_LIST_SHOW ?>,
				//oSearch: {"sSearch": "<?php //echo $_GET['group'] 
										?>"},
				sProcessing: '',
				language: {
					loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
				},
				responsive: true,
				ajax: '<?php echo SITEURL ?>ajax/ajss-student?action=list_all_student&status=get_deleted',
				'columns': [{
						'data': 'user_id'
					},
					{
						'data': 'student_name',
						searchable: true,
						orderable: true,
						responsivePriority: 1
					},
					{
						'data': 'gender',
						"width": "8%",
						searchable: true,
						orderable: true
					},
					{
						'data': 'dob',
						"width": "6%",
						searchable: true,
						orderable: true
					},
					{
						'data': 'allergies',
						"width": "10%",
						searchable: true,
						orderable: true
					},
					{
						'data': 'primary_phone',
						searchable: true,
						orderable: true
					},
					{
						'data': 'father_name',
						searchable: true,
						orderable: true
					},
					{
						'data': 'status',
						"width": "8%",
						searchable: true,
						orderable: true,
						responsivePriority: 1
					},
				],
				"order": [
					[1, "asc"]
				],
				"columnDefs": [{
						"render": function(data, type, row) {
							var action_link = '';
							<?php if (check_userrole_by_code('UT01')) { ?>
								<?php if (in_array("su_student_delete", $_SESSION['login_user_permissions'])) { ?>
									action_link = action_link + "<a href='javascript:void(0);'  data-userid='" + row['user_id'] + "' class='text-primary action_link restore_student' title='Restore Student'>Restore Student</a>";
							<?php }
							} ?>


							return action_link;
						},
						"targets": 8,
						responsivePriority: 999999,
						hidden: true
					},
					{
						type: 'formatted-num',
						targets: 3
					},
					{
						"visible": false,
						"targets": [0]
					}
				]
			});
		} else {
			fillTable();
		}
	})


	function deleted_records() {
		var table = $('.dataGrid').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			//oSearch: {"sSearch": "<?php //echo $_GET['group'] 
									?>"},
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-student?action=list_all_student&status=get_deleted',
			'columns': [{
					'data': 'user_id'
				},
				{
					'data': 'student_name',
					searchable: true,
					orderable: true,
					responsivePriority: 1
				},
				{
					'data': 'gender',
					searchable: true,
					orderable: true
				},
				{
					'data': 'dob',
					searchable: true,
					orderable: true
				},
				{
					'data': 'allergies',
					searchable: true,
					orderable: true
				},
				{
					'data': 'primary_phone',
					searchable: true,
					orderable: true
				},
				{
					'data': 'father_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'status',
					searchable: true,
					orderable: true,
					responsivePriority: 1
				},
			],
			"order": [
				[1, "asc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
						var action_link = '';
						<?php if (check_userrole_by_code('UT01')) { ?>
							<?php if (in_array("su_student_delete", $_SESSION['login_user_permissions'])) { ?>
								action_link = action_link + "<a href='javascript:void(0);'  data-userid='" + row['user_id'] + "' class='text-primary action_link restore_student' title='Restore Student'>Restore Student</a>";
						<?php }
						} ?>


						return action_link;
					},
					"targets": 8,
					responsivePriority: 999999,
					hidden: true
				},
				{
					type: 'formatted-num',
					targets: 3
				},
				{
					"visible": false,
					"targets": [0]
				}
			]
		});
	}

	$(document).on('click', '.restore_student', function() {
		return new swal({
			title: "Are you sure?",
			text: "Do you want to restore record!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Yes",
			closeOnConfirm: false
		}).then((result) => {
			if (result.isConfirmed) {
				var stu_userid = $(this).data('userid');
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
				$.post(targetUrl, {
					stu_userid: stu_userid,
					action: 'student_restore'
				}, function(data, status) {
					if (status == 'success') {
						if (data.code == 1) {
							displayAjaxMsg(data.msg, data.code);

						} else {
							displayAjaxMsg(data.msg, data.code);
						}
					} else {
						displayAjaxMsg(data.msg);
					}
					deleted_records();
				}, 'json');

			} else {
				return false;
			}
		})

	});
</script>
<?php include "../footer.php" ?>