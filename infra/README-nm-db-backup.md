# Deploying the dedicated instance

1. The dedicated nethermind db backup node is deployed using terraform (`terraform/instances/nm-db-backup.tfvars`)
2. `terraform workspace select dev-env-nm-db-backup`
3. `terraform plan -var-file nm-db-backup.tfvars -out nm-db-backup.tfplan`
4. `terraform apply "nm-db-backup.tfplan"`

# Configuring the dedicated instance

Once the node is deployed, run below ansible commands to configure it. We only need nethermind running. So, only two playbooks need to be run.

1. Add `nm-db-backup-node.veriscope.org:` to the inventory file
2. `ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml infra/configure/playbooks/install-prerequisites.yaml`
3. `ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml infra/configure/playbooks/install-update-nethermind.yaml`

Check https://fedstats.veriscope.network/ to confirm nm-db-backup-node.veriscope.org appears in the list sync'ed up

# Regular backups