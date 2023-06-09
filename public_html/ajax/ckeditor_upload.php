<?php
include_once "../includes/config.php";
include_once "../includes/resize-class.php";


if(isset($_FILES['upload']['name']))
{
 
 $file = $_FILES['upload']['tmp_name'];
 
 $file_name = $_FILES['upload']['name'];
 $file_name_array = explode(".", $file_name);
 $extension = end($file_name_array);
 $new_image_name = rand() . '.' . $extension;
//  chmod('upload', 0777);
 $allowed_extension = array("jpg", "gif", "png","jpeg");
 if(in_array(strtolower($extension), $allowed_extension))
 {
  $filepath = '../assets/ckeditor_uploads/' . $new_image_name;
  move_uploaded_file($file,  $filepath);  ////jaha pr file save karani ho
  $function_number = $_GET['CKEditorFuncNum'];
  $url = SITEURL.'assets/ckeditor_uploads/'.$new_image_name;  /// server ka path dena hotta hai

//   $resizeObj = new resize($filepath);
//   $resizeObj->resizeImage(200, 200, 'crop');
//   $resizeObj->saveImage($filepath, 1000);

  $message = '';
  echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($function_number, '$url', '$message');</script>";
 }
}

?>