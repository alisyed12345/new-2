<?php
$pagename = basename($_SERVER['PHP_SELF']);

$new_text_msg_rec = $db->get_var("select count(1) from ss_bulk_sms_reply where is_read = 0");

//THIS IS HACK. TO DISPLAY NEW MESSAGE TO 0 ON OPENING mass_text_msg_reply PAGE, SINCE IT UPDATES AFTER DISPLAYING LEFT MENU
if(basename($_SERVER['REQUEST_URI']) == "mass_text_msg_reply"){
$new_text_msg_rec = 0;
}
// echo "SELECT distinct t.id, t.user_type_group FROM ss_usertypeusermap m 
// INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
// and m.user_id = '".$_SESSION['icksumm_uat_login_userid']."' ";
// die;

// echo "<pre>";
// print_r($_SESSION);
// die;

$role_ary_sidebar = [];

$usertype = $db->get_results("SELECT distinct t.id, t.user_type_subgroup FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE m.user_id = '".$_SESSION['icksumm_uat_login_userid']."' and (t.user_type_subgroup= 'admin' or t.user_type_subgroup = 'principal') ") ;

$user_types_sidebar = $db->get_results("SELECT distinct t.id, t.user_type_subgroup FROM ss_usertypeusermap m 
INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
and m.user_id = '".$_SESSION['icksumm_uat_login_userid']."' ") ;

if(count($usertype) > 0){
$user_types_sidebar = array_merge($user_types_sidebar, $usertype);
}

foreach($user_types_sidebar as $utype){
//if(!in_array($utype->user_type_group, $role_ary_sidebar)){
$role_ary_sidebar[$utype->id] = $utype->user_type_subgroup; 
//}
}



$client_setting = $db->get_row("select * from ss_client_settings where status = 1 order by id desc limit 1");

$user_message_read = $db->get_results("select * from ss_message where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and is_read = 0 
and (created_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' or rec_user_id = '" . $_SESSION['icksumm_uat_login_userid']. "') 
order by id desc");

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/fontawesome.min.css"
integrity="sha512-OdEXQYCOldjqUEsuMKsZRj93Ht23QRlhIb8E/X0sbwZhme8eUw6g8q7AdxGJKakcBbv7+/PX0Gc2btf7Ru8cZA=="
crossorigin="anonymous" />
<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
<style>
.dot {
height: 10px;
width: 10px;
background-color: #ffc00f;
border-radius: 50%;
display: inline-block;
}
</style>
<!-- <style>
.sidebarwidth {
width: 270px !important;
}
.navborder {
border-left: solid 1px #afafaf;
padding-left: 3px !important;
}
</style> -->

<div class="sidebar sidebar-main sidebarwidth">
<div class="sidebar-content">

<div class="sidebar-user">
<div class="category-content">
<div class="media"> <a href="#" class="media-left"><img
                src="<?php echo SITEURL ?>assets/images/dummy.jpg" class="img-circle img-sm" alt=""></a>
        <div class="media-body"> <span
                class="media-heading text-semibold"><?php echo $_SESSION['icksumm_uat_login_fullname'] ?></span>
        </div>
        <div class="media-right media-middle">
        <ul class="icons-list">
                <li> <a href="<?php echo SITEURL ?>logout" title="Logout" class="logoutlink"><i
                        class="icon-switch2"></i></a> </li>
        </ul>
        </div>
</div>
</div>
</div>

<div class="sidebar-category sidebar-category-visible">
<div class="category-content no-padding">
<ul class="navigation navigation-main navigation-accordion">
        <li class="navigation-header navborder" style="font-weight: 300px; font-size: 1.4rem;">
                <span>
                <div class="row">
                        <div class="col-md-4 text-right">
                                <?php echo $_SESSION['icksumm_uat_login_usertype'] ?>
                        </div>
                        <div class="col-md-8">
                                <?php
                                // echo"<pre>";
                                // print_r($role_ary_sidebar);
                                // die;
                                //COMMENTED ON 09-OCT-2020 - TEMPORARILY TO FIX ISSUE 
                                //if($_SESSION['icksumm_uat_login_total_roles_alloted'] > 1){ 
                                if(count($role_ary_sidebar) > 1){
                                ?>
                                <a href="javascript:;" data-toggle="modal" data-target="#modal_switch_account"
                                        class="pull-right">
                                        Switch Account
                                </a>
                                <?php } ?> 
                        </div>
                </div>
                </span>
        <i class="icon-menu" title="Main pages"></i>
        </li>
        
        <?php  
        //SUPER ADMIN, PRINCIPAL (ADMIN) AND TEACHER MENU based on their roles
        if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01' || $_SESSION['icksumm_uat_login_usertypecode'] == 'UT02' || $_SESSION['icksumm_uat_login_usertypecode'] == 'UT04'){ ?>

        <li class="<?php echo ($pagename=="dashboard")?"active":"" ?>"><a
                href="<?php echo SITEURL ?>dashboard" class="navlink"><i class="icon-home4"></i>
                <span>Dashboard</span></a></li>


        <?php   //if(in_array("su_role_list", $_SESSION['login_user_permissions']) || in_array("su_permissions_list", $_SESSION['login_user_permissions'])){
        if($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){  ?>
        <li class="<?php echo ($pagename=="role_list" || $pagename=="permission_list")?"active":"" ?>"> <a
                href="#"><i class="icon-users"></i> <span>Role & Permission </span></a>
        <ul>
                <?php if(in_array("su_role_list", $_SESSION['login_user_permissions'])){  ?>
                <li><a href="<?php echo SITEURL ?>role/role_list"
                        class="navlink <?php echo $pagename=="students_list"?"active":"" ?>">Role</a></li>

                <?php }
        if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){
        if(in_array("su_permissions_list", $_SESSION['login_user_permissions'])){
        
        ?>
                <li><a href="<?php echo SITEURL ?>permissions/permission_list"
                        class="navlink <?php echo $pagename=="family_list"?"active":"" ?>">Permission</a>
                </li>

                <?php } } ?>
        </ul>
        </li>
        <?php } if($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'principal'){  ?> 
                <?php if(in_array("su_role_list", $_SESSION['login_user_permissions'])){  ?>
                
                <li><a href="<?php echo SITEURL ?>role/role_list"
                        class="navlink <?php echo $pagename=="students_list"?"active":"" ?>"><i
                class="icon-user-lock"></i>Role</a></li>

                <?php }?>
                
        <?php } ?>
        <?php   if(in_array("su_student_list", $_SESSION['login_user_permissions']) || in_array("su_family_list", $_SESSION['login_user_permissions'])){  ?>
        <li class="<?php echo ($pagename=="students_list" || $pagename=="family_list")?"active":"" ?>"> <a
                href="#"><i class="icon-users"></i> <span>Students &amp; Families</span></a>
        <ul>


                <?php if(in_array("su_student_list", $_SESSION['login_user_permissions'])){ ?>
                <li><a href="<?php echo SITEURL ?>student/students_list"
                        class="navlink <?php echo $pagename=="students_list"?"active":"" ?>">Student</a>
                </li>
                <?php } ?>


                <?php if(in_array("su_family_list", $_SESSION['login_user_permissions'])){ ?>
                <li><a href="<?php echo SITEURL ?>student/family_list"
                        class="navlink <?php echo $pagename=="family_list"?"active":"" ?>">Family View</a>
                </li>
                <?php } ?>

                <?php if(in_array("su_add_new_family", $_SESSION['login_user_permissions']) || $_SESSION['icksumm_uat_login_usertypesubgroup'] == 'principal' || $_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){ ?>    
                <li><a href="<?php echo SITEURL ?>parents/add_family_student_without_payment"
                        class="navlink <?php echo $pagename=="add_family_student_without_payment"?"active":"" ?>">Add
                        New Family</a>
                </li>
                <?php } ?>
        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_attendence_list", $_SESSION['login_user_permissions']) || in_array("su_attendence_list", $_SESSION['login_user_permissions'])){  ?>
        <li>
        <a href="#"><i class="icon-user-check"></i> <span>Attendance</span></a>
        <ul>
                <li><a href="<?php echo SITEURL ?>attendance/attendance_today"
                        class="navlink <?php echo $pagename=="attendance_today"?"active":"" ?>">Today's
                        Attendance</a></li>
              <!--   <li><a href="<?php echo SITEURL ?>attendance/attendance_history_monthly"
                        class="navlink <?php echo $pagename=="attendance_history_monthly"?"active":"" ?>">
                        Class Wise History</a></li>      -->   
                <li><a href="<?php echo SITEURL ?>attendance/attendance_history"
                        class="navlink <?php echo $pagename=="attendance_history"?"active":"" ?>">
                        Group Wise History</a></li>
                

                

        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_admission_request_list", $_SESSION['login_user_permissions']) || in_array("su_admission_request_list", $_SESSION['login_user_permissions'])){  ?>
        <li>
        <a href="#"><i class="icon-user-plus"></i> <span>Admission Request</span></a>
        <ul>

                <li><a href="<?php echo SITEURL ?>admission_request/admission_request_pending"
                        class="navlink <?php echo $pagename=="admission_request_pending"?"active":"" ?>">Pending</a>
                </li>
                <li><a href="<?php echo SITEURL ?>admission_request/admission_request_completed"
                        class="navlink <?php echo $pagename=="admission_request_completed"?"active":"" ?>">Completed</a>
                </li>

        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_staff_list", $_SESSION['login_user_permissions']) || in_array("su_staff_create", $_SESSION['login_user_permissions'])){  ?>
        <li>
        <a href="#"><i class="icon-user-tie"></i> <span>Staff</span></a>
        <ul>
                
                <li><a href="<?php echo SITEURL ?>staff/staffs_list"
                        class="navlink <?php echo $pagename=="staffs_list"?"active":"" ?>">List All Staff</a></li>
                <li><a href="<?php echo SITEURL ?>staff/staff_pending_list"
                        class="navlink <?php echo $pagename=="staff_pending_list"?"active":"" ?>">Staff
                        Pending List</a></li>
                <li><a href="<?php echo SITEURL ?>staff/staff_add"
                        class="navlink <?php echo $pagename=="staff_add"?"active":"" ?>">Add New Staff Member</a>
                </li>

        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_classes_list", $_SESSION['login_user_permissions']) || in_array("su_classes_create", $_SESSION['login_user_permissions']) || in_array("su_group_list", $_SESSION['login_user_permissions'])){  ?>

        <li>
        <a href="#"><i class="icon-make-group"></i> <span>Groups &amp; Classes</span></a>
        <ul>
                        <li><a href="<?php echo SITEURL ?>group/groups_manage"
                        class="navlink <?php echo $pagename=="groups_manage"?"active":"" ?>">Manage
                        Groups</a></li>
                        <li><a href="<?php echo SITEURL ?>subjects/list_all_subjects"
                        class="navlink <?php echo $pagename=="list_all_subjects"?"active":"" ?>">Manage
                        Classes</a></li>

                        <li><a href="<?php echo SITEURL ?>group/classtime_list"
                        class="navlink <?php echo $pagename=="classtime_list"?"active":"" ?>">Manage Class
                        Time</a></li>

                        <!-- <li><a href="<?php echo SITEURL ?>group/classtime_add"
                        class="navlink <?php echo $pagename=="classtime_add"?"active":"" ?>">Add Class
                        Time</a></li> -->

                        <li><a href="<?php echo SITEURL ?>online_classes/list_online_classes"
                        class="navlink <?php echo $pagename=="list_online_classes"?"active":"" ?>">Manage
                        Online Classes</a></li>
        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_internal_msg_list", $_SESSION['login_user_permissions']) || in_array("su_communicate_list", $_SESSION['login_user_permissions']) || in_array("su_communicate_send_mass_email", $_SESSION['login_user_permissions']) || in_array("su_communicate_sent_text_create", $_SESSION['login_user_permissions']) || in_array("su_communicate_recived_text_view", $_SESSION['login_user_permissions'])){  ?>
        <li>
        <a href="#"><i class="icon-envelop2"></i> <span>Communication</span>
                <?php echo $new_text_msg_rec > 0 ? '<span class="label label-warning">New</span>' : '' ?></a>
        <ul>
        <?php if(in_array("su_internal_msg_list", $_SESSION['login_user_permissions'])) { ?>
                        <li><a href="<?php echo SITEURL ?>message/inertnal_message_list"
                        class="navlink <?php echo $pagename=="mass_text_msg_history"?"active":"" ?>">Internal
                        Message
                        <?php if(count($user_message_read) > 0){ ?>
                        <label class="dot"></label>
                        <?php } ?>
                </a></li>
                        <?php } ?>


                <?php if(in_array("su_communicate_send_mass_email", $_SESSION['login_user_permissions'])) { ?>
                <li><a href="<?php echo SITEURL ?>message/mass_email_history"
                        class="navlink <?php echo $pagename=="mass_email_history"?"active":"" ?>">Mass
                        Emails</a></li>
                <?php } ?>

                <?php if(in_array("su_communicate_sent_text_create", $_SESSION['login_user_permissions'])) { ?>
                <li><a href="<?php echo SITEURL ?>message/mass_text_msg_history"
                        class="navlink <?php echo $pagename=="mass_text_msg_history"?"active":"" ?>">Text
                        Message Sent</a></li>
                <?php } ?>

                <?php //if(in_array("su_communicate_sent_text_view", $_SESSION['login_user_permissions'])) { ?>
                <!-- <li><a href="<?php //echo SITEURL ?>message/mass_text_msg_reply"
                        class="navlink <?php //echo $pagename=="mass_text_msg_reply"?"active":"" ?>">Text
                        Message Received
                        <?php //echo $new_text_msg_rec > 0 ? ('<span class="label label-warning">'.$new_text_msg_rec.'</span>') : '' ?></a>
                </li> -->
                <?php //} ?>



                <?php  //if(in_array("su_message_template_list", $_SESSION['login_user_permissions'])){  ?>
                <!-- <li><a href="<?php echo SITEURL ?>message_template/list_message_template" class="navlink">
                        <span>Message Templates</span></a></li> -->
                <?php //} ?>

                <?php  if(in_array("su_email_template_list", $_SESSION['login_user_permissions'])){  ?>
                <li><a href="<?php echo SITEURL ?>email_template/list_all_email_template" class="navlink">
                        <span>Custom Email Templates</span></a></li>
                <?php } ?>


        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_homework_list", $_SESSION['login_user_permissions']) || in_array("su_homework_create", $_SESSION['login_user_permissions'])){  ?>
        <li>
        <a href="#"><i class="icon-book"></i> <span>Homework</span></a>
        <ul>
                <li><a href="<?php echo SITEURL ?>homework/homework_list"
                        class="navlink <?php echo $pagename=="homework_list"?"active":"" ?>">Homework List</a></li>
                <li><a href="<?php echo SITEURL ?>homework/homework_add"
                        class="navlink <?php echo $pagename=="homework_add"?"active":"" ?>">Send Homework</a>
                </li>

        </ul>
        </li>
        <?php } ?>
        <?php  if(in_array("su_report_list", $_SESSION['login_user_permissions'])){  ?>
        <li> <a href="#"><i class="icon-newspaper2"></i> <span>Report</span></a>
        <ul>

                <li><a href="<?php echo SITEURL ?>report/enroll_report"
                        class="navlink <?php echo $pagename=="enroll_report"?"active":"" ?>">Enrollment
                        Report</a></li>
                <li><a href="<?php echo SITEURL ?>report/admission_pending_req_report"
                        class="navlink <?php echo $pagename=="admission_pending_req_report"?"active":"" ?>">Admission
                        Request (Pending) Report</a></li>
                <li><a href="<?php echo SITEURL ?>report/discount_report"
                        class="navlink"><span>Discount Report</span></a></li>
                <li><a href="<?php echo SITEURL ?>report/payment_report"
                        class="navlink"><span>Scheduled Payment Report</span></a></li>
                <li><a href="<?php echo SITEURL ?>report/registration_payment_report"
                        class="navlink"><span>Registration Payment Report</span></a></li>
                <!--    <li><a href="<?php echo SITEURL ?>report/schedule_payment_report"
                        class="navlink"><span>Schedule Payment Report</span></a></li>
                <li><a href="<?php echo SITEURL ?>report/family_wise_schedule_payment_report"
                        class="navlink"><span>Family Wise (Schedule Payment) Report</span></a></li> -->
        </ul>
        </li>
        <?php } ?>

        <?php   if(in_array("su_basic_fees_list", $_SESSION['login_user_permissions']) || in_array("su_discount_manage_fees_list", $_SESSION['login_user_permissions']) ){  ?>
        <li>
        <a href="#"><i class="icon-coins"></i> <span>Payments</span></a>
        <ul>
                <?php if($client_setting->fees_monthly == 1){ ?>

                <?php if(in_array("su_basic_fees_list", $_SESSION['login_user_permissions'])) { ?>
                <li><a href="<?php echo SITEURL ?>basicfees/basic_fees_list"
                        class="navlink <?php echo $pagename=="basic_fees_list"?"active":"" ?>">Basic
                        Fees</a></li>
                <?php } 

                if(in_array("su_discount_manage_fees_list", $_SESSION['login_user_permissions'])){ ?>
                <li><a href="<?php echo SITEURL ?>discountfees/discount_fees_list"
                        class="navlink <?php echo $pagename=="discount_fees_list"?"active":"" ?>">Fees
                        Discounts</a></li>
                <?php } ?>

                <?php } ?>

                <?php if(in_array("su_family_info", $_SESSION['login_user_permissions'])){ ?>
                <li><a href="<?php echo SITEURL ?>payment/family_info"
                        class="navlink <?php echo $pagename=="family_info"?"active":"" ?>">Family Info</a>
                </li>
                <?php  if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ ?>
                <li><a href="<?php echo SITEURL ?>payment/payment_approval_list"
                        class="navlink <?php echo $pagename=="payment_approval_list"?"active":"" ?>">Payment Approval List</a>
                </li>

                <?php  } } ?>

        </ul>


        </li>
        <?php } ?>


        <?php  if(in_array("su_event_calendar", $_SESSION['login_user_permissions'])){  ?>
        <li><a href="<?php echo SITEURL ?>event_calender/all_list_calender" class="navlink"><i
                class="icon-calendar"></i>
                <span>Event Calendar</span></a></li>
        <?php } ?>
        <?php  if(in_array("su_pages_list", $_SESSION['login_user_permissions'])){  ?>
        <li> <a href="#"><i class="icon-link"></i> <span>Useful Pages</span></a>
        <ul>
                
                <li><a href="<?php echo SITEURL ?>page/page_list"
                        class="navlink <?php echo $pagename=="page_list"?"active":"" ?>">Manage Pages</a>
                </li>
                <li>
                <hr>
                </li>
                

                <?php   
                $pages = $db->get_results("select * from ss_page where active = 1 order by page_name desc");

                if(count($pages) > 0){
                foreach ($pages as $page) {
                ?>
                <li class="<?php echo ($pagename == $page->slug)?"active":"" ?>"><a
                        href="<?php echo SITEURL ?>parents/page?slug=<?= $page->slug ?>"
                        class="navlink"><i
                        class="icon-book2"></i> <?= $page->page_name ?></a></li>

                <?php } } ?>
        </ul>
        </li>
        <?php } ?>                           
        <?php   if(in_array("su_holiday_list", $_SESSION['login_user_permissions']) || in_array("su_announcements_manage", $_SESSION['login_user_permissions'])
        || in_array("su_pages_list", $_SESSION['login_user_permissions']) || in_array("su_login_history", $_SESSION['login_user_permissions']) ){  ?>
        <li>
        <a href="#"><i class="icon-make-group"></i> <span>Others</span></a>
        <ul>
                <?php  if(in_array("su_holiday_list", $_SESSION['login_user_permissions'])){  ?>
                <li><a href="<?php echo SITEURL ?>others/holidays_manage"
                        class="navlink"><span>Holidays</span></a></li>
                <?php  } ?>

                <?php  if(in_array("su_announcements_manage", $_SESSION['login_user_permissions'])){  ?>
                <li><a href="<?php echo SITEURL ?>others/announcements_manage"
                        class="navlink"><span>Announcements</span></a></li>

                <?php  } ?>

                <?php  if(in_array("su_login_history", $_SESSION['login_user_permissions'])){  ?>
                <li class="<?php echo ($pagename=="login_logout")?"active":"" ?>"><a
                        href="<?php echo SITEURL ?>others/login_logout" class="navlink">Login History</a>
                </li>
                <li>
                <hr />
                </li>
                <?php  } ?>

        </ul>
        </li>
        <?php  } ?>

        <?php   if(in_array("su_teacher_resource", $_SESSION['login_user_permissions'])){  ?>
        <li><a href="<?php echo SITEURL ?>teacher_resource/list_all_teacher_resource" class="navlink"><i
                class="icon-circle"></i>
                <span>Teacher Resources</span></a></li>
        <?php  } ?>

        <?php if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){
                if(in_array("ss_log_view", $_SESSION['login_user_permissions'])){ ?>

        <li><a href="<?php echo SITEURL ?>log_view" class="navlink"><i class="icon-home4"></i>
                <span>Log View</span></a></li>

        <?php }} ?>





        <?php }else{ ?>

        <?php
        //======================= PARENTS MENU
if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT05'){ ?>
        <li><a href="<?php echo SITEURL ?>parents/dashboard" class="navlink"><i class="icon-home4"></i>
                <span>Dashboard</span></a></li>

        <?php  // if(in_array("su_attendence_list", $_SESSION['login_user_permissions'])){  ?>
        <li><a href="<?php echo SITEURL ?>parents/attendance" class="navlink"><i
                class="icon-user-check"></i> <span>Attendance</span></a></li>
        <?php // } ?>

        <li>
        <a href="#"><i class="icon-envelop2"></i> <span>Message</span>
                <?php echo $new_text_msg_rec > 0 ? '<span class="label label-warning">New</span>' : '' ?></a>
        <ul>
                <li><a href="<?php echo SITEURL ?>message/inertnal_message_list"
                        class="navlink <?php echo $pagename=="mass_text_msg_history"?"active":"" ?>">Received Internal
                        Message 
                        <?php if(count($user_message_read) > 0){ ?>
                        <label class="dot"></label>
                        <?php } ?>
                </a></li>
                <?php // if(in_array("su_communicate_send_mass_email", $_SESSION['login_user_permissions'])) { ?>
                <li><a href="<?php echo SITEURL ?>message/internal_message_new_parents.php"
                        class="navlink <?php echo $pagename=="mass_email_history"?"active":"" ?>">Send
                        Internal Message</a></li>
                <?php // } ?>

        </ul>
        </li>

        <?php //  if(in_array("su_homework_list", $_SESSION['login_user_permissions'])){  ?>
        <li><a href="<?php echo SITEURL ?>homework/homework_parents" class="navlink"><i
                class="icon-book"></i> <span>Homework</span></a></li>
        <?php // } ?>
        
        <?php
         $pages = $db->get_results("select * from ss_page where active = 1 order by page_name desc");
         if(count($pages) > 0){ ?>
        <li> <a href="#"><i class="icon-link"></i> <span>Useful Pages</span></a>
        <ul>
                <?php   
              

                if(count($pages) > 0){
                foreach ($pages as $page) {
                ?>
                <li class="<?php echo ($pagename == $page->slug)?"active":"" ?>"><a
                        href="<?php echo SITEURL ?>parents/page?slug=<?= $page->slug ?>" class="navlink"><i
                        class="icon-book2"></i> <span><?= $page->page_name ?></span></a></li>

                <?php } } ?>
        </ul>
        </li>
        <?php } ?>
        <!--COMMENTED BECAUSE WE DON'T WANT TO GIVE THIS FEATURE TO ICK
                <li><a href="<?php echo SITEURL ?>teacher_resource/teacher_resources_class_board.php"
                class="navlink"><i class="icon-circle"></i> <span>Teacher Resources</span></a></li> -->
        <li>
        <a href="#"><i class="icon-coins"></i> <span>Payments</span></a>
        <ul>
                <li> <a href="<?php echo SITEURL ?>parents/payment_fees_history_list"
                        class="navlink <?php echo $pagename=="payment_fees_history_list"?"active":"" ?>">Payment
                        History</a></li>
                <li> <a href="<?php echo SITEURL ?>parents/payment_credential_list"
                        class="navlink <?php echo $pagename=="payment_credential_list"?"active":"" ?>">Payment
                        Credential</a></li>
                <li> <a href="<?php echo SITEURL ?>payment/invoice_list"
                        class="navlink <?php echo $pagename=="invoice_list"?"active":"" ?>">Accounting</a></li>
        </ul>
        </li>
        <li>
        <a href="<?php echo SITEURL ?>others/holidays_parent_view"
                        class="navlink"><i class="icon-calendar"></i> <span>Holidays</span></a>
        
        </li>
                

        <?php }} ?>
</ul>
</div>
</div>
</div>
</div>