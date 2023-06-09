<?php
$mob_title = "Message";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_communicate_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->
<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4 style="display:inline-block">Mass Email History</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">Mass Email History</li>
		</ul>
	</div>

	<div class="above-content">
		<?php if (in_array("su_communicate_send_mass_email", $_SESSION['login_user_permissions'])) {   ?>
			<a href="<?php echo SITEURL ?>message/mass_email" class="pull-right"><span class="btn btn-danger">Send New Mass Email</span></a>
		<?php  } ?>
		<?php if (in_array("su_communicate_initiate", $_SESSION['login_user_permissions'])) {   ?>
			<div class="emailbtn">
				<a href="javascript:;" class="pull-right btn btn-danger sendemail btndisable" style="margin-right:10px;"><span class="ajsmsg">Initiate Email Queue</span></a>
				<!-- <button type="button" class="pull-right btn btn-danger sendemail btndisable"  style="margin-right:10px;">Initiate Email Queue</button> -->
			</div>
		<?php } ?>
	</div>
</div>
</div>

<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-flat panel-flat-box">
				<div class="panel-body panel-body-box">
					<div class="row" style="margin-top: 10px;">
						<div class="col-lg-12">
							<div class="table-responsive">
								<table class="table table-bordered dataGrid">
									<thead>
										<tr>
											<th>Bulk Message ID</th>
											<th>Bulk Message ID Encrypted</th>
											<?php if (in_array("su_communicate_initiate", $_SESSION['login_user_permissions'])) {   ?>
												<th style="text-align: center;"><input type="checkbox" id="checkall"></th>
											<?php } ?>
											<th>Date</th>
											<th>Subject</th>
											<th>Sent</th>
											<th>In Queue</th>
											<th>Failed</th>
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
		</div>
		<!-- Modal - Mail Detail-->
		<div id="modal_mail_detail" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h5 class="modal-title">Mail Detail</h5>
					</div>
					<div class="modal-body viewonly" id="mail_detail"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<!-- /Modal -->
	</div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script type="text/javascript" src="http://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js"></script>>

