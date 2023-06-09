<?php 
$mob_title = "Mass Email";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_communicate_sent_text_create", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<style>
.bootstrap-select{
    width: 100% !important;
    padding-top:2px !important;
    font-size: 15px !important; 
}
</style>
<!-- Page header --> 
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>New Text Message</h4>
    </div> 
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li> <a href="<?php echo SITEURL."message/mass_text_msg_history" ?>">Text Message Sent</a></li><li class="active">New Text Message </li>
    </ul>
  </div>
</div> 
<!-- /page header --> 
<!-- Content area -->
<div class="content content-box">
  <form id="frmICK" class="form-validate-jquery" method="post">
    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
        <div class="ajaxMsg"></div>
        <div class="row">          
          <div class="col-md-4">   
            <div class="form-group">
            <label>Group <span class="mandatory">*</span></label> 
					
              <?php 
                if(check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')){
                $groups = $db->get_results("SELECT * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                AND is_active = 1 and is_deleted = 0 order by group_name asc"); 
              }else{
                //$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 and id in (select group_id from ss_staffgroupmap where staff_user_id='".$_SESSION['icksumm_uat_login_userid']."' and active = 1) order by group_name asc"); 
                $groups = $db->get_results("select g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id 
                WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND g.is_active=1 and g.is_deleted=0 
                and d.id in (select classtime_id from ss_staffclasstimemap where staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' 
                and active=1) order by group_name asc");
              }
              ?>
              <select class="form-control required" id="group" name="group" checkMsgToGroup="true">
                <option value="">Select </option>
                <option value="all_groups">All Groups</option>
                <?php foreach($groups as $gr){ ?>
                <option value="<?php echo $gr->id ?>" ><?php echo $gr->group_name ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="form-group">
                <label for="group">Subject <span class="mandatory">*</span></label>
                <select class="form-control required" name="subject" id="subject" >
                <option value="">Select</option>                                  
                </select>
            </div>
        </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Parents Of <span class="mandatory">*</span></label>
              <select class="bootstrap-select required" multiple="multiple" id="student" name="student[]" style="float: none;">

              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message (Maximum 135 Characters)<span id="charNum"></span> <span class="mandatory">*</span></label>
              <textarea class="form-control required" id="message" name="message" onkeyup="countChar(this)" style="height:100px;" ></textarea>            
            </div>
          </div>
        </div>
        <div class="row">
              
          <div class="col-md-12 text-right">
            <div class="ajaxMsgBot"></div>
              <input type="hidden" name="action" value="save_mass_text_msg_to_queue">
              <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<!-- /Content area --> 
<script>
$(document).ready(function(e) {
	//VALIDATION - PARENTS IS REQUIRED, IF GROUP IS SELECTED
	jQuery.validator.addMethod("checkParentSel", function(value, element) {
		if($("#group").val() != ''){
			return ($('#student').val() != '');
		}else{
			return true;
		}
	}, "Required");

/*	$('#teacher').change(function(){
		$('#group').html('<option value="">Loading...</option>');
		$('#group').selectpicker('refresh');
		
		$('#student').html('<option value="">Select</option>');
		$('#student').selectpicker('refresh');
		
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-group';
		$.post(targetUrl,{teacher_id:$('#teacher').val(),action:'fetch_grp_of_teachers_for_select'},function(data,status){
			if(status == 'success' && data.code == 1){
				$('#group').html('<option value="">Select</option>');
				$('#group').append('<option value="all_groups">All Groups</option>');
				$('#group').append(data.optionVal);
			}else{
				$('#group').html('<option value="">Select</option>');
			}
			$('#group').selectpicker('refresh');
		},'json');	
	});*/


    $('#group').change(function(){
        $('#group_id').val($(this).val()); 

    if($('#group').val() == ''){
            $('#subject').html('<option value="">Select</option>');
    }else{
            //SUBJECT
            $('#subject').html('<option value="">Loading...</option>');
            
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
            $.post(targetUrl,{group_id:$('#group').val(),action:'fetch_assigned_group_class_for_select'},function(data,status){
                if(status == 'success' && data != ''){
                    $/*('#subject').html(data);*/
                     $('#subject').html('<option value="">Select Subject</option>');
                    $('#subject').append('<option value="all_subjects">All Subjects</option>');
                    $('#subject').append(data);
                }else{
                    $('#subject').html('<option value="">Select Subject</option>');
                }
            });

    }
  });


    $('#subject').change(function(){
    $('#subject').val($(this).val()); 

        if($('#subject').val() == ''){
            $('#student').html('<option value="">Select</option>');
        }else{
            
            //STUDENT
          //  $('#student').html('<option value="">Loading...</option>');
           $('#student').selectpicker('refresh');

            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
            $.post(targetUrl,{group_id:$('#group').val(),class_id:$('#subject').val(),action:'get_students_of_group_for_select'},function(data,status){
                if(status == 'success' && data.code == 1){
                    // $('#student').html('<option value="">Select</option>');
                    // $('#student').append('<option value="all_students">All Students</option>');
                    $('#student').html(data.optionVal);
                }else{
                    $('#student').html('<option value="">Select</option>');
                }
                $('#student').selectpicker('refresh');
            },'json');
        }
    });

	
/*	$('#group').change(function(){
		$('#student').html('<option value="">Loading...</option>');
		$('#student').selectpicker('refresh');
		
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
		$.post(targetUrl,{teacher_id:$('#teacher').val(),group_id:$('#group').val(),action:'fetch_grp_stu_for_select'},function(data,status){
			if(status == 'success' && data.code == 1){
				$('#student').html('<option value="">Select</option>');
				$('#student').append('<option value="all_students">All Students</option>');
				$('#student').append(data.optionVal);
			}else{
				$('#student').html('<option value="">Select</option>');
			}
			$('#student').selectpicker('refresh');
		},'json');	
	});*/
	
	$('.btn.dropdown-toggle').click(function(){
		var id = $(this).data('id');
		$('#' + id + '-error').css('display','none');
	});
	
	$('#frmICK').submit(function(e){
        e.preventDefault();
			
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){	
            $('#frmICK')[0].reset();
						$('#message').val('');
            $("#student").val('default');
            $("#student").selectpicker("refresh");
						displayAjaxMsg(data.msg,data.code);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg('Error: Process failed');
				}
			},'json');
		}
	});

  $('#student').change(function() {
      var checkele = $('#student').val();
      if (checkele !== '') {
          $("#student-error").empty();
      }
  })

  $('#student').selectpicker().change(function() {
      toggleSelectAll($(this));
  }).trigger('change');
});
function toggleSelectAll(control) {
            var allOptionIsSelected = (control.val() || []).indexOf("all_students") > -1;

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
                    control.selectpicker('val', valuesOf(control.find('option:selected[value!=all_students]')));
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
