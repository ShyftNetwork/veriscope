<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\SandboxTrustAnchorUserCryptoAddress;

class CreateSandboxTrustAnchorUserCryptoAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sandbox_trust_anchor_user_crypto_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('crypto_address')->nullable();
            $table->string('crypto_proof')->nullable();
            $table->string('crypto_type')->nullable();
            $table->integer('sandbox_trust_anchor_user_id')->unsigned()->index();
            $table->foreign('sandbox_trust_anchor_user_id')->references('id')->on('sandbox_trust_anchor_users')->onDelete('cascade');
            $table->timestamps();
        });

        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => '18crLganfufUzqFB2eH7jt4jPPYze45gZs',
            'crypto_proof'    => '',
            'crypto_type'     => 'tBTC',
            'sandbox_trust_anchor_user_id' => 1
        ]);


        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => '0xA4bdddE6cEA9FB6a57949EBA19E6D213dc569C67',
            'crypto_proof'    => '',
            'crypto_type'     => 'tETH',
            'sandbox_trust_anchor_user_id' => 1
        ]);

        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => 't1V6m4PFXCNU3zBDEHfEEwC3ZpxKAZHmiC2',
            'crypto_proof'    => '',
            'crypto_type'     => 'tZEC',
            'sandbox_trust_anchor_user_id' => 1
        ]);


        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => '45VzrocqjddFmy6vC58XPx5unNdHQozupbuCYPtFCXGreP7mZwCLijrX2pCSeMd1jiTFLohGVwqLyJauAtQ9d8xx3WjGKax',
            'crypto_proof'    => '',
            'crypto_type'     => 'tXMR',
            'sandbox_trust_anchor_user_id' => 1
        ]);




        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => '1FRf4bSEBw7zbKDjZZ47kbeB4Lw6rvbnxm',
            'crypto_proof'    => '',
            'crypto_type'     => 'tBTC',
            'sandbox_trust_anchor_user_id' => 2
        ]);


        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => '0x18b6CbE5459f01e683612d36be418Ec5e7Ea936D',
            'crypto_proof'    => '',
            'crypto_type'     => 'tETH',
            'sandbox_trust_anchor_user_id' => 2
        ]);

        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => 't1QB4wfmpc4mTgnpcnvT7x5ncVEWaFy8mhp',
            'crypto_proof'    => '',
            'crypto_type'     => 'tZEC',
            'sandbox_trust_anchor_user_id' => 2
        ]);


        SandboxTrustAnchorUserCryptoAddress::create([
            'crypto_address'  => '41y3dwh9VHDeNbZRhkvhUBYuxNVCgqhzG3y7HhuXMqRK9vq6jF4S88eF8gd5H8g61yZsuuAoUHPmwYGRu8ny5VdRTxWYES5',
            'crypto_proof'    => '',
            'crypto_type'     => 'tXMR',
            'sandbox_trust_anchor_user_id' => 2
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sandbox_trust_anchor_user_crypto_addresses');
    }
}
