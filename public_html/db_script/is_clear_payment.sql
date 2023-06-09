ALTER TABLE `ick_saturday_academy`.`ss_payment_txns`   
  ADD COLUMN `is_clear_payment` TINYINT(2) NULL AFTER `payment_date`;

  ALTER TABLE `ick_saturday_academy`.`ss_payment_txns`   
  CHANGE `is_clear_payment` `is_clear_payment` TINYINT(2) DEFAULT 1 NULL;
