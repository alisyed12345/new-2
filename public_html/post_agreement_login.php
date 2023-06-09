<?php
include "includes/config.php";

if(isset($_GET["authkey"])){
    $username = $_GET["authkey"];
    //$user = $db->get_row("select * from ss_user where MD5(CONCAT('01A1',username)) = '".$db->escape($username)."' OR MD5(CONCAT('01A1',email)) = '".$db->escape($username)."' and is_locked=0 and is_deleted=0 and is_active=1");

    $user = $db->get_row("select * from ss_user where MD5(CONCAT('01A1',username)) = '".$db->escape($username)."' and is_locked=0 and is_deleted=0 and is_active=1");

    if(empty($user)){
        $user = $db->get_row("select * from ss_user where MD5(CONCAT('01A1',email)) = '".$db->escape($username)."' and is_locked=0 and is_deleted=0 and is_active=1");
    }

    if(isset($_GET["arg"])){
        //$db->query("update ss_user set password_expired=0 where id='".$user->id."'");
        //PARENTS EMAIL 					
        $emailbody .= "<p>Assalamualaikum Dear Parent of ".CENTER_SHORTNAME.' '.SCHOOL_NAME.",</p>";
        $emailbody .= "<p>Thanks for completing the ".CENTER_SHORTNAME.' '.SCHOOL_NAME." Registration.</p>"; 
        $emailbody .= "<p>Please find attached copy of the waiver with this email.</p>";
        $emailbody .= "<br><p><strong>FAMILY DETAILS</strong></p>";

        $family = $db->get_row("select * from ss_family where user_id = ".$user->id);
        $emailbody .= "<p><strong>1st parent's:</strong>&nbsp;&nbsp;".$family->father_first_name.' '.$family->father_last_name."</p>";
        if(!empty($family->mother_first_name) && !empty($family->mother_last_name)){
        $emailbody .= "<p><strong>1st parent's:</strong>&nbsp;&nbsp;".$family->mother_first_name.' '.$family->mother_last_name."</p>";
         }
        $emailbody .= "<p><strong>Primary Email:</strong>&nbsp;&nbsp;".$family->primary_email."</p>";
        if(!empty($family->secondary_email)){
        $emailbody .= "<p><strong>Secondary Email:</strong>&nbsp;&nbsp;".$family->secondary_email."</p>";
        }

        $stu_count = 0;
        $students = $db->get_results("select * from ss_student where family_id = ".$family->id);
        foreach($students as $stu){
            $stu_count++;
            $group = $db->get_var("SELECT group_name FROM ss_groups g INNER JOIN ss_studentgroupmap m ON g.id = m.group_id 
            WHERE m.latest = 1 AND m.student_user_id = ".$stu->user_id);

            if(!empty($group)){
                $group = " - Group ".$group;
            }

            $emailbody .= "<p><strong>Student ".$stu_count." Name:</strong>&nbsp;&nbsp;".$stu->first_name." ".$stu->last_name.$group."</p>";
        }

        $emailbody .= "<br><p>Best regards,</p>";
        $emailbody .= "<p>School Team</p>";
       
        //SCHOOL_GEN_EMAIL
        send_my_mail(trim($user->email), CENTER_SHORTNAME.' '.SCHOOL_NAME.' Account Details', $emailbody);
        send_my_mail('lokendra.rajput@quasardigital.com', CENTER_SHORTNAME.' '.SCHOOL_NAME.' Account Details', $emailbody);
    }
                
    if(!empty($user)){
        //ADDED ON 29-AUG-2018
        $type = $db->get_row("select * from ss_usertype where id in (select user_type_id from ss_usertypeusermap where user_id = '".$user->id."') order by preference asc limit 1");
        
        $_SESSION['icksumm_uat_login_userid'] = $user->id;   
        $_SESSION['icksumm_uat_login_usertype'] = $type->user_type;
        $_SESSION['icksumm_uat_login_usertypegroup'] = $type->user_type_group;
        $_SESSION['icksumm_uat_login_usertypecode'] = $type->user_type_code;
        $_SESSION['icksumm_uat_login_username'] = $user->username;
        $_SESSION['icksumm_uat_login_email'] = $user->email;
        
        $total_roles_alloted_to_user = $db->get_results("SELECT distinct user_type_id FROM ss_usertypeusermap m INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE m.user_id = '".$user->id."'");
        $_SESSION['icksumm_uat_login_total_roles_alloted'] = count((array)$total_roles_alloted_to_user);


        if($type->user_type_code == 'UT01'){
            //ADMIN AREA
            $_SESSION['icksumm_uat_login_fullname'] = $user->username;
            $_SESSION['icksumm_uat_login_firstname'] = 'Admin';
        }elseif($type->user_type_code == 'UT05'){
            //PARENTS AREA
            $familyinfo = $db->get_row("select * from ss_family where user_id = '".$user->id."'");
            $_SESSION['icksumm_uat_login_fullname'] = trim($familyinfo->father_first_name.' '.$userinfo->father_last_name);
            $_SESSION['icksumm_uat_login_firstname'] = $familyinfo->father_first_name;
            $_SESSION['icksumm_uat_login_familyid'] = $familyinfo->id;  
        }elseif($type->user_type_code == 'UT02' || $type->user_type_code == 'UT04'){
            //SHEIKH AREA
            $userinfo = $db->get_row("select * from ss_staff where user_id = '".$user->id."'");
            $_SESSION['icksumm_uat_login_fullname'] = $userinfo->first_name.(trim($userinfo->middle_name)!=''?' '.$userinfo->middle_name:'').(trim($userinfo->last_name)!=''?' '.$userinfo->last_name:'');
            $_SESSION['icksumm_uat_login_firstname'] = $userinfo->first_name;
        }elseif($type->user_type_code == 'UT03'){
            //STUDENT AREA
            $userinfo = $db->get_row("select * from ss_student where user_id = '".$user->id."'");
            $_SESSION['icksumm_uat_login_fullname'] = $userinfo->first_name.(trim($userinfo->middle_name)!=''?' '.$userinfo->middle_name:'').(trim($userinfo->last_name)!=''?' '.$userinfo->last_name:'');
            $_SESSION['icksumm_uat_login_firstname'] = $userinfo->first_name;
        }
    
        $db->query("insert into ss_loginhistory set user_id='".$_SESSION['icksumm_uat_login_userid']."', ip_address='".getRealIpAddr()."', login_datetime='".date('Y-m-d H:i:s')."'");

        //REMEMBER ME                   
        if(!empty($_POST["remember"])) {
            setcookie ("member_username",$username,time()+60*60*24*30,'/');
            setcookie ("member_password",$password,time()+60*60*24*30,'/');
            setcookie ("member_school",$school,time()+60*60*24*30,'/');
            setcookie ("member_typecode",$_SESSION['icksumm_uat_login_usertypecode'],time()+60*60*24*30,'/');
        } else {
            if(isset($_COOKIE["member_username"])) {
                setcookie ("member_username","",time(),'/');
            }
            if(isset($_COOKIE["member_password"])) {
                setcookie ("member_password","",time(),'/');
            }
            if(isset($_COOKIE["member_typecode"])) {
                setcookie ("member_typecode","",time(),'/');
            }
        }                    

        if($type->user_type_code == 'UT05'){
            $new_session_students = $db->get_results("select * from ss_family f  INNER JOIN ss_student s ON f.id = s.family_id  INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id where f.user_id='".$_SESSION['icksumm_uat_login_userid']."' AND ssm.session_id ='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_locked=0 and u.is_deleted=0 and u.is_active=1  ");

            if(count((array)$new_session_students) > 0){
                header("location:".SITEURL.'parents/dashboard.php');
            }else{
                header("location:".SITEURL.'new_sesson_term_and_condition.php');
            }
            //header("location:".SITEURL."parents/dashboard.php");
        }else{
            header("location:".SITEURL."dashboard.php");
        }
    }
}

?>