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
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$throttleLimits = 'throttle:50,1';
if(config('app.env') == 'local') 'throttle:99999,1';

Route::group(['middleware' => ['api', $throttleLimits]], function() {

    Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function() {


        Route::post('ivms101-validate/complete','IVMS101Controller@index')->middleware('jsonschema.validate:complete');
        Route::post('ivms101-validate/beneficiary','IVMS101Controller@index')->middleware('jsonschema.validate:beneficiary');
        Route::post('ivms101-validate/originator','IVMS101Controller@index')->middleware('jsonschema.validate:originator');


        Route::get('verified-trust-anchors','VerifiedTrustAnchorController@index');
        Route::get('trust-anchor-extra-data','DiscoveryController@index');
        Route::get('trust-anchor-extra-data-unique','DiscoveryController@unique');

        //Route::get('coalition-contracts','CoalitionContractController@index');
        //Route::get('coalition-contract-anchors','CoalitionContractController@anchors');

        Route::get('shyft-smart-contract-events','BlockexplorerController@index');
        Route::get('smart-contract-transactions','BlockexplorerController@transactions');
        Route::get('get-smart-contract-transaction','BlockexplorerController@get_transaction');
        Route::get('get-smart-contract-address-transactions','BlockexplorerController@get_address_transactions');
        Route::get('get-smart-contract-attestation-components','BlockexplorerController@get_attestation_components');
        Route::get('get-ta-account-attestations','BlockexplorerController@get_ta_account_attestations');
        Route::get('get-user-account-attestations','BlockexplorerController@get_user_account_attestations');
        // Logging
        //Route::resource('user_states', 'UserStatesController', ['only' => ['index']]);
        Route::resource('countries',   'LocationController', ['only' => ['index', 'show']]);

        // User
        // Route::get('user',             'UserController@index')->middleware('can:index,App\User');
        //Route::get('user',             'UserController@index');
        //Route::resource('user',        'UserController', ['only' => ['update', 'show']]);
        //Route::put('user/{id}/verify', 'UserController@verify');

        Route::get('wallet-types',             'TrustAnchorController@wallet_types');
        Route::get('wallet-addresses/{id}',             'TrustAnchorController@wallet_addresses');

        Route::get('kyctemplates',             'KycTemplateController@index');
        Route::get('kyc-template-details','KycTemplateController@kyc_template_details');

        Route::get('trustanchors',             'TrustAnchorController@index');
        Route::get('trustanchor-users','TrustAnchorController@trustanchor_users');
        Route::get('trustanchor-user-attestations','TrustAnchorController@trust_anchor_user_attestations');

        Route::post('contracts/trust-anchor/{id}/create-ta-account', 'ContractsController@create_ta_account');
        Route::post('contracts/trust-anchor/{id}/ta-save-ivms', 'ContractsController@ta_save_ivms');
        Route::post('contracts/trust-anchor/{id}/ta-is-verified', 'ContractsController@ta_is_verified');
        Route::post('contracts/trust-anchor/{id}/ta-reload-account', 'ContractsController@ta_reload_account');
        Route::post('contracts/trust-anchor/{id}/ta-set-jurisdiction', 'ContractsController@ta_set_jurisdiction');

        Route::post('contracts/trust-anchor/{id}/ta-create-user', 'ContractsController@ta_create_user');
        Route::post('contracts/trust-anchor/{id}/ta-create-random-users', 'ContractsController@ta_create_random_users');
        Route::post('contracts/trust-anchor/{id}/ta-set-attestation', 'ContractsController@ta_set_attestation');
        Route::post('contracts/trust-anchor/{id}/ta-get-balance', 'ContractsController@ta_get_balance');

        Route::post('contracts/trust-anchor/{id}/ta-set-unique-address', 'ContractsController@ta_set_unique_address');
        Route::post('contracts/trust-anchor/{id}/ta-get-unique-address', 'ContractsController@ta_get_unique_address');

        Route::post('contracts/trust-anchor/{id}/ta-set-key-value-pair', 'ContractsController@ta_set_key_value_pair');
        Route::get('contracts/trust-anchor/{id}/ta-get-discovery-layer-keys', 'ContractsController@ta_get_discovery_layer_keys');
        Route::post('contracts/trust-anchor/{id}/ta-request-tokens', 'ContractsController@ta_request_tokens');
        Route::post('contracts/trust-anchor/{id}/ta-get-user-attestations', 'ContractsController@ta_get_user_attestations');
        Route::post('contracts/trust-anchor/{id}/ta-get-attestation-components-in-array', 'ContractsController@ta_get_attestation_components_in_array');

        Route::get('contracts/trust-anchor/{id}/ta-get-trust-anchors', 'ContractsController@ta_get_trust_anchors');
        Route::post('contracts/trust-anchor/{id}/ta-get-trust-anchor-users', 'ContractsController@ta_get_trust_anchor_users');
        Route::post('contracts/trust-anchor/{id}/ta-assign-crypto-address', 'ContractsController@ta_assign_crypto_address');

        Route::get('contracts/trust-anchor/{id}/ta-get-user-wallet-addresses', 'ContractsController@ta_get_user_wallet_addresses');
        Route::get('contracts/trust-anchor/{id}/ta-get-all-users', 'ContractsController@ta_get_all_users');
        Route::get('contracts/trust-anchor/{id}/ta-get-all-attestations', 'ContractsController@ta_get_all_attestations');

        Route::post('contracts/trust-anchor/{id}/ta-register-jurisdiction', 'ContractsController@ta_register_jurisdiction');

    });
});
