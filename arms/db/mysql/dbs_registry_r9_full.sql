CREATE DATABASE  IF NOT EXISTS `dbs_registry` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `dbs_registry`;
-- MySQL dump 10.13  Distrib 5.5.24, for osx10.5 (i386)
--
-- Host: ands3.anu.edu.au    Database: dbs_registry
-- ------------------------------------------------------
-- Server version	5.5.25a

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `api_key` varchar(32) NOT NULL,
  `owner_token` varchar(45) DEFAULT NULL,
  `owner_email` varchar(45) DEFAULT NULL,
  `owner_organisation` varchar(45) DEFAULT NULL,
  `owner_purpose` text,
  PRIMARY KEY (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `background_tasks`
--

DROP TABLE IF EXISTS `background_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `background_tasks` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(45) NOT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  `completed` int(10) unsigned DEFAULT NULL,
  `prerequisite_task` mediumint(8) unsigned DEFAULT NULL,
  `log_message` text,
  `param_1` text,
  `param_2` text,
  `status` varchar(45) NOT NULL DEFAULT 'QUEUED',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_source_attributes`
--

DROP TABLE IF EXISTS `data_source_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_source_attributes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `attribute` varchar(32) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_data_sources` (`data_source_id`),
  CONSTRAINT `fk_attribute_data_source` FOREIGN KEY (`data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_source_logs`
--

DROP TABLE IF EXISTS `data_source_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_source_logs` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `date_modified` int(10) unsigned DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `log` text,
  `class` varchar(45) DEFAULT NULL,  
  PRIMARY KEY (`id`),
  KEY `fk_data_source` (`data_source_id`),
  CONSTRAINT `fk_log_data_source` FOREIGN KEY (`data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_sources`
--

DROP TABLE IF EXISTS `data_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_sources` (
  `data_source_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`data_source_id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  UNIQUE KEY `slug_UNIQUE` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `harvest_parameter`
--

DROP TABLE IF EXISTS `harvest_parameter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harvest_parameter` (
  `harvest_id` varchar(256) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `value` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `harvest_requests`
--

DROP TABLE IF EXISTS `harvest_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harvest_requests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `next_harvest` varchar(30) DEFAULT NULL,
  `harvest_frequency` varchar(45) DEFAULT NULL,
  `oai_set` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_harvest_request_data_source` (`data_source_id`),
  KEY `fk_harvest_data_source` (`data_source_id`),
  CONSTRAINT `fk_harvest_data_source` FOREIGN KEY (`data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutional_pages`
--

DROP TABLE IF EXISTS `institutional_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutional_pages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `authorative_data_source_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pk_group` (`group`),
  KEY `fk_data_source` (`authorative_data_source_id`),
  CONSTRAINT `fk_institutional_page_data_source` FOREIGN KEY (`authorative_data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `record_data`
--

DROP TABLE IF EXISTS `record_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_data` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `current` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `data` mediumblob,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `scheme` varchar(45) NOT NULL DEFAULT 'rif',
  PRIMARY KEY (`id`),
  KEY `ro_selector` (`registry_object_id`,`current`),
  KEY `fk_record_data_registry_object` (`registry_object_id`),
  KEY `registry_object_id_UNIQUE` (`registry_object_id`,`id`),
  CONSTRAINT `fk_record_data_registry_object` FOREIGN KEY (`registry_object_id`) REFERENCES `registry_objects` (`registry_object_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_attributes`
--

DROP TABLE IF EXISTS `registry_object_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_attributes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `attribute` varchar(32) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reg_attr` (`registry_object_id`,`attribute`),
  KEY `fk_attr_to_registry_object` (`registry_object_id`),
  CONSTRAINT `fk_attr_to_registry_object` FOREIGN KEY (`registry_object_id`) REFERENCES `registry_objects` (`registry_object_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_metadata`
--

DROP TABLE IF EXISTS `registry_object_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_metadata` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `attribute` varchar(32) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `idx_reg_metadata` (`registry_object_id`,`attribute`),
  KEY `fk_metadata_to_registry_object` (`registry_object_id`),
  CONSTRAINT `fk_metadata_to_registry_object` FOREIGN KEY (`registry_object_id`) REFERENCES `registry_objects` (`registry_object_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_relationships`
--

DROP TABLE IF EXISTS `registry_object_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_relationships` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `related_object_key` varchar(255) NOT NULL,
  `related_object_class` enum('collection','service','party','activity') DEFAULT NULL,
  `origin` varchar(32) NOT NULL DEFAULT 'EXPLICIT',
  PRIMARY KEY (`id`),
  KEY `idx_related_object_pair` (`registry_object_id`,`related_object_key`),
  KEY `fk_registry_object_relationships` (`registry_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_objects`
--

DROP TABLE IF EXISTS `registry_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_objects` (
  `registry_object_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `class` enum('collection','service','activity','party') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(45) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `record_owner` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`registry_object_id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  UNIQUE KEY `slug_UNIQUE` (`slug`),
  KEY `idx_ro_class` (`class`),
  KEY `fk_registry_object_data_source` (`data_source_id`),
  CONSTRAINT `fk_registry_object_data_source` FOREIGN KEY (`data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_statistics`
--

DROP TABLE IF EXISTS `search_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_statistics` (
  `search_term` varchar(255) NOT NULL,
  `occurence` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`search_term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spatial_extents`
--

DROP TABLE IF EXISTS `spatial_extents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spatial_extents` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `coordinates` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_spatial_extent_registry_object` (`registry_object_id`),
  CONSTRAINT `fk_spatial_extent_registry_object` FOREIGN KEY (`registry_object_id`) REFERENCES `registry_objects` (`registry_object_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_terms`
--

DROP TABLE IF EXISTS `tbl_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_terms` (
  `identifier` varchar(255) NOT NULL,
  `identifier_type` varchar(255) NOT NULL,
  `qualifier` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `description_type` varchar(255) DEFAULT NULL,
  `vocabulary_identifier` varchar(255) NOT NULL,
  `parent_term_identifier` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `relationtype` varchar(512) DEFAULT NULL,
  `vocabpath` varchar(1024) DEFAULT NULL,
  `lang` varchar(16) DEFAULT NULL,
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_tbl_terms_1_idx` (`vocabulary_identifier`),
  CONSTRAINT `fk_tbl_terms_1` FOREIGN KEY (`vocabulary_identifier`) REFERENCES `tbl_vocabularies` (`identifier`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_vocabularies`
--

DROP TABLE IF EXISTS `tbl_vocabularies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vocabularies` (
  `identifier` varchar(255) NOT NULL,
  `identifier_type` varchar(512) NOT NULL,
  `version` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `name_type` varchar(45) NOT NULL,
  `description` varchar(256) NOT NULL,
  `source` varchar(256) NOT NULL,
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier_UNIQUE` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `url_mappings`
--

DROP TABLE IF EXISTS `url_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_mappings` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_UNIQUE` (`slug`),
  KEY `idx_url_to_registry_object` (`registry_object_id`),
  KEY `fk_url_map_to_registry_object` (`registry_object_id`),
  CONSTRAINT `fk_url_map_to_registry_object` FOREIGN KEY (`registry_object_id`) REFERENCES `registry_objects` (`registry_object_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vocab_metadata`
--

DROP TABLE IF EXISTS `vocab_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocab_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(512) DEFAULT NULL,
  `description` varchar(12000) DEFAULT NULL,
  `publisher` varchar(512) DEFAULT NULL,
  `contact_name` varchar(512) DEFAULT NULL,
  `contact_email` varchar(512) DEFAULT NULL,
  `contact_number` varchar(45) DEFAULT NULL,
  `website` varchar(512) DEFAULT NULL,
  `revision_cycle` varchar(45) DEFAULT NULL,
  `notes` varchar(3000) DEFAULT NULL,
  `language` varchar(512) DEFAULT NULL,
  `information_sources` varchar(512) DEFAULT NULL,
  `record_owner` varchar(512) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-11-03 12:55:54
