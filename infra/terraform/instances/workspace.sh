#!/bin/bash

workspace_user=$1
if [ -z "$workspace_user" ]; then
    echo -e "\nOne argument expected but none was given. Please provide a terraform workspace name to switch to or create.\n \nFor example, ./workspace.sh my-new-tf-workspace\n"
    exit 1
fi

terraform workspace list
workspace=$(terraform workspace list | grep "$workspace_user")
echo "$workspace"
if [ -n "$workspace" ]; then
    echo "Workspace for $workspace_user already exists. Switching to it..."
    terraform workspace select "$workspace_user"
else
    echo "Workspace for $workspace_user NOT found. Creating one now..."
    terraform workspace new "$workspace_user"
fi
terraform workspace list