<?php 
$mob_title = "Edit Student";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN

if(!in_array("su_student_edit", $_SESSION['login_user_permissions'])){
  include "../includes/unauthorized_msg.php";
 exit;
}


$group_id = $db->get_var("select group_id from ss_studentgroupmap where student_user_id='".$_GET['id']."' order by id desc limit 1");
$user_types = $db->get_results("SELECT fees_discount_id  FROM ss_student_feesdiscounts where student_user_id = '".$_GET['id']."'");

$students = $db->get_row("select * from ss_student s where s.user_id= '" . $_GET['id']. "'");


// echo "SELECT fees_discount_id  FROM ss_student_feesdiscounts where student_user_id = '".$_GET['id']."'";
// die;
$user_types_ary = array();
foreach($user_types as $ut){
    $user_types_ary[] = $ut->fees_discount_id;
}
//GROUP LEVEL CHECK, SHEIKH CAN VIEW DETAILS OF HIS STUDENTS ONLY
/*if(check_userrole_by_code('UT02')){
  if(!in_array($group_id,$_SESSION['icksumm_uat_assigned_groups'])){
    include "../includes/unauthorized_msg.php";
    return;
  }
}*/ 

$user = $db->get_row("select * from ss_user where id='".$_GET['id']."'");
$stu = $db->get_row("select * from ss_student where user_id='".$user->id."'");


if(verifyDate($stu->admission_date)){
  $admission_date =my_date_changer($stu->admission_date,'c') ;
  
}

if(verifyDate($stu->dob)){
  $dob =my_date_changer($stu->dob,'c');
}

if($user->is_deleted == 1){
  $status = "delete_soft";
}elseif($user->is_active == 1){
  $status = "active";
}elseif($user->is_active == 2){
    $status = "hold";
}elseif($user->is_active == 0){
  $status = "inactive";
}

