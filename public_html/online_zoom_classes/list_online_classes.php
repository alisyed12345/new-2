<?php
$mob_title = "Manage Online Classes";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01')) {
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
	<div class="page-header-content">
		<div class="page-title">
			<h4>Manage Online Classes</h4>
		</div>
	</div>
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
			<li class="active">Manage Online Classes</li>
		</ul>
	</div>
 
	<div class="above-content"> <a href="javascript:void(0)" id="addonlineclass" class="pull-right btn btn-primary"><span class=""> Add Online Class</span></a> 
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
                                <th></th>
                                <th>Group Name</th>
								<th>Class Name</th>
								<th>Meeting Url</th>
                                <th>Meeting ID</th>
                                <th>Meeting Password</th>
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
<div id="modalonlineclass" class="modal fade">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    <form name="frmonlineclass" id="frmonlineclass" class="form-validate-jquery" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title headtext">Add Online Class</h5>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-4">
             <div class="form-group">
                <label for="group">Group Name:<span class="mandatory">*</span></label>
                <?php $groups = $db->get_results("select * from ss_groups where is_active = 1 and is_deleted = 0 
				AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' order by group_name asc"); ?>
                <select class="form-control" name="group_id" id="group_id" required>
                <option value="">Select</option>
                <?php foreach($groups as $grp){ ?>
                <option value="<?php echo $grp->id ?>" ><?php echo $grp->group_name ?></option>
                <?php } ?>
                </select>
            </div>
          </div>
          <div class="col-md-4">
             <div class="form-group">
                <label for="group_class">Class Name:<span class="mandatory">*</span></label>
				<?php $classes = $db->get_results("select * from ss_classes where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
				AND is_active = 1 order by class_name asc"); ?>        
				<select class="form-control" name="group_class" id="group_class" required>
					<option value="">Select</option>
					<?php foreach($classes as $cls){ ?>
					<option value="<?php echo $cls->id ?>" ><?php echo $cls->class_name ?></option>
					<?php } ?>              
				</select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
                <label for="meeting_url">Meeting Url:<span class="mandatory">*</span></label>
                <input type="text" class="form-control required" name="meeting_url" id="meeting_url" placeholder="Meeting Url">
            </div>
          </div>
        </div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label for="meeting_id">Meeting ID:<span class="mandatory">*</span></label>
					<input type="text" class="form-control required" name="meeting_id" MeetingID="true" maxlength="10" id="meeting_id" placeholder="Meeting ID">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="meeting_password">Meeting Password:<span class="mandatory">*</span></label>
					<input type="text" class="form-control required" name="meeting_password" id="meeting_password" MeetingID="true" maxlength="6" placeholder="Meeting Password">
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
                <input type="hidden" name="action" id="action" value="online_classes_add">
                <input type="hidden" name="online_class_id" id="online_class_id" value="">
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

        jQuery.validator.addMethod("MeetingID", function(value, element) {
			return this.optional(element) || /^([0-9]{1,10})$/i.test(value);
        }, "Please enter a valid meeting");


	//REMOVE Permission
	$(document).on('click','.remove_online_class',function(data,status){
		if(confirm('Do you want to delete online class?')){
			$('.spinner').removeClass('hide');

			var id = $(this).data('id');

			$.post('<?php echo SITEURL ?>ajax/ajss-online-zoom-classes',{id:id,action:'delete_online_class'},function(data,status){
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
		}
	});



//Add Basic fees start
     $('#modalonlineclass').on('hide.bs.modal', function(e) {
         $('#statusMsg').html('');
         $('#frmonlineclass').trigger('reset');
         var validator = $("#frmonlineclass").validate();
          validator.resetForm();
    });

     $(document).on('click','#addonlineclass',function() {
     	  $('.headtext').html("Add Online Class");
     	  $('#action').val("online_classes_add");
          $('#modalonlineclass').modal('show');
     });


     $('#frmonlineclass').submit(function(e){
     e.preventDefault();
     if($('#frmonlineclass').valid()){
         var targetUrl = '<?php echo SITEURL ?>ajax/ajss-online-zoom-classes';
         $('#statusMsg').html('Processing...');
            
         var formDate = $(this).serialize();
         $.post(targetUrl,formDate,function(data,status){                    
             if(status == 'success'){
             	 $('#statusMsg').html(data.msg);
                 if(data.code == 1){
                     fillTable();
					 if($('#action').val() == 'online_classes_add'){
					    $('#frmonlineclass').trigger('reset');
					  }
                     displayAjaxMsg(data.msg,data.code);
                 }else{
                     displayAjaxMsg(data.msg,data.code);
                 }
             }else{
                 displayAjaxMsg(data.msg);
             }
         },'json');
     }
    });

//Add Basic fees end


//edit Basic fees start

    $('#modalonlineclass').on('hide.bs.modal', function(e) {
         $('#statusMsg').html('');
         $('#frmonlineclass').trigger('reset');
         var validator = $("#frmonlineclass").validate();
          validator.resetForm();
    });


 $(document).on('click','.editonlineclasses',function() {
 	  $('.headtext').html("Edit Online Class");
 	  $('#online_class_id').val($(this).data('id'));
      $('#group_id').val($(this).data('group_id'));
      $('#group_class').val($(this).data('class_id'));
	  $('#meeting_url').val($(this).data('meeting_url'));
	  $('#meeting_id').val($(this).data('meeting_id'));
	  $('#meeting_password').val($(this).data('meeting_password'));
      $('#status').val($(this).data('status'));
      $('#action').val("online_classes_edit");
      $('#modalonlineclass').modal('show');
 });


//edit Basic fees end



});

	function fillTable() {
		var table = $('.datatable-basic').DataTable({
			autoWidth: false,
			destroy: true,
			pageLength: <?php echo TABLE_LIST_SHOW ?>,
			responsive: true,
			ajax: '<?php echo SITEURL ?>ajax/ajss-online-zoom-classes?action=list_online_zoom_classes',
			sProcessing: '',
			language: {
				loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
			},
			'columns': [{
					'data': 'id'
				},
				{
					'data': 'group_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'class_name',
					searchable: true,
					orderable: true
				},
				{
					'data': 'meeting_url',
					searchable: true,
					orderable: true
				},
				{
					'data': 'meeting_id',
					searchable: true,
					orderable: true
				},
				{
					'data': 'meeting_password',
					searchable: true,
					orderable: true
				},
				{
					'data': 'is_active',
					searchable: true,
					orderable: true
				},
			],
			
			"columnDefs": [{
					"render": function(data, type, row) {
                          var links = "";
                           
						    links += "<a href='javascript:;'  data-id='"+row['id']+"'  data-group_id='"+row['group_id']+"' data-class_id='"+row['class_id']+"' data-meeting_url='"+row['meeting_url']+"' data-meeting_id='"+row['meeting_id']+"' data-meeting_password='"+row['meeting_password']+"' data-status='"+row['status']+"'  title='Edit' class='text-primary action_link editonlineclasses'>Edit</a>";
						   
							links += "<a href='javascript:void(0)' data-id = " + row['id'] + " title='Delete' class = 'text-danger remove_online_class action_link'>Delete</a>";
						
						
						// return [links, attechment];
						return links;
					},
					"targets": 7
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