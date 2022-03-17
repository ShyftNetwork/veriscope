## Description

[Laravel Horizon](https://github.com/laravel/horizon) provides a beautiful dashboard and code-driven configuration for your Laravel powered [Redis queues](https://laravel.com/docs/8.x/queues) and allows you to easily monitor key metrics of your queue system such as job throughput, runtime, and job failures.

![Alt text](screenshots/screen-2.png "Horizon Dashboard")

### Problem

When VASPs post attestations to the Shyft Blockchain in Step 1 these attestations arrive to all connected VASPs in Step 2 as events.

![Alt text](screenshots/Shyft-Horizon-Flow-1.png "Horizon Flow")

Attestations in Step 2.1 are sent over to the Exchange via a custom webhook that is configured below.

As you can imagine there will be millions of attestations in the Shyft Blockchain, one for every crypto withdrawal from each Exchange.  Therefore Veriscope uses a Horizon/Redis queuing framework to post these attestations as they arrive directly to the exchange.

When the exchange receives the attestation, it can confirm if the crypto address (in the attestation) is a deposit address on their exchange.  If so, it can proceed by preparing the KYC Template (Step 3).

Below describes the setup and configuration for Horizon.

## Update Instructions

```bash
cd /opt/veriscope
$ sudo scripts/setup-vasp.sh 
+ Located in /opt/veriscope/
+ Service user will be forge


1) Refresh dependencies
2) Install/update nethermind
3) Set up new postgres user
4) Obtain/renew SSL certificate
5) Install/update NGINX
6) Install/update node.js web service
7) Install/update PHP web service
8) Update static node list for nethermind
9) Create admin user
10) Regenerate webhook secret
11) Regenerate oauth secret (passport)
12) Regenerate encrypt secret (EloquentEncryption)
13) Install Redis server
14) Install Passport Client Environment Variables
15) Install Horizon
i) Install Everything
p) show daemon status
w) restart all services
r) reboot
q) quit
Choose what to do: 15

```

Run Step 15 above.

## Webhook configuration for Horizon

Log into the backoffice and choose Constants.

![Alt text](screenshots/constants.png "Constants")

When in constants (Portal Settings) add your custom Webhook URL and Webhook Secret.

Choose Update to save the Webhook URL and Secret.

**Note:** Webhook URL should be the Exchange host API service to receive attestations (Step 2.1 and Step 2.2 above).

![Alt text](screenshots/screen-3.png "Settings")

## Webhook Jobs

When Veriscope receives an attestation event (Step 2.1) it will add the payload to Horizon and post it to the Webhook URL.

To view Completed Jobs, navigate to the Horizon Dashboard. 

![Alt text](screenshots/screen-1.png "Settings")

Choose Completed Jobs.

![Alt text](screenshots/dashboard.png "Settings")

Successful Webhook jobs are called "CallWebhookJob".  To view the job details choose "CallWebhookJob".

![Alt text](screenshots/webhook.png "Settings")

Displayed are the details of the Webhook Post.

![Alt text](screenshots/details.png "Settings")

Below is an example of the payload sent to the Webhook URL.

```
{
"webhookUrl": "https://[YOUR WEBHOOK URL HERE]",
"httpVerb": "post",
"tries": 3,
"requestTimeout": 3,
"backoffStrategyClass": "Spatie\WebhookServer\BackoffStrategy\ExponentialBackoffStrategy",
"signerClass": null,
"headers": {
"Content-Type": "application/json",
"Signature": "[YOUR SIGNATURE HERE]"
},
"verifySsl": true,
"throwExceptionOnFailure": false,
"queue": "default",
"payload": {
	"ta_account": "0x1bD8d3A9AF399Bfdcd17e45DA27c13C05ef64892",
	"jurisdiction": "1",
	"effective_time": "1615913522",
	"expiry_time": "1679158322",
	"is_managed": "1",
	"attestation_hash": "0xdfd0a3b7766bc7f9ae5af62ba814a6e9e860e4da6bca90fc31865b50c8f79e5f",
	"transaction_hash": "0x3a6890fae7782203c0b2e66385e1bc304da0c698251ebda913b4ec5230adce6b",
	"user_account": "0xDAe1743aE4F79DdEa582C3BF2CdF582F1514e365",
	"public_data": "0x313233",
	"public_data_decoded": "123",
	"documents_matrix_encrypted": "0x3738396338356366346230656333323030633435643162346464343865343331303362306639393964353634316635356637356563346364393833306530303933646539363031666337663639636662666137383366663439663837666564616437616666333262353134363063393264366164656233363139313632346362613833353438393164313733393033613566326434383933653132353438613737336261313432393133343834323439343636346132313039353638343432373963636639393434656635313530313434353531313434353531313434353531313463353530306363356565386435303063633535303063633564613561643761366532373330393962386366376239666537346463376637663665623733353432",
	"documents_matrix_encrypted_decoded": "789c85cf4b0ec3200c45d1b4dd48e43103b0f999d5641f55f75ec4cd9830e0093de9601fc7f69cfbfa783ff49f87fedad7aff32b51460c92d6adeb36191624cba8354891d173903a5f2d4893e12548a773ba1429134842494664a210956844279ccf9944ef515014455114455114455114c5500cc5ee8d500cc5500cc5da5ad7a6e273099b8cf7b9fe74dc7f7f6eb73542",
	"availability_address_encrypted": "0x2020202020202020202020202020202020202020202020202020202020202020",
	"availability_address_encrypted_decoded": null,
	"version_code": "3",
	"coin_blockchain": "ETH",
	"coin_token": "USDC",
	"coin_address": "0x6ec88a2cb932eb46dfda0280c0eadb93b6eca13b",
	"coin_memo": "123"
},
"meta": [
],
"tags": [
],
"uuid": "f600f17e-c70c-403e-8235-0497e4168171",
"response": null,
"errorType": null,
"errorMessage": null,
"transferStats": null,
"job": null,
"connection": null,
"chainConnection": null,
"chainQueue": null,
"chainCatchCallbacks": null,
"delay": null,
"afterCommit": null,
"middleware": [
],
"chained": [
]
}
```

Note the **[YOUR WEBHOOK URL HERE]** and **[YOUR SIGNATURE HERE]**.

"coin_address": "0x6ec88a2cb932eb46dfda0280c0eadb93b6eca13b" is the crypto address to store and query against deposit addresses in the Exchange.

Signature is the header param you can use to verify the webhook payload originated from Veriscope.

# Signed Signature Verification
We add a header called Signature that will contain a signature the receiving app can use the payload hasn't been tampered with. This is how that signature is calculated:

```php
// payload is the array passed to the `payload` method of the webhook
// secret is the string given to the `signUsingSecret` method on the webhook.
$payloadJson = json_encode($payload);

$signature = hash_hmac('sha256', $payloadJson, $secret);
```



