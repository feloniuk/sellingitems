const { Component } = Shopware;

Component.extend('selling-items-create', 'selling-items-detail', {
    methods: {
        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.active = true;
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({ name: 'selling.items.index.detail', params: { id: this.item.id } });
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