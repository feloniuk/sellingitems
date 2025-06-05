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
            sortDirection: 'DESC'
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('selling_item_category');
        },

        columns() {
            return [{
                property: 'name',
                dataIndex: 'name',
                label: 'selling-items-category.list.columnName',
                routerLink: 'selling.items.category.index.detail',
                primary: true
            }, {
                property: 'active',
                dataIndex: 'active',
                label: 'selling-items-category.list.columnActive'
            }];
        }
    },

    methods: {
        getList() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            this.isLoading = true;

            this.repository
                .search(criteria)
                .then((result) => {
                    this.items = result;
                    this.total = result.total;
                    this.isLoading = false;
                });
        },

        updateTotal({ total }) {
            this.total = total;
        }
    }
});