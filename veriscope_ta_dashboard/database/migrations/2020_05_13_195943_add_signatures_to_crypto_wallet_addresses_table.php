<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignaturesToCryptoWalletAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_wallet_addresses', function (Blueprint $table) {
            $table->string('signature_hash')->nullable();
            $table->longText('signature')->nullable();
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
            $table->dropColumn('signature_hash');
            $table->dropColumn('signature');
        });
    }
}
