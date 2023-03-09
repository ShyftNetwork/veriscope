variable "aws_account_number" {
  type        = string
  description = "The AWS account number in which infrastructure is to be deployed"
  validation {
    condition     = can(regex("^[0-9]{12}$", var.aws_account_number))
    error_message = "Must be a valid AWS Account number."
  }
  nullable = false
}

variable "role_name" {
  type        = string
  description = "The IAM role name to assume for creating resources"
  nullable    = false
}

variable "tags" {
  type        = map(any)
  description = "AWS resource tags to set on the VPC"
}

variable "vpc_cidr" {
  type        = string
  description = "CIDR block to assign to the VPC"
  nullable    = false
}

variable "region" {
  type        = string
  description = "AWS region to deploy infrastructure in"
  default     = "us-east-1"
  validation {
    condition     = can(regex("^[a-z]{2}-[a-z]+-[1-9]{1}$", var.region))
    error_message = "${var.region} must be a valid AWS region."
  }
  nullable = false
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

variable "private_network_domain" {
  type        = string
  description = "Domain name used to create private DNS hosted zone for internal traffic routing"
  nullable    = false
}

variable "db_cluster_max_capacity" {
  type        = number
  default     = 4
  description = "Maximum capacity of Aurora PostgreSQL cluster for scaling configuration"

  validation {
    condition     = contains([2, 4, 8, 16, 32, 64, 192, 384], var.db_cluster_max_capacity)
    error_message = "Value must be one of [2, 4, 8, 16, 32, 64, 192, 384] for max capacity."
  }
}

variable "delete_custom_rules_schedule" {
  type        = string
  default     = "rate(6 hours)"
  description = "The frequency at which the lambda function to delete custom SG rules in the TA DB cluster Security Group"
}
