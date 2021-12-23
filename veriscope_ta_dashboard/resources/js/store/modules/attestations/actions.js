import {
    //KEEP
    CREATE_TA_ACCOUNT,
    CREATE_TA_ACCOUNT_SUCCESS,
    CREATE_TA_ACCOUNT_FAIL,
    //KEEP
    TA_SAVE_IVMS,
    TA_SAVE_IVMS_SUCCESS,
    TA_SAVE_IVMS_FAIL,
    //KEEP
    TA_IS_VERIFIED,
    TA_IS_VERIFIED_SUCCESS,
    TA_IS_VERIFIED_FAIL,
    //KEEP
    TA_GET_BALANCE,
    TA_GET_BALANCE_SUCCESS,
    TA_GET_BALANCE_FAIL,
    //KEEP
    TA_REGISTER_JURISDICTION,
    TA_REGISTER_JURISDICTION_SUCCESS,
    TA_REGISTER_JURISDICTION_FAIL,
    //KEEP
    TA_SET_UNIQUE_ADDRESS,
    TA_SET_UNIQUE_ADDRESS_SUCCESS,
    TA_SET_UNIQUE_ADDRESS_FAIL,
    //KEEP
    TA_SET_KEY_VALUE_PAIR,
    TA_SET_KEY_VALUE_PAIR_SUCCESS,
    TA_SET_KEY_VALUE_PAIR_FAIL,

    TA_SET_JURISDICTION,
    TA_SET_JURISDICTION_SUCCESS,
    TA_SET_JURISDICTION_FAIL,
    TA_CREATE_USER,
    TA_CREATE_USER_SUCCESS,
    TA_CREATE_USER_FAIL,

    TA_CREATE_RANDOM_USERS,
    TA_CREATE_RANDOM_USERS_SUCCESS,
    TA_CREATE_RANDOM_USERS_FAIL,

    TA_SET_ATTESTATION,
    TA_SET_ATTESTATION_SUCCESS,
    TA_SET_ATTESTATION_FAIL,




    TA_GET_UNIQUE_ADDRESS,
    TA_GET_UNIQUE_ADDRESS_SUCCESS,
    TA_GET_UNIQUE_ADDRESS_FAIL,


    TA_GET_USER_ATTESTATIONS,
    TA_GET_USER_ATTESTATIONS_SUCCESS,
    TA_GET_USER_ATTESTATIONS_FAIL,
    TA_GET_ATTESTATION_COMPONENTS,
    TA_GET_ATTESTATION_COMPONENTS_SUCCESS,
    TA_GET_ATTESTATION_COMPONENTS_FAIL,
    TA_LOAD_COUNTRIES,
    TA_LOAD_COUNTRIES_SUCCESS,
    TA_LOAD_COUNTRIES_FAIL,
    TA_LOAD_WALLET_TYPES,
    TA_LOAD_WALLET_TYPES_SUCCESS,
    TA_LOAD_WALLET_TYPES_FAIL,
    TA_LOAD_WALLET_ADDRESSES,
    TA_LOAD_WALLET_ADDRESSES_SUCCESS,
    TA_LOAD_WALLET_ADDRESSES_FAIL,
    TA_GET_TAS,
    TA_GET_TAS_SUCCESS,
    TA_GET_TAS_FAIL,

    TA_GET_USERS,
    TA_GET_USERS_SUCCESS,
    TA_GET_USERS_FAIL,

    TA_ASSIGN_CRYPTO_ADDRESS,
    TA_ASSIGN_CRYPTO_ADDRESS_SUCCESS,
    TA_ASSIGN_CRYPTO_ADDRESS_FAIL,

    TA_GET_USER_WALLET_ADDRESSES,
    TA_GET_USER_WALLET_ADDRESSES_SUCCESS,
    TA_GET_USER_WALLET_ADDRESSES_FAIL,

    TA_GET_ALL_ATTESTATIONS,
    TA_GET_ALL_ATTESTATIONS_SUCCESS,
    TA_GET_ALL_ATTESTATIONS_FAIL,

    TA_GET_DISCOVERY_LAYER_KEYS,
    TA_GET_DISCOVERY_LAYER_KEYS_SUCCESS,
    TA_GET_DISCOVERY_LAYER_KEYS_FAIL,

} from '../../mutation-types';

