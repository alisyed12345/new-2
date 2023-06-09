<?php
$mob_title = "Software Version Edit";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_staff_create", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}


$version = $db->get_row("SELECT * FROM ss_software_version where id = " . $_GET['id'] . " ");

?>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Software Version</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
             <li><a href="<?php echo SITEURL ?>software_version/version-list">List Software Versions</a></li>
            <li class="active">Edit Software Version </li>
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Major:<span class="mandatory">*</span></label>
                                    <select class="form-control required" data-width="100%" id="major" name="major">
                                        <option value="">Select</option>
                                        <?php for ($i = 0; $i <= 20; $i++) { ?>
                                            <option value="<?php echo $i ?>" <?php echo (isset($version) && $version->major == $i) ? 'selected' : ''; ?>><?php echo $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Minor:<span class="mandatory">*</span></label>
                                    <select class="form-control required" data-width="100%" id="minor" name="minor">
                                        <option value="">Select</option>
                                        <?php for ($i = 0; $i <= 20; $i++) { ?>
                                            <option value="<?php echo $i ?>" <?php echo (isset($version) && $version->minor == $i) ? 'selected' : ''; ?>><?php echo $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notification:<span class="mandatory">*</span></label>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-check-inline">
                                                <label class="form-check-label" for="radio1">
                                                    <input type="radio" class="form-check-input required" id="radio1" name="notification" value="1" <?php echo (isset($version) && $version->notification == 1) ? 'checked' : ''; ?> required> Yes
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check-inline">
                                                <label class="form-check-label" for="radio2">
                                                    <input type="radio" class="form-check-input" id="radio2" name="notification" value="0" <?php echo (isset($version) && $version->notification == 0) ? 'checked' : ''; ?> required> No
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <label id="notification-error" class="validation-error-label" for="notification" style="display: none;">Required field</label>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Patch:<span class="mandatory">*</span></label>
                                    <input type="text" class="form-control required" spacenotallow="true" id="patch" name="patch" maxlength="8" placeholder="Enter Patch" value="<?php echo $version->patch; ?> ">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status:<span class="mandatory">*</span></label>
                                    <select class="form-control required" data-width="100%" id="status" name="status">
                                        <option value="">Select</option>
                                        <option value="1" <?php echo (isset($version) && $version->status == 1) ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo (isset($version) && $version->status == 0) ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description:<span class="mandatory">*</span></label><label class="error" id="statusMsgcomm"></label>
                                    <textarea class="form-control" id="describtion" name="describtion" placeholder="Enter Describtion" style="height:200px"><?php echo $version->describtion; ?></textarea>
                                    
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="id" value="<?php echo $version->id; ?>">
                                <input type="hidden" name="action" value="edit_version">
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
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace('describtion', {
            height: 300,
            filebrowserUploadUrl: "../ajax/ckeditor_upload"
        });
        $('#frmICK').submit(function(e) {
            e.preventDefault();
            var getcode = CKEDITOR.instances.describtion.getData();
            var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
            ckvalue = ckvalue.replace(/&nbsp;/g, '');

            CKEDITOR.instances.describtion.updateElement();


            if ($('#frmICK').valid() && ckvalue.length > 0) {
                $('#statusMsgcomm').html('');
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-software-version';
                $('.spinner').removeClass('hide');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                            CKEDITOR.instances.describtion.setData('');
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg(data.msg);
                    }
                }, 'json');
            }else{
                    if (ckvalue.length === 0) {
                        $('#statusMsgcomm').html('Required Field');
                        return false;
                    } else {
                        $('#statusMsgcomm').html('');
                    }
            }
        });
    });
</script>
<?php include "../footer.php" ?>