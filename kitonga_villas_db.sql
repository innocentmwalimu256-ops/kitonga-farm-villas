-- Kitonga Farm Villas Database Backup
-- Generated: 2026-09-03 15:34:49

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `accommodation_amenity`;
CREATE TABLE `accommodation_amenity` (
  `accommodation_type_id` bigint(20) unsigned NOT NULL,
  `amenity_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`accommodation_type_id`,`amenity_id`),
  KEY `accommodation_amenity_amenity_id_foreign` (`amenity_id`),
  CONSTRAINT `accommodation_amenity_accommodation_type_id_foreign` FOREIGN KEY (`accommodation_type_id`) REFERENCES `accommodation_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accommodation_amenity_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '1');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '2');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '3');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '5');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '6');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '7');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('1', '8');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('2', '1');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('2', '3');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('2', '5');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('2', '6');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('2', '7');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '1');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '2');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '3');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '4');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '5');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '7');
INSERT INTO `accommodation_amenity` (`accommodation_type_id`, `amenity_id`) VALUES ('3', '8');

DROP TABLE IF EXISTS `accommodation_types`;
CREATE TABLE `accommodation_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `base_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `bedrooms` int(11) NOT NULL DEFAULT 1,
  `beds` int(11) NOT NULL DEFAULT 1,
  `bathrooms` int(11) NOT NULL DEFAULT 1,
  `has_interior_kitchen` tinyint(1) NOT NULL DEFAULT 0,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery_images`)),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `minimum_stay` int(11) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accommodation_types_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `accommodation_types` (`id`, `name`, `slug`, `description`, `short_description`, `base_price`, `capacity`, `bedrooms`, `beds`, `bathrooms`, `has_interior_kitchen`, `featured_image`, `gallery_images`, `active`, `minimum_stay`, `sort_order`, `created_at`, `updated_at`) VALUES ('1', 'Luxury Villa', 'luxury-villa', 'Experience premium luxury surrounded by peaceful farm landscape. Features spacious open veranda, private bathroom, and direct access to swimming pool.', 'The ultimate luxury country escape.', '250000.00', '2', '1', '1', '1', '0', 'luxury_villa_img.webp', '[]', '1', '1', '0', '2026-08-26 21:12:06', '2026-08-26 21:12:06');
INSERT INTO `accommodation_types` (`id`, `name`, `slug`, `description`, `short_description`, `base_price`, `capacity`, `bedrooms`, `beds`, `bathrooms`, `has_interior_kitchen`, `featured_image`, `gallery_images`, `active`, `minimum_stay`, `sort_order`, `created_at`, `updated_at`) VALUES ('2', 'Semi Luxury Villa', 'semi-luxury-villa', 'Comfortable and private escape featuring standard premium amenities, beautiful garden view and access to common farm gardens and pool.', 'Perfect balance of comfort and farm experience.', '200000.00', '2', '1', '1', '1', '0', 'semi_luxury_villa_img.webp', '[]', '1', '1', '0', '2026-08-26 21:12:07', '2026-08-26 21:12:07');
INSERT INTO `accommodation_types` (`id`, `name`, `slug`, `description`, `short_description`, `base_price`, `capacity`, `bedrooms`, `beds`, `bathrooms`, `has_interior_kitchen`, `featured_image`, `gallery_images`, `active`, `minimum_stay`, `sort_order`, `created_at`, `updated_at`) VALUES ('3', 'Family Villa', 'family-villa', 'Spacious 2-bedroom house with an interior kitchen, large private dining area, and dedicated parking. Ideal for families and small groups wanting home-cooked farm food.', '2 Bedrooms + Interior Kitchen.', '400000.00', '6', '2', '3', '2', '1', 'family_villa_img.webp', '[]', '1', '1', '0', '2026-08-26 21:12:09', '2026-08-30 20:21:59');

DROP TABLE IF EXISTS `accommodation_units`;
CREATE TABLE `accommodation_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accommodation_type_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `housekeeping_status` varchar(255) NOT NULL DEFAULT 'clean',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accommodation_units_name_unique` (`name`),
  KEY `accommodation_units_accommodation_type_id_foreign` (`accommodation_type_id`),
  CONSTRAINT `accommodation_units_accommodation_type_id_foreign` FOREIGN KEY (`accommodation_type_id`) REFERENCES `accommodation_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `accommodation_units` (`id`, `accommodation_type_id`, `name`, `status`, `housekeeping_status`, `notes`, `created_at`, `updated_at`) VALUES ('1', '1', 'V1 - Luxury Villa 1', 'active', 'clean', NULL, '2026-08-26 21:12:07', '2026-08-26 21:12:07');
