tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Prod"
  Component   = "Instances"
  Purpose     = "Dedicated Nethermind DB sync/backup node. Regularly backup nethermind_db to S3"
  Owner       = "veriscope"
}
region = "us-east-1"
instances = [
  {
    name            = "nm-v1.12.4-db-backup-node",
    instance_type   = "t3.small",
    root_block_size = 500
  },
  {
    name            = "nm-v1.15.0-db-backup-node",
    instance_type   = "t3.small",
    root_block_size = 500
  }
]

env = "prod"

domain = "veriscope.network"

dns_provider = "cloudflare"

# cloudflare_creds = { }