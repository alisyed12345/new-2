<?php
header("Access-Control-Allow-Origin: *");
if (!empty(get_country()->currency)) {
    $currency = get_country()->currency;
} else {
    $currency = '';
}
$teachers = $db->get_results("SELECT s.user_id, s.first_name, s.last_name FROM ss_staff s INNER JOIN ss_user u ON s.user_id = u.id 
    WHERE u.is_active = 1 
    AND user_type_id in (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02') ORDER BY s.first_name desc, s.last_name ASC", ARRAY_A);

$announcements = $db->get_results("select * from ss_announcements where is_active = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
    order by display_order asc");

/// schedule status = 4 for decline
//  schedule status = 2 for cancel
/// schedule status = 1 for success
// schedule status = 0 for pending
// schedule status = 3 for hold
// schedule status = 5 for skipped

//if (!empty($user_id)) {
$all_student_fees_items = $db->get_results("SELECT sfi.id AS sch_item_id,sfi.original_schedule_payment_date, sfi.schedule_payment_date, 
    sum(sfi.amount) AS final_amount, sfi.schedule_status, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, 
    f.primary_email, pay.credit_card_no, t.id as trxn_id, t.payment_unique_id, t.is_clear_payment, c.description, c.comments, c.user_comments 
    FROM ss_student_fees_items sfi
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    INNER JOIN ss_user u ON u.id = s.user_id
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    INNER JOIN ss_family f ON f.id = s.family_id
    INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
    LEFT JOIN ss_student_fees_transactions ON ss_student_fees_transactions.student_fees_item_id = sfi.id
    LEFT JOIN ss_payment_txns t ON t.id = ss_student_fees_transactions.payment_txns_id
    LEFT JOIN ss_payment_gateway_codes c ON c.code = t.payment_response_code
    WHERE (sfi.schedule_status = 4  OR sfi.schedule_status = 5) AND u.is_active = 1 AND u.is_deleted = 0 AND u.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
    AND pay.default_credit_card =1 AND t.is_clear_payment = 1 GROUP BY sfi.schedule_unique_id ORDER BY sfi.original_schedule_payment_date ASC", ARRAY_A);
// }

for ($i = 0; $i < count((array)$all_student_fees_items); $i++) {

    $payment_trxn = $db->get_row("SELECT payment_txns_id,payment_date, pay.credit_card_no,payment_unique_id FROM ss_student_fees_transactions 
                                INNER JOIN ss_payment_txns ON ss_payment_txns.id = ss_student_fees_transactions.payment_txns_id 
                                INNER JOIN ss_paymentcredentials pay ON pay.id = ss_payment_txns.payment_credentials_id
                                WHERE student_fees_item_id = '" . $all_student_fees_items[$i]['sch_item_id'] . "' 
                                AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                                ORDER BY ss_student_fees_transactions.id DESC LIMIT 1");


    $all_student_fees_items[$i]['parent_name'] =  $all_student_fees_items[$i]['father_first_name'] . ' ' . $all_student_fees_items[$i]['father_last_name'];

    if (!empty($payment_trxn->credit_card_no)) {
        $credit_card_number = $star . substr(str_replace(' ', '', base64_decode($payment_trxn->credit_card_no)), -4);
    } else {
        $credit_card_number = $star . substr(str_replace(' ', '', base64_decode($all_student_fees_items[$i]['credit_card_no'])), -4);
    }

    $all_student_fees_items[$i]['final_amount'] = $currency . ($all_student_fees_items[$i]['final_amount'] + 0);



    if (!empty($payment_trxn->payment_date)) {
        $all_student_fees_items[$i]['payment_date'] =  date('m/d/Y', strtotime($payment_trxn->payment_date));
    } else {
        $all_student_fees_items[$i]['payment_date'] = "";
    }
}

//Group strength
$all_groups = $db->get_results("SELECT id,group_name, max_limit,category,is_regis_open,
    (CASE WHEN is_deleted=1 THEN 'Deleted' WHEN is_active=1 THEN 'Active' ELSE 'Inactive' END) AS status from ss_groups 
    WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'", ARRAY_A);

$group_strength = $db->get_results("SELECT DISTINCT sgm.student_user_id FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sgm.latest = 1 AND u.is_deleted=0 ");
$group_strength_total = count((array)$group_strength);

$group_active_strength = $db->get_results("SELECT DISTINCT sgm.student_user_id FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id 
    WHERE ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sgm.latest = 1 AND  u.is_active =1 AND u.is_deleted=0 ");
$group_active_strength_total = count((array)$group_active_strength);

// for($i=0; $i<count((array)$all_groups); $i++){    
//     $group_strength = $db->get_results("SELECT DISTINCT sgm.student_user_id FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
//     WHERE u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and sgm.group_id = '".$all_groups[$i]['id']."' AND sgm.latest = 1 AND u.is_deleted=0");

//     $group_active_strength = $db->get_results("SELECT DISTINCT sgm.student_user_id FROM ss_studentgroupmap sgm inner join ss_user u on sgm.student_user_id = u.id 
//     WHERE u.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and sgm.group_id = '".$all_groups[$i]['id']."' AND sgm.latest = 1 AND  u.is_active =1 AND u.is_deleted=0");

//     $group_strength_total += count((array)$group_strength);
//     $group_active_strength_total += count((array)$group_active_strength);    
// }
?>
<style>
    #timetable.table>tbody>tr>td {
        padding: 5px;
        vertical-align: top;
    }

    #timetable .roster_hr {
        margin: 2px 0px;
    }

    ul.select2-selection__rendered {
        padding-right: 30px !important;
    }

    ul.select2-selection__rendered:after {
        content: '\e9c5';
        font-family: Icomoon;
        display: inline-block;
        position: absolute;
        top: 50%;
        right: 12px;
        margin-top: -8px;
        font-size: 16px;
        line-height: 1;
        color: inherit;
        -webkit-font-smoothing: antialiased;
        cursor: pointer;
    }

    .table>thead>tr>th {
        padding: 12px 5px;
    }

    .fixed {
        table-layout: fixed;
    }
</style>
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

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Class Schedule </h6>
            </div>
            <div class="panel-body teacher_timetable">
                <div class="table-responsive">
                    <table class="table table-bordered" id="timetable">
                        <thead>
                            <tr>
                                <th>Classes</th>

                                <?php

                                $sql = "select scmp.staff_user_id as user_id,s.first_name,s.last_name,s.gender,scmp.role_for_class,ut.*
                                from ss_staffclasstimemap scmp
                                INNER JOIN ss_staff s ON s.user_id = scmp.staff_user_id
                                INNER JOIN ss_user u ON s.user_id = u.id
                                inner join ss_staff_session_map ssm on s.user_id = ssm.staff_user_id
                                inner join ss_usertypeusermap utmp on utmp.user_id = scmp.staff_user_id
                                inner join ss_usertype ut on ut.id = utmp.user_type_id
                                where  scmp.active = 1
                                AND scmp.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                                AND u.is_active = 1
                                AND u.is_deleted = 0
                                AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'  AND ut.user_type_code = 'UT02' AND ut.is_default = 0 ";

                                $groups = $db->get_results("SELECT * FROM ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                                AND is_active = 1 ORDER BY disp_order ASC");
                                if (!empty($groups)) {
                                    foreach ($groups as $grp) { ?>
                                        <th><?php echo $grp->group_name ?> </th>
                                    <?php }
                                } else { ?>
                                    <th>No Records</th>
                                <?php } ?>

                            </tr>
                        </thead>
                        <tbody>
                            <?php $classes = $db->get_results("SELECT * FROM ss_classes WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and 
                            is_active = 1 ORDER BY disp_order ASC");


                            foreach ($classes as $cls) {
                                $sch = $db->get_results("SELECT time_from,time_to FROM `ss_classtime` WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
            AND class_id = '" . $cls->id . "' AND is_active = '1' GROUP BY time_from,time_to", ARRAY_A);


                                // echo "<pre>";
                                // print_r($sch);
                                // die;
                            ?>
                                <tr>
                                    <td>

                                        <?php /* if(count((array)$sch) == 1){ 
                                        if(trim($sch[0]['time_from']) != '' && trim($sch[0]['time_to']) != ''){
                                            $timeperiod_side = date('h:i a',strtotime($sch[0]['time_from']))." - ".date('h:i a',strtotime($sch[0]['time_to']));
                                        }else{
                                            $timeperiod_side = "";
                                        }
                                        ?>
                                    <div class="cal_timeperiod">
                                        <?php echo $timeperiod_side ?>
                                    </div>
                                    <?php } */ ?>

                                        <strong><?php echo $cls->class_name ?></strong>

                                    </td>
                                    <?php foreach ($groups as $grp) {
                                        $helper_name = '';
                                        $helper_user_id = '';
                                        $substitute_name = '';
                                        $substitute_user_id = '';


                                        $classtime = $db->get_row("SELECT * FROM ss_classtime WHERE group_id = " . $grp->id . " and class_id = " . $cls->id . " 
                    and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and is_active = '1' ");

                                        if (trim($classtime->time_from) != '' && trim($classtime->time_to) != '') {
                                            $timeperiod = date('h:i a', strtotime($classtime->time_from)) . " - " . date('h:i a', strtotime($classtime->time_to));
                                        } else {
                                            $timeperiod = "";
                                        }
                                    ?>
                                        <td>

                                            <?php if ($timeperiod != '') {
                                                //$teacher = $db->get_row("SELECT first_name,last_name,gender FROM ss_staff  WHERE user_id = (select staff_user_id from ss_staffclasstimemap where classtime_id = '".$classtime->id."' and active = 1)");  
                                                $teacher = $db->get_row($sql . " and scmp.role_for_class = 'teacher'
                                                AND scmp.classtime_id = '" . $classtime->id . "'
                                                group by scmp.staff_user_id;");
                                                /*     $teacher = $db->get_row("SELECT user_id,first_name,last_name,gender FROM ss_staff s INNER JOIN ss_user u ON s.user_id = u.id inner join ss_staff_session_map ssm on s.user_id = ssm.staff_user_id
                WHERE u.is_active = 1 AND u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND s.user_id IN (select staff_user_id from ss_staffclasstimemap where role_for_class = 'teacher' AND classtime_id = '" . $classtime->id . "' 
                and active = 1 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and staff_user_id in (SELECT user_id FROM ss_usertypeusermap 
                WHERE user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' AND user_type_subgroup = 'teacher')))"); */

                                                if (!empty($teacher)) {
                                                    $teacher_user_id = $teacher->user_id;
                                                    if ($teacher->gender == "f") {
                                                        $teacher_name = "Sr. " . $teacher->first_name . ' ' . $teacher->last_name;
                                                    } else {
                                                        $teacher_name = "Br. " . $teacher->first_name . ' ' . $teacher->last_name;
                                                    }
                                                } else {
                                                    $teacher_user_id = "";
                                                    $teacher_name = "";
                                                }
                                                /*    echo $sql . " and scmp.role_for_class = 'helper'
                                                AND scmp.classtime_id = '" . $classtime->id . "'
                                                group by scmp.staff_user_id;"; */
                                                $helper = $db->get_results($sql . " and scmp.role_for_class = 'helper'
                                                AND scmp.classtime_id = '" . $classtime->id . "'
                                                group by scmp.staff_user_id;");
                                                /*  $helper = $db->get_results("SELECT user_id,first_name,last_name,gender FROM ss_staff s INNER JOIN ss_user u ON s.user_id = u.id inner join ss_staff_session_map ssm on s.user_id = ssm.staff_user_id
                WHERE u.is_active = 1 AND u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND s.user_id IN (select staff_user_id from ss_staffclasstimemap where role_for_class = 'helper' AND classtime_id = '" . $classtime->id . "' 
                and active = 1 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and staff_user_id in (SELECT user_id FROM ss_usertypeusermap 
                WHERE user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' AND user_type_subgroup = 'teacher_helper')))"); */

                                                if (count((array)$helper)) {
                                                    foreach ($helper as $help) {
                                                        if (trim($helper_user_id) != '') {
                                                            $helper_user_id = $helper_user_id . ",";
                                                        }

                                                        $helper_user_id = $helper_user_id . $help->user_id;

                                                        if (trim($helper_name) != '') {
                                                            $helper_name = $helper_name . ", ";
                                                        }

                                                        if ($help->gender == "f") {
                                                            $helper_name = $helper_name . "Sr. " . $help->first_name . ' ' . $help->last_name;
                                                        } else {
                                                            $helper_name = $helper_name . "Br. " . $help->first_name . ' ' . $help->last_name;
                                                        }
                                                    }
                                                } else {
                                                    $helper_user_id = "";
                                                    $helper_name = "";
                                                }

                                                $substitute = $db->get_results($sql . " and scmp.role_for_class = 'substitute'
                                                AND scmp.classtime_id = '" . $classtime->id . "'
                                                group by scmp.staff_user_id;");
                                                /* $substitute = $db->get_results("SELECT user_id,first_name,last_name,gender FROM ss_staff s INNER JOIN ss_user u ON s.user_id = u.id inner join ss_staff_session_map ssm on s.user_id = ssm.staff_user_id
                WHERE u.is_active = 1 AND u.is_deleted = 0 and ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND s.user_id IN (select staff_user_id from ss_staffclasstimemap where role_for_class = 'substitute' AND classtime_id = '" . $classtime->id . "' 
                and active = 1 and session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and staff_user_id in (SELECT user_id FROM ss_usertypeusermap 
                WHERE user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' AND user_type_subgroup = 'teacher_substitute')))"); */
                                                if (count((array)$substitute)) {
                                                    foreach ($substitute as $subs) {
                                                        if (trim($substitute_user_id) != '') {
                                                            $substitute_user_id = $substitute_user_id . ",";
                                                        }

                                                        $substitute_user_id = $substitute_user_id . $subs->user_id;

                                                        if (trim($substitute_name) != '') {
                                                            $substitute_name = $substitute_name . ", ";
                                                        }

                                                        //$substitute_user_id = $substitute->user_id;       
                                                        if ($subs->gender == "f") {
                                                            $substitute_name = $substitute_name . "Sr. " . $subs->first_name . ' ' . $subs->last_name;
                                                        } else {
                                                            $substitute_name = $substitute_name . "Br. " . $subs->first_name . ' ' . $subs->last_name;
                                                        }
                                                    }
                                                } else {
                                                    $substitute_user_id = "";
                                                    $substitute_name = "";
                                                }
                                            ?>

                                                <?php // if(count((array)$sch) != 1){ 
                                                ?>
                                                <div class="cal_timeperiod">
                                                    <?php echo $timeperiod ?> </div>
                                                <?php // } 
                                                ?>
                                                <div id="teachername_<?php echo $classtime->id ?>">
                                                    <strong>Teacher</strong><br /><span id="teacher_<?php echo $classtime->id ?>">
                                                        <?php echo $teacher_name ?> </span>
                                                </div>
                                                <hr class="roster_hr <?php echo count((array)$helper) ? '' : 'hide' ?>" />
                                                <div id="helpername_<?php echo $classtime->id ?>" class="<?php echo count((array)$helper) ? '' : 'hide' ?>">
                                                    <strong>Assistant 1</strong><br /><span id="helper_<?php echo $classtime->id ?>">
                                                        <?php echo $helper_name ?> </span>
                                                </div>
                                                <hr class="roster_hr <?php echo count((array)$substitute) ? '' : 'hide' ?>" />
                                                <div id="substitutename_<?php echo $classtime->id ?>" class="<?php echo count((array)$substitute) ? '' : 'hide' ?>">
                                                    <strong>Assistant 2</strong><br /> <span id="substitute_<?php echo $classtime->id ?>"> <?php echo $substitute_name ?>
                                                    </span>
                                                </div>

                                                <hr class="roster_hr" />
                                                <div class="timeperiod">
                                                    <a href="javascript:void(0)" id="assign_classtime_<?php echo $classtime->id ?>" class="assign_teacher" data-classtimeid="<?php echo $classtime->id ?>" data-classname="<?php echo $cls->class_name ?>" data-groupname="<?php echo $grp->group_name ?>" data-teacheruserid="<?php echo $teacher_user_id ?>" data-helperuserid="<?php echo $helper_user_id ?>" data-substituteuserid="<?php echo $substitute_user_id ?>">Assign</a>
                                                </div>
                                            <?php } else { ?> Class time not available
                                            <?php } ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Group Class Strength </h6>
            </div>
            <div class="panel-body teacher_timetable">
                <div class="table-responsive">
                    <table class="table table-bordered" id="timetable">
                        <thead>
                            <tr>
                                <th>Classes</th>
                                <?php $groups = $db->get_results("SELECT * FROM ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                                and is_active = 1 ORDER BY disp_order ASC");
                                if (!empty($groups)) {
                                    foreach ($groups as $grp) { ?>
                                        <th>
                                            <?php echo $grp->group_name ?> </th>
                                    <?php }
                                } else { ?>
                                    <th>No Records</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $classes = $db->get_results("SELECT * FROM ss_classes WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                            and is_active = 1 ORDER BY disp_order ASC");
                            foreach ($classes as $cls) { ?>
                                <tr>
                                    <td><strong> <?php echo $cls->class_name ?> </strong> </td>
                                    <?php foreach ($groups as $grp) { ?>
                                        <td>
                                            <?php

                                            $groupClassCount = $db->get_var("SELECT COUNT(*) FROM `ss_studentgroupmap` m inner join ss_user u on m.student_user_id = u.id 
                                         INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
                                         WHERE class_id = '" . $cls->id . "' AND group_id= '" . $grp->id . "' AND latest = 1 and u.is_active =1 
                                         AND u.is_deleted=0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");

                                            echo $groupClassCount;

                                            ?>
                                        </td>
                                    <?php } ?>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="col-md-2">Total Students: <?php echo $group_strength_total; ?></div>
                    <div class="col-md-2">Active Students: <?php echo $group_active_strength_total; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>







<!--     <div class="col-md-6">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Schedule</h6>
            </div>
            <div class="panel-body teacher_timetable">
                <div class="table-responsive">
                    <table class="table table-bordered" id="timetable">
                        <tbody>
                            <tr>
                                <td><strong>10:30 AM - 11:00 AM</strong></td>
                                <td>Quran</td>
                            </tr>
                            <tr>
                                <td><strong>11:10 AM â€“ 11:40 PM</strong></td>
                                <td>Arabic</td>
                            </tr>
                            <tr>
                                <td><strong>11:50 PM â€“ 12:20 PM</strong></td>
                                <td>Islamic Studies</td>
                            </tr>
                            <tr>
                                <td><strong>12:30 PM - 1:00 PM</strong></td>
                                <td>Activities Break</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> -->

<?php
$event_calendar = $db->get_results("SELECT * FROM ss_school_calendar  where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
    and status = 1 ORDER BY program_date asc, program_name DESC");

if (count((array)$event_calendar)) {
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6 class="panel-title">Calendar (<?php echo $_SESSION['icksumm_uat_CURRENT_SESSION_TEXT']; ?>)</h6>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Date</th>
                                    <th>Program / Event</th>
                                </tr>
                                <?php foreach ($event_calendar as $event) { ?>
                                    <tr>
                                        <td><?php echo my_date_changer($event->program_date, 'c') ?></td>
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

<div class="row hide">
    <div class="col-lg-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h5 class="panel-title">Group Attendance Chart</h5>
            </div>
            <div class="panel-body">
                <div class="checkbox content-group">
                    <label>
                        <input type="checkbox" class="group-attendance-sort">
                        Sort in descending order
                    </label>
                </div>
                <div class="chart-container">
                    <div class="chart" id="group-attendance-chart"></div>
                    <div class="help-block text-center">Groups</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title"> Failed Payments List </h6>
            </div>
            <div class="panel-body teacher_timetableffffff">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Parent Name</th>
                                <th>Parent Phone</th>
                                <th>Parent Email</th>
                                <th>Payment Date</th>
                                <th>Payment Amount</th>
                                <th>Payment Failed Reason</th>
                                <th>Action</th>
                            </tr>

                            <?php

                            if (count((array)$all_student_fees_items) > 0) {


                                foreach ($all_student_fees_items as $row) {
                                    if ($row['is_clear_payment'] == 1) {
                                        if (!empty($row['user_comments'])) {
                                            $reason = $$row['user_comments'];
                                        } elseif (!empty($row['comments'])) {
                                            $reason = $row['comments'];
                                        } elseif ($row['schedule_status'] == 5) {
                                            $reason = "Skipped";
                                        } else {
                                            $reason = $row['description'];
                                        }

                            ?>

                                        <tr>
                                            <td><?php echo $row['father_first_name'] . ' ' . $row['father_last_name']; ?></td>
                                            <td><?php
                                                if (!empty($row['father_phone'])) {
                                                    echo internal_phone_check($row['father_phone']);
                                                }
                                                ?></td>
                                            <td><?php echo $row['primary_email']; ?></td>
                                            <td><?php echo my_date_changer($row['payment_date']); ?></td>
                                            <td><?php echo $row['final_amount']; ?></td>
                                            <td><?php echo trim($reason); ?></td>
                                            <!-- <td> <a href="javascript:void:;" class="text-primary action_link  bartext<?php //echo $row['trxn_id'] 
                                                                                                                            ?>  reschedule_payments" data-trxnid="<?php //echo $row['trxn_id'] 
                                                                                                                                                                    ?>" title="Retry a payment">Reschedule
                                                    Payment</a> </td> -->
                                            <td><a href="javascript:;" class="text-danger action_link btnclear clear<?php echo $row['trxn_id'] ?>" onclick="btnclear(<?php echo $row['trxn_id'] ?>)" data-trxnid="<?php echo $row['trxn_id'] ?>" title="Clear">Clear</a></td>
                                        </tr>
                                    <?php } ?>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">Data not found.</td>
                                </tr>

                            <?php } ?>
                        </tbody>
                    </table>

                </div>



                <?php $env = $db->get_var("SELECT `value` FROM ss_config WHERE `key`='ENVIRONMENT'"); ?>
                <h6 class="panel-title"> Payment Environment :
                    <?php if ($env == 'qa' || $env == 'prod' || $env == 'production') {
                        echo "Production";
                    } else {
                        echo "Development";
                    } ?>
                </h6>

            </div>
        </div>
    </div>


</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Live Session Schedule</h6>
            </div>
            <div class="panel-body teacher_timetable">
                <div class="table-responsive">
                    <table class="table table-bordered fixed" id="timetable">
                        <thead>
                            <tr>
                                <th>Classes</th>
                                <?php $groups = $db->get_results("SELECT * FROM ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                                and is_active = 1 ORDER BY disp_order ASC");
                                foreach ($groups as $grp) {
                                ?>
                                    <th>
                                        <?php echo $grp->group_name ?> </th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $classes = $db->get_results("SELECT * FROM ss_classes WHERE session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                            and is_active = 1 ORDER BY disp_order ASC");
                            foreach ($classes as $cls) {
                            ?>
                                <tr>
                                    <td style="width:10%;"><strong> <?php echo $cls->class_name ?> </strong></td>
                                    <?php foreach ($groups as $grp) {

                                        $classes_online = $db->get_row("SELECT * FROM ss_classes_online WHERE status <> 2 AND group_id = " . $grp->id . " and class_id = " . $cls->id . "");

                                    ?>
                                        <td style="border-width:0.833333pt;border-style:solid;border-color:rgb(154,154,154);vertical-align:top;padding:1pt 4pt;overflow:hidden;width:10%;">


                                            <?php if (!empty($classes_online->meeting_url) && !empty($classes_online->meeting_id) && !empty($classes_online->meeting_password)) { ?>

                                                Zoom Meeting Link:&nbsp;
                                                <a href="<?php echo $row->meeting_url ?>" style="text-decoration-line:none" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://zoom.us/j/5891547586&amp;source=gmail&amp;ust=1598549948327000&amp;usg=AFQjCNFFjbidokLn-pyGEH468WakI2dRvg"><span style="font-size:9pt;font-family:Arial;background-color:transparent;font-variant-numeric:normal;font-variant-east-asian:normal;text-decoration-line:underline;vertical-align:baseline;white-space:pre-wrap;word-break: break-word;"><?php echo $classes_online->meeting_url ?></span></a>
                                                </p>
                                                Meeting ID: <?php echo $classes_online->meeting_id ?>
                                                <p> Password : <?php echo $classes_online->meeting_password ?> </p>

                                            <?php } ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
                    <h5 class="modal-title">Assign Teacher / Assistant 1 / Assistant 2 - <span id="modal_title_classtime"></span> </h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Teacher:<span class="mandatory">*</span></strong>
                            <div class="form-group multi-select-full">
                                <?php
                                $teacher = $db->get_results("SELECT DISTINCT(s.user_id), s.first_name,s.last_name FROM ss_staff s 
                                INNER JOIN ss_user u ON s.user_id = u.id INNER JOIN ss_staff_session_map ssm1 ON s.user_id = ssm1.staff_user_id
                                INNER JOIN ss_usertypeusermap utum ON u.id = utum.user_id 
                                WHERE u.is_active = 1 AND u.is_deleted = 0  AND ssm1.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                AND utum.user_type_id in (SELECT id FROM ss_usertype 
                WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher') order by s.first_name asc,s.last_name asc"); ?>
                                <select id="teacher_id" name="teacher_id" class="select form-control required" style="width:100%">
                                    <option value="" id="required_remove">Select</option>
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
                            <strong>Assistant 1: </strong>
                            <div class="form-group multi-select-full">
                                <?php $helper = $db->get_results("SELECT DISTINCT(s.user_id), s.first_name,s.last_name FROM ss_staff s INNER JOIN ss_user u 
                ON s.user_id = u.id INNER JOIN ss_staff_session_map ssm1 ON s.user_id = ssm1.staff_user_id INNER JOIN ss_usertypeusermap utum ON u.id = utum.user_id 
                WHERE u.is_active = 1 AND u.is_deleted = 0  AND ssm1.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                AND utum.user_type_id in (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher') 
                order by s.first_name asc,s.last_name asc"); ?>
                                <select id="helper_id" name="helper_id[]" multiple="multiple" class="select form-control" style="width:100%;">
                                    <!--<option value="">Select</option>-->
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
                            <strong>Assistant 2: </strong>
                            <div class="form-group multi-select-full">
                                <?php $substitute = $db->get_results("SELECT DISTINCT(s.user_id), s.first_name,s.last_name FROM ss_staff s INNER JOIN ss_user u 
                ON s.user_id = u.id INNER JOIN ss_staff_session_map ssm1 ON s.user_id = ssm1.staff_user_id INNER JOIN ss_usertypeusermap utum ON u.id = utum.user_id 
                WHERE u.is_active = 1 AND u.is_deleted = 0 AND ssm1.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                AND utum.user_type_id in (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' and user_type_subgroup = 'teacher') 
                order by s.first_name asc,s.last_name asc"); ?>
                                <select id="substitute_id" name="substitute_id[]" multiple="multiple" class="select form-control" style="width:100%;">
                                    <!--<option value="">Select</option>-->
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
                    <div class="row">
                        <div class="col-md-9">
                            <strong id="ajaxMsgBot" class='text-danger'></strong>
                            <div class="ajaxMsgBot"></div>

                        </div>
                        <div class="col-md-3 text-right">
                            <button type="submit" class="btn btn-success"> <i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Assign</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                            <input type="hidden" name="classtimeid" id="assign_tr_classtimeid">
                            <input type="hidden" name="action" value="assign_teacher_to_classtime">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add modal -->
<!-- Modal - Send Email -->
<div id="modal_email" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="frmEmailToGroup" id="frmEmailToGroup" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Send Email To Group <span class="modal_groupname"></span> </h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" class="form-control required" id="subject" name="subject" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>CC</label>
                                <input type="text" class="form-control" id="cc" name="cc" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Message</label>
                                <textarea class="form-control required" id="message" name="message"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="ajaxMsgBot"></div>
                    <button type="submit" class="btn btn-success"> <i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Send</button>
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <input type="hidden" name="groupid" id="groupid">
                    <input type="hidden" name="action" value="send_email_to_group">
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Modal - Send Email -->

<!-- Modal - Send Text -->
<div id="modal_text" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="frmTextToGroup" id="frmTextToGroup" class="form-validate-jquery" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Send Text To Group <span class="modal_groupname"></span> </h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Message</label>
                                <textarea class="form-control required" id="message" name="message"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="ajaxMsgBot"></div>
                    <button type="submit" class="btn btn-success">Send</button>
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <input type="hidden" name="groupid" id="groupid">
                    <input type="hidden" name="action" value="send_text_to_group">
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Modal - Send Text -->

<script>
    $(document).ready(function() {


        //Clear Payment Failed
        $(document).on('click', '.btnclear', function() {
            var trxnid = $(this).data('trxnid');
            $.confirm({
                title: 'Confirm!',
                content: 'Do you want to clear failed payment?',
                buttons: {
                    confirm: function() {
                        $('.clear' + trxnid).html('<span style="color:black;">Processing...</span>');
                        $.post('<?php echo SITEURL ?>ajax/ajss-schedule-payment', {
                            trxnid: trxnid,
                            action: 'failed_payment_clear'
                        }, function(data, status) {
                            if (status == 'success') {
                                if (data.code == 1) {
                                    $('.clear' + trxnid).html('Clear');
                                    location.reload();
                                    displayAjaxMsg(data.msg, data.code);
                                } else {
                                    displayAjaxMsg(data.msg, data.code);
                                }
                            } else {
                                displayAjaxMsg(data.msg);
                            }
                        }, 'json');
                    },
                    cancel: function() {}
                }
            });
        });


        <?php
        if ($_SESSION['icksumm_uat_login_usertypecode'] == 'UT01') {
        ?>

            //OPEN MODAL WINDOW TO SEND EMAIL
            $(document).on('click', '.send_email', function() {
                var groupid = $(this).data('groupid');
                $('#groupid').val(groupid);

                var groupname = $(this).data('groupname');
                $('.modal_groupname').html(groupname);

                $('#modal_email').modal('show');
            });


            //REschedule payments
            $(document).on('click', '.reschedule_payments', function(data, status) {
                if (confirm('Do you want to reschedule payment?')) {

                    var trxnid = $(this).data('trxnid');
                    $('.bartext' + trxnid).html('<span style="color:black;">Processing...</span>');
                    $.post('<?php echo SITEURL ?>ajax/ajss-schedule-payment', {
                        trxnid: trxnid,
                        action: 'reschedule_payment_items'
                    }, function(data, status) {
                        if (status == 'success') {
                            $('.bartext' + trxnid).html('Reschedule Payment');
                            alert(data.msg);
                            location.reload();
                        } else {
                            alert(data.msg);
                        }
                    }, 'json');
                }
            });

            //ASSIGN TEACHER TO CLASS TIME - OPEN POPUIP
            $(document).on('click', '.assign_teacher', function() {
                $('#assign_tr_classtimeid').val($(this).data('classtimeid'));

                $('#modal_title_classtime').html($(this).data('groupname') + ' (' + $(this).data(
                    'classname') + ')');
                $('#teacher_id').val($(this).attr('data-teacheruserid'));

                var helperuserid_ary = $(this).attr('data-helperuserid').split(',');
                $('#helper_id').val(helperuserid_ary).trigger('change');
                //$('#helper_id').val($(this).attr('data-helperuserid'));

                var substituteuserid_ary = $(this).attr('data-substituteuserid').split(',');
                $('#substitute_id').val(substituteuserid_ary).trigger('change');
                //$('#substitute_id').val($(this).attr('data-substituteuserid'));

                $('.select').change();

                $('#modal_assign_teacher').modal('show');

                $("#teacher_id").change(function() {
                    if ($("#teacher_id").val() != '' && $("#teacher_id").val() != null) {
                        $('.validation-error-label').css('color', '#fff');
                    } else {
                        $('.validation-error-label').css('color', '#f44336');
                    };
                });

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
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                displayAjaxMsg(data.msg, data.code);
                            }
                        } else {
                            displayAjaxMsg(data.msg);
                        }
                    }, 'json');
                }
            });

            //SEND EMAIL TO GROUP
            $('#frmEmailToGroup').submit(function(e) {
                e.preventDefault();

                if ($('#frmEmailToGroup').valid()) {
                    var targetUrl = '<?php echo SITEURL ?>ajax/ajss-messages';
                    $('.spinner').removeClass('hide');

                    var formDate = $(this).serialize();
                    $.post(targetUrl, formDate, function(data, status) {
                        if (status == 'success') {
                            if (data.code == 1) {
                                displayAjaxMsg(data.msg, data.code);
                                $("#frmEmailToGroup")[0].reset();
                            } else {
                                displayAjaxMsg(data.msg, data.code);
                            }
                        } else {
                            displayAjaxMsg(data.msg);
                        }
                    }, 'json');
                }
            });

            //OPEN WINDOW TO SEND TEXT
            $(document).on('click', '.send_text', function() {
                var groupid = $(this).data('groupid');
                $('#groupid').val(groupid);

                var groupname = $(this).data('groupname');
                $('.modal_groupname').html(groupname);

                $('#modal_text').modal('show');
            });

            //SEND TEXT TO GROUP
            $('#frmTextToGroup').submit(function(e) {
                e.preventDefault();

                if ($('#frmTextToGroup').valid()) {
                    var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
                    $('.spinner').removeClass('hide');

                    var formDate = $(this).serialize();
                    $.post(targetUrl, formDate, function(data, status) {
                        if (status == 'success') {
                            if (data.code == 1) {
                                displayAjaxMsg(data.msg, data.code);
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

    });
</script>