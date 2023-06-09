<?php
$mob_title = "Classes";
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
      <h4 style="display:inline-block">Classes</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Classes</li>
    </ul>
  </div>
  <div class="above-content"> <a href="javascript:void(0)" id="linkAddClass" class="pull-right"><span class="label label-danger">Add Class</span></a> </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat">
        <div class="panel-body">
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
				<th>Class</th>
				<th>Status</th>
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

<!-- Add Modal -->
<div id="modal_add_class" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
    <form name="frmClass" id="frmClass" class="form-validate-jquery" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Add Class</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8">
            <div class="form-group">
              <label>Class Name:</label>
              <input type="text" name="class_name" id="class_name" class="form-control" required>
            </div>
          </div>
		      <div class="col-md-4">
            <div class="form-group">
              <label>Status:</label>
              <select class="form-control" required name="is_active" id="is_active">
              <option value="">Select</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
              </select>
            </div>
          </div>          
        </div>
      </div>
      <div class="modal-footer">
        <div class="ajaxMsgBot"></div>
          <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" name="class_id" id="class_id">
          <input type="hidden" name="action" value="save_class">
      </div>
      </form>  
    </div>
  </div>
</div>
<!-- /Add modal --> 
<script>
$( document ).ready(function() {
	//FILL TABLE
	fillTable();

  $('#linkAddClass').click(function(){
    $('.modal-title').html('Add Class');
    $('#class_id').val('');
    $('#modal_add_class').modal('show');
  });

	$(document).on('click','.delete_class',function(data,status){
		if(confirm('Do you want to delete class?')){
			$('.spinner').removeClass('hide');

			var class_id = $(this).data('classid');

			$.post('<?php echo SITEURL ?>ajax/ajss-classes',{class_id:class_id,action:'delete_class'},function(data,status){
				if(status == 'success'){
					fillTable();
					displayAjaxMsg(data.msg,data.code);
				}else{
					displayAjaxMsg(data.msg,data.code);
				}
			},'json');
		}
	});

  $(document).on('click','.edit_class',function(data,status){
    displayAjaxMsg("Data loading, please wait",2);
    $('.modal-title').html('Edit Class');
    $('#modal_add_class').modal('show');

    var class_id = $(this).data('classid');

    $.post('<?php echo SITEURL ?>ajax/ajss-classes',{class_id:class_id,action:'fetch_class'},function(data,status){
      if(status == 'success'){
        if(data.code == 1){
          $('#is_active').val(data.is_active);
          $('#class_name').val(data.class_name);
          $('#class_id').val(data.id);

          hideAjaxMsg();
        }
      }
    },'json');
	});

	$('#frmClass').submit(function(e){
		e.preventDefault();

		if($('#frmClass').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
			$('.spinner').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){
				if(status == 'success'){
					if(data.code == 1){
            $('#modal_add_class').modal('hide');
						displayAjaxMsg(data.msg,data.code);
            $("#frmClass")[0].reset();
						fillTable();
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
	});
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
		ajax: '<?php echo SITEURL ?>ajax/ajss-classes?action=list_all_classes',
		'columns': [
			{ 'data': 'class_name',searchable: true,orderable: true },
			{ 'data': 'is_active',searchable: true,orderable: true },
		],
		"columnDefs": [
		{
			"render": function ( data, type, row ) {
				return "<a href='javascript:void(0)' data-classid = " + row['id'] + " title='Edit Class' class='text-primary action_link edit_class'>Edit</a><a href='javascript:void(0)' data-classid = " + row['id'] + " title='Delete Class' class='text-danger action_link delete_class'>Delete</a>";
			}, "targets": 2 },
        ]
    });
}
/* 
<a href='javascript:void(0)' title='Delete Class' data-class='" + row['group_name'] + "' data-classid=" + row['id'] + " class='action_link text-success assignsheikh'><i class='icon-pushpin'></i></a>

<a href='javascript:void(0)' data-classid = " + row['id'] + " title='Remove Group' class='text-danger action_link delete_class'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php"?>
