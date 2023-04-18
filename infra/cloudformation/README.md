# Bootstrapping a new AWS account to use terraform for infrastructure automation

### Manual actions

* Assumption: You have access to an IAM User credentials which has enough permissions to create CloudFormation stacks which inturn will be creating IAM Groups, IAM Roles, IAM Polocies, S3 Buckets, DynamoDB Tables etc.
* Create a S3 bucket (in this example, `shyft-cfn-templates-us-east-1`) to store the packaged cloudformation templates

### Validate and package the template

```bash
$ aws cloudformation validate-template --template-body file://iac-bootstrap.yaml
$ aws cloudformation package --template-file iac-bootstrap.yaml --s3-bucket shyft-cfn-templates-us-east-1 --output-template-file packaged-iac-bootstrap.yaml
$ aws s3 cp packaged-iac-bootstrap.yaml s3://shyft-cfn-templates-us-east-1/packaged-iac-bootstrap.yaml
```

### Create new stack

```bash
$ aws cloudformation create-stack --stack-name iac-bootstrap-veriscope-dev --template-body file://packaged-iac-bootstrap.yaml --capabilities CAPABILITY_NAMED_IAM --disable-rollback --parameters ParameterKey=ServiceName,ParameterValue=veriscope ParameterKey=EnvironmentName,ParameterValue=dev
```

### Update the stack

```bash
$ aws cloudformation update-stack --stack-name iac-bootstrap-veriscope-dev --template-body file://packaged-iac-bootstrap.yaml --capabilities CAPABILITY_NAMED_IAM --parameters ParameterKey=ServiceName,ParameterValue=veriscope ParameterKey=EnvironmentName,ParameterValue=dev
```

### Delete stack

```bash
$ aws cloudformation delete-stack --stack-name iac-bootstrap-veriscope-dev
```

The terraform state S3 bucket is set to be retained in order to not lose any terraform state information in the event of accidental stack deletions etc. Once you've confirmed you don't need any of the terraform state files stored in the S3 bucket, delete the bucket by running the below command

```bash
$ aws s3 rb s3://dev-veriscope-us-east-1-terraform
```
