terraform {
  required_providers {
    postgresql = {
      source  = "cyrilgdn/postgresql"
      version = "~> 1.17"
    }
  }
}

# Random string to use as master password
resource "random_password" "db_user_pwd" {
  length  = 24
  special = false
  numeric = false
}

resource "aws_secretsmanager_secret" "db_user_pwd" {
  name                    = "${var.secret_name_prefix}/${var.db_username}"
  recovery_window_in_days = 0
}

resource "aws_secretsmanager_secret_version" "db_user_pwd" {
  secret_id     = aws_secretsmanager_secret.db_user_pwd.id
  secret_string = random_password.db_user_pwd.result
}

resource "postgresql_role" "db_user" {
  name            = var.db_username
  create_database = true
  login           = true
  password        = random_password.db_user_pwd.result
}

resource "postgresql_database" "db" {
  name  = var.db_name
  owner = postgresql_role.db_user.name
}

resource "postgresql_grant" "db_user_all_privs" {
  database    = postgresql_database.db.name
  role        = postgresql_role.db_user.name
  object_type = "database"
  privileges  = ["CREATE", "CONNECT", "TEMPORARY"]
}
