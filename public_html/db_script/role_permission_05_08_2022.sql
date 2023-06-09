/*#Condition Not Set & permission Not Exists#

#1.student->family list->Login URL(su_family_login_url).

#2.Staff->staff list->Send Login Info(su_staff_send_login_info).

#2.Staff->staff list-> Login url(su_staff_login_url).

#3.group->Manage Classes-> online_classes/list_online_classes(su_online_classes_list,su_online_classes_create,su_online_classes_edit,su_online_classes_delete).

#4.group->Manage Classes-> subjects/list_all_subjects(su_subject_list,su_subject_create,su_subject_edit,su_subject_delete).

#5.PAYMENT->Family info-> Accounting(su_family_accounting).

#6.PAYMENT->Family info-> Accounting->Payment Transaction(su_family_payment_transation).

#7.PAYMENT->Family info-> Accounting-> &  SEND/DOWNLOAD(su_family_invoice_send/su_family_invoice_download).

#8.Report
	1.Enrollment Report(su_report_enrollment)
	2.Admission Request (Pending) Report(su_report_admission_request)
	3.Discount Report(su_report_discount)
	4.Scheduled Payment Report(su_report_scheduled_payment)
	5.Registration Payment Report(su_report_registration_payment)

#9.Other (Section create)
	1.Teacher Resourse->(su_teacher_resource_list,su_teacher_resource_create,su_teacher_resource_edit,su_teacher_resource_delete).
	2.Announcements Manage->(su_announcements_list,su_announcements_create,su_announcements_edit,su_announcements_delete).
	3.Event Calendar->(su_event_calendar_list,su_event_calendar_create,su_event_calendar_edit,su_event_calendar_delete)
	*/
	
