# local variables
locals {
  vpc_cidr_by_six = cidrsubnets(var.vpc_cidr, 8, 4, 4, 4, 4, 4, 8)
  # set aside the first and last few address for admin/emergency
  public_subnets_cidr   = local.vpc_cidr_by_six[1]
  intra_subnets_cidr    = local.vpc_cidr_by_six[2]
  database_subnets_cidr = local.vpc_cidr_by_six[3]
  is_dev                = anytrue([var.env == "dev", var.env == "test"])
  is_prod               = anytrue([var.env == "prod", var.env == "staging"])
  # pipeline_executor_ip_addr = jsondecode(data.http.my_public_ip.response_body).ip
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
      version = "2.22.0"
    }
  }
  backend "s3" {
  }
}



# resources
################################################################################
# VPC Module
################################################################################
module "vpc" {
  source  = "terraform-aws-modules/vpc/aws"
  version = "3.14.2"

  name = var.tags["Name"]
  cidr = var.vpc_cidr

  azs = data.aws_availability_zones.available_azs.names

  intra_subnets = [for i in range(length(data.aws_availability_zones.available_azs.names)) :
  cidrsubnet(local.intra_subnets_cidr, 4, i)]

  public_subnets = [for i in range(length(data.aws_availability_zones.available_azs.names)) :
  cidrsubnet(local.public_subnets_cidr, 4, i)]

  database_subnets = [for i in range(length(data.aws_availability_zones.available_azs.names)) :
  cidrsubnet(local.database_subnets_cidr, 4, i)]

  create_database_subnet_group       = false
  create_database_subnet_route_table = false
  #############################################################
  # ONLY IN DEV ENVIRONMENTS
  create_database_internet_gateway_route = local.is_dev ? true : false
  single_nat_gateway                     = local.is_dev ? true : false
  #############################################################

  #############################################################
  # ONLY IN PROD ENVIRONMENTS
  enable_nat_gateway = local.is_prod ? true : false
  #############################################################

  # when both single_nat_gateway and one_nat_gateway_per_az are specified, single_nat_gateway takes
  # precedence. Hence, this input will be ignored in dev envs and used in prod envs.
  one_nat_gateway_per_az = true

  enable_dns_hostnames = true
  enable_dns_support   = true
  enable_ipv6          = false


  public_subnet_tags = {
    Name = "${replace(title(var.tags["Name"]), " ", "")}-Public"
  }

  intra_subnet_tags = {
    Name = "${replace(title(var.tags["Name"]), " ", "")}-Intra"
  }

  database_subnet_tags = {
    Name = "${replace(title(var.tags["Name"]), " ", "")}-Database"
  }

  tags = var.tags
}

resource "aws_route53_zone" "private_dns_zone" {
  name          = var.private_network_domain
  tags          = var.tags
  force_destroy = true

  vpc {
    vpc_id = module.vpc.vpc_id
  }
}

module "security" {
  depends_on    = [module.vpc]
  source        = "./../modules/security"
  vpc_id        = module.vpc.vpc_id
  tags          = var.tags
  sg_open_ports = <<-CSV
                      port
                      80
                      443
                    CSV
}
