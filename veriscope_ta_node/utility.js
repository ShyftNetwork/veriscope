const Web3 = require('web3');
const BN = require('bn.js');
const ethers = require("ethers");
const pako = require('pako');
const web3_eth_abi = require("web3-eth-abi");
const axios = require('axios');
const EthCrypto = require('eth-crypto');
const EthUtil = require('ethereumjs-util');
const bitcoinjs_lib = require('bitcoinjs-lib');
const bitgo_utxo_lib = require('bitgo-utxo-lib');
const monerojs = require("monero-javascript");
const winston = require('winston');
const dotenv = require('dotenv');
dotenv.config();
const Keyv = require('keyv');
const keyv = new Keyv(process.env.REDIS_URI);


const testNetHttpUrl = process.env.HTTP;
const testNetWsUrl = process.env.WS;
const webhookUrl = process.env.WEBHOOK;
const httpPort = process.env.HTTP_API_PORT;
const webhookClientSecret = process.env.WEBHOOK_CLIENT_SECRET;

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

const logger = winston.createLogger({
  level: (process.env.LOG_LEVEL || 'info'),
  format: winston.format.json(),
  maxsize: 512000000,
  maxFiles: 3,
  tailable: true,
  defaultMeta: { service: 'utility' },
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

function bufferToHex(buffer) {
    var result = EthUtil.bufferToHex(buffer);

    return result;
}

module.exports =   {
    decodeDocument: function (_documentEncoded) {

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
          logger.error(err);
          return null;
      }

    },
    decodeDocumentMatrixInPlace: function (_dataTemplate) {

      if (_dataTemplate.versionCode == 2) {

          let bytesDocumentMatrixEncodedPacked = ethers.utils.toUtf8String(_dataTemplate.encryptedData);

          let encodedDocumentMatrixDataPacked = JSON.parse(bytesDocumentMatrixEncodedPacked);

          return encodedDocumentMatrixDataPacked;

      } else {
          return null;
      }

    },
    unpackCryptoAddress: function (decodeDocumentMatrix) {
      return ethers.utils.toUtf8String(decodeDocumentMatrix.data);
    },
    unpackDocumentsMatrixEncrypted: function(documentMatrix) {

      let decodedDocument = this.decodeDocument(documentMatrix);

      let decodeDocumentMatrix = this.decodeDocumentMatrixInPlace(decodedDocument);

      let unpackedCryptoAddress = this.unpackCryptoAddress(decodeDocumentMatrix);

      return unpackedCryptoAddress;
    },
    packDocumentsMatrixEncrypted: function (address) {

        let addressBuffer = new Buffer(address);

        let travelRuleTemplate = this.createTravelRuleTemplate(addressBuffer);

        let encodedDocumentMatrix = this.encodeDocumentMatrixInPlace(travelRuleTemplate);

        let encodedDocument = this.encodeDocument(encodedDocumentMatrix.bitsMatrix, encodedDocumentMatrix.versionCode, encodedDocumentMatrix.encryptedData);

        return encodedDocument;
    },
    convertComponentsFromHex: function (hex) {
        return web3.utils.hexToAscii(hex);
    },
    convertToByte32: function (string) {
        var temp = web3.utils.asciiToHex(string);
        temp = web3.utils.hexToBytes(temp);
        return temp;
    },
    convertFromByte32: function (string) {
      var temp = web3.utils.bytesToHex(string);
      return web3.utils.hexToAscii(temp);
    },
    createTravelRuleTemplate: function (_documentMatrixDataBytes) {

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
    },
    encodeDocumentMatrixInPlace: function (_dataTemplate) {

        if (_dataTemplate.versionCode === 2 && _dataTemplate.documentMatrixDataBytes != null) {

            let encodedDocumentMatrixDataPacked = JSON.stringify(_dataTemplate.documentMatrixDataBytes);

            let bytesDocumentMatrixEncodedPacked = ethers.utils.toUtf8Bytes(encodedDocumentMatrixDataPacked);

            _dataTemplate["encryptedData"] = bytesDocumentMatrixEncodedPacked;

            return _dataTemplate;
        } else {
            return null;
        }
    },
    encodeDocument: function (_bitsMatrix, _versionCode, _encryptedData) {
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
    },
    trustAnchorGetAttestationArrayForTrustAnchorAccount: async function (account) {
        result = await TrustAnchorStorage.getAttestationKeccakArrayForTrustAnchor(account);
            logger.debug('getAttestationKeccakArrayForTrustAnchor result');
            logger.debug(result);

        var obj = {request: 'ta-get-attestation-array-for-ta-account', result: result};
        this.sendWebhookMessage(obj);
    },
    sendWebhookMessage: function (obj) {

      const instance = axios.create();

      instance.defaults.headers.common['X-WEBHOOK-TOKEN'] = webhookClientSecret;

      instance.post(webhookUrl, {
        obj:obj
      })
      .then((res) => {
        logger.debug('sendWebsocket success');
        logger.debug(`statusCode: ${res.statusCode}`)
        logger.debug(res)
      })
      .catch((error) => {
        logger.error('sendWebsocket error');
        logger.error(error)
      });
    },
    createMoneroAccount: async function () {

     let wallet = await monerojs.createWalletKeys({
        networkType: "mainnet"
     });

     var address = await wallet.getAddress(0, 0);
     var publicKey = await wallet.getPublicViewKey();
     var privateKey = await wallet.getPrivateViewKey();

     return {"address":address, "public_key": publicKey, "private_key": privateKey};

    },
    createZcashAccount: function() {

        let ecPair1 = bitgo_utxo_lib.ECPair.makeRandom({ network: bitgo_utxo_lib.networks.zcash });
        var publicKey = ecPair1.getPublicKeyBuffer().toString('hex');
        var privateKey = ecPair1.toWIF();
        var address = ecPair1.getAddress();

        return {"address":address, "public_key": publicKey, "private_key": privateKey};
    },
    createEthereumAccount: function () {
        var result = web3.eth.accounts.create();

        var address = result['address'];
        var privateKey = result['privateKey'];
        var publicKey = EthCrypto.publicKeyByPrivateKey(
            privateKey
        );

        return {"address":address, "public_key": publicKey, "private_key": privateKey};
    },
    createBitcoinAccount: function () {

      var keyPair = bitcoinjs_lib.ECPair.makeRandom();
      var { address } = bitcoinjs_lib.payments.p2pkh({ pubkey: keyPair.publicKey });
      var publicKey = keyPair.publicKey.toString("hex");
      var privateKey = keyPair.toWIF();

      return {"address":address, "public_key": publicKey, "private_key": privateKey};

    },
    TASign: function(message, privateKey) {

      logger.debug("TASign");
      logger.debug(message);

      var messageBuffer = new Buffer(message);
      logger.debug(messageBuffer);
      var hash = EthUtil.hashPersonalMessage(messageBuffer);
      logger.debug(hash);
      var ecprivkey = Buffer.from(privateKey, 'hex');
      logger.debug(ecprivkey);
      var result = EthUtil.ecsign(hash, ecprivkey, 1);
      logger.debug(result);
      var template = {};

      template['SignatureHash'] = bufferToHex(hash);
      template['Signature'] = { r: bufferToHex(result['r']), s: bufferToHex(result['s']), v: bufferToHex(result['v']) };
      logger.debug(template);

      return template;

    },
    GetEthPublicKey: function(privateKey) {

      logger.debug("GetEthPublicKey");
      logger.debug(privateKey);

      if (Buffer.isBuffer(privateKey)) {
          privateKey = "0x" + privateKey.toString('hex');
      }

      const publicKey = EthCrypto.publicKeyByPrivateKey(
          privateKey
      );

      return publicKey;

    },
    taGetAttestationComponents: async function (attestation_hash) {

        result = await TrustAnchorStorage.getAttestationComponents(attestation_hash);
        logger.debug('getAttestationComponents result');
        logger.debug(result);

        var documentsMatrixEncrypted = this.convertComponentsFromHex(result['documentsMatrixEncrypted']);
        documentsMatrixEncrypted = this.unpackDocumentsMatrixEncrypted(documentsMatrixEncrypted);


        var dict = {};
        dict['type'] = this.convertComponentsFromHex(result['publicData']);
        dict['document'] = documentsMatrixEncrypted;
        dict['memo'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']);
        dict['trustAnchorAddress'] = result['trustAnchorAddress'];
        dict['attestation_hash'] = attestation_hash;

        var obj = {message: 'ta-get-attestation-components', data: dict};
        this.sendWebhookMessage(obj);
    },
    trustAnchorGetAttestationArrayForUserAccount: async function(account){
       result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
          logger.debug('getAttestationKeccakArrayForIdentifiedAddress result');
          logger.debug(result);

       var obj = {request: 'ta-get-attestation-array-for-user-account', result: result};
       this.sendWebhookMessage(obj);
    },
    taGetAttestationComponentsInKeccakArray: async function (user_id, account, index) {

      result = await TrustAnchorStorage.getAttestationComponentsInKeccakArray(account, index);
      logger.debug('getGraphConstructableAttestationInKeccakArray result');

      var list = [];

      var documentsMatrixEncrypted = this.convertComponentsFromHex(result['documentsMatrixEncrypted']);

      documentsMatrixEncrypted = this.unpackDocumentsMatrixEncrypted(documentsMatrixEncrypted);
      var dict = {};
      dict['document_encode'] = documentsMatrixEncrypted;
      dict['jurisdiction'] = result['jurisdiction'];
      dict['trustAnchorAddress'] = result['trustAnchorAddress'];
      dict['publicData'] = result['publicData'];
      dict['availabilityAddressEncrypted'] = result['availabilityAddressEncrypted'];
      dict['documentsMatrixEncrypted'] = result['documentsMatrixEncrypted'];

      dict['type'] = this.convertComponentsFromHex(result['publicData']);
      dict['document'] = documentsMatrixEncrypted;
      dict['document_decrypt'] = documentsMatrixEncrypted;
      dict['memo'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']);
      dict['user_address'] = account;

      logger.debug(dict);
      list.push(dict);
      var obj = { user_id: user_id, message: "ta-get-attestation-components-in-array", data: list };
      this.sendWebhookMessage(obj);
    },
    taGetAttestationKeccakArrayForIdentifiedAddress: async function(user_id, account) {
      result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
      logger.debug('getAttestationKeccakArrayForIdentifiedAddress result');
      logger.debug(result);
      var list = [];
      for(var i = 0; i < result.length; i++) {
        var dict = {hash: result[i]};
        list.push(dict);
      }
      var obj = { user_id: user_id, message: "ta-get-user-attestations", data: list };
      this.sendWebhookMessage(obj);
    },
    getIsTrustAnchorVerified: async function (user_id, account) {

        result = await TrustAnchorManager.isTrustAnchorVerified(account);

        logger.debug('isTrustAnchorVerified result');
        logger.debug(result);

        var obj = { user_id: user_id, message: "ta-is-verified", data: result };
        this.sendWebhookMessage(obj);
    },
    taGetBalance: async function (user_id, account) {
        result = await web3.eth.getBalance(account);
        logger.debug('getBalance result');
        logger.debug(result);

        var obj = { user_id: user_id, message: "ta-get-balance", data: result };
        this.sendWebhookMessage(obj);
    },
    taRegisterJurisdiction: async function (user_id, account_address, jurisdiction) {

        try {
          result = await TrustAnchorManager.setupTrustAnchorJurisdiction(jurisdiction);
          logger.debug('setupTrustAnchorJurisdiction result');
          logger.debug(result);

          var value = result['value'].toNumber();
          logger.debug(value);
          var obj = { user_id: user_id, message: "ta-register-jurisdiction", data: value };
          this.sendWebhookMessage(obj);
        } catch(error) {
          logger.error("caught error");
          logger.error(error.message);
          var list = {message: error.message};
          var obj = { user_id: user_id, message: "ta-register-jurisdiction-error", data: list };
          this.sendWebhookMessage(obj);
        }

    },
    taGetTrustAnchorJurisdiction: async function(user_id, account_address) {
          result = await TrustAnchorManager.getTrustAnchorJurisdiction(account_address);
          logger.debug('getTrustAnchorJurisdiction result');
          logger.debug(result);

          var value = result['value'].toNumber();
          logger.debug(value);
          var obj = { user_id: user_id, message: "ta-get-jurisdiction", data: value };
          this.sendWebhookMessage(obj);

    },

    trustAnchorGetUniqueAddress: async function(account) {
        result = await TrustAnchorManager.getUniqueTrustAnchorExtraDataAddress(account);
        logger.debug('getUniqueTrustAnchorExtraDataAddress result');
        logger.debug(result);

        var obj = {request: 'ta-get-unique-address', result: result};
        this.sendWebhookMessage(obj);
    },
    taSetKeyValuePair: async function(user_id, account, key_name, key_value) {
        result = await TrustAnchorExtraData_Unique.setTrustAnchorKeyValuePair(key_name, key_value);
        logger.debug('setTrustAnchorKeyValuePair result');
        logger.debug(result);
        var value = result['value'].toNumber();
        logger.debug(value);
        var obj = { user_id: user_id, message: "ta-set-key-value-pair", data: value };
        this.sendWebhookMessage(obj);
    },
    taSetUniqueAddress: async function (user_id, account) {
        result = await TrustAnchorManager.setUniqueTrustAnchorExtraDataAddress(process.env.TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS)
        logger.debug('setUniqueTrustAnchorExtraDataAddress result');
        logger.debug(result);
        var value = result['value'].toNumber();
        logger.debug(value);
        var obj = { user_id: user_id, message: "ta-set-unique-address", data: value };
        this.sendWebhookMessage(obj);
    },
    trustAnchorGetNumberOfKeyValuePairs: async function (account) {
        result = await TrustAnchorExtraData_Unique.getTrustAnchorNumberOfKeyValuePairs(account);
        logger.debug('getTrustAnchorNumberOfKeyValuePairs result');
        logger.debug(result);

        var obj = {request: 'ta-get-number-of-key-value-pairs', result: result};
        this.sendWebhookMessage(obj);
    },
    trustAnchorGetKeyValuePairNameByIndex: async function(account, index) {

        result = await TrustAnchorExtraData_Unique.getTrustAnchorKeyValuePairNameByIndex(account, index);
        logger.debug('getTrustAnchorKeyValuePairNameByIndex result');
        logger.debug(result);

        var obj = {request: 'ta-get-key-value-pair-name-by-index', result: result};
        this.sendWebhookMessage(obj);
    },
    trustAnchorGetKeyPairValue: async function (account, key) {
        result = await TrustAnchorExtraData_Unique.getTrustAnchorKeyValuePairValue(account, key);
        logger.debug('getTrustAnchorKeyValuePairNameByIndex result');
        logger.debug(result);

        var obj = {request: 'ta-get-key-pair-value', result: result};
        this.sendWebhookMessage(obj);
    },
    taSetAttestation: async function (attestation_type, user_id, user_address, jurisdiction, effective_time, expiry_time, public_data, documents_matrix_encrypted, availability_address_encrypted, is_managed, ta_address) {

          let nonceCount = await keyv.get('nonceCount');

          result = await TrustAnchorStorage.setAttestation(
            user_address, jurisdiction, effective_time, expiry_time, public_data, documents_matrix_encrypted, availability_address_encrypted, is_managed,
            { nonce: nonceCount}
          );
          logger.debug('setAttestation result');
          logger.debug(result);

          result['documents_matrix_encrypted'] = this.convertFromByte32(documents_matrix_encrypted);
          result['public_data'] = this.convertFromByte32(public_data);
          result['availability_address_encrypted'] = this.convertFromByte32(availability_address_encrypted).trim();
          result['attestation_type'] = attestation_type;
          result['user_address'] = user_address;
          result['ta_address'] = ta_address;
          result['value'] = result['value'].toNumber();
          logger.debug(result);
          var obj = { user_id: user_id, message: "ta-set-attestation", data: result};
          //set nonceCount
          await keyv.set('nonceCount', nonceCount+1);

          return obj;
    },
    trustAnchorGetAttestationArrayForUserAccount: async function (account) {

        result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
            logger.debug('getAttestationKeccakArrayForIdentifiedAddress result');
            logger.debug(result);

        var obj = {request: 'ta-get-attestation-array-for-user-account', result: result};
        this.sendWebhookMessage(obj);

    },
    createEmpyTransaction: async function (nonce, chainId) {

      let tx = {
        to: trustAnchorAccount,
        value: ethers.utils.parseEther("0.00"),
        chainId: chainId,
        nonce: nonce
      };

      // Signing a transaction
      let signedTX = await trustAnchorWallet.signTransaction(tx);

      // Sending ether
      let result = await provider.sendTransaction(tx);

      logger.debug('createEmpyTransaction result');
      logger.debug(result);

      var obj = {request: 'ta-create-empty-transaction', result: result};

      return obj;
    },
    waitForTransaction: async function(hash, confirmations = 2 ,timeout = 60000){
        return  provider.waitForTransaction(hash, confirmations, timeout);
    }
};
