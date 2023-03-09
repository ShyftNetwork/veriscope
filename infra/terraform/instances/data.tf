data "aws_s3_object" "common_infra_output" {
  bucket = "${var.env}-veriscope-us-east-1-terraform"
  key    = "data/common_infra.json"
}

data "aws_vpc" "vpc" {
  id = local.vpc_id
}

data "git_repository" "current" {
  path = "${path.root}/../../../"
}
