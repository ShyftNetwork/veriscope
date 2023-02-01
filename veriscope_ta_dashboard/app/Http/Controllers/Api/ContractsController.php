<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Country;
use App\TrustAnchor;
use App\TrustAnchorUser;
use App\TrustAnchorUserAttestation;
use App\TrustAnchorUserCryptoAddress;
use App\TrustAnchorAssociationCrypto;
use App\CryptoWalletAddress;
use App\TrustAnchorExtraData;
use App\DiscoveryLayerKey;
use GuzzleHttp\Client;
use App\Http\Controllers\BlockchainAnalytics\BlockchainAnalyticsController;

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
        if ($res->getStatusCode() == 200) {
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

        if ($input['legal_person_name']) {
            $ta->legal_person_name = $input['legal_person_name'];
        }

        if ($input['legal_person_name_identifier_type']) {
            $ta->legal_person_name_identifier_type = $input['legal_person_name_identifier_type'];
        }

        if ($input['address_type']) {

            $ta->address_type = $input['address_type'];
        }

        if ($input['street_name']) {
            $ta->street_name = $input['street_name'];
        }

        if ($input['building_number']) {
            $ta->building_number = $input['building_number'];
        }

        if ($input['building_name']) {
            $ta->building_name = $input['building_name'];
        }

        if ($input['postcode']) {
            $ta->postcode = $input['postcode'];
        }

        if ($input['town_name']) {
            $ta->town_name = $input['town_name'];
        }

        if ($input['country_sub_division']) {
            $ta->country_sub_division = $input['country_sub_division'];
        }

        if ($input['country']) {
            $ta->country = $input['country'];
        }
          
        if ($input['department']) {
            $ta->department = $input['department'];
        }
        
        if ($input['sub_department']) {
            $ta->sub_department = $input['sub_department'];
        }

        if ($input['floor']) {
            $ta->floor = $input['floor'];
        }

        if ($input['room']) {
            $ta->room = $input['room'];
        }

        if ($input['town_location_name']) {
            $ta->town_location_name = $input['town_location_name'];
        }

        if ($input['district_name']) {
            $ta->district_name = $input['district_name'];
        }


        if ($input['address_line'])
          $ta->address_line = $input['address_line'];

        if ($input['postbox'])
          $ta->postbox = $input['postbox'];

        if ($input['customer_identification'])
          $ta->customer_identification = $input['customer_identification'];

        if ($input['national_identifier'])
          $ta->national_identifier = $input['national_identifier'];

        if ($input['national_identifier_type'])
          $ta->national_identifier_type = $input['national_identifier_type'];

        if ($input['country_of_registration'])
          $ta->country_of_registration = $input['country_of_registration'];


        $ta->save();

        return response()->json([]);
    }

    public function ta_is_verified(Request $request, $id)
    {
        Log::debug('ContractsController ta_is_verified');

        $input = $request->all();
        $user = User::find($id);

        if (count($input) == 0) {
            $input['account'] = 'noSelect';
        }

        $account = $input['account'];
        Log::debug(print_r($input, true));
        $url = $this->helper_url.'/ta-is-verified?user_id='.$id.'&account='.$account;
        $client = new Client();
        $res = $client->request('GET', $url);
        if ($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_is_verified');
            Log::debug($response);
        } else {
            Log::error('ContractsController ta_is_verified: ' . $res->getStatusCode());
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

        $prefname = $input['prefname'];
        $password = $input['password'];
        Log::debug(print_r($input, true));
        $url = $this->helper_url.'/ta-create-user?user_id='.$id.'&ta_user_id='.$ta_user_id.'&prefname='.$prefname.'&password='.$password;
        $client = new Client();
        $res = $client->request('GET', $url);
        if ($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_create_user');
            Log::debug($response);
        } else {
            Log::error('ContractsController ta_create_user: ' . $res->getStatusCode());
        }

        return response()->json([]);
    }

    public function ta_get_balance(Request $request, $id)
    {
        Log::debug('ContractsController ta_get_balance');

        $input = $request->all();
        $user = User::find($id);

        if (count($input) == 0) {
            $input['account'] = 'noSelect';
        }

        $account = $input['account'];

        Log::debug(print_r($input, true));
        $url = $this->helper_url.'/ta-get-balance?user_id='.$id.'&account='.$account;
        $client = new Client();
        $res = $client->request('GET', $url);
        if ($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_get_balance');
            Log::debug($response);
        } else {
            Log::error('ContractsController ta_get_balance: ' . $res->getStatusCode());
        }

        return response()->json([]);
    }

    public function ta_get_discovery_layer_keys(Request $request, $id)
    {
        Log::debug('ContractsController ta_get_discovery_layer_keys');
        
        $user = User::findOrFail($id);
  
        $keys = DiscoveryLayerKey::orderBy('key', 'ASC')->get(['id', 'key']);

        return response()->json($keys);
    }



    public function ta_get_trust_anchors(Request $request, $id)
    {
        Log::debug('ContractsController ta_get_trust_anchors');

        $user = User::findOrFail($id);

        $trust_anchors = TrustAnchor::where('user_id', $id)->get();
        Log::debug('trust_anchors');
        Log::debug($id);
        foreach ($trust_anchors as $trust_anchor) {
            $attestations = TrustAnchorUserAttestation::where('trust_anchor_id', $trust_anchor->id)->count();
            $trust_anchor->attestations = $attestations;
        }

        return response()->json($trust_anchors);
    }


    function ivms_data_check($value) {

        $result = 'None';

        if($value != '' && !empty($value)) {
          $result = $value;
        }
        return $result;
    }

    public function export_ivms_data(Request $request, $id)
    {
        Log::debug('ContractsController export_ivms_data');

        $user = User::findOrFail($id);
        $role = 'beneficiaryVASP';
        $mockData = "'beneficiary':{'beneficiaryPersons':[{'naturalPerson':{'name':{'nameIdentifier':[{'primaryIdentifier':'Felix','secondaryIdentifier':'Bailey','nameIdentifierType':'LEGL'}]},'geographicAddress':[{'addressType':'HOME','streetName':'Potential Street','townLocationName':'Brooklyn','districtName':'Brooklyn','buildingNumber':'123','buildingName':'Cheese Hut','postcode':'91361','townName':'Thousand Oaks','countrySubDivision':'California','country':'US'}],'customerIdentification':'0xA3a8C1C840A8C2049472065b2664E01E0e8A8b67','dateAndPlaceOfBirth':{'dateOfBirth':'1984-01-14','placeOfBirth':'Estonia'},'countryOfResidence':'CA'}}],'accountNumber':['0xb532cCA105f966a76C3826451818b55fB2190933']},";

        $trust_anchors = TrustAnchor::where('user_id', $id)->first()->toArray();

        foreach($trust_anchors as $index => $value){

          switch($index){

            case 'legal_person_name':

              $legalPersonName = $this->ivms_data_check($value);

            break;

            case 'legal_person_name_identifier_type':

              $legalPersonNameIdentifierType = $this->ivms_data_check($value);

            break;

            case 'address_type':

              $addressType = $this->ivms_data_check($value);

            break;

            case 'department':

              $department = $this->ivms_data_check($value);

            break;

            case 'sub_department':

              $subDepartment = $this->ivms_data_check($value);

            break;

            case 'street_name':

              $streetName = $this->ivms_data_check($value);

            break;

            case 'building_number':

              $buildingNumber = $this->ivms_data_check($value);

            break;

            case 'building_name':

              $buildingName = $this->ivms_data_check($value);

            break;

            case 'floor':

              $floor = $this->ivms_data_check($value);

            break;

            case 'room':

              $room = $this->ivms_data_check($value);

            break;

            case 'postcode':

              $postcode = $this->ivms_data_check($value);

            break;

            case 'town_location_name':

              $townLocationName = $this->ivms_data_check($value);

            break;

            case 'district_name':

              $districtName = $this->ivms_data_check($value);

            break;

            case 'country_sub_division':

              $countrySubDivision = $this->ivms_data_check($value);

            break;

            case 'address_line':

              $addressLine = $this->ivms_data_check($value);

            break;

            case 'country':

              $country = $this->ivms_data_check($value);

            break;

            case 'town_name':

              $townName = $this->ivms_data_check($value);

            break;

            case 'postbox':

              $postbox = $this->ivms_data_check($value);

            break;

            case 'customer_identification':

              $customerIdentification = $this->ivms_data_check($value);

            break;

            case 'national_identifier':

              $nationalIdentifier = $this->ivms_data_check($value);

            break;

            case 'national_identifier_type':

              $nationalIdentifierType = $this->ivms_data_check($value);

            break;

            case 'country_of_registration':

              $countryOfRegistration = $this->ivms_data_check($value);

            break;
          }
        }

        if($request->type == 'oVASP') {
          $role = 'originatingVASP';
          $mockData = "'originator':{'originatorPersons':[{'naturalPerson':{'name':{'nameIdentifier':[{'primaryIdentifier':'Dora','secondaryIdentifier':'Carlson','nameIdentifierType':'LEGL'}]},'geographicAddress':[{'addressType':'HOME','streetName':'Potential Street','townLocationName':'Brooklyn','districtName':'Brooklyn','buildingNumber':'123','buildingName':'Cheese Hut','postcode':'91361','townName':'Thousand Oaks','countrySubDivision':'California','country':'US'}],'nationalIdentification':{'nationalIdentifier':'024181096','nationalIdentifierType':'RAID','registrationAuthority':'RA000589'},'customerIdentification':'0xA3a8C1C840A8C2049472065b2664E01E0e8A8b67','dateAndPlaceOfBirth':{'dateOfBirth':'1986-11-21','placeOfBirth':'New York City'},'countryOfResidence':'US'}}],'accountNumber':['0xDF122a5c1d5ddE991E2FDC5a5743B30F2a34EA6e']},";
        }

        $data = "{" . $mockData . "'$role':{'legalPerson':{'name':{'nameIdentifier':[{'legalPersonName':'$legalPersonName','legalPersonNameIdentifierType':'$legalPersonNameIdentifierType'}]},'geographicAddress':[{'addressType':'$addressType','department':'$department','subDepartment':'$subDepartment','streetName':'$streetName','buildingNumber':'$buildingNumber','buildingName':'$buildingName','floor':'$floor','room':'$room','postBox':'$postbox','postcode':'$postcode','townName':'$townName','townLocationName':'$townLocationName','districtName':'$districtName','countrySubDivision':'$countrySubDivision','addressLine':'$addressLine','country':'$country'}],'customerIdentification':'$customerIdentification','nationalIdentification':{'nationalIdentifier':'$nationalIdentifier','nationalIdentifierType':'$nationalIdentifierType'},'countryOfRegistration':'$countryOfRegistration'}}}";
        $data = str_replace("'", '"', $data);

        Log::debug('ContractsController export_ivms_data id ' . $id);

        return response()->json($data);
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
      if ($res->getStatusCode() == 200) {
          $response = json_decode($res->getBody());
          Log::debug('ContractsController ta_set_key_value_pair');
          Log::debug($response);
      } else {
          Log::error('ContractsController ta_set_key_value_pair: ' . $res->getStatusCode());
      }

      return response()->json([]);
    }

    public function ta_get_trust_anchor_users(Request $request, $id)
    {
        Log::debug('ContractsController ta_get_trust_anchor_users');

        $user = User::findOrFail($id);

        $input = $request->all();

        $list=array();

        $trust_anchors = TrustAnchor::where('user_id', $id)->get();

        foreach ($trust_anchors as $trust_anchor) {
            Log::debug($trust_anchor->id);
            $users = TrustAnchorUser::where('trust_anchor_id', $trust_anchor->id)->get();
            foreach ($users as $user) {
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

        foreach ($trust_anchors as $trust_anchor) {
            Log::debug('trust_anchor');
            Log::debug($trust_anchor->account_address);
            $trust_anchor_users = TrustAnchorUser::where('trust_anchor_id', $trust_anchor->id)->get();

            foreach ($trust_anchor_users as $trust_anchor_user) {
                $addresses = CryptoWalletAddress::where('trust_anchor_user_id', $trust_anchor_user->id)->get();
                foreach ($addresses as $address) {
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

        foreach ($trust_anchors as $trust_anchor) {
            $users = TrustAnchorUser::where('trust_anchor_id', $trust_anchor->id)->get();

            foreach ($users as $user) {
                $object = array();
                $object['ta_prefname'] = $trust_anchor->ta_prefname;
                $object['user_prefname'] = $user->prefname;
                array_push($list, $object);
            }
        }
        return response()->json($list);
    }

    public function ta_get_all_attestations(Request $request, $id)
    {
        Log::debug('ContractsController ta_get_all_attestations');

        $user = User::findOrFail($id);

        $attestations = TrustAnchorUserAttestation::all();

        return response()->json($attestations);
    }

}
