tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Dev"
  Component   = "Instances"
  Owner       = "My name"
}

region = "us-east-1"

instances = [
  {
    # The value of name should be the subdomain of your node. For example,
    # if you want your node to be foo.veriscope.org, then name = "foo"
    name = "sample-1",
    web = {
      instance_type   = "t3.micro",
      root_block_size = 30
    }
    nm = {
      instance_type   = "t3.medium",
      root_block_size = 80
    }
  },
  {
    name = "sample-2",
    web = {
      instance_type   = "t3.micro",
      root_block_size = 30
    }
    nm = {
      instance_type   = "t3.medium",
      root_block_size = 80
    }
    domain = "custom.com"
  },
  {
    name = "sample-3",
    web = {
      instance_type   = "t3.micro",
      root_block_size = 30
    }
    nm = {
      instance_type   = "c5.2xlarge",
      root_block_size = 500
    }
    domain = "another.io",
    tags = {
      Owner   = "Someone else",
      Purpose = "backup",

    }
  }
]

# valid values are "dev" or "test" or "prod" or "staging"
env = "dev"

domain = "mydomain.org"

# valid values are "aws_route53" or "cloudflare"
dns_provider = "aws_route53"
