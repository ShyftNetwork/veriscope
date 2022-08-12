const express = require('express');
const cookieParser = require('cookie-parser');
const createError = require('http-errors');
const bodyParser = require('body-parser');
const winston = require('winston');

const {
  getAllAttestations, 
  getTrustAnchorKeyValuePairCreated,
  getTrustAnchorKeyValuePairUpdated,
  getVerifiedTrustAnchors,
  getValidationForKeyValuePairData
} = require('./blockchain-data')

const ethers = require("ethers");
const Web3 = require('web3');
const dotenv = require('dotenv');
const services = require('./services');

dotenv.config();
const Keyv = require('keyv');
const keyv = new Keyv(process.env.REDIS_URI);
const jwt = require('express-jwt');
const utility = require('./utility');
const session = require('express-session');


const logger = winston.createLogger({
  level: (process.env.LOG_LEVEL || 'info'),
  format: winston.format.json(),
  maxsize: 512000000,
  maxFiles: 3,
  tailable: true,
  defaultMeta: { service: 'http-api' },
  transports: [
    //
    // - Write all logs with level `error` and below to `error.log`
    // - Write all logs with level `info` and below to `combined.log`
    //
    new winston.transports.File({ filename: 'logs/http-api.error.log', level: 'error' }),
    new winston.transports.File({ filename: 'logs/http-api.combined.log' }),
  ],
});

//
// If we're not in production then log to the `console` with the format:
// `${info.level}: ${info.message} JSON.stringify({ ...rest }) `
//

logger.add(new winston.transports.Console({
    format: winston.format.simple(),
}));


const testNetHttpUrl = process.env.HTTP;
const httpPort = process.env.HTTP_API_PORT;
const webhookClientSecret = process.env.WEBHOOK_CLIENT_SECRET;

let provider = new ethers.providers.JsonRpcProvider(process.env.HTTP);
let customWsProvider = new ethers.providers.WebSocketProvider(process.env.WS);


let trustAnchorWallet = new ethers.Wallet(process.env.TRUST_ANCHOR_PK, provider);
let trustAnchorAccount = process.env.TRUST_ANCHOR_ACCOUNT;

let web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

function attachedContract(addr, fn) {
  artifact = require(process.env.CONTRACTS+fn);
  return new ethers.Contract(addr, artifact.abi, trustAnchorWallet);
}

//USED FOR EVENTS
let TrustAnchorManager = attachedContract(process.env.TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS, 'TrustAnchorManager.json');
let TrustAnchorStorage = attachedContract(process.env.TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS, 'TrustAnchorStorage.json');
let TrustAnchorExtraData_Unique = attachedContract(process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS, 'TrustAnchorExtraData_Unique.json');
let TrustAnchorExtraData_Generic = attachedContract(process.env.TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS, 'TrustAnchorExtraData_Generic.json');


//APIS

const app = express();
app.use(bodyParser.json());
app.use(cookieParser())
app.use(session({
  secret: webhookClientSecret,
  resave: false,
  saveUninitialized: true,
  cookie: { secure: false }
}))

var queue = services.bull.queue;

app.locals.bull = services.bull;

app.use(function(req, res, next) {
  req['bull'] = app.locals.bull;
  next();
})


app.use(jwt({
  secret: webhookClientSecret,
  algorithms: ['HS256'],
  credentialsRequired: true,
  getToken: function fromCookieOrQuerystring (req) {
    // query token
    if (req.query && req.query.token) {
        req.session.token = req.query.token;
        return req.query.token;
    } else if (req.session.token){
        return req.session.token;
    }

    return null;

  }
}));


app.use(function (err, req, res, next) {

    if(req.url.includes("arena") && err.name === 'UnauthorizedError'){
      req.session.destroy();
      return res.status(401).send('Invalid token');
    }

    next();
});



app.use('/', services.bull.arena);



// eg: create-new-user-account?user_id=1

