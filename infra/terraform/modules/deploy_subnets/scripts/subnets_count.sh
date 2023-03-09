#!/bin/bash

# modified from https://github.com/hashicorp/terraform/issues/16380

vpc_id=$1

if [ -z ${vpc_id} ]; then
  echo "usage : $0 <vpc_id>" 
  exit 1
fi

count=$(aws ec2 describe-subnets --profile studios --filters "Name=vpc-id,Values=${vpc_id}" | jq '.Subnets | length')

jq -n --arg count ${count} '{"count": $count }'
