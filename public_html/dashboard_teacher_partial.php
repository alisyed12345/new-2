<?php

/*$groups_atten = $db->get_results("SELECT g.*,time_from,time_to FROM ss_groups g INNER JOIN ss_classtime ct ON g.id = ct.group_id 
WHERE  g.is_active=1 AND g.is_deleted=0 AND ct.id IN (SELECT classtime_id FROM ss_staffclasstimemap 
WHERE staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' AND active = 1) ORDER BY group_name ASC");
*/


$groups_atten = $db->get_results("SELECT g.*,c.id as class_id, c.class_name, ctm.time_from, ctm.time_to 
FROM ss_staffclasstimemap m INNER JOIN  ss_classtime ctm ON ctm.id = m.classtime_id 
INNER JOIN ss_groups g ON g.id = ctm.group_id 
INNER JOIN ss_classes c ON c.id = ctm.class_id  
WHERE g.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' and c.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
and m.staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' AND m.active = 1 AND c.is_active=1 AND g.is_active =1 AND g.is_deleted =0 AND ctm.is_active=1 group by ctm.group_id,ctm.class_id");

$announcements = $db->get_results("select * from ss_announcements where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
and is_active = 1 order by display_order asc");
?>
<style>
#timetable.table>tbody>tr>td {
    padding: 5px;
    vertical-align: top;
}

#timetable .roster_hr {
    margin: 2px 0px;
}
#timetable.table > thead > tr > th {
  padding: 5px;
  vertical-align: top;
}
.fixed{
    table-layout: fixed;
}
</style>

<!-- ANNOUNCEMENTS -->
<?php if(count((array)$announcements)){ ?>
<div class="row">
    <div class="col-md-12">
        <marquee class="announcement">
            <?php foreach($announcements as $ann){ ?>
            <span><i class="icon-bell3"></i> <?php echo $ann->message ?></span>
            <?php } ?>
        </marquee>
    </div>
</div>
<?php } ?>