app.get('/create-new-user-account', (req, res) => {
    var user_id = req.param('user_id');

    var TRUST_ANCHOR_PREFNAME = process.env.TRUST_ANCHOR_PREFNAME;
    var TRUST_ANCHOR_ACCOUNT = process.env.TRUST_ANCHOR_ACCOUNT;
    var TRUST_ANCHOR_PK = process.env.TRUST_ANCHOR_PK;

    var ta_sign_template = utility.TASign(process.env.SIGN_MESSAGE+"_TA", TRUST_ANCHOR_PK);
    var ta_public_key = utility.GetEthPublicKey(TRUST_ANCHOR_PK);

    var account_logger = {prefname:TRUST_ANCHOR_PREFNAME, address:TRUST_ANCHOR_ACCOUNT, private_key:"xxxxxxxxxx", public_key:ta_public_key, signature_hash:ta_sign_template};
    var data_logger = {account: account_logger};
    var obj_logger = { user_id: user_id, message: "create-new-user-account", data: data_logger };

    var account = {prefname:TRUST_ANCHOR_PREFNAME, address:TRUST_ANCHOR_ACCOUNT, private_key:TRUST_ANCHOR_PK, public_key:ta_public_key, signature_hash:ta_sign_template};
    var data = {account: account};
    var obj = { user_id: user_id, message: "create-new-user-account", data: data };

    logger.debug(obj_logger);
    utility.sendWebhookMessage(obj);

    res.sendStatus(201);

});

// eg: ta-is-verified?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-is-verified', (req, res) => {
    var user_id = req.param('user_id');
    var account = req.param('account');
    utility.getIsTrustAnchorVerified(user_id, account);
    res.sendStatus(201);
});




// eg: /ta-get-balance?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-get-balance', (req, res) => {
    var user_id = req.param('user_id');
    var account = req.param('account');
    utility.taGetBalance(user_id, account);
    res.sendStatus(201);
});



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
    utility.taRegisterJurisdiction(user_id, account_address, jurisdiction);
    res.sendStatus(201);
});



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
  utility.taSetUniqueAddress(user_id, account);
  res.sendStatus(201);
});




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
    utility.trustAnchorGetUniqueAddress(account);
    res.sendStatus(201);
});




// eg: ta-set-key-value-pair?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5&ta_key_name=ENTITY&ta_key_value=Abc%20Inc.

app.get('/ta-set-key-value-pair', (req, res) => {
    var user_id = req.param('user_id');
    var account = req.param('account');
    var key_name = req.param('ta_key_name');
    var key_value = req.param('ta_key_value');
    utility.taSetKeyValuePair(user_id, account, key_name, key_value);
    res.sendStatus(201);
});



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
    utility.trustAnchorGetNumberOfKeyValuePairs(account);
    res.sendStatus(201);
});


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
    utility.trustAnchorGetKeyValuePairNameByIndex(account, index);
    res.sendStatus(201);
});



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
    utility.trustAnchorGetKeyPairValue(account, key);
    res.sendStatus(201);
});


// eg: ta-create-user?user_id=1&ta_user_id=1&prefname=Nic&password=Password1*

app.get('/ta-create-user', async (req, res) => {

    var prefname = req.param('prefname');
    var user_id = req.param('user_id');
    var ta_user_id = req.param('ta_user_id');

    var result = web3.eth.accounts.create();

    var address = result['address'];
    var privateKey = result['privateKey'];
    var account = {address:address, private_key:privateKey};
    var accountLogger = {address:address, private_key:"xxxxxxxxxx"};

    var user_sign_template = utility.TASign(process.env.SIGN_MESSAGE+"_USER", privateKey.substr(2));
    var user_public_key = utility.GetEthPublicKey(privateKey.substr(2));

    var bitcoinAccount = utility.createBitcoinAccount();
    var bitcoinAccountLogger = Object.assign({}, bitcoinAccount);
    bitcoinAccountLogger['private_key'] = "xxxxxxxxxx";

    var ethereumAccount = utility.createEthereumAccount();
    var ethereumAccountLogger = Object.assign({}, ethereumAccount);
    ethereumAccountLogger['private_key'] = "xxxxxxxxxx";

    var zcashAccount = utility.createZcashAccount();
    var zcashAccountLogger = Object.assign({}, zcashAccount);
    zcashAccountLogger['private_key'] = "xxxxxxxxxx";

    var moneroAccount =  await utility.createMoneroAccount();
    var moneroAccountLogger = Object.assign({}, moneroAccount);
    moneroAccountLogger['private_key'] = "xxxxxxxxxx";

    

    var data_logger = {prefname:prefname, account: accountLogger, user_id: user_id, bitcoinAccount: bitcoinAccountLogger, ethereumAccount: ethereumAccountLogger, zcashAccount: zcashAccountLogger, moneroAccount: moneroAccountLogger, public_key: user_public_key, signature_hash:user_sign_template};
    var obj_logger = { user_id: user_id, ta_user_id: ta_user_id, message: "ta-create-user", data: data_logger };
    logger.debug('ta-create-user');
    logger.debug(obj_logger);

    var data = {prefname:prefname, account: account, user_id: user_id, bitcoinAccount: bitcoinAccount, ethereumAccount: ethereumAccount, zcashAccount: zcashAccount, moneroAccount: moneroAccount, public_key: user_public_key, signature_hash:user_sign_template};
    var obj = { user_id: user_id, ta_user_id: ta_user_id, message: "ta-create-user", data: data };

    utility.sendWebhookMessage(obj);
    
    res.sendStatus(201);
});

