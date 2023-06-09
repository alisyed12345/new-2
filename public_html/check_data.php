<?php
include_once "includes/config.php";
include "header_guest.php"; 
?>
<html>
  <body>   
    <div class="container">
        <div class="row" style="margin-top: 50px;">
            <div class="col-md-12">
                <div class="error-template">
                    <h1 style="font-size: 40px;"><strong>Welcome!</strong></h1>
                    <h2 style="font-size: 25px;"><strong>Mandatory Information</strong></h2>
                    <br>
                    <div class="error-details" style="font-size: 22px;">
                    Please fill out the following mandatory information before proceeding further.
                    </div>
                    <br>
                    <ul style="font-size: 18px;">
                      
                      <li style="margin-top:10px;">School sessions <?php if(empty($current_session->id)){?>(<a href="<?php echo SITEURL ?>settings/school_session">click here</a>) to Add Session <?php } else { echo "<span class='glyphicon glyphicon-check text-success'></span>";} ?>

                      </li> 
                      <li style="margin-top:10px;">Registration Setting <?php if(empty($get_info->new_registration_session)){?>(<a href="<?php echo SITEURL ?>settings/registration_settings">click here</a>) to Add Registration Setting <?php }  else { echo "<span class='glyphicon glyphicon-check text-success'></span>";} ?></li>
                      <?php if($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){  ?>
                      <li style="margin-top:10px;">General Setting <?php if(empty($get_info->school_name) && empty($get_info->center_short_name)){?>(<a href="<?php echo SITEURL ?>settings/general_settings">click here</a>) to Add General Setting <?php }  else { echo "<span class='glyphicon glyphicon-check text-success'></span>";} ?></li>
                      <li style="margin-top:10px;">Software Version  <?php if($version->id == 0){?>(<a href="<?php echo SITEURL ?>software_version/version-create">click here</a>) to Add Software Version<?php }  else { echo "<span class='glyphicon glyphicon-check text-success'></span>";} ?></li>
                      <?php } else {  
                       if((empty($get_info->school_name) && empty($get_info->center_short_name) && empty($get_info->new_registration_session)) ) { ?>
                         <li style="margin-top:10px;"><strong> Please contact BAYYAN (For administrative) <a href="mailto:<?php echo SUPPORT_EMAIL ?>"><?php echo SUPPORT_EMAIL ?></a></strong></li>
                      <?php } ?>
                        <div class="error-actions" style="margin-top: 20px; margin-left:-10px;">
                          <a href="<?php echo SITEURL ?>dashboard" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-log-in"></span> Go To Home</a>
                        </div>
                      <?php } ?>  
                    </ul>
                    <br>
                    <?php  if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->id)) {
                  ?> <div class="error-actions" style="margin-left:10px;">
                        <a href="<?php echo SITEURL ?>dashboard" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-log-in"></span> Go To Home</a>
                    </div>
                    <?php }else{ ?>
                   <!--    <div class="error-actions">
                          <a href="javascript:void(0);" class="btn btn-primary btn-lg" disabled><span class="glyphicon glyphicon-log-in"></span> Go To Login</a>
                      </div> -->
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
  </body>
</html>
<?php include "footer.php" ?>