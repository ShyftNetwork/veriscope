variable "db_username" {
  type        = string
  description = "Name of the PostgeSQL user (role) to create who becomes the owner of the database"
  nullable    = false
}

variable "db_name" {
  type        = string
  description = "Name of the PostgeSQL database to create"
  nullable    = false
}

variable "secret_name_prefix" {
  type = string
}