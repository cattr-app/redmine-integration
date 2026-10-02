<template>
    <ul class="redmine-statuses">
        <li v-for="(redmineStatus, index) of redmineStatuses" :key="redmineStatus.id" class="redmine-statuses__item">
            <label class="redmine-statuses__field">
                <span class="redmine-statuses__label">
                    {{ redmineStatus.name }}
                </span>

                <at-select
                    class="redmine-statuses__select"
                    :value="+redmineStatus.status_id"
                    @on-change="onChange(index, $event)"
                >
                    <at-option
                        v-for="internalStatus of internalStatuses"
                        :key="internalStatus.id"
                        :value="+internalStatus.id"
                    >
                        {{ internalStatus.name }}
                    </at-option>
                </at-select>
            </label>
        </li>
    </ul>
</template>

<script>
    export default {
        name: 'RedmineStatuses',

        model: {
            prop: 'redmineStatuses',
            event: 'change',
        },

        props: {
            redmineStatuses: {
                type: Array,
                required: true,
            },

            internalStatuses: {
                type: Array,
                required: true,
            },

            changeHandler: {
                type: Function,
                default: () => value => {},
            },
        },

        methods: {
            onChange(index, value) {
                const statuses = [...this.redmineStatuses];
                statuses[index].status_id = value;
                this.changeHandler(statuses);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .redmine-statuses {
        &__field {
            display: flex;
            align-items: baseline;

            margin-bottom: 1em;
        }

        &__label {
            width: 150px;
        }
    }
</style>
