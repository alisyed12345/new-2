<?php
//LIVE - PROD SITE
//set_include_path('/webroot/b/a/bayyan005/icksaturdayqa.click2clock.com/www/includes/');

//include_once "config.php";

include_once "../includes/config.php";

$current_dateTime = date('Y-m-d H:i:s');

//schedule_status : 0 Pending 
//schedule_status : 1 Success 
//schedule_status : 2 Cancel 
//schedule_status : 3 Hold 
//schedule_status : 4 Decline 
//schedule_status : 5 Skipped 

$created_user_id = $db->get_var("SELECT u.id FROM `ss_user` u
INNER JOIN ss_usertypeusermap utm ON u.id = utm.user_id
INNER JOIN ss_usertype t ON t.id = utm.user_type_id 
WHERE t.user_type_code = 'UT00'  limit 1 ");

$current_session = $db->get_row("select * from ss_school_sessions where current = 1 AND status = 1");

//Skipped Payment Status Change
$Query = 'SELECT id,schedule_payment_date FROM ss_student_fees_items sfi where ADDTIME(sfi.schedule_payment_date, "'.CRON_PAYMENT_END_TIME.'") < "'.$current_dateTime.'"  AND sfi.session="'.$current_session->id.'" AND retry_count = 0 AND sfi.schedule_status = 0 AND sfi.schedule_status != 5 AND sfi.schedule_notification = 0';
$skipped_payments = $db->get_results($Query);
// echo "<pre>";
// print_r($skipped_payments);
// die;
if(count((array)$skipped_payments) > 0){
    try {
        $db->query('BEGIN');
        $count = 0;
        $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Pending <br> <strong>Current Status : </strong>Skipped";
        foreach($skipped_payments as $val){

            $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $val->id . "' , current_status=0, new_status=5, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session->id . "' , schedule_payment_date = '" .$val->schedule_payment_date. "'");

            $student_fees_items = $db->query("update ss_student_fees_items set schedule_status = 5, updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $val->id . "' ");

            $payment_sch_item_cron_id = $db->get_var("SELECT id FROM ss_payment_sch_item_cron WHERE FIND_IN_SET(".$val->id.", sch_item_ids)");
            if($payment_sch_item_cron_id > 0){
                $delete_sch_cron = $db->query("delete from ss_payment_sch_item_cron where id = ".$payment_sch_item_cron_id." and schedule_status=0");
            }

            if($student_fees_items && $db->insert_id > 0){
                $count++;
            }
        }

        if ($count === count((array)$skipped_payments)) {
            $db->query('COMMIT');
            echo "Success";
        }else{
            $db->query('ROLLBACK');
            echo "Failed";
        }

    } catch (Exception $e) {
        $db->query('ROLLBACK');
        $msg = $e->getMessage();
        CreateLog($_REQUEST, json_encode($msg));
        echo json_encode($msg);
    }

}



//Skipped Payment Status Change to Pending
if(date('Y-m-d').' '.CRON_PAYMENT_START_TIME < $current_dateTime){

    $skipped_payments_pending = $db->get_results('SELECT id,schedule_payment_date FROM ss_student_fees_items sfi where sfi.session="'.$current_session->id.'" AND sfi.schedule_status = 5');
    // echo "<pre>";
    // print_r($skipped_payments_pending);
    // die;
    if(count((array)$skipped_payments_pending) > 0){
        try {
            $db->query('BEGIN');
            $counts = 0;
            $comments = "<strong>Schedule Payments Status  </strong><br><strong>Preview Status : </strong>Skipped  <br> <strong>Current Status : </strong>Pending";
            foreach($skipped_payments_pending as $pay){
    
                $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $pay->id . "' , current_status=5, new_status=0, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $created_user_id . "', session ='" . $current_session->id . "', schedule_payment_date = '" .$pay->schedule_payment_date. "'");
    
                $student_fees_items_histry = $db->query("update ss_student_fees_items set schedule_payment_date='" . date('Y-m-d'). "',schedule_status = 0, schedule_notification=0, updated_at = '" . date('Y-m-d H:i:s') . "' where id = '" . $pay->id . "' ");
                
                $payment_sch_item_cron_id = $db->get_var("SELECT id FROM ss_payment_sch_item_cron WHERE FIND_IN_SET(".$pay->id.", sch_item_ids)");
                if($payment_sch_item_cron_id > 0){
                    $delete_sch_cron = $db->query("delete from ss_payment_sch_item_cron where id =" . $payment_sch_item_cron_id. " and schedule_status=0 ");
                }

                if($student_fees_items_histry && $db->insert_id > 0){
                    $counts++;
                }
            }
    
            if ($counts === count((array)$skipped_payments_pending)) {
                $db->query('COMMIT');
                echo "Success";
            }else{
                $db->query('ROLLBACK');
                echo "Failed";
            }
    
        } catch (Exception $exp) {
            $db->query('ROLLBACK');
            $msgs = $exp->getMessage();
            CreateLog($_REQUEST, json_encode($msgs));
            echo json_encode($msgs);
        }
    
    }
    
}












