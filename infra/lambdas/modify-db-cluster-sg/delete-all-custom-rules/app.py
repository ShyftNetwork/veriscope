import traceback
import boto3
import logging

# setup simple logging
logger = logging.getLogger()
# create formatter and add it to the handlers
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
ch = logging.StreamHandler()
ch.setLevel(level=logging.DEBUG)
ch.setFormatter(formatter)
logger.addHandler(ch)

ec2_client = boto3.client('ec2')
ssm_client = boto3.client('ssm')


def get_sg_id(env: str):
  try:
    ssm_param_path = f'/{env}/common/ta-db-cluster'
    resp = ssm_client.get_parameters_by_path(
      Path=ssm_param_path,
      Recursive=False,
      WithDecryption=True
    )
    logger.debug(f"Response from get_parameters_by_path: {resp}")
    sg_id_param = [param for param in resp['Parameters'] if param['Name'] == f'{ssm_param_path}/sg-id']
    param_count = len(sg_id_param)
    if param_count != 1:
      logger.error(f'Found incorrect number of SSM params. {resp}')
      raise ValueError(f'Expected to find exactly one but found {param_count} SSM params for TA DB Cluster Security Group ID')
    else:
      return sg_id_param[0]['Value']
  except Exception as e:
    logger.error('!!! An error occurred while getting SSM param value of TA DB cluster Security Group.')
    logger.error(f'!!! SSM Param: {ssm_param_path}')
    logger.error(traceback.format_exc())
    raise e


def get_all_custom_sg_rules(sg_id: str):
  logger.info(f"Finding all custom rules in security group {sg_id}.")
  paginator = ec2_client.get_paginator('describe_security_group_rules')
  page_iterator = paginator.paginate(
    Filters=[
        {
            'Name': 'tag:Purpose',
            'Values': ['Allow TA DB cluster access from outside the VPC']
        },
        {
            'Name': 'tag:AddedBy',
            'Values': ['modify-sg-on-demand-lambda-function']
        }
    ],
    DryRun=False,
    PaginationConfig={
        'MaxItems': 1000,
        'PageSize': 100
    }
  )
  custom_rules = []
  for page in page_iterator:
    for sg_rule in page['SecurityGroupRules']:
      custom_rules.append(sg_rule)

  return custom_rules


def delete_sg_rules_ingress(sg_id: str, rule_ids: list):
  logger.info("Deleting custom inbound security group rules for TA DB cluster.")
  try:
    resp = ec2_client.revoke_security_group_ingress(
        SecurityGroupRuleIds=rule_ids,
        GroupId=sg_id)
    logger.info(f'Security group rules {rule_ids} deleted from {sg_id}')
    return resp
  except Exception as e:
    logger.error('!!! An error occurred while deleting inbound security group rules.')
    logger.error(f'!!! Security Group ID: {sg_id} // rule ids: {rule_ids}')
    logger.error(traceback.format_exc())
    raise e


def lambda_handler(event, context):
  """Lambda function to revoke (delete) all custom inbound rules from the TA DB cluster security group

  Parameters
  ----------
  event: dict, required
      API Gateway Lambda Proxy Input Format

      Event doc: https://docs.aws.amazon.com/apigateway/latest/developerguide/set-up-lambda-proxy-integrations.html#api-gateway-simple-proxy-for-lambda-input-format

  context: object, required
      Lambda Context runtime methods and attributes

      Context doc: https://docs.aws.amazon.com/lambda/latest/dg/python-context-object.html

  Returns
  ------
  API Gateway Lambda Proxy Output Format: dict

      Return doc: https://docs.aws.amazon.com/apigateway/latest/developerguide/set-up-lambda-proxy-integrations.html
  """

  try:
    sg_id = get_sg_id(event['env'])
    custom_sg_rules = get_all_custom_sg_rules(sg_id=sg_id)
    logger.debug(f'Custom SG ingress rules discovered: {custom_sg_rules}')
    if not custom_sg_rules:
      logger.warn(f'No custom ingress rules to delete. Exiting.')
      return {
        'result': 'success',
        'api_response': None,
        'detail': f'Found zero custom security group rules. Nothing to delete! \
                    Custom SG rules in SG={sg_id}: {custom_sg_rules}'
      }
    response = delete_sg_rules_ingress(
      sg_id=sg_id, rule_ids=[rule['SecurityGroupRuleId'] for rule in custom_sg_rules])
  except KeyError as ke:
    if repr(ke) == "KeyError('env')":
      return {
        'result': 'failure',
        'api_response': None,
        'detail': f"Please provide 'env' in the event. {repr(ke)}"
      }
  except Exception as e:
    return {
      'result': 'failure',
      'api_response': None,
      'detail': repr(e)
    }
  return {
    'result': 'success',
    'api_response': response,
    'detail': f'Deleted rules: {custom_sg_rules}'
  }
