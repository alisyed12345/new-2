CREATE TABLE `ick_saturday_academy`.`ss_registration_fee_txns`(  
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `registration_id` INT(11),
  `family_id` INT(11),
  `transaction_id` VARCHAR(100),
  `amount` DECIMAL(18,2),
  `reg_payment_type` TINYINT(4),
  `created_at` DATETIME,
  `created_by_user_id` INT,
  PRIMARY KEY (`id`),
  CONSTRAINT `fn_ss_registration_fee_txns_to_ss_family` FOREIGN KEY (`family_id`) REFERENCES `ick_saturday_academy`.`ss_family`(`id`)
);


ALTER TABLE `ick_saturday_academy`.`ss_registration_fee_txns`   
  CHANGE `reg_payment_type` `reg_payment_type` TINYINT(4) NULL COMMENT 'internal=1, external=2';