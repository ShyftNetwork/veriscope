const Web3 = require('web3');
const BN = require('bn.js');
const ethers = require("ethers");
const pako = require('pako');
const web3_eth_abi = require("web3-eth-abi");
const express = require('express');
const axios = require('axios');
const EthCrypto = require('eth-crypto');
const dotenv = require('dotenv');
const bitcoinjs_lib = require('bitcoinjs-lib');
dotenv.config();

const testNetHttpUrl = process.env.HTTP;
const testNetWsUrl = process.env.WS;
const webhookUrl = process.env.WEBHOOK;
const httpPort = process.env.HTTP_API_PORT;
const privateKey = process.env.WEBHOOK_CLIENT_SECRET;

let provider = new ethers.providers.JsonRpcProvider(process.env.HTTP);
let trustAnchorWallet = new ethers.Wallet(process.env.TRUST_ANCHOR_PK, provider);
let trustAnchorAccount = process.env.TRUST_ANCHOR_ACCOUNT;

let web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

const web3HttpProvider = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));
const web3WsProvider = new Web3(new Web3.providers.WebsocketProvider(testNetWsUrl));

function attachedContract(addr, fn) {
  artifact = require(process.env.CONTRACTS+fn);
  return new ethers.Contract(addr, artifact.abi, trustAnchorWallet);
}

let TrustAnchorManager = attachedContract(process.env.TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS, 'TrustAnchorManager.json');

let TrustAnchorStorage = attachedContract(process.env.TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS, 'TrustAnchorStorage.json');

let TrustAnchorExtraData_Unique = attachedContract(process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS, 'TrustAnchorExtraData_Unique.json');

let TrustAnchorExtraData_Generic = attachedContract(process.env.TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS, 'TrustAnchorExtraData_Generic.json');


function decodeDocument(_documentEncoded) {

    const hex = Uint8Array.from(Buffer.from(_documentEncoded, 'hex'));
    try {

        let inflated = pako.inflate(hex);
    
        let inflatedToHex = Buffer.from(inflated).toString('hex');

        let unzippedEncodedDocumentsMatrix = "0x" + inflatedToHex;

        let decodedDocumentsMatrix = web3_eth_abi.decodeParameter(
            {
                "DocumentsMatrixStruct": {
                    "bitsMatrix": 'uint256',
                    "versionCode": 'uint16',
                    "encryptedData": 'bytes'
                }
            },
            unzippedEncodedDocumentsMatrix
        );

        return decodedDocumentsMatrix;

    } catch (err) {
        console.log(err);
        return null;
    }
    
}

function decodeDocumentMatrixInPlace(_dataTemplate) {
    if (_dataTemplate.versionCode == 2) {

        let bytesDocumentMatrixEncodedPacked = ethers.utils.toUtf8String(_dataTemplate.encryptedData);

        let encodedDocumentMatrixDataPacked = JSON.parse(bytesDocumentMatrixEncodedPacked);        

        return encodedDocumentMatrixDataPacked;

    } else {

        return null;

    }
}


function unpackCryptoAddress(decodeDocumentMatrix) {
    return ethers.utils.toUtf8String(decodeDocumentMatrix.data);
}

function unpackDocumentsMatrixEncrypted(documentMatrix) {

    let decodedDocument = decodeDocument(documentMatrix);

    let decodeDocumentMatrix = decodeDocumentMatrixInPlace(decodedDocument);

    let unpackedCryptoAddress = unpackCryptoAddress(decodeDocumentMatrix);

    return unpackedCryptoAddress;

}

function convertComponentsFromHex(hex) {
    return web3.utils.hexToAscii(hex);
}


function convertToByte32(string) {
    var temp = web3.utils.asciiToHex(string);
    temp = web3.utils.hexToBytes(temp);
    return temp;
}

function convertFromByte32(string) {
  var temp = web3.utils.bytesToHex(string);
  return web3.utils.hexToAscii(temp);
}

function createTravelRuleTemplate(_documentMatrixDataBytes) {

    let documentsMatrixBitString = "";

    let documentsMatrixBits = new Array(256);

    documentsMatrixBitString += "1";
    let i = 0;
    for (i = 1 ; i < 255; i++) {
        documentsMatrixBitString += "0";
    }
    documentsMatrixBitString += "0";
    
    let documentMatrixBignum = new BN(documentsMatrixBitString, 2);
    
    let versionCode = 2;

    return {"bitsMatrix" : documentMatrixBignum, "versionCode" : versionCode, "documentMatrixDataBytes" : _documentMatrixDataBytes};
}


