import {

    COMPLETE_ROUTE,
    CURRENT_ROUTE,
    ATTESTATIONS_SOCKET_CONNECTION_SUCCESS,
    //KEEP
    CREATE_TA_ACCOUNT_SUCCESS,
    CREATE_TA_ACCOUNT_FAIL,
    //KEEP
    TA_IS_VERIFIED_SUCCESS,
    TA_IS_VERIFIED_FAIL,
    //KEEP
    TA_GET_BALANCE_SUCCESS,
    TA_GET_BALANCE_FAIL,
    //KEEP
    TA_REGISTER_JURISDICTION_SUCCESS,
    TA_REGISTER_JURISDICTION_FAIL,
    //KEEP
    TA_SET_UNIQUE_ADDRESS_SUCCESS,
    TA_SET_UNIQUE_ADDRESS_FAIL,
    //KEEP
    TA_SET_KEY_VALUE_PAIR_SUCCESS,
    TA_SET_KEY_VALUE_PAIR_FAIL,

    TA_SET_JURISDICTION_SUCCESS,
    TA_SET_JURISDICTION_FAIL,

    TA_EVENT_SUCCESS,
    TA_CREATE_USER_SUCCESS,
    TA_CREATE_USER_FAIL,

    TA_CREATE_RANDOM_USERS_SUCCESS,
    TA_CREATE_RANDOM_USERS_FAIL,

    TA_SET_ATTESTATION_SUCCESS,
    TA_SET_ATTESTATION_FAIL,




    TA_GET_UNIQUE_ADDRESS_SUCCESS,
    TA_GET_UNIQUE_ADDRESS_FAIL,

    TA_GET_USER_ATTESTATIONS_SUCCESS,
    TA_GET_USER_ATTESTATIONS_FAIL,
    TA_GET_ATTESTATION_COMPONENTS_SUCCESS,
    TA_GET_ATTESTATION_COMPONENTS_FAIL,

    TA_GET_TAS_SUCCESS,
    TA_GET_TAS_FAIL,

    TA_GET_USERS_SUCCESS,
    TA_GET_USERS_FAIL,

    TA_LOAD_COUNTRIES_SUCCESS,
    TA_LOAD_COUNTRIES_FAIL,

    TA_LOAD_WALLET_TYPES_SUCCESS,
    TA_LOAD_WALLET_TYPES_FAIL,

    TA_LOAD_WALLET_ADDRESSES_SUCCESS,
    TA_LOAD_WALLET_ADDRESSES_FAIL,

    TA_ASSIGN_CRYPTO_ADDRESS_SUCCESS,
    TA_ASSIGN_CRYPTO_ADDRESS_FAIL,

    TA_GET_USER_WALLET_ADDRESSES_SUCCESS,
    TA_GET_USER_WALLET_ADDRESSES_FAIL,

    TA_GET_ALL_ATTESTATIONS_SUCCESS,
    TA_GET_ALL_ATTESTATIONS_FAIL,

    TA_GET_DISCOVERY_LAYER_KEYS_SUCCESS,
    TA_GET_DISCOVERY_LAYER_KEYS_FAIL,

} from '../../mutation-types';