// eg: ta-set-v3-attestation?attestation_type=WALLET&user_id=1&user_account=0x447832bc6303C87A7C7C0E3894a5C6848Aa24877&jurisdiction=196&effective_time=&expiry_time=&coin_address=0x6878e02e4782cd71af5d48e55e28f951eff5ec7c&coin_blockchain=ETH&coin_token=USDT&coin_memo=memo&ta_account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-set-v3-attestation', (req, res) => {
    var attestation_type = "WALLET";
    var user_id = req.param('user_id');
    var user_account = req.param('user_account');
    var jurisdiction = req.param('jurisdiction');
    var effective_time = req.param('effective_time');
    if (!effective_time) {
      effective_time = Math.floor(Date.now() / 1000) - (60 * 60 * 24 * (365 + 1));// (in the past a year and a day)
    }
    var expiry_time = req.param('expiry_time');
    if (!expiry_time) {
      expiry_time = Math.floor(Date.now() / 1000) + (60 * 60 * 24 * (365 + 1));// (in the future a year and a day)
    }

    var public_data = utility.convertToByte32(req.param('coin_memo'));

    var availability_address_encrypted = " ".padStart(32, ' ');
    availability_address_encrypted = utility.convertToByte32(availability_address_encrypted);

    var coin_address = req.param('coin_address');
    logger.debug('coin_address');
    logger.debug(coin_address);

    var coin_token = req.param('coin_token');
    logger.debug('coin_token');
    logger.debug(coin_token);

    var coin_blockchain = req.param('coin_blockchain');
    logger.debug('coin_blockchain');
    logger.debug(coin_blockchain);

    var coin_type = coin_blockchain+"_"+coin_token;
    logger.debug('coin_type');
    logger.debug(coin_type);

    var travelRuleV3Template = utility.createTravelRuleV3Template(coin_address, coin_type);
    logger.debug('travelRuleV3Template');
    logger.debug(travelRuleV3Template);

    var encodedDocumentMatrix = utility.encodeDocumentMatrixInPlace(travelRuleV3Template);
    logger.debug('encodedDocumentMatrix');
    logger.debug(encodedDocumentMatrix);

    var encodedDocument = utility.encodeDocument(encodedDocumentMatrix.bitsMatrix, encodedDocumentMatrix.versionCode, encodedDocumentMatrix.encryptedData);
    logger.debug('encodedDocument');
    logger.debug(encodedDocument);

    var documents_matrix_encrypted = utility.convertToByte32(encodedDocument);
    logger.debug('documents_matrix_encrypted');
    logger.debug(documents_matrix_encrypted);

    var ta_account = req.param('ta_account');
    var is_managed = true;

    queue.taSetAttestation.add({
      attestation_type: attestation_type,
      user_id: user_id,
      user_address: user_account,
      jurisdiction: jurisdiction,
      effective_time: effective_time,
      expiry_time: expiry_time,
      public_data: public_data,
      documents_matrix_encrypted: documents_matrix_encrypted,
      availability_address_encrypted: availability_address_encrypted,
      is_managed: is_managed,
      ta_address: ta_account
    }, services.bull.opts);

    res.sendStatus(201);
});

