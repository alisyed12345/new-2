<?php
$mob_title = "List Basic Fees";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_email_template_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Custom Email Templates</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active"> Custom Email Templates</li>
		</ul>
	</div>
	<div class="above-content"> 
	<?php if (in_array("su_email_template_create", $_SESSION['login_user_permissions'])) {   ?>
    <a href="<?php echo SITEURL ?>email_template/add_email_template" class="pull-right"><span class="label label-danger"> Add Custom Email Template</span></a> 
	<?php  } ?>
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
								<th>Email Template Type</th>
								<th>Subject</th>
								  <?php if(check_userrole_by_code('UT01') && check_userrole_by_group('admin')){ ?>
                                <th>System Templates</th>
                                 <?php } ?>
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

<!-- /Add modal --> 
<div id="modal_request_detail" class="modal fade">
  <div class="modal-dialog" style="width: fit-content;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Email Template Details <span id="request_detail_title"></span></h5>
      </div>
      <div class="modal-body" id="request_detail"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- /Add modal --> 
<script>
$(document).ready(function() {
    //FILL TABLE
    fillTable();


    $('#modal_request_detail').on('hide.bs.modal', function(e) {
   			$('#overlay').hide();
    });

	//REMOVE Permission
	$(document).on('click','.remove_temp_email',function(data,status){
		var id = $(this).data('id');
		$('.spinner').removeClass('hide');
	  $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete email template?',
            buttons: {
                confirm: function () {

			$.post('<?php echo SITEURL ?>ajax/ajss-email-template',{id:id,action:'delete_email_template'},function(data,status){ 
				if(status == 'success'){
					displayAjaxMsg(data.msg,data.code);
					setTimeout(function() {
						$(".ajaxMsg").html("");
		          }, 8000);
				  fillTable();
				}else{
					displayAjaxMsg(data.msg,data.code);
					setTimeout(function() {
					  $(".ajaxMsg").html("");
		          }, 8000);
				}
			},'json');
		},
		cancel: function () {
            }
	  }
    })
	});

	$(document).on('click','.viewdetail',function(){ 
		var id = $(this).data('id');
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-email-template';

		$('#request_detail_title').html('#' + id );
		$('#request_detail').html('<h5>Data loading, please wait</h5>');
		$('#modal_request_detail').modal('show');
				
		$.post(targetUrl,{id:id,action:'view_email_template'},function(data,status){
			if(status == 'success'){
				$('#request_detail').html(data);
			}
		});
	});

});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-email-template?action=list_email_template',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			   <?php if(check_userrole_by_code('UT01') && check_userrole_by_group('admin')){ ?>
			'columns': [
				{
					'data': 'type_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'email_subject',
					searchable: true,
					orderable: true
				},
				{
					'data': 'defaulttemp',
					searchable: true,
					orderable: true
				},
				{
					'data': 'is_active',
					searchable: true,
					orderable: true
				}
			],
			"order": [
				[0, "desc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
                          var links = "";
                          	links += "<a href='Javascript:void(0)' class='text-orange action_link overlay_link viewdetail' data-id = " + row['id'] + " title='View'>View</a>";

							  <?php  if(in_array("su_email_template_edit", $_SESSION['login_user_permissions'])){ ?>
						    links += "<a href='<?php echo SITEURL ?>email_template/edit_email_template.php?id="+row['id']+"'  title='Edit' class='text-primary action_link editAddfees'>Edit</a>";
							<?php } ?>
							// if (row['system_template'] == 0) {
								<?php  if(in_array("su_email_template_delete", $_SESSION['login_user_permissions'])){ ?>
							links += "<a href='javascript:void(0)' data-id = " + row['id'] + " title='Delete' class = 'text-danger remove_temp_email action_link'>Delete</a>";
							<?php } ?>
						
						// return [links, attechment];
						return links;
					},
					"targets": 4
				}
			]
			  <?php } else { ?>

              'columns': [
				{
					'data': 'type_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'email_subject',
					searchable: true,
					orderable: true
				},
				{
					'data': 'is_active',
					searchable: true,
					orderable: true
				}
			],
			"order": [
				[0, "desc"]
			],
			"columnDefs": [{
					"render": function(data, type, row) {
                          var links = "";
                          	links += "<a href='Javascript:void(0)' class='text-orange action_link overlay_link viewdetail' data-id = " + row['id'] + " title='View'>View</a>";
							  <?php  if(in_array("su_email_template_edit", $_SESSION['login_user_permissions'])){ ?>
						    links += "<a href='<?php echo SITEURL ?>email_template/edit_email_template.php?id="+row['id']+"'  title='Edit' class='text-primary action_link editAddfees'>Edit</a>";
							<?php } ?>
							
							<?php  if(in_array("su_email_template_delete", $_SESSION['login_user_permissions'])){ ?>
						    links += "<a href='javascript:void(0)' data-id = " + row['id'] + " title='Delete' class = 'text-danger remove_temp_email action_link'>Delete</a>";
							<?php } ?>
                       
						
						
						// return [links, attechment];
						return links;
					},
					"targets": 3
				}
			]

              <?php } ?>
		});
	}
</script>
<?php include "../footer.php" ?>