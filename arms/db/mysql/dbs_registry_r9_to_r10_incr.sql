ALTER TABLE `dbs_registry`.`registry_object_relationships` ADD COLUMN `relation_type` VARCHAR(512) NULL  AFTER `origin` , ADD COLUMN `relation_description` VARCHAR(512) NULL  AFTER `relation_type` ;
ALTER TABLE `dbs_registry`.`registry_objects` DROP INDEX `key_UNIQUE` ;

ALTER TABLE `url_mappings` ADD COLUMN 
  `search_title` varchar(255) DEFAULT NULL AFTER `registry_object_id`;

-- drop the old api_keys table
drop table `api_keys`;

CREATE TABLE `api_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(32) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `api_key` varchar(45) DEFAULT NULL,
  `service` varchar(45) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `timestamp` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8;

CREATE TABLE `api_keys` (
  `api_key` varchar(32) NOT NULL,
  `owner_email` varchar(45) DEFAULT NULL,
  `owner_organisation` varchar(45) DEFAULT NULL,
  `owner_purpose` text,
  `created` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `deleted_registry_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `deleted` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `record_data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_data_source_id_idx` (`data_source_id`),
  CONSTRAINT `fk_data_source_id` FOREIGN KEY (`data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

ALTER TABLE `dbs_registry_migration_test`.`url_mappings` DROP FOREIGN KEY `fk_url_map_to_registry_object` ;
ALTER TABLE `dbs_registry_migration_test`.`url_mappings` CHANGE COLUMN `registry_object_id` `registry_object_id` MEDIUMINT(8) UNSIGNED NULL  ;



ALTER TABLE `dbs_registry_migration_test`.`registry_objects` 
ADD INDEX `key_index` USING HASH (`key` ASC) 
, ADD INDEX `key_class_index` USING HASH (`key` ASC, `class` ASC) ;
ALTER TABLE `dbs_registry_migration_test`.`url_mappings` 
ADD INDEX `slug_INDEX` USING HASH (`slug` ASC) ;
ALTER TABLE `dbs_registry_migration_test`.`registry_object_metadata` 
DROP INDEX `idx_reg_metadata` 
, ADD INDEX `idx_reg_metadata` USING HASH (`registry_object_id` ASC, `attribute` ASC) 
, DROP INDEX `fk_metadata_to_registry_object` ;
