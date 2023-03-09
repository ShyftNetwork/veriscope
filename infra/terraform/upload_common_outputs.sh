#!/bin/bash
cd common
terraform init
terraform workspace select dev-env-common
terraform workspace show
rm -f common_infra.json
terraform output -json | tee common_infra.json
cat common_infra.json
aws s3 cp common_infra.json s3://dev-veriscope-us-east-1-terraform/data/common_infra.json --sse AES256