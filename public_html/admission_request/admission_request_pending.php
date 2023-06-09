<?php
$mob_title = "Admission Request (Pending)";
include "../header.php";

//AUTHARISATION CHECK -
if (!in_array("su_admission_request_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
?>
<style type="text/css">
  label.error {
    color: red;
  }
 .clname{
   margin-top: 7px;
 }

 .groupside {
  margin-left: -70px;
 }

 .classside{
   margin-left: 15px;
  }

  #dataTables_Filter{
        float: left;
    }
 
</style>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Admission Request <?php echo is_numeric($_GET['reqid']) ? ('#' . $_GET['reqid']) : '' ?> (Registered/Waiting)</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Admission Request (Registered/Waiting)</li>
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
          <div class="row">
            <div class="col-lg-12">
            <span style="float: right;margin-left: 2px; margin-bottom:10px;">Show Deleted Records</span><input type="checkbox" name="delete_request" id="delete_request" value="1" class="record" style="float: right; margin-bottom:10px;">
              <table class="table table-bordered dataGrid">
                <thead>
                  <tr>
                    <th>Child No</th>
                    <th>View Detail</th>
                    <th>Req No.</th>
                    <th>Student</th>
                    <th>Gender</th>
                    <th>1st Parent Name</th>
                    <th>2nd Parent Name</th>
                    <th>School Grade</th>
                    <th>DoB</th>
                    <th>Status</th>
                    <!-- <th>Class Session Time</th> data-sort='YYYYMMDD'-->
                    <th>Online Reg. Date</th>
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
  </div>

</div>
<!-- Add Modal -->
<div id="modal_schedule_interview" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frmScheduleInterview" id="frmScheduleInterview" class="form-validate-jquery" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title">Schedule Interview for <span id="modal_studentname"></span></h5>
        </div>
        <div class="modal-body">
          <div class="ajaxMsg"></div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <div class="input-group"> <span class="input-group-addon"><i class="icon-calendar22"></i></span>
                  <input class="form-control pickadate" required id="interview_date" name="interview_date" type="text">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" name="sch_int_reqno" id="sch_int_reqno">
          <input type="hidden" name="sch_int_childno" id="sch_int_childno">
          <input type="hidden" name="action" value="schedule_interview">
        </div>
      </form>
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

<!-- Add Modal - Assign Group-->
<div id="modal_assign_group" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frmAssignGroup" id="frmAssignGroup" class="form-validate-jquery" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title">Assign Group To <span id="modal_title_studentname"></span></h5>
        </div>
        <div class="modal-body">
          <?php
          $get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
          if ($get_general_info == 1) {
            $groups = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
              and (is_active=1 or is_active=2) and is_deleted=0 ORDER BY id ASC LIMIT 1");
          ?>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group multi-select-full">
                  <select id="group_id" name="group_id" class="form-control required" style="width:100%">
                    <?php foreach ($groups as $grp) { ?>
                      <option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
          <?php } ?>
          <?php if ($get_general_info == 0) {
            $groups = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                      and is_active=1 and is_deleted=0");
            $classes = $db->get_results("select * from ss_classes where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and is_active=1"); ?>
            <!--   <div class="row"> -->
            <div class="row">
            <?php $cnt = 1;
            $total = count($classes);
            foreach ($classes as $key => $class) { ?>
                <div class="col-md-3" style="margin-bottom:15px;">
                  <div class="form-group clname">
                    <input type="hidden" name="class[]" value="<?php echo $class->id ?>">
                    <span class="classside"><?php echo $class->class_name; ?></span>
                  </div>
                </div>

                <div class="col-md-3" style="margin-bottom:15px;">
                  <div class="form-group multi-select-full">
                    <select name="group_id<?php echo $class->id ?>" id="group_id<?php echo $class->id ?>" class="form-control groupside required" required>
                      <option value="" selected="">Select</option>
                      <?php foreach ($groups as $grp) { ?>
                        <option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
            <?php } ?>
              </div>
          <?php } ?>
          <div id="discount_boxes">
  
          </div> 
        </div>
        <div class="modal-footer">
          <div class="ajaxMsgBot"></div>
          <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Assign</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" name="reqno" id="assign_gr_reqno">
          <input type="hidden" name="childno" id="assign_gr_childno">
          <input type="hidden" name="action" value="assign_group_to_new_student">
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="http://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
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

  $(document).ready(function() {
    $('#interview_date').pickadate({
      min: [<?php echo date('Y') ?>, <?php echo date('m') ?>, <?php echo date('d') ?>],
      formatSubmit: 'yyyy-mm-dd'
    });
    //FILL TABLE
    fillTable();
    $(document).on('click', '.viewdetail', function() {
      var reqno = $(this).data('reqno');
      var childno = $(this).data('childno');
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';
      $('#request_detail_title').html('#' + reqno);
      $('#request_detail').html('<h5>Data loading, please wait</h5>');
      $('#modal_request_detail').modal('show');
      $.post(targetUrl, {
        reqno: reqno,
        childno: childno,
        action: 'view_child_detail'
      }, function(data, status) {
        if (status == 'success') {
          $('#request_detail').html(data);
        }
      });
    });

    //Add Basic fees start
    $('#modal_assign_group').on('hide.bs.modal', function(e) {
        $('.ajaxMsgBot').html('');
        $('#frmAssignGroup').trigger('reset');
        var validator = $("#frmAssignGroup").validate();
        validator.resetForm();
        $('.groupselect').val('');
    });

    $(document).on('click','.assigngroup',function(){
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';
      req_no = $(this).data('reqno');
      all_data={
        'req_no':req_no,
        'action':'assign_gr_discount'
      }
      $.post(targetUrl, all_data, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
                $('#modal_assign_group').modal('show');
                $('#discount_boxes').html(data.msg)
            }else{
                $('#modal_assign_group').modal('show');
                $('#discount_boxes').html('')
            }
          } else {
                $('#modal_assign_group').modal('show');
                $('#discount_boxes').html('')
          }
        }, 'json');
        $('#modal_title_studentname').html($(this).data('studentname'));
        $('#assign_gr_reqno').val($(this).data('reqno'));
        $('#assign_gr_childno').val($(this).data('childno'));
    });

    $(document).on('click', '.assigninterviewdt', function() {
      var reqno = $(this).data('reqno');
      $('#sch_int_reqno').val(reqno);
      $('#sch_int_childno').val($(this).data('childno'));
      $('#modal_studentname').html($(this).data('studentname') + ' (Req #' + reqno + ')');
      $('#modal_schedule_interview').modal('show');
    });

    $('#frmAssignGroup').submit(function(e) {
      e.preventDefault();
      if ($('#frmAssignGroup').valid()) {
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';
        $('.spinner').removeClass('hide');
        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              $('#modal_assign_group').modal('hide');
              fillTable();
            } else {
              displayAjaxMsg(data.msg, data.code);
              // $('#modal_assign_group').modal('hide');
            }
          } else {
            displayAjaxMsg(data.msg);
          }
        }, 'json');
      }
    });

    $('#frmScheduleInterview').submit(function(e) {
      e.preventDefault();
      if ($('#frmScheduleInterview').valid()) {
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';
        $('.spinner').removeClass('hide');
        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              fillTable();
            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          } else {
            displayAjaxMsg(data.msg);
          }
        }, 'json');
      }
    });

    $('#delete_request').on('change', function() {
            if ($('.record').is(":checked")) {
                $delete_request = $(this).val();
                fillTable($delete_request);
            } else {
                fillTable();
            }
    });

    $(document).on('click', '.remove_request', function(e) {
      e.preventDefault();
      var studentname = $(this).data('studentname');
      var childno = $(this).data('childno');
      var reqno = $(this).data('reqno');
       $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete record of request number ' + reqno + '?',
            buttons: {
                confirm: function () {
                  var targetUrl = '<?php echo SITEURL ?>ajax/ajss-admission-request';
                  $('.spinner').removeClass('hide');
                  var formDate = $(this).serialize();
                    $.post(targetUrl, {
                    'action': 'remove_request',
                    childno: childno,
                    reqno: reqno
                    }, function(data, status) {
                        if (status == 'success') {
                          if (data.code == 1) {
                          displayAjaxMsg(data.msg, data.code);
                          fillTable();
                          } else {
                          displayAjaxMsg(data.msg, data.code);
                          }
                        } else {
                        displayAjaxMsg(data.msg);
                        }
                    }, 'json');
                },
                cancel: function () {
                }
              }
              })

      // if (confirm('Do you want to delete record of request number ' + reqno + '?')) {
       
      // }
    });
  });

  function fillTable($delete_request) {
    <?php if (isset($_GET['reqid'])) {
      $reqid = $_GET['reqid'];
    } else {
      $reqid = "";
    } ?>
    var table = $('.dataGrid').DataTable({
      autoWidth: false,
      destroy: true,
      responsive: true,
      pageLength: <?php echo TABLE_LIST_SHOW ?>,
      ajax: {
                "url": '<?php echo SITEURL ?>ajax/ajss-admission-request',
                "type": "post",
                "data": function(d) {
                    d.action = "list_adm_req_pending";
                    d.reqid = '<?php echo $reqid ?>';
                    d.delete_request = $delete_request;
                }
            },
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      'columns': [{
          'data': 'child_no'
        },
        {
          'data': 'student_name'
        },
        {
          'data': 'req_no',
          "width": "8%"
        },
        {
          'data': 'student_link',
          searchable: true,
          orderable: true
        },
        {
          'data': 'gender',
          "width": "8%",
          searchable: true,
          orderable: true
        },
        {
          'data': 'father_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'mother_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'school_grade',
          searchable: true,
          orderable: true
        },
        {
          'data': 'dob',
          searchable: true,
          orderable: true
        },
         {
          'data': 'is_waiting',
          "width": "8%",
          searchable: true,
          orderable: true
        },
        // { 'data': 'class_session',searchable: true,orderable: true },
        {
          'data': 'created_on',
          "width": "10%",
          searchable: true,
          orderable: true
        },
      ],
      "order": [
        [2, "desc"]
      ],
      "columnDefs": [{
                    "targets": 8,
                    "type": "date-uk" 
                },
          {
          "render": function(data, type, row) {
            var actions = "";
            // <a href='javascript:void(0)' title='Schedule Interview' data-childno='" + row['child_no'] + "' data-reqno='" + row['req_no'] + "' data-studentname='" + row['student_name'] + "' class='action_link text-primary assigninterviewdt'><i class='icon-calendar2'></i></a>
            if(row['is_delete'] == 0){
             
             
              <?php if (in_array("su_admission_request_edit", $_SESSION['login_user_permissions'])) { ?>
              actions += "<a href='<?php  echo SITEURL ?>admission_request/edit_admission_request_pending?id=" + row['req_no'] + "' title='Edit Admission Request' data-childno='" + row['child_no'] + "' class='action_link text-primary'><i class='icon-pencil7'></i></a>";
              <?php } ?>


              <?php if (in_array("su_admission_request_delete", $_SESSION['login_user_permissions'])) { ?>
              actions += "<a href='javascript:void(0)' title='Delete Admission Request' data-childno='" + row['child_no'] + "' data-reqno='" + row['req_no'] + "' data-studentname='" + row['student_name'] + "' class='action_link text-danger remove_request'><i class='icon-trash'></i></a>";
              <?php } ?>

              <?php if (in_array("su_admission_request_assign_group", $_SESSION['login_user_permissions'])) { ?>
              actions += "<a href='javascript:void(0)' title='Assign Group' data-childno='" + row['child_no'] + "' data-reqno='" + row['req_no'] + "' data-studentname='" + row['student_name'] + "' class='action_link text-success assigngroup'><i class='icon-pushpin'></i></a>";
              <?php } ?>
            }
            //if($.trim(row['interview'])){
            //}

            return actions;

          },
          "targets": 11
        },
        {
          "visible": false,
          "targets": [0, 1]
        }
      ]
    });
    <?php if (isset($_GET['reqid'])) { ?>
      table.columns(2).search('<?php echo $_GET['reqid'] ?>').draw();
    <?php } ?>
  }
</script>
<?php include "../footer.php" ?>