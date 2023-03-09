tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Test"
  Component   = "Instances"
  Owner       = "Ravitej"
}
region = "us-east-1"
instances = [
  {
    name = "art-12",
    web = {
      instance_type   = "t3.micro",
      root_block_size = 80
    }
    nm = {
      instance_type   = "t3.medium"
      root_block_size = 80
    }
  },
  {
    name = "art-15",
    web = {
      instance_type   = "t3.small",
      root_block_size = 80
    }
    nm = {
      instance_type   = "t3.medium"
      root_block_size = 80
    }
  }
]

env = "test"

domain = "veriscope.net"

dns_provider = "aws_route53"
