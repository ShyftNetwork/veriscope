resource "aws_route53_record" "node_private_dns_record" {
  zone_id  = var.private_dns_zone_id
  name     = var.server_type == "web" ? var.name : "${var.name}-${var.server_type}"
  type     = "A"
  ttl      = "300"
  records  = [aws_instance.node.private_ip]
}

resource "aws_route53_record" "node_public_dns_record" {
  count    = var.dns_provider == "aws_route53" && var.create_public_dns_record ? 1 : 0
  zone_id  = data.aws_route53_zone.domain[0].zone_id
  name     = var.server_type == "web" ? var.name : "${var.name}-${var.server_type}"
  type     = "A"
  ttl      = "300"
  records  = [aws_eip.node_eip[0].public_ip]
}

# Add a record to the domain
resource "cloudflare_record" "node_public_dns_record" {
  count    = var.dns_provider == "cloudflare" && var.create_public_dns_record ? 1 : 0
  zone_id  = data.cloudflare_zone.domain[0].zone_id
  name     = var.server_type == "web" ? var.name : "${var.name}-${var.server_type}"
  type     = "A"
  ttl      = 300
  value    = aws_eip.node_eip[0].public_ip
  # tags     = toset(each.value.tags["Name"])
}
