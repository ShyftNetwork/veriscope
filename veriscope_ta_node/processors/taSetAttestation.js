const utility = require('../utility');

let lastNow = null;
function getTime() {
    const now = (new Date()).getTime();
    if (lastNow == null) { lastNow = now; }
    const result = parseInt(String(now - lastNow)) / 1000;
    lastNow = now;
    return result;
}

module.exports = function(job){

  return new Promise(function(resolve, reject) {

    utility.taSetAttestation(
      job.data.attestation_type,
      job.data.user_id,
      job.data.user_address,
      job.data.jurisdiction,
      job.data.effective_time,
      job.data.expiry_time,
      job.data.public_data,
      job.data.documents_matrix_encrypted,
      job.data.availability_address_encrypted,
      job.data.is_managed,
      job.data.ta_address
    ).then(async function(response){
          utility.sendWebhookMessage(response);
          resolve(response);
      }).catch(function(e){
           reject(new Error(e));
      });

  });

}
