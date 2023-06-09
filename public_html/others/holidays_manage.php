<?php
$mob_title = "Holidays";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_holiday_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
?>

<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Holidays</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Holidays</li>
    </ul>
  </div>
  <?php if (in_array("su_holiday_create", $_SESSION['login_user_permissions'])) {
    //if(check_userrole_by_code('UT01')){
  ?>
    <div class="above-content"> <a href="javascript:void(0)" id="linkAddHoliday" class="pull-right"><span class="label label-danger">Add Holiday</span></a> </div>
  <?php } //}
  ?>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
  <div class="msg"></div>
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat">
        <div class="panel-body">
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Group</th>
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
<!-- Edit Modal -->
<!-- <div id="modal_edit_holiday" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Edit Holiday</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Date:</label>
              <div class="input-group"> <span class="input-group-addon"><i class="icon-calendar22"></i></span>
                <input type="text" class="form-control daterange-basic" value="01/01/2015 - 01/31/2015">
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group multi-select-full">
              <label>Reason:</label>
              <input type="text" class="form-control" value="Eid-ul-fitr">
            </div>
          </div>
          <div class="col-md-12">
            <div class="checkbox">
              <label>
                <input type="checkbox" class="styled" checked="checked">
                Active </label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success">Submit</button>
      </div>
    </div>
  </div>
</div> -->
<!-- /Edit modal -->

