<?php $mob_title = "Feedback"; ?>
<?php include "../header.php";
 include_once "includes/config.php";
?>
<!-- Page header -->
<?php 

    if(!empty($_SESSION['icksumm_uat_login_usertypecode'])){
        return;
    }else{
       return;
    }
?>
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Feedback</h4>
    </div> 
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Feedback</li>
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
              <label>Name</label>
              <input type="text" class="form-control required" lettersonly="true" placeholder="Enter full name" name="full_name" id="full_name" />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Contact Number</label>
              <input type="text" class="form-control" phonenocheck="true" name="contact_no" placeholder="Enter contact number" id="contct_no" />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Email</label>
              <input type="email" class="form-control required" name="email" placeholder="Enter your email" id="email" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Feedback</label>
              <textarea class="form-control required" id="message" placeholder="Enter your Feedback" name="message"></textarea>
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
              <input type="hidden" name="action" value="save_feedback">
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
    $('#contct_no').mask('000-000-0000');

    //VALIDATION - US PHONE FORMAT 
jQuery.validator.addMethod("phonenocheck", function(value, element) {
       return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);  
}, "Enter valid phone number");


	$('#frmICK').submit(function(e){
        e.preventDefault();
		
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-feedback';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){			
						$('#full_name').val('');
						$('#email').val('');
						$('#contct_no').val('');
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
});
</script>
<?php include "../footer.php" ?>
