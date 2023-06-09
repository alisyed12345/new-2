INSERT INTO ss_permissions (permission, permission_name, permission_group_id, public_access, STATUS) 
VALUES ('su_internal_msg_list', 'Internal Message list', '12', '1', '1'), 
       ('su_internal_msg_send', 'Internal Message Send', '12', '1', '1'), 
       ('su_internal_msg_reply', 'Internal Message Reply', '12', '1', '1');

CREATE TABLE `ss_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_id` varchar(100) DEFAULT NULL,
  `invoice_date` datetime NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `receipt_id` varchar(100) DEFAULT NULL,
  `receipt_date` datetime DEFAULT NULL,
  `is_due` tinyint(3) NOT NULL COMMENT '0=Due, 1=Paid, 2=Overdue',
  `invoice_file_path` varchar(200) NOT NULL,
  `receipt_file_path` varchar(200) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0=Active, 1=Delete',
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fkuserid-userid` (`user_id`),
  CONSTRAINT `fkuserid-userid` FOREIGN KEY (`user_id`) REFERENCES `ss_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