<!-- Add Modal -->
<div id="modal_add_holiday" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frmHoliday" id="frmHoliday" class="form-validate-jquery" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title">Add Holiday</h5>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Date:<span class="mandatory">*</span></label>
                <div class="input-group"> <span class="input-group-addon"><i class="icon-calendar22"></i></span>
                  <input type="text" name="holiday_date" class="form-control daterange-basic" required>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Group:</label>
                <div class="form-group multi-select-full">
                  <?php $groups = $db->get_results("select *,(CASE WHEN category='b' THEN 'Beginner' 
                WHEN category='i' THEN 'Intermediate' WHEN category='a' THEN 'Advanced' END) AS category 
                from ss_groups g where g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND is_active=1 
                and is_deleted=0 order by group_name asc"); ?>
                  <select class="bootstrap-select" multiple="multiple" title="All Groups" data-width="100%" id="group_id" name="group_id[]">
                    <?php foreach ($groups as $grp) { ?>
                      <option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Status:<span class="mandatory">*</span></label>
                <select class="form-control" required name="is_active" id="is_active">
                  <option value="">Select</option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Reason (Maximum 255 Characters) <span id="q17length"></span> <span class="mandatory">*</span></label>
                <input type="text" name="reason" id="reason" onKeyDown="textCounter(this,'q17length',255);" onKeyUp="textCounter(this,'q17length',255)" maxlength="255" class="form-control" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="ajaxMsgBot"></div>
          <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" name="holiday_id" id="holiday_id">
          <input type="hidden" name="action" value="save_holiday">
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /Add modal -->
<?php $date_format = my_date_changer('DD MMMM,YYYY');

?>

<script>
  $(document).ready(function() {

   
$('.daterange-basic').on()
    $('.daterange-basic').daterangepicker({
      applyClass: 'bg-slate-600',
      cancelClass: 'btn-default',
      locale: {
      format: '<?php echo $date_format ?>'
    },
      minDate: moment()
      
      
    });
    // 'MMMM D,YYYY'
    $('input').on('keypress', function(e) {
      if (this.value.length === 0 && e.which === 32) {
        return false;
      }
    });

    //FILL TABLE
    fillTable();

    $('#linkAddHoliday').click(function() {
      $('.modal-title').html('Add Holiday');
      $('#holiday_id').val('');
      $('#modal_add_holiday').modal('show');
    });

    $(document).on('click', '.delete_holiday', function(data, status) {
      var holiday_id = $(this).data('holidayid');
      $.confirm({
        title: 'Confirm!',
        content: 'Do you want to delete holiday?',
        buttons: {
          confirm: function() {
            $('.spinner').removeClass('hide');
            $.post('<?php echo SITEURL ?>ajax/ajss-holidays', {
              holiday_id: holiday_id,
              action: 'delete_holiday'
            }, function(data, status) {
              if (status == 'success') {
                fillTable();
                displayAjaxMsg(data.msg, data.code);
                $('.msg').addClass('alert alert-success');
                $('.msg').text(data.msg);
                setTimeout(function() {
                  $('.msg').removeClass('alert alert-success');
                  $('.msg').text('');
                }, 2500);
              } else {
                displayAjaxMsg(data.msg, data.code);
                $('.msg').addClass('alert alert-danger');
                $('.msg').text(data.msg);
                setTimeout(function() {
                  $('.msg').removeClass('alert alert-danger');
                  $('.msg').text('');
                }, 2500);
              }
            }, 'json');
          },
          cancel: function() {}
        }
      });
    });

    // $(document).on('click','.delete_holiday',function(data,status){
    // 	if(confirm('Do you want to delete holiday?')){
    // 		$('.spinner').removeClass('hide');

    // 		var holiday_id = $(this).data('holidayid');

    // 		$.post('<?php echo SITEURL ?>ajax/ajss-holidays',{holiday_id:holiday_id,action:'delete_holiday'},function(data,status){
    // 			if(status == 'success'){
    // 				fillTable();
    // 				displayAjaxMsg(data.msg,data.code);
    //          $('.msg').addClass('alert alert-success');
    //           $('.msg').text(data.msg);
    //           setTimeout(function () {
    //             $('.msg').removeClass('alert alert-success');
    //             $('.msg').text('');
    //           }, 2500);
    // 			}else{
    // 				displayAjaxMsg(data.msg,data.code);
    //          $('.msg').addClass('alert alert-danger');
    //           $('.msg').text(data.msg);
    //           setTimeout(function () {
    //             $('.msg').removeClass('alert alert-danger');
    //             $('.msg').text('');
    //           }, 2500);
    // 			}
    // 		},'json');
    // 	}
    // });



    $('#modal_add_holiday').on('show.bs.modal', function(e) {
      hideAjaxMsg();
      $('#frmHoliday').trigger('reset');
      $('.select').change();
      $("#group_id").val([]).trigger("change");
      var validator = $("#frmHoliday").validate();
      validator.resetForm();
      $('#group_id').selectpicker('refresh');
    });

    $(document).on('click', '.edit_holiday', function(data, status) {
      displayAjaxMsg("Data loading, please wait", 2);
      $('.modal-title').html('Edit Holiday');
      $('#modal_add_holiday').modal('show');

      var holiday_id = $(this).data('holidayid');

      $.post('<?php echo SITEURL ?>ajax/ajss-holidays', {
        holiday_id: holiday_id,
        action: 'fetch_holiday'
      }, function(data, status) {
        if (status == 'success') {
          if (data.code == 1) {
            $('input[name="holiday_date"]').val(data.date_start + ' - ' + data.date_end)
            $('#is_active').val(data.is_active);
            $('#reason').val(data.reason);
            $('#holiday_id').val(data.id);
            var totalchar = $('#reason').val().length;
            var len = 255 - totalchar;
            $('#q17length').html(len + " - left");

            //var grouparray = data.group_ids.split(",");
            //$("#group_id").val(grouparray);

            var grouparray = data.group_ids.split(","),
              i = 0,
              size = grouparray.length,
              $options = $('#group_id option');
            $("#group_id").val(grouparray);

            for (i; i < size; i++) {
              $options.filter('[value="' + grouparray[i] + '"]').prop('selected', true);
            }

            $("#group_id").trigger("change");

            hideAjaxMsg();
          }
        }
      }, 'json');
    });

    $('#frmHoliday').submit(function(e) {
      e.preventDefault();

      if ($('#frmHoliday').valid()) {
        $('.btnsubmit').attr('disabled', true);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-holidays';
        $('.spinner').removeClass('hide');

        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            $('.btnsubmit').attr('disabled', false);
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              $('#group_id').val('');
              $("#frmHoliday")[0].reset();
              $('#modal_add_holiday').modal('hide');
              $('.msg').addClass('alert alert-success');
              $('.msg').text(data.msg);
              setTimeout(function() {
                $('.msg').removeClass('alert alert-success');
                $('.msg').text('');
              }, 2500);
              fillTable();
              $('#group_id').selectpicker('refresh');
            } else {
              displayAjaxMsg(data.msg, data.code);
              $('.msg').addClass('alert alert-danger');
              $('.msg').text(data.msg);
              setTimeout(function() {
                $('.msg').removeClass('alert alert-danger');
                $('.msg').text('');
              }, 2500);
            }
          } else {
            $('.btnsubmit').attr('disabled', false);
            displayAjaxMsg(data.msg);
            $('.msg').addClass('alert alert-danger');
            $('.msg').text(data.msg);
            setTimeout(function() {
              $('.msg').removeClass('alert alert-danger');
              $('.msg').text('');
            }, 2500);
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
      ajax: '<?php echo SITEURL ?>ajax/ajss-holidays?action=list_all_holidays',
      'columns': [{
          'data': 'holiday_date',
          searchable: true,
          orderable: true
        },
        {
          'data': 'reason',
          searchable: true,
          orderable: true
        },
        {
          'data': 'for_group',
          searchable: true,
          orderable: true
        },
        {
          'data': 'is_active',
          searchable: true,
          orderable: true
        },
      ],
      "columnDefs": [{
        "render": function(data, type, row) {
          var btn = '';
          <?php if (in_array("su_holiday_edit", $_SESSION['login_user_permissions'])) {
          ?>
            btn += "<a href='javascript:void(0)' data-holidayid = " + row['id'] + " title='Edit Holiday' class='text-primary action_link edit_holiday'>Edit</a>";
          <?php } ?>
          <?php if (in_array("su_holiday_delete", $_SESSION['login_user_permissions'])) {
          ?>
            btn += "<a href='javascript:void(0)' data-holidayid = " + row['id'] + " title='Delete Holiday' class='text-danger action_link delete_holiday'>Delete</a>";
          <?php }  ?>

          return btn;
        },
        "targets": 4
      }, ]
    });
  }

  function textCounter(field, cnt, maxlimit) {
    var cntfield = document.getElementById(cnt)

    if (field.value.length > maxlimit) // if too long...trim it!
      field.value = field.value.substring(0, maxlimit);
    else {
      if (field.value.length == 0) {
        $('#' + cnt).html('');
      } else {
        var len = maxlimit - field.value.length;
        $('#' + cnt).html(len + " - left");
      }

    }
  }
  /* 
  <a href='javascript:void(0)' title='Delete Holiday' data-holiday='" + row['group_name'] + "' data-holidayid=" + row['id'] + " class='action_link text-success assignsheikh'><i class='icon-pushpin'></i></a>

  <a href='javascript:void(0)' data-holidayid = " + row['id'] + " title='Remove Group' class='text-danger action_link delete_holiday'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php" ?>