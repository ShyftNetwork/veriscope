output "vpc" {
  value = module.vpc
}

output "security" {
  value = module.security
}

output "env" {
  value = var.env
}

output "private_network_domain" {
  value = var.private_network_domain
}

output "private_dns_zone" {
  value = aws_route53_zone.private_dns_zone
}

output "db_cluster" {
  sensitive = true
  value     = module.ta_db_cluster
}

output "modify_sg_on_demand_lambda" {
  value = module.modify_sg_on_demand_lambda_function
}

output "delete_rules_on_schedule_lambda" {
  value = module.delete_all_custom_rules_lambda_function
}

output "ssm_modify_sg_on_demand_lambda" {
  sensitive = true
  value     = aws_ssm_parameter.modify_sg_on_demand_func_arn.name
}

output "ssm_delete_rules_on_schedule_lambda" {
  sensitive = true
  value     = aws_ssm_parameter.delete_all_rules_on_schedule_func_arn.name
}