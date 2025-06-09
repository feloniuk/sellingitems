import template from './selling-items-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('selling-items-detail', {
    template,

    inject: [
        'repositoryFactory',
        'mediaService'
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
            repository: null,
            mediaRepository: null,
            uploadTag: 'selling-items-upload-tag'
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
        },

        mediaModalMainImage() {
            return this.item?.mainImageId || null;
        },

        mediaModalPreviewImage() {
            return this.item?.previewImageId || null;
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('selling_item');
        this.mediaRepository = this.repositoryFactory.create('media');
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
                    this.createNotificationSuccess({
                        title: this.$tc('selling-items.detail.successTitle'),
                        message: this.$tc('selling-items.detail.successMessage')
                    });
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
        },

        onSetMainMediaItem(mediaEntity) {
            this.item.mainImageId = mediaEntity.id;
        },

        onSetPreviewMediaItem(mediaEntity) {
            this.item.previewImageId = mediaEntity.id;
        },

        onRemoveMainMediaItem() {
            this.item.mainImageId = null;
        },

        onRemovePreviewMediaItem() {
            this.item.previewImageId = null;
        },

        onMediaSelectionChange(mediaEntityList, fieldName) {
            if (mediaEntityList.length > 0) {
                if (fieldName === 'mainImage') {
                    this.item.mainImageId = mediaEntityList[0].id;
                } else if (fieldName === 'previewImage') {
                    this.item.previewImageId = mediaEntityList[0].id;
                }
            }
        }
    }
});