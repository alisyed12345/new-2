<?php 
$mob_title = "Admission Request (Completed)";
include "../header.php";


//AUTHARISATION CHECK -
if (!in_array("su_admission_request_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
?>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Admission Request (Completed)</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Admission Request (Completed)</li>
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
        <div class="col-lg-12">
          <table class="table table-bordered dataGrid">
            <thead>
              <tr>
                <th></th>
                <th>Child No</th>
                <th>View Detail</th>
                <th>Req No.</th>
                <th>Student</th>
                <th>Gender</th>
                <th>1st Parent Name</th>
                <th>2nd Parent Name</th>
                <th>School Grade</th>
                <th>Admission Date</th>
                <th>Alloted Group</th>
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
<!-- Add Modal - Admission Request Detail-->
<div id="modal_request_detail" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Request Detail for Request <span id="request_detail_title"></span></h5>
      </div>
      <div class="modal-body" id="request_detail"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</div>
</div>
<!-- /Add modal --> 
<script>
$( document ).ready(function() {
	//FILL TABLE
	fillTable();
	
$(document).on('click','.viewdetail',function(){ 
    var reqno = $(this).data('reqno');
    var childno = $(this).data('childno');
    var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';

    $('#request_detail_title').html('#' + reqno );
    $('#request_detail').html('<h5>Data loading, please wait</h5>');
    $('#modal_request_detail').modal('show');
        
    $.post(targetUrl,{reqno:reqno,childno:childno,action:'view_child_detail'},function(data,status){
      if(status == 'success'){
        $('#request_detail').html(data);
      }
    });
  });
});

function fillTable(){ 
	var table = $('.dataGrid').DataTable({
    autoWidth: false,
		destroy: true,
		responsive: true,
    pageLength: <?php echo TABLE_LIST_SHOW ?>,
		sProcessing:'',		
		language: {
		  loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		ajax: '<?php echo SITEURL ?>ajax/ajss-admission-request?action=list_adm_req_completed',
		'columns': [
    { 'data': 'updated_on' },
		{ 'data': 'child_no' },
		{ 'data': 'student_name'},
		{ 'data': 'req_no' },
		{ 'data': 'student_link',searchable: true,orderable: true }, 
		{ 'data': 'gender',searchable: true,orderable: true },
		{ 'data': 'father_name',searchable: true,orderable: true },
		{ 'data': 'mother_name',searchable: true,orderable: true },
		{ 'data': 'school_grade',searchable: true,orderable: true },
		{ 'data': 'admission_date',searchable: true,orderable: true },	
		{ 'data': 'alloted_group',searchable: true,orderable: true },		 
		],
		"order": [[ 0, "desc" ]],
		"columnDefs": [
			{ "visible": false,  "targets": [ 0,1,2,3] }
        ]
    });
}
</script>
<?php include "../footer.php"?>
