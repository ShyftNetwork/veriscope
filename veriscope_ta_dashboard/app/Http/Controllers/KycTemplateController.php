<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\{KycTemplate,KycTemplateState,KycAttestation,SmartContractAttestation, TrustAnchor, TrustAnchorUser, CryptoWalletAddress, TrustAnchorExtraDataUnique};
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->helper_url = env('SHYFT_TEMPLATE_HELPER_URL');
        $this->messageJSON = "VERISCOPE";
    }

    public function ivmsSchema($ta_id, $tau_id, $type) {

        Log::debug('KycTemplateController ivmsSchema '.$tau_id." ".$type);
        $contents = Storage::disk('local')->get('ivms101-template.json');
        $ivms_template = json_decode($contents, true);

        $tau = TrustAnchorUser::where('id', $tau_id)->firstOrFail();
        $ta = TrustAnchor::where('id', $ta_id)->firstOrFail();

        if ($type == 'ORIGINATOR') {
            $key = 'originator';
            $vasp_key = 'originating';

        }
        else {
            $key = 'beneficiary';
            $vasp_key = 'beneficiary';
        }

        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['name']['nameIdentifier'][0]['primaryIdentifier'] = $tau->primary_identifier;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['name']['nameIdentifier'][0]['secondaryIdentifier'] = $tau->secondary_identifier;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['name']['nameIdentifier'][0]['nameIdentifierType'] = $tau->name_identifier_type;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['addressType'] = $tau->address_type;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['streetName'] = $tau->street_name;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['buildingNumber'] = $tau->building_number;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['buildingName'] = $tau->building_name;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['postcode'] = $tau->postcode;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['townName'] = $tau->town_name;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['countrySubDivision'] = $tau->country_sub_division;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['geographicAddress'][0]['country'] = $tau->country;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['nationalIdentification']['nationalIdentifier'] = $tau->national_identifier;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['nationalIdentification']['nationalIdentifierType'] = $tau->national_identifier_type;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['nationalIdentification']['countryOfIssue'] = $tau->country_of_issue;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['nationalIdentification']['registrationAuthority'] = $tau->registration_authority;

        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['customerIdentification'] = $tau->account_address;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['dateAndPlaceOfBirth']['dateOfBirth'] = $tau->date_of_birth;
        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['dateAndPlaceOfBirth']['placeOfBirth'] = $tau->place_of_birth;

        $ivms_template[$key][$key.'Persons'][0]['naturalPerson']['countryOfResidence'] = $tau->country_of_residence;

        $ivms_template[$vasp_key.'VASP']['legalPerson']['name']['nameIdentifier'][0]['legalPersonName'] = $ta->legal_person_name;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['name']['nameIdentifier'][0]['legalPersonNameIdentifierType'] = $ta->legal_person_name_identifier_type;

        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['addressType'] = $ta->address_type;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['streetName'] = $ta->street_name;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['buildingNumber'] = $ta->building_number;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['buildingName'] = $ta->building_name;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['postcode'] = $ta->postcode;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['townName'] = $ta->town_name;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['countrySubDivision'] = $ta->country_sub_division;
        $ivms_template[$vasp_key.'VASP']['legalPerson']['geographicAddress'][0]['country'] = $ta->country;

        $ivms_template[$vasp_key.'VASP']['legalPerson']['customerIdentification'] = $ta->account_address;


        Log::debug(json_encode($ivms_template));
        return $ivms_template;
    }

    public function isTASenderOrBeneficiary($kycTemplate) {

        $ta = TrustAnchor::where('account_address','=', json_decode($kycTemplate)->BeneficiaryTAAddress)
            ->orWhere('account_address','=', json_decode($kycTemplate)->SenderTAAddress)->first();

        if($ta->account_address == json_decode($kycTemplate)->BeneficiaryTAAddress) {
            return 'BENEFICIARY';
        }
        else if($ta->account_address == json_decode($kycTemplate)->SenderTAAddress) {
            return 'ORIGINATOR';
        }
        return 'NONE';
    }

    public function buildKycTemplate($kt) {
        $kycTemplate = array("AttestationHash"=>$kt->attestation_hash,
                        "BeneficiaryTAAddress"=>$kt->beneficiary_ta_address,
                        "BeneficiaryTAPublicKey"=>$kt->beneficiary_ta_public_key,
                        "BeneficiaryUserAddress"=>$kt->beneficiary_user_address,
                        "BeneficiaryUserPublicKey"=>$kt->beneficiary_user_public_key,
                        "BeneficiaryTASignatureHash"=>$kt->beneficiary_ta_signature_hash,
                        "BeneficiaryTASignature"=>json_decode($kt->beneficiary_ta_signature),
                        "BeneficiaryUserSignatureHash"=>$kt->beneficiary_user_signature_hash,
                        "BeneficiaryUserSignature"=>json_decode($kt->beneficiary_user_signature),
                        "CryptoAddressType"=>$kt->crypto_address_type,
                        "CryptoAddress"=>$kt->crypto_address,
                        "CryptoPublicKey"=>$kt->crypto_public_key,
                        "CryptoSignatureHash"=>$kt->crypto_signature_hash,
                        "CryptoSignature"=>json_decode($kt->crypto_signature),
                        "SenderTAAddress"=>$kt->sender_ta_address,
                        "SenderTAPublicKey"=>$kt->sender_ta_public_key,
                        "SenderUserAddress"=>$kt->sender_user_address,
                        "SenderUserPublicKey"=>$kt->sender_user_public_key,
                        "SenderTASignatureHash"=>$kt->sender_ta_signature_hash,
                        "SenderTASignature"=>json_decode($kt->sender_ta_signature),
                        "SenderUserSignatureHash"=>$kt->sender_user_signature_hash,
                        "SenderUserSignature"=>json_decode($kt->sender_user_signature),
                        "BeneficiaryKYC"=>$kt->beneficiary_kyc,
                        "SenderKYC"=>$kt->sender_kyc,
                        "BeneficiaryTAUrl"=>$kt->beneficiary_ta_url,
                        "SenderTAUrl"=>$kt->sender_ta_url
                    );
        return json_encode($kycTemplate);
    }

    public function updateKycTemplate($kycTemplateJSON) {

        $kycTemplateDecode = json_decode($kycTemplateJSON);

        $kt = KycTemplate::firstOrNew(['attestation_hash' => $kycTemplateDecode->AttestationHash]);

        $kt->attestation_hash = $kycTemplateDecode->AttestationHash;
        $kt->beneficiary_ta_address = $kycTemplateDecode->BeneficiaryTAAddress;
        $kt->beneficiary_ta_public_key = $kycTemplateDecode->BeneficiaryTAPublicKey;
        $kt->beneficiary_user_address = $kycTemplateDecode->BeneficiaryUserAddress;
        $kt->beneficiary_user_public_key = $kycTemplateDecode->BeneficiaryUserPublicKey;
        $kt->beneficiary_ta_signature_hash = $kycTemplateDecode->BeneficiaryTASignatureHash;
        $kt->beneficiary_ta_signature = json_encode($kycTemplateDecode->BeneficiaryTASignature);
        $kt->beneficiary_user_signature_hash = $kycTemplateDecode->BeneficiaryUserSignatureHash;
        $kt->beneficiary_user_signature = json_encode($kycTemplateDecode->BeneficiaryUserSignature);
        $kt->crypto_address_type = $kycTemplateDecode->CryptoAddressType;
        $kt->crypto_address = $kycTemplateDecode->CryptoAddress;
        $kt->crypto_public_key = $kycTemplateDecode->CryptoPublicKey;
        $kt->crypto_signature_hash = $kycTemplateDecode->CryptoSignatureHash;
        $kt->crypto_signature = json_encode($kycTemplateDecode->CryptoSignature);
        $kt->sender_ta_address = $kycTemplateDecode->SenderTAAddress;
        $kt->sender_ta_public_key = $kycTemplateDecode->SenderTAPublicKey;
        $kt->sender_user_address = $kycTemplateDecode->SenderUserAddress;
        $kt->sender_user_public_key = $kycTemplateDecode->SenderUserPublicKey;
        $kt->sender_ta_signature_hash = $kycTemplateDecode->SenderTASignatureHash;
        $kt->sender_ta_signature = json_encode($kycTemplateDecode->SenderTASignature);
        $kt->sender_user_signature_hash = $kycTemplateDecode->SenderUserSignatureHash;
        $kt->sender_user_signature = json_encode($kycTemplateDecode->SenderUserSignature);
        $kt->beneficiary_kyc = $kycTemplateDecode->BeneficiaryKYC;
        $kt->sender_kyc = $kycTemplateDecode->SenderKYC;
        $kt->beneficiary_ta_url = $kycTemplateDecode->BeneficiaryTAUrl;
        $kt->sender_ta_url = $kycTemplateDecode->SenderTAUrl;

        $kt->save();

        $kycTemplateJSON = $this->buildKycTemplate($kt);

        return $kycTemplateJSON;
    }

    public function updateKycTemplateForState($kycTemplate, $state) {

        $kts = KycTemplateState::where('state', $state)->firstOrFail();

        $kycTemplate->kyc_template_state_id = $kts->id;
        $kycTemplate->save();
    }

    public function postKycTemplate($kycTemplateJSON) {

        Log::debug('KycTemplateController postKycTemplate kycTemplateJSON');
        Log::debug(print_r($kycTemplateJSON, true));

        if($this->isTASenderOrBeneficiary($kycTemplateJSON) == 'BENEFICIARY') {
            $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', json_decode($kycTemplateJSON)->SenderTAAddress)->where('key_value_pair_name', 'API_URL')->firstOrFail();
        }
        else if($this->isTASenderOrBeneficiary($kycTemplateJSON) == 'ORIGINATOR') {
            $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', json_decode($kycTemplateJSON)->BeneficiaryTAAddress)->where('key_value_pair_name', 'API_URL')->firstOrFail();
        }

        $url = $taedu->key_value_pair_value;

        $client = new Client();
        $res = $client->request('POST', $url, [
            'json' => ['kycTemplate' => $kycTemplateJSON]
        ]);
        if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

            $response = $res->getBody();
            Log::debug('KycTemplateController postKycTemplate response');
            Log::debug($res->getBody());

        } else {
          Log::error('KycTemplateController postKycTemplate: ' . print_r($res, true));
        }
    }

    public function addUserPublicKey($kycTemplate, $ta) {

        if ($ta->account_address == $kycTemplate->beneficiary_ta_address) {
            $tau = TrustAnchorUser::where('account_address', $kycTemplate->beneficiary_user_address)->firstOrFail();
        }
        else if ($ta->account_address == $kycTemplate->sender_ta_address) {
            $tau = TrustAnchorUser::where('account_address', $kycTemplate->sender_user_address)->firstOrFail();
        }
        else {
            return;
        }


        if (is_null($tau->public_key)) {
                    $url = $this->helper_url.'/GetEthPublicKey';

            $client = new Client();
            $res = $client->request('POST', $url, [
                'json' => ['privateKey' => $tau->private_key]
            ]);
            if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

                $response = $res->getBody();
                $tau->public_key = json_decode($response)->publicKey;
                $tau->save();

            } else {
              Log::error('KycTemplateController addUserPublicKey: ' . print_r($res, true));
            }
        }

        if ($ta->account_address == $kycTemplate->beneficiary_ta_address) {
            $kycTemplate->beneficiary_user_public_key = $tau->public_key;
            $kycTemplate->save();
            $this->updateKycTemplateForState($kycTemplate, 'BENEFICIARY_USER_PUBLIC_KEY');
        }
        else if ($ta->account_address == $kycTemplate->sender_ta_address) {
            $kycTemplate->sender_user_public_key = $tau->public_key;
            $kycTemplate->save();
            $this->updateKycTemplateForState($kycTemplate, 'SENDER_USER_PUBLIC_KEY');
        }
    }

    public function addTAPublicKey($kycTemplate, $ta) {

        if (is_null($ta->public_key)) {
            $url = $this->helper_url.'/GetEthPublicKey';

            $client = new Client();
            $res = $client->request('POST', $url, [
                'json' => ['privateKey' => $ta->private_key]
            ]);
            if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

                $response = $res->getBody();
                $ta->public_key = json_decode($response)->publicKey;
                $ta->save();


            } else {
              Log::error('KycTemplateController addTAPublicKey: ' . print_r($res, true));
            }
        }
        if ($ta->account_address == $kycTemplate->beneficiary_ta_address) {
            $kycTemplate->beneficiary_ta_public_key = $ta->public_key;
            $this->updateKycTemplateForState($kycTemplate, 'BENEFICIARY_TA_PUBLIC_KEY');
        }
        else if ($ta->account_address == $kycTemplate->sender_ta_address) {
            $kycTemplate->sender_ta_public_key = $ta->public_key;
            $this->updateKycTemplateForState($kycTemplate, 'SENDER_TA_PUBLIC_KEY');
        }

        $kycTemplate->save();
    }

    public function addTASignature($kycTemplate, $ta) {

        $url = $this->helper_url.'/TASign';

        $client = new Client();
        $res = $client->request('POST', $url, [
            'json' => ['privateKey' => $ta->private_key, 'messageJSON' => $this->messageJSON."_TA"]
        ]);
        if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

            $response = $res->getBody();
            $ta->signature_hash = json_decode($response)->SignatureHash;
            $ta->signature = json_encode(json_decode($response)->Signature);
            $ta->save();


        } else {
          Log::error('KycTemplateController addTASignature: ' . print_r($res, true));
        }

        if ($ta->account_address == $kycTemplate->beneficiary_ta_address) {
            $kycTemplate->beneficiary_ta_signature_hash = $ta->signature_hash;
            $kycTemplate->beneficiary_ta_signature = $ta->signature;
            $kycTemplate->save();
            $this->updateKycTemplateForState($kycTemplate, 'BENEFICIARY_TA_SIGNATURE');

        }
        else if ($ta->account_address == $kycTemplate->sender_ta_address) {
            $kycTemplate->sender_ta_signature_hash = $ta->signature_hash;
            $kycTemplate->sender_ta_signature = $ta->signature;
            $kycTemplate->save();
            $this->updateKycTemplateForState($kycTemplate, 'SENDER_TA_SIGNATURE');
        }
    }

    public function addUserSignature($kycTemplate, $tau) {

        $url = $this->helper_url.'/TASign';

        $private_key = str_replace("0x", "", $tau->private_key);
        $client = new Client();
        $res = $client->request('POST', $url, [
            'json' => ['privateKey' => $private_key, 'messageJSON' => $this->messageJSON."_USER"]
        ]);
        if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

            $response = $res->getBody();
            $tau->signature_hash = json_decode($response)->SignatureHash;
            $tau->signature = json_encode(json_decode($response)->Signature);
            $tau->save();


        } else {
          Log::error('KycTemplateController addUserSignature: ' . print_r($res, true));
        }

        if ($tau->account_address == $kycTemplate->beneficiary_user_address) {
            $kycTemplate->beneficiary_user_signature_hash = $tau->signature_hash;
            $kycTemplate->beneficiary_user_signature = $tau->signature;
            $kycTemplate->save();
            $this->updateKycTemplateForState($kycTemplate, 'BENEFICIARY_USER_SIGNATURE');

        }
        else if ($tau->account_address == $kycTemplate->sender_user_address) {
            $kycTemplate->sender_user_signature_hash = $tau->signature_hash;
            $kycTemplate->sender_user_signature = $tau->signature;
            $kycTemplate->save();

            $this->updateKycTemplateForState($kycTemplate, 'SENDER_USER_SIGNATURE');
        }
    }

    public function recoverSignature($kycTemplateJSON, $type) {

        $url = $this->helper_url.'/TARecover';

        $client = new Client();
        $res = $client->request('POST', $url, [
            'json' => ['kycTemplate' => $kycTemplateJSON, 'type' => $type]
        ]);
        if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

            $response = $res->getBody();

        } else {
          Log::error('KycTemplateController recoverSignature: ' . print_r($res, true));
        }
    }

    public function attestation($attestation_hash) {

        $sca = SmartContractAttestation::where('attestation_hash', $attestation_hash)->firstOrFail();

        // Does the crypto type and address match a user account
        $crypto_type = $sca->availability_address_encrypted_decoded;
        $crypto_address = $sca->documents_matrix_encrypted_decoded;
        $cwa = CryptoWalletAddress::where('address', $crypto_address)->firstOrFail();

        $tau = TrustAnchorUser::where('id', $cwa->trust_anchor_user_id)->firstOrFail();

        $ta = TrustAnchor::where('id', $cwa->trust_anchor_id)->firstOrFail();

        $attestation = KycAttestation::firstOrCreate(['attestation_hash' => $attestation_hash]);
        $attestation->ta_account = $sca->ta_account;
        $attestation->user_account = $sca->user_account;
        $attestation->public_data_decoded = $sca->public_data_decoded;
        $attestation->documents_matrix_decoded = $sca->documents_matrix_encrypted_decoded;
        $attestation->availability_address_decoded = $sca->availability_address_encrypted_decoded;
        $attestation->save();

        #prepare the template
        $kt = KycTemplate::firstOrCreate(['attestation_hash' => $attestation_hash]);
        $kt->beneficiary_ta_address = $ta->account_address;
        $kt->beneficiary_user_address = $tau->account_address;
        $kt->crypto_address_type = $attestation->availability_address_decoded;
        $kt->crypto_address = $attestation->documents_matrix_decoded;
        $kt->sender_ta_address = $attestation->ta_account;
        $kt->sender_user_address = $sca->user_account;

        $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', $kt->beneficiary_ta_address)->where('key_value_pair_name', 'API_URL')->firstOrFail();
            $url = $taedu->key_value_pair_value;
        $kt->beneficiary_ta_url = $url;

        $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', $kt->sender_ta_address)->where('key_value_pair_name', 'API_URL')->firstOrFail();
            $url = $taedu->key_value_pair_value;
        $kt->sender_ta_url = $url;

        $kt->save();

        $this->updateKycTemplateForState($kt, 'ATTESTATION');

        $this->addTAPublicKey($kt, $ta);

        $this->addUserPublicKey($kt, $ta);

        $this->addTASignature($kt, $ta);

        $this->addUserSignature($kt, $tau);

        #create the template and send to sender ta ip

        $kycTemplateJSON = $this->buildKycTemplate($kt);

        $this->postKycTemplate($kycTemplateJSON);
    }

    public function encryptData($kt, $kycTemplateJSON) {
        #find user kyc and encrypt with other user public key

        if($this->isTASenderOrBeneficiary($kycTemplateJSON) == 'BENEFICIARY') {

            $tau = TrustAnchorUser::where('account_address', json_decode($kycTemplateJSON)->BeneficiaryUserAddress)->firstOrFail();

            $user_kyc = json_encode($this->ivmsSchema($tau->trust_anchor_id, $tau->id, "BENEFICIARY"));
            $public_key = json_decode($kycTemplateJSON)->SenderUserPublicKey;
            $kt->beneficiary_kyc_decrypt = $user_kyc;
            $kt->save();
        }
        else if($this->isTASenderOrBeneficiary($kycTemplateJSON) == 'ORIGINATOR') {
            
            $tau = TrustAnchorUser::where('account_address', json_decode($kycTemplateJSON)->SenderUserAddress)->firstOrFail();

            $user_kyc = json_encode($this->ivmsSchema($tau->trust_anchor_id, $tau->id, "ORIGINATOR"));

            $public_key = json_decode($kycTemplateJSON)->BeneficiaryUserPublicKey;
            $kt->sender_kyc_decrypt = $user_kyc;
            $kt->save();
        }
        else {
            Log::debug('KycTemplateController encryptData kycTemplateJSON');
            Log::debug("missing other User public key to encrypt the kyc data");
            Log::debug(print_r($kycTemplateJSON, true));
            return;
        }

        $url = $this->helper_url.'/EncryptData';

        $client = new Client();
        $res = $client->request('POST', $url, [
            'json' => ['publicKey' => $public_key, 'kycJSON' => $user_kyc]
        ]);
        if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

            $response = $res->getBody();

            $kycEncrypt = json_decode($response)->kycEncrypt;
            if($this->isTASenderOrBeneficiary($kycTemplateJSON) == 'BENEFICIARY'){
                $kt->beneficiary_kyc = $kycEncrypt;
                $this->updateKycTemplateForState($kt, 'BENEFICIARY_KYC');
            }
            else if($this->isTASenderOrBeneficiary($kycTemplateJSON) == 'ORIGINATOR'){
                $kt->sender_kyc = $kycEncrypt;
                $this->updateKycTemplateForState($kt, 'SENDER_KYC');
            }

            $kt->save();


        } else {
          Log::error('KycTemplateController encryptData: ' . print_r($res, true));
        }
    }

    public function decryptData($kt, $kycTemplate) {
        #find user kyc and encrypt with beneficiary user public key
        $is_sender_kyc = false;
        if($this->isTASenderOrBeneficiary($kycTemplate) == 'BENEFICIARY' && json_decode($kycTemplate)->SenderKYC) {
            $tau = TrustAnchorUser::where('account_address', json_decode($kycTemplate)->BeneficiaryUserAddress)->firstOrFail();
            $kyc_data = json_decode($kycTemplate)->SenderKYC;
            $is_sender_kyc = true;
        }
        else if ($this->isTASenderOrBeneficiary($kycTemplate) == 'ORIGINATOR' && json_decode($kycTemplate)->BeneficiaryKYC) {
            $tau = TrustAnchorUser::where('account_address', json_decode($kycTemplate)->SenderUserAddress)->firstOrFail();
            $kyc_data = json_decode($kycTemplate)->BeneficiaryKYC;
        }
        else {
            return;
        }

        $url = $this->helper_url.'/DecryptData';

        $client = new Client();
        $res = $client->request('POST', $url, [
            'json' => ['privateKey' => $tau->private_key, 'kycData' => $kyc_data]
        ]);
        if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

            $response = $res->getBody();
            $kycDecrypt = json_decode($response)->kycDecrypt;
            if($is_sender_kyc) {
                $kt->sender_kyc_decrypt = $kycDecrypt;
            }
            else {
                $kt->beneficiary_kyc_decrypt = $kycDecrypt;
            }
            $kt->save();

        } else {
          Log::error('KycTemplateController decryptData: ' . print_r($res, true));
        }
    }

    public function saveEncryptedKYCData($kt, $kycTemplate) {

        if (is_null($kt->beneficiary_kyc) && json_decode($kycTemplate)->BeneficiaryKYC) {
            $kt->beneficiary_kyc = json_decode($kycTemplate)->BeneficiaryKYC;
        }
        if (is_null($kt->sender_kyc) && json_decode($kycTemplate)->SenderKYC) {
            $kt->sender_kyc = json_decode($kycTemplate)->SenderKYC;
        }
        $kt->save();
    }

    public function kyc_template_request(Request $request) {
        $input = $request->all();
        $kycTemplateJSON = $input['kycTemplate'];

        Log::debug('KycTemplateController kyc_template_request kycTemplateJSON');
        Log::debug(print_r($kycTemplateJSON, true));

        $kycTemplateJSON = $this->updateKycTemplate($kycTemplateJSON);

        $kycTemplateDecode = json_decode($kycTemplateJSON);

        if($kycTemplateDecode->BeneficiaryTASignatureHash && $kycTemplateDecode->BeneficiaryTASignature) {
            $this->recoverSignature($kycTemplateJSON, 'BeneficiaryTA');
        }

        if($kycTemplateDecode->BeneficiaryUserSignatureHash && $kycTemplateDecode->BeneficiaryUserSignature) {
            $this->recoverSignature($kycTemplateJSON, 'BeneficiaryUser');
        }

        if($kycTemplateDecode->SenderTASignatureHash && $kycTemplateDecode->SenderTASignature) {
            $this->recoverSignature($kycTemplateJSON, 'SenderTA');
        }

        if($kycTemplateDecode->SenderUserSignatureHash && $kycTemplateDecode->SenderUserSignature) {
            $this->recoverSignature($kycTemplateJSON, 'SenderUser');
        }

        $kt = KycTemplate::where('attestation_hash', $kycTemplateDecode->AttestationHash)->firstOrFail();

        $ta = TrustAnchor::where('account_address','=', $kycTemplateDecode->BeneficiaryTAAddress)
            ->orWhere('account_address','=', $kycTemplateDecode->SenderTAAddress)->first();

        $this->addTAPublicKey($kt, $ta);

        $this->addUserPublicKey($kt, $ta);

        $this->addTASignature($kt, $ta);

        $tau = TrustAnchorUser::where('account_address','=', $kycTemplateDecode->BeneficiaryUserAddress)
            ->orWhere('account_address','=', $kycTemplateDecode->SenderUserAddress)->first();

        $this->addUserSignature($kt, $tau);

        $this->encryptData($kt, $kycTemplateJSON);

        $this->decryptData($kt, $kycTemplateJSON);

        $kycTemplateJSON = $this->buildKycTemplate($kt);
        Log::debug('KycTemplateController encryptData kycTemplateJSON');
        Log::debug(print_r($kycTemplateJSON, true));
        Log::debug('KycTemplateController encryptData kt');
        Log::debug(print_r($kt, true));
        return response()->json($kycTemplateJSON, 200);
    }


    public function kyc_template_response($attestation_hash){

        $kt = KycTemplate::where('attestation_hash', $attestation_hash)->firstOrFail();
        $kycTemplateJSON = $this->buildKycTemplate($kt);
        Log::debug('KycTemplateController kyc_template_response kycTemplateJSON');
        Log::debug(print_r($kycTemplateJSON, true));

        $this->postKycTemplate($kycTemplateJSON);

    }
}
