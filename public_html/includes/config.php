<?php 
ob_start();
ini_set("display_errors","off");
error_reporting(15);

require_once('phpmailer/class.phpmailer.php');


//SET DEFAULT TIMIEZONE
date_default_timezone_set("America/Chicago");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//PayPal
require_once("PaypalPro.class.php");

//Standard ezSQL Libs
require_once("ezSQL/ez_sql_core.php");
require_once("ezSQL/ez_sql_mysqli.php");


//Credentials
$db_host = "localhost";
$db_name = 'new';
$db_user = 'root';
$db_pswd = ''; 


//Initialise singleton
$db = new ezSQL_mysqli($db_user,$db_pswd,$db_name,$db_host,'UTF-8');



//google login start
/* include_once 'googlelogin/Google_Client.php';
include_once 'googlelogin/contrib/Google_Oauth2Service.php';

$clientId = '694195532802-hug01rafjg59vtes7ema5kes93acipn5.apps.googleusercontent.com'; //Google client ID
$clientSecret = 'M32MGL8YI33rkXjdvQxzBCF4'; //Google client secret
$redirectURL = 'http://localhost/academy/googlelogin_callback.php/'; //Callback URL

//Call Google API
$gClient = new Google_Client();
$gClient->setApplicationName('login_with_google_using_php');
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectURL);

$google_oauthV2 = new Google_Oauth2Service($gClient); */
//google login end

define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/');

$get_info = $db->get_row("SELECT school_header_logo, school_name, contact_organisation_email, school_logo, contact_phone, contact_address,
contact_city, contact_zipcode, new_registration_session, is_waiting, s.abbreviation, center_short_name, one_student_one_lavel, contact_organization_name, contact_admin_email
FROM ss_client_settings cs LEFT OUTER JOIN ss_state s ON cs.contact_state_id = s.id WHERE STATUS=1 ");

$get_cron_info = $db->get_var("SELECT cron_date FROM ss_cron_payment_testing WHERE status = 1");

//CLIENT/SCHOOL/CENTER INFORMATION
define('SCHOOL_NAME', $get_info->school_name);
define('ORGANIZATION_NAME', $get_info->contact_organization_name);
define('SCHOOL_GEN_EMAIL', $get_info->contact_organisation_email);
define('LOGO', $get_info->school_header_logo);
define('SCHOOL_LOGO', $get_info->school_logo);
define('SCHOOL_CONTACT_NO', $get_info->contact_phone);
define('SCHOOL_ADDRESS', $get_info->contact_address);
define('SCHOOL_ADDRESSSTATE', $get_info->abbreviation);
define('SCHOOL_ADDRESSCITY', $get_info->contact_city);
define('SCHOOL_ADDRESSZIPCODE', $get_info->contact_zipcode);
define('CENTER_SHORTNAME', $get_info->center_short_name);
define('ONE_STUDENT_ONE_LEVEL', $get_info->one_student_one_lavel);
define('ADMIN_EMAIL', $get_info->contact_admin_email);
define('ENCRYPTION_METHOD', "AES-256-CBC");
define('SECRET_HASH', "1xXrpIRS4kTV2QSs1j9dibWdDxxE5n55VT1u8kaRPQf3DBMvK5wiUNq7Nknv0t82BHl5rKu7RdIf400tARxMROZ");
define('IV',"Bayyan@2A0B19Qs#");

define('IsWaiting', $get_info->is_waiting);

//SET CONFIG CONSTANTS
$configs = $db->get_results("select * from ss_config");
foreach($configs as $conf){
    define( $conf->key, $conf->value);
}

define('PAYMENT_SUCCESS',"AA Your payment of [AMOUNT] for ".CENTER_SHORTNAME.' '.SCHOOL_NAME." was processed successfully. JZK");
define('PAYMENT_FAILED',"AA Your payment of [AMOUNT] for ".CENTER_SHORTNAME.' '.SCHOOL_NAME." was declined Pls contact ".SCHOOL_GEN_EMAIL." JZK");

define('PAYSERVICE_URL', 'https://securepayuat.bayyan.org/'); 

$current_session = $db->get_row("select * from ss_school_sessions where current = 1 AND status = 1");

//Get Current Session
if(!isset($_SESSION['icksumm_uat_CURRENT_SESSION'])){
    $_SESSION['icksumm_uat_CURRENT_SESSION'] = $current_session->id;
    $_SESSION['icksumm_uat_IS_CURRENT_SESSION_YES'] = $current_session->current;

    if($current_session->current == 1){
        $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $current_session->session;
    }else{
        $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $current_session->session;
    }
    //$_SESSION['icksumm_uat_CURRENT_SESSION_TEXT'] = $current_session->session;
}

$version = $db->get_row("select * from ss_software_version where status = 1 ");
//SET CURRENT VERSION
if(!isset($_SESSION['icksumm_uat_SOFTWARE_VERSION'])){
    $_SESSION['icksumm_uat_SOFTWARE_VERSION'] = $version->major.'.'.$version->minor.'.'.$version->patch;
}

$get_cron_info = $db->get_var("SELECT cron_date FROM ss_cron_payment_testing WHERE status = 1");

$MONTHS = array(1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec");

include "global_function.php";

//SET DEFAULT TIMIEZONE
get_country()->timezone;
?>
