# ### EBS backup
# ## TODO: might want to acquiesce disk before snapshot
# locals {
#   instances_with_backup_ebs = {
#     for instance in var.instances :
#     instance.name => instance.backup_ebs if contains(keys(instance), "backup_ebs")
#   }
# }

# resource "aws_ebs_snapshot" "root_snapshot" {
#   for_each = local.instances_with_backup_ebs
#   # TODO: assuming just one root block device
#   volume_id = aws_instance.node.root_block_device[0].volume_id
#   tags      = var.tags
# }

# ##### Secondary EBS
# locals {
#   # instances_with_secondary_ebs = {
#   #   for instance in var.instances :
#   #   instance.name => instance.secondary_ebs_size if contains(keys(instance), "secondary_ebs_size")
#   # }
# }

# resource "aws_ebs_volume" "secondary_ebs" {
#   for_each          = local.instances_with_secondary_ebs
#   availability_zone = aws_instance.node.availability_zone
#   size              = each.value
#   tags              = var.tags
# }

# resource "aws_volume_attachment" "secondary_ebs" {
#   for_each    = local.instances_with_secondary_ebs
#   device_name = var.secondary_ebs_device_name
#   volume_id   = aws_ebs_volume.secondary_ebs.id
#   instance_id = aws_instance.node.id
# }
