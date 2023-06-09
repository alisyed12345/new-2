<?php
$mob_title = "Role";
include "../header.php";


//AUTHARISATION CHECK -
if (!in_array("su_role_create", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}

?>
<!-- Page header -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">


<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4 style="display:inline-block">Role</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Add Role</li>
        </ul>
    </div>
    <?php if (in_array("su_role_list", $_SESSION['login_user_permissions'])) {   ?>
        <div class="above-content"> <a href="<?php echo SITEURL ?>role/role_list" class="pull-right"><span class="label label-danger">List Role</span></a> </div>
    <?php  } ?>

</div>
<!-- /page header -->


<!-- Content area -->
<div class="content content-box">
    <div class="row">
        <div class="col-lg-12">
            <form id="frmAddRole" class="form-validate-jquery" method="post">
                <div class="panell panel-flat panel-flat-box">
                    <div class="panel-body panel-body-box">
                        <div class="ajaxMsg"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Role Name: <span class="text-danger">*</span></label>
                                    <input type="text" required name="role" id="role" class="form-control" placeholder="Enter role" maxlength="60">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Access: <span class="text-danger">*</span></label>
                                    <select class="form-control required" name="access" id="access" required="required" aria-required="true">
                                        <option value="">Select</option>
                                        <option value="1">Full Access</option>
                                        <option value="0">Limited Access</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                        <input type="checkbox"   id="checkAll">
                        <label ><strong>Checked ALL</strong></label>
                        <br>

                        <div class="row">
                            <?php
                            $permissions_group = $db->get_results("SELECT * FROM ss_permission_groups");

                            foreach ($permissions_group  as $key => $group) { ?>

                                <div class="col-md-3">
                                    <div class="form-group">

                                        <lable><strong><?php echo $group->permission_group ?></strong></lable>
                                        <?php

                                        if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') {
                                            $permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " ");
                                        } else {
                                            $permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " AND public_access = 1");
                                        }

                                        foreach ($permissions as $key => $row) { ?>
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" class="custom-control-input" name="permission[<?php echo $row->id; ?>] " >
                                                <label class="custom-control-label" for="customCheck"><?php echo $row->permission_name; ?></label>
                                            </div>
                                        <?php } ?>

                                    </div>
                                </div>

                            <?php } ?>
                        </div>

                        <div class="row">
                            <div class="col-md-10 text-right">
                                <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="action" value="role_add">
                                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



<script>


    $(document).ready(function() {

        $('#frmAddRole').submit(function(e) {
            e.preventDefault();

            if ($('#frmAddRole').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-role';
                $('.spinner').removeClass('hide');

                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                            $("#frmAddRole").trigger("reset");
                            $('.select').change();
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg(data.msg);
                    }
                }, 'json');
            }
        });

$("#checkAll").click(function(){
$('input:checkbox').not(this).prop('checked', this.checked);
});
});
</script>
<?php include "../footer.php" ?>