module "modify_sg_on_demand_lambda_function" {
  source  = "terraform-aws-modules/lambda/aws"
  version = "4.2.1"

  function_name      = local.modify_sg_on_demand_func_name
  description        = "Modify the TrustAnchor PostgreSQL DB cluster security group rule to authorise/revoke access from given IP address."
  handler            = "app.lambda_handler"
  timeout            = 30
  runtime            = "python3.8"
  attach_policy_json = true
  policy_json        = <<EOF
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "VisualEditor0",
            "Effect": "Allow",
            "Action": [
                "ecr:GetRegistryPolicy",
                "ecr:CreateRepository",
                "ecr:DescribeRegistry",
                "ecr:DescribePullThroughCacheRules",
                "ecr:GetAuthorizationToken",
                "ecr:PutRegistryScanningConfiguration",
                "ecr:CreatePullThroughCacheRule",
                "ecr:DeletePullThroughCacheRule",
                "ecr:PutRegistryPolicy",
                "ecr:GetRegistryScanningConfiguration",
                "ecr:BatchImportUpstreamImage",
                "ecr:DeleteRegistryPolicy",
                "ecr:PutReplicationConfiguration"
            ],
            "Resource": "*"
        },
        {
          "Sid": "PermsToModifySG",
          "Effect": "Allow",
          "Action": [
                "ec2:RevokeSecurityGroupIngress",
                "ec2:AuthorizeSecurityGroupIngress",
                "ec2:DeleteTags",
                "ec2:CreateTags"
            ],
            "Resource": [
                "arn:aws:ec2:${var.region}:${var.aws_account_number}:security-group/${module.ta_db_cluster.security_group_id}",
                "arn:aws:ec2:${var.region}:${var.aws_account_number}:security-group-rule/*"
            ]
        },
        {
          "Sid": "PermsToSSMParams",
          "Effect": "Allow",
          "Action": [
            "ssm:GetParametersByPath",
            "ssm:GetParameters",
            "ssm:GetParameter"
          ],
          "Resource": [
            "arn:aws:ssm:${var.region}:${var.aws_account_number}:parameter/${var.env}/*"
          ]
        }
    ]
}
EOF

  create_package = false
  publish        = true
  ##################
  # Container Image
  ##################
  image_uri     = module.modify_sg_on_demand_docker_image.image_uri
  package_type  = "Image"
  architectures = ["x86_64"]
  tags = merge(local.tags, {
    Env     = var.env
    Purpose = "modify-sg-on-demand"
  })
}

module "modify_sg_on_demand_docker_image" {
  source           = "terraform-aws-modules/lambda/aws//modules/docker-build"
  version          = "4.6.0"
  create_ecr_repo  = true
  ecr_repo         = local.modify_sg_on_demand_ecr_repo_name
  ecr_force_delete = true
  ecr_repo_lifecycle_policy = jsonencode({
    "rules" : [
      {
        "rulePriority" : 1,
        "description" : "Keep only the last 2 images",
        "selection" : {
          "tagStatus" : "any",
          "countType" : "imageCountMoreThan",
          "countNumber" : 2
        },
        "action" : {
          "type" : "expire"
        }
      }
    ]
  })

  image_tag            = "4.1"
  image_tag_mutability = "IMMUTABLE"
  source_path          = "${path.root}/../../lambdas/modify-db-cluster-sg/modify-sg-on-demand"
  scan_on_push         = true
}

resource "aws_ecr_repository_policy" "modify_sg_on_demand_repo_policy" {
  depends_on = [
    module.modify_sg_on_demand_docker_image
  ]
  repository = local.modify_sg_on_demand_ecr_repo_name

  policy = <<EOF
{
  "Version": "2008-10-17",
  "Statement": [
    {
      "Sid": "LambdaECRImageRetrievalPolicy",
      "Effect": "Allow",
      "Principal": {
        "Service": "lambda.amazonaws.com"
      },
      "Action": [
        "ecr:GetDownloadUrlForLayer",
        "ecr:BatchGetImage"
      ]
    }
  ]
}
EOF
}

module "delete_all_custom_rules_lambda_function" {
  source  = "terraform-aws-modules/lambda/aws"
  version = "4.2.1"

