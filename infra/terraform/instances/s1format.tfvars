tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Dev"
  Component   = "Instances"
  Owner       = "Nicolas"
}

region = "us-east-1"

instances = [
  {
    # The value of name should be the subdomain of your node. For example,
    # if you want your node to be foo.veriscope.org, then name = "foo"
    #name            = "q1",
    #instance_type   = "t3.small",
    #root_block_size = 80
  },
  {
    # The value of name should be the subdomain of your node. For example,
    # if you want your node to be foo.veriscope.org, then name = "foo"
    #name            = "q2",
    #instance_type   = "t3.small",
    #root_block_size = 80
  }
]

domain = "veriscope.org"
