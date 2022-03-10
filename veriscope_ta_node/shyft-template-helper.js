const express = require('express');
const app = express();
const bodyParser = require('body-parser');
const EthCrypto = require('eth-crypto');
const EthUtil = require('ethereumjs-util');
const devp2p = require("@ethereumjs/devp2p");
const secp256k1 = require("secp256k1");
const winston = require('winston');

const dotenv = require('dotenv');
dotenv.config();


const logger = winston.createLogger({
  level: (process.env.LOG_LEVEL || 'info'),
  format: winston.format.json(),
  maxsize: 512000000,
  maxFiles: 3,
  tailable: true,
  defaultMeta: { service: 'shyft-template-helper' },
  transports: [
    //
    // - Write all logs with level `error` and below to `error.log`
    // - Write all logs with level `info` and below to `combined.log`
    //
    new winston.transports.File({ filename: 'logs/shyft-template-helper.error.log', level: 'error' }),
    new winston.transports.File({ filename: 'logs/shyft-template-helper.combined.log' }),
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

function getEthPublicKey(_privateKey) {
    let privateKey = _privateKey;

    if (Buffer.isBuffer(_privateKey)) {
        privateKey = "0x" + _privateKey.toString('hex');
    }

    const publicKey = EthCrypto.publicKeyByPrivateKey(
        privateKey
    );

    return publicKey;
}

function getEthAddressFromPublicKey(_publicKey) {
    const publicAddress = EthCrypto.publicKey.toAddress(_publicKey);

    return publicAddress;
}

function encryptData(publicKey, data) {
    logger.debug("encryptData");

    let userPublicKey = new Buffer("04" + publicKey, "hex");
    let bufferData = new Buffer(data);

    let ecies = new devp2p.ECIES(null, devp2p.pk2id(userPublicKey), devp2p.pk2id(userPublicKey));
    let encryptedData = ecies._encryptMessage(bufferData);
    
    logger.debug(encryptedData.toString("base64"));

    return encryptedData.toString("base64");
}

function decryptData(privateKey, data) {

    logger.debug("decryptData");

    let userPrivateKey = new Buffer(privateKey, "hex");
    let bufferEncryptedData = new Buffer(data, "base64");

    const userPublicKey = Buffer.from(secp256k1.publicKeyCreate(userPrivateKey, false));
    let ecies = new devp2p.ECIES(userPrivateKey, devp2p.pk2id(userPublicKey));
    let decryptedData = ecies._decryptMessage(bufferEncryptedData);
    
    logger.debug(decryptedData.toString("utf8"));

    return decryptedData.toString("utf8");
}

function TARecover(template, type) {
    logger.debug("TARecover");
    var returnMessage = {};
    var message;
    var signature;

    logger.debug(type);
    if (type === 'BeneficiaryTA') {
        message = template['BeneficiaryTASignatureHash'];
        signature = template['BeneficiaryTASignature'];
    } else if (type === 'BeneficiaryUser') {
        message = template['BeneficiaryUserSignatureHash'];
        signature = template['BeneficiaryUserSignature'];
    } else if (type === 'SenderTA') {
        message = template['SenderTASignatureHash'];
        signature = template['SenderTASignature'];
    } else if (type === 'SenderUser') {
        message = template['SenderUserSignatureHash'];
        signature = template['SenderUserSignature'];
    } else if (type === 'Crypto') {
        message = template['CryptoSignatureHash'];
        signature = template['CryptoSignature'];
    }

    var v = Buffer.from(signature['v'].substr(2), 'hex');
    var r = Buffer.from(signature['r'].substr(2), 'hex');
    var s = Buffer.from(signature['s'].substr(2), 'hex');

    var messageHash = Buffer.from(message.substr(2), 'hex');
    var result = EthUtil.ecrecover(messageHash, v, r, s, 1);

    var pubkeyString = bufferToHex(result).substr(2);

    // compare public key from signature with public key in message
    if (pubkeyString === template['BeneficiaryTAPublicKey']) {
        returnMessage['beneficiaryTAPublicKey'] = 'found match';
    } else if (pubkeyString === template['BeneficiaryUserPublicKey']) {
        returnMessage['beneficiaryUserPublicKey'] = 'found match';
    } else if (pubkeyString === template['SenderTAPublicKey']) {
        returnMessage['senderTAPublicKey'] = 'found match';
    } else if (pubkeyString === template['SenderUserPublicKey']) {
        returnMessage['senderUserPublicKey'] = 'found match';
    } else {
        returnMessage['publicKey'] = 'no match for type: ' + type;
    }

    var address = getEthAddressFromPublicKey(pubkeyString).toLowerCase();

    
    if (type === 'BeneficiaryTA' && address === template['BeneficiaryTAAddress'].toLowerCase()) {
        returnMessage['beneficiaryTAAddress'] = 'found match';
    } else if (type === 'BeneficiaryUser' && address === template['BeneficiaryUserAddress'].toLowerCase()) {
        returnMessage['beneficiaryUserAddress'] = 'found match';
    } else if (type === 'SenderTA' && address === template['SenderTAAddress'].toLowerCase()) {
        returnMessage['senderTAAddress'] = 'found match';
    } else if (type === 'SenderUser' && address === template['SenderUserAddress'].toLowerCase()) {
        returnMessage['senderUserAddress'] = 'found match';
    } else {
        returnMessage['address'] = 'no match for type: ' + type;
    }
    return returnMessage;
}

// serve files from the public directory
app.use(express.static('public'));
app.use(bodyParser.json());
// start the express web server listening on 8091
app.listen(process.env.TEMPLATE_HELPER_PORT, () => {
    logger.debug('listening on ' + process.env.TEMPLATE_HELPER_PORT);
});

app.post('/TARecover', (req, res) => {
    logger.debug('TARecover');
    var kycTemplate = JSON.parse(req.param('kycTemplate'));
    var type = req.param('type');

    var result = TARecover(kycTemplate, type);
    logger.debug(result);
    res.json(result);
});

app.post('/EncryptData', (req, res) => {
    logger.debug('EncryptData');
    var publicKey = req.param('publicKey');
    var kycJSON = req.param('kycJSON');
    var kycData = JSON.stringify(kycJSON);
    var kycEncrypt = encryptData(publicKey, kycData);
    logger.debug(kycEncrypt);
    res.json({ kycEncrypt: kycEncrypt });
});

app.post('/DecryptData', (req, res) => {

    logger.debug('DecryptData');
    var privateKey = req.param('privateKey');
    var kycData = req.param('kycData');

    var kycDecrypt = decryptData(privateKey.substr(2), kycData);
    logger.debug(kycDecrypt);
    res.json({ kycDecrypt: JSON.parse(kycDecrypt) });
});

app.post('/GetEthPublicKey', (req, res) => {
    logger.debug('GetEthPublicKey');
    var privateKey = req.param('privateKey');
    var publicKey = getEthPublicKey(privateKey);
    logger.debug(publicKey);
    res.json({ publicKey: publicKey });

});