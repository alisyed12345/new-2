<?php
include_once "../includes/config.php";

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
    return;
}
//==========================LIST CRON CLENEDAR=====================
if ($_GET['action'] == 'list_cron_calendar') {
    $finalAry = array();
    $cron_calendar = $db->get_results("SELECT id, status, (CASE WHEN status=1 THEN 'Active' ELSE 'Inactive' END) AS is_active,
		DATE_FORMAT(cron_date,'%m/%d/%Y') AS cron_payment_date FROM ss_cron_payment_testing  where status != 2 ORDER BY id DESC", ARRAY_A);
    // for($i=0; $i<count($event_calendar); $i++){
    //     $event_calendar[$i]['fee_amount'] = '$'.($event_calendar[$i]['fee_amount'] + 0);
    // }
    $finalAry['data'] = $cron_calendar;
    echo json_encode($finalAry);
    exit;
}
//==========================ADD CRON CLENEDAR=====================
elseif ($_POST['action'] == 'cron_calendar_add') {

    $cron_date = date('Y-m-d', strtotime($_POST['cron_date_submit']));
    $status = $_POST['status'];
    $cron_calendar = $db->query('insert into ss_cron_payment_testing set cron_date="' . $cron_date . '", status="' . $status . '", 
		created_on="' . date('Y-m-d H:i:s') . '"');
    $cron_calendar_id = $db->insert_id;
    if ($cron_calendar_id > 0) {
        $dispMsg = "<p class='text-success'> Cron payment date added successfully <p>";
        echo json_encode(array('code' => "1", 'msg' => $dispMsg));
        exit;
    } else {
        $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Cron payment date could not be added <p>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
//==========================EDIT CRON CLENEDAR=====================
elseif ($_POST['action'] == 'cron_calendar_edit') {
    $id = $_POST['cron_calendar_id'];
    $cron_date = $_POST['cron_date'];
    $cron_date = date('Y-m-d', strtotime($cron_date));
    $status = $_POST['status'];
    $corn_calendar = $db->query("update ss_cron_payment_testing set cron_date='" . $cron_date . "', status='" . $status . "', updated_on='" . date('Y-m-d H:i:s') . "' where id = '" . $id . "'");
    if ($corn_calendar) {
        $dispMsg = "<p class='text-success'> Cron payment date updated successfully <p>";
        echo json_encode(array('code' => "1", 'msg' => $dispMsg));
        exit;
    } else {
        $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Cron payment date not updated <p>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}
//=====================DELETE CRON CLENEDAR==================
elseif ($_POST['action'] == 'delete_cron_calendar') {
    if (isset($_POST['id'])) {
        $rec = $db->query("update ss_cron_payment_testing set status='2' where id='" . $_POST['id'] . "'");
        if ($rec > 0) {
            echo json_encode(array('code' => "1", 'msg' => 'Cron payment date deleted successfully'));
            exit;
        } else {
            $return_resp = array('code' => "0", 'msg' => 'Cron payment date not deleted');
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
