CREATE TABLE `ss_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(100) NOT NULL,
  `abbreviation` varchar(5) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

insert  into `ss_country`(`id`,`country`,`abbreviation`,`is_active`) values 
(1,'United States','US',1),
(2,'United Kingdom','GB',1);