function encodeDocumentMatrixInPlace(_dataTemplate) {

    if (_dataTemplate.versionCode === 2 && _dataTemplate.documentMatrixDataBytes != null) {

        let encodedDocumentMatrixDataPacked = JSON.stringify(_dataTemplate.documentMatrixDataBytes);

        let bytesDocumentMatrixEncodedPacked = ethers.utils.toUtf8Bytes(encodedDocumentMatrixDataPacked);

        _dataTemplate["encryptedData"] = bytesDocumentMatrixEncodedPacked;
 
        return _dataTemplate;
    } else {
        return null;
    }
}


function encodeDocument(_bitsMatrix, _versionCode, _encryptedData) {
    if (_versionCode === 1 || _versionCode == 2) {
        let encodedDocumentsMatrix = web3_eth_abi.encodeParameter(
            {
                "DocumentsMatrixStruct": {
                    "bitsMatrix": 'uint256',
                    "versionCode": 'uint16',
                    "encryptedData": 'bytes'
                }
            },
            {
                "bitsMatrix": _bitsMatrix.toString(),
                "versionCode": _versionCode.toString(),
                "encryptedData": _encryptedData
            }
        );

        const hex = Uint8Array.from(Buffer.from(encodedDocumentsMatrix.substr(2), 'hex'));

        let zippedEncodedDocumentsMatrix = pako.deflate(hex).buffer;

        const zippedHex = Buffer.from(zippedEncodedDocumentsMatrix).toString('hex');

        return zippedHex;
    } else {
        return null;
    }
}


function packDocumentsMatrixEncrypted(address) {

    let addressBuffer = new Buffer(address);

    let travelRuleTemplate = createTravelRuleTemplate(addressBuffer);

    let encodedDocumentMatrix = encodeDocumentMatrixInPlace(travelRuleTemplate);

    let encodedDocument = encodeDocument(encodedDocumentMatrix.bitsMatrix, encodedDocumentMatrix.versionCode, encodedDocumentMatrix.encryptedData);

    return encodedDocument;
}

function createBitcoinAccount() {

  var keyPair = bitcoinjs_lib.ECPair.makeRandom();
  var { address } = bitcoinjs_lib.payments.p2pkh({ pubkey: keyPair.publicKey });
  var publicKey = keyPair.publicKey.toString("hex");
  var privateKey = keyPair.toWIF();

  return {"address":address, "public_key": publicKey, "private_key": privateKey};

}

function createEthereumAccount() {
    var result = web3.eth.accounts.create();

    var address = result['address'];
    var privateKey = result['privateKey'];
    var publicKey = EthCrypto.publicKeyByPrivateKey(
        privateKey
    );

    return {"address":address, "public_key": publicKey, "private_key": privateKey};
}

//WEBHOOK

function sendWebhookMessage(obj) {
    
    const instance = axios.create();

    instance.defaults.headers.common['X-WEBHOOK-TOKEN'] = privateKey;

    instance.post(webhookUrl, {
      obj:obj
    })
    .then((res) => {
      console.log(res);
    })
    .catch((error) => {
      console.error(error)
    });

}

//APIS

const app = express();
const bodyParser = require('body-parser');
app.use(bodyParser.json());

app.listen(httpPort, () => {
    console.log('listening on '+httpPort);
});


// eg: create-new-user-account?user_id=1

app.get('/create-new-user-account', (req, res) => {
    var user_id = req.param('user_id');

    var TRUST_ANCHOR_PREFNAME = process.env.TRUST_ANCHOR_PREFNAME;
    var TRUST_ANCHOR_ACCOUNT = process.env.TRUST_ANCHOR_ACCOUNT;
    var TRUST_ANCHOR_PK = process.env.TRUST_ANCHOR_PK;

    var account = {prefname:TRUST_ANCHOR_PREFNAME, address:TRUST_ANCHOR_ACCOUNT, private_key:TRUST_ANCHOR_PK};
    var data = {account: account};
    var obj = { user_id: user_id, message: "create-new-user-account", data: data };
      
    console.log(obj);
    sendWebhookMessage(obj);
  
    res.sendStatus(201);

});

// eg: ta-is-verified?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-is-verified', (req, res) => {
    var user_id = req.param('user_id');
    var account = req.param('account');

    getIsTrustAnchorVerified(user_id, account);
    res.sendStatus(201);
});

