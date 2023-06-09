<?php
include_once "../includes/config.php";
$mob_title = "Add Teacher Resource";
include "../header.php";


?>
<style>
  span.file_name_size {
    display: inline-block;
    width: 30%;
  }

  span.prog {
    display: inline-block;
    width: 10%;
  }

  a.remove_file {
    width: 10%;
  }

  span.mands {
    color: #ff0000;
    display: inline;
    line-height: 1;
    font-size: 12px;
    margin-left: 5px;
  }

  #filelist {
    margin-bottom: 10px;
  }
</style>
<!-- <script type="text/javascript" src="plupload_js/plupload.full.min.js"></script> -->
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Add Teacher Resources</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL . "teacher_resource/list_all_teacher_resource" ?>"> Manage Teacher Resources</a></li>
      <li class="active">Add Teacher Resources</li>
    </ul>
  </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">

  <form id="frmICJC" class="form-validate-jquery" method="post" enctype="multipart/form-data">

    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
        <?php if ($code === 1) { ?>
          <div class="alert alert-success ajaxMsgBot"><?php echo $msg ?></div>
        <?php } elseif ($code === 0) { ?>
          <div class="alert alert-danger ajaxMsgBot"><?php echo $msg ?></div>
        <?php } ?>
        <div class="ajaxMsg"></div>
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Title<span class="mands">*</span></label>
              <input type="text" class="form-control required" id="title" name="title" />
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Group<span class="mands">*</span></label>

              <?php
              //if(check_userrole_by_group('admin')){
              if (check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')) {
                $groups = $db->get_results("SELECT * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  and is_active = 1 and is_deleted = 0 order by group_name asc");
              } else {
                $groups = $db->get_results(" select  distinct g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id WHERE g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND (d.day_number = '0' or d.day_number is null) and g.is_active=1 and g.is_deleted=0 and d.id in (select classtime_id from ss_staffclasstimemap where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND staff_user_id='" .$_SESSION['icksumm_uat_login_userid'] . "' and active=1) order by group_name asc");
               
              }
              ?>
              <!--  <select class="bootstrap-select" data-width="100%" id="group" name="group[]" multiple="multiple" required> -->

              <!--  <select id="divGroups" name="group[]" ata-width="100%" class="selectpicker form-control" multiple data-size="5" data-selected-text-format="count>2" required> -->
              <select class="form-control required" name="group_id" id="group_id">
                <option value="">Select</option>
                <?php foreach ($groups as $gr) { ?>
                  <option value="<?php echo $gr->id ?>"><?php echo $gr->group_name ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
                <label for="group">Class:<span class="mandatory">*</span></label>
                <select class="form-control required" name="subject" id="subject" >
                    <option value="">Select Class</option>
                </select>
            </div>
        </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Status<span class="mands">*</span></label>
              <select class="form-control required" name="status">
                <option value="">Select</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message<span class="mands">*</span></label>
              <label class="error" id="statusMsgcomm"></label>
              <textarea id="summernote" name="message"></textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group" id="attach_box">
              <label>Attachment</label>
              <div class="row">
                <div class="col-md-8 
                
                ">
                  <input type="file" name="attachmentfile[]">
                </div>
                <div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment"> remove</a></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <a href="javascript:void(0)" id="add_more_attachments"><i class="icon-plus2"></i> Add More Attachment</a>
                </div>
              </div>
            </div>
            <div class="row mt-30">
              <div class="col-md-12">
                <div class="form-group">
                  <input type="hidden" name="action" value="save_teacher_resource">
                  <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>

                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>
  </form>
</div>
<script>
  $(document).ready(function(e) {


    CKEDITOR.replace('summernote', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });

    $('input').on('keypress', function(e) {
      if (this.value.length === 0 && e.which === 32) {
        return false;
      }
    });

    $("div.note-editing-area div.note-editable").keypress(function(evt) {
      var kc = evt.keyCode;
      var qbQuestion = CKEDITOR.instances.summernote.getData();
      if (kc === 32 && (qbQuestion.length == 0 || qbQuestion == '<p><br></p>')) {
        event.preventDefault();
      }
    });
    //REMOVE UPLOADED FILE
    $(document).on('click', '.remove_attachment', function() {
      $(this).parent().parent().remove();
    });

    //ADD NEW ATTACHMENT
    $("#add_more_attachments").click(function() {
      $('#attach_box').append('<div class="row mt-10 newattachcls"><div class="col-md-8"><input type="file" name="attachmentfile[]"></div><div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment">remove</a></div></div>');
    });


    $('.btn.dropdown-toggle').click(function() {
      var id = $(this).data('id');
      $('#' + id + '-error').css('display', 'none');
    });

    // $('#frmICJC').submit(function(e) {
    //     //e.preventDefault();
    // var getcode = $('#summernote').summernote('code');
    // getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
    // var div = document.createElement("div");
    // div.innerHTML = getcode;
    // var text = div.textContent || div.innerText || "";      

    $('#frmICJC').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.summernote.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');
    //   if (ckvalue.length === 0) {
    //     $('#statusMsgcomm').html('Required Field');
    //     return false;
    //   } else {
    //     $('#statusMsgcomm').html('');
    //   }
      CKEDITOR.instances.summernote.updateElement();
      // var getcode = CKEDITOR.instances.summernote.getData();
      // getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
      // var div = document.createElement("div");
      // div.innerHTML = getcode;
      // var text = div.textContent || div.innerText || "";    

      if ($('#frmICJC').valid() && ckvalue.length > 0) {
        $('#statusMsgcomm').html('');
        $('.spinner').removeClass('hide');
        var formData = new FormData(this);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-teacher-resource';
        $.ajax({
          url: targetUrl,
          data: formData,
          type: 'POST',
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(data) {
            $('.spinner').addClass('hide');
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              $("#frmICJC")[0].reset();
              CKEDITOR.instances.summernote.setData('');


            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          },
          error: function(data) {
            $('.spinner').addClass('hide');
            displayAjaxMsg(data.msg, data.code);
          }
        }, 'json');
      }else{
       if (ckvalue.length === 0) {
        $('#statusMsgcomm').html('Required Field');
        return false;
      } else {
        $('#statusMsgcomm').html('');
      }

      }
    });



        // var formDate = $(this).serialize();
        // $.post(targetUrl, formDate, function(data, status) {
        //     if (status == 'success') {


        //         if (data.code == 1) {
        //             displayAjaxMsg(data.msg, data.code);
        //             $("#frmICJC")[0].reset();
        //             CKEDITOR.instances.summernote.setData('');


        //         } else {
        //             displayAjaxMsg(data.msg, data.code);
        //         }
        //     } else {
        //         displayAjaxMsg(data.msg);
        //     }
        // }, 'json');
      
    });

    $('#group_id').change(function() {
            $('#group_id').val($(this).val());

            if ($('#group').val() == '') {
                $('#subject').html('<option value="">Select Subject</option>');
                $('#homework_target').html('<option value="">No Records</option>');
                $('#homework_target').selectpicker('refresh');
            } else {
                //SUBJECT
                $('#subject').html('<option value="">Loading...</option>');

                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-teacher-resource';
                $.post(targetUrl, {
                    group_id: $('#group_id').val(),
                    action: 'fetch_assigned_group_class_for_select'
                }, function(data, status) {
                    if (status == 'success' && data != '') {
                        $('#subject').html('<option value="">Select Subject</option>');
                        $('#homework_target').html('<option value="">No Records</option>');
                        $('#homework_target').selectpicker('refresh');
                        $('#subject').append(data);
                    } else {
                        $('#subject').html('<option value="">Subject not found</option>');
                    }
                });

            }
        });


  setTimeout(function() {
    $('.ajaxMsgBot').hide();
  }, 5000);


  //   $("#summernote").on("summernote.change", function (e) {   // callback as jquery custom event 
  //       var getcode = CKEDITOR.instances.message.getData();
  //       getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
  //       var div = document.createElement("div");
  //       div.innerHTML = getcode;
  //       var text = div.textContent || div.innerText || "";

  //       if($.trim(text).length !==0){  
  //            $('#summernote-error').empty();
  //             $('#summernote-error').addClass('validation-valid-label');

  //       }
  //       else{
  //            $('#summernote-error').html('Required field');
  //            $('#summernote-error').addClass('validation-error-label');
  //             $('#summernote-error').removeClass('validation-valid-label');
  //            return false;
  //       }

  // });
</script>
<?php include "../footer.php" ?>