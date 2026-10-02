<template>
    <div class="project-select">
        <at-select
            :key="selectedProjectId"
            ref="select"
            v-model="selectedProjectId"
            class="select"
            clearable
            filterable
            @on-change="handleProjectChange"
        >
            <at-option value="new">{{ $t('project_select.create_new') }}</at-option>
            <at-option v-for="project in projects" :key="project.id" :value="+project.id">
                {{ project.name }}
            </at-option>
        </at-select>

        <at-input
            v-if="selectedProjectId === 'new'"
            v-model="newProjectName"
            class="input"
            :placeholder="$t('project_select.project_name')"
            @change="handleNameChange"
        ></at-input>
    </div>
</template>

<script>
    export default {
        name: 'ProjectSelect',
        props: {
            redmineProject: {
                required: true,
                type: Object,
            },
            projects: {
                required: true,
                type: Array,
            },
        },
        data() {
            return {
                selectedProjectId: 0,
                newProjectName: '',
            };
        },
        methods: {
            handleProjectChange(projectId) {
                this.$emit('change', { id: this.selectedProjectId, name: this.newProjectName });
            },
            handleNameChange(name) {
                this.$emit('change', { id: this.selectedProjectId, name: this.newProjectName });
            },
        },
        mounted() {
            this.selectedProjectId = this.redmineProject.project_id;
            this.newProjectName = this.redmineProject.name;
        },
        watch: {
            projects(projects) {
                const selectedProject = projects.find(p => +p.id === +this.selectedProjectId);
                if (selectedProject) {
                    this.$refs.select.query = selectedProject.name;
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .project-select {
        display: flex;
        flex-flow: row nowrap;
    }

    .select,
    .input {
        flex: 1;
    }

    .select:not(:last-child) {
        margin-right: 1em;
    }
</style>