function getIsTrustAnchorVerified(user_id, account) {
    (async () => {

    result = await TrustAnchorManager.isTrustAnchorVerified(account);

    console.log('isTrustAnchorVerified result');
    console.log(result);

    var obj = { user_id: user_id, message: "ta-is-verified", data: result };
    sendWebhookMessage(obj);

  })();
}


// eg: /ta-get-balance?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-get-balance', (req, res) => {
    var user_id = req.param('user_id');
    var account = req.param('account');
    taGetBalance(user_id, account);
    res.sendStatus(201);
});

function taGetBalance(user_id, account) {
  (async () => {

    result = await web3.eth.getBalance(account);
    console.log('getBalance result');
    console.log(result);

    var obj = { user_id: user_id, message: "ta-get-balance", data: result };
    sendWebhookMessage(obj);

  })();

}


// eg: ta-register-jurisdiction?user_id=1&account_address=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5&jurisdiction=1
/* {
  nonce: 9,
  gasPrice: BigNumber { _hex: '0x77359400', _isBigNumber: true },
  gasLimit: BigNumber { _hex: '0x67e6', _isBigNumber: true },
  to: '0xA858de9D43e6D1e3595c7f7127c89a87Bc634227',
  value: BigNumber { _hex: '0x00', _isBigNumber: true },
  data: '0x68878cde0000000000000000000000000000000000000000000000000000000000000001',
  chainId: 1337,
  v: 2710,
  r: '0x0f7490152cf05a465fd4816090d7f9d449bb91a28d1abb46965632b99f83ab53',
  s: '0x5850eebac5e6cd77d7369cade119afea05a597aa45e5f4b8b820dd1c185dfa19',
  from: '0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5',
  hash: '0xac220119fec47c3a064bd7e4b50db7029a19782a1bff2a3808c63720eb79de94',
  wait: [Function]
}
*/
app.get('/ta-register-jurisdiction', (req, res) => {
    var user_id = req.param('user_id');
    var account_address = req.param('account_address');
    var jurisdiction = req.param('jurisdiction');
    taRegisterJurisdiction(user_id, account_address, jurisdiction);
  res.sendStatus(201);
});


function taRegisterJurisdiction(user_id, account_address, jurisdiction) {

  (async () => {

    try {
      result = await TrustAnchorManager.setupTrustAnchorJurisdiction(jurisdiction);
      console.log('setupTrustAnchorJurisdiction result');
      console.log(result);

      var value = result['value'].toNumber();
      console.log(value);
      var obj = { user_id: user_id, message: "ta-register-jurisdiction", data: value };
      var myJSON = JSON.stringify(obj);
      sendWebhookMessage(obj);
    } catch(error) {
      console.log("caught error");
      console.log(error.message);
      var list = {message: error.message};
      var obj = { user_id: user_id, message: "ta-register-jurisdiction-error", data: list };
      sendWebhookMessage(obj);
    }

  })();

}

function taGetTrustAnchorJurisdiction(user_id, account_address) {

  (async () => {


      result = await TrustAnchorManager.getTrustAnchorJurisdiction("0xC2D6667E2Dd137b3f000254DDF4aA709e3Cf79ff");
      // result = await TrustAnchorManager.isTrustAnchorVerified("0xC2D6667E2Dd137b3f000254DDF4aA709e3Cf79ff");
      console.log('getTrustAnchorJurisdiction result');
      console.log(result);

      var value = result['value'].toNumber();
      console.log(value);
      var obj = { user_id: user_id, message: "ta-get-jurisdiction", data: value };
      var myJSON = JSON.stringify(obj);
      // sendWebhookMessage(obj);
   

  })();

}

/*
trust anchor set unique address
request: GET
response:
{
"request":"ta-set-unique-address",
"result":{"nonce":0,"gasPrice":{"type":"BigNumber","hex":"0x04a817c800"},"gasLimit":{"type":"BigNumber","hex":"0xaf98"},"to":"0x209CC29E6e6fcC4a1eBD050308A7d2BaEEa0D806","value":{"type":"BigNumber","hex":"0x00"},"data":"0xd26d983a0000000000000000000000008e8e3c0680ca4d2e9922fa002de0b0433fbf9e4d","chainId":120852482,"v":241705000,"r":"0xdff93849721847452a3e219fa2b19d8fb5ad0dc7849ebf25e1ce9e12b3f7358b","s":"0x2b89ae3e97e139d31ef130afdddffd04e31178fd1bf1285e06ba8a4a2afe8940","from":"0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6","hash":"0xeb935ae4b767de635a92688a2364a10a086cb598cf992807a1ee9f8f5dc6296f"}
}
*/

