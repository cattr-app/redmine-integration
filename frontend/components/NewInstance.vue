<template>
    <div>
        <div class="row">
            <div class="col-6">
                <p class="label">{{ $t('instance.url') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="instance.url" :placeholder="$t('instance.url')" required></at-input>
            </div>
        </div>

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
                <at-input v-model="instance.webhook_secret" type="password" :placeholder="$t('instance.webhook_secret')" />
                <p>{{ $t('instance.webhook_secret_hint') }}</p>
            </div>
        </div>

        <at-button size="large" type="primary" @click="authorize">{{ $t('instance.authorize') }}</at-button>

        <Authorize :show="showAuthPopup" @cancel="cancelAuth" @submit="submitAuth" />
    </div>
</template>

<script>
    import Authorize from './Authorize';
    import RedmineInstancesService from '../services/redmineInstancesService';

    export default {
        name: 'NewInstance',
        components: {
            Authorize,
        },
        data() {
            return {
                service: new RedmineInstancesService(),
                instance: {
                    url: '',
                    sync_type: 'cron',
                    webhook_secret: '',
                },
                showAuthPopup: false,
            };
        },
        methods: {
            authorize() {
                this.showAuthPopup = true;
            },
            cancelAuth() {
                this.showAuthPopup = false;
            },
            async submitAuth(authData) {
                const data = {
                    url: this.instance.url,
                    sync_type: this.instance.sync_type,
                    webhook_secret: this.instance.sync_type === 'webhook' ? this.instance.webhook_secret : null,
                    sync_new_projects: false,
                    project_sync_interval: 5,
                    task_sync_interval: 5,
                    priority_sync_interval: 5,
                    status_sync_interval: 5,
                    ...authData,
                };

                let result;
                try {
                    result = await this.service.create(data);
                } catch (error) {
                    this.$NotifyError(error);
                    return;
                }

                this.showAuthPopup = false;
                this.$router.push({
                    name: 'AmazingCat_RedmineIntegration.company.redmine.edit',
                    params: { id: result.id },
                });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .row:not(:last-child) {
        margin-bottom: 1.5rem;
    }

    .label {
        font-weight: bold;
    }
</style>
