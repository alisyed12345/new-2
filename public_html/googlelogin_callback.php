<?php
include "includes/config.php";
if(isset($_GET['code'])){
	$gClient->authenticate($_GET['code']);
	$_SESSION['token'] = $gClient->getAccessToken();
	header('Location: ' . filter_var($redirectURL, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
	$gClient->setAccessToken($_SESSION['token']);
}

if ($gClient->getAccessToken()) {
	//Get user profile data from google
  $gpUserProfile = $google_oauthV2->userinfo->get();
    
  $email_verified = $db->get_row("select * from ss_user where is_active = 1 and is_deleted = 0 and username='".$gpUserProfile['email']."' OR email='".$gpUserProfile['email']."' ");
     
  if(count($email_verified) > 0){
     
  //Insert or update user data to the database
  //Check whether user data already exists in database    
  $prevResult = $db->get_row("SELECT * FROM ss_user_google_info WHERE oauth_provider = 'google' AND oauth_uid = '".$gpUserProfile['id']."'");
  if(count($prevResult) > 0){
      //Update user data if already exists
        $db->query("UPDATE ss_user_google_info SET first_name = '".$gpUserProfile['given_name']."', last_name = '".$gpUserProfile['family_name']."', email = '".$gpUserProfile['email']."', gender = '".$gpUserProfile['gender']."', locale = '".$gpUserProfile['locale']."', picture = '".$gpUserProfile['picture']."', link = '".$gpUserProfile['link']."', modified = '".date("Y-m-d H:i:s")."' WHERE oauth_provider = 'google' AND oauth_uid = '".$gpUserProfile['id']."' ");
    }else{
        //Insert user data
        $db->query("INSERT INTO ss_user_google_info SET oauth_provider = 'google', oauth_uid = '".$gpUserProfile['id']."', first_name = '".$gpUserProfile['given_name']."', last_name = '".$gpUserProfile['family_name']."', email = '".$gpUserProfile['email']."', gender = '".$gpUserProfile['gender']."', locale = '".$gpUserProfile['locale']."', picture = '".$gpUserProfile['picture']."', link = '".$gpUserProfile['link']."', created = '".date("Y-m-d H:i:s")."', modified = '".date("Y-m-d H:i:s")."'");
  }  

      $email = md5('01A1'.$gpUserProfile['email']);
      header("location:".SITEURL."post_agreement_login.php?authkey=$email");  
  }else{
      header("location:".SITEURL."login.php?auth=failed");
  } 


} 


?>