// eg: /ta-set-unique-address

app.get('/ta-set-unique-address', (req, res) => {
  var user_id = req.param('user_id');
  var account = req.param('account');
  taSetUniqueAddress(user_id, account);
  res.sendStatus(201);
});


function taSetUniqueAddress(user_id, account) {
  (async () => {

    result = await TrustAnchorManager.setUniqueTrustAnchorExtraDataAddress(process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS)
    console.log('setUniqueTrustAnchorExtraDataAddress result');
    console.log(result);
    var value = result['value'].toNumber();
    console.log(value);
    var obj = { user_id: user_id, message: "ta-set-unique-address", data: value };
    sendWebhookMessage(obj);

  })();
}



/*
trust anchor get unique address
request: GET
response:
{
"request":"ta-get-unique-address",
"result":"0x8e8E3c0680cA4d2e9922fA002dE0b0433fbf9E4D"
}
*/

// eg: /ta-get-unique-address/0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6

app.get('/ta-get-unique-address/:account', (req, res) => {

    var account = req.params.account; 
    trustAnchorGetUniqueAddress(account);
    res.sendStatus(201);
});


function trustAnchorGetUniqueAddress(account) {
  (async () => {

    result = await TrustAnchorManager.getUniqueTrustAnchorExtraDataAddress(account);
    console.log('getUniqueTrustAnchorExtraDataAddress result');
    console.log(result);

    var obj = {request: 'ta-get-unique-address', result: result};
    sendWebhookMessage(obj);

  })();
}

// eg: ta-set-key-value-pair?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5&ta_key_name=ENTITY&ta_key_value=Abc%20Inc.

app.get('/ta-set-key-value-pair', (req, res) => {
    var user_id = req.param('user_id');
    var account = req.param('account');
    var key_name = req.param('ta_key_name');
    var key_value = req.param('ta_key_value');
    taSetKeyValuePair(user_id, account, key_name, key_value);
    res.sendStatus(201);
});

function taSetKeyValuePair(user_id, account, key_name, key_value) {
  (async () => {

    result = await TrustAnchorExtraData_Unique.setTrustAnchorKeyValuePair(key_name, key_value);
    console.log('setTrustAnchorKeyValuePair result');
    console.log(result);
    var value = result['value'].toNumber();
    console.log(value);
    var obj = { user_id: user_id, message: "ta-set-key-value-pair", data: value };
    sendWebhookMessage(obj);

  })();
}


/*
ta get number of key value pairs
request: GET
params: 
:account: 0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6
response:
{
"request":"ta-get-number-of-key-value-pairs",
"result":{"type":"BigNumber","hex":"0x01"}
}

*/

// eg: /ta-get-number-of-key-value-pairs/0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6

app.get('/ta-get-number-of-key-value-pairs/:account', (req, res) => {

    var account = req.params.account; 

    trustAnchorGetNumberOfKeyValuePairs(account);
    res.sendStatus(201);
});

function trustAnchorGetNumberOfKeyValuePairs(account) {
  (async () => {


    result = await TrustAnchorExtraData_Unique.getTrustAnchorNumberOfKeyValuePairs(account);
    console.log('getTrustAnchorNumberOfKeyValuePairs result');
    console.log(result);

    var obj = {request: 'ta-get-number-of-key-value-pairs', result: result};
    sendWebhookMessage(obj);

  })();
}

/*
ta get key value pair name by index
request: GET
params: 
:account: 0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6
:index: 0
response:
{
"request":"ta-get-key-value-pair-name-by-index",
"result":[true,"ENTITY"]
}

*/

// eg: /ta-get-key-value-pair-name-by-index/0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6/0

app.get('/ta-get-key-value-pair-name-by-index/:account/:index', (req, res) => {

    var account = req.params.account; 
    var index = req.params.index; 
    trustAnchorGetKeyValuePairNameByIndex(account, index);
    res.sendStatus(201);
});

function trustAnchorGetKeyValuePairNameByIndex(account, index) {
  (async () => {

    result = await TrustAnchorExtraData_Unique.getTrustAnchorKeyValuePairNameByIndex(account, index);
    console.log('getTrustAnchorKeyValuePairNameByIndex result');
    console.log(result);

    var obj = {request: 'ta-get-key-value-pair-name-by-index', result: result};
    sendWebhookMessage(obj);

  })();
}

