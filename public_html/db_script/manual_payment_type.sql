ALTER TABLE `ick_saturday_academy`.`ss_payment_account_entries`   
  ADD COLUMN `is_force_payment` TINYINT(4) DEFAULT 0 NULL AFTER `is_manual`;
