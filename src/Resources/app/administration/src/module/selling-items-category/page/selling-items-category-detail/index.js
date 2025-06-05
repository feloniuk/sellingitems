import template from './selling-items-category-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('selling-items-category-detail', {
    template,

    inject: [
        'repositoryFactory'
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

    created() {
        this.repository = Shopware.Service('repositoryFactory').create('selling_item_category');
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

            this.repository.get(id, Shopware.Context.api, criteria).then((item) => {
                this.item = item;
                this.isLoading = false;
            }).catch((error) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('selling-items-category.detail.errorTitle'),
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
                        title: this.$tc('selling-items-category.detail.successTitle'),
                        message: this.$tc('selling-items-category.detail.successMessage')
                    });
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('selling-items-category.detail.errorTitle'),
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