/*
ta get key pair value
request: GET
params: 
:account: 0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6
:key: ENTITY
response:
{
"request":"ta-get-key-pair-value",
"result":[true,"ACME_INC."]
}

*/

// eg: /ta-get-key-pair-value/0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6/ENTITY

app.get('/ta-get-key-pair-value/:account/:key', (req, res) => {

    var account = req.params.account; 
    var key = req.params.key; 
    trustAnchorGetKeyPairValue(account, key);
    res.sendStatus(201);
});

function trustAnchorGetKeyPairValue(account, key) {
  (async () => {

    result = await TrustAnchorExtraData_Unique.getTrustAnchorKeyValuePairValue(account, key);
    console.log('getTrustAnchorKeyValuePairNameByIndex result');
    console.log(result);

    var obj = {request: 'ta-get-key-pair-value', result: result};
    sendWebhookMessage(obj);

  })();
}


// eg: ta-create-user?user_id=1&ta_user_id=1&prefname=Nic&password=Password1*

app.get('/ta-create-user', (req, res) => {

    var prefname = req.param('prefname');
    var user_id = req.param('user_id');
    var ta_user_id = req.param('ta_user_id');
    
    var result = web3.eth.accounts.create();
    console.log(result);

    var address = result['address'];
    var privateKey = result['privateKey'];
    var account = {address:address, private_key:privateKey};
    var bitcoinAccount = createBitcoinAccount();
    var ethereumAccount = createEthereumAccount();
    var data = {prefname:prefname, account: account, user_id: user_id, bitcoinAccount: bitcoinAccount, ethereumAccount: ethereumAccount};
    var obj = { user_id: user_id, ta_user_id: ta_user_id, message: "ta-create-user", data: data };
    console.log('ta-create-user');
    console.log(obj);

    sendWebhookMessage(obj);

    res.sendStatus(201);
});


/*
trust anchor set attestation
request: GET
params:
:user_account: 0x4a94595a6622E4FA69945FE6eaD4407e54532d7d
:jurisdiction: INT
:effective_time: UNIX TIMESTAMP
:expiry_time: UNIX TIMESTAMP
:public_data: "WALLET"
:documents_matrix_encrypted: "16Qygw3QgX4TgZxneVWQeVVxp2XNYf9vP9"
:availability_address_encrypted: "BTC"
:is_managed: true

response:
{"request":"ta-set-attestation",
"result":{"nonce":4,"gasPrice":{"type":"BigNumber","hex":"0x04a817c800"},"gasLimit":{"type":"BigNumber","hex":"0x081382"},"to":"0xA434B2394Dac9c18fA3ef92929785cbEf1A7aD79","value":{"type":"BigNumber","hex":"0x00"},"data":"0x762435730000000000000000000000004a94595a6622e4fa69945fe6ead4407e54532d7d0000000000000000000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000005d3f9bc4000000000000000000000000000000000000000000000000000000006104a5c40000000000000000000000000000000000000000000000000000000000000100000000000000000000000000000000000000000000000000000000000000014020202020202020202020202020202020202020202020202020202020204254430000000000000000000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000000000000657414c4c455400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000106373839633633363063303062313431616630636231333032346330346534313330386338663735363262393535343136613432613539323933393935613661356135313632396539323861353234393632343261353934353962353865613938396165383538313865613138316130316231383162313865613132313530633431303263303265343539353865383938316165393538393838303339393630363430353534303663303863343036343032353636336131366536313038353130386531393534646134386337643430306134643364633432633763323132323830626534396230333835383133633033323032626236313638623033303163363130323036370000000000000000000000000000000000000000000000000000","chainId":120852482,"v":241704999,"r":"0xca539dd7b68caa29af05f12106aa4db9aff7022e2ab765ed57bdab2ec7c67d76","s":"0x2ee7e419054e36d7742d6fd34383e4b310178051916a5118f63563a8ddb5a7fd","from":"0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6","hash":"0xb8c9d00c94cc005d8438e9a2d2136b769a7e80087926b8b99ba67ee8046f4fb1"}
}

*/

