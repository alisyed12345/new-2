<?php 
$mob_title = "Page";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_report_enrollment", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}

?>
<style type="text/css">
  div.container { max-width: 1200px }
</style>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Enrollment Report</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Enrollment Report</li>
    </ul>
  </div>
</div>

<?php

$classes = $db->get_results("select s.id,s.class_name from ss_classes s where s.is_active = 1 and session= '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

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
               <select class="form-control" name="subject" id="subject" required>
                <option value="">Select Class</option>  
                <?php foreach($classes as $class){ ?>
                <option value="<?php echo $class->id ?>" ><?php echo $class->class_name ?></option>
                <?php } ?>                                  
                </select>         
            </div>

             <div class="form-group col-md-3">
              <?php 
                if(check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')){
                $groups = $db->get_results("SELECT * from ss_groups where is_active = 1 and is_deleted = 0 and session= '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' order by group_name asc"); 
              }else{
                //$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 and id in (select group_id from ss_staffgroupmap where staff_user_id='".$_SESSION['icksumm_uat_login_userid']."' and active = 1) order by group_name asc"); 
                $groups = $db->get_results("select g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id WHERE g.is_active=1 and g.is_deleted=0 and d.session ='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and d.id in (select classtime_id from ss_staffclasstimemap where staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' and active=1) order by group_name asc");
              }
              ?>
              <select class="form-control" id="group" name="group" checkMsgToGroup="true">
                <option value="">Select Group </option>
                <?php foreach($groups as $gr){ ?>
                <option value="<?php echo $gr->id ?>" ><?php echo $gr->group_name ?></option>
                <?php } ?>
              </select>
            </div>
         <div class="form-group col-md-2">
           <a href="javascript:void(0)" id="btnSurveyStatus" class="btn btn-primary">Filter</a>
           <a href="javascript:void(0)" id="btnReset" style="background: #f2af58;" class="btn btn-defult"><span style='color:white;'>Reset </span></a>
         </div>
       
       </div>
      <div class="row">
        <div class="col-lg-12">
          <table class="table table-bordered table-responsive data-table dataGrid display nowrap">
            <thead>
              <tr>
                <th class="all">Enrollment Date</th>
                <th class="all">Student</th>                
                <th class="all">Gender</th>
                <th class="all">DoB</th>
                <th class="all">Grade</th>
                <th class="all">Age (Yrs)</th>
                <th class="all">1st Parent</th>
                <th class="all">2nd Parent</th>
                <th class="none">Primary Email</th>
                <th class="none">Secondary Email</th>
                <th class="none">1st Parent Phone</th>
                <th class="none">2nd Parent Phone</th>
                <!-- <th>Teacher</th> -->
                <?php foreach ($classes as $class) { ?>
                <th class="none"> <?= $class->class_name ?> </th>
                 <?php } ?>

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
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

<script>
$( document ).ready(function() {
   $.cookie('setPagelimit', 25);
  //FILL TABLE
  var getter = $.cookie("setPagelimit");

  fillTable('','',getter);
 
  $('#btnSurveyStatus').on('click',function(){
      var getter = $.cookie("setPagelimit");
        var group = $('#group').val();
        var subject = $('#subject').val();

        $('.data-table').DataTable().destroy();
        fillTable(group, subject,getter);
  });

  $('#btnReset').on('click',function(){
      var getter = $.cookie("setPagelimit");
        $('#group').val('');
        $('#subject').val('');
        $('.data-table').DataTable().destroy();
        fillTable('','',getter);

  });


 

});

function fillTable(group = '' , subject = '',getter){ 
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

  $.extend( jQuery.fn.dataTableExt.oSort, {
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

  var table = $('.dataGrid').DataTable({
    autoWidth: false,
    destroy: true,
    iDisplayLength: getter,
    sProcessing:'',   
    language: {
       loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
    },
    responsive: true,
    ajax: {
            url: "<?php echo SITEURL ?>ajax/ajss-report?action=enroll_report",
             data:{
                    group:group,
                    subject:subject
                  }
        },
    'columns': [
      { 'data': 'created_on',searchable: true,orderable: true, width: "20%", responsivePriority: 1}, 
      { 'data': 'student',searchable: true,orderable: true, width: "11%", responsivePriority: 2},  
      { 'data': 'gender',searchable: true,orderable: true, width: "11%", responsivePriority: 3 }, 
      { 'data': 'dob',searchable: true,orderable: true, width: "8%", responsivePriority: 4 },
      { 'data': 'school_grade',searchable: true,orderable: true, width: "11%", responsivePriority: 5},
      { 'data': 'age',searchable: true,orderable: true, width: "11%", responsivePriority: 6}, 
      { 'data': 'father',searchable: true,orderable: false, responsivePriority: 7},
      { 'data': 'mother',searchable: true,orderable: false , responsivePriority: 8},
      { 'data': 'primary_email',searchable: true,orderable: false, responsivePriority: 9},
      { 'data': 'secondary_email',searchable: true,orderable: false, responsivePriority: 10},
      { 'data': 'father_phone',searchable: true,orderable: false, responsivePriority: 11},
      { 'data': 'mother_phone',searchable: true,orderable: false, responsivePriority: 12},
      /*{ 'data': 'teacher',searchable: true,orderable: true },*/
      <?php foreach ($classes as $class) { ?>
      { 'data': "group<?= $class->id ?>",searchable: true,orderable: true },
      <?php } ?>
     
    ],
    
    "order": [
                [0, "desc"]
            ],
    "columnDefs": [{
            "targets": 0,
            "type": "date"
    }],
     
    // "columnDefs": [
    //   { "visible": false,  "targets": [ 0 ] }
    //     ],
    dom: 'Bfltip',
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


$('.dataGrid').on( 'length.dt', function ( e, settings, len ) {
$.cookie("setPagelimit", len);

} );
</script>
<?php include "../footer.php" ?>
