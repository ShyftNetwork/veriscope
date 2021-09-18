<?php

namespace App\Shyft;
use Illuminate\Support\Facades\Facade;
use GuzzleHttp\Client;

class SendGrid extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'sendgrid'; }

    public static function unsubscribe($email = 'bryan@paycase.com') {
      $token = config('services.sendgrid.api_key');
      $url = "https://api.sendgrid.com/v3/asm/suppressions/global/" . $email;
      $client = new Client();
      $headers = [
          'Authorization' => 'Bearer ' . $token,
          'Accept'        => 'application/json',
      ];
      $res = $client->request('GET', $url, [
        'headers' => $headers
      ]);
      if($res->getStatusCode() == 200) {
          $response = json_decode($res->getBody());
          //dd($response);
      } else {
          Log::error('Block Cypher status code: ' . $res->getStatusCode());
      }
    }
}
