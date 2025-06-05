import './page/selling-items-category-list';
import './page/selling-items-category-detail';
import './page/selling-items-category-create';

const { Module } = Shopware;

Module.register('selling-items-category', {
    type: 'plugin',
    name: 'selling-items-category',
    title: 'selling-items-category.general.mainMenuItemGeneral',
    description: 'selling-items-category.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-folder-full',

    routes: {
        list: {
            component: 'selling-items-category-list',
            path: 'list'
        },
        detail: {
            component: 'selling-items-category-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'selling.items.category.list'
            }
        },
        create: {
            component: 'selling-items-category-create',
            path: 'create',
            meta: {
                parentPath: 'selling.items.category.list'
            }
        }
    },

    navigation: [{
        label: 'selling-items-category.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'selling.items.category.list',
        icon: 'default-folder-full',
        position: 110,
        parent: 'sw-catalogue'
    }]
});