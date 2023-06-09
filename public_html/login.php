<?php 
include_once "includes/config.php";


if(!empty($_SESSION['icksumm_uat_login_userid'])){

header("location:".SITEURL."dashboard.php");
}
include "header_guest.php"; 
 ?>
 <style>
 @media only screen and (min-width: 768px) {
    .accordingwidth{
        display: block;
    }
    .accordinghide{
        display: none;
    }
    .widthmanage{
        margin-top: 15px;
    }
 }
 @media only screen and (max-width: 768px) {
    .accordingwidth{
        display: none;
    }
    .accordinghide{
        display: block;
    }
 }

  
 
 </style>
<!-- Content area -->
<div class="content login_content">

    <!-- Advanced login -->
    <form id="frmLogin" class="form-validate-jquery" method="post">
        <div class="panel panel-body login-form">
            <div class="text-center">
               
                <div class="row">
                    <div class="col-md-12"><img src="<?php echo SITEURL.LOGO ?>" class="login_win_logo logosize" src="sadas" ></div>
                    <div class="col-md-12"><div class="school_mgt_label"><?php echo SCHOOL_NAME ?> <span class="accordinghide">(<?php echo $viewsession ?>)</span></div></div>
                    <div class="col-md-12"><h5>Enter Username & Password</h5></div>
                </div>
            </div>
            <div class="ajaxMsg"></div>
            <div class="form-group has-feedback has-feedback-left mar-top-10">
                <input type="text" class="form-control loginfield email" name="username" required id="username" value="<?php if (isset($_COOKIE["member_username"])) {
                                                                                                            echo $_COOKIE["member_username"];
                                                                                                          } ?>"
                    placeholder="Email">
            </div>
            <div class="form-group has-feedback has-feedback-left">
                <input type="password" class="form-control loginfield" name="password" required id="password" value="<?php if (isset($_COOKIE["member_password"])) {
                                                                                                                echo $_COOKIE["member_password"];
                                                                                                              } ?>"
                    placeholder="Password">
            </div>
            <div class="row">
                 <div class="col-sm-6">
                    <?php if(!isset($_COOKIE['cook_staff_login'])) { ?>
                    <div class="form-group has-feedback has-feedback-left">
                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITEKEY ?>"></div>
                    </div>
                    <?php } ?>
                 </div>
            </div>
            <div class="row accordingwidth">
                <div class="col-sm-6">
                        <label class="checkbox-inline" style="float:left;">
                            <input type="checkbox" class="styled" name="remember" id="remember"
                                <?php if (isset($_COOKIE["member_username"])) { ?> checked <?php } ?>>
                            Remember </label>
                 </div> 
                 <div class="col-sm-6">
                        <a href="forgot_password" style="float:right;">Forgot password?</a>  
                 </div>    
            </div>
            <div class="form-group login-options accordinghide">
                <div class="row">
                    <div class="col-sm-6">
                        <label class="checkbox-inline" style="float: left;">
                            <input type="checkbox" class="styled" name="remember" id="remember"
                                <?php if (isset($_COOKIE["member_username"])) { ?> checked <?php } ?>>
                            Remember </label>
                    </div>
                    <div class="col-sm-6"> 
                        <a href="forgot_password" style="float: right;margin-top:-20px;">Forgot password?</a> 
                    </div>
                </div>
            </div>
            <div class="row widthmanage">
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="hidden" name="action" id="action" value="login" />
                        <button type="submit" class="btn btn-block btn-success"
                            style="background-color: #37474f !important;border-color: #37474f !important;"><i
                                class="icon-spinner2 spinner hide marR10 insidebtn"></i> Login</button>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="hidden" name="action" id="action" value="login" />
                        <button type="button" class="btn btn-block btn-success"
                            style="background-color: #37474f !important;border-color: #37474f !important;"><a href="<?php echo SITEURL ?>student_registration_request" style="color: white;">Register New Child</a></button>
                    </div>
                </div>
            </div>
            <span>Please email us for your queries and suggestions to <a
                    href="mailto:<?php echo SUPPORT_EMAIL ?>"><?php echo SUPPORT_EMAIL ?></a></span>
        </div>

    </form>
    <!-- /advanced login -->

</div>
<!-- /content area -->
<script>
$(document).ready(function() {
    $('#frmLogin').submit(function(e) {
        e.preventDefault();

        if ($('#frmLogin').valid()) {
            $('.spinner').removeClass('hide');

            var formDate = $(this).serialize();
            var targetUrl = "<?php echo SITEURL ?>ajax/ajss-authenticate";

            <?php if(isset($_GET['redirected_url'])){ ?>
            targetUrl += '?redirected_url=<?php echo $_GET['redirected_url'] ?>';
            <?php } ?>

            $.post(targetUrl, formDate, function(data, status) {
                $('.spinner').addClass('hide');

                if (status == 'success') {
                    if (data.code == 1) {
                        displayAjaxMsg(data.msg, data.code);
                        window.location = data.target_url;
                    } else {
                        $('.ajaxMsg').html(data.msg).addClass('alert alert-danger');
                    }
                } else {
                    $('.ajaxMsg').html('Login failed').addClass('alert alert-danger');
                }
            }, 'json');

            // grecaptcha.reset();
        }
    });
});
</script>
<?php include "footer.php" ?>