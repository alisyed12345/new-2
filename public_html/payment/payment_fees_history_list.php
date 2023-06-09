<?php
$mob_title = "Family Schedule Payment ";
include "../header.php";

$user_id = trim(htmlspecialchars($_GET['id']));


if(!empty(get_country()->currency)){
    $currency = get_country()->currency;
}else{
    $currency = '';
}

if (!in_array("su_payment_fees_history_list", $_SESSION['login_user_permissions']) && is_numeric($user_id)) {
    include "../includes/unauthorized_msg.php";
    exit;
}


$family = $db->get_row("select * from ss_family f LEFT JOIN ss_state s ON f.billing_entered_state = s.id where f.id='" . $user_id . "' And is_deleted=0");

$check_family_exec_mode = $db->get_row("SELECT * from ss_payment_sch_item_cron where family_id='" . $user_id . "' and schedule_status <> 0 ");



$family_schedule_payment = get_family_schedule_payment($user_id );
$schedule_payment_cron = get_schedule_payment_cron($user_id);
$obj_array = payment_confirmation_check((array)$family_schedule_payment,(array)$schedule_payment_cron);

// $students = $db->get_results("select s.user_id from ss_student s inner join ss_user u on u.id = s.user_id 
//   INNER JOIN ss_student_session_map ss ON ss.student_user_id = u.id where s.family_id= '" . $user_id . "' 
//   AND u.is_locked=0 AND u.is_active=1 and u.is_deleted = 0 and ss.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");

  
$students = $db->get_results("SELECT s.user_id,s.first_name,s.last_name  
FROM ss_student s
INNER JOIN ss_user u ON u.id = s.user_id
INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
INNER JOIN ss_family f ON f.id = s.family_id
WHERE s.family_id= '" . $user_id . "' AND ssm.session_id='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND u.is_deleted = 0 AND u.is_active=1 
AND u.is_locked=0   GROUP by s.user_id ");


// $students = $db->get_results("SELECT s.user_id,sfi.schedule_status  FROM ss_student_fees_items sfi
// INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
// INNER JOIN ss_user u ON u.id = s.user_id
// INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
// INNER JOIN ss_family f ON f.id = s.family_id
// WHERE s.family_id= '" . $user_id . "' AND sfi.session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND u.is_deleted = 0 AND u.is_active=1 AND u.is_locked=0  AND sfi.schedule_status != 2  GROUP by s.user_id ORDER BY sfi.id desc");


$yearly_schedule = [];
$current_session_sch_or_not = [];
$stu_not_exist_ids = [];
$stu_exist_ids = [];
$stu_name = "";
foreach ($students as $row) {   

        $check_exist = $db->get_row("select * from ss_student_fees_items where student_user_id = '" . $row->user_id . "' AND (schedule_status = 0 OR schedule_status = 3) and schedule_payment_date >= '" .date('Y-m-d'). "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' " );

        if ($check_exist->full_payment == 1) {
            $yearly_schedule[] = $row->user_id;
        }
    
        if (empty($check_exist)) {
            $stu_not_exist_ids[] = $row->user_id;
        } else {
            $stu_exist_ids[] = $row->user_id;
        }
    
        $current_session_sch = $db->get_row("select i.student_user_id from ss_student_fees_items i inner join ss_student_session_map m on m.student_user_id = i.student_user_id  where i.student_user_id = '" . $row->user_id . "' and m.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
        if (!empty($current_session_sch)) {
            $current_session_sch_or_not[] = $current_session_sch;
        }


        if(count((array)$students) > 0){
            if(!empty($check_exist)){
            $stu_name .= $row->first_name." ".$row->last_name.", ";
            }
        }
    
}
//   echo"<pre>";
//   print_r($stu_exist_ids);
//   die;


$basic_fee_all = 0;
$discount_fee_val_all = 0;
$final_amount_all = 0;
foreach ($students as $stu) {

    
    $check_exist = $db->get_results("select * from ss_student_fees_items where student_user_id = '" . $stu->user_id. "' AND (schedule_status = 0 OR schedule_status = 3) and schedule_payment_date >= '" .date('Y-m-d'). "' AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' " );

    if(!empty($check_exist)){

        $user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $stu->user_id . "' and m.latest = 1 and m.session = '" . 
        $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

        $groups = [];
        foreach ($user_groups as $group) {
            $groups[] = $group->id;
        }
        $group_ids = implode(",", $groups);
        $basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        $new_discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $stu->user_id . "' AND sf.status = 1  and d.status=1 and sf.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND d.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
        
        $discountPercentTotal = $basicFees->fee_amount;
        $discountDollarTotal = 0;
        foreach ($new_discountFeesData as $val) {
            if ($val->discount_unit == 'p') {
                $doller = '';
                $percent = '%';
                $fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
                $discountPercentTotal = $discountPercentTotal - $fee_percent;
            } else {
                $doller = $currency;
                $percent = '';
                $discountDollarTotal += $val->discount_percent;
            }
        }
        $basic_fee_all += $basicFees->fee_amount;
        $final_amount = ($discountPercentTotal - $discountDollarTotal);

        if ($final_amount > 0) {
            $total_final_amount = $final_amount;
        } else {
            $total_final_amount = 0;
        }
    
    $final_amount_all += $total_final_amount;
}
}

$discount_fee_val_all = $basic_fee_all - $final_amount_all;

?>

<style>
    label.error {
        color: red;
    }

    span.mands {
        color: #ff0000;
        display: inline;
        line-height: 1;
        font-size: 12px;
        margin-left: 5px;
    }
</style>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Family Schedule Payment</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
            <li class="active"><a href="family_info.php">Family Info</a></li>
            <li class="active">Family Schedule Payment</li>
        </ul>
    </div>
    <div class="above-content">





        <?php
        // if (count((array)$current_session_sch_or_not) ==  0) {
        if (count((array)$stu_exist_ids) ==  0 && count((array)$students) > 0) {  ?>
            <a href="javascript:;" data-fathername="<?php echo $family->father_first_name . ' ' . $family->father_last_name ?>" data-familyid="<?php echo $user_id ?>" class="pull-right btn btn-success schedulePayment" title='Schedule New Payment'><span>Schedule New Payment</span></a>

        <?php  } ?>

        <?php if (count((array)$stu_exist_ids) > 0 && count((array)$students) > 0) { ?>

            <?php if (count((array)$yearly_schedule) == 0) { ?>

                <?php if (empty($check_family_exec_mode)) { ?>
                <a href="javascript:;" class="pull-right btn btn-danger btncancelaction" title='Cancel All'><span>Cancel All</span></a>
                <?php } ?>

            <?php } ?>


            <a href="javascript:;" style="margin-right: 10px;" data-fathername="<?php echo $family->father_first_name . ' ' . $family->father_last_name ?>" data-familyid="<?php echo $user_id ?>"   data-confirmcheckcon="<?=$obj_array->confirm_check_con ?>" data-notetext="<?=$obj_array->confirm_msg?>" class="pull-right btn btn-primary reschedulePayment" title='Reschedule Payment'>
                <span">Reschedule Payment</span>
            </a>
        <?php  } ?>



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
                            <h5 class="card-title"> Schedule Payment </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <label><strong> Basic Fees: </strong>
                                        <?php echo $currency . ($basic_fee_all + 0) ?>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label><strong> Discount: </strong>
                                        <?php echo $currency . ($discount_fee_val_all + 0) ?>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label><strong> Net Fees: </strong>
                                        <?php echo $currency . ($final_amount_all + 0) ?>
                                    </label>
                                </div>
                            </div>

                            <?php if (!empty($family->father_first_name)) { ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label><strong> 1st Parent Name: </strong>
                                            <?php if (isset($family->father_first_name)) {
                                                echo $family->father_first_name . ' ' . $family->father_last_name;
                                            } ?>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong> 1st Parent Phone : </strong>
                                            <?php
                                            if (isset($family->father_phone)) {
                                                echo internal_phone_check($family->father_phone);
                                            } ?>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong> 1st Parent Email : </strong>
                                            <?php if (isset($family->primary_email)) {
                                                echo $family->primary_email;
                                            } ?>
                                        </label>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (!empty($family->mother_first_name)) { ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label><strong> 2st Parent Name: </strong>
                                            <?php if (isset($family->mother_first_name)) {
                                                echo $family->mother_first_name . ' ' . $family->mother_last_name;
                                            } ?>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong> 2st Parent Phone : </strong>
                                            <?php if (isset($family->mother_phone)) {
                                                echo internal_phone_check($family->mother_phone);
                                            } ?>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label><strong> 2st Parent Email : </strong>
                                            <?php if (isset($family->secondary_email)) {
                                                echo $family->secondary_email;
                                            } ?>
                                        </label>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <label><strong> City : </strong>
                                        <?php if (isset($family->billing_city)) {
                                            echo $family->billing_city;
                                        } ?>
                                </div>

                                <div class="col-md-4">
                                    <label><strong> State : </strong>
                                        <?php if (isset($family->state)) {
                                            echo $family->state;
                                        } ?>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label><strong> Zip Code : </strong>
                                        <?php if (isset($family->billing_post_code)) {
                                            echo $family->billing_post_code;
                                        } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label><strong> Address : </strong>
                                        <?php if (isset($family->billing_address_1)) {
                                            echo $family->billing_address_1;
                                        } ?>
                                    </label>
                                </div>

                            <?php if(!empty($stu_name)){ ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label><strong> Child(ren) : </strong>
                                            <?php echo rtrim($stu_name,", ");?>
                                        </label>
                                    </div>
                                </div>
                                
                                <?php }?>
                            </div>
                        </div>
                    </div>
                    <br>
                    <!-- <div class="row">
                        <div class="col-md-12 text-right text-primary">
                            <a data-toggle="collapse" href="#collapseExample" class="pull-right btn btn-danger regpayhistory" role="button" aria-expanded="false" aria-controls="collapseExample">
                                Show Registration Payment Information
                            </a>
                        </div>
                    </div> -->
                    <div class="row" style="margin-top:15px;">
                        <div class="col-md-12">
                            <div id="reg_payment_detail"></div>
                        </div>
                    </div>
                    <br>

                    <div class="ajaxMsg"></div>
                    <span style="float: right;margin-left: 2px; margin-bottom:10px;">Show Cancelled Schedule</span><input type="checkbox" name="show_cancel_schedule" id="show_cancel_schedule" value="2" class="record" style="float: right; margin-bottom:10px;">
                    <table class="table datatable-basic table-bordered">
                        <thead>
                            <tr>
                                <th>Schedule Date</th>
                                <th>Child(ren)</th>
                                <th>Payment Date</th>
                                <th>Last 4 Digits of CC</th>
                                <th>Final Amount</th>
                                <th>Payment Txns Id</th>
                                <th>Status</th>
                                <th style="text-align: center;">Receipt</th>
                                <th></th>
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





<!--CANCEL All -->
<div id="modalCancelScheduleStatus" class="modal fade">
    <div class="modal-dialog modal-dialog-centered" style="width:400px;">
        <div class="modal-content">
            <form name="frmCancelScheduleStatus" id="frmCancelScheduleStatus" method="post">
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
                            <label for="group_name">Reason<span class="mands">*</span></label>
                            <textarea class="form-control required" id="reason" name="reason" maxlength="250" aria-required="true"></textarea>
                        
                        <?php 
                        if(!empty($schedule_payment_cron)){ ?>
                        		<p style="color:red;"><b>Note</b> :A payment reminder has already been sent to the parents. Do you still want to send this reminder and cancel the previous ones? </p>
                                <input type="checkbox" class="iagree" name="iagree"> <b>I Agree </b><br>
                                <label id="iagree-error" class="error" for="iagree" style="display: none;">requried</label>
                        <?php }
                        ?>
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
                            <button type="submit" class="btn btn-success ">Cancel Payment</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- START Re SCHEDULE MODEL START -->
<div id="modalRsSchedulePayment" class="modal fade modalRsSchedulePayment">
    <div class="modal-dialog modal-dialog-centered" style="width:400px;">
        <div class="modal-content">
            <form name="frmRsSchedulePayment" id="frmRsSchedulePayment" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title" id="familyinfo_title_name">Reschedule Payment</h5>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <label for="group_name">Date<span class="mands">*</span></label>
                            <input required name="schedule_start_date" id="scheduless_start_date" class="form-control required" type="text">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">

                            <?php if (count((array)$yearly_schedule) == 0) { ?>

                                <label for="group_name">Quantity<span class="mands">*</span></label>
                                <input name="quantity" id="quantity" minlength="1" maxlength="2" Digits="true" class="form-control required" type="text">
                                <input type="hidden" name="sch_type" value="monthly">
                            <?php } else { ?>
                                <input type="hidden" name="sch_type" value="yearly">
                            <?php } ?>


                            <input type="hidden" name="stu_ids" id="stu_idss" value="">

                        </div>
                    </div>

                    <br>
                    <div class="row div_conn hide">
                        <div class="col-md-12">
                            <div class="form-group">
                                <p><strong class="text-danger" style="font-size: 18px;">Note :</strong>
                                    <span class="text-danger notetextclass" style="font-size: 15px;"> </span>
                                </p>
                                <div class="form-check-inline">
                                    <label class="form-check-label" for="check2">
                                        <input type="checkbox" class="form-check-input tern_con" style="margin-right:5px;" id="check2" name="tern_con" ><strong>I Agree <span class="mandatory">*</span></strong>
                                    </label>
                                    <label id="tern_con-error" class="validation-error-label" for="tern_con" style="display: inline-block;"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <strong id="statusMsgses"></strong>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="action" value="start_schedule">
                            <input type="hidden" name="familyid" id="familie_id" value="<?php echo $user_id ?>">
                            <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- START Re SCHEDULE MODEL END -->



<!-- START SCHEDULE MODEL START -->
<div id="modalSchedulePayment" class="modal fade modalScheduleNewPayment">
    <div class="modal-dialog modal-dialog-centered" style="width:400px;">
        <div class="modal-content">
            <form name="frmSchedulePayment" id="frmSchedulePayment" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title" id="familyinfo_title_name">Start Schedule Payment</h5>
                </div>
                <div class="modal-body" id="data_load">

                </div>
                <div class="modal-footer familyfooter hide">
                    <div class="row">
                        <div class="col-md-6">
                            <strong id="statusMsgs"></strong>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="action" id="frm_action" value="start_schedule">
                            <input type="hidden" name="familyid" id="familyid">
                            <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
                            <button type="button" class="btn btn-default btnclose" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- START SCHEDULE MODEL END -->




<!-- STATUS SCHEDULE MODEL START -->

<!-- SINGLE CANCEL -->

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
                        <p id="reminder_note"></p>
                        <?php
                        if(!empty($schedule_payment_cron)){ ?>
                        <p id="required_id_two" ></p>
                    <?php
                       } ?>
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
                            <input type="hidden" name="sch_pay_date" id="sch_pay_date">
                            <input type="hidden" name="current_status" id="current_status">
                            <input type="hidden" name="fee_amount" id="fee_amount">
                            <input type="hidden" name="schedule_status" id="schedule_status">
                            <input type="hidden" name="sch_item_id" id="sch_item_id">
                            <button type="submit" class="btn btn-success insidebtntext">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>




<div id="modal_send_model" class="modal fade ">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h5 class="modal-title" style="margin-bottom:20px;" id="familyinfo_title"> <b>Send Receipt</b> ( <span class="payment_date_title"></span> )
                </h5>
            </div>

            <div class="container">
                <div class="row" style="margin-left:3px;">
                    <div class="col-md-2">
                        <strong>Credit Card Number</strong>
                        <p id="crad_no"></p>
                    </div>
                    <div class="col-md-2">
                        <strong>Final Fees</strong>
                        <p id="final_amount"></p>
                    </div>
                </div>
            </div>



            <div class="modal-body">
                <form id="sendInvoiceForm" class="form-validate-jquery" method="post">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email">Email:<span class="mandatory">*</span></label>
                                <input placeholder="Email" id="email" name="email" class="form-control required email" type="email">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="text-left">
                                <p id="sinlingMsg" style="color: green;"></p>
                            </div>
                            <input type="hidden" name="family_id" id="family_id">
                            <input type="hidden" name="stu_user_id" id="stu_user_id">
                            <input type="hidden" name="trxn_id" id="trxn_id">
                            <input type="hidden" name="account_entries_id" id="account_entries_id">
                            <input type="hidden" name="action" value="sendInvoice">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>



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
<script type="text/javascript" src="http://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js"></script>

<script>
    $(document).ready(function() {
        //FILL TABLE
        fillTable();

        var yesterday = new Date((new Date()).valueOf() - 1000 * 60 * 60 * 24);
        $('#scheduless_start_date').pickadate({
            labelMonthNext: 'Go to the next month',
            labelMonthPrev: 'Go to the previous month',
            labelMonthSelect: 'Pick a month from the dropdown',
            labelYearSelect: 'Pick a year from the dropdown',
            selectMonths: true,
            selectYears: true,
            disable: [{
                from: [0, 0, 0],
                to: yesterday
            }],
            min: [<?php echo date('Y') ?>, <?php echo date('m') - 1 ?>, <?php echo date('d') ?>],
            format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
            formatSubmit: 'yyyy-mm-dd'
        });


        $('.reschedulePayment').on('click', function() {


            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
            $.post(targetUrl, {
                familyid: $('#familie_id').val(),
                action: 'get_stu_schedule'
            }, function(data, status) {
                if (status == 'success') {
                    if (data.code == 1) {
                        $('#stu_idss').val(data.stuids);
                    }
                }
            }, 'json');

            $('.modalRsSchedulePayment').modal('show');

            if($(this).data('confirmcheckcon') == 1){
                $('.div_conn').removeClass('hide');
                $('.tern_con').addClass('required');
                
                $('.notetextclass').html($(this).data('notetext'));
            }else{
                $('.div_conn').addClass('hide');
                $('.tern_con').removeClass('required');
            }

        });




        $(document).on('click', '.sendInvoice', function() {

            $('#sendInvoiceForm').trigger('reset');
            var validator = $("#sendInvoiceForm").validate();
            validator.resetForm();

            var familyid = $(this).data('familyid');
            var trxn_id = $(this).data('trxnid');
            var account_entries_id = $(this).data('account_entries_id');
            var crad_no = $(this).data('cradno');
            var final_amount = $(this).data('finalamount');
            var basic_fee = $(this).data('basicfee');
            var discount_fee_amount = $(this).data('discountfeeamount');
            var discount_fee_amount = $(this).data('discountfeeamount');



            $('.payment_date_title').html($(this).data('paymentdate'));

            $('#family_id').val(familyid);
            $('#trxn_id').val(trxn_id);
            $('#account_entries_id').val(account_entries_id);
            $('#stu_user_id').val($(this).data('id'));
            $('#crad_no').html(crad_no);
            $('#final_amount').html(final_amount);
            $('#modal_send_model').modal('show');
        });



        $('#sendInvoiceForm').submit(function(e) {
            e.preventDefault();
            if ($('#sendInvoiceForm').valid()) {
                $('#sinlingMsg').html('Processing...');
                var formDate = $(this).serialize();
                $.post('<?php echo SITEURL ?>ajax/ajss-payment-history', formDate, function(data, status) {
                    $('#sinlingMsg').html('');
                    if (status == 'success') {
                        if (data.code == 1) {
                            $('#sendInvoiceForm').trigger('reset');
                            $("#sinlingMsg").html(data.msg);
                            setTimeout(function() {
                                $("#sinlingMsg").html(' ');
                                $('#modal_send_model').modal('hide');
                            }, 2000);
                        } else {
                            $("#sinlingMsg").html(data.msg);
                            setTimeout(function() {
                                $("#sinlingMsg").html(' ');
                                $('#modal_send_model').modal('hide');
                                fillTable();
                            }, 3000);
                        }
                    } else {
                        $("#sinlingMsg").html(data.msg);
                        setTimeout(function() {
                            $("#sinlingMsg").html(' ');
                            $('#modal_send_model').modal('hide');
                        }, 3000);
                    }
                }, 'json');

            }
        });


        //SCHEDULE PAYMENT
        $('.schedulePayment').on('click', function() {
            var familyid = $(this).data('familyid');
            var fathername = $(this).data('fathername');
            $('#familyid').val(familyid);
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
            $('#familyinfo_title_name').html("Schedule Payment ( " + fathername + " ) ");
            $('#data_load').html('<h5>Data loading... Please wait</h5>');
            $('.modalScheduleNewPayment').modal('show');

            $.post(targetUrl, {
                familyid: familyid,
                action: 'get_stu_not_schedule'
            }, function(data, status) {
                if (status == 'success') {

                    if (data.code == 1) {
                        $('.familyfooter').removeClass('hide');
                        $('#data_load').html(data.msg);
                        var yesterday = new Date((new Date()).valueOf() - 1000 * 60 * 60 * 24);
                        $('#schedule_start_date').pickadate({
                            labelMonthNext: 'Go to the next month',
                            labelMonthPrev: 'Go to the previous month',
                            labelMonthSelect: 'Pick a month from the dropdown',
                            labelYearSelect: 'Pick a year from the dropdown',
                            selectMonths: true,
                            selectYears: true,
                            disable: [{
                                from: [0, 0, 0],
                                to: yesterday
                            }],
                            min: [<?php echo date('Y') ?>, <?php echo date('m') - 1 ?>, <?php echo date('d') ?>],
                            format: "<?php echo my_date_changer('d mmmm, yyyy'); ?>",
                            formatSubmit: 'yyyy-mm-dd'
                        });

                    } else {
                        $('#data_load').html(data.msg);
                    }

                }
            }, 'json');
        });

        $('.modalScheduleNewPayment').on('hide.bs.modal', function(e) {
            $('.btnsubmit').removeClass('hide');
            $('#statusMsgs').html('');
            $('#frmSchedulePayment').trigger('reset');
            var validator = $("#frmSchedulePayment").validate();
            validator.resetForm();
        });


        $(document).on('click', '.btnclose', function() {
            location.reload();
        });



        // SUBMIT EVENT
        $('#frmSchedulePayment').submit(function(e) {
            e.preventDefault();
            if ($('#frmSchedulePayment').valid()) {
                $('.btnsubmit').prop("disabled", true);
                $('#statusMsgs').html('Processing...');
                var targetUrl = "<?php echo SITEURL ?>ajax/ajss-family";
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('.btnsubmit').prop("disabled", false);
                        $('.btnsubmit').addClass('hide');
                        //fillTable();
                        if (data.code == 1) {
                            $('.schedulePayment').hide();
                            $('.reschedulePayment').show();
                            $('.btncancelaction').show();
                            $('.modalScheduleNewPayment').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                            setTimeout(function() {
                                //fillTable();
                                $('#statusMsgs').html('');
                            }, 3000);
                        } else {
                            $('.modalScheduleNewPayment').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('#statusMsgs').html('');
                            }, 4000);
                        }
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }

                }, 'json');

                //grecaptcha.reset();
            }
        });
     

        $('.modalRsSchedulePayment').on('hide.bs.modal', function(e) {
            $('.btnsubmit').removeClass('hide');
            $('#statusMsgses').html('');
            $('#frmRsSchedulePayment').trigger('reset');
            var validator = $("#frmSchedulePayment").validate();
            validator.resetForm();
        });


        // SUBMIT EVENT
        $('#frmRsSchedulePayment').submit(function(e) {
            e.preventDefault();
            if ($('#frmRsSchedulePayment').valid()) {
                $('.btnsubmit').prop("disabled", true);
                $('#statusMsgses').html('Processing...');
                var targetUrl = "<?php echo SITEURL ?>ajax/ajss-family";
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('.btnsubmit').prop("disabled", false);
                        $('.btnsubmit').addClass('hide');
                        //fillTable();
                        location.reload();
                        if (data.code == 1) {
                            $('.modalRsSchedulePayment').modal('hide');
                            displayAjaxMsg(data.msg, data.code);

                            $('#frmRsSchedulePayment').trigger('reset');
                            var validator = $("#frmSchedulePayment").validate();
                            validator.resetForm();
                            setTimeout(function() {
                                $('#statusMsgses').html('');
                            }, 4000);
                        } else {
                            $('.modalRsSchedulePayment').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('#statusMsgses').html('');
                            }, 4000);
                        }
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }

                }, 'json');

            }
        });




        $('.btncancelaction').on('click', function() {
            $('#modalCancelScheduleStatus').modal('show');
        });

        $('#modalCancelScheduleStatus').on('hide.bs.modal', function(e) {
            $('.statusMessagess').html('');
            $('#required_id').html('');
            $('#frmCancelScheduleStatus').trigger('reset');
            var validator = $("#frmCancelScheduleStatus").validate();
            validator.resetForm();
        });
      
        $('#frmCancelScheduleStatus').submit(function(e) {
            e.preventDefault();
            <?php if(!empty($schedule_payment_cron)){ ?>
                if($('.iagree').is('checked')){
                    $('.iagree').removeClass('required');  
                }else{
                    $('.iagree').addClass('required');
                }
            <?php } ?>
            if ($('#frmCancelScheduleStatus').valid()) {
                $('.insidebtntext').prop("disabled", true);
                $('.statusMessagess').html('Processing...');
                var targetUrl = "<?php echo SITEURL ?>ajax/ajss-payment-history";
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('.insidebtntext').prop("disabled", false);
                        //fillTable();
                        $('.statusMessagess').html(data.msg);
                        if (data.code == 1) {
                            $('#modalCancelScheduleStatus').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('#statusMessagess').html('');
                                
                            }, 4000);
                             location.reload();
                        } else {
                            $('#modalCancelScheduleStatus').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('#statusMessagess').html('');
                            }, 4000);
                        }
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }

                }, 'json');
            }
        });

        //CANCEL PAYMENT END

        $(document).on('click', '.btnaction', function() {

            var itemid = $(this).data('itemid');
            var sch_item_id = $(this).data('schunique');
            var date = $(this).data('date');
            var amount = $(this).data('amount');
            var status = $(this).data('status');
            var btntext = $(this).data('btntext');
            var remindernote = $(this).data('remindernote');
            

            $('#fee_amount').val(amount);
            $('#sch_pay_date').val(date);
            $('#reminder_note').html(remindernote);
            $('#current_status').val(status);
            $('#itemid').val(itemid);
            $('#sch_item_id').val(sch_item_id);
            $('#schedule_status').val(btntext);
            var fulltext = "Schedule Date: ( " + date + " ) &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
            fulltext += "  Amount: " + amount + " ";
            $('.datetext').html(btntext);
            $('#titlemsgs').html(fulltext);
            $('.insidebtntext').html(btntext + ' Payment');
            $('#modalScheduleStatus').modal('show');
        });


        $('#modalScheduleStatus').on('hide.bs.modal', function(e) {
            $('#reminder_note').html('');
            $('#required_id').html('');
            $('.statusMessage').html('');
            $('#frmScheduleStatus').trigger('reset');
            var validator = $("#frmScheduleStatus").validate();
            validator.resetForm();
            $('#required_id_two').html('');
        });


        //SCHEDULE STATUS CHANGE EVENT
        $('#frmScheduleStatus').submit(function(e) {
            e.preventDefault();
            <?php if(!empty($schedule_payment_cron)){ ?>
                if($('.agree_check').is('checked')){
                    $('.agree_check').removeClass('required');  
                }else{
                    $('.agree_check').addClass('required');
                }
            <?php } ?>
            if ($('#frmScheduleStatus').valid()) {
                $('.insidebtntext').prop("disabled", true);
                $('.statusMessage').html('Processing...');
                var targetUrl = "<?php echo SITEURL ?>ajax/ajss-payment-history";
                var formDate = $(this).serialize();
                $.post(targetUrl, formDate, function(data, status) {
                    if (status == 'success') {
                        $('.insidebtntext').prop("disabled", false);
                        //fillTable();
                        location.reload();
                        $('.statusMessage').html(data.msg);
                        if (data.code == 1) {
                            $('#modalScheduleStatus').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('.statusMessage').html('');
                            }, 4000);
                        } else {
                            $('#modalScheduleStatus').modal('hide');
                            displayAjaxMsg(data.msg, data.code);
                            setTimeout(function() {
                                $('.statusMessage').html('');
                            }, 4000);
                        }
                    } else {
                        displayAjaxMsg(data.msg, data.code);
                    }

                }, 'json');

                //grecaptcha.reset();
            }
        
        });

        $(document).on('click', '.btnhistory', function() {
            var itemid = $(this).data('itemid');
            var user_id = $(this).data('id');

            $('#list_sechedule_history').html('<h5>Data loading... Please wait</h5>');
            $('#modal_sechedule_history').modal('show');

            var targetUrl = "<?php echo SITEURL ?>ajax/ajss-schedule-payment";
            $.post(targetUrl, {
                user_id: user_id,
                itemid: itemid,
                action: 'view_sechedule_status_history'
            }, function(data, status) {
                if (status == 'success') {

                    if (data.code == 1) {
                        var list = '<table class="table"><thead><tr><th>Schedule Date</th><th>Old Status</th><th>Current Status</th><th>Created Date Time</th><th>Created User</th></tr></thead><tbody>';
                        $.each(data.msg, function(key, value) {

                            if (value.current_status == 1) {
                                var current_status = 'Success';
                            } else if (value.current_status == 2) {
                                var current_status = 'Cancel';
                            } else if (value.current_status == 3) {
                                var current_status = 'Hold';
                            } else if (value.current_status == 4) {
                                var current_status = 'Decline';
                            } else if (value.current_status == 0) {
                                var current_status = 'Pending';
                            } else if (value.current_status == 5) {
                                var current_status = 'Skipped';
                            }

                            if (value.new_status == 1) {
                                var new_status = 'Success';
                            } else if (value.new_status == 2) {
                                var new_status = 'Cancel';
                            } else if (value.new_status == 3) {
                                var new_status = 'Hold';
                            } else if (value.new_status == 4) {
                                var new_status = 'Decline';
                            } else if (value.new_status == 0) {
                                var new_status = 'Pending';
                            } else if (value.new_status == 5) {
                                var new_status = 'Skipped';
                            }

                            if (value.first_name != null) {
                                var name = value.first_name + ' ' + value.last_name;

                            } else if (value.created_by != undefined || value.created_by != null) {
                                var name = value.created_by;
                            } else {
                                var name = '-';
                            }


                            list += '<tr>';
                            list += '<td> ' + value.schedule_payment_date + ' </td>';
                            list += '<td> ' + current_status + ' </td>';
                            list += '<td> ' + new_status + ' </td>';
                            // list += '<td> ' + value.comments + ' </td>';
                            list += '<td> ' + value.created_at + ' </td>';
                            list += '<td> ' + name + ' </td>';
                            list += '</tr>';

                        });

                        list += '</tbody></table>';

                        $('#list_sechedule_history').html(list);

                    } else {
                        $('#list_sechedule_history').html(data.msg);
                    }
                } else {
                    displayAjaxMsg(data.msg, data.code);
                }

            }, 'json');

        });




        //Schedule Type

        $(document).on('click', '.sch_type', function() {
            var schtype = $(this).val();
            if (schtype == 'yearly') {
                $('.qulitydiv').hide();
                $('.quantityval').val('');
            } else {
                $('.qulitydiv').show();
            }
        });

        $('#show_cancel_schedule').on('change', function() {
            if ($('.record').is(":checked")) {
                $show_cancel_schedule = $(this).val();
                fillTable($show_cancel_schedule);
            } else {
                fillTable();
            }
        });

        $(document).on('click', '.regpayhistory', function() {
            var familyid = '<?php echo $_GET['id']; ?>';
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';

            if ($.trim($('#reg_payment_detail').html()) != '') {
                $('#reg_payment_detail').html('');
                $('.regpayhistory').html('Show Registration Payment Information');
            } else {
                $('.regpayhistory').html('Loading... Registration Payment Information');

                $.post(targetUrl, {
                    familyid: familyid,
                    action: 'get_reg_pay_history'
                }, function(data, status) {
                    if (status == 'success') {
                        $('.regpayhistory').html('Hide Registration Payment Information');
                        $('#reg_payment_detail').html(data);
                    }
                });
            }
        });

        $(document).on('click', '.retryschedule', function() {
            var itemid = $(this).data('itemid');
            var user_id = $(this).data('id');

            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-history';
            $('#retryprocess').html('<b style="color:black;">Processing</b>');
            $.post(targetUrl, {
                itemid: itemid,
                id: user_id,
                action: 'retry_schedule_payment'
            }, function(data, status) {
                if (status == 'success') {
                    fillTable();
                    if (data.code == 1) {
                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            $('#statusMsgs').html('');
                        }, 4000);
                    } else {

                        displayAjaxMsg(data.msg, data.code);
                        setTimeout(function() {
                            $('#statusMsgs').html('');
                        }, 4000);
                    }
                }
            }, 'json');
        });

    });

    function fillTable($show_cancel_schedule) {
        var table = $('.datatable-basic').DataTable({
            autoWidth: false,
            destroy: true,
            ordering: true,
            searching: false,
            lengthChange: false,
            // processing: true,
            responsive: true,
            ajax: {
                "url": '<?php echo SITEURL ?>ajax/ajss-payment-history',
                "type": "post",
                "data": function(d) {
                    d.action = "list_history_payments";
                    d.user_id = '<?php echo $user_id ?>';
                    d.show_cancel_sch = $show_cancel_schedule;
                }
            },
            sProcessing: '',
            language: {
                loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
            },
            'columns': [{
                    'data': 'schedule_payment_date',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'child_name',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'payment_date',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'credit_card_no',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'final_amount',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'payment_unique_id',
                    searchable: true,
                    orderable: true
                },
                {
                    'data': 'payment_trxn_status',
                    searchable: true,
                    orderable: true
                },
            ],
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [{
                    "targets": 0,
                    "type": "date"
                },
                { 'visible': false, 'targets': [5] },
                {
                    "render": function(data, type, row) {
                        btn = '';
                        <?php //if($_SESSION['ss_login_usertypecode'] == 'UT01'){ 
                        if (check_userrole_by_code('UT01')) { ?>
                            if (row['payment_trxn_status'] == 'Success') {
                                btn += "<a href='<?php echo SITEURL ?>ajax/ajss-download_invoice_pdf?account_entries_id=" + row['account_entries_id'] + "&id=" + row['user_id'] + "&payment_txns_id=" + row['payment_txns_id'] + "' class='text-primary action_link downloadInvoice' title='Send Message'>Download Receipt</a>";

                                btn += "<a href='javascript:void(0)' class='text-success action_link sendInvoice' title='Send Message' data-account_entries_id='" + row['account_entries_id'] + "' data-id='" + row['user_id'] + "'  data-familyid='" + row['family_id'] + "'  data-trxnid='" + row['payment_txns_id'] + "' data-cradno='" + row['credit_card_no'] + "' data-finalamount='" + row['final_amount'] + "'  data-basicfee='" + row['basic_fee'] + "'  data-discountfeeamount='" + row['discount_fee_amount'] + "' data-paymentdate='" + row['payment_date'] + "'    >Send Receipt</a>";

                            }
                        <?php } ?>
                        return btn;

                    },
                    "targets": 7,
                    "type": "date",
                },
                {
                    "render": function(data, type, row) {
                        btn = '';
                        <?php
                        if (check_userrole_by_code('UT01')) { ?>

                            <?php if (in_array("su_student_list", $_SESSION['login_user_permissions'])) { ?>
                                if (row['schedule_notification'] == 0) {
                                    if (row['is_active'] == 1) {
                                        if (row['payment_trxn_status'] == 'Success' && row['payment_trxn_status'] == 'Cancel') {
                                            btn += '';
                                        } else if (row['payment_trxn_status'] == 'Hold') {
                                            btn += "<a href='javascript:;' class='text-success action_link btnaction'  data-itemid='" + row['id'] + "' data-date='" + row['sch_payment_date'] + "' data-amount='" + row['final_amount'] + "'  data-status='" + row['payment_trxn_status'] + "'  data-btntext='Resume' title='Resume' >Resume</a> ";
                                        } else if (row['payment_trxn_status'] == 'Pending') {
                                            
                                            btn += "<a href='javascript:;' class='text-warning action_link btnaction' data-itemid='" + row['id'] + "'  data-schunique='" + row['schedule_unique_id'] + "' data-date='" + row['sch_payment_date'] + "' data-amount='" + row['final_amount'] + "'  data-status='" + row['payment_trxn_status'] + "' data-btntext='Cancel'   title='Cancel' >Cancel</a> ";

                                            btn += "<a href='javascript:;' class='text-primary action_link btnaction'  data-itemid='" + row['id'] + "' data-date='" + row['sch_payment_date'] + "' data-amount='" + row['final_amount'] + "'  data-status='" + row['payment_trxn_status'] + "' data-btntext='Hold' title='Hold' >Hold</a>";
                                        } else if (row['payment_trxn_status'] == 'Decline') {
                                            btn += '';
                                        }
                                    }
                                } else {
                                    if (row['payment_trxn_status'] !== 'Success' && row['payment_trxn_status'] !== 'Cancel' && row['payment_trxn_status'] !== 'Decline' && row['payment_exec_status']==0)
                                     {
                                        btn += "<a href='javascript:;' class='text-warning action_link btnaction' data-remindernote ='" + row['reminder_note'] + "' data-itemid='" + row['id'] + "' data-schunique='" + row['schedule_unique_id'] + "' data-date='" + row['sch_payment_date'] + "' data-amount='" + row['final_amount'] + "'  data-status='" + row['payment_trxn_status'] + "' data-btntext='Cancel'   title='Cancel' >Cancel</a> ";
                                    }
                                }

                                if (row['payment_trxn_status'] != 'Pending') {
                                    btn += "<a href='javascript:void(0)' class='text-primary action_link btnhistory' data-itemid='" + row['sch_item_id'] + "' data-id='" + row['user_id'] + "' title='View History'> View History </a>";
                                }
                                // if(row['retry']){
                                //     btn += "<a href='javascript:void(0)' class='action_link retryschedule text-secondary' id='retryprocess' data-itemid='"+row['sch_item_id']+"' data-id='" + row['user_id'] + "' title='Retry'> Retry </a>";
                                // } 
                        <?php }
                        } ?>
                        return btn;

                    },
                    "targets": 8,
                    "type": "date",

                }
            ]


        });
    }
</script>
<?php include "../footer.php" ?>