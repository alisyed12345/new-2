<?php
class BasysPayment
{
    protected $apiURL;
    protected $BASYS_API_ACCESS_ID;
    protected $ENVIRONMENT;
  
    public function __construct($config){
        ob_start();
        session_start();
        if (count((array)$config) > 0){
          foreach ($config as $key => $val){
              $this->$key = $val;
          }           

          if(strtoupper(trim($this->ENVIRONMENT))  == 'QA' || strtoupper(trim($this->ENVIRONMENT))  == 'PROD' || strtoupper(trim($this->ENVIRONMENT))  == 'PRODUCTION'){
            $this->apiURL = 'https://app.basysiqpro.com'; 
          }else{
            $this->apiURL = 'https://sandbox.basysiqpro.com/api/transaction';
          }
        }
    }
    
    public function CurlSendPostRequestCustomer($customerData)
    {
      $ip = $_SERVER['REMOTE_ADDR'];
      $familyData = json_decode($customerData);
      $customer_id = mt_rand(100000, 999999);
        $data_request = array(
        "type"=> "sale",
        "amount" => (int) $familyData->registration_fee,
        "currency" => "USD",
        "description" => $familyData->addition_notes,
        "order_id" => "$familyData->order_id",
        "ip_address" => "$ip",
        "payment_method" => array(
        "card" => array(
        "entry_type" => "keyed",
        "number" =>$familyData->creditCardNumber,
        "expiration_date" =>  $familyData->expMonth.'/'.$familyData->expYear,
        "cvc" =>  $familyData->cvv
        )
        )
        );

       
        $url =  $this->apiURL;
        $request = json_encode($data_request); 
        $authentication = $this->BASYS_API_ACCESS_ID; 

        $ch = curl_init();

        curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17",
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $request,
        CURLOPT_HTTPHEADER => array(
        "Authorization: ".$authentication,
        "Content-Type: application/json"
        ),
        ));


     
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

            //echo $curl_error;
        curl_close($ch);
        return json_decode($data);
    }
    
}
?>