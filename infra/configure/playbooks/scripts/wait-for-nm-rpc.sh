#!/bin/bash

echo "A1"
CONNRESULT=1     # nc returns 1 if connection fails
while [ $CONNRESULT -eq 1 ];  do
    echo "A2"
    echo "?" | nc localhost 8545
    CONNRESULT=$?
    echo "A3"
done
echo "A4"