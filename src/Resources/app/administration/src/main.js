import './module/selling-items';
import './module/selling-items-category';

// Регистрируем entity definitions для работы с API
Shopware.EntityDefinition.add('selling_item', {
    entity: 'selling_item',
    properties: {
        id: {
            type: 'uuid',
            flags: { primary_key: true, required: true }
        },
        categoryId: {
            type: 'uuid',
            flags: { required: true }
        },
        mainImageId: {
            type: 'uuid'
        },
        previewImageId: {
            type: 'uuid'
        },
        title: {
            type: 'string',
            flags: { required: true }
        },
        subtitle: {
            type: 'string'
        },
        link: {
            type: 'string'
        },
        active: {
            type: 'boolean'
        }
    }
});

Shopware.EntityDefinition.add('selling_item_category', {
    entity: 'selling_item_category',
    properties: {
        id: {
            type: 'uuid',
            flags: { primary_key: true, required: true }
        },
        name: {
            type: 'string',
            flags: { required: true }
        },
        active: {
            type: 'boolean'
        }
    }
});