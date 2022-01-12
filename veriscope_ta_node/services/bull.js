var express = require('express');
var router = express.Router();
var Queue = require('bull');
var Arena = require('bull-arena');
var Redis = require('ioredis');
var path = require('path');

var {REDIS_URI} = process.env;

var redis = new Redis(REDIS_URI);

var opts = {
  removeOnComplete: 100,
  removeOnFail: false,
  attempts: 10,
  limiter: {
    max: 100, // Limit queue to max 100 jobs per 1 seconds.
    duration: 1000,
    bounceBack: true // important
  },
  /*
  backoffStrategies: {
    jitter: function () {
      return 5000 + Math.random() * 500;
    }
  }
  */
};

// Configure bull arena UI
const arenaConfig = Arena({
  queues: [
    {name: "taSetAttestation",hostId: "main",url: REDIS_URI},
    {name: "taSetAttestationStatusCheck",hostId: "main",url: REDIS_URI},
    {name: "taEmptyTransaction",hostId: "main", url: REDIS_URI},
    {name: "taEmptyTransactionStatusCheck",hostId: "main", url: REDIS_URI}
  ]
},
{
  basePath: '/arena',
  disableListen: true
});


var service = {
  queue: {
    taSetAttestation: new Queue('taSetAttestation', REDIS_URI),
    taSetAttestationStatusCheck: new Queue('taSetAttestationStatusCheck', REDIS_URI),
    taEmptyTransaction: new Queue('taEmptyTransaction', REDIS_URI),
    taEmptyTransactionStatusCheck: new Queue('taEmptyTransactionStatusCheck', REDIS_URI)
  },
  arena: arenaConfig,
  redis: redis,
  opts: opts
};

service.queue.taSetAttestation.process(1, path.join(__dirname,'..','processors/taSetAttestation.js'));
service.queue.taSetAttestationStatusCheck.process(1, path.join(__dirname,'..','processors/taSetAttestationStatusCheck.js'));
service.queue.taEmptyTransaction.process(1, path.join(__dirname,'..','processors/taEmptyTransaction.js'));
service.queue.taEmptyTransactionStatusCheck.process(1, path.join(__dirname,'..','processors/taEmptyTransactionStatusCheck.js'));

module.exports = service;
