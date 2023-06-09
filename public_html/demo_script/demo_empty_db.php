<?php

include_once "../includes/config.php";

$db->query('SET foreign_key_checks = 0;');
$db->query('TRUNCATE TABLE erp_admissionrequest');
$db->query('TRUNCATE TABLE erp_admreq_child');
$db->query('TRUNCATE TABLE erp_admreq_payment');
$db->query('TRUNCATE TABLE erp_attendance');
$db->query('TRUNCATE TABLE erp_bulk_mail_login_info');
$db->query('TRUNCATE TABLE erp_bulk_message');
$db->query('TRUNCATE TABLE erp_bulk_message_emails');
$db->query('TRUNCATE TABLE erp_bulk_sms');
$db->query('TRUNCATE TABLE erp_bulk_sms_mobile');
$db->query('TRUNCATE TABLE erp_bulk_sms_reply');
$db->query('TRUNCATE TABLE erp_admreq_payment');
$db->query('TRUNCATE TABLE erp_family');
$db->query('TRUNCATE TABLE erp_family_payment_info');
$db->query('TRUNCATE TABLE erp_feedback');
$db->query('TRUNCATE TABLE erp_fees');
$db->query('TRUNCATE TABLE erp_fees_thirdparty_status');
$db->query('TRUNCATE TABLE erp_group_day_time');
$db->query('TRUNCATE TABLE erp_holiday_groups');
$db->query('TRUNCATE TABLE erp_holidays');
$db->query('TRUNCATE TABLE erp_homework');
$db->query('TRUNCATE TABLE erp_homework_sms');
$db->query('TRUNCATE TABLE erp_homework_sms_mobile');
$db->query('TRUNCATE TABLE erp_language');
$db->query('TRUNCATE TABLE erp_loginhistory');
$db->query('TRUNCATE TABLE erp_message');
$db->query('TRUNCATE TABLE erp_onlinepaymentinfo');
$db->query('TRUNCATE TABLE erp_parents_payment_info');
$db->query('TRUNCATE TABLE erp_payment_schedule');
$db->query('TRUNCATE TABLE erp_payment_txns');
$db->query('TRUNCATE TABLE erp_payment_schedule_item');
$db->query('TRUNCATE TABLE erp_paymentcredentials');
$db->query('TRUNCATE TABLE erp_paymentcredentials_backup');
$db->query('TRUNCATE TABLE erp_staff');
$db->query('TRUNCATE TABLE erp_staffgroupmap');
$db->query('TRUNCATE TABLE erp_student');
$db->query('TRUNCATE TABLE erp_student_hold');
$db->query('TRUNCATE TABLE erp_studentfeestypemap');
$db->query('TRUNCATE TABLE erp_studentgroupmap');
$db->query('TRUNCATE TABLE erp_user');
$db->query('TRUNCATE TABLE erp_user_extra_permissions');
$db->query('TRUNCATE TABLE erp_user_role_map');

$db->query("insert into erp_user set username='admin@demo.com', password=md5('123456'), email='admin@demo.com', user_type_id=7, is_email_verified=1, is_locked=0, is_active=1, is_deleted=0, school_session_id=2,created_on=NOW(), updated_on=NOW()");

$db->query("insert into erp_user set username='principal@demo.com', password=md5('123456'), email='principal@demo.com', user_type_id=1, is_email_verified=1, is_locked=0, is_active=1, is_deleted=0, school_session_id=2,created_on=NOW(), updated_on=NOW()");

echo "<h1>DONE</h1>"

?>