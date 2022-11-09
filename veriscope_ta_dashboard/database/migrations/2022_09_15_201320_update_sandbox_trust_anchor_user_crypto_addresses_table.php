<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\SandboxTrustAnchorUserCryptoAddress;

class UpdateSandboxTrustAnchorUserCryptoAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('sandbox_trust_anchor_user_crypto_addresses', function($table) {
          $table->longText('crypto_address')->change();
          $table->longText('crypto_proof')->change();
          $table->longText('crypto_private_key')->nullable();
        });

        SandboxTrustAnchorUserCryptoAddress::where('crypto_address', '=', '18crLganfufUzqFB2eH7jt4jPPYze45gZs')
        ->update([
            'crypto_address'     => '18crLganfufUzqFB2eH7jt4jPPYze45gZs',
            'crypto_proof'       => '{"chain": "BTC-mainnet", "asset": "BTC", "address_type": "P2PKH", "address": "18crLganfufUzqFB2eH7jt4jPPYze45gZs", "trust_anchor_pubkey": "0x04c2d213d585fed2213395e61f99b988f692aec84d38b288d14ffc4a90c879531cd92105bf1336da88ec6bf1f86a74293695cfeaa444dc9fcf2614d201e4a64c02", "pubkey": "02bc52ddcc4f24f7c180a2a7aba994a9b58ebd2f91e3b061a82734a86709cd936d", "signature": "H6WiQvsUan2Rn81848TSC0xs8sTzGbZDAhgcm0cvBhbxYTzshFV6BKFBGOJgDo33R+5cYRb7+8HQbSklBaRiDWs="}',
            'crypto_type'        => 'tBTC',
            'crypto_private_key' => 'L5HBFYkwBtVCk51NCCGDogDRjy5vhn7vP2WhEy1TAwXnYTvbCA1o'
        ]);


        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','0xA4bdddE6cEA9FB6a57949EBA19E6D213dc569C67')
        ->update([
            'crypto_address'     => '0xA4bdddE6cEA9FB6a57949EBA19E6D213dc569C67',
            'crypto_proof'       => '{"chain": "ETH-mainnet", "asset": null, "address_type": "EOA", "address": "0xA4bdddE6cEA9FB6a57949EBA19E6D213dc569C67", "trust_anchor_pubkey": "0x04c2d213d585fed2213395e61f99b988f692aec84d38b288d14ffc4a90c879531cd92105bf1336da88ec6bf1f86a74293695cfeaa444dc9fcf2614d201e4a64c02", "signature": "e7889ecdb94099b3f1a9f00bbd6e03c7fd6d0b84bb4f6ccb87215bae5940782d3d272c6794d3dc4d17604953da5fb5992f87df9feb7fdf067843ae48fb17ca651b"}',
            'crypto_type'        => 'tETH',
            'crypto_private_key' => '932dc3b6fae4b3d7234563f34f90487140378b1ee569cff1176f489ea9769b35'
        ]);

        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','t1V6m4PFXCNU3zBDEHfEEwC3ZpxKAZHmiC2')
        ->update([
            'crypto_address'     => 't1V6m4PFXCNU3zBDEHfEEwC3ZpxKAZHmiC2',
            'crypto_proof'       => '{"chain": "zcash-mainnet", "asset": "ZEC", "address_type": "P2PKH", "address": "t1V6m4PFXCNU3zBDEHfEEwC3ZpxKAZHmiC2", "trust_anchor_pubkey": "0x04c2d213d585fed2213395e61f99b988f692aec84d38b288d14ffc4a90c879531cd92105bf1336da88ec6bf1f86a74293695cfeaa444dc9fcf2614d201e4a64c02", "signature": "IOdx63I7yJBDjmuMpZJAfDL16CL3X9/fyFiUjBXffmNiCai/1Dhgef4BK1NLbtsiltCAHg1yiqbnD5vjslYjs2k="}',
            'crypto_type'        => 'tZEC',
            'crypto_private_key' => 'L4PzvEARNJcKhTh7uBXELrXHcMaP8w9pF8rniWXnx66c1X4j76yN'
        ]);


        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','45VzrocqjddFmy6vC58XPx5unNdHQozupbuCYPtFCXGreP7mZwCLijrX2pCSeMd1jiTFLohGVwqLyJauAtQ9d8xx3WjGKax')
        ->update([
            'crypto_address'     => '45PdSUXNcVnMigXnX2eQehWbBXQedg17ybVCWKVMWLPyQgZq6yozgZxPzz9UMqHWx25YiuaPXGCoF85vj5qz2YbqKhw9FGm',
            'crypto_proof'       => '{"chain": "XMR-mainnet", "asset": "XMR", "address_type": "default", "address": "45PdSUXNcVnMigXnX2eQehWbBXQedg17ybVCWKVMWLPyQgZq6yozgZxPzz9UMqHWx25YiuaPXGCoF85vj5qz2YbqKhw9FGm", "trust_anchor_pubkey": "0x04c2d213d585fed2213395e61f99b988f692aec84d38b288d14ffc4a90c879531cd92105bf1336da88ec6bf1f86a74293695cfeaa444dc9fcf2614d201e4a64c02", "signature": "SigV1UHswJNL6RH6LdkoA2oYsRU7DmwgLPbNmb1Tq8uvPdRTX3qLyzLrAMArMsQWe5ADQZDLSfdzUyg6vcgtXVPgh4EL8"}',
            'crypto_type'        => 'tXMR',
            'crypto_private_key' => '1e7b4a3c9f464b4a6f65c10089f547504dcdda67bce51cbb0bef52b3b4d4690c'
        ]);




        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','1FRf4bSEBw7zbKDjZZ47kbeB4Lw6rvbnxm')
        ->update([
            'crypto_address'      => '13J8EydyW5Agge9K4UsxMfKE6u7B2gtfgn',
            'crypto_proof'        => '',
            'crypto_type'         => 'tBTC',
            'crypto_private_key'  => 'L4TbrtWiz3UpnSz8kmjsgpvhTZNojz6bd427ufpM56kLFwjkNqif'
        ]);


        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','0x18b6CbE5459f01e683612d36be418Ec5e7Ea936D')
        ->update([
            'crypto_address'      => '0x08dd8246c4c15F6dA97e5a40ED5a24C405b4FB24',
            'crypto_proof'        => '',
            'crypto_type'         => 'tETH',
            'crypto_private_key'  => 'c0d5445e7eb374aba6ed2a1c584111875467004d4d400848419856faa5361e66'
        ]);

        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','t1QB4wfmpc4mTgnpcnvT7x5ncVEWaFy8mhp')
        ->update([
            'crypto_address'      => 't1JLYsteVEu7ER5fzE2veqF8Cx5gV3U2mvX',
            'crypto_proof'        => '',
            'crypto_type'         => 'tZEC',
            'crypto_private_key'  => 'L1CUhWLbmW1H9SKHvykP4h7i6nnSxErZM2bJnSHhDkBWPzFP9tuw'
        ]);


        SandboxTrustAnchorUserCryptoAddress::where('crypto_address','=','41y3dwh9VHDeNbZRhkvhUBYuxNVCgqhzG3y7HhuXMqRK9vq6jF4S88eF8gd5H8g61yZsuuAoUHPmwYGRu8ny5VdRTxWYES5')
        ->update([
            'crypto_address'      => '47Rgk6NAKz1jBNugTxdE3edm2rqaGFCF1ZPKyCEfX77SU9kW37RpfEGCbwaFfqjUHmNPX6QM3ECK5LvdbyF1k6888hGt6TR',
            'crypto_proof'        => '',
            'crypto_type'         => 'tXMR',
            'crypto_private_key'  => '17bab4ece2c82fe88868fd19b33696bb934825bd9d9907a9dbf2d6e39c461b0b'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
