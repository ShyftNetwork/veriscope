## Overview

The module `deploy_instances` is to spin up EC2 instances which host one or more nodes belonging to a ----------.

**Note:** This module has to be run once per AWS region if deploying a multi-region network.

#### Example

```
module "deploy_instances" {
  instances = {
   us-east-1 = [
    {
      name = "abc-001",
      node_size = "t3.xlarge",
      root_block_size = 500,
      type = "xxxx",
      node_type = "xxxxxx"
    },
    {
      name = "abc-002",
      node_size = "t3.small",
      root_block_size = 80,
      type = "xxxx",
      node_type = "xxxxxx"
    }
  ]
  ap-northeast-1 = [
    {
      name = "abc-301",
      node_size = "t3.large",
      root_block_size = 500,
      type = "xxxx",
      node_type = "xxxxxx"
    }
  ]
}
```
