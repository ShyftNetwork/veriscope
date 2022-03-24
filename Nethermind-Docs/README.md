## Nethermind Docs

Nethermind is the POA node responsible for synchronizing the Shyft Network blockchain.

As a VASP it is recommended that you run and maintain your own relay node.

Step 2 in the scripts/setup-vasp.sh will setup and configure your node.

Full documentation on Nethermind can be found here: [https://docs.nethermind.io/nethermind/](https://docs.nethermind.io/nethermind/).

## How to check the Nethermind's liveness

In the nethermind directory you can find the configuration file.

```
$ pwd
/opt/nm
$ tree -L 1
.
├── Data
├── NLog.config
├── Nethermind.Cli
├── Nethermind.Launcher
├── Nethermind.Runner
├── VaspTestnet.json
├── config.cfg
├── git-hash
├── keystore
├── logs
├── nethermind_db
├── plugins
└── static-nodes.json
```

In the config.cfg there are a number of custom configurations you can set.  For example Health checks.

Open the config.cfg and add the following:

```
"HealthChecks": {
    "Enabled": true,
    "WebhooksEnabled": true,
    "UIEnabled": true,
    "Slug": "/api/health",
    "MaxIntervalWithoutProcessedBlock ": 15,
    "MaxIntervalWithoutProducedBlock": 45
  }
```

**Note:** the Slug parameter /api/health

Restart nethermind and try the following.

```
$ curl localhost:8545/api/health
{"status":"Healthy","totalDuration":"00:00:00.0034157","entries":{"node-health":{"data":{},"description":"The node is now fully synced with a network. Peers: 3.","duration":"00:00:00.0030673","status":"Healthy","tags":[]}}}
```

For more information, see here for Nethermind's documentation on Node Health.

https://docs.nethermind.io/nethermind/ethereum-client/monitoring-node-health

