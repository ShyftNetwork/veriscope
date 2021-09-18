<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Log;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Storage;
use App\UserState;

trait Verifyable
{
    /**
     *
     */
    public function customer_register_verify($ip_address, UserState $line)
    {
        // connect to 4stop and register the customer
        $data = [
            'merchant_id' => config('4stop.merchantId'),
            'password'    => config('4stop.password'),
            'customer_information' => [
                'first_name'  => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name'   => $this->last_name,
                'email'       => $this->email,
                'address1'    => $this->address,
                'address2'    => '',
                'city'        => $this->city,
                'province'    => $this->state,
                'postal_code' => $this->zip,
                'country'     => getISO3166CountryCode($this->country),
                'phone1'      => formatPhone($this->telephone),
                'dob'         => Carbon::parse($this->dob)->format('Y-m-d'),
                'gender'      => substr($this->gender, 0, 1),
            ],
            'user_number'    => $this->id,
            'reg_date'       => Carbon::parse($this->created_at)->format('Y-m-d'),
            'reg_ip_address' => $ip_address,
            'pfc_type'       => config('4stop.pfcTypeRegistration'),
            'industry_type'  => config('4stop.industryType'), // hard coded to cyrto

        ];
        $line->update(['payload' => json_encode($data)]);
        // Log::info("Payload for Customer Registration:\n", [$data]);
        $correct_response = '{
            "id": "233",
            "status": 0,
            "description": "Success",
            "score": 98,
            "rec": "Approve",
            "rules_triggered": [
              {
                "name": "Multi-Accounting : IP shared to Chargeback reason",
                "score": "100.00",
                "display_to_merchant": 1
              }
            ],
            "scrubber_results": {
              "geo_check": "",
              "address_verification": "",
              "phone_verify": "",
              "idv_usa": "",
              "idv_global": "",
              "gav": "",
              "idv_br": "",
              "bav_usa": "",
              "bav_advanced": "",
              "cb_aml": "",
              "cb_bvs": "",
              "email_age": "",
              "compliance_watchlist": "",
              "iovation": "",
              "idv_advance": ""
            },
            "facts": [
              {
                "type": "1",
                "text": "Which one of the following addresses is associated with you?",
                "answers": [
                  {
                    "correct": "false",
                    "text": "509 BIRDIE RD"
                  },
                  {
                    "correct": "false",
                    "text": "667 ASHWOOD NORT CT"
                  },
                  {
                    "correct": "true",
                    "text": "291 LYNCH RD"
                  }
                ]
              },
              {
                "type": "2",
                "text": "Which one of the following area codes is associated with you?",
                "answers": [
                  {
                    "correct": "false",
                    "text": "901"
                  },
                  {
                    "correct": "true",
                    "text": "407/321"
                  },
                  {
                    "correct": "false",
                    "text": "352"
                  }
                ]
              }
            ],
            "confidence_level": 91.5
          }';
        // $this->addHistoryLine([
        //   'transition' => '4StopVerify',
        //   'to' => $this->stateIs(),
        //   'payload' => json_encode($data),
        // ]);
        $line->update(['response' => $correct_response]);

        return json_decode($correct_response);
        // TODO: process real connection to 4stop
        // return $this->__sendRequest('POST', 'customerregistration', $data);
    }

    /**
     *
     */
    public function __sendRequest($method, $resource, $payload)
    {
        $client = new Client(/*['handler' => $this->__getStack()]*/);
        $data = [
            'headers' => $this->__getHeaders($resource),
            'debug' => $this->__debug(),
        ];
        $resource = $this->__serviceUrl($resource);

        if (!empty($payload) && $method != 'GET') {
            $data['form_params'] = $payload;
        } elseif ($payload) {
            $resource .= '?' . http_build_query($payload);
        }
        try {
            $response = $client->request($method, $resource, $data);
            $code = $response->getStatusCode();
            if($code == 200) {
                $body = $response->getBody();
                // Log::info('4Stop body: ', [$body]);
                $stringBody = (string) $body;
                // Log::info('4Stop string body: ', [json_decode($stringBody) ]);
                $json = json_decode($stringBody);
                if(json_last_error() == JSON_ERROR_NONE) {
                  return $json;
                } else {
                  // return the json error
                  return ['status' => -1, 'message' => json_last_error_msg()];
                }
            } else {
                activity()->log('4Stop error code: '. $code . ', with reason: ' . $response->getReasonPhrase());

                Log::error('4Stop error code: '. $code . ', with reason: ' . $response->getReasonPhrase());
                return ['code' => $code, 'status' => -1, 'message' => $response->getReasonPhrase()];
            }
        } catch (RequestException $e) {
            logger( $e->getRequest() );
            if ( $e->hasResponse() ) {
                logger( $e->getResponse() );
            }
            return ['status' => -1, 'message' => 'RequestException'];
        }
    }

    /**
     *
     */
    public function __serviceUrl($resource)
    {
        if( $resource == 'customerregistration' ) {
            $serviceUrl = config('4stop.coreServicesUrl');
        }

        $serviceUrl .= '/' . $resource;

        return $serviceUrl;
    }

    /**
     *
     */
    public function __debug()
    {
        if (config('app.env') == 'local') {
            return fopen(storage_path('logs/4stop.log'), 'a+');
        }
        return false;
    }

    /**
     *
     */
    public function __getHeaders($resource)
    {
      $headers = [
        'Accept' => 'application/json',
      ];
      if($resource == 'customerregistration'){
        // sending registration data
        $headers['Content-Type'] = 'x-www-form-urlencoded';
      }
      return $headers;
    }

}
