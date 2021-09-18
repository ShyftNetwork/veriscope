/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import Vue from 'vue';
import VeeValidate from 'vee-validate';
import kycRouter from './routes/kycRouter';
import store from './store';

Vue.use(VeeValidate);  

// Instanciate the Vue KYC App
if (document.getElementById('kyc')) {
    const kyc = new Vue({
        el: '#kyc',
        store,
        router: kycRouter
    });
}