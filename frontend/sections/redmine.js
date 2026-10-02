import UserInstanceList from '../components/UserInstanceList';

export default {
    // Check if this section can be rendered and accessed, this param IS OPTIONAL (true by default)
    // NOTICE: this route will not be added to VueRouter AT ALL if this check fails
    // MUST be a function that returns a boolean
    accessCheck: async () => true,

    order: 20,

    component: UserInstanceList,

    route: {
        // After processing this route will be named as 'settings.exampleSection'
        name: 'AmazingCat_RedmineIntegration.settings.redmine',

        // After processing this route can be accessed via URL 'settings/example'
        path: '/settings/redmine',

        meta: {
            // After render, this section will be labeled as 'Example Section'
            label: 'settings.redmine.label',

            // Renderable fields array
            fields: [],

            service: {
                getAll: async () => ({}),
            },
        },
    },
};
