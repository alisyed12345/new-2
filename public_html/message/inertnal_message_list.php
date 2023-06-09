<?php
$mob_title = "Message";
include "../header.php";

//AUTHARISATION CHECK 
if ($_SESSION['icksumm_uat_login_usertypecode'] != 'UT05' && !in_array("su_internal_msg_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  exit;
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
      <h4 style="display:inline-block">Internal Message</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Internal Message</li>
    </ul>
  </div>
  <?php

  if (check_userrole_by_code('UT05')) { ?>
    <div class="above-content"> <a href="<?php echo SITEURL ?>message/internal_message_new_parents" class="pull-right"><span class="label label-danger">Send Internal Message</span></a> </div>
    <?php } else {
    if (in_array("su_internal_msg_send", $_SESSION['login_user_permissions'])) { ?>
      <div class="above-content"> <a href="<?php echo SITEURL ?>message/internal_message" class="pull-right"><span class="label label-danger">Send Internal Message</span></a> </div>
  <?php
    }
  } ?>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat panel-flat-box">
        <div class="panel-body panel-body-box">
          <div class="row">
            <div class="col-lg-12">
                <?php if (check_userrole_by_code('UT01')) { ?>
                    <input type="radio" name="filtermsg" value="Staff" class="filtermsgdata" checked> Staff
                    <input type="radio" name="filtermsg" value="Parents" class="filtermsgdata" style="margin-left: 5px;"> Parents
                    
                <?php } ?>
              <div class="table-responsive" style="margin-top: 10px;">
                <table class="table table-bordered dataGrid">
                  <thead>
                    <tr>
                      <th>Message ID</th>
                      <th>ID</th>
                      <th>Sender</th>
                      <th>Receiver</th>
                      <th>Message</th>
                      <th>Date</th>
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
<script>
  $(document).ready(function() {
    //FILL TABLE 
    fillTable();
  
    $('.filtermsgdata').change(function() {
        if ($('.filtermsgdata').is(":checked"))
        {
          var filtername = $(this).val();
          fillTable(filtername);
        }else{
          fillTable();
        }
    });

  }); 
//Add Comment
  function fillTable(filtername) {
    var table = $('.dataGrid').DataTable({
      autoWidth: false,
      destroy: true,
      pageLength: <?php echo TABLE_LIST_SHOW ?>,
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      responsive: true,
      ajax: '<?php echo SITEURL ?>ajax/ajss-message?action=list_messages&filter='+filtername,
      'columns': [{
          'data': 'msgid'
        },
        {
          'data': 'id'
        },
        {
          'data': 'sen_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'rec_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'message',
          searchable: true,
          orderable: true
        },
        {
          'data': 'msg_datetime',
          searchable: true,
          orderable: true
        },
      ],
      "order": [
        [1, "desc"]
      ],
      "columnDefs": [{
          "render": function(data, type, row) {
            var links = '';
            <?php if ($_SESSION['icksumm_uat_login_usertypecode'] == 'UT05' || (in_array("su_internal_msg_reply", $_SESSION['login_user_permissions']))) { ?>
              links = links + "<a href='<?php echo SITEURL ?>message/internal_message_reply?id=" + row['msgid'] + "' class='text-primary action_link overlay_link' title='View Conversation'>View Conversation</a>";
            <?php } ?>
            return links;
          },
          "targets": 6
        },
        {
          "visible": false,
          "targets": [0, 1]
        }
      ]
    });
  }
</script>
<?php include "../footer.php" ?>