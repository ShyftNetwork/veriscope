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


export const TA_REGISTER_JURISDICTION = 'taRegisterJurisdiction';
export const TA_REGISTER_JURISDICTION_SUCCESS = 'taRegisterJurisdictionSuccess';
export const TA_REGISTER_JURISDICTION_FAIL = 'taRegisterJurisdictionFail';

export const TA_SAVE_IVMS = 'taSaveIvms';
export const TA_SAVE_IVMS_SUCCESS = 'taSaveIvmsSuccess';
export const TA_SAVE_IVMS_FAIL = 'taSaveIvmsFail';

export const TA_IS_VERIFIED = 'taIsVerified';
export const TA_IS_VERIFIED_SUCCESS = 'taIsVerifiedSuccess';
export const TA_IS_VERIFIED_FAIL = 'taIsVerifiedFail';

export const TA_EVENT_SUCCESS = 'taEventSuccess';

export const TA_SET_JURISDICTION = 'taSetJurisdiction';
export const TA_SET_JURISDICTION_SUCCESS = 'taSetJurisdictionSuccess';
export const TA_SET_JURISDICTION_FAIL = 'taSetJurisdictionFail';

export const TA_CREATE_USER = 'taCreateUser';
export const TA_CREATE_USER_SUCCESS = 'taCreateUserSuccess';
export const TA_CREATE_USER_FAIL = 'taCreateUserFail';

export const TA_CREATE_RANDOM_USERS = 'taCreateRandomUsers';
export const TA_CREATE_RANDOM_USERS_SUCCESS = 'taCreateRandomUsersSuccess';
export const TA_CREATE_RANDOM_USERS_FAIL = 'taCreateRandomUsersFail';

export const TA_SET_ATTESTATION = 'taSetAttestation';
export const TA_SET_ATTESTATION_SUCCESS = 'taSetAttestationSuccess';
export const TA_SET_ATTESTATION_FAIL = 'taSetAttestationFail';

export const TA_GET_BALANCE = 'taGetBalance';
export const TA_GET_BALANCE_SUCCESS = 'taGetBalanceSuccess';
export const TA_GET_BALANCE_FAIL = 'taGetBalanceFail';

export const TA_GET_USER_ATTESTATIONS = 'taGetUserAttestations';
export const TA_GET_USER_ATTESTATIONS_SUCCESS = 'taGetUserAttestationsSuccess';
export const TA_GET_USER_ATTESTATIONS_FAIL = 'taGetUserAttestationsFail';

export const TA_GET_ATTESTATION_COMPONENTS = 'taGetAttestationComponents';
export const TA_GET_ATTESTATION_COMPONENTS_SUCCESS = 'taGetAttestationComponentsSuccess';
export const TA_GET_ATTESTATION_COMPONENTS_FAIL = 'taGetAttestationComponentsFail';

export const TA_GET_TAS = 'taGetTas';
export const TA_GET_TAS_SUCCESS = 'taGetTasSuccess';
export const TA_GET_TAS_FAIL = 'taGetTasFail';

export const TA_GET_USERS = 'taGetUsers';
export const TA_GET_USERS_SUCCESS = 'taGetUsersSuccess';
export const TA_GET_USERS_FAIL = 'taGetUsersFail';

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

export const TA_SET_UNIQUE_ADDRESS = 'taSetUniqueAddress';
export const TA_SET_UNIQUE_ADDRESS_SUCCESS = 'taSetUniqueAddressSuccess';
export const TA_SET_UNIQUE_ADDRESS_FAIL = 'taSetUniqueAddressFail';

export const TA_GET_UNIQUE_ADDRESS = 'taGetUniqueAddress';
export const TA_GET_UNIQUE_ADDRESS_SUCCESS = 'taGetUniqueAddressSuccess';
export const TA_GET_UNIQUE_ADDRESS_FAIL = 'taGetUniqueAddressFail';

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
