<?php
class FortePayment
{
    protected $apiURL;
    protected $FORTE_API_ACCESS_ID;
    protected $FORTE_API_SECURITY_KEY;
    protected $FORTE_ORGANIZATION_ID;
    protected $FORTE_LOCATION_ID;
    protected $ENVIRONMENT;

    public function __construct($config){
        ob_start();
        session_start();
        if (count((array)$config) > 0){
            foreach ($config as $key => $val){
               $this->$key = $val;
            }
           

        if($this->ENVIRONMENT == 'dev'){
          $this->apiURL = 'https://sandbox.forte.net/api/v3';
        }else{
          $this->apiURL = 'https://api.forte.net/v3'; 
        }

        }
    }
    
   
   

 // if ($enviroment == "production") {
 //    var $apiURL = 'https://sandbox.forte.net/api/v3';
 //    var $FORTE_API_ACCESS_ID = "73b930dbf99086149e6ee38574ecfbe3";
 //    var $FORTE_API_SECURITY_KEY = "adaf6508cf4b52c152ff1c992a903f08";
 //    var $FORTE_ORGANIZATION_ID = "org_368178";
 //    var $FORTE_LOCATION_ID = "loc_225609";
 // }elseif ($enviroment == "sendbox") {
 //    var $apiURL = 'https://sandbox.forte.net/api/v3';
 //    var $FORTE_API_ACCESS_ID = "73b930dbf99086149e6ee38574ecfbe3";
 //    var $FORTE_API_SECURITY_KEY = "adaf6508cf4b52c152ff1c992a903f08";
 //    var $FORTE_ORGANIZATION_ID = "org_368178";
 //    var $FORTE_LOCATION_ID = "loc_225609";
 // }else{
 //  echo "error";
 // }
    
    // var $apiURL = 'https://sandbox.forte.net/api/v3';
    // var $FORTE_API_ACCESS_ID = "73b930dbf99086149e6ee38574ecfbe3";
    // var $FORTE_API_SECURITY_KEY = "adaf6508cf4b52c152ff1c992a903f08";
    // var $FORTE_ORGANIZATION_ID = "org_368178";
    // var $FORTE_LOCATION_ID = "loc_225609"; 



