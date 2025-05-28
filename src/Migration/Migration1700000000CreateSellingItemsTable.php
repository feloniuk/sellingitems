<?php declare(strict_types=1);

namespace SellingItems\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700000000CreateSellingItemsTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700000000;
    }

    public function update(Connection $connection): void
    {
        // Создание таблицы категорий
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `selling_item_category` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        // Создание таблицы selling items
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `selling_item` (
                `id` BINARY(16) NOT NULL,
                `category_id` BINARY(16) NOT NULL,
                `main_image_id` BINARY(16) NULL,
                `preview_image_id` BINARY(16) NULL,
                `title` VARCHAR(255) NOT NULL,
                `subtitle` VARCHAR(255) NULL,
                `link` VARCHAR(500) NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.selling_item.category_id` (`category_id`),
                KEY `fk.selling_item.main_image_id` (`main_image_id`),
                KEY `fk.selling_item.preview_image_id` (`preview_image_id`),
                CONSTRAINT `fk.selling_item.category_id` FOREIGN KEY (`category_id`) 
                    REFERENCES `selling_item_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.selling_item.main_image_id` FOREIGN KEY (`main_image_id`) 
                    REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.selling_item.preview_image_id` FOREIGN KEY (`preview_image_id`) 
                    REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}