
const Web3 = require('web3');
const axios = require('axios');
const dotenv = require('dotenv');
const winston = require('winston');
const fs = require('fs')

dotenv.config();

const webhookUrl = process.env.WEBHOOK;
const testNetHttpUrl = process.env.HTTP;
const privateKey = process.env.WEBHOOK_CLIENT_SECRET;

let web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

const logger = winston.createLogger({
  level: (process.env.LOG_LEVEL || 'info'),
  format: winston.format.json(),
  maxsize: 512000000,
  maxFiles: 3,
  tailable: true,
  defaultMeta: { service: 'blockchain-data' },
  transports: [
    //
    // - Write all logs with level `error` and below to `error.log`
    // - Write all logs with level `info` and below to `combined.log`
    //
    new winston.transports.File({ filename: 'logs/blockchain-data.error.log', level: 'error' }),
    new winston.transports.File({ filename: 'logs/blockchain-data.combined.log' }),
  ],
});

//
// If we're not in production then log to the `console` with the format:
// `${info.level}: ${info.message} JSON.stringify({ ...rest }) `
//

logger.add(new winston.transports.Console({
    format: winston.format.simple(),
}));




function convertToByte32(string) {
  var temp = web3.utils.asciiToHex(string);
  temp = web3.utils.hexToBytes(temp);
  return temp;
}

function convertFromByte32(string) {
  var temp = web3.utils.bytesToHex(string);
  return web3.utils.hexToAscii(temp);
}

function convertComponentsFromHex(hex) {
  return web3.utils.hexToAscii(hex);
}

function sendWebhookMessage(obj) {
  return new Promise((resolve, reject) => {
    const instance = axios.create();

    instance.defaults.headers.common['X-WEBHOOK-TOKEN'] = privateKey;
    instance.post(webhookUrl, {
        obj: obj
      })
      .then((res) => {
        logger.debug('sendWebsocket success');
        logger.debug(`statusCode: ${res.statusCode}`)
        logger.debug(res)
        resolve(obj)
      })
      .catch((error) => {
        logger.error('sendWebsocket error');
        logger.error(error)
        resolve(obj)
      });
  })
}

// node -e 'require("./blockchain-data").getAllAttestations()'
module.exports.getAllAttestations = function (user_id = null) {
  getAllAttestations(user_id);
};


function getAllAttestations(user_id) {
  (async () => {
    
    let source = fs.readFileSync(process.env.CONTRACTS + 'TrustAnchorStorage.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS);

    myContract.getPastEvents('EVT_setAttestation', {
      fromBlock: 0,
      toBlock: "latest",
    }, async function (error, events) {


        for (const [i, event] of events.entries()) {
          event['type'] = convertComponentsFromHex(event['returnValues']['_publicData_0']);
          event['document'] = convertComponentsFromHex(event['returnValues']['_documentsMatrixEncrypted_0']);
          event['document_decrypt'] = convertComponentsFromHex(event['returnValues']['_documentsMatrixEncrypted_0']);

          event['memo'] = convertComponentsFromHex(event['returnValues']['_availabilityAddressEncrypted']);

          var obj = {
            message: "tas-event",
            data: event
          };

          logger.debug(obj);
          await sendWebhookMessage(obj);
          await sendWebhookMessage({
            user_id,
            message: "refresh-all-attestations",
            data: {
              completed: false,
              message: `Loaded ${i} of ${events.length} attestations`
            }
          });
        }
      
      sendWebhookMessage({
        user_id,
        message: "refresh-all-attestations",
        data: {
          completed: true
        }
      });
    })
    logger.debug('getAllAttestations result');
    logger.debug();

  })();
}

function chunkArray(array, chunkSize) {
  return Array.from({
      length: Math.ceil(array.length / chunkSize)
    },
    (_, index) => array.slice(index * chunkSize, (index + 1) * chunkSize)
  );
}
/*
EVT_setTrustAnchorKeyValuePairCreated
*/
// node -e 'require("./blockchain-data").getTrustAnchorKeyValuePairCreated()'
module.exports.getTrustAnchorKeyValuePairCreated = function (user_id = null) {
  getTrustAnchorKeyValuePairCreated(user_id);
};

function getTrustAnchorKeyValuePairCreated(user_id) {
  (async () => {

  let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorExtraData_Unique.json');

  let contracts = JSON.parse(source);

  var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS);


  myContract.getPastEvents('EVT_setTrustAnchorKeyValuePairCreated', {
     fromBlock: 0,
     toBlock: "latest",
  }, async function(error, events){
      
        for (const [i, event] of events.entries()) {
          logger.debug(event);

          var obj = { message: "taedu-event", data: event };
          await sendWebhookMessage(obj);
          await sendWebhookMessage({
            user_id,
            message: "refresh-all-discovery-layer-key-value-pairs",
            data: {
              completed: false,
              message: `Loaded ${i} of ${events.length} discovery layers`
            }
          });
        }
      
      sendWebhookMessage({
        user_id,
        message: "refresh-all-discovery-layer-key-value-pairs",
        data: {
          completed: true
        }
      });

  })


  logger.debug('getTrustAnchorKeyValuePairCreated result');
  logger.debug();

  })();
}

