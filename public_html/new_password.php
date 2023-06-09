<?php $mob_title = "Create New Password"; ?>
<?php include "header_guest.php";  ?>
<!-- Content area --> 
<div class="content login_content"> 
  
  <!-- Advanced login -->
  <form id="frmNewPassword" class="form-validate-jquery" method="post">
    <div class="panel panel-body login-form">
      <div class="text-center">
         <h1 style="margin-bottom: 0px;"><?php echo SCHOOL_NAME ?> </h1>
         <h1 class="content-group-lg" style="margin-top: 0px;">Create New Password <small class="display-block">Password must have minimum 6 characters</small></h1>
      </div>
      <div class="ajaxMsg"></div>
      
      <div class="form-group has-feedback has-feedback-left">
      <span>Password <span class="mandatory">*</span></span>
        <input  name="password" passwordCheck="true"  id="password" class="form-control loginfield required" placeholder="Password"  autocomplete="off">
      </div>
      <div class="form-group has-feedback has-feedback-left">
      <span>Confirm Password <span class="mandatory">*</span></span>
        <input equalTo="#password" name="confirm_password" id="confirm_password" class="form-control loginfield required" type="password" autocomplete="off" placeholder="Confirm Password">    
      </div>
      <div class="form-group">
        <input type="hidden" name="action" value="new_password" />
        <input type="hidden" name="key" value="<?php echo $_GET['id'] ?>" />
        <input type="hidden" name="fr" value="<?php echo $_GET['fr'] ?>" />
        <button type="submit" id="btnSubmit" class="btn btn-block btn-success" style="background-color: #37474f !important;border-color: #37474f !important;"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
      </div>
      <div class="form-group login-options">
        <div class="row">
          <div class="col-sm-12 text-right"> <a href="login.php">Go to Login page</a> </div>
        </div>
      </div>
    </div>
  </form>
  <!-- /advanced login --> 
  
</div>
<!-- /content area --> 
<script>
		$(document).ready(function() {
      // $('#password').val('');
			// $('#confirm_password').val('');

        	$('#frmNewPassword').submit(function(e){
				e.preventDefault();

				if($('#frmNewPassword').valid()){
					$('.spinner').removeClass('hide');
				
					var formDate = $(this).serialize();
					$.post('ajax/ajss-authenticate',formDate,function(data,status){
						$('.spinner').addClass('hide');
						
						if(status == 'success'){
							if(data.code == 1){
								$('#password').val('');
								$('#confirm_password').val('');
                $('#btnSubmit').attr('disabled','disabled');
                window.location = '<?php echo SITEURL.'login' ?>';
								displayAjaxMsg(data.msg,data.code);
							}else{
								displayAjaxMsg(data.msg,data.code);	
							}
						}else{
							displayAjaxMsg('Password change failed','0');
						}
					},'json');
				}
			});
    	});
	  </script>
<?php include "footer.php" ?>
