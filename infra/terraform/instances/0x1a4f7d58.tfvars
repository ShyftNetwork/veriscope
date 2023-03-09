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
    name            = "shyft-onboarding-testnet",
    node_size       = "t3.medium",
    root_block_size = 80
  },
  {
    # Purpose: ONBOARDING TRUST ANCHORS (TESTNET & MAINNET)
    # Repo: paycase-veriscope, onboard_verify_script branch
    name            = "shyft-onboarding-mainnet",
    node_size       = "t3.medium",
    root_block_size = 80
  },
  {
    # Purpose: VERIFICATIONS (MAINNET)
    # Repo: paycase-veriscope, onboard_verify_script branch
    name            = "innofi",
    node_size       = "t3.medium",
    root_block_size = 80
  }
]
