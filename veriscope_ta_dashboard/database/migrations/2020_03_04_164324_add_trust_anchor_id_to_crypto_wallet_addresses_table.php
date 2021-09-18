<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrustAnchorIdToCryptoWalletAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_wallet_addresses', function (Blueprint $table) {
            $table->integer('trust_anchor_id')->unsigned()->index()->nullable();
            $table->foreign('trust_anchor_id')->references('id')->on('trust_anchors')->onDelete('cascade');
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
            $table->dropColumn('trust_anchor_id');
        });
    }
}
