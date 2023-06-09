<?php
$mob_title = "Family View";
include "../header.php";

//AUTHARISATION CHECK 
if (!in_array("su_family_list", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Family View</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Family View</li>
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
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
                <th>Family ID</th>
                <th>1st parent Name</th>
                <th>2nd parent Name</th>
                <th>City</th>
                <th class="text-center action_col"></th>
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
<!-- Add Modal - Staff Detail-->
<div id="modal_family_detail" class="modal fade">
  <div class="modal-dialog" style="width: 910px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" id="familyinfo_title"></h5>
      </div>
      <div class="modal-body viewonly" id="family_detail"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- /Add modal -->


<!-- Add Child Modal Start-->
<div id="modal_add_child" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form name="frm_addchild" class=" reg_form mt-20 form-validate-jquery" id="frm_addchild" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title">Add Child</h5>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <label for="group">First Name <span class="mandatory">*</span></label>
              <input type="text" spacenotallow="true" name="child_first_name" lettersonly="true" tabindex="40" placeholder="First Name" maxlength="25" value="" class="form-control required" lettersonly="true">
            </div>
            <div class="col-md-4">
            <label for="group">Last Name <span class="mandatory">*</span></label>
              <input type="text" spacenotallow="true" name="child_last_name" lettersonly="true" tabindex="45" placeholder="Last Name" maxlength="25" value="" class="form-control required" lettersonly="true">
            </div>
            <div class="col-md-4">
            <label for="group">Gender <span class="mandatory">*</span></label>
              <select name="child_gender" class="form-control required" tabindex="50">
                <option value="">Select Gender</option>
                <option value="m">Male
                </option>
                <option value="f">Female
                </option>
              </select>
            </div>
          </div>
          <br>

          <div class="row">
            <div class="col-md-4">
            <label for="group">Date of Birth <span class="mandatory">*</span></label>
              <input type="text" name="child_dob" tabindex="55" placeholder="Date of Birth" value="" class="form-control datepicker required bgcolor-white">
            </div>


            <div class="col-md-4">
            <label for="group">Allergies <span class="mandatory">*</span></label>
              <input type="text" name="child_allergies" id="child_allergies" maxlength="50" tabindex="60" placeholder="Enter Allergies" class="form-control required">
            </div>

            <div class="col-md-4">
              <?php $grades = array('KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher'); ?>
              <label for="group">Grade <span class="mandatory">*</span></label>
              <select name="child_grade" class="form-control required" tabindex="50">
                <option value="">Select Grade</option>
                <?php foreach ($grades as $garde) { ?>
                  <option value="<?= $garde ?>"><?= $garde ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <div class="ajaxMsgBot pull-center"></div>
          <input type="hidden" name="family_id" id="family_id">
          <input type="hidden" name="action" value="add_child">
          <button type="button" class="btn btn-secondery pull-right" style="margin-left: 5px;" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary pull-right" style="margin-left: 5px;">Submit</button>
          <div class="load pull-right" style=" border-radius: 100px !important; padding: 0.5rem 1rem; "></div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /Add Child Modal Close-->




<!-- Add Modal - Forced Login Admin URL-->
<div class="modal" id="parent_login">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Parent Login URL </h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <input type="text" id="parentloginurl" class="form-control js-copytextarea" readonly>
        <br>
        <button class="js-textareacopybtn" style="vertical-align:top;">Copy URL</button>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
<!-- /Add modal -->
<script>
  var table;

  $(document).ready(function() {
    //FILL TABLE
    fillTable();

    //TO SELECT FORCED ADMIN LOGIN URL
    var copyTextareaBtn = document.querySelector('.js-textareacopybtn');
    copyTextareaBtn.addEventListener('click', function(event) {
      var copyTextarea = document.querySelector('.js-copytextarea');
      copyTextarea.focus();
      copyTextarea.select();
      var successful = document.execCommand('copy');
    });

    $(document).on('click', '.parentforcelogin', function() {
      var loginurl = $(this).data('loginurl');
      $('#parentloginurl').val(loginurl);
      $('#parent_login').modal('show');
    });

    $('.datepicker').pickadate({
      labelMonthNext: 'Go to the next month',
      labelMonthPrev: 'Go to the previous month',
      labelMonthSelect: 'Pick a month from the dropdown',
      labelYearSelect: 'Pick a year from the dropdown',
      selectMonths: true,
      selectYears: 100,
      min: [<?php echo date('Y') - 100 ?>, 01, 01],
      max: [<?php echo date('Y') - 4 ?>, 12, 31],
      format: '<?php echo my_date_changer('d mmmm, yyyy'); ?>',
      formatSubmit: 'yyyy-mm-dd'
    });

    //FETCH STAFF DETAILS
    $(document).on('click', '.viewdetail', function() {
      var familyid = $(this).data('familyid');
      var fathername = $(this).data('fathername');
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';

      $('#familyinfo_title').html(fathername + "'s Family");
      $('#family_detail').html('<h5>Data loading... Please wait</h5>');
      $('#modal_family_detail').modal('show');

      $.post(targetUrl, {
        familyid: familyid,
        action: 'view_family_detail'
      }, function(data, status) {
        if (status == 'success') {
          $('#family_detail').html(data);
        }
      });
    });


    //Add Child
    $(document).on('click', '.addchild', function() {
      var familyid = $(this).data('familyid');
      $('#family_id').val(familyid);
      $('#modal_add_child').modal('show');
    });

    $('#modal_add_child').on('show.bs.modal', function(e) {
      $('#frm_addchild').trigger('reset');
      var validator = $("#frm_addchild").validate();
      validator.resetForm();
    });

    $('#frm_addchild').submit(function(e) {
      e.preventDefault();
    
      if ($('#frm_addchild').valid()) {
        $('.load').html(
          '<strong>Please Wait...</strong>');
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
              $('.load').html('');
              //$('.ajaxMsgBot').html('');

              $("#frm_addchild").trigger("reset");
              $('.select').change();
              displayAjaxMsg(data.msg, data.code);
              $('#modal_add_child').modal('hide');
            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          } else {
            displayAjaxMsg(data.msg);
          }
        }, 'json');
      }
    });




    //SEND LOGIN INFO TO PARENTS
    $(document).on('click', '.sendlogininfo', function() {
      $this = $(this);
      $this.find('i').removeClass('icon-key');
      $this.find('i').addClass('icon-spinner9 spinner spinner-orig');
      var familyid = $this.data('familyid');
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
      $('.bartext' + familyid).html('<span style="color:black;">Processing...</span>');
      $.post(targetUrl, {
        familyid: familyid,
        action: 'email_login_info_to_parents'
      }, function(data, status) {
        if (status == 'success') {
          $('.bartext' + familyid).html('Send Login Info');
          displayAjaxMsg(data.msg, data.code);
        } else {
          $('.bartext' + familyid).html('Send Login Info');
          displayAjaxMsg('Login information sending process failed');
        }

        $this.find('i').removeClass('icon-spinner9 spinner spinner-orig hide');
        $this.find('i').addClass('icon-key');
      }, 'json');
    });
  });

  function fillTable() {
    table = $('.datatable-basic').DataTable({
      autoWidth: false,
      destroy: true,
      pageLength: <?php echo TABLE_LIST_SHOW ?>,
      responsive: true,
      ajax: '<?php echo SITEURL ?>ajax/ajss-family?action=list_family',
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      'columns': [{
          'data': 'id'
        },
        {
          'data': 'father_name',
          searchable: true,
          orderable: true,
        },
        {
          'data': 'mother_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'city',
          searchable: true,
          orderable: true
        },
      ],
      "order": [
        [1, "asc"]
      ],
      "columnDefs": [{
          "render": function(data, type, row) {

            var btn = '';
            <?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
           // if (check_userrole_by_code('UT01') || check_userrole_by_code('UT02')) { ?>

              <?php if (in_array("su_family_view", $_SESSION['login_user_permissions'])) { ?>
                btn += "<a href='#' class='text-warning action_link viewdetail' title='View Details' data-fathername='" + row['father_name'] + "' data-familyid='" + row['id'] + "'>View</a>";
              <?php } ?>

              <?php if (in_array("su_family_edit", $_SESSION['login_user_permissions'])) { ?>
                btn += "<a href='<?php echo SITEURL ?>student/family_edit?fid=" + row['id'] + "' class='text-primary action_link' title='Edit Family Info'>Edit</a>";
              <?php } ?>

              <?php if (in_array("su_family_send_login_info", $_SESSION['login_user_permissions'])) { ?>
              btn += "<a href='javascript:void(0)' class='text-success action_link sendlogininfo bartext" + row['id'] + "' title='Send Login Info' data-familyid='" + row['id'] + "'>Send Login Info</a> ";
              <?php } ?>

              <?php if (in_array("su_family_add_child", $_SESSION['login_user_permissions'])) { ?>
                btn += "<a href='javascript:void(0)' class='text-warning action_link addchild' title='Add Child' data-familyid='" + row['id'] + "'>Add Child</a>";
              <?php } ?>
           
              <?php //}  ?>

              <?php if (in_array("su_family_login_url", $_SESSION['login_user_permissions'])) { ?>
              btn += "<a href='javascript:void(0)' class='text-primary action_link parentforcelogin' title='Login URL' data-loginurl='" + row['admin_forced_login'] + "'>Login URL</a>";
            <?php } ?>

            return btn;
          },
          "targets": 4
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