<?php 
$mob_title = "Edit Class Time";
include "../header.php"; 


//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!in_array("su_classes_edit", $_SESSION['login_user_permissions'])){
	include "../includes/unauthorized_msg.php";
	return;
} 

$classtime = $db->get_row("select * from ss_classtime where id='".$_GET['id']."'");

if($group->is_active == 1){
	$status = "active";
}elseif($group->is_active == 0){
	$status = "inactive";
}


?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Class Time</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL."group/classtime_list" ?>">Class Time List</a></li>
      <li class="active">Edit Class Time</li>
    </ul>
  </div>
  <div class="above-content"> <a href="javascript:history.go(-1)" class="last_page">Go Back To Last Page</a> </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
  <form id="frmICK" class="form-validate-jquery" method="post">
    <div class="panel panel-flat">
      <div class="panel-body">
      <div class="ajaxMsg"></div>
        <div class="row form-group">
          <div class="col-md-3">
            <label for="group">Group</label>
            <?php $groups = $db->get_results("select * from ss_groups where is_active = 1 and is_deleted = 0 AND
             session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' order by group_name asc"); ?>
            <select class="form-control" name="group" id="group" required>
              <option value="">Select</option>
              <?php foreach($groups as $grp){ ?>
              <option value="<?php echo $grp->id ?>" <?php echo $classtime->group_id==$grp->id?"selected='selected'":"" ?> ><?php echo $grp->group_name ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-3">
          <label for="group_class">Class</label>  
          <?php $classes = $db->get_results("select * from ss_classes where is_active = 1 AND  session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
           order by class_name asc"); ?>        
          <select class="form-control" name="group_class" id="group_class" required>
          <option value="">Select</option>
              <?php foreach($classes as $cls){ ?>
              <option value="<?php echo $cls->id ?>" <?php echo $classtime->class_id==$cls->id?"selected='selected'":"" ?>><?php echo $cls->class_name ?></option>
              <?php } ?>              
            </select>
          </div>
          <div class="col-md-3">
            <label for="is_active">Status</label>
            <select class="form-control" name="is_active" id="is_active" required>
              <option value="">Select</option>
              <option value="1" <?php echo $classtime->is_active=="1"?"selected='selected'":"" ?>>Active</option>
              <option value="0" <?php echo $classtime->is_active=="0"?"selected='selected'":"" ?>>Inactive</option>
            </select>
          </div>
        </div>
       
        <div class="row form-group">
          <div class="col-md-1">Time</div>
          <?php /*
           $school_days = $db->get_row("select school_opening_days from ss_client_settings");
           $test = unserialize($school_days->school_opening_days);
            foreach ($test as $day) { ?>
              <input type="hidden" name="no_of_days[]" value="<?php echo $day; ?>">   
          <?php } */ ?>
          
          <div class="col-md-2 col-xs-6">
            <select class="form-control" timeSlotCheck="true" required data-pairSlot="sun_to" name="sun_from" id="sun_from">
            <option value="">Select</option>
            <?php for($hour=0; $hour<24; $hour++){ $hour = str_pad($hour, 2, "0", STR_PAD_LEFT); ?>
            <?php for($min=0; $min<60; $min = $min + 5){ $min = str_pad($min, 2, "0", STR_PAD_LEFT); ?>
            <option value="<?php echo $hour.":".$min ?>" <?php echo $classtime->time_from == ($hour.":".$min.":00")?'selected="selected"':''?>><?php echo date('h : i A', strtotime($hour.":".$min)) ?></option>
            <?php } ?>
            <?php } ?> 
            </select>
          </div>
          
          <div class="col-md-2 col-xs-6">
            <select class="form-control endtime required" timeSlotCheck="true" greaterThan="true" data-pairSlot="sun_from" name="sun_to" id="sun_to" >
            <option value="">Select</option>
            <?php for($hour=0; $hour<24; $hour++){ $hour = str_pad($hour, 2, "0", STR_PAD_LEFT); ?>
            <?php for($min=0; $min<60; $min = $min + 5){ $min = str_pad($min, 2, "0", STR_PAD_LEFT); ?>
            <option value="<?php echo $hour.":".$min ?>" <?php echo $classtime->time_to == ($hour.":".$min.":00")?'selected="selected"':''?>><?php echo date('h : i A', strtotime($hour.":".$min)) ?></option>
            <?php } ?>
            <?php } ?>
            </select>
          </div>
        </div>       
        <div class="row form-group">
          <div class="col-md-10 text-right">
            <div class="ajaxMsgBot"></div>
            </div>
            <div class="col-md-2  text-right">
            <input type="hidden" name="action" value="edit_classtime">
            <input type="hidden" name="classtime_id" value="<?php echo $_GET['id'] ?>">
            
            <button type="submit" class="btn btn-success btnhide"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<script>
$( document ).ready(function() {
	$('#frmICK').submit(function(e){
		e.preventDefault();
    $('.btnhide').prop('disabled', true);
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
			$('.spinner').removeClass('hide');
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						displayAjaxMsg(data.msg,data.code);
            setTimeout(function(){
              $('.btnhide').prop('disabled', false);
            }, 2000);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
	});
});


       $endtime = $('.endtime').val();
      
        $.validator.addMethod('greaterThan', function(value, element) {
          var dateFrom = $('#sun_from').val();
          var dateTo = $('#sun_to').val();
          return dateTo > dateFrom;
        }, "End time should be greater then start time");

</script> 
<!-- /Content area -->
<?php include "../footer.php" ?>
