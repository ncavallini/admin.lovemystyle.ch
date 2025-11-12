-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: lovemyhadmin.mysql.db
-- Creato il: Nov 12, 2025 alle 22:17
-- Versione del server: 8.0.43-34
-- Versione PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lovemyhadmin`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `from_datetime` datetime NOT NULL,
  `to_datetime` datetime NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `brands`
--

CREATE TABLE `brands` (
  `brand_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `supplier_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cash_content`
--

CREATE TABLE `cash_content` (
  `id` int NOT NULL DEFAULT '1',
  `content` int NOT NULL,
  `last_updated_at` datetime NOT NULL,
  `last_updated_by` varchar(255) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Struttura della tabella `clockings`
--

CREATE TABLE `clockings` (
  `clocking_id` char(36) NOT NULL,
  `username` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  `type` enum('in','out') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `closings`
--

CREATE TABLE `closings` (
  `date` date NOT NULL,
  `content` int NOT NULL,
  `income` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `countries`
--

CREATE TABLE `countries` (
  `country_code` char(2) NOT NULL,
  `en` varchar(255) NOT NULL,
  `it` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `customers`
--

CREATE TABLE `customers` (
  `customer_id` char(36) NOT NULL,
  `customer_number` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postcode` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_edit_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `discount_codes`
--

CREATE TABLE `discount_codes` (
  `code` varchar(8) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `discount` int NOT NULL,
  `discount_type` enum('CHF','%') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `elisa_sanna_nov_25`
--

CREATE TABLE `elisa_sanna_nov_25` (
  `id` int NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `currency` char(3) NOT NULL,
  `date` date NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `gift_cards`
--

CREATE TABLE `gift_cards` (
  `card_id` int UNSIGNED NOT NULL,
  `amount` int UNSIGNED NOT NULL,
  `balance` int UNSIGNED NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `sale_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` date NOT NULL,
  `starts_at` date NOT NULL,
  `expires_at` date NOT NULL
) ;

-- --------------------------------------------------------

--
-- Struttura della tabella `password_reset_links`
--

CREATE TABLE `password_reset_links` (
  `username` varchar(255) NOT NULL,
  `token` char(36) NOT NULL,
  `expiration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `username` varchar(255) NOT NULL,
  `token` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `products`
--

CREATE TABLE `products` (
  `product_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `full_price` int NOT NULL,
  `discounted_price` int DEFAULT NULL,
  `price` int GENERATED ALWAYS AS ((case when ((`discounted_price` is not null) and (`discounted_price` < `full_price`)) then `discounted_price` else `full_price` end)) VIRTUAL NOT NULL,
  `vat_id` int NOT NULL DEFAULT '1',
  `last_edit_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_infinite` tinyint(1) NOT NULL DEFAULT '0',
  `is_discounted` tinyint(1) GENERATED ALWAYS AS ((`discounted_price` is not null)) VIRTUAL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `product_variants`
--

CREATE TABLE `product_variants` (
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` int NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sales`
--

CREATE TABLE `sales` (
  `sale_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `paid_cash` int NOT NULL DEFAULT '0',
  `paid_pos` int NOT NULL DEFAULT '0',
  `payment_method` enum('cash','pos','mixed') GENERATED ALWAYS AS ((case when ((`paid_cash` <> 0) and (`paid_pos` = 0)) then _utf8mb4'cash' when ((`paid_cash` = 0) and (`paid_pos` <> 0)) then _utf8mb4'pos' when ((`paid_cash` = 0) and (`paid_pos` = 0)) then NULL else _utf8mb4'mixed' end)) VIRTUAL,
  `username` varchar(255) NOT NULL,
  `discount` double NOT NULL DEFAULT '0',
  `discount_type` enum('CHF','%') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'CHF',
  `status` enum('open','completed','partially_canceled','totally_canceled','negative') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sales_items`
--

CREATE TABLE `sales_items` (
  `sale_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` int NOT NULL DEFAULT '0',
  `total_price` int GENERATED ALWAYS AS ((`price` * `quantity`)) VIRTUAL NOT NULL,
  `is_discounted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` char(2) NOT NULL,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `vat_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `used_discount_codes`
--

CREATE TABLE `used_discount_codes` (
  `id` int NOT NULL,
  `code` varchar(8) NOT NULL,
  `customer_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `password_hash` binary(60) NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` char(2) NOT NULL,
  `iban` varchar(34) NOT NULL,
  `role` enum('STANDARD','OWNER','ADMIN') NOT NULL DEFAULT 'STANDARD',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `needs_password_change` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vats`
--

CREATE TABLE `vats` (
  `vat_id` int NOT NULL,
  `percentage` double NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL DEFAULT '9999-12-31'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indici per le tabelle `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `FK_brands_supplier_id` (`supplier_id`);

--
-- Indici per le tabelle `cash_content`
--
ALTER TABLE `cash_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_cash_content_username` (`last_updated_by`);

--
-- Indici per le tabelle `clockings`
--
ALTER TABLE `clockings`
  ADD PRIMARY KEY (`clocking_id`);

--
-- Indici per le tabelle `closings`
--
ALTER TABLE `closings`
  ADD PRIMARY KEY (`date`);

--
-- Indici per le tabelle `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`country_code`),
  ADD UNIQUE KEY `en` (`en`),
  ADD UNIQUE KEY `it` (`it`);

--
-- Indici per le tabelle `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_number` (`customer_number`),
  ADD UNIQUE KEY `customer_number_2` (`customer_number`);

--
-- Indici per le tabelle `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`code`),
  ADD KEY `FK_discount_codes_customer_id` (`customer_id`);

--
-- Indici per le tabelle `elisa_sanna_nov_25`
--
ALTER TABLE `elisa_sanna_nov_25`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`currency`);

--
-- Indici per le tabelle `gift_cards`
--
ALTER TABLE `gift_cards`
  ADD PRIMARY KEY (`card_id`),
  ADD KEY `FK_gift_cards_customer_id` (`customer_id`),
  ADD KEY `FK_gift_cards_sale_id` (`sale_id`);

--
-- Indici per le tabelle `password_reset_links`
--
ALTER TABLE `password_reset_links`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indici per le tabelle `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indici per le tabelle `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_id` (`product_id`),
  ADD KEY `FK_products_vat_id` (`vat_id`),
  ADD KEY `FK_products_brand_id` (`brand_id`);

--
-- Indici per le tabelle `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indici per le tabelle `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `FK_sales_customer_id` (`customer_id`),
  ADD KEY `FK_sales_username` (`username`);

--
-- Indici per le tabelle `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`sale_id`,`product_id`,`variant_id`) USING BTREE,
  ADD KEY `FK_sales_items_sku` (`product_id`,`variant_id`);

--
-- Indici per le tabelle `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indici per le tabelle `used_discount_codes`
--
ALTER TABLE `used_discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_used_discount_codes_discount_codes` (`code`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Indici per le tabelle `vats`
--
ALTER TABLE `vats`
  ADD PRIMARY KEY (`vat_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `elisa_sanna_nov_25`
--
ALTER TABLE `elisa_sanna_nov_25`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `used_discount_codes`
--
ALTER TABLE `used_discount_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `vats`
--
ALTER TABLE `vats`
  MODIFY `vat_id` int NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `FK_brands_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `cash_content`
--
ALTER TABLE `cash_content`
  ADD CONSTRAINT `FK_cash_content_username` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`username`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD CONSTRAINT `FK_discount_codes_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `gift_cards`
--
ALTER TABLE `gift_cards`
  ADD CONSTRAINT `FK_gift_cards_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_gift_cards_sale_id` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `password_reset_links`
--
ALTER TABLE `password_reset_links`
  ADD CONSTRAINT `FK_password_reset_links_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `FK_password_reset_tokens_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `FK_products_brand_id` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_products_vat_id` FOREIGN KEY (`vat_id`) REFERENCES `vats` (`vat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `FK_product_variants_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `FK_sales_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_sales_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `FK_sales_items_sales_id` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_sales_items_sku` FOREIGN KEY (`product_id`,`variant_id`) REFERENCES `product_variants` (`product_id`, `variant_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `used_discount_codes`
--
ALTER TABLE `used_discount_codes`
  ADD CONSTRAINT `FK_used_discount_codes_discount_codes` FOREIGN KEY (`code`) REFERENCES `discount_codes` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
