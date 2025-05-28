<?php declare(strict_types=1);

namespace SellingItems\Core\Content\SellingItemCategory;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use SellingItems\Core\Content\SellingItem\SellingItemCollection;

class SellingItemCategoryEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;
    protected bool $active;
    protected ?SellingItemCollection $sellingItems;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getSellingItems(): ?SellingItemCollection
    {
        return $this->sellingItems;
    }

    public function setSellingItems(SellingItemCollection $sellingItems): void
    {
        $this->sellingItems = $sellingItems;
    }
}