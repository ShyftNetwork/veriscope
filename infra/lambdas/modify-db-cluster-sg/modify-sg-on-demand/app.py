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


def create_sg_rule_ingress(sg_id: str, cidr: str, port: int):
  logger.info(f"Adding inbound rule to security group {sg_id}.")
  try:
    resp = ec2_client.authorize_security_group_ingress(
      GroupId=sg_id,
      IpProtocol='tcp',
      CidrIp=cidr,
      FromPort=port,
      ToPort=port,
      # IpPermissions=[
      #     {
      #       'IpProtocol': 'tcp',
      #       'FromPort': port,
      #       'ToPort': port,
      #       'IpRanges': [{'CidrIp': cidr}]},
      # ],
      TagSpecifications=[
        {
          'ResourceType': 'security-group-rule',
          'Tags': [
            {
              'Key': 'Purpose',
              'Value': 'Allow TA DB cluster access from outside the VPC'
            },
            {
              'Key': 'AddedBy',
              'Value': 'modify-sg-on-demand-lambda-function'
            }
          ]
        },
      ])
    logger.info(f'Security group rule added to {sg_id} to allow inbound access from {cidr} on port {port}')
    return resp
  except Exception as e:
    logger.error('!!! An error occurred while adding inbound security group rule.')
    logger.error(f'!!! Security Group ID: {sg_id} // CIDR: {cidr} // Port: {port}')
    logger.error(traceback.format_exc())
    raise e


def delete_sg_rule_ingress(sg_id: str, cidr: str, port: int):
  logger.info(f"Deleting inbound rule from security group {sg_id}.")
  try:
    resp = ec2_client.revoke_security_group_ingress(
        IpProtocol='tcp',
        CidrIp=cidr,
        FromPort=port,
        ToPort=port,
        GroupId=sg_id)
    logger.info(f'Security group rule deleted from {sg_id} from {cidr} on port {port}')
    return resp
  except Exception as e:
    logger.error('!!! An error occurred while deleting inbound security group rule.')
    logger.error(f'!!! Security Group ID: {sg_id} // CIDR: {cidr} // Port: {port}')
    logger.error(traceback.format_exc())
    raise e


def get_sg_id(ssm_param_name: str):
  try:
    resp = ssm_client.get_parameter(
      Name=ssm_param_name,
      WithDecryption=True
    )
    logger.debug(f"Response from get_parameter: {resp}")
    return resp['Parameter']['Value']
  except Exception as e:
    logger.error(f'!!! An error occurred while getting SSM param {ssm_param_name}.')
    logger.error(traceback.format_exc())
    raise e


def lambda_handler(event, context):
  """Lambda function to authorise (create) or revoke (delete) inbound traffic through security group for TA RDS DB cluster

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

  print('Event: %s', event)
  logger.info('Event: %s', event)
  # {
  #   "action": "create/delete",
  #   "cidr": "0.0.0.0/0",
  #   "port": "5432",
  #   "sg_id_ssm_param_name": "/dev/common/ta-db-cluster/sg-id"
  # }
  action = event['action']
  cidr = event['cidr']
  port = event['port']
  sg_id_ssm_param_name = event['sg_id_ssm_param_name']
  if action.lower() not in ['create', 'delete']:
    return {
      'result': 'failure',
      'api_response': None,
      'detail': 'The value of action must be one of "create" or "delete"'
    }
  try:
    sg_id = get_sg_id(sg_id_ssm_param_name)
    if action.lower() == 'create':
      response = create_sg_rule_ingress(sg_id=sg_id, cidr=cidr, port=int(port))
    elif action.lower() == 'delete':
      response = delete_sg_rule_ingress(sg_id=sg_id, cidr=cidr, port=int(port))
  except Exception as e:
    return {
      'result': 'failure',
      'api_response': None,
      'detail': repr(e)
    }
  return {
    'result': 'success',
    'api_response': response,
    'detail': None
  }
