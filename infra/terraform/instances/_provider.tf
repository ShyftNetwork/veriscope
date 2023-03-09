provider "aws" {
  region = var.region
  assume_role {
    role_arn = "arn:aws:iam::${var.aws_account_number}:role/${var.role_name}"
  }
}

provider "postgresql" {
  scheme   = "awspostgres"
  host     = local.rds_cluster_output["cluster_endpoint"]
  port     = local.rds_cluster_output["cluster_port"]
  username = local.rds_cluster_output["cluster_master_username"]
  password = local.rds_cluster_output["cluster_master_password"]

  superuser = false
}

provider "cloudflare" {
  api_token = var.cloudflare_creds["api_token"]
}