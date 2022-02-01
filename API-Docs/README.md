## How to create and API Access Token


#### 1. Sign in

Enter your Email address and Password to login.

<img src="screenshots/signin.png" alt="signin" style="zoom:25%;" />



#### 2. Backoffice

Choose the Hamburger menu on the right side of the screen and select Backoffice.

<img src="screenshots/hb1.png" alt="hb1" style="zoom:25%;" />

<img src="screenshots/backoffice.png" alt="backoffice" style="zoom:25%;" />

#### 3. API tokens

Choose Hamburger menu again and select API Tokens.

<img src="screenshots/hb2.png" alt="hb2" style="zoom:25%;" />

<img src="screenshots/apitoken.png" alt="apitoken" style="zoom:25%;" />

#### 4. Create tokens

Choose Create Token button on the left side of the page to create a new token.

<img src="screenshots/createtoken.png" alt="createtoken" style="zoom:25%;" />

#### 5. Copy tokens

Once you have created a token you can choose Copy Token to save the token in your browser clipboard.  You will use the token in the clipboard as the token in the Postman Collection below.

<img src="screenshots/copytoken.png" alt="copytoken" style="zoom:25%;" />


## How to use Veriscope Postman API

[Postman](https://getpostman.com/) is an API Collaboration Platform.

Veriscope now offers several Postman Collections and Environments (JSON files) for a quicker and easier usage of our RESTful APIs.
You only need to import and set up with your own API and secret keys to begin.

#### How to import and configure

- Download the `veriscope-api-postman` repository.

- Click the `Import` button. On Postman for Mac, for example, the button is at the top left:

  <img src="screenshots/import.png" alt="import" style="zoom:33%;" />

- On the `Import` pop-up page, select the `Folder` tab. Click the `Choose folder from your computer` button and choose the root folder of the downloaded repository.

  <img src="screenshots/folder.png" alt="folder" style="zoom:33%;" />

- Select which collections and environments you would like to import and click the `Import` button.

  <img src="screenshots/selectfile.png" alt="selectfile" style="zoom:33%;" />

- Select the `Environments` tab on the left, choose an environment, and set your Base  URL and Token by changing the `Current Value` column (see screenshot);

  <img src="screenshots/environment.png" alt="environment" style="zoom:33%;" />

- Select your newly-added environment from the environment dropdown menu. On Mac, this is at top right, to the left of the eye icon.

  <img src="screenshots/dropdown.png" alt="dropdown" style="zoom:33%;" />

## Postman safety practices

The following practices are advised to secure your account's safety:

- Don't use Collections obtained from an unknown source.
- Review the environment JSON file before its usage.
- Don't use any code that you don't understand.
- Make sure that the withdrawal permission **is not enabled** for your API keys.

## FAQ

**Q:** Why I can't get any response?

You haven't imported the environment file or you've imported it but haven't selected it from the dropdown menu

**Q:** How can I debug a request or find the used URL?

- Open the Postman's console to find requests' parameters and URL.


## API Examples

## Create Shyft User
```
POST https://vs-node-1.veriscope.network/api/v1/server/create_shyft_user
```
Response
```
{
    "address": "0x231aaef9a7665622f825bc7f613c861d31f14844",
    "privateKey": "a3d4406fd7846a256f7f57c4aa758f7fa356ca514229810278cabe52747021d1",
    "publicKey": "9134f44795a92e87872d2acc85c14baa624bccac65ef273d30182279025553f4a891613a537e633088f14a9612d10fc4b786bbc71b9f925d97c58d427086ecf5"
}
```
**Note:** use "address": "0x231aaef9a7665622f825bc7f613c861d31f14844" to represent your Shyft User ID (user_address) in Set Attestation example below.

## Get Jurisdictions
```
GET https://vs-node-1.veriscope.network/api/v1/server/get_jurisdictions
```
Response
```
[
    {
        "id": 1,
        "sortname": "AF",
        "name": "Afghanistan",
        "created_at": "2022-01-24T20:39:37.000000Z",
        "updated_at": "2022-01-24T20:39:37.000000Z"
    },
    {
        "id": 2,
        "sortname": "AL",
        "name": "Albania",
        "created_at": "2022-01-24T20:39:37.000000Z",
        "updated_at": "2022-01-24T20:39:37.000000Z"
    },...
    {
        "id": 246,
        "sortname": "ZW",
        "name": "Zimbabwe",
        "created_at": "2022-01-24T20:39:37.000000Z",
        "updated_at": "2022-01-24T20:39:37.000000Z"
    }
]

```
**Note:** "id": 1 as jurisdiction in Set Attestation example below.
## Set Attestation
Params:
ta_address use your TA account in Manage Organization
user_address from example above or by creating a random user in Manage Users
documents_matrix_encrypted is the crypto withdrawal address from your exchange
availability_address_encrypted is the blockchain (eg BTC, ETH)
```
POST https://vs-node-1.veriscope.network/api/v1/server/set_attestation
```
Request Body
```
jurisdiction: "1"
attestation_type: "WALLET"
user_address: "0x1Ba96127AFa2B9FDE25E0Afd92A1EBAe98e3344A"
public_data: "WALLET"
documents_matrix_encrypted: "0x447832bc6303C87A7C7C0E3894a5C6848Aa24877"
availability_address_encrypted: "ETH"
ta_address: "0x3B5D04F55946690873A7E05Cb9E5A6f5363774cB"
```

## Get Verified Trust Anchors
```
GET https://vs-node-1.veriscope.network/api/v1/server/get_verified_trust_anchors
```
Response
```
[
    {
        "id": 38,
        "account_address": "0x10a32419ABcfbaaae91D0e03bc8390c418E65680",
        "created_at": "2022-01-24T21:15:31.000000Z",
        "updated_at": "2022-01-24T21:15:31.000000Z"
    },...
    {
        "id": 50,
        "account_address": "0xee98c82d995E65B96A570fA50213B0Ce558Cf7F9",
        "created_at": "2022-01-24T21:15:31.000000Z",
        "updated_at": "2022-01-24T21:15:31.000000Z"
    }
]
```

## Is Trust Anchor Verified
```
GET https://vs-node-1.veriscope.network/api/v1/server/verify_trust_anchor/0x10a32419ABcfbaaae91D0e03bc8390c418E65680
```
Response
```
{
    "address": "0x10a32419ABcfbaaae91D0e03bc8390c418E65680",
    "verified": true
}
```

## Get Trust Anchors Details
```
GET https://vs-node-1.veriscope.network/api/v1/server/get_trust_anchor_details/0xc2106031Dac53b629976e12aF769F60afcB38793
```
Response
```
[
    {
        "id": 5,
        "transaction_hash": "0x0cffd2f568c2c7fb2b989501c77e070a63f3a7d513829b6ae3dea9a4978ef225",
        "trust_anchor_address": "0xc2106031Dac53b629976e12aF769F60afcB38793",
        "key_value_pair_name": "ENTITY",
        "key_value_pair_value": "Paycase Inc.",
        "created_at": "2022-01-24T20:44:07.000000Z",
        "updated_at": "2022-01-24T20:44:07.000000Z"
    },
    {
        "id": 7,
        "transaction_hash": "0xf2a69553e68453d42398feaad0e5c04756f9d6d722bac1f7fe97cfb108aea969",
        "trust_anchor_address": "0xc2106031Dac53b629976e12aF769F60afcB38793",
        "key_value_pair_name": "API_URL",
        "key_value_pair_value": "https://paycase.veriscope.network/kyc-template",
        "created_at": "2022-01-24T20:44:07.000000Z",
        "updated_at": "2022-01-24T20:46:59.000000Z"
    },
    {
        "id": 47,
        "transaction_hash": "0x35ec28970de7d2d4399b1c2fa3ea4e6336874f8ce5c544858f6a6bca38f15ed1",
        "trust_anchor_address": "0xc2106031Dac53b629976e12aF769F60afcB38793",
        "key_value_pair_name": "API_URL",
        "key_value_pair_value": "https://paycase.veriscope.network/",
        "created_at": "2022-01-24T21:15:46.000000Z",
        "updated_at": "2022-01-24T21:15:46.000000Z"
    }
]
```
