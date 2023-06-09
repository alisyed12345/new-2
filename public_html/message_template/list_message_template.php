<?php
$mob_title = "List Message Templates";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
// if (!check_userrole_by_code('UT01') && !in_array("su_basic_fees_list", $_SESSION['login_user_permissions'])) {
// 	include "../includes/unauthorized_msg.php";
// 	return;
// }
$get_msg_type = $db->get_results("SELECT * FROM ss_sms_template_types WHERE STATUS = 1");
?>
<!-- Page header -->
<style>
    label.error{
        color:red;
    }
    .mands{
        color:red;
        margin-left: 3px;
    }
</style>
<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Message Templates</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">Message Templates</li>
		</ul>
	</div>
	<div class="above-content"> <a href="javascript:void(0)" id="msgtemplate" class="pull-right "><span class="label label-danger"> Add Message Template</span></a> 
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
								<th>ID</th>
								<th>Message template</th>
								<th>Text Message</th>
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
<div id="modalmsgtemplate" class="modal fade">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    <form name="frm_email_template"  id="frm_email_template" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title headtext">Add Message Template</h5>
      </div>
      <div class="modal-body">
      <div class="row">
            <div class="col-md-3">
            <div class="form-group">
                <label>Message Template Type<span class="mands">*</span></label>
              	<input type="text" name="message_template_type" class="form-control required" id="message_template_type">
            </div>
            </div>
            <div class="col-md-3">
            <div class="form-group">
                <label>Status<span class="mands">*</span></label>
                <select class="form-control required" name="status" id="status">
                <option value="">Select</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
                </select>
            </div>
            </div>
            <!-- <a href="javascript:void(0)" style="margin-top: 2px; float:right; margin-right: 10px;" id="support"><i class="fas fa-question-circle"></i>KeywordsHelp</a> -->
            </div>
             <br>
            <div class="row" style="margin-top:-20px;">
                <div class="col-md-12">
                    <div class="form-group">
                    <label>Message Template Body: <span class="mands">*</span></label>
                    <textarea name="sms_text" class="required form-control" maxlength="140" id="msg_text"></textarea>
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
                <input type="hidden" name="action" id="action" value="message_template_add">
                <input type="hidden" name="msg_temp_id" id="msg_temp_id" value="">
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

	//REMOVE Permission
	// $(document).on('click','.remove_msg_template',function(data,status){
	// 	if(confirm('Do you want to delete message template?')){
	// 		$('.spinner').removeClass('hide');

	// 		var id = $(this).data('id');

	// 		$.post('<?php echo SITEURL ?>ajax/ajss-message-template',{id:id,action:'delete_message_template'},function(data,status){
	// 			if(status == 'success'){
	// 				displayAjaxMsg(data.msg,data.code);
	// 				setTimeout(function() {
	// 					$(".ajaxMsg").html("");
	// 	          }, 8000);
	// 			  fillTable();
	// 			}else{
	// 				displayAjaxMsg(data.msg,data.code);
	// 				setTimeout(function() {
	// 				  $(".ajaxMsg").html("");
	// 	          }, 8000);
	// 			}
	// 		},'json');
	// 	}
	// });

	$(document).on('click', '.remove_msg_template', function(data, status) {
        var id = $(this).data('id');
        $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete message template?',
            buttons: {
                confirm: function () {
                    $('.spinner').removeClass('hide');
                    $.post('<?php echo SITEURL ?>ajax/ajss-message-template', {id:id,action:'delete_message_template'}, function(data, status) {
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



//Add start
     $('#modalmsgtemplate').on('hide.bs.modal', function(e) {
         $('#statusMsg').html('');
         $('#frm_email_template').trigger('reset');
         var validator = $("#frm_email_template").validate();
          validator.resetForm();
    });

     $(document).on('click','#msgtemplate',function() {
     	  $('.headtext').html("Add Message Template");
     	  $('#action').val("message_template_add");
          $('#modalmsgtemplate').modal('show');
     });


     $('#frm_email_template').submit(function(e){
     e.preventDefault();
     if($('#frm_email_template').valid()){
         var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message-template';
         $('#statusMsg').html('Processing...');
            
         var formDate = $(this).serialize();
         $.post(targetUrl,formDate,function(data,status){                    
             if(status == 'success'){
             	
                 if(data.code == 1){
                     fillTable();
					 if($('#action').val() == 'message_template_add'){
					    $('#frm_email_template').trigger('reset');
					  }
					  $('#statusMsg').html(data.msg);
                     setTimeout(function() {
								$("#statusMsg").html('');
								$('#modalmsgtemplate').modal('hide');
					  }, 3000);
                 }else{
					$('#statusMsg').html(data.msg);
                     setTimeout(function() {
								$("#statusMsg").html('');
					  }, 3000);
                 }
             }else{
                  $('#statusMsg').html(data.msg);
                 setTimeout(function() {
								$("#statusMsg").html('');
					  }, 3000);
             }
         },'json');
     }
    });

//Add end


//edit start

    $('#modalmsgtemplate').on('hide.bs.modal', function(e) {
         $('#statusMsg').html('');
         $('#frm_email_template').trigger('reset');
         var validator = $("#frm_email_template").validate();
          validator.resetForm();
    });


 $(document).on('click','.editsmstemp',function() {
 	  $('.headtext').html("Edit Message Template");
 	  $('#msg_temp_id').val($(this).data('id'));
      $('#msg_temp_type_id').val($(this).data('sms_template_id'));
      $('#msg_text').val($(this).data('sms_text'));
      $('#message_template_type').val($(this).data('messagetemplatetype'));
      $('#status').val($(this).data('status'));
      $('#action').val("message_template_edit");
      $('#modalmsgtemplate').modal('show');
 });


//edit end



});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-message-template?action=list_sms_template',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'type_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'sms_text',
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
                          var links = "";
						    links += "<a href='javascript:;'  data-id='"+row['id']+"'  data-sms_template_id='"+row['sms_template_id']+"' data-sms_text='"+row['sms_text']+"' data-status='"+row['status']+"' data-messagetemplatetype='"+row['type_name']+"'  title='Edit' class='text-primary action_link editsmstemp'>Edit</a>";
						  
							links += "<a href='javascript:void(0)' data-id = " + row['id'] + " title='Delete' class = 'text-danger remove_msg_template action_link'>Delete</a>";
						
						
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