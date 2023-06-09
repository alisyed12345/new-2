<?php
include_once "includes/config.php";

if (!isset($_SESSION['icksumm_uat_login_userid']) || trim($_SESSION['icksumm_uat_login_userid']) == '') {
    header('location:' . SITEURL . 'login.php');
}
$genral_info = $db->get_row("select * from ss_client_settings where status = 1");
$check_page = $db->get_results("select * from ss_client_settings");

// if ($_SESSION['scmp_CURRENT_SESSION_TEXT']) {
//     $session = $db->get_var("select session from ss_school_sessions where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT']."' ");
// }else{
//     $session = $db->get_var("select session from ss_school_sessions where current = 1 ");
// }

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php  if($current_session->current == 1){
           $current = $current_session->session;
        }else{
           $current  = $current_session->session."Session not added";
        } ?>
    <title class="session_titile"><?php echo SCHOOL_NAME ?> (<?php echo $current ?>)</title>

    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
    <link href="<?php echo SITEURL ?>assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/core.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/components.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/colors.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/dataTables.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="<?php echo SITEURL ?>assets/css/default.css" rel="stylesheet" />
    <link href="<?php echo SITEURL ?>assets/css/mystyle.css?v2" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/mystyle_themeone.css?v2" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <!-- /global stylesheets -->
    <!-- Core JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/loaders/pace.min.js"></script>
    <!--<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>-->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/libraries/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/libraries/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/libraries/jquery_ui/interactions.min.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/loaders/blockui.min.js"></script>
    <!-- /core JS files -->
    <!-- Theme JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/validation/validate.min.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/bootstrap_multiselect.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/bootstrap_select.min.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_bootstrap_select.js"></script>
    <!--<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/inputs/touchspin.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/select2.min.js"></script>-->
    <!--<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/styling/switch.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/styling/switchery.min.js"></script>-->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/styling/uniform.min.js"></script>
    <!--<script type="text/javascript" src="assets/js/core/app.js"></script>
    <script type="text/javascript" src="assets/js/pages/login_validation.js"></script>-->
    <!-- /theme JS files -->

    <!-- Theme JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/tables/datatables/datatables.min.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/tables/datatables/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/select2.full.min.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_multiselect.js"></script>
    <!-- /theme JS files -->

    <!-- Theme JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/ui/moment/moment.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/daterangepicker.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/anytime.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/pickadate/picker.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/pickadate/picker.date.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/pickadate/picker.time.js">
    </script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/picker_date.js"></script>
    <!-- /theme JS files -->

    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/jquery.mask.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>ckeditor/ckeditor.js"></script>
    <!-- <script src="http://cdn.ckeditor.com/4.6.2/standard-all/ckeditor.js"></script> -->

    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/app.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_validation.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_select2.js"></script>
   
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/myscript.js"></script>
    <?php echo (!empty(get_country()->validator))?get_country()->validator:'' ?>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/creditcard.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/creditCardValidator.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    

    <?php if (strlen(strstr($_SERVER['HTTP_USER_AGENT'], "us.quasarsolutions.sdsdsdsdsdsd")) > 0) { ?>
        <style>
            a.navbar-brand {
                display: none;
            }

            .page-header-content {
                display: none;
            }

            .mob_title {
                display: block;
            }
        </style>
    <?php } else { ?>
        <style>
            .sidebar-content .sidebar-user {
                display: none;
            }
        </style>
    <?php } ?>
</head>

