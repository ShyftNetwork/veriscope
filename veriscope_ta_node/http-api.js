const express = require('express');
const cookieParser = require('cookie-parser');
const createError = require('http-errors');
const bodyParser = require('body-parser');
const winston = require('winston');

const ethers = require("ethers");
const Web3 = require('web3');
const dotenv = require('dotenv');
const services = require('./services');
const EthereumEvents = require('@0xhamachi/ethereum-events');
const fs = require('fs');
var _ = require('underscore');

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
  defaultMeta: {
    service: 'http-api'
  },
  transports: [
    //
    // - Write all logs with level `error` and below to `error.log`
    // - Write all logs with level `info` and below to `combined.log`
    //
    new winston.transports.File({
      filename: 'logs/http-api.error.log',
      level: 'error'
    }),
    new winston.transports.File({
      filename: 'logs/http-api.combined.log'
    }),
  ],
});

//
// If we're not in production then log to the `console` with the format:
// `${info.level}: ${info.message} JSON.stringify({ ...rest }) `
//

logger.add(new winston.transports.Console({
  format: winston.format.simple(),
}));

var ethereumEvents;
var startBlock;
const testNetHttpUrl = process.env.HTTP;
const httpPort = process.env.HTTP_API_PORT;
const webhookClientSecret = process.env.WEBHOOK_CLIENT_SECRET;

let provider = new ethers.providers.JsonRpcProvider(process.env.HTTP);
let customWsProvider = new ethers.providers.WebSocketProvider(process.env.WS);


let trustAnchorWallet = new ethers.Wallet(((process.env.TRUST_ANCHOR_PK).split(','))[0], provider);
let trustAnchorAccount = (((process.env.TRUST_ANCHOR_ACCOUNT)).split(','))[0];

let web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

function attachedContract(addr, fn) {
  artifact = require(process.env.CONTRACTS + fn);
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
  cookie: {
    secure: false
  }
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
  getToken: function fromCookieOrQuerystring(req) {
    // query token
    if (req.query && req.query.token) {
      req.session.token = req.query.token;
      return req.query.token;
    } else if (req.session.token) {
      return req.session.token;
    }

    return null;

  }
}));


app.use(function(err, req, res, next) {

  if (req.url.includes("arena") && err.name === 'UnauthorizedError') {
    req.session.destroy();
    return res.status(401).send('Invalid token');
  }

  next();
});



app.use('/', services.bull.arena);


/** Eth Sync Code **/
const Contract_TrustAnchorStorage = JSON.parse(fs.readFileSync(process.env.CONTRACTS + 'TrustAnchorStorage.json', 'utf8'));
const Contract_TrustAnchorExtraData_Unique = JSON.parse(fs.readFileSync(process.env.CONTRACTS + 'TrustAnchorExtraData_Unique.json', 'utf8'));
const Contract_TrustAnchorExtraData_Generic = JSON.parse(fs.readFileSync(process.env.CONTRACTS + 'TrustAnchorExtraData_Generic.json', 'utf8'));
const Contract_TrustAnchorManager = JSON.parse(fs.readFileSync(process.env.CONTRACTS + 'TrustAnchorManager.json', 'utf8'));

const contracts = [{
    name: 'TrustAnchorStorage',
    address: process.env.TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS,
    abi: Contract_TrustAnchorStorage.abi,
    events: ['EVT_setAttestation'] // optional event filter (default: all events)
  },
  {
    name: 'TrustAnchorExtraData_Unique',
    address: process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS,
    abi: Contract_TrustAnchorExtraData_Unique.abi,
    events: ['EVT_setTrustAnchorKeyValuePairCreated', 'EVT_setTrustAnchorKeyValuePairUpdated', 'EVT_setValidationForKeyValuePairData'] // optional event filter (default: all events)
  },
  {
    name: 'TrustAnchorExtraData_Generic',
    address: process.env.TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS,
    abi: Contract_TrustAnchorExtraData_Generic.abi,
    events: ['EVT_setDataRetrievalParametersCreated']
  },
  {
    name: 'TrustAnchorManager',
    address: process.env.TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS,
    abi: Contract_TrustAnchorManager.abi,
    events: ['EVT_verifyTrustAnchor']
  }
];

const options = {
  pollInterval: 1000, // period between polls in milliseconds (default: 13000)
  confirmations: 1, // n° of confirmation blocks (default: 12)
  chunkSize: 10000, // n° of blocks to fetch at a time (default: 10000)
  concurrency: 10, // maximum n° of concurrent web3 requests (default: 10)
  backoff: 1000 // retry backoff in milliseconds (default: 1000)
};


ethereumEvents = new EthereumEvents(web3, contracts, options);


