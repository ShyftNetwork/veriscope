/**
 * Contracts
 */

export const ATTESTATIONS_SOCKET_CONNECTION_SUCCESS = 'attestationsSocketConnectionSuccess';

/**
 * TA submission
 */
export const CREATE_TA_ACCOUNT = 'createTaAccount';
export const CREATE_TA_ACCOUNT_SUCCESS = 'createTaAccountSuccess';
export const CREATE_TA_ACCOUNT_FAIL = 'createTaAccountFail';

export const TA_SAVE_IVMS = 'taSaveIvms';
export const TA_SAVE_IVMS_SUCCESS = 'taSaveIvmsSuccess';
export const TA_SAVE_IVMS_FAIL = 'taSaveIvmsFail';

export const TA_IS_VERIFIED = 'taIsVerified';
export const TA_IS_VERIFIED_SUCCESS = 'taIsVerifiedSuccess';
export const TA_IS_VERIFIED_FAIL = 'taIsVerifiedFail';

export const TA_EVENT_SUCCESS = 'taEventSuccess';

export const TA_CREATE_USER = 'taCreateUser';
export const TA_CREATE_USER_SUCCESS = 'taCreateUserSuccess';
export const TA_CREATE_USER_FAIL = 'taCreateUserFail';

export const TA_GET_BALANCE = 'taGetBalance';
export const TA_GET_BALANCE_SUCCESS = 'taGetBalanceSuccess';
export const TA_GET_BALANCE_FAIL = 'taGetBalanceFail';

export const TA_GET_ATTESTATION_COMPONENTS = 'taGetAttestationComponents';
export const TA_GET_ATTESTATION_COMPONENTS_SUCCESS = 'taGetAttestationComponentsSuccess';
export const TA_GET_ATTESTATION_COMPONENTS_FAIL = 'taGetAttestationComponentsFail';

export const TA_GET_TAS = 'taGetTas';
export const TA_GET_TAS_SUCCESS = 'taGetTasSuccess';
export const TA_GET_TAS_FAIL = 'taGetTasFail';

export const TA_LOAD_COUNTRIES = 'taLoadCountries';
export const TA_LOAD_COUNTRIES_SUCCESS = 'taLoadCountriesSuccess';
export const TA_LOAD_COUNTRIES_FAIL = 'taLoadCountriesFail';

export const TA_LOAD_WALLET_TYPES = 'taLoadWalletTypes';
export const TA_LOAD_WALLET_TYPES_SUCCESS = 'taLoadWalletTypesSuccess';
export const TA_LOAD_WALLET_TYPES_FAIL = 'taLoadWalletTypesFail';

export const TA_LOAD_WALLET_ADDRESSES = 'taLoadWalletAddresses';
export const TA_LOAD_WALLET_ADDRESSES_SUCCESS = 'taLoadWalletAddressesSuccess';
export const TA_LOAD_WALLET_ADDRESSES_FAIL = 'ttaLoadWalletAddressesFail';

export const TA_ASSIGN_CRYPTO_ADDRESS = 'taAssignCryptoAddress';
export const TA_ASSIGN_CRYPTO_ADDRESS_SUCCESS = 'taAssignCryptoAddressSuccess';
export const TA_ASSIGN_CRYPTO_ADDRESS_FAIL = 'taAssignCryptoAddressFail';

export const TA_GET_USER_WALLET_ADDRESSES = 'taGetUserWalletAddresses';
export const TA_GET_USER_WALLET_ADDRESSES_SUCCESS = 'taGetUserWalletAddressesSuccess';
export const TA_GET_USER_WALLET_ADDRESSES_FAIL = 'taGetUserWalletAddressesFail';

export const TA_GET_ALL_ATTESTATIONS = 'taGetAllAttestations';
export const TA_GET_ALL_ATTESTATIONS_SUCCESS = 'taGetAllAttestationsSuccess';
export const TA_GET_ALL_ATTESTATIONS_FAIL = 'taGetAllAttestationsFail';

export const TA_SET_KEY_VALUE_PAIR = 'taSetKeyValuePair';
export const TA_SET_KEY_VALUE_PAIR_SUCCESS = 'taSetKeyValuePairSuccess';
export const TA_SET_KEY_VALUE_PAIR_FAIL = 'taSetKeyValuePairFail';

export const TA_GET_DISCOVERY_LAYER_KEYS = 'taGetDiscoveryLayerKeys';
export const TA_GET_DISCOVERY_LAYER_KEYS_SUCCESS = 'taGetDiscoveryLayerKeysSuccess';
export const TA_GET_DISCOVERY_LAYER_KEYS_FAIL = 'taGetDiscoveryLayerKeysFail';

/**
 * User submission
 */
export const SUBMIT_USER = 'submitUser';
export const SUBMIT_USER_SUCCESS = 'submitUserSuccess';
export const SUBMIT_USER_FAIL = 'submitUserFail';
export const SUBMIT_ERROR_MESSAGE_CLEAR = 'submitErrorMessageClear';

/**
 * Route
 */
export const COMPLETE_ROUTE = 'completeRoute';
export const CURRENT_ROUTE = 'currentRoute';

/**
 * Countries
 */
export const LOAD_COUNTRIES = 'loadCountries';
export const LOAD_COUNTRIES_SUCCESS = 'loadCountriesSuccess';
export const LOAD_COUNTRIES_FAIL = 'loadCountriesFail';

/**
 * States
 */
export const LOAD_STATES = 'loadStates';
export const LOAD_STATES_SUCCESS = 'loadStatesSuccess';
export const LOAD_STATES_FAIL = 'loadStatesFail';

/**
 * User
 */
export const LOAD_USER = 'loadUser';
export const LOAD_USER_SUCCESS = 'loadUserSuccess';
export const LOAD_USER_FAIL = 'loadUserFail';
export const UPDATE_USER_COUNTRY = 'updateUserCountry';
export const UPDATE_USER_STATE = 'updateUserState';
export const UPDATE_USER = 'updateUser';
export const SET_UI_COUNTRY = 'setUICountry';
export const SET_UI_STATE = 'setUIState';

/**
 * Blockchain analytics providers 
 */

export const LOAD_BA_PROVIDERS = 'loadBAProviders';
export const LOAD_BA_PROVIDERS_SUCCESS = 'loadBAProvidersSuccess';
export const LOAD_BA_PROVIDERS_FAIL = 'loadBAProvidersFail';

/**
 * Blockchain analytics providers supported networks
 */

 export const LOAD_BA_PROVIDERS_NETWORKS = 'loadBAProvidersNetworks';
 export const LOAD_BA_PROVIDERS_NETWORKS_SUCCESS = 'loadBAProvidersSuccessNetworks';
 export const LOAD_BA_PROVIDERS_NETWORKS_FAIL = 'loadBAProvidersFailNetworks';

 /**
 * Create blockchain analytics report
 */
 export const CREATE_BA_REPORT = 'createBAReport'
 export const CREATE_BA_REPORT_SUCCESS = 'createBAReportSuccess'
 export const CREATE_BA_REPORT_FAIL = 'createBAReportFail'

 /**
 * Update blockchain data
 */

export const REFRESH_ALL_ATTESTATIONS = 'refreshAllAttestations'
export const REFRESH_ALL_DISCOVERY_LAYERS = 'refreshAllDiscoveryLayers'
export const REFRESH_ALL_VERIFIED_TAS = 'refreshAllVerifiedTAs'
