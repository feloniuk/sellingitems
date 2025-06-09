<?php declare(strict_types=1);

namespace SellingItems\Core\Content\SellingItem;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use SellingItems\Core\Content\SellingItemCategory\SellingItemCategoryEntity;

class SellingItemEntity extends Entity
{
    use EntityIdTrait;

    protected string $categoryId;
    protected ?string $mainImageId = null;
    protected ?string $previewImageId = null;
    protected string $title;
    protected ?string $subtitle = null;
    protected ?string $link = null;
    protected ?float $price = null;
    protected bool $active = true;
    protected ?SellingItemCategoryEntity $category = null;
    protected ?MediaEntity $mainImage = null;
    protected ?MediaEntity $previewImage = null;

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getMainImageId(): ?string
    {
        return $this->mainImageId;
    }

    public function setMainImageId(?string $mainImageId): void
    {
        $this->mainImageId = $mainImageId;
    }

    public function getPreviewImageId(): ?string
    {
        return $this->previewImageId;
    }

    public function setPreviewImageId(?string $previewImageId): void
    {
        $this->previewImageId = $previewImageId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCategory(): ?SellingItemCategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?SellingItemCategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getMainImage(): ?MediaEntity
    {
        return $this->mainImage;
    }

    public function setMainImage(?MediaEntity $mainImage): void
    {
        $this->mainImage = $mainImage;
    }

    public function getPreviewImage(): ?MediaEntity
    {
        return $this->previewImage;
    }

    public function setPreviewImage(?MediaEntity $previewImage): void
    {
        $this->previewImage = $previewImage;
    }
}