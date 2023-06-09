CREATE TABLE `ick_saturday_academy`.`ss_invoice_info`(
  `id` INT NOT NULL AUTO_INCREMENT,
  `payment_txn_id` INT,
  `invoice_id` INT,
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ss_payment_txn` FOREIGN KEY (`payment_txn_id`) REFERENCES `ick_saturday_academy`.`ss_payment_txns`(`id`),
  CONSTRAINT `fk_ss_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `ick_saturday_academy`.`ss_invoice`(`id`)
);