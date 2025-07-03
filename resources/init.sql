/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:2;', 1750960924),
	('laravel_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1750960924;', 1750960924),
	('laravel_cache_financial_year', 's:4:"1403";', 2066416358),
	('laravel_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1750941208),
	('laravel_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1750941208;', 1750941208);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `deposits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `payer_type` enum('owner','tenant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,0) NOT NULL,
  `financial_year` int(11) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deposits_unit_id_foreign` (`unit_id`),
  KEY `deposits_financial_year_index` (`financial_year`),
  CONSTRAINT `deposits_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
INSERT INTO `deposits` (`id`, `unit_id`, `payer_type`, `amount`, `financial_year`, `is_paid`, `created_at`, `updated_at`) VALUES
	(1, 5, 'owner', 1000000000, 1404, 0, '2025-06-26 14:53:48', '2025-06-26 15:22:43'),
	(2, 5, 'tenant', 1, 1404, 0, '2025-06-26 15:29:26', '2025-06-26 15:29:26');
/*!40000 ALTER TABLE `deposits` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(14,0) NOT NULL,
  `payer_type` enum('owner','tenant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `financial_year` int(11) NOT NULL,
  `unit_ids` json DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_financial_year_index` (`financial_year`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` (`id`, `title`, `total_amount`, `payer_type`, `date_from`, `date_to`, `financial_year`, `unit_ids`, `attachment`, `created_at`, `updated_at`) VALUES
	(1, 'شارژ تیر ماه', 15000000, 'tenant', '2025-06-22', NULL, 1404, '["5", "3", "4"]', NULL, '2025-06-26 16:55:55', '2025-06-27 13:11:27'),
	(2, 'خرابی آسانسور', 800000, 'owner', '2025-06-22', NULL, 1404, '["5"]', NULL, '2025-06-27 06:28:05', '2025-06-27 06:28:05');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(13, '2025_06_26_123244_create_units_table', 2),
	(14, '2025_06_26_123245_create_expenses_table', 2),
	(15, '2025_06_26_123245_create_payments_table', 2),
	(16, '2025_06_26_124245_create_deposits_table', 2),
	(17, '2025_06_26_125523_create_unit_expense_details_table', 2),
	(18, '2025_06_26_125620_create_payment_usages_table', 2),
	(20, '2025_06_26_151450_add_financial_year', 3),
	(21, '2025_06_26_154744_add_unit_ids_to_expences', 4),
	(22, '2025_06_26_155711_update_unit_expense_details', 5),
	(23, '2025_06_26_155956_update_deposit_remove_payment_id', 6),
	(24, '2025_06_27_201830_create_settings_table', 7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `payer_type` enum('owner','tenant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,0) NOT NULL,
  `paid_at` date NOT NULL,
  `financial_year` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_unit_id_foreign` (`unit_id`),
  KEY `payments_financial_year_index` (`financial_year`),
  CONSTRAINT `payments_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` (`id`, `unit_id`, `payer_type`, `amount`, `paid_at`, `financial_year`, `description`, `created_at`, `updated_at`) VALUES
	(1, 5, 'tenant', 10000000, '2025-06-26', 1404, NULL, '2025-06-26 16:13:24', '2025-06-27 12:31:19'),
	(2, 5, 'owner', 14000000, '2025-07-09', 1404, NULL, '2025-06-27 06:29:16', '2025-06-27 11:33:58');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `payment_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `payable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payable_id` bigint(20) unsigned DEFAULT NULL,
  `amount_used` decimal(14,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_usages_payment_id_foreign` (`payment_id`),
  KEY `payment_usages_payable_type_payable_id_index` (`payable_type`,`payable_id`),
  CONSTRAINT `payment_usages_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `payment_usages` DISABLE KEYS */;
INSERT INTO `payment_usages` (`id`, `payment_id`, `payable_type`, `payable_id`, `amount_used`, `created_at`, `updated_at`) VALUES
	(25, 1, 'App\\Models\\UnitExpenseDetail', 6, 0, '2025-06-27 20:52:15', '2025-06-27 20:52:15');
/*!40000 ALTER TABLE `payment_usages` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('86PnMtqYR2YlUpygxSSRJzsaM9mSk2XJNLcGPGed', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidmhhRE9aTUtQNWRUSm1DaVZiWmhKN05vUTZtbjh5M2Zhb0RiazV3MSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDFhaUdZQ2J3SWF2NE0yUndjelR1Sk9HSTRvZzZrQWFmVVltaHVScW5neUdUdVI0YnpaWjZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0NToiaHR0cDovL2J1aWxkaW5nLW1hbmFnZXIuc2VydmVyL2FkbWluL2V4cGVuc2VzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1751556401),
	('B21CrddTVEF9Ug2xpNMjTxk7l6VfnOUucMe2n1AU', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNUlLb3JJMmgycmx4cUlEYkw2TFBUNFBWb3MyOHR2QUd0aTNuU2wwYiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDFhaUdZQ2J3SWF2NE0yUndjelR1Sk9HSTRvZzZrQWFmVVltaHVScW5neUdUdVI0YnpaWjZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1MjoiaHR0cDovL2J1aWxkaW5nLW1hbmFnZXIuc2VydmVyL2FkbWluL2V4cGVuc2VzLzEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1751556344),
	('bUtIlOlf8ac5IWSlt8rTeFjnHKQ6l5piCnLhjF0R', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVzVKd0FPYVZkZW1ZZ29vRWF0OHNkY2wwcjB3STUzYm5EeGl1dDlOTiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDFhaUdZQ2J3SWF2NE0yUndjelR1Sk9HSTRvZzZrQWFmVVltaHVScW5neUdUdVI0YnpaWjZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0NToiaHR0cDovL2J1aWxkaW5nLW1hbmFnZXIuc2VydmVyL2FkbWluL2RlcG9zaXRzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJmaWxhbWVudCI7YTowOnt9fQ==', 1751057711),
	('BWnfVq8PgnaHsscuMfc6prFAAbSVlc52BGKJqutn', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiREpMYzZGZGphYzYxNmdoYm9WNGMxNmMwUWJhamNCSE5aNDlBZ2RLViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDFhaUdZQ2J3SWF2NE0yUndjelR1Sk9HSTRvZzZrQWFmVVltaHVScW5neUdUdVI0YnpaWjZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo2NDoiaHR0cDovL2J1aWxkaW5nLW1hbmFnZXIuc2VydmVyL2FkbWluL3VuaXQtZXhwZW5zZS1kZXRhaWxzLzYvZWRpdCI7fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1751031385),
	('kpx1eUoV5j36IQr3xneeSBkrwEaCWsGo0dYZWzs3', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoia3haZGZmMnB0enBIR0w0TFhvcUE2U1JDRmNDekhVdVRFa1c3ZFljaCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDFhaUdZQ2J3SWF2NE0yUndjelR1Sk9HSTRvZzZrQWFmVVltaHVScW5neUdUdVI0YnpaWjZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1MjoiaHR0cDovL2J1aWxkaW5nLW1hbmFnZXIuc2VydmVyL2FkbWluL2V4cGVuc2VzLzEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1751556344);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'financial_year', '1404', '2025-06-27 20:46:32', '2025-06-27 20:49:30');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` (`id`, `number`, `owner_name`, `tenant_name`, `created_at`, `updated_at`) VALUES
	(1, '1', 'یسب', NULL, '2025-06-26 14:42:04', '2025-06-26 14:42:04'),
	(2, '2', 'سلیمی', NULL, '2025-06-26 14:42:25', '2025-06-26 14:42:25'),
	(3, '3', 'کیا', NULL, '2025-06-26 14:42:37', '2025-06-26 14:42:37'),
	(4, '4', 'میردار', NULL, '2025-06-26 14:42:42', '2025-06-26 14:42:42'),
	(5, '5', 'دادوئی', NULL, '2025-06-26 14:42:47', '2025-06-26 14:42:47'),
	(6, 'cf', 'sdc', NULL, '2025-06-26 20:37:52', '2025-06-26 20:37:52');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `unit_expense_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `payer_type` enum('owner','tenant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_due` decimal(14,0) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `financial_year` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_expense_details_expense_id_foreign` (`expense_id`),
  KEY `unit_expense_details_unit_id_foreign` (`unit_id`),
  KEY `unit_expense_details_financial_year_index` (`financial_year`),
  CONSTRAINT `unit_expense_details_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_expense_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `unit_expense_details` DISABLE KEYS */;
INSERT INTO `unit_expense_details` (`id`, `expense_id`, `unit_id`, `payer_type`, `amount_due`, `is_paid`, `financial_year`, `created_at`, `updated_at`) VALUES
	(6, 2, 5, 'owner', 800000, 0, 1404, '2025-06-27 06:28:05', '2025-06-27 20:52:15'),
	(13, 1, 5, 'tenant', 5000000, 1, 1404, '2025-06-27 13:08:21', '2025-06-27 13:11:27'),
	(14, 1, 6, 'tenant', 5000000, 1, 1404, '2025-06-27 13:11:08', '2025-06-27 20:51:04'),
	(15, 1, 3, 'tenant', 5000000, 0, 1404, '2025-06-27 13:11:27', '2025-06-27 13:11:27'),
	(16, 1, 4, 'tenant', 5000000, 0, 1404, '2025-06-27 13:11:27', '2025-06-27 13:11:27');
/*!40000 ALTER TABLE `unit_expense_details` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'admin@kashizadeh.ir', NULL, '$2y$12$1aiGYCbwIav4M2RwczTuJOGI4og6kAafUYmhuRqngyGTuR4bzZZ6W', 'EuKjTSFAEqquiZwi8Tc9nm7bWb7bQKzEVs5FDqxVnlrMwvfu6rl0MAwkAGmh', '2025-06-26 12:30:03', '2025-06-26 12:30:03'),
	(2, 'Test User', 'test@example.com', '2025-06-27 20:44:51', '$2y$12$gAEz0epuVkuB7y7P.kUf2./yjDiH.GEv9VILp190vjPm9iZdN4i/S', '0yfgMmQNaG', '2025-06-27 20:44:52', '2025-06-27 20:44:52');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
