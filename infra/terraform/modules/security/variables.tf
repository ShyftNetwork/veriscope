# Variables

variable "vpc_id" {}

variable "tags" {
  # default = { Name = "Veriscope Automated Node Deployment" }
}

variable "sg_open_ports" {
  default = <<-CSV
    port
    80
    443
  CSV
}
