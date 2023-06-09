<?php $mob_title = "Dashboard";
include "../header.php";
$announcements = $db->get_results("select * from ss_announcements where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and is_active = 1 order by display_order asc");
$get_all_invoice_data = $db->get_results("SELECT sfi.schedule_unique_id,inv.family_id, inv.invoice_id, inv.invoice_date, inv.invoice_file_path, inv.amount, inv.receipt_id, inv.receipt_date, (CASE WHEN inv.is_due = 2 THEN 'Overdue' WHEN inv.is_due = 1 THEN 'Paid' ELSE 'Due' END) AS due, inv.status FROM ss_invoice inv INNER JOIN ss_student_fees_items sfi ON sfi.schedule_unique_id = inv.schedule_unique_id INNER JOIN ss_family f ON f.id = inv.family_id INNER JOIN ss_user u ON u.id = f.user_id WHERE u.id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND inv.is_due = '0' group by sfi.schedule_unique_id");

$view_invoice_data = $db->get_row("SELECT sfi.schedule_unique_id,inv.family_id, sfi.schedule_payment_date, inv.invoice_id, inv.invoice_date, inv.invoice_file_path, inv.amount, inv.receipt_id, inv.receipt_date, (CASE WHEN inv.is_due = 2 THEN 'Overdue' WHEN inv.is_due = 1 THEN 'Paid' ELSE 'Due' END) AS due, inv.status, f.user_id, f.father_first_name, f.father_last_name, f.billing_address_1 FROM ss_invoice inv INNER JOIN ss_student_fees_items sfi ON sfi.schedule_unique_id = inv.schedule_unique_id INNER JOIN ss_family f ON f.id = inv.family_id INNER JOIN ss_user u ON u.id = f.user_id WHERE u.id = '" . $_SESSION['icksumm_uat_login_userid'] . "' AND inv.is_due = '0' group by sfi.schedule_unique_id");
$get_next_billing_date = $db->get_var("SELECT schedule_payment_date FROM ss_student_fees_items WHERE schedule_unique_id = '" . $view_invoice_data->schedule_unique_id . "' and schedule_payment_date > '" . $view_invoice_data->schedule_payment_date . "'");
$family = $db->get_row("select * from ss_family f LEFT JOIN ss_state s ON f.billing_state_id = s.id where f.id='" . $_SESSION['icksumm_uat_login_familyid'] . "' And is_deleted=0");
 
