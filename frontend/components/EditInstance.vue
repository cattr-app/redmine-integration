<template>
    <div>
        <at-collapse
            class="collapse"
            simple
            :class="{ 'collapse-open': prioritiesDropdown }"
            @on-change="prioritiesDropdown = $event.length > 0"
        >
            <at-collapse-item :title="$t('instance.priorities')">
                <template slot="title">
                    {{ $t('instance.priorities') }}

                    <div class="collapse-icon">
                        <i class="icon icon-chevron-down"></i>
                    </div>
                </template>

                <RedminePriorities
                    :key="internalPriorities + ''"
                    v-model="redminePriorities"
                    :internalPriorities="internalPriorities"
                />
            </at-collapse-item>
        </at-collapse>

        <at-collapse
            class="collapse"
            simple
            :class="{ 'collapse-open': statusesDropdown }"
            @on-change="statusesDropdown = $event.length > 0"
        >
            <at-collapse-item :title="$t('instance.statuses')">
                <template slot="title">
                    {{ $t('instance.statuses') }}

                    <div class="collapse-icon">
                        <i class="icon icon-chevron-down"></i>
                    </div>
                </template>

                <RedmineStatuses
                    :key="internalStatuses + ''"
                    v-model="redmineStatuses"
                    :internalStatuses="internalStatuses"
                />
            </at-collapse-item>
        </at-collapse>

        <at-collapse
            class="collapse"
            simple
            :class="{ 'collapse-open': projectsDropdown }"
            @on-change="projectsDropdown = $event.length > 0"
        >
            <at-collapse-item :title="$t('instance.projects')">
                <template slot="title">
                    {{ $t('instance.projects') }}

                    <div class="collapse-icon">
                        <i class="icon icon-chevron-down"></i>
                    </div>
                </template>

                <div class="filters">
                    <at-input
                        v-model="projectsFilter"
                        type="text"
                        :placeholder="$t('filter.enter-single', [$t('filter.fields.project_name')])"
                        class="filter"
                    >
                        <template slot="prepend">
                            <i class="icon icon-search"></i>
                        </template>
                    </at-input>
                </div>

                <at-table class="table" :columns="columns" :data="filteredRedmineProjecst" pagination></at-table>
            </at-collapse-item>
        </at-collapse>

        <div class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.sync_type') }}</p>
            </div>

            <div class="col-18">
                <at-select v-model="instance.sync_type">
                    <at-option value="cron">{{ $t('instance.cron') }}</at-option>
                    <at-option value="webhook">{{ $t('instance.webhook') }}</at-option>
                </at-select>
            </div>
        </div>

        <div v-show="instance.sync_type === 'webhook'" class="row">
            <div class="col-6"><p class="label">{{ $t('instance.webhook_secret') }}</p></div>
            <div class="col-18">
                <at-input v-model="webhookSecret" type="password" :placeholder="$t('instance.webhook_secret_placeholder')" />
                <p>{{ instance.webhook_secret_configured ? $t('instance.webhook_secret_saved') : $t('instance.webhook_secret_hint') }}</p>
                <label>
                    <input v-model="clearWebhookSecret" type="checkbox" />
                    {{ $t('instance.webhook_secret_clear') }}
                </label>
            </div>
        </div>

        <div v-show="instance.sync_type === 'webhook'" class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.sync_new_projects') }}</p>
            </div>

            <div class="col-18">
                <at-select v-model="instance.sync_new_projects">
                    <at-option :value="0">{{ $t('instance.no') }}</at-option>
                    <at-option :value="1">{{ $t('instance.yes') }}</at-option>
                </at-select>
            </div>
        </div>

        <div v-show="instance.sync_type === 'cron'" class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.project_sync_interval') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="instance.project_sync_interval" />
            </div>
        </div>

        <div v-show="instance.sync_type === 'cron'" class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.task_sync_interval') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="instance.task_sync_interval" />
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.priority_sync_interval') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="instance.priority_sync_interval" />
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.status_sync_interval') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="instance.status_sync_interval" />
            </div>
        </div>

        <at-button size="large" type="primary" @click="submit">{{ $t('instance.submit') }}</at-button>
    </div>
</template>

