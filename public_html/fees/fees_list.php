<?php 
$mob_title = "Payments";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!check_userrole_by_code('UT01') && !check_userrole_by_code('UT04')){
	include "../includes/unauthorized_msg.php";
	return;
} 
?>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Payments</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Payments</li>
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
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
              	<td>PaymentID</td>
                <td>Student</td>
                <th>Group</th>
                <th>Month</th>
                <th>Amount</th>
                <th>Remarks</th>
                <th>Paid On</th>
                <th class="action_col text-center"></th>
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
<script>
$( document ).ready(function() {
	//FILL TABLE
	fillTable();
	
	$(document).on('click','.delete_fees',function(data,status){
		if(confirm('Do you want to delete payment?')){
			$('.spinner').removeClass('hide');
			
			var feesid = $(this).data('feesid');
						
			$.post('<?php echo SITEURL ?>ajax/ajss-fees',{feesid:feesid,action:'delete_fees'},function(data,status){
				if(status == 'success'){
					fillTable();
					displayAjaxMsg(data.msg,data.code);
				}else{
					displayAjaxMsg(data.msg,data.code);
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
		order: [[0, 'desc']],
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		ajax: '<?php echo SITEURL ?>ajax/ajss-fees?action=list_fees',
		'columns': [
		{ 'data': 'id' },
		{ 'data': 'student_name',searchable: true,orderable: true }, 
		{ 'data': 'group_name',searchable: true,orderable: true }, 
		{ 'data': 'month_name',searchable: true,orderable: true }, 
		{ 'data': 'amount',searchable: true,orderable: true },
		{ 'data': 'remarks',searchable: true,orderable: true },
		{ 'data': 'paid_on',searchable: true,orderable: true },
		],
		"columnDefs": [
		{
			"render": function ( data, type, row ) { 
				return "<a href='<?php echo SITEURL ?>fees/fees_edit?id=" + row['id'] + "' title='Edit Payment' class='text-primary action_link overlay_link'>Edit</a><a href='javascript:void(0)' data-feesid = " + row['id'] + " title='Delete Payment' class='text-danger action_link delete_fees'>Delete</a>";
			}, "targets": 7 },
			{ "visible": false,  "targets": [ 0 ] }
        ]
    });
}
</script>
<?php include "../footer.php"?>
