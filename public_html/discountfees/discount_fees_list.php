<?php
$mob_title = "List Fees Discounts";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_discount_manage_fees_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
$countries = $db->get_var("select id from ss_country where is_active = 1  and id = '".get_country()->country_id."'");
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Fees Discounts</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">List Fees Discounts</li>
		</ul>
	</div>

	<?php if (in_array("su_discount_manage_fees_create", $_SESSION['login_user_permissions'])) { ?>
		<div class="above-content"> <a href="javascript:void(0)" id="addDiscountFees" class="pull-right"><span class="label label-danger"> Add Fees Discounts</span></a>
		</div>
	<?php } ?>
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
								<th>Discount Name</th>
								<th>Discount Unit</th>
								<th>Discount</th>
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
<div id="modalAddDiscountFees" class="modal fade">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form name="frmAddDiscountFees" id="frmAddDiscountFees" class="form-validate-jquery" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title headtext">Add Fees Discounts</h5>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label for="discount_name">Discount Name: <span class="mandatory">*</span></label>
								<input type="text" class="form-control" name="discount_name" id="discount_name" spacenotallow="true" required>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="group">Discount Unit: <span class="mandatory">*</span></label>
								<select class="form-control" name="discount_unit" id="discount_unit" required>
									<option value="">Select Unit</option>
									<option value="p">Percent</option>
									<option value="<?php echo get_country()->discount_val; ?>"><?php echo get_country()->discount_option; ?></option>
								</select>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<div class="form-group">
									<label for="discount_percent">Discount Value: <span class="mandatory">*</span></label>
									<input type="text" class="form-control" name="discount_percent" minlength="1" maxlength="8" dollarsscents="true" id="discount_percent" required>
								</div>
							</div>
						</div>


						<div class="col-md-3">
							<div class="form-group">
								<label for="group">Status: <span class="mandatory">*</span></label>
								<select class="form-control" name="status" id="status" required>
									<option value="">Select Status</option>
									<option value="1">Active</option>
									<option value="0">Inactive</option>
								</select>
							</div>
						</div>


					</div>

					<div class="row div_conn hide">
						<div class="col-md-12">
							<div class="form-group">
								<p><strong class="text-danger" style="font-size: 18px;">Note :</strong>
									<span class="text-danger notetextclass" style="font-size: 15px;"> </span>
								</p>
								<div class="form-check-inline">
									<label class="form-check-label" for="check2">
										<input type="checkbox" class="form-check-input tern_con" style="margin-right:5px;" id="check2" name="tern_con" ><strong>I Agree <span class="mandatory">*</span></strong>
									</label>
									<label id="tern_con-error" class="validation-error-label" for="tern_con" style="display: inline-block;"></label>
								</div>
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
							<input type="hidden" name="action" id="action" value="discount_fees_add">
							<input type="hidden" name="discount_fees_id" id="discount_fees_id" value="">
							<button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>

			</form>
		</div>
	</div>
</div>


