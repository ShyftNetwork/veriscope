<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivateKeyEncryptToCryptoWalletAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_wallet_addresses', function (Blueprint $table) {
            $table->encrypted('private_key_encrypt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_wallet_addresses', function (Blueprint $table) {
            $table->dropColumn('private_key_encrypt');
        });
    }
}
