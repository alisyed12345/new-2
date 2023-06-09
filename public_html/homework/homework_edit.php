<?php
$mob_title = "Homework";
include "../header.php";




//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!in_array("su_homework_edit", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}

if (check_userrole_by_code('UT01')) {
    $homework = $db->get_row("select * from ss_homework where id='" . $_GET['id'] . "'");
} else {

    $homework = $db->get_row("select * from ss_homework where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND id='" . $_GET['id'] . "' and group_id in (
        SELECT group_id FROM ss_classtime WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
        AND id IN (SELECT classtime_id FROM ss_staffclasstimemap WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
        AND staff_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "'))");
}

if (empty($homework)) {
    include "../includes/unauthorized_msg.php";
    return;
}
?>
<!-- Page header -->
<style>
    .solidsucess {
        border: 1px black;
        background-color: #5bb95b;
        color: white;
        padding: 5px;
        margin-right: -152px;
    }
     .soliderror {
        border: 1px black;
        background-color: red;
        color: white;
        padding: 5px;
        margin-right: -152px;
    }

</style>
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Homework</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Edit Homework</li>
    </ul>
  </div>
</div>



<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
<?php
    if(isset($_SESSION['success']))
    {
    ?>
    <div class="alert alert-success fade in">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <strong>Success!</strong> <?php echo $_SESSION['success']; ?>
    </div>
    <?php
    }
    //unset($_SESSION['success']);
    if(isset($_SESSION['error']))
    {
    ?>
    <div class="alert alert-danger fade in">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
    </div>
    <?php
    }
    //unset($_SESSION['error']);
?>



    <div class="row">
        <div class="col-lg-12">
            <form id="frmICK" action="<?php echo SITEURL ?>ajax/ajss-homework" class="form-validate-jquery" method="post" enctype="multipart/form-data">
                <div class="panel panel-flat panel-flat-box">
                    <div class="panel-body panel-body-box">
                    	<div class="ajaxMsg"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Group Name:</label>
									<?php
                                    if (check_userrole_by_code('UT01')) {
                                        $groups = $db->get_results("select * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                        AND is_active=1 and is_deleted=0 order by group_name asc");
                                    } else {
                                        $groups = $db->get_results("select * from ss_groups where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                        AND is_active=1 and is_deleted=0 and id in (
                                            SELECT group_id FROM ss_classtime WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                            AND id IN (SELECT classtime_id FROM ss_staffclasstimemap WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                            AND staff_user_id = '".$_SESSION['icksumm_uat_login_userid']."')) order by group_name asc");
                                    }
                                    ?>
                                    <select class="form-control" name="group" id="group" required>
                                    <option value="">Select Group</option>
                                    <?php foreach ($groups as $grp) {?>
                                    <option value="<?php echo $grp->id ?>" <?php echo $homework->group_id == $grp->id ? 'selected="selected"' : '' ?>><?php echo $grp->group_name ?></option>
                                    <?php }?>
                                    </select>
                                </div> 
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Class:<span class="mandatory">*</span></label>
                                    <?php
                                    if (check_userrole_by_code('UT01')) {
                                        $classes = $db->get_results("select * from ss_classes where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                        AND is_active=1 order by disp_order");
                                    } else {
                                        $classes = $db->get_results("select * from ss_classes where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                        AND is_active=1 and id in (
                                            SELECT class_id FROM ss_classtime WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                            AND id IN (SELECT classtime_id FROM ss_staffclasstimemap WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                            AND staff_user_id = '".$_SESSION['icksumm_uat_login_userid']."'))");
                                    }
                                    ?>
                                    <select class="form-control" name="subject" id="subject" required>
                                    <option value="">Select Class</option>
                                    <?php foreach($classes as $cla){ ?>
                                    <option value="<?php echo $cla->id ?>" <?php echo $cla->id == $homework->class_id ? 'selected="selected"':'' ?>><?php echo $cla->class_name ?></option>
                                    <?php } ?>
                                    <!-- <option value="whole_group" <?php echo $homework->student_user_id == "" ? 'selected="selected"':'' ?>>ALL STUDENT OF GROUP</option> -->
                                    </select>
                                    <!-- <select class="form-control" name="subject" id="subject" required>
                                    <option value="">Select Subject</option>                                    
                                    </select> -->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group">Homework For:<span class="mandatory">*</span></label>
                                    <?php 
                                    $students = $db->get_results("SELECT * FROM ss_student s INNER JOIN ss_studentgroupmap sm ON s.user_id = sm.student_user_id 
                                    WHERE sm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                                    AND sm.`latest` = 1 AND sm.group_id = '".$homework->group_id."' GROUP BY s.user_id  ORDER BY first_name, last_name");
                                    ?>
                                    <select class="form-control" name="homework_target" id="homework_target" required>
                                    <option value="">Select Homework For</option>
                                    <?php foreach($students as $stu){ ?>
                                    <option value="<?php echo $stu->user_id ?>" <?php echo $stu->user_id == $homework->student_user_id ? 'selected="selected"':'' ?>><?php echo $stu->first_name.' '.$stu->last_name ?></option>
                                    <?php } ?>
                                    <option value="whole_group" <?php echo $homework->student_user_id == "" ? 'selected="selected"':'' ?>>ALL STUDENT OF GROUP</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        
                     <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                                <label for="first_name">Homework Attachment (allowed file pdf, png, jpg, jpeg ) :</label>
                                <input type="file" id="homework_attachment" name="homework_attechment" accept="image/*,.pdf" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                         <div class="form-group">
                        <?php if(!empty($homework->homework_attechment)){ ?>
                            <p id='attachmentfile' style="margin-top: 30px; display: block;">
                            <a href='<?php echo SITEURL ?>homework/attachments/<?php echo $homework->homework_attechment ?>' title='Attachment' class='text-primary' target='_blank'><?php echo $homework->homework_attechment ?></a> 
                            <input type="hidden" name="old_attachment" id="old_attachment" value="<?php echo $homework->homework_attechment ?>">

                            <span><a href="javascript:void(0)" class="text-danger attechment_remove">Remove</a></span>
                            </p>
                            <?php } ?>
                         </div>
                        </div>
                    </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="first_name">Homework:<span class="mandatory">*</span></label>
                                    <textarea placeholder="Enter Homework" style="height:200px;" id="homework_text" name="homework_text" required class="form-control"><?php echo $homework->homework_text ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-10 text-right">
                        	<div class="ajaxMsgBot">
                             <?php
                                    if(isset($_SESSION['success']))
                                    {
                                    ?>
                                        <span class="solidsucess"><?php echo $_SESSION['success']; ?></span> 
                                    <?php
                                    }
                                    unset($_SESSION['success']);
                                    if(isset($_SESSION['error']))
                                    {
                                    ?>

                                     <span class="soliderror"><?php echo $_SESSION['error']; ?></span> 
                                    <?php
                                    }
                                    unset($_SESSION['error']);
                                ?>   
                            </div>
                            </div>
                            <div class="col-md-2 text-right">
                            	<input type="hidden" name="action" value="homework_edit">
                                <input type="hidden" name="homework_id" value="<?php echo $_GET['id'] ?>">
                                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
$( document ).ready(function() {
    $('#homework_text').summernote();

    $('#group').change(function(){
        $('#group_id').val($(this).val()); 

		if($('#group').val() == ''){
			$('#group_class').html("<option value=''>Select</option>");
            $('#student').html('<option value="">Select</option>');
		}else{
            //STUDENT
            $('#homework_target').html('<option value="">Loading...</option>');
            
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
            $.post(targetUrl,{group_id:$('#group').val(),action:'student_of_group'},function(data,status){
                if(status == 'success' && data.code == 1){
                    $('#homework_target').html(data.optionVal + '<option value="whole_group">ALL STUDENT OF SELECTED CLASS</option>');
                }else{
                    $('#homework_target').html('<option value="">Select</option>');
                }
            },'json');

            //GROUP CLASS
			$('#group_class').html("<option value=''>Loading...</option>");

			$.post('<?php echo SITEURL ?>ajax/ajss-classes',{'action':'fetch_group_class_for_select','group_id':$('#group').val()},function(data,status){
					if(status == 'success'){
						$('#group_class').html(data);
					}else{
						$('#group_class').html("<option value=''>Select</option>");
					}
			});
		}
	});

    
    $('.attechment_remove').on('click', function(){
        if(confirm('Are you sure remove this file.')){
          $('#attachmentfile').remove();
          $('#old_attachment').val('');
        } 
    })


var myFile="";
$('#homework_attachment').on('change',function(){

  myFile = $("#homework_attachment").val();
    //console.log(myFile);
  var upld = myFile.split('.').pop();
  if(!(upld =='pdf' || upld =='jpeg' || upld =='jpg' || upld =='png')){
    alert("Only PDF,JPEG,JPG,PNG are allowed");
    $("#homework_attachment").val('');
  }
  
})

/*
	$('#frmICK').submit(function(e){
		e.preventDefault();

		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-homework';
			$('.spinner').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){
				if(status == 'success'){
					if(data.code == 1){
						displayAjaxMsg(data.msg,data.code);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
	});*/
});
</script>
<?php include "../footer.php"?>