export const mutations = {

    /**
     * Used to track completed route steps
     *
     */
    [COMPLETE_ROUTE] (state, payload=null) {
        if(!payload || typeof payload !== 'string') return;
        state.completedRoutes = [
            ...new Set([
                ...state.completedRoutes,
                payload
            ])
        ]
    },
    /**
     * Used to track current route step
     *
     */
    [CURRENT_ROUTE] (state, { currentRoute='' }={}) {
        state.currentRoute = currentRoute;
    },

    [ATTESTATIONS_SOCKET_CONNECTION_SUCCESS] (state, payload=[]) {
        console.log('ATTESTATIONS_SOCKET_CONNECTION_SUCCESS mutations');
        console.log(state);

        state.attestationsSocketId = payload;
    },

    [CREATE_TA_ACCOUNT_SUCCESS] (state, payload=[]) {
        console.log('mutations CREATE_TA_ACCOUNT_SUCCESS');
        console.log('state');
        console.log(state);
        console.log('payload');
        console.log(payload);
        state.getTasData = [];
        state.getTasData.push(payload);
        // state.ta_accounts.push(payload);
        state.ta_temp_account = payload['account_address'];
        state.showTaCreateAccount = 'block';

    },
    /**
     * Called when user information fails saving
     * @param {object} state
     */
    [CREATE_TA_ACCOUNT_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations CREATE_TA_ACCOUNT_FAIL');
        console.log(state);
    },

    [TA_REGISTER_JURISDICTION_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_REGISTER_JURISDICTION_SUCCESS');
        console.log(state);
        state.taRegisterJurisdictionData = payload;


    },
    [TA_REGISTER_JURISDICTION_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_REGISTER_JURISDICTION_FAIL');
        console.log(state);
    },

    [TA_IS_VERIFIED_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_IS_VERIFIED_SUCCESS');
        state.taIsVerifiedData = payload;
        console.log(state);

        state.showTaIsVerifiedData = 'block';


    },
    [TA_IS_VERIFIED_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_IS_VERIFIED_FAIL');
        console.log(state);
    },

    [TA_EVENT_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_EVENT_SUCCESS_SUCCESS');
        state.taEventData.push(payload);
        console.log(state.taEventData);
    },

    [TA_SET_JURISDICTION_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_SET_JURISDICTION_SUCCESS');
        console.log(state);
        state.taSetJurisdictionData = payload;
        console.log(state.taSetJurisdictionData);
    },

    [TA_SET_JURISDICTION_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_SET_JURISDICTION_FAIL');
        console.log(state);
    },

    [TA_CREATE_USER_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_CREATE_USER_SUCCESS');
        console.log(state);
        state.ta_user_accounts.push(payload);
        state.ta_temp_user = payload['account'];
        state.form.attestation_user_account = payload['account'];
        state.showTaCreateUserResult = 'block';
    },

    [TA_CREATE_USER_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_CREATE_USER_FAIL');
        console.log(state);
    },

    [TA_CREATE_RANDOM_USERS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_CREATE_RANDOM_USERS_SUCCESS');
        console.log(state);
        console.log(payload);
        // state.ta_user_accounts.push(payload);
        // state.ta_temp_user = payload['account'];
        // state.form.attestation_user_account = payload['account'];
        // state.showTaCreateUserResult = 'block';
    },

    [TA_CREATE_RANDOM_USERS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_CREATE_RANDOM_USERS_FAIL');
        console.log(state);
    },


    [TA_SET_ATTESTATION_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_SET_ATTESTATION_SUCCESS');
        console.log(state);


        if(payload['attestation_type'] === 'KYC') {
            state.taSetKycAttestationData = parseInt(payload['result']) + 2;
            state.taSetKycAttestationHashData = payload['resultAttestationKeccak'];
            state.showTaSetKycAttestationResult = 'block';
            state.showTaSetKycAttestationHash = 'block';
            state.showTaSetKycAttestationError = 'none';

        }
        else if(payload['attestation_type'] === 'WALLET') {
            state.taSetWalletAttestationData = parseInt(payload['result']) + 2;
            state.taSetWalletAttestationHashData = payload['resultAttestationKeccak'];
            state.showFatfButton = 'block';

            state.showTaSetWalletAttestationError = 'none';
            state.showTaSetWalletAttestationResult = 'block';
            state.showTaSetWalletAttestationHash = 'block';
        }

    },

    [TA_SET_ATTESTATION_FAIL] (state, payload={}) {
        console.log('mutations TA_SET_ATTESTATION_FAIL');
        console.log(payload);
        console.log(state);
        if(payload['attestation_type'] === 'KYC') {
            state.taSetKycAttestationError = payload['message'];
            state.showTaSetKycAttestationError = 'block';
            state.showTaSetKycAttestationResult = 'none';
            state.showTaSetKycAttestationHash = 'none';
        }
        else if(payload['attestation_type'] === 'WALLET') {
            state.taSetWalletAttestationError = payload['message'];
            state.showTaSetWalletAttestationError = 'block';
            state.showTaSetWalletAttestationResult = 'none';
            state.showTaSetWalletAttestationHash = 'none';
        }

    },

    [TA_GET_BALANCE_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_BALANCE_SUCCESS');
        console.log(state);
        state.taGetBalanceData = payload;
        state.showTaGetBalanceResult = 'block';
    },

    [TA_GET_BALANCE_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_GET_BALANCE_FAIL');
        console.log(state);
    },

    /**
     * Called when user information successfully saved
     * @param {object} state
     */
    [TA_SET_UNIQUE_ADDRESS_SUCCESS] (state, payload=[]) {
        console.log('TA_SET_UNIQUE_ADDRESS_SUCCESS mutations');
        console.log('TA_SET_UNIQUE_ADDRESS_SUCCESS state');
        console.log(state);
        console.log('TA_SET_UNIQUE_ADDRESS_SUCCESS payload');
        console.log(payload);
        // if(!Array.isArray(payload)) return;
        state.taSetUniqueAddressData = payload;
        console.log('state.taSetUniqueAddressData');
        console.log(state.taSetUniqueAddressData);
    },
    /**
     * Called when user information fails saving
     * @param {object} state
     */
    [TA_SET_UNIQUE_ADDRESS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('TA_SET_UNIQUE_ADDRESS_FAIL mutations');
        console.log(state);
        console.log(message);
        console.log(errors);
    },

     /**
     * Called when user information successfully saved
     * @param {object} state
     */
    [TA_GET_UNIQUE_ADDRESS_SUCCESS] (state, payload=[]) {
        console.log('TA_GET_UNIQUE_ADDRESS_SUCCESS mutations');
        console.log('TA_GET_UNIQUE_ADDRESS_SUCCESS state');
        console.log(state);
        console.log('TA_GET_UNIQUE_ADDRESS_SUCCESS payload');
        console.log(payload);
        // if(!Array.isArray(payload)) return;
        state.taGetUniqueAddressData = payload;
        console.log('state.taGetUniqueAddressData');
        console.log(state.taGetUniqueAddressData);
    },
    /**
     * Called when user information fails saving
     * @param {object} state
     */
    [TA_GET_UNIQUE_ADDRESS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('TA_GET_UNIQUE_ADDRESS_FAIL mutations');
        console.log(state);
        console.log(message);
        console.log(errors);
    },

    [TA_GET_USER_ATTESTATIONS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_USER_ATTESTATIONS_SUCCESS');
        console.log(state);
        console.log(payload);
        state.taGetUserAttestationsData = payload;
    },

    [TA_GET_USER_ATTESTATIONS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_GET_USER_ATTESTATIONS_FAIL');
        console.log(state);
    },

    [TA_GET_ATTESTATION_COMPONENTS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_ATTESTATION_COMPONENTS_SUCCESS');
        console.log(state);
        state.taGetAttestationComponentsData = payload;
    },

    [TA_GET_ATTESTATION_COMPONENTS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_GET_ATTESTATION_COMPONENTS_FAIL');
        console.log(state);
    },

    [TA_GET_TAS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_TAS_SUCCESS');
        console.log(state);
        state.getTasData = payload;

    },

    [TA_GET_TAS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_GET_TAS_FAIL');
        console.log(state);
    },

    [TA_GET_DISCOVERY_LAYER_KEYS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_DISCOVERY_LAYER_KEYS_SUCCESS');
        console.log(state);
        state.taGetDiscoveryLayerKeysData = payload;

    },

    [TA_GET_DISCOVERY_LAYER_KEYS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_GET_DISCOVERY_LAYER_KEYS_FAIL');
        console.log(state);
    },

    [TA_GET_USERS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_TA_USERS_SUCCESS');
        console.log(state);
        state.getTaUsersData = payload;

    },

    [TA_GET_USERS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_GET_TA_USERS_FAIL');
        console.log(state);
    },

    /**
     * Used to load the Countries data
     *
     */
    [TA_LOAD_COUNTRIES_SUCCESS] (state, payload=[]) {
        if(!Array.isArray(payload)) return;
        state.taCountryData = payload;
    },
    /**
     * Used when loading the Countries data fails
     *
     */
    [TA_LOAD_COUNTRIES_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('TA Country data failed to load');
    },
    /**
     * Used to load the Kyc data
     *
     */
    [TA_LOAD_WALLET_TYPES_SUCCESS] (state, payload=[]) {

        if(!Array.isArray(payload)) return;
        state.taWalletTypeData = payload;
    },
    /**
     * Used when loading the Countries data fails
     *
     */
    [TA_LOAD_WALLET_TYPES_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('TA Wallet Type data failed to load');
    },

    /**
     * Used to load the Wallet data
     *
     */
    [TA_LOAD_WALLET_ADDRESSES_SUCCESS] (state, payload=[]) {

        if(!Array.isArray(payload)) return;
        console.log('TA_LOAD_WALLET_ADDRESSES_SUCCESS');
        console.log(payload);
        state.taWalletAddressData = payload;
    },
    /**
     * Used when loading the Countries data fails
     *
     */
    [TA_LOAD_WALLET_ADDRESSES_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('TA Wallet Addresses data failed to load');
    },

    [TA_ASSIGN_CRYPTO_ADDRESS_SUCCESS] (state, payload=[]) {
        state.taAssignCryptoAddressData = payload['crypto_address'];

    },
    [TA_ASSIGN_CRYPTO_ADDRESS_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('TA Assign Crypto Address data failed to load');
    },

    [TA_GET_USER_WALLET_ADDRESSES_SUCCESS] (state, payload=[]) {
        console.log('TA_GET_USER_WALLET_ADDRESSES_SUCCESS');
        console.log(payload);
        if(!Array.isArray(payload)) return;
        state.taGetUserWalletAddressData = payload;

    },
    [TA_GET_USER_WALLET_ADDRESSES_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('TA_GET_USER_WALLET_ADDRESSES_FAIL failed to load');
    },

    [TA_GET_ALL_ATTESTATIONS_SUCCESS] (state, payload=[]) {
        console.log('TA_GET_ALL_ATTESTATIONS_SUCCESS');
        console.log(payload);
        state.taGetAllAttestationsData = payload;

    },

    [TA_GET_ALL_ATTESTATIONS_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('TA_GET_ALL_ATTESTATIONS_FAIL failed to load');
    },

    [TA_SET_KEY_VALUE_PAIR_SUCCESS] (state, payload=[]) {
        console.log('TA_SET_KEY_VALUE_PAIR_SUCCESS mutations');
        console.log('TA_SET_KEY_VALUE_PAIR_SUCCESS state');
        console.log(state);
        console.log('TA_SET_KEY_VALUE_PAIR_SUCCESS payload');
        console.log(payload);
        // if(!Array.isArray(payload)) return;
        state.taSetKeyValuePairData = payload;
        console.log('state.taSetKeyValuePairData');
        console.log(state.taSetKeyValuePairData);
    },

    [TA_SET_KEY_VALUE_PAIR_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('TA_SET_KEY_VALUE_PAIR_FAIL mutations');
        console.log(state);
        console.log(message);
        console.log(errors);
    },
};