<!-- <script type="text/javascript" src="http://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js"></script> -->
<script>
	$(document).ready(function() {

		if($('.selectedrow').is(':checked')){
			$('.btndisable').attr("disabled", false);
		}else{
			$('.btndisable').attr("disabled", true);
		}

		jQuery.extend(jQuery.fn.dataTableExt.oSort, {
			"date-eu-pre": function(date) {

				date = date.replace(" ", "");

				if (!date) {
					return 0;
				}

				var year;
				var eu_date = date.split(/[\.\-\/]/);

				/*year (optional)*/
				if (eu_date[2]) {
					year = eu_date[2];
				} else {
					year = 0;
				}

				/*month*/
				var month = eu_date[0];
				if (month.length == 1) {
					month = 0 + month;
				}

				/*day*/
				var day = eu_date[0];
				if (day.length == 1) {
					day = 0 + day;
				}
				//alert(day);
				return (year + month + day) * 1;
			},

			"date-eu-asc": function(a, b) {
				return ((a < b) ? -1 : ((a > b) ? 1 : 0));
			},

			"date-eu-desc": function(a, b) {
				return ((a < b) ? 1 : ((a > b) ? -1 : 0));
			}
		});
		//FILL TABLE
		fillTable();

		//SEND EMAIL ACTIVE / DEACTIVE
		$('#checkall').change(function() {
			if (document.getElementById("checkall").disabled = false) {
				$('.selectedrowunchecked').prop('checked', false);
			} else {
				if ($(this).is(':checked')) {
					$('.selectedrow').prop('checked', true);
				} else {
					$('.selectedrow').prop('checked', false);
				}

				if($('.selectedrow').is(':checked')){
					$('.btndisable').attr("disabled", false);
				}else{
					$('.btndisable').attr("disabled", true);
				}
			}
		});

		$(document).on('click', '.selectedrow', function() {
			if ($('.selectedrow').is(':checked')) {
				$('.btndisable').attr("disabled", false);
			}else{
				$('.btndisable').attr("disabled", true);
			}
		});
			
		$(document).on('click', '.sendemail', function() {
			var msg_ids = [];
			$('.selectedrow').each(function() {
				if ($(this).is(':checked')) {
					msg_ids.push($(this).data('msgid'));
				}
			});
			if (msg_ids.length > 0) {
				$.confirm({
					title: 'Confirm!',
					content: 'Are you sure you want to initiate email sending process?',
					buttons: {
						confirm: function() {
							var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
							$.post(targetUrl, {
								action: 'sendemail',
								msg_ids: msg_ids
							}, function(data, status) {
								if (status == 'success') {
									$.alert({
										title: 'Success',
										content: data.msg,
									});
									$('.emailbtn').hide();
									$('#checkall').prop('checked', false);
									fillTable();
								} else {
									$.alert({
										title: 'Failed',
										content: data.msg,
									});
									fillTable();
								}
							}, 'json');
						},
						cancel: function() {}
					}
				});

			} else {
				//$.alert("<h3>Select atleast one initial email</h3>");
				$.confirm({
					title: 'Alert!',
					content: 'Please Select At Least One Queued Email To Initiate',
				});
			}


		});
		// Delete Mass Email
		$(document).on('click', '.deletedetail', function() {
			var msg_ids = $(this).data('msgid');
			$.confirm({
				title: 'Confirm!',
				content: 'Are you sure you want to delete email?',
				buttons: {
					confirm: function() {
						var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
						$.post(targetUrl, {
							action: 'delete_mass_email',
							msgid: msg_ids
						}, function(data, status) {
							if (status == 'success') {
								fillTable();
								$.alert({
									title: 'Success',
									content: data.msg,
								});
								$('.emailbtn').hide();

							} else {
								fillTable();
								$.alert({
									title: 'Failed',
									content: data.msg,
								});
							}
						}, 'json');
					},
					cancel: function() {}
				}
			});
		});

		//FETCH MAIL DETAILS
		$(document).on('click', '.viewdetail', function() {
			var msgid = $(this).data('msgid');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';

			$('#mail_detail').html('<h5>Data loading, please wait...</h5>');
			$('#modal_mail_detail').modal('show');

			$.post(targetUrl, {
				msgid: msgid,
				action: 'view_mail_detail'
			}, function(data, status) {
				if (status == 'success') {
					$('#mail_detail').html(data);
				}
			});
		});

		//FETCH STAFF DETAILS
		$(document).on('click', '.resend', function() {
			if (confirm('Are you sure you want to re-queue failed deliveries?')) {
				var msgid = $(this).data('msgid');
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';

				$(this).parent().append(' Wait...');
				$.post(targetUrl, {
					msgid: msgid,
					action: 'resend_mass_emails'
				}, function(data, status) {
					if (status == 'success') {
						fillTable();
					}
				});
			}
		});
	});

	function fillTable() {

		var table = $('.dataGrid').DataTable({
			autoWidth: false,
			destroy: true,
			serverSide: true,
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			responsive: true,
			iDisplayLength: <?php echo TABLE_LIST_SHOW ?>,
			ajax: '<?php echo SITEURL ?>ajax/ajss-message?action=mass_email_history',
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'msgid'
				},
				<?php if (in_array("su_communicate_initiate", $_SESSION['login_user_permissions'])) {   ?> {
						'data': 'checkbox',
						searchable: false,
						orderable: false
					},
				<?php } ?> {
					'data': 'created_on',
					searchable: true,
					orderable: true
				},
				{
					'data': 'subject',
					searchable: true,
					orderable: false
				},
				{
					'data': 'sent',
					orderable: true
				},
				{
					'data': 'in_queue',
					orderable: true
				},
				{
					'data': 'failed',
					orderable: true
				},
			],
			"order": [
				[3, "desc"]
			],
			//"aaSorting": [[3, 'desc']],
			"columnDefs": [
				// 	 // {"targets":3, "type":"date-eu"},
				// 	{
				// 		"targets": 3,
				// 		"type": "date-eu-desc"
				// 	},
				{
					"render": function(data, type, row) {
						var links = '';

						if (row['failed'] > 0) {
							//return "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' class='text-primary action_link resend' title='Re-send Failed Mails'><i class = 'icon-mail5' ></i></a>";
							<?php if (in_array("su_communicate_send_mass_email", $_SESSION['login_user_permissions'])) { ?>
								links = "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' class='text-primary action_link resend' title='Re-send Failed Mails'>Re-send Failed Mails</a>";
							<?php } ?>
						} else {
							links = '';
						}
						<?php if (in_array("su_communicate_view", $_SESSION['login_user_permissions'])) { ?>
							links = links + "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' class='text-secondary action_link viewdetail' title='View Email Details'>View Email Details</a>";
						<?php } ?>
						<?php if (in_array("su_communicate_delete", $_SESSION['login_user_permissions'])) { ?>
							if (row['delete']) {
								links = links + "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' class='text-danger action_link deletedetail' title='Delete'>Delete</a>";
								$('.emailbtn').show();
							}
						<?php } ?>
						return links;
					},
					<?php if (in_array("su_communicate_initiate", $_SESSION['login_user_permissions'])) {   ?> "targets": 8,
						"type": "date-eu"
					<?php } else { ?> "targets": 7 <?php } ?>,
						"type": "date-eu"
				},
				{
					"visible": false,
					"targets": [0, 1]
				}
			]
		});
	}
</script>
<?php include "../footer.php" ?>