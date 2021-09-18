/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import Vue from 'vue';
import VeeValidate from 'vee-validate';
import attestationsRouter from './routes/attestationsRouter';
import store from './store';

import {

    ATTESTATIONS_SOCKET_CONNECTION_SUCCESS,
    CREATE_TA_ACCOUNT_SUCCESS, //KEEP
    TA_IS_VERIFIED_SUCCESS, //KEEP
    TA_GET_BALANCE_SUCCESS, //KEEP
    TA_REGISTER_JURISDICTION_SUCCESS, //KEEP
    TA_REGISTER_JURISDICTION_FAIL,
    TA_SET_UNIQUE_ADDRESS_SUCCESS, //KEEP
    TA_SET_KEY_VALUE_PAIR_SUCCESS, //KEEP
    TA_EVENT_SUCCESS,
    TA_SET_JURISDICTION_SUCCESS,
    TA_CREATE_USER_SUCCESS,
    TA_CREATE_RANDOM_USERS_SUCCESS,
    TA_SET_ATTESTATION_SUCCESS,
    TA_SET_ATTESTATION_FAIL,

    TA_SET_GENERIC_ADDRESS_SUCCESS,
    TA_GET_GENERIC_ADDRESS_SUCCESS,

    TA_GET_UNIQUE_ADDRESS_SUCCESS,
    TA_GET_USER_ATTESTATIONS_SUCCESS,
    TA_GET_ATTESTATION_COMPONENTS_SUCCESS,

} from './store/mutation-types';

Vue.use(VeeValidate);

// Instanciate the Vue Contracts App
if (document.getElementById('attestations')) {
    const attestations = new Vue({
        el: '#attestations',
        store,
        router: attestationsRouter,
        created() {

            window.Echo.connector.pusher.connection.bind('connected', function () {
                var socketId = window.Echo.socketId();
                console.log(socketId);
                store.commit(ATTESTATIONS_SOCKET_CONNECTION_SUCCESS, socketId);
            });

            var userId = null;
            if (document.head.querySelector('meta[name="user-id"]')
            && document.head.querySelector('meta[name="user-id"]').content) {
                userId = document.head.querySelector('meta[name="user-id"]').content;
                console.log(userId);
            }

            Echo.private(`user.${userId}`)
                .listen('ContractsInstantiate', (event) => {
                    console.log('ContractsInstantiate attestations private');
                    console.log(event);
                    if (event.data.message == 'create-new-user-account') {
                        console.log('message is create-new-user-account');
                        store.commit(CREATE_TA_ACCOUNT_SUCCESS, event.data.data);
                    }

                    else if (event.data.message == 'ta-register-jurisdiction') {
                        console.log('message is ta-register-jurisdiction');
                        store.commit(TA_REGISTER_JURISDICTION_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-register-jurisdiction-error') {
                        console.log('message is ta-register-jurisdiction-error');
                        store.commit(TA_REGISTER_JURISDICTION_FAIL, event.data.data);
                    }
                    else if (event.data.message == 'ta-is-verified') {
                        console.log('message is ta-is-verified');
                        if(event.data.data) {
                            store.commit(TA_IS_VERIFIED_SUCCESS, 'TA has been verified');
                        }
                        else {
                            store.commit(TA_IS_VERIFIED_SUCCESS, 'TA has NOT been verified');
                        }
                    }
                    else if (event.data.message == 'ta-setup-events') {
                        console.log('message is ta-setup-events');
                        store.commit(TA_SETUP_EVENTS_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-set-jurisdiction') {
                        console.log('message is ta-set-jurisdiction');
                        store.commit(TA_SET_JURISDICTION_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-create-user') {
                        console.log('message is ta-create-user');
                        store.commit(TA_CREATE_USER_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-create-random-users') {
                        console.log('message is ta-create-random-users');
                        store.commit(TA_CREATE_RANDOM_USERS_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-set-attestation') {
                        console.log('message is ta-set-attestation');
                        store.commit(TA_SET_ATTESTATION_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-set-attestation-error') {
                        console.log('message is ta-set-attestation-error');

                        store.commit(TA_SET_ATTESTATION_FAIL, event.data.data);
                    }
                    else if (event.data.message == 'ta-get-balance') {
                        console.log('message is ta-get-balance');
                        var balance = event.data.data;
                        if (parseInt(balance) > 0 ) {
                            balance = balance / 1000000000000000000;
                        }
                        store.commit(TA_GET_BALANCE_SUCCESS, balance + ' SHFT');
                    }
                    else if (event.data.message == 'ta-set-unique-address') {
                        console.log('message is ta-set-unique-address');
                        store.commit(TA_SET_UNIQUE_ADDRESS_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-get-unique-address') {
                        console.log('message is ta-get-unique-address');
                        store.commit(TA_GET_UNIQUE_ADDRESS_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-set-key-value-pair') {
                        console.log('message is ta-set-key-value-pair');
                        store.commit(TA_SET_KEY_VALUE_PAIR_SUCCESS, event.data.data);
                    }

                    else if (event.data.message == 'ta-get-user-attestations') {
                        console.log('message is ta-get-user-attestations');

                        for (var i = 0; i < event.data.data.length; i++) {
                            event.data.data[i]['id'] = i + 1;
                        }
                        store.commit(TA_GET_USER_ATTESTATIONS_SUCCESS, event.data.data);
                    }
                    else if (event.data.message == 'ta-get-attestation-components-in-array') {
                        console.log('message is ta-get-attestation-components-in-array');
                        store.commit(TA_GET_ATTESTATION_COMPONENTS_SUCCESS, event.data.data);
                    }

            });

            Echo.join('contracts')
                .listen('ContractsInstantiate', (event) => {
                    console.log('ContractsInstantiate attestations public');
                    console.log(event);

                    if (event.data.message == 'ta-event') {
                        console.log('message is ta-event');
                        store.commit(TA_EVENT_SUCCESS, event.data.data);
                    }

                });
        },
    });
}