<script>
    import RedminePriorities from './RedminePriorities';
    import RedmineStatuses from './RedmineStatuses';
    import RedmineInstancesService from '../services/redmineInstancesService';
    import PriorityService from '@/services/resource/priority.service.js';
    import ProjectService from '@/services/resource/project.service.js';
    import StatusService from '../services/status.service.js';

    export default {
        name: 'EditInstance',
        components: {
            RedminePriorities,
            RedmineStatuses,
        },
        data() {
            return {
                prioritiesDropdown: false,
                statusesDropdown: false,
                projectsDropdown: false,
                service: new RedmineInstancesService(),
                priorityService: new PriorityService(),
                statusService: new StatusService(),
                projectService: new ProjectService(),
                instance: {},
                webhookSecret: '',
                clearWebhookSecret: false,
                redminePriorities: [],
                internalPriorities: [],
                redmineStatuses: [],
                internalStatuses: [],
                redmineProjects: [],
                internalProjects: [],
                projectRelations: {},
                columns: [
                    {
                        key: 'name',
                        title: this.$t('instance.from_redmine'),
                    },
                    {
                        key: 'actions',
                        title: this.$t('instance.to_cattr'),
                        render: (h, params) => {
                            return h(
                                'div',
                                {
                                    class: 'actions-column',
                                },
                                [
                                    h('RedmineProjectSelect', {
                                        key: params.item.id,
                                        props: {
                                            redmineProject: params.item,
                                            projects: this.internalProjects,
                                        },
                                        on: {
                                            change: data => {
                                                this.projectRelations[params.item.id] = { ...data };
                                            },
                                        },
                                    }),
                                ],
                            );
                        },
                    },
                ],
                projectsFilter: '',
            };
        },
        computed: {
            filteredRedmineProjecst() {
                const value = this.projectsFilter.toLocaleLowerCase();

                return this.redmineProjects.filter(project => project.name.toLocaleLowerCase().indexOf(value) !== -1);
            },
        },
        async created() {
            const id = this.$route.params.id;
            try {
                this.instance = await this.service.show(id);
                this.redminePriorities = await this.service.getPriorities(id);
                this.internalPriorities = await this.priorityService.getAll();
                this.redmineStatuses = await this.service.getStatuses(id);
                this.internalStatuses = await this.statusService.getAll();
                this.redmineProjects = await this.service.getPojects(id);
                this.internalProjects = await this.projectService.getAll();
            } catch (error) {
                this.$NotifyError(error);
            }
        },
        methods: {
            async submit() {
                const id = this.$route.params.id;

                const instanceData = { ...this.instance };
                delete instanceData.webhook_secret_configured;
                if (this.webhookSecret) instanceData.webhook_secret = this.webhookSecret;
                if (this.clearWebhookSecret) instanceData.clear_webhook_secret = true;
                try {
                    await this.service.update(id, instanceData);
                    await this.service.bulkUpdatePriority(id, {
                        priorities: this.redminePriorities.map(priority => ({
                            id: priority.id,
                            priority_id: priority.priority_id,
                        })),
                    });
                } catch (error) {
                    this.$NotifyError(error);
                }

                await this.service.bulkUpdateStatus(id, {
                    statuses: this.redmineStatuses.map(status => ({
                        id: status.id,
                        status_id: status.status_id,
                    })),
                });

                for (let redmineProjectId of Object.keys(this.projectRelations)) {
                    const relation = this.projectRelations[redmineProjectId];
                    if (relation.id === 'new') {
                        const url = this.instance.url.replace(/^https?:\/\//i, '');

                        const data = {
                            name: `${url}/${relation.name}`,
                            description: relation.name,
                        };

                        try {
                            const response = await this.projectService.save(data, true);

                            await this.service.updateProject(id, redmineProjectId, {
                                project_id: response.data.res.id,
                            });
                        } catch (error) {
                            this.$NotifyError(error);
                        }
                    } else {
                        try {
                            await this.service.updateProject(id, redmineProjectId, {
                                project_id: relation.id,
                            });
                        } catch (error) {
                            this.$NotifyError(error);
                        }
                    }
                }

                this.$router.push({ name: 'AmazingCat_RedmineIntegration.company.redmine' });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .row:not(:last-child),
    .collapse {
        margin-bottom: 1.5rem;
    }

    .collapse {
        overflow: unset;

        &::v-deep {
            .at-collapse__header {
                color: #3f536e;
                font-size: 1rem;
                font-weight: bold;
                padding: 1em 0;
            }

            .at-collapse__item--active {
                background: none;

                .at-collapse__content {
                    background: none;
                }
            }

            .at-collapse__content {
                overflow: unset;
            }

            .at-table table {
                overflow: unset;
            }

            .at-table__content {
                border: 0;
            }

            .at-table__thead > tr > th {
                background: none;
            }

            .at-table__cell {
                padding: 1em;
            }
        }
    }

    .collapse-icon {
        display: inline-block;
        transition: all 0.3s;
    }

    .collapse-open .collapse-icon {
        transform: rotate(180deg);
    }

    .label {
        font-weight: bold;
    }

    .at-btn:not(:last-child) {
        margin-right: 1.5rem;
    }

    .filters {
        display: flex;
    }
</style>
