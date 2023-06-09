<?php
$mob_title = "Email Template";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_email_template_edit", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
$get_email_type = $db->get_results("SELECT * FROM ss_email_template_types WHERE STATUS = 1");

if(isset($_GET['id'])){
    $get_data = $db->get_row("SELECT etemp.email_template, etemp.email_subject, etemp.email_cc, etemp.email_bcc, etype.status, etype.type_name, etype.system_template FROM ss_email_templates etemp INNER JOIN ss_email_template_types etype ON etype.id = etemp.email_template_type_id WHERE etemp.id = '".$_GET['id']."'");
}
?>
<!-- Page header -->
<style>
.reg_form .row {
    text-align: left;
}

.reg_form .row [class^="col-"] {
    margin-top: 10px;
}

span.mands {
    color: #ff0000;
    display: inline;
    line-height: 1;
    font-size: 12px;
    margin-left: 5px;
}

label.error {
    color:red;
    margin-top: 0px;
    padding-left: 12px;
}

.error_cust {
    padding-left: 10px;
    z-index: 0;
    display: inline-block;
    margin-bottom: 7px;
    color: #f44336;
    position: relative;
}

.shoinline{
display: flex;
margin-left: -14px;
}

.form-check-inline{
    margin-left: 15px;
}

</style>
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Edit Custom Email Templates</h4>
    </div>
  </div>
  <div class="breadcrumb-line"> 
    <ul class="breadcrumb">
    <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
    <li><a href="<?php echo SITEURL ?>email_template/list_all_email_template"> Custom Email Templates</a></li>
    <li class="active">Edit Custom Email Template</li>
    </ul>
  </div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<div class="panel panel-flat">
    <div class="panel-body">

    <!-- Advanced login -->
    <form name="frm_email_template"  id="frm_email_template" method="post">
         
        <legend class="text-semibold">Email Template Info</legend>
       
          <div class="row">
            <div class="col-md-3">
            <div class="form-group">
                <label>Email Template Title<span class="mands">*</span></label>
                <?php if($get_data->system_template == 1){ ?>
                <input type="text" name="email_template_type" class="form-control required" value="<?= $get_data->type_name ?>" readonly>
                <?php }else{ ?>
                <input type="text" name="email_template_type" class="form-control required" value="<?= $get_data->type_name ?>">
                <?php } ?>
            </div>
            </div>
            <div class="col-md-3">
            <div class="form-group">
                <label>status<span class="mands">*</span></label>
                <select class="form-control required" name="status">
                <option value="">Select</option>
                <option value="1" <?php echo $get_data->status=="1"?"selected='selected'":""?>>Active</option>
                <option value="0" <?php echo $get_data->status=="0"?"selected='selected'":""?>>Inactive</option>
                </select>
            </div>
            </div>
            <?php if(!check_userrole_by_code('UT01') && !check_userrole_by_group('admin')){ if(isset($get_data->system_template)){ ?>
            <input type="hidden"  id="system_temp" name="system_temp" value="<?php echo $get_data->system_template;?>" class="required">
            <?php } else { ?>
            <input type="hidden"  id="system_temp" name="system_temp" value="0" class="required">
            <?php } } ?>
            <?php if(check_userrole_by_code('UT01') && check_userrole_by_group('admin')){ ?>
            <div class="col-md-3">
            <div class="form-group">
                <label>System Template<span class="mands">*</span></label>
                   <div class="form-group">
                      <label class="radio-inline">
                        <input type="radio"  id="system_temp" name="system_temp" value="1" class="required" <?php echo $get_data->system_template=="1"?"checked='checked'":""?>> YES
                      </label>
                      <label class="radio-inline">
                        <input type="radio" id="system_temp" name="system_temp" value="0" class="required" <?php echo $get_data->system_template=="0"?"checked='checked'":""?>> NO
                      </label>
                      <label id="system_temp-error" class="error" for="system_temp" style="display: none;">Required field</label>
                    </div>
                </div>
            </div>
            <?php } ?>
            <a href="javascript:void(0)" style="margin-top: 2px; float:right; margin-right: 10px;" id="support"><i class="fas fa-question-circle"></i> Keyword Help</a>
            </div>
            <div class="row">
            <div class="col-md-6">
            <div class="form-group">
                <label>Subject: <span class="mands">*</span></label>
                 <input type="text" class="form-control required"   name="email_subject" id="email_subject" placeholder="Subject" value="<?= $get_data->email_subject ?>" >
            </div>
            </div>
            <div class="col-md-3">
            <div class="form-group">
                <label>CC: </label>
                <input type="text" name="email_cc" id="email_cc" emailCommaSep="true" value="<?= $get_data->email_cc ?>" placeholder="CC"
                    class="form-control bgcolor-white">
            </div>
            </div>

            <div class="col-md-3">
            <div class="form-group">
                    <label>BCC:</label>
                    <input type="text" class="form-control" emailCommaSep="true"  name="email_bcc" id="email_bcc" placeholder="BCC" value="<?= $get_data->email_bcc ?>" >
              </div>
           </div>
          
       </div>
       <br>
        <div class="row" style="margin-top:-20px;">
                <div class="col-md-12">
                    <div class="form-group">
                    <label>Template Body: <span class="mands">*</span></label>  <label id="statusMsgcomm" style="color:red"></label>
                    <textarea id="summernote" name="template_body" ><?= $get_data->email_template ?></textarea>
                    </div>
                </div>
                
        </div>

       <br>
        <div class="row" style="margin-bottom:50px;">
            <div class="col-md-10 col-xs-8">
                <div class="ajaxMsgBot pull-right"></div>
            </div>
            <div class="col-md-2 col-xs-4">
                <input type="hidden" name="email_temp_id" value="<?php echo $_GET['id'] ?>">
                <input type="hidden" name="action" value="edit_email_template">
                <input type="submit" value="Submit" class="btn btn-success btn-block btnsubmit" tabindex="225">
            </div>
        </div>
    </form>


