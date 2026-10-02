import axios from '@/config/app';
import { store } from '@/store';
import Redmine from '../../components/Redmine';
import InstanceList from '../../components/InstanceList';
import NewInstance from '../../components/NewInstance';
import EditInstance from '../../components/EditInstance';
import { hasRole } from '@/utils/user';

export default {
    // Check if this section can be rendered and accessed, this param IS OPTIONAL (true by default)
    // NOTICE: this route will not be added to VueRouter AT ALL if this check fails
    // MUST be a function that returns a boolean
    accessCheck: async () => {
        let user = store.getters['user/user'];
        if (Object.keys(user).length) {
            return hasRole(user, 'admin');
        }

        return hasRole((await axios.get('/auth/me')).data, 'admin');
    },

    scope: 'company',
    order: 30,
    component: Redmine,

    route: {
        // After processing this route will be named as 'settings.exampleSection'
        name: 'AmazingCat_RedmineIntegration.company.redmine',

        // After processing this route can be accessed via URL 'settings/example'
        path: '/company/redmine',

        meta: {
            // After render, this section will be labeled as 'Example Section'
            label: 'settings.redmine.label',

            service: {
                getAll: async () => ({}),
            },
        },

        children: [
            {
                path: '',
                name: 'AmazingCat_RedmineIntegration.company.redmine',
                component: InstanceList,
            },
            {
                path: 'new',
                name: 'AmazingCat_RedmineIntegration.company.redmine.new',
                component: NewInstance,
            },
            {
                path: 'edit/:id',
                name: 'AmazingCat_RedmineIntegration.company.redmine.edit',
                component: EditInstance,
            },
        ],
    },
};