    public function CurlSendPostRequestCustomer($customerData){

      $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
     
      $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;

      $familyData = json_decode($customerData);
      $customer_id = mt_rand(100000, 999999);

      $creditCardType = substr(strtolower($familyData->creditCardType), 0, 4);

      $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/customers/";


      $data_request = array (
      'first_name' => $familyData->firstName,
      'last_name' =>  $familyData->lastName,
      'customer_id' => $customer_id,
      'addresses' => 
      array (
        0 => 
        array (
          'label' => 'Brown Shipping',
          'first_name' => $familyData->firstName,
          'last_name' =>  $familyData->lastName,
          'company_name' => '',
          'phone' =>$familyData->phone,
          'email' => $familyData->email,
          'shipping_address_type' => 'residential',
          'address_type' => 'default_shipping',
          'physical_address' => 
          array (
            'street_line1' => $familyData->city,
            'street_line2' => '',
            'locality' => $familyData->city,
            'postal_code' => $familyData->zip,
          ),
        ),
        1 => 
        array (
          'label' => 'Brown Billing',
          'first_name' => $familyData->firstName,
          'last_name' => $familyData->lastName,
          'company_name' => '',
          'phone' =>$familyData->phone,
          'email' =>$familyData->email,
          'shipping_address_type' => 'residential',
          'address_type' => 'default_billing',
          'physical_address' => 
          array (
            'street_line1' => $familyData->city,
            'street_line2' => '',
            'locality' => $familyData->city,
            'postal_code' => $familyData->zip,
          ),
        ),
      ),
      'paymethod' => 
      array (
        'label' => $familyData->creditCardType,
        'notes' => 'Business CC',
        'card' => 
        array (
          'account_number' => $familyData->creditCardNumber,
          'expire_month' => $familyData->expMonth,
          'expire_year' =>$familyData->expYear,
          'card_verification_value' => $familyData->cvv,
          'card_type' => $creditCardType,
          'name_on_card' => $familyData->firstName.' '.$familyData->lastName
        ),
      ),
    );

        $request = json_encode($data_request);
        $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

        $ch = curl_init($url);
        $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,        // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",    // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,           // timeout on connect
                    CURLOPT_TIMEOUT        => 20,           // timeout on response
                    CURLOPT_POST            => 1,           // i am sending post data
                    CURLOPT_POSTFIELDS     => $request,     // this are my post vars
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                    )

                );

        curl_setopt_array($ch,$options);
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

            //echo $curl_error;
        curl_close($ch);
        return json_decode($data);
    }



    public function GenrateCustomerToken($customerData){

      $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
      $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;

      $familyData = json_decode($customerData);
      $customer_id = mt_rand(100000, 999999);
      
      $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/customers/";


      $data_request = array (
      'first_name' => $familyData->firstName,
      'last_name' =>  $familyData->lastName,
      'customer_id' => $customer_id,
      'addresses' => 
      array (
        0 => 
        array (
          'label' => 'Brown Shipping',
          'first_name' => $familyData->firstName,
          'last_name' =>  $familyData->lastName,
          'company_name' => '',
          'phone' =>$familyData->phone,
          'email' => $familyData->email,
          'shipping_address_type' => 'residential',
          'address_type' => 'default_shipping',
          'physical_address' => 
          array (
            'street_line1' => $familyData->city,
            'street_line2' => '',
            'locality' => $familyData->city,
            'postal_code' => $familyData->zip,
          ),
        ),
        1 => 
        array (
          'label' => 'Brown Billing',
          'first_name' => $familyData->firstName,
          'last_name' => $familyData->lastName,
          'company_name' => '',
          'phone' =>$familyData->phone,
          'email' =>$familyData->email,
          'shipping_address_type' => 'residential',
          'address_type' => 'default_billing',
          'physical_address' => 
          array (
            'street_line1' => $familyData->city,
            'street_line2' => '',
            'locality' => $familyData->city,
            'postal_code' => $familyData->zip,
          ),
        ),
      ),
      
    );

        $request = json_encode($data_request);
        $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

        $ch = curl_init($url);
        $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,        // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",    // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,           // timeout on connect
                    CURLOPT_TIMEOUT        => 20,           // timeout on response
                    CURLOPT_POST            => 1,           // i am sending post data
                    CURLOPT_POSTFIELDS     => $request,     // this are my post vars
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                    )

                );

        curl_setopt_array($ch,$options);
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

            //echo $curl_errno;
            //echo $curl_error;
        curl_close($ch);
        return json_decode($data);
    }



    
   public function GenratePaymentToken($customerData){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;
    $familyData = json_decode($customerData);

    $creditCardType = substr(strtolower($familyData->creditCardType), 0, 4);
    
    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/customers/$familyData->coustomer_token/paymethods";
   
    $data_request = array (
          "notes" => $familyData->firstName.' '.$familyData->lastName,
        'card' => array (
          'card_type' => $creditCardType,
          'name_on_card' => $familyData->firstName.' '.$familyData->lastName,
          'account_number' => $familyData->creditCardNumber,
          'expire_month' => $familyData->expMonth,
          'expire_year' =>$familyData->expYear,
          'card_verification_value' => $familyData->cvv
          ),
     );

    $request = json_encode($data_request);
    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                CURLOPT_RETURNTRANSFER => true,         // return web page
                CURLOPT_HEADER         => false,        // don't return headers
                CURLOPT_FOLLOWLOCATION => false,         // follow redirects
               // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                CURLOPT_TIMEOUT        => 20,          // timeout on response
                CURLOPT_POST            => 1,            // i am sending post data
                CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                CURLOPT_SSL_VERIFYPEER => false,        //
                CURLOPT_VERBOSE        => 1,
                CURLOPT_HTTPHEADER     => array(
                    "Authorization: Basic $authentication",
                    "Accept: application/json",
                    "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                    "Content-Type: application/json"
                )

            );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

        //echo $curl_errno;
        //echo $curl_error;
    curl_close($ch);
    return json_decode($data);

}


   public function CurlSendPostRequestSchedules($customertoken, $paymethodtoken, $schedule_amount, $schedule_start_date, $monthdiffcount){

        $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
        $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;
        $order_number = mt_rand(100000, 999999);

        $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/schedules";

        $data_request = array(
           "customer_token"=> "$customertoken",
           "paymethod_token"=> "$paymethodtoken",
           "action"=> "sale",
           "schedule_quantity"=> $monthdiffcount,
           "schedule_frequency"=> "monthly",
           "schedule_amount"=> $schedule_amount,
           'sec_code' => 'CCD',
           "schedule_start_date"=> $schedule_start_date,
           "reference_id"=> "INV-".$order_number,
           "order_number"=> 'ICK Academy',
           "item_description"=>"Student Fee Payment"   
        );

        $request = json_encode($data_request);
        $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

        $ch = curl_init($url);
        $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                    CURLOPT_TIMEOUT        => 20,          // timeout on response
                    CURLOPT_POST            => 1,            // i am sending post data
                    CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        //
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                    )

                );

        curl_setopt_array($ch,$options);
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

            //echo $curl_errno;
            //echo $curl_error;
        curl_close($ch);
        return json_decode($data);

    }


  
   public function CurlSendPostRequestOneTimePayment($customerData){

        $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
        $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;
         
        $familyData = json_decode($customerData);

        $creditCardType = substr(strtolower($familyData->creditCardType), 0, 4);
        
        $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/transactions";

        $data_request = array (
              "action" => "sale",
              "authorization_amount" => $familyData->amount,
              "subtotal_amount" => $familyData->amount,
              "billing_address" =>  array (
              "first_name" => $familyData->firstName,
              "last_name" => $familyData->lastName
             ),
           'card' => array (
            'card_type' => $creditCardType,
            'name_on_card' => $familyData->firstName.' '.$familyData->lastName,
            'account_number' => $familyData->creditCardNumber,
            'expire_month' => $familyData->expMonth,
            'expire_year' =>$familyData->expYear,
            'card_verification_value' => $familyData->cvv
            ),
         );

        $request = json_encode($data_request);
        $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

        $ch = curl_init($url);
        $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                    CURLOPT_TIMEOUT        => 20,          // timeout on response
                    CURLOPT_POST            => 1,            // i am sending post data
                    CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        //
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                    )

                );

        curl_setopt_array($ch,$options);
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

            //echo $curl_errno;
            //echo $curl_error;
        curl_close($ch);
        return json_decode($data);

    }






    function GetCustomerSchedulesItem($customertoken){

      $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
      $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;


      $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/customers/$customertoken/scheduleitems?filter=schedule_item_status+eq+'scheduled'";

      $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

      $ch = curl_init($url);
      $options = array(
                      CURLOPT_RETURNTRANSFER => true,         // return web page
                      CURLOPT_HEADER         => false,        // don't return headers
                      CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                     // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                      CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                      CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                      CURLOPT_TIMEOUT        => 20,          // timeout on response
                      CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                      CURLOPT_SSL_VERIFYPEER => false,        //
                      CURLOPT_VERBOSE        => 1,
                      CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                      )

                    );

      curl_setopt_array($ch,$options);
      $data = curl_exec($ch);
      $curl_errno = curl_errno($ch);
      curl_close($ch);
      return json_decode($data);

    }


    function GetCustomerSuspendedItem($customertoken){

      $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
      $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;


      $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/customers/$customertoken/scheduleitems?filter=schedule_item_status+eq+'suspended'";

      $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

      $ch = curl_init($url);
      $options = array(
                      CURLOPT_RETURNTRANSFER => true,         // return web page
                      CURLOPT_HEADER         => false,        // don't return headers
                      CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                     // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                      CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                      CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                      CURLOPT_TIMEOUT        => 20,          // timeout on response
                      CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                      CURLOPT_SSL_VERIFYPEER => false,        //
                      CURLOPT_VERBOSE        => 1,
                      CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                      )

                    );

      curl_setopt_array($ch,$options);
      $data = curl_exec($ch);
      $curl_errno = curl_errno($ch);
      curl_close($ch);
      return json_decode($data);


    }

    


    function UpdateCustomerSchedulesItem($customertoken, $schedule_id, $schedule_item_description, $startDate, $endDate){

      $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
      $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;


      $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/customers/$customertoken/scheduleitems?filter=schedule_item_status+eq+'scheduled'";

      $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

      $ch = curl_init($url);
      $options = array(
                      CURLOPT_RETURNTRANSFER => true,         // return web page
                      CURLOPT_HEADER         => false,        // don't return headers
                      CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                     // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                      CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                      CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                      CURLOPT_TIMEOUT        => 20,          // timeout on response
                      CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                      CURLOPT_SSL_VERIFYPEER => false,        //
                      CURLOPT_VERBOSE        => 1,
                      CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                      )

                    );

      curl_setopt_array($ch,$options);
      $data = curl_exec($ch);
      $curl_errno = curl_errno($ch);

              //echo $curl_errno;
              //echo $curl_error;
      curl_close($ch);
      $schedulesItems = json_decode($data);

      if(!empty($schedulesItems->results)){

       $newItemsArray = [];
       foreach ($schedulesItems->results as $rows) {

        if($rows->schedule_id == $schedule_id){
         $itemsDate = date('Y-m-d',strtotime($rows->schedule_item_date));
         if($startDate <= $itemsDate && $endDate >= $itemsDate){
          $newItemsArray[] = $rows->schedule_item_id;
        }

      }

      }

      foreach ($newItemsArray as $schedule_item_id) {

        $this->PutSuspendedSchedulesItem($schedule_item_id, $schedule_item_description);
      }

    }

  }



  function PutSuspendedSchedulesItem($scheduleitemID, $schedule_item_description){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;

    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/scheduleitems/$scheduleitemID";

    $data_request = array(
     "schedule_item_status"=> "suspended",
     "schedule_item_description" => $schedule_item_description 
   );

    $request = json_encode($data_request);
    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                        CURLOPT_RETURNTRANSFER => true,         // return web page
                        CURLOPT_HEADER         => false,        // don't return headers
                        CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                       // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                        CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                        CURLOPT_TIMEOUT        => 20,          // timeout on response
                        CURLOPT_CUSTOMREQUEST  => "PUT",        // i am sending PUT data
                        CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                        CURLOPT_SSL_VERIFYPEER => false,        //
                        CURLOPT_VERBOSE        => 1,
                        CURLOPT_HTTPHEADER     => array(
                          "Authorization: Basic $authentication",
                          "Accept: application/json",
                          "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                          "Content-Type: application/json"
                        )

                      );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
      //echo $curl_errno;
    curl_close($ch);
    return json_decode($data);

  }



    function PostTransaction($customerData){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;

    $familyData = json_decode($customerData);

    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/transactions";

    if($familyData->card_type == "Visa"){
      $familyData->card_type = "visa";
    }elseif($familyData->card_type == "MasterCard"){
      $familyData->card_type = "mast";
    }elseif($familyData->card_type == "Amex"){
      $familyData->card_type = "amex";
    }elseif($familyData->card_type == "DinersClub"){
      $familyData->card_type = "dine";
    }elseif($familyData->card_type == "jcb"){
      $familyData->card_type = "jcb";
    }elseif($familyData->card_type == "Discover"){
      $familyData->card_type = "disc";
    }

    $data_request = array(
     "action"=> "sale",
     "authorization_amount"=>$familyData->amount,
     "subtotal_amount"=>$familyData->amount,
     "billing_address"=> array("first_name" => $familyData->first_name, "last_name"=> $familyData->last_name),
     "card"=> array("card_type" => $familyData->card_type, "name_on_card"=>$familyData->name_on_card, "account_number" => $familyData->account_number, "expire_month" => $familyData->expire_month, "expire_year" => $familyData->expire_year, "card_verification_value"=> $familyData->card_verification_value)
    );

    $request = json_encode($data_request);

    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,        // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",    // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,           // timeout on connect
                    CURLOPT_TIMEOUT        => 20,           // timeout on response
                    CURLOPT_POST            => 1,           // i am sending post data
                    CURLOPT_POSTFIELDS     => $request,     // this are my post vars
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                    )

                );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    //echo $curl_errno;
    curl_close($ch);
    return json_decode($data,true);

  }





    function UpadteAmountSchedulesItem($scheduleitemID, $schedule_item_amount){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;

    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/scheduleitems/$scheduleitemID";

    $data_request = array(
     "schedule_item_amount"=> $schedule_item_amount
   );

    $request = json_encode($data_request);
    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                        CURLOPT_RETURNTRANSFER => true,         // return web page
                        CURLOPT_HEADER         => false,        // don't return headers
                        CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                       // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                        CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                        CURLOPT_TIMEOUT        => 20,          // timeout on response
                        CURLOPT_CUSTOMREQUEST  => "PUT",        // i am sending PUT data
                        CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                        CURLOPT_SSL_VERIFYPEER => false,        //
                        CURLOPT_VERBOSE        => 1,
                        CURLOPT_HTTPHEADER     => array(
                          "Authorization: Basic $authentication",
                          "Accept: application/json",
                          "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                          "Content-Type: application/json"
                        )

                      );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
      //echo $curl_errno;
    curl_close($ch);
    return json_decode($data);

  }



  function UpdateStatusSchedules($schedule_id, $schedule_status){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;

    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/schedules/$schedule_id";

    $data_request = array(
     "schedule_status"=> $schedule_status
   );

    $request = json_encode($data_request);
    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                        CURLOPT_RETURNTRANSFER => true,         // return web page
                        CURLOPT_HEADER         => false,        // don't return headers
                        CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                       // CURLOPT_ENCODING       => "utf-8",    // handle all encodings
                        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                        CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                        CURLOPT_TIMEOUT        => 20,          // timeout on response
                        CURLOPT_CUSTOMREQUEST  => "PUT",        // i am sending PUT data
                        CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                        CURLOPT_SSL_VERIFYPEER => false,        //
                        CURLOPT_VERBOSE        => 1,
                        CURLOPT_HTTPHEADER     => array(
                          "Authorization: Basic $authentication",
                          "Accept: application/json",
                          "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                          "Content-Type: application/json"
                        )

                      );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
      //echo $curl_errno;
    curl_close($ch);
    return json_decode($data);

  }



  
