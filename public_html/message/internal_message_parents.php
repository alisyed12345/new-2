<?php 
$mob_title = "New Message";
include "../header.php";
//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if($_SESSION['icksumm_uat_login_usertypecode'] != 'UT01' && !in_array("su_permissions_create", $_SESSION['login_user_permissions'])){
	include "../includes/unauthorized_msg.php";
	return;
} 
?>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Message</h4>
    </div> 
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo $SITEURL."parents/dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">New Message</li>
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
          
          <div class="col-md-4 multi-select-full rec_staff">
            <div class="form-group">
           <label>Staff</label>
            
          <?php  

         /* $stus_user_ids = $db->get_results("SELECT s.user_id FROM ss_family f INNER JOIN ss_student s ON s.family_id = f.id WHERE f.user_id='".$_SESSION['scmp_login_userid']."' ");*/
          
         $staffs = $db->get_results("SELECT DISTINCT sctm.staff_user_id, st.first_name, st.last_name FROM    ss_family f 
				INNER JOIN ss_student s ON s.family_id = f.id INNER JOIN ss_user u ON u.id = s.user_id 
				INNER JOIN `ss_studentgroupmap` sgm ON sgm.student_user_id = u.id AND sgm.latest = 1
				INNER JOIN `ss_classtime` ct ON ct.class_id = sgm.class_id
				INNER JOIN `ss_staffclasstimemap` sctm ON sctm.classtime_id = ct.id
				INNER JOIN `ss_staff` st ON st.user_id = sctm.staff_user_id
        INNER JOIN `ss_staff_session_map` ssm ON ssm.session_id = st.user_id
				WHERE f.user_id='".$_SESSION['scmp_login_userid']."' AND ct.is_active=1 AND sctm.active=1 
        AND u.`is_active` = 1 AND u.`is_deleted` = 0 AND ssm.`session_id` = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");
          ?>

             
             <select class="form-control required" id="staff" name="staff">
                <option value="">Select</option>
                <?php foreach($staffs as $sta){
					if($sta->id != $_SESSION['scmp_login_userid']){
				?>
                <option value="<?php echo $sta->staff_user_id ?>" ><?php echo $sta->first_name.' '.$sta->last_name.' '.$sta->user_type ?></option>
                <?php } } ?>
              </select>

            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Message</label>
              <textarea class="form-control required" id="message" name="message"></textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-10 text-right">
            <div class="form-group">
              <div class="ajaxMsgBot"></div>
            </div>
          </div>
          <div class="col-md-2 text-right">
            <div class="form-group">
              <input type="hidden" name="action" value="save_message">
              <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div> 
<!-- /Content area --> 
<script>
$(document).ready(function(e) {
    $('.recipient').click(function(){
		if($(this).val() == 'student'){
			$('.rec_student').removeClass('hide');
			$('.rec_staff').addClass('hide');
			$('#staff').val('');
		}else{
			$('.rec_student').addClass('hide');
			$('.rec_staff').removeClass('hide');
			$('#group').val('');
			$('#student').html('<option value="">Select</option>');
		}
	});
	
	$('#group').change(function(){
		$('#student').html('<option value="">Loading...</option>');
		
		var targetUrl = '<?php echo $SITEURL ?>ajax/ajss-student';
		$.post(targetUrl,{group_id:$('#group').val(),action:'student_of_group'},function(data,status){
			if(status == 'success' && data.code == 1){
				$('#student').html(data.optionVal);
			}else{
				$('#student').html('<option value="">Select</option>');
			}
		},'json');	
	});
	
	$('#frmICK').submit(function(e){
        e.preventDefault();
		
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo $SITEURL ?>ajax/ajss-message';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){			
						$("input[name=message_to][value=staff]").prop('checked', true);
						$('#staff').val('');
						$('#group').val('');
						$('#student').html('<option value="">Select</option>');
						$('#message').val('');
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
	
	//$('.bootstrap-select').selectpicker();
});
</script>
<?php include "../footer.php" ?>
