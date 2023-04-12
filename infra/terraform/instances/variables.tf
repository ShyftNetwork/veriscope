variable "aws_account_number" {
  type        = string
  description = "The AWS account number in which infrastructure is to be deployed"
  nullable    = false
}

variable "role_name" {
  type        = string
  description = "The IAM role name to assume for creating resources"
  nullable    = false
}

variable "region" {
  type        = string
  description = "AWS region to create the EC2 instances in"
  default     = "us-east-1"
  validation {
    condition     = can(regex("^[a-z]{2}-[a-z]+-[1-9]{1}$", var.region))
    error_message = "${var.region} must be a valid AWS region."
  }
  nullable = false
}

variable "tags" {
  type        = map(any)
  description = "AWS resource tags to set on the instances and related resources"
}

variable "instances" {
  description = "List of instances to deploy. Each instance is a map which specify instance type, root block size etc. along with some optional items like tags etc."
  nullable    = false
}

variable "domain" {
  type        = string
  description = "Domain name used to create private DNS hosted zone for internal traffic routing"
  nullable    = false
}

variable "dns_provider" {
  type        = string
  default     = "aws_route53" # By default create secure environments
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