--Family Login URL
INSERT INTO `ss_permissions` (`id`,`permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL,'su_family_login_url', 'Family Login URL', '4', '1', '1');

--Staff ->Send Login Information
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_staff_send_login_info', 'Staff Send Login Information', '5', '1', '1');

--Staff Login URL
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_staff_login_url', 'Staff Login URL', '5', '1', '1');



--ONLINE CLASS
INSERT INTO `ss_permission_groups` (`id`, `permission_group`) VALUES (NULL, 'Online Classes Section');

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_online_classes_list', 'Online Class List', '19', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_online_classes_create', 'Online Class Create', '19', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_online_classes_edit', 'Online Class Edit', '19', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_online_classes_delete', 'Online Class Delete', '19', '1', '1');

--Payment Accounting

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_family_accounting', 'Payment Family Accounting', '11', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_family_payment_transation', 'Payment Family Transaction', '11', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_family_invoice_send', 'Payment Family Invoice/Receipt Send', '11', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_family_invoice_download', 'Payment Family Invoice/Receipt Download', '11', '1', '1');

--Report

INSERT INTO `ss_permission_groups` (`id`, `permission_group`) VALUES (NULL, 'Report Section');

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_report_enrollment', 'Enrollment Report', '20', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_report_admission_request', 'Admission Request Report', '20', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_report_discount', 'Discount Report', '20', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_report_scheduled_payment', 'Scheduled Payment Report', '20', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_report_registration_payment', 'Registration Payment Report', '20', '1', '1');

--Teacher Resourse Section
INSERT INTO `ss_permission_groups` (`id`, `permission_group`) VALUES (NULL, 'Teacher Resourse Section');

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_teacher_resource_list', 'Teacher Resource List', '21', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_teacher_resource_create', 'Teacher Resource Create', '21', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_teacher_resource_edit', 'Teacher Resource Edit', '21', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_teacher_resource_delete', 'Teacher Resource Delete', '21', '1', '1');

--Announcements Section
INSERT INTO `ss_permission_groups` (`id`, `permission_group`) VALUES (NULL, 'Announcements Section');

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_announcements_list', 'Announcements List', '22', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_announcements_create', 'Announcements Create', '22', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_announcements_edit', 'Announcements Edit', '22', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_announcements_delete', 'Announcements Delete', '22', '1', '1');


--Event Calendar Section
INSERT INTO `ss_permission_groups` (`id`, `permission_group`) VALUES (NULL, 'Event Calendar Section');

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_event_calendar_list', 'Event Calendar List', '23', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_event_calendar_create', 'Event Calendar Create', '23', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_event_calendar_edit', 'Event Calendar Edit', '23', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_event_calendar_delete', 'Event Calendar Delete', '23', '1', '1');

--clannge class section to Class Time Section
UPDATE `ss_permission_groups` SET `permission_group` = 'Classes Time Section' WHERE `ss_permission_groups`.`id` = 8;

--Class Section/Subject
INSERT INTO `ss_permission_groups` (`id`, `permission_group`) VALUES (NULL, 'Class Section');

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_class_list', 'Class List', '24', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_class_create', 'Class Create', '24', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_class_edit', 'Class Edit', '24', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_class_delete', 'Class Delete', '24', '1', '1');

--after 9 aug 2022--

INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_staff_pending_list', 'Staff Pending list', '5', '1', '1');

UPDATE `ss_permission_groups` SET `permission_group` = 'Attendance Section' WHERE `ss_permission_groups`.`id` = 2;
UPDATE `ss_permissions` SET `permission_name` = 'Todays Attendance' WHERE `ss_permissions`.`id` = 30;
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_group_wise_attendence_list', 'Group Wise Attendance', '2', '1', '1');

UPDATE `ss_permissions` SET `permission_group_id` = '3' WHERE `ss_permissions`.`id` = 37;

UPDATE `ss_permissions` SET `permission_name` = 'Student Group Assign' WHERE `ss_permissions`.`id` = 37;
UPDATE `ss_permissions` SET `status` = '2' WHERE `ss_permissions`.`id` = 65;
UPDATE `ss_permissions` SET `status` = '2' WHERE `ss_permissions`.`id` = 76;
UPDATE `ss_permissions` SET `permission_name` = 'Family Schedule Payment' WHERE `ss_permissions`.`id` = 90;
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_payment_approval_list', 'Payment Approval List', '11', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_payment_recurring_history', 'Payment Recurring History', '11', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_family_communicate', 'Family Info (Communicate)', '11', '1', '1');
UPDATE `ss_permissions` SET `permission_group_id` = '6' WHERE `ss_permissions`.`id` = 115;
UPDATE `ss_permissions` SET `permission_name` = 'Message Template Create' WHERE `ss_permissions`.`id` = 94; UPDATE `ss_permissions` SET `permission_name` = 'Email Template Create' WHERE `ss_permissions`.`id` = 98;
UPDATE `ss_permissions` SET `permission_name` = 'Communicate Mass Email List' WHERE `ss_permissions`.`id` = 48;
UPDATE `ss_permissions` SET `permission_name` = 'Communicate Mass Eamil Send ' WHERE `ss_permissions`.`id` = 47;
UPDATE `ss_permissions` SET `permission_name` = 'Communicate Mass Email View' WHERE `ss_permissions`.`id` = 49;
UPDATE `ss_permissions` SET `permission` = '`ss_permissions`' WHERE `ss_permissions`.`id` = 50;
UPDATE `ss_permissions` SET `permission_name` = '`Communicate Sent Text List`' WHERE `ss_permissions`.`id` = 50;
UPDATE `ss_permissions` SET `permission` = 'su_communicate_send_text_view', `permission_name` = 'Communicate Send Text View' WHERE `ss_permissions`.`id` = 52;
UPDATE `ss_permissions` SET `status` = '2' WHERE `ss_permissions`.`id` = 53; UPDATE `ss_permissions` SET `status` = '2' WHERE `ss_permissions`.`id` = 54;
UPDATE `ss_permissions` SET `status` = '2' WHERE `ss_permissions`.`id` = 29;
UPDATE `ss_permissions` SET `status` = '2' WHERE `ss_permissions`.`id` = 25;
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_communicate_initiate', 'Communicate Mass Email Initiate', '12', '1', '1');
INSERT INTO `ss_permissions` (`id`, `permission`, `permission_name`, `permission_group_id`, `public_access`, `status`) VALUES (NULL, 'su_communicate_delete', 'Communicate Mass Email Delete', '12', '1', '1');

ALTER TABLE `ss_usertype` ADD `is_default` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'for staff role' AFTER `role_id`;
UPDATE `ss_usertype` SET `is_default` = '1' WHERE `ss_usertype`.`id` = 1;
UPDATE `ss_usertype` SET `is_default` = '1' WHERE `ss_usertype`.`id` = 1;
UPDATE `ss_usertype` SET `is_default` = '1' WHERE `ss_usertype`.`id` = 9;


