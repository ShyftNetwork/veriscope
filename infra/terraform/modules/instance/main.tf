# locals
locals {
  node_unique_name = "${var.env}-${var.name}-${var.server_type}"
  
  node_dns_name = var.create_public_dns_record ? "${var.name}.${var.domain}" : "${var.name}-${var.server_type}.${data.aws_route53_zone.private_dns_zone.name}"

  secret_name_prefix = lower("/${var.tags["Owner"]}/${var.env}/${basename(abspath(path.root))}")

  ssh_pk_secret_name = "${local.secret_name_prefix}/${local.node_unique_name}"

  instance_tags = merge(
    var.tags,
    {
      Name = local.node_dns_name,
      ServerType = var.server_type
    }
  )
}

terraform {
  required_version = ">= 1.3.6"
  required_providers {
    aws = {
      version = "~> 4.0"
    }
    tls = {
      version = "~>4.0.4"
    }
    cloudflare = {
      source  = "cloudflare/cloudflare"
      version = "~> 3.0"
    }
  }
}

resource "tls_private_key" "pk" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "aws_key_pair" "instance" {
  key_name   = local.ssh_pk_secret_name
  public_key = trimspace(tls_private_key.pk.public_key_openssh)
  tags = {
    Name = local.node_dns_name
  }
}

# secret to store ssh priv key for logging onto the EC2 instance
resource "aws_secretsmanager_secret" "node_ssh_pk" {
  name                    = local.ssh_pk_secret_name
  recovery_window_in_days = 0
}

resource "aws_secretsmanager_secret_version" "node_ssh_pk" {
  secret_id     = aws_secretsmanager_secret.node_ssh_pk.id
  secret_string = tls_private_key.pk.private_key_openssh
}

resource "aws_instance" "node" {
  ami           = var.ami_id
  instance_type = var.instance_type
  key_name      = aws_key_pair.instance.key_name
  user_data = templatefile(
    "${path.module}/templates/user-data.tftpl",
    {
      ssh_public_key = trimspace(tls_private_key.pk.public_key_openssh),
      hostname       = local.node_dns_name
    }
  )
  subnet_id = local.subnet_with_most_space
  # IAM Instance profile with this name gets created in "common" root module
  iam_instance_profile        = var.iam_instance_profile
  vpc_security_group_ids      = var.node_sg_ids
  associate_public_ip_address = true
  disable_api_termination     = false
  root_block_device {
    volume_size = var.root_block_size
    volume_type = "gp3"
    iops        = 3000
    tags        = local.instance_tags
  }
  tags = local.instance_tags
  // To ensure that you don't accidentally destroy/create the instance on a future run. 
  // This should only occur if a new subnet were added to the VPC.
  lifecycle {
    ignore_changes = [subnet_id, ami]
  }
}

resource "aws_eip" "node_eip" {
  count = var.create_public_dns_record ? 1 : 0
  instance = aws_instance.node.id
  tags     = local.instance_tags
}