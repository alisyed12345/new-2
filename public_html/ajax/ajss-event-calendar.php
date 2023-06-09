<?php
include_once "../includes/config.php";

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}
//==========================LIST EVENT CLENEDAR=====================
if ($_GET['action'] == 'list_event_calendar') {
    $finalAry = array();
    $event_calendar = $db->get_results("SELECT id, status, program_name,(CASE WHEN status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active,
		 program_date FROM ss_school_calendar  where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
		and status != 2 ORDER BY id DESC", ARRAY_A);
    // for($i=0; $i<count($event_calendar); $i++){
    //     $event_calendar[$i]['fee_amount'] = '$'.($event_calendar[$i]['fee_amount'] + 0);
    // }
    // echo "<pre>";
    // print_r($event_calendar);
    // die;
if(!empty($event_calendar)){    
foreach($event_calendar as $key => $ec){
    $event_calendar[$key]['program_date'] = my_date_changer($ec['program_date']);

}
}
    $finalAry['data'] = $event_calendar;
    echo json_encode($finalAry);
    exit;
}
//==========================ADD EVENT CLENEDAR=====================
elseif ($_POST['action'] == 'event_calendar_add') {

    $event_date = date('Y-m-d', strtotime($_POST['event_date_submit']));
    $event_name = $_POST['event_name'];
    $status = $_POST['status'];
    $event_calendar = $db->query('insert into ss_school_calendar set program_date="' . $event_date . '", status="' . $status . '",
		program_name="' . $event_name . '", created_by_user_id="' . $_SESSION['icksumm_uat_login_userid'] . '",
		session = "' . $_SESSION['icksumm_uat_CURRENT_SESSION'] . '",
		created_on="' . date('Y-m-d H:i:s') . '", updated_by_user_id="' . $_SESSION['icksumm_uat_login_userid'] . '", updated_on="' . date('Y-m-d H:i:s') . '"');
    $event_calendar_id = $db->insert_id;
    if ($event_calendar_id > 0) {
        $dispMsg = "<p class='text-success'> Event added successfully <p>";
        echo json_encode(array('code' => "1", 'msg' => $dispMsg));
        exit;
    } else {
        $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Event could not be added <p>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
//==========================EDIT EVENT CLENEDAR=====================
elseif ($_POST['action'] == 'event_calendar_edit') {
    $id = $_POST['event_calendar_id'];
    $event_date = $_POST['event_date_submit'];
    $event_date = date('Y-m-d', strtotime($event_date));
    $event_name = $_POST['event_name'];
    $status = $_POST['status'];
    $event_calendar = $db->query("update ss_school_calendar set program_date='" . $event_date . "', status='" . $status . "',
	   session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "',
		program_name='" . $event_name . "', updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $id . "'");
    if ($event_calendar) {
        $dispMsg = "<p class='text-success'> Event updated successfully <p>";
        echo json_encode(array('code' => "1", 'msg' => $dispMsg));
        exit;
    } else {
        $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Event not updated <p>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
//=====================DELETE EVENT CLENEDAR==================
elseif ($_POST['action'] == 'delete_event_calendar') {
    if (isset($_POST['id'])) {
        $rec = $db->query("update ss_school_calendar set status='2' where id='" . $_POST['id'] . "'");
        if ($rec > 0) {
            echo json_encode(array('code' => "1", 'msg' => 'Event deleted successfully'));
            exit;
        } else {
            $return_resp = array('code' => "0", 'msg' => 'Event not deleted');
            CreateLog($_REQUEST, json_encode($return_resp));
            echo json_encode($return_resp);
            exit;
        }
    } else {
        $return_resp = array('code' => "0", 'msg' => 'Error: Process failed');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
