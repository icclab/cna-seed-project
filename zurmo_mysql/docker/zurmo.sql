-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: db    Database: zurmo
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.14.04.1

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
-- Table structure for table `__role_children_cache`
--

DROP TABLE IF EXISTS `__role_children_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `__role_children_cache` (
  `permitable_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`permitable_id`,`role_id`),
  UNIQUE KEY `permitable_id` (`permitable_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `__role_children_cache`
--

LOCK TABLES `__role_children_cache` WRITE;
/*!40000 ALTER TABLE `__role_children_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `__role_children_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_group`
--

DROP TABLE IF EXISTS `_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permitable_id` int(11) unsigned DEFAULT NULL,
  `_group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_eman` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_group`
--

LOCK TABLES `_group` WRITE;
/*!40000 ALTER TABLE `_group` DISABLE KEYS */;
INSERT INTO `_group` VALUES (1,'Super Administrators',2,NULL),(2,'Everyone',3,NULL);
/*!40000 ALTER TABLE `_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_group__user`
--

DROP TABLE IF EXISTS `_group__user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_group__user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `_group_id` int(11) unsigned DEFAULT NULL,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_resu__di_puorg_` (`_group_id`,`_user_id`),
  KEY `di_puorg_` (`_group_id`),
  KEY `di_resu_` (`_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_group__user`
--

LOCK TABLES `_group__user` WRITE;
/*!40000 ALTER TABLE `_group__user` DISABLE KEYS */;
INSERT INTO `_group__user` VALUES (1,1,1),(2,1,2);
/*!40000 ALTER TABLE `_group__user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_right`
--

DROP TABLE IF EXISTS `_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_right` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modulename` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` tinyint(11) DEFAULT NULL,
  `permitable_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_right`
--

LOCK TABLES `_right` WRITE;
/*!40000 ALTER TABLE `_right` DISABLE KEYS */;
INSERT INTO `_right` VALUES (1,'UsersModule','Login Via Web',1,3),(2,'UsersModule','Login Via Mobile',1,3),(3,'UsersModule','Login Via Web API',1,3),(4,'AccountsModule','Access Accounts Tab',1,3),(5,'AccountsModule','Create Accounts',1,3),(6,'AccountsModule','Delete Accounts',1,3),(7,'CampaignsModule','Access Campaigns Tab',1,3),(8,'CampaignsModule','Create Campaigns',1,3),(9,'CampaignsModule','Delete Campaigns',1,3),(10,'ContactsModule','Access Contacts Tab',1,3),(11,'ContactsModule','Create Contacts',1,3),(12,'ContactsModule','Delete Contacts',1,3),(13,'ConversationsModule','Access Conversations Tab',1,3),(14,'ConversationsModule','Create Conversations',1,3),(15,'ConversationsModule','Delete Conversations',1,3),(16,'EmailMessagesModule','Access Emails Tab',1,3),(17,'EmailMessagesModule','Create Emails',1,3),(18,'EmailMessagesModule','Delete Emails',1,3),(19,'EmailTemplatesModule','Access Email Templates',1,3),(20,'EmailTemplatesModule','Create Email Templates',1,3),(21,'EmailTemplatesModule','Delete Email Templates',1,3),(22,'LeadsModule','Access Leads Tab',1,3),(23,'LeadsModule','Create Leads',1,3),(24,'LeadsModule','Delete Leads',1,3),(25,'LeadsModule','Convert Leads',1,3),(26,'OpportunitiesModule','Access Opportunities Tab',1,3),(27,'OpportunitiesModule','Create Opportunities',1,3),(28,'OpportunitiesModule','Delete Opportunities',1,3),(29,'MarketingModule','Access Marketing Tab',1,3),(30,'MarketingListsModule','Access Marketing Lists Tab',1,3),(31,'MarketingListsModule','Create Marketing Lists',1,3),(32,'MarketingListsModule','Delete Marketing Lists',1,3),(33,'MeetingsModule','Access Meetings',1,3),(34,'MeetingsModule','Create Meetings',1,3),(35,'MeetingsModule','Delete Meetings',1,3),(36,'MissionsModule','Access Missions Tab',1,3),(37,'MissionsModule','Create Missions',1,3),(38,'MissionsModule','Delete Missions',1,3),(39,'NotesModule','Access Notes',1,3),(40,'NotesModule','Create Notes',1,3),(41,'NotesModule','Delete Notes',1,3),(42,'ReportsModule','Access Reports Tab',1,3),(43,'ReportsModule','Create Reports',1,3),(44,'ReportsModule','Delete Reports',1,3),(45,'TasksModule','Access Tasks',1,3),(46,'TasksModule','Create Tasks',1,3),(47,'TasksModule','Delete Tasks',1,3),(48,'HomeModule','Access Dashboards',1,3),(49,'HomeModule','Create Dashboards',1,3),(50,'HomeModule','Delete Dashboards',1,3),(51,'ExportModule','Access Export Tool',1,3),(52,'SocialItemsModule','Access Social Items',1,3),(53,'ProductsModule','Access Products Tab',1,3),(54,'ProductsModule','Create Products',1,3),(55,'ProductsModule','Delete Products',1,3),(56,'ProductTemplatesModule','Access Catalog Items Tab',1,3),(57,'ProductTemplatesModule','Create Catalog Items',1,3),(58,'ProductTemplatesModule','Delete Catalog Items',1,3),(59,'ProjectsModule','Access Projects Tab',1,3),(60,'ProjectsModule','Create Projects',1,3),(61,'ProjectsModule','Delete Projects',1,3),(62,'CalendarsModule','Access Calandar Tab',1,3),(63,'CalendarsModule','Create Calendar',1,3),(64,'CalendarsModule','Delete Calendar',1,3),(65,'UsersModule','Login Via Mobile',2,4),(66,'UsersModule','Login Via Web',2,4),(67,'UsersModule','Login Via Web API',2,4);
/*!40000 ALTER TABLE `_right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_user`
--

DROP TABLE IF EXISTS `_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timezone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializedavatardata` text COLLATE utf8_unicode_ci,
  `isactive` tinyint(1) unsigned DEFAULT NULL,
  `isrootuser` tinyint(1) unsigned DEFAULT NULL,
  `hidefromselecting` tinyint(1) unsigned DEFAULT NULL,
  `issystemuser` tinyint(1) unsigned DEFAULT NULL,
  `hidefromleaderboard` tinyint(1) unsigned DEFAULT NULL,
  `lastlogindatetime` datetime DEFAULT NULL,
  `permitable_id` int(11) unsigned DEFAULT NULL,
  `person_id` int(11) unsigned DEFAULT NULL,
  `currency_id` int(11) unsigned DEFAULT NULL,
  `manager__user_id` int(11) unsigned DEFAULT NULL,
  `role_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_emanresu` (`username`),
  KEY `permitable_id` (`permitable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_user`
--

LOCK TABLES `_user` WRITE;
/*!40000 ALTER TABLE `_user` DISABLE KEYS */;
INSERT INTO `_user` VALUES (1,'$2y$12$.OlWd15wc5xIagBoSzg.EOvK8vf6k2vKUvcqrPtw5D4rGbwzQVwFy',NULL,NULL,'America/Chicago','super',NULL,1,1,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL),(2,'$2y$12$NSqDrVVbXkrGUOBGS.t.I.TMuimuotMu36IFdaXJCyirTurorGT7K',NULL,NULL,'America/Chicago','backendjoboractionuser',NULL,0,NULL,1,1,1,NULL,4,2,NULL,NULL,NULL);
/*!40000 ALTER TABLE `_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_user_meeting`
--

DROP TABLE IF EXISTS `_user_meeting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_user_meeting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) unsigned DEFAULT NULL,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_resu__di_gniteem` (`meeting_id`,`_user_id`),
  KEY `di_gniteem` (`meeting_id`),
  KEY `di_resu_` (`_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_user_meeting`
--

LOCK TABLES `_user_meeting` WRITE;
/*!40000 ALTER TABLE `_user_meeting` DISABLE KEYS */;
/*!40000 ALTER TABLE `_user_meeting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `annualrevenue` double DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `employees` int(11) DEFAULT NULL,
  `latestactivitydatetime` datetime DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `officephone` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `officefax` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL,
  `billingaddress_address_id` int(11) unsigned DEFAULT NULL,
  `industry_customfield_id` int(11) unsigned DEFAULT NULL,
  `primaryemail_email_id` int(11) unsigned DEFAULT NULL,
  `secondaryemail_email_id` int(11) unsigned DEFAULT NULL,
  `shippingaddress_address_id` int(11) unsigned DEFAULT NULL,
  `type_customfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account`
--

