<?php 
$mob_title = "Permission";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if($_SESSION['icksumm_uat_login_usertypecode'] != 'UT01' && $_SESSION['icksumm_uat_login_usertypecode'] != 'UT02' || !in_array("su_permissions_edit", $_SESSION['login_user_permissions'])){
    include "../includes/unauthorized_msg.php";
    return;
} 

$permissions = $db->get_row("SELECT * FROM ss_permissions WHERE id='".trim($db->escape($_GET['id']))."' ");
?>

<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Permission</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Edit Permission</li>
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
            <form id="frmEditPermission" class="form-validate-jquery" method="post">
                <div class="panel panel-flat panel-flat-box">
                    <div class="panel-body panel-body-box">
                    	<div class="ajaxMsg"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Permission Name:</label>
									  <input type="text" required name="permission" id="permission" class="form-control" placeholder="Enter role"  value="<?php echo $permissions->permission; ?>" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Permission:</label>
                           <input type="text" required name="permission_name" id="permission_name" class="form-control" placeholder="Enter Permission Name"  value="<?php echo $permissions->permission_name; ?>" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                        	<div class="col-md-10 text-right">
                        	<div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                                <input type="hidden" name="permission_id" value="<?php echo $permissions->id; ?>">
                            	<input type="hidden" name="action" value="permission_edit">
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
$( document ).ready(function() {
	$('#frmEditPermission').submit(function(e){
		e.preventDefault();
		if($('#frmEditPermission').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-permission';
			$('.spinner').removeClass('hide');
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						displayAjaxMsg(data.msg,data.code);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
	});
});
</script>
<?php include "../footer.php"?>
