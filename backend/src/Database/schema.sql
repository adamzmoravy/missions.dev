CREATE DATABASE  IF NOT EXISTS `idea-village-sandbox` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `idea-village-sandbox`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `kf_accounts`
--

DROP TABLE IF EXISTS `kf_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `currency` varchar(45) DEFAULT NULL,
  `company_identification` varchar(100) DEFAULT NULL,
  `billing_emails` varchar(200) DEFAULT NULL,
  `data` json DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `spaces` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `account_id_UNIQUE` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_answers`
--

DROP TABLE IF EXISTS `kf_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` int(11) DEFAULT NULL,
  `object_type` varchar(200) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_cache`
--

DROP TABLE IF EXISTS `kf_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_cache` (
  `cache_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(400) NOT NULL,
  `stash` json DEFAULT NULL,
  `store_until` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`cache_id`),
  UNIQUE KEY `kf_cache_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_calendar_events`
--

DROP TABLE IF EXISTS `kf_calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_calendar_events` (
  `calendar_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `data` json DEFAULT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `calendars` json DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `name` varchar(400) DEFAULT NULL,
  `description` text,
  `location` varchar(600) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`calendar_event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_comments`
--

DROP TABLE IF EXISTS `kf_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_comments` (
  `comment_id` varchar(100) NOT NULL,
  `topic_id` varchar(100) DEFAULT NULL,
  `parent_id` varchar(100) DEFAULT NULL,
  `value` text,
  `active` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `posted_time` int(11) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `reactions` json DEFAULT NULL,
  `format` varchar(100) DEFAULT NULL,
  `value_json` json DEFAULT NULL,
  `submissions` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=801 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_email_queue`
--

DROP TABLE IF EXISTS `kf_email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_email_queue` (
  `email_queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `html_message` text,
  `recipients` json NOT NULL,
  `variables` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `finished_sending_at` datetime DEFAULT NULL,
  `sent_out` json DEFAULT NULL,
  `uuid` varchar(400) DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `from_name` varchar(400) DEFAULT NULL,
  `from_email` varchar(400) DEFAULT NULL,
  `subject` varchar(600) DEFAULT NULL,
  `started_sending_at` datetime DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `cc_recipients` json DEFAULT NULL,
  `bcc_recipients` json DEFAULT NULL,
  UNIQUE KEY `kf_email_queue_unique` (`email_queue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=457 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_events`
--

DROP TABLE IF EXISTS `kf_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(200) DEFAULT NULL,
  `data` json DEFAULT NULL,
  `occurred_on` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `actor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4048 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_experiments`
--

DROP TABLE IF EXISTS `kf_experiments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_experiments` (
  `experiment_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) DEFAULT NULL,
  `stage` int(11) DEFAULT NULL,
  `document` json DEFAULT NULL,
  `images` json DEFAULT NULL,
  `show_in` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `links` json DEFAULT NULL,
  `funding` json DEFAULT NULL,
  `disabled` tinyint(4) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `show_in_placement` json DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  PRIMARY KEY (`experiment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=471 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_groups`
--

DROP TABLE IF EXISTS `kf_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_happenings`
--

DROP TABLE IF EXISTS `kf_happenings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_happenings` (
  `happening_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `users_registered` json DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `schedule` json DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `applications` json DEFAULT NULL,
  PRIMARY KEY (`happening_id`),
  UNIQUE KEY `happening_id_UNIQUE` (`happening_id`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_images`
--

DROP TABLE IF EXISTS `kf_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_images` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `context` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`image_id`),
  UNIQUE KEY `image_id_UNIQUE` (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_lang_fields`
--

DROP TABLE IF EXISTS `kf_lang_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_lang_fields` (
  `lang_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_key` varchar(200) NOT NULL,
  `lang_code` varchar(2) NOT NULL,
  `object_id` int(200) NOT NULL,
  `object_type` varchar(200) NOT NULL,
  `content` mediumtext NOT NULL,
  `format` varchar(200) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`lang_field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1345 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_platform_config`
--

DROP TABLE IF EXISTS `kf_platform_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_platform_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `value` json DEFAULT NULL,
  `platform` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_platforms`
--

DROP TABLE IF EXISTS `kf_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_platforms` (
  `platform_id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_key` varchar(200) DEFAULT NULL,
  `uuid` varchar(45) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`platform_id`),
  UNIQUE KEY `platform_id_UNIQUE` (`platform_id`),
  UNIQUE KEY `platform_key_UNIQUE` (`platform_key`),
  KEY `platform_key` (`platform_key`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_profiles`
--

DROP TABLE IF EXISTS `kf_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_profiles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `document_number` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(200) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  `content` mediumtext,
  `images` json DEFAULT NULL,
  `links` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `document` json DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `meta` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_profiles_skills`
--

DROP TABLE IF EXISTS `kf_profiles_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_profiles_skills` (
  `profile_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`profile_skill_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5591 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_questions`
--

DROP TABLE IF EXISTS `kf_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` varchar(200) DEFAULT NULL,
  `object_type` varchar(200) DEFAULT NULL,
  `context` varchar(300) DEFAULT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'thingeyri',
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_rate_limited_events`
--

DROP TABLE IF EXISTS `kf_rate_limited_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_rate_limited_events` (
  `rate_limited_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(400) DEFAULT NULL,
  `set_time` int(11) DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL,
  `allowed_occurances` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'thingeyri',
  PRIMARY KEY (`rate_limited_event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_relationships`
--

DROP TABLE IF EXISTS `kf_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_relationships` (
  `relationship_id` int(11) NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) NOT NULL,
  `follower_table` varchar(100) NOT NULL,
  `leader_id` varchar(100) NOT NULL,
  `leader_table` varchar(100) NOT NULL,
  `relationship_type` varchar(200) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`relationship_id`),
  KEY `relationship_id` (`relationship_id`),
  KEY `follower_id` (`follower_id`),
  KEY `follower_table` (`follower_table`),
  KEY `leader_id` (`leader_id`),
  KEY `leader_table` (`leader_table`),
  KEY `relationship_type` (`relationship_type`(191))
) ENGINE=InnoDB AUTO_INCREMENT=5186 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_resources`
--

DROP TABLE IF EXISTS `kf_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_resources` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `price` varchar(255) DEFAULT NULL,
  `currency` varchar(45) DEFAULT NULL,
  `language` json DEFAULT NULL,
  `images` json DEFAULT NULL,
  `data` json DEFAULT NULL,
  PRIMARY KEY (`resource_id`),
  UNIQUE KEY `resource_id_UNIQUE` (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_resources_bookings`
--

DROP TABLE IF EXISTS `kf_resources_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_resources_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `platform` varchar(45) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `accounting_id` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_id_UNIQUE` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_roles`
--

DROP TABLE IF EXISTS `kf_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(200) NOT NULL,
  `description` mediumtext NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'thingeyri',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_settings`
--

DROP TABLE IF EXISTS `kf_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(200) NOT NULL,
  `value` varchar(200) NOT NULL,
  `platform` varchar(200) DEFAULT 'thingeyri',
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_skill_categories`
--

DROP TABLE IF EXISTS `kf_skill_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_skill_categories` (
  `skill_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(300) NOT NULL,
  `platform` varchar(200) DEFAULT 'thingeyri',
  PRIMARY KEY (`skill_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_skills`
--

DROP TABLE IF EXISTS `kf_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_skills` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `skill_category_id` int(11) DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`skill_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_spaces`
--

DROP TABLE IF EXISTS `kf_spaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_spaces` (
  `space_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) DEFAULT NULL,
  `custom_language_fields` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `disabled` tinyint(4) DEFAULT NULL,
  `key` varchar(200) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`space_id`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_stats`
--

DROP TABLE IF EXISTS `kf_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_stats` (
  `stat_id` int(11) NOT NULL AUTO_INCREMENT,
  `ping` json DEFAULT NULL,
  `day` date DEFAULT NULL,
  `platform` varchar(200) NOT NULL,
  PRIMARY KEY (`stat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_subscriptions`
--

DROP TABLE IF EXISTS `kf_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `units` double DEFAULT NULL,
  `unit_price` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`subscription_id`),
  UNIQUE KEY `subscription_id_UNIQUE` (`subscription_id`)
) ENGINE=InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_survey_results`
--

DROP TABLE IF EXISTS `kf_survey_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_survey_results` (
  `survey_results_id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `questions` json DEFAULT NULL,
  `survery_name` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`survey_results_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_tags`
--

DROP TABLE IF EXISTS `kf_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `label_language` json DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'thingeyri',
  `type` varchar(200) DEFAULT 'experiment',
  `types` json DEFAULT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_tags_to_entities`
--

DROP TABLE IF EXISTS `kf_tags_to_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_tags_to_entities` (
  `tags_to_entities_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) DEFAULT NULL,
  `tag_type` varchar(200) DEFAULT NULL,
  `entity_type` varchar(200) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`tags_to_entities_id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_team_members`
--

DROP TABLE IF EXISTS `kf_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_team_members` (
  `team_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) DEFAULT NULL,
  `object_type` varchar(45) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `roles` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(200) DEFAULT 'sandbox',
  PRIMARY KEY (`team_member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=550 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_translations`
--

DROP TABLE IF EXISTS `kf_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_translations` (
  `translation_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(400) DEFAULT NULL,
  `value` varchar(600) DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  `language` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`translation_id`),
  UNIQUE KEY `translation_id_UNIQUE` (`translation_id`),
  KEY `key` (`key`,`platform`),
  KEY `key-lang-platoform` (`language`,`platform`,`key`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_users`
--

DROP TABLE IF EXISTS `kf_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kf_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL,
  `ip_address` char(16) NOT NULL,
  `username` varchar(40) NOT NULL,
  `password` varchar(255) NOT NULL,
  `hash_type` varchar(35) NOT NULL DEFAULT 'ion',
  `salt` varchar(40) DEFAULT NULL,
  `email` varchar(254) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(11) unsigned NOT NULL,
  `last_login` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `permissions` mediumtext,
  `settings` json DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-02 18:46:26
