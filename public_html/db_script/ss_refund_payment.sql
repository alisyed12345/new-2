CREATE TABLE `ick_saturday_academy`.`ss_refund_payment_txns`(  
  `id` INT NOT NULL AUTO_INCREMENT,
  `family_id` INT NOT NULL,
  `payment_txn_id` INT NOT NULL,
  `refund_txn_id` INT NOT NULL,
  `refund_amount` DECIMAL(18,2),
  `created_on` DATETIME,
  PRIMARY KEY (`id`)
);
