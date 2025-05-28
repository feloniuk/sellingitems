<?php declare(strict_types=1);

namespace SellingItems\Core\Content\SellingItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(SellingItemEntity $entity)
 * @method void set(string $key, SellingItemEntity $entity)
 * @method SellingItemEntity[] getIterator()
 * @method SellingItemEntity[] getElements()
 * @method SellingItemEntity|null get(string $key)
 * @method SellingItemEntity|null first()
 * @method SellingItemEntity|null last()
 */
class SellingItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SellingItemEntity::class;
    }
}