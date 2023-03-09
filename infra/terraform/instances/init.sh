#!/bin/bash

terraform init -backend-config="bucket=veriscope-terraform-state" -backend-config="key=veriscope-instances" -backend-config="region=us-east-1"

terraform validate

terraform workspace list