<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

if (App::environment('new_prod')) {
    URL::forceScheme('https');
}

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "auth:api" middleware group. Enjoy building your API!
|
*/

$throttleLimits = 'throttle:50,1';
if(config('app.env') == 'local') 'throttle:99999,1';

Route::group(['middleware' => ['auth:api', $throttleLimits]], function() {

    Route::group(['prefix' => 'v1/server', 'namespace' => 'Api\Server'], function() {

        Route::post('set_v3_attestation','ContractsController@ta_set_v3_attestation');
        Route::post('create_shyft_user','ContractsController@create_shyft_user');
        Route::get('get_jurisdictions','ContractsController@get_jurisdictions');

        Route::get('get_verified_trust_anchors','TrustAnchorController@get_verified_trust_anchors');
        Route::get('verify_trust_anchor/{address}','TrustAnchorController@verify_trust_anchor');
        Route::get('get_trust_anchor_details/{address}','TrustAnchorController@get_trust_anchor_details');
        Route::get('refresh_all_verified_trust_anchors','TrustAnchorController@refresh_all_verified_trust_anchors');
        Route::get('refresh_all_discovery_layer_key_value_pairs', 'TrustAnchorController@refresh_all_discovery_layer_key_value_pairs');

        # for KYC Template
        Route::get('get_trust_anchor_account','TrustAnchorController@get_trust_anchor_account');
        Route::get('get_attestations','TrustAnchorController@get_attestations');
        Route::post('create_kyc_template','TrustAnchorController@create_kyc_template');
        Route::put('retry_kyc_template','TrustAnchorController@retry_kyc_template');
        Route::get('get_kyc_templates','TrustAnchorController@get_kyc_templates');
        Route::get('get_trust_anchor_api_url','TrustAnchorController@get_trust_anchor_api_url');
        Route::post('encrypt_ivms','TrustAnchorController@encrypt_ivms');
        Route::post('decrypt_ivms','TrustAnchorController@decrypt_ivms');
        Route::post('recover_signature','TrustAnchorController@recover_signature');

        /* Blockchain analytics api methods */

        Route::get('get-blockchain-analytics-providers', 'BlockchainAnalyticsApiController@get_ba_providers');
        Route::get('get-blockchain-analytics-providers-available-networks/{id}', 'BlockchainAnalyticsApiController@get_ba_providers_available_networks');
        Route::post('generate-blockchain-analytics-report', 'BlockchainAnalyticsApiController@get_ba_report');

    });

    Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function() {

        Route::get('tokens','TokenController@index');

        Route::get('verified-trust-anchors','VerifiedTrustAnchorController@index');
        Route::get('trust-anchor-extra-data','DiscoveryController@index');
        Route::get('trust-anchor-extra-data-unique','DiscoveryController@unique');
        Route::get('trust-anchor-extra-data-unique-validations','DiscoveryController@validations');
        Route::get('blockchain-analytics-addresses','BlockchainAnalyticsAddressesController@index');
        Route::get('get-blockchain-analytics-report','BlockchainAnalyticsAddressesController@get_report');
        Route::post('create-blockchain-analytics-report/{id}','BlockchainAnalyticsAddressesController@createReport');

        Route::resource('baProviders',   'BlockchainAnalyticsController', ['only' => ['index', 'show']]);



        Route::get('shyft-smart-contract-events','BlockexplorerController@index');
        Route::get('smart-contract-transactions','BlockexplorerController@transactions');
        Route::get('get-smart-contract-transaction','BlockexplorerController@get_transaction');
        Route::get('get-smart-contract-address-transactions','BlockexplorerController@get_address_transactions');
        Route::get('get-smart-contract-attestation-components','BlockexplorerController@get_attestation_components');
        Route::get('get-ta-account-attestations','BlockexplorerController@get_ta_account_attestations');
        Route::get('get-user-account-attestations','BlockexplorerController@get_user_account_attestations');

        Route::resource('countries','LocationController', ['only' => ['index', 'show']]);

        Route::get('wallet-types','TrustAnchorController@wallet_types');
        Route::get('wallet-addresses/{id}','TrustAnchorController@wallet_addresses');

        Route::get('kyctemplates','KycTemplateController@index');
        Route::get('kyc-template-details','KycTemplateController@kyc_template_details');
        Route::get('kyc-template-data-state-machine','KycTemplateController@kyc_template_data_state_machine');
        Route::get('kyc-template-webhook-state-machine','KycTemplateController@kyc_template_webhook_state_machine');
        Route::get('kyc-template-ivms-state-machine','KycTemplateController@kyc_template_ivms_state_machine');

        Route::get('trustanchors','TrustAnchorController@index');
        Route::get('trustanchor-users','TrustAnchorController@trustanchor_users');
        Route::get('trustanchor-user-attestations','TrustAnchorController@trust_anchor_user_attestations');

        Route::post('contracts/trust-anchor/{id}/create-ta-account', 'ContractsController@create_ta_account');
        Route::post('contracts/trust-anchor/{id}/ta-save-ivms', 'ContractsController@ta_save_ivms');
        Route::post('contracts/trust-anchor/{id}/ta-is-verified', 'ContractsController@ta_is_verified');

        Route::post('contracts/trust-anchor/{id}/ta-create-user', 'ContractsController@ta_create_user');
        Route::post('contracts/trust-anchor/{id}/ta-get-balance', 'ContractsController@ta_get_balance');

        Route::get('contracts/trust-anchor/{id}/ta-get-discovery-layer-keys', 'ContractsController@ta_get_discovery_layer_keys');

        Route::post('contracts/trust-anchor/{id}/ta-get-attestation-components-in-array', 'ContractsController@ta_get_attestation_components_in_array');

        Route::get('contracts/trust-anchor/{id}/ta-get-trust-anchors', 'ContractsController@ta_get_trust_anchors');
        Route::post('contracts/trust-anchor/{id}/ta-assign-crypto-address', 'ContractsController@ta_assign_crypto_address');
        Route::post('contracts/trust-anchor/{id}/ta-set-key-value-pair', 'ContractsController@ta_set_key_value_pair');

        Route::get('contracts/trust-anchor/{id}/ta-get-user-wallet-addresses', 'ContractsController@ta_get_user_wallet_addresses');
        Route::get('contracts/trust-anchor/{id}/ta-get-all-users', 'ContractsController@ta_get_all_users');
        Route::get('contracts/trust-anchor/{id}/ta-get-all-attestations', 'ContractsController@ta_get_all_attestations');

        Route::get('contracts/trust-anchor/{id}/refresh-all-attestations', 'ContractsController@refresh_all_attestations');
        Route::get('contracts/trust-anchor/{id}/refresh-all-discovery-layer-key-value-pairs', 'ContractsController@refresh_all_discovery_layer_key_value_pairs');
        Route::get('contracts/trust-anchor/{id}/refresh-all-verified-tas', 'ContractsController@refresh_all_verified_tas');

    });
});
