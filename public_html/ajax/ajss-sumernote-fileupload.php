<?php
//include_once "../includes/config.php";
//AUTHARISATION CHECK
// if (!isset($_SESSION['icksumm_uat_login_userid'])) {
// 	return;
// }
if (!empty($_FILES['file']['name'])) {
    $name = randomString();
    $ext = explode('.',$_FILES['file']['name']);
    $filename = $name.'.'.$ext[1];
    $target_dir = "homework/attachments/";
    $destination = $_SERVER['DOCUMENT_ROOT'].'/'.$target_dir.$filename;
    $location =  $_FILES["file"]["tmp_name"];
    move_uploaded_file($location,$destination);
    echo SITEURL.$target_dir.$filename;
}
function randomString() {
    return md5(rand(100, 200));
}
?>