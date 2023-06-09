<?php
$mob_title = "Role";
include "../header.php";

//AUTHARISATION CHECK -
if (!in_array("su_role_edit", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}


$role_id = base64_decode($_GET['id']);

$role = $db->get_row("SELECT * FROM ss_role WHERE id='" . trim($db->escape($role_id)) . "' AND status = 1 ");

if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') {
    $permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 ");
} else {

    $permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND public_access = 1");
}


$roleWaisePermission = $db->get_results("SELECT * FROM ss_role_wise_permissions WHERE role_id = '" . $role_id . "' ");

if (is_array($roleWaisePermission)) {
    $newarray = [];
    foreach ($roleWaisePermission as $val) {
        $newarray[] = $val->permission_id;
    }
}
?>

<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4 style="display:inline-block">Role</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li><a href="<?php echo SITEURL . "role/role_list" ?>">List All Role</a></li>
            <li class="active">Edit Role</li>
        </ul>
    </div>
    <div class="above-content">
        <a href="javascript:history.go(-1)" class="last_page">Go Back To Last Page</a>
    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
    <div class="row">
        <div class="col-lg-12">
            <form id="frmEditRole" class="form-validate-jquery" method="post">
                <div class="panel panel-flat panel-flat-box">
                    <div class="panel-body panel-body-box">
                        <div class="ajaxMsg"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Role Name :<span class="text-danger">*</span></label>
                                    <input type="text" required name="role" id="role" class="form-control" placeholder="Enter role" value="<?php echo $role->role; ?>" maxlength="60">
                                </div>
                            </div>

                            <?php
                            $roleForAccess = $db->get_var("SELECT user_type_code FROM `ss_role` INNER JOIN `ss_usertype` ON `ss_usertype`.`role_id`=`ss_role`.`id` WHERE `ss_role`.`id`='" . trim($db->escape($role_id)) . "' AND status = 1 AND `is_default`='0'");
                            if (!empty($roleForAccess)) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="group">Access: <span class="text-danger">*</span></label>
                                        <select class="form-control required" name="access" id="access" required="required" aria-required="true">
                                            <option value="">Select</option>
                                            <option <?php echo ($roleForAccess == 'UT01') ? 'selected' : '' ?> value="1">Full Access</option>
                                            <option <?php echo ($roleForAccess == 'UT02') ? 'selected' : '' ?> value="0">Limited Access</option>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>


                        <div class="row">
                            <?php
                            $permissions_group = $db->get_results("SELECT * FROM ss_permission_groups ");

                            foreach ($permissions_group  as $key => $group) {
                                $total_permissions = count((array)$db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " "));
                                $total_access_permissions = count((array)$db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " AND public_access = 0 "));
                            ?>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?php if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') { ?>
                                            <lable><strong><?php echo $group->permission_group ?></strong></lable>
                                            <?php } else {
                                            if ($total_permissions !== $total_access_permissions) { ?>
                                                <lable><strong><?php echo $group->permission_group ?></strong></lable>

                                        <?php }
                                        } ?>
                                        <?php

                                        if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') {

                                            $permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " ");
                                        } else {
                                            $permissions = $db->get_results("SELECT * FROM ss_permissions WHERE status =1 AND permission_group_id=" . $group->id . " AND public_access = 1");
                                        }


                                        foreach ($permissions as $key => $row) { ?>
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" class="custom-control-input" name="permission[<?php echo $row->id; ?>]" <?php if (is_array($newarray)) {
                                                                                                                                                    if (in_array($row->id, $newarray)) {
                                                                                                                                                        echo "checked";
                                                                                                                                                    }
                                                                                                                                                } ?>>
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
                                <input type="hidden" name="role_id" value="<?php echo $role->id; ?>">
                                <input type="hidden" name="action" value="role_edit">
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
        $('#frmEditRole').submit(function(e) {
            e.preventDefault();
            if ($('#frmEditRole').valid()) {
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-role';
                $('.spinner').removeClass('hide');
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        if (data.code == 1) {
                            displayAjaxMsg(data.msg, data.code);
                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    } else {
                        displayAjaxMsg(data.msg);
                    }
                }, 'json');
            }
        });
    });
</script>
<?php include "../footer.php" ?>