<?php
include_once "../includes/config.php";

require_once '../includes/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();



if ($_POST['action'] == 'general_setting' || $_POST['action'] == 'register_setting') {

    $school_name = trim($db->escape($_POST['school_name']));
    $center_short_name = trim($db->escape($_POST['center_short_name']));
    $school_opening_date = trim($db->escape($_POST['school_opening_date_submit']));
    $school_closing_date = trim($db->escape($_POST['school_closing_date_submit']));
    $new_registration_start_date = trim($db->escape($_POST['new_registration_start_date_submit']));
    $new_registration_end_date = trim($db->escape($_POST['new_registration_end_date_submit']));
    $school_opening_day = $_POST['school_opening_days'];
    $is_new_registration_free = trim($db->escape($_POST['is_new_registration_free']));
    $new_registration_open = trim($db->escape($_POST['new_registration_open']));
    $one_student_one_lavel = trim($db->escape($_POST['one_student_one_level']));
    $country = trim($db->escape($_POST['country_id']));
    $organization_name = trim($db->escape($_POST['organization_name']));

    $fees_monthly = trim($db->escape($_POST['monthly_fee']));


    //$check_school_exist = $db->get_row("select * from ss_client_settings where school_name='".$school_name."' ");
    $check_status = $db->get_row("select status from ss_client_settings where status=1 ");
    // if(empty($check_school_exist)){
    if ($_POST['action'] == 'general_setting') {
        if ($check_status->status != 1) {

            $target_dir_url = "../settings/uploads/";
            $target_dir = "settings/uploads/";
            $extensions_arr = array("jpg", "jpeg", "png");

            //--------------------School logo--------------------//
            if (!empty($_FILES['school_logo']['name'])) {
                $school_logo = str_replace(' ', '-', $_FILES['school_logo']['name']);
                $schoollogo = uniqid() . $school_logo;
                $imgData = $target_dir . $schoollogo;
                $target_file = $target_dir_url . basename($schoollogo);
                $imageFileType = strtolower(pathinfo($school_logo, PATHINFO_EXTENSION));
                if (in_array($imageFileType, $extensions_arr)) {
                    move_uploaded_file($_FILES['school_logo']['tmp_name'], $target_file);
                }
            } else {
                $imgData = '';
            }

            //--------------------Header logo--------------------//
            if (!empty($_FILES['header_logo']['name'])) {
                $header_logo = str_replace(' ', '-', $_FILES['header_logo']['name']);
                $headerlogo = uniqid() . $header_logo;
                $target_file1 =  $target_dir_url . basename($headerlogo);
                $headerlogo = $target_dir . $headerlogo;
                $imagesdata = strtolower(pathinfo($header_logo, PATHINFO_EXTENSION));

                if (in_array($imagesdata, $extensions_arr)) {
                    move_uploaded_file($_FILES['header_logo']['tmp_name'], $target_file1);
                }
            } else {
                $headerlogo = '';
            }
 
            //$db->query("update ss_client_settings set status = 0 where client_user_id = '".$_SESSION['icksumm_uat_login_userid']."'");
            $reg_setting = $db->query("insert into ss_client_settings set client_user_id = '1', school_logo ='" . $imgData . "', school_header_logo ='" . $headerlogo . "', 
                    school_name='" . $school_name . "', contact_organization_name = '".$organization_name."', school_opening_date='" . $school_opening_date . "', school_closing_date='" . $school_closing_date . "', 
                    school_opening_days='" . serialize($school_opening_day) . "',  created_on='" . date('Y-m-d H:i') . "', center_short_name='" . $center_short_name . "',
                    fees_monthly='" . $fees_monthly . "', one_student_one_lavel='" . $one_student_one_lavel . "', country_id = '".$country."', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'");

            $reg_setting_id = $db->insert_id;

            if ($reg_setting_id > 0) {

                $contact_info_setting = $db->query("insert into ss_client_settings set contact_admin_email='" . $_POST['admin_email'] . "', contact_organisation_email='" . $_POST['organization_email'] . "', 
                     contact_phone='" . $_POST['phone_no'] . "', contact_address='" . $_POST['address'] . "', contact_city='" . $_POST['city'] . "', contact_state_id='" . $_POST['state_id'] . "',
                     contact_zipcode='" . $_POST['zip_code'] . "', created_on='" . date('Y-m-d H:i') . "', created_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "'");

                echo json_encode(array('msg' => "<p class='text-success'>Record Added Successfully</p>", 'code' => 1));
                exit;

                echo json_encode(array('msg' => "<p class='text-danger'>Record Not Added</p>", 'code' => 0));
                exit;
            }
        } else {

            if (isset($school_name)) {
                $target_dir_url = "../settings/uploads/";
                $target_dir = "settings/uploads/";
                $extensions_arr = array("jpg", "jpeg", "png");

                //--------------------School logo--------------------//
                if (!empty($_FILES['school_logo']['name'])) {
                    $school_logo = str_replace(' ', '-', $_FILES['school_logo']['name']);
                    $schoollogo = uniqid() . $school_logo;
                    $imgData = $target_dir . $schoollogo;
                    $target_file = $target_dir_url . basename($schoollogo);
                    $imageFileType = strtolower(pathinfo($school_logo, PATHINFO_EXTENSION));
                    if (in_array($imageFileType, $extensions_arr)) {
                        move_uploaded_file($_FILES['school_logo']['tmp_name'], $target_file);
                    }
                } else {
                    $imgData = $_POST['email_logo'];
                }

                //--------------------Header logo--------------------//
                if (!empty($_FILES['header_logo']['name'])) {
                    $header_logo = str_replace(' ', '-', $_FILES['header_logo']['name']);
                    $headerlogo = uniqid() . $header_logo;
                    $target_file1 =  $target_dir_url . basename($headerlogo);
                    $headerlogo = $target_dir . $headerlogo;
                    $imagesdata = strtolower(pathinfo($header_logo, PATHINFO_EXTENSION));

                    if (in_array($imagesdata, $extensions_arr)) {
                        move_uploaded_file($_FILES['header_logo']['tmp_name'], $target_file1);
                    }
                } else {
                    $headerlogo = $_POST['head_logo'];
                }

                $res = $db->query("update ss_client_settings set school_logo ='" . $imgData . "', school_header_logo ='" . $headerlogo . "', school_name='" . $school_name . "', contact_organization_name = '".$organization_name."',
                    school_opening_date='" . $school_opening_date . "', school_closing_date='" . $school_closing_date . "', center_short_name='" . $center_short_name . "',
                    school_opening_days='" . serialize($school_opening_day) . "', fees_monthly='" . $fees_monthly . "', one_student_one_lavel='" . $one_student_one_lavel . "',contact_admin_email='" . $_POST['admin_email'] . "', contact_organisation_email='" . $_POST['organization_email'] . "', 
                    contact_phone='" . $_POST['phone_no'] . "', contact_address='" . $_POST['address'] . "', country_id = '".$country."', contact_city='" . $_POST['city'] . "', contact_state_id='" . $_POST['state_id'] . "',
                    contact_zipcode='" . $_POST['zip_code'] . "', updated_on='" . date('Y-m-d H:i') . "', updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' where status = 1");
                if ($res) {

                    echo json_encode(array('msg' => "<p class='text-success'>Record Updated Successfully</p>", 'code' => 1));
                    exit;
                } else {
                    echo json_encode(array('msg' => "<p class='text-danger'>Record Not Updated Successfully</p>", 'code' => 0));
                    exit;
                }
            }
        }
    }

    if ($_POST['action'] == 'register_setting') {

        $term_and_condition = trim($db->escape($_POST['term_and_condition']));

        // $term_and_condition1 = trim($term_and_condition, "<p>");
        // $term_and_condition1 = trim($term_and_condition1, "</p>");
        // $term_and_condition1 = str_replace("&nbsp;", '', $term_and_condition1);
        // $term_and_condition1 = str_replace("br", '', $term_and_condition1);

        if (!empty(trim($term_and_condition))) {

            $new_registration_session = trim($db->escape($_POST['new_reg_session_id']));
            if (empty($_POST['new_registration_fees'])) {
                $new_registration_fee = "0";
            } else {
                $new_registration_fee = trim($db->escape($_POST['new_registration_fees']));
            }

            if(!strip_tags($_POST["term_and_condition"])){
                 echo json_encode(array('msg' => "<p class='text-danger'>Terms and Conditions cannot be empty</p>", 'code' => 0));
                exit;
            }


            $student_html = '<!DOCTYPE html><html lang="en" moznomarginboxes mozdisallowselectionprint><head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width initial-scale=1.0"> <style type="text/css" media="all"> body{font-family: "Helvetica", "Arial", sans-serif; font-size: 14px; color: #333;}.page-break{page-break-after: always;}.color1{color:#0e772e;}.color2{color:#863d11;}</style></head><body> <table style="width:100%; cellpadding:0px; border:0;" cellpadding="0"> <tr> <td style="width:60%;text-align: left;vertical-align:top;padding-top:20px"> <div style="font-size: 20px;" class="color2"> ICK Saturday Academy </div></td><td style="width:40%; text-align:right;padding-top:10px"> </td></tr><tr> <td colspan="2" style="text-align: center;"> <div style="font-size: 18px;margin-top:30px; text-align:center;"><u>Saturday Academy - Registration Form Terms & Conditions </u></div></td></tr></table> <div class="row"> <div class="col-md-12">' .$_POST["term_and_condition"]. '</div></div></body></html>';
            
            //$staff_html = '<!DOCTYPE html><html lang="en" moznomarginboxes mozdisallowselectionprint><head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width initial-scale=1.0"> <style type="text/css" media="all"> body{font-family: "Helvetica", "Arial", sans-serif; font-size: 14px; color: #333;}.page-break{page-break-after: always;}.color1{color:#0e772e;}.color2{color:#863d11;}</style></head><body> <table style="width:100%; cellpadding:0px; border:0;" cellpadding="0"> <tr> <td style="width:60%;text-align: left;vertical-align:top;padding-top:20px"> <div style="font-size: 20px;" class="color2"> ICK Saturday Academy </div></td><td style="width:40%; text-align:right;padding-top:10px"></td></tr><tr> <td colspan="2" style="text-align: center;"> <div style="font-size: 18px;margin-top:30px; text-align:center;"><u>Saturday Academy - Registration Form Terms & Conditions </u></div></td></tr></table> <div class="row"> <div class="col-md-12"> <h3><strong>Terms And Conditions</strong> </h3> <p> “In the name of Allah, the most beneficial and merciful” </p><p> Narrated Uthman: The Prophet said, "The best among you (Muslims) are those who learn the Quran and teach it." </p><p> Narrated by Jabir ibn Abdullah: The Prophet (saws) said, “On the Day of Judgment the dearest and closest to me, as regards my company, will be those persons who will bear the best moral character.” </p><p> <strong>Attendance:</strong> Staff Member shall attend every class day except when emergencies occur in which case the member must communicate with the principal no later than 1 hour before school start time. </p><p> <strong>COVID 19 Protocol:</strong> Staff must wear masks at all times, no exceptions. </p><p> <strong>Consensus:</strong> All changes to school policies, curriculum or related matters must be discussed with the principal and the subject leads and must go through consensus of the above people before being implemented. </p><p> <strong>Communication:</strong> Staff shall communicate openly via email, txt or Whatapp with other teachers or the school staff in order to facilitate decision making or to resolve issues. All teachers are required to respond to requests or questions from other colleagues in a timely manner. </p><p> <strong>Punctuality:</strong> Staff shall arrive at least 15 mins before their class/duty start time. In case of teaching online, please be prepared for your class at least 10 mins before its scheduled time. </p><p> <strong>Privacy/Confidentiality:</strong> All matters discussed regarding the school or the Islamic Center of Kansas are confidential and must be kept private between the members who are involved. Any public release or discussion of such matters requires permission from the principal who will engage the ICK management to address any such issues as necessary. </p><p> <strong>Conduct:</strong> Staff members are held at a higher standard than most of the community members. As such, they will abide by all ICK policies as well as policies set forth by the ICK Academy. </p><p> <strong>Dress Code:</strong> All staff members must abide by the Islamic dress code when at ICK regardless of the Academy hours. Male staff should wear pants or shalwars, etc. i.e. no shorts. Female teachers should wear an abayah or jilbab with a head scarf. Please discuss any specific questions with the administrative staff. </p><p> <strong>Commitment:</strong> Staff members who agree to teach or volunteer for ICK Academy commit their time for all school days for the current term. All staff members must be available for the entire year. </p><p> <strong>Safety:</strong> Staff members shall refrain from touching or harming the students. </p><p> <strong>Preparation:</strong> Each staff member must prepare for the class ahead of time. </p><p> <strong>Compliance:</strong> All staff members must agree and comply with the Code of Conduct, ICK policies, Islamic laws and procedures and the Local Laws. </p><p> <strong> Use of Photo/Image or Video at the Academy</strong><br>We take pictures or videos of students during the activities of a program for future illustrative purposes and share them with the parents only. </p><p> I hereby grant Islamic Center of Kansas Inc (ICK) (the organization) permission to use mine, or my family members (spouse or children whom I am a legal guardian of) likeness in photographs, video recordings or electronic images in any and all of its publications, including website entries, without payment or any other considerations. I understand and agree that these materials will become the property of the organization and will not be returned. I hereby irrevocably authorize the organization to edit, alter, copy, exhibit, publish or distribute these images for purposes of publicizing the organizations programs or for any other lawful purpose. In addition, I waive the right to inspect or approve the finished product, including written or electronic copy, wherein my likeness appears. Additionally, I waive any right to royalties or other compensation arising or related to the use of my image. I hereby hold harmless and release and forever discharge the organization from all claims, demands, and causes of action which I, my heirs, representatives, executors, administrators, or any other persons acting on my behalf or on behalf of my estate have or may have by reason of this authorization. </p><p> <strong> ICK General Release of Liability</strong><br>As the parent/legal guardian of the minor(s) listed above, I hereby grant permission for the student(s) to participate in the activities of the Islamic Center of Kansas’ Saturday Academy, IQRA’A Quran Center, or Summer School programs. I assume full responsibility for any injuries or damages which may occur to these student(s), in, on, or about the premises of Islamic Center of Kansas, or arising out of its activities, whether occurring on the premises of the center or at any other location, and do hereby fully release, indemnify, discharge and hold harmless the Islamic Center of Kansas, its Trustees, and all associated with it, including teachers, administrators, and volunteers, from any and all claims, responsibilities, liabilities, legal actions or suits, damages or losses of any kind or description, both at law or in equity, arising out of, or in any way connected with, any of the above-mentioned acts and activities. </p></div></div></body></html>';

            $dompdf->loadHtml($student_html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $output = $dompdf->output();
            $fileName = "email_pdf/student-reg-term-condition-" . strtotime(date('h:i:s')) . ".pdf";
            $filepath = "../" . $fileName;
            //$filepath = "../email_pdf/staff-reg-term-condition.pdf";
            file_put_contents($filepath, $output);
            $wait = trim($_POST['is_waiting']);
            
            $email_setting = $db->query("update ss_client_settings set new_registration_start_date='" . $new_registration_start_date . "', new_registration_fees_form_head='" . $_POST['fees_type'] . "',new_registration_end_date='" . $new_registration_end_date . "', is_new_registration_free='" . $is_new_registration_free . "', new_registration_fees='" . $new_registration_fee . "',updated_by_user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "',  is_new_registration_open='" . $new_registration_open . "', is_waiting='".$wait."', registration_page_termsncond='" . $term_and_condition . "', new_registration_session='" . $new_registration_session . "', reg_form_term_cond_attach_url='" . $fileName . "', new_registration_email_cc='" . $_POST['sender_cc'] . "', new_registration_email_bcc='" . $_POST['sender_bcc'] . "', updated_on='" . date('Y-m-d H:i:s') . "'");
            
            if ($email_setting) {
                echo json_encode(array('msg' => "<p class='text-success'>Record Updated Successfully</p>", 'code' => 1));
                exit;
            } else {
                echo json_encode(array('msg' => "<p class='text-danger'>Record Not Updated.</p>", 'code' => 0));
                exit;
            }
        } else {
            echo json_encode(array('msg' => "<p class='text-danger'>Terms and Conditions cannot be empty</p>", 'code' => 0));
            exit;
        }
    }


    //}
    // else{

    //     $return_resp = array('code' => "0",'msg' => 'School name already exist in database.');
    //     CreateLog($_REQUEST, json_encode($return_resp));
    //     echo json_encode($return_resp);
    //     exit;

    // }

} elseif ($_POST['action'] == 'email_setting') {
    $res = $db->query("update ss_client_settings set new_registration_email_cc='" . $_POST['sender_cc'] . "', new_registration_email_bcc='" . $_POST['sender_bcc'] . "', updated_on='" . date('Y-m-d H:i') . "', updated_by_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' where status = 1 ");
    if ($res) {
        echo json_encode(array('code' => "1", 'emailmsg' => "<p class='text-success' style='margin-top:-3px'>Record added successfully</p>"));
        exit;
    } else {
        $return_resp = array('code' => "0", 'emailmsg' => '<p class="text-danger" style="margin-top:-3px">Record  Not added</p>');
        CreateLog($_REQUEST, json_encode($return_resp));
        echo json_encode($return_resp);
        exit;
    }
}elseif ($_POST['action'] == 'fetch_state') {
    $country_id = $_POST['country_id'];
	$get_state = $db->get_results("select * from ss_state where is_active = 1 and country_id = '".$country_id."'");
	$option = "";
	if (count($get_state)) {
		foreach ($get_state as $states) {
			$option .= "<option value = '" . $states->id . "'>" . $states->state . "</option>";
		}
	}
	echo $option;
	exit;
}

