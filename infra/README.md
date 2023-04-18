# Automated Deployment of Veriscope

The code in the `infra/` directory is Infrasructure as Code (IaC) for veriscope nodes. This is made up of two separate parts.

1. Deploy the required infrastructure in a cloud provider (currently using AWS)
2. Configure the deployed instances to host veriscope node(s) connected to a given Shyft Network chain

The first part is achieved using `terraform` while the second part is achieved using `ansible`

Both the parts can be run either in a pipeline form any CI tool like Jenkins, GitHub Actions, GitLab CI/CD, TeamCity etc. or even from any machine. For example, your development machine or a bastion node deployed to control the veriscope nodes for security reasons.

## Prerequisites

### Once per organisation

1. Create CloudFormation Stack in the target AWS account using the CloudFormation template `infra/cloudformation/Terraform-Prerequisites.yaml` using appropriate IAM user or IAM role credentials. This templates creates an IAM role which is to be assumed by `terraform` in order to create the resources in the AWS account. It also creates an S3 bucket for storing terraform state in.

### Secrets

1. INFRA_IAM_ROLE_NAME -> Name of the IAM role in the target that terraform assumes
2. TARGET_AWS_ACCOUNT_ID -> Account ID of the target AWS account into which the infrastructure is deployed
3. SLACK_TOKEN -> Webhook secret for Slack to send messages to channels upon certain conditions being met in the pipeline/playbooks

Configure these secrets in any of the secrets management system that you use. For this GitHub repo (used in our CI pipelines), they are configured in GitHub Secrets.

### For GitHub Actions CI pipeline

1. Create CloudFormation Stack in the target AWS account using the CloudFormation template `infra/cloudformation/GH-OIDC-IAMRole.yaml` using appropriate IAM user or IAM role credentials

```bash
aws cloudformation create-stack --stack-name GH-OIDCProvider-IAMRole
```

### Execution outside of the pipeline

