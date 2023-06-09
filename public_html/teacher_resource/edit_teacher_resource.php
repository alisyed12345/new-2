<?php
include_once "../includes/config.php";
$mob_title = "Edit Teacher Resource";
include "../header.php";


//echo $msg;
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

  #filelist {
    margin-bottom: 10px;
  }
  span.mands {
    color: #ff0000;
    display: inline;
    line-height: 1;
    font-size: 12px;
    margin-left: 5px;
}
</style>
<!-- <script type="text/javascript" src="plupload_js/plupload.full.min.js"></script> -->
<!-- Page header -->
<?php
  $get_teacher_resource = $db->get_row("SELECT g.group_name, cb.id, cb.group_id,cb.subject_id, cb.title, cb.message, cb.status from ss_class_common_board cb 
  INNER JOIN ss_groups g ON g.id = cb.group_id WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
  AND cb.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND cb.id = '".$_GET['id']."'");

$get_class_board_attach = $db->get_results("SELECT id, attachment_file_path FROM ss_class_common_board_attach 
  Where class_common_board_id = '".$get_teacher_resource->id."'"); 
  
  ?>
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Edit Teacher Resources</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL . "teacher_resource/list_all_teacher_resource" ?>"> Manage Teacher Resources</a></li>
      <li class="active">Edit Teacher Resources</li>
    </ul>
  </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
  <form id="frmICJC" class="form-validate-jquery" method="post" enctype="multipart/form-data">
    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
      <div class="ajaxMsg"></div>
        <?php if ($code === 1) { ?>
        <div class="alert alert-success ajaxMsgBot"><?php echo $msg ?></div>
        <?php } elseif ($code === 0) { ?>
        <div class="alert alert-danger ajaxMsgBot"><?php echo $msg ?></div>
        <?php } ?>
        <div class="row">
           <div class="col-md-3">
            <div class="form-group">
              <label>Title <span class="mands">*</span></label>
              <input type="text" class="form-control required" id="title" name="title" value="<?php echo $get_teacher_resource->title ?>" />
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Group <span class="mands">*</span></label>
            
              <?php 
                //if(check_userrole_by_group('admin')){
              if(check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')) {
                $groups = $db->get_results("SELECT * from ss_groups where is_active = 1 and is_deleted = 0 order by group_name asc"); 
              }else{

                $groups = $db->get_results("select distinct g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id WHERE g.is_active=1 and g.is_deleted=0 and d.id in (select classtime_id from ss_staffclasstimemap where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND staff_user_id='" . $_SESSION['icksumm_uat_login_userid']. "' and active=1) order by group_name asc");
              }
              ?>
             <!--  <select class="bootstrap-select" data-width="100%" id="group" name="group[]" multiple="multiple" required> -->

           <!--  <select id="divGroups" name="group[]" ata-width="100%" class="selectpicker form-control" multiple data-size="5" data-selected-text-format="count>2" required> -->
              <select class="form-control required" name="group_id" id="group_id">
                <option value="">Select</option>
                <?php foreach ($groups as $gr) { ?>
                <option value="<?php echo $gr->id ?>" <?php echo $get_teacher_resource->group_id == $gr->id?'selected':'' ?>><?php echo $gr->group_name ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
                <label for="group">Class:<span class="mandatory">*</span></label>
                <?php

                  $group_id = $get_teacher_resource->group_id;
                  if (check_userrole_by_code('UT01')) {
                    $classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classtime ct INNER JOIN ss_classes c ON ct.class_id = c.id where 
                    ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and c.is_active = '1' and  c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
                  } elseif (check_userrole_by_code('UT02')) {
                    $classes = $db->get_results("SELECT DISTINCT c.class_name, c.id FROM ss_classes c INNER JOIN ss_classtime ct ON c.id = ct.class_id 
                    INNER JOIN ss_staffclasstimemap sctm ON ct.id = sctm.classtime_id WHERE c.is_active = 1 AND ct.is_active = 1 AND sctm.active = 1 
                    AND sctm.staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND group_id = '" . $group_id . "' AND 
                    c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and ct.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                    and sctm.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ORDER BY c.disp_order");
                  }
                ?>
                <select class="form-control required" name="subject" id="subject" >
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $cls) { ?>
                  <option value="<?php echo $cls->id ?>" <?php echo $get_teacher_resource->subject_id == $cls->id?'selected':'' ?>><?php echo $cls->class_name ?></option>
                  <?php } ?>
                </select>
            </div>
        </div>
         
          <div class="col-md-3">
            <div class="form-group">
              <label>Status <span class="mands">*</span></label>
              <select class="form-control required" name="status">
                <option value="">Select</option>
                <option value="1" <?php echo $get_teacher_resource->status == '1'?'selected':'' ?>>Active</option>
                <option value="0" <?php echo $get_teacher_resource->status == '0'?'selected':'' ?>>Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message <span class="mands">*</span></label>
              <label class="error" id="statusMsgcomm"></label>
               <textarea id="summernote" name="message" ><?php echo $get_teacher_resource->message ?></textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group" id="attach_box">
              <label>Attachment</label>
             
                <?php foreach ($get_class_board_attach as $key =>$value) { ?>
                <div class="row  attachrow<?php echo $value->id ?>">
                <div class="col-md-8">
                  <a href="<?php echo $value->attachment_file_path ?>" target="_blank"> Attachment <?php echo ($key+1) ?></a>
                </div>
                 <div class="col-md-4"><a href="javascript:void(0)" data-id="<?php echo $value->id ?>" class="old_remove_attachment"> remove</a></div>
                 </div>
                <?php } ?>
                <input type="hidden" name="remove_attachment_id" id="remove_attachment_id" value="">
              
              <br>
              <div class="row">
                <div class="col-md-8">
                  <input type="file" name="attachmentfile[]" >
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
                  <input type="hidden" name="action" value="edit_teacher_resource">
                  <input type="hidden" name="id" value="<?php echo $_GET['id']?>">
                  <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">

          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<!-- /Content area -->
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->

  
<script>
  $(document).ready(function(e) {
    var remove_attach_ids = "";
    
    CKEDITOR.replace('summernote', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });
    //REMOVE UPLOADED FILE
    $(document).on('click', '.remove_attachment', function() {
      $(this).parent().parent().remove();
    });

    //ADD NEW ATTACHMENT
    $("#add_more_attachments").click(function() {
      $('#attach_box').append('<div class="row mt-10"><div class="col-md-8"><input type="file" name="attachmentfile[]"></div><div class="col-md-4"><a href="javascript:void(0)" class="remove_attachment">remove</a></div></div>');
    });


    $('.btn.dropdown-toggle').click(function() {
      var id = $(this).data('id');
      $('#' + id + '-error').css('display', 'none');
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

    $('#frmICJC').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.summernote.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');

      CKEDITOR.instances.summernote.updateElement();


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
              setTimeout(function() {
                location.reload();
              }, 2000);
             
              //$("#frmICJC")[0].reset();
              // CKEDITOR.instances.summernote.setData('');


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

     setTimeout(function(){ 
        $('.ajaxMsgBot').hide();
        }, 5000);

    $(document).on('click', '.old_remove_attachment', function(){
        var attach_id = $(this).data('id');
        $('.attachrow'+attach_id).remove();
        var comm_sep_val = attach_id+',';
        remove_attach_ids = remove_attach_ids+comm_sep_val;
        $('#remove_attachment_id').val(remove_attach_ids);
    });
  });

 
</script>
<?php include "../footer.php" ?>