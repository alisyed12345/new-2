<?php
//LIVE - PROD SITE
set_include_path('/webroot/b/a/bayyan005/icksaturdaydv.click2clock.com/www/includes/');

include_once "config.php";

try {

    $received_payment_txn_ids = $db->get_results("SELECT * FROM `ss_received_payment_txn_ids` WHERE `status` = 0 ");

    if(count((array)$received_payment_txn_ids) > 0){

        foreach($received_payment_txn_ids as $payment_txn){
            if(!empty($payment_txn->request_token)){
                $token = genrate_encrypt_token($payment_txn->request_token);
                $data_request = ['auth_token'=>$token];
                $PAYSERVICE_URL = PAYSERVICE_URL."api/transactions_request";
                $results = response_post_service($data_request,$PAYSERVICE_URL);
            }

        }
        
    }

}catch(Exception $e) {
    $return_resp = json_encode($e);
    CreateLog($_REQUEST, $return_resp);
    //exit;
}

?>