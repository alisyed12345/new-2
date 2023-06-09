<?php
include "header.php";

/* //AUTHARISATION CHECK
if(!isset($_SESSION['icksumm_uat_login_userid'])){
  return;
} */
//AUTHARISATION CHECK 
if (!in_array("ss_log_view", $_SESSION['login_user_permissions'])) { 
	//if ($_SESSION['icksumm_uat_login_usertypecode'] != 'UT01' && $_SESSION['icksumm_uat_login_usertypecode'] != 'UT02' || !in_array("su_role_list", $_SESSION['login_user_permissions'])) {
	include "../includes/unauthorized_msg.php";
	return;
}

$dir = "logs/";

$logs = scandir($dir,1);

$newLogArray = [];
foreach ($logs as $value) {
 trim(str_replace('-', '', $value));
 trim(str_replace('--', '', $value));
 $str1 = trim(str_replace('log_', '', $value));
 $str2 = trim(str_replace('.log', '', $str1));
 $newLogArray[] = trim(str_replace('.', '-', $str2));
}

function compareByTimeStamp($time1, $time2) 
{
  if (strtotime($time1) < strtotime($time2)) 
    return 1;
  else if (strtotime($time1) > strtotime($time2))  
    return -1;
  else
    return 0;
} 

  // sort array with given user-defined function 
usort($newLogArray, "compareByTimeStamp"); 


if(!empty($_GET['logDate'])){
  $date = $_GET['logDate'];
}else{

  $date =  "log_".date('j.n.Y').".log";

}

?>



<link href="logstyle.css" rel="stylesheet">

<div class="container" style="width: 1620px;">
 <div class="mail-box">
    <div class="inbox-body">
      <div class="row">
        <br>
      <div class="col-md-4">
     <label>Select Log Date</label>
     <select name="logerror" class="form-control">
      <option><?php echo $date; ?></option>
        <?php foreach ($newLogArray as $value) {
        $realval = trim(str_replace('-', '.', $value));
        $logval = "log_".$realval.".log";
        ?>
         <option value="" onclick="ViewLog('<?php echo $logval; ?>')" > <?php echo $logval; ?></option>

        <?php } ?>
     </select>
     </div>
     </div>
     <br>
      <aside class="lg-side">
        <div class="inbox-body">
         <div class="mail-option">
         </div>
         <table class="table table-inbox table-hover">
          <tbody>
            <?php 

            if(file_exists("logs/".$date)){

              $files = file("logs/".$date);

              if(!empty($files)){

                $newArray = [];
                foreach ($files as $key => $value) {

                  $str = trim(str_replace('-------------------------', '', $value));

                  $newArray[] = $str;

                }

                $fillterArray = array_filter($newArray);
                $arrayLogs =  array_chunk($fillterArray,6);
                
                //echo '<h3>Toatal Log : '. count($value) . '</h3>';

                foreach ($arrayLogs as $kay=> $value) { ?>

                <?php  foreach ($value as $row) { ?>
                <tr>
                  <td class="view-message  dont-show"><span style="position: relative;word-break: break-all;"><?php echo $row; ?><span></td>
                </tr>
                <?php }?>
                <tr  class="unread">
                  <th>
                  </th>
                  <tr  class="unread">
                    <td></td>
                  </tr>
                  <th>
                  </th>
                </tr>

                <?php } } }else{ echo "No logs found.."; }?>

              </tbody>
            </table>
          </div>
        </aside>
      </div>
    </div>

    <script>

     function ViewLog(logdate){
       location.href = 'log_view.php?logDate='+logdate+'';
     }
   </script>
<?php include "footer.php" ?>