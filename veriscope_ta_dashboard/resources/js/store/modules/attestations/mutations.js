import {

    COMPLETE_ROUTE,
    CURRENT_ROUTE,
    ATTESTATIONS_SOCKET_CONNECTION_SUCCESS,
    //KEEP
    CREATE_TA_ACCOUNT_SUCCESS,
    CREATE_TA_ACCOUNT_FAIL,
    //KEEP
    FRESH_IVMS_SUCCESS,
    FRESH_IVMS_FAIL,
    //KEEP
    TA_SAVE_IVMS_SUCCESS,
    TA_SAVE_IVMS_FAIL,
    //KEEP
    EXPORT_OVASP_IVMS_FAIL,
    EXPORT_BVASP_IVMS_FAIL,
    //KEEP
    TA_IS_VERIFIED_SUCCESS,
    TA_IS_VERIFIED_FAIL,
    //KEEP
    TA_GET_BALANCE_SUCCESS,
    TA_GET_BALANCE_FAIL,
    //KEEP
    TA_SET_KEY_VALUE_PAIR_SUCCESS,
    TA_SET_KEY_VALUE_PAIR_FAIL,

    TA_EVENT_SUCCESS,
    TA_CREATE_USER_SUCCESS,
    TA_CREATE_USER_FAIL,


    TA_GET_TAS_SUCCESS,
    TA_GET_TAS_FAIL,

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
        state.showTaCreateAccountGreenMark = 'block';
        state.showTaCreateAccountErrorMark = 'none';
        window.location.reload();

    },
    /**
     * Called when user information fails saving
     * @param {object} state
     */
     [CREATE_TA_ACCOUNT_FAIL] (state, payload=[], { message='', errors=[] } = {}) {
        console.log('mutations CREATE_TA_ACCOUNT_FAIL');
        console.log(state);
        state.ta_temp_account = payload;
        state.showTaCreateAccount = 'block';
        state.showTaCreateAccountGreenMark = 'none';
        state.showTaCreateAccountErrorMark = 'block';
    },

    [FRESH_IVMS_SUCCESS] (state, payload=[]) {
        console.log('mutations FRESH_IVMS_SUCCESS');
        state.form.attestation_ta_account = payload;
        state.form.ivms_legal_person_name = state.form.attestation_ta_account.legal_person_name;
        state.form.ivms_legal_person_name_identifier_type = state.form.attestation_ta_account.legal_person_name_identifier_type;
        state.form.ivms_address_type = state.form.attestation_ta_account.address_type;
        state.form.ivms_street_name = state.form.attestation_ta_account.street_name;
        state.form.ivms_building_number = state.form.attestation_ta_account.building_number;
        state.form.ivms_building_name = state.form.attestation_ta_account.building_name;
        state.form.ivms_postcode = state.form.attestation_ta_account.postcode;
        state.form.ivms_town_name = state.form.attestation_ta_account.town_name;
        state.form.ivms_country_sub_division = state.form.attestation_ta_account.country_sub_division;
        state.form.ivms_country = state.form.attestation_ta_account.country;
        state.form.ivms_department = state.form.attestation_ta_account.department;
        state.form.ivms_sub_department = state.form.attestation_ta_account.sub_department;
        state.form.ivms_floor = state.form.attestation_ta_account.floor;
        state.form.ivms_room = state.form.attestation_ta_account.room;
        state.form.ivms_town_location_name = state.form.attestation_ta_account.town_location_name;
        state.form.ivms_district_name = state.form.attestation_ta_account.district_name;
        state.form.ivms_address_line = state.form.attestation_ta_account.address_line;
        state.form.ivms_postbox = state.form.attestation_ta_account.postbox;
        state.form.ivms_customer_identification = state.form.attestation_ta_account.customer_identification;
        state.form.ivms_national_identifier = state.form.attestation_ta_account.national_identifier;
        state.form.ivms_national_identifier_type = state.form.attestation_ta_account.national_identifier_type;
        state.form.ivms_country_of_registration = state.form.attestation_ta_account.country_of_registration;
    },

    [TA_SAVE_IVMS_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_SAVE_IVMS_SUCCESS');
        console.log(state);
        state.updatedIvmsData = 'Update Success';
        console.log(payload);

        state.showUpdatedIvmsData = 'block';
        state.showUpdatedIvmsDataGreenMark = 'block';
        state.showUpdatedIvmsDataErrorMark = 'none';
        window.location.reload();

    },

    [TA_SAVE_IVMS_FAIL] (state, { message='', errors=[] } = {}) {
        console.log('mutations TA_SAVE_IVMS_FAIL');
        console.log(state);
        state.updatedIvmsData = 'Please check systemcheck page or TA account not selected.';
        state.showUpdatedIvmsData = 'block';
        state.showUpdatedIvmsDataGreenMark = 'none';
        state.showUpdatedIvmsDataErrorMark = 'block';
    },

    [EXPORT_OVASP_IVMS_FAIL] (state, payload=[]) {
        console.log('mutations EXPORT_OVASP_IVMS_FAIL');
        console.log('state');
        console.log(state);
        state.exportIVMSFailedData = 'Please check systemcheck page or TA account not selected.';
        state.showExportIVMSFailedData = 'block';

    },

    [EXPORT_BVASP_IVMS_FAIL] (state, payload=[]) {
        console.log('mutations EXPORT_BVASP_IVMS_FAIL');
        console.log('state');
        console.log(state);
        state.exportIVMSFailedData = 'Please check systemcheck page or TA account not selected.';
        state.showExportIVMSFailedData = 'block';

    },

    [TA_IS_VERIFIED_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_IS_VERIFIED_SUCCESS');
        state.taIsVerifiedData = payload;
        console.log(state);
        state.showTaIsVerifiedData = 'block';
        state.showTaIsVerifiedDataGreenMark = 'block';
        state.showTaIsVerifiedDataErrorMark = 'none';


    },
    [TA_IS_VERIFIED_FAIL] (state, payload=[], { message='', errors=[] } = {}) {
        console.log('mutations TA_IS_VERIFIED_FAIL');
        state.taIsVerifiedData = payload;
        state.showTaIsVerifiedData = 'block';
        state.showTaIsVerifiedDataGreenMark = 'none';
        state.showTaIsVerifiedDataErrorMark = 'block';
        console.log(state);
    },

    [TA_EVENT_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_EVENT_SUCCESS_SUCCESS');
        state.taEventData.push(payload);
        console.log(state.taEventData);
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

    [TA_GET_BALANCE_SUCCESS] (state, payload=[]) {
        console.log('mutations TA_GET_BALANCE_SUCCESS');
        console.log(state);
        state.taGetBalanceData = payload;
        state.showTaGetBalanceResult = 'block';
        state.showTaGetBalanceResultGreenMark = 'block';
        state.showTaGetBalanceResultErrorMark = 'none';
    },

    [TA_GET_BALANCE_FAIL] (state, payload=[], { message='', errors=[] } = {}) {
        state.taGetBalanceData = payload;
        state.showTaGetBalanceResult = 'block';
        state.showTaGetBalanceResultGreenMark = 'none';
        state.showTaGetBalanceResultErrorMark = 'block';
        console.log('mutations TA_GET_BALANCE_FAIL');
        console.log(state);
    },

    [TA_SET_KEY_VALUE_PAIR_SUCCESS] (state, payload=[]) {
        console.log('TA_SET_KEY_VALUE_PAIR_SUCCESS mutations');
        console.log('TA_SET_KEY_VALUE_PAIR_SUCCESS state');
        console.log(state);
        console.log('TA_SET_KEY_VALUE_PAIR_SUCCESS payload');
        console.log(payload);
        // if(!Array.isArray(payload)) return;
        state.taSetKeyValuePairData = 'Success';
        state.showKeyValuePairResult = 'block';
        state.showKeyValuePairResultGreenMark = 'block';
        state.showKeyValuePairResultErrorMark = 'none';
        console.log('state.taSetKeyValuePairData');
        console.log(state.taSetKeyValuePairData);
    },

    [TA_SET_KEY_VALUE_PAIR_FAIL] (state, payload=[], { message='', errors=[] } = {}) {
        console.log('TA_SET_KEY_VALUE_PAIR_FAIL mutations');

        state.taSetKeyValuePairData = payload;
        state.showKeyValuePairResult = 'block';
        state.showKeyValuePairResultGreenMark = 'none';
        state.showKeyValuePairResultErrorMark = 'block';
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
};