function transactionsWithPaymentToken($customertoken, $paymethodtoken, $customerData){

        $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
        $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;
        $familyData = json_decode($customerData);
  
        $reference_id = mt_rand(100000, 999999);

        $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/transactions";

        $data_request = array (
              "action" => "sale",
              "customer_token" => $customertoken,
              "paymethod_token" => $paymethodtoken,
              "reference_id" => $reference_id,
              "authorization_amount" => $familyData->amount,
              "entered_by" => $familyData->firstName.' '.$familyData->lastName,
              "sales_tax_amount" => $familyData->amount,
              "order_number"=> 'ICK Academy'
              // 'xdata' => array (
              //   'xdata_1' => "ICK Academy",
              //   'xdata_2' => $familyData->schedule_item_ids
              // ),
         );

        $request = json_encode($data_request);
        $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

        $ch = curl_init($url);
        $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                    CURLOPT_TIMEOUT        => 20,          // timeout on response
                    CURLOPT_POST            => 1,            // i am sending post data
                    CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        //
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                        "Authorization: Basic $authentication",
                        "Accept: application/json",
                        "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                        "Content-Type: application/json"
                    )
                );

        curl_setopt_array($ch,$options);
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

            //echo $curl_errno;
            //echo $curl_error;
        curl_close($ch);
        return json_decode($data);

}




  function GetTransactionSettlements($transactionID){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;


    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/transactions/$transactionID/settlements";

    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                    CURLOPT_RETURNTRANSFER => true,         // return web page
                    CURLOPT_HEADER         => false,        // don't return headers
                    CURLOPT_FOLLOWLOCATION => false,         // follow redirects
                   // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                    CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                    CURLOPT_TIMEOUT        => 20,          // timeout on response
                    CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                    CURLOPT_SSL_VERIFYPEER => false,        //
                    CURLOPT_VERBOSE        => 1,
                    CURLOPT_HTTPHEADER     => array(
                      "Authorization: Basic $authentication",
                      "Accept: application/json",
                      "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                      "Content-Type: application/json"
                    )

                  );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);
    return json_decode($data);

  }





 //ACH PAYMENT
  function transactionsWithACH($customerData){

    $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
    $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;
    $familyData = json_decode($customerData);


    $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/transactions";

    $data_request = array (
          "action" => "sale",
          "authorization_amount" => $familyData->amount,
          'billing_address' => array (
            'first_name' => $familyData->firstName,
            'last_name' => $familyData->lastName,
            'phone' => $familyData->phone,
            ),
          'echeck' => array (
            'sec_code' => $familyData->sec_code,
            'account_type' => $familyData->account_type,
            'routing_number' => $familyData->routing_number,
            'account_number' => $familyData->account_number,
            'account_holder' =>  $familyData->firstName.' '.$familyData->lastName
            ),
     );

    $request = json_encode($data_request);
    $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

    $ch = curl_init($url);
    $options = array(
                CURLOPT_RETURNTRANSFER => true,         // return web page
                CURLOPT_HEADER         => false,        // don't return headers
                CURLOPT_FOLLOWLOCATION => false,         // follow redirects
               // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                CURLOPT_TIMEOUT        => 20,          // timeout on response
                CURLOPT_POST            => 1,            // i am sending post data
                CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                CURLOPT_SSL_VERIFYPEER => false,        //
                CURLOPT_VERBOSE        => 1,
                CURLOPT_HTTPHEADER     => array(
                    "Authorization: Basic $authentication",
                    "Accept: application/json",
                    "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                    "Content-Type: application/json"
                )
            );

    curl_setopt_array($ch,$options);
    $data = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

        //echo $curl_errno;
        //echo $curl_error;
    curl_close($ch);
    return json_decode($data);

}




 //ACH PAYMENT VERIFY
 function transactionsWithACHverify($customerData){

  $FORTE_ORGANIZATION_ID = $this->FORTE_ORGANIZATION_ID;
  $FORTE_LOCATION_ID = $this->FORTE_LOCATION_ID;
  $familyData = json_decode($customerData);


  $url = $this->apiURL."/organizations/$FORTE_ORGANIZATION_ID/locations/$FORTE_LOCATION_ID/transactions/verify";

  $data_request = array (
        "action" => "sale",
        "authorization_amount" => "0.00",
         'billing_address' => array (
            'first_name' => $familyData->firstName,
            'last_name' => $familyData->lastName,
            'phone' => $familyData->phone,
            ),
         'physical_address' => array (
            'street_line1' => $familyData->street_line1,
            'street_line2' => $familyData->street_line2,
            'locality' => $familyData->locality,
            'region' => "CA",
            'postal_code' =>  $familyData->postal_code,
            ),
        'echeck' => array (
          'sec_code' => $familyData->sec_code,
          'account_type' => $familyData->account_type,
          'routing_number' => $familyData->routing_number,
          'account_number' => $familyData->account_number,
          'account_holder' =>  $familyData->firstName.' '.$familyData->lastName
          ),
   );

  $request = json_encode($data_request);
  $authentication = base64_encode($this->FORTE_API_ACCESS_ID.":".$this->FORTE_API_SECURITY_KEY);

  $ch = curl_init($url);
  $options = array(
              CURLOPT_RETURNTRANSFER => true,         // return web page
              CURLOPT_HEADER         => false,        // don't return headers
              CURLOPT_FOLLOWLOCATION => false,         // follow redirects
             // CURLOPT_ENCODING       => "utf-8"      // handle all encodings
              CURLOPT_AUTOREFERER    => true,         // set referer on redirect
              CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
              CURLOPT_TIMEOUT        => 20,          // timeout on response
              CURLOPT_POST            => 1,            // i am sending post data
              CURLOPT_POSTFIELDS     => $request,    // this are my post vars
              CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
              CURLOPT_SSL_VERIFYPEER => false,        //
              CURLOPT_VERBOSE        => 1,
              CURLOPT_HTTPHEADER     => array(
                  "Authorization: Basic $authentication",
                  "Accept: application/json",
                  "X-Forte-Auth-Organization-Id: $FORTE_ORGANIZATION_ID",
                  "Content-Type: application/json"
              )
          );

  curl_setopt_array($ch,$options);
  $data = curl_exec($ch);
  $curl_errno = curl_errno($ch);
  $curl_error = curl_error($ch);

      //echo $curl_errno;
      //echo $curl_error;
  curl_close($ch);
  return json_decode($data);

}





    
}
?>