# output "veriscope_nodes_full" {
#   value     = module.web_instances
# }

output "veriscope_nodes" {
  value = {
    web = {
      for name, node in module.web_instances : name => {
        "public_fqdn"              = node.public_fqdn
        "private_fqdn"             = node.private_fqdn
        "public_ip"                = node.aws_instance.public_ip,
        "private_ip"               = node.aws_instance.private_ip,
        "ssh_priv_key_secret_name" = node.ssh_secret_name,
        "instance_id"              = node.aws_instance.id,
        "tags"                     = node.aws_instance.tags_all,
        "availability_zone"        = node.aws_instance.availability_zone,
        "subnet"                   = node.aws_instance.subnet_id,
      }
    },
    nethermind = {
      for name, node in module.nm_instances : name => {
        "public_fqdn"              = node.public_fqdn
        "private_fqdn"             = node.private_fqdn
        "public_ip"                = node.aws_instance.public_ip,
        "private_ip"               = node.aws_instance.private_ip,
        "ssh_priv_key_secret_name" = node.ssh_secret_name,
        "instance_id"              = node.aws_instance.id,
        "tags"                     = node.aws_instance.tags_all,
        "availability_zone"        = node.aws_instance.availability_zone,
        "subnet"                   = node.aws_instance.subnet_id,
      }
    }
  }
}

output "ta_db" {
  sensitive = true
  value     = module.ta_db
}

output "env" {
  value = var.env
}
