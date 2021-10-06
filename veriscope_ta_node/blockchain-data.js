
const Web3 = require('web3');
const axios = require('axios');

const dotenv = require('dotenv');
dotenv.config();

const webhookUrl = process.env.WEBHOOK;
const testNetHttpUrl = process.env.HTTP;
const privateKey = process.env.WEBHOOK_CLIENT_SECRET;

let web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

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

  const instance = axios.create();

  instance.defaults.headers.common['X-WEBHOOK-TOKEN'] = privateKey;

  instance.post(webhookUrl, {
    obj:obj
  })
  .then((res) => {
    console.log('sendWebsocket success');
    console.log(`statusCode: ${res.statusCode}`)
    console.log(res)
  })
  .catch((error) => {
    console.log('sendWebsocket error');
    console.error(error)
  });
}

// node -e 'require("./blockchain-data").getAllAttestations()'
module.exports.getAllAttestations = function () {
    getAllAttestations();
};


function getAllAttestations() {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorStorage.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS);

    myContract.getPastEvents('EVT_setAttestation', {
       fromBlock: 0,
       toBlock: "latest",
    }, function(error, events){

        for (const event of events) {

          event['type'] = convertComponentsFromHex(event['returnValues']['_publicData_0']);
          event['document'] = convertComponentsFromHex(event['returnValues']['_documentsMatrixEncrypted_0']);
          event['document_decrypt'] = convertComponentsFromHex(event['returnValues']['_documentsMatrixEncrypted_0']);

          event['memo'] = convertComponentsFromHex(event['returnValues']['_availabilityAddressEncrypted']);

          var obj = { message: "tas-event", data: event };
          console.log(obj);
          sendWebhookMessage(obj);
        }


    })


    console.log('getAllAttestations result');
    console.log();

    })();
}
/*
EVT_setTrustAnchorKeyValuePairCreated
*/
// node -e 'require("./blockchain-data").getTrustAnchorKeyValuePairCreated()'
module.exports.getTrustAnchorKeyValuePairCreated = function () {
    getTrustAnchorKeyValuePairCreated();
};

function getTrustAnchorKeyValuePairCreated() {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorExtraData_Unique.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS);


    myContract.getPastEvents('EVT_setTrustAnchorKeyValuePairCreated', {
       fromBlock: 0,
       toBlock: "latest",
    }, function(error, events){

        for (const event of events) {
          console.log(event);

          var obj = { message: "taedu-event", data: event };
          sendWebhookMessage(obj);

        }


    })


    console.log('getTrustAnchorKeyValuePairCreated result');
    console.log();

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
          console.log(event);
          var ip_address = event['returnValues']['_ipv4Address'].join('.');
          event['ipv4_address'] = ip_address;

          var obj = { message: "taed-event", data: event };
          sendWebhookMessage(obj);

        }


    })


    console.log('getTrustAnchorKeyValuePairUpdated result');
    console.log();

    })();
}

// node -e 'require("./blockchain-data").getVerifiedTrustAnchors()'
module.exports.getVerifiedTrustAnchors = function () {
    getVerifiedTrustAnchors();
};


function getVerifiedTrustAnchors() {
    (async () => {

    let source = fs.readFileSync(process.env.CONTRACTS+'TrustAnchorManager.json');

    let contracts = JSON.parse(source);

    var myContract = new web3.eth.Contract(contracts.abi, process.env.TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS);

    myContract.getPastEvents('EVT_verifyTrustAnchor', {
       fromBlock: 0,
       toBlock: "latest",
    }, function(error, events){

        for (const event of events) {
          console.log(event);

          var obj = { message: "tam-event", data: event };
          sendWebhookMessage(obj);

        }


    })

    console.log('getVerifiedTrustAnchors result');
    console.log();

    })();
}


