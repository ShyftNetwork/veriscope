const express = require('express');
const app = express();
const bodyParser = require('body-parser');
const EthCrypto = require('eth-crypto');
const ecies = require("eth-ecies");
const EthUtil = require('ethereumjs-util');

const dotenv = require('dotenv');
dotenv.config();

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
    let userPublicKey = new Buffer(publicKey, 'hex');
    let bufferData = new Buffer(data);

    let encryptedData = ecies.encrypt(userPublicKey, bufferData);

    return encryptedData.toString('base64')
}

function decryptData(privateKey, data) {

	console.log('decryptData');

    let userPrivateKey = new Buffer(privateKey, 'hex');
    let bufferEncryptedData = new Buffer(data, 'base64');

    let decryptedData = ecies.decrypt(userPrivateKey, bufferEncryptedData);
    
    return decryptedData.toString('utf8');
}

function TASign(messageJSON, privateKey) {

	  var messageBuffer = new Buffer(JSON.stringify(messageJSON));
    var hash = EthUtil.hashPersonalMessage(messageBuffer);
    
    var ecprivkey = Buffer.from(privateKey,'hex');

    var result = EthUtil.ecsign(hash, ecprivkey, 1);
    var template = {};

   	template['SignatureHash'] = bufferToHex(hash);
   	template['Signature'] = {r: bufferToHex(result['r']),s: bufferToHex(result['s']),v: bufferToHex(result['v'])};

    return template;

}

function TARecover(template, type) {
  var returnMessage = {};
  var message;
  var signature;
  console.log(type);
  if(type === 'BeneficiaryTA') {
    message = template['BeneficiaryTASignatureHash'];
    signature = template['BeneficiaryTASignature'];
  }
  else if(type === 'BeneficiaryUser') {
    message = template['BeneficiaryUserSignatureHash'];
    signature = template['BeneficiaryUserSignature'];
  }
  else if(type === 'SenderTA') {
    message = template['SenderTASignatureHash'];
    signature = template['SenderTASignature'];
  }
  else if(type === 'SenderUser') {
    message = template['SenderUserSignatureHash'];
    signature = template['SenderUserSignature'];
  }
  else if(type === 'Crypto') {
    message = template['CryptoSignatureHash'];
    signature = template['CryptoSignature'];
  }
  
  var v = Buffer.from(signature['v'].substr(2),'hex');
  var r = Buffer.from(signature['r'].substr(2),'hex');
  var s = Buffer.from(signature['s'].substr(2),'hex');
  
  var messageHash = Buffer.from(message.substr(2),'hex');
    result = EthUtil.ecrecover(messageHash, v, r, s, 1);

    var pubkeyString = bufferToHex(result).substr(2);

    // compare public key from signature with public key in message
    if (pubkeyString === template['BeneficiaryTAPublicKey']) {
      returnMessage['beneficiaryTAPublicKey'] = 'found match';
    }
    else if (pubkeyString === template['BeneficiaryUserPublicKey']) {
      returnMessage['beneficiaryUserPublicKey'] = 'found match';
    }
    else if (pubkeyString === template['SenderTAPublicKey']) {
      returnMessage['senderTAPublicKey'] = 'found match';
    }
    else if (pubkeyString === template['SenderUserPublicKey']) {
      returnMessage['senderUserPublicKey'] = 'found match';
    }
    else {
      returnMessage['publicKey'] = 'no match for type: '+type;
    }

    var address = getEthAddressFromPublicKey(pubkeyString);
    if (address === template['BeneficiaryTAAddress']) {
      returnMessage['beneficiaryTAAddress'] = 'found match';
    }
    else if (address === template['BeneficiaryUserAddress']) {
      returnMessage['beneficiaryUserAddress'] = 'found match';
    }
    else if (address === template['SenderTAAddress']) {
      returnMessage['senderTAAddress'] = 'found match';
    }
    else if (address === template['SenderUserAddress']) {
      returnMessage['senderUserAddress'] = 'found match';
    }
    else {
      returnMessage['address'] = 'no match for type: '+type;
    }
    return returnMessage;
}

// serve files from the public directory
app.use(express.static('public'));
app.use(bodyParser.json());
// start the express web server listening on 8091
app.listen(process.env.TEMPLATE_HELPER_PORT, () => {
  console.log('listening on '+process.env.TEMPLATE_HELPER_PORT);
});

app.post('/TASign', (req, res) => {
  console.log('TASign');
  
  var messageJSON = req.param('messageJSON');
  var privateKey = req.param('privateKey');
  console.log(messageJSON);
  console.log(privateKey);
  var result = TASign(messageJSON, privateKey);
  res.json(result);
});

app.post('/TARecover', (req, res) => {
  console.log('TARecover');
  var kycTemplate = JSON.parse(req.param('kycTemplate'));
  var type = req.param('type');

  var result = TARecover(kycTemplate, type);
  res.json(result);
});

app.post('/EncryptData', (req, res) => {
  console.log('EncryptData');
  var publicKey = req.param('publicKey');
  var kycJSON = req.param('kycJSON');
  var kycData = JSON.stringify(kycJSON);
  var kycEncrypt = encryptData(publicKey, kycData);
  console.log(kycEncrypt);
  res.json({kycEncrypt: kycEncrypt});
});

app.post('/DecryptData', (req, res) => {

  console.log('DecryptData');
  var privateKey = req.param('privateKey');
  var kycData = req.param('kycData');

  var kycDecrypt = decryptData(privateKey.substr(2), kycData);
  console.log(kycDecrypt);
  res.json({kycDecrypt: JSON.parse(kycDecrypt)});
});

app.post('/GetEthPublicKey', (req, res) => {
  console.log('GetEthPublicKey');
  var privateKey = req.param('privateKey');
  var publicKey = getEthPublicKey(privateKey);
  res.json({publicKey: publicKey});
  
});

