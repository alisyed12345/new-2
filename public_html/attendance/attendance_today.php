<?php
$mob_title = "Attendance";
include "../header.php";

$attendance_day_number = date('w');
/* 
//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01') && !check_userrole_by_code('UT02')) {
    include "../includes/unauthorized_msg.php";
    return;
}
 */
//AUTHARISATION CHECK 
if (!in_array("su_attendence_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}

$school_days = $db->get_row("select school_opening_days from ss_client_settings where status = 1 order by id desc limit 1");
$serdays = unserialize($school_days->school_opening_days);

//  echo"<pre>";
//  print_r($serdays);
// die;


if (isset($_GET['id'])) {
    $clickedGroup = $db->get_row("select * from ss_groups where md5(concat('g',id)) = '" . $_GET['id'] . "' AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
    $group_id = $clickedGroup->id;
}

if (isset($_GET['cid'])) {
    $clickedClass = $db->get_row("select * from ss_classes where md5(concat('c',id)) = '" . $_GET['cid'] . "' AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
    $class_id = $clickedClass->id;

		$classes = $db->get_results("SELECT distinct c.id, class_name FROM `ss_classes` c INNER JOIN `ss_classtime` ct ON c.id = ct.class_id WHERE c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and ct.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and c.is_active = 1 and ct.is_active = 1 and class_id = '".$class_id."'  order by class_name");

}    

//and ct.day_number = '".$attendance_day_number."'
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4 style="display:inline-block">Today's Attendance</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo SITEURL . "dashboard.php " ?>">
					<i class="icon-home2 position-left"></i> Dashboard</a>
			</li>
			<li class="active">Today's Attendance</li>
		</ul>
	</div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-flat panel-flat-box">
				<div class="panel-body panel-body-box">
                <?php if(in_array($attendance_day_number, $serdays)){ ?>



					<div class="ajaxMsg"></div>
					<form id="frmGetAttendance" class="form-validate-jquery" method="post">

						<div class="row">
							<div class="col-lg-3 col-xs-9">
								<div class="form-group">
									<?php
									if (check_userrole_by_code('UT01')) {
										//REPLACE date('w') WITH 0 FOR TESTING
									$groups = $db->get_results("select distinct g.id, g.group_name from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id where g.is_active=1 
									and g.is_deleted=0 AND g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND
									d.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND d.is_active ='1' order by group_name asc ");
									} else {
										//REPLACE date('w') WITH 0 FOR TESTING
										//ACTUAL CODE
										//$groups = $db->get_results("select g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id WHERE d.day_number = '" . date('w') . "' and g.is_active=1 and g.is_deleted=0 and g.id in (select group_id from ss_staffgroupmap where staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and active=1) order by group_name asc");
										
										$groups = $db->get_results("select DISTINCT g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id 
										WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and g.is_active=1 
										and g.is_deleted=0 and d.id in (select classtime_id from ss_staffclasstimemap 
										where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and active=1) order by group_name asc");
									}
									?>
									<select class="select2 form-control" name="group" id="group" required>
										<option value="">Select Group</option>
										<?php foreach ($groups as $grp) {?>
										<option value="<?php echo $grp->id ?>" <?php echo $group_id==$grp->id ? 'selected="selected"' : '' ?>>
											<?php echo $grp->group_name ?>
										</option>
										<?php }?>
									</select>
								</div>     
							</div>
							<div class="col-lg-3 col-xs-9">
								<select class="select2 form-control" name="group_class" id="group_class" required>
									<option value="">Select Class</option>
									<?php foreach ($classes as $cls) {?>
									<option value="<?php echo $cls->id ?>" <?php echo $class_id==$cls->id ? 'selected="selected"' : '' ?>>
										<?php echo $cls->class_name ?>
									</option>
									<?php }?>
								</select>
							</div>
							<div class="col-lg-2 col-xs-3">
								<i class="icon-spinner2 spinner hide marR10" id="get_spinner"></i>
							</div>
							<div class="col-lg-1 col-xs-4 text-center">
								<div class="att_abse">Absent</div>
							</div>
							<div class="col-lg-1 col-xs-4 text-center">
								<div class="att_pres">Present</div>
							</div>
							<div class="col-lg-1 col-xs-4 text-center">
								<div class="att_late">Late</div>
							</div>
						</div>
						<input type="hidden" name="action" value="get_group_todays_attendance">
					</form>
					<br>
					<form id="frmAttendanceSheet" class="form-validate-jquery" method="post">
						<div class="row attendance_cont">
						</div>
						<div class="row">
							<div class="col-md-10 text-right">
								<div class="ajaxMsgBot" id="ajaxMsgBot_Save"></div>
							</div>
							<div class="col-md-2 col-sm-4 text-right">
								<input type="hidden" name="action" value="save_attendance">
								<input type="hidden" name="group_id" id="group_id" value="<?php echo $group_id ?>">
								<input type="hidden" name="class_id" id="class_id" value="<?php echo $class_id ?>">
								<button type="submit" id="btnSubmit" class="btn btn-block btn-success hide">
									<i class="icon-spinner2 spinner hide marR10 insidebtn" id="save_spinner"></i> Submit</button>
							</div>
						</div>


						
<!-- 						<div class="row">
							<div class="col-md-12">
								<br>
							</div>
						</div>
						<div class="row homework_rows hide">
							<div class="col-md-12">
								<strong>HOMEWORK</strong>
							</div>
						</div>

						<div class="row homework_rows hide">
							<div class="col-md-12">
								<select class="form-control" name="homework_target" id="homework_target">
								</select>
							</div>
						</div>
						<br>
						<div class="row homework_rows hide">
							<div class="col-md-12">
								<textarea placeholder="Enter Homework" id="homework_text" name="homework_text" required class="form-control"></textarea>
							</div>
						</div>
						<br>
						<div class="row homework_rows hide">
							<div class="col-md-9 text-right">
								<div class="ajaxMsgBot" id="ajaxMsgBot_Homework"></div>
							</div>
							<div class="col-md-3 col-sm-4 text-right">
								<button type="submit" id="btnSubmitHomework" class="btn btn-block btn-success">
									<i class="icon-spinner2 spinner hide marR10 insidebtn" id="homework_spinner"></i> Submit Homework</button>
							</div>
						</div> -->
					</form>
					<br>
                   <?php }else{ ?>

                   <h2 style="text-align: center;">Today is <?php echo date('l'); ?> no classes today.</h2>

                   <?php } ?>



				</div>
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
				<h5 class="modal-title">Student Detail
					<span id="studentinfo_title"></span>
				</h5>
			</div>
			<div class="modal-body viewonly" id="student_detail"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- /Add modal -->
<script>
	$(document).ready(function() {
		var glb_already_taken = 0;

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

		$(document).on('click', '.atten_check', function() {
			if (glb_already_taken == 0) {
				var atten_hid_val = $(this).parent().find('.atten_hid').val();
				var atten_new_val = parseInt(atten_hid_val) + 1;

				if (atten_new_val == 3) {
					atten_new_val = 0;
				}

				if (atten_new_val == 0) {
					$(this).removeClass('att_late');
					$(this).addClass('att_abse');
				} else if (atten_new_val == 1) {
					$(this).removeClass('att_abse');
					$(this).addClass('att_pres');
				} else if (atten_new_val == 2) {
					$(this).removeClass('att_pres');
					$(this).addClass('att_late');
				}

				$(this).parent().find('.atten_hid').val(atten_new_val);
			}
		});

		/*$('#period').change(function(){
			if($("#period option:selected").data('curmonth') == '1' || $("#period option:selected").val() == ''){
				$('#full_month').prop( "checked", false);
				$('#full_month').parent().show();
			}else{
				$('#full_month').prop( "checked", true );
				$('#full_month').parent().hide();
			}
		});*/

		$('#frmGetAttendance').submit(function(e) {
			e.preventDefault();

			if ($('#frmGetAttendance').valid()) {
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance';
				$('#get_spinner').removeClass('hide');

				var formDate = $(this).serialize();
				$.post(targetUrl, formDate, function(data, status) {
					if (status == 'success') {
						$('.attendance_cont').html(data.sheet);
						$('#homework_target').html(data.group_students);

						$('.atten_check').each(function(index, element) {
							if ($(element).is(':checked')) {
								$(element).parent().addClass('sel_atten_day');
							}
						});

						if (data.data_found) {
							//COMMETED ON 12-OCT-2020
							//$('#btnSubmit').removeClass('hide');

							if (data.show_submit_btn == 0) {
								glb_already_taken = 1;
								//COMMENTED ON 31-AUG-2018 - UNCOMMENTED ON 12-OCT-2020
								$('#btnSubmit').addClass('hide');
							} else {
								glb_already_taken = 0;
								//COMMENTED ON 31-AUG-2018 - UNCOMMENTED ON 12-OCT-2020
								$('#btnSubmit').removeClass('hide');
							}

							if (data.show_homework_box == 1) {
								$('.homework_rows').removeClass('hide');
							} else {
								$('.homework_rows').addClass('hide');
							}
						} else {
							$('#btnSubmit').addClass('hide');
						}
					} else {
						$('.attendance_cont').html('Error: Process failed');
					}

					$('#get_spinner').addClass('hide');
				}, 'json');
			}
		});

		$('#frmAttendanceSheet').submit(function(e) {
			e.preventDefault();

			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance';
			$('#save_spinner').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl, formDate, function(data, status) {
				if (status == 'success') {
					if (data.code == 1) {
						//RECALL ATTENDANCE DATA
						//$('#group').trigger('change');
						<?php if (check_userrole_by_code('UT01')) {?>
						glb_already_taken = 0;
						//COMMENTED ON 31-AUG-2018- UNCOMMENTED ON 12-OCT-2020
						$('#btnSubmit').removeClass('hide');
						<?php } else {?>
						glb_already_taken = 1;
						//COMMENTED ON 31-AUG-2018 - UNCOMMENTED ON 12-OCT-2020
						$('#btnSubmit').addClass('hide');
						<?php }?>

						$('.homework_rows').removeClass('hide');
						displayAjaxMsg(data.msg, data.code);
						$('#ajaxMsgBot_Homework').html('');
					} else {
						displayAjaxMsg(data.msg, data.code);
					}
				} else {
					displayAjaxMsg(data.msg);
				}

				$('#save_spinner').addClass('hide');
			}, 'json');
		});

		$('#btnSubmitHomework').click(function(e) {
			e.preventDefault();

			if ($('#frmAttendanceSheet').valid()) {
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-homework';
				$('#homework_spinner').removeClass('hide');

				$.post(targetUrl, {
					'action': 'homework_add',
					'homework_text': $('#homework_text').val(),
					'group_id': $('#group').val(),
					'homework_target': $('#homework_target').val()
				}, function(data, status) {
					if (status == 'success') {
						if (data.code == 1) {
							displayAjaxMsg(data.msg, data.code);
							$('#homework_text').val('');
						} else {
							displayAjaxMsg(data.msg, data.code);
						}

						$('#ajaxMsgBot_Save').html('');
					} else {
						displayAjaxMsg(data.msg);
					}

					$('#homework_spinner').addClass('hide');
				}, 'json');
			}

			$('#homework_text-error').addClass('validation-error-label');
		});

		$(document).on('change', '.atten_check', function(data, status) {
			if ($(this).is(':checked')) {
				$(this).parent().find('.atten_hid').val(1);
				$(this).parent().addClass('sel_atten_day');
			} else {
				$(this).parent().find('.atten_hid').val(0);
				$(this).parent().removeClass('sel_atten_day');
			}
		});

		$('#group_class').change(function() {
			$('#class_id').val($(this).val());
			$('#group_id').val($("#group").val());
			$('#frmGetAttendance').submit();
		});

		<?php if (isset($_GET['id']) && isset($_GET['cid'])) { ?>
		$('#frmGetAttendance').submit();
		<?php } ?>
		
		$('#group').change(function() {
			$('.attendance_cont').html('');
			$('.homework_rows').addClass('hide');
			$('#btnSubmit').addClass('hide');

			if ($('#group').val() == '') {
				$('#group_class').html("<option value=''>Select</option>");
			} else {
				$('#group_class').html("<option value=''>Loading...</option>");

				$.post('<?php echo SITEURL ?>ajax/ajss-classes', {
					'action': 'fetch_group_class_for_select',
					'group_id': $('#group').val()
				}, function(data, status) {
					if (status == 'success') {
						$('#group_class').html(data);
					} else {
						$('#group_class').html("<option value=''>Select</option>");
					}
				});
			}
		});

		<?php if (isset($_GET['id'])) {?>
		//$('#group').trigger('change');
		<?php }?>
	});
</script>
<?php include "../footer.php" ?>