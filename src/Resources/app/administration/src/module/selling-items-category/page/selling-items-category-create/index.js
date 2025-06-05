const { Component } = Shopware;

Component.extend('selling-items-category-create', 'selling-items-category-detail', {
    methods: {
        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.active = true;
        },

        onSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({ 
                        name: 'selling.items.category.detail', 
                        params: { id: this.item.id } 
                    });
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
        }
    }
});