?>
<!-- Page header -->
<!-- <script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/visualization/d3/d3.min.js"></script>
<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/plugins/visualization/d3/d3_tooltip.js"></script>
<script type="text/javascript" src="<?php echo SITEURL ?>assets/js/charts/d3/bars/bars_advanced_sortable_vertical.js"></script>-->
<style>
    /* #timetable.table>tbody>tr>td {
    padding: 5px;
    vertical-align: top;
}

#timetable .roster_hr {
    margin: 2px 0px;
}

#timetable {
    font-size: 13px;
}

#timetable td {
    border: solid 1px #888;
}

#timetable th {
    border: solid 1px #888;
    background: #d9d9d9;
} */
    /* .active{
    background-color: #00bfff !important;
    color:white !important;
} */
</style>
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4 class="web_item">Welcome to <?php echo CENTER_SHORTNAME . ' ' . SCHOOL_NAME ?></h4>
            <!-- <a href="javascript:void(0)" class="pull-right text-danger mt-5" id="pickup_dropof_map_link"><i
                    class="icon-bus"></i> Drop-Off / Pick-Up Map</a> -->
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li> <a href="<?php echo SITEURL ?>parents/dashboard"> <i class="icon-home2 position-left"></i>Dashboard</a>
            </li>
        </ul>
    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">

    <?php if (count((array)$announcements)) { ?>
        <div class="row">
            <div class="col-md-12">
                <marquee class="announcement">
                    <?php foreach ($announcements as $ann) { ?>
                        <span><i class="icon-bell3"></i> <?php echo $ann->message ?></span>
                    <?php } ?>
                </marquee>
            </div>
        </div>
    <?php } ?>
    <?php
    $name = [];
    $stu_user_id = [];
    ?>
    <div class="row">
    <div class="col-md-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6 class="panel-title">Class Schedule
                    </h6>
                </div>
                <div class="panel-body ">
                    <div class="row">

                        <?php
                        $students = $db->get_results("select * from ss_student s INNER JOIN ss_user u on s.user_id = u.id
                        INNER JOIN ss_student_session_map ssm on ssm.student_user_id  = u.id where u.is_active = 1
                        AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and s.family_id = '" . $_SESSION['icksumm_uat_login_familyid'] . "' and u.is_deleted = 0");
                        foreach ($students as $stu) {
                            $stu_user_id[] = $stu->user_id;
                            $name[] = ['name' => $stu->first_name . trim(' ' . $stu->middle_name) . ' ' . $stu->last_name, 'id' => $stu->user_id];
                        }

                        ?>
                        <div class="col-md-12">
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($name as $keyval => $stu_name) { ?>
                                    <li class="nav-item <?php if ($keyval == 0) {
                                                            echo 'active';
                                                        } ?>">
                                        <a class="nav-link <?php if ($keyval == 0) {
                                            echo 'active';
                                        } ?>" onclick="childdiv(<?= $stu_name['id'] ?>)" href="#profile" role="tab" data-toggle="tab"><?php echo $stu_name['name']; ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="profile">
                                <?php
                                foreach ($stu_user_id as  $key => $stu_id) {

                                	     $stugroupclass = $db->get_results("select g.id AS group_id, s.id AS class_id, g.group_name,s.class_name from ss_groups g inner join ss_studentgroupmap m on g.id = m.group_id inner join  ss_classes s on m.class_id = s.id where m.latest = 1 and m.student_user_id='" . $stu_id . "' and m.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.is_active = 1");

                        //             $stugroupclass =  $db->get_results("SELECT DISTINCT g.id AS group_id, c.class_name, g.group_name, c.id as class_id,
                        // ct.time_from, ct.time_to,
                        // (SELECT GROUP_CONCAT(CONCAT(s.first_name,' ',s.last_name)) FROM ss_staff s
                        // INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_staff_session_map ssm ON ssm.staff_user_id  = u.id
                        // WHERE ssm.session_id = 3 AND u.is_active = 1 AND u.is_deleted = 0
                        // AND user_id IN ( SELECT staff_user_id FROM ss_staffclasstimemap WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                        // AND ACTIVE = 1)) AS staffname              
                        // FROM ss_groups g INNER JOIN ss_classtime ct ON ct.group_id = g.id
                        // INNER JOIN ss_classes c ON c.id = ct.class_id
                        // INNER JOIN ss_studentgroupmap sgm ON sgm.group_id = g.id
                        // WHERE g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND c.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                        // AND sgm.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sgm.latest = 1
                        // AND g.is_active = 1 AND g.is_deleted = 0 AND c.is_active = 1 AND sgm.student_user_id='" . $stu_id . "'");

                                ?>

                                    <div class="col-lg-12 childss childdiv<?= $stu_id ?>" style="<?php if ($key == 0) {
                                                                                                        echo 'display: block';
                                                                                                    } else {
                                                                                                        echo 'display: none';
                                                                                                    } ?>">
                                        <div class="table-responsive" style="margin-top:15px; ">
                                            <table class="table table-bordered class_datatables" id="timetable">
                                                <thead>
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Level</th>
                                                        <th>Class Time</th>
                                                        <th>Meeting Url</th>
                                                        <th>Meeting ID</th>
                                                        <th>Meeting Password</th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <?php foreach ($stugroupclass as  $row) {
                                                        $get_online_class = $db->get_row("SELECT * FROM ss_classes_online WHERE group_id = '" . $row->group_id . "' AND class_id = '" . $row->class_id . "' AND status <> '2'"); 
                                                        $get_class_time = $db->get_row("SELECT * FROM ss_classtime WHERE group_id = '" . $row->group_id . "' AND class_id = '" . $row->class_id . "' AND is_active = '1'"); ?>
                                                        <tr>
                                                            <td>
                                                                <?php echo  $row->class_name ?>
                                                                <!-- <p> (<?php echo  date('h:i A', strtotime($row->time_from))  ?> - <?php echo  date('h:i A', strtotime($row->time_to)) ?>) </p> -->
                                                            </td>
                                                            <td><?php echo  $row->group_name ?></td>
                                                            <td>
                                                                <?php 
                                                                if(!empty($get_class_time->time_from)){
                                                                    echo  date('h:i a', strtotime($get_class_time->time_from)) . ' to ' . date('h:i a', strtotime($get_class_time->time_to));
                                                                }else{
                                                                    echo "N/A";
                                                                }
      
                                                                
                                                                ?>
                                                            </td>
                                                            <?php if (!empty($get_online_class)) { ?>
                                                                <td><a href="<?php echo  $get_online_class->meeting_url ?>" target="_blank"><?php echo  $get_online_class->meeting_url ?></a></td>
                                                                <td><?php echo  $get_online_class->meeting_id ?></td>
                                                                <td><?php echo  $get_online_class->meeting_password ?></td>
                                                            <?php } else { ?>
                                                                <td>N/A</td>
                                                                <td>N/A</td>
                                                                <td>N/A</td>
                                                            <?php } ?>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                <?php } ?>
                            </div>
                            <!-- <div role="tabpanel" class="tab-pane fade" id="buzz">bbb</div>
                        <div role="tabpanel" class="tab-pane fade" id="references">ccc</div> -->
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <!-- EVENT CALENDAR -->
        <?php
        $event_calendar = $db->get_results("SELECT * FROM ss_school_calendar  where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
    and status = 1 ORDER BY program_date asc, program_name DESC");

        if (count((array)$event_calendar)) {
        ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-flat">
                        <div class="panel-heading">
                            <h6 class="panel-title">Calendar (<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT']; ?>)
                            </h6>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="calendardatatable">
                                     <thead>
                                      <tr>
                                            <th>Date</th>
                                            <th>Program / Event</th>
                                        </tr>
                                      </thead>
                                    <tbody>
                                        <?php foreach ($event_calendar as $event) { ?>
                                            <tr>
                                                <td><?php echo my_date_changer($event->program_date,'c') ?></td>
                                                <td><?php echo $event->program_name ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-flat">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="panel-title">Pending Invoice </h6>
                            </div>
                            <div class="col-md-6 text-right">
                                <!--  <p class="label label-primary text-white" style="font-size: 11px;">Account Status : <span id='totalAmount'></span> </p> -->
                                <p style="font-size: 15px;font-weight: 600;">Account Status : <span id='totalAmount'></span> </p>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered invoice-datatable">
                                <thead>
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Invoice Date</th>
                                        <th>Invoice Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($get_all_invoice_data as $invoice_data) { ?>
                                        <tr role="row" class="odd">
                                            <td class="sorting_1"><?php echo $invoice_data->invoice_id ?></td>
                                            <td><?php echo my_date_changer($invoice_data->invoice_date) ?></td>
                                            <td><?php echo $invoice_data->amount ?></td>

                                            <td><a href="javascript:void(0)" class="action_link viewinvoice text-warning">View</a><a href="<?php echo SITEURL ?>payment/invoice_and_pdf/<?php echo $invoice_data->invoice_file_path ?>" class="action_link" download>Download Invoice</a></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- Add Modal - Assign Teacher-->
    <div id="modal_assign_teacher" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form name="frmAssignTeacher" id="frmAssignTeacher" class="form-validate-jquery" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h5 class="modal-title">Assign Teacher / Helper / Substitute - <span id="modal_title_classtime"></span> </h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Teacher: </strong>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group multi-select-full">
                                    <?php
                                    //                                     $teacher = $db->get_results("SELECT s.user_id, s.first_name,s.last_name FROM ss_staff s
                                    //                                     INNER JOIN ss_user u ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND u.user_type_id in  
                                    // (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher') order by s.first_name asc,s.last_name asc");

                                    $teacher = $db->get_results("SELECT s.user_id, s.first_name,s.last_name FROM ss_staff s
                                        INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_staff_session_map ssm1 ON s.user_id = ssm1.staff_user_id
                                        INNER JOIN ss_usertypeusermap utum ON u.id = utum.user_id
                                        WHERE u.is_active = 1 AND u.is_deleted = 0  AND ssm1.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                                        AND utum.user_type_id in (SELECT id FROM ss_usertype
                                        WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher') order by s.first_name asc,s.last_name asc");
                                    ?>
                                    <select id="teacher_id" name="teacher_id" class="select form-control" required style="width:100%">
                                        <option value="">Select</option>
                                        <?php foreach ($teacher as $tea) { ?>
                                            <option value="<?php echo $tea->user_id ?>">
                                                <?php echo $tea->first_name . " " . $tea->last_name ?> </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Helper: </strong>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group multi-select-full">
                                    <?php
                                    // $helper = $db->get_results("SELECT s.user_id, s.first_name,s.last_name FROM ss_staff s INNER JOIN ss_user u ON s.user_id = u.id
                                    // WHERE u.is_active = 1 AND u.is_deleted = 0 AND u.user_type_id in  
                                    // (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher_helper')
                                    // order by s.first_name asc,s.last_name asc");

                                    $helper = $db->get_results("SELECT s.user_id, s.first_name,s.last_name FROM ss_staff s INNER JOIN ss_user u
                                    ON s.user_id = u.id INNER JOIN ss_staff_session_map ssm1 ON s.user_id = ssm1.staff_user_id INNER JOIN ss_usertypeusermap utum ON u.id = utum.user_id
                                    WHERE u.is_active = 1 AND u.is_deleted = 0  AND ssm1.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                                    AND utum.user_type_id in (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher_helper')
                                    order by s.first_name asc,s.last_name asc");
                                    ?>
                                    <select id="helper_id" name="helper_id" class="select form-control" style="width:100%">
                                        <option value="">Select</option>
                                        <?php foreach ($helper as $hea) { ?>
                                            <option value="<?php echo $hea->user_id ?>">
                                                <?php echo $hea->first_name . " " . $hea->last_name ?> </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Substitute: </strong>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group multi-select-full">
                                    <?php
                                    // $substitute = $db->get_results("SELECT s.user_id, s.first_name,s.last_name FROM ss_staff s
                                    // INNER JOIN ss_user u ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND u.user_type_id in  
                                    // (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher_substitute')
                                    // order by s.first_name asc,s.last_name asc");

                                    $substitute = $db->get_results("SELECT s.user_id, s.first_name,s.last_name FROM ss_staff s INNER JOIN ss_user u
                                    ON s.user_id = u.id INNER JOIN ss_staff_session_map ssm1 ON s.user_id = ssm1.staff_user_id
                                    INNER JOIN ss_usertypeusermap utum ON u.id = utum.user_id
                                    WHERE u.is_active = 1 AND u.is_deleted = 0 AND ssm1.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                                    AND utum.user_type_id in (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher_substitute')
                                    order by s.first_name asc,s.last_name asc");
                                    ?>
                                    <select id="substitute_id" name="substitute_id" class="select form-control" style="width:100%">
                                        <option value="">Select</option>
                                        <?php foreach ($substitute as $sub) { ?>
                                            <option value="<?php echo $sub->user_id ?>">
                                                <?php echo $sub->first_name . " " . $sub->last_name ?> </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="ajaxMsgBot"></div>
                        <button type="submit" class="btn btn-success"> <i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Assign</button>
                        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                        <input type="hidden" name="classtimeid" id="assign_tr_classtimeid">
                        <input type="hidden" name="action" value="assign_teacher_to_classtime">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add modal -->
    <!-- Modal - Map-->
    <div id="modal_mail_detail" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Drop-Off / Pick-Up Map</h5>
                </div>
                <div class="modal-body viewonly">
                    <img src="<?php echo SITEURL . "assets/images/pickup_dropoff_map.jpg" ?>" style="max-width:100%" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Modal -->
    <!-- The Modal -->
    <div class="modal" id="viewModel">
        <div class="modal-dialog modal-xl" style="width:40%;">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Invoice</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="row viewonly">
                        <div class="col-md-6">
                            <label>Invoice #</label> <?php echo $view_invoice_data->invoice_id ?><br />
                            <label>Invoice Date</label><?php echo my_date_changer($view_invoice_data->invoice_date,'c'); ?><br />
                            <label>Invoice Amount</label>
                            <?php if (!empty($view_invoice_data->amount)) { ?> 
                                <?php
                                    if(!empty(get_country()->currency)){
                                        $currency = get_country()->currency;
                                        if(get_country()->abbreviation == 'USA'){
                                            $country_currency_sign = '(USD)';
                                        }
                                    }else{
                                        $currency = '';
                                        $country_currency_sign = '';
                                    } 
                                    echo $currency . $view_invoice_data->amount . $country_currency_sign ?> 
                                <?php } ?><br />
                        </div>
                        <div class="col-md-6 text-right">
                            <label>BILLED TO</label><br />
                            <?php echo $view_invoice_data->father_first_name . ' ' . $view_invoice_data->father_last_name ?><br />
                            <?php echo $view_invoice_data->billing_address_1 ?><br />
                            <?php echo get_country()->country?><br />
                        </div>
                    </div>
                    <div class="row viewonly" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <label>Billing Period</label><?php echo my_date_changer($view_invoice_data->schedule_payment_date,'c') ?><br />
                        </div>
                        <div class="col-md-6">
                            <label>Next Billing Date</label> <?php echo (!empty($get_next_billing_date)) ? my_date_changer($get_next_billing_date,'c') : ""; ?>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <table class="table table-bordered table-sm">
                            <thead style="background: #eee;">
                                <tr>
                                    <th>Item</th>
                                    <th>Student Name</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $students = $db->get_results("select * from ss_student s INNER JOIN ss_user u on s.user_id = u.id INNER JOIN ss_student_session_map ssm on ssm.student_user_id  = u.id where u.is_active = 1 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and s.family_id = '" . $_SESSION['icksumm_uat_login_familyid'] . "'");
                                $studentName = '';
                                foreach ($students as $stu) {
                                    $studentName .= $stu->first_name . " " . $stu->last_name.',';
                                }

                                ?>
                                <tr>
                                    <td>Monthly Payment - <?php echo date('F Y', strtotime($view_invoice_data->schedule_payment_date)) ?></td>
                                    <td><?php echo rtrim($studentName,',') ?></td>
                                    <td><?php 
                                            if(!empty(get_country()->currency)){
                                                $currency = get_country()->currency;
                                            }else{
                                                $currency = '';
                                            }
                                    echo $currency . $view_invoice_data->amount ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-6 text-left">
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(e) {
            $('.class_datatables').DataTable({
                "searching": false,
                "pageLength": false,
                "lengthChange": false,
                "bPaginate": false,
                "serverSide": false,
                "bFilter": true,
                "bInfo": false,
                "bAutoWidth": false
            });
            $('.invoice-datatable').DataTable({
                "searching": false,
                "pageLength": false,
                "lengthChange": false,
                "bPaginate": false,
                "serverSide": false,
                "bFilter": true,
                "bInfo": false,
                "bAutoWidth": false
            });
            accountStatus();
            $('.viewinvoice').on('click', function() {
                $('#viewModel').modal('show');
            });
            //SHOW PICKUP-DROPOFF MAP
            $(document).on('click', '#pickup_dropof_map_link', function() {
                $('#modal_mail_detail').modal('show');
            });

            <?php
            if (check_userrole_by_code('UT01')) { ?>
                //OPEN MODAL WINDOW TO SEND EMAIL
                $(document).on('click', '.send_email', function() {
                    var groupid = $(this).data('groupid');
                    $('#groupid').val(groupid);

                    var groupname = $(this).data('groupname');
                    $('.modal_groupname').html(groupname);

                    $('#modal_email').modal('show');
                });

                //ASSIGN TEACHER TO CLASS TIME - OPEN POPUIP
                $(document).on('click', '.assign_teacher', function() {
                    $('#assign_tr_classtimeid').val($(this).data('classtimeid'));

                    $('#modal_title_classtime').html($(this).data('groupname') + ' (' + $(this).data(
                        'classname') + ')');
                    $('#teacher_id').val($(this).attr('data-teacheruserid'));
                    $('#helper_id').val($(this).attr('data-helperuserid'));
                    $('#substitute_id').val($(this).attr('data-substituteuserid'));
                    $('.select').change();

                    $('#modal_assign_teacher').modal('show');
                });

                //ASSIGN TEACHER TO CLASS TIME - SAVE DATA
                $('#frmAssignTeacher').submit(function(e) {
                    e.preventDefault();

                    if ($('#frmAssignTeacher').valid()) {
                        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
                        $('.spinner').removeClass('hide');

                        var formDate = $(this).serialize();
                        $.post(targetUrl, formDate, function(data, status) {
                            if (status == 'success') {
                                if (data.code == 1) {
                                    $('#teacher_' + data.classtimeid).html(data.teacher_name);
                                    $('#teachername_' + data.classtimeid).removeClass('hide');

                                    $('#helper_' + data.classtimeid).html(data.helper_name);
                                    if ($.trim(data.helper_name) != '') {
                                        $('#helpername_' + data.classtimeid).removeClass('hide');
                                        $('#assign_classtime_' + data.classtimeid).attr(
                                            'data-helperuserid', data.helper_id);
                                    } else {
                                        $('#helpername_' + data.classtimeid).addClass('hide');
                                        $('#assign_classtime_' + data.classtimeid).attr(
                                            'data-helperuserid', '');
                                    }

                                    $('#substitute_' + data.classtimeid).html(data.substitute_name);
                                    if ($.trim(data.substitute_name) != '') {
                                        $('#substitutename_' + data.classtimeid).removeClass(
                                            'hide');
                                        $('#assign_classtime_' + data.classtimeid).attr(
                                            'data-substituteuserid', data.substitute_id);
                                    } else {
                                        $('#substitutename_' + data.classtimeid).addClass('hide');
                                        $('#assign_classtime_' + data.classtimeid).attr(
                                            'data-substituteuserid', '');
                                    }

                                    displayAjaxMsg(data.msg, data.code);
                                    $("#frmAssignTeacher")[0].reset();
                                    $(".select").change();
                                } else {
                                    displayAjaxMsg(data.msg, data.code);
                                }
                            } else {
                                displayAjaxMsg(data.msg);
                            }
                        }, 'json');
                    }
                });
            <?php } ?>
            $('#calendardatatable').DataTable({
                "searching": false,
                "pageLength": false,
                "lengthChange": false,
                "bPaginate": false,
                "serverSide": false,
                "bFilter": true,
                "bInfo": false,
                "bAutoWidth": false
            });
        });

        function childdiv(val) {
            $('.childss').hide();
            $('.childdiv' + val).show();
        }

        function accountStatus() {
            var targetUrl = '<?php echo SITEURL ?>ajax/ajss-payment-account';
            var family_user_id = '<?php echo $family->user_id ?>';
            $.post(targetUrl, {
                family_user_id: family_user_id,
                action: 'account_status'
            }, function(data, status) {
                if (status == 'success') {
                    if (data.code == 1) {
                        $('#totalAmount').html(data.msg);
                    } else {
                        $('#totalAmount').html('0');
                    }
                } else {
                    $('#totalAmount').html('0');
                }

            }, 'json');
        }
    </script>
    <!-- /Content area -->
    <?php include "../footer.php" ?>