  function_name      = local.delete_all_custom_rules_func_name
  description        = "Delete all custom ingress rules added to the TrustAnchor PostgreSQL DB cluster security group to authorise access from outside the VPC."
  handler            = "app.lambda_handler"
  timeout            = 30
  runtime            = "python3.8"
  attach_policy_json = true
  policy_json        = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "ECRPerms",
      "Effect": "Allow",
      "Action": [
        "ecr:GetRegistryPolicy",
        "ecr:CreateRepository",
        "ecr:DescribeRegistry",
        "ecr:DescribePullThroughCacheRules",
        "ecr:GetAuthorizationToken",
        "ecr:PutRegistryScanningConfiguration",
        "ecr:CreatePullThroughCacheRule",
        "ecr:DeletePullThroughCacheRule",
        "ecr:PutRegistryPolicy",
        "ecr:GetRegistryScanningConfiguration",
        "ecr:BatchImportUpstreamImage",
        "ecr:DeleteRegistryPolicy",
        "ecr:PutReplicationConfiguration"
      ],
      "Resource": "*"
    },
    {
      "Sid": "EC2Perms",
      "Effect": "Allow",
      "Action": [
        "ec2:DescribeSecurityGroupRules"
      ],
      "Resource": "*"
    },
    {
      "Sid": "PermsToModifySG",
      "Effect": "Allow",
      "Action": [
        "ec2:RevokeSecurityGroupIngress",
        "ec2:DeleteTags"
      ],
      "Resource": [
        "arn:aws:ec2:${var.region}:${var.aws_account_number}:security-group/${module.ta_db_cluster.security_group_id}",
        "arn:aws:ec2:${var.region}:${var.aws_account_number}:security-group-rule/*"
      ]
    },
    {
      "Sid": "PermsToSSMParams",
      "Effect": "Allow",
      "Action": [
        "ssm:GetParametersByPath",
        "ssm:GetParameters",
        "ssm:GetParameter"
      ],
      "Resource": [
        "arn:aws:ssm:${var.region}:${var.aws_account_number}:parameter/${var.env}/*"
      ]
    }
  ]
}
EOF

  create_package = false
  publish        = true
  ##################
  # Container Image
  ##################
  image_uri     = module.delete_all_custom_rules_docker_image.image_uri
  package_type  = "Image"
  architectures = ["x86_64"]

  allowed_triggers = {
    ScheduledTriggerRule = {
      principal  = "events.amazonaws.com"
      source_arn = aws_cloudwatch_event_rule.delete_custom_rules_schedule.arn
    }
  }
  tags = merge(local.tags, {
    Env     = var.env
    Purpose = "delete-custom-sg-rules"
  })
}

module "delete_all_custom_rules_docker_image" {
  source           = "terraform-aws-modules/lambda/aws//modules/docker-build"
  version          = "4.6.0"
  create_ecr_repo  = true
  ecr_repo         = local.delete_all_custom_rules_ecr_repo_name
  ecr_force_delete = true
  ecr_repo_lifecycle_policy = jsonencode({
    "rules" : [
      {
        "rulePriority" : 1,
        "description" : "Keep only the last 2 images",
        "selection" : {
          "tagStatus" : "any",
          "countType" : "imageCountMoreThan",
          "countNumber" : 2
        },
        "action" : {
          "type" : "expire"
        }
      }
    ]
  })

  image_tag            = "4.1"
  image_tag_mutability = "IMMUTABLE"
  source_path          = "${path.root}/../../lambdas/modify-db-cluster-sg/delete-all-custom-rules"
  scan_on_push         = true
}

resource "aws_ecr_repository_policy" "delete_all_custom_rules_repo_policy" {
  depends_on = [
    module.delete_all_custom_rules_docker_image
  ]
  repository = local.delete_all_custom_rules_ecr_repo_name

  policy = <<EOF
{
  "Version": "2008-10-17",
  "Statement": [
    {
      "Sid": "LambdaECRImageRetrievalPolicy",
      "Effect": "Allow",
      "Principal": {
        "Service": "lambda.amazonaws.com"
      },
      "Action": [
        "ecr:GetDownloadUrlForLayer",
        "ecr:BatchGetImage"
      ]
    }
  ]
}
EOF
}

##################################
# Cloudwatch Events (EventBridge)
##################################
resource "aws_cloudwatch_event_rule" "delete_custom_rules_schedule" {
  name                = "delete_custom_rules_schedule_${var.env}"
  description         = "CRON Schedule for delete all custom rules Lambda Function in the ${var.env} environment"
  schedule_expression = var.delete_custom_rules_schedule
}

resource "aws_cloudwatch_event_target" "delete_all_custom_rules_lambda_function" {
  rule = aws_cloudwatch_event_rule.delete_custom_rules_schedule.name
  arn  = module.delete_all_custom_rules_lambda_function.lambda_function_arn
}

resource "random_pet" "random" {
  length    = 2
  separator = "-"
}

resource "aws_ssm_parameter" "modify_sg_on_demand_func_arn" {
  name        = "/${var.env}/common/modify_sg_on_demand_func/arn"
  description = "The ARN of the lambda function which modifies a given security group on demand by adding/deleting security group rules"
  overwrite   = true
  type        = "SecureString"
  value       = module.modify_sg_on_demand_lambda_function.lambda_function_arn
  tags = merge(local.tags, {
    Env     = var.env,
    Purpose = "modify-sg-on-demand"
  })
}

resource "aws_ssm_parameter" "delete_all_rules_on_schedule_func_arn" {
  name        = "/${var.env}/common/delete_all_rules_on_schedule_func/arn"
  description = "The ARN of the lambda function which runs regularly to delete all custom security group rules from a given security group"
  overwrite   = true
  type        = "SecureString"
  value       = module.delete_all_custom_rules_lambda_function.lambda_function_arn
  tags = merge(local.tags, {
    Env     = var.env
    Purpose = "delete-custom-sg-rules"
  })
}

locals {
  modify_sg_on_demand_func_name         = "modify-sg-on-demand-${random_pet.random.id}"
  modify_sg_on_demand_ecr_repo_name     = local.modify_sg_on_demand_func_name
  delete_all_custom_rules_func_name     = "delete-all-custom-db-cluster-sg-rules-${random_pet.random.id}"
  delete_all_custom_rules_ecr_repo_name = local.delete_all_custom_rules_func_name
}