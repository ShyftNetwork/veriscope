# Using bastion to create and configure veriscope nodes

## Prepare the bastion machine

Install prerequisites on the bastion node

   ```bash
   ansible-playbook infra/configure/playbooks/prep/seed-prep.yaml
   ansible-playbook infra/configure/playbooks/prep/prepare-for-iac.yaml
   ```

## Create your veriscope nodes

   Now that you have prepared your bastion, you can now run terraform modules and ansible playbooks which create and configure your veriscope nodes. For this, please follow the steps in the README.md located in the `infra` folder.
