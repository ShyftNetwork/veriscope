<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrivateKeyToCryptoWalletAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_wallet_addresses', function (Blueprint $table) {
            $table->string('private_key')->nullable();

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
            $table->dropColumn('private_key');
        });
    }
}
