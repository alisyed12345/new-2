<?php 
$mob_title = "Page";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_report_refund", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
if(!empty(get_country()->currency)){
	$currency = get_country()->currency;
}else{
	$currency = '';
}
?>
<style>
.attendance {
}
</style>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Refund Payment Report</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Refund Payment Report</li>
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
        <div class="col-md-12">
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <table class="table table-bordered dataGrid">
            <thead>
              <tr>
              <th>Date</th>
                <th>Primary Email</th>
                <th>1st Parent</th>
                <th>1st Parent Phone</th>
                <th>Reason</th>
                <th>(<?php echo $currency;?>) Amount</th>
                <th>(<?php echo $currency;?>) Refunded Amount</th>
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
</div>
</div>

<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script> 

<script>

$( document ).ready(function() {

  fillTable();

function fillTable(){ 
  var table = $('.dataGrid').DataTable({
    autoWidth: false,
    // destroy: true,
    pageLength: 50,
    // sProcessing:'',
    searchable: true,   
    language: {
    loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
    },
    responsive: true,
    ajax: {
            url: "<?php echo SITEURL ?>ajax/ajss-report?action=refund_payment_report",
  
        },
      'columns': [ 
      { 'data': 'payment_date',searchable: true,orderable: true },
      { 'data': 'primary_email',searchable: true,orderable: true },
      { 'data': 'father_name',searchable: true,orderable: true },
      { 'data': 'father_phone',searchable: true,orderable: true },
      { 'data': 'reason',searchable: true,orderable: true },
      { 'data': 'amount',searchable: true,orderable: true },
      { 'data': 'refund_amount',searchable: true,orderable: true },
    ],
    dom: 'Bfltip',
    buttons: [
                {
                    extend: 'pdf',
                    // extend: 'print',
                    // exportOptions: {
                    //     columns: [ 0, 1, 2, 5, 6, 7, 8, 9 ]
                    // },
                    //Scale: '137',
                    Destination: 'Microsoft Print to PDF',
                },
                {
                    extend: 'excel',
                    title :'Refund Payment Report'
                }
        ]
    });
}
});
</script>
<?php include "../footer.php" ?>
