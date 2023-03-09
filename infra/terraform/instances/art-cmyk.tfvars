tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Prod"
  Component   = "Instances"
  Owner       = "Ravitej"
}
region = "us-east-1"
instances = [
  {
    name            = "art-1.12-upg-1.15",
    instance_type   = "t3.medium",
    root_block_size = 500
  },
  {
    name            = "art-1.15",
    instance_type   = "t3.medium",
    root_block_size = 500
  }
]

env = "prod"

domain = "veriscope.network"

dns_provider = "cloudflare"
