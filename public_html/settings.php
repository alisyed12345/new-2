<?php $mob_title = "Change Password"; ?>
<?php include "header.php";
//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT01') && !check_userrole_by_group('admin')) {
    include "includes/unauthorized_msg.php";
    exit;
}
$sessions = $db->get_results("select session from ss_school_sessions");
$env = $db->get_var("SELECT `value` FROM ss_config WHERE `key`='ENVIRONMENT'");

?>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Settings</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Settings</li>
        </ul>
    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-flat">

                
                <div class="panel-body">
                   <div class="ajaxMsg"></div>

                    <div class="row">
                        <div class="col-md-3">
                        <label>Customer & Payment Token Genrate </label>
                        </div>
                        <div class="col-md-2">
                                <button type="submit" class="btn btn-success tokengenrate"><i class="icon-spinner2 spinnertt hide marR10" id="get_spinner"></i> Click </button>
                        </div>
                    </div>

                    <br>
                    <form id="frmICK" class="form-validate-jquery" method="post">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Session:</label>
                                    <select name="session" id="session" class="form-control required">
                          <option value=""> --Select Option-- </option>
                          <?php foreach ($sessions as $rows) { ?>
                           <option value="<?php echo $rows->session ?>"  <?php echo ($rows->session == $_SESSION['icksumm_uat_CURRENT_SESSION']) ? 'selected' : '' ?> > <?php echo $rows->session ?> </option>
                          <?php } ?>
                         
                        </select>  
                                </div>
                            </div>

                            <div class="col-md-2">
                                <input type="hidden" name="action" value="change_session">
                                <button type="submit" class="btn btn-success" style="margin-top: 26px;"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                            </div>
                        </div>
                 </form>

                <form id="frmICKenv" class="form-validate-jquery" method="post">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label> Enviroment:</label>
                                <select name="env" id="env" class="form-control required">
                                        <option value=""> --Select Option-- </option>
                                        <option value="dev" <?php echo ($env == 'dev' || $env == 'development') ? 'selected' : '' ?>> Development
                                        </option>
                                        <option value="production"
                                            <?php echo ($env == 'qa' || $env == 'prod' || $env == 'production') ? 'selected' : '' ?>> Production </option>
                                    </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <input type="hidden" name="action" value="change_env">
                            <button type="submit" class="btn btn-success" style="margin-top: 26px;"><i class="icon-spinner2 spinneree hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                        </div>
                    </div>
                </form>

               </div>


        </div>
    </div>
</div>
</div>
<!-- /Content area -->
<script>
    $(document).ready(function() {
        $('#frmICK').submit(function(e) {
            e.preventDefault();

            

            if ($('#frmICK').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-user';
                $('.spinner').removeClass('hide');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                            var session = $('#session').val();
                            $('.school_mgt_label').html('<?php echo SCHOOL_NAME ?> Management System (' + session + ')');
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg('Error: Process failed');
                    }

                    $('.spinner').addClass('hide');
                }, 'json');
            }
        });



        $('#frmICKenv').submit(function(e) {
            e.preventDefault();

            if ($('#frmICKenv').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-user';
                $('.spinneree').removeClass('hide');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                      $('.spinneree').addClass('hide');
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg('Error: Process failed');
                    }

                   
                }, 'json');
            }
        });

        

        $('.tokengenrate').on('click', function() {

                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-custom';
                $('.spinnertt').removeClass('hide');
                $.post(targetUrl, {}, function(data, status) {
                    if (status == 'success') {
                      $('.spinnertt').addClass('hide');
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg('Error: Process failed');
                    }

                   
                }, 'json');
            
        });


        
    });
</script>
<?php include "footer.php" ?>