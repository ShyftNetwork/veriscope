const ethers = require("ethers");
const Web3 = require('web3');
const dotenv = require('dotenv');
dotenv.config();

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

let TrustAnchorManager = attachedContract(process.env.TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS, 'TrustAnchorManager.json');
let TrustAnchorExtraData_Unique = attachedContract(process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS, 'TrustAnchorExtraData_Unique.json');

// node -e 'require("./dla-helper").getBalance(address)'
module.exports.getBalance = function (address) {
  getBalance(address);
};

function getBalance(address) {
  web3.eth.getBalance(address).then(console.log);
}

// VASP ONBOARDING

// node -e 'require("./validation-helper").getIsTrustAnchorVerified(account)'
module.exports.getIsTrustAnchorVerified = function (account) {
  getIsTrustAnchorVerified(account);
};

function getIsTrustAnchorVerified(account) {
  (async () => {
    result = await TrustAnchorManager.isTrustAnchorVerified(account);

    console.log('isTrustAnchorVerified result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").doesTrustAnchorHaveUniqueExtraData(account)'
// doesTrustAnchorHaveUniqueExtraData result
// true
module.exports.doesTrustAnchorHaveUniqueExtraData = function (account) {
  doesTrustAnchorHaveUniqueExtraData(account);
};

function doesTrustAnchorHaveUniqueExtraData(account) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.doesTrustAnchorHaveUniqueExtraData(account);

    console.log('doesTrustAnchorHaveUniqueExtraData result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").getTrustAnchorNumberOfKeyValuePairs(account)'
// getTrustAnchorNumberOfKeyValuePairs result
// BigNumber { _hex: '0x01', _isBigNumber: true }
module.exports.getTrustAnchorNumberOfKeyValuePairs = function (account) {
  getTrustAnchorNumberOfKeyValuePairs(account);
};

function getTrustAnchorNumberOfKeyValuePairs(account) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.getTrustAnchorNumberOfKeyValuePairs(account);

    console.log('getTrustAnchorNumberOfKeyValuePairs result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").getTrustAnchorKeyValuePairNameByIndex(account, index)'
// getTrustAnchorKeyValuePairNameByIndex result
// [ true, 'ENTITY', doesExist: true, keyValuePairName: 'ENTITY' ]
module.exports.getTrustAnchorKeyValuePairNameByIndex = function (account, index) {
  getTrustAnchorKeyValuePairNameByIndex(account, index);
};

function getTrustAnchorKeyValuePairNameByIndex(account, index) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.getTrustAnchorKeyValuePairNameByIndex(account, index);

    console.log('getTrustAnchorKeyValuePairNameByIndex result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").getTrustAnchorKeyValuePairValue(account, key)'
// getTrustAnchorKeyValuePairValue result
// [ true, 'M1 Inc.', doesExist: true, keyValuePairValue: 'M1 Inc.' ]
module.exports.getTrustAnchorKeyValuePairValue = function (account, key) {
  getTrustAnchorKeyValuePairValue(account, key);
};

function getTrustAnchorKeyValuePairValue(account, key) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.getTrustAnchorKeyValuePairValue(account, key);

    console.log('getTrustAnchorKeyValuePairValue result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").getNumValidationsForLatestKeyValuePair(account, key)'
// getNumValidationsForLatestKeyValuePair result
// BigNumber { _hex: '0x00', _isBigNumber: true }
module.exports.getNumValidationsForLatestKeyValuePair = function (account, key) {
  getNumValidationsForLatestKeyValuePair(account, key);
};

function getNumValidationsForLatestKeyValuePair(account, key) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.getNumValidationsForLatestKeyValuePair(account, key);

    console.log('getNumValidationsForLatestKeyValuePair result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").getKeyValuePairCurrentNonce(account, key)'
// getKeyValuePairCurrentNonce result
// BigNumber { _hex: '0x00', _isBigNumber: true }
module.exports.getKeyValuePairCurrentNonce = function (account, key) {
  getKeyValuePairCurrentNonce(account, key);
};

function getKeyValuePairCurrentNonce(account, key) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.getKeyValuePairCurrentNonce(account, key);

    console.log('getKeyValuePairCurrentNonce result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").setValidationForKeyValuePairData(account, key, nonce)'
/* 
{
  nonce: 4,
  gasPrice: BigNumber { _hex: '0x4190ab00', _isBigNumber: true },
  gasLimit: BigNumber { _hex: '0x016a78', _isBigNumber: true },
  to: '0xEA64A26723C779dEE63ba3Fbc1021b87e9E71568',
  value: BigNumber { _hex: '0x00', _isBigNumber: true },
  data: '0xf8ec4fc30000000000000000000000006436bc677854de915316d05130d16deb1478fb8e000000000000000000000000000000000000000000000000000000000000006000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000006444f4d41494e0000000000000000000000000000000000000000000000000000',
  chainId: 7341,
  v: 14718,
  r: '0x72a5abebdeb1722edead0d0a3813abf4a5786433eedae532c8a8e6f2a864ef5b',
  s: '0x41093b8a7ddb72c8bb9b62afc97507637a703a54ede1cc2334a8f841dfc0b01b',
  from: '0x2B9BC429909A5710103Ff2e54680bc68266C8B3E',
  hash: '0x82e0dc32d2e00002abd320eac4acc061ac0051a5f7740f8afbe9eb88fe678410',
  type: null,
  confirmations: 0,
  wait: [Function (anonymous)]
}
*/
module.exports.setValidationForKeyValuePairData = function (account, key, nonce) {
  setValidationForKeyValuePairData(account, key, nonce);
};

function setValidationForKeyValuePairData(account, key, nonce) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.setValidationForKeyValuePairData(account, key, nonce);

    console.log('setValidationForKeyValuePairData result');
    console.log(result);

  })();
}

// node -e 'require("./validation-helper").getValidationsArrayForKeyValuePairNonce(account, key, nonce)'
// getValidationsArrayForKeyValuePairNonce result
// [ '0x2B9BC429909A5710103Ff2e54680bc68266C8B3E' ]
module.exports.getValidationsArrayForKeyValuePairNonce = function (account, key, nonce) {
  getValidationsArrayForKeyValuePairNonce(account, key, nonce);
};

function getValidationsArrayForKeyValuePairNonce(account, key, nonce) {
  (async () => {
    result = await TrustAnchorExtraData_Unique.getValidationsArrayForKeyValuePairNonce(account, key, nonce);

    console.log('getValidationsArrayForKeyValuePairNonce result');
    console.log(result);

  })();
}



// node -e 'require("./validation-helper").getValidationForKeyValuePairData()'
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
            console.log(event);
          }
      })

    })();
}

