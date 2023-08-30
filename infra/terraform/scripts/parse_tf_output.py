import argparse
import json
from ruamel.yaml import YAML
from ruamel.yaml.comments import CommentedMap
from ruamel.yaml.scalarstring import SingleQuotedScalarString as SQ

INDENTATION_SPACES = 2


def add_vars_and_comments(inventory, tf_output):

    inventory["all"]["vars"] = CommentedMap()

    env = tf_output["env"]["value"].lower()

    object_depth = 2
    inventory["all"]["vars"]["env"] = env
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "env",
        before="\nMandatory. Environment into which the nodes are deployed. This is also set in terraform module when deploying instances.",
        indent=object_depth*INDENTATION_SPACES
    )

    object_depth = 2
    inventory["all"]["vars"]["veriscope_version"] = ""
    multiline_comment = "\nMandatory. Version of the veriscope app to deploy. It may be a branch or a tag name without the 'origin/' prefix."
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "veriscope_version",
        before=multiline_comment,
        indent=object_depth*INDENTATION_SPACES
    )

    # Add github_token variable under vars with a comment when env is dev or test
    if env in ['dev', 'test']:
        object_depth = 2
        inventory["all"]["vars"]["github_username"] = ""
        multiline_comment = "\nMandatory. GitHub username with permissions to clone the private veriscope-internal repo."
        inventory["all"]["vars"].yaml_set_comment_before_after_key(
            "github_username",
            before=multiline_comment,
            indent=object_depth*INDENTATION_SPACES
        )

        object_depth = 2
        inventory["all"]["vars"]["github_token"] = ""
        multiline_comment = "\nMandatory. GitHub token with permissions to clone the private veriscope-internal repo."
        inventory["all"]["vars"].yaml_set_comment_before_after_key(
            "github_token",
            before=multiline_comment,
            indent=object_depth*INDENTATION_SPACES
        )

    # Add debug variable under vars with a comment
    object_depth = 2
    inventory["all"]["vars"]["debug"] = False
    inventory["all"]["vars"].yaml_set_comment_before_after_key(
        "debug",
        before="\nWhether to print debug messages to the screen while running the playbooks.\nNOTE: It may print secret information too. So, please use with caution.",
        indent=object_depth*INDENTATION_SPACES
    )

    object_depth = 2
    inventory["all"]["vars"]["veriscope_target"] = "veriscope_testnet"
    multiline_comment = "\nMandatory. Identify a chain to deploy to.\nValid values are 'veriscope_testnet', 'fed_testnet', 'fed_mainnet'"
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
    multiline_comment = "\nMandatory. TA dashboard admin user to create.\nIf different for each node, move this var into the host specific level."
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

    for _, instance_data in nethermind.items():
        host_data = CommentedMap()
        host_data["ssh_priv_key_secret_name"] = instance_data["ssh_priv_key_secret_name"]
        host_data["owner"] = instance_data["tags"]["Owner"].lower()
        ansible_inventory["all"]["children"]["nethermind"]["hosts"][instance_data["private_fqdn"]] = host_data
        # ansible_inventory["all"]["children"]["nethermind"]["hosts"].yaml_set_comment_before_after_key(instance_data["private_fqdn"], before="Node Manager host")

    for _, instance_data in web.items():
        host_data = CommentedMap()
        host_data["ssh_priv_key_secret_name"] = instance_data["ssh_priv_key_secret_name"]
        host_data["owner"] = instance_data["tags"]["Owner"].lower()
        host_data["trust_anchors"] = [{
            "private_key": "asdasdasdasdasd",
            "address": SQ('0x87348374hjhjhj'),
            "preferred_name": "pref_name"
        }]
        multiline_comment = "Trust Anchor(s) details. If you're setting up multiple Trust Anchors, add them to the list here.\n" \
                            "trust_anchors:\n" \
                            "  - private_key: dsfhlksdjflgf\n" \
                            "    address: '0x1234567890'\n" \
                            "    preferred_name: Trust Anchor 1\n" \
                            "  - private_key: owieurowiyer98\n" \
                            "    address: '0x0987654321'\n" \
                            "    preferred_name: Trust Anchor 2\n"
        object_depth = 5
        host_data.yaml_set_comment_before_after_key(
            "trust_anchors",
            before=multiline_comment,
            indent=object_depth*INDENTATION_SPACES
        )
        ansible_inventory["all"]["children"]["web"]["hosts"][instance_data["public_fqdn"]] = host_data
        # ansible_inventory["all"]["children"]["web"]["hosts"].yaml_set_comment_before_after_key(instance_data["private_fqdn"], before="Web host")

    for web_instance, nm_instance in zip(web.items(), nethermind.items(), strict=True):
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
