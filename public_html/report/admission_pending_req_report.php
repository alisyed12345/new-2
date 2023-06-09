<?php 
$mob_title = "Page";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_report_admission_request", $_SESSION['login_user_permissions'])) { 
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
      <h4 style="display:inline-block">Admission Request (Pending) Report</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Admission Request (Pending) Report</li>
    </ul>
  </div>
</div>

<?php
  $classes = $db->get_results("select s.id,s.class_name from ss_classes s where s.is_active = 1 ");

?>
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
          <table class="table table-bordered data-table dataGrid">
            <thead>
              <tr>
                <th>Admission Request No.</th>
                <th>Student</th>                
                <th>Gender</th>
                <th>DoB</th>
                <th>Grade</th>
                <th>Age (Yrs)</th>
                <th>1st Parent</th>
                <th>Primary Email</th>
                <th>1st Parent Phone</th>
                <!-- <th>Class Session</th> -->
                <th>Admission Request Date</th>
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
    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
"date-uk-pre": function ( a ) {
    var ukDatea = a.split('/');
    return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
},

"date-uk-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
},

"date-uk-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
}
} );
$( document ).ready(function() {
  //FILL TABLE
  fillTable();

});

function fillTable(group = '' , subject = ''){ 
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
    autoWidth: false,
    destroy: true,
    pageLength: 50,
    sProcessing:'',   
    language: {
    loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
    },
    responsive: true,
    ajax: {
            url: "<?php echo SITEURL ?>ajax/ajss-report?action=admission_pending_req_report",
            data:{
                    group:group,
                    subject:subject
                  }
        },
    'columns': [
      { 'data': 'admreq_id',width:'15%',searchable: true,orderable: true},
      { 'data': 'student',width:'10%',searchable: true,orderable: true},  
      { 'data': 'gender',width:'6%',searchable: true,orderable: true}, 
      { 'data': 'dob',searchable: true,orderable: true},
      { 'data': 'school_grade',width:'10%',searchable: true,orderable: true},
      { 'data': 'age',width:'7%',searchable: true,orderable: true}, 
      { 'data': 'father',width:'14%',searchable: true,orderable: true },
      { 'data': 'primary_email',searchable: true,orderable: true },
      { 'data': 'father_phone',width:'10%',searchable: true,orderable: true },
      // { 'data': 'class_session',searchable: true,orderable: false },
      { 'data': 'created_on',searchable: true,orderable: true,width:'31%'}, 
    ],
    "order": [[ 0, "desc" ]],
    "columnDefs": [
                {
                    "targets": 3,
                    "type": "date-uk" 
                },
      { "visible": true,  "targets": [ 0 ] }
        ],
    dom: 'Bfltip',
    // buttons: [
    //             {
    //               extend: 'pdf',
    //                 // extend: 'print',
    //                 // exportOptions: {
    //                 //     columns: [ 0, 1, 2, 5, 6, 7, 8, 9 ]
    //                 // },
    //                 //Scale: '137',
    //                 Destination: 'Microsoft Print to PDF',
    //             },
    //             {
    //                 extend: 'excel',
    //                 title :'Admission Request (Pending) Report'
    //             }
    //     ]
    buttons: [
                {
                    // extend: 'print',
                    // // exportOptions: {
                    // //     columns: [ 1, 2, 5, 6, 7, 8, 9, 10, 11, 12 ]
                    // // },
                    // //Scale: '137',
                    // Destination: 'Microsoft Print to PDF',

                    extend : 'pdfHtml5',
                // title : function() {
                //     return "ABCDE List";
                // },
                orientation : 'landscape',
                pageSize : 'A3',
                text : '<i class="fa fa-file-pdf-o"> PDF</i>',
                titleAttr : 'PDF',
                title:'Enrollment Report',
                },
                {
                    extend: 'excel'
                }
        ]
    });
}
</script>
<?php include "../footer.php" ?>