export const actions = {

    /**
     * Setup Accounts - Testnet seeded accounts
     * @param {object} context - Context provided by vuex
     * @param {object} payload - Payload to submit
     * @returns {promise}
     */
     [CREATE_TA_ACCOUNT]({ commit, getters, state }, payload) {
        console.log('actions CREATE_TA_ACCOUNT');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {"ta_prefname": state.form.ta_prefname,
                "ta_password": "Password1*"};
                // "ta_password": state.form.ta_password};
        console.log(p);
        return axios.post(`contracts/trust-anchor/${getters.UID}/create-ta-account`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(CREATE_TA_ACCOUNT_FAIL);
            });
    },


    [TA_REGISTER_JURISDICTION]({ commit, getters, state }) {
        console.log('actions TA_REGISTER_JURISDICTION');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {"account_address": state.form.attestation_ta_account.account_address,
                "jurisdiction": state.form.attestation_jurisdiction.id};
        console.log(p);
        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-register-jurisdiction`, p)
            .then(response => {

                state.taRegisterJurisdictionData = "Please Wait....";

                return response;
            })
            .catch(error => {
                commit(TA_REGISTER_JURISDICTION_FAIL);
            });
    },

    [TA_SAVE_IVMS]({ commit, dispatch, getters, state }) {
        console.log('actions TA_SAVE_IVMS');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {
                "legal_person_name": state.form.ivms_legal_person_name,
                "legal_person_name_identifier_type": state.form.ivms_legal_person_name_identifier_type,
                "address_type": state.form.ivms_address_type,
                "street_name": state.form.ivms_street_name,
                "building_number": state.form.ivms_building_number,
                "building_name": state.form.ivms_building_name,
                "postcode": state.form.ivms_postcode,
                "town_name": state.form.ivms_town_name,
                "country_sub_division": state.form.ivms_country_sub_division,
                "country": state.form.ivms_country,
                };
        console.log(p);
        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-save-ivms`, p)
            .then(response => {
                console.log(response);
                dispatch('createTaAccount');
                return response;
            })
            .catch(error => {
                commit(TA_SAVE_IVMS_FAIL);
            });
    },

    [TA_IS_VERIFIED]({ commit, getters, state }) {
        console.log('actions TA_IS_VERIFIED');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {"account": state.form.attestation_ta_account.account_address};
        console.log(p);
        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-is-verified`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(TA_IS_VERIFIED_FAIL);
            });
    },

    [TA_SET_JURISDICTION]({ commit, getters, state }, payload) {
        console.log('actions TA_SET_JURISDICTION');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {"ta_jurisdiction": state.form.ta_jurisdiction,
                "account": state.ta_temp_account};
        console.log(p);
        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-set-jurisdiction`, p)
            .then(response => {
                return response;
            })
            .catch(error => {

                commit(TA_SET_JURISDICTION_FAIL);
            });
    },

    [TA_CREATE_USER]({ commit, getters, state }, payload) {
        console.log('actions TA_CREATE_USER');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {"prefname": state.form.user_prefname,
                "password": 'Password1*',
                "dob": state.form.dob,
                "gender": state.form.gender.label,
                "jurisdiction": state.form.attestation_jurisdiction.id,
                "trust_anchor_account":state.form.attestation_ta_account};
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-create-user`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(TA_CREATE_USER_FAIL);
            });
    },

    [TA_CREATE_RANDOM_USERS]({ commit, getters, state }, payload) {
        console.log('actions TA_CREATE_RANDOM_USERS');
        console.log('state');
        console.log(state);
        var p = {"trust_anchor_account":state.form.attestation_ta_account};
        console.log('payload');
        console.log(p);
        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-create-random-users`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(TA_CREATE_RANDOM_USERS_FAIL);
            });
    },

    [TA_SET_ATTESTATION]({ commit, dispatch, getters, state }, payload) {
        console.log('actions TA_SET_ATTESTATION');
        console.log('state');
        console.log(state);
        console.log('payload');
        console.log(payload);

        var p = {};
        if (payload["type"] == 'WALLET') {

            state.taSetWalletAttestationHashData = '';
            state.taSetWalletAttestationData = 1;
            state.taSetWalletAttestationError = null;

            state.showTaSetWalletAttestationResult = 'block';

            p = {"attestation_type": 'WALLET',
                "user_address": state.form.attestation_user.account_address,
                "jurisdiction": state.form.attestation_jurisdiction.id,
                "effective_time": '',
                "expiry_time": '',
                "public_data": payload["type"],
                "documents_matrix_encrypted": state.form.attestation_document_matrix,
                "availability_address_encrypted": state.form.user_crypto_address_type.wallet_type,
                "ta_account": state.form.attestation_ta_account,
            };
        }
        else {
            p = {"attestation_user": state.form.attestation_user_account,
                "jurisdiction": state.form.attestation_jurisdiction.id,
                "effective_time": state.form.attestation_effective_date,
                "expiry_time": state.form.attestation_expiry_date,
                "public_data": state.form.attestation_public_data.type,
                "documents_matrix_encrypted": state.form.attestation_document_matrix,
                "availability_address_encrypted": state.form.attestation_kyc_data.date_name,
                "ta_account": state.form.attestation_ta_account,
            };
        }
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-set-attestation`, p)
            .then(response => {
                dispatch('taGetUsers');
                return response;
            })
            .catch(error => {
                commit(TA_SET_ATTESTATION_FAIL);
            });
    },

    [TA_GET_BALANCE]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_BALANCE');
        console.log('state');
        console.log(state);
        console.log('payload');
        var p = {"account": state.form.attestation_ta_account.account_address};
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-get-balance`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(TA_GET_BALANCE_FAIL);
            });
    },

    [TA_SET_UNIQUE_ADDRESS]({ commit, getters, state }) {

        var p = {"account": state.form.attestation_ta_account.account_address};
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-set-unique-address/`, p)
            .then(({ data }) => {
                console.log('TA_SET_UNIQUE_ADDRESS response');
                return data;
            })
            .catch((error) => {
                commit(TA_SET_UNIQUE_ADDRESS_FAIL);
            });
    },
    [TA_GET_UNIQUE_ADDRESS]({ commit, getters, state }) {

        var p = {"from_account": state.form.attestation_ta_account.account_address,
                "to_account": state.form.ta_unique_account};
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-get-unique-address/`, p)
            .then(({ data }) => {
                console.log('TA_GET_UNIQUE_ADDRESS response');
                return data;
            })
            .catch((error) => {
                commit(TA_GET_UNIQUE_ADDRESS_FAIL);
            });
    },

    [TA_GET_USER_ATTESTATIONS]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_USER_ATTESTATIONS');
        console.log('state');
        console.log(state);
        console.log('payload');
        console.log(payload);
        var p = {};
        if (payload.account_address) {
            p = {"account": payload.account_address};
        }
        else {
            p = {"account": state.form.ta_user_address};
        }

        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-get-user-attestations`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(TA_GET_USER_ATTESTATIONS_FAIL);
            });
    },

    [TA_GET_ATTESTATION_COMPONENTS]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_ATTESTATION_COMPONENTS');
        console.log('state');
        console.log(state);
        console.log('payload');
        console.log(payload);
        var p = {"account": state.form.attestation_user.account_address,
                "index":payload - 1};
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-get-attestation-components-in-array`, p)
            .then(response => {
                return response;
            })
            .catch(error => {
                commit(TA_GET_ATTESTATION_COMPONENTS_FAIL);
            });
    },

    [TA_GET_TAS]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_TAS');

        return axios.get(`contracts/trust-anchor/${getters.UID}/ta-get-trust-anchors`)
            .then(({ data }) => {
                commit(TA_GET_TAS_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(TA_GET_TAS_FAIL);
            });
    },

    [TA_GET_USERS]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_TA_USERS');
        console.log(state.form.attestation_ta_account);

        var p = {"trust_anchor_id": state.form.attestation_ta_account.id};
        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-get-trust-anchor-users`, p)
            .then(({ data }) => {
                console.log('actions TA_GET_USERS');
                console.log(data);
                commit(TA_GET_USERS_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(TA_GET_USERS_FAIL);
            });
    },

    [TA_LOAD_COUNTRIES]({ commit }) {
        return axios.get('countries')
            .then(({ data }) => {
                commit(TA_LOAD_COUNTRIES_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(TA_LOAD_COUNTRIES_FAIL);
            });
    },

    [TA_LOAD_WALLET_TYPES]({ commit }) {
        return axios.get('wallet-types')
            .then(({ data }) => {
                commit(TA_LOAD_WALLET_TYPES_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(TA_LOAD_WALLET_TYPES_FAIL);
            });
    },

    [TA_LOAD_WALLET_ADDRESSES]({ commit, getters, state }) {
        var wallet_type = state.form.user_crypto_address_type.id;
        var trust_anchor_user_id = state.form.attestation_user.id;
        console.log('wallet_type');
        console.log(wallet_type);
        console.log(`wallet-addresses/${getters.UID}`);
        return axios.get(`wallet-addresses/${getters.UID}?wallet_type=`+wallet_type+`&trust_anchor_user_id=`+trust_anchor_user_id)
            .then(({ data }) => {
                commit(TA_LOAD_WALLET_ADDRESSES_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(TA_LOAD_WALLET_ADDRESSES_FAIL);
            });
    },

    [TA_ASSIGN_CRYPTO_ADDRESS]({ commit, dispatch, getters, state }, payload) {
        console.log('actions TA_ASSIGN_CRYPTO_ADDRESS');
        console.log(state.form.attestation_ta_account);

        var p = {"trust_anchor_user_id": state.form.attestation_user.id,
                "crypto_address": state.form.user_crypto_address,
                "crypto_type": state.form.user_crypto_address_type};

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-assign-crypto-address`, p)
            .then(({ data }) => {
                commit(TA_ASSIGN_CRYPTO_ADDRESS_SUCCESS, data);
                dispatch('loadStates');
                return data;
            })
            .catch((response) => {
                console.log('TA_ASSIGN_CRYPTO_ADDRESS_FAIL');
                console.log(response);
                commit(TA_ASSIGN_CRYPTO_ADDRESS_FAIL);
            });
    },

    [TA_GET_USER_WALLET_ADDRESSES]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_USER_WALLET_ADDRESSES');


        return axios.get(`contracts/trust-anchor/${getters.UID}/ta-get-user-wallet-addresses`)
            .then(({ data }) => {
                commit(TA_GET_USER_WALLET_ADDRESSES_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                console.log('TA_GET_USER_WALLET_ADDRESSES_FAIL');
                console.log(response);
                commit(TA_GET_USER_WALLET_ADDRESSES_FAIL);
            });
    },

    [TA_GET_ALL_ATTESTATIONS]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_ALL_ATTESTATIONS');
        return axios.get(`contracts/trust-anchor/${getters.UID}/ta-get-all-attestations`)
            .then(({ data }) => {
                commit(TA_GET_ALL_ATTESTATIONS_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                console.log('TA_GET_ALL_ATTESTATIONS_FAIL');
                console.log(response);
                commit(TA_GET_ALL_ATTESTATIONS_FAIL);
            });
    },

    [TA_SET_KEY_VALUE_PAIR]({ commit, getters, state }) {

        var p = {"account": state.form.attestation_ta_account.account_address,
                "ta_key_name": state.form.ta_key_name.key,
                "ta_key_value": state.form.ta_key_value
            };
        console.log(p);

        return axios.post(`contracts/trust-anchor/${getters.UID}/ta-set-key-value-pair/`, p)
            .then(({ data }) => {
                console.log('TA_SET_KEY_VALUE_PAIR response');
                return data;
            })
            .catch((error) => {
                commit(TA_SET_KEY_VALUE_PAIR_FAIL);
            });
    },

    [TA_GET_DISCOVERY_LAYER_KEYS]({ commit, getters, state }, payload) {
        console.log('actions TA_GET_DISCOVERY_LAYER_KEYS');

        return axios.get(`contracts/trust-anchor/${getters.UID}/ta-get-discovery-layer-keys`)
            .then(({ data }) => {
                commit(TA_GET_DISCOVERY_LAYER_KEYS_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(TA_GET_DISCOVERY_LAYER_KEYS_FAIL);
            });
    },

};
