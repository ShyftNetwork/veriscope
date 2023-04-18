data "aws_route53_zone" "domain" {
  count = var.dns_provider == "aws_route53" ? 1 : 0
  name  = var.domain
}

# Look for a zone in a specific account by the zone name.
data "cloudflare_zone" "domain" {
  count      = var.dns_provider == "cloudflare" ? 1 : 0
  name       = var.domain
  account_id = var.cloudflare_creds["account_id"]
}

data "aws_route53_zone" "private_dns_zone" {
  zone_id = var.private_dns_zone_id
}

data "aws_subnet" "public" { 
  for_each = toset(var.available_subnet_ids)
  id = each.key
}

# Find the AZs in which the required instance type is supported
data "aws_ec2_instance_type_offerings" "available" {
  filter {
    name = "instance-type"
    values = [var.instance_type]
  }

  location_type = "availability-zone"
}

output "instance_type_azs" {
  value = data.aws_ec2_instance_type_offerings.available.locations
}

output "max_available" {
  value = local.max_available
}

output "public_subnets" {
  value = data.aws_subnet.public
}

locals {
  available_subnets = {
    for k, s in data.aws_subnet.public : k => s if contains(data.aws_ec2_instance_type_offerings.available.locations, s.availability_zone)
  }
  max_available = max(values(local.available_subnets)[*].available_ip_address_count...)
  # subnet with most available IP space and is in AZ in which the requested instance type is supported
  subnet_with_most_space = [for k, s in local.available_subnets : s.id if s.available_ip_address_count == local.max_available][0]
}