// eg: ta-set-attestation?attestation_type=WALLET&user_id=1&user_address=0x447832bc6303C87A7C7C0E3894a5C6848Aa24877&jurisdiction=1&effective_time=&expiry_time=&public_data=WALLET&documents_matrix_encrypted=0xAb00d650758D95BC7A9ceFe248960EEB77344eed&availability_address_encrypted=ETH&ta_address=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-set-attestation', (req, res) => {
    var attestation_type = req.param('attestation_type');
    var user_id = req.param('user_id');
    var user_address = req.param('user_address');
    var jurisdiction = req.param('jurisdiction');
    var effective_time = req.param('effective_time');
    if (effective_time.length == 0) {
      effective_time = Math.floor(Date.now() / 1000) - (60 * 60 * 24 * (365 + 1));// (in the past a year and a day)
    }
    var expiry_time = req.param('expiry_time');
    if (expiry_time.length == 0) {
      expiry_time = Math.floor(Date.now() / 1000) + (60 * 60 * 24 * (365 + 1));// (in the future a year and a day)
    }
    
    var public_data = convertToByte32(req.param('public_data'));

    var documents_matrix_encrypted = packDocumentsMatrixEncrypted(req.param('documents_matrix_encrypted'));
    console.log('documents_matrix_encrypted');
    documents_matrix_encrypted = convertToByte32(documents_matrix_encrypted);

    var availability_address_encrypted = req.param('availability_address_encrypted').padStart(32, ' ');
    availability_address_encrypted = convertToByte32(availability_address_encrypted);
    
    var ta_address = req.param('ta_address');
    var is_managed = true;

    taSetAttestation(attestation_type, user_id, user_address, jurisdiction, effective_time, expiry_time, public_data, documents_matrix_encrypted, availability_address_encrypted, is_managed, ta_address);
    res.sendStatus(201);
});

function taSetAttestation(attestation_type, user_id, user_address, jurisdiction, effective_time, expiry_time, public_data, documents_matrix_encrypted, availability_address_encrypted, is_managed, ta_address) {
     
    (async () => {

      result = await TrustAnchorStorage.setAttestation(user_address, jurisdiction, effective_time, expiry_time, public_data, documents_matrix_encrypted, availability_address_encrypted, is_managed);
      console.log('setAttestation result');
      console.log(result);

      result['documents_matrix_encrypted'] = convertFromByte32(documents_matrix_encrypted);
      result['public_data'] = convertFromByte32(public_data);
      result['availability_address_encrypted'] = convertFromByte32(availability_address_encrypted).trim();
      result['attestation_type'] = attestation_type;
      result['user_address'] = user_address;
      result['ta_address'] = ta_address;
      result['value'] = result['value'].toNumber();
      console.log(result);
      var obj = { user_id: user_id, message: "ta-set-attestation", data: result};
      sendWebhookMessage(obj);

    })();
}

// eg: ta-get-user-attestations?user_id=1&account=0x447832bc6303C87A7C7C0E3894a5C6848Aa24877

app.get('/ta-get-user-attestations', (req, res) => {
  var user_id = req.param('user_id');
  var account = req.param('account');
  taGetAttestationKeccakArrayForIdentifiedAddress(user_id, account);
  res.sendStatus(201);
});


function taGetAttestationKeccakArrayForIdentifiedAddress(user_id, account) {
  (async () => {

    result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
    console.log('getAttestationKeccakArrayForIdentifiedAddress result');
    console.log(result);
    var list = [];
    for(var i = 0; i < result.length; i++) {
      var dict = {hash: result[i]};
      list.push(dict);
    }
    var obj = { user_id: user_id, message: "ta-get-user-attestations", data: list };
    sendWebhookMessage(obj);

  })();

}

// eg: ta-get-attestation-components-in-array?user_id=1&account=0xA155C75C8F5Dd250aB15e061ff6Ecc678374b380&index=1

app.get('/ta-get-attestation-components-in-array', (req, res) => {
  var user_id = req.param('user_id');
  var account = req.param('account');
  var index = req.param('index');
  taGetAttestationComponentsInKeccakArray(user_id, account, index);
  res.sendStatus(201);
});

function taGetAttestationComponentsInKeccakArray(user_id, account, index) {
  (async () => {

    result = await TrustAnchorStorage.getAttestationComponentsInKeccakArray(account, index);
    console.log('getGraphConstructableAttestationInKeccakArray result');
    
    var list = [];

    var documentsMatrixEncrypted = convertComponentsFromHex(result['documentsMatrixEncrypted']);
    
    documentsMatrixEncrypted = unpackDocumentsMatrixEncrypted(documentsMatrixEncrypted);
    var dict = {};
    dict['document_encode'] = documentsMatrixEncrypted;
    dict['jurisdiction'] = result['jurisdiction'];
    dict['trustAnchorAddress'] = result['trustAnchorAddress'];
    dict['publicData'] = result['publicData'];
    dict['availabilityAddressEncrypted'] = result['availabilityAddressEncrypted'];
    dict['documentsMatrixEncrypted'] = result['documentsMatrixEncrypted'];

    dict['type'] = convertComponentsFromHex(result['publicData']);
    dict['document'] = documentsMatrixEncrypted;
    dict['document_decrypt'] = documentsMatrixEncrypted;
    dict['memo'] = convertComponentsFromHex(result['availabilityAddressEncrypted']);
    dict['user_address'] = account;

    console.log(dict);
    list.push(dict);
    var obj = { user_id: user_id, message: "ta-get-attestation-components-in-array", data: list };
    sendWebhookMessage(obj);

  })();

}


