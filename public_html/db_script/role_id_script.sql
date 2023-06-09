ALTER TABLE `ick_saturday_all_fill`.`ss_usertype`   
  DROP COLUMN `role_id`, 
  ADD COLUMN `role_id` INT(11) NULL AFTER `is_active`; 