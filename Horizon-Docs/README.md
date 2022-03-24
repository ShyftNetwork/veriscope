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
$payloadJson = $payload;

$signature = hash_hmac('sha256', $payloadJson, $secret);
```
Sample code (php)
```
$payload = '{"ta_account":"0x1bD8d3A9AF399Bfdcd17e45DA27c13C05ef64892","jurisdiction":"1","effective_time":"1616519661","expiry_time":"1679764461","is_managed":"1","attestation_hash":"0x2b9094e7dd7ab75ca33c061491e3a67abd0442c1513e78ddbaeba9796ba53d07","transaction_hash":"0x40e6cb3187442af3f9c6b0b1715ebdd25a2e0fc1e111b8d9d9b4da5889de149d","user_account":"0xB7391F81113F7CaBca9FCbb90Bb7db4EE8Fe576a","public_data":"0x686f72697a6f6e","public_data_decoded":"horizon","documents_matrix_encrypted":"0x373839633835643134623732383333303130303435303963356363343335366232643938396632343734396164636333653536626535376365393731353339353164373834316162653865313231653436646262666333646166656265646562613666666265653937666165656263376566346237363539376231336664356365643733373535396465323436346635613334396361396164316134636231616436363463383361623263396334346463343831633061343832653831333039613430663234313937353436333039326431313938333331313930376466636561646438623931373261343663356138313831356133363235343863386135313731326134656335636634666132653235343963386135333731323835363039343630336662373734306638653430306134646162313030363535313062363064613331316135653135346532303030343631643536643638643561663439616335363163346138643931613031366235393066383164353839326130623336353430393538303764653938383063646562623430313537303137363430346262303565376461393762326461303636626566666666633133666465653235316231","documents_matrix_encrypted_decoded":"789c85d14b7283301004509c5cc4356b2d989f24749adcc3e56be57ce97153951d7841abe8e121e46dbbfc3dafebedeba6ffbee97faeebc7ef4b76597b13fd5ced737559de2464f5a349ca9ad1a4cb1ad664c83ab2c9c44dc481c0a482e81309a40f241975463092d11983311907dfceadd8b9172a46c5a81815a362548c8a51712a4ec5cf4fa2e2549c8a53712856094603fb7740f8e400a4dab10065510b60da311a5e154e2000461d56d68d5af49ac561c4a8d91a016b590f81d5892a0b3654095807de9880cdebb401570176404bb05e7da97b2da066beffffc13fdee251b1","availability_address_encrypted":"0x2020202020202020202020202020202020202020202020202020202020202020","availability_address_encrypted_decoded":null,"version_code":"3","coin_blockchain":"ETH","coin_token":"USDC","coin_address":"0x930474f6a0732b71f8a5fabc7db1ef054925cf37","coin_memo":"horizon"}';
$secret= "super secret";
$signature = hash_hmac("sha256", $payload, $secret);
echo $signature;
// 9c7a2858b452cdd10bbc36453e6c95d0c8841112606d1092131469596b363571
//headers

Content-Length: 2170
Content-Type: application/json
Signature: 9c7a2858b452cdd10bbc36453e6c95d0c8841112606d1092131469596b363571

```

```
```java
import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.security.NoSuchAlgorithmException;
import java.security.InvalidKeyException;
import javax.xml.bind.DatatypeConverter;

class Main {
  public static void main(String[] args) {
  	try {
	    String key = "the shared secret key here";
	    String message = "[JSON PAYLOAD]";
	    
	    Mac hasher = Mac.getInstance("HmacSHA256");
	    hasher.init(new SecretKeySpec(key.getBytes(), "HmacSHA256"));
	    
	    byte[] hash = hasher.doFinal(message.getBytes());
	    
	    // to lowercase hexits
	    DatatypeConverter.printHexBinary(hash);
  	}
  	catch (NoSuchAlgorithmException e) {}
  	catch (InvalidKeyException e) {}
  }
}
```



