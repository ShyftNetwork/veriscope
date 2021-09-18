<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCryptoTypeToTrustAnchorUserCryptoAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchor_user_crypto_addresses', function (Blueprint $table) {
            $table->string('crypto_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trust_anchor_user_crypto_addresses', function (Blueprint $table) {
            $table->dropColumn('crypto_type');
        });
    }
}
