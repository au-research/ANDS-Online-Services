ALTER TABLE `dbs_registry`.`registry_object_relationships` ADD COLUMN `relation_type` VARCHAR(512) NULL  AFTER `origin` , ADD COLUMN `relation_description` VARCHAR(512) NULL  AFTER `relation_type` ;
ALTER TABLE `dbs_registry`.`registry_objects` DROP INDEX `key_UNIQUE` ;

CREATE  TABLE `dbs_registry`.`api_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `status` VARCHAR(32) NULL ,
  `ip_address` VARCHAR(45) NULL ,
  `api_key` VARCHAR(45) NULL ,
  `service` VARCHAR(45) NULL ,
  `params` VARCHAR(255) NULL ,
  `timestamp` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `dbs_registry`.`api_requests` ADD COLUMN `note` VARCHAR(255) NULL  AFTER `timestamp` ;

