ALTER TABLE `dbs_registry`.`registry_object_relationships` ADD COLUMN `relation_type` VARCHAR(512) NULL  AFTER `origin` , ADD COLUMN `relation_description` VARCHAR(512) NULL  AFTER `relation_type` ;