INSERT INTO `accommodation_units` (`id`, `accommodation_type_id`, `name`, `status`, `housekeeping_status`, `notes`, `created_at`, `updated_at`) VALUES ('2', '1', 'V2 - Luxury Villa 2', 'active', 'clean', NULL, '2026-08-26 21:12:07', '2026-08-26 21:12:07');
INSERT INTO `accommodation_units` (`id`, `accommodation_type_id`, `name`, `status`, `housekeeping_status`, `notes`, `created_at`, `updated_at`) VALUES ('3', '2', 'S1 - Semi Luxury 1', 'active', 'clean', NULL, '2026-08-26 21:12:09', '2026-08-26 21:12:09');
INSERT INTO `accommodation_units` (`id`, `accommodation_type_id`, `name`, `status`, `housekeeping_status`, `notes`, `created_at`, `updated_at`) VALUES ('4', '2', 'S2 - Semi Luxury 2', 'active', 'clean', NULL, '2026-08-26 21:12:09', '2026-08-26 21:12:09');
INSERT INTO `accommodation_units` (`id`, `accommodation_type_id`, `name`, `status`, `housekeeping_status`, `notes`, `created_at`, `updated_at`) VALUES ('5', '3', 'F1 - Family House 1', 'active', 'clean', NULL, '2026-08-26 21:12:10', '2026-08-26 21:12:10');

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('1', 'default', 'tour_booking_created', 'App\\Models\\Booking', 'tour_booking_created', '1', NULL, NULL, '{\"metadata\":{\"reference\":\"KTG-EXP-260827-QEKY\",\"tour\":\"General Farm Tour\",\"guests\":2,\"total\":100000,\"payment_method\":\"arrival\"}}', NULL, '2026-08-27 09:22:08', '2026-08-27 09:22:08');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('2', 'default', 'booking_created', 'App\\Models\\Booking', 'booking_created', '2', NULL, NULL, '{\"new\":{\"reference\":\"KFV-LE8-6545\",\"customer_id\":5,\"accommodation_unit_id\":1,\"check_in\":\"2026-08-27T00:00:00.000000Z\",\"check_out\":\"2026-08-28T00:00:00.000000Z\",\"guests_count\":2,\"status\":\"pending\",\"source\":\"online\",\"subtotal\":\"250000.00\",\"discount\":\"0.00\",\"tax\":\"45000.00\",\"total\":\"295000.00\",\"amount_paid\":\"0.00\",\"balance\":\"295000.00\",\"notes\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":\"2026-08-27T13:17:45.000000Z\",\"created_at\":\"2026-08-27T13:17:45.000000Z\",\"id\":2},\"metadata\":{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/152.0.0.0 Safari\\/537.36\"}}', NULL, '2026-08-27 13:17:46', '2026-08-27 13:17:46');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('3', 'default', 'booking_created', 'App\\Models\\Booking', 'booking_created', '3', NULL, NULL, '{\"new\":{\"reference\":\"KFV-UQW-3538\",\"customer_id\":6,\"accommodation_unit_id\":1,\"check_in\":\"2026-08-28T00:00:00.000000Z\",\"check_out\":\"2026-08-29T00:00:00.000000Z\",\"guests_count\":2,\"status\":\"pending\",\"source\":\"online\",\"subtotal\":\"250000.00\",\"discount\":\"0.00\",\"tax\":\"45000.00\",\"total\":\"295000.00\",\"amount_paid\":\"0.00\",\"balance\":\"295000.00\",\"notes\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":\"2026-08-28T17:32:02.000000Z\",\"created_at\":\"2026-08-28T17:32:02.000000Z\",\"id\":3},\"metadata\":{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/152.0.0.0 Safari\\/537.36\"}}', NULL, '2026-08-28 17:32:04', '2026-08-28 17:32:04');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('4', 'default', 'tour_booking_created', 'App\\Models\\Booking', 'tour_booking_created', '4', NULL, NULL, '{\"metadata\":{\"reference\":\"KTG-EXP-260828-1L5V\",\"tour\":\"General Farm Tour\",\"guests\":2,\"total\":100000,\"payment_method\":\"arrival\"}}', NULL, '2026-08-28 17:44:36', '2026-08-28 17:44:36');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('5', 'default', 'booking_created', 'App\\Models\\Booking', 'booking_created', '5', NULL, NULL, '{\"new\":{\"reference\":\"KFV-LWB-2850\",\"customer_id\":7,\"accommodation_unit_id\":1,\"check_in\":\"2026-08-29T00:00:00.000000Z\",\"check_out\":\"2026-08-31T00:00:00.000000Z\",\"guests_count\":2,\"status\":\"pending\",\"source\":\"online\",\"subtotal\":\"500000.00\",\"discount\":\"0.00\",\"tax\":\"90000.00\",\"total\":\"590000.00\",\"amount_paid\":\"0.00\",\"balance\":\"590000.00\",\"notes\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":\"2026-08-28T17:48:16.000000Z\",\"created_at\":\"2026-08-28T17:48:16.000000Z\",\"id\":5},\"metadata\":{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/152.0.0.0 Safari\\/537.36\"}}', NULL, '2026-08-28 17:48:16', '2026-08-28 17:48:16');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('6', 'default', 'booking_status_updated', 'App\\Models\\Booking', 'booking_status_updated', '4', 'App\\Models\\User', '1', '{\"old\":{\"status\":\"confirmed\"},\"new\":{\"status\":\"cancelled\"},\"metadata\":{\"notes\":null}}', NULL, '2026-08-28 18:08:06', '2026-08-28 18:08:06');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('7', 'default', 'booking_status_updated', 'App\\Models\\Booking', 'booking_status_updated', '4', 'App\\Models\\User', '1', '{\"old\":{\"status\":\"cancelled\"},\"new\":{\"status\":\"confirmed\"},\"metadata\":{\"notes\":null}}', NULL, '2026-08-28 18:09:12', '2026-08-28 18:09:12');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('8', 'default', 'availability_block_created', 'App\\Models\\AvailabilityBlock', 'availability_block_created', '1', 'App\\Models\\User', '1', '{\"new\":{\"accommodation_unit_id\":5,\"start_date\":\"2026-08-30T00:00:00.000000Z\",\"end_date\":\"2026-08-31T00:00:00.000000Z\",\"reason\":\"pachafu\",\"updated_at\":\"2026-08-30T19:39:18.000000Z\",\"created_at\":\"2026-08-30T19:39:18.000000Z\",\"id\":1}}', NULL, '2026-08-30 19:39:18', '2026-08-30 19:39:18');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('9', 'default', 'booking_created', 'App\\Models\\Booking', 'booking_created', '6', 'App\\Models\\User', '1', '{\"new\":{\"reference\":\"KFV-PSZ-4097\",\"customer_id\":8,\"accommodation_unit_id\":2,\"check_in\":\"2026-08-30T00:00:00.000000Z\",\"check_out\":\"2026-08-31T00:00:00.000000Z\",\"guests_count\":2,\"status\":\"pending\",\"source\":\"online\",\"subtotal\":\"250000.00\",\"discount\":\"0.00\",\"tax\":\"45000.00\",\"total\":\"295000.00\",\"amount_paid\":\"0.00\",\"balance\":\"295000.00\",\"notes\":null,\"created_by\":null,\"updated_by\":null,\"updated_at\":\"2026-08-30T19:40:44.000000Z\",\"created_at\":\"2026-08-30T19:40:44.000000Z\",\"id\":6},\"metadata\":{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/152.0.0.0 Safari\\/537.36\"}}', NULL, '2026-08-30 19:40:45', '2026-08-30 19:40:45');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES ('10', 'default', 'availability_block_deleted', NULL, 'availability_block_deleted', NULL, 'App\\Models\\User', '1', '{\"old\":{\"id\":1,\"accommodation_unit_id\":5,\"start_date\":\"2026-08-30T00:00:00.000000Z\",\"end_date\":\"2026-08-31T00:00:00.000000Z\",\"reason\":\"pachafu\",\"created_at\":\"2026-08-30T19:39:18.000000Z\",\"updated_at\":\"2026-08-30T19:39:18.000000Z\"}}', NULL, '2026-08-30 19:58:25', '2026-08-30 19:58:25');

DROP TABLE IF EXISTS `amenities`;
CREATE TABLE `amenities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `amenities_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('1', 'Free Wi-Fi', 'wifi', '2026-08-26 21:12:04', '2026-08-26 21:12:04');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('2', 'DSTV', 'tv', '2026-08-26 21:12:04', '2026-08-26 21:12:04');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('3', 'Azam TV', 'tv', '2026-08-26 21:12:04', '2026-08-26 21:12:04');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('4', 'Interior Kitchen', 'kitchen', '2026-08-26 21:12:04', '2026-08-26 21:12:04');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('5', 'Swimming Pool', 'pool', '2026-08-26 21:12:05', '2026-08-26 21:12:05');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('6', 'King Size Bed', 'bed', '2026-08-26 21:12:05', '2026-08-26 21:12:05');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('7', 'Air Conditioning', 'ac', '2026-08-26 21:12:05', '2026-08-26 21:12:05');
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES ('8', 'Private Terrace', 'terrace', '2026-08-26 21:12:06', '2026-08-26 21:12:06');

DROP TABLE IF EXISTS `availability_blocks`;
CREATE TABLE `availability_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accommodation_unit_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `availability_blocks_accommodation_unit_id_foreign` (`accommodation_unit_id`),
  KEY `availability_blocks_start_date_index` (`start_date`),
  KEY `availability_blocks_end_date_index` (`end_date`),
  CONSTRAINT `availability_blocks_accommodation_unit_id_foreign` FOREIGN KEY (`accommodation_unit_id`) REFERENCES `accommodation_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `booking_guests`;