LOCK TABLES `account` WRITE;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;
/*!40000 ALTER TABLE `account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_project`
--

DROP TABLE IF EXISTS `account_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) unsigned DEFAULT NULL,
  `project_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_tcejorp_di_tnuocca` (`account_id`,`project_id`),
  KEY `di_tnuocca` (`account_id`),
  KEY `di_tcejorp` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_project`
--

LOCK TABLES `account_project` WRITE;
/*!40000 ALTER TABLE `account_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_read`
--

DROP TABLE IF EXISTS `account_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `account_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_read`
--

LOCK TABLES `account_read` WRITE;
/*!40000 ALTER TABLE `account_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_read_subscription`
--

DROP TABLE IF EXISTS `account_read_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_read_subscription` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `modelid` int(11) unsigned NOT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  `subscriptiontype` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_modelid` (`userid`,`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_read_subscription`
--

LOCK TABLES `account_read_subscription` WRITE;
/*!40000 ALTER TABLE `account_read_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_read_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_read_subscription_temp_build`
--

DROP TABLE IF EXISTS `account_read_subscription_temp_build`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_read_subscription_temp_build` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `accountid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_read_subscription_temp_build`
--

LOCK TABLES `account_read_subscription_temp_build` WRITE;
/*!40000 ALTER TABLE `account_read_subscription_temp_build` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_read_subscription_temp_build` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountaccountaffiliation`
--

DROP TABLE IF EXISTS `accountaccountaffiliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountaccountaffiliation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) unsigned DEFAULT NULL,
  `primaryaccountaffiliation_account_id` int(11) unsigned DEFAULT NULL,
  `secondaryaccountaffiliation_account_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountaccountaffiliation`
--

LOCK TABLES `accountaccountaffiliation` WRITE;
/*!40000 ALTER TABLE `accountaccountaffiliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `accountaccountaffiliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountcontactaffiliation`
--

DROP TABLE IF EXISTS `accountcontactaffiliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountcontactaffiliation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `primary` tinyint(1) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `role_customfield_id` int(11) unsigned DEFAULT NULL,
  `accountaffiliation_account_id` int(11) unsigned DEFAULT NULL,
  `contactaffiliation_contact_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountcontactaffiliation`
--

LOCK TABLES `accountcontactaffiliation` WRITE;
/*!40000 ALTER TABLE `accountcontactaffiliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `accountcontactaffiliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountstarred`
--

DROP TABLE IF EXISTS `accountstarred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountstarred` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `basestarredmodel_id` int(11) unsigned DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basestarredmodel_id_account_id` (`basestarredmodel_id`,`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountstarred`
--

LOCK TABLES `accountstarred` WRITE;
/*!40000 ALTER TABLE `accountstarred` DISABLE KEYS */;
/*!40000 ALTER TABLE `accountstarred` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activelanguage`
--

DROP TABLE IF EXISTS `activelanguage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activelanguage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nativename` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activationdatetime` datetime DEFAULT NULL,
  `lastupdatedatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activelanguage`
--

LOCK TABLES `activelanguage` WRITE;
/*!40000 ALTER TABLE `activelanguage` DISABLE KEYS */;
/*!40000 ALTER TABLE `activelanguage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity`
--

DROP TABLE IF EXISTS `activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `latestdatetime` datetime DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity`
--

LOCK TABLES `activity` WRITE;
/*!40000 ALTER TABLE `activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_item`
--

DROP TABLE IF EXISTS `activity_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_meti_di_ytivitca` (`activity_id`,`item_id`),
  KEY `di_ytivitca` (`activity_id`),
  KEY `di_meti` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_item`
--

LOCK TABLES `activity_item` WRITE;
/*!40000 ALTER TABLE `activity_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actual_permissions_cache`
--

DROP TABLE IF EXISTS `actual_permissions_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actual_permissions_cache` (
  `securableitem_id` int(11) unsigned NOT NULL,
  `permitable_id` int(11) unsigned NOT NULL,
  `allow_permissions` tinyint(3) unsigned NOT NULL,
  `deny_permissions` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`securableitem_id`,`permitable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actual_permissions_cache`
--

LOCK TABLES `actual_permissions_cache` WRITE;
/*!40000 ALTER TABLE `actual_permissions_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `actual_permissions_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actual_rights_cache`
--

DROP TABLE IF EXISTS `actual_rights_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actual_rights_cache` (
  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entry` int(11) unsigned NOT NULL,
  PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actual_rights_cache`
--

LOCK TABLES `actual_rights_cache` WRITE;
/*!40000 ALTER TABLE `actual_rights_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `actual_rights_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `address`
--

DROP TABLE IF EXISTS `address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `city` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invalid` tinyint(1) unsigned DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `postalcode` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street1` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street2` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address`
--

LOCK TABLES `address` WRITE;
/*!40000 ALTER TABLE `address` DISABLE KEYS */;
/*!40000 ALTER TABLE `address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditevent`
--

DROP TABLE IF EXISTS `auditevent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditevent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `eventname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modulename` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelid` int(11) DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditevent`
--

LOCK TABLES `auditevent` WRITE;
/*!40000 ALTER TABLE `auditevent` DISABLE KEYS */;
INSERT INTO `auditevent` VALUES (1,'2015-03-04 10:32:47','Item Created','ZurmoModule','User',1,'s:10:\"Super User\";',1),(2,'2015-03-04 10:32:47','User Password Changed','UsersModule','User',1,'s:5:\"super\";',1),(3,'2015-03-04 10:32:47','Item Modified','ZurmoModule','User',1,'a:4:{i:0;s:10:\"Super User\";i:1;a:1:{i:0;s:8:\"isActive\";}i:2;s:5:\"false\";i:3;s:4:\"true\";}',1),(4,'2015-03-04 10:32:47','Item Created','ZurmoModule','Group',1,'s:20:\"Super Administrators\";',1),(5,'2015-03-04 10:32:47','Item Created','ZurmoModule','ImageFileModel',1,'s:10:\"200x50.gif\";',1),(6,'2015-03-04 10:32:47','Item Created','ZurmoModule','ImageFileModel',2,'s:11:\"200x200.gif\";',1),(7,'2015-03-04 10:32:47','Item Created','ZurmoModule','ImageFileModel',3,'s:11:\"580x180.gif\";',1),(8,'2015-03-04 10:32:47','Item Created','ZurmoModule','ImageFileModel',4,'s:14:\"googleMaps.png\";',1),(9,'2015-03-04 10:32:48','Item Created','ZurmoModule','Group',2,'s:8:\"Everyone\";',1),(10,'2015-03-04 10:32:48','Item Created','ZurmoModule','EmailTemplate',1,'s:5:\"Blank\";',1),(11,'2015-03-04 10:32:48','Item Created','ZurmoModule','EmailTemplate',2,'s:8:\"1 Column\";',1),(12,'2015-03-04 10:32:48','Item Created','ZurmoModule','EmailTemplate',3,'s:9:\"2 Columns\";',1),(13,'2015-03-04 10:32:48','Item Created','ZurmoModule','EmailTemplate',4,'s:27:\"2 Columns with strong right\";',1),(14,'2015-03-04 10:32:49','Item Created','ZurmoModule','EmailTemplate',5,'s:9:\"3 Columns\";',1),(15,'2015-03-04 10:32:49','Item Created','ZurmoModule','EmailTemplate',6,'s:19:\"3 Columns with Hero\";',1),(16,'2015-03-04 10:32:52','Item Created','ZurmoModule','User',2,'s:11:\"System User\";',1),(17,'2015-03-04 10:32:52','User Password Changed','UsersModule','User',2,'s:22:\"backendjoboractionuser\";',1),(18,'2015-03-04 10:32:52','Item Modified','ZurmoModule','User',2,'a:4:{i:0;s:11:\"System User\";i:1;a:1:{i:0;s:8:\"isActive\";}i:2;s:5:\"false\";i:3;s:4:\"true\";}',1),(19,'2015-03-04 10:32:52','Item Modified','ZurmoModule','User',2,'a:4:{i:0;s:11:\"System User\";i:1;a:1:{i:0;s:8:\"isActive\";}i:2;s:4:\"true\";i:3;s:5:\"false\";}',1),(20,'2015-03-04 10:32:52','Item Created','ZurmoModule','NotificationMessage',1,'s:6:\"(None)\";',1),(21,'2015-03-04 10:32:52','Item Created','ZurmoModule','Notification',1,'s:52:\"Remove the api test entry script for production use.\";',1);
/*!40000 ALTER TABLE `auditevent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `autoresponder`
--

DROP TABLE IF EXISTS `autoresponder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `autoresponder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `htmlcontent` text COLLATE utf8_unicode_ci,
  `textcontent` text COLLATE utf8_unicode_ci,
  `fromoperationdurationinterval` int(11) DEFAULT NULL,
  `fromoperationdurationtype` text COLLATE utf8_unicode_ci,
  `operationtype` int(11) DEFAULT NULL,
  `enabletracking` tinyint(1) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `marketinglist_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `autoresponder`
--

LOCK TABLES `autoresponder` WRITE;
/*!40000 ALTER TABLE `autoresponder` DISABLE KEYS */;
/*!40000 ALTER TABLE `autoresponder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `autoresponderitem`
--

DROP TABLE IF EXISTS `autoresponderitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `autoresponderitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `processdatetime` datetime DEFAULT NULL,
  `processed` tinyint(1) unsigned DEFAULT NULL,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `emailmessage_id` int(11) unsigned DEFAULT NULL,
  `autoresponder_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `autoresponderitem`
--

LOCK TABLES `autoresponderitem` WRITE;
/*!40000 ALTER TABLE `autoresponderitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `autoresponderitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `autoresponderitemactivity`
--

DROP TABLE IF EXISTS `autoresponderitemactivity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `autoresponderitemactivity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessageactivity_id` int(11) unsigned DEFAULT NULL,
  `autoresponderitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emailmessageactivity_id_autoresponderitem_id` (`emailmessageactivity_id`,`autoresponderitem_id`),
  KEY `emailmessageactivity_id` (`emailmessageactivity_id`),
  KEY `autoresponderitem_id` (`autoresponderitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `autoresponderitemactivity`
--

LOCK TABLES `autoresponderitemactivity` WRITE;
/*!40000 ALTER TABLE `autoresponderitemactivity` DISABLE KEYS */;
/*!40000 ALTER TABLE `autoresponderitemactivity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `basecustomfield`
--

DROP TABLE IF EXISTS `basecustomfield`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `basecustomfield` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data_customfielddata_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `basecustomfield`
--

LOCK TABLES `basecustomfield` WRITE;
/*!40000 ALTER TABLE `basecustomfield` DISABLE KEYS */;
/*!40000 ALTER TABLE `basecustomfield` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `basestarredmodel`
--

DROP TABLE IF EXISTS `basestarredmodel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `basestarredmodel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `basestarredmodel`
--

LOCK TABLES `basestarredmodel` WRITE;
/*!40000 ALTER TABLE `basestarredmodel` DISABLE KEYS */;
/*!40000 ALTER TABLE `basestarredmodel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bytimeworkflowinqueue`
--

DROP TABLE IF EXISTS `bytimeworkflowinqueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bytimeworkflowinqueue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modelclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `processdatetime` datetime DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `modelitem_item_id` int(11) unsigned DEFAULT NULL,
  `savedworkflow_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bytimeworkflowinqueue`
--

LOCK TABLES `bytimeworkflowinqueue` WRITE;
/*!40000 ALTER TABLE `bytimeworkflowinqueue` DISABLE KEYS */;
/*!40000 ALTER TABLE `bytimeworkflowinqueue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calculatedderivedattributemetadata`
--

DROP TABLE IF EXISTS `calculatedderivedattributemetadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calculatedderivedattributemetadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `derivedattributemetadata_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calculatedderivedattributemetadata`
--

LOCK TABLES `calculatedderivedattributemetadata` WRITE;
/*!40000 ALTER TABLE `calculatedderivedattributemetadata` DISABLE KEYS */;
/*!40000 ALTER TABLE `calculatedderivedattributemetadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign`
--

DROP TABLE IF EXISTS `campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `supportsrichtext` tinyint(1) unsigned DEFAULT NULL,
  `sendondatetime` datetime DEFAULT NULL,
  `fromname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromaddress` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `htmlcontent` text COLLATE utf8_unicode_ci,
  `textcontent` text COLLATE utf8_unicode_ci,
  `enabletracking` tinyint(1) unsigned DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `marketinglist_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign`
--

LOCK TABLES `campaign` WRITE;
/*!40000 ALTER TABLE `campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_read`
--

DROP TABLE IF EXISTS `campaign_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `campaign_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_read`
--

LOCK TABLES `campaign_read` WRITE;
/*!40000 ALTER TABLE `campaign_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaignitem`
--

DROP TABLE IF EXISTS `campaignitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaignitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `processed` tinyint(1) unsigned DEFAULT NULL,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `emailmessage_id` int(11) unsigned DEFAULT NULL,
  `campaign_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaignitem`
--

LOCK TABLES `campaignitem` WRITE;
/*!40000 ALTER TABLE `campaignitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaignitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaignitemactivity`
--

DROP TABLE IF EXISTS `campaignitemactivity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaignitemactivity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessageactivity_id` int(11) unsigned DEFAULT NULL,
  `campaignitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emailmessageactivity_id_campaignitem_id` (`emailmessageactivity_id`,`campaignitem_id`),
  KEY `emailmessageactivity_id` (`emailmessageactivity_id`),
  KEY `campaignitem_id` (`campaignitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaignitemactivity`
--

LOCK TABLES `campaignitemactivity` WRITE;
/*!40000 ALTER TABLE `campaignitemactivity` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaignitemactivity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  `relatedmodel_id` int(11) unsigned DEFAULT NULL,
  `relatedmodel_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment`
--

LOCK TABLES `comment` WRITE;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `companyname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `latestactivitydatetime` datetime DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `googlewebtrackingid` text COLLATE utf8_unicode_ci,
  `person_id` int(11) unsigned DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL,
  `industry_customfield_id` int(11) unsigned DEFAULT NULL,
  `secondaryaddress_address_id` int(11) unsigned DEFAULT NULL,
  `secondaryemail_email_id` int(11) unsigned DEFAULT NULL,
  `source_customfield_id` int(11) unsigned DEFAULT NULL,
  `state_contactstate_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_opportunity`
--

DROP TABLE IF EXISTS `contact_opportunity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_opportunity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `opportunity_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_ytinutroppo_di_tcatnoc` (`contact_id`,`opportunity_id`),
  KEY `di_tcatnoc` (`contact_id`),
  KEY `di_ytinutroppo` (`opportunity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_opportunity`
--

LOCK TABLES `contact_opportunity` WRITE;
/*!40000 ALTER TABLE `contact_opportunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_opportunity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_project`
--

DROP TABLE IF EXISTS `contact_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `project_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_tcejorp_di_tcatnoc` (`contact_id`,`project_id`),
  KEY `di_tcatnoc` (`contact_id`),
  KEY `di_tcejorp` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_project`
--

LOCK TABLES `contact_project` WRITE;
/*!40000 ALTER TABLE `contact_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_read`
--

DROP TABLE IF EXISTS `contact_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `contact_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_read`
--

LOCK TABLES `contact_read` WRITE;
/*!40000 ALTER TABLE `contact_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_read_subscription`
--

DROP TABLE IF EXISTS `contact_read_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_read_subscription` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `modelid` int(11) unsigned NOT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  `subscriptiontype` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_modelid` (`userid`,`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_read_subscription`
--

LOCK TABLES `contact_read_subscription` WRITE;
/*!40000 ALTER TABLE `contact_read_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_read_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactstarred`
--

DROP TABLE IF EXISTS `contactstarred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactstarred` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `basestarredmodel_id` int(11) unsigned DEFAULT NULL,
  `contact_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basestarredmodel_id_contact_id` (`basestarredmodel_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactstarred`
--

LOCK TABLES `contactstarred` WRITE;
/*!40000 ALTER TABLE `contactstarred` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactstarred` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactstate`
--

DROP TABLE IF EXISTS `contactstate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactstate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `serializedlabels` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactstate`
--

LOCK TABLES `contactstate` WRITE;
/*!40000 ALTER TABLE `contactstate` DISABLE KEYS */;
INSERT INTO `contactstate` VALUES (1,'New',0,NULL),(2,'In Progress',1,NULL),(3,'Recycled',2,NULL),(4,'Dead',3,NULL),(5,'Qualified',4,NULL),(6,'Customer',5,NULL);
/*!40000 ALTER TABLE `contactstate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactwebform`
--

DROP TABLE IF EXISTS `contactwebform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactwebform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci,
  `redirecturl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `submitbuttonlabel` text COLLATE utf8_unicode_ci,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `excludestyles` tinyint(1) unsigned DEFAULT NULL,
  `enablecaptcha` tinyint(1) unsigned DEFAULT NULL,
  `language` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `defaultpermissionsetting` tinyint(11) DEFAULT NULL,
  `defaultpermissiongroupsetting` int(11) DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `defaultstate_contactstate_id` int(11) unsigned DEFAULT NULL,
  `defaultowner__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactwebform`
--

LOCK TABLES `contactwebform` WRITE;
/*!40000 ALTER TABLE `contactwebform` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactwebform` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactwebform_read`
--

DROP TABLE IF EXISTS `contactwebform_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactwebform_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `contactwebform_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactwebform_read`
--

LOCK TABLES `contactwebform_read` WRITE;
/*!40000 ALTER TABLE `contactwebform_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactwebform_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactwebformentry`
--

DROP TABLE IF EXISTS `contactwebformentry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactwebformentry` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `status` int(11) DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `hashindex` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `entries_contactwebform_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactwebformentry`
--

LOCK TABLES `contactwebformentry` WRITE;
/*!40000 ALTER TABLE `contactwebformentry` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactwebformentry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation`
--

DROP TABLE IF EXISTS `conversation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `latestdatetime` datetime DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownerhasreadlatest` tinyint(1) unsigned DEFAULT NULL,
  `isclosed` tinyint(1) unsigned DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation`
--

LOCK TABLES `conversation` WRITE;
/*!40000 ALTER TABLE `conversation` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_item`
--

DROP TABLE IF EXISTS `conversation_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_meti_di_noitasrevnoc` (`conversation_id`,`item_id`),
  KEY `di_noitasrevnoc` (`conversation_id`),
  KEY `di_meti` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_item`
--

LOCK TABLES `conversation_item` WRITE;
/*!40000 ALTER TABLE `conversation_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_read`
--

DROP TABLE IF EXISTS `conversation_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `conversation_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_read`
--

LOCK TABLES `conversation_read` WRITE;
/*!40000 ALTER TABLE `conversation_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversationparticipant`
--

DROP TABLE IF EXISTS `conversationparticipant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversationparticipant` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hasreadlatest` tinyint(1) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  `conversation_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversationparticipant`
--

LOCK TABLES `conversationparticipant` WRITE;
/*!40000 ALTER TABLE `conversationparticipant` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversationparticipant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversationstarred`
--

DROP TABLE IF EXISTS `conversationstarred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversationstarred` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `basestarredmodel_id` int(11) unsigned DEFAULT NULL,
  `conversation_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basestarredmodel_id_conversation_id` (`basestarredmodel_id`,`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversationstarred`
--

LOCK TABLES `conversationstarred` WRITE;
/*!40000 ALTER TABLE `conversationstarred` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversationstarred` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) unsigned DEFAULT NULL,
  `code` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ratetobase` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_edoc` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency`
--

LOCK TABLES `currency` WRITE;
/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
INSERT INTO `currency` VALUES (1,1,'USD',1);
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencyvalue`
--

DROP TABLE IF EXISTS `currencyvalue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencyvalue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ratetobase` double DEFAULT NULL,
  `value` double DEFAULT NULL,
  `currency_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencyvalue`
--

LOCK TABLES `currencyvalue` WRITE;
/*!40000 ALTER TABLE `currencyvalue` DISABLE KEYS */;
/*!40000 ALTER TABLE `currencyvalue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customfield`
--

DROP TABLE IF EXISTS `customfield`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customfield` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `value` text COLLATE utf8_unicode_ci,
  `basecustomfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customfield`
--

LOCK TABLES `customfield` WRITE;
/*!40000 ALTER TABLE `customfield` DISABLE KEYS */;
/*!40000 ALTER TABLE `customfield` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customfielddata`
--

DROP TABLE IF EXISTS `customfielddata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customfielddata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `defaultvalue` text COLLATE utf8_unicode_ci,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `serializedlabels` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_eman` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customfielddata`
--

LOCK TABLES `customfielddata` WRITE;
/*!40000 ALTER TABLE `customfielddata` DISABLE KEYS */;
INSERT INTO `customfielddata` VALUES (1,'AccountContactAffiliationRoles',NULL,'a:6:{i:0;s:7:\"Billing\";i:1;s:8:\"Shipping\";i:2;s:7:\"Support\";i:3;s:9:\"Technical\";i:4;s:14:\"Administrative\";i:5;s:15:\"Project Manager\";}',NULL),(2,'Industries',NULL,'a:9:{i:0;s:10:\"Automotive\";i:1;s:7:\"Banking\";i:2;s:17:\"Business Services\";i:3;s:6:\"Energy\";i:4;s:18:\"Financial Services\";i:5;s:9:\"Insurance\";i:6;s:13:\"Manufacturing\";i:7;s:6:\"Retail\";i:8;s:10:\"Technology\";}',NULL),(3,'AccountTypes',NULL,'a:3:{i:0;s:8:\"Prospect\";i:1;s:8:\"Customer\";i:2;s:6:\"Vendor\";}',NULL),(4,'LeadSources',NULL,'a:4:{i:0;s:14:\"Self-Generated\";i:1;s:12:\"Inbound Call\";i:2;s:9:\"Tradeshow\";i:3;s:13:\"Word of Mouth\";}',NULL),(5,'MeetingCategories','Meeting','a:2:{i:0;s:7:\"Meeting\";i:1;s:4:\"Call\";}',NULL),(6,'SalesStages','Prospecting','a:6:{i:0;s:11:\"Prospecting\";i:1;s:13:\"Qualification\";i:2;s:11:\"Negotiating\";i:3;s:6:\"Verbal\";i:4;s:10:\"Closed Won\";i:5;s:11:\"Closed Lost\";}',NULL),(7,'ProductStages',NULL,'a:3:{i:0;s:4:\"Open\";i:1;s:4:\"Lost\";i:2;s:3:\"Won\";}',NULL),(8,'Titles',NULL,'a:4:{i:0;s:3:\"Mr.\";i:1;s:4:\"Mrs.\";i:2;s:3:\"Ms.\";i:3;s:3:\"Dr.\";}',NULL);
/*!40000 ALTER TABLE `customfielddata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customfieldvalue`
--

DROP TABLE IF EXISTS `customfieldvalue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customfieldvalue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `multiplevaluescustomfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `multiplevaluescustomfield_id` (`multiplevaluescustomfield_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customfieldvalue`
--

LOCK TABLES `customfieldvalue` WRITE;
/*!40000 ALTER TABLE `customfieldvalue` DISABLE KEYS */;
/*!40000 ALTER TABLE `customfieldvalue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard`
--

DROP TABLE IF EXISTS `dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashboard` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `isdefault` tinyint(1) unsigned DEFAULT NULL,
  `layoutid` int(11) DEFAULT NULL,
  `layouttype` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard`
--

LOCK TABLES `dashboard` WRITE;
/*!40000 ALTER TABLE `dashboard` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `derivedattributemetadata`
--

DROP TABLE IF EXISTS `derivedattributemetadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `derivedattributemetadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializedmetadata` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `derivedattributemetadata`
--

LOCK TABLES `derivedattributemetadata` WRITE;
/*!40000 ALTER TABLE `derivedattributemetadata` DISABLE KEYS */;
/*!40000 ALTER TABLE `derivedattributemetadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dropdowndependencyderivedattributemetadata`
--

DROP TABLE IF EXISTS `dropdowndependencyderivedattributemetadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dropdowndependencyderivedattributemetadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `derivedattributemetadata_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dropdowndependencyderivedattributemetadata`
--

LOCK TABLES `dropdowndependencyderivedattributemetadata` WRITE;
/*!40000 ALTER TABLE `dropdowndependencyderivedattributemetadata` DISABLE KEYS */;
/*!40000 ALTER TABLE `dropdowndependencyderivedattributemetadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `emailaddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isinvalid` tinyint(1) unsigned DEFAULT NULL,
  `optout` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email`
--

LOCK TABLES `email` WRITE;
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
/*!40000 ALTER TABLE `email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailaccount`
--

DROP TABLE IF EXISTS `emailaccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailaccount` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fromname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromaddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` text COLLATE utf8_unicode_ci,
  `replytoname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outboundhost` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outboundusername` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outboundpassword` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outboundsecurity` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outboundtype` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outboundport` int(11) DEFAULT NULL,
  `usecustomoutboundsettings` tinyint(1) unsigned DEFAULT NULL,
  `replytoaddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailaccount`
--

LOCK TABLES `emailaccount` WRITE;
/*!40000 ALTER TABLE `emailaccount` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailaccount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailbox`
--

DROP TABLE IF EXISTS `emailbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailbox` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailbox`
--

LOCK TABLES `emailbox` WRITE;
/*!40000 ALTER TABLE `emailbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailfolder`
--

DROP TABLE IF EXISTS `emailfolder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailfolder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `emailbox_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailfolder`
--

LOCK TABLES `emailfolder` WRITE;
/*!40000 ALTER TABLE `emailfolder` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailfolder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessage`
--

DROP TABLE IF EXISTS `emailmessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sendattempts` int(11) DEFAULT NULL,
  `sentdatetime` datetime DEFAULT NULL,
  `sendondatetime` datetime DEFAULT NULL,
  `headers` text COLLATE utf8_unicode_ci,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `folder_emailfolder_id` int(11) unsigned DEFAULT NULL,
  `content_emailmessagecontent_id` int(11) unsigned DEFAULT NULL,
  `sender_emailmessagesender_id` int(11) unsigned DEFAULT NULL,
  `error_emailmessagesenderror_id` int(11) unsigned DEFAULT NULL,
  `account_emailaccount_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessage`
--

LOCK TABLES `emailmessage` WRITE;
/*!40000 ALTER TABLE `emailmessage` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessage_read`
--

DROP TABLE IF EXISTS `emailmessage_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessage_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `emailmessage_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessage_read`
--

LOCK TABLES `emailmessage_read` WRITE;
/*!40000 ALTER TABLE `emailmessage_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessage_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessageactivity`
--

DROP TABLE IF EXISTS `emailmessageactivity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessageactivity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `latestdatetime` datetime DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `latestsourceip` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_id` int(11) unsigned DEFAULT NULL,
  `emailmessageurl_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessageactivity`
--

LOCK TABLES `emailmessageactivity` WRITE;
/*!40000 ALTER TABLE `emailmessageactivity` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessageactivity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessagecontent`
--

DROP TABLE IF EXISTS `emailmessagecontent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessagecontent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `htmlcontent` text COLLATE utf8_unicode_ci,
  `textcontent` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessagecontent`
--

LOCK TABLES `emailmessagecontent` WRITE;
/*!40000 ALTER TABLE `emailmessagecontent` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessagecontent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessagerecipient`
--

DROP TABLE IF EXISTS `emailmessagerecipient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessagerecipient` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `toaddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `toname` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `emailmessage_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `remailmessage` (`emailmessage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessagerecipient`
--

LOCK TABLES `emailmessagerecipient` WRITE;
/*!40000 ALTER TABLE `emailmessagerecipient` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessagerecipient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessagerecipient_item`
--

DROP TABLE IF EXISTS `emailmessagerecipient_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessagerecipient_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessagerecipient_id` int(11) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_meti_di_tneipiceregassemliame` (`emailmessagerecipient_id`,`item_id`),
  KEY `di_tneipiceregassemliame` (`emailmessagerecipient_id`),
  KEY `di_meti` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessagerecipient_item`
--

LOCK TABLES `emailmessagerecipient_item` WRITE;
/*!40000 ALTER TABLE `emailmessagerecipient_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessagerecipient_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessagesender`
--

DROP TABLE IF EXISTS `emailmessagesender`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessagesender` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fromaddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromname` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessagesender`
--

LOCK TABLES `emailmessagesender` WRITE;
/*!40000 ALTER TABLE `emailmessagesender` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessagesender` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessagesender_item`
--

DROP TABLE IF EXISTS `emailmessagesender_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessagesender_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessagesender_id` int(11) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_meti_di_rednesegassemliame` (`emailmessagesender_id`,`item_id`),
  KEY `di_rednesegassemliame` (`emailmessagesender_id`),
  KEY `di_meti` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessagesender_item`
--

LOCK TABLES `emailmessagesender_item` WRITE;
/*!40000 ALTER TABLE `emailmessagesender_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessagesender_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessagesenderror`
--

DROP TABLE IF EXISTS `emailmessagesenderror`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessagesenderror` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `createddatetime` datetime DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessagesenderror`
--

LOCK TABLES `emailmessagesenderror` WRITE;
/*!40000 ALTER TABLE `emailmessagesenderror` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessagesenderror` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailmessageurl`
--

DROP TABLE IF EXISTS `emailmessageurl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailmessageurl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emailmessageactivity_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailmessageurl`
--

LOCK TABLES `emailmessageurl` WRITE;
/*!40000 ALTER TABLE `emailmessageurl` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailmessageurl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailsignature`
--

DROP TABLE IF EXISTS `emailsignature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailsignature` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `textcontent` text COLLATE utf8_unicode_ci,
  `htmlcontent` text COLLATE utf8_unicode_ci,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailsignature`
--

LOCK TABLES `emailsignature` WRITE;
/*!40000 ALTER TABLE `emailsignature` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailsignature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailtemplate`
--

DROP TABLE IF EXISTS `emailtemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailtemplate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `isdraft` tinyint(1) unsigned DEFAULT NULL,
  `builttype` int(11) DEFAULT NULL,
  `modelclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `htmlcontent` text COLLATE utf8_unicode_ci,
  `textcontent` text COLLATE utf8_unicode_ci,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `isfeatured` tinyint(1) unsigned DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailtemplate`
--

LOCK TABLES `emailtemplate` WRITE;
/*!40000 ALTER TABLE `emailtemplate` DISABLE KEYS */;
INSERT INTO `emailtemplate` VALUES (1,NULL,0,3,NULL,'Blank','Blank','en',NULL,NULL,'{\"baseTemplateId\":\"\",\"icon\":\"icon-template-0\",\"dom\":{\"canvas1\":{\"content\":{\"builderrowelement_1393965668_53163a6448794\":{\"content\":{\"buildercolumnelement_1393965668_53163a644866d\":{\"content\":[],\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1\"}},\"class\":\"BuilderRowElement\"}},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#ffffff\",\"color\":\"#545454\"}}},\"class\":\"BuilderCanvasElement\"}}}',NULL,1),(2,NULL,0,3,NULL,'1 Column','1 Column','en',NULL,NULL,'{\"baseTemplateId\":\"\",\"icon\":\"icon-template-5\",\"dom\":{\"canvas1\":{\"content\":{\"builderheaderimagetextelement_1393965594_53163a1a0eb53\":{\"content\":{\"buildercolumnelement_1393965594_53163a1a0ef48\":{\"content\":{\"builderimageelement_1393965594_53163a1a0ee52\":{\"content\":{\"image\":1},\"properties\":[],\"class\":\"BuilderImageElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1393965594_53163a1a145cc\":{\"content\":{\"builderheadertextelement_1393965594_53163a1a14515\":{\"content\":{\"text\":\"Acme Inc. Newsletter\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"color\":\"#ffffff\",\"font-weight\":\"bold\",\"text-align\":\"right\"}}},\"class\":\"BuilderHeaderTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1:2\",\"header\":\"1\"},\"frontend\":{\"inlineStyles\":{\"background-color\":\"#282a76\"}}},\"class\":\"BuilderHeaderImageTextElement\"},\"builderrowelement_1393965668_53163a6448794\":{\"content\":{\"buildercolumnelement_1393965668_53163a644866d\":{\"content\":{\"buildertitleelement_1393965668_53163a6447762\":{\"content\":{\"text\":\"Hello there William S...\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#666666\",\"font-size\":\"24\",\"font-weight\":\"bold\",\"text-align\":\"center\"}}},\"class\":\"BuilderTitleElement\"},\"builderimageelement_1393970522_53164d5a3787a\":{\"content\":{\"image\":3},\"properties\":[],\"class\":\"BuilderImageElement\"},\"builderexpanderelement_1393970557_53164d7d2881e\":{\"content\":[],\"properties\":{\"frontend\":{\"height\":\"10\"}},\"class\":\"BuilderExpanderElement\"},\"buildertextelement_1393965781_53163ad53b77c\":{\"content\":{\"text\":\"\\n<p>\\n    Orsino, the <i>Duke of Illyria<\\/i>, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: \\\"If music be the food of love, play on.\\\" It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that <b>Olivia<\\/b> plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.\\n<\\/p>\\n\"},\"properties\":[],\"class\":\"BuilderTextElement\"},\"builderbuttonelement_1393965942_53163b76e666c\":{\"content\":[],\"properties\":{\"backend\":{\"text\":\"Call Me\",\"sizeClass\":\"medium-button\",\"align\":\"left\"},\"frontend\":{\"href\":\"http:\\/\\/localhost\\/Zurmo\\/app\\/index.php\",\"target\":\"_blank\",\"inlineStyles\":{\"background-color\":\"#97c43d\",\"border-color\":\"#7cb830\"}}},\"class\":\"BuilderButtonElement\"},\"builderdividerelement_1393965948_53163b7cb98ae\":{\"content\":[],\"properties\":{\"frontend\":{\"inlineStyles\":{\"border-top-width\":\"1\",\"border-top-style\":\"solid\",\"border-top-color\":\"#cccccc\"}},\"backend\":{\"divider-padding\":\"10\"}},\"class\":\"BuilderDividerElement\"},\"buildersocialelement_1394060039_5317ab07cf03d\":{\"content\":[],\"properties\":{\"backend\":{\"layout\":\"vertical\",\"services\":{\"Twitter\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.twitter.com\\/\"},\"Facebook\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.facebook.com\\/\"},\"GooglePlus\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/gplus.com\"}}}},\"class\":\"BuilderSocialElement\"},\"builderexpanderelement_1393970592_53164da0bd137\":{\"content\":[],\"properties\":{\"frontend\":{\"height\":\"10\"}},\"class\":\"BuilderExpanderElement\"},\"builderfooterelement_1393966090_53163c0ac51bd\":{\"content\":{\"text\":\"[[GLOBAL^MARKETING^FOOTER^HTML]]\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#efefef\",\"font-size\":\"10\"}}},\"class\":\"BuilderFooterElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"}},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#ffffff\",\"color\":\"#545454\"}}},\"class\":\"BuilderCanvasElement\"}}}',NULL,2),(3,NULL,0,3,NULL,'2 Columns','2 Columns','en',NULL,NULL,'{\"baseTemplateId\":\"\",\"icon\":\"icon-template-2\",\"dom\":{\"canvas1\":{\"content\":{\"builderheaderimagetextelement_1393965594_53163a1a0eb53\":{\"content\":{\"buildercolumnelement_1393965594_53163a1a0ef48\":{\"content\":{\"builderimageelement_1393965594_53163a1a0ee52\":{\"content\":{\"image\":1},\"properties\":[],\"class\":\"BuilderImageElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1393965594_53163a1a145cc\":{\"content\":{\"builderheadertextelement_1393965594_53163a1a14515\":{\"content\":{\"text\":\"Acme Inc. Newsletter\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"color\":\"#ffffff\",\"font-weight\":\"bold\",\"text-align\":\"right\"}}},\"class\":\"BuilderHeaderTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1:2\",\"header\":\"1\"},\"frontend\":{\"inlineStyles\":{\"background-color\":\"#282a76\"}}},\"class\":\"BuilderHeaderImageTextElement\"},\"builderrowelement_1394062546_5317b4d264a62\":{\"content\":{\"buildercolumnelement_1394062546_5317b4d26488b\":{\"content\":{\"buildertitleelement_1394062546_5317b4d263942\":{\"content\":{\"text\":\"Hello there William S...\"},\"properties\":{\"backend\":{\"headingLevel\":\"h1\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#666666\",\"font-size\":\"28\",\"font-weight\":\"bold\",\"line-height\":\"200\"}}},\"class\":\"BuilderTitleElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1393965668_53163a6448794\":{\"content\":{\"buildercolumnelement_1393965668_53163a644866d\":{\"content\":{\"buildertextelement_1393965781_53163ad53b77c\":{\"content\":{\"text\":\"\\n<p>\\n    Orsino, the <i>Duke of Illyria<\\/i>, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: \\\"If music be the food of love, play on.\\\" It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that <b>Olivia<\\/b> plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.\\n<\\/p>\\n\"},\"properties\":[],\"class\":\"BuilderTextElement\"},\"builderbuttonelement_1393965942_53163b76e666c\":{\"content\":[],\"properties\":{\"backend\":{\"text\":\"Contact Us Now\",\"sizeClass\":\"medium-button\",\"align\":\"left\"},\"frontend\":{\"href\":\"http:\\/\\/localhost\\/Zurmo\\/app\\/index.php\",\"target\":\"_blank\",\"inlineStyles\":{\"background-color\":\"#97c43d\",\"border-color\":\"#7cb830\"}}},\"class\":\"BuilderButtonElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1394061698_5317b182c1f19\":{\"content\":{\"buildertextelement_1394061967_5317b28fc8088\":{\"content\":{\"text\":\"\\n<b>New Articles<\\/b>\\n<ul>\\n    <li>Article Name about something<\\/li>\\n    <li>10 ways to create email templates<\\/li>\\n    <li>Great new marketing tools from Acme<\\/li>\\n    <li>Best blog post of the year<\\/li>\\n    <li>Meet our new chef<\\/li>\\n<\\/ul>\\n\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#f6f6f7\",\"color\":\"#323232\",\"font-size\":\"16\"}}},\"class\":\"BuilderTextElement\"},\"builderexpanderelement_1394062193_5317b37137abc\":{\"content\":[],\"properties\":{\"frontend\":{\"height\":\"10\"}},\"class\":\"BuilderExpanderElement\"},\"buildertitleelement_1394062361_5317b419e1c51\":{\"content\":{\"text\":\"Acme Elsewhere\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#6c1d1d\",\"font-weight\":\"bold\",\"line-height\":\"200\"}}},\"class\":\"BuilderTitleElement\"},\"buildersocialelement_1394060039_5317ab07cf03d\":{\"content\":[],\"properties\":{\"backend\":{\"layout\":\"vertical\",\"services\":{\"Twitter\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.twitter.com\\/\"},\"Facebook\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.facebook.com\\/\"},\"GooglePlus\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/gplus.com\"}}}},\"class\":\"BuilderSocialElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"2\"}},\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062652_5317b53c906f9\":{\"content\":{\"buildercolumnelement_1394062652_5317b53c90615\":{\"content\":{\"builderdividerelement_1394062652_5317b53c901fc\":{\"content\":[],\"properties\":{\"frontend\":{\"inlineStyles\":{\"border-top-width\":\"1\",\"border-top-style\":\"dotted\",\"border-top-color\":\"#efefef\"}},\"backend\":{\"divider-padding\":\"10\"}},\"class\":\"BuilderDividerElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062641_5317b53112a36\":{\"content\":{\"buildercolumnelement_1394062641_5317b5311291a\":{\"content\":{\"builderfooterelement_1394062641_5317b5311226e\":{\"content\":{\"text\":\"[[GLOBAL^MARKETING^FOOTER^HTML]]\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"font-size\":\"11\",\"background-color\":\"#ebebeb\"}}},\"class\":\"BuilderFooterElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"}},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#ffffff\",\"color\":\"#545454\"}}},\"class\":\"BuilderCanvasElement\"}}}',NULL,3),(4,NULL,0,3,NULL,'2 Columns with strong right','2 Columns with strong right','en',NULL,NULL,'{\"baseTemplateId\":\"\",\"icon\":\"icon-template-3\",\"dom\":{\"canvas1\":{\"content\":{\"builderheaderimagetextelement_1393965594_53163a1a0eb53\":{\"content\":{\"buildercolumnelement_1393965594_53163a1a0ef48\":{\"content\":{\"builderimageelement_1393965594_53163a1a0ee52\":{\"content\":{\"image\":1},\"properties\":[],\"class\":\"BuilderImageElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1393965594_53163a1a145cc\":{\"content\":{\"builderheadertextelement_1393965594_53163a1a14515\":{\"content\":{\"text\":\"Acme Inc. Newsletter\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"color\":\"#ffffff\",\"font-weight\":\"bold\",\"text-align\":\"right\"}}},\"class\":\"BuilderHeaderTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1:2\",\"header\":\"1\"},\"frontend\":{\"inlineStyles\":{\"background-color\":\"#282a76\"}}},\"class\":\"BuilderHeaderImageTextElement\"},\"builderrowelement_1394062546_5317b4d264a62\":{\"content\":{\"buildercolumnelement_1394062546_5317b4d26488b\":{\"content\":{\"buildertitleelement_1394062546_5317b4d263942\":{\"content\":{\"text\":\"Hello there William S...\"},\"properties\":{\"backend\":{\"headingLevel\":\"h1\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#666666\",\"font-size\":\"28\",\"font-weight\":\"bold\",\"line-height\":\"200\"}}},\"class\":\"BuilderTitleElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1393965668_53163a6448794\":{\"content\":{\"buildercolumnelement_1393965668_53163a644866d\":{\"content\":{\"buildertextelement_1394061967_5317b28fc8088\":{\"content\":{\"text\":\"\\n <b>New Products<\\/b>\\n<ul>\\n    <li><a href=\\\"#\\\" target=\\\"_blank\\\">AcmeMaster 10,000<\\/a><\\/li>\\n    <li><a href=\\\"#\\\">ProAcme 5,000<\\/a><\\/li>\\n    <li><a href=\\\"#\\\">AcmeMaster++<\\/a><\\/li>\\n    <li><a href=\\\"#\\\" target=\\\"_blank\\\">The Acme Beginner pro<\\/a><\\/li>\\n<\\/ul>\\n\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#f6f6f7\",\"color\":\"#323232\",\"font-size\":\"16\"}}},\"class\":\"BuilderTextElement\"},\"buildertitleelement_1394062361_5317b419e1c51\":{\"content\":{\"text\":\"Follow Us!\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#6c1d1d\",\"font-weight\":\"bold\",\"line-height\":\"200\"}}},\"class\":\"BuilderTitleElement\"},\"buildersocialelement_1394060039_5317ab07cf03d\":{\"content\":[],\"properties\":{\"backend\":{\"layout\":\"vertical\",\"services\":{\"Twitter\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.twitter.com\\/\"},\"Facebook\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.facebook.com\\/\"},\"GooglePlus\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/gplus.com\"}}}},\"class\":\"BuilderSocialElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1394061698_5317b182c1f19\":{\"content\":{\"buildertextelement_1393965781_53163ad53b77c\":{\"content\":{\"text\":\"\\n<p>\\n    Orsino, the <i>Duke of Illyria<\\/i>, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: \\\"If music be the food of love, play on.\\\" It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that <b>Olivia<\\/b> plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.\\n<\\/p>\\n\"},\"properties\":[],\"class\":\"BuilderTextElement\"},\"builderbuttonelement_1393965942_53163b76e666c\":{\"content\":[],\"properties\":{\"backend\":{\"text\":\"Contact Us Now\",\"sizeClass\":\"medium-button\",\"align\":\"left\"},\"frontend\":{\"href\":\"http:\\/\\/localhost\\/Zurmo\\/app\\/index.php\",\"target\":\"_blank\",\"inlineStyles\":{\"background-color\":\"#97c43d\",\"border-color\":\"#7cb830\"}}},\"class\":\"BuilderButtonElement\"},\"builderexpanderelement_1394062193_5317b37137abc\":{\"content\":[],\"properties\":{\"frontend\":{\"height\":\"10\"}},\"class\":\"BuilderExpanderElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1:2\"}},\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062652_5317b53c906f9\":{\"content\":{\"buildercolumnelement_1394062652_5317b53c90615\":{\"content\":{\"builderdividerelement_1394062652_5317b53c901fc\":{\"content\":[],\"properties\":{\"frontend\":{\"inlineStyles\":{\"border-top-width\":\"1\",\"border-top-style\":\"dotted\",\"border-top-color\":\"#efefef\"}},\"backend\":{\"divider-padding\":\"10\"}},\"class\":\"BuilderDividerElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062641_5317b53112a36\":{\"content\":{\"buildercolumnelement_1394062641_5317b5311291a\":{\"content\":{\"builderfooterelement_1394062641_5317b5311226e\":{\"content\":{\"text\":\"[[GLOBAL^MARKETING^FOOTER^HTML]]\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"font-size\":\"11\",\"background-color\":\"#ebebeb\"}}},\"class\":\"BuilderFooterElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"}},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#ffffff\",\"color\":\"#545454\"}}},\"class\":\"BuilderCanvasElement\"}}}',NULL,4),(5,NULL,0,3,NULL,'3 Columns','3 Columns','en',NULL,NULL,'{\"baseTemplateId\":\"\",\"icon\":\"icon-template-4\",\"dom\":{\"canvas1\":{\"content\":{\"builderheaderimagetextelement_1393965594_53163a1a0eb53\":{\"content\":{\"buildercolumnelement_1393965594_53163a1a0ef48\":{\"content\":{\"builderimageelement_1393965594_53163a1a0ee52\":{\"content\":{\"image\":1},\"properties\":[],\"class\":\"BuilderImageElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1393965594_53163a1a145cc\":{\"content\":{\"builderheadertextelement_1393965594_53163a1a14515\":{\"content\":{\"text\":\"Acme Inc. Newsletter\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"color\":\"#ffffff\",\"font-weight\":\"bold\",\"text-align\":\"right\"}}},\"class\":\"BuilderHeaderTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1:2\"},\"frontend\":{\"inlineStyles\":{\"background-color\":\"#282a76\"}}},\"class\":\"BuilderHeaderImageTextElement\"},\"builderrowelement_1394062546_5317b4d264a62\":{\"content\":{\"buildercolumnelement_1394062546_5317b4d26488b\":{\"content\":{\"buildertitleelement_1394062546_5317b4d263942\":{\"content\":{\"text\":\"Latest entries on our database\"},\"properties\":{\"backend\":{\"headingLevel\":\"h1\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#666666\",\"font-size\":\"28\",\"font-weight\":\"bold\",\"line-height\":\"200\"}}},\"class\":\"BuilderTitleElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1393965668_53163a6448794\":{\"content\":{\"buildercolumnelement_1393965668_53163a644866d\":{\"content\":{\"builderimageelement_1394063801_5317b9b9eedc5\":{\"content\":{\"image\":2},\"properties\":[],\"class\":\"BuilderImageElement\"},\"buildertitleelement_1394063416_5317b838c6ce1\":{\"content\":{\"text\":\"Property at NYC\"},\"properties\":{\"backend\":{\"headingLevel\":\"h2\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"18\",\"font-family\":\"Georgia\",\"font-weight\":\"bold\"}}},\"class\":\"BuilderTitleElement\"},\"builderplaintextelement_1394063772_5317b99cab31e\":{\"content\":{\"text\":\"Orsino, the Duke of Illyria, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: \\\"If music be the food of love, play on.\\\" It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that Olivia plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.\"},\"properties\":[],\"class\":\"BuilderPlainTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1394061698_5317b182c1f19\":{\"content\":{\"builderimageelement_1394063806_5317b9be406a3\":{\"content\":{\"image\":2},\"properties\":[],\"class\":\"BuilderImageElement\"},\"buildertitleelement_1394063420_5317b83cb81a3\":{\"content\":{\"text\":\"Chalet in Bs. As.\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"18\",\"font-family\":\"Georgia\",\"font-weight\":\"bold\"}}},\"class\":\"BuilderTitleElement\"},\"builderplaintextelement_1394063737_5317b979ce2a3\":{\"content\":{\"text\":\"Orsino, the Duke of Illyria, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: \\\"If music be the food of love, play on.\\\" It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that Olivia plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.\"},\"properties\":[],\"class\":\"BuilderPlainTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1394063404_5317b82c72b5c\":{\"content\":{\"builderimageelement_1394063809_5317b9c1da156\":{\"content\":{\"image\":2},\"properties\":[],\"class\":\"BuilderImageElement\"},\"buildertitleelement_1394063425_5317b8410f24b\":{\"content\":{\"text\":\"Tiny Island\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"18\",\"font-family\":\"Georgia\",\"font-weight\":\"bold\"}}},\"class\":\"BuilderTitleElement\"},\"builderplaintextelement_1394063741_5317b97d68d8d\":{\"content\":{\"text\":\"Orsino, the Duke of Illyria, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: \\\"If music be the food of love, play on.\\\" It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that Olivia plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.\"},\"properties\":[],\"class\":\"BuilderPlainTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"3\"}},\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062652_5317b53c906f9\":{\"content\":{\"buildercolumnelement_1394062652_5317b53c90615\":{\"content\":{\"builderbuttonelement_1394063832_5317b9d8a797c\":{\"content\":[],\"properties\":{\"backend\":{\"text\":\"Click for more details\",\"sizeClass\":\"large-button\",\"width\":\"100%\",\"align\":\"center\"},\"frontend\":{\"href\":\"http:\\/\\/google.com\",\"target\":\"_blank\",\"inlineStyles\":{\"background-color\":\"#8224e3\",\"color\":\"#ffffff\",\"font-weight\":\"bold\",\"text-align\":\"center\",\"border-color\":\"#8224e3\",\"border-width\":\"1\",\"border-style\":\"solid\"}}},\"class\":\"BuilderButtonElement\"},\"builderdividerelement_1394062652_5317b53c901fc\":{\"content\":[],\"properties\":{\"frontend\":{\"inlineStyles\":{\"border-top-width\":\"1\",\"border-top-style\":\"dotted\",\"border-top-color\":\"#efefef\"}},\"backend\":{\"divider-padding\":\"10\"}},\"class\":\"BuilderDividerElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062641_5317b53112a36\":{\"content\":{\"buildercolumnelement_1394062641_5317b5311291a\":{\"content\":{\"builderfooterelement_1394062641_5317b5311226e\":{\"content\":{\"text\":\"[[GLOBAL^MARKETING^FOOTER^HTML]]\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"font-size\":\"11\",\"background-color\":\"#ebebeb\"}}},\"class\":\"BuilderFooterElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"}},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#ffffff\",\"color\":\"#545454\"}}},\"class\":\"BuilderCanvasElement\"}}}',NULL,5),(6,NULL,0,3,NULL,'3 Columns with Hero','3 Columns with Hero','en',NULL,NULL,'{\"baseTemplateId\":\"\",\"icon\":\"icon-template-1\",\"dom\":{\"canvas1\":{\"content\":{\"builderheaderimagetextelement_1393965594_53163a1a0eb53\":{\"content\":{\"buildercolumnelement_1393965594_53163a1a0ef48\":{\"content\":{\"builderimageelement_1393965594_53163a1a0ee52\":{\"content\":{\"image\":1},\"properties\":[],\"class\":\"BuilderImageElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1393965594_53163a1a145cc\":{\"content\":{\"builderheadertextelement_1393965594_53163a1a14515\":{\"content\":{\"text\":\"Acme Real Estate\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"color\":\"#ffffff\",\"font-weight\":\"bold\",\"text-align\":\"right\"}}},\"class\":\"BuilderHeaderTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"1:2\",\"header\":\"1\",\"border-negation\":{\"border-top\":\"none\",\"border-right\":\"none\",\"border-bottom\":\"none\",\"border-left\":\"none\"}},\"frontend\":{\"inlineStyles\":{\"background-color\":\"#282a76\"}}},\"class\":\"BuilderHeaderImageTextElement\"},\"builderrowelement_1394062546_5317b4d264a62\":{\"content\":{\"buildercolumnelement_1394062546_5317b4d26488b\":{\"content\":{\"buildertitleelement_1394062546_5317b4d263942\":{\"content\":{\"text\":\"New on our Downtown NYC locations\"},\"properties\":{\"backend\":{\"headingLevel\":\"h1\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"28\",\"font-weight\":\"bold\",\"line-height\":\"100\"}}},\"class\":\"BuilderTitleElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1394122137_53189d999cade\":{\"content\":{\"buildercolumnelement_1394122137_53189d999c769\":{\"content\":{\"builderimageelement_1394122137_53189d999b21b\":{\"content\":{\"image\":4},\"properties\":[],\"class\":\"BuilderImageElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"},\"builderrowelement_1393965668_53163a6448794\":{\"content\":{\"buildercolumnelement_1393965668_53163a644866d\":{\"content\":{\"builderimageelement_1394063801_5317b9b9eedc5\":{\"content\":{\"image\":2},\"properties\":[],\"class\":\"BuilderImageElement\"},\"buildertitleelement_1394063416_5317b838c6ce1\":{\"content\":{\"text\":\"Property at NYC\"},\"properties\":{\"backend\":{\"headingLevel\":\"h2\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"18\",\"font-family\":\"Georgia\",\"font-weight\":\"bold\"}}},\"class\":\"BuilderTitleElement\"},\"builderplaintextelement_1394063772_5317b99cab31e\":{\"content\":{\"text\":\"With its welcoming fireplace, wood-paneled ceiling, limestone floor, and luminous\\nview into a stunning courtyard, The Sterling Mason lobby imparts the intimate warmth of home.\"},\"properties\":{\"backend\":{\"border-negation\":{\"border-top\":\"none\",\"border-right\":\"none\",\"border-bottom\":\"none\",\"border-left\":\"none\"}}},\"class\":\"BuilderPlainTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1394061698_5317b182c1f19\":{\"content\":{\"builderimageelement_1394063806_5317b9be406a3\":{\"content\":{\"image\":2},\"properties\":[],\"class\":\"BuilderImageElement\"},\"buildertitleelement_1394063420_5317b83cb81a3\":{\"content\":{\"text\":\"Chalet in Bs. As.\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"18\",\"font-family\":\"Georgia\",\"font-weight\":\"bold\"}}},\"class\":\"BuilderTitleElement\"},\"builderplaintextelement_1394063737_5317b979ce2a3\":{\"content\":{\"text\":\"With its welcoming fireplace, wood-paneled ceiling, limestone floor, and luminous\\nview into a stunning courtyard, The Sterling Mason lobby imparts the intimate warmth of home.\"},\"properties\":{\"backend\":{\"border-negation\":{\"border-top\":\"none\",\"border-right\":\"none\",\"border-bottom\":\"none\",\"border-left\":\"none\"}}},\"class\":\"BuilderPlainTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"},\"buildercolumnelement_1394063404_5317b82c72b5c\":{\"content\":{\"builderimageelement_1394063809_5317b9c1da156\":{\"content\":{\"image\":2},\"properties\":[],\"class\":\"BuilderImageElement\"},\"buildertitleelement_1394063425_5317b8410f24b\":{\"content\":{\"text\":\"Luminus Loft\"},\"properties\":{\"backend\":{\"headingLevel\":\"h3\"},\"frontend\":{\"inlineStyles\":{\"color\":\"#323232\",\"font-size\":\"18\",\"font-family\":\"Georgia\",\"font-weight\":\"bold\"}}},\"class\":\"BuilderTitleElement\"},\"builderplaintextelement_1394063741_5317b97d68d8d\":{\"content\":{\"text\":\"With its welcoming fireplace, wood-paneled ceiling, limestone floor, and luminous\\nview into a stunning courtyard, The Sterling Mason lobby imparts the intimate warmth of home.\"},\"properties\":{\"backend\":{\"border-negation\":{\"border-top\":\"none\",\"border-right\":\"none\",\"border-bottom\":\"none\",\"border-left\":\"none\"}}},\"class\":\"BuilderPlainTextElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":{\"backend\":{\"configuration\":\"3\"}},\"class\":\"BuilderRowElement\"},\"builderrowelement_1394062641_5317b53112a36\":{\"content\":{\"buildercolumnelement_1394062641_5317b5311291a\":{\"content\":{\"buildersocialelement_1394121396_53189ab49a77c\":{\"content\":[],\"properties\":{\"backend\":{\"layout\":\"horizontal\",\"services\":{\"Facebook\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.facebook.com\\/\"},\"GooglePlus\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/gplus.con\"},\"Instagram\":{\"enabled\":\"1\",\"url\":\"http:\\/\\/www.instagram.com\\/\"}}}},\"class\":\"BuilderSocialElement\"},\"builderfooterelement_1394062641_5317b5311226e\":{\"content\":{\"text\":\"[[GLOBAL^MARKETING^FOOTER^HTML]]\"},\"properties\":{\"frontend\":{\"inlineStyles\":{\"font-size\":\"11\",\"background-color\":\"#ebebeb\"}}},\"class\":\"BuilderFooterElement\"}},\"properties\":[],\"class\":\"BuilderColumnElement\"}},\"properties\":[],\"class\":\"BuilderRowElement\"}},\"properties\":{\"frontend\":{\"inlineStyles\":{\"background-color\":\"#fefefe\",\"color\":\"#545454\",\"border-color\":\"#284b7d\",\"border-width\":\"10\",\"border-style\":\"solid\"}},\"backend\":{\"border-negation\":{\"border-top\":\"none\",\"border-right\":\"none\",\"border-bottom\":\"none\",\"border-left\":\"none\"}}},\"class\":\"BuilderCanvasElement\"}}}',NULL,6);
/*!40000 ALTER TABLE `emailtemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailtemplate_read`
--

DROP TABLE IF EXISTS `emailtemplate_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailtemplate_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `emailtemplate_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailtemplate_read`
--

LOCK TABLES `emailtemplate_read` WRITE;
/*!40000 ALTER TABLE `emailtemplate_read` DISABLE KEYS */;
INSERT INTO `emailtemplate_read` VALUES (1,1,'G2',1),(2,2,'G2',1),(3,3,'G2',1),(4,4,'G2',1),(5,5,'G2',1),(6,6,'G2',1);
/*!40000 ALTER TABLE `emailtemplate_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exportfilemodel`
--

DROP TABLE IF EXISTS `exportfilemodel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exportfilemodel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filemodel_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exportfilemodel`
--

LOCK TABLES `exportfilemodel` WRITE;
/*!40000 ALTER TABLE `exportfilemodel` DISABLE KEYS */;
/*!40000 ALTER TABLE `exportfilemodel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exportitem`
--

DROP TABLE IF EXISTS `exportitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exportitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `iscompleted` tinyint(1) unsigned DEFAULT NULL,
  `exportfiletype` text COLLATE utf8_unicode_ci,
  `exportfilename` text COLLATE utf8_unicode_ci,
  `modelclassname` text COLLATE utf8_unicode_ci,
  `processoffset` int(11) DEFAULT NULL,
  `serializeddata` longtext COLLATE utf8_unicode_ci,
  `isjobrunning` tinyint(1) unsigned DEFAULT NULL,
  `cancelexport` tinyint(1) unsigned DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `exportfilemodel_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exportitem`
--

LOCK TABLES `exportitem` WRITE;
/*!40000 ALTER TABLE `exportitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `exportitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exportitem_read`
--

DROP TABLE IF EXISTS `exportitem_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exportitem_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `exportitem_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exportitem_read`
--

LOCK TABLES `exportitem_read` WRITE;
/*!40000 ALTER TABLE `exportitem_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `exportitem_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filecontent`
--

DROP TABLE IF EXISTS `filecontent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filecontent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filecontent`
--

LOCK TABLES `filecontent` WRITE;
/*!40000 ALTER TABLE `filecontent` DISABLE KEYS */;
INSERT INTO `filecontent` VALUES (1,'GIF87a»\02\0„\0\0ÃÃÃñññæææ£££úúú∑∑∑≈≈≈™™™±±±\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0,\0\0\0\0»\02\0\0˛»I´Ω8ÎÕªˇ`(édiûh™ÆlÎæp,œtmﬂxÆÔ|Ôˇ¿†pH,\Zè»§r…l:ü–®tJ≠ZØÿ¨vÀÌzø‡∞xL.õœËtT0\nv``à∑È§Ä^ó»ÒjCz}ÜmâsnyìéãÅC\0Éú\0õ}¢ù•#êß§áôBà´muí¥\0∂™åπª∞D¢†\0Üñs«û…z∂nï “Ã≈¬@—◊´\0´‡‡õì∑{œ„æ‹>éºÏÓ‚ÙÓÖ€tËÌﬁ˝¿4!`8{ºq®Wû¿¢‘ô∞Ï6d˚\ZÈô®aY≈fws8Í3:ìÚXÈÈtÅ‰2î!u≥–*ÄöØ“\r(‘rî≈õ£l∆º°h“$Kä¿]\"WÚ–ø))Œ1Ñ(©√°4‘Èq\'@G]^ße€c¡¿\"ñ~¬b]À∂≠€∑p„ ùK∑Æ›ªxÛÍ›À∑ØﬂøÄL∏∞a∏\0\0;'),(2,'GIF87a»\0»\0„\0\0ÃÃÃñññ±±±úúú£££™™™∑∑∑æææ≈≈≈\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0,\0\0\0\0»\0»\0\0˛»I´Ω8ÎÕªˇ`(édiûh™ÆlÎæp,œtmﬂxÆÔ|Ôˇ¿†pH,\Zè»§r…l:ü–®tJ≠ZØÿ¨vÀÌzø‡∞xL.õœË¥zÕnªﬂ∏|NØ€Ô¯º~œÔ˚ˇÄÅÇÉÑÖÜáàâäãåçéèêëíìîïñóòôöõúùûü†°¢£§•¶ß®©™´¨≠ÆØ∞±≤≥¥µ∂∑∏π∫ªºΩæø¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—ô’◊Ÿ€ÿ*‘÷ﬂ⁄‚›ÊÖÎÎÏÎ„\0ÒÛ%ÍÒÓıÏÛ˝ÚXÔÄÌ\0Xá‡`¬Ö\Zö\0[Aá‹Aîà0£BÜ˛É¥Ìêﬁ∫	%Î°¨xB‰ o*%§<)ìÂ°éﬂ‘ò†SBOüv^`W3\0ú@Ö¯˘siPD˘$VπpÄÑ©™b`gÄ¢—\rQØzî†\0V≤÷’Y“‰◊∂1/,,0ó√⁄ó¸h¬•)pùUºbÓ˝z°#H\rˇ÷\'ÿ¶€B¸J\\ú˜≠„∏rjàlm2…ÀzAT-ÄA\ng?˛M]EJüû¿:≠Ÿ±™	’É]°)”ßI5îV|a∑ﬂ¿ë+‘q‰;æ3	∑ÕToÕãC∑w˙üzû\'dﬂàQ„·’∫⁄∂\0æ√√„œ*o4@«n˝m%˙y4Ì˙|Â7›}˜¸A_<Ñ√MÇﬁd¿Œ>°†\nX°4f®·ÜvË·á Ü(‚à$ñh‚â(¶®‚ä,∂Ë‚ã0∆(„å4÷h„ç8Ê®„é<ˆË„è@)‰êDi‰ëH&©‰íL6È‰ìPF)ÂîTViÂïXf©Âñ\\vÈÂó`Ü)Êòd:\0;'),(3,'GIF87aD¥\0„\0\0ÃÃÃñññ£££úúú±±±∑∑∑æææ™™™≈≈≈\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0,\0\0\0\0D¥\0\0˛»I´Ω8ÎÕªˇ`(édiûh™ÆlÎæp,œtmﬂxÆÔ|Ôˇ¿†pH,\Zè»§r…l:ü–®tJ≠ZØÿ¨vÀÌzø‡∞xL.õœË¥zÕnªﬂ∏|NØ€Ô¯º~œÔ˚ˇÄÅÇÉÑÖÜáàâäãåçéèêëíìîïñóòôöõúùûü†°¢£§•¶ß®©™´¨≠ÆØ∞±≤≥¥µ∂∑∏π∫ªºΩæø¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷◊ÿŸ⁄€‹›ﬁﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ˜¯˘˙˚¸˝˛ˇ\0\nH∞†¡É*\\»∞°√á#JúH±¢≈ã3j‹»±£«è˛ CäI≤§…ì(S™\\…≤eâ\00c ú)S¿0@\0$\nËÑ9‡@/jÜ¿9¥Áœ†Cy\ZuIÜ¶Uö: 8p5Ä\0 \nt\043mzÿ⁄ıkÿ±e©~ÈJ7Î;ÈàªÅ´^Xí“TÀØﬁΩ¸“,◊Àa´v3xì/≈zèR1lï∞Ü…î-_¿LWs„-îgFæÄ Ô„`3àM;ä‡´û1¥N\r[√l µOc¡J†∏Ò„≈MèÓú”ÍÅªU8‡:fn&ú€r \r”\'ZöœuW\':}ºW·Zõ@`’Ùoô¡)‹∂>!∫L—IÊk◊¿û¶{´ÒM˛†ﬂyÿ~ËE°^	Ü7wåY0^oÄ¶“u]\rV\0!g!}	Z± 	ﬂ¡WAVLNêbL\Z1bLºH`3¬ã$∫HSåÙ@ç%:q‚^–§‹®£rI`ñ£K:©öñ2≠ˆ MR˙ó$YÜP&\0F^p&\0k\Z±”ï¥âeZ§i¡öréôDû`≈•L\0	#=zôÑT≤Iß~JHS†C∫•û\n.ä‚£wZ:¡îóÅ)ÇëP©„é)‰iger∫‹LHRäƒÇàP\0Ç(j§“Ñ†é¥fpïÉpy⁄*‰LD0‡Æ∫˛∫˙ƒ`òw@´ı5Z†yFâÅ™ t\\Wƒj öµ\0dx$îû^¿≠≥K§S¢(\0Pô,öò^pc\0˜ˆy’WÖ€bÚ“koù˘Z∞oøÏ·ÓªÄäàìé;∞Ïáo¿+πÒc§≈2a‹W∂›c&O02§ãÄS¥û\n SqΩä‡1\0¬Çó¡À+˙|≤)Û‘¡Ä»j\04™‚Ç∞0\0Îv‹4¡)]±öS=ƒUÄ≈ñò\Z ç+÷gZv=⁄î6;ãù4rÓ¨ıÕ·hôº\ZÙÏ‹Yœy∂”∫6˚Ç«zmvêd#>∑@~ÿbÄ¥O¬ﬁwﬂ˛â©3€òK0˘tVYéπ‹ãa,Ã3≈µÔπLˇgÁÿÃjy Æ:Äáw«(Ï•#asÀFﬁ∫9KS@∫‰]µ,µÎ2ÇΩ©·._ù;©ΩCQ¶Õ\Z_ÒBOÇÏ>  \'ˆœNºÙ¸^˝Kü~¨LDr_Æ˜$ Õ∞\nr∫OÅéÒ£_ÌÎ{Çy,†=í)ÓT\nõ	Vó3»©Ä®;`¬*¥\0BÅê–¥•/Áô\0T![û‚*†AÈq0Å3ÅóìÄAE1oÄÚú¢6ÇQAåv£Î$√˜≈DÖ+<¬“⁄ÑA\Z+]&0éƒóCÊ—É8a{ÄÄhQki%ÏR˛–ﬁF¿	÷038dû»¸óAÈŸ¨_-ú\"æ∆Ø›ï¨ã3ÒU”ƒª\råG?Ü°Óx‰≈◊ùçébT£lÜ§3∆ä‡∫ÃGÅ\n2hK=∫ü	ÚáH®)àå¨–%yÉÒ(oY(¸õëD£H!qÜ‘µ^Çπ}eØáâ‹§9yÉ%+CycÍ|HæuID«[§ä\\Âù OÊ±åérâ`÷ëñ-ê]åŒ≤…X pu*ƒ€µ*˘ıarò!/U&l\"œRÊ\"4eÄ¥¢Äwœ;˚fÆî\'òÂ,Œ(5ôúï†\0»!Nr‚y±y2Øû˜|Ê:[†ƒ≈|ÊaΩ≤·U˛\ZËKr}¨èsVGóà∫ã¢uÅπí«âr3äîQßø⁄3Œò∞25≤IçJyñ“êÚ`§Ù€÷cf˙ÕÆHr•}	ÊõùfÆj6ÌA·‡‰Åzµeï-%\nHÂò”ûΩî2wëòW†∫?∏$ıUåäTx™/Í‡Ë\0SuQTäB÷\rÄ\n?≤!\Z\nô¿¨DA+TÚ¬÷Ø˙ıØÄ\r¨`Kÿ¬\Zˆ∞àM¨bÀÿ∆:ˆ±êç¨d\'KŸ Zˆ≤òÕ¨f7ÀŸŒzˆ≥†\r≠hGK⁄“öˆ¥®M≠jWÀ⁄÷∫ˆµ∞ç≠lgK€⁄⁄ˆ∂∏Õ≠nwÀ€ﬁ˙ˆ∑¿\rÆpáK‹‚\Z˜∏»MÆró&À‹Ê:˜π–çÆtßK›ÍZ˜∫ÿÕÆv∑À›Óz˜ª‡\rØx«K^èD\0\0;'),(4,'âPNG\r\n\Z\n\0\0\0\rIHDR\0\0D\0\0\0¥\0\0\0B3√X\0\0\0PLTE\"\Z! 0=\0&&&05)<74,<X\"I8F9]87Ihx;DB=XQ?cvGDBOWCPNNYVL[YZEXyPqTa\\J`_\\keUjuYlBeefljhnotzufyws8x«KeàWqî_}Ø_}≤vyÄb|£jÖpâsåxìkâ rá*v±\0]íyríBmá´dÑºwä´ué≥~ê≠{íµTü‚kã¬jö’ré¡uí«{ú÷†⁄ÅFíW™s8áuMé}XîkL⁄aaáöÇô)Ñô2ãÆ\nÖ°ï∂ä¶\"ã£1úª)ô≥>°ø2Ω∂:çüCäÖwãôuïÖ`ûènè°Dñ≠BòßW∏ÖHØ∏Vª≠N£¿8™≈HÆ«T≤ YµÕ`ﬁ†4‰ìÊî	ÍñÌòÚö\Z‡ù)˘ù$Ê®=∆æA“¥A‘°fŸ∑{ÂΩMÁ≤XÁºrÔÀX¸¬u˘ÿbˇ·hÄÖåìçÅôïèïóôÖò∏é°øë¢Ω¨üÉ†ùó•†ï•πãÆ≥ò≥ßé∫ØòΩ≥ù•••¶≠∏Ø∞∞∫∂Æ∫∏µÉú∆ã§Àä¶”ò™∆î≠“ó±Ÿà™Áãµ˝êÆ‰ô∑Áîª˝ùºÚôæ˝®±¡®ª◊±ºÕ£Ω„Ωƒëù¡˝∂¬◊∞¿⁄∫ƒ”∏≈⁄ª÷ §¡Ó¨≈È•ƒÛ£≈˝¶»˝®∆Ù™»ˆ´À˝¥«‰∂Ã∞Œ˝≤–˛ø”Û∫‘˝¿Ω¥⁄¡ï…¿Æƒ¿∏À≈∏ ﬂ™Ã—≤Œ‹≤–…Ω“÷º◊ﬁº“·∑Û É˝œíË—ßË›Ω˜⁄™ˇ·ôˇ≥¿¿¿ «¿œÀ√√Ã€‘Õ¬‘—…ÿ—∆⁄’À›ÿŒ÷’”›Ÿ—ﬁ‹Ÿ¬œ·∆“ÂÀ’‚»’ÎÕŸÍƒ÷Û¿◊¸√€ˇÀ€ÚÀ›˙‘€Ê÷ﬁÎ”ﬂÚ⁄‚√ﬂÎ ”‡—Ã‡ˇ⁄·Î”‡ı“„˝⁄‰Ù‹Á¯›Ë˜ŸËˇ‡ﬁÃ·‹”‚ﬁÿÂ‡◊Ê‚⁄ÂÔ‘Ì„–Ë‰€˛Á…ÊÊÊÈÊ·ÌÍ„ÂÎˆÌÒˆÈÒ˛ÓÍÛÒÓˇ¯ÊıÙÛÙˆ˙˘¯˜˛˛˛ˇˇˇÌ»Í\n\0\0\0bKGDˇ•Ú≈\0\0 \0IDATx⁄ºΩ[lIz®©ßdY€Ç ´!àh¢[RüÈÒÏ—¨\0ÕÏÃ—≠ÁbÔ¯ÿÇe[^{é[¢n{§ñ∫ßEpÇ^\\⁄&¶ó≤ |†¿Cµ%Ç¡K’í©Æá‹åDıDÇ\0|‡FUREf.òŸâLÏˇˇëôU,^§3ﬁòi™XU$+#ø¯ÔÒ«æ)^.[L≥√–a\Z/≥©âRπ\\ﬁ∞∏« uµÜÆ;a⁄öQ∆°Ö\Z˛√4≥,F˙·\0éa1û>M\'«31FjçGiıHº©‚”Oü‚Ø{2A#«≈ﬂ‚ÏÕ‹‹‹Ω_˘Âóø˛Õ·ìW_≈\'4ô2zÆƒX»≈´>£.+ú—X!c\\∏6GÔ¬∞‡+¸gXaX‰t≈SìÃ¡9–Ë{ÊÄﬁl7Ï\0úô¶Æπ°AQ”ﬁºy#gôï+GI\\è=\\¶´de~„˙ÕõWËÌ√—î¶k1A0“ÚórÕ¬´287‰3åYÚ%Ô9~`Æ^)GØ–ÍV¢ü)ïíüV|√Ô6›ÑqÌFŒ⁄WãÄà[@nuHL—[◊nJàh:i?L0Ù6uçÏ—pQ.∫	Ã0D˜Ä°7Ò5Úí∏)e…\n\r›Û55å3õ>;M-g!Lêë≈‚Ëe~µ¡ô/¿˚\r€üÑœ¿`Ç–„\Z+ÜéØà‡m<K§Y\0ëijÃãÊÕ—aäƒÔ,ÒrÂêwŸå¯kﬁ|ÂÊÕÎ7ÌKjNünëúÒÅtY†¬`•xUa,ÒD\rgï≤\0åsKÒUR<—[ÒY|`%?±eî8Á⁄\rb®Èº_-N≤\Z”\\¯AÇñoËÂ-|ò!Âí£q&ncä’ÅJà“{Ö(≥#DrùI9ƒÂ_„%”d¸3`ËÀœg\0¢ÈÂÂe.d\rMÅV®â˜sILIÃ≤¡Ëvõ%ú&-4òÏêÉ4ñ±XhÄåÇâFYç,¢\\	\'ÅƒëÔÄx£?ä_‡möø2·V± öVükzíôx»;hâ_!—ƒtπ¸‚*,ıª¯·€»ˆ-ÇË)\\^#|KΩ¨`¶DO»óOÈN\0éQ!îbq%û√ØúV\'Á¶…aIïJ÷ã◊ê°´wÒï}í/ﬂ\",∏tÍBﬂ9»8Bt¯˝˜=Î¿…C@QÛ>Ã◊â#ﬂ7-‡Wì∫\\ﬁ¢g;@4Ù®¢öÎå&ó—Z¥¬‡Ú_|NΩòõõ¢©Òà≤Ô≤eê∫Åw—˚I$ïW\n∏\nÜk$îHPqIëd±$H‚ÜÊì H≤WàKà£ Ã\ZL¡°{@\Zg$àLæ±aiÜ&ƒëŒ∑B\n«êkPê;M◊	Ô|´˝Ís|*≥Gm6∞D‚ƒ¬èèŸ¡É\"H\\è=ΩÇ∆bBüKæL^é_1N3;~¬H∆k∑¡ê±û7]ˇ…ß7Ø_πCoÿß89y %,u:≥|∞#Ä(ˇ>º°ª+<y2Ù≈âé@†gÛyçÈf<)KKÖLfmV¢˛ù íS4DÇ®$d*ÿoñÈ8Œ<j≥/gﬂ8Œ+ú¸eí	˙‘TÔg∫K6ä≤°(T/ÖÊÇ®1±Q(¡WZÖ:R‰Í∫•n*\'˝KÂìÎÄ ö◊Ô–5Å•¡Oè\Z˝‡0b8ò0\"cqƒ™µãıÕ¢EM¡£ªh—m:∞7AîQÚ>ä˜0Ù≈üã¨R\\xE+ Aæ∏ã´R§^8\\ÃO~Úì€œüﬂ˛	é€∑o‡?◊õûóDƒI¢G\'õëXx0·%ÄË‰˛√˚æjÖu,Ç(“Í0%0L”4$	T√„≥4√á[Z,d“{á®è©Ÿ(Å…Îxû∑˛ı◊_˘r^Ûﬂ,Ø!DÜò™‹ƒTI!g´§IãÅB(–,0ôJÃÇØ$`òÅÖh6±|Ò=®π–\Z◊BA∂‘Û ÁBn°khd@X¬¨¶øL·ØÄﬂîGö^ÅèøKò’ÀA¡Ææ~ü≥ˆ	\"¸\0◊m`à3?‰dEÊ5P$\"˘≥∂%zﬂŸP∫†ßj<g®8ÉÔã89Ñ2È–!è8±0—&	‘\rˇºﬂåÑEÒ»ZóÕ*ôöÆe≥˘≥_Ö´´·Wgc±¥Gà\rU@TséûD˜èÆﬁd^{*’ÿÿ∫˛ÚÎ Âw∂Ø-OÉoÈ8e˛c9SÚ!\r˝)	”|eÃÅ„¢àÇU´…k3Ñ\n\nx÷P7Ãj¿ÉÎVÄfµ°Ó8h7\'–å¨·\nq§(è,Sj)”⁄0‰]Hä#ﬂÜeï⁄ç≥\ZÃÅ]›D\Zcì®ZÅv4yXƒè¢9@m)6ØÒU¶ñ°J\n∞H(·{ÈÌÍ÷\Z∞™JñêDíÄ˛yøõ8·z4l˜˚aæ.ÏBú⁄2´’iÙy —**i%ú◊≥¿ˇœ2S}ûﬂDjé¢Ë/ó¿ÉtΩé÷Øø˘ÊÎu∏«ÌkkkØ\\îN4x‰ “Ä\nãE∏n].?°∫–¨FÿNSÚn2˘U\0ÜAOD˛}`E⁄åL 0´ãΩçƒëA öë†‚,ˆ$+ƒëç‚à	πX—+%à wØ)tè\r,KŸÿH†J”ÖÛ®lgCô◊`;\n\Z|°§DOd_+ŸÙA≠°¯ﬂß8AàNvá˘ì¬ãGÁåº≥C\'˜Á≠}\'Ov¡cíD¿ê°3∆+ £h∏Pgåû=;˙‡Ï‡`ú<”‹´$äù≥ù!JHeB«à÷€[/z«√Œ∆TcŒgççÌÅõJ•\\øΩÒR6¥â,_„öÇ‚Z°óf.t‚ê6°\\åíŒu∑ÀëèæÿÓz$APõi]q˙xÁÊÜM¨2ö-V%é≤,Gj)‚˚sË!‡°6ªA/ßw—fä°⁄„®à¥\0ùÍ*≠{[â–ë˙l•J°d≤ÑPÇœÖÃ¸ÉŒ˛<˙°ÄàG\'€ÚPû8—|CBÇwÔÂÛyñtÒ1R`òá!$Z—fn>8K&Ã–‡‡`>?88î©`®DœvÑHŒ—î$KËÍºéˆØøno]˜è{ù¡x o4ºoÌÙ::Àùûñ\nôî‹úD∫‹ ¥5tì)Ñ(≥Ï¥%ú8∞)#È\0¬≈Q.ï!˝{›sAΩ≤–∑°%dÆÀÑˆ¬0C®âuonlÿ6ë∆≈\rAy∏EYUëG‡•“Ú4¯˜‰‡7›√gwâ≈ÇóŸ2#«L∏¯óMRiJ;—*Q∫-6ïTPR‡v¸äaòÄ,?Ï]\\\\\r?\n6W´!\n)\nƒB™£∞,\'Ô¨V∞1\n„Å%$PBüZL∆ŸØV77Wø:õ∏‰åDi0≥CúËŸ– BüE9\\°¢vÄ(\0à¬„ﬁqB<•y^‡w§\ZSÆrË•≤‚ö¨H1l?3‡ÃÅir\rdõ[∆/ŒË(!4òÔ—˜nL1ﬁåVi/b \ntêF.G#€Pf5Ω€P¥hï‚»–™¬◊âooÅovı> ÏMõ\rò¬j) ‘_∂JF9Ú“∏¢ÜD⁄=+¶ã≤ºK*|ÖÔ¯‡Éﬁ°9%x¸√P@Dw£¥Ø6\'\"]W;úù`“o!±§IItveuuıÏŸ-ó£¥Mƒ˙Ì br∂ΩvëáŸŒ≈∏Â˘Aj‹±ùÓîÕR∂∞0:-ßïÅÎ⁄∫úbÚŸ\0\"¿¡; w5œ)ªeæ‚‰\ZW—B‡œ§∞VP[°B1…ôR`11^tå,¯fˆF§Ì¡Öï¡·*q$¬	Ö&o~π¸Ü¬’Ù›”£D± z\nÆNx;cK™4Æ)üÑîY)îXÙ\nKÄ&\\9“_A/∞¥˘¡}¡f’[	Á\0faõ¥¥‰Eë#«åíäçGŒYÏùJàü:{∂ÔloÓI∫Vò(Å“é=´5IË·OQ.≠jÒßΩV°Œ\\Ä(ïmm:Z≥≠é÷®•≤c)≠50pV≤(ƒ$FY†®(Ø€üûÙB€jMu8†]«aÌæÎX$òäÃÒ ·1#ÙñóóAL¡@Ò£πxcB?†d#≥öìmÑ∫\n|È§/ã#>9ôGÆ°%åki…ÚÚLÜ´ﬂJõƒœ*±´‡()/ÕJ8Ù˝âp\"°˘o,÷q*ú¯|∞öÅ/+}`®/8<©õ∞o\\gñ´ÜÁÄıóâkyQJ.vƒ¨7M¥ßÕËπx•Eb#÷√ÉO´Ø92âûe2;°¥-DÉ$âñ1˝ KÚœ9œü˝ıÛÁÎN{¿åŒ1«∆;ôÎÎz‡uè9ùÈ%Õ˜4îö‰ßq#‰∂4∞1∆K|˚íÆûw3ﬂÁŒÖ/~êMâ{Ìπ° æÊ—Ô•‹+ó…å%π2íçÆ\Z¸\0K¬A§â;:qbr;qd(ñnD⁄l7_14 =™b†≈nñ%£á§“(ˇôÃ±ËQ˛Ñ¯xV$«\"∑\0å¢ŸA·?8ˇ¡ˇ?àÑ»>46ôñ’πÂ)¡Ì{ñgóú§¨q\\◊Qø∏2	ú⁄¨T;Jôï4aGîÀîÙ»LLmØVÍ,F)≥≈√VkíDòhz\"«J—Zy3ˇıÀôø˝Ÿ«0~ˆK«s\r◊9_`=Ät7</i¡¬:¥–c ﬁ1*BaFc s≤≠\0Máwºµ±’g© ’ôjtÉ±∆KcA„≈é∞£±Q.˘÷ÒWŒ≈ Ôq]4ÅO3√RÆ\ZÁo‰m‚((VÍ*È∑ÂDH∫R1§Që4´Ø_£«;õD± íQ\"›∑\r!˜T$ìr=±JSµëË·LFN7ì\'ÇJ*¶ƒıÖ´ãC—2¨£ª/J{c∞ÊÊùÆ	∞±£kr<√q„ƒ∑q)⁄\0©•¯µ\r∫aKQa‚…Æ%Qò•›!“À”‡ƒ\Zë¥û˘ıóüˇ˚èˇ¸Wˇø˙Ûè?˙WdûÉ‰ˆ@uÅ\n3Ñâ°´¿–éJ«B€–ä°fö¶Æõ◊—·ÅñÚéøZk‰—∏◊>t¿¯xz*Ù/ÖÌ˙ÿ•N≠58ÓxçÜ⁄ò{≈<?N<á_Üıjùq‰$uï0xå©	9*≈Q1Jû‡¿ ë–fÂΩi≥Æñï`ÜÒÑHQ*Õêà–|H˙\' }pëzQ9kâüÍêﬁ=9˚ıq,bˆUãpîJZñÎ‡‚¥:éeïméÄÎßtœrƒx>§◊n∏Æ—Óq«∂Ä^x+◊RhMHà2OÜ∂Àæ÷ÙŒ2]ÑI•Ì Bˇ<|4l¡îü¯ﬁØˇ‚£ø˙9˛ˆ£øÅ´[\Z÷úù•∫T`\"\"-Ñ´ö\"ÉF‡øc…F‡DÂ5`fm-≈\"ÕÎË∆S©„>@‘ŸÈ˚)¶µ∑v∑vå«=/•π`Ÿ*HÑÊ0eD—_»\nqÎ*Y\0BUFU8kã2koÆ°6ª3µ\\⁄≈$™0´Èœ⁄±A´Gj\re0| 0ƒ¯)Ÿ∏“d¬véÚd∫íA<YtƒÔ\\MU\ZãKiè Ú1—.\0·ç•|‘˝æáB)H1üºê˚kk””k0â.ﬂÒa¿{<æs]∞!\nâä¥ΩC$r¯ewgjÆ4·ær™]Pˆ·g˘—Ø˛s4~ı—/-{c÷\\ÉUä&Ú˜\"y/f\0)[3LÃ≈\ZnåW)Ä»ëπù¸PÂ©p¨3t\Z\'’Ë5¶,Ä»ÖóôÎÇﬁ(I≥\Z+ﬁÇHHÁ»:‚HV–PDëBßïŒö-3khV_øA!Ö=j≥ÇDA1\"\'71•+Ò¿ÈqñQïehVÑìQéÚ˚÷PÆ©S1Cè,ñ∞Ñ$J∏XÚ!Ar,ÇNú=\rÊ&÷®‹òJΩZCà<|ö5Çu\0Ôkl˜X kÌ6“ª’ÅÏ\0ë–fÈL%IOcZ¶§Ò⁄\"sˇÂ£ø˝œâÒ∑çôB;ìN+W`ÉY{Qâ&øá\r@ÙbúÈòLuTBTæ§u\01óx˘íﬁ	\Z≠±’ká©9Ó9r¢§—$≥⁄õ\\.\'á∞∏•uÑp$Jî»Qâ‹Jq‰Qaê®È.Â?“{rïYç⁄ÃêM!ÓÃ≤ö5¸<p—\\cfd‚*˚öTü*CseÒë!¯<m\"R˝√^ï<ˇÓ+Ò(ã∂≤íDçö÷⁄\Zt:~QyÙù”ôu\0¢÷éé÷T–®˚cﬁE«Oô,kŸ›≠4∂v¨qk_êÑ±Óÿ√ü‚Dñ∏Ç≥hº¯È/ûﬂˇ÷∑~˜ª¯‡?ók\\GùkŒ¿¬W•±ô®DSwŸ∂4/tI€Ÿû;÷>ÈxÌkÓòmè˘cñßÈÅ÷açyav,∞⁄«› 0uèg}ø√q∆,ª≤§ÕjP∑IåTZSä#èìÈlE∂•%√„ºZÖ.ª7Ì\n’7Óí¡Ø6´|*K…ua¡SJåÃ6ÚŒ`}Eqır≤ç)Y©äé‘+Ç°k∑∏4}\"+Ø$!*…ßòA\n}xGª‘—1Üæm™\Z\"ﬁòÍt÷¶ùTß¶u¶Ç„æØßX ˚Å]º¶ufwàj≈Ü’,ë\"iHÂ•≠óÍ˛Oˇ˛Ô„˜˜Øı«ﬂ˙kxÙ˜ﬂié<:çÉú±YY≠;ïºó”ÁYììF rË‚µWkke◊\Z⁄Y\\¨^Ñ.H60Z,zùUTó4f\'∞àcYπcâ)éP∆Xq|DæÉû∞8◊±–]ç/¿¨æ˛)i≥•=j≥»¨ €\0#(ßrπ%&ãÑÑ∑èYö($4J[ù~·ƒEEGœCüF∫:^Ùz§Œ@∑GŒ!uÊõó∂J\"Ó,ß∫#uÊ_t›lk jg©éVœMl∑nvbh\'HÍBàT∞:·4ˇÏß„oøı«ıwÒÀﬂˇÏØ…àÖ®”ºDI%ÔÂ–}í*≈«&Tô@\"VÁ®Ïæ ÍkZâXê&¢’¢ZM‹4ÆI%G¬t6≤±≥¶™wJ\"ŒÎ8™…¶ —\" ≥gmF4\ZëæVˆªî7™\nÜc˘=Ê¨Å´@ÂBbú¨í–∆ƒã¶Îï%™Wß}\\^\0Ü‚‘+hΩy„î–\"\0EkEà ó≤ùÄT´÷8Ê,;“∞noodAcÁÿ% KeΩÙnÇ®vî∫+≥-D(ØáA≥…ïVQÊ˛SÇËØæıgØ∆œ~.ÊOöâ†”\\ÄAQ÷KdÕq„∆¨\\\n\Z°1Cd(≠ØÖa‹√®‰M œ—©®Çî®VõP’—’QÂën	”9Vl<™”ÖÊÁ)H‰\Z„ì•∑–f&ñ{:n¥\0ñúL”P§µTéÁÙß+“3q›<TEÿqÄ»`7™RófQ»º≥|>õ’@A≤ÿ6*ÉJFà<,◊Î`c¡XŸ◊;åé\0æ”Añ{cñÎÇπ‡çuæÔtv\Z>ºïwz#Ô≤[h«BU’ÿüñM≤∫tÓßø@p˛ ˙„?¸C\"Èøk\\¡ÇU†NìÖ^†YfÔ“¬IDõ>#>°≤sFóÃºÓ≈)-EEQI#x2¶†,äOV2T¸∞f\Zq‰QÜ∂úPlfÙf–| $∞.ˆ⁄ò<—µ°∑–fXí\Z€∏Ë≤∏\nSŒ	7π™$G¯a0€Jê©»RTtƒ®$ˇ⁄çC%ÚÃ0w!÷¡>”dX€\n#´Å»£àÊ¡§H\Zû`}}GD¸eÕ%-ÒÅkâ7∫√Ô—NÇHNS∫ƒÚ\ZV(0#ö¸B	à§:˚π⁄4Å≥ÄVu‰ßqôºèJ\nq˜Oñ&ù1ì]Dbÿß,åΩ9¯y,o$	Ç…ˇÄ|3ëœgû=Y…Pï:3BY|õ’mJ⁄vâáo.ô’ﬂ(gmpp…WK	ºl‰”ã\rJFT%ü¬†⁄Xπ¬p◊é‰H3ı((J–U5“-¡–dIÏ˜ xby# í◊X…íÒÊçy$R≠\"Îœ…∑V5D±‡îèñvãçÏ6∂ÖùSÀkÜLïg~&!˙.|˝˝ÔDÖQ5±`p7\"X’L˙i¯ç(à0M⁄\r\'\'-ﬁ+<`ì¢eÊäA~$rôh÷†»\'=Ê1iV£:Úê¿©i\"$kWBÕ`4˝•í°qOƒ #ôëÿÊPÜüΩ”tÛ÷≠ªq»1ÚÉ∞(˚za<ﬁŸ7ãﬂ2ç®6\\¢]ÉE»´0¢ÈµhÎ†rÄ\'~∑ó]oöúb¶∫Ì…¬Uú(±^$Kö&cıâÙk9ÒH∫qësXV	·¬ªôD{Åhë˛Ä¡ÚY‹—%‡æ˜ó˝≈ﬂ¡¯√ﬂ˝≥ø˚≥ﬂ˝.<˙ãèY.E”°π¬™&?-Kﬂ–\",ôB#i àTûîUÖL°ò6Ñ8√™ÿ§7Á`éK&·BjàÖ¡A@[‹/OøzÂ¨≠ykA‡aä÷¶˚ ˆj1êh(√PX”ﬂû…ıÏ‚†‰# ÛOØﬂöùyoY#‰˙{zÖ«=Ω√;˙f$d»7K‰©bÈÕπRfVT˚ö‘k4…*!¿∏pÓØ<ßLÒ0ï–ƒ«x%Drî8.yR	[ÛıâGX∫Bi}ÆvQè˛CÑÆ*j5SÃ güˇÙG}Î[ﬂ˙}|ãüˇﬂÑ∏îî°ö\'–iæ‹˜Aj´Aã˛‰§ìÄwúìSb£^Ycë(“LLIÒl(STe∞mœa‹Ysöªºcå’›Õ.÷qX1òC÷î◊’Â©Y$q$6∆¢ï:¿$¬ºÆÊ77nﬁù}œÂIü?ÙÛY¢ ‹3\\\"ï¿O¯fF§F)2˘0™¥4åí⁄ö‡á¶31CWÔLNNj,14]”∆AZéÁáaMàH˛∞<ÓDÊ4@å%∞é!*IÊòéfU@Ù˛Œ9|9OKB‚—ŒR¬àóÁ>ˇÚ/?˛ªäÒqõ„¥ñ0V≠Ú#Ãà6{Äö√´—}{‹	πûHÇ‰rI87”k¬‡$s–Aìi\r%ëò,ßæŸ.ÔoY[€ã…ﬂüÕÓ‡ùdA:Æ1(	1+‘“‚πÍáó=“U‡f;ﬁ˛ññ√->º\ZX˙¸Ω/ffˆ√o∞ãN$é¥h«≈„ﬁ≠~FnÉ√~Dn7åîÀ#y™\"€!‹‹bH˚»J·‹7›≥∂J”˚|v¶◊ñD≤ø\0f-À¬^Ê&Ö˘í(695™ªÜ◊3ª‰<∂qŒÜ*™ô√_*ã]t4¥<ä$‹¸˙”_¸Eb¸‚ª¶\nhÉ(¢B\"ÑÕÏ∞®¸4€¿ß\0?å6Àâ7Ç∞®…}√K±W‚ÑdL]V@‰±‹⁄Ò,3ΩÓ∫@€_ÔÊ¨DE=≠ºÈ∫≠Îûù˜Cﬂ1u/hi1ÛÆoi‡ás?ÔÊ=œ‹7∆›˝†\0˜én3KõüΩsgføÔ{yª∞‰z¶â\"KãeR,äí⁄¸ICã´%¢èå#EOt%j7>ãÃn“kä\'¥≠πk‰‹ﬂ¢\'¬JäCØûëëôÑ(2õTxå«ÎhÓËv\\e	·ˇ‘€¨¢ºÚRó2È∑WDÈ›!\"{–2’–‡Óa+áˇ˘£ˇîˇk)Óp°â≠4cX€8â:\r^∂70Ñ\rí?)…\ZUH¿tPb¥q⁄Ø®ÄÊ|ûÉÏ\0Ê{°K%XûoröÎ˜{-\rA]ãÄ®4⁄·Ê√›A]]Û°Ü˜ÎèÑˆë˙∫Ü†Â»˚uG≠ŒôÏØk\ZÍÍè¥°y¥ﬂqÕCAˆëÓñcÎÌá.¢é4º∏Ûd]]]ö(à>Åˇ<Í€FõÅøŸÉ G+QöC“•¯aîÆ∑J_9†Î)@t˝È∂lòM™3¶¿CT+ íÖã \"Qr∂Ÿ±4]|th&qÔ8æ≠;gö05‰Á[Z|[àFváà~u1Ç»dYç˙Å¸Ùß	Ü~Ns\"&\n+@,µ5ﬂ◊kdè£q*ª“≠PÕxî∏◊‚·ÓW™—ï˝à6\0∫UdÑA\0N”∆5≠‰“é‰ÎÚÔwDö&  £Ñ™Îˆ∫YŒ-¿	Z\Z<ÔH^AdÏ∞Á57;†Fˆ◊’j¥√ ≠é≠øw{Ê≈˛†´¡[=ítdeÂH∑ì›à!2;ev:©ÕÑé-Êñ•ıì∫—∫gQ»—2¢BYòe2Eæ;f)@tΩÈÖ|\"·a$ºˆjà‰ÑKKô˛Œ¨	√bQ≥£HÖ!Ëª⁄—€–bµ‹ZYîTZÖr°PXZ|õ‰ÎŒvu%D%31ı˘/˝âbËO?\ZÉw`‰÷íÖCQØ+…»I–i≈¨E≥íH£%UZYg‡ßë-íUÏÿë\r@NÿYÀıd‘ŒÒﬁo;‰µµfIà»Ä(ÔÂÎ<ß.Î7i6Aù4-\n\"/Ë¢Géfz˚√p„H7º◊àˆœÃ∏˚É˙o•.ﬂr$—‡bóCdôAQ¨“feëÚ¿@[(.Ô∂åJFF‰§&B‚-Q¡¨LísFµ§ O‰i^\"TÆôYñ¶ïÌ˙ÜÊqª^k∞›∂f\r|W◊<¥—+w¬ÇuÕ42ˇ·GóÜÒ¬\nÖﬂ>Dô≈4‡YDt’†~ øæ˜ÀèceÊàÓ-ñÍ£öÄh°%cÅSRß—÷≥¯íY“	≥»àuç0àÿ)26é6@(¢<äexûºQ∂◊V˜æß’ÚìÍÏpõõO@ƒMDB‘C‰u’π.<Wv@ù˘a[õVgªÕ«÷›ô˜ˆ\rQ◊˚+0z«±\r ¬Â´¥s\\qï”`ÔFÒpcπöƒHïËÒf,à,—√™ÈÆ‹I§9ïª§@B•d‘Çàó¢ñ¿⁄»6olxN√x—ÎÏv\ZÇ|CÉÊÂ±à›Ò∆õamÅ|*G>@DÊÀ“6æŸûv’ÜËYz8ì~òŒTCÙ˘ãÚœ˛T\n¢Ôr⁄öHÅ3É∫\0\0 \0IDAT◊†´\\+Œ%%§N≥¬8‘á^åUQ\rb,‘s‰’;åK´Åi*Å∆Çhµ{˙˛.◊9ÙæÎDBÓYAî◊Î:@tXœ≤\\êVá\"∞¶ÚÊÑ»€Ô9ÏH∑V:ç«÷Î/Z-˚ÉÓ#Ö˛C˘•√˝+]ãØ˚z¢R/Î´ÿ—‡Pà•[oèO º=ïA:√“EˆLÒ™Îãˆ|TŒ•DÊ<Œ˝ı[“\"\0≥“ãﬂñå {îxmIƒÀ√\"≥ÏÂ;</`&Ëg÷‹ÂıVπ9hvÄ$◊j¨Ü¿¢xdÕDÜ\"˝vÇhhàî⁄É=˝·ÉD3h}¶iœøÛ?“¯Œ?LaÂ¥¿\"´\Z∑C6ë®”B+Zñq•Ç\"∑’}`L˙€rµ+™CM€≠3=∑•À˜ö¡,q¢Ä’5t7Õ∫ß7{N3ÛªÎ¿fÍÓ™+	å•Ü.xßGèö	\"‹™’ÂiÕ·zÍ“˙,º´.[ÍÓ◊ıoæ_◊≤≤ê&QDÏ03Qxîäj\Z-É‘‰‰D©l;ÉÂm‰6Ìd‰HŸFOR◊X‚í.-ò]õìÇàï1¢a©ºG©\"‚S\"£L=»¿4¬eµ5≥Œ≠>ËpªõY}sCs0V?Ê9\0ëÂÂy}≥Â\Zyı£5€ÌfV\'¬D;@400·√·DhWˇz\\√üˇ˙¯ªg{\rCj+¥™#SÄöú®†H=’f&¢DOä\"ÊÑ∫ÿqWµ¿¶M˝OTáñôA§ΩFòhD…∞G°kvËaÊ—©H◊UÔt<ÀÛÄù≤ÏBä≥<3;;;ÛÜJë7VPô≠ºNßá{ì\0⁄x‘ïÏõ⁄˙‡–@Ü\"{éõÂ5B9T__ﬂ\rÎ;!h∞œÉ ‰ó\"iGeññÒí±Œ=ñVŒ®-ºXëM‰=*bœµ 2-—É/”ı¡X∫⁄⁄∫Ï‹â’}l√bé£7∑˘A◊¯¶Ÿ!⁄Uô≈Ö €C4@=($ÎœÑ6+óˇïD—«ˇu¥¡\\™´Q]µ2®©Jà‹\n_CrªhÓ!3ÌßÄ£aêÁOô∑ê˘rôbc	–‘“∆qJ\"w∞√ç?g‚nB|Œ§óË˝bxÕá7{Æz˛y—tÎ÷ÕÎ¥;¬;r‰p◊ hZŒ»poœ„¬`oOo:3®Wƒ≤yvP_Œeª¢5ﬁÏyıÃ-«ŒÖ ‰´:èlÉ√xº`∏Ê`ÄË\Zx˜Å&‰4ıLäÍxmô€H¢%ëQ[<ƒ*15?»77≥ÒÊÓºk9›∂’’Â5õ◊£_ø≥ ⁄µ‰Ÿˆ~ö Jõ[M\"?ˇ˙®-í•Xn!¬…ÎhBp˚ìÅJöì≥`◊Ñ»‘C\'á”É _\npÈslﬁàÙÄ$™ 1nYñ˘n4B∏oÉ¢∫Gö-DSÚ)¨≠;Öè,ÍƒÑ˜ıˆˆâ@„`û{	é∞ÛØ*G√ƒ\\⁄ı¶”aÎåCäqû\'Z4FÈtã·v%–gWÁπ˛©Ä ´Ä(ë+’ÄH8g8¨hœ+˛·™Éº„¿ÍÒ⁄:◊∫€¸˙\rûè>âıN⁄lh/â‚Ì-}Fmá€æ˚\'Ú›”£Œ™•Bù©@£∏ì”l:…–∫í!ˆF-à∞1ÀtôÇÙU˝ \\µôâÚ1;ÑÀÂ!˜cUÊ…÷62ÇaíLÒò£\"∫®Ú“ËËË»∂’hôAY¿ÌZãÀ—∏Ì¯¸sml4Âï_≠q#Ÿ~D⁄∞À•d1cô´z=}€ë`ì∂@6c÷	¢§{ÜkJXHÄd\ràJ“9{0zF∞·ƒ8ñ#‘º„9ù\rmnPob´KY≤Ù÷u±—£Ì!ä¥ô‹`./W⁄’f>‡ˇÛ@‰m*à∞àß*\ZqnXõJ24oÃ®í1D∫–vf≠˚nñ\rÍ˘\rìZﬁbá*dJ‘pQUÎ\' äK\"´ò¨zÊÓ∫%´“∂]áÛFbóH‹\\»l–lÎòﬁÉ„ÂﬂkÄõ≈y1˛£§„,,ú∏Tg+ï•µ&— BA¥.≥œ¯~ê#\n\"ÑGÙß≤ƒ’Äà[ODO+ñô/\n;M(ˆµµ5«◊≤≈/ñæŸ[Coıÿﬁ$ §Å£Ù√•©	#iWﬂ3M∞‹ ˇ·„?˝Ïh#Ô@†ò)“*TH©7/ÁÁ^æ|9Wy#àå¢»˜√\r∂¯rj¶ÃÂóòN/≤£ÚyRü’Ñ»Å†Uªu≈V~Oµ’3‚Òn·‘bˇ⁄≠ª÷‹4â∆±ˇICÛ{Z‡8Åã<J19¯úÇ¬®§≤¥˙ß`\r]π~Û⁄=%i©∫\n˚3$T;O\\A\ràîsˆ S1uÒ´√ô¬ÜÑßßßó±™±¨k°Ê5Ô\nQ¥’„Ÿˆç.-¶¢>AÑvıØgÃ¬W•ˇÓªø¯_±£çX)dUSïæv∞?ôDñÓ◊/_Œ”ˇ+€-®	x˜∂„9Ÿ	8qœ1å…}-9^î∆alÅ(˙˝‹à¨™¶‚Ú ≈- ˝û«eÃmà∞˛<πiçJoÀåç˙˙p;ÎYwΩØµ¥ŸO˙f\\ïÏõâõÚ‚,#DWõn^o˙FÍ:˘~Ê%\nªÜxmI$!\ZN‘\0«Ø*´{xhhË…´äü\\z\'Übì®\0ˆÉ≥¥¥d‡ﬁÆÑ]˝‹|¯ÒWO˛Ò£Ô`Gπ∑◊ıdSœ ¥TæÉ:(‡nä¿|Ä!m˛%´0xîÅÉë–2π®[mIÌ¶-4—¥ÊEÏ°¬„êÉ¸e””[$@ƒ*üâtrπ‹îêñ7\"≥:“f€1kzGV∂\râêÅk◊7è±†Û¿x’<€\"å¢´.%˝U∑ˆ¨≤kÿqÙ™·c\\Ωø“L)÷L{¿ÕZ\Z¿Ãá9…)Â¶û\r\rêÕÙ·h.\"#ÀÍ ÔQ◊ Çø`-—ûwZòD¬9+|ı¯qüi~Á;	\ZB±yCn\Zûî°‡ˇÊÎıı \\GàÊ_ŒWjóxSá&Eõòö 1öMLVÏkhi⁄åø©Ü(1‚•5\0ÿÚû\\B’NP=≥4´≠·› R9èÅQÙ»*ª”2\n3pœ”Íﬂ≥¬ÆÊ¢V/‹@n$r`	ˇﬂ∞§EÑç\"Ø\\CA${À©•¶U˚eiÒöıD¥§¿ t‚5•Ïã\'ƒírÜµ.yíhQ¢% Ω-ÑHË≥œ—$˙L¢_=1Õüˇ<õÃ	ƒÊ\rA¶ì&\'ı\"ËÊÁrsÛÎaıÀıu`»`âbäËLlÈ¨‚˙’—F™ÏB”\Zå-O„o¯ﬁ øg´$JBÙY”M’•qÔÇHv1lœp>?ˆÓÁ‹vΩ˙nÕyo#¨o®oÛ(DU,≈S%a…8¬Ç#T®WØ†aYDí¥d†»*E˚=∂ëD	Z˚º2j7,Å,Úji\'Üv/å›\"¸‡Ó∏µ<=m¢%2˜ô–f00\0˘À_∆0ÄA≠{T∆w;ùq⁄ßC≈Ë˙‹‹õõ«}Î9ıZ¢bB¨ª–“ìíß⁄g†i\rìÍiÇRj∆V◊+ÒôD≈°Y˝ñr\"CÈ™ÎroŒÓÇ(Üà˛ûdî¥≤Ò(´Ïxm~–÷)Ùèô.è]œIƒ≠%F—Ó˝[`Q£ ∫.	êñHb†(ë˜Häˆe2†ªj@d%≠ÎƒÜ‡â≠„	∆Gó¨Ùª	¢Ω@îpK&CQÙú¢D »6ˇÊo¢OhaV7oî-ï) («ûg5«taIÎZ£¡4ôÜí\rΩ™Xvï(¢ra,~Ê„(¬øm—SµjäIë·¯*⁄∏Uù±JÜn^õìãxÜ°¸å¿TÉ{≠iÃI\ZG‡Ryæ◊–‘èªﬁ{~â{›ıı-û+70™)çÎ÷rWbAînÉê“<êœë7ôlπ∏O…√Èt°ÍÍ“2j]yÌ—5?yˇA¥›%øç ⁄°\"-ì¸d:ì˛óü≈æQ¡]Æ®\\∞^⁄\rÌ¿íbÄXCÇ\"ç(z˘Rgh]œâÅΩß&Ω≠˘CNö\\É‘ËÙìa±rî#WYÑ&âµÌB÷pˇ\"{H‘ƒK˜ﬁ⁄ç°ÑIT ^)f∫.[ﬁ*Îà;û”\rë€÷ﬂ€Ôô^[G‡{•‰n’∏nÌÓ5a›põ\\2∏\r´”N¨À‡b’ÄÕD\rß˚“√ÜGF3eIK}6 Í„´%—ÿ‘d\r˛  dváh4)\Z4ì12âí≈[bBÏ¡àı“Å\'∂0î‹i(à—C≥3ππóÎ∫Ñhnjb2Q ë ,ÎH¡ÂdÚ-dæ! sCÑ≤\n¥ñÏΩy#§ëÓ´™~$Ô‡Øï›m∫ñd®úﬁ´ ¬M¯`C3™–ÌÃµä#ñ∆\\ﬂbûsÃ∂ aÿêw¥Ü@√ÇÁ®ˆ\"Æ[cM◊Ö zÆ\niÙxfì≈ xÙé&JˆäAîÓ˚∞˜∆\rãêÙ∂Ï—eUH·Â$CCOûL°X⁄Óí˜ à™r¯5EvRLíIDUçhWõÊ–PÈC€¿AdL∏]‡´ÉÀ°…ﬁrXvVö»EÛ/ÁÊÄ°¶¶kœÂ¶å®@Dà	,π*U© a	Éó+‹gDxÙZ$≠T}CAÑ!k°5MQ˘õ\\¯€Ó›†m1C£ª¢x€¢CuEG¥^«\n!V°’\\x…qÛû„≤¿Øœ∫N€{‹õ.ïê)ã@#®T!àUÂ`Fõç \"·Ãülñ—ñ!\ZHü9—óNü˘pdÑ»~àœç¶”¯»¶ç\r¸-KJ”â\\ƒ––q…Ô§Õ∫ˆ\0Qlµ·≈Uçx<¯j(íñàfÄ¯0uÏ]≠≈;=,ÄÂ≤! “5˝ÂQõyÈhä\"-iqSñØWY÷B°OÇkÖ^ëIMä;Æs,fCÚÆ6∞cΩDÌü™B‘/ûﬂΩu„*Ì™∏y=bhiWÜ⁄l…eÔ5tôÿˇ∆ê+æÑ%¥\ZË18*{m›ûuÃöﬂÎ—Tƒ©ïåøw\rÃjFwB.˙]í◊OÔx¬¨Fíá‡Ò\\‹ÚÅ%{⁄>“f\'ŒÙ•{OúÍÎIßG“√œûùNUó¶ƒÒh\ZÆÈŸhapp¯·\0|?3¥Ω2€D{pŒ7«“Ã\nàÜæzÚ` 2juœ”ï∏•˝Ç≤3«c˛¶06J°Y=èyF†Ëk. ˙âäŸxˇhUIëÄh\nº|;®Â£aoπ¯ö¢àHm‰dßÉxÒ‚≈Ω{wo5]i∫v˝:ü‰∆ã∑`(—©‹≠ÓÊ˜ﬁkË[h\r!Yeﬂ,:{ÀÈn∞›z-<f∑6xºEUﬂÉ’7úÄ6ˆ(z≤„(zB‹›í·¡rOπ å}§ÕN˜ÙÙù:›;t™Áƒ––Èû°«È—ëæQ¿i` ›wÍÃâﬁ—g}èG”∏\r|ò˛ÎJ◊`Ëù JÔëö©ƒç·zj¸ı=p˚3æ˙™)n-jã¶˙YQ√⁄E†⁄›ëóè6—ÁÇ¢oÑBÀŸIÉ(·îU˙Â“à…Üa‡„9Xë±ƒ∂ÖïZ‡<ø‡|˙Èï+WöööÆ+zBWo‹S?UÿÕªOWÓü∑ù54[›6è˙»QxáÈ¨\"A∏!8gıAws(⁄»Jåî\ngXGÇ®È∆F≈9v„\ZàWàPˆ\Z<âê%@Ù0\rıúÍ;1r™ˆú~<Ú·È«=C#==√¿<Ÿ◊3r¢ÁD¶˜‘È”È—·> Ïƒ≥Ù[	¢ ™ ·◊îŸÈƒç!m&ú3,&*	£(ö:˘≈˝@ò‹Ó©vñ©$:hLêE≥°&îY1i˛T5räßLH\"@[ß%wÍGgFƒÔ≈/s/Ó›Er\0;It‘∏vı÷LÙC£{`(ñ—ãò/Õ«∫≤¥ô÷–«57Ö—ilÃ¨“j°Ì2?8‘ê≈-?•ÿ)kh÷:òD®„y0û—c£Ë	@Ù§∑<#1Aâä¢}h˜úËÎ˚D—Ëât˙tÔÈæ—ﬁ3££\'FA<ùN?Mü@]6“◊w‚ÒÈæÙ©«Ω òF˚N?ﬁC{ÇËÒˆUo´¢°*˚˙ÂÁS)&ºk—ùG¥<∑<â÷Ò¬&„\Z§1{â%`◊õÊ}†®r˚GeŒπå©¶Bﬁ‡âB˛vaëºÙ‹ªıÈï´»ŒÕm0u≠È⁄›πX¶∑óÎµd4\"v¨€x=s,:!¨\"JÏå\n÷å^›§0≤0àΩ^)J#ŸFÜ≥,m¥õo∫yçQ(≠û\"n¨À‚o¢VŒ’EÙÅKå ød–ËâﬁûS#\'FFÄ£æ”\0Q_@t\nXÌE˜p:Åf”â—3=œﬁNÌ\0Qeø&D	ÁLh3É ˙LÂ›ïI$OœÑ+Ã\n$®]s…®¨-ì§ó‡’[¥«≥iﬁ”ÊXïwü»9Gk;_ÂLÍªÛÜ∂5˚q«QÚ-P\\†∂nlCœu9Æ55]ΩÚÈ≠ª˜fbÇ ÷“R-nî]?@≥jıÆÕÀñh∫d[Ï≤WÈgŒ¸À‹8B«¿Œ∏)Ë˚&ºáï’◊Ø°=∫¶⁄?Z‘â©\"PƒMÂä\Z˚Ü>@}Å∑|¥Øßg§–≥œFŒÙ\rıÇ&FI‘w˙Tﬂ„Ωg‡¡„=ßO£»˙mi≥ëGèwáh) Òî\rñ∞´£π∑î ä ®&m:ßéo¢>Jh—œ…(ÒıØùqﬂ”À…Å€a‰ö≠™MñÀ—âk\"27¸ƒ⁄‰”í2x™—!h@©}˙Èß∑Ó¡\0€˙ç\"g©P(d2r˙ä!\ZµÀéo6‘7çN4‡%\0¡0»í	<\'‰@V6.Éƒº·◊/ÁıπÛÎﬂÄùO}C#¿Në$à∞)aâE°\n4”Q;1P≈1¢ıS⁄ÁRá—Èœ~L êG†◊NÙ^gzzO\rıûÓiÈÍœÙÙˆú>›ìQ⁄l`x«ÀﬁDC€C§d∂pµMl§»bà>ü)WÃx®ûQo*Ÿ≠–‹õ®«Wt‚`Dë[—î®Qh‚i≥π\\î4¿ﬁº°‚¢¯€ãôª€–sΩ©È \r‡Ê˘Ûπäë–{KKô™’∏õ9îúô·Å·≤Àèu◊è◊`ªîc{ªG°X*z!∫Tñ¶E÷—7¿ê\Zﬂ@báf¯ÇíwMÚ0‹d4í≤∞ú˘Z\"≠WÌK§¿ê•á$ñû°‘€ÇËa\ZÕnp‘_Ωz5=Ω¯•°•Õˆ(àvÑ(Ω+DO„À(≠Í@\"!†–AÑi ÀDËQW\'?Ú|‹∏ä1¢Ë∑RU‰z q∆9óS·∂:/}nÊÓßË´ﬂ‹*|Æ^}ıbÆ\\{,•∑∆Iˆ Ü*¬’ô≤´µπ\rfΩ„Ÿ…O≠Zw1€÷∆u\r œã)åÊa!*kTâ“t7Á•(\Z)∂i€T% ™®Ò*n|7º ∏Œ¬‘Ù‘ƒÙ“3T{èü¶{Ec[Á,iWÉKL:g—Õ1®œsd∑aÉŒ1∏D OAôõRÆÿzØ(∫ıM¢Òç§e=55==Ö\0Å}§ ¬Çêπ˘ª∑∂ÚÉ¯Äπ;Û‚\rñó∑V\rÇ°›™,qYã◊÷\\Ôÿ$#¢=—j¬<äñQ~ø®1Õ∞®~\rK™ÊÊrπ,Z◊‘ø|í+ÇHûÑúË‹\' ⁄∫ıl+DIñƒCƒÿû<|80úV—û!⁄C{Kzàkö™∞´Eê4Je%O∏gñ∫|q\nîhMÙj≈◊ƒ¶Ûk◊A»Wî%-ÎÂ5«qƒO—*@Õü+U¶3ŸÀ`-ﬂõyQï©-â∂¥1ÑA4\\.ªAw†uY‡ﬂã2nq\Z¶˙ªZ˜ä£j5çÈ¨X§@Ÿ\\nJñ+ñVwö§ u⁄âh§öWåò.\'Î6≈UnQ\\/$X)£\'XŒòHzÏ—ª¢]ì¥Mı’Å|˘YúÒ§Kt§kÜIùy\"”à§åÖ|Å,‚F•là(˙˙Â\\≈~|Ÿ$û6êy≠>m¿ÛΩÚÿã˘{7∂ÚÉ¯‹nÔÓ∑qﬁyu§ÚO.F3È≠Ì	°Ñ \"!Ì∫`Ô∞qÀïHì)} HqÀa‘Hﬂ=nb4Ø¸h\n‹∏Æ…ü≠U¥Ã√F¥ÿWl¯P€ÍvÅ(fIÿïSCÌ\r¢ùÿ⁄õ]-:øKÉxô¥ŸØÔï‘yK§û4Ÿ†OV‰¢?ÉÙÌëù≤hõ\ZG„Qí\r0nΩ¨EåcW<<~Ød˚•n{÷;ú;Á/úª3{s+?ù˝bNjUg≈Ù,2È\ZJlœö¨JõÅêvY}}}C=\'àî2EsVìGï—]~4Æù1NYçπó_£6+F¥pP7*ü´v¥Æ‹‰∏ëÀ¢qpo%«ÙpMQÙÆ=Íﬂ\r\":«ãMÂÏ.!h\'>#àfTCfÉz“;¡dNÌ2ŸéY\nfÜz\\∂úµ*´Û≈*å(öØRû7ﬁﬁ⁄éP\\ﬂ…:ü‹ôπ}a˝¸ΩŸÀfoI~ÆÅ◊Û≥=DË≈g2µÃË∑F()àp}Q\'Ä†ôπBﬁ(àî8ı4⁄`\0bÑÎ¿Ñ\Ziµ≤8EOÜµ¿˘Ùz•ESƒdm#¯´\"\"áôÏDbh_.·ºÓm`•˛€@¥£´Ùkj3¥´ÈòäµZ-ó&Dºö<|–M‰õôzà•ª9Æ…∫2:	.>\n{}¢çk’`»Ø_ìÕ%Õ%Øı|˚xÍ¸ßÒˆ˘πoØﬂæ0{˚Ú˙∑œüøp{ˆñ?3ÛŒ‡@’X¨í<<€‚#	⁄Bï⁄l¬¢v	N–†πIx¢Ω∞ñ#\ZôÇ≈(Ã%j◊Ö∆„∂ÒË–pÚJ•kµ2àö`\Z—˛EÇ(vœ@°◊oZŸÎ»…‡R<\r€@¥ã)‘ïŸ\"≤´-`◊sS‡ -ìËÛî≈¿&ç@áÓ˘YºÇ…¢3ÇÆ£∂üÚ¥h¶û„ï€úà!ˆ\"¢àdó-67{„üÃΩòπpy˝¬Ö˘ıÛ/÷œﬂæ|æÃŒÃÃ‹˙¥ΩÛ—@∑}TÛêëå\nΩµ9ÔÑPÖ  L≤Àˇ\0F≥ÔUH Sî™†˙YRqLßîi‰ö·Ö^‡†OÇà‚äÇ¬R¢@ñÑª¸eúé\rêfA)\'z\'ñ»~258º˝^°› ⁄›9S˙Å3çù ìËÀœP‰\nmFÁ2‹‘9°á∂8Îçï£¸Îõx\"ò±<ÒjCúGÕÉ∞£.ª3sÔ€≥_Ãﬁ>ø~˘Ú¸¸Ö€Î∑œ_∏≥~˘¸Ì€ÁWFË£\rAn°(ΩnﬁEè—»$!\Zû.ï?\nX3uÚÅº:=JÃƒA¨F£Œ∏“ùµh8Øâ∂º° ÇÖ6%38î2JlüS≈˚ÚË9±I°¥≈&(Ω≠ä{íﬁ¢]\Z⁄Dq∑Zéß^ÊÑ6˚ı8›vŸqi¬tõtZF@pãÇè—¶(£`»¢›√’aæ+¢àπ ÅŒ•^‹k∫;{Óﬁ_Ãí\"õø|@˙∂∂∫“ıIjPB4L€>ﬁôˇ!îNL3FƒH«˜Õ,ÎjfHë)úÓ®låN‹èıµ\rJ€»~.0{ÿ‡Êï9zªÉÁ¸E\Z9Ÿç_m¯¿∆Ò°µ\rkI“[†Ù‰]!⁄!áØ¶π,\\Üÿû⁄ÏÂ˚GöñMW\Zb.\ZOÁP—’Ä\"‹2£y—Y_e›Wâ∞wÉXí¢´ü;<ı…ãÁÁnœﬁº;÷@4Á‹ã7ù«WV?Y]¿Ü/™\\c»Ü=Ù÷‰TÙ!C¶*7\"ªt\rÎ˙c‡ükÒú»ÿ≈RÙÕJ±áÔ¯6µ`w±}V‡¢	.9g8{7\"ADCâˆiQÅ¨Æ^≤´˜ŒÂr\Zn/Ω#DUÕj@Dv5^FI®)k‚A4ÀR0xªÈb–Ûa}Ÿ±b;D9x8∂Sv‹s∏\\É¢O“≤÷ù–P\rü^Pª‘[∑÷«æ˝bfˆÚÂŸ/nÇ63s˚ºΩ˙Ì≈’≈‘\nÚÛå\"˘—\0Q‰ˇˇB*’-!¬≥úf◊≥›ûgG•º∏€ºLUæ&:∂X:–‘∫€>§8Oc≠nÄ€u◊#ˇ˛ä˘{ccéÃ›KãHbdTÂ=J{ÅËmå•wÑË—Ó	ª⁄P€yó•É?´•`-∂FvùÌﬂ	[4æÛ±£õÁ∫æ±í<éä™JqC`àãMáX$:ˇY”≥≥≥Û`Gœﬁ;Ù]\0®=u~pu•{ÂO∫Ç\ZÉ†¯ªà¢ßÔÇXDHQ¢y‹íe·Â2<b‘Nÿ”Úƒ,n·‡È7Y–ﬁ·y«;·?õóÉ„å ÜÀ#œ\nùô/`ÃÑN—Üu◊—ÅGI∏XüDGï‚w ∆r≠\\axÌ—ÙÙÚÚtÖ±Tõ•wUgï[=*™≤´UNyYöDÛ¢c≠©K„°ùJ5ZA™µ±5€π‹w·)ò.˜”÷Ë»∏Ê	Ü6l&ìk∆‹¸ÃÌÁoØœüCÎ˘‹ÌV·ìf‡gdaea+?4“,ΩAIƒø9B0+±BãÇRÆÁÍ!ãÎ´À∏%E~Ã±¶Dóå•<Ìx —.\"ølæçeEe~ÁﬁÏÃ\\äs¸éˆí˛ö·y‹Ú5O¿:wJé^“+N¿Ë8ô`˚j3$IYﬁ√‚Ä“¥x•6KôÌ˙Ì—„ÃÆ)õÿë≈)È‡œ—i«Q£[æË≥,,†∏Â5Úqò©Tp<–‡©Œ‡∏K3>èRÖ¨C\\\'«ÿvΩ7.ÄÎua˝Œπ{3≥Á@a√ªÙN Ça¡“O´«zÑpV\"Ö¶ ;Ü∏Äà!Àä Ú∞öW∆Û/zÌ≠«Ωé÷ ’)$Qg*»^jøî\rSç©ã_c‡4∂¶ZÉŒFX~˙€—ÈOµdcáÀπ¶∫•ïª \0\0 \0IDATTì4ö€}5íÇ®¥Q=∏Q’Ê“;BèÌ \Zû;L—Æ1LD“¡ßs◊9ùplá v@uÜ«7≈:;<p{è>9\"⁄!6e±h„9·ƒPQß^(∂/ÃŒÃŒúª≥~·2_y=∏≤¥∂¸I–í¡ÊÉÍ#ÔŒR¸⁄ª1ÙîZQ!j®o≥±Qî£öë,\'\Z€i‘{Y∂dæh4j)-5VëÊç•¬‘ÌŸ;gúFù58Ag´„4jQGpú˚¸íÁµw8T€5∞.«A©\ZM)Ü¨ç≠#WıﬁCà‰¨ë8ñ=Œô9¯x⁄±ÎxëZ;W⁄an<Ñ®›uyÄO9∏í¿i1ÒÏ!<aôÒ\r[Fπë!≥¥ÿâ≈¸§›9w{Ê.Ü^úÀø^XXÿã°Ä„ñ[Ω˚xÜheE∆†d!k≥gmÿ∑Z9gñllßÖ„\"ÌVS™Û¢◊Ÿqâ%!‚ sR~ÍŒÏù∆˘0•{ÌçÌV–÷S{ßêD0•>¥ì<U€ç  ÈS€2dÂ&jé\'ˇv\r=ådnl‚2\\ç9¢–b:∆\Z;¡É\'ÏK„ÌÌQ«x£ÄHˆçGa¥Åa«-t5˙Ü¢oé€~~‹[ø}nfÊãŸsœÁ/w¨§∂®‚2±“`M°Ò[c»,$•3òE,# zò&Í’VÀ¥y ‘ç2Ä,–^Q\rÉa¯Ng*Â©ãn¢Kù6ìΩ	S¶Õ¨ÒãeÂ^+@‰\nà¸±îÎDâ`”Œ)eVã°ç‚‘o¢°°›!¢-ˇ¥Ôê‹ÕQBGfÉ+?f[c°›∏ùcÓx\0W?Vˆx«ò–S•qØÉ\"π¶*.≤76∞d€)k‹îàw\Z?)øô«Ï2(¥s˜Êgg\n{\0àÛ€)üﬂ\nDh% ¬ÔôÑË!∂ııº˙Œc\ry¥É+Ô.H<â’≤¨¢„µ„›é{1Â˙Ià∆å÷‘7çQÎœ∏ƒ5ÄËí°_¥=xœ%Ç»∫®[`ï´]©®ˆà¿rûòÎËËŒö¯P-÷Ü9±\rDôÙ[@Ñ≠u≈à√D;B≤ËU©T¶Ú=ô¡GO\rﬂ	]¥p?j öÃ9Úmj^øFi0ë±∞≥\rlÃ¨Ÿÿ1?:Ûî{⁄πÁ33ÊÊ¡\'ª|aˆã[7õ˙∑ T˚ìóü©˝⁄o\r\"Aë4≤P°$DòÇ|√±NÓ{é™Hìw◊–≥éÔ“6!⁄@¶ (x€iÛ)N4ﬁÍ¨Uk]oΩ7{ß=¥€9H≠Z06÷ö ˙~6’⁄	L#<jÔ‘ù$D%nàºG5Ddµ∑OÊò÷ù\'aÑ€∆F9}ﬂ3∂Åhzqﬁ∫T¢◊t∞¿hÑÀ ÍÍÍhµáøDß0ÍaRîÁ\rôD/_2Ú±Ór»\\ß§[—ıπÆ≠˙‡≥í≈r\"ëo”Ür≥Lq!Éá≈…)YJbﬁÄWÓˆ¸¸ÂÛü§ff®N(\Z°F“ªd∑0‡8∏Õkø%u&(äuThCÚåÀÒ:4?Ù-Ìjëı¿†ˆ&¬\n}ÉŒGƒ#[7≈Im~Ä+–£†l‡ΩY¡;´ëZK;>v†qËå°`)|ó\ZÛ∞ˇ÷Iñ∏Lö©h„æ-MM¥Oäk≥›Hëø°˘ŒÜﬂ›4Á˝§IÙjm-j0æÊmˆãS(FË™GWVWF$DØW˚˚˚#nFFVªZˆÂ¡Öó ê¢ü}˛˘ØøƒﬁByT Å\\◊≤<‹¨Ë–ÅŒòÈ¿å¬ÑÁ„1ÎaÌ\r¢Hl≤f<t&©öÅã√=ÿ›/fŒùüôΩ˚b•1ø≤Ù©ÿé÷?Çé]o˘†á^˛€RÙ÷≤®ê»ﬂ+ÖÜUÿ~8ùîıPÁOeı¢ãÌΩ`’«õÈ‡Zã!G§d?è0mã{¸Ó≠[∑öÓ¿C:¢LïkºÀ°ÿøcsß˝“•v<<C…ùDº&D®Ã⁄\'·˜OL£¶GY‰77 D&éÊ˝“Ùƒ4n˚Äˇ÷ú∂ú≥F\'^9ıÔl€\\Ωø≤≤˙ıÎÖïB◊ÍÎÖ◊$V˙ﬁøÙËÍ\nH§Ãlﬁøø\n¢	…âè\\‹	¢AäzîLå[ﬂª⁄‘tıs¨Ï‰X‘óJ•≤6»_«	M«≥,6›60<Õ•2\"ÎaAçŸt˛≤E\"G∂”Ÿ≈ÁMM∑f—®~çâ±~ÍÙr˝÷“‡˘A§hÁ;úaaËæµ(zä‚à*4V¿*”ñ«öÎ;Mó≤(Å $êÜqÂ.∏p=á^ÜÆe£≠i5Ò◊<KúÈ\nû¨!äå‘i6Lß#è`µN=´Ç\Zk9¥ˆΩÔa]w+(™µÜf◊r|Õ	¯yæ∂∂∂‹ΩÏ8›Ørπ£mÊ8~ª∂¸jÚ‡ÍJ·¿fˇÅ˛Õ’ï˛≈ÕïñÉÉõ´Ö˛’ï—ë’GGA|≤π:XX›,7é>Z-¢0z∂à2O2SQ…=£≤óœ^änãn6e˚≠Ìxñ»£\0|” PÔb*$@_\r∑:à#óÌ\rŸcŒ“‹0qéh»[WÅô/f.\\X≈mWâ¢ª≥wŒµ}2(ÖÎN¢h´óøÜﬁ#3ôz\nÕƒ\Z”\n^ææ°€t]<úñ;Å\0à™§Ì¯tIŸº3n|ÇMÚqkZày\\ªKí…s,p:ÙÒÒÏ∏·a◊G!⁄l«rÑ„«-7È„5 ¢Ëa\"ˇ{0@7vwDGª◊&ˇh÷:P_`Ÿk;ÿp–Ã8⁄‹v†Ÿ<0π÷\\Â.Æ¨Ùoú<ÿ≤Ÿ2)ˇ‰Ë—ÆÕ˚GO]}=≤\nÇ Zz/¥l÷=∫tpp AÁ∑Ö®Ä*sπl»\"kóÕ5ÇD∫˘°}1ËÏ;«˘•K„ù≠¡ªwABπÌ«Sû4¢˝dI¡Œ¡ÄH4&0K/Æâ≥NgÃÛÖUD›ùΩwÓˆJ·¸‚Î›np2‡∏3C¬H~é*(\Z\0Ö∆XÅ \Z≠Œ;ÎèÅ¥¿û\\\0DCÁ™„í⁄hZ •Ÿ®∏^Æ>h«lôM‡®∏t,ü\n≠àVFñQyLLô‚Q˚™öË»Ö!2ÙΩ;aòÎòX^;8˘jbŸ=‡fè˙N=pÛj≠°m¸‡ögpº£krkØºÊ\'˚W7ªéÆn∂,ÆúlŸÑˇ√7´GA¨û<ŸﬂÙ‰Ê£¡’Æ£õ\0◊ÊÅ≈É˝´#…f€AÙî>m‰•O˝3ÌW_PgV≤≥©êb‘ù·qœiòÇŒVü‚å\"ƒ!çX@Uíª õÓÔ\\°sªõûg@èF±°~d\\µïOÚªBD^~-QTçœSˆ!\"%ü=\0àL0qÇ–#öeµ‰µ¡∫a¥8:V“ Èbop‹ù`Oπ°9x¬2Fî≤Zv!ˆ*d∂,E∑6ÓàN>D˜De£!D”—´0úÏ\0Û˘¿\Z=ÀºÊzg≠ππÌ‡—£GªA¸¨u]s\Zö€@MÉ]Ωÿu;`=\nèéˆo≈QXYX=zÚ˛}@ÁºpÛ@as¿¡˚´Ø´<¸m \Zöò	DN;Ïå{XM~£L›ÉÁÁ÷_§BÓ•8\0C°VÄ®£√˜è)\rèi;Nﬁ=πq%M‘ZÉIÌkIÜÓ“ﬁ≈¶ŸÅÅÙ»H_]∫sÓˆÃ˘Àóœ-≠Ïz1‡X‹‚ÂÔD–.±ßZ@¶h∞<ïÜ\"@d(àßÌÿ‘õe∏ÉãAn(Ë≠®‚:71âOîjÄù&r«W\ZûÀÏ®#Ï…àMò),ë«·P”CÅŒæjÜ&:«Ñ6˚ﬁ˜¬∞≥s‚U˜¡µWÀkcGΩÜÊµµÉ›Õu\r;-ıé”yÙ`ŒôX^≥yÂ˛—Õ˙ÆÕPl˚7Ä#–b´´\nÔ&j∞ì-VÅ¶.0°»9{ºDÿYt™\\ññqÁ\0—_º\\œ•¿!ªËwv:ÜóΩDí®√˜\0¢,ÿÅ¡q:ªÜö[QDÄ·9Êéñ‹C5D◊Øﬁ-Y√ïı–ãÁ.œÇ0∫¸|sut/¢(®8ÓP%H{ÄËÈ0Q$!*ÄM$!‚NwCw≥÷‡,ÁrF%C¥	œH0Dá,≈áú1ˆÇNÈl$L\'⁄‡\'ZÑ·âÿÉ∂Ñ9W JgRQ. ∫N∂¢˚áÖ÷kGëö∂zÔË—Â∂ÉN˜¡‹r™±µÊÜe7w\0@t~a`◊Ê—G\'OZl¢¿πtq∞udeÒ\0äPeÖÆÉ˘<(<∞ëé∂ê: Ï—ìÈÈâ¯êìˆÀˇ˛Ãô˝‡Gˇ€Ã≠´WÔ∫fc6€8Êwv∏ó≤ùç¡x iòj.Jîíƒ‚òS‹\"cÈHî¨≥~!è~^¶Ì®…<«Ë ‡˘;≥≥ﬂû_oïº´(7ƒ®%ÑvCh∑Hfí°·à¢¢¢Üqªﬁ©Gà‚fmQΩã™›,\"›0uU2ç≤vóNÈÙ§t6b	≈’ﬁa⁄R‘òN!Ø‚Lú©$D‡ñE	çén’…ÊQ◊‡‡Rj•ÓÆ.Ø~Úh}Œ]k;ztøZõ<⁄ÈëE41Ω≤J¨m¢%0©ènAõ öN\0¢¡ìÃ ‚…£èÓ?…Éë›“µ⁄î ⁄=wÜdc CF@˙øˇ?¸Êø¶”ˇıˇ¯—˜/É;::;dZ`ut:Åﬂ	˛ÜÔuæ÷éà—·9‰”ÀÛ‡5!¥-∆¶&––ºCû|”ß∏ßﬂJWı8Aä._∏∞~˚€ç{p–®¨®\Z°=s¥3CÙ€2Hm\"I@d:]]N√±nÇ(≤u¢É∑îGFáON„°Ó“∏∆ï•âC˙4.[$° ™ã=¿FeÕòg%)J@4ïdhb¢˝—&ùX€€√>++õÆ„≠·X~Ö_ù54Ñ÷<-\",”ß›.¨¨¨R	Œ™\Z+#ØE|zîæﬂ‹å^⁄D\"ú9-µ˙?üÓS/˜ù˛Âıª\\Ïπ¬≤>qêü.æKôœ≥YGD≠Ö#‚áå⁄ÿ–\'&J÷]ÙÏo^ΩãmÇñÜ∑Ù…])¥ﬁûKùªw\'µπ+Ep‘ﬁï†›023—ˆ˚Ç¢®\"ßYøBÜT7ê®-ôE-óósZ€∫05·ﬂÎbüY§ˆdˇ˜®Ú “5\roQR¢) ó%a=gz{œ CÜ£‘j&Sx251===5çj”Ø÷@√≠Q˙ıÈ3† ùñâ±—Q¯:Úˇ}g ˘ÖQıÚË»^  (ãç$Ú£«Øˇów0THGºñ´âı¥ÿ@çÈ{c√“\'¶>%sËährd•∑ˆZ\Z]ŸÙ.üüY_ü[›M£	/ˇ]⁄£Ç©ˆ	axHPÑq\"q˛ êâ€7ä⁄’•®•å<à‰0CÔ˜+1)úáE≤ã,Ì\Z˘˜4KQ‹#ˆ‹¢Z–RY/‚â®ú’y&4rDUM<yUoOOØ8å*Ÿ≤Z¥?RÛóQ(°®xÚn]–GÜ˙wÉË©L¨‰pY‰øˇ/…7¸À˜ˇw¨D◊Çä¯óºjÒU*AúÈ0+¢%Dôﬂ!sË⁄\r’û∆™’¶Ïu·¸Ã¸¸ÂsÁœÆ<KÔ8®¨Ë≠ Dè“£;`Ñ\r´p….\"àƒIPX•∆M›ˆ≥À	Dq ©ü0Shî(wƒ‘ë¶Ù@ü!ˇ^O,µCW≠ƒπ_^h3ÀkBÑ∫l:	—†j\'S!µ^üJOñóó	ß¡ÙªA‘ø#Dt;òÇ§kœ$=ˆGøÛ;DZÌü~dâ#^∑0d…ô≈Ë¨\\⁄˘\ZÑÿ2î‚µ;§ öÓæâ~FtòÏÃ˙ycˆÚ˘{≥∑?ŸQ¡è‡>Fkp/‡‡,f\nÉÖvø∆≥ÂÑå›^\Z=}Z0ô·å0f…ªŒ DCD¶©vbDi	ì«≠+¿¶‘*P\0ÍI$¸{´wPâCJÚ$Çr›œZ~¨	Ê\\≠çÂdvÑ∂ŒG\Z‹ì¡Ëπ•¬“R·m!z¥;D	ëE «á4¸/Ù!=¸A?äÍ‚÷Cƒ0í!Bd¶)&BªÄbDòà]Ωõ¸°®y‚.é.º^:]˝‚Îëm¢AeEª∞\r≥≤ÛÙm≥€ä—”ßTü/›j!ã(ÌëÄ»‘Çb¢Uï~“Ñ∆äÖ\rY€‚¥–&Mµø`’QgYı[`!2?¨ÑHóÅ ¬z¸J}∂ï°ù4π4ﬁ¢°]!íFÓÍ˝Á”ﬂ˚ùÔˇ”)z¯„∆~V[\ZñÍ≤ßSCg`Áùˆ|XöåuZqﬂØÚ6gi/¨Ä öΩpy›ZŸb]Wò@;lÇÈÉw/.+\Z≈√ô≠GVYô·ßÖL-åƒü($ 1ÛA\"]ûhU5∆Ó∫E°∆ìFZ€ÿ@ˇf”Ìà.ãEoâMj3Ò[,r”å\"TSS˚rZn\ZJJ\"¢hõ^V5™©≈˜‘§+≥DÓûƒ˙#—âﬂ;£˛¯èÑ ¨F7)∫p‹©âGº‡÷ÿà!jV$£CùÈr9˝4n≥X£…‘»J˜˘ÀÁœœ›9ﬂ∏çå‘‡G<æjã>{6äÏÿécã\"J{£Íÿ¶h„mâ‚/$ˇ–∞¢HJ!îGÖÅ$DÃêEª}ì™=Nö©.)`mS£œ+c±⁄cU˛ı•ïÁ∆X2]!$Ü	È|cÉWVÑLL\r’ÏdUÉ†⁄˛DMÜ^√Zï5 \"ı\0lèÖm zÈ≥¸è‚Ø~ˇ˜~Áƒø–£ÍeVCÅ$¬-ÿã\0mkù—ö2Êˆ¢I0Ñ=Ö2x∞ÿ®<Î{)]ÎrVÚ©€ÿ∆·Úy™dYH‹ÿä1à«WUÎ/ß\\sD{îe4(≥ä≈\"NÇá^,¸ï48±œ¢?UêÇﬁW‰ÿ ›ñµÑ»ÃR{ÜeŸ\Z&\n˘(wM\'ø>VX`mﬂCˇ˛ñuï4Ts›ÿ?S\'ZD\neë•	xÑX4¢)ìoî&∂å¡áª ¥còµD´˝´++ãÉQuZ‘ÃatµPX©ÇËn9çù|VÓ˘±˙”ˇ$m¢ˇ»˜&´œB¿‘éÜõØP©\"}D*ONûJ∏>ñª%ñ®Ù∂ëuÌ‹>?3;{æ{u•´kÂŸv.ã[Ñ–ƒakª&C—ﬁQUﬂÂ`€ñ¸“»„4H∫gèœ,ú¬ìU“£g‡˚ßB,=}:Z¥Sñœ£•Ç˜/Û`‡aÈ≥ÂâÂä∞a„tW“®—Á’ÁQ ñ…Ûtô5Kà∂Ë∑pÙÏ–M⁄ED÷DçÒd[Üv/c®)àV˜∑¨Æˆ◊≠ba\ZH•◊ãOpw˚æÚ˛˚´’ûxçm- ¥^øZ~E√˛«·Á˘Õâﬂú˘ù¯¯«ˇK®MLÜ*‘T≠6e’áÅ˚‘ê!\'§…iΩ\'ö≈~˙X^ﬁ(1:ìoqáÓ—+©À≥≥3Á^∏]ÁW∂mÉ˚5ÍÅßDÕ◊Ü(™\rT˜œyr⁄ ∏î>±Ä”—◊≥pjÅ¢j\'dÃç¬jØi∑∫ŒtùÓ∑ApŸY°±>[ˆc‰P%Ì\"fÖÒ∆M‡Bª\'Ú˜’ñ¥U-ƒ‚ÍHåq[ÿı—◊e»ëÏ£}hm∑Öcph[Ä“;çg€Ath!í\nõ´É´K˝´$ÇFV¡+´ÉãÉÉ´ã˝ı.ÆnˆÔá◊˚π„tØçyN˜£†p|{|¯{T`¸£G¡$E?Ë`÷ÿañà÷ã•Öb\\üí@ecΩ€è£≤ÑÁ>èñGwÍ@æ“}aÊﬁ˘ÀÎ∑Å°—Ì\Z\Z¬„´∞Ù9≤“\n…ìmí)ä\"àúS÷ôΩgFœúÍ]ËÈ}|z°˜‘ô—ÖgN-ÙùÜGgFFzz?<e≠”y´øÀÓ?u¶¯®7›€ì~¯@R§Ö¢^èmu˛]=∫h1]˛Ω:6 \n,n±ãX2ÇÑ›“òÓáÜô˘Êæ“∆∆Ùƒ∂„I\rÇ“ªçg€B‘Ú>@¥πr‰‰·ñç˝´K˚≠÷u≠Ç6Î:y≤kuµÆÓ˛°ìÔü<≤πz‰d›…Õñ˝-õÉáO\Z˜ˆkˆé´ˇwøAkˇü0NÙO¯Ë7=ˇØ\rQpÑGS«˘Ú‘î,˝@SÄkTOb‹ ÜÆﬁNHŸß∆”;^ÿ¬JÎ∑œ•Ä°;XtΩ¢∫%\r ¥›∆¡Àœƒkmv‚b#≤Aè ˆáÏTK˛T¶∑/˝·Bœ„«=@Õôû°Èﬁ3ôSÂÒÈëæ”ßFñä÷©ÆÆS‹>cµtÂOèûÍ{\ZôEöÎLí˝ÒP%R4?>¶º¨”qo◊¥ ∑»z’B,°‹,KÁXjk1D”UéŸ∂ÌπÙeª÷√\0—Í·~ÄË˛˝’≈˝õu˝èéú\\=.œÎ’#è’≠n÷=Z}ﬂ…Áè¨¨¥l¬€7è<ZyTÁÔœy›G6÷N˛‡a≈¯˛ˇâÌr‡™«Û›y-≤\nEÏUEË61˙\rwßÀ§˝ïŒ\nEù^™yåv≈xΩ≤4∑~˘€ó/_n\\›y9ö…å.ûE4’=MZËÂƒ‰, 5G|òûÂ“á]è∫∫N/ˆû>ub·T¶ßD“âﬁ«=#}ßzz·<ÍÌY81íŒXÊÛg[Nùxdü»üIà‘9ü·82ƒUÎ\nŸä))Z \\4r˙o…¸}í4\0ƒy•Íôí ≈ _äÕ?qb-04\r—ƒƒû⁄{	ﬁ∂Ì´¢˛√\0Q›ë∫∫∫ÕññìèY]Y)Z]=T\0¨PŸ≠‘Â7Ôπ_XÖ∑ØÓáwﬁﬂ‹ø·67¨≠5ˇ7âÓÏ~ÛÔ»·›› YµÓ<ÌπÖ€Æ5\',ÍbÅ¢ÁM◊#∑¨“ÿ€˝‚F_Ø^8Ô‹ÂÀw6ÿπg∫¬nê\'*„!’Åˆd©$ÜU}zπäùÑ:≥Nù£Ëëy\"˝¯‘¬áßüydù~åˆÙÙÙçÙ¬W–fôS`mgNY†ŒŒtqPkßN±•á‚‹ﬁHü·Ñ»öI¶-äu∏∫˘ˇ^ÉEf%3§lÀ’vQúãe≤ß¯˙ÅÆÔ¢¡∑GháË—ÍI¿Á˝*R´;º˙˛˝ñ’——’ñ√uuáªbàñ\nãè\0*êDáWVVΩ˝kX.∞÷|˙Ià~G!1DgF·\'jx*èqgÉä~H¸à•%2◊>Ì{Ñ–5Ziûπ}~v~˝˘πNÔ’DDC‚˚I,+ö0ê É\Zgú•J´ıãbç—~Í@dö\'zNÉkŒŸô3-\'FœúÓ91˙¯Tœ©—ﬁgN=:—˜,›w¢´ÎÑâ/õˆô3‡Æâhö◊†dta\'G«/ZïÒ\"nÑ∂0zt·ﬂkòVãé£àºˇ-y¥ËDÕSM¡`v-Mﬂ\r¢°∑òÂ≠m	3DãáÍ6),˛ΩùÌS˜ù‡ÁñKiyqeª¥ä™Œc£[ÒrKâ÷[æM	ã‰8-ÛB/Ö–UH∂ ËÇπ¶nŸh]∏Ña…!y Ñ…\0Câ\'Ös⁄”ùŒN6{C¢√ÔqÕ4j4≠•[„Ó∫Ô˜˜˚ı√<¡ %◊∂F√ÄòôÓœ|ü¸{{«^›Ú€BmÙ–q—V’ÍÍ±±1Ñ(æ◊~j->îàÊèÆŒÎx„ØÜæ˝WÂ&÷@Ã⁄y]Â≠\r¨j(cbòàUW±:XÍñé!ÙÈQ£·¸œÊ≥Ω1’ÇhÜ%3n[_cY—¨dÀ˜÷`INQK0›zNøB∂$iU∑¨è1Pî`K\'÷V„#âÒ‡`0\Zºüçåè‹ç‡Íû¡¡a9≠¨Ú\\Z9√a!€û«¸ôQåYÂàa€§˜¶©Of£X—i˚gc©Ü¨\\,ºtù∑~]U\Zø/DsáGh_Ü\0\"3[CÌ{[CUØÌÌu˙∑÷™∂6AõΩ∫µô‹™z“>∂5÷æµ’ﬂ√ÿ⁄Í¨⁄É?ß∆ˆ™í…\'CUﬁÓûˇÎ(¥ø˚ÊˇÆIƒ[t ã<ªz‰¨<∂/X+ZÀLÍCã!å›ùJv7£mΩ[€∑„ú$“Ahü¥YÙÚ%€Ïqoˆp–ª’÷ùí\"¸/I€€€Úv¸~pJ⁄æ?¢±3∏πd±X\ZŒéK€håwvÇÇ¬&8ÔΩ˜ﬁ§5)EÄÉùÀbà∫hòÕo\"˛=OT>õZÈJyXÓæ%áR,+(\"∫ƒö„M‚•_Û˚f ⁄⁄J`â\Z©YÉˆzí‘¶ë™µ)X≥j⁄»l)“}ù§un¡µ‰ŒŒŒÚ¢ˇ?€ lhG3$%ÇÇË;p`˙3‚:aV‰Ûlµ¬2ó˙r*!<6◊óë°È≥K;NÛÔ∏ˆ∑mˇóT´N\nÀmπÍUeU4;…ëtHc∆æ@Åºz“Å/\n¸∂∏*o”\'Ω7 â†e?_÷Z%,ÕìïpÀﬂìzOŒÕê À≈≤˙X˚◊IÛ‘K+—ÿ·ö⁄ü°˚°D\"ƒJ”<cµi‰ñ´M·èÑBA¯CJ‹¶Ó¬Ø¢©§–[øıÌ˜»ÒÌ7fÊ“pπVE\"1‡0€∆ä|ÅAïßY¶#êá–!öneæ”≤≤R€º0›CGèÀ®émlÄ´éâ§y~û4Iƒ& Å®¿˙T;rÕSïß©Mû.NfhÀÛo›öÄÁ©EJC_çí≥2∆äx€ gàÆÈÊõQ5∆Ï\\¨À˙a5Dv¿R≤Õ!‚ˆD]úÁL¨D/he3D:$YI,÷…&Ûr¯4ãﬂÅ\'Ñ©ﬂa0îLÓÌÏË;+ª?;C!˙ÊﬂœÃ‚∞x7D|¿fdt\Z+@EÚÒ¢µ‘óØZ≥RAU}ºÿ|∂Ì≠ó.hÜÊ˛§€q*SK<BÑ|±0.◊Ä˚Ães’Æ2ö$\"\Z4åèbobAﬂäèﬁ\"cé\n£q<+∏πÃ4Ÿ˙?;|(ÛYô!ô7ıH#Õﬂ”^‡≥! i5Ì™…ç92¨x›mf·à˛F›_\0\0 \0IDATåY¢π√34Uú°Ë\0ÓÉ®›øE“Ø˘°Ÿî&„÷»¨Mv	cU{˛Ω£g˜ƒ_QÙWoÄ˝Å”\n¬¢@Df˚p8ùµQa–Z¢µ‘ç«G⁄7∫?COTuπ∂ˆÏÖ•›˙®ÆÂADE/ﬂz$K…\"¬ñ»\\6ö±¡ëfnà∆∞´,>Zò!∞˜„qG˝—ø\'	DÑ\"öiÂYì´K®X¢≈\\§˘{Ü¯l ‘q(à‚D¥sr±§l_`ÍÃ°ãw@-—Ì√ùËÄ\nπˆùùØﬂZ-ﬂ¢6ŒX|ˇ∞¸=≥~SXÁ_Å5›Ÿ~ÃüDyî\0Ll≠\rÔuVÈUª-T}Ûˆ£Gz¯| c÷™üìË!ßQ\ZS‘§nl-Ü≤\ZøL÷˚æS{aqw˜¬Ÿa›ï°gKã≤9îÜØÚ ri∑^ÜxªÃåôD£¿D^ﬁrîî%°êaÂÆ-√Ë=^¥)Bë\"ZûjŒ!H§}Ø…öz&«‡\\¶tµ#ù∂ÇÌ∑˚–hO£‰@ƒæñR!uv{ø’AáFàπˆ[ß¸\0—ﬁ÷pD“˙8Ûq~G≈á◊ˆ»ó3ÒaQg2?∫Ë\0nc…‰Z|ãB‘π€≤pD—∑ØÔvO“ V£VÉS LBkú†öŒvrôªJ–z&ùÿí%¨≈Jf„$cææùïï≈∑j⁄ŒFÙî˘q“.≤\0^~å9`Ó⁄3©¿]I∂¥Ô≤´\'Pÿ‰¶øÈ◊Î¢ï≥œ¶à®6>Næ¢_È÷¿T˛v7|çX—»ÍÛ) <ŒIÒvk\Z˚ËÂÊbE;eŸ€aπTÅq{cáahjÍ@ÜDÌù´Â4k∂W’πW~Í‘Q?˙dù«⁄è≠ì/áˆÜéûz’Üh≠|oæ7¥wt-Ÿy\n˛-Ö®<<¸MDˇC/˜zè@HÅ(ëE⁄j\0ºáX,mòökç≤eR˜·¥ﬁ–Û2Ñ“yt(∑,•.’æÖÖ!]\ZX8ƒ¥–Ë¡‚%‘MQñ	]¢5TC≈≠Î∫f)¿ú¨OdÜ&EIVU5ëx¢Æe⁄_à9G∂aDë¬Â/€¨™(øéX”ÊﬂìWCJ˙\ræ¢O”›Âd^D≥:Ødyn ÈÚ \Z=ƒ«µÜ¢··°£q\0ÅeÕ\0¢µ-Å(x—/˜ém≈¢©≠ŒS√√ß^›\ZNUÌùÚ\'è«]ı˛ßø˚ª”z˘Ü®Rq®@¿ø*∆VâÁ∂3ffõãÕÿgéE©1”Å˘˙`È÷≈*q{d—ÏÏ,ßIÄ!Fè…\ZU±\rdQx8‹<4;á·9ë\'EHØh∫¡[\n–ÅM\"â@ÑÛ§$UÕ®|Ü¢ÚI∫û—^∂[î¢,ï∆Ó\"E,LüØÀ‡ì@cãÏ\0¢Î}\'ƒ¥Í“\\ä+kÊ∏]˜¿⁄—Hö»ùî61Z≤ öö*â!ju∆˜∆ Y÷!⁄⁄\Zà6·∂Í˘r∏\nQ⁄¢Uuv˙AÌΩ\n?ø7Ùj¸ÿﬁ0ÉH-wú˛ˆ∑ØWÈÂ;;´Ud`É\"FVW#1N“L#√P-Ìo≈£]<Dﬁ–:È*yÇÇëU)s{.&4º™Ø,\0CWóu›C2D‘n ^˛ú3;Q’{M’|ü*À(ªd9-Ii\\t …€≤∏-À€|öÔQeµGíz2>û?øyó4Öàì9›\Zﬂá\"ûœ°íµÈ≈ˆq,Œx∫Ãr±)Ú;ºíiƒpÕõ;Êò›KãµqEÏ¸ÃL ·-\'Nt{ÙÖ∫øD§Â@†Y≥*FÕV(/Â≈ :\Z∑ Í$1∆cùIx˛jße™:ıÚp◊©˛˛v/B®“ÿ∏3Eé‚Á_`Cô6Ëö¥øv©ŸíÆ¡ıı`©b»DvNcfV#öS¥µãM◊‘˙⁄Z§»r’…-Y%6ÎÜ®ØG◊9üa‡0.]◊t{.)é S…MZÂ|\0ëèœh\0QåB4aôFCg‹G	näò6¢Î\\]\")Çãçmv._¢óÃ‡‘F€`≥r±©Ïx@ÑkÂË÷ºy≤@$[ùçe3t˜˜¬ÅõÒö5ÀÇ(>|‹ÇË‘©µN\"¨·9ÊèøZ÷‘—8Öhè@¥Ùıo~≥\"@!2EñQÊ3f\Z◊È(\"xè2K⁄7¥L8UÚ˙°\ZsŒg“-aÜYXhjZy§∆æ£jŸ	(ä¢Æ)Æ∫zN◊ª\"F•—◊„´ì°ææ«pı¯2|}=‹¯Í8!RUú ç˘6\'n° \Zœià‚y1√à\0‰¶»µ†KpÈ5ûT4^„VjÎQUÎËµ√–‘,ÊT˙1	≈ôQ⁄lˆ\0L–G§JKvCtò8J…—úB4VµGíb{ÌC{¿∆X;⁄D~‘\\ÙÀΩµSU†„\0\"øõò«™N\rùJnç·ü™=˚^ª_Øòﬁm˘≥?k~¨VÏÏÃû7L\"√9\rU*∂7ÑÂæÛ4:t˘j¿Ì)ÂµC24ÓúüŸ¥¡)é≈ΩŸtmq°ˆ•∏ÓΩ™Ôà∑&ƒtÍÂ[ØG’}Q˝úJ “£]FΩ†˚§pØıe¬<rS/£$™ÎÌÌ≠≥ JN‹∫5*∆≥ö„Öb¬(∆Äbe,ä¨‘©›…HWõÒv.èt›+Û\'NàÁ9=À∂ŒÎãEIDÙ˚∂”AûbMN›øøD$TmgÕˆ»ˇtÒÊ{ƒôö¬lY)¢c\"0ÜîLÓm%˜í™˛haaÒ˙ıÖepìÌÏ(S∆¬qS†Õ—÷yöõ\r”N˚KMcŸÔÈ¿ÒππJá£Ê6è±πmp¿îÖÖ⁄∑⁄Œ∂,◊FòOìíDKèsò¶\'ŸzI÷√]ë.úΩ’◊ãj≠˜¥˘8∏B»w∏Q”¢ä´J∏zQôD≈5ô√PÒÉßÌ(r,\Z∂eëÕ NÍU¿ç•™qﬁËtgTW∞€ÍãubéQ∞â∞ØŒsÙd1Tz@∑tÜÏâlê;∆á¨ºô3‚ÅÊœ¶∞,üÉñßc´◊]ö¬N>x\0Fa{˚’i)Ò‡¡áñ*é%U‹÷£ik†û}ÀxÈüãÇ9&W9pXpI*z`fÌ[ããm/’v”\0∂R„àóèüp≤ B÷Ù∫ÆàÆ!D\ZBÑÆZWÓfŒI‰SE>ÌRg\0$äÓÊ?ÃóÌGëmZÛYπr¨ÚúŒ	√lk¶&?¢Í∆y1›Q!∫.;≈Í,Á¸“≠ÕœÕπ”µûÁchﬂ<GIá{Êß}ÿøØ»%e˙“µééÀM÷ÖçufïìÑ¶4›JK©Ø&õÁ»yFóA43k®º\"¶¥lÌ\'mD”ª±òN2DÇÃr˘l)ÅñÈ:ß·Ï-*âz∫∏.<>_¶.&íH\"….à>.NfóÃ≤rÑíCÁ0§ê¬◊ÿ2	4.Ò¥/÷‡Rê¥ÒJz:‡ÌñUó7ñUW\"ät¶æÃeµxÜÏÛó{∆ßÓÁ)\"1^Ñ°ÏayMº§D2\\%CÖZËÀ¶Zåƒ.ÿÁ∫Dú≤|UV:B÷∫\",8„ÿ~L»Å÷W€v·ÏÓÙŸz≈»ÙÈƒºñ©ù@º|¢\'1™·(û”Â∞°á{cÜÌ\r˚2€Ω\')aUU)Öe9ú	+2<¬”√Y«d)eQƒd¥Ω·^Ê±¢ÒJ«∆Ì∏f´:0:N∫;h<ÀŒóà1˚Ï≤ÌV_,£ã@ÙË—z\"ë‹¢vGrì2twjs3A≤6W∫\"∂ÀA4T¢ÉE—ƒ\\≠.k%ñÖ|i›ØÃ∑45óå˚û[ŸœÁÓ•ö’Ùf(dÖ.˙éjœZjkóñŒ^∏P´>~K—∞\r9í»†D\\…+[iôÆüL‡\"ÎE√úØóÌıìHƒPµà3«1\0N?≤0˘˛˚£.ÑHı‚~YÆæ[£HÏ{LçKñ˛ÁY∂”†—\"ﬁ®w€´€ÛR©cˇDÍ Dºî›ãmÏcû=Œ<†$ÉhsçéôªüÑ«≤œ1q∏^LÂÇîL—8Båª«Ò ≤D≤∆KÛ- ÇAW.7¥.üWŸœv€-à¿øóÅ‘…Ú|K”’Ü÷Öï›•≥Õãªªæ≈ÖÖ´õ⁄ññ	GXmÅπ¸îkSì•¶ñõ¶Ü{√zZ∑Ò{|,â&∆¡õxﬂ\"ÎÒ„C$Œoà°âÔÆ∫ÁËD\"≈ŒÖ—æXˆ#z†[çúË∆ÆùÈ¿¨àê≤ ≤˙bmÃDO €€€áHjao,û%â)Äh∏*ô ”ˆ™:…≤ZÁì&6i∂˝Ö  \ZÊPÄ¢`1ä\"†®ÖÃy@ƒ≠¥f‘‹7ûìo=C¡ˇ∑<œlà-Èì5-Æ\0C∏TfeÂ◊Æµ6]æ‘–‘∫∞¨”ZêãA§–ùõ†yjzì°C|.CVy´°ûòÖõÉ! *íDä¢éó.m_%çBúÀ˘rÕ(÷“^Nçò*òu\nÕ0òú	3∂˝)8Õ÷ë∏Yé7∆D{…µ·dr\n!_W)TuÓ%„√k[[√…·8®Ω¯pÚ≈!*Ú¯‘A\r·Gâ¿“ÿÿ‘Fé¶ $®©oúˆG”⁄¿`Ëê™Ã~™¨`~Á I`ÀÉƒ£œvÂJÎB€ÊÙów\0ˇ„ÉóÆ6-<√æ,r%%‚AÊ@d´\ng!#I‡yÑ\"K≈oUhππ)b\nãX4À§c—=⁄¡›+©*D∫)(:À\nZi3ﬁ∂§Ì˛G&ﬂ‡∑\0D∑ÔSà¨®ﬁ©™S«◊—ﬁÿ±ˆc$ƒåŸˆxN∂=¯çÉË@Ö6LNCÜπ°1§¢(îV¶ˆù;Qî°âqóIÑ˛=\\áBà ≈÷ñÂgª¿–RÌKµmo5/^≥_IcCkÀR\n√‹¨E„≠∆!≤óV9íè∏{Ø%q“Eéd\ZgÕ\"V£°(¢<äË–8“(‘&IéUÃäÆ)f( ’m√LØvúP5˜T,+ÂœÍâÏæXîPûôπ—˚IÏ§ôröbO˙„¢ŒˆdúBO∂”Ô`∂=/\'Öá/\0—P—oßà^\\0ùØ4\\≤¿±è+ç\rMqWŒ∫ü=Qî‚1Í©˝!rcD¸{Ög]æt±eI¢„>dË¬¬Ö.Ã…^vΩ¨+ó/]EïÀ6‘gI\"ô9\nº{5·6Û¨xß¨É\n¢Qú!Cæ-BQ>DŸ…lÛ$æ¿ÜgŸÖ˘XtÕlk¢g5Æ€€——Å„õ-Òc@\0ìnµƒ⁄≈é\0—(B‰®3\0∆¸‘prì@¥VU’πÜè±l{2I≤Ì…‰ãBT–√/M°5cüB€’∆úÎ’:\rß)ËnèñÂuEYü⁄è†¬Yø¡ÓlE≥ö{êZb5¥Œ[AL7üÌ÷íô!µíæ(Éπt≈Mv+›.^®&ç~¢]ªæÒ¬3äAñ5ôD4n©∂!rSî¶Ò\"é,Úh¬ëjŒÙã]s∂‘¡≠ƒ™V°\ZjV?öD√í\"…û®Jﬁãg‹Ü»Rg[c…·cC…ª	TgkÒŒ„ÿz˘X≤”ÇËh‹Ç®§°zá>†®∑s–3≠/5]vπÒ“≈6v›Ìí≠¡˚›=Ä°|àÓ∫!\ZΩE§—l:3;”⁄`!$„÷#Åx˚‡ÕÔæ’^X÷3⁄b”˜Ø-¥b†¡©Ò*© te—≥j|$ﬁe|ÛVÅ£ÀÃéSk\ZÏ!ã¢—!≤2 1âïI€d~˛\n\\˙¨Y|tF1{AÍŒÜ {;ô4À≈ZÚà6!DNs?Ω„µ!≤‘Ÿ÷—°xïüB‘˘j‹å@D≥Ì\"ímˇˇQaäàQtq¸vlv∫µÈbKõûí,à&d•Ë‹‡©É‰chÙ÷-Ñ(bnœíl∆»UËXH5öÔ≠Ê⁄ªKæﬁt∫Ì˚≠8Fl∫µµ±ëÈ∂+óöÊ66äH\"ÏIµM\"´e≥©6\nñã≥·ì]¥ÇYÒî\"âß˚ÒûEbY£ÛIìû]Õ8˜Ë—#≠€kj^stÜ§#NÁ]\02IÁ)î¨¬π	Vúã¡:Aù%∆⁄Yj˚ê†Ÿˆd“ ∂ˇ!∫øüî∏Kå¢k\r„ì‹|lb¬ôª±ÆÑ÷››ΩR÷d®8Då!µÃù{}qﬁñÌ‰<\n∂¥úΩ∞€rˆ≠∑|Ü˙pô8cœÌÆ¥±‡˘Â´”3E!íDõ!G°}ÌXDÙﬁ$£iúi∂ ¢E\"€OD§àìÃE‘f≠Í¨Âé93äŸò:wˇ—éó7∫ª5lh±„E¨/ñ7£ÓÊ~\"»\0¢â)⁄Ñ±«\"÷4wºõH⁄-®¸ìÃ˛Y¡…éê˚j¸~!⁄á\"º∆h50≥5oA$À°µıÙDv|Ònp}Ì Ñˆâç3à0Z4´™Ñ°∆¶«§ãÃŒPí”(òfÛÓ“Ÿ6–i}˙/ìî∞ŒÒX&æH}π∆È¢•\0\"b	Dÿ·Œ(∫Âegç:˛\ZŒät”$A¢E@({||ê\\_\nÁıÕÃÏÑ;3QØ©Ú∫ñ=/Ñw≈âÄ®‹<ƒ&Áıæ}V·Ü‰=¶Çwq€¬\Z«i`≥«ÒÚb[jê§›≥.H	`dmbòÙÔˇ√˚RÑF—Âfà÷Âıue2∏vwr-°É•P	Çàh≥®Å[C¡úßçúµû#ã˙„ƒ∏~ÎãmDéÄÂ@j”fZh∑I„“∆FqI$änIDèî)«ê=‰Ó˚Ôø/\"ëXÓ<R§>&E *«D©kºß$eÿÂJ\"›F¸ÁeUv3îíx¡ S:3ä%≈31ë≠rñîÆØ\'ïhTë“™º¯%)Å°D\"·ÏÑŸ«√œÜ®êQCå¢´ËÅèD¡P<òéO¨M¨=BÖQ0_õÒî°kÓg7,∆2¶∫”ı\Z◊ª+WlàÕ∆ò@ãD¬ÕóàF√˝ë!⁄FàdŸ¶\'¸⁄ŸÇ¡ä€.⁄{πá)Çˇ≈¥i˛Ä‰^”ñ≥û5:Ô933\'gz_9¿e∫*˚@«a¶¯òûµ7Ö4{ˆaãíÒU(—UMU—*zQÜ(1Y=‚@4|\0D˚Sƒå¢â€ìÎ°¯ƒ:‹Æ≠ªﬁD1ÑÚ+2Û!∫-≠›u—fî°iÙjhG˝,í?í≥˚_-FÕµoÒ∫¬Ûj:âp¨©≤ÖP‘dC$‰A$Y\"»ñCå\";>ƒß?nŸÔΩó.¢∫Ø0D∏ä=?”$ïDV™(´-≠ŸhÛ¢¢´™÷µS€´%{@ä#˚®ô$ñ%‹Œ»yä!D\"-Ñ!QV¢\"JÍÍj®ÿU9ò°DM\rH¢Í∞oòË`Ö$FQﬂ›	D¡¯ö”@”ìI˚=E®ÜÇ∏?i-» zµYã’Î\'—jQŸô»ãa80~Ù⁄^ú¸⁄ó.4◊>3]áï2Ê %“Çku&Á™3Ir,j¡\"∞»Ã¸∏eìáôI=˘ﬁ{∑ÚQ1àP·µgt$´ñï≥‘R,ìe˝`qÅ˜ºWıÍπÉ∏E≤2ÅÃq^í±w¡s0C¢®F´Å¿™ËCE.À¡ ¨øz2\rLqT$á_\"E}Ëd4€ØÛI÷(NP°ÊÇÇà7÷ÉDΩwI=)m”¬ÈgíÏnøë7UÍæÔtezj€jjõ1≠§≥Ón‹û6VËo‡q[8Q¯\0s§ûé4Êª.7mÿ‡È\Z`çŸ›“Ã»~oºtà»CúAW:≤{°0ØYCìx™´’házû˜ÚZDü¡M√ºú&o[ïÖXƒSXì›u3$ä~?Æ)¸j®Ö)AôıÉJåPù6Â/Aßh¨MU{7¬ó\rw8Ñ\n¢ÜQ\ZSÜ6ÆSÜı¢¢”`ã2âi-C…t5/\\®=ãq+¯òF#8k¬Tvü=3L⁄`±Î‰¬\'∫q€<œe1‰»°\"b˚π1h¢—ƒC@ÑGLmc˙NÌæ”ì5∑`~è|D4…´Û\':T\"&|dÖè°√KèF9úò!xJ`à`eV¶t¥P¡Ks∞2´>9\0ùÏﬂD}∆L\"∑©}(ä.bÊc’∫%Õµùö*\r\"W˝«qrÙ6DW.≥‹53Eﬁi	î$3£¶6ˇ@ã|ge·≠∂≈66\"ç3e”LGóqsˆ“/@=•ªûÂù√Gò“”g4ã—ââ ˜Éàó…‹Í”ˆ	ß\0õÑÁX>\nÊ–:;’®I¥∑§G≥Ö»Q…÷û¬^ŸƒÑ≥÷Fd£5±‰≥ÉS&]úîŸHÕ…Å3\'â…D(44IIÑûã¢fÃE∑qLïë#à(F‡Ôí	\Zóñp€kHNäÄ[—¬\'l{éôYÔ´]\\\\lmX~LÏo¸»»‹¸¸2PÑb‡\n.bWÙhU\Z—f≤lFsS¶‘ä‡œ8XD| Q(¢ÿ2ëﬂ”t“fd(öÑéóÃÂÆç√m®iSv,K¯p|∂Qßàû\\c¬Çà2Ñcp¢√ñ¬˛ÓG©…WÁ\0A4^\rb(1RS=íÏé∆∆»\ZOxx≤4Qî{±iÊ„≈ /Âfm¿µªXO JYO)ΩJ≤L?“\0\nGZP5mÂ≠◊\Zóñ…e‚ı(x˘ªÛ∏ ueÅåïjù_y∏kçn\'£ä8DÓéhG$9˘b»-¡ˆáà#·Í¶ñÀ±¡‡±√[†CNù∫¬ÑèHXÂóE»‰ò\'◊\'æÎ^ØGòà bs§\"≥;jz2ˇÚ àŒúFÉïYq\Z´	·‡!|x_IT#Í‰ø(B£’„∑≥ ⁄X ˘^÷åòÇmäñöëØe%ìôùaﬂ™ä&t„®≥&ºcÇi>[A.ÁóøO]¥y\"&\r‹1äÑò¨o∆Úâ…*ÆˆÖà$_[TS’Lç√ô÷ÇËö;ÀH÷|X∑1”ñÂc©A§§<πqïÜÄ¢\0ÔöhﬁQü^%àw∫fsx˚…ƒôö3(:øÒ`à≤/8uÚ_î°|mF´à,å0–(£˝’∞Ãïä=§”Ípïd3=ÀÍ÷°ëLÊÎÈXÖa˛Ç¥/=¸^#Ò∞s!“π¨~!Àêéπ‘≈ä©3õπ} ‚RJ§òÍlT@ïñ≤ˆ:T9ño˘D3π∫K…_÷&{Ú¢ªwùeëqZèÍÇH§û∫B˚CDRâÅÍq*àF™C…ööëÅÍÍƒfhdú˚Hë˚íS\'ˇ*⁄ò4JÕÎ€‡ö= ñDìµTñä‰(≈æÊ5KõëA À\"eŸ‰b†<û=úûûi[|∏Ç!À+çœ∂˜ÅHÃ.∂‹B u?OüÂïkˇ‡\"éCï§®Â–€C#πh⁄ê≥›.:°±¿RÄº±Yû<ø∆•h`ÿÅ®3/TTöãO¨ËëƒÊ\0Dg¿2\nëø«´kjjHF$ë8#ÁY\'â°ÚÇÕΩ≤=„ â·ñ∆öÊlíRV¥\ZN≥1ÎÇh©—\rQLJ√I{¯7◊Æ}oÒ·¸<∫GW.´YıDy≈,Ñ,©ƒSc;¢l’VDóÅ6#mml;£ÃÊùÅƒ‰cºLî>∂ÂcãY¯¡t˛§5©\0Dyß≤û\Z)\ZÏ∑«\"jÅ‘£ÁŒE4N:S=ô<âQ£ÍÒ‰È3¿R2t¶ÊÙ%)O$æÏƒ…{!Ñ\nô’YY≥—Ò€s\n\\¯+ó”ïƒ¨vµcëAüÜŒ∆dÃÅÌÛ\0ö.—\'Pt8#\ZLÎáﬂˇ˛ﬂ<\\aÀEXƒ)¢\\9BÒqïZ3†\nõ\\UX¢Ë©GcÛÿ∫\"	1éd‡\'£n£Ö™+ÂÃö§el:üQIîwÊ\"ß“\0)\Z∞∆\"ÆfºN%@ƒƒQÕÈÍ3õõ’ÄLˇ…ÕÕÅÍÅ–x(^õ°˛PàI§ê#õ\n^¯ñÀ\ZESyÔ;§ÆO:Ω{Ê6	]neß6ß\nÑ»$Œ‘g¡âüüõ!(!D\rÏ\\É ò®!>\\|¯êõùù%˘ì∆&˜ßz[œ⁄bË\ZXÂZ˜ “»rx*ë@Z/Ì\n¨8ﬂ‰∏(.π7\rMÊAyŸ>æ3ÕÑˆ\nXü#âd…ìÚC¬yv8´£˝t∏”jﬂÃˇ\"!√˝‡öU˜\'&Oˆ\'\'„ßOé\'˚kD \rTüÆ£âl?=≤bëÆ˝ÿ•˝ç¢	*P2\'X}≤ \Z≈t6j≥KKVÈ1´Ìs»Ü>ÈLùÂBƒÎ¯è¬\râ1ÍiYñ€àq›BÛD¶»j!àlFük1Á∫sCDCç\r8Êº/‹ù÷ö.¿¯õ§Á √gÛCånÆD÷*æîDs˚ \nRÜ∞Kœ¢h†òÁW˝Åππçx¡´U*D˜C„C®÷∆k™A´m÷ÙxàóV3ê¨9\rÚ©ÜÃ›ÓLıôP\"DRÅÎOú¸Ctà∞¿àÊÔ¡*z ÅKsÂ*ä¡V4)¨∏ö•a˘} ‚„kvx∫ï‘¨òòˆ@™$UÂ£ëH4 ∞ä†(∆Á°shà8ù8jÑ⁄? Œâ:”Q˘‰\r:í(E£Ó˘Í,%a\n—ï€˜g»E—ƒË‡P 2v˜Ó˝ÁÃù9«–SV!¥≥´˚˚´Gí®‹¿e#…ÇæúLÄ•îÿú<C¨§<$0h})ptDOÿIJ?Y√S$œÇD¢UB»ëE∫»≤\0DmY¡Ìú&Àa÷ïJj\\ÔÍŸ©ì2µç) aä@E§ïÕ’@ƒGwQt∑‘˛â\"?.©Bmh’û&Ÿ\Z^‰<äx˜‚õÇQ{ànä&&‚ÒÇ	™CBƒö…Ç8∆˚˚G6Q çÉr#1$êJDù¡wubsºø†i4Ö›‘ŸôèÉ‘◊˛YÇàq¥F◊`„n±\0\0?IDAT;A\"fV[Å{Ä]D≠Wr ⁄¿YFÈYÚ+pîÏ“e2˛A∞x^ULƒ¢drê2,A%#w$ıosÖﬁõ>*\"pø¢|\ZS¯çã:OÙó≥Ë(eØDí$Úz…LŸh9Ÿ}◊ëkYÁCƒ±E¡‚ÂÇSáÑà\"∑âDP:]}≤s]∂ÅÍÓc®>3û’‘$7õõñøñÖ\0qÚ/ñ`˙ÿ—ì¨£í≠¶4HD7a¯–’Óc--\n”Û8≠=‚|¿óhâ\Z3+xYõµK\\™ptàañŒÊ⁄Ú B~8Å¯Ôò¬ø¥ÄÌg≤≥JÀR_L1s?≤Ì£·¨å<àD◊IÄßàìDŸM≠ˆ:$Dk´)Jõì†¡‡Ûá‡Åì˝˝\'´Oû	%˚íÓíã\"$∆û¢\\m\\œ>S(ãqwu„ÇhèÚKπä´≠uñ˚@ƒ=yíVÊg5k™4˝∑d!vcÎ∆—Çıœ≠~Ìπy0ﬁyBïùßµ® d©¿\"\\1E#u\ZUñrçª∆á÷-Lz–=x›‘Üv!&8Ú)\"íŸèÚ€2†^Ã& •hÜJÜ®T\":gè†T3BQàXG…ëÍÒ–…ÏÚlÎ)1h›ÿW28˜ãEú,AÑUí.ıOñ+:A\"v∂E´g±4àd ö”:s öo¢.\ZÌ\"‚≠>8¬0ö\'?d\'R∞u¸3ù*¶y∏v‡ûÁQïUß$•…7\rm;ôçbNÔJÖàd]ÌäímCªV˛9Ú…u`“òãƒY%Èë¥ÃsÖº≥x>EìE*v—~?…@¬(∂ê÷“ôM0å¿:\neá!·)©ì_û\\‰Ú‘aà@‹:°6õ&A\"ñò$fÈı†fµ=M’¢@@çZ„Jì\r›◊Iw†	÷∂™˘ô´óù’\Z6D∑oª!‚!÷ÒHüY¥…ï+P_:äV1ZS\"Ñ≤êJ£Üø_¿Açﬂ_…âY_0Z≤Eì`?±À«w3∆·àÖÂÜË˛Ôí_~˘≈OÔM‰QîÕ–Ôæ2ˇ˘√ÁÉË†ÉÓœõÏGﬂLm—í5âππ%l$Qîû,àÚtaÒn≥√ﬂySNê»&Õj9\"ÚΩ¶+◊öZU2€áú˛«ÀÀÀ<é∑•V9ù≤LåÎ∆e	kœåluëë°3ÿ¬O]m\'j√Prã¢s¿báú·]T°üG*‰yÏ\'Ÿ◊≈òè∂=[Aâ∫*±ªjJM≥\'F˘D-ü,·çŒŒŒY :MmÌ˝”á¸¯3Û´œF˛—Mëê+áÓ=5ﬂ∏˜ÅàíDí˛‡öÅè∂Ÿ&qÜpTMílé@\"N~¯`Ÿ7Â∫{ D8ÈWfÇh¶ëâ\'ﬁ€VÇu§”RJ’¥X.D?X\\\"Û´hjshÀ ä≥Æ™§ª]≤åÎãK>åôπëú4á√D\\Ã§\\Ú»\Zmo?\"ª=2Q9XÂ ‘\'@D≤ØWV\\B’*ËHëı5\"˛‘\r|ß≤¶ëâÄ*>1®.] ><=Aèr!∫ˇ’”w˚oºÛÆ˘˘çAó,ƒº)QOÕÎ7Óπ*¢ÒC¿ñ\r$âit¶JböˆÙÈ‰‰Äì©\rì∆ÁÉ(W-gœpò\\«>“‰⁄,MÜöiŸ©:ß´≤ ´€yµ’˚|aÛì¡|í±t–03€Äê\"Ë(§‡œBS+øÄ£0D©9\\-·Ë0Àaím™ò	„¸Ù§úY˛TDt˘˜¯,—Fﬂ\"C˛Ω™wTPàö$´ﬁÛiïß¬«¥Öñ…Lp—…KsŸÌ}u˝˙¿‡ù;wˇ·ç˛A[£ÕÜrN9@ÙŒG¡Cπö*6bØ0Eâ‰¯hµöì5!¿©˙ÃHMÚÃ…öìD4ì≥ñ\0Q^€+	5— ë&¯|æ^=‚Ø?πQeΩ¿“}\\⁄‘76·ÇZ¢üÆtıd2™°“Aüpcöi=cp>ú˝	wtÌÒ¬‚‚ Óä≤ªõô@5øÒ(ãJ#ÑNVÿñ≤∞béîk”\Zá:3ªLõÖD+¬Ë˙@ËÅn∑ D^’Ëê¥Ó^ﬁ+ŒÎL¯Ëf˘∏Å™{áø[˝Œ¸ÒôÅ;‰Ó ∞˛6ùñI‘a2¯ø~∑6yá|o*˙i(ÙBt/˙]Úß!àÏXÃ9H2ñÜk˙Cß´’˝ì„∂«Oçï˙€¶Óó(à0˜:fâ. ¢◊^ıÖ¡°÷≤”Ö¨v…Tb ««ä¶Ô¿°:©˙9\\ﬁ·ìîz=ZØs]bèØ.l‘üÎ1zÍÎ9£NQ+œÌ÷˚Í∫L)¢ôúÃª‹éﬁBQ2›ÃßÒÆôZF©ÊiÀJr…!Ö¡¢9R&~•Q§C@TÚà˝Íÿ‡º∫.·.-…Î˜ÎÅµ#ÊØzœÎ*>|&¶‰o`÷ôÁâ∫∂ ⁄3Ø_a˜ÔLÉõOÛ©j üàì°Ωµèˇ…|:˝Î_òÊÁI¥ıØf~6¸	\"G aÆ\rìlâI”vú¥>¥.°É]3á!:ø∫çâD=ÄÄØO0xC¨ØÔRçJ=Ïà∫¢f¿7ﬂÂ´Î#}~\"Y˜Ò=}F§À®K˜‘IΩaæﬁêœ1\n#µŒËäDÎö[∫v+ÍñÂÇπòqÃ⁄Ví¯›Ô~∑e∫∑˘ªx4OO„ësLmÀ•ìS`EY„5§Èj¥7†íÓ]ç®Héï#ùô\n›¨H{µàWV“™\"O¡‚z@ô\Z|.àLÛ/®ôCè§9pcËoüòø˙` ¸«˛wﬁ˘±˘ÙÊùΩ/ﬂyÁÃı7n\"D[{7n|n~@(˙É\"&é\Z∞≤˚GFHL€ı-“~vÒ9 ⁄_›]\'V5ô∫ﬁòW4ÓúœWßï)£ß§äQ”{√F•\\óà∏.≥+<_øÛËÅ®·\ZëDöO!µS©˜ÑÎ{˚|©òœT+¢æ^\0„z∫¬]=-ªïããı-mﬂõûûû+(â‹¶çK©)•\'˚hvz\\Jç#[ÉØ\\%:0oß¢;–È5@èqF7HúW‘äÌTáﬂ€≠GŒ{uı\'wX∏\nAƒ¨Ú|àÆ∏¬?Êgoº38ˆâiﬁ¯“ºﬁ?xÑœ–áÊ?ÙﬂÑ;?Bànö_ˇ¯ã/áÆﬂ,\"ˇ¡]ØEIJå˜◊PC€˝0πÃ•È≥í5´„Tﬂ€A\"êDöıïö·„pCG%Ÿ^V	≤\'„„3Á2u⁄ºoÄq “’sFØ?™”g}]@ Á33¢0@Tü÷|ızΩoiÖ@ÙÉÀççó\Z\Z\Z\Z/¬ÅˆvÀºK©πPbÚ»,t0+…çül1tÌ“>\0øUS+¿P{%”}>P¡GºÜ	™À–%Q>ﬂ±\Z≠–Ô˘LÜÔË\0øÅí»∂7ÜË+ÛGŸß5aÆû	æ¸SÛçw@ì˝ì˘ÀÃüÙèˇhà>0W¸Ÿ«7n†$*È2é<ó ≤)¬ƒ»ô3Ÿ5¥¥2Ì˜À–Ë≠	*àægâÄ\0˝¿éñà‰:dN¥À®å’´ âåÆÆ.`Jñ+”\"«qQ=≠—zŒ◊£ÌTˆÌ¯∫¥YüiV\Zrù§÷Ez}£æKÎÚi†Œ\"{¿#;\0™∆F†™·j8cy\Z±ÌJ\rE|Ω–!§ùb?9⁄Fjl°¬áB=ëñπÜîéz\"D›ﬁ>p4u`ÀËÓ6¯àNÊ˙´LÓÌSfù—SsΩmÁ˚…ßOüÆ}l~éø4Ùîä®üöø¸ØÊøÅŸÙÙÈ∑(Düø1pÛÊ»ΩRzAÑ‰ß¡#LÇ÷áÖ(x\0D∑Ê,mvÂ*ˆÜí%R=(ât£œËÈä‘Û èz{@4\\•dä»	D=}Ωº™À|oXN+}íô◊¯ÄiÇäÑ\r•7◊à„’ÂÈgœ˙VölàÚè|¨.^ljmkkEfﬁ|˝ıø|∏˜&ÅàUNª\"‹◊‹»∫ÉC˚XU›≤G4@´utg^—ÙÓn’6í~^ó—á‘â´Êd?ˆ)≥~ººåâ_ª≤ÒC”Bl¶&G@s\rÄv$h}k›∫t˝≥9Ù∆èÕ\'ÒÙ∆u4¨·\'~4·G•ªfœ-à\\qÑPVy\Z|èå |AàÚ´:6?GÉD¯ôS•ûﬁﬁ∞f\0*◊’q¡ãê1z5ΩWè(∆vùij√ËCàÃÜ+Mª¶a∞ÙÉAZÁABy\Z¢-?ﬁ›˝≈Ó3”|∂ªªª“‘ÿò5ç{ﬂÉQ‡k∆õkk[ÊÎ„∆ﬁB4=Ω\"kN˙KÆ[∫B∑DùV2Ω#†(i`GÔˆ\Zí¡u√˚¬pëŒõ¢™ñVf-≥≤4Úï—ùœÃØ>@˜ÏŒ àü˛ﬂöø%vﬂ¸…∑>03†≤ÇOˇÌ[7ﬂ˙7Û›Î7Ô!Dø5ü~pèÈî∆¬s¢©É‚‰¥>¿™ŒõØ˜‡Å$ãXÇÿ∞åâÓ*â‚“}≠ä¶“ê.n|1R]}¶é;\\UL|¶óööZ)@:@E°∑Bîï⁄zªÒÃºñ¶õõõõ.^º⁄–p©±4¢^}`¸ıµ nåë7MÑà»+b^QÀ^ñ”ÿf:≠dº¢GMÎ\\Ö∑∑√‡‘æ5\rÙôö.RfmpœDwYölY7Õﬂåﬂ#ﬁ˛èn˛∆¸Õ«ˇ¸Ù∑oÙ~¸’”è˙ØOﬂ}„ÊΩëõüõÎÔéLÌÅÅ4?ˇÂ_L~|N	Û¢\0·—WJ–zÍp ëÃÒ¸Ïúp˛∂xÈc(˜uÆO3ç»‹€ºÜª=#^ñqJ)·ê”î$Ï⁄Cê$Õx¶Îœv>|¯ÃÃoÚ¥èÉpÄ£÷÷ à\"H5^æí≥7\0è\ZÑ∆\0ñˆ^}–ÿã√ó5π\"À∫◊∞`Ç!}¨`j´·Û6\'qPú|	Ûdrnôu°¨¸hC¡h´›ØfCdùŸ¡÷ÕØû~˘Âˇ˘lË/ﬁπ3¯ÓO˛1˛¡çÉ˜Fﬁ˝‡óÒèo‹∏y\'î¸‚Ûß Ä>˙¯›wﬂº9Ùì_˛¯ÊÕˇ ïÑ–‘qÚØN‚w¿Ùπ°‡b±sIÇs¿LZ&˘ç§ïT«ÆËC¡—ålW\r∆\rnôπ˚T\\ÀÇ¿Ec~ßR5‚„4=Õ$íƒâ\"oJº]KÊò∞X#õû&Pµ∂6°ÁÜ\\1∞|\0Õ÷$‹$·Œ¯†ÒóØøÓ+¨ˇ.5Ì òâµª’Gèûà¬e÷VT”UfM“e ã7<ò√œ]ˇ!Iº«9ªÉ7ÆˇË›õ7nÙﬂ≠6pc`‡&*∏oæÛŒ¿Õ¡{I3ﬂ¸•π>0rs‡Êá˜F˙o~Ù˚û[:BS¥2Ì“ÿæÜŸ‘sç à‡Ñëí∑«léò&ÙË™÷#ÄAßŒßj‰ìÆ\Z\\Aà∂7ÿ÷,DEü_FáNÍÍÀ¿˜Òè˙0T\"çπÒ!RMññ1†l Ö R$QÃ©RIµ⁄p’ñı$±ÉÃ7_ˇK¯ˇ◊†∞Ú‘¬M≠«Bçó€ñ$:Á‹ì¢Ò££ £G)&Mﬁˆ¿—©¸ÏhŸ—ü)üÒîÉπ)#Oˇ∂á¢Ñ?Äèã¥A¶oú«Ë¸xrû@∑òÊ1;Îqùﬂ;É\0«Õ¡¡í‚∏wÁ√;,¸HÓÖÃ¯ıÉÉÎ—>¬|«G~x/X\ZCC•´Û@	Êˆ˜Z»åÀŒ~.A4w{‹ù5#ÇàtC≥ötæbÃZ÷}1j‚hZ”f4“\r\rn\rSﬂydïòàp?π“€ó©qÑAn\\]ﬁï1+U\Z‰Î·.æÀW÷Ä¢®)ªjZ]Ω‹ZÈò;;6hn≠çà^ü$Üµø≠≠πÕ„â7ƒïk∏ÿ∫§Ú1÷πÎaÆøG∂“s\0Ú∂¢tzﬁ.Ô‡´é*_˜˛œ™óï∑øÊ!éŸQjo{:3ùˇ2fgffß˜ï∞ŒîÙ+∫ß3R°ëÏ¨ÁÄÀÂø3ÚzlˇÚ9¸5µÔuÀ;∆˝•˛‰¡\0e°¥z…Ui˝\\a)ÿ‹ÿÑ´ŸØ¶<0<G˜¶1Y°˚xL|IFeF≈ùˆ–QØÖœ’KQ dg√\r—c‰V2ïè3=Ω*◊ï!An≥R®√–¸6_ÑØ◊”Á4g)¶B$Á÷‹ÛÆí)5∆º{‚Ï√Ì\0˛¡SÒ◊3}‹# =eºÁáûr2oòπÏáÈgÂûØuÇ\\ô˘ow–ÅÛº˝∂ßÃ˚CƒÂì2Âœ?)S^˛πÀCº^*â<~-˚˙üÔ|zº¨\"<>˛ÔéUÈbŸø«Ù¨v)˚Üü„xO…mÚ+sh$xˇw{ÔˆèL¢°Á|B(x1’ò]i}hàÿP´π1[ëù@4Hd°ilg=\\˝XóQiÑ{’@’{»\'ühv=zî\rH¢ò/SâJOU+3$»mV÷ıe2¿‚9µŒ‡}∫VI!r˜¿∫Ã’<à\\%!`¨åd\ZGË”{˛˚üÃ¸IªG9“.vó)û„ë„GQû˝∆\'«=<Ë/< É#Œ˘ÀD\nä\"qÈ<ÆxdŒÙÄ™ıH∫˛Èﬁì∆T≠G;°Î™ZQÓ=˛˘◊™*æÒÚÍÒó;éytèˇÌ€ù(‰JP∂ˇñy˙ÙÀ˘‡∆Õèá–˝…RF4t¡t∆åÎü=á bÂ©L·’†Õ”≤U.°±ùı}u Ç\0\"≤”o{{¢p}Od\'≈Í3\0¢Ugf%ÉËq]ÊúÆGªÃJâπ{|]ôòOcÈnà‰} ¢ˆ≠]®Ê¶hÑ9RÓ?ÃŸ(¸kGÀÄêü+?CG*ã(?˜H‰A…£l¸˝üæﬂÖwáΩ].¬˝O_Êï≤@˙⁄#ÎùÂ´y¿iêUOﬂ+^ê^—≤OØ1è¸ÍD†¨¢ÏìäÛ√”{æQV¢\"Wæ7p˝˙\r∞∂Ô°í—·≤fÃdΩ˛í! åv{‚÷D4H$Y5∞4Ò·„˚z∞®“ËäRà‘ﬁ>dÇ3î:·—÷F‡$Yïì0»Ìı\0Q∏ûÛı\Z=]—˙òQiˆë ∑P…e8¢LiânàH±ı\0çTø9b;R˛µÇHQ _Î˛9Å(Ä9UˆsÂo=<Ò§\0¢?˛”F<DWÅ:;\0uˆÈ±O$ÂËß¸Øè¯O®û¥˛\n\Z‹ûéUPjùﬁò˘uèˇè*ºﬁ2˛H‡Ñ∑Ã[ˆ´ﬁØDﬁ?˙Ø+Ö! ?€Ï¡è>£{ÑÑ©ÉPâÇËy¢Éds+≠K˝≠8Ã+ª„6K°cÕEbdêb,çŸDÈ∫p∏ \";Ìô$ÍÍ”}ÅX›Èôô5I%¨&˜ˆˆDïL/(Ûj†á◊µ>3”´ÖC≠”t)¨iΩ\"ÕtA‰äø‰∫gbÃn] /≥f>∫g˚Á‡lyî2‰8@t4r¸8æçc«~ƒ#ëe/ï˝ó¿qÑ(Ek0ùÂü¬oyÌµ»k«#Øtx‘HÖWØËO¸~øßb’ÔiÔ}Èø:~TıÌ®8*©˙’◊@ùÈU«›~4%âÖ!:Ë8C•	¢ÁB(»fÃÇ≈^ﬂ˛Ç({W0D-\\¢A\"rmI÷‰0@W_ÓÌS3ΩôÓ¥7¬≤Œ≈åtØ¢ıı•I=ÌÉç(ÖKiK ó´ö¶íµòöö1Õ¥°rWØÜèjöÜªe3&í{¸ÒÆíkæë@\\Ø¥PF˘·ë2∞å<ÌeGâ‡‚é~ÕÔëËÉ/{‰ÛˇÔ†¢.~™ˇRG<G~·£wNòQèq\"”€ùÓÜoΩ÷-ü8^vD÷ƒó·7Íú˜HgôÍQ>©(;˙≥î4Û<°C≠>Bt∆ÃÂÊ`Nu)l“AÀ9Y-òÚ∞‹$A“0>H⁄kXÜå;Ì1ÿÀRLìÙâmêô÷≤’\Z%)ndA≤∫ò€í§s}∫Ív∫lÀ\Z(⁄v˘¯πS>ywu©3ú\"Z!ªÌ¥BªŸ]@bME#•≠Ó2kY\"›@\\îó4”xÖ˜\ZÁciÕ€Ì≈≤«Ã™Wåt¸˘éΩ[UU>}≠\\≤Úü¢C\"TR1⁄Ûä° õ1s’•u]Ø2˚Öbà¨* Dt^¨ª™ò∑:ODv⁄cÂæΩ\"Uß#˜≤!/ÕÜË1Ö»0\rN¬¥õõ\rDÀK¥üè/*ñÒCâ»*≥gë„yOo€Q≤-√llX©çúVD›T+ƒéHÖÊÂåHE∑±]°gt@Á3X¢†¶d“ÙVUÊ)ˇôÛBg˛Zóe[Û©Ñ9\0\0\0\0IENDÆB`Ç');
/*!40000 ALTER TABLE `filecontent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filemodel`
--

DROP TABLE IF EXISTS `filemodel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filemodel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `type` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `filecontent_id` int(11) unsigned DEFAULT NULL,
  `relatedmodel_id` int(11) unsigned DEFAULT NULL,
  `relatedmodel_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filemodel`
--

LOCK TABLES `filemodel` WRITE;
/*!40000 ALTER TABLE `filemodel` DISABLE KEYS */;
INSERT INTO `filemodel` VALUES (1,'200x50.gif',449,'image/gif',3,1,NULL,NULL),(2,'200x200.gif',712,'image/gif',4,2,NULL,NULL),(3,'580x180.gif',1898,'image/gif',5,3,NULL,NULL),(4,'googleMaps.png',39873,'image/png',6,4,NULL,NULL);
/*!40000 ALTER TABLE `filemodel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamebadge`
--

DROP TABLE IF EXISTS `gamebadge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamebadge` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamebadge`
--

LOCK TABLES `gamebadge` WRITE;
/*!40000 ALTER TABLE `gamebadge` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamebadge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamecoin`
--

DROP TABLE IF EXISTS `gamecoin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamecoin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `value` int(11) DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamecoin`
--

LOCK TABLES `gamecoin` WRITE;
/*!40000 ALTER TABLE `gamecoin` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamecoin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamecollection`
--

DROP TABLE IF EXISTS `gamecollection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamecollection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamecollection`
--

LOCK TABLES `gamecollection` WRITE;
/*!40000 ALTER TABLE `gamecollection` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamecollection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamelevel`
--

DROP TABLE IF EXISTS `gamelevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamelevel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamelevel`
--

LOCK TABLES `gamelevel` WRITE;
/*!40000 ALTER TABLE `gamelevel` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamelevel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamenotification`
--

DROP TABLE IF EXISTS `gamenotification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamenotification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamenotification`
--

LOCK TABLES `gamenotification` WRITE;
/*!40000 ALTER TABLE `gamenotification` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamenotification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamepoint`
--

DROP TABLE IF EXISTS `gamepoint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamepoint` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamepoint`
--

LOCK TABLES `gamepoint` WRITE;
/*!40000 ALTER TABLE `gamepoint` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamepoint` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamepointtransaction`
--

DROP TABLE IF EXISTS `gamepointtransaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamepointtransaction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `value` int(11) DEFAULT NULL,
  `createddatetime` datetime DEFAULT NULL,
  `gamepoint_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gamepoint_id` (`gamepoint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamepointtransaction`
--

LOCK TABLES `gamepointtransaction` WRITE;
/*!40000 ALTER TABLE `gamepointtransaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamepointtransaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamereward`
--

DROP TABLE IF EXISTS `gamereward`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamereward` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cost` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `expirationdatetime` datetime DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamereward`
--

LOCK TABLES `gamereward` WRITE;
/*!40000 ALTER TABLE `gamereward` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamereward` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamereward_read`
--

DROP TABLE IF EXISTS `gamereward_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamereward_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `gamereward_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamereward_read`
--

LOCK TABLES `gamereward_read` WRITE;
/*!40000 ALTER TABLE `gamereward_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamereward_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamerewardtransaction`
--

DROP TABLE IF EXISTS `gamerewardtransaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamerewardtransaction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `redemptiondatetime` datetime DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  `transactions_gamereward_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamerewardtransaction`
--

LOCK TABLES `gamerewardtransaction` WRITE;
/*!40000 ALTER TABLE `gamerewardtransaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamerewardtransaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamescore`
--

DROP TABLE IF EXISTS `gamescore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamescore` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamescore`
--

LOCK TABLES `gamescore` WRITE;
/*!40000 ALTER TABLE `gamescore` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamescore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `globalmetadata`
--

DROP TABLE IF EXISTS `globalmetadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `globalmetadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `classname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializedmetadata` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_emaNssalc` (`classname`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `globalmetadata`
--

LOCK TABLES `globalmetadata` WRITE;
/*!40000 ALTER TABLE `globalmetadata` DISABLE KEYS */;
INSERT INTO `globalmetadata` VALUES (1,'ContactsModule','a:10:{s:17:\"designerMenuItems\";a:4:{s:14:\"showFieldsLink\";b:1;s:15:\"showGeneralLink\";b:1;s:15:\"showLayoutsLink\";b:1;s:13:\"showMenusLink\";b:1;}s:26:\"globalSearchAttributeNames\";a:4:{i:0;s:8:\"fullName\";i:1;s:8:\"anyEmail\";i:2;s:11:\"officePhone\";i:3;s:11:\"mobilePhone\";}s:13:\"startingState\";i:1;s:12:\"tabMenuItems\";a:1:{i:0;a:4:{s:5:\"label\";s:80:\"eval:Zurmo::t(\'ContactsModule\', \'ContactsModulePluralLabel\', $translationParams)\";s:3:\"url\";a:1:{i:0;s:17:\"/contacts/default\";}s:5:\"right\";s:19:\"Access Contacts Tab\";s:6:\"mobile\";b:1;}}s:24:\"shortcutsCreateMenuItems\";a:1:{i:0;a:4:{s:5:\"label\";s:82:\"eval:Zurmo::t(\'ContactsModule\', \'ContactsModuleSingularLabel\', $translationParams)\";s:3:\"url\";a:1:{i:0;s:24:\"/contacts/default/create\";}s:5:\"right\";s:15:\"Create Contacts\";s:6:\"mobile\";b:1;}}s:48:\"updateLatestActivityDateTimeWhenATaskIsCompleted\";b:1;s:46:\"updateLatestActivityDateTimeWhenANoteIsCreated\";b:1;s:55:\"updateLatestActivityDateTimeWhenAnEmailIsSentOrArchived\";b:1;s:51:\"updateLatestActivityDateTimeWhenAMeetingIsInThePast\";b:1;s:15:\"startingStateId\";i:5;}'),(2,'Currency','a:4:{s:7:\"members\";a:3:{i:0;s:6:\"active\";i:1;s:4:\"code\";i:2;s:10:\"rateToBase\";}s:5:\"rules\";a:9:{i:0;a:2:{i:0;s:6:\"active\";i:1;s:7:\"boolean\";}i:1;a:3:{i:0;s:6:\"active\";i:1;s:7:\"default\";s:5:\"value\";b:1;}i:2;a:2:{i:0;s:4:\"code\";i:1;s:8:\"required\";}i:3;a:2:{i:0;s:4:\"code\";i:1;s:6:\"unique\";}i:4;a:3:{i:0;s:4:\"code\";i:1;s:4:\"type\";s:4:\"type\";s:6:\"string\";}i:5;a:4:{i:0;s:4:\"code\";i:1;s:6:\"length\";s:3:\"min\";i:3;s:3:\"max\";i:3;}i:6;a:4:{i:0;s:4:\"code\";i:1;s:5:\"match\";s:7:\"pattern\";s:19:\"/^[A-Z][A-Z][A-Z]$/\";s:7:\"message\";s:35:\"Code must be a valid currency code.\";}i:7;a:2:{i:0;s:10:\"rateToBase\";i:1;s:8:\"required\";}i:8;a:3:{i:0;s:10:\"rateToBase\";i:1;s:4:\"type\";s:4:\"type\";s:5:\"float\";}}s:20:\"defaultSortAttribute\";s:4:\"code\";s:32:\"lastAttemptedRateUpdateTimeStamp\";i:1425465175;}');
/*!40000 ALTER TABLE `globalmetadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagefilemodel`
--

DROP TABLE IF EXISTS `imagefilemodel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imagefilemodel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `isshared` tinyint(1) unsigned DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `inactive` tinyint(1) unsigned DEFAULT NULL,
  `filemodel_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagefilemodel`
--

LOCK TABLES `imagefilemodel` WRITE;
/*!40000 ALTER TABLE `imagefilemodel` DISABLE KEYS */;
INSERT INTO `imagefilemodel` VALUES (1,0,200,50,0,1),(2,0,200,200,0,2),(3,0,580,180,0,3),(4,0,580,180,0,4);
/*!40000 ALTER TABLE `imagefilemodel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import`
--

DROP TABLE IF EXISTS `import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import`
--

LOCK TABLES `import` WRITE;
/*!40000 ALTER TABLE `import` DISABLE KEYS */;
/*!40000 ALTER TABLE `import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `createddatetime` datetime DEFAULT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  `createdbyuser__user_id` int(11) unsigned DEFAULT NULL,
  `modifiedbyuser__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item`
--

LOCK TABLES `item` WRITE;
/*!40000 ALTER TABLE `item` DISABLE KEYS */;
INSERT INTO `item` VALUES (1,'2015-03-04 10:32:46','2015-03-04 10:32:47',NULL,NULL),(2,'2015-03-04 10:32:47','2015-03-04 10:32:52',1,1),(3,'2015-03-04 10:32:47','2015-03-04 10:32:47',1,1),(4,'2015-03-04 10:32:47','2015-03-04 10:32:47',1,1),(5,'2015-03-04 10:32:47','2015-03-04 10:32:47',1,1),(6,'2015-03-04 10:32:47','2015-03-04 10:32:47',1,1),(7,'2015-03-04 10:32:47','2015-03-04 10:32:48',1,1),(8,'2015-03-04 10:32:48','2015-03-04 10:32:49',1,1),(9,'2015-03-04 10:32:48','2015-03-04 10:32:48',1,1),(10,'2015-03-04 10:32:48','2015-03-04 10:32:48',1,1),(11,'2015-03-04 10:32:48','2015-03-04 10:32:48',1,1),(12,'2015-03-04 10:32:49','2015-03-04 10:32:49',1,1),(13,'2015-03-04 10:32:49','2015-03-04 10:32:49',1,1),(14,'2015-03-04 10:32:51','2015-03-04 10:32:52',1,1),(15,'2015-03-04 10:32:52','2015-03-04 10:32:52',1,1),(16,'2015-03-04 10:32:52','2015-03-04 10:32:52',1,1);
/*!40000 ALTER TABLE `item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobinprocess`
--

DROP TABLE IF EXISTS `jobinprocess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobinprocess` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobinprocess`
--

LOCK TABLES `jobinprocess` WRITE;
/*!40000 ALTER TABLE `jobinprocess` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobinprocess` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `joblog`
--

DROP TABLE IF EXISTS `joblog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `joblog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `enddatetime` datetime DEFAULT NULL,
  `isprocessed` tinyint(1) unsigned DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `startdatetime` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `joblog`
--

LOCK TABLES `joblog` WRITE;
/*!40000 ALTER TABLE `joblog` DISABLE KEYS */;
/*!40000 ALTER TABLE `joblog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kanbanitem`
--

DROP TABLE IF EXISTS `kanbanitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kanbanitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `sortorder` int(11) DEFAULT NULL,
  `kanbanrelateditem_item_id` int(11) unsigned DEFAULT NULL,
  `task_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kanbanitem`
--

LOCK TABLES `kanbanitem` WRITE;
/*!40000 ALTER TABLE `kanbanitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `kanbanitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketinglist`
--

DROP TABLE IF EXISTS `marketinglist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketinglist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `fromname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromaddress` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `anyonecansubscribe` tinyint(1) unsigned DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketinglist`
--

LOCK TABLES `marketinglist` WRITE;
/*!40000 ALTER TABLE `marketinglist` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketinglist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketinglist_read`
--

DROP TABLE IF EXISTS `marketinglist_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketinglist_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `marketinglist_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketinglist_read`
--

LOCK TABLES `marketinglist_read` WRITE;
/*!40000 ALTER TABLE `marketinglist_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketinglist_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketinglistmember`
--

DROP TABLE IF EXISTS `marketinglistmember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketinglistmember` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `createddatetime` datetime DEFAULT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  `unsubscribed` tinyint(1) unsigned DEFAULT NULL,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `marketinglist_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketinglistmember`
--

LOCK TABLES `marketinglistmember` WRITE;
/*!40000 ALTER TABLE `marketinglistmember` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketinglistmember` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meeting`
--

DROP TABLE IF EXISTS `meeting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meeting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `enddatetime` datetime DEFAULT NULL,
  `processedforlatestactivity` tinyint(1) unsigned DEFAULT NULL,
  `location` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logged` tinyint(1) unsigned DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `startdatetime` datetime DEFAULT NULL,
  `activity_id` int(11) unsigned DEFAULT NULL,
  `category_customfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meeting`
--

LOCK TABLES `meeting` WRITE;
/*!40000 ALTER TABLE `meeting` DISABLE KEYS */;
/*!40000 ALTER TABLE `meeting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meeting_read`
--

DROP TABLE IF EXISTS `meeting_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meeting_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `meeting_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meeting_read`
--

LOCK TABLES `meeting_read` WRITE;
/*!40000 ALTER TABLE `meeting_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `meeting_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meeting_read_subscription`
--

DROP TABLE IF EXISTS `meeting_read_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meeting_read_subscription` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `modelid` int(11) unsigned NOT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  `subscriptiontype` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_modelid` (`userid`,`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meeting_read_subscription`
--

LOCK TABLES `meeting_read_subscription` WRITE;
/*!40000 ALTER TABLE `meeting_read_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `meeting_read_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messagesource`
--

DROP TABLE IF EXISTS `messagesource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messagesource` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` blob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sourceCategory` (`category`,`source`(767))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messagesource`
--

LOCK TABLES `messagesource` WRITE;
/*!40000 ALTER TABLE `messagesource` DISABLE KEYS */;
/*!40000 ALTER TABLE `messagesource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messagetranslation`
--

DROP TABLE IF EXISTS `messagetranslation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messagetranslation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `translation` blob,
  `language` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `messagesource_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sourceLanguageTranslation` (`messagesource_id`,`language`,`translation`(767))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messagetranslation`
--

LOCK TABLES `messagetranslation` WRITE;
/*!40000 ALTER TABLE `messagetranslation` DISABLE KEYS */;
/*!40000 ALTER TABLE `messagetranslation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mission`
--

DROP TABLE IF EXISTS `mission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `duedatetime` datetime DEFAULT NULL,
  `latestdatetime` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `reward` text COLLATE utf8_unicode_ci,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `takenbyuser__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mission`
--

LOCK TABLES `mission` WRITE;
/*!40000 ALTER TABLE `mission` DISABLE KEYS */;
/*!40000 ALTER TABLE `mission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mission_read`
--

DROP TABLE IF EXISTS `mission_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mission_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `mission_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mission_read`
--

LOCK TABLES `mission_read` WRITE;
/*!40000 ALTER TABLE `mission_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `mission_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modelcreationapisync`
--

DROP TABLE IF EXISTS `modelcreationapisync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modelcreationapisync` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `servicename` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `modelid` int(11) unsigned NOT NULL,
  `modelclassname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `createddatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modelcreationapisync`
--

LOCK TABLES `modelcreationapisync` WRITE;
/*!40000 ALTER TABLE `modelcreationapisync` DISABLE KEYS */;
/*!40000 ALTER TABLE `modelcreationapisync` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multiplevaluescustomfield`
--

DROP TABLE IF EXISTS `multiplevaluescustomfield`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multiplevaluescustomfield` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `basecustomfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `basecustomfield_id` (`basecustomfield_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multiplevaluescustomfield`
--

LOCK TABLES `multiplevaluescustomfield` WRITE;
/*!40000 ALTER TABLE `multiplevaluescustomfield` DISABLE KEYS */;
/*!40000 ALTER TABLE `multiplevaluescustomfield` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `named_securable_actual_permissions_cache`
--

DROP TABLE IF EXISTS `named_securable_actual_permissions_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `named_securable_actual_permissions_cache` (
  `securableitem_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `permitable_id` int(11) unsigned NOT NULL,
  `allow_permissions` tinyint(3) unsigned NOT NULL,
  `deny_permissions` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`securableitem_name`,`permitable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `named_securable_actual_permissions_cache`
--

LOCK TABLES `named_securable_actual_permissions_cache` WRITE;
/*!40000 ALTER TABLE `named_securable_actual_permissions_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `named_securable_actual_permissions_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `namedsecurableitem`
--

DROP TABLE IF EXISTS `namedsecurableitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `namedsecurableitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `securableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_eman` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `namedsecurableitem`
--

LOCK TABLES `namedsecurableitem` WRITE;
/*!40000 ALTER TABLE `namedsecurableitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `namedsecurableitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `occurredondatetime` datetime DEFAULT NULL,
  `activity_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note`
--

LOCK TABLES `note` WRITE;
/*!40000 ALTER TABLE `note` DISABLE KEYS */;
/*!40000 ALTER TABLE `note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note_read`
--

DROP TABLE IF EXISTS `note_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `note_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note_read`
--

LOCK TABLES `note_read` WRITE;
/*!40000 ALTER TABLE `note_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `note_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownerhasreadlatest` tinyint(1) unsigned DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `notificationmessage_id` int(11) unsigned DEFAULT NULL,
  `owner__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
INSERT INTO `notification` VALUES (1,'RemoveApiTestEntryScriptFile',NULL,15,1,1);
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificationmessage`
--

DROP TABLE IF EXISTS `notificationmessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificationmessage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `htmlcontent` text COLLATE utf8_unicode_ci,
  `textcontent` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificationmessage`
--

LOCK TABLES `notificationmessage` WRITE;
/*!40000 ALTER TABLE `notificationmessage` DISABLE KEYS */;
INSERT INTO `notificationmessage` VALUES (1,NULL,'If this website is in production mode, please remove the app/test.php file.',16);
/*!40000 ALTER TABLE `notificationmessage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificationsubscriber`
--

DROP TABLE IF EXISTS `notificationsubscriber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificationsubscriber` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hasreadlatest` tinyint(1) unsigned DEFAULT NULL,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  `task_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificationsubscriber`
--

LOCK TABLES `notificationsubscriber` WRITE;
/*!40000 ALTER TABLE `notificationsubscriber` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificationsubscriber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunity`
--

DROP TABLE IF EXISTS `opportunity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `closedate` date DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `probability` tinyint(11) DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL,
  `amount_currencyvalue_id` int(11) unsigned DEFAULT NULL,
  `stage_customfield_id` int(11) unsigned DEFAULT NULL,
  `source_customfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunity`
--

LOCK TABLES `opportunity` WRITE;
/*!40000 ALTER TABLE `opportunity` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunity_project`
--

DROP TABLE IF EXISTS `opportunity_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunity_project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `opportunity_id` int(11) unsigned DEFAULT NULL,
  `project_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_tcejorp_di_ytinutroppo` (`opportunity_id`,`project_id`),
  KEY `di_ytinutroppo` (`opportunity_id`),
  KEY `di_tcejorp` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunity_project`
--

LOCK TABLES `opportunity_project` WRITE;
/*!40000 ALTER TABLE `opportunity_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunity_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunity_read`
--

DROP TABLE IF EXISTS `opportunity_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunity_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `opportunity_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunity_read`
--

LOCK TABLES `opportunity_read` WRITE;
/*!40000 ALTER TABLE `opportunity_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunity_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunitystarred`
--

DROP TABLE IF EXISTS `opportunitystarred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunitystarred` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `basestarredmodel_id` int(11) unsigned DEFAULT NULL,
  `opportunity_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basestarredmodel_id_opportunity_id` (`basestarredmodel_id`,`opportunity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunitystarred`
--

LOCK TABLES `opportunitystarred` WRITE;
/*!40000 ALTER TABLE `opportunitystarred` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunitystarred` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ownedsecurableitem`
--

DROP TABLE IF EXISTS `ownedsecurableitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ownedsecurableitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned DEFAULT NULL,
  `owner__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner__user_id` (`owner__user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ownedsecurableitem`
--

LOCK TABLES `ownedsecurableitem` WRITE;
/*!40000 ALTER TABLE `ownedsecurableitem` DISABLE KEYS */;
INSERT INTO `ownedsecurableitem` VALUES (1,1,1),(2,2,1),(3,3,1),(4,4,1),(5,5,1),(6,6,1);
/*!40000 ALTER TABLE `ownedsecurableitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permissions` tinyint(11) DEFAULT NULL,
  `type` tinyint(11) DEFAULT NULL,
  `permitable_id` int(11) unsigned DEFAULT NULL,
  `securableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission`
--

LOCK TABLES `permission` WRITE;
/*!40000 ALTER TABLE `permission` DISABLE KEYS */;
INSERT INTO `permission` VALUES (1,27,1,3,1),(2,27,1,3,2),(3,27,1,3,3),(4,27,1,3,4),(5,27,1,3,5),(6,27,1,3,6);
/*!40000 ALTER TABLE `permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permitable`
--

DROP TABLE IF EXISTS `permitable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permitable` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permitable`
--

LOCK TABLES `permitable` WRITE;
/*!40000 ALTER TABLE `permitable` DISABLE KEYS */;
INSERT INTO `permitable` VALUES (1,1),(2,2),(3,8),(4,14);
/*!40000 ALTER TABLE `permitable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jobtitle` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobilephone` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `officephone` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `officefax` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `primaryaddress_address_id` int(11) unsigned DEFAULT NULL,
  `primaryemail_email_id` int(11) unsigned DEFAULT NULL,
  `title_customfield_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ownedsecurableitem_id` (`ownedsecurableitem_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `person`
--

LOCK TABLES `person` WRITE;
/*!40000 ALTER TABLE `person` DISABLE KEYS */;
INSERT INTO `person` VALUES (1,NULL,'Super',NULL,'User',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,NULL,'System',NULL,'User',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `person` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personwhohavenotreadlatest`
--

DROP TABLE IF EXISTS `personwhohavenotreadlatest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personwhohavenotreadlatest` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `person_item_id` int(11) unsigned DEFAULT NULL,
  `mission_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personwhohavenotreadlatest`
--

LOCK TABLES `personwhohavenotreadlatest` WRITE;
/*!40000 ALTER TABLE `personwhohavenotreadlatest` DISABLE KEYS */;
/*!40000 ALTER TABLE `personwhohavenotreadlatest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perusermetadata`
--

DROP TABLE IF EXISTS `perusermetadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perusermetadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `classname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializedmetadata` text COLLATE utf8_unicode_ci,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perusermetadata`
--

LOCK TABLES `perusermetadata` WRITE;
/*!40000 ALTER TABLE `perusermetadata` DISABLE KEYS */;
/*!40000 ALTER TABLE `perusermetadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy`
--

DROP TABLE IF EXISTS `policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modulename` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permitable_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy`
--

LOCK TABLES `policy` WRITE;
/*!40000 ALTER TABLE `policy` DISABLE KEYS */;
/*!40000 ALTER TABLE `policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portlet`
--

DROP TABLE IF EXISTS `portlet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portlet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `column` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `layoutid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `viewtype` text COLLATE utf8_unicode_ci,
  `serializedviewdata` text COLLATE utf8_unicode_ci,
  `collapsed` tinyint(1) unsigned DEFAULT NULL,
  `_user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portlet`
--

LOCK TABLES `portlet` WRITE;
/*!40000 ALTER TABLE `portlet` DISABLE KEYS */;
/*!40000 ALTER TABLE `portlet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `quantity` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `pricefrequency` int(11) DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL,
  `contact_id` int(11) unsigned DEFAULT NULL,
  `opportunity_id` int(11) unsigned DEFAULT NULL,
  `producttemplate_id` int(11) unsigned DEFAULT NULL,
  `stage_customfield_id` int(11) unsigned DEFAULT NULL,
  `sellprice_currencyvalue_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_productcategory`
--

DROP TABLE IF EXISTS `product_productcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_productcategory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned DEFAULT NULL,
  `productcategory_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_yrogetactcudorp_di_tcudorp` (`product_id`,`productcategory_id`),
  KEY `di_tcudorp` (`product_id`),
  KEY `di_yrogetactcudorp` (`productcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_productcategory`
--

LOCK TABLES `product_productcategory` WRITE;
/*!40000 ALTER TABLE `product_productcategory` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_productcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_read`
--

DROP TABLE IF EXISTS `product_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `product_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_read`
--

LOCK TABLES `product_read` WRITE;
/*!40000 ALTER TABLE `product_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productcatalog`
--

DROP TABLE IF EXISTS `productcatalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productcatalog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productcatalog`
--

LOCK TABLES `productcatalog` WRITE;
/*!40000 ALTER TABLE `productcatalog` DISABLE KEYS */;
/*!40000 ALTER TABLE `productcatalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productcatalog_productcategory`
--

DROP TABLE IF EXISTS `productcatalog_productcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productcatalog_productcategory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `productcatalog_id` int(11) unsigned DEFAULT NULL,
  `productcategory_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_yrogetactcudorp_di_golatactcudorp` (`productcatalog_id`,`productcategory_id`),
  KEY `di_golatactcudorp` (`productcatalog_id`),
  KEY `di_yrogetactcudorp` (`productcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productcatalog_productcategory`
--

LOCK TABLES `productcatalog_productcategory` WRITE;
/*!40000 ALTER TABLE `productcatalog_productcategory` DISABLE KEYS */;
/*!40000 ALTER TABLE `productcatalog_productcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productcategory`
--

DROP TABLE IF EXISTS `productcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productcategory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `productcategory_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productcategory`
--

LOCK TABLES `productcategory` WRITE;
/*!40000 ALTER TABLE `productcategory` DISABLE KEYS */;
/*!40000 ALTER TABLE `productcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productcategory_producttemplate`
--

DROP TABLE IF EXISTS `productcategory_producttemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productcategory_producttemplate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `productcategory_id` int(11) unsigned DEFAULT NULL,
  `producttemplate_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_di_etalpmettcudorp_di_yrogetactcudorp` (`productcategory_id`,`producttemplate_id`),
  KEY `di_yrogetactcudorp` (`productcategory_id`),
  KEY `di_etalpmettcudorp` (`producttemplate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productcategory_producttemplate`
--

LOCK TABLES `productcategory_producttemplate` WRITE;
/*!40000 ALTER TABLE `productcategory_producttemplate` DISABLE KEYS */;
/*!40000 ALTER TABLE `productcategory_producttemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producttemplate`
--

DROP TABLE IF EXISTS `producttemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producttemplate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `status` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `pricefrequency` int(11) DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `sellpriceformula_id` int(11) unsigned DEFAULT NULL,
  `cost_currencyvalue_id` int(11) unsigned DEFAULT NULL,
  `listprice_currencyvalue_id` int(11) unsigned DEFAULT NULL,
  `sellprice_currencyvalue_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producttemplate`
--

LOCK TABLES `producttemplate` WRITE;
/*!40000 ALTER TABLE `producttemplate` DISABLE KEYS */;
/*!40000 ALTER TABLE `producttemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `status` int(11) DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_read`
--

DROP TABLE IF EXISTS `project_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `project_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_read`
--

LOCK TABLES `project_read` WRITE;
/*!40000 ALTER TABLE `project_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projectauditevent`
--

DROP TABLE IF EXISTS `projectauditevent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projectauditevent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `eventname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `_user_id` int(11) unsigned DEFAULT NULL,
  `project_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projectauditevent`
--

LOCK TABLES `projectauditevent` WRITE;
/*!40000 ALTER TABLE `projectauditevent` DISABLE KEYS */;
/*!40000 ALTER TABLE `projectauditevent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  `role_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_eman` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedcalendar`
--

DROP TABLE IF EXISTS `savedcalendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedcalendar` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `location` text COLLATE utf8_unicode_ci,
  `moduleclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `startattributename` text COLLATE utf8_unicode_ci,
  `endattributename` text COLLATE utf8_unicode_ci,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `timezone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedcalendar`
--

LOCK TABLES `savedcalendar` WRITE;
/*!40000 ALTER TABLE `savedcalendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedcalendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedcalendar_read`
--

DROP TABLE IF EXISTS `savedcalendar_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedcalendar_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `savedcalendar_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedcalendar_read`
--

LOCK TABLES `savedcalendar_read` WRITE;
/*!40000 ALTER TABLE `savedcalendar_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedcalendar_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedcalendarsubscription`
--

DROP TABLE IF EXISTS `savedcalendarsubscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedcalendarsubscription` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `_user_id` int(11) unsigned DEFAULT NULL,
  `savedcalendar_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedcalendarsubscription`
--

LOCK TABLES `savedcalendarsubscription` WRITE;
/*!40000 ALTER TABLE `savedcalendarsubscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedcalendarsubscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedreport`
--

DROP TABLE IF EXISTS `savedreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedreport` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `moduleclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `type` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedreport`
--

LOCK TABLES `savedreport` WRITE;
/*!40000 ALTER TABLE `savedreport` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedreport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedreport_read`
--

DROP TABLE IF EXISTS `savedreport_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedreport_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `savedreport_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedreport_read`
--

LOCK TABLES `savedreport_read` WRITE;
/*!40000 ALTER TABLE `savedreport_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedreport_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedsearch`
--

DROP TABLE IF EXISTS `savedsearch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedsearch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `viewclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedsearch`
--

LOCK TABLES `savedsearch` WRITE;
/*!40000 ALTER TABLE `savedsearch` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedsearch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedworkflow`
--

DROP TABLE IF EXISTS `savedworkflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedworkflow` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `isactive` tinyint(1) unsigned DEFAULT NULL,
  `moduleclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `type` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `triggeron` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedworkflow`
--

LOCK TABLES `savedworkflow` WRITE;
/*!40000 ALTER TABLE `savedworkflow` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedworkflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `securableitem`
--

DROP TABLE IF EXISTS `securableitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securableitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `securableitem`
--

LOCK TABLES `securableitem` WRITE;
/*!40000 ALTER TABLE `securableitem` DISABLE KEYS */;
INSERT INTO `securableitem` VALUES (1,7),(2,9),(3,10),(4,11),(5,12),(6,13);
/*!40000 ALTER TABLE `securableitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sellpriceformula`
--

DROP TABLE IF EXISTS `sellpriceformula`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sellpriceformula` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `discountormarkuppercentage` double DEFAULT NULL,
  `producttemplate_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sellpriceformula`
--

LOCK TABLES `sellpriceformula` WRITE;
/*!40000 ALTER TABLE `sellpriceformula` DISABLE KEYS */;
/*!40000 ALTER TABLE `sellpriceformula` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shorturl`
--

DROP TABLE IF EXISTS `shorturl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shorturl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8_unicode_ci,
  `createddatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shorturl`
--

LOCK TABLES `shorturl` WRITE;
/*!40000 ALTER TABLE `shorturl` DISABLE KEYS */;
/*!40000 ALTER TABLE `shorturl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socialitem`
--

DROP TABLE IF EXISTS `socialitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `socialitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8_unicode_ci,
  `latestdatetime` datetime DEFAULT NULL,
  `ownedsecurableitem_id` int(11) unsigned DEFAULT NULL,
  `note_id` int(11) unsigned DEFAULT NULL,
  `touser__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socialitem`
--

LOCK TABLES `socialitem` WRITE;
/*!40000 ALTER TABLE `socialitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `socialitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socialitem_read`
--

DROP TABLE IF EXISTS `socialitem_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `socialitem_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `socialitem_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socialitem_read`
--

LOCK TABLES `socialitem_read` WRITE;
/*!40000 ALTER TABLE `socialitem_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `socialitem_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stuckjob`
--

DROP TABLE IF EXISTS `stuckjob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stuckjob` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stuckjob`
--

LOCK TABLES `stuckjob` WRITE;
/*!40000 ALTER TABLE `stuckjob` DISABLE KEYS */;
/*!40000 ALTER TABLE `stuckjob` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `completeddatetime` datetime DEFAULT NULL,
  `completed` tinyint(1) unsigned DEFAULT NULL,
  `duedatetime` datetime DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `activity_id` int(11) unsigned DEFAULT NULL,
  `requestedbyuser__user_id` int(11) unsigned DEFAULT NULL,
  `project_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task`
--

LOCK TABLES `task` WRITE;
/*!40000 ALTER TABLE `task` DISABLE KEYS */;
/*!40000 ALTER TABLE `task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_read`
--

DROP TABLE IF EXISTS `task_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `securableitem_id` int(11) unsigned NOT NULL,
  `munge_id` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `securableitem_id_munge_id` (`securableitem_id`,`munge_id`),
  KEY `task_read_securableitem_id` (`securableitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_read`
--

LOCK TABLES `task_read` WRITE;
/*!40000 ALTER TABLE `task_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_read_subscription`
--

DROP TABLE IF EXISTS `task_read_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_read_subscription` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `modelid` int(11) unsigned NOT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  `subscriptiontype` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_modelid` (`userid`,`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_read_subscription`
--

LOCK TABLES `task_read_subscription` WRITE;
/*!40000 ALTER TABLE `task_read_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_read_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taskchecklistitem`
--

DROP TABLE IF EXISTS `taskchecklistitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taskchecklistitem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci,
  `sortorder` int(11) DEFAULT NULL,
  `completed` tinyint(1) unsigned DEFAULT NULL,
  `task_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taskchecklistitem`
--

LOCK TABLES `taskchecklistitem` WRITE;
/*!40000 ALTER TABLE `taskchecklistitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `taskchecklistitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflowmessageinqueue`
--

DROP TABLE IF EXISTS `workflowmessageinqueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflowmessageinqueue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modelclassname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `processdatetime` datetime DEFAULT NULL,
  `serializeddata` text COLLATE utf8_unicode_ci,
  `item_id` int(11) unsigned DEFAULT NULL,
  `modelitem_item_id` int(11) unsigned DEFAULT NULL,
  `savedworkflow_id` int(11) unsigned DEFAULT NULL,
  `triggeredbyuser__user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflowmessageinqueue`
--

LOCK TABLES `workflowmessageinqueue` WRITE;
/*!40000 ALTER TABLE `workflowmessageinqueue` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflowmessageinqueue` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-03-04 10:34:56
