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

locals {
  max_available = max(values(data.aws_subnet.public)[*].available_ip_address_count...)
  subnet_with_most_space = [for k, s in data.aws_subnet.public : s.id if s.available_ip_address_count == local.max_available][0]
}
