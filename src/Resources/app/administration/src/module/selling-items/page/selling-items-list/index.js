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
            return this.repositoryFactory.create('selling_item');
        },

        columns() {
            return [{
                property: 'title',
                dataIndex: 'title',
                label: 'selling-items.list.columnTitle',
                routerLink: 'selling.items.detail',
                primary: true
            }, {
                property: 'subtitle',
                dataIndex: 'subtitle',
                label: 'selling-items.list.columnSubtitle'
            }, {
                property: 'category.name',
                dataIndex: 'category.name',
                label: 'selling-items.list.columnCategory'
            }, {
                property: 'active',
                dataIndex: 'active',
                label: 'selling-items.list.columnActive'
            }];
        }
    },

    methods: {
        getList() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('category');
            criteria.addAssociation('mainImage');
            criteria.addAssociation('previewImage');

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