// eg: ta-get-user-attestations?user_id=1&account=0x447832bc6303C87A7C7C0E3894a5C6848Aa24877

app.get('/ta-get-user-attestations', (req, res) => {
  var user_id = req.param('user_id');
  var account = req.param('account');
  utility.taGetAttestationKeccakArrayForIdentifiedAddress(user_id, account);
  res.sendStatus(201);
});


// eg: ta-get-attestation-components-in-array?user_id=1&account=0xA155C75C8F5Dd250aB15e061ff6Ecc678374b380&index=1

app.get('/ta-get-attestation-components-in-array', (req, res) => {
  var user_id = req.param('user_id');
  var account = req.param('account');
  var index = req.param('index');
  utility.taGetAttestationComponentsInKeccakArray(user_id, account, index);
  res.sendStatus(201);
});

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

    utility.trustAnchorGetAttestationArrayForTrustAnchorAccount(account);
    res.sendStatus(201);
});



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

    utility.trustAnchorGetAttestationArrayForUserAccount(account);
    res.sendStatus(201);
});




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
    logger.debug('/ta-get-attestation-components');


    var attestation_hash = req.param('attestation_hash');
    logger.debug('attestation_hash');
    logger.debug(attestation_hash);

    utility.taGetAttestationComponents(attestation_hash);

    res.sendStatus(201);
});


app.get('/ta-nonce-count',  async (req, res) => {

    logger.debug('/ta-nonce-count');

    let baseNonce = await provider.getTransactionCount(trustAnchorAccount);

    let baseNoncePending = await  provider.getTransactionCount(trustAnchorAccount,"pending");


    res.status(200).json({count: baseNonce , address: trustAnchorAccount, pendingCount:  baseNoncePending });
});

/*
save all attestations to database
request: GET
params:
:user_id: 1
*/
// eg: refresh-all-attestations?user-id=1

app.get('/refresh-all-attestations', (req, res) => {
  var user_id = req.param('user_id');
  refreshAllAttestations(user_id);
  res.sendStatus(201);
});

function refreshAllAttestations(user_id) {
  (async () => {
    logger.debug('refreshAllAttestations');
    var obj = { user_id: user_id, message: "refresh-all-attestations", data: {completed:false} };
    utility.sendWebhookMessage(obj);
    getAllAttestations(user_id)
  })();
}

/*
save all Discovery Layer Key Value Pairs to database
request: GET
params:
:user_id: 1
*/
// eg: refresh-all-discovery-layer-key-value-pairs?user-id=1

app.get('/refresh-all-discovery-layer-key-value-pairs', (req, res) => {
  var user_id = req.param('user_id');
  refreshAllDiscoveryLayerKeyValuePairs(user_id);
  refreshAllDiscoveryLayerKeyValuePairsUpdated(user_id);
  refreshValidationsForKeyValuePairData(user_id);
  res.sendStatus(201);
});

function refreshAllDiscoveryLayerKeyValuePairs (user_id) {
  (async () => {
    logger.debug('refreshAllDiscoveryLayerKeyValuePairs');
    var obj = { user_id: user_id, message: "refresh-all-discovery-layer-key-value-pairs", data: {completed:false} };
    utility.sendWebhookMessage(obj);
    getTrustAnchorKeyValuePairCreated(user_id)
  })();
}
function refreshAllDiscoveryLayerKeyValuePairsUpdated (user_id) {
  (async () => {
    logger.debug('refreshAllDiscoveryLayerKeyValuePairsUpdated');
    var obj = { user_id: user_id, message: "refresh-all-discovery-layer-key-value-pairs", data: {completed:false} };
    utility.sendWebhookMessage(obj);
    getTrustAnchorKeyValuePairUpdated(user_id)
  })();
}
function refreshValidationsForKeyValuePairData (user_id) {
  (async () => {
    logger.debug('getValidationForKeyValuePairData');
  
    getValidationForKeyValuePairData(user_id)
  })();
}
/*
save all verified ta's to database
request: GET
params:
:user_id: 1
*/
// eg: refresh-all-verified-tas?user-id=1