1. Jump on to the bastion node by following the README.md in the [veriscope_bastions](https://github.com/Paycase/veriscope_bastions) repo. If a bastion node hasn't been setup for you, please contact your infrastructure administrator.

2. Once on the bastion, follow the steps in the README.md at `infra/configure/playbooks/prep/README.md` to prepare your bastion.

## Deploy/Delete EC2 Instances (Terraform)

* On the bastion, `cd` into `~/source/Paycase/veriscope`
* Checkout the branch you would like to work on
* `cd` into `infra/terraform/instances` directory
* Copy `sample.tfvars` and create a file named `<your-github-handle>.tfvars`
* Edit that file and update (add/remove) your instances.
* Initialise your terraform module and validate it.

```bash
$ terraform init -backend-config="./../backend.<env>.tfvars" -backend-config="backend.instances.tfvars"
...
...
...
Terraform has been successfully initialized!
You may now begin working with Terraform. Try running "terraform plan" to see
any changes that are required for your infrastructure. All Terraform commands
should now work.

If you ever set or change modules or backend configuration for Terraform,
rerun this command to reinitialize your working directory. If you forget, other
commands will detect it and remind you to do so if necessary.
```

```bash
$ terraform validate
Success! The configuration is valid.
```

* Run `workspace.sh` to either create or switch to it (if it already exists)
   ***A new terraform workspace will be created for you based on your GitHub handle***

```bash
./workspace.sh <your-github-handle>
```

For example,

```bash
$ ./workspace.sh art-cmyk
...
...
...
Workspace for art-cmyk already exists. Switching to it...
  default
  addressproof-art-cmyk
  dev-0x1a4f7d58
* dev-art-cmyk
  dev-art-cmyk-1
  dev-art-cmyk.tfvars
  dev-art-cmyk1
  dev-cooldeark-bastion
  dev-env-0x1a4f7d58
  dev-env-art-cmyk
  dev-env-cooldeark
  dev-env-cooldeark-new
  dev-env-new-dummy
  dev-env-nm-db-backup
  dev-env-s1format
  dev-s1format
```

* Hopefully, it is all green. If not, contact your infrastructure administrator and resolve the issue.
* Now it is time to plan and apply the config.

```bash
export TF_VAR_aws_account_number=<your-aws-account-number>
export TF_VAR_role_name=<iam-role-name-to-assume>
export TF_VAR_cloudflare_creds=<cloudflare_api_token>
terraform plan -var-file <your-github-handle>.tfvars -out=<your-github-handle>.tfplan
```

For example,

```bash
$ export TF_VAR_aws_account_number=12345678912

$ export TF_VAR_role_name=my-infra-role

$ export TF_VAR_cloudflare_creds='{ api_token = "kashdkajshdajhajhdkjahdakjshdakjsd", account_id = "f8079878ds876s7ds6d76sd7s6dds" }'

$ terraform plan -var-file art-cmyk.tfvars -out=art-cmyk.tfplan
...
...
...
Saved the plan to: art-cmyk.tfplan

To perform exactly these actions, run the following command to apply:
    terraform apply "art-cmyk.tfplan"
```

* Review the plan and ensure terraform says it will create the instances according to the values specified in your `.tfvars` file. Now run the apply command if it all looks good.

```bash
terraform apply "<your-github-handle>.tfplan"
```

For example,

```bash
$ terraform apply "art-cmyk.tfplan"
...
...
...
module.ta_db["art-2"].aws_secretsmanager_secret.db_user_pwd: Creating...
module.ta_db["art-2"].aws_secretsmanager_secret.db_user_pwd: Creation complete after 2s [id=arn:aws:secretsmanager:us-east-1:529979553088:secret:/ravitej/dev/instances/trustanchor-art-2-XesWvc]
module.ta_db["art-2"].aws_secretsmanager_secret_version.db_user_pwd: Creating...
module.ta_db["art-2"].aws_secretsmanager_secret_version.db_user_pwd: Creation complete after 0s [id=arn:aws:secretsmanager:us-east-1:529979553088:secret:/ravitej/dev/instances/trustanchor-art-2-XesWvc|2BBC8DF6-E426-4CBB-83F4-DA186C5FDDB4]

Apply complete! Resources: 13 added, 0 changed, 0 destroyed.

Outputs:

ssh_secret_names = {
  "art-2.veriscope.org" = "/ravitej/dev/instances/art-2.veriscope.org"
}
...
...
...
```

* The above step could take a few minutes. So, time for your favourite drink! At the end of the apply run, outputs will be printed. Some of them might be hidden due to being sensitive. You can run `terraform output -json` to see the hidden outputs. Included in the outputs are FQDN, public and private IP addresses of the node created, SSH key secret name, TA DB password secret name etc.

* Sometimes an error might occur acquiring state lock like below. This could be for various reasons, including forcibly exiting a previous plan/apply/destroy actions.

```bash
$ terraform plan -var-file art-cmyk.tfvars -out=art-cmyk.tfplan
Acquiring state lock. This may take a few moments...
╷
│ Error: Error acquiring the state lock
│
│ Error message: ConditionalCheckFailedException: The conditional request failed
│ Lock Info:
│   ID:        fa50f66d-489c-3782-7f2f-4571db2ed92d
│   Path:      dev-veriscope-us-east-1-terraform/instances.tfstate
│   Operation: OperationTypeApply
│   Who:       ravit@KRSNA-x360
│   Version:   1.3.3
│   Created:   2022-12-13 12:11:21.526195545 +0000 UTC
│   Info:
│
│
│ Terraform acquires a state lock to protect the state from being written
│ by multiple users at the same time. Please resolve the issue above and try
│ again. For most commands, you can disable locking with the "-lock=false"
│ flag, but this is not recommended.
$ terraform force-unlock -force fa50f66d-489c-3782-7f2f-4571db2ed92d
Terraform state has been successfully unlocked!

The state has been unlocked, and Terraform commands should now be able to
obtain a new lock on the remote state.
```

* When you are done with the instances and don't need them anymore, you can delete (`destroy` in terraform language) by running the `terraform destroy` command. You will have to run, `init`, `validate` before destroy if you have a new terminal session. After you have initialised and validated, run the below command to destroy all the instances and their related infrastructure.

```bash
terraform destroy -var-file <your-github-handle>.tfvars
```

For example,

```bash
$ terraform destroy -var-file art-cmyk.tfvars
...
...
...
Do you really want to destroy all resources in workspace "dev-art-cmyk"?
  Terraform will destroy all your managed infrastructure, as shown above.
  There is no undo. Only 'yes' will be accepted to confirm.

  Enter a value:
```

It shows you all the changes and asks you whether to proceed. Upon typing yes and hitting enter it will proceed with deleting the infrastructure. An example of the output upon completion below.

```bash
...
...
...
module.instances["art-2-bastion-test"].aws_key_pair.instance: Destruction complete after 0s
module.instances["art-2-bastion-test"].tls_private_key.pk: Destroying... [id=70daeb867024ee2bf50fe6464cbff9745c5a9e39]
module.instances["art-2-bastion-test"].tls_private_key.pk: Destruction complete after 0s

Destroy complete! Resources: 13 destroyed.
```

## Install/Configure Veriscope (Ansible)

### Prepare

* Copy the FQDN, private ip address from the outputs above and paste into the ansible inventory file located at `infra/configure/inventory/veriscope-nodes.yaml`. This file also contains the Trust Anchor account address and private keys. Hence, it is ignored by git. Any changes you make are only available on the machine you use as the ansible controller (most probably your bastion host)

* The contents of the file should look like below. It should contain the FQDNs of the nodes and the TA account info for each node, i.e., the private key and the account address in the `hosts` section. It also contains a group of variables common to all the hosts in the `vars` section

```yaml
all:
  hosts:
  children:
    web:
      hosts:
        foo-001.my-custom-domain.com:
          private_ip: 10.10.10.10
          trust_anchor_pk: db3906947188edfe196fe01d3e161ef82722daec9e3259323997c4e877b20cb4
          trust_anchor_account: "0xe33bC570112172E2D64e8233d02454BBA56B67A2"
          ssh_secret_name: /owner/env/instances/foo-001.my-custom-domain.com
          nm_host: foo-001-nm.my-custom-domain.com
        bar-002.my-custom-domain.com:
          private_ip: 10.10.10.11
          trust_anchor_pk: db3906947188edfe196fe01d3e161ef82706947188edfe196fe01d3e161ef827
          trust_anchor_account: "0x67A212172E2D64e8233de33bC570102454BBA56B"
          ssh_secret_name: /owner/env/instances/bar-002.my-custom-domain.com
          nm_host: bar-002-nm.my-custom-domain.com
    nethermind:
      hosts:
        foo-001-nm.my-custom-domain.com:
          private_ip: 10.10.10.12
          ssh_secret_name: /owner/env/instances/foo-001-nm.my-custom-domain.com
        bar-002-nm.my-custom-domain.com:
          private_ip: 10.10.10.13
          ssh_secret_name: /owner/env/instances/bar-002-nm.my-custom-domain.com
  vars:
    # Whether to print debug messages to the screen while running the playbooks. NOTE: It may print secret information too. So, please use with caution.
    debug: true

    env: dev
    
    # Identify a chain to deploy to - choose from the list of directory names in chains/
    # One of 'veriscope_testnet', 'fed_testnet', 'fed_mainnet'
    veriscope_target: veriscope_testnet

    # Owner of the veriscope nodes. The value must be equal to the value of the Owner tag in the terraform variables file (.tfvars file).
    # If each node has a different owner, this can be moved to hosts: section and specified per node.
    owner: foobar
    
    addressproofs_module:
      # Whether to install addressproofs module (true/false). If setting to true, need to provide valid value for "gh_pat_secret_name" below
      install: true
      # AWS Secrets Manager secret name which holds the GitHub Personal Access Token which allows access to the ShyftNetwork/addressproofs repo.
      gh_pat_secret_name: /{{ owner }}/addressproofs-gh-pat

    # The details of the TA dashboard (PHP Laravel app) admin user. Required values are
    # firstname, lastname, email and password. password is optional. If not provided, it 
    # will be auto-generated and stored in infra/configure/playbooks/credentials/ta_dashboard_admin_pwd/<host-name>
    ta_dashboard_admin_user:
      firstname: Krishna
      lastname: Vasudeva
      email: krishna@shyft.network
      # optional password
      # password: mysupersecretpassword
    
    # Use this list to specify which apps to perform update (re-install) on beyond the copying 
    # the new version of the code during a node update.
    apps_to_update:
      - api
      - dashboard
    
    # Use this dict to specify the keys and values for changes to TA API config changes
    ta_api_config_changes:
      HTTP: http://{{ nm_host }}:8545
      WS: ws://{{ nm_host }}:8545
```

* `cd` into the root of the repository

* Download SSH keys for your nodes

```bash
ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml infra/configure/playbooks/prep/get-ssh-key-for-nodes.yaml
```

* If not already existing, create a secret in AWS Secrets Manager with the GitHub PAT as its value

### Install - new method

* **Very important**: Define values for all the variables in the `vars` section in `infra/configure/inventory/veriscope-nodes.yaml` file. For example, `veriscope_target`, `ta_dashboard_admin_user` etc.

* Execute the below command to install veriscope on the target host(s) which were specified in `infra/configure/inventory/veriscope-nodes.yaml`

```bash
ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml infra/configure/playbooks/install-veriscope.yaml
```

## Update veriscope node

* Update veriscope by deploying a new version of the code. `update-veriscope.yaml` playbook copies the version of the code on the controller/bastion node onto the target host(s). It expects a value for the variable `apps_to_update` which is a list of veriscope components to update (re-install). Valid values are `api` and `dashboard`. The below example command passes in extra vars inline, instead of updating the `vars` section in `veriscope-nodes.yaml` file.

```bash
ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml --extra-vars apps_to_update="['api', 'dashboard']" infra/configure/playbooks/update-veriscope.yaml
```

or

```bash
ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml --extra-vars apps_to_update="['dashboard']" infra/configure/playbooks/update-veriscope.yaml
```

or

```bash
ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml --extra-vars apps_to_update="['api']" infra/configure/playbooks/update-veriscope.yaml
```

The below command is an example of passing in the extra variables via a file where the below content is part of the `inventory/veriscope-nodes.yaml` file under the `vars` section.

```yaml
...
...
...
  # Use this list to specify which apps to perform update (re-install) on beyond the copying 
  # the new version of the code during a node update.
apps_to_update:
  - api
  - dashboard
...
...
...
```

```bash
ansible-playbook -i infra/configure/inventory/veriscope-nodes.yaml infra/configure/playbooks/update-veriscope.yaml
```