/*
trust anchor get attestation array for trust anchor account
request: GET
params:
:account: 0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6
response:
{
"request":"ta-get-attestation-array-for-ta-account",
"result":["0x1e291da4a3f07dbbd1b5146994a5204475b57a2dfc1f7bb29db8cb55cb2c9f0a"]
}
*/

// eg: /ta-get-attestation-array-for-ta-account/0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6

app.get('/ta-get-attestation-array-for-ta-account/:account', (req, res) => {
    
    var account = req.params.account; 
 
    trustAnchorGetAttestationArrayForTrustAnchorAccount(account);
    res.sendStatus(201);
});


function trustAnchorGetAttestationArrayForTrustAnchorAccount(account) {
    (async () => {

    result = await TrustAnchorStorage.getAttestationKeccakArrayForTrustAnchor(account);
        console.log('getAttestationKeccakArrayForTrustAnchor result');
        console.log(result);

    var obj = {request: 'ta-get-attestation-array-for-ta-account', result: result};
    sendWebhookMessage(obj);

    })();
}

/*
trust anchor set attestation
request: GET
params:
:account: 0x4a94595a6622E4FA69945FE6eaD4407e54532d7d
response:
{
"request":"ta-get-attestation-array-for-user-account",
"result":["0x1e291da4a3f07dbbd1b5146994a5204475b57a2dfc1f7bb29db8cb55cb2c9f0a"]
}
*/

// eg: /ta-get-attestation-array-for-user-account/0x4a94595a6622E4FA69945FE6eaD4407e54532d7d

app.get('/ta-get-attestation-array-for-user-account/:account', (req, res) => {
    
    var account = req.params.account; 
 
    trustAnchorGetAttestationArrayForUserAccount(account);
    res.sendStatus(201);
});


function trustAnchorGetAttestationArrayForUserAccount(account) {
    (async () => {

    result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
        console.log('getAttestationKeccakArrayForIdentifiedAddress result');
        console.log(result);
    
    var obj = {request: 'ta-get-attestation-array-for-user-account', result: result};
    sendWebhookMessage(obj);

    })();
}


/*
trust anchor get attestation components
request: GET
params:
:attestation_hash: 0x1e291da4a3f07dbbd1b5146994a5204475b57a2dfc1f7bb29db8cb55cb2c9f0a

response:
{
"request":"ta-get-attestation-components",
"result":["0xb8866C168a432E4c0AfD6507e86FA4c12cF5f6f6",1,{"type":"BigNumber","hex":"0x5d3f9bc4"},{"type":"BigNumber","hex":"0x6104a5c4"},"0x57414c4c4554","0x37383963363336306330306231343161663063623133303234633034653431333038633866373536326239353534313661343261353932393339393561366135613531363239653932386135323439363234326135393435396235386561393839616538353831386561313831613031623138316231386561313231353063343130326330326534353935386538393831616539353839383830333939363036343035353430366330386334303634303235363633613136653631303835313038653139353464613438633764343030613464336463343263376332313232383062653439623033383538313363303332303262623631363862303330316336313032303637","0x2020202020202020202020202020202020202020202020202020202020425443",true]
}
*/

// eg: ta-get-attestation-components?attestation_hash=0x31b24ad5f701548700a29427260c85232679530132e13e4110ee0ada9d8f25f3

app.get('/ta-get-attestation-components', (req, res) => {
    console.log('/ta-get-attestation-components');


    var attestation_hash = req.param('attestation_hash');
    console.log('attestation_hash');
    console.log(attestation_hash);
    taGetAttestationComponents(attestation_hash);
    res.sendStatus(201);
});

