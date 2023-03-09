#!/bin/bash

set -e

echo "Updating nethermind config with new enodes info...\n"

ENODE=`curl -s -X POST -d '{"jsonrpc":"2.0","id":1, "method":"admin_nodeInfo", "params":[]}' http://localhost:8545/ | jq '.result.enode'`
echo "This enode: $ENODE . Updating ethstats setting...\n"
jq ".EthStats.Contact = $ENODE" $NETHERMIND_CFG | sponge $NETHERMIND_CFG

cat $NETHERMIND_CFG

echo "\n"
