const utility = require('../utility');

module.exports = function(job){

  return new Promise(function(resolve, reject) {

    utility.createEmpyTransaction(
      job.data.chainId,
      job.data.nonce
    ).then(async function(response){
           resolve(response);
      }).catch(function(e){
           reject(new Error(e));
      });

  });

}
