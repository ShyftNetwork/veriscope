output "iam_instance_profile_name" {
  value = aws_iam_instance_profile.nodes_inst_profile.name
}

output "node_sg_id" {
  value = aws_security_group.node_sg.id
}
