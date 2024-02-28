const utility = require('../utility');
const Web3 = require('web3');
const fs = require('fs');

const testNetHttpUrl = process.env.HTTP;
const web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

const Contract_TrustAnchorStorage = JSON.parse(fs.readFileSync(process.env.CONTRACTS + 'TrustAnchorStorage.json', 'utf8'));
const tasInterface = utility.contractInterface(Contract_TrustAnchorStorage);

module.exports = function(job) {

  return new Promise(function(resolve, reject) {

    if (job.data.evt.name !== 'EVT_setAttestation') {
        // Throw a new Error if the event name does not match 'EVT_setAttestation'
        reject(new Error("Event name does not match 'EVT_setAttestation'"));
    }

    web3.currentProvider.send({
      method: "trace_transaction",
      params: [job.data.evt.transactionHash],
      jsonrpc: "2.0",
      id: new Date().getTime()
    }, function(error, result) {
      // if is error
      if (error) {
        reject(new Error(error));
      } else {

        let parsed = tasInterface.parseTransaction({
          data: result.result[0].action.input
        });
        let args = parsed.args;

        let data = {};
        data['transactionHash'] = job.data.evt.transactionHash;
        data['blockNumber'] = job.data.evt.blockNumber;
        data['event'] = "EVT_setAttestation";
        data['returnValues'] = {};

        data['returnValues']['attestationKeccak'] = job.data.evt.values.attestationKeccak;
        data['returnValues']['msg_sender'] = job.data.evt.values.msg_sender;
        data['returnValues']['_identifiedAddress'] = job.data.evt.values._identifiedAddress;
        data['returnValues']['_jurisdiction'] = job.data.evt.values._jurisdiction;
        data['returnValues']['_effectiveTime'] = job.data.evt.values._effectiveTime.toString();
        data['returnValues']['_expiryTime'] = job.data.evt.values._expiryTime.toString();
        data['returnValues']['_publicData_0'] = job.data.evt.values._publicData_0;
        data['returnValues']['_documentsMatrixEncrypted_0'] = job.data.evt.values._documentsMatrixEncrypted_0;
        data['returnValues']['_availabilityAddressEncrypted'] = job.data.evt.values._availabilityAddressEncrypted;
        data['returnValues']['_isManaged'] = job.data.evt.values._isManaged;
        data['returnValues']['_publicDataLength'] = job.data.evt.values._publicDataLength;
        data['returnValues']['_documentsMatrixEncryptedLength'] = job.data.evt.values._documentsMatrixEncryptedLength;

        data['type'] = utility.convertComponentsFromHex(args._publicData);
        data['document'] = utility.convertComponentsFromHex(args._documentsMatrixEncrypted);
        data['document_decrypt'] = utility.convertComponentsFromHex(args._documentsMatrixEncrypted);
        data['memo'] = utility.convertComponentsFromHex(args._availabilityAddressEncrypted).trim();

        try {
          let values = utility.taGetAttestationComponents(args, job.data.evt.transactionHash, job.data.evt.values.msg_sender);
          data['traceValues'] = values.data;
          var obj = {
            message: "tas-event",
            data: data
          };
          console.log("resolve", obj);
          resolve(obj);
        } catch (error) {
          reject(new Error(error));
        }

      }
    });

  });

}