$stugroupclass = $db->get_results("select g.id AS group_id, s.id AS class_id, g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id 
inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='".$_GET['id']."' and g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
and s.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");

?>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Edit Student</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li><a href="<?php echo SITEURL."student/students_list" ?>">Student</a></li>
            <li class="active">Edit Student</li>
        </ul>

    </div>
    <!-- <div class="above-content">
    <a href="javascript:history.go(-1)" class="last_page">Go Back To Last Page</a>
  </div> -->
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <form id="frmICK" class="form-validate-jquery" method="post">
                <div class="panel panel-flat">
                    <div class="panel-body">
                    <?php if(empty($user)){ ?>
                    <div class="alert alert-danger">Student information not found</div>
                    <?php } ?>
                    <div class="ajaxMsg"></div>
                        <legend class="text-semibold"><i class="icon-user position-left"></i> Personal Information</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Admission Number:<span class="mandatory">*</span></label>
                                    <input placeholder="Admission Number" readonly  name="admission_no" id="admission_no" value="<?php echo $stu->user_id ?>" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Admission Date:<span class="mandatory">*</span></label>
                                    <input placeholder="Admission Date" required name="admission_date" id="admission_date" value="<?php  echo $admission_date ?>" class="form-control" type="text" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Grade:<span class="mandatory">*</span></label>
                              <?php $grades = array('KG', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade', '6th Grade or higher');?>
                                <select name="child_grade" class="form-control required" tabindex="50">
                                    <option value="">Select Grade</option>
                                    <?php foreach ($grades as $garde) { ?>
                                    <option value="<?= $garde ?>" <?php if($garde == $stu->school_grade){ echo "selected"; } ?> ><?= $garde ?></option>
                                    <?php } ?>
                                </select>
                               </div>
                            </div>
                        
                        </div>
                   
                        <div class="row">
                             <div class="col-md-2">
                                <label><strong>Class</strong></label>
                             </div>
                             <div class="col-md-2">
                                <label></label><strong>Group</strong><span class="mandatory">*</span></label>
                             </div>
                         </div>
                         <br>
                         
                        
                          <?php 
                           $get_general_info = $db->get_var("select one_student_one_lavel from ss_client_settings where status = 1");
                           if ($get_general_info == 0) {
                            $groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 
                            and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
                           }else{
                            $groups = $db->get_results("select * from ss_groups where is_active=1  and is_deleted=0 
                            and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ORDER BY id ASC LIMIT 1");
                           }
                           //$check_group_and_class = $db->get_results("SELECT * FROM ss_studentgroupmap WHERE student_user_id = '".trim($_GET['id'])."' AND latest = 1 AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
                          
                          // $classes = $db->get_results("select c.id, c.class_name from ss_classes c inner join ss_studentgroupmap m on m.class_id = c.id where c.is_active= 1 and c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and  m.student_user_id = '".trim($_GET['id'])."' AND m.latest = 1 AND m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
                          //  order by id desc");
                            $classes = $db->get_results("select c.id, c.class_name from ss_classes c where c.is_active= 1 and c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'  order by c.disp_order ");
                               foreach ($classes as $key => $class) { ?>
                            <div class="row">
                             <div class="col-md-2">
                                <div class="form-group">
                                  <input type="hidden" name="class[]" value="<?php echo $class->id ?>">
                                    <span><?php echo $class->class_name; ?></span>
                                </div>
                              </div>

                              <div class="col-md-2">
                                <div class="form-group multi-select-full">
                                  <select id="group_id<?php echo $class->id ?>" name="group_id<?php echo $class->id ?>" class="select form-control required" required>
                                      <option value="" selected="">-- Select Group--</option>

                                    <?php
                                    foreach ($groups as $grp) {
                                      
                                        // if ($get_general_info == 1) {
                                        //  $group_id = $db->get_var("select  group_id from ss_studentgroupmap where latest=1 and student_user_id='".$_GET['id']."' and group_id='". $grp->id."' and class_id='".$class->id."' ");
                                        // }else{
                                        /*COMMENTED ON 21AUG2021
                                        $group_id = $db->get_var("select  group_id from ss_studentgroupmap where latest=1 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'
                                        and student_user_id='".$_GET['id']."' and group_id='". $grp->id."'"); */
                                        //ADDED ON 21AUG2021
                                        $group_id = $db->get_var("select  group_id from ss_studentgroupmap where latest=1 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                          and student_user_id='".$_GET['id']."' and group_id='". $grp->id."' and class_id='".$class->id."'");
                                        // }?>
                                    <option value="<?php echo $grp->id ?>" <?php if (!empty($group_id)) {
                                            echo "selected";
                                        } ?> ><?php echo $grp->group_name  ?></option>
                                    <?php
                                    } ?>
                                  </select>
                                </div>
                              </div>
                         </div>
                          <?php }?>
                          <br>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>First Name:<span class="mandatory">*</span></label>
                                    <input placeholder="First Name" spacenotallow="true"  required lettersonly="true" name="first_name" value="<?php echo $stu->first_name ?>" class="form-control" type="text">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Last Name:<span class="mandatory">*</span></label>
                                    <input placeholder="Last Name" spacenotallow="true" required lettersonly="true" value="<?php echo $stu->last_name ?>" name="last_name" class="form-control" type="text">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date of Birth: <span class="mandatory">*</span></label>
                                    <input placeholder="Date of Birth" name="dob" id="dob" value="<?php
                                    echo $dob ?>" class="form-control required" type="text">
                                </div>
                            </div>
                        </div>
                       
                        <!-- <div class="row">
                          <div class="col-md-4">
                                <div class="form-group">
                                    <label>Username:<span class="mandatory">*</span></label>
                                    <input placeholder="Username" name="username" readonly id="username" class="form-control" type="text" value="<?php echo $user->username ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Password:</label>
                                    <input placeholder="Password" name="password" id="password" class="form-control" type="text">
                                    <div class="help-block">NOTE: If you do not want to change password, leave it blank</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Confirm Password:</label>
                                    <input placeholder="Confirm Password" equalTo="#password" name="confirm_password" id="confirm_password" class="form-control" type="text">
                                </div>
                            </div>
                            
                        </div> -->
                        <div class="row">
                            <div class="col-md-4">
                            <label>Allergies: <span class="mandatory">*</span></label>
                          <input type="text" name="child_allergies" id="child_allergies" maxlength="50" tabindex="60" placeholder="Enter Allergies" value="<?php echo $stu->allergies ?>" class="form-control required">
                        </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gender:<span class="mandatory">*</span></label>
                                    <div class="col-md-12">
                                        <label class="radio-inline">
                                            <input type="radio" required id="gender_m" name="gender" <?php echo $stu->gender == "m"?'checked="checked"':'' ?> value="m"> Male
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" id="gender_f" name="gender" <?php echo $stu->gender == "f"?'checked="checked"':'' ?> value="f"> Female
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                            <div class="form-group">
                                    <label>Status: <span class="mandatory">*</span></label>
                                    <select name="status" id="status" required class="select form-control required">
                                        <option value="">Select</option>
                                        <option value="active" <?php echo $status == "active"?'selected="selected"':'' ?>>Active</option>
                                        <!-- <option value="hold" <?php echo $status == "hold"?'selected="selected"':'' ?>>Hold</option> -->
                                        <option value="inactive" <?php echo $status == "inactive"?'selected="selected"':'' ?>>Inactive</option>
                                        <option value="delete_soft" <?php echo $status == "delete_soft"?'selected="selected"':'' ?>>Delete</option>
                                    </select>
                                </div>
                            </div>
                      <?php 

                        if(in_array("su_family_info", $_SESSION['login_user_permissions'])){ ?>
                           <?php $discounts = $db->get_results("select * from ss_fees_discounts where status=1 and session ='".$_SESSION['icksumm_uat_CURRENT_SESSION']."'"); ?>
                             <div class="col-md-6" style="<?php if($_GET['page'] == 'family_info'){ echo 'display:block';  }else{ echo 'display:none';  } ?>" >
                              <div class="form-group multi-select-full">
                                <label>Discount Fees: </label>
                                <select id="fees_discount_id"  class="bootstrap-select" name="fees_discount_id[]"  data-width="100%" multiple='multiple' >
                                    <option value="">Select Discount</option>
                                  <?php foreach($discounts as $discount){  if ($discount->discount_unit == 'd') {
                                         $doller = '$';
                                         $percent = '';
                                        }elseif ($discount->discount_unit == 'p') {
                                          $percent = '%';
                                          $doller = '';
                                        }?>
                                  <option value="<?php echo $discount->id ?>" <?php echo in_array($discount->id, $user_types_ary)?'selected="selected"':'' ?>><?php echo  $discount->discount_name.' ( '. $doller.''.($discount->discount_percent + 0).''. $percent.' ) '; ?></option>
                                  <?php } ?>
                                </select>
                              </div> 
                            </div>
                         <?php } ?> 
                        </div> 
                        <!-- <br />
                        <legend class="text-semibold"><i class="icon-envelop position-left"></i>Contact Information</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input placeholder="Email" class="form-control" name="email" value="<?php echo $user->email ?>" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Telephone (xxx-xxx-xxxx):</label>
                                    <input placeholder="Telephone XXX-XXX-XXXX" class="form-control" phonenocheck="true" id="phone" name="phone" value="<?php echo $stu->phone ?>" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mobile:</label>
                                    <input placeholder="Mobile" class="form-control" id="mobile" phonenocheck="true" name="mobile" value="<?php echo $stu->mobile ?>" type="text">
                                </div>
                            </div>
                        </div>
                        <br /> -->
                       <!--  <legend class="text-semibold"><i class="icon-camera position-left"></i>Photo</legend>
                        <div class="row">
                             -->
                            <!--<div class="col-md-1">
                                <div class="form-group">
<img src="../assets/images/demo/users/face5.jpg" style="width:50px" class="img-circle" />
                                </div>
                            </div>-->
                        <!-- <div class="col-md-4">
                                <div class="form-group">
                                    <label>Photo:</label>
                                    <input type="file">
                                </div>
                            </div>
                        </div> -->
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                                  <label>Comments:</label>
                                  <textarea type="text" name="comments" class="form-control"><?php echo $stu->comments ?></textarea>
                            </div>
                          </div>
                          </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10 text-right">
                              <div class="ajaxMsgBot"></div>
                            </div>
                            <div class="col-md-2 text-right">
                              <?php if(!empty($user)){ ?>
                              <input type="hidden" name="action" value="edit_student">
                                <input type="hidden" name="user_id" value="<?php echo $_GET['id'] ?>">
                                <button type="submit" class="btn btn-success" style="margin-right:30px;margin-bottom:30px;"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Content area -->
<script>

$(document).ready(function() {
    $('#phone').mask('000-000-0000');
    $('#mobile').mask('000-000-0000');

  $('#admission_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: true,
    max: [<?php echo date('Y') ?>,<?php echo date('m') ?>,<?php echo date('d') ?>],
    format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
    formatSubmit: 'yyyy-mm-dd'
    });

  $('#dob').pickadate({
    labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: 100,
        min: [ <?php echo date('Y') - 100 ?> , 01, 01],
        max: [<?php echo date('Y') - 4 ?>, 12, 31],
        format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
        
        formatSubmit: 'yyyy-mm-dd'
    });

    //VALIDATION - US PHONE FORMAT 
    jQuery.validator.addMethod("phonenocheck", function(value, element) {
          return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);  
    }, "Enter valid phone number");


  $(document).on('change','#status', function() {
    <?php
    $family_schedule_payment = get_family_schedule_payment($students->family_id);
    $schedule_payment_cron = get_schedule_payment_cron($students->family_id);
    $obj_array = payment_confirmation_check((array)$family_schedule_payment,(array)$schedule_payment_cron);
    ?>

    var confirm_check_con = "<?php echo $obj_array->confirm_check_con ?>";

    if(confirm_check_con == 1){
      var confirm_msg = "<?php echo $obj_array->confirm_msg ?>";
      $.confirm({
        title: '! ALERT ',
        content: confirm_msg,
      });

    }

  });

    
  $('#frmICK').submit(function(e){
    e.preventDefault();

    if($('#frmICK').valid()){
      var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
      $('.spinner').removeClass('hide');
      
      var formDate = $(this).serialize();
      $.post(targetUrl,formDate,function(data,status){          
        if(status == 'success'){
          if(data.code == 1){
            //FOR HARD DELETE
            // if($('#status').val() == 'delete_soft'){
            //   window.location = '<?php // echo SITEURL ?>student/students_list';
            // }
            displayAjaxMsg(data.msg,data.code);
          }else{
            displayAjaxMsg(data.msg,data.code);
          }
        }else{
          displayAjaxMsg(data.msg);
        }
      },'json');
    }
  });
   //$('#pre-selected-options').multiSelect();


});
</script>
<?php include "../footer.php" ?>