<?php 
$mob_title = "Edit Staff";
include "../header.php";

  
//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!in_array("su_staff_edit", $_SESSION['login_user_permissions'])){
	include "../includes/unauthorized_msg.php";
	exit;
}
 
$all_staffs = $db->get_row("SELECT id, CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) AS staff_name,  mobile, email, (CASE WHEN is_request=1 THEN 'Active' ELSE 'Panding' END) AS status FROM ss_staff_registration WHERE is_request = 0 AND session ='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND id = '".$_GET['id']."'");
$user = $db->get_row("select * from ss_user where email = '".$all_staffs->email."'");
$staff = $db->get_row("select * from ss_staff where user_id = '".$user->id."'");

$user_types = $db->get_results("SELECT user_type_id  FROM ss_usertypeusermap where user_id = '".$_GET['id']."'");
$user_types_ary = array();
foreach($user_types as $ut){
	$user_types_ary[] = $ut->user_type_id;
}

if($user->is_deleted == 1){
	$status = "delete_soft";
}elseif($user->is_active == 1){
	$status = "active";
}elseif($user->is_active == 0){
	$status = "inactive";
}

if(verifyDate($staff->joining_date)){
	$joining_date = date('d F, Y',strtotime($staff->joining_date));
}

if(verifyDate($staff->dob)){
	$dob = date('d F, Y',strtotime($staff->dob));
}
?>  
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Staff</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL."staff/staffs_list" ?>">Staff Register List</a></li>
      <li class="active">Edit Register Staff</li>
    </ul>
  </div>
  <div class="above-content">
  <a href="javascript:history.go(-1)" class="last_page">Go Back To Last Page</a>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
  <div class="row">
    <div class="col-lg-12">
      <form id="frmICK" class="form-validate-jquery" method="post">
        <div class="panel panel-flat">
          <div class="panel-body">
            <div class="ajaxMsg"></div>
            <legend class="text-semibold"><i class="icon-user position-left"></i> Personal Information</legend>
            
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="first_name">First Name:<span class="mandatory">*</span></label>
                  <input placeholder="First Name" spacenotallow="true" id="first_name" lettersonly="true" name="first_name" required class="form-control required" type="text" value="<?php echo $staff->first_name ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="middle_name">Middle Name:</label>
                  <input placeholder="Middle Name" name="middle_name" lettersonly="true" id="middle_name" class="form-control" type="text" value="<?php echo $staff->middle_name ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="last_name">Last Name: <span class="mandatory">*</span></label>
                  <input placeholder="Last Name" spacenotallow="true" name="last_name" lettersonly="true" id="last_name" class="form-control required" type="text" value="<?php echo $staff->last_name ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Gender:<span class="mandatory">*</span></label>
                  <div class="col-md-12">
                    <label class="radio-inline">
                      <input type="radio" required <?php echo $staff->gender=="m"?'checked="checked"':'' ?> name="gender" id="gender_m" value="m">
                      Male </label>
                    <label class="radio-inline">
                      <input type="radio" id="gender" <?php echo $staff->gender=="f"?'checked="checked"':'' ?> name="gender" id="gender_f" value="f">
                      Female </label>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="dob">Date of Birth:</label>
                  <input placeholder="Date of Birth" id="dob" name="dob" class="form-control" type="text" value="<?php echo $dob ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Staff Type:<span class="mandatory">*</span></label>
                  <?php $userType = $db->get_results("select * from ss_usertype where is_active=1 and user_type_group = 'staff'"); ?>
                  <!--<select class="select form-control required" name="user_type" id="user_type">-->
                  <select class="bootstrap-select" multiple="multiple" data-width="100%" id="user_type" name="user_type[]" required>
                    <!--<option value="">Select</option>-->
                    <?php foreach($userType as $type){ ?>
                    <option value="<?php echo $type->id ?>" <?php echo in_array($type->id,$user_types_ary)?'selected="selected"':'' ?>><?php echo $type->user_type ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <label for="status">Status</label>
                <select class="select form-control" name="status" id="status" required>
                  <option value="">Select</option>
                  <option value="active" <?php echo $status == "active"?'selected="selected"':'' ?>>Active</option>
                  <option value="inactive" <?php echo $status == "inactive"?'selected="selected"':'' ?>>Inactive</option>
                  <option value="delete_soft" <?php echo $status == "delete_soft"?'selected="selected"':'' ?>>Delete </option>
                  <?php /*?><option value="delete_hard" <?php echo $status == "delete_hard"?'selected="selected"':'' ?>>Delete (Permanent)</option><?php */?>
                </select>
              </div>
            </div>
            <div class="row">
              <!-- <div class="col-md-4">
                <div class="form-group">
                  <label>Username:<span class="mandatory">*</span></label>
                  <input placeholder="Username" readonly id="username" class="form-control" type="text" value="<?php echo $user->username ?>">
                </div>
              </div> -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Email:<span class="mandatory">*</span></label>
                  <input placeholder="Email" name="email" id="email" required class="form-control" type="email" value="<?php echo $user->email ?>">                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Password:</label>
                  <input placeholder="Password" name="password" id="password" passwordCheck="true" class="form-control" type="password">
                  <div class="help-block">Leave it blank, if you don't want to change password</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Confirm Password:</label>
                  <input placeholder="Confirm Password" equalTo="#password" name="confirm_password" id="confirm_password" class="form-control" type="password">
                </div>
              </div>
            </div>
            <legend class="text-semibold"><i class="icon-envelop position-left"></i>Contact Information</legend>
            <div class="row">
              <!-- <div class="col-md-4">
                <div class="form-group">
                  <label>Email:<span class="mandatory">*</span></label>
                  <input placeholder="Email" name="email" required class="form-control" type="email" value="<?php echo $user->email ?>">
                </div>
              </div> -->
            
              <div class="col-md-4">
                <div class="form-group">
                  <label>Primary No:<span class="mandatory">*</span></label>
                  <input placeholder="Primary No" name="mobile" maxlength="10" required PhoneNumber="true" class="form-control phone_no" id="mobile" type="text" value="<?php echo internal_phone_check($staff->mobile, 'edit') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Alternate No (xxx-xxx-xxxx):</label>
                  <input placeholder="Alternate No XXX-XXX-XXXX" name="phone" maxlength="12" PhoneNumber="true" class="form-control phone_no" id="phone" type="text" value="<?php echo internal_phone_check($staff->phone, 'edit') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Address Line 1:<span class="mandatory">*</span></label>
                  <input placeholder="Address Line 1" spacenotallow="true" name="address_1" required class="form-control" type="text" value="<?php echo $staff->address_1 ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Address Line 2:</label>
                  <input placeholder="Address Line 2" name="address_2" class="form-control" type="text" value="<?php echo $staff->address_2 ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>City:<span class="mandatory">*</span></label>
                  <input placeholder="City" required spacenotallow="true" name="city" lettersonly="true" class="form-control" type="text" value="<?php echo $staff->city ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>State:<span class="mandatory">*</span></label>
                  <?php $states = $db->get_results("select * from ss_state where is_active=1"); ?>
                  <select class="select form-control required" name="state_id" id="state_id">
                    <option value="">Select</option>
                    <?php foreach($states as $state){ ?>
                    <option value="<?php echo $state->id ?>" <?php echo $staff->state_id == $state->id?'selected="selected"':'' ?>><?php echo $state->state ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="country">Country:<span class="mandatory">*</span></label>
                  <?php $countrys = $db->get_results("select * from ss_country where is_active=1"); ?>
                  <select class="select form-control required" name="country_id" id="country_id">
                    <option value="">Select</option>
                    <?php foreach($countrys as $country){ ?>
                    <option value="<?php echo $country->id ?>" <?php echo $staff->country_id == $country->id?'selected="selected"':'' ?>><?php echo $country->country ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                   <label for="country">ZipCode:<span class="mandatory">*</span></label>
                  <input type="text" name="post_code" maxlength="5" id="post_code" value="<?php echo $staff->post_code ?>" zipCodeCheck="true" placeholder="Zip Code" class="form-control required" tabindex="205">
                 </div>
              </div>
            </div>
            <?php /*?><legend class="text-semibold"><i class="icon-camera position-left"></i>Photo</legend>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Photo:</label>
                  <input type="file" accept="image/*">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group"> </div>
              </div>
            </div><?php */?>
            <div class="row">
              <div class="col-md-10 text-right">
                <div class="ajaxMsgBot"></div>
              </div>
              <div class="col-md-2 text-right">
                <input type="hidden" name="action" value="edit_staff">
                <input type="hidden" name="user_id" value="<?php echo $staff->id ?>">
                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /Content area --> 
<script>
$(document).ready(function() {
<?php echo get_country()->phone_formate ?>
	$('#joining_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: true,
		max: [<?php echo date('Y') ?>,<?php echo date('m') ?>,<?php echo date('d') ?>],
		formatSubmit: 'yyyy-mm-dd'
    });
	
	$('#dob').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
         selectYears: 100,
        min: [<?php echo date('Y')-100 ?>,01,01],
        max: [<?php echo date('Y')-1 ?>,12,31],
		formatSubmit: 'yyyy-mm-dd'
    });
	
	$('#frmICK').submit(function(e){
		e.preventDefault();
		
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-staff';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						displayAjaxMsg(data.msg,data.code);
           if($('#status').val() == 'delete_soft'){
              window.location = '<?php echo SITEURL ?>staff/staffs_list';
            }
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
</script>
<?php include "../footer.php" ?>