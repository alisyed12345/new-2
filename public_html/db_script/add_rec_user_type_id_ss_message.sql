ALTER TABLE `ss_message` ADD `rec_user_type_id` INT NULL AFTER `rec_user_id`;
ALTER TABLE `ss_message` ADD `created_user_type_id` INT NULL AFTER `session`;