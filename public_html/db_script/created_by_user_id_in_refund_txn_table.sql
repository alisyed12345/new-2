ALTER TABLE `ick_saturday_academy`.`ss_refund_payment_txns`   
  ADD COLUMN `created_by_user_id` INT(11) NOT NULL AFTER `refund_amount`;