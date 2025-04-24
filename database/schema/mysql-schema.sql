
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `state_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `sub_category` varchar(255) DEFAULT NULL,
  `productName` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `initialQuantity` varchar(255) DEFAULT NULL,
  `quantityStock` varchar(255) DEFAULT NULL,
  `materialDispatchDate` date DEFAULT NULL,
  `deliveryDate` date DEFAULT NULL,
  `receivedDate` date DEFAULT NULL,
  `allocationOfficer` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_project_id_foreign` (`project_id`),
  KEY `inventory_site_id_foreign` (`site_id`),
  KEY `inventory_store_id_foreign` (`store_id`),
  CONSTRAINT `inventory_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` enum('1','2','3','4','5') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_user_project_id_foreign` (`project_id`),
  KEY `project_user_user_id_foreign` (`user_id`),
  CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_type` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `work_order_number` varchar(255) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_capacity` decimal(10,2) NOT NULL,
  `end_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `project_in_state` varchar(255) NOT NULL,
  `agreement_number` varchar(255) DEFAULT NULL,
  `agreement_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `site_name` varchar(255) NOT NULL,
  `state` bigint(20) unsigned DEFAULT NULL,
  `district` bigint(20) unsigned DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `project_capacity` varchar(255) DEFAULT NULL,
  `ca_number` varchar(255) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `ic_vendor_name` bigint(20) unsigned DEFAULT NULL,
  `sanction_load` varchar(255) DEFAULT NULL,
  `meter_number` varchar(255) DEFAULT NULL,
  `load_enhancement_status` enum('Yes','No') NOT NULL DEFAULT 'No',
  `site_survey_status` enum('Pending','Done') NOT NULL DEFAULT 'Pending',
  `net_meter_sr_no` varchar(255) DEFAULT NULL,
  `solar_meter_sr_no` varchar(255) DEFAULT NULL,
  `material_inspection_date` date DEFAULT NULL,
  `spp_installation_date` date DEFAULT NULL,
  `commissioning_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `site_engineer` bigint(20) unsigned DEFAULT NULL,
  `survey_latitude` decimal(10,7) DEFAULT NULL,
  `survey_longitude` decimal(10,7) DEFAULT NULL,
  `actual_latitude` decimal(10,7) DEFAULT NULL,
  `actual_longitude` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sites_project_id_foreign` (`project_id`),
  KEY `sites_state_foreign` (`state`),
  KEY `sites_district_foreign` (`district`),
  CONSTRAINT `sites_district_foreign` FOREIGN KEY (`district`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sites_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sites_state_foreign` FOREIGN KEY (`state`) REFERENCES `states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `states` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `country_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `store_incharge_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stores_project_id_foreign` (`project_id`),
  KEY `stores_store_incharge_id_foreign` (`store_incharge_id`),
  CONSTRAINT `stores_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stores_store_incharge_id_foreign` FOREIGN KEY (`store_incharge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `streetlight_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streetlight_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `site_id` bigint(20) unsigned NOT NULL,
  `engineer_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `approved_by` varchar(255) DEFAULT NULL,
  `materials_consumed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`materials_consumed`)),
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `streetlight_tasks_project_id_foreign` (`project_id`),
  KEY `streetlight_tasks_site_id_foreign` (`site_id`),
  KEY `streetlight_tasks_engineer_id_foreign` (`engineer_id`),
  KEY `streetlight_tasks_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `streetlight_tasks_engineer_id_foreign` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `streetlight_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `streetlight_tasks_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `streetlights` (`id`) ON DELETE CASCADE,
  CONSTRAINT `streetlight_tasks_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `streetlights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streetlights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` tinyint(4) NOT NULL,
  `state` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `block` varchar(255) NOT NULL,
  `panchayat` varchar(255) NOT NULL,
  `ward` varchar(255) DEFAULT NULL,
  `pole` varchar(255) DEFAULT NULL,
  `isSurveyDone` tinyint(1) NOT NULL DEFAULT 0,
  `isNetworkAvailable` tinyint(1) NOT NULL DEFAULT 0,
  `isInstallationDone` tinyint(1) NOT NULL DEFAULT 0,
  `complete_pole_number` varchar(255) DEFAULT NULL,
  `uname` varchar(255) DEFAULT NULL,
  `district_id` tinyint(4) DEFAULT NULL,
  `block_id` tinyint(4) DEFAULT NULL,
  `panchayat_id` tinyint(4) DEFAULT NULL,
  `ward_id` tinyint(4) DEFAULT NULL,
  `pole_id` tinyint(4) DEFAULT NULL,
  `luminary_qr` varchar(255) DEFAULT NULL,
  `battery_qr` varchar(255) DEFAULT NULL,
  `panel_qr` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `beneficiary` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `SID` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `streetlights_project_id_foreign` (`project_id`),
  CONSTRAINT `streetlights_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `task_name` varchar(255) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `approved_by` varchar(255) DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image`)),
  `materials_consumed` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `engineer_id` bigint(20) unsigned DEFAULT NULL,
  `manager_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_project_id_foreign` (`project_id`),
  KEY `tasks_site_id_foreign` (`site_id`),
  KEY `tasks_vendor_id_foreign` (`vendor_id`),
  KEY `tasks_manager_id_foreign` (`manager_id`),
  CONSTRAINT `tasks_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(4) NOT NULL DEFAULT 1,
  `manager_id` bigint(20) unsigned DEFAULT NULL,
  `site_engineer_id` bigint(20) unsigned DEFAULT NULL,
  `accountName` varchar(255) DEFAULT NULL,
  `accountNumber` varchar(255) DEFAULT NULL,
  `ifsc` varchar(255) DEFAULT NULL,
  `bankName` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `gstNumber` varchar(255) DEFAULT NULL,
  `pan` varchar(255) DEFAULT NULL,
  `aadharNumber` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `disableLogin` tinyint(1) NOT NULL DEFAULT 0,
  `address` varchar(255) DEFAULT NULL,
  `contactNo` varchar(255) DEFAULT NULL,
  `lastOnline` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_manager_id_foreign` (`manager_id`),
  KEY `users_site_engineer_id_foreign` (`site_engineer_id`),
  CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_site_engineer_id_foreign` FOREIGN KEY (`site_engineer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