async function startSync() {
  startBlock = await keyv.get('startBlock');
  if (startBlock === undefined) {
    startBlock = 1;
  }
  ethereumEvents.start(startBlock);
}


ethereumEvents.on('block.confirmed', async (blockNumber, events, done) => {
  //set startBlock
  logger.info(`block.confirmed`);
  logger.info(`startBlock: ${blockNumber}`);
  await keyv.set('startBlock', blockNumber);
  pipe(events, done)
});

ethereumEvents.on('error', err => {
  logger.error("error", err);
});

// Start startSync
startSync();


function transform_EVT_setAttestation(events, done) {
  events.forEach((evt) => {
    if (evt.name == 'EVT_setAttestation') {
      queue.taTraceAndParseTransaction.add({evt: evt}, services.bull.opts);
    }
  });
}

function transform_EVT_setTrustAnchorKeyValuePairCreatedOrupdated(events, done) {
  events.forEach((evt) => {

    let data = {};
    data['transactionHash'] = evt.transactionHash;
    data['blockNumber'] = evt.blockNumber;
    data['event'] = evt.name;
    data['returnValues'] = {};

    data['returnValues']['_trustAnchorAddress'] = evt.values._trustAnchorAddress;
    data['returnValues']['_keyValuePairName'] = evt.values._keyValuePairName;
    data['returnValues']['_keyValuePairValue'] = evt.values._keyValuePairValue;


    var obj = {
      message: "taedu-event",
      data: data
    };

    queue.taWebhookSend.add(obj, services.bull.opts);

  });
}

function transform_EVT_setDataRetrievalParametersCreated(events, done) {
  events.forEach((evt) => {
    let data = {};
    data['transactionHash'] = evt.transactionHash;
    data['blockNumber'] = evt.blockNumber;
    data['event'] = evt.name;
    data['returnValues'] = {};

    data['returnValues']['_trustAnchorAddress'] = evt.values._trustAnchorAddress;
    data['returnValues']['_endpointName'] = evt.values._endpointName;

    var ip_address = evt.values._ipv4Address.join('.');
    data['ipv4_address'] = ip_address;

    var obj = {
      message: "taed-event",
      data: data
    };
    queue.taWebhookSend.add(obj, services.bull.opts);
  });


}

function transform_EVT_setValidationForKeyValuePairData(events, done) {
  events.forEach((evt) => {
    let data = {};
    data['transactionHash'] = evt.transactionHash;
    data['blockNumber'] = evt.blockNumber;
    data['event'] = evt.name;
    data['returnValues'] = {};

    data['returnValues']['_trustAnchorAddress'] = evt.values._trustAnchorAddress;
    data['returnValues']['_keyValuePairName'] = evt.values._keyValuePairName;
    data['returnValues']['_validatorAddress'] = evt.values._validatorAddress;

    var obj = {
      message: "taedu-event",
      data: data
    };
    queue.taWebhookSend.add(obj, services.bull.opts);
  });
  done();
}


function transform_EVT_verifyTrustAnchor(events, done) {
  events.forEach((evt) => {
    let data = {};
    data['transactionHash'] = evt.transactionHash;
    data['blockNumber'] = evt.blockNumber;
    data['event'] = "EVT_verifyTrustAnchor";
    data['returnValues'] = {};
    data['returnValues']['trustAnchorAddress'] = evt.values.trustAnchorAddress;
    var obj = {
      message: "tam-event",
      data: data
    };
    queue.taWebhookSend.add(obj, services.bull.opts);
  });
}

function pipe(events, done) {
  if (events.length) {
    switch (events[0].name) {
      case 'EVT_setAttestation':
        transform_EVT_setAttestation(events, done);
        break;
      case 'EVT_setTrustAnchorKeyValuePairCreated':
        transform_EVT_setTrustAnchorKeyValuePairCreatedOrupdated(events, done);
        break;
      case 'EVT_setTrustAnchorKeyValuePairUpdated':
        transform_EVT_setTrustAnchorKeyValuePairCreatedOrupdated(events, done);
        break;
      case 'EVT_setDataRetrievalParametersCreated':
        transform_EVT_setDataRetrievalParametersCreated(events, done);
        break;
      case 'EVT_setValidationForKeyValuePairData':
        transform_EVT_setValidationForKeyValuePairData(events, done);
        break;
      case 'EVT_verifyTrustAnchor':
        transform_EVT_verifyTrustAnchor(events, done);
        break;
      default:
        done();
    }
    done();
  }
  done();
}







app.get('/refresh_event_sync', async (req, res) => {
  ethereumEvents.stop();
  let assignBlock = req.query.startBlock || 1;
  await keyv.set('startBlock', assignBlock);
  startBlock = await keyv.get('startBlock');
  logger.info(`refresh_event_sync`);
  logger.info(`startBlock: ${startBlock}`);
  ethereumEvents.start(startBlock);
  res.sendStatus(200);
});
// eg: create-new-user-account?user_id=1

