CREATE TABLE `ss_change_email_request` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `new_email` varchar(255) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=>pending 1=>approve 2=> Reject',
  `user_type` tinyint(1) DEFAULT NULL COMMENT '0->parent\r\n1->teacher\r\n2->admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

