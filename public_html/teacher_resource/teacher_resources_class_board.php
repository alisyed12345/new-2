<?php 
$mob_title = "Teacher Resources Class Board";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!check_userrole_by_code('UT03') && !check_userrole_by_code('UT05')){
	include "../includes/unauthorized_msg.php";
	return;
} 



$students = $db->get_results("select * from ss_student s inner join ss_user u on s.user_id = u.id INNER JOIN ss_student_session_map ssm
on ssm.student_user_id = u.id where u.is_active = 1 
AND ssm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and s.family_id = '" . $_SESSION['ss_login_familyid'] . "'");
            
	 
?>
<!-- Page header -->

<style type="text/css">
	.h6{
	    margin-top: 23px;
	}
</style>

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Teacher Resources Class Board</h4>
    </div> 
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo $SITEURL."parents/dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Teacher Resources Class Board</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content content-box">
    <div class="panel panel-flat panel-flat-box">
      <div class="panel-body panel-body-box">
        <?php 
        foreach ($students as $stu) {
          $stu_group = $db->get_row("SELECT g.id AS group_id, g.group_name FROM ss_studentgroupmap m INNER JOIN ss_groups g ON m.group_id = g.id 
          WHERE m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and m.latest = 1 AND student_user_id = " . $stu->user_id . " ORDER BY m.id LIMIT 1");
          $students_group[] = $stu_group->group_id;
  
          $group_info = $db->get_row("SELECT s.* FROM ss_staffgroupmap m INNER JOIN ss_staff s ON m.staff_user_id = s.user_id 
          WHERE m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and m.active = 1 AND m.group_id = '" . $stu_group->group_id . "' ORDER BY m.id LIMIT 1");
  
          $get_class_board = $db->get_results("SELECT cb.id, g.group_name, cb.group_id, cb.title, cb.message, 
          (CASE WHEN cb.status=1 THEN 'Active' ELSE 'Inactive' END) AS STATUS, cb.created_on FROM ss_class_common_board cb 
          INNER JOIN ss_groups g ON g.id = cb.group_id  WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and cb.status=1 and cb.group_id = '".$stu_group->group_id."' order by cb.id desc");
  
        foreach($get_class_board as $row){?>
          <div class="row">
        	<div class="col-md-6 text-left">
        		<h3 style="margin-top: 0;"><?php echo $row->title ?> - <?php echo $stu_group->group_name ?></h3>
        	</div>
          <div class="col-md-6 text-right">
            <h6 class="h6" style="margin-top: 5px;">Posted On: <?php echo date('m/d/Y h:i A', strtotime($row->created_on)); ?></h6>
          </div>
        </div>
        <div class="row">
         <?php 
          $get_class_board_attach = $db->get_results("SELECT attachment_file_path FROM ss_class_common_board_attach Where class_common_board_id = '".$row->id."'");
          $attachemts = '';
          foreach ($get_class_board_attach as $key =>$value) {
            $attachemts .= '<div class="col-md-1" style="margin-left: 15px;"><a href="'.$value->attachment_file_path.'" target="_blank"> Attachment '.($key+1).'</a></div>';
           }
        ?>
          <div class="col-md-12">
          <blockquote><?php echo $row->message ?></blockquote>
          <?php echo $attachemts; ?>
          </div>
        </div>
     
        <hr>
      <?php } ?>
      <?php } ?>
      </div>
    </div>
</div>
<!-- /Content area --> 

<?php include "../footer.php" ?>
