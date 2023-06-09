ALTER TABLE `ss_family` ADD `is_paid_registration_fee` BOOLEAN NOT NULL DEFAULT FALSE AFTER `comments`;
UPDATE `ss_family` SET `is_paid_registration_fee` = 1