/*
EVT_setTrustAnchorKeyValuePairUpdated
*/
// node -e 'require("./blockchain-data").getTrustAnchorKeyValuePairUpdated()'
module.exports.getTrustAnchorKeyValuePairUpdated = function () {
    getTrustAnchorKeyValuePairUpdated();
};

function getTrustAnchorKeyValuePairUpdated() {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorExtraData_Unique.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS);


    myContract.getPastEvents('EVT_setTrustAnchorKeyValuePairUpdated', {
       fromBlock: 0,
       toBlock: "latest",
    }, function(error, events){

        for (const event of events) {
          logger.debug(event);

          var obj = { message: "taedu-event", data: event };
          sendWebhookMessage(obj);

        }


    })


    logger.debug('getTrustAnchorKeyValuePairUpdated result');
    logger.debug();

    })();
}
/*
EVT_setDataRetrievalParametersCreated
*/

// node -e 'require("./blockchain-data").getTrustAnchorDataRetrievalParametersCreated()'
module.exports.getTrustAnchorDataRetrievalParametersCreated = function () {
    getTrustAnchorDataRetrievalParametersCreated();
};

function getTrustAnchorDataRetrievalParametersCreated() {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorExtraData_Generic.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS);


    myContract.getPastEvents('EVT_setDataRetrievalParametersCreated', {
       fromBlock: 0,
       toBlock: "latest",
    }, function(error, events){

        for (const event of events) {
          logger.debug(event);
          var ip_address = event['returnValues']['_ipv4Address'].join('.');
          event['ipv4_address'] = ip_address;

          var obj = { message: "taed-event", data: event };
          sendWebhookMessage(obj);

        }


    })


    logger.debug('getTrustAnchorKeyValuePairUpdated result');
    logger.debug();

    })();
}

// node -e 'require("./blockchain-data").getVerifiedTrustAnchors()'
module.exports.getVerifiedTrustAnchors = function (user_id = null) {
    getVerifiedTrustAnchors(user_id);
};


function getVerifiedTrustAnchors(user_id) {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorManager.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS);

    myContract.getPastEvents('EVT_verifyTrustAnchor', {
       fromBlock: 0,
       toBlock: "latest",
    }, async function(error, events){

      
        for (const [i, event] of events.entries()) {
          logger.debug(event);

          var obj = { message: "tam-event", data: event };
          await sendWebhookMessage(obj);

          await sendWebhookMessage({
            user_id,
            message: "refresh-all-verified-tas",
            data: {
              completed: false,
              message: `Loaded ${i} of ${events.length} verified trust anchors`
            }
          });
        }
      
      sendWebhookMessage({
        user_id,
        message: "refresh-all-verified-tas",
        data: {
          completed: true
        }
      });


    })

    logger.debug('getVerifiedTrustAnchors result');
    logger.debug();

    })();
}


// node -e 'require("./blockchain-data").getValidationForKeyValuePairData()'
module.exports.getValidationForKeyValuePairData = function (user_id = null) {
    getValidationForKeyValuePairData(user_id);
};


function getValidationForKeyValuePairData(user_id) {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorExtraData_Unique.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS);

    myContract.getPastEvents('EVT_setValidationForKeyValuePairData', {
       fromBlock: 0,
       toBlock: "latest",
    }, async function(error, events){

      
        for (const [i, event] of events.entries()) {
          logger.debug(event);

          var obj = { message: "taedu-event", data: event };
          await sendWebhookMessage(obj);

          await sendWebhookMessage({
            user_id,
            message: "get-validation-for-key-value-pair-data",
            data: {
              completed: false,
              message: `Loaded ${i} of ${events.length} validations`
            }
          });
        }
      
      sendWebhookMessage({
        user_id,
        message: "get-validation-for-key-value-pair-data",
        data: {
          completed: true
        }
      });


    })

    logger.debug('getValidationForKeyValuePairData result');
    logger.debug();

    })();
}

