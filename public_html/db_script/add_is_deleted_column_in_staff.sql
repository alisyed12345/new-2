ALTER TABLE `ick_saturday_all_fill`.`ss_staff`   
  ADD COLUMN `is_deleted` TINYINT(4) DEFAULT 0 NULL AFTER `updated_on`;