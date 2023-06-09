ALTER TABLE `ick_saturday_academy`.`ss_invoice`   
  CHANGE `is_due` `is_due` TINYINT(3) NOT NULL COMMENT '0=Due, 1=Paid, 2=Overdue, 3=failed';