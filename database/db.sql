SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `cities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `state_id`, `created_at`, `updated_at`) VALUES
(1, 'Anantapur', 1, NULL, NULL),
(2, 'Chittoor', 1, NULL, NULL),
(3, 'Kishanganj', 4, NULL, NULL),
(4, 'Lakhisarai', 4, NULL, NULL),
(5, 'Madhepura', 4, NULL, NULL),
(6, 'South 24 Parganas', 35, NULL, NULL),
(7, 'Uttar Dinajpur (North Dinajpur)', 35, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `productName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initialQuantity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantityStock` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `materialDispatchDate` date DEFAULT NULL,
  `deliveryDate` date DEFAULT NULL,
  `receivedDate` date DEFAULT NULL,
  `allocationOfficer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `site_id` bigint UNSIGNED DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(5, '2016_06_01_000002_create_oauth_access_tokens_table', 1);
-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Laravel Personal Access Client', 'Z5GlW5osjA4JflWCum1kxIIul1scyQKSjJ0gzavn', NULL, 'http://localhost', 1, 0, 0, '2024-11-27 11:32:36', '2024-11-27 11:32:36'),
(2, NULL, 'Laravel Password Grant Client', 'CVw3G0IBqaFzlcGSvIkretJsi91hRglMCLQZnmEn', 'users', 'http://localhost', 0, 1, 0, '2024-11-27 11:32:36', '2024-11-27 11:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-11-27 11:32:36', '2024-11-27 11:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'authToken', '756adc79a5c7eccd942ee213fa22e2e8021fef9f382a64a38564d83c38143c4b', '[\"*\"]', NULL, NULL, '2024-11-27 12:08:06', '2024-11-27 12:08:06'),
(1680, 'App\\Models\\User', 35, 'authToken', '262f4c2bb3f5f6d8c6b167c524230595bf705b26d62c284baa3be909778379ad', '[\"*\"]', NULL, NULL, '2025-01-15 08:28:35', '2025-01-15 08:28:35'),
(1681, 'App\\Models\\User', 35, 'authToken', 'a08f76121c9ee8b0fe421c4951e075ea54091db61a47a493f1f163a054cc6815', '[\"*\"]', NULL, NULL, '2025-01-15 08:37:19', '2025-01-15 08:37:19');
-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint UNSIGNED NOT NULL,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `work_order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_capacity` decimal(10,2) NOT NULL,
  `end_date` date NOT NULL,
  `description` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `project_in_state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agreement_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agreement_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_name`, `project_type`, `start_date`, `work_order_number`, `rate`, `created_at`, `updated_at`, `project_capacity`, `end_date`, `description`, `total`, `project_in_state`, `agreement_number`, `agreement_date`) VALUES
(10, 'BREDA 11th WO', '0', '2025-03-05', '974', 1.00, '2024-12-28 11:38:23', '2025-03-05 10:53:57', 8209.00, '2025-03-05', 'Work Order against Design, Supply, Installation, Testing & Commissioning with Comprehensive Maintenance (5 Years) of Grid Connected Rooftop Solar Photovoltaic (PV) Commissionary , State of Bihar.', 8209.00, '4', NULL, NULL),
(11, 'Streetlight Project', '1', '2025-02-23', 'PO122518', 50000.00, '2025-02-03 10:36:00', '2025-02-23 02:51:23', 4.95, '2025-02-23', 'Describe', 247500.00, '4', '33', '2024-11-24'),
(14, 'NTPC_DP', '0', '2025-02-26', '2025/RY', 10000000.00, '2025-02-26 04:12:20', '2025-02-26 04:12:20', 1.00, '2025-04-30', 'We will put it later', 10000000.00, '9', NULL, NULL),
(15, 'BREDA 13th WO Purnea 713kw', '0', '2025-03-05', '1978', 48820.20, '2025-03-05 05:51:22', '2025-03-05 11:23:23', 713.00, '2025-03-05', 'BREDA 13th WO PURNEA', 34808802.60, '4', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_user`
--

CREATE TABLE `project_user` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role` enum('1','2','3','4','5') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_user`
--

INSERT INTO `project_user` (`id`, `project_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(54, 10, 68, '1', '2025-02-06 07:29:26', '2025-02-06 07:29:26'),
(55, 10, 69, '1', '2025-02-06 07:29:26', '2025-02-06 07:29:26'),
(56, 10, 74, '1', '2025-02-06 07:29:26', '2025-02-06 07:29:26'),
(57, 10, 76, '1', '2025-02-06 07:29:26', '2025-02-06 07:29:26'),
(58, 10, 34, '1', '2025-02-06 07:29:54', '2025-02-06 07:29:54'),
(59, 10, 38, '1', '2025-02-06 07:29:54', '2025-02-06 07:29:54'),
(179, 11, 138, '1', '2025-03-08 07:43:04', '2025-03-08 07:43:04');

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` bigint UNSIGNED NOT NULL,
  `breda_sl_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `site_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` bigint UNSIGNED DEFAULT NULL,
  `district` bigint UNSIGNED DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_capacity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ca_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ic_vendor_name` bigint UNSIGNED DEFAULT NULL,
  `sanction_load` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meter_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `load_enhancement_status` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `site_survey_status` enum('Pending','Done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `net_meter_sr_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solar_meter_sr_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material_inspection_date` date DEFAULT NULL,
  `spp_installation_date` date DEFAULT NULL,
  `commissioning_date` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `site_engineer` bigint UNSIGNED DEFAULT NULL,
  `survey_latitude` decimal(10,7) DEFAULT NULL,
  `survey_longitude` decimal(10,7) DEFAULT NULL,
  `actual_latitude` decimal(10,7) DEFAULT NULL,
  `actual_longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sites`
--

INSERT INTO `sites` (`id`, `breda_sl_no`, `project_id`, `site_name`, `state`, `district`, `location`, `project_capacity`, `ca_number`, `contact_no`, `ic_vendor_name`, `sanction_load`, `meter_number`, `load_enhancement_status`, `site_survey_status`, `net_meter_sr_no`, `solar_meter_sr_no`, `material_inspection_date`, `spp_installation_date`, `commissioning_date`, `remarks`, `created_at`, `updated_at`, `site_engineer`, `survey_latitude`, `survey_longitude`, `actual_latitude`, `actual_longitude`) VALUES
(6899, '62', 10, 'NEW P S PIRGANJ KABIYA', 4, 94, 'Purnea EAST', '5', '10120048861', '8298252994', NULL, NULL, NULL, 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:20', '2025-02-11 09:40:20', NULL, NULL, NULL, NULL, NULL),
(6900, '40', 10, 'P S SHIKSHA NAGAR BANMANKHI', 4, 94, 'BANMANKHI', '5', '10140011567', '9199502622', NULL, NULL, 'C1297870', 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:20', '2025-02-26 11:16:48', NULL, NULL, NULL, 25.7680622, 87.4764074),
(6901, '48', 10, 'P S MIRCHAIBARI UTTAR', 4, 94, 'BANMANKHI', '5', '10140038292', '9771900519', NULL, NULL, 'C1157610', 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:20', '2025-02-11 09:40:20', NULL, NULL, NULL, NULL, NULL),
(7790, '243', 10, 'P S ANUSUCHIT JATI TOLA GAIDUHA', 4, 94, 'RUPAULI', '5', NULL, NULL, NULL, NULL, NULL, 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:22', '2025-02-11 09:40:22', NULL, NULL, NULL, NULL, NULL),
(7828, '297', 10, 'P S SARKAL TOLA', 4, 94, 'BHAWANIPUR', '5', '101510701627', '6206341247', NULL, NULL, 'C1298261', 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:22', '2025-02-11 09:40:22', NULL, NULL, NULL, NULL, NULL),
(8494, '1390', 10, 'NEW P S DURGAPUR PASCHIM TOLA', 4, 94, 'BHAWANIPUR', '5', '10150019012', NULL, NULL, NULL, 'C1242080', 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:23', '2025-02-11 09:40:23', NULL, NULL, NULL, NULL, NULL),
(8495, '1392', 10, 'P S SUKHASAN JAGIR', 4, 94, 'BARHARA KOTHI', '5', NULL, '9801054707', NULL, NULL, NULL, 'No', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-11 09:40:23', '2025-02-11 09:40:23', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `country_id`, `created_at`, `updated_at`) VALUES
(1, 'Andhra Pradesh', 1, NULL, NULL),
(4, 'Bihar', 1, NULL, NULL),
(35, 'West Bengal', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `store_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_incharge_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `project_id`, `store_name`, `address`, `store_incharge_id`, `created_at`, `updated_at`) VALUES
(7, 11, 'Pritam Kumar', 'CO-Sunil Kumar Singh, Mabbi Chowk, Near Darbhanga  College of Engineering, Lalsahpur , Thana- Mabbi, Darbhanga -846005', 93, '2025-02-11 07:08:47', '2025-02-11 07:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `streelight_poles`
--

CREATE TABLE `streelight_poles` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `isSurveyDone` tinyint(1) NOT NULL DEFAULT '0',
  `beneficiary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isNetworkAvailable` tinyint(1) NOT NULL DEFAULT '0',
  `isInstallationDone` tinyint(1) NOT NULL DEFAULT '0',
  `complete_pole_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `luminary_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sim_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `battery_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panel_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `survey_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submission_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `streelight_poles`
--

INSERT INTO `streelight_poles` (`id`, `task_id`, `isSurveyDone`, `beneficiary`, `remarks`, `isNetworkAvailable`, `isInstallationDone`, `complete_pole_number`, `ward`, `luminary_qr`, `sim_number`, `battery_qr`, `panel_qr`, `lat`, `lng`, `survey_image`, `submission_image`, `file`, `created_at`, `updated_at`) VALUES
(15, 263, 1, 'Madam Sah', 'Purani Chatanama', 1, 0, 'BIH/MAD//PUR/VAN//10', NULL, NULL, NULL, NULL, NULL, 25.6038500, 86.9979074, NULL, NULL, NULL, '2025-03-06 08:08:23', '2025-03-06 08:08:23'),
(16, 265, 1, 'Vinod chaudhari', 'Pokhar ke pas', 1, 0, 'BIH/MUZ/KAT/KAT//1', NULL, NULL, NULL, NULL, NULL, 26.2204624, 85.6398427, NULL, NULL, NULL, '2025-03-06 09:12:44', '2025-03-06 09:12:44'),
(245, 288, 1, 'Karelal mahto', 'Karelal matho', 1, 0, 'BIH/LAK/SUR/RAM//10/9', NULL, NULL, NULL, NULL, NULL, 25.1409817, 86.0875221, '\"[\\\"streetlights\\\\\\/survey\\\\\\/245\\\\\\/1741454881_photo_0.jpg\\\",\\\"streetlights\\\\\\/survey\\\\\\/245\\\\\\/1741454881_photo_1.jpg\\\"]\"', NULL, NULL, '2025-03-08 17:28:01', '2025-03-08 17:28:01'),
(246, 288, 1, 'Guddu Kumar', 'Guddu Kumar', 1, 0, 'BIH/LAK/SUR/RAM//10/10', NULL, NULL, NULL, NULL, NULL, 25.1409817, 86.0875221, '\"[\\\"streetlights\\\\\\/survey\\\\\\/246\\\\\\/1741454898_photo_0.jpg\\\",\\\"streetlights\\\\\\/survey\\\\\\/246\\\\\\/1741454898_photo_1.jpg\\\"]\"', NULL, NULL, '2025-03-08 17:28:18', '2025-03-08 17:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `streetlights`
--

CREATE TABLE `streetlights` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `block` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `panchayat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ward` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pole` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_surveyed_poles` int NOT NULL DEFAULT '0',
  `number_of_installed_poles` int NOT NULL DEFAULT '0',
  `isSurveyDone` tinyint(1) NOT NULL DEFAULT '0',
  `isNetworkAvailable` tinyint(1) NOT NULL DEFAULT '0',
  `isInstallationDone` tinyint(1) NOT NULL DEFAULT '0',
  `complete_pole_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_id` tinyint DEFAULT NULL,
  `block_id` tinyint DEFAULT NULL,
  `panchayat_id` tinyint DEFAULT NULL,
  `ward_id` tinyint DEFAULT NULL,
  `pole_id` tinyint DEFAULT NULL,
  `luminary_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `battery_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panel_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `SID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `streetlights`
--

INSERT INTO `streetlights` (`id`, `task_id`, `state`, `district`, `block`, `panchayat`, `ward`, `pole`, `number_of_surveyed_poles`, `number_of_installed_poles`, `isSurveyDone`, `isNetworkAvailable`, `isInstallationDone`, `complete_pole_number`, `uname`, `district_id`, `block_id`, `panchayat_id`, `ward_id`, `pole_id`, `luminary_qr`, `battery_qr`, `panel_qr`, `file`, `lat`, `lng`, `remark`, `created_at`, `updated_at`, `project_id`, `SID`) VALUES
(776, 'LAK001', 'Bihar', 'Lakhisarai', 'Surajgarha', 'Mohamadpur', '5,6,7,8,9,10', NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-14 06:21:51', '2025-02-14 06:21:51', 11, 0),
(777, 'LAK002', 'Bihar', 'Lakhisarai', 'Surajgarha', 'Ghosaith', '5,6,7,8,9', NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-14 06:21:51', '2025-02-14 06:21:51', 11, 0),
(778, 'LAK003', 'Bihar', 'Lakhisarai', 'Surajgarha', 'Kaswa', '2,3,4,5,6, 7', NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-14 06:21:51', '2025-02-14 06:21:51', 11, 0),
(1003, 'DAR027', 'Bihar', 'Darbhanga ', 'Bahadurpur ', 'JALWARA', '11,13', NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-14 06:21:51', '2025-02-14 06:21:51', 11, 0);

-- --------------------------------------------------------

--
-- Table structure for table `streetlight_tasks`
--

CREATE TABLE `streetlight_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `engineer_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `manager_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `approved_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `materials_consumed` json DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pole_id` bigint UNSIGNED DEFAULT '1234',
  `site_id` bigint UNSIGNED DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `streetlight_tasks`
--

INSERT INTO `streetlight_tasks` (`id`, `project_id`, `engineer_id`, `vendor_id`, `manager_id`, `status`, `approved_by`, `materials_consumed`, `start_date`, `end_date`, `created_at`, `updated_at`, `pole_id`, `site_id`, `completed_at`, `rejected_at`) VALUES
(262, 11, 108, 114, 117, 'Pending', NULL, NULL, '2025-03-06', '2025-03-06', '2025-03-06 04:49:33', '2025-03-06 04:49:33', 1, 985, NULL, NULL),
(263, 11, 88, 130, 91, 'Pending', NULL, NULL, '2025-03-06', '2025-03-07', '2025-03-06 04:51:31', '2025-03-06 04:51:31', 1, 863, NULL, NULL),
(308, 11, 140, 55, 91, 'Pending', NULL, NULL, '2025-03-08', '2025-03-10', '2025-03-08 07:57:14', '2025-03-08 07:57:14', 1234, 895, NULL, NULL),
(309, 11, 85, 122, 91, 'Pending', NULL, NULL, '2025-03-08', '2025-03-11', '2025-03-08 09:05:53', '2025-03-08 09:05:53', 1234, 840, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `site_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `task_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci,
  `materials_consumed` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `engineer_id` bigint UNSIGNED DEFAULT NULL,
  `manager_id` bigint UNSIGNED DEFAULT '66',
  `completed_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `site_id`, `vendor_id`, `task_name`, `status`, `start_date`, `end_date`, `description`, `approved_by`, `image`, `materials_consumed`, `created_at`, `updated_at`, `activity`, `engineer_id`, `manager_id`, `completed_at`, `rejected_at`) VALUES
(296, 10, 7236, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', NULL, NULL, '[\"tasks\\/296\\/1740127513_PS Santi nagar 39.pdf\",\"tasks\\/296\\/1740127513_photo_1.jpg\"]', NULL, '2025-02-12 06:49:58', '2025-02-21 08:58:01', 'Installation', 68, 66, NULL, NULL),
(297, 10, 7237, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', 'Site installation done', NULL, '[\"tasks\\/297\\/1740403253_PS pakadia sanjhlli 92.pdf\",\"tasks\\/297\\/1740403253_photo_1.jpg\"]', NULL, '2025-02-12 06:49:58', '2025-02-24 13:26:02', 'Installation', 68, 66, NULL, NULL),
(298, 10, 7238, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', 'Site installation Done', NULL, '[\"tasks\\/298\\/1740403382_PS Garbanlli bazar 145.pdf\",\"tasks\\/298\\/1740403383_photo_1.jpg\"]', NULL, '2025-02-12 06:49:58', '2025-02-24 13:26:06', 'Installation', 68, 66, NULL, NULL),
(299, 10, 7239, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', 'Site visit', NULL, '[\"tasks\\/299\\/1740499105_1738841944_PS pakadia sanjhlli 92.pdf\",\"tasks\\/299\\/1740499106_photo_1.jpg\",\"tasks\\/299\\/1740499106_photo_2.jpg\"]', NULL, '2025-02-12 06:49:58', '2025-02-25 16:29:57', 'Installation', 68, 66, NULL, NULL),
(300, 10, 7242, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', NULL, NULL, '[\"tasks\\/300\\/1740501436_1738841944_PS pakadia sanjhlli 92.pdf\",\"tasks\\/300\\/1740501436_photo_1.jpg\",\"tasks\\/300\\/1740501436_photo_2.jpg\"]', NULL, '2025-02-12 06:49:58', '2025-02-26 07:41:52', 'Installation', 68, 66, NULL, NULL),
(301, 10, 7243, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', NULL, NULL, '[\"tasks\\/301\\/1740561546_TRNAV1HRFVYM50943Q_Ticket.pdf\",\"tasks\\/301\\/1740561546_photo_1.jpg\",\"tasks\\/301\\/1740561546_photo_2.jpg\"]', NULL, '2025-02-12 06:49:58', '2025-02-26 12:44:51', 'Installation', 68, 66, NULL, NULL),
(302, 10, 7244, 58, NULL, 'Completed', '2025-02-12', '2025-02-12', 'Hello', NULL, '[\"tasks\\/302\\/1740562466_Js interview Questions .pdf\"]', NULL, '2025-02-12 06:49:58', '2025-02-26 12:44:54', 'Installation', 68, 66, NULL, NULL),
(479, 10, 7023, 47, NULL, 'Completed', '2025-03-08', '2025-03-08', 'Roof solar installation done', NULL, '[\"tasks\\/479\\/1741499110_photo_0.jpg\",\"tasks\\/479\\/1741499111_photo_1.jpg\"]', NULL, '2025-03-08 12:01:04', '2025-03-09 07:06:10', 'Installation', 69, 66, NULL, NULL),

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` tinyint NOT NULL DEFAULT '1',
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `manager_id` bigint UNSIGNED DEFAULT NULL,
  `site_engineer_id` bigint UNSIGNED DEFAULT NULL,
  `accountName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountNumber` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ifsc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gstNumber` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_cheque_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_documents` json DEFAULT NULL,
  `pan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aadharNumber` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aadhar_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `firstName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `disableLogin` tinyint(1) NOT NULL DEFAULT '0',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contactNo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastOnline` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `email_verified_at`, `password`, `role`, `project_id`, `assigned_by`, `manager_id`, `site_engineer_id`, `accountName`, `accountNumber`, `ifsc`, `bankName`, `branch`, `gstNumber`, `cancelled_cheque_document`, `gst_document`, `additional_documents`, `pan`, `pan_document`, `aadharNumber`, `aadhar_document`, `remember_token`, `created_at`, `updated_at`, `firstName`, `lastName`, `image`, `status`, `disableLogin`, `address`, `contactNo`, `lastOnline`) VALUES
(11, 'Admin', 'pm@dashandots.tech', 'projmanager', '2024-11-27 13:43:43', '$2y$12$ylNXj.kCMlenPbbk92n1eO8Z5sXqi5Lk6o/p77d7I7hjKee2PD8Nq', 0, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'VfBzN97RiYp4TZaNfLN1oMtUBxEuhIaQEg6QsD3CadWdiX3H68KLCfNbBK4z', '2024-11-27 13:43:43', '2024-12-04 10:55:26', 'Rohit', 'Gupta', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, '123 gali, jhajjar, Haryana', '9909230914', '2024-11-27 13:43:43'),
(34, 'MD Enterprises', 'pv001@gmail.com', 'mdenterprises5951', NULL, '$2y$12$GlC8oSxYsPQZzQtwE65WZe9VUGSyyIAXJTUEA3e/OPGDI1nlwGfHm', 3, 10, NULL, 66, 71, 'M.D. Enterprises', '6147375414', NULL, 'Kotak Mahendra Bank', NULL, '06QXVPS1644J1ZE', NULL, NULL, NULL, NULL, NULL, 'N/A', NULL, NULL, '2024-12-28 12:18:18', '2025-02-25 11:06:37', 'Sumit', 'Saini', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/mdenterprises5951_20250225_110637.jpg', 'active', 0, 'Bihar', '9416669148', NULL),
(35, NULL, 'zaid@sugslloyds.com', 'zaid2281', NULL, '$2y$12$npjkvXxJQ402UhFJilyy6uklYvUhqtpYrPCjz6PdkJfwbkkeUBnjy', 1, 10, NULL, 66, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-28 12:22:12', '2024-12-28 12:22:12', 'Zaid', 'Malik', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Noida Sector 16', '8127694046', NULL),
(38, 'Manjhi Manpower Private Limited', 'pv002@gmail.com', 'manjhimanpowerprivatelimited8948', NULL, '$2y$12$GlC8oSxYsPQZzQtwE65WZe9VUGSyyIAXJTUEA3e/OPGDI1nlwGfHm', 3, 10, NULL, 66, 74, 'Manjhi manpower private limited', '50200058843614', NULL, 'HDFC Bank', NULL, '10AAOCM4269J2Z8', NULL, NULL, NULL, NULL, NULL, 'N/A', NULL, NULL, '2024-12-30 05:47:24', '2024-12-30 05:47:24', 'Diwakar', 'Kumar', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Purnea, Bihar', '7250190752', NULL),
(39, 'Natural power enterprises', 'pv003@gmail.com', 'naturalpowerenterprises3047', NULL, '$2y$12$GlC8oSxYsPQZzQtwE65WZe9VUGSyyIAXJTUEA3e/OPGDI1nlwGfHm', 3, 10, NULL, 66, 74, 'Natural power enterprises', '50200060721321', NULL, 'HDFC Bank', NULL, '09AASFN8787D1ZF', NULL, NULL, NULL, NULL, NULL, 'N/A', NULL, NULL, '2024-12-30 05:51:21', '2025-03-08 13:08:05', 'Meet', 'Yadav', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/naturalpowerenterprises3047_20250308_130805.jpg', 'active', 0, 'Purnea, Bihar', '9315901494', NULL),
(40, 'Lalsun Energy Power System Pvt. Ltd.', 'pv004@gmail.com', 'lalsunenergypowersystempvt.ltd.1043', NULL, '$2y$12$GlC8oSxYsPQZzQtwE65WZe9VUGSyyIAXJTUEA3e/OPGDI1nlwGfHm', 3, 10, NULL, 66, 74, 'Lalsun energy power system pvt ltd', '921020010825624', NULL, 'Axis Bank', NULL, '10AAECL4300P1ZS', NULL, NULL, NULL, NULL, NULL, 'N/A', NULL, NULL, '2024-12-30 05:56:35', '2024-12-30 05:56:35', 'Rakesh', 'Kumar', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Purnea, Bihar', '9931730529', NULL),
(85, 'Golu Kumar', 'golub5549@gmail.com', 'golu7923', NULL, '$2y$12$N.eJ.rMx2RbyeZvhZxsRVeCgNwK1pqhSR3tzpPV4MU86t4.rGHv72', 1, 11, NULL, 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-07 07:47:20', '2025-03-08 03:32:46', 'Golu', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/golu7923_20250308_033246.jpg', 'active', 0, 'Madhepura', '9306932487', NULL),
(87, 'Ashutosh Aryan', 'ashutosharyan4u4@gmail.com', 'ashutosh1183', NULL, '$2y$12$kwoTZEw5813Hr3gkpPUmm.BlNEt1rczhunexd146n3WLmTLoQ90Rm', 1, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-07 07:50:59', '2025-02-26 04:53:07', 'Ashutosh', 'Aryan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/ashutosh1183_20250226_045307.jpg', 'active', 0, 'Madhepura', '9471661590', NULL),
(88, 'Lalijeet Rishi', 'rishilaljeet@gmail.com', 'laljeet5525', NULL, '$2y$12$VJ6GgPALjlBaFx9zqs9CVOolwQU7WwTwD8plFMTGpQaWXjI1quhK2', 1, 11, NULL, 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-07 07:52:13', '2025-02-18 09:39:26', 'Laljeet', 'Rishi', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Madhepura', '9102473741', NULL),
(89, 'Ujjain kumar chandravanshi', 'ujjainktr17@gmail.com', 'ujjain8743', NULL, '$2y$12$RJqHN4fw1zBIndbTQkXVjucIAq9kKwIrs6TXC6SPJQldadWs51OEq', 1, 11, NULL, 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-07 07:53:32', '2025-03-05 10:50:27', 'Ujjain', 'kumar chandravanshi', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/ujjain8743_20250305_105027.jpg', 'active', 0, 'Madhepura', '6200214392', NULL),
(91, 'Anand', 'anand.karn@sugslloyds.com', 'anand5168', NULL, '$2y$12$N3hQfsAV6U2IFUVXD/RWXuaEFzc01Hz978j6Oy4D1JkCg8/DYnceW', 2, 11, NULL, 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4buaPEzxevIXg9w5uTeHOVgZovTeSjGw2pJypfHYxVljN48KO3kY3bpDiQzi', '2025-02-07 12:06:17', '2025-03-05 06:37:30', 'Anand', 'Karan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/anand5168_20250305_063730.jpg', 'active', 0, 'Madhepura 852113', '9560046610', NULL),
(92, NULL, 'Aas@gmail.vom', 'sda1447', NULL, '$2y$12$fQtbVKxCB99aDxusNI8XkuwkRNBcVZpJGmi0qe3T/kmTwbCKkTRBS', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-07 12:08:27', '2025-02-07 12:08:27', 'sda', 'dad', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, '452', '45525864185', NULL),
(93, 'Pritam Kumar', 'pritam848282@gmail.com', 'pritam6615', NULL, '$2y$12$R0Vk./dQHC1yQri39j9nK.2XVmsB59WfGWmQSglJsmQP79SnvdLha', 1, 11, NULL, 117, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 06:27:27', '2025-02-26 07:39:15', 'Pritam', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/pritam6615_20250226_073915.jpg', 'active', 0, 'Darbhanga', '9608081737', NULL),
(94, 'Abhishek Raj', 'abhishek12000320083@gmail.com', 'abhishek1962', NULL, '$2y$12$abzR79r9uY0l0eHRJu8d..x82ctLooptvgDUwRfvrWG52XTG8qWiW', 1, 11, NULL, 117, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 06:29:11', '2025-02-26 06:36:24', 'Abhishek', 'Raj', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/abhishek1962_20250226_063624.jpg', 'active', 0, 'Darbhanga', '7545843850', NULL),
(95, 'Aashish Ranjan', 'Ashishbhumi96@gmail.com', 'aashish6021', NULL, '$2y$12$U5y3z9xI75GdXT11Huv42.vq7TrLChEZCQ8RB0ZrfEFmRTqEC4XSO', 1, 11, NULL, 117, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 06:30:35', '2025-02-26 07:09:38', 'Aashish', 'Ranjan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/aashish6021_20250226_070938.jpg', 'active', 0, 'Darbhanga', '9661560234', NULL),
(97, NULL, 'rahulghz1996@gmail.com', 'rahul2082', NULL, '$2y$12$sUuscl/P7YJFeudWf5AjX.4xqDvdWoSW5dEfZ9dbWA4ieJ/y/SyQC', 1, 11, NULL, 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:29:57', '2025-02-26 07:38:36', 'Rahul', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/rahul2082_20250226_073835.jpg', 'active', 0, 'Lakhisarai', '7903106797', NULL),
(98, NULL, 'rajp86899@gmail.com', 'prabhat8785', NULL, '$2y$12$iIEpwpU1gwz0l97Sbmv3v.Q/fz9MSvAPyBj1RuattmPnDcQJKFWci', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:31:27', '2025-02-26 05:58:51', 'Prabhat', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/prabhat8785_20250226_055851.jpg', 'active', 0, 'Lakhisarai', '7992238916', NULL),
(99, NULL, 'mdzafaransarimgs@gmail.com', 'md2985', NULL, '$2y$12$XoYpr6zx9xbn9zVH76u.3u4oiSlxwXcnehe11NKT2WcjIv2JSD5L6', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:32:49', '2025-02-26 07:31:46', 'MD', 'Zafar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/md2985_20250226_073146.jpg', 'active', 0, 'Lakhisarai', '7754056575', NULL),
(100, NULL, 'dk2780429@gmail.com', 'dharmendra1641', NULL, '$2y$12$iodvu4oxa3s11Or87Ut2POJAdNzbgqGrFrlupR2ceM9KX.MszA1CK', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:34:34', '2025-03-08 05:40:15', 'Dharmendra', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/dharmendra1641_20250308_054015.jpg', 'active', 0, 'Lakhisarai', '9304201850', NULL),
(101, NULL, 'aayush@sugslloyds.com', 'aayush5773', NULL, '$2y$12$FatWYt9YE.wg9OSOY/zxx.LBQrrTRh5cXjb9XYrbo3kMAM6SNk0pe', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:36:05', '2025-02-26 05:41:36', 'Aayush', 'Gautam', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/aayush5773_20250226_054136.jpg', 'active', 0, 'Muzaffarpur', '9905763313', NULL),
(102, NULL, 'kumarraunak504@gmail.com', 'raunak3361', NULL, '$2y$12$fNT1M24Lc1P1JjoOZVF7q.EO5TtbXAnTUKt1MKF80xv07XTMssSii', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:37:51', '2025-02-26 07:14:02', 'Raunak', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/raunak3361_20250226_071402.jpg', 'active', 0, 'Muzaffarpur', '7632893838', NULL),
(103, NULL, 'rohitkuma200@gmail.com', 'rohit6719', NULL, '$2y$12$Kiv..zDhq5VjxJpXkLsPF.2ykxiuRG7Boib7zPmrl.s96xcQojHXC', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:39:03', '2025-02-26 06:24:57', 'Rohit', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/rohit6719_20250226_062457.jpg', 'active', 0, 'Muzaffarpur', '7007078395', NULL),
(104, NULL, 'Amirkhanvaishali@gmail.com', 'aamir4108', NULL, '$2y$12$xB7ns38EJs7IOCufr7VtBezyCcT52rF0soG5LmEnjBeO85PZ9PMpq', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:40:16', '2025-02-26 08:12:25', 'Aamir', 'Khan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/aamir4108_20250226_081225.jpg', 'active', 0, 'Muzaffarpur', '7070275560', NULL),
(105, NULL, 'sonukumarray303@gmail.com', 'sonu4923', NULL, '$2y$12$hd8Btlilln30GnJBTNvbA.a.3WzkhcUZgZdw1rjwRFKTAwuLFvymK', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:41:19', '2025-02-26 07:21:39', 'Sonu', 'Kumar Ray', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/sonu4923_20250226_072139.jpg', 'active', 0, 'Muzaffarpur', '9135860859', NULL),
(106, NULL, 'sa811387@gmail.com', 'sohail5146', NULL, '$2y$12$RWcIWLGWnzIEc703XYfcie2P9eqEUgb4BR4j0oDkvnPDPz2Rgf8EO', 1, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-08 07:42:48', '2025-03-04 07:08:36', 'Sohail', 'Ansari', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/sohail5146_20250304_070836.jpg', 'active', 0, 'Muzaffarpur', '9871183972', NULL),
(108, 'Aditya Ranjan', 'kumaraditya88888@gmail.com', 'aditya1581', NULL, '$2y$12$GEDYref99R1.EwBvusuGOe91OaO9PLFRTvRbasj/SpqRRamUvdcee', 1, 11, NULL, 117, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-10 08:03:40', '2025-02-26 07:42:01', 'Aditya', 'Ranjan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/aditya1581_20250226_074201.jpg', 'active', 0, 'Darbhanga', '6209551526', NULL),
(109, 'Desh Bhushan', 'deshbhushan706@gmail.com', 'desh9988', NULL, '$2y$12$7LCMNiWOqzfPSV09zk6o/.ZceThTk7p0VcNJTlswYDE/lVyL4.9e2', 1, 11, NULL, 117, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-10 08:05:04', '2025-02-26 06:09:54', 'Desh', 'Bhushan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/desh9988_20250226_060954.jpg', 'active', 0, 'Darbhanga', '8340629953', NULL),
(110, 'Abhishek Kumar Singh', 'Cuteabhishek330@gmail.com', 'abhishekkumarsingh2090', NULL, '$2y$12$VKOjXXnZ.WjgVruojoQE8OrzUPG1csWEfZOBbgLusNvdjvaDpTsf6', 3, 11, NULL, 116, NULL, 'Abhishek Kumar Singh', '35710585540', NULL, 'State Bank Of India', NULL, '10DVBPS7585B1ZN', NULL, NULL, NULL, 'DVBPS7585B', NULL, '916550552177', NULL, NULL, '2025-02-10 09:22:52', '2025-02-10 09:22:52', 'Abhishek', 'Singh', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Muzaffarpur', '7992434162', NULL),
(113, 'LUV & SUN Solution Private Limited', 'NISHANTNIHAL007@GMAIL.COM', 'luv&sunsolutionprivatelimited1568', NULL, '$2y$12$PxRErRvISE68Ieuc0oEQWuwm7aWuV9.RowCmj3reUWa31S0lDJY2W', 3, 11, 117, 117, NULL, 'LUV & SUN Solution Private Limited', '39525942095', NULL, 'State Bank Of India', NULL, '10AAECL2821M1ZS', NULL, NULL, NULL, 'AAECL2821M', NULL, '240245427544', NULL, NULL, '2025-02-10 09:59:42', '2025-02-10 09:59:42', 'Nishant', 'Nihal', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Darbhanga', '6200084818', NULL),
(114, 'R.A.S Construction', 'rasconstructionmfp@gmail.com', 'r.a.sconstruction6327', NULL, '$2y$12$z79iB2XiuEMT6WqPbbEgc..ThoGJKyDiAPiVpymfQp2JTlj2o1VJC', 3, 11, NULL, 117, NULL, 'R.A.S Construction', '78960200002123', NULL, 'Bank Of Baroda', NULL, '10CWQPS0985N1ZS', NULL, NULL, NULL, 'CWQPS0985N', NULL, '709131180432', NULL, NULL, '2025-02-10 10:07:14', '2025-02-10 10:07:14', 'Chandan Kumar', 'Singh', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Darbhanga', '8235118357', NULL),
(115, 'BITTU KUMAR SINGH', 'bittukumarsingh0729@gmail.com', 'bittukumarsingh6544', NULL, '$2y$12$pl74YgXwg0RpND6FSRF6W.UWErbjeRnrhBopKsvLTa81mw5TT5cPC', 3, 11, NULL, 117, NULL, 'BITTU KUMAR SINGH', '000734001105794', NULL, 'Siwan Central Co-op Bank', NULL, 'N/A', NULL, NULL, NULL, 'RZSPS5442A', NULL, '622071153442', NULL, NULL, '2025-02-10 10:39:15', '2025-02-10 10:39:15', 'Bittu', 'Singh', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Darbhanga', '9955345759', NULL),
(116, 'Rupesh', 'rupesh@sugslloyds.com', 'rupesh2751', NULL, '$2y$12$N3hQfsAV6U2IFUVXD/RWXuaEFzc01Hz978j6Oy4D1JkCg8/DYnceW', 2, 11, NULL, 116, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'p8ii2pMXpQVRyhZGN9uMUaqINDiUKGxvQUl8m1VJEGoiXAuDr5y6GcjHOMQK', '2025-02-11 07:03:15', '2025-02-27 05:59:15', 'Rupesh', 'Pratap', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/rupesh2751_20250227_055915.jpg', 'active', 0, 'Muzaffarpur and Lakhisarai', '7999411502', NULL),
(117, 'Satish Thakur', 'satish@sugslloyds.com', 'satish7055', NULL, '$2y$12$qsEDy02JEYwX/4raYfe2r.cVu3jjPrDu0VcRKd/1PoDUB63dJ2Hka', 2, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '6tHv9OaiQslMooHPQWFgeKCc711mZSYkCT4QxZUgtCIA8pNxUdeKTJbhEy51', '2025-02-11 07:05:21', '2025-02-26 04:30:57', 'Satish', 'Thakur', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/satish7055_20250226_043057.jpg', 'active', 0, 'Darbhanga', '7503348636', NULL),
(118, NULL, 'roushan@sugslloyds.com', 'roushankumar1037', NULL, '$2y$12$2RH3kbRrkYpmMwpAy6d2Iu/HhhzXnsz8XUikWOjI9JGyd4FdVbkVO', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-14 10:27:13', '2025-02-14 10:27:13', 'Roushan Kumar', 'Ray', NULL, 'active', 0, 'Patna , BIHAR', '9289909933', NULL),
(119, NULL, 'avisinghgulshan@gmail.com', 'abhishek2675', NULL, '$2y$12$03SLL4IjOvxudYpApCCqTeN5SK4f4fNcS2WtyQR11zyQAcIUuwnhu', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-17 07:31:37', '2025-02-26 05:42:41', 'Abhishek', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/abhishek2675_20250226_054241.jpg', 'active', 0, 'Lakhisarai', '6200720601', NULL),
(120, NULL, 'rohitkumarchauhan237@gmail.com', 'rohit9201', NULL, '$2y$12$HvwZCPE7WStinTs3VIweUuT1rwdjdOqGIqjMs.2ZOrkK9QcShCFf.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-17 07:33:28', '2025-03-05 11:03:57', 'Rohit', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/rohit9201_20250305_110357.jpg', 'active', 0, 'Muzaffarpur', '9835613092', NULL),
(121, 'AAYAT ENTERPRISES', 'amberbaheri@gmail.com', 'aayatenterprises9209', NULL, '$2y$12$IwuzHPdSrm9vomwuyeCMlOGkB2zJQ3tny.eTi34YW2VYCgBshjxWi', 3, 11, NULL, 91, NULL, 'MUNNI KUMARI MAHTO', '41906719589', NULL, 'State Bank Of India', NULL, '10ADJPM9444Q1ZO', NULL, NULL, NULL, 'ADJPM9444Q', NULL, '451863619622', NULL, NULL, '2025-02-17 09:08:35', '2025-02-27 05:32:02', 'Bablu', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/aayatenterprises9209_20250227_053202.jpg', 'active', 0, 'Madhepura', '8409049840', NULL),
(122, 'DELIGHT POWER PRIVATE LIMITED', 'post2aftab@gmail.com', 'delightpowerprivatelimited5773', NULL, '$2y$12$ECV.QbZomidQuBZIJQKFGejGx.5ENeKdrto2AccL6Y9UxDdLFJ9gG', 3, 11, NULL, 91, NULL, 'DELIGHT POWER PRIVATE LIMITED', '50200067747383', NULL, 'HDFC Bank', NULL, '10AAICD7568F1ZP', NULL, NULL, NULL, 'CJTPA4081R', NULL, '861842676235', NULL, NULL, '2025-02-17 09:13:51', '2025-02-17 09:13:51', 'Aftab', 'Alam', NULL, 'active', 0, 'Madhepura', '9939193030', NULL),
(126, 'Evedin Technologies LLP', 'ppant13@gmail.com', 'evedintechnologiesllp7600', NULL, '$2y$12$.lQWTA6SmIANyPJHqG5yT.f0nqpu6hHPITia9ikxwt2P4.0y2ur7K', 3, 11, NULL, 91, NULL, 'Mr . RAJESH KUMAR', '34938194930', NULL, 'State Bank Of India', NULL, '07AALFE3208K1ZE', NULL, NULL, NULL, 'AALFE3208K', NULL, '309859841543', NULL, NULL, '2025-02-17 09:21:55', '2025-02-17 09:21:55', 'Prakash', 'Pant', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Madhepura', '7065471579', NULL),
(127, 'Harshita and Raghuraj Pratap Tradors (S)', 'biren.alpha123@gmail.com', 'harshitaandraghurajprataptradors8503', NULL, '$2y$12$J06mbkl3gIq5P5gHe/stJOoDoe8MCFlinWdALwqgCUXjlXQ9Xh1VG', 3, 11, NULL, 91, NULL, 'Biren Singh', '50200096121936', NULL, 'HDFC Bank', NULL, '10JYVPS1207M1ZX', NULL, NULL, NULL, 'JYVPS1207M', NULL, '803013625908', NULL, NULL, '2025-02-17 09:23:49', '2025-02-17 09:23:49', 'Biren', 'Singh', 'https://randomuser.me/api/portraits/men/3.jpg', 'active', 0, 'Madhepura', '8294497332', NULL),
(131, 'Shiv Enterprises', 'shiventerprises8051@gmail.com', 'shiventerprises9535', NULL, '$2y$12$nMKWMx7klqHbXZIECV.mveBL.Nuo7MZq.dxrkB4eXirlDkcvJqm3y', 3, 11, NULL, 91, NULL, 'Mr. ASHUTOSH KUMAR', '31861440781', NULL, 'State Bank Of India', NULL, 'N/A', NULL, NULL, NULL, 'CFQPK9445G', NULL, '948135208631', NULL, NULL, '2025-02-17 09:36:23', '2025-02-27 07:37:59', 'Ashutosh', 'Kumar', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/shiventerprises9535_20250227_073759.jpg', 'active', 0, 'Madhepura', '8051950990', NULL),
(137, 'ASSETS MANAGEMENT SERVICES', 'basantjha940@gmail.com', 'assetsmanagementservices3290', NULL, '$2y$12$rxQh7DblXx3U0.kYQFZbduauAMdE/GzDd05rzz7GW1PilbBBVu3N.', 3, 11, NULL, 116, NULL, 'Basant  kumar Jha', '917010077525080', NULL, 'N/A', NULL, 'N/A', NULL, NULL, NULL, 'ASVPJ0542K', NULL, '214657194151', NULL, NULL, '2025-02-18 07:34:21', '2025-02-26 11:32:37', 'Basant  kumar', 'Jha', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/assetsmanagementservices3290_20250226_113237.jpg', 'active', 0, 'Lakhisarai', '6202110990', NULL),
(138, 'Ajay Kumar Sahani', 'niteshkr31381265@gmail.com', 'ajaykumarsahani3522', NULL, '$2y$12$MJW9y1SVXkigrGSs.SUBSexajqkCgNSQLeejXL7WdMxOmewc43t36', 3, 11, NULL, 116, NULL, 'Ajay Kumar Sahani', '36883420659', NULL, 'State Bank Of India', NULL, '10HYLPS6499MIZD', NULL, NULL, NULL, 'HYLPS6499M', NULL, '588281979748', NULL, NULL, '2025-02-18 07:36:49', '2025-03-05 10:49:32', 'Ajay Kumar', 'Sahani', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/ajaykumarsahani3522_20250305_104932.jpg', 'active', 0, 'Muzaffarpur', '7488827209', NULL),
(139, 'Ridhi Sidhi Construction', 'rakeshranjan1983@yahoo.com', 'ridhisidhiconstruction5583', NULL, '$2y$12$roONgHtdMMq6eKP.wS9DJ.ZJXeb64p40AIPMgiO1wns0NpjaU0xWm', 3, 11, NULL, 116, NULL, 'Rakesh Ranjan', '921020006747374', NULL, 'N/A', NULL, 'N/A', NULL, NULL, NULL, 'BRIPR57OG', NULL, '352084498567', NULL, NULL, '2025-02-18 07:39:05', '2025-03-05 06:23:50', 'Rakesh', 'Ranjan', 'https://sugslloyd.s3.ap-south-1.amazonaws.com/users/avatar/ridhisidhiconstruction5583_20250305_062350.jpg', 'active', 0, 'Muzaffarpur', '7893668775', NULL),
(146, 'Vashisht Infra Solutions', 'vashishtinfrasolution@gmail.com', 'vashishtinfrasolutions9802', NULL, '$2y$12$Tmtq25iA8HoT2RZgSyFYceD9cdoD.t.M69LkIrC8RiZb534.E1HOC', 3, NULL, NULL, NULL, NULL, 'Vashisht Infra Solutions', '924020024144532', NULL, 'Axis Bank', NULL, '06AFSPD0700P1ZE', NULL, NULL, NULL, 'AFSPD0700P', NULL, '606659385094', NULL, NULL, '2025-03-05 09:22:16', '2025-03-05 09:22:16', 'Suresh', 'Dutt', NULL, 'active', 0, 'Building No./Flat No.: 681/6 Name Of Premises/Building: Gali Number 3 Road/Street: Mehlana Road Locality/Sub Locality: Ashok Vihar City/Town/Village: Sonipat District: Sonipat State: Haryana PIN Code: 131001', '8950803714', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_project_id_foreign` (`project_id`),
  ADD KEY `inventory_site_id_foreign` (`site_id`),
  ADD KEY `inventory_store_id_foreign` (`store_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_user`
--
ALTER TABLE `project_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_user_project_id_foreign` (`project_id`),
  ADD KEY `project_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sites_project_id_foreign` (`project_id`),
  ADD KEY `sites_state_foreign` (`state`),
  ADD KEY `sites_district_foreign` (`district`),
  ADD KEY `sites_breda_sl_no_index` (`breda_sl_no`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stores_project_id_foreign` (`project_id`),
  ADD KEY `stores_store_incharge_id_foreign` (`store_incharge_id`);

--
-- Indexes for table `streelight_poles`
--
ALTER TABLE `streelight_poles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `streelight_poles_task_id_foreign` (`task_id`);

--
-- Indexes for table `streetlights`
--
ALTER TABLE `streetlights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `streetlights_project_id_foreign` (`project_id`);

--
-- Indexes for table `streetlight_tasks`
--
ALTER TABLE `streetlight_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `streetlight_tasks_project_id_foreign` (`project_id`),
  ADD KEY `streetlight_tasks_engineer_id_foreign` (`engineer_id`),
  ADD KEY `streetlight_tasks_vendor_id_foreign` (`vendor_id`),
  ADD KEY `streetlight_tasks_manager_id_foreign` (`manager_id`),
  ADD KEY `streetlight_tasks_site_id_foreign` (`site_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_id_foreign` (`project_id`),
  ADD KEY `tasks_site_id_foreign` (`site_id`),
  ADD KEY `tasks_vendor_id_foreign` (`vendor_id`),
  ADD KEY `tasks_manager_id_foreign` (`manager_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_manager_id_foreign` (`manager_id`),
  ADD KEY `users_site_engineer_id_foreign` (`site_engineer_id`),
  ADD KEY `users_project_id_foreign` (`project_id`),
  ADD KEY `users_assigned_by_foreign` (`assigned_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=723;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6744;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `project_user`
--
ALTER TABLE `project_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8515;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `streelight_poles`
--
ALTER TABLE `streelight_poles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `streetlights`
--
ALTER TABLE `streetlights`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1004;

--
-- AUTO_INCREMENT for table `streetlight_tasks`
--
ALTER TABLE `streetlight_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=482;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_user`
--
ALTER TABLE `project_user`
  ADD CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sites`
--
ALTER TABLE `sites`
  ADD CONSTRAINT `sites_district_foreign` FOREIGN KEY (`district`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sites_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sites_state_foreign` FOREIGN KEY (`state`) REFERENCES `states` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stores_store_incharge_id_foreign` FOREIGN KEY (`store_incharge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `streetlights`
--
ALTER TABLE `streetlights`
  ADD CONSTRAINT `streetlights_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `streetlight_tasks`
--
ALTER TABLE `streetlight_tasks`
  ADD CONSTRAINT `streetlight_tasks_engineer_id_foreign` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `streetlight_tasks_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `streetlight_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `streetlight_tasks_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `streetlights` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `streetlight_tasks_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_site_engineer_id_foreign` FOREIGN KEY (`site_engineer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;
