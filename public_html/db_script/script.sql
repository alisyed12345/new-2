
-- Add 3 colomn  in txn summary  01-03-2022
ALTER TABLE `ss_txn_summary`  ADD `family_id` INT(10) NOT NULL  AFTER `phone`,  ADD `student_fees_item_id` VARCHAR(50) NOT NULL  AFTER `family_user_id`,  ADD `payment_status` TINYINT NOT NULL DEFAULT '0' COMMENT '0=failed,1=success'  AFTER `student_fees_item_id`;
-- Set Not NULL txn summary  01-03-2022
ALTER TABLE `ss_txn_summary` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `email` `email` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `phone` `phone` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
-- Add ss_student_fees_itemscolomn  in  ss_student_fees_items for unique schedule  07-03-2022
ALTER TABLE `ss_student_fees_items` ADD COLUMN `schedule_unique_id` VARCHAR(50) NOT NULL AFTER `id`; 


--Add Schedule_unique_id,family_id and remove student_item_id,user_id (unique Schedule-> single invoice )25-March-2022

ALTER TABLE `ss_invoice` ADD `schedule_unique_id` VARCHAR(100) NOT NULL AFTER `id`;
ALTER TABLE `ss_invoice` DROP `student_item_id`;
ALTER TABLE `ss_invoice` DROP FOREIGN KEY `fkuserid-userid`;
ALTER TABLE `ss_invoice` DROP `user_id`;
ALTER TABLE `ss_invoice` ADD `family_id` INT(10) NOT NULL AFTER `id`;

--Add wallet_amount colomn in ss_payment_sch_item_cron 28-March-2022
ALTER TABLE `ss_payment_sch_item_cron` ADD `wallet_amount` DOUBLE NOT NULL DEFAULT '0' AFTER `schedule_payment_date`;
--Change amount colomn to cc_amount colomn in ss_payment_sch_item_cron 28-March-2022
ALTER TABLE `ss_payment_sch_item_cron` CHANGE `amount` `cc_amount` DOUBLE NOT NULL DEFAULT '0';
--Add total_amount colomn in ss_payment_sch_item_cron 28-March-2022
ALTER TABLE `ss_payment_sch_item_cron` ADD `total_amount` DECIMAL NOT NULL AFTER `schedule_payment_date`;

----Change Varchar limit
ALTER TABLE `ss_payment_sch_item_cron` CHANGE `sch_item_ids` `sch_item_ids` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `ss_payment_sch_item_cron_backup` CHANGE `sch_item_ids` `sch_item_ids` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

--Change foreign key restriction for group ,class in class time table
ALTER TABLE `ss_classtime` DROP FOREIGN KEY `ssfk_classid`; ALTER TABLE `ss_classtime` ADD CONSTRAINT `ssfk_classid` FOREIGN KEY (`class_id`) REFERENCES `ss_classes`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `ss_classtime` DROP FOREIGN KEY `ssfk_createdby`; ALTER TABLE `ss_classtime` ADD CONSTRAINT `ssfk_createdby` FOREIGN KEY (`created_by_user_id`) REFERENCES `ss_user`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `ss_classtime` DROP FOREIGN KEY `ssfk_group_by_groupid`; ALTER TABLE `ss_classtime` ADD CONSTRAINT `ssfk_group_by_groupid` FOREIGN KEY (`group_id`) REFERENCES `ss_groups`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `ss_classtime` DROP FOREIGN KEY `ssfk_updatedby`; ALTER TABLE `ss_classtime` ADD CONSTRAINT `ssfk_updatedby` FOREIGN KEY (`updated_by_user_id`) REFERENCES `ss_user`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
