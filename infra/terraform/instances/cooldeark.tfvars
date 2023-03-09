tags = {
  DeployedBy  = "Veriscope Automated Deployment"
  Environment = "Dev"
  Component   = "Instances"
}
region = "us-east-1"
instances = [
  {
    # Purpose: ONBOARDING TRUST ANCHORS (TESTNET & MAINNET)
    # Repo: paycase-veriscope, onboard_verify_script branch
    name      = "ap-testnet-jlmade-001",
    node_size = "t3.medium",
    #domain = "veriscope.org",
    root_block_size = 80
  }
]

domain = "veriscope.org"