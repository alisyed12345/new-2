CREATE TABLE `ick_saturday`.`ss_cron_payment_testing`(  
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cron_date` DATE,
  `status` TINYINT(3) DEFAULT 1,
  `created_on` DATETIME,
  PRIMARY KEY (`id`)
);

ALTER TABLE `ick_saturday`.`ss_cron_payment_testing`   
  ADD COLUMN `updated_on` DATETIME NULL AFTER `created_on`;  