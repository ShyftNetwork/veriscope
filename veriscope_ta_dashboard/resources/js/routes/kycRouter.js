import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import store from '../store';
import {
    CURRENT_ROUTE
} from '../store/mutation-types';

// Import essential route based Components
import kycBase from '../templates/kyc/kycBase';
// import kycPersonal from '../templates/kyc/kycPersonal';
// import kycLocation from '../templates/kyc/kycLocation';
// import kycReview from '../templates/kyc/kycReview';
// import kycComplete from '../templates/kyc/kycComplete';

// Define a routes array
const routes = [
    {
        path: '/auth/kyc',
        components: {
            default: kycBase,
        },
        children: [
            
        ],
    },
];


// Define kycRouter configurations
const kycRouter = new VueRouter({
    mode: 'history',
    routes,
    scrollBehavior (to, from, savedPosition) {
        return { x: 0, y: 0 }
    }
});

// Add a route guard to update the current path name
kycRouter.beforeEach(function(to, from, next) {
    store.commit({
        type: CURRENT_ROUTE,
        currentRoute: to.name
    });

    next();
})

// Export kycRouter
export default kycRouter;