CREATE TABLE `booking_guests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `passport_number` varchar(255) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_guests_booking_id_index` (`booking_id`),
  CONSTRAINT `booking_guests_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `booking_guests` (`id`, `booking_id`, `full_name`, `passport_number`, `nationality`, `phone`, `is_primary`, `created_at`, `updated_at`) VALUES ('1', '2', 'kashogi', NULL, NULL, '0988765433', '1', '2026-08-27 13:17:45', '2026-08-27 13:17:45');
INSERT INTO `booking_guests` (`id`, `booking_id`, `full_name`, `passport_number`, `nationality`, `phone`, `is_primary`, `created_at`, `updated_at`) VALUES ('2', '3', 'akram', NULL, NULL, '0718334267', '1', '2026-08-28 17:32:03', '2026-08-28 17:32:03');
INSERT INTO `booking_guests` (`id`, `booking_id`, `full_name`, `passport_number`, `nationality`, `phone`, `is_primary`, `created_at`, `updated_at`) VALUES ('3', '5', 'kashogi', NULL, NULL, '0000009876', '1', '2026-08-28 17:48:16', '2026-08-28 17:48:16');
INSERT INTO `booking_guests` (`id`, `booking_id`, `full_name`, `passport_number`, `nationality`, `phone`, `is_primary`, `created_at`, `updated_at`) VALUES ('4', '6', 'frwefgtrwet', NULL, NULL, 'fgrteygrtyhrt', '1', '2026-08-30 19:40:45', '2026-08-30 19:40:45');

DROP TABLE IF EXISTS `booking_items`;
CREATE TABLE `booking_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `item_type` varchar(255) NOT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `description_snapshot` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_items_booking_id_foreign` (`booking_id`),
  CONSTRAINT `booking_items_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `item_id`, `description_snapshot`, `quantity`, `unit_price_snapshot`, `total`, `created_at`, `updated_at`) VALUES ('1', '1', 'tour', '2', 'General Farm Tour (09:00 AM - 11:00 AM)', '2', '50000.00', '100000.00', '2026-08-27 09:22:06', '2026-08-27 09:22:06');
INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `item_id`, `description_snapshot`, `quantity`, `unit_price_snapshot`, `total`, `created_at`, `updated_at`) VALUES ('2', '2', 'accommodation', '1', 'Stay in Luxury Villa (1 nights @ 250,000 TZS/night)', '1', '250000.00', '250000.00', '2026-08-27 13:17:45', '2026-08-27 13:17:45');
INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `item_id`, `description_snapshot`, `quantity`, `unit_price_snapshot`, `total`, `created_at`, `updated_at`) VALUES ('3', '3', 'accommodation', '1', 'Stay in Luxury Villa (1 nights @ 250,000 TZS/night)', '1', '250000.00', '250000.00', '2026-08-28 17:32:03', '2026-08-28 17:32:03');
INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `item_id`, `description_snapshot`, `quantity`, `unit_price_snapshot`, `total`, `created_at`, `updated_at`) VALUES ('4', '4', 'tour', '2', 'General Farm Tour (09:00 AM - 11:00 AM)', '2', '50000.00', '100000.00', '2026-08-28 17:44:36', '2026-08-28 17:44:36');
INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `item_id`, `description_snapshot`, `quantity`, `unit_price_snapshot`, `total`, `created_at`, `updated_at`) VALUES ('5', '5', 'accommodation', '1', 'Stay in Luxury Villa (2 nights @ 250,000 TZS/night)', '2', '250000.00', '500000.00', '2026-08-28 17:48:16', '2026-08-28 17:48:16');
INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `item_id`, `description_snapshot`, `quantity`, `unit_price_snapshot`, `total`, `created_at`, `updated_at`) VALUES ('6', '6', 'accommodation', '1', 'Stay in Luxury Villa (1 nights @ 250,000 TZS/night)', '1', '250000.00', '250000.00', '2026-08-30 19:40:44', '2026-08-30 19:40:44');

