<?php declare(strict_types=1);

namespace SellingItems\Core\Content\SellingItemCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(SellingItemCategoryEntity $entity)
 * @method void set(string $key, SellingItemCategoryEntity $entity)
 * @method SellingItemCategoryEntity[] getIterator()
 * @method SellingItemCategoryEntity[] getElements()
 * @method SellingItemCategoryEntity|null get(string $key)
 * @method SellingItemCategoryEntity|null first()
 * @method SellingItemCategoryEntity|null last()
 */
class SellingItemCategoryCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SellingItemCategoryEntity::class;
    }
}