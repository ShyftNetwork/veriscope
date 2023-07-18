# local variables
locals {
  security_output    = jsondecode(data.aws_s3_object.common_infra_output.body)["security"]["value"]
  vpc_output         = jsondecode(data.aws_s3_object.common_infra_output.body)["vpc"]["value"]
  rds_cluster_output = jsondecode(data.aws_s3_object.common_infra_output.body)["db_cluster"]["value"]
  env                = jsondecode(data.aws_s3_object.common_infra_output.body)["env"]["value"]
  private_dns_zone   = jsondecode(data.aws_s3_object.common_infra_output.body)["private_dns_zone"]["value"]
  vpc_id             = local.vpc_output["vpc_id"]
  public_subnets     = local.vpc_output["public_subnets"]
  tags = merge(var.tags, {
    ENVIRONMENT  = lower(local.env),
    TF_WORKSPACE = terraform.workspace,
    GIT_BRANCH   = data.external.git.result.branch,
    GIT_TAG      = data.external.git.result.tag
  })
}

# terraform configuration
terraform {
  required_version = ">= 1.3.6"
  required_providers {
    aws = {
      version = "~> 4.0"
    }
    postgresql = {
      source  = "cyrilgdn/postgresql"
      version = "~> 1.17"
    }
    git = {
      source  = "paultyng/git"
      version = "0.1.0"
    }
    random = {
      version = "~> 3.4"
    }
    cloudflare = {
      source  = "cloudflare/cloudflare"
      version = "~> 3.0"
    }
  }

  backend "s3" {
  }
}

# resources
resource "random_id" "index" {
  byte_length = 2
}

module "web_instances" {
  source   = "./../modules/instance"
  for_each = { for inst in var.instances : inst.name => inst }

  env                  = local.env
  ami_id               = data.aws_ami.ubuntu.id
  iam_instance_profile = local.security_output["iam_instance_profile_name"]
  node_sg_ids          = [local.security_output["node_sg_id"]]
  available_subnet_ids     = local.public_subnets
  root_block_size          = each.value.web.root_block_size
  instance_type            = each.value.web.instance_type
  name                     = each.value.name
  server_type              = "web"
  domain                   = contains(keys(each.value), "domain") ? each.value.domain : var.domain
  tags                     = contains(keys(each.value), "tags") ? merge(local.tags, each.value.tags) : local.tags
  dns_provider             = var.dns_provider
  cloudflare_creds         = var.cloudflare_creds
  create_public_dns_record = true
  private_dns_zone_id      = local.private_dns_zone["zone_id"]
}

module "nm_instances" {
  source   = "./../modules/instance"
  for_each = { for inst in var.instances : inst.name => inst }
  depends_on = [
    aws_security_group.nethermind_traffic_between_web_and_nm_instances
  ]

  env                  = local.env
  ami_id               = data.aws_ami.ubuntu.id
  iam_instance_profile = local.security_output["iam_instance_profile_name"]
  node_sg_ids = [
    local.security_output["node_sg_id"],
    aws_security_group.nethermind_traffic_between_web_and_nm_instances[each.key].id
  ]
  available_subnet_ids     = local.public_subnets
  root_block_size          = each.value.nm.root_block_size
  instance_type            = each.value.nm.instance_type
  name                     = each.value.name
  server_type              = "nm"
  domain                   = contains(keys(each.value), "domain") ? each.value.domain : var.domain
  tags                     = contains(keys(each.value), "tags") ? merge(local.tags, each.value.tags) : local.tags
  dns_provider             = var.dns_provider
  cloudflare_creds         = var.cloudflare_creds
  create_public_dns_record = false
  private_dns_zone_id      = local.private_dns_zone["zone_id"]
}

resource "aws_security_group" "nethermind_traffic_between_web_and_nm_instances" {
  for_each               = { for inst in var.instances : inst.name => inst }
  description            = "Security group to allow nethermind traffic between web and nm instances for ${each.key} veriscope node"
  name_prefix            = "web-nm-traffic-sg-"
  vpc_id                 = local.vpc_id
  revoke_rules_on_delete = true

  ingress {
    description = "Nethermind traffic from web instance"
    from_port   = 8545
    to_port     = 8545
    protocol    = "tcp"
    cidr_blocks = ["${module.web_instances[each.key].aws_instance.private_ip}/32"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    data.aws_vpc.vpc.tags,
    { VeriscopeNode = each.key }
  )
}

module "ta_db" {
  source             = "./../modules/postgres_db"
  for_each           = { for inst in var.instances : inst.name => inst }
  db_username        = "trustanchor-${each.key}"
  db_name            = "trustanchor-${each.key}"
  secret_name_prefix = module.web_instances[each.key].secret_name_prefix
}