app.get('/refresh-all-verified-tas', (req, res) => {
  var user_id = req.param('user_id');
  refreshAllVerifiedTAS(user_id);
  res.sendStatus(201);
});

function refreshAllVerifiedTAS (user_id) {
  (async () => {
    logger.debug('refreshAllTAS');
    var obj = { user_id: user_id, message: "refresh-all-verified-tas", data: {completed:false} };
    utility.sendWebhookMessage(obj);
    getVerifiedTrustAnchors(user_id)
  })();
}

// catch 404 and forward to error handler
app.use(function(req, res, next) {
    next(createError(404));
});


keyv.on('error', (err) => {

 logger.error('keyv connection error')
 logger.error(err)

});



app.listen(httpPort, async () => {

  await keyv.clear();

  let nonceCount = await trustAnchorWallet.getTransactionCount();
  //set nonceCount
  await keyv.set('nonceCount', nonceCount);

  logger.debug('listening on '+httpPort);
});

/**
 * taSetAttestation
 */

queue.taSetAttestation.on('completed', async function(job, response) {
    // A job taSetAttestation
    logger.debug('taSetAttestation:', job);
    logger.debug('response', response);

    queue.taSetAttestationStatusCheck.add(response, services.bull.opts);

});

queue.taSetAttestation.on('failed', function(job, err) {
    // A job failed.
    logger.error('taSetAttestation: failed job err', err);
});
queue.taSetAttestation.on('error', function(error) {
    // An error occured.
    logger.error('taSetAttestation: error job', error);
});



/**
 * taSetAttestationStatusCheck
 */

queue.taSetAttestationStatusCheck.on('completed', async function(job, response) {
    // A job taSetAttestationStatusCheck
    logger.debug('taSetAttestationStatusCheck:', job);
    logger.debug('response', response);
});

queue.taSetAttestationStatusCheck.on('failed', function(job, err) {
    queue.taEmptyTransaction.add(job.data, services.bull.opts);
    // A job failed.
    logger.error('taSetAttestationStatusCheck: failed job err', err);
});
queue.taSetAttestationStatusCheck.on('error', function(error) {
    // An error occured.
    logger.error('taSetAttestationStatusCheck: error job', error);
});




/**
 * taEmptyTransaction
 */
queue.taEmptyTransaction.on('completed', async function(job, response) {
    // A job taEmptyTransaction
    logger.debug('taEmptyTransaction:', job);
    logger.debug('response', response);
});

queue.taEmptyTransaction.on('failed', function(job, err) {
    // A job failed.
    logger.error('taEmptyTransaction: failed job err', err);
});
queue.taEmptyTransaction.on('error', function(error) {
    // An error occured.
    logger.error('taEmptyTransaction: error job', error);
});


/**
 * taEmptyTransactionStatusCheck
 */
queue.taEmptyTransactionStatusCheck.on('completed', async function(job, response) {
    // A job taEmptyTransaction
    logger.debug('taEmptyTransactionStatusCheck:', job);
    logger.debug('response', response);
});

queue.taEmptyTransactionStatusCheck.on('failed', function(job, err) {
    queue.taEmptyTransaction.add(job.data, services.bull.opts);
    // A job failed.
    logger.error('taEmptyTransactionStatusCheck: failed job err', err);
});
queue.taEmptyTransactionStatusCheck.on('error', function(error) {
    // An error occured.
    logger.error('taEmptyTransactionStatusCheck: error job', error);
});

