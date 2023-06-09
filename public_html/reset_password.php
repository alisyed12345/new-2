<?php $mob_title = "Password Recovery"; ?>
<?php include "header_guest.php";  ?>
<style>
    .footerforloginp {
        position: fixed;
        width: 100%;
        bottom: 0;
    }
</style>
<!-- Content area -->

<div class="content login_content">

    <!-- Advanced login -->
    <form id="frmPassword" class="form-validate-jquery" method="post">
        <div class="panel panel-body login-form">
            <div class="text-center">
                <!-- <div class="icon-object border-warning-400 text-warning-400"><i class="icon-reading"></i></div> -->
                <!-- <h1 class="content-group-lg">Reset Password</h1> -->
                <h1 class="content-group-lg">Create New Password <small class="display-block">Password must have minimum 6 characters</small></h1>
            </div>
            <div class="ajaxMsg"></div>

            <div class="form-group has-feedback has-feedback-left">
                <input type="password" class="form-control loginfield" passwordCheck="true" name="password" required id="password" placeholder="New Password">
            </div>
            <div class="form-group has-feedback has-feedback-left">
                <input type="password" class="form-control loginfield" name="confirm_password" required equalTo="#password" id="confirm_password" placeholder="Confirm Password">
            </div>
            <div class="form-group has-feedback has-feedback-left">
                <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITEKEY ?>"></div>
            </div>
            <div class="form-group">
                <input type="hidden" name="action" id="action" value="reset_password" />
                <input type="hidden" name="key" value="<?php echo $_GET["key"] ?>" />
                <button type="submit" class="btn btn-block btn-success" style="background-color: #37474f !important;border-color: #37474f !important;"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
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
                            //displayAjaxMsg(data.msg, data.code);
                            Swal.fire({
                                title: data.msg,
                                html: 'You will be redirectec to login page soon',
                                timer: 10000,
                                timerProgressBar: true,
                                onBeforeOpen: () => {
                                    Swal.showLoading();
                                    setInterval(function() {
                                        window.location = data.target_url;
                                    }, 5000)
                                }
                            });
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg('Login failed', 0);
                    }
                }, 'json');

                grecaptcha.reset();
            }
        });
    });
</script>
<?php include "footer.php" ?>