DROP TABLE IF EXISTS `booking_status_history`;
CREATE TABLE `booking_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `from_status` varchar(255) NOT NULL,
  `to_status` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_status_history_booking_id_foreign` (`booking_id`),
  KEY `booking_status_history_user_id_foreign` (`user_id`),
  KEY `booking_status_history_created_at_index` (`created_at`),
  CONSTRAINT `booking_status_history_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_status_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('1', '1', 'initial', 'confirmed', NULL, 'Direct Experience Booking created online for General Farm Tour (2 guests).', '2026-08-27 09:22:06');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('2', '2', 'none', 'pending', NULL, 'Initial booking creation.', '2026-08-27 13:17:45');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('3', '3', 'none', 'pending', NULL, 'Initial booking creation.', '2026-08-28 17:32:03');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('4', '4', 'initial', 'confirmed', NULL, 'Direct Experience Booking created online for General Farm Tour (2 guests).', '2026-08-28 17:44:36');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('5', '5', 'none', 'pending', NULL, 'Initial booking creation.', '2026-08-28 17:48:16');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('6', '4', 'confirmed', 'cancelled', '1', 'Status changed from confirmed to cancelled.', '2026-08-28 18:08:06');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('7', '4', 'cancelled', 'confirmed', '1', 'Status changed from cancelled to confirmed.', '2026-08-28 18:09:12');
INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`) VALUES ('8', '6', 'none', 'pending', NULL, 'Initial booking creation.', '2026-08-30 19:40:45');

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `accommodation_unit_id` bigint(20) unsigned DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests_count` int(11) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `source` varchar(255) NOT NULL DEFAULT 'online',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_reference_unique` (`reference`),
  KEY `bookings_customer_id_foreign` (`customer_id`),
  KEY `bookings_accommodation_unit_id_foreign` (`accommodation_unit_id`),
  KEY `bookings_created_by_foreign` (`created_by`),
  KEY `bookings_updated_by_foreign` (`updated_by`),
  KEY `bookings_check_in_index` (`check_in`),
  KEY `bookings_check_out_index` (`check_out`),
  KEY `bookings_status_index` (`status`),
  KEY `bookings_source_index` (`source`),
  CONSTRAINT `bookings_accommodation_unit_id_foreign` FOREIGN KEY (`accommodation_unit_id`) REFERENCES `accommodation_units` (`id`),
  CONSTRAINT `bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `bookings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bookings` (`id`, `reference`, `customer_id`, `accommodation_unit_id`, `check_in`, `check_out`, `guests_count`, `status`, `source`, `subtotal`, `discount`, `tax`, `total`, `amount_paid`, `balance`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('1', 'KTG-EXP-260827-QEKY', '4', NULL, '2026-08-28', '2026-08-28', '2', 'confirmed', 'online_experience', '100000.00', '0.00', '0.00', '100000.00', '0.00', '100000.00', 'Experience: General Farm Tour | Slot: 09:00 AM - 11:00 AM | Payment: arrival', NULL, NULL, '2026-08-27 09:22:06', '2026-08-27 09:22:06');
INSERT INTO `bookings` (`id`, `reference`, `customer_id`, `accommodation_unit_id`, `check_in`, `check_out`, `guests_count`, `status`, `source`, `subtotal`, `discount`, `tax`, `total`, `amount_paid`, `balance`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('2', 'KFV-LE8-6545', '5', '1', '2026-08-27', '2026-08-28', '2', 'pending', 'online', '250000.00', '0.00', '45000.00', '295000.00', '0.00', '295000.00', NULL, NULL, NULL, '2026-08-27 13:17:45', '2026-08-27 13:17:45');
INSERT INTO `bookings` (`id`, `reference`, `customer_id`, `accommodation_unit_id`, `check_in`, `check_out`, `guests_count`, `status`, `source`, `subtotal`, `discount`, `tax`, `total`, `amount_paid`, `balance`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('3', 'KFV-UQW-3538', '6', '1', '2026-08-28', '2026-08-29', '2', 'pending', 'online', '250000.00', '0.00', '45000.00', '295000.00', '0.00', '295000.00', NULL, NULL, NULL, '2026-08-28 17:32:02', '2026-08-28 17:32:02');
INSERT INTO `bookings` (`id`, `reference`, `customer_id`, `accommodation_unit_id`, `check_in`, `check_out`, `guests_count`, `status`, `source`, `subtotal`, `discount`, `tax`, `total`, `amount_paid`, `balance`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('4', 'KTG-EXP-260828-1L5V', '6', NULL, '2026-08-29', '2026-08-29', '2', 'confirmed', 'online_experience', '100000.00', '0.00', '0.00', '100000.00', '100000.00', '0.00', 'Experience: General Farm Tour | Slot: 09:00 AM - 11:00 AM | Payment: arrival', NULL, '1', '2026-08-28 17:44:36', '2026-08-28 18:10:28');
INSERT INTO `bookings` (`id`, `reference`, `customer_id`, `accommodation_unit_id`, `check_in`, `check_out`, `guests_count`, `status`, `source`, `subtotal`, `discount`, `tax`, `total`, `amount_paid`, `balance`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('5', 'KFV-LWB-2850', '7', '1', '2026-08-29', '2026-08-31', '2', 'pending', 'online', '500000.00', '0.00', '90000.00', '590000.00', '0.00', '590000.00', NULL, NULL, NULL, '2026-08-28 17:48:16', '2026-08-28 17:48:16');
INSERT INTO `bookings` (`id`, `reference`, `customer_id`, `accommodation_unit_id`, `check_in`, `check_out`, `guests_count`, `status`, `source`, `subtotal`, `discount`, `tax`, `total`, `amount_paid`, `balance`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('6', 'KFV-PSZ-4097', '8', '2', '2026-08-30', '2026-08-31', '2', 'pending', 'online', '250000.00', '0.00', '45000.00', '295000.00', '0.00', '295000.00', NULL, NULL, NULL, '2026-08-30 19:40:44', '2026-08-30 19:40:44');

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-admin@kitonga.com|127.0.0.1', 'i:1;', '1788258850');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-admin@kitonga.com|127.0.0.1:timer', 'i:1788258850;', '1788258850');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-ownerkitonga@gmail.com|127.0.0.1', 'i:1;', '1788347198');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-ownerkitonga@gmail.com|127.0.0.1:timer', 'i:1788347198;', '1788347198');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-ownerkitongafarm@gmailcom|127.0.0.1', 'i:1;', '1788354408');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-ownerkitongafarm@gmailcom|127.0.0.1:timer', 'i:1788354407;', '1788354407');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:16:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"view_bookings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"create_bookings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"edit_bookings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"cancel_bookings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"view_revenue\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"view_profit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"view_expenses\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:12:\"create_sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:5;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"refund_sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:16:\"adjust_inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:10:\"manage_cms\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:12:\"manage_media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"manage_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"view_audit_logs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"manage_settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:6:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"owner\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:7:\"manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:9:\"reception\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:7:\"cashier\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"housekeeping\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"farm_staff\";s:1:\"c\";s:3:\"web\";}}}', '1788521728');

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cms_pages`;
CREATE TABLE `cms_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cms_pages` (`id`, `title`, `slug`, `seo_title`, `seo_description`, `active`, `created_at`, `updated_at`) VALUES ('1', 'Homepage', 'home', 'Where Luxury Meets Farm Life', 'Premium countryside accommodation and farm stay in Komkonga, Tanga.', '1', '2026-08-26 21:12:13', '2026-08-26 21:12:13');
INSERT INTO `cms_pages` (`id`, `title`, `slug`, `seo_title`, `seo_description`, `active`, `created_at`, `updated_at`) VALUES ('2', 'Our Farm', 'farm', 'Organic Agriculture & Farm-to-Table', 'Ecology guidelines and cattle feeding models in Tanga.', '1', '2026-08-26 21:12:14', '2026-08-26 21:12:14');
INSERT INTO `cms_pages` (`id`, `title`, `slug`, `seo_title`, `seo_description`, `active`, `created_at`, `updated_at`) VALUES ('3', 'Experiences & Tours', 'experiences', 'Farm Activities & Walks', 'Guided farm walks and cattle milking experiences in Tanga.', '1', '2026-08-26 21:12:14', '2026-08-26 21:12:14');
INSERT INTO `cms_pages` (`id`, `title`, `slug`, `seo_title`, `seo_description`, `active`, `created_at`, `updated_at`) VALUES ('4', 'About Us', 'about', 'The Story of Kitonga Farm Villas', 'Our history, values, and community impact.', '1', '2026-08-26 21:12:14', '2026-08-26 21:12:14');
INSERT INTO `cms_pages` (`id`, `title`, `slug`, `seo_title`, `seo_description`, `active`, `created_at`, `updated_at`) VALUES ('5', 'Gallery', 'gallery', 'Visual Gallery - Kitonga Farm Villas', 'Photos of our luxury villas and organic farms.', '1', '2026-08-26 21:12:14', '2026-08-26 21:12:14');
INSERT INTO `cms_pages` (`id`, `title`, `slug`, `seo_title`, `seo_description`, `active`, `created_at`, `updated_at`) VALUES ('6', 'Policies', 'policies', 'Booking Policies & Refund Rules', 'Read our cancellation and stay terms.', '1', '2026-08-26 21:12:15', '2026-08-26 21:12:15');

