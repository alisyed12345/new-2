<?php
include_once "includes/config.php";
$genral_info = $db->get_row("select school_header_logo, new_registration_session from ss_client_settings where status = 1");
$currentPageUrl = strtolower($_SERVER["REQUEST_URI"]);
if (strpos($currentPageUrl, 'student_registration_request') !== false) {
    $curr_session = $db->get_row("select * from ss_school_sessions where id= " . $genral_info->new_registration_session . " ");
    $viewsession = $curr_session->session;
 
} else {
    if(!empty($_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'])){
        $viewsession = $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'];
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if(!empty(SCHOOL_NAME)){ 
    if($current_session->current == 1){
           $current = $current_session->session;
        }else{
           $current  = $current_session->session."Session not added";
        }
        ?>

    <title class="session_titile"><?php echo SCHOOL_NAME ?> (<?php echo $current ?>)</title>
    <?php }else{ ?>
       <title class="session_titile">ICK Summer Camp</title> 
    <?php } ?>
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

    <!-- <script type="text/javascript" src="<?php echo SITEURL ?>ckeditor/ckeditor.js"></script> -->

    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/app.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_validation.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_select2.js"></script>
  <!--   <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/editor_ckeditor.js"></script> -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/myscript.js"></script>
    <?php echo (!empty(get_country()->validator))?get_country()->validator:'' ?>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/creditcard.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/creditCardValidator.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <style>
    @media only screen and (max-width: 768px) {
        .navcl{
            display:none;
        }
    }
     .logosize{
    max-height: 50px;
 } 
    </style>
    <?php if (strlen(strstr($_SERVER['HTTP_USER_AGENT'], "us.quasarsolutions.sdsdsdsdsdsd")) > 0) {?>
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
    <?php } else {?>
        <style>
            .sidebar-content .sidebar-user {
                display: none;
            }
        </style>
    <?php }?>

    <?php //TO SHOW RED STRIP ON HEADER TO INDICATE THAT SESSION IS NOT CURRENT
if ($_SESSION['icksumm_uat_IS_CURRENT_SESSION_YES'] == 0) {?>
        <style>
            .page-header-content {
                background: #ae0006 !important;
            }

            .page-title h4 {
                color: #fff !important;
            }
        </style>
    <?php }?>

</head>

<body>
    <!-- Main navbar -->
    <div class="navbar navbar-inverse">
        <?php if(!empty($get_info->school_header_logo)){?> 
        <div class="navbar-header">
            <!-- <ul class="nav navbar-nav navbar-left">
                <li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="icon-paragraph-justify3"></i></a>
                </li>
            </ul> -->

            <img src="<?php echo SITEURL . $get_info->school_header_logo ?>" style="margin-left:40px;" class="logosize">
            <div class="mob_title"><?php echo $mob_title ?></div>
        </div>
        <?php } ?>
        <div class="navbar-collapse collapse" id="navbar-mobile">
        <?php if(!empty(SCHOOL_NAME) && !empty($viewsession)){?>
            <div class="school_mgt_label hidden-xs"><span class="header_lable_text" style="float:right;margin-right:20px;"> <?php echo CENTER_SHORTNAME . ' ' . SCHOOL_NAME . ' (' . $viewsession . ')' ?>
            </span></div>
        <?php }else{ ?>
            <div class="school_mgt_label hidden-xs"><span class="header_lable_text" style="text-align:center;"> ICK Summer Camp
            </span></div>
        <?php } ?>
        
        </div>
    </div>
    <!-- /main navbar -->
    <!-- Page container -->
    <div class="page-container">
        <!-- Page content -->
        <div class="page-content">
            <!-- Main content -->
            <div class="content-wrapper">
                <div class="blog-section-right-bg hidden-xs ">
                    <img src="<?php echo SITEURL ?>assets/images/strategy-section-bg.png">
                </div>
                <div class="blog-section-left-bg hidden-xs ">
                    <img src="<?php echo SITEURL ?>assets/images/services-section-bg.png">
                </div>