const utility = require('../utility');

module.exports = function(job){

  return new Promise(async function(resolve, reject) {

    try {
        const receipt = await utility.waitForTransaction(job.data.data.hash);
        resolve(receipt);
    } catch (error) {
        reject(error)
    }

  });

}
