# local variables
locals {
  vpc_cidr_by_six = cidrsubnets(var.vpc_cidr, 4, 4, 4, 4, 4, 4, 4)
  # set aside the first and last few address for admin/emergency
  public_subnets_cidr   = local.vpc_cidr_by_six[1]
  private_subnets_cidr  = local.vpc_cidr_by_six[2]
  database_subnets_cidr = local.vpc_cidr_by_six[3]
  # is_dev                = anytrue([var.env == "dev", var.env == "test"])
  # is_prod               = anytrue([var.env == "prod", var.env == "staging"])
  # pipeline_executor_ip_addr = jsondecode(data.http.my_public_ip.response_body).ip
  tags = merge(var.tags, {
    ENVIRONMENT  = lower(var.env),
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
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 2.25"
    }
  }
  backend "s3" {
  }
}



# resources
resource "aws_eip" "nat" {
  vpc   = true
}

################################################################################
# VPC Module
################################################################################
module "vpc" {
  source  = "terraform-aws-modules/vpc/aws"
  version = "3.19.0"

  name = var.tags["Name"]
  cidr = var.vpc_cidr

  azs = data.aws_availability_zones.available_azs.names

  private_subnets = [for i in range(length(data.aws_availability_zones.available_azs.names)) :
  cidrsubnet(local.private_subnets_cidr, 3, i)]

  public_subnets = [for i in range(length(data.aws_availability_zones.available_azs.names)) :
  cidrsubnet(local.public_subnets_cidr, 3, i)]

  database_subnets = [for i in range(length(data.aws_availability_zones.available_azs.names)) :
  cidrsubnet(local.database_subnets_cidr, 3, i)]

  create_database_subnet_group           = true
  create_database_subnet_route_table     = true
  create_database_internet_gateway_route = false

  enable_nat_gateway     = true
  single_nat_gateway     = true
  one_nat_gateway_per_az = false
  // Use the EIPs created above for the NAT gateways
  reuse_nat_ips       = true
  external_nat_ip_ids = aws_eip.nat[*].id

  enable_dns_hostnames = true
  enable_dns_support   = true
  enable_ipv6          = false

  # Cloudwatch log group and IAM role will be created
  enable_flow_log                      = true
  create_flow_log_cloudwatch_log_group = true
  create_flow_log_cloudwatch_iam_role  = true

  flow_log_max_aggregation_interval         = 60
  flow_log_cloudwatch_log_group_name_prefix = "/${var.env}/${replace(var.tags["Name"], " ", "")}-vpc/"
  flow_log_cloudwatch_log_group_name_suffix = "flow-logs"
  vpc_flow_log_tags = local.tags
  
  public_subnet_tags = merge(local.tags, {
    Name = "${replace(title(var.tags["Name"]), " ", "")}-Public"
  })

  private_subnet_tags = merge(local.tags, {
    Name = "${replace(title(var.tags["Name"]), " ", "")}-Private"
  })

  database_subnet_tags = merge(local.tags, {
    Name = "${replace(title(var.tags["Name"]), " ", "")}-Database"
  })

  tags = local.tags
}

resource "aws_route53_zone" "private_dns_zone" {
  name          = var.private_network_domain
  tags          = local.tags
  force_destroy = true

  vpc {
    vpc_id = module.vpc.vpc_id
  }
}

module "security" {
  depends_on    = [module.vpc]
  source        = "./../modules/security"
  vpc_id        = module.vpc.vpc_id
  tags          = local.tags
  sg_open_ports = <<-CSV
                      port
                      80
                      443
                    CSV
}