DROP TABLE IF EXISTS `cms_sections`;
CREATE TABLE `cms_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cms_page_id` bigint(20) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `value` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_sections_cms_page_id_key_unique` (`cms_page_id`,`key`),
  KEY `cms_sections_key_index` (`key`),
  CONSTRAINT `cms_sections_cms_page_id_foreign` FOREIGN KEY (`cms_page_id`) REFERENCES `cms_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cms_sections` (`id`, `cms_page_id`, `key`, `type`, `value`, `metadata`, `created_at`, `updated_at`) VALUES ('1', '1', 'hero_headline', 'text', 'Where Luxury Meets Farm Life', NULL, '2026-08-26 21:12:13', '2026-08-26 21:12:13');
INSERT INTO `cms_sections` (`id`, `cms_page_id`, `key`, `type`, `value`, `metadata`, `created_at`, `updated_at`) VALUES ('2', '1', 'hero_subheadline', 'text', 'Escape to authentic countryside serenity in Komkonga Village. Indulge in private villas, pure organic food, and premium nature tours.', NULL, '2026-08-26 21:12:13', '2026-08-26 21:12:13');
INSERT INTO `cms_sections` (`id`, `cms_page_id`, `key`, `type`, `value`, `metadata`, `created_at`, `updated_at`) VALUES ('3', '1', 'brand_story', 'text', 'Kitonga Farm Villas is a luxury countryside destination in Komkonga, Tanga. We connect high-end villa hospitality with organic farming—offering fresh fruits, swimming pool, forest trails, and cattle farm experiences.', NULL, '2026-08-26 21:12:13', '2026-08-26 21:12:13');
INSERT INTO `cms_sections` (`id`, `cms_page_id`, `key`, `type`, `value`, `metadata`, `created_at`, `updated_at`) VALUES ('4', '2', 'farm_story', 'text', 'Kitonga Farm Villas is built on a 50-acre organic reserve dedicated to agroecology. We cultivate arabica coffee, harvest raw honey, and raise dairy cows, all while maintaining absolute preservation of the native countryside flora and fauna.', NULL, '2026-08-26 21:12:14', '2026-08-26 21:12:14');
INSERT INTO `cms_sections` (`id`, `cms_page_id`, `key`, `type`, `value`, `metadata`, `created_at`, `updated_at`) VALUES ('5', '3', 'experiences_intro', 'text', 'Immerse yourself in native agrarian lifestyles and explore the Tanga landscape with our guides.', NULL, '2026-08-26 21:12:14', '2026-08-26 21:12:14');
INSERT INTO `cms_sections` (`id`, `cms_page_id`, `key`, `type`, `value`, `metadata`, `created_at`, `updated_at`) VALUES ('6', '4', 'brand_story', 'text', 'Kitonga Farm Villas was founded on the philosophy that true luxury is found in nature, silence, and clean, organic living. Tucked away in Komkonga Village within the Tanga Region, our farm-resort offers a pure, unhurried escape from modern stress. We operate on ecological farming guidelines, powering our operations with sustainability and supporting local village families.', NULL, '2026-08-26 21:12:14', '2026-08-26 21:12:14');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_phone_index` (`phone`),
  KEY `customers_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('1', 'Juma Shabaan', '+255754998877', 'juma@example.com', 'Frequent local guest, prefers quiet semi-luxury villa.', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('2', 'Sarah Jenkins', '+1415998877', 'sarah.j@example.com', 'International traveler, interested in dairy farm tour.', '2026-08-26 21:12:04', '2026-08-26 21:12:04');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('3', 'Mariam Mchome', '+255655121212', 'mariam@example.com', 'Family group booking.', '2026-08-26 21:12:04', '2026-08-26 21:12:04');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('4', 'juma', '057654796', 'steven@gmail.com', NULL, '2026-08-27 09:22:05', '2026-08-27 09:22:05');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('5', 'kashogi', '0988765433', 'stebv@gmailcom', NULL, '2026-08-27 13:17:44', '2026-08-27 13:17:44');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('6', 'mpenja', '0784523', 'mpenja@gmail.com', NULL, '2026-08-28 17:32:02', '2026-08-28 17:44:36');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('7', 'kashogi', '0000009876', 'somadian@gmail.com', NULL, '2026-08-28 17:48:16', '2026-08-28 17:48:16');
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES ('8', 'frwefgtrwet', 'fgrteygrtyhrt', 'juma@gmail.com', NULL, '2026-08-30 19:40:44', '2026-08-30 19:40:44');

DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `expense_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES ('1', 'Farm Operations', 'Seeds, livestock feed, fertilizer, and general agriculture inputs.', '2026-08-26 21:12:16', '2026-08-26 21:12:16');
INSERT INTO `expense_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES ('2', 'Resort Utilities', 'Electricity, water supply, Azam TV renewals, and laundry gas.', '2026-08-26 21:12:16', '2026-08-26 21:12:16');
INSERT INTO `expense_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES ('3', 'Staff Payroll', 'Salaries, overtime, and kitchen crew allowances.', '2026-08-26 21:12:17', '2026-08-26 21:12:17');

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `expense_category_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'approved',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `expenses_category_index` (`category`),
  KEY `expenses_date_index` (`date`),
  KEY `expenses_status_index` (`status`),
  KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `expenses` (`id`, `category`, `expense_category_id`, `amount`, `date`, `description`, `payment_method`, `attachment_path`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('1', 'farm', '1', '45000.00', '2026-08-21', 'Purchase of Organic Fertilizers: 10 bags of organic manure for greenhouses.', 'mobile_money', NULL, 'approved', '1', '2026-08-26 21:12:17', '2026-08-26 21:12:17');
INSERT INTO `expenses` (`id`, `category`, `expense_category_id`, `amount`, `date`, `description`, `payment_method`, `attachment_path`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('2', 'resort', '2', '12000.00', '2026-08-24', 'Azam TV Resort subscription: Monthly subscription for 5 villa units.', 'cash', NULL, 'approved', '1', '2026-08-26 21:12:18', '2026-08-26 21:12:18');

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `farm_tours`;
CREATE TABLE `farm_tours` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `capacity_per_slot` int(11) NOT NULL DEFAULT 20,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `video` varchar(255) DEFAULT NULL,
  `inclusions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`inclusions`)),
  `highlights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights`)),
  `good_to_know` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  UNIQUE KEY `farm_tours_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `farm_tours` (`id`, `name`, `slug`, `description`, `price`, `capacity_per_slot`, `active`, `created_at`, `updated_at`, `category`, `duration`, `featured_image`, `gallery`, `video`, `inclusions`, `highlights`, `good_to_know`, `featured`, `sort_order`, `seo_title`, `seo_description`, `status`) VALUES ('1', 'Normal Farm Tour', 'normal-farm-tour', 'A relaxed, guided entry into the rhythmic beauty of Kitonga. Wander through central palm pathways, observe seasonal fruit plantations, and understand our farming philosophy before cooling off in our rural farm bar and pool lounge.', '20000.00', '30', '1', '2026-08-26 21:12:10', '2026-08-26 21:12:10', 'Nature & Trails', '2 Hours', 'normal_farm_tour.webp', '[\"normal_farm_tour.webp\",\"gallery_img_0217.jpg\",\"gallery_img_0216.jpg\",\"gallery_img_0223.jpg\",\"gallery_img_0220.jpg\",\"farm_gallery_1.jpg\"]', NULL, '[\"Guided farm path tour\",\"Fresh coconut refreshments\",\"Access to the swimming pool\",\"A tour of the central mango orchard\"]', '[\"Vibrant papaya and organic chilli fields\",\"Relaxing countryside swimming pool\",\"Pure fresh-picked coconut juice straight from our palms\"]', 'Wear comfortable closed walking shoes, a sun hat, and bring your swimwear and towel.', '1', '1', 'Normal Farm Tour - Authentic Guided Tour', 'Tour the central farm paths, crop areas (mango, papaya, chilli) and finish with a refreshing swim in our pool.', 'published');
INSERT INTO `farm_tours` (`id`, `name`, `slug`, `description`, `price`, `capacity_per_slot`, `active`, `created_at`, `updated_at`, `category`, `duration`, `featured_image`, `gallery`, `video`, `inclusions`, `highlights`, `good_to_know`, `featured`, `sort_order`, `seo_title`, `seo_description`, `status`) VALUES ('2', 'General Farm Tour', 'general-farm-tour', 'Our complete agritourism experience. Dive deep into all operational aspects of Kitonga Farm: visit vanilla and strawberry greenhouses, interact with dairy and poultry livestock, and taste farm-fresh organic items right from the soil.', '50000.00', '15', '1', '2026-08-26 21:12:10', '2026-08-26 21:12:10', 'Complete Ecosystem', '4 Hours', 'general_farm_hero.webp', '[\"general_farm_hero.webp\",\"three_cows.webp\",\"download_41.webp\",\"download_40.webp\"]', NULL, '[\"Complete farm tour covering all zones\",\"Greenhouses and vanilla farms admission\",\"Livestock interaction (cattle, goats, poultry)\",\"Mini-bar beverage & farm yogurt tasting\",\"Swimming pool access\"]', '[\"High-tech vanilla and strawberry greenhouses\",\"Interacting with dairy cows and poultry birds\",\"Local yogurt and milk tasting session\"]', 'Perfect for families and agricultural enthusiasts. Includes tasting items, but please advise us on any food allergies before arrival.', '1', '2', 'General Farm Tour - Immersive Experience', 'Detailed wider tour including livestock sections (dairy/cattle, goat farm, poultry houses: chicken, turkeys, ducks) plus vanilla and strawberry greenhouses.', 'published');

DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE `inventory_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_product_id_foreign` (`product_id`),
  KEY `inventory_movements_created_by_foreign` (`created_by`),
  KEY `inventory_movements_type_index` (`type`),
  CONSTRAINT `inventory_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES ('1', '1', 'opening', '120', 'manual', NULL, 'Initial stock seeding', NULL, '2026-08-26 21:12:11', '2026-08-26 21:12:11');
