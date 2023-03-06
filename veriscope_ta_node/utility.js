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
const _ = require('underscore');
dotenv.config();
const Keyv = require('keyv');
const keyv = new Keyv(process.env.REDIS_URI);


const testNetHttpUrl = process.env.HTTP;
const testNetWsUrl = process.env.WS;
const webhookUrl = process.env.WEBHOOK;
const httpPort = process.env.HTTP_API_PORT;
const webhookClientSecret = process.env.WEBHOOK_CLIENT_SECRET;

const versionCodeLength = 4;
const coinTypeLength = 32;
const coinAddressHexLengthLength = 2;

let provider = new ethers.providers.JsonRpcProvider(process.env.HTTP);
let trustAnchorWallet = new ethers.Wallet(((process.env.TRUST_ANCHOR_PK).split(','))[0], provider);
let trustAnchorAccount = ((process.env.TRUST_ANCHOR_ACCOUNT).split(','))[0];

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

      if (_dataTemplate.versionCode == 2 || _dataTemplate.versionCode == 3) {

          let bytesDocumentMatrixEncodedPacked = ethers.utils.toUtf8String(_dataTemplate.encryptedData);

          let encodedDocumentMatrixDataPacked = JSON.parse(bytesDocumentMatrixEncodedPacked);

          return encodedDocumentMatrixDataPacked;

      } else {
          return null;
      }

    },
    contractInterface: function(contract) {
      return new ethers.utils.Interface(contract.abi);
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
    formatAndPadVersionCodeTo64Bits: function (_versionCode) {

        return ethers.utils.hexZeroPad(ethers.utils.hexlify(_versionCode), versionCodeLength);
    },
    formatGenericAddressToBytes: function (_coinAddress) {
        let output = 0x0;
        let outputLength = 0;

        if (ethers.utils.isHexString(_coinAddress)) {
            let addressBigNumberHex = ethers.utils.hexlify(_coinAddress);
            let coinAddressHexLength = ethers.utils.hexDataLength(addressBigNumberHex);

            output = addressBigNumberHex;

            outputLength = ethers.utils.hexZeroPad(ethers.utils.hexlify(coinAddressHexLength), 2);

        } else {

            let addressStringToHex = ethers.utils.hexlify(ethers.utils.toUtf8Bytes(_coinAddress));
            let coinAddressHexLength = ethers.utils.hexDataLength(addressStringToHex);

            output = addressStringToHex;

            outputLength = ethers.utils.hexZeroPad(ethers.utils.hexlify(coinAddressHexLength), 2);
        }

        return {"bytesLength": outputLength, "bytesOutput": output};
    },
    formatAndPadCoinTypeTo256Bits: function (_coinType) {

        let coinTypeBigNumberHex = ethers.utils.formatBytes32String(_coinType);

        return ethers.utils.hexZeroPad(coinTypeBigNumberHex, 32);
    },
    createTravelRuleV3Template: function(_coinAddress, _coinType) {
        let documentsMatrixBitString = "";

        let documentsMatrixBits = new Array(256);

        documentsMatrixBitString += "0";

        //bits 0 to 253 disabled
        for (let i = 0 ; i < 254; i++) {
            documentsMatrixBitString += "0";
        }
        //254th bit set
        documentsMatrixBitString += "1";
        //255th bit not set
        documentsMatrixBitString += "0";

        let versionCode = 3;

        let versionCodePaddedAndFormatted = this.formatAndPadVersionCodeTo64Bits(versionCode);

        let outputAddressDict = this.formatGenericAddressToBytes(_coinAddress);
        let coinTypePaddedAndFormatted = this.formatAndPadCoinTypeTo256Bits(_coinType);

        let outputAddressBytesLength = outputAddressDict.bytesLength;
        let outputAddressHex = outputAddressDict.bytesOutput;

        let outputAddressBytesLengthDecoded = ethers.BigNumber.from(outputAddressBytesLength);

        let documentMatrixDataBytes = ethers.utils.concat([versionCodePaddedAndFormatted, coinTypePaddedAndFormatted, outputAddressBytesLength, outputAddressHex]);

        let documentMatrixBignum = new BN(documentsMatrixBitString, 2);

        let hexlifiedData = ethers.utils.hexlify(documentMatrixDataBytes);

        let dict = {"bitsMatrix" : documentMatrixBignum, "versionCode" : versionCode, "documentMatrixDataBytes" : documentMatrixDataBytes};

        return {"bitsMatrix" : documentMatrixBignum, "versionCode" : versionCode, "documentMatrixDataBytes" : documentMatrixDataBytes};
    },
    decodeTravelRuleV3Template: function (_documentMatrixData) {

        let hexlifiedData = ethers.utils.hexlify(_documentMatrixData);

        let versionCode = ethers.utils.hexDataSlice(hexlifiedData, 0, versionCodeLength);

        let coinType = ethers.utils.hexDataSlice(hexlifiedData, versionCodeLength, versionCodeLength + coinTypeLength);

        let coinAddressHexLength = ethers.utils.hexDataSlice(hexlifiedData, versionCodeLength + coinTypeLength, versionCodeLength + coinTypeLength + coinAddressHexLengthLength);

        let decodedVersionCode = ethers.BigNumber.from(ethers.utils.hexStripZeros(versionCode));

        let decodedCoinType = ethers.utils.parseBytes32String(coinType);
        let decodedCoinAddressHexLength = parseInt(coinAddressHexLength);

        let coinAddress = ethers.utils.hexDataSlice(hexlifiedData, versionCodeLength + coinTypeLength + coinAddressHexLengthLength, versionCodeLength + coinTypeLength +  + coinAddressHexLengthLength + decodedCoinAddressHexLength);

        let baseOffset = ethers.utils.hexDataLength(coinAddress) - decodedCoinAddressHexLength;

        let decodedAddress = ethers.utils.hexDataSlice(coinAddress, baseOffset);

        try {
            decodedAddress = ethers.utils.toUtf8String(decodedAddress, true);
        } catch (TypeError) {

        }

        return {"versionCode": decodedVersionCode, "coinAddress": decodedAddress, "coinType": decodedCoinType};
    },
    encodeDocumentMatrixInPlace: function (_dataTemplate) {

        if (_dataTemplate.versionCode === 2 || _dataTemplate.versionCode === 3 && _dataTemplate.documentMatrixDataBytes != null) {

            let encodedDocumentMatrixDataPacked = JSON.stringify(_dataTemplate.documentMatrixDataBytes);

            let bytesDocumentMatrixEncodedPacked = ethers.utils.toUtf8Bytes(encodedDocumentMatrixDataPacked);

            _dataTemplate["encryptedData"] = bytesDocumentMatrixEncodedPacked;

            return _dataTemplate;
        } else {
            return null;
        }
    },
    encodeDocument: function (_bitsMatrix, _versionCode, _encryptedData) {
        if (_versionCode === 1 || _versionCode == 2 || _versionCode == 3) {
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
    sendWebhookMessageAsync: function (obj) {

      const instance = axios.create();

      instance.defaults.headers.common['X-WEBHOOK-TOKEN'] = webhookClientSecret;

      return  instance.post(webhookUrl, {
        obj: obj
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
        logger.debug('documentsMatrixEncrypted');
        logger.debug(documentsMatrixEncrypted);

        var decodedDocument = this.decodeDocument(documentsMatrixEncrypted);
        logger.debug('decodedDocument');
        logger.debug(decodedDocument);

        var versionCode = decodedDocument.versionCode;
        logger.debug('versionCode');
        logger.debug(versionCode);

        if (versionCode == 3) {
            var encryptedData = decodedDocument.encryptedData;
            logger.debug('encryptedData');
            logger.debug(encryptedData);

            var decodedDocumentMatrixInPlace = this.decodeDocumentMatrixInPlace(decodedDocument);
            logger.debug('decodedDocumentMatrixInPlace');
            logger.debug(decodedDocumentMatrixInPlace);

            var documentMatrixDataBytes = Object.values(decodedDocumentMatrixInPlace);
            logger.debug('documentMatrixDataBytes');
            logger.debug(documentMatrixDataBytes);

            var travelRuleV3Template = this.decodeTravelRuleV3Template(documentMatrixDataBytes);
            logger.debug('travelRuleV3Template');
            logger.debug(travelRuleV3Template);

            var coinAddress = travelRuleV3Template.coinAddress;
            logger.debug('coinAddress');
            logger.debug(coinAddress);

            var coinType = travelRuleV3Template.coinType;
            logger.debug('coinType');
            logger.debug(coinType);
            var temp = coinType.split("_");
            var coinBlockchain = temp[0];
            logger.debug('coinBlockchain');
            logger.debug(coinBlockchain);
            var coinToken = temp[1];
            logger.debug('coinToken');
            logger.debug(coinToken);

            var dict = {};
            dict['type'] = 'WALLET';
            dict['version_code'] = versionCode;
            dict['coin_blockchain'] = coinBlockchain;
            dict['coin_token'] = coinToken;
            dict['coin_address'] = coinAddress;
            dict['trustAnchorAddress'] = result['trustAnchorAddress'];
            dict['attestation_hash'] = attestation_hash;

            dict['public_data'] = result['publicData'];
            dict['public_data_decoded'] = this.convertComponentsFromHex(result['publicData']);
            dict['coin_memo'] = this.convertComponentsFromHex(result['publicData']);

            dict['documents_matrix_encrypted'] = result['documentsMatrixEncrypted'];
            dict['documents_matrix_encrypted_decoded'] = this.convertComponentsFromHex(result['documentsMatrixEncrypted']);

            dict['availability_address_encrypted'] = result['availabilityAddressEncrypted'];
            dict['availability_address_encrypted_decoded'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']);

            logger.debug('taGetAttestationComponents');
            logger.debug(dict);

            var obj = {message: 'ta-get-attestation-components', data: dict};
            this.sendWebhookMessage(obj);

        } else if (versionCode == 2) {
            documentsMatrixEncrypted = this.unpackDocumentsMatrixEncrypted(documentsMatrixEncrypted);
            var dict = {};
            dict['type'] = 'WALLET';
            dict['document'] = documentsMatrixEncrypted;
            dict['memo'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']);


            dict['version_code'] = versionCode;
            dict['coin_blockchain'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']).trim();
            dict['coin_token'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']).trim();;
            dict['coin_address'] = documentsMatrixEncrypted;
            dict['coin_memo'] = this.convertComponentsFromHex(result['publicData']);

            dict['trustAnchorAddress'] = result['trustAnchorAddress'];
            dict['attestation_hash'] = attestation_hash;

            dict['public_data'] = result['publicData'];
            dict['public_data_decoded'] = this.convertComponentsFromHex(result['publicData']);

            dict['documents_matrix_encrypted'] = result['documentsMatrixEncrypted'];
            dict['documents_matrix_encrypted_decoded'] = this.convertComponentsFromHex(result['documentsMatrixEncrypted']);

            dict['availability_address_encrypted'] = result['availabilityAddressEncrypted'];
            dict['availability_address_encrypted_decoded'] = this.convertComponentsFromHex(result['availabilityAddressEncrypted']);

            logger.debug('taGetAttestationComponents');
            logger.debug(dict);

            var obj = {message: 'ta-get-attestation-components', data: dict};
            this.sendWebhookMessage(obj);
        }

    },
    trustAnchorGetAttestationArrayForUserAccount: async function(account){
       result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
          logger.debug('getAttestationKeccakArrayForIdentifiedAddress result');
          logger.debug(result);

       var obj = {request: 'ta-get-attestation-array-for-user-account', result: result};
       this.sendWebhookMessage(obj);
    },
    taGetAttestationComponents: function (data,attestation_hash,trustAnchorAddress) {

      var obj;
      var documentsMatrixEncrypted = this.convertComponentsFromHex(data._documentsMatrixEncrypted);
      var decodedDocument = this.decodeDocument(documentsMatrixEncrypted);
      var versionCode = decodedDocument.versionCode;

      if (versionCode == 3) {
          var encryptedData = decodedDocument.encryptedData;
          var decodedDocumentMatrixInPlace = this.decodeDocumentMatrixInPlace(decodedDocument);
          var documentMatrixDataBytes = Object.values(decodedDocumentMatrixInPlace);

          var travelRuleV3Template = this.decodeTravelRuleV3Template(documentMatrixDataBytes);
          var coinAddress = travelRuleV3Template.coinAddress;


          var coinType = travelRuleV3Template.coinType;
          var temp = coinType.split("_");
          var coinBlockchain = temp[0];
          var coinToken = temp[1];

          var dict = {};
          dict['type'] = 'WALLET';
          dict['version_code'] = versionCode;
          dict['coin_blockchain'] = coinBlockchain;
          dict['coin_token'] = coinToken;
          dict['coin_address'] = coinAddress;
          dict['trustAnchorAddress'] = trustAnchorAddress;
          dict['attestation_hash'] = attestation_hash;

          dict['public_data'] = data._publicData;
          dict['public_data_decoded'] = this.convertComponentsFromHex(data._documentsMatrixEncrypted);
          dict['coin_memo'] = this.convertComponentsFromHex(data._publicData);

          dict['documents_matrix_encrypted'] = data._documentsMatrixEncrypted;
          dict['documents_matrix_encrypted_decoded'] = this.convertComponentsFromHex(data._documentsMatrixEncrypted);

          dict['availability_address_encrypted'] = data._availabilityAddressEncrypted;
          dict['availability_address_encrypted_decoded'] = this.convertComponentsFromHex(data._availabilityAddressEncrypted).trim();

          console.log('taGetAttestationComponents');
          console.log(dict);

          obj = {message: 'ta-get-attestation-components', data: dict};

      } else if (versionCode == 2) {
          documentsMatrixEncrypted = this.unpackDocumentsMatrixEncrypted(documentsMatrixEncrypted);
          var dict = {};
          dict['type'] = 'WALLET';
          dict['document'] = documentsMatrixEncrypted;
          dict['memo'] = this.convertComponentsFromHex(data._availabilityAddressEncrypted).trim();


          dict['version_code'] = versionCode;
          dict['coin_blockchain'] = this.convertComponentsFromHex(data._availabilityAddressEncrypted).trim();
          dict['coin_token'] = this.convertComponentsFromHex(data._availabilityAddressEncrypted).trim();;
          dict['coin_address'] = documentsMatrixEncrypted;
          dict['coin_memo'] = this.convertComponentsFromHex(data._publicData);

          dict['trustAnchorAddress'] = trustAnchorAddress;
          dict['attestation_hash'] = attestation_hash;

          dict['public_data'] = data._publicData;
          dict['public_data_decoded'] = this.convertComponentsFromHex(data._publicData);

          dict['documents_matrix_encrypted'] = data._documentsMatrixEncrypted;
          dict['documents_matrix_encrypted_decoded'] = this.convertComponentsFromHex(data._documentsMatrixEncrypted);

          dict['availability_address_encrypted'] = data._availabilityAddressEncrypted;
          dict['availability_address_encrypted_decoded'] = this.convertComponentsFromHex(data._availabilityAddressEncrypted).trim();

          console.log('taGetAttestationComponents');
          console.log(dict);

          obj = {message: 'ta-get-attestation-components', data: dict};

      }

      return obj;

    },
    getIsTrustAnchorVerified: async function (user_id, account) {

        var obj = { user_id: user_id, message: "ta-is-verified", data: '' };

        if(account == 'noSelect'){
            obj.data = 'noSelect';
        }else{
            result = await TrustAnchorManager.isTrustAnchorVerified(account);

            logger.debug('isTrustAnchorVerified result');
            logger.debug(result);

            obj.data = result;
        }
        this.sendWebhookMessage(obj);
    },
    taGetBalance: async function (user_id, account) {

        var obj = { user_id: user_id, message: "ta-get-balance", data: '' };

        if(account == 'noSelect'){
            obj.data = 'noSelect';
        } else {
            result = await web3.eth.getBalance(account);
            logger.debug('getBalance result');
            logger.debug(result);

            obj.data = result;
        }

        this.sendWebhookMessage(obj);
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

    taSetKeyValuePair: async function(user_id, account, key_name, key_value) {

        let result = 'none';

        //get ta private key
        let taPk  = _.first(_.filter(process.env.TRUST_ANCHOR_PK.split(","), function(v)  { if( web3.eth.accounts.privateKeyToAccount(v).address.toUpperCase() === account.toUpperCase()) { return  v; } }));
        //put your address private key
        let signer = new ethers.Wallet(taPk, provider);
        //reconfig your contract connect with a new signer
        let TrustAnchorExtraData_UniqueWithAccount = TrustAnchorExtraData_Unique.connect(signer);
        try{
            result = await TrustAnchorExtraData_UniqueWithAccount.setTrustAnchorKeyValuePair(key_name, key_value);
        }catch(e){
            logger.error('setTrustAnchorKeyValuePair error , account : ' + taPk);
        }

        if (result == 'none') {
            var obj = { user_id: user_id, message: "ta-set-key-value-pair", data: 'fail' };
            this.sendWebhookMessage(obj);
            return;
        }

        logger.debug('setTrustAnchorKeyValuePair result');
        logger.debug(result);
        var value = result['value'].toNumber();
        logger.debug(value);
        var obj = { user_id: user_id, message: "ta-set-key-value-pair", data: value };
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
          //get ta private key
          let taPk  = _.first(_.filter(process.env.TRUST_ANCHOR_PK.split(","), function(v)  { if( web3.eth.accounts.privateKeyToAccount(v).address.toUpperCase() === ta_address.toUpperCase()) { return  v; } }));
          //put your address private key
          let signer = new ethers.Wallet(taPk, provider);
          let addr   = await signer.getAddress();
          let nonceCount = await keyv.get(`nonceCount_${addr}`);
          //reconfig your contract connect with a new signer
          let TrustAnchorStorageWithAccount = TrustAnchorStorage.connect(signer);
          result = await TrustAnchorStorageWithAccount.setAttestation(
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
          await keyv.set(`nonceCount_${addr}`, nonceCount+1);

          return obj;
    },
    trustAnchorGetAttestationArrayForUserAccount: async function (account) {

        result = await TrustAnchorStorage.getAttestationKeccakArrayForIdentifiedAddress(account);
            logger.debug('getAttestationKeccakArrayForIdentifiedAddress result');
            logger.debug(result);

        var obj = {request: 'ta-get-attestation-array-for-user-account', result: result};
        this.sendWebhookMessage(obj);

    },
    createEmpyTransaction: async function (ta_address, nonce, chainId) {

      let tx = {
        to: ta_address,
        value: ethers.utils.parseEther("0.00"),
        chainId: chainId,
        nonce: nonce
      };

      //get ta private key
      let taPk  = _.first(_.filter(process.env.TRUST_ANCHOR_PK.split(","), function(v)  { if( web3.eth.accounts.privateKeyToAccount(v).address.toUpperCase() === ta_address.toUpperCase()) { return  v; } }));
      //put your address private key
      let signer = new ethers.Wallet(taPk, provider);
      // Signing a transaction
      let signedTX = await signer.signTransaction(tx);
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
