<?php 
$mob_title = "Add Payment";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!check_userrole_by_code('UT01') && !check_userrole_by_code('UT04')){
	include "../includes/unauthorized_msg.php";
	return;
} 
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Add Payment</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Add Payment</li>
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
          <form id="frmICK" class="form-validate-jquery" method="post">
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                <?php $students = $db->get_results("SELECT s.user_id, 
				concat(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name) as student_name FROM ss_student s INNER JOIN 
				ss_user u ON s.user_id = u.id INNER JOIN ss_school_sessions ss ON ss.id = u.session_id WHERE u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and u.is_active = 1 AND u.is_deleted = 0"); ?>
                  <label>Student:<span class="mandatory">*</span></label>
                  <select class="select-minimum form-control" id="student" name="student" required>
                    <option value="">Student</option>
                    <?php foreach($students as $stu){
					$group = $db->get_var("SELECT g.group_name FROM ss_studentgroupmap m INNER JOIN ss_groups g on m.group_id=g.id 
					WHERE latest=1 and m.student_user_id = '".$stu->user_id."'"); ?>
                    <option value="<?php echo $stu->user_id ?>"><?php echo $stu->student_name.' - '.$group ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-2">
                <div class="form-group">
                  <label>From:<span class="mandatory">*</span></label>
                  <select class="select form-control" id="start_month" name="start_month" required>
                    <option value="">Select Month-Year</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-2 hide">
                <div class="form-group">
                  <label>To:</label>
                  <select class="select form-control" id="end_month" name="end_month">
                    <option value="">Select Month-Year</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-2">
                <div class="form-group">
                  <label>Amount:</label>
                  <input type="text" name="amount" required id="amount" class="form-control number" />
                </div>
              </div>
            </div>
            <div class="row">              
              <div class="col-lg-3">
                <div class="form-group">
                  <label>Receipt Number:</label>
                  <input type="text" name="receipt_no" required id="receipt_no" class="form-control" />
                </div>
              </div>
              <div class="col-lg-7">
                <div class="form-group">
                  <label>Remarks:</label>
                  <input type="text" name="remarks" id="remarks" class="form-control" />
                </div>
              </div>
              <div class="col-lg-2">
                <div class="form-group"> <br />
                  <input type="hidden" name="action" value="submit_fees">
                  <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                </div>
              </div>
            </div>
          </form>
          <br>
          <legend class="text-semibold"><i class="icon-coin-dollar position-left"></i> Previous Entries</legend>
          <div class="table-responsive">
          <table class="table table-condensed table-bordered">
            <tr>
              <th>Month</th>
              <th>Amount</th>
              <th>Receipt No</th>
              <th>Paid On</th>
              <th>Remarks</th>
              <th></th>
            </tr>
            <tbody id="history"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$( document ).ready(function() {
	/*$('#group').change(function(){
		$('#student').html('<option value="">Select</option>');
		$('#start_month').html('<option value="">Select</option>');
		$('#end_month').html('<option value="">Select</option>');
		
		var groupid = $('#group').val();
		if(groupid != ''){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-fees.php';
			$('#student').html('<option value="">Loading...</option>');
			
			$.post(targetUrl,{'action':'get_group_student',groupid:groupid},function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						$('#student').html(data.option);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}  
			},'json');
		}else{
			$('#student').html('<option value="">Select</option>');
		}
	});*/
	
	$(document).on('click','.delete_fees',function(data,status){
		if(confirm('Do you want to delete payment?')){
			$('.spinner').removeClass('hide');
			
			var feesid = $(this).data('feesid');
						
			$.post('<?php echo SITEURL ?>ajax/ajss-fees',{feesid:feesid,action:'delete_fees'},function(data,status){
				if(status == 'success'){
					$('#student').trigger('change');
					displayAjaxMsg(data.msg,data.code);
				}else{
					displayAjaxMsg(data.msg,data.code);
				}
			},'json');
		}
	});
	
	$('#start_month').change(function(){
		$end_month = '<option value="">Select</option>';
		
		if($(this).val() != ''){
			$('#start_month  > option:selected').each(function() { 
				$order = $(this).data('order');
			});
			
			$('#start_month > option').each(function() {
				if($(this).data('order') > $order){
					$end_month = $end_month + '<option value="' + $(this).attr('value') + '">' + $(this).html() + '</option>';
				}
			});
		}
		
		$('#end_month').html($end_month);
	});
	
	$('#student').change(function(){
		$('#start_month').html('<option value="">Select</option>');
		$('#end_month').html('<option value="">Select</option>');
		
		var student_user_id = $('#student').val();
		if(student_user_id != ''){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-fees';
			$('#start_month').html('<option value="">Loading...</option>');
			
			$.post(targetUrl,{'action':'get_payable_month',student_user_id:student_user_id},function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						$('#start_month').html(data.option);
						$('#history').html(data.history);
					}else{
						$('#start_month').html('<option value="">Select Month-Year</option>');
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					$('#start_month').html('<option value="">Select Month-Year</option>');
					displayAjaxMsg(data.msg);
				}  
			},'json');
		}else{
			$('#start_month').html('<option value="">Select Month-Year</option>');
		}
	});
	
	$('#frmICK').submit(function(e){
        e.preventDefault();
		
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-fees';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){
						$('#history').html(data.history);
						$('#start_month').val('').trigger('change');
						$('#amount').val('');
						$('#receipt_no').val('');
						$('#remarks').val('');
						displayAjaxMsg(data.msg,data.code);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg('Error: Process failed');
				}
			},'json');
		}
	});
	
	/*//FETCH STUDENT DETAILS
	$(document).on('click','.viewdetail',function(){ 
		var userid = $(this).data('userid');
		var studentname = $(this).data('studentname');
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student.php';

		$('#studentinfo_title').html(' - ' + studentname );
		$('#student_detail').html('<h5>Data loading... Please wait</h5>');
		$('#modal_student_detail').modal('show');
				
		$.post(targetUrl,{userid:userid,action:'view_student_detail'},function(data,status){
			if(status == 'success'){
				$('#student_detail').html(data);
			}
		});
	});
	
	$('#period').change(function(){
		if($("#period option:selected").data('curmonth') == '1' || $("#period option:selected").val() == ''){ 
			$('#full_month').prop( "checked", false);
			$('#full_month').parent().show(); 
		}else{
			$('#full_month').prop( "checked", true ); 
			$('#full_month').parent().hide();
		}
	});
		
    $('#frmGetAttendance').submit(function(e){
        e.preventDefault();
		
		if($('#frmGetAttendance').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance.php';
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
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-attendance.php';
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
		if($(this).is(':checked')){
			$(this).parent().find('.atten_hid').val(1);
			$(this).parent().addClass('sel_atten_day');
		}else{
			$(this).parent().find('.atten_hid').val(0);
			$(this).parent().removeClass('sel_atten_day');
		}
	});*/
});
</script>
<?php include "../footer.php"?>