INSERT INTO `inventory_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES ('2', '2', 'opening', '45', 'manual', NULL, 'Initial stock seeding', NULL, '2026-08-26 21:12:12', '2026-08-26 21:12:12');
INSERT INTO `inventory_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES ('3', '3', 'opening', '60', 'manual', NULL, 'Initial stock seeding', NULL, '2026-08-26 21:12:12', '2026-08-26 21:12:12');
INSERT INTO `inventory_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES ('4', '4', 'opening', '20', 'manual', NULL, 'Initial stock seeding', NULL, '2026-08-26 21:12:12', '2026-08-26 21:12:12');
INSERT INTO `inventory_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES ('5', '5', 'opening', '200', 'manual', NULL, 'Initial stock seeding', NULL, '2026-08-26 21:12:13', '2026-08-26 21:12:13');
INSERT INTO `inventory_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES ('6', '6', 'opening', '80', 'manual', NULL, 'Initial stock seeding', NULL, '2026-08-26 21:12:13', '2026-08-26 21:12:13');

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2026_08_23_031231_create_permission_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_08_23_031233_create_activity_log_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2026_08_23_031234_add_event_column_to_activity_log_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2026_08_23_031234_create_media_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2026_08_23_031235_add_batch_uuid_column_to_activity_log_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2026_08_23_032253_create_notifications_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2026_08_23_052302_create_settings_and_customers_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2026_08_23_052303_create_accommodation_and_booking_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2026_08_23_052304_create_tours_products_and_inventory_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2026_08_23_052305_create_sales_payments_and_expenses_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2026_08_23_052306_create_audit_logs_and_cms_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2026_08_23_062200_create_production_extensions_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2026_08_25_142200_update_farm_tours_table_for_experiences', '1');

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('1', 'App\\Models\\User', '1');
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('2', 'App\\Models\\User', '2');
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('3', 'App\\Models\\User', '3');
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('4', 'App\\Models\\User', '4');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned DEFAULT NULL,
  `sale_id` bigint(20) unsigned DEFAULT NULL,
  `method` varchar(255) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `paid_at` timestamp NULL DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_booking_id_foreign` (`booking_id`),
  KEY `payments_sale_id_foreign` (`sale_id`),
  KEY `payments_recorded_by_foreign` (`recorded_by`),
  KEY `payments_method_index` (`method`),
  KEY `payments_reference_index` (`reference`),
  KEY `payments_status_index` (`status`),
  KEY `payments_paid_at_index` (`paid_at`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `booking_id`, `sale_id`, `method`, `reference`, `amount`, `status`, `paid_at`, `recorded_by`, `created_at`, `updated_at`) VALUES ('1', '4', NULL, 'cash', NULL, '50000.00', 'completed', '2026-08-28 18:08:58', '1', '2026-08-28 18:08:58', '2026-08-28 18:08:58');
INSERT INTO `payments` (`id`, `booking_id`, `sale_id`, `method`, `reference`, `amount`, `status`, `paid_at`, `recorded_by`, `created_at`, `updated_at`) VALUES ('2', '4', NULL, 'cash', NULL, '50000.00', 'completed', '2026-08-28 18:10:28', '1', '2026-08-28 18:10:28', '2026-08-28 18:10:28');

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('1', 'view_bookings', 'web', '2026-08-26 21:11:48', '2026-08-26 21:11:48');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('2', 'create_bookings', 'web', '2026-08-26 21:11:50', '2026-08-26 21:11:50');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('3', 'edit_bookings', 'web', '2026-08-26 21:11:50', '2026-08-26 21:11:50');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('4', 'cancel_bookings', 'web', '2026-08-26 21:11:50', '2026-08-26 21:11:50');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('5', 'view_revenue', 'web', '2026-08-26 21:11:51', '2026-08-26 21:11:51');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('6', 'view_profit', 'web', '2026-08-26 21:11:51', '2026-08-26 21:11:51');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('7', 'view_expenses', 'web', '2026-08-26 21:11:52', '2026-08-26 21:11:52');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('8', 'create_sales', 'web', '2026-08-26 21:11:52', '2026-08-26 21:11:52');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('9', 'refund_sales', 'web', '2026-08-26 21:11:53', '2026-08-26 21:11:53');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('10', 'adjust_inventory', 'web', '2026-08-26 21:11:53', '2026-08-26 21:11:53');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('11', 'manage_cms', 'web', '2026-08-26 21:11:53', '2026-08-26 21:11:53');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('12', 'manage_media', 'web', '2026-08-26 21:11:54', '2026-08-26 21:11:54');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('13', 'manage_users', 'web', '2026-08-26 21:11:54', '2026-08-26 21:11:54');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('14', 'manage_roles', 'web', '2026-08-26 21:11:54', '2026-08-26 21:11:54');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('15', 'view_audit_logs', 'web', '2026-08-26 21:11:54', '2026-08-26 21:11:54');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('16', 'manage_settings', 'web', '2026-08-26 21:11:55', '2026-08-26 21:11:55');

DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_name_unique` (`name`),
  UNIQUE KEY `product_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('1', 'Farm Produce', 'farm_produce', '2026-08-26 21:12:10', '2026-08-26 21:12:10');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('2', 'Mini Bar', 'mini_bar', '2026-08-26 21:12:11', '2026-08-26 21:12:11');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('3', 'Dairy & Poultry', 'dairy-poultry', '2026-08-27 13:39:41', '2026-08-27 13:39:41');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('4', 'Honey & Coffee', 'honey-coffee', '2026-08-27 13:39:41', '2026-08-27 13:39:41');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('5', 'Greens & Orchard', 'greens-orchard', '2026-08-27 13:39:42', '2026-08-27 13:39:42');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('6', 'Dairy & Eggs', 'dairy-eggs', '2026-08-29 13:46:06', '2026-08-29 13:46:06');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('7', 'Forest Honey & Apiary', 'forest-honey-apiary', '2026-08-29 13:46:06', '2026-08-29 13:46:06');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('8', 'Highland Orchard & Fruits', 'highland-orchard-fruits', '2026-08-29 13:46:07', '2026-08-29 13:46:07');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('9', 'Fresh Vegetables & Chillis', 'fresh-vegetables-chillis', '2026-08-29 13:46:07', '2026-08-29 13:46:07');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_category_id` bigint(20) unsigned NOT NULL,
  `sku` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) NOT NULL DEFAULT 5,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_product_category_id_foreign` (`product_category_id`),
  CONSTRAINT `products_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('1', '6', 'KFV-P-MANGO', 'Farm Fresh Free-Range Eggs', 'Organic free-range eggs with rich golden yolks, harvested every morning across open grassy orchard runs.', 'farm_egg_trays.webp', 'tray (30 eggs)', '15000.00', '1000.00', '120', '10', '0', '2026-08-26 21:12:11', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('2', '6', 'HARV-EGGS-01', 'Kitonga Fresh Whole Milk (Maziwa Halisi)', '100% pure fresh unpasteurized whole farm milk rich in natural cream, bottled daily in 1L, 3L, and 5L containers.', 'IMG_0404.jpg', 'liter (1L / 3L / 5L)', '3000.00', '10000.00', '50', '5', '0', '2026-08-26 21:12:11', '2026-08-30 20:53:26');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('3', '6', 'KFV-P-MILK', 'Kitonga Mtindi Safi (Cultured Sour Milk)', 'Thick, creamy traditional mtindi cultured naturally from pure cow milk for a refreshing authentic sour milk taste.', 'IMG_0394.jpg', '500ml bottle', '2500.00', '1000.00', '60', '10', '0', '2026-08-26 21:12:12', '2026-08-30 20:59:33');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('4', '6', 'HARV-HONEY-01', 'Kitonga Farm Natural & Flavoured Yoghurt', 'Smooth, rich artisanal drinking yoghurt available in luscious Vanilla and ripe Strawberry infusions.', 'IMG_0389.jpg', '500ml bottle', '3000.00', '12000.00', '25', '5', '0', '2026-08-26 21:12:12', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('5', '7', 'KFV-MB-COFFEE', 'Raw Wild Forest Honey (Pure & Unfiltered)', 'Pure, unheated golden amber honey harvested from traditional highland top-bar apiaries in the Komkonga forest.', 'raw_forest_honey.webp', '500ml jar', '20000.00', '800.00', '200', '10', '0', '2026-08-26 21:12:12', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('6', '8', 'KFV-MB-SODA', 'Sweet Kitonga Highland Mangoes', 'Sun-ripened, fragrant organic mangoes picked directly from our mature estate orchard canopies.', 'mango_wallpaper.jpg', 'kg', '5000.00', '1200.00', '80', '10', '0', '2026-08-26 21:12:13', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('7', '8', 'HARV-MILK-01', 'Tree-Ripened Sweet Papaws (Papayas)', 'Luscious, vibrant orange sweet papaws cultivated in fertile highland soil and harvested at peak natural sweetness.', '/images/pawpaw_fresh.webp', 'Piece / kg', '4000.00', '2000.00', '40', '10', '1', '2026-08-29 13:46:07', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('8', '8', 'HARV-MANGO-01', 'Sun-Drenched Estate Pineapples', 'Naturally sweet and juicy tropical pineapples grown with pure highland mountain rainfall and sunshine.', '/images/pineapple_fresh.webp', 'Piece', '4500.00', '2200.00', '50', '15', '1', '2026-08-29 13:46:07', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('9', '5', 'HARV-PAPAW-01', 'Spring-Fed Fresh Farm Vegetables', 'Crisp pesticide-free garden greens, tender spinach, crisp lettuce, sweet basil, and fresh mint gathered at dawn.', 'fresh_vegetables_garden.webp', 'bundle', '6000.00', '2000.00', '60', '10', '0', '2026-08-29 13:46:07', '2026-08-29 21:32:18');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('13', '6', 'KFV-MTINDI-5L', 'Kitonga Cultured Sour Milk / Mtindi (5 Liters)', 'Traditional thick and creamy cultured sour milk made from 100% pure Kitonga pasture milk. Rich in natural probiotics and authentic heritage taste, bottled in a 5L container.', '/images/download_44.webp', '5 Liters', '17000.00', '12000.00', '50', '5', '1', '2026-08-30 20:40:35', '2026-08-30 20:47:51');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('14', '6', 'KFV-MTINDI-3L', 'Kitonga Cultured Sour Milk / Mtindi (3 Liters)', 'Traditional thick and creamy cultured sour milk made from pure farm pasture milk, bottled fresh in a convenient 3L container.', '/images/download_46.webp', '3 Liters', '13000.00', '9000.00', '50', '5', '1', '2026-08-30 20:40:35', '2026-08-30 20:47:51');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('15', '6', 'KFV-FRESH-5L', 'Fresh Whole Farm Milk (5 Liters)', '100% pure fresh unpasteurized whole farm milk rich in natural golden cream, harvested daily from our pasture-fed dairy cattle in a 5L container.', '/images/IMG_0404.jpg', '5 Liters', '13000.00', '8500.00', '100', '5', '1', '2026-08-30 20:40:35', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('16', '6', 'KFV-FRESH-3L', 'Fresh Whole Farm Milk (3 Liters)', 'Pure wholesome unhomogenized fresh farm milk rich in nutrients and natural cream, bottled daily in a 3L container.', '/images/download_45.webp', '3 Liters', '9000.00', '6000.00', '100', '5', '1', '2026-08-30 20:40:35', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('17', '6', 'KFV-YOGURT-1L', 'Kitonga Farm Artisanal Yogurt (1 Liter)', 'Smooth, velvety artisanal drinking yogurt cultured from fresh morning milk, available in luscious Vanilla and ripe Strawberry infusions in a 1L bottle.', '/images/yogurt_1l.webp', '1 Liter', '6000.00', '3800.00', '80', '5', '1', '2026-08-30 20:40:35', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('18', '6', 'KFV-YOGURT-05L', 'Kitonga Farm Artisanal Yogurt (0.5 L / 500ml)', 'Delicious probiotic artisanal drinking yogurt crafted from pure whole milk, bottled in a convenient 500ml on-the-go size.', '/images/IMG_0389.jpg', '0.5 L', '3000.00', '1800.00', '120', '5', '1', '2026-08-30 20:40:35', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('19', '7', 'KFV-ASALI-1KG', 'Raw Wild Forest Honey (1kg Net Weight)', '100% pure, unfiltered and unheated golden raw honey harvested from traditional highland top-bar apiaries deep in the Komkonga forest (1kg net weight).', '/images/raw_forest_honey.webp', '1 kg', '10000.00', '6500.00', '100', '5', '1', '2026-08-30 20:40:36', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('20', '6', 'KFV-MAYAI-KISASA', 'Farm Fresh Organic Eggs (Tray)', 'Farm-fresh organic eggs with vibrant golden yolks, gathered every morning from free-range pasture hens.', '/images/farm_egg_trays.webp', 'Tray (30 Eggs)', '8000.00', '5500.00', '150', '5', '1', '2026-08-30 20:40:36', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('21', '8', 'KFV-MANGO-1KG', 'Sweet Kitonga Highland Mangoes (1kg)', 'Sun-ripened, fragrant organic mangoes picked directly from mature estate orchard trees (1kg).', '/images/mango_wallpaper.jpg', '1 kg', '3000.00', '1500.00', '80', '5', '1', '2026-08-30 20:40:36', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('22', '1', 'KFV-PAPAW-1PC', 'Tree-Ripened Sweet Papaws (Papayas)', 'Luscious, vibrant orange sweet papaws cultivated in fertile highland soil and harvested at peak natural sweetness.', NULL, 'Piece / kg', '4000.00', '2000.00', '40', '5', '0', '2026-08-30 20:40:36', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('23', '1', 'KFV-PINEAPPLE-1PC', 'Sun-Drenched Estate Pineapples', 'Naturally sweet and juicy tropical pineapples grown with pure highland mountain rainfall and sunshine.', NULL, 'Piece', '4500.00', '2200.00', '50', '5', '0', '2026-08-30 20:40:36', '2026-08-30 20:47:52');
INSERT INTO `products` (`id`, `product_category_id`, `sku`, `name`, `description`, `image`, `unit`, `selling_price`, `cost_price`, `stock`, `low_stock_threshold`, `active`, `created_at`, `updated_at`) VALUES ('24', '9', 'KFV-VEG-BUNDLE', 'Spring-Fed Fresh Vegetables & Greens', 'Crisp pesticide-free garden greens, tender spinach, lettuce, sweet basil, and fresh mint gathered at dawn.', '/images/fresh_vegetables_garden.webp', 'Bundle', '6000.00', '2500.00', '60', '5', '1', '2026-08-30 20:40:36', '2026-08-30 20:47:52');

DROP TABLE IF EXISTS `rates`;
CREATE TABLE `rates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accommodation_type_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `day_of_week` int(11) DEFAULT NULL,
  `rate_adjustment_type` varchar(255) NOT NULL DEFAULT 'fixed',
  `value` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rates_accommodation_type_id_start_date_end_date_index` (`accommodation_type_id`,`start_date`,`end_date`),
  CONSTRAINT `rates_accommodation_type_id_foreign` FOREIGN KEY (`accommodation_type_id`) REFERENCES `accommodation_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `refunds`;
