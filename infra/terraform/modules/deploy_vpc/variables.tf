variable "assume_role_arn" {
  type        = string
  description = "ARN of the role to assume to create resources"
}

variable "vpc_tags" {
  description = "AWS resource tags to set on the VPC"
  type        = map(any)
}

variable "vpc_cidr" {
  description = "CIDR block to assign to the VPC"
  type        = string
}