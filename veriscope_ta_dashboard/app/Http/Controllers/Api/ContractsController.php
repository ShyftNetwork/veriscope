<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{User,Country,TrustAnchor, TrustAnchorUser, TrustAnchorUserAttestation, TrustAnchorUserCryptoAddress, TrustAnchorAssociationCrypto, CryptoWalletAddress, TrustAnchorExtraData, DiscoveryLayerKey};
use GuzzleHttp\Client;

class ContractsController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {
          // $this->middleware('auth');

          $this->helper_url = env('HTTP_API_URL');
      }

      public function create_ta_account(Request $request, $id)
      {
          Log::debug('ContractsController create_ta_account');

          $input = $request->all();
       
          $user = User::findOrFail($id);

          Log::debug(print_r($input, true));

          $url = $this->helper_url.'/create-new-user-account?user_id='.$id;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController create_ta_account');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController create_ta_account: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_save_ivms(Request $request, $id)
      {
          Log::debug('ContractsController ta_save_ivms');

          $input = $request->all();

          Log::debug(print_r($input, true));

          $user = User::find($id);
          $ta = TrustAnchor::first();

          if ($input['legal_person_name'])
            $ta->legal_person_name = $input['legal_person_name'];
          if ($input['legal_person_name_identifier_type'])
            $ta->legal_person_name_identifier_type = $input['legal_person_name_identifier_type'];
          if ($input['address_type'])
            $ta->address_type = $input['address_type'];

          if ($input['street_name'])
            $ta->street_name = $input['street_name'];

          if ($input['building_number'])
            $ta->building_number = $input['building_number'];

          if ($input['building_name'])
            $ta->building_name = $input['building_name'];

          if ($input['postcode'])
            $ta->postcode = $input['postcode'];

          if ($input['town_name'])
            $ta->town_name = $input['town_name'];

          if ($input['country_sub_division'])
            $ta->country_sub_division = $input['country_sub_division'];

          if ($input['country'])
            $ta->country = $input['country'];

          $ta->save();

          return response()->json([]);
      }

      public function ta_is_verified(Request $request, $id)
      {
          Log::debug('ContractsController ta_is_verified');

          $input = $request->all();
          $user = User::find($id);
  
          $account = $input['account'];
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-is-verified?user_id='.$id.'&account='.$account;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_is_verified');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_is_verified: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_set_jurisdiction(Request $request, $id)
      {
          Log::debug('ContractsController ta_set_jurisdiction');

          $input = $request->all();
          $user = User::find($id);

          $ta_jurisdiction = $input['ta_jurisdiction'];
          $account = $input['account'];
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-set-jurisdiction?user_id='.$id.'&account='.$account.'&ta_jurisdiction='.$ta_jurisdiction;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_set_jurisdiction');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_set_jurisdiction: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }
      
      public function ta_create_user(Request $request, $id)
      {
          Log::debug('ContractsController ta_create_user');

          $input = $request->all();

          $user = User::findOrFail($id);

          $ta = TrustAnchor::where('id', $input['trust_anchor_account']['id'])->first();
          $input['trust_anchor_id'] = $ta->id;
          $tau = new TrustAnchorUser($input);
          $tau->save();
          $ta_user_id = $tau->id;
          $ta->trustAnchorUser()->save($tau);

          #associate unused crypto address to this user
          // $btc_address = CryptoWalletAddress::where('crypto_wallet_type_id', 1)->where('trust_anchor_user_id', null)->orderBy('id')->first();
          // $btc_address->trust_anchor_user_id = $tau->id;
          // $btc_address->trust_anchor_id = $ta->id;
          // $btc_address->save();

          // $eth_address = CryptoWalletAddress::where('crypto_wallet_type_id', 2)->where('trust_anchor_user_id', null)->orderBy('id')->first();
          // $eth_address->trust_anchor_user_id = $tau->id;
          // $eth_address->trust_anchor_id = $ta->id;
          // $eth_address->save();

          $prefname = $input['prefname'];
          $password = $input['password'];
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-create-user?user_id='.$id.'&ta_user_id='.$ta_user_id.'&prefname='.$prefname.'&password='.$password;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_create_user');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_create_user: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_create_random_users(Request $request, $id)
      {
          Log::debug('ContractsController ta_create_random_users');

          $input = $request->all();

          $user = User::findOrFail($id);

          $ta = TrustAnchor::where('id', $input['trust_anchor_account']['id'])->first();
          $input['trust_anchor_id'] = $ta->id;

          $path = '../app/SqlDumps/fake_users.csv';
          $handle = fopen($path, "r");
          $primaryIdentifiers = array();
          $secondaryIdentifiers = array();
          $nameIdentifierTypes = array();
          $addressTypes = array();
          $streetNames = array();
          $buildingNumbers = array();
          $postcodes = array();
          $townNames = array();
          $countrySubDivisions = array();
          $countrys = array();
          $nationalIdentifiers = array();
          $nationalIdentifierTypes = array();
          $countryOfIssues = array();
          $registrationAuthoritys = array();
          $dateOfBirths = array();
          $placeOfBirths = array();
          $countryOfResidences = array();

          while ($csvLine = fgetcsv($handle, 1000, ",")) {
            $primaryIdentifier = $csvLine[0];
            array_push($primaryIdentifiers, $primaryIdentifier);
            $secondaryIdentifier = $csvLine[1];
            array_push($secondaryIdentifiers, $secondaryIdentifier);
            $nameIdentifierType = $csvLine[2];
            array_push($nameIdentifierTypes, $nameIdentifierType);
            $addressType = $csvLine[3];
            array_push($addressTypes, $addressType);
            $streetName = $csvLine[4];
            array_push($streetNames, $streetName);
            $buildingNumber = $csvLine[5];
            array_push($buildingNumbers, $buildingNumber);
            $postcode = $csvLine[6];
            array_push($postcodes, $postcode);
            $townName = $csvLine[7];
            array_push($townNames, $townName);
            $countrySubDivision = $csvLine[8];
            array_push($countrySubDivisions, $countrySubDivision);
            $country = $csvLine[9];
            array_push($countrys, $country);
            $nationalIdentifier = $csvLine[10];
            array_push($nationalIdentifiers, $nationalIdentifier);
            $nationalIdentifierType = $csvLine[11];
            array_push($nationalIdentifierTypes, $nationalIdentifierType);
            $countryOfIssue = $csvLine[12];
            array_push($countryOfIssues, $countryOfIssue);
            $registrationAuthority = $csvLine[13];
            array_push($registrationAuthoritys, $registrationAuthority);
            $dateOfBirth = $csvLine[14];
            array_push($dateOfBirths, $dateOfBirth);
            $placeOfBirth = $csvLine[15];
            array_push($placeOfBirths, $placeOfBirth);
            $countryOfResidence = $csvLine[16];
            array_push($countryOfResidences, $countryOfResidence);
              
          }

          $length = count($primaryIdentifiers);
          

          $index = rand(0, $length);
       
          $full_name = $primaryIdentifiers[$index]. ' ' .$secondaryIdentifiers[$index];
          Log::debug(print_r($full_name, true));

          $input['trust_anchor_id'] = $ta->id;
          $input['prefname'] = $full_name;
          $input['primary_identifier'] = $primaryIdentifiers[$index];
          $input['secondary_identifier'] = $secondaryIdentifiers[$index];
          $input['name_identifier_type'] = $nameIdentifierTypes[$index];
          $input['address_type'] = $addressTypes[$index];
          $input['street_name'] = $streetNames[$index];
          $input['building_number'] = $buildingNumbers[$index];
          $input['postcode'] = $postcodes[$index];
          $input['town_name'] = $townNames[$index];
          $input['country_sub_division'] = $countrySubDivisions[$index];
          $input['country'] = $countrys[$index];
          $input['national_identifier'] = $nationalIdentifiers[$index];
          $input['national_identifier_type'] = $nationalIdentifierTypes[$index];
          $input['country_of_issue'] = $countryOfIssues[$index];
          $input['registration_authority'] = $registrationAuthoritys[$index];
          $input['date_of_birth'] = $dateOfBirths[$index];
          $input['place_of_birth'] = $placeOfBirths[$index];
          $input['country_of_residence'] = $countryOfResidences[$index];

          $tau = new TrustAnchorUser($input);
          $tau->save();

          $ta_user_id = $tau->id;
          $ta->trustAnchorUser()->save($tau);

          $prefname = $input['prefname'];
          $password = $input['prefname'];

          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-create-user?user_id='.$id.'&ta_user_id='.$ta_user_id.'&prefname='.$prefname.'&password='.$password;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_create_user');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_create_user: ' . $res->getStatusCode());
          }

          

          return response()->json([]);
      }

      public function ta_set_attestation(Request $request, $id)
      {
          Log::debug('ContractsController ta_set_attestation');

          $input = $request->all();
          Log::debug(print_r($input, true));

          $user = User::find($id);

          $attestation_type = $input['attestation_type'];
          $user_address = $input['user_address'];
          $ta_address = $input['ta_account']['account_address'];

          $jurisdiction = $input['jurisdiction'];
          $effective_time = $input['effective_time'];
          $expiry_time = $input['expiry_time'];
          $public_data = $input['public_data'];
          $documents_matrix_encrypted = $input['documents_matrix_encrypted'];
          $availability_address_encrypted = $input['availability_address_encrypted'];
          
          $url = $this->helper_url.'/ta-set-attestation?attestation_type='.$attestation_type.'&user_id='.$id.'&user_address='.$user_address.'&jurisdiction='.$jurisdiction.'&effective_time='.$effective_time.'&expiry_time='.$expiry_time.'&public_data='.$public_data.'&documents_matrix_encrypted='.$documents_matrix_encrypted.'&availability_address_encrypted='.$availability_address_encrypted.'&ta_address='.$ta_address;

          Log::debug($url);
          
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_set_attestation');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_set_attestation: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_get_balance(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_balance');

          $input = $request->all();
          $user = User::find($id);

          $account = $input['account'];
        
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-get-balance?user_id='.$id.'&account='.$account;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_get_balance');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_get_balance: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_set_unique_address(Request $request, $id)
      {
          Log::debug('ContractsController ta_set_unique_address');

          $input = $request->all();
          $user = User::find($id);

          $account = $input['account'];

          $url = $this->helper_url.'/ta-set-unique-address?user_id='.$id.'&account='.$account;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController tam_set_unique_address');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController tam_set_unique_address: ' . $res->getStatusCode());
          }
      
          return response()->json([]);
      }

      public function ta_get_discovery_layer_keys(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_discovery_layer_keys');

          $user = User::findOrFail($id);
          
          $keys = DiscoveryLayerKey::get(['id', 'key']);

            
          return response()->json($keys);
      } 

      public function ta_set_key_value_pair(Request $request, $id)
      {
          Log::debug('ContractsController ta_set_key_value_pair');

          $input = $request->all();
          $user = User::find($id);

          $account = $input['account'];
          $ta_key_name = $input['ta_key_name'];
          $ta_key_value = $input['ta_key_value'];

          $url = $this->helper_url.'/ta-set-key-value-pair?user_id='.$id.'&account='.$account.'&ta_key_name='.$ta_key_name.'&ta_key_value='.$ta_key_value;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_set_key_value_pair');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_set_key_value_pair: ' . $res->getStatusCode());
          }
      
          return response()->json([]);
      }

      public function ta_get_unique_address(Request $request, $id)
      {
          Log::debug('ContractsController tam_get_unique_address');

          $input = $request->all();

          $user = User::find($id);

          $from_account = $input['from_account'];
          $to_account = $input['to_account'];

          $url = $this->helper_url.'/ta-get-unique-address?user_id='.$id.'&from_account='.$from_account.'&to_account='.$to_account;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_get_unique_address');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_get_unique_address: ' . $res->getStatusCode());
          }
      
          return response()->json([]);
      }

      public function ta_request_tokens(Request $request, $id)
      {
          Log::debug('ContractsController ta_request_tokens');

          $input = $request->all();
          $user = User::find($id);

          $account = $input['account'];
          $amount = $input['amount'];
        
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-request-tokens?user_id='.$id.'&account='.$account.'&amount='.$amount;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_request_tokens');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_request_tokens: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_get_user_attestations(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_user_attestations');

          $input = $request->all();
          $user = User::find($id);

          $account = $input['account'];
        
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-get-user-attestations?user_id='.$id.'&account='.$account;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_get_user_attestations');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_get_user_attestations: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function ta_get_attestation_components_in_array(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_attestation_components_in_array');

          $input = $request->all();
          $user = User::find($id);

          $account = $input['account'];
          $index = $input['index'];
        
          Log::debug(print_r($input, true));
          $url = $this->helper_url.'/ta-get-attestation-components-in-array?user_id='.$id.'&account='.$account.'&index='.$index;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_get_attestation_components_in_array');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_get_attestation_components_in_array: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }
      
      public function ta_get_trust_anchors(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_trust_anchors');

          $user = User::findOrFail($id);
          
          $trust_anchors = TrustAnchor::where('user_id', $id)->get();
          Log::debug('trust_anchors');
          Log::debug($id);
          foreach($trust_anchors as $trust_anchor) {

            $attestations = TrustAnchorUserAttestation::where('trust_anchor_id', $trust_anchor->id)->count();
            $trust_anchor->attestations = $attestations;
          }
            
          return response()->json($trust_anchors);
      } 

      public function ta_get_trust_anchor_users(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_trust_anchor_users');

          $user = User::findOrFail($id);
          
          $input = $request->all();

          $list=array();

          $trust_anchors = TrustAnchor::where('user_id', $id)->get();

          foreach($trust_anchors as $trust_anchor) {
            Log::debug($trust_anchor->id);
            $users = TrustAnchorUser::where('trust_anchor_id', $trust_anchor->id)->get();
            foreach($users as $user) {
              $user->ta_prefname = $trust_anchor->ta_prefname;
              array_push($list, $user);
            }
          }

          return response()->json($list);
      }
      
      public function ta_assign_crypto_address(Request $request, $id)
      {
          Log::debug('ContractsController ta_assign_crypto_address');

          $user = User::findOrFail($id);
          
          $input = $request->all();
          
          $crypto_address = new TrustAnchorUserCryptoAddress($input);

          $crypto_address->save();

          return response()->json($crypto_address);
      }

      public function ta_get_user_wallet_addresses(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_user_wallet_addresses');

          $user = User::findOrFail($id);
          
          $trust_anchors = TrustAnchor::where('user_id', $id)->get();

          $list=array();

          foreach($trust_anchors as $trust_anchor) {
              Log::debug('trust_anchor');
              Log::debug($trust_anchor->account_address);
              $trust_anchor_users = TrustAnchorUser::where('trust_anchor_id', $trust_anchor->id)->get();

              foreach($trust_anchor_users as $trust_anchor_user) {
                  $addresses = CryptoWalletAddress::where('trust_anchor_user_id', $trust_anchor_user->id)->get();
                  foreach($addresses as $address) {
                    $object = array();
                    $object['user_prefname'] = $trust_anchor_user->prefname;
                    $object['user_wallet_address'] = $address->address;
                    $object['user_wallet_type'] = $address->cryptoWalletType->wallet_type;
                    array_push($list, $object);
                  }
              }
              
          }
          return response()->json($list);
      }

      public function ta_get_all_users(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_all_users');

          $user = User::findOrFail($id);
          
          $trust_anchors = TrustAnchor::where('user_id', $id)->get();

          $list=array();

          foreach($trust_anchors as $trust_anchor) {

              $users = TrustAnchorUser::where('trust_anchor_id', $trust_anchor->id)->get();

              foreach($users as $user) {
                $object = array();
                $object['ta_prefname'] = $trust_anchor->ta_prefname;
                $object['user_prefname'] = $user->prefname;
                array_push($list, $object);
              }
              
          }
          return response()->json($list);
      }

      public function ta_register_jurisdiction(Request $request, $id)
      {
          Log::debug('ContractsController ta_register_jurisdiction');

          $user = User::findOrFail($id);
          
          $input = $request->all();
          $input['user_id'] = $id;
          $account_address = $input['account_address'];
          $jurisdiction = $input['jurisdiction'];

          $url = $this->helper_url.'/ta-register-jurisdiction?user_id='.$id.'&account_address='.$account_address.'&jurisdiction='.$jurisdiction;
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_register_jurisdiction');
            Log::debug($response);
           
              
          } else {
              Log::error('ContractsController ta_register_jurisdiction: ' . $res->getStatusCode());
          }

          return response()->json();

      }

      public function ta_get_all_attestations(Request $request, $id)
      {
          Log::debug('ContractsController ta_get_all_attestations');

          $user = User::findOrFail($id);

          $attestations = TrustAnchorUserAttestation::all();

          return response()->json($attestations);
      }
}
