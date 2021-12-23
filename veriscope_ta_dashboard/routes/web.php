<?php
if (App::environment('new_prod')) {
    URL::forceScheme('https');
}
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// TODO: clean up all closures and move to controllers (so we can cache the routes)

Auth::routes();

Route::post('webhook','WebhookController@webhook_request');

Route::get('webhook-post-ta-data','WebhookController@webhook_post_ta_data');

Route::post('kyc-template','\App\Http\Controllers\KycTemplateController@kyc_template_request');

Route::post('ivms101-validate/complete','IVMS101Controller@index')->middleware('jsonschema.validate:complete');
Route::post('ivms101-validate/beneficiary','IVMS101Controller@index')->middleware('jsonschema.validate:beneficiary');
Route::post('ivms101-validate/originator','IVMS101Controller@index')->middleware('jsonschema.validate:originator');

Route::get('errors/suspended', function () { return view('errors.suspended'); })->name('suspended');
Route::get('errors/terminated', function () { return view('errors.terminated'); })->name('terminated');
Route::get('errors/403', function () { return view('errors/403'); });
Route::get('errors/404', function () { return view('errors/404'); });
Route::get('errors/500', function () { return view('errors/500'); });
Route::get('errors/503', function () { return view('errors/503'); });

// TODO: should be a post and logout button should be a form submit.
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

// Two factor authentication routes
Route::get('/2fa','PasswordSecurityController@show2faForm');
Route::post('/generate2faSecret','PasswordSecurityController@generate2faSecret')->name('generate2faSecret');
Route::post('/2fa','PasswordSecurityController@enable2fa')->name('enable2fa');
Route::post('/disable2fa','PasswordSecurityController@disable2fa')->name('disable2fa');
Route::post('/2faVerify', function () {
    return redirect(URL()->previous());
})->name('2faVerify')->middleware('2fa');


// set password routes after initial access has been granted
Route::get('auth/password/set/{token}', 'Auth\OnboardController@passwordSet');
Route::post('auth/password/assign', 'Auth\OnboardController@passwordAssign')->name('password.assign');


if(config('shyft.onboarding')) {
    Route::group(['middleware' => 'force.dashboard'], function() {
      Route::get('/', function () { return view('welcome'); });
    });

  // Request access to the website
  Route::get('requested', function () { return view('auth.requested'); })->name('requested');


  Route::get('auth/email/manage', function () { return view('auth.email.manage'); })->name('email.manage');

  Route::group(['middleware' => 'maintenance'], function() {

    Route::group(['middleware' => ['shyft.revoked', 'auth','2fa']], function() {

        Route::group(['prefix' => 'auth'], function(){
            Route::get('welcome', function () { return view('auth.welcome'); })->name('welcome');
            // KYC wizard
            Route::get('kyc', 'Auth\OnboardController@kyc')->name('kyc');

            Route::get('attestations', 'Auth\AttestationController@attestations')->name('attestations');

            Route::get('attestations/manage-organization', 'Auth\AttestationController@manage_organization')->name('manage-organization');
            Route::get('attestations/manage-users', 'Auth\AttestationController@manage_users')->name('manage-users');
            Route::get('attestations/vasp-manager', 'Auth\AttestationController@vasp_manager')->name('vasp-manager');
            Route::get('attestations/fatf-travel-rule-reports', 'Auth\AttestationController@fatf_travel_rule_reports')->name('fatf-travel-rule-reports');
            Route::get('attestations/coalition', 'Auth\AttestationController@coalition')->name('coalition');
            Route::get('attestations/trust-anchor-setup', 'Auth\AttestationController@trust_anchor_setup')->name('trust_anchor_setup');
            Route::get('attestations/new', 'Auth\AttestationController@new')->name('new');
            Route::get('attestations/admin', 'Auth\AttestationController@admin')->name('admin');
            Route::get('attestations/attestation-logs', 'Auth\AttestationController@attestation_logs')->name('attestation_logs');

            Route::get('password/manage', function () {
                return view('auth.passwords.manage');
            })->name('password.manage');

            Route::put('password/manage', '\App\Http\Controllers\Auth\OnboardController@passwordUpdate')->name('password.manage.update');
            Route::put('email/manage', '\App\Http\Controllers\Auth\OnboardController@emailUpdate')->name('email.manage.update');
        });

        // dashboard routes
        Route::group(['prefix' => 'dashboard'], function() {
            Route::get('', function () { return view('auth.welcome'); })->name('welcome');
            Route::get('settings', function () { return view('auth.settings');})->name('settings');
        });

    });
  });
}


// backoffice routes (move to routes/backoffice.php)
if(Config::get('backoffice.enabled')) {
    if(!config('shyft.onboarding')) {
      Route::get('/', function () {
          $path = base_path() . "/package.json"; // ie: /var/www/laravel/app/storage/json/filename.json
          if (!File::exists($path)) {
              throw new Exception("Invalid File");
          }
          $file = File::get($path); // string
          $json = json_decode($file, true);

          return view('backoffice', ['json' => $json]);
      });
    };

    Route::group(['prefix' => 'backoffice', 'middleware' => ['shyft.revoked', 'group:admin','2fa'], 'namespace' => 'Backoffice'], function() {
        Route::get('/', '\App\Http\Controllers\Backoffice\DashboardController@index')->name('backoffice.dashboard');

        Route::resource('kyctemplates', 'KycTemplatesController', ['only' => ['index']]);

        Route::post('kyc-template','\App\Http\Controllers\KycTemplateController@kyc_template_request');
        Route::get('kyctemplates/{id}/details',       'KycTemplatesController@kyc_template_details');

        Route::get('constants', 'ConstantsController@index')->name('constants.index')->middleware('can:edit,App\Constant');
        Route::put('constants/update', 'ConstantsController@update')->name('constants.update')->middleware('can:edit,App\Constant');

        Route::get('/blockexplorer', function () { return view('blockexplorer.index'); })->name('blockexplorer');
        Route::get('/blockexplorer/transaction/{id}/view',       'BlockExplorerController@view');
        Route::get('/blockexplorer/address/{id}/view',       'BlockExplorerController@account_address');
        Route::get('/blockexplorer/attestation/{id}/view',       'BlockExplorerController@attestation_components');
        Route::get('/blockexplorer/ta-account/{id}/view',       'BlockExplorerController@ta_account');
        Route::get('/blockexplorer/user-account/{id}/view',       'BlockExplorerController@user_account');

        Route::get('/discovery', function () { return view('discovery.index'); })->name('discovery');
        Route::get('/verified-trust-anchors', function () { return view('verifiedtrustanchors.index'); })->name('verifiedtrustanchors');

    });
}