app.get('/create-new-user-account', (req, res) => {

  var user_id = req.query.user_id;
  var TRUST_ANCHOR_PK = (process.env.TRUST_ANCHOR_PK).split(',');
  var TRUST_ANCHOR_ACCOUNT = (process.env.TRUST_ANCHOR_ACCOUNT).split(',');
  var TRUST_ANCHOR_PREFNAME = ((process.env.TRUST_ANCHOR_PREFNAME).replace(/"/g, '')).split(',');

  var accountDataLength = TRUST_ANCHOR_ACCOUNT.length;
  var preDataLength = TRUST_ANCHOR_PREFNAME.length;
  var pkDataLength = TRUST_ANCHOR_PK.length;
  var dataResult = {};
  var utilityStatus = true;

  if (accountDataLength == preDataLength && accountDataLength == pkDataLength) {

    for(var theNumber = 0 ; theNumber < accountDataLength ; theNumber++){

      try {
        var ta_sign_template = utility.TASign(process.env.SIGN_MESSAGE + "_TA", TRUST_ANCHOR_PK[theNumber]);
        var ta_public_key = utility.GetEthPublicKey(TRUST_ANCHOR_PK[theNumber]);
      } catch(error) {
        utilityStatus = false;
        logger.error('create-new-user-account error is :' + error )
        console.log(error);
      }

      var account_logger = {
        prefname: TRUST_ANCHOR_PREFNAME[theNumber],
        address: TRUST_ANCHOR_ACCOUNT[theNumber],
        private_key: "xxxxxxxxxx",
        public_key: ta_public_key,
        signature_hash: ta_sign_template
      };

      var data_logger = {
        account: account_logger
      };

      var obj_logger = {
        user_id: user_id,
        message: "create-new-user-account",
        data: data_logger
      };

      var account = {
        prefname: TRUST_ANCHOR_PREFNAME[theNumber],
        address: TRUST_ANCHOR_ACCOUNT[theNumber],
        private_key: TRUST_ANCHOR_PK[theNumber],
        public_key: ta_public_key,
        signature_hash: ta_sign_template
      };

      var data = {
        account: account
      };

      dataResult[theNumber] = data;
    }

    if (utilityStatus) {
      var obj = {
        user_id: user_id,
        message: "create-new-user-account",
        // data: data
        data : dataResult
      };

      logger.info(obj_logger);
      utility.sendWebhookMessage(obj);
      res.sendStatus(200);

    } else {
      var obj = {
          user_id: user_id,
          message: "create-new-user-account",
          data : 'wrongData'
      };
      res.sendStatus(203);
    }

  } else {
    var obj = {
      user_id: user_id,
      message: "create-new-user-account",
      data : 'missingData'
    };
    res.sendStatus(204);
  }

});

// eg: ta-is-verified?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-is-verified', (req, res) => {
  var user_id = req.query.user_id;
  var account = req.query.account;
  utility.getIsTrustAnchorVerified(user_id, account);
  res.sendStatus(200);
});




// eg: /ta-get-balance?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-get-balance', (req, res) => {
  var user_id = req.query.user_id;
  var account = req.query.account;
  utility.taGetBalance(user_id, account);
  res.sendStatus(200);
});

// eg: ta-set-key-value-pair?user_id=1&account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5&ta_key_name=ENTITY&ta_key_value=Abc%20Inc.

