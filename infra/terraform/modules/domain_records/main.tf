
data "aws_route53_zone" "domain" {
  name = var.domain
}

resource "aws_route53_record" "nodes_dns_records" {
  for_each  = var.nodes_ips
  zone_id   = data.aws_route53_zone.domain.zone_id
  name      = each.key
  type      = "A"
  ttl       = "300"
  records   = [each.value]
}