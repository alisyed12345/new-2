<?php 
include_once "includes/config.php";

if(isset($_GET['unique_key']) && !empty($_GET['unique_key'])){

$user_id = $_GET['unique_key'];
$user = $db->get_row("select * from ss_user where md5(concat('0A0',id,'0Z0')) = '". $user_id ."' and is_locked=0 and is_deleted=0 and is_active=1");


if($user->password_expired != 1){
if(count((array)$user) > 0){
    $user_role_map = $db->get_row("select ut.id from ss_usertype ut inner join ss_user_role_map urm on ut.linked_role_id = urm.role_id 
    where user_id = '".$user->id."'");

    //ADDED ON 04SEP2021
    // $type = $db->get_row("select * from ss_usertype where id = '".$user_role_map->id."'");
    
    $type = $db->get_row("select * from ss_usertype where id in (select user_type_id 
    from ss_usertypeusermap where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
    and user_id = '".$user->id."') order by preference asc limit 1");
    //echo("select * from ss_usertype where id = '".$user_role_map->id."'"); exit;
    $_SESSION['icksumm_uat_login_userid'] = $user->id; 	
    $_SESSION['icksumm_uat_login_usertype'] = $type->user_type;
    $_SESSION['icksumm_uat_login_usertypegroup'] = $type->user_type_group;
    $_SESSION['icksumm_uat_login_usertypesubgroup'] = $type->user_type_subgroup;
    $_SESSION['icksumm_uat_login_usertypecode'] = $type->user_type_code;
    $_SESSION['icksumm_uat_login_username'] = $user->username;
    $_SESSION['icksumm_uat_login_email'] = $user->email;
    $_SESSION['login_user_permissions'] = get_user_role_waise_permission($user->id, $type->id);
    
    // $total_roles_alloted_to_user = $db->get_results("SELECT distinct user_type_id FROM ss_usertypeusermap m 
    // INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE m.user_id = '".$user->id."'");
    // $_SESSION['icksumm_uat_login_total_roles_alloted'] = count((array)$total_roles_alloted_to_user);

    // $total_roles_alloted_to_user = $db->get_results("select distinct ut.id as user_type_id from ss_usertype ut 
    // inner join ss_user_role_map urm on ut.linked_role_id = urm.role_id where user_id = '".$user->id."'");

    // $_SESSION['icksumm_uat_login_total_roles_alloted'] = count((array)$total_roles_alloted_to_user);

    $total_roles_alloted_to_user = $db->get_results("SELECT distinct user_type_id FROM ss_usertypeusermap m 
    INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
    and m.user_id = '".$_SESSION['icksumm_uat_login_userid']."' ") ;
    $_SESSION['icksumm_uat_login_total_roles_alloted'] = count((array)$total_roles_alloted_to_user);

    if($type->user_type_code == 'UT01'){
        //ADMIN AREA
        $_SESSION['icksumm_uat_login_fullname'] = $user->username;
        $_SESSION['icksumm_uat_login_firstname'] = 'Admin';
    }elseif($type->user_type_code == 'UT05'){
        //PARENTS AREA
        $familyinfo = $db->get_row("select * from ss_family where user_id = '".$user->id."'");
        $_SESSION['icksumm_uat_login_fullname'] = trim($familyinfo->father_first_name.' '.$familyinfo->father_last_name);
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


    // echo "<pre>";
    // print_r($_SESSION);
    // die;

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
    
    if(isset($_SESSION['icksumm_uat_redirected_url'])){
        $target_url = SITEURL.$_SESSION['icksumm_uat_redirected_url'];
    }else{
        if($type->user_type_code == 'UT05'){
            $new_session_students = $db->get_results("select * from ss_family f  INNER JOIN ss_student s ON f.id = s.family_id  INNER JOIN ss_user u ON u.id = s.user_id where f.user_id='".$_SESSION['icksumm_uat_login_userid']."' AND u.session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_locked=0 and u.is_deleted=0 and u.is_active=1  ");

            if(count((array)$new_session_students) > 0){
                $target_url = SITEURL.'parents/dashboard.php';
            }else{
                $target_url = SITEURL.'dashboard.php';
            
            } 
        }else{
            $target_url = SITEURL.'dashboard.php';
        }
    }
    
    header("Location: ".$target_url."");
    die(); 
}else{
    header("Location: ".SITEURL."");
    die();   
}
}else{
$user_key = md5('0A0'.$user->id.'0Z0');
$target_url = SITEURL.'onlineclass-waiver.php?key='.$user_key;
header("Location: ".$target_url."");
die();
}	

}else{

  header("Location: ".SITEURL."");
  die();
}




?>