-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 26, 2026 at 03:40 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lynxloop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `item_condition` enum('new','like_new','good','fair','poor') NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_trade_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `brand` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `status` enum('draft','pending','active','reserved','sold','traded','archived','removed') NOT NULL DEFAULT 'pending',
  `view_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `pickup_only` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_bumped_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('message','listing_approved','listing_rejected','listing_sold','listing_traded','order_update','trade_update','report_update','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `agreed_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','accepted','declined','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('not_applicable','pending','paid','failed','refunded') NOT NULL DEFAULT 'not_applicable',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_searches`
--

CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `query` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `last_notified_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `reported_listing_id` int(11) DEFAULT NULL,
  `reason` enum('spam','fraud','inappropriate','prohibited_item','harassment','other') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','under_review','resolved','dismissed') NOT NULL DEFAULT 'open',
  `admin_notes` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `trade_request_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `trade_requests`
--

CREATE TABLE `trade_requests` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `offered_listing_id` int(11) DEFAULT NULL,
  `offered_item_description` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','declined','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `university_role` enum('student','alumni','faculty','admin') NOT NULL DEFAULT 'student',
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) NOT NULL DEFAULT 0,
  `avg_response_minutes` int(11) DEFAULT NULL,
  `account_status` enum('active','suspended','banned','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_parent_id` (`parent_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversations_listing_id` (`listing_id`),
  ADD KEY `idx_conversations_created_by` (`created_by`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conversation_user` (`conversation_id`,`user_id`),
  ADD KEY `idx_conversation_participants_user_id` (`user_id`),
  ADD KEY `idx_conversation_participants_conversation_id` (`conversation_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verification_token` (`verification_token`),
  ADD KEY `fk_email_verifications_user` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_favorites_user_listing` (`user_id`,`listing_id`),
  ADD KEY `idx_favorites_user_id` (`user_id`),
  ADD KEY `idx_favorites_listing_id` (`listing_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_listings_user_id` (`user_id`),
  ADD KEY `idx_listings_category_id` (`category_id`),
  ADD KEY `idx_listings_status` (`status`),
  ADD KEY `idx_listings_price` (`price`),
  ADD KEY `idx_listings_created_at` (`created_at`),
  ADD KEY `idx_listings_trade_allowed` (`is_trade_allowed`),
  ADD KEY `idx_listings_last_bumped_at` (`last_bumped_at`);

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_listing_images_listing_id` (`listing_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_conversation_id` (`conversation_id`),
  ADD KEY `idx_messages_sender_id` (`sender_id`),
  ADD KEY `idx_messages_created_at` (`created_at`),
  ADD KEY `idx_messages_is_read` (`is_read`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_is_read` (`is_read`),
  ADD KEY `idx_notifications_created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_listing_id` (`listing_id`),
  ADD KEY `idx_orders_buyer_id` (`buyer_id`),
  ADD KEY `idx_orders_seller_id` (`seller_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `fk_password_resets_user` (`user_id`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_price_history_listing_id` (`listing_id`),
  ADD KEY `idx_price_history_changed_at` (`changed_at`);

--
-- Indexes for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_saved_searches_user_id` (`user_id`),
  ADD KEY `idx_saved_searches_active` (`is_active`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reports_resolved_by` (`resolved_by`),
  ADD KEY `idx_reports_reporter_id` (`reporter_id`),
  ADD KEY `idx_reports_reported_user_id` (`reported_user_id`),
  ADD KEY `idx_reports_reported_listing_id` (`reported_listing_id`),
  ADD KEY `idx_reports_status` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_listing` (`listing_id`),
  ADD KEY `fk_reviews_order` (`order_id`),
  ADD KEY `fk_reviews_trade_request` (`trade_request_id`),
  ADD KEY `idx_reviews_reviewer_id` (`reviewer_id`),
  ADD KEY `idx_reviews_reviewee_id` (`reviewee_id`);

--
-- Indexes for table `trade_requests`
--
ALTER TABLE `trade_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trade_requests_offered_listing` (`offered_listing_id`),
  ADD KEY `idx_trade_requests_listing_id` (`listing_id`),
  ADD KEY `idx_trade_requests_requester_id` (`requester_id`),
  ADD KEY `idx_trade_requests_owner_id` (`owner_id`),
  ADD KEY `idx_trade_requests_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`university_role`),
  ADD KEY `idx_users_status` (`account_status`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_sessions_user_id` (`user_id`),
  ADD KEY `idx_user_sessions_expires_at` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `price_history`
--
ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_searches`
--
ALTER TABLE `saved_searches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trade_requests`
--
ALTER TABLE `trade_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conversations_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversations_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `fk_conv_participants_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conv_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_email_verifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `fk_listings_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_listings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD CONSTRAINT `fk_listing_images_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orders_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orders_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `price_history`
--
ALTER TABLE `price_history`
  ADD CONSTRAINT `fk_price_history_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD CONSTRAINT `fk_saved_searches_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_searches_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_reported_listing` FOREIGN KEY (`reported_listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reports_reported_user` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reports_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reviews_reviewee` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_trade_request` FOREIGN KEY (`trade_request_id`) REFERENCES `trade_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `trade_requests`
--
ALTER TABLE `trade_requests`
  ADD CONSTRAINT `fk_trade_requests_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_trade_requests_offered_listing` FOREIGN KEY (`offered_listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_trade_requests_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_trade_requests_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- ============================================================
-- SEED DATA (Demo / Development)
-- ============================================================

--
-- Categories
--

INSERT INTO categories (id, name, slug, description, is_active) VALUES
(1, 'Textbooks & Courseware', 'textbooks', 'Academic textbooks, workbooks, and study materials', 1),
(2, 'Electronics & Gear', 'electronics', 'Laptops, monitors, headphones, and tech accessories', 1),
(3, 'Apparel & Accessories', 'apparel', 'Clothing, bags, shoes, and fashion items', 1),
(4, 'Furniture & Decor', 'furniture', 'Desks, chairs, lamps, and dorm essentials', 1),
(5, 'Art & Studio Supplies', 'art-supplies', 'Paints, brushes, canvases, and creative tools', 1),
(6, 'Experiences & Misc', 'experiences', 'Event tickets, tutoring, services, and everything else', 1),
(7, 'Other', 'other', 'Anything that does not fit the categories above', 1);

--
-- Users (password for ALL users is: password)
--

INSERT INTO users (id, first_name, last_name, email, password_hash, university_role, verification_status, account_status, bio, avg_response_minutes) VALUES
(1, 'Jordan', 'Mitchell', 'jordan@campus.edu',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',  'verified', 'active', 'CS major, coffee enthusiast, always looking for good deals on tech.', NULL),
(2, 'Alana',  'Reid',     'alana@campus.edu',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',  'verified', 'active', 'Biochem senior selling off textbooks before graduation.', 15),
(3, 'Mateo',  'Vega',     'mateo@campus.edu',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',  'verified', 'active', 'Audio nerd. Building speakers in my dorm since freshman year.', 45),
(4, 'Kai',    'Okafor',   'kai@campus.edu',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',  'verified', 'active', 'Fashion cohort rep. Upcycling is my thing.', 120),
(5, 'Serena', 'Holt',     'serena@campus.edu',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumni',   'verified', 'active', 'Graduated last spring, clearing out my apartment.', 30),
(6, 'Noah',   'Bennett',  'noah@campus.edu',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',  'verified', 'active', 'Econ tutor. Selling notes and supplies.', NULL),
(7, 'Priya',  'Raman',    'priya@campus.edu',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',  'verified', 'active', 'Robotics club president. Building drones on weekends.', 60),
(8, 'Ivy',    'Chen',     'ivy@campus.edu',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty',  'verified', 'active', 'Fine arts adjunct. Studio B is my second home.', 180);

--
-- Listings
--

INSERT INTO listings (id, user_id, category_id, title, description, item_condition, price, is_trade_allowed, quantity, brand, location, status, pickup_only, last_bumped_at) VALUES
(1,  2, 1, 'Organic Chemistry Essentials Bundle',       'Three gently used textbooks with fresh margin notes and laminated quick-reference sheets. Covers OChem I and II.', 'good',     65.00, 1, 1, 'Pearson',     'Library West',       'active', 1, NOW()),
(2,  5, 1, 'Data Structures in Practice',               'CS205 workbook with flashcards and color-coded tabs. All practice problems completed in pencil, easy to erase.', 'like_new', 40.00, 0, 1, 'O\'Reilly',   'CS Building Lobby',  'active', 1, NULL),
(3,  6, 1, 'Microeconomics Lab Notes',                  'Printed slides annotated with exam tips from the Fall cohort. Includes bonus formula sheet.', 'good',     25.00, 1, 1, NULL,          'Student Union',      'active', 0, NULL),
(4,  8, 1, 'Studio Art Sketch Pads (3-pack)',            'Acid-free pads 18x24 with bonus charcoal set. Opened but never used — wrong size for my class.', 'new',      30.00, 0, 3, 'Strathmore',  'Art Building',       'active', 1, NULL),
(5,  2, 1, 'Intro to Psychology 11th Edition',           'Highlighting in chapters 1-6, rest is clean. Comes with unused online access code.', 'fair',     20.00, 0, 1, 'Cengage',     'Library West',       'active', 1, NULL),
(6,  3, 2, 'Retro Vinyl Desk Speakers',                 'Hand-built ash cabinets with aux and Bluetooth. Warm analog sound, perfect for a studio corner.', 'like_new', 120.00, 1, 1, NULL,          'Dorm Hall C',        'active', 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(7,  7, 2, 'Quadcopter Starter Kit',                    '3D printed frame, spare props, flight controller presets, and a carrying case. Flies great.', 'good',    85.00, 0, 1, 'Custom Build', 'Engineering Lab',    'active', 1, NULL),
(8,  1, 2, 'USB-C Portable Monitor 15.6"',              'Matte panel with magnetic case stand and calibration sheet. Used for one semester.', 'like_new', 95.00, 0, 1, 'ASUS',        'CS Building Lobby',  'active', 1, NULL),
(9,  3, 2, 'Mechanical Keyboard (Cherry MX Brown)',      'Full-size layout with PBT keycaps and detachable cable. Great typing feel, too loud for my roommate.', 'good', 55.00, 1, 1, 'Keychron',    'Dorm Hall C',        'active', 1, NULL),
(10, 5, 2, 'Noise-Canceling Headphones',                'Over-ear, 30hr battery, comes with hard case and airplane adapter. Small scratch on left cup.', 'fair', 70.00, 0, 1, 'Sony',        'Student Union',      'active', 1, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(11, 4, 3, 'Reclaimed Canvas Jacket',                   'Indigo-dyed jacket with reinforced seams and hidden phone pocket. Size M.', 'like_new', 80.00, 1, 1, NULL,          'Fashion Lab',        'active', 1, NULL),
(12, 4, 3, 'Hand Loomed Scarf Set',                     'Pair of plant-dyed scarves with gradient weaves. One warm-toned, one cool.', 'new', 35.00, 1, 2, NULL,          'Fashion Lab',        'active', 0, NULL),
(13, 4, 3, 'Vintage Denim Overalls',                    'Authentic 90s find, perfectly broken in. Fits like a modern size S/M.', 'fair', 45.00, 1, 1, 'Levi\'s',     'Fashion Lab',        'active', 1, NULL),
(14, 5, 4, 'Zero-Gravity Desk Chair',                   'Breathable mesh with adjustable lumbar kit and rolling mat included. Disassembles for transport.', 'good', 150.00, 0, 1, 'Autonomous',  'Off-campus (rides available)', 'active', 1, NULL),
(15, 6, 4, 'Mini Hydroponic Grow Bar',                  'Self-watering LED planter for herbs. Great for dorm kitchens or windowsills.', 'like_new', 40.00, 1, 1, 'AeroGarden', 'Student Union',      'active', 1, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(16, 1, 4, 'IKEA Kallax Shelf Unit (2x4)',              'White, no damage. Comes with 4 fabric drawer inserts. You pick up from my apartment.', 'good', 50.00, 0, 1, 'IKEA',        'Off-campus',         'active', 1, NULL),
(17, 8, 5, 'Oil Paint Set — 24 Colors',                 'Professional grade tubes, most are 80%+ full. Selling because I switched to acrylics.', 'good', 55.00, 0, 1, 'Winsor & Newton', 'Art Building',   'active', 1, NULL),
(18, 8, 5, 'Tabletop Easel with Storage Drawer',        'Beechwood easel, folds flat. Drawer holds brushes and small tubes.', 'like_new', 35.00, 1, 1, NULL,              'Art Building',   'active', 1, NULL),
(19, 6, 6, 'Calc II Tutoring — 5 Sessions',             'One-on-one tutoring, 1 hour each. I got an A+ and love explaining limits and integrals.', 'new', 75.00, 1, 1, NULL, 'Library or Zoom', 'active', 0, NULL),
(20, 7, 6, 'Concert Tickets x2 — Indie Night',          'Campus amphitheater, Saturday the 26th. Can\'t make it, selling at face value.', 'new', 30.00, 0, 2, NULL, 'Amphitheater Box Office', 'active', 0, NULL);

INSERT INTO listings (id, user_id, category_id, title, description, item_condition, price, is_trade_allowed, quantity, brand, location, status, pickup_only) VALUES
(21, 2, 1, 'Biology 101 Textbook',     'Sold this last week!', 'good', 35.00, 0, 1, NULL, 'Library', 'sold', 1),
(22, 3, 2, 'Old Bluetooth Speaker',    'Pending review by admin.', 'fair', 15.00, 0, 1, 'JBL', NULL, 'pending', 1);

--
-- Listing Images
--

INSERT INTO listing_images (listing_id, image_path, is_primary, sort_order) VALUES
(1,  'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=900&q=80', 1, 0),
(1,  'https://images.unsplash.com/photo-1457694587812-e8bf29a43845?auto=format&fit=crop&w=900&q=80', 0, 1),
(2,  'https://images.unsplash.com/photo-1457694587812-e8bf29a43845?auto=format&fit=crop&w=900&q=80', 1, 0),
(3,  'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=900&q=80', 1, 0),
(4,  'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80', 1, 0),
(5,  'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=900&q=80', 1, 0),
(6,  'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80', 1, 0),
(6,  'https://images.unsplash.com/photo-1505740106531-4243f3831c55?auto=format&fit=crop&w=900&q=80', 0, 1),
(6,  'https://images.unsplash.com/photo-1484704849700-09d5f5c0e9ce?auto=format&fit=crop&w=900&q=80', 0, 2),
(7,  'https://images.unsplash.com/photo-1505740106531-4243f3831c55?auto=format&fit=crop&w=900&q=80', 1, 0),
(8,  'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&q=80', 1, 0),
(9,  'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80', 1, 0),
(10, 'https://images.unsplash.com/photo-1484704849700-09d5f5c0e9ce?auto=format&fit=crop&w=900&q=80', 1, 0),
(11, 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80', 1, 0),
(12, 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80', 1, 0),
(13, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80', 1, 0),
(14, 'https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=900&q=80', 1, 0),
(15, 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80', 1, 0),
(16, 'https://images.unsplash.com/photo-1487017159836-4e23ece2e4cf?auto=format&fit=crop&w=900&q=80', 1, 0),
(17, 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80', 1, 0),
(18, 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80', 1, 0),
(19, 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=900&q=80', 1, 0),
(20, 'https://images.unsplash.com/photo-1505740106531-4243f3831c55?auto=format&fit=crop&w=900&q=80', 1, 0);

--
-- Price History (triggers price drop badges)
--

INSERT INTO price_history (listing_id, old_price, new_price, changed_at) VALUES
(1,  85.00,  65.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5,  30.00,  20.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(10, 95.00,  70.00, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(14, 200.00, 150.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(9,  65.00,  55.00, DATE_SUB(NOW(), INTERVAL 12 HOUR));

--
-- Conversations & Messages
--

INSERT INTO conversations (id, listing_id, created_by, created_at) VALUES
(1, 1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 6, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 11, 5, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 20, 6, DATE_SUB(NOW(), INTERVAL 6 HOUR));

INSERT INTO conversation_participants (conversation_id, user_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 3),
(3, 5), (3, 4),
(4, 6), (4, 7);

INSERT INTO messages (conversation_id, sender_id, message_body, is_read, created_at) VALUES
(1, 1, 'Hey, is this still available?', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 2, 'Yes! It is. Are you free to pick up tomorrow?', 1, DATE_SUB(NOW(), INTERVAL 47 HOUR)),
(1, 1, 'Works for me. Library West lobby at 3pm?', 1, DATE_SUB(NOW(), INTERVAL 46 HOUR)),
(1, 2, 'Perfect, see you then!', 1, DATE_SUB(NOW(), INTERVAL 45 HOUR)),
(2, 1, 'Hey, is this still available?', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 3, 'Still got them! Want to come hear them first?', 0, DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(3, 5, 'Love the jacket! Would you trade for a pair of vintage boots?', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 4, 'What size are the boots? Send me a pic if you can.', 1, DATE_SUB(NOW(), INTERVAL 71 HOUR)),
(3, 5, 'Size 8, barely worn. I will bring them by the fashion lab.', 0, DATE_SUB(NOW(), INTERVAL 70 HOUR)),
(4, 6, 'Hey, is this still available?', 0, DATE_SUB(NOW(), INTERVAL 6 HOUR));

--
-- Notifications
--

INSERT INTO notifications (user_id, type, title, body, reference_id, is_read, created_at) VALUES
(2, 'message', 'New message about your listing', 'Hey, is this still available?', 1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 'message', 'New message about your listing', 'Hey, is this still available?', 6, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 'message', 'New message about your listing', 'Love the jacket! Would you trade for a pair of vintage boots?', 11, 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 'message', 'New message about your listing', 'Hey, is this still available?', 20, 0, DATE_SUB(NOW(), INTERVAL 6 HOUR));

--
-- Favorites
--

INSERT INTO favorites (user_id, listing_id, created_at) VALUES
(1, 6,  DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 11, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 14, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 17, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(5, 9,  DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 15, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(6, 1,  DATE_SUB(NOW(), INTERVAL 2 DAY));

--
-- Saved Searches
--

INSERT INTO saved_searches (user_id, query, category_id, is_active, created_at) VALUES
(1, 'textbook',    1, 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'keyboard',    2, 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'desk chair',  4, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 'headphones',  2, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 'art supplies', 5, 1, DATE_SUB(NOW(), INTERVAL 4 DAY));

--
-- Reviews
--

INSERT INTO reviews (reviewer_id, reviewee_id, listing_id, rating, comment, created_at) VALUES
(1, 2, 21, 5, 'Super fast and friendly. Book was exactly as described.', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(5, 4, 11, 4, 'Great jacket, small delay on meetup but worked out.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 2, 21, 5, 'Alana is awesome. Easy trade.', DATE_SUB(NOW(), INTERVAL 6 DAY));

UPDATE users SET average_rating = 5.00, total_reviews = 2 WHERE id = 2;
UPDATE users SET average_rating = 4.00, total_reviews = 1 WHERE id = 4;

-- ============================================================
-- Test Accounts Quick Reference
-- ============================================================
-- All passwords: password
--
-- jordan@campus.edu  (id=1) — Buyer, has favorites and saved searches
-- alana@campus.edu   (id=2) — Seller, 2 reviews, 15min response time
-- mateo@campus.edu   (id=3) — Seller, 45min response time
-- kai@campus.edu     (id=4) — Seller, 1 review, 2hr response time
-- serena@campus.edu  (id=5) — Alumni seller
-- noah@campus.edu    (id=6) — Student seller
-- priya@campus.edu   (id=7) — Has unread message, 1hr response time
-- ivy@campus.edu     (id=8) — Faculty seller, 3hr response time

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
