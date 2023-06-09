<?php
include_once "includes/config.php";

include "header_guest.php";  ?>
<!-- Content area -->
<div class="content login_content">

    <!-- Advanced login -->
    <form id="frmLogin" class="form-validate-jquery" method="post">
        <div class="panel panel-body login-form">
            <div class="text-center">
               
                <div class="row">
                    <div class="col-md-12"><img src="<?php echo SITEURL.LOGO ?>" class="login_win_logo" src="sadas"></div>
                    <div class="col-md-12"><div class="school_mgt_label"><?php echo SCHOOL_NAME ?></div></div>
                    <div class="col-md-12"><h2 style="font-size: 5rem;color: #000;font-weight: bold;">WE'RE DOWN FOR MAINTENANCE</h2></div>
                    <div class="col-md-12 text-center"><h5>This website is undergoing maintenance and will be back soon</h5></div>
                </div>
            </div>
            <div class="ajaxMsg"></div>            
            <div class=" text-center">Please email us your queries and suggestions to <a
                    href="mailto:ick-support@bayyan.org">ick-support@bayyan.org</a></div>
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

            grecaptcha.reset();
        }
    });
});
</script>
<?php include "footer_guest.php" ?>