app.get('/ta-set-key-value-pair', (req, res) => {
  var user_id = req.query.user_id;
  var account = req.query.account;
  var key_name = req.query.ta_key_name;
  var key_value = req.query.ta_key_value;
  utility.taSetKeyValuePair(user_id, account, key_name, key_value);
  res.sendStatus(200);
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



// eg: ta-set-v3-attestation?attestation_type=WALLET&user_id=1&user_account=0x447832bc6303C87A7C7C0E3894a5C6848Aa24877&jurisdiction=196&effective_time=&expiry_time=&coin_address=0x6878e02e4782cd71af5d48e55e28f951eff5ec7c&coin_blockchain=ETH&coin_token=USDT&coin_memo=memo&ta_account=0x41dEaD8e323EEc29aDFD88272A8f5C7f1F8E53A5

app.get('/ta-set-v3-attestation', (req, res) => {
  var attestation_type = "WALLET";
  var user_id = req.query.user_id;
  var user_account = req.query.user_account;
  var jurisdiction = req.query.jurisdiction;
  var effective_time = req.query.effective_time;
  if (!effective_time) {
    effective_time = Math.floor(Date.now() / 1000) - (60 * 60 * 24 * (365 + 1)); // (in the past a year and a day)
  }
  var expiry_time = req.query.expiry_time;
  if (!expiry_time) {
    expiry_time = Math.floor(Date.now() / 1000) + (60 * 60 * 24 * (365 + 1)); // (in the future a year and a day)
  }

  var public_data = utility.convertToByte32(req.query.coin_memo);

  var availability_address_encrypted = " ".padStart(32, ' ');
  availability_address_encrypted = utility.convertToByte32(availability_address_encrypted);

  var coin_address = req.query.coin_address;
  logger.info('coin_address');
  logger.info(coin_address);

  var coin_token = req.query.coin_token;
  logger.info('coin_token');
  logger.info(coin_token);

  var coin_blockchain = req.query.coin_blockchain;
  logger.info('coin_blockchain');
  logger.info(coin_blockchain);

  var coin_type = coin_blockchain + "_" + coin_token;
  logger.info('coin_type');
  logger.info(coin_type);

  var travelRuleV3Template = utility.createTravelRuleV3Template(coin_address, coin_type);
  logger.info('travelRuleV3Template');
  logger.info(travelRuleV3Template);

  var encodedDocumentMatrix = utility.encodeDocumentMatrixInPlace(travelRuleV3Template);
  logger.info('encodedDocumentMatrix');
  logger.info(encodedDocumentMatrix);

  var encodedDocument = utility.encodeDocument(encodedDocumentMatrix.bitsMatrix, encodedDocumentMatrix.versionCode, encodedDocumentMatrix.encryptedData);
  logger.info('encodedDocument');
  logger.info(encodedDocument);

  var documents_matrix_encrypted = utility.convertToByte32(encodedDocument);
  logger.info('documents_matrix_encrypted');
  logger.info(documents_matrix_encrypted);

  var ta_account = req.query.ta_account;
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

app.get('/', (req, res) => {
  res.sendStatus(200);
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



app.get('/ta-nonce-count', async (req, res) => {

  logger.info('/ta-nonce-count');

  let baseNonce = await provider.getTransactionCount(trustAnchorAccount);

  let baseNoncePending = await provider.getTransactionCount(trustAnchorAccount, "pending");


  res.status(200).json({
    count: baseNonce,
    address: trustAnchorAccount,
    pendingCount: baseNoncePending
  });
});


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
  _.each((process.env.TRUST_ANCHOR_PK).split(","), async  function(pk)  {
    let signer = new ethers.Wallet(pk, provider);
    let addr   = await signer.getAddress();
    nonceCount = await signer.getTransactionCount();
    await keyv.set(`nonceCount_${addr}`, nonceCount);
   });
  logger.info('listening on ' + httpPort);
});

/**
 * taSetAttestation
 */

queue.taSetAttestation.on('completed', function(job, response) {
  // A job taSetAttestation
  logger.info(`taSetAttestation: ${job}`);
  logger.info(`response ${response}`);

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

queue.taSetAttestationStatusCheck.on('completed', function(job, response) {
  // A job taSetAttestationStatusCheck
  logger.info(`taSetAttestationStatusCheck: ${job}`);
  logger.info(`response ${response}`);
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
queue.taEmptyTransaction.on('completed', function(job, response) {
  // A job taEmptyTransaction
  logger.info(`taEmptyTransaction: ${job}`);
  logger.info(`response ${response}`);
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
queue.taEmptyTransactionStatusCheck.on('completed', function(job, response) {
  // A job taEmptyTransaction
  logger.info(`taEmptyTransactionStatusCheck: ${job}`);
  logger.info(`response ${response}`);
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


/**
 * taTraceAndParseTransaction
 */
queue.taTraceAndParseTransaction.on('completed', function(job, response) {
  // A job completed
  logger.info(`taTraceAndParseTransaction: ${job}`);
  logger.info(`response ${response}`);
  queue.taWebhookSend.add(response, services.bull.opts);

});

queue.taTraceAndParseTransaction.on('failed', function(job, err) {
  // A job failed.
  logger.error('taTraceAndParseTransaction: failed job err', err);
});
queue.taTraceAndParseTransaction.on('error', function(error) {
  // An error occured.
  logger.error('taTraceAndParseTransaction: error job', error);
});




/**
 * taWebhookSend
 */
queue.taWebhookSend.on('completed', function(job, response) {
  // A job completed
  logger.info(`taWebhookSend: ${job}`);
  //logger.debug('response', response);
});

queue.taWebhookSend.on('failed', function(job, err) {
  queue.taWebhookSend.add(job.data, services.bull.opts);
  // A job failed.
  logger.error('taWebhookSend: failed job err', err);
});
queue.taWebhookSend.on('error', function(error) {
  // An error occured.
  logger.error('taWebhookSend: error job', error);
});
