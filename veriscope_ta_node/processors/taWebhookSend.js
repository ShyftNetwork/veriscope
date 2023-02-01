const utility = require('../utility');

module.exports = function(job){

  return new Promise(function(resolve, reject) {

    utility.sendWebhookMessageAsync(job.data).then(function(response){
       resolve(response.data);
    }).catch(function(error){
       reject(new Error(error));
    });


  });

}
