CREATE TABLE `users`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(255) NOT NULL,
    `last_time` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `passwords` VARCHAR(255) NOT NULL,
    `is_verified` BOOLEAN NOT NULL,
    `roles_id` BIGINT NOT NULL,
    `profit_rate_id` BIGINT NOT NULL
);
CREATE TABLE `roles`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
);
CREATE TABLE `contact_submission`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(255) NOT NULL,
    `messages` LONGTEXT NOT NULL,
    `timestamp` TIMESTAMP NOT NULL
);
CREATE TABLE `profit_margin`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `rate` BIGINT NOT NULL
);
CREATE TABLE `product_data`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` BIGINT NOT NULL,
    `description` BIGINT NOT NULL,
    `price_per_gram` INT NOT NULL,
    `stock` INT NOT NULL,
    `timestamps` TIMESTAMP NOT NULL
);
ALTER TABLE
    `users` ADD CONSTRAINT `users_profit_rate_id_foreign` FOREIGN KEY(`profit_rate_id`) REFERENCES `profit_margin`(`id`);
ALTER TABLE
    `users` ADD CONSTRAINT `users_roles_id_foreign` FOREIGN KEY(`roles_id`) REFERENCES `roles`(`name`);