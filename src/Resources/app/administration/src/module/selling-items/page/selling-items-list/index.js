import template from './selling-items-list.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('selling-items-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            items: null,
            isLoading: true,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            total: 0,
            repository: null
        };
    },

    computed: {
        columns() {
            return [{
                property: 'title',
                dataIndex: 'title',
                label: 'selling-items.list.columnTitle',
                routerLink: 'selling.items.detail',
                primary: true
            }, {
                property: 'category.name',
                dataIndex: 'category.name',
                label: 'selling-items.list.columnCategory'
            }, {
                property: 'price',
                dataIndex: 'price',
                label: 'selling-items.list.columnPrice'
            }, {
                property: 'active',
                dataIndex: 'active',
                label: 'selling-items.list.columnActive'
            }];
        }
    },

    created() {
        this.repository = Shopware.Service('repositoryFactory').create('selling_item');
        this.getList();
    },

    methods: {
        getList() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('category');

            this.isLoading = true;

            this.repository
                .search(criteria, Shopware.Context.api)
                .then((result) => {
                    this.items = result;
                    this.total = result.total;
                    this.isLoading = false;
                })
                .catch((error) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: 'Error',
                        message: error.message
                    });
                });
        },

        updateTotal({ total }) {
            this.total = total;
        }
    }
});