<?php 
$mob_title = "User Role & Permissions";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if($_SESSION['icksumm_uat_login_usertypecode'] != 'UT01' && $_SESSION['icksumm_uat_login_usertypecode'] != 'UT02' || !in_array("su_user_role_permissions_create", $_SESSION['login_user_permissions'])){
  include "../includes/unauthorized_msg.php";
  return;
} 
?>
<!-- Page header -->
<?php

  $user_id = $_GET['userid'];

  $user_name = $db->get_var("SELECT username FROM ss_user where id = '".$user_id."' ");

  $user_roleId = $db->get_var("SELECT role_id FROM ss_user_role_map where user_id = '".$user_id."' ");


if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){
 $roles = $db->get_results("SELECT id, role FROM ss_role where status = 1  order by role ASC");
}else{
   $roles = $db->get_results("SELECT id, role FROM ss_role where status = 1 AND public = 1  order by role ASC");
}

  ?>

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">User Role & Permissions Add</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL ?>staff/staffs_list"> List All Staff </a></li>
      <li class="active">User Role & Permissions Add</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content content-box">
    <div class="row">
        <div class="col-lg-12">         
            <form id="frmAddUser" class="form-validate-jquery" method="post">
                <div class="panel panel-flat panel-flat-box">
                    <div class="panel-body panel-body-box">
                      <div class="ajaxMsg"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">User Name:</label>
                                    <span><?php echo $user_name; ?></span>
                                </div>
                            </div>
                          </div>
                          
                          <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Role:</label>
                                  <select class="roleid form-control required" name="role_id">
                                      <option value="">--Select Option--</option>
                                      <?php foreach ($roles as $row) { ?>
                                      <option value="<?php echo $row->id;  ?>"  <?php if(isset($user_roleId)){  if($row->id == $user_roleId){ echo "selected"; }     } ?>  ><?php echo $row->role;  ?></option>
                                      <?php } ?>
                                </select>
                                </div>
                            </div>
                        </div>

                        <div class="user_permission">
                           <h4 class="text-center">Loading...</h4>
                        </div>

                        <div class="row">
                          <div class="col-md-10 text-right">
                          <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                              <input type="hidden" name="user_id" value="<?php echo $_GET['userid'] ?>">
                              <input type="hidden" name="action" value="user_role_and_permission_add">
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
    
    var user_id = "<?php echo $_GET['userid']?>";
    var roleid = $('.roleid').val();
    get_permission(user_id, roleid);

   
   $(document).on('change','.roleid',function() {
    var user_id = "<?php echo $_GET['userid']?>";
    var roleid = $('.roleid').val();
       get_permission(user_id, roleid);
   });


  
  
  $('#frmAddUser').submit(function(e){
    e.preventDefault();
    
    if($('#frmAddUser').valid()){
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-role';
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

function get_permission(user_id, roleid){

      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-role';
      var formDate = $(this).serialize();
      $.post(targetUrl,{'user_id':user_id, 'roleid':roleid,'action':'role_get_permission'},function(data,status){
              $('.user_permission').html(data);
        });
  }

</script>
<?php include "../footer.php"?>
