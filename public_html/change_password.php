<?php $mob_title = "Change Password";?>
<?php include "header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01') && !check_userrole_by_group('admin')) {
    include "includes/unauthorized_msg.php";
    exit;
}
?>
      <!-- Page header -->
      <div class="page-header page-header-default">
        <div class="page-header-content">
          <div class="page-title">
            <h4>Change Password</h4>
          </div>
        </div>
        <div class="breadcrumb-line">
          <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Change Password</li>
          </ul>
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
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Old Password:<span class="mandatory">*</span></label>
                        <input placeholder="Old Password" required name="old_password" id="old_password" class="form-control" type="password">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>New Password:<span class="mandatory">*</span></label>
                        <input placeholder="New Password" required passwordCheck="true" name="new_password" id="new_password" class="form-control" type="password">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Confirm Password:<span class="mandatory">*</span></label>
                        <input placeholder="Confirm Password" required equalTo="#new_password" name="confirm_password" id="confirm_password" class="form-control" type="password">
                      </div>
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-9 text-right">
                        	<div class="ajaxMsgBot"></div>
                            </div>

                    <div class="col-md-3 text-right">
                      <input type="hidden" name="action" value="change_password">
                      <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
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
$( document ).ready(function() {
	$('#frmICK').submit(function(e){
        e.preventDefault();

		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-user';
			$('.spinner').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){
				if(status == 'success'){
					if(data.code == 1){
            $("#frmICK")[0].reset();
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
});

jQuery.extend(jQuery.validator.messages, {
    equalTo: "Password did not match.",
});

</script>
      <?php include "footer.php"?>