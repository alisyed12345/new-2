<?php 
$mob_title = "Page";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_report_discount", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
if(!empty(get_country()->currency)){
	$currency = get_country()->currency;
}else{
	$currency = '';  
}



$family=$db->get_results("SELECT f.id from ss_student_feesdiscounts as sdf 
inner join ss_user as u on u.id=sdf.student_user_id
inner join ss_student as s on s.user_id=u.id
inner join ss_family as f on f.id=s.family_id
where u.is_active=1 and u.is_deleted=0 
GROUP by f.id");

$total_count = 0;
foreach($family as $family_id){

  $student_count=$db->get_var("SELECT count(id)  FROM `ss_student` WHERE `family_id` = $family_id->id");
  $total_count += $student_count;
 
}

   

?>
<style>
.attendance {
}
.dataGrid.table > tbody > tr > td{
  /* padding-top: 13px !important; */
  padding-bottom: 13px !important;
}
table, td {
            border-bottom: 1px solid black !important;
        }
legend {
  font-size: 12px;
  padding-top: 10px;
  padding-bottom: 0px;
  text-transform: uppercase;
  margin-top: 10px;
}
/*.btn {
  border-radius: 0px !important;
  padding: 0.5rem 1rem;
  float: right;
  font-weight: 500 !important;
}*/

.datatable-btn{
      position: relative;
     display: inline-block; 
    box-sizing: border-box;
    margin-right: 0.333em;
    margin-bottom: 0.333em;
    padding: 0.5em 1em;
    border: 1px solid #999;
    border-radius: 2px;
    cursor: pointer;
    font-size: 0.88em;
    line-height: 1.6em;
    color: black;
    white-space: nowrap;
    overflow: hidden;
    background-color: #e9e9e9;
    background-image: linear-gradient(to bottom, #fff 0%, #e9e9e9 100%);
    
}
.datatable-btn:focus, .datatable-btn:hover {
    border: 1px solid #666;
    background-color: #e0e0e0;
    background-image: linear-gradient(to bottom, #f9f9f9 0%, #e0e0e0 100%);
    color: black;
}
</style>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Discount Report</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Discount Report</li>
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
          <div class="row">
            <div class="col-md-3">
                <span style="font-weight: 700;">Number of student getting discount :  </span> 
                <span ><?php echo $total_count?></span>
            </div>
            <!-- <div class="col-md-3">
                <span style="font-weight: 700;">Total expected monthly fees:</span>
            </div>
            <div class="col-md-2">
                <span style="font-weight: 700;">Total discount:</span>
            </div>
            <div class="col-md-2">
               <span style="font-weight: 700;">Total net fees:</span>
            </div> -->
          <div class="col-md-1" style="float: right;">
                <!-- <a href="<?php echo SITEURL ?>report/pdf/discount_report_pdf" target="_blank" class="datatable-btn">Export PDF</a> </div> -->
                <div class="col-md-1" style="float: right; width: 6.94%;">
             <!-- <a href="<?php echo SITEURL ?>report/excel/discount_report_excel" class="datatable-btn">Export CSV</a> -->
             </div> 
          </div>
        </div>
      </div>
      <div class="row" style="margin-top: 10px;">
        <div class="col-lg-12">
          <table class="table table-bordered data-table dataGrid">
            <thead>
              <tr>          
                <th>1st Parent's Name / 2nd Parent's Name</th>
                <th>Student Name</th>
                <th>(<?php echo $currency;?>) Original Fees</th>
                <th>(<?php echo $currency;?>) Discount</th>
                <th>(<?php echo $currency;?>) Net fees</th>
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

  //FILL TABLE
  fillTable();
});
function fillTable(){ 
  var total = 0;
  var total_monthly_fee = 0;
  var table = $('.dataGrid').DataTable({
    autoWidth: false,
    destroy: true,
    pageLength: 50,
    sProcessing:'',   
    language: {
       loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
    },
    responsive: true,
    ajax: {
            url: "<?php echo SITEURL ?>ajax/ajss-report?action=discount_report",
        },
    'columns': [
      { 'data': 'parent_name',searchable: true,orderable: true, width:'25%'}, 
      { 'data': 'child_name',searchable: true,orderable: true},
      { 'data': 'basic_fee',searchable: true,orderable: true}, 
      { 'data': 'discount_fee',searchable: true,orderable: true},
      { 'data': 'net_fee',searchable: true,orderable: true},
    ],
    "order": [[ 0, "desc" ]],
    "columnDefs": [
      { "visible": true,  "targets": [ 0 ] }
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
                    title :'Manual Payment Report'
                }
        ]
    });
}
</script>

<?php include "../footer.php" ?>
