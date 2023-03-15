data "aws_region" "current" {}

data "aws_caller_identity" "this" {}

data "aws_ecr_authorization_token" "token" {}

data "aws_availability_zones" "available_azs" {}

data "external" "git" {
  program = ["${path.module}/../scripts/get-git-branch-tag.sh"]
  query = {
    id = "123"
  }
}