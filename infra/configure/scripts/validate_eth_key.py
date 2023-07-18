"""
Module for checking Ethereum private key and corresponding address.
"""
import sys
from eth_utils import is_hex, decode_hex
from eth_keys import keys


def check_key(private_key_hex, provided_address):
    """
    Check if Ethereum private key is valid and if it corresponds to the provided Ethereum address.
    """
    if not is_hex(private_key_hex) or len(private_key_hex) != 64:
        return 1

    private_key = decode_hex(private_key_hex)
    lower_bound = 1
    upper_bound = 0xfffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140
    if not lower_bound <= int.from_bytes(private_key, byteorder='big') <= upper_bound:
        return 2

    private_key_obj = keys.PrivateKey(private_key)
    generated_address = private_key_obj.public_key.to_checksum_address()
    if generated_address.lower() != provided_address.lower():
        return 3

    return 0


def main():
    """
    Accept input and call key checking function.
    """
    if len(sys.argv) != 3:
        sys.exit("Usage: python3 check_ethereum_key.py private_key ethereum_address")

    private_key = sys.argv[1]
    ethereum_address = sys.argv[2]
    sys.exit(check_key(private_key, ethereum_address))


if __name__ == "__main__":
    main()
