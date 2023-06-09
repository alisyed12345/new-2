<?php $mob_title = "Attendance"; ?>
<?php include "../header.php";

//AUTHARISATION CHECK - UT05 MEANS PARENTS
if(!check_userrole_by_code('UT05')){
	include "../includes/unauthorized_msg.php";
	return;
} 
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Attendance</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Attendance</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content content-box">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat panel-flat-box">
        <div class="panel-body panel-body-box">
          <div class="ajaxMsg"></div>
          <form id="frmGetAttendance" class="form-validate-jquery" method="post">
            <div class="row">


              <?php /*?><div class="col-lg-3">
                <?php 
				$students = $db->get_results("select * from ss_student where family_id='".$_SESSION['icksumm_uat_login_familyid']."'"); 
			  ?>
                <select class="select form-control" name="student" id="student" required>
                  <option value="">Select Student</option>
                  <?php foreach($students as $stud){ ?>
                  <option value="<?php echo $stud->id ?>"><?php echo $stud->first_name.' '.trim($stud->middle_name.' '.$stud->last_name) ?></option>
                  <?php } ?>
                </select>
              </div><?php */?>


            <div class="col-md-3 col-xs-6">
              <div class="form-group">
                <label>Group: <span class="mandatory">*</span></label>
                <?php   
                   $groups =  $db->get_results("SELECT DISTINCT g.id, g.group_name FROM ss_groups g INNER JOIN ss_studentgroupmap m ON g.id = m.group_id 
                   INNER JOIN ss_student_session_map ss ON ss.session_id = m.session 
                   WHERE m.latest = 1 
                   AND m.student_user_id IN ( SELECT s.user_id FROM ss_student s INNER JOIN ss_user u ON s.user_id = u.id 
                   WHERE u.is_active = 1 AND u.is_deleted = 0 
                   AND u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND s.family_id = '" .$_SESSION['icksumm_uat_login_familyid']. "' ) 
                   AND ss.session_id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                   AND m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");    

                  //  echo "select DISTINCT g.id, g.group_name from ss_groups g 
                  //  inner join ss_studentgroupmap m on g.id = m.group_id where m.latest = 1 
                  //  and m.student_user_id IN ( select s.user_id from ss_student s inner join ss_user u on s.user_id = u.id 
                  //  INNER JOIN ss_student_session_map ss ON ss.session_id = m.session
                  //  where u.is_active = 1 AND u.is_deleted = 0 
                  //  AND u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and s.family_id = '" .$_SESSION['icksumm_uat_login_familyid']. "' ) where 
                  //  ss.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                  //  AND m.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ";
                ?>
                <select class="select2 form-control" name="group" id="group" required>
                  <option value="">Select Group</option>
                  <?php foreach($groups as $grp){ ?>
                  <option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
                  <?php } ?>
                </select>
                </div>
              </div>

              <div class="col-md-3 col-xs-6">
              <div class="form-group">
                 <label>Class: <span class="mandatory">*</span></label>
                 <select class="select2 form-control" name="group_class" id="group_class" required>
                  <option value="">Select Class</option>
                </select>
                </div>
              </div>

              <div class="col-lg-3 col-xs-6">
                <label>Month: <span class="mandatory">*</span></label>
                <select class="select2 form-control" id="period" name="period" required>
                  <option value="">Select Month</option>
                  <?php // for ($i = 0; $i < 6; $i++){ ?>
                  <!-- <option value="<?php echo date('m-Y', strtotime("-$i month")) ?>" data-curmonth="<?php echo $i?0:1 ?>"><?php echo date('M-Y', strtotime("-$i month")) ?></option> -->
                  <?php // }	?>

                  <?php 
                  $current_session = $db->get_row("select * from ss_school_sessions where id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
                  $cur_ses_start_month = date('m',strtotime($current_session->start_date));
                  $cur_ses_start_year = date('Y',strtotime($current_session->start_date));
                  $cur_ses_end_date = $current_session->end_date;
                  $end_date=date('Y-m-d');

                  for($i = date('Y-m-d',mktime(0,0,0,$cur_ses_start_month,1,$cur_ses_start_year)); 
                  $i <= $end_date ; 
                  $i = date('Y-m-d', strtotime("+1 months", strtotime($i)))){
                    echo '<option value="'.date('m',strtotime($i)).'-'.date('Y',strtotime($i)).'" data-curmonth="'.(date('m',strtotime($i))==date('m') && date('Y',strtotime($i))==date('Y')?'1':'0').'">'.date('M',strtotime($i)).'-'.date('Y',strtotime($i));
                  }
                  ?>
                </select>
              </div>
              <div class="col-lg-3 col-xs-6">
                <input type="hidden" name="action" value="get_attendance_of_family">
                <button type="submit" class="btn btn-success mt-30"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Show</button>
              </div>
            </div>
          </form>
          <br>
          <form id="frmAttendanceSheet" class="form-validate-jquery" method="post">
            <div class="row">
              <div class="col-lg-12">
                <div class="attendance_cont"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-10 col-xs-9 text-right">
                <div class="ajaxMsgBot"></div>
              </div>
              <div class="col-md-2 col-xs-3 text-right">
                <input type="hidden" name="action" value="save_attendance">
                <input type="hidden" name="student_id" id="student_id" value="">                
              </div>
            </div>
          </form>
          <br>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Add Modal - Student Detail-->
<div id="modal_student_detail" class="modal fade">
<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h5 class="modal-title">Student Detail <span id="studentinfo_title"></span></h5>
    </div>
    <div class="modal-body viewonly" id="student_detail"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
</div>
<!-- /Add modal -->
<script>
$( document ).ready(function() {
	$('#student').change(function(){
		$('#student_id').val($(this).val()); 
	});



  $('#group').change(function(){
    if($('#group').val() == ''){
      $('#group_class').html("<option value=''>Select</option>");
    }else{
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
	
	//FETCH STUDENT DETAILS
	$(document).on('click','.viewdetail',function(){ 
		var userid = $(this).data('userid');
		var studentname = $(this).data('studentname');
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';

		$('#studentinfo_title').html(' - ' + studentname );
		$('#student_detail').html('<h5>Data loading... Please wait</h5>');
		$('#modal_student_detail').modal('show');
				
		$.post(targetUrl,{userid:userid,action:'view_student_detail'},function(data,status){
			if(status == 'success'){
				$('#student_detail').html(data);
			}
		});
	});
		
    $('#frmGetAttendance').submit(function(e){
        e.preventDefault();

		if($('#frmGetAttendance').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance';
			$('#get_spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					$('.attendance_cont').html(data.sheet);
					
					$('.atten_check').each(function(index, element) {
                    	if($(element).is(':checked')){
							$(element).parent().addClass('sel_atten_day');
						}    
                    });
					
					if(data.data_found){
						$('#btnSubmit').removeClass('hide');
					}else{
						$('#btnSubmit').addClass('hide');
					}
				}else{
					$('.attendance_cont').html('Error: Process failed');
				}
				
				$('#get_spinner').addClass('hide');
			},'json');
		}
    });
	
	$('#frmAttendanceSheet').submit(function(e){
        e.preventDefault();
		
		if($('#frmAttendanceSheet').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance';
			$('#save_spinner').removeClass('hide');
			
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
				
				$('#save_spinner').addClass('hide');
			},'json');
		}
    });
});
</script>
<?php include "../footer.php"?>
