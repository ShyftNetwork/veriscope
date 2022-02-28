# Veriscope Chain Descriptions
This repository supports three shyft network blockchains.  Below describes their purpose.

## Veriscope Testnet

**Note:** Newly onboarded VASPs utilize this private network to test their veriscope integration.
Replace HTTP and WS in /veriscope_ta_node/.env with the following and restart ta-node-1 and ta-node-2.

```
sudo systemctl restart ta-node-1 ta-node-2
```

```
HTTP="http://tx.veriscope.network:9400/"
WS="wss://tx.veriscope.network:9400/"
```

blockexplorer: https://bx.veriscope.network/

fedstats: https://fedstats.veriscope.network/

https://bx.veriscope.network/address/0x43E56edA913216666DA92Bc27a874D967F3Cb206/contracts
TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS="0x43E56edA913216666DA92Bc27a874D967F3Cb206"

https://bx.veriscope.network/address/0xe515c95221B8e62c2D5b9548F8a7C5e17307f766/contracts
TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS="0xe515c95221B8e62c2D5b9548F8a7C5e17307f766"

https://bx.veriscope.network/address/0x7cC356A02119623A42E26d138fac925b6F5A444c/contracts
TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS="0x7cC356A02119623A42E26d138fac925b6F5A444c"

https://bx.veriscope.network/address/0xC6a080668A62F35687EDBb69B102B3a3766b51a8/contracts
TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS="0xC6a080668A62F35687EDBb69B102B3a3766b51a8"

## Fed Testnet

**Note:** This testnet is maintained by Shyft Federation members and mirrors the Shyft Mainnet

Replace HTTP and WS in /veriscope_ta_node/.env with the following and restart ta-node-1 and ta-node-2.

```
HTTP="https://rpc.testnet.shyft.network/"
WS="wss://rpc.testnet.shyft.network/"
```

blockexplorer: https://bx.testnet.shyft.network

fedstats: https://stats.testnet.shyft.network 

https://bx.testnet.shyft.network/address/0xfAe0e268A55E3feaA3c50f316B7c250eC1972cd2/contracts
TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS="0xfAe0e268A55E3feaA3c50f316B7c250eC1972cd2"

https://bx.testnet.shyft.network/address/0x6a938EDBc451dB9C2047765342164048dCFa9CDD/contracts
TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS="0x6a938EDBc451dB9C2047765342164048dCFa9CDD"

https://bx.testnet.shyft.network/address/0xB55EedB14DB4704b7a91644797A4Aa6DdA3275d4/contracts
TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS="0xB55EedB14DB4704b7a91644797A4Aa6DdA3275d4"

https://bx.testnet.shyft.network/address/0x75622C54E0625E314939589ffef5F181F623D188/contracts
TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS="0x75622C54E0625E314939589ffef5F181F623D188"

## Shyft Mainnet

Replace HTTP and WS in /veriscope_ta_node/.env with the following and restart ta-node-1 and ta-node-2.

```
HTTP="https://rpc.shyft.network/"
WS="wss://rpc.shyft.network/"
```
blockexplorer: https://bx.shyft.network/

fedstats: https://stats.shyft.network/

https://bx.shyft.network/address/0x86F5A10c74b77Ae99D952ab68C0a6ECB191dFEc9/contracts
TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS="0x86F5A10c74b77Ae99D952ab68C0a6ECB191dFEc9"

https://bx.shyft.network/address/0xc6c63301b5C770e705ac3D13e932c82e420096fD/contracts
TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS="0xc6c63301b5C770e705ac3D13e932c82e420096fD"

https://bx.shyft.network/address/0x8f29c911c95160e6Df2fF4063F6c90eC7943Ab17/contracts
TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS="0x8f29c911c95160e6Df2fF4063F6c90eC7943Ab17"

https://bx.shyft.network/address/0xEA64A26723C779dEE63ba3Fbc1021b87e9E71568/contracts
TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS="0xEA64A26723C779dEE63ba3Fbc1021b87e9E71568"