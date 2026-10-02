<template>
    <div v-show="show" class="auth-popup">
        <div class="row">
            <div class="col-6">
                <p class="label">{{ $t('authorize.auth_type') }}</p>
            </div>

            <div class="col-18">
                <at-select v-model="method">
                    <at-option value="basic">{{ $t('authorize.login_and_password') }}</at-option>
                    <at-option value="api_key">{{ $t('authorize.api_key') }}</at-option>
                </at-select>
            </div>
        </div>

        <div v-show="method == 'basic'" class="row">
            <div class="col-6">
                <p class="label">{{ $t('authorize.login') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="login" :placeholder="$t('authorize.login')"></at-input>
            </div>
        </div>

        <div v-show="method == 'basic'" class="row">
            <div class="col-6">
                <p class="label">{{ $t('authorize.password') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="password" type="password" :placeholder="$t('authorize.password')"></at-input>
            </div>
        </div>

        <div v-show="method == 'api_key'" class="row">
            <div class="col-6">
                <p class="label">{{ $t('authorize.api_key') }}</p>
            </div>

            <div class="col-18">
                <at-input v-model="api_key" :placeholder="$t('authorize.api_key')"></at-input>
            </div>
        </div>

        <at-button size="large" type="primary" @click="submit">{{ $t('authorize.ok') }}</at-button>
        <at-button size="large" @click="cancel">{{ $t('authorize.cancel') }}</at-button>
    </div>
</template>

<script>
    export default {
        name: 'Authorize',
        props: {
            show: {
                type: Boolean,
            },
        },
        data() {
            return {
                method: 'basic',
                login: '',
                password: '',
                api_key: '',
            };
        },
        methods: {
            cancel() {
                this.$emit('cancel');
            },
            async submit() {
                const data = {
                    auth_method: this.method,
                };

                if (this.method === 'basic') {
                    data['auth_login'] = this.login;
                    data['auth_password'] = this.password;
                } else if (this.method === 'api_key') {
                    data['auth_api_key'] = this.api_key;
                }

                this.$emit('submit', data);
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

    .auth-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        min-width: 600px;
        background: #ffffff;
        border: 1px solid #eeeef5;
        box-sizing: border-box;
        box-shadow: 0px 0px 100px rgba(63, 51, 86, 0.05);
        border-radius: 20px;
        padding: 1.5rem;
        z-index: 1;
    }

    .at-btn:not(:last-child) {
        margin-right: 1.5rem;
    }
</style>
