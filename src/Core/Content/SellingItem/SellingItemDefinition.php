<?php declare(strict_types=1);

namespace SellingItems\Core\Content\SellingItem;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use SellingItems\Core\Content\SellingItemCategory\SellingItemCategoryDefinition;

class SellingItemDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'selling_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return SellingItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return SellingItemEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('category_id', 'categoryId', SellingItemCategoryDefinition::class))->addFlags(new Required()),
            new FkField('main_image_id', 'mainImageId', MediaDefinition::class),
            new FkField('preview_image_id', 'previewImageId', MediaDefinition::class),
            (new StringField('title', 'title'))->addFlags(new Required()),
            new StringField('subtitle', 'subtitle'),
            new StringField('link', 'link'),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            new CreatedAtField(),
            new UpdatedAtField(),
            new ManyToOneAssociationField('category', 'category_id', SellingItemCategoryDefinition::class, 'id'),
            new ManyToOneAssociationField('mainImage', 'main_image_id', MediaDefinition::class, 'id'),
            new ManyToOneAssociationField('previewImage', 'preview_image_id', MediaDefinition::class, 'id'),
        ]);
    }
}