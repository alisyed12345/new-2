ALTER TABLE `ick_saturday_academy`.`ss_payment_sch_item_cron`   
  ADD COLUMN `payment_gateway` VARCHAR(20) NULL AFTER `payment_response_code`;