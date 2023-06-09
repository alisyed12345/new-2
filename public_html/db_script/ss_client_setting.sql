ALTER TABLE `uk_school`.`ss_client_settings`   
  ADD COLUMN `country_id` INT(11) NULL AFTER `is_waiting`;
  ALTER TABLE `uk_school`.`ss_client_settings`  
  ADD CONSTRAINT `fk_up_country_id` FOREIGN KEY (`country_id`) REFERENCES `uk_school`.`ss_country`(`id`)