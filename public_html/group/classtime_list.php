<?php 
$mob_title = "Class Time";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!in_array("su_classes_list", $_SESSION['login_user_permissions'])){
include "../includes/unauthorized_msg.php";
return;
} 
?>
<!-- Page header -->

<div class="page-header page-header-default">
<div class="page-header-content">
<div class="page-title">
	<h4 style="display:inline-block">Manage Class Time</h4>
</div>
</div>
<div class="breadcrumb-line">
<ul class="breadcrumb">
	<li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
	<li class="active">Manage Class Time</li>
</ul>
</div>
<?php if (in_array("su_classes_create", $_SESSION['login_user_permissions'])) {   ?>
<div class="above-content"> <a href="classtime_add" class="pull-right"><span class="label label-danger">Add New Class Time</span></a> </div>
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
			<th>GroupDispOrder</th>
			<th>ClassTimeID</th>
			<th>Group</th>
			<th>Class</th>
			<!-- <th>Days</th> -->
			<th>Class From</th>
			<th>Class To</th>
			<!--<th>Assign To</th>-->
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
<!-- Add Modal --> 
<!-- <div id="modal_assign_sheikh" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<form name="frmAssignSheikh" id="frmAssignSheikh" class="form-validate-jquery" method="post">
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h5 class="modal-title">Assign Sheikh To <span id="modal_groupname"></span></h5>
</div>    
<div class="modal-body">
	<div class="row">
	<div class="col-md-12">
		<div class="form-group multi-select-full">
		<select id="staff_user_id" name="staff_user_id" class="select form-control" required>            
		</select>
		</div>
	</div>
	</div>
</div>
<div class="modal-footer">
	<div class="ajaxMsgBot"></div>      
	<button type="submit" class="btn btn-success">Assign</button>
	<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
	<input type="hidden" name="classtimeid" id="classtimeid">
	<input type="hidden" name="action" value="assign_sheikh_to_group">
</div>
</form>
</div>
</div>
</div> --> 
<!-- /Add modal --> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script>
$( document ).ready(function() {
//FILL TABLE
fillTable();
$(document).on('click', '.remove_classtime', function(data, status) {
		var classtime_id = $(this).data('classtimeid');
		var groupid = $(this).data('groupid');
		var classid = $(this).data('classid');
        $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete, class schedule will be permanently delete?',
            buttons: {
                confirm: function () {
                    $('.spinner').removeClass('hide');
					$.post('<?php echo SITEURL ?>ajax/ajss-classes',{classtime_id:classtime_id, groupid:groupid, classid:classid, action:'delete_classtime'},function(data,status){
						if(status == 'success'){
							if(data.code == 1){
								displayAjaxMsg(data.msg,data.code);
								fillTable();
							}else{
								displayAjaxMsg(data.msg,data.code);
							}
						}else{
							displayAjaxMsg(data.msg);
						}
					},'json');
                },
                cancel: function () {
                }
            }
        });
    });

// $(document).on('click','.assignsheikh',function(){ 
// 	var classtimeid = $(this).data('classtimeid');
// 	$('#classtimeid').val(classtimeid);
	
// 	$('#modal_groupname').html($(this).data('groupname'));
	
// 	displayAjaxMsg('Data loading... Please wait',2);	//2 means warning
// 	$('#modal_assign_sheikh').modal('show');
// 	$.post('<?php echo SITEURL ?>ajax/ajss-staff.php',{action:'get_sheikh_to_assign_group',classtimeid:classtimeid},function(data,status){
// 		if(status == 'success'){
// 			hideAjaxMsg();
// 			$('#staff_user_id').html(data);
// 		}
// 	});
// });

// 	$('#frmAssignSheikh').submit(function(e){
// 		e.preventDefault();
	
// 		if($('#frmAssignSheikh').valid()){
// 			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-group.php';
// 			$('.spinner').removeClass('hide');
		
// 			var formDate = $(this).serialize();
// 			$.post(targetUrl,formDate,function(data,status){					
// 				if(status == 'success'){
// 					if(data.code == 1){
// 						displayAjaxMsg(data.msg,data.code);
// 						fillTable();
// 					}else{
// 						displayAjaxMsg(data.msg,data.code);
// 					}
// 				}else{
// 					displayAjaxMsg(data.msg);
// 				}
// 			},'json');
// 		}
// 	});
});

function fillTable(){
var table = $('.datatable-basic').DataTable({
autoWidth: false,
destroy: true,
pageLength: <?php echo TABLE_LIST_SHOW ?>,
responsive: true,
sProcessing:'',		
language: {
		loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
},
ajax: '<?php echo SITEURL ?>ajax/ajss-classes?action=list_classtimes',
'columns': [
	{ 'data': 'disp_order' },
	{ 'data': 'id' },
{ 'data': 'group_name',searchable: true,orderable: true }, 
{ 'data': 'class_name',searchable: true,orderable: true }, 
// { 'data': 'day',searchable: true,orderable: true }, 
{ 'data': 'time_from',searchable: true,orderable: true },
{ 'data': 'time_to',searchable: true,orderable: true },
{ 'data': 'is_active',searchable: true,orderable: true },
],
"order": [[ 2, "asc" ],[ 3, "asc" ]],
"columnDefs": [
	{
		"render": function ( data, type, row ) {

		var btn = ''; 
			<?php  if(in_array("su_classes_edit", $_SESSION['login_user_permissions'])){ ?>
			btn +="<a href='<?php echo SITEURL ?>group/classtime_edit?id=" + row['id'] + "' title='Edit Class Time' class='text-primary action_link overlay_link'>Edit</a>";  
			<?php } ?>   
			if(row['delete'] == ''){
				<?php  if(in_array("su_classes_delete", $_SESSION['login_user_permissions'])){ ?>       
				btn +="<a href='javascript:void(0)' data-classtimeid = " + row['id'] + " data-groupid = "+ row['group_id'] +"  data-classid = "+ row['class_id'] +" title='Remove Class Time' class='text-danger action_link remove_classtime'>Delete</a>";
				<?php } ?>
			}
			return btn;
		}, "targets": 7 },
		{ "visible": false,  "targets": [ 0,1 ] }
	]
});
}
/* <a href='javascript:void(0)' data-classtimeid = " + row['id'] + " title='Remove Class Time' class='text-danger action_link remove_group'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php"?>
