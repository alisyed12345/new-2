<?php include "../header.php" ;
if (!in_array("su_staff_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  exit;
}

?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Staff</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Staffs</li>
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
          <div class="ajaxMsg"></div>
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
                <th>User ID</th>
                <th>Staff Name</th>
                <th>Email</th>
                <th>Joining Date</th>
                <th>Type</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
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
<!-- Add Modal - Staff Detail-->
<div id="modal_staff_detail" class="modal fade">
<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h5 class="modal-title">Staff Detail <span id="staffinfo_title"></span></h5>
    </div>
    <div class="modal-body viewonly" id="staff_detail"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
</div>
<!-- /Add modal -->
<script>
var table;

$( document ).ready(function() {
	//FILL TABLE
	fillTable(); 
	 
	//FETCH STAFF DETAILS
	$(document).on('click','.viewdetail',function(){ 
		var userid = $(this).data('userid');
		var staffname = $(this).data('staffname');
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff';

		$('#staffinfo_title').html(' - ' + staffname );
		$('#staff_detail').html('<h5>Data loading... Please wait</h5>');
		$('#modal_staff_detail').modal('show');
				
		$.post(targetUrl,{userid:userid,action:'view_staff_detail'},function(data,status){
			if(status == 'success'){
				$('#staff_detail').html(data);
			}
		});
	});
	
	//REMOVE STAFF
	$(document).on('click','.remove_staff',function(data,status){
		if(confirm('Do you want to delete staff?')){
			$('.spinner').removeClass('hide');
			
			var user_id = $(this).data('staffid');
						
			$.post('<?php echo SITEURL ?>ajax/ajss-staff',{user_id:user_id,action:'delete_staff'},function(data,status){
				if(status == 'success'){
					fillTable();
					displayAjaxMsg(data.msg,data.code);
				}else{
					displayAjaxMsg(data.msg,data.code);
				}
			},'json');
		}
	});
	
	//ADDED ON 21-MAR-2018
    $('.datatable-basic tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
	});
});

function format ( d ) {
    // `d` is the original data object for the row
    return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
        '<tr>'+
            '<td>Full name:</td>'+
            '<td>'+d.email+'</td>'+
        '</tr>'+
        '<tr>'+
            '<td>Extension number:</td>'+
            '<td>'+d.joining_date+'</td>'+
        '</tr>'+
        '<tr>'+
            '<td>Extra info:</td>'+
            '<td>And any further details here (images etc)...</td>'+
        '</tr>'+
    '</table>';
}

function fillTable(){
	table = $('.datatable-basic').DataTable({
        autoWidth: false,
		destroy: true,
		responsive: true,
		ajax: '<?php echo SITEURL ?>ajax/ajss-staff?action=list_all_staff',
		sProcessing:'',		
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		'columns': [
		{ 'data': 'user_id' },
		{ 'data': 'staff_name',searchable: true,orderable: true, className:'details-control' }, 
		{ 'data': 'email',searchable: true,orderable: true },
		{ 'data': 'joining_date',searchable: true,orderable: true },
		{ 'data': 'user_type',searchable: true,orderable: true },
		{ 'data': 'status',searchable: true,orderable: true },
		{ 'data': 'joining_date' }
		],
		"columnDefs": [
		{
			"render": function ( data, type, row ) { 
				<?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
				if(check_userrole_by_code('UT01')){ ?>
				return "<a href='#' class='text-warning action_link viewdetail' data-staffname='" + row['staff_name'] + "' data-userid='" + row['user_id'] + "'><i class = 'icon-eye' ></i></a><a href='<?php echo SITEURL ?>staff/staff_edit?id=" + row['user_id'] + "' class='text-primary action_link'><i class = 'icon-pencil5' ></i></a>";
				<?php }elseif(check_userrole_by_code('UT04')){ ?>
				return "<a href='#' class='text-warning action_link viewdetail' data-staffname='" + row['staff_name'] + "' data-userid='" + row['user_id'] + "'><i class = 'icon-eye' ></i></a>";
				<?php } ?>
			}, "targets": 6 },
			{ "visible": false,  "targets": [ 0 ] }
        ]
    });
}

/*<a href='javascript:void(0)' data-staffid = " + row['user_id'] + " class = 'text-danger remove_staff action_link'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php"?>
