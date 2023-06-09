<?php include "header_guest.php" ?>
<!-- Content area -->
<div class="content login_content">

<!-- Advanced login -->
<form action="#">
    <div class="panel panel-body login-form">
        <div class="text-center">
            <div class="icon-object border-warning-400 text-warning-400"><i class="icon-reading"></i></div>
            <h5 class="content-group-lg">Regenerate Password <small class="display-block">Enter your credentials</small></h5>
        </div>

        <div class="form-group has-feedback has-feedback-left">
            <input type="text" class="form-control" placeholder="Username">
            <div class="form-control-feedback">
                <i class="icon-user text-muted"></i>
            </div>
        </div>

        <div class="form-group has-feedback has-feedback-left">
            <input type="text" class="form-control" placeholder="Email">
            <div class="form-control-feedback">
                <i class="icon-earth text-muted"></i>
            </div>
        </div>

        <div class="form-group">
            <a href="dashboard.php" class="btn btn-block btn-success">Submit</a>
        </div>
        <div class="form-group login-options">
            <div class="row">
                <div class="col-sm-12">
                    <a href="login.php">Go to Login page</a>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- /advanced login -->

</div>
<!-- /content area -->
<?php include "footer.php" ?>