function taGetAttestationComponents(attestation_hash) {
  (async () => {
    
    result = await TrustAnchorStorage.getAttestationComponents(attestation_hash);
    console.log('getAttestationComponents result');
    console.log(result);

    var documentsMatrixEncrypted = convertComponentsFromHex(result['documentsMatrixEncrypted']);
    documentsMatrixEncrypted = unpackDocumentsMatrixEncrypted(documentsMatrixEncrypted);


    var dict = {};
    dict['type'] = convertComponentsFromHex(result['publicData']);
    dict['document'] = documentsMatrixEncrypted;
    dict['memo'] = convertComponentsFromHex(result['availabilityAddressEncrypted']);
    dict['trustAnchorAddress'] = result['trustAnchorAddress'];
    dict['attestation_hash'] = attestation_hash;

    var obj = {message: 'ta-get-attestation-components', data: dict};
    sendWebhookMessage(obj);

  })();

}

(async function() {

    provider.on("block", (blockNumber) => {
        console.log("block# " + blockNumber);
    });

    TrustAnchorStorage.on("EVT_setAttestation", async (attestationKeccak, msg_sender, _identifiedAddress, _jurisdiction, _effectiveTime, _expiryTime, _publicData_0, _documentsMatrixEncrypted_0, _availabilityAddressEncrypted, _isManaged, _publicDataLength, _documentsMatrixEncryptedLength, event) => {
        console.log("event EVT_setAttestation");
        data = {};
        data['transactionHash'] = event.transactionHash;
        data['event'] = "EVT_setAttestation";
        data['returnValues'] = {};

        data['returnValues']['attestationKeccak'] = attestationKeccak;
        data['returnValues']['msg_sender'] = msg_sender;
        data['returnValues']['_identifiedAddress'] = _identifiedAddress;
        data['returnValues']['_jurisdiction'] = _jurisdiction;
        data['returnValues']['_effectiveTime'] = _effectiveTime.toString();
        data['returnValues']['_expiryTime'] = _expiryTime.toString();
        data['returnValues']['_publicData_0'] = _publicData_0;
        data['returnValues']['_documentsMatrixEncrypted_0'] = _documentsMatrixEncrypted_0;
        data['returnValues']['_availabilityAddressEncrypted'] = _availabilityAddressEncrypted;
        data['returnValues']['_isManaged'] = _isManaged;
        data['returnValues']['_publicDataLength'] = _publicDataLength;
        data['returnValues']['_documentsMatrixEncryptedLength'] = _documentsMatrixEncryptedLength;

        data['type'] = convertComponentsFromHex(data['returnValues']['_publicData_0']);
        data['document'] = convertComponentsFromHex(data['returnValues']['_documentsMatrixEncrypted_0']);
        data['document_decrypt'] = convertComponentsFromHex(data['returnValues']['_documentsMatrixEncrypted_0']);

        data['memo'] = convertComponentsFromHex(data['returnValues']['_availabilityAddressEncrypted']);

        var obj = {
            message: "tas-event",
            data: data
        };
        sendWebhookMessage(obj);

        console.log(data);
    });

    TrustAnchorExtraData_Generic.on("EVT_setDataRetrievalParametersCreated", async (_trustAnchorAddress, _endpointName, _ipv4Address, event) => {
        console.log("event EVT_setDataRetrievalParametersCreated");
        data = {};
        data['transactionHash'] = event.transactionHash;
        data['event'] = "EVT_setDataRetrievalParametersCreated";
        data['returnValues'] = {};

        data['returnValues']['_trustAnchorAddress'] = _trustAnchorAddress;
        data['returnValues']['_endpointName'] = _endpointName;

        var ip_address = _ipv4Address.join('.');
        data['ipv4_address'] = ip_address;

        var obj = {
            message: "taed-event",
            data: data
        };
        sendWebhookMessage(obj);

        console.log(data);
    });

    TrustAnchorExtraData_Unique.on("EVT_setTrustAnchorKeyValuePairCreated", async (_trustAnchorAddress, _keyValuePairName, _keyValuePairValue, event) => {
        console.log("event EVT_setDataRetrievalParametersCreated");
        data = {};
        data['transactionHash'] = event.transactionHash;
        data['event'] = "EVT_setTrustAnchorKeyValuePairCreated";
        data['returnValues'] = {};

        data['returnValues']['_trustAnchorAddress'] = _trustAnchorAddress;
        data['returnValues']['_keyValuePairName'] = _keyValuePairName;
        data['returnValues']['_keyValuePairValue'] = _keyValuePairValue;


        var obj = {
            message: "taedu-event",
            data: data
        };
        sendWebhookMessage(obj);

        console.log(data);
    });


})();
