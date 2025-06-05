import template from './selling-items-category-list.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('selling-items-category-list', {
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
                property: 'name',
                dataIndex: 'name',
                label: 'selling-items-category.list.columnName',
                routerLink: 'selling.items.category.detail',
                primary: true
            }, {
                property: 'active',
                dataIndex: 'active',
                label: 'selling-items-category.list.columnActive'
            }];
        }
    },

    created() {
        this.repository = Shopware.Service('repositoryFactory').create('selling_item_category');
        this.getList();
    },

    methods: {
        getList() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

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