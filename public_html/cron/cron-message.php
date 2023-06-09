<?php
//LIVE - PROD SITE
//set_include_path('/home3/bayyanor/public_html/ick/academy_new/includes/');
//DEV
set_include_path('/webroot/b/a/bayyan005/icksaturdayqa.click2clock.com/www/includes/');

include_once "config.php";
date_default_timezone_set("America/Chicago");
//CURRENT SESSION
$current_session_row = $db->get_row("select * from ss_school_sessions where current = 1 ");
$current_session = $current_session_row->id;
//ADDED ON 15-DEC-2020
$max_attempt_count = 100;

$requests = $db->get_results("select * from ss_bulk_message_emails where delivery_status <> 1 and attempt_counter < " . $max_attempt_count . " 
order by attempt_counter asc limit 100");

//ADDED ON 21-DEC-2020
foreach ($requests as $req) {
	$message = $db->get_row("select * from ss_bulk_message where id = '" . $req->bulk_message_id . "' and scheduled_time <= '" . date('Y-m-d H:i:s') . "'");
	if (strtotime($message->scheduled_time)  <=  strtotime(date('Y-m-d H:i:s'))) {
		$new_msg_body = $message->message;


		if(!empty($req->family_id)){

			$requests = $db->get_row("SELECT f.user_id, f.father_first_name, f.father_last_name, f.father_area_code, f.father_phone,f.mother_first_name, f.mother_last_name, 
			f.mother_area_code, f.mother_phone, f.primary_email, f.secondary_email, f.primary_contact, f.billing_address_1, f.billing_address_2, f.billing_city, 
			f.billing_state_id, f.billing_entered_state, f.billing_country_id, f.billing_post_code, s.first_name, s.middle_name, s.last_name, s.dob, s.gender, 
			s.allergies, s.school_grade, st.state 
			from ss_family f 
			INNER JOIN ss_student s ON s.family_id = f.id
			Inner join ss_user u on u.id=s.user_id
			LEFT JOIN ss_state st ON st.id = f.billing_entered_state 
			INNER JOIN ss_student_session_map ssm on ssm.student_user_id = s.user_id 
			where f.id = '" . $req->family_id . "' AND ssm.session_id = '" . $current_session . "' and u.is_active=1 and u.is_deleted=0
			AND e.delivery_status <> 1 and e.attempt_counter < " . $max_attempt_count . " order by e.attempt_counter asc limit 30");

			$new_msg_body = str_replace('{parent1_first_name}', $value->father_first_name, $new_msg_body);
			$new_msg_body = str_replace('{parent1_last_name}', $value->father_last_name, $new_msg_body);
			$new_msg_body = str_replace('{parent1_phone}', $value->father_phone, $new_msg_body);
			$new_msg_body = str_replace('{parent1_email}', trim($value->primary_email), $new_msg_body);
			$new_msg_body = str_replace('{parent2_first_name}', $value->mother_first_name, $new_msg_body);
			$new_msg_body = str_replace('{parent2_last_name}', $value->mother_last_name, $new_msg_body);
			$new_msg_body = str_replace('{parent2_email}', $value->secondary_email, $new_msg_body);
			$new_msg_body = str_replace('{parent2_phone}', $value->mother_phone, $new_msg_body);
			$new_msg_body = str_replace('{address_1}', $value->billing_address_1, $new_msg_body);
			$new_msg_body = str_replace('{address_2}', $value->billing_address_2, $new_msg_body);
			$new_msg_body = str_replace('{city}', $value->billing_city, $new_msg_body);
			$new_msg_body = str_replace('{state_name}', $value->state, $new_msg_body);
			$new_msg_body = str_replace('{child1_first_name}', $value->first_name, $new_msg_body);
			$new_msg_body = str_replace('{child1_last_name}', $value->last_name, $new_msg_body);
			$new_msg_body = str_replace('{child1_dob}', $value->dob, $new_msg_body);
			$new_msg_body = str_replace('{child1_grade}', $value->school_grade, $new_msg_body);
			$new_msg_body = str_replace('{child1_gender}', $value->gender, $new_msg_body);
			$new_msg_body = str_replace('{child1_allergies}', $value->allergies, $new_msg_body);


		}



		if (!empty($message)) {
			$attachments = $db->get_results("select * from ss_bulk_message_attachment inner join ss_bulk_message_emails on ss_bulk_message_emails.bulk_message_id = ss_bulk_message_attachment.bulk_message_id where  ss_bulk_message_attachment.bulk_message_id = '" . $req->bulk_message_id . "' AND delivery_status = 2 group by attachment_file ");
			if (count((array)$attachments) > 0) {
				$attachmentFiles = array();
				foreach ($attachments as $attach) {
					//LIVE SITE
					//$attachmentFiles[] = '/home3/bayyanor/public_html/ick/summercamp/message/attachments/'.$attach->attachment_file;
					if ($message->request_from == 'homework_module') {
						$attachmentFiles[] =  SITEURL . 'homework/attachments/' . $attach->attachment_file;
					} else {
						//LIVE QA SITE 
						$attachmentFiles[] = SITEURL . 'message/attachments/' . $attach->attachment_file;
						//DEV SITE
						//$attachmentFiles[] = '/webroot/b/a/bayyan005/icksaturdaydv.click2clock.com/www/message/attachments/'.$attach->attachment_file;
					}
					//QA SITE
					//$attachmentFiles[] = '/webroot/b/a/bayyan005/ick-saturday-aca.click2clock.com/www'.$attach->attachment_file;
					//DEV SITE
					//$attachmentFiles[] = '/webroot/b/a/bayyan005/icksaturdaydv.click2clock.com/www/message/attachments/'.$attach->attachment_file;
				}
				// echo "<pre>";
				// print_r($attachmentFiles);
				// die;
				$email_subject = $message->subject;
				$mail_service_array = array(
					'subject' => $email_subject,
					'message' => $new_msg_body,
					'request_from' => MAIL_SERVICE_KEY,
					'attachment_file_name' => $attach->attachment_file,
					'attachment_file' => $attachmentFiles,
					'to_email' => [$req->receiver_email],
					'cc_email' => SCHOOL_GEN_EMAIL,
					'bcc_email' => ''
				);
				//COMMENTED ON 15-JAN-2022
				// if(mailservice($mail_service_array)){
				// 	//bulk_email_with_attachment(EMAIL_GENERAL, $message->subject, $new_msg_body, $attachmentFiles);
				// 	$requests = $db->query("update ss_bulk_message_emails set delivery_status = 1, attempt_counter = attempt_counter + 1, 
				// 	email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
				// }else{
				// 	$requests = $db->query("update ss_bulk_message_emails set delivery_status = 0, attempt_counter = attempt_counter + 1, 
				// 	email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
				// } 
				//ADDED BY UROOJ ON 15-JAN-2022
				mailservice($mail_service_array);
				$requests = $db->query("update ss_bulk_message_emails set delivery_status = 1, attempt_counter = attempt_counter + 1, email_sent_on = '" . date('Y-m-d H:i:s') . "' where id = '" . $req->id . "'");
				echo "success";
			} else {
				$email_subject = $message->subject;
				$mail_service_array = array(
					'subject' => $email_subject,
					'message' => $new_msg_body,
					'request_from' => MAIL_SERVICE_KEY,
					'attachment_file_name' => $attach->attachment_file,
					'attachment_file' => $attachmentFiles,
					'to_email' => [$req->receiver_email],
					'cc_email' => SCHOOL_GEN_EMAIL,
					'bcc_email' => ''
				);


				//COMMENTED ON 15-JAN-2022
				// if(mailservice($mail_service_array)){
				// 	//send_my_mail(EMAIL_GENERAL, $message->subject, $new_msg_body, SCHOOL_GEN_EMAIL);
				// 	$requests = $db->query("update ss_bulk_message_emails set delivery_status = 1, attempt_counter = attempt_counter + 1, 
				// 	email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
				// }else{  
				// 	$requests = $db->query("update ss_bulk_message_emails set delivery_status = 0, attempt_counter = attempt_counter + 1, 
				// 	email_sent_on = '".date('Y-m-d H:i:s')."' where id = '".$req->id."'");
				// }
				//ADDED ON 15-JAN-2022
				mailservice($mail_service_array);
				$requests = $db->query("update ss_bulk_message_emails set delivery_status = 1, attempt_counter = attempt_counter + 1, email_sent_on = '" . date('Y-m-d H:i:s') . "' where id = '" . $req->id . "'");
				echo "success";
			}
		}
	}
}


////////////////GENERATE REPORT////////////////////////////////////
$message_rep = $db->get_row("select * from ss_bulk_message where session = '" . $current_session . "' AND is_report_gen = 0 and (scheduled_time is NULL or scheduled_time <= '" . date('Y-m-d H:i:s') . "')");
if ($message_rep) {
	$emails_all_rep = $db->get_results("select * from ss_bulk_message_emails where bulk_message_id = '" . $message_rep->id . "'");
	$emails_succ_rep = $db->get_results("select * from ss_bulk_message_emails where bulk_message_id = '" . $message_rep->id . "' and delivery_status = 1");
	//$emails_fail_rep = $db->get_results("select * from ss_bulk_message_emails where bulk_message_id = '".$message_rep->id."' and delivery_status = 0 and attempt_counter = 2");
	$emails_fail_rep = $db->get_results("select * from ss_bulk_message_emails where bulk_message_id = '" . $message_rep->id . "' and delivery_status = 0 and attempt_counter = " . $max_attempt_count);
	if (count((array)$emails_all_rep) == (count((array)$emails_succ_rep) + count((array)$emails_fail_rep)) && count((array)$emails_all_rep) > 0) {
		foreach ($emails_fail_rep as $email_rep) {
			$failed_emails = "<li>" . $email_rep->receiver_email . "</li>" . $failed_emails;
		}
		$failed_emails = "<ul>" . $failed_emails . "</ul>";
		$emailbody_rep = "<table style='border:0' cellspaceing='5' cellpadding='5'><tr><td>Assalamu-alaikum Webmaster,<br><br>Report of mass email with subject <strong><i>" . $message_rep->subject . "</i></strong> is given below:<br><br><strong>Total Emails Sent Successfully: </strong>" . count((array)$emails_succ_rep) . "<br><br><strong>Total Failed Emails: </strong>" . count((array)$emails_fail_rep) . "<br><br><strong>Failed Emails are: </strong>" . $failed_emails . "<br>Thank You<br>" . CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " Team</td></tr></table>";
		$email_rep_sub = CENTER_SHORTNAME . ' ' . SCHOOL_NAME . " - Mass Email Report";

		$mail_service_array = array(
			'subject' => $email_rep_sub,
			'message' => $emailbody_rep,
			'request_from' => MAIL_SERVICE_KEY,
			'attachment_file_name' => '',
			'attachment_file' => '',
			'to_email' => [SCHOOL_GEN_EMAIL],
			'cc_email' => '',
			'bcc_email' => ''
		);
		mailservice($mail_service_array);
		$db->query("update ss_bulk_message set is_report_gen = 1 where id = '" . $message_rep->id . "'");
	}
}