(async function() {
  
    provider.on("block", (blockNumber) => {
        logger.debug("block# " + blockNumber);
    });

    TrustAnchorStorage.on("EVT_setAttestation",  (attestationKeccak, msg_sender, _identifiedAddress, _jurisdiction, _effectiveTime, _expiryTime, _publicData_0, _documentsMatrixEncrypted_0, _availabilityAddressEncrypted, _isManaged, _publicDataLength, _documentsMatrixEncryptedLength, event) => {
            logger.debug("event EVT_setAttestation");
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

            data['type'] = utility.convertComponentsFromHex(data['returnValues']['_publicData_0']);
            data['document'] = utility.convertComponentsFromHex(data['returnValues']['_documentsMatrixEncrypted_0']);
            data['document_decrypt'] = utility.convertComponentsFromHex(data['returnValues']['_documentsMatrixEncrypted_0']);

            data['memo'] = utility.convertComponentsFromHex(data['returnValues']['_availabilityAddressEncrypted']);

            var obj = {
                message: "tas-event",
                data: data
            };
            utility.sendWebhookMessage(obj);

            logger.debug(data);
    });

    TrustAnchorExtraData_Generic.on("EVT_setDataRetrievalParametersCreated",  (_trustAnchorAddress, _endpointName, _ipv4Address, event) => {
            logger.debug("event EVT_setDataRetrievalParametersCreated");
            data = {};
            data['transactionHash'] = event.transactionHash;
            data['blockNumber'] = event.blockNumber;
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
            utility.sendWebhookMessage(obj);

            logger.debug(data);
    });

    TrustAnchorExtraData_Unique.on("EVT_setTrustAnchorKeyValuePairCreated",  (_trustAnchorAddress, _keyValuePairName, _keyValuePairValue, event) => {
            logger.debug("event EVT_setDataRetrievalParametersCreated");
            data = {};
            data['transactionHash'] = event.transactionHash;
            data['blockNumber'] = event.blockNumber;
            data['event'] = "EVT_setTrustAnchorKeyValuePairCreated";
            data['returnValues'] = {};

            data['returnValues']['_trustAnchorAddress'] = _trustAnchorAddress;
            data['returnValues']['_keyValuePairName'] = _keyValuePairName;
            data['returnValues']['_keyValuePairValue'] = _keyValuePairValue;


            var obj = {
                message: "taedu-event",
                data: data
            };
            utility.sendWebhookMessage(obj);

            logger.debug(data);
    });

    TrustAnchorExtraData_Unique.on("EVT_setTrustAnchorKeyValuePairUpdated",  (_trustAnchorAddress, _keyValuePairName, _keyValuePairValue, event) => {
            logger.debug("event EVT_setTrustAnchorKeyValuePairUpdated");
            data = {};
            data['transactionHash'] = event.transactionHash;
            data['blockNumber'] = event.blockNumber;
            data['event'] = "EVT_setTrustAnchorKeyValuePairUpdated";
            data['returnValues'] = {};

            data['returnValues']['_trustAnchorAddress'] = _trustAnchorAddress;
            data['returnValues']['_keyValuePairName'] = _keyValuePairName;
            data['returnValues']['_keyValuePairValue'] = _keyValuePairValue;


            var obj = {
                message: "taedu-event",
                data: data
            };
            utility.sendWebhookMessage(obj);

            logger.debug(data);
    });

    //ONLY AVAILABLE ON SHYFT MAINNET
    if(TrustAnchorExtraData_Unique.address == "0xEA64A26723C779dEE63ba3Fbc1021b87e9E71568") {
        TrustAnchorExtraData_Unique.on("EVT_setValidationForKeyValuePairData",  (_trustAnchorAddress, _keyValuePairName, _validatorAddress, event) => {
              logger.debug("event EVT_setTrustAnchorKeyValuePairUpdated");
              data = {};
              data['transactionHash'] = event.transactionHash;
              data['blockNumber'] = event.blockNumber;
              data['event'] = "EVT_setValidationForKeyValuePairData";
              data['returnValues'] = {};

              data['returnValues']['_trustAnchorAddress'] = _trustAnchorAddress;
              data['returnValues']['_keyValuePairName'] = _keyValuePairName;
              data['returnValues']['_validatorAddress'] = _validatorAddress;


              var obj = {
                  message: "taedu-event",
                  data: data
              };
              utility.sendWebhookMessage(obj);

              logger.debug(data);
      });
    }
    

    TrustAnchorManager.on("EVT_verifyTrustAnchor",  (trustAnchorAddress, event) => {
            logger.debug("event EVT_verifyTrustAnchor");
            data = {};
            data['transactionHash'] = event.transactionHash;
            data['event'] = "EVT_verifyTrustAnchor";
            data['returnValues'] = {};

            data['returnValues']['trustAnchorAddress'] = trustAnchorAddress;


            var obj = { message: "tam-event", data: data };
            utility.sendWebhookMessage(obj);

            logger.debug(data);
    });
  })();
