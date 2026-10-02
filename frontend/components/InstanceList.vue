<template>
    <div>
        <div class="controls">
            <at-button
                type="primary"
                @click.prevent="$router.push({ name: 'AmazingCat_RedmineIntegration.company.redmine.new' })"
            >
                {{ $t('instance.create') }}
            </at-button>
        </div>

        <at-table class="table" size="large" :columns="columns" :data="instances"></at-table>

        <Authorize :show="showAuthPopup" @cancel="cancelAuth" @submit="submitAuth" />
    </div>
</template>

<script>
    import Authorize from './Authorize';
    import RedmineInstancesService from '../services/redmineInstancesService';

    export default {
        name: 'InstanceList',
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
        },
        methods: {
            onEdit(item) {
                this.$router.push({
                    name: 'AmazingCat_RedmineIntegration.company.redmine.edit',
                    params: { id: item.id },
                });
            },
            onAuthorize(item) {
                this.selectedItem = item;
                this.showAuthPopup = true;
            },
            cancelAuth() {
                this.showAuthPopup = false;
            },
            async submitAuth(authData) {
                const data = {
                    ...this.selectedItem,
                    ...authData,
                };

                await this.service.update(data.id, data);

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
                                                    this.onEdit(params.item);
                                                },
                                            },
                                            class: 'action-button',
                                        },
                                        this.$t('instance.edit'),
                                    ),
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
                                        this.$t('instance.reauthorize'),
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
    .controls {
        display: flex;
        flex-flow: row nowrap;
        justify-content: flex-end;
        margin-bottom: 1.5rem;
    }

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

        & ::v-deep .actions-column > :not(:last-child) {
            margin-right: 8px;
        }
    }
</style>
