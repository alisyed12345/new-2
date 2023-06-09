<?php include_once "includes/config.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/core.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/components.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/colors.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/sweetalert2.min.css" rel="stylesheet" />
    <link href="<?php echo SITEURL ?>assets/css/dataTables.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITEURL ?>assets/css/mystyle.css" rel="stylesheet" type="text/css">
    <!-- /global stylesheets -->
    <!-- Core JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/loaders/pace.min.js"></script>
    <!--<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>-->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/libraries/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/libraries/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/libraries/jquery_ui/interactions.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/loaders/blockui.min.js"></script>
    <!-- /core JS files -->
    <!-- Theme JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/validation/validate.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/bootstrap_select.min.js"></script>
    <!--<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/inputs/touchspin.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/select2.min.js"></script>-->
    <!--<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/styling/switch.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/styling/switchery.min.js"></script>-->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/styling/uniform.min.js"></script>
    <!--<script type="text/javascript" src="assets/js/core/app.js"></script>
    <script type="text/javascript" src="assets/js/pages/login_validation.js"></script>-->
    <!-- /theme JS files -->

    <!-- Theme JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/tables/datatables/dataTables.responsive.min.js"></script>    
    <!--<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/forms/selects/select2.min.js"></script>-->    
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_multiselect.js"></script>
    <!-- /theme JS files -->

    <!-- Theme JS files -->
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/notifications/jgrowl.min.js"></script>
	<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/ui/moment/moment.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/daterangepicker.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/anytime.min.js"></script>
	<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/pickadate/picker.date.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/pickers/pickadate/picker.time.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/picker_date.js"></script>
    <!-- /theme JS files -->

    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/core/app.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/pages/form_validation.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/sweetalert2.all.min.js"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/jquery.mask.min.js"></script>
    <!-- Optional: include a polyfill for ES6 Promises for IE11 -->
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill"></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/myscript.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/creditCardValidator.js"></script>

    <style>
    .navbar-inverse{
        background-color: #37474f;
        border-color: #37474f;
    }
    </style>
</head>
<body>
    <!-- Page container -->
    <div class="page-container">
        <!-- Page content -->
        <div class="page-content">
            <!-- Main content -->
            <div class="content-wrapper">