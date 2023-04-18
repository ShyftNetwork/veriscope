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

data "aws_ami" "ubuntu" {
  most_recent = true

  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-focal-20.04-amd64-server-*"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }

  owners = ["099720109477"] # Canonical
}
