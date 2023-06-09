<?php
include_once "includes/config.php";
//AUTHARISATION CHECK - UT05 MEANS PARENTS
if (check_userrole_by_code('UT05')) {
  header('location:' . SITEURL . "parents/dashboard.php");
  exit;
}
if (!empty($current_session->id) && !empty($get_info->school_name) || !empty($get_info->center_short_name) && !empty($get_info->new_registration_session) && !empty($version->major)) {

  $mob_title = "Dashboard";
  include "header.php"; ?>

  <!-- Page header -->
  <script type="text/javascript" src="assets/js/plugins/visualization/d3/d3.min.js"></script>
  <script type="text/javascript" src="assets/js/plugins/visualization/d3/d3_tooltip.js"></script>
  <script type="text/javascript" src="assets/js/charts/d3/bars/bars_advanced_sortable_vertical.js"></script>

  <div class="page-header page-header-default">
    <div class="page-header-content">
      <div class="page-title">
        <h4 class="web_item">Welcome to <?php echo SCHOOL_NAME ?></h4>
      </div>
    </div>
    <div class="breadcrumb-line">
      <ul class="breadcrumb">
        <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      </ul>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    <?php
    //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
    if (check_userrole_by_code('UT01')) {
      include "dashboard_admin_partial.php";
    } elseif (check_userrole_by_code('UT02')) {
      include "dashboard_teacher_partial.php";
    } else {
      include "includes/session_unauthorized.php";
    }
    ?>

  </div>
<?php } else {
  header('location:' . SITEURL . 'check_data.php');
} ?>
<script>
  $(document).ready(function() {
    //SHOW PICKUP-DROPOFF MAP
    $(document).on('click', '#pickup_dropof_map_link', function() {
      $('#modal_mail_detail').modal('show');
    });
  });
</script>
<!-- /Content area -->
<?php include "footer.php" ?>