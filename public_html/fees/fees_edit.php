<?php 
$mob_title = "Edit Payment";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!check_userrole_by_code('UT01') && !check_userrole_by_code('UT04')){
	include "../includes/unauthorized_msg.php";
	return;
} 

if(isset($_GET['id'])){
	$fees = $db->get_row("select * from ss_fees where id='".$_GET['id']."'");
	$fees_to = $db->get_row("select * from ss_fees where session='".$fees->session."' order by id desc limit 1");
	$studentgroupmap = $db->get_row("select * from ss_studentgroupmap where latest=1 and student_user_id='".$fees->student_user_id."' order by id desc limit 1");	
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Edit Payment- <?php echo 'Record ID #'.$_GET['id'] ?></h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo SITEURL."fees/fees_list" ?>">Payments List</a></li>
      <li class="active">Edit Payment</li>
    </ul>
  </div>
  <div class="above-content">
  <a href="javascript:history.go(-1)" class="last_page">Go Back To Last Page</a>
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
              <?php /*?><div class="col-lg-3">
                <div class="form-group">
                  <?php 
				$groups = $db->get_results("select * from ss_groups where is_active=1 and is_deleted=0"); 
			  ?>
                  <label>Group:<span class="mandatory">*</span></label>
                  <select class="select form-control" name="group" id="group" required>
                    <option value="">Select Group</option>
                    <?php foreach($groups as $grp){ ?>
                    <option value="<?php echo $grp->id ?>"><?php echo $grp->group_name ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div><?php */?>
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
                    <option value="<?php echo $stu->user_id ?>" <?php echo $fees->student_user_id==$stu->user_id?'selected="selected"':'' ?>><?php echo $stu->student_name.' - '.$group ?></option>
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
                  <input type="text" name="amount" required id="amount" value="<?php echo $fees->amount ?>" class="form-control number" />
                </div>
              </div>
            </div>
            <div class="row">              
              <div class="col-lg-3">
                <div class="form-group">
                  <label>Receipt Number:</label>
                  <input type="text" name="receipt_no" required id="receipt_no" value="<?php echo $fees->receipt_no ?>" class="form-control" />
                </div>
              </div>
              <div class="col-lg-7">
                <div class="form-group">
                  <label>Remarks:</label>
                  <input type="text" name="remarks" id="remarks" value="<?php echo $fees->remarks ?>"  class="form-control" />
                </div>
              </div>
              <div class="col-lg-2">
                <div class="form-group"> <br />
                  <input type="hidden" name="fees_id" value="<?php echo $_GET['id'] ?>">
                  <input type="hidden" name="action" value="edit_fees">
                  <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
                </div>
              </div>
            </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$( document ).ready(function() { 
	//$('#group').val(<?php echo $studentgroupmap->group_id ?>).trigger('change');
	//fill_students(<?php echo $studentgroupmap->group_id ?>,<?php echo $fees->student_user_id ?>);
	fill_months(<?php echo $fees->student_user_id ?>,'<?php echo $fees->month.'-'.$fees->year ?>');
	
	$('#group').change(function(){ 
		$('#student').html('<option value="">Select</option>');
		$('#start_month').html('<option value="">Select</option>');
		$('#end_month').html('<option value="">Select</option>');
		
		var groupid = $('#group').val();
		if(groupid != ''){
			fill_students(groupid);
		}else{
			$('#student').html('<option value="">Select</option>');
		}
	});
	
	$('#start_month').change(function(){
		fill_to_month();
	});
	
	$('#student').change(function(){
		$('#start_month').html('<option value="">Select</option>');
		$('#end_month').html('<option value="">Select</option>');
		
		var student_user_id = $('#student').val();
		if(student_user_id != ''){
			fill_months(student_user_id);
		}else{
			$('#start_month').html('<option value="">Select</option>');
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
});

function fill_to_month(){
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
}

function fill_months(student_user_id, sel_month = ''){
	var targetUrl = '<?php echo SITEURL ?>ajax/ajss-fees';
	$('#start_month').html('<option value="">Loading...</option>');
	
	$.post(targetUrl,{'action':'get_payable_month',student_user_id:student_user_id},function(data,status){					
		if(status == 'success'){
			if(data.code == 1){
				$('#start_month').html(data.option);
				
				if(sel_month != ''){
					$('#start_month').val(sel_month);
				}
				
				fill_to_month();
			}else{
				displayAjaxMsg(data.msg,data.code);
			}
		}else{
			displayAjaxMsg(data.msg);
		}  
	},'json');
}

function fill_students(groupid, student_user_id=0){
	var targetUrl = '<?php echo SITEURL ?>ajax/ajss-fees';
	$('#student').html('<option value="">Loading...</option>');
			
	$.post(targetUrl,{'action':'get_group_student',groupid:groupid},function(data,status){					
		if(status == 'success'){
			if(data.code == 1){
				$('#student').html(data.option);
				
				if(student_user_id > 0){
					$('#student').val(<?php echo $fees->student_user_id ?>);
				}
			}else{
				displayAjaxMsg(data.msg,data.code);
			}
		}else{
			displayAjaxMsg(data.msg);
		}  
	},'json');
}
</script>
<?php include "../footer.php"?>
