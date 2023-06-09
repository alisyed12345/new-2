<?php
include_once "../includes/config.php";
include_once "../includes/FortePayment.class.php";

$forte_configarray = array(
  'FORTE_API_ACCESS_ID' => FORTE_API_ACCESS_ID,
  'FORTE_API_SECURITY_KEY' => FORTE_API_SECURITY_KEY,
  'FORTE_ORGANIZATION_ID' => FORTE_ORGANIZATION_ID,
  'FORTE_LOCATION_ID' => FORTE_LOCATION_ID,
  'ENVIRONMENT' => ENVIRONMENT,
);
$fortePayment = new FortePayment($forte_configarray);

//AUTHARISATION CHECK
if (!isset($_SESSION['icksumm_uat_login_userid'])) {
  return;
}

if ($_POST['action'] == 'list_payments_credentcials') {
  $finalAry = array();
  $user_id = trim($_POST['user_id']);

  $all_credit_card_data = $db->get_results("SELECT pay.id, f.id as family_id, pay.credit_card_no, pay.credit_card_exp, pay.credit_card_cvv,
          (CASE pay.default_credit_card WHEN '0' THEN 'No' ELSE 'Yes' END) AS defaultno, pay.credit_card_type,
          CONCAT(f.father_first_name,' ',COALESCE(f.father_last_name,'')) AS father_name FROM ss_family f
        INNER JOIN ss_paymentcredentials pay ON f.id = pay.family_id where f.id='" . $user_id . "' and pay.credit_card_deleted = 0 and  f.is_deleted = 0 ", ARRAY_A);
  for ($i = 0; $i < count((array)$all_credit_card_data); $i++) {
    $star = '************';
    $all_credit_card_data[$i]['credit_card_no'] = $star . substr(str_replace(' ', '', base64_decode($all_credit_card_data[$i]['credit_card_no'])), -4);
  
    $payment_credentials = $db->get_row("select * from ss_payment_txns where payment_credentials_id = '" .$all_credit_card_data[$i]['id']. "'");

    if($all_credit_card_data[$i]['defaultno'] == 'No'){
      $all_credit_card_data[$i]['payment_credentials_delete'] = 1;
    }else{
      $all_credit_card_data[$i]['payment_credentials_delete'] = 0;
    }
    

    $arrayval = base64_decode($all_credit_card_data[$i]['credit_card_exp']);
    $card_exp_data = explode("-", $arrayval);
    $exp_data =  $card_exp_data[0] . '/' . $card_exp_data[1];

    $all_credit_card_data[$i]['credit_card_exp'] = rtrim($exp_data, '/');

    $all_credit_card_data[$i]['credit_card_type'] = base64_decode($all_credit_card_data[$i]['credit_card_type']);

    $all_credit_card_data[$i]['credit_card_cvv'] = base64_decode($all_credit_card_data[$i]['credit_card_cvv']);
    $all_credit_card_data[$i]['father_name'] = $all_credit_card_data[$i]['father_name'];
  }
  $finalAry['data'] = $all_credit_card_data;
  echo json_encode($finalAry);
  exit;
}


// //==========================ADD / EDIT Credit Card=====================
// elseif ($_POST['action'] == 'credit_card_add' || $_POST['action'] == 'credit_card_edit') {
//   //if(in_array("su_basic_fees_create", $_SESSION['login_user_permissions'])){
//   $db->query('BEGIN');
//   $credit_card_type = base64_encode($_POST['credit_card_type']);
//   $credit_card_no = base64_encode($_POST['credit_card_no']);
//   $expiry_month = $_POST['expiry_month'];
//   $expiry_year = $_POST['expiry_year'];
//   $cvv_no = base64_encode($_POST['cvv_no']);
//    $exp_card_addon = $expiry_month . '/' . $expiry_year;
//   $expiry_card = base64_encode($exp_card_addon);
//   $is_credit_already = $db->query("select * from ss_paymentcredentials where family_id='" . trim($db->escape($_POST['family_id'])) . "' AND credit_card_no='".$credit_card_no."' AND credit_card_exp='".$expiry_card."' AND credit_card_cvv='".$cvv_no."' AND default_credit_card =1 ");
//   if($is_credit_already){
//       $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Payment credentials already exist <p>');
//       echo json_encode($return_resp);
//       exit;
//   }
//   if (!empty($_POST['default']) && $_POST['default'] === "Yes") {
//     $default = 1;
//   } else {
//     $default = 0;
//   }
 
//   $family_info = $db->get_row("select * from ss_family where id = '" . trim($db->escape($_POST['family_id'])) . "'");
//   if (!empty($family_info->forte_customer_token)) {
//     $forte_customer_token = $family_info->forte_customer_token;
//   } else {
//     $forte_customer_token = "";
//   }
//   $forteParamsSend = array(
//     'coustomer_token' => $forte_customer_token,
//     'creditCardType' => trim($_POST['credit_card_type']),
//     'creditCardNumber' => trim($_POST['credit_card_no']),
//     'expMonth' => $_POST['expiry_month'],
//     'expYear' => $_POST['expiry_year'],
//     'cvv' => $_POST['cvv_no'],
//     'firstName' => $family_info->father_first_name,
//     'lastName' => $family_info->father_last_name,
//     'email' => $family_info->primary_email,
//     'phone' => $family_info->father_phone,
//     'city' => $family_info->billing_city,
//     'zip'    => $family_info->billing_post_code,
//     'countryCode' => 'US',
//   );
//   $forteParams = json_encode($forteParamsSend);
//   if (!empty($family_info->forte_customer_token)) {
//     $paymentToken = $fortePayment->GenratePaymentToken($forteParams);
//     if (isset($paymentToken->paymethod_token)) {
//       $customertoken = $family_info->forte_customer_token;
//       $paymethodtoken = $paymentToken->paymethod_token;
//     } else {
//       $customertoken = "";
//       $paymethodtoken = "";
//     }
//   } else {
//     $customerPostRequest = $fortePayment->CurlSendPostRequestCustomer($forteParams);
//     if (isset($customerPostRequest->customer_token) && isset($customerPostRequest->default_paymethod_token)) {
//       $customertoken = $customerPostRequest->customer_token;
//       $paymethodtoken = $customerPostRequest->default_paymethod_token;
//       $db->query("update ss_family set forte_customer_token='" . $customertoken . "' where id='" . trim($db->escape($_POST['family_id'])) . "'");
//     } else {
//       $customertoken = "";
//       $paymethodtoken = "";
//     }
//   }
//   if (!empty($customertoken) && !empty($paymethodtoken)) {

//     if (isset($_POST['action']) &&  $_POST['action'] == 'credit_card_edit') {
//       $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=0, credit_card_deleted=1 where id='" . trim($db->escape($_POST['payid'])) . "'");
//       $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=0 where family_id='" . trim($db->escape($_POST['family_id'])) . "'");
//     } else {
//       if (!empty($_POST['default']) && $_POST['default'] === "Yes") {
//         $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=0 where family_id='" . trim($db->escape($_POST['family_id'])) . "'");
//       }
//     }
//     $credit_card = $db->query("insert into ss_paymentcredentials set family_id='" . trim($db->escape($_POST['family_id'])) . "', credit_card_no='" . $credit_card_no . "', credit_card_type='" . $credit_card_type . "',
//             credit_card_exp='" . $expiry_card . "', credit_card_cvv='" . $cvv_no . "', default_credit_card='" . $default . "', forte_payment_token= '" . $paymethodtoken . "',  created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "', created_on='" . date('Y-m-d H:i:s') . "' ");
//     $credit_card_id = $db->insert_id;
//     if ($credit_card_id > 0) {
//       $family_info = $db->get_row("select * from ss_family where id ='" . trim($db->escape($_POST['family_id'])) . "' ");
//       if (isset($_POST['privewschedule']) && $_POST['privewschedule'] == "Yes" && isset($_POST['default']) && $_POST['default'] === "Yes") {
//         $comments = "<strong>Reschedule Payments Status  </strong><br><strong>Preview Status : </strong> Skiped  <br> <strong>Current Status : </strong> Pending";
//         $get_childs = $db->get_results("SELECT s.user_id FROM ss_student s INNER JOIN ss_user u ON u.id = s.user_id INNER JOIN ss_school_sessions ss ON ss.id = u.session_id where s.family_id = '" . trim($db->escape($_POST['family_id'])) . "' AND u.is_active=1 AND u.is_deleted=0");
//         if (count($get_childs) > 0) {
//           foreach ($get_childs as $stu) {

//             $get_data_skiped_payments = $db->get_results("SELECT * FROM ss_student_fees_items where student_user_id = '" . $stu->user_id . "' AND schedule_status=4");
//             if (count($get_data_skiped_payments) > 0) {
//               // $next_payment_date = $db->get_var("SELECT schedule_payment_date FROM ss_student_fees_items WHERE student_user_id = '" . $stu->user_id . "' AND schedule_status=0 ORDER BY schedule_payment_date ASC");
//               foreach ($get_data_skiped_payments as $key => $items) {
//                 $schedule_new_date = Date('Y-m-d', strtotime("+1 days")); // Add/Updated credit card : payment is scheduled for next day because cron is working only between 06am to 11pm, Anyone can add CC at midnight That's why scheduled for next day not today.
//                 $db->query("update ss_student_fees_items set schedule_payment_date = '" . $schedule_new_date . "', schedule_status = 0, updated_at = '" . date('Y-m-d H:i') . "' where id='" . $items->id . "'");
//                 $res = $db->query("insert into ss_student_fees_item_status_history set student_fees_items_id='" . $items->id . "' , current_status=4, new_status=0, comments='" . $comments . "', created_at='" . date('Y-m-d H:i:s') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'  ");
//               }
//             }
//           }
//         }
//       }
//       $star = '************';
//       $card_no = $star . substr(str_replace(' ', '', $_POST['credit_card_no']), -4);
//       $emailbody_parents .= "Assalamualaikum,<br><br>";
//       $emailbody_parents .= "Your Payment information was updated for " . CENTER_SHORTNAME . " " . SCHOOL_NAME . ". This is the confirmation email<br><br>";
//       $emailbody_parents .= "Your new payment credential information is,<br><br>";
//       $emailbody_parents .= '<table style="width:100%; cellpadding:0px; border:0;" cellpadding="0">
//                             <tr>
//                                 <td colspan="2" style="text-align: center;">
//                                     <div style="font-size: 18px; text-align:left;"><u> New Payment Credential Information</u></div>
//                                 </td>
//                             </tr>   
//                             <tr>
//                             <td colspan="2" style="text-align: left; padding-top:30px">
//                             <table style="width:100%; cellpadding:0px; border:0;" cellspacing="3">


//                             <tr><td style="width: 25%;" class="color2">Last 4 Digits of Credit Card:</td>
//                             <td style="width: 75%; text-align:left;">' . $card_no . '
//                             </td></tr>
                            
//                             <tr><td style="width: 25%;" class="color2">Credit Card Expiry:</td>
//                             <td style="width: 75%; text-align:left;">' . $_POST['expiry_month'] . '/' . $_POST['expiry_year'] . '
//                             </td></tr>     

//                         </table>';
//       $emailbody_parents .= "<br><br>JazakAllah Khairan";
//       $emailbody_parents .= "<br>Islamic Center of Kansas";
//       $emailbody_parents .= "<br><br>For any comments or question, please send email to <a href='academy@ickansas.org'> academy@ickansas.org </a>";
//       $emailbody_parents .= "<br> You agreed to give us consent to authorize electronic fund transfer from the account specified above for " . CENTER_SHORTNAME . " " . SCHOOL_NAME;
//       $mailservice_request_from = MAIL_SERVICE_KEY;
//       $mail_service_array = array(
//         'subject' => CENTER_SHORTNAME . " " . SCHOOL_NAME . ' New Payment Credential',
//         'message' => $emailbody_parents,
//         'request_from' => $mailservice_request_from,
//         'attachment_file_name' => '',
//         'attachment_file' => '',
//         'to_email' => [$family_info->primary_email, $family_info->secondary_email],
//         'cc_email' => EMAIL_GENERAL,
//         'bcc_email' => ''
//       );
//       mailservice($mail_service_array);

//       $db->query('COMMIT');
//       $dispMsg = "<p class='text-success'> Payment credentials added successfully <p>";
//       echo json_encode(array('code' => "1", 'msg' => $dispMsg));
//       exit;
//     } else {
//       $db->query('ROLLBACK');
//       $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> Payment credentials not added <p>');
//       CreateLog($_REQUEST, json_encode($return_resp));
//       echo json_encode($return_resp);
//       exit;
//     }
//   } else {
//     $msg = $paymentToken->response->response_desc;
//     if (!$msg) {
//       $msg = "Invalid card details";
//     }
//     $db->query('ROLLBACK');
//     $return_resp = array('code' => "0", 'msg' => '<p class="text-danger"> ' . $msg . ' <p>');
//     CreateLog($_REQUEST, json_encode($return_resp));
//     echo json_encode($return_resp);
//     exit;
//   }
// } 
elseif ($_POST['action'] == 'set_default') {
  if (isset($_POST['id']) && isset($_POST['family_id'])) {

    $all_CC_0 = $db->query("update ss_paymentcredentials set default_credit_card=0 ,updated_on='" . date('Y-m-d H:i') . "' where family_id='" . trim($db->escape($_POST['family_id'])) . "'");
    $sql_ret = $db->query("update ss_paymentcredentials set default_credit_card=1 where id='" . trim($db->escape($_POST['id'])) . "'");
    
    if ($sql_ret && $all_CC_0) {
      echo json_encode(array('code' => "1", 'msg' => 'Payment credentials default set successfully'));
      exit;
    } else {
      $return_resp = array('code' => "0", 'msg' => 'Error: Payment credentials default set failed');
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

elseif ($_POST['action'] == 'delete_credential') {
  if (isset($_POST['id'])) {
    $sql_ret = $db->query("update ss_paymentcredentials set forte_payment_token = null, default_credit_card=0, credit_card_deleted=1 where id='" . trim($db->escape($_POST['id'])) . "'");
    if ($sql_ret) {
      echo json_encode(array('code' => "1", 'msg' => 'Payment credentials deleted successfully'));
      exit;
    } else {
      $return_resp = array('code' => "0", 'msg' => 'Error: Payment credentials deletion failed');
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


elseif ($_POST['action'] == 'check_priveus_payment') {
  if (isset($_POST['family_id'])) {

    $all_student_fees_items = $db->get_results("SELECT GROUP_CONCAT(sfi.id) AS sch_item_id, GROUP_CONCAT(s.first_name) as child_name, u.is_active, sfi.original_schedule_payment_date, sfi.schedule_payment_date, CONCAT('$',SUM(sfi.amount)) AS final_amount, sfi.schedule_status,sfi.schedule_unique_id, s.family_id, s.user_id,  f.father_first_name, f.father_last_name, f.father_phone, f.primary_email, pay.credit_card_no,sfi.schedule_notification FROM ss_student_fees_items sfi
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    INNER JOIN ss_user u ON u.id = s.user_id
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
    INNER JOIN ss_family f ON f.id = s.family_id
    INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
    WHERE s.family_id = '" . trim($db->escape($_POST['family_id'])) . "' AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND u.is_deleted = 0  AND u.is_locked=0  AND pay.default_credit_card =1 AND sfi.schedule_status = 4
    GROUP BY sfi.schedule_unique_id,sfi.schedule_status ORDER BY sfi.id desc", ARRAY_A);


    // $all_student_fees_items = $db->get_results("SELECT sfi.id AS sch_item_id,sfi.original_schedule_payment_date, sfi.schedule_payment_date, SUM(sfi.amount) AS final_amount, s.family_id
    // FROM ss_student_fees_items sfi
    // INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    // INNER JOIN ss_user u ON u.id = s.user_id
    // INNER JOIN ss_school_sessions ss ON ss.id = u.session_id
    // INNER JOIN ss_family f ON f.id = s.family_id
    // INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
    // LEFT JOIN ss_student_fees_transactions ON ss_student_fees_transactions.student_fees_item_id = sfi.id
    // LEFT JOIN ss_payment_txns t ON t.id = ss_student_fees_transactions.payment_txns_id
    // WHERE s.family_id = '" . trim($db->escape($_POST['family_id'])) . "' AND u.is_active = 1 AND u.is_deleted = 0 AND pay.default_credit_card =1 AND sfi.schedule_status = 4
    // GROUP BY sfi.original_schedule_payment_date,schedule_status,payment_unique_id 
    // ORDER BY  sfi.original_schedule_payment_date ASC", ARRAY_A);

    // for ($i = 0; $i < count($all_student_fees_items); $i++) {
    //   $trxn_child_names = $db->get_results("SELECT s.first_name FROM ss_student_fees_items sfi
    // INNER JOIN ss_student s ON sfi.student_user_id = s.user_id  INNER JOIN ss_family f ON f.id = s.family_id WHERE  sfi.schedule_payment_date = '" . $all_student_fees_items[$i]['schedule_payment_date'] . "' AND s.family_id = '" . $all_student_fees_items[$i]['family_id'] . "' GROUP BY s.user_id");
    //   $child_name = "";
    //   foreach ($trxn_child_names as $row) {
    //     $child_name .= $row->first_name . ", ";
    //   }
    //   $all_student_fees_items[$i]['schedule_payment_date'] =  date('m/d/Y', strtotime($all_student_fees_items[$i]['original_schedule_payment_date']));
    //   $all_student_fees_items[$i]['final_amount'] = '$' . ($all_student_fees_items[$i]['final_amount'] + 0);
    //   $all_student_fees_items[$i]['child_name'] = rtrim($child_name, ', ');
    // }
    if (count((array)$all_student_fees_items) > 0) {
      $html = '        
    <table class="table">
      <thead>
        <tr>
          <th>Schedule Date</th>
          <th>Child(ren)</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>';
      $total_amount = 0;
      $student_fees_items_ids = "";
      foreach ($all_student_fees_items as $row) {
        $student_fees_items_ids .= $row['sch_item_id']."|";
        $amout = str_replace("$", "", $row['final_amount']);
        $total_amount += $amout;
        $html .= '<tr>
                <td>' . Date('m/d/Y', strtotime($row['schedule_payment_date'])) . '</td>
                <td>' . $row['child_name'] . '</td>
                <td>' . $row['final_amount'] . '</td>
                </tr>';
      }
      $html .= '<tr><td></td><th>Total Amount</th><td>$' . ($total_amount + 0) . '</td></tr> </tbody> </table>';
    
      echo json_encode(array('code' => "1", 'msg' => "$html", 'student_fees_items_ids' => rtrim($student_fees_items_ids,'|'), 'decline_total_amount' => ($total_amount + 0)));
      exit;
    } else {
      echo json_encode(array('code' => "0", 'msg' => 'No data found'));
      exit;
    }
  }
}
