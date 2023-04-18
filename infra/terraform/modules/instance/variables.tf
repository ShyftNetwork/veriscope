# Variables

variable "iam_instance_profile" {
  type = string
  nullable = false
}

variable "ami_id" {
  type = string
  nullable = false
}

variable "node_sg_ids" {
  type     = list(string)
  nullable = false
}

variable "available_subnet_ids" {
  type        = set(string)
  description = "IDs of the subnets to put the instance in."
  nullable    = false
}

variable "secondary_ebs_device_name" {
  type = string
  default = "/dev/sdf"
}

variable "root_block_size" {
  type        = string
  description = "Size in GB of the root EBS block"
  nullable    = false
}

variable "instance_type" {
  type        = string
  description = "EC2 instance type of the instance to create"
  nullable    = false
}

variable "server_type" {
  type        = string
  description = "The type of server this instance is going to be used for ('web' or 'nm')"

  validation {
    condition     = contains(["web", "nm"], var.server_type)
    error_message = "Value must be one of 'web', 'nm'. Values are case-sensitive."
  }
  nullable = false
}

variable "name" {
  type        = string
  description = "The name of the node. This becomes the subdomain of the node's FQDN"
  nullable    = false
}

variable "tags" {
  type = map
}

variable "private_dns_zone_id" {
  type = string
  description = "The id of the private hosted zone attached to the VPC"
  nullable    = false
}

variable "domain" {
  type        = string
  description = "Domain in which to create public DNS records for the instance"
  nullable    = false
}

variable "create_public_dns_record" {
  type        = bool
  description = "Whether to create a public DNS record for this instance"
  nullable    = false
}

variable "dns_provider" {
  type        = string
  default     = "aws_route53"
  description = "DNS provider which manages the DNS records for this domain."

  validation {
    condition     = contains(["aws_route53", "cloudflare"], var.dns_provider)
    error_message = "Value must be one of 'aws_route53', 'cloudflare'. Values are case-sensitive."
  }
  nullable = true
}

variable "cloudflare_creds" {
  type        = map(string)
  description = "API token to authenticate with Cloudflare and the account id which contains the zone."

  nullable = true
}

variable "env" {
  type        = string
  default     = "prod" # By default create secure environments
  description = "Environment to deploy in. Valid values are prod, staging, dev, or test."

  validation {
    condition     = contains(["prod", "staging", "dev", "test"], var.env)
    error_message = "Value must be one of 'prod', 'staging', 'dev', or 'test'. Values are case-sensitive."
  }
  nullable = false
}