<body>
    <!-- Main navbar -->
    <div class="navbar navbar-inverse">

        <div class="navbar-header">
            <ul class="nav navbar-nav navbar-left">
                <li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="icon-paragraph-justify3"></i></a>
                </li>
            </ul>
            <?php if(!empty($genral_info->school_header_logo)){
                if (!empty($current_session->id) && !empty($get_info->school_name) || !empty($get_info->center_short_name) && !empty($get_info->new_registration_session) && !empty($version->major)) {
                ?>

            <a class="navbar-brand" href="<?php echo SITEURL ?>dashboard"><img src="<?php echo SITEURL . $genral_info->school_header_logo ?>"></a>
            <div class="mob_title"><?php echo $mob_title ?></div>
            <?php } else { ?>
             <a class="navbar-brand" href="<?php echo SITEURL ?>check_data"><img src="<?php echo SITEURL . $genral_info->school_header_logo ?>"></a>
            <div class="mob_title"><?php echo $mob_title ?></div>
            <?php } } ?>

            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
                <li><a class="sidebar-mobile-main-toggle"><i class="icon-paragraph-justify3"></i></a></li>
            </ul>
        </div>

        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php if(!empty(SCHOOL_NAME)){ ?>
            <div class="school_mgt_label hidden-xs"><span class="header_lable_text"> <?php echo SCHOOL_NAME ?>
                    (<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT']; ?>)</span> <span style="font-size:20px; margin-left:10px;">v<?php echo $_SESSION['icksumm_uat_SOFTWARE_VERSION'] ?>
                    <?php if (ENVIRONMENT == 'dev') {
                echo ucfirst(ENVIRONMENT);
            } ?>
                </span></div>
            <?php }else{ ?>
                <div class="school_mgt_label hidden-xs"><span class="header_lable_text">ICK Summer Camp</span></div>
            <?php } ?>
            <ul class="nav navbar-nav navbar-right">
                <?php 
                if (!empty($current_session->id) && !empty($version->id) ||(!empty($get_info->school_name) && !empty($get_info->new_registration_session))) {
                    if ($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01' ) {
                        if (check_userrole_by_code('UT01')) { ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-bubbles4"></i>
                                <!-- COMMENTED ON 10-JUNE-2021 <span class="visible-xs-inline-block position-right">Notification</span> -->
                                <!--TEMPORARILY COMMENTED-->
                                <span class="badge bg-warning-400" id="notification_count">wait</span>
                            </a>
                            <!--TEMPORARILY COMMENTED-->
                            <div class="dropdown-menu dropdown-content width-350">
                                <div class="dropdown-content-heading">
                                    Notification
                                </div>
                                <ul class="media-list dropdown-content-body" id="notification_summary">
                                </ul>
                                <div class="dropdown-content-footer">
                                    <a href="<?php echo SITEURL ?>admission_request/admission_request_pending" data-popup="tooltip"><i class="icon-menu display-block"></i></a>
                                </div>
                            </div>
                        </li>
                <?php }
                    }
                }?>
                <!--  <li class="dropdown dropdown-user" style="float: left;">
                	<?php if (check_userrole_by_code('UT02')) { ?>
                    <a href="<?php echo SITEURL ?>help/teacher_help_doc" style="margin-top: 5px;" class="navlink">
                        <img src="<?php echo SITEURL ?>assets/images/help.png" alt="">
                    </a>
                    <?php } else if (check_userrole_by_code('UT05')) { ?>
                    <a href="<?php echo SITEURL ?>help/parents_help_doc" style="margin-top: 5px;" class="navlink">
                        <img src="<?php echo SITEURL ?>assets/images/help.png" alt="">
                    </a>
                     <?php } else if (check_userrole_by_code('UT01')) { ?>
                    <a href="<?php echo SITEURL ?>help/admin_help_doc" style="margin-top: 5px;" class="navlink">
                        <img src="<?php echo SITEURL ?>assets/images/help.png" alt="">
                    </a>
                    <?php } ?>
                </li> -->
                <?php if (!empty($current_session->id)  && !empty($version->id) || (!empty($get_info->school_name) && !empty($get_info->new_registration_session))) { ?>
                <li class="dropdown dropdown-user" style="float:right"> 
                    <a class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo SITEURL ?>assets/images/dummy.jpg" alt="">
                        <span><?php echo $_SESSION['icksumm_uat_login_fullname'] ?></span>
                        <i class="caret"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <!--<li><a href="#"><i class="icon-user-plus"></i> My profile</a></li>
                        <li><a href="#"><i class="icon-coins"></i> My balance</a></li>
                        <li><a href="#"><span class="badge bg-teal-400 pull-right">58</span> <i class="icon-comment-discussion"></i> Messages</a></li>
                        <li class="divider"></li> -->

                        <?php if (check_userrole_by_code('UT01') ) { ?>
                            <li><a href="<?php echo SITEURL ?>change_password"><i class="icon-key"></i> Change Password</a>
                            </li>
                        <?php } elseif (check_userrole_by_code('UT01') || check_userrole_by_code('UT02') || check_userrole_by_code('UT04')) { ?>
                            <li><a href="<?php echo SITEURL ?>staff/staff_edit_personal"><i class="icon-key"></i> Edit
                                    Personal Info</a></li>
                        <?php } elseif (check_userrole_by_code('UT05')) { ?>
                            <li><a href="<?php echo SITEURL ?>parents/parents_edit_personal"><i class="icon-key"></i> Edit
                                    Personal Info</a></li>
                            <!-- <li><a href="<?php echo SITEURL ?>parents/bank_cc_info"><i class="icon-coins"></i> Edit Payment Info</a></li> -->
                        <?php } ?>
  
                        <?php if (check_userrole_by_subgroup('admin')) { ?>
                            <li><a href="<?php echo SITEURL ?>config_info_view"><i class="icon-eye"></i> Configuration</a>
                            </li>

                            <li><a href="<?php echo SITEURL ?>software_version/version-list" class="settinglink"><i class="icon-gear"></i> Software Versions </a></li>

                            <li class="divider"></li>
                            <li><a href="<?php echo SITEURL ?>settings/general_settings" class="settinglink"><i class="icon-gear"></i> General Settings </a></li>
                            <li><a href="<?php echo SITEURL ?>cron/cron-payment-testing" class="settinglink"><i class="icon-gear"></i> Payment Cron Settings </a></li>
                        <?php } ?>
                        <?php if (count((array)$check_page) > 0 && (check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal'))) { ?>
                            <li><a href="<?php echo SITEURL ?>settings/registration_settings" class="settinglink"><i class="icon-gear"></i> Registration Settings </a></li>
                            <li><a href="<?php echo SITEURL ?>settings/school_session" class="settinglink"><i class="icon-gear"></i> School Sessions </a></li>
                        <?php } ?>

                       

                        <li class="divider"></li>
                        <li><a href="<?php echo SITEURL ?>logout" class="logoutlink"><i class="icon-switch2"></i>
                                Logout</a></li>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <!-- /main navbar -->
    <!-- Page container -->
    <div class="page-container">
        <!-- Page content -->
        <div class="page-content">
            <!-- Main sidebar -->

            <?php 
                if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin') {
                    if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->id)) {
                        include "includes/sidemenu.php";
                    }
                }elseif($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'principal'){
                    if (!empty($get_info->school_name) && !empty($get_info->new_registration_session)) {
                        include "includes/sidemenu.php";
                    }
                }elseif($_SESSION['icksumm_uat_login_usertypecode'] == 'UT05' || $_SESSION['icksumm_uat_login_usertypecode'] == 'UT04' || $_SESSION['icksumm_uat_login_usertypecode'] == 'UT03' || $_SESSION['icksumm_uat_login_usertypecode'] == 'UT02'){
                    include "includes/sidemenu.php";
                }
           ?>

        
            <!-- /main sidebar -->
            <!-- Main content -->
            <div class="content-wrapper">
                <div class="blog-section-right-bg hidden-xs ">
                    <img src="<?php echo SITEURL ?>assets/images/strategy-section-bg.png">
                </div>
                <div class="blog-section-left-bg hidden-xs ">
                    <img src="<?php echo SITEURL ?>assets/images/services-section-bg.png">
                </div>