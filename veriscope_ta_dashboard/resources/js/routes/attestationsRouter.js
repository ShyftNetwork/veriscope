import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import store from '../store';
import {
    CURRENT_ROUTE
} from '../store/mutation-types';

// Import essential route based Components
import attestationsBase from '../templates/attestations/attestationsBase';
import attestationsManageOrganization from '../templates/attestations/attestationsManageOrganization';
import attestationsManageUsers from '../templates/attestations/attestationsManageUsers';
import attestationsAdmin from '../templates/attestations/attestationsAdmin';

// Define a routes array
const routes = [
    {
        path: '/auth/attestations',
        components: {
            default: attestationsBase,
        },
        children: [
            {
                path: 'manage-organization',
                name: 'manage-organization',
                component: attestationsManageOrganization
            },
            {
                path: 'manage-users',
                name: 'manage-users',
                component: attestationsManageUsers
            },
            {
                path: 'admin',
                name: 'admin',
                component: attestationsAdmin
            }
        ],
    },
];


// Define contractsRouter configurations
const attestationsRouter = new VueRouter({
    mode: 'history',
    routes,
    scrollBehavior (to, from, savedPosition) {
        return { x: 0, y: 0 }
    }
});

// Add a route guard to update the current path name
attestationsRouter.beforeEach(function(to, from, next) {
    store.commit({
        type: CURRENT_ROUTE,
        currentRoute: to.name
    });

    next();
})

// Export attestationsRouter
export default attestationsRouter;
