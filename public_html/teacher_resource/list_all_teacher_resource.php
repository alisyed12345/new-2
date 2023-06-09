<?php
$mob_title = "Manage Teacher Resources";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_teacher_resource_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
?>
<style>
  table.message_list thead {
    display: block;
  }
</style>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Manage Teacher Resources</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Manage Teacher Resources</li>
    </ul>
  </div>
  <?php if (in_array("su_teacher_resource_create", $_SESSION['login_user_permissions'])) {   ?>
    <div class="above-content"> <a href="<?php echo SITEURL ?>teacher_resource/create_teacher_resource.php" class="pull-right"><span class="label label-primary">Add Teacher Resource</span></a> </div>
  <?php  } ?>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat panel-flat-box">
        <div class="panel-body panel-body-box">
          <div class="ajaxMsg"></div>
          <div class="row">
            <div class="col-lg-12">
              <div class="table-responsive">
                <table class="table table-bordered dataGrid">
                  <thead>
                    <tr>
                      <th>Id</th>
                      <th>Title</th>
                      <th>Group Name</th>
                      <th>Class Name</th>
                      <th>Status</th>
                      <th>Attachment</th>
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
    </div>
  </div>
</div>

<!-- /Add modal -->
<!-- Add Modal - Admission Request Detail-->
<div id="modal_request_detail" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Teacher Resources Details <span id="request_detail_title"></span></h5>
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
  $(document).ready(function() {
    //FILL TABLE 
    fillTable();

    $('#modal_request_detail').on('hide.bs.modal', function(e) {
      $('#overlay').hide();
    });

    $(document).on('click', '.viewdetailss', function() {
      var id = $(this).data('id');
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-teacher-resource';

      $('#request_detail_title').html('#' + id);
      $('#request_detail').html('<h5>Data loading, please wait</h5>');
      $('#modal_request_detail').modal('show');

      $.ajax({
        url: targetUrl,
        cache: false,
        data: {
          id: id,
          action: 'view_teacher_resource'
        },
        success: function(html) {
          $('#request_detail').html(html);
        }
      });



    });

    $(document).on('click', '.delete_teach', function(data, status) {
      var id = $(this).data('id');
      $.confirm({
        title: 'Confirm!',
        content: 'Do you want to delete?',
        buttons: {
          confirm: function() {
            $('.spinner').removeClass('hide');
            $.post('<?php echo SITEURL ?>ajax/ajss-teacher-resource', {
              id: id,
              action: 'delete_teach_resource'
            }, function(data, status) {
              if (status == 'success') {
                displayAjaxMsg(data.msg, data.code);
                setTimeout(function() {
                  $(".ajaxMsg").html("");
                }, 8000);
                fillTable();
              } else {
                displayAjaxMsg(data.msg, data.code);
                setTimeout(function() {
                  $(".ajaxMsg").html("");
                }, 8000);
              }
            }, 'json');
          },
          cancel: function() {}
        }
      });
    });
  });

  function fillTable() {
    var table = $('.dataGrid').DataTable({
      autoWidth: false,
      destroy: true,
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      responsive: true,
      ajax: '<?php echo SITEURL ?>ajax/ajss-teacher-resource?action=list_teacher_resource',
      'columns': [{
          'data': 'id'
        },
        {
          'data': 'title',
          searchable: true,
          orderable: true
        },
        {
          'data': 'group_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'class_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'status',
          searchable: true,
          orderable: true
        },
        {
          'data': 'attachment_file_path',
          searchable: true,
          orderable: true
        },
      ],
      "order": [
        [0, "desc"]
      ],
      "columnDefs": [{
          "render": function(data, type, row) {
            var btn = '';
            btn += "<a href='Javascript:void(0)' class='text-orange action_link viewdetailss' data-id = " + row['id'] + " title='View'>View</a>";

            <?php if (check_userrole_by_code('UT01')) { ?>
              <?php if (in_array("su_teacher_resource_edit", $_SESSION['login_user_permissions'])) { ?>
                btn += "<a href='<?php echo SITEURL ?>teacher_resource/edit_teacher_resource.php?id=" + row['id'] + "' class='text-primary action_link' title='Edit'>Edit</a>";
              <?php } ?>

              <?php if (in_array("su_teacher_resource_delete", $_SESSION['login_user_permissions'])) { ?>
                btn += "<a href='Javascript:void(0)' data-id = " + row['id'] + " class='text-danger action_link delete_teach' title='Delete'>Delete</a>";
              <?php } ?>

            <?php } else {  ?>

              if (row['created_by_user_id'] == row['check_role']) {
                <?php if (in_array("su_teacher_resource_edit", $_SESSION['login_user_permissions'])) { ?>
                  btn += "<a href='<?php echo SITEURL ?>teacher_resource/edit_teacher_resource.php?id=" + row['id'] + "' class='text-primary action_link' title='Edit'>Edit</a>";
                <?php } ?>

                <?php if (in_array("su_teacher_resource_delete", $_SESSION['login_user_permissions'])) { ?>
                  btn += "<a href='Javascript:void(0)' data-id = " + row['id'] + " class='text-danger action_link delete_teach' title='Delete'>Delete</a>";
                <?php } ?>
              }
            <?php } ?>

            return btn;
          },
          "targets": 5
        },
        {
          "visible": false,
          "targets": [0]
        }
      ]
    });
  }
</script>
<?php include "../footer.php" ?>