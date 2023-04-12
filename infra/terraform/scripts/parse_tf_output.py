import argparse
import json
import os
from ruamel.yaml import YAML
from ruamel.yaml.comments import CommentedMap

INDENTATION_SPACES = 2


def add_vars_and_comments(inventory, tf_output):

    inventory["all"]["vars"] = CommentedMap()

    # Add debug variable under vars with a comment
    object_depth = 2
    inventory["all"]["vars"]["debug"] = False
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "debug",
        before="\nWhether to print debug messages to the screen while running the playbooks.\nNOTE: It may print secret information too. So, please use with caution.",
        indent=object_depth*INDENTATION_SPACES
    )

    object_depth = 2
    inventory["all"]["vars"]["env"] = tf_output["env"]["value"].lower()
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "env",
        before="\nEnvironment into which the nodes are deployed. This is also set in terraform module when deploying instances.",
        indent=object_depth*INDENTATION_SPACES
    )

    object_depth = 2
    inventory["all"]["vars"]["veriscope_target"] = "veriscope_testnet"
    multiline_comment = "\nIdentify a chain to deploy to.\nValid values are 'veriscope_testnet', 'fed_testnet', 'fed_mainnet'"
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "veriscope_target",
        before=multiline_comment,
        indent=object_depth*INDENTATION_SPACES
    )

    object_depth = 2
    inventory["all"]["vars"]["ta_dashboard_admin_user"] = {
        "firstname": "Krishna",
        "lastname": "Vasudeva",
        "email": "krishna@shyft.network",
        "password": "password"
    }
    multiline_comment = "\nTA dashboard admin user to create.\nIf different for each node, move this var into the host specific level."
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "ta_dashboard_admin_user",
        before=multiline_comment,
        indent=object_depth*INDENTATION_SPACES
    )

    return inventory


def parse_tf_output(tf_output):
    ansible_inventory = CommentedMap()

    ansible_inventory["all"] = CommentedMap()
    ansible_inventory["all"]["children"] = CommentedMap()
    ansible_inventory["all"]["children"]["nethermind"] = CommentedMap()
    ansible_inventory["all"]["children"]["nethermind"]["hosts"] = CommentedMap()
    ansible_inventory["all"]["children"]["web"] = CommentedMap()
    ansible_inventory["all"]["children"]["web"]["hosts"] = CommentedMap()
    ansible_inventory["all"]["vars"] = CommentedMap()

    nethermind = tf_output["veriscope_nodes"]["value"]["nethermind"]
    web = tf_output["veriscope_nodes"]["value"]["web"]

    for instance_name, instance_data in nethermind.items():
        host_data = CommentedMap()
        host_data["ssh_priv_key_secret_name"] = instance_data["ssh_priv_key_secret_name"]
        host_data["owner"] = instance_data["tags"]["Owner"].lower()
        ansible_inventory["all"]["children"]["nethermind"]["hosts"][instance_data["private_fqdn"]] = host_data
        # ansible_inventory["all"]["children"]["nethermind"]["hosts"].yaml_set_comment_before_after_key(instance_data["private_fqdn"], before="Node Manager host")

    for instance_name, instance_data in web.items():
        host_data = CommentedMap()
        host_data["ssh_priv_key_secret_name"] = instance_data["ssh_priv_key_secret_name"]
        host_data["trust_anchor_private_key"] = "db3906947188edfe196fe01d3e161ef82706947188edfe196fe01d3e161ef827"
        host_data["trust_anchor_account_address"] = "0x67A212172E2D64e8233de33bC570102454BBA56B"
        host_data["trust_anchor_preferred_name"] = "trust_anchor_preferred_name"
        host_data["owner"] = instance_data["tags"]["Owner"].lower()
        ansible_inventory["all"]["children"]["web"]["hosts"][instance_data["public_fqdn"]] = host_data
        # ansible_inventory["all"]["children"]["web"]["hosts"].yaml_set_comment_before_after_key(instance_data["private_fqdn"], before="Web host")

    for web_instance, nm_instance in zip(web.items(), nethermind.items()):
        nm_instance_name, nm_instance_info = nm_instance
        web_instance_name, web_instance_info = web_instance
        ansible_inventory["all"]["children"]["web"]["hosts"][web_instance_info["public_fqdn"]]["nm_host"] = nm_instance_info['private_fqdn']

    return ansible_inventory


def main():
    parser = argparse.ArgumentParser(description="Parse Terraform output and create Ansible inventory file.")
    parser.add_argument("tf_output_path", help="Path to the Terraform output JSON file")
    parser.add_argument("ansible_inventory_path", help="Path to the generated Ansible inventory YAML file")

    args = parser.parse_args()

    with open(args.tf_output_path, "r") as tf_output_file:
        tf_output = json.load(tf_output_file)

    ansible_inventory = parse_tf_output(tf_output)
    ansible_inventory = add_vars_and_comments(ansible_inventory, tf_output)

    with open(args.ansible_inventory_path, "w") as ansible_inventory_file:
        yaml = YAML()
        yaml.indent(mapping=2, sequence=4, offset=2)
        yaml.explicit_start = True
        yaml.width = 80
        yaml.allow_unicode = True

        ansible_inventory_file.write("#############################################################################\n")
        ansible_inventory_file.write("# This is a generated Ansible inventory file from Terraform output\n")
        ansible_inventory_file.write("# nethermind and web are defined as children groups\n")
        ansible_inventory_file.write("# vars is applicable for all groups. However, vars or anything under vars\n")
        ansible_inventory_file.write("### can be moved into specific host group or even individual hosts\n")
        ansible_inventory_file.write("### if you'd like to customise values per host group or per host\n")
        ansible_inventory_file.write("#############################################################################\n")

        yaml.dump(ansible_inventory, ansible_inventory_file)


if __name__ == "__main__":
    main()
