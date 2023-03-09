tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Dev"
  Component   = "Instances"
  Purpose     = "Dedicated Nethermind DB sync/backup node. Regularly backup nethermind_db to S3"
  Owner       = "veriscope"
}
region = "us-east-1"
instances = [
  {
    name            = "nm-v1.12.4-db-backup-node",
    instance_type   = "t3.medium",
    root_block_size = 160
  },
  {
    name            = "nm-v1.15.0-db-backup-node",
    instance_type   = "t3.medium",
    root_block_size = 160
  }
]

env = "dev"

domain = "veriscope.org"

dns_provider = "aws_route53"
