import template from './selling-items-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('selling-items-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            repository: null
        };
    },

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('selling_item_category');
        },

        categoryCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('active', true));
            return criteria;
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('selling_item');
        this.getItem();
    },

    methods: {
        getItem() {
            if (!this.$route.params.id) {
                return;
            }

            const criteria = new Criteria();
            criteria.addAssociation('category');
            criteria.addAssociation('mainImage');
            criteria.addAssociation('previewImage');

            this.repository
                .get(this.$route.params.id, Shopware.Context.api, criteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('selling-items.detail.errorTitle'),
                        message: exception
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});