</div>
<!-- /content area -->
</div>
</div>
<!-- START SCHEDULE MODEL START -->
<div id="modalemailtemp" class="modal fade">
  <div class="modal-dialog modal-dialog-centered" style="width:800px !important;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title headtext">Keyword Help</h5>
      </div>
      <hr>
      <div class="modal-body">
      <table class="table table-bordered" style="margin-top: -20px;">
        <tbody>
            <tr>
            <th scope="row">Parent1 First Name</th>
            <td>{parent1_first_name}</td>
            </tr>
            <tr>
            <th scope="row">Parent1 Phone</th>
            <td>{parent1_phone}</td>
            </tr>
            <tr>
            <th scope="row">Parent1 Email</th>
            <td>{parent1_email}</td>
            </tr>
            <tr>
            <th scope="row">Parent2 First Name</th>
            <td>{parent2_first_name}</td>
            </tr>
            <tr>
            <th scope="row">Parent2 Phone</th>
            <td>{parent2_phone}</td>
            </tr>
            <tr>
            <th scope="row">Parent2 Email</th>
            <td>{parent2_email}</td>
            </tr>
            <tr>
            <th scope="row">Address1</th>
            <td>{address_1}</td>
            </tr>
            <tr>
            <th scope="row">Address2</th>
            <td>{address_2}</td>
            </tr>
            <tr>
            <th scope="row">City</th>
            <td>{city}</td>
            </tr>
            <tr>
            <th scope="row">State Name</th>
            <td>{state_name}</td>
            </tr>
            <tr>
            <th scope="row">Child1 First Name</th>
            <td>{child1_first_name}</td>
            </tr>
            <tr>
            <th scope="row">Child1 Last Name</th>
            <td>{child1_last_name}</td>
            </tr>
            <tr>
            <th scope="row">Child1 DOB</th>
            <td>{child1_dob}</td>
            </tr>
            <tr>
            <th scope="row">Child1 Grade</th>
            <td>{child1_grade}</td>
            </tr>
            <tr>
            <th scope="row">Child1 Gender</th>
            <td>{child1_gender}</td>
            </tr>
            <tr>
            <th scope="row">Child1 Allergies</th>
            <td>{child1_allergies}</td>
            </tr>
            <tr>
            <th scope="row">Group</th>
            <td>{group}</td>
            </tr>
            <tr>
            <th scope="row">Class</th>
            <td>{class}</td>
            </tr>
       
        </tbody>
        </table>
      </div>
      <div class="modal-footer">
         <div class="row">
            <div class="col-md-9">
               <strong id="statusMsg"></strong>
            </div>
             <div class="col-md-3">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
  </div>
</div>


<!-- /Content area -->
<script src="<?php echo SITEURL ?>assets/js/jquery-ui.min.js"></script>
<!-- <script src="<?php echo SITEURL ?>assets/js/jquery-ui-timepicker-addon.js"></script> -->
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script type="text/javascript">


jQuery(document).ready(function() {

    $("#frm_email_template").validate({
    ignore: ".note-editor *"
  });

jQuery.validator.addMethod("emailCommaSep", function(value, element) {
var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
var str_array = value.split(',');

for(var i = 0; i < str_array.length; i++) {
    if($.trim(str_array[i]) != ''){
        if(!re.test(String($.trim(str_array[i])).toLowerCase())){
            return false;
        }
    }
}
	
	return true;
}, "Enter valid email");

CKEDITOR.replace('summernote', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });

$('#frm_email_template').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.summernote.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');

      CKEDITOR.instances.summernote.updateElement();


      if ($('#frm_email_template').valid() && ckvalue.length > 0) {
        $('#statusMsgcomm').html('');
        $('.spinner').removeClass('hide');
        var formData = new FormData(this);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-email-template'; 
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
              // $("#frm_email_template")[0].reset();
            //    CKEDITOR.instances.summernote.setData('');


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

$(document).on('click','#support',function() {
     	  $('.headtext').html("Keyword Help");
          $('#modalemailtemp').modal('show');
     });

});

</script>
<?php include "../footer.php" ?>