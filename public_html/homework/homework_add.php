<?php
$mob_title = "Homework";
include "../header.php";


//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_homework_create", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}
?>
<!-- Page header -->

<style>
.error {
  font-size: 13px !important;
}
</style>

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4 style="display:inline-block">Send Homework</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Send Homework</li>
        </ul>
    </div>
</div>
<!-- /page header -->
<style>
    .solidsucess {
        border: 1px black;
        background-color: #5bb95b;
        color: white;
        padding: 5px;
        margin-right: -152px;
    }

    .soliderror {
        border: 1px black;
        background-color: red;
        color: white;
        padding: 5px;
        margin-right: -152px;
    }
    .bootstrap-select{
        width: 100% !important;
        padding-top:2px !important;
        font-size: 15px !important;
        
    } 
</style>

<!-- Content area -->
<div class="content content-box">
    <div class="ajaxMsgBot">
        <?php
        if (isset($_SESSION['success'])) {
        ?>
            <div class="alert alert-success fade in">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>Success!</strong> <?php echo $_SESSION['success']; ?>
            </div>
        <?php
        }
        //unset($_SESSION['success']);
        if (isset($_SESSION['error'])) {
        ?>
            <div class="alert alert-danger fade in">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
            </div>
        <?php
        }
        //unset($_SESSION['error']);
        ?>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <form id="frmHomework"   method="post" enctype="multipart/form-data">
                <div class="panel panel-flat panel-flat-box">
                    <div class="panel-body panel-body-box">
                        <div class="ajaxMsg"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Group Name:<span class="mandatory">*</span></label>
                                    <?php

                                    if (check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')) {

                                        $groups = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND is_active=1 and is_deleted=0 order by group_name asc");
                                    } else {
                                        //$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 and id in (select group_id from ss_staffgroupmap where staff_user_id='".$_SESSION['icksumm_uat_login_userid']."' and active = 1) order by group_name asc"); 
                                        $groups = $db->get_results("select * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND is_active=1 and is_deleted=0 and id in (SELECT group_id FROM ss_classtime WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND id IN (SELECT classtime_id FROM ss_staffclasstimemap WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' and active = 1 )) order by group_name asc");
                                    }
                                    ?>
                                    <select class="form-control required" name="group" id="group" >
                                        <option value="">Select Group</option>
                                        <?php foreach ($groups as $grp) { ?>
                                            <option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Homework For:<span class="mandatory">*</span></label>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="bootstrap-select required" multiple="multiple" name="homework_target[]" id="homework_target"> 
                                                <option value="">Select Homework For</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Homework Attachment (allowed file pdf, png, jpg, jpeg ) :</label>
                                    <input type="file" class="form-control" name="homework_attechment" id="homework_attechment" accept="image/*,.pdf">
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="first_name">Homework:<span class="mandatory">*</span></label>
                                    <p id="statusMsgcomm" style="color:red;font-size:13px;"></p>
                                    <textarea placeholder="Enter Homework"  id="homework_text" name="homework_text"  class="form-control required"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot" style="margin-right:150px; margin-top:10px;">

                                    <?php
                                    if (isset($_SESSION['success'])) {
                                    ?>
                                        <span class="solidsucess"><?php echo $_SESSION['success']; ?></span>
                                    <?php
                                    }
                                    unset($_SESSION['success']);
                                    if (isset($_SESSION['error'])) {
                                    ?>

                                        <span class="soliderror"><?php echo $_SESSION['error']; ?></span>
                                    <?php
                                    }
                                    unset($_SESSION['error']);
                                    ?>

                                </div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="action" value="homework_add">
                                <input type="hidden" name="submit_form" value="form_seesion">
                                <input type="hidden" name="group_id" id="group_id" value="">
                                <button type="submit" class="btn btn-success btnsubmit">Send <i class="icon-spinner2 spinner hide marR10 insidebtn"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script src="http://cdn.ckeditor.com/4.6.2/standard-all/ckeditor.js"></script>
<script> 
    $(document).ready(function() {

        $('.btnsubmit').attr('disabled', false);
        setTimeout(function() {
            $(".ajaxMsgBot").hide();
        }, 3000);

    CKEDITOR.replace('homework_text', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });

    $('#frmHomework').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.homework_text.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');
    
    //   if (ckvalue.length === 0) {
    //     $('#statusMsgcomm').html('Required Field');
    //     return false;
    //   } else {
    //     $('#statusMsgcomm').html('');
    //   }
      CKEDITOR.instances.homework_text.updateElement();
      // var getcode = CKEDITOR.instances.summernote.getData();
      // getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
      // var div = document.createElement("div");
      // div.innerHTML = getcode;
      // var text = div.textContent || div.innerText || "";    

      if ($('#frmHomework').valid() && ckvalue.length > 0) {
        $('#statusMsgcomm').html('');
        $('.spinner').removeClass('hide');
        var formData = new FormData(this);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-homework';
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
              $("#frmHomework")[0].reset();
              $('#homework_target').selectpicker('refresh');
              CKEDITOR.instances.homework_text.setData('');
              $("#staff").selectpicker("refresh");


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




        //$('#group').change(function(){
        //	$('#group_id').val($(this).val()); 
        //});

        var myFile = "";
        $('#homework_attechment').on('change', function() {

            myFile = $("#homework_attechment").val();

            var upld = myFile.split('.').pop();
            console.log(upld);
            if (!(upld == 'pdf' || upld == 'jpeg' || upld == 'jpg' || upld == 'png')) {
                alert("Only PDF,JPEG,JPG,PNG are allowed");
                $("#homework_attechment").val('');
            }

        })

        $('#group').change(function() {
            $('#group_id').val($(this).val());

            if ($('#group').val() == '') {
                $('#subject').html('<option value="">Select Subject</option>');
                $('#homework_target').html('<option value="">No Records</option>');
                $('#homework_target').selectpicker('refresh');
            } else {
                //SUBJECT
                $('#subject').html('<option value="">Loading...</option>');

                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
                $.post(targetUrl, {
                    group_id: $('#group').val(),
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

        $('#homework_target').change(function() {
            var checkele = $('#homework_target').val();
            if (checkele !== '') {
                $("#homework_target-error").empty();
            }
        })

        $('#homework_target').selectpicker().change(function() {
            toggleSelectAll($(this));
        }).trigger('change');


        $('#subject').change(function() {
            $('#subject').val($(this).val());

            if ($('#subject').val() == '') {
                $('#homework_target').html('<option value="">No Records</option>');
                $('#homework_target').selectpicker('refresh');
            } else {

                //STUDENT
                $('#homework_target').html('<option value="">Loading...</option>');

                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
                $.post(targetUrl, {
                    group_id: $('#group').val(),
                    class_id: $('#subject').val(),
                    action: 'student_of_group'
                }, function(data, status) {
                    if (status == 'success' && data.code == 1) {
                       if(data.optionVal == null){
                        $('#homework_target').html('<option value="">No Records</option>');
                       }
                       else{
                        $('#homework_target').html(data.optionVal);
                        $('#homework_target').selectpicker('refresh');
                       }
                    } else {
                        $('#homework_target').html('<option value="">Select</option>');
                    }
                    $('#homework_target').selectpicker('refresh');
                }, 'json');
            }
        });


        // $(".btnsubmit").click(function() {
        //     if ($('#frmICK').valid()) {
        //         var getcode = CKEDITOR.instances.homework_text.getData();
        // var ckvalue =  getcode.replace(/<[^>]*>/gi, '').trim();
        // ckvalue =  ckvalue.replace(/&nbsp;/g, '');           
        // if(ckvalue.length===0){
        //     $('#statusMsgcomm').html('Required Field');
            
        //     console.log(ckvalue);
        //     return false;
        // }else{
        //     $('#statusMsgcomm').html('');
        // }
        //         $('.btnsubmit').attr('disabled', true);
        //         $('.spinner').removeClass('hide');
        //         $("#frmICK").submit();
        //         return true;
        //     } else {
        //         return false;
        //     }

        // });

        // $('#frmICK').submit(function(e){
        // 	e.preventDefault();
        // 	if($('#frmICK').valid()){
        // 		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-homework';
        // 		$('.spinner').removeClass('hide');

        // 		var formDate = $(this).serialize();
        // 		$.post(targetUrl,formDate,function(data,status){					
        // 			if(status == 'success'){
        // 				if(data.code == 1){
        // 					displayAjaxMsg(data.msg,data.code);
        // 					$( "#frmICK" )[0].reset();
        // 				}else{
        // 					displayAjaxMsg(data.msg,data.code);
        // 				}
        // 			}else{
        // 				displayAjaxMsg(data.msg);
        // 			}
        // 		},'json');
        // 	}
        // });
    });

    function toggleSelectAll(control) {
            var allOptionIsSelected = (control.val() || []).indexOf("whole_group") > -1;

            function valuesOf(elements) {
                return $.map(elements, function(element) {
                    return element.value;
                });
            }

            if (control.data('allOptionIsSelected') != allOptionIsSelected) {
                // User clicked 'All' option
                if (allOptionIsSelected) {
                    // Can't use .selectpicker('selectAll') because multiple "change" events will be triggered
                    control.selectpicker('val', valuesOf(control.find('option')));
                } else {
                    control.selectpicker('val', []);
                }
            } else {
                // User clicked other option
                if (allOptionIsSelected && control.val().length != control.find('option').length) {
                    // All options were selected, user deselected one option
                    // => unselect 'All' option
                    control.selectpicker('val', valuesOf(control.find('option:selected[value!=whole_group]')));
                    allOptionIsSelected = false;
                } else if (!allOptionIsSelected && control.val().length == control.find('option').length - 1) {
                    // Not all options were selected, user selected all options except 'All' option
                    // => select 'All' option too
                    control.selectpicker('val', valuesOf(control.find('option')));
                    allOptionIsSelected = true;
                }
            }
            control.data('allOptionIsSelected', allOptionIsSelected);
        }
</script>
<?php include "../footer.php" ?>