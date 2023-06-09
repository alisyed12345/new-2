<?php $mob_title = "Password Recovery"; ?>
<?php include "header_guest.php";  ?>
<!-- Content area -->

<div class="content login_content">

  <!-- Advanced login -->
  <form id="frmPassword" class="form-validate-jquery" method="post">
    <div class="panel panel-body login-form">
      <div class="text-center">
        <div class="col-md-12"><img src="<?php echo SITEURL.LOGO ?>" class="login_win_logo logosize" src="sadas" ></div>
        <!-- <div class="icon-object border-warning-400 text-warning-400"><i class="icon-reading"></i></div> -->
        <h1 class="school_mgt_label"><?php echo SCHOOL_NAME ?></h1>
        <h1 class="content-group-lg">Password Recovery<small class="display-block">Enter your credentials</small></h1>
      </div>
      <div class="ajaxMsg"></div>
      <div class="form-group has-feedback has-feedback-left"> 
        <input type="text" class="form-control loginfield" name="username_email" required id="username_email" placeholder="Username / Email">
        <!-- <div class="form-control-feedback"> <i class="icon-user text-muted"></i> </div> -->
      </div>
      <div class="form-group has-feedback has-feedback-left">
        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITEKEY ?>"></div>
      </div>
      <div class="form-group">
        <input type="hidden" name="action" value="password_recovery" />
        <button type="submit" class="btn btn-block btn-success" style="background-color: #37474f !important;border-color: #37474f !important;"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
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
    $('#frmPassword').submit(function(e) {
      e.preventDefault();

      if ($('#frmPassword').valid()) {
        $('.spinner').removeClass('hide');

        var formDate = $(this).serialize();
        $.post('ajax/ajss-authenticate', formDate, function(data, status) {
          $('.spinner').addClass('hide');

          if (status == 'success') {
            if (data.code == 1) {
              var validator = $("#frmPassword").validate();
              validator.resetForm();
              $("#frmPassword")[0].reset();
              displayAjaxMsg(data.msg, data.code);
            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          } else {
            displayAjaxMsg('Login failed', 0);
          }
        }, 'json');
      }
      // reset the google reCAPTCHA
      grecaptcha.reset();
    });
  });
</script>
<?php include "footer.php" ?>