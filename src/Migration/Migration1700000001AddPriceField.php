<?php declare(strict_types=1);

namespace SellingItems\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700000001AddPriceField extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700000001;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `selling_item`
            ADD COLUMN `price` DECIMAL(10,2) NULL DEFAULT NULL AFTER `link`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}