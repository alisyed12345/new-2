<?php 
$mob_title = "Schedule Payment";
include "../header.php";

  $user_id = trim(htmlspecialchars($_GET['id']));

  if(!in_array("su_payment_recurring_history", $_SESSION['login_user_permissions']) && is_numeric($user_id)){
    include "../includes/unauthorized_msg.php";
  exit;
  }    

             
   $student_fees_items = $db->get_results("select * from ss_student_fees_items where student_user_id = '".$user_id."' AND schedule_status = 0");
     $itemid = "";
    foreach ($student_fees_items as $scheule_item) { 
      $itemid .=  $scheule_item->id.' '; 
     }

   if(count((array)$student_fees_items) > 0){ 
   $btntext = "Reschedule";
   }else{
   $btntext = "Start Schedule";
   }

  if(!empty(get_country()->currency)){
    $currency = get_country()->currency;
  }else{
    $currency = '';
  }
 

  //  $student_data = $db->get_row("SELECT s.admission_no, s.user_id, 
  //   CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student_name, s.allergies , s.dob,
  //   (CASE s.gender WHEN 'm' THEN 'Male' ELSE 'Female' END) AS gender, s.school_grade, f.father_phone, f.father_area_code, f.mother_phone, 
  //   f.mother_area_code,s.qur_sch_stu_user_id,pay.credit_card_no,
  //   CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father_name,
  //   (CASE WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status FROM ss_user u 
  //   INNER JOIN ss_school_sessions ss ON ss.id = u.session_id
  //   INNER JOIN ss_student s ON u.id = s.user_id INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id 
  //   where s.user_id='".$user_id."' AND u.is_deleted = 0 AND u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

  $student_data = $db->get_row("SELECT s.admission_no, s.user_id, 
  CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS student_name, s.allergies , s.dob,
  (CASE s.gender WHEN 'm' THEN 'Male' ELSE 'Female' END) AS gender, s.school_grade, f.father_phone, f.father_area_code, f.mother_phone, 
  f.mother_area_code,s.qur_sch_stu_user_id,pay.credit_card_no,
  CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father_name,
  (CASE WHEN u.is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status 
  FROM ss_user u 
  INNER JOIN ss_student s ON u.id = s.user_id 
  INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
  INNER JOIN ss_school_sessions ss ON ss.id = ssm.session_id
   INNER JOIN ss_family f ON s.family_id = f.id  INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id 
  where s.user_id='".$user_id."' AND u.is_deleted = 0 AND ss.id = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."'");

   $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='".$user_id."' and m.latest = 1 ");
   $groups = [];
   foreach($user_groups as $group){
    $groups[] = $group->id;
   }

   $group_ids = implode(",", $groups);
   $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (".$group_ids.") AND status = 1 AND session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' ");

   //sum(d.discount_percent)

  //$basicDiscountFees = $db->get_var("select sum(d.discount_percent) from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '".$_GET['id']."' AND sf.status = 1  and d.status=1");

  $discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '".$_GET['id']."' AND sf.status = 1  and d.status=1");
  
  $discountPercentTotal = $basicFees->fee_amount;
  $discountDollarTotal = 0;
  $discountDetailes = "";
  foreach ($discountFeesData as $val) { 
    if ($val->discount_unit == 'p') {
        $doller = '';
        $percent = '%';

      $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
			$discountPercentTotal = $discountPercentTotal - $fee_percent;

    }elseif ($val->discount_unit == 'd') {
        $doller = $currency;
        $percent = '';
     
      $discountDollarTotal+= $val->discount_percent;
    }elseif ($val->discount_unit == 'L') {
        $doller = $currency;
        $percent = '';
    
      $discountDollarTotal+= $val->discount_percent;
    }
    
    $amount_val = $val->discount_percent + 0;
    $discountDetailes .=  $val->discount_name.' ( '. $doller.''.$amount_val.''.$percent.'  ), '; 

     }


     $final_amount = ($discountPercentTotal - $discountDollarTotal);
    if($final_amount > 0){
      $actualbasicDiscountFees = $final_amount;
    }else{
      $actualbasicDiscountFees = 0;
    }
      
   ?>

   <style>
     .error{
      color:red;
     }
   </style>
<!-- Page header -->



<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Schedule Payment</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
      <li class="active">List Schedule Payment</li>
    </ul>
  </div>
  <div class="above-content">

 
  <?php if(!empty($basicFees)){  ?>

    <?php if(count((array)$student_fees_items) > 0){  ?>
      <!-- <a href="javascript:void(0);" class="pull-right btncancelaction" style="margin-left: 5px;"><span class="label label-danger">Cancel All</span></a> -->
    <?php } ?>
   <?php  if ($actualbasicDiscountFees > 0) { ?> 
   <!-- <a href="javascript:void(0);" class="pull-right startschedule"><span class="label label-primary btntextname"><?php echo  $btntext; ?></span></a>  -->
   <?php  } ?> 

 <?php } ?>


</div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
    <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat">
        <div class="panel-body">

           <div class="row">
            <div class="col-md-12 mt-4 mt-xl-0">
                <h5 class="card-title"> Schedule Recurring Payment</h5>

                    
                      <div class="row">
                        <div class="col-md-4">
                             <label><strong> Student Name :   </strong><?php if(!empty(trim($student_data->student_name))){ echo $student_data->student_name; } ?>  </label>
                        </div>
                        <div class="col-md-4">
                            <label><strong> 1st Parent Name:   </strong> <?php if(!empty(trim($student_data->father_name))){ echo $student_data->father_name; } ?> </label>
                        </div>

                        <div class="col-md-4">
                            <label><strong> 1st Parent Phone :  </strong> <?php if(!empty(trim($student_data->father_phone))){ echo internal_phone_check($student_data->father_phone); } ?> </label>
                        </div>

                     </div>

                      <div class="row">
                        <div class="col-md-4">
                            <label><strong> Status : </strong>  <?php if(!empty(trim($student_data->status))){ echo $student_data->status; } ?> </label>
                        </div>
                        
                        <div class="col-md-4">
                             <label><strong> Last Four Digits Credit Card : </strong>  ************<?php echo $CardNumber = substr(str_replace(' ', '', base64_decode($student_data->credit_card_no)), -4); ?> </label>
                        </div>
                      </div>

                      <div class="row">
                         <div class="col-md-4">
                             <label><strong> Basic Fee Amount : </strong>  <?php if(!empty(trim($basicFees->fee_amount))){ echo $currency.($basicFees->fee_amount + 0); } ?> </label>
                        </div>
                         
                         <div class="col-md-4">
                            <label><strong> Discount Fee Amount : </strong>
                              <?php
                                  
                                  if(!empty(trim($discount_fee_val))){
                                        echo $currency.($discount_fee_val + 0).' '; } 
                                        
                                       if(!empty($discountDetailes)){ 
                                        echo rtrim($discountDetailes, ', '); }  
                                         ?>      
                            </label>
                        </div>
                        
                        <div class="col-md-4">
                           <label><strong> Final Amount : </strong>  <?php if(!empty(trim($actualbasicDiscountFees))){ echo $currency.$actualbasicDiscountFees; } else{ echo $currency."0"; }?> </label>
                        </div>
                    </div>

            </div>
           </div>
          <br>
          <br>


          <div class="ajaxMsg"></div>
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
                <th>ID</th>
                <th>Schedule Date</th>
                <th>Create Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th class="text-center action_col"></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- START SCHEDULE MODEL START -->
<div id="modalSchedulePayment" class="modal fade">
  <div class="modal-dialog modal-dialog-centered" style="width:400px;">
    <div class="modal-content">
    <form name="frmSchedulePayment" id="frmSchedulePayment" class="form-validate-jquery" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
         <?php if(count((array)$student_fees_items) > 0){ ?>
          <h5 class="modal-title">Reschedule Payment</h5>
        <?php }else{ ?>
          <h5 class="modal-title">Start Schedule Payment</h5>
        <?php } ?>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <label for="group_name">Date</label>
            <input required name="schedule_start_date" id="schedule_start_date" class="form-control" type="text">
            <i class="fas fa-calendar input-prefix" tabindex=0></i>
          </div>
          &nbsp;
         <?php if(count((array)$student_fees_items) > 0){ ?>
    
          <?php }else{ ?>
            <div class="col-md-12">
            <label for="group_name">Quantity</label>
            <input required name="quantity" id="quantity" minlength="1" maxlength="2" Digits="true" class="form-control" type="text">
          </div>
        <?php } ?>
        </div>
      </div>
      <div class="modal-footer">
         <div class="row">
            <div class="col-md-6">
               <strong id="statusMsg"></strong>
            </div>
             <div class="col-md-6">
                <input type="hidden" name="action" value="start_schedule">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="fee_amount" value="<?php echo $actualbasicDiscountFees; ?>">
                <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
      
      </form>
    </div>
  </div>
</div>
</div>
<!-- START SCHEDULE MODEL END -->



<!-- STATUS SCHEDULE MODEL START -->
<div id="modalScheduleStatus" class="modal fade">
  <div class="modal-dialog modal-dialog-centered" style="width:400px;">
    <div class="modal-content">
    <form name="frmScheduleStatus" id="frmScheduleStatus" class="form-validate-jquery" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title datetext"></h5>
      </div>

      <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
             <span id="titlemsgs"></span>
            </div>
        </div>
         <br>
        <div class="row"> 
          <div class="col-md-12">
            <label for="group_name">Reason</label>
            <textarea class="form-control required" id="reason" name="reason" maxlength="250" aria-required="true"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
         <div class="row">
            <div class="col-md-6">
               <strong class="statusMessage"></strong>
            </div>
             <div class="col-md-6">
                <input type="hidden" name="action" value="status_schedule_save">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="itemid" id="itemid">
                 <input type="hidden" name="current_status" id="current_status">
                <input type="hidden" name="fee_amount" value="<?php echo $actualbasicDiscountFees; ?>">
                <input type="hidden" name="schedule_status" id="schedule_status">
                <button type="submit" class="btn btn-success insidebtntext">Submit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
      
      </form>
    </div>
  </div>
</div>
</div>
<!-- STATUS SCHEDULE MODEL END -->


<!-- View Schedule History-->
<div id="modal_sechedule_history" class="modal fade">
<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h5 class="modal-title">Payment Schedule History</h5>
    </div>
    <div class="modal-body viewonly" id="list_sechedule_history"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
</div>
<!-- /View Schedule History -->

<!--CANCEL All -->
<div id="modalCancelScheduleStatus" class="modal fade">
  <div class="modal-dialog modal-dialog-centered" style="width:400px;">
    <div class="modal-content">
    <form name="frmCancelScheduleStatus" id="frmCancelScheduleStatus" class="form-validate-jquery" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Cancel All Schedule</h5>
      </div>

      <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
             <span id="titlemsg"></span>
            </div>
        </div>
         <br>
        <div class="row"> 
          <div class="col-md-12">
            <label for="group_name">Reason</label>
            <textarea class="form-control required" id="reason" name="reason" maxlength="250" aria-required="true"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
         <div class="row">
            <div class="col-md-6">
               <strong class="statusMessagess"></strong>
            </div>
             <div class="col-md-6">
                <input type="hidden" name="action" value="status_cancel_all_schedule">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="itemid" value="<?php echo $itemid; ?>">
                <button type="submit" class="btn btn-success insidebtntext">Submit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
      
      </form>
    </div>
  </div>
</div>

<script>
$( document ).ready(function() {
  //FILL TABLE
      fillTable(); 

    //VALIDATION - digits 
    jQuery.validator.addMethod("Digits", function(value, element) { 
           return this.optional(element) || /^((?!(0))[0-9]{1,2})$/i.test(value);
    }, "Enter valid Quantity");

    var yesterday = new Date((new Date()).valueOf()-1000*60*60*24);
    $('#schedule_start_date').pickadate({
        labelMonthNext: 'Go to the next month',
        labelMonthPrev: 'Go to the previous month',
        labelMonthSelect: 'Pick a month from the dropdown',
        labelYearSelect: 'Pick a year from the dropdown',
        selectMonths: true,
        selectYears: true,
        disable: [
               { from: [0,0,0], to: yesterday }
            ],
        min: [<?php echo date('Y') ?>,<?php echo date('m') - 1 ?>,<?php echo date('d') ?>],
        formatSubmit: 'yyyy-mm-dd'
    });


     $('#modalSchedulePayment').on('hide.bs.modal', function(e) {
         $('#statusMsg').html('');
         $('#frmSchedulePayment').trigger('reset');
         var validator = $("#frmSchedulePayment").validate();
          validator.resetForm();
    });

     $(document).on('click','.startschedule',function() {
          $('#modalSchedulePayment').modal('show');
    });

         //EDIT FAMILIE SUBMIT EVENT
    $('#frmSchedulePayment').submit(function(e) {
        e.preventDefault();
        if ($('#frmSchedulePayment').valid()) {
           $('.btnsubmit').prop("disabled", true);
           $('#statusMsg').html('Processing...');
           var targetUrl = "<?php echo SITEURL ?>ajax/ajss-schedule-payment";
            var formDate = $(this).serialize();
            $.post(targetUrl,formDate, function(data, status) {
                if (status == 'success') {
                           $('.btnsubmit').prop("disabled", false);
                            fillTable();
                            $('#statusMsg').html(data.msg);
                           if (data.code == 1) {
                                 $('.btntextname').html("Reschedule");
                                displayAjaxMsg(data.msg, data.code);
                            } else {
                                displayAjaxMsg(data.msg, data.code);
                            }
                        } else {
                              displayAjaxMsg(data.msg, data.code);
                        }
                
            }, 'json');

            //grecaptcha.reset();
        }
    });

  //CANCEL PAYMENT START

    $(document).on('click','.btncancelaction',function() {
      $('#modalCancelScheduleStatus').modal('show');
    });


     $('#modalCancelScheduleStatus').on('hide.bs.modal', function(e) {
         $('.statusMessagess').html('');
         $('#frmCancelScheduleStatus').trigger('reset');
         var validator = $("#frmCancelScheduleStatus").validate();
          validator.resetForm();
    });

        $('#frmCancelScheduleStatus').submit(function(e) {
        e.preventDefault();
        if ($('#frmCancelScheduleStatus').valid()) {
           $('.insidebtntext').prop("disabled", true);
           $('.statusMessagess').html('Processing...');
           var targetUrl = "<?php echo SITEURL ?>ajax/ajss-schedule-payment";
            var formDate = $(this).serialize();
            $.post(targetUrl,formDate, function(data, status) {
                if (status == 'success') {
                           $('.insidebtntext').prop("disabled", false);
                            fillTable();
                            $('.statusMessagess').html(data.msg);
                           if (data.code == 1) {
                                displayAjaxMsg(data.msg, data.code);
                            } else {
                                displayAjaxMsg(data.msg, data.code);
                            }
                        } else {
                              displayAjaxMsg(data.msg, data.code);
                        }
                
            }, 'json');

            //grecaptcha.reset();
        }
    });

  //CANCEL PAYMENT END



  $(document).on('click','.btnaction',function() {

       var itemid = $(this).data('itemid');
       var date = $(this).data('date');
       var amount = $(this).data('amount');
       var status = $(this).data('status');
       var btntext = $(this).data('btntext');
       $('#current_status').val(status);
       $('#itemid').val(itemid);
       $('#schedule_status').val(btntext);
       var fulltext = "Schedule Date: ( "+date+" ) &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
            fulltext += "  Amount: "+amount+" ";
         $('.datetext').html(btntext);
         $('#titlemsgs').html(fulltext);
         $('.insidebtntext').html(btntext);
        $('#modalScheduleStatus').modal('show');
    });


     $('#modalScheduleStatus').on('hide.bs.modal', function(e) {
         $('.statusMessage').html('');
         $('#frmScheduleStatus').trigger('reset');
         var validator = $("#frmScheduleStatus").validate();
          validator.resetForm();
    });


     //SCHEDULE STATUS CHANGE EVENT
    $('#frmScheduleStatus').submit(function(e) {
        e.preventDefault();
        if ($('#frmScheduleStatus').valid()) {
           $('.insidebtntext').prop("disabled", true);
           $('.statusMessage').html('Processing...');
           var targetUrl = "<?php echo SITEURL ?>ajax/ajss-schedule-payment";
            var formDate = $(this).serialize();
            $.post(targetUrl,formDate, function(data, status) {
                if (status == 'success') {
                           $('.insidebtntext').prop("disabled", false);
                            fillTable();
                            $('.statusMessage').html(data.msg);
                           if (data.code == 1) {
                                displayAjaxMsg(data.msg, data.code);
                            } else {
                                displayAjaxMsg(data.msg, data.code);
                            }
                        } else {
                              displayAjaxMsg(data.msg, data.code);
                        }
                
            }, 'json');

            //grecaptcha.reset();
        }
    });


   //view_sechedule_status_history

  
      $(document).on('click','.btnhistory',function() {
            var itemid = $(this).data('itemid');
            var user_id = $(this).data('id');

           $('#list_sechedule_history').html('<h5>Data loading... Please wait</h5>');
           $('#modal_sechedule_history').modal('show');

            var targetUrl = "<?php echo SITEURL ?>ajax/ajss-schedule-payment";
            $.post(targetUrl,{user_id:user_id,itemid:itemid,action:'view_sechedule_status_history'}, function(data, status) {
                if (status == 'success') {
                   
                    if (data.code == 1) {
                  var list = '<table class="table"><thead><tr><th>Schedule Date</th><th>Old Status</th><th>Current Status</th><th>Created Date Time</th><th>Created User</th></tr></thead><tbody>';
          $.each(data.msg, function( key, value ) {

                       if(value.current_status == 1){
                        var current_status = 'Success';
                        }else if(value.current_status == 2){
                        var current_status = 'Cancel';
                        }else if(value.current_status == 3){
                        var current_status = 'Hold';
                        }else if(value.current_status == 4){
                        var current_status = 'Decline';
                        }else if(value.current_status == 0){
                        var current_status = 'Pending';
                        }else if(value.current_status == 5){
                        var current_status = 'Skipped';
                        }

                        if(value.new_status == 1){
                        var new_status = 'Success';
                        }else if(value.new_status == 2){
                        var new_status = 'Cancel';
                        }else if(value.new_status == 3){
                        var new_status = 'Hold';
                        }else if(value.new_status == 4){
                        var new_status = 'Decline';
                        }else if(value.new_status == 0){
                        var new_status = 'Pending';
                        }else if(value.new_status == 5){
                        var new_status = 'Skipped';
                        }

                        if(value.first_name == null){
                         if(value.created_by != null){
                          var name = value.created_by;
                         }else{
                          var name = "-";
                         }
                          
                         }else{
                         var name = value.first_name+' '+value.last_name;
                        }


           list += '<tr>';
           list += '<td> '+value.schedule_payment_date+' </td>';
           list += '<td> '+current_status+' </td>';
           list += '<td> '+new_status+' </td>';
           // list += '<td> '+value.comments+' </td>';
           list += '<td> '+value.created_at+' </td>';
           list += '<td> '+name+' </td>';
           list += '</tr>';

          });

                    list += '</tbody></table>';

                   $('#list_sechedule_history').html(list);

               }else{
                 $('#list_sechedule_history').html(data.msg);
               }
                } else {
                      displayAjaxMsg(data.msg, data.code);
                }
                
            }, 'json');

    });
   
});

function fillTable(){
  var table = $('.datatable-basic').DataTable({
        autoWidth: false,
        destroy: true,
        ordering: true,
        searching: false,
        lengthChange: false,
        processing: true,
        responsive: true,
        ajax: {
            "url": '<?php echo SITEURL ?>ajax/ajss-schedule-payment',
            "type": "post",
            "data": function(d) {
                d.action = "list_sechedule_payments";
                d.user_id = '<?php echo $user_id ?>';
            }
        },
       sProcessing:'',    
    language: {
       loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
    },
    'columns': [
    { 'data': 'student_user_id' },
    { 'data': 'schedule_payment_date',searchable: true,orderable: true },
    { 'data': 'created_at',searchable: true,orderable: true },  
    { 'data': 'amount',searchable: true,orderable: true },
    { 'data': 'status',searchable: true,orderable: true },
    ],
    "columnDefs": [
    {
      "render": function ( data, type, row ) { 
        btn = '';
        <?php //if($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01'){ 
        if(check_userrole_by_code('UT01')){ ?>
        
        <?php  if(in_array("su_student_list", $_SESSION['login_user_permissions'])){ ?>
        // if(row['status'] == 'Success' && row['status'] == 'Cancel'){
        //          btn += '';
        // }else if(row['status'] == 'Hold'){
        //           btn +="<a href='javascript:;' class='text-success action_link btnaction'  data-itemid='"+row['id']+"' data-date='"+row['schedule_payment_date']+"' data-amount='"+row['amount']+"'  data-status='"+row['status']+"'  data-btntext='Resume' title='Resume' >Resume</a> ";
        // }else if(row['status'] == 'Pending'){
        //          btn +="<a href='javascript:;' class='text-warning action_link btnaction' data-itemid='"+row['id']+"' data-date='"+row['schedule_payment_date']+"' data-amount='"+row['amount']+"'  data-status='"+row['status']+"' data-btntext='Cancel'   title='Cancel' >Cancel</a> ";
        //          btn +="<a href='javascript:;' class='text-primary action_link btnaction'  data-itemid='"+row['id']+"' data-date='"+row['schedule_payment_date']+"' data-amount='"+row['amount']+"'  data-status='"+row['status']+"' data-btntext='Hold' title='Hold' >Hold</a>";
        // }else if(row['status'] == 'Decline'){
        //  btn += ''; 
        // }
        
        btn +="<a href='javascript:;' class='text-info action_link btnhistory' data-itemid='"+row['id']+"' data-id='"+row['student_user_id']+"'  title='History' >View History</a> ";
      
        <?php } } ?>
        return btn;

      }, "targets": 5 },
      { "visible": false,  "targets": [ 0 ] }
        ]
    });
}

</script>
<?php include "../footer.php"?>
