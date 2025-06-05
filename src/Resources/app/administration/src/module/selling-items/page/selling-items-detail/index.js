import template from './selling-items-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('selling-items-detail', {
    template,

    inject: [
        'repositoryFactory',
        'context'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing')
    ],

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            isSaveSuccessful: false,
            repository: null
        };
    },

    computed: {
        categoryRepository() {
            return Shopware.Service('repositoryFactory').create('selling_item_category');
        }
    },

    created() {
        this.repository = Shopware.Service('repositoryFactory').create('selling_item');
        this.getItem();
    },

    methods: {
        getItem() {
            const id = this.$route.params.id;
            if (!id) {
                this.item = this.repository.create(Shopware.Context.api);
                return;
            }

            this.isLoading = true;
            const criteria = new Criteria();
            criteria.addAssociation('category');
            criteria.addAssociation('mainImage');
            criteria.addAssociation('previewImage');

            this.repository.get(id, Shopware.Context.api, criteria).then((item) => {
                this.item = item;
                this.isLoading = false;
            }).catch((error) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('selling-items.detail.errorTitle'),
                    message: error.message
                });
            });
        },

        onSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                    this.isSaveSuccessful = true;
                    this.createNotificationSuccess({
                        title: this.$tc('selling-items.detail.successTitle'),
                        message: this.$tc('selling-items.detail.successMessage')
                    });
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('selling-items.detail.errorTitle'),
                        message: exception.message
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
            this.isSaveSuccessful = false;
        }
    }
});