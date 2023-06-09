<?php 
include_once "../includes/config.php";


if($_POST['action'] == 'email_support'){ 
		$emailbody_support = "Information received from ".CENTER_SHORTNAME." ".SCHOOL_NAME." is:<br>";
		$emailbody_support .= "<br><br><strong>Name:</strong> ".$_POST['name'];
		$emailbody_support .= "<br><br><strong>Phone Number:</strong> ".$_POST['phone_no'];
		$emailbody_support .= "<br><br><strong>Email:</strong> ".$_POST['email'];
		$emailbody_support .= "<br><br><strong>Message:</strong> ".$_POST['message'];
		$emailbody_support .= "<br><br>Bayyan Team";											


		$res = send_my_mail("moh.urooj@gmail.com", 'Support: '.CENTER_SHORTNAME." ".SCHOOL_NAME, $emailbody_support);
		$res = send_my_mail("support@bayyan.org", 'Support: '.CENTER_SHORTNAME." ".SCHOOL_NAME, $emailbody_support);

		if($res == true){
			echo json_encode(array('code' => "1",'msg' => 'Message sent successfully'));
		}else{
			$return_resp = array('code' => "0",'msg' => 'Error occured. Please try again.', 'err_pos' => 1);
		    echo json_encode($return_resp);
		    exit;
		}
	
	
	exit;
}


?>