<?php
$mob_title = "List Basic Fees";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_basic_fees_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
if(!empty(get_country()->currency)){
	$currency = get_country()->currency;
}else{
	$currency = '';
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
<div class="page-header-content">
	<div class="page-title">
		<h4>Basic Fees</h4>
	</div>
</div>
<div class="breadcrumb-line">
	<ul class="breadcrumb">
		<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
		<li class="active">List Basic Fees</li>
	</ul>
</div>
<?php if(in_array("su_basic_fees_create", $_SESSION['login_user_permissions'])){	
?>

<?php 
$basic_fees = $db->get_results("SELECT * FROM ss_basicfees bcf where bcf.status != 2 and bcf.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
if(isset($genral_info->one_student_one_lavel) && $genral_info->one_student_one_lavel == 0 && count((array)$basic_fees) == 0){ ?>

<div class="above-content"> <a href="javascript:void(0)" id="addbasicfees" class="pull-right"><span class="label label-danger"> Add Basic Fees</span></a> 
</div>

<?php }elseif(isset($genral_info->one_student_one_lavel) && $genral_info->one_student_one_lavel == 1 ){ ?>
<div class="above-content"> <a href="javascript:void(0)" id="addbasicfees" class="pull-right"><span class="label label-danger"> Add Basic Fees</span></a> 
</div>

<?php }} ?>

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
							<?php if($genral_info->one_student_one_lavel == 1){ ?>
							<th>Group</th>
							<?php } ?>
							<th>Fees Amount</th>
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
<div id="modalAddBasicFees" class="modal fade">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<form name="frmAddBasicFees" id="frmAddBasicFees" class="form-validate-jquery" method="post">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h5 class="modal-title headtext">Add Basic Fees</h5>
	</div>
	<div class="modal-body">
	<div class="row">
	<?php if($genral_info->one_student_one_lavel == 1){ ?>
		<div class="col-md-4">
			<div class="form-group">
			<label for="group">Group Name:<span class="mandatory">*</span></label>
			<?php

			if(check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')){

				$groups = $db->get_results("select * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
				and is_active=1 and is_deleted=0 order by group_name asc"); 
			}else{
				//$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 and id in (select group_id from ss_staffgroupmap where staff_user_id='".$_SESSION['icksumm_uat_login_userid']."' and active = 1) order by group_name asc"); 
				$groups = $db->get_results("select * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
				and is_active=1 and is_deleted=0 and id in (
					SELECT group_id FROM ss_classtime WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
					and id IN (SELECT classtime_id FROM ss_staffclasstimemap WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
					and staff_user_id = '".$_SESSION['icksumm_uat_login_userid']."')) order by group_name asc");
			}
			?>
			<select class="form-control required" name="group_id" id="group_id">
			<option value="">Select Group</option>
			<?php foreach($groups as $grp){ ?>
			<option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
			<?php } ?>
			</select>
		</div>
		</div>
        <?php } ?>
		<div class="col-md-4">
		<div class="form-group">
			<label for="group">Fees Amount: <span class="mandatory">*</span></label>
			<input type="text" class="form-control required"  dollarsscents="true" minlength="1" maxlength="8"  name="fee_amount" id="fee_amount" placeholder="Fees Amount (<?php echo (!empty(get_country()->currency))?get_country()->currency:'' ?>) ">
		</div>
		</div>
		<?php if($genral_info->one_student_one_lavel == 1){ ?>
		<div class="col-md-4">
		<div class="form-group">
		<label for="group">Status: <span class="mandatory">*</span></label>
		<select class="form-control" name="status" id="status" required>
			<option value="">Select Status</option>
			<option value="1">Active</option>
			<option value="0">Inactive</option>
		</select>
		</div>
		</div>
		<?php } ?>
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
			<input type="hidden" name="action" id="action" value="basic_fees_add">
			<input type="hidden" name="basic_fees_id" id="basic_fees_id" value="">
			<button type="submit" class="btn btn-success btnsubmit" id="btn_disable_id"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
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
	$(document).on('click', '.remove_basic_fees', function(data, status) {
		var id = $(this).data('feesid');
		$.confirm({
			title: 'Confirm!',
			content: 'Do you want to delete basic Fees?',
			buttons: {
				confirm: function () {
					$('.spinner').removeClass('hide');
					$.post('<?php echo SITEURL ?>ajax/ajss-basicfees',{id:id,action:'delete_basic_fees'}, function(data, status) {
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
		});
	});

	// $(document).on('click','.remove_basic_fees',function(data,status){
	// 	if(confirm('Do you want to delete basic Fees?')){
	// 		$('.spinner').removeClass('hide');

	// 		var id = $(this).data('feesid');

	// 		$.post('<?php echo SITEURL ?>ajax/ajss-basicfees',{id:id,action:'delete_basic_fees'},function(data,status){
	// 			if(status == 'success'){
	// 				displayAjaxMsg(data.msg,data.code);
	// 				setTimeout(function() {
	// 					$(".ajaxMsg").html("");
	// 				}, 8000);
	// 				fillTable();
	// 			}else{
	// 				displayAjaxMsg(data.msg,data.code);
	// 				setTimeout(function() {
	// 					$(".ajaxMsg").html("");
	// 				}, 8000);
	// 			}
	// 		},'json');
	// 	}
	// });



	//Add Basic fees start
	$('#modalAddBasicFees').on('hide.bs.modal', function(e) {
		$('#statusMsg').html('');
		$('#frmAddBasicFees').trigger('reset');
		var validator = $("#frmAddBasicFees").validate();
		validator.resetForm();
	});

	$(document).on('click','#addbasicfees',function() {
		$('.headtext').html("Add Basic Fees");
		$('#action').val("basic_fees_add");
		$('#modalAddBasicFees').modal('show');
	});

	
	$('#frmAddBasicFees').submit(function(e){
		e.preventDefault();
		$('#btn_disable_id').prop('disabled', true);
		if($('#frmAddBasicFees').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-basicfees';
			$('#statusMsg').html('Processing...');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){                    
				if(status == 'success'){
					$('#statusMsg').html(data.msg);
					if(data.code == 1){
						fillTable();
						if($('#action').val() == 'basic_fees_add'){
						$('#frmAddBasicFees').trigger('reset');
						$('#addbasicfees').hide();
						}
						displayAjaxMsg(data.msg,data.code);
						$('#modalAddBasicFees').modal('hide');
						$('#btn_disable_id').prop('disabled', false);
					}else{
						displayAjaxMsg(data.msg,data.code);
						$('#btn_disable_id').prop('disabled', false);
					}
				}else{
					displayAjaxMsg(data.msg);
					$('#btn_disable_id').prop('disabled', false);
				}
			},'json');
		}
	});
	//Add Basic fees end


	//edit Basic fees start
	$('#modalAddBasicFees').on('hide.bs.modal', function(e) {
			$('#statusMsg').html('');
			$('#frmAddBasicFees').trigger('reset');
			var validator = $("#frmAddBasicFees").validate();
			validator.resetForm();
	});


	$(document).on('click','.editAddfees',function() {

		$('.headtext').html("Edit Basic Fees");
		$('#basic_fees_id').val($(this).data('id'));
		$('#group_id').val($(this).data('group'));
		var price = $(this).data('amount');
		var currency = "<?php echo $currency; ?>";
		var amount = price.replace(currency, '');
		$('#fee_amount').val(amount);
		$('#status').val($(this).data('status'));
		$('#action').val("basic_fees_edit");
		$('#modalAddBasicFees').modal('show');

		if($(this).data('confirmcheckcon') == 1){
			$('.div_conn').removeClass('hide');
			$('.tern_con').addClass('required');
			
			$('.notetextclass').html($(this).data('notetext'));
		}else{
			$('.div_conn').addClass('hide');
			$('.tern_con').removeClass('required');
		}

	});
	//edit Basic fees end

});

function fillTable() {
	var table = $('.datatable-basic').DataTable({
		autoWidth: false,
		destroy: true,
		pageLength: <?php echo TABLE_LIST_SHOW ?>,
		responsive: true,
		ajax: '<?php echo SITEURL ?>ajax/ajss-basicfees?action=list_basic_fees',
		sProcessing: '',
		language: {
			loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		'columns': [{
				'data': 'id'
			},
			<?php if($genral_info->one_student_one_lavel == 1){ ?>
			{
				'data': 'group_name',
				searchable: true,
				orderable: true
			},
			<?php } ?>
			{
				'data': 'fee_amount',
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
						<?php if($genral_info->one_student_one_lavel == 1){ ?>
						<?php if(in_array("su_basic_fees_edit", $_SESSION['login_user_permissions'])){ ?>
						links += '<a href="javascript:;"  data-id='+row['id']+'  data-group='+row['group_id']+' data-amount="'+row['fee_amount']+'" data-status="'+row['status_id']+'" data-confirmcheckcon='+row['confirm_check_con']+' data-notetext="'+row['note_text']+'" title="Edit" class="text-primary action_link editAddfees">Edit</a>';
						<?php } ?>

						//links += "<a href='javascript:void(0)' data-feesid = " + row['id'] + " title='Delete' class = 'text-danger remove_basic_fees action_link'>Delete</a>";
					  
						<?php }else{ ?>
						<?php if(in_array("su_basic_fees_edit", $_SESSION['login_user_permissions'])){ ?>
						if(row['status_id'] == 1){
						links += '<a href="javascript:;"  data-id='+row['id']+'  data-group='+row['group_id']+' data-amount="'+row['fee_amount']+'" data-status="'+row['status_id']+'" data-confirmcheckcon='+row['confirm_check_con']+' data-notetext="'+row['note_text']+'" title="Edit" class="text-primary action_link editAddfees">Edit</a>';
						}
						<?php } ?>
						<?php } ?>
					// return [links, attechment];
					return links;
				},
				"targets": <?php if($genral_info->one_student_one_lavel == 1){ echo 4; }else{  echo 3; }?>
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