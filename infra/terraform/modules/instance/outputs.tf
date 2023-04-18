output "aws_instance" {
  value = aws_instance.node
}

output "secret_name_prefix" {
  value = local.secret_name_prefix
}

output "ssh_secret_name" {
  value = aws_secretsmanager_secret.node_ssh_pk.name
}

output "public_fqdn" {
  value = var.create_public_dns_record ? var.dns_provider == "aws_route53" ? aws_route53_record.node_public_dns_record[0].fqdn : cloudflare_record.node_public_dns_record[0].hostname : ""
}

output "private_fqdn" {
  value = aws_route53_record.node_private_dns_record.fqdn
}
