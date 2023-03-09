terraform {
  required_providers {
    aws = {
      version = ">= 4.0"
    }
  }
}

locals {
  public_private_cidrs = cidrsubnets(data.aws_vpc.vpc.cidr_block, 4, 4)
}

module "public_subnet_addrs" {
  source = "hashicorp/subnets/cidr"
  base_cidr_block = local.public_private_cidrs[0]
  # for_each = data.aws_availability_zones.available_azs
  networks = [for az in data.aws_availability_zones.available_azs.names : 
              { name = "public-${az}"
                new_bits = 20
              }]
}

module "private_subnet_addrs" {
  source = "hashicorp/subnets/cidr"
  base_cidr_block = local.public_private_cidrs[1]
  # for_each = data.aws_availability_zones.available_azs
  networks = [for az in data.aws_availability_zones.available_azs.names : 
              { name = "private-${az}"
                new_bits = 20
              }]
}

data "aws_vpc" "vpc" {
  id = var.vpc_id
}

data "aws_availability_zones" "available_azs" {}

resource "aws_subnet" "public_subnets" {
  count                   = length(data.aws_availability_zones.available_azs)
  vpc_id                  = data.aws_vpc.vpc.id
  cidr_block              = module.public_subnet_addrs.network_cidr_blocks[count.index]
  availability_zone       = data.aws_availability_zones.available_azs.names[count.index]
  tags                    = data.aws_vpc.vpc.tags
}


resource "aws_subnet" "private_subnets" {
  count                   = length(data.aws_availability_zones.available_azs)
  vpc_id                  = data.aws_vpc.vpc.id
  cidr_block              = module.private_subnet_addrs.network_cidr_blocks[count.index]
  availability_zone       = data.aws_availability_zones.available_azs.names[count.index]
  tags                    = data.aws_vpc.vpc.tags
}

resource "aws_route_table" "rt" {
  vpc_id = var.vpc_id
  route {
      cidr_block = "0.0.0.0/0"
      gateway_id = var.igw_id
  }
  tags   = data.aws_vpc.vpc.tags
}

resource "aws_route_table" "private_rt" {
  vpc_id = var.vpc_id
  tags   = data.aws_vpc.vpc.tags
}

resource "aws_route_table_association" "rt_association" {
  count          = length(aws_subnet.public_subnets)
  subnet_id      = element(aws_subnet.public_subnets.*.id, count.index)
  route_table_id = aws_route_table.rt.id
}

resource "aws_route_table_association" "private_rt_association" {
  count          = length(aws_subnet.private_subnets)
  subnet_id      = element(aws_subnet.private_subnets.*.id, count.index)
  route_table_id = aws_route_table.private_rt.id
}
