import './page/selling-items-list';
import './page/selling-items-detail';
import './page/selling-items-create';

const { Module } = Shopware;

Module.register('selling-items', {
    type: 'plugin',
    name: 'selling-items',
    title: 'selling-items.general.mainMenuItemGeneral',
    description: 'selling-items.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    routes: {
        list: {
            component: 'selling-items-list',
            path: 'list'
        },
        detail: {
            component: 'selling-items-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'selling.items.list'
            }
        },
        create: {
            component: 'selling-items-create',
            path: 'create',
            meta: {
                parentPath: 'selling.items.list'
            }
        }
    },

    navigation: [{
        id: 'selling-items',
        label: 'selling-items.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'selling.items.list',
        icon: 'default-shopping-paper-bag-product',
        position: 100,
        parent: 'sw-catalogue'
    }]
});