CREATE TABLE `refunds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `refund_method` varchar(255) NOT NULL DEFAULT 'cash',
  `gateway_refund_id` varchar(255) DEFAULT NULL,
  `reason` text NOT NULL,
  `processed_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refunds_processed_by_foreign` (`processed_by`),
  KEY `refunds_payment_id_index` (`payment_id`),
  CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),
  CONSTRAINT `refunds_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('1', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('1', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('1', '3');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('1', '4');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('1', '6');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('2', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('2', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('2', '3');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('3', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('3', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('3', '3');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('4', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('4', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('4', '3');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('5', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('5', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('6', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('7', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('7', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('8', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('8', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('8', '4');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('8', '5');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('9', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('9', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('9', '4');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('10', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('10', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('10', '5');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('11', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('11', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('12', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('12', '2');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('13', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('14', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('15', '1');
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('16', '1');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('1', 'owner', 'web', '2026-08-26 21:11:55', '2026-08-26 21:11:55');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('2', 'manager', 'web', '2026-08-26 21:11:56', '2026-08-26 21:11:56');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('3', 'reception', 'web', '2026-08-26 21:11:56', '2026-08-26 21:11:56');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('4', 'cashier', 'web', '2026-08-26 21:11:57', '2026-08-26 21:11:57');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('5', 'farm_staff', 'web', '2026-08-26 21:11:57', '2026-08-26 21:11:57');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('6', 'housekeeping', 'web', '2026-08-26 21:11:57', '2026-08-26 21:11:57');

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `description_snapshot` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cost_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'product',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_reference_unique` (`reference`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_created_by_foreign` (`created_by`),
  KEY `sales_category_index` (`category`),
  KEY `sales_status_index` (`status`),
  CONSTRAINT `sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('4ipJ4sSgWg1EczrPVU54bkVyHldQU3GtuXLhC7zj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiZmNGSW9hc2JnbzE2VWVjQzRwNGZXM2Z2RnhtOEEzOUxFZzV3QUozViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1788435326');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('I6EJULYWRvq0AmfR0TQE8f5rMfHnNvFR7QfzCKxX', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRHhyMGZBZ2Zlc0pvaHhWVmtTQ0xVVjgyeFo0dmltdE9mNUppTWFyZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9jbXMiO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmNtcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', '1788436400');

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('1', 'check_in_time', '14:00', 'Default villa check-in time', '2026-08-26 21:12:02', '2026-08-26 21:12:02');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('2', 'check_out_time', '11:00', 'Default villa check-out time', '2026-08-26 21:12:02', '2026-08-26 21:12:02');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('3', 'tax_rate', '18.00', 'VAT percentage in Tanzania', '2026-08-26 21:12:02', '2026-08-26 21:12:02');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('4', 'currency', 'TZS', 'System currency symbol', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('5', 'cancellation_policy', 'Full refund up to 7 days before check-in. 50% refund 3-7 days. No refund under 3 days.', 'Default booking cancellation policy description', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('6', 'deposit_percentage', '50.00', 'Deposit percentage required to confirm a booking', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('7', 'contact_email', 'kitongafarmvillas@gmail.com', 'Public contact email address', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('8', 'contact_phone', '+255 758 774 695', 'Public contact phone/WhatsApp number', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('9', 'location_coordinates', '-5.15833,39.06222', 'Google Map pin coordinates for the farm villas', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('10', 'breakfast_policy', 'Complimentary premium farm breakfast included for all occupants.', 'Breakfast terms of stay', '2026-08-26 21:12:03', '2026-08-26 21:12:03');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES ('11', 'whatsapp_phone', '+255 758 774 695', NULL, '2026-09-03 14:43:20', '2026-09-03 14:43:20');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'Kitonga Owner', 'owner@kitongafarm.com', '+255712345678', '1', NULL, '$2y$12$ZPKA/JuDw5.TObRejIqyZuWGnFmN4DGHCMgmNhgiL8LwxFHoC810W', NULL, '2026-08-26 21:11:59', '2026-08-26 21:11:59');
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', 'Villa Manager', 'manager@kitongafarm.com', '+255712345679', '1', NULL, '$2y$12$RTNjLuVMYX5CPZXCIcq7n.Zrnxtmqrn3jHGsTzteCjwKLoDJP.7mK', NULL, '2026-08-26 21:12:00', '2026-08-26 21:12:00');
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('3', 'Front Desk Reception', 'reception@kitongafarm.com', '+255712345680', '1', NULL, '$2y$12$VIm3t4Ycz4xX9sKBNtgYm.Hn8FZq05RnBLeUIPImaJDAjJWK0HLqW', NULL, '2026-08-26 21:12:01', '2026-08-26 21:12:01');
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('4', 'Main Cashier', 'cashier@kitongafarm.com', '+255712345681', '1', NULL, '$2y$12$aBPKpOJHo2EWwCUAWwAH9u50dHyv8T8zueCv/FXUB2PggC.aHywVC', NULL, '2026-08-26 21:12:02', '2026-08-26 21:12:02');

SET FOREIGN_KEY_CHECKS=1;