<script>
	$(document).ready(function() {
		//FILL TABLE
		fillTable();

		jQuery.validator.addMethod("dollarsscents", function(value, element) {
			return this.optional(element) || /^[1-9]\d{0,4}(\.\d{0,2})?$/i.test(value);
		}, "Please enter a valid amount");

		//REMOVE Permission
		$(document).on('click', '.remove_discount_fees', function(data, status) {
			var id = $(this).data('discountfeesid');
			  $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete Fees Discounts?',
            buttons: {
                confirm: function () {
				$('.spinner').removeClass('hide');
				$.post('<?php echo SITEURL ?>ajax/ajss-discount-fees', {
					id: id,
					action: 'delete_discount_fees'
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
			cancel: function () {
            }
	     	}
	      })

		});



		//Add Fees Discounts start
		$('#modalAddDiscountFees').on('hide.bs.modal', function(e) {
			$('#statusMsg').html('');
			$('#frmAddDiscountFees').trigger('reset');
			var validator = $("#frmAddDiscountFees").validate();
			validator.resetForm();
		});

		$(document).on('click', '#addDiscountFees', function() {
			$('.headtext').html("Add Fees Discount");
			$('#action').val("discount_fees_add");
			$('#modalAddDiscountFees').modal('show');
		});


		$('#frmAddDiscountFees').submit(function(e) {
			e.preventDefault();
			if ($('#frmAddDiscountFees').valid()) {
				if($('#discount_unit').val() == 'p'){
					if($('#discount_percent').val()>100){
						$('#statusMsg').css('color','red');
						$('#statusMsg').html('Discount Percentage Should be less than 100');
						return false;
					}
				}
				var targetUrl = '<?php echo SITEURL ?>ajax/ajss-discount-fees';
				$('#statusMsg').html('Processing...');
				$('.btnsubmit').attr('disabled', true);
				var formDate = $(this).serialize();
				$.post(targetUrl, formDate, function(data, status) {
					if (status == 'success') {
						$('#statusMsg').html(data.msg);
						$('.btnsubmit').attr('disabled', false);
						if (data.code == 1) {
							if ($('#action').val() == 'discount_fees_add') {
								$('#frmAddDiscountFees').trigger('reset');
							}
							displayAjaxMsg(data.msg, data.code);
							fillTable();
							setTimeout(function() {
								$('#modalAddDiscountFees').modal('hide');
							}, 2000);


						} else {
							displayAjaxMsg(data.msg, data.code);
						}
					} else {
						displayAjaxMsg(data.msg);
					}
				}, 'json');
			}
		});

		//Add Basic fees end


		//edit Fees Discounts start

		$('#modalAddDiscountFees').on('hide.bs.modal', function(e) {
			$('#statusMsg').html('');
			$('.div_conn').addClass('hide');
			$('#modalAddDiscountFees').trigger('reset');
			var validator = $("#frmAddDiscountFees").validate();
			validator.resetForm();
		});


		$(document).on('click', '.editAddfees', function() {
			$('.headtext').html("Edit Fees Discount");
			$('#discount_fees_id').val($(this).data('id'));
			$('#discount_name').val($(this).data('discountname'));
			$('#discount_unit').val($(this).data('discountunit'));
			$('#discount_percent').val($(this).data('discountpercent'));
			$('#status').val($(this).data('status'));
			$('#action').val("discount_fees_edit");
			$('#modalAddDiscountFees').modal('show');

			if($(this).data('confirmcheckcon') == 1){
			$('.div_conn').removeClass('hide');
			$('.tern_con').addClass('required');
			
			$('.notetextclass').html($(this).data('notetext'));
		}else{
			$('.div_conn').addClass('hide');
			$('.tern_con').removeClass('required');
		}
		});


		//edit Fees Discounts end	

	});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-discount-fees?action=list_discount_fees',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'discount_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'discountunit',
					searchable: true,
					orderable: true
				},
				{
					'data': 'discount_percent',
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
				[0, "desc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
						var links = "";
						<?php if (in_array("su_discount_manage_fees_edit", $_SESSION['login_user_permissions'])) { ?>
							links += "<a href='javascript:;'  data-id='" + row['id'] + "'  data-discountname='" + row['discount_name'] + "' data-discountunit='" + row['discount_unit'] + "' data-discountpercent='" + row['discount_percent'] + "' data-status='" + row['status_id'] + "' data-confirmcheckcon="+row['confirm_check_con']+" data-notetext='"+row['note_text']+"'  title='Edit' class='text-primary action_link editAddfees'>Edit</a>";
						<?php } ?>

						<?php if (in_array("su_discount_manage_fees_delete", $_SESSION['login_user_permissions'])) { ?>
							links += "<a href='javascript:void(0)' data-discountfeesid = " + row['id'] + " title='Delete' class = 'text-danger remove_discount_fees action_link'>Delete</a>";
						<?php } ?>


						// return [links, attechment];
						return links;
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
</script>
<?php include "../footer.php" ?>