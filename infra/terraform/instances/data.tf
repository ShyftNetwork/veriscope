data "aws_s3_object" "common_infra_output" {
  bucket = "${var.env}-veriscope-${var.region}-terraform"
  key    = "data/common_infra.json"
}

data "aws_vpc" "vpc" {
  id = local.vpc_id
}

data "external" "git" {
  program = ["${path.root}/../scripts/get-git-branch-tag.sh"]
  query = {
    id = "123"
  }
}
