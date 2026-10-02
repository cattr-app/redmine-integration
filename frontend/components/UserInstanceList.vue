<template>
    <div>
        <at-table class="table" size="large" :columns="columns" :data="instances"></at-table>

        <Authorize :show="showAuthPopup" @cancel="cancelAuth" @submit="submitAuth" />
    </div>
</template>

<script>
    import Authorize from './Authorize';
    import RedmineInstancesService from '../services/redmineInstancesService';

    export default {
        name: 'UserInstanceList',
        components: {
            Authorize,
        },
        data() {
            return {
                service: new RedmineInstancesService(),
                instances: [],
                selectedItem: null,
                showAuthPopup: false,
            };
        },
        async mounted() {
            this.instances = await this.service.list();

            this.instances.forEach(async instance => {
                const result = await this.service.checkAuth(instance.id);
                this.$set(instance, 'authorized', !!result.status);
            });
        },
        methods: {
            onAuthorize(item) {
                this.selectedItem = item;
                this.showAuthPopup = true;
            },
            cancelAuth() {
                this.showAuthPopup = false;
            },
            async submitAuth(authData) {
                await this.service.createUser(this.selectedItem.id, {
                    redmine_instance_id: this.selectedItem.id,
                    ...authData,
                });

                this.instances.forEach(async instance => {
                    const result = await this.service.checkAuth(instance.id);
                    this.$set(instance, 'authorized', !!result.status);
                });

                this.showAuthPopup = false;
            },
        },
        computed: {
            columns() {
                return [
                    {
                        key: 'url',
                        title: 'URL',
                    },
                    {
                        key: 'actions',
                        title: this.$t('instance.actions'),
                        render: (h, params) => {
                            const { authorized } = params.item;

                            return h(
                                'div',
                                {
                                    class: 'actions-column',
                                },
                                [
                                    h(
                                        'AtButton',
                                        {
                                            props: {
                                                type: 'primary', // AT-ui button display type
                                                icon: 'icon-edit', // Prepend icon to button
                                            },
                                            on: {
                                                click: () => {
                                                    this.onAuthorize(params.item);
                                                },
                                            },
                                            class: 'action-button',
                                        },
                                        authorized ? this.$t('instance.reauthorize') : this.$t('instance.authorize'),
                                    ),
                                ],
                            );
                        },
                    },
                ];
            },
        },
    };
</script>

<style lang="scss" scoped>
    .table {
        position: relative;

        &::v-deep tr {
            th {
                background: #fff;
                color: #c4c4cf;
            }
        }

        &::v-deep .at-table {
            &__content {
                border: 0;
            }

            &__tbody {
                tr:last-child .at-table__cell {
                    border-bottom: 0;
                }
            }

            &__cell {
                max-width: 250px;
                overflow: hidden;

                padding-top: $spacing-05;
                padding-bottom: $spacing-05;
                border-bottom: 2px solid $blue-3;

                &:last-child {
                    max-width: unset;
                }
            }
        }
    }
</style>