<!-- CLASS SCHEDULE -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat ">
            <div class="panel-heading">
                <h6 class="panel-title">Class Schedule</h6>
            </div>
            <div class="panel-body teacher_timetable">
                <div class="table-responsive">
                    <table class="table table-bordered" id="timetable">
                        <thead>
                            <tr>
                                <th>Subject</th>
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
                                
                                $groups = $db->get_results("SELECT * FROM ss_groups 
                                where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active = 1 ORDER BY disp_order ASC");
                        foreach ($groups as $grp) { ?>
                                <th><?php echo $grp->group_name ?></th>
                                <?php }?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $classes = $db->get_results("SELECT * FROM ss_classes 
                            WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active = 1 ORDER BY disp_order ASC");
                    foreach ($classes as $cls) {
                        $sch = $db->get_results("SELECT time_from,time_to FROM `ss_classtime` 
                        WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND class_id = '".$cls->id."' GROUP BY time_from,time_to",ARRAY_A); 
                        ?>
                            <tr>
                                <td><strong><?php echo $cls->class_name ?></strong>
                                    <?php /* if(count((array)$sch) == 1){ 
				if(trim($sch[0]['time_from']) != '' && trim($sch[0]['time_to']) != ''){
					$timeperiod_side = date('h:i a',strtotime($sch[0]['time_from']))." - ".date('h:i a',strtotime($sch[0]['time_to']));
				}else{
					$timeperiod_side = "";
				}
				?>
                                    <div class="cal_timeperiod"><?php echo $timeperiod_side ?></div>
                                    <?php } */ ?>
                                </td>
                                <?php foreach ($groups as $grp) {
					$helper_name = '';
					$substitute_name = '';
                   
					$classtime = $db->get_row("SELECT * FROM ss_classtime WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND group_id = " . $grp->id . " and class_id = " . $cls->id);
                    $staffclasstime = $db->get_row("SELECT * FROM ss_staffclasstimemap WHERE session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND active = '1' AND classtime_id = '".$classtime->id."'");
                    if(isset($staffclasstime->id) && !empty($staffclasstime->id)){
                        if (trim($classtime->time_from) != '' && trim($classtime->time_to) != '') {
                            $timeperiod = date('h:i a', strtotime($classtime->time_from)) . " - " . date('h:i a', strtotime($classtime->time_to));
                        } else {
                            $timeperiod = "";
                        }
                    }else {
                        $timeperiod = "";
                    }
				?>
                                <td>
                                    <!-- <?php //THIS CONDITION ADDED TO HIDE CLASS TEACHER NAME FROM SOCIAL HOUR TIME - TEMPORARILY FOR ONLINE CLASSES
									//if($cls->id != 4){ ?> -->
                                    <?php if ($timeperiod != '') {		
				/* 	$teacher = $db->get_row("SELECT user_id,first_name,last_name,gender FROM ss_staff INNER JOIN ss_user u 
                    ON user_id = u.id WHERE  u.is_active = 1 AND u.is_deleted = 0 and user_id 
                    IN (select staff_user_id from ss_staffclasstimemap 
                    where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND classtime_id = '".$classtime->id."' 
                    and active = 1 and session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                    and staff_user_id in (SELECT user_id FROM ss_usertypeusermap 
                    WHERE user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT02' AND user_type_subgroup = 'teacher')))");   */
                    $teacher = $db->get_row($sql . " and scmp.role_for_class = 'teacher'
                    AND scmp.classtime_id = '" . $classtime->id . "'
                    group by scmp.staff_user_id;");


					if(!empty($teacher)){
					  if($teacher->gender == "f"){
						$teacher_name = "Sr. ".$teacher->first_name.' '.$teacher->last_name;
					  }else{
						$teacher_name = "Br. ".$teacher->first_name.' '.$teacher->last_name;
					  }
					}else{
						 $teacher_name = "";
					}
/* 
					$helper = $db->get_results("SELECT user_id,first_name,last_name,gender 
                    FROM ss_staff s INNER JOIN ss_user u ON s.user_id = u.id WHERE u.is_active = 1 AND u.is_deleted = 0 AND user_id IN (select staff_user_id 
					from ss_staffclasstimemap sctm where sctm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND role_for_class = 'helper' 
                    AND classtime_id = '".$classtime->id."' and active = 1 and 
					staff_user_id in (SELECT user_id FROM ss_usertypeusermap WHERE user_type_id = (SELECT id FROM ss_usertype WHERE 
					user_type_code = 'UT02' AND user_type_subgroup = 'teacher_helper')))"); */
                    $helper = $db->get_results($sql . " and scmp.role_for_class = 'helper'
                    AND scmp.classtime_id = '" . $classtime->id . "'
                    group by scmp.staff_user_id;");
					if(count((array)$helper)){
						foreach($helper as $help){
							if(trim($helper_name) != ''){
								$helper_name = $helper_name.", ";
							}
							
							if($help->gender == "f"){
								$helper_name = $helper_name."Sr. ".$help->first_name.' '.$help->last_name;
							}else{
								$helper_name = $helper_name."Br. ".$help->first_name.' '.$help->last_name;
							}
						}
					}else{
					  $helper_name = "";
					}
				
    				/* $substitute = $db->get_results("SELECT user_id,first_name,last_name,gender FROM ss_staff 
                    INNER JOIN ss_user u ON user_id = u.id WHERE  u.is_active = 1 AND u.is_deleted = 0 and user_id IN (select staff_user_id 
					from ss_staffclasstimemap sctm where sctm.session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
                    AND role_for_class = 'substitute' AND classtime_id = '".$classtime->id."' and active = 1 
                    and staff_user_id in (SELECT user_id FROM ss_usertypeusermap WHERE user_type_id = (SELECT id FROM ss_usertype WHERE 
					user_type_code = 'UT02' AND user_type_subgroup = 'teacher_substitute')))");   */

                    $substitute = $db->get_results($sql . " and scmp.role_for_class = 'substitute'
                    AND scmp.classtime_id = '" . $classtime->id . "'
                    group by scmp.staff_user_id;");
                    
					if(count((array)$substitute)){
						foreach($substitute as $subs){
							if(trim($substitute_name) != ''){
								$substitute_name = $substitute_name.", ";
							}
							
							if($subs->gender == "f"){
								$substitute_name = $substitute_name."Sr. ".$subs->first_name.' '.$subs->last_name;
							}else{
								$substitute_name = $substitute_name."Br. ".$subs->first_name.' '.$subs->last_name;
							}
						}
					}else{
					  $substitute_name = "";
					}
					 
					?>
                                    <div class="cal_timeperiod"><?php echo $timeperiod ?></div>
                                    <?php // }?>
                                    <div class="<?php echo !empty($teacher) ? '' : 'hide' ?>"><strong>Teacher:</strong>
                                        <span><?php echo $teacher_name ?></span>
                                    </div>
                                    <hr class="roster_hr <?php echo count((array)$helper)?'':'hide' ?>" />
                                    <div class="<?php echo count((array)$helper)?'':'hide' ?>"> <strong>Assistant 1:</strong> <span>
                                            <?php echo $helper_name ?> </span> </div>
                                    <hr class="roster_hr <?php echo count((array)$substitute)?'':'hide' ?>" />
                                    <div class="<?php echo count((array)$substitute)?'':'hide' ?>">
                                        <strong>Assistant 2:</strong>
                                        <span>
                                            <?php echo $substitute_name ?> </span>
                                    </div>
                                    <?php } else { ?>
                                    Class time not available
                                    <?php } ?>
                                    <!-- <?php // } //THIS CONDITION ADDED TO HIDE CLASS TEACHER NAME FROM SOCIAL HOUR TIME - TEMPORARILY FOR ONLINE CLASSES ?> -->
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

<!-- TODAYS ATTENDANCE -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Today’s Attendance</h6>
            </div>
            <div class="panel-body teacher_timetable">
                <div class="row">
                    <?php
if (count((array)$groups_atten)) {
    foreach ($groups_atten as $grp) {?>
                    <div class="col-lg-3 col-sm-6 atte_group_name"> <a
                            href="<?php echo SITEURL ?>attendance/attendance_today?id=<?php echo md5('g' . $grp->id) ?>&cid=<?php echo md5('c' . $grp->class_id) ?>"
                            class="btn bg-warning-400 btn-block">
                            <?php echo '<div style="font-size:18px">' . $grp->group_name . '<br>('. $grp->class_name .') </div>' ?>
                            <?php echo date('h:i a', strtotime($grp->time_from)) . ' - ' . date('h:i a', strtotime($grp->time_to)) ?>
                        </a> </div>
                    <?php }
} else {?>
                    <div class="col-lg-12">No class is scheduled for today</div>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EVENT CALENDAR -->
<?php
    $event_calendar =$db->get_results("SELECT * FROM ss_school_calendar  where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' 
    and status = 1 ORDER BY program_date asc, program_name DESC");

    if(count((array)$event_calendar)){
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
                            <?php foreach($event_calendar as $event){ ?>
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

<!-- LIVE SESSION -->
<?php $is_class = $db->get_results("SELECT cls.id as classid,cls.class_name, grp.group_name,grp.id as groupid FROM `ss_staffclasstimemap`stm INNER JOIN ss_classtime ct on ct.id= stm.classtime_id INNER JOIN ss_classes cls ON cls.id = ct.class_id INNER JOIN ss_groups grp ON grp.id = ct.group_id where stm.staff_user_id='".$_SESSION['icksumm_uat_login_userid']."' AND ct.is_active=1 ANd ct.session='".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND cls.is_active=1 AND grp.is_active=1 AND grp.is_deleted=0"); 

$total = [];
if(count((array)$is_class)>0)
{
    $total_online = []; $is_unique_class =[];
    for($x=0;$x<count($is_class);$x++){
     
     if(!in_array($is_class[$x]->classid, $is_unique_class)){
        $is_unique_class[] = $is_class[$x]->classid;
        $total_online['group_name'] = $is_class[$x]->group_name;
        $total_online['class_name'] = $is_class[$x]->class_name;
        $total_online['groupid'] = $is_class[$x]->groupid; 
        $total_online['classid'] = $is_class[$x]->classid;     
        $online_class = $db->get_results("SELECT cls.group_id,cls.meeting_url, cls.meeting_id, cls.meeting_password
        FROM ss_classes_online cls where status = 1 AND class_id ='".$is_class[$x]->classid."' ");
        if($online_class){
        $total_online['groups']= $online_class;
        $total[] = $total_online; 
        }
     }   
    }
   
    
    $total_grpids = array_column($total, 'groupid');
    $total_grpids = array_unique($total_grpids);
    $teacher_group = implode(',', $total_grpids);
    $groups = $db->get_results("SELECT id,group_name FROM ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                                and is_active = 1 and id IN ($teacher_group) ORDER BY disp_order ASC"); 
}


if(count((array)$total)>0){
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Live Session Schedule </h6>
            </div>
            <div class="panel-body teacher_timetable">
                <?php  
                $classes = $db->get_results("select * from ss_classes where session = '".$_SESSION['icksumm_uat_CURRENT_SESSION']."' AND is_active=1 ORDER BY disp_order ASC"); ?> 

                <table class="table table-bordered fixed" id="live_session">
                    <thead>
                        <tr>
                            <th
                                style="border-width:0.833333pt;border-style:solid;border-color:rgb(154,154,154);vertical-align:top;padding:1pt 4pt;overflow:hidden">Classes
                            </th>
                            
                            <?php foreach($groups as $val){  ?>
                            <th style="border-width:0.833333pt;border-style:solid;border-color:rgb(154,154,154);vertical-align:top;padding:1pt 4pt;overflow:hidden">
                                <?php echo $val->group_name; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>

                    
                        <?php
                         
                         foreach($total as $row){  ?>
                        <tr>
                            <th
                                style="border-width:0.833333pt;border-style:solid;border-color:rgb(154,154,154);vertical-align:top;padding:1pt 4pt;overflow:hidden">
                                <?php echo $row['class_name'] ?></th>
                         
                           <?php foreach($row['groups'] as $grp){ ?>
                            <td
                                style="border-width:0.833333pt;border-style:solid;border-color:rgb(154,154,154);vertical-align:top;padding:1pt 4pt;overflow:hidden">
                                Zoom Meeting Link:&nbsp;
                                <a href="<?php echo $grp->meeting_url ?>" style="text-decoration-line:none"
                                    target="_blank"
                                    data-saferedirecturl="https://www.google.com/url?q=https://zoom.us/j/5891547586&amp;source=gmail&amp;ust=1598549948327000&amp;usg=AFQjCNFFjbidokLn-pyGEH468WakI2dRvg"><span
                                        style="font-size:9pt;font-family:Arial;background-color:transparent;font-variant-numeric:normal;font-variant-east-asian:normal;text-decoration-line:underline;vertical-align:baseline;white-space:pre-wrap;word-break: break-word;"><?php echo $grp->meeting_url; ?></span></a>
                                </p>
                                Meeting ID: <?php echo $grp->meeting_id; ?>
                                <p> Password : <?php echo $grp->meeting_password; ?> </p>
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

<?php } ?>
<script type="text/javascript">
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
        $('#live_session').DataTable({
                "searching": false,
                "pageLength": false,
                "lengthChange": false,
                "bPaginate": false,
                "serverSide": false,
                "bFilter": true,
                "bInfo": false,
                "bAutoWidth": false
            });
</script>