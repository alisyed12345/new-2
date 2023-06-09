<?php 
$mob_title = "Attendance";
include "../header.php";

/* //AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!check_userrole_by_code('UT01') && !check_userrole_by_code('UT02') && !check_userrole_by_code('UT04')){
	include "../includes/unauthorized_msg.php";
	return;
}  */
//AUTHARISATION CHECK 
if (!in_array("su_group_wise_attendence_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
?>

<!-- Page header -->

<style>
/* body{font-size: 14px !important;} */
#attendance_sheet .radio label {
    padding-left: 15px;
}

.rowhide{
  display:none;
}
</style>

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Group Wise Attendance History</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Group Wise Attendance History</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat">
        <div class="panel-body">
          <div class="ajaxMsg"></div>
          <form id="frmGetAttendance" class="form-validate-jquery" method="post">
          <div class="row">
						<div class="col-lg-1 col-xs-4 text-center"><div class="att_pres">Present</div></div>
						<div class="col-lg-1 col-xs-4 text-center"><div class="att_late">Late</div></div>
						<div class="col-lg-1 col-xs-4 text-center"><div class="att_abse">Absent</div></div>
            
            <div class="col-md-4 showmonthdiv" style="float: right; display:none;">
              <div class="form-check-inline">
                <label class="form-check-label" for="showfullmonth">
                  <input type="checkbox" class="form-check-input" id="showfullmonth" name="showfullmonth" value=""> Show Full Month
                </label>
              </div>
            </div>
        </div>

        <br>
            <div class="row">
              <div class="col-md-3 col-xs-6">
              <div class="form-group">
                <?php   
                if(check_userrole_by_code('UT01') || check_userrole_by_code('UT04')){
                  $groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 
                  AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' order by group_name asc"); 
                }else{
                //$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0 and id in (select group_id from ss_staffgroupmap where staff_user_id='".$_SESSION['icksumm_uat_login_userid']."' and active = 1)"); 

                //REPLACE date('w') WITH 0 FOR TESTING
                $groups = $db->get_results("select DISTINCT g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id 
                WHERE  g.is_active=1 and g.is_deleted=0 and d.id in (select classtime_id from ss_staffclasstimemap 
                where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' 
                and active=1) order by group_name asc");
              }
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
                <select class="select2 form-control" name="group_class" id="group_class" required>
                  <option value="">Select Class</option>
                </select>
                </div>
              </div>
              <div class="col-md-3 col-xs-6">
              <div class="form-group">
                <select class="select2 form-control" id="period" name="period" required>
                  <option value="">Select Month-Year</option>
                  <!-- <?php for ($i = 0; $i < 6; $i++){ ?>
                  <option value="<?php echo date('m-Y', strtotime("-$i month")) ?>" data-curmonth="<?php echo $i?0:1 ?>"><?php echo date('M-Y', strtotime("-$i month")) ?></option>
                  <?php }	?> -->

                  <?php 
                  $current_session = $db->get_row("select * from ss_school_sessions where id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");
                  $cur_ses_start_month = date('m',strtotime($current_session->start_date));
                  $cur_ses_start_year = date('Y',strtotime($current_session->start_date));
                //  $cur_ses_end_date = $current_session->end_date;
                  $cur_ses_end_date = date('Y-m-d');

                  for($i = date('Y-m-d',mktime(0,0,0,$cur_ses_start_month,1,$cur_ses_start_year)); $i < $cur_ses_end_date ; $i = date('Y-m-d', strtotime("+1 months", strtotime($i)))){
                    echo '<option value="'.date('m',strtotime($i)).'-'.date('Y',strtotime($i)).'" data-curmonth="'.(date('m',strtotime($i))==date('m') && date('Y',strtotime($i))==date('Y')?'1':'0').'">'.date('M',strtotime($i)).'-'.date('Y',strtotime($i));
                  }
                  ?>

                </select>
                </div>
              </div>
           <!-- <div class="col-lg-3">
              <div class="form-group">
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="full_month" id="full_month" value="1">
                    Show Full Month </label>
                </div>
                </div>
              </div> -->
              <div class="col-md-3 col-xs-6"><div class="form-group">
                <input type="hidden" name="action" value="get_attendance_of_group">
                <button type="submit" id="getattend" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Get Attendance</button>
                </div>
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
              <div class="col-md-10 text-right">
                <div class="ajaxMsgBot"></div>
              </div>
              <div class="col-md-2 text-right">
              	<?php if(check_userrole_by_code('UT01')){ ?>
                <input type="hidden" name="action" value="save_attendance">
                <input type="hidden" name="group_id" id="group_id" value="">
                <input type="hidden" name="class_id" id="class_id" value="">
                <button type="submit" id="btnSubmit" class="btn btn-success hide"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="save_spinner"></i> Submit</button>
                <?php } ?>
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
  $('.showmonthdiv').hide();
	/*$('#group').change(function(){
		$('#group_id').val($(this).val()); 
	});*/

  
  $(document).on('click','#showfullmonth',function(){ 

    if($("#showfullmonth").is(':checked')){
      $("#getattend").click()
    // $('.rowheide').show();
    }else{
     // $('.rowhide').hide();
     $("#getattend").click();
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
	
  $('#group').change(function(){
		if($('#group').val() == ''){
			$('#group_class').html("<option value=''>Select</option>");
		}else{
			$('#group_class').html("<option value=''>Loading...</option>");

			$.post('<?php echo SITEURL ?>ajax/ajss-classes',{'action':'fetch_group_class_for_select_history','group_id':$('#group').val()},function(data,status){
					if(status == 'success'){
						$('#group_class').html(data);
					}else{
						$('#group_class').html("<option value=''>Select</option>");
					}
			});
		}
	});

	// $('#period').change(function(){
	// 	if($("#period option:selected").data('curmonth') == '1' || $("#period option:selected").val() == ''){ 
	// 		$('#full_month').prop( "checked", false);
	// 		$('#full_month').parent().show(); 
	// 	}else{
	// 		$('#full_month').prop( "checked", true ); 
	// 		$('#full_month').parent().hide();
	// 	}
	// });
		
    $('#frmGetAttendance').submit(function(e){
var selected_month=$('#period').val();

    $('#showfullmonth').val(selected_month);
    
      e.preventDefault();
		 $('.showmonthdiv').hide();

      if($('#frmGetAttendance').valid()){
        $('#group_id').val($('#group').val()); 
        $('#class_id').val($('#group_class').val()); 

        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance';
        $('#get_spinner').removeClass('hide');
        
        var formDate = $(this).serialize();
        $.post(targetUrl,formDate,function(data,status){					
          if(status == 'success'){
            $('.attendance_cont').html(data.sheet);
             $.get(targetUrl,{action:'get_working_days'},function(res,st){  
              // if(res<7){
              $('.showmonthdiv').show();
              // }
              })
            $('.atten_check').each(function(index, element) {
              /*if($(element).is(':checked')){
                $(element).parent().addClass('sel_atten_day');
              }*/

              if($(element).is(':checked') && $(element).val() == '1'){
                $(element).parent().parent().parent().addClass('att_pres');
              }else if($(element).is(':checked') && $(element).val() == '2'){
                $(element).parent().parent().parent().addClass('att_late');
              }else if($(element).is(':checked') && $(element).val() == '0'){
                $(element).parent().parent().parent().addClass('att_abse');
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
	
	$(document).on('change','.atten_check',function(data,status){
    $(this).parent().parent().parent().find('.atten_hid').val($(this).val());

    $(this).parent().parent().parent().removeClass('att_abse').removeClass('att_pres').removeClass('att_late');

    if($(this).val() == 0){
      $(this).parent().parent().parent().addClass('att_abse');
    }else if($(this).val() == 1){
      $(this).parent().parent().parent().addClass('att_pres');
    }else if($(this).val() == 2){
      $(this).parent().parent().parent().addClass('att_late');
    }
		/*if($(this).is(':checked')){
			$(this).parent().find('.atten_hid').val(1);
			$(this).parent().addClass('sel_atten_day');
		}else{
			$(this).parent().find('.atten_hid').val(0);
			$(this).parent().removeClass('sel_atten_day');
		}*/
	});
});
</script>
<?php include "../footer.php"?>
