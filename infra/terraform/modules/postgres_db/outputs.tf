output "ta_db_user_pwd" {
  sensitive = true
  value = {
    (var.db_username) : random_password.db_user_pwd
  }
}

output "ta_db_userpwd_arn" {
  value = aws_secretsmanager_secret_version.db_user_pwd.arn
}