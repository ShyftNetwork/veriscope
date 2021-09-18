import Vue from 'vue';
import Vuex from 'vuex';
import kyc from './modules/kyc/';
import attestations from './modules/attestations/';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
    	kyc,
    	attestations
        
    },
});
