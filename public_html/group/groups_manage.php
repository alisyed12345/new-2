<?php
$mob_title = "Groups";
include "../header.php";


//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_group_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}

$all_groups = $db->get_results("SELECT id,group_name, max_limit,category,is_regis_open,
(CASE WHEN is_deleted=1 THEN 'Deleted' WHEN is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status from ss_groups", ARRAY_A);

$group_strength_total = 0;
$group_active_strength_total = 0;

for ($i = 0; $i < count((array)$all_groups); $i++) {

  $group_strength = $db->get_results("SELECT DISTINCT sgm.student_user_id FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
  INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id

  WHERE ssm.session_id  = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and sgm.group_id = '" . $all_groups[$i]['id'] . "' AND sgm.latest = 1 AND u.is_deleted=0");

  $group_active_strength = $db->get_results("SELECT DISTINCT sgm.student_user_id FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
  WHERE ssm.session_id  = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and sgm.group_id = '" . $all_groups[$i]['id'] . "' AND sgm.latest = 1 AND  u.is_active =1 AND u.is_deleted=0");

  $group_strength_total += count((array)$group_strength);
  $group_active_strength_total += count((array)$group_active_strength);
}
?>
<!-- Page header -->
<style>
  span.mands {
    color: #ff0000;
    display: inline;
    line-height: 1;
    font-size: 12px;
    margin-left: 5px;
}
</style>
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Manage Groups</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Manage Groups</li>
    </ul>
  </div>
  <div class="above-content">
  <?php if (in_array("su_group_create", $_SESSION['login_user_permissions'])) {   ?>
     <a href="javascript:void(0)" id="linkAddGroup" class="pull-right"><span class="label label-danger">Add Group</span></a>
     <?php  } ?>
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
          <!-- <div class="row">
            <div class="col-md-6">
            <h6> Total Strength / Total Active  =  <?php echo $group_strength_total; ?> / <?php echo $group_active_strength_total; ?> </h6>
            </div>
          </div> -->
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
                <th>Group Name</th>
                <th>Strength ( Active + Inactive ) </th>
                <th>Max Limit</th>
                <th>Registration Open/Closed</th>
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
<div id="modal_add_group" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frmGroup" id="frmGroup" class="form-validate-jquery" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title">Add Group</h5>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label for="group_name">Group:<span class="mands">*</span></label>
              <input placeholder="Group Name" spacenotallow="true" required name="group_name" id="group_name" class="form-control" type="text">
            </div>

            <div class="col-md-6">
              <label for="max_limit">Group Max Limit:<span class="mands">*</span></label>
              <input placeholder="Group Max Limit" name="max_limit" id="max_limit" class="form-control required" dollarsscents="true" value="<?php echo GROUP_MAX_LIMIT ?>" type="text">

            </div>
          </div>
          <div class="row" style="margin-top:20px;">
            <div class="col-md-6">
              <label for="status">Status:<span class="mands">*</span></label>
              <select class="form-control" name="is_active" id="is_active" required>
                <option value="">Select</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="is_regis_open">Registration Open/Closed:<span class="mands">*</span></label>
              <select class="form-control" name="is_regis_open" id="is_regis_open" required>
                <option value="">Select</option>
                <option value="1">Open</option>
                <option value="0">Closed</option>
              </select>
            </div>
          </div>
          <br>
        </div>
        <div class="modal-footer">
          <div class="row col-md-12">
              <div class="ajaxMsgBot" style="display:inline;padding: inherit;"></div>
              <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <input type="hidden" name="group_id" id="group_id">
              <input type="hidden" name="action" value="save_group">
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /Add modal -->
<script>
  $(document).ready(function() {

    //FILL TABLE
    fillTable();

    $('#linkAddGroup').click(function() {
      $('.modal-title').html('Add Group');
      $("#frmGroup")[0].reset();
      $('#group_id').val('');
      $('.ajaxMsgBot').html("");
      $('#modal_add_group').modal('show');
    });

    jQuery.validator.addMethod("dollarsscents", function(value, element) {
      return this.optional(element) || /^[1-9]\d{0,3}(\.\d{0,2})?$/i.test(value);
    }, "Please enter a valid group max limit");

    $('input').on('keypress', function(e) {
      if (this.value.length === 0 && e.which === 32) {
        return false;
      }
    });

    $(document).on('click', '.delete_group', function(data, status) {
      var group_id = $(this).data('groupid');
        $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete group?',
            buttons: {
                confirm: function () {
                    $('.spinner').removeClass('hide');
                    $.post('<?php echo SITEURL ?>ajax/ajss-group', {
                      group_id: group_id,
                      action: 'delete_group'
                    }, function(data, status) {
                      if (status == 'success') {
                        displayAjaxMsg(data.msg, data.code);
                        fillTable();
                        setTimeout(function() {
                          $(".ajaxMsg").hide();
                        }, 80000);
                      } else {
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                          $(".ajaxMsg").hide();
                        }, 80000);
                      }
                    }, 'json');
                },
                cancel: function () {
                }
            }
        });
    });

    // $(document).on('click', '.delete_group', function(data, status) {
    //   if (confirm('Do you want to delete group?')) {
    //     $('.spinner').removeClass('hide');

    //     var group_id = $(this).data('groupid');

    //     $.post('<?php echo SITEURL ?>ajax/ajss-group', {
    //       group_id: group_id,
    //       action: 'delete_group'
    //     }, function(data, status) {
    //       if (status == 'success') {
    //         displayAjaxMsg(data.msg, data.code);
    //         fillTable();
    //         setTimeout(function() {
    //           $(".ajaxMsg").hide();
    //         }, 80000);
    //       } else {
    //         displayAjaxMsg(data.msg, data.code);
    //         setTimeout(function() {
    //           $(".ajaxMsg").hide();
    //         }, 80000);
    //       }
    //     }, 'json');
    //   }
    // });

    $('#modal_add_group').on('show.bs.modal', function(e) {
      $('#frmGroup').trigger('reset');
      var validator = $("#frmGroup").validate();
      validator.resetForm();
    });


    $(document).on('click', '.edit_group', function(data, status) {
      $('.ajaxMsgBot').html("Data loading, please wait");
      $('.modal-title').html('Edit Group');
      $('#modal_add_group').modal('show');

      var group_id = $(this).data('groupid');
      
      $.post('<?php echo SITEURL ?>ajax/ajss-group', {
        group_id: group_id,
        action: 'fetch_group'
      }, function(data, status) {
        if (status == 'success') {
          if (data.code == 1) {
            $('#is_active').val(data.is_active);
            $('#group_name').val(data.group_name);
            $('#category').val(data.category);
            $('#max_limit').val(data.max_limit);
            $('#group_id').val(data.id);
            $('#is_regis_open').val(data.is_regis_open);
            $('.ajaxMsgBot').html("");
          }
        }
      }, 'json');
    });

    $('#frmGroup').submit(function(e) {
      e.preventDefault();

      if ($('#frmGroup').valid()) {
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-group';
        $('.spinner').removeClass('hide');

        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              $("#frmGroup")[0].reset();
              $('#modal_add_group').modal('hide');
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
  });

  function fillTable() {
    var table = $('.datatable-basic').DataTable({
      autoWidth: false,
      destroy: true,
      pageLength: <?php echo TABLE_LIST_SHOW ?>,
      responsive: true,
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      ajax: '<?php echo SITEURL ?>ajax/ajss-group?action=list_all_groups',
      'columns': [{
          'data': 'group_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'strength',
          searchable: true,
          orderable: true
        },
        {
          'data': 'max_limit',
          searchable: true,
          orderable: true
        },
        {
          'data': 'is_regis_open',
          searchable: true,
          orderable: true
        },
        {
          'data': 'status',
          searchable: true,
          orderable: true
        },
      ],
     
      "aaSorting": [[0, "asc"]],
      "columnDefs": [{
        "render": function(data, type, row) {

          var btn = '';
          <?php if (in_array("su_group_edit", $_SESSION['login_user_permissions'])) { ?>
            btn += "<a href='javascript:void(0)' data-groupid = " + row['id'] + " title='Edit Group' class='text-primary action_link edit_group'>Edit</a>";
          <?php } ?>

          <?php if (in_array("su_group_delete", $_SESSION['login_user_permissions'])) { ?>
            if (row['delete'] == '') {
              btn += "<a href='javascript:void(0)' data-groupid = " + row['id'] + " title='Delete Group' class='text-danger action_link delete_group'>Delete</a>";
            }
          <?php } ?>
          return btn;
        },
        "targets": 5
      }, ]
    });

  }
  /*
  <a href='javascript:void(0)' title='Delete Group' data-class='" + row['group_name'] + "' data-groupid=" + row['id'] + " class='action_link text-success assignsheikh'><i class='icon-pushpin'></i></a>

  <a href='javascript:void(0)' data-groupid = " + row['id'] + " title='Remove Group' class='text-danger action_link delete_group'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php" ?>