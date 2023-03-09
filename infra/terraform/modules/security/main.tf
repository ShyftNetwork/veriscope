terraform {
  required_providers {
    aws = {
      version = ">= 4.0"
    }
  }
}
# locals
locals {
  security_group_open_ports = csvdecode(var.sg_open_ports)
  account_id                = data.aws_caller_identity.current.account_id
}

# To handle instance defaults
locals {
  region = data.aws_region.current.name
}

data "aws_caller_identity" "current" {}

data "aws_region" "current" {}

# resources
resource "aws_security_group" "node_sg" {
  description = "Security group to control SSH and HTTP traffic between veriscope node(s) and the internet"
  name_prefix = "veriscope-nodes-sg"
  vpc_id      = var.vpc_id
}

resource "aws_security_group_rule" "sg_rule_ingress" {
  type              = "ingress"
  security_group_id = aws_security_group.node_sg.id
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]

  for_each  = { for port in local.security_group_open_ports : port.port => port }
  from_port = each.key
  to_port   = each.key
}

resource "aws_security_group_rule" "sg_rule_egress" {
  type              = "egress"
  description       = "Allow all outbound traffic to anywhere"
  security_group_id = aws_security_group.node_sg.id
  from_port         = -1
  to_port           = -1
  protocol          = -1
  cidr_blocks       = ["0.0.0.0/0"]
}

resource "aws_iam_policy" "ec2_svc_role_cwlogs_perms" {
  name = "EC2SvcRoleCWLogsPerms-${local.region}-${var.vpc_id}"

  policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "logs:CreateLogGroup",
        "logs:CreateLogStream",
        "logs:PutLogEvents"
      ],
      "Resource": "*"
    }
  ]
}
EOF
}

// This IAM policy is to allow the EC2 instance to read secrets from AWS Secrets Manager.
// But this is currently not required and so, commented out. However, we expect to use 
// this policy in the future. Hence, leaving it here.

// resource "aws_iam_policy" "ec2_svc_role_sm_perms" {
//   name = "EC2SvcRoleSecretsPerms-${local.region}"

//   policy = <<EOF
// {
//   "Version": "2012-10-17",
//   "Statement": [
//     {
//       "Effect": "Allow",
//       "Action": [
//         "secretsmanager:GetResourcePolicy",
//         "secretsmanager:GetSecretValue",
//         "secretsmanager:DescribeSecret",
//         "secretsmanager:ListSecretVersionIds"
//       ],
//       "Resource": "${data.aws_secretsmanager_secret.target_pwd.arn}"
//     },
//     {
//       "Effect": "Allow",
//       "Action": [
//         "secretsmanager:GetRandomPassword",
//         "secretsmanager:ListSecrets",
//         "kms:Decrypt"
//       ],
//       "Resource": "*"
//     }
//   ]
// }
// EOF
// }

resource "aws_iam_role" "ec2_service_role" {
  name = "EC2InstanceServiceRole-${local.region}-${var.vpc_id}"

  assume_role_policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": "sts:AssumeRole",
      "Principal": {
        "Service": "ec2.amazonaws.com"
      },
      "Effect": "Allow",
      "Sid": ""
    }
  ]
}
EOF

  tags = var.tags
}

resource "aws_iam_role_policy_attachment" "s3_readonly_policy_attachment" {
  role       = aws_iam_role.ec2_service_role.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonS3ReadOnlyAccess"
}

resource "aws_iam_role_policy_attachment" "cloudwatch_agent_policy_attachment" {
  role       = aws_iam_role.ec2_service_role.name
  policy_arn = "arn:aws:iam::aws:policy/CloudWatchAgentServerPolicy"
}

// Associated to Secrets Manager IAM policy mentioned above. Leaving it here for future purposes.

// resource "aws_iam_role_policy_attachment" "ec2_svc_role_sm_perms_policy_attachment" {
//   role       = aws_iam_role.ec2_service_role.name
//   policy_arn = aws_iam_policy.ec2_svc_role_sm_perms.arn
// }

resource "aws_iam_role_policy_attachment" "ec2_svc_role_cwlogs_perms_policy_attachment" {
  role       = aws_iam_role.ec2_service_role.name
  policy_arn = aws_iam_policy.ec2_svc_role_cwlogs_perms.arn
}

resource "aws_iam_instance_profile" "nodes_inst_profile" {
  name = "NodeInstanceProfile-${local.region}-${var.vpc_id}"
  role = aws_iam_role.ec2_service_role.name
}
