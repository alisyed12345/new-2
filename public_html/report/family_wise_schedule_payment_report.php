  <?php 
$mob_title = "Page";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_report_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
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
      <h4 style="display:inline-block">Family Wise (Schedule Payment) Report</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Family Wise (Schedule Payment) Report</li>
    </ul>
  </div>
</div>

<?php
   $get_family = $db->get_results("SELECT f.id, CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father_name FROM ss_family f INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_student_fees_items sfi ON sfi.student_user_id = s.user_id GROUP BY f.father_first_name, f.father_last_name");
?>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
<div class="row">
<div class="col-lg-12">
  <div class="panel panel-flat">
    <div class="panel-body">
          <div class="row">
            <div class="form-group col-md-3">
                    <select class="form-control" name="family" id="familyid" required> 
                        <option value="">Select Parent</option> 
                        <?php foreach($get_family as $family){ ?>
                            <option value="<?php echo $family->id ?>"><?php echo $family->father_name ?></option> 
                        <?php } ?>
                    </select>  
            </div>
             <div class="form-group col-md-3">
               <select class="form-control" name="status" id="status" required>
                <option value="">Select Status</option>  
                <option value="5">Schedule</option>
                <option value="1">Success</option>
                <option value="2">Cancel</option>
                <option value="3">Hold</option>
                <option value="4">Decline</option>
                </select>         
            </div>
          
            <a href="javascript:void(0)" id="btnSurveyStatus" class="btn btn-primary">Filter</a>
            <a href="javascript:void(0)" id="btnReset" style="background: #f2af58;" class="btn btn-defult"><span style='color:white;'>Reset </span></a>
         <!-- <div class="form-group col-md-1">
          
         </div>

         <div class="form-group col-md-1">
           
         </div> -->
       </div>
      <div class="row">
        <div class="col-lg-12">
          <table class="table table-bordered data-table dataGrid">
            <thead>
              <tr>
                <th>Child(ren)</th>
                <th>1st Parent</th>
                <th>2nd Parent</th>
                <th>1st Parent Email</th>
                <th>2nd Parent Email</th>
                <th>1st Parent Phone</th>
                <th>2nd Parent Phone</th>
                <th>Schedule Date</th>                
                <th>Payment Date</th>
                <th>Final Amount</th>
                <!-- <th>Payment Txns Id</th> -->
                <th>Status</th>
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
 
  $('#btnSurveyStatus').on('click',function(){
        var family = $('#familyid').val();
        var status = $('#status').val();
        $('.data-table').DataTable().destroy();
        fillTable(family, status);
  });
  
  $('#btnReset').on('click',function(){
        $('#familyid').val('');
        $('#status').val('');
        $('.data-table').DataTable().destroy();
        fillTable();

  });

});

function fillTable(family = '' , status = ''){ 
  var buttonCommon = {
        exportOptions: {
            format: {
                body: function ( data, row, column, node ) {
                    // Strip $ from salary column to make it numeric
                    return data;
                }
            }
        }
    };
  
  var table = $('.dataGrid').DataTable({
    searching: false,
    autoWidth: false,
    destroy: true,
    pageLength: 50,
    sProcessing:'',   
    language: {
       loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
    },
    responsive: true,
    ajax: {
            url: "<?php echo SITEURL ?>ajax/ajss-report?action=family_wise_sche_payment_report",
             data:{
                    family:family,
                    status:status
                   }
        },
    'columns': [ 
      { 'data': 'child_name',searchable: false,orderable: false },
      { 'data': 'father',searchable: false,orderable: false },
      { 'data': 'mother',searchable: false,orderable: false },
      { 'data': 'primary_email',searchable: false,orderable: false },
      { 'data': 'secondary_email',searchable: false,orderable: false },
      { 'data': 'father_phone',searchable: false,orderable: false },
      { 'data': 'mother_phone',searchable: false,orderable: false },
      { 'data': 'schedule_date',searchable: false,orderable: false },
      { 'data': 'payment_date',searchable: false,orderable: false },
      { 'data': 'final_amount',searchable: false,orderable: false },
      { 'data': 'payment_trxn_status',searchable: false,orderable: false },
    ],
    "order": [[ 0, "desc" ]],
    "columnDefs": [
      { "visible": true,  "targets": [ 0 ] }
        ],
    dom: 'Bfltip',
       buttons: [
            'excel', 'print'
        ]
    });
}
</script>
<?php include "../footer.php" ?>
