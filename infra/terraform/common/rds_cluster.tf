module "ta_db_cluster" {
  source  = "terraform-aws-modules/rds-aurora/aws"
  version = "7.7.0"

  name                                = "ta-db-cluster-${var.env}"
  vpc_id                              = module.vpc.vpc_id
  subnets                             = module.vpc.database_subnets
  engine                              = "aurora-postgresql"
  engine_version                      = "14.5"
  engine_mode                         = "provisioned"
  deletion_protection                 = true
  enable_http_endpoint                = true
  iam_database_authentication_enabled = false
  allowed_cidr_blocks                 = module.vpc.private_subnets_cidr_blocks
  allowed_security_groups             = [module.security.node_sg_id]
  create_security_group               = true
  security_group_egress_rules         = {} # Empty map. No egress security rules
  create_db_subnet_group              = false
  db_subnet_group_name                = module.vpc.database_subnet_group_name

  db_parameter_group_name         = aws_db_parameter_group.ta_db_pm_group.id
  db_cluster_parameter_group_name = aws_rds_cluster_parameter_group.ta_cluster_pm_group.id

  master_username        = "postgres"
  create_random_password = true
  random_password_length = 24

  backup_retention_period    = 7
  skip_final_snapshot        = false
  auto_minor_version_upgrade = true
  publicly_accessible        = false
  instance_class             = "db.serverless"
  instances = {
    one = {}
  }
  # preferred_backup_window = 
  # preferred_maintenance_window = 

  serverlessv2_scaling_configuration = {
    max_capacity = var.db_cluster_max_capacity
    min_capacity = 0.5
  }

  # lifecycle {
  #   ignore_changes = [availability_zones]
  # }
}

resource "aws_db_parameter_group" "ta_db_pm_group" {
  name        = "ta-postgres-db-parameter-group-${var.env}"
  family      = "aurora-postgresql14"
  description = "Parameter group for TrustAnchor postgresql db"
}

resource "aws_rds_cluster_parameter_group" "ta_cluster_pm_group" {
  name        = "ta-postgres-cluster-parameter-group-${var.env}"
  family      = "aurora-postgresql14"
  description = "Parameter group for TrustAnchor postgresql cluster"
}

resource "aws_secretsmanager_secret" "ta_db_cluster" {
  name                    = "/${var.env}/common/ta-db-cluster"
  recovery_window_in_days = 0
}

resource "aws_secretsmanager_secret_version" "ta_db_cluster" {
  secret_id = aws_secretsmanager_secret.ta_db_cluster.id
  secret_string = jsonencode(
    {
      cluster_endpoint        = module.ta_db_cluster.cluster_endpoint
      cluster_port            = module.ta_db_cluster.cluster_port
      cluster_master_username = module.ta_db_cluster.cluster_master_username
  })
}

resource "aws_ssm_parameter" "db_cluster_sg_id" {
  name        = "/${var.env}/common/ta-db-cluster/sg-id"
  description = "The ID of the security group that is attached to the TA DB cluster"
  overwrite   = true
  type        = "SecureString"
  value       = module.ta_db_cluster.security_group_id
  tags        = local.tags
}