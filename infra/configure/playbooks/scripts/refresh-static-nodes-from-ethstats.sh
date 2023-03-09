#!/bin/bash

set -e

echo "Refreshing static nodes from ethstats..."

DEST=$NM_ROOT/static-nodes.json
echo '[' >$DEST
wscat -x '{"emit":["ready"]}' --connect $ETHSTATS_GET_ENODES | grep enode | jq '.emit[1].nodes' | grep  -oP '"enode://.*?"'   | sed '$!s/$/,/' | tee -a $DEST
echo ']' >>$DEST

echo "\n"

cat $DEST

echo "\n"
