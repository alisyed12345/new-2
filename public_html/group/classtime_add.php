<?php 
$mob_title = "Add Class Time";
include "../header.php"; 

  
//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!in_array("su_classes_create", $_SESSION['login_user_permissions'])){
	include "../includes/unauthorized_msg.php";
	return;
}  

//where is_new_registration_open = 1 and status = 1
//$school_days = $db->get_row("select school_opening_days from ss_client_settings");
//$test = unserialize($school_days->school_opening_days);

?>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Add New Class Time</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL."group/classtime_list" ?>">Manage Class Time</a></li>
      <li class="active">Add New Class Time</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
  <form id="frmICK" name="frmICK" class="form-validate-jquery" method="post">
    <div class="panel panel-flat">
      <div class="panel-body">
      <div class="ajaxMsg"></div>
        <div class="row">
          <div class="col-md-4">
            <label for="group">Group <span class="mandatory">*</span></label>
            <?php $groups = $db->get_results("select * from ss_groups where is_active = 1 and is_deleted = 0 
            AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' order by group_name asc"); ?>
            <select class="form-control required" name="group" id="group">
              <option value="">Select</option>
              <?php foreach($groups as $grp){ ?>
              <option value="<?php echo $grp->id ?>" ><?php echo $grp->group_name ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-4">
          <label for="group_class">Class <span class="mandatory">*</span></label>  
          <?php $classes = $db->get_results("select * from ss_classes where is_active = 1 AND 
          session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' order by class_name asc"); ?>        
          <select class="form-control required" name="group_class" id="group_class" data-width="100%">
              <option value="">Select</option>
              <?php foreach($classes as $cls){ ?>
              <option value="<?php echo $cls->id ?>" ><?php echo $cls->class_name ?></option>
              <?php } ?>              
            </select>
          </div>
          <div class="col-md-4">
            <label for="is_active">Status <span class="mandatory">*</span></label>
            <select class="form-control required" name="is_active" id="is_active">
              <option value="">Select</option>
              <option value="1" >Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        
        <br>
        <div class="row">
          <div class="col-md-1" style="margin-top:30px;">Class time</div>
          
          <div class="col-md-2 col-xs-6">
            <label for="sun_from">Start Time <span class="mandatory">*</span></label> 
            <select class="form-control required" data-pairSlot="sun_to" name="sun_from" id="sun_from">
            <option value="">Select From</option>
            <?php for($hour=0; $hour<24; $hour++){ $hour = str_pad($hour, 2, "0", STR_PAD_LEFT); ?>
            <?php for($min=0; $min<60; $min = $min + 5){ $min = str_pad($min, 2, "0", STR_PAD_LEFT); ?>
            <option value="<?php echo $hour.":".$min ?>"><?php echo date('h : i A', strtotime($hour.":".$min)) ?></option>
            <?php } ?>
            <?php } ?> 
            </select>
                          <label id="sun_from-error" class="validation-error-label" for="sun_from" style="display: none;">This field is required</label> 

          </div>
          <div class="col-md-2 col-xs-6">
           <label for="sun_to">End Time <span class="mandatory">*</span></label> 
            <select class="form-control endtime required"  data-pairSlot="sun_from" name="sun_to" id="sun_to" greaterThan="true" onchange="childdiv()">
            <option value="">Select To</option>
            <?php for($hour=0; $hour<24; $hour++){ $hour = str_pad($hour, 2, "0", STR_PAD_LEFT); ?>
            <?php for($min=0; $min<60; $min = $min + 5){ $min = str_pad($min, 2, "0", STR_PAD_LEFT);?>
              
            <option value="<?php echo $hour.":".$min ?>"><?php echo date('h : i A', strtotime($hour.":".$min)) ?></option>
            <?php } ?>
            <?php } ?>
            </select>
            <label id="sun_to-error" class="validation-error-label" for="sun_to" style="display: none;">This field is required</label>
          </div>
        </div>
        <?php /*
        foreach($test as $day){
        if($day == 1){
          $d = 'Monday';
        }elseif($day == 2){
          $d = 'Tuesday';
        }elseif($day == 3){
          $d = 'Wednesday';
        }elseif($day == 4){
          $d = 'Thursday';
        }elseif($day == 5){
          $d = 'Friday';
        }elseif($day == 6){
          $d = 'Saturday';
        }elseif($day == 0){
          $d = 'Sunday';
        }
        ?>
        <input type="hidden" name="no_of_days[]" value="<?php echo $day; ?>">   
        <?php } */ ?> 
        <div class="row form-group">
          <div class="col-md-3">
            
          </div>
          <div class="col-md-2 col-xs-6">
              
          </div>
        </div>     
        <div class="row form-group">
          <div class="col-md-10 text-right">
            <div class="ajaxMsgBot"></div>
            </div>
            <div class="col-md-2  text-right">
            <input type="hidden" name="action" value="add_classtime">
           
            <button type="submit" class="btn btn-success" id="button_work"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<script>
$( document ).ready(function() {
	$('#frmICK').submit(function(e){
    $('#button_work').prop('disabled', true);
		e.preventDefault();
		
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						displayAjaxMsg(data.msg,data.code);
						$( "#frmICK" )[0].reset();
            $(".select2").val('').trigger('change');
            $(".select2").change();
            setTimeout(function(){
              $('#button_work').prop('disabled', false);
            }, 2000);
					}else{
						displayAjaxMsg(data.msg,data.code);
            setTimeout(function(){
              $('#button_work').prop('disabled', false);
            }, 2000);
					}
				}else{
					displayAjaxMsg(data.msg);
          setTimeout(function(){
              $('#button_work').prop('disabled', false);
            }, 2000);
				}
			},'json');
		}
	});

  /*$('#group').change(function(){
    var groupid = $(this).val();
    if(groupid == ''){
      $('#group_class').html('<option value="">Select</option>');
    }else{
      $.post('<?php echo SITEURL ?>ajax/ajss-classes.php',{groupid:groupid,action:'fetch_group_classes'},function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						$('#group_class').html(data.classes);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
    }    
  });*/
});
function childdiv() {
       $endtime = $('.endtime').val();
        $.validator.addMethod('greaterThan', function(value, element) {
          var dateFrom = $('#sun_from').val();
          var dateTo = $('#sun_to').val();
          return $endtime > dateFrom;
        }, "End time should be greater then start time");
}
</script>  
<!-- /Content area -->
<?php include "../footer.php" ?>
