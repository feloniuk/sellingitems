const { Component } = Shopware;

Component.extend('selling-items-create', 'selling-items-detail', {
    methods: {
        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.active = true;
            // Initialize with null values for images
            this.item.mainImageId = null;
            this.item.previewImageId = null;
        },

        onClickSave() {
            this.isLoading = true;

            // Validate required fields
            if (!this.item.title || !this.item.categoryId) {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('selling-items.detail.errorTitle'),
                    message: 'Please fill in all required fields'
                });
                return;
            }

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({ name: 'selling.items.detail', params: { id: this.item.id } });
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
        }
    }
});