<?php
include_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

//SPECIAL FUNCTION FOR SENDING MASS EMAILS WITH ATTACHMENTS
function bulk_email_with_attachment($receiver_user, $subject, $body, $attach_path_ary)
{
	$mail = new PHPMailer();
	$mail->IsSMTP(); // telling the class to use SMTP

	try {
		$mail->Host = EMAIL_HOST;  		// specify main and backup server
		$mail->SMTPAuth = true;     	// turn on SMTP authentication

		$mail->Port = EMAIL_PORT;				//port 465 for gmail (g suite) , default is 25
		$mail->SMTPSecure = EMAIL_ENCRYPTION;		//for gmail (g suite)

		$mail->Username = EMAIL_UN;  	// SMTP username
		$mail->Password = EMAIL_PS; 	// SMTP password
		$mail->CharSet = 'UTF-8';

		if (filter_var(EMAIL_FROM_SCHOOL, FILTER_VALIDATE_EMAIL) !== false) {
			$mail->From = EMAIL_FROM_SCHOOL;
		}
		$mail->FromName = EMAIL_FROMNAME;

		// $mail->AddAddress($receiver_user, ''); 

		// $mail->AddReplyTo(EMAIL_REPLYTO);

		if (ENVIRONMENT == 'production') {
			if (filter_var($receiver_user, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddAddress($receiver_user, '');
			}
		} else {
			if (filter_var(DEV_TESTER_EMAIL, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddAddress(DEV_TESTER_EMAIL, '');
			}
		}

		if (ENVIRONMENT == 'production') {
			if (filter_var(EMAIL_REPLYTO, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddReplyTo(EMAIL_REPLYTO);
			}
		} else {
			if (filter_var(DEV_TESTER_EMAIL, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddReplyTo(DEV_TESTER_EMAIL);
			}
		}

		foreach ($attach_path_ary as $attach) {
			$mail->AddAttachment($attach);
		}

		$mail->IsHTML(true);                                  // set email format to HTML

		$mail->Subject = $subject;

		$body = generate_email_body($body);
		$body = str_replace('\n', '', $body);
		$body = str_replace('\r', '', $body);
		$mail->Body = $body;	//$email_body;

		$mail->Send();

		return true;
	} catch (phpmailerException $e) {
		echo $e->errorMessage();
		return false;
	} catch (Exception $e) {
		echo $e->getMessage();
		return false;
	}
}

//SEND MAIL
function send_my_mail($receiver_user, $subject, $body, $email_from = EMAIL_FROM)
{
	$mail = new PHPMailer();
	$mail->IsSMTP(); // telling the class to use SMTP

	try {
		//$mail->SMTPDebug = 3;
		$mail->Host = EMAIL_HOST;  		// specify main and backup server
		$mail->SMTPAuth = true;     	// turn on SMTP authentication

		$mail->Port = EMAIL_PORT;	//25;//465;				//port 465 for gmail (g suite) , default is 25
		$mail->SMTPSecure = EMAIL_ENCRYPTION;		//for gmail (g suite)

		$mail->Username = EMAIL_UN;  	// SMTP username
		$mail->Password = EMAIL_PS; 	// SMTP password
		$mail->CharSet = 'UTF-8';

		if (filter_var(EMAIL_FROM_SCHOOL, FILTER_VALIDATE_EMAIL) !== false) {
			$mail->From = EMAIL_FROM_SCHOOL;	//$email_from;
		}
		$mail->FromName = EMAIL_FROMNAME;

		// $mail->AddAddress($receiver_user, ''); 

		// $mail->AddReplyTo(EMAIL_REPLYTO);

		// $mail->AddAddress($receiver_user, ''); 
		if (ENVIRONMENT == 'production') {
			if (filter_var($receiver_user, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddAddress($receiver_user, '');
			}
		} else {
			if (filter_var(DEV_TESTER_EMAIL, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddAddress(DEV_TESTER_EMAIL, '');
			}
		}

		//$mail->AddReplyTo(EMAIL_REPLYTO);
		if (ENVIRONMENT == 'production') {
			if (filter_var(EMAIL_REPLYTO, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddReplyTo(EMAIL_REPLYTO);
			}
		} else {
			if (filter_var(DEV_TESTER_EMAIL, FILTER_VALIDATE_EMAIL) !== false) {
				$mail->AddReplyTo(DEV_TESTER_EMAIL);
			}
		}

		//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
		$mail->IsHTML(true);                                  // set email format to HTML

		$mail->Subject = $subject;

		$body = generate_email_body($body);
		$body = str_replace('\n', '', $body);
		$body = str_replace('\r', '', $body);
		$mail->Body = $body;	//$email_body;

		if ($mail->Send()) {
			return true;
		} else {
			$user_input = array('receiver_user' => $receiver_user, 'subject' => $subject, 'body' => $body, 'email_from' => $email_from);
			CreateLog($user_input, 'Receive false on sending mail');
			return false;
		}
	} catch (phpmailerException $e) {
		$user_input = array('receiver_user' => $receiver_user, 'subject' => $subject, 'body' => $body, 'email_from' => $email_from);
		CreateLog($user_input, json_encode($e));
		return false;
	} catch (Exception $e) {
		$user_input = array('receiver_user' => $receiver_user, 'subject' => $subject, 'body' => $body, 'email_from' => $email_from);
		CreateLog($user_input, json_encode($e));
		return false;
	}
}

//GENERATE EMAIL BODY
function generate_email_body($body_content)
{
	$html = ' 
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
  @media (max-width: 400px) {
    .chunk {
    width: 100% !important;
    }
  }
  </style>
  </head>
<body> 
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
      <td style="padding:0px; text-align:center">
      <table border="0" cellpadding="10" cellspacing="0" width="100%">
      <tr><td style="text-align:center" colspan="2"><img src="http://icksaturdayqa.click2clock.com//settings/uploads/60b7888d6ece8ick-email-logo.jpg"></td>
	  </tr>
	  <br>
	  <tr><td style="background:#fff;text-align: left;" colspan="3">' . $body_content . '</td></tr>
      <tr>
      <td style="color:#333; text-align:left; border-top: solid 3px #333;" colspan="3">
	  	<table align="left" style="width: 35%; min-width: 400px;" class="chunk">
        <tr><td><strong style="color:#333;">Address :</strong> <a href="" style="color:#333; text-decoration:none;">' . SCHOOL_ADDRESS . ',' . SCHOOL_ADDRESSCITY . ',' . SCHOOL_ADDRESSSTATE . ',' . SCHOOL_ADDRESSZIPCODE . '</a></td></tr>
        </table>
        <table align="center" style="width: 65%; min-width: 400px;float: left;" class="chunk">
        <tr><td style=" text-align:left;"><strong style="color:#333;">Email:</strong> <a href="mailto:' . SCHOOL_GEN_EMAIL . '" style="color:#333; text-decoration:none;">' . SCHOOL_GEN_EMAIL . '</a></td>
        <td style=" text-align:left;"><strong style="color:#333;">Tel:</strong> <a href="tel:' . SCHOOL_CONTACT_NO . '" style="color:#333; text-decoration:none;">' . SCHOOL_CONTACT_NO . '</a></td>
        
        </table>
      </td>
      </tr>         
      </table>
      </td>
      </tr>
      </table></body></html>';
	return $html;
}

function getRealIpAddr()
{
	if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	{
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}



//ADDED ON 29-MAR-2020 
function CreateLog($request, $return_resp)
{
	try {
		$log  = "RemoteIP: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
			"CurrentURL: " . $_SERVER['REQUEST_URI'] . PHP_EOL .
			"PreviousURL: " . $_SERVER['HTTP_REFERER'] . PHP_EOL .
			"UserInput: " . json_encode($request) . PHP_EOL .
			"Session: " . (isset($_SESSION) ? json_encode($_SESSION) : '') . PHP_EOL .
			"SystemResponse: " . $return_resp . PHP_EOL .
			"-------------------------" . PHP_EOL;
		//Save string to log, use FILE_APPEND to append.
		file_put_contents(ROOTPATH . 'logs/log_academy_' . date("j.n.Y") . '.log', $log, FILE_APPEND);
	} catch (Exception $e) {
	}
}

//ADDED ON 09-FEB-2018
function generatePassword($passLength = 6)
{
	$alphabet = "ABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < $passLength; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}

//ADDED ON 09-FEB-2018
function verifyDate($date, $strict = true)
{
	$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
	if ($strict) {
		$errors = DateTime::getLastErrors();
		if (!empty($errors['warning_count'])) {
			return false;
		}
	}
	return $dateTime !== false;
}

//ADDED ON 11-FEB-2018
function generateUsername($firstName, $lastName, $dob = '')
{
	global $db;
	$dobSplit = explode('-', $dob);
	$newUsername = strtolower(str_replace(' ', '', $firstName)) . $dobSplit[2] . $dobSplit[1];
	$usernameCheck = $db->get_row("select * from ss_user where username='" . $newUsername . "'");

	if (empty($usernameCheck)) {
		return $newUsername;
	} else {
		$newUsername = strtolower(str_replace(' ', '', $firstName) . str_replace(' ', '', $lastName)) . $dobSplit[2];
		$usernameCheck = $db->get_row("select * from ss_user where username='" . $newUsername . "'");

		if (empty($usernameCheck)) {
			return $newUsername;
		} else {
			$newUsername = strtolower(str_replace(' ', '', $firstName) . str_replace(' ', '', $lastName)) . $dobSplit[2] . $dobSplit[1] . rand(pow(10, 4 - 1), pow(10, 4) - 1);
			$usernameCheck = $db->get_row("select * from ss_user where username='" . $newUsername . "'");

			if (empty($usernameCheck)) {
				return $newUsername;
			}
		}
	}
}

//ADDED ON 25-JUL-2018
function areClasstimeConflict($staff_id, $classtime_id)
{
	global $db;
	$conflict = false;

	$classtime_1 = $db->get_results("select * from ss_classtime where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
	and id in (select classtime_id from ss_staffclasstimemap where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
	and active = 1 and staff_user_id = '" . $staff_id . "')");

	foreach ($classtime_1 as $ctime) {
		$day_no = $ctime->day_number;
		$CT1_F = strtotime($ctime->time_from);
		$CT1_T = strtotime($ctime->time_to);

		$classtime_2 = $db->get_row("select * from ss_classtime where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and id = '" . $classtime_id . "'");
		if (!empty($classtime_2)) {
			$CT2_F = strtotime($classtime_2->time_from);
			$CT2_T = strtotime($classtime_2->time_to);

			if (($CT1_F < $CT2_F && $CT1_T > $CT2_T) || ($CT1_F < $CT2_F && $CT1_T < $CT2_T && $CT1_T > $CT2_F) ||
				($CT1_F > $CT2_F && $CT1_F < $CT2_T && $CT1_T > $CT2_T) || ($CT1_F > $CT2_F && $CT1_F < $CT2_T && $CT1_T < $CT2_T && $CT1_T > $CT1_F) || $CT1_F == $CT2_F || $CT1_T == $CT2_T
			) {
				$conflict = true;
				break;
			}
		}
	}

	return $conflict;
}

//ADDED ON 12-FEB-2018
function areGroupsConflict($group1_id, $group2_id)
{
	global $db;
	$conflict = false;

	$group1 = $db->get_results("select * from ss_group_day_time where group_id = '" . $group1_id . "'");

	foreach ($group1 as $grp1) {
		$day_no = $grp1->day_number;
		$G1_F = strtotime($grp1->time_from);
		$G1_T = strtotime($grp1->time_to);

		$group2 = $db->get_row("select * from ss_group_day_time where group_id = '" . $group2_id . "' and day_number = '" . $day_no . "'");
		if (!empty($group2)) {
			$G2_F = strtotime($group2->time_from);
			$G2_T = strtotime($group2->time_to);

			//if(($G1_F <= $G2_F && $G1_T >= $G2_T) || ($G1_F <= $G2_F && $G1_T <= $G2_T && $G1_T >= $G2_F) || 
			//($G1_F >= $G2_F && $G1_F <= $G2_T && $G1_T >= $G2_T) || ($G1_F >= $G2_F && $G1_F <= $G2_T && $G1_T <= $G2_T && $G1_T >= $G1_F)){
			if (($G1_F < $G2_F && $G1_T > $G2_T) || ($G1_F < $G2_F && $G1_T < $G2_T && $G1_T > $G2_F) ||
				($G1_F > $G2_F && $G1_F < $G2_T && $G1_T > $G2_T) || ($G1_F > $G2_F && $G1_F < $G2_T && $G1_T < $G2_T && $G1_T > $G1_F)
			) {
				$conflict = true;
				break;
			}
		}
	}

	return $conflict;
}


//ADDED ON 03-APR-2018
function isAccessedFromApp()
{
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if (!preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
		return true;
	} else {
		return false;
	}
}

//ADDED ON 12-FEB-2018
function feesMonths()
{
	global $db;
	$counter = 0;

	$mons = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
	$payableMonths = array(8, 9, 10, 11, 12, 1, 2, 3, 4, 5);

	$counter++;

	if (date('m') >= 8 && date('m') <= 12) {
		foreach ($payableMonths as $payMon) {
			$counter++;

			if ($payMon >= 8 && $payMon <= 12) {
				$option .= '<option data-order="' . $counter . '" value="' . $payMon . '-' . date('Y') . '">' . $mons[$payMon] . '-' . date('Y') . '</option>';
			} else {
				$option .= '<option data-order="' . $counter . '" value="' . $payMon . '-' . (date('Y') + 1) . '">' . $mons[$payMon] . '-' . (date('Y') + 1) . '</option>';
			}
		}
	} elseif (date('m') >= 1 && date('m') <= 5) {
		foreach ($payableMonths as $payMon) {
			$counter++;

			if ($payMon >= 9 && $payMon <= 12) {
				$option .= '<option data-order="' . $counter . '" value="' . $payMon . '-' . (date('Y') - 1) . '">' . $mons[$payMon] . '-' . (date('Y') - 1) . '</option>';
			} else {
				$option .= '<option data-order="' . $counter . '" value="' . $payMon . '-' . date('Y') . '">' . $mons[$payMon] . '-' . date('Y') . '</option>';
			}
		}
	}

	return $option;
}

//FUNCTION TO RETURN USER FULL NAME
function getUserFullName($userid, $onlyFirstName = false)
{
	global $db;

	//$user = $db->get_row("select u.*,t.user_type_code from ss_user u inner join ss_usertype t on u.user_type_id = t.id where u.id = '".$userid."'");

	$user = $db->get_row("select u.*,t.user_type_code, t.user_type_subgroup from ss_usertypeusermap m 
	INNER JOIN ss_user u ON u.id = m.user_id inner join ss_usertype t on m.user_type_id = t.id 
	where m.user_id = '" . $userid . "' order by m.id desc ");

	if ($user->user_type_code == 'UT01') {
		if ($user->user_type_subgroup == 'principal') {
			$fullname = 'Principal';
			$firstname = 'Principal';
		} else {
			$fullname = 'Administrator';
			$firstname = 'Administrator';
		}
	} elseif ($user->user_type_code == 'UT02' || $user->user_type_code == 'UT04') {
		$staff = $db->get_row("select * from ss_staff where user_id = '" . $userid . "'");
		$fullname = $staff->first_name . ' ' . trim($staff->middle_name . ' ') . $staff->last_name;
		if ($staff->middle_name) {
			$fullname = $staff->first_name . ' ' . $staff->middle_name . ' ' . $staff->last_name;
		}
		$firstname = $staff->first_name;
	} elseif ($user->user_type_code == 'UT03') {
		$student = $db->get_row("select * from ss_student where user_id = '" . $userid . "'");
		$fullname = $student->first_name . ' ' . trim($student->middle_name . ' ') . $student->last_name;
		if ($student->middle_name) {
			$fullname = $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name;
		}
		$firstname = $student->first_name;
	} elseif ($user->user_type_code == 'UT05') {
		$family = $db->get_row("select * from ss_family where user_id = '" . $userid . "'");
		$fullname = $family->father_first_name . ' ' . $family->father_last_name;
		$firstname = $family->father_first_name;
	}


	return $onlyFirstName ? $firstname : $fullname;
}

function slug($text)
{
	// replace non letter or digits by -
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	// trim
	$text = trim($text, '-');

	// remove duplicate -
	$text = preg_replace('~-+~', '-', $text);

	// lowercase
	$text = strtolower($text);

	if (empty($text)) {
		return 'n-a';
	}

	return $text;
}

//ADDED ON 15-AUG-2018
function getRandomString($passLength)
{
	$alphabet = "ABCDEFGHIJKLMNOPQRSTUWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < $passLength; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}

function RandomString($length = 16)
{
	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
}

/*//ADDED ON 15-AUG-2018
function encodeVar($var){
	echo urlencode(base64_encode(getRandomString(4).$var));
}

//ADDED ON 15-AUG-2018
function decodeVar($var){
	echo substr(base64_decode(urldecode($var)),4);
}*/

//ADDED ON 16-AUG-2018
//CHECK USER ROLE BY USER CODE
function check_userrole_by_code($user_type_code)
{
	//$user_type_code_ary = array();
	//$user_type_code_ary = $_SESSION['icksumm_uat_login_usertypecode_ary'];
	//return in_array($user_type_code,$user_type_code_ary);
	if ($_SESSION['icksumm_uat_login_usertypecode'] == $user_type_code) {
		return true;
	} else {
		return false;
	}
}

//ADDED ON 16-AUG-2018
//CHECK USER ROLE BY USER CODE
function check_userrole_by_group($user_type_group)
{
	//$user_type_group_ary = array();
	//$user_type_group_ary = $_SESSION['icksumm_uat_login_usertypegroup_ary'];
	//return in_array($user_type_group,$user_type_group_ary);
	if ($_SESSION['icksumm_uat_login_usertypegroup'] == $user_type_group) {
		return true;
	} else {
		return false;
	}
}

function check_userrole_by_subgroup($user_type_subgroup)
{
	//$user_type_group_ary = array();
	//$user_type_group_ary = $_SESSION['icksumm_uat_login_usertypegroup_ary'];
	//return in_array($user_type_group,$user_type_group_ary);
	if ($_SESSION['icksumm_uat_login_usertypesubgroup'] == $user_type_subgroup) {
		return true;
	} else {
		return false;
	}
}

function get_user_role_waise_permission($userid, $user_type_id)
{


	global $db;

	// $user_role_id = $db->get_var("select role_id from ss_user_role_map where user_id = '".$userid."' AND status=1 ");

	$user_role_id = $db->get_var("select t.role_id from ss_usertypeusermap m INNER JOIN ss_usertype t ON t.id = m.user_type_id where m.user_type_id = '" . $user_type_id . "' and m.user_id = '" . $userid . "'");

	$user_permissions = $db->get_results("select permission from ss_role_wise_permissions INNER JOIN ss_permissions  ON ss_role_wise_permissions.permission_id = ss_permissions.id where ss_role_wise_permissions.role_id = '" . $user_role_id . "'");

	$user_extra_permissions = $db->get_results("select ss_permissions.permission from ss_user_extra_permissions INNER JOIN ss_permissions ON ss_user_extra_permissions.extra_permission_id = ss_permissions.id  where ss_user_extra_permissions.user_id = '" . $userid . "'");


	$arrayExtraPermission = [];
	foreach ($user_extra_permissions as $rows) {
		$arrayExtraPermission[] = $rows->permission;
	}

	$arrayPermission = [];
	foreach ($user_permissions as $row) {
		$arrayPermission[] = $row->permission;
	}

	return $finalPermissionsArray = array_unique(array_merge($arrayPermission, $arrayExtraPermission));
}
//Mail Body Header 
function mail_body_header()
{
	$header = ' 
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
  @media (max-width: 400px) {
    .chunk {
    width: 100% !important;
    }
  }
  </style>
  </head>
<body> 
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
      <td style="padding:0px; text-align:center">
      <table border="0" cellpadding="10" cellspacing="0" width="100%">
      <tr><td style="text-align:center" colspan="2"><img src="' . SITEURL . SCHOOL_LOGO . '"><br><p><span style="font-size:22px;">' . ORGANIZATION_NAME . '</span><br><span style="font-size:15px;margin-top:4px;">' . SCHOOL_ADDRESS . ', ' . SCHOOL_ADDRESSCITY . ', ' . SCHOOL_ADDRESSSTATE . ', ' . SCHOOL_ADDRESSZIPCODE . '</span></p></td>
	  </tr>
	  <br>';
	return $header;
}
//Mail Body Footer
function mail_body_footer()
{
	$footer = '<tr>
      <td style="color:#333; text-align:left; border-top: solid 3px #333;" colspan="3">
	  	<table align="left" style="width: 35%; min-width: 400px;" class="chunk">
        <tr><td><strong style="color:#333;">Email:</strong> <a href="mailto:' . SCHOOL_GEN_EMAIL . '" style="color:#333; text-decoration:none;">' . SCHOOL_GEN_EMAIL . '</a></td></tr>
        </table>
        <table align="center" style="width: 65%; min-width: 400px;float: right;" class="chunk">
        <tr><td style=" text-align:left;"><strong style="color:#333;">Tel:</strong> <a href="tel:' . SCHOOL_CONTACT_NO . '" style="color:#333; text-decoration:none;">' . SCHOOL_CONTACT_NO . '</a></td>
        </table>
      </td>
      </tr>         
      </table>
      </td>
      </tr>
      </table></body></html>';
	return $footer;
}
//Mail Service
function mailservice($data_request)
{
	$url = "https://mailservice.bayyan.org/mail-request.php";
	$body = $data_request['message'];


	$full_message = mail_body_header() . '<tr><td style="background:#fff;text-align: left;" colspan="3">' . $body . '</td></tr>' . mail_body_footer();
	$data_request = (object) array_merge((array) $data_request, (array) ['message' => $full_message]);

	$request = json_encode($data_request);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_BUFFERSIZE, 84000); // curl buffer size in bytes
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$info = curl_getinfo($ch); // curl analysis
	curl_close($ch);
}
//-----------------------FIle---------------------//
function reArrayFiles(&$file_post)
{
	$file_ary = array();
	$file_count = count((array)$file_post['name']);
	$file_keys = array_keys($file_post);
	for ($i = 0; $i < $file_count; $i++) {
		foreach ($file_keys as $key) {
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}
	return $file_ary;
}
//-----------------------IMAGE  TO BINARY---------------------//
function image_binary($image_path)
{
	$path = ROOTPATH . $image_path;
	$filename = basename($path);
	$type = pathinfo($filename, PATHINFO_EXTENSION);
	$data = file_get_contents($path);
	return $pic = 'data:image/' . $type . ';base64,' . base64_encode($data);
}


//-----------------------ENCRIPTION IN PAYMENT SYSTEM TOKEN---------------------//
function genrate_encrypt_token($paygateway)
{
	$textToEncrypt = "'" . $_SERVER['SERVER_NAME'] . "', '" . $paygateway . "', '" . bin2hex(random_bytes(10)) . "'";
	$encryptionMethod = ENCRYPTION_METHOD;
	$secretHash = SECRET_HASH;
	$iv = IV;
	return $encryptedMessage = openssl_encrypt($textToEncrypt, $encryptionMethod, $secretHash, 0, $iv);
}


function genrate_decrypt_token($token)
{

	$encryptionMethod = ENCRYPTION_METHOD;
	$secretHash = SECRET_HASH;
	$iv = IV;

	return $decryptedMessage = openssl_decrypt($token, $encryptionMethod, $secretHash, 0, $iv);
}

//-----------------------URL REQUEST RESPONSE CURL---------------------//
function curl_request($URL)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $URL);
	$data = curl_exec($ch);
	return json_decode($data);
}

//-----------------------URL REQUEST Data with URL---------------------//
function response_post_service($data_request, $url)
{
	$request = json_encode($data_request);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_BUFFERSIZE, 84000); // curl buffer size in bytes
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$curl_errno = curl_errno($ch);
	$info = curl_getinfo($ch); // curl analysis
	curl_close($ch);
	return json_decode($response);
}



function student_fee_discount($user_id)
{

	global $db;

	$fees_monthly = $db->get_var("SELECT fees_monthly FROM  ss_client_settings  WHERE status = 1");
	if ($fees_monthly == 1) {
		$school_session_month = null;
	} else {

		$sch_sessions = $db->get_row("select *  from ss_school_sessions s where s.current = 1 ");
		if (!empty($sch_sessions) && !empty($sch_sessions->start_date) && !empty($sch_sessions->end_date)) {
			$date1 = $sch_sessions->start_date;
			$date2 = $sch_sessions->end_date;
			$ts1 = strtotime($date1);
			$ts2 = strtotime($date2);
			$year1 = date('Y', $ts1);
			$year2 = date('Y', $ts2);
			$month1 = date('m', $ts1);
			$month2 = date('m', $ts2);
			$school_session_month = (($year2 - $year1) * 12) + ($month2 - $month1);
		} else {
			$school_session_month = null;
		}
	}


	$user_groups = $db->get_results("select g.id from ss_groups g INNER JOIN ss_studentgroupmap m ON m.group_id = g.id where m.student_user_id='" . $user_id . "' and m.latest = 1 and m.session = '" .
		$_SESSION['icksumm_uat_CURRENT_SESSION'] . "' and g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'");
	$groups = [];
	foreach ($user_groups as $group) {
		$groups[] = $group->id;
	}
	$group_ids = implode(",", $groups);
	$basicFees = $db->get_row("select fee_amount from ss_basicfees where group_id IN (" . $group_ids . ") AND status = 1 AND session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' ");
	$discountFeesData = $db->get_results("select * from ss_student_feesdiscounts sf INNER JOIN ss_fees_discounts d ON d.id = sf.fees_discount_id where sf.student_user_id = '" . $user_id . "' AND sf.status = 1  and d.status=1");
	$discountPercentTotal = $basicFees->fee_amount;
	$discountDollarTotal = 0;
	foreach ($discountFeesData as $val) {
		if ($val->discount_unit == 'd') {
			$discountDollarTotal += $val->discount_percent;
		} elseif ($val->discount_unit == 'p') {
			$fee_percent = ($discountPercentTotal * $val->discount_percent) / 100;
			$discountPercentTotal = $discountPercentTotal - $fee_percent;
		}
	}
	$final_amount = ($discountPercentTotal - $discountDollarTotal);
	if ($final_amount > 0) {
		if (!empty($sch_sessions) && !empty($sch_sessions->fees_full_payment_discount_unit) && !empty($sch_sessions->fees_full_payment_discount_value)) {
			if (strtolower($sch_sessions->fees_full_payment_discount_unit) == 'p') {
				$pre_final_amount = $final_amount * $school_session_month;
				$discountFees = ($pre_final_amount * $sch_sessions->fees_full_payment_discount_value) / 100;
				$actualbasicDiscountFees = $pre_final_amount - $discountFees;
			} else {
				$pre_final_amount = $final_amount * $school_session_month;
				$actualbasicDiscountFees = $pre_final_amount - $sch_sessions->fees_full_payment_discount_value;
			}
		} else {
			$actualbasicDiscountFees = $final_amount;
		}
	} else {
		$actualbasicDiscountFees = 0;
	}
	// $finalAllStudentFeeAmount += $actualbasicDiscountFees;
	return $userDataAmount = $actualbasicDiscountFees;
}



function get_schedule_payments_results($stu_user_id)
{

	global $db;

	$array =  $db->get_results("SELECT sfi.* 
	FROM ss_student_fees_items sfi
	INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
	INNER JOIN ss_user u ON u.id = s.user_id
	INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
    WHERE sfi.student_user_id = " . $stu_user_id . " AND sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sfi.schedule_payment_date >= '" . date('Y-m-d') . "' AND u.is_active = 1
    AND u.is_deleted = 0 AND u.is_locked=0 AND (sfi.schedule_status = 0 OR sfi.schedule_status = 3) ");
	return $array;
}


function get_family_schedule_payment($family_id = null)
{
	global $db;

	$sql_quey = "SELECT f.id as parent_id, sfi.schedule_payment_date, sfi.schedule_unique_id, f.father_first_name,f.father_last_name,f.father_phone, f.mother_first_name,f.mother_last_name,f.primary_email,
	f.secondary_email,billing_address_1,billing_address_2,billing_city,billing_state_id,billing_entered_state,billing_country_id,billing_post_code,forte_customer_token,
    pay.credit_card_no, GROUP_CONCAT(DISTINCT(s.user_id)) AS  user_id,GROUP_CONCAT(DISTINCT CONCAT(s.first_name,' ',s.last_name)) as student_name
    FROM ss_student_fees_items sfi
    INNER JOIN ss_student s ON sfi.student_user_id = s.user_id
    INNER JOIN ss_user u ON u.id = s.user_id
    INNER JOIN ss_student_session_map ssm ON ssm.student_user_id = u.id
    INNER JOIN ss_family f ON f.id = s.family_id
    INNER JOIN ss_paymentcredentials pay ON pay.family_id = s.family_id
    WHERE sfi.session='" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sfi.schedule_payment_date >= '" . date('Y-m-d') . "' AND u.is_active = 1
    AND u.is_deleted = 0 AND pay.default_credit_card =1 AND u.is_locked=0 AND (sfi.schedule_status = 0 OR sfi.schedule_status = 3) ";

	$sql_quey_two = " GROUP by s.family_id ORDER BY sfi.id desc";

	if ($family_id > 0) {
		$final_qur = $sql_quey . ' and f.id=' . $family_id . ' ' . $sql_quey_two;
		return $family_schedule_payment = $db->get_row($final_qur);
	} else {
		$final_qur = $sql_quey . $sql_quey_two;
		return $family_schedule_payment = $db->get_results($final_qur);
	}
}


function get_schedule_payment_cron($family_id = null)
{

	global $db;

	$quer_sql = "SELECT  id,schedule_unique_id,schedule_payment_date,sch_item_ids,family_id,`session`, total_amount as old_total_amount,created_at,updated_at,wallet_amount,cc_amount,schedule_status,retry_count,is_approval,reason,payment_unique_id,payment_response_code,payment_response
	FROM `ss_payment_sch_item_cron` sfic
	WHERE sfic.schedule_status = 0 AND sfic.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sfic.is_approval='1'";

	$quer_sql_two = " ORDER BY sfic.schedule_payment_date ASC";

	if ($family_id > 0) {
		$final_qur = $quer_sql . ' and family_id=' . $family_id . ' ' . $quer_sql_two;
		return $family_schedule_payment = $db->get_results($final_qur);
	} else {
		$final_qur = $quer_sql . $quer_sql_two;
		return $family_schedule_payment = $db->get_results($final_qur);
	}
}

//payment confirmation popup
function payment_confirmation_check($family_schedule_payment, $schedule_payment_cron, $slug_text = null)
{

	if (count((array)$schedule_payment_cron) > 0 && count((array)$family_schedule_payment) > 0) {
		$confirm_check_con = 1;
		$confirm_msg = "A payment reminder has already been sent to the parents. Do you still want to send this reminder and cancel the previous ones?";
	} else {
		$confirm_check_con = 0;
		$text_note = "";
	}

	// if(count((array)$schedule_payment_cron) > 0 && count((array)$family_schedule_payment) > 0){
	// 	$confirm_check_con = 1;
	// 	if(count((array)$schedule_payment_cron) > 0 ){
	// 		$schedule_payment_cron_count = count((array)$schedule_payment_cron);
	// 	}else{
	// 		$schedule_payment_cron_count = 0;
	// 	}
	// 	if(count((array)$family_schedule_payment) > 0){
	// 		$family_schedule_payment_count = count((array)$family_schedule_payment);
	// 	}else{
	// 		$family_schedule_payment_count = 0;
	// 	}

	// 	if(count((array)$schedule_payment_cron) > 0 && isset($schedule_payment_cron[0]->schedule_payment_date)){
	// 		$near_by_payment_date = Date('m/d/Y',strtotime($schedule_payment_cron[0]->schedule_payment_date));
	// 	}else{
	// 		$near_by_payment_date = Date('m/d/Y',strtotime($family_schedule_payment[0]->schedule_payment_date));
	// 	}

	// 	$text_note = "We have already sent the payment reminder with the current fees to the ".str_pad($schedule_payment_cron_count, 2, '0', STR_PAD_LEFT)." families. There are ".str_pad($family_schedule_payment_count, 2, '0', STR_PAD_LEFT)."  family payments in the current scheduled list.";

	// }elseif(count((array)$schedule_payment_cron) > 0 && count((array)$family_schedule_payment) == 0){
	// 	$confirm_check_con = 1;
	// 	$family_schedule_payment_count = count((array)$schedule_payment_cron);
	// 	$near_by_payment_date = Date('m/d/Y',strtotime($schedule_payment_cron[0]->schedule_payment_date));
	// 	$text_note = "We have already sent the payment reminder with the current fees to the ".str_pad($family_schedule_payment_count, 2, '0', STR_PAD_LEFT)." families.";

	// }elseif(count((array)$family_schedule_payment) > 0 && count((array)$schedule_payment_cron) == 0){
	// 	$confirm_check_con = 1;
	// 	$family_schedule_payment_count = count((array)$family_schedule_payment);
	// 	$near_by_payment_date = Date('m/d/Y',strtotime($family_schedule_payment[0]->schedule_payment_date));
	// 	$text_note = "There are ".str_pad($family_schedule_payment_count, 2, '0', STR_PAD_LEFT)."  family payments in the current scheduled list.";

	// }else{
	// 	$confirm_check_con = 0;
	// 	$text_note = "";

	// }

	//$confirm_msg = "Next payment will be deducted on ".$near_by_payment_date." ".$text_note." If you change the fees then all families will get new payment reminders of updated fees.";


	$obj_array = (object) array('confirm_check_con' => $confirm_check_con, 'confirm_msg' => $confirm_msg);
	return $obj_array;
}


function get_payment_on_excution($family_id = null)
{
	global $db;
	$quer_sqls = "SELECT  id,schedule_unique_id,schedule_payment_date,sch_item_ids,family_id,`session`, total_amount as old_total_amount,created_at,updated_at,wallet_amount,cc_amount,schedule_status,retry_count,is_approval,reason,payment_unique_id,payment_response_code,payment_response
	FROM `ss_payment_sch_item_cron` sfic
	WHERE sfic.schedule_status <> 0 AND sfic.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' AND sfic.is_approval='1' ";

	$quer_sql_two = " ORDER BY sfic.schedule_payment_date ASC";
	if ($family_id > 0) {
		$final_qur = $quer_sqls . ' and family_id=' . $family_id . ' ' . $quer_sql_two;
		return $payment_on_excution = $db->get_results($final_qur);
	} else {
		$final_qur = $quer_sqls . $quer_sql_two;
		return $payment_on_excution = $db->get_results($final_qur);
	}
}



//schedule_payment_update_notify
function schedule_payment_update_notify($family, $email_text)
{

	$emailbody = "Assalamualaikum, <strong> " . $family->father_first_name . " " . $family->father_last_name . "</strong><br><br>";
	$emailbody .= $email_text . "<br><br><br>";
	$emailbody .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME . ' Team';
	$emailbody .= "<br><br>For any comments or question, please send email to " . SUPPORT_EMAIL . "";
	$subject = CENTER_SHORTNAME . " " . SCHOOL_NAME . " - New Payment Reminder ";

	$mail_service_array = array(
		'subject' => $subject,
		'message' => $emailbody,
		'request_from' => MAIL_SERVICE_KEY,
		'attachment_file_name' => [],
		'attachment_file' => [],
		'to_email' => [$family->primary_email, SCHOOL_GEN_EMAIL],
		'cc_email' => [],
		'bcc_email' => []
	);

	mailservice($mail_service_array);
}



//INVOICE GENRATE AND SEND EMAIL
//INVOICE GENRATE AND SEND EMAIL
function genrate_and_send_invoice($family_data)
{

	global $db;
	$invoce_id = mt_rand();
	include "../payment/invoice_pdf.php";

	define("DOMPDF_UNICODE_ENABLED", true);

	$dompdf = new Dompdf;
	$dompdf->loadHtml($html);

	// (Optional) Setup the paper size and orientation 
	$dompdf->setPaper('Executive', 'Potrate');

	// Render the HTML as PDF 
	$dompdf->render();
	$path = '../payment/invoice_and_pdf';
	$filename = "/" . $invoce_id;
	$output = $dompdf->output();
	$fullpath = $invoce_id . 'invoice.pdf';
	file_put_contents($path . $filename . 'invoice.pdf', $output);
	unset($dompdf);
	$file_path = SITEURL . 'payment/invoice_and_pdf/' . $fullpath;
	//--------------Invoice Insert-----------//
	/*  $Student_invoice_Id = $db->get_var("SELECT id FROM `ss_invoice` where schedule_unique_id = '" . $remind->schedule_unique_id . "' ");
	if (!empty($Student_invoice_Id)) {
		$db->query("update ss_invoice set invoice_id = '" . $invoce_id . "', invoice_date = '" . date('Y-m-d') . "', is_due = '1', invoice_file_path = '" . $fullpath . "' where schedule_unique_id = '" . $remind->schedule_unique_id . "' ");
	} else {
	} */
	if (!empty($family_data->schedule_unique_id) && !empty($family_data->family_id)) {
		$res = $db->query("update ss_invoice set status = 0 where schedule_unique_id = '" . $family_data->schedule_unique_id . "' and family_id='" . $family_data->family_id . "'");

		$db->query("insert into ss_invoice set  family_id='" . $family_data->family_id . "',schedule_unique_id='" . $family_data->schedule_unique_id . "', invoice_id='" . $invoce_id . "', invoice_date='" . date('Y-m-d') . "', amount='" . $family_data->total_amount . "', is_due='0', status = '1', created_at='" . date('Y-m-d H:i:s') . "', invoice_file_path='" . $fullpath . "', created_by='" . $_SESSION['icksumm_uat_login_userid'] . "'");
		$star = '************';
		$credit_card_no = $star . substr(str_replace(' ', '', base64_decode($family_data->credit_card_no)), -4);
		$emailbody_support = "Assalamualaikum, <strong> " . $family_data->father_first_name . " " . $family_data->father_last_name . "</strong><br><br>";
		$emailbody_support .= "Please ignore the previous mail. The new payment details are as follows - <br><br>";

		if (isset($family_data->new_schedule_payment_date) && !empty($family_data->new_schedule_payment_date)) {
			$emailbody_support .= "<b>New Schedule Date : </b> " . date('m/d/Y', strtotime($family_data->new_schedule_payment_date)) . " .<br>";
		} else {
			$emailbody_support .= "<b>Schedule Date : </b> " . date('m/d/Y', strtotime($family_data->schedule_payment_date)) . " .<br>";
		}

		if (isset($family_data->total_amount) && !empty($family_data->total_amount) && isset($family_data->old_total_amount) && !empty($family_data->old_total_amount)) {
			$emailbody_support .= "<b>New Payable Amount : </b> $" . $family_data->total_amount . " .<br>";
		} else {
			$emailbody_support .= "<b>Payable Amount : </b> $" . $family_data->total_amount . " .<br>";
		}

		// $emailbody_support .= "<br><br>Please find the attached receipt with new updated. <br>";

		//$emailbody_support .= "<br>Your child basic fees is updated from $".$family_data->old_total_amount." to $".$family_data->total_amount.". Please find the attached receipt with new updated fees.";
		$emailbody_support_last .= '<br><br>' . BEST_REGARDS_TEXT . '<br>' . ORGANIZATION_NAME . ' Team';
		$emailbody_support_last .= "<br><br>For any comments or question, please send email to " . SUPPORT_EMAIL . "";
		$subject = CENTER_SHORTNAME . " " . SCHOOL_NAME . " - New Payment Reminder ";

		$html = str_replace("src", "", $html);
		// $html=str_replace("Company logo","<h1>Invoice</h1>",$html);
		$mail_service_array = array(
			'subject' => $subject,
			'message' => $emailbody_support . "<br>" . $html . "<br>" . $emailbody_support_last,
			'request_from' => MAIL_SERVICE_KEY,
			'attachment_file_name' => '',
			'attachment_file' => '',
			'to_email' => [$family_data->primary_email, SCHOOL_GEN_EMAIL],
			'cc_email' => [],
			'bcc_email' => []
		);

		mailservice($mail_service_array);
	}
}

function check_user_type($user_type_code, $user_id)
{
	global $db;
	$user_type_id = $db->get_var("SELECT ut.id FROM ss_usertype ut INNER JOIN ss_usertypeusermap m ON ut.id = m.user_type_id WHERE user_type_code = '" . $user_type_code . "' AND m.user_id = '" . $user_id . "'");
	return $user_type_id;
}

function messagesender($user_id, $reply = null, $user_type_code = null)
{
	global $db;
	if (!empty($reply)) {
		$sender_user_type = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_type_id = '" . $reply . "' AND user_id = '" . $user_id . "'");
		$user_type = check_user_type($user_type_code, $user_id);
		if (!empty($user_type)) {
			$created_user_type = check_user_type($user_type_code, $user_id);
		} else {
			if (!empty($sender_user_type)) {
				$created_user_type = $sender_user_type;
			} else {
				$created_user_type = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_id = '" . $user_id . "'");
			}
		}
	} else {
		if (!empty($user_type_code)) {
			$created_user_type = check_user_type($user_type_code, $user_id);
		} else {
			$created_user_type = $db->get_var("SELECT m.user_type_id FROM ss_user u INNER JOIN ss_usertypeusermap m ON u.id = m.user_id WHERE u.id = '" . $user_id . "'");
		}
	}
	return $created_user_type;
}
function messagereceiver($staff_user_id, $students = null, $reply = null)
{
	global $db;
	//echo $staff_user_id."<br>";
	if (!empty($reply)) {
		$check_reply = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_type_id = '" . $reply . "' AND user_id = '" . $staff_user_id . "'");
	} else {
		$check_staff = $db->get_var("SELECT m.user_type_id FROM ss_staff s INNER JOIN ss_usertypeusermap m ON s.user_id = m.user_id INNER JOIN ss_usertype ut ON m.user_type_id = ut.id WHERE s.user_id = '" . $staff_user_id . "' AND ut.user_type_group = 'staff'");
		$check_parent = $db->get_var("SELECT DISTINCT f.user_id AS family_user_id FROM ss_student s INNER JOIN ss_family f ON f.id = s.family_id WHERE s.user_id IN ('" . implode("','", (array)$students) . "') AND f.user_id = '" . $staff_user_id . "'");
	}

	if (!empty($check_parent)) {
		$rec_user_type = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_id = '" . $check_parent . "'");
	} elseif (!empty($check_staff)) {
		$rec_user_type = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_id = '" . $staff_user_id . "' AND user_type_id = '" . $check_staff . "'");
	} elseif (!empty($check_reply)) {
		$rec_user_type = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_id = '" . $staff_user_id . "' AND user_type_id = '" . $check_reply . "'");
	} else {
		$rec_user_type = $db->get_var("SELECT user_type_id FROM ss_usertypeusermap WHERE user_id = '" . $staff_user_id . "'");
	}

	return $rec_user_type;
}

function get_country()
{
	global $db;
	$country_id = $db->get_var("SELECT country_id FROM ss_client_settings WHERE STATUS=1 ");
	$get_country = $db->get_row("SELECT id, country, abbreviation FROM ss_country WHERE is_active = 1 AND id = '" . $country_id . "'");
	if (strtoupper($get_country->abbreviation) == 'GB') {
		$timezone = date_default_timezone_set('Europe/London');
		$length = '8';
		$validator = '<script type="text/javascript" src="' . SITEURL . 'assets/js/country_waise_validator/uk_zipcode.js"></script>';
		$currency = '£';
		$discount_val = 'L';
		$discount_option = 'Pound';
		$country = $country_id;
		$phone = "jQuery.validator.addMethod('PhoneNumber', function(value, element) {
			return this.optional(element) || /^\d{3}-?\d{3}-?\d{4}$/i.test(value);
		}, 'Enter valid phone number');";
		$alternateno = 'XXXXXXXXXX';
	} elseif (strtoupper($get_country->abbreviation) == 'USA') {
		$timezone = date_default_timezone_set("America/Chicago");
		$length = '5';
		$validator = '<script type="text/javascript" src="' . SITEURL . 'assets/js/country_waise_validator/usa_zipcode.js"></script>';
		$currency = '$';
		$discount_val = 'd';
		$discount_option = 'Dollar';
		$country = $country_id;
		$phone = "$('.phone_no').mask('000-000-0000');

					jQuery.validator.addMethod('PhoneNumber', function(value, element) {
						return this.optional(element) || /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i.test(value);
					}, 'Enter valid phone number');";
		$alternateno = 'XXX-XXX-XXXX';
	}
	return  $country_based_data = (object)['country_id' => strtoupper($country), 'abbreviation' => strtoupper($get_country->abbreviation), 'country' => strtoupper($get_country->country), 'length' => $length, 'validator' => $validator, 'currency' => $currency, 'discount_option' => $discount_option, 'discount_val' => $discount_val, 'phone_formate' => $phone, 'alternateno' => $alternateno, 'timezone' => $timezone];
}

function my_date_changer($my_date, $calender_and_time = null)
{

	$all_detail = get_country();
	$country_id =  $all_detail->country_id;
	$country_abbreviation =  $all_detail->abbreviation;
	// $currency = $all_detail->currency;

	if (!empty($my_date)) {

		if (!empty($country_id) && !empty($country_abbreviation)) {
			if (strtoupper($country_abbreviation) == 'USA') { // USA is used United States

				if ($my_date === 'd mmmm, yyyy') {
					return 'mmmm d yyyy';
				} elseif ($my_date === 'DD MMMM,YYYY') {            ///// daterangepicker
					return 'MMMM D YYYY';
				} elseif (!empty($my_date) && $calender_and_time === 'c') {

					return date('F d Y', strtotime($my_date));
				} elseif (!empty($my_date) && !empty($calender_and_time) == 't') {

					return date('m/d/Y h:i a', strtotime($my_date));
				} elseif (!empty($my_date)) {

					return date('m/d/Y', strtotime($my_date));
				} else {
					return "problem in US";
				}
			} elseif (strtoupper($country_abbreviation) == 'GB') { // GB is used United Kingdom

				if ($my_date === 'd mmmm, yyyy') {
					return 'd mmmm yyyy';
				} elseif ($my_date === 'DD MMMM,YYYY') {      //// daterangepicker
					return 'DD MMMM YYYY';
				} elseif (!empty($my_date) && $calender_and_time === 'c') {

					return date('d F Y', strtotime($my_date));
				} elseif (!empty($my_date) && !empty($calender_and_time) == 't') {

					return date('d-m-Y H:i a', strtotime($my_date));
				} elseif (!empty($my_date)) {

					return date('d-m-Y', strtotime($my_date));
				}
			} else {
				return "Problem in UK.";
			}
		} else {
			return " abbreviation and id is not available ";
		}
	}
}

function internal_phone_check($phone_no_check, $action_type = null)
{
	$country_abbreviation = get_country()->abbreviation;
	$result =  str_replace('-', '', $phone_no_check);
	if (empty($result)) {
		$result = $phone_no_check;
	}
	if (strtoupper($country_abbreviation) == 'USA') {
		if (preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $phone_no_check, $matches)) {
			return $matches[0];
		} else {
			$spilt_phone_no  = str_split($phone_no_check);
			$final_phone_no  = $spilt_phone_no[0] . $spilt_phone_no[1] . $spilt_phone_no[2] . '-' . $spilt_phone_no[3] . $spilt_phone_no[4] . $spilt_phone_no[5] . '-' . $spilt_phone_no[6] . $spilt_phone_no[7] . $spilt_phone_no[8] . $spilt_phone_no[9];
			return $final_phone_no;
		}
	} elseif (strtoupper($country_abbreviation) == 'GB') {
		if (preg_match('/^\d{3}-?\d{3}-?\d{4}$/', $result,  $matches)) {
			$spilt_phone_no  = str_split($matches[0]);
			$final_phone_no  = $spilt_phone_no[0] . $spilt_phone_no[1] . $spilt_phone_no[2] . $spilt_phone_no[3] . ' ' . $spilt_phone_no[4] . $spilt_phone_no[5] . $spilt_phone_no[6] . $spilt_phone_no[7] . $spilt_phone_no[8] . $spilt_phone_no[9];
			if ($action_type == 'edit') {
				return $matches[0];
			} else {
				return $final_phone_no;
			}
		}
	}
}

function phone_sms($receiver_mobile_no, $message_text = null)
{

	if (strlen($receiver_mobile_no) == 10) {
		$receiver_mobile_no = '+44' . $receiver_mobile_no;
	}

	$url = "https://api.twilio.com/2010-04-01/Accounts/AC03d61d504f943a06ee253f79861547b1/Messages.json";
	$from = "441509323059";
	$to = $receiver_mobile_no;
	$id = "AC03d61d504f943a06ee253f79861547b1";
	$token = "0069c93d420fdd2713c06d0f045dc7de";
	$data = array(
		'From' => $from,
		'To' => $to,
		'Body' => $message_text,

	);
	$post = http_build_query($data);
	$x = curl_init($url);
	curl_setopt($x, CURLOPT_POST, true);
	curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
	curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($x, CURLOPT_POSTFIELDS, $post);
	//var_dump($post);
	$output = curl_exec($x);
	curl_close($x);

	$dec = json_decode($output, true);
	if (($dec['status'] == 'queued' || $dec['status'] == 'sent') && !empty($dec['sid'])) {
		$status = 1;
		$error_code = null;
		$error_message = null;
	} else {
		$status = 0;
		if (isset($dec['error_code']) && !empty($dec['error_code'])) {
			$error_code = $dec['error_code'];
		} else {
			$error_code = null;
		}
		if (isset($dec['error_message']) && !empty($dec['error_message'])) {
			$error_message = $dec['error_message'];
		} else {
			$error_message = null;
		}
	}

	$respose_array = (object)['status' => $status, 'error_code' => $error_code, 'error_message' => $error_message, 'response' => $dec];
	return json_encode($respose_array);
}
